<?php
if (!isset($_REQUEST['k']) or $_REQUEST['k']!='rgE6fswSrn'){echo 'no auth'; exit;}

include 'db.php';
include 'functions.php';
ini_set('display_errors',1);
error_reporting(E_ALL);
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////


if (!isset($_REQUEST['k']) or $_REQUEST['k']!='rgE6fswSrn'){echo 'no auth'; exit;}


require_once 'class/Google/vendor/autoload.php';




$client = new Google_Client();


// Get your credentials from the console
//k-tehno-call-center 
$client->setClientId('997002113765-i667275tr0qpb4f7r3b1cco4les4hnl5.apps.googleusercontent.com');


$client->setClientSecret('NR2GkwUnYudpAOE6s8AHKkXi');
$client->setRedirectUri('http://k-tehno.ru/admin/gd.php?k=rgE6fswSrn');
//$client->setScopes(array('https://accounts.google.com/o/oauth2/auth'));

$client->setScopes(array('https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive.metadata.readonly https://www.googleapis.com/auth/drive.appdata https://www.googleapis.com/auth/drive.readonly https://www.googleapis.com/auth/drive.apps.readonly https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.me https://docs.google.com/feeds/ https://docs.googleusercontent.com/ https://spreadsheets.google.com/feeds/'));
        
//session_start();

if (isset($_GET['code']) || (isset($_SESSION['access_token5']) && $_SESSION['access_token5'])) {
if (isset($_GET['code'])) {
    header('Location: http://k-tehno.ru/admin/gd.php?k=rgE6fswSrn');
    $client->authenticate($_GET['code']);
    $_SESSION['access_token5'] = $client->getAccessToken();
} else{
    $client->setAccessToken($_SESSION['access_token5']);
}
    
  



function retrieveAllFiles($service) {
  $result = array();
  $pageToken = NULL;

  do {
    try {
      $parameters = array();
      if ($pageToken) {
        $parameters['pageToken'] = $pageToken;
      }
      $files = $service->files->listFiles($parameters);

      $result = array_merge($result, $files->getFiles());
      $pageToken = $files->getNextPageToken();
    } catch (Exception $e) {
      print "An error occurred: " . $e->getMessage();
      $pageToken = NULL;
    }
  } while ($pageToken);
  return $result;
}

$service = new Google_Service_Drive($client);
$a=retrieveAllFiles($service);

$data=array();
foreach($a as $key => $arr){
    
    if ($arr['mimeType']=='audio/3gpp'){
        $data[$key]=array();
        
        $data[$key]['id']=$arr['id'];
        $data[$key]['name']=$arr['name'];//2017_04_01_13_33_04_89264008493_89264008493.3gp
            $data[$key]['name']=str_replace(array(' ','.3gp'),'',$data[$key]['name']);
        $data[$key]['date']=mb_substr($data[$key]['name'],0,4,'UTF-8')
                    .'-'.mb_substr($data[$key]['name'],5,2,'UTF-8')
                    .'-'.mb_substr($data[$key]['name'],8,2,'UTF-8')
                    .' '.mb_substr($data[$key]['name'],11,2,'UTF-8')
                    .':'.mb_substr($data[$key]['name'],14,2,'UTF-8')
                    .':'.mb_substr($data[$key]['name'],17,2,'UTF-8');
        $data[$key]['phone']=mb_substr($data[$key]['name'],mb_strlen($data[$key]['name'],'UTF-8')-10,10,'UTF-8');
        if ($data[$key]['phone']!=''){$data[$key]['phone']='8'.$data[$key]['phone'];}
        
        echo '<br />'.$key .' = ';
        print_rf($data[$key]);
        echo '<br />';
    }
}





} else {
$authUrl = $client->createAuthUrl();
header('Location: ' . $authUrl);
exit();
}

?>