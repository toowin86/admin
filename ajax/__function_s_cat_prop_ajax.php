<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');

include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


    $_t=_GP('_t');
    // ************************************************************
    // Автозаполнение свойств
    if ($_t=='autocomplete_prop_select'){
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        $data_['items']=array();
        $prop_id=_GP('_prop_id');
        $term=_GP('_term');

        
        $sql_prop = "SELECT s_prop_val.id , s_prop_val.val 
        				FROM s_prop_val 
        					WHERE s_prop_val.s_prop_id='"._DB($prop_id)."'
                            AND s_prop_val.val LIKE '%"._DB($term)."%'
        						ORDER BY FIELD(s_prop_val.val,'"._DB($term)."') DESC, s_prop_val.val
                                LIMIT 100
        "; 
     
        $res_prop = mysql_query($sql_prop) or die(mysql_error());
        for ($myrow_prop = mysql_fetch_array($res_prop),$i=0; $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop),$i++)
        {
          
            $data_['items'][$i]['id']=$myrow_prop[0];
            $data_['items'][$i]['text']=$myrow_prop[1];
            //$data_[]=$myrow_prop[1];
        }

        
        echo json_encode($data_);
    }
    // ************************************************************
    // Автозаполнение свойств
    if ($_t=='autocomplete_prop'){
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        $data_=array();
        $prop_id=_GP('_prop_id');
        $term=_GP('term');
        
        $sql_prop = "SELECT s_prop_val.val 
        				FROM s_prop_val 
        					WHERE s_prop_val.s_prop_id='"._DB($prop_id)."'
                            AND s_prop_val.val LIKE '%"._DB($term)."%'
        						ORDER BY FIELD(s_prop_val.val,'"._DB($term)."'), s_prop_val.val
                                LIMIT 100
        "; 
        //echo $sql_prop;
        $res_prop = mysql_query($sql_prop) or die(mysql_error());
        for ($myrow_prop = mysql_fetch_array($res_prop); $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop))
        {
            $data_[]=$myrow_prop[0];
        }
        
        echo json_encode($data_);
    }
    // ************************************************************
    // Добавляем новое значение свойства
    if ($_t=='add_prop_val'){
        $data_=array();
        $nomer=_GP('nomer');
        $s_prop_val__val=_GP('s_prop_val__val',array());
        $s_prop_val__val_img=_GP('s_prop_val__val_img',array());
        
        
        $s_prop_val__val_arr=array();
        $s_prop_val__val_img_arr=array();
            if (!is_array($s_prop_val__val)){
                if ($s_prop_val__val!=''){
                    $s_prop_val__val_arr[0]=$s_prop_val__val;
                    $s_prop_val__val_img_arr[0]=$s_prop_val__val_img;
                }
            }else{
                $s_prop_val__val_arr=$s_prop_val__val;
                $s_prop_val__val_img_arr=$s_prop_val__val_img;
            }
        
            if (count($s_prop_val__val_arr)==0){echo 'Не указано ниодного значения свойства!';exit();}
            
            $data_['prop_val']=array();
            $data_['prop_val_status']=array();
        
        
            foreach($s_prop_val__val_arr as $key => $prop_val_val){
                    $sql = "SELECT IF(COUNT(*)>0,s_prop_val.id,'')
                    				FROM s_prop_val 
                    					WHERE s_prop_val.val='"._DB($prop_val_val)."'
                                        AND s_prop_val.s_prop_id='"._DB($nomer)."'
                    					
                    	"; 
                    $res = mysql_query($sql) or die(mysql_error());
                    $myrow = mysql_fetch_array($res);
                    $prop_val_id=$myrow[0];
                    $data_['prop_val_img'][$prop_val_id]='';
                    if ($prop_val_id==""){
                        $sql = "INSERT into s_prop_val (
                        				s_prop_id,
                        				val
                        			) VALUES (
                        				'"._DB($nomer)."',
                 		                '"._DB($prop_val_val)."'
                        )";
                        $data_['sql'][]=$sql;
                        if (!mysql_query($sql)){echo $sql;exit();}
                        else{
                            $prop_val_id = mysql_insert_id();
                            $data_['prop_val_status'][$prop_val_id]='ok';
                            $data_['prop_val'][$prop_val_id]=$prop_val_val; 
                        }
                        
                        if (isset($s_prop_val__val_img_arr[$key]) and $s_prop_val__val_img_arr[$key]!=''){
                            $img=$s_prop_val__val_img_arr[$key];
                            $ext=preg_replace("/.*?\./", '', $img);
                            $img_='../../i/s_prop_val/temp/'.$img;
                            
                            $file_name=ru_us($prop_val_val).'.'.$ext;
                            while(file_exists('../../i/s_prop_val/original/'.$file_name)){
                                //echo '+1';
                                $file_name=ru_us($prop_val_val).'_'.rand(100,999).'.'.$ext;
                            }
                            
                            if (file_exists($img_)){
                                //копируем файл
                                if (!copy($img_,'../../i/s_prop_val/original/'.$file_name)){echo 'Ошибка копирования файла: '.$img_;exit();}
                                else{
                                    //создаем миниатюру
                                    $size_arr= getimagesize($img_);
                                    $w_orig=$size_arr[0];
                                    $h_orig=$size_arr[1];
                                    smart_resize_image( $img_, '../../i/s_prop_val/small/'.$file_name, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                
                                    
                                    //удаляем временный файл
                                    if (!unlink($img_)){echo 'Ошибка удаления временного файла: "'.$img_.'"';exit();}
                                    
                                }
                            }
                            
                            $sql = "INSERT into a_photo (
                            				a_menu_id,
                            				row_id,
                                            img,
                                            comments,
                                            sid
                            			) VALUES (
                            				'24',
                            				'"._DB($prop_val_id)."',
                                            '"._DB($file_name)."',
                                            '"._DB($prop_val_val)."',
                                            '1'
                            )";
                            $data_['sql'][]=$sql;
                            if (!mysql_query($sql)){echo $sql;exit();}
                           
                            $data_['prop_val_img'][$prop_val_id]=$file_name; 
                        }
                        
                    }else{
                        $data_['prop_val_status'][$prop_val_id]='indb';
                        $data_['prop_val'][$prop_val_id]=$prop_val_val;
                    }
                }
            
        echo json_encode($data_);
    }
    // ************************************************************
    // Удаляем свойство и значение
    if ($_t=='del_prop'){
        $data_=array();
        $nomer=_GP('nomer');
        //Удаляем связи с каталогом
        $sql = "DELETE 
        			FROM s_cat_s_prop_val 
        				WHERE s_cat_s_prop_val.id2 IN (SELECT s_prop_val.id FROM s_prop_val WHERE s_prop_val.s_prop_id='"._DB($nomer)."')
        ";
        $data_['sql'][]=$sql;
        if (!mysql_query($sql)){echo $sql;exit();}
        
        $sql="SELECT a_photo.img
                    FROM a_photo 
        				WHERE a_photo.row_id IN (SELECT s_prop_val.id FROM s_prop_val WHERE s_prop_val.s_prop_id='"._DB($nomer)."')
                        AND a_photo.a_menu_id='24'
                ";
        $res = mysql_query($sql) or die(mysql_error());
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $img=$myrow[0];
            $img_='../../i/s_prop_val/original/'.$img;
            if (file_exists($img_) and $img!=''){
                if (!unlink($img_)){echo 'Ошибка удаления оригинального файла изображения: "'.$img_.'"';exit();}
            }
            $img_='../../i/s_prop_val/small/'.$img;
            if (file_exists($img_) and $img!=''){
                if (!unlink($img_)){echo 'Ошибка удаления уменьшенного файла изображения: "'.$img_.'"';exit();}
            }
            
        }
        
        //Удаляем связи с фото
        $sql = "DELETE 
        			FROM a_photo 
        				WHERE a_photo.row_id IN (SELECT s_prop_val.id FROM s_prop_val WHERE s_prop_val.s_prop_id='"._DB($nomer)."')
                        AND a_photo.a_menu_id='24'
        ";
        $data_['sql'][]=$sql;
        if (!mysql_query($sql)){echo $sql;exit();}
        //Удаляем значения
        $sql = "DELETE 
        			FROM s_prop_val 
        				WHERE s_prop_id='"._DB($nomer)."'
        ";
        $data_['sql'][]=$sql;
        if (!mysql_query($sql)){echo $sql;exit();}
        
        //Удаляем свойство
        $sql = "DELETE 
        			FROM s_prop
        				WHERE id='"._DB($nomer)."'
        ";
        $data_['sql'][]=$sql;
        if (!mysql_query($sql)){echo $sql;exit();}
        
        
        echo json_encode($data_);
    }
    
    // ************************************************************
    // Добавляем новое свойство и значение
    if ($_t=='prop_find'){
        $data_['items']=array();
        $prop_id=_GP('prop_id');
        $term=_GP('term');
        
        $sql = "SELECT s_prop_val.id, s_prop_val.val
        				FROM s_prop_val
        					WHERE s_prop_val.s_prop_id='"._DB($prop_id)."'
                            AND s_prop_val.val LIKE '%"._DB($term)."%'
        						ORDER BY s_prop_val.val
        "; 
        $res = mysql_query($sql) or die(mysql_error());
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['items'][$i]['id']=$myrow[0];
            $data_['items'][$i]['text']=$myrow[1];
        }
        
        
        echo json_encode($data_);
    }
    
    
    // ************************************************************
    // Добавляем новое свойство и значение
    if ($_t=='s_prop_add'){
        $data_=array();
        $s_prop_name=_GP('s_prop_name');
        $s_prop_tip=_GP('s_prop_tip');
            $data_tip='Текст';
            if ($s_prop_tip=='1'){$s_prop_tip_txt='Список';}
            if ($s_prop_tip=='2'){$s_prop_tip_txt='Авто добавление';}
            if ($s_prop_tip=='3'){$s_prop_tip_txt='Авто добавление';$data_tip='Число';}
        $s_prop_val__val=_GP('s_prop_val__val',array()); //значения
        $s_prop_val__val_img=_GP('s_prop_val__val_img',array());
        
            $s_prop_val__val_arr=array();
            $s_prop_val__val_img_arr=array();
            if (!is_array($s_prop_val__val)){
                if ($s_prop_val__val!=''){
                    $s_prop_val__val_arr[0]=$s_prop_val__val;
                    $s_prop_val__val_img_arr[0]=$s_prop_val__val_img;
                }
            }else{
                $s_prop_val__val_arr=$s_prop_val__val;
                $s_prop_val__val_img_arr=$s_prop_val__val_img;
            }
        
            if ($s_prop_name==''){echo 'Не указано название свойства!';exit();}
            if (count($s_prop_val__val_arr)==0){echo 'Не указано ниодного значения свойства!';exit();}
        
            $sql = "SELECT IF(COUNT(*)>0,s_prop.id,''), MAX(s_prop.sid)
            				FROM s_prop 
            					WHERE s_prop.name='"._DB($s_prop_name)."' 
            					
            	"; 
            $res = mysql_query($sql) or die(mysql_error());
            $myrow = mysql_fetch_array($res);
            $s_prop_id=$myrow[0];
            $s_prop_news_id=$myrow[1]+1;
            
            if ($s_prop_id==''){//Добавляем
                $sql = "INSERT into s_prop (
                				sid,
                                chk_active,
                				name,
                                tip,
                                data_tip
                			) VALUES (
                				
                				'"._DB($s_prop_news_id)."',
                                '1',
                                '"._DB($s_prop_name)."',
                                '"._DB($s_prop_tip_txt)."',
                                '"._DB($data_tip)."'
                )";
                $data_['sql'][]=$sql;
                if (!mysql_query($sql)){echo $sql;exit();}
                else{$s_prop_id = mysql_insert_id(); }
                
            }else{
                echo 'Свойство с таким названием уже существует! <a target="_blank" href="?inc=s_prop&nomer='.$s_prop_id.'">id = '.$s_prop_id.'</a>';
                exit();
            }
            $data_['prop_id']=$s_prop_id;
            
            //Добавляем значения
            $data_['prop_val']=array();
            $data_['prop_val_status']=array();
        
        
            if ($data_['prop_id']>0){
                foreach($s_prop_val__val_arr as $key => $prop_val_val){
                    $sql = "SELECT IF(COUNT(*)>0,s_prop_val.id,'')
                    				FROM s_prop_val 
                    					WHERE s_prop_val.val='"._DB($prop_val_val)."'
                                        AND s_prop_val.s_prop_id='"._DB($data_['prop_id'])."'
                    					
                    	"; 
                    $res = mysql_query($sql) or die(mysql_error());
                    $myrow = mysql_fetch_array($res);
                    $prop_val_id=$myrow[0];
                    if ($prop_val_id==""){
                        $sql = "INSERT into s_prop_val (
                        				s_prop_id,
                        				val
                        			) VALUES (
                        				'"._DB($data_['prop_id'])."',
                 		                '"._DB($prop_val_val)."'
                        )";
                        $data_['sql'][]=$sql;
                        if (!mysql_query($sql)){echo $sql;exit();}
                        else{$prop_val_id = mysql_insert_id();$data_['prop_val_status'][$prop_val_id]='ok';$data_['prop_val'][$prop_val_id]=$prop_val_val; }
                        $data_['prop_val_img'][$prop_val_id]='';
                        //добавляем фото к свойству
                        
                        if (isset($s_prop_val__val_img_arr[$key]) and $s_prop_val__val_img_arr[$key]!=''){
                            $img=$s_prop_val__val_img_arr[$key];
                            $ext=preg_replace("/.*?\./", '', $img);
                            $img_='../../i/s_prop_val/temp/'.$img;
                            
                            $file_name=ru_us($prop_val_val).'.'.$ext;
                            while(file_exists('../../i/s_prop_val/original/'.$file_name)){
                                //echo '+1';
                                $file_name=ru_us($prop_val_val).'_'.rand(100,999).'.'.$ext;
                            }
                            
                            if (file_exists($img_)){
                                //копируем файл
                                if (!copy($img_,'../../i/s_prop_val/original/'.$file_name)){echo 'Ошибка копирования файла: '.$img_;exit();}
                                else{
                                    //создаем миниатюру
                                    $size_arr= getimagesize($img_);
                                    $w_orig=$size_arr[0];
                                    $h_orig=$size_arr[1];
                                    smart_resize_image( $img_, '../../i/s_prop_val/small/'.$file_name, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                
                                    
                                    //удаляем временный файл
                                    if (!unlink($img_)){echo 'Ошибка удаления временного файла: "'.$img_.'"';exit();}
                                    
                                }
                            }
                            
                            $sql = "INSERT into a_photo (
                            				a_menu_id,
                            				row_id,
                                            img,
                                            comments,
                                            sid
                            			) VALUES (
                            				'24',
                            				'"._DB($prop_val_id)."',
                                            '"._DB($file_name)."',
                                            '"._DB($prop_val_val)."',
                                            '1'
                            )";
                            $data_['sql'][]=$sql;
                           if (!mysql_query($sql)){echo $sql;exit();}
                           $data_['prop_val_img'][$prop_val_id]=$file_name; 
                        }
                        
                        
                    }else{
                        $data_['prop_val_status'][$prop_val_id]='indb';
                        $data_['prop_val'][$prop_val_id]=$prop_val_val;
                    }
                }
            }
            
        
        echo json_encode($data_);
    }
    
    // ************************************************************
    // Добавляем новое значение свойства
    if ($_t=='prop_view_from_struktura'){
        $data_=array();
        $data_['id']=array();
        $nomer=_GP('nomer',array());
        if (is_array($nomer) and count($nomer)>0){
        
            $sql = "SELECT DISTINCT s_prop.id
                				FROM s_prop, s_prop_val, s_cat_s_prop_val, s_cat_s_struktura
                					WHERE s_prop.id=s_prop_val.s_prop_id
                                    AND s_prop_val.id=s_cat_s_prop_val.id2
                                    AND s_cat_s_prop_val.id1=s_cat_s_struktura.id1
                                    AND s_cat_s_struktura.id2 IN ('".implode("','",$nomer)."')
                                    
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['id'][]=$myrow[0];
            }
        }
        
        echo json_encode($data_);
    }
    // ************************************************************
    // Удаляем свойство  у товара
    if ($_t=='s_cat_s_prop_val_id_remove'){
        $data_=array();
        $s_cat_s_prop_val_id=_GP('s_cat_s_prop_val_id');
        $sql_del = "DELETE 
        			FROM s_cat_s_prop_val
        				WHERE id='"._DB($s_cat_s_prop_val_id)."'
        ";
        $mt = microtime(true);
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
       
       
        echo json_encode($data_);
    }
    
    // ************************************************************
    // открываем свойство  у товара
    if ($_t=='s_cat_s_prop_val_id_change'){
        $data_=array();
        $data_['p']=array();
        $s_cat_s_prop_val_id=_GP('s_cat_s_prop_val_id');
        $sql="SELECT  s_prop_val.id
                        			FROM s_cat_s_prop_val, s_prop_val
                        				WHERE s_cat_s_prop_val.id='"._DB($s_cat_s_prop_val_id)."'
                                        AND s_prop_val.id=s_cat_s_prop_val.id2
                        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $row = mysql_fetch_array($res);
        $data_['i']=$row[0];
        
        $sql="SELECT s_prop_val.id, s_prop_val.val
                    FROM s_prop_val
                        WHERE s_prop_val.s_prop_id IN 
                                
                                (SELECT  pv1.s_prop_id
                        			FROM s_cat_s_prop_val, s_prop_val AS pv1
                        				WHERE s_cat_s_prop_val.id='"._DB($s_cat_s_prop_val_id)."'
                                        AND pv1.id=s_cat_s_prop_val.id2)
                ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        while ($row = mysql_fetch_array($res)) {
            $data_['p'][$row[0]]=$row[1];
        }
        echo json_encode($data_);
    }  
    // ************************************************************
    // Изменяем свойство у товара
    if ($_t=='s_prop_quick_change_save'){
        $data_=array();
        $data_['p']=array();
        $s_prop_val_id=_GP('s_prop_val_id');
        $s_cat_id=_GP('s_cat_id');
        
        $s_cat_s_prop_val_id=_GP('s_cat_s_prop_val_id');
        
        $sql_del = "DELETE 
        			FROM s_cat_s_prop_val
        				WHERE id='"._DB($s_cat_s_prop_val_id)."'
        ";
        $mt = microtime(true);
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
       
        $sql_ins = "INSERT into s_cat_s_prop_val (
        				id1,
        				id2
        			) VALUES (
        				'"._DB($s_cat_id)."',
        				'"._DB($s_prop_val_id)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
        $data_['id'] = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
        
        
        echo json_encode($data_);
    } 
     
// Загрузка фото к свойству
if ($_t=='upload_s_prop'){

    $inc=_GP('_inc','s_prop_val');
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
}
?>