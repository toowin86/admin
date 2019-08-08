<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
    
    $prop_id_arr=array();
    $prop_name_arr=array();
    $prop_tip_arr=array();
    $prop_data_tip_arr=array();
    
    $sql_prop = "SELECT id, name, tip, data_tip
    				FROM s_prop 
    					WHERE chk_active='1'
    						ORDER BY s_prop.sid
    "; 
    $res_prop = mysql_query($sql_prop) or die(mysql_error());
    for ($myrow_prop = mysql_fetch_array($res_prop),$i_prop=0; $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop),$i_prop++)
    {
        $prop_id_arr[$i_prop]=$myrow_prop['id'];
        $prop_name_arr[$i_prop]=$myrow_prop['name'];
        $prop_tip_arr[$i_prop]=$myrow_prop['tip'];
        $prop_data_tip_arr[$i_prop]=$myrow_prop['data_tip'];
    }
?>

<div class="__function_s_cat_prop">
    <div>
        <span class="__function_s_cat_prop__add_new_prop btn_orange"><i class="fa fa-plus"></i> Добавить свойство</span>
        <select class="s_prop_view" data-placeholder="Отобразить свойство">
            <option></option>
            <?php
            foreach($prop_id_arr as $i_prop => $prop_id){
                ?>
                <option value="<?=$prop_id;?>"><?=$prop_name_arr[$i_prop];?></option>
                <?php
            }
            ?>
        </select>
    </div>
    
    <div class="ttable __function_s_cat_prop__res">
    <?php
    
    foreach($prop_id_arr as $i_prop => $prop_id)
    {
        ?>
        <div class="ttable_tbody_tr" data-id="<?=$prop_id;?>" data-tip="<?=$prop_tip_arr[$i_prop];?>" data-data_tip="<?=$prop_data_tip_arr[$i_prop];?>">
            <div class="ttable_tbody_tr_td __function_s_cat_prop__name"><?=$prop_name_arr[$i_prop];?></div>
            <div class="ttable_tbody_tr_td __function_s_cat_prop__val"><?php
            
            if ($prop_tip_arr[$i_prop]=='Список'){
                
            
            ?>
                <select name="__function_s_cat_prop__select" class="__function_s_cat_prop__select" data-placeholder="<?=$prop_name_arr[$i_prop];?>" multiple>
                    
                    <?php 
                    
                    $sql_prop_val = "SELECT s_prop_val.id, s_prop_val.val, (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE s_prop_val.id=a_photo.row_id AND a_photo.a_menu_id='24' ORDER BY a_photo.sid LIMIT 1) AS photo 
                    				FROM s_prop_val 
                    					WHERE s_prop_val.s_prop_id='"._DB($prop_id)."' 
                    						ORDER BY s_prop_val.val
                    "; 
                    $res_prop_val = mysql_query($sql_prop_val) or die(mysql_error());
                    for ($myrow_prop_val = mysql_fetch_array($res_prop_val); $myrow_prop_val==true; $myrow_prop_val = mysql_fetch_array($res_prop_val))
                    {
                        $img_='../i/s_prop_val/original/'.$myrow_prop_val['photo'];
                        if (!file_exists($img_) or $myrow_prop_val['photo']==''){
                            $img_='';
                        }
                        
                        ?>
                        <option data-img="<?=_IN($img_);?>" value="<?=$myrow_prop_val[0];?>"><?=$myrow_prop_val[1];?></option>
                        <?php
                    }
                    
                    ?>
                </select>
            <?php
            
            }
            if ($prop_tip_arr[$i_prop]=='Авто добавление'){
             ?>
                <input type="text" name="__function_s_cat_prop__input<?=$prop_id;?>" class="__function_s_cat_prop__input" placeholder="<?=$myrow_prop[1];?>" />
                
              <?php
            }
            ?>
            </div>
            <div class="ttable_tbody_tr_td __function_s_cat_prop__com_prop">
                <?php
               
                if ($prop_tip_arr[$i_prop]=='Список'){
                ?>
                <span class="ico ico_add __function_s_cat_prop__add_val" title="Добавить значение свойства"></span>
                <?php   
                }
                ?>
                <span class="fa fa-minus __function_s_cat_prop__noview" title="Скрыть свойство"></span>
                <!--<span class="ico ico_del __function_s_cat_prop__del" title="Удалить свойство"></span>-->
            </div>
        </div>
        <?php
    }
    
    ?>
    
    </div>
    
</div>