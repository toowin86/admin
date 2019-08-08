<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


$_t=_GP('_t');
// ************************************************************
// 
if ($_t==''){

}



//************************************************************************************************** 
}else{
    echo 'Ошибка авторизации!';
}
?>