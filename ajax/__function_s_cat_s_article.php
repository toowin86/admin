<?php 
    //Артиклы, цены и количество от поставщиков для товаров в каталоге
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php 
if (isset($_t)){
    
    //****************************************************************************************************************************************
    //ПОИСК АРТИКУЛОВ
    //****************************************************************************************************************************************
    if ($_t=='find'){

        if ($find_tip=='fillter'){
            if (_GP('s_article_find_text')!=''){
              if ($WHERE!='') {$WHERE.=' AND ';}
              $WHERE.=" s_cat.id IN (SELECT s_article.s_cat_id FROM s_article WHERE s_article.article LIKE '%"._DB(_GP('s_article_find_text'))."%')";
            }
        }
        if ($find_tip=='sql'){
            
            $col_m[$i]="(SELECT IF(COUNT(*)>0,GROUP_CONCAT(CONCAT(`i_contr`.`name`,'::',`s_article`.`article`,'::',`s_article`.`price`,'::',`s_article`.`id`) SEPARATOR '||'),'') 
                            FROM `s_article`, `i_contr` 
                                WHERE `s_article`.`s_cat_id`=`s_cat`.`id` 
                                    AND `s_article`.`i_contr_id`=`i_contr`.`id` 
                                    ) AS s_article ";
           
        }
    }
    //****************************************************************************************************************************************
    //УДАЛЕНИЕ АРТИКУЛОВ
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
        			FROM s_article
        				WHERE s_cat_id $nomer_
        ";
        $mt = microtime(true);
        $res_prop = mysql_query($sql_del_prop); if (!$res_prop){echo $sql_del_prop;exit();}
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql_del_prop;$data_['_sql']['time'][]=$mt;
        
    }
    //****************************************************************************************************************************************
    //ИЗМЕНЕНИЕ АРТИКУЛОВ
    //****************************************************************************************************************************************
    elseif ($_t=='change'){
        
       $data_['_d'][$data_['col'][$i]]=array();
        
       $sql_s_article = "SELECT     (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE s_article.i_contr_id=i_contr.id) AS i_contr_name,
                                    s_article.i_contr_id,
                                    s_article.article,
                                    s_article.kolvo,
                                    s_article.price,
                                    (SELECT IF(COUNT(*)>0,i_contr.nakrutka,'') FROM i_contr WHERE s_article.i_contr_id=i_contr.id) AS i_contr_nakrutka
                        
           				FROM s_article 
        				    WHERE s_article.s_cat_id='"._DB($nomer)."'
        ";
       $mt = microtime(true);
       $res_s_article = mysql_query($sql_s_article) or die(mysql_error().'<br/>'.$sql_s_article);
       $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_article;$data_['_sql']['time'][]=$mt;
       for ($myrow_s_article = mysql_fetch_array($res_s_article),$ii=0;$myrow_s_article==true; $myrow_s_article = mysql_fetch_array($res_s_article),$ii++)
       {
            $data_['_d'][$data_['col'][$i]][$ii][0]=$myrow_s_article[0];
            $data_['_d'][$data_['col'][$i]][$ii][1]=$myrow_s_article[1];
            $data_['_d'][$data_['col'][$i]][$ii][2]=$myrow_s_article[2];
            $data_['_d'][$data_['col'][$i]][$ii][3]=$myrow_s_article[3];
            $data_['_d'][$data_['col'][$i]][$ii][4]=$myrow_s_article[4];
            $data_['_d'][$data_['col'][$i]][$ii][5]=$myrow_s_article[5];
       }
       
    }
    elseif ($_t=='copy'){
        
       $data_['_d'][$data_['col'][$i]][$nomer]=array();
        
       $sql_s_article = "SELECT     (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE s_article.i_contr_id=i_contr.id) AS i_contr_name,
                                    s_article.i_contr_id,
                                    s_article.article,
                                    s_article.kolvo,
                                    s_article.price,
                                    (SELECT IF(COUNT(*)>0,i_contr.nakrutka,'') FROM i_contr WHERE s_article.i_contr_id=i_contr.id) AS i_contr_nakrutka
                        
           				FROM s_article 
        				    WHERE s_article.s_cat_id='"._DB($nomer)."'
        ";
       $mt = microtime(true);
       $res_s_article = mysql_query($sql_s_article) or die(mysql_error().'<br/>'.$sql_s_article);
       $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_article;$data_['_sql']['time'][]=$mt;
       for ($myrow_s_article = mysql_fetch_array($res_s_article),$ii=0;$myrow_s_article==true; $myrow_s_article = mysql_fetch_array($res_s_article),$ii++)
       {
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][0]=$myrow_s_article[0];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][1]=$myrow_s_article[1];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][2]=$myrow_s_article[2];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][3]=$myrow_s_article[3];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][4]=$myrow_s_article[4];
            $data_['_d'][$data_['col'][$i]][$nomer][$ii][5]=$myrow_s_article[5];
       }
       
    }
    
    // ******************** ИМПОРТ *******************************
    elseif ($_t=='paste'){ 
        $sql_del = "DELETE 
        			FROM s_article 
        				    WHERE s_article.s_cat_id='"._DB($id)."'
        ";
        $res_del = mysql_query($sql_del) or die(mysql_error().'<br>'.$sql_del);
        
        $val_arr=array();
        if (isJSON($col_val_arr[$key_col])==true){
            $val_arr=json_decode($col_val_arr[$key_col]);
        }else{
            $val_arr[0]=$col_val_arr[$key_col];
        }
        if (count($val_arr)>0){
            foreach($val_arr as $s_article_key=> $s_article_arr){
                if (isset($s_article_arr[0]) and isset($s_article_arr[1]) and $s_article_arr[0]!='' and $s_article_arr[1]!=''){
                    $i_contr_name=$s_article_arr[0];
                    $i_contr_id=$s_article_arr[1];
                    $article=$s_article_arr[2];
                    $kolvo=$s_article_arr[3];
                    $price=$s_article_arr[4];
                    $nakrutka=$s_article_arr[5];
                    
                    //Проверяем на наличие данного контр-агента
                    $sql="SELECT 
                                COUNT(*) 
                                    FROM i_contr
                                    WHERE i_contr.id='"._DB($i_contr_id)."'
                                    ";
                    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                    $myrow = mysql_fetch_array($res);
                    if ($myrow[0]==0){
                        $sql_ins = "INSERT into i_contr (
                        				id,
                        				name,
                                        nakrutka
                        			) VALUES (
                        				'"._DB($i_contr_id)."',
                        				'"._DB($i_contr_name)."',
                                        '"._DB($nakrutka)."'
                        )";
                        
                        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    }else{
                        $sql_upp = "UPDATE i_contr 
                        			SET  
                        				nakrutka='"._DB($nakrutka)."'
                        		
                        		WHERE id='"._DB($i_contr_id)."'
                        ";
                        $res = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
                    }
                    
                    //Добавляем данные о ценах в базу
                    $sql_ins = "INSERT into s_article (
                    				i_contr_id,
                    				s_cat_id,
                                    article,
                                    kolvo,
                                    price
                    			) VALUES (
                    				'"._DB($i_contr_id)."',
                    				'"._DB($id)."',
                    				'"._DB($article)."',
                    				'"._DB($kolvo)."',
                    				'"._DB($price)."'
                    )";
                    
                    $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
                    
                }
            }
        }
        //print_rf($val_arr);
    }
    //****************************************************************************************************************************************
    //СОХРАНЕНИЕ АРТИКУЛОВ
    //****************************************************************************************************************************************
    elseif ($_t=='save'){
       
       $i_contr_id_arr=_GP('__function_s_cat_s_article_add_i_contr_select');
       $article_arr=_GP('__function_s_cat_s_article_article');
       $price_arr=_GP('__function_s_cat_s_article_price');
       $kolvo_arr=_GP('__function_s_cat_s_article_kolvo');
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
        
        
        $sql = "DELETE 
        			FROM s_article 
        				WHERE s_article.s_cat_id IN ('".implode("','",$nomer_arr)."')
        ";
        //echo $sql;exit;
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        foreach($i_contr_id_arr as $key => $i_contr_id){
            $price=str_replace(' ','',$price_arr[$key]);
            $kolvo=str_replace(' ','',$kolvo_arr[$key]);
            
            if ($article_arr[$key]!='' and $i_contr_id!=''){
                foreach ($nomer_arr as $kkey => $nomer_){
                    $sql = "INSERT into s_article (
                    				s_cat_id,
                    				i_contr_id,
                                    article,
                                    kolvo,
                                    price
                    			) VALUES (
                    				'"._DB($nomer_)."',
                    				'"._DB($i_contr_id)."',
                                    '"._DB($article_arr[$key])."',
                                    '"._DB($kolvo)."',
                                    '"._DB($price)."'
                    )";
                    
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                }
            }
        }
        
    }
    

    
    
    //****************************************************************************************************************************************
    //ПАРСИНГ АРТИКУЛОВ
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
        $col_m[$ii]=$data_['col'][$key_col];
        
        //вывод названия столбца
        if ($script_opt5=='1'){//пустое поле
            if ($txt_menu!=''){$txt_menu.=$script_opt1;}
            $txt_menu.=  $script_opt2.str_replace('"',$script_opt3.'"',$data_['col_ru'][$key_col]).$script_opt2;
        }
    }
}
?>