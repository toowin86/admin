<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>

<script>

function a_options_change(th_){
    var txt='';
    
    if (th_.attr('class')=='options_name'){//Название опции
        var val=th_.text();
        txt+='<textarea data-col="name" class="a_options_save">'+val+'</textarea>';
        th_.closest('td').html(txt).find('textarea').focus().blur(function(){
            a_options_save($(this));
        }).keyup(function(e){if (e.which==13){
            a_options_save($(this));
        } });
    }else{//Значение опции
        var tip=th_.closest('tr').data('tip');
        
        if (tip=='Текст'){
             var val=th_.text();
             txt+='<textarea data-col="val" class="a_options_save">'+val+'</textarea>';
             th_.closest('td').html(txt).find('textarea').focus().blur(function(){
                    a_options_save($(this));
                }).keyup(function(e){if (e.which==13){
                    a_options_save($(this));
                } });
        }
        else if (tip=='HTML-код'){
            var val=th_.text();
            var data_=new Object();
            data_['_t']='a_options_get_html';
            data_['id']=th_.closest('tr').data('id');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_options.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            		if (is_json(data)==true){
            		    data=JSON.parse(data);
            		    txt='<div class="html_code_div">';
                        txt+='<div data-col="val" class="a_options_html_save" title="Сохранить"></div>';
                        txt+='<div class="a_options_html_close" title="Закрыть не сохраняя"><div style="display:none;">'+val+'</div></div>';
                        txt+='<textarea  id="editor1" class="a_options_save">'+data.html_+'</textarea>';
                        txt+='</div>';
                        th_.closest('td').html(txt);
                        var ckeditor1 = CKEDITOR.replace('editor1');
                        AjexFileManager.init({returnTo: 'ckeditor', editor: ckeditor1});
                        
        
                    }else{
                        alert_m(data,'','error','none');
                    }
            	}
            });
        }
        else if (tip=='Дата-время'){
             var val=th_.text();
             txt+='<input data-col="val" class="a_options_save " value="'+val+'" />';
             
             th_.closest('td').html(txt).find('input').datetimepicker().focus().blur(function(){
                    a_options_save($(this));
                }).keyup(function(e){if (e.which==13){
                    a_options_save($(this));
                } });
        }
        else if (tip=='Input'){
            var chk_=th_.find('input[type=checkbox]').prop('checked');
            
            var data_=new Object();
            data_['_t']='a_options_save';
            data_['id']=th_.closest('tr').data('id');
            data_['col']='val';
            data_['val']='0';if (chk_==true){data_['val']='1';}
            
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_options.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            		if (is_json(data)==false){
            		    if (data_['val']=='0'){th_.find('input[type=checkbox]').addAttr('checked');}
                        else{th_.find('input[type=checkbox]').removeAttr('checked');}
                        
                        alert_m(data,'','error','none');
                    }
            	}
            });
        }
    }
}



//Сохранение
function a_options_save(th_){
    
    var data_=new Object();
    data_['_t']='a_options_save';
    data_['id']=th_.closest('tr').data('id');
    data_['col']=th_.data('col');
    data_['val']=th_.val();
    
        
    if (data_['col']=='val'){
        var cl_='options_val';
        var tip=th_.closest('tr').data('tip');
        if (tip=='Текст'){cl_+=' options_val_text';}
        else if (tip=='HTML-код'){cl_+=' options_val_html';data_['val']=CKEDITOR.instances.editor1.getData();}
        else if (tip=='Дата-время'){cl_+=' options_val_data';}
        else if (tip=='Input'){cl_+=' options_val_input';}
    }else{
        cl_='options_name';
    }

    th_.closest('td').append('<span class="ico_loading ico" style="float:right;"></span>');
    $.ajax({
    	"type": "POST",
    	"url": "ajax/a_options.php",
    	"dataType": "text",
    	"data":data_,
    	"success":function(data,textStatus){
    	    $('.ico_loading').detach();
    		if (is_json(data)==true){
        	      data_n=JSON.parse(data);
                  if (tip=='HTML-код'){CKEDITOR.instances.editor1.destroy();}
    		      th_.closest('td').html('<span class="'+cl_+'">'+data_n.val+'</span>');
    		}
            else{
                alert_m(data,function(){
                    th_.focus();
                },'error','none');
            }
            //проверяем пустые опции
            $('.options_val_text').each(function(){
                var txt=$(this).text();
                if (txt.trim()==''){
                    $(this).html('<i class="fa fa-plus" />');
                }
            });
            
            
    	}
    });
    
}

//***********************************************************************************************************
//***********************************************************************************************************
//***********************************************************************************************************
$(document).ready(function(){
    // Изменение названия опции
    $(document).delegate('.options_name','click',function(){
        a_options_change($(this));
    });
    
    // Изменение значения опции
    $(document).delegate('.options_val','click',function(){
        a_options_change($(this));
    });
    
    $(document).delegate('.a_options_html_save','click',function(){
        a_options_save($(this));
    });
    //отмена сохранения
    $(document).delegate('.a_options_html_close','click',function(){
        var val=$(this).find('div').text();
        CKEDITOR.instances.editor1.destroy();
        $(this).closest('td').html('<span class="options_val options_val_html">'+val+'</span>');
    });
    //Сохранение  опции
    $(document).delegate('.a_options_save','keyup',function(e){
        var th_=$(this);
        if (e.which==13){
         
        }
    });
    
    //Добавление новой опции
    $(document).delegate('.top_com___add','click',function(){
        var txt='';
        txt+='<form class="add_new_a_options">';
        
        //HASH
        txt+='<p class="span_hash_auto">';
        txt+='<span class="span_hash" data-name="SMTP: порт" data-val="25"><span class="ico ico_hash"></span> SMTP: порт</span>';

        txt+='</p>';
        //end HASH
        
        txt+='<table>';
        
        txt+='<tr>';
            txt+='<td>Название опции*</td>';
            txt+='<td><input name="name" type="text" /></td>';
        txt+='</tr>';
        txt+='<tr>';
            txt+='<td>Значение опции*</td>';
            txt+='<td><input name="val" type="text" /></td>';
        txt+='</tr>';
        txt+='<tr>';
            txt+='<td>Тип опции*</td>';
            txt+='<td><select name="tip"><option value="Текст">Текст</option><option value="HTML-код">HTML-код</option><option value="Дата-время">Дата-время</option><option value="Input">Input</option></select></td>';
        txt+='</tr>';
        txt+='</table></form>';
        txt+='<div><center><span class="btn_orange add_new_a_options_save">Добавить</span></center></div>';
        alert_m(txt,'','add','none');
        
    });
    
    
    //Сохранение новой опции
    $(document).delegate('.add_new_a_options_save','click',function(){
        var th_=$(this);
        var err_txt='';
        var data_=$('.add_new_a_options').serializeObject();
            if (data_['name']==''){err_txt+='Не заполнено поле "Название опции"!<br />';}
            if (data_['val']==''){err_txt+='Не заполнено поле "Значение опции"!<br />';}
            if (data_['tip']==''){err_txt+='Не заполнено поле "Тип опции"!<br />';}
        data_['_t']='add_new_a_options';
        if(err_txt!=''){alert_m(err_txt,'','error','none');}
        else{//добавляем
            th_.addClass('ico').addClass('loading_gray').removeClass('add_new_a_options_save').removeClass('btn_orange').text('Добавляем');
            $.ajax({
            	"type": "POST",
            	"url": "ajax/a_options.php",
            	"dataType": "text",
            	"data":data_,
            	"success":function(data,textStatus){
            		th_.removeClass('ico').removeClass('loading_gray').addClass('add_new_a_options_save').addClass('btn_orange').text('Добавить');
            		if (is_json(data)==true){
        	               data_n=JSON.parse(data);
                            alert_m('Опция успешно добавлена!',function(){window.location.reload(true);},'ok');
                    }else{
                        if (data=='more_one'){
                            alert_m('Опция с таким названием существует!',function(){$('input[name=name]').focus().select();},'error','none');
                        }else{
                            alert_m(data,'','error','none');
                        }
                    }
            	}
            });
        } 
    });
    
    //Проверяем пустые строки
    $('.options_val_text').each(function(){
        var txt=$(this).text();
        if (txt.trim()==''){
            $(this).html('<i class="fa fa-plus" />');
        }
    });
    
});//end ready
</script>