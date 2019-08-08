<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script src="js/jquery.mjs.nestedSortable.js"></script>

<script>
<?php
//************ ДОБАВЛЕНИЕ *************
$tx=0;
    foreach ($a_com_arr['com'] as $a_com_id => $com_){
        if ($com_=='add'){
            if (isset($a_admin_a_com_arr[$a_com_id])){
                $tx=1;
            }
        }
    }
if ($tx==1){echo "var a_admin_add=1;\n";}
else{echo "var a_admin_add=0;\n";}
//************ ИЗМЕНЕНИЕ *************
$tx=0;
    foreach ($a_com_arr['com'] as $a_com_id => $com_){
        if ($com_=='change'){
            if (isset($a_admin_a_com_arr[$a_com_id])){
                $tx=1;
            }
        }
    }
if ($tx==1){echo "var a_admin_change=1;\n";}
else{echo "var a_admin_change=0;\n";}
//************ УДАЛЕНИЕ *************
$tx=0;
    foreach ($a_com_arr['com'] as $a_com_id => $com_){
        if ($com_=='del'){
            if (isset($a_admin_a_com_arr[$a_com_id])){
                $tx=1;
            }
        }
    }
if ($tx==1){echo "var a_admin_del=1;\n";}
else{echo "var a_admin_del=0;\n";}
?>
//проверка пустых значений
function chk_null(){

    $('.a_admin_edit_span, .a_menu_name_edit span').each(function(){
        var th_=$(this);var text=th_.html();text=text.replace(" ", "");
      
        if(text==''){
            th_.html('<span class="ico ico_add"></span>');
        }
    });
}

//Проверка функций
function change_a_com(){
    $('.sortable li .a_menu_a_com_block').html('');
    if ($('.a_com_menu.active').size()==0){
        
        $('select[name=a_com_select] option').each(function(){
            var th_=$(this);
            if (typeof th_.data('a_menu_a_com')!='undefined'){
                 
                var a_com_name=th_.text();
                var a_com_id=th_.val();
                var a_menu_a_com_txt= th_.data('a_menu_a_com').toString();
              
                if ( (a_menu_a_com_txt.split(',').length - 1) > 0){
                    var a_menu_a_com_arr=a_menu_a_com_txt.split(',');
                }else{
                    var a_menu_a_com_arr=[a_menu_a_com_txt];
                }
              
                
                for (var i in a_menu_a_com_arr){
                    var a_menu_id=a_menu_a_com_arr[i];
                    //создаем пункты функций в меню
                    $('.sortable li[data-id="'+a_menu_id+'"] > .a_menu_block .a_menu_a_com_block').append('<span data-a_com_id="'+a_com_id+'">'+a_com_name+'</span>');
                    
                }
                a_menu_a_com_arr.delete;
            }
            
        });
        
      // alert_m($('.a_com_menu.active').size()); 
    }
}

//выделяем выбранные столбцы
function view_a_admin_a_col(){
   
    $('input[name=chk_active]').closest('.ttable2_tbody_tr[data-id!=""]').removeClass('active');
    $('.sortable li > .a_menu_block .a_menu_a_com_block>span').removeClass('active');
    
    if ($('.admins_change_ul li.active').size()>0){
        var a_admin_id=$('.admins_change_ul li.active').data('id');
        var a_admin_a_col=$('.admins_change_ul li.active').data('col').toString();
        
        if ((a_admin_a_col.split(',').length - 1)>0){var arr_=a_admin_a_col.split(',');}
        else{var arr_=[a_admin_a_col];}
        
        for (var i in arr_){
            $('.a_menu_all_info .ttable2_tbody_tr[data-id="'+arr_[i]+'"] input[name=chk_active]').closest('.ttable2_tbody_tr').addClass('active');
        }
        
        //выбираем функции
        
        var a_admin_a_com=$('.admins_change_ul li.active').data('com').toString();
        //alert(a_admin_a_com);
        if ((a_admin_a_com.split(',').length - 1)>0){var arr_com=a_admin_a_com.split(',');}
        else{var arr_com=[a_admin_a_com];}
        
        for (var j in arr_com){
            var tx=arr_com[j];
            var arr_0=tx.split(':');
            
            $('.sortable li[data-id="'+arr_0[0]+'"] > .a_menu_block .a_menu_a_com_block>span[data-a_com_id='+arr_0[1]+']').addClass('active');
            
        }
        
    }
}

//потеря фокуса
function a_menu_name_blur(th_2,th_){
            if (th_2.val()!=''){
                var data_=new Object();
                data_['_t']='a_menu_name_edit_save';
                data_['a_menu_id']=th_2.closest('li[data-id!=""]').data('id');
                data_['val']=th_2.val();
                
                $.ajax({
                	"type": "POST",
                	"url": "ajax/a_menu.php",
                	"dataType": "text",
                	"data":data_,
                	"success":function(data,textStatus){
                		if (data!='ok'){
                		  alert_m(data,'','error','none');
                		}
                        else{
                            th_.html('<span>'+data_['val']+'</span>').addClass('a_menu_name_edit');
                        }
                        chk_null();
                	}
                });
                
            }else{
                alert_m('Введите название пункта меню!',function(){th_.find('.a_menu_name_edit_input').focus();},'error');
                
            }
        }

//сохранение sid/pid
function save_sort(){
    var data_=new Object();
    data_['_t']='save_sort';
    data_['id_arr']=new Object();
    data_['pid_arr']=new Object();
    
    //перебор по ячейкам
    var i=0;
    $('.sortable li').each(function(){
        data_['id_arr'][i]=$(this).data('id');
        var pid=$(this).closest('ol').closest('li').data('id');
        if (typeof pid=='undefined'){pid=0;}
        data_['pid_arr'][i]=pid;
        i++;
    });
    
    $.ajax({
    	"type": "POST",
    	"url": "ajax/a_menu.php",
    	"dataType": "text",
    	"data":data_,
    	"success":function(data,textStatus){
    		if (data!='ok'){
    		  alert_m('Error:<br />'+data,'','error','none');
    		}                  
    	}
    });
}
//сохранение col sid/pid
function save_sort_col(th_2){
    var data_=new Object();
    data_['_t']='save_sort_col';
    data_['id_arr']=new Object();
    data_['pid_arr']=new Object();
    
    //перебор по ячейкам
    var i=0;
    th_2.find('.ttable2_tbody_tr').each(function(){
        data_['id_arr'][i]=$(this).data('id');
        i++;
    });
    
    $.ajax({
    	"type": "POST",
    	"url": "ajax/a_menu.php",
    	"dataType": "text",
    	"data":data_,
    	"success":function(data,textStatus){
    		if (data!='ok'){
    		  alert_m('Error:<br />'+data,'','error','none');
    		}                  
    	}
    });
}
// Получение информации о меню
function get_menu_info(th_){

        th_.removeClass('ico_down').addClass('ico_loading');
        var data_=new Object();
        data_['_t']='get_menu_info';
        data_['id']=th_.closest('li').data('id');
        var chk_block=th_.closest('li').find('.a_menu_block_del').size();
        var add_txt='';if (a_admin_add=='1'){add_txt='<div class="a_menu_add_new_col ico ico_add" data-id="'+data_['id']+'" title="Добавить новый столбец"></div>';}
        $.ajax({
        	"type": "POST",
        	"url": "ajax/a_menu.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   th_.css({'background-image':''});
               th_.addClass('a_menu_block_hide_info').removeClass('a_menu_block_get_info').removeClass('ico_loading').addClass('ico_up');
               
        	   if (is_json(data)==true){
        	       data_n=JSON.parse(data);
                   if (typeof data_n.chk_active =='object'){
                    
                        if (count(data_n.chk_active)>0){
                            var tbl_='';
                            for (var id in data_n.chk_active){
                                var chk_='';if (data_n.chk_active[id]=='1'){chk_=' checked="checked"';}
                                var chk_v='';if (data_n.chk_view[id]=='1'){chk_v=' checked="checked"';}
                                var chk_c='';if (data_n.chk_change[id]=='1'){chk_c=' checked="checked"';}
                               
                                var tip_=data_n.tip[id];
                                if ((tip_.split('Связанная таблица').length - 1)>0){tip_='<span class="a_menu__change_connect">'+tip_+'</span>';}
                                
                                tbl_+='<div class="ttable2_tbody_tr" data-id="'+data_n.id[id]+'">';
                                tbl_+='<div class="ttable2_tbody_tr_td chk_col"><span class="chk_col_span_bg"><input name="chk_active" type="checkbox" value="1" '+chk_+' /><span class="chk_col_click"></span></span></div>';
                                tbl_+='<div class="ttable2_tbody_tr_td"><span class="ru_col"><input name="col_ru" value="'+data_n.col_ru[id]+'" type="text" /></span></div>';
                                tbl_+='<div class="ttable2_tbody_tr_td"><span class="us_col">'+data_n.col[id]+'</span></div>';
                                tbl_+='<div class="ttable2_tbody_tr_td chk_col"><input name="chk_view" type="checkbox" value="1" '+chk_v+' /></div>';
                                tbl_+='<div class="ttable2_tbody_tr_td chk_col"><input name="chk_change" type="checkbox" value="1" '+chk_c+' /></div>';
                                tbl_+='<div class="ttable2_tbody_tr_td"><span class="tip_col"><span>'+tip_+'</span></span></div>';
                                
                                if (chk_block==1){tbl_+='<div class="ttable2_tbody_tr_td"><span class="ico ico_del a_menu_col_del"></span></div>';}
                                else{tbl_+='<div class="ttable2_tbody_tr_td"></div>';}
                                
                                tbl_+='</div>';
                                //th_.closest('.a_menu_block').find('.a_menu_all_info').append();
                            }
                            if (tbl_!=''){
                                
                                th_.closest('.a_menu_block').find('.a_menu_all_info').html('<hr /><h4>Столбцы:</h4>'+add_txt+'<div style="clear:both;"></div><div class="ttable2"><div class="ttable2_thead"><div class="ttable2_thead_tr"><div class="ttable2_thead_tr_th"></div><div class="ttable2_thead_tr_th">Название</div><div class="ttable2_thead_tr_th">Столбец</div><div class="ttable2_thead_tr_th" title="Отображать">Отобр.</div><div class="ttable2_thead_tr_th" title="Изменять">Изм.</div><div class="ttable2_thead_tr_th">Тип</div><div class="ttable2_thead_tr_th"></div></div></div><div class="ttable2_tbody">'+tbl_+'</div></div>');
                                view_a_admin_a_col();
                                
                                th_2=th_.closest('.a_menu_block').find('.a_menu_all_info .ttable2');
                                //СОРТИРОВКА
                                th_.closest('.a_menu_block').find('.a_menu_all_info .ttable2_tbody').sortable({
                                    delay: 150,
                                    placeholder: "photo_res__item_placeholder",
                                    start: function( event, ui ) {
                                        
                                    },
                                    stop: function( event, ui ) {
                                        setTimeout(function(){save_sort_col(th_2);},200);
                                    }
                                });
                                
                                
                                
                            }
                        }
                        else{
                            th_.closest('.a_menu_block').find('.a_menu_all_info').html('<hr /><h4>Столбцы:</h4>'+add_txt+'<div style="clear:both;"></div>');
                            //alert_m('Не заполнены столбцы для данного пункта!','','error','none');
                        }
                   }else{
                        alert_m('Ошибка получения данных:<br />'+data,'','error','none');
                   }
                   
        	   }else{
        		  alert_m('Error:<br />'+data,'','error','none');
        		}                  
        	}
        });
    
}
// сохранение 
function a_admin_save_info(th_){
   
    var data_=new Object();
    var err_text='';
    data_['_t']='a_admin_save_info';
    data_['col']=th_.attr('name');
    data_['val']=th_.val();
    data_['a_admin_id']=th_.closest('li').data('id');
    
    if (data_['val']==''){err_text+='Введите значение!';}
    
    if (err_text!=''){alert_m(err_text,function(){th_.focus();},'error');}
    else{
        
        $.ajax({
        	"type": "POST",
        	"url": "ajax/a_menu.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        		if (data!='ok'){
        		  alert_m('Error:<br />'+data,'','error','none');
        		}
                else{
                    th_.closest('div').html('<span class="a_admin_edit_span" data-col="'+data_['col']+'">'+th_.val()+'</span>');
                }
        	}
        });
    }
}

//Проверка соответствия объекта и должности
function chk_post_obj(){
    
    $('.set_i_post__zp_target_select option').attr('disabled','disabled').removeAttr('selected');
    
    var obj=$('.set_i_post__add_post option:selected').data('obj');
    $('.set_i_post__zp_target_select option').each(function(){
        if ($(this).data('obj')==obj){
            $(this).removeAttr('disabled');
        }
    });
    $('.set_i_post__zp_target_select option:visible:first').attr('selected');
    $('.set_i_post__zp_target_select').select2({'width':'100%'});
}


//Поиск текущих должностей
function find_zp(a_admin_id,callback){
    callback=callback || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='find_zp';
    data_['a_admin_id']=a_admin_id;
        if (data_['a_admin_id']==''){err_text+='Не определен работник!';}
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt='';
    	            if (count(data_n.i_post)>0){
    	               for (var i in data_n.i_post){
                           var a_admin_i_post=data_n.i_post[i]['id'];
                           var dt1=data_n.i_post[i]['dt1'];
                           var dt2=data_n.i_post[i]['dt2'];
                           var id2=data_n.i_post[i]['id2'];
    	                   i_post_name=$('.set_i_post__add_post option[value="'+id2+'"]').text();
                           var cl_='i_zp_closed';
                                if (dt2==''){
                                    dt2='<span class="btn_gray a_admin_i_post_closed">Уволить</span>';
                                    cl_='i_zp_open';
                                }
                           txt+='<div class="find_zp_item '+cl_+'" data-id="'+_IN(a_admin_i_post)+'">';
        	                   txt+='<div class="i_post_name">'+i_post_name+'</div>';
                               txt+='<div class="data1">Прием на работу: <strong>'+dt1+'</strong></div>';
                               txt+='<div class="data2">Увольнение: '+dt2+'</div>';
                               txt+='<div style="clear:both;"></div>';
                               var txt_tbl='';
                               if (count(data_n.i_post[i]['zp'])>0){
                                    for(var i_obj_id in data_n.i_post[i]['zp']){
                                        var i_obj_name=$('.set_i_post__zp_target_select option[value="'+i_obj_id+'"]').text();
                                        var i_zp_val=data_n.i_post[i]['zp'][i_obj_id];
                                        txt_tbl+='<div class="ttable_tbody_tr"><div class="ttable_tbody_tr_td">'+i_obj_name+'</div><div class="ttable_tbody_tr_td">'+i_zp_val+'</div></div>';
                                    }
                               }
                               if (txt_tbl!=''){
                                   txt+='<h3>Начисление заработной платы:</h3>';
                                   txt+='<div class="ttable find_zp_item_tbl">';
                                   txt+='<div class="ttable_thead">';
                                       txt+='<div class="ttable_thead_tr">';
                                           txt+='<div class="ttable_thead_tr_th">Тип начисления</div>';
                                           txt+='<div class="ttable_thead_tr_th">Размер</div>';
                                       txt+='</div>';
                                   txt+='</div><div class="ttable_tbody">';
                                   txt+=txt_tbl;
                                   txt+='</div></div>';
                               }
                           txt+='</div>';
    	               }
    	            }else{
    	               txt='Нет ни одной должности!';
    	            }
                    $('.find_zp_res').html(txt);
                    if (typeof callback=='function'){callback();}
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}
// Подписать заявление на  увольнение
function zp_closed_save(a_admin_i_post,callback){
    callback=callback || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='zp_closed_save';
    data_['a_admin_i_post']=a_admin_i_post;
    data_['zp_closed_data']=$('input[name="zp_closed_data"]').val();
    data_['zp_closed_info']=$('textarea[name="zp_closed_info"]').val();
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    alert_m('Увольнение подписано!');
                    $('.zp_closed_form').arcticmodal('close');
                    find_zp($('.set_i_post__h1 input[name="a_admin_id"]').val(),function(){
                        if (typeof callback=='function'){callback();}
                    });
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}


// ************************************************************************************************************
// ************************************************************************************************************
// ************************************************************************************************************
$(document).ready(function(){
    change_a_com();
    chk_null();//проверка нулевых значений
    //СОРТИРОВКА
    $('ol.sortable').nestedSortable({
    	forcePlaceholderSize: true,
    	handle: 'div',
    	helper:	'clone',
    	items: 'li',
    	opacity: .6,
        delay: 300,
    	revert: 150,
        placeholder: 'placeholder',
    	tabSize: 25,
    	tolerance: 'pointer',
    	toleranceElement: '.a_menu_get_info',
    	maxLevels: 3,
    	isTree: true,
    	expandOnHover: 500,
    	startCollapsed: true,
        
        update: function (event, ui) {
            save_sort();
        }
    });
    
    //перебор фото админов для подвязки загрузчика
    $('.admins_change_td_img').each(function(){
        var th_=$(this);
        var id_=th_.attr('id');
        var a_admin_id=th_.closest('li').data('id');        
        
        //перезагрузка фото админов
        var uploader_admin = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight,html4',
    		browse_button : id_,
            url : 'ajax/a_menu.php?_t=upload',
            chunk_size : '1mb',
            rename : true,
            dragdrop: true,
            resize: {
                  width: 1000,
                  height: 1000,
                  crop: false,
                  quality:70
            },
            filters : {
                max_file_size : '10mb',
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
                   up.setOption('multipart_params', {a_admin_id : a_admin_id});
                }
            },
            init : {
                QueueChanged: function(up) {uploader_admin.start();},
    			BeforeUpload: function(up, file) {},
                FileUploaded: function(up, file, info) {
                   th_.css({'background':'url(../i/a_admin/original/'+info.response+'?'+Math.random()+') no-repeat center center',"background-size":"contain"});
                }
            }
        });
        uploader_admin.init();
    });
    
    
    
    //Изменение активности
    $(document).delegate('.a_menu_chk_active','change',function(){
        var data_=new Object();
        data_['_t']='a_menu_chk_active';
        data_['a_menu_id']=$(this).closest('li').data('id');
        if ($(this).prop('checked')==true){
            data_['val']='1';
        }
        else{
            data_['val']='0';
        }
        $.ajax({
        	"type": "POST",
        	"url": "ajax/a_menu.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   
               
        		if (data!='ok'){
        		  alert_m('Error:<br />'+data,'','error','none');
        		}
        	}
        });
    });

    
    //разворачиание пункта меню
    $(document).delegate('.sortable .a_menu_block_get_info','click',function(){
        get_menu_info($(this));
    });
    
    //скрыть блок
    $(document).delegate('.a_menu_block_hide_info','click',function(){
        var th_=$(this);
        th_.addClass('a_menu_block_get_info').removeClass('a_menu_block_hide_info').addClass('ico_down').removeClass('ico_up');
        th_.closest('.a_menu_block').find('.a_menu_all_info').html('');
    });
    
    //права доступа
    $(document).delegate('.sortable .a_menu_get_info_div','click',function(e){
       
        if (e.target.nodeName!='INPUT'){//не изменение активности
            var th_=$(this);
            var a_com_menu_class=$('.a_com_menu').attr('class');
            
            if ($('.admins_change_ul>li.active').size()>0){
                // меняем доступ к пункту меню
                var data_=new Object();
                data_['_t']='a_admin_a_menu_save';
                data_['a_admin_id']=$('.admins_change_ul>li.active').data('id');
                data_['a_menu_id']=th_.closest('li').data('id');
                
                var cl_=th_.closest('.a_menu_block').attr('class').toString();
                if ((cl_.split('selected').length - 1)>0){
                    data_['tip']='del';
                    th_.closest('.a_menu_block').removeClass('selected').find('.a_menu_all_info .ttable2 .ttable2_tbody_tr').removeClass('active');
                  
                   
                    
                }else{
                    data_['tip']='add';
                   
                    th_.closest('.a_menu_block').addClass('selected').find('.a_menu_all_info .ttable2 .ttable2_tbody_tr').addClass('active');
                     
                }
                
                // добавляем данные о выбранных пунктах в DOM модель
                $('.admins_change_ul>li.active').data('inc',implode(',',sel_in_array($('.a_menu_block.selected, .a_menu_block.tempselected').closest('li'),'data','id')));
                
                $.ajax({
                	"type": "POST",
                	"url": "ajax/a_menu.php",
                	"dataType": "text",
                	"data":data_,
                	"success":function(data,textStatus){
                	   if (is_json(data)==true){
                            data_n=JSON.parse(data);
                            $('.admins_change_ul>li.active').data('col',data_n.col);
                            //view_a_admin_a_col();
                        }
                        else{
                            alert_m('Error:<br />'+data,'','error','none');
                        }
                       
                        
                	}
                });
                
               
            }
            else if($('.a_com_menu.active').size()>0){
                // привязываем функции админ-меню
                var data_=new Object();
                data_['_t']='a_menu_a_com_save';
                data_['a_com_id']=$('select[name=a_com_select] option:selected').val();
                data_['a_menu_id']=th_.closest('li').data('id');
                
                    var a_menu_id_txt=$('select[name=a_com_select] option:selected').data('a_menu_a_com').toString();
                    var a_menu_id_arr=a_menu_id_txt.split(',');var max_key=count(a_menu_id_arr);
                    
                //alert('1='+a_menu_id_arr);
                var cl_=th_.closest('.a_menu_block').attr('class').toString();
                
                if ((cl_.split('selected_menu').length - 1)>0){
                    data_['tip']='del';
                    th_.closest('.a_menu_block').removeClass('selected_menu');
                    var key=array_search(data_['a_menu_id'],a_menu_id_arr);a_menu_id_arr.splice(key, 1);
                    
                }else{
                    data_['tip']='add';
                    th_.closest('.a_menu_block').addClass('selected_menu');
                    a_menu_id_arr[max_key]=data_['a_menu_id'];   
                }
                //alert('2='+a_menu_id_arr);
                $('select[name=a_com_select] option:selected').data('a_menu_a_com',implode(',',a_menu_id_arr));
                //alert('3='+$('select[name=a_com_select] option:selected').data('a_menu_a_com'));
                $.ajax({
                	"type": "POST",
                	"url": "ajax/a_menu.php",
                	"dataType": "text",
                	"data":data_,
                	"success":function(data,textStatus){
                	   if (data!='ok'){
                            alert_m('Error:<br />'+data,'','error','none');
                        }
                	}
                });
                
            }
            else{
                $(this).closest('.a_menu_block').find('.a_menu_block_get_info').click();
                $(this).closest('.a_menu_block').find('.a_menu_block_hide_info').click();
                
                
            }
            //кнопки
            th_.closest('.a_menu_block').find('.a_menu_a_com_block span').trigger('click');
        }
    });
    
    //Добавляем нового пользователя
    $(document).delegate('.admins_new_user','click',function(){
        var txt='';
        txt+='<form class="add_new_admin">';
        
        //HASH
        txt+='<p class="span_hash_auto">';
        txt+='<span class="span_hash" data-chk_active="1" data-name="Вершинский Алексей" data-email="toowin86@yandex.ru" data-phone="8(963)191-2703" data-comments="Разработчик админ-панели"><span class="ico ico_hash"></span> toowin86@yandex.ru</span>';

        txt+='</p>';
        //end HASH
        
        txt+='<div class="ttable2">';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Активность</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="chk_active" type="checkbox" checked="checked" value="1"/></div>';
        txt+='</div>';
        
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Имя пользователя*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="name" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Email*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="email" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Телефон</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="phone" type="text" maxlength="15" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Фото</div>';
            txt+='<div class="ttable2_tbody_tr_td">';
                txt+='<div class="a_admin_photo_load"><input type="button" id="add_board_photo" value="Обзор..." /></div>';
                txt+='<div class="a_admin_photo_res"></div>';
            txt+='</div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Комментарии</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="comments" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Филиал</div>';
            txt+='<div class="ttable2_tbody_tr_td"><select name="i_tp_admin">'+$('.preload2 .i_tp').html()+'</select></div>';
        txt+='</div>';      
        txt+='</div></form>';
        txt+='<div><center><span class="btn_orange add_new_admin_save">Добавить</span></center></div>';
        
        alert_m(txt,'','adduser','none');
        $('input[name=phone]').integer_().mask('0(000)000-00-00', {placeholder: "_(___)___-__-__",clearIfNotMatch: true});
        $('select[name=i_post_admin], select[name=i_tp_admin]').select2({'width':'100%',allowClear: true});
        
        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight,html4',
    		browse_button : 'add_board_photo',
            url : 'ajax/a_menu.php?_t=upload',
            chunk_size : '1mb',
            rename : true,
            dragdrop: true,
            resize: {
                  width: 1000,
                  height: 1000,
                  crop: false,
                  quality:70
            },
            filters : {
                max_file_size : '10mb',
                mime_types: [
                    {title : "Image files", extensions : "jpg,jpeg,gif,png"}
                ]
            },
    		flash_swf_url : 'js/Moxie.swf',
    		silverlight_xap_url : 'js/Moxie.xap',
            init : {
                QueueChanged: function(up) { uploader.start();},
    			BeforeUpload: function(up, file) {$('.a_admin_photo_res').html('<img src="i/l_20_w.GIF">');},
                FileUploaded: function(up, file, info) {
                    $('.a_admin_photo_res').html('<img data-img="'+info.response+'" class="add_foto_one_new_img" src="../i/a_admin/temp/'+info.response+'" />');
                }
            }
        });
        uploader.init();
    });
    
    //Сохранение нового пользовтеля
    $(document).delegate('.add_new_admin_save','click',function(){
        var th_=$(this);
        var err_text='';
        var data_=$('.add_new_admin').serializeObject();
        data_['img']=$('.add_foto_one_new_img').data('img');
        data_['_t']='add_new_admin_save';
            
            if (data_['name']==''){err_text+='Не заполнено "Имя"!<br />';}
            if (data_['email']==''){err_text+='Не заполнено "email"!<br />';}
            //if (typeof data_['img']=='undefined'){err_text+='Загрузите фотографию пользователя!<br />';}
            
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            th_.addClass('ico').addClass('loading_gray').removeClass('add_new_admin_save').removeClass('btn_orange').text('Добавляем');
             $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
                    th_.removeClass('ico').removeClass('loading_gray').addClass('add_new_admin_save').addClass('btn_orange').text('Добавить');
            		if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        alert_m('Пользователь №'+data_n.id+' успешно добавлен!',function(){window.location.reload(true);},'ok');
                        
                    }
                    else{
                        alert_m('Error:<br />'+data,'','error','none');
                    }
            	    
            	}
            });
        }
    });
    
    
    //Изменение активности пользователя
    $(document).delegate('.admins_change_td_chk_active input[data-col="chk_active"]','change',function(){
        var data_=new Object();
        data_['_t']='a_admin_chk_active_edit';
        data_['a_admin_id']=$(this).closest('li').data('id');
        data_['val']='0';
        if ($(this).prop('checked')==true){ data_['val']='1';}
       
        
        $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   
        	       $('.admins_all_view').trigger('click').trigger('click');
            		if (data!='ok'){
            		  alert_m('Error:<br />'+data,'','error','none');
            		}
                    
            	}
        });
    });
    //Изменение инормации о пользователе
    $(document).delegate('.a_admin_edit_span','click',function(){
        var th_=$(this);
        $(this).closest('div').html('<input name="'+th_.data('col')+'" class="a_admin_save_info" type="text" value="'+th_.text()+'" />');
        
        //для телефона
        if (th_.data('col')=='phone'){
            $('.a_admin_save_info').integer_().mask('0(000)000-00-00', {placeholder: "_(___)___-__-__",clearIfNotMatch: true});
        }
        
        $('.a_admin_save_info').focus().blur(function(){
            a_admin_save_info($(this));
        }).keyup(function(e){if (e.which==13){a_admin_save_info($(this));}});
        
    });
    
    //Удаление пользователя
    $(document).delegate('.a_admin_a_menu_del','click',function(){
        var th_=$(this);
        var data_=new Object();
        data_['_t']='a_admin_del';
        data_['a_admin_id']=th_.closest('li').data('id');
        
        th_.removeClass('a_admin_a_menu_del').html('<span class="ico ico_loading"></span> Удаление');
        $.ajax({
        	"type": "POST",
        	"url": "ajax/a_menu.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        		if (data!='ok'){
        		  th_.addClass('a_admin_a_menu_del').html('<span class="ico ico_del"></span> Удалить');
        		  alert_m('Error:<br />'+data,'','error','none');
        		}
                else{
                    th_.closest('li[data-id!=""]').detach();
                }
                
        	}
        });
        
    });
    
    
    //Добавить новый столбец
    $(document).delegate('.a_menu_add_new_col','click',function(){
        var id_=$(this).data('id');
        var txt='';
        txt+='<form class="add_new_col">'
        
        //HASH
        txt+='<p class="span_hash_auto">';
        txt+='<span class="span_hash" data-col="name" data-tip="Текст" data-col_ru="Название" data-chk_active="1" data-chk_view="1" data-chk_change="1" data-def=""><span class="ico ico_hash"></span> Название</span> ';
        txt+='<span class="span_hash" data-col="chk_active" data-tip="chk" data-col_ru="Активность" data-chk_active="1" data-chk_view="1" data-chk_change="1" data-def="1"><span class="ico ico_hash"></span> Активность</span> ';
        txt+='<span class="span_hash" data-col="html_code" data-tip="HTML-код" data-col_ru="Описание" data-chk_active="1" data-chk_view="0" data-chk_change="1" data-def=""><span class="ico ico_hash"></span> Описание</span> ';
        txt+='<span class="span_hash" data-col="photo" data-tip="Фото" data-col_ru="Фото" data-chk_active="1" data-chk_view="0" data-chk_change="1" data-def="" ><span class="ico ico_hash"></span> Фото</span><br /> ';
        txt+='<span class="span_hash" data-col="data_create" data-tip="Дата-время" data-col_ru="Дата создания" data-chk_active="1" data-chk_view="0" data-chk_change="0" data-def="CURRENT_TIMESTAMP" ><span class="ico ico_hash"></span> Дата создания</span> ' ;
        txt+='<span class="span_hash" data-col="data_change" data-tip="Дата-время" data-col_ru="Дата изменения" data-chk_active="1" data-chk_view="1" data-chk_change="0" data-def="" ><span class="ico ico_hash"></span> Дата изменения</span> ';
        txt+='<span class="span_hash" data-col="price" data-tip="Стоимость" data-col_ru="Цена" data-chk_active="1" data-chk_view="1" data-chk_change="1" data-def=""><span class="ico ico_hash"></span> Цена</span> ';
        txt+='</p>';
        //end HASH
        
        txt+=' <div class="ttable2">';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Активность</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input type="checkbox" value="1" name="chk_active" checked="checked" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Название (ru)*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input type="text" name="col_ru" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Столбец (us)*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input type="text" name="col" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Отображать</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input type="checkbox" value="1" name="chk_view" checked="checked" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Изменять</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input type="checkbox" value="1" name="chk_change" checked="checked" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Тип данных*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><select name="tip">'+$('.tip_col_div').html()+'</select><div class="tip_res"></div></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Значение по умолчанию</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input type="text" value="" name="def"></div></div>';
        txt+='</div>';
        txt+='<div class="a_menu_add_new_col_com"><center><span data-id="'+id_+'" class="btn_orange a_menu_add_new_col_save">Добавить</span></center></div>';
        
        txt+='</form>';
        alert_m(txt,'','add','none','600');
        
        $('select[name=tip] option[data-def=1]').attr('selected','selected');$('input[name=col_ru]').focus();
    });
    
    //выбор типа
    $(document).delegate('select[name=tip]','change',function(){
        $('input[name=col]').removeAttr('disabled');
        var txt='';
        if ($(this).val()=='enum'){
            txt+='<p>Значение для поля enum в формате: <strong>"текст1","текст2"</strong></p><p><input type="text" name="enum_val"></p>';
        }
        else if ($(this).val()=='Связанная таблица 1-max'){
            txt='<p>Укжите таблицу связи:</p><select name="1-max">'+$('.inc_div').html()+'</select>';
        }
        else if ($(this).val()=='Связанная таблица max-max'){
            txt='<p>Укжите таблицу связи max-max:</p><select name="max-max">'+$('.inc_div').html()+'</select>';
        }
        $('.tip_res').html(txt);
    });
    
    //Получение имен сталбцов
    $(document).delegate('select[name=1-max], select[name=max-max]','change',function(){
        var name=$(this).attr('name');
        
        var data_=new Object();
        data_['_t']='get_col';
        data_['a_menu_id']=$(this).val();
        $('select[name='+name+'_div]').detach();
        $('select[name='+name+']').after('<img class="loading_i" src="i/l_20_w.GIF" />');
        $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   $('.loading_i').detach();
                    if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        var txt='';
                        for(var i in data_n.col_id){
                            txt+='<option value="'+data_n.col_id[i]+'">'+data_n.col_ru[i]+'</option>';
                        }
                        if (txt!=''){
                            var chk_name="Checkbox/Мультиселект";if (name=='1-max'){chk_name="Авто-добавление";}
                            $('select[name='+name+']').after('<div class="'+name+'_div"><p>Столбец:</p><p><select name="'+name+'_col">'+txt+'</select></p><p>Условия: <input name="'+name+'_usl" type="text" placeholder="Условия" /></p><p><label><input type="checkbox" name="'+name+'_chk" value="1" checked="checked" /> '+chk_name+'</label></p></div>');
                        }
                    }else{
            		  alert_m('Error:<br />'+data,'','error','none');
            		}
            	}
            });
        
    });
    
    // Выбор по умолчанию
    $(document).delegate('.def_cur','click',function(){
        $('input[name=def]').val($(this).text());
    });
    
    //сохранение нового столбца
    $(document).delegate('.a_menu_add_new_col_save','click',function(){
        $('input[name=col]').val(($('input[name=col]').val())); // переводим в английский
        var th_=$(this);
        var err_txt='';
        var data_=$('.add_new_col').serializeObject();
            if (data_['col']==''){err_txt+='Не заполнено поле "Столбец (us)"!<br />';}
            if (data_['col_ru']==''){err_txt+='Не заполнено поле "Название (ru)	"!<br />';}
            if (data_['tip']=='enum' && data_['enum_val']==''){err_txt+='Заполните значения поля enum!<br />';}
            if (data_['tip']=='Связанная таблица 1-max' && (data_['1-max']=='' || typeof data_['1-max_col']=='undefined')){err_txt+='Укажите таблицу и столбец связи!<br />';}
            if (data_['tip']=='Связанная таблица max-max' && (data_['max-max']=='' || typeof data_['max-max_col']=='undefined')){err_txt+='Укажите таблицу и столбец связи!<br />';}
        
        data_['_t']='add_new_col';
        data_['a_menu_id']=th_.data('id');
        
        if(err_txt!=''){alert_m(err_txt,'','error','none');}
        else{//добавляем
            th_.addClass('ico').addClass('loading_gray').removeClass('a_menu_add_new_col_save').removeClass('btn_orange').text('Добавляем');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	    th_.removeClass('ico').removeClass('loading_gray').addClass('a_menu_add_new_col_save').addClass('btn_orange').text('Добавить');
            		
                    $('.sortable li[data-id="'+data_['a_menu_id']+'"]').find('.a_menu_block_hide_info').click();
                    $('.sortable li[data-id="'+data_['a_menu_id']+'"]').find('.a_menu_block_get_info').click();
                    
                    if (data!='ok'){
            		  alert_m('Error:<br />'+data,'','error','none');
            		}else{
            		  alert_m('Столбец успешно добавлен!','','ok');
                      $('.add_new_col input[name=col_ru], .add_new_col input[name=col]').val('');
            		}
            	}
            });
        }    
        
        
    });
    
    //Добавление новой таблицы
    $(document).delegate('.top_com___add','click',function(){
        var txt='';
        txt+='<form class="add_new_inc">';
        
        //HASH
        txt+='<p class="span_hash_auto">';
        txt+='<span class="span_hash" data-chk_active="1" data-name="Структура" data-inc="s_str" data-comments="Редактор структуры" data-pid="1"><span class="ico ico_hash"></span> Структура</span>';
        txt+='<span class="span_hash" data-chk_active="1" data-name="Каталог" data-inc="s_cat" data-comments="Редактор каталога" data-pid="0"><span class="ico ico_hash"></span> Каталог</span>';
        txt+='<span class="span_hash" data-chk_active="1" data-name="Новости" data-inc="s_news" data-comments="Редактор новостей" data-pid="0"><span class="ico ico_hash"></span> Новости</span>';
        
        txt+='</p>';
        //end HASH
        
        txt+='<div class="ttable2">';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Активность</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="chk_active" type="checkbox" checked="checked" value="1"/></div>';
        txt+='</div>';
        
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Название меню (name)*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="name" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Название таблицы (inc)*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="inc" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Добавить pid (дерево)</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="pid" type="checkbox" value="1" /></div>';
        txt+='</div>';
        
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Комментарии</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="comments" type="text" /></div>';
        txt+='</div>';
        
        txt+='</div></form>';
        txt+='<div><center><span class="btn_orange add_new_inc_save">Добавить</span></center></div>';
        alert_m(txt,'','add','none');
        
    });
    /*
    $(document).delegate('input[name=inc]','keyup',function(){
        $(this).val(ru_us($(this).val()));
    });
    */
    //Сохранение новой таблицы
    $(document).delegate('.add_new_inc_save','click',function(){
        var th_=$(this);
        var err_txt='';
        var data_=$('.add_new_inc').serializeObject();
            if (data_['name']==''){err_txt+='Не заполнено поле "Название меню (name)"!<br />';}
            if (data_['inc']==''){err_txt+='Не заполнено поле "Название таблицы (inc)"!<br />';}
        data_['_t']='add_new_inc';
        if(err_txt!=''){alert_m(err_txt,'','error','none');}
        else{//добавляем
            th_.addClass('ico').addClass('loading_gray').removeClass('add_new_inc_save').removeClass('btn_orange').text('Добавляем');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            		th_.removeClass('ico').removeClass('loading_gray').addClass('add_new_inc_save').addClass('btn_orange').text('Добавить');
            		if (data!='ok'){
            		  alert_m('Error:<br />'+data,'','error','none');
            		}else{
            		  alert_m('Таблица успешно добавлена!',function(){window.location.reload(true);},'ok');
            		}
            	}
            });
        } 
    });
    
    //удаление col
    $(document).delegate('.a_menu_col_del','click',function(){
        var th_=$(this);
        var data_=new Object();
        data_['_t']='del_col';
        data_['a_col_id']=th_.closest('.ttable2_tbody_tr').data('id');
        th_.addClass('ico_loading').removeClass('ico_del');
        $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            		if (data!='ok'){
            		  th_.removeClass('ico_loading').addClass('ico_del');
            		  alert_m('Error:<br />'+data,'','error','none');
            		}else{
            		  
                      th_.closest('li').find('.a_menu_block_hide_info').click().closest('li').find('.a_menu_block_get_info').click();

                      
            		  alert_m('Столбец успешно удален.','','ok');
            		}
            	}
            });
    });
    
    //удаление пункта меню - таблицы
    $(document).delegate('.a_menu_block_del','click',function(){
        var th_=$(this);
        var data_=new Object();
        data_['_t']='del_inc';
        data_['a_menu_id']=th_.closest('li').data('id');
        th_.addClass('ico_loading').removeClass('ico_del');
        $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            		if (data!='ok'){
            		  th_.removeClass('ico_loading').addClass('ico_del');
            		  alert_m('Error:<br />'+data,'','error','none');
            		}else{
            		  th_.closest('li[data-id!=""]').detach();
            		  alert_m('Пункт меню успешно удален. Таблица удалена!','','ok');
            		}
            	}
            });
    });
    
    //Изменение имени таблицы
    $(document).delegate('.a_menu_name_edit','click',function(){
        var th_=$(this);
        th_.html('<input class="a_menu_name_edit_input" type="text" name="name" value="'+th_.text()+'" />').removeClass('a_menu_name_edit');
        th_.find('.a_menu_name_edit_input').focus().blur(function(){
            a_menu_name_blur($(this),th_);
        }).keyup(function(e){if (e.which==13){a_menu_name_blur($(this),th_);}});
    });
    
    
    //Изменение
    $(document).delegate('.a_menu_all_info .ttable2_tbody_tr_td input[name!=""]','change',function(e, myName, myValue){
        var th_=$(this);
        var data_=new Object();
        data_['_t']='col_edit';
        data_['a_col_id']=th_.closest('.ttable2_tbody_tr').data('id');
        data_['a_col_tip']=th_.attr('name');
        data_['a_col_val']=th_.val();
        
        if (th_.attr('type')=='checkbox'){
            if (th_.is(':checked')){data_['a_col_val']='1';}
            else{data_['a_col_val']='0';}
        }
        $.ajax({
        	"type": "POST",
        	"url": "ajax/a_menu.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        		if (data!='ok'){
        		  alert_m(data,'','error','none');
        		}
        	}
        });
        
    });
    
    //Права доступа 
    $(document).delegate('.a_admin_a_menu_getinfo','click',function(){
        
        
        $('.a_com_menu').removeClass('active');
        var th_=$(this).closest('li');
        
        $('.a_menu_block').removeClass('selected');
        
        if ($('.a_menu_block.selected_menu').size()>0){
            $('.a_menu_block').removeClass('selected_menu');
            change_a_com(); //чекаем функии
        }
        
        
        
        if (th_.attr('class')=='active'){
            $('.admins_change_ul li').removeClass('active');
        }else{
            $('.admins_change_ul li').removeClass('active');
            
            th_.addClass('active');
            
            var a_admin_inc=th_.data('inc').toString();
            var arr_=a_admin_inc.split(',');
            
            for (var i in arr_){
                $('.sortable li[data-id="'+arr_[i]+'"] > .a_menu_block').addClass('selected');
            }
        }
        view_a_admin_a_col();
    });
    
    //Изменение доступа для столбцов меню
    $(document).delegate('.selected .chk_col_click','click',function(){
        var th_=$(this);
        var data_=new Object();
        data_['_t']='a_admin_a_com_change';
        data_['a_col_id']=th_.closest('.ttable2_tbody_tr').data('id');
        data_['a_admin_id']=$('.admins_change_ul li.active').data('id');
        
        
        if (typeof th_.closest('.chk_col_span_bg').closest('.ttable2_tbody_tr[data-id!=""]').attr('class')=='undefined'){cl_='';}
        else{
            var cl_=th_.closest('.chk_col_span_bg').closest('.ttable2_tbody_tr[data-id!=""]').attr('class').toString(); 
        }
        
	    var cur_col_id=$('.admins_change_ul li.active').data('col').toString();
        var cur_col_id_arr=cur_col_id.split(',');var max_key=count(cur_col_id_arr);//+1
                  
        if ((cl_.split('active').length - 1)>0){
            data_['tip']='del';
            th_.closest('.chk_col_span_bg').closest('.ttable2_tbody_tr[data-id!=""]').removeClass('active');
            var key=array_search(data_['a_col_id'],cur_col_id_arr);
            cur_col_id_arr.splice(key, 1); 
        }
        else{
            data_['tip']='add';
            th_.closest('.chk_col_span_bg').closest('.ttable2_tbody_tr[data-id!=""]').addClass('active');
            cur_col_id_arr[max_key]=data_['a_col_id'];
        }
        $('.admins_change_ul li.active').data('col',implode(',',cur_col_id_arr)); //записываем данные доступа
        
        
        $.ajax({
        	"type": "POST",
        	"url": "ajax/a_menu.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        		if (data!='ok'){
                    alert_m(data,function(){window.location.reload(true);},'error','none');
                }               
        	}
        });
        
      
    });
    
    // Выбор функции com
    $('select[name=a_com_select]').select2({'width':'100%',allowClear: true}).change(function(){
        if ($(this).val()!=''){
            $('.a_com_options').css({'display':'inherit'});
            if ($('.a_com_menu.active').size()>0){$('.a_com_menu').click().click();}
        }
        else{
            if ($('.a_com_menu.active').size()>0){$('.a_com_menu').click();}
            $('.a_com_options').css({'display':'none'});
        }
    });
    
    // Добавление функции com
    $(document).delegate('.a_com_add','click',function(){
        var txt='';
        txt+='<form class="add_new_com">';
        
        //HASH
        txt+='<p class="span_hash_auto">';
        txt+='<span class="span_hash" data-name="Добавить" data-com="add" data-tip="Общая"><span class="ico ico_hash"></span> Добавить</span>';
        txt+='<span class="span_hash" data-name="Изменить" data-com="change" data-tip="По id"><span class="ico ico_hash"></span> Изменить</span>';
        txt+='<span class="span_hash" data-name="Удалить" data-com="del" data-tip="По id"><span class="ico ico_hash"></span> Удалить</span>';

        txt+='</p>';
        //end HASH
        
        txt+='<div class="ttable2">';
        
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Активность</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="chk_active" type="checkbox" checked="checked" value="1"/></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Название*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="name" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Функция (us)*</div>';
            txt+='<div class="ttable2_tbody_tr_td"><input name="com" type="text" /></div>';
        txt+='</div>';
        txt+='<div class="ttable2_tbody_tr">';
            txt+='<div class="ttable2_tbody_tr_td">Тип</div>';
            txt+='<div class="ttable2_tbody_tr_td"><select name="tip"><option value="Общая">Общая</option><option value="По id">По id</option></select></div>';
        txt+='</div>';      
        txt+='</div></form>';
        txt+='<div><center><span class="btn_orange add_new_com_save">Добавить</span></center></div>';
        
        alert_m(txt,'','add','none');
    });
    
    // Сохранение функции com
    $(document).delegate('.add_new_com_save','click',function(){
        var th_=$(this);
        var err_text='';
        var data_=$('.add_new_com').serializeObject(); 
            if (data_['name']==''){err_text+='Не заполнено поле "Название"!<br />';}
            if (data_['com']==''){err_text+='Не заполнено поле "Функция"!<br />';}
            
        data_['_t']='add_new_com_save';
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            th_.addClass('ico').addClass('loading_gray').removeClass('add_new_com_save').removeClass('btn_orange').text('Добавляем');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   th_.removeClass('ico').removeClass('loading_gray').addClass('add_new_com_save').addClass('btn_orange').text('Добавить');
                   
            	   if (is_json(data)==true){
            			data_n=JSON.parse(data);
                        alert_m('Функция успешно добавлена!','','ok');
                        $('select[name=a_com_select]').append('<option data-a_menu_a_com="" value="'+data_n.id+'">'+data_['name']+'</option>');
                        
                        $('.add_new_com input').val('');
                        $('select[name=a_com_select] option').removeAttr('selected');
                        $('select[name=a_com_select] option[value='+data_n.id+']').attr('selected','selected');$('select[name=a_com_select]').select2({allowClear: true});
                        
            		}
            		else{
            			alert_m(data,'','error','none');
            		}
            	}
            });
        }
    });
    
    
    // Удаление функции com
    $(document).delegate('.a_com_del','click',function(){
        var data_=new Object();
        data_['_t']='a_com_del';
        data_['a_com_id']=$('select[name=a_com_select]').val();
        if (data_['a_com_id']!=''){
            $('.a_com_del').removeClass('ico_del').addClass('ico_loading');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	    $('.a_com_del').addClass('ico_del').removeClass('ico_loading');
            		if (data=='ok'){
            			$('select[name=a_com_select] option').removeAttr('selected');
                        $('select[name=a_com_select] option[value='+data_['a_com_id']+']').detach();
                       $('select[name=a_com_select]').select2({allowClear: true});
            		}
            		else{
            			alert_m(data,'','error','none');
            		}
            	}
            });
        }
    });
    
    // Принадлежность меню функции com
    $(document).delegate('.a_com_menu','click',function(){
        
        //Если активирована передача прав доступа
        if ($('.admins_change_ul li.active').size()>0){$('.admins_change_ul li.active .a_admin_a_menu_getinfo').click();}
        
        var th_=$(this);
        var cl_=th_.attr('class').toString();
        
        if ((cl_.split('active').length - 1)>0){th_.removeClass('active');$('.sortable .a_menu_block').removeClass('selected_menu');}
        else{
            th_.addClass('active');
            //обработка пунктов меню
            var a_menu_a_com_id=$('select[name=a_com_select] option:selected').data('a_menu_a_com').toString();
            	
            var a_menu_a_com_id_arr=a_menu_a_com_id.split(',');
            for (var i in a_menu_a_com_id_arr){
                $('.sortable li[data-id="'+a_menu_a_com_id_arr[i]+'"] > .a_menu_block').addClass('selected_menu');
            }
        
        }
        change_a_com(); // проверяем функции
    });
    
    //Изменение доступа функций 
    $(document).delegate('.a_menu_a_com_block span','click',function(){
        var th_=$(this);
        var cl_=th_.closest('.a_menu_block').attr('class').toString();
        
        if ($('.admins_change_ul li.active').size()>0 && (cl_.split('selected').length - 1) > 0){
            var data_=new Object();
            data_['_t']='a_menu_a_com_block';
            data_['a_com_id']=th_.data('a_com_id');
            data_['a_menu_id']=th_.closest('li[data-id!=""]').data('id');
            data_['a_admin_id']=$('.admins_change_ul li.active').data('id');
            var a_admin_a_com_txt=$('.admins_change_ul li.active').data('com').toString();
            //alert(a_admin_a_com_txt);
            
            if ( (a_admin_a_com_txt.split(',').length - 1) > 0){
                var a_admin_a_com_arr=a_admin_a_com_txt.split(',');
            }else{
                var a_admin_a_com_arr=[a_admin_a_com_txt];
            }
            var max_key=count(a_admin_a_com_arr);
            var col_id=data_['a_menu_id']+':'+data_['a_com_id'];
            
            var cl_=th_.attr('class');
            if (cl_=='active'){
                th_.removeClass('active');
                data_['tip']='del';
                var key=array_search(col_id,a_admin_a_com_arr);a_admin_a_com_arr.splice(key, 1);
                
            }else{
                th_.addClass('active');
                data_['tip']='add';
                a_admin_a_com_arr[max_key]=col_id;
            }
            //alert(implode(',',a_admin_a_com_arr));
            $('.admins_change_ul li.active').data('com',implode(',',a_admin_a_com_arr));
            
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	    if (data!='ok'){
            			alert_m(data,'','error','none');
            		}
            	}
            });
        
        }
    });
    
     // Информация о пункте меню
    $(document).delegate('.a_menu_add_info','click',function(){
        var data_=new Object();
        data_['_t']='a_menu_info';
        data_['a_menu_id']=$(this).closest('li[data-id!=""]').data('id');
        
        
        var txt='';
        txt+='<div class="html_code_div">';
        txt+='<div data-id="'+data_['a_menu_id']+'" class="a_menu_info_save"></div>';
        txt+='<textarea id="editor1" name="editor1">';
        txt+='</textarea></div>';
        
        alert_m(txt,'','html','none','1000');
        var ckeditor1 = CKEDITOR.replace('editor1');
        AjexFileManager.init({returnTo: 'ckeditor', editor: ckeditor1});
        
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	    CKEDITOR.instances.editor1.setData(data) ;
            	}
            });
      
    });
    
    //Сохранение информации
    $(document).delegate('.a_menu_info_save','click',function(){
        var data_=new Object();
        data_['_t']='a_menu_info_save';
        data_['a_menu_id']=$(this).data('id');
        data_['html']=CKEDITOR.instances.editor1.getData();
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	     if (data!='ok'){
            	       alert_m(data,'','error','none');
            	     }else{
                       CKEDITOR.instances.editor1.destroy();$.arcticmodal('close');
                       alert_m('Информация сохранена!',function(){$('html,body').css({'width':'inherit','height':'inherit','overflow':'inherit','z-index':'inherit','position':'inherit'});},'ok');
            	     }
            	}
            });
    });
    
    //показать скрытых
    $(document).delegate('.admins_all_view','click',function(){
        $('.admins_change_ul li').css({'display':'inherit'});
        $(this).detach();
    });
    
    //Получение информаци о connect
    $(document).delegate('.a_menu__change_connect','click',function(){
        var tip_=$(this).text();
        var err_text='';
            
        var data_=new Object();
        data_['_t']='get_info_connect';
        data_['a_col_id']=$(this).closest('.ttable2_tbody_tr').data('id');
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	    if (is_json(data)==true){
            	        data_n=JSON.parse(data);
                        
                        if (data_n.chk=='1'){var chk0='';var chk1=' selected="selected"';}
                        else{var chk0=' selected="selected"';var chk1='';}
                        
                        var txt='';
                        txt+='<form class="a_menu__change_connect_form">';
                        
                        if (tip_=='Связанная таблица max-max'){txt+='<div>Мульти-select или chkbox:</div><div><select name="chk" class="select2"><option value="0"'+chk0+'>Checkbox</option><option value="1"'+chk1+'>Мульти-select</option></select></div>';}
                        else if (tip_=='Связанная таблица 1-max'){txt+='<div>Автодобавление или select:</div><div><select name="chk" class="select2"><option value="0"'+chk0+'>Мульти-выбор</option><option value="1"'+chk1+'>Авто-добавление</option></select></div>';}
            
                        txt+='<div>Условия связи:</div>';
                        txt+='<div><input type="text" name="usl" value="'+data_n.usl+'" placeholder="Условия связи" /></div><br />';
                        txt+='<div><center><span data-id="'+data_['a_col_id']+'" class="btn_orange a_menu__change_connect_save">Сохранить</span></center></div>';
                        txt+='</form>';
                        
                        alert_m(txt,'','change','none','450');
            	    }else{
                       alert_m(data,'','error','none');
                    }
            	}
            });
        }
    });
    
    //Сохранение информации о connect
    $(document).delegate('.a_menu__change_connect_save','click',function(){
        var data_=$('.a_menu__change_connect_form').serializeObject(); //форма
        var th_=$(this);
        data_['a_col_id']=$(this).data('id');
        data_['_t']='save_info_connect';
        th_.closest('center').html('<span class="ico ico_loading"></span>');
        $.ajax({
            	"type": "POST",
            	"url": "ajax/a_menu.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   th_.closest('center').html('<span data-id="'+data_['a_col_id']+'" class="btn_orange a_menu__change_connect_save">Сохранить</span>');
            	    if (is_json(data)==true){
            	        $('.a_menu__change_connect_form').arcticmodal('close');
                        alert_m('Данные сохранены!');
            	    }else{
                       alert_m(data,'','error','none');
                    }
            	}
            });
        
    });
    
    //выгрузка БД
    $(document).delegate('.top_com___loading','click',function(e){e.preventDefault();});
    $(document).delegate('.__a_menu__copy_db','click',function(e){
        e.preventDefault();
        var th_=$(this);
        var href_=th_.attr('href');
        th_.addClass('top_com___loading').addClass('loading38').removeClass('__a_menu__copy_db').css({'display':'block'});
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php"+href_,
    		"dataType": "text",
    		"success":function(data,textStatus){
    		    th_.addClass('__a_menu__copy_db').removeClass('loading38').removeClass('top_com___loading');
    	        alert_m(data,'','ok',5000);      
    		}
    	});
        
    });
    $(document).delegate('.ico_loading','click',function(e){e.preventDefault();});
    $(document).delegate('.ico_change_db','click',function(e){
        e.preventDefault();
        var th_=$(this);
        var href_=th_.attr('href');
        th_.addClass('ico_loading').removeClass('ico_change_db');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php"+href_,
    		"dataType": "text",
    		"success":function(data,textStatus){
    		  th_.addClass('ico_change_db').removeClass('ico_loading');
    	        alert_m(data,'','ok',5000);      
    		}
    	});
        
    });
    //end выгрузка БД
    
    //Филиалы
    $('select[name="a_admin_i_tp_id"]').select2({'width':'100%',allowClear: true}).change(function(){
        var th_=$(this);
        var data_=new Object();
        data_['a_admin_id']=th_.closest('li').data('id');
        data_['i_tp_id']=th_.val();
        data_['_t']='a_admin_i_tp_change';
        $.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php",
    		"dataType": "text",
            "data":data_,
    		"success":function(data,textStatus){
    	        if (data!='ok'){
    	           alert_m(data,'','error','none');
    	        }
    		}
    	});
    });
    
    
    //Должности/зп
    $(document).delegate('.set_i_post','click',function(){
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='set_i_post';
        data_['a_admin_id']=$(this).closest('li').data('id');
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        alert_m(data,'','add','none');
                $('.set_i_post__add_post').select2({'width':'100%'}).change(function(){
                    chk_post_obj();
                });
                $('.set_i_post__zp_val_input').autoNumeric('init');
                $('.set_i_post__zp_target_select').select2({'width':'100%'}).change(function(){
                    
                    var tip_txt =$('.set_i_post__zp_target_select option:selected').text();
                    if ((tip_txt.split('вручную').length - 1)==0){
                        $('.set_i_post__zp_val_input').removeAttr('disabled');
                    }
                    else{
                        $('.set_i_post__zp_val_input').val('');
                        $('.set_i_post__zp_val_input').attr('disabled','disabled');
                    }
                    
                });
                $('.set_i_post__data_input').datetimepicker({lang:'ru',timepicker:false, mask:true,format:'d.m.Y',closeOnDateSelect:true});
                $('.set_i_post__data_input_end').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true});
	           
                chk_post_obj();
                find_zp(data_['a_admin_id']);
            }
    	});
    });
    
    //Добавляем расчет з/п
    $(document).delegate('.set_i_post__zp_target_add','click',function(){
        var err_text='';
        var tip_id =$('.set_i_post__zp_target_select').val();
        var tip_txt =$('.set_i_post__zp_target_select option:selected').text();
        var val =$('.set_i_post__zp_val_input').val()-0;
        
        if ((tip_txt.split('вручную').length - 1)==0){
            if (val==0 || val!=$('.set_i_post__zp_val_input').val()){err_text+='Проверьте значение!';}
        }
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            var txt='<div class="ttable_tbody_tr">';
                txt+='<div class="ttable_tbody_tr_td"><span class="tip_zp_cur" data-id="'+_IN(tip_id)+'">'+tip_txt+'</span></div>';
                txt+='<div class="ttable_tbody_tr_td"><span class="val_zp_cur">'+val+'</span></div>';
                txt+='<div class="ttable_tbody_tr_td set_i_post__add_all_zp_com"><i class="fa fa-minus" title="Удалить"></i></div>';
            txt+='</div>';
            $('.set_i_post__add_all_zp_tbl .ttable_tbody').prepend(txt);
        }
    });
    
    //Сохранение
    $(document).delegate('.add_form_save','click',function(){
        var err_text='';
        var data_=new Object();
        data_['_t']='add_form_save';
        data_['a_admin_id']=$('input[name="a_admin_id"]').val();
        data_['i_post_id']=$('.set_i_post__add_post').val();
        data_['i_post_data']=$('.set_i_post__data_input').val();
        var i=0;
        data_['i_zp_tip']=new Object();
        data_['i_zp_val']=new Object();
        $('.set_i_post__add_all_zp_tbl.ttable .ttable_tbody_tr').each(function(){
            data_['i_zp_tip'][i]=$(this).find('.tip_zp_cur').data('id');
            data_['i_zp_val'][i]=$(this).find('.val_zp_cur').text();
            i++;
        });
        if (count(data_['i_zp_tip'])==0){
            err_text+='Укажите формулы расчета з/п';
        }
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            
        	loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/a_menu.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			loading(0);
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        $('.set_i_post__add_all_zp_tbl.ttable .ttable_tbody').html('');
        	            find_zp(data_['a_admin_id']);
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    //Удаляем расчет з/п
    $(document).delegate('.set_i_post__add_all_zp_com .fa-minus','click',function(){
        $(this).closest('.ttable_tbody_tr').detach();
    });
    
    //Должности
    $('select[name="a_admin_i_post_id"]').select2({'width':'650px',allowClear: true}).change(function(){
        var th_=$(this);
        var data_=new Object();
        data_['a_admin_id']=th_.closest('li').data('id');
        data_['i_post_id']=th_.val();
        data_['_t']='a_admin_i_post_change';
        $.ajax({
    		"type": "POST",
    		"url": "ajax/a_menu.php",
    		"dataType": "text",
            "data":data_,
    		"success":function(data,textStatus){
    	        if (data!='ok'){
    	           alert_m(data,'','error','none');
    	        }
    		}
    	});
    });
    
    //Увольняем
    $(document).delegate('.a_admin_i_post_closed','click',function(){
        var txt='<div class="zp_closed_form" data-id="'+$(this).closest('.find_zp_item').data('id')+'"><h1>Укажите дату и причину увольнения!</h1>';
        txt+='<p>Дата увольнения:</p>';
        txt+='<div><input type="text" name="zp_closed_data" placeholder="Дата увольнения" value="<?=date('d.m.Y');?>" /></div>';
        txt+='<p>Причина увольнения:</p>';
        txt+='<div><textarea name="zp_closed_info" placeholder="Причина увольнения"></textarea></div>';
        txt+='<div><center><span class="btn_orange zp_closed_save">Подписать увольнение</span></center></div></div>';
        alert_m(txt,'','info','none');
        $('input[name="zp_closed_data"]').datetimepicker({lang:'ru',timepicker:false, mask:true,format:'d.m.Y',closeOnDateSelect:true});
    });
    
    //Подписываем увольнение
    $(document).delegate('.zp_closed_save','click',function(){
        var a_admin_i_post=$('.zp_closed_form').data('id');
        zp_closed_save(a_admin_i_post);
    });
    
    //Изменение даты приема на работу
    $(document).delegate('.i_zp_open .data1 strong','click',function(){
        var th_=$(this);
        var cur_data=th_.text();
        th_.closest('.data1').html('Прием на работу: <input type="text" class="i_zp_open_data1_input" value="'+_IN(cur_data)+'" />');
        $('.i_zp_open_data1_input').datetimepicker({lang:'ru',timepicker:false, mask:true,format:'d.m.Y',closeOnDateSelect:true,onSelectDate:function(ct,$i){
            $('.i_zp_open_data1_input').trigger('blur');
        }});
        $('.i_zp_open_data1_input').focus();
    
    });
    //сохраняем новую дату приема на работу
    $(document).delegate('.i_zp_open_data1_input','blur',function(){
        var th_=$(this);
        var err_text='';
        var data_=new Object();
        data_['_t']='i_zp_open_data1_input_save';
        data_['i_zp_id']=th_.closest('.i_zp_open').data('id');
        data_['data1']=th_.val();
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/a_menu.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			loading(0);
                    th_.closest('.data1').html('Прием на работу: <strong>'+data_['data1']+'</strong>');
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
    
    //Изменение просмотра товаров
    $(document).delegate('input.chk_view_all_s_cat','change',function(){
        
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='chk_view_all_s_cat_change';
        data_['a_admin_id']=th_.closest('li').data('id');
        data_['val']=th_.prop('checked');
        if (data_['val']==true){data_['val']='1'}else{data_['val']='0';}
      
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/a_menu.php",
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
    
    
});
</script>