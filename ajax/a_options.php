<?php
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
include "../db.php";
include "../functions.php";
if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


$_t=_GP('_t');
// ************************************************************
// 
if ($_t=='add_new_a_options'){
    $name=_GP('name');
    $val=_GP('val');
    $tip=_GP('tip');
    
    $sql = "SELECT COUNT(*)
    				FROM a_options 
    					WHERE a_options.name='"._DB($name)."'
    	"; 
    $res = mysql_query($sql) or die(mysql_error());
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]>0){
        echo 'more_one';
    }
    else{
        $sql = "INSERT into a_options (
        				name,
        				val,
                        tip
        			) VALUES (
        				'"._DB($name)."',
        				'"._DB($val)."',
        				'"._DB($tip)."'
        )";
        if (!mysql_query($sql)){echo $sql;$new_id=0;}
        else{
            $new_id = mysql_insert_id();
            $data_['id']=$new_id;
            echo json_encode($data_);
        }
        
        
    }
    

}

// ************************************************************
// 
if ($_t=='a_options_get_html'){
    $id=_GP('id');
    $sql = "SELECT val
    				FROM a_options 
    					WHERE a_options.id='"._DB($id)."'
    	"; 
    $res = mysql_query($sql) or die(mysql_error());
    $myrow = mysql_fetch_array($res);
    $data_['html_']=$myrow[0];
    echo json_encode($data_);

}
// ************************************************************
// 
if ($_t=='a_options_save'){
    $data_=array();
    $id=_GP('id');
    $col=_GP('col');
    $data_['val']=_GP('val');
    
    //Обновлеение
    $sql = "
    		UPDATE a_options 
    			SET  
    				"._DB($col)."='"._DB($data_['val'])."'
    		
    		WHERE id='"._DB($id)."'
    ";
    if(!mysql_query($sql)){echo $sql;exit();}
    else{$data_['sql'][]=$sql;} # *** DEBUG
    
    $sql = "SELECT a_options.tip
    				FROM a_options
    					WHERE a_options.id='"._DB($id)."'
    	"; 
    $res = mysql_query($sql); 
        if (!$res){echo $sql;exit();} 
        else{$data_['sql'][]=$sql;}# *** DEBUG
        
    $myrow = mysql_fetch_array($res);
    $tip=$myrow[0];
    
    if ($tip=='HTML-код'){
        $data_['val']=substr(strip_tags($data_['val']),0,500);
    }
    
    echo json_encode($data_);
}



//************************************************************************************************** 
}else{
    echo 'Ошибка авторизации!';
}
?>