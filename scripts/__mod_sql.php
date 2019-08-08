<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script>



//*****************************************************************************************************

   
    $(document).ready(function(){
        //отправка sql
        $(document).delegate('.sql_help_id','click',function(e){
            var th_=$(this);
            e.preventDefault();
            $('.sql_').val(th_.data('val'));
        
        });//отправка sql
        $(document).delegate('.sql_help_tbl','click',function(e){
            var val=$(this).text();
            var txt= $('.sql_').val();
            if ( (txt.split('`').length - 1)>1){
                var m=txt.split('`');
                $('.sql_').val(m[0]+'`'+val+'`'+m[2]);
            }
            
            
        
        });
        //отправка sql
        $(document).delegate('.sql_start','click',function(){
           
            var err_text='';
            var th_=$(this);
            var data_=new Object();
            data_['_t']='start';
            data_['sql']=$('.sql_').val();
            
            if (err_text!=''){alert_m(err_text,'','error','none');}
            else{
            	$('.sql_res').html('<span class="ico ico_loading">Загрузка...</span>');
            	$.ajax({
            		"type": "POST",
            		"url": "?inc=__mod_sql",
            		"dataType": "text",
            		"data":data_,
            		"success":function(data,textStatus){
            			$('.sql_res').html(data);
            		}
            	});
            }
        });
    });
    
    
    </script>