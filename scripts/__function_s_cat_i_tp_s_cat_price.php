<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
if (tip_=='start'){ //СТАРТ - ОБЪЯВЛЯЕМ ФУНКЦИИ LIVE

    $(document).delegate('.i_tp_s_cat_price_change_open','click',function(){
        $('.i_tp_s_cat_price_change_div').css({'display':'block'});
        $('.i_tp_s_cat_price_change_open').text('Скрыть цены в других филиалах')
            .addClass('i_tp_s_cat_price_change_close')
            .removeClass('i_tp_s_cat_price_change_open');
    });
    $(document).delegate('.i_tp_s_cat_price_change_close','click',function(){
        $('.i_tp_s_cat_price_change_div:not(.active)').css({'display':'none'});
        $('.i_tp_s_cat_price_change_close').text('Показать цены во всех филиалах')
            .addClass('i_tp_s_cat_price_change_open')
            .removeClass('i_tp_s_cat_price_change_close');
    });
    
}//end start
//ФИЛЬТР
// *******************************************************************************
// *******************************************************************************
else if (tip_=='fillter'){ //ФИЛЬТР ПО ТОВАРУ

    
    var txt='';
    return txt;
}
//ПОИСК
// *******************************************************************************
// *******************************************************************************
else if (tip_=='find'){ //ПОИСК ПО ТОВАРУ

    var text='';
    if ((th_.split('||').length - 1)>0){
        var arr_i_tp_s_cat_price = th_.split('||');
    }
    else{
        var arr_i_tp_s_cat_price = new Array();
        arr_i_tp_s_cat_price[0]=th_;
    }
    for(var i in arr_i_tp_s_cat_price) 
    {
        th_2=arr_i_tp_s_cat_price[i];
        if ((th_2.split('@@').length - 1)>0){
            var arr_i_tp_s_cat_price2 = th_2.split('@@');
            var new_text='<p>'+arr_i_tp_s_cat_price2[0]+': <strong>'+arr_i_tp_s_cat_price2[1]+'</strong></p>';
            text+=new_text;
        }
        
           
    }
    return '<div>'+text+'</div>';
}
// *******************************************************************************
// *******************************************************************************
else if (tip_=='add'){//ОТКРЫТИЕ ОКНА ДОБАВЛЕНИЯ ТОВАРА
    $('.i_tp_s_cat_price').float_(); //Целое число
}
// *******************************************************************************
// *******************************************************************************
else if (tip_=='change'){//ОТКРЫТИЕ ОКНА ИЗМЕНЕНИЯ ТОВАРА
    $('.i_tp_s_cat_price').float_(); //Целое число
     var val_=th_._d['i_tp_s_cat_price'];
     if (typeof val_=='object'){
         for (var i in val_){
            $('input[name="i_tp_s_cat_price_'+val_[i][0]+'"]').val(val_[i][1]);
         }
     }
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='save'){
   
    //сохраняет автоматом т.к. свойства передалются в select -> name функцией seriallize
    return '';
}
// *******************************************************************************
// *******************************************************************************
// парсим
else if(tip_=='parser'){
   
}
// *******************************************************************************
// *******************************************************************************
// Экспорт
else if(tip_=='export'){
            var txt ='<li data-col="'+col_+'">'
                +'<input type="checkbox" id="export_chk_'+col_+'" name="export_chk_'+col_+'" data-col="'+col_+'" /> '
                +'<label for="export_chk_'+col_+'">'
                    +$('.__other__add .ttable .ttable_tbody_tr[data-col="'+col_+'"]').find('.ttable_tbody_tr_td:first').html()
                +'</label> '
                +'<div class="export_textarea_div">'
                    +'<textarea placeholder="PHP код для изменения поля $myrow[\''+col_+'\']" name="export_code_'+col_+'"></textarea>'
                +'</div><div style="clear:both;"></div>'
                +'</li>';
         return txt;
}
else{
    alert_m('Тип функции ('+tip_+') '+col_+' не определен!','','error','none');
}
// *******************************************************************************
// *******************************************************************************
//end  prop
