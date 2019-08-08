<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script type="text/javascript">

//поиск
function find(limit,callback){
    limit=limit || '';
    callback=callback || '';
    
    var err_text='';
    var data_=new Object();
    data_['_t']='m_platezi__find';
    data_['limit']=limit;
    data_['txt']=$('input[name="find_txt"]').val();
    data_['i_scheta_id']=$('select[name="i_scheta_id_find"]').val();
    data_['i_tp_id']=$('select[name="i_tp_id_find"]').val();
    data_['d1']=$('input[name="m_platezi_data1_find"]').val();
    data_['d2']=$('input[name="m_platezi_data2_find"]').val();
    data_['tip']=sel_in_array($('.m_platezi_fillter__tip_ul li.active'),'data','id');
    data_['del_active']=$('.m_platezi_fillter__active_remove li.active').data('id')
    data_['sort']=$('.m_platezi_fillter__sort_ul li.active').data('id')
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$('.m_platezi_find_res_loading').html('<p class="m_platezi__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    		    $('.m_platezi_find_res_loading .m_platezi__loading').detach();
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt='';
                    var cnt_=0;//общее количество
    	            if (typeof data_n.p!='undefined' && typeof data_n.p.i=='object'){
                       var cnt_=data_n.cnt_;//общее количество
                       var data_days='';
                       var cl_tr='data_odd_class';
    	               for(var i in data_n.p.i){
    	                   var a_admin_info=$('select[name="a_admin"] option[value="'+data_n.p.aai[i]+'"]').text();
                            if (a_admin_info==''){a_admin_info=data_n.p.aai[i];}
    	                   var schet_name=$('select[name="i_scheta"] option[value="'+data_n.p.sch[i]+'"]').text();
                           var comm_=data_n.p.c[i];
                            if (comm_==''){comm_='<i class="fa fa-plus quick_change_pl_comments"></i>';}
                            else{comm_='<span class="quick_change_pl_comments">'+comm_+'</span>'}
                            
                            var info_='';
                            var tip_='';
                            if (data_n.p.a[i]=='16'){//заказы
                                info_='<a href="?inc=m_zakaz&nomer='+data_n.p.ii[i]+'" target="_blank">Заказ №'+data_n.p.ii[i]+'</a>';
                                tip_='Заказ';
                            }
                            else if (data_n.p.a[i]=='17'){//поступления
                                info_='<a href="?inc=m_postav&nomer='+data_n.p.ii[i]+'" target="_blank">Поступление №'+data_n.p.ii[i]+'</a>';
                                tip_='Поступление';
                            }
                            else if (data_n.p.a[i]=='4'){//З/П
                                info_='з/п '+$('select[name="a_admin_id"] option[value="'+data_n.p.ii[i]+'"]').text()+'';
                                tip_='З/П';
                            }
                            else if (data_n.p.a[i]=='40'){//реклама
                                info_='Реклама №'+data_n.p.ii[i];
                                tip_='Реклама';
                            }
                            else if (data_n.p.a[i]=='100'){//Расходы
                                info_=''+$('.m_platezi_i_rashodi_select select[name="i_rashodi_id"] option[value="'+data_n.p.ii[i]+'"]').text();
                                tip_='Расходы';
                            }
                            else if (data_n.p.a[i]=='42'){//переводы
                                if ((data_n.p.sk[i]-0)>0){
                                   info_='Перевод с счета: ';
                                }
                                if((data_n.p.sd[i]-0)>0){
                                    info_='Перевод на счет: ';
                                }
                                info_+=$('select[name="i_scheta"] option[value="'+data_n.p.ii[i]+'"]').text();
                                tip_='Перевод';
                            }
                            else if (data_n.p.a[i]=='105'){//ввод/вывод
                                if ((data_n.p.sk[i]-0)>0){
                                    
                                    tip_='Ввод';
                                }
                                if((data_n.p.sd[i]-0)>0){
                                    
                                    tip_='Вывод';
                                }
                                info_+=''+$('select[name="i_inout_id"] option[value="'+data_n.p.ii[i]+'"]').text();
                                
                            }
                            else{
                                info_='Тип не определен! data_n.p.a[i]='+data_n.p.a[i];
                            }
                           var data_time=data_n.p.d[i];
                           var data_time_arr=data_time.split(' ');
                           cl_tr2='';
                            if (data_time_arr[0]!=data_days){
                                //if (cl_tr=='data_odd_class'){cl_tr='data_even_class';}
                                //else{cl_tr='data_odd_class';}
                                data_days=data_time_arr[0];
                                cl_tr2=" m_platezi_data_class_line";
                            }
                           
                           var cl_1='';var title_1='';
                           var cl_2='';var title_2='';var print_='Распечатать РКО';var print_cl='docx_rko';cl_tr='data_even_class';
                           if (data_n.p.sk[i]>0){cl_tr='data_odd_class';print_cl='docx_pko';print_='Распечатать ПКО';cl_1=" thumbnail";title_1='<span style="width:200px;">Остаток: '+number_format(data_n.p.o[i],0,'.',' ')+' руб.</span>';}
                           else{cl_2=" thumbnail";title_2='<span style="width:200px;">Остаток: '+number_format(data_n.p.o[i],0,'.',' ')+' руб.</span>';}
    	                       
                           
    	                   txt+='<div class="ttable_tbody_tr '+cl_tr+cl_tr2+'" data-id="'+data_n.p.i[i]+'">';
    	                       txt+='<div class="ttable_tbody_tr_td pl_id"><span class="for_mobile">№</span><span class="val thumbnail">'+data_n.p.i[i]+'<span>Создатель платежа: <br /><strong>'+a_admin_info+'</strong></span></span></div>';
                               var dd_='<p>Дата создания: <strong>'+data_n.p.dc[i]+'</strong></p>';
                               if (data_n.p.dd[i]!=''){dd_+='<p>Дата удаления: <strong>'+data_n.p.dd[i]+'</strong></p>';}
    	                       txt+='<div class="ttable_tbody_tr_td pl_data"><span class="for_mobile">Дата</span><span class="val thumbnail">'+data_time+'<span>'+dd_+'</span></span></div>';
                               txt+='<div class="ttable_tbody_tr_td pl_prihod"><span class="for_mobile">Приход</span><span class="val'+cl_1+'"><strong>'+number_format(data_n.p.sk[i],0,'.',' ')+'</strong>'+title_1+'</span></div>';
    	                       txt+='<div class="ttable_tbody_tr_td pl_rashod"><span class="for_mobile">Расход</span><span class="val'+cl_2+'"><strong>'+number_format(data_n.p.sd[i],0,'.',' ')+'</strong>'+title_2+'</span></div>';
    	                       txt+='<div class="ttable_tbody_tr_td pl_schet"><span class="for_mobile">Счет</span><span class="val">'+schet_name+'</span></div>';
    	                       txt+='<div class="ttable_tbody_tr_td pl_tip"><span class="for_mobile">Тип</span><span class="val">'+tip_+'</span></div>';
    	                       txt+='<div class="ttable_tbody_tr_td pl_naznachenie"><span class="for_mobile">Назначение</span><span class="val">'+info_+'</span></div>';
    	                       txt+='<div class="ttable_tbody_tr_td pl_comments"><span class="for_mobile">Комментарии</span><span class="val">'+comm_+'</span></div>';
                               if (data_['del_active']!='2'){
    	                           txt+='<div class="ttable_tbody_tr_td pl_funct"><span class="for_mobile">Комментарии</span><span class="val"><i class="fa fa-remove" title="Отменить платеж"></i> <a class="'+print_cl+'" href="?inc=i_docs&com=print&file_name='+print_cl+'&nomer='+data_n.p.i[i]+'"><i class="fa fa-print" title="'+print_+'"></i></a></span></div>';
    	                       }
                           txt+='</div>';
    	               }
    	            }
                    //если найдены результаты
                    if (txt!=''){
                        if (limit==''){
                            txt='<div class="ttable m_platezi_res_tbl"><div class="ttable_thead"><div class="ttable_thead_tr">'
                            +'<div class="ttable_thead_tr_th">№</div>'
                            +'<div class="ttable_thead_tr_th">Дата</div>'
                            +'<div class="ttable_thead_tr_th">Приход</div>'
                            +'<div class="ttable_thead_tr_th">Расход</div>'
                            +'<div class="ttable_thead_tr_th">Счет</div>'
                            +'<div class="ttable_thead_tr_th">Тип</div>'
                            +'<div class="ttable_thead_tr_th">Назначение</div>'
                            +'<div class="ttable_thead_tr_th">Комментарии</div>'
                            +'<div class="ttable_thead_tr_th">Фун.</div>'
                            +'</div></div><div class="ttable_tbody">'
                            +txt+'</div></div>';
                        }
                        
                        var all_saldo=data_n.kredit-data_n.debet;
                        $('.m_platezi_find_res_info .phihod_sum span').html(number_format(data_n.kredit,0,'.',' '));
                        $('.m_platezi_find_res_info .rashod_sum span').html(number_format(data_n.debet,0,'.',' '));
                        $('.m_platezi_find_res_info .ishod_ostatok span').html(all_saldo);
                        
                    }else{
                        txt='Результаты не найдены!';
                    }
                    
                    //Догрузка
                    var cur_cnt=($('.m_platezi_find_res>.ttable>.ttable_tbody>.ttable_tbody_tr').size()-0)+(count(data_n.p.i)-0);
                    if (cnt_>cur_cnt){
                        $('.m_platezi_find_res_loading').html('<div class="m_platezi__load_add">Загрузить ещё...</div>');
                    }
                    
                    if (limit==''){
                        $('.m_platezi_find_res').html(txt);
                    }else{//Догрузка
                        $('.m_platezi_find_res>.ttable>.ttable_tbody').append(txt);
                    }
                    
                    
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}

//СОХРАНЕНИЕ
function m_platezi_add_form__save(callback){
    callback=callback || '';
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='m_platezi__save';
    data_['nomer']=$('input[name="nomer"]').val();
    data_['summa']=str_replace(' ','',$('.m_platezi_add_form input[name="summa"]').val());
        if (data_['summa']<=0){err_text+='<p>Сумма платежа должна быть больше 0</p>';}

        
    data_['data']=$('.m_platezi_add_form input[name="date"]').val();
        if (data_['data']==''){err_text+='<p>Не указана дата платежа!</p>';}
        
    data_['i_scheta']=$('.m_platezi_add_form select[name="i_scheta"]').val();
    data_['a_admin']=$('.m_platezi_add_form select[name="a_admin"]').val();
    data_['pl_comments']=$('.m_platezi_add_form .pl_comments_textarea').val();
    data_['pl_tip']=$('.tabs_tip_platezi').tabs('option','active');
    data_['tip']='';
    
    if (data_['pl_tip']==0){ //Заказы
        data_['m_zakaz_id']=$('.m_platezi_add_form select[name="m_zakaz_id"]').val();
        if (data_['m_zakaz_id']==''){err_text+='<p>Укажите номер заказа!</p>';}
        data_['tip']=$('.m_platezi_add_form select[name="m_zakaz_tip"]').val();
    }
    else if (data_['pl_tip']==1){//поступления
        data_['m_postav_id']=$('.m_platezi_add_form select[name="m_postav_id"]').val();
        if (data_['m_postav_id']==''){err_text+='<p>Укажите номер поступления!</p>';}
        data_['tip']=$('.m_platezi_add_form select[name="m_postav_tip"]').val();
    }
    else if (data_['pl_tip']==2){//з/п
        data_['a_admin_id']=$('.m_platezi_add_form select[name="a_admin_id"]').val();
        if (data_['a_admin_id']==''){err_text+='<p>Укажите работника, кому выдается зарплата!</p>';}
        data_['tip']=$('.m_platezi_add_form select[name="a_admin_tip"]').val();
    }
    else if (data_['pl_tip']==3){//реклама
        data_['i_reklama_id']=$('.m_platezi_add_form select[name="i_reklama_id"]').val();
        if (data_['i_reklama_id']==''){err_text+='<p>Укажите рекламную компанию!</p>';}
        data_['tip']='Дебет';
    }
    else if (data_['pl_tip']==4){//Расходы
        data_['i_rashodi_id']=$('.m_platezi_add_form select[name="i_rashodi_id"]').val();
        if (data_['i_rashodi_id']==''){err_text+='<p>Укажите тип расхода!</p>';}
        data_['tip']='Дебет';
    }
    else if (data_['pl_tip']==5){//Переводы
        data_['i_scheta_id']=$('.m_platezi_add_form select[name="i_scheta_id"]').val();
        if (data_['i_scheta_id']==''){err_text+='<p>Укажите счет получателя!</p>';}
        data_['tip']='Дебет';
    }
    else if (data_['pl_tip']==6){//ввод/вывод
        data_['i_inout']=$('.m_platezi_add_form select[name="i_inout_id"]').val();
        if (data_['i_inout']==''){err_text+='<p>Укажите назначение ввода/вывода!</p>';}
        data_['tip']=$('.m_platezi_add_form select[name="i_inout_tip"]').val();
    }
    
    
    if (data_['tip']=='Дебет'){
        var cur_sum_in_schet=$('.m_platezi_add_schet_info').data('summa');
        if (typeof cur_sum_in_schet=='undefined' || (cur_sum_in_schet-0)<data_['summa']){
            err_text+='<p>На счете <strong>'+$('.m_platezi_add_schet_info select[name="i_scheta"] option:selected').text()+'</strong> не достаточно средств для совершения платежа</p>';
        }
    }
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
                loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    alert_m('Платеж успешно добавлен!');
                    $('.reload_schet_info').trigger('click');
                    add_platezi_form_clear();
                    chk_all_summa();
    	            find();
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}


//Очистка формы добавления
function add_platezi_form_clear(){
    
    var dt_ = new Date();
    var h=dt_.getHours();
    var m=dt_.getMinutes();
    var s=dt_.getSeconds();
    

    
    $('input[name="date"], .pl_data').val(dt_.toLocaleDateString()+' '+PrefInt(h,2)+':'+PrefInt(m,2));
    
    $('.pl_comments_textarea, .i_reklama_comments, .m_platezi_add_form_div input[name="summa"]').val('');
    
    $('.m_platezi_add_head h2').html('Добавление платежа <input type="hidden" value="" name="nomer">');
    $('.m_platezi_add_form select[name="m_zakaz_id"]').html('').trigger('change');
    $('.m_platezi_add_form select[name="m_postav_id"]').html('').trigger('change');
}

//Получаем информацию по счету
function get_schet_summa(i_scheta_id,callback){
    i_scheta_id=i_scheta_id || '-1';
    callback=callback || '';

    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='m_platezi_get_schet_summa';
    data_['i_scheta_id']=i_scheta_id;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			
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
function chk_all_summa(callback){
    callback=callback || '';
    get_schet_summa('-1',function(sum_obj){
            if (typeof sum_obj!='undefined' && typeof sum_obj.s!='undefined'){
                if (typeof sum_obj.s=='object'){
                    var all_sum=0;
                    for(var i_scheta_id in sum_obj.s){
                        $('.i_schet_information .i_schet_item[data-id="'+i_scheta_id+'"]').find('.i_schet_item_sum strong').html(number_format(sum_obj.s[i_scheta_id],0,'.',' '));
                        all_sum=(all_sum-0)+(sum_obj.s[i_scheta_id]-0);
                    }
                    $('.i_schet_information .i_schet_item[data-id="-1"]').find('.i_schet_item_sum strong').html(number_format(all_sum,0,'.',' '));
                }
            }
            if (typeof callback=='function'){callback();}
        });
}


//быстрое сохранение
function quick_change_pl(nomer,col,val,callback){
    nomer=nomer || '';
    col=col || '';
    val=val || '';
    callback=callback || '';
    
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='quick_change_pl';
    data_['nomer']=nomer;
    data_['col']=col;
    data_['val']=val;
        if (nomer-0<=0){err_text+='Не определен номер!';}
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
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

//Получение информации о заказе
function get_info_from_zakaz(){
    var err_text='';
    var data_=new Object();
    data_['_t']='get_info_from_zakaz';
    data_['nomer']=$('select[name="m_zakaz_id"]').val();
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$('.m_platezi_m_zakaz_info').html('<span class="ico ico_loading"></span>');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof data_n.d!='undefined'){
    	               var sum=data_n.s-data_n.p;
                       var txt_status='';
                       if (sum==0){txt_status='<h2 style="color:#090;">Заказ оплачен полностью</h2>';}
                       if (data_n.p==0){txt_status='<h2 style="color:#900;">Заказ не оплачен</h2>';}
                       if (data_n.p>0 && sum>0){txt_status='<h2 style="color:#009;">Заказ частично оплачен</h2>';}
                       if (sum<0){txt_status='<h2 style="color:#f00;">Переплата по заказу</h2>';}
                       
    	               var txt=txt_status+'<p><a href="?inc=m_zakaz&nomer='+data_['nomer']+'" target="_blank">Заказ №<strong>'+data_['nomer']+'</strong> от '+data_n.d+'</a></p>';
                       txt+='<p>Покупатель: <strong>'+data_n.n+'</strong></p>';
                       txt+='<p>Итого '+data_n.c+' позиц'+end_word(data_n.c,'ий','ия','ии')+' на сумму: <strong>'+number_format(data_n.s,0,'.',' ')+'</strong> руб.</p>';
                       txt+='<p>Оплачено: <strong>'+number_format(data_n.p,0,'.',' ')+'</strong> руб.</p>';
                       $('select[name="m_zakaz_tip"]').val('Кредит').trigger('change');
                       if (sum>0){
                            txt+='<h2 style="color:#090;">К оплате: <strong>'+number_format(sum,0,'.',' ')+'</strong> руб.</h2>';
                       }
                       else if (sum<0){
                            $('select[name="m_zakaz_tip"]').val('Дебет').trigger('change');
                            txt+='<h2 style="color:#900;">К воврату: <strong>'+number_format(sum,0,'.',' ')+'</strong> руб.</h2>';
                            sum=sum*(-1);
                       }
                       $('.m_platezi_add_main_info input[name="summa"]').val(sum);
    	               $('.m_platezi_m_zakaz_info').html(txt);
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}
    		}
    	});
    }
}

//Получение информации о поступлении
function get_info_from_postav(){
    var err_text='';
    var data_=new Object();
    data_['_t']='get_info_from_postav';
    data_['nomer']=$('select[name="m_postav_id"]').val();
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$('.m_platezi_m_postav_info').html('<span class="ico ico_loading"></span>');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof data_n.d!='undefined'){
    	               var sum=data_n.s-data_n.p;
                       var txt_status='';
                       if (sum==0){txt_status='<h2 style="color:#090;">Поступление оплачено полностью</h2>';}
                       if (data_n.p==0){txt_status='<h2 style="color:#900;">Поступление не оплачено</h2>';}
                       if (data_n.p>0 && sum>0){txt_status='<h2 style="color:#009;">Поступление частично оплачено</h2>';}
                       if (sum<0){txt_status='<h2 style="color:#f00;">Переплата по поступлению</h2>';}
                       
    	               var txt=txt_status+'<p><a href="?inc=m_postav&nomer='+data_['nomer']+'" target="_blank">Поступление №<strong>'+data_['nomer']+'</strong> от '+data_n.d+'</a></p>';
                       txt+='<p>Поставщик: <strong>'+data_n.n+'</strong></p>';
                       txt+='<p>Итого '+data_n.c+' позиц'+end_word(data_n.c,'ий','ия','ии')+' на сумму: <strong>'+number_format(data_n.s,0,'.',' ')+'</strong> руб.</p>';
                       txt+='<p>Оплачено: <strong>'+number_format(data_n.p,0,'.',' ')+'</strong> руб.</p>';
                       $('select[name="m_postav_tip"]').val('Дебет').trigger('change');
                       if (sum>0){
                            txt+='<h2 style="color:#090;">К оплате: <strong>'+number_format(sum,0,'.',' ')+'</strong> руб.</h2>';
                       }
                       else if (sum<0){
                            $('select[name="m_postav_tip"]').val('Кредит').trigger('change');
                            txt+='<h2 style="color:#900;">К воврату: <strong>'+number_format(sum,0,'.',' ')+'</strong> руб.</h2>';
                            sum=sum*(-1);
                       }
                       $('.m_platezi_add_main_info input[name="summa"]').val(sum);
    	               $('.m_platezi_m_postav_info').html(txt);
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}
    		}
    	});
    }
}

//Расчет зарплаты работника
function get_info_from_zp(){
    var err_text='';
    var data_=new Object();
    data_['_t']='get_info_from_zp';
    data_['a_admin_id']=$('.m_platezi_a_admin_select select[name="a_admin_id"]').val();
        if (data_['a_admin_id']==''){err_text+='<p>Укажите работника!</p>';}
    data_['a_admin_i_post_id']=$('.m_platezi_a_admin_i_post_select select[name="a_admin_i_post"]').val();
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$('.m_platezi_a_admin_info').html('<span class="ico ico_loading"></span>');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    		  $('.m_platezi_a_admin_info').html('');
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt='';
                    var all_sum=0;
    	            if (typeof data_n.i_post=='object'){
    	               for (var i_post_id in data_n.i_post){//перебор по должностям
    	                   
                           var txt_dol='';
                           var sum_dol=0;
                           for (var i_zp_id in data_n.i_obj_target[i_post_id]){//перебор по начислениям на должность
                                var sum_cur=data_n.zp_cur_zp_summ[i_post_id][i_zp_id];
                                sum_dol=(sum_dol-0)+(sum_cur-0);
                                txt_dol+='<p>'+data_n.i_obj_target[i_post_id][i_zp_id]+' ('+data_n.i_zp_val[i_post_id][i_zp_id]+'): <strong>'+sum_cur+'</strong> руб.</p>';
                                if (typeof data_n.zp_cur_zp_zakaz!='undefined' && typeof data_n.zp_cur_zp_zakaz[i_post_id]!='undefined'){
                                    for (var jj in data_n.zp_cur_zp_zakaz[i_post_id][i_zp_id]){
                                        var m_zakaz_id= data_n.zp_cur_zp_id[i_post_id][i_zp_id][jj];
                                        var ddt= data_n.zp_cur_zp_dt[i_post_id][i_zp_id][jj];
                                        var ddtc= data_n.zp_cur_zp_dtc[i_post_id][i_zp_id][jj];
                                        if (data_n.i_obj_target[i_post_id][i_zp_id]=='Фиксированная сумма с поступления: авто' || data_n.i_obj_target[i_post_id][i_zp_id]=='Процент с маржи проданного товара из поступления: авто' || data_n.i_obj_target[i_post_id][i_zp_id]=='Фиксированная сумма с поступления: вручную'){
                                            txt_dol+='<p><a target="_blank" href="?inc=m_postav&nomer='+m_zakaz_id+'">Поступление ';
                                        }
                                        else{
                                            txt_dol+='<p><a target="_blank" href="?inc=m_zakaz&nomer='+m_zakaz_id+'">Заказ ';
                                        }
                                        txt_dol+='№'+m_zakaz_id+'</a> = <strong>'+data_n.zp_cur_zp_zakaz[i_post_id][i_zp_id][jj]+'</strong> (<span>'+ddt+'</span> - <span>'+ddtc+'</span>)</p>';
                                    }
                                }
                           }
                           all_sum=(all_sum-0)+(sum_dol-0);
                           txt+='<div class="s_post_res_block" data-i_post_id="'+i_post_id+'"><h2>'+data_n.i_post[i_post_id]+': <strong>'+sum_dol+'</strong> руб. <span class="s_post_res_block_view">развернуть</span></h2><div class="s_post_res_block_info">'+txt_dol+'</div></div>';
    	               }
                       
                       $('select[name="a_admin_tip"]').val('Дебет').trigger('change');
                       
                       var txt_all='';
                       var all_sum_all=(all_sum-0)-(data_n.summa_debet-0)+(data_n.summa_kredit-0);
                       
                       txt_all+='<h2 style="color:#090;">К выдаче: <strong>'+all_sum_all+'</strong> руб.</h2><hr>';
                       
                       if (all_sum>0){//заработано
                        txt_all+='<h2 style="color:#090;">Итого заработанно: <strong>'+all_sum+'</strong> руб.</h2>';
                       }
                       else if(all_sum==0){
                        txt_all+='<h2 style="color:#009;">Средств к выдаче нет</h2>';
                       }
                       else if (all_sum<0){
                        $('select[name="a_admin_tip"]').val('Кредит').trigger('change');
                        txt_all+='<h2 style="color:#900;">Перебор по зарплате: <strong>'+all_sum+'</strong> руб.</h2>';
                       }
                       
                       if (data_n.summa_debet-0>0){ // выдано
                        txt_all+='<h2 style="color:#900;">Итого выдано работнику: <strong>'+data_n.summa_debet+'</strong> руб.</h2>';
                       }
                       if (data_n.summa_kredit-0>0){ // внесено
                        txt_all+='<h2 style="color:#009;">Итого внесено работником: <strong>'+data_n.summa_kredit+'</strong> руб.</h2>';
                       }
                       
                       
                       txt=txt_all+'<hr>'+txt;
                       $('.m_platezi_add_main_info input[name="summa"]').val(all_sum_all);
    	            }
                    $('.m_platezi_a_admin_info').html(txt);
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

//Удаление платежа
function pl_remove(id,pass,callback){
    callback=callback || '';
    id = id || '';

    var err_text='';
    var data_=new Object();
    data_['_t']='pl_remove';
    data_['id']=id;
        if (data_['id']==''){err_text!='<p>Не определен номер платежа!</p>';}
    data_['pass']=pass;
        if (data_['pass']==''){err_text!='<p>Пароль не может быть пустым!</p>';}
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	loading(1);
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			loading(0);
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof callback=='function'){callback(data_n);}
                    var txt='Платеж №'+data_n.id+' успешно удален!';
                    $.arcticmodal('close');
                    alert_m(txt);
    			}
    			else{
    				alert_m(data,function(){
    				    $('input[name="pl_remove_pass"]').select().focus();
    				},'error','none');
    			}            
    		}
    	});
    }
    
    
}
//выбор филиала
function chk_filial(){
    
    var i_tp_id=$('select[name="a_admin"] option:selected').data('i_tp_id');
    $('select[name="i_scheta"] option[data-i_tp_id="'+i_tp_id+'"]').prop('disabled',false).attr('disabled',false);
    $('select[name="i_scheta"] option[data-i_tp_id="'+i_tp_id+'"]:not(:disabled):first').prop('selected','selected');
    $('select[name="i_scheta"] option[data-i_tp_id!="'+i_tp_id+'"]').attr('disabled','disabled');
    
    $('select[name="i_scheta"]').select2({'width':'100%'});
    
    $('select[name="i_tp_id_find"] option[value!="'+i_tp_id+'"]').prop('disabled','disabled').prop('selected',false);
    $('select[name="i_tp_id_find"] option[value="'+i_tp_id+'"]').prop('disabled',false);
    $('select[name="i_tp_id_find"] option[value="'+i_tp_id+'"]:first').prop('selected','selected');
    $('select[name="i_tp_id_find"]').select2({'width':'100%'});
    //alert(i_tp_id);
}
    
//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
$(document).ready(function(){
    
    window.onfocus = function () {chk_all_summa();}//при фокусе вкладки обновляем данные о счетах
    //*************** ДОБАВЛЕНИЕ ПЛАТЕЖА ******************************
    $('.tabs_tip_platezi').tabs();//табы
    
    //Добавление платежа
    $('.m_platezi_other_info input[name="date"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true});
    $('input[name="summa"]').autoNumeric('init');
    
    $('select[name="i_tp_id_find"]').select2({'width':'100%'});
    
    //Выбор счета
    $('select[name="i_scheta"]').select2({'width':'100%'}).change(function(){
        $('.m_platezi_add_schet_info').html('');
        get_schet_summa($(this).val(),function(sum_obj){
            if (typeof sum_obj!='undefined' && typeof sum_obj.s!='undefined'){
                if (typeof sum_obj.s=='object'){
                    for(var i_scheta_id in sum_obj.s){
                        $('.m_platezi_add_schet_info').data('summa',sum_obj.s[i_scheta_id]).html('<span class="cur_i_schet_info">На счете: <strong>'+number_format(sum_obj.s[i_scheta_id],0,'.',' ')+'</strong> руб.</span> <i class="add_all_summ fa fa-plus"></i> <i class="reload_schet_info fa fa-refresh"></i>');
                    }
                }
            }
        });
    });
    $('select[name="i_scheta"]').trigger('change');
    $(document).delegate('.reload_schet_info','click',function(){
        $('select[name="i_scheta"]').trigger('change');
    });
    
    //Получаем информацию по всем счетам
    chk_all_summa();
    chk_filial();

    //Выбор работника
    $('select[name="a_admin"]').select2({'width':'100%'}).change(function(){
        chk_filial();
        find();
    });

    
    //*************** ПОИСК ******************************
    //Форма поиска - дата
    $('input[name="m_platezi_data1_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(current_time,$input){
        find();
    }});
    $('input[name="m_platezi_data2_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(current_time,$input){
        find();
    }});
    
    $('.m_platezi_fillter__print').hide();//печать кассовой книги
    
    //Поиск по счетам
    $('select[name="i_scheta_id_find"]').select2({'width':'100%'}).change(function(){
        if ($(this).val()-0>0){
            $('.m_platezi_fillter__print').show();
        }else{
            $('.m_platezi_fillter__print').hide();
        }
        find();
    });
    
    //поиск по счету
    $(document).delegate('.i_schet_information .i_schet_item','click',function(){
        var id_=$(this).data('id');
        $('select[name="i_scheta_id_find"]').val(id_).trigger('change');
    });
    
    //Фильтр по напоминаниям
    $(document).delegate('.m_platezi_fillter__tip li','click',function(){
        var th_=$(this);
        var cl_=th_.attr('class');
        if (typeof cl_!='undefined' && (cl_.split('active').length - 1)>0){
            th_.removeClass('active');
        }else{
            th_.addClass('active');
        }
        find();
    });
    //Сортировка
    $(document).delegate('.m_platezi_fillter__sort_ul li','click',function(){
        var th_=$(this);
        $('.m_platezi_fillter__sort_ul li').removeClass('active');
        th_.addClass('active');
        find();
    });
    //Активные / удаленые
    $(document).delegate('.m_platezi_fillter__active_remove_ul li','click',function(){
        var th_=$(this);
        $('.m_platezi_fillter__active_remove_ul li').removeClass('active');
        th_.addClass('active');
        find();
    });
    //поиск по enter
    $(document).delegate('.m_platezi_fillter__find_txt input[name="find_txt"]','keyup',function(e){
        if (e.which==13){
            find();
        }
    });
    // поиск при щелчке по иконке
    $(document).delegate('.m_platezi_fillter__find_txt i','click',function(e){
        find();
    });
    
    // догрузка
    $(document).delegate('.m_platezi__load_add','click',function(e){
        var limit_=$('.m_platezi_find_res>.ttable>.ttable_tbody>.ttable_tbody_tr').size();
        find(limit_);
    });
    
    find();//начальный поиск
    
    
    //Информация
    $(document).delegate('.m_platezi_first_tbl .fa-info-circle','click',function(){
        if ($(this).find('>span').size()>0){
            alert_m($(this).find('>span').html(),'','info','none');
        }
    });
    
    //Быстрое изменение
    $(document).delegate('.quick_change_pl_comments','click',function(){
        var th_=$(this);
        var txt=th_.text();
        th_.closest('.val').html('<input type="text" value="'+_IN(txt)+'" placeholder="Комментарии" class="quick_change_pl_comments_input" />');
        $('.quick_change_pl_comments_input').focus();
    });
    
    //Сохранение комментариев
    $(document).delegate('.quick_change_pl_comments_input','blur',function(){
        var th_=$(this);
        var id_=th_.closest('.ttable_tbody_tr').data('id');
        var val_=th_.val();
        quick_change_pl(id_,'comments',val_,function(){
            
        });
        if (val_==''){
            val_='<i class="fa fa-plus quick_change_pl_comments"></i>';
        }else{
            val_='<span class="quick_change_pl_comments">'+val_+'</span>';
        }
        th_.closest('.val').html(val_);
    });
    // сохранение комментариев при enter
    $(document).delegate('.quick_change_pl_comments_input','keyup',function(e){
        if (e.which==13){
            $(this).trigger('blur');
        }
    });
    
    //автозаполнение заказа
    $('select[name="m_zakaz_id"]').select2({'width':'100%',allowClear: true,closeOnSelect:false,
        ajax: {
            url: "ajax/m_platezi.php",
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
    }).change(function(){
        //Получение информации о заказе
        get_info_from_zakaz();
    });
    
    //автозаполнение поступления
    $('select[name="m_postav_id"]').select2({'width':'100%',allowClear: true,closeOnSelect:false,
        ajax: {
            url: "ajax/m_platezi.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, 
                    page: params.page,
                    _t:'m_postav_id_autocomplete'
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
        //Получение информации о поступлении
        get_info_from_postav();
    });
    
    
    //Работник для расчета з/п
    $('select[name="a_admin_id"]').select2({'width':'100%',allowClear: true,closeOnSelect:false}).change(function(){
        get_info_from_zp();
    });
    //По кнопке расчета
    $(document).delegate('.m_platezi_a_admin_chk_zp','click',function(){
        get_info_from_zp();
    });
    
    //должности работника
    $('select[name="a_admin_i_post"]').select2({'width':'100%',allowClear: true,closeOnSelect:false,
        ajax: {
            url: "ajax/m_platezi.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, 
                    page: params.page,
                    _t:'a_admin_i_post_autocomplete',
                    a_admin_id:$('select[name="a_admin_id"]').val()
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
    
    
    //автозаполнение Рекламы
    $('select[name="i_reklama_id"]').select2({'width':'100%',allowClear: true,closeOnSelect:false,
        ajax: {
            url: "ajax/m_platezi.php",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, 
                    page: params.page,
                    _t:'i_reklama_id_autocomplete'
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
    
    
    //Тип платежа в з/п
    $('select[name="a_admin_tip"]').select2({'width':'100%',allowClear: false});
    
    //Расходы
    $('select[name="i_rashodi_id"]').select2({'width':'100%',allowClear: true});
    
    //Счет
    $('select[name="i_scheta_id"]').select2({'width':'100%',allowClear: true});
    

    //Ввод/вывод
    $('select[name="i_inout_id"]').select2({'width':'100%',allowClear: true});
    
    //Ввод/вывод тип
    $('select[name="i_inout_tip"]').select2({'width':'100%',allowClear: false});
    

    //Сохранение
    $(document).delegate('.m_platezi_add_form__save','click',function(){
        m_platezi_add_form__save(function(){
            
        });
    });
    
    
    //Очистка формы - новый платеж
    $(document).delegate('.m_platezi_add_form__clear','click',function(){
        add_platezi_form_clear();
    });
    
    $('select[name="m_zakaz_tip"], select[name="m_postav_tip"]').select2({'width':'100%',allowClear: false,closeOnSelect:false});
    
    //скрываем счета
    $(document).delegate('.m_platezi_pl_hide','click',function(){
        var th_=$(this);
        var cl_=th_.attr('class');
        if ( (cl_.split('active').length - 1)>0){
            th_.hide();
            chk_all_summa(function(){
                th_.show();
                th_.removeClass('active').attr('title','Скрыть счета');
                $('.i_schet_item').show();
            });
            
        }else{
            th_.addClass('active').attr('title','Отобразить счета');;
            $('.i_schet_item').hide();//.css({'visibility':'hidden'});
        }
    });
    $('.m_platezi_pl_hide').click();
    
    //Перенос всей суммы в платеж
    $(document).delegate('.add_all_summ','click',function(){
        var sum_=$('.cur_i_schet_info strong').html();
        $('.m_platezi_add_main_info input[name="summa"]').val(sum_);
    });
    
    //Отмена платежа
    $(document).delegate('.pl_funct .fa-remove','click',function(){
        var txt='<div class="pl_remove_del_form"><p>Введите пароль для удаления платежа:</p><input type="hidden" name="id" value="'+$(this).closest('.ttable_tbody_tr').data('id')+'" />';
        txt+='<div><input type="password" name="pl_remove_pass" placeholder="Пароль"></div>';
        txt+='<div><span class="pl_remove_del btn_orange">Удалить</span></div></div>';
        
        alert_m(txt,'','info','none');
        $('input[name="pl_remove_pass"]').focus();
        
    });
    $(document).delegate('input[name="pl_remove_pass"]','keyup',function(e){
        if (e.which==13){
            $('.pl_remove_del').trigger('click');
        }
    });
    //подтверждение удаления платежа
    $(document).delegate('.pl_remove_del','click',function(){
        var id=$('.pl_remove_del_form').find('input[name="id"]').val();
        var pass=$('.pl_remove_del_form').find('input[name="pl_remove_pass"]').val();
        pl_remove(id,pass,function(){
            find();
        });
    });
    
    
     //Открываем добавление платежа
     $(document).delegate('.m_platezi_add_form_open_close_div','click',function(){
        var th_=$(this).closest('.m_platezi_add_form');
        var cl_=th_.attr('class');
        if ((cl_.split('m_platezi_add_form_open').length - 1)>0){
            $(this).addClass('m_platezi_add_form_open_div').removeClass('m_platezi_add_form_close_div').text('Открыть форму добавления платежа');
            th_.addClass('m_platezi_add_form_close').removeClass('m_platezi_add_form_open');
        }else{
            $(this).addClass('m_platezi_add_form_close_div').removeClass('m_platezi_add_form_open_div').text('Закрыть форму добавления платежа');
            th_.addClass('m_platezi_add_form_open').removeClass('m_platezi_add_form_close');
        }
     });
    
    
    
     //Печать кассовой книги
     $(document).delegate('.m_platezi_fillter__print i','click',function(){
        if ($('input[name="m_platezi_data1_find"]').val()==''){
            alert_m('Укажите дату начала','','error','none');
        }else{
            window.location.href = "?inc=i_docs&com=print&file_name=kassa_book&i_scheta_id="+$('select[name="i_scheta_id_find"]').val()+"&data_start="+$('input[name="m_platezi_data1_find"]').val()+"&data_end="+$('input[name="m_platezi_data2_find"]').val()+"";
    
        }
        
    });
    
    //движение формы добавления платежа
    var m=getPageSize();
    
    $(window).scroll(function () {
        var m=getPageSize();
        var top_0=($('.left_podmenu_div:visible ul').outerHeight()+$('.header_block').outerHeight()-0);
        
        var pos_=$('.m_platezi_add_form_div').css('position');
       if (pos_=='fixed'){//Для полного экрана (не для мобильной верстки)
        $('.m_platezi_add_form_div').css({'height':$(window).height()});
		if ($(this).scrollTop() > 0) {
		      var top_=top_0-$(this).scrollTop();
              if (top_<0){top_=0;}
			$('.m_platezi_add_form_div').css({'top':top_});
            $('.m_platezi_add_form_div').css({'height':$(window).height()});
		} else {
			$('.m_platezi_add_form_div').css({'top':top_0+'px'});
            $('.m_platezi_add_form_div').css({'height':$(window).height()});
		}
        }else{
            $('.m_platezi_add_form_div').css({'height':'inherit'});
            $('.m_platezi_add_form_div').css({'top':'0'});
        }
	});
    $(window).scroll();
    $(window).resize(function(){$(window).scroll();})
    
    
    //Открытие расшифровки платежей
    $(document).delegate('.s_post_res_block .s_post_res_block_view','click',function(){
        var th_=$(this);
        if (th_.closest('.s_post_res_block').find('.s_post_res_block_info>p:eq(5)').css('display')=='none'){
            th_.text('свернуть');
            th_.closest('.s_post_res_block').find('.s_post_res_block_info>p').css({'display':'block'});   
        }
        else{
            th_.text('развернуть');
            th_.closest('.s_post_res_block').find('.s_post_res_block_info>p').css({'display':'none'});
            th_.closest('.s_post_res_block').find('.s_post_res_block_info>p:eq(0)').css({'display':'block'}).next('p').css({'display':'block'}).next('p').css({'display':'block'}).next('p').css({'display':'block'});

        }
    });
    
});//ready
</script>