<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
?>
<script type="text/javascript">

var null_val='<img src="i/mess_add.png" style="width:18px;"/>';
var val_id;
//поиск
function find(limit,callback){

    limit=limit || '';
    callback=callback || '';
        //новый поиск
        if (limit==''){
            $('.c_call_client_res').html('');
        }
    var err_text='';
    var data_=new Object();
    data_['_t']='c_call_client__find';
    data_['txt']=$('input[name="find_txt"]').val();
    
    data_['d1']=$('input[name="c_call_client_data1_find"]').val();
    data_['d2']=$('input[name="c_call_client_data2_find"]').val();

    
    data_['limit']=limit;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
        $('.c_call_client_res').append('<p class="c_call_client__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
    	$('.c_call_client__load_add').detach();
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/c_call_client.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			$('.c_call_client__loading, .all_calls_find').detach();
                
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var day_class='odd';
                    var old_class_day='';
                    var cnt_=data_n.cnt_;//общее количество
                    var cur_cnt_=0;//текущее количество
                    var txt='';
                    for (var i in data_n['i']){//перебор по звонкам
                        var dd=data_n['dd'][i];
                        var arr=dd.split(' ');
                        if (typeof arr[1]!='undefined'){
                            if (arr[0]!=old_class_day){old_class_day=arr[0];if (day_class=='odd'){day_class='even'}else{day_class='odd';}}
                            dd='<span class="thumbnail">'+arr[1]+'<span>'+arr[0]+'</span></span>';
                        }
                        
                        txt+='<div data-id="'+data_n['i'][i]+'" class="ttable_tbody_tr '+day_class+'">';
                        
                        txt+='<div class="ttable_tbody_tr_td c_call_client__tbl_dd"><div class="for_mobile">Дата</div><div class="val">'+dd+'</div></div>';
                        txt+='<div class="ttable_tbody_tr_td"><div class="for_mobile">Работник</div><div class="val">'+data_n['an'][i]+'</div></div>';
                        
                        txt+='<div class="ttable_tbody_tr_td i_contr_td" data-id="'+data_n['ii'][i]+'"><div class="for_mobile">Клиент</div><div class="val">';
                        if (data_n['in'][i]!=''){
                            txt+='<p><span class="c_call_client__i_contr_name">'+data_n['in'][i]+'</span></p>';
                        }
                        txt+='<p><span class="c_call_client__i_contr_phone">'+data_n['ip'][i]+'</span></p></div></div>';
                        txt+='<div class="ttable_tbody_tr_td"><div class="for_mobile">Комментарии</div><div class="val"><span class="c_call_client__comments">'+data_n['c'][i]+'</span></div></div>';
                        if (typeof data_n['a_col_ru_arr']=='object'){
                            for(var a_col_id in data_n['a_col_ru_arr']){
                                var val_=data_n['col'][i]['val'][a_col_id];
                                val_id=data_n['col'][i]['val_'][a_col_id];
                                
                                //если мульти значения
                                if ((val_.split('||').length - 1)>0){
                                    var val_arr=val_.split('||');
                                    var val_id_arr=val_id.split('||');
                                    val_='';
                                    for (var jj in val_arr){
                                        if (val_!=''){val_+=', ';}
                                        val_+='<span class="c_call_client__cols_val_multi" data-id="'+val_id_arr[jj]+'">'+val_arr[jj]+'</span>';
                                    }
                                }
                                
                                txt+='<div class="ttable_tbody_tr_td"><div class="for_mobile">'
                                +data_n['a_col_ru_arr'][a_col_id]
                                +'</div><div class="val"><span class="c_call_client__cols_val"'
                                +' data-col="'+data_n['a_col_col_arr'][a_col_id]+'"'
                                +' data-col_id="'+a_col_id+'"'
                                +' data-val_id="'+_IN(data_n['col'][i]['val_'][a_col_id])+'"'
                                +' data-inc="'+data_n['a_menu_inc_arr'][a_col_id]+'">'+val_+'</span></div></div>';
                            }
                            
                        }
                        txt+='<div class="ttable_tbody_tr_td c_call_client__tbl_td_comm"><div class="for_mobile"></div><div class="val">';
                        if (data_n['zi'][i]==''){
                          txt+='<a href="?inc=m_zakaz&com=add_zakaz_in_c_call_client&id='+data_n['i'][i]+'" target="_blank"><i class="fa fa-plus c_call_client_add_m_zakaz" title="Добавить заказ"><span>Заказ</span></i></a> ';
                        }
                        else{
                            txt+='<a href="?inc=m_zakaz&nomer='+data_n['zi'][i]+'" target="_blank"><span>Заказ №'+data_n['zi'][i]+'</span></a> ';
                        }
                        txt+='<i class="fa fa-remove c_call_client_remove" title="Удалить звонок"></i> </div></div>';
                        
                        txt+='</div>';
                        cur_cnt_++;
                    
                    }
                    if (txt==''){txt='Звонков не найдено!';}
                    
                    cur_cnt_=(cur_cnt_-0)+($('.ttable_tbody_tr').size()-0);
    	            if (txt!=''){
                       
                       if (limit==''){
        	               var txt_2='<div class="ttable c_call_client_thead">'
                                +''
                                +'<div class="ttable_thead_tr">'
                                +'<div class="ttable_thead_tr_th">Дата</div>'
                                +'<div class="ttable_thead_tr_th">Работник</div>'
                                +'<div class="ttable_thead_tr_th">Клиент</div>'
                                +'<div class="ttable_thead_tr_th">Комментарии</div>';
                                for(var a_col_id in data_n['a_menu_inc_arr']){
                                   txt_2+='<div class="ttable_thead_tr_th">'+data_n['a_col_ru_arr'][a_col_id]+'</div>'; 
                                }
                                txt_2+='<div class="ttable_thead_tr_th">Опции</div>';
                                txt_2+='</div>'
                                +'<div class="ttable_tbody">'
                                +txt
                                +'</div>';
                                +'</div>';
                                $('.c_call_client_res').append(txt_2);
                        }else{
                           
                            $('.c_call_client_res .ttable_tbody').append(txt);
                            
                        }
                        
                        $('.c_call_client_res .c_call_client__cols_val:empty, .c_call_client_res .c_call_client__comments:empty').html(null_val);//добавляем +
        
                        
                        if (cnt_>cur_cnt_){
                            $('.c_call_client_res').append('<div class="c_call_client__load_add">Загрузить ещё...</div>');
                        }
                        $('.c_call_client_res').prepend('<div class="all_calls_find">Найдено: '+cnt_+'</div>');
    	                
    	            }
                    if (typeof callback=='function'){callback(data_n);}
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}



//Очистка
function c_call_client_clear(callback){
    callback=callback || '';
    $('.c_call_client_add__other_comments').val('');
    $('.c_call_client_add_form input').val('');
    $('.c_call_client_add_form select option').removeAttr('selected');
    $('.c_call_client_add_form select').each(function(){
        $(this).trigger('change');
    });
    if (typeof callback=='function'){callback();}
}

//Сохранение
function c_call_client_save(callback){
    callback=callback || '';
    var err_text='';
    var data_=$('.c_call_client_add_form').serializeObject();
    data_['_t']='c_call_client_save';
    if (data_['i_contr_phone']==''){err_text+='<p>Не указан телефон клиента!</p>';}
    if (data_['i_contr_name']=='' ){err_text+='<p>Не указано имя клиента!</p>';}
    if (data_['comments']=='' ){err_text+='<p>Нет описания звонка!</p>';}
    
    $('.c_call_client_add__quest').each(function(){
        var th_=$(this).find('input[name*="c_questions_id_"], select[name*="c_questions_id_"]');
        var val=th_.val();
        var name_=th_.closest('.c_call_client_add__quest').find('>span:first').text();
        
        if (th_.attr('required')=='required' && (val=='' || val==null)){
            err_text+='<p>Не заполнено поле <strong>'+name_+'</strong>!</p>';
        }
    });
    
    
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/c_call_client.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            c_call_client_clear();//очищаем форму
                    if (typeof callback=='function'){callback(data_n);}
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}

//Удаление звонка
function c_call_client_remove(id_){
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='c_call_client_remove';
    data_['nomer']=id_;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
        $('.ttable_tbody_tr[data-id="'+id_+'"]').detach();
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/c_call_client.php",
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


//Поиск товаров и работ
function items_and_work_add(){
    var err_text='';
    var data_=$('.c_call_client_add_form').serializeObject();
    data_['_t']='items_and_work_add';
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/c_call_client.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            add_work(data_n.w);
    	            add_item(data_n.i);
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

//Добавление работ
function add_work(arr){
    var txt='';
    if (typeof arr=='object' && typeof arr.p=='object'){
        for (var i in arr.p){
            txt+='<p><a target="_blank" href="../'+arr.i[i]+'">'+arr.n[i]+'</a> <strong>'+arr.p[i]+'</strong></p>';
        }
    }
    $('.c_call_client_info_works').html(txt);
}

//Добавление товаров
function add_item(arr){
    var txt='';
    if (typeof arr=='object' && typeof arr.p=='object'){
        for (var i in arr.p){
            txt+='<p><a target="_blank" href="../'+arr.i[i]+'">'+arr.n[i]+'</a> <strong>'+arr.p[i]+'</strong></p>';
        }
    }
    $('.c_call_client_info_items').html(txt);
    
}

//Синхронизация звонков с Google диска
function google_drive_syn_calls(callback){
    callback=callback || '';
    var data_=new Object();
    data_['_t']='google_drive_syn_calls';
    data_['d']=$('.google_drive_syn_data').val();
    $('.google_drive_block .ico_loading').css({'display':'inline-block'});
    $('.google_drive_block .google_drive_syn_calls, .google_drive_syn_data, .google_drive_block .google_drive_exit').css({'display':'none'});
    
	$.ajax({
		"type": "POST",
		"url": "ajax/c_call_client.php",
		"dataType": "text",
		"data":data_,
		"success":function(data,textStatus){
		     $('.google_drive_block .ico_loading').css({'display':'none'});
             $('.google_drive_block .google_drive_syn_calls, .google_drive_syn_data, .google_drive_block .google_drive_exit').css({'display':'inline-block'});
    
	        if (is_json(data)==true){
	            data_n=JSON.parse(data);
                //if (data_n.err!=''){alert_m(data_n.err,'','error','none');}
               
                    var txt='<h2>Добавлено '+data_n.cnt_+' '+end_word(data_n.cnt_,'звонков','звонок','звонка')+':</h2>';
                    for (var i in data_n['n']){
                        txt+='<p>'+data_n['n'][i]['date']+' - '+data_n['n'][i]['phone']+'</p>';
                    }
                    alert_m(txt,'','ok','none');
    	            if (typeof callback=='function'){callback();}
                
			}
			else{
				alert_m(data,'','error','none');
			}            
		}
	});
    
    
}

function quick_change_save(id_,cl_,inc,col,val_id, callback){
    
    id_ =id_ || '';
    cl_ =cl_ || '';
    inc =inc || '';
    col =col || '';
    val_id =val_id || '';
    callback =callback || '';
    
    var err_text='';
    var data_=new Object();//$('.****').serializeObject();
    data_['_t']='c_call_quick_change';
    data_['id_']=id_;
    data_['cl_']=cl_;
    data_['inc']=inc;
    data_['col']=col;
    data_['val_id']=val_id;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/c_call_client.php",
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

//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
$(document).ready(function(){
    
    find();
    $('input[name="c_call_client_data1_find"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(current_time,$input){
        find();
    }});
    $('input[name="c_call_client_data2_find"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(current_time,$input){
        find();
    }});
    //Поиск
    $(document).delegate('.c_call_client_fillter__find_txt input','keyup',function(e){
        if (e.which==13){
            find();
        }
    });
    $(document).delegate('.c_call_client_fillter__find_txt .fa-search','click',function(){
       find(); 
    });
    
    
    $('.google_drive_syn_data').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i:s',closeOnDateSelect:true,onClose: function(current_time,$input){
        
    }});
    
    //автозаполнение мультивыбора
    $('.c_call_client_add__quest select').each(function(){
        var th_=$(this);
        var col_id=th_.data('a_col_id');
        th_.select2({'width':'100%',allowClear: true,closeOnSelect:false,
                    ajax: {
                        url: "ajax/c_call_client.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                
                                dt: $('.c_call_client_add_form').serializeObject(),
                                term: params.term, 
                                page: params.page,
                                _t:'autocomplete_input',
                                _col_id:col_id
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
                    items_and_work_add();
                });
        
    });
    
    //автозаполнение полей
    $('.c_call_client_add__quest>input').each(function(){
        var th_=$(this);
        var col_id=th_.data('a_col_id');

        th_.autocomplete({
            minLength: 0
          }).focus(function() {
                 th_.autocomplete("option", "source",function(request, response){
                     request['dt']=$('.c_call_client_add_form').serializeObject();
                     request['sel']=$('.c_questions_id_901').val();
                     request['_t']='autocomplete_input';
                     request['_col_id']=col_id;
                     if (typeof jqxhr!='undefined'){jqxhr.abort();}
                     jqxhr = $.ajax({
                    	"type": "POST",
                    	"url": "ajax/c_call_client.php",
                    	"dataType": "text",
                    	"data":request,
                    	"success":function(data,textStatus){
                    	   th_.removeClass('ui-autocomplete-loading');
                    	   if (is_json(data)==true){
                        	       data_n=JSON.parse(data);
                                   response( $.map( data_n.items, function(item) {
                                        return {
                                            label: item.name,
                                            value: item.name,
                                            id: item.id
                                        }
                                    }));
                            }else{
                                alert_m(data,'','error','none');
                            }
                            
                            
                            
                            
                    	}
                    });
                 })
            th_.autocomplete("search", th_.val()).on( "autocompleteclose", function( event, ui ) {
                    if (th_.val()!=''){
                        th_.blur();
                        items_and_work_add();
                    }
                        
                    });;
          });
    });
    
    //автозаполнение покупателя
    $('input[name="i_contr_phone"]').integer_().mask("0(000)000-0000",{clearIfNotMatch:false});
    $('input[name="i_contr_phone"]').autocomplete({
            minLength: 0
          }).focus(function() {
                 $('input[name="i_contr_phone"]').autocomplete("option", "source",function(request, response){
                     request['dt']=$('.c_call_client_add_form').serializeObject();
                     request['_t']='autocomplete_i_contr_phone';
                     if (typeof jqxhr!='undefined'){jqxhr.abort();}
                     jqxhr = $.ajax({
                    	"type": "POST",
                    	"url": "ajax/c_call_client.php",
                    	"dataType": "text",
                    	"data":request,
                    	"success":function(data,textStatus){
                    	   $('input[name="i_contr_phone"]').removeClass('ui-autocomplete-loading');
                    	   if (is_json(data)==true){
                        	       data_n=JSON.parse(data);
                                   response( $.map( data_n.items, function(item) {
                                        return {
                                            label: item.text,
                                            value: item.phone,
                                            phone: item.phone,
                                            name: item.name,
                                            r_status: item.r_status,
                                            m_zakaz_id: item.m_zakaz_id,
                                            r_diagnoz: item.r_diagnoz
                                        }
                                    }));
                            }else{
                                alert_m(data,'','error','none');
                            }
                    	}
                    });
                 }).on( "autocompleteselect", function( event, ui ) {
                  
                    $('input[name="i_contr_name"]').val(ui.item.name); 
                    $('input[name="i_contr_phone"]').val(ui.item.phone); 
                    var txt='';
                    if (typeof(ui.item.m_zakaz_id)=='object'){
                        for(var j in ui.item.m_zakaz_id){
                            if (ui.item.m_zakaz_id[j]!=''){
                                txt+='<p><a href="?inc=m_zakaz&nomer='+ui.item.m_zakaz_id[j]+'">Заказ №'+ui.item.m_zakaz_id[j]+'';
                                if (ui.item.r_status[j]!=''){
                                    txt+=' ('+ui.item.r_status[j]+')';
                                }
                                txt+='</a>:</p>';
                            }
                            
                            if (ui.item.r_diagnoz[j]!=''){
                                txt+='<p class="c_call_client_info_client_diagnoz">'+ui.item.r_diagnoz[j]+'</p>';
                            }
                        }
                    }
                    
                    $('.c_call_client_info_client').html(txt); 
                 });
            $('input[name="i_contr_phone"]').autocomplete("search", $('input[name="i_contr_phone"]').val());
          });
    
    //автозаполнение покупателя
    $('input[name="i_contr_name"]').autocomplete({
            minLength: 0
          }).focus(function() {
                 $('input[name="i_contr_name"]').autocomplete("option", "source",function(request, response){
                     request['dt']=$('.c_call_client_add_form').serializeObject();
                     request['_t']='autocomplete_i_contr_name';
                     if (typeof jqxhr!='undefined'){jqxhr.abort();}
                     jqxhr = $.ajax({
                    	"type": "POST",
                    	"url": "ajax/c_call_client.php",
                    	"dataType": "text",
                    	"data":request,
                    	"success":function(data,textStatus){
                    	   $('input[name="i_contr_name"]').removeClass('ui-autocomplete-loading');
                    	   if (is_json(data)==true){
                        	       data_n=JSON.parse(data);
                                   response( $.map( data_n.items, function(item) {
                                        return {
                                            label: item.text,
                                            value: item.name,
                                            phone: item.phone,
                                            name: item.name,
                                            r_status: item.r_status,
                                            m_zakaz_id: item.m_zakaz_id,
                                            r_diagnoz: item.r_diagnoz
                                        }
                                    }));
                            }else{
                                alert_m(data,'','error','none');
                            }
                    	}
                    });
                 }).on( "autocompleteselect", function( event, ui ) {
                   
                    $('input[name="i_contr_name"]').val(ui.item.name); 
                    $('input[name="i_contr_phone"]').val(ui.item.phone); 
                    var txt='';
                    if (typeof(ui.item.m_zakaz_id)=='object'){
                        for(var j in ui.item.m_zakaz_id){
                            if (ui.item.m_zakaz_id[j]!=''){
                                txt+='<p><a href="?inc=m_zakaz&nomer='+ui.item.m_zakaz_id[j]+'">Заказ №'+ui.item.m_zakaz_id[j]+'';
                                if (ui.item.r_status[j]!=''){
                                    txt+=' ('+ui.item.r_status[j]+')';
                                }
                                txt+='</a>:</p>';
                            }
                            
                            if (ui.item.r_diagnoz[j]!=''){
                                txt+='<p class="c_call_client_info_client_diagnoz">'+ui.item.r_diagnoz[j]+'</p>';
                            }
                        }
                    }
                    
                    $('.c_call_client_info_client').html(txt); 
                    
                 });
            $('input[name="i_contr_name"]').autocomplete("search", $('input[name="i_contr_name"]').val());
          });
    
    //Сохранение
    $(document).delegate('.c_call_client_add__save','click',function(){
        c_call_client_save(function(){
            find();
        });
    });
    
    //Удаление звонка
    $(document).delegate('.c_call_client_remove','click',function(){
        var id_=$(this).closest('div.ttable_tbody_tr').data('id');
        c_call_client_remove(id_);
    });
    
    //Копирование номера телефона
    $(document).delegate('.c_call_client_add__copy_name','click',function(){
        var phone_=$('input[name="i_contr_phone"]').val();
       
        $('input[name="i_contr_name"]').val(phone_);
        
    });
    
    //Догрузка
    $(document).delegate('.c_call_client__load_add','click',function(){
        find($('.ttable_tbody_tr').size());
    });
    
    
    $(document).delegate('.google_drive_syn_calls','click',function(){
        google_drive_syn_calls();
    });
    //выбор работника
    
    //Изменение
    $(document).delegate('.c_call_client__i_contr_name, .c_call_client__i_contr_phone, .c_call_client__comments, .c_call_client__cols_val','click',function(){
        var th_=$(this);
        var txt=th_.text();
        var id_=th_.closest('.ttable_tbody_tr').data('id');
        var cl_=th_.attr('class');
        var val_id=th_.data('val_id');
        
        if (cl_=='c_call_client__i_contr_name'){
            //th_.closest('p').html('<input type="text" data-id="'+_IN(id_)+'" data-cl_="i_contr_name" class="c_call_client__quick_change_input" value="'+_IN(txt)+'" />');
        }
        else if (cl_=='c_call_client__i_contr_phone'){
            //th_.closest('p').html('<input type="text" data-id="'+_IN(id_)+'" data-cl_="i_contr_phone" class="c_call_client__quick_change_input" value="'+_IN(txt)+'" />');
        }
        else if (cl_=='c_call_client__comments'){
            th_.closest('div').html('<textarea data-id="'+_IN(id_)+'" data-cl_="comments" class="c_call_client__quick_change_input">'+(txt)+'</textarea>');
            $('.c_call_client__quick_change_input').focus().blur(function(){
                quick_change_save(id_,cl_,'','',$('.c_call_client__quick_change_input').val());
                $('.c_call_client__quick_change_input').closest('.val').html('<span class="'+cl_+'">'+$('.c_call_client__quick_change_input').val()+'</span>');
            });
        }
        else if (cl_=='c_call_client__cols_val'){
            var inc=th_.data('inc');
            var col=th_.data('col');
            var col_id=th_.data('col_id');
            
            //мультиселект
            if ($('select[name="c_questions_id_'+col_id+'"]').size()>0){
                var txt_select='';
                if (th_.find('.c_call_client__cols_val_multi').size()>0){
                    th_.find('.c_call_client__cols_val_multi').each(function(){
                        txt_select+='<option value="'+$(this).data('id')+'" selected="selected">'+$(this).text()+'</option>';
                    });
                }else{
                    var tx_=(th_.text()).trim();
                    if (tx_!=''){
                        txt_select+='<option value="'+val_id+'" selected="selected">'+th_.text()+'</option>';
                    }
                    
                }
                th_.closest('div').html('<select data-col_id="'+_IN(col_id)+'" data-id="'+_IN(id_)+'" data-cl_="val" data-inc="'+_IN(inc)+'" data-col="'+_IN(col)+'" class="c_call_client__quick_change_select" multiple="multiple">'+txt_select+'</select>');
                $('.c_call_client__quick_change_select').select2({'width':'100%', allowClear: true,closeOnSelect:false,
                        ajax: {
                            url: "ajax/c_call_client.php",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    
                                    dt: '',
                                    term: params.term, 
                                    page: params.page,
                                    _t:'autocomplete_input',
                                    _col_id:col_id
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
                        
                    }).focus().blur(function() {
                      
                        var val_txt='';
                        var val_id_txt='';
                        var i=0;
                        var val_id_=new Object();
                        $('.c_call_client__quick_change_select option:selected').each(function(){
                            val_id_[i]=$(this).attr('value');
                            if (val_txt!=''){val_txt+=', ';}
                            val_txt+='<span class="c_call_client__cols_val_multi" data-id="'+val_id_[i]+'">'+$(this).text()+'</span>';
                            
                            if(val_id_txt!=''){val_id_txt+='||';}
                            val_id_txt+=val_id_[i];
                            
                            i++;
                            
                        });
                        quick_change_save(id_,cl_,inc,col,val_id_);
                        if (val_txt==''){val_txt=null_val;}
                        $('.c_call_client__quick_change_select').closest('.val').html('<span class="c_call_client__cols_val" data-col="'+_IN(col)+'" data-col_id="'+_IN(col_id)+'" data-val_id="'+val_id_txt+'" data-inc="'+_IN(inc)+'">'+val_txt+'</span>');
    
                    }).on("select2:select", function (e) {
                        $('select.quick_change option[value="'+e.params.data['id']+'"]').text(e.params.data['name']);
                        $(this).select2('open');
                    }).on("select2:unselect", function (e) {
                        $(this).select2('open');
                    }).on("select2:close", function (e) { 
                        $(this).trigger('blur');
                    });
                   $('.c_call_client__quick_change_select').select2('open');
            }
            //автокомплит
            if ($('input[name="c_questions_id_'+col_id+'"]').size()>0){
                  
                var quick_arr= new Object();
                th_.closest('div').html('<input type="text" data-col_id="'+_IN(col_id)+'" data-id="'+_IN(id_)+'" data-cl_="val" data-inc="'+_IN(inc)+'" data-col="'+_IN(col)+'" class="c_call_client__quick_change_input" value="'+_IN(txt)+'" />');
                $('.c_call_client__quick_change_input').autocomplete({
                        minLength: 0
                      }).focus(function() {
                             $('.c_call_client__quick_change_input').autocomplete("option", "source",function(request, response){
                                
                                
                                 request['dt']=new Object();
                                 $('.c_call_client__quick_change_input').closest('.ttable_tbody_tr').find('.c_call_client__cols_val').each(function(){
                                    request['dt']['c_questions_id_'+$(this).data('col_id')]=$(this).text();
                                 });
                                 request['sel']='';
                                 request['_t']='autocomplete_input';
                                 quick_arr['id']='';
                                 quick_arr['name']='';
                                 quick_arr['term']='';
                                 request['_col_id']=col_id;
                                 if (typeof jqxhr!='undefined'){jqxhr.abort();}
                                 jqxhr = $.ajax({
                                	"type": "POST",
                                	"url": "ajax/c_call_client.php",
                                	"dataType": "text",
                                	"data":request,
                                	"success":function(data,textStatus){
                                	   $('.c_call_client__quick_change_input').removeClass('ui-autocomplete-loading');
                                	   if (is_json(data)==true){
                                    	       data_n=JSON.parse(data);
                                               response( $.map( data_n.items, function(item) {
                                                    return {
                                                        label: item.name,
                                                        value: item.name,
                                                        id: item.id
                                                    }
                                                }));
                                        }else{
                                            alert_m(data,'','error','none');
                                        }
                                	}
                                });
                             }).autocomplete("option", "select",function(event, ui){
                                    quick_arr['id']=ui.item.id;
                                    quick_arr['name']=ui.item.name;
                                    quick_arr['term']=ui.item.term;
                             });
                        $('.c_call_client__quick_change_input').autocomplete("search", $('.c_call_client__quick_change_input').val()).on( "autocompleteclose", function( event, ui ) {
                                
                                    
                                    val_id_=quick_arr['id'];//новое значение
                                    var val_txt=$('.c_call_client__quick_change_input').val();
                                    if (val_id_==''){
                                        if (val_id==''){
                                            val_id_=val_txt; //новое значение без привязки к id
                                        }else{
                                            val_id_=val_id;//старое значение
                                        }
                                    }
                                    quick_change_save(id_,cl_,inc,col,val_id_);
                                    if (val_txt==''){val_txt=null_val;}
                                    $('.c_call_client__quick_change_input').closest('.val').html('<span class="c_call_client__cols_val" data-col="'+_IN(col)+'" data-col_id="'+_IN(col_id)+'" data-val_id="'+_IN(val_id_)+'" data-inc="'+_IN(inc)+'">'+val_txt+'</span>');
                                     quick_arr['id']='';
                                     quick_arr['name']='';
                                     quick_arr['term']='';
                                    
                                    
                                });
                      }).focus().keyup(function(){
                        val_id='';
                      });
           
            }
            
           
            
          ////////////////////////////////////////////////
        
        }
        $('.c_call_client_res .c_call_client__cols_val:empty, .c_call_client_res .c_call_client__comments:empty').html(null_val);//добавляем +
        
    });
    
    //Отключение синхронзации Google Drive
    $(document).delegate('.google_drive_exit','click',function(){
        var err_text='';
        var data_=new Object();
        data_['_t']='google_drive_exit';
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/c_call_client.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
        	            window.location.reload(true);
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    
});
</script>