<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
     // добавить товары в поступление, через запятую, : - количество ?inc=m_zakaz&add_id=33447:2,33448,33446
?>
<script>
var jqxhr;
var jqxhr1;
var jqxhr2;

var i_contr_id;
var i_contr_name;
var i_contr_org;
var i_contr_org_id;
var i_contr_phone;
var i_contr_email;

var s_cat_arr=new Object();
var i_contr_arr=new Object();
var i_contr_org_arr=new Object();;
var i_contr_active_id;
var term;
var reg_ext=/.*?\./;//регулярка для расширения файла


var history_name=''; //история URL
var history_url=''; //история URL

var rko_txt='<?php            
$sql = "SELECT i_docs.name, i_docs.chk_active
    				FROM i_docs 
    					WHERE a_menu_id='50'
                        AND file_name='docx_rko'
 ";
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
{
    if ($myrow[1]=='1'){echo '<a class="m_postav_print_rko" data-href="?inc=i_docs&com=print&file_name=docx_rko&nomer=" href=""><i title="'.$myrow['name'].'" class="fa fa-print"></i></a>';}
}
?>';

//*****************************************************************************************************
// Поиск
function find(limit,callback){
    limit=limit || '';
    callback=callback || '';
    
    $('.m_postav__all_find_res').detach();
        //новый поиск
        if (limit==''){
            $('.m_postav_find_res').html('');
        }
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='m_postav__find';
    data_['txt']=$('.m_postav_fillter__find_txt input').val();
    data_['control_num']=$('.m_postav_fillter__control_num input').val();
    data_['status_']=sel_in_array($('.m_postav_fillter__tip li.active'),'data','val');//$('.m_postav_fillter__tip li.active').data('val');
    data_['in_sklad']=sel_in_array($('.m_postav_fillter__sklad_or_zakaz li.active'),'data','val');//$('.m_postav_fillter__sklad_or_zakaz li.active').data('val');
    data_['sort']=$('.m_postav_fillter__sort li.active').data('val');
    data_['d1']=$('input[name="m_postav_data1_find"]').val();
    data_['d2']=$('input[name="m_postav_data2_find"]').val();
    
    data_['limit']=limit;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
        $('.m_postav_find_res').append('<p class="m_postav__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			$('.m_postav__loading').detach();
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var cnt_=data_n.cnt_;//общее количество
                    var pl_all=data_n.pl_all;//общее количество платежей
                    var sum_all=data_n.sum_all;//общая сумма заказов
                    var cur_cnt_=0;//текущее количество
                    var txt='';
                    
                    for (var i in data_n['i']){//перебор по поставкам
                    
                        var work_arr=data_n.w[i];
                        var txt_items='';
                        var kol_=0;var sum_=0;var sum_pp=0;
                        var all_load=0;
                        
                        var dl_class='bez_dostavki';
                        var dl=data_n.dl[i];//просрочка дней
                        if (dl!='-'){
                            if ((dl-0)>0){
                                dl_class='dostavka_vputi';
                            }
                            else  if((dl-0)==0){
                                dl_class='dostavka_poluchenie';
                            }
                            else  if((dl-0)<0){
                                dl_class='dostavka_zaderzhka';
                            }
                            
                            
                        }
                        else{
                            //оплата
                            if (data_n.p[i]-0>0 && data_n.s[i]=='В обработке' ){
                                dl_class='dostavka_oplachen';
                            }
                        }
                        
                        var m_postav_m_zakaz_txt='';//заказы
                        var m_postav_m_zakaz_arr=new Object();//заказы
                        var ii=0;//заказы
                        
                        //перебор по товарам
                        for (var j in work_arr['i']){
                            kol_=kol_-0+(work_arr['k'][j]-0);
                            var itog=(work_arr['p'][j]-0)*(work_arr['k'][j]-0);
                            var itog_pp=(work_arr['pp'][j]-0)*(work_arr['k'][j]-0);
                            sum_=sum_-0+itog-0;
                            sum_pp=sum_pp-0+itog_pp-0;
                            
                            
                            var prod_txt='';
                            var prod=work_arr['pr'][j];
                                if (prod!=''){
                                    if ((prod.split(',').length - 1)>0){
                                        var prod_arr=prod.split(',');
                                    }else{
                                        var prod_arr=new Array();
                                        prod_arr[0]=prod;
                                    } 
                                    //продажа в заказе
                                    var info_zakaz='';
                                    var cl_zakaz='';
                                    if ((itog-0)<(work_arr['ps'][j]-0)){
                                        var sm=(work_arr['ps'][j]-0)-(itog-0);
                                        cl_zakaz=' m_postav_item__sales_plus';
                                        info_zakaz+='<p class="m_postav_item__sales_plus">Данный товар принес прибыли: <strong>'+sm+'</strong> руб.</p>';
                                        
                                    }else if((itog-0)>(work_arr['ps'][j]-0)){
                                        var sm=(itog-0)-(work_arr['ps'][j]-0);
                                        cl_zakaz=' m_postav_item__sales_minus';
                                        info_zakaz+='<p class="m_postav_item__sales_minus">Данный товар еще в минусе на сумму: <strong>'+sm+'</strong> руб.</p>';
                                    }else{
                                        cl_zakaz=' m_postav_item__sales_null';
                                        info_zakaz+='<p class="m_postav_item__sales_null">Данный товар окупил себя по 0</p>';
                                    }
                                    prod_txt+='<i class="fa fa-shopping-cart m_postav_item__sales thumbnail'+cl_zakaz+'"> '+count(prod_arr)+'<span>';
                                    prod_txt+='<div>Сумма заказов: <strong>'+work_arr['ps'][j]+'</strong> руб.</div>';
                                    prod_txt+=info_zakaz;
                                    
                                    
                                    for (var ii in prod_arr){
                                        prod_txt+='<a href="?inc=m_zakaz&com=_change&nomer='+prod_arr[ii]+'" target="_blank">Заказ №'+prod_arr[ii]+'</a><br />';
                                    }
                                    prod_txt+='</span></i>';
                                }
                                
                            var st_='';if (all_load>=3){st_=' style="display:none;"';}
                            
                            txt_items+='<div class="ttable_tbody_tr"'+st_+'>';
                                var name=work_arr['n'][j];
                                txt_items+='<div class="ttable_tbody_tr_td m_postav_item__td_name"><span class="for_mobile">Название</span><span class="val"><a href="../'+work_arr['i'][j]+'" target="_blank">'+work_arr['i'][j]+'</a>. ';
                                var img=work_arr['im'][j];
                                if (img!=''){
                                    txt_items+='<i class="fa fa-image thumbnail"><span>'+name+'';
                                    txt_items+='<a title="'+_IN(name)+'" href="../i/s_cat/original/'+_IN(img)+'" target="_blank" class="zoom"><img src="../i/s_cat/small/'+_IN(img)+'" /></a>';
                                    txt_items+='</span></i>';
                                }
                            
                                if (prod_txt!=''){
                                    txt_items+=prod_txt;
                                }
                                if (work_arr['mz'][j]!=''){
                                    txt_items+='<a href="?inc=m_zakaz&nomer='+work_arr['mz'][j]+'" target="_blank" title="Заказ №'+work_arr['mz'][j]+'"><i class="fa fa-reorder m_postav_find_m_zakaz_id"></i></a>';
                                }
                                txt_items+='<a href="?inc=s_cat&nomer='+work_arr['i'][j]+'" target="_blank">'+name+'</a>';
                                txt_items+=' <span class="s_cat_s_prop_val_tbl">'+work_arr['pv'][j]+'</span>';
                                txt_items+='<div>'+work_arr['c'][j]+'</div></span></div>';
                                txt_items+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Цена</span><span class="val">'+work_arr['p'][j]+'</span></div>';
                                txt_items+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Количество</span><span class="val">'+work_arr['k'][j]+'</span></div>';
                                txt_items+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Итого</span><span class="val">'+itog+'</span></div>';
                            txt_items+='</div>';
                            
                            
                            if (!in_array(work_arr['mz'][j],m_postav_m_zakaz_arr) && work_arr['mz'][j]!=''){
                                m_postav_m_zakaz_arr[ii]=work_arr['mz'][j];
                                if (m_postav_m_zakaz_txt!=''){m_postav_m_zakaz_txt+=', ';}
                                m_postav_m_zakaz_txt+='<a href="?inc=m_zakaz&nomer='+m_postav_m_zakaz_arr[ii]+'" target="_blank">'+m_postav_m_zakaz_arr[ii]+'</a>';
                                //m_postav_m_zakaz_txt+=work_arr['mz'][j];
                            }
                            all_load++;
                        }
                        var add_load=all_load-3;
                        if (all_load>3){txt_items+='<div><div class="m_postav_item__view_all_tr">Показать еще ('+add_load+')</div></div>';}
                        delete(work_arr);
                        var marza=sum_pp-sum_;
                        
                        var d1=(data_n.d1[i]).substr(0, 10);
                        var t1=(data_n.d1[i]).substr(11, 8);
                        
                            
                        var ststus_='';
                        if (data_n.s[i]=='В обработке'){ststus_='<i class="status_fa fa fa-clock-o thumbnail"><span><h3>В обработке</h3></span></i> <span class="for_mobile">В обработке</span>';}
                        if (data_n.s[i]=='Отправлен'){ststus_='<i class="status_fa fa fa-truck thumbnail"><span><h3>Номер:</h3><p>'+data_n.dt[i]+'</p><div><a target="_blank" href="https://www.pochta.ru/tracking#'+data_n.dt[i]+'">На сайт Почты</a></div></span></i> <span class="for_mobile">Отправлен</span>';}
                        if (data_n.s[i]=='Доставлен'){ststus_='<i class="status_fa fa fa-check-circle-o thumbnail"><span><h3>Доставлен:</h3><p>'+data_n.d2[i]+'</p></span></i> <span class="for_mobile">Доставлен</span>';}
                        if (data_n.s[i]=='Отменен'){ststus_='<i class="status_fa fa fa-times-circle" title="Отменен"></i> <span class="for_mobile">Отменен</span>';}
                        
                        var pl_='';
                        
                        if (data_n.p[i]-0==0){
                            pl_='circle-o';
                        }else{
                            if ((sum_-0)>(data_n.p[i]-0)){
                                pl_='dot-circle-o';
                            }
                            else{
                                pl_='check-circle';
                            }
                            if (data_n.s[i]=='В обработке'){
                                ststus_='<i class="status_fa fa fa-rub" title="Оплачен"></i> <span class="for_mobile">Оплачен</span>';
                            }
                        }
                        
                        //alert(m_postav_m_zakaz_txt);
                        txt+='<div class="m_postav_item '+dl_class+'" data-id="'+data_n.i[i]+'">';
                        txt+='<div class="m_postav_item_left">';
                            txt+='<div class="m_postav_item__main">';
                                txt+='<div class="m_postav_item__id">';
                                    //txt+='<input type="checkbox" name="select_item" id="m_postav_item_'+data_n.i[i]+'" />';
                                    txt+='<label for="m_postav_item_'+data_n.i[i]+'"><span class="for_mobile">Поступление №</span>';
                                    txt+=data_n.i[i];
                                    if (m_postav_m_zakaz_txt!=''){
                                        txt+=' <span class="m_postav_item_m_zakaz_id"><span class="for_mobile">Заказы: </span> '+m_postav_m_zakaz_txt+'</span>';
                                    }
                                    if (data_n.cn[i]>0){
                                        txt+='<div class="control_num_find_item" title="Номер накладной">'+data_n.cn[i]+'</div>';
                                    }
                                    txt+='</label>';
                                    txt+='<span class="for_mobile"> от </span><div class="m_postav_item__data1 thumbnail">'+d1+'<span><h3>Создан:</h3><p>'+d1+'</p><p>'+t1+'</p></span></div>';
                                txt+='</div>';
                                txt+='<div class="m_postav_item__status">';
                                txt+=ststus_;
                                if (dl!='-'){
                                    txt+='<span class="m_postav_item__days_ thumbnail">'+dl+'<span>Ориентировочно до доставки: <br />'+dl+' '+end_word(dl, 'дней','день','дня')+'</span></span>';
                                }
                                
                                txt+='</div>';
                            txt+='</div>';
                            txt+='<div class="m_postav_item__i_contr">';
                                if (data_n.o[i]!=''){txt+='<div class="m_postav_item__i_contr_org" data-id="'+data_n.oi[i]+'"><span>'+data_n.o[i]+'</span><div class="m_postav_item__i_contr_name" data-id="'+data_n.ci[i]+'"><span>'+data_n.c[i]+'</span></div></div>';}
                                else{txt+='<div class="m_postav_item__i_contr_name" data-id="'+data_n.ci[i]+'"><span>'+data_n.c[i]+'</span></div>';}
                                
                                txt+='<div class="m_postav_item__i_contr_cont">'
                                    if (data_n.cp[i]!=''){txt+='<span class="m_postav_item__i_contr_phone"><i class="fa fa-phone"></i> <a href="tel:'+data_n.cp[i]+'">'+data_n.cp[i]+'</a></span>';}
                                    if (data_n.ce[i]!=''){txt+='<span class="m_postav_item__i_contr_email"><a target="_blank" href="mailto:'+data_n.ce[i]+'?subject=Поступление №'+$('input[name="nomer"]').val()+'">'+data_n.ce[i]+'</a></span>';}
                                    
                                txt+='</div>'
                            txt+='</div>'
                            
                            txt+='<div class="m_postav_item__project_name">'+data_n.pn[i]+'</div>';
                            txt+='<div style="clear:both;"></div>';
                            txt+='<div class="m_postav_item__mess">'+data_n.h[i]+'</div>';
                            
                            
                        txt+='</div>';
                        txt+='<div class="m_postav_item_right">';
                            
                            txt+='<div class="m_postav_item__pay"><i class="fa fa-money"><i class="fa fa-'+pl_+'"></i></i><span>Оплата <span>'+number_format(data_n.p[i],0,'.',' ')+'</span><i class="fa fa-rouble"></i></span></div>';
                            txt+='<p class="m_postav_item__s_cat_itogo">';
                            txt+='Итого: <span>'+number_format(kol_,0,'.',' ')+'</span> шт. на сумму: <span>'+number_format(sum_,0,'.',' ')+' <i class="fa fa-rouble"></i></span> ';
                            txt+='<i class="fa fa-info-circle m_postav_price_info"><span>Продажная стоимость товара в заказе: <strong>'+number_format(sum_pp,0,'.',' ')+'</strong> руб.<br /> Маржа с заказа: <strong>'+number_format(marza,0,'.',' ')+'</strong> руб.</span></i>';
                            txt+='</p><div style="clear:both;"></div>';
                            
                            txt+='<div class="ttable m_postav_item__s_cat_tbl">'+txt_items+'</div>';
                            
                        txt+='</div><div style="clear:both;"></div>';
                        txt+='</div>';
                        cur_cnt_++;
                    }
                    if (txt==''){txt='Поступлений не найдено!';}
                    
    	            if (txt!=''){
    	               cur_cnt_=cur_cnt_+($('div.m_postav_item').size())-0;
                       if (cnt_>cur_cnt_){
                        
                            txt=txt+'<div class="m_postav__load_add">Загрузить ещё...</div>';
                       }
                       txt='<p class="m_postav__all_find_res">Найдено <strong>'+cnt_+'</strong> поступлени'+end_word(cnt_,'й','е','я')+' на сумму: <strong>'+number_format(sum_all,0,'.',' ')+'</strong> руб., совершено платежей на сумму: <strong>'+number_format(pl_all,0,'.',' ')+'</strong> руб.</p>'+txt;
    	               $('.m_postav_find_res').append(txt);
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

//*****************************************************************************************************
//Загрузка поступления для изменения
function postav_load(id,callback){
    
    
    callback=callback || '';
    add_zakaz_form_clear();
    
    
    //Формирование ссылки HISTORY
    history_url='?inc=m_postav&nomer='+id;
    history_name='№'+id+'. Поступление товаров';
    History.replaceState({state:3}, history_name, history_url);
    
    
    var th_=$(this);
    var data_=new Object();
    data_['_t']='postav_load';
    data_['nomer']=id;
	loading(1);
	$.ajax({
		"type": "POST",
		"url": "ajax/m_postav.php",
		"dataType": "text",
		"data":data_,
		"success":function(data,textStatus){
			loading(0);
	        if (is_json(data)==true){
	            data_n=JSON.parse(data);
                
	            if (typeof data_n.d1 !='undefined'){
	               $('.m_postav_add__html_code_hide').html(data_n.h);
                   $('.m_postav_add_head h2').html('Изменение поступления №'+data_['nomer']+' <input type="hidden" value="'+data_['nomer']+'" name="nomer">');
                
                    $('input[name="date"]').val(data_n.d1);
                    $('input[name="date_info"]').val(data_n.d2);
                    $('input[name="project_name"]').val(data_n.pn);
                    $('textarea[name="comments"]').val(data_n.c);
                    
                    //Добавляем контрагента
                    i_contr_select(data_n['i_contr'],data_n['i_contr_org'],data_n['active']);
                    
                    $('select[name="a_admin"] option').removeAttr('selected');
                    $('select[name="a_admin"] option[value="'+data_n.a+'"]').prop('selected','selected');
                    $('select[name="a_admin"]').select2({'width':'100%',minimumResultsForSearch: 20});
                    change_worker_tp();//выбор работника
                    
                    
                    
                    //товары, услуги
                    var sal_kol=0;
                    var sum_order=0;
                    var barcode='';
                    var txt_zakaz='';
                    var m_zakaz_arr=new Object();
                    var ii=0;
                    
                    if (typeof data_n.i['i']!='undefined'){
                        var items_=data_n.i;
                        for(var i in items_.i){
                            
                            barcode='';
                            if (typeof items_.b[i] =='object'){
                                var cnt=count(items_.b[i]);
                                if (items_.k[i]==cnt || cnt>1){//штучный
                                    barcode='0||';
                                }
                                else{//партийный
                                    barcode='1||';
                                }
                                
                                var j=0;
                                
                                //вначале добавляем проданные
                                for (var m_tovar_id in items_.b[i]){
                                    if (items_.sal[i][m_tovar_id]!=''){
                                        barcode+=m_tovar_id+'##'+items_.b[i][m_tovar_id]+'##'+items_.sal[i][m_tovar_id];
                                        
                                        if (j<cnt-1){barcode+='@@';}
                                        sal_kol++;
                                        j++;
                                    }
                                }
                                //потом добавляем не проданные
                                for (var m_tovar_id in items_.b[i]){
                                    if (items_.sal[i][m_tovar_id]==''){
                                        barcode+=m_tovar_id+'##'+items_.b[i][m_tovar_id]+'##'+items_.sal[i][m_tovar_id];
                                        
                                        if (j<cnt-1){barcode+='@@';}
                                        j++;
                                    }
                                }
                            }
                            
                            //По заказу
                            if (items_.mz[i]-0>0 && !in_array(items_.mz[i],m_zakaz_arr)){
                                if (txt_zakaz!=''){txt_zakaz+=', ';}
                                txt_zakaz+='<a href="?inc=m_zakaz&nomer='+items_.mz[i]+'" target="_blank">Заказ №'+items_.mz[i]+'</a>';
                                m_zakaz_arr[ii]=items_.mz[i];
                                ii++;
                            }
                            
                            sum_order=(sum_order-0)+(items_.p[i]*items_.k[i])-0;
                            s_cat_add(items_.i[i],items_.n[i],items_.k[i],items_.p[i],items_.t[i],items_.c[i],barcode,items_.img[i],sal_kol,items_.pr[i],items_.mz[i],items_.mzt[i]);
                        }
                    }
                    
                    
                    //платежи
                    var sum_pay=0;
                    if (typeof data_n.pl['p']!='undefined'){
                        var pl_=data_n.pl;
                        for(var i in pl_.p){
                            if (pl_.t[i]=='Кредит'){
                                sum_pay=(sum_pay-0)-(pl_.p[i]-0);
                                pl_add(pl_.d[i],-(pl_.p[i]-0),pl_.s[i],pl_.i[i],pl_.t[i],'Кредит');
                            }
                            else if (pl_.t[i]=='Дебет'){
                                sum_pay=(sum_pay-0)+(pl_.p[i]-0);
                                pl_add(pl_.d[i],pl_.p[i],pl_.s[i],pl_.i[i],pl_.t[i]);
                            }
                            else{
                                alert(pl_.t[i]);
                            }
                            
                        }
                    }
                    //файлы
                    if (typeof data_n.fl!='undefined'){
                        var fl_=data_n.fl;
                        var txt_f='';
                        
                        
                        var t_txt='';
                        for(var i in fl_.f){
                            var img_=fl_.f[i];
                            var name_=fl_.c[i];
                            var ext=img_.replace(reg_ext, "");
                            var t_txt='';
                            if (ext=='jpg' || ext=='jpeg' || ext=='gif' || ext=='png'){
                                t_txt='<div class="photo_res__item_image" style="background: url(../i/m_postav/small/'+img_+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></div>';
                            }
                            else if(ext=='docx' || ext=='doc'){
                                t_txt='<i class="fa fa-file-word-o"></i>';
                            }
                            else if(ext=='xlsx' || ext=='xls'){
                                t_txt='<i class="fa fa-file-excel-o"></i>';
                            }
                            else if(ext=='zip' || ext=='rar'){
                                t_txt='<i class="fa fa-file-zip-o"></i>';
                            }
                            else if(ext=='txt'){
                                t_txt='<i class="fa fa-file-text-o"></i>';
                            }
                            else if(ext=='psd'){
                                t_txt='<i class="fa fa-file-picture-o"></i>';
                            }
                            else if(ext=='pdf'){
                                t_txt='<i class="fa fa-file-pdf-o"></i>';
                            }
                            else{
                                t_txt='<i class="fa fa-file"></i>';
                            }
                            
                            txt_f+='<li class="photo_res__mess" data-img="'+img_+'"><a href="../i/m_postav/original/'+img_+'" target="_blank">'+t_txt+'</a><div class="m_postav_add__docs_res_div"><input class="m_postav_add__docs_res_text" placeholder="Название файла" value="'+name_+'" /></div><i title="Удалить документ" class="fa fa-remove"></i></li>';
                        }
                        if (txt_f!=''){
                            $('.m_postav_add__docs_res ul').html(txt_f);
                            $('.m_postav_add__docs_res ul').sortable();
                        }
                    }
                    
                    var status_pay='';
                    if (sum_pay>0){
                        if (sum_order>sum_pay){
                            status_pay='частично оплачен';
                            $('.status_order_div').addClass('no_full_pay');//добавлем класс
                        }
                        else if (sum_order==sum_pay){
                            status_pay='оплачен';
                            $('.status_order_div').addClass('full_pay');//добавлем класс
                        }
                        else{
                            status_pay='переплата';
                            $('.status_order_div').addClass('max_full_pay');//добавлем класс
                        }
                    }else{
                        status_pay='не оплачен';
                        $('.status_order_div').addClass('no_pay');//добавлем класс
                    }
                    var status_=data_n.st;
                    if (status_=='Отменен'){
                        $('.status_order_div').addClass('closed');//добавлем класс
                        $('.m_postav_add_form__save').hide();//скрываем кнопку сохранить
                        $('.m_postav_add_form__plus').show();
                    }else{
                        $('.status_order_div').removeClass('closed');//удаяем класс
                        
                        if (status_=='В обработке'){
                            $('.status_order_div').addClass('no_end');//добавлем класс
                        }
                        else if (status_=='Отправлен'){
                            $('.status_order_div').addClass('no_full_end');//добавлем класс
                        }
                        else if (status_=='Доставлен'){
                            $('.status_order_div').addClass('full_end');//добавлем класс
                        }
                        
                        status_='<span class="m_zakaz_current_status">'+status_+'</span> <span class="m_zakaz_current_status_pay">'+status_pay+'</span>';
                        $('.m_postav_add_form__close').show();
                        
                    }
                    
                    if (txt_zakaz!=''){
                        status_+='<span class="m_postav_m_zakaz_info">'+txt_zakaz+'</span>';
                    }
                    
                    
                    $('input[name="control_sum"]').val(data_n.sum);
                    $('input[name="control_num"]').val(data_n.num);
                    
                    //доставка
                    $('input[name="m_dostavka_tracking_number"]').val(data_n.dost['tracking_number']);
                    $('input[name="m_dostavka_fio"]').val(data_n.dost['fio']);
                    $('textarea[name="m_dostavka_adress"]').val(data_n.dost['adress']);
                    $('input[name="m_dostavka_index"]').val(data_n.dost['index_']);
                    $('input[name="m_dostavka_phone"]').val(data_n.dost['phone']);
                    $('input[name="m_dostavka_summa"]').val(data_n.dost['summa']);
                    $('input[name="m_dostavka_data"]').val(data_n.dost['data']);
                    $('.i_tp_span_cur').data('id',data_n.tpi).find('.i_tp_span_cur_txt').text(data_n.tpn);
                    
                    if(data_n.dost['i_city_id']!='' && data_n.dost['i_city_id']!=null){
                        $('select[name="m_dostavka_city_id"]').html('<option></option>').append('<option selected="selected" value="'+data_n.dost['i_city_id']+'">'+data_n.dost['i_city_name']+'</option>').trigger("change");
                    }else{
                        $('select[name="m_dostavka_city_id"] option').removeAttr('selected');
                        $('select[name="m_dostavka_city_id"] option:first').attr('selected','selected').closest('select').trigger("change");
                    }
                    if (data_n.dost['i_tk_id']!='' && data_n.dost['i_tk_id']!=null){
                        $('select[name="i_tk_id"]').val(data_n.dost['i_tk_id']).trigger("change");
                    }else{
                        $('select[name="i_tk_id"] option').removeAttr('selected');
                        $('select[name="i_tk_id"] option:first').attr('selected','selected').closest('select').val(data_n.dost['i_tk_id']).trigger("change");
                    }
                    //ссылки для печати 
                    if ($('.m_postav_all_info_icon ul li').length>0){
                        $('.m_postav_all_info_icon').show();
                        $('.m_postav_all_info_icon ul li a').each(function(){
                            $(this).attr('href',$(this).data('href')+data_['nomer']);
                        });
                    }
                    //Логи
                    var log_txt='';
                    for(var i in data_n.log){
                        var log_txt=data_n.log[i]['l'];
                        var m_log_type=data_n.log[i]['t'];
                        var m_log_id=data_n.log[i]['i'];
                        var dt=data_n.log[i]['d'];
                        var a_admin=data_n.log[i]['a'];
                        add_log(m_log_id,m_log_type,log_txt,a_admin,dt);
                    }
                    
                    chk_i_contr();
                    chk_summ();//Проверяем сумму
                    chk_comments(); //проверяем комментарии
                    chk_pl();//проверка платежей
                    chk_dostavka();
                    
                    //статус заказа
                    $('.status_order_div').html(status_).show();
                    
                    find_mess(data_['nomer'],'',function(){
                        if (typeof callback=='function'){callback(data_n);}
                    });
                    
                    
                    //открываем изменение поступления, если оно закрыто
                    if ($('.m_postav_add_form_open_div:visible').size()>0){
                        $('.m_postav_add_form_open_close_div').trigger('click');
                        
                    }
                    //перемещаемся к изменению
                    $('html,body').animate({scrollTop: $('.m_postav_add_head>h2').offset().top}, {queue: false, easing: 'swing', duration: 300, complete: function(){
		                  }});
                    
                }else{
                    alert_m('Заказ не прочитан!','','error','none');
                }
                
			}
			else{
				alert_m(data,'','error','none');
			}            
		}
	});
}

// save
//СОХРАНЕНИЕ
function m_postav_add_form__save(callback){
    callback=callback || '';
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='m_postav__save';
    data_['nomer']=$('input[name="nomer"]').val();
    data_['a_admin_id']=$('select[name="a_admin"]').val();
    data_['i_contr_id']=$('input[name="i_contr"]').data('i_contr_id');
    data_['i_tp_id']=$('.i_tp_span_cur').data('id');
    
  
    if (data_['i_contr_id']=='' || data_['i_contr_id']==null){err_text+='<p>Укажите контрагента</p>';}
    data_['i_contr_org_id']='';
    if ($('.i_contr_tip_2.active').size()>0 && typeof $('input[name="i_contr"]').data('active_id')!='undefined'){
        data_['i_contr_org_id']=$('input[name="i_contr"]').data('active_id');
    }    
    
    
    data_['data']=$('input[name="date"]').val();
        if (data_['data']=='' || data_['data']=='__.__.____'){err_text+='<p>Укажите дату</p>';}
    data_['data_info']=$('input[name="date_info"]').val();
    data_['comments']=$('textarea[name="comments"]').val();
    data_['project_name']=$('.m_postav_add__project_name').val();
    data_['html_code']=$('.m_postav_add__html_code_hide').html();
    //data_['m_zakaz_id']=$('select[name="m_zakaz_id"]').val();
    
    //товары
    data_['item']=new Object();
    data_['item']['id']=new Object();
    data_['item']['price']=new Object();
    data_['item']['kol']=new Object();
    data_['item']['comments']=new Object();
    data_['item']['barcode']=new Object();
    data_['item']['m_zakaz_id_new']=new Object();
    var i=0;
    var all_summ=0;
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        var th_=$(this);
        data_['item']['id'][i]=th_.data('id');
        data_['item']['kol'][i]=th_.find('.find_tovar__kol_span').text();
        data_['item']['price'][i]=th_.find('.find_tovar__sum_span').text();
        data_['item']['comments'][i]=th_.find('.s_cat_info_tovar>div').html();
        data_['item']['barcode'][i]=th_.find('.s_cat_barcode_tovar_cur').html();
        data_['item']['m_zakaz_id_new'][i]=th_.find('.s_cat_m_zakaz_id_new').data('m_zakaz_id_new');
        if (data_['data_info']!='' && $('.s_cat_barcode_tovar__error').size()>0){
            err_text+='<p>Не все штрх-коды указаны</p>';
        }
        all_summ=all_summ+(data_['item']['kol'][i]*data_['item']['price'][i]);
        i++;
    });
    //доставка
    data_['m_dostavka_tracking_number']=$('input[name="m_dostavka_tracking_number"]').val();
    data_['i_tk_id']=$('select[name="i_tk_id"]').val();
    data_['m_dostavka_fio']=$('input[name="m_dostavka_fio"]').val();
    data_['m_dostavka_city_id']=$('select[name="m_dostavka_city_id"]').val();
    data_['m_dostavka_adress']=$('textarea[name="m_dostavka_adress"]').val();
    data_['m_dostavka_index']=$('input[name="m_dostavka_index"]').val();
    data_['m_dostavka_phone']=$('input[name="m_dostavka_phone"]').val();
    data_['m_dostavka_summa']=$('input[name="m_dostavka_summa"]').val();
    data_['m_dostavka_data']=$('input[name="m_dostavka_data"]').val();
    
    if (count(data_['item']['id'])==0){err_text+='<p>Добавьте в заказ товары или услуги!</p>';}
    
    //Платежи
    data_['pl']=new Object();
    data_['pl']['id']=new Object();
    data_['pl']['data']=new Object();
    data_['pl']['sum']=new Object();
    data_['pl']['schet']=new Object();
    i=0;
    $('.m_postav_add_pl .ttable_tbody_tr').each(function(){
        var th_=$(this);
        data_['pl']['id'][i]=th_.data('id');
        data_['pl']['data'][i]=th_.find('.ttable_tbody_tr_td:first .val').text();
        data_['pl']['sum'][i]=(th_.find('.pl_tr__sum').text()).replace(' ','');
        data_['pl']['schet'][i]=$('.pl_schet option:contains('+th_.find('.pl_schet_name').text()+')').val();
        i++;
    });
    
    //Файлы
    data_['fl']=new Object();
    data_['fl']['f']=new Object();
    data_['fl']['c']=new Object();
    i=0;
    $('.m_postav_add__docs_res ul li').each(function(){
        var th_=$(this);
        data_['fl']['f'][i]=th_.data('img');
        data_['fl']['c'][i]=th_.find('input').val();
        i++;
    });
    
    
    //логи
    data_['log_t']='';
    data_['log_l']='';
    data_['log_d']='';
    $('.m_log_item[data-id=""]').each(function(){
        var th_log=$(this);
        if (data_['log_t']!=''){data_['log_t']+='||';}
        if (data_['log_l']!=''){data_['log_l']+='||';}
        if (data_['log_d']!=''){data_['log_d']+='||';}
        data_['log_t']+=th_log.find('.m_log_item_t').text();
        data_['log_l']+=th_log.find('.m_log_item_l').text();
        data_['log_d']+=th_log.find('.m_log_item_d').text();
    });
    
    data_['control_sum']=$('input[name="control_sum"]').val();
    data_['control_num']=$('input[name="control_num"]').val();
    if (data_['control_sum']-0>0){
        if (data_['control_sum']-0!=all_summ-0){
            err_text+='<p>Сумма заказа <strong>'+all_summ+'</strong> не соответствует контрольной сумме: '+data_['control_sum']+'</p>';
        }
        
    }
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
                if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    
                    //Сумма на счетах  -отображение в шаблоне
                    if ($('.m_platezi_all_info').size()>0 && typeof get_sum_info_from_shablon=='function'){get_sum_info_from_shablon(); }
    
    	            postav_load(data_n.nomer,function(){
    	               if (typeof callback=='function'){callback(data_n);}
    	            });
                    
                    
                    
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}
//Очистка формы добавления
function add_zakaz_form_clear(){
    
    var dt_ = new Date();
    var h=dt_.getHours();
    var m=dt_.getMinutes();
    var s=dt_.getSeconds();
    
    $('select[name="a_admin"] option, .pl_schet option').removeAttr('selected');
        $('.pl_schet').change();
    
    $('input[name="i_contr"]').val('').data('i_contr_id','').data('i_contr_name','').data('i_contr_phone','').data('i_contr_email','').data('i_contr_org_id','').data('i_contr_org_name','').data('i_contr_org_cnt','').data('i_contr_org_email','').data('i_contr_org_phone','').data('active_id','').data('i_contr_org_cnt','');
    
    $('input[name="date"], .pl_data').val(dt_.toLocaleDateString()+' '+PrefInt(h,2)+':'+PrefInt(m,2));
    
    $('input[name="date_info"]').val('');
    $('input[name="control_num"]').val('');
    $('input[name="control_sum"]').val('');
    $('input[name="project_name"]').val('');
    $('textarea[name="comments"]').val('');
    $('.pl_price').val('0');
    $('.find_tovar_res__tbl .ttable_tbody_tr').detach();
    $('.find_usluga_res__tbl .ttable_tbody_tr').detach();
    $('.m_postav_add_pl .ttable_tbody_tr').detach();
    $('.m_postav_add_pl .ttable_tbody_tr').detach();
    $('.m_postav_add_head h2').html('Добавление поступления <input type="hidden" value="" name="nomer">');
    $('.m_postav_all_mess ul, .m_postav_mess_files ul').html('');
    $('.m_postav_add__docs_res ul').html('');
    $('.m_postav_all_info_icon ul li a').attr('href','');
    $('.m_postav_add__html_code_hide').html('');
    CKEDITOR.instances.m_postav_mess_text.setData('');
    

    $('.m_postav_add_form__plus').hide();//убираем кнопку открытия заказа
    $('.m_postav_add_form__close').hide();//убираем кнопку отмены заказа
    $('.m_postav_add_form__save').show();//открываем кнопку сохранить
    
    //$('select[name="m_zakaz_id"]').val(null).trigger("change");
    
    //доставка
    $('.m_postav__dostavka_div input, .m_postav__dostavka_div textarea').val('');
    $('.m_postav__dostavka_div select').val('');
    $('.m_postav__dostavka_div select option').removeAttr('selected');
    $('.m_postav_all_info_icon').hide();
    $('select[name="a_admin"]').select2({'width':'100%',minimumResultsForSearch: 20});
    change_worker_tp();
    
    //удаляем классы
    $('.status_order_div').removeClass('no_full_pay').removeClass('no_pay').removeClass('max_full_pay').removeClass('full_pay')
    .removeClass('no_end').removeClass('full_end').removeClass('no_full_end').removeClass('closed');
    
    $('.status_order_div').hide();//убираем статус заказа
    //Формирование ссылки HISTORY
    history_url='?inc=m_postav';
    history_name='Поступление товаров';
    History.replaceState({state:3}, history_name, history_url);
    
    chk_i_contr();
    chk_summ();//Проверяем сумму
    chk_comments(); //проверяем комментарии
    chk_pl();//проверка платежей
}

//проверяем товары у данного контрагента по заказам
function check_items_from_i_contr(i_contr_id){
    var err_text='';
    var data_=new Object();//$('.****').serializeObject();
    data_['_t']='check_items_from_i_contr';
    data_['i_contr_id']=i_contr_id;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof data_n['m_zakaz_s_cat_m_zakaz_id']=='object'){
    	               var res_arr=new Object();
                       var i=0;
    	               for(var m_zakaz_s_cat_id in data_n['m_zakaz_s_cat_m_zakaz_id']){
    	                   var m_zakaz_id=data_n['m_zakaz_s_cat_m_zakaz_id'][m_zakaz_s_cat_id];
                           if (typeof res_arr[m_zakaz_id]!='object') {res_arr[m_zakaz_id]=new Object();}
    	                   res_arr[m_zakaz_id][m_zakaz_s_cat_id]=data_n['m_zakaz_s_cat_s_cat_id'][m_zakaz_s_cat_id];
                           
                           i++;
    	               }
                       var txt='';
                       for(var m_zakaz_id in res_arr){
                        txt+='<div class="find_tovar_res_help_item" data-id="'+m_zakaz_id+'">';
                        txt+='<h2><a class="find_tovar_res_help_zakaz_name" href="?inc=m_zakaz&nomer='+m_zakaz_id+'">'+data_n['m_zakaz_s_cat_m_zakaz_txt'][m_zakaz_id]+'</a></h2>';
                        
                        for(var m_zakaz_s_cat_id in res_arr[m_zakaz_id]){
                            var s_cat_id=res_arr[m_zakaz_id][m_zakaz_s_cat_id];
                            var key_=array_search(s_cat_id,data_n['s_cat']['id']);
                            var s_cat_name=data_n['s_cat']['name'][key_];
                            var s_cat_prop=data_n['s_cat']['prop'][key_];
                            var s_cat_price=data_n['s_cat']['price'][key_];
                            txt+='<p>'+s_cat_id+'. <a class="find_tovar_res_help_item_add" data-id="'+s_cat_id+'" data-kolvo="'+data_n['m_zakaz_s_cat_kolvo'][m_zakaz_s_cat_id]+'" href="?inc=s_cat&nomer='+s_cat_id+'">'+s_cat_name+'</a> <span class="find_tovar_res_help_prop">'+s_cat_prop+'</span> <span class="find_tovar_res_help_kolvo">'+data_n['m_zakaz_s_cat_kolvo'][m_zakaz_s_cat_id]+'</span> X <span class="find_tovar_res_help_price">'+s_cat_price+' руб.</span></p>';
                        }
                        txt+='</div>';
                       }
                       
                       $('.find_tovar_res_help').html(txt);
                       
                    }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}


//Добавляем товар в поступление по id
function s_cat_add_from_id(id_,kol,m_zakaz_id_new,m_zakaz_txt_new){
    id_= id_ || '';
    kol= kol || 1;
    m_zakaz_id_new= m_zakaz_id_new || '';
    m_zakaz_txt_new= m_zakaz_txt_new || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='s_cat_add_from_id';
    
    var cnt_arr=new Array();
    var id_txt='';
    
    //получаем количество
    var id_arr=new Array();
    id_=id_+'';
    if ((id_.split(',').length - 1)>0){
       id_arr=id_.split(',');
    }else{
        id_arr[0]=id_;
    }
    for(var i in id_arr){
        var id=id_arr[i];
        if ((id.split(':').length - 1)>0){
            var id_arr2=id.split(':');
            cnt_arr[id_arr2[0]]=id_arr2[1];
            if (id_txt!=''){id_txt+=',';}
            id_txt+=id_arr2[0];
        }
        else{
            if (id_txt!=''){id_txt+=',';}
            id_txt+=id;
        }
    }
    
    data_['id_']=id_txt;
    
        if (id_==''){err_text+='<p>Не определен id для добавления</p>';}
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    //alert(typeof data_n['i']);
    	            if (typeof data_n['i']=='object'){
    	               for(var i in data_n['i']){
    	                    s_cat_arr['id']=data_n['i'][i]['id'];
                            s_cat_arr['name']=data_n['i'][i]['value'];
                            s_cat_arr['price']=0;//data_n['i'][i]['p']
                            s_cat_arr['tip']=data_n['i'][i]['t'];
                            s_cat_arr['img']=data_n['i'][i]['img'];
                            s_cat_arr['pr']=data_n['i'][i]['pr'];
                            
                            var cnt_=kol;
                            if (typeof cnt_arr[s_cat_arr['id']]!='undefined'){
                                cnt_=cnt_arr[s_cat_arr['id']];
                            }
                            s_cat_add(s_cat_arr['id'],s_cat_arr['name'],cnt_,s_cat_arr['price'],s_cat_arr['tip'],'','',s_cat_arr['img'],'',s_cat_arr['pr'],m_zakaz_id_new,m_zakaz_txt_new);
            
            
                            
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


//Добавление  товара
function s_cat_add(id,name,kol,price,s_cat_tip,comm,barcode,img,sal_kol,prop_val,m_zakaz_id_new,m_zakaz_txt_new){
    barcode=barcode || '';
    prop_val=prop_val || '';
    comm=comm || '';
    img=img || '';
    m_zakaz_id_new=m_zakaz_id_new || '';
    m_zakaz_txt_new=m_zakaz_txt_new || '';

    sal_kol=sal_kol || 0;
        sal_kol=sal_kol-0+1;
        //alert(sal_kol);
    if (id!=''){
        
        if (s_cat_tip=='1' || s_cat_tip=='Товар'){
            var nom_=$('.find_tovar_res__tbl .ttable_tbody_tr').size()-0+1;
            var tip_name='товара';
            var tip_name2='товар';
        }
        else{
            var nom_=$('.find_usluga_res__tbl .ttable_tbody_tr').size()-0+1;
            var tip_name='услуги';
            var tip_name2='услугу';
        }
        
        var txt='';
        txt+='<div class="ttable_tbody_tr" data-id="'+id+'" data-nom="'+nom_+'" data-img="'+_IN(img)+'">';
            txt+='<div class="ttable_tbody_tr_td"><span class="for_mobile">№</span>';
                txt+='<span class="val">'+nom_+'</span>';
            txt+='</div>';

            txt+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Название</span><span class="val">';
                txt+='<span class="s_cat_name_td">'+name+'</span> <span class="s_cat_name_prop_val">'+prop_val+'</span>';
                if (img!=''){
                    txt+='<a title="'+_IN(name)+'" href="../i/s_cat/original/'+_IN(img)+'" target="_blank" class="zoom"><i class="fa fa-image thumbnail"><span>'+id+'. '+name+'';
                    txt+='<img src="../i/s_cat/small/'+_IN(img)+'" />';
                    txt+='</span></i></a>';
                }
            txt+='</span></div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar__kol"><span class="for_mobile">Количество</span><span class="val">';
                txt+='<span class="find_tovar__kol_span" data-min="'+_IN(sal_kol)+'">'+kol+'</span>';
            txt+='</span></div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar__sum"><span class="for_mobile">Сумма</span><span class="val">';
                txt+='<span class="find_tovar__sum_span">'+price+'</span>';
            txt+='</span></div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar__itogo"><span class="for_mobile">Итого</span><span class="val">';
                txt+=price;
            txt+='</span></div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar_res__com_td"><span class="for_mobile">Функции</span><span class="val">';
                txt+='<i class="fa fa-barcode s_cat_barcode_tovar" title="Ввести штрих-коды '+tip_name2+'"><div style="display:none;" class="s_cat_barcode_tovar_cur">'+barcode+'</div></i>';
                txt+='<i class="fa fa-file-word-o s_cat_info_tovar" title="Описание '+tip_name+'"><div class="s_cat_info_hidden">'+comm+'</div></i>';
                txt+='<i class="fa fa-edit s_cat_change_tovar" title="Изменить '+tip_name2+'"></i>';
                var cl_order='';
                if (m_zakaz_id_new-0>0){
                    cl_order='active';
                }
                txt+='<i class="fa fa-reorder s_cat_m_zakaz_id_new '+cl_order+'" title="К заказу" data-m_zakaz_id_new="'+m_zakaz_id_new+'" data-m_zakaz_txt_new="'+m_zakaz_txt_new+'"></i>';
                txt+='<i class="fa fa-remove s_cat_del_tovar" title="Удалить '+tip_name2+'"></i>';
                
            txt+='</span></div>';
        txt+='</div>';
        if (s_cat_tip=='1' || s_cat_tip=='Товар'){
            $('.find_tovar_res__tbl .ttable_tbody').append(txt);
        }else{
            $('.find_usluga_res__tbl .ttable_tbody').append(txt);
        }
        $('.s_cat_items_find').val('');
        
        $('.zoom').fancybox({prevEffect:'none',nextEffect:'none',helpers:{title:{type:'outside'},thumbs:{width:50,height:50}}}); //фото
    
        
        chk_summ();//Проверяем сумму
        chk_comments();
        chk_barcode();//проверка штрих-кодов
        chk_dostav();//проверка доставки
    }
}


//Добавление организации
function i_contr_org_form(nomer,val,callback){
    callback=callback || '';//функция
    var th_=$(this);
    var data_=new Object();
    data_['_t']='i_contr_org_form';
    data_['nomer'] = nomer || '';
    data_['val'] = val || '';
	loading(1);
	$.ajax({
		"type": "POST",
		"url": "ajax/m_postav.php",
		"dataType": "text",
		"data":data_,
		"success":function(data,textStatus){
			loading(0);
	        alert_m(data,function(){
	           
	        },'i_contr_org_form','none');
            $('input[name=i_contr_org_phone]').integer_().mask("0(000)000-00-00",{clearIfNotMatch: true});
           $('.modal_i_contr_org_form .i_contr_org_mini_form_save').click(function(){
                i_contr_org_form_save(callback);
            });
            
         }
   });
}

//сохранение организации
function i_contr_org_form_save(callback){
    callback=callback || '';//функция
        if ($('.modal_i_contr_org_form').size()>0){
        var err_text='';
        var data_=$('.modal_i_contr_org_form form').serializeObject();
            $('.modal_i_contr_org_form tr.mandat').each(function(){
                var val=$(this).find('input[name!=""],select[name!=""],textarea[name!=""]').val();
                if (val==''){
                    var tx=$(this).find('td:first').text();
                    err_text+='<p>Не заполнено поле [<strong>'+tx.trim()+'</strong>]</p>';
                }
            });
        data_['_t']='i_contr_org_form_save';
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/m_postav.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
                    loading(0);
        	        if (is_json(data)==true){
        	            var data_n=JSON.parse(data);
                     
        	            if (typeof callback=='function'){
        	               callback(data_n);
        	            }
                        $('.m_postav_item__i_contr_org').arcticmodal('close');
        			}
        			else{
        				alert_m(data,'','error','none');
        			}
        		}
        	});
        }
    }
}

function chk_num(){
    var i=1;
    $('.find_usluga_res__tbl .ttable_tbody_tr').each(function(){
        $(this).find('td:first').html(i);
        i++;
    });
    i=1;
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        $(this).find('td:first').html(i);
        i++;
    });
}

//Форма добавления товара
function s_cat_add_change_form(val_,nomer,callback){
    var val_=val_ || '';
    var nomer=nomer || '';
    var callback=callback || '';
    
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    
    data_['tip']='1';
    if ($('.ui-state-active').find('a[href="#tabs-2"]').size()==1){
        data_['tip']='2';
    }
    
    data_['_t']='m_postav__s_cat_add';
    data_['val']=val_;
    data_['nomer']=nomer;
    data_['s_struktura_id']=$('select[name="s_struktura_s_cat_select"]').val();
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    			alert_m(data,'','s_cat_form','none');
                $('select[name="s_struktura_id"]').select2({'width':'100%'});
                $('select[name="tip"]').select2({'width':'100%'});
                $('form[name="m_postav_s_cat_add_form"] input[name="price"]').float_().spinner({min:0});
                $('form[name="m_postav_s_cat_add_form"] input[name="name"]').focus();
                $('.m_postav_s_cat_add_form_save').click(function(){
                    s_cat_add_change_form_save(callback);
                });
    		}
    	});
    }
    chk_num();//Проверяем номера
}
//Форма сохранения товара
function s_cat_add_change_form_save(callback){
    var callback=callback || '';
    var err_text='';
    var th_=$(this);
    var data_=$('form[name="m_postav_s_cat_add_form"]').serializeObject();
    data_['_t']='m_postav__s_cat_save';
    $('.m_postav_s_cat_add_form .ttable_tbody_tr.mandat').each(function(){
            var val=$(this).find('input[name!=""],select[name!=""],textarea[name!=""]').val();
            if (val==''){
                var tx=$(this).find('td:first').text();
                err_text+='<p>Не заполнено поле [<strong>'+tx.trim()+'</strong>]</p>';
            }
        });
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    		  loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    $('.m_postav_s_cat_add_form').arcticmodal('close');
    	            if (typeof callback=='function'){callback(data_n);}
                    
                    //логирование
                    var price_='';if (data_n.price!=''){price_=', цена: '+data_n.price;}
                    if ($('.m_postav_s_cat_add_form input[name="nomer"]').val()==''){
                        add_log('','Добавление товара',data_n.name+' #'+data_n.id+price_);
                    }
                    else{
                        add_log('Изменение товара',data_n.name+' #'+data_n.id+price_);
                    }
                    
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}
//Добавление платежа
function pl_add(data,summa,schet_id,pl_id,pl_tip){
    pl_id=pl_id || '';
    pl_tip=pl_tip || 'Дебет';
    summa=str_replace(' ','',summa);
    var znak='';
    var print_txt='';
    if (pl_tip=='Кредит'){
        //znak='-';
        print_txt='<span class="m_postav_print_pko"><i title="Распечатать ПКО" class="fa fa-print"></i></span>';
    }
    else if(pl_tip=='Дебет'){
        print_txt='<span class="m_postav_print_rko"><i title="Распечатать РКО" class="fa fa-print"></i></span>';
    }
    
    var schet_name= $('.pl_schet option[value="'+schet_id+'"]').text();
    
    if (pl_id==''){
      add_log('','Добавление платежа',znak+number_format(str_replace(' ','',summa),0,'.',' ')+' ('+schet_name+') '+data);
    }
    
    $('.m_postav_add_pl .ttable_tbody').append('<div class="ttable_tbody_tr" data-id="'+pl_id+'"><div class="ttable_tbody_tr_td"><span class="for_mobile">Дата</span><span class="val">'+data+'</span></div>'
    +'<div class="ttable_tbody_tr_td"><span class="for_mobile">Сумма</span><span class="pl_tr__sum val">'+znak+number_format(str_replace(' ','',summa),0,'.',' ')+'</span></div>'
    +'<div class="ttable_tbody_tr_td"><span class="for_mobile">Счет</span><span class="pl_schet_name val">'+schet_name+'</span></div>'
    +'<div class="ttable_tbody_tr_td m_postav_add_pl__com"><span class="for_mobile">Функции</span><span class="val">'
    +print_txt
    +'<i title="Удалить платеж" class="fa fa-remove"></i></span></div></div>');
      
}


//Добавление / удаление / изменение организации на форме контрагента
function i_contr_org_in_i_cont_form(id,tip,name,inn){
    id=id || '';
    tip=tip || 'del';
    name=name || '';
    inn=inn || '';
    if (id!=''){
        if (tip=='del'){
            $('.i_contr_org_current div[data-id="'+id+'"]').detach();
        }
        else if(tip=='add'){
            var txt='<input type="hidden" name="i_contr_org_id[]" value="'+id+'" /><span class="i_contr_org_mini_edit"><i class="fa fa-building"></i><span>'+name+'</span> / <span>'+inn+'</span></span><span class="i_contr_org_mini_change"><span class="fa fa-edit" title="Изменить организацию"></span></span><span class="i_contr_org_mini_remove"><span class="fa fa-remove" title="Удалить организацию"></span></span>';
            if ($('.i_contr_org_current div[data-id="'+id+'"]').size()>0){//изменение
                $('.i_contr_org_current div[data-id="'+id+'"]').html(txt);
            }else{//добавление
                $('.i_contr_org_current').append('<div data-id="'+id+'">'+txt+'</div>');
                $('.i_contr_org_current').sortable();
            }
        }
        else{
            alert_m('Не определен tip!','','error','none');
        }
    }
}

//Проверка покупателя 
function chk_dostavka(){
    var status_='<i class="fa fa-minus" title="Доставка не требуется"></i>';
    var summ_='';
    if ($('select[name="i_tk_id"]').val()!=''){//заказана доставка
        status_='<i class="fa fa-exclamation" title="Заказана доставка"></i>';
        summ_=$('input[name="m_dostavka_summa"]').val()+' <i class="fa fa-rouble"></i>';
        
        if ($('select[name="m_dostavka_tracking_number"]').val()!=''){
            status_='<i class="fa fa-external-link" title="Заказ отправлен"></i>';
        }
    }
    $('.m_postav_add_items__dost_st').html(status_);
    $('.m_postav_add_work__dost_sum').html(summ_);
}
//Проверка покупателя  //2018-05-17 toowin86
function chk_i_contr(){
    $('.m_postav_i_contr_tip>span').hide();
    
    var i_contr_id=$('input[name="i_contr"]').data('i_contr_id');
    var active_id=$('input[name="i_contr"]').data('active_id');
    var i_contr_org_cnt=$('input[name="i_contr"]').data('i_contr_org_cnt');

    if (typeof i_contr_id!='undefined' && i_contr_id!=''){
        $('.m_postav_i_contr_tip .i_contr_tip_1').show();
        if (typeof $('input[name="i_contr"]').data('i_contr_org_id0')!='undefined'){//есть организации
            $('.m_postav_i_contr_tip .i_contr_tip_2').show();
        }
        $('input[name="i_contr"]').removeClass('input_error').addClass('input_ok');
        $('.i_contr_com').html('<span class="fa fa-plus" title="Добавить нового контрагента"></span><span class="fa fa-edit" title="Изменить контрагента"></span>');
        
        //Заполняем телефон и имя
        var i_contr_tip=$('.m_postav_i_contr_tip>span.active').data('id');
        $('.i_contr_add_form_email').text('');
        $('.i_contr_add_form_phone').text('');
        $('.cnt_org_in_select').text('-');
        if (i_contr_tip=='1'){//физ-лицо
            $('input[name="i_contr"]').val($('input[name="i_contr"]').data('i_contr_name'));
            var phone_=$('input[name="i_contr"]').data('i_contr_phone');
            var email_=$('input[name="i_contr"]').data('i_contr_email');
            if (typeof phone_!='undefined' && phone_!=''){
                $('.i_contr_add_form_phone').html('<i class="fa fa-phone"></i> <a href="tel:'+phone_+'">'+phone_+'</a>');
            }
            if (typeof email_!='undefined' && email_!=''){
                $('.i_contr_add_form_email').html('<i class="fa fa-envelope-o"></i> <a href="mailto:'+email_+'?subject='+$('input[name="nomer"]').val()+'" target="_blank">'+email_+'</a>');
            }
            
        }
        else if (i_contr_tip=='2'){//организация
            if (typeof i_contr_org_cnt!='undefined'){
                 for(var i=0;i<i_contr_org_cnt;i++){
                    if ($('input[name="i_contr"]').data('i_contr_org_id'+i)==active_id){
                        
                        $('.cnt_org_in_select').text(i+1);
                        $('input[name="i_contr"]').val($('input[name="i_contr"]').data('i_contr_org_name'+i));
                        var phone_=$('input[name="i_contr"]').data('i_contr_org_phone'+i);
                        var email_=$('input[name="i_contr"]').data('i_contr_org_email'+i);
                        if (typeof phone_!='undefined' && phone_!=''){
                            $('.i_contr_add_form_phone').html('<i class="fa fa-phone"></i> <a href="tel:'+phone_+'">'+phone_+'</a>');
                        }
                        if (typeof email_!='undefined' && email_!=''){
                            $('.i_contr_add_form_email').html('<i class="fa fa-envelope-o"></i> <a href="mailto:'+email_+'?subject='+$('input[name="nomer"]').val()+'" target="_blank">'+email_+'</a>');
                        }
                    }
                 }
            }
        }
        check_items_from_i_contr(i_contr_id);
    }else{
        $('input[name="i_contr"]').removeClass('input_ok').addClass('input_error');
        $('.i_contr_com').html('<span class="fa fa-plus" title="Добавить нового контрагента"></span>');
    }
    
    
}//end 2018-05-17 toowin86

//Форма контрагента
function i_contr_form(nomer,val,callback){
    val=val || '';
    callback=callback || '';//функция
    var th_=$(this);
    var data_=new Object();
    data_['_t']='i_contr_form';
    data_['nomer'] = nomer || '';
	loading(1);
	$.ajax({
		"type": "POST",
		"url": "ajax/m_postav.php",
		"dataType": "text",
		"data":data_,
		"success":function(data,textStatus){
			loading(0);
	        alert_m(data,function(){
	           $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').autocomplete("destroy");
	        },'i_contr_form','none','');
	           $('.modal_i_contr_form .i_contr_mini_form_save').click(function(){
                    i_contr_form_save(callback);
                });
                if (val!=''){
                    $('.modal_i_contr_form input[name="name"]').val(val);
                }
                $('.modal_i_contr_form select[name="i_contr_org_tip_director"]').select2({'width':'100%'});
                $('.modal_i_contr_form select[name="i_contr_org_na_osnovanii"]').select2({'width':'100%'});
                
                $('.modal_i_contr_form .email').emailautocomplete();
                $('.modal_i_contr_form .phone').integer_().mask("0(000)000-00-00",{clearIfNotMatch: true});
                $('.modal_i_contr_form input[name="adress"]').keyup();
                
                
                $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').keyup(function(){
                    i_contr_arr['id']='';
                });
                $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').dblclick(function(){
                    $(this).autocomplete( "search", $(this).val() );
                });
                $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').autocomplete({
                        minLength: 0,
                        appendTo: ".m_postav_add_form_div",
                        source: function(request, response){
                             request['_t']='i_contr_org_autocomplete';
                             if (typeof jqxhr!='undefined'){jqxhr.abort();}
                             jqxhr = $.ajax({
                            	"type": "POST",
                            	"url": "ajax/m_postav.php",
                            	"dataType": "text",
                            	"data":request,
                            	"success":function(data,textStatus){
                            	   if (is_json(data)==true){
                                	       var data_n=JSON.parse(data);
                                           response(data_n);
                                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                                            
                                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                               var txt='<p>'+data_n[i].value+' (ИНН:'+data_n[i].i+')</p>';
                                               $(this).html(txt);
                                            });
                                            
                                    }else{
                                        alert_m(data,'','error','none');
                                    }
                            	}
                            });
                        },
                        select: function( event, ui ) {
                            i_contr_arr['id']=ui.item.id;
                            i_contr_arr['name']=ui.item.value;
                            i_contr_arr['i']=ui.item.i;
                            i_contr_arr['k']=ui.item.k;
                            i_contr_arr['o']=ui.item.o;
                            i_contr_arr['bi']=ui.item.bi;
                            i_contr_arr['ba']=ui.item.ba;
                            i_contr_arr['sc']=ui.item.sc;
                            i_contr_arr['ks']=ui.item.ks;
                            i_contr_arr['p']=ui.item.p;
                            i_contr_arr['u']=ui.item.u;
                            i_contr_arr['td']=ui.item.td;
                            i_contr_arr['fd']=ui.item.fd;
                            i_contr_arr['no']=ui.item.no;
                        },
                        close: function( event, ui ) {
                           
                            if (i_contr_arr['id']!=''){
                                i_contr_org_in_i_cont_form(i_contr_arr['id'],'add',i_contr_arr['name'],i_contr_arr['i']);
                            }
                        }
                    });
                
                $('.modal_i_contr_form select[name="i_contr_i_contr_id"]').select2({'width':'100%', 
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
                  }});
                  
                $('.modal_i_contr_form select[name="i_reklama_id"]').select2({'width':'100%'}).change(function(){
                    var cnt_=$('.modal_i_contr_form select[name="i_reklama_id"] option:selected').data('cnt');$('.modal_i_contr_form .i_reklama_id_info').html(end_word(cnt_,'Пришло '+cnt_+' клиентов','Пришел '+cnt_+' клиент','Пришло '+cnt_+' клиента'));
                    
                    if ($(this).val()=='3'){
                        $('.modal_i_contr_form .i_contr_i_contr_div').show();
                    }
                }).change();
               
		}
	});
    
}


//Сохранение контрагента
function i_contr_form_save(callback){
    callback=callback || '';//функция
    if ($('.modal_i_contr_form').size()>0){
        var err_text='';
        var data_=$('.modal_i_contr_form form').serializeObject();
            $('.modal_i_contr_form .ttable_tbody_tr.mandat').each(function(){
                var val=$(this).find('input[name!=""],select[name!=""],textarea[name!=""]').val();
                if (val==''){
                    var tx=$(this).find('.ttable_tbody_tr_td:first').text();
                    err_text+='<p>Не заполнено поле [<strong>'+tx.trim()+'</strong>]</p>';
                }
            });
        data_['_t']='i_contr_form_save';
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
            loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/m_zakaz.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
                    loading(0);
        	        if (is_json(data)==true){
        	            var data_n=JSON.parse(data);
                        $('.i_contr_mini_form').arcticmodal('close');
                        
                        
                        //логирование
                        var log_txt='';
                        
                        if (data_n.i_contr['name']!=''){log_txt+=': '+data_n.i_contr['name'];}
                        if (data_n.i_contr['id']!=''){log_txt+=' ('+data_n.i_contr['id']+')';}
                        if (data_n.i_contr['email']!=''){log_txt+=', email: '+data_n.email;}
                        if (data_n.i_contr['phone']!=''){log_txt+=', телефон: '+data_n.phone;}
                        
                        if ($('.i_contr_mini_form input[name="nomer"]').val()==''){ add_log('','Добавление контрагента',log_txt);}
                        else{add_log('','Изменение контрагента',log_txt);}
                       
                        i_contr_select(data_n.i_contr,data_n.i_contr_org,data_n.active,function(){
                            if (typeof callback=='function'){
                               callback(data_n);
            	            }
                        });
        	            
        			}
        			else{
        			     
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    }
}

//проверка комментариев 
function chk_comments(){
   $('.find_tovar_res__tbl .ttable_tbody_tr, .find_usluga_res__tbl .ttable_tbody_tr').each(function(){
        
        //комментарии
        if ($(this).find('.s_cat_info_hidden').html()==''){
            $(this).find('.s_cat_info_hidden').closest('i').css({'color':'#f00'});
        }else{
            $(this).find('.s_cat_info_hidden').closest('i').css({'color':'#090'});
        }
   });
}
//Проверка суммы
function chk_summ(){
    var sum=0;
    var kol=0;
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        var k=$(this).find('.find_tovar__kol_span').text();k=k.replace(' ','');
        kol=(kol-0)+(k-0);
        var s=$(this).find('.find_tovar__sum_span').text();s=s.replace(' ','');
      
        var itogo=(k*s)-0;
        sum=sum-0+itogo-0;
        $(this).find('.find_tovar__itogo .val').text(number_format(itogo,0,'.',' '));
      
    });
    $('.find_tovar_res_all_info .find_tovar_all_kol').text(kol);
        $('.m_postav_add_items__all_tovar').text(kol);
        $('.m_postav_add_items__all_sum').html('<span>'+sum+'</span> <i class="fa fa-rouble"></i>');
    $('.find_tovar_res_all_info .find_tovar_all_sum').text(number_format(sum,0,'.',' '));
    
    
    chk_all_opl();
}

//Проверка платежей
function chk_pl(){
    var pl_summ=0;
    $('.m_postav_add_pl .ttable_tbody_tr').each(function(){
        var sum=(($(this).find('.pl_tr__sum').text()).replace(' ',''))-0;
        pl_summ=pl_summ-0+sum-0;
    });
    $('.pl_all_kol, .m_postav_add_items__pl_kol').text($('.m_postav_add_pl .ttable_tbody_tr').size());
    $('.pl_all_sum').text(number_format(pl_summ,0,'.',' '));
    $('.m_postav_add_work__pl_sum').html('<span>'+pl_summ+'</span> <i class="fa fa-rouble"></i>');
    $('.pl_end_word').text(end_word(pl_summ,'ей','','а'));
    chk_all_opl();
}
//Проверка необходимости оплаты
function chk_all_opl(){
    var pl_=$('.m_postav_add_work__pl_sum span').text()-0;
    var sum_w=$('.m_postav_add_work__all_sum span').text()-0;
    var sum_i=$('.m_postav_add_items__all_sum span').text()-0;
    if (((sum_w-0)+(sum_i-0))>(pl_-0)){
        var minus=((sum_w-0)+(sum_i-0))-(pl_-0);
        $('.pl_price').val(minus);
    }else{
        $('.pl_price').val('0');
    }
    
}

//*****************************************************************************************************
//ПОИСК ПО СООБЩЕНИЯМ
function find_mess(id,limit,callback){
    id=id || '';
    limit=limit || '';
    callback=callback || '';
        if (limit==''){$('.m_postav_all_mess ul').html('');}
    var err_text='';
    var data_=new Object();
    data_['_t']='m_postav__mess_find';
    data_['id']=id;
    data_['limit']=limit;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt='';
                    if (typeof data_n.m['i']!='undefined'){
                        var m_arr=data_n.m;
                        
                        var a_admin_id=data_n.a_admin_id;
                        var cl_='';var ui_='';var un_='';var ue_='';var up_='';var ut_='';
                        for(var i in m_arr.i){
                            cl_='other_user';
                            
                            if (typeof m_arr.ai[i] !='undefined'){//админ 
                                if (a_admin_id==m_arr.ai[i]){cl_='this_user';}
                                ui_=m_arr.ai[i];
                                un_=m_arr.an[i];
                                ue_=m_arr.ae[i];
                                up_='';if (m_arr.ap[i]!=''){up_='<img src="../i/a_admin/original/'+m_arr.ap[i]+'" />';}
                                
                                ut_='admin ';
                            }
                            else if (typeof m_arr.ii[i] !='undefined'){//контрагент
                                ui_=m_arr.ii[i];
                                un_=m_arr.in_[i];
                                ue_=m_arr.ie[i];
                                up_='';if (m_arr.ap[i]!=''){up_='<img src="../i/i_contr/original/'+m_arr.ip[i]+'" />';}
                                ut_='user ';
                            }
                            
                            txt+='<li data-id="'+m_arr.i[i]+'" class="'+ut_+cl_+'" data-tip="'+ut_+'" data-user_id="'+ui_+'">';
                            txt+='<div class="m_postav_mess__user">';
                                txt+=up_;
                                txt+='<h3>'+un_+'</h3>';
                                txt+='<div>'+ue_+'</div>';
                                txt+='<p>'+m_arr.d[i]+'</p>';
                            txt+='</div>';
                            txt+='<div class="m_postav_mess__message">';
                                txt+='<p>'+m_arr.m[i]+'</p>';
                                txt+='<div class="m_postav_mess__files">';
                                
                                var file_=m_arr.f[i];
                                var txt_file='';
                                var path_='../i/m_dialog/original/';
                                for(var j in file_.n){
                                    ext=(file_.n[j]).replace(reg_ext, "");
                                    if (ext=='jpg' || ext=='jpeg' || ext=='png' || ext=='gif'){
                                        txt_file+='<li><a target="_blank" href="'+path_+file_.n[j]+'"><img src="../i/m_dialog/small/'+file_.n[j]+'" /></a><div>'+file_.t[j]+'</div></li>';
                                    }else{
                                        txt_file+='<li><a target="_blank" href="'+path_+file_.n[j]+'"><i class="fa fa-file-o"></i></a><div>'+file_.t[j]+'</div></li>';
                                    }
                                    
                                }
                                if (txt_file!=''){
                                    txt+='<ul>'+txt_file+'</ul><div style="clear:both;"></div>';
                                }
                                
                                txt+='</div>';
                                if (a_admin_id==m_arr.ai[i]){
                                    txt+='<div class="command"><i class="fa fa-remove"></i></div>';
                                }
                            txt+='</div><div style="clear:both;"></div>';
                            txt+='</li>';
                        }
                        $('.m_postav_all_mess ul').append(txt);
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
//*****************************************************************************************************
//ОТПРАВКА СООБЩЕНИЯ ПО ЗАКАЗУ
function send_mess(id,callback){
    id=id || '';
    callback=callback || '';
    if (id!=''){
        var err_text='';
        var data_=new Object();
        data_['_t']='send_mess';
        data_['id']=id;
        data_['text']=CKEDITOR.instances.m_postav_mess_text.getData();//$('.m_postav_mess_text').val();
     
        var i=0;
        data_['f']=new Object();
            data_['f']['i']=new Object();
            data_['f']['t']=new Object();
        $('li.photo_res__mess').each(function(){
            var th_=$(this);
            data_['f']['i'][i]=th_.data('img');
            data_['f']['t'][i]=th_.find('input').val();
            i++;
        });
        
        if (data_['text']=='' && count(data_['f']['i'])==0){
            err_text+='<p>Сообщение не может быть пустым!</p>';
        }
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	loading(1);
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/m_postav.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			loading(0);
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        CKEDITOR.instances.m_postav_mess_text.setData('');
                        $('.m_postav_mess_files ul').html('');
        	            if (typeof callback=='function'){callback(data_n);}   
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    }else{
        alert_m('Не определен номер поступления!','','error','none');
    }
}
//*****************************************************************************************************
//Удаление сообщения
function mess_del(id,callback){
    id = id || '';
    callback = callback || '';
    
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='mess_del';
    data_['id']=id;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof callback=='function'){callback(data_n);}   
                    
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}

//*****************************************************************************************************

// Форма ввода серийных номеров
function s_cat_barcode_tovar_form(th_,callback){
    callback=callback || '';
    var kol=th_.closest('.ttable_tbody_tr').find('.find_tovar__kol_span').html()-0;
    
    var barcode_cur_tip='0';//тип партийности товара
    var barcode_cur=th_.find('.s_cat_barcode_tovar_cur').html();
    var barcode_arr = new Array();
        if (barcode_cur!=''){
            
            //получаем тип партийности
            if ((barcode_cur.split('||').length - 1)>0){
                var barcode_cur_arr=barcode_cur.split('||');
                barcode_cur_tip=barcode_cur_arr[0];
                barcode_cur=barcode_cur_arr[1];
            }
            
            //получаем штрихкоды
            if ((barcode_cur.split('@@').length - 1)>0){
                barcode_arr = barcode_cur.split('@@');
            }else{
                barcode_arr[0]=barcode_cur;
            }
        }
   
    var txt='';
    var val='';
    var sal='';
    var dis='';
    var m_tovar_id='';
    var m_tovar_sal='';
    txt+='<div class="barcode_tabs">';
        txt+='<ul>';
            txt+='<li><a href="#tabs_barcode_item">Штучный</a></li>';
        if (kol>1){
            txt+='<li><a href="#tabs_barcode_part">Партийный</a></li>';
        }
        txt+='</ul>';
        txt+='<div id="tabs_barcode_item">';
            txt+='<p>Введите штрих-код каждого товара:</p>';
            for(i=0;i<kol;i=i+1){
                            
                val='';
                sal='';
                dis='';
                m_tovar_id='';
                m_tovar_sal='';
                if (barcode_cur_tip=='0'){//штучный
                    if (typeof barcode_arr[i]!='undefined'){
                        val=barcode_arr[i];
                        var val_arr=val.split('##');
                        val=val_arr[1];
                        m_tovar_id=val_arr[0];
                        m_tovar_sal=val_arr[2];
                        if (m_tovar_sal!=''){sal='<i class="fa fa-shopping-cart" title="Товар продан"></i>';dis=' disabled="disabled"';}
                        else{
                            if (m_tovar_id!=''){sal='<i class="fa fa-check-square" title="Товар уже на складе"></i>';}
                        }
                        
                    }
                }
                txt+='<p class="barcode_item_p"><input type="text" placeholder="Штрих-код товара" data-sal="'+_IN(m_tovar_sal)+'" data-id="'+_IN(m_tovar_id)+'" '+dis+' class="barcode_item" value="'+_IN(val)+'" />'+sal+'</p>';
            }
        txt+='</div>';
        if (kol>1){
            txt+='<div id="tabs_barcode_part">';
                 txt+='<p>Введите штрих-код для партии:</p>';
                 var val='';
                 if (barcode_cur_tip=='1'){//Партийный
                    if (typeof barcode_arr[0]!='undefined'){
                        val=barcode_arr[0];
                        var val_arr=val.split('##');
                        val=val_arr[1];
                        m_tovar_id=val_arr[0];
                        m_tovar_sal=val_arr[2];
                        if (m_tovar_id!=''){sal='<i class="fa fa-shopping-cart" title="Товар продан"></i>';dis=' disabled="disabled"';}
                    
                    }
                 }
                 txt+='<p><input type="text" placeholder="Штрих-код партии" '+dis+' data-sal="'+_IN(m_tovar_sal)+'" data-id="'+_IN(m_tovar_id)+'" class="barcode_part" value="'+val+'" />'+sal+'</p>';
            txt+='</div>';
        }
        txt+='<div><center><span class="btn_orange barcode_save">Сохранить</span></center></div>';
    txt+='</div>';
    
    alert_m(txt,'','barcode_modal','none');
    
    var cur_tab=0;
    if (barcode_cur_tip=='1'){//партийный
        cur_tab=1;
    }
    $('.barcode_tabs').tabs({
                        active: cur_tab,
                        activate: function( event, ui ) {
                            $('.modal_barcode_modal input:visible:first').focus();
                        }
                        });//табы
    
    $('.modal_barcode_modal input:visible:first').focus();
    if (typeof callback=='function'){callback(th_);}
}
//*****************************************************************************************************

//Сохранение штрихкодов
function s_cat_barcode_tovar_form_save(th_,callback){
    callback=callback || '';
    var active = $(".barcode_tabs").tabs("option","active");
   
    var cur_barcode_arr=sel_in_array($('.modal_barcode_modal input:visible'),'val');
    var cur_m_tovar_id_arr=sel_in_array($('.modal_barcode_modal input:visible'),'data','id');
    var cur_sal_arr=sel_in_array($('.modal_barcode_modal input:visible'),'data','sal');
    var cur_barcode_all_arr=new Array();
    for (var i in cur_barcode_arr){
        cur_barcode_all_arr[i]=cur_m_tovar_id_arr[i]+'##'+cur_barcode_arr[i]+'##'+cur_sal_arr[i];
    }
    
    var cur_barcode=cur_barcode_all_arr.join('@@');
    cur_barcode=active+'||'+cur_barcode;
    th_.find('.s_cat_barcode_tovar_cur').html(cur_barcode);
        
    //логирование
    add_log('','Ввод штрих-кода товара',cur_barcode);

    
    if (typeof callback=='function'){callback(th_);}
}
//Проверка штрих-кодов
function chk_barcode(){
    var no_error=1;//
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        var th_=$(this);
        var kol=th_.find('.find_tovar__kol_span').html()-0;
        var barcode_cur=th_.find('.s_cat_barcode_tovar_cur').html();
        
        var title='';
        var status_code=0;
        var barcode_cur_tip='0';//тип партийности товара
        var barcode_arr=new Array();
        
        if (barcode_cur!=''){
            //получаем тип партийности
            if ((barcode_cur.split('||').length - 1)>0){
                var barcode_cur_arr=barcode_cur.split('||');
                barcode_cur_tip=barcode_cur_arr[0];
                barcode_cur=barcode_cur_arr[1];
            }
            
            //получаем штрихкоды
            if ((barcode_cur.split('@@').length - 1)>0){
                barcode_arr = barcode_cur.split('@@');
            }else{
                barcode_arr[0]=barcode_cur;
            }
            
            //меняем тип товара на штучный
            if (barcode_cur_tip=='1' && kol==1){
                barcode_cur_tip='0';
                th_.find('.s_cat_barcode_tovar_cur').html(barcode_cur_tip+'||'+barcode_arr[0]);
            }
            
            
            if (barcode_cur_tip=='0'){//штучный
            
                if (barcode_arr.length>kol){
                    title+='Количество штрих-кодов указано больше чем товара';
                }
                if(barcode_arr.length<kol){
                    title+='Количество штрих-кодов указано меньше чем товара';
                }
                if(barcode_arr.length==kol){
                    status_code=1;
                    title+='Заданы все штрих-кода для штучного товара';
                }
            }
            if (barcode_cur_tip=='1'){//партийный
                if (kol<=1){
                    title+='Укажите штрих-код для штучного товара. Ранее был задан для партийного';
                }else{
                    
                    if (barcode_arr.length==1){
                        status_code=1;
                        title+='Задан штрих-код партийного товара';
                    }else{
                        title+='Не определен штрих код партийного товара';
                    }
                }
            }
            
        }else{
            title+='Штрих-коды не указаны!';
        }
        
        if (status_code==0){
            no_error=0;
            th_.find('.s_cat_barcode_tovar').removeClass('s_cat_barcode_tovar__ok').addClass('s_cat_barcode_tovar__error');
        }else{
            th_.find('.s_cat_barcode_tovar').removeClass('s_cat_barcode_tovar__error').addClass('s_cat_barcode_tovar__ok');
        }
        th_.find('.s_cat_barcode_tovar').attr('title',title);
    });
    
    if (no_error==0){
        $('.m_postav_auto_barcode').addClass('m_postav_auto_barcode__error').removeClass('m_postav_auto_barcode__ok');
    }else{
        $('.m_postav_auto_barcode').addClass('m_postav_auto_barcode__ok').removeClass('m_postav_auto_barcode__error');
    }
}

//проверка доставки
function chk_dostav(){
    if ($('input[name="date_info"]').val()!=''){
        $('.s_cat_barcode_tovar').show();
        $('.m_postav_auto_barcode').show();
    }else{
        $('.s_cat_barcode_tovar').hide();
        $('.m_postav_auto_barcode').hide();
    }
}


//ОТМЕНА ПОСТУПЛЕНИЯ
function m_postav_close(nomer,callback){
    callback=callback || '';
    
    var err_text='';
    var data_=new Object();
    data_['nomer']=nomer;
    data_['_t']='m_postav_close';
    
    if (data_['nomer']==''){
        err_text+='Не определен номер поступления для отмены';
    }
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    
    	            if (typeof callback=='function'){callback(data_n);}
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}
//ВОЗВРАТ из ОТМЕНЫ ПОСТУПЛЕНИЯ
function m_postav_open(nomer,callback){
    callback=callback || '';
    
    var err_text='';
    var data_=new Object();
    data_['nomer']=nomer;
    data_['_t']='m_postav_open';
    
    if (data_['nomer']==''){
        err_text+='Не определен номер поступления для открытия';
    }
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_postav.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof callback=='function'){callback(data_n);}
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}


//Добавление контрагента //2018-05-17 toowin86
function i_contr_select(i_contr_arr,i_contr_org_arr,active_id,callback){
    //  i_contr_arr['id'] - int
    //  i_contr_arr['name'] - text
    //  i_contr_arr['phone'] - int
    //  i_contr_arr['email'] - text
    //  i_contr_org_arr['id'][$i] - int
    //  i_contr_org_arr['name'][$i] - text
    //  i_contr_org_arr['phone'][$i] - int
    //  i_contr_org_arr['email'][$i] - text
    //  active_id - int (id выбранной организации. если 0, то выбран контрагент)
    /*
    alert('i_contr_arr[id]='+i_contr_arr['id']+"\n"+
    'i_contr_arr[name]='+i_contr_arr['name']+"\n"+
    'i_contr_arr[phone]='+i_contr_arr['phone']+"\n"+
    'i_contr_arr[email]='+i_contr_arr['email']+"\n"+
    'i_contr_org_arr[id]='+i_contr_org_arr['id']+"\n"+
    'i_contr_org_arr[name]='+i_contr_org_arr['name']+"\n"+
    'i_contr_org_arr[phone]='+i_contr_org_arr['phone']+"\n"+
    'i_contr_org_arr[email]='+i_contr_org_arr['email']+"\n"+
    'active_id='+active_id+"\n");
    */
    
    active_id = active_id || 0;
    callback=callback || "";
    $('.i_contr_tip_1, .i_contr_tip_2').removeClass('active');//удаляем классы активности
    $('.cnt_org_in_select').detach(); //удаляем количествво организаций
    
        
    //проверка выбранного контрагента - физ.лицо или организация
    var active_name=''; 
        if (active_id==0){active_name=i_contr_arr['name']; $('.i_contr_tip_1').addClass('active');}
        else{$('.i_contr_tip_2').addClass('active');}
    var active_phone=i_contr_arr['phone'];
    var active_email=i_contr_arr['email'];
    var org_cnt=0;
    var active_num='-';
    if (typeof i_contr_org_arr['id']=='object' && i_contr_org_arr['id'].length>0){
        for(var i in i_contr_org_arr['id']){
            if (i_contr_org_arr['id'][i]==active_id){
                active_num=i+1;
                active_name=i_contr_org_arr['name'][i];
                active_phone=i_contr_org_arr['phone'][i];
                active_email=i_contr_org_arr['email'][i];
            }
            //заносим данные организаций
            $('input[name="i_contr"]')
            .data('i_contr_org_id'+i,i_contr_org_arr['id'][i])
            .data('i_contr_org_name'+i,i_contr_org_arr['name'][i])
            .data('i_contr_org_phone'+i,i_contr_org_arr['phone'][i])
            .data('i_contr_org_email'+i,i_contr_org_arr['email'][i]);
            
            org_cnt++;
        }
    }
    
    //заносим данные контр агента
    $('input[name="i_contr"]')
        .data('active_id',active_id)
        .data('i_contr_id',i_contr_arr['id'])
        .data('i_contr_name',i_contr_arr['name'])
        .data('i_contr_phone',i_contr_arr['phone'])
        .data('i_contr_email',i_contr_arr['email'])
        .data('i_contr_org_cnt',org_cnt);
    
    //Заполняем активного контр.агента
    $('input[name="i_contr"]').val(active_name);
    if (active_phone!=''){
        $('.i_contr_add_form_phone').html('<i class="fa fa-phone"></i> <a href="tel:'+active_phone+'">'+active_phone+'</a>');
    }
    if (active_email!=''){
        $('.i_contr_add_form_email').html('<i class="fa fa-envelope-o"></i> <a href="mailto:'+active_email+'?subject='+$('input[name="nomer"]').val()+'" target="_blank">'+active_email+'</a>');
    }
    $('.i_contr_tip_2').append('<span class="cnt_org_in_select">'+active_num+'</span>');//количество организаций
    

    
    chk_i_contr();
    if (typeof callback=='function'){callback();}
}//end 2018-05-17 toowin86

//Выбор работника
function change_worker_tp(){
    var i_tp_id=$('select[name="a_admin"]').find('option:selected').data('i_tp_id');
    var i_tp_name=$('select[name="a_admin"]').find('option:selected').data('i_tp_name');
    var i_tp_id_old=$('.i_tp_span .i_tp_span_cur').data('id');
    
    $('.i_tp_span .i_tp_span_cur').data('id',i_tp_id).html('<span class="i_tp_span_cur_txt">'+i_tp_name+'</span>');
        
    if (i_tp_id!=i_tp_id_old){
        chk_filial();//проверка филиала
    }
}


//ЛОГИ
function add_log(m_log_id,m_log_type,log_txt,a_admin,dt){
    m_log_id=m_log_id || '';
    a_admin=a_admin || '';
    m_log_type=m_log_type || '';
    log_txt=log_txt || '';
    dt = dt || date('d.m.Y H:i:s');
    
    
    //удаляем логи по этому же действию до сохранения
    if (m_log_type=='Выбор контрагента'
        || m_log_type=='Выбор работника'
        || m_log_type=='Изменение даты доставки'
        || m_log_type=='Выбор филиала'
        || m_log_type=='Изменение даты'
        || m_log_type=='Изменение комментариев'
        || m_log_type=='Выбор ответственного'
        || m_log_type=='Изменение названия проекта'
        || m_log_type=='Изменение ФИО получателя в доставке'
        || m_log_type=='Изменение города в доставке'
        || m_log_type=='Изменение адреса в доставке'
        || m_log_type=='Изменение индекса в доставке'
        || m_log_type=='Изменение телефона в доставке'
        || m_log_type=='Изменение номера отправления в доставке'
        || m_log_type=='Изменение даты отправления в доставке'
        || m_log_type=='Изменение транспортной компании в доставке'
        ){
        $('.m_log_item[data-id=""]').each(function(){
            var th_0=$(this);
            if (th_0.find('.m_log_item_t').text()==m_log_type){
                
                th_0.detach();
            }
        });
    }
    
    
    var txt_log='<div class="m_log_item" data-id="'+m_log_id+'">';
    txt_log+='<span class="m_log_item_t">'+m_log_type+'</span>';
    txt_log+='<span class="m_log_item_l">'+log_txt+'</span>';
    txt_log+='<span class="m_log_item_a">'+a_admin+'</span>';
    txt_log+='<span class="m_log_item_d">'+dt+'</span>';
    txt_log+='</div>';
    
    $('.m_postav_log_text').prepend(txt_log);
}


//выбор филиала
function chk_filial(){
    var i_tp_id=$('.i_tp_span_cur').data('id');
    $('.pl_schet option[data-i_tp_id!="'+i_tp_id+'"]').prop('disabled','disabled').prop('selected',false);
    $('.pl_schet option[data-i_tp_id="'+i_tp_id+'"]').prop('disabled',false);
    $('.pl_schet option[data-i_tp_id="'+i_tp_id+'"]:first').prop('selected','selected');
    $('.pl_schet').select2({'width':'100%'});
    //alert(i_tp_id);
}


//******************************************************************************************************************
//******************************************************************************************************************
//******************************************************************************************************************
$(document).ready(function(){
    history_name=($('.content_block_main h1').text()).trim(); //история URL
    //ФОРМА ДОБАВЛЕНИЯ ЗАКАЗА
    chk_i_contr();
    chk_summ();//Проверяем сумму
    chk_comments(); //проверяем комментарии
    chk_pl();//проверка платежей
    find();//поиск
    chk_dostavka();//доставка
    chk_barcode();
    
    var m_postav_mess_text_=CKEDITOR.replace('m_postav_mess_text',{
    allowedContent:true,
    height: '100px',
    toolbar: [
    { name: 'document', groups: [ 'mode', 'document' ], items: [ 'Source'] },
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript','NumberedList', 'BulletedList' ] },
    { name: 'links', items: [ 'Link', 'Unlink'] }]});
    AjexFileManager.init({returnTo: 'ckeditor', editor: m_postav_mess_text_});

    
    $('.tabs_items_work').tabs();//табы
    $('.tabs_items_work > ul > li:visible:first a').trigger('click');
    $('.pl_schet').select2({'width':'100%'});
    
    $('input[name="m_postav_data1_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(current_time,$input){
        find();
    }});
    $('input[name="m_postav_data2_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(current_time,$input){
        find();
    }});
    
    $('input[name="m_dostavka_data"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true});
    $('input[name="m_dostavka_phone"]').integer_().mask("0(000)000-00-00",{clearIfNotMatch: true});
    $('input[name="m_dostavka_index"]').integer_();
    $('input[name="m_dostavka_summa"]').float_().spinner({min:0});
    $('select[name="i_tk_id"]').select2({allowClear: true,'width':'100%'});
    $('select[name="m_dostavka_city_id"]').select2({allowClear: true,'width':'100%',
        ajax: {
            url: "ajax/m_postav.php",
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
        
    });
    
    //Запуск поиска
    $('select[name="s_struktura_s_cat_select"]').select2({allowClear: true,'width':'100%'}).change(function(){
        var th_=$(this).closest('div[id*=tab]').find('.s_cat_items_find');
        th_.autocomplete( "search", th_.val());
    });
    $(document).delegate('.s_cat_items_find','dblclick',function(){
        $(this).autocomplete( "search", $(this).val() );
    });
    
    //Статусы
    $(document).delegate('.m_postav_fillter__find_ststus ul li','click',function(){
        $('.m_postav_fillter__find_ststus ul li').removeClass('active');
        $(this).addClass('active');
        find();//поиск
    });
    
    //Информация
    $(document).delegate('.m_postav_first_tbl .fa-info-circle','click',function(){
        if ($(this).find('>span').size()>0){
            alert_m($(this).find('>span').html(),'','info','none');
        }
    });
    
    //Адрес контрагента
    $(document).delegate('.modal_i_contr_form input[name="adress"]','keyup',function(){
        var val=$(this).val();
        $('.i_contr_map').detach();
        if (val!=''){
            $(this).closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:first').append(' <a class="i_contr_map" href="http://maps.yandex.ru/?text='+val+'" target="_blank">карта</a>');
        }
    });
    
    

    
    //работник
    $('select[name="a_admin"]').select2({'width':'100%'}).change(function(){
        change_worker_tp();//выбор работника
        add_log('','Выбор работника',$('select[name="a_admin"]').find('option:selected').text()+' #'+$('select[name="a_admin"]').val());
    });
    change_worker_tp();//выбор работника
    chk_filial();//проверка филиала
    
    //Дата
    $('.pl_data').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true});
    $('input[name="date"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true});
    $('input[name="date_info"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true});
    
    
    //Быстрый ввод поступления на склад
    $(document).delegate('.m_postav_other_info_time span','click',function(){
        var th_=$(this);
        var time_=th_.data('time')*1000;//переводим в милисекунды
        if (time_==0){
            $('input[name="date_info"]').val('');
        }else{
            var dt_ = new Date(); 
            var dt_2=dt_.valueOf() + time_;
            $('input[name="date_info"]').val(date('d.m.Y H:i',dt_2)).trigger('change');
            add_log('','Изменение даты доставки',$('input[name="date_info"]').val());
            
        }
    });
    
    //изменение даты доставки
    $(document).delegate('input[name="date_info"]','change',function(){
        chk_dostav();
    });
    $(document).delegate('input[name="date_info"]','blur',function(){
        var th_=$(this);
        add_log('','Изменение даты доставки',th_.val());
    });
    
    
    //покупатель
    $(document).delegate('input[name="i_contr"]','keyup',function(){
        $('input[name="i_contr"]').data('id','');
        chk_i_contr();
    });
    
  
    //Добавление нового контрагента
    $(document).delegate('.i_contr_com .fa-plus','click',function(){
        var val='';
        if ($('.i_contr_tip_1.active').size()>0){
            val=$('input[name="i_contr"]').val();
        }
        i_contr_form('',val,function(data_n){
             $('.modal_i_contr_form').arcticmodal('close');
        });
    });
   
    //Изменение контрагента
    $(document).delegate('.i_contr_com .fa-edit','click',function(){
        i_contr_form($('input[name="i_contr"]').data('i_contr_id'),'',function(data_n){
            $('.modal_i_contr_form').arcticmodal('close');
        });
    });
    //Изменение дубликата покупателя
    $(document).delegate('.i_contr_duble_change','click',function(){
        var id_=$(this).data('id');
        $.arcticmodal('close');
        i_contr_form(id_,'',function(data_n){
            $('.modal_i_contr_form').arcticmodal('close');
            i_contr_select(data_n['i_contr'],data_n['i_contr_org'],data_n['active']);
        });
    });
    
    //Изменение контрагента из таблицы
    $(document).delegate('.m_postav_item__i_contr .m_postav_item__i_contr_name','click',function(){
        i_contr_form($(this).data('id'),'',function(data_n){
            $('.modal_i_contr_form').arcticmodal('close');
            find();
        });
    });
    
    //Очистка товара
    $(document).delegate('input[name="s_cat_tovar_find"]','keyup',function(){
        s_cat_arr['id']='';
    });
    
    //АВТОКОМПЛИТ ТОВАРА
    $('.find_tovar .s_cat_items_find').autocomplete({
        minLength: 0,
        appendTo: ".m_postav_add_form_div",
        source: function(request, response){
            s_cat_arr['id']='';
             request['_t']='s_cat_autocomplete';
             
             request['tip']='1';
                if ($('.ui-state-active').find('a[href="#tabs-2"]').size()==1){
                    request['tip']='2';
                }
             request['s_struktuta_id']=$('.find_tovar .s_cat_items_find').closest('div[id*=tab]').find('select[name="s_struktura_s_cat_select"]').val();
             if (typeof jqxhr1!='undefined'){jqxhr1.abort();}
             
             $('.find_tovar .s_cat_items_find').closest('div').find('i').removeClass('fa-search').addClass('ico').addClass('loading_gray');
             jqxhr1 = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_postav.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	
            	   $('.find_tovar .s_cat_items_find').closest('div').find('i').addClass('fa-search').removeClass('ico').removeClass('ui-autocomplete-loading').removeClass('loading_gray');
            	   if (is_json(data)==true){
                	       var data_n=JSON.parse(data);
                           response(data_n);
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                if (typeof data_n[i]!='undefined'){
                                    var img_='';
                                    if (data_n[i].img!='') {
                                        img_='<div class="ttable2_tbody_tr_td"><img src="../i/s_cat/original/'+data_n[i].img+'" /></div>';
                                    }
                                    
                                    var txt='<div class="tbl_autocomplate ttable2">';
                                    txt+='<div class="ttable2_tbody_tr">';
                                    txt+=img_
                                    var pr_=''; if (data_n[i].pr!='' && typeof data_n[i].pr!='undefined'){pr_='<p class="tbl_autocomplate_prop_val">'+data_n[i].pr+'</p>';}
                                    txt+='<td><h1>'+data_n[i].value+'</h1>'+pr_+'</td></tr><tr><td>';
                                    
                                    if (data_n[i].p!=''){
                                        txt+='<p>'+data_n[i].p+' <i class="fa fa-rouble"></i></p>';
                                    }
                                    txt+='</div></div></div>';
                                 
                                   $(this).html(txt);
                                   $('.find_tovar .s_cat_items_find').focus();
                               }
                            });
                            
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
        },
        select: function( event, ui ) {
            s_cat_arr['id']=ui.item.id;
            s_cat_arr['name']=ui.item.value;
            s_cat_arr['price']=ui.item.p;
            s_cat_arr['tip']=ui.item.t;
            s_cat_arr['img']=ui.item.img;
            s_cat_arr['pr']=ui.item.pr;
        },
        close: function( event, ui ) {
            if (typeof s_cat_arr['id']!='undefined'){
                if (s_cat_arr['id']>0){
                    var price=0;//s_cat_arr['price'];
                    
                    //логирование
                    add_log('','Добавление товара',s_cat_arr['name']+' #'+s_cat_arr['id']);
                    
                    s_cat_add(s_cat_arr['id'],s_cat_arr['name'],1,price,s_cat_arr['tip'],'','',s_cat_arr['img'],'',s_cat_arr['pr']);
                }else{
                    if (s_cat_arr['id']=='-1'){
                        $('.find_tovar .s_cat_items_find').val('');
                        s_cat_add_change_form($('.find_tovar .s_cat_items_find').val(),'',function(res){
                            if (res.tip=='Товар'){$('.tabs1_li').show();}
                            if (res.tip=='Услуга'){$('.tabs2_li').show();}
                            
                            s_cat_add(res.id,res.name,1,res.price,res.tip,'','','');
                            $('.modal_s_cat_form').arcticmodal('close');
                        });
                    }
                }
                
            }
        }
    });
    //Удаление товара
    $(document).delegate('.s_cat_del_tovar','click',function(){
        var th_=$(this);
        var name_=th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text();
        var price_=th_.closest('.ttable_tbody_tr').find('.find_tovar__sum_span').text();
        var id_=th_.closest('.ttable_tbody_tr').data('id');
        add_log('','Удаление товара',name_+' #'+id_+', цена: '+price_+' руб.');
        
        th_.closest('.ttable_tbody_tr').detach();
        chk_summ();//Проверяем сумму
        chk_num();//Проверяем номера
    });
    
    //Изменение количества товара //, .find_tovar__sum span
    $(document).delegate('.find_tovar__kol_span','click',function(){
        var th_=$(this);
        th_.closest('.val').html('<input type="text" class="find_tovar__kol_val" value="'+_IN(th_.text())+'" />');
        var min_=1;if (typeof th_.data('min')!='undefined' ){min_=th_.data('min')-0;}
        
        $('.find_tovar__kol_val').float_().spinner({min:min_}).focus().select();
    });
    $(document).delegate('.find_tovar__kol_val','blur',function(){
        var th_=$(this);
        add_log('','Изменение количества товара',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+th_.val()+' шт.');
        th_.closest('.val').html('<span class="find_tovar__kol_span">'+th_.val()+'</span>');
        chk_summ();//Проверяем сумму
        chk_barcode();
    });
    $(document).delegate('.find_tovar__kol_val','keyup',function(e){
        if (e.which==13){
            $(this).trigger('blur');
        }
    });
    //Изменение цены товара 
    $(document).delegate('.find_tovar__sum_span','click',function(){
        var th_=$(this);
        th_.closest('.val').html('<input type="text" class="find_tovar__sum_val" value="'+_IN(th_.text())+'" />');
        $('.find_tovar__sum_val').float_().spinner({min:1}).focus().select();
    });
    $(document).delegate('.find_tovar__sum_val','blur',function(){
        var th_=$(this);
        add_log('','Изменение цены товара',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+number_format(th_.val(),0,'.','')+' руб.');
        th_.closest('.val').html('<span class="find_tovar__sum_span">'+number_format(th_.val(),0,'.','')+'</span>');
        chk_summ();//Проверяем сумму
    });
    $(document).delegate('.find_tovar__sum_val','keyup',function(e){
        if (e.which==13){
            $(this).trigger('blur');
        }
    });
    
    //Выбор типа покупателя
    $(document).delegate('.m_postav_i_contr_tip span','click',function(){
        var th_=$(this);
        $('.m_postav_i_contr_tip>span').removeClass('active');
        if (th_.data('id')=='1'){//Физ-лицо
            $('.i_contr_tip_1').addClass('active');
            $('input[name="i_contr"]').data('active_id','0');
        }else{//Организация
            $('.i_contr_tip_2').addClass('active');//активируем вкладку
            
            var i_contr_org_cnt=$('input[name="i_contr"]').data('i_contr_org_cnt');
            var active_id_cur=$('input[name="i_contr"]').data('active_id');
            if (active_id_cur=='0'){//выбор первой организации
            $('input[name="i_contr"]').data('active_id',$('input[name="i_contr"]').data('i_contr_org_id0'));
            }else{
                var k=0;
                
                for(var i=0;i<i_contr_org_cnt;i++){
                    if (k==1){
                        $('input[name="i_contr"]').data('active_id',$('input[name="i_contr"]').data('i_contr_org_id'+i));
                        k++; 
                    }
                    if ($('input[name="i_contr"]').data('i_contr_org_id'+i)==active_id_cur){
                        if (i_contr_org_cnt-1==i){//если последняя была выбрана - переходим на первую
                        
                            $('input[name="i_contr"]').data('active_id',$('input[name="i_contr"]').data('i_contr_org_id0'));
                            
                        }else{
                           k++; 
                        }
                        
                    }
                    
                    
                }
            }
        }
        chk_i_contr();
    });
    
    
    //Очистка покупателя при поставщика
    $(document).delegate('input[name="i_contr"]','keyup',function(){
        i_contr_id='';$('input[name="i_contr"]').data('i_contr_id','');
    });
    //Автозаполнение поставщика
    $(document).delegate('input[name="i_contr"]','dblclick',function(){
        $(this).autocomplete( "search", $(this).val() );
    });
    
    //Автозаполнение покупателя
    $('input[name="i_contr"]').autocomplete({
        minLength: 0,
        appendTo: ".m_postav_add_form_div",
        source: function(request, response){
             request['_t']='i_contr_autocomplete';
             term=request['term'];
             if (typeof jqxhr!='undefined'){jqxhr.abort();}
             jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_postav.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	   if (is_json(data)==true){
                	        var data_n=JSON.parse(data);
                            response(data_n);
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                
                                var txt='<table class="tbl_autocomplate">';
                                txt+='<tr>';
                                txt+='<td colspan="2"><h1>'+data_n[i].label;
                                if (typeof data_n[i].i_contr_org['name']=='object' && data_n[i].i_contr_org['name'].length>0){
                                    for (var j in data_n[i].i_contr_org['name']){
                                        txt+='<span class="i_contr_org_autocomp_in_i_contr"> - '+data_n[i].i_contr_org['name'][j]+'</span>';
                                    }
                                    
                                }
                                txt+='</h1></td></tr>';
                                if (data_n[i].i_contr['phone']!='' || data_n[i].i_contr['email']!=''){
                                    txt+='<tr><td>';
                                    if (data_n[i].i_contr['phone']!=''){
                                        txt+='<p>'+data_n[i].i_contr['phone']+'</p>';
                                    }
                                    txt+='</td><td>';
                                    if (data_n[i].i_contr['email']!=''){
                                        txt+='<p>'+data_n[i].i_contr['email']+'</p>';
                                    }
                                    txt+='</td></tr>';
                                }
                                txt+='</table>';
                               $(this).html(txt);
                            });
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
        },
        select: function( event, ui ) {
            i_contr_arr['id']=ui.item['i_contr'];
            if (ui.item.label=='Добавить нового контрагента'){
                i_contr_arr['id']='-1';
            }else{
                i_contr_arr['id']=ui.item['i_contr'].id;
                i_contr_arr['name']=ui.item['i_contr'].name;
                i_contr_arr['phone']=ui.item['i_contr'].phone;
                i_contr_arr['email']=ui.item['i_contr'].email;
                i_contr_org_arr['id']=ui.item['i_contr_org'].id;
                i_contr_org_arr['name']=ui.item['i_contr_org'].name;
                i_contr_org_arr['phone']=ui.item['i_contr_org'].phone;
                i_contr_org_arr['email']=ui.item['i_contr_org'].email;
                i_contr_active_id=ui.item['active'];
            }
        },
        close: function( event, ui ) {
            if (i_contr_arr['id']!=''){
                if (i_contr_arr['id']=='-1'){
                    
                    var val=$('input[name="i_contr"]').val();
                    
                    i_contr_form('',val,function(data_n){
                        $('.modal_i_contr_form').arcticmodal('close');
                        i_contr_select(data_n['i_contr'],data_n['i_contr_org'],data_n['active']);
                        
                    });
                    
                }else{
                    if (typeof i_contr_arr['id']!='undefined'){
                        i_contr_select(i_contr_arr,i_contr_org_arr,i_contr_active_id);
                        add_log('','Выбор контрагента',i_contr_arr['name']+' #'+i_contr_arr['id']);
                        if ( typeof i_contr_active_id!='undefined' && i_contr_active_id-0>0){
                            
                            add_log('Добавление организации контрагента: '+i_contr_org_arr['name'][0]+' #'+i_contr_org_arr['id'][0]);
                        }
                    }
                }
            }
        }
    });
    
    //Добавление организации
    $(document).delegate('.i_contr_mini_form_org_add .fa-plus','click',function(){
        i_contr_org_form('',$('.i_contr_mini_form input[name="i_contr_org_name_auto"]').val(),function(res){
            
            i_contr_org_in_i_cont_form(res.id,'add',res.i_contr_org_name,res.i_contr_org_inn);
            $('.modal_i_contr_org_form').arcticmodal('close');
        });
    });
    
    //Изменить организацию с формы контрагента
    $(document).delegate('.i_contr_org_mini_change','click',function(){
        i_contr_org_form($(this).closest('div[data-id!=""]').data('id'),$(this).closest('div[data-id!=""]').find('.i_contr_org_mini_edit span:first').text(),function(res){
            i_contr_org_in_i_cont_form(res.id,'add',res.i_contr_org_name,res.i_contr_org_inn);
        });
    });
    //Изменить организацию с таблицы
    $(document).delegate('.m_postav_item__i_contr_org>span','click',function(){
        i_contr_org_form($(this).closest('.m_postav_item__i_contr_org').data('id'),'',function(res){
            find();
            
        });
    });
    //Удаление организации с формы контрагента
    $(document).delegate('.i_contr_org_mini_remove','click',function(){
        i_contr_org_in_i_cont_form($(this).closest('div[data-id!=""]').data('id'),'del');
    });
    
    //Изменение товара
    $(document).delegate('.s_cat_change_tovar','click',function(){
        var th_=$(this);
        var kol_=th_.closest('.ttable_tbody_tr').find('.find_tovar__kol_span').text();
        var sum_=th_.closest('.ttable_tbody_tr').find('.find_tovar__sum_span').text();
        var comm_=th_.closest('.ttable_tbody_tr').find('.s_cat_info_hidden').html();
        var t_='Товар';if ($('.ui-state-active').find('a[href="#tabs-2"]').size()==1){ t_='Услуга';}
        s_cat_add_change_form('',th_.closest('.ttable_tbody_tr').data('id'),function(res){
           
            th_.closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:first').next().html(res.name);
            
            
        });
    });
    
    //Открываем форму комментариев
    $(document).delegate('.s_cat_info_tovar','click',function(){
        var th_=$(this);
        var nom=th_.closest('.ttable_tbody_tr').data('nom');
        var val_=th_.find('.s_cat_info_hidden').html();
        var txt='<form class="s_cat_add_comments">';
        txt+='<p>Введите описание товара в заказе:</p>';
        txt+='<div><textarea class="s_cat_add_comments_txt">'+val_+'</textarea></div>';
        txt+='<div><center><span data-nom="'+nom+'" class="btn_gray s_cat_add_comments_save">Сохранить</span></center></div>';
        txt+='</form>';
        alert_m(txt,'','info','none');
        $('.s_cat_add_comments_txt').focus();
    });
    
    //Сохранение комментариев к товару
    $(document).delegate('.s_cat_add_comments_save','click',function(){
        var txt=$('.s_cat_add_comments_txt').val();
        var nom=$(this).data('nom');
        $('div[id*="tabs-"]:visible .ttable_tbody_tr[data-nom="'+nom+'"]').find('.s_cat_info_tovar>div').html(txt);
        $('.s_cat_add_comments').arcticmodal('close');
        chk_comments();
    });
    
    //Сохранениие заказ
    $(document).delegate('.m_postav_add_form__save','click',function(){
        m_postav_add_form__save(function(){
            find();
        });
    });
    
    //Комментарии
    $(document).delegate('.m_postav_comments_div textarea','focus',function(){
        $(this).css({'height':'120px'});    
    });
    $(document).delegate('.m_postav_comments_div textarea','blur',function(){
        $(this).css({'height':'24px'});    
    });
    $(document).delegate('.m_postav_add__comments','blur',function(){
        add_log('','Изменение комментариев',$('.m_postav_add__comments').val());
    });
    
    //Добавление платежей
    $(document).delegate('.m_postav_add_pl__add_com','click',function(){
        var err_txt='';
        var sum_=($('.pl_price').val())-0;
        if ($('.pl_data').val()==''){err_txt+='<p>Не указана дата</p>';}
        //if (sum_<=0){err_txt+='<p>Сумма должна быть больше нуля</p>';}
        if (err_txt==''){
            if (sum_<=0){
                pl_add($('.pl_data').val(),sum_,$('.pl_schet').val(),'','Кредит');
            }
            else{
                pl_add($('.pl_data').val(),sum_,$('.pl_schet').val());
            }
        }
        else{
            alert_m(err_txt,'','error','none');
        }
        chk_pl();
    });
    //Удаление платежа
    $(document).delegate('.m_postav_add_pl__com .fa-remove','click',function(){
        $(this).closest('.ttable_tbody_tr').detach();
        chk_pl();
    });
    //РАСПЕЧАТАТЬ ПКО
    $(document).delegate('.m_postav_add_pl__com .fa-print','click',function(){
        alert_m('Заказ будет сохранен!');
    });
    
    //****************************************** ПОИСК ********************************************
    //Фильтр по статусам заказов
    $(document).delegate('.m_postav_fillter__tip li','click',function(){
        var th_=$(this);
        var cl_=th_.attr('class');
        if (typeof cl_=='undefined'){cl_='';}
        if ((cl_.split('active').length - 1)>0){
            th_.removeClass('active');
        }
        else{
            th_.addClass('active');
        }
        find();
    });
    //Фильтр на склад или под заказ
    $(document).delegate('.m_postav_fillter__sklad_or_zakaz li','click',function(){
        var th_=$(this);
        var cl_=th_.attr('class');
        if (typeof cl_=='undefined'){cl_='';}
        if ((cl_.split('active').length - 1)>0){
            th_.removeClass('active');
        }
        else{
            th_.addClass('active');
        }
        find();
    });
    //Сортировка
    $(document).delegate('.m_postav_fillter__sort li','click',function(){
        var th_=$(this);
        $('.m_postav_fillter__sort li').removeClass('active');
        th_.addClass('active');
        if (th_.data('val')=='2'){
            $('.m_postav_fillter__tip li[data-val="Доставлен"]').trigger('click');
        }
        else{
            find();
        }
        
    });
    
    //Загрузка поступления
    $(document).delegate('.m_postav_item__id label','click',function(){
        postav_load($(this).closest('.m_postav_item').data('id'));
    });
    
    //Очистка формы
    $(document).delegate('.m_postav_add_form__clear','click',function(){
        add_zakaz_form_clear();
    });
    
    //догрузка
    $(document).delegate('.m_postav__load_add','click',function(){
        $('.m_postav__load_add').detach();
        find($('div.m_postav_item').size());
    });
    
    
    //ЗАГРУЗКА ДОКУМЕНТОВ В СООБЩЕНИИ
    var upload_photo = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : 'upload_button',
        url : 'ajax/m_postav.php',
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
                {title : "Image files", extensions : "jpg,jpeg,gif,png"},
                { title : "Zip files", extensions : "zip,rar" },
                { title : "docs files", extensions : "doc,docx,xls,xlsx,ppt,pptx,pdf,psd,txt" }
            ]
        },
    	flash_swf_url : 'js/Moxie.swf',
    	silverlight_xap_url : 'js/Moxie.xap',
        preinit : {
                Init: function(up, info) {
                
                },
                UploadFile: function(up, file) {
                   up.setOption('multipart_params', {'_t' : 'upload'});
                }
            },
            init : {
                QueueChanged: function(up) {upload_photo.start();},
        		BeforeUpload: function(up, file) {
        		  $('.m_postav_mess_files .loading_file').prepend('<img src="i/l_20_w.gif"> Загрузка...');
                },
                FileUploaded: function(up, file, info) {
                    $('.m_postav_mess_files .loading_file').html('');
                    
                    var arr = (info.response).split('@@');
                    
                    var img_=arr[0];
                    var name_=arr[1];
                    
                    var reg_f=/.*?\./;
                    var ext=img_.replace(reg_f, "");
                    
                    
                    var t_txt='';
                    if (ext=='jpg' || ext=='jpeg' || ext=='gif' || ext=='png'){
                        t_txt='<div class="photo_res__item_image" style="background: url(../i/m_dialog/temp/'+img_+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></div>';
                    }
                    else if(ext=='docx' || ext=='doc'){
                        t_txt='<i class="fa fa-file-word-o"></i>';
                    }
                    else if(ext=='xlsx' || ext=='xls'){
                        t_txt='<i class="fa fa-file-excel-o"></i>';
                    }
                    else if(ext=='zip' || ext=='rar'){
                        t_txt='<i class="fa fa-file-zip-o"></i>';
                    }
                    else if(ext=='txt'){
                        t_txt='<i class="fa fa-file-text-o"></i>';
                    }
                    else if(ext=='psd'){
                        t_txt='<i class="fa fa-file-picture-o"></i>';
                    }
                    else if(ext=='pdf'){
                        t_txt='<i class="fa fa-file-pdf-o"></i>';
                    }
                    else{
                        t_txt='<i class="fa fa-file"></i>';
                    }
                    $('.m_postav_mess_files ul').append('<li class="photo_res__mess" data-img="'+img_+'"><a href="../i/m_dialog/temp/'+img_+'" target="_blank">'+t_txt+'</a><div class="m_postav_mess_files_text"><input class="m_postav_mess_files_text" placeholder="Название файла" value="'+name_+'" /></div><i title="Удалить документ" class="fa fa-remove"></i></li>');
                    
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
    
    //ЗАГРУЗКА ДОКУМЕНТОВ В ЗАКАЗЕ
    var upload_photo_m_postav = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : 'm_postav_add__docs_load_com',
        url : 'ajax/m_postav.php',
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
                {title : "Image files", extensions : "jpg,jpeg,gif,png"},
                { title : "Zip files", extensions : "zip,rar" },
                { title : "docs files", extensions : "doc,docx,xls,xlsx,ppt,pptx,pdf,psd,txt" }
            ]
        },
    	flash_swf_url : 'js/Moxie.swf',
    	silverlight_xap_url : 'js/Moxie.xap',
        preinit : {
                Init: function(up, info) {
                
                },
                UploadFile: function(up, file) {
                   up.setOption('multipart_params', {'_t' : 'm_postav_upload_docs'});
                }
            },
            init : {
                QueueChanged: function(up) {upload_photo_m_postav.start();},
        		BeforeUpload: function(up, file) {
        		  $('.m_postav_add__docs_load').append('<span class="loading"><img src="i/l_20_w.gif"> Загрузка...</span>');
                },
                FileUploaded: function(up, file, info) {
                    $('.m_postav_add__docs_load .loading').detach();
                    
                    var arr = (info.response).split('@@');
                    
                    var img_=arr[0];
                    var name_=arr[1];
                    
                    var reg_f=/.*?\./;
                    var ext=img_.replace(reg_f, "");
                    
                    var t_txt='';
                    if (ext=='jpg' || ext=='jpeg' || ext=='gif' || ext=='png'){
                        t_txt='<div class="photo_res__item_image" style="background: url(../i/m_postav/temp/'+img_+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></div>';
                    }
                    else if(ext=='docx' || ext=='doc'){
                        t_txt='<i class="fa fa-file-word-o"></i>';
                    }
                    else if(ext=='xlsx' || ext=='xls'){
                        t_txt='<i class="fa fa-file-excel-o"></i>';
                    }
                    else if(ext=='zip' || ext=='rar'){
                        t_txt='<i class="fa fa-file-zip-o"></i>';
                    }
                    else if(ext=='txt'){
                        t_txt='<i class="fa fa-file-text-o"></i>';
                    }
                    else if(ext=='psd'){
                        t_txt='<i class="fa fa-file-picture-o"></i>';
                    }
                    else if(ext=='pdf'){
                        t_txt='<i class="fa fa-file-pdf-o"></i>';
                    }
                    else{
                        t_txt='<i class="fa fa-file"></i>';
                    }
                    $('.m_postav_add__docs_res ul').append('<li class="photo_res__mess" data-img="'+img_+'"><a href="../i/m_postav/temp/'+img_+'" target="_blank">'+t_txt+'</a><div class="m_postav_add__docs_res_div"><input class="m_postav_add__docs_res_text" placeholder="Название файла" value="'+name_+'" /></div><i title="Удалить документ" class="fa fa-remove"></i></li>');
                    $('.m_postav_add__docs_res ul').sortable();
                }
            }
            });
        upload_photo_m_postav.init();
        
        upload_photo_m_postav.bind('Error', function(up, err) {
            var err_text='';
            if (err.message=='File size error.'){err_text+='Превышен размер загружаемого файла!<br />';}
            if (err.message=='File extension error.'){err_text+='Не верный тип файла!<br />';}
            
            if (err_text!=''){alert_m(err_text,'','error','none');}
        });
    
        //Удаление загруженных документов
        $(document).delegate('.photo_res__mess .fa-remove','click',function(){
            $(this).closest('.photo_res__mess').detach();
            
        });
        
        //Отправка сообщения
        $(document).delegate('.m_postav_send_mess','click',function(){
            
            var nomer_=$('.m_postav_add_head h2 input[name="nomer"]').val()
            if (nomer_==''){
                m_postav_add_form__save(function(data_n){
                    find();
                    send_mess(data_n.nomer,function(){
                        find_mess(data_n.nomer);
                    });
                });
            }else{
                send_mess(nomer_,function(){
                    find_mess(nomer_);
                });
            }
            
        });
    
    //Удаление сообщения
    $(document).delegate('.m_postav_all_mess .command .fa-remove','click',function(){
        
        mess_del($(this).closest('li').data('id'),function(){
            
        });
        $(this).closest('li').detach();
    });
    
    //проверка доставки
    $(document).delegate('input[name="m_dostavka_adress"], input[name="m_dostavka_tracking_number"], input[name="m_dostavka_summa"]','keyup',function(){
        chk_dostavka();
    });
    $(document).delegate('select[name="i_tk_id"]','change',function(){
        chk_dostavka();
    });
    
    //выбор филиала
    $(document).delegate('.i_tp_span .i_tp_span_cur .i_tp_span_cur_txt','click',function(){
        var th_=$(this);
        var i_tp_id=$(this).closest('.i_tp_span_cur').data('id');
        th_.closest('.i_tp_span_cur').html('<select class="i_tp_span_cur_select">'+th_.closest('.i_tp_span').find('select').html()+'</select>');
        $('.i_tp_span_cur_select').val(i_tp_id);
        $('.i_tp_span_cur_select').select2({'width':'100%'}).select2("open").on("select2:closing", function(){
            $('.i_tp_span_cur_select').trigger('change');
        });
    });
    $(document).delegate('.i_tp_span_cur_select','change',function(){
        
        var th_=$(this).closest('.i_tp_span').find('.i_tp_span_cur');
        var i_tp_id_new=$(this).val();
        var i_tp_name_new=$(this).find('option:selected').text();
        th_.data('id',i_tp_id_new).html('<span class="i_tp_span_cur_txt">'+i_tp_name_new+'</span>');
        chk_filial();
    });
    $(document).delegate('.i_tp_span_cur_select','blur',function(){
        $(this).trigger('change');
    });
    
    
    //Поиск
    $(document).delegate('.m_postav_fillter__find_txt input','keyup',function(e){
        if (e.which==13){
            find();
        }
    });
    $(document).delegate('.m_postav_fillter__find_txt .fa-search','click',function(){
       find(); 
    });
    //Поиск
    $(document).delegate('.m_postav_fillter__control_num input','keyup',function(e){
        if (e.which==13){
            find();
        }
    });
    $(document).delegate('.m_postav_fillter__control_num .fa-search','click',function(){
       find(); 
    });

    //движение формы добавления поступления
    
    var m=getPageSize();
    
    $(window).scroll(function () {
        var m=getPageSize();
        var top_0=($('.left_podmenu_div:visible ul').outerHeight()+$('.header_block').outerHeight()-0);
        
        var pos_=$('.m_postav_add_form_div').css('position');
       if (pos_=='fixed'){//Для полного экрана (не для мобильной верстки)
        $('.m_postav_add_form_div').css({'height':$(window).height()});
		if ($(this).scrollTop() > 0) {
		      var top_=top_0-$(this).scrollTop();
              if (top_<0){top_=0;}
			$('.m_postav_add_form_div').css({'top':top_});
            $('.m_postav_add_form_div').css({'height':$(window).height()});
		} else {
			$('.m_postav_add_form_div').css({'top':top_0+'px'});
            $('.m_postav_add_form_div').css({'height':$(window).height()});
		}
        }else{
            $('.m_postav_add_form_div').css({'height':'inherit'});
            $('.m_postav_add_form_div').css({'top':'0'});
        }
	});
    $(window).scroll();
    $(window).resize(function(){$(window).scroll();})
    
    //Открытие окна подробной информации
    $(document).delegate('.m_postav_add__html_code','click',function(){
        var html_=$('.m_postav_add__html_code_hide').html();
        var html_txt='<div class="m_postav_add__html_code__modal">';
        html_txt+='<p>Описание проекта</p>';
        html_txt+='<div><textarea id="ckeditor1" name="m_postav_add__html_code_txt">'+html_+'</textarea></div>';
        html_txt+='<div><center><span class="m_postav_add__html_code_save btn_orange">Сохранить</span></center></div>';
        html_txt+='</div>';
        alert_m(html_txt,'','html_code__info','none');
        var edit_=CKEDITOR.replace('ckeditor1',{
            allowedContent:true,
            toolbar: [
            { name: 'document', groups: [ 'mode', 'document' ], items: [ 'Source','-', 'Templates', 'autosaveKey' ] },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
            { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
            { name: 'insert', items: [ 'Image',  'Table', 'toolbar','HorizontalRule'] },
            '/',
            { name: 'styles', items: [ 'Styles', 'Format',  'FontSize' ] },
            { name: 'colors', items: [ 'TextColor' ] },
            { name: 'tools', items: [ 'Maximize' ] },
            
        ]
        });
        AjexFileManager.init({returnTo: 'ckeditor', editor: edit_});
    });
    
    //Сохранение окна подробной информации
    $(document).delegate('.m_postav_add__html_code_save','click',function(){
        $('.m_postav_add__html_code_hide').html(CKEDITOR.instances.ckeditor1.getData());
        $('.m_postav_add__html_code__modal').arcticmodal('close');
    });
    
    
    //РАСПЕЧАТАТЬ РКО
    $(document).delegate('.m_postav_print_rko','click',function(){
        var m_platezi_id=$(this).closest('.ttable_tbody_tr').data('id');
        if (typeof m_platezi_id=='undefined' || m_platezi_id==''){
            var i = $('.m_postav_print_rko').index($(this));
          
            m_postav_add_form__save(function(){
                var m_platezi_id=$('.m_postav_print_rko:eq('+i+')').closest('.ttable_tbody_tr').data('id');
                window.location.href = "?inc=i_docs&com=print&file_name=docx_rko&nomer="+m_platezi_id;
            });
        }
        else{
            window.location.href = "?inc=i_docs&com=print&file_name=docx_rko&nomer="+m_platezi_id;
        }
    });
    //РАСПЕЧАТАТЬ ПКО
    $(document).delegate('.m_postav_print_pko','click',function(){
        var m_platezi_id=$(this).closest('.ttable_tbody_tr').data('id');
        if (typeof m_platezi_id=='undefined' || m_platezi_id==''){
            var i = $('.m_postav_print_pko').index($(this));
          
            m_postav_add_form__save(function(){
                var m_platezi_id=$('.m_postav_print_pko:eq('+i+')').closest('.ttable_tbody_tr').data('id');
                window.location.href = "?inc=i_docs&com=print&file_name=docx_pko&nomer="+m_platezi_id;
            });
        }
        else{
            window.location.href = "?inc=i_docs&com=print&file_name=docx_pko&nomer="+m_platezi_id;
        }
    });
    
    
    
    //Ввод  штрих-кодов товара
    $(document).delegate('.s_cat_barcode_tovar','click',function(){
        var th_=$(this);
        
        s_cat_barcode_tovar_form(th_,function(th_){
            $('.barcode_save').click(function(){
                s_cat_barcode_tovar_form_save(th_,function(){
                    chk_barcode();
                    $.arcticmodal('close');
                });
            });
        });
    });
    
    
    //Перевод форуса при вводе штрихкодов
    $(document).delegate('.barcode_item','keyup',function(e){
        if (e.which==13){
            var t=$(this).closest('p').next().find('input').attr('type');
            if (typeof t!='undefined'){
                $(this).closest('p').next().find('input').focus();
            }else{
                $('.barcode_save').trigger('click');
            }
        }
    });
    $(document).delegate('.barcode_part','keyup',function(e){
        if (e.which==13){
            $('.barcode_save').trigger('click');
        }
    });
    
    //К заказу
    /*
    $('select[name="m_zakaz_id"]').select2({'width':'100%',allowClear: true,closeOnSelect:false,
        ajax: {
            url: "ajax/m_postav.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, 
                    page: params.page,
                    _t:'m_zakaz_id_autocomplete'
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
    */
    
    
    //Отобразить весь товар в поставке
    $(document).delegate('.m_postav_item__view_all_tr','click',function(){
        var th_=$(this);
        th_.closest('.ttable').find('.ttable_tbody_tr').css({'display':'table-row'});
        th_.detach();
    });
    
    
    //Отменяем поступление
    $(document).delegate('.m_postav_add_form__close','click',function(){
        var nomer=$('input[name="nomer"]').val();
        m_postav_close(nomer,function(){
            find();
            postav_load(nomer,function(){
               if (typeof callback=='function'){callback(data_n);}
            });
        });
    });
    
    //Открываем поступление
    $(document).delegate('.m_postav_add_form__plus','click',function(){
        var nomer=$('input[name="nomer"]').val();
        m_postav_open(nomer,function(){
            find();
            postav_load(nomer,function(){
               if (typeof callback=='function'){callback(data_n);}
            });
        });
    });
    
    
        
     //Открываем фильтр
     $(document).delegate('.view_all_fillter','click',function(){
        if ($('.display_none').size()>0){
            $(this).addClass('view_all_fillter_none').removeClass('view_all_fillter_inherit').text('Скрыть фильтр');
            $('.display_none').addClass('display_inherit').removeClass('display_none');
        }
        else{
            $(this).addClass('view_all_fillter_inherit').removeClass('view_all_fillter_none').text('Открыть фильтр');
            $('.display_inherit').addClass('display_none').removeClass('display_inherit');
        }
        
     });
    
    
     //Открываем добавление поступления
     $(document).delegate('.m_postav_add_form_open_close_div','click',function(){
        var th_=$(this).closest('.m_postav_add_form');
        var cl_=th_.attr('class');
        if ((cl_.split('m_postav_add_form_open').length - 1)>0){
            $(this).addClass('m_postav_add_form_open_div').removeClass('m_postav_add_form_close_div').text('Открыть форму добавления поступления');
            th_.addClass('m_postav_add_form_close').removeClass('m_postav_add_form_open');
        }else{
            $(this).addClass('m_postav_add_form_close_div').removeClass('m_postav_add_form_open_div').text('Закрыть форму добавления поступления');
            th_.addClass('m_postav_add_form_open').removeClass('m_postav_add_form_close');
        }
     });
    
    
    //Назначить автоматически штрих-коды
    $(document).delegate('.m_postav_auto_barcode','click',function(){
        var i=0;
        $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
            var nom=$('input[name="date_info"]').val();
                nom=nom.replace(' ','');
                nom=nom.replace(':','');
                nom=str_replace('.','',nom);
                nom=nom+'_'+i;
            var th_=$(this);
            if (th_.find('.s_cat_barcode_tovar__error').size()>0){//не заполнены коды, заполнены не все коды или количество товара не совпадает с количеством кодов
                var kol_=th_.find('.find_tovar__kol_span').text()-0;
                var bar_code=th_.find('.s_cat_barcode_tovar_cur').text()//0||##444##@@##555##
                
                if (bar_code!=''){
                
                    
                    if ((bar_code.split('||').length - 1)>0){
                        
                        var bar_arr1=bar_code.split('||');
                        var bar_items=bar_arr1[1];//##444##@@##555##
                        var cnt_bar=(bar_items.split('@@').length);
                        var bar_arr2=bar_items.split('@@');
                        
                        if (cnt_bar>kol_){//убираем лишний код
                            var txt='';
                            for (var ii=0;ii<kol_;ii++){
                                if ((kol_-2)<=ii){
                                    if (txt!=''){txt+='@@';}
                                    txt+=bar_arr2[ii];
                                }
                            }
                            th_.find('.s_cat_barcode_tovar_cur').text(bar_arr1[0]+'||'+txt);
                        }
                        else if (cnt_bar<kol_){//добавляем недостающий код
                            var new_bar_code=bar_code;
                            for (var ii=cnt_bar; ii<kol_;ii++){
                                new_bar_code+='@@##'+nom+'_'+ii+'##';
                            }
                            th_.find('.s_cat_barcode_tovar_cur').text(new_bar_code);
                        }
                        
                    }else{
                       alert_m('Не верно указан штрих-код: '+bar_code,'','error','none'); 
                    }
                    
                }
                else{//штрих код не задан
                    if (kol_>10){//создаем штрих код для партийного товара
                        th_.find('.s_cat_barcode_tovar_cur').text('1||##'+nom+'_0##');
                    }
                    else{//создаем штрих код для штучного товара
                        var txt='';
                        for (var ii=0;ii<kol_;ii++){
                            if (txt!=''){txt+='@@';}
                            txt+='##'+nom+'_'+ii+'##';
                        }
                        th_.find('.s_cat_barcode_tovar_cur').text('0||'+txt);
                    }
                }
            }
            i++;
        });
        
        chk_barcode();
    });
    
    
    ///Ввод заказа к каждому товару
    $(document).delegate('.s_cat_m_zakaz_id_new','click',function(){
       var cur_m_zakaz_id_new=$(this).data('m_zakaz_id_new');
       var cur_m_zakaz_txt_new=$(this).data('m_zakaz_txt_new');
       txt_opt="";if (cur_m_zakaz_id_new!=''){txt_opt='<option selected="selected" value="'+cur_m_zakaz_id_new+'">'+cur_m_zakaz_txt_new+'</option>';}
        
       var txt='';
       txt+='<h2>Укажите номер заказа</h2>';
       txt+='<div class="s_cat_m_zakaz_id_new_alert"><select data-placeholder="Укажите заказ" name="m_zakaz_id_new">'+txt_opt+'</select></div>';
       txt+='<div class="s_cat_m_zakaz_id_new_save_div"><span data-id="'+$(this).closest('.ttable_tbody_tr').data('id')+'" class="btn_orange s_cat_m_zakaz_id_new_save">Сохранить</span></div>';
       
       alert_m(txt,'','ok','none');
       
        $('select[name="m_zakaz_id_new"]').select2({'width':'100%',allowClear: true,closeOnSelect:true,
            ajax: {
                url: "ajax/m_postav.php",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, 
                        page: params.page,
                        _t:'m_zakaz_id_autocomplete'
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
            
        }).on("select2:closing", function(){
            
                //$('.s_cat_m_zakaz_id_new_save').trigger('click');
            
        });
        
    });
    
    //Добаляем товар из справки в поступление
    $(document).delegate('.find_tovar_res_help_item_add','click',function(e){
        e.preventDefault();
        var th_=$(this);
        var th_zakaz=th_.closest('.find_tovar_res_help_item');
        var m_zakaz_id_new=th_zakaz.data('id');
        var m_zakaz_id_txt=th_zakaz.find('.find_tovar_res_help_zakaz_name').text();
        var id_=th_.data('id');
        var kolvo=th_.data('kolvo');
        th_.closest('p').detach();
        if (th_zakaz.find('p').length==0){th_zakaz.detach();}
        s_cat_add_from_id(id_,kolvo,m_zakaz_id_new,m_zakaz_id_txt);
    });
    
     
    //Клонирование поступления
    $(document).delegate('.m_postav_add_form__clone','click',function(){
            
        $('.m_postav_add_head h2').html('Добавление поступления <input type="hidden" value="" name="nomer">');
        $('.m_postav_add__project_name').val('');
        //удаляем классы
        $('.status_order_div').removeClass('no_full_pay').removeClass('no_pay').removeClass('max_full_pay').removeClass('full_pay')
        .removeClass('no_end').removeClass('full_end').removeClass('no_full_end').removeClass('closed');
        
        $('.status_order_div').hide();//убираем статус заказа
        //Формирование ссылки HISTORY
        history_url='?inc=m_postav';
        history_name='Поступление товаров';
        History.replaceState({state:3}, history_name, history_url);
        
        chk_i_contr();
        chk_summ();//Проверяем сумму
        chk_comments(); //проверяем комментарии
        chk_pl();//проверка платежей
    });
    
    
    $(document).delegate('.s_cat_m_zakaz_id_new_save','click',function(){
        var m_zakaz_id_new=$('select[name="m_zakaz_id_new"]').val();
        var th_=$('.find_tovar_res__tbl .ttable_tbody_tr[data-id="'+$(this).data('id')+'"]').find('.s_cat_m_zakaz_id_new');
        
        th_.data('m_zakaz_id_new',$('select[name="m_zakaz_id_new"]').val()).data('m_zakaz_txt_new',$('select[name="m_zakaz_id_new"] option:selected').text());
        if (m_zakaz_id_new-0>0){th_.addClass('active');}
        else{th_.removeClass('active');}
        $('.s_cat_m_zakaz_id_new_save').arcticmodal('close');
    });
    <?php
    
    if (_GP('nomer')!=''){
        ?>
        postav_load('<?=_GP('nomer');?>');
        <?php
    }
    
    
    //Добавление товара или услуги в заказ
    if (_GP('add_id')!=''){
        ?>
        s_cat_add_from_id('<?=_GP('add_id');?>');
        <?php
    }
    
    ?>
});
</script>