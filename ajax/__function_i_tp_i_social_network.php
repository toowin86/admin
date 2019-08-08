<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php

    
if (isset($_t)){
    if ($_t=='find'){
        if ($find_tip=='fillter'){
        
        }
        if ($find_tip=='sql'){
            $col_m[$i]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(CONCAT(`i_social_network`.`name`,': ',`i_tp_i_social_network`.`name`) SEPARATOR '||'),'') FROM `i_tp_i_social_network`, `i_social_network` WHERE `i_tp_i_social_network`.`i_tp_id`=`i_tp`.`id` AND `i_tp_i_social_network`.`i_social_network_id`=`i_social_network`.`id` ) AS i_social_network ";
           
        }    
    }
    //****************************************************************************************************************************************
    //УДАЛЕНИЕ СОЦ СЕТЕЙ
    //****************************************************************************************************************************************
    elseif ($_t=='delete'){
        
        // проверяем удаляем одну строку и несколько
        if (!is_array($nomer)){
            $nomer_="='"._DB($nomer)."'";
        }else{
            $nomer_=" IN ('".implode("','",$nomer)."')";
        }
        
        //Удаляем связи 
        $sql_del_i_social = "DELETE 
        			FROM i_tp_i_social_network 
        				WHERE i_tp_id $nomer_
        ";
        $mt = microtime(true);
        $res_i_social = mysql_query($sql_del_i_social); if (!$res_i_social){echo $sql_del_i_social;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_del_i_social;$data_['_sql']['time'][]=$mt;
        
        
    }
    //****************************************************************************************************************************************
    //СОХРАНЕНИЕ СОЦ СЕТЕЙ
    //****************************************************************************************************************************************
    
    if ($_t=='save'){
        
        
        if ($nomer==''){echo 'r_model_s_cat_save: $nomer=""<br />';exit();}
        
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
        
         //УДАЛЯЕМ СТАРЫЕ СВОЙСТВА
        $sql_PROP = "DELETE 
        			FROM i_tp_i_social_network 
        				WHERE i_tp_id IN ('".implode("','",$nomer_arr)."')
        ";
        $mt = microtime(true);
        $res_ = mysql_query($sql_PROP); if (!$res_){echo $sql_PROP;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_PROP;$data_['_sql']['time'][]=$mt;
        
        
        
        $sql_i_social = "SELECT i_social_network.id
         				FROM i_social_network 
         					WHERE i_social_network.chk_active='1'
         					
         ";
          
         $mt = microtime(true);
         $res_i_social = mysql_query($sql_i_social) or die(mysql_error().'<br>'.$sql_i_social);
         $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_social;$data_['_sql']['time'][]=$mt;
         
         for ($myrow_i_social = mysql_fetch_array($res_i_social); $myrow_i_social==true; $myrow_i_social = mysql_fetch_array($res_i_social))
         {
            $val_=_GP('i_tp_i_social_network_id_'.$myrow_i_social[0]);
            if ($val_!=''){
                foreach($nomer_arr as $k_i_social => $nomer_){
                
                    $sql_i_social_ins = "INSERT into i_tp_i_social_network (
                    				i_tp_id,
                    				i_social_network_id,
                                    name
                    			) VALUES (
                    				'"._DB($nomer_)."',
                    				'"._DB($myrow_i_social[0])."',
                    				'"._DB($val_)."'
                    )";
                    
                    $mt = microtime(true);
                    $res_i_social_ins = mysql_query($sql_i_social_ins) or die(mysql_error().'<br>'.$sql_i_social_ins);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_social_ins;$data_['_sql']['time'][]=$mt;
                        
                }
            }
         }
        
        
    }
    if ($_t=='change'){
        $data_['_d'][$data_['col'][$i]]=array();
        
        $sql_i_social_change = "SELECT i_social_network.id, i_tp_i_social_network.name
        				FROM i_tp_i_social_network, i_social_network
        					WHERE i_tp_i_social_network.i_tp_id='"._DB($nomer)."' 
                            AND i_social_network.id=i_tp_i_social_network.i_social_network_id
                            AND i_social_network.chk_active='1'
                            
                            ORDER BY i_social_network.sid
        					
        ";
        $mt = microtime(true);
        $res_i_social_change = mysql_query($sql_i_social_change); if (!$res_i_social_change){echo $sql_i_social_change;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_i_social_change;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_i_social_change = mysql_fetch_array($res_i_social_change); $myrow_i_social_change==true; $myrow_i_social_change = mysql_fetch_array($res_i_social_change))
        {
            $data_['_d'][$data_['col'][$i]][$myrow_i_social_change[0]]=$myrow_i_social_change[1];
            
        }
       
    }
    if ($_t=='copy'){
        $data_['_d'][$data_['col'][$i]][$nomer]=array();
        
        $sql_i_social_change = "SELECT i_social_network.name, i_tp_i_social_network.name
        				FROM i_tp_i_social_network, i_social_network
        					WHERE i_tp_i_social_network.i_tp_id='"._DB($nomer)."' 
                            AND i_social_network.id=i_tp_i_social_network.i_social_network_id
                            AND i_social_network.chk_active='1'
                            
                            ORDER BY i_social_network.sid
        					
        ";
        $mt = microtime(true);
        $res_i_social_change = mysql_query($sql_i_social_change); if (!$res_i_social_change){echo $sql_i_social_change;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_i_social_change;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_i_social_change = mysql_fetch_array($res_i_social_change),$ii=0; $myrow_i_social_change==true; $myrow_i_social_change = mysql_fetch_array($res_i_social_change),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][0]=$myrow_i_social_change[0];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][1]=$myrow_i_social_change[1];
            
        }
       
    }
    
    // ******************** ИМПОРТ *******************************
    elseif ($_t=='paste'){ 
        
        
        //Удаляем старые записи моделей
        $sql_del = "DELETE 
        			FROM i_tp_i_social_network 
        				WHERE i_tp_id='"._DB($id)."'
        ";
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        
        
        $val_arr=array();
        if (isJSON($col_val_arr[$key_col])==true){
            $val_arr=json_decode($col_val_arr[$key_col]);
        }else{
            $val_arr[0]=$col_val_arr[$key_col];
        }
        
        //есть соц.сети
        if (count($val_arr)>0){
            //Перебор по свойствам
            foreach($val_arr as $soc_key=> $soc_arr){
                if (isset($soc_arr[0]) and isset($soc_arr[1]) and $soc_arr[0]!='' and $soc_arr[1]!=''){
                    $soc_name=$soc_arr[0];
                    $soc_val=$soc_arr[1];
                    
                        ///СОЦ.СЕТЬ
                        $sql_soc="SELECT 
                                    IF(COUNT(*)>0,i_social_network.id,'')
                                        FROM i_social_network
                                        WHERE i_social_network.name='"._DB($soc_name)."'
                                        ";
                        $res_soc = mysql_query($sql_soc) or die(mysql_error().'<br>'.$sql_soc);
                        $myrow_soc = mysql_fetch_array($res_soc);
                        $soc_id=$myrow_soc[0];
                        if ($soc_id==''){
                            $sql_ins = "INSERT into i_social_network (
                            				name
                            			) VALUES (
                                            '"._DB($soc_name)."'
                            )";
                            
                            $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                            $soc_id = mysql_insert_id();
                            
                        }
                        
                        $sql_ins = "INSERT into i_tp_i_social_network (
                        				name,
                                        i_tp_id,
                                        i_social_network_id
                        			) VALUES (
                                        '"._DB($soc_val)."',
                                        '"._DB($id)."',
                                        '"._DB($soc_id)."'
                        )";
                        //echo $sql_ins.'<br />';
                        $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                            
                        
                    
                }
            }
        }
    }
}
else{//INCLUDE из obrabotchik -> export_csv
    if ($inc=='export_csv'){
        
    }
    
}
?>