<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
if (typeof s_prop_template_color!='function'){
    
    //Добавление в выпадающий список изображений
    function s_prop_template_color (state) {
    
     if (!state.id) { return state.text; }
     var img_=$('.__other__add_form .__function_s_cat_prop__select option[value="'+state.id+'"]').attr('data-img');
        if (typeof img_=='undefined'){img_='';}
        
        var txt_=' <span>' + state.text + '</span>';
        if (img_!=''){
            img_='<div class="s_prop_val_opt_img_div"><img src="'+img_+'" /></div>';
            txt_=' <span>' + state.text + '</span>'+img_;
        }
        
      var $state = $(
       txt_
      );
      return $state;
    };
}
if (typeof chk_load_img_for_prop!='function'){
    
    //Добавление в выпадающий список изображений
    function chk_load_img_for_prop() {
        
        var i=$('.s_prop_val_load_img[id!=""]').length-0+1;
      
        $('.s_prop_val_load_img').each(function(){
         
            if (typeof $(this).attr('id')=='undefined'){
                var th_=$(this);
                
                th_.attr('id','s_prop_val_load_img_'+i);
             
                //Скрипт для загрузки фоток для товара
                var upload_photo_prop = new plupload.Uploader({
                    runtimes : 'html5,flash,silverlight,html4',
                	browse_button : 's_prop_val_load_img_'+i,
                    drop_element : 's_prop_val_load_img_'+i,
                    url : 'ajax/__function_s_cat_prop_ajax.php',
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
                            {title : "Image files", extensions : "jpg,jpeg,gif,png"}
                        ]
                    },
                	flash_swf_url : 'js/Moxie.swf',
                	silverlight_xap_url : 'js/Moxie.xap',
                    preinit : {
                            Init: function(up, info) {            
                            },
                            UploadFile: function(up, file) {
                               up.setOption('multipart_params', {'_t' : 'upload_s_prop','inc':'s_prop_val'});
                            }
                        },
                        init : {
                            QueueChanged: function(up) {upload_photo_prop.start();},
                    		BeforeUpload: function(up, file) {
                                th_.closest('.s_prop_val_div_item').find('.s_prop_val_res_img').prepend('<div class="loading_img_span"><div class="cssload-container"><div class="cssload-loading"><i></i><i></i><i></i><i></i></div></div> загрузка </div>');
                            },
                            FileUploaded: function(up, file, info) {
                                $('.loading_img_span').detach();
                                
                                var num=th_.closest('.s_prop_val_div_item').find('.photo_res__item').length;
                                    th_.closest('.s_prop_val_div_item').find('input[name="s_prop_val__val_img"]').val(info.response);
                                    th_.closest('.s_prop_val_div_item').find('.s_prop_val_res_img').html('<div data-num="'+num+'" class="photo_res__item"> <a rel="load_photo" href="../i/s_prop_val/temp/'+info.response+'" class="zoom"><img class="photo_res__item_image" src="../i/s_prop_val/temp/'+info.response+'" /></a>'+'</div>');
                                    $('.zoom').fancybox();
                            }
                        }
                        });
                    upload_photo_prop.init();
                    
                    upload_photo_prop.bind('Error', function(up, err) {
                        var err_text='';
                        if (err.message=='File size error.'){err_text+='Превышен размер загружаемого файла!<br />';}
                        if (err.message=='File extension error.'){err_text+='Не верный тип файла!<br />';}
                        
                        if (err_text!=''){alert_m(err_text,'','error','none');}
                    });
            
            
                i++;
            }
        });
        
    }
}
if (tip_=='start'){ //СТАРТ - ОБЪЯВЛЯЕМ ФУНКЦИИ LIVE

    var prop_add_txt='<form class="__function_s_cat_prop__prop_add_form">';
    prop_add_txt+='<div><p>Введите название свойства:</p></div>';
    prop_add_txt+='<div><input type="text" name="s_prop_name" placeholder="Введите название свойства" /></div>';
    prop_add_txt+='<div><p>Укажите тип свойства:</p></div>';
    prop_add_txt+='<div><select name="s_prop_tip">';
        prop_add_txt+='<option value="1">Список</option>';
        prop_add_txt+='<option value="2">Авто добавление</option>';
        prop_add_txt+='<option value="3">Авто добавление (в поиске от и до)</option>';
    prop_add_txt+='</select></div>';
    prop_add_txt+='<div class="s_prop_val_div_all">';
        prop_add_txt+='<div class="s_prop_val_div_item">';
            prop_add_txt+='<div><p>Укажите значения свойства:</p></div>';
            prop_add_txt+='<div class="s_prop_val">';
                prop_add_txt+='<div><textarea name="s_prop_val__val" placeholder="Укажите значение свойства"></textarea></div>';
                prop_add_txt+='<div class="s_prop_val_img"><input type="hidden" name="s_prop_val__val_img" value="" /><div class="s_prop_val_load_img"><i class="fa fa-image"></i> Загрузить фотографию</div><div class="s_prop_val_res_img"></div></div>';
            prop_add_txt+='</div>';
        prop_add_txt+='</div>';
    prop_add_txt+='</div>';
    prop_add_txt+='<div class="s_prop_val__add_new_val_div"><span class="s_prop_val__add_new_val btn_gray">Добавить еще значение</span></div>';
    prop_add_txt+='<div class="__function_s_cat_prop__com"><center><span class="btn_orange __function_s_cat_prop__prop_add_save">Сохранить свойство</span></center></div>';
    prop_add_txt+='</form>';
    
    $(document).delegate('.__function_s_cat_prop__add_new_prop','click',function(){
        alert_m(prop_add_txt,'','add','none','',function(){
            chk_load_img_for_prop();//проверка для загрузки фото
        });
    });
    
    //Поиск свойств в данной ветке структуры multiselect
    $(document).delegate('select#s_struktura_id','change',function(){
   
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='prop_view_from_struktura';
        data_['nomer']=th_.val();
        
    	$.ajax({
    		"type": "POST",
    		"url":  "ajax/__function_s_cat_prop_ajax.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if(typeof data_n.id!='undefined'){
    	             
    	               $('.__other__add_form .__function_s_cat_prop__res .ttable_tbody_tr').each(function(){
    	                   if ($(this).find('select option:selected').length==0){
    	                       $(this).hide();
    	                   }
                           else if($(this).find('input').length>0){
                                if ($(this).find('input').val()==''){
    	                           $(this).hide();
    	                       }
                           }
    	               });
    	               for (var i in data_n.id){
    	                   $('.__other__add_form .__function_s_cat_prop__res').find('.ttable_tbody_tr[data-id="'+data_n.id[i]+'"]').show();
    	               }
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    });
    
    
    //Поиск свойств в данной ветке структуры chkbox
    $(document).delegate('div#s_struktura_id','click',function(){
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='prop_view_from_struktura';
        data_['nomer']=sel_in_array($('div#s_struktura_id .active').closest('li'),'data','id');
        
    	$.ajax({
    		"type": "POST",
    		"url":  "ajax/__function_s_cat_prop_ajax.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if(typeof data_n.id!='undefined'){
    	             
    	               $('.__other__add_form .__function_s_cat_prop__res .ttable_tbody_tr').each(function(){
    	                  
    	                   if ($(this).find('select option:selected').size()==0){
    	                       $(this).hide();
    	                   }
                           else if($(this).find('input').size()>0){
                                if ($(this).find('input').val()=='' && $(this).find('select').size()==0){
    	                           $(this).hide();
    	                       }
                           }
    	               });
    	               for (var i in data_n.id){//
                            $('.__other__add_form .__function_s_cat_prop__res').find('.ttable_tbody_tr[data-id="'+data_n.id[i]+'"]').show();
    	               }
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
       
    });

    //Добавляем новое значение свойства в форму добавления свойства
    $(document).delegate('.s_prop_val__add_new_val','click',function(){
        var prop_add='';
        prop_add+='<div class="s_prop_val_div_item">';
            prop_add+='<div><p>Укажите значения свойства:</p></div>';
            prop_add+='<div class="s_prop_val">';
                prop_add+='<div><textarea name="s_prop_val__val" placeholder="Укажите значение свойства"></textarea></div>';
                prop_add+='<div class="s_prop_val_img"><input type="hidden" name="s_prop_val__val_img" value="" /><div class="s_prop_val_load_img"><i class="fa fa-image"></i> Загрузить фотографию</div><div class="s_prop_val_res_img"></div></div>';
            prop_add+='</div>';
        prop_add+='</div>';
        $('.s_prop_val_div_all').append(prop_add);
        chk_load_img_for_prop();//проверка для загрузки фото
    });

    //Сохранение нового свойства
    $(document).delegate('.__function_s_cat_prop__prop_add_save','click',function(){
        var th_=$(this);
        $('textarea[name=s_prop_val__val]').each(function(){
            if ($(this).val()==''){$(this).closest('div').detach();}
        });
        var data_=$('.__function_s_cat_prop__prop_add_form').serializeObject();
        var err_txt='';
        data_['_t']='s_prop_add';
        
        if (data_['s_prop_name']==''){err_txt+='Укажите название свойства!<br />';}
        if ($('textarea[name=s_prop_val__val]').size()==0){err_txt+='Укажите хотя бы одно значение свойства!<br />';$('.__function_s_cat_prop__prop_add_form .s_prop_val').append('<div><textarea name="s_prop_val__val"></textarea></div>');}
        
        if (err_txt!=''){alert_m(err_txt,'','error','none');}
        else{
            th_.closest('.__function_s_cat_prop__com').find('center').html('<span class="ico ico_loading"></span> Добавление...');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/__function_s_cat_prop_ajax.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   $('.__function_s_cat_prop__com').find('center').html('<span class="btn_orange __function_s_cat_prop__prop_add_save">Сохранить свойство</span>');
            	   if (is_json(data)==true){
            	        var txt='';
                        data_n=JSON.parse(data);
                        var prop_val=data_n.prop_val;
                        var prop_val_img=data_n.prop_val_img;
                        var prop_val_status=data_n.prop_val_status;
                        var s_prop_id=data_n.prop_id;
                        
                        var err_text='';
                        txt+='<div class="ttable_tbody_tr" data-tip="'+data_['s_prop_tip']+'" data-id="'+s_prop_id+'">';
                        txt+='<div class="ttable_tbody_tr_td __function_s_cat_prop__name">'+data_['s_prop_name']+'</div>';
                        txt+='<div class="ttable_tbody_tr_td __function_s_cat_prop__val">';
                        
                        if (data_['s_prop_tip']=='1'){//Список
                            txt+='<select name="__function_s_cat_prop__select" class="__function_s_cat_prop__select" data-placeholder="'+data_['s_prop_name']+'" multiple><option></option>';
                               
                                for(var i in prop_val_status ){
                                    
                                    if (prop_val_status[i]=='ok'){
                                        var img_='';
                                        if (prop_val_img[i]!='' && typeof prop_val_img[i]!='undefined'){
                                            img_='../i/s_prop_val/original/'+prop_val_img[i];
                                        }
                                        
                                        txt+='<option value="'+i+'" selected="selected" data-img="'+img_+'">'+prop_val[i]+'</option>'+"\n";
                                    }else{
                                        err_text+='Свойство со значением "'+prop_val[i]+'" есть в базе!<br />';
                                    }
                                }
                            txt+='</select>';
                        }else if(data_['s_prop_tip']=='2'){//Авто добавление
                            txt+='<input type="text" name="__function_s_cat_prop__input'+s_prop_id+'" class="__function_s_cat_prop__input" placeholder="'+data_['s_prop_name']+'" value="'+data_['s_prop_val__val']+'" />';
                        }
                        else if(data_['s_prop_tip']=='3'){//Авто добавление (в поиске от и до)
                            txt+='<input type="text" name="__function_s_cat_prop__input'+s_prop_id+'" class="__function_s_cat_prop__input" placeholder="'+data_['s_prop_name']+'" value="'+data_['s_prop_val__val']+'" />';
                        }
                        
                        txt+='</div>';
                        txt+='<div class="ttable_tbody_tr_td __function_s_cat_prop__com_prop">';
                            txt+='<span class="ico ico_add __function_s_cat_prop__add_val" title="Добавить значение свойства"></span>';
                            //txt+='<span class="ico ico_edit __function_s_cat_prop__change" title="Изменить свойство"></span>';
                            txt+='<span class="ico ico_minus __function_s_cat_prop__noview" title="Скрыть свойство"></span>';
                            //txt+='<span class="ico ico_del __function_s_cat_prop__del" title="Удалить свойство"></span>';
                        txt+='</div>';
                        txt+='</div>';
                        
      
                        //СЕЛЕКТ
                        
                        if (err_text==''){
                            alert_m('Свойство добавлено!',function(){
                            
                                $('.preload .__other__add .s_prop_view').append('<option value="'+s_prop_id+'">'+data_['s_prop_name']+'</option>');
                                $('.preload .__other__add .__function_s_cat_prop__res').append(txt);
                                    $('.preload .__other__add .__function_s_cat_prop__res select option').removeAttr('selected');
                                $('.__other__add_form .__function_s_cat_prop__res').append(txt);
                                
                                //АВТОЗАПОЛНЕНИЕ
                                var prop_id=0;
                                $('.__other__add_form .__function_s_cat_prop__input').autocomplete({
                                    minLength: 0,
                                    appendTo: ".__other__add_form",
                                    source: function(request, response){
                                                 request['_t']='autocomplete_prop';
                                                 request['_prop_id']=prop_id;
                                                 
                                                 $.ajax({
                                                	"type": "POST",
                                                	"url": "ajax/__function_s_cat_prop_ajax.php",
                                                	"dataType": "text",
                                                	"data":request,
                                                	"success":function(data,textStatus){
                                                	   if (is_json(data)==true){
                                                    	       data_n=JSON.parse(data);
                                                               if (count(data_n)>0){
                                                                response(data_n);
                                                               }
                                                        }else{
                                                            alert_m(data,'','error','none');
                                                        }
                                                	}
                                                });
                                             }
                                  }).focus(function() {
                                        prop_id=$(this).closest('.ttable_tbody_tr').data('id');
                                       $(this).autocomplete("search", $(this).val());
                                  });
                                  
                                  
                                  
                                $('.__other__add_form .__function_s_cat_prop__res .__function_s_cat_prop__select:last-child').select2({allowClear: true,'width':'100%',templateResult: s_prop_template_color,templateSelection: s_prop_template_color}).on("select2:select", function (e) {$(this).select2('open');})
                                    .on("select2:unselect", function (e) {$(this).select2('open');}); //chk;
                            },'ok');
                        }
                        else{
                            alert_m(err_text,'','error','none');
                        }
                    }
                    else{
                        alert_m(data,'','error','none');
                    }
                }
             });
        }
    
    });

    
    // удалить свойство
    $(document).delegate('.__other__add_form .__function_s_cat_prop__del','click',function(){
        var txt='<div class="__function_s_cat_prop__del_question"><center>';
        txt+='<p>Вы точно хотите удалить данное свойство?</p>';
        txt+='<p>Для данного свойства будут удалены все связи с каталогом!</p><br />';
        txt+='<p><span data-id="'+$(this).closest('.ttable_tbody_tr').data('id')+'" class="btn_orange __function_s_cat_prop__del_ok">Удалить!</span></p>';
        txt+='</center></div>';
        alert_m(txt,'','info','none');
        
        
    });
    //скрываем
    $(document).delegate('.__function_s_cat_prop__noview','click',function(){
        $(this).closest('.ttable_tbody_tr').find('input,textarea,select').val('');
        $(this).closest('.ttable_tbody_tr').find('select option').removeAttr('selected');
        $(this).closest('.ttable_tbody_tr').find('input[type="checkbox"]').removeAttr('checked');
        $(this).closest('.ttable_tbody_tr').find('select').select2({'width':'100%',templateResult: s_prop_template_color,templateSelection: s_prop_template_color}).on("select2:select", function (e) {$(this).select2('open');})
              .on("select2:unselect", function (e) {$(this).select2('open');}); //chk
        
        $(this).closest('.ttable_tbody_tr').hide();
    });
    //Подтвержение удаления
    $(document).delegate('.__function_s_cat_prop__del_ok','click',function(){
        $('.__function_s_cat_prop__del_question').arcticmodal('close');
        var data_=new Object();
        data_['_t']='del_prop';
        data_['nomer']=$(this).data('id');
        $('.__function_s_cat_prop__res .ttable_tbody_tr[data-id='+data_['nomer']+']').detach(); // удаляем везде, в том числе и в исходном DIV preload
        $.ajax({
            	"type": "POST",
            	"url": "ajax/__function_s_cat_prop_ajax.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   if (is_json(data)==true){
            	       data_n=JSON.parse(data);
            	       //удаляем свойство
                       // форма, фильтр, окно добавлния
                    }else{
                        alert_m(data,'','error','none');
                    }
                }
        });
        
    });
    
    // Добавить значение свойства
    $(document).delegate('.__other__add_form .__function_s_cat_prop__add_val','click',function(){
        var txt='<form class="__function_s_cat_prop__add_prop_val_question" data-id="'+$(this).closest('.ttable_tbody_tr').data('id')+'">';
       
        txt+='<div class="s_prop_val_div_all">';
            txt+='<div class="s_prop_val_div_item">';
                txt+='<div><p>Укажите значения свойства "'+$(this).closest('.ttable_tbody_tr').find('.__function_s_cat_prop__name').text()+'":</p></div>';
                txt+='<div class="s_prop_val">';
                    txt+='<div><textarea name="s_prop_val__val" placeholder="Укажите значение свойства"></textarea></div>';
                    txt+='<div class="s_prop_val_img"><input type="hidden" name="s_prop_val__val_img" value="" /><div class="s_prop_val_load_img"><i class="fa fa-image"></i> Загрузить фотографию</div><div class="s_prop_val_res_img"></div></div>';
                txt+='</div>';
            txt+='</div>';
        txt+='</div>';
        txt+='<p><span class="s_prop_val__add_new_val btn_gray">Добавить еще значение</span></p>';
        txt+='<div><center><span class="btn_orange __function_s_cat_prop__add_prop_val_ok">Добавить</span></center></div>';
        txt+='</form>';
        
        alert_m(txt,'','add','none','');
        chk_load_img_for_prop();//проверка для загрузки фото
        $('form.__function_s_cat_prop__add_prop_val_question textarea[name=s_prop_val__val]').focus();
    });
    
    //Сохранить добавление свойства
    $(document).delegate('.__function_s_cat_prop__add_prop_val_ok','click',function(){
        $('.__function_s_cat_prop__add_prop_val_question textarea[value=""]').detach();
        var err_text='';
        var th_=$(this);
        var data_=$('.__function_s_cat_prop__add_prop_val_question').serializeObject();
        data_['_t']='add_prop_val';
        data_['nomer']=th_.closest('form').data('id');
        if (typeof data_['s_prop_val__val'] == 'undefined'){err_text+='Заполнине минимум одно свойство!<br />';}
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            th_.closest('center').html('<span class="ico ico_loading"></span> Добавление...');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/__function_s_cat_prop_ajax.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
         	        if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        var id_=0;
                        var prop_val_=data_n.prop_val;
                        var prop_val_img_=data_n.prop_val_img;
                        var prop_val_status=data_n.prop_val_status;
            	        //добавляем свойство
                        var text_='';
                        var err_text='';
                        for (var id_ in prop_val_){
                            if (prop_val_status[id_]=='ok'){
                                var img_='';
                                if (prop_val_img_[id_]!='' && typeof prop_val_img_[id_]!='undefined'){
                                    img_='../i/s_prop_val/original/'+prop_val_img_[id_];
                                }
                                        
                                text_+='<option value="'+id_+'" data-img="'+img_+'">'+prop_val_[id_]+'</option>';
                            }else{
                                err_text+='Свойство "'+prop_val_[id_]+'" уже имеется - добавлено не было!<br />';
                            }
                        }
                        if (err_text!=''){alert_m(err_text,'','error','none');}
                        if (text_!=''){
                           
                            // форма, фильтр, окно добавлния
                            $('.__other__add_form .__function_s_cat_prop__res .ttable_tbody_tr[data-id="'+data_['nomer']+'"] select').append(text_).find('option[value='+id_+']').attr('selected','selected').closest('select')
                                .select2({allowClear: true,'width':'100%',templateResult: s_prop_template_color,templateSelection: s_prop_template_color}).on("select2:select", function (e) {$(this).select2('open');})
                                .on("select2:unselect", function (e) {$(this).select2('open');}); //chk
                            $('.preload .__other__add .__function_s_cat_prop__res .ttable_tbody_tr[data-id="'+data_['nomer']+'"] select').append(text_);
                            $('.__other__fillter_prop_ress .ttable_tbody .ttable_tbody_tr[data-id="'+data_['nomer']+'"] select').append(text_);
                        
                            
                        
                        }
                        $('.__function_s_cat_prop__add_prop_val_question').arcticmodal('close');
                    }else{
                        alert_m(data,'','error','none');
                    }
                }
             });
        }
        
    });

    
    //ОТКРЫВАЕМ ФИЛЬТР
    $(document).delegate('.__other__fillter_prop','click',function(){
        var th_=$(this);
        $('.__other__fillter_prop_ress .__function_s_cat_prop__com_prop').detach();
        var form_=$('.__other__fillter_prop_ress').clone();
        
        alert_m('<div class="fillter_prop_window"><h2>Фильтр по свойствам:</h2><input type="hidden" id="tags" value="tag1,tag2" style="width: 400px;">'+form_.html()+'<div class="fillter_prop_window_com"><center><span class="btn_orange fillter_prop_window_save">Применить</span></center></div></div>','','info','none');
        $('.fillter_prop_window .__function_s_cat_prop__val .__function_s_cat_prop__select').select2({'width':'100%',templateResult: s_prop_template_color,templateSelection: s_prop_template_color});//.on("select2:select", function (e) {$(this).select2('open');})
              //.on("select2:unselect", function (e) {$(this).select2('open');}); //chk
        
        var prop_fillter_check;
        var prop_fillter_name;
        $(".fillter_prop_window .__function_s_cat_prop__selectajax").select2({'width':'100%',templateResult: s_prop_template_color,templateSelection: s_prop_template_color,allowClear: true,
            ajax: {
              url: "ajax/__function_s_cat_prop_ajax.php",
              dataType: 'json',
              delay: 250,
              data: function(params, page) {
                      prop_fillter_name=params.term;
                      return {
                            '_term': params.term,
                            '_t': 'autocomplete_prop_select',
                            '_prop_id':prop_fillter_check,
                            page: params.page
                      };
                },
              processResults: function (data, params) {
                params.page = params.page || 1;
        
                return {
                  results: data.items,
                  pagination: {
                    more: (params.page * 30) < data.total_count
                  }
                };
              },
              cache: true
            },
            minimumInputLength: 1
          }).on("select2:open", function (e) {
                prop_fillter_check=$(this).closest('.ttable_tbody_tr').data('id');
                $(this).select2('open');
                
          }).on("select2:select", function (e) {
                if ($('.__other__fillter_prop_ress .ttable .ttable_tbody_tr select option[value='+e.params.data.id+']').size()==0){//добавляем если нет свойства
                    $('.__other__fillter_prop_ress .ttable .ttable_tbody_tr[data-id='+prop_fillter_check+'] select').append('<option value="'+e.params.data.id+'">'+e.params.data.text+'</option>');
                }
               // $(this).select2('open');
                $(this).next().find('.select2-search__field').val(prop_fillter_name).keyup();
          });
        
        
        
    });
    
    //Сохраняем фильтр
    $(document).delegate('.fillter_prop_window_save','click',function(){
        $('.__other__fillter_prop_ress .ttable_tbody_tr select option').removeAttr('selected');
        $('.__other__fillter_prop_ress .ttable_tbody_tr input').val('');
        
        $('.fillter_prop_window .ttable_tbody_tr').each(function(){
            var th_=$(this);
            var id_=th_.data('id');
            var data_tip_=th_.data('data_tip');
            
            
            if (data_tip_=='Текст'){
                var val_=th_.find('select').val();
               
                if (!!val_) {
                    th_.find('select option:selected').each(function(){
                        var val_new=$(this).val();
                        $('.__other__fillter_prop_ress .ttable_tbody_tr[data-id="'+id_+'"] select').val(val_).find('option[value="'+val_new+'"]').attr('selected','selected');;
                        
                    });
                }
            }
            else if (data_tip_=='Число'){
                var val_1=th_.find('input:first').val();
                var val_2=th_.find('input:last-child').val();
                
                $('.__other__fillter_prop_ress .ttable_tbody_tr[data-id="'+id_+'"] input:first').val(val_1).attr('value',val_1);
                
                $('.__other__fillter_prop_ress .ttable_tbody_tr[data-id="'+id_+'"] input:last-child').val(val_2).attr('value',val_2);
                
                
            }
        });
        
        $('.fillter_prop_window').arcticmodal('close');
        find();
    });
    
    //очистка фильтра свойств
    $(document).delegate('.__other__fillter_prop_clear','click',function(){
        $('.__function_s_cat_prop__select option').removeAttr('selected');
        $('.__function_s_cat_prop__select option').prop('selected',false);
        $('.__other__fillter_prop_ress input').val('').removeAttr('value').attr('value','');
        $('.__other__fillter_prop_kol, .__other__fillter_prop_clear').detach();

        find();
    });
    
    
    //удаление свойства от товара
    $(document).delegate('.__other_td__funct_prop .fa-remove','click',function(){
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='s_cat_s_prop_val_id_remove';
        data_['s_cat_s_prop_val_id']=th_.closest('p').data('id');
        th_.closest('p').detach();
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_cat_prop_ajax.php",
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
    });
    
    
    //изменение свойства товара
    $(document).delegate('.__other_td__funct_prop strong','dblclick',function(){
        var err_text='';
        var th_=$(this);
        var th_span=$(this).closest('.prop_quick_change');
        var data_=new Object();
        data_['_t']='s_cat_s_prop_val_id_change';
        data_['s_cat_s_prop_val_id']=th_.closest('p').data('id');
       
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_cat_prop_ajax.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        if (typeof data_n.p!='undefined'){
                            var txt='';
                            for(var s_prop_val_id in data_n.p){
                                var sel_='';
                                if (data_n.i==s_prop_val_id){
                                    sel_=' selected="selected"';
                                }
                                txt+='<option value="'+s_prop_val_id+'"'+sel_+'>'+data_n.p[s_prop_val_id]+'</option>';
                            }
                            if (txt!=''){
                                th_span.html('<select class="prop_quick_change_select">'+txt+'</select>');
                                th_span.find('select').select2({'width':'100%'}).select2('open').on("select2:select", function (e) {s_prop_quick_change_save(th_span);}).on("select2:close", function (e) {s_prop_quick_change_save(th_span);});
                            }
                           
                        }
                        
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    //сохранение быстрого изменения
    function s_prop_quick_change_save(th_){

        var data_=new Object();
        data_['_t']='s_prop_quick_change_save';
        data_['s_cat_id']=th_.closest('.ttable_tbody_tr').data('id');
        data_['s_prop_val_id']=th_.find('select').val();
        data_['s_cat_s_prop_val_id']=th_.closest('p').data('id');
        var name=th_.find('select option:selected').text();
        
    	$.ajax({
            "type": "POST",
            "url": "ajax/__function_s_cat_prop_ajax.php",
            "dataType": "text",
            "data":data_,
            "success":function(data,textStatus){
                if (is_json(data)==true){
                    data_n=JSON.parse(data);
                    if (typeof data_n.id!='undefined'){
                        th_.closest('p').data('id',data_n.id);
                        th_.html('<strong>'+name+'</strong>');
                    }
                    
            	}
            	else{
            		alert_m(data,'','error','none');
            	}            
            }
            });
    }
    
    
}//end start
//ФИЛЬТР
// *******************************************************************************
// *******************************************************************************
else if (tip_=='fillter'){ //ФИЛЬТР ПО ТОВАРУ
    //alert_m('Фильтр свойств - разрабатывается!');
    
    var prop_fillter_val=$('.__other__add .__function_s_cat_prop__res').clone();
    prop_fillter_val.find('.__function_s_cat_prop__input').each(function(){
        var data_tip_=$(this).closest('.ttable_tbody_tr').data('data_tip');
        var data_id_=$(this).closest('.ttable_tbody_tr').data('id');
        
        if (data_tip_=='Текст'){
            var th_=$(this).attr('placeholder');
            $(this).closest('.__function_s_cat_prop__val').html('<select class="__function_s_cat_prop__selectajax" multiple="" data-placeholder="'+th_+'" name="__function_s_cat_prop__select"><option></option></select>');
        }
        else if (data_tip_=='Число'){
            var th_=$(this).attr('placeholder');
            $(this).closest('.__function_s_cat_prop__val').html('<input class="__function_s_cat_prop__input" placeholder="'+th_+' от" name="__function_s_cat_prop__input_'+data_id_+'" value="" /> - <input class="__function_s_cat_prop__input" placeholder="'+th_+' до" name="__function_s_cat_prop__input_'+data_id_+'" value="" />');
           
        }
    });
    
    
    var txt='';
    txt+='<span class="btn_gray __other__fillter_prop" data-s_prop_val_id="">Фильтр</span>';
    txt+='<div class="__other__fillter_prop_ress" style="display:none;"><table>'+prop_fillter_val.html()+'</table></div>';
    return txt;
}
//ПОИСК
// *******************************************************************************
// *******************************************************************************
else if (tip_=='find'){ //ПОИСК ПО ТОВАРУ
    $('.__other__fillter_prop_kol, .__other__fillter_prop_clear').detach();
    //проверяем количество товаров в фильтре
    var kol_=$('.__other__res_form .__other__fillter_prop_ress').find('input[value!=""],option[selected=selected]').size();
    if ($('.__other__fillter_prop_clear').size()==0 && kol_>0){
        $('.__other__fillter_prop').after('<span class="ico ico_del __other__fillter_prop_clear" title="сбросить фильтрацию свойств"></span>');//кнопка сброса фильтра свойств
        $('.__other__fillter_prop').append('<span class="__other__fillter_prop_kol">('+kol_+')</span>');
    }

    var text='';
    if ((th_.split('||').length - 1)>0){
        var arr_prop = th_.split('||');
    }
    else{
        var arr_prop = new Array();
        arr_prop[0]=th_;
    }
    var max_l=0;
    for(var i in arr_prop) 
    {
        var prop_cur=arr_prop[i];
        if ((prop_cur.split(':').length - 1)>0){
            var arr_prop2=prop_cur.split(':');
            var name=arr_prop2[0];
            var val=arr_prop2[1];
            var s_cat_s_prop_val_id=(arr_prop2[2]).trim();
            var new_text='<p data-id="'+s_cat_s_prop_val_id+'"><span>'+name+'</span>: <span class="prop_quick_change"><strong>'+val+'</strong></span> <i class="fa fa-remove"></i></p>';
            text+=new_text;
            
            $('body').append('<div class="find_p_prop" style="position:absolute;">'+new_text+'</div>');
            var max_l1=$('.find_p_prop').css('width');$('.find_p_prop').detach();
            max_l1=max_l1.replace('px','');max_l1=max_l1-0;
            if (max_l<max_l1){max_l=max_l1;}
        }
    }
    max_l=max_l+10;
    return '<div style="display:block; min-width:'+max_l+'px">'+text+'</div>';
}
// *******************************************************************************
// *******************************************************************************
else if (tip_=='add'){//ОТКРЫТИЕ ОКНА ДОБАВЛЕНИЯ ТОВАРА
    $('.__other__add_form .__function_s_cat_prop__res').find('.ttable_tbody_tr').hide();//скрываем все свойства
    
    $('.__other__add_form .__function_s_cat_prop__select').select2({allowClear: true,'width':'100%',templateSelection: s_prop_template_color,templateResult: s_prop_template_color}).on("select2:select", function (e) {$(this).select2('open');})
              /*.on("select2:unselect", function (e) {$(this).select2('open');})*/.on("change",function(){
                var l_=$(this).val();
                if (typeof l_=='undefined' || count(l_)==0){
                    $(this).closest('.ttable_tbody_tr').hide();
                }
              }); //chk
    
    
    $('.__other__add_form .__function_s_cat_prop__res .ttable_tbody_tr').each(function(){
        var prop_id=$(this).data('id');
        var th_=$(this).find('.__function_s_cat_prop__input');
        
          th_.autocomplete({
                minLength: 0,
                appendTo: ".__other__add_form"
              }).focus(function() {
                     th_.autocomplete("option", "source",function(request, response){
                         request['_t']='autocomplete_prop';
                         request['_prop_id']=prop_id;
                         
                         if (typeof jqxhr!='undefined'){jqxhr.abort();}
                         jqxhr = $.ajax({
                        	"type": "POST",
                        	"url": "ajax/__function_s_cat_prop_ajax.php",
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
                     });
                th_.autocomplete("search", th_.val());
              });
          
          
        
        
    });
    
    //Отображение свойств
    $('.__other__add_form .s_prop_view').select2({'width':'100%',allowClear: true}).change(function(){
        var th_=$(this);
        $('.__other__add_form .__function_s_cat_prop__res').find('.ttable_tbody_tr[data-id="'+th_.val()+'"]').show();
        th_.val(null).find('option').removeAttr('selected');
    });
    
    //получаем свойства от структуры
    
    if ($('.__other__add_form #s_struktura_id:visible').length>0){
        $('.__other__add_form #s_struktura_id').trigger('change');
    }
}
// *******************************************************************************
// *******************************************************************************
else if (tip_=='change'){//ОТКРЫТИЕ ОКНА ИЗМЕНЕНИЯ ТОВАРА
    $('.__other__add_form .__function_s_cat_prop__res').find('.ttable_tbody_tr').hide();//скрываем все свойства
   
  
    if ($('div#s_struktura_id:visible').length>0){$('div#s_struktura_id').trigger('click');}
    if ($('select#s_struktura_id:visible').length>0){$('select#s_struktura_id').trigger('change');}

    
    var th_2=th_._d['prop'];

    for (var i in th_2){
        var val_prop=th_2[i];
        if (val_prop[0]==''){
            $('.__other__add_form .__function_s_cat_prop__select option[value='+val_prop[1]+']').attr('selected','selected').closest('.ttable_tbody_tr').show();//отображаем выбранные свойства;
        }
        else{
            $('.__other__add_form .__function_s_cat_prop__res .ttable_tbody_tr[data-id='+val_prop[0]+'] .__function_s_cat_prop__input').val(val_prop[1]).closest('.ttable_tbody_tr').show();//отображаем выбранные свойства
        }
    }

    $('.__other__add_form .__function_s_cat_prop__res .ttable_tbody_tr').each(function(){
        var th_=$(this).find('.__function_s_cat_prop__input');
        var prop_id=$(this).data('id');
          th_.autocomplete({
                minLength: 0
              }).focus(function() {
                     th_.autocomplete("option", "source",function(request, response){
                         request['_t']='autocomplete_prop';
                         request['_prop_id']=prop_id;
                         
                         if (typeof jqxhr!='undefined'){jqxhr.abort();}
                         jqxhr = $.ajax({
                        	"type": "POST",
                        	"url": "ajax/__function_s_cat_prop_ajax.php",
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
                     });
                th_.autocomplete("search", th_.val());
              });
          
          
    });
          
        //Отображение свойств
        $('.__other__add_form .s_prop_view').select2({'width':'100%',allowClear: true,templateSelection: s_prop_template_color,templateResult: s_prop_template_color}).change(function(){
            var th_=$(this);
            $('.__other__add_form .__function_s_cat_prop__res').find('.ttable_tbody_tr[data-id="'+th_.val()+'"]').show();
            th_.val(null).find('option').removeAttr('selected');
        });
        
      

    
   $('.__other__add_form .__function_s_cat_prop__select').select2({templateResult: s_prop_template_color,templateSelection: s_prop_template_color,allowClear: true,'width':'100%'});/*.on("select2:select", function (e) {$(this).select2('open');})
              .on("select2:unselect", function (e) {$(this).select2('open');}); //chk*/
     
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
    var txt=$('.__other__add .__function_s_cat_prop__res').clone();
    //
    txt.find('.ttable_tbody_tr').each(function(){
        $(this).find('.__function_s_cat_prop__com_prop').html('<input type="text" name="parsing_prop['+$(this).data('id')+']" placeholder="Селектор свойства '+$(this).find('.__function_s_cat_prop__name').text()+'" /><div><textarea placeholder="Код, после выполнения переменной $res" name="parsing__code_prop['+$(this).data('id')+']"></textarea></div>');
        $(this).find('.__function_s_cat_prop__val').html('<input type="checkbox" class="parsing_col_chk" name="parsing_chk_prop['+$(this).data('id')+']" value="1" />');
    
    });

    txt='<div class="ttable __function_s_cat_prop__parser"><div class="ttable_thead"><div class="ttable_thead_tr"><div class="ttable_thead_tr_td">Свойство</div><div class="ttable_thead_tr_td">Парсим?</div><div class="ttable_thead_tr_td">селектор</div></div></div><div class="ttable_tbody">'+txt.html()+'</div></div>';
    return txt;
}
// *******************************************************************************
// *******************************************************************************
// Экспорт
else if(tip_=='export'){
    var txt_clone=$('.__other__add .__function_s_cat_prop__res').clone();
    var txt='';
    //
    txt_clone.find('.ttable_tbody_tr').each(function(){
        var col_='prop['+$(this).data('id')+']';
         txt +='<li data-col="prop">'
                +'<input type="checkbox" id="export_chk_'+col_+'" name="export_chk_'+col_+'"  data-col="'+col_+'" value="'+$(this).data('id')+'" /> '
                +'<label for="export_chk_'+col_+'">'
                    +$(this).find('.__function_s_cat_prop__name').text()
                +'</label> '
                +'<div class="export_textarea_div">'
                    +'<textarea placeholder="PHP код для изменения поля $myrow[\'prop_'+$(this).data('id')+'\']" name="export_code[prop['+$(this).data('id')+']]"></textarea>'
                +'</div><div style="clear:both;"></div>'
                +'</li>';
        
    });

    return txt;
}
else{
    alert_m('Тип функции ('+tip_+') '+col_+' не определен!','','error','none');
}
// *******************************************************************************
// *******************************************************************************
//end  prop
