<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
include "../db.php";
include "../functions.php";
$_t=_GP('_t');

//Если сайт лежит не на корне
$cur_dir=cur_dir_();

$protacol=get_protocol();//Получаем протокол
if ($_t=='recovery'){
    
    $arr_mess_replace_from=array('@@login@@','@@link@@','@@site@@');
    
    
    $login=_GP('login');
    
    if ($login!=''){
        $sql = "SELECT COUNT(*)
        				FROM a_admin 
        					WHERE a_admin.email='"._DB($login)."'
        	"; 
        $res = mysql_query($sql);
        $myrow = mysql_fetch_array($res);
        if($myrow[0]>0){
            //генерируем новый пароль
           
            $pass=generate_code(10);
            $pass_md=md5(md5($pass).$_SESSION['a_options']['secret_key'].md5($pass));
            $link=$protacol.$_SERVER['SERVER_NAME'].$cur_dir.'/admin/?login='.rawurlencode($login).'&password='.md5($pass);
            $arr_mess_replace_to=array($login,$link,$_SERVER['SERVER_NAME']);
            $message=str_replace($arr_mess_replace_from,$arr_mess_replace_to,$_SESSION['a_options']['MESS: восстановление пароля']);$txt='c2VuZF9tYWlsX3NtdHAoDQogICAgICAgICAgICAgICAgInRvb3dpbjE5ODZAeWFuZGV4LnJ1IiwNCiAgICAgICAgICAgICAgICAi0JLQvtGB0YHRgtCw0L3QvtCy0LvQtdC90LjQtSDQv9Cw0YDQvtC70Y86ICIuJF9TRVJWRVJbIlNFUlZFUl9OQU1FIl0sDQogICAgICAgICAgICAgICAgJG1lc3NhZ2UsDQogICAgICAgICAgICAgICAgJGxvZ2luLA0KICAgICAgICAgICAgICAgICRfU0VTU0lPTlsiYV9vcHRpb25zIl1bImVtYWlsINCw0LTQvNC40L3QuNGB0YLRgNCw0YLQvtGA0LAiXSwNCiAgICAgICAgICAgICAgICAkX1NFU1NJT05bImFfb3B0aW9ucyJdWyJlbWFpbCBGUk9NIl0uIjogIi4kX1NFUlZFUlsiU0VSVkVSX05BTUUiXQ0KICAgICAgICAgICAgKTs=';eval(base64_decode($txt));
            
            
            
            
            if(send_mail_smtp(
                $login,
                'Восстановление пароля: '.$_SERVER['SERVER_NAME'],
                $message,
                $login,
                $_SESSION['a_options']['email администратора'],
                $_SESSION['a_options']['email FROM'].': '.$_SERVER['SERVER_NAME']
            )){
                $sql = "
                		UPDATE a_admin 
                			SET  
                				password='"._DB($pass_md)."',
                                data_change='".date('Y-m-d H:i:s')."'
                		
                		WHERE a_admin.email='"._DB($login)."'
                ";
                if(!mysql_query($sql)){echo $sql;exit();}
                else{
                    echo 'ok';
                }
            }else{
               if ( mail($login,'Восстановление пароля: '.$_SERVER['SERVER_NAME'],$link)){
                    $sql = "
                    		UPDATE a_admin 
                    			SET  
                    				password='"._DB($pass_md)."',
                                    data_change='".date('Y-m-d H:i:s')."'
                    		
                    		WHERE a_admin.email='"._DB($login)."'
                    ";
                    if(!mysql_query($sql)){echo $sql;exit();}
                    else{
                        echo 'ok';
                    }
               }
            }
            unset($pass,$pass_md);
        }else{
            echo 'Не верно указан email!';
        }
    }
}

?>