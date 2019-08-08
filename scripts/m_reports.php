<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
?>
<script type="text/javascript">
//поиск
function find(callback){
    callback=callback || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='m_reports__find';
    data_['tip_report']=$('select[name="tip_report"]').val();
    data_['tip']=$('select[name="tip_report"] option:selected').data('tip');
    if (data_['tip']=='report1'){
        data_['d1']=$('input[name="m_reports_data1_report1_find"]').val();
        data_['d2']=$('input[name="m_reports_data2_report1_find"]').val();
        data_['sort']=$('.m_reports_fillter__sort li.active').data('val');
        data_['pay']=$('.m_reports_fillter__pay li.active').data('val');
    }
    if (data_['tip']=='report2'){
        data_['d1']=$('input[name="m_reports_data1_report2_find"]').val();
        data_['d2']=$('input[name="m_reports_data2_report2_find"]').val();
    }
    if (data_['tip']=='report3'){
        data_['year']=$('select[name="report3_year"]').val();
    }
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$('.m_reports_find_res, .m_reports_all_summa, .m_reports_all_workers').html('');
        
        $('.m_reports_find_res_loading').html('<p class="m_reports__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_reports.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    		    $('.m_reports_find_res_loading .m_reports__loading').detach();
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt='';
                    
                     // ОТЧЕТ ПО ПРИБЫЛИ
                    if (data_['tip_report']=='Отчет по прибыли'){
                        $('.m_reports_full_info').show();
                        var work_arr=new Object();//З/п работника
                        var sebes_tov=0;
                        var sum_tov=0;
                        var sebes_work=0;
                        var sum_work=0;
                        var kol_zakaz=count(data_n.z.i);
                        
        	            if (typeof data_n.z=='object' && typeof data_n.z.i=='object'){
        	               for (var i in data_n.z.i){
        	                   
                               //з/п приемщика
                                var a_admin_name=data_n.z.an[i];
                                if (typeof work_arr[a_admin_name]=='undefined'){work_arr[a_admin_name]=new Object(); work_arr[a_admin_name]['p']=0;work_arr[a_admin_name]['w']=0;}
                                work_arr[a_admin_name]['p']=(work_arr[a_admin_name]['p']-0)+(data_n.z.az[i]-0);
                              
                                            
        	                   txt+='<tr data-id="'+data_n.z.i[i]+'">';
                                    txt+='<td class="m_reports__td_id"><a href="?inc=m_zakaz&nomer='+data_n.z.i[i]+'">'+data_n.z.i[i]+'</a></td>';
                                    txt+='<td class="m_reports__td_data"><span>'+data_n.z.d[i]+'</span>';
                                    if (data_n.z.dd[i]!=''){
                                        txt+='<p>Выполнен: '+data_n.z.dd[i]+'</p>';
                                    }
                                    txt+='</td>';
                                    
                                    //txt+='<td>'+data_n.z.s[i]+'</td>';
                                    //txt+='<td>'+data_n.z.dd[i]+'</td>';
                                    txt+='<td><span data-id="'+data_n.z.ii[i]+'">'+data_n.z.in_[i]+'</span></td>';
                                    txt+='<td><span data-id="'+data_n.z.ai[i]+'" class="thumbnail">'+data_n.z.an[i]+'<span>Начислено за оформление заказа: <strong>'+data_n.z.az[i]+' руб.</strong></span></span></td>';
                                    txt+='<td>';
                                    var itog=0;
                                    var txt_t='';
                                    var sebes_zakaz=0;
                                    var prib_zakaz=0;
                                    if (typeof data_n.z.t[i]=='object' && typeof data_n.z.t[i].i=='object'){
                                        
                                        //Перебор по товарам и услугам внутри заказа
                                        for (var j in data_n.z.t[i].i){
                                        
                                        
                                            var sum=data_n.z.t[i].zp[j]*data_n.z.t[i].zk[j];
                                            
                                            itog=itog-0+(sum-0);
                                            var sum_=data_n.z.t[i].ts[j];
                                            if (sum_==0){
                                                sum_=data_n.z.t[i].ws[j];
                                            }
                                            
                                            
                                            
                                            sebes_zakaz=(sebes_zakaz-0)+(sum_-0);//Себестоимость заказа
                                            
                                            var sum_txt_='0';if ((sum_-0)>0){sum_txt_='-'+sum_;}
                                            
                                            if (data_n.z.t[i].t[j]=='Товар'){
                                                sum_tov=(sum_tov-0)+(sum-0);
                                                sebes_tov=(sebes_tov-0)+(sum_-0); //Себестоимость товара
                                                txt_t+='<tr data-id="'+data_n.z.t[i].i[j]+'">'
                                                        +'<td><a href="?inc=s_cat&nomer='+data_n.z.t[i].i[j]+'">'+data_n.z.t[i].n[j]+'</a></td>'
                                                        +'<td>'+data_n.z.t[i].zk[j]+'</td>'
                                                        +'<td>'+data_n.z.t[i].zp[j]+'</td>'
                                                        +'<td><strong>'+sum+'</strong></td>'
                                                        +'<td><span class="m_reports_res_all_sebes" title="Себестоимость">'+sum_txt_+'</span></td>'
                                                        +'</tr>';
                                                
                                            }else if (data_n.z.t[i].t[j]=='Услуга'){
                                                txt_t+='<tr data-id="'+data_n.z.t[i].i[j]+'">'
                                                        +'<td><a href="?inc=s_cat&nomer='+data_n.z.t[i].i[j]+'">'+data_n.z.t[i].n[j]+'</a></td>'
                                                        +'<td>'+data_n.z.t[i].zk[j]+'</td>'
                                                        +'<td>'+data_n.z.t[i].zp[j]+'</td>'
                                                        +'<td><strong>'+sum+'</strong></td>'
                                                        +'<td><span class="thumbnail m_reports_res_all_sebes">'+sum_txt_+'<span>Работнику: '+data_n.z.t[i].wi[j]+'</span></span></td>'
                                                        +'</tr>';
                                            
                                                sum_work=(sum_work-0)+(sum-0);
                                                sebes_work=(sebes_work-0)+(sum_-0); //Себестоимость товара
                                                
                                                //з/п работника
                                                var a_worker_name=data_n.z.t[i].wa[j];
                                                if (a_worker_name!=''){
                                                    if (typeof work_arr[a_worker_name]=='undefined'){work_arr[a_worker_name]=new Object();work_arr[a_worker_name]['w']=0;work_arr[a_worker_name]['p']=0;}
                                                    work_arr[a_worker_name]['w']=(work_arr[a_worker_name]['w']-0)+(sum_-0);
                                                
                                                }
                                            }
                                            
                                        }
                                        prib_zakaz=(itog-0)-(sebes_zakaz-0);
                                    }
                                    if (txt_t!=''){
                                        txt+='<table class="m_reports_res_tbl_items">'+txt_t+'</table>';
                                    }
                                    txt+='</td>';
                                    txt+='<td class="m_reports_res_td_itog">'
                                    +'<h3>Сумма заказа: <span class="m_reports_res_all_viruchka">'+itog+' руб.</span></h3>'
                                    +'<h3>Прибыль: <span class="m_reports_res_all_prib">'+prib_zakaz+' руб.</span></h3><hr />'
                                    +'<h3 class="m_reports_res_td_pay_span"><i class="fa fa-money"></i> Оплачено: <span>'+data_n.z.sp[i]+' руб.</span></h3>'
                                    +'</td>';
                                    
                                    
                               txt+='</tr>';
                               
        	               }
                           
                           
                           
                           
                           //Себестоимость
                           var prib_tov_all=sum_tov-sebes_tov;
                           $('.m_reports_all_summa').html('<h2>Статистика проданных товаров</h2>');
                           $('.m_reports_all_summa').append('<p>Всего заказов: <strong>'+number_format(kol_zakaz,0,'.',' ')+'</strong></p>');
                           $('.m_reports_all_summa').append('<p>Сумма проданых товаров: <strong>'+number_format(sum_tov,0,'.',' ')+' руб.</strong></p>');
                           $('.m_reports_all_summa').append('<p>Себестоимость проданых товаров: <strong>'+number_format(sebes_tov,0,'.',' ')+' руб.</strong></p>');
                           $('.m_reports_all_summa').append('<p>Маржа с товаров: <strong>'+number_format(prib_tov_all,0,'.',' ')+' руб.</strong></p><hr/>');
                           
                           var prib_work_all=sum_work-sebes_work;
                           $('.m_reports_all_summa').append('<h2>Статистика оказанных услуг</h2>');
                           $('.m_reports_all_summa').append('<p>Сумма оказанных услуг: <strong>'+number_format(sum_work,0,'.',' ')+' руб.</strong></p>');
                           $('.m_reports_all_summa').append('<p>Себестоимость оказанных услуг: <strong>'+number_format(sebes_work,0,'.',' ')+' руб.</strong></p>');
                           $('.m_reports_all_summa').append('<p>Маржа с оказанных услуг: <strong>'+number_format(prib_work_all,0,'.',' ')+' руб.</strong></p><hr/>');
                           
                           var viruchka_all=(sum_work-0)+(sum_tov-0);
                           var prib_all=(prib_work_all-0)+(prib_tov_all-0);
                           var sebes_all=(sebes_tov-0)+(sebes_work-0);
                           $('.m_reports_all_summa').append('<h2 class="m_reports_res_all_viruchka">Выручка: <strong>'+number_format(viruchka_all,0,'.',' ')+' руб.</strong></h2>');
                           $('.m_reports_all_summa').append('<h2 class="m_reports_res_all_sebes">Себестоимость: <strong>'+number_format(sebes_all,0,'.',' ')+' руб.</strong></h2>');
                           $('.m_reports_all_summa').append('<h2 class="m_reports_res_all_prib">Маржа: <strong>'+number_format(prib_all,0,'.',' ')+' руб.</strong></h2><hr/>');
                           
                           
                           
                           
                           //Обработка з/п
                           var all_p=0;
                           var all_w=0;
                           var zp='';
                           for (var a_admin_name in work_arr){
                            var p_=0;var w_=0;var a_=0;
                            if (typeof work_arr[a_admin_name]['p']!='undefined'){p_=work_arr[a_admin_name]['p']-0;}
                            if (typeof work_arr[a_admin_name]['w']!='undefined'){w_=work_arr[a_admin_name]['w']-0;}
                            a_=(p_-0)+(w_-0);
                            all_p=(all_p-0)+(p_-0);
                            all_w=(all_w-0)+(w_-0);
                            zp+='<h2 class="m_reports_find_res_h2_workers">'+a_admin_name+': <span><span>'+a_+'</span> руб.</span></h2>';
                            if (p_>0){
                                zp+='<p>За заказы: <strong>'+p_+' руб.</strong></p>';
                            }
                            if (w_>0){
                                zp+='<p>За работы: <strong>'+w_+' руб.</strong></p>';
                            }
                            
                           }
                           zp+='<hr/>';
                           zp+='<h2 class="m_reports_find_res_h2_zakazi_all">Всего за заказы: <span><span>'+all_p+'</span> руб.</span></h2>';
                           zp+='<h2 class="m_reports_find_res_h2_workers_all">Всего за работы: <span><span>'+all_w+'</span> руб.</span></h2>';
                           $('.m_reports_all_workers').html(zp);
                          
        	               if (txt!=''){//вывод таблицы доходов
        	                   txt='<table class="ttable reports_res_table" style="width:100%">'+txt+'</table>';
        	               }
                           
                           
                           
                           //вывод расходов
                           var rash='';
                           var all_sum_rash=0;
                           if (typeof data_n.r.summa == 'object'){
                                for(var id in data_n.r.summa){
                                    all_sum_rash=(all_sum_rash-0)+(data_n.r.summa[id]-0);
                                    rash+='<tr>'
                                        +'<td class="m_reports_find_res_td_rash_name">'+data_n.r.name[id]+'</td>'
                                        +'<td class="m_reports_find_res_td_rash_sum">'+number_format(data_n.r.summa[id],0,'.',' ')+'</td>'
                                        +'</tr>';
                                }
                                if (rash!=''){
                                    rash='<hr/><h2 class="m_reports_find_res_h2_rash">Расходы за период: <span><span>'+number_format(all_sum_rash,0,'.',' ')+'</span> руб.</span></h2>'
                                        +'<table class="m_reports_find_res_tbl_rash ttable">'+rash+'</table>';
                                }
                           }
                           $('.m_reports_all_rashodi').html(rash);
                           
                           //вывод рекламы
                           var reklama='';
                           var all_sum_reklama=0;
                           if (typeof data_n.rk.summa == 'object'){
                                for(var id in data_n.rk.summa){
                                    all_sum_reklama=(all_sum_reklama-0)+(data_n.rk.summa[id]-0);
                                    reklama+='<tr>'
                                        +'<td class="m_reports_find_res_td_reklama_name">'+data_n.rk.name[id]+'</td>'
                                        +'<td class="m_reports_find_res_td_reklama_sum">'+number_format(data_n.rk.summa[id],0,'.',' ')+'</td>'
                                        +'</tr>';
                                }
                                if (reklama!=''){
                                    reklama='<hr/><h2 class="m_reports_find_res_h2_reklama">Реклама за период: <span><span>'+number_format(all_sum_reklama,0,'.',' ')+'</span> руб.</span></h2>'
                                        +'<table class="m_reports_find_res_tbl_reklama ttable">'+reklama+'</table>';
                                }
                           }
                           $('.m_reports_all_reklama').html(reklama);
                           
                           var itog_txt='';
                           var all_rashodi_=(all_p-0)+(all_sum_rash-0)+(all_sum_reklama-0);
                           itog_txt+='<h2 class="m_reports_find_res_all_rash_vse">Всего расходов: <span><span>'+number_format(all_rashodi_,0,'.',' ')+'</span> руб.</span><h2>';
                           var all_prib_=prib_all-all_rashodi_;
                           itog_txt+='<h2 class="m_reports_find_res_all_prib_vse">Всего прибыли: <span><span>'+number_format(all_prib_,0,'.',' ')+'</span> руб.</span><h2>';
                           
                           $('.m_reports_all_itog_vse').html(itog_txt);
                           
                        }
                        else{
                            alert_m('Не определен объект data_n.z','','error','none');
                        }
                    }
                    // ОТЧЕТ ПО ТОВАРАМ
                    if (data_['tip_report']=='Отчет по товарам'){
                        $('.m_reports_full_info').hide();
                        if (typeof data_n.i=='object'){
                            var txt_tbl='';
                            for(var i in data_n.i){
                                txt_tbl+='<tr data-id="'+data_n.i[i]+'">';
                                txt_tbl+='<td><a href="../i/s_cat/original/'+data_n.img[i]+'" class="zoom" target="_blank"><img src="../i/s_cat/small/'+data_n.img[i]+'"></a></td>';
                                txt_tbl+='<td><a href="?inc=s_cat&nomer='+data_n.i[i]+'">'+data_n.n[i]+'</a> | <a target="_blank" style="color:#900;" href="../'+data_n.i[i]+'">На сайт</a></td>';
                                txt_tbl+='<td>'+data_n.p[i]+' <i class="fa fa-rub"></i></td>';
                                txt_tbl+='<td>'+data_n.cnt_[i]+'</td>';
                                txt_tbl+='<td>'+data_n.dt[i]+'<p>На сайте: '+data_n.dtt[i]+' '+end_word(data_n.dtt[i],'дней','день','дня')+'</p></td>';
                                txt_tbl+='</tr>';
                            }
                            if (txt_tbl!=''){
                                txt='<table class="ttable m_reports_find_res_s_cat_pop"><thead>'
                                +'<tr>'
                                +'<th>Фото</th>'
                                +'<th>Название</th>'
                                +'<th>Цена</th>'
                                +'<th>Просмотров</th>'
                                +'<th>Дата создания товара</th>'
                                +'</tr>'
                                +'</thead><tbody>'+txt_tbl+'</tbody></table>';
                            }
                            
                        }else{
                            txt='<p>Результатов не найдено!</p>';
                        }
                    }
                    
                    //ОТЧЕТ ПО МЕСЯЦАМ
                    if (data_['tip_report']=='Отчет по месяцам'){
                        if (typeof data_n.z=='object'){
                            txt=create_report_mounth(data_n);
                        }
                        else{
                            alert_m('Не определен объект data_n.z','','error','none');
                        }
                    }
                    
                    $('.m_reports_find_res').html(txt);
                    $('.zoom').fancybox();
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
    
    
}

//ФОРМИРУЕМ ОТЧЕТ ПО МЕСЯЦАМ
function create_report_mounth(data_n){
    var txt='';
    var txt_arr=new Object();
    txt_arr['Пусто']='';
    txt_arr['Кварталы']='';
    txt_arr['Месяца']='';
    txt_arr['Заказы']='';
    txt_arr['Оборот']='';
    txt_arr['Оборот по товарам']='';
    txt_arr['Оборот по услугам']='';
    txt_arr['Себестоимость']='';
    txt_arr['Себестоимость по товарам']='';
    txt_arr['Себестоимость по услугам']='';
    txt_arr['Прибыль']='';
    txt_arr['Прибыль по товарам']='';
    txt_arr['Прибыль по услугам']='';
    txt_arr['Зарплата менеджнера']='';
    txt_arr['Реклама']='';
    txt_arr['Расходы итого']='';
    txt_arr['Вводы']='';
    txt_arr['Выводы']='';
    txt_arr['Чистая прибыль']='';
    
    var tbl1 = ['Кварталы','Месяца','Заказы','Пусто'
    ,'Оборот по товарам','Себестоимость по товарам','Прибыль по товарам','Пусто'
    ,'Оборот по услугам','Себестоимость по услугам','Прибыль по услугам','Пусто'
    ,'Оборот','Себестоимость','Прибыль','Пусто'
    
    ];
    
    var j=tbl1.length-0+1;
    for (var rash_id in data_n.rash){
        txt_arr['Расходы: '+data_n.rash[rash_id]]='';
        tbl1[j]='Расходы: '+data_n.rash[rash_id];
        j++;
    }
    
    var tbl2=['Расходы итого','Реклама','Зарплата менеджнера','Пусто','Чистая прибыль','Пусто','Вводы','Выводы'];
    var tbl =tbl1.concat(tbl2);
    
    
    var monthNames = [ "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", 
                       "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь" ];
    var kv=1;var chk=0;         
    for(var m in data_n.z){//перебор по месяцам
        
        chk++;
        if (chk==3){txt_arr['Кварталы']+='<td class="report_3_tbl_kv" colspan="3">'+kv+' квартал</td>';chk=0;kv++;}
        
        
        txt_arr['Месяца']+='<td>'+monthNames[m-1]+'</td>';
        txt_arr['Заказы']+='<td>'+number_format(data_n.z[m]['i'],0,',',' ')+'</td>';
        txt_arr['Оборот']+='<td class="report_3_tbl_all">'+number_format(data_n.z[m]['va'],0,',',' ')+'</td>';
        txt_arr['Оборот по товарам']+='<td class="report_3_tbl_all">'+number_format(data_n.z[m]['vi'],0,',',' ')+'</td>';
        txt_arr['Оборот по услугам']+='<td class="report_3_tbl_all">'+number_format(data_n.z[m]['vw'],0,',',' ')+'</td>';
        txt_arr['Себестоимость']+='<td class="report_3_tbl_minus">'+number_format(data_n.z[m]['sa'],0,',',' ')+'</td>';
        txt_arr['Себестоимость по товарам']+='<td class="report_3_tbl_minus">'+number_format(data_n.z[m]['si'],0,',',' ')+'</td>';
        txt_arr['Себестоимость по услугам']+='<td class="report_3_tbl_minus">'+number_format(data_n.z[m]['sw'],0,',',' ')+'</td>';
        txt_arr['Прибыль']+='<td class="report_3_tbl_plus report_3_tbl_main">'+number_format(data_n.z[m]['pa'],0,',',' ')+'</td>';
        txt_arr['Прибыль по товарам']+='<td class="report_3_tbl_plus">'+number_format(data_n.z[m]['pi'],0,',',' ')+'</td>';
        txt_arr['Прибыль по услугам']+='<td class="report_3_tbl_plus">'+number_format(data_n.z[m]['pw'],0,',',' ')+'</td>';
        txt_arr['Зарплата менеджнера']+='<td class="report_3_tbl_minus">'+number_format(data_n.z[m]['zm'],0,',',' ')+'</td>';
        txt_arr['Реклама']+='<td class="report_3_tbl_minus">'+number_format(data_n.z[m]['rka'],0,',',' ')+'</td>';
        txt_arr['Расходы итого']+='<td class="report_3_tbl_minus report_3_tbl_main">'+number_format(data_n.z[m]['ra'],0,',',' ')+'</td>';
    
            for (var rash_id in data_n.rash){
                txt_arr['Расходы: '+data_n.rash[rash_id]]+='<td class="report_3_tbl_minus">'+number_format(data_n.z[m]['r'][rash_id],0,',',' ')+'</td>';
            }
        txt_arr['Вводы']+='<td class="report_3_tbl_all">'+number_format(data_n.z[m]['ina'],0,',',' ')+'</td>';
        txt_arr['Выводы']+='<td class="report_3_tbl_plus report_3_tbl_main">'+number_format(data_n.z[m]['outa'],0,',',' ')+'</td>';
        txt_arr['Чистая прибыль']+='<td class="report_3_tbl_plus report_3_tbl_main">'+number_format(data_n.z[m]['pch'],0,',',' ')+'</td>';
        
        
    }
    
    for(var key in tbl){
        txt+='<tr>';
        if (tbl[key]=='Месяца' || tbl[key]=='Кварталы'){
            txt+='<td></td>';
        }
        else if(tbl[key]=='Пусто'){
            txt+='<td class="report_3_tbl_null" colspan="'+(m-0+1)+'"></td>';
        }
        else if( ((tbl[key]).split('Расходы:').length - 1)>0){
            txt+='<td class="report_3_tbl_rash">'+tbl[key]+'</td>';
        }
        else{
            txt+='<td class="report_3_tbl_name">'+tbl[key]+'</td>';
        }
        if (typeof txt_arr[tbl[key]] !='undefined'){txt+=txt_arr[tbl[key]];}
        
        txt+='</tr>';
    }
    //если были данные
    if (txt!=''){
        txt='<table class="ttable report_3_tbl">'+txt+'</table>';
    }
    
    return txt;
}

//**********************************************************************************************************************
//**********************************************************************************************************************
//**********************************************************************************************************************
$(document).ready(function(){
    
    $('select[name="tip_report"]').select2({'width':'100%'}).change(function(){
        var th_=$(this);
        $('.fillter_report, .m_reports_full_info').hide();
        if (th_.val()=='Отчет по прибыли'){
            $('.fillter_report1').show();
            $('.m_reports_full_info').show();
        }
        else if (th_.val()=='Отчет по товарам'){
            $('.fillter_report2').show();
            
        }
        else if (th_.val()=='Отчет по месяцам'){
            $('.fillter_report3').show();
            
        }
        else{
            
            
        }
        
        find();//начальный поиск
    }).change();
    
    $('select[name="report3_year"]').select2({'width':'100%'}).change(function(){
        find();//начальный поиск
    });
    
    $('input[name="m_reports_data1_report1_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:false,onClose: function(current_time,$input){
        $(this).trigger('blur');
        find();
    }});
    $('input[name="m_reports_data2_report1_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:false,onClose: function(current_time,$input){
        $(this).trigger('blur');
        find();
    }});
    $('input[name="m_reports_data1_report2_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:false,onClose: function(current_time,$input){
        $(this).trigger('blur');
        find();
    }});
    $('input[name="m_reports_data2_report2_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:false,onClose: function(current_time,$input){
        $(this).trigger('blur');
        find();
    }});
    
    
    
});


//Сортировка
$(document).delegate('.m_reports_fillter__sort li','click',function(){
    var th_=$(this);
    $('.m_reports_fillter__sort li').removeClass('active');
    th_.addClass('active');
    find();
    
});

//Оплата
$(document).delegate('.m_reports_fillter__pay li','click',function(){
    var th_=$(this);
    $('.m_reports_fillter__pay li').removeClass('active');
    th_.addClass('active');
    find();
    
});

</script>