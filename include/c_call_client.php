<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
     

?>
<!-- Форма приема звонка -->    
<form class="c_call_client_add_form">
    <div class="c_call_client_add">
        <?php
        $sql = "SELECT COUNT(*)
                        FROM c_questions, a_col
        					WHERE c_questions.chk_active='1'
                            AND a_col.id=c_questions.a_col_id
                            ORDER BY c_questions.sid
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $cnt_=$myrow[0];
        
        $a_col_id_arr=array();
        if ($cnt_>0){
        ?>
        <!-- Основной блок -->
        <div class="c_call_client_add__questions">
        <?php
        
        $sql = "SELECT      c_questions.id,
                            c_questions.a_col_id,
                            a_col.col_ru,
                            c_questions.comments,
                            c_questions.tip,
                            c_questions.chk_required
                            
        				FROM c_questions, a_col
        					WHERE c_questions.chk_active='1'
                            AND a_col.id=c_questions.a_col_id
                            ORDER BY c_questions.sid
        ";
         
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $a_col_id_arr[]=$myrow[1];
            $required='';$required_cl='';if ($myrow[5]=='1'){$required=' required="required"';$required_cl='required';}
            $comments=$myrow[3]; if ($comments==''){$comments=$myrow[2];}
            ?>
            <div class="c_call_client_add__quest <?=$required_cl;?>">
                <span class="c_call_client_add__quest_span"><?=$comments;?></span>
            <?php
            if ($myrow[4]=='Одно значение'){
                ?>
                <input type="text" placeholder="<?=_IN($myrow[2]);?>" data-a_col_id="<?=_IN($myrow[1])?>" name="c_questions_id_<?=_IN($myrow[1])?>" <?=$required;?> />
                <?php
            }
            if ($myrow[4]=='Несколько значений'){
                ?>
                <select data-placeholder="<?=_IN($myrow[2]);?>" data-a_col_id="<?=_IN($myrow[1])?>" name="c_questions_id_<?=_IN($myrow[1])?>" <?=$required;?> multiple>

                </select> 
                <?php
            }
                
            ?>
            </div>
            <?php
        }
        ?>
        </div>
        <?php
        }
        ?>
        <!-- Справка -->
        <div class="c_call_client_info">
            <h2>Клиент</h2>
            <div class="c_call_client_info_client"></div>
            <hr />
            <?php
            //351 - тип (товары и услуги)
            //354 - бренд (товары)
            //359 - модель (товары)
            //901 - неисправности (услуги)
            if (in_array('351',$a_col_id_arr) or in_array('354',$a_col_id_arr) or in_array('359',$a_col_id_arr) or in_array('901',$a_col_id_arr)){
                if (in_array('351',$a_col_id_arr) or in_array('901',$a_col_id_arr)){
                ?>
                <h2>Работы</h2>
                <div class="c_call_client_info_works"></div>
                <hr />
                <?php
                }
                if (in_array('351',$a_col_id_arr) or in_array('354',$a_col_id_arr) or in_array('359',$a_col_id_arr)){
                ?>
                <h2>Товары</h2>
                <div class="c_call_client_info_items"></div>
                <?php
                }
            }
            ?>
            
        </div>
  
        <!-- Общая информация -->
        <div class="c_call_client_add__other required">
            <div>
                <span>Описание звонка:</span>
                <textarea name="comments" class="c_call_client_add__other_comments" required="required" placeholder="Описание звонка"></textarea>
            </div>
            
            <div class="c_call_client_add__other_info required">
                
                <span>Телефон:</span>
                <input type="text" name="i_contr_phone" placeholder="Телефон клиента" required="required" />
                
                <span>ФИО клиента: <i class="fa fa-copy c_call_client_add__copy_name" title="Указать в качестве имени номер телефона"></i></span>
                <input type="text" name="i_contr_name" placeholder="ФИО клиента" required="required" />
            </div>
            <div class="c_call_client_add__other_com">
                <span class="btn_orange c_call_client_add__save"><i class="fa fa-phone"></i> Добавить</span>
            </div>
        
        </div> 
    </div>
</form>
<div style="clear: both;"></div>
<hr />
<!-- Фильтр -->
<div class="c_call_client_fillter">
    <div class="c_call_client_fillter__find_txt">
        <input name="find_txt" placeholder="Поиск по звонкам" type="text" value="<?=_IN(_GP('nomer'));?>" />
        <i class="fa fa-search"></i>
        <div style="clear: both;"></div>
    </div>
    
    <div class="c_call_client_fillter__data_in_out">
        <input type="text" name="c_call_client_data1_find" placeholder="от" />
        -
        <input type="text" name="c_call_client_data2_find" placeholder="до" />
    </div>
    
    <div class="google_drive_block">
        <?php
        /*
        if (isset($err_google)){
             if ($err_google==''){
                
                 //Если есть связь с google drive
                 if ((isset($_SESSION['google_drive_access_token']) && $_SESSION['google_drive_access_token'])) {
                    ?>
                    <span class="ico_loading ico" style="display: none;">Получение данных с Google Drive</span>
                    <span><input type="text" class="google_drive_syn_data" placeholder="Дата" value="<?=_IN($_SESSION['a_options']['Google Drive - дата последней синхронизации звонков']);?>" /></span>
                    <span class="google_drive_syn_calls"><i class="fa fa-refresh"></i> Синхронизировать звонки с диска</span>
             
                    <span class="google_drive_exit"><i class="fa fa-remove"></i> Отключиться</span>
                    <?php
                 }
                 else{//Если нет связи с google drive
                    
                    ?>
                    <a class="google_drive_connect" href="?inc=c_call_client&google_drive_connect"><i class="fa fa-plus"></i> Подключиться к Google Disk</a>
                    <?php
                 }
                 
               
             }
             else{
                echo $err_google;
             }
        }*/
        ?>
       
    </div>
    
    <div style="clear: both;"></div>       
</div>
<!-- Последние принятые звонки -->
<div class="c_call_client_res">

</div>