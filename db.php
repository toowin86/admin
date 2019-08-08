<?php
//Конфигурация базы данных
//*** МЕНЯЕМ КОДИРОВАННЫЕ НА РУСКОЯЗЫЧНЫЕ ДОМЕНЫ
$mt_db = microtime(true);
require_once('class/idna_convert.class.php'); 
$IDN = new idna_convert();
$_SERVER["HTTP_HOST"] = $IDN->decode($_SERVER["HTTP_HOST"]);
$_SERVER['SERVER_NAME'] = $IDN->decode($_SERVER['SERVER_NAME']);
// ***************************************************************
if ($_SERVER['SERVER_NAME']=='localhost'){preg_match('/^[\/]{1,}[\.0-9a-zA-Z_-]{1,}/si', $_SERVER['REQUEST_URI'],$adm);$_SERVER['SERVER_NAME']=$_SERVER['SERVER_NAME'].$adm[0];}
if (!isset($_SERVER['REQUEST_SCHEME'])){$_SERVER['REQUEST_SCHEME']='http';}

ini_set('session.gc_maxlifetime', 25920000);
ini_set('session.cookie_lifetime', 25920000);

$dir_ses=$_SERVER['DOCUMENT_ROOT'].'/adm_ses';

if (!file_exists($dir_ses)){mkdir($dir_ses);}
ini_set('session.save_path', $dir_ses);


session_start(); 					#Старт сессии
session_name($_SERVER["HTTP_HOST"].'_admin');
//


//************************************************************************

$base_name="u0372611_ktehno";
$base_n="localhost";
$base_login="u0372_ktehno";
$base_pass="";


//*************************************************************************

$db = @mysql_connect("$base_n","$base_login","$base_pass") or die ("Could not connect to MySQL");//Подключение бд
mysql_select_db("$base_name",$db); # Выбор бд
mysql_query ("SET NAMES UTF8", $db); #Установка кодировки

$err=1;
$res=mysql_query ("SELECT COUNT(*) FROM information_schema.tables WHERE TABLE_SCHEMA = '$base_name' AND TABLE_NAME='a_options'");
$myrow = mysql_fetch_array($res);

if ($myrow[0]>0) {
    
    $err=0;



    mysql_select_db("$base_name",$db) or die ("Could not select database");     // Выбор бд
    mysql_query("SET NAMES utf8", $db);
    
    // ОПЦИИ
    $sql = "SELECT a_options.name, a_options.val
    				FROM a_options
    "; 
    $res = mysql_query($sql) or die(mysql_error());
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $_SESSION['a_options'][$myrow[0]]=$myrow[1];
    }
}
//если базы нет или скрипт новее последнего обновления -> запускаем скрипт создания/обновления б/д

if  (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'install.php')){

    if ($err==1){include dirname(__FILE__).DIRECTORY_SEPARATOR.'install.php';exit();}
    else{
        
        // проверка на обновление
        
            $update= date('Y-m-d H:i:s', filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'install.php'));
            $sql = "SELECT IF(COUNT(*)>0,a_options.val,'') 
            				FROM a_options 
            					WHERE a_options.name='ADMIN: TIMESTAMP'
            	"; 
            $res = mysql_query($sql) or die(mysql_error());
            $myrow = mysql_fetch_array($res);
            
            if ($update!=$myrow[0]){
                echo 'update: '.$update.'!='.$myrow[0];
                include dirname(__FILE__).DIRECTORY_SEPARATOR.'install.php';exit();
            }
        
    }
}else{echo 'error: no '.dirname(__FILE__).DIRECTORY_SEPARATOR.'install.php';}


//создаем архив базы данных
//http://site.ru/admin/?com=copy_db&inc_to_db=***table&key=****$_SESSION['a_options']['secret_key']
if (isset($_REQUEST['com']) and $_REQUEST['com']=='copy_db'){
    if (isset($_SESSION['a_options']['secret_key']) 
        and isset($_REQUEST['key']) 
        and $_REQUEST['key']==$_SESSION['a_options']['secret_key']){
        
        $inc_to_db='';
        if (isset($_REQUEST['inc_to_db']) and $_REQUEST['inc_to_db']!=''){
            $inc_to_db=mysql_real_escape_string(trim(strip_tags($_REQUEST['inc_to_db'])));
        }
        
        if (file_exists('db.sql')){@unlink('db.sql');}
        system("mysqldump -h$base_n -u$base_login -p$base_pass $base_name $inc_to_db > db.sql");// -A - все базы данных
        
        //проверяем данные для отправки файла на Яндекс-диск
        if (isset($_SESSION['a_options']['YANDEX: login']) 
            and $_SESSION['a_options']['YANDEX: login']!=''
            and isset($_SESSION['a_options']['YANDEX: password']) 
            and $_SESSION['a_options']['YANDEX: password']!=''
            ){
            if (!class_exists('webdav_client')) {
                require('class/class_webdav_client.php');
            }
            
            $wdc = new webdav_client();
            $wdc->set_server('ssl://webdav.yandex.ru');
            $wdc->set_port(443);
            $wdc->set_user(trim(strip_tags($_SESSION['a_options']['YANDEX: login'])));
            $wdc->set_pass(trim(strip_tags($_SESSION['a_options']['YANDEX: password'])));
            $wdc->set_protocol(1);
            $wdc->set_debug(false);
            
            if ( !$wdc->open() )
            {
                print 'Не возможно скопировать архив базы данных на Яндекс-диск '.$_SESSION['a_options']['YANDEX: login'].'<br /> \r\n';
                print 'Error: could not open server connection <br /> \r\n';
                
            }
            else{
                if ( !$wdc->check_webdav() )
                {
                    print 'Не возможно скопировать архив базы данных на Яндекс-диск: '.$_SESSION['a_options']['YANDEX: login'].'<br /> \r\n';
                }
                else{
                 
                    $http_status = $wdc->mkcol("/db_arhiv");
                    $http_status = $wdc->mkcol("/db_arhiv/".$_SERVER['SERVER_NAME']);
                    if (file_exists('db.sql')){
                       $http_status = $wdc->put_file(  "/db_arhiv/".$_SERVER['SERVER_NAME'].'/db_'.$inc_to_db.'_'.date('Y_m_d_H_i_s').'.sql', 'db.sql'  );
                        switch ($http_status){
                           case 200:
                           case 201:
                           case 204:
                            
                            break;
                            default:
                            $wdc->errorMsg = 'Error ocured '.$http_status;
                            
                            break;
                        }
                    }
                    $wdc->close();
                    echo 'Данные успешно выгружены на Яндекс-диск '.$_SESSION['a_options']['YANDEX: login'];
                    exit;
                    
                            
                }
            }
        }
    }else{
        echo 'Не верный пароль для архивирования базы данный!';exit();
    }
}    

//end архив базы данных

// *************************************************************************


unset ($base_login,$base_pass); # Удаляем переменные базы данных

?>