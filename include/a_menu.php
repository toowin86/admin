<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
     
    $i_post_arr=array();
    $sql = "SELECT i_post.id, i_post.name
    				FROM i_post
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $i_post_arr[$myrow[0]]=$myrow[1];
    }
     
?>
<!-- Верхнее меню --> 
<div class="top_com">
    <ul>
    <?php
    //************ ДОБАВЛЕНИЕ *************
    foreach ($a_com_arr['com'] as $a_com_id => $com_){
        if ($com_=='add'){
            if (isset($a_admin_a_com_arr[$a_com_id])){
            ?>
                <li><div class="top_com___<?=$com_;?>" title="<?=$a_com_arr['name'][$a_com_id];?>"></div></li>
            <?php
            }
        }
    }
    ?>
    
    <?php
        //************ ВЫГРУЗКА *************
        foreach ($a_com_arr['com'] as $a_com_id => $com_){
            if ($com_=='change'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
        ?>
            <li><a class="__a_menu__copy_db" title="Выгрузить базу данных на Яндекс-диск" target="_blank" href="?com=copy_db&key=<?=@$_SESSION['a_options']['secret_key'];?>" ></a></li>
        <?php
                }
            }
        }
    ?>
    </ul>
</div>
<div class="main_block">
    <div class="page_name_block">
        <h1 class="page_name">
            <?php 
                if (isset($a_menu_arr['name'][$inc_id]) and $a_menu_arr['name'][$inc_id]!=''){
                    echo $a_menu_arr['name'][$inc_id];
                }
            ?>
        </h1>
    </div>
    <div class="admin_content_block">
    <div class="a_com_block">
        <div class="a_com_main">
            <span class="a_com_span_text">Функции:</span>
            
            <?php
                //************ ДОБАВЛЕНИЕ *************
                foreach ($a_com_arr['com'] as $a_com_id => $com_){
                    if ($com_=='add'){
                        if (isset($a_admin_a_com_arr[$a_com_id])){
                ?>
                    <span class="ico ico_<?=$com_;?> a_com_<?=$com_;?>" title="<?=$a_com_arr['name'][$a_com_id];?> функцию"></span>
                <?php
                        }
                    }
                }
            ?>
            
            
            <select name="a_com_select" data-placeholder="Укажите пункт меню">
                <option></option>
                <?php
                $sql = "SELECT      a_com.id, 
                                    a_com.name, 
                                    (SELECT GROUP_CONCAT(a_menu_a_com.id1 SEPARATOR ',') FROM a_menu_a_com WHERE a_menu_a_com.id2=a_com.id)
                                    
                				FROM a_com
                                    ORDER BY a_com.name
                "; 
                $res = mysql_query($sql);
                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                {
                    ?>
                    <option data-a_menu_a_com="<?=$myrow[2];?>" value="<?=$myrow[0];?>"><?=$myrow[1];?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="a_com_options" style="display: none;">
        
        
        <?php
            //************ УДАЛЕНИЕ *************
            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                if ($com_=='del'){
                    if (isset($a_admin_a_com_arr[$a_com_id])){
            ?>
                <span class="ico ico_<?=$com_;?> a_com_<?=$com_;?>" title="<?=$a_com_arr['name'][$a_com_id];?> функцию"></span>
            <?php
                    }
                }
            }
        ?>   
        <?php
            //************ ИЗМЕНЕНИЕ *************
            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                if ($com_=='change'){
                    if (isset($a_admin_a_com_arr[$a_com_id])){
            ?>
                <span class="ico ico_menu_orange a_com_menu" title="Подключить к меню"></span>
            <?php
                    }
                }
            }
        ?>   
        </div>
        
    </div>
    <div style="clear: both;"></div>
    <hr />
    <div class="ttable2 page_res_tree_table">
        <div class="ttable2_tbody_tr">
            <!-- Администраторы -->
            <div class="ttable2_tbody_tr_td">
                <div>
                    <?php
                        //************ ДОБАВЛЕНИЕ *************
                        foreach ($a_com_arr['com'] as $a_com_id => $com_){
                            if ($com_=='add'){
                                if (isset($a_admin_a_com_arr[$a_com_id])){
                        ?>
                       <span class="btn_orange admins_new_user"><span class="ico ico_login"></span> Новый пользователь</span>
                       <?php
                                }
                            }
                        }
                    ?>
                    
                    <?php
                        $sql_admin = "SELECT   COUNT(*)
                                				FROM a_admin 
                                                    WHERE a_admin.chk_active='0'
                        "; 
                        $res_admin = mysql_query($sql_admin);
                        $myrow_admin = mysql_fetch_array($res_admin);
                        if($myrow_admin[0]>0){
                    ?>
                    <span class="btn_gray admins_all_view"><span class="ico ico_view"></span>Показать не активных</span>
                    <?php
                        }
                    ?>
                </div>
                <div class="admins_change_block">
                    <ul class="admins_change_ul">
                <?php
                
                $sql_admin = "SELECT    a_admin.id,
                                        a_admin.chk_active,
                                        a_admin.name,
                                        a_admin.phone,
                                        a_admin.email,
                                        a_admin.comments,
                                        (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo, a_menu WHERE a_photo.a_menu_id=a_menu.id AND a_menu.inc='a_admin' AND a_photo.row_id=a_admin.id AND a_photo.tip='Основное' LIMIT 1) AS img,
                                        a_admin.data_create,
                                        (SELECT IF(COUNT(*)>0,GROUP_CONCAT(a_admin_i_post.id2 SEPARATOR ','),'') FROM a_admin_i_post WHERE a_admin_i_post.id1=a_admin.id) AS a_admin_i_post_id,
                                        a_admin.i_tp_id,
                                        a_admin.chk_view_all_s_cat
                                
                				FROM a_admin 
                					ORDER BY a_admin.chk_active DESC, a_admin.data_change DESC
                "; 
                $res_admin = mysql_query($sql_admin);
                for ($myrow_admin = mysql_fetch_array($res_admin); $myrow_admin==true; $myrow_admin = mysql_fetch_array($res_admin))
                {
                    //print_rf($myrow_admin);
                    //получаем разрешенные пункты меню
                    $inc_txt='';
                    $sql = "SELECT id2 
                    				FROM a_admin_a_menu 
                    					WHERE id1='"._DB($myrow_admin[0])."'
                    "; 
                    $res = mysql_query($sql);
                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                    {
                        if ($inc_txt!=''){$inc_txt.=',';}
                        $inc_txt.=$myrow[0];
                    }
                    
                    //получаем разрешенные к отображению столбцы
                    $col_txt='';
                    $sql = "SELECT id2 
                    				FROM a_admin_a_col
                    					WHERE id1='"._DB($myrow_admin[0])."'
                    "; 
                    $res = mysql_query($sql);
                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                    {
                        if ($col_txt!=''){$col_txt.=',';}
                        $col_txt.=$myrow[0];
                    }
                    //получаем разрешенные к функциям
                    $com_txt='';
                    $sql = "SELECT a_menu_a_com.id1, a_menu_a_com.id2
                    				FROM a_admin_a_menu_a_com, a_menu_a_com
                    					WHERE a_admin_a_menu_a_com.id1='"._DB($myrow_admin[0])."'
                                        AND a_admin_a_menu_a_com.id2=a_menu_a_com.id
                    "; 
                    $res = mysql_query($sql) or die(mysql_error()); //if (!$res){echo $sql;}
                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                    {
                        if ($com_txt!=''){$com_txt.=',';}
                        $com_txt.=$myrow[0].':'.$myrow[1];
                    }
                    
                    
                    $img_='../i/a_admin/original/'.$myrow_admin['img'];
                    if ($myrow_admin['img']=='' or !file_exists($img_)){
                        $img_='';
                    }
                    if ($img_!=''){$img_='style="background: url('.$img_.'?'.rand(1000,9999).') no-repeat center center; background-size:contain;"';}
                    else{$img_=' style="background: url(i/no_user.png) no-repeat; background-size:contain;"';}
                    
                    $cl_active=' style="display:none;"';$chk_='';if ($myrow_admin['chk_active']=='1'){$chk_=' checked="checked"';$cl_active='';}
                    
                    ?>
                    <li <?=$cl_active;?> data-inc="<?=$inc_txt;?>" data-col="<?=$col_txt;?>" data-com="<?=$com_txt;?>" data-id="<?=$myrow_admin['id'];?>">
                        <div class="ttable2 admins_change_table">
                            <div class="ttable2_tbody_tr">
                                <div class="ttable2_tbody_tr_td">
                                    <div class="admins_change_td_chk_active">
                                        <input type="checkbox" value="1" data-col="chk_active"<?=$chk_;?> <?php
                                            //************ ИЗМЕНЕНИЕ *************
                                            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                                if ($com_=='change'){
                                                    if (isset($a_admin_a_com_arr[$a_com_id])){
                                            ?>
                                                    
                                           <?php
                                                    }
                                                    else{
                                                        echo ' name="chk_active'.$myrow_admin['id'].'"';
                                                    }
                                                }
                                            }
                                            ?> />
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr_td admins_change_td_img" id="a_admin_img_<?=$myrow_admin[0];?>" rowspan="3" <?=$img_;?>></div>
                                <div class="ttable2_tbody_tr_td">
                                    <div class="admins_change_td_name"><span <?php
                                        //************ ИЗМЕНЕНИЕ *************
                                        foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                            if ($com_=='change'){
                                                if (isset($a_admin_a_com_arr[$a_com_id])){
                                        ?>
                                                class="a_admin_edit_span"
                                       <?php
                                                }
                                            }
                                        }
                                        ?> data-col="name"><?=$myrow_admin['name'];?></span>
                                    </div>
                                       
                                    <div class="admins_change_td_phone">
                                       <span <?php
                                        //************ ИЗМЕНЕНИЕ *************
                                        foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                            if ($com_=='change'){
                                                if (isset($a_admin_a_com_arr[$a_com_id])){
                                        ?>
                                       class="a_admin_edit_span"
                                       <?php
                                                }
                                            }
                                        }
                                        ?> data-col="phone"><?php if($myrow_admin['phone']!=''){echo conv_('phone_from_db',$myrow_admin['phone']);}?></span>
                                    </div>
                                        
                                        
                                    <div class="admins_change_td_email">
                                            <span <?php
                                            //************ ИЗМЕНЕНИЕ *************
                                            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                                if ($com_=='change'){
                                                    if (isset($a_admin_a_com_arr[$a_com_id])){
                                            ?>
                                           class="a_admin_edit_span"
                                           <?php
                                                    }
                                                }
                                            }
                                            ?>  data-col="email"><?=$myrow_admin['email'];?></span>
                                                
                                    </div>
                                    
                                      <div class="a_menu_a_admin_i_tp">
                                        <p>Филиал:</p>
                                        
                                            <?php
                                            //************ ИЗМЕНЕНИЕ *************
                                            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                                if ($com_=='change'){
                                                    
                                                    $i_tp_id=$myrow_admin['i_tp_id'];
                                                    if (isset($a_admin_a_com_arr[$a_com_id])){
                                                        ?>
                                                        <select name="a_admin_i_tp_id">
                                                        <?php
                                                        
                                                        $sql_i_tp = "SELECT i_tp.id, i_tp.chk_active, i_tp.name
                                                            				FROM i_tp
                                                         ";
                                                        $res_i_tp = mysql_query($sql_i_tp) or die(mysql_error().'<br/>'.$sql_i_tp);
                                                        for ($myrow_i_tp = mysql_fetch_array($res_i_tp); $myrow_i_tp==true; $myrow_i_tp = mysql_fetch_array($res_i_tp))
                                                        {
                                                            $dis_='';if ($myrow_i_tp['chk_active']=='0'){ $dis_=' disabled="disabled"';}
                                                            $sel_=''; if($myrow_admin['i_tp_id']==$myrow_i_tp['id']){$sel_=' selected="selected"';}
                                                            ?>
                                                            <option value="<?=$myrow_i_tp['id'];?>"<?=$sel_.$dis_;?>><?=$myrow_i_tp['name'];?></option>
                                                            <?php
                                                        }
                                                        
                                                        ?>
                                                        </select>
                                                        <?php
                                                        
                                                    }else{
                                                        //Просмотр должностей
                                                        $jj=0;
                                                        foreach ($i_post_arr as $i_post_id => $i_post_name)
                                                        {
                                                            if (isset($a_admin_i_post_arr) and in_array($i_post_id,$a_admin_i_post_arr)){
                                                                if ($jj>0){echo ', ';}
                                                                echo $i_post_name;
                                                                $jj++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                               
                                    </div>
                                        
                                </div>
                                <div class="ttable2_tbody_tr_td ">
                                    <div class="admins_change_td_del">
                                    <?php
                                            //************ УДАЛЕНИЕ *************
                                            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                                if ($com_=='del'){
                                                    if (isset($a_admin_a_com_arr[$a_com_id])){
                                            ?>
                                                     <span class="btn_gray a_admin_a_menu_del"><span class="ico ico_del"></span> Удалить</span>
                                           <?php
                                                    }
                                                }
                                            }
                                            ?>
                                    </div>
                                    <div class="admins_change_td_a_menu"><?php
                                            //************ ИЗМЕНЕНИЕ *************
                                            foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                                if ($com_=='change'){
                                                    if (isset($a_admin_a_com_arr[$a_com_id])){
                                            ?>
                                                   <span class="btn_orange a_admin_a_menu_getinfo"><span class="ico ico_menu"></span> Права доступа</span>
                                           <?php
                                                    }
                                                }
                                            }
                                            ?> 
                                    </div>
                                     
                                     <!-- Должности -->
                                    <div class="a_menu_a_admin_post">
                 
                                        <div class="set_i_post_div"><span class="btn_gray set_i_post"><i class="fa fa-users"></i> Должности</span></div> 
                                        <div style="clear: both;"></div>
                                    </div>
                                    
                                </div>
                            </div>
                            
                        </div>
                          
                                  
                        
                        
                        <div class="chk_view_all_s_cat">
                                        
                        <?php
                        $chk_='';$chk_txt='Просмотр только своего товара';
                        if ($myrow_admin['chk_view_all_s_cat']=='1'){
                            $chk_txt='Просмотр всего товара';
                            $chk_=' checked="checked"';
                        }
                        
                        //************ ИЗМЕНЕНИЕ *************
                        foreach ($a_com_arr['com'] as $a_com_id => $com_){
                            if ($com_=='change'){
                                if (isset($a_admin_a_com_arr[$a_com_id])){
                        ?>
                                <input type="checkbox" class="chk_view_all_s_cat" value="<?=$myrow_admin[0];?>"<?=$chk_;?> id="chk_view_all_s_cat_<?=$myrow_admin[0];?>" /> 
                       <?php
                                }
                            }
                        }

                        ?>
                            <label for="chk_view_all_s_cat_<?=$myrow_admin[0];?>"><?=$chk_txt;?></label>
                        </div>
                        
                    </li>
                    <?php
                }
                
                ?>
                    </ul>
                </div>
            </div>
            <!-- МЕНЮ -->
            <div class="ttable2_tbody_tr_td" >
            <div class="a_menu_info">МЕНЮ админ-панели:</div>
            <div class="page_res">
            <?php
            
            function tree_txt($a_menu_arr,$pid_start=0){
                global $a_com_arr, $a_admin_a_com_arr;
                $res='';
                if (isset($a_menu_arr['pid']) and count($a_menu_arr['pid'])>0){
                    foreach($a_menu_arr['pid'] as $id => $pid){
                        if ($pid==$pid_start){
                            if ($pid_start!=0){$res.="\t\t";}
                            $chk='';if ($a_menu_arr['chk_active'][$id]=='1'){$chk=' checked="checked"';}
                            
                            
                                        //************ ДОБАВЛЕНИЕ *************
                                        $info='';
                                        foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                            if ($com_=='change'){
                                                if (isset($a_admin_a_com_arr[$a_com_id])){
                                                    $info='<div class="a_menu_add_info ico_info ico"></div><div class="a_menu_block_get_info ico_down ico"></div>';
                                                    $info.='<a target="_blank" href="?com=copy_db&inc_to_db='.$a_menu_arr['inc'][$id].'&key='.@$_SESSION['a_options']['secret_key'].'" class="ico_change_db ico" title="Загрузить таблицу на яндекс-диск"></a>';
                                                }
                                            }
                                        }
                                        $del='';
                                        //************ УДАЛЕНИЕ *************
                                        foreach ($a_com_arr['com'] as $a_com_id => $com_){
                                            if ($com_=='del'){
                                                if (isset($a_admin_a_com_arr[$a_com_id])){
                                                    if ($a_menu_arr['chk_block'][$id]=='0'){$del='<div class="a_menu_block_del ico_del ico"></div>';}
                                                }
                                            }
                                        }
                            
                            
                            $chk_active_='';if ($a_menu_arr['chk_block'][$id]=='0'){$chk_active_='<input type="checkbox" value="1" class="a_menu_chk_active" '.$chk.'/>';}
                             
                            $res.='<li data-id="'.$id.'">
                                        <div class="a_menu_block">
                                            <div class="ttable2">
                                                <div class="ttable2_tbody_tr">
                                                    <div class="ttable2_tbody_tr_td a_menu_get_info a_menu_get_info_div">
                                                        '.$chk_active_.'
                                                        <span class="a_menu_name_edit"><span>'.$a_menu_arr['name'][$id].'</span></span>
                                                        <span> (<span class="a_menu_inc_name">'.$a_menu_arr['inc'][$id].'</span>) </span>
                                                    </div>
                                                    <div class="ttable2_tbody_tr_td a_menu_a_com_block">
                                                        
                                                    </div>
                                                    <div class="ttable2_tbody_tr_td a_menu_com_td">'.$info.$del.'</div>
                                                </div>
                                            </div>
                                            <div class="a_menu_all_info"></div>
                                        </div>';
                            $txt=tree_txt($a_menu_arr,$id);
                            if ($txt!='') {$res.="\n\t\t".'<ol>'."\n".$txt."\t\t".'</ol>'."\n";}
                            $res.='</li>'."\n";
                        }
                    }
                }
                return $res;
            }
            
            echo '<ol class="sortable">'.tree_txt($a_menu_arr).'</ol>';
            ?>
            
            
            </div>
            </div>
    </div>
    </div>
    </div>
</div>

<!-- Скрытый DIV пред-загрузки -->
<div class="preload" style="display: none;">
    <!-- ТИП СТОЛБЦОВ -->
    <select class="tip_col_div">
    <?php
    $sql_="SELECT `COLUMN_DEFAULT` 
                FROM `information_schema`.`COLUMNS` 
                    WHERE `TABLE_SCHEMA`='"._DB($base_name)."'                    
                    AND `TABLE_NAME`='a_col' 
                    AND `COLUMN_NAME`='tip'
     "; 
	$res_def = mysql_query($sql_);
	$myrow_def = mysql_fetch_array($res_def);
	$column_def=$myrow_def[0];
    
    $sql_="SELECT `COLUMN_TYPE` 
                FROM `information_schema`.`COLUMNS` 
                    WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                    AND `TABLE_NAME`='a_col' 
                    AND `COLUMN_NAME`='tip'
                
                "; 
	$res_enum = mysql_query($sql_);
	$myrow_enum = mysql_fetch_array($res_enum);
	$variants = explode ("','", substr($myrow_enum[0], 5, -1));
	foreach ($variants as &$variant){
        $def='';if ($column_def==trim($variant, "'")){$def=' data-def="1"';}
	    ?>
        <option value="<?=_IN(trim($variant, "'"));?>"<?=$def;?>><?=_IN(trim($variant, "'"));?></option>
        <?php
	}
    ?>
    </select>
    <select class="inc_div">
        <option></option>
    <?php
    foreach($a_menu_arr['name'] as $id => $name){
        ?>
        <option value="<?=$id;?>"><?=$name;?></option>
        <?php
    }
    ?>
    </select>
    <?php
    
    ?>
</div>
<div class="preload2" style="display: none;">
    <!-- ДОЛЖНОСТИ -->
    <select class="i_post">
    <?php
    $sql = "SELECT i_post.id, i_post.name
    				FROM i_post 
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
    
        ?>
        <option value="<?=$myrow['id'];?>"><?=$myrow['name'];?></option>
        <?php
    }
    ?>
    </select>
    
    <!-- ФИЛИАЛЫ -->
    <select class="i_tp">
    <?php
    $sql = "SELECT i_tp.id, i_tp.name
    				FROM i_tp 
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
    
        ?>
        <option value="<?=$myrow['id'];?>"><?=$myrow['name'];?></option>
        <?php
    }
    ?>
    </select>
    <?php
    
    ?>
</div>