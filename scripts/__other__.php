<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script src="js/jquery.mjs.nestedSortable.js"></script>
<script type="text/javascript">
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
if ($tx==1){echo "var __other__add=1;\n";}
else{echo "var __other__add=0;\n";}
//************ ИЗМЕНЕНИЕ *************
$tx=0;
    foreach ($a_com_arr['com'] as $a_com_id => $com_){
        if ($com_=='change'){
            if (isset($a_admin_a_com_arr[$a_com_id])){
                $tx=1;
            }
        }
    }
if ($tx==1){echo "var __other__change=1;\n";}
else{echo "var __other__change=0;\n";}
//************ УДАЛЕНИЕ *************
$tx=0;
    foreach ($a_com_arr['com'] as $a_com_id => $com_){
        if ($com_=='del'){
            if (isset($a_admin_a_com_arr[$a_com_id])){
                $tx=1;
            }
        }
    }
if ($tx==1){echo "var __other__del=1;\n";}
else{echo "var __other__del=0;\n";}
?>

var start_select=0;
var start_select_move=0;
var start_quick_change=0;//маркер быстрого редактирования
var select_class='';
var null_val='<img src="i/mess_add.png" style="width:18px;"/>';
var editor_quick_change;
var window_add_open=0;
var loading_rows="<?=$_SESSION['a_options']['Количество загружаемых строк'];?>";

var history_name=''; //история URL
var history_url=''; //история URL

var parsing_chk=0;
var source_autocomplete; //переменная на автокомплит
var jqxhr;

var tree='<?php if(isset($inc) and isset($_SESSION['tree_view'][$inc]) and isset($_SESSION['tree_view'][$inc])){echo $_SESSION['tree_view'][$inc];}else{echo 'close';}?>';
var copy_form_id='';
var copy_form_name='';
// все функции
function all_functions(col_,tip_,th_){
   
<?php

$sql = "SELECT  a_col.`col`

				FROM a_col
					WHERE a_col.chk_active='1'
                    AND a_col.a_menu_id='"._DB($inc_id)."'
                    AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
                    AND a_col.`tip`='Функция'
               
                
"; 
$res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
while ($myrow = mysql_fetch_array($res)) 
{
    ?>
    
        if (col_=='<?=$myrow[0];?>' || tip_=='start'){
    <?php
    $file_='scripts/__function_'.$inc.'_'.$myrow[0].'.php';
    if (file_exists($file_)){
        
        include $file_;
        
    } else{
        echo 'alert_m("Нет файла: '.$file_.'","","error","none");';
        
    }
    ?>
        }
    <?php
}

?>
} 
//СОХРАНЕНИЕ
function save(cur_class, callback){
    cur_class= cur_class || '__other__add__save';
    callback = callback || '';
    var err_text='';
        
        //дата изменения
       $('.__other__add_form .ttable .ttable_tbody_tr').each(function(){
            if ($(this).css('display')=='none'){
                $(this).detach();
            }
        });
        
        var data_=$('.__other__add_form').serializeObject();
        
        data_['_t']='save';
        data_['_inс']=$('#page_res').data('inc');
        data_['_nomer']= $('.__other__add_form .row_id__span').text();
        data_['_col']=$('.__other__add_form .row_id__span').data('col');
       
        //перебор по ячейкам
        $('.__other__add_form .ttable .ttable_tbody_tr').each(function(){
            var th_=$(this);
            var col_=th_.data('col');
            var tip_=th_.data('tip');
            var display_=th_.css('display');
           
            //ошибки
            if (col_=='name' && display_!='none'){
                if (typeof data_['name']!='undefined'){
                    if (data_['name']==''){err_text+='Не заполнено поле name!<br />';}
                }
            }
        
            //дополнительная обработка полей
            if (tip_=='HTML-код'){
              
                    data_[col_]=CKEDITOR.instances[th_.find('.html_editor').attr('id')].getData();
                    
            }
            else if (tip_=='Связанная таблица max-max'){
                    if (th_.find('div.connect_max_max').size()>0){
                        data_[th_.find('div.connect_max_max').data('col_id_connect')]=sel_in_array(th_.find('div.connect_max_max ul li div.active').closest('li'),'data','id');
                    }
            }
            else if (tip_=='Функция'){
                    err_text+=all_functions(col_,'save',data_); //save
            }
            else{
                
            }
        });
        
        //ФОТО
        data_['photo_img']=sel_in_array($('.__other__add_form .photo_res__item'),'data','img');
         
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            top_menu('loading_s');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/__other__.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	  
            	   if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        
                        
                        //замена фото
                        var i_img=0;
                        $('.__other__add_form .__other__photo_res .photo_res__item').each(function(){
                            $(this).data('img',data_n.img[i_img]);
                            i_img++;
                        });
                        
                        if (data_n.new_>0){ // добавление
                            $('.__other__add_form').data('save','1');
                            var new_=data_n.new_;
                            alert_m('Запись №'+new_+' успешно добавлена!',function(){
                                
                               if (cur_class=='__other__add__saveclose'){
                                    $('.__other__add_form').arcticmodal('close'); 
                                    $('.__other__add_form').detach();
                               }
                               else if(cur_class=='__other__add__saveadd'){
                                    top_menu('saveadd');
                                
                               }else{
                                    $('.__other__add_form h2').html('Изменение значения №<span class="row_id__span">'+new_+'</span>:');
                                    $('.__other__add_form .__other__add_copy_from_div').hide();
                                    if (window_add_open==1){top_menu('savechange');}else{top_menu('add');}
                               }
                               if (typeof callback=='function'){callback(data_n);}
                            });
                            
                            //последняя страница
                            $('.__other__open_lastpage').html('(<a href="?inc='+data_['_inc']+'&com=_change&nomer='+new_+'">Последняя страница</a>)');
                           
                            
                        }else{ // изменение
                            alert_m('Запись успешно сохранена!',function(){
                                if (cur_class=='__other__add__saveclose'){
                                    $('.__other__add_form').arcticmodal('close'); 
                                    $('.__other__add_form').detach();
                               }
                               else if(cur_class=='__other__add__saveadd'){
                                    $('.__other__add_form .__other__add_copy_from_div').show();
                                    $('.__other__add_form h2').html('Добавление нового значения<span class="row_id__span"></span>');
                                    top_menu('saveadd');
                               }else{
                                    if (window_add_open==1){top_menu('savechange');}else{top_menu('add');}
                               }
                               if (typeof callback=='function'){callback(data_n);}
                            });//,'ok','none'
                        }
            		}
                    else{
                        //Определить текущий класс
                        if ($('.modal_change').size()>0){top_menu('savechange');}
                        else if ($('.modal_add').size()>0){top_menu('saveadd');}
                        else{alert('Определить текущий класс');}
                        
                        alert_m(data,function(){
                            window.location.reload(true);
                        },'error','none');
                    }                
            	}
            });
        }
        
}
//ПАРСИНГ
function parsing(){ //tip=parsing_start, tip=parsing_add
    
    if (parsing_chk==0){
        var err_text='';
        var data_=new Object();
       
            data_['_parsing__link']=$('.modal_add input[name="_parsing__link"]').val();
            data_['_parsing__name']=$('.modal_add input[name="_parsing__name"]').val();
            data_['_parsing__page']=$('.modal_add input[name="_parsing__page"]').val();
            data_['_parsing__block']=$('.modal_add input[name="_parsing__block"]').val();
            data_['_parsing__link_item']=$('.modal_add input[name="_parsing__link_item"]').val();
            data_['_parsing__if']=$('.modal_add input[name="_parsing__if"]').val();
            data_['_parsing__work']=$('.modal_add select[name="_parsing__work"]').val();
            data_['_parsing__main']=$('.modal_add select[name="_parsing__main"]').val();
            data_['_parsing__update']=$('.modal_add select[name="_parsing__update"]').val();
            
            $('.modal_add .__other__parsing_res .ttable_tbody .ttable_tbody_tr').each(function(){
                var th_=$(this);
                //alert(th_.find('input[type="checkbox"]').attr('checked')+' - '+th_.find('.parsing_col_chk').prop('checked'));
                if (th_.find('input[type="checkbox"]').prop('checked')==true){
                    data_[th_.find('input[type="checkbox"]').attr('name')]='1';
                    th_.find('.ttable_tbody_tr_td:last').find('input[name!=""], textarea[name!=""], select[name!=""]').each(function(){
                        var th_2=$(this);
                        var name_=th_2.attr('name');
                        var val_=th_2.val();
                        data_[name_]=val_;
                    });
                    
                }
            });
        data_['_t']='parsing';
         
        data_['_inс']=$('#page_res').data('inc');
        if (data_['parsing__link']==''){err_text+='Не указана "Ссылка на сайт*"<br />';}
        if (data_['parsing__block']==''){err_text+='Не указан "Селектор блока товара*"<br />';}
       
        
            var col_main=$('.__other__parsing_form select[name=_parsing__main]').val();
            var val_main= $('.__other__parsing_form input[name=parsing_'+col_main+']').val();
                if (typeof val_main=='undefined'){val_main='';}
        if ( (val_main.split('$article').length - 1)==0){err_text+='В основном столбце должна быть указана переменная $article!<br />';}
        if ( (val_main.split('$html_in').length - 1)>0){err_text+='В основном столбце не должна быть указана переменная $html_in!<br />';}
        
        
        $('.__other__parsing_form .select_multi').each(function(){
            if ($(this).closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:first input[type=checkbox]').attr('checked')=='checked' && $(this).find('option:selected').size()==0){
                err_text+='Не заполнен "'+$(this).closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:first').next().text()+'"<br />';
            }
        });
        
        if (err_text!=''){alert_m(err_text,'','error','none');$('.parsing__stop').closest('center').html('<span class="btn_orange parsing__start">Продолжить</span>');}
        else{
            
        $('.parsing__stop').closest('center').append('<span class="loading_parsing ico ico_loading"></span>');
        jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/__other__.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            	   $('.loading_parsing').detach();
            	   if (is_json(data)==true && count(JSON.parse(data))>0){
                        data_n=JSON.parse(data);

                            var add_=parseInt(data_n.add);
                            var upp_=parseInt(data_n.upp);
                            
                            var data_arr=data_n._d;
                            var txt='';
                            var txt_head='';
                            
                            for (var i in data_arr){
                                var data_val_arr=data_arr[i];
                                txt+='<div class="ttable_tbody_tr">';
                                for (var j in data_val_arr){
                                    if (i==0){//формируем шапку
                                        txt_head+='<div class="ttable_thead_tr_th">'+j+'</div>';
                                    }
                                    if(typeof data_val_arr[j]=='object'){
                                        var data_object=data_val_arr[j];
                                        var data_object_txt='';
                                        for (var k in data_object){
                                            if (data_object_txt!=''){data_object_txt+=', ';}
                                            data_object_txt+=data_object[k];
                                        }
                                        txt+='<div class="ttable_tbody_tr_td">'+data_object_txt+'</div>';
                                    }
                                    else{
                                        txt+='<div class="ttable_tbody_tr_td">'+data_val_arr[j]+'</div>';
                                    }
                                    
                                    
                                }
                                txt+='</div>';
                            }
                           
                                alert_m('<h2 class="page_name">Добавлено: '+add_+', обновлено: '+upp_+'</h2><div class="ttable"><div class="ttable_thead"><div class="ttable_thead_tr">'+txt_head+'</div></div><div class="ttable_tbody">'+txt+'</div></div>',function(){
                                    var link_=data_n.link_;
                                    if (link_!=''){
                                       
                                        $('input[name=_parsing__link]').val(link_);
                                        parsing();
                                    }
                                },'ok',10000);
                       
                        
                        
                    }else{
                        alert_m(data,function(){parsing();},'error','none');
                       
                    }  
            	}
            });
        
        }
    }
}
    
    
//сохранение при перетаскивание
function save_tree_sort(){
    if ($('.__other__tree').size()>0){
        var data_=new Object();
        data_['_t']='save_sort';
        data_['_inc']=$('#page_res').data('inc');
        var ii=new Object();
        var pp=new Object();
        data_['i']=new Object();
        data_['p']=new Object();
        
        //перебор по ячейкам
        var i=0;
        $('.a_menu_block').each(function(){
            
            ii[i]=$(this).find('.ttable_tbody_tr').data('id');
            var pid=$(this).closest('ol').closest('li').find('.ttable_tbody_tr').data('id');
            if (typeof pid=='undefined'){pid=0;}
            pp[i]=pid;
            
            i++;
        });
        data_['i']=JSON.stringify(ii);
        data_['p']=JSON.stringify(pp);
        
        
        $.ajax({
        	"type": "POST",
        	"url": "ajax/__other__.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        		if (is_json(data)==false){
        		  alert_m('Error:<br />'+data,'','error','none');
        		}                  
        	}
        });
    }
}
//разворачиваем дерево
function open_tree(){
   $('.fa_max_tree').detach();
   $('.__other__tree ol').closest('.a_menu_block_div').prepend('<i class="fa fa-minus fa_min_tree"></i>');
   $('.__other__tree ol').show();
   tree='open';
   var data_=new Object();
   data_['_t']='open_tree';
   data_['inc']=$('#page_res').data('inc');
    $.ajax({
    	"type": "POST",
    	"url": "ajax/__other__.php",
    	"dataType": "text",
    	"data":data_,
    	"success":function(data,textStatus){                
    	}
    });
   
}
//сворачиваем дерево
function close_tree(){
   $('.fa_min_tree').detach();
   $('.__other__tree ol').closest('.a_menu_block_div').prepend('<i class="fa fa-plus fa_max_tree"></i>');
   $('.__other__tree ol').hide();
   tree='close';
   var data_=new Object();
   data_['_t']='close_tree';
   data_['inc']=$('#page_res').data('inc');
    $.ajax({
    	"type": "POST",
    	"url": "ajax/__other__.php",
    	"dataType": "text",
    	"data":data_,
    	"success":function(data,textStatus){
    	}
    });
}
//Меняем отображение столбцов для пользователей
function a_col_change(col_,inc_,tip, callback){
    callback =callback || '';
    tip = tip || 1;
    var err_text='';
    var data_=new Object();
    data_['_t']='a_col_change';
    data_['col']=col_;
    data_['inc']=inc_;
    data_['tip']=tip;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/__other__.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof callback=='function'){
    	               callback();
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

function sorting_tree_on(){
    if ($('ol.sortable').size()>0){
    $('ol.sortable').nestedSortable({ 
        forcePlaceholderSize: false,
        autoScroll: false,
		handle: '#sorting_tree_btn',
        delay: 300,
		helper:	'clone',
		items: 'li',
		opacity: 0.5,
		placeholder: 'placeholder',
		revert: 250,
		tolerance: 'pointer',
		toleranceElement: '.a_menu_block',
        update: function(up){
           save_tree_sort();
        }
    });
    }
}

//формируем дерево
function create_tree(id_,arr_all){
    var txt='';
    var arr_=arr_all._d;
    
    var tip_arr=arr_all.tip;
    var col_arr=arr_all.col;
    var chk_change_arr=arr_all.chk_change_;
    
    for (var i in arr_){
      
        if (arr_[i]['pid']==id_){
            
            var txt_='';
            for (var j in tip_arr){
               
                    var dis_=' disabled="disabled"'; 
                    var cl_quick_change='';
                    
                    if (chk_change_arr[j]=='1' && __other__change=='1'){cl_quick_change=' class="quick_change"';dis_='';}
                    
                if (tip_arr[j]=='Текст'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__text" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Длинный текст'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__longtext" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='HTML-код'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__htmlcode" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Целое число'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__int" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Дробное число'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__float" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Стоимость'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__price" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+gap(arr_[i][col_arr[j]])+'</span></div></div>';
                }
                else if(tip_arr[j]=='Дата'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__data" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Дата-время'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__datatime" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Телефон'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__phone" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Email'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__email" data-col="'+col_arr[j]+'"><div class="__other__tree__sort_div"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div></div>';
                }
                else if(tip_arr[j]=='Связанная таблица 1-max'){
                    txt+='<div class="ttable_tbody_tr_td __other_td__1max" data-col="'+col_arr[j]+'"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div>';
                }
                else if(tip_arr[j]=='Связанная таблица max-max'){
                    //var text_=implode(', ',arr_[i][col_arr[j]]);
                    var text_='';//implode(', ',val_arr[arr_col[col_id]]);
                        if (typeof arr_[i][col_arr[j]]=='object'){
                            for (var kkk in arr_[i][col_arr[j]]){
                                if (typeof arr_[i][col_arr[j]]!='undefined' && typeof arr_[i][col_arr[j]+'_']!='undefined' && typeof arr_[i][col_arr[j]+'_'][kkk]!='undefined'){
                                if (text_!=''){text_+=', ';}
                                    text_+='<span data-id="'+arr_[i][col_arr[j]][kkk]+'">'+arr_[i][col_arr[j]+'_'][kkk]+'</span>';
                                }
                            }
                        }
                                        
                    txt_+='<div class="ttable_tbody_tr_td __other_td__maxmax" data-col="'+col_arr[j]+'"><span'+cl_quick_change+'>'+text_+'</span></div>';
                }
                else if(tip_arr[j]=='Функция'){
                    var th_=$(this);
                    var result_=all_functions(th_.data('col'),'find',th_);
                    if (typeof result_=='undefined'){result_='';}
                    
                    txt_+='<div class="ttable_tbody_tr_td __other_td__funct __other_td__funct_'+col_arr[j]+'" data-col="'+col_arr[j]+'"><span>'+result_+'</span></div>';
                }
                else if(tip_arr[j]=='chk'){
                    var chk_='';
                    if (arr_[i][col_arr[j]]==1){chk_=' checked="checked"';}
                    txt_+='<div class="ttable_tbody_tr_td __other_td__chk" data-col="'+col_arr[j]+'"><input'+dis_+cl_quick_change+' type="checkbox"'+chk_+' value="1" /></div>';
                }
                else if(tip_arr[j]=='enum'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__enum" data-col="'+col_arr[j]+'"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div>';
                }
                else if(tip_arr[j]=='Цвет'){
                    txt_+='<div class="__other_td__color" data-col="'+col_arr[j]+'"><span'+cl_quick_change+'>'+arr_[i][col_arr[j]]+'</span></div>';
                                   
                }
                else if(tip_arr[j]=='Фото'){
                    txt_+='<div class="ttable_tbody_tr_td __other_td__photo" data-col="'+col_arr[j]+'">';
                    var result_=arr_[i]['photo_img'];
                    var result_tip=arr_[i]['photo_tip'];
                    var kol_photo=0;
                    for (var key_ in result_){
                        if (result_tip[key_]=='Основное' && kol_photo==0){
                            kol_photo++;
                            txt_+='<a rel="a_photo_'+arr_[i]['id']+'" class="zoom" href="../i/<?=$inc;?>/original/'+result_[key_]+'"><img src="../i/<?=$inc;?>/small/'+result_[key_]+'" border="0" /></a>';
                        }else{
                            txt_+='<a rel="a_photo_'+arr_[i]['id']+'" class="zoom" href="../i/<?=$inc;?>/original/'+result_[key_]+'"></a>';
                        }
                    }
                    
                    txt_+='</div>';
                }else if(tip_arr[j]=='Ссылка'){
                    var link_=arr_[i][col_arr[j]];
                    cl_quick_change='';
                    txt_+='<div class="ttable_tbody_tr_td __other_td__link" data-col="'+col_arr[j]+'"><span'+cl_quick_change+'><a href="'+link_+'" target="_blank" >'+link_.substr(0,16)+'...</a></span></div>';
                }
            }
            
            txt+='<li><div class="a_menu_block_div">';
                txt+='<div class="ttable a_menu_block">';
                    txt+='<div class="ttable_tbody_tr" data-id="'+arr_[i]['id']+'">';
                        txt+='<div class="ttable_tbody_tr_td __other_td__id">';
                            
                            if (__other__change==1){
                                txt+='<span style="padding:3px;min-width:40px;" class="btn_gray nomer_com___change  span_hash" data-id="'+arr_[i]['id']+'" title="Изменить"><i class="fa fa-pencil-square-o"></i> '+arr_[i]['id']+'</span>';// Изменить
                            }
                        txt+='</div><div id="sorting_tree_btn"><i class="fa fa-sort" title="Перенести"></i></div>';
                        <?php
                            if ($inc=='s_struktura'){
                            ?>  
                            txt+='<div class="link_tree_btn"><a title="На сайт" target="_blank" class="" href="/'+arr_[i]['id']+'/"><i class="fa fa-link"></i></a></div>';
                            <?php    
                            }
                        ?>
                        txt+=txt_;
                        txt+='<div class="ttable_tbody_tr_td __other__tree__com">';
                            if (__other__add==1){
                                txt+='<span class="btn_green nomer_com___add" data-id="'+arr_[i]['id']+'" title="Добавить"> <i class="fa fa-plus"></i></span>'; //Добавить
                            }
                            if (__other__del==1){
                                txt+='<span class="btn_red nomer_com___del" data-id="'+arr_[i]['id']+'" title="Удалить"> <i class="fa fa-remove"></i></span>'; //Удалить
                            }
                            <?php
                            if ($inc=='s_struktura'){
                            ?>  
                                if(typeof arr_[i]['tip']!='undefined' && arr_[i]['tip']=='Каталог'){
                                    txt+='<a target="_blank" class="btn_gray nomer_com___s_cat" href="?inc=s_cat&com=_find&&s_struktura_id='+arr_[i]['id']+'"><i class="fa fa-th-large"></i></a>';
                                }
                                
                            <?php    
                            }
                            ?>
                        txt+='</div>';
                    txt+='</div>';
                txt+='</div>';
                txt+=create_tree(arr_[i]['id'],arr_all);
            txt+='</div></li>';
        }
    }
    if (id_==0){
        if (txt!=''){txt='<ol class="sortable ui-sortable __other__tree">'+txt+'</ol>';}
    }else{
        if (txt!=''){txt='<ol>'+txt+'</ol>';}
    }
    
    
    return txt;
}

//проверка на выделение
function chk_select()
{
    $('.th_sorting_price').detach();//цены
    var cnt_select_=$('.__other__res_table .ttable_tbody .ttable_tbody_tr.active').size()-0;
    $('.__other__res_all_rows_info_select').html('');
    if (cnt_select_>1){
        top_menu('multi_chk');
        $('.__other__res_all_rows_info_select').html('Выбрано <strong>'+cnt_select_+'</strong> строк'+end_word(cnt_select_,'','а','и'));
    }else{
        top_menu('add');
    }
    //сумма
    var pr_=$('.__other__res_table .ttable_tbody_tr.active');
    if (pr_.length>0 && $('.__other_td__price  .quick_change').length>0){
        var sum_all=0;
        pr_.each(function(){
            var sum_=($(this).find('.__other_td__price  .quick_change').html()).replace(' ','')-0;
          
            sum_all+=sum_;
        });
        
        $('.__other__th_sorting[data-col="price"]').append('<div class="th_sorting_price">'+sum_all+' руб.</div>');
    }
}

//БЫСТРОЕ ИЗМЕНЕНИЕ -> СОХРАНЕНИЕ
function quick_change(th_){
  
    var err='';
        //Добавляем коммент для мобильной версии
    var ru_='<span class="for_mobile">'+th_.closest('.ttable_tbody_tr_td').data('ru')+'</span>';
    

    if (th_.closest('.ttable_tbody_tr_td').data('col')=='name' && th_.val()==''){err+='Поле name не может быть пустым!';}

    if (err==''){
    var class_=th_.closest('.ttable_tbody_tr_td').attr('class');
        class_=class_.replace('ttable_tbody_tr_td ','');
    var data_=new Object();
    data_['_t']='quick_change';
    data_['_inс']=$('#page_res').data('inc');
    data_['_nomer']=th_.closest('.ttable_tbody_tr').data('id');
    data_['col']=th_.closest('.ttable_tbody_tr_td').data('col');

    if (class_=='__other_td__chk'){
        data_['val']='0';
        if (th_.is(':checked')==true){
            data_['val']='1';
        }
    }else if (class_=='__other_td__text' 
            || class_=='__other_td__int'
            || class_=='__other_td__float'
            || class_=='__other_td__price'
            || class_=='__other_td__data'
            || class_=='__other_td__datatime'
            || class_=='__other_td__phone'
            || class_=='__other_td__email'
            || class_=='__other_td__longtext'
    ){
        data_['val']=th_.val();
       
        if (data_['val']==''){
            th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+null_val+'</span>');
        }else{
           th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+data_['val']+'</span>'); 
        }
        
    }
    else if (class_=='__other_td__link'){
        data_['val']=th_.val();
       
        if (data_['val']==''){
            th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+null_val+'</span>');
        }else{
            var link_=data_['val'];
           th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change"><a target="_blank" href="'+link_+'">'+link_.substr(0,16)+'</a></span>'); 
        }
        
    }
    else if (class_=='__other_td__1max'){
        data_['val']=th_.val();
        var col_1max=th_.closest('.ttable_tbody_tr_td').data('col');
        var val_1max='';
        
        if ($('.__other__add').find('select[name='+col_1max+']').size()>0){//select
            $('.__other__add').find('select[name='+col_1max+'] option').each(function(){
                if ($(this).val()==data_['val']){
                    val_1max=$(this).text();
                }
            });
        }
        else{//input
            val_1max=data_['val'];
        }
        
        
        if (data_['val']==''){
            th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+null_val+'</span>');
        }else{
           th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+val_1max+'</span>'); 
        }
            
    }
    else if (class_=='__other_td__maxmax'){
        //alert(class_);
        data_['val']=th_.val();
        var new_val='';     
        th_.find('option').each(function(){
            if (in_array($(this).val(),data_['val'])){
                if (new_val!=''){new_val+=', ';}
                new_val+='<span data-id="'+$(this).val()+'">'+$(this).text()+'</span>';
            }
        });
        if (new_val==''){
            th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+null_val+'</span>');
        }else{
           th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+new_val+'</span>'); 
        }
        
    }
    else if (class_=='__other_td__enum'){
        data_['val']=th_.val();

        if (data_['val']==''){
            th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+null_val+'</span>');
        }else{
           th_.closest('.ttable_tbody_tr_td').html(ru_+'<span class="quick_change">'+data_['val']+'</span>'); 
        }
    }
    else{
        err+='Не определен class: '+class_;
    }
    

    
         // alert(err);
    if (err!=''){
        alert_m(err,'','error','none');
    }
    else{
        start_quick_change=0;//маркер быстрого изменения
        $.ajax({
        	"type": "POST",
        	"url": "ajax/__other__.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   if (is_json(data)==false){
                    alert_m(data,function(){
                        //window.location.reload(true);
                    },'error','none');
                }                
        	}
        });
    }
    }else{
        th_.closest('.ttable_tbody_tr_td').html('<span class="quick_change">'+th_.data('value')+'</span>');
        alert_m(err,'','error','none');
    }
}

//данные для фото
var photo_data;

//Определяем содержание верхнего меню (кнопки добавления или сохранения)
function top_menu(tip_){
    $('.top_com ul li, .__quick_change_maxcol').css({'display':'none'});
    if (tip_=='loading'){
        $('.top_com___loading').closest('li').css({'display':'inherit'});
    }
    else if (tip_=='loading_s'){
        $('.top_com___loading_s').closest('li').css({'display':'inherit'});
    }
    else if (tip_=='loading_f'){
        $('.top_com___loading_f').closest('li').css({'display':'inherit'});
    }
    else if (tip_=='add'){
        $('.top_com___add').closest('li').css({'display':'inherit'});
        $('.top_com___add_parsing').closest('li').css({'display':'inherit'});
        $('.top_com___add_xls').closest('li').css({'display':'inherit'});
        $('.top_com___export_csv').closest('li').css({'display':'inherit'});
        $('.__other__copypaste').closest('li').css({'display':'inherit'});
    }
    else if (tip_=='parsing'){
        $('.top_com ul li').css({'float':'inherit'});//переводим в вертикальное меню
        $('.top_com__other__start_parsing').closest('li').css({'display':'inherit'});
        $('.top_com__other__close').closest('li').css({'display':'inherit'});////
    }
    else if (tip_=='stop_parsing'){
        $('.top_com ul li').css({'float':'inherit'});//переводим в вертикальное меню
        $('.top_com___loading').closest('li').css({'display':'inherit'});
        $('.top_com__other__stop_parsing').closest('li').css({'display':'inherit'});
        $('.top_com__other__close').closest('li').css({'display':'inherit'});////
    }
    else if (tip_=='export_csv'){
        $('.top_com ul li').css({'float':'inherit'});//переводим в вертикальное меню
        $('.top_com__other__start_export_csv').closest('li').css({'display':'inherit'});
        $('.top_com__other__close').closest('li').css({'display':'inherit'});////
    }
    else if (tip_=='savechange'){
        $('.top_com ul li').css({'float':'inherit'});//переводим в вертикальное меню
        $('.modal_hide, .modal_add').addClass('modal_change').removeClass('modal_hide').removeClass('modal_add');
        $('.__other__add__saveadd').closest('li').css({'display':'inherit'});
        $('.__other__add__saveclose').closest('li').css({'display':'inherit'});
        $('.__other__add__save').closest('li').css({'display':'inherit'});////
        $('.top_com__other__close').closest('li').css({'display':'inherit'});////
    }
    else if (tip_=='saveadd'){
        $('.top_com ul li').css({'float':'inherit'});//переводим в вертикальное меню
        $('.modal_hide, .modal_change').addClass('modal_add').removeClass('modal_hide').removeClass('modal_change');
        $('.__other__add__saveadd').closest('li').css({'display':'inherit'});
        $('.__other__add__saveclose').closest('li').css({'display':'inherit'});
        $('.__other__add__save').closest('li').css({'display':'inherit'});////
        $('.top_com__other__close').closest('li').css({'display':'inherit'});////
    }
    else if (tip_=='multi_chk'){
        $('.top_com___add').closest('li').css({'display':'inherit'});
        $('.__other__delete, .__other__noactive, .__other__active, .__other__add_to_zakaz, .__other__add_to_postav').closest('li').css({'display':'inherit'});
        $('.top_com___export_csv, .__other__copypaste').closest('li').css({'display':'inherit'});
        $('.__quick_change_maxcol').css({'display':'inherit'});
    }
}

function update_fillter(){
    $('.__other__fillter_chk').select2({'width':'100%',allowClear: true,closeOnSelect:false}).change(function(){find();}); //chk
    $('.__other__fillter_max').each(function(){
        var th_=$(this);
        var _col_id=th_.data('col_id');
        th_.select2({'width':'100%',allowClear: true,closeOnSelect:false,
                    ajax: {
                        url: "ajax/__other__.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, 
                                page: params.page,
                                _t:'autocomplete_max_max',
                                _col_id:_col_id
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
                    
                }).change(function(){find();});
    });

    $('.__other__fillter_enum option').removeAttr('selected');
    $('.__other__fillter_enum').select2({'width':'100%',allowClear: true}).change(function(){find();});
    $('select.__other__fillter_1max').select2({'width':'100%',allowClear: true}).change(function(){find();});
    
    $('.__other__fillter_photo').select2({'width':'100%',allowClear: true}).change(function(){find();}).on("select2:select", function (e) {$(this).select2('open');})
              .on("select2:unselect", function (e) {$(this).select2('open');});
    $('.__other__fillter_data1, .__other__fillter_data2').datepicker();
    $('.__other__fillter_datatime1, .__other__fillter_datatime2').datetimepicker({onClose: function(current_time,$input){
        find();
    }});
    
    $('.__other__fillter_price1, .__other__fillter_price2').integer_();//цена
    
    $('.__other__fillter_div input').keyup(function(e){if(e.which==13){find();}});
    
    $('.zoom').fancybox({prevEffect:'none',nextEffect:'none',helpers:{title:{type:'outside'},thumbs:{width:50,height:50}}}); //фото
    
    //Скрипт для загрузки фоток для товара
    var upload_photo = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : '__other__head_photo_load',
        drop_element : '__other__head_photo_load',
        url : 'ajax/__other__.php',
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
                   up.setOption('multipart_params', {'_t' : 'upload_items','inc':$('#page_res').data('inc')});
                }
            },
            init : {
                QueueChanged: function(up) {upload_photo.start();},
        		BeforeUpload: function(up, file) {$('.__other__head_photo_load').hide();$('.loading_img_span').detach();$('.ttable_thead_tr_th[data-tip="Фото"]').append('<div class="loading_img_span"><div class="cssload-container"><div class="cssload-loading"><i></i><i></i><i></i><i></i></div></div> загрузка </div>');},
                FileUploaded: function(up, file, info) {
                    $('.__other__head_photo_load').show();
                    $('.loading_img_span').detach();
                    find();
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
    
        //Выбор условия or или and для max-max
        $('._other_f__maxmax_opt1').select2({'width':'100%'}).change(function(){
            find();
        });
}


// поиск
function find(tip,function_res){
    tip = tip || '';
    function_res = function_res || '';
    
    var data_=$('.__other__res_form').serializeObject(); //фильтр
    //сортировка
    data_['s__minmax_col']=sel_in_array($('.__other__res_table .ttable_thead .ttable_thead_tr .ttable_thead_tr_th .__other__sort_up.active').closest('.ttable_thead_tr_th'),'data','col');
    data_['s__maxmin_col']=sel_in_array($('.__other__res_table .ttable_thead .ttable_thead_tr .ttable_thead_tr_th .__other__sort_down.active').closest('.ttable_thead_tr_th'),'data','col');
    data_['_t']='find';
    data_['_inc']=$('#page_res').data('inc');
    if (typeof $('.__other__res_loading_row_20_inp').val() !='undefined'){
        loading_rows=$('.__other__res_loading_row_20_inp').val();
    }
    
    if (tip=='reload_all'){ //перезагрузка при сохранении - изменение
        data_['LIMIT']=$('.page_res .__other__res_table>.ttable_tbody>.ttable_tbody_tr').size()-0;
        tip='';
    }
    if (tip=='reload_all_new'){ //перезагрузка при сохранении - добавление
        data_['LIMIT']=$('.page_res .__other__res_table>.ttable_tbody>.ttable_tbody_tr').size()-0+1;
        tip='';
    }
    if (tip=='loadind'){ //догрузка
        data_['LIMIT']=$('.page_res .__other__res_table>.ttable_tbody>.ttable_tbody_tr').size()+', '+loading_rows;
    }
    if (tip=='loadind_all'){ // подгрузка всего товара
        data_['LIMIT']=$('.page_res .__other__res_table>.ttable_tbody>.ttable_tbody_tr').size()+', '+$('.__other__res_loading_row_all span').text();
        tip='loadind';
    }
    
    //Формирование ссылки HISTORY
    history_txt='';
    for(var name_ in data_){
        if (name_.substr(0, 3)=='f__' || name_.substr(0, 11)=='__function_'){
            var n_=name_.replace("f__", "");
            var n_=n_.replace("__function_", "");
            if (typeof data_[name_]=='object'){
                var history_max='';
                var kol=0;
                for (var key_ in data_[name_]){
                    if (data_[name_][key_]!=''){kol++;}
                    history_max+='&'+n_+'[]='+data_[name_][key_];
                }
                if (kol>0){
                    history_txt+=history_max;
                }
            }
            else{
                if(data_[name_]!=''){history_txt+='&'+n_+'='+data_[name_];}
            }
        }
    }
    
    if (history_txt!=''){
        history_name=($('h1.page_name').text()).trim()+". Поиск";
        history_url='?inc='+$('#page_res').data('inc');+'&com=_find&'+history_txt;
    }else{
        history_name=($('h1.page_name').text()).trim();
        history_url='?inc='+$('#page_res').data('inc');;
    }
    History.replaceState({state:3}, history_name, history_url);
    history.pushState({param: history_name}, '', history_url);
    //end Формирование ссылки HISTORY
    
    
    top_menu('loading_f');
    if (typeof jqxhr!='undefined'){jqxhr.abort();}
    jqxhr = $.ajax({
    	"type": "POST",
    	"url": "ajax/__other__.php",
    	"dataType": "text",
    	"data":data_,
    	"success":function(data,textStatus){
            
            if (is_json(data)==true){
                data_n=JSON.parse(data);
                
                if (typeof data_n.error!='undefined'){
                    var txt_err='';
                    for (var i in data_n.error){
                        txt_err+=data_n.error[i];
                    }
                    if (txt_err!=''){
                        alert_m(txt_err,'','error','none');
                    }
                }
                
                var txt='';
                var fillter='';
                $('.__other__res_all_rows_info').detach();
                $('.__other__res_loading_row_tr').html('');
                
                if (data_n.t=='table'){ // ТАБЛИЦА
                    var arr_col_ru=data_n.col_ru;
                    var arr_col=data_n.col;
                    var arr_chk_change=data_n.chk_change_;
                    
                    var arr_tip=data_n.tip;
                    var txt_footer='';
                    if (count(arr_col_ru)>0){
                     
                        if (tip=='start'){
                            var sort_block='<span class="ico ico_down_gray __other__sort_down"></span><span class="ico ico_up_gray __other__sort_up"></span>';
                                    
                            /////////////////////////////////////////// ШАПКА - ТАБЛИЦА  ***********************************************
                            txt+='<form class="__other__res_form">'
                                +'<div class="ttable __other__res_table">'
                                +'<div class="ttable_thead">'
                                +'<div class="ttable_thead_tr">';
                            txt+='<div class="ttable_thead_tr_th __other__th_id1" data-tip="Целое число" data-col="id">';
                           
                            txt+='<span class="btn_gray __other__check_all">Выделить</span><span class="btn_gray __other__check_all_close">Отменить</span> <i class="fa fa-eye-slash" data-inc="<?=$inc;?>" title="Отображать столбцы"></i>'+sort_block;
                            
                            txt+='</div>';
                            
                            for(var col_id in arr_col_ru){
                                
                                //Быстрое изменение
                                var quick_change_max='';
                                if (__other__change==1){
                                    quick_change_max='<span title="Изменить" style="display:none;" class="ico ico_edit __quick_change_maxcol"></span>';
                                }
                                    
                                if (arr_tip[col_id]=='Фото'){
                                    txt+='<div class="ttable_thead_tr_th" data-tip="'+arr_tip[col_id]+'" data-col="'+arr_col[col_id]+'"><i class="fa fa-close" title="Скрыть столбец"></i> <span>'+arr_col_ru[col_id]+'</span><div id="__other__head_photo_load" class="btn_gray __other__head_photo_load">Загрузить</div></div>';
                                }
                                else if (arr_tip[col_id]=='Цвет'){
                                    txt+='<div class="ttable_thead_tr_th" data-tip="'+arr_tip[col_id]+'" data-col="'+arr_col[col_id]+'"><i class="fa fa-close" title="Скрыть столбец"></i> <span>'+arr_col_ru[col_id]+'</span></div>';
                                }
                                else if(arr_tip[col_id]=='Связанная таблица max-max'){
                                    txt+='<div class="ttable_thead_tr_th __other__th_sorting" data-tip="'+arr_tip[col_id]+'" data-col="'+arr_col[col_id]+'"><i class="fa fa-close" title="Скрыть столбец"></i> '+quick_change_max+sort_block+'<span>'+arr_col_ru[col_id]+'</span><div class="__other__th_sorting_maxmax_andor"><select class="_other_f__maxmax_opt1" name="f__'+arr_col[col_id]+'_opt1"><option value="or">ИЛИ</option><option value="and">И</option></select></div></div>';
                                }
                                else if(arr_tip[col_id]=='Функция'){
                                    txt+='<div class="ttable_thead_tr_th __other__th_sorting" data-tip="'+arr_tip[col_id]+'" data-col="'+arr_col[col_id]+'"><i class="fa fa-close" title="Скрыть столбец"></i> '+quick_change_max+'<span>'+arr_col_ru[col_id]+'</span></div>';
                                }
                                else{
                                    txt+='<div class="ttable_thead_tr_th __other__th_sorting" data-tip="'+arr_tip[col_id]+'" data-col="'+arr_col[col_id]+'"><i class="fa fa-close" title="Скрыть столбец"></i> '+quick_change_max+sort_block+'<span>'+arr_col_ru[col_id]+'</span></div>';
                                }
                            }
                            txt+='</div><div class="ttable_thead_tr">';
                            
                            txt+='<div class="__other__th_id2 ttable_thead_tr_th">';
                                txt+='<div class="__other__fillter_div"><input type="text" name="f__id" class="__other__fillter_id" placeholder="id" value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></div>';
                            txt+='</div>';
                    
                            for(var col_id in arr_col_ru){
                                //фильтр
                                txt+='<div class="ttable_thead_tr_th" data-col="'+arr_col[col_id]+'">';
                                if (arr_tip[col_id]=='Текст'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+quick_change_max+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_text" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                }
                                else if(arr_tip[col_id]=='Длинный текст'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_text" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                }
                                else if(arr_tip[col_id]=='HTML-код'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_text" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                
                                }
                                else if(arr_tip[col_id]=='Целое число'){
                                    txt+='<div class="__other__fillter_div"><div class="__other__fillter_int"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_int1" placeholder="от" value="" />-<input type="text" class="__other__fillter_int2" placeholder="до" value="" name="f__'+arr_col[col_id]+'" /></div></span></div>';
                                
                                }
                                else if(arr_tip[col_id]=='Дробное число'){
                                    txt+='<div class="__other__fillter_div"><div class="__other__fillter_float"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_int1" placeholder="от" value="" />-<input type="text" class="__other__fillter_int2" placeholder="до" value="" name="f__'+arr_col[col_id]+'" /></div></span></div>';
                                
                                }
                                else if(arr_tip[col_id]=='Стоимость'){
                                    txt+='<div class="__other__fillter_div"><div class="__other__fillter_price"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input name="f__'+arr_col[col_id]+'" type="text" class="__other__fillter_price1" placeholder="от" value="" />-<input name="f__'+arr_col[col_id]+'" type="text" class="__other__fillter_price2" placeholder="до" value="" /></div></span></div>';
                                
                                }
                                else if(arr_tip[col_id]=='Дата'){
                                    txt+='<div class="__other__fillter_div"><div class="__other__fillter_data"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input name="f__'+arr_col[col_id]+'" type="text" class="__other__fillter_data1" placeholder="от" value="" />-<input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_data2" placeholder="до" value="" /></div></span></div>';
                                }
                                else if(arr_tip[col_id]=='Дата-время'){
                                    txt+='<div class="__other__fillter_div"><div class="__other__fillter_datatime"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input name="f__'+arr_col[col_id]+'" type="text" class="__other__fillter_datatime1" placeholder="от" value="" />-<input name="f__'+arr_col[col_id]+'" type="text" class="__other__fillter_datatime2" placeholder="до" value="" /></div></span></div>';
                                
                                }
                                else if(arr_tip[col_id]=='Телефон'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_tel" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                }
                                else if(arr_tip[col_id]=='Email'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_email" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                }
                                else if(arr_tip[col_id]=='Связанная таблица 1-max'){
                                    if ($('.preload .__other__add select[name='+arr_col[col_id]+']').size()>0){
                                        var txt_1max='<select name="f__'+arr_col[col_id]+'" data-placeholder="'+arr_col_ru[col_id]+'" class="__other__fillter_1max"><option></option>'+$('.preload .__other__add select[name='+arr_col[col_id]+']').html()+'</select>';
                                    }else{
                                        txt_1max='<input type="text" placeholder="'+arr_col_ru[col_id]+'" class="__other__fillter_1max" name="f__'+arr_col[col_id]+'" />';
                                    }
                                    
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val">'+txt_1max+'</span></div>';
                                }
                                else if(arr_tip[col_id]=='Связанная таблица max-max'){
                                    var val_max='';
                                    var col_id_=0;
                                    if ($('select#'+arr_col[col_id]+'').size()>0){
                                        val_max=$('select#'+arr_col[col_id]).html();
                                        col_id_=$('select#'+arr_col[col_id]).closest('.ttable_tbody_tr').data('col_id');
                                    }else{
                                        if ($('div#'+arr_col[col_id]).size()>0){
                                            col_id_=$('div#'+arr_col[col_id]).closest('.ttable_tbody_tr').data('col_id');
                                            $('div#'+arr_col[col_id]).find('ul li').each(function(){
                                                val_max+='<option value="'+$(this).data('id')+'">'+$(this).find('>div>span').text()+'</option>';
                                            });
                                        }
                                    }
                                  
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><select name="f__'+arr_col[col_id]+'" data-col_id="'+col_id_+'" data-placeholder="'+arr_col_ru[col_id]+'" class="__other__fillter_max" multiple>'+val_max+'</select></span></div>';
                                }
                                else if(arr_tip[col_id]=='Функция'){
                                    txt+=all_functions(arr_col[col_id],'fillter');
                                }
                                else if(arr_tip[col_id]=='chk'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><select name="f__'+arr_col[col_id]+'" data-placeholder="'+arr_col_ru[col_id]+'" class="__other__fillter_chk"><option></option><option value="1">Вкл.</option><option value="0">Откл.</option></select></span></div>';
                                }
                                else if(arr_tip[col_id]=='enum'){
                                   txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><select name="f__'+arr_col[col_id]+'" data-placeholder="'+arr_col_ru[col_id]+'" class="__other__fillter_enum"><option></option>'+$('.preload .__other__add select[name='+arr_col[col_id]+']').html()+'</select></span></div>';
                                }
                                else if(arr_tip[col_id]=='Цвет'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_color" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                }
                                else if(arr_tip[col_id]=='Фото'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><select name="f__'+arr_col[col_id]+'" data-placeholder="'+arr_col_ru[col_id]+'" class="__other__fillter_photo"><option></option><option value="1">С фото</option><option value="0">Без фото</option></select></span></div>';
                                }
                                else if(arr_tip[col_id]=='Ссылка'){
                                    txt+='<div class="__other__fillter_div"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input type="text" name="f__'+arr_col[col_id]+'" class="__other__fillter_link" placeholder="Поиск..." value="" /><span class="ico ico_search"></span><div style="clear:both;"></div></span></div>';
                                }
                            txt+='</div>';
                            }
                            txt+='</div></div>';
                            txt+='<div class="ttable_tbody">';
                        }
                        
                        
                        
                        /////////////////////////////////////////// ОСНОВНАЯ ЧАСТЬ - ТАБЛИЦА ***********************************************
                        var data_arr=data_n._d;
                        if (count(data_arr)>0){
                            
                            for (var key_ in data_arr){
                                
                                var id_=data_arr[key_]['id'];
                                txt+='<div class="ttable_tbody_tr" data-id="'+id_+'">';
                                
                                txt+='<div class="ttable_tbody_tr_td __other_td__com">';
                                txt+='<div><span class="for_mobile">№</span><span class="val">';
                                <?php
                                    if ($inc=='s_cat'){
                                    ?>  
                                    txt+='<a target="_blank" title="На сайт" class="s_cat_number_link" href="/'+id_+'"><i class="fa fa-link"></i> '+gap(id_)+'</a>';
                                
                                <?php
                                    }else{
                                        ?>
                                        txt+=gap(id_);
                                        <?php
                                    }
                                    ?>
                                txt+='</span></div><div class="clear"></div>';
                                if (__other__change==1){
                                    txt+='<div class="nomer_com___change_div"><span data-id="'+id_+'" class="btn_gray nomer_com___change" title="Изменить"> <i class="fa fa-pencil-square-o"></i></span></div>';//Изменить
                                }
                                if (__other__del==1){
                                    txt+='<div class="nomer_com___del_div"><span data-id="'+id_+'" class="btn_red nomer_com___del" title="Удалить"> <i class="fa fa-remove"></i></span></div>';//Удалить
                                }
                                
                                txt+='</div>';
                                var val_arr=data_arr[key_];

                                for(var col_id in arr_col){ //перебор по столбцам
                                    
                                    //if (val_arr[arr_col[col_id]]==''){val_arr[arr_col[col_id]]=null_val;}
                                    
                                    var cl_quick_change=' class="val"';if (arr_chk_change[col_id]=='1' && __other__change=='1'){ cl_quick_change=' class="val quick_change"';}
                                    
                                    if (arr_tip[col_id]=='Текст'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__text" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                        
                                    }
                                    else if(arr_tip[col_id]=='Длинный текст'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__longtext" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='HTML-код'){
                                        var val_=val_arr[arr_col[col_id]];
                                        var val_min='<span>'+val_+'</span>';
                                        if (val_.length>150){
                                            val_min='<span class="view_longtext">'+val_.substr(0, 150)+'<span style="display:none;">'+val_.substr(150, (val_.length)-150)+'</span></span>';
                                       }
                                        txt+='<div class="ttable_tbody_tr_td __other_td__htmlcode" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val">'+val_min+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Целое число'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__int" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Дробное число'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__float" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Стоимость'){
                                        var price_=number_format(val_arr[arr_col[col_id]],0);
                                        txt+='<div class="ttable_tbody_tr_td __other_td__price" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+price_+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Дата'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__data" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Дата-время'){
                                        var val_=(val_arr[arr_col[col_id]]).split(' ');
                                        txt+='<div class="ttable_tbody_tr_td __other_td__datatime" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'><p>'+val_[0]+'</p><p>'+val_[1]+'</p></span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Телефон'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__phone" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Email'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__email" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Связанная таблица 1-max'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__1max" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Связанная таблица max-max'){
                                       var text_='';//implode(', ',val_arr[arr_col[col_id]]);
                                        if (typeof val_arr[arr_col[col_id]]=='object'){
                                            for (var kkk in val_arr[arr_col[col_id]]){
                                                if (text_!=''){text_+=', ';}
                                                text_+='<span data-id="'+val_arr[arr_col[col_id]][kkk]+'">'+val_arr[arr_col[col_id]+'_'][kkk]+'</span>';
                                            }
                                        }
                                        
                                        txt+='<div class="ttable_tbody_tr_td __other_td__maxmax" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+text_+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Функция'){
                                       
                                        var result_=all_functions(arr_col[col_id],'find',val_arr[arr_col[col_id]]);
                                        if (typeof result_=='undefined'){result_='';}
                                        txt+='<div class="ttable_tbody_tr_td __other_td__funct __other_td__funct_'+arr_col[col_id]+'" data-col="'+arr_col[col_id]+'" data-ru="'+arr_col_ru[col_id]+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+'>'+result_+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='chk'){
                                        var dis_='';if (cl_quick_change==''){dis_=' disabled="disabled"';}
                                        var chk_='';if (val_arr[arr_col[col_id]]=='1'){chk_=' checked="checked"';}
                                        
                                        txt+='<div class="ttable_tbody_tr_td __other_td__chk" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><center><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val"><input'+cl_quick_change+dis_+' type="checkbox" value="1" '+chk_+'></span></center></div>';
                                    }
                                    else if(arr_tip[col_id]=='enum'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__enum" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Цвет'){
                                        txt+='<div class="ttable_tbody_tr_td __other_td__color" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'>'+val_arr[arr_col[col_id]]+'</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Фото'){
                                        
                                        txt+='<div class="ttable_tbody_tr_td __other_td__photo" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span class="val">';
                                        
                                        var result_=val_arr['photo_img'];
                                        var result_tip=val_arr['photo_tip'];
                                        var kol_photo=0;
                                        for (var key_ in result_){
                                            if (kol_photo==0){
                                                kol_photo++;
                                                txt+='<a rel="a_photo_'+id_+'" class="zoom" href="../i/'+$('#page_res').data('inc')+'/original/'+result_[key_]+'"><img src="../i/'+data_['_inc']+'/small/'+result_[key_]+'" border="0" /></a>';
                                            }else{
                                                txt+='<a rel="a_photo_'+id_+'" class="zoom" href="../i/'+$('#page_res').data('inc')+'/original/'+result_[key_]+'"></a>';
                                            }
                                        }
                                        
                                        txt+='</span></div>';
                                    }
                                    else if(arr_tip[col_id]=='Ссылка'){
                                        var link_=val_arr[arr_col[col_id]];
                                        cl_quick_change='';
                                        txt+='<div class="ttable_tbody_tr_td __other_td__link" data-col="'+arr_col[col_id]+'" data-ru="'+_IN(arr_col_ru[col_id])+'"><span class="for_mobile">'+arr_col_ru[col_id]+'</span><span'+cl_quick_change+'><a target="_blank" href="'+val_arr[arr_col[col_id]]+'">'+link_.substr(0,16)+'...</a></span></div>';
                                    }
                                    else{
                                        txt+='<div class="ttable_tbody_tr_td">Не определен тип данных = '+arr_tip[col_id]+'</div>';
                                    }
                                }
                                
                                txt+='</div>';
                            }//end for
                            
                            
                            //Определяем количество записей
                           
                            
                            var cnt_all=data_n._cnt-0;//все количество
                                if (tip=='loadind'){
                                    cnt_cur=$('.page_res .__other__res_table>.ttable_tbody>.ttable_tbody_tr').size()-0+count(data_arr);
                                }else{
                                    cnt_cur=count(data_arr);
                                }
                                
                            
                            if (cnt_all>cnt_cur){
                                var txt_cnt=cnt_all-cnt_cur;
                                var colspan_=count(arr_col_ru);
                                
                                txt_cnt2=txt_cnt;
                                if (txt_cnt>loading_rows){txt_cnt2=loading_rows;}
                               
                                txt_footer=''
                                +'<div class="ttable2_tbody_tr">'
                                    +'<div class="ttable2_tbody_tr_td">'
                                        +'<div class="__other__res_loading_row_20">Загрузить еще <input class="__other__res_loading_row_20_inp" name="__other__res_loading_row_20_inp'+Math.floor(Math.random() * 10)+'" value="'+txt_cnt2+'">' +' строк'+end_word(txt_cnt2,'','у','и')+' </div>'
                                    +'</div>'
                                    +'<div class="ttable2_tbody_tr_td">'
                                        +'<div class="__other__res_loading_row_all">Загрузить ВСЁ (<span>'+txt_cnt +'</span>)</div>'
                                    +'</div>';
                                txt_footer+='</div>';
                            }
                            
                        }else{
                            var colspan_=count(arr_col_ru)+1;
                            txt_footer+='<div class="ttable2_tbody_tr"><div class="ttable2_tbody_tr_td">Данных не найдено!</div></div>';
                            txt+='</div></div>';
                        }
                       
                        if (tip=='start'){
                            txt+='</div>';
                            txt+='</div></form>';
                            $('.page_res').html(txt);
                            update_fillter();
                        }
                        else if (tip=='loadind'){
                            $('.page_res .__other__res_table>.ttable_tbody').append(txt);
                        }
                        else{ //шапка прогруженна
                            $('.page_res .__other__res_table>.ttable_tbody').html(txt);
                            
                            
                            
                            
                        }
                        
                            $('.__other__res_loading_row_tr').html(txt_footer);
                            
                            if (typeof cnt_cur == 'undefined'){cnt_cur='0';}if (typeof cnt_all == 'undefined'){cnt_all='0';}
                        $('.page_res .__other__res_table>.ttable_tbody .ttable_tbody_tr_td span.quick_change:empty').html(null_val);//добавляем +
                        if (typeof cnt_cur=='undefined'){var cnt_cur=0;}
                        
                        $('.page_res').prepend('<p class="__other__res_all_rows_info">Загружено <strong>'+cnt_cur+'</strong> запис'+end_word(cnt_cur,'ей','ь','и')+' из <strong>'+cnt_all+'</strong>. <span class="__other__res_all_rows_info_select"></span></p>');
                        
                    }else{
                        $('.page_res').html('<p>Нет столбцов для вывода!</p>');
                    }
                    
                    //alert($(window).width()+' - '+$('.page_res').width()+' - '+$(document).width());
                }
                else if(data_n.t=='tree'){ // ДЕРЕВО
                
                    
                
                    var txt='';
                    var arr_col_ru=data_n.col_ru;
                    var arr_col=data_n.col;
                    var arr_chk_change=data_n.chk_change_;
                    
                    var arr_tip=data_n.tip;
                    /////////////////////////////////////////// ОСНОВНАЯ ЧАСТЬ - ДЕРЕВО ***********************************************
                    
                    
                    if (count(data_n)>0){
                        var txt=create_tree(0,data_n);
                    }else{
                        txt+='<p>Данных не найдено!</p>';
                    }

                    txt='<div class="fillter_tree">'
                        +'<span onclick="open_tree();" class="btn_gray">Открыть дерево</span>'
                        +'<span onclick="close_tree();" class="btn_gray">Закрыть дерево</span>'
                        
                        +'</div>'
                        +txt;
                    
                    
                    $('.page_res').html(txt);
                    $('.page_res .__other__tree .ttable .ttable_tbody_tr_td span.quick_change:empty').html(null_val);//добавляем +
                        
                    //свертываие дерева
                  
                    if (tree=='close'){
                        close_tree();
                    }else{
                        open_tree();
                    }
                    sorting_tree_on();
                    $('.zoom').fancybox();
                    
                }
                
                
                
            }else{
                alert_m(data,'','error','none');
            }
            
            if (typeof function_res == 'function') {function_res();}
            else{chk_select();}
                        
                        
    	}
    });
}
//Сворачиваем дерево max-max при открытии окна изменения записи
function _other_form_close_tree(th_){
    var mm=0;
    th_.find('>ul').find('li').each(function(){
        if ($(this).find('ul').length>0){ 
            mm=1;
            if ( $(this).find('ul').find('.active').length>0){
                $(this).prepend('<i class="fa fa-minus" title="Свернуть"></i>');
                $(this).find('ul').show();
            }else{
                $(this).prepend('<i class="fa fa-plus" title="Развернуть"></i>');
                $(this).find('ul').hide();
            }
           
            
        }
    });
    if (mm==1){
        th_.closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:first').append('<div class="connect_max_max_open_close_all_tree"><span class="btn_gray connect_max_max_open_all_tree"  onclick="open_tree();"><i class="fa fa-plus"></i> Развернуть все</span><span class="btn_gray connect_max_max_close_all_tree"  onclick="close_tree();"><i class="fa fa-minus"></i> Свернуть все</span></div>');
    }
}


// ************************************************************************************************************
// ************************************************************************************************************
// ************************************************************************************************************
$(document).ready(function(){
    
    //разворачиваем дерево max-max при открытии окна изменения записи
    $(document).delegate('.connect_max_max .fa-plus','click',function(){
        $(this).closest('li').find('>ul').show();
        $(this).addClass('fa-minus').removeClass('fa-plus').attr('title','Развернуть');;
    });
    //сворачиваем дерево max-max при открытии окна изменения записи
    $(document).delegate('.connect_max_max .fa-minus','click',function(){
        $(this).closest('li').find('>ul').hide();
        $(this).addClass('fa-plus').removeClass('fa-minus').attr('title','Свернуть');
    });
    //свернуть вссе дерево  при открытии окна изменения записи
    $(document).delegate('.connect_max_max_close_all_tree','click',function(){
        $(this).closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:last').find('.fa-minus').each(function(){
            $(this).click();
        });
    });
    //разернуть вссе дерево  при открытии окна изменения записи
    $(document).delegate('.connect_max_max_open_all_tree','click',function(){
        $(this).closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:last').find('.fa-plus').each(function(){
            $(this).click();
        });
    });
    
    
    $(document).delegate('.ico_search','click',function(){
        find();
    });
    
    all_functions('','start');
    
    //сортировка
    $(document).delegate('.__other__sort_down, .__other__sort_up','click',function(){
        
        var cl_=$(this).attr('class');
        $(this).closest('.ttable_thead_tr_th').find('.__other__sort_down, .__other__sort_up').removeClass('active');
        
        if ((cl_.split('active').length - 1)>0){
            $(this).removeClass('active');
        }else{
            $(this).addClass('active');
        }
        find();
    });
    
    // цена
    $(document).delegate('.__other__fillter_price1, .__other__fillter_price2','keyup',function(){
        $(this).val(gap($(this).val()));
    });
    
    photo_data='<div class="photo_del"><span class="ico ico_del"></span></div>';
    photo_data+='<div class="photo_data"><input type="text" name="photo_desc" placeholder="Описание" /></div>';
    photo_data+='<div class="photo_tip"><select name="photo_tip" data-placeholder="Тип">'+$('.photo_tip_ex').html()+'</select></div>';
    

    //Подгрузка новых значений
    $(document).delegate('.__other__res_loading_row_20','click',function(e){
        if (e.target.nodeName!='INPUT'){
            find('loadind');
        }
        
    });
    
    //Подгрузка всех значений
    $(document).delegate('.__other__res_loading_row_all','click',function(){
        find('loadind_all');
    });
    
    //Выбор при max=max
    $(document).delegate('form.__other__add_form .connect_max_max ul li>div','click',function(){
        var cl_=$(this).attr('class');
        if (cl_=='active'){
            $(this).removeClass('active');
        }else{
            $(this).addClass('active');
        }
    });
    
    //щелчек по строке для фокуса при добавлении
   
    $(document).delegate('.__other__add_form .ttable .ttable_tbody_tr .ttable_tbody_tr_td:last-child','click',function(e){
        var th_=$(this).closest('.ttable_tbody_tr');
        var tip=th_.data('tip');
       
        if (typeof th_.data('col') !='undefined'){
          
        if (tip!='Фото' && tip!='Функция' && tip!='Связанная таблица max-max'){
         
            th_.find('input[type=text], select').trigger('focus');
            
            if (e.target.nodeName!='INPUT'){
                if (th_.find('input[type=checkbox]').is(':checked')){
                    th_.find('input[type=checkbox]').removeAttr('checked');
                }
                else{
                    th_.find('input[type=checkbox]').prop('checked','checked');
                }
            }
               
        }
        }
    }); 
    
    
    //удаляем фото
    $(document).delegate('.__other__add_form .photo_del','click',function(){
        var th_=$(this);
        
        var data_=new Object();
        data_['_t']='delete_photo';
        data_['img']=th_.closest('.photo_res__item').data('img');
        data_['_inc']=$('#page_res').data('inc');
        th_.closest('.photo_res__item').detach();

    });
    


function nomer_com___change(inc_,col_,nomer_,callback){
    callback=callback || '';
    //получение данных
    var editor_change=1;
    var data_=new Object();
    data_['_t']='change';
    data_['_inc']=inc_;
    data_['_col']=col_;
    data_['_nomer']=nomer_;
        
    $.ajax({
            	"type": "POST",
            	"url": "ajax/__other__.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
                    if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        
                        //последняя страница
                        if (data_['_nomer']!=''){
                            $('.__other__open_lastpage').html('(<a href="?inc='+data_['_inc']+'&com=_change&nomer='+data_['_nomer']+'">Последняя страница</a>)');
                        }
                            
                            //обработка столбцов
                            for (var i in data_n.col){
                                var col_=data_n.col[i];
                                var tip_=data_n.tip[i];
                                var chk_change_=data_n.chk_change[i];
                                var val_=data_n._d[col_];
                                    
                                    
                                    if (chk_change_=='1'){
                                         $('.__other__add_form>.ttable>.ttable_tbody_tr[data-col='+col_+']').css({'display':'inherit'});//открываем разрешенные для редактирования столбцы
                                    }
                                    //**************
                                    else{//удаляем запрещенные
                                        $('.__other__add_form>.ttable>.ttable_tbody_tr[data-col='+col_+']').detach();
                                    }
                                    //****************
                                    if (tip_=='Текст'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_);
                                        
                                    }
                                    else if(tip_=='Длинный текст'){
                                        $('.__other__add_form').find('textarea[name='+col_+']').val(val_);
                                    }
                                    else if(tip_=='HTML-код'){
                                        if ($('.__other__add_form').find('#editor'+editor_change+'[name='+col_+']').size()==0){
                                            $('.__other__add_form').find('textarea[name='+col_+']').val(val_).attr('id','editor'+editor_change);
                                            
                                            var edit_=CKEDITOR.replace('editor'+editor_change,{
                                                        allowedContent:true,
                                                        contentsCss: 'js/ckeditor/contents.css',
                                                        toolbar: [
                                                        { name: 'document', groups: [ 'mode', 'document' ], items: [ 'Source','-', 'Templates' ] },
                                                        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                                                        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
                                                        { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                                                        { name: 'insert', items: [ 'Image',  'Table', 'Iframe','HorizontalRule' ] },
                                                        '/',
                                                        { name: 'styles', items: [ 'Styles', 'Format',  'FontSize' ] },
                                                        { name: 'colors', items: [ 'TextColor' ] },
                                                        { name: 'tools', items: [ 'Maximize' ] },
                                                        
                                                    ]
                                            });
                                           AjexFileManager.init({returnTo: 'ckeditor', editor: edit_});
                                        }else{
                                           CKEDITOR.instances['editor'+editor_change].setData(val_);
                                        }
                                        editor_change++;
                                    
                                    }
                                    else if(tip_=='Целое число'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_).spinner().integer_(); //Целое число;
                                    }
                                    else if(tip_=='Дробное число'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_).spinner({numberFormat: "n"}).float_();//Дробное число
                                    }
                                    else if(tip_=='Стоимость'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(number_format(val_,2)).autoNumeric('init');
                                    }
                                    else if(tip_=='Дата'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_).datetimepicker({lang:'ru',timepicker:false, mask:true,format:'d.m.Y',closeOnDateSelect:true});
                                    }
                                    else if(tip_=='Дата-время'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_).datetimepicker({lang:'ru', mask:true,format:'d.m.Y H:i:s'});
                                    }
                                    else if(tip_=='Телефон'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_);
                                    }
                                    else if(tip_=='Email'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_);
                                    }
                                    else if(tip_=='Связанная таблица 1-max'){
                                        if ($('.__other__add_form .connect_1_max_sel[name='+col_+']').size()>0){//select
                                            $('.__other__add_form').find('select[name='+col_+'] option[value='+val_+']').attr('selected','selected');
                                            $('.__other__add_form').find('select[name='+col_+']').select2({'width':'100%'}).on("select2:select", function (e) {$(this).select2('open');})
                                                .on("select2:unselect", function (e) {$(this).select2('open');});
                                        }else{//auto-add -> connect_1_max
                                            $('.__other__add_form').find('input[name='+col_+']').val(val_);
                                            
                                             $(".__other__add_form .connect_1_max").autocomplete({
                                                appendTo: ".__other__add_form",
                                                minLength: 0
                                              }).focus(function() {
                                                   var th_=$(this);
                                                   var str=th_.closest('.ttable_tbody_tr_td').find('.connect_1_max_source').html();
                                                        var col_id=th_.data('col_id');// имя столбца заполнения
                                                         th_.autocomplete("option", "source",function(request, response){
                                                             request['_t']='autocomplete_1_max';
                                                             request['_col_id']=col_id;
                                                             
                                                             if (typeof jqxhr!='undefined'){jqxhr.abort();}
                                                             jqxhr = $.ajax({
                                                            	"type": "POST",
                                                            	"url": "ajax/__other__.php",
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
                                              
                                        }
                                    }
                                    else if(tip_=='Связанная таблица max-max'){
                                      
                                        if ($('.__other__add_form #'+col_).size()>0){
                                            var tip_max=$('.__other__add_form #'+col_).get(0).tagName;
                                            if (tip_max=='DIV'){
                                                $('.__other__add_form #'+col_+' ul li').each(function(){
                                                    var each_id=$(this).data('id');
                                                    if (in_array(each_id,val_)){
                                                        $(this).find('>div').addClass('active');
                                                    }
                                                });
                                                
                                                    _other_form_close_tree($('.__other__add_form #'+col_));
                                                
                                                
                                           
                                            }else{//SELECT
                                                
                                                for (var id_max in val_){
                                                    var txt='<option value="'+id_max+'" selected="selected">'+val_[id_max]+'</option>';
                                                    $('.__other__add_form #'+col_+'').append(txt);
                                                }
                                                //////////////////////////////////////////////////////////////////////////
                                                //max-max
                                                  $('.__other__add_form #'+col_+'').each(function(){
                                                    var th_=$(this);
                                                   
                                                    //исключение - связанные товары
                                                    var _col_name=th_.closest('.ttable_tbody_tr').data('col');// имя столбца заполнения
                                                    var w_='100%';
                                                    var str_='';
                                                    if (_col_name=='s_cat_s_cat'){
                                                        w_='50%';
                                                        th_.closest('.ttable_tbody_tr_td').append('<select class="s_cat_s_cat_s_str"><option value="-1">[Все]</option></select>');
                                                        $('.s_cat_s_cat_s_str').select2({'width':'45%',
                                                            ajax: {
                                                                url: "ajax/__other__.php",
                                                                dataType: 'json',
                                                                delay: 250,
                                                                data: function (params) {
                                                                    return {
                                                                        q: params.term,
                                                                        page: params.page,
                                                                        _t:'s_cat_s_cat_s_str'
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
                                                        });
                                                    }//*********** исключение
                                                    
                                                    th_.select2({'width':w_,
                                                        ajax: {
                                                            url: "ajax/__other__.php",
                                                            dataType: 'json',
                                                            delay: 250,
                                                            data: function (params) {
                                                                return {
                                                                    q: params.term,
                                                                    page: params.page,
                                                                    _t:'autocomplete_max_max',
                                                                    _col_id:th_.data('col'),
                                                                    str_:th_.closest('.ttable_tbody_tr_td').find('.s_cat_s_cat_s_str').val()
                                                                    
                                                                };
                                                            },
                                                            processResults: function (data, page) {
                                                                return {
                                                                results: data.items
                                                                };
                                                            },
                                                            cache: true
                                                        },
                                                        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                                                        minimumInputLength: 0
                                                        
                                                    });
                                                   
                                                  });
                                                /////////////////////////////////////////////////////////////////////////
                                                //max-max
                                               
                                                
                                            }
                                        } else{
                                            $('.__other__add_form .ttable_tbody_tr[data-col='+col_+'] .ttable_tbody_tr_td:last-child').html('Нет данных для связи max-max!');
                                            
                                        }
                                    }
                                    else if(tip_=='Функция'){ //change
                                        
                                        all_functions(col_,'change',data_n);
                                    }
                                    else if(tip_=='chk'){
                                        if (val_=='1'){$('.__other__add_form').find('input[name='+col_+']').prop('checked','checked');}
                                        else{$('.__other__add_form').find('input[name='+col_+']').removeAttr('checked');}
                                    }
                                    else if(tip_=='enum'){
                                        $('.__other__add_form').find('select[name='+col_+'] option').each(function(){
                                            if ($(this).val()==val_){
                                                $(this).attr('selected','selected');
                                            }
                                        });
                                        
                                        $('.__other__add_form').find('select[name='+col_+']').select2({'width':'100%'}).on("select2:select", function (e) {$(this).select2('open');})
                                            .on("select2:unselect", function (e) {$(this).select2('open');});
                                        
                                    }
                                    else if(tip_=='Цвет'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_);
                                        var th_=$('.__other__add_form').find('input[name='+col_+']');
                                        th_.ColorPicker({color:'#'+val_,onChange: function (hsb, hex, rgb) {
                                            th_.val(hex);
                                            th_.closest('.ttable_tbody_tr_td').find('.color_ex').css({'background':'#'+hex});
                                        }}).bind('keyup', function(){
                                            $(this).ColorPickerSetColor(this.value);
                                            th_.closest('.ttable_tbody_tr_td').find('.color_ex').css({'background':'#'+this.value});
                                        }).keyup();
                                    }
                                    else if(tip_=='Фото'){
                                        // Обработка загруженных фото
                                        var photo_id=val_['id'];
                                        var photo_img=val_['img'];
                                        var photo_tip=val_['tip'];
                                        var photo_info=val_['info'];
                                        var photo_comments=val_['comments'];
                                        
                                        $('.__other__add_form .photo_tip_ex').select2({'width':'100%'});
                                        
                                        
                                        for (var i in photo_id){
                                            $('.__other__add_form .__other__photo_res').append('<li data-num="'+i+'" class="photo_res__item" data-img="'+photo_img[i]+'"><a rel="load_photo" href="../i/'+$('#page_res').data('inc')+'/original/'+photo_img[i]+'" class="zoom"><div class="photo_res__item_image" style="background: url(../i/'+$('#page_res').data('inc')+'/small/'+photo_img[i]+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></a>'+photo_data+'<span class="__other__size_img">'+photo_info[i][0]+'x'+photo_info[i][1]+'</span></li>');
                                            $('.zoom').fancybox();
                                            //меняем тип фото
                                            $('.__other__add_form li[data-num='+i+']').find('select[name=photo_tip] option[value="'+photo_tip[i]+'"]').attr('selected','selected');
                                            $('.__other__add_form li[data-num='+i+']').find('select[name="photo_tip"]').select2({'width':'100%'}).closest('li').find('input[name="photo_desc"]').val(photo_comments[i]);
                                            $('.__other__add_form .__other__photo_res').sortable({delay: 150,placeholder: "photo_res__item_placeholder",start: function( event, ui ) {$('.zoom').unbind();},stop: function( event, ui ) {setTimeout(function(){$('.zoom').fancybox();},200);}});
                                        
                                        }
                                        
                                        //перезагрузка фото
                                        $('.__other__add_form .drop_target').attr('id','add_form_upload_photo');
                                        var upload_photo = new plupload.Uploader({
                                            runtimes : 'html5,flash,silverlight,html4',
                                        	browse_button : 'add_form_upload_photo',
                                            drop_element : 'add_form_upload_photo',
                                            url : 'ajax/__other__.php',
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
                                                       up.setOption('multipart_params', {'_t' : 'upload','_inc':$('#page_res').data('inc')});
                                                    }
                                                },
                                                init : {
                                                    QueueChanged: function(up) {upload_photo.start();},
                                            		BeforeUpload: function(up, file) {$('.__other__add_form .upload_photo_loading').addClass('ico').addClass('ico_loading');},
                                                    FileUploaded: function(up, file, info) {
                                                        var num=$('.__other__photo_res .photo_res__item').size();
                                                        $('.__other__add_form .__other__photo_res').prepend('<li data-num="'+num+'" class="photo_res__item" data-img="'+info.response+'"><a rel="load_photo" href="../i/'+$('#page_res').data('inc')+'/temp/'+info.response+'" class="zoom"><div class="photo_res__item_image" style="background: url(../i/'+$('#page_res').data('inc')+'/temp/'+info.response+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></a>'+photo_data+'<span class="__other__size_img"></span></li>');
                                                        $('.zoom').fancybox();
                                                        $('.__other__add_form .upload_photo_loading').removeClass('ico').removeClass('ico_loading');
                                                        
                                                        //меняем тип фото
                                                        $('.__other__add_form li[data-num='+num+']').find('select[name=photo_tip]').val($('.__other__add_form  select.photo_tip_ex').val()).select2({'width':'100%'});
                                                        $('.__other__photo_res').sortable({delay: 150,placeholder: "photo_res__item_placeholder",start: function( event, ui ) {$('.zoom').unbind();},stop: function( event, ui ) {setTimeout(function(){$('.zoom').fancybox();},200);}});
                                
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
                                           
                                           
                                           
                                           $('.__other__add_form .photo_crop').select2({'width':'100%'}).change(function(){
                                                
                                                var opt_=$(this).val();
                                                if (opt_=='1'){
                                                    upload_photo.settings.resize.crop=false;
                                                }
                                                else if(opt_=='2'){
                                                    upload_photo.settings.resize.crop=true;
                                                }
                                                upload_photo.refresh();
                                           }).change();
                                           $('.__other__add_form .photo_compress').select2({'width':'100%'}).change(function(){
                                                
                                                var opt_=$(this).val();
                                                if (opt_=='1'){
                                                    upload_photo.settings.resize.width=5000;
                                                    upload_photo.settings.resize.height=5000;
                                                    upload_photo.settings.resize.quality=100;
                                                }else{
                                                    upload_photo.settings.resize.quality=80;
                                                    if(opt_=='2'){
                                                        upload_photo.settings.resize.width=500;
                                                        upload_photo.settings.resize.height=500;
                                                    }
                                                    else if(opt_=='3'){
                                                        upload_photo.settings.resize.width=1000;
                                                        upload_photo.settings.resize.height=1000;
                                                    }
                                                    else if(opt_=='4'){
                                                        upload_photo.settings.resize.width=1500;
                                                        upload_photo.settings.resize.height=1500;
                                                    }
                                                    else if(opt_=='5'){
                                                        upload_photo.settings.resize.width=2000;
                                                        upload_photo.settings.resize.height=2000;
                                                    }
                                                }
                                                upload_photo.refresh();
                                           }).change();
                                           
                                           
                                    }
                                    else if(tip_=='Ссылка'){
                                        $('.__other__add_form').find('input[name='+col_+']').val(val_);
                                    }
                                    else{
                                        alert_m( 'Не определен тип tip_="'+tip_+'"','','error','none');
                                    }
                                
                            }
                            
                            //***********************************************************************
                            if (typeof callback=='function'){callback(data_n);}
                            //
                            //замена кнопки
                            setTimeout(function(){
                                top_menu('savechange');
                                window_add_open=1; //окно открылось
                               
                            },500);
               
                      
                            //Название или keywords или description
                            $('.__other__add_form .ttable_tbody_tr[data-tip="Длинный текст"] textarea, .__other__add_form .ttable_tbody_tr[data-tip="Текст"] input').keyup(function(){
                                var ln_=($(this).val()).length;
                                $(this).closest('.ttable_tbody_tr').find('.__other__add_form_len_text').html('('+ln_+')');
                            });
                            //Длина строки
                            $('.__other__add_form .ttable_tbody_tr[data-tip="Длинный текст"] textarea, .__other__add_form .ttable_tbody_tr[data-tip="Текст"] input').each(function(){
                                $(this).closest('.ttable_tbody_tr').find('.col_us_mini').append('<span class="__other__add_form_len_text"></span>');
                            }).keyup();
                           
                            //Открытие закрытие связанных таблиц
                            if ($('.__other__add_form .connect_max_max_close_all_tree').length>0){
                                if (tree=='close'){
                                    $('.__other__add_form .connect_max_max_close_all_tree').trigger('click');
                                }
                                else if (tree=='open'){
                                    $('.__other__add_form .connect_max_max_open_all_tree').trigger('click');
                                }
                            }
                            
                    }
                    else{
                        alert_m(data,function(){
                            
                            $.arcticmodal('close');
                            chk_select();
                        },'error','none');
                    }
                }
             });
}


//Изменение
$(document).delegate('.nomer_com___change, .__quick_change_maxcol','click',function(){
        
        var data_=new Object();
        var th_=$(this);
        data_['_inc']=$('#page_res').data('inc');
        //быстрое массовое изменение
        var quick_change_maxcol=0;
        var cl_=th_.attr('class');
            if ((cl_.split('__quick_change_maxcol').length - 1)>0){
                data_['_nomer']=sel_in_array($('.__other__res_table .ttable_tbody .ttable_tbody_tr.active'),'data','id');
                data_['_nomer']=data_['_nomer'].join(',');
                var _nomer=implode(', ',data_['_nomer']);
                quick_change_maxcol=1;
                data_['_col']=th_.closest('.ttable_thead_tr_th').data('col');
            }else{
                data_['_nomer']=th_.data('id');
                var _nomer=data_['_nomer'];
                data_['_col']='-1';//все
                history_name='Изменение записи №'+data_['_nomer'];
                history_url='?inc='+$('#page_res').data('inc')+'&com=_change&nomer='+_nomer;
                History.replaceState({state:3},history_name, history_url);
                history.pushState({param: history_name}, '', history_url);
            }
        
        
        $('.__other__add_form').arcticmodal('close');//исключаем вторичое открытие окна
        alert_m('<form class="__other__add_form">'+$('.__other__add').html()+'</form>',function(){
        
             
            //после закрытия окна
       
               if (typeof $('.__other__add_form').data('save')!='undefined'){ //было сохранение
                find('reload_all_new',function(){top_menu('add');save_tree_sort();});
               }else{
                   //после закрытия окна без сохранения
                    History.replaceState({state:3}, history_name, history_url);
                    history.pushState({param: history_name}, '', history_url);
                    window_add_open=0; //окно закрылось
                    chk_select();
                    
                    
               }
            
        },'hide','none','',function(){
            
            $('.__other__add_form>.ttable>.ttable_tbody_tr[data-col!=""]').css({'display':'none'});//закрываем все столбцы
            top_menu('loading');
            $('.__other__add_form .__other__add_copy_from_div').hide();//скрываем клонирование
            
            nomer_com___change(data_['_inc'],data_['_col'],data_['_nomer'],function(){
                $('.__other__add_form h2').html('Изменение значения №<span data-col="'+data_['_col']+'" class="row_id__span">'+_nomer+'</span>:');
            });
            
         });
        
    });
    
    
    
    
    //Добавление
    $(document).delegate('.top_com___add, .nomer_com___add','click',function(){
        
        // добавляем в ветку
        var cl_=$(this).attr('class'); var nomer_='';
        if ((cl_.split('nomer_com___add').length - 1)>0){nomer_=$(this).data('id');}
        
        $('.__other__add_form').arcticmodal('close');//исключаем вторичое открытие окна
        alert_m('<form class="__other__add_form"><input type="hidden" name="__pid" value="'+nomer_+'" />'+$('.__other__add').html()+'</form>',function(){
               
               //было сохранение
               if (typeof $('.__other__add_form').data('save')!='undefined'){
                find('reload_all_new',function(){top_menu('add');save_tree_sort();});
               }else{
                   //после закрытия окна без сохранения
                   window_add_open=0; //окно закрылось
                   chk_select();
               }
               
        },'hide','none','',function(){
            top_menu('loading');
            //заголовок 
            $('.__other__add_form h2').html('Добавление нового значения<span class="row_id__span"></span>:');
            
            
            
            //HTML-редактор
            var editor_=1;
            $('.__other__add_form .html_editor').each(function(i){
                    $(this).attr('id','editor'+editor_);
                    
                    var edit_=CKEDITOR.replace('editor'+editor_,{
                                allowedContent:true,
                                contentsCss: 'js/ckeditor/contents.css',
                                toolbar: [
                                { name: 'document', groups: [ 'mode', 'document' ], items: [ 'Source','-', 'Templates' ] },
                                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                                { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
                                { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                                { name: 'insert', items: [ 'Image',  'Table', 'Iframe','HorizontalRule' ] },
                                '/',
                                { name: 'styles', items: [ 'Styles', 'Format',  'FontSize' ] },
                                { name: 'colors', items: [ 'TextColor' ] },
                                { name: 'tools', items: [ 'Maximize' ] },
                                
                            ]
                    });
                    AjexFileManager.init({returnTo: 'ckeditor', editor: edit_});
                    //DjenxExplorer.init({returnTo: edit_,lang : 'ru'});
                    editor_++;
            });
            
            //автозаполнение
            $('.__other__add_form .ttable .ttable_tbody_tr[data-tip="Текст"] input[type=text]').each(function(){
                var th_=$(this);
                var col_id=th_.closest('.ttable_tbody_tr').data('col_id');// имя столбца заполнения
                th_.autocomplete({
                    minLength: 0,
                    appendTo: ".__other__add_form",
                    source: function(request, response){
                         request['_t']='autocomplete_input';
                         request['_col_id']=col_id;
                         
                         if (typeof jqxhr!='undefined'){jqxhr.abort();}
                         jqxhr = $.ajax({
                        	"type": "POST",
                        	"url": "ajax/__other__.php",
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
                    close: function( event, ui ) {
                        th_.trigger('keyup');
                    }
                });
            });
            
            //Длина строки
            $('.__other__add_form .ttable_tbody_tr[data-tip="Длинный текст"] textarea, .__other__add_form .ttable_tbody_tr[data-tip="Текст"] input').each(function(){
                $(this).closest('.ttable_tbody_tr').find('.col_us_mini').append('<span class="__other__add_form_len_text"></span>');
            });
            //Название или keywords или description
            $('.__other__add_form .ttable_tbody_tr[data-tip="Длинный текст"] textarea, .__other__add_form .ttable_tbody_tr[data-tip="Текст"] input').keyup(function(){
                var ln_=($(this).val()).length;
                $(this).closest('.ttable_tbody_tr').find('.__other__add_form_len_text').html('('+ln_+')');
            });
            
            //Целое число
            $('.__other__add_form .spinner').spinner().integer_();
            
            //Дробное число
            $('.__other__add_form .spinner_f').spinner({numberFormat: "n"}).float_();
            
            //Цена
            $('.__other__add_form .price').autoNumeric('init');
            //дата
            $(".__other__add_form .data").datetimepicker({lang:'ru',timepicker:false, mask:true,format:'d.m.Y',closeOnDateSelect:true});
            //Дата-время
            $(".__other__add_form .datatime").datetimepicker({lang:'ru', mask:true,format:'d.m.Y H:i:s'});
            //телефон
            $(".__other__add_form .phone").integer_().mask("0(000)000-00-00",{clearIfNotMatch: true});
            //enum
            $(".__other__add_form .enum").select2({'width':'100%'}).on("select2:select", function (e) {$(this).select2('open');})
                .on("select2:unselect", function (e) {$(this).select2('open');});
            
            //email
            $(".__other__add_form .email").emailautocomplete();
            //автозаполнение 1-max
            $('.__other__add_form .connect_1_max_sel').select2({allowClear: true,'width':'100%'}).on("select2:select", function (e) {$(this).select2('open');})
                .on("select2:unselect", function (e) {$(this).select2('open');}); 
          
            //in_array
            
             
             $(".__other__add_form .connect_1_max").autocomplete({
                appendTo: ".__other__add_form",
                minLength: 0
              }).focus(function() {
                   var th_=$(this);
                   var str=th_.closest('.ttable_tbody_tr_td').find('.connect_1_max_source').html();
                   
                        var col_id=th_.data('col_id');// имя столбца заполнения
                         th_.autocomplete("option", "source",function(request, response){
                             request['_t']='autocomplete_1_max';
                             request['_col_id']=col_id;
                             
                             if (typeof jqxhr!='undefined'){jqxhr.abort();}
                             jqxhr = $.ajax({
                            	"type": "POST",
                            	"url": "ajax/__other__.php",
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
                
              //max-max
              $('.__other__add_form .connect_max_max_sel').each(function(){
                var th_=$(this);
               
                //исключение - связанные товары
                var _col_name=th_.closest('.ttable_tbody_tr').data('col');// имя столбца заполнения
                var w_='100%';
                var str_='';
                if (_col_name=='s_cat_s_cat'){
                    w_='50%';
                    th_.closest('.ttable_tbody_tr_td').append('<select class="s_cat_s_cat_s_str"><option value="-1">[Все]</option></select>');
                    $('.s_cat_s_cat_s_str').select2({'width':'45%',
                            ajax: {
                                url: "ajax/__other__.php",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        q: params.term,
                                        page: params.page,
                                        _t:'s_cat_s_cat_s_str'
                                        
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
                        });
                }//*********** исключение
                
                th_.select2({'width':w_,
                    ajax: {
                        url: "ajax/__other__.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                page: params.page,
                                _t:'autocomplete_max_max',
                                _col_id:th_.data('col'),
                                str_:th_.closest('.ttable_tbody_tr_td').find('.s_cat_s_cat_s_str').val()
                                
                            };
                        },
                        processResults: function (data, page) {
                            return {
                            results: data.items
                            };
                        },
                        cache: true
                    },
                    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                    minimumInputLength: 0
                    
                });
               
              });
              
            //max-max
             $(".__other__add_form .connect_max_max").each(function(){
                _other_form_close_tree($('.__other__add_form #'+$(this).attr('id')));
                
                //Открытие закрытие связанных таблиц
                if ($('.__other__add_form .connect_max_max_close_all_tree').length>0){
                    if (tree=='close'){
                        $('.__other__add_form .connect_max_max_close_all_tree').trigger('click');
                    }
                    else if (tree=='open'){
                        $('.__other__add_form .connect_max_max_open_all_tree').trigger('click');
                    }
                }
                
                
             });
             
              
              
              //цвет
              $('.__other__add_form .color').each(function(){
                    var th_=$(this);
                    th_.ColorPicker({color:'#'+th_.val(),onChange: function (hsb, hex, rgb) {
                        th_.val(hex);
                        th_.closest('.ttable_tbody_tr_td').find('.color_ex').css({'background':'#'+hex});
                    }}).bind('keyup', function(){
                        $(this).ColorPickerSetColor(this.value);
                        th_.closest('.ttable_tbody_tr_td').find('.color_ex').css({'background':'#'+this.value});
                    });
                    
              });
              //функция
              $('.__other__add_form .ttable_tbody_tr[data-tip=Функция]').each(function(){//add
                    var th_=$(this);
                    all_functions(th_.data('col'),'add','');
              });
              
              //ФОТО
                $('.__other__add_form .photo_tip_ex').select2();
                
                                        
              //перезагрузка фото
            $('.__other__add_form .drop_target').attr('id','add_form_upload_photo');
            var upload_photo = new plupload.Uploader({
                runtimes : 'html5,flash,silverlight,html4',
            	browse_button : 'add_form_upload_photo',
                drop_element : 'add_form_upload_photo',
        
                url : 'ajax/__other__.php',
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
                           up.setOption('multipart_params', {'_t' : 'upload','_inc':$('#page_res').data('inc')});
                        }
                    },
                    init : {
                        QueueChanged: function(up) {upload_photo.start();},
                		BeforeUpload: function(up, file) {$('.__other__add_form .upload_photo_loading').addClass('ico').addClass('ico_loading');},
                        FileUploaded: function(up, file, info) {
                            var num=$('.__other__photo_res .photo_res__item').size();
                            $('.__other__add_form .__other__photo_res').prepend('<li data-num="'+num+'" class="photo_res__item" data-img="'+info.response+'"><a rel="load_photo" href="../i/'+$('#page_res').data('inc')+'/temp/'+info.response+'" class="zoom"><div class="photo_res__item_image" style="background: url(../i/'+$('#page_res').data('inc')+'/temp/'+info.response+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></a>'+photo_data+'</li>');
                            $('.zoom').fancybox();
                            $('.__other__add_form .upload_photo_loading').removeClass('ico').removeClass('ico_loading');
                            
                            //меняем тип фото
                            $('.__other__add_form li[data-num='+num+']').find('select[name=photo_tip]').val($('.__other__add_form  select.photo_tip_ex').val()).select2({'width':'120px'});
                            
                            $('.__other__add_form .__other__photo_res').sortable({delay: 150,placeholder: "photo_res__item_placeholder",start: function( event, ui ) {$('.zoom').unbind();},stop: function( event, ui ) {setTimeout(function(){$('.zoom').fancybox();},200);}});
    
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
               
               //замена кнопки
               $('.__other__add_form .photo_crop').select2({'width':'100%'}).change(function(){
                                                
                    var opt_=$(this).val();
                    if (opt_=='1'){
                        upload_photo.settings.resize.crop=false;
                    }
                    else if(opt_=='2'){
                        upload_photo.settings.resize.crop=true;
                    }
                    upload_photo.refresh();
               }).change();
               $('.__other__add_form .photo_compress').select2({'width':'100%'}).change(function(){
                    
                    var opt_=$(this).val();
                    if (opt_=='1'){
                        upload_photo.settings.resize.width=5000;
                        upload_photo.settings.resize.height=5000;
                        upload_photo.settings.resize.quality=100;
                    }else{
                        upload_photo.settings.resize.quality=80;
                        if(opt_=='2'){
                            upload_photo.settings.resize.width=500;
                            upload_photo.settings.resize.height=500;
                        }
                        else if(opt_=='3'){
                            upload_photo.settings.resize.width=1000;
                            upload_photo.settings.resize.height=1000;
                        }
                        else if(opt_=='4'){
                            upload_photo.settings.resize.width=1500;
                            upload_photo.settings.resize.height=1500;
                        }
                        else if(opt_=='5'){
                            upload_photo.settings.resize.width=2000;
                            upload_photo.settings.resize.height=2000;
                        }
                    }
                    upload_photo.refresh();
               }).change();
                                           
                                           
               setTimeout(function(){
                    top_menu('saveadd');
                    window_add_open=1; //окно открылось
               },500);
               
               
               
                //автозаполнение (копирование формы)
                $('.__other__add_form .__other__add_copy_from').autocomplete({
                appendTo: ".__other__add_form",
                minLength: 0,
                source: function(request, response){
                     request['_t']='__other__add_copy_from';
                     request['inc']=$('#page_res').data('inc');
                     copy_form_name='';
                     copy_form_id='';
                     if (typeof jqxhr!='undefined'){jqxhr.abort();}
                     jqxhr = $.ajax({
                    	"type": "POST",
                    	"url": "ajax/__other__.php",
                    	"dataType": "text",
                    	"data":request,
                    	"success":function(data,textStatus){
                    	   if (is_json(data)==true){
                        	        var data_n=JSON.parse(data);
                                    response(data_n);
                                    $('.ui-autocomplete:visible').css({'z-index':'1000'});
                                    $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                                    $('.ui-autocomplete:visible li').each(function(i,elem) {
                                        
                                    });
                            }else{
                                alert_m(data,'','error','none');
                            }
                    	}
                    });
                },
                select: function( event, ui ) {
                    copy_form_id=ui.item.id;
                    copy_form_name=ui.item.value;
                    
                },
                close: function( event, ui ) {
                    //$('input[name="i_contr"]').val('');
                    if (copy_form_id!=''){
                        //alert(copy_form_id+' - '+copy_form_name);
                        $('.__other__add_form>.ttable').html($('.__other__add>.ttable').html());
                       $('.__other__add_form .__other__add_copy_from').val('');
                        nomer_com___change($('#page_res').data('inc'),'-1',copy_form_id);
                    }
                }
            });
    
            
        },'1');
        
    });
    
    
    
    
    //СОХРАНЕНИЕ
    $(document).delegate('.__other__add__saveadd, .__other__add__saveclose, .__other__add__save', 'click',function(){
            var cur_class=$(this).attr('class');
            save(cur_class);
            
            
        
    });
    
    
    //УДАЛЕНИЕ
    $(document).delegate('.nomer_com___del','click',function(){
        var th_=$(this);
        var err_text='';
        var data_=new Object();
        data_['_t']='delete';
        data_['_inс']=$('#page_res').data('inc');
        
        
        if ($('.__other__tree').size()>0){//дерево
            var txt_='';
            th_.closest('li').find('.a_menu_block .ttable_tbody_tr').each(function(){
                if (txt_!=''){txt_+=',';}
                txt_+=$(this).data('id');
            });
            data_['_nomer']=txt_;
            th_.closest('li').detach();
        } 
        else{//таблица
            data_['_nomer']=th_.data('id');
            th_.closest('.ttable_tbody_tr').detach();
            
        }
        if (typeof data_['_nomer']=='undefined'){err_text+='<p>Не определен номер!</p>';}
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        $.ajax({
        	"type": "POST",
        	"url": "ajax/__other__.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   
        	   if (is_json(data)==false){
                    alert_m(data,function(){
                        find('',function(){chk_select();});
                    },'error','none');
                }
        	}
        });
        }
    });
    
    //МАССОВОЕ УДАЛЕНИЕ
    $(document).delegate('.__other__delete','click',function(){
        var data_=new Object();
        data_['_t']='delete';
        data_['_inс']=$('#page_res').data('inc');
        data_['_nomer']='';
        $('.__other__res_table .ttable_tbody .ttable_tbody_tr.active').each(function(){
           if (data_['_nomer']!=''){data_['_nomer']+=','; }
           data_['_nomer']+=$(this).data('id'); 
        });
        //data_['_nomer']=sel_in_array($('.__other__res_table .ttable_tbody .ttable_tbody_tr.active'),'data','id');
        $('.__other__res_table .ttable_tbody .ttable_tbody_tr.active').detach();
        
        
        $.ajax({
        	"type": "POST",
        	"url": "ajax/__other__.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   find('',function(){top_menu('add');});
        	   if (is_json(data)==false){
                    alert_m(data,function(){
                        window.location.reload(true);
                    },'error','none');
                }                
        	}
        });
    });
    
    //МУЛЬТИ ВЫДЕЛЕНИЕ


    $(document).delegate('.__other__res_table .ttable_tbody .ttable_tbody_tr','click',function(e){
       if (start_quick_change==0 && start_select_move==0){
            if (e.target.nodeName=='DIV'){
                if (start_select==0){
                    var class_=($(this).attr('class')).replace('ttable_tbody_tr ','');
                    
                    if (class_=='active'){
                        $(this).removeClass('active');
                    }else{
                        $(this).addClass('active');
                    }
                }
            }
            chk_select();
        }
    });


    $(document).delegate('.__other__res_table .ttable_tbody .ttable_tbody_tr','mousedown',function(e){
       if (start_quick_change==0){
            if (e.target.nodeName=='DIV'){
                e.preventDefault();
                start_select=1;
                var class_=($(this).attr('class')).replace('ttable_tbody_tr ','');
                select_class=class_;
            }
        }
    });
    
    $(document).delegate('*','mousedown',function(e){
       
        if (start_quick_change==1 && (e.target.nodeName=='DIV' || e.target.nodeName=='DIV')){
            //$('.page_name').prepend(' -'+start_quick_change+'-'+e.target.nodeName+';');
            $('select,input,textarea').trigger('blur');
        }
     });       
    
    
    $(document).delegate('.__other__res_table .ttable_tbody .ttable_tbody_tr','mouseup',function(e){
        if (start_quick_change==0){
            e.preventDefault();
            if (start_select==1){
                start_select=0;
                select_class='';
                chk_select();
            }
        }
    });
    $(document).delegate('.__other__res_table .ttable_tbody .ttable_tbody_tr','mousemove',function(e){
        if (start_quick_change==0){
            if (e.buttons==0 && start_select==1){start_select=0;select_class='';chk_select();}
            
            if (start_select==1){
                start_select_move=1;
                if (select_class=='active'){
                    $(this).removeClass('active');
                }else{
                    $(this).addClass('active');
                }
            }else{
                start_select_move=0;
            }
        }else{
                start_select_move=0;
            }
        
    });
    
    
    
    
    //выделить все
    $(document).delegate('.__other__check_all','click',function(){
        $('.__other__res_table .ttable_tbody .ttable_tbody_tr').addClass('active');
        chk_select();
    });
    //отменить выделение
    $(document).delegate('.__other__check_all_close','click',function(){
        $('.__other__res_table .ttable_tbody .ttable_tbody_tr').removeClass('active');
        chk_select();
    });
    
    
    //МАСОВОЕ ИЗМЕНЕНИЕ АКТИВНОСТИ
    $(document).delegate('.__other__active, .__other__noactive','click',function(){

        var data_=new Object();
        data_['_t']='quick_change';
        data_['_inс']=$('#page_res').data('inc');
        data_['_nomer']=sel_in_array($('.__other__res_table .ttable_tbody_tr.active'),'data','id');
        data_['_nomer']=data_['_nomer'].join(',');
        data_['col']='chk_active';
        data_['val']='0';
        if ($(this).attr('class')=='__other__active'){data_['val']='1';}
        
        $('.__other__res_table .ttable_tbody_tr.active').find('.__other_td__chk').each(function(){
            if($(this).data('col')=='chk_active'){
                if (data_['val']=='1'){$(this).find('input').prop('checked','checked');}
                else{$(this).find('input').removeAttr('checked');}
            }
        });
        $('.__other__check_all_close').click();
        $.ajax({
        	"type": "POST",
        	"url": "ajax/__other__.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   if (is_json(data)==false){
                    alert_m(data,function(){
                    },'error','none');
                }                
        	}
        });
    });
    
    //МАСОВОЕ Добавление товара в заказ
    $(document).delegate('.__other__add_to_zakaz','click',function(){
        
        var data_=new Object();
        data_['_nomer']=sel_in_array($('.__other__res_table .ttable_tbody_tr.active'),'data','id');
        data_['_nomer']=data_['_nomer'].join(',');
        window.location.href = "?inc=m_zakaz&add_id="+data_['_nomer'];

    });
    //МАСОВОЕ Добавление товара в поступление
    $(document).delegate('.__other__add_to_postav','click',function(){

        var data_=new Object();
        data_['_nomer']=sel_in_array($('.__other__res_table .ttable_tbody_tr.active'),'data','id');
        data_['_nomer']=data_['_nomer'].join(',');
        window.location.href = "?inc=m_postav&add_id="+data_['_nomer'];

    });

    
    //БЫСТРОЕ ИЗМЕНЕНИЕ
    $(document).delegate('input.quick_change, textarea.quick_change','change',function(){
        quick_change($(this));
        
    });
    //БЫСТРОЕ ИЗМЕНЕНИЕ
    $(document).delegate('span.quick_change','dblclick',function(){
        var th_=$(this).closest('.ttable_tbody_tr_td');
        var class_=th_.attr('class');
            class_=class_.replace('ttable_tbody_tr_td ','');
        var val_=$(this).text();
        var th_2=$(this);
        if (start_quick_change==0){//маркер быстрого редактирования
            start_quick_change=1;//маркер быстрого редактирования
            
            //$('.page_name').append(start_quick_change+'+'+class_);
            if (class_=='__other_td__text'
            ){
                th_.html('<textarea data-value="'+_IN(val_)+'" class="quick_change">'+val_+'</textarea>');
                th_.find('textarea.quick_change').focus().blur(function(){
                    
                    var th_2=$(this);
                    quick_change(th_2);
                    
                });
            }
            else if (class_=='__other_td__int'
                || class_=='__other_td__float'
                || class_=='__other_td__price'
                || class_=='__other_td__data'
                || class_=='__other_td__datatime'
                || class_=='__other_td__phone'
                || class_=='__other_td__email'
            ){
                th_.html('<input type="text" data-value="'+_IN(val_)+'" value="'+_IN(val_)+'" class="quick_change" />');
                
                
                $('.__other_td__data input').datetimepicker({lang:'ru',timepicker:false, mask:true,format:'d.m.Y',closeOnDateSelect:true});
                $('.__other_td__datatime input').datetimepicker({lang:'ru', mask:true,format:'d.m.Y H:i:s'});
                $('.__other_td__price input').autoNumeric('init');//цена
                $('.__other_td__int input').spinner().integer_(); //Целое число
                $('.__other_td__float input').spinner({numberFormat: "n"}).float_();//Дробное число
                $(".__other_td__email input").emailautocomplete();
                $(".__other_td__phone input").integer_().mask("0(000)000-00-00",{clearIfNotMatch: true});
                
                
                th_.find('input.quick_change').focus().blur(function(){
                    
                    var th_2=$(this);
                    quick_change(th_2);
                    
                }).keyup(function(e){if(e.which==13){
                    var th_2=$(this);
                    quick_change(th_2);
                    
                }});
            }
            else if (class_=='__other_td__link'){
                th_.html('<input type="text" data-value="'+$(this).find('a').attr('href')+'" value="'+$(this).find('a').attr('href')+'" class="quick_change" />');
                th_.find('input.quick_change').focus().blur(function(){
                    
                    var th_2=$(this);
                    quick_change(th_2);
                    
                }).keyup(function(e){if(e.which==13){
                    var th_2=$(this);
                    quick_change(th_2);
                    
                }});
            }
            else if (class_=='__other_td__longtext'){
                th_.html('<textarea class="quick_change">'+val_+'</textarea>');
                th_.find('textarea.quick_change').focus().blur(function(){
                    quick_change($(this));
                }).keyup(function(e){if(e.which==13){
                    quick_change($(this));
                }});
            }
            else if (class_=='__other_td__enum'){
                var tip_=th_.data('col');
                var data_enum=$('.__other__add').find('select[name='+tip_+']').html();
                th_.html('<select class="quick_change">'+data_enum+'</select>');
                $('select.quick_change option').each(function(){
                    var val_cur=$(this).text();
                    if (val_==val_cur){
                        $(this).attr('selected','selected');
                    }
                });
                
                // 
                $('select.quick_change').select2({'width':'200px'}).focus().blur(function(){
                    quick_change($(this));
                }).on("select2:select", function (e) {$(this).select2('open');})
                  .on("select2:unselect", function (e) {$(this).select2('open');});
                $('select.quick_change').select2('open');
           
            }
             else if (class_=='__other_td__1max'){
                var col_1max=th_.data('col');
               
               if ($('.__other__add').find('select[name='+col_1max+']').size()>0){
                    var data_1max=$('.__other__add').find('select[name='+col_1max+']').html();
                    th_.html('<select class="quick_change">'+data_1max+'</select>');
                    $('select.quick_change option').each(function(){
                        var val_cur=$(this).text();
                        if (val_==val_cur){
                            $(this).attr('selected','selected');
                        }
                    });
                    
                    // 
                    $('select.quick_change').select2({'width':'100%'}).focus().blur(function(){
                        quick_change($(this));
                    }).on("select2:select", function (e) {
                        $(this).select2('open');
                    }).on("select2:unselect", function (e) {
                        $(this).select2('open');
                    });
                    $('select.quick_change').select2('open');
                    
                }else{//INPUT
                    th_.html('<input type="text" class="quick_change" value="'+val_+'" />');
                    var col_=th_.closest('.ttable_tbody_tr_td').data('col');
                    th_.find('.quick_change').focus().autocomplete({
                            appendTo: ".__other__add_form",
                            minLength: 0,
                            source: function(request, response){
                                 request['_t']='autocomplete_1_max';
                                 request['_col']=col_;
                                 request['_inc']=$('#page_res').data('inc');
                                 th_.addClass('ui-autocomplete-loading');
                                 if (typeof jqxhr!='undefined'){jqxhr.abort();}
                                 jqxhr = $.ajax({
                                	"type": "POST",
                                	"url": "ajax/__other__.php",
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
                            close: function( event, ui ) {
                                th_.trigger('keyup');
                            }
                        });
                }
            }
            else if (class_=='__other_td__maxmax'){
                var col_maxmax=th_.closest('.ttable_tbody_tr_td').data('col');
                
                var val_max='';
                th_2.find('>span').each(function(){
                    var id_max=$(this).data('id');
                    var txt_max=$(this).text();
                    val_max+='<option value="'+id_max+'" selected="selected">'+txt_max+'</option>';
                });
                
                    th_.html('<select class="quick_change" name="'+col_maxmax+'" multiple>'+val_max+'</select>');
                    
                    var _col_id=th_.data('col_id');
                    $('select.quick_change[name='+col_maxmax+']').select2({'width':'100%',
                    allowClear: true,closeOnSelect:false,
                    ajax: {
                        url: "ajax/__other__.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, 
                                page: params.page,
                                _t:'autocomplete_max_max',
                                _col:col_maxmax
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
                        quick_change($(this));
                    }).on("select2:select", function (e) {
                        $('select.quick_change option[value="'+e.params.data['id']+'"]').text(e.params.data['name']);
                        $(this).select2('open');
                    }).on("select2:unselect", function (e) {
                        $(this).select2('open');
                    });
                    $('select.quick_change[name='+col_maxmax+']').select2('open');
                    
            }
            else{
                alert_m('Не определен тип! '+class_,'','error','none');
            }
        }//маркер быстрого редактирования
        
        
        
    });
    
    $(document).delegate('.cke_button__save','click',function(e){
        e.preventDefault();
        quick_change($(this));
    });
    // end quick change
    
    $(document).delegate('.top_com__other__close','click',function(){
        $.arcticmodal('close');
    })
    
    
    //ПАРСИНГ
   
    
    $(document).delegate('.top_com___add_parsing','click',function(){
        alert_m('<form class="__other__parsing_form">'+$('.add_parsing__hide').html()+'</form>',function(){
            find('',function(){
                chk_select();
            });
        },'add','none',1000,function(){
            top_menu('parsing');
        });
        
        //перебор по col
        var txt_parser='';
        var txt_parser_select='';
        $('.__other__add>.ttable>.ttable_tbody_tr').each(function(){
            var tip_=$(this).data('tip');
            var col_=$(this).data('col');
            var name_=$(this).find('>.ttable_tbody_tr_td:first span:first').next().text();
            txt_parser_select+='<option value="'+col_+'">'+name_+'</option>';
            
            
            if (tip_=='Текст' 
                    || tip_=='Длинный текст'
                    || tip_=='HTML-код'
                    || tip_=='Целое число'
                    || tip_=='Дробное число'
                    || tip_=='Стоимость'
                    || tip_=='Дата'
                    || tip_=='Дата-время'
                    || tip_=='Телефон'
                    || tip_=='Email'
                    || tip_=='Цвет'
                    || tip_=='Ссылка'
                    ){
                txt_parser+='<div class="ttable_tbody_tr">';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input type="checkbox" class="parsing_col_chk" name="parsing_chk_'+col_+'" value="1" /></div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input name="parsing_'+col_+'" type="text" placeholder="Селектор для '+name_+'" /><div><textarea placeholder="Код, после выполнения переменной $res" name="parsing__code_'+col_+'"></textarea></div></div>';
                txt_parser+='</div>';
            }

            else if(tip_=='Связанная таблица 1-max'){
                
                txt_parser+='<div class="ttable_tbody_tr">';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input type="checkbox" class="parsing_col_chk" name="parsing_chk_'+col_+'" value="1" /></div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input name="parsing_'+col_+'" type="text" placeholder="Селектор для '+name_+'" /><div><textarea placeholder="Код, после выполнения переменной $res" name="parsing__code_'+col_+'"></textarea></div></div>';
                txt_parser+='</div>';
            }
            else if(tip_=='Связанная таблица max-max'){
                
                if (typeof $('select[name=f__'+col_+']').html()!='undefined'){
                    txt_parser+='<div class="ttable_tbody_tr super_main__parsing">';
                        txt_parser+='<div class="ttable_tbody_tr_td"><input type="checkbox" class="parsing_col_chk" name="parsing_chk_'+col_+'" value="1" /></div>';
                        txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                        txt_parser+='<div class="ttable_tbody_tr_td"><input type="hidden" name="parsing_'+col_+'" value="\'\'" /><select data-col="'+col_+'" class="select_multi" name="parsing_'+col_+'_sel" multiple>'+$('select[name=f__'+col_+']').html()+'</select></div>';
                    txt_parser+='</div>';
                }
            }
            else if(tip_=='Функция'){
                
                txt_parser+='<div class="ttable_tbody_tr">';
                    txt_parser+='<div class="ttable_tbody_tr_td"></div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">';
                    txt_parser+=all_functions(col_,'parser');
                    txt_parser+='</div>';
                txt_parser+='</div>';
            }
            else if(tip_=='chk'){
                
                txt_parser+='<div class="ttable_tbody_tr">';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input type="checkbox" class="parsing_col_chk" name="parsing_chk_'+col_+'" value="1" /></div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input name="parsing_'+col_+'" type="text" placeholder="Селектор для '+name_+'" /><div><textarea placeholder="Код, после выполнения переменной $res" name="parsing__code_'+col_+'"></textarea></div></div>';
                txt_parser+='</div>';
            }
            else if(tip_=='enum'){
                
                txt_parser+='<div class="ttable_tbody_tr">';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input type="checkbox" class="parsing_col_chk" name="parsing_chk_'+col_+'" value="1" /></div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input name="parsing_'+col_+'" type="text" placeholder="Селектор для '+name_+'" /><div><textarea placeholder="Код, после выполнения переменной $res" name="parsing__code_'+col_+'"></textarea></div></div>';
                txt_parser+='</div>';
            }
            else if(tip_=='Фото'){
                
                txt_parser+='<div class="ttable_tbody_tr">';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input type="checkbox" class="parsing_col_chk" name="parsing_chk_'+col_+'" value="1" /></div>';
                    txt_parser+='<div class="ttable_tbody_tr_td">'+name_+'</div>';
                    txt_parser+='<div class="ttable_tbody_tr_td"><input name="parsing_'+col_+'" type="text" placeholder="Селектор для '+name_+'" /><div><textarea placeholder="Код, после выполнения переменной $res" name="parsing__code_'+col_+'"></textarea></div></div>';
                txt_parser+='</div>';
            }
            else{
                alert_m('Не опознан тип! tip_='+tip_,'','error','none');
            }
            
        });
        if (txt_parser_select!=''){
            txt_parser_select='<div>Укажите основной столбец*: <select name="_parsing__main">'+txt_parser_select+'</select> ';
            txt_parser_select+='<span style="color:red;font-size:20px;">ВАЖНО:</span> ';
            txt_parser_select+='<span>Селектор должен парсить не входя во внутрь страницы!</span> ';
            txt_parser_select+='<select name="_parsing__update"><option value="0">Не обновлять старые значения</option><option value="1">Обновлять старые значения</option></select>';
            txt_parser_select+='</div><br /> ';
            
        }
        
        txt_parser='<div class="ttable_thead"><div class="ttable_thead_tr"><div class="ttable_thead_tr_th">Парсить?</div>'
            +'<div class="ttable_thead_tr">Название</div>'
            +'<div class="ttable_thead_tr">Селектор</div></div></div><div class="ttable_tbody">'+txt_parser+'</div>';
        $('.__other__parsing_form .__other__parsing_res').before(txt_parser_select).append(txt_parser);
        $('select[name=_parsing__main]').val('name');
        
        //max-max
        $('.select_multi').each(function(){
            var th_=$(this);
            var _t='autocomplete_max_max';
            var _col=th_.data('col');// имя столбца заполнения
            th_.select2({'width':'100%',closeOnSelect:false,
                ajax: {
                    url: "ajax/__other__.php",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            _t:_t,
                            _col:_col
                        };
                    },
                    processResults: function (data, page) {
                        return {
                        results: data.items
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 0
                
            }).on("select2:select", function (e) {$(this).select2('open');})
              .on("select2:unselect", function (e) {$(this).select2('open');});
           
          });
        
        
    });
    
    
    
    
    //начало парсинга
    $(document).delegate('.top_com__other__start_parsing','click',function(){
        top_menu('stop_parsing');
        parsing_chk=0;
        parsing();
        
    
    });
    //остановка парсинга
    $(document).delegate('.top_com__other__stop_parsing','click',function(){
        top_menu('parsing');
        parsing_chk=1;
        jqxhr.abort();
    });
    
    
     //удаление hash
    $(document).delegate('.del_hash','click',function(){
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='del_hash';
        data_['nomer']=th_.data('id');
        if (data_['nomer']==''){err_text+='<p>Не определен id быстрой ссылки!</p>';}
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__other__.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			loading(0);
        	        if (data=='ok'){
        	            $('span#hash_'+data_['nomer']).detach();
                        th_.detach();
        	            
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    //Выгрузка в csv
    $(document).delegate('.top_com___export_csv','click',function(){
        
        var nomer='';var nomer_txt='';
        var cnt_=$('.__other__res_table .ttable_tbody .ttable_tbody_tr.active').size()-0;
        if (cnt_>0){
            $('.__other__res_table .ttable_tbody .ttable_tbody_tr.active').each(function(){
                if(nomer!=''){nomer+=',';}
                nomer+=$(this).data('id');
            });
            nomer_txt='Выгрузить '+cnt_+' '+end_word(cnt_,'выделенных записей','выделенную запись','выделенные записи');
        }else{
            nomer_txt='Выгрузить все записи';
        }
        
        
        var txt='';
        $('.__other__add .ttable .ttable_tbody_tr[data-col]').each(function(){
            
            var col_tip=$(this).data('tip');
            var col_=$(this).data('col');
            
            if (col_tip=='Фото'){
                var col_name_txt=$(this).find('.ttable_tbody_tr_td:first p:first').html()+' <span>photo</span>';
                
                txt +='<li data-col="'+col_+'"><input type="checkbox" id="export_chk_'+col_+'" name="export_chk_'+col_+'" data-col="'+col_+'" /> <label for="export_chk_'+col_+'">'
                +col_name_txt+'</label> <div class="export_textarea_div"><textarea placeholder="PHP код для изменения поля $data_[\''+col_+'\']" name="export_code['+col_+']"></textarea></div><div style="clear:both;"></div></li>';
            
            }
            else if (col_tip=='Функция'){
                //alert(col_);
                txt +=all_functions(col_,'export');
            }
            else{
               var col_name_txt=$(this).find('.ttable_tbody_tr_td:first').html();
               
               txt +='<li data-col="'+col_+'"><input type="checkbox" id="export_chk_'+col_+'" name="export_chk_'+col_+'" data-col="'+col_+'"/> <label for="export_chk_'+col_+'">'
                +col_name_txt+'</label> <div class="export_textarea_div"><textarea placeholder="PHP код для изменения поля $myrow[\''+col_+'\']" name="export_code['+col_+']"></textarea></div><div style="clear:both;"></div></li>';
            
            }
            
        });
        top_menu('export_csv');
        var txt_=$('.add_export_csv__hide').clone();
        txt_.find('.export_ul').html(txt);
        
        
        alert_m(txt_.html(),function(){
            chk_select();
        },'change','none',800,function(){
            $('.modal_change input[name=nomer]').val(nomer);
            $('.modal_change h3').text(nomer_txt);
            $('.modal_change .export_ul').sortable();
            $('.modal_change .export_all_form input[type=checkbox]').prop('checked','checked');
        });
        
    });
    
    //Отправка данных на получение csv
    $(document).delegate('.top_com__other__start_export_csv','click',function(){
        
        var i=0;
        $('.export_all_form .export_ul li').each(function(){
            var th_=$(this);
            if (th_.find('input[type=checkbox]').prop('checked')==true){
                th_.find('input[type=checkbox]').prop('name','col_chk['+i+']['+th_.data('col')+']');
                i++;
            }
        });
        $('.export_all_form').submit();
    });
    
    //Добавить новое поле при выгрузки в csv
    $(document).delegate('.export_all_form_add_null_col', 'click',function(){
        var kol_nul_col=$('li[data-col*=__null__]').size();
        $('.export_all_form ul').append('<li data-col="__null__'+kol_nul_col+'"><i class="fa fa-share-square" style="margin:5px 10px;color:#666;"></i> <input type="checkbox" data-id="export_chk_null_'+kol_nul_col+'" data-col="data_[\'__null__'+kol_nul_col+'\']" name="export_chk_data___null__'+kol_nul_col+'" /> <label for="export_chk_null_'+kol_nul_col+'">$data_[\'__null__'+kol_nul_col+'\']=\'\';</label> <div class="export_textarea_div"><textarea placeholder="PHP код для изменения поля $myrow[\'__null__'+kol_nul_col+'\']" name="export_code[__null__'+kol_nul_col+']"></textarea></div><div style="clear:both;"></div></li>');
        $('input[data-id=export_chk_null_'+kol_nul_col+']').prop('checked','checked');
    });
    
    // Сортировка данных при выборе быстрой ссылки - экспорт csv
    $(document).delegate('.span_hash_sort_and_create','click',function(){
        var th_=$(this);
        var new_data='';
        $('.modal_change input[name*="__null__"]').closest('li').detach();
        jQuery.each(this.attributes,function(i, val){
            var n_=val.name;
            if ((n_.split('data-').length - 1)>0){
                var name=n_.replace('data-','');
                var cnt_=$('.modal_change .export_ul li input[name="'+name+'"], .modal_change .export_ul li textarea[name="'+name+'"]').size();
                if (cnt_>0){
                    new_data+=$('.modal_change .export_ul li input[name="'+name+'"], .modal_change .export_ul li textarea[name="'+name+'"]').closest('li').outerHTML();
                    $('.modal_change .export_ul li input[name="'+name+'"], .modal_change .export_ul li textarea[name="'+name+'"]').closest('li').detach();
                }else{
                    if ((name.split('export_code[__null__').length - 1)>0){
                        var kol_nul_col=name.replace('export_code[__null__','');
                        kol_nul_col=kol_nul_col.replace(']','');
                        new_data+='<li data-col="__null__'+kol_nul_col+'"><i class="fa fa-share-square" style="margin:5px 10px;color:#666;"></i> <input type="checkbox" data-id="export_chk_null_'+kol_nul_col+'" data-col="data_[\'__null__'+kol_nul_col+'\']" name="export_chk___null__'+kol_nul_col+'" /> <label for="export_chk_null_'+kol_nul_col+'">$data_[\'__null__'+kol_nul_col+'\']=\'\';</label> <div class="export_textarea_div"><textarea placeholder="PHP код для изменения поля $myrow[\'__null__'+kol_nul_col+'\']" name="export_code[__null__'+kol_nul_col+']"></textarea></div><div style="clear:both;"></div></li>';
                    }
                }
                
            }
            
        });
        $('.modal_change .export_ul').prepend(new_data).sortable();
        th_.addClass('span_hash').removeClass('span_hash_sort_and_create').trigger('click').addClass('span_hash_sort_and_create').removeClass('span_hash');
    });
    
    //Удаление хэша экспорта
    $(document).delegate('del_hash_export_csv','click',function(){
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='del_hash_export';
        data_['nomer']=th_.data('id');
        if (data_['nomer']==''){err_text+='<p>Не определен id быстрой ссылки!</p>';}
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__other__.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			loading(0);
        	        if (data=='ok'){
        	            $('span#hash_'+data_['nomer']).detach();
                        th_.detach();
        	            
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    
    //Разворачиваем дерево
    $(document).delegate('.fa_max_tree','click',function(){
        $(this).closest('.a_menu_block_div').find('>ol').show();
        $(this).addClass('fa-minus').removeClass('fa-plus').addClass('fa_min_tree').removeClass('fa_max_tree');
    });
    //Сворачиваем дерево
    $(document).delegate('.fa_min_tree','click',function(){
        $(this).closest('.a_menu_block_div').find('>ol').hide();
        $(this).addClass('fa-plus').removeClass('fa-minus').addClass('fa_max_tree').removeClass('fa_min_tree');
    });


    //Скрываем столбец в дереве
    $(document).delegate('.__other__res_table .ttable_thead_tr_th .fa-close','click',function(){
       var col_=$(this).closest('.ttable_thead_tr_th').data('col');
       var inc_=$('#page_res').data('inc');
       $('.__other__res_table  .ttable_tbody_tr_td[data-col="'+col_+'"]').detach();
       $('.__other__res_table  .ttable_thead_tr_th[data-col="'+col_+'"]').detach();
       a_col_change(col_,inc_,-1);
    });

    //выводим все столбцы для отображения
    $(document).delegate('.__other__res_table .fa-eye-slash','click',function(){
        var err_text='';
        var data_=new Object();
        data_['_t']='a_col_change_all';
        data_['inc']=$('#page_res').data('inc');
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/__other__.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        var txt='';
        	            for(var i in data_n.col){
        	               var chk_=' checked="checked"'; if (data_n.view[i]=='-1'){var chk_='';}
        	               txt+='<div><input type="checkbox" id="chk0_'+data_n.col[i]+'" '+chk_+'/> <label for="chk0_'+data_n.col[i]+'">'+data_n.col_ru[i]+'</label></div>';
        	            }
                        if (txt!=''){
                            txt='<div class="chk_view_col_form">'
                                +'<div class="chk_view_col_form_res">'
                                    +txt
                                +'</div>'
                                +'<div class="chk_view_col_form_save"><span class="btn_orange">Сохранить</span></div>'
                                +'</div>'
                        }
                        alert_m(txt,'','ok','none');
        			}    
                    
                    
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    //Сохраняем столбцы
    $(document).delegate('.chk_view_col_form_save','click',function(){
        var inc_=$('#page_res').data('inc');
        var cnt_ =$('.chk_view_col_form_res input').length;
        $('.chk_view_col_form_res input').each(function(){
            var th_=$(this);
            var col_=str_replace('chk0_','',th_.attr('id'));
            
            if (th_.prop('checked')==true){
                a_col_change(col_,inc_,1,function(){
                    cnt_--;
                    if (cnt_==0){window.location.reload(true);}
                });
            }
            else{
                a_col_change(col_,inc_,-1,function(){
                    cnt_--;
                    if (cnt_==0){window.location.reload(true);}
                });
            }
        });
        
    });
    
    
    function many_save(arr_){
        if (arr_.length>0){
            $('.__other__add_form input[name="name"]').val(arr_[0]).trigger('keyup');

            
            save('__other__add__saveadd', function(){
                arr_.splice(0, 1);
                if (arr_.length>0){
                    many_save(arr_);
                }
            });
        }
    }
    
    //Автоматическое значение нескольких значений 
    $(document).delegate('.__other__add_many_add','click',function(){
        var err_text='';
        var data_=new Object();
        data_['__other__add_many']=$('.__other__add_form .__other__add_many').val();
        if (data_['__other__add_many']==''){
            err_text+='<p>Значение не должно быть пустым!</p>';
        }
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	var arr_=(data_['__other__add_many']).split("\n");
            for(var i in arr_){
                if ((arr_[i]).trim()==''){
                    arr_.splice(i, 1);
                }
            }
            many_save(arr_);
                //__other__add__saveadd
        }
    });
    
    
    //выгрузить/Загрузить записи
    $(document).delegate('.__other__copypaste','click',function(){
        var txt='<div class="__other__copypaste_div">';
        txt+='<h2>Выгрузка / Загрузка записей</h2>';
        txt+='<p>выгрузите выбранные записи в csv или добавьте в каталог записи из csv файла</p></hr>';
        txt+='<div>';
        if ($('.__other__res_table .ttable_tbody .ttable_tbody_tr.active').length>0){
            txt+='<span class="btn_orange __other__copy">Выгрузить csv</span> ';
        }
        
        txt+='<span class="btn_orange __other__paste" id="__other__paste">Загрузить csv</span><span class="__other__paste_span"></span>';
        txt+='</div>';
        txt+='</div>';
        //выводим окно
        alert_m(txt,'','info','none');
        
        //загрузка прайса
    var upload_photo = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : '__other__paste',
        drop_element : '__other__paste',
        url : 'ajax/__other__.php',
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
                { title : "docs files", extensions : "csv" }
            ]
        },
    	flash_swf_url : 'js/Moxie.swf',
    	silverlight_xap_url : 'js/Moxie.xap',
        preinit : {
                Init: function(up, info) {
                
                },
                UploadFile: function(up, file) {
                   up.setOption('multipart_params', {'_t' : '__other__paste','_inc' :$('#page_res').data('inc')});
                }
            },
            init : {
                QueueChanged: function(up) {upload_photo.start();},
        		BeforeUpload: function(up, file) {$('.__other__paste_span').html('<i class="ico ico_loading"></i> Идет загрузка...');$('#__other__paste').hide();},
                FileUploaded: function(up, file, info) {
                    //info.response
                    $('.__other__paste_span').html('');
                    $('#__other__paste').show();
                    var data=info.response;
                    if (is_json(data)==true){
                        data_n=JSON.parse(data);
                        var txt='';
                        
                        
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
        
    });
    
    //Выгрузка записей в csv 
    $(document).delegate('.__other__copy','click',function(){
          var data_=new Object();
        data_['_nomer']=sel_in_array($('.__other__res_table .ttable_tbody_tr.active'),'data','id');
        data_['_nomer']=data_['_nomer'].join(',');
        var form=$('<form class="__other__copyform" name="__other__copyform" action="?inc=copypaste&_inc='+$('#page_res').data('inc')+'" method="post"><textarea name="_nomer">'+data_['_nomer']+'</textarea></form>');
    
        $('body').append(form);
        form.submit();
      
    });
    
    //клонирование названия в описание
    $(document).delegate('.__othrer__clone_name_html_code','click',function(){
        var th_=$(this)
        var txt=CKEDITOR.instances[th_.closest('.ttable_tbody_tr').find('.html_editor').attr('id')].getData();
        txt= '<p>'+ th_.closest('.__other__add_form').find('input[name="name"]').val()+'</p>'+txt;
        CKEDITOR.instances[th_.closest('.ttable_tbody_tr').find('.html_editor').attr('id')].setData(txt);
    });
        
    //Выгрузка записей в csv 
    $(document).delegate('.__other__paste','click',function(){
        
    });
    
    <?php
    //****************************************************************************************************************************************************
    // **********************************************************   ОБРАБОТКА ПЕРЕМЕННЫХ GET-ЗАПРОСА *****************************************************
    // ***************************************************************************************************************************************************
    
    if ($com!=''){
        if ($com=='_add'){
            echo 'find("start",function(){$(".top_com___add").click();});// стартовый поиск';
        }
        elseif ($com=='_change'){
            if ($nomer!=''){
            ?>
            find("start",function(){
                $(".__other__fillter_id").val("<?=$nomer;?>"); 
                    find("",function(){
                        if ($(".nomer_com___change[data-id=<?=$nomer;?>]").size()>0){
                            $(".nomer_com___change[data-id=<?=$nomer;?>]").click();
                        }else{
                            alert_m('Отсутствует запись с id = <?=$nomer;?> или отключена функция изменения',function(){chk_select();},'error','none');
                        }
                    });
                
                });
           
            <?php 
            }else{
                echo 'find("start",function(){alert_m("Не указана переменная nomer!","","error");});// стартовый поиск';
            }
        }
        elseif ($com=='_del'){
            if ($nomer!=''){
            ?>
            find("start",function(){
                $(".__other__fillter_id").val("<?=$nomer;?>"); 
                    find("",function(){
                        if ($(".nomer_com___del[data-id=<?=$nomer;?>]").size()>0){
                            $(".nomer_com___del[data-id=<?=$nomer;?>]").click();
                        }else{
                            alert_m('Отсутствует запись с id = <?=$nomer;?>',function(){chk_select();},'error','none');
                        }
                        
                    });
                
                });
           
            <?php 
            }else{
                echo 'find("start",function(){alert_m("Не указана переменная nomer!","","error");});// стартовый поиск';
            }
        }
        elseif($com=='_find'){
            
            ?>
            find("start",function(){
                <?php
                //перебор по столбцам
                foreach($_REQUEST as $name_ => $val_){
                    if ($name_!='' and $name_!='com' and $name_!='inc'){
                        
                        
                        if (is_array($val_)){//заполнение нескольких строк
                            
                            foreach($val_ as $key2 => $val2){
                                if ($val2!=''){
                                    ?>
                                    if ($(".__other__res_form select[name='f__<?=_IN($name_);?>']").size()>0){
                                        $(".__other__res_form select[name='f__<?=_IN($name_);?>'] option[value='<?=$val2;?>']").attr("selected","selected").closest('select').select2();
                                    }
                                    if ($(".__other__res_form select[name='__function_<?=_IN($name_);?>']").size()>0){
                                        $(".__other__res_form select[name='__function_<?=_IN($name_);?>'] option[value='<?=$val2;?>']").attr("selected","selected");
                                    }
                                    <?php
                                }
                                ?>
                                
                                $(".__other__res_form input[name='f__<?=_IN($name_);?>']:eq(<?=_DB($key2);?>)").val("<?=_DB($val2);?>").attr('value',"<?=_DB($val2);?>");
                                $(".__other__res_form input[name='__function_<?=_IN($name_);?>']:eq(<?=_DB($key2);?>)").val("<?=_DB($val2);?>").attr('value',"<?=_DB($val2);?>");
                                
                                <?php
                            }
                            
                        }else{//одной строки
                            $name_=strip_tags($name_);
                            $val_=strip_tags($val_);
                            
                            ?>
    
                            $(".__other__res_form input[name='f__<?=_IN($name_);?>']").val("<?=_DB($val_);?>").attr('value',"<?=_DB($val_);?>");;
                            $(".__other__res_form input[name='__function_<?=_IN($name_);?>']").val("<?=_DB($val_);?>").attr('value',"<?=_DB($val_);?>");;
                            
                            if ($(".__other__res_form select[name='f__<?=_IN($name_);?>']").size()>0){
                                $(".__other__res_form select[name='f__<?=_IN($name_);?>'] option[value='<?=_DB($val_);?>']").attr("selected","selected").closest('select').select2();
                            }
                            if ($(".__other__res_form select[name='__function_<?=_IN($name_);?>']").size()>0){
                                $(".__other__res_form select[name='__function_<?=_IN($name_);?>'] option[value='<?=_DB($val_);?>']").attr("selected","selected");
                            }
                            <?php
                        }
                    }
                }
                ?>
                    find("",function(){
                        //alert_m('_find','','error','none');
                        chk_select();
                    });
                
                });
           
            <?php
        }
        else{
            ?>
            find("start",function(){alert_m("Функция <?=$com;?> не определена!","","error","none");});// стартовый поиск
            <?php
        }
    }else{
        if ($nomer!=''){
           ?>
            find("start",function(){
                $(".__other__fillter_id").val("<?=$nomer;?>");
                    find("",function(){
                        chk_select();
                    });
                });
           
            <?php 
        }
        else{
            echo 'find("start");// стартовый поиск';
        }
    }
    //****************************************************************************************************************************************************
    // **********************************************************  end ОБРАБОТКА ПЕРЕМЕННЫХ GET-ЗАПРОСА **************************************************
    // ***************************************************************************************************************************************************
    
    ?>

});
</script>