<?php
    if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
        
    $inc_export=_GP('inc_export'); //таблица для выгрузки
    if ($inc_export!=''){
       //header('Content-Disposition: attachment;filename*=UTF-8"EXPORT_CSV_'.$inc_export.'.csv"');
 
    header('Date: '.date('D M j G:i:s T Y'));
    header('Last-Modified: '.date('D M j G:i:s T Y'));
    header('Content-Disposition: attachment;filename="EXPORT_CSV_'.$inc_export.'.csv"');
    header('Content-Type: application/vnd.ms-excel; ');
    //header('Content-Type: text/plain; charset: utf-8');
    header('Cache-Control: no-store, no-cache');
  
    ini_set('display_errors', 1); 
    error_reporting(E_ALL);
 /**/
    echo "\xEF\xBB\xBF";


    
    $nomer=_GP('nomer'); //номера через запятую или пусто при выгрузке всех строк
        
    
    $script_name=_GP('script_name'); //Имя скрипта для сохранения
    $script_opt1=_GP('script_opt1'); //Разделитель полей
    $script_opt2=_GP('script_opt2'); //Значения полей обрамлены
    $script_opt3=_GP('script_opt3'); //Символ экранирования
    $script_opt4=_GP('script_opt4'); //Разделитель строк
    $script_opt5=_GP('script_opt5'); //Выводить названия столбцов
    
    $col_chk=_GP('col_chk',array());//массив столбцов для экспорта => col_chk[0][prop[17]]  = on; col_chk[1][__null__1]=on
    $export_code=_GP('export_code',array());//массив значений для eval - export_code[name]=2
    


    
    // a_menu_id -> $inc_id
    $sql = "SELECT a_menu.id
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc_export)."'
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $myrow = mysql_fetch_array($res);
    $inc_id_export=$myrow[0];


    //СОХРАНЯЕМ НАСТРОЙКИ СКРИПТА В БАЗУ ДАННЫХ
    $sql = "SELECT IF(COUNT(*)>0,a_export_csv.id,'')
    				FROM a_export_csv 
    					WHERE a_export_csv.name='"._DB($script_name)."' 
                        AND a_export_csv.a_menu_id='"._DB($inc_id_export)."' 
    	"; 
    
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $myrow = mysql_fetch_array($res);
    $a_export_csv_id=$myrow[0];
    if ($a_export_csv_id==''){
        $sql = "INSERT into a_export_csv (
        				a_menu_id,
        				name,
                        opt1,
                        opt2,
                        opt3,
                        opt4,
                        opt5,
                        pop
                        
        			) VALUES (
        				'"._DB($inc_id_export)."',
        				'"._DB($script_name)."',
                        '"._DB($script_opt1)."',
                        '"._DB($script_opt2)."',
                        '"._DB($script_opt3)."',
                        '"._DB($script_opt4)."',
                        '"._DB($script_opt5)."',
                        '1'
                        
        )";
        
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        	else{$a_export_csv_id = mysql_insert_id();}
        
    }else{
        $sql = "UPDATE a_export_csv 
        			SET  
        				opt1='"._DB($script_opt1)."',
        				opt2='"._DB($script_opt2)."',
        				opt3='"._DB($script_opt3)."',
        				opt4='"._DB($script_opt4)."',
        				opt5='"._DB($script_opt5)."',
        				pop=pop+1
                        
        		
        		WHERE id='"._DB($a_export_csv_id)."'
        ";
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
    }
    
    //удаляем записи
    
    $sql = "DELETE 
    			FROM a_export_csv_col 
    				WHERE a_export_csv_id='"._DB($a_export_csv_id)."'
    ";
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
    
    foreach ($col_chk as $key => $col_arr){//перебор по столбцам
          
        foreach($col_arr as $col_name => $on){
            if ($on!='on' and $on>0){//число
                $col_name=$col_name.'['.$on.']';
            }
            $code='';
            if (isset($export_code[str_replace(']','',$col_name)])){
                $code=$export_code[str_replace(']','',$col_name)];
            }
            
            $sql = "INSERT into a_export_csv_col (
            				a_export_csv_id,
            				col,
                            code
            			) VALUES (
            				'"._DB($a_export_csv_id)."',
            				'"._DB($col_name)."',
                            '"._DB($code)."'
            )";
            
            $res = mysql_query($sql);
            	if (!$res){echo $sql;exit();}
            
            
            
        }
        
    }
    
    $export_code2=array();
    foreach($export_code as $key => $val){
        if (strstr($key,'[')==true){
            $key=str_replace('[','_',$key);
        }
        $export_code2[$key]=$val;
    }
    unset($export_code);$export_code=$export_code2;
/**/

    
    //*******************************************
    
    // получаем массив всех изменяемых столбцов
    $sql = "SELECT  a_col.id,
                    a_col.col,
                    a_col.col_ru,
                    a_col.tip,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_change='1'
                        AND a_col.a_menu_id='"._DB($inc_id_export)."'
                        AND a_col.id IN (
                                            SELECT a_admin_a_col.id2
                                                FROM a_admin_a_col, a_admin
                                                    WHERE a_admin_a_col.id1=a_admin.id
                                                        AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                                                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                        )
                    ORDER BY a_col.sid
    "; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $photo=0;$data_['col']=array();$data_['col_ru']=array();$data_['tip']=array();$col_m=array();$table_m=array();
    $connect_arr['inc']=array();
    $connect_arr['usl']=array();
    $connect_arr['col']=array();
    $connect_arr['col_']=array();
    $connect_arr['chk_change_']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['id'][$i]=$myrow[0];
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
        $data_['chk_change_'][$i]=$myrow[4];
        
    }
    $nomer_arr=array();
    $txt_menu='';
    
    if (strstr($nomer,',')==true){$nomer_arr=explode(',',$nomer);}else{if ($nomer!=''){$nomer_arr[0]=$nomer;}}
    $WHERE_EXPORT='';if (count($nomer_arr)>0) {$WHERE_EXPORT=' WHERE `'.$inc_export."`.`id` IN ('".implode("','",$nomer_arr)."')";}
    
    $ii=0;$connect_arr['col_connect']=array();
    foreach ($col_chk as $key => $col_arr){//перебор по столбцам
        
            
        foreach($col_arr as $col_name => $on){
            if (in_array($col_name,$data_['col'])==true){
                
            $key_col = array_search($col_name, $data_['col']);
            
                //****************************************************************************************************************************************
                // *************  ФОРМИРОВАНИЕ МАССИВА ДЛЯ MAIN SQL **************************************************************************************
                //****************************************************************************************************************************************
                if ($data_['tip'][$key_col]=='Связанная таблица max-max'){
                
                    if ($script_opt5=='1'){//название столбца
                        if ($txt_menu!=''){$txt_menu.=$script_opt1;}
                        $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
                    }                    
                    $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                            (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                            a_connect.usl,
                                            a_connect.tbl
                    				FROM a_connect 
                    					WHERE a_connect.a_col_id1='"._DB($data_['id'][$key_col])."'
                    					
                    	";
                    $mt = microtime(true);
                    $res_connect = mysql_query($sql_connect) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                    $myrow_connect = mysql_fetch_array($res_connect);
            
            
                    $connect_arr['inc'][$key_col]=$myrow_connect[0];
                    $connect_arr['col'][$key_col]=$myrow_connect[1];
                    $connect_arr['usl'][$key_col]=$myrow_connect[2];
                    $connect_arr['tbl'][$key_col]=$myrow_connect[3];
                    
                    $tbl_connect_=$inc_export."_".$connect_arr['inc'][$key_col];
                    if ($connect_arr['tbl'][$key_col]!=''){
                       $tbl_connect_=$connect_arr['tbl'][$key_col]; 
                    }
                    
                    $connect_arr['col_'][$key_col]=$myrow[1];
                    // print_rf($connect_arr);
                    //ok
                    //$col_m[$i]=" IF(COUNT(*)>0,GROUP_CONCAT(DISTINCT `"._DB($inc)."_".$connect_arr['inc'][$i]."`.`id2` SEPARATOR ','),'') AS ".$connect_arr['col_'][$i];
                    $col_m[$ii]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(DISTINCT `".$tbl_connect_."`.`id2` SEPARATOR ','),'') FROM `".$tbl_connect_."` WHERE `".$tbl_connect_."`.`id1`=`"._DB($inc_export)."`.`id`) AS ".$data_['col'][$key_col];
                    
                    $inc_=$myrow_connect[0];
                    $connect_arr['col_connect'][$key_col]=$data_['col'][$key_col];
                    $maxmax_arr[$inc_]=array();$maxmax_arrid[$inc_]=array();
                    $AND_='';if (count($nomer_arr)>0) {$AND_="AND `".$tbl_connect_."`.`id1` IN ('".implode("','",$nomer_arr)."')";}
                    $sql = "SELECT  `".$inc_."`.`id`,
                                    `".$inc_."`.`".$connect_arr['col'][$key_col]."`
                				FROM `".$inc_."`, `".$tbl_connect_."`
                                
                					WHERE `".$inc_."`.`id`=`".$tbl_connect_."`.`id2`
                                    $AND_
                	"; 
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            
                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                    {
                        $maxmax_arr[$connect_arr['col_connect'][$key_col]][$myrow[0]]=$myrow[1];
                    }
                }
                elseif ($data_['tip'][$key_col]=='Связанная таблица 1-max'){
                 
                        if ($script_opt5=='1'){//название столбца
                            if ($txt_menu!=''){$txt_menu.=$script_opt1;}
                            $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
                        }
                        $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                                (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                                a_connect.usl
                        				FROM a_connect 
                        					WHERE a_connect.a_col_id1='"._DB($data_['id'][$key_col])."'
                        					
                        	";
                        
                        $mt = microtime(true);
                        $res_connect = mysql_query($sql_connect) or die(mysql_error());
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                        $myrow_connect = mysql_fetch_array($res_connect);
                        
                        $col_m[$ii]="(SELECT `"._DB($myrow_connect[0])."`.`"._DB($myrow_connect[1])."` FROM `"._DB($myrow_connect[0])."` WHERE `"._DB($myrow_connect[0])."`.`id` = `"._DB($inc_export)."`.`"._DB($data_['col'][$key_col])."` LIMIT 1) AS "._DB($data_['col'][$key_col]);
                    
                }
                elseif ($data_['tip'][$key_col]=='Функция'){
                    
                    $find_tip='sql';
                    $file_function='ajax/__function_'.$inc_export.'_'.$data_['col'][$key_col].'.php';
                   
                    if (file_exists($file_function)){
                       
                            include $file_function;
                            
                    }else{
                        echo 'Нет файла функции: '.$file_function.'<br />';exit;
                    }
                }
                elseif ($data_['tip'][$key_col]=='Фото'){
                    
                    if ($script_opt5=='1'){//название столбца
                        if ($txt_menu!=''){$txt_menu.=$script_opt1;}
                        $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
                    }                    
                    $col_m[$ii]="(SELECT CONCAT('"._DB($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'])."/i/"._DB($inc_export)."/original/',`a_photo`.`img`) FROM `a_photo` WHERE `a_photo`.`row_id` = `"._DB($inc_export)."`.`id` AND `a_photo`.`a_menu_id`='"._DB($inc_id_export)."' ORDER BY `a_photo`.`sid` LIMIT 1) AS "._DB($data_['col'][$key_col]);
                }
                else{

                    if ($script_opt5=='1'){//название столбца
                        if ($txt_menu!=''){$txt_menu.=$script_opt1;}
                        $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
                    }                    
                    $col_m[$ii]=$data_['col'][$key_col];
                }
            }else{
                $col_m[$ii]="'' AS ".$col_name;
                
                if ($script_opt5=='1'){//пустое поле
                    if ($txt_menu!=''){$txt_menu.=$script_opt1;}
                    $txt_menu.=  $script_opt2.$script_opt2;
                }
            }
          
          $ii++;
        }
       
        
    }//перебор по столбцам
    
    
    if (isset($txt_menu) and $txt_menu!=''){
        echo $txt_menu;
        if ($script_opt4=='\n'){echo "\n";}
        else{echo $script_opt4;}
    }
    
    $SQL_MAIN='';
    foreach($col_m as $key => $sql_col){
        if ($SQL_MAIN!=''){$SQL_MAIN.=', ';}
        $SQL_MAIN.=$sql_col;
    }
    
    $sql = "SELECT $SQL_MAIN
    				FROM `"._DB($inc_export)."`
    					$WHERE_EXPORT
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $id_arr=array();
    //echo $sql.'<br /><br />';exit;
    for ($myrow = mysql_fetch_assoc($res); $myrow==true; $myrow = mysql_fetch_assoc($res))
    {
        //print_rf($myrow);
        //Выполняем код
        foreach($myrow as $col => $val){
            
            if (isset($export_code[$col]) and $export_code[$col]!=''){
                if ( syntax_check_php_file($export_code[$col]) ) {
                    eval($export_code[$col]);
                }else{
                    echo 'error code: '.$export_code[$col];exit;
                }
            }
        }
        //print_rf($export_code);print_rf($myrow);exit;
        //******************************************************************
        $ii=0;//маркер для разделителя столбцов
        foreach($myrow as $col => $val){
            if ($ii>0){echo $script_opt1;}//разделитель столбцов
            
            if (in_array($col,$connect_arr['col_connect'])){//MAX-MAX
                if (isset($export_code[$col]) and $export_code[$col]==''){//если не было обработки
                    if ($myrow[$col]!=''){//если есть связь
                        //преобразуем в массив
                        $arr_connect=array();
                        if (strstr($myrow[$col],',')==true){$arr_connect=explode(',',$myrow[$col]);}
                        else{ $arr_connect[0]=$myrow[$col];}
                        
                        //перебор массива
                        $myrow[$col]='';
                        foreach($arr_connect as $key_connect => $id_connect){
                            if ($myrow[$col]!=''){$myrow[$col].=', ';}
                            if (isset($maxmax_arr[$col][$id_connect])){
                                
                                $myrow[$col].=$maxmax_arr[$col][$id_connect];
                            }else{
                                print_rf($maxmax_arr);
                                $myrow[$col].='no data $maxmax_arr[$col][$id_connect]; $col='.$col.';$id_connect='.$id_connect;
                            }
                        }
                    }
                }
            }
            //if (str_replace('"',$script_opt3.'"',iconv('UTF-8','CP1251',$myrow[$col]))==''){$myrow[$col]=$col;}
            echo $script_opt2.str_replace('"',$script_opt3.'"',$myrow[$col]).$script_opt2;
            
            $ii++;//маркер для разделителя столбцов
        }
        
        //разделитель строк
        if ($script_opt4=='\n'){echo "\n";}
        else{echo $script_opt4;}
        
        //****************************************************************
        
    }
    
        

    $include_=0;
    }else{
        if (isset($_SESSION['old_inc']) and $_SESSION['old_inc']!=''){
            header('Location: ?inc='.$_SESSION['old_inc']);
        }else{
            
            header('Location: ?inc=s_strukrura');
        }
    }
?>