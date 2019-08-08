<?php
if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
/*
//Подключаем библиотеку Google API
 require_once 'class/Google/vendor/autoload.php';
 $client = new Google_Client();
 $err_google='';
    if (!isset($_SESSION['a_options']['Google Client Secret - для Google Drive']) or $_SESSION['a_options']['Google Client Secret - для Google Drive']==''){
     $err_google.='<p>Отсутствует параметр <strong>Google Client Secret - для Google Drive</strong></p>';  
    }
    if (!isset($_SESSION['a_options']['Google Redirect Uri - для Google Drive']) or $_SESSION['a_options']['Google Redirect Uri - для Google Drive']==''){
     $err_google.='<p>Отсутствует параметр <strong>Google Redirect Uri - для Google Drive</strong></p>';  
    }
 if ($err_google==''){
     $client->setClientId($_SESSION['a_options']['Google Client Secret - для Google Drive']);
     $client->setClientSecret($_SESSION['a_options']['Google Redirect Uri - для Google Drive']);
     $client->setRedirectUri('http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=c_call_client');
     $client->setScopes(array('https://www.google.com/m8/feeds/user/ https://www.google.com/m8/feeds/ https://www.googleapis.com/auth/drive https://apps-apis.google.com/a/feeds/user/ https://apps-apis.google.com/a/feeds/alias/ https://apps-apis.google.com/a/feeds/groups/ https://www.google.com/m8/feeds https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive.metadata.readonly https://www.googleapis.com/auth/drive.appdata https://www.googleapis.com/auth/drive.readonly https://www.googleapis.com/auth/drive.apps.readonly https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.me https://docs.google.com/feeds/ https://docs.googleusercontent.com/ https://spreadsheets.google.com/feeds/'));
     
     //Если есть связь с google drive
     if (isset($_REQUEST['code']) || (isset($_SESSION['google_drive_access_token']) && $_SESSION['google_drive_access_token'])) {
        if (isset($_REQUEST['code'])) {
            $client->authenticate($_REQUEST['code']);
            $_SESSION['google_drive_access_token'] = $client->getAccessToken();
            header('Location: http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=c_call_client');
        } else{
            $client->setAccessToken($_SESSION['google_drive_access_token']);
        }
     }
     else{//Если нет связи с google drive
        
        if (isset($_REQUEST['google_drive_connect'])){
            $authUrl = $client->createAuthUrl();
            header('Location: ' . $authUrl);
        }
     }
     
 }

*/

//google_drive_connect

?>