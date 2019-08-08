<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<div class="google_block">
<?php
    
    

    if ($err_google!=''){echo $err_google;}
    else{
     //Если есть связь с google drive
     if ((isset($_SESSION['google_drive_access_token']) && $_SESSION['google_drive_access_token'])) {
        $access_token = $_SESSION['google_drive_access_token']['access_token'];
        
        $cnt_upload=0;//количество выгруженных
        $cnt_ins=0;//количество добавленных
        $cnt_upp=0;//количество измененных
        
        $url = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json&v=3.0&oauth_token='.$access_token;
        $xmlresponse =  curl($url);
        $contacts = json_decode($xmlresponse,true);
    //print_rf($contacts);exit;
        
        

        //получаем список контактов
    	$google_contacts = array();
    	$google_contacts['name'] = array();
    	$google_contacts['email'] = array();
    	$google_contacts['phone'] = array();
    	$google_contacts['etag'] = array();
    	$google_contacts['link'] = array();
        
        $SQL_TXT='';
        $i=0;
    	if (!empty($contacts['feed']['entry'])) {
    		foreach($contacts['feed']['entry'] as $contact) {
    		   $cnt_upload++;
    		   $name=@$contact['title']['$t'];
    		   $etag=@$contact['gd$etag'];
    		   $link=@$contact['link'][1]['href'];
    		   $email='';
               if (isset($contact['gd$email']) and isset($contact['gd$email'][0]) and isset($contact['gd$email'][0]['address'])){
                    $email=@$contact['gd$email'][0]['address'];
               }
               $phone='';
               if (isset($contact['gd$phoneNumber']) and isset($contact['gd$phoneNumber'][0]) and isset($contact['gd$phoneNumber'][0]['$t'])){
                    $phone=str_replace('+7','8',@$contact['gd$phoneNumber'][0]['$t']);
                    $phone=preg_replace('/[\D]{1,}/s', '',$phone);
               }
               
               
                
               //Тип авторизации - Телефон
               if(isset($_SESSION['a_options']['Регистрация: email-0/sms-1']) and $_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){
                    if ($phone!=''){
                        $SQL_TXT.=", '"._DB($phone)."'";
                    }
                    
               }else{//Тип авторизации - email
                    if ($email!=''){
                        $SQL_TXT.=", '"._DB($email)."'";
                    }
               }
               
    			$google_contacts['etag'][$i]=$etag;
    			$google_contacts['link'][$i]=$link;
    			$google_contacts['name'][$i]=$name;
    			$google_contacts['email'][$i]=$email;
    			$google_contacts['phone'][$i]=$phone;
    				
                $i++;
    		}
    	}
        
        
    //Загрузить контакты на сайт с Google аккаунта
    if (isset($_REQUEST['contacts_to_site'])){
        
        
        $SQL_UPP_NAME='';
        $SQL_UPP_ID='';
        $SQL_UPP_PHONE='';
        $SQL_UPP_EMAIL='';
        //echo $_SESSION['a_options']['Регистрация: email-0/sms-1'];exit;
        //Тип авторизации - Телефон
        if(isset($_SESSION['a_options']['Регистрация: email-0/sms-1']) and $_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){
           $sql_main="SELECT i_contr.id, i_contr.phone
                            FROM i_contr
                                WHERE i_contr.phone IN  ('*' $SQL_TXT)
                                    
                        ";
            $res = mysql_query($sql_main) or die(mysql_error().'<br />'.$sql_main);

            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                if (in_array($myrow['phone'],$google_contacts['phone'])){
                    $key=array_search($myrow['phone'],$google_contacts['phone']);
                    
                    $SQL_UPP_NAME.="\n\r WHEN id = ".$myrow['id']." THEN '"._DB($google_contacts['name'][$key])."'";
                    $SQL_UPP_EMAIL.="\n\r WHEN id = ".$myrow['id']." THEN '"._DB($google_contacts['email'][$key])."'";
                    if ($SQL_UPP_ID!=""){$SQL_UPP_ID.=", ";} $SQL_UPP_ID.="'"._DB($myrow['id'])."'";
                    $cnt_upp++;
                    unset($google_contacts['name'][$key],$google_contacts['email'][$key],$google_contacts['phone'][$key]);
                }
            }
        }else{//Тип авторизации - email
            $sql_main="SELECT i_contr.id, i_contr.email
                            FROM i_contr
                                WHERE i_contr.email IN  ('*' $SQL_TXT)
                                    
                        ";
            $res = mysql_query($sql_main) or die(mysql_error().'<br />'.$sql_main);
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                if (in_array($myrow['email'],$google_contacts['email'])){
                    $key=array_search($myrow['email'],$google_contacts['email']);
                    
                    $SQL_UPP_NAME.="\n\r WHEN id = ".$myrow['id']." THEN '"._DB($google_contacts['name'][$key])."'";
                    $SQL_UPP_PHONE.="\n\r WHEN id = ".$myrow['id']." THEN '"._DB($google_contacts['phone'][$key])."'";
                    if ($SQL_UPP_ID!=""){$SQL_UPP_ID.=", ";} $SQL_UPP_ID.="'"._DB($myrow['id'])."'";
                    $cnt_upp++;
                    unset($google_contacts['name'][$key],$google_contacts['email'][$key],$google_contacts['phone'][$key]);
                }
            }
        }
       
        if ($SQL_UPP_ID!=''){
            if(isset($_SESSION['a_options']['Регистрация: email-0/sms-1']) and $_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){
               $sql="UPDATE `i_contr` SET `name` = CASE
                    $SQL_UPP_NAME
                    END ,
                `email` = CASE
                    $SQL_UPP_EMAIL
                    END 
                WHERE id IN ($SQL_UPP_ID)";
            }else{//Тип авторизации - email
                $sql="UPDATE `i_contr` SET `name` = CASE
                    $SQL_UPP_NAME
                    END ,
                `phone` = CASE
                    $SQL_UPP_PHONE
                    END 
                WHERE id IN ($SQL_UPP_ID)";
            }
            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        }
        
        $SQL_INSERT='';
        foreach($google_contacts['name'] as $key => $name){
            if ($SQL_INSERT!=''){$SQL_INSERT.=', ';}
            $SQL_INSERT.="('"._DB($name)."','"._DB($google_contacts['email'][$key])."','"._DB($google_contacts['phone'][$key])."')";
            $cnt_ins++;
        }
        if ($SQL_INSERT!=''){
            $sql_ins = "INSERT into `i_contr` (
            				name,
            				email,
                            phone
            			) VALUES $SQL_INSERT";
            $res_ins = mysql_query($sql_ins) or die(mysql_error().'<br />'.$sql_ins);
            
        }
        echo '<p>Выгружено с Google: '.$cnt_upload.' контакт'.end_word($cnt_upload,'ов','','а').'</p>';
        echo '<p>Добавлено на сайт: '.$cnt_ins.' контакт'.end_word($cnt_ins,'ов','','а').'</p>';
        echo '<p>Изменено на сайте: '.$cnt_upp.' контакт'.end_word($cnt_upp,'ов','','а').'</p>';
    }
    //Выгрузить контакты с сайта на Google аккаунт
    if (isset($_REQUEST['contacts_to_google'])){
        //ajax загрузка
    }
        
        
        
         $sql = "SELECT  COUNT(*)
                            FROM i_contr 
            					WHERE i_contr.chk_active='1'
                                
            ";
            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
            $myrow = mysql_fetch_array($res);
        ?>
        <div>
            <a href="?inc=start_menu&contacts_to_site"><i class="fa fa-user-plus"></i>Загрузить контакты на сайт с Google аккаунта <?=@$_SESSION['a_options']['Google Drive - email google для синхронизации контактов и диска'];?></a>
            <a class="contacts_to_google" href="?inc=start_menu&contacts_to_google" data-cur="0" data-ins="0" data-upp="0" data-cnt="<?=$myrow[0];?>"><i class="fa fa-google-plus"></i>Выгрузить контакты с сайта на Google аккаунт <?=@$_SESSION['a_options']['Google Drive - email google для синхронизации контактов и диска'];?></a>
            <a href="?inc=start_menu&exit_google"><i class="fa fa-sign-out"></i>Отвязать Google аккаунт</a>
        </div>
        <?php
     }
     else{
        ?>
        <div>
            <a href="?inc=start_menu&google_drive_connect"><i class="fa fa-google" title="Подключить Google аккаунт для синхронизации контактов">  <span>Подключить Google аккаунт</span></a></i>
        </div>
        <?php
     }
}
?>
</div>
<div class="clear"></div>

<div class="start_menu_first_div">
<?php
    
    //Проверяем контакты
    $sql = "SELECT COUNT(*)
    				FROM i_contr 
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $i_contr_cnt=$myrow[0];
    
    if ($i_contr_cnt>0){
        ?>
        <div class="start_menu_i_contr">
            <!-- -->
              <div class="start_menu_i_contr_struktura">
                <div class="start_menu_i_contr_struktura_div">
                    <h2>Структура клиентов</h2>
                    <i class="fa fa-refresh" title="Обновить"></i>
                    <?php
                    
      
                    
                    ?>
                    <div class="start_menu_load" id="i_contr_struktura" data-tip="">
                    <?php
                    
                    ?>
                    </div>
                </div>
            </div>
        
        
            <!-- -->
              <div class="start_menu_i_contr_top">
                <div class="start_menu_i_contr_top_div">
                    <h2>ТОП клиентов</h2>
                    <i class="fa fa-refresh" title="Обновить"></i>
                    <div class="start_menu_load" id="i_contr_top" data-tip="">
                    <?php
                    
                    ?>
                    </div>
                </div>
            </div>
        
        </div>
        <?php
    }
    
    
    
    //Проверяем заказы
    $sql = "SELECT COUNT(*)
    				FROM m_zakaz 
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_zakaz_cnt=$myrow[0];
    
    if ($m_zakaz_cnt>0){
        ?>
        <div class="start_menu_m_zakaz">
        <?php
        
        ?>
         <?php
        $sql = "SELECT COUNT(m_zakaz.id)
    				FROM m_zakaz, m_zakaz_s_cat,s_cat
                    WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id
                    AND m_zakaz_s_cat.s_cat_id=s_cat.id
                    AND s_cat.tip='Услуга'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $m_zakaz_work_cnt=$myrow[0];
        $m_zakaz_txt='';
        $m_zakaz_data='';
        if ($m_zakaz_work_cnt>0){
            $m_zakaz_txt='работам';
            $m_zakaz_data='work';
        }
        
        $sql = "SELECT COUNT(m_zakaz.id)
    				FROM m_zakaz, m_zakaz_s_cat,s_cat
                    WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id
                    AND m_zakaz_s_cat.s_cat_id=s_cat.id
                    AND s_cat.tip='Товар'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $m_zakaz_item_cnt=$myrow[0];
        if ($m_zakaz_work_cnt>0){
            if ($m_zakaz_txt!=''){$m_zakaz_txt='товарам и '.$m_zakaz_txt;}
            if ($m_zakaz_data!=''){$m_zakaz_data='items,'.$m_zakaz_data;}

            
        }
         ?>
         
        <div class="start_menu_m_zakaz_dohod">
            <div class="start_menu_m_zakaz_dohod_div">
                <h2>Доход по <?=$m_zakaz_txt;?></h2>
                <i class="fa fa-refresh" title="Обновить"></i>
                <div class="start_menu_load" id="m_zakaz_dohod" data-tip="<?=$m_zakaz_data;?>">
                <?php
                
                ?>
                </div>
            </div>
        </div>
        
        
        <!-- Последние заказы в работе -->
        <div class="start_menu_m_zakaz_work">
            <div class="start_menu_m_zakaz_work_div">
                <h2>Последние заказы в работе <a href="?inc=m_zakaz">все заказы</a></h2>
                <i class="fa fa-refresh" title="Обновить"></i>
                <div class="start_menu_load" id="m_zakaz_work" data-tip="in_work">
                <?php
               
                ?>
                </div>
            </div>
        </div>
        <?php
        ?>
        </div>
        
        
        
        <div class="start_menu_m_zakaz">
        
            <!-- Всего заказов -->
            <div class="start_menu_m_zakaz_cnt">
                <div class="start_menu_m_zakaz_middle_check_div">
                    <h2>Всего заказов</h2>
                    <i class="fa fa-refresh" title="Обновить"></i>
                    <div class="start_menu_load" id="m_zakaz_cnt" data-tip="cnt">
                    <?php
                   
                    ?>
                    </div>
                </div>
            </div>
            <!-- Средний чек -->
            <div class="start_menu_m_zakaz_middle_check">
                <div class="start_menu_m_zakaz_middle_check_div">
                    <h2>Средний чек</h2>
                    <i class="fa fa-refresh" title="Обновить"></i>
                    <div class="start_menu_load" id="m_zakaz_middle_check" data-tip="middle_check">
                    <?php
                   
                    ?>
                    </div>
                </div>
            </div>
        
        </div>
        
        <?php
    }
    
    
    //Проверяем платежи
    $sql = "SELECT COUNT(*)
    				FROM m_platezi
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_platezi_cnt=$myrow[0];
    
    if ($m_platezi_cnt>0){
        ?>
        <div class="start_menu_m_platezi">
        <?php
        
        ?>
        <div class="start_menu_m_platezi_in_out">
            <div class="start_menu_s_cat_last_add_div">
                <h2>Доходы расходы <a href="?inc=m_platezi">все платежи</a></h2>
                <i class="fa fa-refresh" title="Обновить"></i>
                <div class="start_menu_load" id="m_platezi_in_out" data-tip="">
                <?php
               
                ?>
                </div>
            </div>
        </div>
        <?php
         ?>
         
        <div class="start_menu_m_platezi_rashod">
            <div class="start_menu_s_cat_pop_add_div">
                <h2>Структура расходов <a href="?inc=i_rashodi">расходы</a></h2>
                <i class="fa fa-refresh" title="Обновить"></i>
                <div class="start_menu_load" id="m_platezi_rashod" data-tip="">
                <?php
                
                ?>
                </div>
            </div>
        </div>
        <?php
        ?>
        </div>
        <?php
    }
    
    //Проверяем товары
    $sql = "SELECT COUNT(*)
    				FROM s_cat 
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $s_cat_cnt=$myrow[0];
    
    if ($s_cat_cnt>0){
        ?>
        <div class="start_menu_s_cat">
        <?php
        
        ?>
        <div class="start_menu_s_cat_last_add">
            <div class="start_menu_s_cat_last_add_div">
                <h2>Последние добавленные товары/услуги в каталог <a href="?inc=s_cat">каталог</a></h2>
                <i class="fa fa-refresh" title="Обновить"></i>
                <div class="start_menu_load" id="s_cat_add" data-tip="last">
                <?php
               
                ?>
                </div>
            </div>
        </div>
        <?php
         ?>
         
        <div class="start_menu_s_cat_pop_add">
            <div class="start_menu_s_cat_pop_add_div">
                <h2>Популярные товары за последний месяц <a href="?inc=s_cat">каталог</a></h2>
                <i class="fa fa-refresh" title="Обновить"></i>
                <div class="start_menu_load" id="s_cat_add" data-tip="pop">
                <?php
                
                ?>
                </div>
            </div>
        </div>
        <?php
        ?>
        </div>
        <?php
    }
    
?>
</div>