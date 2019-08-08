<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php 
if (isset($_t)){
    if ($_t=='find'){
    }
    
    if ($_t=='save'){
        $s_test_quest=_GP('s_tests');
        
        //ПОЛУЧАЕМ ID теста
        $sql_s_test="SELECT 
                    IF(COUNT(*)>0,s_test.id,'')
                        FROM s_test
                        WHERE s_test.s_struktura_id='"._DB($nomer)."'
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
            				'"._DB($nomer)."'
            )";
            $mt = microtime(true);
            $res_s_test_ins = mysql_query($sql_s_test_ins);if (!$res_s_test_ins){echo $sql_s_test_ins;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_ins;$data_['_sql']['time'][]=$mt;
            $s_test_id = mysql_insert_id();
        }
        
        $sql_s_test_del = "DELETE 
        			FROM s_test_s_test_quest
        				WHERE s_test_s_test_quest.id1='"._DB($s_test_id)."'
        ";
        
        $mt = microtime(true);
        $res_s_test_del = mysql_query($sql_s_test_del);if (!$res_s_test_del){echo $sql_s_test_del;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_del;$data_['_sql']['time'][]=$mt;
       
            
        $s_test_quest_arr=array();
        if ($s_test_quest!=''){
            if (mb_strstr($s_test_quest,';',false,'utf-8')==true){
                $s_test_quest_arr=explode(';',$s_test_quest);
            }else{
                $s_test_quest_arr[]=$s_test_quest;
            }
        }
        foreach($s_test_quest_arr as $key_s_tests_quest=>$s_test_quest_id){
            $sql_s_test_ins = "INSERT into s_test_s_test_quest (
            				id1,
            				id2
            			) VALUES (
            				'"._DB($s_test_id)."',
            				'"._DB($s_test_quest_id)."'
            )";
            $mt = microtime(true);
            $res_s_test_ins = mysql_query($sql_s_test_ins);if (!$res_s_test_ins){echo $sql_s_test_ins;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_ins;$data_['_sql']['time'][]=$mt;
            $s_test_s_test_quest_id = mysql_insert_id();
        }
        
        //Опции
        $s_test_data_start=_GP('s_test_data_start');
            if ($s_test_data_start!=''){
                $s_test_data_start=date('Y-m-d H:i:s',strtotime($s_test_data_start));
            }
        $s_test_data_end=_GP('s_test_data_end');
            if ($s_test_data_end!=''){
                $s_test_data_end=date('Y-m-d H:i:s',strtotime($s_test_data_end));
            } 
        $s_test_time_for_test=_GP('s_test_time_for_test');
            if ($s_test_time_for_test!=''){
                $s_test_time_for_test=date('0000-00-00 H:i:00',strtotime($s_test_time_for_test));
            }
        $s_test_cnt_try=_GP('s_test_cnt_try');
        $s_test_cnt_quest=_GP('s_test_cnt_quest');
        $s_test_chk_active=_GP('s_test_chk_active');
            if ($s_test_chk_active=='on'){$s_test_chk_active='1';}else{$s_test_chk_active='0';}
        $s_test_chk_reg=_GP('s_test_chk_reg');
            if ($s_test_chk_reg=='on'){$s_test_chk_reg='1';}else{$s_test_chk_reg='0';}
        $s_test_chk_rand_quest=_GP('s_test_chk_rand_quest');
            if ($s_test_chk_rand_quest=='on'){$s_test_chk_rand_quest='1';}else{$s_test_chk_rand_quest='0';}
        $s_test_chk_rand_answer=_GP('s_test_chk_rand_answer');
            if ($s_test_chk_rand_answer=='on'){$s_test_chk_rand_answer='1';}else{$s_test_chk_rand_answer='0';}
        
       
        //Изменяем опции
        $sql_s_test_upp = "UPDATE s_test 
        			SET  
        				cnt_try='"._DB($s_test_cnt_try)."',
        				data_start='"._DB($s_test_data_start)."',
        				data_end='"._DB($s_test_data_end)."',
        				time_for_test='"._DB($s_test_time_for_test)."',
        				cnt_quest='"._DB($s_test_cnt_quest)."',
        				chk_active='"._DB($s_test_chk_active)."',
        				chk_reg='"._DB($s_test_chk_reg)."',
        				chk_rand_quest='"._DB($s_test_chk_rand_quest)."',
        				chk_rand_answer='"._DB($s_test_chk_rand_answer)."'
        		
        		WHERE s_test.id='"._DB($s_test_id)."'
        ";
        $mt = microtime(true);
        $res_s_test_upp = mysql_query($sql_s_test_upp) or die(mysql_error().'<br>'.$sql_s_test_upp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_upp;$data_['_sql']['time'][]=$mt;
    }
    if ($_t=='change'){
        
        $data_['_d'][$data_['col'][$i]]=array();
        
        //ПОЛУЧАЕМ ID теста
        $sql_s_test="SELECT 
                    IF(COUNT(*)>0,s_test.id,'')
                        FROM s_test
                        WHERE s_test.s_struktura_id='"._DB($nomer)."'
                        ";
        $mt = microtime(true);
        $res_s_test = mysql_query($sql_s_test);if (!$res_s_test){echo $sql_s_test;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test;$data_['_sql']['time'][]=$mt;
        $row_s_test = mysql_fetch_array($res_s_test);
        $s_test_id=$row_s_test[0];
        if ($s_test_id==''){
            $sql_s_test_ins = "INSERT into s_test (
            				s_struktura_id,
                            chk_active
            			) VALUES (
            				'"._DB($nomer)."',
                            '0'
            )";
            $mt = microtime(true);
            $res_s_test_ins = mysql_query($sql_s_test_ins);if (!$res_s_test_ins){echo $sql_s_test_ins;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_ins;$data_['_sql']['time'][]=$mt;
            $s_test_id = mysql_insert_id();
        }
        
        $sql_s_test = "SELECT   s_test.chk_active,
                                s_test.chk_reg,
                                s_test.chk_rand_quest,
                                s_test.chk_rand_answer,
                                s_test.cnt_try,
                                s_test.cnt_quest,
                                s_test.data_start,
                                s_test.data_end,
                                s_test.time_for_test
                                
    				FROM s_test
    					WHERE s_test.id='"._DB($s_test_id)."'
                        
                        "; 
        $res_s_test = mysql_query($sql_s_test) or die(mysql_error());
        $myrow_s_test = mysql_fetch_array($res_s_test);
        if ($myrow_s_test['data_start']=='0000-00-00 00:00:00'){$myrow_s_test['data_start']='';}
        if ($myrow_s_test['data_end']=='0000-00-00 00:00:00'){$myrow_s_test['data_end']='';}
        if ($myrow_s_test['time_for_test']=='0000-00-00 00:00:00'){$myrow_s_test['time_for_test']='';}
        
        if ($myrow_s_test['data_start']!=''){$myrow_s_test['data_start']=date('d.m.Y H:i',strtotime($myrow_s_test['data_start']));}
        if ($myrow_s_test['data_end']!=''){$myrow_s_test['data_end']=date('d.m.Y H:i',strtotime($myrow_s_test['data_end']));}
        if ($myrow_s_test['time_for_test']!=''){$myrow_s_test['time_for_test']=date('H:i',strtotime($myrow_s_test['time_for_test']));}
        
        $data_['_d'][$data_['col'][$i].'_opt']['chk_active']=$myrow_s_test['chk_active'];
        $data_['_d'][$data_['col'][$i].'_opt']['chk_reg']=$myrow_s_test['chk_reg'];
        $data_['_d'][$data_['col'][$i].'_opt']['chk_rand_quest']=$myrow_s_test['chk_rand_quest'];
        $data_['_d'][$data_['col'][$i].'_opt']['chk_rand_answer']=$myrow_s_test['chk_rand_answer'];
        $data_['_d'][$data_['col'][$i].'_opt']['cnt_try']=$myrow_s_test['cnt_try'];
        $data_['_d'][$data_['col'][$i].'_opt']['cnt_quest']=$myrow_s_test['cnt_quest'];
        $data_['_d'][$data_['col'][$i].'_opt']['data_start']=$myrow_s_test['data_start'];
        $data_['_d'][$data_['col'][$i].'_opt']['data_end']=$myrow_s_test['data_end'];
        $data_['_d'][$data_['col'][$i].'_opt']['time_for_test']=$myrow_s_test['time_for_test'];
        
        $sql_s_test = "SELECT  s_test_quest.id,
                        s_test_quest.name,
                        s_test_quest.data_create,
                        s_test_quest.html_code,
                        s_test_quest.chk_tip
    				FROM s_test_quest, s_test_s_test_quest
    					WHERE s_test_quest.id=s_test_s_test_quest.id2
                        AND s_test_s_test_quest.id1='"._DB($s_test_id)."'
                        
                        "; 
        $res_s_test = mysql_query($sql_s_test) or die(mysql_error());
        
        for ($myrow_s_test = mysql_fetch_array($res_s_test),$ii=0; $myrow_s_test==true; $myrow_s_test = mysql_fetch_array($res_s_test),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$ii]['id']=$myrow_s_test[0];
            $data_['_d'][$data_['col'][$i]][$ii]['name']=$myrow_s_test[1];
            $data_['_d'][$data_['col'][$i]][$ii]['dt']=date('d.m.Y H:i',strtotime($myrow_s_test['data_create']));;
            $data_['_d'][$data_['col'][$i]][$ii]['html_code']=$myrow_s_test[3];
            $data_['_d'][$data_['col'][$i]][$ii]['chk_tip']=$myrow_s_test[4];
            //Получаем ответы
            $sql_answer = "SELECT  s_test_answer.id,
                            s_test_answer.name,
                            s_test_answer.chk_true,
                            s_test_answer.html_code,
                            (SELECT IF(COUNT(*)>0,GROUP_CONCAT(a_photo.img SEPARATOR '::'),'') FROM a_photo WHERE a_photo.a_menu_id='401' AND a_photo.row_id=s_test_answer.id ORDER BY a_photo.sid) AS img
                        
                            
        				FROM s_test_answer 
        					WHERE s_test_answer.s_test_quest_id='"._DB($myrow_s_test['id'])."'"; 
            $res_answer = mysql_query($sql_answer) or die(mysql_error());
            
            for ($myrow_answer = mysql_fetch_array($res_answer),$j=0; $myrow_answer==true; $myrow_answer = mysql_fetch_array($res_answer),$j++)
            {
                $data_['_d'][$data_['col'][$i]][$ii]['answer']['id'][$j]=$myrow_answer[0];
                $data_['_d'][$data_['col'][$i]][$ii]['answer']['name'][$j]=$myrow_answer[1];
                $data_['_d'][$data_['col'][$i]][$ii]['answer']['chk_true'][$j]=$myrow_answer[2];
                $data_['_d'][$data_['col'][$i]][$ii]['answer']['html_code'][$j]=$myrow_answer[3];
                $data_['_d'][$data_['col'][$i]][$ii]['answer']['img'][$j]=$myrow_answer[4];
            }
       
        }
        
        
    }
    if ($_t=='copy'){
        
        //ПОЛУЧАЕМ ID теста
        $sql_s_test="SELECT 
                    IF(COUNT(*)>0,s_test.id,'')
                        FROM s_test
                        WHERE s_test.s_struktura_id='"._DB($nomer)."'
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
            				'"._DB($nomer)."'
            )";
            $mt = microtime(true);
            $res_s_test_ins = mysql_query($sql_s_test_ins);if (!$res_s_test_ins){echo $sql_s_test_ins;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_test_ins;$data_['_sql']['time'][]=$mt;
            $s_test_id = mysql_insert_id();
        }
        
        $data_['_d'][$data_['col'][$i]][$nomer]=array();
        
        $sql_s_test = "SELECT   s_test.chk_active,
                                s_test.chk_reg,
                                s_test.chk_rand_quest,
                                s_test.chk_rand_answer,
                                s_test.cnt_try,
                                s_test.cnt_quest,
                                s_test.data_start,
                                s_test.data_end,
                                s_test.time_for_test
                                
    				FROM s_test
    					WHERE s_test.id='"._DB($s_test_id)."'
                        
                        "; 
        $res_s_test = mysql_query($sql_s_test) or die(mysql_error());
        $myrow_s_test = mysql_fetch_array($res_s_test);
        if ($myrow_s_test['data_start']=='0000-00-00 00:00:00'){$myrow_s_test['data_start']='';}
        if ($myrow_s_test['data_end']=='0000-00-00 00:00:00'){$myrow_s_test['data_end']='';}
        if ($myrow_s_test['time_for_test']=='0000-00-00 00:00:00'){$myrow_s_test['time_for_test']='';}
        
        if ($myrow_s_test['data_start']!=''){$myrow_s_test['data_start']=date('d.m.Y H:i',strtotime($myrow_s_test['data_start']));}
        if ($myrow_s_test['data_end']!=''){$myrow_s_test['data_end']=date('d.m.Y H:i',strtotime($myrow_s_test['data_end']));}
        if ($myrow_s_test['time_for_test']!=''){$myrow_s_test['time_for_test']=date('d.m.Y H:i:s',strtotime($myrow_s_test['time_for_test']));}
        
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['chk_active']=$myrow_s_test['chk_active'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['chk_reg']=$myrow_s_test['chk_reg'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['chk_rand_quest']=$myrow_s_test['chk_rand_quest'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['chk_rand_answer']=$myrow_s_test['chk_rand_answer'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['cnt_try']=$myrow_s_test['cnt_try'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['cnt_quest']=$myrow_s_test['cnt_quest'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['data_start']=$myrow_s_test['data_start'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['data_end']=$myrow_s_test['data_end'];
        $data_['_d'][$data_['col'][$i].'_opt'][$nomer]['time_for_test']=$myrow_s_test['time_for_test'];
        
        $sql_s_test = "SELECT  s_test_quest.id,
                        s_test_quest.name,
                        s_test_quest.data_create,
                        s_test_quest.html_code,
                        s_test_quest.chk_tip
    				FROM s_test_quest, s_test_s_test_quest
    					WHERE s_test_quest.id=s_test_s_test_quest.id2
                        AND s_test_s_test_quest.id1='"._DB($s_test_id)."'
                        
                        "; 
        $res_s_test = mysql_query($sql_s_test) or die(mysql_error());
        
        for ($myrow_s_test = mysql_fetch_array($res_s_test),$ii=0; $myrow_s_test==true; $myrow_s_test = mysql_fetch_array($res_s_test),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$nomer][$ii]['id']=$myrow_s_test[0];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii]['name']=$myrow_s_test[1];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii]['dt']=date('d.m.Y H:i',strtotime($myrow_s_test['data_create']));;
            $data_['_d'][$data_['col'][$i]][$nomer][$ii]['html_code']=$myrow_s_test[3];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii]['chk_tip']=$myrow_s_test[4];
            //Получаем ответы
            $sql_answer = "SELECT  s_test_answer.id,
                            s_test_answer.name,
                            s_test_answer.chk_true,
                            s_test_answer.html_code,
                            (SELECT IF(COUNT(*)>0,GROUP_CONCAT(a_photo.img SEPARATOR '::'),'') FROM a_photo WHERE a_photo.a_menu_id='401' AND a_photo.row_id=s_test_answer.id ORDER BY a_photo.sid) AS img
                        
                            
        				FROM s_test_answer 
        					WHERE s_test_answer.s_test_quest_id='"._DB($myrow_s_test['id'])."'"; 
            $res_answer = mysql_query($sql_answer) or die(mysql_error());
            
            for ($myrow_answer = mysql_fetch_array($res_answer),$j=0; $myrow_answer==true; $myrow_answer = mysql_fetch_array($res_answer),$j++)
            {
                $data_['_d'][$data_['col'][$i]][$nomer][$ii]['answer']['id'][$j]=$myrow_answer[0];
                $data_['_d'][$data_['col'][$i]][$nomer][$ii]['answer']['name'][$j]=$myrow_answer[1];
                $data_['_d'][$data_['col'][$i]][$nomer][$ii]['answer']['chk_true'][$j]=$myrow_answer[2];
                $data_['_d'][$data_['col'][$i]][$nomer][$ii]['answer']['html_code'][$j]=$myrow_answer[3];
                $data_['_d'][$data_['col'][$i]][$nomer][$ii]['answer']['img'][$j]=$myrow_answer[4];
            }
       
        }
        
        
    }
    
    // ******************** ИМПОРТ *******************************
    if ($_t=='paste'){ 
        
        $val_arr=array();
        if (isJSON($col_val_arr[$key_col])==true){
            $val_arr=json_decode($col_val_arr[$key_col]);
        }else{
            $val_arr[0]=$col_val_arr[$key_col];
        }
        //есть свойства в импортируемом файле
        if (count($val_arr)>0){
            
        }
        echo '<p style="color:#900;">Импорт тестов в разработке</p>';
    }
}
else{//INCLUDE из obrabotchik -> export_csv
    if ($inc=='export_csv'){
        
        
    }
}
?>