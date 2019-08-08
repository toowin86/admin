<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>

//ЧПУ
// *******************************************************************************
// *******************************************************************************
if (tip_=='start'){
     //английский url
    var ru_url='<?php if (isset($_SESSION['a_options']['Русский URL']) and $_SESSION['a_options']['Русский URL']=='0'){echo '0';} else{echo '1';}?>';
    
    // функция
    chk_url_a=function(){
        if ($('.__other__add_form input[name=url]').size()>0){
            if ($('.__other__add_form input[name=url]').val()!=''){
                var ll_val=$('.__other__add_form input[name=url]').val();
                var ll='http://<?=$_SERVER['SERVER_NAME'].$cur_dir;?>/';
                /*
                ll_val=ll_val.replace(/([<>@`#&.*+?^=!;%,№\'\":${}()|\[\]\/\\])/g, '');
                ll_val=ll_val.replace(/([ ~_])/g, '-');
                */
                $('.__other__add_form .s_news_url_a').attr('href',ll+$('.connect_1_max_sel[name=s_struktura_id]').val()+'/'+ll_val);
            }
        }
    }
    
    chk_url=function(){
        if ($('.__other__add_form input[name="url"]').size()>0){
            if (($('.row_id__span').text()-0)==0){//только при дабавлении
                $('.__other__add_form input[name="url"]').val('');
            }
            $('.__other__add_form .s_news_url_a').attr('href','#');
            
            var link_=$('.__other__add_form .row_id__span').text();
            if (typeof link_=='undefined'){link_='';}
            if ($('.__other__add_form input[name="name"]').val()!='' && typeof $('.__other__add_form input[name=name]').val()!='undefined'){
                link_=$('.__other__add_form input[name="name"]').val();
            }
            link_=link_.replace(/([<>@`#&.*+?^=!;%,№\'\":${}()|\[\]\/\\])/g, '');
            link_=link_.replace(/([ ~_])/g, '-');
            
            //переводим в английский url
            if (ru_url=='0'){
                link_=ru_us(link_);
            }
                
            if (($('.row_id__span').text()-0)==0){//только при дабавлении
                $('.__other__add_form input[name=url]').val(link_);
            }
            chk_url_a();//меняем ссылку
        }
    }

    $(document).delegate('.__other__add_form input[name="name"]','keyup',function(){
        chk_url();
    });
    
    $(document).delegate('.__other__add_form input[name="url"]','keyup',function(){
        chk_url_a();
    });
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='change'){
    var val_=th_._d['url'];
    $('.__other__add_form input[name=url]').val(_IN(val_));
    
    if (val_==''){
        chk_url();
    }else{
        chk_url_a();
    }
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='add'){

}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='save'){
    var err_text='';
    if (th_['url']==''){err_text+='Не заполнен URL!<br />';}
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
    var txt ='<li data-col="'+col_+'">'
                +'<input type="checkbox" id="export_chk_'+col_+'" name="export_chk_'+col_+'" data-col="'+col_+'" /> '
                +'<label for="export_chk_'+col_+'">'
                    +$(this).find('.__function_s_cat_prop__name').text()
                +'</label> '
                +'<div class="export_textarea_div">'
                    +'<textarea placeholder="PHP код для изменения поля $myrow[\''+col_+'\']" name="export_code_'+col_+'"></textarea>'
                +'</div><div style="clear:both;"></div>'
                +'</li>';
         return txt;
}
else{
    alert_m('Тип функции '+col_+' не определен!','','error','none');
}
// *******************************************************************************
// *******************************************************************************
//end  ЧПУ
