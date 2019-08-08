<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
?><script type="text/javascript">


//Создаем структуру
function create_tree(pid,str_arr){
    var txt='';
    if (typeof str_arr.i=='object'){
        for (var i in str_arr.i){
            if (str_arr.p[i]==pid){
                
                var txt_cnt=''; var cl_='null_items'; if (str_arr.c[i]>0){txt_cnt=' <span>('+str_arr.c[i]+')</span>';cl_='not_null_items';}
                
                txt+='<li class="m_tovar_s_str_li '+cl_+'" data-id="'+str_arr.i[i]+'">';
                txt+='<span class="m_tovar_s_cat_block">';
                if (str_arr.c[i]-0>0){
                    txt+='<span class="m_tovar_s_cat_div">';
                        txt+='<span class="m_tovar_s_cat_ico"><i class="fa fa-plus"></i></span>';
                    txt+='</span>';
                }
                txt+='<span class="m_tovar_s_struktura_name">'+str_arr.n[i];
                //if (str_arr.c[i]>0){txt+=txt_cnt;}
                txt+='<span></span>';
                txt+='</span>';
                txt+='</span>';
                if (str_arr.c[i]-0>0){
                    txt+='<div class="m_tovar_s_cat_res"></div>';
                }
                txt+=create_tree(str_arr.i[i],str_arr);
                txt+='</li>';
                
            }
        }
    }
    if (txt!=''){txt='<ul class="m_tovar_str">'+txt+'</ul>';}
    return txt; 
}
//получаем количество элементов у структуры
function get_cnt_items_in_tree(callback){
    callback = callback || '';
    var err_text='';
    var data_=$('.m_tovar_fillter form').serializeObject();
    data_['_t']='get_cnt_items_in_tree';
    data_['term']=$('.m_zakaz_s_cat_m_tovar_fillter__term').val();
    data_['status_tovar']=sel_in_array($('.m_zakaz_s_cat_m_tovar_fillter__status li.active'),'data','val');
    data_['id']='';
    //перебор по веткам с товарами
    $('.not_null_items').each(function(){
        if (data_['id']!=''){data_['id']+=',';}
        data_['id']+=$(this).data('id');
    });

    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat_m_tovar.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    		
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    if (typeof data_n.s == 'object' && typeof data_n.s.i == 'object'){
                        for(var i in data_n.s.i){
                            $('.not_null_items[data-id="'+data_n.s.i[i]+'"]').find('.m_tovar_s_struktura_name>span').html(' ('+data_n.s.c[i]+')');
                        }
                    }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
} 

//Загружаем структуру
function m_tovar_s_struktura_find(callback){
   
    callback = callback || '';
    var err_text='';
    var data_=$('.m_tovar_fillter form').serializeObject();
    data_['_t']='m_tovar_s_struktura_find';
    data_['term']=$('.m_zakaz_s_cat_m_tovar_fillter__term').val();
    data_['status_tovar']=sel_in_array($('.m_zakaz_s_cat_m_tovar_fillter__status li.active'),'data','val');
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$('.m_tovar_res').html('<div class="m_tovar_loading"><span class="ico ico_loading"></span> Загрузка</div>');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat_m_tovar.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			$('.m_tovar_loading').detach();
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    if (typeof data_n.s == 'object' && typeof data_n.s.i == 'object'){
    	               txt=create_tree(0,data_n.s);
                       $('.m_tovar_res').html(txt);
                       get_cnt_items_in_tree();
                       if (typeof callback=='function'){callback(data_n);}
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

function create_s_cat(s_cat_arr){
    var txt='';
    if (typeof s_cat_arr.i=='object'){
        for(var i in s_cat_arr.i){
            txt+='<div class="m_tovar_s_cat_item" data-id="'+s_cat_arr.i[i]+'">';
                txt+='<span class="m_tovar_s_cat_func"><input type="checkbox" name="chk_'+s_cat_arr.i[i]+'" /></span>';
                txt+='<span class="m_tovar_s_cat_name">'+s_cat_arr.n[i]+'</span>';
                txt+='<span class="m_tovar_s_cat_prop">'+s_cat_arr.pr[i]+'</span>';
                txt+='<span class="m_tovar_s_cat_price"><span>'+s_cat_arr.p[i]+'</span> <i class="fa fa-ruble"></i></span>';
                txt+='<span class="m_tovar_s_cat_barcode">'+s_cat_arr.b[i]+'</span>';
                txt+='<span class="m_tovar_s_cat_data"><a href="?inc=m_postav&nomer='+s_cat_arr.pi[i]+'">№'+s_cat_arr.pi[i]+' от '+s_cat_arr.d[i]+'</a></span>';
                txt+='<span class="m_tovar_s_cat_sale">';
                if (s_cat_arr.zi[i]!=''){
                    txt+='<a href="?inc=m_zakaz&nomer='+s_cat_arr.zi[i]+'">Заказ №'+s_cat_arr.zi[i]+' от '+s_cat_arr.zd[i]+'</a>';
                    txt+='<p>'+s_cat_arr.zp[i]+' руб.</p>';
                }
                txt+='</span>';
            txt+='</div>';
        }
    }
    
    return txt;
}

//Загружаем товар
function find_s_cat(th_,callback){
    
    callback=callback || '';
    var err_text='';
    var data_=$('.m_tovar_fillter form').serializeObject();
    data_['_t']='find_s_cat';
    data_['s_struktura_id']=th_.data('id');
    data_['term']=$('.m_zakaz_s_cat_m_tovar_fillter__term').val();
    data_['status_tovar']=sel_in_array($('.m_zakaz_s_cat_m_tovar_fillter__status li.active'),'data','val');
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat_m_tovar.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof data_n.s == 'object' && typeof data_n.s.i == 'object'){
    	               txt=create_s_cat(data_n.s);
                       th_.find('>.m_tovar_s_cat_block .m_tovar_s_cat_ico').html('<i class="fa fa-minus"></i>');
                       th_.find('>.m_tovar_s_cat_block .m_tovar_s_cat_ico').addClass('m_tovar_s_cat_ico_minus').removeClass('m_tovar_s_cat_ico');
                       th_.find('>.m_tovar_s_cat_res').html(txt);
                       if (typeof callback=='function'){callback(data_n);}
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
    
    
}

//Функция проверки выделения
function chk_select(){
    if ($('.m_tovar_s_cat_func input[type="checkbox"]:checked').length>0){
        $('.top_com__m_zakaz_create').closest('li').show();
    }else{
        $('.top_com__m_zakaz_create').closest('li').hide();
    }
}

//Создаем заказ
function m_zakaz_create(){
    var err_text='';
    var data_=new Object();//$('.****').serializeObject();
    data_['_t']='m_zakaz_create';
    data_['m_tovar_id']='';
    $('.m_tovar_s_cat_func input[type="checkbox"]:checked').each(function(){
        var th_=$(this);
        if (data_['m_tovar_id']!=''){data_['m_tovar_id']+=',';}
        data_['m_tovar_id']+=th_.closest('.m_tovar_s_cat_item').data('id');
    });
    
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz_s_cat_m_tovar.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            alert_m('Создан заказ №'+data_n.m_zakaz_id);
                    var th_=$('.m_tovar_s_cat_item:visible:first').closest('.m_tovar_s_str_li');
                    find_s_cat(th_,function(){});
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

    m_tovar_s_struktura_find();//выводим корневую структуру
    chk_select();//проверка выбранных товаров
    
    //Загрузка товара из указанной ветки
    $(document).delegate('.m_tovar_s_cat_ico','click',function(){
        var th_=$(this).closest('.m_tovar_s_str_li');
        find_s_cat(th_,function(){});
    });
    
    //Закрытие ветки товаров
    $(document).delegate('.m_tovar_s_cat_ico_minus','click',function(){
        var th_=$(this).closest('.m_tovar_s_str_li');
        th_.find('.m_tovar_s_cat_res').html('');
        th_.find('.m_tovar_s_cat_ico_minus').html('<i class="fa fa-plus"></i>').addClass('m_tovar_s_cat_ico').removeClass('m_tovar_s_cat_ico_minus');
    });
    
    //загрузка всего товара
    $(document).delegate('.m_zakaz_s_cat_m_tovar_fillter__open_btn','click',function(){
        $('.not_null_items').each(function(){
            $(this).find('.m_tovar_s_cat_ico').trigger('click');
        });
    });
    
    //Фильтр по статусам
    $(document).delegate('.m_zakaz_s_cat_m_tovar_fillter__status li','click',function(){
        var th_=$(this);
        if (((th_.attr('class')).split('active').length - 1)>0){
            th_.removeClass('active');
        }else{
            th_.addClass('active');
        }
        m_tovar_s_struktura_find();
    });
    
    //Выделение чекбокса
    $(document).delegate('.m_tovar_s_cat_func input[type="checkbox"]','change',function(){
        chk_select();
    });
    
    //Списание товара
    $(document).delegate('.top_com__m_zakaz_create','click',function(){
        m_zakaz_create();
    });
    
    //Контрагент -поставщик
    $('select[name="m_zakaz_s_cat_m_tovar_i_contr_postav"]').select2({'width':'100%', allowClear: true,
        ajax: {
            url: "ajax/m_postav.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
              return {
                term: params.term, // search term
                _t: 'i_contr_autocomplete'
              };
            },
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
      }}).change(function(){
        m_tovar_s_struktura_find();//выводим корневую структуру
      });
    //Контрагент -покупатель
    $('select[name="m_zakaz_s_cat_m_tovar_i_contr_pokup"]').select2({'width':'100%', allowClear: true,
                    ajax: {
                        url: "ajax/m_zakaz.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                          return {
                            term: params.term, // search term
                            _t: 'i_contr_autocomplete'
                          };
                        },
                        processResults: function (data) {
                          return {
                            results: data
                          };
                        },
                        cache: true
                  }}).change(function(){
        m_tovar_s_struktura_find();//выводим корневую структуру
      });
        
      //Скрывать пустые
    $(document).delegate('.m_zakaz_s_cat_m_tovar_fillter__remove_btn','click',function(){
        if ($('.null_items>.m_tovar_s_cat_block').css('display')=='none'){
            $('.null_items>.m_tovar_s_cat_block').css({'display':'block'});
            $('.m_zakaz_s_cat_m_tovar_fillter__remove_btn').text('Скрыть пустые');
        }
        else{
            $('.null_items>.m_tovar_s_cat_block').css({'display':'none'});
            $('.m_zakaz_s_cat_m_tovar_fillter__remove_btn').text('Показать пустые');
        }
        
    });
    
    //поиск по назанию товара
    $(document).delegate('.m_zakaz_s_cat_m_tovar_fillter__term','keydown',function(e){
        if (e.which=='13'){
            e.preventDefault();
            m_tovar_s_struktura_find();//выводим корневую структуру
        }
    });
    
});
</script>