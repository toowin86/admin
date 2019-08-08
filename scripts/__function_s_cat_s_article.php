<?php 
    //Артиклы, цены и количество от поставщиков для товаров в каталоге
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
     
?>
   function nakrutka(th_,id_){
        var err_text='';
        var data_=new Object();
        data_['_t']='nakrutka';
        data_['id']=id_;
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_cat_s_article_ajax.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        th_.find('.nakrutka_input').val(data);           
        		}
        	});
        }
    }
function convert_price_start_convert(callback){
    callback=callback || '';
    
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='convert_price_start_convert';
    data_['opt_tip']=$('.price_convert_opt').val();
    data_['i_contr']=$('.convert_price_options .i_contr').val();
        if (data_['i_contr']=='' || data_['i_contr']==null){err_text+='<p>Не указан поставщик</p>';}
    data_['f']=$('.convert_price_file_name').val();
    data_['opt1']=$('.price_convert_change_row_1 span').text();
        if (data_['opt1']==''){err_text+='<p>Не выбран столбец с артикулом</p>';}
    data_['opt2']=$('.price_convert_change_row_2 span').text();
        if (data_['opt2']==''){err_text+='<p>Не выбран столбец с ценой</p>';}
    data_['opt3']=$('.price_convert_change_row_3 span').text();
        if (data_['opt3']=='' && data_['opt_tip']=='2'){err_text+='<p>Не выбран столбец с названием</p>';}
        if (data_['opt3']==''){data_['opt3']='-1';}
    data_['opt4']=$('.price_convert_change_row_4 span').text();
        if (data_['opt4']==''){data_['opt4']='-1';}
    data_['opt5']=$('.price_convert_change_row_5 span').text();
        if (data_['opt5']==''){data_['opt5']='-1';}

    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__function_s_cat_s_article_ajax.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var cnt_upp=data_n.all_edit;
                    if (typeof cnt_upp=='undefined'){cnt_upp=0;}
                    var txt='<p>Изменено: '+cnt_upp+' значений</p>';
                    for(var i in data_n.upp_n){
                        txt+='<p><strong>'+data_n.upp_n[i]+'.</strong> старая цена: '+data_n.upp_p1[i]+', новая цена: '+data_n.upp_p2[i]+'</p>';
                    }
                    //$.arcticmodal('close');
    	            alert_m(txt,'','ok','none');
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

//форма загрузки прайса
function s_article_price_convert_form(){
    var txt='<div class="s_article_price_convert_form">';
    txt+='<h2>Загрузка прайса</h2>';
    txt+='<p>Выберете файл прайса в xls, xlsx или csv формате:</p>';
    txt+='<div class="s_article_price_convert_price_load_div" id="s_article_price_convert_price_load_div">Загрузка прайса</div>';
    txt+='</div>';
    alert_m(txt,'','info','none');
    
    
    
    //загрузка прайса
    var upload_photo = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : 's_article_price_convert_price_load_div',
        drop_element : 's_article_price_convert_price_load_div',
        url : 'ajax/__function_s_cat_s_article_ajax.php',
        chunk_size : '1mb',
        rename : true,
        dragdrop: true,
            resize: {
              width: 1500,
              height: 1500,
              crop: false,
              quality:100
        },
        filters : {
            max_file_size : '15mb',
            mime_types: [
                { title : "docs files", extensions : "xls,xlsx,csv" }
            ]
        },
    	flash_swf_url : 'js/Moxie.swf',
    	silverlight_xap_url : 'js/Moxie.xap',
        preinit : {
                Init: function(up, info) {
                
                },
                UploadFile: function(up, file) {
                   up.setOption('multipart_params', {'_t' : 's_article_price_convert'});
                }
            },
            init : {
                QueueChanged: function(up) {upload_photo.start();},
        		BeforeUpload: function(up, file) {$('#s_article_price_convert_price_load_div').html('<i class="ico ico_loading"></i> Идет загрузка...');},
                FileUploaded: function(up, file, info) {
                    //info.response
                    $('#s_article_price_convert_price_load_div').html('Загрузка прайса');
                    var data=info.response;
                    if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        var txt='';
                        var txt_top='';
                        var jj=0;
                        if (typeof data_n.t=='object'){
                            for(var i in data_n.t){
                                txt+='<div class="ttable_tbody_tr">';
                                for(var j in data_n.t[i]){
                                    txt+='<div class="ttable_tbody_tr_td">'+data_n.t[i][j]+'</div>';
                                    
                                    if (jj==0){
                                        txt_top+='<div class="ttable_thead_tr_th">'+j+'</div>';
                                    }
                            
                                }
                                if (txt_top!=''){jj=1;}
                                txt+='</div>';
                            }
                        }
                        if (txt!=''){
                            
                            txt='<div class="ttable convert_price_ttable" style="width:100%"><div class="ttable_thead"><div class="ttable_thead_tr">'+txt_top+'</div></div><div class="ttable_tbody">'+txt+'</div></div>';
                            
                            txt='<div class="convert_price_options">'
                                +'<p><select class="i_contr"></select></p>'
                                +'<div><p><span class="price_convert_change_row_1 active">1. Укажите столбец с артикулом*: <span></span></span></p>'
                                +'<p><span class="price_convert_change_row_2">2. Укажите столбец с ценой*: <span></span></span></p>'
                                +'<p><span class="price_convert_change_row_3">3. Укажите столбец с названием (в случае добавления нового товара): <span></span></span></p>'
                                +'<p><span class="price_convert_change_row_4">4. Укажите столбец с количеством (если есть): <span></span></span></p>'
                                +'<p><span class="price_convert_change_row_5">5. Укажите столбец с вашей ценой (в случае добавления нового товара): <span></span></span></p></div>'
                                +'<div>Что делаем: <select class="price_convert_opt"><option value="1">Обновить цены</option><option value="2">Добавить товар</option></select></div>'
                                +'<div><span class="btn_orange convert_price_start_convert">Начать</span></div>'
                                +'</div><input class="convert_price_file_name" type="hidden" value="'+data_n.f+'" />'
                                +txt;
                            $.arcticmodal('close');
                            alert_m(txt,'','ok','none');
                            $('.price_convert_opt').select2({'width':'100%'});
                            $('.convert_price_options .i_contr').select2({'width':'100%', 
                                ajax: {
                                    url: "ajax/__function_s_cat_s_article_ajax.php",
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
                              }});
                            
                        }
                        
                    }else{
                        alert_m(data,'','error','none');
                    }
                }
            }
        });
        upload_photo.init();
        
        upload_photo.bind('Error', function(up, err) {
            var err_text='';
            if (err.message=='File size error.'){err_text+='Превышен размер загружаемого файла!<br />';}
            if (err.message=='File extension error.'){err_text+='Не верный тип файла!<br />';}
            
            if (err_text!=''){alert_m(err_text,'','error','none');}
        });
       
    
}

function find_postav_in_this_struktura(){
    var str_arr=new Object();
    if ($('.__other__add_form select#s_struktura_id').size()>0){
        str_arr=$('.__other__add_form select#s_struktura_id').val();
    }
    else{
        str_arr=sel_in_array($('.__other__add_form #s_struktura_id .active').closest('li'),'data','id');
    }
    
    //Получаем поставщиков для текущей структуры
    if (count(str_arr)>0){// && $('.__other__add_form .__function_s_cat_s_article__res tbody tr').size()==0
        var data_=new Object();
        data_['_t']='find_postav_in_this_struktura';
        data_['str_arr']=str_arr;
        
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_cat_s_article_ajax.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
        	            if (typeof data_n.i == 'object'){
        	               var txt='';
        	               for(var i in data_n.i){
        	                   txt+=add_s_article(data_n.n[i],data_n.i[i],'','','',data_n.nn[i]);
        	               }
                            $('.__other__add_form .__function_s_cat_s_article__res .ttable_tbody').append(txt);
                            $('.__other__add_form .s_cat_s_article_price').autoNumeric('init');
                            $('.__other__add_form .s_cat_s_article_kolvo').autoNumeric('init');
                            $('.__other__add_form .s_cat_s_article_add_i_contr_select').select2({'width':'100%', 
                                ajax: {
                                    url: "ajax/__function_s_cat_s_article_ajax.php",
                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                      return {
                                        term: params.term,
                                        _t: 'i_contr_autocomplete'
                                      };
                                    },
                                    processResults: function (data) {
                                      return {
                                        results: data
                                      };
                                    },
                                    cache: true
                              }});
          
        	            }
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        
    }
    
}

function add_s_article(i_contr_name,i_contr_id,article,kolvo,price,nakrutka){
    i_contr_name=i_contr_name || '';
    i_contr_id=i_contr_id || '';
    article=article || '';
    kolvo=kolvo || '';
    price=price || '';
    nakrutka=nakrutka || '';
    var txt='';
    txt+='<div class="ttable_tbody_tr __function_s_cat_s_article_add_i_contr_tr">';
        txt+='<div class="ttable_tbody_tr_td"><select name="__function_s_cat_s_article_add_i_contr_select[]" class="s_cat_s_article_add_i_contr_select" data-placeholder="Поставщик">';
        if (i_contr_id!=''){
            txt+='<option value="'+i_contr_id+'" selected="selected">'+i_contr_name+'</option>';
        }
        txt+='</select></div>';
        txt+='<div class="ttable_tbody_tr_td"><input value="'+article+'" type="text" placeholder="Артикул" name="__function_s_cat_s_article_article[]" class="s_cat_s_article_article" /></div>';
        txt+='<div class="ttable_tbody_tr_td"><input value="'+price+'" type="text" placeholder="Цена" name="__function_s_cat_s_article_price[]" class="s_cat_s_article_price" /></div>';
        txt+='<div class="ttable_tbody_tr_td"><input value="'+kolvo+'" type="text" placeholder="Кол." name="__function_s_cat_s_article_kolvo[]" class="s_cat_s_article_kolvo" /></div>';
        txt+='<div class="ttable_tbody_tr_td"><span class="s_cat_s_article_add_i_contr_del" title="Удалить"><i class="fa fa-minus" tytle="Удалить"></i></span> <div class="s_cat_s_article_article_nakrutka_div"><input type="number" value="'+nakrutka+'" class="nakrutka_input" /> % <i class="fa fa-bar-chart" title="Пересчитать цену с накруткой '+nakrutka+'%" data-nakrutka="'+nakrutka+'"></i></div></div>';
        
    txt+='</div>';
    return txt;
}



if (tip_=='start'){ //СТАРТ - ОБЪЯВЛЯЕМ ФУНКЦИИ LIVE
    
    //Таблица
    $(document).delegate('.convert_price_ttable .ttable_tbody_tr_td','mouseover',function(){
        var th_num = $(this).index();
        $('.convert_price_ttable .ttable_tbody_tr_td').css({"background":"inherit"});
        $('.convert_price_ttable .ttable_tbody_tr').each(function(){
            $(this).children('.ttable_tbody_tr_td').each(function(td_num){
                if(td_num==th_num){
                    $(this).css({"background":"#9fec8e"});
                }
            });
        });
    });
    
    //пересчет цены
    $(document).delegate('.s_cat_s_article_article_nakrutka_div .fa','click',function(){
        var prec_=$(this).closest('.s_cat_s_article_article_nakrutka_div').find('input').val();
        var price_zakup=$(this).closest('.__function_s_cat_s_article_add_i_contr_tr').find('.s_cat_s_article_price').val();
        price_zakup=(price_zakup.replace(' ',''))-0;
        var price_new=(price_zakup*prec_/100)+(price_zakup-0);
        $('.__other__add_form').find('input[name="price"]').val(price_new);
    });
    
    
    //выбор столбцов для прайсов
    $(document).delegate('.convert_price_ttable .ttable_tbody_tr_td','click',function(){
        var th_num = $(this).index();
        var nom = $('.convert_price_ttable .ttable_thead_tr_th:eq('+th_num+')').text();
        var th_=$('.convert_price_options').find('.active');
        th_.find('span').text(nom);
        th_.removeClass('active');
        th_.closest('p').next('p').find('span').addClass('active');
    });
    
    $(document).delegate('.convert_price_options>div:first p span','click',function(){
        $('.convert_price_options span').removeClass('active');
        $(this).addClass('active').find('span').text('');
    });
    
    
    //Загрузка прайсов
    $(document).delegate('.s_article_price_convert','click',function(){
        s_article_price_convert_form();
    });
    
    //Начало конвертации прайса
    $(document).delegate('.convert_price_start_convert','click',function(){
        convert_price_start_convert();
    });
    
    // быстрое изменение цены
    $(document).delegate('.s_article_price_quick','click',function(){
        var th_=$(this).closest('.s_article_price_quick_div');
        var sum=$(this).text();
        var id=th_.data('id');
        th_.html('<input type="text" class="s_article_price_quick_input" value="'+_IN(sum)+'" />');
        th_.find('.s_article_price_quick_input').focus().select().blur(function(){
            var th_2=$(this);
            var err_text='';
            var data_=new Object();
            data_['_t']='s_article_price_quick';
            data_['sum']=$(this).val();
            data_['id']=id;
            
            if (err_text!=''){alert_m(err_text,'','error','none');}
            else{
            	$.ajax({
            		"type": "POST",
            		"url": "ajax/__function_s_cat_s_article_ajax.php",
            		"dataType": "text",
            		"data":data_,
            		"success":function(data,textStatus){
            	        if (is_json(data)==true){
            	            data_n=JSON.parse(data);
            	            th_.html('<span class="s_article_price_quick">'+data_['sum']+'</span>');
            			}
            			else{
            				alert_m(data,'','error','none');
            			}            
            		}
            	});
            }
        });
    });
    
    $(document).delegate('.s_article_price_quick_input','keyup',function(e){
        if (e.which==13){
            $(this).trigger('blur');
        }
    });  
    $(document).delegate('.s_article_find_text','keyup',function(e){
        if (e.which==13){
            find();
        }
    });    
    
    
    $(document).delegate('.ico_search_postav','click',function(){
        find_postav_in_this_struktura();
    });
    
 
    
    //Добавление новой цены
    $(document).delegate('.__other__add_form .__function_s_cat_s_article_add_new','click',function(){
        var txt=add_s_article();
        $('.__other__add_form .__function_s_cat_s_article__res .ttable_tbody').append(txt);
        
        $('.__other__add_form .s_cat_s_article_price').autoNumeric('init');
        $('.__other__add_form .s_cat_s_article_kolvo').autoNumeric('init');
        $('.__function_s_cat_s_article__res .ttable_tbody .ttable_tbody_tr:last-child  .s_cat_s_article_add_i_contr_select').select2({'width':'100%', 
            ajax: {
                url: "ajax/__function_s_cat_s_article_ajax.php",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    term: params.term,
                    _t: 'i_contr_autocomplete'
                  };
                },
                processResults: function (data, page) {
                  return {
                    results: data
                  };
                },
                cache: true
          }}).change(function(){
            var th_=$(this).closest('.__function_s_cat_s_article_add_i_contr_tr');
            nakrutka(th_,$(this).val());
          });
        
    });
    
    //Удаление
    $(document).delegate('.__other__add_form .s_cat_s_article_add_i_contr_del','click',function(){
        $(this).closest('.ttable_tbody_tr').detach();
    });
    
}//end start
//ФИЛЬТР
// *******************************************************************************
// *******************************************************************************
else if (tip_=='fillter'){ //ФИЛЬТР ПО ТОВАРУ
   
    var txt='<input type="text" name="s_article_find_text" placeholder="Найти" class="s_article_find_text" value="" /> <span class="fa fa-book s_article_price_convert" title="Загрузить прайс"></span>';
    
    return txt;
}
//ПОИСК
// *******************************************************************************
// *******************************************************************************
else if (tip_=='find'){ //ПОИСК ПО ТОВАРУ
    var text='';
    
    if ((th_.split('||').length - 1)>0){
        var arr_s_article = th_.split('||');
    }
    else{
        var arr_s_article = new Array();
        arr_s_article[0]=th_;
    }
    var max_l=0;
    for(var i in arr_s_article) 
    {
        
        var s_article_cur=arr_s_article[i];
        if ((s_article_cur.split('::').length - 1)>0){
            var s_article_a=s_article_cur.split('::');
            text+='<p><strong>'+s_article_a[0]+'</strong> ('+s_article_a[1]+'): <div data-id="'+s_article_a[3]+'" class="s_article_price_quick_div"><span class="s_article_price_quick">'+s_article_a[2]+'</span></div></p>';
        }
    }
    return '<div class="s_article_find__div">'+text+'</div>';
    
}
// *******************************************************************************
// *******************************************************************************
else if (tip_=='add'){//ОТКРЫТИЕ ОКНА ДОБАВЛЕНИЯ ТОВАРА
   
}
// *******************************************************************************
// *******************************************************************************
else if (tip_=='change'){//ОТКРЫТИЕ ОКНА ИЗМЕНЕНИЯ ТОВАРА
    var val_=th_._d['s_article'];
    var txt='';
    for (var i in val_){
        txt+=add_s_article(val_[i][0],val_[i][1],val_[i][2],val_[i][3],val_[i][4],val_[i][5]);
    }
    if (txt!=''){
        $('.__other__add_form .__function_s_cat_s_article__res .ttable_tbody').append(txt);
        $('.__other__add_form .s_cat_s_article_price').autoNumeric('init');
        $('.__other__add_form .s_cat_s_article_kolvo').autoNumeric('init');
        $('.__other__add_form .s_cat_s_article_add_i_contr_select').select2({'width':'400px', 
            ajax: {
                url: "ajax/__function_s_cat_s_article_ajax.php",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    term: params.term,
                    _t: 'i_contr_autocomplete'
                  };
                },
                processResults: function (data) {
                  return {
                    results: data
                  };
                },
                cache: true
          }});
    }
    find_postav_in_this_struktura();
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='save'){
    $('.__other__add_form .__function_s_cat_s_article__res .ttable_tbody .ttable_tbody_tr').each(function(){
        if ($(this).find('.s_cat_s_article_add_i_contr_select').size()>0){
            if ($(this).find('.s_cat_s_article_add_i_contr_select').val()=='' || $(this).find('.s_cat_s_article_article').val()==''){
                $(this).detach();
            }
        }
    });
    //++++
    //сохраняет автоматом т.к. передалются в select -> name функцией seriallize
    return '';
}
// *******************************************************************************
// *******************************************************************************
// парсим
else if(tip_=='parser'){

    txt='';
    return txt;
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
