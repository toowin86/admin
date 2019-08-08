<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<div class="i_tp_i_social_network_div">
<?php
     $sql_i_social = "SELECT i_social_network.id,i_social_network.name
     				FROM i_social_network 
     					WHERE i_social_network.chk_active='1'
     						ORDER BY i_social_network.sid
     ";
      
     $mt = microtime(true);
     $res_i_social = mysql_query($sql_i_social) or die(mysql_error().'<br>'.$sql_i_social);
     $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_social;$data_['_sql']['time'][]=$mt;
     
     for ($myrow_i_social = mysql_fetch_array($res_i_social); $myrow_i_social==true; $myrow_i_social = mysql_fetch_array($res_i_social))
     {
        ?>
        <div class="i_tp_i_social_network_item">
            <label for="i_tp_i_social_network_id_<?=$myrow_i_social[0];?>"><?=$myrow_i_social[1];?></label> <input id="i_tp_i_social_network_id_<?=$myrow_i_social[0];?>" name="i_tp_i_social_network_id_<?=$myrow_i_social[0];?>" placeholder="<?=_IN($myrow_i_social[1]);?>" type="text" />
        </div>
        <?php
     }
     
     ?>
     <p><a href="?inc=i_social_network" target="_blank">Изменение соц.сетей</a></p>
</div>