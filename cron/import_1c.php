<?php
//http://licota24.ru/admin/cron/import_1c.php?pass=Kfgw9w2M2p644Ls
header('Content-type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache');
include "../db.php";
include "../functions.php";
$pass=_GP('pass');
$mess_admin='';
$data_=array();




if ($pass=='Kfgw9w2M2p644Ls')
{
    $s_cat_old=array();
    //Получаем массив текущих товаров
    $sql = "SELECT s_cat.id, s_cat.name
    				FROM s_cat
    ";
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $s_cat_old[$myrow[0]]=$myrow[1];
    }
    
    //Чтение файлов 1С
    $file='../../_download/1c.csv';
    if (file_exists($file)){
        // ОТкрываем файл
        $fp = fopen($file, "r"); 
        if ($fp) 
        {
            $i=0;
            $data_['code']=array();
            $data_['mini_desc']=array();
            $data_['s_struktura_name']=array();
            $data_['name']=array();
            $data_['html_code']=array();
            $data_['photo']=array();
            $data_['kolvo']=array();
            $data_['price']=array();
            
//"Код";"Наименование";"Родитель";"Артикул";"Полное наименование";"Основное изображение";"Остаток";"Цена"
            
            while (!feof($fp)){
                $cat = fgets($fp);
                //$cat = iconv('cp1251','utf-8',fgets($fp));
                $cat=str_replace('\;','@@',$cat);
                $cat=str_replace(';','$$',$cat);
                $cat=str_replace('@@',';',$cat);
                
                list($data_['code'][$i],$data_['mini_desc'][$i],$data_['s_struktura_name'][$i],$data_['name'][$i],$data_['html_code'][$i],$data_['photo'][$i],$data_['kolvo'][$i],$data_['price'][$i]) = explode('$$',$cat);
                $data_['price'][$i]=str_replace(' ','',$data_['price'][$i]);
                $i++;
            }
            
            print_rf($data_);exit;
            
            $SQL_INS="";$SQL_UPP_ID="";$SQL_UPP_KOL="";$SQL_UPP_PRICE="";$kol_ins=0;$kol_upp=0;
            foreach($data_['name'] as $i => $name){
                if (in_array($name,$s_cat_old)){
                    $s_cat_id=array_search($name,$s_cat_old);//Товар есть - меняем цену и количество
                    if ($s_cat_id>0){
                        
                        if($SQL_UPP_ID!=''){$SQL_UPP_ID.=",";}
                        $SQL_UPP_ID.="'"._DB($s_cat_id)."'";
                        
                        $SQL_UPP_PRICE.=" WHEN id = '"._DB($s_cat_id)."' THEN '"._DB($data_['price'][$i])."'";
                        $SQL_UPP_KOL.=" WHEN id = '"._DB($s_cat_id)."' THEN '"._DB($data_['kolvo'][$i])."'";
                        $kol_upp++;
                        
                        if (strlen($SQL_UPP_PRICE)>5000){
                            // КАТАЛОГ
                            $sql = "UPDATE `s_cat` SET `price` = CASE
                                        $SQL_UPP_PRICE
                                        END ,
                                    `kolvo` = CASE
                                        $SQL_UPP_KOL
                                        END 
                                    WHERE id IN ($SQL_UPP_ID)
                            ";
                            //echo $sql;
                            echo $sql;
                            if(!mysql_query($sql)){echo $sql;exit;}
                            $SQL_UPP_ID="";$SQL_UPP_KOL="";$SQL_UPP_PRICE="";
                        }
                    }else{
                        $mess_admin.='<p>Не определен id. $name='.$name.'</p>';
                    }
                }else{//Товара не было - добавляем в базу
                    
                    $kol_ins++;
                    if ($SQL_INS!=''){$SQL_INS.=",";}
                    $SQL_INS.=" (
                    				'0',
                    				'"._DB($data_['name'][$i])."',
                                    '"._DB($data_['price'][$i])."',
                                    '"._DB($data_['mini_desc'][$i])."',
                                    '"._DB($data_['html_code'][$i])."',
                                    '".date('Y-m-d H:i:s')."',
                                    '"._DB($data_['kolvo'][$i])."'
                                    
                    )";
                    
                   
                    
                    if (strlen($SQL_INS)>10000){
                        $sql = "INSERT into s_cat (
                                    chk_active,
                    				name,
                    				price,
                                    mini_desc,
                                    html_code,
                                    data_change,
                                    kolvo
                                    
                    			) VALUES $SQL_INS";
                        
                        echo $sql;
                        $res = mysql_query($sql);	if (!$res){echo $sql;exit();}
                         $SQL_INS='';
                    }
                }
            }
            
            //Массовое обновление
            if ($SQL_UPP_ID!=''){
                // КАТАЛОГ
                $sql = "UPDATE `s_cat` SET `price` = CASE
                            $SQL_UPP_PRICE
                            END ,
                        `kolvo` = CASE
                            $SQL_UPP_KOL
                            END 
                        WHERE id IN ($SQL_UPP_ID)
                ";
                echo $sql;
                if(!mysql_query($sql)){echo $sql;exit;}
               // echo $sql;
                $SQL_UPP_ID="";$SQL_UPP_KOL="";$SQL_UPP_PRICE="";
            }
            
            //Массовое добавление
            if ($SQL_INS!=''){
                $sql = "INSERT into s_cat (
                            chk_active,
            				name,
            				price,
                            mini_desc,
                            html_code,
                            data_change,
                            kolvo
                            
            			) VALUES $SQL_INS";
                echo $sql;
                $res = mysql_query($sql);	if (!$res){echo $sql;exit();}
            }
            if ($kol_ins>0){
                $mess_admin.='<p>Добавлено в базу '.$kol_ins.' запись(ей)</p>';
            }
            if ($kol_upp>0){
                $mess_admin.='<p>Обновлено в базе '.$kol_upp.' запись(ей)</p>';
            }
        }
    }else{
        $mess_admin.='<p>Не найден файл /_download/1c.csv</p>';
    }
    
}else{
    $mess_admin.='<p>Не верно указан пароль!</p>';
}
//***************************************************************************
//****************** ОТПРАВКА СООБЩЕНИЙ *************************************
//***************************************************************************
if($mess_admin!=''){
    echo $mess_admin;
    $sql = "SELECT a_options.name, a_options.val
        				FROM a_options
        "; 
        $res = mysql_query($sql) or die(mysql_error());
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            if ($myrow[0]=='SMTP: сервер'){$config['smtp_host'] =$myrow[1];}
            if ($myrow[0]=='SMTP: password'){$config['smtp_password'] =$myrow[1];}
            if ($myrow[0]=='SMTP: login'){$config['smtp_login'] =$myrow[1];}
            if ($myrow[0]=='SMTP: порт'){$config['smtp_port'] =$myrow[1];}
            if ($myrow[0]=='email администратора'){$_SESSION['a_options']['email администратора'] =$myrow[1];}
        }
        $config['smtp_charset']  = 'UTF-8';
        $config['smtp_debug']    = true;
        $config['smtp_from'] = $config['smtp_login'];
        $config['smtp_username'] = 'ya.yarsis@yandex.ru';
        
        if(!send_mail_smtp(
            $_SESSION['a_options']['email администратора'],
            '1С конвертация',
            $mess_admin, 
            'Администратору', 
            '',
            '1С конвертация',
            '3',
            array(),
            array(),
            '0',
            '0',
            $config
        )){
                print_rf($config);
            }
    }
?>