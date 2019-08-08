<?php
//Артиклы, цены и количество от поставщиков для товаров в каталоге
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');

include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


    $_t=_GP('_t');
    // ************************************************************

if ($_t=='nakrutka'){
    $id=_GP('id');
    $sql = "SELECT  i_contr.`nakrutka`
    				
                    FROM i_contr
    					WHERE i_contr.id='"._DB($id)."'
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $myrow = mysql_fetch_array($res);
    $nn=$myrow[0]-0;
    echo $nn;
}
    // Автозаполнение контрагентов
if ($_t=='i_contr_autocomplete'){
    
    $data_=array();
    $term=_GP('term','');
    
    $data_[0]['label']='Добавить нового контрагента';
    $data_[0]['value']=$term;
    $data_[0]['id']='';
    $data_[0]['p']='';
    $data_[0]['text']='';
    $data_[0]['e']='';
    

    
    $sql = "SELECT  i_contr.id, 
                    i_contr.name, 
                    i_contr.`phone`, 
                    i_contr.`email`, 
                    (SELECT 
                            IF(COUNT(*)>0,i_contr_org.name,'') 
                        FROM i_contr_org, i_contr_i_contr_org
                            WHERE i_contr_i_contr_org.id2=i_contr_org.id
                            AND i_contr_i_contr_org.id1=i_contr.id
                            LIMIT 1
                            ) AS org, 
                    (SELECT 
                            IF(COUNT(*)>0,i_contr_org.id,'') 
                        FROM i_contr_org, i_contr_i_contr_org
                            WHERE i_contr_i_contr_org.id2=i_contr_org.id
                            AND i_contr_i_contr_org.id1=i_contr.id
                            LIMIT 1
                            ) AS org_id, 
                    (SELECT COUNT(s_article.id) FROM s_article WHERE s_article.i_contr_id=i_contr.id) AS cnt_,
                    
                    i_contr.`nakrutka` AS nn
    				
                    FROM i_contr
    					WHERE i_contr.id='"._DB($term)."'
                        OR i_contr.name LIKE '%"._DB($term)."%'
                        OR i_contr.`phone` LIKE '"._DB($term)."%'
                        OR i_contr.`email` LIKE '"._DB($term)."%'
   						OR i_contr.id IN (SELECT i_contr_i_contr_org.id1 FROM i_contr_org, i_contr_i_contr_org WHERE i_contr_i_contr_org.id2=i_contr_org.id AND i_contr_org.name LIKE '%"._DB($term)."%' OR i_contr_org.inn LIKE '%"._DB($term)."%')
                        
                        ORDER BY cnt_ DESC
                        LIMIT 20
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_[$i]['label']=$myrow[1];
        $data_[$i]['value']=$myrow[1];
        $data_[$i]['text']=$myrow[1];
        
        $data_[$i]['p']='';if ($myrow[2]!=''){$data_[$i]['p']=conv_('phone_from_db',$myrow[2]);}
        
        $data_[$i]['e']=$myrow[3];
        $data_[$i]['org']=$myrow['org'];
        $data_[$i]['org_id']=$myrow['org_id'];
        $data_[$i]['id']=$myrow[0];
    }
    echo json_encode($data_);
}
//*********************************************************************************************************

if ($_t=='find_postav_in_this_struktura'){
    $data_=array();
    $str_arr=_GP('str_arr','');
    $SQL_='';
    if (is_array($str_arr) and count($str_arr)>0){$SQL_=implode("','",$str_arr);}
    else{echo 'Не верно указан раздел структуры';exit;}
    
    
    

    $data_['i']=array();
    $data_['n']=array();
    $data_['nn']=array();
    $sql = "SELECT DISTINCT i_contr.id, i_contr.name, i_contr.nakrutka
                    FROM s_article, s_cat_s_struktura, i_contr
    					WHERE s_article.s_cat_id=s_cat_s_struktura.id1
                        AND s_cat_s_struktura.id2 IN ('".$SQL_."')
                        AND s_article.i_contr_id=i_contr.id
                        ORDER BY i_contr.name
    ";
     
    $mt = microtime(true);
   $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
   $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['i'][$i]=$myrow[0];
        $data_['n'][$i]=$myrow[1];
        $data_['nn'][$i]=$myrow[2];
    }
    echo json_encode($data_);
}
//*********************************************************************************************************

if ($_t=='s_article_price_quick'){
    $data_=array();
    $id=_GP('id');
        if ($id==''){echo 'Не определен id';exit;}
    $sum=_GP('sum')-0;
    
    $sql = "
    		UPDATE s_article 
    			SET  
    				price='"._DB($sum)."'
                    
    		WHERE s_article.id='"._DB($id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    
    echo json_encode($data_);
}
//*********************************************************************************************************

if ($_t=='convert_price_start_convert'){
    $data_=array();
    $fileName=_GP('f');
    $opt_tip=_GP('opt_tip');
    $i_contr=_GP('i_contr');
    $opt1=_GP('opt1')-0;
        if ($opt1<0){echo 'Не указан столбец с артикулом';exit;}
    $opt2=_GP('opt2')-0;
        if ($opt2<0){echo 'Не указан столбец с ценой';exit;}
    $opt3=_GP('opt3')-0;
    $opt4=_GP('opt4')-0;
    $opt5=_GP('opt5')-0;
    $all_edit=0;
    $targetDir = '../../upload/file/temp';
    $empty_value = 0;		//счетчик пустых значений
    
    $s_cat_arr=array();
    $s_cat_arr['n']=array();
    $s_cat_arr['p']=array();
    $s_cat_arr['a']=array();
    $s_cat_arr['ap']=array();
    $s_cat_arr['a_all']=array();
    //получаем массив текущих товаров
    $sql = "SELECT  DISTINCT
                    s_cat.id, 
                    s_cat.name,
                    s_cat.price
                    
        				FROM s_cat
                            
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $s_cat_arr['n'][$myrow[0]]=$myrow[1];
        $s_cat_arr['p'][$myrow[0]]=$myrow[2];
        $s_cat_arr['a'][$myrow[0]]=array();
        $s_cat_arr['ap'][$myrow[0]]=array();
        $sql_s_article = "SELECT s_article.id, s_article.article, s_article.price
            				FROM s_article 
            					WHERE s_article.s_cat_id='"._DB($myrow[0])."'
                                AND s_article.i_contr_id='"._DB($i_contr)."'
         ";
        $mt = microtime(true);
        $res_s_article = mysql_query($sql_s_article) or die(mysql_error().'<br/>'.$sql_s_article);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_article;$data_['_sql']['time'][]=$mt;
        for ($myrow_s_article = mysql_fetch_array($res_s_article); $myrow_s_article==true; $myrow_s_article = mysql_fetch_array($res_s_article))
        {
            $s_cat_arr['a'][$myrow[0]][$myrow_s_article[0]]=$myrow_s_article[1];
            $s_cat_arr['ap'][$myrow[0]][$myrow_s_article[0]]=$myrow_s_article[2];
            $s_cat_arr['a_all'][$myrow_s_article[0]]=$myrow_s_article[1];
        }
    }
    
    //обрабатываем и выводим полученную таблицу
        $ext=preg_replace("/.*?\./", '', $fileName);
        //Чтение xls файла
        if ($ext=='xls' or $ext=='xlsx'){
            
            set_time_limit(1800);
            ini_set('memory_liit', '128M');

            $chunkSize = 1000;		//размер считываемых строк за раз
            $startRow = 2;			//начинаем читать со строки 2, в PHPExcel первая строка имеет индекс 1, и как правило это строка заголовков
            $exit = false;			//флаг выхода
            

            if (!file_exists($targetDir . '/' .$fileName)) {
            	echo 'no file: '.$targetDir . '/' .$fileName;
                exit();
            }
            if (!file_exists('../class/PHPExcel.php')){echo 'Не найден файл ../class/PHPExcel.php';exit;}
            require_once '../class/PHPExcel.php';
            
            $objReader = PHPExcel_IOFactory::createReaderForFile($targetDir . '/' .$fileName);
            $objReader->setReadDataOnly(true);
          
          
            // PHPExcel
            class chunkReadFilter implements PHPExcel_Reader_IReadFilter 
            {
                private $_startRow = 0; 
                private $_endRow = 0; 
                public function setRows($startRow, $chunkSize) { 
                    $this->_startRow    = $startRow; 
                    $this->_endRow      = $startRow + $chunkSize; 
                } 
            	public function readCell($column, $row, $worksheetName = '') { 
                    //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow 
                    if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) { 
                        return true; 
                    } 
                    return false; 
                } 
            }
            
            $chunkFilter = new chunkReadFilter(); 
            $objReader->setReadFilter($chunkFilter); 
            //внешний цикл, пока файл не кончится
            $SQL_='';
            $SQL_1='';
            $SQL_2='';
            $SQL_3='';
            
            while (!$exit) 
            {
                
            	$chunkFilter->setRows($startRow,$chunkSize); 	//устанавливаем знаечние фильтра
            	$objPHPExcel = $objReader->load($targetDir . '/' .$fileName);		//открываем файл
            	$objPHPExcel->setActiveSheetIndex(0);		//устанавливаем индекс активной страницы
            	$objWorksheet = $objPHPExcel->getActiveSheet();	//делаем активной нужную страницу
            	for ($i = $startRow; $i < $startRow + $chunkSize; $i++) 	//внутренний цикл по строкам
            	{
                   $article = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($opt1, $i)->getValue()));
                   $name = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($opt3, $i)->getValue()));
                   $price = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($opt2, $i)->getValue()))-0;
                   $kolvo=0;if ($opt4-0>=0){$kolvo = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($opt4, $i)->getValue()));}
                   $price2=$price;if ($opt5-0>=0){$price2 = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($opt5, $i)->getValue()));}
                   
                   if ($opt_tip=='1'){//Обновляем цены и наличие
                        if ($article!='' and $price!=''){
                           if ($SQL_!=''){$SQL_.=',';} 
                           $SQL_.="'"._DB($article)."'";
                           
                           $SQL_1.=" WHEN article='"._DB($article)."' THEN '"._DB($price)."'";
                           $SQL_2.=" WHEN article='"._DB($article)."' THEN '"._DB($kolvo)."'";
                           $SQL_3.=" WHEN article='"._DB($article)."' THEN '"._DB($price2)."'";
                            
                           if (mb_strlen($SQL_1,'UTF-8')>5000){
                               $sql = "UPDATE `s_article` SET `price` = CASE
                                            ".$SQL_1."
                                            END ,
                                        `kolvo` = CASE
                                            $SQL_2
                                            END 
                                        WHERE s_article.article IN ($SQL_) AND s_article.i_contr_id='"._DB($i_contr)."'
                               ";
                               $mt = microtime(true);
                               $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                               $edit = mysql_affected_rows();
                               $all_edit=$all_edit+$edit;
                               //echo $edit.'<br />'.$sql.'<br />';
                               $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                               
                               
                               $sql = "UPDATE `s_cat` SET `price` = CASE
                                            ".$SQL_3."
                                            END 
                                        WHERE s_cat.article IN ($SQL_)
                               ";
                               $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                               
                               
                               $SQL_='';
                               $SQL_1='';
                               $SQL_2='';
                               $SQL_3='';
                           }
                           
                       }
                   }
                   elseif ($opt_tip=='2'){//Добавляем товар в каталог
                        if ($price-0>0 and $name!='' and $article!=''){
                            if (in_array($name,$s_cat_arr['n'])){
                                $s_cat_id=array_search($name,$s_cat_arr['n']);
                            }else{
                                if (in_array($article,$s_cat_arr['a_all'])){
                                    $s_article_id=array_search($article,$s_cat_arr['a_all']);
                                    foreach($s_cat_arr['a'] as $s_cat_id_ => $arr){
                                        if ($s_cat_arr['a'][$s_cat_id_][$s_article_id]==$article){
                                            $s_cat_id=$s_cat_id_;
                                        }
                                    }
                                }
                                else{
                                    $sql = "INSERT into s_cat (
                                    				chk_active,
                                    				name,
                                                    price,
                                                    html_code,
                                                    data_change
                                    			) VALUES (
                                    				'0',
                                                    '"._DB($name)."',
                                                    '"._DB($price2)."',
                                                    '<p>"._DB($name)."</p>',
                                    				'".date('Y-m-d H:i:s')."'
                                    )";
                                    
                                    $mt = microtime(true);
                                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                    $s_cat_id = mysql_insert_id();
                                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                }
                            }
                            
                            if (isset($s_cat_arr['a']) and isset($s_cat_arr['a'][$s_cat_id]) and in_array($article,$s_cat_arr['a'][$s_cat_id])){
                                $s_article_id=array_search($article,$s_cat_arr['a'][$s_cat_id]);
                                $sql = "
                                		UPDATE s_article 
                                			SET  
                                				price='"._DB($price)."',
                                				kolvo='"._DB($kolvo)."'
                                		
                                		WHERE id='"._DB($s_article_id)."'
                                ";
                                $mt = microtime(true);
                                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                            }
                            
                            else{
                            
                                $sql = "INSERT into s_article (
                                                i_contr_id,
                                				s_cat_id,
                                                article,
                                				price,
                                                kolvo
                                			) VALUES (
                                				'"._DB($i_contr)."',
                                				'"._DB($s_cat_id)."',
                                                '"._DB($article)."',
                                                '"._DB($price)."',
                                                '"._DB($kolvo)."'
                                                
                                )";
                                
                                $mt = microtime(true);
                                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                
                            }
                        }
                   }
                   
                    if (empty($article)){$empty_value++;}		//проверяем значение на пустоту
            		if ($empty_value == 300)		//после 300 пустых значений, завершаем обработку файла, думая, что это конец
            		{	
            			$exit = true;	
            			break;		
            		}	
            	}
                //echo '+++';
                //Запрос обновления
                if ($opt_tip=='1'){//Обновляем цены и наличие
                    if ($SQL_!=''){
                           $sql = "UPDATE `s_article` SET `price` = CASE
                                        ".$SQL_1."
                                        END ,
                                    `kolvo` = CASE
                                        $SQL_2
                                        END 
                                    WHERE article IN ($SQL_) AND i_contr_id='"._DB($i_contr)."'
                           ";
                           $mt = microtime(true);
                           $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                           $edit = mysql_affected_rows();
                           $all_edit=$all_edit+$edit;
                           $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                           
                           
                           
                           $sql = "UPDATE `s_cat` SET `price` = CASE
                                        ".$SQL_3."
                                        END 
                                    WHERE s_cat.article IN ($SQL_)
                           ";
                           $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                           
                           $SQL_='';
                           $SQL_1='';
                           $SQL_2='';
                           $SQL_3='';
                    }
                }
                elseif ($opt_tip=='2'){//Добавляем товар в каталог
                        
                }
                
            	$objPHPExcel->disconnectWorksheets(); 		//чистим 
            	unset($objPHPExcel); 						//память
            	$startRow += $chunkSize;					//переходим на следующий шаг цикла, увеличивая строку, с которой будем читать файл
            }
            

        }
        //*************************************************************************
    $s_cat_arr2=array();
    $s_cat_arr2['p']=array();
    $s_cat_arr2['n']=array();
    $s_cat_arr2['a']=array();
    $data_['upp_n']=array();
    $data_['upp_p1']=array();
    $data_['upp_p2']=array();
    //получаем массив текущих товаров
    $sql = "SELECT  DISTINCT
                    s_cat.id,
                    s_cat.name,
                    s_cat.price
                    
        				FROM s_cat
                            
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $s_cat_arr2['n'][$myrow[0]]=$myrow[1];
        $s_cat_arr2['p'][$myrow[0]]=$myrow[2];
        $s_cat_arr2['a'][$myrow[0]]=array();
        $s_cat_arr2['ap'][$myrow[0]]=array();
        $sql_s_article = "SELECT s_article.id, s_article.article, s_article.price
            				FROM s_article 
            					WHERE s_article.s_cat_id='"._DB($myrow[0])."'
                                AND s_article.i_contr_id='"._DB($i_contr)."'
         ";
        $mt = microtime(true);
        $res_s_article = mysql_query($sql_s_article) or die(mysql_error().'<br/>'.$sql_s_article);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_article;$data_['_sql']['time'][]=$mt;
        for ($myrow_s_article = mysql_fetch_array($res_s_article); $myrow_s_article==true; $myrow_s_article = mysql_fetch_array($res_s_article))
        {
            $s_cat_arr2['a'][$myrow[0]][$myrow_s_article[0]]=$myrow_s_article[1];
            $s_cat_arr2['ap'][$myrow[0]][$myrow_s_article[0]]=$myrow_s_article[2];
        }
    }
    
    foreach($s_cat_arr2['ap'] as $s_cat_id => $s_aricle_arr2){
        foreach($s_aricle_arr2 as $s_article_id => $price_new){
            $price_old=0;
            if (isset($s_cat_arr['ap'][$s_cat_id]) and isset($s_cat_arr['ap'][$s_cat_id][$s_article_id])){
                $price_old=$s_cat_arr['ap'][$s_cat_id][$s_article_id];
            }
            
            if ($price_new!=$price_old){
                $data_['upp_n'][$s_cat_id]=$s_cat_arr2['n'][$s_cat_id];
                $data_['upp_p1'][$s_cat_id]=$price_old;
                $data_['upp_p2'][$s_cat_id]=$price_new;
            }
        }
    }
    
    unlink($targetDir . '/' .$fileName);
    
    $data_['all_edit']=$all_edit;
    echo json_encode($data_);
}
//*********************************************************************************************************

if ($_t=='s_article_price_convert'){
    $inc='s_article';
    $id=_GP('id');

    $targetDir = '../../upload/file/temp';
        if (!file_exists('../../upload')){mkdir('../../upload',0777);}
        if (!file_exists('../../upload/file')){mkdir('../../upload/file',0777);}
        if (!file_exists($targetDir)){mkdir($targetDir,0777);}
    $fileName='';


    
    // проверяем на пустоту
    if (!isset($_SESSION['a_admin'][$inc]['price_temp']) or $_SESSION['a_admin'][$inc]['price_temp']==''){
            
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
                
        $_SESSION['a_admin'][$inc]['price_temp']=$fileName;
    }else{
        $fileName=$_SESSION['a_admin'][$inc]['price_temp'];
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
        unset($_SESSION['a_admin'][$inc]['price_temp']);
        $data_=array();
        $data_['t']=array();
        
        //обрабатываем и выводим полученную таблицу
        $ext=preg_replace("/.*?\./", '', $fileName);
        
        //Чтение xls файла
        if ($ext=='xls' or $ext=='xlsx'){
            
            set_time_limit(1800);
            ini_set('memory_liit', '128M');
            /*	some vars	*/
            $chunkSize = 1000;		//размер считываемых строк за раз
            $startRow = 2;			//начинаем читать со строки 2, в PHPExcel первая строка имеет индекс 1, и как правило это строка заголовков
            $exit = false;			//флаг выхода
            $empty_value = 0;		//счетчик пустых знаений
            /*	some vars	*/
            if (!file_exists($targetDir . '/' .$fileName)) {
            	echo 'no file: '.$targetDir . '/' .$fileName;
                exit();
            }
            if (!file_exists('../class/PHPExcel.php')){echo 'Не найден файл ../class/PHPExcel.php';exit;}
            require_once '../class/PHPExcel.php';
            
            $objReader = PHPExcel_IOFactory::createReaderForFile($targetDir . '/' .$fileName);
            $objReader->setReadDataOnly(true);
          
          
            // PHPExcel
            class chunkReadFilter implements PHPExcel_Reader_IReadFilter 
            {
                private $_startRow = 0; 
                private $_endRow = 0; 
                /**  Set the list of rows that we want to read  */ 
                public function setRows($startRow, $chunkSize) { 
                    $this->_startRow    = $startRow; 
                    $this->_endRow      = $startRow + $chunkSize; 
                } 
            	public function readCell($column, $row, $worksheetName = '') { 
                    //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow 
                    if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) { 
                        return true; 
                    } 
                    return false; 
                } 
            }
            
            $chunkFilter = new chunkReadFilter(); 
            $objReader->setReadFilter($chunkFilter); 
            //внешний цикл, пока файл не кончится
            
            while ( !$exit ) 
            {
                
            	$chunkFilter->setRows($startRow,$chunkSize); 	//устанавливаем знаечние фильтра
            	$objPHPExcel = $objReader->load($targetDir . '/' .$fileName);		//открываем файл
            	$objPHPExcel->setActiveSheetIndex(0);		//устанавливаем индекс активной страницы
            	$objWorksheet = $objPHPExcel->getActiveSheet();	//делаем активной нужную страницу
            	for ($i = $startRow; $i < $startRow + $chunkSize; $i++) 	//внутренний цикл по строкам
            	{
                   for ($j=0; $j<20;$j++){
                        $value = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($j, $i)->getValue()));
                        $data_['t'][$i][$j]=$value;
                        
                        if (!isset($data_['cnt_'][$j])){$data_['cnt_'][$j]=0;}
                        if ($value!=''){$data_['cnt_'][$j]++;}
                   }
                   
                   if ($i>30){
                        $exit = true;
                        break;
                   }
            	}
                
                foreach($data_['cnt_'] as $j => $cnt_){
                    if ($cnt_==0){
                        foreach($data_['t'] as $i => $val){
                            unset($data_['t'][$i][$j]);
                        }
                    }
                }
            	$objPHPExcel->disconnectWorksheets(); 		//чистим 
            	unset($objPHPExcel); 						//память
            	$startRow += $chunkSize;					//переходим на следующий шаг цикла, увеличивая строку, с которой будем читать файл
            }

        }else{
            echo 'Данный тип файла не поддерживается';
            exit;
        }
        
        
        $data_['f']=$fileName;
        echo json_encode($data_);
        
        exit;
    }
    echo $fileName;

}

}
?>