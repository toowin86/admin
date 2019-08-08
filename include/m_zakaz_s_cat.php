<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
  
?>
<div class="m_zakaz_s_cat_block">
    <h1>Панель доставок</h1>
    <!-- Фильтр -->
    <div class="m_zakaz_s_cat_fillter">
        <form>
            <div>
                <p>Дата доставки</p>
                <div class="m_zakaz_s_cat_fillter_data">
                    <input type="text" name="data_dostavki1" value="<?=date('d.m.Y');?>"  />-<input type="text" name="data_dostavki2"  value="<?=date('d.m.Y');?>" />
                </div>
            </div>
            <div>
                <p>Доставка</p>
                <div class="m_zakaz_s_cat_fillter_dostavka">
                    <select name="dostavka" data-placeholder="Статус доставки">
                        <option value="1" selected="selected">Доставка (не выполнен)</option>
                        <option value="2">Доставка (выполнен)</option>
                        <option value="3">Без доставки</option>
                    </select>
                </div>
            </div>
            <div>
                <p>Статус доставки</p>
                <div class="m_zakaz_s_cat_fillter_status">
                    <select name="status_dostavki" data-placeholder="Статус доставки" multiple>
                        <option value="Не заказан">Не заказан</option>
                        <option value="В наличии у поставщика">В наличии у поставщика</option>
                        <option value="Заказан">Заказан</option>
                        <option value="Доработка">Доработка</option>
                        <option value="Отложенная закупка">Отложенная закупка</option>
                        <option value="В наличии на складе">В наличии на складе</option>
                    </select>
                </div>
            </div>
            <div>
                <p>Город</p>
                <div class="m_zakaz_s_cat_fillter_city">
                    <select name="city" data-placeholder="Город доставки" multiple></select>
                </div>
            </div>
            <div>
                <p>ТК</p>
                <div class="m_zakaz_s_cat_fillter_i_tk">
                    <select name="i_tk" data-placeholder="Транспортная компания" multiple></select>
                </div>
            </div>
            <div>
                <p>Структура</p>
                <div class="m_zakaz_s_cat_fillter_s_struktura">
                    <select name="s_struktura" data-placeholder="Структура" multiple></select>
                </div>
            </div>
            <div>
                <span class="m_zakaz_s_cat_find_com btn_gray"><i class="fa fa-search"></i> Поиск</span>
            </div>
            <div>
                <span class="m_zakaz_s_cat_csv btn_gray"><i class="fa fa-file-excel-o"></i> Выгрузить в csv</span>
                <span class="m_zakaz_s_cat_marshrut btn_gray"><i class="fa fa-truck"></i> Маршрутный лист</span>
            </div>
        </form>
    </div>
    
    <!-- Таблица -->
    <div class="m_zakaz_s_cat_res"></div>
</div>