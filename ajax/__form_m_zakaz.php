<?php
//ВСЕ ФУНКЦИИ
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

include "../db.php";
include "../functions.php";


if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


$_t=_GP('_t');
//***********************************************************************
//ФОРМА заказа ***********************************************
//***********************************************************************
// ************************************************************
// 
if ($_t=='m_zakaz'){
    $nomer=_GP('nomer');
    
    ?>
    
    <?php
    
}
//***********************************************************************
//Сохранение заказа ***********************************************
//***********************************************************************
if ($_t=='m_zakaz_save'){
    $nomer=_GP('nomer');
    $data_=array();
    
    echo json_encode($data_);
}
//************************************************************************************************** 
}else{
    
    $_SESSION['error']['auth_'.date('Y-m-d H:i:s')]='Ошибка авторизации! $login="'.@$_SESSION['admin']['login'].'", pass: "'.@$_SESSION['admin']['password'].'"';
    echo 'Ошибка авторизации!';
    
}

?>