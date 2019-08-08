<?php
//Артиклы, цены и количество от поставщиков для товаров в каталоге
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');

include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){
//Получаем id админа
$sql = "SELECT IF(COUNT(*)>0,a_admin.id,'') 
    				FROM a_admin 
    					WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."' 
                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
        	"; 
$res = mysql_query($sql);
$myrow = mysql_fetch_array($res);
$a_admin_id_cur=$myrow[0];

    $_t=_GP('_t');
    // ************************************************************

    if ($_t=='s_tests_add_question'){
        $name_=_GP('name');
            if ($name_==''){echo 'Вопрос не должен быть пустым';exit;}
        $data_=array();
       
        $sql = "SELECT  IF(COUNT(*)>0,s_test_quest.id,''),
                        IF(COUNT(*)>0,s_test_quest.html_code,''),
                        IF(COUNT(*)>0,s_test_quest.data_create,''),
                        IF(COUNT(*)>0,s_test_quest.a_admin_id,''),
                        IF(COUNT(*)>0,s_test_quest.chk_tip,'')
        				FROM s_test_quest 
        					WHERE s_test_quest.name='"._DB($name_)."'
        	"; 
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $data_['nomer']=$myrow[0];
        $data_['html_code']=$myrow[1];
        $data_['data_create']=$myrow[2];
        $data_['a_admin_id']=$myrow[3];
        $data_['chk_tip']=$myrow[4];
        if ($data_['nomer']==''){
            $sql_ins = "INSERT into s_test_quest (
            				name,
                            a_admin_id,
                            html_code
            			) VALUES (
            				'"._DB($name_)."',
                            '"._DB($a_admin_id_cur)."',
                            ''
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
            $data_['nomer'] = mysql_insert_id();
            $data_['a_admin_id']=$a_admin_id_cur;
            $data_['data_create']=date('Y-m-d H:i:s');
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
        }
        $data_['data_create']=date('d.m.Y H:i',strtotime($data_['data_create']));
        $data_['answer']=array();
        $data_['answer']['id']=array();
        $data_['answer']['name']=array();
        $data_['answer']['chk_true']=array();
        $data_['answer']['html_code']=array();
        $data_['answer']['img']=array();
        
        
        //Получаем ответы
        $sql = "SELECT  s_test_answer.id,
                        s_test_answer.name,
                        s_test_answer.chk_true,
                        s_test_answer.html_code,
                        (SELECT IF(COUNT(*)>0,GROUP_CONCAT(a_photo.img SEPARATOR '::'),'') FROM a_photo WHERE a_photo.a_menu_id='401' AND a_photo.row_id=s_test_answer.id ORDER BY a_photo.sid) AS img
                        
    				FROM s_test_answer 
    					WHERE s_test_answer.s_test_quest_id='"._DB($data_['nomer'])."'"; 
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['answer']['id'][$i]=$myrow[0];
            $data_['answer']['name'][$i]=$myrow[1];
            $data_['answer']['chk_true'][$i]=$myrow[2];
            $data_['answer']['html_code'][$i]=$myrow[3];
            $data_['answer']['img'][$i]=$myrow[4];
            
            
        }
   
        
        
        
        echo json_encode($data_);
    }
    
    //Добавление ответа
    if ($_t=='s_tests_add_answer'){
        $data_=array();
        $s_test_quest_id=_GP('s_test_quest_id');
        if ($s_test_quest_id==''){echo 'Номер теста не может быть пустым s_test_quest_id='.$s_test_quest_id;exit;}
        $sql="SELECT 
                    COUNT(*) 
                        FROM s_test_quest
                        WHERE s_test_quest.id='"._DB($s_test_quest_id)."'
                        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        if ($myrow[0]==''){echo 'Тест с номером '.$s_test_quest_id.' не обнаружен!';exit;}
        else{
            $sql_ins = "INSERT into s_test_answer (
            				s_test_quest_id,
                            html_code
            			) VALUES (
            				'"._DB($s_test_quest_id)."',
                            ''
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
            $data_['nomer'] = mysql_insert_id();
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
        } 
        echo json_encode($data_);
    }
    
    //**********************************************************************************
    //Автозаполнение вопросов
    if ($_t=='s_tests_add_question_autocomp'){
        $data_=array();
        $term=_GP('term');
        //Получаем ответы
        $sql = "SELECT  s_test_quest.id,
                        s_test_quest.name,
                        s_test_quest.data_create,
                        s_test_quest.html_code,
                        s_test_quest.chk_tip
                        
    				FROM s_test_quest
    					WHERE s_test_quest.name LIKE '%"._DB($term)."%'
                        LIMIT 20
                        "; 
        $res = mysql_query($sql) or die(mysql_error());
        
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_[$i]['id']=$myrow[0];
            $data_[$i]['value']=$myrow[1];
            $data_[$i]['dt']=date('d.m.Y H:i',strtotime($myrow['data_create']));;
            $data_[$i]['html_code']=$myrow[3];
            $data_[$i]['chk_tip']=$myrow[4];
            //Получаем ответы
            $sql_answer = "SELECT  s_test_answer.id,
                            s_test_answer.name,
                            s_test_answer.chk_true,
                            s_test_answer.html_code,
                            (SELECT IF(COUNT(*)>0,GROUP_CONCAT(a_photo.img SEPARATOR '::'),'') FROM a_photo WHERE a_photo.a_menu_id='401' AND a_photo.row_id=s_test_answer.id ORDER BY a_photo.sid) AS img
                        
                            
        				FROM s_test_answer 
        					WHERE s_test_answer.s_test_quest_id='"._DB($myrow['id'])."'"; 
            $res_answer = mysql_query($sql_answer) or die(mysql_error());
            
            for ($myrow_answer = mysql_fetch_array($res_answer),$j=0; $myrow_answer==true; $myrow_answer = mysql_fetch_array($res_answer),$j++)
            {
                $data_[$i]['answer']['id'][$j]=$myrow_answer[0];
                $data_[$i]['answer']['name'][$j]=$myrow_answer[1];
                $data_[$i]['answer']['chk_true'][$j]=$myrow_answer[2];
                $data_[$i]['answer']['html_code'][$j]=$myrow_answer[3];
                $data_[$i]['answer']['img'][$j]=$myrow_answer[4];
            }
       
        }
        echo json_encode($data_);
        
    }
    
    //****************************************************************
    // Сохранение ответа
    if ($_t=='s_tests_answer_name_save'){
        $data_=array();
        $id=_GP('id');
        $name=_GP('name');
        
        if ($name==''){
            $sql_del = "DELETE 
            			FROM s_test_answer 
            				WHERE id='"._DB($id)."'
            ";
            $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        }else{
            $sql_upp = "UPDATE s_test_answer 
            			SET  
            				name='"._DB($name)."'
            		
            		WHERE id='"._DB($id)."'
            ";
            $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        }
        
        
        
        echo json_encode($data_);
        
    }
    //****************************************************************
    // Сохранение правильности
    if ($_t=='s_tests_answer_chk_true_save'){
        $data_=array();
        $id=_GP('id');
        $chk_true=_GP('chk_true');
        
            $sql_upp = "UPDATE s_test_answer 
            			SET  
            				chk_true='"._DB($chk_true)."'
            		
            		WHERE id='"._DB($id)."'
            ";
            $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        
        
        
        
        echo json_encode($data_);
        
    }
    //****************************************************************
    // Сохранение описания
    if ($_t=='s_tests_html_code_save'){
        $data_=array();
        $s_test_quest_id=_GP('s_test_quest_id');
        $html_code=_GP('html_code');
        
            $sql_upp = "UPDATE s_test_quest
            			SET  
            				html_code='"._DB($html_code)."'
            		
            		WHERE id='"._DB($s_test_quest_id)."'
            ";
            $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        
        
        echo json_encode($data_);
        
    }
    //****************************************************************
    // Сохранение типа вопроса
    if ($_t=='s_test_save_tip'){
        $data_=array();
        $s_test_quest_id=_GP('s_test_quest_id');
        $chk_tip=_GP('chk_tip');
        
        $sql_upp = "UPDATE s_test_quest
            			SET  
            				chk_tip='"._DB($chk_tip)."'
            		
            		WHERE id='"._DB($s_test_quest_id)."'
            ";
        $mt = microtime(true);
        $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
        
        echo json_encode($data_);
        
    }
    //****************************************************************
    // Удаление ответа
    if ($_t=='s_tests_res_answer_remove'){
        $data_=array();
        $id=_GP('id');
        
        
        $sql_del = "DELETE 
        			FROM a_photo 
        				WHERE a_photo.a_menu_id='401'
                        AND a_photo.row_id='"._DB($id)."'
        ";
       
        $mt = microtime(true);
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
         
        $sql_del = "DELETE 
        			FROM s_test_answer 
        				WHERE id='"._DB($id)."'
        ";
       
        $mt = microtime(true);
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
        
        
        
        echo json_encode($data_);
        
    }
    
    //****************************************************************
    // Удаление статистики пользователя по тесту
    if ($_t=='s_test_clear_all_results'){
        $data_=array();
        $f_n=_GP('f_n');
        if($f_n==''){echo 'Не определен f_n';exit;}
        $i_contr_id=_GP('i_contr_id');
        
        //ПОЛУЧАЕМ ID теста
        $sql_s_test="SELECT 
                    IF(COUNT(*)>0,s_test.id,'')
                        FROM s_test
                        WHERE s_test.s_struktura_id='"._DB($f_n)."'
                        ";
        $mt = microtime(true);
        $res_s_test = mysql_query($sql_s_test);if (!$res_s_test){echo $sql_s_test;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test;$data_['_sql']['time'][]=$mt;
        $row_s_test = mysql_fetch_array($res_s_test);
        $s_test_id=$row_s_test[0];
        if ($s_test_id==''){
            $sql_s_test_ins = "INSERT into s_test (
            				s_struktura_id
            			) VALUES (
            				'"._DB($f_n)."'
            )";
            $mt = microtime(true);
            $res_s_test_ins = mysql_query($sql_s_test_ins);if (!$res_s_test_ins){echo $sql_s_test_ins;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_ins;$data_['_sql']['time'][]=$mt;
            $s_test_id = mysql_insert_id();
        }
        
        
        $WHERE_="";
        if($i_contr_id!='-1'){
            $WHERE_=" AND s_test_i_contr.i_contr_id='"._DB($i_contr_id)."'";
        }
        
        //ПОЛУЧАЕМ ID s_test_i_contr
        $sql_s_test="SELECT 
                    IF(COUNT(*)>0,s_test_i_contr.id,'')
                        FROM s_test_i_contr
                        WHERE s_test_i_contr.s_test_id='"._DB($s_test_id)."'
                        $WHERE_
                        ";
        $mt = microtime(true);
        //echo $sql_s_test;
        $res_s_test = mysql_query($sql_s_test);if (!$res_s_test){echo $sql_s_test;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test;$data_['_sql']['time'][]=$mt;
        for ($myrow_s_test = mysql_fetch_array($res_s_test); $myrow_s_test==true; $myrow_s_test = mysql_fetch_array($res_s_test))
        {
            //print_rf($myrow_s_test);
            $sql_del = "DELETE 
            			FROM s_test_i_contr_s_test_quest_s_test_answer
            				WHERE s_test_i_contr_s_test_quest_s_test_answer.s_test_i_contr_s_test_quest_id IN
                                (SELECT s_test_i_contr_s_test_quest.id FROM s_test_i_contr_s_test_quest WHERE s_test_i_contr_s_test_quest.s_test_i_contr_id='"._DB($myrow_s_test[0])."')
            ";
           
            $mt = microtime(true);
            $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
            
            $sql_del = "DELETE 
            			FROM s_test_i_contr_s_test_quest
            				WHERE s_test_i_contr_s_test_quest.s_test_i_contr_id ='"._DB($myrow_s_test[0])."'
            ";
           //echo $sql_del;
            $mt = microtime(true);
            $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
            
            
        }
        
        //Удаляем информацию по тесту
        $sql_del = "DELETE 
        			FROM s_test_i_contr
        				WHERE s_test_i_contr.s_test_id='"._DB($s_test_id)."'
                        $WHERE_
        ";
       
        $mt = microtime(true);
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
        
        
        
        echo json_encode($data_);
        
    }
    

}
?>