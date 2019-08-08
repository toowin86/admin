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
            
            $sql_prop = "SELECT s_prop.id
            				FROM s_prop 
            					WHERE s_prop.data_tip='Число'
            ";
             
            $mt = microtime(true);
            $res_prop = mysql_query($sql_prop);if (!$res){echo $sql_prop;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_prop;$data_['_sql']['time'][]=$mt;
            
            for ($myrow_prop = mysql_fetch_array($res_prop); $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop))
            {
                $__function_s_cat_prop_input=_GP('__function_s_cat_prop__input_'.$myrow_prop[0],array());
                
                $USL_='';
                //ОТ 
                if (isset($__function_s_cat_prop_input[0]) and $__function_s_cat_prop_input[0]!=''){
                    $__function_s_cat_prop_input[0]=$__function_s_cat_prop_input[0]-0;
                    $USL_.=" AND s_prop_val.val >= "._DB($__function_s_cat_prop_input[0])."";
                }
                //ДО
                if (isset($__function_s_cat_prop_input[1]) and $__function_s_cat_prop_input[1]!=''){
                    $__function_s_cat_prop_input[1]=$__function_s_cat_prop_input[1]-0;
                    $USL_.=" AND s_prop_val.val <= "._DB($__function_s_cat_prop_input[1])."";
                }
                
                if ($USL_!=''){
                    if ($WHERE!='') {$WHERE.=' AND ';}
                    $WHERE.=" s_cat.id IN (SELECT s_cat_s_prop_val.id1 FROM s_cat_s_prop_val, s_prop_val WHERE s_cat_s_prop_val.id2=s_prop_val.id AND s_prop_val.s_prop_id='"._DB($myrow_prop[0])."' $USL_)";
                }
                
                
            }
            
            
            $__function_s_cat_prop__arr=_GP('__function_s_cat_prop__select',array());
            if (!is_array($__function_s_cat_prop__arr)){
                if ($__function_s_cat_prop__arr!=''){
                    $n=$__function_s_cat_prop__arr;unset($__function_s_cat_prop__arr);
                    $__function_s_cat_prop__arr[0]=$n;unset($n);
                    
                }
            }
            if (count($__function_s_cat_prop__arr)>0){
                 if (count($__function_s_cat_prop__arr)>1){
                    
                    $s_prop_id_arr=array();
                    $sql__prop = "SELECT s_prop_val.id, s_prop_val.s_prop_id 
                    				FROM s_prop_val 
                    					WHERE s_prop_val.id IN ('".implode("','",$__function_s_cat_prop__arr)."')
                    "; 
                    
                    $mt = microtime(true);
                    $res__prop = mysql_query($sql__prop); if (!$res__prop){echo $sql__prop;exit();}
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql__prop;$data_['_sql']['time'][]=$mt;
                    
                    for ($myrow__prop = mysql_fetch_array($res__prop); $myrow__prop==true; $myrow__prop = mysql_fetch_array($res__prop))
                    {
                        $s_prop_id_arr[$myrow__prop[1]][]=$myrow__prop[0];
                    }
                    
                    
                    
                    //перебор по свойствам
                    foreach($s_prop_id_arr as $s_prop_id => $s_prop_val_arr){
                        
                        
                                                        
                                                    
                        $sql_prop = "SELECT s_cat_s_prop_val.id1 
                                                    FROM s_cat_s_prop_val, s_prop_val
                                                        WHERE s_prop_val.id=s_cat_s_prop_val.id2
                                                        AND s_prop_val.id IN ('".implode("','",$s_prop_val_arr)."')
                                                        AND s_prop_val.s_prop_id='"._DB($s_prop_id)."'
                                                        
                        "; 
                        
                        $mt = microtime(true);
                        $res_prop = mysql_query($sql_prop); if (!$res_prop){echo $sql_prop;exit();}
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop;$data_['_sql']['time'][]=$mt;
                    
                        
                        $sql_prop_txt='';
                        for ($myrow_prop = mysql_fetch_array($res_prop); $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop))
                        {
                            if ($sql_prop_txt!=''){$sql_prop_txt.=',';}
                            $sql_prop_txt.="'".$myrow_prop[0]."'";
                        }
                        if ($sql_prop_txt==''){$sql_prop_txt="''";}
                        if ($WHERE!='') {$WHERE.=' AND ';}
                        $WHERE.=" s_cat.id IN ($sql_prop_txt)";
                        
                        
                                                       
                    }
                }
            
                if (count($__function_s_cat_prop__arr)==1){
                    if ($WHERE!='') {$WHERE.=' AND ';}
                    $WHERE.=" s_cat.id IN (SELECT s_cat_s_prop_val.id1 FROM s_cat_s_prop_val WHERE s_cat_s_prop_val.id2 IN ('".implode("','",$__function_s_cat_prop__arr)."'))";
                }
            }
        }
        if ($find_tip=='sql'){
            
            
            
             /*
            $FROM.=', (SELECT `s_cat_s_prop_val`.`id1` AS pi , `s_prop_val`.`val` AS pv  FROM `s_cat_s_prop_val` , `s_prop_val` WHERE `s_cat_s_prop_val`.`id2` = `s_prop_val`.`id`) AS p_';
            if ($WHERE!=''){$WHERE.=" AND ";}
            $WHERE.=" `s_cat`.`id` = p_.pi ";
            
            $col_m[$i]=" IF(COUNT(*)>0,GROUP_CONCAT(DISTINCT p_.pv SEPARATOR ', '),'') AS prop ";
            */
            
           /*
            $FROM.=', `s_cat_s_prop_val`, `s_prop_val`';
            if ($WHERE!=''){$WHERE.=" AND ";}
            $WHERE.=" `s_cat`.`id`=`s_cat_s_prop_val`.`id1` AND `s_cat_s_prop_val`.`id2`=`s_prop_val`.`id` ";
            
            $col_m[$i]=" IF(COUNT(*)>0,GROUP_CONCAT(`s_prop_val`.`val` SEPARATOR ', '),'') AS prop ";
            */
            
            $col_m[$i]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(CONCAT(`s_prop`.`name`,': ',`s_prop_val`.`val`,': ',`s_cat_s_prop_val`.`id`) SEPARATOR '||'),'') FROM `s_cat_s_prop_val`, `s_prop_val`,`s_prop` WHERE `s_cat_s_prop_val`.`id2`=`s_prop_val`.`id` AND `s_prop_val`.`s_prop_id`=`s_prop`.`id` AND `s_cat_s_prop_val`.`id1`=`"._DB($inc)."`.`id`) AS prop ";
           
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
        			FROM s_cat_s_prop_val 
        				WHERE id1 $nomer_
        ";
        $mt = microtime(true);
        $res_prop = mysql_query($sql_del_prop); if (!$res_prop){echo $sql_del_prop;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_del_prop;$data_['_sql']['time'][]=$mt;
        
        //ОЧИСТКА ПУСТЫХ СВОЙСТВ
        if ($_SESSION['a_options']['Удалять свойства не относящиеся ни к одному товару']=='1'){
            
            $sql_del_prop = "DELETE 
            			FROM s_prop_val 
            				WHERE id NOT IN (SELECT DISTINCT s_cat_s_prop_val.id2 FROM s_cat_s_prop_val)
            ";
            $mt = microtime(true);
            $res_prop = mysql_query($sql_del_prop);
            $data_['del_null_prop']=mysql_affected_rows();
            	if (!$res_prop){echo $sql_del_prop;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del_prop;$data_['_sql']['time'][]=$mt;
            
            //ОЧИСТКА ПУСТЫХ СВОЙСТВ
            $sql_del_prop = "DELETE 
            			FROM s_prop
            				WHERE id NOT IN (SELECT DISTINCT s_prop_val.s_prop_id FROM s_prop_val)
            ";
            $mt = microtime(true);
            $res_prop = mysql_query($sql_del_prop);
            $data_['del_null_prop2']=mysql_affected_rows();
            	if (!$res_prop){echo $sql_del_prop;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del_prop;$data_['_sql']['time'][]=$mt;
        }
        //
        
    }
    //****************************************************************************************************************************************
    //ИЗМЕНЕНИЕ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='change'){ 
        
        $data_['_d'][$data_['col'][$i]]=array();
        
        $sql_prop_change = "SELECT IF(s_prop.tip='Авто добавление',s_prop.id,''), IF(s_prop.tip='Авто добавление',s_prop_val.val,s_cat_s_prop_val.id2)
        				FROM s_cat_s_prop_val, s_prop_val, s_prop
        					WHERE s_cat_s_prop_val.id1='"._DB($nomer)."' 
                            AND s_cat_s_prop_val.id2=s_prop_val.id
                            AND s_prop.id=s_prop_val.s_prop_id
                            
        					
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
    elseif ($_t=='copy'){ 
        
        $data_['_d'][$data_['col'][$i]][$nomer]=array();
        
        $sql_prop_change = "SELECT s_prop.name, s_prop_val.val
        				FROM s_cat_s_prop_val, s_prop_val, s_prop
        					WHERE s_cat_s_prop_val.id1='"._DB($nomer)."' 
                            AND s_cat_s_prop_val.id2=s_prop_val.id
                            AND s_prop.id=s_prop_val.s_prop_id
                            
        					
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
        
        //Удаляем старые свойства данного товара
        $sql_del = "DELETE 
        			FROM s_cat_s_prop_val 
        				WHERE id1='"._DB($id)."'
        ";
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        
        //Обрабатываем массив свойств
        $val_arr=array();
        if (isJSON($col_val_arr[$key_col])==true){
            $val_arr=json_decode($col_val_arr[$key_col]);
        }
        //есть свойства в импортируемом файле
        if (count($val_arr)>0){
            //Перебор по свойствам
            foreach($val_arr as $prop_key=> $prop_arr){
                if (isset($prop_arr[0]) and isset($prop_arr[1]) and $prop_arr[0]!='' and $prop_arr[1]!=''){
                    $prop_name=$prop_arr[0];
                    $prop_val=$prop_arr[1];
                    
                    //получаем id свойства
                    $sql_prop="SELECT 
                                IF(COUNT(*)>0,s_prop.id,'')
                                    FROM s_prop
                                    WHERE s_prop.name='"._DB($prop_name)."'
                                    ";
                    $res_prop = mysql_query($sql_prop) or die(mysql_error().'<br>'.$sql_prop);
                    $myrow_prop = mysql_fetch_array($res_prop);
                    $s_prop_id=$myrow_prop[0];
                    if ($s_prop_id==''){
                        $sql_ins = "INSERT into s_prop (
                        				sid,
                                        chk_active,
                                        chk_fillter,
                                        chk_main,
                                        name,
                                        tip,
                                        data_tip
                        			) VALUES (
                        				'0',
                        				'0',
                                        '0',
                                        '0',
                                        '"._DB($prop_name)."',
                                        'Список',
                                        'Текст'
                        )";
                        
                        $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                        $s_prop_id = mysql_insert_id();
                        
                    }
                    
                    //Получаем id значения свойства
                    
                    $sql_prop="SELECT 
                                IF(COUNT(*)>0,s_prop_val.id,'')
                                    FROM s_prop_val
                                    WHERE s_prop_val.val='"._DB($prop_val)."'
                                    AND s_prop_val.s_prop_id='"._DB($s_prop_id)."'
                                    ";
                    $res_prop = mysql_query($sql_prop) or die(mysql_error().'<br>'.$sql_prop);
                    $myrow_prop = mysql_fetch_array($res_prop);
                    $s_prop_val_id=$myrow_prop[0];
                    if ($s_prop_val_id==''){
                        $sql_ins = "INSERT into s_prop_val (
                        				s_prop_id,
                                        val
                        			) VALUES (
                                        '"._DB($s_prop_id)."',
                                        '"._DB($prop_val)."'
                        )";
                        
                        $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                        $s_prop_val_id = mysql_insert_id();
                        
                    }
                    //Добавляем свойство к товару
                    $sql_ins = "INSERT into s_cat_s_prop_val (
                        				id1,
                                        id2
                        			) VALUES (
                                        '"._DB($id)."',
                                        '"._DB($s_prop_val_id)."'
                        )";
                        
                    $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    
                }
            }
        }
        //print_rf($val_arr);
    }
    //****************************************************************************************************************************************
    //СОХРАНЕНИЕ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='save'){
       
        if ($data_['chk_change'][$key]=='1'){ //если разрешен доступ на ищменение
        //получаем массив свойств
        if ($nomer==''){echo 'prop_save: $nomer=""<br />';exit();}
       
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
        
        
        $__function_prop_val_id=_GP('__function_s_cat_prop__select');
        
        
        $__function_prop_val_id_arr=array();
        if (!is_array($__function_prop_val_id)){
            if ($__function_prop_val_id!=''){
                $__function_prop_val_id_arr[0]=$__function_prop_val_id;
            }
        }
        else{//массив
            $__function_prop_val_id_arr=$__function_prop_val_id;
        }
        
        //УДАЛЯЕМ СТАРЫЕ СВОЙСТВА
        $sql_PROP = "DELETE 
        			FROM s_cat_s_prop_val 
        				WHERE id1 IN ('".implode("','",$nomer_arr)."')
        ";
        $mt = microtime(true);
        $res_ = mysql_query($sql_PROP); if (!$res_){echo $sql_PROP;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_PROP;$data_['_sql']['time'][]=$mt;
        
        //Перебор по массиву - формирование sql
        $SQL_INS_PROP="";
        foreach($__function_prop_val_id_arr as $key => $__function_prop_val_id){
            if ($nomer!='' and $__function_prop_val_id!=''){
                foreach ($nomer_arr as $kkey => $nomer_){
                    if ($SQL_INS_PROP!=''){$SQL_INS_PROP.=", ";}
                    $SQL_INS_PROP.="('"._DB($nomer_)."','"._DB($__function_prop_val_id)."')";
                }
            }
        }
        
        if ($SQL_INS_PROP!=''){
            $SQL_INS_PROP="INSERT into s_cat_s_prop_val (
            				id1,
            				id2
            			) VALUES ".$SQL_INS_PROP;
                        
            $mt = microtime(true);
            $res_ = mysql_query($SQL_INS_PROP); if (!$res_){echo $SQL_INS_PROP;exit();}
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$SQL_INS_PROP;$data_['_sql']['time'][]=$mt;
        
            
            
        }
        
        //ПЕРЕБОР по СВОЙСТВАМ С АВТОДОБАВЛЕНИЕМ
        $sql_prop = "SELECT s_prop.id 
        				FROM s_prop 
        					WHERE tip='Авто добавление'
                            
        "; 
        $mt = microtime(true);
        $res_prop = mysql_query($sql_prop); if (!$res_prop){echo $sql_prop;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop;$data_['_sql']['time'][]=$mt;
        
        for ($myrow_prop = mysql_fetch_array($res_prop); $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop))
        {
            $__function_prop_val=_GP('__function_s_cat_prop__input'.$myrow_prop[0]);
            if ($__function_prop_val!=''){
                $sql_prop_val = "SELECT IF(COUNT(*)>0,s_prop_val.id,'')
                				FROM s_prop_val 
                					WHERE s_prop_val.s_prop_id='"._DB($myrow_prop[0])."'
                                    AND s_prop_val.val='"._DB($__function_prop_val)."'
                	"; 
                $mt = microtime(true);
                $res_prop_val = mysql_query($sql_prop_val); if (!$res_prop_val){echo $sql_prop_val;exit();}
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_prop_val;$data_['_sql']['time'][]=$mt;
            
            
                $myrow_prop_val = mysql_fetch_array($res_prop_val);
                $s_prop_val_id=$myrow_prop_val[0];
                if ($s_prop_val_id==''){
                    $sql_ins = "INSERT into s_prop_val (
                    				s_prop_id,
                    				val
                    			) VALUES (
                    				'"._DB($myrow_prop[0])."',
                    				'"._DB($__function_prop_val)."'
                    )";
                    $mt = microtime(true);
                    $res_ = mysql_query($sql_ins); 
                        if (!$res_){echo $sql_ins;exit();}
                        else{$s_prop_val_id = mysql_insert_id(); }
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
                    
                }
                if ($s_prop_val_id!=''){
                    foreach ($nomer_arr as $kkey => $nomer_){
                        $sql_ins = "INSERT into s_cat_s_prop_val (
                        				id1,
                        				id2
                        			) VALUES (
                        				'"._DB($nomer_)."',
                        				'"._DB($s_prop_val_id)."'
                        )";
                        $mt = microtime(true);
                        $res_ = mysql_query($sql_ins); 
                            if (!$res_){echo $sql_ins;exit();}
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
                    }
                }
            }
            
        }
        
        }
    }
    

    
    
    //****************************************************************************************************************************************
    //ПАРСИНГ СВОЙСТВ
    //****************************************************************************************************************************************
    elseif ($_t=='parsing'){
        
        if ($parsing__work_=='in_db'){
            if ($cur_id==0){echo 'error prop: Не определен id каталога - $cur_id!';exit();}
            
            //Очищаем старые свойства
            $sql_prop_del = "DELETE 
            			FROM s_cat_s_prop_val 
            				WHERE id1='"._DB($cur_id)."'
            ";
            $data_['_sql'][]=$sql_prop_del;
            if (!mysql_query($sql_prop_del)){echo $sql_prop_del;exit();}
        }
       
       if (is_array($chk_)){
            foreach($chk_ as $s_prop_id => $chk_val){
                if ($chk_val=='1'){
                    
                    //получаем значение свойства
                    eval('$val='.$selector_[$s_prop_id].';'.$code_[$s_prop_id]);;
                    if (isset($val) and $val!=''){
                        $data_['_d'][$j][$data_['col'][$i]][$s_prop_id]=$val;
                    
                        if ($parsing__work_=='in_db'){
                            //проверяем, есть ли свойство в базе
                            $sql_prop = "SELECT IF(COUNT(*)>0,s_prop_val.id,'')
                            				FROM s_prop_val 
                            					WHERE s_prop_val.s_prop_id='"._DB($s_prop_id)."' 
                            					AND s_prop_val.val='"._DB($val)."'
                            	"; 
                            $res_prop = mysql_query($sql_prop) or die(mysql_error());
                            $myrow_prop = mysql_fetch_array($res_prop);
                            $s_prop_val_id=$myrow_prop[0];
                           
                            
                            //добавляем значение свойства
                            if ($s_prop_val_id==''){
                                $sql_prop_ins = "INSERT into s_prop_val (
                                				s_prop_id,
                                				val
                                			) VALUES (
                                				'"._DB($s_prop_id)."',
                                				'"._DB($val)."'
                                )";
                                $data_['_sql'][]=$sql_prop_ins;
                                if (!mysql_query($sql_prop_ins)){echo $sql_prop_ins;exit();}
                                else{$s_prop_val_id = mysql_insert_id(); }
                                
                            }
                            
                            //Добавляем свойство в таблицу связи
                            $sql_prop_ins = "INSERT into s_cat_s_prop_val (
                            				id1,
                            				id2
                            			) VALUES (
                            				'"._DB($cur_id)."',
                            				'"._DB($s_prop_val_id)."'
                            )";
                            $data_['_sql'][]=$sql_prop_ins;
                            if (!mysql_query($sql_prop_ins)){echo $sql_prop_ins;exit();}
                        
                       }
                        
                        
                    }
                }
            }
        
       }else{
            echo 'error prop: $chk_ != array();';
       }
    
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
        $col_m[$ii]="(SELECT GROUP_CONCAT(DISTINCT s_prop_val.val SEPARATOR ',') FROM s_prop_val, s_cat_s_prop_val WHERE s_prop_val.s_prop_id='"._DB($on)."' AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id) AS "._DB($data_['col'][$key_col])."_".$on;
    
        $sql_prop = "SELECT s_prop.name
        				FROM s_prop 
        					WHERE s_prop.id='"._DB($on)."'
        	"; 
        
        $mt = microtime(true);
        $res_prop = mysql_query($sql_prop);if (!$res_prop){echo $sql;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_prop;$data_['_sql']['time'][]=$mt;
        $myrow_prop = mysql_fetch_array($res_prop);
        $name_prop=$myrow_prop[0];
        
        
        if ($script_opt5=='1'){//название столбца
            if ($txt_menu!=''){$txt_menu.=$script_opt1;}
            $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$name_prop).$script_opt2;
        }
    }
}
?>