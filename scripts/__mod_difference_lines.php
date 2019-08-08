<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script>
//*****************************************************************************************************
$(document).ready(function(){
    $(document).delegate('.__mod_difference_lines_send','click',function(e){
        var del_clone=$('#del_clone').prop('checked');
            
            if (del_clone==true){del_clone='1';}else{del_clone='0';}
        var line1=$('.__mod_difference_lines_1_text').val();
        var line2=$('.__mod_difference_lines_2_text').val();
        var arr1 = line1.split("\n");
        var arr2 = line2.split("\n");
        var txt1='';
        var txt2=''; 
        var t='';
        var t1all='';
        var t2all='';
        var t1='';
        var t2='';
        var m=0;
        var kol_del1=0;
        var kol_del2=0;
        
        for (var i in arr1){arr1[i]=((arr1[i]).toLowerCase()).trim();}
        for (var i in arr2){arr2[i]=((arr2[i]).toLowerCase()).trim();}
        for (var i in arr1){
            t='';t1='';t2='';
            if (in_array((arr1[i]),arr2)){
                t=''+arr1[i]+'<br/>';
                t1=''+arr1[i]+'<br/>';
            }else{
                t='<strong>'+arr1[i]+'</strong><br/>';
                t2='<strong>'+arr1[i]+'</strong><br/>';
            }
            m=0;
            for (var j in arr1){
                if ((arr1[j])==(arr1[i]) && j!=i){
                    m=1;
                }
            }
            if (m==1){
                if (del_clone=='1'){
                    t='';
                    t1='';
                    t2='';
                    kol_del1++;
                    delete(arr1[i]);
                }else{
                    t='<i>'+t+'</i>';
                }
            }
            t1all+=t1;
            t2all+=t2;
            txt1+=t;
        }
        if (kol_del1>0){txt1='<p>Удалено '+kol_del1+' дубл'+end_word(kol_del1,'ей','ь','я')+'</p>'+txt1;}
        $('.res1').html(txt1+'<hr/>'+t1all+t2all);
        
        
        t1all='';
        t2all='';
        for (var i in arr2){
            t='';t1='';t2='';
            if (in_array((arr2[i]),arr1)){
                t+=''+arr2[i]+'<br/>';
                t1=''+arr2[i]+'<br/>';
            }else{
                t+='<strong>'+arr2[i]+'</strong><br/>';
                t2='<strong>'+arr2[i]+'</strong><br/>';
            }
            m=0;
            for (var j in arr2){
                if ((arr2[j])==(arr2[i]) && j!=i){
                    m=1;
                }
            }
            if (m==1){
                if (del_clone=='1'){
                    t='';
                    t1='';
                    t2='';
                    delete(arr2[i]);
                    kol_del2++;
                }else{
                    t='<i>'+t+'</i>';
                }
            }
            txt2+=t;
            t1all+=t1;
            t2all+=t2;
        }
        if (kol_del2>0){txt2='<p>Удалено '+kol_del2+' дубл'+end_word(kol_del2,'ей','ь','я')+'</p>'+txt2;}
        $('.res2').html(txt2+'<hr/>'+t1all+t2all);
        
    });
});
</script>