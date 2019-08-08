<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php 
if (isset($_t)){
    
    //****************************************************************************************************************************************
    //ПОИСК СВОЙСТВ
    //****************************************************************************************************************************************
    if ($_t=='find'){

        if ($find_tip=='fillter'){
            $r_model_find_text=_GP('r_model_find_text');
            if ($r_model_find_text!=''){
                if ($WHERE!='') {$WHERE.=' AND ';}
                $WHERE.=" s_cat.id IN (SELECT r_model_s_cat.id2 FROM r_model_s_cat, r_model WHERE r_model_s_cat.id1=r_model.id AND r_model.name LIKE '%"._DB($r_model_find_text)."%')";
            }
            
        }
        if ($find_tip=='sql'){
            
            
            $col_m[$i]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(CONCAT(`r_tip_oborud`.`name`,' ',`r_brend`.`name`,' ',`r_model`.`name`) SEPARATOR '||'),'') FROM `r_model_s_cat`,`r_model`, `r_tip_oborud`,`r_brend` WHERE `r_model`.`r_brend_id`=`r_brend`.`id` AND `r_model`.`r_tip_oborud_id`=`r_tip_oborud`.`id` AND `r_model_s_cat`.`id1`=`r_model`.`id` AND `r_model_s_cat`.`id2`=`"._DB($inc)."`.`id`) AS r_model ";
           
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
        $sql_del_prop = "DELETE 
        			FROM r_model_s_cat 
        				WHERE id2 $nomer_
        ";
        $mt = microtime(true);
        $res_prop = mysql_query($sql_del_prop); if (!$res_prop){echo $sql_del_prop;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_del_prop;$data_['_sql']['time'][]=$mt;
        
        
    }
    //****************************************************************************************************************************************
    //ИЗМЕНЕНИЕ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='change'){ 
        
        $data_['_d'][$data_['col'][$i]]=array();
        
        $sql_prop_change = "SELECT r_model.id, r_tip_oborud.name, r_brend.name, r_model.name
        				FROM r_model_s_cat, r_model, r_brend, r_tip_oborud
        					WHERE r_model_s_cat.id2='"._DB($nomer)."' 
                            AND r_model_s_cat.id1=r_model.id
                            AND r_brend.id=r_model.r_brend_id
                            AND r_tip_oborud.id=r_model.r_tip_oborud_id
                            
                            ORDER BY r_model.name
        					
        ";
        $mt = microtime(true);
        $res_prop_change = mysql_query($sql_prop_change); if (!$res_prop_change){echo $sql_prop_change;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop_change;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_prop_change = mysql_fetch_array($res_prop_change),$ii=0; $myrow_prop_change==true; $myrow_prop_change = mysql_fetch_array($res_prop_change),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$ii][0]=$myrow_prop_change[0];
            $data_['_d'][$data_['col'][$i]][$ii][1]=$myrow_prop_change[1].' '.$myrow_prop_change[2].' '.$myrow_prop_change[3];
            
        }
        
    }
    //****************************************************************************************************************************************
    //выгрузка
    //****************************************************************************************************************************************
    elseif ($_t=='copy'){ 
        
        $data_['_d'][$data_['col'][$i]][$nomer]=array();
        
        $sql_prop_change = "SELECT r_tip_oborud.name, r_brend.name, r_model.name
        				FROM r_model_s_cat, r_model, r_brend, r_tip_oborud
        					WHERE r_model_s_cat.id2='"._DB($nomer)."' 
                            AND r_model_s_cat.id1=r_model.id
                            AND r_brend.id=r_model.r_brend_id
                            AND r_tip_oborud.id=r_model.r_tip_oborud_id
                            
                            ORDER BY r_model.name
        					
        ";
        $mt = microtime(true);
        $res_prop_change = mysql_query($sql_prop_change); if (!$res_prop_change){echo $sql_prop_change;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop_change;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_prop_change = mysql_fetch_array($res_prop_change),$ii=0; $myrow_prop_change==true; $myrow_prop_change = mysql_fetch_array($res_prop_change),$ii++)
        {
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][0]=$myrow_prop_change[0];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][1]=$myrow_prop_change[1];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][2]=$myrow_prop_change[2];
            
        }
        
    }
    
    // ******************** ИМПОРТ *******************************
    elseif ($_t=='paste'){ 
        
        
        //Удаляем старые записи моделей
        $sql_del = "DELETE 
        			FROM r_model_s_cat 
        				WHERE id2='"._DB($id)."'
        ";
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        
        
        $val_arr=array();
        if (isJSON($col_val_arr[$key_col])==true){
            $val_arr=json_decode($col_val_arr[$key_col]);
        }else{
            $val_arr[0]=$col_val_arr[$key_col];
        }
        //есть свойства в импортируемом файле
        if (count($val_arr)>0){
            foreach($val_arr as $key_model=>$val_model_arr){
                if (isset($val_model_arr[0]) and isset($val_model_arr[1]) and isset($val_model_arr[2])
                    and $val_model_arr[0]!='' and $val_model_arr[1]!='' and $val_model_arr[2]!=''){
                
                $r_tip_oborud_name=$val_model_arr[0];
                $r_brend_name=$val_model_arr[1];
                $r_model_name=$val_model_arr[2];
                
                ///ТИП ОБОРУДОВАНИЯ
                $sql_tip="SELECT 
                            IF(COUNT(*)>0,r_tip_oborud.id,'')
                                FROM r_tip_oborud
                                WHERE r_tip_oborud.name='"._DB($r_tip_oborud_name)."'
                                ";
                $res_tip = mysql_query($sql_tip) or die(mysql_error().'<br>'.$sql_tip);
                $myrow_tip = mysql_fetch_array($res_tip);
                $r_tip_oborud_id=$myrow_tip[0];
                if ($r_tip_oborud_id==''){
                    $sql_ins = "INSERT into r_tip_oborud (
                    				name
                    			) VALUES (
                                    '"._DB($r_tip_oborud_name)."'
                    )";
                    
                    $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    $r_tip_oborud_id = mysql_insert_id();
                    
                }
                
                ///БРЕНД ОБОРУДОВАНИЯ
                $sql_brend="SELECT 
                            IF(COUNT(*)>0,r_brend.id,'')
                                FROM r_brend
                                WHERE r_brend.name='"._DB($r_brend_name)."'
                                ";
                $res_brend = mysql_query($sql_brend) or die(mysql_error().'<br>'.$sql_brend);
                $myrow_brend = mysql_fetch_array($res_brend);
                $r_brend_id=$myrow_brend[0];
                if ($r_brend_id==''){
                    $sql_ins = "INSERT into r_brend (
                    				name
                    			) VALUES (
                                    '"._DB($r_brend_name)."'
                    )";
                    
                    $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    $r_brend_id = mysql_insert_id();
                }
                
                //МОДЕЛЬ
                $sql_model="SELECT 
                            IF(COUNT(*)>0,r_model.id,'')
                                FROM r_model
                                WHERE r_model.name='"._DB($r_model_name)."'
                                AND r_model.r_brend_id='"._DB($r_brend_id)."'
                                AND r_model.r_tip_oborud_id='"._DB($r_tip_oborud_id)."'
                                ";
                $res_model = mysql_query($sql_model) or die(mysql_error().'<br>'.$sql_model);
                $myrow_model = mysql_fetch_array($res_model);
                $r_model_id=$myrow_model[0];
                if ($r_model_id==''){
                    $sql_ins = "INSERT into r_model (
                                    r_brend_id,
                                    r_tip_oborud_id,
                    				name
                    			) VALUES (
                                    '"._DB($r_brend_id)."',
                                    '"._DB($r_tip_oborud_id)."',
                                    '"._DB($r_model_name)."'
                    )";
                    
                    $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    $r_model_id = mysql_insert_id();
                }
                
                
                $sql_ins = "INSERT into r_model_s_cat (
                				id1,
                                id2
                			) VALUES (
                                '"._DB($r_model_id)."',
                                '"._DB($id)."'
                )";
                
                $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                
                
            }
            }
        }
    }
    //****************************************************************************************************************************************
    //СОХРАНЕНИЕ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='save'){
       
        if ($data_['chk_change'][$key]=='1'){ //если разрешен доступ на ищменение
        //получаем массив свойств
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
        
        
        $r_model=_GP('r_model');
        
        $r_model_arr=array();
        if ($r_model!=''){
            if (strstr($r_model,',')==true){
                $r_model_arr=explode(',',$r_model);
            }else{
                $r_model_arr[0]=$r_model;
            }
        }
       
        
        //УДАЛЯЕМ СТАРЫЕ СВОЙСТВА
        $sql_PROP = "DELETE 
        			FROM r_model_s_cat 
        				WHERE id2 IN ('".implode("','",$nomer_arr)."')
        ";
        $mt = microtime(true);
        $res_ = mysql_query($sql_PROP); if (!$res_){echo $sql_PROP;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_PROP;$data_['_sql']['time'][]=$mt;
        
        //Перебор по массиву - формирование sql
        $SQL_INS_R_MODEL="";
        foreach($r_model_arr as $key => $r_model_id){
            foreach ($nomer_arr as $kkey => $nomer_){
                if ($SQL_INS_R_MODEL!=''){$SQL_INS_R_MODEL.=", ";}
                $SQL_INS_R_MODEL.="('"._DB($r_model_id)."','"._DB($nomer_)."')";
            }
        }
        
        if ($SQL_INS_R_MODEL!=''){
            $SQL_INS_R_MODEL="INSERT into r_model_s_cat (
            				id1,
            				id2
            			) VALUES ".$SQL_INS_R_MODEL;
                        
            $mt = microtime(true);
            $res_ = mysql_query($SQL_INS_R_MODEL); if (!$res_){echo $SQL_INS_R_MODEL;exit();}
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$SQL_INS_R_MODEL;$data_['_sql']['time'][]=$mt;
        
            
            
        }
        
        }
    }
    

    
    
    //****************************************************************************************************************************************
    //ПАРСИНГ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='parsing'){
        
    
    }
    //****************************************************************************************************************************************
    // не определен тип
    //****************************************************************************************************************************************
    else{
        echo 'Не определен тип function_prop! $_t='.$_t;
        exit();
    }
}
else{//INCLUDE из obrabotchik -> export_csv
    if ($inc=='export_csv'){
        $col_m[$ii]="(SELECT GROUP_CONCAT(CONCAT(r_tip_oborud.name,' ', r_brend.name,' ', r_model.name) SEPARATOR '; ') FROM r_model_s_cat, r_model,r_brend,r_tip_oborud WHERE r_model_s_cat.id2=s_cat.id AND r_model_s_cat.id1=r_model.id AND r_model.r_tip_oborud_id=r_tip_oborud.id AND r_model.r_brend_id=r_brend.id)";//$data_['col'][$key_col];
        
        //вывод названия столбца
        if ($script_opt5=='1'){//пустое поле
            if ($txt_menu!=''){$txt_menu.=$script_opt1;}
            $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
        }
    }
}
?>