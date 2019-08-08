<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script type="text/javascript">
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
var r_neispr_arr=new Object();//неисправности
var i_contr_arr=new Object();
var i_contr_org_arr=new Object();;
var i_contr_active_id;
var term;
var reg_ext=/.*?\./;//регулярка для расширения файла
var current_time=date('d.m.Y H:i');
var history_name=''; //история URL
var history_url=''; //история URL

var date_cur_change;//выбор текущей даты
var comments_log;//логирование комментариев
var m_zakaz_r_service_model_name='';//выбранная модель оборудования



//*****************************************************************************************************
// Поиск
function find(limit,callback){
    limit=limit || '';
    callback=callback || '';
        //новый поиск
        if (limit==''){
            $('.m_zakaz_find_res').html('');
        }
    var err_text='';
    var data_=new Object();
    data_['_t']='m_zakaz__find';
    data_['txt']=$('.m_zakaz_fillter__find_txt input').val();
    data_['status_']=sel_in_array($('.m_zakaz_fillter__status_zakaz li.active'),'data','val');
    //data_['status_']=$('.m_zakaz_fillter__status_zakaz li.active').data('val');
    data_['status_service']='-1';
        if ($('.m_zakaz_fillter__service_ul li.active').size()>0){
            data_['status_service']=$('.m_zakaz_fillter__service_ul li.active').data('id');
        }
    
    data_['status_pay']=sel_in_array($('.m_zakaz_fillter__status_pay li.active'),'data','val');//$('.m_zakaz_fillter__status_pay li.active').data('val');
    data_['i_tp']=$('select[name="i_tp_id_find"]').val();
    data_['a_admin_otvet_id']= $('select[name="m_zakaz_otvet_find"]').val();
    data_['i_tk_id']= $('select[name="m_zakaz_i_tk_find"]').val();
    data_['i_reklama_id']= $('select[name="m_zakaz_i_reklama_find"]').val();
    data_['d1']=$('input[name="m_zakaz_data1_find"]').val();
    data_['d2']=$('input[name="m_zakaz_data2_find"]').val();
    data_['d1_done']=$('input[name="m_zakaz_data_done1_find"]').val();
    data_['d2_done']=$('input[name="m_zakaz_data_done2_find"]').val();
    data_['fire']=$('input[name="m_zakaz_fire"]').prop('checked');
    data_['fire_h']=$('input[name="m_zakaz_fire_h"]').val();
    
    data_['time']=sel_in_array($('.m_zakaz_fillter__time li.active'),'data','id');
    data_['sort']=$('.m_zakaz_fillter__sort li.active').data('val');///sel_in_array(,'data','val');

    
    data_['limit']=limit;
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
        $('.m_zakaz_find_res').append('<p class="m_zakaz__loading"><img src="i/l_20_w.gif" /> Загрузка...</p>');
    	
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    			$('.m_zakaz__loading').detach();
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var cnt_=data_n.cnt_;//общее количество
                    var pl_all=data_n.pl_all;//общее количество платежей
                    var sum_all=data_n.sum_all;//общая сумма заказов
                    
                    var time1=data_n.time1;//количество просроченных
                        $('.m_zakaz_fillter__time .time_li_1 span').html('('+time1+')');
                    var time2=data_n.time2;//количество напоминаний
                        $('.m_zakaz_fillter__time .time_li_2 span').html('('+time2+')');
                    var cur_cnt_=0;//текущее количество
                    var txt='';
                    for (var i in data_n['i']){//перебор по заказам
                    
                        var work_arr=data_n.w[i];
                        var txt_items='';
                        var kol_=0;var sum_=0;
                        var all_load=0;
                        for (var j in work_arr['i']){
                            kol_=kol_-0+(work_arr['k'][j]-0);
                            var itog=(work_arr['p'][j]-0)*(work_arr['k'][j]-0);
                            sum_=sum_-0+itog-0;
                            var st_='';if (all_load>=3){st_=' style="display:none;"';}
                              
                                var name=work_arr['n'][j];
                                var line_class='';
                                var status_txt='';
                                
                                if (work_arr['pi'][j]!='' && work_arr['ps'][j]!=''){
                                    var prib=((itog-0)-(work_arr['ps'][j]-0));
                                    var cl_post='m_zakaz_item__m_postav';
                                    if (prib>0){cl_post+=' m_zakaz_item__m_postav_green';}
                                    else if (prib==0){cl_post+=' m_zakaz_item__m_postav_gray';}
                                    else if (prib<0){cl_post+=' m_zakaz_item__m_postav_red';}
                                    
                                    
                                    status_txt+='<i class="fa fa-check-square thumbnail '+cl_post+'"><span>';
                                    
                                    status_txt+='<div class="m_zakaz_item__m_postav_sebest">Себестоимость: '+work_arr['ps'][j]+' руб.</div>';
                                    status_txt+='<div class="m_zakaz_item__m_postav_prib">Прибыль: '+prib+' руб.</div>';
                                    status_txt+=' <a href="?inc=m_postav&nomer='+work_arr['pi'][j]+'">';
                                    status_txt+='<div class="m_zakaz_item__m_postav_post"><strong>Поступление №'+work_arr['pi'][j]+'</strong></div>';
                                    status_txt+='</a></span></i>';
                                    line_class='good';
                                }
                                else if (work_arr['aan'][j]!=''){
                                    
                                    status_txt+='<i class="fa fa-check-square thumbnail m_zakaz_item__m_postav m_zakaz_item__m_postav_green"><span>';
                                    
                                    status_txt+='<div>'+work_arr['aan'][j]+'</div>';
                                    status_txt+='</span></i>';
                                    line_class='good';
                                }
                                else{
                                    var ksk=work_arr['ksk'][j];//на складе
                                    var pst=work_arr['pst'][j];//статус поступления
                                    
                                    if (pst!=''){//указано поступление под данный заказ
                                    
                                        var pst_arr=pst.split(',');
                                        if (pst_arr[0]-0>0){
                                            var cl_pst='';
                                            if (pst_arr[1]=='В обработке'){cl_pst='m_zakaz_m_postav_item_status_in_work';}
                                            if (pst_arr[1]=='Оплачен'){cl_pst='m_zakaz_m_postav_item_status_pay';}
                                            if (pst_arr[1]=='Доставлен'){cl_pst='m_zakaz_m_postav_item_status_chk';}
                                            
                                            status_txt+='<i class="fa fa-ship thumbnail m_zakaz_m_postav_item_status '+cl_pst+'">';
                                            if(ksk-0>0){//есть на складе
                                            status_txt+=ksk;
                                            status_txt+='<span><h2>На складе '+ksk+' шт.</h2>';
                                            }
                                            else{
                                                status_txt+='<span>';
                                            }
                                            status_txt+='<a href="?inc=m_postav&nomer='+pst_arr[0]+'" target="_blank">Поступление №'+pst_arr[0]+' ('+pst_arr[1]+')</a>';
                                            status_txt+='</span>';
                                            status_txt+='</i>';
                                        }
                                    }else{//поступления под данный заказ нет - проверяем наличие на складе
                                        if(ksk-0>0){//есть на складе
                                            status_txt+='<i class="fa fa-truck m_zakaz_m_postav_item_sklad thumbnail">'+ksk;
                                            status_txt+='<span><h2>На складе '+ksk+' шт.</h2></span></i>';
                                        }
                                    }
                                    
                                }
                                
                                txt_items+='<div class="ttable_tbody_tr '+line_class+'"'+st_+'>';
                                txt_items+='<div class="ttable_tbody_tr_td m_zakaz_item__td_name"><span class="for_mobile">Название</span><span class="val">';
                                txt_items+=status_txt;
                                
                                var img=work_arr['im'][j];
                                if (img!=''){
                                    txt_items+='<i class="fa fa-image thumbnail"><span>'+name+'';
                                    txt_items+='<a title="'+_IN(name)+'" href="../i/s_cat/original/'+_IN(img)+'" target="_blank" class="zoom"><img src="../i/s_cat/small/'+_IN(img)+'" /></a>';
                                    txt_items+='</span></i>';
                                }
                                txt_items+='<a href="../'+work_arr['i'][j]+'" target="_blank">'+work_arr['i'][j]+'</a>. ';
                                txt_items+=' <a href="?inc=s_cat&nomer='+work_arr['i'][j]+'" target="_blank">'+name+'</a>';
                                txt_items+=' <span class="s_cat_s_prop_val_tbl">'+work_arr['pr'][j]+'</span>';
                                
                                txt_items+='<div>'+work_arr['c'][j]+'</div></span></div>';
                                txt_items+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Стоимость</span><span class="val">'+work_arr['p'][j]+'</span></div>';
                                txt_items+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Количество</span><span class="val">'+work_arr['k'][j]+'</span></div>';
                                txt_items+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Итого</span><span class="val">'+itog+'</span></div>';
                            txt_items+='</div>';
                            all_load++;
                        }
                        var add_load=all_load-3;
                        if (all_load>3){txt_items+='<div class="ttable_tbody_tr"><div class="ttable_tbody_tr_td m_zakaz_item__view_all_tr">Показать еще ('+add_load+')</div></div>';}
                        delete(work_arr);
                        
                        
                        var d1=(data_n.d1[i]).substr(0, 10);
                        var t1=(data_n.d1[i]).substr(11, 8);
                        
                        var time_='';
                        if (data_n.d2[i]!=''){
                                var style_time='#000';
                                var d_i=data_n.d_[i]-0;
                                var txt_dt='Осталось';
                                if (d_i>0){
                                    if (d_i>24){
                                        d_i=d_i/24;
                                        if (d_i<2){
                                            style_time='#ff7200';  
                                        }
                                        else if(d_i<3 && d_i>=2){
                                            style_time='#ffcc00';   
                                        }
                                        else if(d_i<5 && d_i>=3){
                                            style_time='#e2df00';   
                                        }
                                        else{
                                            style_time='#090';
                                        }
                                        d_i=number_format(d_i,0,'.',' ')+' '+end_word(number_format(d_i,0,'.',''),'дней','день','дня');;
                                    }
                                    else{
                                        style_time='#f00';
                                        d_i=number_format(d_i,0,'.',' ')+' час'+end_word(number_format(d_i,0,'.',''),'ов','','а');
                                    }
                                }else{
                                    txt_dt='Просрочка';
                                    if (d_i<24){
                                        d_i=d_i/24*(-1);
                                        d_i=number_format(d_i,0,'.',' ')+' '+end_word(number_format(d_i,0,'.',''),'дней','день','дня');
                                    }else{
                                        d_i=d_i*(-1);
                                        d_i=number_format(d_i,0,'.',' ')+' час'+end_word(number_format(d_i,0,'.',''),'ов','','а');
                                    }
                                }
                                time_='<i style="color:'+style_time+';" class="fa fa-bell" title="'+data_n.d2[i]+'\n\t'+txt_dt+': '+d_i+'"><span class="for_mobile">'+data_n.d2[i]+'</span></i>';
                            }
                            
                        var status_='';
                        
                        if (data_n.s[i]=='В обработке'){status_='<i class="status_fa fa fa-clock-o" title="В обработке"><span class="for_mobile">В обработке</span></i>';}
                        if (data_n.s[i]=='Отменен'){status_='<i class="status_fa fa fa-times-circle" title="Отменен"><span class="for_mobile">Отменен</span></i>';}
                        if (data_n.s[i]=='Выполнен'){status_='<i class="status_fa fa fa-check-circle-o" title="Выполнен"><span class="for_mobile">Выполнен</span></i>';}
                        if (data_n.s[i]=='Частично выполнен'){status_='<i class="status_fa fa fa-pie-chart" title="Частично выполнен"><span class="for_mobile">Частично выполнен</span></i>';}
                        
                        
                        
                        var pl_='';
                        var pl_txt='';
                        if (data_n.p[i]-0==0 && (sum_-0)>0){
                            pl_='circle-o';
                            pl_txt='Не оплачен';
                        }else{
                            if ((sum_-0)>(data_n.p[i]-0)){
                                pl_='dot-circle-o';
                                pl_txt='Частично оплачен';
                            }
                            else{
                                pl_='check-circle';
                                pl_txt='Оплачен';
                                if (data_n.s[i]=='В обработке'){
                                    status_='<i class="status_fa fa fa-rub" title="Оплачен"><span class="for_mobile">Оплачен</span></i>';
                                }
                            }
                        }
                        //Поступление по заказу
                        if (data_n.s[i]!='Выполнен' && data_n.s[i]!='Отменен'){
                            /*
                            if (data_n.pi[i]!=''){
                                var m_postav_status='';
                                var m_postav_status_txt='';
                                if (data_n.ps[i]=='В обработке'){m_postav_status=' m_postav_v_obrabotke';m_postav_status_txt='Заказан';}
                                if (data_n.ps[i]=='Отправлен'){m_postav_status=' m_postav_v_otpravlen';m_postav_status_txt='Отправлен';}
                                if (data_n.ps[i]=='Доставлен'){m_postav_status=' m_postav_v_dostavlen';m_postav_status_txt='Доставлен';}
                                status_='<a href="?inc=m_postav&nomer='+data_n.pi[i]+'" target="_blank"><i class="status_fa fa fa-truck thumbnail'+m_postav_status+'" title="Заказан у поставщика"><span>Заказ поставщику №'+data_n.pi[i]+' ('+m_postav_status_txt+')</span></i><span class="for_mobile">Заказ поставщика №'+data_n.pi[i]+'</span></a>';
                            }
                            */
                        }
                        
                        //Статус акта
                        var txt_status_act='';
                        var cl_st='';
                        var status_cl='';
                        if (data_n.r_st[i]!=''){
                            if (data_n.r_st[i]=='Принят'){cl_st=' r_service_status r_service_status_prinyat';}
                            if (data_n.r_st[i]=='На диагностике'){cl_st=' r_service_status r_service_status_diagnostika';}
                            if (data_n.r_st[i]=='Согласование'){cl_st=' r_service_status r_service_status_soglas';}
                            if (data_n.r_st[i]=='Ожидание предоплаты'){cl_st=' r_service_status r_service_status_predoplata';}
                            if (data_n.r_st[i]=='В работе'){cl_st=' r_service_status r_service_status_inwork';}
                            if (data_n.r_st[i]=='Готов'){cl_st=' r_service_status r_service_status_gotov';}
                            if (data_n.r_st[i]=='Ожидание запчастей'){cl_st=' r_service_status r_service_status_wait';}
                            if (data_n.r_st[i]=='Отдан'){cl_st=' r_service_status r_service_status_otdan';}
                            
                            txt_status_act=' <span class="m_zakaz_r_service_status"><span>'+data_n.r_st[i]+'</span></span>';
                        }
                        else{
                            
                            status_cl=' m_zakaz_v_obrabotke';
                            if (data_n.s[i]=='Отменен'){status_cl=' m_zakaz_otmenen';}
                            else{//не отменен
                                if (data_n.p[i]-0==0 && (sum_-0)>0){ //не оплачен
                                    if (data_n.s[i]=='В обработке'){status_cl=' m_zakaz_v_obrabotke';}
                                    if (data_n.s[i]=='Частично выполнен'){status_cl=' m_zakaz_chsticno_vipolnen';}
                                    if (data_n.s[i]=='Выполнен'){status_cl=' m_zakaz_gotov';}
                                }else{
                                    if ((sum_-0)>(data_n.p[i]-0)){ //частично оплачен
                                        if (data_n.s[i]=='В обработке'){status_cl=' m_zakaz_chsticno_vipolnen';}
                                        if (data_n.s[i]=='Частично выполнен'){status_cl=' m_zakaz_chsticno_vipolnen';}
                                        if (data_n.s[i]=='Выполнен'){status_cl=' m_zakaz_gotov';}
                                    }
                                    else{ //оплачен
                           
                                        if (data_n.s[i]=='В обработке'){status_cl=' m_zakaz_chsticno_vipolnen';}
                                        if (data_n.s[i]=='Частично выполнен'){status_cl=' m_zakaz_chsticno_vipolnen';}
                                        if (data_n.s[i]=='Выполнен'){status_cl=' m_zakaz_vipolnen';}
                                        
                                    }
                                }
                            }
                        
                        }
                        var cl_border='';if (data_n.sd[i]=='Доработка'){cl_border=' m_zakaz_item_border_red';}
                        txt+='<div class="m_zakaz_item'+status_cl+cl_st+cl_border+'" data-id="'+data_n.i[i]+'">';
                        
                        //Статус акта
                        txt+=txt_status_act;
                        txt+='<div class="m_zakaz_item_left">';
                            txt+='<div class="m_zakaz_item__main">';
                                txt+='<div class="m_zakaz_item__id">';
                                    txt+='<input type="checkbox" name="select_item" id="m_zakaz_item_'+data_n.i[i]+'" />';
                                    txt+='<label for="_m_zakaz_item_'+data_n.i[i]+'"><span class="for_mobile">Заказ №</span>'+data_n.i[i]+'';
                                    txt+='<div class="m_zakaz_item__data1" title="'+d1+'\n\t'+t1+'"><span class="for_mobile">от </span> '+d1+'</div></label>';
                                txt+='</div>';
                                txt+='<div class="m_zakaz_item__status">';
                                txt+=status_;
                                
                                txt+='<i class="fa fa-money"><i class="fa fa-'+pl_+'"></i><span class="for_mobile">'+pl_txt+'</span></i>';
                                if (data_n.mc[i]>0){
                                    var cl_com='';if (data_n.mc[i]>9){cl_com=' style="font-size:9px;left:5px;"';}
                                    if (data_n.mc[i]>99){cl_com=' style="left:6px;"';}
                                    txt+='<i class="fa fa-comments-o"><span'+cl_com+'>'+data_n.mc[i]+'</span></i>';
                                }
                                
                                txt+='</div>';
                                
                                
                            txt+='</div>';
                            txt+='<div class="m_zakaz_item__i_contr">';
                                if (data_n.o[i]!=''){txt+='<div class="m_zakaz_item__i_contr_org" data-id="'+data_n.oi[i]+'"><span>'+data_n.o[i]+'</span><div class="m_zakaz_item__i_contr_name" data-id="'+data_n.ci[i]+'"><span>'+data_n.c[i]+'</span></div></div>';}
                                else{txt+='<div class="m_zakaz_item__i_contr_name" data-id="'+data_n.ci[i]+'"><span>'+data_n.c[i]+'</span></div>';}
                                
                                txt+='<div class="m_zakaz_item__i_contr_cont">'
                                    if (data_n.cp[i]!=''){txt+='<span class="m_zakaz_item__i_contr_phone"><i class="fa fa-phone"></i> <a href="tel:'+data_n.cp[i]+'">'+data_n.cp[i]+'</a></span>';}
                                    if (data_n.ce[i]!=''){txt+='<span class="m_zakaz_item__i_contr_email"><i class="fa fa-envelope"></i> <a target="_blank" href="mailto:'+data_n.ce[i]+'?subject=Заказ №'+$('input[name="nomer"]').val()+'">'+data_n.ce[i]+'</a></span>';}
                                    
                                txt+='</div>'
                            txt+='</div>'
                            if (data_n.mod[i]!=''){
                                txt+='<div class="m_zakaz_item__project_name">'+data_n.pn[i]+'. '+data_n.mod[i];
                            }else{
                                txt+='<div class="m_zakaz_item__project_name">'+data_n.pn[i];
                            }
                            txt+='</div>';
                            
                            //другие заказы контрагента
                            var mz2=data_n.mz2[i];
                            var mz2_arr=new Array();
                            var txt_mz2='';
                            if (mz2!='' && mz2 != null){
                                if ((mz2.split(',').length - 1)>0){
                                    mz2_arr=mz2.split(',');
                                }else{
                                    mz2_arr[0]=mz2;
                                }
                            }
                            for(var key in mz2_arr){
                                var mz0=mz2_arr[key];
                                mz0_arr=mz0.split('@@');
                                if (txt_mz2!=''){txt_mz2+=', ';}
                                var mz2_cl='';
                                if (mz0_arr[1]=='Отменен'){mz2_cl='mz2_closed';}
                                if (mz0_arr[1]=='Выполнен'){mz2_cl='mz2_chk';}
                                if (mz0_arr[1]=='Частично выполнен'){mz2_cl='mz2_nofullchk';}
                                if (mz0_arr[1]=='В обработке'){mz2_cl='mz2_inwork';}
                                txt_mz2+='<a class="'+mz2_cl+'" title="Заказ '+mz0_arr[0]+' ('+mz0_arr[1]+')" target="_blank" href="?inc=m_zakaz&nomer='+mz0_arr[0]+'">'+mz0_arr[0]+'</a>';
                            }
                            if (txt_mz2!=''){
                                txt+='<div class="m_zakaz_mz2"> <span>Другие заказы:</span> '+txt_mz2+'</div>';
                            }
                            
                            
                            if (data_n.r_st[i]!='' || data_n.r_di[i]!=''){
                                txt+='<div style="clear:both;"></div><div class="m_zakaz_item__service_info">';
                                
                                
                                //Диагноз
                                if (data_n.r_di[i]!=''){
                                    txt+=' <i class="fa fa-cog thumbnail"><span>'+data_n.r_di[i]+'</span></i>';
                                }
                                if (data_n.r_n[i]!='' || data_n.r_n[i]!=''){
                                    txt+='<span class="m_zakaz_item__r_neispr">'+data_n.r_n[i]+'</span>';
                                }
                                txt+='</div>';
                            }
                            
                            
                            txt+='<div style="clear:both;"></div>';
                            txt+='<div class="m_zakaz_item__mess_div"><i class="fa fa-eye"> Комментарии</i> <div class="m_zakaz_item__mess">'+data_n.h[i]+'</div></div>';
                            
                            
                        txt+='</div>';
                        txt+='<div class="m_zakaz_item_right">';
                            if (data_n.ao[i]!=''){
                                txt+='<div class="m_zakaz_item__otvet';
                                    if (data_n.cao[i]=='1'){txt+=' current_admin_otvet';}
                                    else{txt+=' other_admin_otvet';}
                                txt+='"><div class="for_mobile">Ответственный</div><span>'+data_n.ao[i]+'</span></div>';
                                txt+='<div class="clear"></div>';
                            }
                            txt+='<div class="m_zakaz_item__pay">'+time_+'<span>Оплата <span>'+number_format(data_n.p[i],0,'.',' ')+'</span><i class="fa fa-rouble"></i></span></div>';
                            txt+='<p class="m_zakaz_item__s_cat_itogo">';
                            txt+='Итого: <span>'+number_format(kol_,0,'.',' ')+'</span> шт. на сумму: <span>'+number_format(sum_,0,'.',' ')+' <i class="fa fa-rouble"></i></span>';
                            txt+='</p><div style="clear:both;"></div>';
                            
                            txt+='<div class="ttable m_zakaz_item__s_cat_tbl">'+txt_items+'</div><div class="m_zakaz_item__s_cat_tbl_bg"></div>';
                            
                        txt+='</div><div style="clear:both;"></div>';
                        txt+='</div>';
                        cur_cnt_++;
                    }
                    if (txt==''){txt='Заказов не найдено!';}
                    
    	            if (txt!=''){
    	               cur_cnt_=cur_cnt_+($('div.m_zakaz_item').size())-0;
                       if (cnt_>cur_cnt_){
                            txt+='<div class="m_zakaz__load_add">Загрузить ещё...</div>';
                       }
                       
                       var txt_chk='';
                       if (cnt_-0>0){txt_chk='<input type="checkbox" class="m_zakaz__all_find_select_chk_all" /> ';}
                       
                       txt='<p class="m_zakaz__all_find_res">'+txt_chk+' Найдено <strong>'+cnt_+'</strong> заказов'+end_word(cnt_,'й','е','я')+' на сумму: <strong>'+number_format(sum_all,0,'.',' ')+'</strong> руб., совершено платежей на сумму: <strong>'+number_format(pl_all,0,'.',' ')+'</strong> руб.</p>'+txt;
    	               
    	               $('.m_zakaz_find_res').append(txt);
                       chk_multiselect();
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
//Загрузка заказа для изменения
function zakaz_load(id,callback){
    callback=callback || '';
    
    
    
    add_zakaz_form_clear();
    var th_=$(this);
    var data_=new Object();
    data_['_t']='zakaz_load';
    data_['nomer']=id;
    
    //Формирование ссылки HISTORY
    history_url='?inc=m_zakaz&nomer='+id;
    history_name='№'+id+'. Заказы покупателей';
    History.replaceState({state:3}, history_name, history_url);
    
	loading(1);
	$.ajax({
		"type": "POST",
		"url": "ajax/m_zakaz.php",
		"dataType": "text",
		"data":data_,
		"success":function(data,textStatus){
			loading(0);
	        if (is_json(data)==true){
	            data_n=JSON.parse(data);
                
	            if (typeof data_n.d1 !='undefined'){
	               
                   
	               $('.m_zakaz_add__html_code_hide').html(data_n.h);
                   $('.m_zakaz_add_head h2').html('Изменение заказа №'+data_['nomer']+' <input type="hidden" value="'+data_['nomer']+'" name="nomer">');
                   
                    if (data_n.ci!=''){
                        $('input[name="c_call_client_id"]').val(data_n.ci);
                        $('.c_call_client_info_div').html('Оформление заказа по звонку №'+data_n.ci).show();
                    }
                    
                    
                    
                    $('input[name="date"]').val(data_n.d1);
                    $('input[name="date_info"]').val(data_n.d2);
                    $('input[name="project_name"]').val(data_n.pn);
                    $('textarea[name="comments"]').val(data_n.c);
                    
                    //Добавляем контрагента
                    i_contr_select(data_n['i_contr'],data_n['i_contr_org'],data_n['active']);
                    
                    //alert(data_n.a+'-'+$('select[name="a_admin"]').val());
                    $('select[name="a_admin"] option').removeAttr('selected');
                    $('select[name="a_admin"] option[value="'+data_n.a+'"]').prop('selected','selected');
                    $('select[name="a_admin"]').select2({'width':'100%',minimumResultsForSearch: 20});
                    change_worker_tp();//выбор работника
                    
                    $('select[name="a_admin_otvet"] option').removeAttr('selected');
                    $('select[name="a_admin_otvet"] option[value="'+data_n.ao+'"]').prop('selected','selected');
                    $('select[name="a_admin_otvet"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20});
                    
                    //товары, услуги
                    var sum_order=0;
                    var end_kol=0;
                    var all_kol=0;
                    var all_item_kol=0;
                    var all_work_kol=0;
                    var end_work_kol=0;
                    if (typeof data_n.i['i']!='undefined'){
                        var items_=data_n.i;
                        for(var i in items_.i){
                            
                             var barcode='';
                             if (items_.t[i]=='1'){all_item_kol=(all_item_kol-0)+(items_.k[i]-0);}
                             if (items_.t[i]=='2'){all_work_kol=(all_work_kol-0)+(items_.k[i]-0);}
                             
                             //товар
                             if (typeof items_.m_b!='undefined' && typeof items_.m_b[i]=='object'){
                                for(var m_tovar_id in items_.m_b[i]){
                                    if (barcode!=''){barcode+='@@';}
                                    barcode+=m_tovar_id+'##'+items_.m_b[i][m_tovar_id]+'##'+items_.m_k[i][m_tovar_id]+'##'+items_.m_v[i][m_tovar_id];
                                }
                                end_kol=(end_kol-0)+(items_.k[i]-0);
                                
                             }
                             //услуга
                             if (typeof items_.w_a!='undefined' && typeof items_.w_a[i]=='object'){
                                for(var m_zakaz_s_cat_a_admin_i_post_id in items_.w_a[i]){
                                    barcode=items_.w_a[i][m_zakaz_s_cat_a_admin_i_post_id]+'@@'+items_.w_p[i][m_zakaz_s_cat_a_admin_i_post_id]+'@@'+items_.w_s[i][m_zakaz_s_cat_a_admin_i_post_id];
                                }
                                end_work_kol=(end_work_kol-0)+(items_.k[i]-0);
                             }
                            all_kol=(all_kol-0)+(items_.k[i]-0);
                            sum_order=(sum_order-0)+(items_.p[i]*items_.k[i])-0;
                            s_cat_add(items_.i[i],items_.n[i],items_.k[i],items_.p[i],items_.t[i],items_.c[i],barcode,items_.img[i],items_.pr[i]);
                        }
                    }
                    
                    //товары из поступления
                    
                    if (typeof data_n.ip['i']!='undefined'){
                        var items_2=data_n.ip;
                        for(var i in items_2.i){
                             //товар
                             if (typeof items_.m_b!='undefined' && typeof items_.m_b[i]=='object'){
                                for(var m_tovar_id in items_.m_b[i]){
                                    if (barcode!=''){barcode+='@@';}
                                    barcode+=m_tovar_id+'##'+items_.m_b[i][m_tovar_id]+'##'+items_.m_k[i][m_tovar_id]+'##'+items_.m_v[i][m_tovar_id];
                                } 
                             }
                             
                             if (!in_array(items_2.i[i],items_.i)){
                                s_cat_add_in_postav(items_2.i[i],items_2.n[i],items_2.k[i],items_2.p[i],items_2.t[i],items_2.c[i],barcode,items_.img[i],items_2.pr[i]);
                             }
                            
                        }
                    }
                    s_cat_in_postav_chk();
                    
                    var status_order='';
                   
                    //платежи
                    var sum_pay=0;
                    if (typeof data_n.pl!='undefined' &&  typeof data_n.pl['p']!='undefined'){
                        var pl_=data_n.pl;
                        for(var i in pl_.p){
                            if (pl_.t[i]=='Кредит'){
                                sum_pay=(sum_pay-0)+(pl_.p[i]-0);
                            }
                            else if (pl_.t[i]=='Дебет'){
                                sum_pay=(sum_pay-0)-(pl_.p[i]-0);
                            }
                            pl_add(pl_.d[i],pl_.p[i],pl_.s[i],pl_.i[i],pl_.t[i]);
                        }
                    }
                    
                    var status_pay='';
                    if (sum_pay>0){
                        if (sum_order>sum_pay){
                            status_pay='Частично оплачен';
                            $('.status_order_div').addClass('no_full_pay');//добавлем класс
                        }
                        else if (sum_order==sum_pay){
                            status_pay='Оплачен';
                            $('.status_order_div').addClass('full_pay');//добавлем класс
                        }
                        else{
                            status_pay='Переплата';
                            $('.status_order_div').addClass('max_full_pay');//добавлем класс
                        }
                    }else{
                        status_pay='Не оплачен';
                        $('.status_order_div').addClass('no_pay');//добавлем класс
                    }
                    
                    var status_=data_n.st;
                    if (status_=='Отменен'){
                        $('.status_order_div').addClass('closed');//добавлем класс
                        $('.m_zakaz_add_form__save').hide();//скрываем кнопку сохранить
                        $('.m_zakaz_add_form__plus').show();
                    }else{
                        if (status_=='В обработке'){
                            $('.status_order_div').addClass('no_end');//добавлем класс
                        }
                        else if (status_=='Частично выполнен'){
                            $('.status_order_div').addClass('no_full_end');//добавлем класс
                        }
                        else if (status_=='Выполнен'){
                            $('.status_order_div').addClass('full_end');//добавлем класс
                        }
                        
                        status_='<span class="m_zakaz_current_status">'+status_+'</span> <span class="m_zakaz_current_status_pay">'+status_pay+'</span>';
                        $('.m_zakaz_add_form__close').show();
                        
                    }
                    //Сервис
                    if (typeof data_n.rem!='undefined'){
                        if (typeof data_n.rem.ti!='undefined' && data_n.rem.ti!=''){
                            
                            $('select[name="r_status"]').data('data_inform',data_n.rem.di).val(data_n.rem.stat).trigger('change');
                            $('select[name="r_tip_oborud"]').val(data_n.rem.ti).trigger('change');
                            $('select[name="r_brend"]').html('<option value="'+data_n.rem.bi+'">'+data_n.rem.bn+'</option>').val(data_n.rem.bi).trigger('change');
                            $('input[name="r_model"]').val(data_n.rem.mn);
                            $('input[name="komplekt"]').val(data_n.rem.k);
                            $('input[name="sost"]').val(data_n.rem.s);
                            $('input[name="r_service_id"]').val(data_n.rem.si);
                            $('.m_zakaz_r_service_status_info_data_vidachi').val(data_n.rem.dv);
                            
                            for (var n in data_n.rem.n){
                                r_neispr_add(data_n.rem.n[n],data_n.rem.nn[n]);
                            }
                            
                            $('select[name="r_brend"]').select2("close");
                            $('textarea[name="diagnoz"]').val(data_n.rem.dz);
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
                                t_txt='<div class="photo_res__item_image" style="background: url(../i/m_zakaz/small/'+img_+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></div>';
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
                            
                            txt_f+='<li class="photo_res__mess" data-img="'+img_+'"><a href="../i/m_zakaz/original/'+img_+'" target="_blank">'+t_txt+'</a><div class="m_zakaz_add__docs_res_div"><input class="m_zakaz_add__docs_res_text" placeholder="Название файла" value="'+name_+'" /></div><i title="Удалить документ" class="fa fa-remove"></i></li>';
                        }
                        if (txt_f!=''){
                            $('.m_zakaz_add__docs_res ul').html(txt_f);
                            $('.m_zakaz_add__docs_res ul').sortable();
                        }
                    }
                    
                    //доставка
                    if (typeof data_n.dost!='undefined'){
                        if (data_n.dost['chk_active']=='1'){
                            $('input[name="m_dostavka_chk_active"]').prop('checked',true);
                        }else{
                            $('input[name="m_dostavka_chk_active"]').prop('checked',false);
                        }
                        
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
                            $('select[name="i_tk_id"]').val(data_n.dost['i_tk_id']).select2({'width':'100%'});//trigger("change");
                        }else{
                            $('select[name="i_tk_id"] option').removeAttr('selected');
                            $('select[name="i_tk_id"] option:first').attr('selected','selected').closest('select').val(data_n.dost['i_tk_id']).trigger("change");
                        }
                    }
                    
                    //ссылки для печати 
                    $('.m_zakaz_all_info_icon').show();
                    $('.m_zakaz_all_info_icon ul li a').each(function(){
                        $(this).attr('href',$(this).data('href')+data_['nomer']);
                    });
                    
                    
                    
                    
                    //статус заказа
                    $('.status_order_div').html(status_).show();
                    
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
                    chk_docs();//проверка документов
                    
                    find_mess(data_['nomer'],'',function(){
                        if (typeof callback=='function'){callback(data_n);}
                    });
                    
                    $('select[name="r_brend"]').select2("close");
                    
                    //$('.m_zakaz_add_form_div_div input').trigger('blur');//убирает выделение авсех автокоплитов
                    $('.s_cat_items_find, .r_neispravnosti, input[name="r_model"], input[name="komplekt"], input[name="sost"]').autocomplete("close");
                    
                    $('.m_zakaz_add_form_div_div select').trigger('blur');
                    
                    
                    //открываем изменение заказа, если оно закрыто
                    if ($('.m_zakaz_add_form_open_div:visible').size()>0){
                        $('.m_zakaz_add_form_open_close_div').trigger('click');
                    }
                    //перемещаемся к изменению
                    $('html,body').animate({scrollTop: $('.m_zakaz_add_head>h2').offset().top}, {queue: false, easing: 'swing', duration: 300, complete: function(){
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

//СОХРАНЕНИЕ
function m_zakaz_add_form__save(callback){
    callback=callback || '';
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='m_zakaz__save';
    data_['nomer']=$('input[name="nomer"]').val();
    data_['a_admin_id']=$('select[name="a_admin"]').val();
    data_['a_admin_otvet_id']=$('select[name="a_admin_otvet"]').val();
    data_['i_contr_id']=$('input[name="i_contr"]').data('i_contr_id');
    data_['i_tp_id']=$('.i_tp_span_cur').data('id');
    
        if (data_['i_contr_id']=='' || data_['i_contr_id']==null){err_text+='<p>Укажите контрагента</p>';}
    data_['i_contr_org_id']='';
    if ($('.i_contr_tip_2.active').size()>0 && typeof $('input[name="i_contr"]').data('active_id')!='undefined'){
        data_['i_contr_org_id']=$('input[name="i_contr"]').data('active_id');
    }    
    data_['data']=$('input[name="date"]').val();
        if (data_['data']=='' 
        || data_['data']=='__.__.____'  
        || data_['data']==null  
        || data_['data']=='01.01.1970 03:00'){err_text+='<p>Укажите дату</p>';}
        
    data_['data_info']=$('input[name="date_info"]').val();
    data_['comments']=$('textarea[name="comments"]').val();
    data_['project_name']=$('.m_zakaz_add__project_name').val();
    data_['html_code']=$('.m_zakaz_add__html_code_hide').html();
    data_['c_call_client_id']=$('input[name="c_call_client_id"]').val();
    
    //Услуги, товары
    data_['i_i']='';
    data_['i_p']='';
    data_['i_k']='';
    data_['i_c']='';
    data_['i_b']='';
    data_['i_w']='';
    var i=0;
    $('.find_usluga_res__tbl .ttable_tbody_tr, .find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        var th_=$(this);
        
        if (i>0){data_['i_i']+='|||';}
        if (i>0){data_['i_p']+='|||';}
        if (i>0){data_['i_k']+='|||';}
        if (i>0){data_['i_c']+='|||';}
        if (i>0){data_['i_b']+='|||';}
        if (i>0){data_['i_w']+='|||';}
        
        
        data_['i_i']+=th_.data('id');
        data_['i_k']+=th_.find('.find_tovar__kol_span').text();
        data_['i_p']+=th_.find('.find_tovar__sum_span').text();
        data_['i_c']+=th_.find('.s_cat_info_tovar>div').html();
     
        if (typeof th_.find('.s_cat_set_barcode_ok').html()!='undefined'){
            data_['i_b']+=th_.find('.s_cat_set_barcode_ok .s_cat_cur_barcode').html();
        }
       
        if (typeof th_.find('.s_cat_set_worker_ok').html()!='undefined'){
            data_['i_w']+=th_.find('.s_cat_set_worker_ok .s_cat_cur_worker').html();
        }
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
    data_['m_dostavka_chk_active']=$('input[name="m_dostavka_chk_active"]').prop('checked');
    
    data_['r_tip_oborud']=$('select[name="r_tip_oborud"]').val();
    //Сервис
    if (data_['r_tip_oborud']!='' && typeof data_['r_tip_oborud']!='undefined'){
       
        data_['r_status']=$('select[name="r_status"]').val();
        data_['r_brend']=$('select[name="r_brend"]').val();
        data_['r_model']=$('input[name="r_model"]').val();
        data_['data_vidachi']=$('input[name="m_zakaz_r_service_status_info_data_vidachi"]').val();
        data_['komplekt']=$('input[name="komplekt"]').val();
        //data_['r_neispravnosti']=$('select[name="r_neispravnosti"]').val();//старое
        data_['r_neispravnosti']=sel_in_array($('.m_zakaz_r_neispravnosti_res>div'),'data','id');
        
        data_['sost']=$('input[name="sost"]').val();
        data_['r_service_id']=$('input[name="r_service_id"]').val();
        data_['diagnoz']=$('textarea[name="diagnoz"]').val();
    
        if (data_['status']==''){err_text+='<p>Укажите статус ремонта!</p>';}
        if (data_['r_tip_oborud']==''){err_text+='<p>Укажите тип оборудования!</p>';}
        if (data_['r_brend']==''){err_text+='<p>Укажите бренд оборудования!</p>';}
        if (data_['r_model']==''){err_text+='<p>Укажите модель оборудования!</p>';}
        if (data_['komplekt']==''){err_text+='<p>Укажите комплектацию оборудования!</p>';}
        if (count(data_['r_neispravnosti'])==0){err_text+='<p>Выберите неисправности!</p>';}
        if (data_['sost']==''){err_text+='<p>Укажите состояние оборудования!</p>';}
        
        if (data_['status']=='Выдан' && data_['data_vidachi']==''){
            err_text+='<p>Укажите дату выдачи!</p>';
        }
    }
    
    //if (count(data_['item']['id'])==0){err_text+='<p>Добавьте в заказ товары или услуги!</p>';}
    
    //Платежи
    data_['pl']=new Object();
    data_['pl']['id']=new Object();
    data_['pl']['data']=new Object();
    data_['pl']['sum']=new Object();
    data_['pl']['schet']=new Object();
    i=0;
    $('.m_zakaz_add_pl .ttable_tbody_tr').each(function(){
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
    $('.m_zakaz_add__docs_res ul li').each(function(){
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
    	            data_n=JSON.parse(data);
                    
    	            //Сумма на счетах  -отображение в шаблоне
                    if ($('.m_platezi_all_info').size()>0 && typeof get_sum_info_from_shablon=='function'){get_sum_info_from_shablon(); }
                    //Напоминания о заказах
                    if ($('.header_block__profile .m_zakaz_all_info').size()>0 && typeof get_zakaz_info_from_shablon=='function'){get_zakaz_info_from_shablon(); }
                    
                    zakaz_load(data_n.nomer,function(){
                       find();
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

//ОТМЕНА ЗАКАЗА
function m_zakaz_close(nomer,callback){
    callback=callback || '';
    
    var err_text='';
    var data_=new Object();
    data_['nomer']=nomer;
    data_['_t']='m_zakaz_close';
    
    if (data_['nomer']==''){
        err_text+='Не определен номер заказа для отмены';
    }
    
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
//ВОЗВРАТ из ОТМЕНЫ ЗАКАЗА
function m_zakaz_open(nomer,callback){
    callback=callback || '';
    
    var err_text='';
    var data_=new Object();
    data_['nomer']=nomer;
    data_['_t']='m_zakaz_open';
    
    if (data_['nomer']==''){
        err_text+='Не определен номер заказа для открытия';
    }
    
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

//Очистка формы добавления
function add_zakaz_form_clear(){

    $('select[name="a_admin"] option, select[name="a_admin_otvet"] option, .pl_schet option').removeAttr('selected');
        $('.pl_schet').change();
    $('input[name="i_contr"]').val('').data('i_contr_id','').data('i_contr_name','').data('i_contr_phone','').data('i_contr_email','').data('i_contr_org_id','').data('i_contr_org_name','').data('i_contr_org_cnt','').data('i_contr_org_email','').data('i_contr_org_phone','').data('active_id','').data('i_contr_org_cnt','');
    $('.i_contr_add_form_email, .i_contr_add_form_phone').html('');
    $('input[name="date"], .pl_data').val(date('d.m.Y H:i'));
    
    $('input[name="date_info"]').val('');
    $('input[name="project_name"]').val('');
    $('textarea[name="comments"]').val('');
    $('.pl_price').val('0');
    $('.find_tovar_res__tbl .ttable_tbody_tr').detach();
    $('.find_usluga_res__tbl .ttable_tbody_tr').detach();
    $('.m_zakaz_add_pl .ttable_tbody_tr').detach();
    $('.m_zakaz_add_pl .ttable_tbody_tr').detach();
    $('.m_zakaz_add_head h2').html('Добавление заказа <input type="hidden" value="" name="nomer">');
    $('.m_zakaz_all_mess ul, .m_zakaz_mess_files ul').html('');
    $('.m_zakaz_add__docs_res ul').html('');
    $('.m_zakaz_all_info_icon ul li a').attr('href','');
    $('.m_zakaz_add__html_code_hide').html('');
    CKEDITOR.instances.m_zakaz_mess_text.setData('');
    $('.m_zakaz_add_form__plus').hide();//убираем кнопку открытия заказа
    $('.m_zakaz_add_form__close').hide();//убираем кнопку отмены заказа
    $('.m_zakaz_add_form__save').show();//открываем кнопку сохранить
    //доставка
    $('.m_zakaz__dostavka_div input, .m_zakaz__dostavka_div textarea').val('');
    $('.m_zakaz__dostavka_div select').val('');
    $('.m_zakaz__dostavka_div select option').removeAttr('selected');
    $('.m_zakaz_all_info_icon').hide();
    
    $('select[name="a_admin"]').select2({'width':'100%',minimumResultsForSearch: 20});
    change_worker_tp();
    
    $('select[name="a_admin_otvet"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20});
    
    //Сервис
    $('select[name="r_status"]').data('data_inform','').val(null).trigger('change');
    $('select[name="r_tip_oborud"]').val(null).trigger('change');
    $('select[name="r_brend"]').val(null).trigger('change');
    //$('select[name="r_neispravnosti"]').val(null).trigger('change');///////старое
    $('.m_zakaz_r_neispravnosti_res').html('');
    
    $('input[name="r_model"]').val('');
    $('input[name="komplekt"]').val('');
    $('input[name="sost"]').val('');
    $('input[name="r_service_id"]').val('');
    $('.m_zakaz_r_service_status_info_txt').html('');
    $('.m_zakaz_r_service_status_info').html('');
    $('.m_zakaz_r_service_status_info_data_vidachi').val(date('d.m.Y H:i'));
    $('textarea[name="diagnoz"]').val('');
    $('.m_zakaz_add_items__mess_all').html('');
    //удаляем классы
    $('.status_order_div').removeClass('no_full_pay').removeClass('no_pay').removeClass('max_full_pay').removeClass('full_pay')
    .removeClass('no_end').removeClass('full_end').removeClass('no_full_end').removeClass('closed');
    $('.status_order_div').hide();//убираем статус заказа
    
    $('input[name="c_call_client_id"]').val('');//Очищаем номер звонка
    $('.c_call_client_info_div').html('').hide();//удаляем заказ по звонку
    
    //логирование
    $('.m_zakaz_log_text').html('');//Очистка логов

    
    chk_i_contr();
    chk_summ();//Проверяем сумму
    chk_comments(); //проверяем комментарии
    chk_pl();//проверка платежей
    chk_docs();//проверка документов
    s_cat_in_postav_chk();
    //Формирование ссылки HISTORY
    history_url='?inc=m_zakaz';
    history_name='Заказы покупателей';
    History.replaceState({state:3}, history_name, history_url);
    
}
//Добавляем товар в заказ по id
function s_cat_add_from_id(id_){
    id_= id_ || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='s_cat_add_from_id';
    data_['id_']=id_;
        if (id_==''){err_text+='<p>Не определен id для добавления</p>';}
    
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
    	            data_n=JSON.parse(data);
                    //alert(typeof data_n['i']);
                    var t_=new Array();
    	            if (typeof data_n['i']=='object'){
    	               for(var i in data_n['i']){
    	                    s_cat_arr['id']=data_n['i'][i]['id'];
                            s_cat_arr['name']=data_n['i'][i]['value'];
                            s_cat_arr['price']=data_n['i'][i]['p'];
                            s_cat_arr['tip']=data_n['i'][i]['t'];
                            s_cat_arr['img']=data_n['i'][i]['img'];
                            s_cat_arr['b']=data_n['i'][i]['b'];
                            s_cat_arr['k']=data_n['i'][i]['k'];
                            s_cat_arr['pr']=data_n['i'][i]['pr'];
                            t_[data_n['i'][i]['t']]=data_n['i'][i]['t'];//тип
                            //Формируем доступные штрих-коды
                            var barcode='';
                            if (typeof s_cat_arr['b']=='object'){
                                for (var m_tovar_id in s_cat_arr['b']){
                                    if (barcode!=''){barcode+='@@';}
                                    barcode+=m_tovar_id+'##'+s_cat_arr['b'][m_tovar_id]+'##'+s_cat_arr['k'][m_tovar_id]+'##0';
                                }
                            }
                            
                            s_cat_add(s_cat_arr['id'],s_cat_arr['name'],1,s_cat_arr['price'],s_cat_arr['tip'],'',barcode,s_cat_arr['img'],s_cat_arr['pr']);
                                 
    	               }
                       
                       if(typeof t_[1]=='string'){
                          $('.tabs_items_work').tabs("load",1);
                       }
                       else if(typeof t_[2]=='string'){
                          $('.tabs_items_work').tabs("load",2);
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
function s_cat_add(id,name,kol,price,s_cat_tip,comm,barcode,img,prop_val){
    comm=comm || '';
    barcode=barcode || '';
    img=img || '';
    prop_val=prop_val || '';
    
    var worker_or_barcode='';
    if (id!=''){
        
        if (s_cat_tip=='1' || s_cat_tip=='Товар'){
            var nom_=$('.find_tovar_res__tbl .ttable_tbody_tr').size()-0+1;
            var tip_name='товара';
            var tip_name2='товар';
            worker_or_barcode='<i class="fa fa-barcode s_cat_set_barcode" title="Задать штрих-код '+tip_name+'"><div class="s_cat_cur_barcode" style="display:none;">'+barcode+'</div></i>';
            
        }
        else{
            var nom_=$('.find_usluga_res__tbl .ttable_tbody_tr').size()-0+1;
            var tip_name='услуги';
            var tip_name2='услугу';
            worker_or_barcode='<i class="fa fa-user s_cat_set_worker" title="Указать исполнителя '+tip_name+'"><div class="s_cat_cur_worker" style="display:none;">'+barcode+'</div></i>';
            
        }
        
        var txt='';
        txt+='<div class="ttable_tbody_tr" data-id="'+id+'" data-nom="'+nom_+'">';
            txt+='<div class="ttable_tbody_tr_td find_tovar__id"><span class="for_mobile">№</span>';
                txt+='<span class="val">'+nom_+'</span>';
            txt+='</div>';
            txt+='<div class="ttable_tbody_tr_td"><span class="for_mobile">Название</span>';
                txt+='<span class="val"><span class="s_cat_name_td">'+name+' <span class="s_cat_name_prop_val">'+prop_val+'</span></span>';
                if (img!=''){
                    txt+='<a title="'+_IN(name)+'" href="../i/s_cat/original/'+_IN(img)+'" target="_blank" class="zoom"><i class="fa fa-image thumbnail"><span>'+id+'. '+name+'';
                    txt+='<img src="../i/s_cat/small/'+_IN(img)+'" />';
                    txt+='</span></i></a>';
                }
                txt+='</span>';
            txt+='</div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar__kol"><span class="for_mobile">Кол.</span>';
                txt+='<span class="find_tovar__kol_span val">'+kol+'</span>';
            txt+='</div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar__sum">';
                txt+='<span class="for_mobile">Цена</span>';
                txt+='<span class="find_tovar__sum_span val">'+price+'</span>';
            txt+='</div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar__itogo">';
                txt+='<span class="for_mobile">Итого</span>';
                txt+='<span class="val">'+price+'</span>';
            txt+='</div>';
            txt+='<div class="ttable_tbody_tr_td find_tovar_res__com_td">';
                txt+='<span class="for_mobile">Функции</span>';
                txt+='<span class="val">';
                txt+=worker_or_barcode;
                txt+='<i class="fa fa-file-word-o s_cat_info_tovar" title="Описание '+tip_name+'"><div class="s_cat_info_hidden">'+comm+'</div></i>';
                txt+='<i class="fa fa-edit s_cat_change_tovar" title="Изменить '+tip_name2+'"></i>';
                txt+='<i class="fa fa-remove s_cat_del_tovar" title="Удалить '+tip_name2+'"></i>';
                txt+='</span>';
            txt+='</div>';
        txt+='</div>';
        if (s_cat_tip=='1' || s_cat_tip=='Товар'){
            $('.find_tovar_res__tbl .ttable_tbody').append(txt);
        }else{
            $('.find_usluga_res__tbl .ttable_tbody').append(txt);
        }
        $('.s_cat_items_find').val('');
        
        chk_summ();//Проверяем сумму
        chk_comments();
        chk_barcode();//проверка штрих-кодов
        chk_workers();//проверка работников
        $('.zoom').fancybox({prevEffect:'none',nextEffect:'none',helpers:{title:{type:'outside'},thumbs:{width:50,height:50}}}); //фото
        
    }
     chk_docs();
}

//Добавление  товара из поступления
function s_cat_add_in_postav(id,name,kol,price,s_cat_tip,comm,barcode,img,prop_val){
    var txt='';
    txt+='<li title="Добавить товар к заказу" class="s_cat_add_in_postav_li" data-s_cat_id="'+id+'">'+name+'</li>';
    
    $('.tovar_res_from_postav ul').append(txt);
    
}

//Проверка добавления товара из потупления
function s_cat_in_postav_chk(){
    var txt='';
    $('.tovar_res_from_postav ul li').each(function(){
        txt+=$(this).html();
    });
    if (txt!=''){
        $('.tovar_res_from_postav').show();
    }else{
        $('.tovar_res_from_postav').hide();
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
		"url": "ajax/m_zakaz.php",
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
            $('.modal_i_contr_org_form .ttable_tbody_tr.mandat').each(function(){
                var val=$(this).find('input[name!=""],select[name!=""],textarea[name!=""]').val();
                if (val==''){
                    var tx=$(this).find('.ttable_tbody_tr_td:first').text();
                    err_text+='<p>Не заполнено поле [<strong>'+tx.trim()+'</strong>]</p>';
                }
            });
        data_['_t']='i_contr_org_form_save';
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
                     
        	            if (typeof callback=='function'){
        	               callback(data_n);
        	            }
                        $('.m_zakaz_item__i_contr_org').arcticmodal('close');
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
        $(this).find('.ttable_tbody_tr_td:first').html(i);
        i++;
    });
    i=1;
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        $(this).find('.ttable_tbody_tr_td:first').html(i);
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
    
    data_['_t']='m_zakaz__s_cat_add';
    data_['val']=val_;
    data_['nomer']=nomer;
    data_['s_struktura_id']=$('select[name="s_struktura_s_cat_select"]').val();
    
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
    			alert_m(data,'','s_cat_form','none');
                $('select[name="s_struktura_id"]').select2({'width':'100%',minimumResultsForSearch: 20});
                $('select[name="tip"]').select2({'width':'100%',minimumResultsForSearch: 20});
                $('form[name="m_zakaz_s_cat_add_form"] input[name="price"]').float_().spinner({min:0});
               
                $('form[name="m_zakaz_s_cat_add_form"] input[name="name"]').focus();
                
                $('.m_zakaz_s_cat_add_form_save').click(function(){
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
    var data_=$('form[name="m_zakaz_s_cat_add_form"]').serializeObject();
    data_['_t']='m_zakaz__s_cat_save';
    $('.m_zakaz_s_cat_add_form .ttable_tbody_tr.mandat').each(function(){
            var val=$(this).find('input[name!=""],select[name!=""],textarea[name!=""]').val();
            if (val==''){
                var tx=$(this).find('.ttable_tbody_tr_td:first').text();
                err_text+='<p>Не заполнено поле [<strong>'+tx.trim()+'</strong>]</p>';
            }
        });
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
    	            data_n=JSON.parse(data);
                    $('.modal_s_cat_form').arcticmodal('close');
    	            if (typeof callback=='function'){callback(data_n);}  
                    //логирование
                    var tip_tov_usl=$('.m_zakaz_add_items .ui-tabs-active .ui-tabs-anchor').text();
                    var price_='';if (data_n.price!=''){price_=', цена: '+data_n.price;}
                    if ($('.m_zakaz_s_cat_add_form input[name="nomer"]').val()==''){
                        if ((tip_tov_usl.split('Услуги').length - 1)>0){
                            add_log('','Добавление услуги',data_n.name+' #'+data_n.id+price_);
                        }else{
                            add_log('','Добавление товара',data_n.name+' #'+data_n.id+price_);
                        }
                    }
                    else{
                        if ((tip_tov_usl.split('Услуги').length - 1)>0){
                            add_log('Изменение услуги',data_n.name+' #'+data_n.id+price_);
                        }else{
                            add_log('Изменение товара',data_n.name+' #'+data_n.id+price_);
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
//Добавление платежа
function pl_add(data,summa,schet_id,pl_id,pl_tip){
    pl_id=pl_id || '';
    pl_tip=pl_tip || 'Кредит';
    summa=str_replace(' ','',summa);
    var znak='';
    if (pl_tip=='Кредит'){
        var print_txt='';if (schet_id=='461'){print_txt='<span class="m_zakaz_print_pko"><i title="Распечатать ПКО" class="fa fa-print"></i></span>';}
    }
    else if(pl_tip=='Дебет'){
        znak='-';
        var print_txt='';if (schet_id=='461'){print_txt='<span class="m_zakaz_print_rko"><i title="Распечатать РКО" class="fa fa-print"></i></span>';}
    }
    var schet_name= $('.pl_schet option[value="'+schet_id+'"]').text();
    if (pl_id==''){
      add_log('','Добавление платежа',znak+number_format(str_replace(' ','',summa),0,'.',' ')+' ('+schet_name+') '+data);
    }
    $('.m_zakaz_add_pl .ttable_tbody').append('<div class="ttable_tbody_tr" data-id="'+pl_id+'"><div class="ttable_tbody_tr_td"><span class="for_mobile">Дата</span><span class="val">'+data+'</span></div>'
        +'<div class="ttable_tbody_tr_td"><span class="for_mobile">Сумма</span><span class="pl_tr__sum val">'+znak+number_format(str_replace(' ','',summa),0,'.',' ')+'</span></div>'
        +'<div class="ttable_tbody_tr_td "><span class="for_mobile">Счет</span><span class="val pl_schet_name">'+schet_name+'</span></div>'
        +'<div class="ttable_tbody_tr_td m_zakaz_add_pl__com"><span class="for_mobile">Функции</span><span class="val">'
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

//Проверка доставки 
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
    $('.m_zakaz_add_items__dost_st').html(status_);
    $('.m_zakaz_add_work__dost_sum').html(summ_);
    
    //Оповещение клиента
    if($('input[name="m_dostavka_tracking_number"]').val()!='' && $('input[name="m_dostavka_data"]').val()!='' && $('select[name="i_tk_id"]').val()!=''){
        $('.m_zakaz__dostavka_send_mess').html('<span class="btn_gray">Оповестить клиента</span>');
       }else{
        $('.m_zakaz__dostavka_send_mess').html('');
       }
    
    //Проверка выбранности доставки
    if ($('.m_dostavka_chk_active').prop('checked')==true){
            $('.m_zakaz__dostavka_div_main').addClass('yes_dostavka').removeClass('no_dostavka');
            $('.m_dostavka_li').css({'display':'inline-block'});
        }
        else{
            $('.m_zakaz__dostavka_div_main').addClass('no_dostavka').removeClass('yes_dostavka');
            $('.m_dostavka_li').css({'display':'none'});
        }
}
//Проверка покупателя  //2018-05-17 toowin86
function chk_i_contr(){
    $('.m_zakaz_i_contr_tip>span').hide();
    
    var i_contr_id=$('input[name="i_contr"]').data('i_contr_id');
    var active_id=$('input[name="i_contr"]').data('active_id');
    var i_contr_org_cnt=$('input[name="i_contr"]').data('i_contr_org_cnt');

    if (typeof i_contr_id!='undefined' && i_contr_id!=''){
        $('.m_zakaz_i_contr_tip .i_contr_tip_1').show();
        if (typeof $('input[name="i_contr"]').data('i_contr_org_id0')!='undefined'){//есть организации
            $('.m_zakaz_i_contr_tip .i_contr_tip_2').show();
        }
        $('input[name="i_contr"]').removeClass('input_error').addClass('input_ok');
        $('.i_contr_com').html('<span class="fa fa-plus" title="Добавить нового контрагента"></span><span class="fa fa-edit" title="Изменить контрагента"></span>');
        
        //Заполняем телефон и имя
        var i_contr_tip=$('.m_zakaz_i_contr_tip>span.active').data('id');
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
		"url": "ajax/m_zakaz.php",
		"dataType": "text",
		"data":data_,
		"success":function(data,textStatus){
			loading(0);
           
	        alert_m(data,function(){
	           $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').autocomplete("destroy");
	        },'i_contr_form','none','');
            
            
           
	           $('.i_contr_org_current').sortable();
               $('.modal_i_contr_form .i_contr_mini_form_save').click(function(){
    	            
                    i_contr_form_save(callback);
                });
                if (val!='' && val!='Добавить нового контрагента' && $('.modal_i_contr_form input[name="name"]').val()==''){
                    $('.modal_i_contr_form input[name="name"]').val(val);
                }
                $('.modal_i_contr_form select[name="i_contr_org_tip_director"]').select2({'width':'100%',minimumResultsForSearch: 20});
                $('.modal_i_contr_form select[name="i_contr_org_na_osnovanii"]').select2({'width':'100%',minimumResultsForSearch: 20});
                
                $('.modal_i_contr_form .email').emailautocomplete();
                $('.modal_i_contr_form .phone').integer_().mask("0(000)000-00-00",{clearIfNotMatch: true,placeholder:'_(___)___-__-__'});
                $('.modal_i_contr_form input[name="adress"]').keyup();
                
                
                $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').keyup(function(){
                    i_contr_arr['id']='';
                });
                $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').dblclick(function(){
                    $(this).autocomplete( "search", $(this).val() );
                });
                
                
                $('.modal_i_contr_form input[name="i_contr_org_name_auto"]').autocomplete({
                        minLength: 0,
                        appendTo: ".i_contr_mini_form_org_add",
                        source: function(request, response){
                             request['_t']='i_contr_org_autocomplete';
                             if (typeof jqxhr!='undefined'){jqxhr.abort();}
                             jqxhr = $.ajax({
                            	"type": "POST",
                            	"url": "ajax/m_zakaz.php",
                            	"dataType": "text",
                            	"data":request,
                            	"success":function(data,textStatus){
                            	   if (is_json(data)==true){
                                	       var data_n=JSON.parse(data);
                                           response(data_n.items);
                                           
                                            $('.ui-autocomplete:visible').css({'z-index':'2000'});
                                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                                            
                                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                               var txt='<p>'+data_n.items[i].value+' (ИНН:'+data_n.items[i].i+')</p>';
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
                                $('input[name="i_contr_org_name_auto"]').val('');
                            }
                        }
                    });
                
                $('.modal_i_contr_form select[name="i_contr_i_contr_id"]').select2({'width':'100%', 
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
                  }});
                  
                $('.modal_i_contr_form select[name="i_reklama_id"]').select2({'width':'100%',minimumResultsForSearch: 20}).change(function(){
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
function chk_summ(callback){
    callback=callback || '';
    var sum=0;
    var kol=0;
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        var k=$(this).find('.find_tovar__kol .find_tovar__kol_span').text();k=k.replace(' ','');
        kol=(kol-0)+(k-0);
        var s=$(this).find('.find_tovar__sum .find_tovar__sum_span').text();s=s.replace(' ','');
      
        var itogo=(k*s)-0;
        sum=sum-0+itogo-0;
        $(this).find('.find_tovar__itogo .val').text(number_format(itogo,0,'.',' '));
      
    });
    $('.find_tovar_res_all_info .find_tovar_all_kol').text(kol);
        $('.m_zakaz_add_items__all_tovar').text(kol);
        $('.m_zakaz_add_items__all_sum').html('<span>'+sum+'</span> <i class="fa fa-rouble"></i>');
    $('.find_tovar_res_all_info .find_tovar_all_sum').text(number_format(sum,0,'.',' '));
    
    
    //услуги
    sum=0;
    kol=0;
    $('.find_usluga_res__tbl .ttable_tbody_tr').each(function(){
        var k=$(this).find('.find_tovar__kol .find_tovar__kol_span').text();k=k.replace(' ','');
        kol=(kol-0)+(k-0);
        var s=$(this).find('.find_tovar__sum .find_tovar__sum_span').text();s=s.replace(' ','');
      
        var itogo=(k*s)-0;
        sum=sum-0+itogo-0;
        $(this).find('.find_tovar__itogo .val').text(number_format(itogo,0,'.',' '));
        
    });
    $('.find_tovar_res_all_info .find_tovar_all_kol').text(kol);

        $('.find_usluga_all_kol, .m_zakaz_add_items__all_work').text(kol);
        $('.m_zakaz_add_work__all_sum').html('<span>'+sum+'</span> <i class="fa fa-rouble"></i>');
    $('.find_usluga_res_all_info .find_usluga_all_sum').text(number_format(sum,0,'.',' '));
    chk_all_opl(function(){
        if (typeof(callback)=='function'){callback();}
    });
}

//Проверка платежей
function chk_pl(callback){
    callback=callback || '';
    var pl_summ=0;
    $('.m_zakaz_add_pl .ttable_tbody_tr').each(function(){
        var sum=(($(this).find('.pl_tr__sum').text()).replace(' ',''))-0;
        pl_summ=pl_summ-0+sum-0;
    });
    $('.pl_all_kol, .m_zakaz_add_items__pl_kol').text($('.m_zakaz_add_pl .ttable_tbody_tr').size());
    $('.pl_all_sum').text(number_format(pl_summ,0,'.',' '));
    $('.m_zakaz_add_work__pl_sum').html('<span>'+pl_summ+'</span> <i class="fa fa-rouble"></i>');
    $('.pl_end_word').text(end_word(pl_summ,'ей','','а'));
    //alert(typeof(callback));
    chk_all_opl(function(callback){
        if (typeof(callback)=='function'){callback();}
    });
}
//Проверка необходимости оплаты
function chk_all_opl(callback){
    callback=callback || '';
    var pl_=$('.m_zakaz_add_work__pl_sum span').text()-0;
    var sum_w=$('.m_zakaz_add_work__all_sum span').text()-0;
    var sum_i=$('.m_zakaz_add_items__all_sum span').text()-0;
    if (((sum_w-0)+(sum_i-0))>(pl_-0)){
        var minus=((sum_w-0)+(sum_i-0))-(pl_-0);
        $('.pl_price').val(minus);
    }else{
        $('.pl_price').val('0');
    }
    if (typeof callback=='finction'){callback();}
    
}
//*****************************************************************************************************
//ДОБАВЛЕНИЕ СООБЩЕНИЯ
function add_mess(id,cl_,ut_,ui_,up_,un_,ue_,text,data_,file_arr,m_arr_ai,a_admin_id,data_sms,data_email,callback){
    callback = callback || '';
        
    var txt='';
    txt+='<li data-id="'+id+'" class="'+ut_+' '+cl_+'" data-tip="'+ut_+'" data-user_id="'+ui_+'">';
    txt+='<div class="m_zakaz_mess__user">';
        txt+=up_;
        txt+='<h3>'+un_+'</h3>';
        txt+='<div>'+ue_+'</div>';
        txt+='<p>'+text+'</p>';
    txt+='</div>';
    txt+='<div class="m_zakaz_mess__message">';
        txt+='<p>'+data_+'</p>';
        txt+='<div class="send_mess_comm">';
        
        
            txt+='<span class="send_mess_comm_sms btn_gray"><i class="fa fa-mobile"></i> ';
            if (data_sms!=''){
                txt+='Оповещен по sms: <span>'+data_sms+'</span>';
            }else{
                txt+='Оповестить по sms';
            }
           txt+='</span>';
            txt+='<span class="send_mess_comm_email btn_gray"><i class="fa fa-envelope-o"></i> ';
            if (data_email!=''){
                txt+='Оповещен по email: <span>'+data_email+'</span>';
            }else{
                txt+='Оповестить по email';
            }
           txt+='</span>';
       
        
        txt+='</div>';
        txt+='<div class="m_zakaz_mess__files">';
        
        var file_=file_arr;
        var txt_file='';
        var path_='../i/m_dialog/original/';
        for(var j in file_.n){
            ext=(file_.n[j]).replace(reg_ext, "");
            if (ext=='jpg' || ext=='jpeg' || ext=='png' || ext=='gif'){
                txt_file+='<li><a target="_blank" rel="mess" href="'+path_+file_.n[j]+'" class="zoom"><img src="../i/m_dialog/small/'+file_.n[j]+'" /></a><div>'+file_.t[j]+'</div></li>';
            }else{
                txt_file+='<li><a target="_blank" rel="mess" href="'+path_+file_.n[j]+'"><i class="fa fa-file-o"></i></a><div>'+file_.t[j]+'</div></li>';
            }
            
        }
        if (txt_file!=''){
            txt+='<ul>'+txt_file+'</ul><div style="clear:both;"></div>';
        }
        
        txt+='</div>';
        if (a_admin_id==m_arr_ai){
            txt+='<div class="command"><i class="fa fa-remove"></i></div>';
        }
        //<i class="fa fa-envelope"></i>
    txt+='</div><div style="clear:both;"></div>';
    txt+='</li>';
    
    if (typeof callback=='function'){callback();}
    return txt;
}


//*****************************************************************************************************
//ПОИСК ПО СООБЩЕНИЯМ
function find_mess(id,limit,callback){
    id=id || '';
    limit=limit || '';
    callback=callback || '';
    
        if (limit==''){$('.m_zakaz_all_mess ul').html('');}
    var err_text='';
    var data_=new Object();
    data_['_t']='m_zakaz__mess_find';
    data_['id']=id;
    data_['limit']=limit;
    
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
    	            data_n=JSON.parse(data);
                    
                    var txt='';
                    var cnt_load=0;
                    if (typeof data_n.m['i']!='undefined'){
                        var m_arr=data_n.m;
                        cnt_load=count(m_arr.i);
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
                                
                                ut_='admin';
                            }
                            else if (typeof m_arr.ii[i] !='undefined'){//контрагент
                                ui_=m_arr.ii[i];
                                un_=m_arr.in_[i];
                                ue_=m_arr.ie[i];
                                up_='';if (m_arr.ap[i]!=''){up_='<img src="../i/i_contr/original/'+m_arr.ip[i]+'" />';}
                                ut_='user';
                            }
                            
                            txt+=add_mess(m_arr.i[i],cl_,ut_,ui_,up_,un_,ue_,m_arr.d[i],m_arr.m[i],m_arr.f[i],m_arr.ai[i],a_admin_id,m_arr.ds[i],m_arr.de[i]);
                            
                        }
                        
                        //всего сообщений
                        var cnt_mess_all=data_n.cnt_;
                        
                        if ($('.m_zakaz_item__window').size()>0){//Выводим в окне
                            $('.m_zakaz_item__window>ul').append(txt);
                            var cnt_mess_cur=($('.m_zakaz_item__window>ul>li').size()-0);
                            $('.m_zakaz_add_items__mess_all__window').html('Отображено '+cnt_mess_cur+' соосбщени'+end_word(cnt_mess_cur,'й','е','я')+' из '+cnt_mess_all+' соосбщени'+end_word(cnt_mess_all,'й','е','я'));
                            if (cnt_mess_cur<cnt_mess_all){
                                $('.m_zakaz_item__window').append('<p class="load_new_mess_in_zakaz">Загрузить еще сообщения</p>');
                            }
                            $('.m_zakaz_item__window .zoom').fancybox({prevEffect:'none',nextEffect:'none',helpers:{title:{type:'outside'},thumbs:{width:50,height:50}}}); //фото
                        }
                        else{//Загружаем в заказ
                            $('.m_zakaz_all_mess>ul').append(txt);
                            var cnt_mess_cur=($('.m_zakaz_all_mess>ul>li').size()-0);
                            $('.m_zakaz_add_items__mess_all').html('Отображено '+cnt_mess_cur+' соосбщени'+end_word(cnt_mess_cur,'й','е','я')+' из '+cnt_mess_all+' соосбщени'+end_word(cnt_mess_all,'й','е','я'));
                            if (cnt_mess_cur<cnt_mess_all){
                                $('.m_zakaz_all_mess').append('<p class="load_new_mess_in_zakaz">Загрузить еще сообщения</p>');
                            }
                            $('.m_zakaz_all_mess .zoom').fancybox({prevEffect:'none',nextEffect:'none',helpers:{title:{type:'outside'},thumbs:{width:50,height:50}}}); //фото
                        }
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
        data_['text']=CKEDITOR.instances.m_zakaz_mess_text.getData();//$('.m_zakaz_mess_text').val();
     
        var i=0;
        data_['f']=new Object();
            data_['f']['i']=new Object();
            data_['f']['t']=new Object();
        $('.m_zakaz_mess_files li.photo_res__mess').each(function(){
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
        		"url": "ajax/m_zakaz.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			loading(0);
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        CKEDITOR.instances.m_zakaz_mess_text.setData('');
                        $('.m_zakaz_mess_files ul').html('');
        	            if (typeof callback=='function'){callback(data_n);}   
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    }else{
        alert_m('Не определен номер заказа!','','error','none');
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
    		"url": "ajax/m_zakaz.php",
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
//****************************************************************************************************
//Назначение штрих-кодов
function s_cat_set_barcode(th_, callback){
    callback=callback || '';
    var id_=th_.closest('.ttable_tbody_tr').data('id');
    var id_all='';
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        if (id_all!=''){id_all+=',';}
        id_all+=$(this).data('id');
    });
    var name_=th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').html();
    var kol_need=th_.closest('.ttable_tbody_tr').find('.find_tovar__kol_span').html();
    var val='';
    
    if (th_.find('.s_cat_cur_barcode').size()>0){
        var barcode=th_.find('.s_cat_cur_barcode').html();
        if (barcode!=''){
            var barcode_arr=barcode.split('@@');
            for(var i in barcode_arr){
                var arr=(barcode_arr[i]).split('##');
                if (arr[2]>0){//если товар в наличии
                    var disabled_='disabled="disabled"';var sel='';if (arr[3]>0){sel=' class="selected"';disabled_='';}
                    var kol_txt='';if ((arr[2]-0)>1){kol_txt='<input type="text" class="part_kol" placeholder="Кол." data-max="'+_IN(arr[2])+'" value="'+arr[3]+'" '+disabled_+' />';}
                    val+='<li'+sel+' value="'+arr[0]+'" data-barcode="'+_IN(arr[1])+'" data-kol="'+_IN(arr[2])+'">'+arr[1]+' '+kol_txt+'</li>';
                }
            }
        }
    }
    
    if (barcode!=''){
        var txt='<div class="s_cat_set_barcode__form">';

        if (val==''){
            txt+='<h2>Данного товара нет на складе. Сделайте поступление: </h2>';
            txt+='<div style="margin:10px 0;"><a href="?inc=m_postav&add_id='+id_+'" target="_blank">'+name_+'</a></div> <div><a class="btn_gray" href="?inc=m_postav&add_id='+id_all+'" target="_blank">всех товаров</a></div>';
    
        }
        else{
            txt+='<h2>Введите штрих-коды товаров</h2>';
            txt+='<p>Выбранно: <span class="s_cat_set_barcode__form_chk"></span></p>';
            txt+='<p><ul class="s_cat_set_barcode__cur">';
            txt+=val;
            txt+='</ul></p>';
            txt+='<div><center><span class="btn_orange s_cat_set_barcode_save">Выбрать</span></center></div>';
        }
        
        txt+='</div>';
        alert_m(txt,'','info','none');
        
        //автовыбор  товара
        if ($('.s_cat_set_barcode__cur li.selected').length==0){//если не выбран товар
            var mm=0;
            $('.s_cat_set_barcode__cur li').each(function(){
               if (kol_need<mm){
                $(this).trigger('click');
               }
               mm++; 
            });
        }
        
        $('.part_kol').each(function(){
            var max=$(this).data('max');
            $(this).spinner({max:max,min:1,stop: function( event, ui ) {chk_barcode_form();}});
        });
        
        chk_barcode_form();
        //сохранение штрих-кодов
        $('.s_cat_set_barcode_save').click(function(){
            s_cat_set_barcode_save(th_,function(){
                
            });
        });
        if (typeof callback=='function'){callback(data_n);}
    }else{
        alert_m('<h2>Данного товара нет на складе. Сделайте поступление: </h2><div style="margin:10px 0;"><a href="?inc=m_postav&add_id='+id_+'" target="_blank">'+name_+'</a></div> <div><a class="btn_gray" href="?inc=m_postav&add_id='+id_all+'" target="_blank">всех товаров</a></div>','','error','none');
    }
}

//сохранение штрих-кодов
function s_cat_set_barcode_save(th_, callback){
    callback=callback || '';
    var all_kol=0;
    var barcode='';
    $('.s_cat_set_barcode__cur li').each(function(){
        
        var cl_=$(this).attr('class');
        var kol=0; if (cl_=='selected'){
            if ($(this).find('input').size()>0){
                kol=$(this).find('input').val()-0;
            }else{
                kol=1;
            }
        }
        all_kol+=(kol-0);
        if (barcode!=''){barcode+='@@';}
        barcode+=$(this).attr('value')+'##'+$(this).data('barcode')+'##'+$(this).data('kol')+'##'+kol;
    });
    
    th_.find('.s_cat_cur_barcode').html(barcode);
    $('.s_cat_set_barcode__form').arcticmodal('close');
    if (all_kol>0){
        th_.closest('.ttable_tbody_tr').find('.find_tovar__kol_span').html(all_kol);
    }
    chk_summ();//Проверяем сумму
    chk_barcode();//проверка штрих-кодов
    if (typeof callback=='function'){callback();}
    
    //логирование
    add_log('','Выбор штрих-кода товара',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+barcode);
}

//Проверка штрих-кодов внутри формы выбора штрих-кода
function chk_barcode_form(){
    var kol=0;
    $('.s_cat_set_barcode__cur li').each(function(){
        
        var cl_=$(this).attr('class');
        if (cl_=='selected'){
            if ($(this).find('input').size()>0){
                kol=(kol-0)+($(this).find('input').val()-0);
            }
            else{
                kol++;
            }
        }
    });
    $('.s_cat_set_barcode__form_chk').html(kol+' товар'+end_word(kol,'ов','','а'));
}
//Проверка штрих-кодов в заказе
function chk_barcode(){
    
    $('.find_tovar_res__tbl .ttable_tbody_tr').each(function(){
        var th_=$(this);
        var err=1;
        
        var kol_=th_.find('.find_tovar__kol_span').html()-0;
        var barcode=th_.find('.s_cat_cur_barcode').html();
        if (barcode!=''){
            var cur_kol=0;
            var barcode_arr=barcode.split('@@');
            for (var i in barcode_arr){
                var barcode_arr2=(barcode_arr[i]).split('##');
                var k_=barcode_arr2[3]-0;
                if (k_>0){
                    cur_kol+=k_-0;
                }
            }
        }
        if (cur_kol==kol_){
            err=0;
        }
        
        if (err==0){th_.find('.s_cat_set_barcode').addClass('s_cat_set_barcode_ok').removeClass('s_cat_set_barcode_error');}
        else{th_.find('.s_cat_set_barcode').addClass('s_cat_set_barcode_error').removeClass('s_cat_set_barcode_ok');}
    });
}


//****************************************************************************************************
//Назначение работника на работу
function s_cat_set_worker(th_, callback){
    // a_admin_id@@a_admin_i_post_id@@summa // summa для устанавливаемой вручную суммы, иначе она равна ''
    // пример: 5@@12@@ или 8@@21@@1200
    
    callback=callback || '';
    
    var kol_need=th_.closest('.ttable_tbody_tr').find('.find_tovar__kol_span').html();
    var val='';
    
    if (th_.find('.s_cat_cur_worker').size()>0){
        var worker=th_.find('.s_cat_cur_worker').html();
        if (worker!=''){
            var worker_arr=worker.split('@@');
            for(var i in worker_arr){
                var a_admin_id=worker_arr[0];
                var a_admin_i_post_id=worker_arr[1];
                var summa=worker_arr[2]-0;
            }
        }
    }
    
    //выводим форму для выбора работников
    var err_text='';
    var data_=new Object();
    data_['_t']='s_cat_set_worker';
    data_['nom']=th_.closest('.ttable_tbody_tr').data('nom');
    
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
    	        alert_m(data,'','info','none');
                $('.s_cat_set_worker_select').val(a_admin_id);
                $('.s_cat_set_worker_res ul[data-id="'+a_admin_id+'"]').show();
                $('.s_cat_set_worker_res ul[data-id="'+a_admin_id+'"] li[data-id="'+a_admin_i_post_id+'"]').addClass('active');
                change_worker($('.s_cat_set_worker_res ul:visible li[data-id="'+a_admin_i_post_id+'"]'),summa);
                
                
                $('.s_cat_set_worker_select').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
                    s_cat_set_worker_select_change();
                }).on("select2:open", function (e) {

                    var select2Instance = $('.s_cat_set_worker_select').data('select2');
                        select2Instance.on('results:message', function(params){
                          this.dropdown._resizeDropdown();
                          this.dropdown._positionDropdown();
                        });
                });
                
                
    		}
    	});
    }
}
//выбор должности работника
function change_worker(th_,val){
    val=val || '';
    $('.s_cat_set_worker_res ul li').removeClass('active');
    $('.s_cat_set_worker_res_auto').html('');
    var auto=th_.data('auto');
    if (auto=='0'){
        $('.s_cat_set_worker_res_auto').html('<input type="text" name="summa" placeholder="Сумма" value="'+val+'" />');
    }
    $('.s_cat_set_worker_res_auto input').autoNumeric('init');//цена
    th_.addClass('active');
}

//выбор работника при назначении работы
function s_cat_set_worker_select_change(){
    var a_admin_id=$('.s_cat_set_worker_select option:selected').val();
    $('.s_cat_set_worker_res ul').hide();
    $('.s_cat_set_worker_res ul[data-id="'+a_admin_id+'"]').show();
    $('.s_cat_set_worker_res ul:visible li:first').trigger('click');
}

// Сохраняем присвоение к работ работника
function s_cat_set_worker_save(){
    var nom=$('.s_cat_set_worker_div').data('nom');
    var err_text='';
    var a_admin_id = $('.s_cat_set_worker_div .s_cat_set_worker_select').val();
    if (a_admin_id==''){
        var txt='';
    }
    else{
        var i_post_id=$('.s_cat_set_worker_res ul li.active').data('id');
            if (typeof i_post_id=='undefined'){err_text+='<p>Укажите должность!</p>';}
        var worker_summa=$('.s_cat_set_worker_res_auto input[name="summa"]').val();
            if (typeof worker_summa=='undefined'){
                worker_summa='';
            }
            
            if($('.s_cat_set_worker_res_auto input[name="summa"]:visible').size()==1 && worker_summa==''){
                err_text+='<p>Укажите сумму!</p>';
            }
            worker_summa=worker_summa.replace(' ','');
            var txt=a_admin_id+'@@'+i_post_id+'@@'+worker_summa;
    }
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
        
        $('.find_usluga_res__tbl .ttable_tbody_tr[data-nom="'+nom+'"]').find('.s_cat_cur_worker').html(txt);
        $('.s_cat_set_worker_div').arcticmodal('close');
        //логирование
        var sm_='';if (worker_summa!=''){sm_=', сумма '+worker_summa;}
        add_log('','Назначение работника на услугу',$('.find_usluga_res__tbl .ttable_tbody_tr[data-nom="'+nom+'"]').find('.s_cat_name_td').text()+' #'+$('.find_usluga_res__tbl .ttable_tbody_tr[data-nom="'+nom+'"]').data('id')+': '+$('.s_cat_set_worker_div .s_cat_set_worker_select option:selected').text()+' ('+$('.s_cat_set_worker_res ul li.active').text()+sm_+')');
                    
    }
    
    chk_workers();//проверка работников
}
//проверка работников
function chk_workers(){
    
    $('.find_usluga_res__tbl .ttable_tbody_tr').each(function(){
        var th_=$(this);
        if ($(this).find('.s_cat_cur_worker').html()!=''){
            th_.find('.s_cat_set_worker').addClass('s_cat_set_worker_ok').removeClass('s_cat_set_worker_error')
        }else{
            th_.find('.s_cat_set_worker').addClass('s_cat_set_worker_error').removeClass('s_cat_set_worker_ok');
        }
    });
       
}

//Отправка sms о статусе
function service_status_info_send_sms(callback){
    callback=callback || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='service_status_info_send_sms';
    data_['nomer']=$('.m_zakaz_add_head input[name="nomer"]').val();
    
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
    	            data_n=JSON.parse(data);
    	            if (typeof callback=='function'){
    	               callback(data_n);
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
    
}

//Отправка sms о диагнозе
function service_diagnoz_send_sms(callback){
    callback=callback || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='service_diagnoz_send_sms';
    data_['nomer']=$('.m_zakaz_add_head input[name="nomer"]').val();
    
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
    	            data_n=JSON.parse(data);
    	            if (typeof callback=='function'){
    	               callback(data_n);
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

//Выдача оборудования
function r_service_status_otdan_change(callback){
    callback=callback || '';
    //добавляем платеж
    //alert('+1');
    $('.m_zakaz_add_pl__add_com').trigger('click');
    if ($('.m_zakaz_add_items__all_tovar').text()-0>0){
        
        var link=$('.m_zakaz_all_info_icon').find('a[href*="docx_tovar_check"]').attr('href');
        window.open(link);
    }
    if ($('.m_zakaz_add_items__all_work').text()-0>0){
        var link=$('.m_zakaz_all_info_icon').find('a[href*="docx_act"]').attr('href');
        window.location.href = link; 
    }
    $('.m_zakaz_add_pl .ttable_tbody_tr:last').find('.m_zakaz_print_pko').trigger('click');//ПКО
   
}

//Отправка сообщения
function send_mess_comm_send(id_mess,tip,callback){
    callback=callback || '';
    var err_text='';
    var data_=new Object();
    data_['_t']='send_mess_comm_send';
    data_['tip']=tip;
    data_['id']=id_mess;
    
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
    	            data_n=JSON.parse(data);
    			}
    			else{
    				alert_m(data,'','error','none');
    			}
                if (typeof(callback)=='function'){callback(data);}      
    		}
    	});
    }
}

//Проверка кнопки для отправки ссообщения
function chk_send_button(){
    $('.m_zakaz_all_mess ul .send_mess_comm_sms').hide();
    if ($('.i_contr_add_form_phone a').text()!=''){
        $('.m_zakaz_all_mess ul .send_mess_comm_sms').show();
    }
    
    $('.m_zakaz_all_mess ul .send_mess_comm_email').hide();
    if ($('.m_zakaz_item__i_contr_email a').text()!=''){
        $('.m_zakaz_all_mess ul .send_mess_comm_email').show();
    }
}
//Форма добавления неисправности
function r_neispr_add(id,name,callback){
    var arr_id=sel_in_array($('.m_zakaz_r_neispravnosti_res>div'),'data','id');
    callback=callback || '';
    var txt='';
    
    if (in_array(id,arr_id)){
        alert_m('Данная неисправность уже выбрана!','','error');
    }
    else{
        txt+='<div data-id="'+id+'"><strong>'+name+'</strong> <span><i class="fa fa-remove m_zakaz_r_neispr_remove" title="Удалить"></i></span></div>';
        $('.m_zakaz_r_neispravnosti_res').append(txt);
        $('.r_neispravnosti').val('').trigger('focus');
        
    }
    
    if (typeof(callback)=='function'){callback();}
}
//Форма добавления новой неисправности
function r_neispr_form_new(term,callback){
    term=term || '';
    var txt='';
    txt+='<div class="r_neispr_form_new">';
    txt+='<h2>Добавление новой неисправности</h2>';
    txt+='<p>Укажите тип оборудования:</p>';
    txt+='<select class="r_neispr_form_new_tip_oborud" data-placeholder="Тип оборудования"></select>';
    txt+='<p>Название неисправности:</p>';
    txt+='<input type="text" class="r_neispr_form_new_name" placeholder="Неисправность" value="'+term+'">';
    txt+='<div class="r_neispr_form_new_com"><span class="r_neispr_form_new_save btn_orange">Сохранить</span></div>';
    txt+='</div>';
    
    alert_m(txt,'','add','none');
    $('.r_neispr_form_new_tip_oborud').html($('select[name="r_tip_oborud"]').html()).select2({'width':'100%',minimumResultsForSearch: 20});
    $('.r_neispr_form_new_tip_oborud').val($('select[name="r_tip_oborud"]').val()).trigger('change');
    $('.r_neispr_form_new_name').focus();
}
//сохранение новой неисправности
function r_neispr_form_new_save(){
    var err_text='';
    var data_=new Object();
    data_['_t']='r_neispr_form_new_save';
    data_['tip_']=$('.r_neispr_form_new_tip_oborud').val();
        if (data_['tip_']==''){err_text+='<p>Укажите тип оборудования!</p>';}
    data_['name_']=$('.r_neispr_form_new_name').val();
        if (data_['name_']==''){err_text+='<p>Название неисправности не может быть пустой</p>';}
    
    
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
    	            data_n=JSON.parse(data);
    	            if (typeof data_n.id!='undefined'){
    	               $.arcticmodal('close');
    	               r_neispr_add(data_n.id,data_['name_']);
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}


///Проверка на вывод документов
function chk_docs(){
    $i=0;
    //Договор приема
    $('a[data-href*="file_name=dogovor3"]').closest('li').hide();
    if ($('a[href="#tabs-7"]:visible').size()>0
        && $('select[name="r_status"]').val()!=''
        && $('select[name="r_tip_oborud"]').val()!='' 
        && $('select[name="r_brend"]').val()!=''
        && $('input[name="r_model"]').val()!=''
        && $('input[name="komplekt"]').val()!=''
        && $('.m_zakaz_r_neispravnosti_res>div').size()>0
        && $('input[name="sost"]').val()!=''
        
        ){
        $i=1;
        $('a[data-href*="file_name=dogovor3"]').closest('li').show();
    }
    
    
    //Бланк посылки
    $('a[data-href*="file_name=post_form"]').closest('li').hide();
    if ($('a[href="#tabs-5"]:visible').size()>0
        && $('input[name="m_dostavka_fio"]').val()!=''
        && $('select[name="m_dostavka_city_id"]').val()!='' 
        && $('input[name="m_dostavka_adress"]').val()!=''
        && $('input[name="m_dostavka_index"]').val()!=''
        && $('input[name="m_dostavka_phone"]').val()!=''
        ){
        $i=1;
        $('a[data-href*="file_name=post_form"]').closest('li').show();
    }
    
    
    // Счет
    $('a[data-href*="file_name=docx_schet"]').closest('li').hide();
    if ( ( ($('.m_zakaz_add_items__all_work').text()-0)>0 || ($('.m_zakaz_add_items__all_tovar').text()-0)>0)
    //&& $('.i_contr_tip_2.active').size()>0 
        
    ){
        $i=1;
        $('a[data-href*="file_name=docx_schet"]').closest('li').show();
    }
    $('a[data-href*="file_name=schet_print"]').closest('li').hide();
    if ($('.i_contr_tip_2.active').size()>0
        && ( ($('.m_zakaz_add_items__all_work').text()-0)>0 || ($('.m_zakaz_add_items__all_tovar').text()-0)>0)
        
    ){
        $i=1;
        $('a[data-href*="file_name=schet_print"]').closest('li').show();
    }
    //Товарный чек
    $('a[data-href*="file_name=docx_tovar_check"]').closest('li').hide();
    if ( $('#tabs-1 .find_tovar__id').length>0){
        $i=1;
        $('a[data-href*="file_name=docx_tovar_check"]').closest('li').show();
    }
    
    //Акт выполненных работ
    $('a[data-href*="file_name=docx_act"]').closest('li').hide();
    if ((($('.m_zakaz_add_items__all_work').text()-0)>0)
        && $('.s_cat_set_worker_error').size()==0
    ){
        $i=1;
        $('a[data-href*="file_name=docx_act"]').closest('li').show();
    }
    
    //ТТНif ( $('#tabs-1 .find_tovar__id').length>0)
    $('a[data-href*="file_name=ttn"]').closest('li').hide();
    if ($('.i_contr_tip_2.active').size()>0
        && $('#tabs-1 .find_tovar__id').length>0
    ){
        $i=1;
        $('a[data-href*="file_name=ttn"]').closest('li').show();
    }
    
    
    //Счет-фактура
     $('a[data-href*="file_name=sf"]').closest('li').hide();
    if ($('.i_contr_tip_2.active').size()>0
        && ( ($('.m_zakaz_add_items__all_work').text()-0)>0 || ($('.m_zakaz_add_items__all_tovar').text()-0)>0)
        && $('.s_cat_set_barcode_error').size()==0
        && $('.s_cat_set_worker_error').size()==0
    ){
        $('a[data-href*="file_name=sf"]').closest('li').show();
    }
    //Гарантия
     $('a[data-href*="file_name=garant_t"]').closest('li').hide();
    if ( ( ($('.m_zakaz_add_items__all_work').text()-0)>0 || ($('.m_zakaz_add_items__all_tovar').text()-0)>0)
        && $('.s_cat_set_barcode_error').size()==0
        && $('.s_cat_set_worker_error').size()==0
    ){
        $i=1;
        $('a[data-href*="file_name=garant_t"]').closest('li').show();
    }
    
    //Диагностическое
     $('a[data-href*="file_name=diagnoz"]').closest('li').hide();
    if ($('.r_service_diagnoz').val()!=''
        
    ){
        $i=1;
        $('a[data-href*="file_name=diagnoz"]').closest('li').show();
    }
    //отображаем перечень документов
    if($i==1){
        $('.m_zakaz_all_info_icon').show();
    }else{
        $('.m_zakaz_all_info_icon').hide();
    }
}

//выбор  бренда
function brend_add(id,name,callback){
    callback=callback || '';
    $('select[name="r_brend"]').html('<option value="'+id+'" selected="selected">'+name+'</option>');
    $('select[name="r_brend"]').trigger('change').select2("close");
    chk_docs();//проверка документов
}


//Заполнение заказа из звонка
function zakaz_load_from_call(id_,callback){
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='zakaz_load_from_call';
    data_['id']=id_;
    
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
    	            data_n=JSON.parse(data);
                    
                    //Получаем контр-агента
                    i_contr_select(data_n.i_contr_id,data_n.i_contr_name,data_n.i_contr_org_id,data_n.i_contr_org_name,data_n.i_contr_phone,data_n.i_contr_email);
                    
                    //alert(data_n['r_tip_oborud']+typeof data_n['r_tip_oborud']);
                    if (typeof data_n['r_tip_oborud']=='object' || typeof data_n['r_brend']=='object' || typeof data_n['r_model']=='object'){
                        $('select[name="r_status"]').val('Принят').trigger('change');
                        $('.m_zakaz_r_service_status_info_diagnoz_add').trigger('click');
                    }
    	            if (typeof data_n['r_tip_oborud']=='object' && typeof data_n['r_tip_oborud']['val']!='undefined'){
    	               $('select[name="r_tip_oborud"] option').removeAttr('selected');
    	               $('select[name="r_tip_oborud"] option[data-val="'+data_n['r_tip_oborud']['val']+'"]').attr('selected','selected');
                       $('select[name="r_tip_oborud"]').trigger('change').select2('close');
    	            }
    	            if (typeof data_n['r_brend']=='object' && typeof data_n['r_brend']['val']!='undefined'){
    	               brend_add(data_n['r_brend']['id_z_p_p'],data_n['r_brend']['val']);
    	            }
    	            if (typeof data_n['r_model']=='object' && typeof data_n['r_model']['val']!='undefined'){
    	               $('input[name="r_model"]').val(data_n['r_model']['val']);
    	               //brend_add(data_n['r_model']['id_z_p_p'],data_n['r_model']['val']);
    	            }
    	            if (typeof data_n['r_neispravnosti']=='object' && typeof data_n['r_neispravnosti']['val']!='undefined'){
    	               if (typeof data_n['r_neispravnosti']['val']=='object'){
        	               for(var i in data_n['r_neispravnosti']['val']){
        	                   r_neispr_add(data_n['r_neispravnosti']['id_z_p_p'][i],data_n['r_neispravnosti']['val'][i]);
        	               }
    	               }else{
    	                   r_neispr_add(data_n['r_neispravnosti']['id_z_p_p'],data_n['r_neispravnosti']['val']);
    	               }
                    }
                    
                $('input[name="c_call_client_id"]').val(id_);
                $('.c_call_client_info_div').html('Оформление заказа по звонку №'+id_).show();
                  
                  
                  
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
        || m_log_type=='Выбор филиала'
        || m_log_type=='Изменение даты'
        || m_log_type=='Изменение комментариев'
        || m_log_type=='Выбор ответственного'
        || m_log_type=='Установка напоминания'
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
    
    $('.m_zakaz_log_text').prepend(txt_log);
}

//мультивыбор
function chk_multiselect(){

    
    var open_=0;
    var close_=0;
    
    $('.m_zakaz_item__status').each(function(){
        var th_=$(this);
        if (th_.closest('.m_zakaz_item').find('input[name="select_item"]:checked').length>0){
            if (th_.find('.fa-times-circle').length>0){
                close_++;
            }
            else{
                open_++;
            }
        }
    });
    if (open_>0 || close_>0){
        if (open_>0){
            $('.top_com__m_zakaz_close').closest('li').show();
            $('.top_com__m_zakaz_marshrut').closest('li').show();
            if (close_==0){
                $('.top_com__m_zakaz_start').closest('li').hide();
            }
        }
        if (close_>0){
            $('.top_com__m_zakaz_start').closest('li').show();
            if (open_==0){
                $('.top_com__m_zakaz_close').closest('li').hide();
                $('.top_com__m_zakaz_marshrut').closest('li').hide();
            }
        }
    }
    else{
        $('.top_com__m_zakaz_close').closest('li').hide();
        $('.top_com__m_zakaz_start').closest('li').hide();
        $('.top_com__m_zakaz_marshrut').closest('li').hide();
    }
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
    chk_barcode();//проверка штрих-кодов
    chk_docs();//проверка документов
    $('.c_call_client_info_div').hide();//скрываем звонок
    $('.pl_price').autoNumeric('init');
    
    var m_zakaz_mess_text_=CKEDITOR.replace('m_zakaz_mess_text',{
    allowedContent:true,
    height: '100px',
    toolbar: [
    { name: 'document', groups: [ 'mode', 'document' ], items: [ 'Source'] },
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript','NumberedList', 'BulletedList' ] },
    { name: 'links', items: [ 'Link', 'Unlink'] }]});
    AjexFileManager.init({returnTo: 'ckeditor', editor: m_zakaz_mess_text_});

    
    $('.tabs_items_work').tabs();//табы
    $('.tabs_items_work > ul > li:visible:first a').trigger('click');
    $('.pl_schet').select2({'width':'100%',minimumResultsForSearch: 20});
    
    
    $('input[name="m_zakaz_data1_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(){
        find();
    }});
    $('input[name="m_zakaz_data2_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(){
        find();
    }});
    
    $('input[name="m_zakaz_data_done1_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(){
        find();
    }});
    $('input[name="m_zakaz_data_done2_find"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(){
        find();
    }});
    $('input[name="m_dostavka_data"]').datetimepicker({lang:'ru',timepicker:false, mask:false,format:'d.m.Y',closeOnDateSelect:true,onClose: function(){
        chk_dostavka();
    }});
  
    $('input[name="m_dostavka_phone"]').integer_().mask("0(000)000-0000",{clearIfNotMatch: true, placeholder: "_(___)___-__-__"});
    $('input[name=m_dostavka_index]').integer_().mask("000000",{clearIfNotMatch: true, placeholder: "______"});
    $('input[name="m_dostavka_summa"]').float_().spinner({min:0, change: function( event, ui ) {
        var th_=$(this);
        if (th_.val()!=''){
            add_log('','Изменение суммы в доставке',th_.val());
        }
    }});
    $('select[name="i_tk_id"]').select2({allowClear: true,'width':'100%',minimumResultsForSearch: 20}).change(function(){
        chk_dostavka();
    }).on("select2:close", function (e) { 
        add_log('','Изменение транспортной компании в доставке',$(this).find('option:selected').text());
    });
    $('select[name="m_dostavka_city_id"]').select2({allowClear: true,'width':'100%',
        ajax: {
            url: "ajax/m_zakaz.php",
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
        
    }).change(function(){
        add_log('','Изменение города в доставке',$(this).find('option:selected').text());
    });
    
    //Запуск поиска
    $('select[name="s_struktura_s_cat_select"]').select2({allowClear: true,'width':'100%',minimumResultsForSearch: 20}).change(function(){
        var th_=$(this).closest('div[id*=tab]').find('.s_cat_items_find');
        th_.autocomplete( "search", th_.val());
    });
    $(document).delegate('.s_cat_items_find','dblclick',function(){
        $(this).autocomplete( "search", $(this).val() );
    });
    
    $(document).delegate('.s_cat_items_nalich','change',function(){
        var th_=$(this).closest('.find_tovar_tbl').find('.s_cat_items_find');
        th_.autocomplete( "search", th_.val() );
    });
    //Информация
    $(document).delegate('.m_zakaz_first_tbl .fa-info-circle','click',function(){
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
    $('select[name="a_admin"]').select2({'width':'100%',minimumResultsForSearch: 20}).change(function(){
        change_worker_tp();
        
        add_log('','Выбор работника',$('select[name="a_admin"]').find('option:selected').text()+' #'+$('select[name="a_admin"]').val());
        
    });
    change_worker_tp();//выбор работника
    chk_filial();//проверка филиала
    
    //Ответственный
    $('select[name="a_admin_otvet"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
        add_log('','Выбор ответственного',$('select[name="a_admin_otvet"]').find('option:selected').text()+' #'+$('select[name="a_admin_otvet"]').val());
    });//.change();
    
    //Дата
    $('.pl_data').datetimepicker({lang:'ru',timepicker:true, mask:true,format:'d.m.Y H:i',closeOnDateSelect:true,dayOfWeekStart:1});
    $('input[name="date"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,dayOfWeekStart:1,step: 30});
    $('input[name="date_info"]').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,dayOfWeekStart:1,step: 60,onClose:function(dp,$input){
        
        var dt_ = new Date($('input[name="date_info"]').val()); 
        var dt_2=dt_.valueOf();
        add_log('','Установка напоминания','на '+date('d.m.Y H:i', dt_2));
    } });
    
    //логирование даты
    $(document).delegate('input[name="date"]','focus',function(){
        date_cur_change=$('input[name="date"]').val();
    });
    $(document).delegate('input[name="date"]','blur',function(){
        if (typeof date_cur_change=='undefined'){
            date_cur_change=$('input[name="date"]').val();
        }
        else{
            if (date_cur_change!=$('input[name="date"]').val()){
                add_log('','Изменение даты',$('input[name="date"]').val());
                date_cur_change=$('input[name="date"]').val();
            }
        }
    });
    
    //Логирование комментариев
    $(document).delegate('textarea[name="comments"]','focus',function(){
        comments_log=$('textarea[name="comments"]').val();
    });
    //Логирование комментариев
    $(document).delegate('textarea[name="comments"]','blur',function(){
        if (comments_log!=$('textarea[name="comments"]').val()){
            add_log('','Изменение комментариев',$('textarea[name="comments"]').val());
            comments_log=$('textarea[name="comments"]').val();
        }
    });
    
    
    //Быстрый ввод напомианий
    $(document).delegate('.m_zakaz_other_info_time span','click',function(){
        var th_=$(this);
        var time_=th_.data('time')*1000;//переводим в милисекунды
        //add_log('','Установка напоминания','с '+$('input[name="date_info"]').val());
        if (time_==0){
            add_log('','Установка напоминания','с '+$('input[name="date_info"]').val()+' на 0');
            $('input[name="date_info"]').val('');
        }else{
            var dt_ = new Date(); 
            var dt_2=dt_.valueOf() + time_;
            add_log('','Установка напоминания','с '+$('input[name="date_info"]').val()+' на '+date('d.m.Y H:i',dt_2));
            $('input[name="date_info"]').val(date('d.m.Y H:i',dt_2));
        }
    });
    
    //покупатель
    $(document).delegate('input[name="i_contr"]','keyup',function(){
        $('input[name="i_contr"]')
        .data('i_contr_id','')
        .data('i_contr_name','')
        .data('i_contr_phone','')
        .data('i_contr_email','')
        .data('i_contr_org_id','')
        .data('i_contr_org_name','')
        .data('i_contr_org_phone','')
        .data('i_contr_org_email','')
        .data('i_contr_org_cnt','')
        .data('active_id','');
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
    $(document).delegate('.m_zakaz_item__i_contr .m_zakaz_item__i_contr_name','click',function(){
        i_contr_form($(this).data('id'),'',function(data_n){
            $('.modal_i_contr_form').arcticmodal('close');
            find();
        });
    });
    
    //Очистка товара
    $(document).delegate('input[name="s_cat_tovar_find"]','keyup',function(){
        s_cat_arr['id']='';
    });
    
    //автозаполнение неисправностей
    $('.r_neispravnosti').autocomplete({
        minLength: 0,
        appendTo: ".m_zakaz_add_form_div",
        source: function(request, response){
             request['_r_tip_id']=$('select[name="r_tip_oborud"]').val();
             request['_t']='r_neispravnosti_autocomplete';
             r_neispr_arr['id']='';
             r_neispr_arr['name']='';
             r_neispr_arr['term']='';
             if (typeof jqxhr1!='undefined'){jqxhr1.abort();}
          
             jqxhr1 = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_zakaz.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	   $('.r_neispravnosti').removeClass('ui-autocomplete-loading');
                    if (is_json(data)==true){
                	       var data_n=JSON.parse(data);
                          
                           response( $.map( data_n.items, function(item) {
                                return {
                                    label: item.text,
                                    value: item.name,
                                    id: item.id,
                                    name: item.name,
                                    term: request['term']
                                }
                            }));
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            $('.ui-autocomplete:visible li:first-child').css({'border-bottom':'1px solid #333','color':'#900','font-weight':'bold'});
                           
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
        },
        select: function( event, ui ) {
            r_neispr_arr['id']=ui.item.id;
            r_neispr_arr['name']=ui.item.name;
            r_neispr_arr['term']=ui.item.term;
            
        },
        close: function( event, ui ) {
            if (typeof r_neispr_arr['id']!='undefined' && r_neispr_arr['id']!=''){
                $('.r_neispravnosti').val('');
                if (r_neispr_arr['id']>0){
                    
                    //добавляем неисправность
                    r_neispr_add(r_neispr_arr['id'],r_neispr_arr['name']);
                }else{
                    if (r_neispr_arr['id']=='-1'){
                        r_neispr_form_new(r_neispr_arr['term']);
                    }
                }
                
            }
        }
    });
    $(document).delegate('.r_neispravnosti','focus',function(){
        $('.r_neispravnosti').autocomplete("search", $(this).val());
    });
    
    //АВТОКОМПЛИТ ТОВАРА
    $('.find_tovar .s_cat_items_find').autocomplete({
        minLength: 0,
        appendTo: ".m_zakaz_add_form_div",
        source: function(request, response){
            s_cat_arr['id']='';
             request['_t']='s_cat_autocomplete';
             term=request['term'];
             request['nalich']=$('.s_cat_items_nalich').prop('checked');
             request['tip']='1';
                if ($('.ui-state-active').find('a[href="#tabs-2"]').size()==1){
                    request['tip']='2';
                }
             request['s_struktuta_id']=$('.find_tovar .s_cat_items_find').closest('div[id*=tab]').find('select[name="s_struktura_s_cat_select"]').val();
             if (typeof jqxhr1!='undefined'){jqxhr1.abort();}
             
             $('.find_tovar .s_cat_items_find').closest('div').find('i').removeClass('fa-search').addClass('ico').addClass('loading_gray');
             jqxhr1 = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_zakaz.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	
            	   $('.find_tovar .s_cat_items_find').closest('div').find('i').addClass('fa-search').removeClass('ico').removeClass('loading_gray');
            	   if (is_json(data)==true){
                	       var data_n=JSON.parse(data);
                           response(data_n);
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                if (typeof data_n[i]!='undefined'){
                                    var img_='';
                                    if (data_n[i].img!='') {
                                        img_='<td colspan="1" rowspan="2"><img src="../i/s_cat/original/'+data_n[i].img+'" /></td>';
                                    }
                                    var kol_=0;
                                    for(var n in data_n[i].k){
                                        kol_=kol_-0+(data_n[i].k[n])-0;
                                    }
                                    
                                    var kol_txt='';
                                    if (count(data_n[i].k)>0){
                                        if (kol_>0){
                                            kol_txt='<span class="tbl_autocomplate_nalich">В наличии ('+kol_+')</span>';
                                        }else{
                                            kol_txt='<span class="tbl_autocomplate_prodan">Продано</span>';
                                        }
                                    }
                                    var txt='<table class="tbl_autocomplate">';
                                    txt+='<tr>';
                                    txt+=img_;
                                                                                                            
                                    var pr_=''; if (data_n[i].pr!=''){pr_='<p class="tbl_autocomplate_prop_val">'+data_n[i].pr+'</p>';}
                                    txt+='<td><h1>'+data_n[i].value+' '+kol_txt+'</h1>'+pr_+'</td></tr><tr><td>';
                                    if (data_n[i].p!=''){
                                        txt+='<p>'+data_n[i].p+' <i class="fa fa-rouble"></i></p>';
                                    }
                                    txt+='</td></tr></table>';
                                 
                                   $(this).html(txt);
                                   //$('.find_tovar .s_cat_items_find').focus();
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
            s_cat_arr['b']=ui.item.b;
            s_cat_arr['k']=ui.item.k;
            s_cat_arr['pr']=ui.item.pr;
        },
        close: function( event, ui ) {
            if (typeof s_cat_arr['id']!='undefined'){
                if (s_cat_arr['id']>0){
                    
                    //Формируем доступные штрих-коды
                    var barcode='';
                    if (typeof s_cat_arr['b']=='object'){
                        for (var m_tovar_id in s_cat_arr['b']){
                            if (barcode!=''){barcode+='@@';}
                            barcode+=m_tovar_id+'##'+s_cat_arr['b'][m_tovar_id]+'##'+s_cat_arr['k'][m_tovar_id]+'##0';
                        }
                    }
                    
                    //логирование
                    add_log('','Добавление товара',s_cat_arr['name']+' #'+s_cat_arr['id']);
                    //добавляем товар
                    s_cat_add(s_cat_arr['id'],s_cat_arr['name'],1,s_cat_arr['price'],s_cat_arr['tip'],'',barcode,s_cat_arr['img'],s_cat_arr['pr']);
                }else{
                    if (s_cat_arr['id']=='-1'){
                        $('.find_tovar .s_cat_items_find').val('');
                        s_cat_add_change_form(term,'',function(res){
                            if (res.tip=='Товар'){$('.tabs1_li').show();}
                            if (res.tip=='Услуга'){$('.tabs2_li').show();}
                            s_cat_add(res.id,res.name,1,res.price,res.tip,'','',res.img,'');
                            $('.modal_s_cat_form').arcticmodal('close');
                        });
                    }
                }
                
            }
        }
    });
    $(document).delegate('.find_tovar .s_cat_items_find','focus',function(){
        $('.find_tovar .s_cat_items_find').autocomplete("search", $(this).val());
    });
    
    
    //Автозаполнение УСЛУГ
    $('.find_usluga .s_cat_items_find').autocomplete({
        minLength: 0,
        appendTo: ".m_zakaz_add_form_div",
        source: function(request, response){
             s_cat_arr['id']='';
             term=request['term'];
             request['_t']='s_cat_autocomplete';
             
             request['tip']='1';
                if ($('.ui-state-active').find('a[href="#tabs-2"]').size()==1){
                    request['tip']='2';
                }
             request['s_struktuta_id']=$('.find_usluga .s_cat_items_find').closest('div[id*=tab]').find('select[name="s_struktura_s_cat_select"]').val();
             if (typeof jqxhr!='undefined'){jqxhr.abort();}
             
             $('.find_usluga .s_cat_items_find').closest('div').find('i').removeClass('fa-search').addClass('ico').addClass('loading_gray');
             jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_zakaz.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	
            	   $('.find_usluga .s_cat_items_find').closest('div').find('i').addClass('fa-search').removeClass('ico').removeClass('loading_gray');
            	   if (is_json(data)==true){
                	       var data_n=JSON.parse(data);
                           response(data_n);
                            $('.ui-autocomplete:visible').css({'z-index':'1000'});
                            $('.ui-autocomplete:visible li').css({'border-bottom':'1px dotted #900'});
                            $('.ui-autocomplete:visible li').each(function(i,elem) {
                                if (typeof data_n[i]!='undefined'){
                                    var img_='';
                                    if (data_n[i].img!='') {
                                        img_='<td colspan="1" rowspan="2"><img src="../i/s_cat/original/'+data_n[i].img+'" /></td>';
                                    }
                                    
                                    var txt='<table class="tbl_autocomplate">';
                                    txt+='<tr>';
                                    txt+=img_
                                    var pr_=''; if (data_n[i].pr!=''){pr_='<p class="tbl_autocomplate_prop_val">'+data_n[i].pr+'</p>';}
                                    txt+='<td><h1>'+data_n[i].value+'</h1>'+pr_+'</td></tr><tr><td>';
                                    if (data_n[i].p!=''){
                                        txt+='<p>'+data_n[i].p+' <i class="fa fa-rouble"></i></p>';
                                    }
                                    txt+='</td></tr></table>';
                                 
                                   $(this).html(txt);
                                   //$('.find_usluga .s_cat_items_find').focus();
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
                    //логирование
                    add_log('','Добавление услуги',s_cat_arr['name']+' #'+s_cat_arr['id']);
                    
                    s_cat_add(s_cat_arr['id'],s_cat_arr['name'],1,s_cat_arr['price'],s_cat_arr['tip'],'','',s_cat_arr['img'],s_cat_arr['pr']);
                }else{
                    if (s_cat_arr['id']=='-1'){
                        $('.find_usluga .s_cat_items_find').val('');
                        s_cat_add_change_form(term,'',function(res){
                            if (res.tip=='Товар'){$('.tabs1_li').show();}
                            if (res.tip=='Услуга'){$('.tabs2_li').show();}
                            s_cat_add(res.id,res.name,1,res.price,res.tip,'','',res.img,'');
                            $('.modal_s_cat_form').arcticmodal('close');
                        });
                    }
                }
                
            }
        }
    });
    $(document).delegate('.find_usluga .s_cat_items_find','focus',function(){
        $('.find_usluga .s_cat_items_find').autocomplete("search", $(this).val());
    });
        
    //Удаление товара
    $(document).delegate('.s_cat_del_tovar','click',function(){
        var tip_tov_usl=$('.m_zakaz_add_items .ui-tabs-active .ui-tabs-anchor').text();
        var tip_t_u='';
            if ((tip_tov_usl.split('Услуги').length - 1)>0){
                tip_t_u='2';
            }else{
                tip_t_u='1';
            }
        var name_=$(this).closest('.ttable_tbody_tr').find('.s_cat_name_td').text();
        var price_=$(this).closest('.ttable_tbody_tr').find('.find_tovar__sum_span').text();
        var id_=$(this).closest('.ttable_tbody_tr').data('id');
        
        if (tip_t_u=='2'){
            add_log('','Удаление услуги',name_+' #'+id_+', цена: '+price_+' руб.');
        }else{
            add_log('','Удаление товара',name_+' #'+id_+', цена: '+price_+' руб.');
        }
        
        
        
        $(this).closest('.ttable_tbody_tr').detach();
        chk_summ();//Проверяем сумму
        chk_num();//Проверяем номера
        chk_docs(); //проверка документов
    });
    
    //Изменение количества товара //, .find_tovar__sum span
    $(document).delegate('.find_tovar__kol_span','click',function(){
        var th_=$(this);
        th_.closest('.ttable_tbody_tr_td').html('<input type="text" class="find_tovar__kol_val" value="'+_IN(th_.text())+'" />');
        $('.find_tovar__kol_val').float_().spinner({min:1}).focus();
    });
    $(document).delegate('.find_tovar__kol_val','blur',function(){
        var th_=$(this);
        var tip_tov_usl=$('.m_zakaz_add_items .ui-tabs-active .ui-tabs-anchor').text();
        var tip_t_u='';
        if ((tip_tov_usl.split('Услуги').length - 1)>0){
            add_log('','Изменение количества услуги',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+th_.val());
        }else{
            add_log('','Изменение количества товара',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+th_.val());
        }
        
        th_.closest('.ttable_tbody_tr_td').html('<span class="for_mobile">Кол.</span><span class="find_tovar__kol_span val">'+th_.val()+'</span>');
        chk_summ();//Проверяем сумму
        chk_barcode();//проверка штрих-кодов
    });
    $(document).delegate('.find_tovar__kol_val','keyup',function(e){
        if (e.which==13){
            $(this).trigger('blur');
        }
    });
    //Изменение цены товара 
    $(document).delegate('.find_tovar__sum_span','click',function(){
        var th_=$(this);
        th_.closest('.ttable_tbody_tr_td').html('<input type="text" class="find_tovar__sum_val" value="'+_IN(th_.text())+'" />');
        $('.find_tovar__sum_val').float_().spinner({min:1}).focus();
    });
    $(document).delegate('.find_tovar__sum_val','blur',function(){
        var th_=$(this);
        var tip_tov_usl=$('.m_zakaz_add_items .ui-tabs-active .ui-tabs-anchor').text();
        var tip_t_u='';
        if ((tip_tov_usl.split('Услуги').length - 1)>0){
            add_log('','Изменение цены услуги',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+number_format(th_.val(),0,'.',''));
        }else{
            add_log('','Изменение цены товара',th_.closest('.ttable_tbody_tr').find('.s_cat_name_td').text()+' #'+th_.closest('.ttable_tbody_tr').data('id')+': '+number_format(th_.val(),0,'.',''));
        }
        th_.closest('.ttable_tbody_tr_td').html('<span class="for_mobile">Цена</span><span class="find_tovar__sum_span val">'+number_format(th_.val(),0,'.','')+'</span>');
        chk_summ();//Проверяем сумму
    });
    $(document).delegate('.find_tovar__sum_val','keyup',function(e){
        if (e.which==13){
            $(this).trigger('blur');
        }
    });
    
    //Выбор типа покупателя
    $(document).delegate('.m_zakaz_i_contr_tip span','click',function(){
        var th_=$(this);
        $('.m_zakaz_i_contr_tip>span').removeClass('active');
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
    
    
    //Очистка покупателя при заполнении
    $(document).delegate('input[name="i_contr"]','keyup',function(){
        i_contr_id='';$('input[name="i_contr"]').data('i_contr_id','');
    });
    //Автозаполнение покупателя
    $(document).delegate('input[name="i_contr"]','dblclick',function(){
        $(this).autocomplete( "search", $(this).val() );
    });
    
    //Автозаполнение покупателя
    $('input[name="i_contr"]').autocomplete({
        minLength: 0,
        appendTo: ".m_zakaz_add_form_div",
        source: function(request, response){
             request['_t']='i_contr_autocomplete';
             term=request['term'];
             if (typeof jqxhr!='undefined'){jqxhr.abort();}
             jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_zakaz.php",
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
    $(document).delegate('.m_zakaz_item__i_contr_org>span','click',function(){
        i_contr_org_form($(this).closest('.m_zakaz_item__i_contr_org').data('id'),'',function(res){
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
           
            //изменился тип
            if (t_!=res.tip){
                //alert(t_+' - '+res.tip);
                th_.closest('.ttable_tbody_tr').detach();
                if (res.tip=='Товар'){$('.tabs1_li').show();}
                if (res.tip=='Услуга'){$('.tabs2_li').show();}
                s_cat_add(res.id,res.name,kol_,sum_,res.tip,comm_,'',res.img,'');
                $('.modal_s_cat_form').arcticmodal('close');
            }
            else{//если тип не изменился - меняем название
                th_.closest('.ttable_tbody_tr').find('.ttable_tbody_tr_td:first').next().html(res.name);
            }
            
            
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
    
    //Сохранениие заказа
    $(document).delegate('.m_zakaz_add_form__save','click',function(){
        m_zakaz_add_form__save(function(){
            //find();
        });
    });
    
    //Комментарии
    $(document).delegate('.m_zakaz_comments_div textarea','focus',function(){
        $(this).css({'height':'120px'});    
    });
    $(document).delegate('.m_zakaz_comments_div textarea','blur',function(){
        $(this).css({'height':'24px'});    
    });
    
    //Добавление платежей
    $(document).delegate('.m_zakaz_add_pl__add_com','click',function(){
        var err_txt='';
        var sum_=str_replace(' ','',$('.pl_price').val())-0;
        if ($('.pl_data').val()==''){err_txt+='<p>Не указана дата</p>';}
        if (sum_==0){err_txt+='<p>Сумма должна быть равна нулю</p>';}
        if (err_txt==''){
            pl_add($('.pl_data').val(),sum_,$('.pl_schet').val());
        }
        else{
            alert_m(err_txt,'','error','none');
        }
        chk_pl();
    });
    //Удаление платежа
    $(document).delegate('.m_zakaz_add_pl__com .fa-remove','click',function(){
        var th_=$(this);
        var data_=th_.closest('.ttable_tbody_tr').find('.pl_tr__sum').text();
        var schet_=th_.closest('.ttable_tbody_tr').find('.pl_schet_name').text();
        var summ_=th_.closest('.ttable_tbody_tr').find('.val:first').text();
        add_log('','Удаление платежа',summ_+' ('+schet_+') '+data_);
        th_.closest('.ttable_tbody_tr').detach();
        chk_pl();
    });
    //РАСПЕЧАТАТЬ ПКО
    $(document).delegate('.m_zakaz_add_pl__com .fa-print','click',function(){
        alert_m('Заказ будет сохранен!');
    });
    
    //****************************************** ПОИСК ********************************************
    //Фильтр по статусам заказов
    $(document).delegate('.m_zakaz_fillter__status_zakaz li','click',function(){
        var th_=$(this);
        //$('.m_zakaz_fillter__status_zakaz li').removeClass('active');
        if (th_.attr('class')=='active'){
            th_.removeClass('active');
        }else{
            th_.addClass('active');
        }
        find();
    });
    //Фильтр по статусам оплаты
    $(document).delegate('.m_zakaz_fillter__status_pay li','click',function(){
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
    //Фильтр по статусам сервиса
    $(document).delegate('.m_zakaz_fillter__service_ul li','click',function(){
        var th_=$(this);
        $('.m_zakaz_fillter__service_ul li').removeClass('active');
        th_.addClass('active');
        find();
    });
    //Фильтр по напоминаниям
    $(document).delegate('.m_zakaz_fillter__time ul > li','click',function(){
        var th_=$(this);
        var cl_=th_.attr('class');
        if ((cl_.split('active').length - 1)>0){
            th_.removeClass('active');
        }else{
            th_.addClass('active');
        }
        find();
    });
    
    //Сортирровка
    $(document).delegate('.m_zakaz_fillter__sort li','click',function(){
        var th_=$(this);
        $('.m_zakaz_fillter__sort li').removeClass('active');
        th_.addClass('active');
        find();
    });
    
    //Загрузка заказа
    $(document).delegate('.m_zakaz_item__id label','click',function(){
        zakaz_load($(this).closest('.m_zakaz_item').data('id'));
    });
    
    
    //Очистка формы
    $(document).delegate('.m_zakaz_add_form__clear','click',function(){
        add_zakaz_form_clear();
    });
    
    //догрузка
    $(document).delegate('.m_zakaz__load_add','click',function(){
        $('.m_zakaz__load_add').detach();
        find($('div.m_zakaz_item').size());
    });
    
    
    //ЗАГРУЗКА ДОКУМЕНТОВ В СООБЩЕНИИ
    var upload_photo = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : 'upload_button',
        url : 'ajax/m_zakaz.php',
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
        		  $('.m_zakaz_mess_files .loading_file').prepend('<img src="i/l_20_w.gif"> Загрузка...');
                },
                FileUploaded: function(up, file, info) {
                    $('.m_zakaz_mess_files .loading_file').html('');
                    
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
                    $('.m_zakaz_mess_files ul').append('<li class="photo_res__mess" data-img="'+img_+'"><a href="../i/m_dialog/temp/'+img_+'" target="_blank">'+t_txt+'</a><div class="m_zakaz_mess_files_text"><input class="m_zakaz_mess_files_text" placeholder="Название файла" value="'+name_+'" /></div><i title="Удалить документ" class="fa fa-remove"></i></li>');
                    
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
    var upload_photo_m_zakaz = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
    	browse_button : 'm_zakaz_add__docs_load_com',
        url : 'ajax/m_zakaz.php',
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
                   up.setOption('multipart_params', {'_t' : 'm_zakaz_upload_docs'});
                }
            },
            init : {
                QueueChanged: function(up) {upload_photo_m_zakaz.start();},
        		BeforeUpload: function(up, file) {
        		  $('.m_zakaz_add__docs_load').append('<span class="loading"><img src="i/l_20_w.gif"> Загрузка...</span>');
                },
                FileUploaded: function(up, file, info) {
                    $('.m_zakaz_add__docs_load .loading').detach();
                    
                    var arr = (info.response).split('@@');
                    
                    var img_=arr[0];
                    var name_=arr[1];
                    
                    var reg_f=/.*?\./;
                    var ext=img_.replace(reg_f, "");
                    
                    var t_txt='';
                    if (ext=='jpg' || ext=='jpeg' || ext=='gif' || ext=='png'){
                        t_txt='<div class="photo_res__item_image" style="background: url(../i/m_zakaz/temp/'+img_+'); background-size: contain; background-position: center center; background-repeat: no-repeat;" /></div>';
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
                    $('.m_zakaz_add__docs_res ul').append('<li class="photo_res__mess" data-img="'+img_+'"><a href="../i/m_zakaz/temp/'+img_+'" target="_blank">'+t_txt+'</a><div class="m_zakaz_add__docs_res_div"><input class="m_zakaz_add__docs_res_text" placeholder="Название файла" value="'+name_+'" /></div><i title="Удалить документ" class="fa fa-remove"></i></li>');
                    $('.m_zakaz_add__docs_res ul').sortable();
                    add_log('','Загрузка документа',name_);
                }
            }
            });
        upload_photo_m_zakaz.init();
        
        upload_photo_m_zakaz.bind('Error', function(up, err) {
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
        $(document).delegate('.m_zakaz_send_mess','click',function(){
            
            var nomer_=$('.m_zakaz_add_head h2 input[name="nomer"]').val()
            if (nomer_==''){
                m_zakaz_add_form__save(function(data_n){
                    var nomer_=data_n.nomer;
                    send_mess(nomer_,function(){
                        find_mess(nomer_);
                    });
                });
            }else{
                send_mess(nomer_,function(){
                    find_mess(nomer_);
                });
            }
            
        });
    
    //Удаление сообщения
    $(document).delegate('.m_zakaz_all_mess .command .fa-remove','click',function(){
        
        mess_del($(this).closest('li').data('id'),function(){
            
        });
        $(this).closest('li').detach();
        var cnt_=($('.m_zakaz_add_items__mess_all').text())-1;
        $('.m_zakaz_add_items__mess_all').text(cnt_);
    });
    
    //проверка доставки
    $(document).delegate('input[name="m_dostavka_adress"], input[name="m_dostavka_tracking_number"], input[name="m_dostavka_summa"]','keyup',function(){
        chk_dostavka();
    });
    
    $(document).delegate('input[name="m_dostavka_tracking_number"]','blur',function(){
        var th_=$(this);
        if (th_.val()!=''){
            add_log('','Изменение номера отправления в доставке',th_.val());
        }
    });
    $(document).delegate('input[name="m_dostavka_adress"]','blur',function(){
        var th_=$(this);
        if (th_.val()!=''){
            add_log('','Изменение адреса в доставке',th_.val());
        }
    });
    $(document).delegate('input[name="m_dostavka_data"]','blur',function(){
        var th_=$(this);
        if (th_.val()!=''){
            add_log('','Изменение даты отправления в доставке',th_.val());
        }
    });
    $(document).delegate('input[name="m_dostavka_fio"]','blur',function(){
        var th_=$(this);
        if (th_.val()!=''){
            add_log('','Изменение ФИО получателя в доставке',th_.val());
        }
    });
    $(document).delegate('input[name="m_dostavka_phone"]','blur',function(){
        var th_=$(this);
        if (th_.val()!=''){
            add_log('','Изменение телефона в доставке',th_.val());
        }
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
    $(document).delegate('.m_zakaz_fillter__find_txt input','keyup',function(e){
        if (e.which==13){
            find();
        }
    });
    $(document).delegate('.m_zakaz_fillter__find_txt .fa-search','click',function(){
       find(); 
    });
    
    //Поиск по филиалу
    $('select[name="i_tp_id_find"]').select2({'width':'100%',minimumResultsForSearch: 20}).change(function(){
        find();
    });
    //Поиск по ответственному
    $('select[name="m_zakaz_otvet_find"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
        find();
    });
    //Поиск по ТК
    $('select[name="m_zakaz_i_tk_find"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
        find();
    });
    
    //Поиск по Рекламе
    $('select[name="m_zakaz_i_reklama_find"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
        find();
    });
    //движение формы добавления заказа
    

    
    var m=getPageSize();
    
    $(window).scroll(function () {
        var m=getPageSize();
        var top_0=($('.left_podmenu_div:visible ul').outerHeight()+$('.header_block').outerHeight()-0);
        
        //$('.m_zakaz_find h1').html($('.left_podmenu_div:visible ul').outerHeight()+' + '+$('.header_block').outerHeight()+' = '+top_0);
        var pos_=$('.m_zakaz_add_form_div').css('position');
       if (pos_=='fixed'){//Для полного экрана (не для мобильной верстки)
        $('.m_zakaz_add_form_div').css({'height':$(window).height()});
		if ($(this).scrollTop() > 0) {
		      var top_=top_0-$(this).scrollTop();
              if (top_<0){top_=0;}
			$('.m_zakaz_add_form_div').css({'top':top_});
            $('.m_zakaz_add_form_div').css({'height':$(window).height()});
		} else {
			$('.m_zakaz_add_form_div').css({'top':top_0+'px'});
            $('.m_zakaz_add_form_div').css({'height':$(window).height()});
		}
        }else{
            $('.m_zakaz_add_form_div').css({'height':'inherit'});
            $('.m_zakaz_add_form_div').css({'top':'0'});
        }
	});
    $(window).scroll();
    $(window).resize(function(){$(window).scroll();})
    
    //Открытие окна подробной информации
    $(document).delegate('.m_zakaz_add__html_code','click',function(){
        var html_=$('.m_zakaz_add__html_code_hide').html();
        var html_txt='<div class="m_zakaz_add__html_code__modal">';
        html_txt+='<p>Описание проекта</p>';
        html_txt+='<div><textarea id="ckeditor1" name="m_zakaz_add__html_code_txt">'+html_+'</textarea></div>';
        html_txt+='<div><center><span class="m_zakaz_add__html_code_save btn_orange">Сохранить</span></center></div>';
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
    $(document).delegate('.m_zakaz_add__html_code_save','click',function(){
        $('.m_zakaz_add__html_code_hide').html(CKEDITOR.instances.ckeditor1.getData());
        $('.m_zakaz_add__html_code__modal').arcticmodal('close');
    });
    
    //РАСПЕЧАТАТЬ ПКО
    $(document).delegate('.m_zakaz_print_pko','click',function(){
        var m_platezi_id=$(this).closest('.ttable_tbody_tr').data('id');
        
        if (typeof m_platezi_id=='undefined' || m_platezi_id==''){
            var i = $('.m_zakaz_print_pko').index($(this));
          
            m_zakaz_add_form__save(function(){
                var m_platezi_id=$('.m_zakaz_print_pko:eq('+i+')').closest('.ttable_tbody_tr').data('id');
                window.location.href = "?inc=i_docs&com=print&file_name=docx_pko&nomer="+m_platezi_id;
            });
        }
        else{
            window.location.href = "?inc=i_docs&com=print&file_name=docx_pko&nomer="+m_platezi_id;
        }
    });
    //РАСПЕЧАТАТЬ РКО
    $(document).delegate('.m_zakaz_print_rko','click',function(){
        var m_platezi_id=$(this).closest('.ttable_tbody_tr').data('id');
        
        if (typeof m_platezi_id=='undefined' || m_platezi_id==''){
            var i = $('.m_zakaz_print_rko').index($(this));
          
            m_zakaz_add_form__save(function(){
                var m_platezi_id=$('.m_zakaz_print_rko:eq('+i+')').closest('.ttable_tbody_tr').data('id');
                window.location.href = "?inc=i_docs&com=print&file_name=docx_rko&nomer="+m_platezi_id;
            });
        }
        else{
            window.location.href = "?inc=i_docs&com=print&file_name=docx_rko&nomer="+m_platezi_id;
        }
    });
    
    //Установить штрих-коды
    $(document).delegate('.s_cat_set_barcode','click',function(){
        var th_=$(this);
        s_cat_set_barcode(th_,function(){
            
        });
    });
    
    
    //выбор штрих кодов
    $(document).delegate('.s_cat_set_barcode__form ul li','click',function(e){
        if (e.target.tagName=='LI'){
            var class_=$(this).attr('class');
            if (class_!='selected'){$(this).addClass('selected').find('input').removeAttr('disabled').focus();}
            else{$(this).removeClass('selected').find('input').val('').blur().attr('disabled','disabled');}
            chk_barcode_form();
        }
        
    });
    $(document).delegate('.s_cat_set_barcode__form ul li input','keyup',function(e){
        if (e.which==13) {
            chk_barcode_form();
        }
    });
    $(document).delegate('.s_cat_set_barcode__form ul li input','blur',function(e){
        chk_barcode_form();
    });
    
    
    //Выбор нескольких заказов
    $(document).delegate('input[name="select_item"]','change',function(){
       chk_multiselect();
    });
    
    //Отменяем несколько заказов
    $(document).delegate('.top_com__m_zakaz_close','click',function(){
        var nomer=sel_in_array($('input[name="select_item"]:checked').closest('.m_zakaz_item'),'data','id');
        
        m_zakaz_close(nomer,function(){
            find();
            add_zakaz_form_clear();
            
        });
    });
    //Открываем несколько заказов
    $(document).delegate('.top_com__m_zakaz_start','click',function(){
        var nomer=sel_in_array($('input[name="select_item"]:checked').closest('.m_zakaz_item'),'data','id');
        
        m_zakaz_open(nomer,function(){
            find();
            add_zakaz_form_clear();
            
        });
    });
    
    //Маршрутный лист
    $(document).delegate('.top_com__m_zakaz_marshrut','click',function(){
        var nomer='';
        $('input[name="select_item"]:checked').closest('.m_zakaz_item').each(function(){
            if (nomer!=''){nomer+=',';}
            nomer+=$(this).data('id');
        });
        window.location.href = "?inc=i_docs&com=print&file_name=marshrut_list&nomer="+nomer;
    });
    
    
    //Отменяем заказ
    $(document).delegate('.m_zakaz_add_form__close','click',function(){
        var nomer=$('input[name="nomer"]').val();
        m_zakaz_close(nomer,function(){
            find();
            zakaz_load(nomer,function(){
               if (typeof callback=='function'){callback(data_n);}
            });
        });
    });
    
    //Открываем заказ
    $(document).delegate('.m_zakaz_add_form__plus','click',function(){
        var nomer=$('input[name="nomer"]').val();
        m_zakaz_open(nomer,function(){
            find();
            zakaz_load(nomer,function(){
               if (typeof callback=='function'){callback(data_n);}
            });
        });
    });
    
    
    
    
    //Работник
    $(document).delegate('.s_cat_set_worker','click',function(){
        var th_=$(this);
        s_cat_set_worker(th_,function(){
            
        });
    });
    
    //выбор типа работника
    $(document).delegate('.s_cat_set_worker_res ul li','click',function(){
        change_worker($(this));
    });
    
    //Сохранение типа работника
    $(document).delegate('.s_cat_set_worker_save','click',function(){
        s_cat_set_worker_save();
    });
    
    
    
    
    // ********************************************************************************************************
    //***************************** СЕРВИС ********************************************************************
    //*********************************************************************************************************
    //статус
    $('select[name="r_status"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
        var r_status=$(this).val();
        $('.m_zakaz_r_service_status_info').html('');
            
            
        if (r_status=='Готов'){
            var data_inform=$('select[name="r_status"]').data('data_inform');
            var txt='<span class="btn_orange service_status_info_send_sms"><i class="fa fa-envelope"></i> Отправить sms</span>';
            if (data_inform!=''){
                txt='<span class="m_zakaz_r_service_status_info_txt" title="'+data_inform+'">Оповещен.</span> <span class="btn_gray service_status_info_send_sms"><i class="fa fa-envelope"></i></span>';
                txt='<span class="check_status_otdan_btn btn_orange">Выдать</span> '+txt;
            }
            
            $('.m_zakaz_r_service_status_info').html(txt);
        }
        if (r_status=='Принят'){
            var txt='<span class="btn_gray m_zakaz_r_service_status_info_diagnoz_add"><i class="fa fa-plus"></i> Диагностика</span>';
            $('.m_zakaz_r_service_status_info').html(txt);
        }
        if (r_status=='Отдан'){
            
            var txt='<span>Дата выдачи:</span> <input type="text" value="'+date('d.m.Y H:i')+'" name="m_zakaz_r_service_status_info_data_vidachi" class="m_zakaz_r_service_status_info_data_vidachi" placeholder="Дата выдачи">';
            $('.m_zakaz_r_service_status_info').html(txt);
            $('.m_zakaz_r_service_status_info_data_vidachi').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:true,onClose: function(){
                
            }});
        }
        if (r_status!=''){
            $('.m_zakaz_r_service_view').removeClass('m_zakaz_r_service_hide');
        }else{
            $('.m_zakaz_r_service_view').addClass('m_zakaz_r_service_hide');
        }
        chk_docs();//проверка документов
    });
    
    //Отдан
    $(document).delegate('.check_status_otdan_btn','click',function(){
        $('select[name="r_status"]').val('Отдан').trigger('change');
        r_service_status_otdan_change();//выдача
    });
    
    //Тип оборудования
    $('select[name="r_tip_oborud"]').select2({'width':'100%',allowClear: true,minimumResultsForSearch: 20}).change(function(){
        
        if ($(this).val()!=''){
            $('select[name="r_brend"]').select2("open");
            //Очищаем модель и бренд
            $('select[name="r_brend"]').val(null).trigger('change');
            $('input[name="r_model"]').val('');
        }
        chk_docs();//проверка документов
    });
    
    //Быстрый выбор в сервисе
    $(document).delegate('.m_zakaz_r_service_quick_span label','click',function(){
        var th_=$(this);
        var id=th_.data('id');
        if (th_.closest('.ttable2_tbody_tr').find('.m_zakaz_r_service_div select').size()>0){
            th_.closest('.ttable2_tbody_tr').find('.m_zakaz_r_service_div select').val(id).trigger('change');
        }
        else{
            th_.closest('.ttable2_tbody_tr').find('input').val(id);
        }
        
        chk_docs();//проверка документов
    });
    
    //Бренд оборудования
    $('select[name="r_brend"]').select2({'width':'100%',allowClear: true,
            ajax: {
                url: "ajax/m_zakaz.php",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, 
                        page: params.page,
                        _t:'autocomplete_r_brend',
                        _r_tip_id:$('select[name="r_tip_oborud"]').val()
                    };
                },
                processResults: function (data, page) {
                    var txt='';
                    var j=0;
                    for (var i in data.items){
                        if (j<3){
                            txt+='<label data-id="'+_IN(data.items[i].id)+'" data-text="'+_IN(data.items[i].text)+'">'+data.items[i].name+'</label>';
                        }
                        j++;
                    }
                    $('select[name="r_brend"]').closest('.ttable2_tbody_tr').find('.m_zakaz_r_service_quick_span_auto').html(txt);
                    if ($( ".tabs_items_work" ).tabs("option","active")==0){
                        return {
                        results: data.items
                        };
                    } 
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; },
            minimumInputLength: 0
            
        }).on("select2:select", function (e) {
            $('input[name="r_model"]').val('').focus().autocomplete( "search", "" );
        }).change(function(e){
            if ($(this).val()!=''){
                var name_='';
                if (typeof e.target[0]!='undefined'){
                    name_=e.target[0].innerText;
                    $(this).find('option:selected').text(name_);
                    $('input[name="r_model"]').val('').focus().autocomplete( "search", "" );
                    }
            }
            chk_docs();//проверка документов
        });
    
    //Быстрый выбор бренда
    $(document).delegate('.m_zakaz_r_service_quick_span_auto label','click',function(){
        
        
        var th_=$(this);
        var id=th_.data('id');
        var name=th_.data('text');
        brend_add(id,name);
        

    });
    //Быстрый выбор модели
    $(document).delegate('.m_zakaz_r_service_quick_span_input label','click',function(){
        var th_=$(this);
        var id=th_.data('id');
        var name=th_.data('text');
        th_.closest('.ttable2_tbody_tr').find('input').val(name);
        chk_docs();//проверка документов
    });
    
    //
    $(document).delegate('input[name="r_model"]','blur',function(){
        chk_docs();//проверка документов
    });
    
    //Модель оборудования
    $('input[name="r_model"]').autocomplete({
            minLength: 1,
            appendTo: ".m_zakaz_add_form_div",
            source: function(request, response){
                if ($( ".tabs_items_work" ).tabs("option","active")==0 && $('input[name="r_model"]:focus').size()>0){//при активной вкладке сервиса
                 request['_t']='autocomplete_r_model';
                 request['_r_tip_id']=$('select[name="r_tip_oborud"]').val();
                 request['_r_brend_id']=$('select[name="r_brend"]').val();
                 if (request['_r_tip_id']!='' && request['_r_brend_id']!=''){
                     if (typeof jqxhr!='undefined'){jqxhr.abort();}
                     jqxhr = $.ajax({
                    	"type": "POST",
                    	"url": "ajax/m_zakaz.php",
                    	"dataType": "text",
                    	"data":request,
                    	"success":function(data,textStatus){
                    	   $('input[name="r_model"]').removeClass('ui-autocomplete-loading');
                    	   if (is_json(data)==true){
                       	        data_n=JSON.parse(data);
                                response(data_n);
                                var txt='';
                                var j=0;
                                for (var i in data_n){
                                    if (j<3){
                                        txt+='<label data-text="'+_IN(data_n[i])+'">'+data_n[i]+'</label>';
                                    }
                                    j++;
                                }
                                $('input[name="r_model"]').closest('.ttable2_tbody_tr').find('.m_zakaz_r_service_quick_span_input').html(txt);
                                 
                            }else{
                                alert_m(data,'','error','none');
                            }
                    	}
                    });
                    }
                }
            },
            close: function( event, ui ) {
                
            }
        });
    
    //комплектация
    $('input[name="komplekt"]').autocomplete({
            minLength: 0,
            appendTo: ".m_zakaz_add_form_div",
            source: function(request, response){
                if ($( ".tabs_items_work" ).tabs("option","active")==0){//при активной вкладке сервиса
                 request['_t']='autocomplete_komplekt';
                 
                 if (typeof jqxhr!='undefined'){jqxhr.abort();}
                 jqxhr = $.ajax({
                	"type": "POST",
                	"url": "ajax/m_zakaz.php",
                	"dataType": "text",
                	"data":request,
                	"success":function(data,textStatus){
                	   
                	   if (is_json(data)==true){
                   	        data_n=JSON.parse(data);
                            response(data_n);
                        }else{
                            alert_m(data,'','error','none');
                        }
                	}
                });
                }
            },
            close: function( event, ui ) {
                
            }
        });
    $(document).delegate('input[name="komplekt"]','focus',function(){
        $('input[name="komplekt"]').autocomplete("search", $(this).val());
    }); 
    $(document).delegate('input[name="komplekt"]','blur',function(){
        chk_docs();//проверка документов
    });
    
    //состояние
    $('input[name="sost"]').autocomplete({
        minLength: 0,
        appendTo: ".m_zakaz_add_form_div",
        source: function(request, response){
            if ($( ".tabs_items_work" ).tabs("option","active")==0){//при активной вкладке сервиса
            
 
             request['_t']='autocomplete_sost';
             
             if (typeof jqxhr!='undefined'){jqxhr.abort();}
             jqxhr = $.ajax({
            	"type": "POST",
            	"url": "ajax/m_zakaz.php",
            	"dataType": "text",
            	"data":request,
            	"success":function(data,textStatus){
            	   
            	   if (is_json(data)==true){
               	        data_n=JSON.parse(data);
                        response(data_n);
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
            }
        },
        close: function( event, ui ) {
            
        }
    });
    $(document).delegate('input[name="sost"]','focus',function(){
        $('input[name="sost"]').autocomplete("search", $(this).val());
    });
    $(document).delegate('input[name="sost"]','blur',function(){
        chk_docs();//проверка документов
    });
    
    
    //Оповещение sms
    $(document).delegate('.service_status_info_send_sms','click',function(){
        var nomer=$('.m_zakaz_add_head input[name="nomer"]').val();
        if (typeof nomer=='undefined'){nomer='';}

        m_zakaz_add_form__save(function(data_n){
            if (nomer==''){nomer=data_n.nomer;}
            service_status_info_send_sms(function(){
                zakaz_load(nomer,function(){
                    //find();
                });
            });
        });
        
    });
    
    //Диагностика
    $(document).delegate('.m_zakaz_r_service_status_info_diagnoz_add','click',function(){
        var err_text='';
        var data_=new Object();
        data_['_t']='s_cat_autocomplete';
        data_['term']='Диагностика';
        data_['tip']='2';
        
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
        	            data_n=JSON.parse(data);
                        
                        if (typeof data_n[1]!='undefined' && data_n[1].id!='undefined'){
                            s_cat_arr['id']=data_n[1].id;
                            s_cat_arr['name']=data_n[1].value;
                            s_cat_arr['price']=data_n[1].p;
                            s_cat_arr['tip']=data_n[1].t;
                            s_cat_arr['img']=data_n[1].img;
                            s_cat_arr['b']=data_n[1].b;
                            s_cat_arr['k']=data_n[1].k;
                            s_cat_arr['pr']=data_n[1].pr;
                                        
            	            s_cat_add(s_cat_arr['id'],s_cat_arr['name'],1,s_cat_arr['price'],s_cat_arr['tip'],'','',s_cat_arr['img'],s_cat_arr['pr']);
                        }
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    //Диагностическое заключение
    $(document).delegate('.r_service_diagnoz_send','click',function(){
        if ($('.r_service_diagnoz').val()==''){
            alert_m('Заполните диагноз до отправки sms!','','error','none');
        }
        else{
            var nomer=$('.m_zakaz_add_head input[name="nomer"]').val();
            if (typeof nomer=='undefined'){nomer='';}
            
             m_zakaz_add_form__save(function(data_n){
                if (nomer==''){nomer=data_n.nomer;}
                service_diagnoz_send_sms(function(){
                    find_mess(nomer,'',function(){
                        if (typeof callback=='function'){callback(data_n);}
                    });
                });
            });
        }
    });
    
    //Догрузка сообщений в заказе
    $(document).delegate('.load_new_mess_in_zakaz','click',function(){
        $('.load_new_mess_in_zakaz').detach();
        find_mess($('input[name="nomer"]').val(),$('.m_zakaz_all_mess>ul>li').size());
    });
    
    //Отобразить весь товар в заказе
    $(document).delegate('.m_zakaz_item__view_all_tr','click',function(){
        var th_=$(this);
        th_.closest('.ttable').find('.ttable_tbody_tr').css({'display':'table-row'});
        th_.detach();
    });
    
    //Сообщения по заказу - быстрый вывод
    $(document).delegate('.m_zakaz_item__status .fa-comments-o','click',function(){
        var id=$(this).closest('.m_zakaz_item').data('id');
        alert_m('<div class="m_zakaz_item__window"><h1>Сообщения к заказу №'+id+'</h1><p class="m_zakaz_add_items__mess_all__window"></p><ul></ul></div>','','info','none','',function(){
            find_mess(id,'','',1);
        });
        
    });
        
        
    //Отправить сообщение на телефон клиенту
    $(document).delegate('.send_mess_comm_sms','click',function(){
        var id_mess=$(this).closest('li[data-id!=""]').data('id');
        send_mess_comm_send(id_mess,'sms',function(){
            find_mess($('input[name="nomer"]').val());
        });
    }); 
    //Отправить сообщение на email клиенту
    $(document).delegate('.send_mess_comm_email','click',function(){
        var id_mess=$(this).closest('li[data-id!=""]').data('id');
        send_mess_comm_send(id_mess,'email',function(){
            find_mess($('input[name="nomer"]').val());
        });
    });
     
     //удаляем неисправность   
    $(document).delegate('.m_zakaz_r_neispr_remove','click',function(){
        $(this).closest('div').detach();
    }); 
        
    //сохранение новой неисправности
    $(document).delegate('.r_neispr_form_new_save','click',function(){
        r_neispr_form_new_save();
    });  
        
     $(document).delegate('.r_neispr_form_new_name','keyup',function(e){
        if (e.which==13){
            $('.r_neispr_form_new_save').trigger('click');
        }
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
     
     //Проверка доставки
     $(document).delegate('input[name="m_dostavka_tracking_number"], input[name="m_dostavka_data"]','keyup',function(){
        chk_dostavka();
     });
     
     chk_dostavka();
     
     
     //Оповещение клиента
     $(document).delegate('.m_zakaz__dostavka_send_mess>span','click',function(){
        var tx=$('input[name="m_dostavka_tracking_number"]').val();
         m_zakaz_add_form__save(function(data_n){
            
            CKEDITOR.instances.m_zakaz_mess_text.setData('Заказ отправлен. Номер отправления: '+tx);
       
            //отправка сообщения
             var nomer_=$('.m_zakaz_add_head h2 input[name="nomer"]').val()
            if (nomer_==''){
                m_zakaz_add_form__save(function(data_n){
                    var nomer_=data_n.nomer;
                    send_mess(nomer_,function(dt){
                        var id_mess=dt.nomer;
                        //отправка сообщений
                        send_mess_comm_send(id_mess,'email',function(){
                            send_mess_comm_send(id_mess,'sms',function(){
                                find_mess(nomer_);
                            });
                        });
                        
                    });
                });
            }else{
                send_mess(nomer_,function(dt){
                    var id_mess=dt.nomer;
                        //отправка сообщений
                        send_mess_comm_send(id_mess,'email',function(){
                            send_mess_comm_send(id_mess,'sms',function(){
                                find_mess(nomer_);
                            });
                        });
                });
            }
        });
        
     });
     
     //Открываем добавление заказа
     $(document).delegate('.m_zakaz_add_form_open_close_div','click',function(){
        var th_=$(this).closest('.m_zakaz_add_form');
        var cl_=th_.attr('class');
        if ((cl_.split('m_zakaz_add_form_open').length - 1)>0){
            $(this).addClass('m_zakaz_add_form_open_div').removeClass('m_zakaz_add_form_close_div').text('Открыть форму добавления заказа');
            th_.addClass('m_zakaz_add_form_close').removeClass('m_zakaz_add_form_open');
        }else{
            $(this).addClass('m_zakaz_add_form_close_div').removeClass('m_zakaz_add_form_open_div').text('Закрыть форму добавления заказа');
            th_.addClass('m_zakaz_add_form_open').removeClass('m_zakaz_add_form_close');
        }
     });
     
     //Копирование из заказа
     $(document).delegate('.find_tovar_res_all_copy_m_zakaz','click',function(){
        var txt='<div class="find_tovar_res_all_copy_m_zakaz_div"><p>Укажите заказ, из которого копировать товар?</p>';
        txt+='<div><select name="m_zakaz_id_copy" data-placeholer="№ заказа"></select></div>';
        txt+='<div><span class="find_tovar_res_all_copy_m_zakaz_com btn_orange">Копировать</span></div>';
        txt+='</div>';
        alert_m(txt,'','info','none');
        
        //К заказу
        $('select[name="m_zakaz_id_copy"]').select2({'width':'100%',allowClear: true,closeOnSelect:true,
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
        
     });
     //получение и перенос товара
     $(document).delegate('.find_tovar_res_all_copy_m_zakaz_com','click',function(){
        var err_text='';
        var data_=new Object();
        data_['_t']='find_tovar_res_all_copy_m_zakaz_com';
        data_['id']=$('select[name="m_zakaz_id_copy"]').val();
        
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
        	            data_n=JSON.parse(data);
                        if (typeof data_n=='object'){
            	            for(var i in data_n){
            	               s_cat_add(data_n[i].id,data_n[i].value,data_n[i].kk,data_n[i].pp,data_n[i].t,data_n[i].cc,data_n[i].b,data_n[i].img,data_n[i].pr);
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
     
     //Клонирование доставки 
    $(document).delegate('.m_dostavka_phone_clone','click',function(){
        $('.m_zakaz_m_dostavka_phone').val($('.i_contr_add_form_phone a').text());
        
    });
     //Клонирование доставки 
    $(document).delegate('.m_dostavka_fio_clone','click',function(){
        $('.m_zakaz_m_dostavka_fio').val($('input[name="i_contr"]').val());
    });
    
    //Клонирование заказа
    $(document).delegate('.m_zakaz_add_form__clone','click',function(){
            
        $('.m_zakaz_add_head h2').html('Добавление заказа <input type="hidden" value="" name="nomer">');
        $('.s_cat_cur_barcode').html('');
        
        chk_i_contr();
        chk_summ();//Проверяем сумму
        chk_comments(); //проверяем комментарии
        chk_pl();//проверка платежей
        chk_docs();//проверка документов
        chk_barcode();//проверка штрих-кодов
        //Формирование ссылки HISTORY
        history_url='?inc=m_zakaz';
        history_name='Заказы покупателей';
        History.replaceState({state:3}, history_name, history_url);
    });
    
    //Выделить все заказы
    $(document).delegate('.m_zakaz__all_find_select_chk_all','click',function(){
        //alert($(this).prop('checked'));
        if ($(this).prop('checked')==true){
            $('.m_zakaz_find_res input[name="select_item"]').prop('checked',true);
        }else{
            $('.m_zakaz_find_res input[name="select_item"]').prop('checked',false);
        }
        chk_multiselect();
    });
    
    
    //Добавление товара в заказ из поступления
    $(document).delegate('.s_cat_add_in_postav_li','click',function(){
        
        var th_=$(this);
        s_cat_add_from_id(th_.data('s_cat_id'));
        
        $(this).detach();
        s_cat_in_postav_chk();
    });

    
    //Выбор доставки
    $(document).delegate('.m_dostavka_chk_active','change',function(){
      
        chk_dostavka();
    });
    
    //Горящие заказы
    $(document).delegate('input[name="m_zakaz_fire"]','change',function(){
        find();
    });
    $('input[name="m_zakaz_fire_h"]').integer_();
    $(document).delegate('input[name="m_zakaz_fire_h"]','blur',function(){
        find();
    });
    
    
    <?php
    
    if (_GP('nomer')!=''){
        ?>
        zakaz_load('<?=_GP('nomer');?>');
        
        
        <?php
    }
    
    if (_GP('com')=='add_zakaz_in_c_call_client' and _GP('id')!=''){
        ?>
        $('input[name="c_call_client_id"]').val('<?=_GP('id');?>');
        zakaz_load_from_call('<?=_GP('id');?>');
        <?php
    }
    
    //Добавление товара или услуги в заказ
    if (_GP('add_id')!=''){
        ?>
        s_cat_add_from_id('<?=_GP('add_id');?>');
        <?php
    }
    ?>
    
    $(document).delegate('.m_zakaz_item__mess_div > i','click',function(){
        if ($(this).closest('.m_zakaz_item__mess_div').find('.m_zakaz_item__mess').css('display')=='block'){
            $(this).closest('.m_zakaz_item__mess_div').find('.m_zakaz_item__mess').css({'display':'none'});  
        }else{
            $(this).closest('.m_zakaz_item__mess_div').find('.m_zakaz_item__mess').css({'display':'block'});
        }
        
    });
    
});
</script>