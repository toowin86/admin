<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
?>
<div class="top_com">
    <ul>
        <li style="display: none;"><div class="top_com__m_zakaz_create" title="Инвентаризация -> списать товары"></div></li>
        
        
    </ul>
</div>
<div class="m_tovar_main_div">
    <div class="m_tovar_fillter">
        <h1>Склад</h1>
        <form action="#">
            <div class="m_zakaz_s_cat_m_tovar_fillter__tbl">
                <div class="m_zakaz_s_cat_m_tovar_fillter__open m_zakaz_s_cat_m_tovar_fillter__tbl_td">
                    <span class="m_zakaz_s_cat_m_tovar_fillter__open_btn btn_gray">Загрузить товар</span>
                </div>
                <div class="m_zakaz_s_cat_m_tovar_fillter__open m_zakaz_s_cat_m_tovar_fillter__tbl_td">
                    <span class="m_zakaz_s_cat_m_tovar_fillter__remove_btn btn_gray">Скрыть пустые</span>
                </div>
                <div class="m_zakaz_s_cat_m_tovar_fillter__status m_zakaz_s_cat_m_tovar_fillter__tbl_td">
                    <ul>
                        <li data-val="В наличии" class="status_li_1 active"><span><i class="fa fa-check-square-o"></i> В наличии <span></span></span></li>
                        <li data-val="Продан" class="status_li_2"><span><i class="fa fa-square-o"></i> Продан <span></span></span></li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <div class="m_zakaz_s_cat_m_tovar_fillter__i_contr_postav m_zakaz_s_cat_m_tovar_fillter__tbl_td">
                    <p>Поставщик:</p>
                    <select name="m_zakaz_s_cat_m_tovar_i_contr_postav" data-placeholder="Поставщик"></select>
                </div>
                <div class="m_zakaz_s_cat_m_tovar_fillter__i_contr_pokup m_zakaz_s_cat_m_tovar_fillter__tbl_td">
                    <p>Покупатель:</p>
                    <select name="m_zakaz_s_cat_m_tovar_i_contr_pokup" data-placeholder="Покупатель"></select>
                </div>
                <div class="m_zakaz_s_cat_m_tovar_fillter__i_contr_text m_zakaz_s_cat_m_tovar_fillter__tbl_td">
                    <p>Поиск по товару:</p>
                    <input type="text" class="m_zakaz_s_cat_m_tovar_fillter__term" placeholder="Поиск по товарам" />
                </div>
            </div>
        </form>
    </div>
    <div class="m_tovar_res"></div>

</div>