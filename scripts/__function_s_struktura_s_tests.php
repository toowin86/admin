<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>

//ТЕСТЫ
// *******************************************************************************
// *******************************************************************************
if (tip_=='start'){
    //скрыытие вопрсов
    hide_quest=function(){
        $('.__other__add_form .s_tests_res_item_answer_res').hide();
        $('.s_struktura_s_tests_div_main').prepend('<span class="s_test_hide_com_all"><i class="fa fa-chevron-circle-down"></i> Развернуть все вопросы</span>');
        $('.s_test_hide_com').html('<i class="fa fa-chevron-circle-down" title="Развернуть вопрос"></i>');
    }
    
    //проверка наличия вопросов и их заполнение
    chk_s_test=function(){
        var txt='';
        //перебор вопросов
        $('.__other__add_form .s_tests_res_item').each(function(){
            if (txt!=''){txt+=';';}
            txt+=$(this).data('id');
        });
        $('input[name="s_tests"]').val(txt);
        var cnt_=$('.__other__add_form .s_tests_res_item').length;
        $('.__other__add_form .s_tests_cnt_quest').html('Всего '+cnt_+' вопрос'+end_word(cnt_,'ов','','а'));
    }
    
    // Добавление вопроса
    add_question=function(id,name,dt,html_code,chk_tip,answer){
        var err_txt='';
        if (id==''){err_txt+='Не определен номер вопроса (№'+id+')';}
        if ($('.s_tests_res_item[data-id="'+id+'"]').length>0){err_txt+='Данный вопрос уже добавлен';}
        
        var txt='';
        if (err_txt==''){
            txt+='<div class="s_tests_res_item" data-id="'+id+'">';
                txt+='<p class="s_tests_res_item_info"><span class="s_tests_res_item_info_nom">'+id+'</span> вопрос <span>добавлен: <span>'+dt+'</span></span> <span class="s_test_hide_com"><i class="fa fa-chevron-circle-up" title="Свернуть вопрос"></i></span></p>';
                txt+='<div class="s_tests_res_item_tbl">';
                    txt+='<div class="s_tests_res_item_main">';
                        txt+='<h3>'+name+'</h3>';
                        txt+='<textarea style="display:none;" class="s_tests_res_item_desc" placeholder="Подробное описание вопроса">'+html_code+'</textarea>';
                    txt+='</div>';
                    txt+='<div class="s_tests_res_item_funct">';
                        
                        txt+='<div class="s_test_tip_question_div"><select class="tip_question" data-placeholder="Правильные ответы"><option value="0"';
                        if (chk_tip=='0'){txt+=' selected="selected"';}
                        txt+='>Один правильный</option><option value="1"';
                        if (chk_tip=='1'){txt+=' selected="selected"';}
                        txt+='>Несколько правильных</option></select></div>';
                        txt+='<i class="fa fa-plus s_tests_res_item_answer_add" title="Добавить ответ"></i>';
                        txt+='<i class="fa fa-edit s_tests_res_item_html_code" title="Описание вопроса"></i>';
                        txt+='<i class="fa fa-minus s_tests_res_item_del" title="Убрать вопрос из теста (не удалить)"></i>';
                    txt+='</div>';
                txt+='</div>';
                txt+='<div class="s_tests_res_item_answer_res">';
               
                txt+='</div>';
            txt+='</div>';
       
            $('.__other__add_form .s_tests_add_res').prepend(txt);
            $('.__other__add_form .s_tests_res_item[data-id="'+id+'"] .tip_question').select2({'width':'100%',closeOnSelect:true,minimumResultsForSearch: Infinity}).change(function(){ s_test_save_tip(id);});
            //Добавляем ответы
            if (typeof answer!='undefined' && typeof answer['id']=='object'){
                for(var i in answer['id']){
                    
                    add_answer(id,answer['id'][i],answer['name'][i],answer['chk_true'][i],answer['html_code'][i],answer['img'][i]);
                }
                
            }
        }else{
            alert_m(err_txt,'','error','none');
        }
        chk_s_test();
    }
    
    // Добавление ответа
    add_answer=function(s_test_quest_id,id,name,chk_true,html_code,img){
        name=name || '';
        chk_true=chk_true || '';
        html_code=html_code || '';
        img=img || '';
        var img_arr=new Object();
        if (img!=''){
            if ((img.split('::').length - 1)>0){
                img_arr=img.split('::');
            }
        }
        
        var chk_true_txt='';if (chk_true=='1'){chk_true_txt=' checked="checked"';}
        var err_txt='';
        if (id==''){err_txt+='Не определен номер ответа (№'+id+')';}
        if ($('.s_tests_res_item[data-id="'+id+'"]').length>0){err_txt+='Данный вопрос уже добавлен';}
        
        var txt='';
        if (err_txt==''){
            txt+='<div class="s_tests_res_answer_item" data-id="'+id+'">';
                txt+='<div class="s_tests_res_answer_item_tbl">';
                    txt+='<div class="s_tests_res_answer_item_main">';
                        txt+='<input type="text" class="s_tests_res_answer_item_main_input" placeholer="Введите ответ" value="'+_IN(name)+'" />';
                    txt+='</div>';
                    txt+='<div class="s_tests_res_answer_item_funct">';
                        
                        txt+='<label for="'+s_test_quest_id+'_'+id+'"><input type="checkbox" id="'+s_test_quest_id+'_'+id+'" '+chk_true_txt+' /> Ответ верный</label>';
                        //txt+='<i class="fa fa-edit" title="Описание"><textarea class="s_tests_res_answer_item_html_code" style="display:none">'+html_code+'</textarea></i>';
                        txt+='<i class="fa fa-remove s_tests_res_answer_remove" title="Удалить ответ"></i>';
                    txt+='</div>';
                txt+='</div>';
            txt+='</div>';
            
            //Добавляем
            $('.__other__add_form .s_tests_res_item[data-id="'+s_test_quest_id+'"] .s_tests_res_item_answer_res').append(txt);
       
                
        }else{
            alert_m(err_txt,'','error','none');
        }
    }
    
    s_test_clear_all_results=function(f_n){
        var data_=new Object();
        data_['_t']='s_test_clear_all_results';
        data_['f_n']=f_n;
        data_['i_contr_id']=$('.__other__add_form .i_contr_id').val();
        
        
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
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
    
    //очистка тестов
    $(document).delegate('.s_test_clear_all_results','click',function(){
        s_test_clear_all_results($('.__other__add_form .row_id__span').text());
    });
    
    //Сохранение описания вопроса
    s_tests_html_code_save=function(s_test_quest_id,html_code){
        
            var data_=new Object();
            data_['_t']='s_tests_html_code_save';
            data_['s_test_quest_id']=s_test_quest_id;
            data_['html_code']=html_code;
            
            
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
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
    
    //Сохранение типа вопроса
    s_test_save_tip=function(s_test_quest_id){
        
            var data_=new Object();
            data_['_t']='s_test_save_tip';
            data_['s_test_quest_id']=s_test_quest_id;
            data_['chk_tip']=$('.__other__add_form .s_tests_res_item[data-id="'+s_test_quest_id+'"] .tip_question').val();
            
            
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
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
    
    //открыть форму добавления тестов
    $(document).delegate('.open_test_form','click',function(){
        $(this).closest('.s_struktura_s_tests_div_first').find('.s_struktura_s_tests_div').show();
        $(this).detach();
    });
    
    
    
    //Добавление вопроса
    $(document).delegate('.__other__add_form .s_tests_add_question_com','click',function(){
        var quest_name=$('.__other__add_form .s_tests_add_question').val();
        if (quest_name==''){alert_m('Введите название вопроса',function(){$('.__other__add_form .s_tests_add_question').focus()},'error',3000);}
        else{
            
            
            var err_text='';
            var th_=$(this);
            var data_=new Object();
            data_['_t']='s_tests_add_question';
            data_['name']=quest_name;
            
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
        	            add_question(data_n.nomer,quest_name,data_n.data_create,data_n.html_code,data_n.chk_tip,data_n.answer);
                        $('.__other__add_form .s_tests_add_question').val('');
                        
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
            
            
        }
    });
    
     
    //Удаление вопроса
    $(document).delegate('.s_tests_res_item_del','click',function(){
        $(this).closest('.s_tests_res_item[data-id!=""]').detach();
        chk_s_test();
    });
    
    //Добавить ответ
    $(document).delegate('.s_tests_res_item_answer_add','click',function(){
        var s_test_quest_id=$(this).closest('.s_tests_res_item').data('id');
        var data_=new Object();
        data_['_t']='s_tests_add_answer';
        data_['s_test_quest_id']=s_test_quest_id;
        
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            add_answer(s_test_quest_id,data_n.nomer,'','','');
                    $('.__other__add_form .s_tests_res_item[data-id="'+s_test_quest_id+'"] .s_tests_res_item_answer_res .s_tests_res_answer_item_main_input:last').focus();
                    
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    });
    
    //Сохранение ответа
    $(document).delegate('.__other__add_form .s_tests_res_answer_item_main_input','blur',function(){
        var th_=$(this);
        var data_=new Object();
        data_['_t']='s_tests_answer_name_save';
        data_['id']=th_.closest('.s_tests_res_answer_item').data('id');
        data_['name']=th_.val();
        if (data_['name']==''){
            th_.closest('.s_tests_res_answer_item').detach();
        }
        
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
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
       
    });
    
    //указание правильного ответа
    $(document).delegate('.__other__add_form .s_tests_res_answer_item_funct input[type="checkbox"]','change',function(){
        var th_=$(this);
        var data_=new Object();
        
        data_['_t']='s_tests_answer_chk_true_save';
        
        var chk_true=th_.prop('checked');
        if (chk_true==true){data_['chk_true']='1';}
        else{data_['chk_true']='0';}
        
        data_['id']=th_.closest('.s_tests_res_answer_item').data('id');

        
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
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
        
        
    });
    
    
    //Удалить ответ
    $(document).delegate('.s_tests_res_answer_remove','click',function(){
        var data_=new Object();
        var th_=$(this);
        
        data_['_t']='s_tests_res_answer_remove';
        data_['id']=th_.closest('.s_tests_res_answer_item').data('id');
        th_.closest('.s_tests_res_answer_item').detach();
        
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__function_s_struktura_s_tests_ajax.php",
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
    });
    
    //Описание вопроса
    $(document).delegate('.s_tests_res_item_html_code','click',function(){
        var txt='';
        txt+='<div class="s_tests_res_item_html_code_form" data-id="'+$(this).closest('.s_tests_res_item').data('id')+'">';
            txt+='<textarea id="s_tests_res_item_html_code_edit'+$(this).closest('.s_tests_res_item').data('id')+'">';
            txt+=$(this).closest('.s_tests_res_item').find('.s_tests_res_item_desc').val();
            txt+='</textarea>';
            txt+='<div class="s_tests_res_item_desc_save_div"><span class="btn_orange s_tests_res_item_desc_save">Сохранить</span></div>';
        txt+='</div>';
        alert_m(txt,'','info','none','','','1');
        
        var edit_=CKEDITOR.replace('s_tests_res_item_html_code_edit'+$(this).closest('.s_tests_res_item').data('id')+'',{allowedContent:true});
        AjexFileManager.init({returnTo: 'ckeditor', editor: edit_});
                                           
                                           
    });
    //сохраняем описание вопроса
    $(document).delegate('.s_tests_res_item_desc_save','click',function(){
        var html_code=CKEDITOR.instances['s_tests_res_item_html_code_edit'].getData();
        var s_test_quest_id=$(this).closest('.s_tests_res_item_html_code_form').data('id');
        s_tests_html_code_save(s_test_quest_id,html_code);
        $('.__other__add_form .s_tests_res_item[data-id="'+s_test_quest_id+'"]').find('.s_tests_res_item_desc').val(html_code);
        $('.s_tests_res_item_html_code_form').arcticmodal('close');
    });
    
    //Открыть вопрос
    $(document).delegate('.s_test_hide_com','click',function(){
        var th_=$(this);
        var th_2=th_.closest('.s_tests_res_item').find('.s_tests_res_item_answer_res');
        
        if (th_2.css('display')=='none'){
            th_2.show();
            th_.html('<i class="fa fa-chevron-circle-up" title="Развернуть вопрос"></i>');
        }else{
            th_2.hide();
            th_.html('<i class="fa fa-chevron-circle-down" title="Развернуть вопрос"></i>');
        }
        
    });
    //Открыть все вопросы
    $(document).delegate('.s_test_hide_com_all','click',function(){
        var th_=$(this);
        
        if ($('.__other__add_form .s_tests_res_item_answer_res:first').css('display')=='none'){
            th_.html('<i class="fa fa-chevron-circle-up"></i> Свернуть все вопросы');
            $('.__other__add_form .s_tests_res_item_answer_res').show();
            $('.__other__add_form .s_test_hide_com').html('<i class="fa fa-chevron-circle-up" title="Свернуть вопрос"></i>');
        }else{
            th_.html('<i class="fa fa-chevron-circle-down"></i> Развернуть все вопросы');
            $('.__other__add_form .s_tests_res_item_answer_res').hide();
            $('.__other__add_form .s_test_hide_com').html('<i class="fa fa-chevron-circle-down" title="Развернуть вопрос"></i>');
        
        }
        
    });
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='change'){
    var val_=th_._d['s_tests'];
    for (var i in val_){
        add_question(val_[i]['id'],val_[i]['name'],val_[i]['dt'],val_[i]['html_code'],val_[i]['tip'],val_[i]['answer']);
    }
    var val_opt=th_._d['s_tests_opt'];
    $('.__other__add_form .s_test_data_start').val(val_opt.data_start);
    $('.__other__add_form .s_test_data_end').val(val_opt.data_end);
  
    $('.__other__add_form .s_test_cnt_try').val(val_opt.cnt_try);
    $('.__other__add_form .s_test_cnt_quest').val(val_opt.cnt_quest);
    if (val_opt.chk_active=='1'){$('.__other__add_form .s_test_chk_active').prop('checked','checked');}else{$('.__other__add_form .s_test_chk_active').prop('checked',false);}
    if (val_opt.chk_reg=='1'){$('.__other__add_form .s_test_chk_reg').prop('checked','checked');}else{$('.__other__add_form .s_test_chk_reg').prop('checked',false);}
    if (val_opt.chk_rand_quest=='1'){$('.__other__add_form .s_test_chk_rand_quest').prop('checked','checked');}else{$('.__other__add_form .s_test_chk_rand_quest').prop('checked',false);}
    if (val_opt.chk_rand_answer=='1'){$('.__other__add_form .s_test_chk_rand_answer').prop('checked','checked');}else{$('.__other__add_form .s_test_chk_rand_answer').prop('checked',false);}
    
    //дата
    $(' .s_test_data_start').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(current_time,$input){}});
    $('.__other__add_form  .s_test_data_end').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(current_time,$input){}});
    $('.__other__add_form .s_test_time_for_test').val(val_opt.time_for_test).timeDropper({format:'HH:mm',setCurrentTime:false}).val(val_opt.time_for_test);
    
    //автозаполнение
    if (typeof s_test_autocomp=='undefined'){var s_test_autocomp=new Object();}
    $('.__other__add_form  .s_tests_add_question').autocomplete({
        minLength: 0,
        appendTo: ".__other__add_form  .s_struktura_s_tests_div_main",
        source: function(request, response){
             request['_t']='s_tests_add_question_autocomp';
             s_test_autocomp['id']='';
             if (typeof jqxhr!='undefined'){jqxhr.abort();}
             jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/__function_s_struktura_s_tests_ajax.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	   if (is_json(data)==true){
                	       var data_n=JSON.parse(data);
                           response(data_n);
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            
                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                var answer_txt='';
                                if (typeof data_n[i].answer=='object'){
                                    for(var j in data_n[i].answer){
                                        if (typeof data_n[i].answer['name'][j]!='undefined'){
                                            if (answer_txt!=''){answer_txt=', ';}
                                            answer_txt+=data_n[i].answer['name'][j];
                                        }
                                    }
                                }
                                if (answer_txt!=''){answer_txt=' (ответы: '+answer_txt+')';}
                               var txt='<p>'+data_n[i].value+''+answer_txt+'</p>';
                               $(this).html(txt);
                            });
                            
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
        },
        select: function( event, ui ) {
            s_test_autocomp['id']=ui.item.id;
            s_test_autocomp['name']=ui.item.value;
            s_test_autocomp['dt']=ui.item.dt;
            s_test_autocomp['html_code']=ui.item.html_code;
            s_test_autocomp['answer']=ui.item.answer;
        },
        close: function( event, ui ) {
            if (s_test_autocomp['id']!=''){
                add_question(s_test_autocomp['id'],s_test_autocomp['name'],s_test_autocomp['dt'],s_test_autocomp['html_code'],s_test_autocomp['tip'],s_test_autocomp['answer']);
                $('.__other__add_form  .s_tests_add_question').val('');
            }
        }
    });
    
    $('.__other__add_form  .s_tests_add_question').dblclick(function(){
        $(this).autocomplete( "search", $(this).val() );
    });
    $('.s_tests_add_res').sortable({delay: 150,placeholder: "s_tests_res_item",start: function( event, ui ) {},stop: function( event, ui ) {chk_s_test();}});
    hide_quest();//скрытие вопросов
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='add'){
    //дата
    $('.__other__add_form  .s_test_data_start').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(current_time,$input){}});
    $('.__other__add_form  .s_test_data_end').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(current_time,$input){}});

//автозаполнение
    if (typeof s_test_autocomp=='undefined'){var s_test_autocomp=new Object();}
    $('.__other__add_form  .s_tests_add_question').autocomplete({
        minLength: 0,
        appendTo: ".__other__add_form  .s_struktura_s_tests_div_main",
        source: function(request, response){
             request['_t']='s_tests_add_question_autocomp';
             s_test_autocomp['id']='';
             if (typeof jqxhr!='undefined'){jqxhr.abort();}
             jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/__function_s_struktura_s_tests_ajax.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	   if (is_json(data)==true){
                	       var data_n=JSON.parse(data);
                           response(data_n);
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            
                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                var answer_txt='';
                                if (typeof data_n[i].answer=='object'){
                                    for(var j in data_n[i].answer){
                                        if (typeof data_n[i].answer['name'][j]!='undefined'){
                                            if (answer_txt!=''){answer_txt=', ';}
                                            answer_txt+=data_n[i].answer['name'][j];
                                        }
                                    }
                                }
                                if (answer_txt!=''){answer_txt=' (ответы: '+answer_txt+')';}
                               var txt='<p>'+data_n[i].value+''+answer_txt+'</p>';
                               $(this).html(txt);
                            });
                            
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
        },
        select: function( event, ui ) {
            s_test_autocomp['id']=ui.item.id;
            s_test_autocomp['name']=ui.item.value;
            s_test_autocomp['dt']=ui.item.dt;
            s_test_autocomp['html_code']=ui.item.html_code;
            s_test_autocomp['answer']=ui.item.answer;
        },
        close: function( event, ui ) {
            if (s_test_autocomp['id']!=''){
                add_question(s_test_autocomp['id'],s_test_autocomp['name'],s_test_autocomp['dt'],s_test_autocomp['html_code'],s_test_autocomp['tip'],s_test_autocomp['answer']);
                $('.__other__add_form  .s_tests_add_question').val('');
            }
        }
    });
    
    $('.__other__add_form  .s_tests_add_question').dblclick(function(){
        $(this).autocomplete( "search", $(this).val() );
    });
    $('.s_tests_add_res').sortable({delay: 150,placeholder: "s_tests_res_item",start: function( event, ui ) {},stop: function( event, ui ) {chk_s_test();}});
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='save'){
    var err_text='';
    //if (th_['s_tests']==''){err_text+='Не заполнен ТЕСТ!<br />';}
    return err_text;
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='find'){
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='parser'){
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='export'){
    var txt ='';
    return txt;
}
else{
    alert_m('Тип функции '+col_+' не определен!','','error','none');
}
// *******************************************************************************
// *******************************************************************************
//end  ТЕСТЫ
