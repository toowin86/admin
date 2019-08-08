<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script src="js/Highcharts/highcharts.js"></script>
<script src="js/Highcharts/modules/exporting.js"></script>
<script src="js/Highcharts/modules/series-label.js"></script>
<link type="text/css" href="js/Highcharts/css/highcharts.css" rel="stylesheet" />

<script type="text/javascript">
var jqxhr;

//Изменение заказа
function change_zakaz(tip_,id_,block_id,val_){
    tip_=tip_ || '';
    id_=id_ || '';
    val_=val_ || '';
    if (id_!='' && (tip_=='otvet' || tip_=='cancel' || tip_=='data_end')){
        var err_text='';
        var data_=new Object();
        data_['_t']='change_zakaz';
        data_['tip_']=tip_;
        data_['val_']=val_;
        data_['id_']=id_;
      
        	
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/start_menu.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        var th_=$('#'+block_id);
                        var param=new Object();
                        var tip_=th_.data('tip');
                        if (typeof tip_!='undefined'){param['tip']=tip_;}
                        var period_=th_.data('period');
                        if (typeof period_!='undefined'){param['period']=period_;}
                        
        	            start_menu_load(block_id,param,function(txt){
                            th_.html(txt);
                            
                        });
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
     
    }
    else{
       alert_m('Не определена переменная id_='+id_+' или не верный тип tip_='+tip_,'','error','none');
    }
    
    
}

//Функция построения данных каталога
function s_cat_add(res){
    var txt=''
    
    if (typeof res=='object'){
        for (var i in res){
            txt+=''
            +'<div class="start_menu_s_cat_last_add_item">'
            +'<div class="start_menu_s_cat_last_add_item_img">';
            if (res[i].img!=''){
                txt+='<a class="zoom" href="../i/s_cat/original/'+res[i].img+'"><div style="background: #fff url(\'../i/s_cat/small/'+res[i].img+'\') no-repeat top center; background-size: cover;" ></div></a>';
            }
            txt+='</div>'
            +'<a href="?inc=s_cat&com=_change&nomer='+res[i].id+'">'
            +'<h3>'+res[i].name+'</h3>'
            +'<p>'+res[i].price+' руб.</p>';
            if (typeof res[i].hours_!='undefined'){
                txt+='<p>Создан '+res[i].hours_+' '+end_word(res[i].hours_,'часов','час','часа')+' назад.</p>';
            }
            if (typeof res[i].cnt!='undefined'){
                txt+='<p>Просмотров: '+res[i].cnt+'</p>';
            }
            txt+='</a></div>';
        }
    }
    return txt;
}

//средний чек
function m_zakaz_middle_check(res){
    var check_=new Array();
    for(var i in res.check){
        check_[i]=res.check[i]-0;
    }
    
    
    $('#m_zakaz_middle_check').css({'height':'320px'});
    Highcharts.chart('m_zakaz_middle_check', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Средний чек'
    },
    xAxis: {
        categories: res.x_all
    },
    yAxis: {
        title: {
            text: 'Средний чек, рублей.'
        },
        labels: {
            formatter: function () {
                return number_format(this.value,0,'.',' ') + ' руб.';
            }
        }
    },
    plotOptions: {
        spline: {
            marker: {
                radius: 4,
                lineColor: '#dddddd',
                lineWidth: 1
            }
        }
    },
    series: [{
        name: 'Средний чек',
        marker: {
            symbol: 'square'
        },
        data: check_
    }]
});
}

//Всего заказов
function m_zakaz_cnt(res){
    var all_=new Array();
    for(var i in res.all){
        all_[i]=res.all[i]-0;
    }
    
    var good_=new Array();
    for(var i in res.good){
        good_[i]=res.good[i]-0;
    }
    var cancel_=new Array();
    for(var i in res.cancel){
        cancel_[i]=res.cancel[i]-0;
    }
    var in_work_=new Array();
    for(var i in res.in_work){
        in_work_[i]=res.in_work[i]-0;
    }
    $('#m_zakaz_cnt').css({'height':'320px'});
    Highcharts.chart('m_zakaz_cnt', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Заказы'
    },
    xAxis: {
        categories: res.x_all
    },
    yAxis: {
        title: {
            text: 'Количество заказов'
        },
        labels: {
            formatter: function () {
                return number_format(this.value,0,'.',' ') + ' шт.';
            }
        }
    },
    plotOptions: {
        spline: {
            marker: {
                radius: 4,
                lineColor: '#dddddd',
                lineWidth: 1
            }
        }
    },
    series: [{
        name: 'Всего заказов',
        marker: {
            symbol: 'square'
        },
        data: all_
    },{
        name: 'Выполненные',
        marker: {
            symbol: 'square'
        },
        data: good_
    },{
        name: 'Отмененные',
        marker: {
            symbol: 'square'
        },
        data: cancel_
    },{
        name: 'В обработке',
        marker: {
            symbol: 'square'
        },
        data: in_work_
    }]
});
}


//вывод заказов
function m_zakaz_work(res){
    var txt=''
    if (typeof res=='object'){
        for (var i in res){
            
            var cl_='';
            if (res[i].status=='В обработке'){cl_='m_zakaz_work_item_start';}
            if (res[i].status=='Частично выполнен'){cl_='m_zakaz_work_item_in_work';}
            if (res[i].data_end!=''){cl_+=' m_zakaz_work_item_deadline';}
            
            txt+=''
            +'<div class="start_menu_m_zakaz_work_item '+cl_+'" data-id="'+res[i].id+'">'
            +'<div class="start_menu_m_zakaz_work_item_name">'
            +'<a href="?inc=m_zakaz&nomer='+res[i].id+'"><h3>'+res[i].project_name+'</h3></a>';
            txt+='</div>'
            
            +'<div class="start_menu_m_zakaz_work_item_info"><div class="start_menu_m_zakaz_work_item_status"><strong>'+res[i].status+'</strong></div>'
            +'<p>Итого: <strong>'+number_format(res[i].sum,0,'.',' ')+'</strong> руб.</p>'
            +'<p>Оплачено: <strong>'+number_format(res[i].pay,0,'.',' ')+'</strong> руб.</p>';
            if (res[i].data_!=''){
                txt+='<p class="start_menu_m_zakaz_work_item_data">Создан: '+res[i].data_+'</p>';
            }
            txt+='<div class="start_menu_m_zakaz_work_item_data_end">';
            if (res[i].data_end!=''){
                txt+='<div>Дедлайн:</div><div><span class="m_zakaz_change_deadline">'+res[i].data_end+'</span></div>';
            }else{
                txt+='<span class="btn_gray start_menu_m_zakaz_work_item_otvet">Назначить себя</span>';
            }
            txt+='</div>';
            txt+='<div class="thumbnail start_menu_m_zakaz_work_item_tbl">Итого <strong>'+count(res[i].items)+'</strong> '+end_word(count(res[i].items),'позиций','позиция','позиции')+'<span>';
            txt+='<div class="ttable">';
            for(var j in res[i].items){
                txt+='<div class="ttable_tbody_tr"><div class="ttable_tbody_tr_td"><a href="../'+res[i].items[j].id+'">'+res[i].items[j].id+'</a>. <a href="?inc=s_cat&nomer='+res[i].items[j].id+'">'+res[i].items[j].name+'</a></div><div class="ttable_tbody_tr_td">'+res[i].items[j].price+'р.</div><div class="ttable_tbody_tr_td">'+res[i].items[j].kolvo+'шт.</div></div>';
            }
            txt+='</div></span>';
            txt+='</div>';
            txt+='<div class="start_menu_m_zakaz_work_item_com"><span class="btn_red start_menu_m_zakaz_work_item_cancel">Отменить</span></div>';
            txt+='</div>';
            txt+='</div>';
        }
    }
    return txt;
}

//График доходов за период
function m_zakaz_dohod(res){
    var items_=new Array();
    for(var i in res.items){
        items_[i]=res.items[i]-0;
    }
    var works_=new Array();
    for(var i in res.works){
        works_[i]=res.works[i]-0;
    }
    var sum_=new Array();
    for(var i in res.sum){
        sum_[i]=res.sum[i]-0;
    }
    $('#m_zakaz_dohod').css({'height':'320px'});
    Highcharts.chart('m_zakaz_dohod', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Доход по товарам и работам'
    },
    xAxis: {
        categories: res.x_all
    },
    yAxis: {
        title: {
            text: 'Доход, рублей.'
        },
        labels: {
            formatter: function () {
                return number_format(this.value,0,'.',' ') + ' руб.';
            }
        }
    },
    plotOptions: {
        spline: {
            marker: {
                radius: 4,
                lineColor: '#dddddd',
                lineWidth: 1
            }
        }
    },
    
    series: [{
        name: 'Товары',
        marker: {
            symbol: 'square'
        },
        data: items_

    }, {
        name: 'Услуги',
        marker: {
            symbol: 'diamond'
        },
        data: works_
    }, {
        name: 'Итого',
        marker: {
            symbol: 'diamond'
        },
        data: sum_
    }]
});
}

//Платежи
function m_platezi_in_out(res){
    var vozvrat_=new Array();
    for(var i in res.vozvrat){
        vozvrat_[i]=res.vozvrat[i]-0;
    }
    var m_postav_=new Array();
    for(var i in res.m_postav){
        m_postav_[i]=res.m_postav[i]-0;
    }
    var i_rashodi_=new Array();
    for(var i in res.i_rashodi){
        i_rashodi_[i]=res.i_rashodi[i]-0;
    }
    var a_admin_=new Array();
    for(var i in res.a_admin){
        a_admin_[i]=res.a_admin[i]-0;
    }
    var i_inout_=new Array();
    for(var i in res.i_inout){
        i_inout_[i]=res.i_inout[i]-0;
    }
    var i_reklama_=new Array();
    for(var i in res.i_reklama){
        i_reklama_[i]=res.i_reklama[i]-0;
    }
    $('#m_platezi_in_out').css({'height':'320px'});
    Highcharts.chart('m_platezi_in_out', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Структура расходов'
    },
    xAxis: {
        categories: res.x_all
    },
    yAxis: {
        title: {
            text: 'Сумма, рублей.'
        },
        labels: {
            formatter: function () {
                return number_format(this.value,0,'.',' ') + ' руб.';
            }
        }
    },
    plotOptions: {
        spline: {
            marker: {
                radius: 4,
                lineColor: '#dddddd',
                lineWidth: 1
            }
        }
    },
    
    series: [{
        name: 'Возвраты',
        marker: {
            symbol: 'square'
        },
        data: vozvrat_

    }, {
        name: 'Оплата поставщикам',
        marker: {
            symbol: 'diamond'
        },
        data: m_postav_
    }, {
        name: 'Расходы',
        marker: {
            symbol: 'diamond'
        },
        data: i_rashodi_
    }, {
        name: 'Зарплата',
        marker: {
            symbol: 'diamond'
        },
        data: a_admin_
    }, {
        name: 'Дивиденты',
        marker: {
            symbol: 'diamond'
        },
        data: i_inout_
    }, {
        name: 'Реклама',
        marker: {
            symbol: 'i_reklama'
        },
        data: i_reklama_
    }]
});
}


//структура клиентов
function i_contr_struktura(res){
    var i_contr_new_=new Array();
    for(var i in res.i_contr_new){
        i_contr_new_[i]=res.i_contr_new[i]-0;
    }
    var i_contr_old_=new Array();
    for(var i in res.i_contr_old){
        i_contr_old_[i]=res.i_contr_old[i]-0;
    }
    $('#i_contr_struktura').css({'height':'320px'});
    Highcharts.chart('i_contr_struktura', {
    chart: {
        type: 'line'
    },
    title: {
        text: '<p>Всего клиентов: <strong>'+res.i_contr_all+'</strong>, добавлено за последний год: <strong>'+res.i_contr_all_year+'</strong></p>'
    },
    xAxis: {
        categories: res.x_all
    },
    yAxis: {
        title: {
            text: 'Количество'
        },
        labels: {
            formatter: function () {
                return number_format(this.value,0,'.',' ') + ' шт.';
            }
        }
    },
    plotOptions: {
        spline: {
            marker: {
                radius: 4,
                lineColor: '#dddddd',
                lineWidth: 1
            }
        }
    },
    
    series: [{
        name: 'Постоянные клиенты',
        marker: {
            symbol: 'square'
        },
        data: i_contr_old_

    }, {
        name: 'Новые клиенты',
        marker: {
            symbol: 'diamond'
        },
        data: i_contr_new_
    }]
});

}

//Структура платежей
function m_platezi_rashod(res){
    var val_=new Array();
    for(var i in res.val){
        
        val_[i]={ 'name':res.name[i], 'y':res.val[i]-0 };
    }
    $('#m_platezi_rashod').css({'height':'320px'});
    
    // Radialize the colors
    Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
    return {
        radialGradient: {
            cx: 0.5,
            cy: 0.3,
            r: 0.7
        },
        stops: [
            [0, color],
            [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
        ]
    };
    });
    
    // Build the chart
    Highcharts.chart('m_platezi_rashod', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Структура расходов'
        },
        tooltip: {
            pointFormat: '<b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    },
                    connectorColor: 'silver'
                }
            }
        },
        series: [{
            name: 'Расходы',
            data: val_//
        }]
    });
}
//топ клиентов
function i_contr_top(res){
    var val_=new Array();
    var j=0;
    for(var i in res.name){
        val_[j]=['<a target="_blank" href="?inc=i_contr&nomer='+res.id[i]+'">'+res.name[i]+'</a>', res.val[i]*1 ];//{ 'name':res.name[i], 'y':res.val[i]*1 };
        j++;
    }
    $('#i_contr_top').css({'height':'320px'});
    Highcharts.chart('i_contr_top', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'ТОП клиентов'
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: -45,
            style: {
                fontSize: '10px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Сумма (руб)'
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        pointFormat: 'Сумма: <b>{point.y:.1f} руб.</b>'
    },
    series: [{
        name: 'Клиенты',
        data: val_,
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#FFFFFF',
            align: 'right',
            format: '{point.y:.1f}', // one decimal
            y: 10, // 10 pixels down from the top
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    }]
});
}
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
//Подгрузка статистики
function start_menu_load(id_,param,callback){
    id_=id_ || '';
    param=param || new Object();
    callback=callback || '';
    
    if (id_!=''){
        var err_text='';
        var data_=new Object();
        data_['_t']='start_menu_load';
        data_['param']=param;
        data_['id_']=id_;
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	
        	jqxhr = $.ajax({
        		"type": "POST",
        		"url": "ajax/start_menu.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        		  
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
        	            if (typeof data_n.res!='undefined'){
        	               var txt='';
                           
                           if (id_=='s_cat_add'){txt=s_cat_add(data_n.res);}//каталог
                           if (id_=='m_zakaz_work'){txt=m_zakaz_work(data_n.res);}//заказы
                           if (id_=='m_zakaz_dohod'){txt=m_zakaz_dohod(data_n.res);}//доход
                           if (id_=='m_zakaz_cnt'){txt=m_zakaz_cnt(data_n.res);}//всего заказов
                           if (id_=='m_zakaz_middle_check'){txt=m_zakaz_middle_check(data_n.res);}//средний чек
                           if (id_=='m_platezi_in_out'){txt=m_platezi_in_out(data_n.res);}//платежи
                           if (id_=='m_platezi_rashod'){txt=m_platezi_rashod(data_n.res);}//структура платежей
                           if (id_=='i_contr_struktura'){txt=i_contr_struktura(data_n.res);}//структура клиентов
                           if (id_=='i_contr_top'){txt=i_contr_top(data_n.res);}//топ клиентов
                           
        	               if (typeof callback=='function'){return callback(txt);}
        	            }
                        else{
                            alert_m('Не обнаружена переменная data_n.res','','error','none');
                        }
                        
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    }else{
        alert_m('Не определена переменная id_='+id_,'','error','none');
    }
    
    
}

//******************************************************************************************************************
//******************************************************************************************************************
//******************************************************************************************************************
$(document).ready(function(){
        
    //Подгрузка модулей
    $('.start_menu_load').each(function(){
        
        var th_=$(this);
        var id_=th_.attr('id');
        var param=new Object();
        
        var tip_=th_.data('tip');
        if (typeof tip_!='undefined'){param['tip']=tip_;}
        var period_=th_.data('period');
        if (typeof period_!='undefined'){param['period']=period_;}
        
        th_.html('<span class="ico ico_loading"></span> Загрузка...');
        start_menu_load(id_,param,function(txt){
            th_.html(txt);
            
        });
       
        
        
    });
    
    //Назначить ответственного на заказ
    $(document).delegate('.start_menu_m_zakaz_work_item_otvet','click',function(){
        var id_=$(this).closest('.start_menu_m_zakaz_work_item').data('id');
        var tip_='otvet';
        var block_id=$(this).closest('.start_menu_load').attr('id');

        change_zakaz(tip_,id_,block_id);
    })
    
    //Отменить заказ
    $(document).delegate('.start_menu_m_zakaz_work_item_cancel','click',function(){
        var id_=$(this).closest('.start_menu_m_zakaz_work_item').data('id');
        var block_id=$(this).closest('.start_menu_load').attr('id');
        var tip_='cancel';
        change_zakaz(tip_,id_,block_id);
    })
    
    
    //Изменить дедлайн
    $(document).delegate('.m_zakaz_change_deadline','click',function(){
         var dt=$(this).text();
        $(this).html('<input type="date" class="m_zakaz_change_deadline_input" value="'+dt+'" />');
        $(this).removeClass('m_zakaz_change_deadline');
        $('.m_zakaz_change_deadline_input').datetimepicker({lang:'ru',timepicker:true, mask:false,format:'d.m.Y H:i',closeOnDateSelect:false,onClose: function(){
            $('.m_zakaz_change_deadline_input').blur();
        }}).focus();
        
    });
    $(document).delegate('.m_zakaz_change_deadline_input','blur',function(){
        var id_=$(this).closest('.start_menu_m_zakaz_work_item').data('id');
        var block_id=$(this).closest('.start_menu_load').attr('id');
        var dt_new=$(this).val();
        $(this).closest('span').addClass('m_zakaz_change_deadline').html(dt_new);
        var tip_='data_end';
        change_zakaz(tip_,id_,block_id,dt_new);
    });
    
    //Обновлении
    $(document).delegate('.start_menu_first_div .fa-refresh','click',function(){
        var th_=$(this).closest('div').find('.start_menu_load');
        
        var id_=th_.attr('id');
        var param=new Object();
        
        var tip_=th_.data('tip');
        if (typeof tip_!='undefined'){param['tip']=tip_;}
        var period_=th_.data('period');
        if (typeof period_!='undefined'){param['period']=period_;}
        
        th_.html('<span class="ico ico_loading"></span> Загрузка...');
        start_menu_load(id_,param,function(txt){
            th_.html(txt);
            
        });
    });
    
    
    //Выгрузка контактов на Google аккаунт
    function contacts_to_google(){
       
        var data_=new Object();
        var err_text='';
        data_['_t']='contacts_to_google';
        data_['cnt']=$('.contacts_to_google').data('cnt');
        data_['cur']=$('.contacts_to_google').data('cur');
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$('.contacts_to_google').append('<span class="ico ico_loading"></span>');
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/start_menu.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        			$('.contacts_to_google .ico_loading').detach();
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
                        $('.contacts_to_google').data('cur',data_n.cur);
                        $('.contacts_to_google').data('ins',data_n.cnt_ins);
                        $('.contacts_to_google').data('upp',data_n.cnt_upp);
                        if (data_n.cur-0<$('.contacts_to_google').data('cnt')-0){
                            contacts_to_google();
                        }
                        else{
                            $('.contacts_to_google').data('cur',0);
                            $('.contacts_to_google').data('ins',0);
                            $('.contacts_to_google').data('upp',0);
                            var txt='<p>Количество добавленных контактов: <strong>'+$('.contacts_to_google').data('ins')+'</strong></p><p>Количество обновленных контактов: <strong>'+$('.contacts_to_google').data('upp')+'</strong></p>';
                            $('.google_block').prepend(txt);
                            alert_m('<h2>Выгрузка завершена!</h2>'+txt,'','ok');
                        }
        	            
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    }
    
    
    //Выгрузка контактов на google
    $(document).delegate('.contacts_to_google','click',function(e){
        e.preventDefault();
        contacts_to_google();
    });
    
    
    
});



</script>