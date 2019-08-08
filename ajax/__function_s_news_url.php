<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php

    
if (isset($_t)){
    if ($_t=='find'){
        $col_m[$myrow[0]]=$myrow[1];
    }
    if ($_t=='save'){
        
        
        $sql_url = "SELECT s_news.name, s_news.url
        				FROM s_news
        					WHERE id='"._DB($nomer)."'
        	"; 
       
        $mt = microtime(true);
        $res_url = mysql_query($sql_url);if (!$res_url){echo $sql_url;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
        $myrow_url = mysql_fetch_array($res_url);
        $s_news_name=$myrow_url[0];
        $s_news_url=$myrow_url[1];

        $url_=_GP('url');if (is_array($url_)){echo 'URL is array';exit();}
      
               $sql_url = "SELECT COUNT(*)
                				FROM s_news
                					WHERE url='"._DB($url_)."'
                                    AND id!='"._DB($nomer)."'
                	"; 
                
                $mt = microtime(true);
                $res_url = mysql_query($sql_url);if (!$res_url){echo $sql_url;exit();}
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
                $myrow_url = mysql_fetch_array($res_url);
                $i_url=0;
                while($myrow_url[0]>0){
                    $url_=$url_.'_'.$i_url;
                    $sql_url = "SELECT COUNT(*)
                    				FROM s_news
                    					WHERE url='"._DB($url_)."'
                                        AND id!='"._DB($nomer)."'
                    	"; 
                    
                    $mt = microtime(true);
                    $res_url = mysql_query($sql_url);if (!$res){echo $sql_url;exit();}
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
                    $myrow_url = mysql_fetch_array($res_url);
                    $i_url++;
                }

        $sql_url = "
        		UPDATE s_news
        			SET  
        				url='"._DB($url_)."'
        		
        		WHERE id='"._DB($nomer)."'
        ";
        $mt = microtime(true);
        if(!mysql_query($sql_url)){echo $sql_url;}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
        
        
        
    }
    if ($_t=='change'){
        
        $col_m[$i]='url';
        if ($SQL_COL!=''){$SQL_COL.=', ';}
        $SQL_COL.="`s_news`.`url`";
        
    }
    if ($_t=='copy'){
        
        $col_m[$i]='url';
        if ($SQL_COL!=''){$SQL_COL.=', ';}
        $SQL_COL.="`s_news`.`url`";
        
    }
    
    // ******************** ИМПОРТ *******************************
    if ($_t=='paste'){ 
        
        $return_sql['SQL_UPP'][$col_].=" WHEN id = "._DB($id)." THEN '"._DB($col_val_arr[$key_col])."'";
                            
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