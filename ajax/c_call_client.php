<?php

header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
ini_set('display_errors',1);
error_reporting(E_ALL);

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

//**************************************************************************************************
//Поиск звонков
if ($_t=='c_call_client__find'){
    $data_=array();
    $WHERE="";
    $HAVING="";
    $TABLE="";
    $ORDER='c_call_client.data_create DESC';
    $LIMIT="";
    
    
    //Дата
    $d1=_GP('d1');
    if ($d1!=''){
        $WHERE.=" AND c_call_client.data_create>='".date('Y-m-d H:i:s',strtotime($d1))."'";
    }
    $d2=_GP('d2');
    if ($d2!=''){
        $WHERE.=" AND c_call_client.data_create<='".date('Y-m-d H:i:s',strtotime($d2))."'";
    }
    
    
    $data_['a_menu_inc_arr']=array();
    $data_['a_col_col_arr']=array();
    $data_['tip_arr']=array();
    $data_['c_questions_id']=array();
    $data_['a_col_ru_arr']=array();
    
    ///ПОЛУЧАЕМ ЗНАЧЕНИЕ ПОЛЕЙ
    $sql = "SELECT      c_questions.id,
                        c_questions.a_col_id,
                        a_col.col_ru,
                        c_questions.comments,
                        c_questions.tip,
                        (SELECT IF(COUNT(*)>0,a_menu.inc,'') FROM a_menu WHERE a_col.a_menu_id=a_menu.id LIMIT 1) AS a_menu_inc,
                        a_col.col
                        
                        
    				FROM c_questions, a_col
    					WHERE c_questions.chk_active='1'
                        AND a_col.id=c_questions.a_col_id
                        ORDER BY c_questions.sid
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $SQL_COL_='';
    $col_arr=array();
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $a_col_id=$myrow[1];
        $data_['tip_arr'][$a_col_id]=$myrow[4];
        $data_['c_questions_id'][$a_col_id]=$myrow[0];
        $data_['a_menu_inc_arr'][$a_col_id]=$myrow[5];
        $data_['a_col_col_arr'][$a_col_id]=$myrow[6];
        $data_['a_col_ru_arr'][$a_col_id]=$myrow[2];
        
        $table_ =$data_['a_menu_inc_arr'][$a_col_id];
        $col_=$data_['a_col_col_arr'][$a_col_id];
        if ($data_['tip_arr'][$myrow[1]]=='Одно значение'){
           
           $SQL_COL_.=", (SELECT IF(COUNT(*)>0,IF(c_call_answer.id_z_p_p>0,
                                    (SELECT IF(COUNT(*)>0,`$table_`.`$col_`,'') FROM `$table_` WHERE c_call_answer.id_z_p_p=`$table_`.id LIMIT 1),
                                    c_call_answer.comments),'')
                				FROM c_call_answer 
                					WHERE c_call_answer.c_questions_id='"._DB($data_['c_questions_id'][$a_col_id])."' 
                					AND c_call_answer.c_call_client_id=c_call_client.id) AS ".$table_.'_'.$col_
                                    .", 
                     (SELECT IF(COUNT(*)>0,IF(c_call_answer.id_z_p_p>0,
                                    c_call_answer.id_z_p_p,
                                    '-'),'')
                				FROM c_call_answer 
                					WHERE c_call_answer.c_questions_id='"._DB($data_['c_questions_id'][$a_col_id])."' 
                					AND c_call_answer.c_call_client_id=c_call_client.id) AS ".$table_.'_'.$col_."_"
                     ;
        }else{
            
        
            $SQL_COL_.=", (SELECT 
                                    GROUP_CONCAT(DISTINCT `$table_`.`$col_` SEPARATOR '||') 
                				FROM c_call_answer, `$table_`
                					WHERE c_call_answer.c_questions_id='"._DB($data_['c_questions_id'][$a_col_id])."' 
                					AND c_call_answer.c_call_client_id=c_call_client.id
                                    AND c_call_answer.id_z_p_p=`$table_`.id                                    
                	) AS ".$table_.'_'.$col_."
                    , (SELECT 
                                    GROUP_CONCAT(DISTINCT `$table_`.`id` SEPARATOR '||') 
                				FROM c_call_answer, `$table_`
                					WHERE c_call_answer.c_questions_id='"._DB($data_['c_questions_id'][$a_col_id])."' 
                					AND c_call_answer.c_call_client_id=c_call_client.id
                                    AND c_call_answer.id_z_p_p=`$table_`.id                                    
                	)  AS ".$table_.'_'.$col_."_";
        }
        $col_arr[]=$table_.'_'.$col_;
    }
    
    
    //Догрузка
    $kol_load=100;
    $limit=_GP('limit');
        if ($limit!=''){
            $LIMIT=$limit.', '.$kol_load;
        }
    if ($LIMIT!=''){$LIMIT=' LIMIT '.$LIMIT;}else{$LIMIT=' LIMIT '.$kol_load;}
    
    //ФИЛЬТР
    //Поиск
    $txt=_GP('txt');
    if ($txt!=''){
        
        if (strstr($txt,' ')==true){
            $term_arr = explode(" ", $txt);
            $sql_having="";
            for($i=0;$i<count($term_arr);$i++)
            {
                if ($i>0) {$sql_having.=" AND ";}

                $sql_having.=" (
                        comments LIKE '%"._DB($txt)."%'  
                        OR a_admin_name LIKE '"._DB($term_arr[$i])."%' 
                        OR i_contr_phone LIKE '%"._DB($term_arr[$i])."%' 
                        OR i_contr_name LIKE '%"._DB($term_arr[$i])."%' ";
                foreach ($col_arr as $k => $col_){
                    $sql_having.=" OR ".$col_." LIKE '%"._DB($term_arr[$i])."%'";
                }
                $sql_having.= "
                        ) ";
            }
            if ($sql_having!='') {$HAVING.="  (".$sql_having.")";}
        }
        else {
            $term_arr=array();
            
            
            $HAVING.="  (comments LIKE '%"._DB($txt)."%'  
            OR a_admin_name LIKE '"._DB($txt)."%' 
            OR i_contr_phone LIKE '%"._DB($txt)."%'
            OR i_contr_name LIKE '%"._DB($txt)."%'";
            foreach ($col_arr as $k => $col_){
                $HAVING.=" OR ".$col_." LIKE '%"._DB($txt)."%'";
            }
            $HAVING.="
            )";
        }
       
    }
    if ($HAVING!=''){$HAVING=' HAVING '.$HAVING;}
    
     //Количество
     $data_['cnt_']=0;
    $sql = "SELECT  c_call_client.id,
                    (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE c_call_client.a_admin_id=a_admin.id LIMIT 1) AS a_admin_name,
                    (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE c_call_client.i_contr_id=i_contr.id LIMIT 1) AS i_contr_name,
                    (SELECT IF(COUNT(*)>0,i_contr.phone,'') FROM i_contr WHERE c_call_client.i_contr_id=i_contr.id LIMIT 1) AS i_contr_phone,
                    comments AS comments
                    $SQL_COL_
                    
                    
    				FROM `c_call_client` $TABLE
    					WHERE `c_call_client`.id>0
                        $WHERE
                        
                        GROUP BY c_call_client.id
                        
                        $HAVING
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['cnt_']++;
    }
    
    if ($ORDER!=''){$ORDER=' ORDER BY '.$ORDER;}
    $data_['i']=array();
    $data_['ai']=array();
    $data_['an']=array();
    $data_['ii']=array();
    $data_['in']=array();
    $data_['c']=array();
    $data_['zi']=array();
    //ОСНОВНОЙ SQL запрос
    $sql = "SELECT  c_call_client.id,
                    c_call_client.a_admin_id AS a_admin_id,
                    (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE c_call_client.a_admin_id=a_admin.id LIMIT 1) AS a_admin_name,
                    c_call_client.i_contr_id AS i_contr_id,
                    (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE c_call_client.i_contr_id=i_contr.id LIMIT 1) AS i_contr_name,
                    (SELECT IF(COUNT(*)>0,i_contr.phone,'') FROM i_contr WHERE c_call_client.i_contr_id=i_contr.id LIMIT 1) AS i_contr_phone,
                    
                    c_call_client.comments AS comments,
                    IF(c_call_client.m_zakaz_id>0,c_call_client.m_zakaz_id,'') AS m_zakaz_id,
                    c_call_client.data_create
                    $SQL_COL_
                    
    				FROM `c_call_client` $TABLE
    					WHERE `c_call_client`.id>0
                        $WHERE
                        
                        $HAVING
                        
                        $ORDER
                        $LIMIT
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['i'][$i]=$myrow['id'];
        $data_['ai'][$i]=$myrow['a_admin_id'];
        $data_['zi'][$i]=$myrow['m_zakaz_id'];
        $data_['an'][$i]=$myrow['a_admin_name'];
        if (strstr($myrow['a_admin_name'],' ')==true){
            $a_admin_name=explode(' ',$myrow['a_admin_name']);
            if (isset($a_admin_name[0])){
                $data_['an'][$i]=$a_admin_name[0];
            }
        }
        
        
        
        $data_['ii'][$i]=$myrow['i_contr_id'];
        $data_['in'][$i]=$myrow['i_contr_name'];
        $data_['ip'][$i]=conv_('phone_from_db',$myrow['i_contr_phone']);
        if ($data_['ip'][$i]==$data_['in'][$i]){$data_['in'][$i]='';}
        $data_['c'][$i]=$myrow['comments'];
        $data_['dd'][$i]=date('d.m.Y H:i',strtotime($myrow['data_create']));
        $data_['col'][$i]['val']=array();
        
        foreach($data_['tip_arr'] as $a_col_id => $tip_){
            $table_=$data_['a_menu_inc_arr'][$a_col_id];
            $col_=$data_['a_col_col_arr'][$a_col_id];
            //echo '$a_col_id='.$a_col_id.'. $tip_='.$tip_.'<br />';
            $txt='';
            if (isset($myrow[$table_.'_'.$col_])){
                $txt=$myrow[$table_.'_'.$col_];
            }
            
            $data_['col'][$i]['val'][$a_col_id]=$txt;
            
            
            $txt_='';
            if (isset($myrow[$table_.'_'.$col_.'_'])){
                $txt_=$myrow[$table_.'_'.$col_.'_'];
            }
            
            $data_['col'][$i]['val_'][$a_col_id]=$txt_;
        }
        
       
        
    }
    
    echo json_encode($data_);
}
//**************************************************************************************************
//Удаление звонка
if ($_t=='c_call_client_remove'){
    $nomer=_GP('nomer');
    
    // Удаляем данные, связанные со звонком. 
    $sql_del = "DELETE 
    			FROM c_call_answer 
    				WHERE c_call_client_id='"._DB($nomer)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql_del) or die(mysql_error().'<br />'.$sql_del);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
    
    // Удаляем звонок
    $sql_del = "DELETE 
    			FROM c_call_client 
    				WHERE id='"._DB($nomer)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql_del) or die(mysql_error().'<br />'.$sql_del);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
    
    echo json_encode($data_);
}
//**************************************************************************************************
//Сохранение звонка
if ($_t=='c_call_client_save'){
    $data_=array();
    
    $data_['i_contr_phone']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_phone'));
        if ($data_['i_contr_phone']-0==0){echo 'Телефон не может быть пустым!';exit;}
    $data_['i_contr_name']=_GP('i_contr_name');
        if ($data_['i_contr_name']==''){echo 'Имя клиента не может быть пустым!';exit;}
    $data_['comments']=_GP('comments');
    
    
    $data_['col_arr']=array();
    $data_['a_menu_inc_arr']=array();
    $data_['a_col_col_arr']=array();
    $data_['tip_arr']=array();
    $data_['c_questions_id']=array();
    
    ///ПОЛУЧАЕМ ЗНАЧЕНИЕ ПОЛЕЙ
    $sql = "SELECT      c_questions.id,
                        c_questions.a_col_id,
                        a_col.col_ru,
                        c_questions.comments,
                        c_questions.tip,
                        (SELECT IF(COUNT(*)>0,a_menu.inc,'') FROM a_menu WHERE a_col.a_menu_id=a_menu.id LIMIT 1) AS a_menu_inc,
                        a_col.col
                        
                        
    				FROM c_questions, a_col
    					WHERE c_questions.chk_active='1'
                        AND a_col.id=c_questions.a_col_id
                        ORDER BY c_questions.sid
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['tip_arr'][$myrow[1]]=$myrow[4];
        $data_['c_questions_id'][$myrow[1]]=$myrow[0];
        $data_['a_menu_inc_arr'][$myrow[1]]=$myrow[5];
        $data_['a_col_col_arr'][$myrow[1]]=$myrow[6];
        

        $val_=_GP('c_questions_id_'.$myrow[1].'');
        if ($val_!=''){
            if (is_array($val_)){
                $data_['col_arr'][$myrow[1]]=$val_;
            }
            else{
                $data_['col_arr'][$myrow[1]][0]=$val_;
            }
        }else{
            $data_['col_arr'][$myrow[1]]='';
        }
        
       
    }
    //print_rf($data_);exit;
    
    //Реклама
    $sql = "SELECT IF(COUNT(*)>0,i_reklama.id,'')
    				FROM i_reklama 
    					WHERE i_reklama.name='Звонок в call-центр' 
                        LIMIT 1
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $i_reklama_id=$myrow[0];
    if ($i_reklama_id==''){
        $sql_insert = "INSERT into i_reklama (
        				chk_active,
        				name
        			) VALUES (
        				'1',
        				'Звонок в call-центр'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
        $i_reklama_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
        
    }
        
        
    //Клиент
    $sql = "SELECT IF(COUNT(*)>0,i_contr.id,''), IF(COUNT(*)>0,i_contr.name,'')
    				FROM i_contr 
    					WHERE i_contr.phone='"._DB($data_['i_contr_phone'])."' 
    					LIMIT 1
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['i_contr_id']=$myrow[0];
    $data_['i_contr_name_old']=$myrow[1];
    
    if ($data_['i_contr_id']==''){//Новый клиент
        
        ///Добавляем клиента, если его нет в базе
        $sql_insert = "INSERT into i_contr (
                        chk_active,
        				name,
        				phone,
                        data_change,
                        i_reklama_id,
                        password
                        
        			) VALUES (
                        '1',
        				'"._DB($data_['i_contr_name'])."',
        				'"._DB($data_['i_contr_phone'])."',
                        '".date('Y-m-d H:i:s')."',
                        '"._DB($i_reklama_id)."',
                        '".md5(rand(10000,99999999))."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
        $data_['i_contr_id'] = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
        
    }else{
        if ($data_['i_contr_name_old']!=$data_['i_contr_name']){
            echo 'Клиент с таким номером в базе уже есть, но с другим именем: '.$data_['i_contr_name_old'];exit();
        }
    }
    

    
    //Добавляем информацию о звонке в БД
    $sql_insert = "INSERT into c_call_client (
    				a_admin_id,
    				i_contr_id,
                    comments
    			) VALUES (
    				'"._DB($a_admin_id_cur)."',
    				'"._DB($data_['i_contr_id'])."',
                    '"._DB($data_['comments'])."'
                    
    )";
    
    $mt = microtime(true);
    $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
    $c_call_client_id = mysql_insert_id();
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
    
    //Проверяем ответы на вопросы - если есть в базе - вводим их id
    foreach($data_['a_menu_inc_arr'] as $a_col_id => $table){
        $col_=$data_['a_col_col_arr'][$a_col_id];
        $tip_=$data_['tip_arr'][$a_col_id];
        $c_questions_id=$data_['c_questions_id'][$a_col_id];
        $val_arr=$data_['col_arr'][$a_col_id];
            if ($tip_=='Одно значение'){
                if (is_array($val_arr)){
                foreach($val_arr as $key => $val_){
                    
                   
                    $sql = "SELECT IF(COUNT(*)>0,`$table`.id,'')
                    				FROM `$table`
                    					WHERE `$table`.`$col_`='"._DB($val_)."' 
                    					
                    	"; 
                    
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $myrow = mysql_fetch_array($res);
                    //print_rf($myrow);
                    //echo '<br />'.$c_questions_id.'<br />'.$c_call_client_id.'<br /><hr />';
                    $id_z_p_p=$myrow[0];
                    if ($id_z_p_p==''){//добавляем в комментарии
                        $sql_insert = "INSERT into c_call_answer (
                        				c_call_client_id,
                        				c_questions_id,
                                        id_z_p_p,
                                        comments
                        			) VALUES (
                        				'"._DB($c_call_client_id)."',
                        				'"._DB($c_questions_id)."',
                                        '0',
                                        '"._DB($val_)."'
                        )";
                        
                        $mt = microtime(true);
                        $res_insert = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                        $new_id = mysql_insert_id();
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                        
                    }else{
                        $sql_insert = "INSERT into c_call_answer (
                        				c_call_client_id,
                        				c_questions_id,
                                        id_z_p_p,
                                        comments
                        			) VALUES (
                        				'"._DB($c_call_client_id)."',
                        				'"._DB($c_questions_id)."',
                                        '"._DB($id_z_p_p)."',
                                        ''
                        )";
                        
                        $mt = microtime(true);
                        $res_insert = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                        $new_id = mysql_insert_id();
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                        
                    }
                }
                }
                else{//значение не указано
                    
                }
            }
            else{//если несколько значений 
            
                if (is_array($data_['col_arr'][$a_col_id])){
                    foreach($data_['col_arr'][$a_col_id] as $key2 => $id_z_p_p){
                        $sql_insert = "INSERT into c_call_answer (
                            				c_call_client_id,
                            				c_questions_id,
                                            id_z_p_p,
                                            comments
                            			) VALUES (
                            				'"._DB($c_call_client_id)."',
                            				'"._DB($c_questions_id)."',
                                            '"._DB($id_z_p_p)."',
                                            ''
                            )";
                            
                            $mt = microtime(true);
                            $res_insert = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                            $new_id = mysql_insert_id();
                            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                            
                    }
                }
            }
        
        
        
    }
    
    
    echo json_encode($data_);
}
//автозаполнение покупателей
if ($_t=='autocomplete_i_contr_phone'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    
    $data_=array();
    $data_['items']=array();
    $term=preg_replace('/[\D]{1,}/s', '',_GP('term'));
    $dt_=_GP('dt');

    $sql_i_contr = "SELECT i_contr.id, i_contr.name, i_contr.phone, i_contr.email
    				FROM i_contr 
    					WHERE i_contr.phone LIKE '%$term%'
                        LIMIT 20
    ";
     
    $mt = microtime(true); 
    $res_i_contr = mysql_query($sql_i_contr);if (!$res_i_contr){echo $sql_i_contr;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_contr;$data_['_sql']['time'][]=$mt;
    
    for ($myrow_i_contr = mysql_fetch_array($res_i_contr),$i=0; $myrow_i_contr==true; $myrow_i_contr = mysql_fetch_array($res_i_contr),$i++)
    {
        $data_['items'][$i]['name']=$myrow_i_contr[1];
        $data_['items'][$i]['text']=$myrow_i_contr[1].' '. conv_('phone_from_db',$myrow_i_contr[2]);
        $data_['items'][$i]['phone']=conv_('phone_from_db',$myrow_i_contr[2]);
        $data_['items'][$i]['id']=$myrow_i_contr[0];
        
        // Заказы
        $data_['items'][$i]['r_status']=array();
        $data_['items'][$i]['m_zakaz_id']=array();
        $data_['items'][$i]['r_diagnoz']=array();
        
        $sql_m_zakaz = "SELECT 
                        m_zakaz.id, 
                        m_zakaz.status,
                        (SELECT IF(COUNT(*)>0,r_service.status,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS status_r_service,
                        (SELECT IF(COUNT(*)>0,r_service.diagnoz,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS diagnoz
                        
                        
        				FROM m_zakaz 
        					WHERE m_zakaz.i_contr_id = '"._DB($myrow_i_contr[0])."' 
        						ORDER BY m_zakaz.data DESC
        ";
         
        $mt = microtime(true);
        $res_m_zakaz = mysql_query($sql_m_zakaz) or die(mysql_error().'<br />'.$sql_m_zakaz);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_zakaz;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$j=0; $myrow_m_zakaz==true; $myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$j++)
        {
            $data_['items'][$i]['r_status'][$j]=$myrow_m_zakaz['status_r_service'];
            $data_['items'][$i]['m_zakaz_id'][$j]=$myrow_m_zakaz['id'];
            $data_['items'][$i]['r_diagnoz'][$j]=$myrow_m_zakaz['diagnoz'];
        }
        
    }
    
    echo json_encode($data_);
}
//автозаполнение покупателей
if ($_t=='autocomplete_i_contr_name'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    
    $data_=array();
    $data_['items']=array();
    
    $term=_GP('term');
    $dt_=_GP('dt');

    $sql_i_contr = "SELECT i_contr.id, i_contr.name, i_contr.phone, i_contr.email
    				FROM i_contr 
    					WHERE i_contr.name LIKE '%$term%'
                        LIMIT 20
    ";
     
    $mt = microtime(true);
    $res_i_contr = mysql_query($sql_i_contr);if (!$res_i_contr){echo $sql_i_contr;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_contr;$data_['_sql']['time'][]=$mt;
    
    for ($myrow_i_contr = mysql_fetch_array($res_i_contr),$i=0; $myrow_i_contr==true; $myrow_i_contr = mysql_fetch_array($res_i_contr),$i++)
    {
        $data_['items'][$i]['name']=$myrow_i_contr[1];
        $data_['items'][$i]['text']=$myrow_i_contr[1].' '. conv_('phone_from_db',$myrow_i_contr[2]);
        $data_['items'][$i]['phone']=conv_('phone_from_db',$myrow_i_contr[2]);
        $data_['items'][$i]['id']=$myrow_i_contr[0];
        
        // Заказы
        $data_['items'][$i]['r_status']=array();
        $data_['items'][$i]['m_zakaz_id']=array();
        $data_['items'][$i]['r_diagnoz']=array();
        
        $sql_m_zakaz = "SELECT 
                        m_zakaz.id, 
                        m_zakaz.status,
                        (SELECT IF(COUNT(*)>0,r_service.status,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS status_r_service,
                        (SELECT IF(COUNT(*)>0,r_service.diagnoz,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS diagnoz
                        
                        
        				FROM m_zakaz 
        					WHERE m_zakaz.i_contr_id = '"._DB($myrow_i_contr[0])."' 
        						ORDER BY m_zakaz.data DESC
        ";
         
        $mt = microtime(true);
        $res_m_zakaz = mysql_query($sql_m_zakaz) or die(mysql_error().'<br />'.$sql_m_zakaz);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_zakaz;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$j=0; $myrow_m_zakaz==true; $myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$j++)
        {
            $data_['items'][$i]['r_status'][$j]=$myrow_m_zakaz['status_r_service'];
            $data_['items'][$i]['m_zakaz_id'][$j]=$myrow_m_zakaz['id'];
            $data_['items'][$i]['r_diagnoz'][$j]=$myrow_m_zakaz['diagnoz'];
        }
        
    }
    
    echo json_encode($data_);
}
//автозаполнение полей
if ($_t=='autocomplete_input'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $data_=array();
    
    $term=_GP('term');
    $col_id=_GP('_col_id');
    $dt_=_GP('dt');
    
    
    $r_tip_oborud_name=@$dt_['c_questions_id_351'];//ТИП ОБОРУДОВАНИЯ
    $r_brend_name=@$dt_['c_questions_id_354']; // БРЕНД ОБОРУДОВАНИЯ
    $r_model_name=@$dt_['c_questions_id_359']; // МОДЕЛЬ ОБОРУДОВАНИЯ
    $r_neispravnosti_arr=@$dt_['c_questions_id_901']; // НЕИСПРАВНОСТИ

    
    
    if ($col_id!=''){
        
        $sql = "SELECT a_col.col, a_menu.inc
        				FROM a_menu, a_col 
        					WHERE a_col.id='"._DB($col_id)."' 
        					AND a_col.a_menu_id=a_menu.id
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error());
        $mt = microtime(true)-$mt;// $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
        $myrow = mysql_fetch_array($res);
        $col=$myrow[0];
        $inc=$myrow[1];
    }else{
        $col=_GP('_col');
        $inc=_GP('_inc');
    }
    if ($col=='' or $inc==''){echo 'Не определены переменные $col="'.$col.'", $inc="'.$inc.'"';exit;}
    
    $GROUP=" GROUP BY `"._DB($inc)."`.`"._DB($col)."`";
    $SEL_="";
    $TBL="";
    $WHERE="WHERE `"._DB($inc)."`.`"._DB($col)."` LIKE '%"._DB($term)."%'";
    $ORDER="ORDER BY `"._DB($inc)."`.`"._DB($col)."` LIKE '"._DB($term)."%' DESC";
    
    //Тип
    if ($col_id=='351'){ // для товаров и услуг
        $SEL_=", (SELECT COUNT(r_service.id) FROM r_model, r_service WHERE r_model.r_tip_oborud_id=r_tip_oborud.id AND r_service.r_model_id=r_model.id) AS cnt_ ";
        $ORDER=" ORDER BY cnt_ DESC";
    }
    //Бренд
    if ($col_id=='354'){ // для товаров
        
        $SEL_=", (SELECT COUNT(r_service.id) 
            FROM r_model, r_service, r_tip_oborud 
            WHERE r_model.r_tip_oborud_id=r_tip_oborud.id 
            AND r_tip_oborud.name='"._DB($r_tip_oborud_name)."' 
            AND r_model.r_brend_id=r_brend.id 
            AND r_service.r_model_id=r_model.id) AS cnt_ ";
        $ORDER=" ORDER BY cnt_ DESC";
    }
    //Неисправности
    if ($col_id=='901'){// для  услуг
        
        
        //Получаем id типа
        $sql = "SELECT r_tip_oborud.id
        				FROM r_tip_oborud 
        					WHERE r_tip_oborud.name='"._DB($r_tip_oborud_name)."' 
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql);if (!$res){echo $sql;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $r_tip_oborud_id=$myrow[0];
        
        $TBL="";
        $WHERE.="  AND r_neispravnosti.chk_active='1'";
        
        $SEL_=", (SELECT COUNT(r_service.id) 
            FROM r_model, r_service, r_tip_oborud , r_service_r_neispravnosti, r_brend
            WHERE r_model.r_tip_oborud_id=r_tip_oborud.id 
            AND r_tip_oborud.name='"._DB($r_tip_oborud_name)."' 
            AND r_model.r_brend_id=r_brend.id
            AND r_brend.name='"._DB($r_brend_name)."'
            AND r_service.r_model_id=r_model.id
            AND r_service_r_neispravnosti.id1=r_service.id
            AND r_service_r_neispravnosti.id2=r_neispravnosti.id) AS cnt_ ";
            
        $ORDER=" ORDER BY FIELD(`r_tip_oborud_id`,'"._DB($r_tip_oborud_id)."') DESC,  cnt_ DESC";
    }
    //Модель
    if ($col_id=='359'){//для товара
        
        if ($r_tip_oborud_name!='' and $r_brend_name!=''){
        $TBL=", r_tip_oborud, r_brend";
        $WHERE.="   AND r_model.r_tip_oborud_id=r_tip_oborud.id 
                    AND r_tip_oborud.name='"._DB($r_tip_oborud_name)."'
                    AND r_model.r_brend_id=r_brend.id 
                    AND r_brend.name='"._DB($r_brend_name)."'
                    ";
        
        $SEL_=", (SELECT COUNT(r_service.id) 
            FROM r_model, r_service, r_tip_oborud, r_brend
            WHERE r_model.r_tip_oborud_id=r_tip_oborud.id 
            AND r_tip_oborud.name='"._DB($r_tip_oborud_name)."' 
            AND r_model.r_brend_id=r_brend.id 
            AND r_brend.name='"._DB($r_brend_name)."'
            AND r_service.r_model_id=r_model.id) AS cnt_ ";
        $ORDER=" ORDER BY cnt_ DESC, pop DESC";
        }
    }
    
    $data_['items']=array();
    
    $sql_connect = "SELECT `"._DB($inc)."`.id, `"._DB($inc)."`.`"._DB($col)."` $SEL_
    				FROM `"._DB($inc)."` $TBL
                        $WHERE
                        $GROUP
    					$ORDER
                       LIMIT 50
    "; 
    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect[1];
        $data_['items'][$i]['text']=$myrow_connect[1];
        $data_['items'][$i]['id']=$myrow_connect[0];
    }

    
    
    
    
    //echo $sql_connect;exit;
    echo json_encode($data_);
}



//***********************************************************************************************
//***********************************************************************************************
//***********************************************************************************************
//Выводим товары и услуги
if ($_t=='items_and_work_add'){
    $data_=array();
    

    //Данные для выбора услуг
    $SQL_TBL_WORK="";
    $SQL_SEL_WORK="";
    $SQL_WHERE_WORK="";
    $SQL_ORDER_WORK="ORDER BY RAND()";
    $SQL_GROUP_WORK=" GROUP BY s_cat.id";
    
    //Данные для выбора товара
    $SQL_TBL_ITEM="";
    $SQL_SEL_ITEM="";
    $SQL_WHERE_ITEM="";
    $SQL_ORDER_ITEM="ORDER BY s_cat.pop DESC";
    $SQL_GROUP_ITEM="";
    
    
    // ОСОБЫЕ УСЛОВИЯ
    $r_tip_oborud_name=@$_REQUEST['c_questions_id_351'];//ТИП ОБОРУДОВАНИЯ
        if ($r_tip_oborud_name!=''){
            //if ($SQL_WHERE_ITEM!=''){$SQL_WHERE_ITEM.=" AND ";}
            $SQL_WHERE_ITEM.=" AND (s_cat.name LIKE '%"._DB($r_tip_oborud_name)."%' OR  s_cat.html_code LIKE '%"._DB($r_tip_oborud_name)."%')";
            
            $SQL_SEL_WORK=", COUNT(r_service.id) AS cnt_";
            $SQL_TBL_WORK.=", m_zakaz, m_zakaz_s_cat, r_service, r_model, r_tip_oborud";
            $SQL_WHERE_WORK.="  AND m_zakaz_s_cat.s_cat_id=s_cat.id
                                AND m_zakaz_s_cat.m_zakaz_id=r_service.m_zakaz_id
                                AND r_service.r_model_id=r_model.id
                                AND r_model.r_tip_oborud_id=r_tip_oborud.id
                                AND r_tip_oborud.name='"._DB($r_tip_oborud_name)."'
                ";
            $SQL_ORDER_WORK=" ORDER BY cnt_ DESC";
        }
    $r_brend_name=@$_REQUEST['c_questions_id_354']; // БРЕНД ОБОРУДОВАНИЯ
        if ($r_brend_name!=''){
            //if ($SQL_WHERE_ITEM!=''){$SQL_WHERE_ITEM.=" AND ";}
            $SQL_WHERE_ITEM.=" AND (s_cat.name LIKE '%"._DB($r_brend_name)."%' OR  s_cat.html_code LIKE '%"._DB($r_brend_name)."%')";
        }
    $r_model_name=@$_REQUEST['c_questions_id_359']; // МОДЕЛЬ ОБОРУДОВАНИЯ
        if ($r_model_name!=''){
            //if ($SQL_WHERE_ITEM!=''){$SQL_WHERE_ITEM.=" AND ";}
            $SQL_WHERE_ITEM.=" AND (s_cat.name LIKE '%"._DB($r_model_name)."%' OR  s_cat.html_code LIKE '%"._DB($r_model_name)."%')";
        }
    
    //неисправности
    if (isset($_REQUEST['c_questions_id_901'])){
        $r_neispravnosti_arr=array();
        if (is_array($_REQUEST['c_questions_id_901'])){
            $r_neispravnosti_arr=$_REQUEST['c_questions_id_901'];
        }
        else{
            if ($_REQUEST['c_questions_id_901']!=''){
                $r_neispravnosti_arr[]=$_REQUEST['c_questions_id_901'];
            }
        }
    
        if (count($r_neispravnosti_arr)>0){
            if (strstr($SQL_TBL_WORK,'m_zakaz_s_cat')!=true){
                $SQL_TBL_WORK.=", m_zakaz, m_zakaz_s_cat, r_service";
                $SQL_WHERE_WORK.="AND m_zakaz_s_cat.s_cat_id=s_cat.id
                                AND m_zakaz_s_cat.m_zakaz_id=r_service.m_zakaz_id";
            }
                $SQL_TBL_WORK.=", r_service_r_neispravnosti";
                $SQL_WHERE_WORK.="  AND r_service_r_neispravnosti.id1=r_service.id
                                AND r_service_r_neispravnosti.id2 IN ('".implode("','",$r_neispravnosti_arr)."')";
            
        }
    }
    
    //ПОЛУЧАЕМ ТОВАРЫ
    $sql = "SELECT s_cat.id,
                    s_cat.name,
                    s_cat.price
                     $SQL_SEL_ITEM
                     
    				FROM s_cat $SQL_TBL_ITEM
    					WHERE s_cat.chk_active='1'
                        AND s_cat.tip='Товар'
                        $SQL_WHERE_ITEM
                        $SQL_GROUP_ITEM
                        $SQL_ORDER_ITEM
                        LIMIT 5
                        
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['i']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        
            $data_['i']['n'][$i]=$myrow['name'];
            $data_['i']['p'][$i]=$myrow['price'];
            $data_['i']['i'][$i]=$myrow['id'];
       
    }
    //ПОЛУЧАЕМ УСЛУГИ
    $sql = "SELECT s_cat.id,
                    s_cat.name,
                    s_cat.price
                     $SQL_SEL_WORK
    				FROM s_cat $SQL_TBL_WORK
    					WHERE s_cat.chk_active='1'
                        AND s_cat.tip='Услуга'
                        $SQL_WHERE_WORK
                        $SQL_GROUP_WORK
                        $SQL_ORDER_WORK
                        LIMIT 5
                        
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['w']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
            $data_['w']['n'][$i]=$myrow['name'];
            $data_['w']['p'][$i]=$myrow['price'];
            $data_['w']['i'][$i]=$myrow['id'];
            
    }
    echo json_encode($data_);
}

//***********************************************************************************************
//***********************************************************************************************
//***********************************************************************************************
//Синхронизируем звонки
if ($_t=='google_drive_syn_calls'){
    $data_=array();
    $data_['err']='';
    $d_=_GP('d');
        if ($d_!=''){$d_=date('Y-m-d H:i:s',strtotime($d_));}
    
    //Получаем массив уже существующих записей
    $sql = "SELECT c_call_client.id,c_call_client.data_create, i_contr.phone
    				FROM c_call_client, i_contr
    					WHERE c_call_client.i_contr_id=i_contr.id
                        AND c_call_client.data_create >'"._DB($d_)."' 
    						
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $c_call_data_arr=array();
    $c_call_phone_arr=array();
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $c_call_data_arr[$myrow[0]]=$myrow[1];
        $c_call_phone_arr[$myrow[0]]=$myrow[2];
    }    

    //Реклама
    $sql = "SELECT IF(COUNT(*)>0,i_reklama.id,'')
    				FROM i_reklama 
    					WHERE i_reklama.name='Звонок в call-центр' 
                        LIMIT 1
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $i_reklama_id=$myrow[0];
    if ($i_reklama_id==''){
        $sql_insert = "INSERT into i_reklama (
        				chk_active,
        				name
        			) VALUES (
        				'1',
        				'Звонок в call-центр'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
        $i_reklama_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
        
    }
                                
        
    require_once '../class/Google/vendor/autoload.php';
    $client = new Google_Client();
    
    $err_google='';
    if (!isset($_SESSION['a_options']['Google Client Secret - для Google Drive']) or $_SESSION['a_options']['Google Client Secret - для Google Drive']==''){
     $err_google.='<p>Отсутствует параметр <strong>Google Client Secret - для Google Drive</strong></p>';  
    }
    if (!isset($_SESSION['a_options']['Google Redirect Uri - для Google Drive']) or $_SESSION['a_options']['Google Redirect Uri - для Google Drive']==''){
     $err_google.='<p>Отсутствует параметр <strong>Google Redirect Uri - для Google Drive</strong></p>';  
    }
     if ($err_google==''){
         $client->setClientId($_SESSION['a_options']['Google Client Secret - для Google Drive']);
         $client->setClientSecret($_SESSION['a_options']['Google Redirect Uri - для Google Drive']);
         $client->setRedirectUri('http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=c_call_client');
         $client->setScopes(array('https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive.metadata.readonly https://www.googleapis.com/auth/drive.appdata https://www.googleapis.com/auth/drive.readonly https://www.googleapis.com/auth/drive.apps.readonly https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.me https://docs.google.com/feeds/ https://docs.googleusercontent.com/ https://spreadsheets.google.com/feeds/'));
         
         //Если есть связь с google drive
         if ((isset($_SESSION['google_drive_access_token']) && $_SESSION['google_drive_access_token'])) {
            $client->setAccessToken($_SESSION['google_drive_access_token']);
            //
            
            $service = new Google_Service_Drive($client);
           
            $a=retrieveAllFiles($service);
            if (isset($a['err']) and $a['err']=='An error occurred'){
                unset($_SESSION['google_drive_access_token']);
                $data_['err'].='no connect google drive';
            }
            if ($data_['err']==''){
                $data_['k']=array();
                $cnt_=0;
                foreach($a as $key => $arr){
                   
                   if (mb_strstr($arr['name'],'.3gp',false,'utf-8')==true or mb_strstr($arr['name'],'.ogg',false,'utf-8')==true){
                   
                        $data_['k'][$key]=array();
                        $err='';
                        
                        $data_['k'][$key]['id']=$arr['id'];
                        $data_['k'][$key]['name']=$arr['name'];//2017_04_01_13_33_04_89264008493_89264008493.3gp
                            $data_['k'][$key]['name']=str_replace(array(' ','.3gp','.ogg'),'',$data_['k'][$key]['name']);
                        $data_['k'][$key]['date']=mb_substr($data_['k'][$key]['name'],0,4,'UTF-8')
                                    .'-'.mb_substr($data_['k'][$key]['name'],5,2,'UTF-8')
                                    .'-'.mb_substr($data_['k'][$key]['name'],8,2,'UTF-8')
                                    .' '.mb_substr($data_['k'][$key]['name'],11,2,'UTF-8')
                                    .':'.mb_substr($data_['k'][$key]['name'],14,2,'UTF-8')
                                    .':'.mb_substr($data_['k'][$key]['name'],17,2,'UTF-8');
                        $data_['k'][$key]['phone']=mb_substr($data_['k'][$key]['name'],mb_strlen($data_['k'][$key]['name'],'UTF-8')-10,10,'UTF-8');
                        if ($data_['k'][$key]['phone']!=''){$data_['k'][$key]['phone']='8'.$data_['k'][$key]['phone'];}
                        
                        
                        if ($data_['k'][$key]['date']!=date('Y-m-d H:i:s',strtotime($data_['k'][$key]['date']))){
                            $err.= '<p>Не верный формат даты ('.$data_['k'][$key]['date'].' в имени файла <strong>'.$arr['name'].'</strong>): укажите при сохранении записи дату вначале</p>';
                        }
                        
                        $ph_int=$data_['k'][$key]['phone']-0;
                        if ($data_['k'][$key]['phone']!=($ph_int).''){
                            $err.= '<p>Не верный формат телефона ('.$data_['k'][$key]['phone'].' в имени файла <strong>'.$arr['name'].'</strong>): укажите при сохранении записи телефон вконце имени файла</p>';
                        }
                        $data_['err'].=$err;
                        
                        
                        //Добавление записей в базу данных
                        $add_ok=1;
                        
                        
                    
                    
                        if ($err==''){
                            
                            //Прверяем на вхождение во временой интервал 
                            if ($d_!=''){
                                if (strtotime($data_['k'][$key]['date'])<strtotime($d_)){
                                    $add_ok=0;
                                    //echo $data_['k'][$key]['date'].'+'.$d_.'+<br />';
                                    
                                }
                            }
                          
                            //Проверяем на наличие в базе
                            if (in_array($data_['k'][$key]['date'],$c_call_data_arr)){
                                $k = array_search($data_['k'][$key]['date'], $c_call_data_arr);
                                if ($c_call_phone_arr[$k]==$data_['k'][$key]['phone']){
                                    $add_ok=0;
                                   // echo $c_call_phone_arr[$k].'+'.$data_['k'][$key]['phone'].'+<br />';
                                }
                            }
                           //echo '+'.$add_ok.'+<br />';
                            //Добаляем в базу
                            if ($add_ok==1){
                                
                                //Клиент
                                $sql = "SELECT IF(COUNT(*)>0,i_contr.id,'')
                                				FROM i_contr 
                                					WHERE i_contr.phone='"._DB($data_['k'][$key]['phone'])."' 
                                					LIMIT 1
                                	"; 
                                
                                $mt = microtime(true);
                                $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                $myrow = mysql_fetch_array($res);
                                $data_['i_contr_id']=$myrow[0];
                                
                                if ($data_['i_contr_id']==''){//Новый клиент
                                    
                                    ///Добавляем клиента, если его нет в базе
                                    $sql_insert = "INSERT into i_contr (
                                                    chk_active,
                                    				name,
                                    				phone,
                                                    data_change,
                                                    i_reklama_id,
                                                    password
                                                    
                                    			) VALUES (
                                                    '1',
                                    				'"._DB($data_['k'][$key]['phone'])."',
                                    				'"._DB($data_['k'][$key]['phone'])."',
                                                    '".date('Y-m-d H:i:s')."',
                                                    '"._DB($i_reklama_id)."',
                                                    '".md5(rand(10000,99999999))."'
                                    )";
                                    
                                    $mt = microtime(true);
                                    $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                                    $data_['i_contr_id'] = mysql_insert_id();
                                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                                    
                                }
                                
                                
                                //Добавляем информацию о звонке в БД
                                $sql_insert = "INSERT into c_call_client (
                                				a_admin_id,
                                				i_contr_id,
                                                comments,
                                                data_create
                                                
                                			) VALUES (
                                				'"._DB($a_admin_id_cur)."',
                                				'"._DB($data_['i_contr_id'])."',
                                                '',
                                                '"._DB($data_['k'][$key]['date'])."'
                                                
                                )";
                                
                                $mt = microtime(true);
                                $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                                $c_call_client_id = mysql_insert_id();
                                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                                
                                $data_['n'][$key]['date']=$data_['k'][$key]['date'];
                                $data_['n'][$key]['phone']=$data_['k'][$key]['phone'];
                                
                                $cnt_++;
                            }
                            
                            
                        }
                        
                        
                    }
                }
                $data_['cnt_']=$cnt_;
                
                $sql_upp = "
                		UPDATE a_options 
                			SET  
                				val='".date('Y-m-d H:i:s')."'
                		
                		WHERE name='Google Drive - дата последней синхронизации звонков'
                ";
                $res = mysql_query($sql_upp) or die(mysql_error().'<br />'.$sql_upp);
                
            }
            
         }
         else{
            echo 'no data $_SESSION[google_drive_access_token]';
            exit;
         }
         
       
     }
    echo json_encode($data_);
}

//***********************************************************************************************
//***********************************************************************************************
//***********************************************************************************************
//Быстрое Сохранение
if ($_t=='c_call_quick_change'){
    $data_=array();
    $data_['cl_']=_GP('cl_');
    $data_['col']=_GP('col');
    $data_['id_']=_GP('id_');
    $data_['inc']=_GP('inc');
    $data_['val_id']=_GP('val_id');
    
    //Комментарии
    if ($data_['cl_']=='c_call_client__comments'){
        
        $sql_upp = "
        		UPDATE c_call_client 
        			SET  
        				comments='"._DB($data_['val_id'])."',
                        data_change=NOW(),
                        a_admin_id_change='"._DB($a_admin_id_cur)."'
        		
        		WHERE id='"._DB($data_['id_'])."'
        ";

        $mt = microtime(true);
        $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br />'.$sql_upp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;

    }
    //ДРУГИЕ ПОЛЯ
    elseif($data_['cl_']=='c_call_client__cols_val'){
        
        $sql = "SELECT  IF(COUNT(*)>0,c_questions.id,'')
                            
                            
        				FROM c_questions, a_col, a_menu
        					WHERE c_questions.chk_active='1'
                            AND a_col.id=c_questions.a_col_id
                            AND a_col.a_menu_id=a_menu.id
                            AND a_menu.inc='"._DB($data_['inc'])."'
                            AND a_col.col='"._DB($data_['col'])."'
        ";
         
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        $myrow = mysql_fetch_array($res);
        $c_questions_id=$myrow[0];
        if ($c_questions_id==''){echo 'Не определен c_questions.id';}
        
        $sql_del = "DELETE 
        			FROM c_call_answer 
        				WHERE c_call_answer.c_call_client_id='"._DB($data_['id_'])."'
                        AND c_call_answer.c_questions_id='"._DB($c_questions_id)."'
        ";
        $mt = microtime(true);
        $res = mysql_query($sql_del) or die(mysql_error().'<br />'.$sql_del);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
        
        //Мульти значение
        if (is_array($data_['val_id'])){
            foreach($data_['val_id'] as $key => $val_){
                $sql_insert = "INSERT into c_call_answer (
                				c_call_client_id,
                				c_questions_id,
                                id_z_p_p,
                                comments
                			) VALUES (
                				'"._DB($data_['id_'])."',
                				'"._DB($c_questions_id)."',
                                '"._DB($val_)."',
                                ''
                )";
                
                $mt = microtime(true);
                $res_insert = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                $new_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
            }
        }
        else{//Одиночное значение
        
            $sql = "SELECT COUNT(*)
            				FROM `"._DB($data_['inc'])."`
            					WHERE "._DB($data_['inc']).".id='"._DB($data_['val_id'])."' 
            					ORDER BY id DESC
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            if ($myrow[0]>0){//если есть связь
                $sql_insert = "INSERT into c_call_answer (
                				c_call_client_id,
                				c_questions_id,
                                id_z_p_p,
                                comments
                			) VALUES (
                				'"._DB($data_['id_'])."',
                				'"._DB($c_questions_id)."',
                                '"._DB($data_['val_id'])."',
                                ''
                )";
                
                $mt = microtime(true);
                $res_insert = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                $new_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                  
            }else{//Если связи нет - вставляем в комментарии
                $sql_insert = "INSERT into  c_call_answer (
                				c_call_client_id,
                				c_questions_id,
                                id_z_p_p,
                                comments
                			) VALUES (
                				'"._DB($data_['id_'])."',
                				'"._DB($c_questions_id)."',
                                '0',
                                '"._DB($data_['val_id'])."'
                )";
                
                $mt = microtime(true);
                $res_insert = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
                $new_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_insert;$data_['_sql']['time'][]=$mt;
                            
            }
            
        }
    }
    
        echo json_encode($data_);
}

//***********************************************************************************************
//***********************************************************************************************
//***********************************************************************************************
//Отключение синхронизации
if ($_t=='google_drive_exit'){
    $data_=array();
    unset($_SESSION['google_drive_access_token']);
    
    echo json_encode($data_);
}
 //***********************************************************************************************
//***********************************************************************************************
//***********************************************************************************************


}
?>