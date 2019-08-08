<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 


    $sql_i_tp = "SELECT i_tp.id, 
                        i_tp.name,
                        
                        (SELECT a_admin.i_tp_id FROM a_admin WHERE a_admin.id='".$a_admin_id_cur."' LIMIT 1) AS i_tp_cur,
                        ''
    				FROM i_tp 
    					WHERE i_tp.chk_active='1'
    						ORDER BY i_tp.sid
    ";
     
    $mt = microtime(true);
    $res_i_tp = mysql_query($sql_i_tp) or die(mysql_error().'<br>'.$sql_i_tp);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_tp;$data_['_sql']['time'][]=$mt;
    $sel_i_tp='';$i=0;
    for ($myrow_i_tp = mysql_fetch_array($res_i_tp); $myrow_i_tp==true; $myrow_i_tp = mysql_fetch_array($res_i_tp))
    {
        $sel_cur=''; if ($myrow_i_tp[0]==$myrow_i_tp[2]){$sel_cur=' active';}
        $sel_i_tp.='
        <div class="i_tp_s_cat_price_change_div'.$sel_cur.'">
            <div>Цена в филиале <strong>'.$myrow_i_tp[1].'</strong>: </div>
            <div><input type="number" class="i_tp_s_cat_price" name="i_tp_s_cat_price_'.$myrow_i_tp[0].'" placeholder="Цена в филиале '._IN($myrow_i_tp[1]).'" /></div>
        </div>';
        $i++;
    }
    ?>
    <?=$sel_i_tp;?>
    
    <?php
    if ($i>1){
        ?>
        <div class="i_tp_s_cat_price_change_com_div"><span class="btn_gray i_tp_s_cat_price_change_open">Показать цены во всех филиалах</span></div>
        <?php
    }
    
?>

