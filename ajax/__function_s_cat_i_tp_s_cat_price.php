<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php

    
if (isset($_t)){
    if ($_t=='find'){
         if ($find_tip=='fillter'){
            $r_model_find_text=_GP('r_model_find_text');
            if ($r_model_find_text!=''){
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.=" s_cat.id IN (SELECT r_model_s_cat.id2 FROM r_model_s_cat, r_model WHERE r_model_s_cat.id1=r_model.id AND r_model.name LIKE '%"._DB($r_model_find_text)."%')";
            }
            
        }
        if ($find_tip=='sql'){
            
            
            $col_m[$i]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(CONCAT(`i_tp`.`name`,'@@',`i_tp_s_cat_price`.`price`) SEPARATOR '||'),'') FROM `i_tp_s_cat_price`, `i_tp` WHERE `i_tp`.`id`=`i_tp_s_cat_price`.`i_tp_id` AND `i_tp_s_cat_price`.`s_cat_id`=`"._DB($inc)."`.`id`) AS i_tp_s_cat_price ";
           
        }
    }
    //****************************************************************************************************************************************
    //УДАЛЕНИЕ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='delete'){
        
        // проверяем удаляем одну строку и несколько
        if (!is_array($nomer)){
            $nomer_="='"._DB($nomer)."'";
        }else{
            $nomer_=" IN ('".implode("','",$nomer)."')";
        }
        
        //Удаляем связи 
        $sql_i_tp_s_cat_price = "DELETE 
        			FROM i_tp_s_cat_price 
        				WHERE s_cat_id $nomer_
        ";
        $mt = microtime(true);
        $res_i_tp_s_cat_price = mysql_query($sql_i_tp_s_cat_price); if (!$res_i_tp_s_cat_price){echo $sql_i_tp_s_cat_price;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_i_tp_s_cat_price;$data_['_sql']['time'][]=$mt;
        
        
    }
    if ($_t=='save'){
       
          if ($data_['chk_change'][$key]=='1'){ //если разрешен доступ на изменение
            //получаем массив свойств
            if ($nomer==''){echo 'i_tp_s_cat_price: $nomer=""<br />';exit();}
            
            //массовое изменение записей
            $nomer_arr=array();
            if (strstr($nomer,',')==true){
                $nomer_arr = explode(",", $nomer);
                foreach($nomer_arr as $key_nom =>$nomer_){
                    $nomer_arr[$key_nom]=trim($nomer_);
                }
            }
            if (count($nomer_arr)==0){
                $nomer_arr[0]=$nomer;
            }
            
            //УДАЛЯЕМ СТАРЫЕ ЦЕНЫ
            $sql_PROP = "DELETE 
            			FROM i_tp_s_cat_price 
            				WHERE s_cat_id IN ('".implode("','",$nomer_arr)."')
            ";
            $mt = microtime(true);
            $res_ = mysql_query($sql_PROP); if (!$res_){echo $sql_PROP;exit();}
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_PROP;$data_['_sql']['time'][]=$mt;
            
            
            $SQL_INS_I_TP_S_CAT_PRICE="";
            $sql_i_tp = "SELECT i_tp.id
            				FROM i_tp 
            					WHERE i_tp.chk_active='1'
            ";
             
            $mt = microtime(true);
            $res_i_tp = mysql_query($sql_i_tp) or die(mysql_error().'<br>'.$sql_i_tp);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_tp;$data_['_sql']['time'][]=$mt;
            
            for ($myrow_i_tp = mysql_fetch_array($res_i_tp); $myrow_i_tp==true; $myrow_i_tp = mysql_fetch_array($res_i_tp))
            {
                $i_tp_s_cat_price_cur=_GP('i_tp_s_cat_price_'.$myrow_i_tp['id']);
                if ($i_tp_s_cat_price_cur!=''){
                    foreach ($nomer_arr as $kkey => $nomer_){
                        if ($SQL_INS_I_TP_S_CAT_PRICE!=''){$SQL_INS_I_TP_S_CAT_PRICE.=", ";}
                        $SQL_INS_I_TP_S_CAT_PRICE.="('"._DB($myrow_i_tp['id'])."','"._DB($nomer_)."','".$i_tp_s_cat_price_cur."')";
                    }
                }
            }
            
            //Добавляем
            if($SQL_INS_I_TP_S_CAT_PRICE!=''){
                 $SQL_INS_I_TP_S_CAT_PRICE="INSERT into i_tp_s_cat_price (
                				i_tp_id,
                				s_cat_id,
                                price
                			) VALUES ".$SQL_INS_I_TP_S_CAT_PRICE;
                            
                $mt = microtime(true);
                $res_ = mysql_query($SQL_INS_I_TP_S_CAT_PRICE); if (!$res_){echo $SQL_INS_I_TP_S_CAT_PRICE;exit();}
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$SQL_INS_I_TP_S_CAT_PRICE;$data_['_sql']['time'][]=$mt;
            
            }
            
       }
        
    }
    if ($_t=='change'){
        
        $data_['_d'][$data_['col'][$i]]=array();
        
        $sql_prop_change = "SELECT i_tp_s_cat_price.i_tp_id, i_tp_s_cat_price.price
        				FROM i_tp_s_cat_price
        					WHERE i_tp_s_cat_price.s_cat_id='"._DB($nomer)."' 
                            
        					
        ";
        $mt = microtime(true);
        $res_prop_change = mysql_query($sql_prop_change); if (!$res_prop_change){echo $sql_prop_change;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop_change;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_prop_change = mysql_fetch_array($res_prop_change),$ii=0; $myrow_prop_change==true; $myrow_prop_change = mysql_fetch_array($res_prop_change),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$ii][0]=$myrow_prop_change[0];
            $data_['_d'][$data_['col'][$i]][$ii][1]=$myrow_prop_change[1];
            
        }
        
    }
    if ($_t=='copy'){
        
        $data_['_d'][$data_['col'][$i]][$nomer]=array();
        
        $sql_prop_change = "SELECT i_tp_s_cat_price.i_tp_id, i_tp_s_cat_price.price
        				FROM i_tp_s_cat_price
        					WHERE i_tp_s_cat_price.s_cat_id='"._DB($nomer)."' 
                            
        					
        ";
        $mt = microtime(true);
        $res_prop_change = mysql_query($sql_prop_change); if (!$res_prop_change){echo $sql_prop_change;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop_change;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_prop_change = mysql_fetch_array($res_prop_change),$ii=0; $myrow_prop_change==true; $myrow_prop_change = mysql_fetch_array($res_prop_change),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][0]=$myrow_prop_change[0];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][1]=$myrow_prop_change[1];
            
        }
        
    }
    
    // ******************** ИМПОРТ *******************************
    elseif ($_t=='paste'){ 
        
        //Удаляем старые цены
        $sql_del = "DELETE
        				FROM i_tp_s_cat_price
        					WHERE i_tp_s_cat_price.s_cat_id='"._DB($id)."' 
                            
        					
        ";
        $res_del = mysql_query($sql_del); if (!$res_del){echo $sql_del;exit();}
        
        $val_arr=array();
        if (isJSON($col_val_arr[$key_col])==true){
            $val_arr=json_decode($col_val_arr[$key_col]);
        }else{
            $val_arr[0]=$col_val_arr[$key_col];
        }
        //есть  в импортируемом файле
        if (count($val_arr)>0){
            foreach($val_arr as $price_key=> $price_arr){
                if (isset($price_arr[0]) and isset($price_arr[1]) and $price_arr[0]!='' and $price_arr[1]!=''){
                    $i_tp_id=$price_arr[0];
                    $price=$price_arr[1];
                    
                    $sql_price="SELECT 
                                COUNT(*) 
                                    FROM i_tp
                                    WHERE i_tp.id='"._DB($i_tp_id)."'
                                    ";
                    $res_price = mysql_query($sql_price) or die(mysql_error().'<br>'.$sql_price);
                    $myrow_price = mysql_fetch_array($res_price);
                    if ($myrow_price[0]>0){
                        //Добавляем данные по филиалам
                        $sql_ins = "INSERT into i_tp_s_cat_price (
                        				i_tp_id,
                        				price,
                                        s_cat_id
                        			) VALUES (
                        				'"._DB($i_tp_id)."',
                        				'"._DB($price)."',
                                        '"._DB($id)."'
                        )";
                        
                        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    }
                }
            }
            
        }
    }
    
}
else{//INCLUDE из obrabotchik -> export_csv
    if ($inc=='export_csv'){
        $col_m[$ii]="(SELECT GROUP_CONCAT(CONCAT(i_tp.name,': ', i_tp_s_cat_price.price) SEPARATOR '; ') FROM i_tp_s_cat_price, i_tp WHERE i_tp_s_cat_price.s_cat_id=s_cat.id AND i_tp_s_cat_price.i_tp_id=i_tp.id)";//$data_['col'][$key_col];
        
        //вывод названия столбца
        if ($script_opt5=='1'){//пустое поле
            if ($txt_menu!=''){$txt_menu.=$script_opt1;}
            $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
        }
    } 
}
?>