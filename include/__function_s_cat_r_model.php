<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>

        <input type="text" value="" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" class="<?=$data_['col'][$myrow[0]];?>" style="width:1000px;" />
        <input type="hidden" name="r_model" />
        <p> <span>Для массового выбора наведите на нужный пункт с нажатой кнопкой Ctrl</span> <span class="r_model_clear_all_model"><i class="fa fa-remove" title="Убрать все модели"></i></span></p>
        <div class="r_model__all"></div>
        
        <div class="<?=$data_['col'][$myrow[0]];?>_res">
        </div>
        <div class="clear"></div>