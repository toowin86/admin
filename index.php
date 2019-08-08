<?php

setlocale(LC_ALL, 'ru_RU.UTF-8');
ini_set('display_errors', 1); 
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 3000);
error_reporting(E_ALL);
// Admin-panel v-web.ru v6.0
// create by toowin86@yandex.ru

// $_SESSION['admin']['email']
// $_SESSION['admin']['password']
header('Content-type: text/html; charset=utf-8');
header('Content-language: ru'); // en = English 
header('Cache-Control: no-store, no-cache');

//подключаем базу данных
if (file_exists('db.php')){include 'db.php';}else{echo 'ERROR: no db config file!';exit();}
// *************************************************************************

//соответствия
$sql = "SELECT name, html_code FROM s_words";
$res = mysql_query($sql); if (!$res){echo $sql; exit();}
for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
{
    $_SESSION['s_words'][$myrow[0]]=$myrow[1];
}


//подключаем файл функций
if (file_exists('functions.php')){include 'functions.php';}else{echo 'ERROR: no functions file!';exit();}

$a_admin_id_cur=0;
if (isset($_SESSION['admin']) and isset($_SESSION['admin']['email']) and $_SESSION['admin']['email']!='' and isset($_SESSION['admin']) and isset($_SESSION['admin']['password']) and $_SESSION['admin']['password']!=''){
    //Получаем id админа
    $sql = "SELECT IF(COUNT(*)>0,a_admin.id,'') 
        				FROM a_admin 
        					WHERE a_admin.email='"._DB(@$_SESSION['admin']['email'])."' 
                            AND a_admin.password='"._DB(@$_SESSION['admin']['password'])."'
            	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    $a_admin_id_cur=$myrow[0];
}


//Если сайт лежит не на корне
$cur_dir=cur_dir_();
//end Если сайт лежит не на корне
//обработка ошибок
if (isset($_SESSION['error']) and is_array($_SESSION['error']) and count($_SESSION['error'])>0){
    $email_from='toowin86@yandex.com';
    if (isset($_SESSION['options']['Отправка счетов при сохранении на email']) and is_email($_SESSION['options']['Отправка счетов при сохранении на email'])){
        $email_from=$_SESSION['options']['Отправка счетов при сохранении на email'];
    }
    $txt='<tr><td><strong>Ошибка</strong></td><td><strong>Сообщение</strong></td></tr>';
    foreach($_SESSION['error'] as $err_tip => $err_mes){
        $txt.='<tr><td style="padding:10px;border:1px solid #999;">'.$err_tip.'</td><td style="padding:10px;border:1px solid #999;">'.$err_mes.'</td></tr>';
    }
    send_mail_smtp('toowin86@yandex.com','Отчет об ошибках: '.$_SERVER['SERVER_NAME'],'<table>'.$txt.'</table>','Разработчику',$email_from,'от '.$_SERVER['SERVER_NAME'],2);
    unset($_SESSION['error']);
}

//обработка переменных
$inc=_GP('inc'); // include
    $com=_GP('com'); // функция
    $nomer=_GP('nomer'); // номер

$inc_id=0; // include_id
$title='';$auth_info='';


if ($inc!='' and !file_exists('obrabotchik/'.$inc.'.php')){
    $_SESSION['old_inc']=$inc;
}

if ($inc=='' and $com=='' and $nomer==''){
    if (isset($_SESSION['old_inc']) and $_SESSION['old_inc']!=''){
        $inc=$_SESSION['old_inc'];
    }else{
        $inc='start_menu';
    }
}

//авторизация
if (_GP('login')!='' and _GP('password')!=''){
    $sql = "SELECT IF(COUNT(*)>0,a_admin.chk_active,'-'), IF(COUNT(*)>0,a_admin.id,'-')
    				FROM a_admin 
    					WHERE a_admin.email='".rawurldecode(_DB(_GP('login')))."' 
                        AND a_admin.password='".md5(_GP('password').$_SESSION['a_options']['secret_key']._GP('password'))."'
    	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]=='-'){$auth_info='Ссылка не действительна!';header("Location: http://".$_SERVER['SERVER_NAME'].$cur_dir.'/admin/');}
    elseif($myrow[0]=='0'){unset($_SESSION['admin']['email'],$_SESSION['admin']['password']);$auth_info='Пользователь отключен!';}
    elseif($myrow[0]=='1'){
        //изменяем пароль
        
        
        $pass=generate_code(10);
        $pass_md5=_GP('password');//md5($pass);//постоянное обновление паролей
        $pass_md=md5($pass_md5.$_SESSION['a_options']['secret_key'].$pass_md5);
        $sql = "UPDATE a_admin 
        			SET  
        				password='"._DB($pass_md)."',
                        data_change='".date('Y-m-d H:i:s')."',
                        data_visit='".date('Y-m-d H:i:s')."'
        		
        		WHERE a_admin.email='"._DB(_GP('login'))."'
        ";
       
        if(!mysql_query($sql)){echo $sql;exit();}
        
        
        unset($_SESSION['admin']['email'],$_SESSION['admin']['password']);
        $_SESSION['admin']['email']=_DB(_GP('login'));
        $_SESSION['admin']['password']=$pass_md;
        header("Location: http://".$_SERVER['SERVER_NAME'].$cur_dir.'/admin/');
    }
    
}


if (isset($_SESSION['admin']['email']) 
        and $_SESSION['admin']['email']!='' 
        and isset($_SESSION['admin']['password']) 
        and $_SESSION['admin']['password']!=''){
           
    $res_auth=admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password']);
    if ($res_auth=='-'){unset($_SESSION['admin']['email'],$_SESSION['admin']['password']);header("Refresh: 3");}
    elseif ($res_auth=='0'){unset($_SESSION['admin']['email'],$_SESSION['admin']['password']);header("Refresh: 3");}
    elseif ($res_auth=='1'){
        
        // меняем дату последнего визита
        $sql = "UPDATE a_admin 
        			SET  
                        data_visit='".date('Y-m-d H:i:s')."'
        		
        		WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."'
                AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
        ";
       
        if(!mysql_query($sql)){echo $sql;exit();}
        
        //МЕНЮ
        $a_menu_arr['pid']=array();$a_menu_arr['chk_active']=array();$a_menu_arr['name']=array();$a_menu_arr['inc']=array();
        
        $sql = "SELECT  a_menu.id,
                        a_menu.pid,
                        a_menu.chk_active,
                        a_menu.name,
                        a_menu.inc,
                        a_menu.comments,
                        a_menu.chk_block,
                        (SELECT COUNT(*) FROM a_admin_a_menu, a_admin WHERE a_admin_a_menu.id2=a_menu.id AND a_admin_a_menu.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."') AS dostup
                        
        				FROM a_menu 
        						ORDER BY sid 
        "; 
        $res = mysql_query($sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $a_menu_arr['pid'][$myrow[0]]=$myrow[1];
            $a_menu_arr['chk_active'][$myrow[0]]=$myrow[2];
            $a_menu_arr['name'][$myrow[0]]=$myrow[3];
            $a_menu_arr['inc'][$myrow[0]]=$myrow[4];
            $a_menu_arr['comments'][$myrow[0]]=$myrow[5];
            $a_menu_arr['chk_block'][$myrow[0]]=$myrow[6];
            $a_menu_arr['dostup'][$myrow[0]]=$myrow[7];
        }
        $top_menu_txt='';
        $left_menu=array();
        $left_menu_txt='';
        $active_id=0;
        
        if(isset($a_menu_arr['pid']) and count($a_menu_arr['pid'])>0){
            foreach($a_menu_arr['pid'] as $id => $pid){
                if ($a_menu_arr['chk_active'][$id]=='1' and $a_menu_arr['dostup'][$id]=='1'){
                    
                    if ($pid!=0){
                        $cl='';if ($a_menu_arr['inc'][$id]==$inc or ($inc=='' and $inc_id=='')){$cl=' class="active"';$active_id=$pid;$inc_id=(int) $id;}
                        $img='';if (file_exists('i/menu/'.$a_menu_arr['inc'][$id].'.png')){$img=' style="background-image: url(i/menu/'.$a_menu_arr['inc'][$id].'.png);"';}else{$img=' style="padding: 0 10px 0 10px;"';}
                        if (!isset($left_menu[$pid])) {$left_menu[$pid]='';}
                        $left_menu[$pid].='<li'.$cl.'><a href="?inc='.$a_menu_arr['inc'][$id].'"><table cellpadding="0" cellspacing="0"><tr><td'.$img.'>'.$a_menu_arr['name'][$id].'</td></tr></table></a></li>';
                    }
                }
            }
            foreach($a_menu_arr['pid'] as $id => $pid){
                if ($a_menu_arr['chk_active'][$id]=='1' and $a_menu_arr['dostup'][$id]=='1'){
                    if ($pid==0){
                        if ($a_menu_arr['inc'][$id]==$inc){$title=$a_menu_arr['name'][$id];$inc_id=(int) $id;}
                        $cl='';if ($a_menu_arr['inc'][$id]==$inc or ($active_id!=0 and $id==$active_id)){$cl=' class="active"';if ($active_id==0){$active_id=$id;}}
                        $top_menu_txt.='<li'.$cl.'><a data-id="'.$id.'" href="#">'.$a_menu_arr['name'][$id].'</a></li>';
                    }
                }
            }
        }
       
        //if ($inc_id==0){echo 'Error: null $inc_id;';exit();}
        
        // массив столбцов $names
        $names=array();
        $sql = "SELECT COUNT(*)
        				FROM a_menu 
        					WHERE a_menu.inc='"._DB($inc)."'
        	"; 
        $res = mysql_query($sql) or die(mysql_error());
        $myrow = mysql_fetch_array($res);
        if ($inc!='' and $myrow[0]>0){
            $names=get_column_names_with_show($inc);
        }
        
        foreach($left_menu as $pid => $txt)
        {
            $dis='';if ($active_id!=$pid){$dis=' style="display:none;"';}
            $left_menu_txt.='<div'.$dis.' class="left_podmenu_div" data-id="'.$pid.'">
                    <ul>'.$txt.'</ul></div>';
        }
        
        // Получаем данные функционального меню
        $a_com_arr['com']=array();$a_com_arr['name']=array();
        $sql = "SELECT  a_com.id,
                        a_com.com,
                        a_com.name
        				FROM a_menu_a_com, a_com
        					WHERE a_menu_a_com.id1='"._DB($inc_id)."'
                            AND a_menu_a_com.id2=a_com.id
        "; 
        $res = mysql_query($sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $a_com_arr['com'][$myrow[0]]=$myrow[1];
            $a_com_arr['name'][$myrow[0]]=$myrow[2];
        }

        //Доступ к функциям данного пользователя
        $a_admin_a_com_arr=array();
        $sql = "SELECT  a_com.id,
                        a_com.com
        				FROM a_admin_a_menu_a_com, a_admin, a_menu_a_com, a_com
        					WHERE a_admin_a_menu_a_com.id1=a_admin.id
                            AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                            AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                            AND a_menu_a_com.id=a_admin_a_menu_a_com.id2
                            AND a_menu_a_com.id1='"._DB($inc_id)."'
                            AND a_menu_a_com.id2=a_com.id
        "; 
        $res = mysql_query($sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $a_admin_a_com_arr[$myrow[0]]=$myrow[1];
        }
        
        // Получаем данные столбцов из базы
        $a_col_arr['chk_view']=array();$a_col_arr['chk_change']=array();$a_col_arr['col']=array();$a_col_arr['col_ru']=array();$a_col_arr['tip']=array();
        $sql = "SELECT  a_col.id,
                        a_col.chk_view,
                        a_col.chk_change,
                        a_col.col,
                        a_col.col_ru,
                        a_col.tip
                        
        				FROM a_col 
        					WHERE a_col.chk_active='1'
                            AND a_col.a_menu_id='"._DB($inc_id)."'
        						ORDER BY sid
        "; 
        $res = mysql_query($sql);
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $a_col_arr['chk_view'][$myrow[0]]=$myrow[1];
            $a_col_arr['chk_change'][$myrow[0]]=$myrow[2];
            $a_col_arr['col'][$myrow[0]]=$myrow[3];
            $a_col_arr['col_ru'][$myrow[0]]=$myrow[4];
            $a_col_arr['tip'][$myrow[0]]=$myrow[5];
        }
        
       
        
        // ОБРАБОТЧИК
        $include_=1;
        if ($inc!=''){
            if (file_exists('obrabotchik/'._DB($inc).'.php')){
                include 'obrabotchik/'._DB($inc).'.php';
            }
        }
        
        if ($include_==1){
            if (file_exists('shablon.php')){include 'shablon.php';}else{echo 'no shablon.php';exit();}
        }
        
    }
}else{
    if (file_exists('start.php')){include 'start.php';}
    else{echo 'no start.php';exit();}
}

unset($db);

?>