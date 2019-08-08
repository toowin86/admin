<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>

    function add_r_model(id,name){
        var txt='';
        txt+='<div data-id="'+id+'">';
        txt+='<i class="fa fa-remove" title="Удалить"></i>';
        txt+='<span>';
        txt+=name;
        txt+='</span>';
        txt+='</div>';
        return txt;
    }
    
//ЧПУ
// *******************************************************************************
// *******************************************************************************
if (tip_=='start'){
    
    // функция
    $(document).delegate('.__other__add_form .r_model_res .fa-remove','click',function(){
        var id_del=$(this).closest('div').data('id');
        $(this).closest('div').detach();
        var val=$('.__other__add_form input[name="r_model"]').val();
        if (val!=''){
            if ((val.split(',').length - 1)>0){
                var arr = val.split(',');
                val='';
                for (var i in arr){
                    if (arr[i]!=id_del){
                        if (val!=''){val+=',';}
                        val+=arr[i];
                    }
                }
            }else{
                if (val==id_del){
                    val='';
                }
            }
        }
        $('.__other__add_form input[name="r_model"]').val(val);
        $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
    });
    
    //удаляем все модели
    $(document).delegate('.r_model_clear_all_model','click',function(){
        $('.__other__add_form input[name="r_model"]').val('');
        $('.__other__add_form .r_model_res').html('');
        $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
    });
    
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='change'){
    
    var val_=th_._d['r_model'];
    var txt='';
    var val='';
    for (var i in val_){
            if (val!=''){val+=',';}
            val+=val_[i][0];
            txt+=add_r_model(val_[i][0],val_[i][1]);
            
    }
    $('.__other__add_form input[name="r_model"]').val(val);
    $('.__other__add_form .r_model_res').html(txt);
    $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
    
    
    $('.r_model').each(function(){
        var th_=$(this);
        
        th_.autocomplete({
            appendTo: ".__other__add_form",
            minLength: 0,
            source: function(request, response){
                 request['_t']='find';
                 request['id']=$('.__other__add_form input[name="r_model"]').val();
                 
                 if (typeof jqxhr!='undefined'){jqxhr.abort();}
                 jqxhr = $.ajax({
                	"type": "POST",
                	"url": "ajax/__function_s_cat_r_model_ajax.php",
                	"dataType": "text",
                	"data":request,
                	"success":function(data,textStatus){
                	   th_.removeClass('ui-autocomplete-loading');
                	   if (is_json(data)==true){
                    	       data_n=JSON.parse(data);
                               response(data_n);
                        }else{
                            alert_m(data,'','error','none');
                        }
                	}
                });
            },
            select:function( event, ui ) {
                
                var val=$('.__other__add_form input[name="r_model"]').val();
                if (val!=''){val+=',';}
                val+=ui.item.id;
                $('.__other__add_form input[name="r_model"]').val(val);
                
                var txt='';
                txt+=add_r_model(ui.item.id,ui.item.label);
                
                
                $('.__other__add_form .r_model_res').prepend(txt);
                $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
    
                
            },
            focus: function( e, ui ) {
                var kol=$('.__other__add_form .r_model_res div[data-id="'+ui.item.id+'"]').size();
                if (e.ctrlKey==true && kol==0){
                    $('.ui-menu-item:contains("'+ui.item.label+'")').detach();
                    var val=$('.__other__add_form input[name="r_model"]').val();
                    if (val!=''){val+=',';}
                    val+=ui.item.id;
                    $('.__other__add_form input[name="r_model"]').val(val);
                    
                    var txt='';
                    txt+=add_r_model(ui.item.id,ui.item.label);
                    
                    $('.__other__add_form .r_model_res').prepend(txt);
                    $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
                    
                }
            },
            close: function( event, ui ) {
                $('.__other__add_form .r_model').val('');
                th_.trigger('keyup');
            }
        });
    });
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='add'){

    $('.r_model').each(function(){
        var th_=$(this);
        
        th_.autocomplete({
            minLength: 0,
            appendTo: ".__other__add_form",
            source: function(request, response){
                 request['_t']='find';
                 request['id']=$('.__other__add_form input[name="r_model"]').val();
                 
                 if (typeof jqxhr!='undefined'){jqxhr.abort();}
                 jqxhr = $.ajax({
                	"type": "POST",
                	"url": "ajax/__function_s_cat_r_model_ajax.php",
                	"dataType": "text",
                	"data":request,
                	"success":function(data,textStatus){
                	   th_.removeClass('ui-autocomplete-loading');
                	   if (is_json(data)==true){
                    	       data_n=JSON.parse(data);
                               response(data_n);
                        }else{
                            alert_m(data,'','error','none');
                        }
                	}
                });
            },
            select:function( event, ui ) {
                
                var val=$('.__other__add_form input[name="r_model"]').val();
                if (val!=''){val+=',';}
                val+=ui.item.id;
                $('.__other__add_form input[name="r_model"]').val(val);
                
                var txt='';
                txt+=add_r_model(ui.item.id,ui.item.label);
                
                
                $('.__other__add_form .r_model_res').prepend(txt);
                $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
    
                
            },
            focus: function( e, ui ) {
                var kol=$('.__other__add_form .r_model_res div[data-id="'+ui.item.id+'"]').size();
                if (e.ctrlKey==true && kol==0){
                    $('.ui-menu-item:contains("'+ui.item.label+'")').detach();
                    var val=$('.__other__add_form input[name="r_model"]').val();
                    if (val!=''){val+=',';}
                    val+=ui.item.id;
                    $('.__other__add_form input[name="r_model"]').val(val);
                    
                    var txt='';
                    txt+=add_r_model(ui.item.id,ui.item.label);
                    
                    $('.__other__add_form .r_model_res').prepend(txt);
                    $('.__other__add_form .r_model__all').html('Выбрано: '+$('.__other__add_form .r_model_res>div').size());
                    
                }
            },
            close: function( event, ui ) {
                $('.__other__add_form .r_model').val('');
                th_.trigger('keyup');
            }
        });
    });
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='save'){
    var err_text='';
    
    return err_text;
}
//ФИЛЬТР
// *******************************************************************************
// *******************************************************************************
else if (tip_=='fillter'){ //ФИЛЬТР ПО ТОВАРУ
   
    var txt='<div class="__other__fillter_div"><input type="text" name="r_model_find_text" placeholder="Найти" class="r_model_find_text" value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></div>';
    
    return txt;
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='find'){
  

    var text='';
    if ((th_.split('||').length - 1)>0){
        var arr_prop = th_.split('||');
    }
    else{
        var arr_prop = new Array();
        arr_prop[0]=th_;
    }
    for(var i in arr_prop) 
    {
            var new_text='<p>'+arr_prop[i]+'</p>';
            text+=new_text;
    }
    return '<div>'+text+'</div>';
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='parser'){
    return '';
}// *******************************************************************************
// *******************************************************************************
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
    alert_m('Тип функции '+tip_+' ('+col_+') не определен!','','error','none');
}
// *******************************************************************************
// *******************************************************************************
//end  ЧПУ
