<?php 
    if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
    if (!isset($cur_dir)){
        $cur_dir='';
        if (strstr(__FILE__,'/docs/')==true){
            $dir_par_arr=explode('/docs/',__FILE__);
            
            $cur_dir=str_replace(array('/admin/functions.php','admin/functions.php','admin/install.php'),'',$dir_par_arr[1]);
        }
        
        if ($cur_dir!=''){$cur_dir='/'.$cur_dir;}
    } 
?><html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Установка/обновление админ-панели <?=$_SERVER['SERVER_NAME'].$cur_dir;?>]</title>
</head> 
<body>

<?php
//создаем папки
$dir='../i';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_cat';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_cat/original';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_cat/small';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_cat/temp';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_struktura';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_struktura/original';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_struktura/small';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../i/s_struktura/temp';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/css';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/_obrabotchik';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/ajax';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/js';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/i';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/fonts';
if (!file_exists($dir)){mkdir($dir,0777);}
$dir='../shablon/com';
if (!file_exists($dir)){mkdir($dir,0777);}

$univers_code='utf8_unicode_ci';
$table_schema=$base_name;
include 'functions.php';

$s_cat_tip=_GP('s_cat_tip');

if ($s_cat_tip=='')
{
    $sql_col = "SELECT 
                    COLUMN_DEFAULT
            
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = '$table_schema'
                AND TABLE_NAME='s_cat'
                AND COLUMN_NAME='tip'
                 "; 
               
    $res_col = mysql_query($sql_col) or die(mysql_error());
    $myrow = mysql_fetch_array($res_col);
    $s_cat_tip=$myrow[0];
    if ($s_cat_tip!='Товар' and $s_cat_tip!='Услуга' ){
        $s_cat_tip='Товар';
    }
    
}


function sql_to_arr($sql){
        $arr_0=array('INSERT',' ','INTO');
        $arr_1=array(')','`');
        
        
        $sql_arr=explode('VALUES',$sql);
        $sql_arr_=str_replace($arr_0,'',$sql_arr[0]);
        $sql_arr2=explode('(',$sql_arr_);
    $table=str_replace('`','',$sql_arr2[0]);
        $col_a=str_replace($arr_1,'',$sql_arr2[1]);
    $col_arr=explode(',',$col_a);
        $data_txt=str_replace(');','',$sql_arr[1]);
        $data_txt=str_replace(', ',',',$data_txt);
        $data_arr=explode('),',$data_txt);
    foreach ($data_arr as $key => $val){
        $val=trim($val);
       
        $first=substr($val,0,1);
        if ($first=='('){$val=substr($val,1,strlen($val));}
        $val_arr=explode(",",$val);
    if (count($col_arr)==count($val_arr)){
        foreach ($val_arr as $key2 => $val2){
            $val2=str_replace("'",'',$val2);
            $arr[$key][$col_arr[$key2]]=$val2;
        }
    } else{
        print_rf($arr);
        return false;
    }
    }
    
    if (isset($arr) and count($arr)>0){
       return $arr; 
    }else{return false;}
}


function ins_opt($arr){
    
    $i=0;
    if (count($arr)>0){
        foreach($arr as $name => $val){
            
            $tip='Текст';
            if ($val!=strip_tags($val)){$tip='HTML-код';}
            elseif ($val=='1' or $val=='0'){$tip='Input';}
            elseif (date('Y-m-d H:i:s', strtotime($val))==$val){$tip='Дата-время';}
            
            $sql = "SELECT COUNT(*) 
            				FROM a_options 
            					WHERE name='"._DB($name)."'
            	"; 
            $res = mysql_query($sql); 
            $myrow = mysql_fetch_array($res);
            if ($myrow[0]==0){
                
                $sql = "INSERT into a_options (
                				name,
                				val,
                                tip
                			) VALUES (
                				'"._DB($name)."',
                				'"._DB($val)."',
                                '"._DB($tip)."'
                )";
                if (!mysql_query($sql)){echo $sql;exit();}
                else{$i++; }
                
            }else{
                
                if ($name=='ADMIN: TIMESTAMP'){
                    $sql = "
                    		UPDATE a_options 
                    			SET  
                    				val='"._DB($val)."',
                                    tip='"._DB($tip)."'
                    		
                    		WHERE name='"._DB($name)."'
                    ";
                    if(!mysql_query($sql)){echo $sql;}
                }
            }
            
        }
        
    }
    return $i;
}

function ins_tbl($tbl,$arr){
    $j=0;
    if (count($arr)>0){
        foreach($arr as $i => $col_arr){
            
            $SQL_S="";$SQL_I1="";$SQL_I2="";$SQL_U='';
            $id_='';
            foreach($col_arr as $col => $val){
                
                if($col=='id'){
                    $sql = "SELECT COUNT(*) 
            				FROM `"._DB($tbl)."`
            					WHERE `"._DB($tbl)."`.`id`='"._DB($val)."'
                	"; 
                    $res = mysql_query($sql) or die(mysql_error());
                    $myrow = mysql_fetch_array($res);
                    if ($myrow[0]>0){
                        $id_=$val;
                    }
                }
               
                    
                    if ($SQL_S!=''){$SQL_S.=' AND ';}
                    if ($SQL_I1!=''){$SQL_I1.=', ';}
                    if ($SQL_I2!=''){$SQL_I2.=', ';}
                    if ($SQL_U!=''){$SQL_U.=', ';}
                    
                    $SQL_S.="`".$col."`='"._DB($val)."'";
                    $SQL_I1.="`".$col."`";
                    $SQL_I2.="'"._DB($val)."'";
                    $SQL_U.="`".$col."` = '"._DB($val)."'";;
            }
            
            if ($id_=='')
            {
                if ($SQL_S!=''){
                    $sql = "SELECT COUNT(*) 
                				FROM `"._DB($tbl)."`
                					WHERE $SQL_S
                	"; 
                    $res = mysql_query($sql) or die(mysql_error());
                    $myrow = mysql_fetch_array($res);
                    if ($myrow[0]==0){
                        
                        $sql = "INSERT into `"._DB($tbl)."` (
                        				$SQL_I1
                        			) VALUES (
                        				$SQL_I2
                        )";
                        if (!mysql_query($sql)){echo $sql.'<br />'.mysql_error();exit();}
                        else{$j++; }
                        
                    }
                }
            }
            elseif($id_!=''){
                $sql = "
                		UPDATE `"._DB($tbl)."` 
                			SET  
                				$SQL_U
                		
                		WHERE id='"._DB($id_)."'
                ";
                //if (!mysql_query($sql)){echo $sql.'<br />'.mysql_error();exit();}
            }
            
            
        }
        
    }
    return $j;
}

//вывод данных
function echo_data_add($sql,$tbl){
        $arr=sql_to_arr($sql);
        if ($arr){
            $kol=ins_tbl($tbl,$arr);
            $cl_='#999';if ($kol>0){$kol='<strong>'.$kol.'</strong>';$cl_='#090';}
            echo '<p style="color:'.$cl_.';">Добавлено в таблицу '.$tbl.' '.$kol.' записей!</p>';
        } else{
            echo '<p style="color:#900;">Не определен массив!<p>';
        }
        unset($arr);
    }
    
// ***************************************************************************************************************
//********************************************************************************************************************
// ************************  ЭТАП 1 *********************************************************************************
if (!isset($_REQUEST['email']) and !isset($update)){
?>    
    <form method="post" action="?">
        <table>
            <tr>
                <td><span>Название филиала</span></td>
                 <td><input type="text" value="Основной" name="i_tp_name" /></td>
            </tr>
            <tr>
                <td><span>Город филиала</span></td>
                 <td><input type="text" value="Краснодар" name="i_city_name" /></td>
            </tr>
            <tr>
                <td><span>Имя администратора:</span></td>
                <td><input value="Алексей" name="admin_name" /></td>
            </tr>
            <tr>
                <td><span>Email администратора:</span></td>
                <td><input value="toowin86@yandex.ru" name="email" /></td>
            </tr>
            <tr>
                <td><span>Email при отправлении:</span></td>
                <td><input value="support@v-web.ru" name="email_from" /></td>
            </tr>
            <tr>
                <td><span>SMTP: сервер:</span></td>
                <td><input value="mail.nic.ru" name="smtp_server" /></td>
            </tr>
            <tr>
                <td><span>SMTP: порт:</span></td>
                <td><input value="587" name="smtp_port" /></td>
            </tr>
            <tr>
                <td><span>SMTP: login:</span></td>
                <td><input value="krassupport@v-web.ru" name="smtp_login" /></td>
            </tr>
            <tr>
                <td><span>SMTP: password:</span></td>
                <td><input value="1986689119AA" name="smtp_password" /></td>
            </tr>
            <tr>
                <td><span>Тип в номенклатуры (по умолчанию):</span></td>
                <td><select name="s_cat_tip"><option value="Товар">Товар</option><option value="Услуга">Услуга</option></select></td>
            </tr>
            <tr>
                <td><span>Логин аккаунта Яндекс-диска (для архивации БД):</span></td>
                <td><input value="ya.v-web@yandex.ru" name="ya_login" /></td>
            </tr>
            <tr>
                <td><span>Пароль аккаунта Яндекс-диска (для архивации БД):</span></td>
                 <td><input type="password" value="1986689119AA" name="ya_password" /></td>
            </tr>
        </table>   
        <p><center><input type="submit" value="Продолжить" /></center></p>
    </form>
    
<?php    




}
// ***************************************************************************************************************
//********************************************************************************************************************
// ************************  ЭТАП 2 *********************************************************************************
else{ 
    
//Создаем базу данных 
$sql="CREATE DATABASE IF NOT EXISTS `$base_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci; ";
if (!mysql_query($sql)){echo $sql;exit();}
$sql="USE `$base_name`; ";
if (!mysql_query($sql)){echo $sql;exit();}
mysql_query("SET NAMES utf8");

//********************************************************************************************************
//********************************************************************************************************
//********************************************************************************************************

//Создаем таблицы

//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_admin';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']="tinyint(1)";
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']="varchar(999)";
$col['phone']['_type']="varchar(255)";
$col['email']['_type']="varchar(255)";
$col['password']['_type']="varchar(255)";
$col['comments']['_type']="longtext";
$col['i_tp_id']['_type']='int(11)';
$col['i_tp_id']['DEFAULT']="'1'";
$col['chk_view_all_s_cat']['_type']="tinyint(1)";
$col['chk_view_all_s_cat']['DEFAULT']="'1'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="timestamp";
$col['data_change']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_visit']['_type']="timestamp";
$col['data_visit']['DEFAULT']="'0000-00-00 00:00:00'";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comments` longtext COLLATE utf8_unicode_ci NOT NULL,
  `i_tp_id` int(11) NULL DEFAULT '1',
  `chk_view_all_s_cat` tinyint(1) NOT NULL DEFAULT '1',
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_change` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Таблица пользователей' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_col';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']="tinyint(1)";
$col['chk_active']['DEFAULT']="'1'";
$col['chk_view']['_type']="tinyint(1)";
$col['chk_view']['DEFAULT']="'1'";
$col['chk_change']['_type']="tinyint(1)";
$col['chk_change']['DEFAULT']="'1'";
$col['a_menu_id']['_type']='int(11)';
$col['col']['_type']="varchar(999)";
$col['col_ru']['_type']="varchar(999)";
$col['tip']['_type']="enum('Текст','Длинный текст','HTML-код','Целое число','Дробное число','Стоимость','Дата','Дата-время','Телефон','Email','Связанная таблица 1-max','Связанная таблица max-max','Функция','chk','enum','Цвет','Фото','Ссылка')";
$col['tip']['DEFAULT']="'Текст'";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_col` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `chk_view` tinyint(1) NOT NULL DEFAULT '1',
  `chk_change` tinyint(1) NOT NULL DEFAULT '1',
  `a_menu_id` int(11) NOT NULL,
  `col` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `col_ru` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `tip` enum('Текст','Длинный текст','HTML-код','Целое число','Дробное число','Стоимость','Дата','Дата-время','Телефон','Email','Связанная таблица 1-max','Связанная таблица max-max','Функция','chk','enum','Цвет','Фото','Ссылка') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Текст',
  PRIMARY KEY (`id`),
  INDEX `fk_a_menu_has_a_col_a_col1_idx` (`a_menu_id` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Столбцы для вывода из таблиц' AUTO_INCREMENT=45 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//




//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_admin_a_col';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_admin_a_col` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) NOT NULL,
  `id2` int(11) NOT NULL,
  
  INDEX `fk_a_admin_has_a_col_a_col1_idx` (`id2` ASC) ,
  INDEX `fk_a_admin_has_a_col_a_admin_idx` (`id1` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_a_admin_has_a_col_a_admin`
    FOREIGN KEY (`id1` )
    REFERENCES `a_admin` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_a_admin_has_a_col_a_col1`
    FOREIGN KEY (`id2` )
    REFERENCES `a_col` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
    
 ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Доступ пользователей к столбцам таблиц' AUTO_INCREMENT=186 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_com';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']="tinyint(1)";
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']="varchar(999)";
$col['com']['_type']="varchar(999)";
$col['tip']['_type']="enum('Общая','По id','','')";
$col['tip']['DEFAULT']="'Общая'";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_com` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `com` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `tip` enum('Общая','По id','','') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Общая',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список функций админ-панели (добавить, удалить, изменить...)' AUTO_INCREMENT=4 ;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_menu';
$col['id']['_type']='int(11)';
$col['pid']['_type']='int(11)';
$col['sid']['_type']="int(11)";
$col['chk_active']['_type']="tinyint(1)";
$col['chk_active']['DEFAULT']="'1'";
$col['chk_block']['_type']="tinyint(1)";
$col['chk_block']['DEFAULT']="'0'";
$col['name']['_type']="varchar(255)";
$col['inc']['_type']="varchar(255)";
$col['comments']['_type']="longtext";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `a_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `chk_block` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comments` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Меню админки' AUTO_INCREMENT=59 ;



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_admin_a_menu';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_admin_a_menu` (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `id1` int(11) NOT NULL,
  `id2` int(11) NOT NULL,
  
  INDEX `fk_a_admin_has_a_menu_a_menu1_idx` (`id2` ASC) ,
  INDEX `fk_a_admin_has_a_menu_a_admin_idx` (`id1` ASC) ,
  
  PRIMARY KEY (`id`),
  
  CONSTRAINT `fk_a_admin_has_a_menu_a_admin`
    FOREIGN KEY (`id1` )
    REFERENCES `a_admin` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_a_admin_has_a_menu_a_menu1`
    FOREIGN KEY (`id2` )
    REFERENCES `a_menu` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Доступ пользователей к пунктам админ-меню' AUTO_INCREMENT=904 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_menu_a_com';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_menu_a_com` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) NOT NULL,
  `id2` int(11) NOT NULL,
  
  INDEX `fk_a_menu_has_a_com_a_com1_idx` (`id2` ASC) ,
  INDEX `fk_a_menu_has_a_com_a_menu_idx` (`id1` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_a_menu_has_a_com_a_menu`
    FOREIGN KEY (`id1` )
    REFERENCES `a_menu` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_a_menu_has_a_com_a_com1`
    FOREIGN KEY (`id2` )
    REFERENCES `a_com` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Таблица связи пунктов меню с функциями' AUTO_INCREMENT=52 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_admin_a_menu_a_com';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_admin_a_menu_a_com` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) NOT NULL,
  `id2` int(11) NOT NULL,

  INDEX `fk_a_admin_has_a_menu_a_com_a_menu_a_com1_idx` (`id2` ASC) ,
  INDEX `fk_a_admin_has_a_menu_a_com_a_admin_idx` (`id1` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_a_admin_has_a_menu_a_com_a_admin`
    FOREIGN KEY (`id1` )
    REFERENCES `a_admin` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_a_admin_has_a_menu_a_com_a_menu_a_com1`
    FOREIGN KEY (`id2` )
    REFERENCES `a_menu_a_com` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
    

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Распределение прав доступа для функций a_admin - a_menu_a_com' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//





//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_connect';
$col['id']['_type']='int(11)';
$col['a_col_id1']['_type']="int(11)";
$col['a_col_id2']['_type']="int(11)";
$col['usl']['_type']="longtext";
$col['chk']['_type']="tinyint(1)";
$col['chk']['DEFAULT']="'1'";
$col['tbl']['_type']="longtext";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_connect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_col_id1` int(11) DEFAULT NULL,
  `a_col_id2` int(11) NOT NULL,
  `usl` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'условия вывода',
  `chk` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Для 1-max: 1-автодобавление, max-max: 1-select, 0 - checkbox',
  `tbl` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'таблица',
  
  INDEX `fk_a_col_has_a_col_a_col1_idx` (`a_col_id2` ASC) ,
  INDEX `fk_a_col_has_a_col_a_col_idx` (`a_col_id1` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_a_col_has_a_col_a_col`
    FOREIGN KEY (`a_col_id1` )
    REFERENCES `a_col` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_a_col_has_a_col_a_col1`
    FOREIGN KEY (`a_col_id2` )
    REFERENCES `a_col` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
    
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Таблица связей между таблицами 1-max и max-max' AUTO_INCREMENT=4 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//




//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_options';
$col['id']['_type']='int(11)';
$col['name']['_type']='varchar(999)';
$col['val']['_type']='longtext';
$col['tip']['_type']="enum('Текст','HTML-код','Дата-время','Input')";
$col['tip']['DEFAULT']="'Текст'";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `val` longtext COLLATE utf8_unicode_ci NOT NULL,
  `tip` enum('Текст','HTML-код','Дата-время','Input') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Текст',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Опции админки' AUTO_INCREMENT=9 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-11-26 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_photo';
$col['id']['_type']='bigint(20)';
$col['a_menu_id']['_type']='int(11)';
$col['row_id']['_type']='bigint(20)';
$col['sid']['_type']='int(11)';
$col['tip']['_type']="enum('Основное','Галерея','Меню','Фон','Прочее','Слайдер')";
$col['tip']['DEFAULT']="'Основное'";
$col['img']['_type']='varchar(999)';
$col['comments']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_photo` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `a_menu_id` int(11) NULL,
  `row_id` bigint(20) NULL,
  `sid` int(11) NOT NULL,
  `tip` enum('Основное','Галерея','Меню','Фон','Прочее','Слайдер') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Основное',
  `img` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `comments` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `fk_a_photo_a_menu_idx` (`a_menu_id` ASC) ,
  INDEX `fk_a_photo_row_id_idx` (`row_id` ASC),
  INDEX `fk_a_photo_tip_idx` (`tip` ASC) ,
  CONSTRAINT `fk_a_photo_a_menu`
    FOREIGN KEY (`a_menu_id` )
    REFERENCES `a_menu` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Фотографии для всех таблиц' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-05-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_parsing';
$col['id']['_type']='int(11)';
$col['a_menu_id']['_type']='int(11)';
$col['name']['_type']='text';
$col['url']['_type']='text';
$col['selector_page']['_type']='text';
$col['selector_block']['_type']='text';
$col['selector_card']['_type']='text';
$col['main_if']['_type']='text';
$col['tip']['_type']="enum('Тест','Добавление в базу')";
$col['tip']['DEFAULT']="'Тест'";
$col['main_col']['_type']='varchar(999)';
$col['tip_update']['_type']="enum('Не обновлять','Обновлять')";
$col['tip_update']['DEFAULT']="'Не обновлять'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['pop']['_type']="int(11)";
$col['sleep_']['_type']='int(11)';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_parsing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_menu_id` int(11) NULL,
  `name` text NULL,
  `url` text NULL,
  `selector_page` text NOT NULL,
  `selector_block` text NOT NULL,
  `selector_card` text NOT NULL,
  `main_if` text NOT NULL,
  `tip` enum('Тест','Добавление в базу') NOT NULL DEFAULT 'Тест',
  `main_col` varchar(999) NOT NULL,
  `tip_update` enum('Не обновлять','Обновлять') NOT NULL DEFAULT 'Не обновлять',
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pop` int(11) NULL,
  `sleep_` int(11) NULL,

  PRIMARY KEY (`id`),
  INDEX `fk_a_parsing_has_a_parsing_a_menu1_idx` (`a_menu_id` ASC) 
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Настройки парсера' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-05-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_parsing_col';
$col['id']['_type']='int(11)';
$col['a_parsing_id']['_type']='int(11)';
$col['col']['_type']='text';
$col['chk_active']['_type']='tinyint(1)';
$col['selector']['_type']='text';
$col['code']['_type']='text';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_parsing_col` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_parsing_id` int(11) NULL,
  `col` text NULL,
  `chk_active` tinyint(1) NOT NULL,
  `selector` text NOT NULL,
  `code` text NOT NULL,

  PRIMARY KEY (`id`),
  INDEX `fk_a_parsing_col_has_a_parsing_col_a_parsing1_idx` (`a_parsing_id` ASC) 
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Настройки столбцов для парсера' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-05-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_export_csv';
$col['id']['_type']='int(11)';
$col['a_menu_id']['_type']='int(11)';
$col['name']['_type']='text';
$col['opt1']['_type']='text';
$col['opt2']['_type']='text';
$col['opt3']['_type']='text';
$col['opt4']['_type']='text';
$col['opt5']['_type']='tinyint(1)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['pop']['_type']="int(11)";


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_export_csv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_menu_id` int(11) NULL,
  `name` text NULL,
  `opt1` text NULL,
  `opt2` text NOT NULL,
  `opt3` text NOT NULL,
  `opt4` text NOT NULL,
  `opt5` tinyint(1) NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pop` int(11) NULL,

  PRIMARY KEY (`id`),
  INDEX `fk_a_admin_has_a_export_csv_a_menu1_idx` (`a_menu_id` ASC) 
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Настройки экспорта csv' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-05-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_export_csv_col';
$col['id']['_type']='int(11)';
$col['a_export_csv_id']['_type']='int(11)';
$col['col']['_type']='text';
$col['code']['_type']='text';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `a_export_csv_col` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_export_csv_id` int(11) NULL,
  `col` text NULL,
  `code` text NOT NULL,

  PRIMARY KEY (`id`),
  INDEX `fk_a_export_csv_has_a_export_csv_col_a_export_csv1_idx` (`a_export_csv_id` ASC) 
    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Настройки столбцов для экспорта csv' AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-03-19 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_class_unit';
$col['id']['_type']='smallint(6)';
$col['name']['_type']='varchar(255)';
$col['number_code']['_type']='varchar(5)';
$col['rus_name1']['_type']='varchar(50)';
$col['eng_name1']['_type']='varchar(50)';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_class_unit` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'pk',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `number_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `rus_name1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `eng_name1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Общероссийский классификатор единиц измерения ОКЕИ' AUTO_INCREMENT=461 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-03-19 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_sinonim';
$col['id']['_type']='BIGINT(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name1']['_type']='TEXT';
$col['name2']['_type']='TEXT';
$col['tip']['_type']='INT(11)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `i_sinonim` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `chk_active` TINYINT(1) NULL DEFAULT 1 ,
  `name1` TEXT NULL ,
  `name2` TEXT NULL ,
  `tip` INT(11) NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Таблица синонимов'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-03-19 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_bik';
$col['id']['_type']='int(11)';
$col['REALL']['_type']='varchar(4)';
$col['PZN']['_type']='varchar(2)';
$col['UER']['_type']='varchar(1)';
$col['RGN']['_type']='varchar(2)';
$col['IND']['_type']='varchar(6)';
$col['TNP']['_type']='varchar(1)';
$col['NNP']['_type']='varchar(25)';
$col['ADR']['_type']='varchar(30)';
$col['RKC']['_type']='varchar(9)';
$col['NAMEP']['_type']='varchar(45)';
$col['NAMEN']['_type']='varchar(30)';
$col['NEWNUM']['_type']='varchar(9)';
$col['NEWKS']['_type']='varchar(9)';
$col['PERMFO']['_type']='varchar(6)';
$col['SROK']['_type']='varchar(2)';
$col['AT1']['_type']='varchar(7)';
$col['AT2']['_type']='varchar(7)';
$col['TELEF']['_type']='varchar(25)';
$col['REGN']['_type']='varchar(9)';
$col['OKPO']['_type']='varchar(8)';
$col['DT_IZM']['_type']='datetime';
$col['CKS']['_type']='varchar(6)';
$col['KSNP']['_type']='varchar(20)';
$col['DATE_IN']['_type']='datetime';
$col['DATE_CH']['_type']='datetime';
$col['VKEYDEL']['_type']='varchar(8)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_bik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `REALL` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PZN` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UER` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RGN` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IND` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TNP` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NNP` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ADR` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RKC` varchar(9) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NAMEP` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NAMEN` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NEWNUM` varchar(9) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NEWKS` varchar(9) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PERMFO` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SROK` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AT1` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AT2` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TELEF` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `REGN` varchar(9) COLLATE utf8_unicode_ci DEFAULT NULL,
  `OKPO` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DT_IZM` datetime DEFAULT NULL,
  `CKS` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `KSNP` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DATE_IN` datetime DEFAULT NULL,
  `DATE_CH` datetime DEFAULT NULL,
  `VKEYDEL` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4267 ;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-06-21 toowin86 // COMMENT 'pk'
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_scheta';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['i_tp_id']['_type']='int(11)';
$col['i_tp_id']['DEFAULT']="'1'";
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='varchar(255)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_scheta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NULL,
  `i_tp_id` int(11) NOT NULL DEFAULT '1',
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Счета организации' AUTO_INCREMENT=461 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_struktura';
$col['id']['_type']='bigint(20)';
$col['pid']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['tip']['_type']="enum('Ручное заполнение','Каталог','Ссылка','Функция')";
$col['tip']['DEFAULT']="'Ручное заполнение'";

$col['url']['_type']='text';
$col['page_name']['_type']='text';
$col['description']['_type']='text';
$col['page_name']['_type']='text';
$col['keywords']['_type']='longtext';
$col['html_code']['_type']='longtext';
$col['icon']['_type']='text';
$col['skidka']['_type']='float';
$col['skidka']['DEFAULT']="'0'";
$col['article']['_type']='text';
$col['tag']['_type']='text';

$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="datetime";
$col['a_admin_id_change']['_type']="int(11)";
$col['a_admin_id_change']['DEFAULT']="'0'";
$col['a_admin_id_create']['_type']="int(11)";
$col['a_admin_id_create']['DEFAULT']="'0'";
$col['view']['_type']="enum('Видно всем','Видно зарегистрированным','Видно после оплаты')";
$col['view']['DEFAULT']="'Видно всем'";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_struktura` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `tip` enum('Ручное заполнение','Каталог','Ссылка','Функция') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Ручное заполнение',
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `page_name` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `keywords` longtext COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `icon` text COLLATE utf8_unicode_ci NOT NULL,
  `skidka` float NOT NULL DEFAULT '0',
  `article` text COLLATE utf8_unicode_ci NOT NULL,
  `tag` text COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `a_admin_id_change` int(11) NULL DEFAULT '0',
  `a_admin_id_create` int(11) NULL DEFAULT '0',
  `view` enum('Видно всем','Видно зарегистрированным','Видно после оплаты') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Видно всем',
  PRIMARY KEY (`id`),
  
  INDEX `fk_s_struktura_has_sid_idx` (`sid` ASC),
  INDEX `fk_s_struktura_has_pid_idx` (`pid` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


 
//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_struktura_s_struktura';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';

$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `s_struktura_s_struktura` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `id1` bigint(20) NOT NULL ,
  `id2` bigint(20) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_s_struktura_s_struktura_s_struktura1_idx` (`id1` ASC) ,
  INDEX `fk_s_struktura_s_struktura_s_struktura2_idx` (`id2` ASC)
  )
ENGINE = InnoDB
COMMENT = 'Связь для вывода ссылочных пунктов меню на сайте, когда один'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-01-28 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_shablon';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_shablon` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-01-28 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_struktura_s_shablon';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_struktura_s_shablon` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_s_struktura_s_shablon_id1_idx` (`id1` ASC) ,
  INDEX `fk_s_struktura_s_shablon_id2_idx` (`id2` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Отношение товара-услуги к ветке структуры' AUTO_INCREMENT=17 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-01-28 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_words';
$col['id']['_type']='bigint(20)';
$col['name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="datetime";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_words` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_change` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-02-11 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='info';
$col['id']['_type']='int(11)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-02-11 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_tip_price';
$col['id']['_type']='int(11)';
$col['name']['_type']='varchar(999)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_tip_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_cat';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['url']['_type']='text';
$col['price']['_type']='float';
$col['html_code']['_type']='longtext';
$col['tip']['_type']="enum('Товар','Услуга')";
$col['tip']['DEFAULT']="'$s_cat_tip'";
$col['mini_desc']['_type']='text';
$col['data_end']['_type']="timestamp";
$col['data_end']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="timestamp";
$col['data_change']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_last_visit']['_type']="timestamp";
$col['data_last_visit']['DEFAULT']="'0000-00-00 00:00:00'";
$col['article']['_type']='longtext';
$col['s_tip_price_id']['_type']="int(11)";
$col['s_tip_price_id']['DEFAULT']="'1'";
$col['i_class_unit_id']['_type']="int(11)";
$col['i_class_unit_id']['DEFAULT']="'135'";
$col['link']['_type']="text";
$col['pop']['_type']="int(11)";
$col['kolvo']['_type']="float";
$col['chk_new']['_type']='tinyint(1)';
$col['chk_new']['DEFAULT']="'0'";
$col['price_convert']['_type']='float';
$col['price_tip']['_type']="enum('USD','EUR','CNY','GBP','JPY','KZT')";
$col['price_tip']['DEFAULT']="'USD'";
$col['a_admin_id_change']['_type']="int(11)";
$col['a_admin_id_change']['DEFAULT']="'0'";
$col['a_admin_id_create']['_type']="int(11)";
$col['a_admin_id_create']['DEFAULT']="'0'";
$col['i_contr_id_create']['_type']="bigint(20)";
$col['i_contr_id_create']['DEFAULT']="'0'";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `s_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `price` float NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `tip` enum('Товар','Услуга') COLLATE utf8_unicode_ci NOT NULL DEFAULT '$s_cat_tip',
  `mini_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `data_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_change` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `article` longtext COLLATE utf8_unicode_ci NOT NULL,
  `s_tip_price_id` int(11) NULL DEFAULT '1',
  `i_class_unit_id` int(11) NULL DEFAULT '135',
  `link` text COLLATE utf8_unicode_ci NOT NULL,
  `pop` int(11) COLLATE utf8_unicode_ci NOT NULL,
  `kolvo` float NOT NULL,
  `chk_new` tinyint(1) NOT NULL DEFAULT '0',
  `price_convert` float NOT NULL,
  `price_tip` enum('USD','EUR','CNY','GBP','JPY','KZT') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
  `a_admin_id_change` int(11) NULL DEFAULT '0',
  `a_admin_id_create` int(11) NULL DEFAULT '0',
  `i_contr_id_create` bigint(20) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `k_s_cat_has_price_idx` (`price` ASC),
  INDEX `k_s_cat_has_s_tip_price_id_idx` (`s_tip_price_id` ASC),
  INDEX `k_s_cat_has_i_class_unit_id_idx` (`i_class_unit_id` ASC),
  INDEX `k_s_cat_has_a_admin_id_change_idx` (`a_admin_id_change` ASC),
  INDEX `k_s_cat_has_a_admin_id_create_idx` (`a_admin_id_create` ASC),
  INDEX `k_s_cat_has_data_change_idx` (`data_change` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_cat_s_cat';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_cat_s_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_s_cat_has_s_cat_s_cat_idx1` (`id2` ASC) ,
  INDEX `fk_s_cat_has_s_cat_s_cat_idx2` (`id1` ASC) ,
  CONSTRAINT `fk_s_cat_has_s_cat_s_cat2`
    FOREIGN KEY (`id1` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_s_cat_has_s_cat_s_cat1`
    FOREIGN KEY (`id2` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-01-28 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
/////////////////// 

$table='s_prop';
$col['id']['_type']='bigint(20)';
$col['sid']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['chk_fillter']['_type']='tinyint(1)';
$col['chk_fillter']['DEFAULT']="'1'";
$col['chk_main']['_type']='tinyint(1)';
$col['chk_main']['DEFAULT']="'0'";
$col['name']['_type']='longtext';
$col['comments']['_type']='longtext';
$col['tip']['_type']="enum('Список','Авто добавление')";
$col['tip']['DEFAULT']="'Список'";
$col['data_tip']['_type']="enum('Текст','Число')";
$col['data_tip']['DEFAULT']="'Текст'";
$col['tip_view']['_type']="enum('Основной','Шаблон1','Шаблон2')";
$col['tip_view']['DEFAULT']="'Основной'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

 
$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `s_prop` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sid` bigint(20) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `chk_fillter` tinyint(1) NOT NULL DEFAULT '1',
  `chk_main` tinyint(1) NOT NULL DEFAULT '0',
  `name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `comments` longtext COLLATE utf8_unicode_ci NOT NULL,
  `tip` enum('Список','Авто добавление') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Список',
  `data_tip` enum('Текст','Число') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Текст',
  `tip_view` enum('Основной','Шаблон1','Шаблон2') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Основной',
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
 /**/
//************************************************************************//

//************************************************************************// 2015-01-28 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='s_prop_val';
$col['id']['_type']='bigint(20)';
$col['s_prop_id']['_type']='bigint(20)';
$col['val']['_type']='longtext';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

 
$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `s_prop_val` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `s_prop_id` bigint(20) NULL,
  `val` longtext COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_s_prop_val_has_s_prop_s_prop_id_idx` (`s_prop_id` ASC) ,
  CONSTRAINT `fk_s_prop_val_has_s_prop_s_prop_id`
    FOREIGN KEY (`s_prop_id` )
    REFERENCES `s_prop` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13176 ;



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//


//************************************************************************// 2015-01-16 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
/**/
$table='s_cat_s_prop_val';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';

 
$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_cat_s_prop_val` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_s_cat_has_s_prop_val_s_prop_val_idx` (`id2` ASC) ,
  INDEX `fk_s_cat_has_s_prop_val_s_cat_idx` (`id1` ASC) ,
  CONSTRAINT `fk_s_cat_has_s_prop_val_s_cat`
    FOREIGN KEY (`id1` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_s_cat_has_s_prop_val_s_prop_val`
    FOREIGN KEY (`id2` )
    REFERENCES `s_prop_val` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=39410;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';

//************************************************************************//


//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_cat_s_struktura';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_cat_s_struktura` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_s_cat_has_s_struktura_s_struktura_idx` (`id2` ASC) ,
  INDEX `fk_s_cat_has_s_struktura_s_cat_idx` (`id1` ASC) ,
  CONSTRAINT `fk_s_cat_has_s_struktura_s_cat`
    FOREIGN KEY (`id1` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_s_cat_has_s_struktura_s_struktura`
    FOREIGN KEY (`id2` )
    REFERENCES `s_struktura` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13814;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



////////////////////// ДЛЯ ТЕСТОВ
//************************************************************************// 2019-02-24 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_cat_s_struktura_fortest';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_cat_s_struktura_fortest` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_s_struktura_has_s_struktura_s_struktura_idx` (`id2` ASC) ,
  INDEX `fk_s_struktura_has_s_struktura_s_cat_idx` (`id1` ASC) ,
  CONSTRAINT `fk_s_struktura_has_s_struktura_s_cat`
    FOREIGN KEY (`id1` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_s_struktura_has_s_struktura_s_struktura`
    FOREIGN KEY (`id2` )
    REFERENCES `s_struktura` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13814;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_news';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['sid']['DEFAULT']="'0'";
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['description']['_type']='text';
$col['url']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="timestamp";
$col['data_publish']['_type']="timestamp";
$col['html_code']['_type']='longtext';
$col['a_admin_id']['_type']="int(11)";
$col['s_struktura_id']['_type']="int(11)";

$col['a_admin_id_change']['_type']="int(11)";
$col['a_admin_id_change']['DEFAULT']="'0'";
$col['a_admin_id_create']['_type']="int(11)";
$col['a_admin_id_create']['DEFAULT']="'0'";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `s_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_change` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_publish` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `a_admin_id` int(11) NULL,
  `s_struktura_id` int(11) NULL,
  `a_admin_id_change` int(11) NULL DEFAULT '0',
  `a_admin_id_create` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `fk_s_news_has_a_admin_id_idx` (`a_admin_id` ASC) ,
  INDEX `fk_s_news_has_s_struktura_id_idx` (`s_struktura_id` ASC) ,
  INDEX `fk_s_news_has_a_admin_id_create_idx` (`a_admin_id_create` ASC) ,
  INDEX `fk_s_news_has_a_admin_id_change_idx` (`a_admin_id_change` ASC) 
  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

$sql = "DROP TABLE IF EXISTS `s_search_history`
";
$res = mysql_query($sql);
	if (!$res){echo $sql;exit();}

//************************************************************************// 2015-07-11 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='l_search_history';
$col['id']['_type']='bigint(20)';
$col['name']['_type']='text';
$col['ip']['_type']='varchar(45)';
$col['cnt']['_type']='int(11)';
$col['url']['_type']='LONGTEXT';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";



$needle[$table]['_sql_']="


CREATE  TABLE IF NOT EXISTS `l_search_history` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` TEXT NULL ,
  `ip` VARCHAR(45) NULL ,
  `cnt` int(11) NULL ,
  `url` LONGTEXT NULL ,
  `data_create` TIMESTAMP NULL DEFAULT current_timestamp ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-07-11 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='l_user_log';
$col['id']['_type']='bigint(20)';
$col['ip']['_type']='varchar(45)';
$col['url']['_type']='text';
$col['ref']['_type']='text';
$col['user_agent']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";



$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `l_user_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `ip` varchar(45) NULL ,
  `url` text NULL ,
  `ref` text NULL ,
  `user_agent` text NULL ,
  
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  
  PRIMARY KEY (`id`),
  INDEX `fk_l_user_log_has_ipx` (`ip` ASC) ,
  INDEX `fk_l_user_log_has_data_createx` (`data_create` ASC) 
)ENGINE = InnoDB

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

$sql = "DROP TABLE IF EXISTS `s_price_history`
";
$res = mysql_query($sql);
	if (!$res){echo $sql;exit();}

//************************************************************************// 2015-07-08 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='l_price_history';
$col['id']['_type']='bigint(20)';
$col['s_cat_id']['_type']='bigint(20)';
$col['price']['_type']='float';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

 
$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `l_price_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `s_cat_id` bigint(20) NOT NULL ,
  `price` FLOAT NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_l_price_history_s_cat1_idx` (`s_cat_id` ASC) ,
  CONSTRAINT `fk_l_price_history_s_cat1`
    FOREIGN KEY (`s_cat_id` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'История изменения цен';



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//

//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_contr';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='longtext';
$col['email']['_type']='VARCHAR(255)';
$col['password']['_type']='VARCHAR(255)';
$col['phone']['_type']='VARCHAR(255)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="datetime";
$col['data_last_visit']['_type']="datetime";
$col['html_code']['_type']='longtext';
$col['adress']['_type']='longtext';
$col['link']['_type']='longtext';
$col['i_contr_id']['_type']='bigint(20)';
$col['i_reklama_id']['_type']='int(11)';
$col['skidka']['_type']='float';
$col['nakrutka']['_type']='float';
$col['pasport']['_type']='varchar(999)';
$col['birthday']['_type']='date';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_contr` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_change` datetime NOT NULL,
  `data_last_visit` datetime NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `adress` longtext COLLATE utf8_unicode_ci NOT NULL,
  `link` longtext COLLATE utf8_unicode_ci NOT NULL,
  `i_contr_id` bigint(20) COLLATE utf8_unicode_ci NOT NULL,
  `i_reklama_id` int(11) COLLATE utf8_unicode_ci NOT NULL,
  `skidka` float COLLATE utf8_unicode_ci NOT NULL,
  `nakrutka` float COLLATE utf8_unicode_ci NOT NULL,
  `pasport` varchar(999) COLLATE utf8_unicode_ci NOT NULL,
  `birthday` date COLLATE utf8_unicode_ci NOT NULL,
  
  PRIMARY KEY (`id`),
      INDEX `fk_i_contr_i_contr1_idx` (`i_contr_id` ASC),
      INDEX `fk_i_contr_i_reklama1_idx` (`i_reklama_id` ASC)
  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Контрагенты' AUTO_INCREMENT=1 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



//************************************************************************// 2018-03-12 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_contr_s_struktura';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_contr_s_struktura` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_i_contr_has_s_struktura_s_struktura_idx` (`id2` ASC) ,
  INDEX `fk_i_contr_has_s_struktura_i_contr_idx` (`id1` ASC) ,
  CONSTRAINT `fk_i_contr_has_s_struktura_i_contr`
    FOREIGN KEY (`id1` )
    REFERENCES `i_contr` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_i_contr_has_s_struktura_s_struktura`
    FOREIGN KEY (`id2` )
    REFERENCES `s_struktura` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13814;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



/* 
   
$sql = "select COUNT(*) from information_schema.columns where table_name = 'i_contr' and column_name = 'i_contr_org_id'
	"; 

$res = mysql_query($sql);if (!$res){echo $sql;exit();}
$myrow = mysql_fetch_array($res);
if ($myrow[0]>0){

    $sql = "ALTER TABLE `i_contr` DROP COLUMN `i_contr_org_id`;";
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);    
}*/

//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_contr_org';
$col['id']['_type']='bigint(20)';
$col['name']['_type']='TEXT';
$col['inn']['_type']='VARCHAR(255)';
$col['kpp']['_type']='VARCHAR(255)';
$col['ogrn']['_type']='VARCHAR(255)';
$col['bik']['_type']='VARCHAR(255)';
$col['bank']['_type']='TEXT';
$col['schet']['_type']='VARCHAR(255)';
$col['kschet']['_type']='VARCHAR(255)';
$col['phone']['_type']='VARCHAR(255)';
$col['email']['_type']='VARCHAR(255)';
$col['site']['_type']='VARCHAR(255)';
$col['u_adress']['_type']='TEXT';
$col['fio_director']['_type']='TEXT';
$col['tip_director']['_type']='VARCHAR(255)';
$col['na_osnovanii']['_type']='VARCHAR(255)';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_contr_org` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `inn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `kpp` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `ogrn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `bik` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `bank` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `schet` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `kschet` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `site` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `u_adress` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `fio_director` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `tip_director` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `na_osnovanii` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-08-03 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_contr_i_contr_org';
$col['id']['_type']='BIGINT(20)';
$col['id1']['_type']='BIGINT(20)';
$col['id2']['_type']='BIGINT(20)';


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `i_contr_i_contr_org` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `id1` BIGINT(20) NOT NULL ,
  `id2` BIGINT(20) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_i_contr_i_contr_org_i_contr1_idx` (`id1` ASC) ,
  INDEX `fk_i_contr_i_contr_org_i_contr_org1_idx` (`id2` ASC) ,
  CONSTRAINT `fk_i_contr_i_contr_org_i_contr1`
    FOREIGN KEY (`id1` )
    REFERENCES `i_contr` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_i_contr_i_contr_org_i_contr_org1`
    FOREIGN KEY (`id2` )
    REFERENCES `i_contr_org` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2017-06-24 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_social_network';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='TEXT';
$col['comments']['_type']='longtext';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_social_network` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `comments` longtext COLLATE utf8_unicode_ci NULL ,
  PRIMARY KEY (`id`))
ENGINE=InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Соц.сети'


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-06-24 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_tp';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='TEXT';
$col['i_contr_org_id']['_type']='bigint(20)';
$col['phone']['_type']='varchar(45)';
$col['i_city_id']['_type']='int(11)';
$col['adress']['_type']='varchar(999)';
$col['index_']['_type']='varchar(45)';
$col['email']['_type']='varchar(255)';
$col['site']['_type']='varchar(255)';
$col['worktime']['_type']='varchar(255)';
$col['comments']['_type']='longtext';
$col['geo']['_type']='text';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_tp` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `i_contr_org_id` bigint(20) NULL ,
  `phone` varchar(45) COLLATE utf8_unicode_ci NULL ,
  `i_city_id` int(11) NULL ,
  `adress` varchar(999) COLLATE utf8_unicode_ci NULL,
  `index_` varchar(45) COLLATE utf8_unicode_ci NULL ,
  `email` varchar(255) COLLATE utf8_unicode_ci NULL ,
  `site` varchar(255) COLLATE utf8_unicode_ci NULL ,
  `worktime` varchar(255) COLLATE utf8_unicode_ci NULL ,
  `comments` longtext COLLATE utf8_unicode_ci NULL ,
  `geo` text COLLATE utf8_unicode_ci NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_i_tp_i_contr_org1_idx` (`i_contr_org_id` ASC) ,
  CONSTRAINT `fk_i_tp_i_contr_org1`
    FOREIGN KEY (`i_contr_org_id` )
    REFERENCES `i_contr_org` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE=InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Точки продаж'


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2017-06-24 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_tp_i_social_network';
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['i_tp_id']['_type']='int(11)';
$col['i_social_network_id']['_type']='int(11)';
$col['name']['_type']='TEXT';
$col['data_create']['_type']='timestamp';
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_tp_i_social_network` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `sid` int(11) NOT NULL,
  `i_tp_id` int(11) NOT NULL,
  `i_social_network_id` int(11) NOT NULL,
  `name` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_i_tp_i_social_network_has_i_tp_id_idx` (`i_tp_id` ASC),
  INDEX `fk_i_tp_i_social_network_has_i_social_network_id_idx` (`i_social_network_id` ASC),
  INDEX `fk_i_tp_i_social_network_has_sid_idx` (`sid` ASC))
ENGINE=InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Соц.сети'


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-07-02 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_post';
$col['id']['_type']='int(11)';
$col['name']['_type']='VARCHAR(255)';
$col['obj']['_type']="enum('Заказ', 'Поступление', 'Работа')";
$col['obj']['DEFAULT']="'Работа'";
$col['comments']['_type']='LONGTEXT';
$col['data_create']['_type']='timestamp';
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `i_post` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NULL ,
  `obj` enum('Заказ', 'Поступление', 'Работа') NULL DEFAULT 'Работа',
  `comments` LONGTEXT NULL ,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Справочник должностей'


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-12-13 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_obj';
$col['id']['_type']='int(11)';
$col['obj']['_type']="enum('Заказ','Поступление','Работа')";
$col['obj']['DEFAULT']="'Заказ'";
$col['target']['_type']="enum('Процент со всего заказа','Процент с работы','Фиксированная сумма с заказа: авто','Фиксированная сумма с поступления: авто','Фиксированная сумма с работы: авто','Фиксированная сумма с работы: вручную','Процент с маржи заказа','Процент с маржи проданного товара из поступления: авто')";
$col['target']['DEFAULT']="'Процент со всего заказа'";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `i_obj` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `obj` enum('Заказ','Поступление','Работа') NOT NULL DEFAULT 'Заказ',
  `target` enum('Процент со всего заказа','Процент с работы','Фиксированная сумма с заказа: авто','Фиксированная сумма с поступления: авто','Фиксированная сумма с работы: авто','Фиксированная сумма с работы: вручную','Процент с маржи заказа', 'Процент с маржи проданного товара из поступления: авто') NOT NULL DEFAULT 'Процент со всего заказа',
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Справочник объектов начислени з/п'


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-12-13 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_zp';
$col['id']['_type']='int(11)';
$col['i_obj_id']['_type']='int(11)';
$col['val']['_type']="FLOAT";
$col['data_create']['_type']='timestamp';
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `i_zp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `i_obj_id` int(11) NULL,
  `val` FLOAT NULL ,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_i_zp_has_i_obj_id_idx` (`i_obj_id` ASC) )
ENGINE = InnoDB
COMMENT = 'Справочник зарплат'


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-07-02 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_admin_i_post';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';
$col['data_start']['_type']='DATETIME';
$col['data_start']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_end']['_type']='DATETIME';
$col['data_end']['DEFAULT']="'0000-00-00 00:00:00'";
$col['end_info']['_type']='LONGTEXT';
$col['data_create']['_type']='timestamp';
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `a_admin_i_post` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id1` INT NOT NULL ,
  `id2` INT NOT NULL ,
  `data_start` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `data_end` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `end_info` longtext COLLATE utf8_unicode_ci NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_a_admin_i_post_a_admin1_idx` (`id1` ASC) ,
  INDEX `fk_a_admin_i_post_i_post1_idx` (`id2` ASC) ,
  CONSTRAINT `fk_a_admin_i_post_a_admin1`
    FOREIGN KEY (`id1` )
    REFERENCES `a_admin` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_a_admin_i_post_i_post1`
    FOREIGN KEY (`id2` )
    REFERENCES `i_post` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-12-13 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_admin_i_post_i_zp';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';


$needle[$table]['_sql_']="
CREATE  TABLE IF NOT EXISTS `a_admin_i_post_i_zp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id1` INT NULL ,
  `id2` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_a_admin_i_post_i_zp_a_admin_i_post1_idx` (`id1` ASC) ,
  INDEX `fk_a_admin_i_post_i_zp_i_zp1_idx` (`id2` ASC) )
ENGINE = InnoDB


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='i_docs';
$col['id']['_type']='bigint(20)';
$col['a_menu_id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['file_name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

 
$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `i_docs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `a_menu_id` int(11) NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `file_name` text COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_i_docs_a_menu1_idx` (`a_menu_id` ASC) ,
  CONSTRAINT `fk_i_docs_a_menu1`
    FOREIGN KEY (`a_menu_id` )
    REFERENCES `a_menu` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)

 ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci  COMMENT='Документы для печати' AUTO_INCREMENT=13176 ;



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//


//************************************************************************// 2015-06-07 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='i_reklama';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

 
$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `i_reklama` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci  COMMENT='Рекламные компании' AUTO_INCREMENT=13176 ;



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//

//************************************************************************// 2015-06-18 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='i_tk';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['chk_cart']['_type']='tinyint(1)';
$col['chk_cart']['DEFAULT']="'0'";
$col['name']['_type']='text';
$col['i_contr_org_id']['_type']='bigint(20)';
$col['price']['_type']='float';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

 
$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `i_tk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `chk_cart` tinyint(1) NOT NULL DEFAULT '0',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `i_contr_org_id` bigint(20) NULL,
  `price` float NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_i_contr_org_has_i_tk_i_tk_idx` (`i_contr_org_id` ASC)
  

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Транспортные компании' AUTO_INCREMENT=13176 ;



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//

//************************************************************************// 2015-06-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='i_city';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='varchar(255)';
$col['us_name']['_type']='varchar(255)';
$col['region']['_type']='varchar(255)';
$col['nomer']['_type']='int(11)';
$col['nom_region']['_type']='int(11)';

 
$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `us_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nomer` int(11) NULL,
  `nom_region` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1108 ;



";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//



//************************************************************************// 2015-06-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  

$table='s_article';
$col['id']['_type']='bigint(20)';
$col['s_cat_id']['_type']='bigint(20)';
$col['i_contr_id']['_type']='bigint(20)';
$col['article']['_type']='text';
$col['kolvo']['_type']='float';
$col['price']['_type']='float';
$col['data_change']['_type']="timestamp";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `s_article` (
  `id` bigint(20) NOT NULL  AUTO_INCREMENT,
  `s_cat_id` bigint(20) NOT NULL COMMENT 'Наличие товара у поставщиков',
  `i_contr_id` bigint(20) NOT NULL,
  `article` text COLLATE utf8_unicode_ci NOT NULL,
  `kolvo` float NULL,
  `price` float NULL,
  `data_change` timestamp NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_s_aricle_s_cat1_idx` (`s_cat_id` ASC),
  INDEX `fk_s_aricle_i_contr1_idx` (`i_contr_id` ASC)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1108 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
/**/
//************************************************************************//



//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_post';
$col['id']['_type']='int(11)';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `m_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='market';
$col['id']['_type']='int(11)';


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `market` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_zakaz';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['data']['_type']='timestamp';
$col['data']['DEFAULT']="'0000-00-00 00:00:00'";
$col['i_tp_id']['_type']='int(11)';
$col['i_tp_id']['DEFAULT']="'1'";
$col['a_admin_id']['_type']='int(11)';
$col['a_admin_otvet_id']['_type']='int(11)';
$col['i_contr_id']['_type']='bigint(20)';
$col['i_contr_org_id']['_type']='bigint(20)';
$col['project_name']['_type']='VARCHAR(255)';
$col['status']['_type']="enum('В обработке','Частично выполнен','Отменен','Выполнен')";
$col['status']['DEFAULT']="'В обработке'";
$col['tip_pay']['_type']="enum('Оплата отключена', 'Робокасса', 'Яндекс-деньги кошелек', 'Яндекс-деньги карта', 'Счет на организацию', 'Наличными при получении')";
$col['tip_pay']['DEFAULT']="'Оплата отключена'";
$col['comments']['_type']='longtext';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_end']['_type']='datetime';
$col['data_end']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_done']['_type']='datetime';
$col['data_done']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_change']['_type']='datetime';
$col['data_change']['DEFAULT']="'0000-00-00 00:00:00'";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_zakaz` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `data` TIMESTAMP NULL ,
  `i_tp_id` INT(11) NULL DEFAULT '1',
  `a_admin_id` INT(11) NULL ,
  `a_admin_otvet_id` INT(11) NULL ,
  `i_contr_id` bigint(20) NULL ,
  `i_contr_org_id` bigint(20) NULL ,
  `project_name` VARCHAR(255) NULL ,
  `status` enum('В обработке','Частично выполнен','Отменен','Выполнен') NULL DEFAULT 'В обработке' ,
  `tip_pay` enum('Оплата отключена','Робокасса','Яндекс-деньги кошелек','Яндекс-деньги карта', 'Счет на организацию','Наличными при получении') NULL DEFAULT 'Оплата отключена',
  `comments` LONGTEXT NULL ,
  `html_code` LONGTEXT NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `data_end` datetime NULL DEFAULT '0000-00-00 00:00:00',
  `data_done` datetime NULL DEFAULT '0000-00-00 00:00:00',
  `data_change` datetime NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_zakaz_a_admin1_idx` (`a_admin_id` ASC) ,
  INDEX `fk_m_zakaz_i_contr1_idx` (`i_contr_id` ASC) ,
  INDEX `fk_m_zakaz_i_tp_idx` (`i_tp_id` ASC),
  INDEX `fk_m_zakaz_a_admin_otvet_idx` (`a_admin_otvet_id` ASC) ,
  INDEX `fk_m_zakaz_i_contr_org_idx` (`i_contr_org_id` ASC) ,
  CONSTRAINT `fk_m_zakaz_a_admin1`
    FOREIGN KEY (`a_admin_id` )
    REFERENCES `a_admin` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_m_zakaz_i_contr1`
    FOREIGN KEY (`i_contr_id` )
    REFERENCES `i_contr` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Заказы покупателей'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_postav';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['data']['_type']='timestamp';
$col['data']['DEFAULT']="'0000-00-00 00:00:00'";
$col['i_tp_id']['_type']='int(11)';
$col['a_admin_id']['_type']='int(11)';
$col['i_contr_id']['_type']='bigint(20)';
$col['i_contr_org_id']['_type']='bigint(20)';
$col['project_name']['_type']='VARCHAR(255)';
$col['status']['_type']="enum('В обработке','Отправлен','Доставлен','Отменен')";
$col['status']['DEFAULT']="'В обработке'";
$col['tip_pay']['_type']="enum('Оплата отключена','Робокасса','Яндекс-деньги кошелек','Яндекс-деньги карта', 'Счет на организацию','Наличными при получении')";
$col['tip_pay']['DEFAULT']="'Оплата отключена'";
$col['comments']['_type']='longtext';
$col['html_code']['_type']='longtext';
$col['control_num']['_type']='int(11)';
$col['control_sum']['_type']='float';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_end']['_type']='datetime';
$col['data_end']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_done']['_type']='datetime';
$col['data_done']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_change']['_type']='datetime';
$col['data_change']['DEFAULT']="'0000-00-00 00:00:00'";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_postav` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `data` TIMESTAMP NULL ,
  `i_tp_id` INT(11) NULL ,
  `a_admin_id` INT(11) NULL ,
  `i_contr_id` bigint(20) NULL ,
  `i_contr_org_id` bigint(20) NULL ,
  `project_name` VARCHAR(255) NULL ,
  `status` enum('В обработке','Отправлен','Доставлен','Отменен') NULL DEFAULT 'В обработке' ,
  `tip_pay` enum('Оплата отключена','Робокасса','Яндекс-деньги кошелек','Яндекс-деньги карта', 'Счет на организацию','Наличными при получении') NULL DEFAULT 'Оплата отключена',

  `comments` LONGTEXT NULL ,
  `html_code` LONGTEXT NULL ,
  `control_num` INT(11) NULL ,
  `control_sum` float NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `data_end` datetime NULL DEFAULT '0000-00-00 00:00:00', 
  `data_done`  datetime NULL DEFAULT '0000-00-00 00:00:00',
  `data_change` datetime NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_postav_a_admin1_idx` (`a_admin_id` ASC) ,
  INDEX `fk_m_postav_i_contr1_idx` (`i_contr_id` ASC) ,
  INDEX `fk_m_postav_i_contr_org_idx` (`i_contr_org_id` ASC) ,
  INDEX `fk_m_postav_i_tp_idx` (`i_tp_id` ASC) ,
  CONSTRAINT `fk_m_postav_a_admin1`
    FOREIGN KEY (`a_admin_id` )
    REFERENCES `a_admin` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_m_postav_i_contr1`
    FOREIGN KEY (`i_contr_id` )
    REFERENCES `i_contr` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Поступление товара'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-06-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_log';
$col['id']['_type']='bigint(20)';
$col['data_create']['_type']='timestamp';
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['a_admin_id']['_type']='int(11)';
$col['a_menu_id']['_type']='int(11)';
$col['id_z_p_p']['_type']='int(11)';
$col['m_log_type_id']['_type']='int(11)';
$col['text']['_type']='text';


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `a_admin_id` int(11) NULL,
  `a_menu_id` int(11) NULL,
  `id_z_p_p` int(11) NULL,
  `m_log_type_id` int(11) NULL,
  `text` text NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_log_a_admin_id1_idx` (`a_admin_id` ASC) ,
  INDEX `fk_m_log_a_menu_id1_idx` (`a_menu_id` ASC) ,
  INDEX `fk_m_log_id_z_p_p1_idx` (`id_z_p_p` ASC) ,
  INDEX `fk_m_log_m_log_type_id1_idx` (`m_log_type_id` ASC) 
)
ENGINE = InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Логи'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2018-03-15 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_log_type';
$col['id']['_type']='bigint(20)';
$col['name']['_type']='text';


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_log_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `name` text NULL ,
  PRIMARY KEY (`id`)  
)
ENGINE = InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Типы логов'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-06-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_dostavka';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['data']['_type']='timestamp';
$col['data']['DEFAULT']="'0000-00-00 00:00:00'";
$col['m_zakaz_id']['_type']='int(11)';
$col['m_postav_id']['_type']='int(11)';
$col['i_tk_id']['_type']='int(11)';
$col['fio']['_type']='text';
$col['index_']['_type']='int(11)';
$col['i_city_id']['_type']='int(11)';
$col['tracking_number']['_type']='text';
$col['adress']['_type']='text';
$col['phone']['_type']='varchar(255)';
$col['summa']['_type']='float';


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_dostavka` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `data` TIMESTAMP NULL ,
  `m_zakaz_id` int(11) NULL,
  `m_postav_id` int(11) NULL,
  `i_tk_id` int(11) NULL,
  `fio` text NULL ,
  `index_` int(11) NULL ,
  `i_city_id` int(11) NULL ,
  `tracking_number` text NULL ,
  `adress` text NULL ,
  `phone` varchar(255) NULL ,
  `summa` float NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_dostavka_m_zakaz1_idx` (`m_zakaz_id` ASC) ,
  INDEX `fk_m_dostavka_i_tk1_idx` (`i_tk_id` ASC) ,
  INDEX `fk_m_dostavka_i_city1_idx` (`i_city_id` ASC) ,
  CONSTRAINT `fk_m_dostavka_m_zakaz1`
    FOREIGN KEY (`m_zakaz_id` )
    REFERENCES `m_zakaz` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_m_dostavka_i_tk1`
    FOREIGN KEY (`i_tk_id` )
    REFERENCES `i_tk` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_m_dostavka_i_city1`
    FOREIGN KEY (`i_city_id` )
    REFERENCES `i_city` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Доставка товара'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-06-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_platezi';
$col['id']['_type']='int(11)';
$col['a_admin_id']['_type']='int(11)';
$col['data']['_type']="timestamp";
$col['i_scheta_id']['_type']='int(11)';
$col['summa']['_type']='float';
$col['tip']['_type']="ENUM('Дебет','Кредит')";
$col['tip']['DEFAULT']="'Кредит'";
$col['a_menu_id']['_type']='int(11)';
$col['a_menu_id']['DEFAULT']="'16'";
$col['id_z_p_p']['_type']='BIGINT(20)';
$col['comments']['_type']='longtext';
$col['ostatok']['_type']='float';
$col['a_admin_id_info']['_type']='int(11)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_platezi` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `a_admin_id` int(11) NULL ,
  `data` TIMESTAMP NULL ,
  `i_scheta_id` int(11) NULL ,
  `summa` float NULL ,
  `tip` ENUM('Дебет','Кредит') NULL DEFAULT 'Кредит' ,
  `a_menu_id` int(11) NULL DEFAULT '16' ,
  `id_z_p_p` BIGINT NULL ,
  `comments` LONGTEXT NULL,
  `ostatok` float NULL,
  `a_admin_id_info` int(11) NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_platezi_i_scheta1_idx` (`i_scheta_id` ASC) ,
  INDEX `fk_m_platezi_a_menu1_idx` (`a_menu_id` ASC) ,
  INDEX `fk_m_platezi_id_z_p_p1_idx` (`id_z_p_p` ASC),
  INDEX `fk_m_platezi_a_admin_id_info1_idx` (`a_admin_id_info` ASC),
  INDEX `fk_m_platezi_tip_idx` (`tip` ASC),
  INDEX `fk_m_platezi_data_idx` (`data` ASC) ,
  INDEX `fk_m_platezi_a_admin1_idx` (`a_admin_id` ASC) ,
  CONSTRAINT `fk_m_platezi_i_scheta1`
    FOREIGN KEY (`i_scheta_id` )
    REFERENCES `i_scheta` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_m_platezi_a_menu1`
    FOREIGN KEY (`a_menu_id` )
    REFERENCES `a_menu` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_m_platezi_a_admin1`
    FOREIGN KEY (`a_admin_id` )
    REFERENCES `a_admin` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Таблица платежей'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-06-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='l_m_platezi_remove';
$col['id']['_type']='int(11)';
$col['a_admin_id']['_type']='int(11)';
$col['data']['_type']="timestamp";
$col['i_scheta_id']['_type']='int(11)';
$col['summa']['_type']='float';
$col['tip']['_type']="ENUM('Дебет','Кредит')";
$col['tip']['DEFAULT']="'Кредит'";
$col['a_menu_id']['_type']='int(11)';
$col['a_menu_id']['DEFAULT']="'16'";
$col['id_z_p_p']['_type']='BIGINT(20)';
$col['comments']['_type']='longtext';
$col['ostatok']['_type']='float';
$col['a_admin_id_info']['_type']='int(11)';
$col['data_create']['_type']="timestamp";
$col['data_del']['_type']="timestamp";
$col['data_del']['DEFAULT']="CURRENT_TIMESTAMP";

  
$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `l_m_platezi_remove` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `a_admin_id` int(11) NULL ,
  `data` TIMESTAMP NULL ,
  `i_scheta_id` int(11) NULL ,
  `summa` float NULL ,
  `tip` ENUM('Дебет','Кредит') NULL DEFAULT 'Кредит' ,
  `a_menu_id` int(11) NULL DEFAULT '16' ,
  `id_z_p_p` BIGINT NULL ,
  `comments` LONGTEXT NULL,
  `ostatok` float NULL,
  `a_admin_id_info` int(11) NULL ,
  `data_create` TIMESTAMP NULL,
  `data_del` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_platezi_i_scheta1_idx` (`i_scheta_id` ASC) ,
  INDEX `fk_m_platezi_a_menu1_idx` (`a_menu_id` ASC) ,
  INDEX `fk_m_platezi_a_admin1_idx` (`a_admin_id` ASC),
  INDEX `fk_m_platezi_a_admin_id_info1_idx` (`a_admin_id_info` ASC),
  INDEX `fk_m_platezi_id_z_p_p_idx` (`id_z_p_p` ASC)
)
ENGINE = InnoDB 
DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
COMMENT = 'Логи удаленных платежей'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



//************************************************************************// 2014-12-05 ghostwolf616 // 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_zakaz_a_admin';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';
$col['chk_active']['_type']="tinyint(1)";
$col['chk_active']['DEFAULT']="'1'";


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `m_zakaz_a_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) NULL,
  `id2` int(11) NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `fk_m_zakaz_a_admin_i_id1_idx` (`id1` ASC) ,
  INDEX `fk_m_zakaz_a_admin_i_id2_idx` (`id2` ASC) 
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 ghostwolf616 // 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_zakaz_s_cat';
$col['id']['_type']='bigint(20)';
$col['m_zakaz_id']['_type']='int(11)';
$col['s_cat_id']['_type']='bigint(20)';
$col['kolvo']['_type']='float';
$col['price']['_type']='float';
$col['comments']['_type']='longtext';
$col['status_dostavki']['_type']="enum('Не заказан','В наличии у поставщика','Заказан','Доработка','Отложенная закупка','В наличии на складе')";
$col['status_dostavki']['DEFAULT']="'Не заказан'";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_zakaz_s_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `m_zakaz_id` int(11) NOT NULL ,
  `s_cat_id` bigint(20) NOT NULL ,
  `kolvo` FLOAT NULL ,
  `price` FLOAT NULL ,
  `comments` LONGTEXT NULL ,
  `status_dostavki` enum('Не заказан','В наличии у поставщика','Заказан','Доработка','Отложенная закупка','В наличии на складе') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Не заказан',
  
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_zakaz_s_cat_m_zakaz1_idx` (`m_zakaz_id` ASC) ,
  INDEX `fk_m_zakaz_s_cat_s_cat1_idx` (`s_cat_id` ASC) ,
  CONSTRAINT `fk_m_zakaz_s_cat_m_zakaz1`
    FOREIGN KEY (`m_zakaz_id` )
    REFERENCES `m_zakaz` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_m_zakaz_s_cat_s_cat1`
    FOREIGN KEY (`s_cat_id` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Товар в заказе из каталога'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 ghostwolf616 // 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_zakaz_s_cat_a_admin_i_post';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';
$col['summa']['_type']='float';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `m_zakaz_s_cat_a_admin_i_post` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  `summa` float NULL,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_m_zakaz_s_cat_a_admin_i_post_m_zakaz_s_cat1_idx` (`id1` ASC),
  INDEX `fk_m_zakaz_s_cat_a_admin_i_post_a_admin_i_post1_idx` (`id2` ASC)

    )
ENGINE = InnoDB
COMMENT = 'Оказанные услуги в заказе'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2014-12-05 ghostwolf616 // 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_postav_s_cat';
$col['id']['_type']='bigint(20)';
$col['m_postav_id']['_type']='int(11)';
$col['s_cat_id']['_type']='bigint(20)';
$col['kolvo']['_type']='float';
$col['price']['_type']='float';
$col['m_zakaz_id']['_type']='int(11)';
$col['comments']['_type']='longtext';


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_postav_s_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `m_postav_id` int(11) NOT NULL ,
  `s_cat_id` bigint(20) NOT NULL ,
  `kolvo` FLOAT NULL ,
  `price` FLOAT NULL ,
  `m_zakaz_id` int(11) NULL,
  `comments` LONGTEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_postav_s_cat_m_postav1_idx` (`m_postav_id` ASC) ,
  INDEX `fk_m_postav_s_cat_s_cat1_idx` (`s_cat_id` ASC) ,
  CONSTRAINT `fk_m_postav_s_cat_m_postav1`
    FOREIGN KEY (`m_postav_id` )
    REFERENCES `m_postav` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_m_postav_s_cat_s_cat1`
    FOREIGN KEY (`s_cat_id` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Товар в поступлении из каталога'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-11-12 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_tovar';
$col['id']['_type']='bigint(20)';
$col['i_tp_id']['_type']='int(11)';
$col['m_postav_s_cat_id']['_type']='bigint(20)';
/*
$col['status']['_type']="enum('В наличии','Продан','Брак')";
$col['status']['DEFAULT']="'В наличии'";
$col['kolvo']['_type']='float';
*/
$col['barcode']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
/*

  `status` ENUM('В наличии','Продан','Брак') NULL DEFAULT 'В наличии' ,
  `kolvo` float NULL ,
*/

$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_tovar` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `i_tp_id` INT NULL ,
  `m_postav_s_cat_id` BIGINT(20) NULL ,
  `barcode` text NULL ,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_tovar_i_tp1_idx` (`i_tp_id` ASC) ,
  INDEX `fk_m_tovar_m_postav_s_cat1_idx` (`m_postav_s_cat_id` ASC)
    )
ENGINE = InnoDB
COMMENT = 'Склад'
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2015-11-12 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_zakaz_s_cat_m_tovar';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';
$col['kolvo']['_type']='int(11)';
$col['kolvo']['DEFAULT']="'1'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_zakaz_s_cat_m_tovar` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `id1` BIGINT NULL ,
  `id2` BIGINT NULL ,
  `kolvo` int(11) NULL DEFAULT '1',
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_zakaz_s_cat_m_tovar_m_zakaz_s_cat1_idx` (`id1` ASC) ,
  INDEX `fk_m_zakaz_s_cat_m_tovar_m_tovar1_idx` (`id2` ASC)
  )
ENGINE = InnoDB
COMMENT = 'Проданный товар в заказе'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-06-11 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_a_admin_i_contr';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='bigint(20)';


$needle[$table]['_sql_']="

CREATE  TABLE IF NOT EXISTS `m_a_admin_i_contr` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `id1` int(11) NULL ,
  `id2` bigint(20) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_m_a_admin_i_contr_a_admin1_idx` (`id1` ASC) ,
  INDEX `fk_m_a_admin_i_contr_i_contr1_idx` (`id2` ASC) ,
  CONSTRAINT `fk_m_a_admin_i_contr_a_admin1`
    FOREIGN KEY (`id1` )
    REFERENCES `a_admin` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_m_a_admin_i_contr_i_contr1`
    FOREIGN KEY (`id2` )
    REFERENCES `i_contr` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;


";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************// 2014-12-05 ghostwolf616 // 2014-12-05 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='m_dialog';
$col['id']['_type']='bigint(20)';
$col['pid']['_type']='bigint(20)';
$col['sid']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['m_a_admin_i_contr_id1']['_type']='bigint(20)';
$col['m_a_admin_i_contr_id2']['_type']='bigint(20)';

$col['a_menu_id']['_type']='int(11)';
$col['row_id']['_type']='bigint(20)';


$col['m_zakaz_id']['_type']='int(11)';
$col['m_postav_id']['_type']='int(11)';
$col['s_cat_id']['_type']='bigint(20)';

$col['subject']['_type']='TEXT';
$col['message']['_type']='LONGTEXT';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['chk_in_out']['_type']="tinyint(1)";
$col['chk_view']['_type']="tinyint(1)";
$col['chk_view']['DEFAULT']="'0'";
$col['data_view']['_type']="timestamp";
$col['data_send_sms']['_type']="timestamp";
$col['data_send_email']['_type']="timestamp";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `m_dialog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) NULL,
  `sid` bigint(20) NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `m_a_admin_i_contr_id1` bigint(20) NULL,
  `m_a_admin_i_contr_id2` bigint(20) NULL,
  `a_menu_id` int(11) NULL,
  `row_id` bigint(20) NULL,
  `m_zakaz_id` int(11) NULL,
  `m_postav_id` int(11) NULL,
  `s_cat_id` bigint(20) NULL,
  `subject` TEXT COLLATE utf8_unicode_ci NULL,
  `message` LONGTEXT COLLATE utf8_unicode_ci NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chk_in_out` tinyint(1) NULL,
  `chk_view` tinyint(1) NOT NULL DEFAULT '0',
  `data_view` timestamp NOT NULL,
  `data_send_sms` timestamp NOT NULL,
  `data_send_email` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_m_a_admin_i_contr_id1_m_a_admin1_idx` (`m_a_admin_i_contr_id1` ASC) ,
  INDEX `fk_m_a_admin_i_contr_id2_i_contr1_idx` (`m_a_admin_i_contr_id2` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_m_zakaz_id_idx` (`m_zakaz_id` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_m_postav_id_idx` (`m_postav_id` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_s_cat_id_idx` (`s_cat_id` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_pid_idx` (`pid` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_sid_idx` (`sid` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_row_id_idx` (`row_id` ASC),
  INDEX `fk_m_a_admin_i_contr_id2_a_menu_id_idx` (`a_menu_id` ASC)
  
  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2015-10-11 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_contr_s_cat';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['i_contr_id']['_type']='bigint(20)';
$col['s_cat_id']['_type']='bigint(20)';
$col['html_code']['_type']='longtext';
$col['data_change']['_type']="timestamp";
$col['data_change']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `i_contr_s_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `i_contr_id` bigint(20) NULL,
  `s_cat_id` bigint(20) NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_change` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) ,
  INDEX `fk_i_contr_s_cat_i_contr_idx` (`i_contr_id` ASC) ,
  INDEX `fk_i_contr_s_cat_s_cat_idx` (`s_cat_id` ASC) ,
  CONSTRAINT `fk_i_contr_s_cat_i_contr_id1`
    FOREIGN KEY (`i_contr_id` )
    REFERENCES `i_contr` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_i_contr_s_cat_s_cat_id1`
    FOREIGN KEY (`s_cat_id` )
    REFERENCES `s_cat` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Избранные товары' AUTO_INCREMENT=1 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************// 2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='remont';
$col['id']['_type']='bigint(20)';

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `remont` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************//  2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_tip_oborud';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `r_tip_oborud` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_brend';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `r_brend` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_tip_brend';
$col['id']['_type']='bigint(20)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['r_tip_oborud_id']['_type']='bigint(20)';
$col['r_brend_id']['_type']='bigint(20)';
$col['name']['_type']='text';
$col['description']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `r_tip_brend` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NULL,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `r_tip_oborud_id` bigint(20) NULL,
  `r_brend_id` bigint(20) NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `k_r_tip_brend_r_tip_oborud_idx` (`r_tip_oborud_id` ASC),
  INDEX `k_r_tip_brend_r_brend_idx` (`r_brend_id` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_model';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";

$col['r_tip_oborud_id']['_type']="bigint(20)";
$col['r_brend_id']['_type']="bigint(20)";

$col['name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['pop']['_type']="bigint(20)";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="


CREATE TABLE IF NOT EXISTS `r_model` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `r_tip_oborud_id` bigint(20) NULL,
  `r_brend_id` bigint(20) NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `html_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `pop` bigint(20) NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `k_r_tip_oborud_id_has_r_tip_oborud_idx` (`r_tip_oborud_id` ASC),
  INDEX `k_r_brend_id_has_r_brend_idx` (`r_brend_id` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************//  2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_service';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['status']['_type']="enum('Принят','На диагностике','Согласование','Ожидание предоплаты','В работе','Ожидание запчастей','Готов','Отдан')";
$col['status']['DEFAULT']="'Принят'";
$col['data_priem']['_type']="timestamp";
$col['data_priem']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_inform']['_type']="timestamp";
$col['data_inform']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_vidachi']['_type']="timestamp";
$col['data_vidachi']['DEFAULT']="'0000-00-00 00:00:00'";
$col['a_admin_id']['_type']="int(11)";
$col['i_contr_id']['_type']="bigint(20)";
$col['m_zakaz_id']['_type']="bigint(20)";
$col['r_model_id']['_type']="bigint(20)";
$col['sn']['_type']='text';
$col['komplekt']['_type']='text';
$col['sost']['_type']='text';
$col['comments']['_type']='longtext';
$col['diagnoz']['_type']='longtext';
$col['chk_garant']['_type']='tinyint(1)';
$col['chk_garant']['DEFAULT']="'0'";
$col['r_service_id']['_type']='bigint(20)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `r_service` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('Принят','На диагностике','Согласование','Ожидание предоплаты','В работе','Ожидание запчастей','Готов','Отдан') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Принят',
  `data_priem` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_inform` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_vidachi` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `a_admin_id` int(11) NULL,
  `i_contr_id` bigint(20) NULL,
  `m_zakaz_id` bigint(20) NULL,
  `r_model_id` bigint(20) NULL,
  `sn` text COLLATE utf8_unicode_ci NOT NULL,
  `komplekt` text COLLATE utf8_unicode_ci NOT NULL,
  `sost` text COLLATE utf8_unicode_ci NOT NULL,
  `comments` longtext COLLATE utf8_unicode_ci NOT NULL,
  `diagnoz` longtext COLLATE utf8_unicode_ci NOT NULL,
  `chk_garant` tinyint(1) NOT NULL DEFAULT '0',
  `r_service_id` bigint(20) NULL,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `k_a_admin_id_has_a_admin_idx` (`a_admin_id` ASC),
  INDEX `k_i_contr_id_has_i_contr_idx` (`i_contr_id` ASC),
  INDEX `k_m_zakaz_id_has_m_zakaz_idx` (`m_zakaz_id` ASC),
  INDEX `k_r_model_id_has_r_model_idx` (`r_model_id` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-07-19 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_neispravnosti';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['r_tip_oborud_id']['_type']='bigint(20)';
$col['r_tip_oborud_id']['DEFAULT']="'0'";
$col['name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `r_neispravnosti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NULL DEFAULT '1',
  `r_tip_oborud_id` bigint(20) NULL DEFAULT '0',
  `name` text NULL,
  `html_code` longtext NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_r_neispravnosti_r_tip_oborud1_idx` (`r_tip_oborud_id` ASC)
) ENGINE = InnoDB

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-07-19 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_service_r_neispravnosti';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='int(11)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `r_service_r_neispravnosti` (
  `id` bigint(20) NOT NULL,
  `id1` bigint(20) NOT NULL,
  `id2` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_r_service_r_neispravnosti_r_service1_idx` (`id1` ASC),
  INDEX `fk_r_service_r_neispravnosti_r_neispravnosti1_idx` (`id2` ASC)
) ENGINE = InnoDB

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************//  2016-07-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_neispravnosti_s_struktura';
$col['id']['_type']='int(11)';
$col['id1']['_type']='int(11)';
$col['id2']['_type']='int(11)';

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `r_neispravnosti_s_struktura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) NOT NULL,
  `id2` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_r_neispravnosti_s_struktura_r_neispravnosti1_idx` (`id1` ASC),
  INDEX `fk_r_neispravnosti_s_struktura_s_struktura1_idx` (`id2` ASC)
) ENGINE = InnoDB
COMMENT = 'Таблица соответствия неисправностей товарам и услугам из структуры'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2015-10-10 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='r_model_s_cat';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `r_model_s_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NULL,
  `id2` bigint(20) NULL,
  PRIMARY KEY (`id`),
  INDEX `k_r_model_s_cat_id_has_r_model_idx` (`id1` ASC),
  INDEX `k_r_model_s_cat_id_has_s_cat_idx` (`id2` ASC)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6312 ;

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-04-07 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_rashodi';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_rashodi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NULL DEFAULT 1,
  `name` text NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Таблица наименований расходов'

";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-04-07 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_inout';
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="

CREATE TABLE IF NOT EXISTS `i_inout` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `chk_active` TINYINT(1) NULL DEFAULT 1,
  `name` TEXT NULL,
  `data_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Ввод/вывод на счет'
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-04-21 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='l_s_cat_pop';
$col['id']['_type']='bigint(20)';
$col['s_cat_id']['_type']='bigint(20)';

$col['name']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `l_s_cat_pop` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `s_cat_id` bigint(20) NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_l_s_cat_pop_s_cat1_idx` (`s_cat_id` ASC))
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-09-30 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='c_call_client';
$col['id']['_type']='bigint(20)';
$col['a_admin_id']['_type']='int(11)';
$col['i_contr_id']['_type']='bigint(20)';
$col['m_zakaz_id']['_type']='int(11)';
$col['comments']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_change']['_type']="timestamp";
$col['a_admin_id_change']['_type']='int(11)';

$needle[$table]['_sql_']="
CREATE  TABLE IF NOT EXISTS `c_call_client` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `a_admin_id` int(11) NULL ,
  `i_contr_id` bigint(20) NULL ,
  `m_zakaz_id` int(11) NULL ,
  `comments` longtext NULL ,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP ,
  `data_change` timestamp NULL ,
  `a_admin_id_change` int(11) NULL,
  PRIMARY KEY (`id`) ,
  INDEX `fk_c_call_client_a_admin1_idx` (`a_admin_id` ASC) ,
  INDEX `fk_c_call_client_i_contr2_idx` (`i_contr_id` ASC),
  INDEX `fk_c_call_client_m_zakaz_id2_idx` (`m_zakaz_id` ASC),
  INDEX `fk_c_call_client_a_admin_id_change2_idx` (`a_admin_id_change` ASC))
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-09-30 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='c_questions'; // столбцы с вопросами
$col['id']['_type']='int(11)';
$col['sid']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['chk_required']['_type']='tinyint(1)';
$col['chk_required']['DEFAULT']="'0'";
$col['a_col_id']['_type']='int(11)';
$col['tip']['_type']="enum('Одно значение', 'Несколько значений')";
$col['tip']['DEFAULT']="'Одно значение'";
$col['comments']['_type']='longtext';

$needle[$table]['_sql_']="
CREATE  TABLE IF NOT EXISTS `c_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `sid` int(11) NOT NULL,
  `chk_active` tinyint(1) NULL DEFAULT '1' ,
  `chk_required` TINYINT(1) NULL DEFAULT 0,
  `a_col_id` int(11) NOT NULL,
  `tip` enum('Одно значение', 'Несколько значений') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Одно значение',
  `comments` longtext NULL,
  PRIMARY KEY (`id`) ,
  INDEX `fk_c_questions_a_col1_idx` (`a_col_id` ASC)
  )
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-09-30 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='c_call_answer'; // ответы на вопросы
$col['id']['_type']='bigint(20)';
$col['c_call_client_id']['_type']='bigint(20)';
$col['c_questions_id']['_type']='int(11)';
$col['id_z_p_p']['_type']='bigint(20)';
$col['comments']['_type']='longtext';


$needle[$table]['_sql_']="
CREATE  TABLE IF NOT EXISTS `c_call_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,
  `c_call_client_id` bigint(20) NOT NULL ,
  `c_questions_id` int(11) NOT NULL ,
  `id_z_p_p` bigint(20) NULL ,
  `comments` longtext NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_c_call_answer_c_call_client1_idx` (`c_call_client_id` ASC) ,
  INDEX `fk_c_call_answer_c_questions1_idx` (`c_questions_id` ASC),
  INDEX `fk_c_call_answer_id_z_p_p_idx` (`id_z_p_p` ASC))
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-09-30 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='call'; // звонки
$col['id']['_type']='bigint(20)';


$needle[$table]['_sql_']="
CREATE  TABLE IF NOT EXISTS `c_call_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT ,

  PRIMARY KEY (`id`))
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-09-30 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_call_target'; // звонки
$col['id']['_type']='int(11)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `i_call_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NULL DEFAULT 1,
  `name` text NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'Цель звонка'
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2016-10-28 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='l_r_model_pop';
$col['id']['_type']='bigint(20)';
$col['r_model_id']['_type']='bigint(20)';

$col['name']['_type']='text';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `l_r_model_pop` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `r_model_id` bigint(20) NULL,
  `name` text NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_l_r_model_pop_r_model1_idx` (`r_model_id` ASC))
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//


//************************************************************************//  2017-06-12 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='i_tp_s_cat_price';
$col['id']['_type']='bigint(20)';
$col['i_tp_id']['_type']='int(11)';
$col['s_cat_id']['_type']='bigint(20)';
$col['price']['_type']='float';
$col['a_admin_id']['_type']='int(11)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `i_tp_s_cat_price` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `i_tp_id` int(11) NULL,
  `s_cat_id` bigint(20) NULL,
  `price` FLOAT NULL,
  `a_admin_id` int(11) NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_l_i_tp_s_cat_price_i_tp_id1_idx` (`i_tp_id` ASC),
  INDEX `fk_l_i_tp_s_cat_price_s_cat_id1_idx` (`s_cat_id` ASC),
  INDEX `fk_l_i_tp_s_cat_price_a_admin_id1_idx` (`a_admin_id` ASC)
  )
ENGINE = InnoDB
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//



//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['s_struktura_id']['_type']='int(11)';
$col['chk_reg']['_type']='tinyint(1)';
$col['chk_reg']['DEFAULT']="'1'";
$col['chk_rand_quest']['_type']='tinyint(1)';
$col['chk_rand_quest']['DEFAULT']="'0'";
$col['chk_rand_answer']['_type']='tinyint(1)';
$col['chk_rand_answer']['DEFAULT']="'0'";
$col['cnt_try']['_type']='int(11)';
$col['cnt_quest']['_type']='int(11)';
$col['data_start']['_type']="timestamp";
$col['data_start']['DEFAULT']="'0000-00-00 00:00:00'";
$col['data_end']['_type']="timestamp";
$col['data_end']['DEFAULT']="'0000-00-00 00:00:00'";
$col['time_for_test']['_type']="datetime";
$col['time_for_test']['DEFAULT']="'0'";
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NULL DEFAULT '1',
  `name` text NULL,
  `s_struktura_id` int(11) NOT NULL,
  `chk_reg` tinyint(1) NULL DEFAULT '1',
  `chk_rand_quest` tinyint(1) NULL DEFAULT '0',
  `chk_rand_answer` tinyint(1) NULL DEFAULT '0',
  `cnt_try` int(11) NULL,
  `cnt_quest` int(11) NULL,
  `data_start` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `data_end` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `time_for_test` datetime NULL DEFAULT '0000-00-00 00:00:00',
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_s_test_options_s_struktura_s_struktura1_idx` (`s_struktura_id` ASC)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test_quest';
$col['id']['_type']='bigint(20)';
$col['chk_active']['_type']='tinyint(1)';
$col['chk_active']['DEFAULT']="'1'";
$col['name']['_type']='text';
$col['chk_tip']['_type']='tinyint(1)';
$col['chk_tip']['DEFAULT']="'1'";
$col['html_code']['_type']='longtext';
$col['a_admin_id']['_type']='int(11)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test_quest` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_active` tinyint(1) NULL DEFAULT '1',
  `name` TEXT NULL,
  `chk_tip` tinyint(1) NULL DEFAULT '1' COMMENT 'один (0) или несколько (1) правильных ответов',
  `html_code` longtext NULL,
  `a_admin_id` int(11) NOT NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_s_test_a_admin1_idx` (`a_admin_id` ASC)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test_s_test_quest';
$col['id']['_type']='bigint(20)';
$col['id1']['_type']='bigint(20)';
$col['id2']['_type']='bigint(20)';

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test_s_test_quest` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id1` bigint(20) NOT NULL,
  `id2` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_s_test_has_s_struktura_s_test1_idx` (`id2` ASC),
  INDEX `fk_s_test_s_test_quest_s_test1_idx` (`id1` ASC)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test_answer';
$col['id']['_type']='bigint(20)';
$col['chk_true']['_type']='tinyint(1)';
$col['chk_true']['DEFAULT']="'0'";
$col['s_test_quest_id']['_type']='int(11)';
$col['name']['_type']='text';
$col['html_code']['_type']='longtext';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chk_true` tinyint(1) NULL DEFAULT '0',
  `s_test_quest_id` int(11) NOT NULL,
  `name` text NULL,
  `html_code` longtext NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_s_test_answer_s_test1_idx` (`s_test_quest_id` ASC)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test_i_contr';
$col['id']['_type']='bigint(20)';
$col['s_test_id']['_type']='bigint(20)';
$col['i_contr_id']['_type']='bigint(20)';

$col['data_start']['_type']="timestamp";
$col['data_start']['DEFAULT']="CURRENT_TIMESTAMP";
$col['data_end']['_type']="timestamp";
$col['data_end']['DEFAULT']="'0000-00-00 00:00:00'";
$col['comments']['_type']='longtext';

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test_i_contr` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `s_test_id` bigint(20) NOT NULL,
  `i_contr_id` bigint(20) NOT NULL,
  `data_start` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_end` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `comments` longtext NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_s_test_i_contr_s_struktura_i_contr1_idx` (`i_contr_id` ASC),
  INDEX `fk_s_test_i_contr_s_test1_idx` (`s_test_id` ASC)
)
ENGINE = InnoDB
COMMENT = 'Параметры каждого теста для каждого пользоателя';
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test_i_contr_s_test_quest';
$col['id']['_type']='bigint(20)';
$col['s_test_i_contr_id']['_type']='bigint(20)';
$col['s_test_quest_id']['_type']='bigint(20)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test_i_contr_s_test_quest` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `s_test_i_contr_id` bigint(20) NOT NULL,
  `s_test_quest_id` bigint(20) NOT NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_table1_s_test_i_contr1_idx` (`s_test_i_contr_id` ASC),
  INDEX `fk_table1_s_test_quest1_idx` (`s_test_quest_id` ASC)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//

//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='s_test_i_contr_s_test_quest_s_test_answer';
$col['id']['_type']='bigint(20)';
$col['s_test_i_contr_s_test_quest_id']['_type']='bigint(20)';
$col['s_test_answer_id']['_type']='bigint(20)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `s_test_i_contr_s_test_quest_s_test_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `s_test_i_contr_s_test_quest_id` bigint(20) NOT NULL,
  `s_test_answer_id` bigint(20) NOT NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_table1_s_test_i_contr_s_test_quest1_idx` (`s_test_i_contr_s_test_quest_id` ASC),
  INDEX `fk_table1_s_test_answer1_idx` (`s_test_answer_id` ASC)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//




//************************************************************************//  2019-03-01 toowin86
///////////////////
///  || || ||  ////
///  \/ \/ \/  ////
///////////////////  
$table='a_redirect';
$col['id']['_type']='bigint(20)';
$col['link_in']['_type']='longtext';
$col['link_out']['_type']='longtext';
$col['kol']['_type']='bigint(20)';
$col['data_create']['_type']="timestamp";
$col['data_create']['DEFAULT']="CURRENT_TIMESTAMP";

$needle[$table]['_sql_']="
CREATE TABLE IF NOT EXISTS `a_redirect` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `link_in` longtext NULL,
  `link_out` longtext NULL,
  `kol` bigint(20) NOT NULL,
  `data_create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB;
";

///////////////////
///  /\ /\ /\  //// 
///  || || ||  ////  
///////////////////  
include 'install_inc.php';
//************************************************************************//
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
echo '<hr />';


// перебор таблиц
$sql = "SELECT 
            TABLE_NAME,
            TABLE_COLLATION
            
                FROM information_schema.tables 
                WHERE TABLE_SCHEMA = '$table_schema'"; 
$res = mysql_query($sql); if (!$res) {echo $sql;}
$current=array();
for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
{
    $current[$myrow[0]]['_coding_']['name']=$myrow[1];
    // добавление столбцов
    $sql_col = "SELECT 
                    COLUMN_NAME,
                    COLUMN_TYPE,
                    COLLATION_NAME,
                    COLUMN_DEFAULT
            
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = '$table_schema'
                AND TABLE_NAME='$myrow[0]'"; 
    $res_col = mysql_query($sql_col);
    for ($myrow_col = mysql_fetch_array($res_col); $myrow_col==true; $myrow_col = mysql_fetch_array($res_col))
    {
       $current[$myrow[0]]['col'][$myrow_col[0]]['_type_']=$myrow_col[1];
       $current[$myrow[0]]['col'][$myrow_col[0]]['_coding_']=$myrow_col[2];
       $current[$myrow[0]]['col'][$myrow_col[0]]['_def_']=$myrow_col[3];
    }
    
}
// Проверка таблиц
foreach ($needle as $table_name => $table_arr) {
    if (!isset($current[$table_name])){
        echo '<strong style="color:#909">NONE TABLE:</strong> НЕТ ТАБЛИЦЫ <strong>'.$table_name.'</strong> <br />';
    }
}
foreach ($current as $table_name => $table_arr) {
    if (!isset($needle[$table_name])){
        echo '<strong style="color:#505">OTHER TABLE:</strong> ПРИСУТСТВУЕТ ТАБЛИЦА ИЗ НЕСТАНДАРТНЫХ <strong>'.$table_name.'</strong> <br />';
    }
}
echo '<hr />';

// СРАВНЕНИЕ МАССИВОВ
foreach ($needle as $table_name => $table_arr) {
    if (isset($current[$table_name]))
    {
        // проверка кодировки
        if ($current[$table_name]['_coding_']['name']!=$needle[$table_name]['_coding_']['name'])
        {
            echo '<strong style="color:#009">EDIT:</strong> ...КОДИРОВКА НЕ СООТВЕТСТВУЕТ <strong>'.$current[$table_name]['_coding_']['name'].' ('.$needle[$table_name]['_coding_']['name'].')</strong> - меняем...<br />';
            if (mysql_query($needle[$table_name]['_coding_']['_sql_'])){echo '<strong style="color:#090">OK:</strong> ...КОДИРОВКА УСПЕШНО ЗАМЕНЕНА: '.$needle[$table_name]['_coding_']['_sql_'].'<br />';}
            else {echo '<strong style="color:#900">ERROR:</strong> ...ОШИБКА ЗАМЕНЫ КОДИРОВКИ: '.$needle[$table_name]['_coding_']['_sql_'].'<br />';}
        }
        //col
        foreach ($needle[$table_name]['col'] as $col => $col_arr) {
        if (isset($current[$table_name]['col'][$col]))
        {
            // проверка кодировки столбцов
            
            
            if ($current[$table_name]['col'][$col]['_coding_']!=str_replace("CHARACTER SET utf8 COLLATE ","",$needle[$table_name]['col'][$col]['_coding_'])
            and (strstr($needle[$table_name]['col'][$col]['_type_'],'char')==true 
            or strstr($needle[$table_name]['col'][$col]['_type_'],'text')==true ) ){
                    echo '<strong style="color:#009">EDIT:</strong> ...CHARACTER НЕ СООТВЕТСТВУЕТ: <strong>'
                        .$current[$table_name]['col'][$col]['_coding_'].' != '.str_replace("CHARACTER SET utf8 COLLATE ","",$needle[$table_name]['col'][$col]['_coding_'])
                        .' -> '.'</strong> меняем...<br />';
                    
                    if (mysql_query($needle[$table_name]['col'][$col]['_sql_upp'])){
                        //echo '<strong style="color:#090">OK:</strong> ... ...СТОЛБЦЕЦ УСПЕШНО ИЗМЕНЕН <strong>'.$col.'</strong> '.$needle[$table_name]['col'][$col]['_type_'].', '.str_replace("CHARACTER SET utf8 COLLATE ","",$needle[$table_name]['col'][$col]['_coding_']).', '.$needle[$table_name]['col'][$col]['_def_'].'<br />';
                    } else {echo '<strong style="color:#900">ERROR:</strong> ... ...ОШИБКА ЗАПРОСА ПРИ ДОБАВЛЕНИИ СТОЛБЦА <strong>'.$col.'</strong> - sql: '.$needle[$table_name]['col'][$col]['_sql_upp'].'<br />';}
             
                }
            
            if (mb_strtolower($current[$table_name]['col'][$col]['_type_'],'utf-8')!=mb_strtolower($needle[$table_name]['col'][$col]['_type_'],'utf-8'))
            {
                echo '<strong style="color:#009">EDIT:</strong> ..._type_ НЕ СООТВЕТСТВУЕТ: <strong>'
                   .'"' .mb_strtolower($current[$table_name]['col'][$col]['_type_'],'utf-8').'" != "'.mb_strtolower($needle[$table_name]['col'][$col]['_type_'],'utf-8').'"  '
                    .' -> '.'</strong> меняем...<br />';
                
                if (mysql_query($needle[$table_name]['col'][$col]['_sql_upp'])){
                    //echo '<strong style="color:#090">OK:</strong> ... ...СТОЛБЦЕЦ УСПЕШНО ИЗМЕНЕН <strong>'.$col.'</strong> '.$needle[$table_name]['col'][$col]['_type_'].', '.str_replace("CHARACTER SET utf8 COLLATE ","",$needle[$table_name]['col'][$col]['_coding_']).', '.$needle[$table_name]['col'][$col]['_def_'].'<br />';
                } else {echo '<strong style="color:#900">ERROR:</strong> ... ...ОШИБКА ЗАПРОСА ПРИ ДОБАВЛЕНИИ СТОЛБЦА <strong>'.$col.'</strong> - sql: '.$needle[$table_name]['col'][$col]['_sql_upp'].'<br />';}
             
            }
            if(trim($current[$table_name]['col'][$col]['_def_'])=='0000-00-00 00:00:00'){$current[$table_name]['col'][$col]['_def_']='';}
            if(str_replace("default ","",mb_strtolower(str_replace("'","",$needle[$table_name]['col'][$col]['_def_'])))=='0000-00-00 00:00:00'){$needle[$table_name]['col'][$col]['_def_']='';} 
            
            if (mb_strtolower($current[$table_name]['col'][$col]['_def_'],'utf-8')!= str_replace("default ","",mb_strtolower(str_replace("'","",$needle[$table_name]['col'][$col]['_def_']),'utf-8')))
            {
                echo '<strong style="color:#009">EDIT:</strong> ..._def_ НЕ СООТВЕТСТВУЕТ: <strong>'.
                ($current[$table_name]['col'][$col]['_def_']).' != '.str_replace("default ","",(str_replace("'","",$needle[$table_name]['col'][$col]['_def_']))).' -> '.'</strong> меняем...<br />';
                
                if (mysql_query($needle[$table_name]['col'][$col]['_sql_upp'])){
                    //echo '<strong style="color:#090">OK:</strong> ... ...СТОЛБЦЕЦ УСПЕШНО ИЗМЕНЕН <strong>'.$col.'</strong> '.$needle[$table_name]['col'][$col]['_type_'].', '.str_replace("CHARACTER SET utf8 COLLATE ","",$needle[$table_name]['col'][$col]['_coding_']).', '.$needle[$table_name]['col'][$col]['_def_'].'<br />';
                } else {echo '<strong style="color:#900">ERROR:</strong> ... ...ОШИБКА ЗАПРОСА ПРИ ДОБАВЛЕНИИ СТОЛБЦА <strong>'.$col.'</strong> - sql: '.$needle[$table_name]['col'][$col]['_sql_upp'].'<br />';}
             
            }
            // проверка типа столбцов
        } else {
                echo '<strong style="color:#099">CREATE:</strong> ...СТОЛБЦЕЦ ОТСУТСТВУЕТ <strong>'.$col.'</strong> - создаем...<br />';
                if (mysql_query($needle[$table_name]['col'][$col]['_sql_ins'])){
                    echo '<strong style="color:#090">OK:</strong> ... ...СТОЛБЦЕЦ УСПЕШНО ДОБАВЛЕН <strong>'.$col.'</strong> - sql: '.$needle[$table_name]['col'][$col]['_sql_ins'].'<br />';
                } else {echo '<strong style="color:#900">ERROR:</strong> ... ...ОШИБКА ЗАПРОСА ПРИ ДОБАВЛЕНИИ СТОЛБЦА <strong>'.$col.'</strong> - sql: '.$needle[$table_name]['col'][$col]['_sql_ins'].'<br />';
            }
        }
        }//end col
    } else{
        echo '<strong style="color:#099">CREATE:</strong> ТАБЛИЦА ОТСУТСТВУЕТ <strong>'.$table_name.'</strong> - создание... <br />';
        if(!mysql_query($needle[$table_name]['_sql_'])){echo '<br /><br />'.$needle[$table_name]['_sql_'].'<br /><br />'; mysql_query($needle[$table_name]['_sql_']) or die (mysql_error());}
    }
}
echo '<hr />';
//********************************************************************************************************








$arr=array();
    $arr['ADMIN: TIMESTAMP']=$update;

    $arr['secret_key']=generate_code(15);
    $arr['email FROM']=_GP('email_from','support@v-web.ru');
    $arr['email администратора']=_GP('email','toowin86@yandex.ru');
    $arr['SMTP: сервер']=_GP('smtp_server','mail.nic.ru');
    $arr['SMTP: порт']=_GP('smtp_port','25');
    $arr['SMTP: login']=_GP('smtp_login','krassupport@v-web.ru');
    $arr['SMTP: password']=_GP('smtp_password','1986689119AA');
    $arr['MESS: восстановление пароля']='<p><strong>Сайт:</strong> <a href="http://@@site@@">@@site@@</a></p><p><strong>Ваш логин:</strong> <a href="mailto:@@login@@">@@login@@</a></p><p><a style="background-repeat:repeat-x;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) #A2A2A2;border-image:none;border-radius:4px;border-style:solid;border-width:1px;box-shadow:0 1px 0 rgba(255,255,255,0.2) inset,0 1px 2px rgba(0,0,0,0.05);color:#333333;cursor:pointer;display:inline-block;font-size:16px;line-height:20px;margin:3px;padding:4px 12px;text-align:center;text-shadow:0 1px 1px rgba(255,255,255,0.75);vertical-align:middle;text-decoration:none;-moz-border-bottom-colors:none;-moz-border-left-colors:none;-moz-border-right-colors:none;-moz-border-top-colors:none;background-color:#F5F5F5;background-image:linear-gradient(to bottom,#FFFFFF,#E6E6E6);" href="@@link@@" target="_blank">ВОЙТИ в админ-панель: @@site@@</a></p>';
    
    $arr['YANDEX: login']=_GP('ya_login','ya.v-web@yandex.ru');
    $arr['YANDEX: password']=_GP('ya_password','1986689119AA');
    
    $arr['Количество загружаемых строк']='100';
    $arr['Количество строк в слайдере']='15';
    $arr['Высота миниатюры']='300';
    $arr['Ширина миниатюры']='300';
    $arr['Сайт активен']='1';
    
    $arr['Регистрация: email-0/sms-1']='0';
    $arr['Регистрация: пароль вводит пользователь-0/генерируется автоматом-1']='0';
    $arr['Регистрация: без подтверждения-0/с подтверждением-1']='0';
    $arr['Регистрация: данные - фамилия']='0';
    $arr['Регистрация: данные - имя']='0';
    $arr['Регистрация: данные - отчество']='0';
    $arr['Регистрация: данные - email']='1';
    $arr['Регистрация: данные - телефон']='0';
    $arr['Регистрация: данные - адрес']='0';
    $arr['Регистрация: платная регистрация']='0';
    $arr['Регистрация: платная авторизация']='0';
    
    $arr['smsintel: login']='';
    $arr['smsintel: password']='';
    
    $arr['Регистрация: MESS: отправка кода подтверждения на email']='<h2>Код подтверждения: <strong>@@code@@</strong>.</h2><p>Для продолжения регистрации введите код в поле &quot;Код подтверждения&quot;.</p>';
    $arr['Регистрация: MESS: отправка кода подтверждения на телефон']='Код: @@code@@. Введите данный код для продолжения регистрации.';
    $arr['Регистрация: MESS: отправка оповещения об успешной регистрации на email']='<h3>Вы успешно зарегистрированы на сайте!</h3><p>Ваш логин: <strong>@@email@@</strong>.</p><p>Ваш пароль: <strong>@@password@@</strong>.</p><p><a href="@@link@@">Ссылка для входа</a></p>';
    $arr['Регистрация: MESS: отправка оповещения об успешной регистрации на телефон']='Пользователь успешно зарегистрирован! Логин: @@phone@@, пароль: @@password@@';
    $arr['Регистрация: MESS: отправка сообщения с восстановленным паролем на email']='Восстановление пароля. Логин: @@email@@, пароль: @@password@@';
    
    $arr['Доска объявлений: MESS: добавление нового объявления']='<h3>Добавлено новое объявление №@@id@@:</h3><table border="0" cellpadding="5" cellspacing="0" style="width: 100%;"><tbody><tr><td colspan="3" rowspan="1"><a href="http://@@link@@"><strong>@@заголовок@@</strong></a></td><td style="text-align: right;"><a href="http://@@link_del@@"><img alt="Удалить" src="http://@@site@@/upload/image/mess_close.png" style="width: 40px; height: 40px;" title="Удалить" /></a></td></tr><tr><td colspan="3">@@раздел@@</td><td rowspan="5">@@фото@@</td></tr><tr><td colspan="3" rowspan="3">@@текст@@</td></tr><tr></tr><tr></tr><tr><td>@@email@@</td><td>@@телефон@@</td><td>@@имя@@</td></tr></tbody></table>';
    
    
    $arr['Корзина: доставка']='1';
    $arr['Корзина: доставка: транспортная компания']='1';
    $arr['Корзина: доставка: транспортная компания: по умолчанию']='Самовывоз';
    $arr['Корзина: доставка: стоимость доставки включается в заказ']='0';
    $arr['Корзина: доставка: ФИО']='1';
    $arr['Корзина: доставка: индекс']='1';
    $arr['Корзина: доставка: адрес']='1';
    $arr['Корзина: доставка: телефон']='1';
    $arr['Корзина: доставка: город']='1';
    $arr['Корзина: доставка: комментарии']='1';
    $arr['Корзина: доставка: карта']='';
    
    $arr['Корзина: оплата']='1';
    $arr['Корзина: оплата: робокасса']='1';
    $arr['Корзина: оплата: яндекс-деньги кошелек']='1';
    $arr['Корзина: оплата: яндекс-деньги карта']='1';
    $arr['Корзина: оплата: счет на организацию']='1';
    $arr['Корзина: оплата: наличными при получении']='1';
    $arr['Корзина: оплата: по умолчанию']='Корзина: оплата: яндекс-деньги карта';
    $arr['Корзина: оплата: номер счета кошелька Яндекс-денег']='4100145224562';
    $arr['Корзина: оплата: сайт для iframe']='chistka23.ru';
    $arr['Корзина: оплата: Яндекс-денеги HTTP-уведомления: секрет']='';
    $arr['Заказ: MESS: отправка счета - email']='<h2>Счет №@@nomer@@ от @@data@@</h2><p>Покставщик: <strong>@@postavchik@@</strong>.</p><p>Покупатель: <strong>@@pokupatel@@</strong>.</p>
<p>На сумму: <strong>@@sum@@</strong> рублей.</p><hr /><h3>Товар:</h3><div>@@table@@</div>';
    
    $arr['Заказ: MESS: успешное оформление заказа - sms на телефон']='Заказ @@nomer@@ успешно оформлен! @@host@@';
    $arr['Заказ: MESS: успешное оформление заказа - email']='<h1>Заказ №@@nomer@@ от @@date@@</h1>
        <p>Выбран способ оплаты: @@$txt_pay@@</p>
        <h2>Товар/услуги в заказе:</h2>
        @@table@@
        <p><a href="http://@@host@@/?com=m_cart&login=@@login@@&password=@@password@@&link=@@link_lk@@">Ссылка на вход в личный кабинет</a></p>
        <p style="text-align:right;">С Уважением, <a href="http://@@host@@">@@firm@@</a></p>';
    
    $arr['Корзина: товар: отображать количество']='1';
    $arr['Корзина: товар: отображать описание']='1';
    $arr['Корзина: товар: отображать артикл']='0';
    $arr['Корзина: MESS: быстрая покупка']='<p><a href="@@url@@">@@tovar@@</a></p><p>Покупатель: @@i_contr@@</p>';
    

$arr['Регистрация: MESS: отправка сообщения с восстановленным паролем на телефон']='Новый пароль: @@password@@. @@site@@';
$arr['Корзина: оплата: робокасса: login']='';
$arr['Корзина: оплата: робокасса: password1']='';
$arr['Корзина: оплата: робокасса: password2']='';
 
 
 
    $arr['Онлайн-заказ: доставка: ФИО']='1';
    $arr['Онлайн-заказ: доставка: индекс']='1';
    $arr['Онлайн-заказ: доставка: адрес']='1';
    $arr['Онлайн-заказ: доставка: телефон']='1';
    $arr['Онлайн-заказ: доставка: email']='1';
    $arr['Онлайн-заказ: доставка: город']='1';
    $arr['Онлайн-заказ: доставка: комментарии']='1';
    $arr['Онлайн-заказ: доставка: дата записи']='0';
    $arr['Доска объявлений: время жизни объявления']='30';
    $arr['Поступление: доставка: среднее время доставки товара, дней']='25';
    $arr['Русский URL']=0;
 
$arr['Google captcha secret']='6LfUKQ0TAAAAAIyWN4gItVEJnGnHNu4wXITxQu6g';
$arr['Пароль для удаления платежей']='19866891';
$arr['Google идентификатор клиента - для Google Drive']='';
$arr['Google Client Secret - для Google Drive']='';
$arr['Google Redirect Uri - для Google Drive']='';
$arr['Google Drive - дата последней синхронизации звонков']='';
$arr['Google Drive - email google для синхронизации контактов и диска']='default';
$arr['Google API - синхронизировать контакты с Google']=0;
$arr['Добавить в url город (филиал)']=0;
$arr['Корзина: быстрая покупка']=1;
$arr['Google Captcha secret']='';
$arr['Поступление: размещение: Номер поступления']='Блок инфо';//Или Основной блок
$arr['Поступление: размещение: Сумма поступления']='Блок инфо';//Или Основной блок
$arr['Название контрагента для инвентаризации']='Инвентаризация';//инвентаризации
$arr['Дедлайн для новых заказов, часов']='24';//Дедлайн для новых заказов//часов
$arr['Ограничить количество товаров в корзине по наличию в каталоге']='1';//Ограничить количество товаров в корзине по каталогу
$arr['Удалять свойства не относящиеся ни к одному товару']='0';//
$arr['Версия для слабовидящих']='0';//


$arr['SEO: Добавлять город в заголовок в конец']='0';//    
$arr['SEO: Добавлять город в заголовок в конец (структура)']='0';//    
$arr['SEO: Добавлять купить для товаров в заголовок в конец']='0';//
$arr['SEO: Добавлять заказать для товаров в заголовок в конец']='0';//    
$arr['SEO: Добавлять заказать для услуг в заголовок в конец']='0';//    
$arr['SEO: Добавлять цену товара или услуги в конец заголовка']='0';//    
$arr['SEO: Добавлять основные свойства товара или услуги в конец заголовка']='0';//    
$arr['HTTPS']='0';//  
$arr['Ключ API: Яндекс Геодекодер']='';// 
    
//Корзина: оплата: Яндекс-денеги HTTP-уведомления: секрет
$kol=ins_opt($arr);
$cl_='#999';if ($kol>0){$kol='<strong>'.$kol.'</strong>';$cl_='#090';}
echo '<p style="color:'.$cl_.';">Добавлено '.$kol.' опций!</p>';
unset($arr);
///**********************************************************************************************
$sql = "SELECT COUNT(*) 
				FROM a_admin 
					WHERE  id='1'
	"; 
$res = mysql_query($sql);
$myrow = mysql_fetch_array($res);

if ($myrow[0]==0){
    $arr[0]['id']='1';
    $arr[0]['name']=_GP('admin_name','Алексей');
    $arr[0]['email']=_GP('email','toowin86@yandex.ru');
    $arr[0]['password']=generate_code(15);
    $arr[0]['chk_active']='1';
    
    $kol=ins_tbl('a_admin',$arr);
    $cl_='#999';if ($kol>0){$kol='<strong>'.$kol.'</strong>';$cl_='#090';}
    echo '<p style="color:'.$cl_.';">Добавлено в таблицу a_admin '.$kol.' записей!</p>';
    unset($arr);
    
}
///**********************************************************************************************

$sql = "
		UPDATE a_menu 
			SET  
				inc='m_postav'
		
		WHERE id='17'
";
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);

$tbl='a_menu';
$sql="
INSERT INTO `a_menu` (`id`, `pid`, `sid`, `chk_active`, `chk_block`, `name`, `inc`, `comments`) VALUES
(1, 0, 10, 1, 1, 'Администрирование', 'admin', ''),
(2, 1, 12, 1, 1, 'Меню', 'a_menu', 'Редактирование меню админ-панели'),
(3, 1, 13, 1, 1, 'Опции', 'a_options', 'Опции'),
(4, 1, 11, 0, 1, 'Логи', 'a_admin', ''),
(5, 0, 1, 1, 1, 'Сайт', 'site', ''),
(6, 5, 2, 1, 0, 'Структура', 's_struktura', ''),
(7, 5, 4, 1, 0, 'Каталог', 's_cat', ''),
(8, 5, 8, 1, 0, 'Новости', 's_news', ''),
(15, 0, 14, 1, 0, 'Интернет-магазин', 'market', ''),
(16, 15, 15, 1, 0, 'Заказы', 'm_zakaz', 'Заказы от покупателей'),
(17, 15, 16, 1, 0, 'Поступление товара', 'm_postav', 'Поступление товара на склад'),
(18, 15, 17, 1, 0, 'Диалоги', 'm_dialog', 'Диалоги'),
(21, 5, 3, 1, 0, 'Шаблоны отображеня', 's_shablon', ''),
(22, 5, 9, 1, 0, 'Переменные на сайт', 's_words', ''),
(23, 7, 6, 1, 0, 'Свойства', 's_prop', 'Характеристики товара-услуг'),
(24, 7, 7, 1, 0, 'Значения свойств', 's_prop_val', 'Значения свойств'),
(26, 0, 19, 1, 0, 'Справочники', 'info', ''),
(25, 26, 18, 1, 0, 'Контрагенты', 'i_contr', 'Покупатели-поставщики'),
(27, 7, 5, 1, 0, 'Типы прайсов', 's_tip_price', 'Типы прайсов для каталога'),
(28, 26, 20, 0, 0, 'Единицы измерений', 'i_class_unit', ''),
(29, 26, 21, 1, 0, 'Транспортные компании', 'i_tk', 'Транспортные компании и стоимость их услуг'),
(40, 26, 22, 1, 0, 'Реклама', 'i_reklama', 'Рекламные компании'),
(41, 26, 19, 1, 0, 'Организации', 'i_contr_org', 'Справочник организаций'),
(42, 26, 23, 1, 0, 'Счета', 'i_scheta', 'Счета организации'),
(50, 15, 18, 1, 0, 'Платежи', 'm_platezi', 'Платежи организации'),
(51, 26, 25, 1, 0, 'Города', 'i_city', 'Справочник городов'),
(52, 15, 26, 1, 0, 'Доставка', 'm_dostavka', 'Таблица доставки'),
(53, 26, 10, 1, 0, 'Филиалы', 'i_tp', 'Филиалы компании'),
(54, 26, 15, 1, 0, 'Должности', 'i_post', 'Должности работников'),
(60, 26, 15, 1, 0, 'Документы', 'i_docs', 'Документы для печати'),
(70, 0, 70, 0, 0, 'Сервис', 'remont', 'Сервис'),
(71, 70, 71, 0, 0, 'Прием оборудования', 'r_service_add', 'Прием оборудования в ремонт'),
(72, 70, 72, 0, 0, 'АКТЫ', 'r_service', 'АКТЫ'),
(73, 70, 73, 1, 0, 'Тип оборудования', 'r_tip_oborud', 'Тип оборудования'),
(74, 70, 74, 1, 0, 'Бренд', 'r_brend', 'Бренд оборудования'),
(75, 70, 75, 1, 0, 'Модель', 'r_model', 'Модели оборудования'),
(80, 5, 76, 1, 0, 'Избранное', 'i_contr_s_cat', 'Избранные товары'),
(82, 70, 80, 0, 0, 'Неисправности', 'r_neispravnosti', 'Неисправности'),
(90, 15, 19, 0, 0, 'Склад', 'm_zakaz_s_cat_m_tovar', 'Склад товара'),
(100, 26, 79, 1, 0, 'Виды расходов', 'i_rashodi', 'Справочник: виды расходов'),
(105, 26, 80, 1, 0, 'Ввод/вывод', 'i_inout', 'Справочник: ввод/вывод на счет'),
(110, 15, 81, 1, 0, 'Отчеты', 'm_reports', 'Отчеты по Интернет-магазину'),
(201, 15, 201, 1, 0, 'Прием звонка', 'c_call_client', 'Прием нового звонка'),
(202, 1, 202, 1, 0, 'Конфигурация вопросов', 'c_questions', 'Вопросы из столбцов существующих таблиц'),
(203, 2, 203, 0, 0, 'Столбцы', 'a_col', 'Столбцы админ меню'),
(208, 26, 204, 1, 0, 'Цель звонка', 'i_call_target', 'Цель звонка'),
(300, 70, 205, 1, 0, 'Тип/бренд', 'r_tip_brend', 'Тип/бренд оборудования'),
(320, 26, 20, 1, 0, 'Соц.сети', 'i_social_network', 'Соц.сети'),
(350, 1, 10, 1, 0, 'Логи магазина', 'm_log', 'Логи Интернет-магазина'),
(355, 26, 100, 0, 0, 'Логи магазина', 'm_log_type', 'Типы логов Интернет-магазина'),
(400, 5, 10, 1, 0, 'Вопросы', 's_test_quest', ''),
(401, 5, 11, 1, 0, 'Ответы на вопросы', 's_test_answer', ''),
(500, 15, 100, 1, 0, 'Доставка', 'm_zakaz_s_cat', ''),
(555, 1, 200, 1, 0, 'Редирект', 'a_redirect', '');
"; 
echo_data_add($sql,$tbl);

///**********************************************************************************************

$tbl='a_com';
$sql="
INSERT INTO `a_com` (`id`, `sid`, `chk_active`, `name`, `com`, `tip`) VALUES
(1, 1, 1, 'Добавить', 'add', 'Общая'),
(2, 2, 1, 'Изменить', 'change', 'По id'),
(3, 3, 1, 'Удалить', 'del', 'По id'),
(4, 4, 1, 'Импорт XLS', 'add_xls', 'Общая'),
(5, 5, 1, 'Парсинг', 'add_parsing', 'Общая'),
(6, 4, 1, 'Экспорт CSV', 'export_csv', 'Общая');

";
echo_data_add($sql,$tbl);
//* *****************************************************************

///**********************************************************************************************

$tbl='a_menu_a_com';
$sql="
INSERT INTO `a_menu_a_com` (`id`, `id1`, `id2`) VALUES
(2, 7, 2),
(8, 8, 1),
(16, 3, 2),
(21, 3, 1),
(24, 8, 2),
(27, 6, 2),
(46, 6, 3),
(48, 7, 3),
(49, 7, 1),
(51, 6, 1),
(53, 2, 1),
(55, 2, 2),
(58, 8, 3),
(62, 18, 2),
(66, 2, 3),
(70, 21, 1),
(71, 21, 2),
(72, 21, 3),
(73, 22, 1),
(74, 22, 2),
(75, 22, 3),
(76, 23, 1),
(77, 23, 2),
(78, 23, 3),
(79, 24, 1),
(80, 24, 2),
(81, 24, 3),
(82, 25, 1),
(83, 25, 2),
(84, 7, 5),
(86, 27, 1),
(87, 27, 2),
(88, 27, 3),
(89, 28, 1),
(90, 7, 6),
(91, 29, 1),
(92, 29, 2),
(93, 29, 3),
(94, 40, 1),
(95, 40, 2),
(96, 40, 3),
(97, 41, 1),
(98, 41, 2),
(99, 41, 3),
(100, 42, 1),
(101, 42, 2),
(102, 50, 6),
(103, 51, 1),
(104, 51, 2),
(105, 52, 6),
(106, 23, 6),
(108, 53, 1),
(109, 53, 2),
(112, 52, 1),
(113, 52, 2),
(116, 50, 1),
(117, 50, 2),
(118, 50, 3),
(119, 16, 1),
(120, 16, 2),
(121, 54, 1),
(122, 54, 2),
(123, 54, 3),
(130, 60, 1),
(131, 60, 2),
(132, 60, 3),
(133, 72, 1),
(134, 72, 2),
(135, 72, 3),
(136, 73, 1),
(137, 73, 2),
(138, 73, 3),
(139, 74, 1),
(140, 74, 2),
(141, 74, 3),
(142, 75, 1),
(143, 75, 2),
(144, 75, 3),
(142, 80, 1),
(143, 80, 2),
(144, 80, 3),
(160, 100, 1),
(161, 100, 2),
(162, 100, 3),
(165, 105, 1),
(166, 105, 2),
(167, 105, 3),
(200, 82, 1),
(201, 82, 2),
(202, 82, 3),
(300, 202, 1),
(301, 202, 2),
(350, 208, 1),
(351, 208, 2),
(352, 208, 3),
(370, 300, 1),
(371, 300, 2),
(372, 300, 3),
(390, 320, 1),
(391, 320, 2),
(392, 320, 3),
(400, 355, 1),
(401, 355, 2),
(402, 355, 3),
(409, 400, 1),
(410, 400, 2),
(411, 400, 3),
(412, 401, 1),
(413, 401, 2),
(414, 401, 3),
(500, 500, 1),
(501, 500, 2),
(502, 500, 3),
(605, 555, 1),
(606, 555, 2),
(607, 555, 3);
";
 
echo_data_add($sql,$tbl);
//Удаление связи покупателей
$res = mysql_query("DELETE FROM a_col WHERE id='152'");
	if (!$res){echo $sql;exit();}

$res = mysql_query("DELETE FROM a_connect WHERE id='16'");
	if (!$res){echo $sql;exit();}
//* *****************************************************************
///**********************************************************************************************

$tbl='a_col';
$sql="INSERT INTO `a_col` (`id`, `sid`, `chk_active`, `chk_view`, `chk_change`, `a_menu_id`, `col`, `col_ru`, `tip`) VALUES
(1, 1, 1, 1, 1, 7, 'chk_active', 'Активность', 'chk'),
(2, 2, 1, 1, 1, 7, 'name', 'Название', 'Текст'),
(3, 4, 1, 1, 1, 7, 'price', 'Цена', 'Стоимость'),
(4, 5, 1, 0, 1, 7, 'html_code', 'Описание', 'HTML-код'),
(5, 8, 1, 1, 0, 7, 'data_change', 'Дата изменения', 'Дата-время'),
(18, 19, 1, 1, 1, 6, 'chk_active', 'Акт.', 'chk'),
(21, 20, 1, 1, 1, 6, 'name', 'Название', 'Текст'),
(23, 6, 1, 1, 1, 7, 's_struktura_id', 'В структуре', 'Связанная таблица max-max'),
(30, 23, 1, 1, 1, 3, 'name', 'Название', 'Текст'),
(32, 24, 1, 1, 1, 3, 'val', 'Значение', 'HTML-код'),
(33, 25, 1, 1, 1, 3, 'tip', 'Тип', 'enum'),
(34, 1, 1, 1, 1, 8, 'chk_active', 'Активность', 'chk'),
(35, 2, 1, 1, 1, 8, 'name', 'Заголовок', 'Текст'),
(36, 8, 1, 0, 1, 8, 'data_create', 'Дата создания', 'Дата-время'),
(37, 7, 1, 1, 0, 8, 'data_change', 'Дата изменения', 'Дата-время'),
(38, 6, 1, 0, 1, 8, 'html_code', 'Текст новости', 'HTML-код'),
(39, 1, 1, 1, 1, 4, 'name', 'Имя', 'Текст'),
(40, 1, 1, 1, 1, 4, 'email', 'Email', 'Email'),
(41, 1, 1, 1, 1, 4, 'phone', 'Телефон', 'Телефон'),
(42, 1, 1, 1, 1, 4, 'comments', 'Комментарии', 'Длинный текст'),
(43, 1, 1, 1, 1, 4, 'data_create', 'Дата создания', 'Дата-время'),
(44, 4, 1, 1, 1, 8, 'a_admin_id', 'Автор', 'Связанная таблица 1-max'),
(45, 3, 1, 0, 1, 7, 'url', 'ЧПУ', 'Функция'),
(46, 9, 1, 0, 1, 7, 'tip', 'Тип', 'enum'),
(47, 10, 1, 0, 0, 7, 'data_create', 'Дата создания', 'Дата-время'),
(48, 34, 1, 0, 1, 6, 'url', 'ЧПУ', 'Функция'),
(49, 35, 1, 0, 1, 6, 'page_name', 'Название страницы', 'Длинный текст'),
(50, 36, 1, 0, 1, 6, 'html_code', 'Описание', 'HTML-код'),
(51, 37, 1, 0, 0, 6, 'data_create', 'Дата создания', 'Дата-время'),
(52, 38, 1, 0, 0, 6, 'data_change', 'Дата изменения', 'Дата-время'),
(54, 7, 1, 1, 1, 7, 'prop', 'Свойства', 'Функция'),
(55, 11, 1, 1, 1, 7, 'photo', 'Фото', 'Фото'),
(56, 12, 1, 1, 1, 7, 'mini_desc', 'Краткое описание', 'Длинный текст'),
(71, 39, 1, 1, 1, 21, 'chk_active', 'Активность', 'chk'),
(72, 40, 1, 1, 1, 21, 'name', 'Название', 'Текст'),
(73, 41, 1, 1, 1, 6, 's_shablon_id', 'Шаблон', 'Связанная таблица max-max'),
(75, 42, 1, 1, 1, 6, 'tip', 'Тип', 'enum'),
(76, 43, 1, 1, 1, 22, 'name', 'Название', 'Текст'),
(77, 44, 1, 1, 1, 22, 'html_code', 'Значение', 'HTML-код'),
(78, 45, 1, 1, 0, 22, 'data_change', 'Дата изменения', 'Дата-время'),
(79, 46, 1, 0, 0, 22, 'data_create', 'Дата создания', 'Дата-время'),
(80, 47, 1, 1, 1, 23, 'chk_active', 'Активность', 'chk'),
(81, 48, 1, 1, 1, 23, 'name', 'Название', 'Текст'),
(82, 49, 1, 1, 1, 23, 'tip', 'Тип', 'enum'),
(83, 50, 1, 0, 0, 23, 'data_create', 'Дата создания', 'Дата-время'),
(84, 51, 1, 1, 1, 24, 's_prop_id', 'Свойство', 'Связанная таблица 1-max'),
(85, 52, 1, 1, 1, 24, 'val', 'Значение свойства', 'Длинный текст'),
(86, 53, 1, 0, 0, 24, 'data_create', 'Дата создания', 'Дата-время'),
(87, 54, 1, 1, 1, 25, 'chk_active', 'Активность', 'chk'),
(88, 55, 1, 1, 1, 25, 'name', 'Название. Имя', 'Длинный текст'),
(89, 56, 1, 1, 1, 25, 'email', 'email', 'Email'),
(90, 57, 1, 0, 0, 25, 'password', 'Пароль', 'Длинный текст'),
(91, 58, 1, 1, 1, 25, 'phone', 'Телефон', 'Телефон'),
(93, 60, 1, 0, 0, 25, 'data_create', 'Дата создания', 'Дата-время'),
(94, 61, 1, 1, 0, 25, 'data_change', 'Дата изменения', 'Дата-время'),
(95, 62, 1, 1, 1, 25, 'html_code', 'Описание', 'HTML-код'),
(96, 63, 1, 1, 1, 25, 'adress', 'Адрес', 'Длинный текст'),
(97, 64, 1, 1, 1, 25, 'link', 'Сайт', 'Длинный текст'),
(98, 65, 1, 0, 1, 25, 'photo', 'Фото', 'Фото'),
(99, 66, 1, 0, 0, 7, 'article', 'Артикл', 'Длинный текст'),
(100, 67, 1, 1, 1, 27, 'name', 'Название', 'Текст'),
(101, 68, 1, 0, 0, 7, 's_tip_price_id', 'Тип прайса', 'Связанная таблица 1-max'),
(102, 69, 1, 0, 1, 6, 'photo', 'Фото', 'Фото'),
(103, 70, 1, 0, 1, 6, 'description', 'Описание META', 'Длинный текст'),
(104, 71, 1, 0, 1, 6, 'keywords', 'Ключи META', 'Длинный текст'),
(110, 72, 1, 0, 1, 23, 'comments', 'Описание', 'Длинный текст'),
(115, 73, 1, 1, 1, 28, 'name', 'Название', 'Текст'),
(116, 74, 1, 1, 1, 28, 'rus_name1', 'Сокращенное русское', 'Текст'),
(117, 75, 1, 1, 1, 28, 'eng_name1', 'Сокращенное английское', 'Текст'),
(118, 76, 1, 0, 1, 7, 'i_class_unit_id', 'Ед.', 'Связанная таблица 1-max'),
(119, 77, 1, 0, 1, 7, 's_cat_s_cat', 'Свзанные товары', 'Связанная таблица max-max'),
(120, 78, 1, 0, 1, 6, 'icon', 'Иконка', 'Текст'),
(121, 5, 1, 1, 1, 8, 's_struktura_id', 'В структуре', 'Связанная таблица 1-max'),
(122, 3, 1, 0, 1, 8, 'url', 'Ссылка', 'Функция'),
(123, 79, 1, 1, 1, 8, 'photo', 'Фото', 'Фото'),
(130, 80, 1, 1, 1, 23, 'chk_fillter', 'В фильтре', 'chk'),
(132, 83, 1, 0, 1, 6, 's_struktura_s_struktura_id', 'Ссылка', 'Связанная таблица max-max'),
(140, 81, 1, 0, 1, 6, 'skidka', 'Скидка', 'Дробное число'),
(141, 82, 1, 1, 1, 29, 'chk_active', 'Активность', 'chk'),
(142, 84, 1, 1, 1, 29, 'name', 'Название', 'Текст'),
(143, 85, 1, 1, 1, 29, 'price', 'Цена', 'Стоимость'),
(144, 86, 1, 1, 1, 29, 'i_contr_org_id', 'Организация', 'Связанная таблица 1-max'),
(145, 87, 1, 0, 1, 7, 'data_end', 'Время действия', 'Дата-время'),
(146, 88, 1, 0, 1, 25, 'skidka', 'Скидка', 'Дробное число'),
(150, 89, 1, 1, 1, 40, 'name', 'Название', 'Текст'),
(151, 90, 1, 0, 1, 25, 'i_reklama_id', 'Реклама', 'Связанная таблица 1-max'),
(153, 92, 1, 1, 1, 41, 'name', 'Название', 'Длинный текст'),
(154, 93, 1, 1, 1, 41, 'inn', 'ИНН', 'Целое число'),
(155, 94, 1, 0, 1, 41, 'kpp', 'КПП', 'Текст'),
(156, 95, 1, 0, 1, 41, 'ogrn', 'ОГРН', 'Текст'),
(157, 96, 1, 0, 1, 41, 'bik', 'БИК', 'Текст'),
(158, 97, 1, 0, 1, 41, 'bank', 'Банк', 'Длинный текст'),
(159, 98, 1, 0, 1, 41, 'schet', 'Счет', 'Текст'),
(160, 99, 1, 0, 1, 41, 'kschet', 'Кор.счет', 'Текст'),
(161, 100, 1, 0, 1, 41, 'u_adress', 'Юридический адрес', 'Длинный текст'),
(162, 101, 1, 0, 1, 41, 'fio_director', 'ФИО Директора', 'Текст'),
(163, 102, 1, 0, 1, 41, 'na_osnovanii', 'На основании', 'Текст'),
(1180, 103, 1, 1, 1, 41, 'email', 'email', 'Email'),
(1181, 104, 1, 1, 1, 41, 'site', 'Сайт', 'Текст'),
(164, 103, 1, 0, 1, 42, 'name', 'Название', 'Текст'),
(165, 104, 1, 1, 0, 42, 'data_create', 'Дата создания', 'Дата-время'),
(166, 105, 1, 1, 1, 16, 'data', 'Дата заказа', 'Дата'),
(167, 106, 1, 1, 1, 16, 'a_admin_id', 'Работник', 'Связанная таблица 1-max'),
(168, 107, 1, 1, 1, 16, 'i_contr_id', 'Покупатель', 'Связанная таблица 1-max'),
(169, 108, 1, 1, 1, 16, 'project_name', 'Название заказа', 'Текст'),
(170, 109, 1, 1, 1, 16, 'status', 'Статус', 'enum'),
(171, 110, 1, 1, 1, 16, 'comments', 'Комментарии', 'Длинный текст'),
(172, 111, 1, 0, 1, 16, 'data_end', 'Дата отмены', 'Дата'),
(173, 112, 1, 1, 1, 16, 'data_done', 'Дата выполнения', 'Дата'),
(174, 113, 1, 1, 1, 50, 'data', 'Дата платежа', 'Дата'),
(175, 114, 1, 1, 1, 50, 'summa', 'Сумма платежа', 'Стоимость'),
(177, 116, 1, 1, 1, 50, 'i_scheta_id', 'Счет', 'Связанная таблица 1-max'),
(178, 117, 1, 1, 1, 50, 'comments', 'Комментарии', 'Длинный текст'),
(179, 118, 1, 1, 1, 51, 'name', 'Город', 'Текст'),
(180, 119, 1, 1, 1, 51, 'region', 'Регион', 'Текст'),
(181, 120, 1, 0, 1, 51, 'nomer', 'Номер', 'Целое число'),
(182, 121, 1, 0, 1, 51, 'nom_region', 'Номер региона', 'Целое число'),
(183, 122, 1, 1, 1, 52, 'm_zakaz_id', 'Номер заказа', 'Связанная таблица 1-max'),
(184, 123, 1, 1, 1, 52, 'i_tk_id', 'ТК', 'Связанная таблица 1-max'),
(185, 124, 1, 1, 1, 52, 'fio', 'ФИО', 'Текст'),
(186, 125, 1, 1, 1, 52, 'index_', 'Индекс', 'Целое число'),
(187, 126, 1, 1, 1, 52, 'i_city_id', 'Город', 'Связанная таблица 1-max'),
(188, 127, 1, 1, 1, 52, 'adress', 'Адрес', 'Длинный текст'),
(189, 128, 1, 1, 1, 52, 'phone', 'Телефон', 'Телефон'),
(190, 10, 1, 1, 1, 40, 'chk_active',  'Активность', 'chk'),
(191, 129, 1, 1, 1, 53, 'chk_active',  'Активность', 'chk'),
(192, 130, 1, 1, 1, 53, 'name', 'Название', 'Длинный текст'),
(193, 131, 1, 1, 1, 53, 'i_contr_org_id', 'Организация', 'Связанная таблица 1-max'),
(194, 132, 1, 1, 1, 53, 'phone', 'Телефон', 'Телефон'),
(195, 133, 1, 1, 1, 53, 'adress','Адрес', 'Длинный текст'),
(196, 134, 1, 1, 1, 53, 'email', 'email', 'Email'),
(1182, 135, 1, 1, 1, 53, 'site', 'Сайт', 'Текст'),
(197, 135, 1, 1, 1, 53, 'comments', 'Комментарии', 'Длинный текст'),
(198, 136, 1, 1, 1, 41, 'photo', 'Фото', 'Фото'),
(199, 99, 1, 0, 1, 41, 'phone', 'Телефон', 'Телефон'),
(200, 137, 1, 0, 1, 41, 'tip_director', 'Должность руководителя', 'Текст'),
(205, 138, 1, 1, 1, 52, 'tracking_number', 'Номер отслеживания', 'Длинный текст'),
(206, 139, 1, 1, 1, 16, 'tip_pay', 'Тип оплаты', 'enum'),
(210, 140, 1, 1, 1, 54, 'name', 'Название', 'Текст'),
(211, 141, 1, 1, 1, 54, 'comments', 'Описание', 'Длинный текст'),
(212, 143, 1, 1, 1, 4, 'a_admin_i_post_id', 'Распределение должностей', 'Связанная таблица max-max'),
(220, 144, 1, 1, 1, 25, 'i_contr_org_id', 'Организации', 'Связанная таблица max-max'),
(221, 145, 1, 1, 1, 16, 'i_contr_org_id', 'Организация', 'Связанная таблица 1-max'),
(250, 146, 1, 0, 1, 6, 'article', 'Артикл', 'Текст'),
(300, 147, 1, 0, 1, 16, 'photo', 'Фото', 'Фото'),
(301, 148, 1, 0, 1, 41, 'html_code', 'Описание', 'HTML-код'),
(302, 149, 1, 0, 1, 41, 'photo', 'Фото', 'Фото'),
(305, 150, 1, 1, 1, 52, 'summa', 'Стоимость', 'Стоимость'),
(310, 160, 1, 1, 1, 60, 'a_menu_id', 'Меню', 'Связанная таблица 1-max'),
(311, 161, 1, 1, 1, 60, 'name', 'Название', 'Текст'),
(312, 164, 1, 1, 1, 60, 'html_code', 'Документ', 'HTML-код'),
(313, 163, 1, 1, 1, 2, 'name', 'Название', 'Текст'),
(314, 162, 1, 1, 1, 60, 'file_name', 'Название файла', 'Текст'),
(315, 159, 1, 1, 1, 60, 'chk_active', 'Активность', 'chk'),
(350, 160, 1, 1, 1, 73, 'chk_active', 'Активность', 'chk'),
(351, 161, 1, 1, 1, 73, 'name', 'Название типа оборудования', 'Текст'),
(352, 162, 1, 1, 1, 73, 'html_code', 'Описание', 'HTML-код'),
(353, 163, 1, 1, 1, 74, 'chk_active', 'Активность', 'chk'),
(354, 164, 1, 1, 1, 74, 'name', 'Название', 'Текст'),
(355, 165, 1, 1, 1, 74, 'html_code', 'Описание', 'HTML-код'),
(356, 166, 1, 1, 1, 75, 'chk_active', 'Активность', 'chk'),
(357, 167, 1, 1, 1, 75, 'r_tip_oborud_id', 'Тип оборудования', 'Связанная таблица 1-max'),
(358, 168, 1, 1, 1, 75, 'r_brend_id', 'Бренд оборудования', 'Связанная таблица 1-max'),
(359, 169, 1, 1, 1, 75, 'name', 'Название', 'Текст'),
(360, 170, 1, 1, 1, 75, 'html_code', 'Описание', 'HTML-код'),
(400, 171, 1, 1, 1, 72, 'chk_active', 'Активность', 'chk'),
(401, 172, 1, 1, 1, 72, 'status', 'Статус', 'enum'),
(402, 173, 1, 1, 1, 72, 'data_priem', 'Дата приема', 'Дата-время'),
(403, 174, 1, 1, 1, 72, 'data_vidachi', 'Дата выдачи', 'Дата-время'),
(404, 175, 1, 1, 1, 72, 'a_admin_id', 'Приемщик', 'Связанная таблица 1-max'),
(405, 176, 1, 1, 1, 72, 'i_contr_id', 'Заказчик', 'Связанная таблица 1-max'),
(406, 177, 1, 1, 1, 72, 'm_zakaz_id', 'Номер заказа', 'Связанная таблица 1-max'),
(407, 178, 1, 1, 1, 72, 'r_model_id', 'Модель', 'Связанная таблица 1-max'),
(408, 179, 1, 0, 1, 72, 'sn', 'Серийный номер', 'Текст'),
(409, 180, 1, 0, 1, 72, 'komplekt', 'Комплектация', 'Текст'),
(410, 181, 1, 0, 1, 72, 'sost', 'Состояние', 'Текст'),
(411, 182, 1, 0, 1, 72, 'comments', 'Комментарии', 'HTML-код'),
(412, 183, 1, 1, 1, 72, 'chk_garant', 'Гарантия', 'chk'),
(413, 184, 1, 0, 1, 72, 'r_service_id', 'Номер гарантийного акта', 'Связанная таблица 1-max'),
(420, 185, 1, 1, 1, 16, 'i_tp_id', 'Точка продаж', 'Связанная таблица 1-max'),
(450, 186, 1, 1, 1, 80, 'chk_active', 'Активность', 'chk'),
(451, 187, 1, 1, 1, 80, 'i_contr_id', 'Контрагент', 'Связанная таблица 1-max'),
(452, 188, 1, 1, 1, 80, 's_cat_id', 'Товар', 'Связанная таблица 1-max'),
(453, 189, 1, 1, 1, 80, 'html_code', 'Комментарии', 'HTML-код'),
(454, 190, 1, 1, 0, 80, 'data_create', 'Дата создания', 'Дата-время'),
(460, 191, 1, 0, 1, 60, 'photo', 'Фото', 'Фото'),
(480, 192, 1, 1, 1, 53, 'i_city_id', 'Город', 'Связанная таблица 1-max'),
(500, 193, 0, 0, 1, 7, 'price_convert', 'Цена в валюте', 'Стоимость'),
(501, 194, 0, 0, 1, 7, 'price_tip', 'Валюта', 'enum'),
(505, 0, 1, 1, 1, 51, 'chk_active', 'Активность', 'chk'),
(508, 195, 1, 1, 1, 17, 'i_tp_id', 'Филиал', 'Связанная таблица 1-max'),
(516, 231, 1, 1, 0, 7, 'pop', 'Популярность', 'Целое число'),
(600, 199, 0, 1, 1, 7, 'kolvo', 'Кол.', 'Целое число'),
(605, 200, 1, 1, 1, 50, 'a_admin_id', 'Работник', 'Связанная таблица 1-max'),
(606, 201, 1, 1, 1, 50, 'a_menu_id', 'Меню', 'Связанная таблица 1-max'),
(607, 225, 1, 1, 1, 50, 'tip', 'Тип', 'enum'),
(608, 226, 1, 1, 1, 50, 'id_z_p_p', 'Номер', 'Целое число'),
(620, 230, 1, 1, 1, 23, 'data_tip', 'Тип данных', 'enum'),
(700, 231, 1, 1, 1, 42, 'sid', 'Сорт.', 'Целое число'),
(750, 232, 1, 1, 1, 100, 'chk_active', 'Активность', 'chk'),
(751, 233, 1, 1, 1, 100, 'name', 'Название', 'Текст'),
(760, 234, 1, 1, 1, 105, 'chk_active', 'Активность', 'chk'),
(761, 235, 1, 1, 1, 105, 'name', 'Название', 'Текст'),
(850, 5, 0, 0, 1, 7, 's_article', 'Цены у поставщиков', 'Функция'),
(903, 133, 1, 1, 1, 53, 'index_', 'Индекс', 'Текст'),
(900, 234, 1, 1, 1, 82, 'chk_active', 'Активность', 'chk'),
(901, 161, 1, 1, 1, 82, 'name', 'Название неисправности', 'Текст'),
(902, 164, 1, 1, 1, 82, 'html_code', 'Описание', 'HTML-код'),
(920, 165, 1, 1, 1, 82, 'r_tip_oborud_id', 'Тип оборудования', 'Связанная таблица 1-max'),
(940, 200, 1, 1, 1, 82, 'r_neispravnosti_s_struktura_id', 'Разделы структуры', 'Связанная таблица max-max'),
(990, 1, 1, 1, 1, 203, 'chk_active', 'Активность', 'chk'),
(991, 2, 1, 1, 1, 203, 'col', 'Столбец', 'Текст'),
(992, 2, 1, 1, 1, 203, 'col_ru', 'Название столбца', 'Текст'),
(993, 3, 1, 1, 1, 203, 'a_menu_id', 'Меню', 'Связанная таблица 1-max'),
(1000, 300, 1, 1, 1, 202, 'chk_active', 'Активность', 'chk'),
(1001, 301, 1, 1, 1, 202, 'a_col_id', 'Столбец', 'Связанная таблица 1-max'),
(1002, 302, 1, 0, 1, 202, 'comments', 'Комментарии', 'Длинный текст'),
(1003, 303, 1, 1, 1, 202, 'sid', 'Сорт.', 'Целое число'),
(1004, 304, 1, 1, 1, 202, 'tip', 'Тип', 'enum'),
(1008, 299, 1, 1, 1, 202, 'chk_required', 'Обязательность', 'chk'),
(1100, 160, 1, 1, 1, 208, 'chk_active', 'Активность', 'chk'),
(1101, 161, 1, 1, 1, 208, 'name', 'Цель звонка', 'Текст'),
(1200, 180, 0, 1, 1, 7, 'a_admin_id_change', 'Кто изменил', 'Связанная таблица 1-max'),
(1201, 181, 0, 1, 1, 7, 'a_admin_id_create', 'Кто создал', 'Связанная таблица 1-max'),
(1300, 305, 1, 1, 1, 300, 'chk_active', 'Активность', 'chk'),
(1301, 307, 1, 1, 1, 300, 'r_tip_oborud_id', 'Тип оборудования', 'Связанная таблица 1-max'),
(1302, 308, 1, 1, 1, 300, 'r_brend_id', 'Бренд оборудования', 'Связанная таблица 1-max'),
(1303, 306, 1, 1, 1, 300, 'sid', 'Сорт.', 'Целое число'),
(1304, 309, 1, 1, 1, 300, 'name', 'Название типа/бенда', 'Текст'),
(1305, 310, 1, 0, 1, 300, 'description', 'Краткое описание типа/бенда', 'Текст'),
(1306, 311, 1, 0, 1, 300, 'html_code', 'Описание', 'HTML-код'),
(1307, 312, 1, 0, 1, 300, 'photo', 'Фото', 'Фото'),
(1308, 312, 1, 0, 1, 73, 'photo', 'Фото', 'Фото'),
(511, 313, 1, 0, 1, 74, 'photo', 'Фото', 'Фото'),
(1400, 314, 1, 0, 1, 53, 'photo', 'Фото', 'Фото'),
(1401, 0, 1, 1, 1, 53, 'sid', 'Сорт.', 'Целое число'),
(1405, 350, 1, 0, 1, 82, 'photo', 'Фото', 'Фото'),
(1450, 3, 0, 0, 1, 7, 'i_tp_s_cat_price', 'Цена в филиале', 'Функция'),
(1500, 400, 1, 0, 1, 53, 'worktime', 'График работы', 'Текст'),
(1600, 231, 1, 1, 1, 320, 'sid', 'Сорт.', 'Целое число'),
(1601, 232, 1, 1, 1, 320, 'chk_active', 'Активность', 'chk'),
(1602, 233, 1, 1, 1, 320, 'name', 'Название соц.сети', 'Текст'),
(1603, 234, 1, 0, 1, 320, 'comments', 'Комментарии', 'Длинный текст'),
(1650, 136, 1, 1, 1, 53, 'i_social_network', 'Соц.сети', 'Функция'),
(1800, 10, 1, 1, 1, 350, 'text', 'Логи магазина', 'Текст'),
(1801, 20, 1, 0, 0, 350, 'data_create', 'Дата создания', 'Дата-время'),
(1802, 1, 1, 1, 1, 350, 'a_admin_id', 'Имя пользователя', 'Связанная таблица 1-max'),
(1803, 2, 1, 1, 1, 350, 'a_menu_id', 'Тип логов', 'Связанная таблица 1-max'),
(1804, 3, 1, 1, 1, 350, 'id_z_p_p', 'Номер пункта', 'Целое число'),
(1900, 300, 0, 0, 0, 53, 'geo', 'Гео координаты', 'Текст'),
(1950, 302, 1, 1, 1, 24, 'photo', 'Фото', 'Фото'),
(2000, 81, 1, 1, 1, 23, 'chk_main', 'Основное', 'chk'),
(2020, 300, 0, 0, 1, 6, 'tag', 'Тег', 'Текст'),
(2030, 99, 0, 0, 1, 25, 'nakrutka', 'Накрутка %', 'Дробное число'),
(2050, 83, 1, 1, 1, 29, 'chk_cart', 'В корзине', 'chk'),
(2060, 190, 0, 1, 1, 7, 'i_contr_id_create', 'Контрагент', 'Связанная таблица 1-max'),
(2080, 180, 0, 1, 1, 8, 'a_admin_id_change', 'Кто изменил', 'Связанная таблица 1-max'),
(2081, 181, 0, 1, 1, 8, 'a_admin_id_create', 'Кто создал', 'Связанная таблица 1-max'),
(2082, 180, 0, 1, 1, 6, 'a_admin_id_change', 'Кто изменил', 'Связанная таблица 1-max'),
(2083, 181, 0, 1, 1, 6, 'a_admin_id_create', 'Кто создал', 'Связанная таблица 1-max'),
(2100, 9, 1, 1, 1, 8, 'sid', 'Сорт.', 'Целое число'),
(2110, 900, 1, 1, 1, 25, 's_struktura_id', 'В структуре', 'Связанная таблица max-max'),
(2200, 1, 1, 1, 1, 355, 'name', 'Название', 'Текст'),
(2201, 2, 1, 1, 1, 350, 'm_log_type', 'Тип логов', 'Связанная таблица 1-max'),
(2215, 999, 0, 1, 1, 8, 'data_publish', 'Дата публикации', 'Дата-время'),
(2216, 310, 0, 0, 1, 8, 'description', 'Описание новости', 'Текст'),
(2220, 99, 0, 0, 1, 6, 's_tests', 'Тесты', 'Функция'),
(2300, 1, 1, 1, 1, 400, 'chk_active', 'Активность', 'chk'),
(2301, 2, 1, 1, 1, 400, 'name', 'Название', 'Текст'),
(2302, 3, 1, 0, 1, 400, 'html_code', 'Описание', 'HTML-код'),
(2303, 1, 1, 1, 1, 401, 'chk_true', 'Верный', 'chk'),
(2304, 3, 1, 1, 1, 401, 'name', 'Ответ на вопрос', 'Текст'),
(2305, 4, 1, 0, 1, 401, 'html_code', 'Описание', 'HTML-код'),
(2306, 2, 1, 1, 1, 401, 's_test_quest_id', 'Вопрос', 'Связанная таблица 1-max'),
(2307, 11, 1, 0, 1, 400, 'photo', 'Фото', 'Фото'),
(2308, 11, 1, 0, 1, 401, 'photo', 'Фото', 'Фото'),
(2310, 160, 1, 0, 1, 400, 'chk_tip', 'Несколько правильных ответов', 'chk'),
(2400, 200, 1, 1, 1, 42, 'i_tp_id', 'Точка продаж', 'Связанная таблица 1-max'),
(2500, 900, 0, 1, 1, 7, 's_struktura_fortest_id', 'Разделы для тестов', 'Связанная таблица max-max'),
(2501, 901, 0, 1, 1, 6, 'view', 'Видимость', 'enum'),
(2550, 900, 1, 1, 1, 23, 'tip_view', 'Отображение', 'enum'),
(2551, 900, 1, 0, 1, 24, 'html_code', 'Описание свойства', 'HTML-код'),
(2700, 1, 1, 1, 1, 555, 'link_in', 'Что заменить', 'Ссылка'),
(2701, 2, 1, 1, 1, 555, 'link_out', 'На что заменить', 'Ссылка'),
(2702, 3, 1, 1, 0, 555, 'kol', 'Кол.', 'Целое число'),
(2800, 99, 0, 0, 1, 7, 'chk_new', 'Новинка', 'chk'); 
"; 
echo_data_add($sql,$tbl);
//* *****************************************************************

//Удаляем из m_platezi . m_zakaz_id
$sql = "DELETE 
			FROM a_admin_a_col 
				WHERE id2='1951'
";
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
$sql = "DELETE 
			FROM a_connect 
				WHERE a_col_id1='1951' OR a_col_id2='1951'
";
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
$sql = "DELETE 
			FROM a_col 
				WHERE id='1951'
";
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);

///**********************************************************************************************

$tbl='a_connect';
$sql="
INSERT INTO `a_connect` (`id`, `a_col_id1`, `a_col_id2`, `usl`, `chk`, `tbl`) VALUES
(3, 44, 39, '', 0, ''),
(4, 23, 21, '`s_struktura`.`tip`=\"Каталог\"', 0, ''),
(5, 73, 72, '', 0, ''),
(6, 84, 81, '', 1, ''),
(7, 101, 100, '', 0, ''),
(8, 118, 116, '', 1, ''),
(9, 119, 2, '', 0, ''),
(10, 121, 21, '', 0, ''),
(11, 132, 21, 's_struktura.tip=\"Каталог\"', 1, ''),
(12, 144, 153, '', 1, ''),
(15, 151, 150, '', 0, ''),
(17, 167, 39, '', 0, ''),
(18, 168, 88, '', 0, ''),
(20, 177, 164, '', 0, ''),
(21, 183, 169, '', 0, ''),
(22, 184, 142, '', 0, ''),
(23, 187, 179, '', 0, ''),
(24, 193, 153, '', 1, ''),
(25, 212, 210, '', 1, ''),
(30, 220, 153, '', 0, ''),
(31, 221, 153, '', 0, ''),
(35, 310, 313, '', 0, ''),
(36, 357, 351, '', 0, ''),
(37, 358, 354, '', 0, ''),
(38, 404, 39, '', 0, ''),
(39, 405, 88, '', 0, ''),
(40, 406, 169, '', 0, ''),
(41, 407, 359, '', 0, ''),
(42, 413, 402, '', 0, ''),
(43, 420, 192, '', 0, ''),
(44, 451, 88, '', 0, ''),
(45, 452, 2, '', 0, ''),
(50, 480, 179, '', 1, ''),
(55, 508, 192, '', 1, ''),
(60, 605, 39, '', 1, ''),
(61, 606, 313, '', 1, ''),
(70, 920, 351, '', 0, ''),
(75, 940, 21, 's_struktura.tip=\"Каталог\"', 1, ''),
(80, 993, 313, '', 1, ''),
(81, 1001, 992, '', 0, ''),
(90, 1200, 39, '', 1, ''),
(91, 1201, 39, '', 1, ''),
(100, 1301, 351, '', 0, ''),
(101, 1302, 354, '', 0, ''),
(110, 1802, 39, '', 0, ''),
(111, 1803, 313, '', 0, ''),
(120, 2060, 88, '', 0, ''),
(130, 2080, 39, '', 1, ''),
(131, 2081, 39, '', 1, ''),
(132, 2082, 39, '', 1, ''),
(133, 2083, 39, '', 1, ''),
(140, 2110, 21, '', 0, ''),
(141, 2201, 2200, '', 0, ''),
(145, 2306, 2301, '', 0, ''),
(150, 2400, 192, '', 0, ''),
(160, 2500, 21, '', 0, 's_cat_s_struktura_fortest');
";
echo_data_add($sql,$tbl);
//* *****************************************************************

///**********************************************************************************************


$tbl='i_class_unit';
$sql="
INSERT INTO `i_class_unit` (`id`, `name`, `number_code`, `rus_name1`, `eng_name1`) VALUES
(1, 'Миллиметр', '003', 'мм', 'mm'),
(2, 'Сантиметр', '004', 'см', 'cm'),
(3, 'Дециметр', '005', 'дм', 'dm'),
(4, 'Метр', '006', 'м', 'm'),
(5, 'Километр; тысяча метров', '008', 'км; 10^3 м', 'km'),
(6, 'Мегаметр; миллион метров', '009', 'Мм; 10^6 м', 'Mm'),
(7, 'Дюйм (25.4 мм)', '039', 'дюйм', 'in'),
(8, 'Фут (0.3048 м)', '041', 'фут', 'ft'),
(9, 'Ярд (0.9144 м)', '043', 'ярд', 'yd'),
(10, 'Морская миля (1852 м)', '047', 'миля', 'n mile'),
(11, 'Квадратный миллиметр', '050', 'мм2', 'mm2'),
(12, 'Квадратный сантиметр', '051', 'см2', 'cm2'),
(13, 'Квадратный дециметр', '053', 'дм2', 'dm2'),
(14, 'Квадратный метр', '055', 'м2', 'm2'),
(15, 'Тысяча квадратных метров', '058', '10^3 м^2', 'daa'),
(16, 'Гектар', '059', 'га', 'ha'),
(17, 'Квадратный километр', '061', 'км2', 'km2'),
(18, 'Квадратный дюйм (645.16 мм2)', '071', 'дюйм2', 'in2'),
(19, 'Квадратный фут (0.092903 м2)', '073', 'фут2', 'ft2'),
(20, 'Квадратный ярд (0.8361274 м2)', '075', 'ярд2', 'yd2'),
(21, 'Ар (100 м2)', '109', 'а', 'a'),
(22, 'Кубический миллиметр', '110', 'мм3', 'mm3'),
(23, 'Кубический сантиметр; миллилитр', '111', 'см3; мл', 'cm3; ml'),
(24, 'Литр; кубический дециметр', '112', 'л; дм3', 'I; L; dm^3'),
(25, 'Кубический метр', '113', 'м3', 'm3'),
(26, 'Децилитр', '118', 'дл', 'dl'),
(27, 'Гектолитр', '122', 'гл', 'hl'),
(28, 'Мегалитр', '126', 'Мл', 'Ml'),
(29, 'Кубический дюйм (16387.1 мм3)', '131', 'дюйм3', 'in3'),
(30, 'Кубический фут (0.02831685 м3)', '132', 'фут3', 'ft3'),
(31, 'Кубический ярд (0.764555 м3)', '133', 'ярд3', 'yd3'),
(32, 'Миллион кубических метров', '159', '10^6 м3', '10^6 m3'),
(33, 'Гектограмм', '160', 'гг', 'hg'),
(34, 'Миллиграмм', '161', 'мг', 'mg'),
(35, 'Метрический карат', '162', 'кар', 'МС'),
(36, 'Грамм', '163', 'г', 'g'),
(37, 'Килограмм', '166', 'кг', 'kg'),
(38, 'Тонна; метрическая тонна (1000 кг)', '168', 'т', 't'),
(39, 'Килотонна', '170', '10^3 т', 'kt'),
(40, 'Сантиграмм', '173', 'сг', 'cg'),
(41, 'Брутто-регистровая тонна (2.8316 м3)', '181', 'БРТ', '-'),
(42, 'Грузоподъемность в метрических тоннах', '185', 'т грп', '-'),
(43, 'Центнер (метрический) (100 кг); гектокилограмм; квинтал1 (метрический); децитонна', '206', 'ц', 'q; 10^2 kg'),
(44, 'Ватт', '212', 'Вт', 'W'),
(45, 'Киловатт', '214', 'кВт', 'kW'),
(46, 'Мегаватт; тысяча киловатт', '215', 'МВт; 10^3 кВт', 'MW'),
(47, 'Вольт', '222', 'В', 'V'),
(48, 'Киловольт', '223', 'кВ', 'kV'),
(49, 'Киловольт-ампер', '227', 'кВ.А', 'kV.A'),
(50, 'Мегавольт-ампер (тысяча киловольт-ампер)', '228', 'МВ.А', 'MV.A'),
(51, 'Киловар', '230', 'квар', 'kVAR'),
(52, 'Ватт-час', '243', 'Вт.ч', 'W.h'),
(53, 'Киловатт-час', '245', 'кВт.ч', 'kW.h'),
(54, 'Мегаватт-час; 1000 киловатт-часов', '246', 'МВт.ч; 10^3 кВт.ч', 'МW.h'),
(55, 'Гигаватт-час (миллион киловатт-часов)', '247', 'ГВт.ч', 'GW.h'),
(56, 'Ампер', '260', 'А', 'A'),
(57, 'Ампер-час (3.6 кКл)', '263', 'А.ч', 'A.h'),
(58, 'Тысяча ампер-часов', '264', '10^3 А.ч', '10^3 A.h'),
(59, 'Кулон', '270', 'Кл', 'C'),
(60, 'Джоуль', '271', 'Дж', 'J'),
(61, 'Килоджоуль', '273', 'кДж', 'kJ'),
(62, 'Ом', '274', 'Ом', '<омега>'),
(63, 'Градус Цельсия', '280', 'град. C', 'град. C'),
(64, 'Градус Фаренгейта', '281', 'град. F', 'град. F'),
(65, 'Кандела', '282', 'кд', 'cd'),
(66, 'Люкс', '283', 'лк', 'lx'),
(67, 'Люмен', '284', 'лм', 'lm'),
(68, 'Кельвин', '288', 'K', 'K'),
(69, 'Ньютон', '289', 'Н', 'N'),
(70, 'Герц', '290', 'Гц', 'Hz'),
(71, 'Килогерц', '291', 'кГц', 'kHz'),
(72, 'Мегагерц', '292', 'МГц', 'MHz'),
(73, 'Паскаль', '294', 'Па', 'Pa'),
(74, 'Сименс', '296', 'См', 'S'),
(75, 'Килопаскаль', '297', 'кПа', 'kPa'),
(76, 'Мегапаскаль', '298', 'МПа', 'MPa'),
(77, 'Физическая атмосфера (101325 Па)', '300', 'атм', 'atm'),
(78, 'Техническая атмосфера (98066.5 Па)', '301', 'ат', 'at'),
(79, 'Гигабеккерель', '302', 'ГБк', 'GBq'),
(80, 'Милликюри', '304', 'мКи', 'mCi'),
(81, 'Кюри', '305', 'Ки', 'Ci'),
(82, 'Грамм делящихся изотопов', '306', 'г Д/И', 'g fissile isotopes'),
(83, 'Миллибар', '308', 'мб', 'mbar'),
(84, 'Бар', '309', 'бар', 'bar'),
(85, 'Гектобар', '310', 'гб', 'hbar'),
(86, 'Килобар', '312', 'кб', 'kbar'),
(87, 'Фарад', '314', 'Ф', 'F'),
(88, 'Килограмм на кубический метр', '316', 'кг/м3', 'kg/m3'),
(89, 'Беккерель', '323', 'Бк', 'Bq'),
(90, 'Вебер', '324', 'Вб', 'Wb'),
(91, 'Узел (миля/ч)', '327', 'уз', 'kn'),
(92, 'Метр в секунду', '328', 'м/с', 'm/s'),
(93, 'Оборот в секунду', '330', 'об/с', 'r/s'),
(94, 'Оборот в минуту', '331', 'об/мин', 'r/min'),
(95, 'Километр в час', '333', 'км/ч', 'km/h'),
(96, 'Метр на секунду в квадрате', '335', 'м/с2', 'm/s2'),
(97, 'Кулон на килограмм', '349', 'Кл/кг', 'C/kg'),
(98, 'Секунда', '354', 'с', 's'),
(99, 'Минута', '355', 'мин', 'min'),
(100, 'Час', '356', 'ч', 'h'),
(101, 'Сутки', '359', 'сут; дн', 'd'),
(102, 'Неделя', '360', 'нед', '-'),
(103, 'Декада', '361', 'дек', '-'),
(104, 'Месяц', '362', 'мес', '-'),
(105, 'Квартал', '364', 'кварт', '-'),
(106, 'Полугодие', '365', 'полгода', '-'),
(107, 'Год', '366', 'г; лет', 'a'),
(108, 'Десятилетие', '368', 'деслет', '-'),
(109, 'Килограмм в секунду', '499', 'кг/с', '-'),
(110, 'Тонна пара в час', '533', 'т пар/ч', '-'),
(111, 'Кубический метр в секунду', '596', 'м3/с', 'm3/s'),
(112, 'Кубический метр в час', '598', 'м3/ч', 'm3/h'),
(113, 'Тысяча кубических метров в сутки', '599', '10^3 м3/сут', '-'),
(114, 'Бобина', '616', 'боб', '-'),
(115, 'Лист', '625', 'л.', '-'),
(116, 'Сто листов', '626', '100 л.', '-'),
(117, 'Тысяча стандартных условных кирпичей', '630', 'тыс станд. усл. кирп', '-'),
(118, 'Дюжина (12 шт.)', '641', 'дюжина', 'Doz; 12'),
(119, 'Изделие', '657', 'изд', '-'),
(120, 'Сто ящиков', '683', '100 ящ.', 'Hbx'),
(121, 'Набор', '704', 'набор', '-'),
(122, 'Пара (2 шт.)', '715', 'пар', 'pr; 2'),
(123, 'Два десятка', '730', '20', '20'),
(124, 'Десять пар', '732', '10 пар', '-'),
(125, 'Дюжина пар', '733', 'дюжина пар', '-'),
(126, 'Посылка', '734', 'посыл', '-'),
(127, 'Часть', '735', 'часть', '-'),
(128, 'Рулон', '736', 'рул', '-'),
(129, 'Дюжина рулонов', '737', 'дюжина рул', '-'),
(130, 'Дюжина штук', '740', 'дюжина шт', '-'),
(131, 'Элемент', '745', 'элем', 'CI'),
(132, 'Упаковка', '778', 'упак', '-'),
(133, 'Дюжина упаковок', '780', 'дюжина упак', '-'),
(134, 'Сто упаковок', '781', '100 упак', '-'),
(135, 'Штука', '796', 'шт', 'pc; 1'),
(136, 'Сто штук', '797', '100 шт', '100'),
(137, 'Тысяча штук', '798', 'тыс. шт; 1000 шт', '1000'),
(138, 'Миллион штук', '799', '10^6 шт', '10^6'),
(139, 'Миллиард штук', '800', '10^9 шт', '10^9'),
(140, 'Биллион штук (Европа); триллион штук', '801', '10^12 шт', '10^12'),
(141, 'Квинтильон штук (Европа)', '802', '10^18 шт', '10^18'),
(142, 'Крепость спирта по массе', '820', 'креп. спирта по массе', '% mds'),
(143, 'Крепость спирта по объему', '821', 'креп. спирта по объему', '% vol'),
(144, 'Литр чистого (100%) спирта', '831', 'л 100% спирта', '-'),
(145, 'Гектолитр чистого (100%) спирта', '833', 'Гл 100% спирта', '-'),
(146, 'Килограмм пероксида водорода', '841', 'кг H2О2', '-'),
(147, 'Килограмм 90%-го сухого вещества', '845', 'кг 90% с/в', '-'),
(148, 'Тонна 90%-го сухого вещества', '847', 'т 90% с/в', '-'),
(149, 'Килограмм оксида калия', '852', 'кг К2О', '-'),
(150, 'Килограмм гидроксида калия', '859', 'кг КОН', '-'),
(151, 'Килограмм азота', '861', 'кг N', '-'),
(152, 'Килограмм гидроксида натрия', '863', 'кг NaOH', '-'),
(153, 'Килограмм пятиокиси фосфора', '865', 'кг Р2О5', '-'),
(154, 'Килограмм урана', '867', 'кг U', '-'),
(155, 'Погонный метр', '018', 'пог. м', ''),
(156, 'Тысяча погонных метров', '019', '10^3 пог. м', ''),
(157, 'Условный метр', '020', 'усл. м', ''),
(158, 'Тысяча условных метров', '048', '10^3 усл. м', ''),
(159, 'Километр условных труб', '049', 'км усл. труб', ''),
(160, 'Тысяча квадратных дециметров', '054', '10^3 дм2', ''),
(161, 'Миллион квадратных дециметров', '056', '10^6 дм2', ''),
(162, 'Миллион квадратных метров', '057', '10^6 м2', ''),
(163, 'Тысяча гектаров', '060', '10^3 га', ''),
(164, 'Условный квадратный метр', '062', 'усл. м2', ''),
(165, 'Тысяча условных квадратных метров', '063', '10^3 усл. м2', ''),
(166, 'Миллион условных квадратных метров', '064', '10^6 усл. м2', ''),
(167, 'Квадратный метр общей площади', '081', 'м2 общ. пл', ''),
(168, 'Тысяча квадратных метров общей площади', '082', '10^3 м2 общ. пл', ''),
(169, 'Миллион квадратных метров общей площади', '083', '10^6 м2 общ. пл', ''),
(170, 'Квадратный метр жилой площади', '084', 'м2 жил. пл', ''),
(171, 'Тысяча квадратных метров жилой площади', '085', '10^3 м2 жил. пл', ''),
(172, 'Миллион квадратных метров жилой площади', '086', '10^6 м2 жил. пл', ''),
(173, 'Квадратный метр учебно-лабораторных зданий', '087', 'м2 уч. лаб. здан', ''),
(174, 'Тысяча квадратных метров учебно-лабораторных зданий', '088', '10^3 м2 уч. лаб. здан', ''),
(175, 'Миллион квадратных метров в двухмиллиметровом исчислении', '089', '10^6 м2 2 мм исч', ''),
(176, 'Тысяча кубических метров', '114', '10^3 м3', ''),
(177, 'Миллиард кубических метров', '115', '10^9 м3', ''),
(178, 'Декалитр', '116', 'дкл', ''),
(179, 'Тысяча декалитров', '119', '10^3 дкл', ''),
(180, 'Миллион декалитров', '120', '10^6 дкл', ''),
(181, 'Плотный кубический метр', '121', 'плотн. м3', ''),
(182, 'Условный кубический метр', '123', 'усл. м3', ''),
(183, 'Тысяча условных кубических метров', '124', '10^3 усл. м3', ''),
(184, 'Миллион кубических метров переработки газа', '125', '10^6 м3 перераб. газа', ''),
(185, 'Тысяча плотных кубических метров', '127', '10^3 плотн. м3', ''),
(186, 'Тысяча полулитров', '128', '10^3 пол. л', ''),
(187, 'Миллион полулитров', '129', '10^6 пол. л', ''),
(188, 'Тысяча литров; 1000 литров', '130', '10^3 л; 1000 л', ''),
(189, 'Тысяча каратов метрических', '165', '10^3 кар', ''),
(190, 'Миллион каратов метрических', '167', '10^6 кар', ''),
(191, 'Тысяча тонн', '169', '10^3 т', ''),
(192, 'Миллион тонн', '171', '10^6 т', ''),
(193, 'Тонна условного топлива', '172', 'т усл. топл', ''),
(194, 'Тысяча тонн условного топлива', '175', '10^3 т усл. топл', ''),
(195, 'Миллион тонн условного топлива', '176', '10^6 т усл. топл', ''),
(196, 'Тысяча тонн единовременного хранения', '177', '10^3 т единовр. хран', ''),
(197, 'Тысяча тонн переработки', '178', '10^3 т перераб', ''),
(198, 'Условная тонна', '179', 'усл. т', ''),
(199, 'Тысяча центнеров', '207', '10^3 ц', ''),
(200, 'Вольт-ампер', '226', 'В.А', ''),
(201, 'Метр в час', '231', 'м/ч', ''),
(202, 'Килокалория', '232', 'ккал', ''),
(203, 'Гигакалория', '233', 'Гкал', ''),
(204, 'Тысяча гигакалорий', '234', '10^3 Гкал', ''),
(205, 'Миллион гигакалорий', '235', '10^6 Гкал', ''),
(206, 'Калория в час', '236', 'кал/ч', ''),
(207, 'Килокалория в час', '237', 'ккал/ч', ''),
(208, 'Гигакалория в час', '238', 'Гкал/ч', ''),
(209, 'Тысяча гигакалорий в час', '239', '10^3 Гкал/ч', ''),
(210, 'Миллион ампер-часов', '241', '10^6 А.ч', ''),
(211, 'Миллион киловольт-ампер', '242', '10^6 кВ.А', ''),
(212, 'Киловольт-ампер реактивный', '248', 'кВ.А Р', ''),
(213, 'Миллиард киловатт-часов', '249', '10^9 кВт.ч', ''),
(214, 'Тысяча киловольт-ампер реактивных', '250', '10^3 кВ.А Р', ''),
(215, 'Лошадиная сила', '251', 'л. с', ''),
(216, 'Тысяча лошадиных сил', '252', '10^3 л. с', ''),
(217, 'Миллион лошадиных сил', '253', '10^6 л. с', ''),
(218, 'Бит', '254', 'бит', ''),
(219, 'Байт', '255', 'бай', ''),
(220, 'Килобайт', '256', 'кбайт', ''),
(221, 'Мегабайт', '257', 'Мбайт', ''),
(222, 'Бод', '258', 'бод', ''),
(223, 'Генри', '287', 'Гн', ''),
(224, 'Тесла', '313', 'Тл', ''),
(225, 'Килограмм на квадратный сантиметр', '317', 'кг/см^2', ''),
(226, 'Миллиметр водяного столба', '337', 'мм вод. ст', ''),
(227, 'Миллиметр ртутного столба', '338', 'мм рт. ст', ''),
(228, 'Сантиметр водяного столба', '339', 'см вод. ст', ''),
(229, 'Микросекунда', '352', 'мкс', ''),
(230, 'Миллисекунда', '353', 'млс', ''),
(231, 'Рубль', '383', 'руб', ''),
(232, 'Тысяча рублей', '384', '10^3 руб', ''),
(233, 'Миллион рублей', '385', '10^6 руб', ''),
(234, 'Миллиард рублей', '386', '10^9 руб', ''),
(235, 'Триллион рублей', '387', '10^12 руб', ''),
(236, 'Квадрильон рублей', '388', '10^15 руб', ''),
(237, 'Пассажиро-километр', '414', 'пасс.км', ''),
(238, 'Пассажирское место (пассажирских мест)', '421', 'пасс. мест', ''),
(239, 'Тысяча пассажиро-километров', '423', '10^3 пасс.км', ''),
(240, 'Миллион пассажиро-километров', '424', '10^6 пасс. км', ''),
(241, 'Пассажиропоток', '427', 'пасс.поток', ''),
(242, 'Тонно-километр', '449', 'т.км', ''),
(243, 'Тысяча тонно-километров', '450', '10^3 т.км', ''),
(244, 'Миллион тонно-километров', '451', '10^6 т. км', ''),
(245, 'Тысяча наборов', '479', '10^3 набор', ''),
(246, 'Грамм на киловатт-час', '510', 'г/кВт.ч', ''),
(247, 'Килограмм на гигакалорию', '511', 'кг/Гкал', ''),
(248, 'Тонно-номер', '512', 'т.ном', ''),
(249, 'Автотонна', '513', 'авто т', ''),
(250, 'Тонна тяги', '514', 'т.тяги', ''),
(251, 'Дедвейт-тонна', '515', 'дедвейт.т', ''),
(252, 'Тонно-танид', '516', 'т.танид', ''),
(253, 'Человек на квадратный метр', '521', 'чел/м2', ''),
(254, 'Человек на квадратный километр', '522', 'чел/км2', ''),
(255, 'Тонна в час', '534', 'т/ч', ''),
(256, 'Тонна в сутки', '535', 'т/сут', ''),
(257, 'Тонна в смену', '536', 'т/смен', ''),
(258, 'Тысяча тонн в сезон', '537', '10^3 т/сез', ''),
(259, 'Тысяча тонн в год', '538', '10^3 т/год', ''),
(260, 'Человеко-час', '539', 'чел.ч', ''),
(261, 'Человеко-день', '540', 'чел.дн', ''),
(262, 'Тысяча человеко-дней', '541', '10^3 чел.дн', ''),
(263, 'Тысяча человеко-часов', '542', '10^3 чел.ч', ''),
(264, 'Тысяча условных банок в смену', '543', '10^3 усл. банк/ смен', ''),
(265, 'Миллион единиц в год', '544', '10^6 ед/год', ''),
(266, 'Посещение в смену', '545', 'посещ/смен', ''),
(267, 'Тысяча посещений в смену', '546', '10^3 посещ/смен', ''),
(268, 'Пара в смену', '547', 'пар/смен', ''),
(269, 'Тысяча пар в смену', '548', '10^3 пар/смен', ''),
(270, 'Миллион тонн в год', '550', '10^6 т/год', ''),
(271, 'Тонна переработки в сутки', '552', 'т перераб/сут', ''),
(272, 'Тысяча тонн переработки в сутки', '553', '10^3 т перераб/ сут', ''),
(273, 'Центнер переработки в сутки', '554', 'ц перераб/сут', ''),
(274, 'Тысяча центнеров переработки в сутки', '555', '10^3 ц перераб/ сут', ''),
(275, 'Тысяча голов в год', '556', '10^3 гол/год', ''),
(276, 'Миллион голов в год', '557', '10^6 гол/год', ''),
(277, 'Тысяча птицемест', '558', '10^3 птицемест', ''),
(278, 'Тысяча кур-несушек', '559', '10^3 кур. несуш', ''),
(279, 'Минимальная заработная плата', '560', 'мин. заработн. плат', ''),
(280, 'Тысяча тонн пара в час', '561', '10^3 т пар/ч', ''),
(281, 'Тысяча прядильных веретен', '562', '10^3 пряд.верет', ''),
(282, 'Тысяча прядильных мест', '563', '10^3 пряд.мест', ''),
(283, 'Доза', '639', 'доз', ''),
(284, 'Тысяча доз', '640', '10^3 доз', ''),
(285, 'Единица', '642', 'ед', ''),
(286, 'Тысяча единиц', '643', '10^3 ед', ''),
(287, 'Миллион единиц', '644', '10^6 ед', ''),
(288, 'Канал', '661', 'канал', ''),
(289, 'Тысяча комплектов', '673', '10^3 компл', ''),
(290, 'Место', '698', 'мест', ''),
(291, 'Тысяча мест', '699', '10^3 мест', ''),
(292, 'Тысяча номеров', '709', '10^3 ном', ''),
(293, 'Тысяча гектаров порций', '724', '10^3 га порц', ''),
(294, 'Тысяча пачек', '729', '10^3 пач', ''),
(295, 'Процент', '744', '%', ''),
(296, 'Промилле (0.1 процента)', '746', 'промилле', ''),
(297, 'Тысяча рулонов', '751', '10^3 рул', ''),
(298, 'Тысяча станов', '761', '10^3 стан', ''),
(299, 'Станция', '762', 'станц', ''),
(300, 'Тысяча тюбиков', '775', '10^3 тюбик', ''),
(301, 'Тысяча условных тубов', '776', '10^3 усл.туб', ''),
(302, 'Миллион упаковок', '779', '10^6 упак', ''),
(303, 'Тысяча упаковок', '782', '10^3 упак', ''),
(304, 'Человек', '792', 'чел', ''),
(305, 'Тысяча человек', '793', '10^3 чел', ''),
(306, 'Миллион человек', '794', '10^6 чел', ''),
(307, 'Миллион экземпляров', '808', '10^6 экз', ''),
(308, 'Ячейка', '810', 'яч', ''),
(309, 'Ящик', '812', 'ящ', ''),
(310, 'Голова', '836', 'гол', ''),
(311, 'Тысяча пар', '837', '10^3 пар', ''),
(312, 'Миллион пар', '838', '10^6 пар', ''),
(313, 'Комплект', '839', 'компл', ''),
(314, 'Секция', '840', 'секц', ''),
(315, 'Бутылка', '868', 'бут', ''),
(316, 'Тысяча бутылок', '869', '10^3 бут', ''),
(317, 'Ампула', '870', 'ампул', ''),
(318, 'Тысяча ампул', '871', '10^3 ампул', ''),
(319, 'Флакон', '872', 'флак', ''),
(320, 'Тысяча флаконов', '873', '10^3 флак', ''),
(321, 'Тысяча тубов', '874', '10^3 туб', ''),
(322, 'Тысяча коробок', '875', '10^3 кор', ''),
(323, 'Условная единица', '876', 'усл. ед', ''),
(324, 'Тысяча условных единиц', '877', '10^3 усл. ед', ''),
(325, 'Миллион условных единиц', '878', '10^6 усл. ед', ''),
(326, 'Условная штука', '879', 'усл. шт', ''),
(327, 'Тысяча условных штук', '880', '10^3 усл. шт', ''),
(328, 'Условная банка', '881', 'усл. банк', ''),
(329, 'Тысяча условных банок', '882', '10^3 усл. банк', ''),
(330, 'Миллион условных банок', '883', '10^6 усл. банк', ''),
(331, 'Условный кусок', '884', 'усл. кус', ''),
(332, 'Тысяча условных кусков', '885', '10^3 усл. кус', ''),
(333, 'Миллион условных кусков', '886', '10^6 усл. кус', ''),
(334, 'Условный ящик', '887', 'усл. ящ', ''),
(335, 'Тысяча условных ящиков', '888', '10^3 усл. ящ', ''),
(336, 'Условная катушка', '889', 'усл. кат', ''),
(337, 'Тысяча условных катушек', '890', '10^3 усл. кат', ''),
(338, 'Условная плитка', '891', 'усл. плит', ''),
(339, 'Тысяча условных плиток', '892', '10^3 усл. плит', ''),
(340, 'Условный кирпич', '893', 'усл. кирп', ''),
(341, 'Тысяча условных кирпичей', '894', '10^3 усл. кирп', ''),
(342, 'Миллион условных кирпичей', '895', '10^6 усл. кирп', ''),
(343, 'Семья', '896', 'семей', ''),
(344, 'Тысяча семей', '897', '10^3 семей', ''),
(345, 'Миллион семей', '898', '10^6 семей', ''),
(346, 'Домохозяйство', '899', 'домхоз', ''),
(347, 'Тысяча домохозяйств', '900', '10^3 домхоз', ''),
(348, 'Миллион домохозяйств', '901', '10^6 домхоз', ''),
(349, 'Ученическое место', '902', 'учен. мест', ''),
(350, 'Тысяча ученических мест', '903', '10^3 учен. мест', ''),
(351, 'Рабочее место', '904', 'раб. мест', ''),
(352, 'Тысяча рабочих мест', '905', '10^3 раб. мест', ''),
(353, 'Посадочное место', '906', 'посад. мест', ''),
(354, 'Тысяча посадочных мест', '907', '10^3 посад. мест', ''),
(355, 'Номер', '908', 'ном', ''),
(356, 'Квартира', '909', 'кварт', ''),
(357, 'Тысяча квартир', '910', '10^3 кварт', ''),
(358, 'Койка', '911', 'коек', ''),
(359, 'Тысяча коек', '912', '10^3 коек', ''),
(360, 'Том книжного фонда', '913', 'том книжн. фонд', ''),
(361, 'Тысяча томов книжного фонда', '914', '10^3 том. книжн. фонд', ''),
(362, 'Условный ремонт', '915', 'усл. рем', ''),
(363, 'Условный ремонт в год', '916', 'усл. рем/год', ''),
(364, 'Смена', '917', 'смен', ''),
(365, 'Лист авторский', '918', 'л. авт', ''),
(366, 'Лист печатный', '920', 'л. печ', ''),
(367, 'Лист учетно-издательский', '921', 'л. уч.-изд', ''),
(368, 'Знак', '922', 'знак', ''),
(369, 'Слово', '923', 'слово', ''),
(370, 'Символ', '924', 'символ', ''),
(371, 'Условная труба', '925', 'усл. труб', ''),
(372, 'Тысяча пластин', '930', '10^3 пласт', ''),
(373, 'Миллион доз', '937', '10^6 доз', ''),
(374, 'Миллион листов-оттисков', '949', '10^6 лист.оттиск', ''),
(375, 'Вагоно(машино)-день', '950', 'ваг (маш).дн', ''),
(376, 'Тысяча вагоно-(машино)-часов', '951', '10^3 ваг (маш).ч', ''),
(377, 'Тысяча вагоно-(машино)-километров', '952', '10^3 ваг (маш).км', ''),
(378, 'Тысяча место-километров', '953', '10 ^3мест.км', ''),
(379, 'Вагоно-сутки', '954', 'ваг.сут', ''),
(380, 'Тысяча поездо-часов', '955', '10^3 поезд.ч', ''),
(381, 'Тысяча поездо-километров', '956', '10^3 поезд.км', ''),
(382, 'Тысяча тонно-миль', '957', '10^3 т.миль', ''),
(383, 'Тысяча пассажиро-миль', '958', '10^3 пасс.миль', ''),
(384, 'Автомобиле-день', '959', 'автомоб.дн', ''),
(385, 'Тысяча автомобиле-тонно-дней', '960', '10^3 автомоб.т.дн', ''),
(386, 'Тысяча автомобиле-часов', '961', '10^3 автомоб.ч', ''),
(387, 'Тысяча автомобиле-место-дней', '962', '10^3 автомоб.мест. дн', ''),
(388, 'Приведенный час', '963', 'привед.ч', ''),
(389, 'Самолето-километр', '964', 'самолет.км', ''),
(390, 'Тысяча километров', '965', '10^3 км', ''),
(391, 'Тысяча тоннаже-рейсов', '966', '10^3 тоннаж. рейс', ''),
(392, 'Миллион тонно-миль', '967', '10^6 т. миль', ''),
(393, 'Миллион пассажиро-миль', '968', '10^6 пасс. миль', ''),
(394, 'Миллион тоннаже-миль', '969', '10^6 тоннаж. миль', ''),
(395, 'Миллион пассажиро-место-миль', '970', '10^6 пасс. мест. миль', ''),
(396, 'Кормо-день', '971', 'корм. дн', ''),
(397, 'Центнер кормовых единиц', '972', 'ц корм ед', ''),
(398, 'Тысяча автомобиле-километров', '973', '10^3 автомоб. км', ''),
(399, 'Тысяча тоннаже-сут', '974', '10^3 тоннаж. сут', ''),
(400, 'Суго-сутки', '975', 'суго. сут.', ''),
(401, 'Штук в 20-футовом эквиваленте (ДФЭ)', '976', 'штук в 20-футовом эквиваленте', ''),
(402, 'Канало-километр', '977', 'канал. км', ''),
(403, 'Канало-концы', '978', 'канал. конц', ''),
(404, 'Тысяча экземпляров', '979', '10^3 экз', ''),
(405, 'Тысяча долларов', '980', '10^3 доллар', ''),
(406, 'Тысяча тонн кормовых единиц', '981', '10^3 корм ед', ''),
(407, 'Миллион тонн кормовых единиц', '982', '10^6 корм ед', ''),
(408, 'Судо-сутки', '983', 'суд.сут', ''),
(409, 'Гектометр', '017', '', 'hm'),
(410, 'Миля (уставная) (1609.344 м)', '045', '', 'mile'),
(411, 'Акр (4840 квадратных ярдов)', '077', '', 'acre'),
(412, 'Квадратная миля', '079', '', 'mile2'),
(413, 'Жидкостная унция СК (28.413 см3)', '135', '', 'fl oz (UK)'),
(414, 'Джилл СК (0.142065 дм3)', '136', '', 'gill (UK)'),
(415, 'Пинта СК (0.568262 дм3)', '137', '', 'pt (UK)'),
(416, 'Кварта СК (1.136523 дм3)', '138', '', 'qt (UK)'),
(417, 'Галлон СК (4.546092 дм3)', '139', '', 'gal (UK)'),
(418, 'Бушель СК (36.36874 дм3)', '140', '', 'bu (UK)'),
(419, 'Жидкостная унция США (29.5735 см3)', '141', '', 'fl oz (US)'),
(420, 'Джилл США (11.8294 см3)', '142', '', 'gill (US)'),
(421, 'Жидкостная пинта США (0.473176 дм3)', '143', '', 'liq pt (US)'),
(422, 'Жидкостная кварта США (0.946353 дм3)', '144', '', 'liq qt (US)'),
(423, 'Жидкостный галлон США (3.78541 дм3)', '145', '', 'gal (US)'),
(424, 'Баррель (нефтяной) США (158.987 дм3)', '146', '', 'barrel (US)'),
(425, 'Сухая пинта США (0.55061 дм3)', '147', '', 'dry pt (US)'),
(426, 'Сухая кварта США (1.101221 дм3)', '148', '', 'dry qt (US)'),
(427, 'Сухой галлон США (4.404884 дм3)', '149', '', 'dry gal (US)'),
(428, 'Бушель США (35.2391 дм3)', '150', '', 'bu (US)'),
(429, 'Сухой баррель США (115.627 дм3)', '151', '', 'bbl (US)'),
(430, 'Стандарт', '152', '', '-'),
(431, 'Корд (3.63 м3)', '153', '', '-'),
(432, 'Тысячи бордфутов (2.36 м3)', '154', '', '-'),
(433, 'Нетто-регистровая тонна', '182', '', '-'),
(434, 'Обмерная (фрахтовая) тонна', '183', '', '-'),
(435, 'Водоизмещение', '184', '', '-'),
(436, 'Фунт СК. США (0.45359237 кг)', '186', '', 'lb'),
(437, 'Унция СК. США (28.349523 г)', '187', '', 'oz'),
(438, 'Драхма СК (1.771745 г)', '188', '', 'dr'),
(439, 'Гран СК. США (64.798910 мг)', '189', '', 'gn'),
(440, 'Стоун СК (6.350293 кг)', '190', '', 'st'),
(441, 'Квартер СК (12.700586 кг)', '191', '', 'qtr'),
(442, 'Центал СК (45.359237 кг)', '192', '', '-'),
(443, 'Центнер США (45.3592 кг)', '193', '', 'cwt'),
(444, 'Длинный центнер СК (50.802345 кг)', '194', '', 'cwt (UK)'),
(445, 'Короткая тонна СК. США (0.90718474 т) [2*]', '195', '', 'sht'),
(446, 'Длинная тонна СК. США (1.0160469 т) [2*]', '196', '', 'lt'),
(447, 'Скрупул СК. США (1.295982 г)', '197', '', 'scr'),
(448, 'Пеннивейт СК. США (1.555174 г)', '198', '', 'dwt'),
(449, 'Драхма СК (3.887935 г)', '199', '', 'drm'),
(450, 'Драхма США (3.887935 г)', '200', '', '-'),
(451, 'Унция СК. США (31.10348 г); тройская унция', '201', '', 'apoz'),
(452, 'Тройский фунт США (373.242 г)', '202', '', '-'),
(453, 'Эффективная мощность (245.7 ватт)', '213', '', 'B.h.p.'),
(454, 'Британская тепловая единица (1.055 кДж)', '275', '', 'Btu'),
(455, 'Гросс (144 шт.)', '638', '', 'gr; 144'),
(456, 'Большой гросс (12 гроссов)', '731', '', '1728'),
(457, 'Короткий стандарт (7200 единиц)', '738', '', '-'),
(458, 'Галлон спирта установленной крепости', '835', '', '-'),
(459, 'Международная единица', '851', '', '-'),
(460, 'Сто международных единиц', '853', '', '-');
";
echo_data_add($sql,$tbl);
//* *****************************************************************



///**********************************************************************************************

$tbl='s_shablon';

$sql="
INSERT INTO `s_shablon` (`id`, `chk_active`, `name`) VALUES
(1, 1, 'Верхнее меню'),
(2, 1, 'Левое меню'),
(3, 1, 'Нижнее меню'),
(4, 1, 'Правое меню'),
(5, 1, 'Слайдер на главной'),
(6, 1, 'Галерея'),
(7, 1, 'Информация'),
(8, 1, 'Новости'),
(9, 1, 'Отзывы');


";
echo_data_add($sql,$tbl);
//* *****************************************************************



///**********************************************************************************************

$tbl='s_words';
$sql="

INSERT INTO `s_words` (`id`, `name`, `html_code`, `data_change`, `data_create`) VALUES
(1, 'Название организации', '<p>Веб-студия V-Web.ru</p>', '2015-01-28 15:26:38', '2015-01-28 08:26:38'),
(2, 'Код', '<p>391</p>', '2015-01-28 15:26:47', '2015-01-28 08:26:47'),
(3, 'Телефон', '<p>271-27-03</p>', '2015-01-28 15:27:01', '2015-01-28 08:27:01'),
(4, 'Город', '<p>Красноярск</p>', '2015-01-28 15:27:12', '2015-01-28 08:27:12'),
(5, 'Улица', '<p>Маерчака</p>', '2015-01-28 15:27:33', '2015-01-28 08:27:33'),
(6, 'Дом', '<p>38</p>', '2015-01-28 15:27:39', '2015-01-28 08:27:39'),
(7, 'Офис', '<p>925</p>', '2015-01-28 15:27:47', '2015-01-28 08:27:47'),
(8, 'email', '<p>toowin86@yandex.ru</p>', '2015-01-28 15:28:07', '2015-01-28 08:28:07'),
(9, 'Логотип', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(10, 'Соц.сети: vk.com', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(11, 'Соц.сети: facebook.com', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(12, 'Соц.сети: plus.google.com', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(13, 'График работы', 'Пн.-Сб. 10:00 - 20:00', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(14, 'Карта', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(17, 'Скрипты в шапку сайта', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(30, 'Код 2', '', '2015-01-28 15:27:01', '2015-01-28 08:27:01'),
(31, 'Телефон 2', '', '2015-01-28 15:27:01', '2015-01-28 08:27:01'),
(40, 'Телефон для директа', '<p>246-27-03</p>', '2015-01-28 15:27:01', '2015-01-28 08:27:01'),
(41, 'Телефон для adwords', '<p>246-26-33</p>', '2015-01-28 15:27:01', '2015-01-28 08:27:01'),
(44, 'Скрипты в футер сайта', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47'),
(45, 'Текст в футере', '', '2015-01-28 16:27:47', '2015-01-28 08:29:47');


";
echo_data_add($sql,$tbl);
//* *****************************************************************

///**********************************************************************************************

$tbl='i_scheta';
$sql="

INSERT INTO `i_scheta` (`id`, `chk_active`, `sid`, `i_tp_id`, `name`, `data_create`) VALUES
(461, 1, 1, 1, 'Касса', '2015-06-21 07:59:39'),
(462, 1, 2, 1, 'Банковский счет', '2015-06-21 07:59:52'),
(463, 1, 3, 1, 'Яндекс-деньги', '2015-06-21 08:00:03');";

echo_data_add($sql,$tbl);
//* *****************************************************************

//* *****************************************************************

$tbl='i_rashodi';
$sql="
INSERT INTO `i_rashodi` (`id`, `chk_active`, `name`, `data_create`) VALUES
(1, 1, 'Аренда', '2016-04-09 12:34:01'),
(2, 1, 'Интернет', '2016-04-09 12:34:06'),
(3, 1, 'Рабочий телефон', '2016-04-09 12:34:15'),
(4, 1, 'Транспортные расходы', '2016-04-09 12:34:26'),
(5, 1, 'Налоги', '2016-04-09 12:34:31'),
(6, 1, 'Коммисия', '2016-04-09 12:34:40'),
(7, 1, 'Расходные материалы', '2016-04-09 12:35:21'),
(8, 1, 'Продукты', '2016-04-09 12:35:28'),
(9, 1, 'Возвраты', '2016-04-09 12:35:56'),
(10, 1, 'Штрафы', '2016-04-17 07:28:45');
";
echo_data_add($sql,$tbl);
//* *****************************************************************
//* *****************************************************************

$tbl='i_inout';
$sql="
INSERT INTO `i_inout` (`id`, `chk_active`, `name`, `data_create`) VALUES
(1, 1, 'Дивиденды / ввод на счет', '2016-04-09 12:34:01'),
(2, 1, 'Оборудование покупка / продажа', '2016-04-09 12:34:06'),
(3, 1, 'Транспорт покупка / продажа', '2016-04-09 12:34:06');
";
echo_data_add($sql,$tbl);
//* *****************************************************************


///**********************************************************************************************

$tbl='i_reklama';
$sql="

INSERT INTO `i_reklama` (`id`, `name`, `data_create`) VALUES
(1, 'Регистрация на сайте', '2015-06-07 15:04:33'),
(2, 'Интернет раскрутка', '2015-06-20 10:56:29'),
(3, 'От знакомых', '2015-06-20 10:57:01'),
(4, 'Наружная реклама', '2015-06-20 10:57:15'),
(5, '2GIS или Yarmap', '2015-06-20 10:57:41'),
(6, 'Flamp', '2015-06-20 10:57:53'),
(7, 'Визитки или листовки', '2015-06-20 10:59:06'),
(8, 'Радио', '2015-06-20 10:59:14'),
(9, 'Директ', '2015-06-20 10:59:26'),
(10, 'Телевидение', '2015-06-20 10:59:48');";

echo_data_add($sql,$tbl);


//* *****************************************************************


$tbl='i_post';
$sql="
INSERT INTO `i_post` (`id`, `name`, `obj`) VALUES
(1, 'Руководитель отдела продаж','Заказ'),
(2, 'Менеджер по работе с клиентами','Заказ'),
(3, 'Руководитель отдела поставок','Поступление'),
(4, 'Менеджер по поставкам','Поступление'),
(5, 'Специалист','Работа'),
(6, 'Подрядчик','Работа');

";
echo_data_add($sql,$tbl);

$sql_upp = "UPDATE i_post SET obj='Заказ' WHERE id IN ('1','2')";
$res = mysql_query($sql_upp) or die(mysql_error().'<br />'.$sql_upp);
$sql_upp = "UPDATE i_post SET obj='Поступление' WHERE id IN ('3','4')";
$res = mysql_query($sql_upp) or die(mysql_error().'<br />'.$sql_upp);

//* *****************************************************************


//* *****************************************************************



//* *****************************************************************


///**********************************************************************************************

$tbl='i_city';
$sql="


INSERT INTO `city` (`id`, `name`, `region`, `nomer`, `nom_region`) VALUES
(1, 'Абаза', 'Хакасия', 1902, 19),
(2, 'Абакан', 'Хакасия', 1901, 19),
(3, 'Абдулино', 'Оренбургская область', 5602, 56),
(4, 'Абинск', 'Краснодарский край', 2302, 23),
(5, 'Агидель', 'Башкортостан', 202, 2),
(6, 'Агрыз', 'Татарстан', 1602, 16),
(7, 'Адыгейск', 'Адыгея', 102, 1),
(8, 'Азнакаево', 'Татарстан', 1603, 16),
(9, 'Азов', 'Ростовская область', 6102, 61),
(10, 'Ак-Довурак', 'Тыва', 1702, 17),
(11, 'Аксай', 'Ростовская область', 6103, 61),
(12, 'Алагир', 'Северная Осетия-Алания', 1502, 15),
(13, 'Алапаевск', 'Свердловская область', 6602, 66),
(14, 'Алатырь', 'Чувашия', 2102, 21),
(15, 'Алдан', 'Якутия', 1402, 14),
(16, 'Алейск', 'Алтайский край', 2202, 22),
(17, 'Александров', 'Владимирская область', 3302, 33),
(18, 'Александровск', 'Пермский край', 5902, 59),
(19, 'Александровск-Сахалинский', 'Сахалинская область', 6502, 65),
(20, 'Алексеевка', 'Белгородская область', 3102, 31),
(21, 'Алексин', 'Тульская область', 7102, 71),
(22, 'Алзамай', 'Иркутская область', 3802, 38),
(23, 'Альметьевск', 'Татарстан', 1604, 16),
(24, 'Амурск', 'Хабаровский край', 2702, 27),
(25, 'Анадырь', 'Чукотский АО', 8701, 87),
(26, 'Анапа', 'Краснодарский край', 2303, 23),
(27, 'Ангарск', 'Иркутская область', 3803, 38),
(28, 'Андреаполь', 'Тверская область', 6902, 69),
(29, 'Анжеро-Судженск', 'Кемеровская область', 4202, 42),
(30, 'Анива', 'Сахалинская область', 6503, 65),
(31, 'Апатиты', 'Мурманская область', 5102, 51),
(32, 'Апрелевка', 'Московская область', 5001, 50),
(33, 'Апшеронск', 'Краснодарский край', 2304, 23),
(34, 'Арамиль', 'Свердловская область', 6603, 66),
(35, 'Ардатов', 'Мордовия', 1302, 13),
(36, 'Ардон', 'Северная Осетия-Алания', 1503, 15),
(37, 'Арзамас', 'Нижегородская область', 5202, 52),
(38, 'Аркадак', 'Саратовская область', 6402, 64),
(39, 'Армавир', 'Краснодарский край', 2305, 23),
(40, 'Арсеньев', 'Приморский край', 2502, 25),
(41, 'Арск', 'Татарстан', 1622, 16),
(42, 'Артем', 'Приморский край', 2503, 25),
(43, 'Артемовск', 'Красноярский край', 2402, 24),
(44, 'Артемовский', 'Свердловская область', 6604, 66),
(45, 'Архангельск', 'Архангельская область', 2901, 29),
(46, 'Асбест', 'Свердловская область', 6605, 66),
(47, 'Асино', 'Томская область', 7002, 70),
(48, 'Астрахань', 'Астраханская область', 3001, 30),
(49, 'Аткарск', 'Саратовская область', 6403, 64),
(50, 'Ахтубинск', 'Астраханская область', 3002, 30),
(51, 'Ачинск', 'Красноярский край', 2403, 24),
(52, 'Аша', 'Челябинская область', 7402, 74),
(53, 'Бабаево', 'Вологодская область', 3502, 35),
(54, 'Бабушкин', 'Бурятия', 302, 3),
(55, 'Бавлы', 'Татарстан', 1605, 16),
(56, 'Багратионовск', 'Калининградская область', 3902, 39),
(57, 'Байкальск', 'Иркутская область', 3804, 38),
(58, 'Баймак', 'Башкортостан', 203, 2),
(59, 'Бакал', 'Челябинская область', 7403, 74),
(60, 'Баксан', 'Кабардино-Балкария', 702, 7),
(61, 'Балабаново', 'Калужская область', 4002, 40),
(62, 'Балаково', 'Саратовская область', 6404, 64),
(63, 'Балахна', 'Нижегородская область', 5203, 52),
(64, 'Балашиха', 'Московская область', 5002, 50),
(65, 'Балашов', 'Саратовская область', 6405, 64),
(66, 'Балей', 'Забайкальский край', 7502, 75),
(67, 'Балтийск', 'Калининградская область', 3903, 39),
(68, 'Барабинск', 'Новосибирская область', 5402, 54),
(69, 'Барнаул', 'Алтайский край', 2201, 22),
(70, 'Барыш', 'Ульяновская область', 7302, 73),
(71, 'Батайск', 'Ростовская область', 6104, 61),
(72, 'Бежецк', 'Тверская область', 6903, 69),
(73, 'Белая Калитва', 'Ростовская область', 6105, 61),
(74, 'Белая Холуница', 'Кировская область', 4302, 43),
(75, 'Белгород', 'Белгородская область', 3101, 31),
(76, 'Белебей', 'Башкортостан', 204, 2),
(77, 'Белев', 'Тульская область', 7103, 71),
(78, 'Белинский', 'Пензенская область', 5803, 58),
(79, 'Белово', 'Кемеровская область', 4203, 42),
(80, 'Белогорск', 'Амурская область', 2802, 28),
(81, 'Белозерск', 'Вологодская область', 3503, 35),
(82, 'Белокуриха', 'Алтайский край', 2203, 22),
(83, 'Беломорск', 'Карелия', 1002, 10),
(84, 'Белорецк', 'Башкортостан', 205, 2),
(85, 'Белореченск', 'Краснодарский край', 2306, 23),
(86, 'Белоусово', 'Калужская область', 4003, 40),
(87, 'Белоярский', 'Ханты-мансийский АО', 8602, 86),
(88, 'Белый', 'Тверская область', 6904, 69),
(89, 'Бердск', 'Новосибирская область', 5403, 54),
(90, 'Березники', 'Пермский край', 5903, 59),
(91, 'Березовский', 'Свердловская область', 6606, 66),
(92, 'Березовский', 'Кемеровская область', 4204, 42),
(93, 'Беслан', 'Северная Осетия-Алания', 1504, 15),
(94, 'Бийск', 'Алтайский край', 2204, 22),
(95, 'Бикин', 'Хабаровский край', 2703, 27),
(96, 'Билибино', 'Чукотский АО', 8702, 87),
(97, 'Биробиджан', 'Еврейская АО', 7901, 79),
(98, 'Бирск', 'Башкортостан', 206, 2),
(99, 'Бирюсинск', 'Иркутская область', 3805, 38),
(100, 'Бирюч', 'Белгородская область', 3111, 31),
(101, 'Благовещенск', 'Башкортостан', 207, 2),
(102, 'Благовещенск', 'Амурская область', 2801, 28),
(103, 'Благодарный', 'Ставропольский край', 2602, 26),
(104, 'Бобров', 'Воронежская область', 3602, 36),
(105, 'Богданович', 'Свердловская область', 6607, 66),
(106, 'Богородицк', 'Тульская область', 7104, 71),
(107, 'Богородск', 'Нижегородская область', 5204, 52),
(108, 'Боготол', 'Красноярский край', 2404, 24),
(109, 'Богучаны', 'Красноярский край', 2425, 24),
(110, 'Богучар', 'Воронежская область', 3603, 36),
(111, 'Бодайбо', 'Иркутская область', 3806, 38),
(112, 'Бокситогорск', 'Ленинградская область', 4701, 47),
(113, 'Болгар', 'Татарстан', 1616, 16),
(114, 'Бологое', 'Тверская область', 6905, 69),
(115, 'Болотное', 'Новосибирская область', 5404, 54),
(116, 'Болохово', 'Тульская область', 7105, 71),
(117, 'Болхов', 'Орловская область', 5702, 57),
(118, 'Большой Камень', 'Приморский край', 2504, 25),
(119, 'Бор', 'Нижегородская область', 5205, 52),
(120, 'Борзя', 'Забайкальский край', 7503, 75),
(121, 'Борисоглебск', 'Воронежская область', 3604, 36),
(122, 'Боровичи', 'Новгородская область', 5302, 53),
(123, 'Боровск', 'Калужская область', 4004, 40),
(124, 'Бородино', 'Красноярский край', 2405, 24),
(125, 'Братск', 'Иркутская область', 3807, 38),
(126, 'Бронницы', 'Московская область', 5003, 50),
(127, 'Брянск', 'Брянская область', 3201, 32),
(128, 'Бугульма', 'Татарстан', 1606, 16),
(129, 'Бугуруслан', 'Оренбургская область', 5603, 56),
(130, 'Буденновск', 'Ставропольский край', 2603, 26),
(131, 'Бузулук', 'Оренбургская область', 5604, 56),
(132, 'Буинск', 'Татарстан', 1607, 16),
(133, 'Буй', 'Костромская область', 4402, 44),
(134, 'Буйнакск', 'Дагестан', 502, 5),
(135, 'Бутурлиновка', 'Воронежская область', 3605, 36),
(136, 'Валдай', 'Новгородская область', 5303, 53),
(137, 'Валуйки', 'Белгородская область', 3103, 31),
(138, 'Велиж', 'Смоленская область', 6702, 67),
(139, 'Великие Луки', 'Псковская область', 6002, 60),
(140, 'Великий Новгород', 'Новгородская область', 5301, 53),
(141, 'Великий Устюг', 'Вологодская область', 3504, 35),
(142, 'Вельск', 'Архангельская область', 2902, 29),
(143, 'Венев', 'Тульская область', 7106, 71),
(144, 'Верещагино', 'Пермский край', 5904, 59),
(145, 'Верея', 'Московская область', 5004, 50),
(146, 'Верхнеуральск', 'Челябинская область', 7404, 74),
(147, 'Верхний Тагил', 'Свердловская область', 6608, 66),
(148, 'Верхний Уфалей', 'Челябинская область', 7405, 74),
(149, 'Верхняя Пышма', 'Свердловская область', 6609, 66),
(150, 'Верхняя Салда', 'Свердловская область', 6610, 66),
(151, 'Верхняя Тура', 'Свердловская область', 6611, 66),
(152, 'Верхотурье', 'Свердловская область', 6612, 66),
(153, 'Верхоянск', 'Якутия', 1403, 14),
(154, 'Весьегонск', 'Тверская область', 6906, 69),
(155, 'Ветлуга', 'Нижегородская область', 5206, 52),
(156, 'Видное', 'Московская область', 5005, 50),
(157, 'Вилюйск', 'Якутия', 1404, 14),
(158, 'Вилючинск', 'Камчатский край', 4102, 41),
(159, 'Вихоревка', 'Иркутская область', 3808, 38),
(160, 'Вичуга', 'Ивановская область', 3702, 37),
(161, 'Владивосток', 'Приморский край', 2501, 25),
(162, 'Владикавказ', 'Северная Осетия-Алания', 1501, 15),
(163, 'Владимир', 'Владимирская область', 3301, 33),
(164, 'Волгоград', 'Волгоградская область', 3401, 34),
(165, 'Волгодонск', 'Ростовская область', 6106, 61),
(166, 'Волгореченск', 'Костромская область', 4403, 44),
(167, 'Волжск', 'Марий Эл', 1202, 12),
(168, 'Волжский', 'Волгоградская область', 3402, 34),
(169, 'Вологда', 'Вологодская область', 3501, 35),
(170, 'Володарск', 'Нижегородская область', 5207, 52),
(171, 'Волоколамск', 'Московская область', 5006, 50),
(172, 'Волосово', 'Липецкая область', 4809, 48),
(173, 'Волхов', 'Ленинградская область', 4702, 47),
(174, 'Волчанск', 'Свердловская область', 6613, 66),
(175, 'Вольск', 'Саратовская область', 6406, 64),
(176, 'Воркута', 'Коми', 1102, 11),
(177, 'Воронеж', 'Воронежская область', 3601, 36),
(178, 'Ворсма', 'Нижегородская область', 5208, 52),
(179, 'Воскресенск', 'Московская область', 5007, 50),
(180, 'Воткинск', 'Удмуртия', 1802, 18),
(181, 'Всеволожск', 'Ленинградская область', 4703, 47),
(182, 'Вуктыл', 'Коми', 1103, 11),
(183, 'Выборг', 'Ленинградская область', 4704, 47),
(184, 'Выкса', 'Нижегородская область', 5209, 52),
(185, 'Высоковск', 'Московская область', 5008, 50),
(186, 'Высоцк', 'Ленинградская область', 4705, 47),
(187, 'Вытегра', 'Вологодская область', 3505, 35),
(188, 'Вышний Волочек', 'Тверская область', 6907, 69),
(189, 'Вяземский', 'Хабаровский край', 2704, 27),
(190, 'Вязники', 'Владимирская область', 3303, 33),
(191, 'Вязьма', 'Смоленская область', 6703, 67),
(192, 'Вятские Поляны', 'Кировская область', 4303, 43),
(193, 'Гаврилов Посад', 'Ивановская область', 3703, 37),
(194, 'Гаврилов-Ям', 'Ярославская область', 7602, 76),
(195, 'Гагарин', 'Смоленская область', 6704, 67),
(196, 'Гаджиево', 'Мурманская область', 5115, 51),
(197, 'Гай', 'Оренбургская область', 5605, 56),
(198, 'Галич', 'Костромская область', 4404, 44),
(199, 'Гатчина', 'Ленинградская область', 4706, 47),
(200, 'Гвардейск', 'Калининградская область', 3904, 39),
(201, 'Гдов', 'Псковская область', 6003, 60),
(202, 'Геленджик', 'Краснодарский край', 2307, 23),
(203, 'Георгиевск', 'Ставропольский край', 2604, 26),
(204, 'Глазов', 'Удмуртия', 1803, 18),
(205, 'Горбатов', 'Нижегородская область', 5210, 52),
(206, 'Горно-Алтайск', 'Алтай', 401, 4),
(207, 'Горнозаводск', 'Пермский край', 5905, 59),
(208, 'Горняк', 'Алтайский край', 2205, 22),
(209, 'Городец', 'Нижегородская область', 5211, 52),
(210, 'Городище', 'Пензенская область', 5804, 58),
(211, 'Городище', 'Волгоградская область', 3420, 34),
(212, 'Городовиковск', 'Калмыкия', 802, 8),
(213, 'Гороховец', 'Владимирская область', 3304, 33),
(214, 'Горячий Ключ', 'Краснодарский край', 2308, 23),
(215, 'Грайворон', 'Белгородская область', 3104, 31),
(216, 'Гремячинск', 'Пермский край', 5906, 59),
(217, 'Грозный', 'Чечня', 2001, 20),
(218, 'Грязи', 'Липецкая область', 4802, 48),
(219, 'Грязовец', 'Вологодская область', 3506, 35),
(220, 'Губаха', 'Пермский край', 5907, 59),
(221, 'Губкин', 'Белгородская область', 3105, 31),
(222, 'Губкинский', 'Ямало-ненецкий АО', 8902, 89),
(223, 'Гудермес', 'Чечня', 2003, 20),
(224, 'Гуково', 'Ростовская область', 6107, 61),
(225, 'Гулькевичи', 'Краснодарский край', 2309, 23),
(226, 'Гурьевск', 'Кемеровская область', 4205, 42),
(227, 'Гурьевск', 'Калининградская область', 3905, 39),
(228, 'Гусев', 'Калининградская область', 3906, 39),
(229, 'Гусиноозерск', 'Бурятия', 303, 3),
(230, 'Гусь-Хрустальный', 'Владимирская область', 3305, 33),
(231, 'Давлеканово', 'Башкортостан', 208, 2),
(232, 'Далматово', 'Курганская область', 4502, 45),
(233, 'Дальнегорск', 'Приморский край', 2505, 25),
(234, 'Дальнереченск', 'Приморский край', 2506, 25),
(235, 'Данилов', 'Ярославская область', 7603, 76),
(236, 'Данков', 'Липецкая область', 4803, 48),
(237, 'Дегтярск', 'Свердловская область', 6614, 66),
(238, 'Дедовск', 'Московская область', 5009, 50),
(239, 'Демидов', 'Смоленская область', 6705, 67),
(240, 'Дербент', 'Дагестан', 504, 5),
(241, 'Десногорск', 'Смоленская область', 6706, 67),
(242, 'Дзержинск', 'Нижегородская область', 5212, 52),
(243, 'Дзержинский', 'Московская область', 5010, 50),
(244, 'Дивногорск', 'Красноярский край', 2406, 24),
(245, 'Дигора', 'Северная Осетия-Алания', 1505, 15),
(246, 'Димитровград', 'Ульяновская область', 7303, 73),
(247, 'Дмитриев', 'Курская область', 4602, 46),
(248, 'Дмитров', 'Московская область', 5011, 50),
(249, 'Дмитровск', 'Орловская область', 5703, 57),
(250, 'Дно', 'Псковская область', 6004, 60),
(251, 'Добрянка', 'Пермский край', 5908, 59),
(252, 'Долгопрудный', 'Московская область', 5012, 50),
(253, 'Долинск', 'Сахалинская область', 6505, 65),
(254, 'Домодедово', 'Московская область', 5013, 50),
(255, 'Донецк', 'Ростовская область', 6108, 61),
(256, 'Донской', 'Тульская область', 7107, 71),
(257, 'Дорогобуж', 'Смоленская область', 6707, 67),
(258, 'Дрезна', 'Московская область', 5014, 50),
(259, 'Дубна', 'Московская область', 5015, 50),
(260, 'Дубовка', 'Волгоградская область', 3403, 34),
(261, 'Дудинка', 'Красноярский край', 8401, 24),
(262, 'Духовщина', 'Смоленская область', 6708, 67),
(263, 'Дюртюли', 'Башкортостан', 209, 2),
(264, 'Дятьково', 'Брянская область', 3202, 32),
(265, 'Егорьевск', 'Московская область', 5016, 50),
(266, 'Ейск', 'Краснодарский край', 2310, 23),
(267, 'Екатеринбург', 'Свердловская область', 6601, 66),
(268, 'Елабуга', 'Татарстан', 1608, 16),
(269, 'Елец', 'Липецкая область', 4804, 48),
(270, 'Елизово', 'Камчатский край', 4103, 41),
(271, 'Ельня', 'Смоленская область', 6709, 67),
(272, 'Еманжелинск', 'Челябинская область', 7406, 74),
(273, 'Емва', 'Коми', 1104, 11),
(274, 'Енисейск', 'Красноярский край', 2407, 24),
(275, 'Ершов', 'Саратовская область', 6407, 64),
(276, 'Ессентуки', 'Ставропольский край', 2605, 26),
(277, 'Ефремов', 'Тульская область', 7108, 71),
(278, 'Железноводск', 'Ставропольский край', 2606, 26),
(279, 'Железногорск', 'Красноярский край', 2408, 24),
(280, 'Железногорск', 'Курская область', 4603, 46),
(281, 'Железногорск-Илимский', 'Иркутская область', 3821, 38),
(282, 'Железнодорожный', 'Московская область', 5017, 50),
(283, 'Жердевка', 'Тамбовская область', 6802, 68),
(284, 'Жигулевск', 'Самарская область', 6302, 63),
(285, 'Жиздра', 'Калужская область', 4005, 40),
(286, 'Жирновск', 'Волгоградская область', 3404, 34),
(287, 'Жуков', 'Калужская область', 4006, 40),
(288, 'Жуковка', 'Брянская область', 3203, 32),
(289, 'Жуковский', 'Московская область', 5018, 50),
(290, 'Завитинск', 'Амурская область', 2803, 28),
(291, 'Заводоуковск', 'Тюменская область', 7202, 72),
(292, 'Заволжск', 'Ивановская область', 3704, 37),
(293, 'Заволжье', 'Нижегородская область', 5213, 52),
(294, 'Задонск', 'Липецкая область', 4805, 48),
(295, 'Заинск', 'Татарстан', 1620, 16),
(296, 'Закаменск', 'Бурятия', 304, 3),
(297, 'Заозерный', 'Красноярский край', 2409, 24),
(298, 'Заозерск', 'Мурманская область', 5103, 51),
(299, 'Западная Двина', 'Тверская область', 6908, 69),
(300, 'Заполярный', 'Мурманская область', 5104, 51),
(301, 'Зарайск', 'Московская область', 5019, 50),
(302, 'Заречный', 'Свердловская область', 6615, 66),
(303, 'Заречный', 'Пензенская область', 5805, 58),
(304, 'Заринск', 'Алтайский край', 2206, 22),
(305, 'Звенигово', 'Марий Эл', 1203, 12),
(306, 'Звенигород', 'Московская область', 5020, 50),
(307, 'Зверево', 'Ростовская область', 6109, 61),
(308, 'Зеленогорск', 'Красноярский край', 2410, 24),
(309, 'Зеленогорск', 'Санкт-Петербург', 7802, 78),
(310, 'Зеленоград', 'Москва', 7702, 77),
(311, 'Зеленоградск', 'Калининградская область', 3907, 39),
(312, 'Зеленодольск', 'Татарстан', 1609, 16),
(313, 'Зеленокумск', 'Ставропольский край', 2607, 26),
(314, 'Зерноград', 'Ростовская область', 6110, 61),
(315, 'Зея', 'Амурская область', 2804, 28),
(316, 'Зима', 'Иркутская область', 3809, 38),
(317, 'Златоуст', 'Челябинская область', 7407, 74),
(318, 'Злынка', 'Брянская область', 3204, 32),
(319, 'Змеиногорск', 'Алтайский край', 2207, 22),
(320, 'Знаменск', 'Астраханская область', 3003, 30),
(321, 'Зубцов', 'Тверская область', 6909, 69),
(322, 'Зуевка', 'Кировская область', 4304, 43),
(323, 'Ивангород', 'Ленинградская область', 4707, 47),
(324, 'Иваново', 'Ивановская область', 3701, 37),
(325, 'Ивантеевка', 'Московская область', 5021, 50),
(326, 'Ивдель', 'Свердловская область', 6616, 66),
(327, 'Игарка', 'Красноярский край', 2411, 24),
(328, 'Ижевск', 'Удмуртия', 1801, 18),
(329, 'Избербаш', 'Дагестан', 505, 5),
(330, 'Изобильный', 'Ставропольский край', 2608, 26),
(331, 'Иланский', 'Красноярский край', 2412, 24),
(332, 'Инза', 'Ульяновская область', 7304, 73),
(333, 'Инсар', 'Мордовия', 1303, 13),
(334, 'Инта', 'Коми', 1105, 11),
(335, 'Ипатово', 'Ставропольский край', 2609, 26),
(336, 'Ирбит', 'Свердловская область', 6617, 66),
(337, 'Иркутск', 'Иркутская область', 3801, 38),
(338, 'Исилькуль', 'Омская область', 5502, 55),
(339, 'Искитим', 'Новосибирская область', 5405, 54),
(340, 'Истра', 'Московская область', 5022, 50),
(341, 'Ишим', 'Тюменская область', 7203, 72),
(342, 'Ишимбай', 'Башкортостан', 210, 2),
(343, 'Йошкар-Ола', 'Марий Эл', 1201, 12),
(344, 'Кадников', 'Вологодская область', 3507, 35),
(345, 'Казань', 'Татарстан', 1601, 16),
(346, 'Калач', 'Воронежская область', 3606, 36),
(347, 'Калачинск', 'Омская область', 5503, 55),
(348, 'Калач-на-Дону', 'Волгоградская область', 3405, 34),
(349, 'Калининград', 'Калининградская область', 3901, 39),
(350, 'Калининск', 'Саратовская область', 6408, 64),
(351, 'Калтан', 'Кемеровская область', 4206, 42),
(352, 'Калуга', 'Калужская область', 4001, 40),
(353, 'Калязин', 'Тверская область', 6911, 69),
(354, 'Камбарка', 'Удмуртия', 1804, 18),
(355, 'Каменка', 'Пензенская область', 5806, 58),
(356, 'Каменногорск', 'Ленинградская область', 4708, 47),
(357, 'Каменск-Уральский', 'Свердловская область', 6618, 66),
(358, 'Каменск-Шахтинский', 'Ростовская область', 6111, 61),
(359, 'Камень-на-Оби', 'Алтайский край', 2208, 22),
(360, 'Камешково', 'Владимирская область', 3306, 33),
(361, 'Камызяк', 'Астраханская область', 3004, 30),
(362, 'Камышин', 'Волгоградская область', 3406, 34),
(363, 'Камышлов', 'Свердловская область', 6619, 66),
(364, 'Канаш', 'Чувашия', 2103, 21),
(365, 'Кандалакша', 'Мурманская область', 5105, 51),
(366, 'Канск', 'Красноярский край', 2414, 24),
(367, 'Карабаново', 'Владимирская область', 3307, 33),
(368, 'Карабаш', 'Челябинская область', 7408, 74),
(369, 'Карасук', 'Новосибирская область', 5406, 54),
(370, 'Карачаевск', 'Карачаево-Черкесия', 902, 9),
(371, 'Карачев', 'Брянская область', 3205, 32),
(372, 'Каргат', 'Новосибирская область', 5407, 54),
(373, 'Каргополь', 'Архангельская область', 2903, 29),
(374, 'Карпинск', 'Свердловская область', 6620, 66),
(375, 'Карталы', 'Челябинская область', 7409, 74),
(376, 'Касимов', 'Рязанская область', 6202, 62),
(377, 'Касли', 'Челябинская область', 7410, 74),
(378, 'Каспийск', 'Дагестан', 506, 5),
(379, 'Катав-Ивановск', 'Челябинская область', 7411, 74),
(380, 'Катайск', 'Курганская область', 4503, 45),
(381, 'Качканар', 'Свердловская область', 6621, 66),
(382, 'Кашин', 'Тверская область', 6912, 69),
(383, 'Кашира', 'Московская область', 5023, 50),
(384, 'Кедровый', 'Томская область', 7003, 70),
(385, 'Кемерово', 'Кемеровская область', 4201, 42),
(386, 'Кемь', 'Карелия', 1003, 10),
(387, 'Кизел', 'Пермский край', 5909, 59),
(388, 'Кизилюрт', 'Дагестан', 507, 5),
(389, 'Кизляр', 'Дагестан', 508, 5),
(390, 'Кимовск', 'Тульская область', 7109, 71),
(391, 'Кимры', 'Тверская область', 6913, 69),
(392, 'Кингисепп', 'Ленинградская область', 4709, 47),
(393, 'Кинель', 'Самарская область', 6303, 63),
(394, 'Кинешма', 'Ивановская область', 3705, 37),
(395, 'Киреевск', 'Тульская область', 7110, 71),
(396, 'Киренск', 'Иркутская область', 3810, 38),
(397, 'Киржач', 'Владимирская область', 3308, 33),
(398, 'Кириллов', 'Вологодская область', 3508, 35),
(399, 'Кириши', 'Ленинградская область', 4710, 47),
(400, 'Киров', 'Кировская область', 4301, 43),
(401, 'Киров', 'Калужская область', 4007, 40),
(402, 'Кировград', 'Свердловская область', 6622, 66),
(403, 'Кирово-Чепецк', 'Кировская область', 4305, 43),
(404, 'Кировск', 'Ленинградская область', 4711, 47),
(405, 'Кировск', 'Мурманская область', 5106, 51),
(406, 'Кирс', 'Кировская область', 4306, 43),
(407, 'Кирсанов', 'Тамбовская область', 6803, 68),
(408, 'Киселевск', 'Кемеровская область', 4207, 42),
(409, 'Кисловодск', 'Ставропольский край', 2610, 26),
(410, 'Климовск', 'Московская область', 5024, 50),
(411, 'Клин', 'Московская область', 5025, 50),
(412, 'Клинцы', 'Брянская область', 3206, 32),
(413, 'Ключи', 'Камчатский край', 4104, 41),
(414, 'Княгинино', 'Нижегородская область', 5214, 52),
(415, 'Ковдор', 'Мурманская область', 5107, 51),
(416, 'Ковров', 'Владимирская область', 3309, 33),
(417, 'Ковылкино', 'Мордовия', 1304, 13),
(418, 'Когалым', 'Ханты-мансийский АО', 8603, 86),
(419, 'Кодинск', 'Красноярский край', 2415, 24),
(420, 'Козельск', 'Калужская область', 4008, 40),
(421, 'Козловка', 'Чувашия', 2104, 21),
(422, 'Козьмодемьянск', 'Марий Эл', 1204, 12),
(423, 'Кола', 'Мурманская область', 5108, 51),
(424, 'Кологрив', 'Костромская область', 4405, 44),
(425, 'Коломна', 'Московская область', 5026, 50),
(426, 'Колпашево', 'Томская область', 7004, 70),
(427, 'Колпино', 'Санкт-Петербург', 7803, 78),
(428, 'Кольчугино', 'Владимирская область', 3310, 33),
(429, 'Коммунар', 'Ленинградская область', 4712, 47),
(430, 'Комсомольск', 'Ивановская область', 3706, 37),
(431, 'Комсомольск-на-Амуре', 'Хабаровский край', 2705, 27),
(432, 'Конаково', 'Тверская область', 6914, 69),
(433, 'Кондопога', 'Карелия', 1004, 10),
(434, 'Кондрово', 'Калужская область', 4009, 40),
(435, 'Константиновск', 'Ростовская область', 6112, 61),
(436, 'Копейск', 'Челябинская область', 7412, 74),
(437, 'Кораблино', 'Рязанская область', 6203, 62),
(438, 'Кореновск', 'Краснодарский край', 2311, 23),
(439, 'Коркино', 'Челябинская область', 7413, 74),
(440, 'Королев', 'Московская область', 5027, 50),
(441, 'Короча', 'Белгородская область', 3106, 31),
(442, 'Корсаков', 'Сахалинская область', 6506, 65),
(443, 'Коряжма', 'Архангельская область', 2904, 29),
(444, 'Костерево', 'Владимирская область', 3311, 33),
(445, 'Костомукша', 'Карелия', 1005, 10),
(446, 'Кострома', 'Костромская область', 4401, 44),
(447, 'Котельники', 'Московская область', 5078, 50),
(448, 'Котельниково', 'Волгоградская область', 3407, 34),
(449, 'Котельнич', 'Кировская область', 4307, 43),
(450, 'Котлас', 'Архангельская область', 2905, 29),
(451, 'Котово', 'Волгоградская область', 3408, 34),
(452, 'Котовск', 'Тамбовская область', 6804, 68),
(453, 'Кохма', 'Ивановская область', 3707, 37),
(454, 'Красавино', 'Вологодская область', 3515, 35),
(455, 'Красноармейск', 'Московская область', 5028, 50),
(456, 'Красноармейск', 'Саратовская область', 6409, 64),
(457, 'Красновишерск', 'Пермский край', 5910, 59),
(458, 'Красногорск', 'Московская область', 5029, 50),
(459, 'Краснодар', 'Краснодарский край', 2301, 23),
(460, 'Красное Село', 'Санкт-Петербург', 7804, 78),
(461, 'Краснозаводск', 'Московская область', 5030, 50),
(462, 'Краснознаменск', 'Московская область', 5031, 50),
(463, 'Краснознаменск', 'Калининградская область', 3908, 39),
(464, 'Краснокаменск', 'Забайкальский край', 7504, 75),
(465, 'Краснокамск', 'Пермский край', 5911, 59),
(466, 'Краснослободск', 'Мордовия', 1305, 13),
(467, 'Краснослободск', 'Волгоградская область', 3409, 34),
(468, 'Краснотурьинск', 'Свердловская область', 6623, 66),
(469, 'Красноуральск', 'Свердловская область', 6624, 66),
(470, 'Красноуфимск', 'Свердловская область', 6625, 66),
(471, 'Красноярск', 'Красноярский край', 2401, 24),
(472, 'Красный Кут', 'Саратовская область', 6410, 64),
(473, 'Красный Сулин', 'Ростовская область', 6113, 61),
(474, 'Красный Холм', 'Тверская область', 6915, 69),
(475, 'Кременки', 'Калужская область', 4010, 40),
(476, 'Кронштадт', 'Санкт-Петербург', 7805, 78),
(477, 'Кропоткин', 'Краснодарский край', 2312, 23),
(478, 'Крымск', 'Краснодарский край', 2313, 23),
(479, 'Кстово', 'Нижегородская область', 5215, 52),
(480, 'Кубинка', 'Московская область', 5079, 50),
(481, 'Кувандык', 'Оренбургская область', 5606, 56),
(482, 'Кувшиново', 'Тверская область', 6916, 69),
(483, 'Кудымкар', 'Пермский край', 8101, 59),
(484, 'Кузнецк', 'Пензенская область', 5807, 58),
(485, 'Куйбышев', 'Новосибирская область', 5408, 54),
(486, 'Кулебаки', 'Нижегородская область', 5216, 52),
(487, 'Кумертау', 'Башкортостан', 211, 2),
(488, 'Кунгур', 'Пермский край', 5912, 59),
(489, 'Купино', 'Новосибирская область', 5409, 54),
(490, 'Курган', 'Курганская область', 4501, 45),
(491, 'Курганинск', 'Краснодарский край', 2314, 23),
(492, 'Курильск', 'Сахалинская область', 6508, 65),
(493, 'Курлово', 'Владимирская область', 3312, 33),
(494, 'Куровское', 'Московская область', 5032, 50),
(495, 'Курск', 'Курская область', 4601, 46),
(496, 'Куртамыш', 'Курганская область', 4504, 45),
(497, 'Курчатов', 'Курская область', 4604, 46),
(498, 'Куса', 'Челябинская область', 7414, 74),
(499, 'Кушва', 'Свердловская область', 6626, 66),
(500, 'Кызыл', 'Тыва', 1701, 17),
(501, 'Кыштым', 'Челябинская область', 7415, 74),
(502, 'Кяхта', 'Бурятия', 305, 3),
(503, 'Лабинск', 'Краснодарский край', 2315, 23),
(504, 'Лабытнанги', 'Ямало-ненецкий АО', 8903, 89),
(505, 'Лагань', 'Калмыкия', 803, 8),
(506, 'Ладушкин', 'Калининградская область', 3909, 39),
(507, 'Лаишево', 'Татарстан', 1621, 16),
(508, 'Лакинск', 'Владимирская область', 3313, 33),
(509, 'Лангепас', 'Ханты-мансийский АО', 8604, 86),
(510, 'Лахденпохья', 'Карелия', 1006, 10),
(511, 'Лебедянь', 'Липецкая область', 4806, 48),
(512, 'Лениногорск', 'Татарстан', 1610, 16),
(513, 'Ленинск', 'Волгоградская область', 3410, 34),
(514, 'Ленинск-Кузнецкий', 'Кемеровская область', 4208, 42),
(515, 'Ленск', 'Якутия', 1405, 14),
(516, 'Лермонтов', 'Ставропольский край', 2611, 26),
(517, 'Лесной', 'Свердловская область', 6627, 66),
(518, 'Лесозаводск', 'Приморский край', 2507, 25),
(519, 'Лесосибирск', 'Красноярский край', 2416, 24),
(520, 'Ливны', 'Орловская область', 5704, 57),
(521, 'Ликино-Дулево', 'Московская область', 5033, 50),
(522, 'Липецк', 'Липецкая область', 4801, 48),
(523, 'Липки', 'Тульская область', 7111, 71),
(524, 'Лиски', 'Воронежская область', 3607, 36),
(525, 'Лихославль', 'Тверская область', 6917, 69),
(526, 'Лобня', 'Московская область', 5034, 50),
(527, 'Лодейное Поле', 'Ленинградская область', 4713, 47),
(528, 'Ломоносов', 'Санкт-Петербург', 7806, 78),
(529, 'Лосино-Петровский', 'Московская область', 5035, 50),
(530, 'Луга', 'Ленинградская область', 4714, 47),
(531, 'Луза', 'Кировская область', 4308, 43),
(532, 'Лукоянов', 'Нижегородская область', 5217, 52),
(533, 'Луховицы', 'Московская область', 5036, 50),
(534, 'Лысково', 'Нижегородская область', 5218, 52),
(535, 'Лысьва', 'Пермский край', 5913, 59),
(536, 'Лыткарино', 'Московская область', 5037, 50),
(537, 'Льгов', 'Курская область', 4605, 46),
(538, 'Люберцы', 'Московская область', 5038, 50),
(539, 'Любим', 'Ярославская область', 7604, 76),
(540, 'Людиново', 'Калужская область', 4011, 40),
(541, 'Лянтор', 'Ханты-мансийский АО', 8605, 86),
(542, 'Магадан', 'Магаданская область', 4901, 49),
(543, 'Магнитогорск', 'Челябинская область', 7416, 74),
(544, 'Майкоп', 'Адыгея', 101, 1),
(545, 'Майский', 'Кабардино-Балкария', 703, 7),
(546, 'Макаров', 'Сахалинская область', 6509, 65),
(547, 'Макарьев', 'Костромская область', 4406, 44),
(548, 'Макушино', 'Курганская область', 4505, 45),
(549, 'Малая Вишера', 'Новгородская область', 5304, 53),
(550, 'Малгобек', 'Ингушетия', 604, 6),
(551, 'Малмыж', 'Кировская область', 4309, 43),
(552, 'Малоархангельск', 'Орловская область', 5705, 57),
(553, 'Малоярославец', 'Калужская область', 4012, 40),
(554, 'Мамадыш', 'Татарстан', 1611, 16),
(555, 'Мамоново', 'Калининградская область', 3910, 39),
(556, 'Мантурово', 'Костромская область', 4407, 44),
(557, 'Мариинск', 'Кемеровская область', 4209, 42),
(558, 'Мариинский Посад', 'Чувашия', 2105, 21),
(559, 'Маркс', 'Саратовская область', 6411, 64),
(560, 'Махачкала', 'Дагестан', 501, 5),
(561, 'Мглин', 'Брянская область', 3207, 32),
(562, 'Мегион', 'Ханты-мансийский АО', 8606, 86),
(563, 'Медвежьегорск', 'Карелия', 1007, 10),
(564, 'Медногорск', 'Оренбургская область', 5607, 56),
(565, 'Медынь', 'Калужская область', 4013, 40),
(566, 'Межгорье', 'Башкортостан', 212, 2),
(567, 'Междуреченск', 'Кемеровская область', 4210, 42),
(568, 'Мезень', 'Архангельская область', 2906, 29),
(569, 'Меленки', 'Владимирская область', 3314, 33),
(570, 'Мелеуз', 'Башкортостан', 213, 2),
(571, 'Менделеевск', 'Татарстан', 1612, 16),
(572, 'Мензелинск', 'Татарстан', 1613, 16),
(573, 'Мещовск', 'Калужская область', 4014, 40),
(574, 'Миасс', 'Челябинская область', 7417, 74),
(575, 'Микунь', 'Коми', 1106, 11),
(576, 'Миллерово', 'Ростовская область', 6114, 61),
(577, 'Минеральные Воды', 'Ставропольский край', 2612, 26),
(578, 'Минусинск', 'Красноярский край', 2417, 24),
(579, 'Миньяр', 'Челябинская область', 7418, 74),
(580, 'Мирный', 'Якутия', 1406, 14),
(581, 'Мирный', 'Архангельская область', 2907, 29),
(582, 'Михайлов', 'Рязанская область', 6204, 62),
(583, 'Михайловка', 'Волгоградская область', 3411, 34),
(584, 'Михайловск', 'Ставропольский край', 2613, 26),
(585, 'Михайловск', 'Свердловская область', 6628, 66),
(586, 'Мичуринск', 'Тамбовская область', 6805, 68),
(587, 'Могоча', 'Забайкальский край', 7505, 75),
(588, 'Можайск', 'Московская область', 5039, 50),
(589, 'Можга', 'Удмуртия', 1805, 18),
(590, 'Моздок', 'Северная Осетия-Алания', 1506, 15),
(591, 'Мончегорск', 'Мурманская область', 5109, 51),
(592, 'Морозовск', 'Ростовская область', 6115, 61),
(593, 'Моршанск', 'Тамбовская область', 6806, 68),
(594, 'Мосальск', 'Калужская область', 4015, 40),
(595, 'Москва', 'Москва', 7701, 77),
(596, 'Муравленко', 'Ямало-ненецкий АО', 8904, 89),
(597, 'Мураши', 'Кировская область', 4310, 43),
(598, 'Мурманск', 'Мурманская область', 5101, 51),
(599, 'Муром', 'Владимирская область', 3315, 33),
(600, 'Мценск', 'Орловская область', 5706, 57),
(601, 'Мыски', 'Кемеровская область', 4211, 42),
(602, 'Мытищи', 'Московская область', 5040, 50),
(603, 'Мышкин', 'Ярославская область', 7605, 76),
(604, 'Набережные Челны', 'Татарстан', 1618, 16),
(605, 'Навашино', 'Нижегородская область', 5219, 52),
(606, 'Наволоки', 'Ивановская область', 3708, 37),
(607, 'Надым', 'Ямало-ненецкий АО', 8905, 89),
(608, 'Назарово', 'Красноярский край', 2418, 24),
(609, 'Назрань', 'Ингушетия', 601, 6),
(610, 'Называевск', 'Омская область', 5504, 55),
(611, 'Нальчик', 'Кабардино-Балкария', 701, 7),
(612, 'Нариманов', 'Астраханская область', 3005, 30),
(613, 'Наро-Фоминск', 'Московская область', 5041, 50),
(614, 'Нарткала', 'Кабардино-Балкария', 704, 7),
(615, 'Нарьян-Мар', 'Ненецкий АО', 8301, 83),
(616, 'Находка', 'Приморский край', 2508, 25),
(617, 'Невель', 'Псковская область', 6005, 60),
(618, 'Невельск', 'Сахалинская область', 6510, 65),
(619, 'Невинномысск', 'Ставропольский край', 2614, 26),
(620, 'Невьянск', 'Свердловская область', 6629, 66),
(621, 'Нелидово', 'Тверская область', 6918, 69),
(622, 'Неман', 'Калининградская область', 3911, 39),
(623, 'Нерехта', 'Костромская область', 4408, 44),
(624, 'Нерчинск', 'Забайкальский край', 7506, 75),
(625, 'Нерюнгри', 'Якутия', 1407, 14),
(626, 'Нестеров', 'Калининградская область', 3912, 39),
(627, 'Нефтегорск', 'Самарская область', 6304, 63),
(628, 'Нефтекамск', 'Башкортостан', 214, 2),
(629, 'Нефтекумск', 'Ставропольский край', 2615, 26),
(630, 'Нефтеюганск', 'Ханты-мансийский АО', 8607, 86),
(631, 'Нея', 'Костромская область', 4409, 44),
(632, 'Нижневартовск', 'Ханты-мансийский АО', 8608, 86),
(633, 'Нижнекамск', 'Татарстан', 1614, 16),
(634, 'Нижнеудинск', 'Иркутская область', 3822, 38),
(635, 'Нижние Серги', 'Свердловская область', 6630, 66),
(636, 'Нижний Ломов', 'Пензенская область', 5808, 58),
(637, 'Нижний Новгород', 'Нижегородская область', 5201, 52),
(638, 'Нижний Тагил', 'Свердловская область', 6631, 66),
(639, 'Нижняя Салда', 'Свердловская область', 6632, 66),
(640, 'Нижняя Тура', 'Свердловская область', 6633, 66),
(641, 'Николаевск', 'Волгоградская область', 3412, 34),
(642, 'Николаевск-на-Амуре', 'Хабаровский край', 2706, 27),
(643, 'Никольск', 'Вологодская область', 3509, 35),
(644, 'Никольск', 'Пензенская область', 5809, 58),
(645, 'Никольское', 'Ленинградская область', 4715, 47),
(646, 'Новая Ладога', 'Ленинградская область', 4716, 47),
(647, 'Новая Ляля', 'Свердловская область', 6634, 66),
(648, 'Новоалександровск', 'Ставропольский край', 2616, 26),
(649, 'Новоалтайск', 'Алтайский край', 2209, 22),
(650, 'Новоаннинский', 'Волгоградская область', 3413, 34),
(651, 'Нововоронеж', 'Воронежская область', 3608, 36),
(652, 'Новодвинск', 'Архангельская область', 2908, 29),
(653, 'Новозыбков', 'Брянская область', 3208, 32),
(654, 'Новокубанск', 'Краснодарский край', 2316, 23),
(655, 'Новокузнецк', 'Кемеровская область', 4212, 42),
(656, 'Новокуйбышевск', 'Самарская область', 6305, 63),
(657, 'Новомичуринск', 'Рязанская область', 6205, 62),
(658, 'Новомосковск', 'Тульская область', 7112, 71),
(659, 'Новопавловск', 'Ставропольский край', 2617, 26),
(660, 'Новоржев', 'Псковская область', 6006, 60),
(661, 'Новороссийск', 'Краснодарский край', 2317, 23),
(662, 'Новосибирск', 'Новосибирская область', 5401, 54),
(663, 'Новосиль', 'Орловская область', 5707, 57),
(664, 'Новосокольники', 'Псковская область', 6007, 60),
(665, 'Новотроицк', 'Оренбургская область', 5608, 56),
(666, 'Новоузенск', 'Саратовская область', 6412, 64),
(667, 'Новоульяновск', 'Ульяновская область', 7305, 73),
(668, 'Новоуральск', 'Свердловская область', 6635, 66),
(669, 'Новохоперск', 'Воронежская область', 3609, 36),
(670, 'Новочебоксарск', 'Чувашия', 2106, 21),
(671, 'Новочеркасск', 'Ростовская область', 6116, 61),
(672, 'Новошахтинск', 'Ростовская область', 6117, 61),
(673, 'Новый Оскол', 'Белгородская область', 3107, 31),
(674, 'Новый Уренгой', 'Ямало-ненецкий АО', 8906, 89),
(675, 'Ногинск', 'Московская область', 5042, 50),
(676, 'Нолинск', 'Кировская область', 4311, 43),
(677, 'Норильск', 'Красноярский край', 2419, 24),
(678, 'Ноябрьск', 'Ямало-ненецкий АО', 8907, 89),
(679, 'Нурлат', 'Татарстан', 1615, 16),
(680, 'Нытва', 'Пермский край', 5914, 59),
(681, 'Нюрба', 'Якутия', 1408, 14),
(682, 'Нягань', 'Ханты-мансийский АО', 8609, 86),
(683, 'Нязепетровск', 'Челябинская область', 7419, 74),
(684, 'Няндома', 'Архангельская область', 2909, 29),
(685, 'Облучье', 'Еврейская АО', 7902, 79),
(686, 'Обнинск', 'Калужская область', 4016, 40),
(687, 'Обоянь', 'Курская область', 4606, 46),
(688, 'Обь', 'Новосибирская область', 5410, 54),
(689, 'Одинцово', 'Московская область', 5043, 50),
(690, 'Ожерелье', 'Московская область', 5044, 50);";

echo_data_add($sql,$tbl);
//* *****************************************************************

///**********************************************************************************************

$tbl='i_city';
$sql="
INSERT INTO `city` (`id`, `name`, `region`, `nomer`, `nom_region`) VALUES
(691, 'Озерск', 'Челябинская область', 7420, 74),
(692, 'Озерск', 'Калининградская область', 3913, 39),
(693, 'Озеры', 'Московская область', 5045, 50),
(694, 'Октябрьск', 'Самарская область', 6306, 63),
(695, 'Октябрьский', 'Башкортостан', 215, 2),
(696, 'Октябрьский', 'Пермский край', 5915, 59),
(697, 'Окуловка', 'Новгородская область', 5305, 53),
(698, 'Олекминск', 'Якутия', 1409, 14),
(699, 'Оленегорск', 'Мурманская область', 5110, 51),
(700, 'Олонец', 'Карелия', 1008, 10),
(701, 'Омск', 'Омская область', 5501, 55),
(702, 'Омутнинск', 'Кировская область', 4312, 43),
(703, 'Онега', 'Архангельская область', 2910, 29),
(704, 'Опочка', 'Псковская область', 6008, 60),
(705, 'Орел', 'Орловская область', 5701, 57),
(706, 'Оренбург', 'Оренбургская область', 5601, 56),
(707, 'Орехово-Зуево', 'Московская область', 5046, 50),
(708, 'Орлов', 'Кировская область', 4313, 43),
(709, 'Орск', 'Оренбургская область', 5609, 56),
(710, 'Оса', 'Пермский край', 5916, 59),
(711, 'Осинники', 'Кемеровская область', 4213, 42),
(712, 'Осташков', 'Тверская область', 6919, 69),
(713, 'Остров', 'Псковская область', 6009, 60),
(714, 'Островной', 'Мурманская область', 5111, 51),
(715, 'Острогожск', 'Воронежская область', 3610, 36),
(716, 'Отрадное', 'Ленинградская область', 4717, 47),
(717, 'Отрадный', 'Самарская область', 6307, 63),
(718, 'Оха', 'Сахалинская область', 6511, 65),
(719, 'Оханск', 'Пермский край', 5917, 59),
(720, 'Очер', 'Пермский край', 5918, 59),
(721, 'Павлово', 'Нижегородская область', 5220, 52),
(722, 'Павловск', 'Санкт-Петербург', 7807, 78),
(723, 'Павловск', 'Воронежская область', 3611, 36),
(724, 'Павловский Посад', 'Московская область', 5047, 50),
(725, 'Палласовка', 'Волгоградская область', 3414, 34),
(726, 'Партизанск', 'Приморский край', 2509, 25),
(727, 'Певек', 'Чукотский АО', 8703, 87),
(728, 'Пенза', 'Пензенская область', 5801, 58),
(729, 'Первомайск', 'Нижегородская область', 5221, 52),
(730, 'Первоуральск', 'Свердловская область', 6636, 66),
(731, 'Перевоз', 'Нижегородская область', 5228, 52),
(732, 'Пересвет', 'Московская область', 5077, 50),
(733, 'Переславль-Залесский', 'Ярославская область', 7606, 76),
(734, 'Пермь', 'Пермский край', 5901, 59),
(735, 'Пестово', 'Новгородская область', 5306, 53),
(736, 'Петров Вал', 'Волгоградская область', 3415, 34),
(737, 'Петровск', 'Саратовская область', 6413, 64),
(738, 'Петровск-Забайкальский', 'Забайкальский край', 7507, 75),
(739, 'Петрозаводск', 'Карелия', 1001, 10),
(740, 'Петропавловск-Камчатский', 'Камчатский край', 4101, 41),
(741, 'Петухово', 'Курганская область', 4506, 45),
(742, 'Петушки', 'Владимирская область', 3316, 33),
(743, 'Печора', 'Коми', 1107, 11),
(744, 'Печоры', 'Псковская область', 6010, 60),
(745, 'Пикалево', 'Ленинградская область', 4718, 47),
(746, 'Пионерский', 'Калининградская область', 3914, 39),
(747, 'Питкяранта', 'Карелия', 1009, 10),
(748, 'Плавск', 'Тульская область', 7113, 71),
(749, 'Пласт', 'Челябинская область', 7421, 74),
(750, 'Плес', 'Ивановская область', 3709, 37),
(751, 'Поворино', 'Воронежская область', 3612, 36),
(752, 'Подольск', 'Московская область', 5048, 50),
(753, 'Подпорожье', 'Ленинградская область', 4719, 47),
(754, 'Покачи', 'Ханты-мансийский АО', 8610, 86),
(755, 'Покров', 'Владимирская область', 3317, 33),
(756, 'Покровск', 'Якутия', 1410, 14),
(757, 'Полевской', 'Свердловская область', 6637, 66),
(758, 'Полесск', 'Калининградская область', 3915, 39),
(759, 'Полысаево', 'Кемеровская область', 4214, 42),
(760, 'Полярные Зори', 'Мурманская область', 5112, 51),
(761, 'Полярный', 'Мурманская область', 5113, 51),
(762, 'Поронайск', 'Сахалинская область', 6512, 65),
(763, 'Порхов', 'Псковская область', 6011, 60),
(764, 'Похвистнево', 'Самарская область', 6308, 63),
(765, 'Почеп', 'Брянская область', 3209, 32),
(766, 'Починок', 'Смоленская область', 6710, 67),
(767, 'Пошехонье', 'Ярославская область', 7607, 76),
(768, 'Правдинск', 'Калининградская область', 3916, 39),
(769, 'Приволжск', 'Ивановская область', 3710, 37),
(770, 'Приморск', 'Ленинградская область', 4720, 47),
(771, 'Приморско-Ахтарск', 'Краснодарский край', 2318, 23),
(772, 'Приозерск', 'Ленинградская область', 4721, 47),
(773, 'Прокопьевск', 'Кемеровская область', 4215, 42),
(774, 'Пролетарск', 'Ростовская область', 6118, 61),
(775, 'Протвино', 'Московская область', 5049, 50),
(776, 'Прохладный', 'Кабардино-Балкария', 705, 7),
(777, 'Псков', 'Псковская область', 6001, 60),
(778, 'Пугачев', 'Саратовская область', 6414, 64),
(779, 'Пудож', 'Карелия', 1010, 10),
(780, 'Пустошка', 'Псковская область', 6012, 60),
(781, 'Пучеж', 'Ивановская область', 3711, 37),
(782, 'Пушкин', 'Санкт-Петербург', 7809, 78),
(783, 'Пушкино', 'Московская область', 5050, 50),
(784, 'Пущино', 'Московская область', 5051, 50),
(785, 'Пыталово', 'Псковская область', 6013, 60),
(786, 'Пыть-Ях', 'Ханты-мансийский АО', 8611, 86),
(787, 'Пятигорск', 'Ставропольский край', 2618, 26),
(788, 'Радужный', 'Ханты-мансийский АО', 8612, 86),
(789, 'Радужный', 'Владимирская область', 3318, 33),
(790, 'Райчихинск', 'Амурская область', 2805, 28),
(791, 'Раменское', 'Московская область', 5052, 50),
(792, 'Рассказово', 'Тамбовская область', 6807, 68),
(793, 'Ревда', 'Свердловская область', 6638, 66),
(794, 'Реж', 'Свердловская область', 6639, 66),
(795, 'Реутов', 'Московская область', 5053, 50),
(796, 'Ржев', 'Тверская область', 6920, 69),
(797, 'Родники', 'Ивановская область', 3712, 37),
(798, 'Рославль', 'Смоленская область', 6711, 67),
(799, 'Россошь', 'Воронежская область', 3613, 36),
(800, 'Ростов', 'Ярославская область', 7608, 76),
(801, 'Ростов-на-Дону', 'Ростовская область', 6101, 61),
(802, 'Рошаль', 'Московская область', 5054, 50),
(803, 'Ртищево', 'Саратовская область', 6415, 64),
(804, 'Рубцовск', 'Алтайский край', 2210, 22),
(805, 'Рудня', 'Смоленская область', 6712, 67),
(806, 'Руза', 'Московская область', 5055, 50),
(807, 'Рузаевка', 'Мордовия', 1306, 13),
(808, 'Рыбинск', 'Ярославская область', 7609, 76),
(809, 'Рыбное', 'Рязанская область', 6206, 62),
(810, 'Рыльск', 'Курская область', 4607, 46),
(811, 'Ряжск', 'Рязанская область', 6207, 62),
(812, 'Рязань', 'Рязанская область', 6201, 62),
(813, 'Салават', 'Башкортостан', 216, 2),
(814, 'Салаир', 'Кемеровская область', 4216, 42),
(815, 'Салехард', 'Ямало-ненецкий АО', 8901, 89),
(816, 'Сальск', 'Ростовская область', 6119, 61),
(817, 'Самара', 'Самарская область', 6301, 63),
(818, 'Санкт-Петербург', 'Санкт-Петербург', 7801, 78),
(819, 'Саранск', 'Мордовия', 1301, 13),
(820, 'Сарапул', 'Удмуртия', 1806, 18),
(821, 'Саратов', 'Саратовская область', 6401, 64),
(822, 'Саров', 'Нижегородская область', 5222, 52),
(823, 'Сасово', 'Рязанская область', 6208, 62),
(824, 'Сатка', 'Челябинская область', 7422, 74),
(825, 'Сафоново', 'Смоленская область', 6713, 67),
(826, 'Саяногорск', 'Хакасия', 1903, 19),
(827, 'Саянск', 'Иркутская область', 3811, 38),
(828, 'Светлогорск', 'Калининградская область', 3918, 39),
(829, 'Светлоград', 'Ставропольский край', 2619, 26),
(830, 'Светлый', 'Калининградская область', 3919, 39),
(831, 'Светогорск', 'Ленинградская область', 4722, 47),
(832, 'Свирск', 'Иркутская область', 3812, 38),
(833, 'Свободный', 'Амурская область', 2806, 28),
(834, 'Себеж', 'Псковская область', 6014, 60),
(835, 'Северобайкальск', 'Бурятия', 306, 3),
(836, 'Северодвинск', 'Архангельская область', 2911, 29),
(837, 'Северо-Курильск', 'Сахалинская область', 6513, 65),
(838, 'Североморск', 'Мурманская область', 5114, 51),
(839, 'Североуральск', 'Свердловская область', 6640, 66),
(840, 'Северск', 'Томская область', 7005, 70),
(841, 'Севск', 'Брянская область', 3210, 32),
(842, 'Сегежа', 'Карелия', 1011, 10),
(843, 'Сельцо', 'Брянская область', 3211, 32),
(844, 'Семенов', 'Нижегородская область', 5223, 52),
(845, 'Семикаракорск', 'Ростовская область', 6120, 61),
(846, 'Семилуки', 'Воронежская область', 3614, 36),
(847, 'Сенгилей', 'Ульяновская область', 7306, 73),
(848, 'Серафимович', 'Волгоградская область', 3416, 34),
(849, 'Сергач', 'Нижегородская область', 5224, 52),
(850, 'Сергиев Посад', 'Московская область', 5056, 50),
(851, 'Сердобск', 'Пензенская область', 5810, 58),
(852, 'Серов', 'Свердловская область', 6641, 66),
(853, 'Серпухов', 'Московская область', 5057, 50),
(854, 'Сертолово', 'Ленинградская область', 4729, 47),
(855, 'Сестрорецк', 'Санкт-Петербург', 7810, 78),
(856, 'Сибай', 'Башкортостан', 217, 2),
(857, 'Сим', 'Челябинская область', 7423, 74),
(858, 'Сковородино', 'Амурская область', 2807, 28),
(859, 'Скопин', 'Рязанская область', 6209, 62),
(860, 'Славгород', 'Алтайский край', 2211, 22),
(861, 'Славск', 'Калининградская область', 3920, 39),
(862, 'Славянск-на-Кубани', 'Краснодарский край', 2319, 23),
(863, 'Сланцы', 'Ленинградская область', 4723, 47),
(864, 'Слободской', 'Кировская область', 4314, 43),
(865, 'Слюдянка', 'Иркутская область', 3813, 38),
(866, 'Смоленск', 'Смоленская область', 6701, 67),
(867, 'Снежинск', 'Челябинская область', 7424, 74),
(868, 'Снежногорск', 'Мурманская область', 5116, 51),
(869, 'Собинка', 'Владимирская область', 3319, 33),
(870, 'Советск', 'Калининградская область', 3921, 39),
(871, 'Советск', 'Тульская область', 7114, 71),
(872, 'Советск', 'Кировская область', 4315, 43),
(873, 'Советская Гавань', 'Хабаровский край', 2708, 27),
(874, 'Советский', 'Ханты-мансийский АО', 8613, 86),
(875, 'Сокол', 'Вологодская область', 3510, 35),
(876, 'Сокольники', 'Тульская область', 7115, 71),
(877, 'Солигалич', 'Костромская область', 4410, 44),
(878, 'Соликамск', 'Пермский край', 5920, 59),
(879, 'Солнечногорск', 'Московская область', 5058, 50),
(880, 'Сольвычегодск', 'Архангельская область', 2912, 29),
(881, 'Соль-Илецк', 'Оренбургская область', 5610, 56),
(882, 'Сольцы', 'Новгородская область', 5307, 53),
(883, 'Сорочинск', 'Оренбургская область', 5611, 56),
(884, 'Сорск', 'Хакасия', 1904, 19),
(885, 'Сортавала', 'Карелия', 1012, 10),
(886, 'Сосенский', 'Калужская область', 4017, 40),
(887, 'Сосновка', 'Кировская область', 4316, 43),
(888, 'Сосновоборск', 'Красноярский край', 2420, 24),
(889, 'Сосновый Бор', 'Ленинградская область', 4724, 47),
(890, 'Сосногорск', 'Коми', 1108, 11),
(891, 'Сочи', 'Краснодарский край', 2320, 23),
(892, 'Спас-Деменск', 'Калужская область', 4018, 40),
(893, 'Спас-Клепики', 'Рязанская область', 6210, 62),
(894, 'Спасск', 'Пензенская область', 5812, 58),
(895, 'Спасск-Дальний', 'Приморский край', 2510, 25),
(896, 'Спасск-Рязанский', 'Рязанская область', 6211, 62),
(897, 'Среднеколымск', 'Якутия', 1411, 14),
(898, 'Среднеуральск', 'Свердловская область', 6642, 66),
(899, 'Сретенск', 'Забайкальский край', 7508, 75),
(900, 'Ставрополь', 'Ставропольский край', 2601, 26),
(901, 'Старая Русса', 'Новгородская область', 5308, 53),
(902, 'Старица', 'Тверская область', 6921, 69),
(903, 'Стародуб', 'Брянская область', 3212, 32),
(904, 'Старый Оскол', 'Белгородская область', 3108, 31),
(905, 'Стерлитамак', 'Башкортостан', 218, 2),
(906, 'Стрежевой', 'Томская область', 7006, 70),
(907, 'Строитель', 'Белгородская область', 3109, 31),
(908, 'Струнино', 'Владимирская область', 3320, 33),
(909, 'Ступино', 'Московская область', 5059, 50),
(910, 'Суворов', 'Тульская область', 7116, 71),
(911, 'Суджа', 'Курская область', 4608, 46),
(912, 'Судогда', 'Владимирская область', 3321, 33),
(913, 'Суздаль', 'Владимирская область', 3322, 33),
(914, 'Суоярви', 'Карелия', 1013, 10),
(915, 'Сураж', 'Брянская область', 3213, 32),
(916, 'Сургут', 'Ханты-мансийский АО', 8614, 86),
(917, 'Суровикино', 'Волгоградская область', 3417, 34),
(918, 'Сурск', 'Пензенская область', 5811, 58),
(919, 'Сусуман', 'Магаданская область', 4902, 49),
(920, 'Сухиничи', 'Калужская область', 4019, 40),
(921, 'Сухой Лог', 'Свердловская область', 6643, 66),
(922, 'Сходня', 'Московская область', 5060, 50),
(923, 'Сызрань', 'Самарская область', 6309, 63),
(924, 'Сыктывкар', 'Коми', 1101, 11),
(925, 'Сысерть', 'Свердловская область', 6644, 66),
(926, 'Сычевка', 'Смоленская область', 6714, 67),
(927, 'Сясьстрой', 'Ленинградская область', 4725, 47),
(928, 'Тавда', 'Свердловская область', 6645, 66),
(929, 'Таганрог', 'Ростовская область', 6121, 61),
(930, 'Тайга', 'Кемеровская область', 4217, 42),
(931, 'Тайшет', 'Иркутская область', 3814, 38),
(932, 'Талдом', 'Московская область', 5061, 50),
(933, 'Талица', 'Свердловская область', 6646, 66),
(934, 'Тамбов', 'Тамбовская область', 6801, 68),
(935, 'Тара', 'Омская область', 5505, 55),
(936, 'Тарко-Сале', 'Ямало-ненецкий АО', 8908, 89),
(937, 'Таруса', 'Калужская область', 4020, 40),
(938, 'Татарск', 'Новосибирская область', 5411, 54),
(939, 'Таштагол', 'Кемеровская область', 4218, 42),
(940, 'Тверь', 'Тверская область', 6901, 69),
(941, 'Теберда', 'Карачаево-Черкесия', 903, 9),
(942, 'Тейково', 'Ивановская область', 3713, 37),
(943, 'Темников', 'Мордовия', 1307, 13),
(944, 'Темрюк', 'Краснодарский край', 2321, 23),
(945, 'Терек', 'Кабардино-Балкария', 706, 7),
(946, 'Тетюши', 'Татарстан', 1617, 16),
(947, 'Тимашевск', 'Краснодарский край', 2322, 23),
(948, 'Тихвин', 'Ленинградская область', 4726, 47),
(949, 'Тихорецк', 'Краснодарский край', 2323, 23),
(950, 'Тобольск', 'Тюменская область', 7204, 72),
(951, 'Тогучин', 'Новосибирская область', 5412, 54),
(952, 'Тольятти', 'Самарская область', 6310, 63),
(953, 'Томари', 'Сахалинская область', 6514, 65),
(954, 'Томмот', 'Якутия', 1412, 14),
(955, 'Томск', 'Томская область', 7001, 70),
(956, 'Топки', 'Кемеровская область', 4219, 42),
(957, 'Торжок', 'Тверская область', 6922, 69),
(958, 'Торопец', 'Тверская область', 6923, 69),
(959, 'Тосно', 'Ленинградская область', 4727, 47),
(960, 'Тотьма', 'Вологодская область', 3511, 35),
(961, 'Трехгорный', 'Челябинская область', 7425, 74),
(962, 'Троицк', 'Челябинская область', 7426, 74),
(963, 'Троицк', 'Московская область', 5062, 50),
(964, 'Трубчевск', 'Брянская область', 3214, 32),
(965, 'Туапсе', 'Краснодарский край', 2324, 23),
(966, 'Туймазы', 'Башкортостан', 219, 2),
(967, 'Тула', 'Тульская область', 7101, 71),
(968, 'Тулун', 'Иркутская область', 3815, 38),
(969, 'Туран', 'Тыва', 1703, 17),
(970, 'Туринск', 'Свердловская область', 6647, 66),
(971, 'Тутаев', 'Ярославская область', 7610, 76),
(972, 'Тында', 'Амурская область', 2808, 28),
(973, 'Тырныауз', 'Кабардино-Балкария', 707, 7),
(974, 'Тюкалинск', 'Омская область', 5506, 55),
(975, 'Тюмень', 'Тюменская область', 7201, 72),
(976, 'Уварово', 'Тамбовская область', 6808, 68),
(977, 'Углегорск', 'Сахалинская область', 6515, 65),
(978, 'Углич', 'Ярославская область', 7611, 76),
(979, 'Удачный', 'Якутия', 1413, 14),
(980, 'Удомля', 'Тверская область', 6924, 69),
(981, 'Ужур', 'Красноярский край', 2422, 24),
(982, 'Узловая', 'Тульская область', 7117, 71),
(983, 'Улан-Удэ', 'Бурятия', 301, 3),
(984, 'Ульяновск', 'Ульяновская область', 7301, 73),
(985, 'Унеча', 'Брянская область', 3215, 32),
(986, 'Урай', 'Ханты-мансийский АО', 8615, 86),
(987, 'Урень', 'Нижегородская область', 5225, 52),
(988, 'Уржум', 'Кировская область', 4317, 43),
(989, 'Урюпинск', 'Волгоградская область', 3418, 34),
(990, 'Усинск', 'Коми', 1109, 11),
(991, 'Усмань', 'Липецкая область', 4807, 48),
(992, 'Усолье', 'Пермский край', 5921, 59),
(993, 'Усолье-Сибирское', 'Иркутская область', 3816, 38),
(994, 'Уссурийск', 'Приморский край', 2511, 25),
(995, 'Усть-Джегута', 'Карачаево-Черкесия', 904, 9),
(996, 'Усть-Илимск', 'Иркутская область', 3817, 38),
(997, 'Усть-Катав', 'Челябинская область', 7427, 74),
(998, 'Усть-Кут', 'Иркутская область', 3818, 38),
(999, 'Усть-Лабинск', 'Краснодарский край', 2325, 23),
(1000, 'Устюжна', 'Вологодская область', 3512, 35),
(1001, 'Уфа', 'Башкортостан', 201, 2),
(1002, 'Ухта', 'Коми', 1110, 11),
(1003, 'Учалы', 'Башкортостан', 220, 2),
(1004, 'Уяр', 'Красноярский край', 2423, 24),
(1005, 'Фатеж', 'Курская область', 4609, 46),
(1006, 'Фокино', 'Приморский край', 2512, 25),
(1007, 'Фокино', 'Брянская область', 3216, 32),
(1008, 'Фролово', 'Волгоградская область', 3419, 34),
(1009, 'Фрязино', 'Московская область', 5063, 50),
(1010, 'Фурманов', 'Ивановская область', 3714, 37),
(1011, 'Хабаровск', 'Хабаровский край', 2701, 27),
(1012, 'Хадыженск', 'Краснодарский край', 2326, 23),
(1013, 'Ханты-Мансийск', 'Ханты-мансийский АО', 8601, 86),
(1014, 'Харабали', 'Астраханская область', 3006, 30),
(1015, 'Харовск', 'Вологодская область', 3513, 35),
(1016, 'Хасавюрт', 'Дагестан', 509, 5),
(1017, 'Хвалынск', 'Саратовская область', 6416, 64),
(1018, 'Хилок', 'Забайкальский край', 7509, 75),
(1019, 'Химки', 'Московская область', 5064, 50),
(1020, 'Холм', 'Новгородская область', 5309, 53),
(1021, 'Холмск', 'Сахалинская область', 6516, 65),
(1022, 'Хотьково', 'Московская область', 5065, 50),
(1023, 'Цивильск', 'Чувашия', 2107, 21),
(1024, 'Цимлянск', 'Ростовская область', 6122, 61),
(1025, 'Чадан', 'Тыва', 1704, 17),
(1026, 'Чайковский', 'Пермский край', 5922, 59),
(1027, 'Чапаевск', 'Самарская область', 6311, 63),
(1028, 'Чаплыгин', 'Липецкая область', 4808, 48),
(1029, 'Чебаркуль', 'Челябинская область', 7428, 74),
(1030, 'Чебоксары', 'Чувашия', 2101, 21),
(1031, 'Чегем', 'Кабардино-Балкария', 708, 7),
(1032, 'Чекалин', 'Тульская область', 7118, 71),
(1033, 'Челябинск', 'Челябинская область', 7401, 74),
(1034, 'Чердынь', 'Пермский край', 5923, 59),
(1035, 'Черемхово', 'Иркутская область', 3819, 38),
(1036, 'Черепаново', 'Новосибирская область', 5413, 54),
(1037, 'Череповец', 'Вологодская область', 3514, 35),
(1038, 'Черкесск', 'Карачаево-Черкесия', 901, 9),
(1039, 'Чермоз', 'Пермский край', 5924, 59),
(1040, 'Черноголовка', 'Московская область', 5075, 50),
(1041, 'Черногорск', 'Хакасия', 1905, 19),
(1042, 'Чернушка', 'Пермский край', 5925, 59),
(1043, 'Черняховск', 'Калининградская область', 3922, 39),
(1044, 'Чехов', 'Московская область', 5066, 50),
(1045, 'Чистополь', 'Татарстан', 1619, 16),
(1046, 'Чита', 'Забайкальский край', 7501, 75),
(1047, 'Чкаловск', 'Нижегородская область', 5226, 52),
(1048, 'Чудово', 'Новгородская область', 5310, 53),
(1049, 'Чулым', 'Новосибирская область', 5414, 54),
(1050, 'Чусовой', 'Пермский край', 5926, 59),
(1051, 'Чухлома', 'Костромская область', 4411, 44),
(1052, 'Шагонар', 'Тыва', 1705, 17),
(1053, 'Шадринск', 'Курганская область', 4507, 45),
(1054, 'Шарыпово', 'Красноярский край', 2424, 24),
(1055, 'Шарья', 'Костромская область', 4412, 44),
(1056, 'Шатура', 'Московская область', 5067, 50),
(1057, 'Шахтерск', 'Сахалинская область', 6518, 65),
(1058, 'Шахты', 'Ростовская область', 6123, 61),
(1059, 'Шахунья', 'Нижегородская область', 5227, 52),
(1060, 'Шацк', 'Рязанская область', 6212, 62),
(1061, 'Шебекино', 'Белгородская область', 3110, 31),
(1062, 'Шелехов', 'Иркутская область', 3820, 38),
(1063, 'Шенкурск', 'Архангельская область', 2913, 29),
(1064, 'Шилка', 'Забайкальский край', 7510, 75),
(1065, 'Шимановск', 'Амурская область', 2809, 28),
(1066, 'Шлиссельбург', 'Ленинградская область', 4728, 47),
(1067, 'Шумерля', 'Чувашия', 2108, 21),
(1068, 'Шумиха', 'Курганская область', 4508, 45),
(1069, 'Шуя', 'Ивановская область', 3715, 37),
(1070, 'Щекино', 'Тульская область', 7119, 71),
(1071, 'Щелково', 'Московская область', 5068, 50),
(1072, 'Щербинка', 'Московская область', 5069, 50),
(1073, 'Щигры', 'Курская область', 4610, 46),
(1074, 'Щучье', 'Курганская область', 4509, 45),
(1075, 'Электрогорск', 'Московская область', 5070, 50),
(1076, 'Электросталь', 'Московская область', 5071, 50),
(1077, 'Электроугли', 'Московская область', 5072, 50),
(1078, 'Элиста', 'Калмыкия', 801, 8),
(1079, 'Энгельс', 'Саратовская область', 6417, 64),
(1080, 'Эртиль', 'Воронежская область', 3615, 36),
(1081, 'Юбилейный', 'Московская область', 5073, 50),
(1082, 'Югорск', 'Ханты-мансийский АО', 8616, 86),
(1083, 'Южа', 'Ивановская область', 3716, 37),
(1084, 'Южно-Сахалинск', 'Сахалинская область', 6501, 65),
(1085, 'Южноуральск', 'Челябинская область', 7429, 74),
(1086, 'Юрга', 'Кемеровская область', 4220, 42),
(1087, 'Юрьевец', 'Ивановская область', 3717, 37),
(1088, 'Юрьев-Польский', 'Владимирская область', 3323, 33),
(1089, 'Юрюзань', 'Челябинская область', 7430, 74),
(1090, 'Юхнов', 'Калужская область', 4021, 40),
(1091, 'Ядрин', 'Чувашия', 2109, 21),
(1092, 'Якутск', 'Якутия', 1401, 14),
(1093, 'Ялуторовск', 'Тюменская область', 7205, 72),
(1094, 'Янаул', 'Башкортостан', 221, 2),
(1095, 'Яранск', 'Кировская область', 4319, 43),
(1096, 'Яровое', 'Алтайский край', 2212, 22),
(1097, 'Ярославль', 'Ярославская область', 7601, 76),
(1098, 'Ярцево', 'Смоленская область', 6715, 67),
(1099, 'Ясногорск', 'Тульская область', 7120, 71),
(1100, 'Ясный', 'Оренбургская область', 5612, 56),
(1101, 'Яхрома', 'Московская область', 5074, 50);";

echo_data_add($sql,$tbl);
//* *****************************************************************
//* *****************************************************************



$tbl='s_tip_price';
$sql="
INSERT INTO `s_tip_price` (`id`, `name`) VALUES
(1, 'Основной');

";
echo_data_add($sql,$tbl);
//* *****************************************************************
//* *****************************************************************


$tbl='i_contr_org';
$sql="
INSERT INTO `i_contr_org` (`id`,`name`,`inn`) VALUES
(1,'ООО \"Фирма\"',2460000000);

";
echo_data_add($sql,$tbl);
//* *****************************************************************

//* *****************************************************************


$tbl='i_social_network';
$sql="
INSERT INTO `i_social_network` (`id`, `sid`, `chk_active`, `name`,`comments`) VALUES
(1, 1, 1, 'VK',''),
(2, 2, 1, 'Facebook',''),
(3, 3, 1, 'Instagram',''),
(4, 4, 1, 'WhatsApp',''),
(5, 5, 1, 'Skype',''),
(6, 6, 1, 'Youtube',''),
(7, 7, 1, 'Twitter',''),
(8, 8, 1, 'Одноклассники',''),
(9, 9, 1, 'Google+',''),
(10, 10, 1, 'Телеграм',''),
(11, 11, 1, 'Мой мир@Mail.ru','')
;

";
echo_data_add($sql,$tbl);
//* *****************************************************************

//* *****************************************************************
$i_city_id='595';//Москва
if (isset($_REQUEST['i_city_name']) and $_REQUEST['i_city_name']!=''){

$sql = "SELECT IF(COUNT(*)>0,i_city.id,'')
				FROM i_city 
				   WHERE i_city.name='".str_replace(array("'",'"','\\',"/"),'',strip_tags($_REQUEST['i_city_name']))."'
";
 
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);
$i_city_id=$myrow[0];
    if ($i_city_id==''){
        $i_city_id='595';//Москва
    }
}

if (isset($_REQUEST['i_tp_name']) and $_REQUEST['i_tp_name']!=''){
$tbl='i_tp';
$sql="
INSERT INTO `i_tp` (`id`, `chk_active`, `name`,`i_contr_org_id`,`i_city_id`) VALUES
(1, 1, '".str_replace(array("'",'"','\\',"/"),'',strip_tags($_REQUEST['i_tp_name']))."',1,'".$i_city_id."');

";
echo_data_add($sql,$tbl);
}
//* *****************************************************************

//* *****************************************************************


$tbl='i_docs';
$sql="
INSERT INTO `i_docs` (`id`, `a_menu_id`, `chk_active`, `name`, `file_name`, `html_code`, `data_create`) VALUES
(13176, 16, 1, 'Счет', 'docx_schet', '<p><a href=\"/upload/file/schet_docx.docx\">/upload/file/schet_docx.docx</a></p>', '2015-10-10 11:33:16'),
(13177, 50, 1, 'ПКО', 'docx_pko', '<p><a href=\"/upload/file/pko.docx\">/upload/file/pko.docx</a></p>', '2015-10-10 11:34:53'),
(13178, 16, 1, 'Акт выполненных работ', 'docx_act', '<p><a href=\"/upload/file/akt.docx\">/upload/file/akt.docx</a></p>', '2015-10-10 11:35:43'),
(13179, 16, 1, 'Товарный чек', 'docx_tovar_check', '<p><a href=\"/upload/file/tch.docx\">/upload/file/tch.docx</a></p>', '2015-10-10 11:36:58'),
(13180, 16, 1, 'ТТН', 'ttn', '<p><a href=\"/upload/file/ttn.docx\">/upload/file/ttn.docx</a></p>', '2015-10-10 11:40:41'),
(13181, 16, 1, 'Счет-фактура', 'sf', '<p><a href=\"/upload/file/sf.docx\">/upload/file/sf.docx</a></p>', '2015-10-10 11:41:03'),
(13182, 16, 1, 'Договор №1', 'dogovor1', '<p><a href=\"/upload/file/dogovor1.docx\">/upload/file/dogovor1.docx</a></p>', '2015-10-10 11:41:42'),
(13183, 16, 1, 'Договор №2', 'dogovor2', '<p><a href=\"/upload/file/dogovor2.docx\">/upload/file/dogovor2.docx</a></p>', '2015-10-10 11:42:02'),
(13184, 16, 1, 'Гарантия', 'garant_t', '<p><a href=\"/upload/file/garant_t.docx\">/upload/file/garant_t.docx</a></p>', '2015-10-10 11:42:02'),
(13185, 16, 1, 'Диагностика', 'diagnoz', '<p><a href=\"/upload/file/diagnoz.docx\">/upload/file/diagnoz.docx</a></p>', '2015-10-10 11:42:02'),
(13186, 41, 1, 'Акт сверки', 'aktsverki', '<p><a href=\"/upload/file/aktsverki.docx\">/upload/file/aktsverki.docx</a></p>', '2015-10-10 11:42:02'),
(13187, 16, 1, 'Счет с печатью', 'schet_print', '<p><a href=\"/upload/file/schet_print.docx\">/upload/file/schet_print.docx</a></p>', '2015-10-10 11:42:02'),
(13190, 50, 1, 'РКО', 'docx_rko', '<p><a href=\"/upload/file/rko.docx\">/upload/file/rko.docx</a></p>', '2016-04-11 11:34:53'),
(13300, 16, 1, 'Бланк посылки', 'post_form', '<p><a href=\"/upload/file/post_form.docx\">/upload/file/post_form.docx</a></p>', '2016-04-11 11:34:53'),
(13401, 16, 0, 'Договор поставки', 'dogovor_postavki', '<p><a href=\"/upload/file/dogovor_postavki.docx\">/upload/file/dogovor_postavki.docx</a></p>', '2016-04-11 11:34:53'),
(13420, 16, 0, 'Маршрутный лист', 'marshrut_list', '<p><a href=\"/upload/file/marshrut_list.docx\">/upload/file/marshrut_list.docx</a></p>', '2018-01-20 11:34:53'),
(13500, 16, 0, 'Договор мебель', 'dogovor_mebel', '<p><a href=\"/upload/file/dogovor_mebel.docx\">/upload/file/dogovor_mebel.docx</a></p>', '2018-10-28 11:34:53'),
(13600, 50, 1, 'Кассовая книга', 'kassa_book', '<p><a href=\"/upload/file/kassa_book.docx\">/upload/file/kassa_book.docx</a></p>', '2018-11-23 11:34:53');

";
echo_data_add($sql,$tbl);
//* *****************************************************************

//* *****************************************************************


$tbl='i_obj';
$sql="
INSERT INTO `i_obj` (`id`, `obj`, `target`) VALUES
(1, 'Заказ', 'Процент со всего заказа'),
(3, 'Поступление', 'Фиксированная сумма с поступления: авто'),
(4, 'Работа', 'Процент с работы'),
(5, 'Заказ', 'Фиксированная сумма с заказа: авто'),
(6, 'Работа', 'Фиксированная сумма с работы: авто'),
(7, 'Работа', 'Фиксированная сумма с работы: вручную'),
(9, 'Заказ', 'Процент с маржи заказа'),
(12, 'Поступление', 'Процент с маржи проданного товара из поступления: авто');
";
echo_data_add($sql,$tbl);
//* *****************************************************************

//* *****************************************************************


$tbl='m_log_type';
$sql="
INSERT INTO `m_log_type` (`id`, `name`) VALUES
(1, 'Отмена заказа'),
(2, 'Отрытие заказа'),
(3, 'Выбор контрагента'),
(4, 'Выбор работника'),
(5, 'Выбор филиала'),
(6, 'Изменение даты'),
(7, 'Изменение комментариев'),
(8, 'Выбор ответственного'),
(9, 'Установка напоминания'),
(10, 'Добавление товара'),
(11, 'Удаление товара'),
(12, 'Изменение цены товара'),
(13, 'Изменение количества товара'),
(14, 'Выбор штрих-кода товара'),
(15, 'Добавление комментариев к товару'),
(16, 'Добавление услуги'),
(17, 'Удаление услуги'),
(18, 'Изменение цены услуги'),
(19, 'Изменение количества услуги'),
(20, 'Добавление комментариев к услуге'),
(21, 'Добавление платежа'),
(22, 'Удаление платежа'),
(23, 'Изменение названия проекта'),
(24, 'Загрузка документа'),
(25, 'Удаление документа'),
(26, 'Изменение ФИО получателя в доставке'),
(27, 'Изменение города в доставке'),
(28, 'Изменение адреса в доставке'),
(29, 'Изменение индекса в доставке'),
(30, 'Изменение телефона в доставке'),
(31, 'Изменение номера отправления в доставке'),
(32, 'Изменение даты отправления в доставке'),
(33, 'Изменение транспортной компании в доставке'),
(34, 'Изменение товара'),
(35, 'Изменение услуги'),
(36, 'Добавление контрагента'),
(37, 'Изменение контрагента'),
(38, 'Назначение работника на услугу'),
(39, 'Изменение суммы в доставке'),
(40, 'Ввод штрих-кода товара'),
(41, 'Изменение даты доставки');
";
echo_data_add($sql,$tbl);
//* *****************************************************************



///**********************************************************************************************
//добавляем фото
$dir_='../i';
if (!file_exists($dir_)){mkdir($dir_,0777);}
$dir_='../i/a_admin';
if (!file_exists($dir_)){mkdir($dir_,0777);}
$dir_='../i/a_admin/original';
if (!file_exists($dir_)){mkdir($dir_,0777);}
if (file_exists('i/sa.png')){
    copy('i/sa.png',$dir_.'/sa.png');



$tbl='a_photo';
$sql="
INSERT INTO `a_photo` (`a_menu_id`, `row_id`, `sid`, `tip`, `img`) VALUES
(4, 1, 1, 'Основное', 'sa.png');


";
    
    echo_data_add($sql,$tbl);
    
}else{
    echo '<br />НЕТ ФОТО АДМИНА sa.png<br />';
}
//* *****************************************************************

$sql = "SELECT COUNT(*) 
				FROM a_admin
					WHERE id='1'
	"; 
$res = mysql_query($sql);
$myrow = mysql_fetch_array($res);
if ($myrow[0]>0){
    //***********************************************************************************************
    // Добавляем данные в доступ
    $sql = "SELECT COUNT(*) 
    				FROM a_admin_a_menu
    	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]==0){
        
        $sql = "INSERT into a_admin_a_menu (
        				id1,
        				id2
        			) (SELECT '1', a_menu.id
                            FROM a_menu
                                WHERE a_menu.chk_active='1'
        )";
        if (!mysql_query($sql)){echo $sql;exit();}
    }
    
    //***********************************************************************************************
    // Добавляем данные в доступ
    $sql = "SELECT COUNT(*) 
    				FROM a_admin_a_menu_a_com 
    	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]==0){
        
        $sql = "INSERT into a_admin_a_menu_a_com (
        				id1,
        				id2
        			) (SELECT '1', a_menu_a_com.id
                            FROM a_menu_a_com
        )";
        if (!mysql_query($sql)){echo $sql;exit();}
    } 
    //***********************************************************************************************
    // Добавляем данные в доступ
    $sql = "SELECT COUNT(*) 
    				FROM a_admin_a_col
    	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]==0){
        
        $sql = "INSERT into a_admin_a_col (
        				id1,
        				id2
        			) (SELECT '1', a_col.id
                            FROM a_col
        )";
        if (!mysql_query($sql)){echo $sql;exit();}
    }
    //**************************
    // Добавляем данные в доступ должности
    $sql = "SELECT COUNT(*) 
    				FROM a_admin_i_post
    	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]==0){
        
        $sql = "INSERT into a_admin_i_post (
        				id1,
        				id2
        			) (SELECT '1', i_post.id
                            FROM i_post
        )";
        if (!mysql_query($sql)){echo $sql;exit();}
    }

}
//***********************************************************************************************
$sql = "
ALTER TABLE `s_cat_s_prop_val` CHANGE `id1` `id1` BIGINT( 20 ) NULL;
ALTER TABLE `s_cat_s_prop_val` CHANGE `id2` `id2` BIGINT( 20 ) NULL;
ALTER TABLE `s_prop_val` CHANGE `s_prop_id` `s_prop_id` BIGINT( 20 ) NULL;

"; 
$res = mysql_query($sql);
$sql = "
		UPDATE a_col 
			SET  
				tip='Текст'
		
		WHERE id IN ('154','155','156','157','158','159','160')
";
$res = mysql_query($sql);

echo '<h1 style="font-size:24px; color:#090;">Обновление базы данных успешно завершено! <a href="http://'.$_SERVER['SERVER_NAME'].$cur_dir .'/admin">Перейти в админку...</a></h1>';
//echo '<script>window.location.href = "http://'.$_SERVER['SERVER_NAME'].'/admin";</script>';


$res = mysql_query("ALTER TABLE `m_zakaz_s_cat` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT;");

//удаляем столбец barcode
$names=array();
$names=get_column_names_with_show('m_postav_s_cat');

if (in_array('barcode',$names)){
    echo 'ALTER TABLE `m_postav_s_cat`   DROP  `barcode`;<br />';
    $res = mysql_query("ALTER TABLE `m_postav_s_cat`   DROP  `barcode`;");    
}

$names=get_column_names_with_show('m_tovar');
if (in_array('kolvo',$names)){
    echo 'ALTER TABLE `m_tovar` DROP `kolvo`<br />';
    $res = mysql_query("ALTER TABLE `m_tovar` DROP `kolvo`;");    
}
if (in_array('status',$names)){
    echo 'ALTER TABLE `m_tovar` DROP `status`<br />';
    $res = mysql_query("ALTER TABLE `m_tovar` DROP `status`;");    
}
$names=get_column_names_with_show('i_zp');
if (in_array('target',$names)){
    echo 'ALTER TABLE `i_zp` DROP `target`<br />';
    $res = mysql_query("ALTER TABLE `i_zp` DROP `target`;");    
}

$names=get_column_names_with_show('m_zakaz_s_cat');
if (in_array('a_admin_id',$names)){
    echo 'ALTER TABLE `m_zakaz_s_cat` DROP `a_admin_id`<br />';
    $res = mysql_query("ALTER TABLE `m_zakaz_s_cat` DROP `a_admin_id`;");    
}
if (in_array('a_admin_price',$names)){
    echo 'ALTER TABLE `m_zakaz_s_cat` DROP `a_admin_price`<br />';
    $res = mysql_query("ALTER TABLE `m_zakaz_s_cat` DROP `a_admin_price`;");    
}



$names=get_column_names_with_show('m_platezi');
if (in_array('m_zakaz_id',$names)){
    $sql = "
    		UPDATE m_platezi 
    			SET  
    				id_z_p_p=m_zakaz_id,
                    a_menu_id='16'
                    
    		
    		WHERE m_zakaz_id>0
    ";
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    
    
    echo 'ALTER TABLE `m_platezi` DROP `m_zakaz_id`<br />';
    $res = mysql_query("ALTER TABLE `m_platezi` DROP `m_zakaz_id`;");    
}

if (in_array('m_postav_id',$names)){
    $sql = "
    		UPDATE m_platezi 
    			SET  
    				id_z_p_p=m_postav_id,
                    a_menu_id='17'
                    
    		
    		WHERE m_postav_id>0
    ";
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    
    
    echo 'ALTER TABLE `m_platezi` DROP `m_postav_id`<br />';
    $res = mysql_query("ALTER TABLE `m_platezi` DROP `m_postav_id`;");    
    
    $res = @mysql_query("DROP TABLE `m_dialog_file`;");    
    
}

$sql = "SELECT COUNT(*)
				FROM i_city 
					WHERE i_city.us_name IS NULL OR
                       i_city.us_name=''
	"; 

$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);

if ($myrow[0]>0){
    $sql = "SELECT i_city.id,i_city.name 
    				FROM i_city 
					   WHERE i_city.us_name  IS NULL OR
                       i_city.us_name=''
    ";
     
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $sql_upp = "
        		UPDATE i_city 
        			SET  
        				i_city.us_name='"._DB(ru_us(trim($myrow['name'])))."'
        		
        		WHERE id='"._DB($myrow['id'])."'
        ";
        mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
    }
}


$names=get_column_names_with_show('m_dialog');
if (in_array('m_zakaz_id',$names)){
    //СООБЩЕНИЯ
    $sql = "SELECT COUNT(*)
    				FROM m_dialog 
    					WHERE m_dialog.m_zakaz_id>0
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $myrow = mysql_fetch_array($res);
    
    if ($myrow[0]>0){
        $sql = "SELECT m_dialog.m_zakaz_id,m_dialog.id 
        				FROM m_dialog 
    					   WHERE m_dialog.m_zakaz_id>0
        ";
         
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $sql_upp = "
            		UPDATE m_dialog 
            			SET  
            				m_dialog.row_id='"._DB($myrow['m_zakaz_id'])."',
                            m_dialog.a_menu_id='16'
            		
            		WHERE id='"._DB($myrow['id'])."'
            ";
            mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        }
    }
    $sql = "ALTER TABLE `m_dialog` DROP `m_zakaz_id` ";
    mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    
}

if (in_array('m_postav_id',$names)){
    $sql = "SELECT COUNT(*)
    				FROM m_dialog 
    					WHERE m_dialog.m_postav_id>0
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $myrow = mysql_fetch_array($res);
    
    if ($myrow[0]>0){
        $sql = "SELECT m_dialog.m_postav_id,m_dialog.id 
        				FROM m_dialog 
    					   WHERE m_dialog.m_postav_id>0
        ";
         
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $sql_upp = "
            		UPDATE m_dialog 
            			SET  
            				m_dialog.row_id='"._DB($myrow['m_postav_id'])."',
                            m_dialog.a_menu_id='17'
            		
            		WHERE id='"._DB($myrow['id'])."'
            ";
            mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        }
    }
    $sql = "ALTER TABLE `m_dialog` DROP `m_postav_id` ";
    mysql_query($sql) or die(mysql_error().'<br>'.$sql);
}


if (in_array('s_cat_id',$names)){

$sql = "SELECT COUNT(*)
				FROM m_dialog 
					WHERE m_dialog.s_cat_id>0
	"; 

$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);

if ($myrow[0]>0){
    $sql = "SELECT m_dialog.s_cat_id,m_dialog.id 
    				FROM m_dialog 
					   WHERE m_dialog.s_cat_id>0
    ";
     
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $sql_upp = "
        		UPDATE m_dialog 
        			SET  
        				m_dialog.row_id='"._DB($myrow['s_cat_id'])."',
                        m_dialog.a_menu_id='7'
        		
        		WHERE id='"._DB($myrow['id'])."'
        ";
        mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
    }
}
    $sql = "ALTER TABLE `m_dialog` DROP `s_cat_id` ";
    mysql_query($sql) or die(mysql_error().'<br>'.$sql);
}


}



$names_m_postav_arr=get_column_names_with_show('m_postav');
if (isset($names_m_postav_arr) and is_array($names_m_postav_arr) and in_array('m_zakaz_id',$names_m_postav_arr)){
    $sql = "SELECT COUNT(*)
    				FROM m_postav 
    				   WHERE m_postav.m_zakaz_id>0
    ";
     
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]>0){
         $sql = "SELECT m_postav.id, m_postav.m_zakaz_id
        				FROM m_postav 
        				   WHERE m_postav.m_zakaz_id>0
        ";
         
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $sql_upp = "UPDATE m_postav_s_cat 
            			SET  
            				m_postav_s_cat.m_zakaz_id='"._DB($myrow[1])."'
            		
            		WHERE m_postav_s_cat.m_postav_id='"._DB($myrow[0])."'
            ";
            $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        }
    }
    
    
    
    
}
$sql = "show tables like 'm_dostavka'";
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);
if ($myrow[0]=='m_dostavka'){
$sql = "SELECT m_dostavka.id, m_dostavka.i_tk_id-0, m_dostavka.i_city_id-0
				FROM m_dostavka 
				   WHERE m_dostavka.chk_active='1'
                   AND m_dostavka.i_tk_id IS NULL
                   AND m_dostavka.i_city_id IS NULL
";
 
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
{
    if ($myrow[1]-0==0 and $myrow[2]-0==0){
       $sql_upp = "UPDATE m_dostavka 
            			SET  
            				m_dostavka.chk_active='0'
            		
            		WHERE m_dostavka.id='"._DB($myrow[0])."'
            ";
            //echo $sql.'<br />--'.$sql_upp.'<br /><br />';
            $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
    }
}
}
$sql = "show tables like 's_article'";
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);
if ($myrow[0]=='s_article'){
    $sql = "ALTER TABLE `s_article` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT;
    ";
     
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
}


$sql_upp = "UPDATE a_connect 
			SET  
				a_connect.usl=''
		
		WHERE a_connect.id='10'
";
//echo $sql.'<br />--'.$sql_upp.'<br /><br />';
$res_upp = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);

//удаляем  таблицу старую
$sql = "show tables like 'i_contr_s_test_answer'";
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);
if ($myrow[0]=='i_contr_s_test_answer'){
    $sql = "DROP TABLE `i_contr_s_test_answer`";
    mysql_query($sql) or die(mysql_error().'<br>'.$sql);  
}

$sql = "show tables like 's_struktura_s_test_options'";
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);
if ($myrow[0]=='s_struktura_s_test_options'){
    $sql = "DROP TABLE `s_struktura_s_test_options`";
    mysql_query($sql) or die(mysql_error().'<br>'.$sql);  
}




$sql = "show tables like 'a_col'";
$res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
$myrow = mysql_fetch_array($res);
if ($myrow[0]=='a_col'){
    $sql_upp = "UPDATE a_col SET col='chk_tip' WHERE id='2310'";
    mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
    $sql_upp = "UPDATE a_menu SET inc='s_test_quest' WHERE id='400'";
    mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
}
?>
</body>
</html>