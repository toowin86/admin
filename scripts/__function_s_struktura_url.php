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
        if ($('.__other__add_form input[name=url]').val()!=''){
            if ($('.__other__add_form input[name=url]').size()>0){
                var ll_val=$('.__other__add_form input[name=url]').val();
                var ll='http://<?=$_SERVER['SERVER_NAME'];?>/';
                /*
                ll_val=ll_val.replace(/([<>@`#&.*+?^=!;%,№\'\":${}()|\[\]\/\\])/g, '');
                ll_val=ll_val.replace(/([ ~_])/g, '-');
               */
                           
                var th_=$('.__other__add_form .enum[name=tip]');
                if (th_.val()=='Ссылка'){
                    ll='';
                    if ((ll_val.split('http://').length - 1)==0 && (ll_val.split('https://').length - 1)==0 && (ll_val.split('ftp://').length - 1)==0){
                        ll='http://';
                    }
                }
                else{
                    ll_val=ll_val+'/';
                }
                
                $('.__other__add_form .s_struktura_url_a').attr('href',ll+ll_val);
            }
        }
    }
    
    chk_url=function(){
        if ($('.__other__add_form input[name=url]').size()>0){
            var th_=$('.__other__add_form .enum[name=tip]');
            if (($('.row_id__span').text()-0)==0){//только при дабавлении
                $('.__other__add_form input[name=url]').val('');
            }
            $('.__other__add_form .s_struktura_url_a').attr('href','#');
            
            if (th_.val()=='Ссылка'){
                $('.__other__add_form input[name=url]').attr('placeholder','Укажите url ссылки');
            }
            else{
                var link_=$('.__other__add_form .row_id__span').text();
                if (typeof link_=='undefined'){link_='';}
                if ($('.__other__add_form input[name=name]').val()!='' && typeof $('.__other__add_form input[name=name]').val()!='undefined'){
                    link_=$('.__other__add_form input[name=name]').val();
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
            }
            chk_url_a();//меняем ссылку
        }
    }
    
    $(document).delegate('.__other__add_form .enum[name="tip"]','change',function(){
        chk_url();
    });
    
    $(document).delegate('.__other__add_form input[name="name"]','keyup',function(){
        if ($('.__other__add_form .enum[name=tip]').val()!='Ссылка'){
            chk_url();
        }
    });
    
    $(document).delegate('.__other__add_form input[name="url"]','keyup',function(){
        chk_url_a();
    });
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='change'){
    if ($('.__other__add_form input[name=url]').size()==0){
        $('.__other__add_form .enum[name=tip]').find('option[value=Ссылка]').attr('disabled','disabled');
        $('.__other__add_form .enum[name=tip]').change().closest('td').append('<span>Чтобы активировать ссылку - вклчите ЧПУ в администрировании!</span>');
    }
    
    var val_=th_._d['url'];
    $('.__other__add_form input[name=url]').val(_IN(val_));
    
    chk_url_a();
    
}
// *******************************************************************************
// *******************************************************************************
else if(tip_=='add'){
    if ($('.__other__add_form input[name=url]').closest('tr').css('display')=='none'){
        $('.__other__add_form .enum[name=tip]').find('option[value=Ссылка]').attr('disabled','disabled');
        $('.__other__add_form .enum[name=tip]').change().closest('td').append('<span>Чтобы активировать ссылку и функцию - вклчите ЧПУ в администрировании!</span>');
    }
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
                    +'<textarea placeholder="PHP код для изменения поля $data_[\''+col_+'\']" name="export_code_'+col_+'"></textarea>'
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
