<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script type="text/javascript">

function select_status(status){
    
    var cl_='';
    if (status=='Не заказан'){cl_='m_zakaz_s_cat_no_order';}
    if (status=='В наличии у поставщика'){cl_='m_zakaz_s_cat_in_postav';}
    if (status=='Заказан'){cl_='m_zakaz_s_cat_order';}
    if (status=='Доработка'){cl_='m_zakaz_s_cat_work';}
    if (status=='Отложенная закупка'){cl_='m_zakaz_s_cat_longtime';}
    if (status=='В наличии на складе'){cl_='m_zakaz_s_cat_in_sklad';}
    return cl_;
}

//Добавление
function add_row(   id,
                    m_zakaz_id,
                    project_name,
                    data,
                    s_cat_name,
                    price,
                    kolvo,
                    i_contr_name,
                    i_contr_phone,
                    city,
                    adress,
                    status,
                    s_cat_id,
                    i_tk_name,
                    s_struktura,
                    dostavka_data,
                    comments,
                    data_end,
                    i_tk_id,
                    s_prop_val
    ){
    
    var cl_=select_status(status);
    var txt='';
    txt+='<div class="ttable_tbody_tr '+cl_+'" data-id="'+_IN(id)+'" data-m_zakaz_id="'+_IN(m_zakaz_id)+'">';
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_num">'+id+' <a href="?inc=i_docs&com=print&file_name=docx_tovar_check&nomer='+m_zakaz_id+'"><i class="fa fa-print" title="Товарный чек"></i></a></div>';
        if (data_end==''){data_end='<i class="fa fa-plus"></i>';}
        if (dostavka_data==''){dostavka_data='<i class="fa fa-plus"></i>';}
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_data_dostavki"><span>'+dostavka_data+'</span></div>';
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_zakaz_info">';
            txt+='<div class="m_zakaz_s_cat_i_contr"><span>'+i_contr_name+'</span> <a href="tel:'+i_contr_phone+'">'+i_contr_phone+'</a></div>';
            txt+='<div class="m_zakaz_s_cat_zakaz"><a href="?inc=m_zakaz&nomer='+m_zakaz_id+'">Заказ №'+m_zakaz_id+' от '+data+'</a>, напомнить: <span class="m_zakaz_s_cat_data_end"><span>'+data_end+'</span></span></div>';
            if (city==''){
                txt+='<div class="m_zakaz_s_cat_city"><strong>Самовывоз</strong></div>';
            }
            else{
                txt+='<div class="m_zakaz_s_cat_city"><strong>'+city+'</strong> <span>'+adress+'</span></div>';
            }
            
        txt+='</div>';
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_zakaz_status"><span>'+status+'</span></div>';
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_zakaz_s_cat">';
            txt+='<p>'+s_cat_id+' <a href="?inc=s_cat&nomer='+s_cat_id+'">'+s_cat_name+' <span class="m_zakaz_s_cat_s_prop_val">'+s_prop_val+'</span></a>,';
            txt+='<span class="m_zakaz_s_cat_zakaz_s_cat_kol">'+kolvo+'</span> шт., <strong>'+price+'</strong> руб.';
            txt+='</p>';
            if (comments==''){comments='<i class="fa fa-plus" title="Добавить комментарий"></i>';}
            txt+='<div class="m_zakaz_s_cat_zakaz_comments"><span>'+comments+'</span></div>';
        txt+='</div>';
        if (i_tk_name==''){i_tk_name='<i class="fa fa-plus" title="Добавить комментарий"></i>';}
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_zakaz_i_tk" data-i_tk_id="'+i_tk_id+'"><span>'+i_tk_name+'</span></div>';
        txt+='<div class="ttable_tbody_tr_td m_zakaz_s_cat_zakaz_s_struktura">';
        if (typeof s_struktura=='object'){
            
            for (var j in s_struktura){
                txt+='<div data-str_id="'+j+'">'+s_struktura[j]+'</div>';
            }
        }
        txt+='</div>';
    txt+='</div>';
    return txt;
}

//выгрузка в csv
function get_csv(){
    $('.m_zakaz_s_cat_fillter>form').attr('method','POST').attr('action','?inc=export_csv_m_zakaz_s_cat').submit();
}

//поиск
function find(limit,callback){
    limit=limit || '';
    callback=callback || '';
    var err_text='';
    var data_=$('.m_zakaz_s_cat_fillter>form').serializeObject();
    data_['_t']='find';
    data_['limit']='limit';
    
    
    
    
    
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
        if (limit==''){
            $('.m_zakaz_s_cat_res').html('<p class="m_zakaz_s_cat_res__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
        }
        else{
            $('.m_zakaz_s_cat_res').append('<p class="m_zakaz_s_cat_res__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
        }
    	
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			$('.m_zakaz_s_cat_res__loading').detach();
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt='';
                    if (typeof data_n.i=='object'){
                        if (typeof data_n.i['id']=='object'){
                            for(var i in data_n.i['id']){
                                txt+=add_row(data_n.i['id'][i],
                                                data_n.i['m_zakaz_id'][i],
                                                data_n.i['project_name'][i],
                                                data_n.i['data'][i],
                                                data_n.i['s_cat_name'][i],
                                                data_n.i['price'][i],
                                                data_n.i['kolvo'][i],
                                                data_n.i['i_contr_name'][i],
                                                data_n.i['i_contr_phone'][i],
                                                data_n.i['city'][i],
                                                data_n.i['adress'][i],
                                                data_n.i['status'][i],
                                                data_n.i['s_cat_id'][i],
                                                data_n.i['i_tk_name'][i],
                                                data_n.i['s_struktura'][i],
                                                data_n.i['dostavka_data'][i],
                                                data_n.i['comments'][i],
                                                data_n.i['data_end'][i],
                                                data_n.i['i_tk_id'][i],
                                                data_n.i['s_prop_val'][i]
                                            );
                                }
                            
                        }else{
                            txt+='Открытые <a href="?inc=m_zakaz">заказы</a> отсутствуют!';
                        }
                    }
    	            if (txt!=''){
                            txt='<div class="ttable m_zakaz_s_cat_res_tbl">'+txt+'</div>';
                            $('.m_zakaz_s_cat_res').html(txt);
                        }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}


//Изменение полей
function change_info(tip_,m_zakaz_s_cat_id,info){
    var err_text='';
    var data_=new Object();
    data_['_t']=tip_;
    data_['m_zakaz_s_cat_id']=m_zakaz_s_cat_id;
    data_['val']=info;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}
//******************************************************************************************************************
//******************************************************************************************************************
//******************************************************************************************************************
$(document).ready(function(){



    find();
    
    //поиск по иконке
    $(document).delegate('.m_zakaz_s_cat_find_com','click',function(){
        find();
    });
    //поиск по иконке
    $(document).delegate('.m_zakaz_s_cat_csv','click',function(){
        get_csv();
    });
    
    //поиск по дате доставки
    $('input[name="data_dostavki1"], input[name="data_dostavki2"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,dayOfWeekStart:1,step: 60,onClose:function(dp,$input){
        
    } }).blur(function(){
        find();
    });
    
    $('select[name="status_dostavki"]').select2({'width':'100%'}).change(function(){
        find();
    });
    $('select[name="dostavka"]').select2({'width':'100%'}).change(function(){
        find();
    });
       $('.m_zakaz_s_cat_fillter_city select').select2({allowClear: true,'width':'100%',
        ajax: {
            url: "ajax/m_zakaz.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, 
                    page: params.page,
                    _t:'autocomplete_city'
                };
            },
            processResults: function (data, page) {
                return {
                results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; },
        minimumInputLength: 0
        
    }).change(function(){
        find();
    });
    
    //ТК
  $('.m_zakaz_s_cat_fillter_i_tk>select').select2({'width':'100%', 
            ajax: {
                url: "ajax/m_zakaz.php",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    term: params.term, // search term
                    _t: 'i_tk_autocomplete'
                  };
                },
                processResults: function (data) {
                  return {
                    results: data.items
                  };
                },
                cache: true
          }})
        .on("select2:close", function (e) { 
            find();
            
        });
        
    //Структура
  $('.m_zakaz_s_cat_fillter_s_struktura>select').select2({'width':'100%', 
            ajax: {
                url: "ajax/m_zakaz_s_cat.php",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    term: params.term, // search term
                    _t: 's_struktura_autocomplete'
                  };
                },
                processResults: function (data) {
                  return {
                    results: data.items
                  };
                },
                cache: true
          }})
        .on("select2:close", function (e) { 
            find();
            
        });
    
    //Изменение статуса
    $(document).delegate('.m_zakaz_s_cat_zakaz_status>span','click',function(){
        var th_=$(this);
        var th_main=th_.closest('div');
        var cur_status=th_.text();
        var cl_old=select_status(cur_status);
        th_main.closest('.ttable_tbody_tr').removeClass(cl_old);
        var m_zakaz_s_cat_id=th_.closest('.ttable_tbody_tr').data('id');
        
        var txt='<select class="m_zakaz_s_cat_zakaz_status_select" data-placeholer="Статус товара">';
        txt+='<option value="Не заказан">Не заказан</option>';
        txt+='<option value="В наличии у поставщика">В наличии у поставщика</option>';
        txt+='<option value="Заказан">Заказан</option>';
        txt+='<option value="Доработка">Доработка</option>';
        txt+='<option value="Отложенная закупка">Отложенная закупка</option>';
        txt+='<option value="В наличии на складе">В наличии на складе</option>';
        txt+='</select>';
        
        th_main.html(txt);
        
        $('.m_zakaz_s_cat_zakaz_status_select').select2({'width':'100%'}).select2("open")
                .on("select2:close", function (e) { 
                    
                    var new_status=$(this).find('option:selected').text();
                    change_info('change_status',m_zakaz_s_cat_id,new_status);
                    var cl_new=select_status(new_status);
                    th_main.html('<span>'+new_status+'</span>');
                    th_main.closest('.ttable_tbody_tr').addClass(cl_new);
                    
                });
        
        
    });
    
    //Изменение даты доставки
    $(document).delegate('.m_zakaz_s_cat_data_dostavki>span','click',function(){
        var th_=$(this);
        var th_main=th_.closest('div');
        var m_zakaz_s_cat_id=th_.closest('.ttable_tbody_tr').data('id');
        var m_zakaz_id=th_.closest('.ttable_tbody_tr').data('m_zakaz_id');
        var cur_data=th_.text();
        th_main.html('<input type="text" class="m_zakaz_s_cat_data_dostavki_input" value="'+cur_data+'" />');
        $('.m_zakaz_s_cat_data_dostavki_input').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,dayOfWeekStart:1,step: 60,onClose:function(dp,$input){
               
        } }).focus().blur(function(){
             var new_data_dostavki=$('.m_zakaz_s_cat_data_dostavki_input').val();
            $('.ttable_tbody_tr[data-m_zakaz_id="'+m_zakaz_id+'"]').find('.m_zakaz_s_cat_data_dostavki').html('<span>'+new_data_dostavki+'</span>');
            change_info('change_data_dostavki',m_zakaz_s_cat_id,new_data_dostavki);
        });
        
        
    });
    
    //Изменение даты напоминания
    $(document).delegate('.m_zakaz_s_cat_data_end>span','click',function(){
        var th_=$(this);
        var th_main=th_.closest('span');
        var m_zakaz_s_cat_id=th_.closest('.ttable_tbody_tr').data('id');
        var m_zakaz_id=th_.closest('.ttable_tbody_tr').data('m_zakaz_id');
        var cur_data=th_.text();
        th_main.html('<input type="text" class="m_zakaz_s_cat_data_end_input" value="'+cur_data+'" />');
        $('.m_zakaz_s_cat_data_end_input').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,dayOfWeekStart:1,step: 60,onClose:function(dp,$input){
               
        } }).focus().blur(function(){
             var new_data_end=$('.m_zakaz_s_cat_data_end_input').val();
            $('.ttable_tbody_tr[data-m_zakaz_id="'+m_zakaz_id+'"]').find('.m_zakaz_s_cat_data_end').html('<span>'+new_data_end+'</span>');
            change_info('change_data_end',m_zakaz_s_cat_id,new_data_end);
        });
        
        
    });
    
    //Изменение комментариев
    $(document).delegate('.m_zakaz_s_cat_zakaz_comments>span','click',function(){
        var th_=$(this);
        var th_main=th_.closest('div');
        var m_zakaz_s_cat_id=th_.closest('.ttable_tbody_tr').data('id');
        var cur_comments=th_.text();
        th_main.html('<input type="text" class="m_zakaz_s_cat_comments_input" value="'+cur_comments+'" />');
        $('.m_zakaz_s_cat_comments_input').focus().blur(function(){
             var new_comments=$('.m_zakaz_s_cat_comments_input').val();
             var new_comments_txt=new_comments;
             if (new_comments==''){new_comments_txt='<i class="fa fa-plus" title="Добавить комментарий"></i>';}
            th_main.html('<span>'+new_comments_txt+'</span>');
            change_info('change_comments',m_zakaz_s_cat_id,new_comments);
        }).keyup(function(e){
            if (e.which==13){
                $(this).blur();
            }
        });
        
        
    });
    
    //Изменение ТК
    $(document).delegate('.m_zakaz_s_cat_zakaz_i_tk','click',function(){
        var th_=$(this);
        var th_main=th_.closest('div');
        var m_zakaz_s_cat_id=th_.closest('.ttable_tbody_tr').data('id');
        var cur_i_tk_name=th_.text();
        var cur_i_tk_id=th_.closest('div').data('i_tk_id');
        th_main.html('<select class="m_zakaz_s_cat_zakaz_i_tk_select"><option value="'+cur_i_tk_id+'" selected="selected">'+cur_i_tk_name+'</option></select>');
        
        $('.m_zakaz_s_cat_zakaz_i_tk_select').select2({'width':'100%', 
                    ajax: {
                        url: "ajax/m_zakaz.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                          return {
                            term: params.term, // search term
                            _t: 'i_tk_autocomplete'
                          };
                        },
                        processResults: function (data) {
                          return {
                            results: data.items
                          };
                        },
                        cache: true
                  }}).select2("open")
                .on("select2:close", function (e) { 
                    
                    var new_i_tk_val=$(this).find('option:selected').text();
                    var new_i_tk_id=$(this).find('option:selected').attr('value');
                    change_info('change_i_tk',m_zakaz_s_cat_id,new_i_tk_id);
                    if (new_i_tk_val==''){new_i_tk_val='<i class="fa fa-plus" title="Выбрать транспортную компанию"></i>';}
                    th_main.html('<span>'+new_i_tk_val+'</span>');
                    
                });
        
    });
    
    //Маршрутный лист
    $(document).delegate('.m_zakaz_s_cat_marshrut','click',function(){
        var err_text='';
        var data_=$('.m_zakaz_s_cat_fillter>form').serializeObject();
        data_['_t']='get_id_zakaz';
        
        $.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof data_n.id=='string' && data_n.id!=''){
    	               $('.marshrut_list_div').detach();
    	               $('h1').append('<div class="marshrut_list_div" style="display:none;"><form class="marshrut_list_form" action="?inc=i_docs&com=print&file_name=marshrut_list" method="POST"><textarea class="marshrut_list_nomer" name="nomer"></textarea></form></div>');
    	               $('.marshrut_list_nomer').val(data_n.id);
                       $('.marshrut_list_form').submit();
                    }
                    else{
                        alert_m('Нет данных для выгрузки','','error','none');
                    }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
        
    });
    
    
});
</script>