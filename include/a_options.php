<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
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
    <div style="clear: both;"></div>
    <div class="admin_content_block">
        <table class="ttable a_options_table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Значение</th>
                </tr>
            </thead>
            <tbody>
            <?php
            
            $sql = "SELECT id, name, val, tip
            				FROM a_options
            "; 
            $res = mysql_query($sql);
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $id     =$myrow[0];
                $name   =$myrow[1];
                $val    =$myrow[2];
                $tip    =$myrow[3];
                ?>
                    <tr data-id="<?=$id;?>" data-tip="<?=$tip;?>">
                        <td>
                            <span class="options_name"><?=$name;?></span>
                        </td>
                        <td style="text-align: left;">
                <?php
                
                
                if ($tip=='Текст'){
                    echo '<span class="options_val options_val_text">'.$val.'</span>';
                }
                elseif ($tip=='HTML-код'){
                    echo '<span class="options_val options_val_html">'.substr(strip_tags($val),0,500).'</span>';
                }
                elseif ($tip=='Дата-время'){
                    echo '<span class="options_val options_val_data">'.$val.'</span>';
                }
                elseif ($tip=='Input'){
                    $chk_='';if ($val=='1'){$chk_='checked="checked"';}
                    echo '<span class="options_val options_val_input"><input type="checkbox" '.$chk_.' /></span>';
                }
                ?>
                        </td>
                    </tr>
                <?php 
                
            }
            ?>
            
            </tbody>
        </table>
    </div>
</div>

<!-- Скрытый DIV пред-загрузки -->
<div class="preload" style="display: none;">
    
</div>
