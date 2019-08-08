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
//ФОРМА контрагента ***********************************************
//***********************************************************************
// ************************************************************
// 
if ($_t=='i_contr'){
    $nomer=_GP('nomer');
    
    $photo_arr['id']=array();
    $photo_arr['img']=array();
    $photo_arr['tip']=array();
    $photo_arr['comments']=array();
    $myrow['chk_active']='1';
    $myrow['name']='';
    $myrow['email']='';
    $myrow['phone']='';
    $myrow['data_create']='';
    $myrow['data_change']='';
    $myrow['data_last_visit']='';
    $myrow['html_code']='';
    $myrow['adress']='';
    $myrow['link']='';
    $myrow['i_contr_id']='';
    $myrow['i_contr_id_name']='';
    $myrow['i_reklama_id']='';
    $myrow['i_reklama_name']='';
    $myrow['i_contr_org_id']='';
    
    //Проверяем на существование записи
    if ($nomer!=''){
        $sql = "SELECT COUNT(*)
        				FROM i_contr 
        					WHERE i_contr.id='"._DB($nomer)."'
        	"; 
        
        $res = mysql_query($sql);if (!$res){echo $sql;exit();}
        $myrow = mysql_fetch_array($res);
        if($myrow[0]>0){
            $sql = "SELECT  i_contr.chk_active,
                            i_contr.name,
                            i_contr.email,
                            i_contr.phone,
                            i_contr.data_create,
                            i_contr.data_change,
                            i_contr.data_last_visit,
                            i_contr.html_code,
                            i_contr.adress,
                            i_contr.link,
                            i_contr.i_contr_id,
                            (SELECT i2.name FROM i_contr AS i2  WHERE i2.id=i_contr.i_contr_id) AS i_contr_id_name,
                            i_contr.i_reklama_id,
                            (SELECT i_reklama.name FROM i_reklama WHERE i_reklama.id=i_contr.i_reklama_id) AS i_reklama_name,
                            i_contr.i_contr_org_id
                            
            				FROM i_contr 
            					WHERE i_contr.id='"._DB($nomer)."'
            	"; 
            
            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
            $myrow = mysql_fetch_array($res);
            //***********************************************************************************
            $sql_photo = "SELECT a_photo.id,a_photo.img, a_photo.tip, a_photo.comments
                        FROM a_photo, a_menu 
                            WHERE a_menu.inc='i_contr' 
                                AND a_menu.id=a_photo.a_menu_id 
                                AND a_photo.row_id='"._DB($nomer)."'
                                    ORDER BY sid
            ";
             
            $res_photo = mysql_query($sql_photo);if (!$res_photo){echo $sql_photo;exit();}
            //фото
            for ($myrow_photo = mysql_fetch_array($res_photo),$i=0; $myrow_photo==true; $myrow_photo = mysql_fetch_array($res_photo),$i++)
            {
                $photo_arr['id'][$i]=$myrow_photo['id'];
                $photo_arr['img'][$i]=$myrow_photo['img'];
                $photo_arr['tip'][$i]=$myrow_photo['tip'];
                $photo_arr['comments'][$i]=$myrow_photo['comments'];
            }
            
            if ($myrow['i_contr_org_id']>0){
                $sql_org = "SELECT  i_contr_org.name,
                                    i_contr_org.inn,
                                    i_contr_org.kpp,
                                    i_contr_org.ogrn,
                                    i_contr_org.bik,
                                    i_contr_org.bank,
                                    i_contr_org.schet,
                                    i_contr_org.kschet,
                                    i_contr_org.u_adress,
                                    i_contr_org.fio_director,
                                    i_contr_org.na_osnovanii
                                    
                				FROM i_contr_org 
                					WHERE i_contr_org.id='"._DB($myrow['i_contr_org_id'])."' 
                	"; 
                
                $res_org = mysql_query($sql_org);if (!$res_org){echo $sql_org;exit();}
                $myrow_org = mysql_fetch_array($res_org);
            }
            
        }else{
            echo 'Контрагента с номером '._DB($nomer).' не существует!';exit;
        }
    }
    
    ?>
    
    <table style="width: 100%;" class="ttable">
        <tr>
            <td>Активность</td>
            <td><input name="chk_active" type="checkbox" value="1" /></td>
        </tr>
        <tr class="mandat">
            <td>Фамилия Имя Отчество*</td>
            <td><input name="name" placeholder="ФИО" type="text" value="" /></td>
        </tr>
        <?php 
            if($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0'){//email
        ?>
        
            <tr class="mandat">
                <td>Email*</td>
                <td><input name="email" placeholder="Email" type="text" value="" /></td>
            </tr>
            <tr>
                <td>Телефон</td>
                <td><input name="phone" placeholder="Телефон" type="text" value="" /></td>
            </tr>
        <?php
        }else{//телефон
            ?>
            <tr class="mandat">
                <td>Телефон*</td>
                <td><input name="phone" placeholder="Телефон" type="text" value="" /></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input name="email" placeholder="Email" type="text" value="" /></td>
            </tr>
            <?php
            <?php
        }
        ?>
        <tr>
            <td>Пароль</td>
            <td><input name="password" placeholder="password" type="password" value="" /> <a target="_blank" href="http://<?=$_SERVER['SERVER_NAME'];?>/?com=m_cart&login=&password=" title="Войти в личный кабинет на сайте"><i class="fa fa-key"></i></a></td>
        </tr>
    </table>
    <p>Данная форма в процессе разработки...</p>
   
    <?php
    
}
//***********************************************************************
//Сохранение контрагента ***********************************************
//***********************************************************************
if ($_t=='i_contr_save'){
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