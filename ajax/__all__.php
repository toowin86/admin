<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

include "../db.php";
include "../functions.php";


if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


$_t=_GP('_t');
// ************************************************************
// ПОИСК ККОНТРАГЕНТА
//**************************************************************
if ($_t=='i_contr_autocomplete'){
    $data_=array();
    $term=_GP('term');
    
    $data_[0]['label']='Добавить нового контрагента';
    $data_[0]['value']=$term;
    $data_[0]['id']='';
    
    
    
    //Тип авторизации
    $login="phone";if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0'){$login="email";}
    
    $sql = "SELECT i_contr.id, i_contr.name, i_contr.`$login`
    				FROM i_contr 
    					WHERE i_contr.id='"._DB($term)."' 
                        OR i_contr.name LIKE '%"._DB($term)."%'
                        OR i_contr.`$login` LIKE '"._DB($term)."%'
   						
                        LIMIT 20
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_[$i]['label']=_DB($myrow[1].' '.$myrow[2]);
        $data_[$i]['value']=_DB($myrow[1]);
        $data_[$i]['id']=$myrow[0];
        
    }
    
    
    echo json_encode($data_);
}

//************************************************************************************************** 
}else{
    
    $_SESSION['error']['auth_'.date('Y-m-d H:i:s')]='Ошибка авторизации! $login="'.@$_SESSION['admin']['login'].'", pass: "'.@$_SESSION['admin']['password'].'"';
    echo 'Ошибка авторизации!';
    
}

?>