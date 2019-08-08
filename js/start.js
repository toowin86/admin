
// Запрос на восстановление пароля
function start__recovery_password(){
    var data_=new Object();
    data_['_t']='recovery';
    data_['login']=$('.start__input_email').val();
    if(data_['login']!=''){
        $('.start__recovery_password').html('<div class="loading_gray">Отправка...</div>').addClass('start__recovery_password_noclick').removeClass('start__recovery_password');
        $.ajax({
        	"type": "POST",
        	"url": "ajax/start.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   $('.start__recovery_password_noclick').html('<span>Отправить пароль еще раз</span>').addClass('start__recovery_password').removeClass('start__recovery_password_noclick');
        	   if (data=='ok'){
        	       $('.start__block_res').html('<span style="color:green;">Ссылка для входа успешно отправлена на email!</span>');
        	       $('.start__input_password').val('').focus();
        	   }else{
        	       $('.start__block_res').html('ОШИБКА:'+data);
        	   }
        	}
        });
    }else{
        $('.start__block_res').html('Введите свой email!');
    }
}


$(document).ready(function(){
    $('.start__form_auth').submit(function(e){e.preventDefault();});
    $('.start__input_email').keyup(function(e){
        
        if (e.which==13){
            start__recovery_password();
        }
    });
    //запрос пароля
    $(document).delegate('.start__recovery_password','click',function(){
        start__recovery_password();
    });
});