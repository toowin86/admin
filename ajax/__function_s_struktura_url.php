<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php 
if (isset($_t)){
    if ($_t=='find'){
        $col_m[$myrow[0]]=$myrow[1];
    }
    if ($_t=='save'){
        
        
        $sql_url = "SELECT s_struktura.name, s_struktura.tip, s_struktura.url
        				FROM s_struktura
        					WHERE id='"._DB($nomer)."'
        	"; 
        
        $mt = microtime(true);
        $res_url = mysql_query($sql_url);if (!$res){echo $sql_url;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
        $myrow_url = mysql_fetch_array($res_url);
        $struktura_name=$myrow_url[0];
        $struktura_tip=$myrow_url[1];
        $struktura_url=$myrow_url[2];
        
        $url_=_GP('url');if (is_array($url_)){echo 'URL is array';exit();}
        if ($struktura_tip=='Ручное заполнение' or $struktura_tip=='Каталог' or $struktura_tip=='Функция'){
               $sql_url = "SELECT COUNT(*)
                				FROM s_struktura
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
                    				FROM s_struktura
                    					WHERE url='"._DB($url_)."'
                                        AND id!='"._DB($nomer)."'
                    	"; 
                    
                    $mt = microtime(true);
                    $res_url = mysql_query($sql_url);if (!$res_url){echo $sql_url;exit();}
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
                    $myrow_url = mysql_fetch_array($res_url);
                    $i_url++;
                }
        }
        
        
        $sql_url = "
        		UPDATE s_struktura 
        			SET  
        				url='"._DB($url_)."'
        		
        		WHERE id='"._DB($nomer)."'
        ";
       
        $mt = microtime(true);
        if(!mysql_query($sql_url)){echo $sql_url;}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
        
        
        
    }
    if ($_t=='change'){
        
        $sql_url = "SELECT id, pid, url
        				FROM s_struktura
        						ORDER BY sid
        ";
         
        $mt = microtime(true);
        $res_url = mysql_query($sql_url);if (!$res_url){echo $sql_url;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
        $struktura_arr['id']=array();
        $struktura_arr['pid']=array();
        $struktura_arr['url']=array();
        for ($myrow_url = mysql_fetch_array($res_url),$i_url=0; $myrow_url==true; $myrow_url = mysql_fetch_array($res_url),$i_url++)
        {
            $struktura_arr['id'][$i_url]=$myrow_url[0];
            $struktura_arr['pid'][$i_url]=$myrow_url[1];
            $struktura_arr['url'][$i_url]=$myrow_url[2];
        }
        
      
        //получение родителей
        $arr_par_id=array();
        if (isset($struktura_arr['id'])){
          $arr_par_id=get_parent_id_array($nomer,$struktura_arr['id'],$struktura_arr['pid']);   
        }
        foreach ($arr_par_id as $key_ => $id_){
            $key_url = array_search($id_, $struktura_arr['id']);
            $data_['_d']['parents_url'][]=$struktura_arr['url'][$key_url];
        }
        
        $col_m[$i]='url';
        if ($SQL_COL!=''){$SQL_COL.=', ';}
        $SQL_COL.="`s_struktura`.`url`";
        
    }
    if ($_t=='copy'){
        
        $sql_url = "SELECT id, pid, url
        				FROM s_struktura
        						ORDER BY sid
        ";
         
        $mt = microtime(true);
        $res_url = mysql_query($sql_url);if (!$res_url){echo $sql_url;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_url;$data_['_sql']['time'][]=$mt;
        $struktura_arr['id']=array();
        $struktura_arr['pid']=array();
        $struktura_arr['url']=array();
        for ($myrow_url = mysql_fetch_array($res_url),$i_url=0; $myrow_url==true; $myrow_url = mysql_fetch_array($res_url),$i_url++)
        {
            $struktura_arr['id'][$i_url]=$myrow_url[0];
            $struktura_arr['pid'][$i_url]=$myrow_url[1];
            $struktura_arr['url'][$i_url]=$myrow_url[2];
        }
        
      
        //получение родителей
        $arr_par_id=array();
        if (isset($struktura_arr['id'])){
          $arr_par_id=get_parent_id_array($nomer,$struktura_arr['id'],$struktura_arr['pid']);   
        }
        foreach ($arr_par_id as $key_ => $id_){
            $key_url = array_search($id_, $struktura_arr['id']);
            $data_['_d']['parents_url'][]=$struktura_arr['url'][$key_url];
        }
        
        $col_m[$i]='url';
        if ($SQL_COL!=''){$SQL_COL.=', ';}
        $SQL_COL.="`s_struktura`.`url`";
        
    }
    // ******************** ИМПОРТ *******************************
    elseif ($_t=='paste'){ 
        
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