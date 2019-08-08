<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

include "../db.php";
include "../functions.php";


if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){
$base_memory_usage = memory_get_usage();
$base_memory_usage = memory_get_usage();


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
// 
if ($_t=='find'){
     $data_=array();
        //$data_['col']; # столбцы
        //$data_['col_ru']; # столбцы на русском
        //$data_['tip']; # тип столбца
        
        //$data_['O']='sid'; # столбец сортировки
        //$data_['OA']='A'; # тип сортировки A-ASC, D-DESC
    
    // получаем данные
    $inc=_GP('_inc'); //таблица
    
    $data_['_d']=array(); //ДАННЫЕ
    $WHERE='';//условия
    $FROM='';//таблицы для более сложных условий
    
    //массив столбцов
    $names=array();$names=get_column_names_with_show($inc);
    if (!isset($names) or !is_array($names) or count($names)==0){
        echo 'В таблице `'.$inc.'` нет столбцов!';exit();
    }
        
    // a_menu_id -> $inc_id
    $sql = "SELECT a_menu.id
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."'
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0];
    
    //получение id записи
    $f__id=_GP('f__id');
    if ($f__id!=''){$WHERE.="`"._DB($inc)."`.`id`='"._DB($f__id)."'";}
        

    // получаем массив всех отображаемых столбцов
    $sql = "SELECT  a_col.id,
                    a_col.col,
                    a_col.col_ru,
                    a_col.tip,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_view='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
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
        if (isset($_SESSION['a_col_view']) and isset($_SESSION['a_col_view'][$inc]) and isset($_SESSION['a_col_view'][$inc][$myrow[1]]) and $_SESSION['a_col_view'][$inc][$myrow[1]]=='-1'){
            
        }
        else{//столбец не скрыт пользователем
        
        $data_['id'][$i]=$myrow[0];
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
        $data_['chk_change_'][$i]=$myrow[4];
        
        //ОБРАБОТКА ФИЛЬТРОВ
        
        if ($data_['tip'][$i]=='Текст'//**** +++
            or $data_['tip'][$i]=='Длинный текст'
            or $data_['tip'][$i]=='HTML-код'
            or $data_['tip'][$i]=='Email'
            or $data_['tip'][$i]=='Ссылка'
        ){
            if (_GP('f__'.$data_['col'][$i])!=''){
                $val_f=_GP('f__'.$data_['col'][$i]); 
                
                
                if ($val_f!=''){
                    if (strstr($val_f,'@@')==true){//несколько слов
                        $val_f_arr=explode('@@',$val_f);
                        foreach($val_f_arr as $key => $val_f){
                            if ($WHERE!='') {$WHERE.=' AND ';}
                            $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."` LIKE '%"._DB($val_f)."%'";
                        }
                    }
                    else{ //одно слово
                        if ($WHERE!='') {$WHERE.=' AND ';}
                        $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."` LIKE '%"._DB($val_f)."%'";
                    }
                }
                
            }
        }
        elseif ( $data_['tip'][$i]=='Телефон'//**** +++
            or $data_['tip'][$i]=='enum'
            or $data_['tip'][$i]=='Цвет'
            or $data_['tip'][$i]=='chk'
        ){
            if (_GP('f__'.$data_['col'][$i])!=''){
                $val_f=_GP('f__'.$data_['col'][$i]);
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`='"._DB($val_f)."'";
            }
        }
        elseif ($data_['tip'][$i]=='Связанная таблица 1-max'
        ){
            
            if (_GP('f__'.$data_['col'][$i])!=''){
                $val_f=_GP('f__'.$data_['col'][$i]);
    
                if (preg_replace('/[\D]{1,}/s', '',$val_f)==$val_f){
                    
                    if ($WHERE!='') {$WHERE.=' AND ';}
                    $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`='"._DB($val_f)."'";
                }
                else{
                  
                    $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                            (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                            a_connect.usl
                    				FROM a_connect 
                    					WHERE a_connect.a_col_id1='"._DB($data_['id'][$i])."'
                    					
                    	";
                    
                    $mt = microtime(true);
                    $res_connect = mysql_query($sql_connect) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                    $myrow_connect = mysql_fetch_array($res_connect);
                
                
                
                    $sql_max = "SELECT `".$myrow_connect[0]."`.`id`
                    				FROM `".$myrow_connect[0]."` 
                    					WHERE `".$myrow_connect[0]."`.`".$myrow_connect[1]."` LIKE '"._DB($val_f)."%'
                                        LIMIT 1
                    				
                    	"; 
                    
                    $mt = microtime(true);
                    $res_max = mysql_query($sql_max);if (!$res_max){echo $sql_max;exit();}
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_max;$data_['_sql']['time'][]=$mt;
                    $myrow_max = mysql_fetch_array($res_max);
                    if ($myrow_max[0]!=''){
                        if ($WHERE!='') {$WHERE.=' AND ';}
                        $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`='"._DB($myrow_max[0])."'";
                    }
                }
            
                
            }
        }
        elseif ($data_['tip'][$i]=='Стоимость'
        ){
            $arr_fillter=_GP('f__'.$data_['col'][$i]);
            if (isset($arr_fillter[0]) and $arr_fillter[0]!=''){
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`>='".str_replace(' ','',_DB($arr_fillter[0]))."'";
            }
            if (isset($arr_fillter[1]) and $arr_fillter[1]!=''){
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`<='".str_replace(' ','',_DB($arr_fillter[1]))."'";
            }
        }
        elseif ($data_['tip'][$i]=='Связанная таблица max-max'
        ){
            $maxmax_opt1=_GP('f__'.$data_['col'][$i].'_opt1');
            $maxmax_=_GP('f__'.$data_['col'][$i]);
            $maxmax_arr=array();
           
            if (!is_array($maxmax_)){
                if ($maxmax_!=''){
                    $maxmax_arr[0]=$maxmax_;
                }
            }else{
                $maxmax_arr=$maxmax_;
            }
           
            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                    a_connect.usl,
                                    a_connect.tbl
            				FROM a_connect 
            					WHERE a_connect.a_col_id1='"._DB($data_['id'][$i])."'
            					
            	";
             //echo $sql_connect;
             
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
            
            $myrow_connect = mysql_fetch_array($res_connect);
            $connect_arr['inc'][$i]=$myrow_connect[0];
            $connect_arr['col'][$i]=$myrow_connect[1];
            $connect_arr['usl'][$i]=$myrow_connect[2];
            $connect_arr['tbl'][$i]=$myrow_connect[3];
            
            //таблица связи
            $tbl_connect_=$inc.'_'.$connect_arr['inc'][$i];
            if ($connect_arr['tbl'][$i]!=''){
               $tbl_connect_=$connect_arr['tbl'][$i]; 
            }
            
            
            $WH_MAX=""; //ОК
            if (count($maxmax_arr)>0){ //23.86
                if (isset($maxmax_arr[0]) and $maxmax_arr[0]=='-1'){
                    
                    if ($WHERE!=''){$WHERE.=" AND ";}
                    $WHERE.=" `".$inc."`.`id` NOT IN (SELECT `".$tbl_connect_."`.`id1` FROM `".$tbl_connect_."`)";
                    
                }else{
                    if ($maxmax_opt1=='or'){
                        $WH_MAX=" AND ".$tbl_connect_."_.`id2` IN ('".implode("','",$maxmax_arr)."')";
                    
                        if ($WHERE!=''){$WHERE.=" AND ";}
                        $WHERE.=" `".$inc."`.`id`=".$tbl_connect_."_.`id1` ". $WH_MAX;
                        $FROM.=", `".$tbl_connect_."` AS ".$tbl_connect_."_";
                    }
                    elseif ($maxmax_opt1=='and'){
                     
                        if ($WHERE!=''){$WHERE.=" AND ";}
                        $WHERE.=" `".$inc."`.`id` IN (SELECT ".$tbl_connect_."_.`id1` FROM `".$tbl_connect_."` AS ".$tbl_connect_."_ WHERE   ".$tbl_connect_."_.`id2` IN ('".implode("','",$maxmax_arr)."') GROUP BY ".$tbl_connect_."_.`id1`  HAVING COUNT(".$tbl_connect_."_.`id1`)='".count($maxmax_arr)."')";
                        
                    }
                    
                }
            }
            
            
            
            
        }
        elseif ($data_['tip'][$i]=='Функция'
        ){
            $find_tip='fillter';
            $file_function='__function_'.$inc.'_'.$data_['col'][$i].'.php';
            if (file_exists($file_function)){
                    include $file_function;
                   
            }else{
                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php<br />';
            }
        }
        elseif ($data_['tip'][$i]=='Фото'
        ){
            if (_GP('f__'.$data_['col'][$i])!=''){
                $val_f=_GP('f__'.$data_['col'][$i]);
                $usl_img="IN";
                    if ($val_f=='0'){$usl_img="NOT IN";}
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.="`"._DB($inc)."`.`id` $usl_img (SELECT a_photo.row_id FROM a_photo WHERE a_photo.a_menu_id='"._DB($inc_id)."')";
            }
        }
        elseif ($data_['tip'][$i]=='Целое число'
            or $data_['tip'][$i]=='Дробное число'
            or $data_['tip'][$i]=='Дата'
            or $data_['tip'][$i]=='Дата-время'
        ){
            $arr_fillter=_GP('f__'.$data_['col'][$i]);
            if (isset($arr_fillter[0]) and $arr_fillter[0]!=''){
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`>='"._DB($arr_fillter[0])."'";
            }
            if (isset($arr_fillter[1]) and $arr_fillter[1]!=''){
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.="`"._DB($inc)."`.`"._DB($data_['col'][$i])."`<='"._DB($arr_fillter[1])."'";
            }
        }
        else{
            echo 'NO TIP COL FOR FILLTER! $data_[tip][$i]='.$data_['tip'][$i].'<br />';
            exit();
        }
        
        //****************************************************************************************************************************************
        // *************  ФОРМИРОВАНИЕ МАССИВА ДЛЯ MAIN SQL **************************************************************************************
        //****************************************************************************************************************************************
        if ($myrow[3]=='Связанная таблица max-max'){
            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                    a_connect.usl,
                                    a_connect.tbl
                                    
            				FROM a_connect 
            					WHERE a_connect.a_col_id1='"._DB($data_['id'][$i])."'
            					
            	";
             //echo $sql_connect;
            //$res_connect = mysql_query($sql_connect) or die(mysql_error());
            
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
            $myrow_connect = mysql_fetch_array($res_connect);
            
            $connect_arr['inc'][$i]=$myrow_connect[0];
            $connect_arr['col'][$i]=$myrow_connect[1];
            $connect_arr['usl'][$i]=$myrow_connect[2];
            $connect_arr['tbl'][$i]=$myrow_connect[3];
            
            $tbl_connect_=$inc.'_'.$connect_arr['inc'][$i];
            if ($connect_arr['tbl'][$i]!=''){
               $tbl_connect_=$connect_arr['tbl'][$i]; 
            }
            
            $connect_arr['col_'][$i]=$myrow[1];
            //ok
            //$col_m[$i]=" IF(COUNT(*)>0,GROUP_CONCAT(DISTINCT `"._DB($inc)."_".$connect_arr['inc'][$i]."`.`id2` SEPARATOR ','),'') AS ".$connect_arr['col_'][$i];
            $col_m[$i]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(DISTINCT `".$tbl_connect_."`.`id2` SEPARATOR ','),'') FROM `".$tbl_connect_."` WHERE `".$tbl_connect_."`.`id1`=`"._DB($inc)."`.`id`) AS ".$connect_arr['col_'][$i];
            
            
        }
        elseif ($myrow[3]=='Связанная таблица 1-max'){
         
                $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                        (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                        a_connect.usl
                				FROM a_connect 
                					WHERE a_connect.a_col_id1='"._DB($data_['id'][$i])."'
                					
                	";
                
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                $myrow_connect = mysql_fetch_array($res_connect);
                
                $col_m[$i]="(SELECT IF(COUNT(*)>0,`"._DB($myrow_connect[0])."`.`"._DB($myrow_connect[1])."`,'') FROM `"._DB($myrow_connect[0])."` WHERE `"._DB($myrow_connect[0])."`.`id` = `"._DB($inc)."`.`"._DB($data_['col'][$i])."` LIMIT 1) AS "._DB($data_['col'][$i])." ";
            
        }
        elseif ($myrow[3]=='Функция'){
            
            $find_tip='sql';
            $file_function='__function_'.$inc.'_'.$data_['col'][$i].'.php';
            if (file_exists($file_function)){
                    include $file_function;
                    
            }else{
                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php<br />';
            }
        }
        elseif ($myrow[3]=='Фото'){
            $photo=1;
        }
        else{
            $col_m[$i]=$data_['col'][$i];
        }
        } 
    }
    $LIMIT=_GP('LIMIT',20);//$_SESSION['a_options']['Количество загружаемых строк']);//ЛИМИТ

        //массив столбцов
        $SQL_COL='';
        foreach($col_m as $col_id => $col_){
            if ($SQL_COL!=''){$SQL_COL.=', ';}
            
            if (strstr($col_,' AS ')==true){ //содержжит вложенный селект
                $SQL_COL.=$col_;
            }else{
                $SQL_COL.="`"._DB($inc)."`.`".$col_."`";
            }
        }
        
        //$LIMIT
        if ($LIMIT!=''){$LIMIT=' LIMIT '.$LIMIT;}
        if ($WHERE!=''){$WHERE=' WHERE '.$WHERE;}
        //if ($FROM!=''){$FROM=', '.$FROM;}
        
        //сортировка
        $ORDER='';//сортировка
        $s__minmax_col=_GP('s__minmax_col',array());//сортировка
        $s__maxmin_col=_GP('s__maxmin_col',array());//сортировка
        
        foreach($s__minmax_col as $key => $col_sort){
            
            //связанная таблица
            $key0 = array_search($col_sort, $data_['col']);
            if ($data_['tip'][$key0]=='Связанная таблица 1-max'){
                if ($ORDER!=''){$ORDER.=', ';}
                $ORDER.=''.$col_sort.' ASC';
            }
            elseif ($data_['tip'][$key0]=='Связанная таблица max-max'){
                if ($ORDER!=''){$ORDER.=', ';}
                $ORDER.=''.$col_sort.' ASC';
            }
            else{
                if ($ORDER!=''){$ORDER.=', ';}
                $ORDER.='`'._DB($inc).'`.`'.$col_sort.'` ASC';
            }
            
        }
        foreach($s__maxmin_col as $key => $col_sort){
            //связанная таблица
            $key0 = array_search($col_sort, $data_['col']);

            if (@$data_['tip'][$key0]=='Связанная таблица 1-max'){
                if ($ORDER!=''){$ORDER.=', ';}
                $ORDER.=''.$col_sort.' DESC';
            }
            elseif (@$data_['tip'][$key0]=='Связанная таблица max-max'){
                if ($ORDER!=''){$ORDER.=', ';}
                $ORDER.=''.$col_sort.' DESC';
            }
            else{
                if ($ORDER!=''){$ORDER.=', ';}
                $ORDER.='`'._DB($inc).'`.`'.$col_sort.'` DESC';
            }
        }
        //сортировка по умолчанию
        
        if ($ORDER==''){if (in_array('data_change',$names)){$ORDER='`'._DB($inc).'`.`data_change` DESC';}}
        if ($ORDER==''){if (in_array('sid',$names)){$ORDER='`'._DB($inc).'`.`sid`';$data_['O']='sid';}}
        
        
        if (in_array('pid',$names)){//если дерево
        
            if ($SQL_COL!='') {$SQL_COL.=',';}
            $SQL_COL.="`"._DB($inc)."`.`pid`";
            
            $ORDER='`'._DB($inc).'`.`sid`';$data_['O']='sid'; //единстваенно возможная сортировка для дерева
            $LIMIT='';
        } 
    
        
        if ($ORDER!='') {$ORDER=' ORDER BY '.$ORDER;}
        if ($SQL_COL!='') {$SQL_COL=', '.$SQL_COL;}
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // ВСЕ КОЛИЧЕСТВО
        $sql_cnt = "SELECT COUNT(DISTINCT `"._DB($inc)."`.`id`)
                            
        				FROM `"._DB($inc)."` $FROM
                                $WHERE
                                
        ";
        $mt = microtime(true);
        $res_cnt = mysql_query($sql_cnt) or die($sql_cnt.'<br />'.mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_cnt;$data_['_sql']['time'][]=$mt;
        
        $myrow_cnt = mysql_fetch_array($res_cnt);
        $data_['_cnt']=$myrow_cnt[0];
        
       
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////// MAIN ///////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $sql_main = "SELECT DISTINCT `"._DB($inc)."`.`id`
                            ".$SQL_COL." 
                            
        				FROM `"._DB($inc)."` $FROM
                                $WHERE
                                GROUP BY `"._DB($inc)."`.`id`
        						$ORDER
                                $LIMIT
        ";
        //echo $sql_main;
        
        $mt = microtime(true);
        $res_main = mysql_query($sql_main) or die($sql_main.'<br />'.mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_main;$data_['_sql']['time'][]=$mt;
        
        
        
        $id_arr=array();
        for ($myrow_main = mysql_fetch_array($res_main),$i=0; $myrow_main==true; $myrow_main = mysql_fetch_array($res_main),$i++)
        {
           $id_arr[$i]=$myrow_main[0];
           $data_['_d'][$i]['id']=$myrow_main[0];
           
           if (in_array('pid',$names)){$data_['_d'][$i]['pid']=$myrow_main['pid'];}
           
           foreach($col_m as $col_id => $col_){
            
            if (strstr($col_,' AS ')==true){ //содержжит вложенный селект
                   $str_pos=strpos($col_,' AS ');
                   if (isset($myrow_main[trim(substr($col_,$str_pos+4,strlen($col_)-$str_pos-4))])){
                        $data_['_d'][$i][trim(substr($col_,$str_pos+4,strlen($col_)-$str_pos-4))]=$myrow_main[trim(substr($col_,$str_pos+4,strlen($col_)-$str_pos-4))];
                        //$data_['_d'][$i][trim(substr($col_,$str_pos+4,strlen($col_)-$str_pos-4))]=strip_tags($myrow_main[trim(substr($col_,$str_pos+4,strlen($col_)-$str_pos-4))]);
                   }else{
                    $data_['error'][]= 'NO INDEX: "'.trim(substr($col_,$str_pos+4,strlen($col_)-$str_pos-4)).'"'.'<br />'.$sql_main;
                    //print_rf($myrow_main);echo '<br />'.$sql_main;exit;
                   }
                   
                }else{
                    //echo $myrow_main[$col_].'<br />';
                    if (isset($myrow_main[$col_])){$data_['_d'][$i][$col_]=strip_tags($myrow_main[$col_]);}
                    else{
                        $data_['_d'][$i][$col_]='';
                        //$data_['error'][]='COL: no exists: $myrow_main['.$col_.']<br />';
                        //echo 'COL: no exists: $myrow_main['.$col_.']<br />';
                    }
                }
                
           }
           
           //MAX-MAX
           //print_r($sql_main);
           
           foreach($connect_arr['col_'] as $key => $col_){
                unset($data_['_d'][$i][$col_]); //удаляем значение для max-max ранее созданное
                if (!isset($myrow_main[$col_])){
                         $data_['error'][]='MAX-MAX: no exists: $myrow_main['.$col_.']<br />';
                         //exit();
                    }
                
                if ($myrow_main[$col_]!=''){
                    if (strstr($myrow_main[$col_],',')==true){
                        $data_['_d'][$i][$col_]=explode(',',$myrow_main[$col_]);  
                    }else{
                        //print_r($myrow_main);echo '+'.$col_.'+<br />';
                        $data_['_d'][$i][$col_][]=$myrow_main[$col_];
                    }
                    
                }else{
                    $data_['_d'][$i][$col_]=array();
                }
                
           }//end MAX-MAX
           //
           
        }
        
       
        if (count($id_arr)>0){
            ///MAX-MAX
            foreach($connect_arr['inc'] as $key => $inc_){
                
                
                $tbl_connect_=$inc."_".$connect_arr['inc'][$key];
                if ($connect_arr['tbl'][$key]!=''){
                   $tbl_connect_=$connect_arr['tbl'][$key]; 
                }
                
                
                
                $maxmax_arr[$inc_]=array();$maxmax_arrid[$inc_]=array();
                
                $sql = "SELECT  `".$inc_."`.`id`,
                                `".$inc_."`.`".$connect_arr['col'][$key]."`
            				FROM `".$inc_."`, `".$tbl_connect_."`
                            
            					WHERE `".$inc_."`.`id`=`".$tbl_connect_."`.`id2`
                                AND `".$tbl_connect_."`.`id1` IN ('".implode("','",$id_arr)."')
            	"; 
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                {
                    $maxmax_arr[$inc_][$myrow[0]]=$myrow[1];
                }
                //заменяем значения
                foreach($id_arr as $key1 => $id_){
                   
                    foreach($data_['_d'][$key1][$connect_arr['col_'][$key]] as $key2 => $id_connect){
                        if (isset($maxmax_arr[$inc_][$id_connect])){
                            $data_['_d'][$key1][$connect_arr['col_'][$key].'_'][$key2]=$maxmax_arr[$inc_][$id_connect];
                        }
                    }
                }
                
            }
            //ФОТО
            if($photo==1){
                ///////////////////////////////
                $photo_arr=array();
                $photo_arr_tipx=array();
                $photo_arr_row_id=array();
                $sql = "SELECT  `a_photo`.`id`,
                                `a_photo`.`tip`,
                                `a_photo`.`img`,
                                `a_photo`.`row_id`
                                
                                
            				FROM `a_photo`
                            
            					WHERE `a_photo`.`a_menu_id`='"._DB($inc_id)."'
                                AND `a_photo`.`row_id` IN ('".implode("','",$id_arr)."')
                                
                                ORDER BY FIELD(`tip`,'Основное') DESC, `a_photo`.`sid`
                              
                                                          
                                                          
                                
            	"; 
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                {
                    $photo_arr[]=$myrow['img'];
                    $photo_arr_tip[]=$myrow['tip'];
                    $photo_arr_row_id[]=$myrow['row_id'];
                }
                
                
                foreach($id_arr as $key1 => $id_){
                    unset($data_['_d'][$key1]['photo_tip'],$data_['_d'][$key1]['photo_img']);
                   
                    foreach($photo_arr_row_id as $a_photo_key => $id_2){
                        if ($id_==$id_2){
                            $data_['_d'][$key1]['photo_img'][]=$photo_arr[$a_photo_key];
                            $data_['_d'][$key1]['photo_tip'][]=$photo_arr_tip[$a_photo_key];
                        }
                    }
                }
                
            }
            
        }
        
    // **********************************************************************
    // ********************** ТАБЛИЦА ***************************************
    // **********************************************************************
    if (!in_array('pid',$names)){//table
        $data_['t']='table';
        
        
        
    }//end table
    // **********************************************************************
    // ********************** ДЕРЕВО ***************************************
    // **********************************************************************
    else{//tree
        $data_['t']='tree';

        
    }//end tree

    echo json_encode($data_);
}


// ************************************************************
// СОРТИРОВКА
if ($_t=='save_sort'){
    $id_arr=json_decode( _GP('i'), true);//_GP('i',array());
    $pid_arr=json_decode(_GP('p'), true);//_GP('p',array());
    $inc=_GP('_inc');
   
    if (count($id_arr)>0){
        $pid_txt='';
        $sid_txt='';
        foreach($id_arr as $sid => $id){
            $sid_=(int) $sid + 1;
            $pid_txt.=" WHEN ".$id. " THEN ".$pid_arr[$sid];
            $sid_txt.=" WHEN ".$id. " THEN ".$sid_;
        }
        
            $sql = "
            		UPDATE `"._DB($inc)."`
            			SET  
            				pid=CASE id $pid_txt END,
            				sid=CASE id $sid_txt END
            		
            		WHERE id IN ('".implode("','",$id_arr)."')
            ";
            $data_['sql'][]=$sql;
           
            $mt = microtime(true);
            mysql_query($sql) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
            echo json_encode($data_);
    }
}

//************************************************************************************************** 
// Загрузка фото
elseif ($_t=='upload'){

    $inc=_GP('_inc');
    $id=_GP('id');
        if ($id>0){
            $targetDir = '../../i/'.$inc.'/original';
        }else{
            $targetDir = '../../i/'.$inc.'/temp';
        }
    
    $fileName='';
    

    if (!is_array($_SESSION['a_admin'])){unset($_SESSION['a_admin']);}
    
    // проверяем на пустоту
    if (!isset($_SESSION['a_admin'][$inc]['photo_temp']) or $_SESSION['a_admin'][$inc]['photo_temp']==''){
            
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
                
        $_SESSION['a_admin'][$inc]['photo_temp']=$fileName;
    }else{
        $fileName=$_SESSION['a_admin'][$inc]['photo_temp'];
    }
    
    
        
    @set_time_limit(5 * 60);
    if (!file_exists('../../i')) {@mkdir('../../i',0777);}
    if (!file_exists('../../i/'.$inc)) {@mkdir('../../i/'.$inc,0777);}
    if (!file_exists('../../i/'.$inc.'/small/')) {@mkdir('../../i/'.$inc.'/small/',0777);}
    if (!file_exists('../../i/'.$inc.'/original/')) {@mkdir('../../i/'.$inc.'/original/',0777);}
    if (!file_exists('../../i/'.$inc.'/temp/')) {@mkdir('../../i/'.$inc.'/temp/',0777);}
    
    
    if (!file_exists($targetDir)) {@mkdir($targetDir,0777);}
    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 5 * 3600; // Temp file age in seconds
    $filePath = $targetDir . '/' . $fileName;

    
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    
    if ($cleanupTargetDir) { // Удаление старых файлов
    	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {}
    	while (($file = readdir($dir)) !== false) {
    		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
    		if ($tmpfilePath == "{$filePath}.part") {
    			continue;
    		}
    		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
    			@unlink($tmpfilePath);
    		}
    	}
    	closedir($dir);
    }
    if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {}
    
    if (!empty($_FILES)) {
    	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {}
    	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {}
    } else {if (!$in = @fopen("php://input", "rb")) {}}
    
    while ($buff = fread($in, 4096)) {fwrite($out, $buff);}
    
    @fclose($out);
    @fclose($in);
    
    if (!$chunks || $chunk == $chunks - 1) {
        rename("{$filePath}.part", $filePath);
        unset($_SESSION['a_admin'][$inc]['photo_temp']);
        if ($id>0){
            
            $size_arr= getimagesize($filePath);
            $w_orig=$size_arr[0];
            $h_orig=$size_arr[1];
            smart_resize_image( $filePath, '../../i/'.$inc.'/small/'.$fileName, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                              
            $sql = "SELECT a_menu.id
            				FROM a_menu 
            					WHERE a_menu.inc='"._DB($inc)."' 
            				
            	"; 
            
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $myrow = mysql_fetch_array($res);
            $a_menu_id=$myrow[0];
            
            $sql = "INSERT into a_photo (
            				sid,
            				img,
                            a_menu_id,
                            tip,
                            row_id
            			) VALUES (
            				'0',
            				'"._DB($fileName)."',
                            '"._DB($a_menu_id)."',
                            'Основное',
                            '"._DB($id)."'
            )";
            
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $new_id = mysql_insert_id();
            
        }
    }
    echo $fileName;

}
//************************************************************************************************** 
//************************************************************************************************** 
// Автозаполнение
elseif ($_t=='autocomplete_input'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $data_=array();
    
    $term=_GP('term');
    $col_id=_GP('_col_id');
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
    
    $sql_connect = "SELECT `"._DB($inc)."`.`"._DB($col)."`
    				FROM `"._DB($inc)."`
                        WHERE `"._DB($inc)."`.`"._DB($col)."` LIKE '%"._DB($term)."%'
    					ORDER BY `"._DB($inc)."`.`"._DB($col)."` LIKE '"._DB($term)."%' DESC
                       LIMIT 30
    "; 
    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $mt = microtime(true)-$mt; //$data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
    {
        $data_[]=$myrow_connect[0];
    }
    
    echo json_encode($data_);
}
//************************************************************************************************** 
// Автозаполнение
elseif ($_t=='autocomplete_1_max'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('term');
    $col_id=_GP('_col_id');
    
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
        
        $sql = "SELECT a_col.id
        				FROM a_menu, a_col 
        					WHERE a_col.col='"._DB($col)."' 
        					AND a_col.a_menu_id=a_menu.id
                            AND  a_menu.inc='"._DB($inc)."' 
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error());
        $mt = microtime(true)-$mt;// $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
        $myrow = mysql_fetch_array($res);
        $col_id=$myrow[0];
        
    }
    if ($col=='' or $inc==''){echo 'Не определены переменные $col="'.$col.'", $inc="'.$inc.'"';exit;}
    
    $sql_="SELECT IF(COUNT(*)>0,`COLUMN_DEFAULT`,'') 
                FROM `information_schema`.`COLUMNS` 
                    WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                    AND `TABLE_NAME`='"._DB($inc)."' 
                    AND `COLUMN_NAME`='"._DB($col)."'
        "; 
	$res_def = mysql_query($sql_);
	$myrow_def = mysql_fetch_array($res_def);
	$val_def=$myrow_def[0]; 
                            
    
    $sql_connect = "SELECT  a_menu.id,
                            a_menu.inc AS inc_,
                            a_col.id,
                            a_col.col AS col_,
                            a_connect.usl  AS usl_,
                            a_connect.chk AS chk_
                            
    				FROM a_connect, a_col, a_menu
    					WHERE a_connect.a_col_id1='"._DB($col_id)."'
                        AND a_col.id=a_connect.a_col_id2
    					AND a_col.a_menu_id=a_menu.id
    	"; 

    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $mt = microtime(true)-$mt; //$data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
        
    
    if (mysql_num_rows($res_connect)==0) {echo 'Не задана таблица связи!'; break;}
    $myrow_connect = mysql_fetch_array($res_connect);
    $table_connect=$myrow_connect['inc_'];
    $col_connect=$myrow_connect['col_'];
    $usl_connect=$myrow_connect['usl_'];
    $chk_connect=$myrow_connect['chk_'];
    
    //Получаем возможные значения
    $data_=array();
    $sql_connect = "SELECT `"._DB($table_connect)."`.`"._DB($col_connect)."`,
                            (SELECT COUNT(*) FROM `"._DB($inc)."` WHERE `"._DB($inc)."`.`"._DB($col)."`=`"._DB($table_connect)."`.`id`) AS cnt_
    				FROM `"._DB($table_connect)."`
                        WHERE `"._DB($table_connect)."`.`"._DB($col_connect)."` LIKE '%"._DB($term)."%'
                        GROUP BY `"._DB($table_connect)."`.`"._DB($col_connect)."`
    					ORDER BY FIELD(`id`,'"._DB($val_def)."') DESC, `"._DB($table_connect)."`.`"._DB($col_connect)."` LIKE '"._DB($term)."%' DESC, cnt_ DESC, `"._DB($col_connect)."`
                       LIMIT 30
    "; 
    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $mt = microtime(true)-$mt; //$data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
    {
        $data_[]=$myrow_connect[0];
    }
    

    
    echo json_encode($data_);
}


//************************************************************************************************** 
// Автозаполнение
elseif ($_t=='autocomplete_max_max'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    $col_id=_GP('_col_id');
    $_col=_GP('_col');
    
    $SQL_COL="";
    if ($col_id!=''){$SQL_COL="a_col.id='"._DB($col_id)."' ";}
    if ($_col!=''){$SQL_COL="a_col.col='"._DB($_col)."' ";}
    
    $sql = "SELECT a_col.col, a_menu.inc, a_col.id
    				FROM a_menu, a_col 
    					WHERE $SQL_COL
    					AND a_col.a_menu_id=a_menu.id
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt;// $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    $myrow = mysql_fetch_array($res);
    $col=$myrow[0];
    $inc=$myrow[1];
    $col_id=$myrow[2];

                       
   
    $sql_connect = "SELECT  a_menu.id,
                            a_menu.inc AS inc_,
                            a_col.id,
                            a_col.col AS col_,
                            a_connect.usl  AS usl_,
                            a_connect.chk AS chk_,
                            a_connect.tbl
                            
    				FROM a_connect, a_col, a_menu
    					WHERE a_connect.a_col_id1='"._DB($col_id)."'

                        AND a_col.id=a_connect.a_col_id2
    					AND a_col.a_menu_id=a_menu.id
    	"; 

    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $mt = microtime(true)-$mt; //$_tdata_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
        
    
    if (mysql_num_rows($res_connect)==0) {echo 'Не задана таблица связи!'; break;}
    $myrow_connect = mysql_fetch_array($res_connect);
    $inc_connect=$myrow_connect['inc_'];//$table_connect
    $col_connect=$myrow_connect['col_'];
    $usl_connect=$myrow_connect['usl_'];
    $chk_connect=$myrow_connect['chk_'];
    
    $tbl_connect_=$inc."_".$inc_connect;
    if ($myrow_connect['tbl']!=''){
        $tbl_connect_=$myrow_connect['tbl']; 
    }
    //$tbl_connect=$myrow_connect['tbl'];


    //PID
    $names=get_column_names_with_show($inc_connect);
    $inc_connect_pid=" '0' AS pid_,";
    if (in_array('pid',$names)){//присутствует вложенность
        $inc_connect_pid="`"._DB($inc_connect)."`.`pid` AS pid_,";
    }
    //end  PID
    
    // ИСКЛЮЧЕНИЕ *************** для связанных товаров
    $WHERE_ISKL='';
    if (_GP('str_')!='' and _GP('str_')!='-1'){
        $WHERE_ISKL=" AND s_cat.id IN (SELECT s_cat_s_struktura.id1 FROM s_cat_s_struktura WHERE s_cat_s_struktura.id2='"._DB(_GP('str_'))."')";
    }
    // ИСКЛЮЧЕНИЕ ***************
    $sql_connect = "SELECT  `"._DB($inc_connect)."`.`id` AS id_,
                            $inc_connect_pid
                            `"._DB($inc_connect)."`.`"._DB($col_connect)."` AS val_,
                            (SELECT COUNT(*) FROM `".$tbl_connect_."` WHERE `".$tbl_connect_."`.`id1`=`"._DB($inc_connect)."`.`id`) AS cnt_
                            
    				FROM `"._DB($inc_connect)."`
    					WHERE `"._DB($inc_connect)."`.`"._DB($col_connect)."` LIKE '%"._DB($term)."%'
                         $WHERE_ISKL
                        GROUP BY id_
                        ORDER BY `"._DB($inc_connect)."`.`"._DB($col_connect)."` LIKE '"._DB($term)."%' DESC, cnt_ DESC, `"._DB($col_connect)."`
                       LIMIT 100
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $data_['items']=array();
    $data_['items'][0]['name']='[пусто]';
    $data_['items'][0]['text']='[пусто]';
    $data_['items'][0]['pid']='0';
    $data_['items'][0]['id']='-1';
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=1; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['val_'];
        $data_['items'][$i]['text']=$myrow_connect['val_'];
        $data_['items'][$i]['pid']=$myrow_connect['pid_'];
        $data_['items'][$i]['id']=$myrow_connect['id_'];
    } 

    echo json_encode($data_);
}
///////////////////////////////////////////////////////////////////////////////////////////
elseif ($_t=='s_cat_s_cat_s_str'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
   
                            
    $sql_connect = "SELECT  `s_struktura`.`id` AS id_,
                            `s_struktura`.`pid` AS pid_,
                            `s_struktura`.`name` AS val_
    				FROM s_struktura
    					WHERE s_struktura.name LIKE '%"._DB($term)."%'
                        GROUP BY s_struktura.id
                        ORDER BY s_struktura.sid
                      
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $data_['items']=array();
    $data_['items'][0]['name']='[Все]';
    $data_['items'][0]['text']='[Все]';
    $data_['items'][0]['pid']='0';
    $data_['items'][0]['id']='-1';
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=1; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['val_'];
        $data_['items'][$i]['text']=$myrow_connect['val_'];
        $data_['items'][$i]['pid']=$myrow_connect['pid_'];
        $data_['items'][$i]['id']=$myrow_connect['id_'];
        if ($myrow_connect['pid_']-0>0){$data_['items'][$i]['text']='- '.$data_['items'][$i]['text'];}
    } 

    echo json_encode($data_);
}


// *****************************************************************************************************************************************
// ИЗМЕНЕНИЕ ЗАПИСИ ***********************************************************************************************************************
// *****************************************************************************************************************************************
elseif ($_t=='change'){
    $data_=array();
    $inc=_GP('_inc');
    $nomer=_GP('_nomer');
    $_SESSION[$inc]['other_lastpage']=$nomer;//сохраняем последнюю страницу
    $data_['__lastpage']= $_SESSION[$inc]['other_lastpage'];
    
    if (strstr($nomer,',')==true){
        $nomer_arr=explode(',',$nomer);
        $nomer_where=" IN ('".implode("','",$nomer_arr)."')";
    }else{
        $nomer_where=" = '"._DB($nomer)."'";
    }
        
        
    $_col=_GP('_col');
        $col_where='';
        if ($_col!='-1'){$col_where=" AND a_col.col='"._DB($_col)."'";}
    
    
    //Определяем menu_id
    $sql = "SELECT IF(COUNT(*)>0,a_menu.id,'')
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."' 
    					LIMIT 1
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die($sql . '<br />'. mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0]; if ($inc_id==''){echo 'Не определен пункт меню $inc_id=""!<br />';print_r($myrow).'<br />'.$sql;exit();}
    
    //получаем набор столбцов
    $sql = "SELECT  a_col.`id`,
                    a_col.`col`,
                    a_col.`col_ru`,
                    a_col.`tip`,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
                        AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
                        $col_where
                    ORDER BY a_col.sid
                    
    "; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
       
    $data_['col']=array();
    $data_['col_ru']=array();
    $data_['tip']=array();
    $data_['chk_change']=array();
    
    $i=0;$col_m=array();
    $SQL_COL='';
    while ($myrow = mysql_fetch_array($res)) 
    {
        //print_r($myrow);
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
        $data_['chk_change'][$i]=$myrow[4];
        
        if ($data_['tip'][$i]=='Связанная таблица 1-max'){
            if ($data_['chk_change'][$i]=='1'){
                $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                        (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                        a_connect.usl,
                                        a_connect.chk
                				FROM a_connect 
                					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
                					
                	";
                 //echo $sql_connect;
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die($sql_connect.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
           
           
                $myrow_connect = mysql_fetch_array($res_connect);
                $connect_arr['inc'][$i]=$myrow_connect[0];
                $connect_arr['col'][$i]=$myrow_connect[1];
                $connect_arr['usl'][$i]=$myrow_connect[2];
                $connect_arr['chk'][$i]=$myrow_connect[3];
                
                $col_m[$i]=$data_['col'][$i];
                if ($SQL_COL!=''){$SQL_COL.=', ';}
                
                if ($connect_arr['chk'][$i]=='1'){//АВТО ADD
                    $SQL_COL.="(SELECT `"._DB($connect_arr['inc'][$i])."`.`"._DB($connect_arr['col'][$i])."` FROM `"._DB($connect_arr['inc'][$i])."` WHERE `"._DB($connect_arr['inc'][$i])."`.`id`=`"._DB($inc)."`.`"._DB($data_['col'][$i])."` LIMIT 1) AS ".$data_['col'][$i];
                }
                else{
                   
                    $SQL_COL.="`"._DB($inc)."`.`".$col_m[$i]."`";
                }
            
            }
        }
        elseif ($data_['tip'][$i]=='Связанная таблица max-max'){
            if ($data_['chk_change'][$i]=='1'){
                $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                        (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                        a_connect.usl,
                                        a_connect.chk,
                                        a_connect.tbl
                                        
                				FROM a_connect 
                					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
                					
                	";
                 //echo $sql_connect;
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die($sql_connect.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
           
           
                $myrow_connect = mysql_fetch_array($res_connect);
                $connect_arr['inc'][$i]=$myrow_connect[0];
                $connect_arr['col'][$i]=$myrow_connect[1];
                $connect_arr['usl'][$i]=$myrow_connect[2];
                $connect_arr['chk'][$i]=$myrow_connect[3];
                $connect_arr['tbl'][$i]=$myrow_connect[4];
                
                $tbl_connect_=$inc.'_'.$connect_arr['inc'][$i];
                if ($connect_arr['tbl'][$i]!=''){
                   $tbl_connect_=$connect_arr['tbl'][$i]; 
                }
                
                
                $connect_arr['col_'][$i]=$myrow[1];
                $data_['_d'][$data_['col'][$i]]=array();
                
                
                $sql_connect = "SELECT `".$tbl_connect_."`.`id2`,
                                        (SELECT `"._DB($connect_arr['inc'][$i])."`.`"._DB($connect_arr['col'][$i])."` FROM `"._DB($connect_arr['inc'][$i])."` WHERE `"._DB($connect_arr['inc'][$i])."`.`id`=`".$tbl_connect_."`.`id2` LIMIT 1)
                				FROM `".$tbl_connect_."`
                					WHERE `".$tbl_connect_."`.`id1` $nomer_where
                						
                "; 
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die($sql_connect.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
           
           
                for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
                {
                    
                    if ($connect_arr['chk'][$i]=='1'){
                        $data_['_d'][$data_['col'][$i]][$myrow_connect[0]]=$myrow_connect[1];
                    }
                    else{
                        if (!in_array($myrow_connect[0],$data_['_d'][$data_['col'][$i]])){
                            $data_['_d'][$data_['col'][$i]][]=$myrow_connect[0];
                        }
                    }
                    
                }
           }
        }
        elseif ($data_['tip'][$i]=='Функция'){
            
            $file_function='__function_'.$inc.'_'.$data_['col'][$i].'.php';
            if (file_exists($file_function)){
                include $file_function;
            }else{
                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php<br />';
            }
             
        }
        elseif ($data_['tip'][$i]=='Фото'){
            if ($data_['chk_change'][$i]=='1'){
                //***********************************
                $data_['_d'][$data_['col'][$i]]=array();
                $sql_img = "SELECT  `a_photo`.`id`,
                                    `a_photo`.`tip`,
                                    `a_photo`.`img`,
                                    `a_photo`.`comments`
                                
                                
            				FROM `a_photo`
                            
            					WHERE `a_photo`.`a_menu_id`='"._DB($inc_id)."'
                                AND `a_photo`.`row_id`  $nomer_where
                                
                                ORDER BY `a_photo`.`sid`
                              
            	"; //BY FIELD(`tip`,'Основное') DESC, 
                $mt = microtime(true);
                $res_img = mysql_query($sql_img) or die($sql_img.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_img;$data_['_sql']['time'][]=$mt;
           
           
                for ($myrow_img = mysql_fetch_array($res_img),$j=0; $myrow_img==true; $myrow_img = mysql_fetch_array($res_img),$j++)
                {
                    
                    $file='../../i/'.$inc.'/original/'.$myrow_img['img'];
                    if (file_exists($file) and $myrow_img['img']!=''){
                        $data_['_d'][$data_['col'][$i]]['info'][$j]=@getimagesize($file);
                        $data_['_d'][$data_['col'][$i]]['id'][$j]=$myrow_img['id'];
                        $data_['_d'][$data_['col'][$i]]['tip'][$j]=$myrow_img['tip'];
                        $data_['_d'][$data_['col'][$i]]['img'][$j]=$myrow_img['img'];
                        $data_['_d'][$data_['col'][$i]]['comments'][$j]=$myrow_img['comments'];
                    }else{
                        $sql_del = "DELETE 
                        			FROM a_photo 
                        				WHERE id='"._DB($myrow_img['id'])."'
                        ";
                        $mt = microtime(true);
                        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
                    }
                }
            }
        }
        else{
            if ($data_['chk_change'][$i]=='1'){
                $col_m[$i]=$data_['col'][$i];
                if ($SQL_COL!=''){$SQL_COL.=', ';}
                $SQL_COL.="`"._DB($inc)."`.`".$col_m[$i]."`";
            }
        }
        $i++;
    }
     
    
    //массив столбцов
    //echo '['.$nomer_where.']';exit();
    if ($SQL_COL!=''){
        $sql_main = "SELECT "._DB($SQL_COL)." 
                            
        				FROM `"._DB($inc)."`
                        
                        WHERE `"._DB($inc)."`.`id` $nomer_where
                        
                        LIMIT 1
        ";
        
        //echo $sql_main;
        $data_['_sql'][]=$sql_main;
        $mt = microtime(true);
        $res_main = mysql_query($sql_main) or die($sql_main.'<br />'.mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_main;$data_['_sql']['time'][]=$mt;
           
           
        $myrow_main = mysql_fetch_array($res_main);
        foreach($col_m as $col_id => $col_){
            
            //предварительная обработка
            if ($data_['tip'][$col_id]=='Дата'){
                $myrow_main[$col_]=conv_('data_from_db',$myrow_main[$col_]);
            }
            elseif ($data_['tip'][$col_id]=='Дата-время'){
                $myrow_main[$col_]=conv_('data_from_db',$myrow_main[$col_]);
            }
            elseif ($data_['tip'][$col_id]=='Телефон'){
                $myrow_main[$col_]=conv_('phone_from_db',$myrow_main[$col_]);
            }
            
            $data_['_d'][$col_]=$myrow_main[$col_];
        }
    }
    echo json_encode($data_);
}

// ****************************************************************************************************************************************
// БЫСТРОЕ ИЗМЕНЕНИЕ ЗАПИСИ **********************************************************************************************************************
// 
elseif ($_t=='quick_change'){
    $data_=array();
    $inc=_GP('_inс');$names=get_column_names_with_show($inc);
    
    $nomer=_GP('_nomer'); $nomer_arr=array();
    if ($nomer=='' and !is_array($nomer)){echo 'Не определен номер!';exit();}
    //Определяем id ячеек для изменения
   
   // if (!is_array($nomer)){
    if (strstr($nomer,',')==true){
        $nomer_arr=explode(',',$nomer);
    }else{
        $nomer_arr[0]=$nomer;
    }
    $SQL_UPDATE=implode("','",$nomer_arr);
    
    
    
    $col=_GP('col');
    $val=_GP('val');
    
    if ($col==''){echo 'Не определен столбец!';exit();}
    
    
    $sql = "SELECT IF(COUNT(*)>0,a_menu.id,'')
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."' 
    					LIMIT 1
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0]; if ($inc_id==''){echo 'Не определен пункт меню $inc_id=""!';exit();}
    
    $sql = "SELECT  `a_col`.`id`,
                    `a_col`.`tip`,
                    `a_col`.`chk_change`,
                    `a_col`.`col`
                    
                    
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
                        AND a_col.`col`='"._DB($col)."'
                        AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
    
                    ORDER BY a_col.sid
                    
    "; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $myrow = mysql_fetch_array($res);
    $col_tip=$myrow['tip'];
    $col_=$myrow['col'];
    
    $chk_change=$myrow['chk_change']; if ($chk_change=='0') {echo 'Запрещено редактирование!';exit();}
    
    //ОБРАБОТКА ЗНАЧЕНИЙ в ЗАВИСИМОСТИ ОТ ТИПА
    switch ($col_tip) { //ТИП
        case "Текст":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "Длинный текст":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "HTML-код":
            $val=$val;
        break;
        //***********************************************************************************
        case "Целое число":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "Дробное число":
            $val=str_replace(' ','',$val);
        break;
        //***********************************************************************************
        case "Стоимость":
            $val=str_replace(' ','',$val);
        break;
        //***********************************************************************************
        case "Дата":
            $val=conv_('date_to_db',$val);
        break;
        //***********************************************************************************
        case "Дата-время":
            $val=conv_('date_to_db',$val);
        break;
        //***********************************************************************************
        case "Телефон":
            $val=conv_('phone_to_db',$val);
        break;
        //***********************************************************************************
        case "Email":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "Связанная таблица 1-max":
            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                    a_connect.usl,
                                    a_connect.chk
            				FROM a_connect 
            					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
            					
            	";
             //echo $sql_connect;.
             
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    
             
            $myrow_connect = mysql_fetch_array($res_connect);
            $connect_arr['inc']=$myrow_connect[0];
            $connect_arr['col']=$myrow_connect[1];
            $connect_arr['usl']=$myrow_connect[2];
            $connect_arr['chk']=$myrow_connect[3];
            if ($connect_arr['chk']=='1'){//авто-добавление
                if (strip_tags($val)!=''){
                    
                    $sql_connect = "SELECT IF(COUNT(*)>0,`"._DB($connect_arr['inc'])."`.`id`,'')
                    				FROM `"._DB($connect_arr['inc'])."` 
                    					WHERE `"._DB($connect_arr['col'])."`='"._DB(strip_tags($val))."'
                    	"; 
                    
                    $res_connect = mysql_query($sql_connect);if (!$res_connect){echo $sql_connect;exit();}
                    $myrow_connect = mysql_fetch_array($res_connect);
                    $val_id=$myrow_connect[0];
                    
                    if ($val_id==''){
                        $sql_connect = "INSERT into `"._DB($connect_arr['inc'])."` (
                                				`"._DB($connect_arr['col'])."`
                                			) VALUES (
                                				'"._DB(strip_tags($val))."'
                                )";
                        $mt = microtime(true);
                        $res_connect = mysql_query($sql_connect) or die(mysql_error());
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                        
                        if (!$res_connect){echo $sql_connect.'<br />'.mysql_error();exit();}
                        else{$val_id = mysql_insert_id();}
                    }
                    $val=$val_id;
                }
            }
        break;
        //***********************************************************************************
        case "Связанная таблица max-max":
            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                    a_connect.usl,
                                    a_connect.chk,
                                    a_connect.tbl
            				FROM a_connect 
            					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
            					
            	";
             //echo $sql_connect;
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
            
            $myrow_connect = mysql_fetch_array($res_connect);
            $connect_arr['inc']=$myrow_connect[0];
            $connect_arr['col']=$myrow_connect[1];
            $connect_arr['usl']=$myrow_connect[2];
            $connect_arr['chk']=$myrow_connect[3];
            $connect_arr['tbl']=$myrow_connect[4];
            
            $tbl_connect_=$inc.'_'.$connect_arr['inc'];
            if ($connect_arr['tbl']!=''){
               $tbl_connect_=$connect_arr['tbl']; 
            }
            
            
            //удаляем старые записи 
            $sql_connect = "DELETE 
            			FROM `".$tbl_connect_."` 
            				WHERE `".$tbl_connect_."`.`id1` IN ('".$SQL_UPDATE."')
            ";
            
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
            
            if (!$res_connect){echo $sql_connect;exit();}
            
            //добавляем новые записи
            $val_arr=array();
            if (!is_array($val)){
                if ($val!=''){
                    $val_arr[0]=$val;
                }
            }else{$val_arr=$val;}
            
            $SQL_INS="";
            if (count($val_arr)>0){
                foreach($val_arr as $key => $val_id){
                    foreach($nomer_arr as $key => $nom_){
                        if ($val_id>0){
                            if ($SQL_INS!=''){$SQL_INS.=', ';}
                            $SQL_INS.="(
                            				'"._DB($nom_)."',
                            				'"._DB($val_id)."'
                            )";
                        }
                    }
                }
            }
            if ($SQL_INS!=''){
                $sql_connect = "INSERT into `".$tbl_connect_."`  (
                    				id1,
                    				id2
                    			) VALUES $SQL_INS";
                $data_['sql_'][]=$sql_connect;
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
            
                if (!$res_connect){echo $sql_connect;$new_id=0;}

                
            }else{
                $data_['sql_'][]='$SQL_INS = ""';
            }
            
            if (in_array('data_change',$names)){
                $sql_upp = "
                		UPDATE `".$inc."`
                			SET  
                				data_change='"._DB(date('Y-m-d H:i:s'))."'
                		
                		WHERE id IN ('".$SQL_UPDATE."')
                ";
                $mt = microtime(true);
                $res_upp = mysql_query($sql_upp) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
            
                if(!$res_upp){echo $sql_upp;exit();}
            }
            
            unset($val);
        break;
        //***********************************************************************************
        case "Функция":
            
        break;
        //***********************************************************************************
        case "chk":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "enum":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "Цвет":
            $val=strip_tags($val);
        break;
        //***********************************************************************************
        case "Ссылка":
            $val=strip_tags($val);
        break;
    }//end ТИП
        
        
      // *********************************************************************************
    // ********** ЦЕНА *******************************************************
    // *********************************************************************************
    if ($inc=='s_cat' and $col_=='price'){
        $sql = "SELECT DISTINCT id, price
        				FROM s_cat
        					WHERE `s_cat`.`id` IN ('".$SQL_UPDATE."')";
       $mt = microtime(true);
       $res = mysql_query($sql);if (!$res){echo $sql;exit();}
       $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
       
       for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
       {
       
            if (str_replace(array('.00',' '),'',_GP('val'))!=str_replace(array('.00',' '),'',$myrow[1])){
                $sql_ins = "INSERT into l_price_history (
                				s_cat_id,
                				price
                			) VALUES (
                				'"._DB($myrow[0])."',
                				'"._DB($myrow[1])."'
                )";
                
                $mt = microtime(true);
                $res_ins = mysql_query($sql_ins);
                	if (!$res_ins){echo $sql_ins;exit();}
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
                
            }
       } 
       
    }
        
        
    //формируем SQL
    if ($SQL_UPDATE!='' and isset($val)){
        $sql_upp="UPDATE `"._DB($inc)."` SET `"._DB($col)."`='"._DB($val)."' WHERE `id` IN ('".$SQL_UPDATE."')";
        
        $mt = microtime(true);
        $res_upp = mysql_query($sql_upp) or die(mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
            
    }
    //доп обработка URL и даты изменения
    $arr_=change_row($inc,$col,$nomer_arr);
    
    if (isset($arr_) and is_array($arr_)){
        $data_new=$data_;
        $data_new['2']=$arr_;
        unset($data_);$data_=$data_new;
    }
    
    echo json_encode($data_);
}
// ****************************************************************************************************************************************
// УДАЛЕНИЕ ЗАПИСИ **********************************************************************************************************************
// 
elseif ($_t=='delete'){
    $data_=array();
    $inc=_GP('_inс');
    
    $nomer=_GP('_nomer'); $nomer_arr=array();
    if ($nomer==''){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='Не определен номер!';echo 'Не определен номер!';exit();}

    $SQL_UPDATE=str_replace(",","','",$nomer);

    
    
    //Определяем menu_id
    $sql = "SELECT IF(COUNT(*)>0,a_menu.id,'')
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."' 
    					LIMIT 1
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
          
   
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0]; if ($inc_id==''){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='Не определен пункт меню $inc_id=""!';echo 'Не определен пункт меню $inc_id=""!<br />';print_r($myrow).'<br />'.$sql;exit();}
    
    //получаем набор столбцов
    $sql = "SELECT DISTINCT
                    a_col.`id`,
                    a_col.`col`,
                    a_col.`tip`
                    
    				FROM a_col
    					WHERE a_col.a_menu_id='"._DB($inc_id)."'
                        
                    ORDER BY a_col.sid
                    
    ";
    
    $mt = microtime(true);
    $res0 = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;

    if(!$res){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='$sql: '.$sql;}
    $data_['col']=array();
    $data_['tip']=array();
    
    $i=0;$col_m=array();
    $SQL_COL='';
    while ($myrow = mysql_fetch_array($res0)) 
    {
      
        $data_['col'][$i]=$myrow[1];
        $data_['tip'][$i]=$myrow[2];
        
        if ($data_['tip'][$i]=='Связанная таблица max-max'){
            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                    a_connect.tbl
            				FROM a_connect 
            					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
            					
            	";
             //echo $sql_connect;
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;

                if(!$res_connect){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='$sql_connect: '.$sql_connect;}
            $myrow_connect = mysql_fetch_array($res_connect);
            $connect_arr['inc'][$i]=$myrow_connect[0];
            $connect_arr['col'][$i]=$myrow_connect[1];
            $connect_arr['tbl'][$i]=$myrow_connect[2];
           
            $tbl_connect_=$inc.'_'.$connect_arr['inc'][$i];
            if ($connect_arr['tbl'][$i]!=''){
               $tbl_connect_=$connect_arr['tbl'][$i]; 
            }
            
            
            $sql_connect = "DELETE
            				FROM `".$tbl_connect_."`
            					WHERE `".$tbl_connect_."`.`id1` IN ('".$SQL_UPDATE."')
            						
            ";
            $data_['_sql'][]=$sql_connect;
            $mt = microtime(true);
            $res_connect = mysql_query($sql_connect) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;

                if(!$res_connect){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='$sql_connect: '.$sql_connect;}
            
        }
        elseif ($data_['tip'][$i]=='Функция'){
            $file_function='__function_'.$inc.'_'.$data_['col'][$i].'.php';
            //echo '***'.$file_function;
            if (file_exists($file_function)){
                    include $file_function;
            }else{
                $_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php';
                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php<br />';
            }
             
        }
        elseif ($data_['tip'][$i]=='Фото'){
            
            $sql = "SELECT `a_photo`.`img`
            				FROM `a_photo`
            					WHERE `a_photo`.`a_menu_id`='"._DB($inc_id)."'
                                AND `a_photo`.`row_id` IN ('".$SQL_UPDATE."')
            "; 
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;

                if(!$res){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='$sql: '.$sql;}
                
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $img_='../../i/'.$inc.'/original/'.$myrow[0];
                if (file_exists($img_) and $myrow[0]!=''){
                    $data_['_unlink'][]=$img_;
                    if (!unlink($img_)){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='Ошибка удаления изображения: '.$img_; echo 'Ошибка удаления изображения: '.$img_.'<br />';}
                }
                
                $img_='../../i/'.$inc.'/small/'.$myrow[0];
                if (file_exists($img_) and $myrow[0]!=''){
                    $data_['_unlink'][]=$img_;
                    if (!unlink($img_)){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='Ошибка удаления изображения: '.$img_; echo 'Ошибка удаления изображения: '.$img_.'<br />';}
                }
            }
            
            
            $sql_img = "DELETE
            				FROM `a_photo`
            					WHERE `a_photo`.`a_menu_id`='"._DB($inc_id)."'
                                AND `a_photo`.`row_id` IN ('".$SQL_UPDATE."')
            						
            ";
            $mt = microtime(true);
            $res_del = mysql_query($sql_img) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_img;$data_['_sql']['time'][]=$mt;
            
                if(!$res_del){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='$sql_img: '.$sql_img;}
        }
    }
    
    $sql = "DELETE 
    			FROM `"._DB($inc)."`
    				WHERE `"._DB($inc)."`.`id` IN ('".$SQL_UPDATE."')
    ";
    $mt = microtime(true);
    $res_del = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
    if(!$res_del){$_SESSION['error']['other_ajax__delete_'.date('Y-m-d H:i:s')]='$sql: '.$sql;}
        
    echo json_encode($data_);
}
// ****************************************************************************************************************************************
// СОХРАНЕНИЕ ЗАПИСИ **********************************************************************************************************************
// 
elseif ($_t=='save'){

    $inc=_GP('_inс'); 
        if ($inc==''){echo 'Пустая переменная $inc!';exit();}
        $names=get_column_names_with_show($inc);
    $_col=_GP('_col'); 
        $col_where='';
        if ($_col!='-1' and $_col!=''){$col_where=" AND a_col.col='"._DB($_col)."'";}
    $nomer=_GP('_nomer','');
        $nomer_arr=array();
      
        if (strstr($nomer,',')==true){
            $nomer_arr = explode(",", $nomer);
            foreach($nomer_arr as $key_nom =>$nomer_){
                $nomer_arr[$key_nom]=trim($nomer_);
            }
        }
    $data_=array();
    
    
    //Получаем id пункта меню
    $sql = "SELECT IF(COUNT(*)>0,a_menu.id,'')
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."' 
    					LIMIT 1
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0]; if ($inc_id==''){echo 'Не определен пункт меню $inc_id=""!';exit();}
    
    
    
    //Создаем новую запись
    $data_['new_']=0;
    if ($nomer==''){
        $a_admin_id_create0='';
        $a_admin_id_create1=''; 
        if (in_array('a_admin_id_create',$names)){
            $a_admin_id_create0=', a_admin_id_create';
            $a_admin_id_create1=", '"._DB($a_admin_id_cur)."'"; 
        }
                
        
        if (in_array('pid',$names)){
            $__pid=_GP('__pid',0);
            
            // ОБРАБОТКА SID
            if (in_array('sid',$names)){ // с sid
                if ($__pid==0){
                    $sql = "SELECT MAX(sid)
                    				FROM `"._DB($inc)."`
                    	"; 
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
                  
                    $myrow = mysql_fetch_array($res);
                    $sid=$myrow[0]+1;
                }
                else{
                    $sql = "SELECT MAX(sid)
                    				FROM `"._DB($inc)."`
                    					WHERE `"._DB($inc)."`.pid='"._DB($__pid)."' 
                    					
                    	"; 
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
                    $myrow = mysql_fetch_array($res);
                    $sid=$myrow[0]+1;
                    
                    $sql = "
                    		UPDATE `"._DB($inc)."` 
                    			SET  
                    				sid=sid+1
                    		
                    		WHERE sid>='"._DB($sid)."'
                    ";
                    $mt = microtime(true);
                    mysql_query($sql) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
                }
                $sql = "INSERT into `"._DB($inc)."` (
                				id,
                				pid,
                                sid
                                $a_admin_id_create0
                			) VALUES (
                				NULL,
                				'"._DB($__pid)."',
                                '"._DB($sid)."'
                                $a_admin_id_create1
                )";
                $mt = microtime(true);
                if (!mysql_query($sql)){echo mysql_error().'<br />'.$sql;exit();}
                else{$nomer = mysql_insert_id(); }
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
                
                
            
            } //end с sid
            else{ //без sid
                $sql = "INSERT into `"._DB($inc)."` (
                				id,
                				pid
                                $a_admin_id_create0
                			) VALUES (
                				NULL,
                				'"._DB($__pid)."'
                                $a_admin_id_create1
                )";
                $mt = microtime(true);
                if (!mysql_query($sql)){echo mysql_error().'<br />'.$sql;exit();}
                else{$nomer = mysql_insert_id(); }
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
            }//end без sid
            
            
            
            
        }else{ //без pid
           $sql = "INSERT into `"._DB($inc)."` (
            				id
                            $a_admin_id_create0
            			) VALUES (
            				NULL
                            $a_admin_id_create1
            )";
            $mt = microtime(true);
            if (!mysql_query($sql)){echo mysql_error().'<br />'.$sql;exit();}
            else{$nomer = mysql_insert_id(); }
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      
        }
        
        $data_['new_']=$nomer; //номер
    }
    
    if (count($nomer_arr)==0){
        $nomer_arr[0]=$nomer;
    }
    
    
    
    // получаем массив всех изменяемых столбцов
    $data_['col']=array();
    $data_['col_ru']=array();
    $data_['tip']=array();
    $data_['chk_change']=array();
    
    $col_m=array();
    $table_m=array();
    
    $sql = "SELECT  a_col.`id`,
                    a_col.`col`,
                    a_col.`col_ru`,
                    a_col.`tip`,
                    a_col.chk_change
                    
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
                        AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
                        $col_where
                    ORDER BY a_col.sid
                    
    "; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
   
    $SQL_UPDATE="";$file_function=array();
    while ($myrow = mysql_fetch_array($res)) 
    {
        $data_['col'][$myrow[0]]=$myrow[1];
        $data_['col_ru'][$myrow[0]]=$myrow[2];
        $data_['tip'][$myrow[0]]=$myrow[3];
        $data_['chk_change'][$myrow[0]]=$myrow[4];
        //получаем значение по умолчанию
        $val_def='';
        if ($myrow[1]!='' and $inc!=''){
            $sql_="SELECT IF(COUNT(*)>0,`COLUMN_DEFAULT`,'') 
                        FROM `information_schema`.`COLUMNS` 
                            WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                            AND `TABLE_NAME`='"._DB($inc)."' 
                            AND `COLUMN_NAME`='"._DB($myrow[1])."'
                "; 
            $mt = microtime(true);
            $res_def = mysql_query($sql_) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_;$data_['_sql']['time'][]=$mt;
      
			$myrow_def = mysql_fetch_array($res_def);
			$val_def=$myrow_def[0]; 
        }
        if ($val_def=='CURRENT_TIMESTAMP'){$val_def=date('d.m.Y H:i:s');}                      
            
        $val_=_GP($data_['col'][$myrow[0]]);
        // **********************************************************************************
        switch ($data_['tip'][$myrow[0]]) { //ТИП
        case "Текст":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
            }
        break;
        //***********************************************************************************
        case "Длинный текст":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
            }
        break;
        //***********************************************************************************
        case "HTML-код":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
            }
        break;
        //***********************************************************************************
        case "Целое число":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
            }
        break;
        //***********************************************************************************
        case "Дробное число":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
            }
        break;
        //***********************************************************************************
        case "Стоимость":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(conv_('price_to_db',$val_))."'";
            }
        break;
        //***********************************************************************************
        case "Дата":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if (preg_replace('/[\D]{1,}/s', '',$val_)>0){$val_=conv_('data_to_db',$val_);}
                else{$val_='';}
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
            }
        break;
        //***********************************************************************************
        case "Дата-время":
            if ($data_['col'][$myrow[0]]!='data_change' or _GP('data_change')!=''){//дата изменения
                if ($data_['chk_change'][$myrow[0]]=='1'){
                    if (preg_replace('/[\D]{1,}/s', '',$val_)>0){$val_=conv_('data_to_db',$val_);}
                    else{$val_='';}
                    
                    if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}    
                    $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
                }
            }
        break;
        //***********************************************************************************
        case "Телефон":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                $val_=conv_('phone_to_db',$val_);
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
            }
        break;
        //***********************************************************************************
        case "Email":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
            }
        break;
        //***********************************************************************************
        case "Связанная таблица 1-max":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                $sql_connect = "SELECT  a_menu.id,
                                        a_menu.inc AS inc_,
                                        a_col.id,
                                        a_col.col AS col_,
                                        a_connect.usl  AS usl_,
                                        a_connect.chk AS chk_
                                        
                				FROM a_connect, a_col, a_menu
                					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
                                    AND a_col.id=a_connect.a_col_id2
                					AND a_col.a_menu_id=a_menu.id
                	"; 
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
          
                
                if (mysql_num_rows($res_connect)==0) {echo 'Не задана таблица связи!'; break;}
                $myrow_connect = mysql_fetch_array($res_connect);
                $table_connect=$myrow_connect['inc_'];
                $col_connect=$myrow_connect['col_'];
                $usl_connect=$myrow_connect['usl_'];
                    if ($usl_connect!=''){
                        $usl_connect=' WHERE '.$usl_connect;
                    }
                $chk_connect=$myrow_connect['chk_']; 
                
                /**/
                //comments: Добавляется перед вставкой значения
                if ($chk_connect=='1'){//авто-добавление
                    if ($val_!=''){
                        
                        $sql_connect = "SELECT IF(COUNT(*)>0,`"._DB($table_connect)."`.`id`,'')
                        				FROM `"._DB($table_connect)."` 
                        					WHERE `"._DB($table_connect)."`.`"._DB($col_connect)."`='"._DB(strip_tags($val_))."'
                        				
                        	"; 
                       
                        $mt = microtime(true);
                        $res_connect = mysql_query($sql_connect);if (!$res_connect){echo $sql_connect;exit();}
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                        $myrow_connect = mysql_fetch_array($res_connect);
                        $val_id=$myrow_connect[0];
                        if ($val_id==''){
                            //ДОБАВЛЯЕМ ЗНАЧЕНИЕ В СВЯЗАННУЮ ТАБЛИЦУ
                            $sql_connect = "INSERT into `"._DB($table_connect)."` (
                            				`"._DB($col_connect)."`
                            			) VALUES (
                            				'"._DB(strip_tags($val_))."'
                            )";
                            
                            $mt = microtime(true);
                            $res_connect = mysql_query($sql_connect) or die(mysql_error());
                            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                  
                            if (!$res_connect){echo $sql_connect.'<br />'.mysql_error();exit();}
                            else{$val_ = mysql_insert_id();}
                            
                        }else{
                            $val_=$val_id;
                        }
                    }
                }
                //селектор
                
                
                if ($val_!=''){
                    if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
                    $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB(strip_tags($val_))."'";
                }
           }
        break;
        //***********************************************************************************
        case "Связанная таблица max-max":
            if ($data_['chk_change'][$myrow[0]]=='1'){
                $sql_connect = "SELECT  a_menu.id,
                                        a_menu.inc AS inc_,
                                        a_col.id AS col_id_,
                                        a_col.col AS col_,
                                        a_connect.usl  AS usl_,
                                        a_connect.chk AS chk_,
                                        a_connect.tbl,
                                        a_connect.id AS a_connect_id
                                        
                				FROM a_connect, a_col, a_menu
                					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
                                    AND a_col.id=a_connect.a_col_id2
                					AND a_col.a_menu_id=a_menu.id
                	"; 
               
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
          
          
                if (mysql_num_rows($res_connect)==0) {echo 'Не задана таблица связи!'; break;}
                $myrow_connect = mysql_fetch_array($res_connect);
                $inc_connect=$myrow_connect['inc_'];//$table_connect
                $col_connect=$myrow_connect['col_'];
                $col_id_connect=$myrow_connect['col_'].'_'.$myrow_connect['a_connect_id'];
                
                $tbl_connect_=$inc.'_'.$inc_connect;
                if ($myrow_connect['tbl']!=''){
                   $tbl_connect_=$myrow_connect['tbl']; 
                }
                
                $val_arr=array();
                $val_arr=_GP($col_id_connect,array()); if ($val_arr!=''){if (!is_array($val_arr)){$a=$val_arr; unset($val_arr);$val_arr[]=$a;}}
                //print_rf($val_arr);
                //ФОРМИРУЕМ НОВЫЕ КЛЮЧИ
                $VALUES_="";
                if (isset($val_arr) and is_array($val_arr) and count($val_arr)>0){
                
                    foreach($val_arr as $key => $id2){
                        
                        foreach($nomer_arr as $key_nom => $nomer_){
                            if ($VALUES_!=''){$VALUES_.=", \n";}
                            $VALUES_.="('"._DB($nomer_)."', '"._DB($id2)."')";
                           
                        }
                        
                        
                    }
                }else{
                    if (!isset($val_arr) or !is_array($val_arr) ){
                        echo 'error $val_arr<br />';exit();
                    }
                }
                
                //удаляем старые ключи
                $sql_connect = "DELETE 
                			FROM `".$tbl_connect_."` 
                				WHERE id1 IN ('".implode("','",$nomer_arr)."')
                ";
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
          
                if (!$res_connect){echo $sql_connect.'<br />'.mysql_error();exit();}
                
                
                if ($VALUES_!=''){
                    //добавляем новые ключи
                    $sql_connect = "INSERT into `".$tbl_connect_."` (
                    				id1,
                    				id2
                    			) VALUES 
                                    $VALUES_
                    ";
                    
                    $mt = microtime(true);
                    $res_connect = mysql_query($sql_connect) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
              
          
                    if (!$res_connect){echo $sql_connect.'<br />'.mysql_error();exit();}
                }
            }
        break;
        //***********************************************************************************
        case "Функция":
        
            $file_f='__function_'.$inc.'_'.$data_['col'][$myrow[0]].'.php';
             // echo '+++';exit;
            if (file_exists($file_f)){
                    $file_function[$myrow[0]]=$file_f;
                   //include_once $file_function;
                
            }else{
                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$myrow[0]].'.php<br />';
            }
            
        break;
        //***********************************************************************************
        case "chk":
        if ($data_['chk_change'][$myrow[0]]=='1'){
            if ($val_!='1'){$val_='0';}
            if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
            $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
        }
        break;
        //***********************************************************************************
        case "enum":
        if ($data_['chk_change'][$myrow[0]]=='1'){
            if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
            $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
        }
        break;
        //***********************************************************************************
        case "Цвет":
        if ($data_['chk_change'][$myrow[0]]=='1'){
            if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
            $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
        }
        break;
        //***********************************************************************************
        case "Ссылка":
        if ($data_['chk_change'][$myrow[0]]=='1'){
            if ($SQL_UPDATE!=''){$SQL_UPDATE.=", ";}
            $SQL_UPDATE.="`".$inc."`.`".$data_['col'][$myrow[0]]."` = '"._DB($val_)."'";
        }
        break;
        }//end ТИП
        
    }
    
    // *********************************************************************************
    // ********** ЦЕНА *******************************************************
    // *********************************************************************************
    if ($inc=='s_cat' and in_array('price',$data_['col'])){
        $sql = "SELECT DISTINCT id, price
        				FROM s_cat
        					WHERE `s_cat`.`id` IN ('".implode("','",$nomer_arr)."')
        	";
       $mt = microtime(true);
       $res = mysql_query($sql);if (!$res){echo $sql;exit();}
       $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
       
       for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
       {
            if (str_replace(array('.00',' '),'',_GP('price'))!=str_replace(array('.00',' '),'',$myrow[1])){
                $sql_ins = "INSERT into l_price_history (
                				s_cat_id,
                				price
                			) VALUES (
                				'"._DB($myrow[0])."',
                				'"._DB($myrow[1])."'
                )";
                
                $mt = microtime(true);
                $res_ins = mysql_query($sql_ins);
                	if (!$res_ins){echo $sql_ins;exit();}
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
                
            }
       } 
       
    }

    //СОХРАНЯЕМ ИЗМЕНЕНИЯ
    if ($SQL_UPDATE!=''){
        $SQL_UPDATE="UPDATE `"._DB($inc)."` SET ".$SQL_UPDATE." WHERE `"._DB($inc)."`.`id` IN ('".implode("','",$nomer_arr)."')";
        //echo $SQL_UPDATE;exit();
        $mt = microtime(true);
        $res = mysql_query($SQL_UPDATE) or die(mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$SQL_UPDATE;$data_['_sql']['time'][]=$mt;
              
    }
    
    //фото
    $photo_img=_GP('photo_img',array());
    $photo_tip=_GP('photo_tip',array());
    $photo_desc=_GP('photo_desc',array());
    if (!is_array($photo_img)){$a=$photo_img;unset($photo_img);$photo_img[0]=$a;}
    if (!is_array($photo_tip)){$a=$photo_tip;unset($photo_tip);$photo_tip[0]=$a;}
    if (!is_array($photo_desc)){$a=$photo_desc;unset($photo_desc);$photo_desc[0]=$a;}
    
    //проверяем директории на существование
    if (!file_exists('../../i/')){mkdir('../../i/',0777);}
    if (!file_exists('../../i/'.$inc.'/')){mkdir('../../i/'.$inc.'/',0777);}
    if (!file_exists('../../i/'.$inc.'/original/')){mkdir('../../i/'.$inc.'/original/',0777);}
    if (!file_exists('../../i/'.$inc.'/small/')){mkdir('../../i/'.$inc.'/small/',0777);}
    
    
    if (count($nomer_arr)==1){//единичное редактирование - не работает для массового
         
         if ($data_['new_']==0){//при изменении
            //проверяем старые фото
            $sql_img = "SELECT      id,
                                    tip, 
                                    img,
                                    comments
                                
            				FROM a_photo 
            					WHERE a_menu_id='"._DB($inc_id)."'
                                AND row_id='"._DB($nomer)."'
            						ORDER BY sid
            ";
            
           
            $mt = microtime(true);
            $res_img = mysql_query($sql_img) or die(mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_img;$data_['_sql']['time'][]=$mt;
                    
            for ($myrow_img = mysql_fetch_array($res_img); $myrow_img==true; $myrow_img = mysql_fetch_array($res_img))
            {
                $img_='../../i/'.$inc.'/original/'.$myrow_img[2];
                if (file_exists($img_) and $myrow_img[2]!=''){//файла нет
                    if (!in_array($myrow_img[2],$photo_img)){ //нет в массиве новых фото -> удаляем
                        
                        if(!unlink($img_)){echo 'Ошибка удаления файла '.$img_;exit();}
                        
                        
                        
                        //чистим записи в БД в случае отсутствия файла изображения
                        $sql_del = "DELETE 
                        			FROM a_photo 
                        				WHERE id='"._DB($myrow_img[0])."'
                        ";
                        $mt = microtime(true);
                        mysql_query($sql_del) or die(mysql_error());
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
            
                    }else{//если файл есть, обновляем информацию
                        
                        $key = array_search($myrow_img[2], $photo_img);
                        $sql_upp_img = "
                        		UPDATE a_photo 
                        			SET  
                        				sid='"._DB($key)."',
                                        tip='"._DB($photo_tip[$key])."',
                        				comments='"._DB($photo_desc[$key])."'
                        		
                        		WHERE id='"._DB($myrow_img[0])."'
                        ";
                        $mt = microtime(true);
                        mysql_query($sql_upp_img) or die(mysql_error());
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_upp_img;$data_['_sql']['time'][]=$mt;
            
                        unset($photo_img[$key],$photo_tip[$key],$photo_desc[$key]);
                        $data_['img'][$key]=$myrow_img[2];
                    }
                }else{
                    //чистим записи в БД в случае отсутствия файла изображения
                    $sql_del = "DELETE 
                    			FROM a_photo 
                    				WHERE id='"._DB($myrow_img[0])."'
                    ";
                   
                    $mt = microtime(true);
                    mysql_query($sql_del) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
            
                    
                }
            }
           
         }//end при изменении
        
        //добавляем новые фото
        $data_['img']=array();
        if (count($photo_img)>0){
            
            foreach($photo_img as $key => $img){
                if ($img!=''){
                    $copy_chk=0;
                    $img_='../../i/'.$inc.'/temp/'.$img;
                        //при копировании
                        if (!file_exists($img_)){$img_='../../i/'.$inc.'/original/'.$img;$copy_chk=1;}
                    $ext=preg_replace("/.*?\./", '', $img);
                    
                    if (file_exists($img_)){
                        //формируем новое название
                        if(in_array('name',$names) and _GP('name')!=''){
                           
                            $file_name=ru_us(_GP('name')).'_'.ru_us($photo_tip[$key]).'.'.$ext;
                            while(file_exists('../../i/'.$inc.'/original/'.$file_name)){
                                //echo '+1';
                                $file_name=ru_us(_GP('name')).'_'.ru_us($photo_tip[$key]).'_'.date('YmdHis').'_'.rand(100,999).'.'.$ext;
                            }
                        }else{
                            $file_name=$img;
                            while(file_exists('../../i/'.$inc.'/original/'.$file_name)){
                                $file_name=str_replace($ext,'',$img).'_'.ru_us($photo_tip[$key]).'_'.date('YmdHis').'_'.rand(100,999).'.'.$ext;
                            }
                        }
                        
                        $data_['img'][$key]=$file_name;
                        
                        //копируем файл
                        if (!copy($img_,'../../i/'.$inc.'/original/'.$file_name)){echo 'Ошибка копирования файла: '.$img_;exit();}
                        else{
                            //создаем миниатюру
                            $size_arr= getimagesize($img_);
                            $w_orig=$size_arr[0];
                            $h_orig=$size_arr[1];
                            smart_resize_image( $img_, '../../i/'.$inc.'/small/'.$file_name, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
        
                            
                            //удаляем временный файл
                            if ($copy_chk==0){
                                if (!unlink($img_)){echo 'Ошибка удаления временного файла: "'.$img_.'"';exit();}
                            }
                        }
                        
                        //записываем данные в базу
                        $sql_ins_img = "INSERT into a_photo (
                                        a_menu_id,
                                        sid,
                                        row_id,
                        				img,
                        				tip,
                                        comments
                        			) VALUES (
                        				'"._DB($inc_id)."',
                                        '"._DB($key)."',
                        				'"._DB($nomer)."',
                                        '"._DB($file_name)."',
                                        '"._DB($photo_tip[$key])."',
                                        '"._DB($photo_desc[$key])."'
                        )";
                        
                        $mt = microtime(true);
                        mysql_query($sql_ins_img) or die(mysql_error());
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_ins_img;$data_['_sql']['time'][]=$mt;
            
                        
                    }
                    else{//файл отсутствует
                        echo 'файл отсутствует $img_: '.$img_;exit();
                    }
                }else{
                    echo 'Пустое изображение в массиве $photo_img: ';print_r($photo_img);exit();
                }
            }
        }//end фото
        
        //Удаляем временные файлы
        $targetDir = '../../i/'.$inc.'/temp';
        if (is_dir($targetDir)){
            //чистим папку временных файлов
            $maxFileAge = 1*3600; // час
            RemoveDirTime($targetDir,$maxFileAge);
        }
        
       
        
        
        
        
    }//end единичное редактирование - не работает для массового
    
        //ПОДКЛЮЧАЕМ ОБРАБОТКУ ФУНКЦИЙ
        foreach($file_function as $key => $file_f){
          
            @include_once $file_f;
        }
    
    //доп обработка URL и даты изменения
    $arr_=change_row($inc,$_col,$nomer_arr);
    if (isset($arr_) and is_array($arr_)){
        if (isset($arr_) and is_array($arr_)){
            $data_new=$data_;
            $data_new['2']=$arr_;
            unset($data_);$data_=$data_new;
        }
    }
        
    echo json_encode($data_);

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//ПАРСИНГ
elseif ($_t=='parsing'){
    $data_=array();
    $data_['add']=0;
    $data_['upp']=0;
    $project_id=0;//маркер нового проекта >0 
    
    $inc=_GP('_inс');
    include '../class/simple_html_dom.php';
    
    $html = new simple_html_dom();

    //получаем значения необходимые для парсинга
    $link_=_GP('_parsing__link'); if ($link_==''){echo 'Отсутствует ссылка для парсинга! $link_="'.$link_.'"';exit();}
        $link_=str_replace('https://','http://',$link_);
        preg_match("/^(http:\/\/)?([^\/]+)/i",$link_, $matches);
        
        $data_['host']='';
        if(isset($matches[0]) and $matches[0]!='') {$data_['host']=$matches[0];}
        else{echo 'Не определен хост: '.$link_;exit();}
    
    $page_=_GP('_parsing__page'); //if ($page_==''){echo 'Отсутствует селектор следующей страницы! $page_="'.$page_.'"';exit();}
    $block_=_GP('_parsing__block'); if ($block_==''){echo 'Отсутствует селектор блока товара! $block_="'.$block_.'"';exit();}
    $link_item_=_GP('_parsing__link_item'); //if ($link_item_==''){echo 'Отсутствует селектор ссылки на карточку товара! $link_item_="'.$link_item_.'"';exit();}
    $parsing__if_=_GP('_parsing__if'); //if ($parsing__if_==''){echo 'Отсутствует проверка на соответствие, т.е. подходит весь товар! $parsing__if_="'.$parsing__if_.'"';exit();}
    $parsing__work_=_GP('_parsing__work'); //if ($parsing__work_==''){echo 'Добавление в базу или тест! $parsing__work_="'.$parsing__work_.'"';exit();}
    $parsing__main_=_GP('_parsing__main'); if ($parsing__main_==''){echo 'Основной столбец для сравнения! $parsing__main_="'.$parsing__main_.'"';exit();}
        if (is_array($parsing__main_)){echo 'Закройте дубликат окна!';exit();}
    $parsing__update_=_GP('_parsing__update'); //if ($parsing__update_==''){echo 'Не оновлять старые значения! $parsing__main_="'.$parsing__update_.'"';exit();}
    $parsing__sleep_=_GP('_parsing__sleep');
        
    
    
    /*
    
    $html = file_get_html($link_);
    */
    $html_txt=readPage($link_);//получаем данные страницы
   
    if ($html_txt!=''){
      // echo $html_txt;exit();
        $html=str_get_html($html_txt);
        unset($html_txt);
    }else{
        echo $html_txt;exit();
    }
    
    
    if (!$html){
        //$html =  file_get_html($link_);
        if (!$html){echo 'Не удалось получить содержимое страницы $html=file_get_html("'.$link_.'")!';exit();}
    }
    
    //получаем ссылку на следующую страницу
    $data_['link_']='';
    if ($page_!=''){
        //echo '1<br />'.$page_.'<br />';
        eval($page_);
        //echo '2<br />';
        if (isset($a_links) and $a_links!=''){
            preg_match("/^(http:\/\/)?([^\/]+)/i",$a_links, $matches);
            if (isset($matches[0]) and $matches[0]!=''){
                $a_links=str_replace($matches[0],'',$a_links);
            }
            if (substr($a_links,0,1)!='/'){$data_['link_']=$data_['host'].'/'.$a_links;}
            else{$data_['link_']=$data_['host'].$a_links;}
        }else{
            if (!isset($a_links)){
                echo 'Не верно указан селектор на следующую страницу!<br /> eval($page_); $page_='.$page_;
                exit();
            }
        }
    }
    
    //массив столбцов
    $names=array();$names=get_column_names_with_show($inc);
    if (!isset($names) or !is_array($names) or count($names)==0){
        echo 'В таблице `'.$inc.'` нет столбцов!';exit();
    }
    
    $sql = "SELECT a_menu.id
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."'
    	"; 
    $mt = microtime(true);
    $res =  mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0];
    
    
    
    //*****----------------------------------------   
    //ЗАПИСЬ ПРОЕКТА В БАЗУ
    $tipp='Тест';if ($parsing__work_=='in_db'){$tipp='Добавление в базу';}
    $name_=_GP('_parsing__name'); 
    $sql = "SELECT IF(COUNT(*)>0,a_parsing.id,0)
    				FROM a_parsing 
    					WHERE a_parsing.name='"._DB($name_)."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $project_id=$myrow[0];
    if ($project_id==0){//создаем новый проект
        
        $parsing__update_txt='Не обновлять';if ($parsing__update_=='1'){}$parsing__update_txt='Обновлять';
        $sql = "INSERT into a_parsing (
        				a_menu_id,
                        name,
        				url,
                        selector_page,
                        selector_block,
                        selector_card,
                        main_if,
                        tip,
                        main_col,
                        tip_update,
                        pop,
                        sleep_
        			) VALUES (
        				'"._DB($inc_id)."',
        				'"._DB($name_)."',
        				'"._DB($link_)."',
        				'"._DB($page_)."',
        				'"._DB($block_)."',
        				'"._DB($link_item_)."',
        				'"._DB($parsing__if_)."',
        				'"._DB($tipp)."',
        				'"._DB($parsing__main_)."',
        				'"._DB($parsing__update_)."',
        				'1',
                        '"._DB($parsing__sleep_)."'
                        
                        
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        	else{$project_id = mysql_insert_id();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    }else{
        $sql = "
        		UPDATE a_parsing 
        			SET  
        				selector_page='"._DB($page_)."',
        				selector_block='"._DB($block_)."',
        				selector_card='"._DB($link_item_)."',
        				main_if='"._DB($parsing__if_)."',
        				tip='"._DB($tipp)."',
        				main_col='"._DB($parsing__main_)."',
        				tip_update='"._DB($parsing__update_)."',
        				pop=pop+1,
        				sleep_='"._DB($parsing__sleep_)."'
                        
        		
        		WHERE id='"._DB($project_id)."'
        ";
        
        //url='"._DB($link_)."',
        
        $mt = microtime(true);
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        //очистка столбцов          
        $sql = "DELETE 
        			FROM a_parsing_col 
        				WHERE a_parsing_id='"._DB($project_id)."'
        ";
        //echo $sql. '-'.$project_id;exit();
        $mt = microtime(true);
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                
    }
    
    foreach($_REQUEST as $key => $val){
        
        if (mb_substr($key, 0, 14, 'utf-8')=='parsing__code_'){
            
            $arr=array();
            if(isset($val) and is_array($val)){
                foreach($val as $key2 => $val2){
                    $val_k=str_replace('parsing__code_','',$key);
                    $chk_='';if (isset($_REQUEST['parsing_chk_'.$val_k][$key2])){$chk_=$_REQUEST['parsing_chk_'.$val_k][$key2];}
                    $selector_='';if (isset($_REQUEST['parsing_'.$val_k][$key2])){$selector_=$_REQUEST['parsing_'.$val_k][$key2];}//селектор
                    
                    
                    
                    $code_=$val2;//код
                    //*****----------------------------------------   
                    //ЗАПИСЬ ПРОЕКТА В БАЗУ
            
                    if ($project_id>0){
                        $sql_ins = "INSERT into a_parsing_col (
                        				a_parsing_id,
                                        col,
                                        chk_active,
                                        selector,
                                        code
                                        
                        			) VALUES (
                        				'"._DB($project_id)."',
                        				'"._DB($val_k)."["._DB($key2)."]',
                        				'"._DB($chk_)."',
                        				'"._DB($selector_)."',
                        				'"._DB($code_)."'
                        )";
                       // echo $sql_ins.'<br />';
                        $mt = microtime(true);
                        $res_ins = mysql_query($sql_ins);
                        	if (!$res_ins){echo $sql_ins;exit();}
                        	else{$new_id = mysql_insert_id();}
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
                        
                    }
                }
            }else{//не массив
            
        
                $val_k=str_replace('parsing__code_','',$key);
                $chk_=_GP('parsing_chk_'.$val_k);
                $selector_=_GP('parsing_'.$val_k);//селектор
                $code_=_GP('parsing__code_'.$val_k);//код
                //*****----------------------------------------   
                //ЗАПИСЬ ПРОЕКТА В БАЗУ
        
                if ($project_id>0){
                    $sql_ins = "INSERT into a_parsing_col (
                    				a_parsing_id,
                                    col,
                                    chk_active,
                                    selector,
                                    code
                                    
                    			) VALUES (
                    				'"._DB($project_id)."',
                    				'"._DB($val_k)."',
                    				'"._DB($chk_)."',
                    				'"._DB($selector_)."',
                    				'"._DB($code_)."'
                    )";
                   // echo $sql_ins.'<br />';
                    $mt = microtime(true);
                    $res_ins = mysql_query($sql_ins);
                    	if (!$res_ins){echo $sql_ins;exit();}
                    	else{$new_id = mysql_insert_id();}
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
                    
                }
            }
        }
    }
    
    //end ЗАПИСЬ ПРОЕКТА В БАЗУ
    //*****---------------------------------------- 
    
        //получаем набор столбцов
    $sql = "SELECT  a_col.`id`,
                    a_col.`col`,
                    a_col.`col_ru`,
                    a_col.`tip`
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_change='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
                        AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
    
                    ORDER BY a_col.sid
                    
    "; 
    $mt = microtime(true);
    $res =  mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $data_['col']=array();
    $data_['col_ru']=array();
    $data_['tip']=array();
    $i=0;//счетчик столбцов - col
    while ($myrow = mysql_fetch_array($res)) 
    {
        //Получаем данные столбцов
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
        $data_['col_id'][$i]=$myrow[0];
        
        $i++;
    }
    
    //echo '3<br />';
    eval('$items_arr='.$block_.';');
    //echo '4<br />';
    $data_['_d']=array();
    
    
    $j=0;//счетчик записей
    
    foreach($items_arr as $article) {// ПЕРЕБОР ПО БЛОКУ ТОВАРОВ

        $new_=0;//маркер добавления
        $cur_id=0;
        
        //получаем главную запись
        $chk_=_GP('parsing_chk_'.$parsing__main_);
        $selector_=_GP('parsing_'.$parsing__main_);//селектор
        $code_=_GP('parsing__code_'.$parsing__main_);//код
        unset($val);
        //echo '5<br />';
        eval('$val='.$selector_.';'.$code_);
        //echo '6<br />'; 
        if (!isset($val)){$val="";}
        $main_val=$val;
            
        //СОЗДАЕМ ЗАПИСЬ В БАЗЕ - главную запись
        
        if ($parsing__work_=='in_db'){//основной столбец для сравнения
        
            if (isset($main_val) and !is_array($main_val) and $main_val!=''){
                $sql = "SELECT IF (COUNT(*)>0,`"._DB($inc)."`.`id`,'')
                				FROM `"._DB($inc)."` 
                					WHERE `"._DB($inc)."`.`"._DB($parsing__main_)."`='"._DB($main_val)."' 
                				
                	"; 
                $mt = microtime(true);
                $res =  mysql_query($sql) or die(mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
                $myrow = mysql_fetch_array($res);
                $cur_id=$myrow[0];
                //echo $cur_id.'<br />';
                if ($cur_id==''){
                    //добавляем
                    $sql = "INSERT into `"._DB($inc)."` (
                    				"._DB($parsing__main_)."
                    			) VALUES (
                    				'"._DB($main_val)."'
                    )";
                    $mt = microtime(true);
                    $res =  mysql_query($sql) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
                    if (!$res){echo $sql;exit();}
                    
                    else{$cur_id = mysql_insert_id(); }
                    $new_=1;
                    $data_['add']++;
                }else{
                    //обновляем
                    if ($parsing__update_==0){ 
                        continue; // переходим на следующую запись в цикле
                    }
                    $data_['upp']++;
                }
                //обновляем
            }else{
                echo 'Значение основного столбца ('.$parsing__main_.') равно пустоте или массиву $main_val';
                exit();
            }
        }

        //echo $main_val;exit();
        
        // end получаем главную запись
        $html_in = new simple_html_dom();
        $err=0;
        if ($parsing__if_!=''){
            //echo '7<br />';
            $err=1;eval('if('.$parsing__if_.'){$err=0;}');
            //echo '8<br />';
        }
        if ($err==0){//проверка на соответствие
          //memoryUsage(memory_get_usage(), $base_memory_usage);exit;
            //получаем ссылку для карточки товара
            $link_item_url='';
            if ($link_item_!=''){
                //echo '9<br />';
                eval($link_item_);
                //echo '10<br />';
                 //ЗАХОДИМ ВО ВНУТЬ СТРАНИЦЫ
                if($link_item_url!=''){
                    if ($parsing__sleep_>0){sleep(($parsing__sleep_)-0);}
                    
                     /*
                     $html_in = file_get_html($link_item_url);
                     */
                   
                    $html_txt=readPage($link_item_url);
                    if ($html_txt!=''){
                        $html_in=str_get_html($html_txt);
                    }
                   
                }
                else{
                    echo '$link_item_url=""';exit();
                }
                
                if (!$html_in){
                    if (!$html_in){echo 'Не удалось получить содержимое страницы $html_in=file_get_html("'.$link_item_url.'")!';exit();}
                }
            }
            
            $data_['_d'][$j]=array();
            
            unset($in_page_arr);
            $in_page_arr=array();
            $SQL_UPP='';
            
            $name_='';
            //перебор по остальным столбцам
            foreach($data_['col'] as $i => $col_) 
            {
                
                //получаем переменные передаваемые с парсинга
                $chk_=_GP('parsing_chk_'.$data_['col'][$i]);
                $selector_=_GP('parsing_'.$data_['col'][$i]);//селектор
                $code_=_GP('parsing__code_'.$data_['col'][$i]);//код
                
                //*****----------------------------------------   
                
                if ($chk_=='1' or is_array($chk_)){ //включен парсинг по данному столбцу
                        
                    if (!is_array($chk_)){
                        
                        //echo '$val='.$selector_.';'.$code_;
                        //echo '11<br />';
                        eval('$val='.$selector_.';'.$code_);
                        //echo '12<br />';
                        if (isset($val)){
                            $data_['_d'][$j][$data_['col'][$i]]=$val;
                        }
                    
                        
                    }
                    if($col_=='name'){$name_=$val;}
                    
                    switch ($data_['tip'][$i]) { //ТИП
                        case "Текст":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Длинный текст":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "HTML-код":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Целое число":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Дробное число":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Стоимость":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Дата":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Дата-время":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Телефон":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Email":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Связанная таблица 1-max":
                            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                                    a_connect.usl,
                                                    a_connect.chk
                            				FROM a_connect 
                            					WHERE a_connect.a_col_id1='"._DB($data_['col_id'][$i])."'
                            					
                            	";
                            $mt = microtime(true);
                            $res_connect =  mysql_query($sql_connect) or die(mysql_error());
                            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    
                           
                            $myrow_connect = mysql_fetch_array($res_connect);
                            $connect_arr['inc'][$i]=$myrow_connect[0];
                            $connect_arr['col'][$i]=$myrow_connect[1];
                            $connect_arr['usl'][$i]=$myrow_connect[2];
                            $connect_arr['chk'][$i]=$myrow_connect[3];
                            
                            $sql = "SELECT IF(COUNT(*)>0,`"._DB($connect_arr['inc'][$i])."`.`id`,'')
                            				FROM `"._DB($connect_arr['inc'][$i])."` 
                            					WHERE `"._DB($connect_arr['inc'][$i])."`.`"._DB($connect_arr['col'][$i])."`='"._DB($val)."' 
                            					
                            	"; 
                            
                            $mt = microtime(true);
                            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                            $myrow = mysql_fetch_array($res);
                            $val_id=$myrow[0];
                            if ($val_id==''){
                                $sql = "INSERT into `"._DB($connect_arr['inc'][$i])."` (
                                				`"._DB($connect_arr['col'][$i])."`
                                			) VALUES (
                                				'"._DB($val)."' 
                                )";
                                
                                $mt = microtime(true);
                                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                $val_id = mysql_insert_id();
                                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                
                            }
                            
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val_id)."'";
                            
                        break;
                        //***********************************************************************************
                        case "Связанная таблица max-max":
                            
                            $sql_connect = "SELECT  (SELECT a_menu.inc FROM a_menu,a_col WHERE a_col.id=a_connect.a_col_id2 AND a_menu.id=a_col.a_menu_id), 
                                                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id2),
                                                    a_connect.usl,
                                                    a_connect.chk,
                                                    a_connect.tbl
                            				FROM a_connect 
                            					WHERE a_connect.a_col_id1='"._DB($data_['col_id'][$i])."'
                            					
                            	";
                            $mt = microtime(true);
                            $res_connect =  mysql_query($sql_connect) or die(mysql_error());
                            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    
                           
                            $myrow_connect = mysql_fetch_array($res_connect);
                            $connect_arr['inc'][$i]=$myrow_connect[0];
                            $connect_arr['col'][$i]=$myrow_connect[1];
                            $connect_arr['usl'][$i]=$myrow_connect[2];
                            $connect_arr['chk'][$i]=$myrow_connect[3];
                            $connect_arr['tbl'][$i]=$myrow_connect[4];
                            
                            $tbl_connect_=$inc.'_'.$connect_arr['inc'][$i];
                            if ($connect_arr['tbl'][$i]!=''){
                               $tbl_connect_=$connect_arr['tbl'][$i]; 
                            }
                            
                            $sql_connect = "DELETE
                            				FROM `".$tbl_connect_."`
                            					WHERE `".$tbl_connect_."`.`id1`='"._DB($cur_id)."'
                            						
                            "; 
                            
                            $mt = microtime(true);
                            if (!mysql_query($sql_connect)){$_SESSION['error']['other_ajax__parsing_'.$inc.'_'.date('Y-m-d H:i:s')]=$sql_connect;echo $sql_connect;exit();}
                            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
    
                            
                            //echo 'parsing_'.$data_['col'][$i].'_sel';
                            $val_arr=_GP('parsing_'.$data_['col'][$i].'_sel');//селектор
                            if (!is_array($val_arr)){$n=$val_arr;unset($val_arr);$val_arr[0]=$n;}
                            
                            $txt_max='';
                            foreach($val_arr as $kk => $max_id){
                                if ($max_id!='' and $cur_id!=''){
                                    if ($txt_max!=''){$txt_max.=', ';}
                                    $txt_max.="('"._DB($cur_id)."','"._DB($max_id)."')";
                                }
                            }
                            
                            //добавляем связи
                            if ($txt_max!=''){
                                $sql_max = "INSERT into `".$tbl_connect_."` (
                                				id1,
                                				id2
                                			) VALUES 
                                            $txt_max
                                            ";
                                           
                             $mt = microtime(true);
                             if (!mysql_query($sql_max)){echo $sql_max;$_SESSION['error']['other_ajax__parsing_'.$inc.'_'.date('Y-m-d H:i:s')]=$sql_max;}
                             $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_max;$data_['_sql']['time'][]=$mt;
    
                                  
                            }
                            
                            
                        break;
                        //***********************************************************************************
                        case "Функция":
                        
                            
                            $file_function='__function_'.$inc.'_'.$data_['col'][$i].'.php';
                            if (file_exists($file_function)){
                                    include $file_function;
                            }else{
                                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php<br />';
                            }
                        break;
                        //***********************************************************************************
                        case "chk":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "enum":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Цвет":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                        case "Фото":
                            if ($new_==1){//новая запись
                            
                                
                                //проверяем директории на существование
                                if (!file_exists('../../i/')){mkdir('../../i/',0777);}
                                if (!file_exists('../../i/'.$inc.'/')){mkdir('../../i/'.$inc.'/',0777);}
                                if (!file_exists('../../i/'.$inc.'/original/')){mkdir('../../i/'.$inc.'/original/',0777);}
                                if (!file_exists('../../i/'.$inc.'/small/')){mkdir('../../i/'.$inc.'/small/',0777);}
                                
                                $img_i=0;
                                
                                if (!is_array($val) and $val!=''){
                                    $val2=$val;
                                    unset($val);
                                    $val[0]=$val2;
                                }
                                
                                foreach($val as $key => $img_){
                                 //echo '+'.$img_.'<br />';
                                 if (mb_substr($img_,0,2,'UTF-8')=='//'){$img_=str_replace('//','http://',$img_);}
                                  //echo '*'.$img_.'<br /><br />';
                                    ///парсим фото 
                                    if ($img_!=''){
                                        
                                        $ext=preg_replace("/.*?\./", '', $img_);
                                        $new_img_name='img_parsing_'.rand(100,999).'_'.date('YmdHis').'.'.$ext;
                                        
                                        $new_img='../../i/'.$inc.'/original/'.$new_img_name;
                                        file_put_contents($new_img,file_get_contents($img_));
                                        
                                        //создаем миниатюру
                                        $size_arr= getimagesize($img_);
                                        $w_orig=$size_arr[0];
                                        $h_orig=$size_arr[1];
                                        
                                        smart_resize_image( $img_, '../../i/'.$inc.'/small/'.$new_img_name, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                          
                                    
                                    
                                        $tip_img='Галерея';if ($img_i==0){$tip_img='Основное';}
                                        
                                        $sql_img = "INSERT into a_photo (
                                        				a_menu_id,
                                        				row_id,
                                                        sid,
                                                        tip,
                                                        img
                                        			) VALUES (
                                        				'"._DB($inc_id)."',
                                        				'"._DB($cur_id)."',
                                                        '"._DB($img_i)."',
                                                        '"._DB($tip_img)."',
                                                        '"._DB($new_img_name)."'
                                        )";
                                        
                                        $mt = microtime(true);
                                        mysql_query($sql_img) or die(mysql_error());
                                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_img;$data_['_sql']['time'][]=$mt;
        
                                        $img_i++;
                                    }
                                }
                                
                            }
                        break;
                        case "Ссылка":
                            if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                            $SQL_UPP.="`"._DB($col_)."`='"._DB($val)."'";
                        break;
                        //***********************************************************************************
                    }
                    
                }//включен парсинг
                
            }//end перебор по столбцам
            
            
            if (in_array('url',$names) and $name_!=''){
                if ($SQL_UPP!=''){$SQL_UPP.=", ";}
                $SQL_UPP.="`url`='"._DB(make_url($name_))."'";
               
            }
            
            
            //ОБНОВЛЯЕМ БАЗУ ДАННЫХ
            if ($parsing__work_=='in_db'){
                if ($SQL_UPP==''){echo '$SQL_UPP="'.$SQL_UPP.'"';exit();}
                if ($cur_id==0){echo '$cur_id=0';exit();}
                    $SQL_UPP = "
                    		UPDATE `"._DB($inc)."` 
                    			SET  
                    				$SQL_UPP
                    		
                    		WHERE `"._DB($inc)."`.`id`='"._DB($cur_id)."'
                    ";
                    
                    $mt = microtime(true);
                    mysql_query($SQL_UPP) or die(mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$SQL_UPP;$data_['_sql']['time'][]=$mt;

                    
                
            }
             
           $j++;
        }//не выполняется проверкка на соответствие
        
        //echo '<br />+++++'.$SQL_UPP;
        $html_in->clear();
        $article->clear();
        unset($html_in,$article);
    }
    unset($items_arr);
    
    
    echo json_encode($data_);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//УДАЛИТЬ БЫСТРУЮ ССЫЛКУ
elseif ($_t=='del_hash'){
    
    $nomer=_GP('nomer');
    $sql = "DELETE 
    			FROM a_parsing_col 
    				WHERE a_parsing_col.a_parsing_id='"._DB($nomer)."'
    ";
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
        
    $sql = "DELETE 
    			FROM a_parsing
    				WHERE a_parsing.id='"._DB($nomer)."'
    ";
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
    
    echo 'ok';
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//УДАЛИТЬ БЫСТРУЮ ССЫЛКУ
elseif ($_t=='del_hash_export'){
    
    $nomer=_GP('nomer');
    $sql = "DELETE 
    			FROM a_export_csv_col 
    				WHERE a_export_csv_col.a_export_csv_id='"._DB($nomer)."'
    ";
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
        
    $sql = "DELETE 
    			FROM a_export_csv
    				WHERE a_export_csv.id='"._DB($nomer)."'
    ";
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
    
    echo 'ok';
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Сессии открытия дерева
elseif ($_t=='open_tree'){
    
    $inc=_GP('inc');
    $_SESSION['tree_view'][$inc]='open';
    echo 'OK';
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Сессии открытия дерева
elseif ($_t=='close_tree'){
    
    $inc=_GP('inc');
    $_SESSION['tree_view'][$inc]='close';
    echo 'OK';
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Автозаполнение формы путем копирования
elseif ($_t=='__other__add_copy_from'){
    $data_=array();
    $inc=_GP('inc');
    $term=_GP('term');
    
    //Определяем menu_id
    $sql = "SELECT IF(COUNT(*)>0,a_menu.id,'')
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."' 
    					LIMIT 1
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die($sql . '<br />'. mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0]; if ($inc_id==''){echo 'Не определен пункт меню $inc_id=""!<br />';print_r($myrow).'<br />'.$sql;exit();}
    
    
    //получаем набор столбцов
    $sql = "SELECT  a_col.`id`,
                    a_col.`col`,
                    a_col.`col_ru`,
                    a_col.`tip`,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
                        AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
                        AND a_col.`col`='name'
                    ORDER BY a_col.sid
                    
    "; //$col_where
    $mt = microtime(true);
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
       
    $data_['col']=array();
    $data_['col_ru']=array();
    $data_['tip']=array();
    $data_['chk_change']=array();
    
    $i=0;$col_m=array();
    $SQL_COL='';
    $WHERE_COL='';
    while ($myrow = mysql_fetch_array($res)) 
    {
        //print_r($myrow);
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
        $data_['chk_change'][$i]=$myrow[4];
        
        
        if ($data_['chk_change'][$i]=='1'){
            $col_m[$i]=$data_['col'][$i];
            if ($SQL_COL!=''){$SQL_COL.=', ';}
            $SQL_COL.="`"._DB($inc)."`.`".$col_m[$i]."`";
            
            if ($WHERE_COL!=''){$WHERE_COL.=' AND ';}
            $WHERE_COL.="`"._DB($inc)."`.`".$col_m[$i]."` LIKE '%".$term."%'";
        }
        
        $i++;
    }
    
    if ($WHERE_COL!=''){$WHERE_COL=' WHERE '.$WHERE_COL;} 
    
    //массив столбцов
    //echo '['.$nomer_where.']';exit();
    if ($SQL_COL!=''){
        $data_=array();
        $sql_main = "SELECT `"._DB($inc)."`.id, "._DB($SQL_COL)." 
                            
        				FROM `"._DB($inc)."`
                        
                        $WHERE_COL
                        
        ";
        
        //WHERE `"._DB($inc)."`.`id` $nomer_where
       
        $res_main = mysql_query($sql_main) or die($sql_main.'<br />'.mysql_error());
   
        for ($myrow_main = mysql_fetch_array($res_main),$i=0; $myrow_main==true; $myrow_main = mysql_fetch_array($res_main),$i++)
        {
            $data_[$i]['label']=$myrow_main[1];
            $data_[$i]['value']=$myrow_main[1];
            $data_[$i]['text']=$myrow_main[1];
            $data_[$i]['id']=$myrow_main[0];
        }
    }
    
    echo json_encode($data_);
}

//////////////////////////////////////////////////////////////////////////////////
//Добавление / удаление отображения столбцов для пользователя
elseif ($_t=='a_col_change'){
    $data_=array();
    $inc=_GP('inc');
    $col=_GP('col');
    $tip=_GP('tip');
    
    $_SESSION['a_col_view'][$inc][$col]=$tip;
    
    echo json_encode($data_);
}

//////////////////////////////////////////////////////////////////////////////////
//Вывод столбцов для пользователя
elseif ($_t=='a_col_change_all'){
    $data_=array();
    $inc=_GP('inc');
    
    $sql = "SELECT a_menu.id
    				FROM a_menu 
    					WHERE a_menu.inc='"._DB($inc)."'
    	"; 
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error());
    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $myrow = mysql_fetch_array($res);
    $inc_id=$myrow[0];
    
    // получаем массив всех отображаемых столбцов
    $sql = "SELECT  a_col.id,
                    a_col.col,
                    a_col.col_ru,
                    a_col.tip
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_view='1'
                        AND a_col.a_menu_id='"._DB($inc_id)."'
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
    
    $data_['col']=array();$data_['col_ru']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['view'][$i]='1';
        if (isset($_SESSION['a_col_view']) and isset($_SESSION['a_col_view'][$inc]) and isset($_SESSION['a_col_view'][$inc][$myrow[1]]) and $_SESSION['a_col_view'][$inc][$myrow[1]]=='-1'){
            $data_['view'][$i]='-1';
        }
    }
    
    echo json_encode($data_);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Добавление товара по загрузке фотографий
elseif ($_t=='upload_items'){
    $data_=array();
    $inc=_GP('inc');
    
    $targetDir = '../../i/'.$inc.'/original';
    $fileName='';
    
    
    
    // проверяем на пустоту
    if (!isset($_SESSION['a_admin'][$inc]['photo_temp']) or $_SESSION['a_admin'][$inc]['photo_temp']==''){
            
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
        
        if (!empty($_FILES)) {$_SESSION['a_admin'][$inc]['photo_temp_name'] = str_replace('.'.$ext,'', $_FILES["file"]["name"]);}
        
        $_SESSION['a_admin'][$inc]['photo_temp']=$fileName;
    }else{
        $fileName=$_SESSION['a_admin'][$inc]['photo_temp'];
    }
    
    @set_time_limit(5 * 60);
    if (!file_exists('../../i')) {@mkdir('../../i',0777);}
    if (!file_exists('../../i/'.$inc)) {@mkdir('../../i/'.$inc,0777);}
    
    
    if (!file_exists($targetDir)) {@mkdir($targetDir,0777);}
    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 5 * 3600; // Temp file age in seconds
    $filePath = $targetDir . '/' . $fileName;
    

    
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    
    if ($cleanupTargetDir) { // Удаление старых файлов
    	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {}
    	while (($file = readdir($dir)) !== false) {
    		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
    		if ($tmpfilePath == "{$filePath}.part") {
    			continue;
    		}
    		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
    			@unlink($tmpfilePath);
    		}
    	}
    	closedir($dir);
    }
    if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {}
    
    if (!empty($_FILES)) {
    	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {}
    	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {}
    } else {if (!$in = @fopen("php://input", "rb")) {}}
    
    while ($buff = fread($in, 4096)) {fwrite($out, $buff);}
    
    @fclose($out);
    @fclose($in);
    
    if (!$chunks || $chunk == $chunks - 1) {
        rename("{$filePath}.part", $filePath);
        $names=get_column_names_with_show($inc);
       
        
        
        if (in_array('name',$names)){
            
            $sql_1="";
            $sql_2="";
            
            if (in_array('data_change',$names)){
                $sql_1.=",data_change";
                $sql_2.=",'".date('Y-m-d H:i:s')."'";
            }
            if (in_array('chk_active',$names)){
                $sql_1.=",chk_active";
                $sql_2.=",'0'";
            }
            if (in_array('a_admin_id_create',$names)){
                $sql_1.=",a_admin_id_create";
                $sql_2.=",'"._DB($a_admin_id_cur)."'";
            }
            if (in_array('a_admin_id_change',$names)){
                $sql_1.=",a_admin_id_change";
                $sql_2.=",'"._DB($a_admin_id_cur)."'";
            }
            if (in_array('url',$names)){
                $url_=ru_us(@$_SESSION['a_admin'][$inc]['photo_temp_name']);
                $sql="SELECT 
                            COUNT(*) 
                                FROM `$inc`
                                WHERE `$inc`.url='"._DB($url_)."'
                                ";
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $myrow = mysql_fetch_array($res);
                while($myrow[0]>0){
                    $url_=ru_us(@$_SESSION['a_admin'][$inc]['photo_temp_name']).'_'.date('s').rand(100,999);
                    $sql="SELECT 
                                COUNT(*) 
                                    FROM `$inc`
                                    WHERE `$inc`.url='"._DB($url_)."'
                                    ";
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                }
                
                $sql_1.=",url";
                $sql_2.=",'"._DB($url_)."'";
            }
            
            $sql_ins = "INSERT into `$inc` (
            				name
                            $sql_1
            			) VALUES (
            				'"._DB(@$_SESSION['a_admin'][$inc]['photo_temp_name'])."'
                            $sql_2
            )";
            
            $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
            $id = mysql_insert_id();
            
            
            
            
            $size_arr= getimagesize($filePath);
            $w_orig=$size_arr[0];
            $h_orig=$size_arr[1];
            
            smart_resize_image( $filePath, '../../i/'.$inc.'/small/'.$fileName, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                              
            $sql = "SELECT a_menu.id
            				FROM a_menu 
            					WHERE a_menu.inc='"._DB($inc)."' 
            				
            	"; 
            
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $myrow = mysql_fetch_array($res);
            $a_menu_id=$myrow[0];
            
            $sql = "INSERT into a_photo (
            				sid,
            				img,
                            a_menu_id,
                            tip,
                            row_id
            			) VALUES (
            				'0',
            				'"._DB($fileName)."',
                            '"._DB($a_menu_id)."',
                            'Основное',
                            '"._DB($id)."'
            )";
            
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $new_id = mysql_insert_id();
            
        }
        unset($_SESSION['a_admin'][$inc]['photo_temp'],$_SESSION['a_admin'][$inc]['photo_temp_name'],$_SESSION['a_admin'][$inc]);
      
    }
    echo $fileName;
}

//*********************************************************************************************************
// ЗАГРУЗКА ФАЙЛА CSV
if ($_t=='__other__paste'){
    $inc=_GP('_inc');
    $id=_GP('id');

    $targetDir = '../../upload/copy/temp';
        if (!file_exists('../../upload')){mkdir('../../upload',0777);}
        if (!file_exists('../../upload/copy')){mkdir('../../upload/copy',0777);}
        if (!file_exists($targetDir)){mkdir($targetDir,0777);}
    $fileName='';


    
    // проверяем на пустоту
    if (!isset($_SESSION['a_admin'][$inc]['copy_temp']) or $_SESSION['a_admin'][$inc]['copy_temp']==''){
            
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
                
        $_SESSION['a_admin'][$inc]['copy_temp']=$fileName;
    }else{
        $fileName=$_SESSION['a_admin'][$inc]['copy_temp'];
    }
    
    
        
    @set_time_limit(5 * 60);

    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 5 * 3600; // Temp file age in seconds
    $filePath = $targetDir . '/' . $fileName;
    

    
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    
    if ($cleanupTargetDir) { // Удаление старых файлов
    	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {}
    	while (($file = readdir($dir)) !== false) {
    		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
    		if ($tmpfilePath == "{$filePath}.part") {
    			continue;
    		}
    		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
    			@unlink($tmpfilePath);
    		}
    	}
    	closedir($dir);
    }
    if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {}
    
    if (!empty($_FILES)) {
    	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {}
    	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {}
    } else {if (!$in = @fopen("php://input", "rb")) {}}
    
    while ($buff = fread($in, 4096)) {fwrite($out, $buff);}
    
    @fclose($out);
    @fclose($in);
    
    if (!$chunks || $chunk == $chunks - 1) {
        rename("{$filePath}.part", $filePath);
        unset($_SESSION['a_admin'][$inc]['copy_temp']);
        $data_=array();
        $data_['f']=$fileName;
        
        //////////************************************************************************************
        //Добавление или обноление значений *********************************************************
        //*******************************************************************************************
        function other_paste_new_val($col_paste,$inc,$col_cur,$col_val_arr,$a_menu_id,$return_sql){
            global $base_name;

            //print_rf($col_val_arr);exit;
            $id=$col_val_arr[0];//импортируемый номер
            $key_name=-1; if (in_array('name',$col_paste)){$key_name=array_search('name',$col_paste);}
        
            $SQL_UPP="";
            foreach($col_paste as $key_col => $col_){
                    
                //если есть доступ для изменения
                if (in_array($col_,$col_cur['col'])){
                    $key_col_cur=array_search($col_,$col_cur['col']);
                    if ($col_cur['chk_change'][$key_col_cur]=='1'){
                        //Добавляем или обновляем в базе
                        
                        //Проверяем тип данных
                        if ($col_cur['tip'][$key_col_cur]=='Связанная таблица 1-max'){
                            $return_sql['SQL_UPP'][$col_].=" WHEN id = "._DB($id)." THEN '"._DB($col_val_arr[$key_col])."'"."\n";
                        }
                        elseif ($col_cur['tip'][$key_col_cur]=='Связанная таблица max-max'){
                            
                            // 1. Удаляем старые записи из связанной таблице по данному id
                            // 2. Разбираем json объект
                            // 3. Проверяем тип импортируемых данных - автодобавление (если нет данного значения - добавляем) в связанную таблицу или нет
                            // 4. Добавляем в таблицу связи 
                            
                            $tbl_connect_=$col_cur['inc'][$key_col_cur]."_".$col_cur['connect_inc'][$key_col_cur];
                            if ($col_cur['connect_tbl'][$key_col_cur]!=''){
                               $tbl_connect_=$col_cur['connect_tbl'][$key_col_cur]; 
                            }
                            
                             //удаляем старые записи 
                            $sql_connect = "DELETE 
                            			FROM `".$tbl_connect_."` 
                            				WHERE `".$tbl_connect_."`.`id1` IN ('"._DB($id)."')
                            ";
                            
                            $mt = microtime(true);
                            $res_connect = mysql_query($sql_connect) or die(mysql_error());
                            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
                            if (!$res_connect){echo $sql_connect;exit();}
                            
                            $val_arr=array();
                            if (isJSON($col_val_arr[$key_col])==true){
                                $val_arr=json_decode($col_val_arr[$key_col]);
                            }
                          
                            $tbl_connect_=$inc."_".$col_cur['connect_inc'][$key_col_cur];
                            if ($col_cur['connect_tbl'][$key_col_cur]!=''){
                               $tbl_connect_=$col_cur['connect_tbl'][$key_col_cur]; 
                            }
                            
                            //Получаем тип
                            if (gettype($val_arr)=='array'){// не добавлять значения в связанную таблицу
                                
                                //Добавляем связи со связанной таблицей
                                $SQL_CONNECT="";
                                foreach($val_arr as $key_connect => $id_connect){
                                   
                                    if ($SQL_CONNECT!=""){$SQL_CONNECT.=", ";}
                                    $SQL_CONNECT.="('"._DB($id)."','"._DB($id_connect)."')";
                                
                                } 
                                if ($SQL_CONNECT!=''){
                                    $sql_ins_connect = "INSERT into `".$tbl_connect_."` (
                                    				id1,
                                    				id2
                                    			) VALUES $SQL_CONNECT";
                                    $res_ins_connect = mysql_query($sql_ins_connect) or die(mysql_error().'<br>'.$sql_ins_connect);
                                    //echo $sql_ins_connect;
                                }
                            }
                            elseif (gettype($val_arr)=='object'){// добавлять значения в связанную таблицу
                                //Добавляем значения в таблицу связи
                                foreach($val_arr as $id_connect => $name_connect){
                                    $sql_conn="SELECT 
                                                COUNT(*) 
                                                    FROM `"._DB($col_cur['connect_inc'][$key_col_cur])."`
                                                    WHERE `"._DB($col_cur['connect_inc'][$key_col_cur])."`.`id`='"._DB($id_connect)."'
                                                   
                                                    ";
                                    $res_conn = mysql_query($sql_conn) or die(mysql_error().'<br>'.$sql);
                                    $myrow_conn = mysql_fetch_array($res_conn);
                                    if ($myrow_conn[0]==0){
                                        $sql_ins = "INSERT into `"._DB($col_cur['connect_inc'][$key_col_cur])."` (
                                				        id,
                                                        ".$col_cur['connect_col'][$key_col_cur]."
                                        			) VALUES (
                                                        '"._DB($id_connect)."',
                                        				'"._DB($name_connect)."'
                                        )";
                                        
                                        $res_ins_connect = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                                        
                                    }
                                    $sql_ins_connect = "INSERT into `".$tbl_connect_."` (
                                    				id1,
                                    				id2
                                    			) VALUES ('"._DB($id)."','"._DB($id_connect)."')";
                                    $res_ins_connect = mysql_query($sql_ins_connect) or die(mysql_error().'<br>'.$sql_ins_connect);
                                    
                                }
                            }
                           
                            
                            //echo '<br />****'.gettype($val_arr).' - '.isJSON($col_val_arr[$key_col]).'->>>>>>>>'.$col_cur['col'][$key_col_cur].' - '.$col_cur['connect_inc'][$key_col_cur].'=='.$col_val_arr[$key_col].'+++<br />';
                           // echo '<br />+++'.$col_cur['connect_col'][$key_col_cur].'+++<br />';
                                
                            
                        }
                        elseif ($col_cur['tip'][$key_col_cur]=='Функция'){
                           
                            $_t='paste';
                          
                            $file_function='__function_'.$col_cur['inc'][$key_col_cur].'_'.$col_cur['col'][$key_col_cur].'.php';
                            //echo $file_function;
                            if (file_exists($file_function)){
                             
                                    include $file_function;
                                    
                            }else{
                                echo 'Нет файла функции: __function_'.$col_cur['inc'][$key_col_cur].'_'.$col_cur['col'][$key_col_cur].'.php<br />';
                            }
                           
                        }
                        elseif ($col_cur['tip'][$key_col_cur]=='Фото'){
                            
                            //1. Разбираем json объект
                            //2. Копируем фотографию во временную папку
                            //3. Удаляем все фотографии у данного номера из папки и  базы данных
                            //4. Переносим фотографию в постоянную папку и создаем миниатюру
                            //5. Добавляем данные о новой фотографии в базу данных
                            $val_arr=array();
                            if (isJSON($col_val_arr[$key_col])==true){
                                $val_arr=json_decode($col_val_arr[$key_col]);
                            }
                             if (isset($col_val_arr) and isset($col_val_arr[$key_name])){
                                $img_name_start=str_replace(array('---','--'),'-',ru_us($col_val_arr[$key_name]));
                             }else{
                                $img_name_start=date('YmdHis').'_'.rand(1000,9999);
                             }
                             
                             //echo $img_name_start;exit;
                             //проверяем директории на существование
                                    if (!file_exists('../../i/')){mkdir('../../i/',0777);}
                                    if (!file_exists('../../i/'.$inc.'/')){mkdir('../../i/'.$inc.'/',0777);}
                                    if (!file_exists('../../i/'.$inc.'/original/')){mkdir('../../i/'.$inc.'/original/',0777);}
                                    if (!file_exists('../../i/'.$inc.'/small/')){mkdir('../../i/'.$inc.'/small/',0777);}
                                    if (!file_exists('../../i/'.$inc.'/temp/')){mkdir('../../i/'.$inc.'/temp/',0777);}
                                
                                $new_img_arr=array();
                                if (isset($val_arr) and isset($val_arr[0]) and (is_array($val_arr[0]) or is_object($val_arr[0]))){
                                    foreach($val_arr[0] as $key_ => $img_sourse){
                                        $ext=preg_replace("/.*?\./", '', $img_sourse);
                                        // echo $img_sourse.' -- '.$ext.'<br />';
                                        
                                        $new_img_arr[$key_]='../../i/'.$inc.'/temp/'.rand(0,99999).'_'.date('YmdHis').'.'.$ext;
                                        file_put_contents($new_img_arr[$key_],file_get_contents($img_sourse));
                                            
                                        
                                    }    
                                }
                                
                                //Получаем старые изображениия из бд
                                $sql_img="SELECT img
                                            FROM a_photo
                                                WHERE a_photo.a_menu_id='"._DB($a_menu_id)."'
                                                AND a_photo.row_id='"._DB($id)."'
                                        ";
                                $res_img = mysql_query($sql_img) or die(mysql_error().'<br>'.$sql_img);
                                while ($myrow_img = mysql_fetch_array($res_img)) {
                                    //echo 'unlink: '.'../../i/'.$inc.'/original/'.$myrow_img[0].'<br />';
                                    @unlink('../../i/'.$inc.'/original/'.$myrow_img[0]);
                                    @unlink('../../i/'.$inc.'/small/'.$myrow_img[0]);
                                }
                                
                                
                                //Удаляем старые изображениия
                                $sql_del="DELETE 
                                            FROM a_photo
                                                WHERE a_photo.a_menu_id ='"._DB($a_menu_id)."'
                                                AND a_photo.row_id='"._DB($id)."'
                                        ";
                                $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
                                //echo $sql_del.'<br />';
                                
                                
                               //КОПИРУЕМ НОВЫЕ КАРТИНКИ В ПОСТОЯННУЮ ДИРРЕКТОРИЮ
                               foreach($new_img_arr as $kkey => $img_temp){
                                    $ext=preg_replace("/.*?\./", '', $img_temp);
                                    $kkey_txt='';if ($kkey>0){$kkey_txt='_'.$kkey.'';}
                                    //создаем миниатюру
                                    $size_arr= getimagesize($img_temp);
                                    $w_orig=$size_arr[0];
                                    $h_orig=$size_arr[1];
                                    $new_img=$img_name_start.$kkey_txt.'.'.$ext;
                                    
                                    //echo 'copy:'.$img_temp,'../../i/'.$inc.'/original/'.$new_img.'<br />';
                                    copy($img_temp,'../../i/'.$inc.'/original/'.$new_img);
                                    smart_resize_image( $img_temp, '../../i/'.$inc.'/small/'.$new_img, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                                
                                    //ДОБАВЛЯЕМ В БАЗУ
                                    $sql_ins = "INSERT into a_photo (
                                                    sid,
                                    				row_id,
                                                    img,
                                    				a_menu_id,
                                                    tip,
                                                    comments
                                                    
                                    			) VALUES (
                                                    '"._DB($kkey)."',
                                    				'"._DB($id)."',
                                                    '"._DB($new_img)."',
                                    				'"._DB($a_menu_id)."',
                                                    '"._DB($val_arr[1][$kkey])."',
                                                    '"._DB($val_arr[2][$kkey])."'
                                    )";
                                    //echo $sql_ins.'<br />';
                                    $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                                    
                                }
                                        
                                        
                               
                            //print_rf($val_arr);
                            
                        }
                        else{///ДРУГОЙ ТИП
                           
                            $return_sql['SQL_UPP'][$col_].=" WHEN id = "._DB($id)." THEN '"._DB($col_val_arr[$key_col])."'";
                                
                        }
                        //print_rf($col_cur['col'][$key_col_cur]);
                       // print_rf($col_cur['tip'][$key_col_cur]);
                        //echo '<br />++++++++++++++++++++<br />';
                    }
                    
                }    
            }
            
        
            if (!isset($return_sql['SQL_UPP']['id']) or $return_sql['SQL_UPP']['id']==''){
                $return_sql['SQL_UPP']['id']=$id;
            }else{
                $return_sql['SQL_UPP']['id'].=', '.$id;
            }
            echo '<p style="color:#090;">Запись №'.$id.' успешно импортирована!</p>';
            return $return_sql;
        }///////////////////////////////////////////////////////////////////////////////////////////////////


        //Получаем текущие столбцы
        $sql = "SELECT      ac1.id
                            , ac1.col
                            , ac1.tip
                            , ac1.chk_change
                            , am1.id
                            , (SELECT IF(COUNT(a_connect.id)>0,am2.inc,'') FROM a_connect, a_col AS ac2, a_menu AS am2 WHERE a_connect.a_col_id1=ac1.id AND a_connect.a_col_id2=ac2.id AND ac2.a_menu_id=am2.id)
        				    , (SELECT IF(COUNT(a_connect.id)>0,ac3.col,'') FROM a_connect, a_col AS ac3 WHERE a_connect.a_col_id1=ac1.id AND a_connect.a_col_id2=ac3.id)
        				   , (SELECT IF(COUNT(a_connect.id)>0,a_connect.tbl,'') FROM a_connect WHERE a_connect.a_col_id1=ac1.id)
        				   
                            
                        FROM a_menu AS am1, a_col  AS ac1
        					WHERE ac1.a_menu_id=am1.id
                            AND  ac1.chk_change='1'
                            AND  am1.inc='"._DB($inc)."' 
                            AND ac1.id IN (
                                            SELECT a_admin_a_col.id2
                                                FROM a_admin_a_col, a_admin
                                                    WHERE a_admin_a_col.id1=a_admin.id
                                                        AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                                                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                        )
        	"; 
        $res = mysql_query($sql) or die(mysql_error());
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['_col']['id'][$i]=$myrow[0];
            $data_['_col']['col'][$i]=$myrow[1];
            $data_['_col']['tip'][$i]=$myrow[2];
            
            $data_['_col']['chk_change'][$i]=$myrow[3];
            $data_['_col']['a_menu_id'][$i]=$myrow[4];
            $data_['_col']['inc'][$i]=$inc;
            $data_['_col']['connect_inc'][$i]=$myrow[5];
            $data_['_col']['connect_col'][$i]=$myrow[6];
            $data_['_col']['connect_tbl'][$i]=$myrow[7];
            
        }

        //читаем файл
        $arr = kama_parse_csv_file($filePath);
        
        $return_sql['SQL_UPP']=array();
        $col_paste=array();
        ///Перебираем по столбцам
        $jj=0;
        foreach($arr[0] as $key => $col_){
            $col_paste[$jj]=$col_;
            
            $return_sql['SQL_UPP'][$col_]='';//sql для обновления
            
            $jj++;
        }
        unset($arr[0]);//удаляем столбцы
        
        //ПЕРЕБОР ПО СТРОКАМ
        foreach($arr as $row_num => $col_val_arr){
            
            //Проверяем наличие текущей записи, если нет - добавляем
            $sql="SELECT COUNT(*)
                        FROM `$inc`
                        WHERE `$inc`.`id`='"._DB($col_val_arr[0])."'
                    ";
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $myrow = mysql_fetch_array($res);
            if ($myrow[0]==0){
                $sql_ins = "INSERT into `$inc` (
                				id
                			) VALUES (
                				'"._DB($col_val_arr[0])."'
                )";
                
                $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
            }
            
            //Получаем номер a_menu
            $a_menu_id='';
            $sql_a_menu="SELECT a_menu.id
                            FROM a_menu
                            WHERE a_menu.inc='"._DB($inc)."'
                            ";
            $res_a_menu = mysql_query($sql_a_menu) or die(mysql_error().'<br>'.$sql_a_menu);
            $myrow_a_menu = mysql_fetch_array($res_a_menu);
            $a_menu_id=$myrow_a_menu[0];
                            
            //Изменяем запись
            $return_sql=other_paste_new_val($col_paste,$inc,$data_['_col'],$col_val_arr,$a_menu_id,$return_sql);
            
            if (isset($return_sql['SQL_UPP']['id']) and mb_strlen($return_sql['SQL_UPP']['id'],'utf-8')>100){
                //echo '<br />+++++++++++++8+++++++++++++++++<br />';
                $sql_upp0 = "";
                foreach($return_sql['SQL_UPP'] as $col_ => $SQL_UPPDATE){
                    if ($col_!='id' and $SQL_UPPDATE!=''){
                        if ($sql_upp0!=''){$sql_upp0.=", ";}
                        $sql_upp0.=" `$col_` = CASE ".$SQL_UPPDATE. " END ";
                        $return_sql['SQL_UPP'][$col_]='';
                    }
                }
                $sql_upp0="UPDATE $inc SET ".$sql_upp0." WHERE id IN (".$return_sql['SQL_UPP']['id'].")";
                //echo '<br />+'.$sql_upp0;
                $return_sql['SQL_UPP']['id']='';
                $res = mysql_query($sql_upp0) or die(mysql_error().'<br>'.$sql_upp0);
                
            }
            
        }
        if (isset($return_sql['SQL_UPP']['id']) and mb_strlen($return_sql['SQL_UPP']['id'],'utf-8')>0){
           // echo '<br />+++++++++++++8+++++++++++++++++<br />';
            $sql_upp0 = "";
            foreach($return_sql['SQL_UPP'] as $col_ => $SQL_UPPDATE){
                if ($col_!='id' and $SQL_UPPDATE!=''){
                    if ($sql_upp0!=''){$sql_upp0.=", ";}
                    $sql_upp0.=" `$col_` = CASE ".$SQL_UPPDATE. " END ";
                }
            }
            $sql_upp0="UPDATE $inc SET ".$sql_upp0." WHERE id IN (".$return_sql['SQL_UPP']['id'].")";
            //echo '<br />+++'.$sql_upp0;
            $res = mysql_query($sql_upp0) or die(mysql_error().'<br>'.$sql_upp0);
            
        }
       
        exit;
       
        echo json_encode($data_);
    }

}


//************************************************************************************************** 
}else{
    
    $_SESSION['error']['auth_'.date('Y-m-d H:i:s')]='Ошибка авторизации! $login="'.@$_SESSION['admin']['login'].'", pass: "'.@$_SESSION['admin']['password'].'"';
    echo 'Ошибка авторизации!';
    
}

?>