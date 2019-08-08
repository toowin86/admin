<?php

//удаление столбцов
function del_col($a_col_id){
   
    //получаем имя столбца
    $sql = "SELECT  a_col.col, 
                    (SELECT a_menu.inc FROM a_menu WHERE a_menu.id=a_col.a_menu_id) AS inc,
                    (SELECT a_menu.id FROM a_menu WHERE a_menu.id=a_col.a_menu_id) AS a_menu_id,
                    a_col.tip
                    
    				FROM a_col
    					WHERE a_col.id='"._DB($a_col_id)."'
    	"; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $myrow = mysql_fetch_array($res);
    
    $col_name=$myrow[0];
    $inc=$myrow[1];
    $a_menu_id=$myrow[2];
    $tip=$myrow[3];
    
    if ($tip=='Связанная таблица max-max'){
        $sql = "SELECT a_menu.inc
        				FROM a_connect, a_menu, a_col
        					WHERE a_connect.a_col_id1='"._DB($a_col_id)."'
                            AND a_col.id=a_connect.a_col_id2
                            AND a_menu.id=a_col.a_menu_id 
                            
        	"; 
        $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
        $myrow = mysql_fetch_array($res);
        $inc_connect=$myrow[0];
        
        //удаляем таблицу связи
        if ($inc_connect!=''){
            $sql = "DROP TABLE `".$inc."_".$inc_connect."`";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
        }
       
        //удаляем из a_connect
        $sql = "DELETE 
        			FROM a_connect 
        					WHERE a_connect.a_col_id1='"._DB($a_col_id)."'
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
    }
    //удаляем фото
   
    if ($tip=='Фото' ){
        $sql_img = "SELECT img
        				FROM a_photo 
        					WHERE a_menu_id='"._DB($a_menu_id)."' 
        						
        "; 
      
        $res_img = mysql_query($sql_img) or die(mysql_error());
        for ($myrow_img = mysql_fetch_array($res_img); $myrow_img==true; $myrow_img = mysql_fetch_array($res_img))
        {
            //print_r($myrow_img);
            $img_='../../i/'.$inc.'/original/'.$myrow_img[0];
            if (isset($img_) and $myrow_img[0]!=''){
               unset($img_); 
            }
            $img_='../../i/'.$inc.'/small/'.$myrow_img[0];
            if (isset($img_) and $myrow_img[0]!=''){
               unset($img_); 
            }
        }
        $sql_img = "DELETE 
        			FROM a_photo 
        				WHERE a_menu_id='"._DB($a_menu_id)."'
        ";
      
        mysql_query($sql_img) or die($sql_img.'<br />'.mysql_error());
        
    }
    if ($tip=='Связанная таблица 1-max' ){
        //удаляем связь
        $sql = "DELETE 
        			FROM a_connect 
        					WHERE a_connect.a_col_id1='"._DB($a_col_id)."'
        ";
        mysql_query($sql) or die(mysql_error());
    }
  
    if ($tip!='Связанная таблица max-max' and $tip!='Функция' and $tip!='Фото'){
        //удаляем столбец
        $sql = "ALTER TABLE `"._DB($inc)."` DROP `"._DB($col_name)."`;";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
    }
    
        
    //Удаляем доступ
    $sql = "DELETE 
    			FROM a_admin_a_col 
    				WHERE id2='"._DB($a_col_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
   
    
    //удаляем из таблицы a_col
    $sql = "DELETE 
    			FROM a_col 
    				WHERE a_col.id='"._DB($a_col_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());

    
    return true;
}

header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


$_t=_GP('_t');

// ************************************************************
// Загрузка фото
if ($_t=='upload'){
    $a_admin_id=_GP('a_admin_id');
    $fileName='';
    $targetDir = '../../i/a_admin/temp';
    
    if ($a_admin_id!=''){
        $targetDir = '../../i/a_admin/original';
        //имя файла
        $sql = "SELECT a_photo.id, a_photo.img
        				FROM a_photo, a_menu
        					WHERE a_photo.a_menu_id=a_menu.id
                            AND a_menu.inc='a_admin'
                            AND a_photo.tip='Основное'
                            AND a_photo.row_id='"._DB($a_admin_id)."'
                            
        	"; 
        $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
        $myrow = mysql_fetch_array($res);
        $fileName=$myrow[1];
        
        //если есть такое изображение  - удаляем его 
        $img_='../../i/a_admin/original/'.$fileName;
        if (file_exists($img_) and $fileName!=''){
            @unlink($img_);
        }
        
        //удаляем из базы
        $sql = "DELETE 
        			FROM a_photo 
        				WHERE id='"._DB($myrow[0])."'
        ";
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        
    }
    //echo $a_admin_id.'+';exit();
    // проверяем на пустоту
    if ($fileName==''){
        
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        
        if (file_exists($targetDir.'/'.$fileName)){
            $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
        }
        
    }
  
    
    @set_time_limit(5 * 60);
    if (!file_exists('../../i')) {@mkdir('../../i',0777);}
    if (!file_exists('../../i/a_admin')) {@mkdir('../../i/a_admin',0777);}
    
    
    if (!file_exists($targetDir)) {@mkdir($targetDir,0777);}
    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 50 * 3600; // Temp file age in seconds
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
        if ($a_admin_id!=''){
            $sql = "INSERT into a_photo (
            				a_menu_id,
            				tip,
                            row_id,
                            img,
                            sid
            			) VALUES (
            				'4',
            				'Основное',
                            '"._DB($a_admin_id)."',
                            '"._DB($fileName)."',
                            '1'
            )";
            $res = mysql_query($sql);
            	if (!$res){echo $sql;exit();}
        }
        
        echo $fileName;
    }
    

}

// ************************************************************
// Сохранение информации о меню
if ($_t=='a_menu_info_save'){
    $a_menu_id=_GP('a_menu_id');
    $html=_GP('html');
    if ($a_menu_id>0){
        $sql = "
        		UPDATE a_menu 
        			SET  
        				comments='"._DB($html)."'
        		
        		WHERE id='"._DB($a_menu_id)."'
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        echo 'ok';
    }
}

// ************************************************************
// Информация о меню
if ($_t=='a_menu_info'){
    $a_menu_id=_GP('a_menu_id');
    if ($a_menu_id>0){
        $sql = "SELECT comments
        				FROM a_menu 
        					WHERE id='"._DB($a_menu_id)."' 
        					ORDER BY id DESC
        	"; 
        $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
        $myrow = mysql_fetch_array($res);
        echo $myrow['comments'];
    }
}
// ************************************************************
// Перераспределение прав доступа
if ($_t=='a_menu_a_com_block'){
    $a_com_id=_GP('a_com_id');
    $a_menu_id=_GP('a_menu_id');
    $a_admin_id=_GP('a_admin_id');
    $tip=_GP('tip');
    
    if ($tip=='add'){
        $sql = "INSERT into a_admin_a_menu_a_com (
        				id1,
        				id2
        			) (SELECT '"._DB($a_admin_id)."', a_menu_a_com.id
                            FROM a_menu_a_com
                                WHERE a_menu_a_com.id1='"._DB($a_menu_id)."'
                                AND a_menu_a_com.id2='"._DB($a_com_id)."'
        )";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        $new_id = mysql_insert_id(); 
        
    }
    elseif($tip=='del'){
        $sql = "DELETE 
        			FROM a_admin_a_menu_a_com 
        				WHERE  a_admin_a_menu_a_com.id1='"._DB($a_admin_id)."'
                        AND    a_admin_a_menu_a_com.id2 IN (SELECT a_menu_a_com.id FROM a_menu_a_com WHERE a_menu_a_com.id1='"._DB($a_menu_id)."' AND a_menu_a_com.id2='"._DB($a_com_id)."')
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
    }
    else{
        echo 'Не верный тип!';
        exit();
    }
    echo 'ok';
}
// ************************************************************
// Перераспределение прав доступа
if ($_t=='a_menu_a_com_save'){
    $a_com_id=_GP('a_com_id');
    $a_menu_id=_GP('a_menu_id');
    $tip=_GP('tip');
    
    if ($tip=='add'){
        $sql = "INSERT into a_menu_a_com (
        				id1,
        				id2
        			) VALUES (
        				'"._DB($a_menu_id)."',
        				'"._DB($a_com_id)."'
        )";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        
    }
    elseif($tip=='del'){
        
        //Удааляем связи доступа
        $sql = "DELETE 
        			FROM a_admin_a_menu_a_com 
        				WHERE  a_admin_a_menu_a_com.id2 IN (SELECT a_menu_a_com.id FROM a_menu_a_com WHERE a_menu_a_com.id1='"._DB($a_menu_id)."' AND a_menu_a_com.id2='"._DB($a_com_id)."')
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        
        // Удаляем связи
        $sql = "DELETE 
        			FROM a_menu_a_com 
        				WHERE id1='"._DB($a_menu_id)."'
                        AND id2='"._DB($a_com_id)."'
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
    }
    else{
        echo 'Не определен тип!';exit();
    }
    
    echo 'ok';
}
// ************************************************************
// Сохранение новой функции
if ($_t=='a_com_del'){
    $a_com_id=_GP('a_com_id');
    
    //Удаление доступа к функциям 
    $sql = "DELETE 
    			FROM a_admin_a_menu_a_com 
    				WHERE a_admin_a_menu_a_com.id2 IN (SELECT a_menu_a_com.id FROM a_menu_a_com WHERE a_menu_a_com.id2='"._DB($a_com_id)."')
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    //Удаление связанных функций
    $sql = "DELETE 
    			FROM a_menu_a_com 
    				WHERE a_menu_a_com.id2='"._DB($a_com_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    //Удаление функции
    $sql = "DELETE 
    			FROM a_com 
    				WHERE a_com.id='"._DB($a_com_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    echo 'ok';

}
// ************************************************************
// Сохранение новой функции
if ($_t=='add_new_com_save'){
    $name=_GP('name');
    $com=_GP('com');
    $tip=_GP('tip');
    $chk_active=_GP('chk_active','0');
    $data=array();
    $sql = "INSERT into a_com (
    				chk_active,
    				name,
                    com,
                    tip
    			) VALUES (
    				'"._DB($chk_active)."',
    				'"._DB($name)."',
                    '"._DB($com)."',
                    '"._DB($tip)."'
                    
    )";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $data['id']=mysql_insert_id();  
    
    echo json_encode($data);

}
// ************************************************************
// Изменение доступа к пункту меню
if ($_t=='a_admin_a_com_change'){
    $a_col_id=_GP('a_col_id');
    $a_admin_id=_GP('a_admin_id');
    $tip=_GP('tip');
    
    if ($tip=='add'){
        $sql = "INSERT into a_admin_a_col (
        				id1,
        				id2
        			) VALUES (
        				'"._DB($a_admin_id)."',
        				'"._DB($a_col_id)."'
        )";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        
    }
    elseif($tip=='del'){
        $sql = "DELETE 
        			FROM a_admin_a_col 
        				WHERE id1='"._DB($a_admin_id)."'
                        AND id2='"._DB($a_col_id)."'
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
    }
    else{
        echo 'Не определен тип!';exit();
    }
    
    echo 'ok';
}

// ************************************************************
// Изменение филиала
if ($_t=='a_admin_i_tp_change'){
    $i_tp_id=_GP('i_tp_id');
    $a_admin_id=_GP('a_admin_id');

    $sql = "
    		UPDATE a_admin 
    			SET  
    				i_tp_id='"._DB($i_tp_id)."'
    		
    		WHERE id='"._DB($a_admin_id)."'
    ";
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    
    echo 'ok';
}
// ************************************************************
// Изменение должностей
if ($_t=='a_admin_i_post_change'){
    $i_post_id=_GP('i_post_id');
    $a_admin_id=_GP('a_admin_id');
    
    $sql = "DELETE 
    			FROM a_admin_i_post 
    				WHERE a_admin_i_post.id1='"._DB($a_admin_id)."'
    ";
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
    
    $i_post_id_arr=array();
    if ($i_post_id!=''){
        if (is_array($i_post_id)){
            $i_post_id_arr=$i_post_id;
        }else{
            $i_post_id_arr[0]=$i_post_id;
        }
    }
    
    foreach($i_post_id_arr as $key =>$val){
        $sql = "INSERT into a_admin_i_post (
        				id1,
        				id2
        			) VALUES (
        				'"._DB($a_admin_id)."',
        				'"._DB($val)."'
        )";
        
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        
    }
    
    echo 'ok';
}
// ************************************************************
// Удаляем пользователя
if ($_t=='a_admin_del'){
    $a_admin_id=_GP('a_admin_id');
    $err='';
    
    //ПРОВЕРКА НА НАЛИЧИЕ ПРОДАЖИ
    $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_zakaz.id SEPARATOR ', '),'')
    				FROM m_zakaz 
    					WHERE m_zakaz.a_admin_id='"._DB($a_admin_id)."'
                        OR m_zakaz.a_admin_otvet_id='"._DB($a_admin_id)."'
    					
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]!=''){
        $err.='<p>Проверьте заказы: '._DB($myrow[0]).'</p>';
    }
    
    //выставлен работником
     $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_zakaz_s_cat.m_zakaz_id SEPARATOR ', '),'')
    				FROM m_zakaz_s_cat, m_zakaz_s_cat_a_admin_i_post
    					WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id
                        AND m_zakaz_s_cat_a_admin_i_post.id2 IN (SELECT a_admin_i_post.id FROM a_admin_i_post WHERE a_admin_i_post.id1='"._DB($a_admin_id)."')
    					
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]!=''){
        $err.='<p>Выставлен работником в заказах: '._DB($myrow[0]).'</p>';
    }
    
    //поступлений
    $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_postav.id SEPARATOR ', '),'')
    				FROM m_postav 
    					WHERE m_postav.a_admin_id='"._DB($a_admin_id)."'
    					
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]!=''){
        $err.='<p>Проверьте поступления: '._DB($myrow[0]).'</p>';
    }
    
    //платежей
    $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_platezi.id SEPARATOR ', '),'')
    				FROM m_platezi 
    					WHERE m_platezi.a_admin_id='"._DB($a_admin_id)."'
    					
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]!=''){
        $err.='<p>Проверьте платежи: '._DB($myrow[0]).'</p>';
    }
    
    //выдача з/п
    $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_platezi.id SEPARATOR ', '),'')
    				FROM m_platezi 
    					WHERE m_platezi.id_z_p_p='"._DB($a_admin_id)."'
                        AND m_platezi.a_menu_id='4'
    					
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]!=''){
        $err.='<p>Выдача з/п работнику - проверьте платежи: '._DB($myrow[0]).'</p>';
    }
    //приемка
    $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(r_service.m_zakaz_id SEPARATOR ', '),'')
    				FROM r_service 
    					WHERE r_service.a_admin_id='"._DB($a_admin_id)."'
    					
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]!=''){
        $err.='<p>Проверьте акты приема оборудования в ремонт: '._DB($myrow[0]).'</p>';
    }
    
    
    
    
   if ($err==''){

    //Удаляем связи
    $sql = "DELETE 
    			FROM a_admin_a_menu_a_com 
    				WHERE id1='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    //Удаляем связи
    $sql = "DELETE 
    			FROM a_admin_a_col 
    				WHERE id1='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    //Удаляем связи
    $sql = "DELETE 
    			FROM a_admin_a_menu 
    				WHERE id1='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    //Удаляем должности
    $sql = "DELETE 
    			FROM a_admin_i_post_i_zp
    				WHERE a_admin_i_post_i_zp.id1 IN (SELECT `a_admin_i_post`.`id` FROM `a_admin_i_post` WHERE `a_admin_i_post`.`id1`='"._DB($a_admin_id)."')
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    //Удаляем должности
    $sql = "DELETE 
    			FROM a_admin_i_post
    				WHERE id1='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    
    
     //***************************
    
    $sql = "SELECT IF(COUNT(*)>0,a_photo.img,'')
    				FROM a_photo, a_menu
    				WHERE a_photo.a_menu_id=a_menu.id
                    AND a_menu.inc='a_admin'
                    AND a_photo.row_id='"._DB($a_admin_id)."'
                    AND a_photo.tip='Основное'
    	"; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $myrow = mysql_fetch_array($res);
    $img='../../i/a_admin/original/'.$myrow[0];
    if ($myrow[0]!=''){
        if (file_exists($img)){
            if (!@unlink($img)){
                echo 'Ошибка удаления файла '.$img;
            }
        }
        $sql = "DELETE 
        			FROM a_photo
        				WHERE a_photo.a_menu_id IN (SELECT a_menu.id FROM a_menu WHERE a_menu.inc='a_admin')
                       
                        AND a_photo.row_id='"._DB($a_admin_id)."'
                        AND a_photo.tip='Основное'
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
      
    }
    

    $sql_del = "DELETE 
    			FROM a_admin_i_post 
    				WHERE id1='"._DB($a_admin_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql_del) or die(mysql_error().'<br />'.$sql_del);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
    
    
    //удаляем диалоги
    $sql_del = "DELETE 
    			FROM m_dialog 
    				WHERE m_a_admin_i_contr_id1 IN (SELECT m_a_admin_i_contr.id FROM m_a_admin_i_contr WHERE id1='"._DB($a_admin_id)."')
                    OR m_a_admin_i_contr_id2 IN (SELECT m_a_admin_i_contr.id FROM m_a_admin_i_contr WHERE id1='"._DB($a_admin_id)."')
    
    ";
    $mt = microtime(true);
    $res = mysql_query($sql_del) or die(mysql_error().'<br />'.$sql_del);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
    
    
    //удаляем сообщения
    $sql_del = "DELETE 
    			FROM m_a_admin_i_contr 
    				WHERE id1='"._DB($a_admin_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql_del) or die(mysql_error().'<br />'.$sql_del);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_del;$data_['_sql']['time'][]=$mt;
    
    
    //удаляем админа
    $sql = "DELETE 
    			FROM a_admin
    				WHERE id='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    echo 'ok';
    }
    else{
        echo $err;
    }
    
    
}
// ************************************************************
// Сохраняем нового пользователя
if ($_t=='add_new_admin_save'){
    $chk_active=_GP('chk_active','0');
    $name=_GP('name');
    $email=_GP('email');
    $phone=_GP('phone');if ($phone!=''){$phone=conv_('phone_to_db',$phone);}
    $img=_GP('img');$new_img='';
    $comments=_GP('comments');
    $i_tp_admin=_GP('i_tp_admin');
    $i_post_admin=_GP('i_post_admin');
       if ($i_post_admin!=''){ 
        if (!is_array($i_post_admin)){
            $n=$i_post_admin;unset($i_post_admin);$i_post_admin[0]=$n;
            }
       }else{
        unset($i_post_admin);$i_post_admin=array();
       }
    
    $sql = "INSERT into a_admin (
    				chk_active,
    				name,
                    phone,
                    email,
                    password,
                    comments,
                    data_change,
                    i_tp_id
                    
    			) VALUES (
    				'"._DB($chk_active)."',
    				'"._DB($name)."',
                    '"._DB($phone)."',
                    '"._DB($email)."',
                    '"._DB(rand(1000,9999))."',
                    '"._DB($comments)."',
                    '".date('Y-m-d H:i:s')."',
                    '"._DB($i_tp_admin)."'
    )";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $a_admin_id = mysql_insert_id(); 
    
    foreach($i_post_admin as $key => $val){
        $sql = "INSERT into a_admin_i_post (
        				id1,
        				id2
        			) VALUES (
        				'"._DB($a_admin_id)."',
        				'"._DB($val)."'
        )";
        
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
    }
    $file_name='';
    if ($img!=''){
        $ext=preg_replace("/.*?\./", '', $img);
        $file_name=ru_us($name).'.'.$ext;
        $new_img='../../i/a_admin/original/'.$file_name;
        
        $i=0;
        while(file_exists($new_img)){
            $file_name=ru_us($name).'_'.$i.'.'.$ext;
            $new_img='../../i/a_admin/original/'.$file_name;
            $i++;
        }
        if (@copy('../../i/a_admin/temp/'.$img,$new_img)){
            if (!@unlink('../../i/a_admin/temp/'.$img)){echo 'Ошибка удаления файла: ../../i/a_admin/temp/'.$img; exit();}
            $sql = "SELECT a_menu.id
            				FROM a_menu 
            					WHERE a_menu.inc='a_admin'
            	"; 
            $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
            $myrow = mysql_fetch_array($res);
            $a_menu_id=$myrow[0];
            
            $sql = "INSERT into a_photo (
            				a_menu_id,
            				row_id,
                            sid,
                            tip,
                            img,
                            comments
            			) VALUES (
                            '"._DB($a_menu_id)."',
            				'"._DB($a_admin_id)."',
            				'1',
                            'Основное',
                            '"._DB($file_name)."',
                            '"._DB($name)."'
            )";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
        }else{
            echo 'Ошибка копирования файла: ../../i/a_admin/temp/'.$img;exit();
        }
    }
    $data['id']=$a_admin_id;
    $data['img']=$file_name;
    echo json_encode($data);
    
}
// ************************************************************
// Сохраняем информацию о пользователе
if ($_t=='a_admin_save_info'){
    $a_admin_id=_GP('a_admin_id');
    $col=_GP('col');
    $val=_GP('val');
    
    if ($col=='phone'){$val=conv_('phone_to_db',$val);}
    $sql = "
    		UPDATE a_admin 
    			SET  
    				"._DB($col)."='"._DB($val)."',
                    data_change='".date('Y-m-d H:i:s')."'
                    
    		
    		WHERE id='"._DB($a_admin_id)."'
    ";
    //echo $sql;
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    
    echo 'ok';
}

// ************************************************************
// Меняем активность пользователя
if ($_t=='a_admin_chk_active_edit'){
    $a_admin_id=_GP('a_admin_id');
    $val=_GP('val');
    
    $sql = "
    		UPDATE a_admin 
    			SET  
    				chk_active='"._DB($val)."',
                    data_change='".date('Y-m-d H:i:s')."'
                    
    		WHERE id='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    echo 'ok';
    
}
// ************************************************************
// Меняем права доступа
if ($_t=='a_admin_a_menu_save'){
    $a_admin_id=_GP('a_admin_id');
    $tip=_GP('tip');
    $a_menu_id=_GP('a_menu_id');
    
    //добавляем запись
    if ($tip=='add'){
        $sql = "INSERT into a_admin_a_menu (
				id1,
				id2
			) VALUES ('"._DB($a_admin_id)."','"._DB($a_menu_id)."')
            
            ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        
        //добавляем опции доступа к столбцам (a_admin_a_col)
        $sql = "INSERT into a_admin_a_col (
       				id1,
       				id2
       			) (SELECT '"._DB($a_admin_id)."', a_col.id
                        FROM a_col
                            WHERE a_col.a_menu_id='"._DB($a_menu_id)."'
        )";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        
        
        
    }
    elseif ($tip=='del'){
        $sql = "DELETE 
			FROM a_admin_a_menu 
				WHERE id1='"._DB($a_admin_id)."'
                AND id2='"._DB($a_menu_id)."'
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
        
        //удаляем опции доступа к столбцам (a_admin_a_col)
        $sql = "DELETE 
        			FROM a_admin_a_col 
        				WHERE id1='"._DB($a_admin_id)."'
                        AND id2 IN (SELECT a_col.id FROM a_col WHERE a_col.a_menu_id='"._DB($a_menu_id)."')
        ";
        mysql_query($sql) or die($sql.'<br />'.mysql_error());
    }
    else{
        echo 'Не определен тип!';exit();
    }
    
    //обновление данных админа
    $sql = "
    		UPDATE a_admin 
    			SET  
    				data_change='".date('Y-m-d H:i:s')."'
    		
    		WHERE id='"._DB($a_admin_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());

    $data_['col']='';
    $sql = "SELECT a_admin_a_col.id2
    				FROM a_admin_a_col 
    					WHERE a_admin_a_col.id1='"._DB($a_admin_id)."'
    "; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        if ($data_['col']!=''){$data_['col'].=',';}
        $data_['col'].=$myrow[0];
    }
    
    
    echo json_encode($data_);
}
// ************************************************************
// Удаляем таблицу
if ($_t=='del_inc'){
    $a_menu_id=_GP('a_menu_id');
    //получаем inc
    $sql = "SELECT a_menu.inc FROM a_menu WHERE a_menu.id='"._DB($a_menu_id)."'"; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $myrow = mysql_fetch_array($res);
    $inc=$myrow[0];
    if ($inc==''){echo 'Не определена переменная $inc!';exit();}
    
    //проверяем связанные таблицы с данной
    $sql = "SELECT  (SELECT a_col.a_menu_id FROM a_col WHERE a_col.id=a_connect.a_col_id1) AS a_menu_id_,
                    (SELECT a_menu.inc FROM a_menu, a_col AS c2 WHERE a_connect.a_col_id1=c2.id AND c2.a_menu_id=a_menu.id) AS inc_2_,
                    a_connect.a_col_id1 AS a_col_id_,
                    (SELECT a_col.col FROM a_col WHERE a_col.id=a_connect.a_col_id1) AS col_,
                    (SELECT a_col.tip FROM a_col WHERE a_col.id=a_connect.a_col_id1) AS tip_,
                    a_connect.tbl
                    
    				FROM a_connect, a_col 
    					WHERE a_col.a_menu_id='"._DB($a_menu_id)."'
                        AND a_col.id=a_connect.a_col_id2
    					
    "; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        if ($myrow['tip_']=='Связанная таблица 1-max'){
            
            // удаляем столбец связи
            $sql = "ALTER TABLE `"._DB($myrow['inc_2_'])."` DROP `"._DB($myrow['col_'])."`;";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
            //удаляем связи
            $sql = "DELETE 
            			FROM a_admin_a_col 
            				WHERE id2='"._DB($myrow['a_col_id_'])."'
            ";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
            
            // удляем запись из a_col
            $sql = "DELETE 
            			FROM a_col 
            				WHERE id='"._DB($myrow['a_col_id_'])."'
            ";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
            
        }
        elseif ($myrow['tip_']=='Связанная таблица max-max'){

            //удаляем связи
            $sql = "DELETE 
            			FROM a_admin_a_col 
            				WHERE id2='"._DB($myrow['a_col_id_'])."'
            ";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
            //удаляем связи
            $sql = "DELETE 
            			FROM a_connect 
            				WHERE a_col_id1 ='"._DB($myrow['a_col_id_'])."'
            ";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
            // удляем запись из a_col
            $sql = "DELETE 
            			FROM a_col 
            				WHERE id='"._DB($myrow['a_col_id_'])."'
            ";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
            
            //удаляем таблицу связи 
            if ($myrow['tbl']!=''){
                $sql = "DROP TABLE `"._DB($myrow['tbl'])."`"; //toowin86 2019-02-24
            }else{
                $sql = "DROP TABLE `"._DB($myrow['inc_2_'])."_"._DB($inc)."`";
            }
            
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
        }
    }
    //удаляем связи
    $sql = "DELETE 
    			FROM a_connect 
    				WHERE a_col_id2 IN (SELECT a_col.id FROM a_col WHERE a_col.a_menu_id='"._DB($a_menu_id)."' )
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    
    //Удаляем связанные функции
    $sql = "DELETE 
    			FROM a_admin_a_menu_a_com 
    				WHERE a_admin_a_menu_a_com.id2 IN (SELECT a_menu_a_com.id FROM a_menu_a_com WHERE a_menu_a_com.id1='"._DB($a_menu_id)."')
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    //Удаление связанных функций
    $sql = "DELETE 
    			FROM a_menu_a_com 
    				WHERE a_menu_a_com.id1='"._DB($a_menu_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    //удаляем связи
    $sql = "DELETE 
    			FROM a_admin_a_menu
    				WHERE id2='"._DB($a_menu_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    

    //удаляем пункт меню
    $sql = "SELECT id 
    				FROM a_col 
    					WHERE a_menu_id='"._DB($a_menu_id)."'
    "; 
    $res = mysql_query($sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        if (!del_col($myrow[0])){echo 'error '.$myrow[0];exit();}
    }
    
    //удаляем из a_menu
    $sql = "DELETE 
    			FROM a_menu 
    				WHERE a_menu.id='"._DB($a_menu_id)."' 
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
            
    
    //удаляем таблицу
    $sql = "DROP TABLE `"._DB($inc)."`";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    
    
    echo 'ok';
}
// ************************************************************
// Изменяем колонку
if ($_t=='col_edit'){
    
    /*
    a_col_id	5
    a_col_tip	chk_change
    a_col_val	1
    */
    $a_col_id=_GP('a_col_id');
    $a_col_tip=_GP('a_col_tip');
    $a_col_val=_GP('a_col_val');
    
    if ($a_col_id>0){
        $sql = "
        		UPDATE a_col 
        			SET  
        				"._DB($a_col_tip)."='"._DB($a_col_val)."'
        		
        		WHERE id='"._DB($a_col_id)."'
        ";
        if(!mysql_query($sql)){echo $sql;exit();}
        echo 'ok';
    }
}


// ************************************************************
// Удаляем колонку
if ($_t=='del_col'){
    $a_col_id=_GP('a_col_id');
    if (del_col($a_col_id)){echo 'ok';}
}
// ************************************************************
// Добавляем таблицу и пункт меню
if ($_t=='add_new_inc'){
    $name=_GP('name');
    $inc=_GP('inc');
    $chk_active=_GP('chk_active','0');
    $comments=_GP('comments');
    $pid=_GP('pid',0); 
        $SQL_PID="";
        if ($pid=='1'){
            $SQL_PID="`pid` int(11) NOT NULL, `sid` int(11) NOT NULL, ";
        }
    
     $sql="SELECT COUNT(*)
                FROM `information_schema`.`TABLES` 
                    WHERE `TABLE_SCHEMA`='"._DB($base_name)."'                    
                    AND `TABLE_NAME`='"._DB($inc)."'
                    
     "; 
	$res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]>0){
        echo 'Данная таблица уже присутствует в базе данных!';
    }else{
        // Добавляем таблицу
        $SQL_CREATE="CREATE TABLE IF NOT EXISTS `".$inc."` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              $SQL_PID
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
        if (!mysql_query($SQL_CREATE)){echo $SQL_CREATE;exit();}
        else{
            
            $sql = "SELECT MAX(sid)+1 
            				FROM a_menu
            	"; 
            $res = mysql_query($sql);
            $myrow = mysql_fetch_array($res);
            $new_sid=$myrow[0];
            //Добавляем информацию в a_menu
            $sql = "INSERT into a_menu (
            				pid,
            				sid,
                            chk_active,
                            name,
                            inc,
                            comments
                            
            			) VALUES (
            				'0',
            				'"._DB($new_sid)."',
                            '"._DB($chk_active)."',
                            '"._DB($name)."',
                            '"._DB($inc)."',
                            '"._DB($comments)."'
                            
            )";
            if (!mysql_query($sql)){echo $sql;exit();}
            
        }
        
    }
    echo 'ok';
    
}
// ************************************************************
// Получаем имена столбцов
if ($_t=='get_col'){
    $a_menu_id=_GP('a_menu_id');
    $data['col']=array();
    $data['col_ru']=array();
    $sql = "SELECT a_col.id, a_col.col_ru
    				FROM a_col 
    					WHERE a_col.a_menu_id='"._DB($a_menu_id)."' 
    						ORDER BY a_col.sid
    "; 
    $res = mysql_query($sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data['col_id'][]=$myrow[0];
        $data['col_ru'][]=$myrow[1];
    }
    echo json_encode($data);
}
// ************************************************************
// Добавляем новый столбец в таблицу
if ($_t=='add_new_col'){
    $a_menu_id=_GP('a_menu_id');
    $col=_GP('col');
    $col_ru=_GP('col_ru');
    $chk_active=_GP('chk_active','0');
    $chk_view=_GP('chk_view','0');
    $chk_change=_GP('chk_change','0');
    $tip=_GP('tip');
    $DEF=_GP('def');//по умолчанию
    $err_text='';
    
    
    //получаем название таблицы
    $sql = "SELECT IF(COUNT(*)>0,a_menu.inc,'')
    				FROM a_menu 
    					WHERE id='"._DB($a_menu_id)."' 
    					
    	"; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $myrow = mysql_fetch_array($res);
    $inc=$myrow[0];
    if ($inc==''){echo 'Не опознан $inc='.$inc; exit();}
    
    
         $sql="SELECT COUNT(*)
                FROM `information_schema`.`TABLES` 
                    WHERE `TABLE_SCHEMA`='"._DB($base_name)."'                    
                    AND `TABLE_NAME`='"._DB($inc)."'
                    
     "; 
	$res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]==0){
        // Добавляем таблицу
        $SQL_CREATE="CREATE TABLE IF NOT EXISTS `".$inc."` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
        mysql_query($SQL_CREATE) or die($SQL_CREATE.'<br />'.mysql_error());
    }
    
    // Проверяем наличие столбца в таблице
    $sql="SELECT COUNT(*)
                FROM `information_schema`.`COLUMNS` 
                    WHERE `TABLE_SCHEMA`='"._DB($base_name)."'                    
                    AND `TABLE_NAME`='"._DB($inc)."' 
                    AND `COLUMN_NAME`='"._DB($col)."'
                    
     "; 
	$res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]>0){
        $err_text.= 'Данный столбец уже присутствует в базе данных!';
       
    }else{
        //'Текст','Длиный текст','HTML-код','Целое число','Дробное число','Дата','Дата-время','Телефон',
        //'Email','Связанная таблица 1-max','Связанная таблица max-max','Функция','chk','enum'
       
        if ($tip=='Текст'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` VARCHAR( 999 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ".$DEF.";";
        }
        elseif($tip=='Длинный текст'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ".$DEF.";";
        }
        elseif($tip=='HTML-код'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ".$DEF.";";
        }
        elseif($tip=='Целое число'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` INT(255) NOT NULL ".$DEF.";";
        }
        elseif($tip=='Дробное число'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` FLOAT  ".$DEF.";";
        }
        elseif($tip=='Стоимость'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` FLOAT  ".$DEF.";";
        }
        elseif($tip=='Дата'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` DATE NOT NULL ".$DEF.";";
        }
        elseif($tip=='Дата-время'){
            //ALTER TABLE `s_cat` ADD `ssssc` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;
            $TIP_="DATETIME";if ($DEF=='CURRENT_TIMESTAMP'){$TIP_="TIMESTAMP";}
            
            if ($DEF!=''){$DEF=" DEFAULT ".$DEF."";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` $TIP_ NOT NULL ".$DEF.";";
        }
        elseif($tip=='Телефон'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` VARCHAR(255) NOT NULL ".$DEF.";";
        }
        elseif($tip=='Email'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ".$DEF.";";
        }
        elseif($tip=='Связанная таблица 1-max'){
            $a_menu_id_connect=_GP('1-max');
            $a_col_col_connect=_GP('1-max_col');
            $a_col_usl_connect=_GP('1-max_usl');
            $a_col_chk_connect=_GP('1-max_chk');
            
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            //ALTER TABLE `s_cat` ADD `ssf` INT NOT NULL DEFAULT '1'
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` INT(11) NOT NULL ".$DEF.";";
        }
        elseif($tip=='Связанная таблица max-max'){
            $a_menu_id_connect=_GP('max-max');
            $a_col_col_connect=_GP('max-max_col');
            $a_col_usl_connect=_GP('max-max_usl');
            $a_col_chk_connect=_GP('max-max_chk');
            
            $sql_ = "SELECT a_menu.inc 
            				FROM a_menu 
            					WHERE a_menu.id='"._DB($a_menu_id_connect)."'
            	"; 
            $res = mysql_query($sql_) or die($sql_.'<br />'.mysql_error());
            $myrow = mysql_fetch_array($res);
            $inc_connect=$myrow[0];
            
            
            $SQL="SELECT COUNT(*) FROM `"._DB($inc)."`";//test
        }
        elseif($tip=='Функция'){
            $SQL="SELECT COUNT(*) FROM `"._DB($inc)."`";//test
        }
        elseif($tip=='chk'){
            if ($DEF!=''){if ($DEF=='1'){$DEF="TRUE";} else{$DEF="FALSE";} $DEF=" DEFAULT ".$DEF."";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` BOOLEAN NOT NULL ".$DEF." ;";
        }
        elseif($tip=='enum'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            
            $enum_val=str_replace('"',"'",_GP('enum_val'));
            $SQL="ALTER TABLE 
                    `"._DB($inc)."` 
                ADD `"._DB($col)."` ENUM( ".$enum_val." ) 
                
                    NOT NULL 
                    ".$DEF.";";
            
        }
        elseif($tip=='Цвет'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` VARCHAR(255) NOT NULL ".$DEF.";";
        }
        elseif($tip=='Фото'){
            $SQL='';
        }
        elseif($tip=='Ссылка'){
            if ($DEF!=''){$DEF=" DEFAULT '"._DB($DEF)."'";}
            $SQL="ALTER TABLE `"._DB($inc)."` ADD `"._DB($col)."` TEXT NOT NULL ".$DEF.";";
        }
        //выполнение запроса
        if(isset($SQL)){
            if ($SQL!=''){
                mysql_query($SQL) or die($SQL.'<br />'.mysql_error());
            }
        }else{
            echo 'Тип не определен! <br />$tip="'.$tip.'"';exit();
        }
        
        
    }
    
        // Проверяем есть ли такой столбец в a_col
        $sql = "SELECT COUNT(*)
        				FROM a_col 
        					WHERE a_menu_id='"._DB($a_menu_id)."'
        					AND col='"._DB($col)."'
        	"; 
        $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
        $myrow = mysql_fetch_array($res);
    
        if($myrow[0]==0){
            //Получаем максимальный sid
            $sql_ = "SELECT MAX(sid)+1 FROM a_col"; 
            $res = mysql_query($sql_) or die($sql_.'<br />'.mysql_error());
            $myrow = mysql_fetch_array($res);
            $new_sid=$myrow[0];
            
            //добавляем записи в таблицу a_col
            $sql_ = "INSERT into a_col (
            				sid,
            				chk_active,
                            chk_view,
                            chk_change,
                            a_menu_id,
                            col,
                            col_ru,
                            tip
            			) VALUES (
            				'"._DB($new_sid)."',
            				'"._DB($chk_active)."',
                            '"._DB($chk_view)."',
                            '"._DB($chk_change)."',
                            '"._DB($a_menu_id)."',
                            '"._DB($col)."',
                            '"._DB($col_ru)."',
                            '"._DB($tip)."'
            )";
            mysql_query($sql_) or die($sql_.'<br />'.mysql_error());
            
                $a_col_id_new = mysql_insert_id();
                if ($tip=='Связанная таблица 1-max'){
                    
                    $sql_ = "INSERT into a_connect (
                    				a_col_id1,
                                    a_col_id2,
                                    usl,
                                    chk
                    			) VALUES (
                    				'"._DB($a_col_id_new)."',
                                    '"._DB($a_col_col_connect)."',
                                    '"._DB($a_col_usl_connect)."',
                                    '"._DB($a_col_chk_connect)."'
                    )";
                    mysql_query($sql_) or die($sql_.'<br />'.mysql_error());
                }
                elseif($tip=='Связанная таблица max-max'){
                    
                    $SQL_CREATE="CREATE TABLE IF NOT EXISTS `".$inc."_".$inc_connect."` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `id1` int(11) NOT NULL,
                                  `id2` int(11) NOT NULL,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
                    mysql_query($SQL_CREATE) or die($SQL_CREATE.'<br />'.mysql_error());
                    
                   
                    $sql_ = "INSERT into a_connect (
                    				a_col_id1,
                                    a_col_id2,
                                    usl,
                                    chk
                    			) VALUES (
                    				'"._DB($a_col_id_new)."',
                                    '"._DB($a_col_col_connect)."',
                                    '"._DB($a_col_usl_connect)."',
                                    '"._DB($a_col_chk_connect)."'
                    )";
                    mysql_query($sql_) or die($sql_.'<br />'.mysql_error());
                    
                }
            
            
        }
        else{
            $err_text.= '<br />Столбец col="'._DB($col).'" уже имеется в таблице a_col<br />'; 
        }
    
    // проверяем имеется ди данный столбец в базе
    if ($err_text!=''){echo $err_text;}
    else{echo 'ok';}
    
}
// ************************************************************
// Получение информации о пункте меню
if ($_t=='get_menu_info'){
    $id=_GP('id');
    
    $data['chk_active']=array();
    $data['chk_view']=array();
    $data['chk_change']=array();
    $data['col']=array();
    $data['tip']=array();
    
    // Получение информации о данном пункте меню
    $sql = "SELECT  id,
                    chk_active,
                    chk_view,
                    chk_change,
                    col,
                    col_ru,
                    tip
                    
    				FROM a_col 
    					WHERE a_col.a_menu_id='"._DB($id)."' 
    						ORDER BY sid
    "; 
    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data['id'][$i]=$myrow[0];
        $data['chk_active'][$i]=$myrow[1];
        $data['chk_view'][$i]=$myrow[2];
        $data['chk_change'][$i]=$myrow[3];
        $data['col'][$i]=$myrow[4];
        $data['col_ru'][$i]=$myrow[5];
        $data['tip'][$i]=$myrow[6];
    }
    
    echo json_encode($data);
}
// ************************************************************
// ИЗМЕНЕНИЕ НАЗВАНИЯ МЕНЮ
if ($_t=='a_menu_name_edit_save'){
    $a_menu_id=_GP('a_menu_id');
    $val=_GP('val');
    $sql = "
    		UPDATE a_menu 
    			SET  
    				name='"._DB($val)."'
    		
    		WHERE id='"._DB($a_menu_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    echo 'ok';
}
// ************************************************************
// АКТИВНОСТЬ
if ($_t=='a_menu_chk_active'){
    $a_menu_id=_GP('a_menu_id');
    $val=_GP('val');
    $sql = "
    		UPDATE a_menu 
    			SET  
    				chk_active='"._DB($val)."'
    		
    		WHERE id='"._DB($a_menu_id)."'
    ";
    mysql_query($sql) or die($sql.'<br />'.mysql_error());
    echo 'ok';
}
// ************************************************************
// СОРТИРОВКА
if ($_t=='save_sort'){
    $id_arr=_GP('id_arr',array());
    $pid_arr=_GP('pid_arr',array());
    
    if (count($id_arr)>0){
        $pid_txt='';
        $sid_txt='';
        foreach($id_arr as $sid => $id){
            $sid_=(int) $sid + 1;
            $pid_txt.=" WHEN ".$id. " THEN ".$pid_arr[$sid];
            $sid_txt.=" WHEN ".$id. " THEN ".$sid_;
        }
        
            $sql = "
            		UPDATE a_menu 
            			SET  
            				pid=CASE id $pid_txt END,
            				sid=CASE id $sid_txt END
            		
            		WHERE id IN ('".implode("','",$id_arr)."')
            ";
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            echo 'ok';
    }
}
// ************************************************************
// Получение данных связи
if ($_t=='get_info_connect'){
    $data_=array();
    $a_col_id=_GP('a_col_id');
    
    if ($a_col_id!=''){
        $sql = "SELECT usl, chk
        				FROM a_connect 
        					WHERE a_connect.a_col_id1='"._DB($a_col_id)."' 
        					
        	"; 
        $res = mysql_query($sql) or die(mysql_error());
        $myrow = mysql_fetch_array($res);
        $data_['usl']=_IN($myrow[0]);
        $data_['chk']=$myrow[1];
        
    }else{
        $_SESSION['error']['a_menu_ajax__'.$_t.'_'.date('Y-m-d H:i:s')]='Не определен $a_col_id';
        echo 'Не определен $a_col_id';exit();
    }
    echo json_encode($data_);
}
// ************************************************************
// Сохранение данных связи
if ($_t=='save_info_connect'){
    $data_=array();
    $a_col_id=_GP('a_col_id');
    $chk=_GP('chk');
    $usl=_GP('usl');
    
    if ($a_col_id!=''){
        $sql = "
        		UPDATE a_connect 
        			SET  
        				usl='"._DB($usl)."',
        				chk='"._DB($chk)."'
        		
        		WHERE a_col_id1='"._DB($a_col_id)."'
        ";
        if(!mysql_query($sql)){echo $sql;$_SESSION['error']['a_menu_ajax__'.$_t.'_'.date('Y-m-d H:i:s')]=$sql;exit();}
        $data_['satus_']='ok';
    }else{
        $_SESSION['error']['a_menu_ajax__'.$_t.'_'.date('Y-m-d H:i:s')]='Не определен $a_col_id';
        echo 'Не определен $a_col_id';exit();
    }
    echo json_encode($data_);
}
// ************************************************************
// СОРТИРОВКА col
if ($_t=='save_sort_col'){
    $id_arr=_GP('id_arr',array());
    
    if (count($id_arr)>0){
        $sid_txt='';
        foreach($id_arr as $sid => $id){
            $sid_=(int) $sid + 1;
            $sid_txt.=" WHEN ".$id. " THEN ".$sid_;
        }
        
            $sql = "
            		UPDATE a_col
            			SET  
            				sid=CASE id $sid_txt END
            		
            		WHERE id IN ('".implode("','",$id_arr)."')
            ";
            
            mysql_query($sql) or die($sql.'<br />'.mysql_error());
            echo 'ok';
    }
}
// ************************************************************
// Должности
if ($_t=='set_i_post'){
    $a_admin_id=_GP('a_admin_id');
    $sql = "SELECT name
    				FROM a_admin 
    					WHERE a_admin.id='"._DB($a_admin_id)."' 
    				
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $myrow = mysql_fetch_array($res);
    $a_admin_name=$myrow[0];
    ?>
    <h1 class="set_i_post__h1">Должности: <strong><?=$a_admin_name;?></strong><input type="hidden" name="a_admin_id" value="<?=$a_admin_id;?>" /></h1>
    <div class="set_i_post__add_form">
    <h2>Добавить новую должность</h2>
    <div class="set_i_post__add_main ttable2">
        <div class="ttable2_tbody_tr">
            <div class="ttable2_tbody_tr_td">
                <select class="set_i_post__add_post" data-placeholder="Должность">
                
               
            <?php
            
            //Форма добавления новой должности
            $sql = "SELECT i_post.id, i_post.name, i_post.comments, i_post.obj
                				FROM i_post
                						ORDER BY i_post.name
             ";
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                ?>
                <option value="<?=_IN($myrow['id']);?>" data-title="<?=_IN($myrow['comments']);?>" data-obj="<?=_IN($myrow['obj']);?>"><?=$myrow['name'];?></option>
                <?php
            }
        
            ?>
            </select>
        </div>
        <div class="ttable2_tbody_tr_td">
            <input type="text" class="set_i_post__data_input" placeholder="Дата вступления" value="<?=date('d.m.Y');?>" />
        </div>
   
    </div>
    <div class="ttable2_tbody_tr">
        <div class="ttable2_tbody_tr_td">
            <h2>Расчет зарплаты:</h2>
        </div>
    </div>
    <div class="ttable2_tbody_tr">
        <div class="ttable2_tbody_tr_td set_i_post__add_zp">
            <select class="set_i_post__zp_target_select" data-placeholder="Тип з/п">
                <?php
                
                $sql = "SELECT id, obj, target
                    				FROM i_obj
                 ";
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                {
                   ?>
                   <option value="<?=_IN($myrow['id']);?>" data-obj="<?=_IN($myrow['obj']);?>"><?=($myrow['target']);?></option>
                   <?php
                }
                
                ?>
            </select>
            
            
        </div>
        <div class="ttable2_tbody_tr_td">
            <input type="text" class="set_i_post__zp_val_input" placeholder="Значение" />
        </div>
        <div class="ttable2_tbody_tr_td">
            <span class="btn_gray set_i_post__zp_target_add">Добавить</span>
        </div>
        </div>
        <div class="ttable2_tbody_tr">
            <div class="ttable2_tbody_tr_td"><h3>Начисление з/п</h3></div>
        </div>

    </div>
    <div class="set_i_post__add_all_zp">
        <div class="set_i_post__add_all_zp_tbl ttable">
            <div class="ttable_thead">
                <div class="ttable_thead_tr">
                    <div class="ttable_thead_tr_th">Тип</div>
                    <div class="ttable_thead_tr_th">Значение</div>
                    <div class="ttable_thead_tr_th"></div>
                </div>
            </div>
            <div class="ttable_tbody">
            </div>
        </div>
    </div>
                
    <div class="add_form_save_div">
        <div><center><span class="btn_orange add_form_save">Сохранить</span></center></div>
    </div>

    <hr />
    <div class="set_i_post__added_div">
        <h2>Текущие должности:</h2>
        <div class="find_zp_res"></div>
    </div>
    <?php
    
}
// ************************************************************
// Сохранение должности
if ($_t=='add_form_save'){
    
    $a_admin_id=_GP('a_admin_id');
    $i_post_id=_GP('i_post_id');
    $i_post_data=_GP('i_post_data');
    $i_zp_tip=_GP('i_zp_tip',array());
    $i_zp_val=_GP('i_zp_val',array());
    
    //Добавляем должность к админу
    $sql = "INSERT into a_admin_i_post (
    				id1,
    				id2,
                    data_start,
                    data_end
    			) VALUES (
    				'"._DB($a_admin_id)."',
    				'"._DB($i_post_id)."',
                    '".date('Y-m-d H:i:s',strtotime($i_post_data))."',
                    NULL
    )";
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $a_admin_i_post_id = mysql_insert_id();
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    foreach($i_zp_tip as $key => $val){
        //Добавляем расчет з/п
        $sql = "SELECT IF(COUNT(*)>0,i_zp.id,'')
        				FROM i_zp 
        					WHERE i_zp.i_obj_id='"._DB($val)."'
                            AND i_zp.val='"._DB($i_zp_val[$key])."'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $i_zp_id=$myrow[0];
        if ($i_zp_id==''){
            //Добавляем тип и значения расчета з.п 
            $sql = "INSERT into i_zp (
            				i_obj_id,
            				val
            			) VALUES (
            				'"._DB($val)."',
            				'"._DB($i_zp_val[$key])."'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $i_zp_id = mysql_insert_id();
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
        }
        
        //Привязываем расчет з/п к долности работника
        $sql = "INSERT into a_admin_i_post_i_zp (
        				id1,
        				id2
        			) VALUES (
        				'"._DB($a_admin_i_post_id)."',
        				'"._DB($i_zp_id)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    }
    echo json_encode($data_);
}

// ************************************************************
// Поиск должностей
if ($_t=='find_zp'){
    $data_=array();
    $data_['i_post']=array();
    $a_admin_id=_GP('a_admin_id');
    
    $sql = "SELECT id, id2, data_start, data_end
				FROM a_admin_i_post
					WHERE id1='"._DB($a_admin_id)."'
                    ORDER BY data_end
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['i_post'][$i]['id']=$myrow['id'];
        $data_['i_post'][$i]['id2']=$myrow['id2'];
        $data_['i_post'][$i]['dt1']='';
        if ($myrow['data_start']!='' and $myrow['data_start']!='0000-00-00 00:00:00'){
            $data_['i_post'][$i]['dt1']=date('d.m.Y',strtotime($myrow['data_start']));
        }
        $data_['i_post'][$i]['dt2']='';
        if ($myrow['data_end']!='' and $myrow['data_end']!='0000-00-00 00:00:00'){
            $data_['i_post'][$i]['dt2']=date('d.m.Y',strtotime($myrow['data_end']));
        }
        
         $data_['i_post'][$i]['zp']=array();
        //Получаем условия начисления зарплаты
        $sql_zp = "SELECT i_zp.i_obj_id, i_zp.val
            				FROM a_admin_i_post_i_zp, i_zp 
            					WHERE a_admin_i_post_i_zp.id1='"._DB($myrow['id'])."'
                                AND a_admin_i_post_i_zp.id2=i_zp.id
                                
         ";
         //echo $sql_zp;
        $mt = microtime(true);
        $res_zp = mysql_query($sql_zp) or die(mysql_error().'<br/>'.$sql_zp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_zp;$data_['_sql']['time'][]=$mt;
        for ($myrow_zp = mysql_fetch_array($res_zp); $myrow_zp==true; $myrow_zp = mysql_fetch_array($res_zp))
        {
            $data_['i_post'][$i]['zp'][$myrow_zp['i_obj_id']]=$myrow_zp['val'];
        }
        $i++;
    }
    echo json_encode($data_);
}

// ************************************************************
// Поиск должностей
if ($_t=='zp_closed_save'){
    $data_=array();
    $a_admin_i_post=_GP('a_admin_i_post');
    $zp_closed_data=_GP('zp_closed_data');
        if ($zp_closed_data==''){echo 'Дата не должна быть пустой';exit;}
        $zp_closed_data=date('Y-m-d H:i:s',strtotime($zp_closed_data));
    //проверяем дату приема на работу
    $sql = "SELECT data_start
    				FROM a_admin_i_post 
    					WHERE a_admin_i_post.id='"._DB($a_admin_i_post)."' 
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_start=$myrow['data_start'];
        if ($data_start=='' or $data_start=='0000-00-00 00:00:00'){echo 'Не верно указана дата приема на работу';exit;}
    if (strtotime($data_start)>=strtotime($zp_closed_data)){
        echo 'Дата увольнения не может быть равной или быть меньше даты приема на работу '.date('d.m.Y',strtotime($data_start));exit();
    }
        
    $zp_closed_info=_GP('zp_closed_info');
    
    $sql = "
    		UPDATE a_admin_i_post 
    			SET  
    				data_end='"._DB($zp_closed_data)."',
    				end_info='"._DB($zp_closed_info)."'
    		
    		WHERE id='"._DB($a_admin_i_post)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    echo json_encode($data_);
}

// ************************************************************
// Изменение даты приема на работу
if ($_t=='i_zp_open_data1_input_save'){
    $data_=array();
    $i_zp_id=_GP('i_zp_id');
    $data1=_GP('data1');
        $data1=date('Y-m-d H:i:s',strtotime($data1));
    
    $sql = "UPDATE a_admin_i_post 
    			SET  
    				data_start='"._DB($data1)."'
    		
    		WHERE id='"._DB($i_zp_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    echo json_encode($data_);
}

// ************************************************************
// Изменение просмотра товаров
if ($_t=='chk_view_all_s_cat_change'){
    $data_=array();
    $a_admin_id=_GP('a_admin_id');
    $val=_GP('val');
    
    $sql = "UPDATE a_admin
    			SET  
    				chk_view_all_s_cat='"._DB($val)."'
    		
    		WHERE a_admin.id='"._DB($a_admin_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    echo json_encode($data_);
}

//************************************************************************************************** 
}else{
    echo 'Ошибка авторизации!';
}
?>