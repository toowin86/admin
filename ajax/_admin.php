<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){
    $_t=_GP('_t');
    
    if ($_t=='last_visit'){
        $sql = "
        		UPDATE a_admin 
        			SET  
        				data_visit='".date('Y-m-d H:i:s')."'
        		
        		WHERE email='"._DB($_SESSION['admin']['email'])."'
                AND password='"._DB($_SESSION['admin']['password'])."'
        ";
        if(!mysql_query($sql)){echo $sql;exit();}
    }
    
}else{
    echo 'Ошибка авторизации!';
}
?>