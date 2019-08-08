<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');

include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


    $_t=_GP('_t');
    // ************************************************************
    // Автозаполнение свойств
    
    if ($_t=='find'){

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        $WHERE='';
        
        $id=_GP('id');
        $id_arr=array();
        if (strstr($id,',')==true){
            $id_arr=explode(',',$id);
        }else{
            if ($id!=''){
                $id_arr[0]=$id;
            }
        }
        
        $sql_in='';
        if (count($id_arr)>0){
            $sql_in=" AND r_model.id NOT IN ('".implode("','",$id_arr)."')";
        }
        
        $data_=array();
        
        $_find_txt=_GP('term');
        if ($_find_txt!=''){
            if (strstr($_find_txt,' ')==true){//несколько слов
                $_find_txt_arr=explode(' ',$_find_txt);
                foreach($_find_txt_arr as $key => $_find_txt){
                    $WHERE.="\n AND (r_model.name LIKE '%"._DB($_find_txt)."%' OR r_brend.name LIKE '%"._DB($_find_txt)."%' ) \n";
                }
            }
            else{ //одно слово
                $WHERE.="\n AND (r_model.name LIKE '%"._DB($_find_txt)."%' OR r_brend.name LIKE '%"._DB($_find_txt)."%' ) \n";
            }
        }
        
        
        $sql_connect = "SELECT r_tip_oborud.name, r_brend.name,  r_model.name, r_model.id
        				FROM r_tip_oborud,r_brend,r_model
                            WHERE r_model.r_tip_oborud_id=r_tip_oborud.id
                            AND r_model.r_brend_id=r_brend.id
                            $WHERE
                            $sql_in
        					ORDER BY r_model.name
                           LIMIT 100
        "; 
        $mt = microtime(true);
        $res_connect = mysql_query($sql_connect) or die(mysql_error());
        $ii=0;
        for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
        {
            $data_[$ii]['label']=$myrow_connect[0].' '.$myrow_connect[1]. ' '.$myrow_connect[2];
            $data_[$ii]['value']='';
            $data_[$ii]['id']=$myrow_connect[3];
            $ii++;
        }
        
        echo json_encode($data_);
    } 
    
    
    
}
?>