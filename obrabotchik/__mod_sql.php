<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 


if (_GP('_t')=='start'){
    $sql=_GP('sql');
    if (mb_strstr($sql,'select',false,'utf-8')==true or mb_strstr($sql,'SELECT',false,'utf-8')==true){
        
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        while ($row = mysql_fetch_array($res)) {
            print_rf($row);
        }
        
    }else{
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        print_rf($res);
    }
    
exit;
}
?>