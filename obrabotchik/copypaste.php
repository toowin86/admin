<?php
$data_=array();
    $inc=_GP('_inc');
        if ($inc==''){echo 'Отсутствует переменная _inc';exit;}
    $nomer=_GP('_nomer');
        if ($nomer==''){echo 'Отсутствуют номера для выгрузки ';exit;}
    if (strstr($nomer,',')==true){
        $nomer_arr=explode(',',$nomer);
        $nomer_where=" IN ('".implode("','",$nomer_arr)."')";
    }else{
        $nomer_where=" = '"._DB($nomer)."'";
        $nomer_arr[0]=$nomer;
    }
    
    //протакол
    $protacol=get_protocol();

    $data_['id'][0]='';
    $data_['col'][0]='id';
    $data_['tip'][0]='id';
    $data_['chk_change'][0]='1';
    $data_['a_menu_id'][0]='';

     $sql = "SELECT a_col.id, a_col.col, a_col.tip, a_col.chk_change, a_menu.id
        				FROM a_menu, a_col 
        					WHERE a_col.a_menu_id=a_menu.id
                            AND  a_col.chk_change='1'
                            AND  a_menu.inc='"._DB($inc)."' 
                            AND a_col.id IN (
                                            SELECT a_admin_a_col.id2
                                                FROM a_admin_a_col, a_admin
                                                    WHERE a_admin_a_col.id1=a_admin.id
                                                        AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                                                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                        )
        	"; 
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error());
        $mt = microtime(true)-$mt;// $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['id'][$i]=$myrow[0];
            $data_['col'][$i]=$myrow[1];
            $data_['tip'][$i]=$myrow[2];
            $data_['chk_change'][$i]=$myrow[3];
            $data_['a_menu_id'][$i]=$myrow[4];
        }
  
    foreach($nomer_arr as $key => $nomer){
        $SQL_COL='';
        
        foreach($data_['col'] as $i => $col){
            
            $myrow[0]=$data_['id'][$i];
            $inc_id=$data_['a_menu_id'][$i];
            
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
                
                $tbl_connect_=$inc."_".$connect_arr['inc'][$i];
                if ($connect_arr['tbl'][$i]!=''){
                   $tbl_connect_=$connect_arr['tbl'][$i]; 
                }
                
                $connect_arr['col_'][$i]=$col;
                $data_['_d'][$data_['col'][$i]][$nomer]=array();
                
                
                $sql_connect = "SELECT `".$tbl_connect_."`.`id2`,
                                        (SELECT `"._DB($connect_arr['inc'][$i])."`.`"._DB($connect_arr['col'][$i])."` FROM `"._DB($connect_arr['inc'][$i])."` WHERE `"._DB($connect_arr['inc'][$i])."`.`id`=`".$tbl_connect_."`.`id2` LIMIT 1)
                				FROM `".$tbl_connect_."`
                					WHERE `".$tbl_connect_."`.`id1` ='"._DB($nomer)."'
                						
                "; 
                $mt = microtime(true);
                $res_connect = mysql_query($sql_connect) or die($sql_connect.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;
           
           
                for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
                {
                    //print_rf($myrow_connect);
                    if ($connect_arr['chk'][$i]=='1'){
                        $data_['_d'][$data_['col'][$i]][$nomer][$myrow_connect[0]]=$myrow_connect[1];
                    }
                    else{
                        if (!in_array($myrow_connect[0],$data_['_d'][$data_['col'][$i]][$nomer])){
                            $data_['_d'][$data_['col'][$i]][$nomer][]=$myrow_connect[0];
                        }
                        
                    }
                    
                }
           }
        }
        elseif ($data_['tip'][$i]=='Функция'){
            
            $file_function='ajax/__function_'.$inc.'_'.$data_['col'][$i].'.php';
            if (file_exists($file_function)){
                $_t='copy';
                include $file_function;
            }else{
                echo 'Нет файла функции: __function_'.$inc.'_'.$data_['col'][$i].'.php<br />';
            }
            
             
        }
        elseif ($data_['tip'][$i]=='Фото'){
            if ($data_['chk_change'][$i]=='1'){
                //***********************************
                $data_['_d'][$data_['col'][$i]][$nomer]=array();
                $sql_img = "SELECT  `a_photo`.`id`,
                                    `a_photo`.`tip`,
                                    `a_photo`.`img`,
                                    `a_photo`.`comments`
                                
                                
            				FROM `a_photo`
                            
            					WHERE `a_photo`.`a_menu_id`='"._DB($inc_id)."'
                                AND `a_photo`.`row_id`  ='"._DB($nomer)."'
                                
                                ORDER BY `a_photo`.`sid`
                              
            	"; //BY FIELD(`tip`,'Основное') DESC, 
                $mt = microtime(true);
                $res_img = mysql_query($sql_img) or die($sql_img.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_img;$data_['_sql']['time'][]=$mt;
           
           
                for ($myrow_img = mysql_fetch_array($res_img),$j=0; $myrow_img==true; $myrow_img = mysql_fetch_array($res_img),$j++)
                {
                   
                    $file='../i/'.$inc.'/original/'.$myrow_img['img'];
                    if (file_exists($file) and $myrow_img['img']!=''){
                        //$data_['_d'][$data_['col'][$i]][$nomer]['info'][$j]=@getimagesize($file);
                        //$data_['_d'][$data_['col'][$i]][$nomer]['id'][$j]=$myrow_img['id'];
                        $data_['_d'][$data_['col'][$i]][$nomer][0][$j]=$protacol.$_SERVER['SERVER_NAME'].'/i/'.$inc.'/original/'.$myrow_img['img'];
                        $data_['_d'][$data_['col'][$i]][$nomer][1][$j]=$myrow_img['tip'];
                        $data_['_d'][$data_['col'][$i]][$nomer][2][$j]=$myrow_img['comments'];
                    }else{
                    }
                }
            }
        }
        else{
            if ($data_['chk_change'][$i]=='1'){
                $col_m[$i]=$data_['col'][$i];
                if ($SQL_COL!=''){$SQL_COL.=', ';}
                $SQL_COL.="`"._DB($inc)."`.`".$col_m[$i]."`";
                $data_['_d'][$col_m[$i]][$nomer]='';
            }
        }
    }
    
    
        if ($SQL_COL!=''){
        $sql_main = "SELECT `"._DB($inc)."`.`id`, "._DB($SQL_COL)." 
                            
        				FROM `"._DB($inc)."`
                        
                        WHERE `"._DB($inc)."`.`id` ='"._DB($nomer)."'
                        
                        LIMIT 1
        ";
        
    //echo $sql_main;
        $data_['_sql'][]=$sql_main;
        $mt = microtime(true);
        $res_main = mysql_query($sql_main) or die($sql_main.'<br />'.mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_main;$data_['_sql']['time'][]=$mt;
           
           
        $myrow_main = mysql_fetch_array($res_main);
        $data_['_d']['id'][$nomer]=$myrow_main[0];
            
        foreach($col_m as $i => $col_){
            $col_=$data_['col'][$i];
            //предварительная обработка
            if ($data_['tip'][$i]=='Дата'){
                $myrow_main[$col_]=conv_('data_from_db',$myrow_main[$col_]);
            }
            elseif ($data_['tip'][$i]=='Дата-время'){
                $myrow_main[$col_]=conv_('data_from_db',$myrow_main[$col_]);
            }
            elseif ($data_['tip'][$i]=='Телефон'){
                $myrow_main[$col_]=conv_('phone_from_db',$myrow_main[$col_]);
            }
            
            $data_['_d'][$col_][$nomer]=$myrow_main[$col_];
        }
    }
    
}

//print_rf($col_m);
//print_rf($data_['_d']);exit;
//

//******************************************
//********************** ВЫВОД CSV *********
//******************************************
/**/
header('Date: '.date('D M j G:i:s T Y'));
header('Last-Modified: '.date('D M j G:i:s T Y'));
header('Content-Disposition: attachment;filename="EXPORT_CSV2.csv"');
header('Content-Type: application/vnd.ms-excel');//; charset=utf-8
header('Cache-Control: no-store, no-cache');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

echo "\xEF\xBB\xBF";
$create_data=array();

foreach($data_['_d'] as $col_ => $col_arr){
    $create_data[0][]=$col_;
}
echo kama_create_csv_file( $create_data );

$j=1;
foreach($data_['_d']['id'] as $nomer => $id){
    $create_data=array();
    $create_data[$j]=array();
    foreach($data_['_d'] as $col_ => $col_arr){
        if (is_array($data_['_d'][$col_][$nomer])){$m=@json_encode($data_['_d'][$col_][$nomer],JSON_UNESCAPED_UNICODE,JSON_PARTIAL_OUTPUT_ON_ERROR); if (!$m){$m=json_encode($data_['_d'][$col_][$nomer]);} $data_['_d'][$col_][$nomer]=$m;}//,JSON_UNESCAPED_UNICODE,JSON_PARTIAL_OUTPUT_ON_ERROR
        if (mb_strstr($data_['_d'][$col_][$nomer],"\n",false,'utf-8')==true){
            $data_['_d'][$col_][$nomer]=$data_['_d'][$col_][$nomer];//str_replace("\n","\\n",)
        }
        $create_data[$j][]=$data_['_d'][$col_][$nomer];
    }
    //print_rf($create_data);exit;
    echo "\n".kama_create_csv_file( $create_data );
    unset($create_data);
   $j++;
}

   
   
exit;
?>