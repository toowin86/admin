<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<?php

?>
<div class="top_com">
    <ul>
        <li style="display: none;"><div class="top_com__other__close" title="Закрыть окно"></div></li>
        <li style="display: none;"><div class="top_com___loading loading38" ></div></li>
        <li style="display: none;"><div class="top_com___loading_s loading_s38" ></div></li>
        <li style="display: none;"><div class="top_com___loading_f loading_f38" ></div></li>
        <li style="display: none;"><div class="__other__add__save" title="Сохранить"></div></li>
        <li style="display: none;"><div class="__other__add__saveadd" title="Сохранить и добавить еще"></div></li>
        <li style="display: none;"><div class="__other__add__saveclose" title="Сохранить и закрыть окно"></div></li>
        <?php
        //************ ДОБАВЛЕНИЕ *************
        foreach ($a_com_arr['com'] as $a_com_id => $com_){
            if ($com_=='add'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
                ?>
                    <li style="display: none;"><div class="top_com___add" title="<?=$a_com_arr['name'][$a_com_id];?>"></div></li>
                <?php
                }
            }
            if ($com_=='add_xls'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
                ?>
                    <li style="display: none;"><div class="top_com___add_xls" title="Парсинг с EXCEL"></div></li>
                <?php
                }
            }
            if ($com_=='export_csv'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
                ?>
                    <li style="display: none;"><div class="top_com___export_csv" title="Экспорт в CSV"></div></li>
                    <li style="display: none;"><div class="top_com__other__start_export_csv" title="Выгрузить данные в CSV"></div>
                        <div class="add_export_csv__hide">
                            <form class="export_all_form" method="post" action="?inc=export_csv">
                                <p class="span_hash_auto">
                            <?php
                                $sql = "SELECT      id,
                                                    name,
                                                    opt1,
                                                    opt2,
                                                    opt3,
                                                    opt4,
                                                    opt5
                                                    
                                                    
                                				FROM a_export_csv
                                					WHERE a_export_csv.a_menu_id='"._DB($inc_id)."' 
                                					ORDER BY name
                                ";
                                 
                                $mt = microtime(true);
                                $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                
                                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                {
                                    ?>
                                    <span   id="hash_<?=$myrow['id'];?>"
                                            data-script_name="<?=_IN(($myrow['name']));?>"
                                            data-script_opt1="<?=_IN(($myrow['opt1']));?>"
                                            data-script_opt2="<?=_IN(($myrow['opt2']));?>"
                                            data-script_opt3="<?=_IN(($myrow['opt3']));?>"
                                            data-script_opt4="<?=_IN(($myrow['opt4']));?>"
                                            data-script_opt5="<?=_IN(($myrow['opt5']));?>"
                                    <?php
                                    $sql_col = "SELECT col, code
                                    				FROM a_export_csv_col 
                                    					WHERE a_export_csv_col.a_export_csv_id='"._DB($myrow['id'])."' 
                                    					ORDER BY id DESC
                                    ";
                                     
                                    $mt = microtime(true);
                                    $res_col = mysql_query($sql_col);if (!$res_col){echo $sql_col;exit();}
                                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_col;$data_['_sql']['time'][]=$mt;
                                    
                                    for ($myrow_col = mysql_fetch_array($res_col); $myrow_col==true; $myrow_col = mysql_fetch_array($res_col))
                                    {
                                        ?>
                                        data-export_chk_<?=_IN(($myrow_col['col']));?>="1"
                                        data-export_code[<?=_IN(($myrow_col['col']));?>]="<?=_IN(($myrow_col['code']));?>"
                                        <?php
                                    }
                                    ?>
                                            
                                    class="span_hash_sort_and_create"><span class="ico ico_hash"></span> <?=_IN($myrow['name']);?></span><i title="Удалить быструю ссылку" data-id="<?=$myrow['id'];?>" class="fa fa-remove del_hash_export_csv"></i> 
                                    <?php
                                }
                                ?>
                                </p>
                                <hr />
                                <h1>Экспорт в CSV</h1>
                                <h3></h3>
                                <input name="nomer" type="hidden" value="" /><input name="inc_export" type="hidden" value="<?=$inc;?>" />
                                <div><span>Название скрипта: </span><input name="script_name" type="text" placeholder="Название скрипта" /><div style="clear:both;"></div></div><hr />
                                <div><span>Разделитель полей: </span><input type="text" name="script_opt1" placeholder="Разделитель полей" value=";" /><div style="clear:both;"></div></div>
                                <div><span>Значения полей обрамлены: </span><input type="text" name="script_opt2" placeholder="Значения полей обрамлены" value="&quot;" /><div style="clear:both;"></div></div>
                                <div><span>Символ экранирования: </span><input type="text" name="script_opt3" placeholder="Символ экранирования" value="&quot;" /><div style="clear:both;"></div></div>
                                <div><span>Разделитель строк: </span><input type="text" name="script_opt4" placeholder="Разделитель строк" value="\n" /><div style="clear:both;"></div></div>
                                <div><span><label for="script_opt5">Выводить названия столбцов:</label> </span><input style="width:10px;" name="script_opt5" type="checkbox" id="script_opt5" value="1" /><div style="clear:both;"></div></div>
                                <ul class="export_ul">
                                
                                </ul>
                                <div class="export_all_form_add_null_col btn_orange">
                                    <i class="fa fa-plus-square"></i> Добавить новое поле
                                </div>
                            </form>
                        </div>
                    </li>
                <?php
                }
            }
            if ($com_=='add_parsing'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
                ?>
                    <li style="display: none;"><div class="top_com__other__start_parsing" title="Начать парсинг"></div></li>
                    <li style="display: none;"><div class="top_com__other__stop_parsing" title="Остановить парсинг"></div></li>
                    <li style="display: none;"><div class="top_com___add_parsing" title="Парсинг с сайта"></div>
                        <div class="add_parsing__hide">
                            <p class="span_hash_auto">
                            <?php
                            $sql = "SELECT      id,
                                                name,
                                                url,
                                                selector_page,
                                                selector_block,
                                                selector_card,
                                                main_if,
                                                tip,
                                                main_col,
                                                tip_update,
                                                data_create,
                                                pop,
                                                main_col,
                                                sleep_
                                                
                                                
                                                
                            				FROM a_parsing 
                            					WHERE a_parsing.a_menu_id='"._DB($inc_id)."' 
                            					ORDER BY name
                            ";
                             
                            $mt = microtime(true);
                            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                            
                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                            {
                                $tipp='in_db';if($myrow['tip']=='Тест'){$tipp='test';}
                                ?>
                                <span class="hash_parsing_div">
                                <i title="Удалить быструю ссылку" data-id="<?=$myrow['id'];?>" class="fa fa-remove del_hash"></i> 
                                <span   id="hash_<?=$myrow['id'];?>"
                                        data-_parsing__link="<?=_IN($myrow['url']);?>"
                                        data-_parsing__page="<?=_IN($myrow['selector_page']);?>" 
                                        
                                        data-_parsing__name="<?=_IN($myrow['name']);?>"
                                        
                                        data-_parsing__block="<?=_IN($myrow['selector_block']);?>"
                                        data-_parsing__link_item="<?=_IN($myrow['selector_card']);?>"
                                        data-_parsing__main="<?=_IN($myrow['main_col']);?>"
                                        data-_parsing__work="<?=$tipp;?>"
                                        data-_parsing__sleep="<?=_IN($myrow['sleep_']);;?>"
                                <?php
                                    
                                    $sql_col = "SELECT col, chk_active, selector, code
                                    				FROM a_parsing_col 
                                    					WHERE a_parsing_col.a_parsing_id='"._DB($myrow['id'])."' 
                                    					
                                    ";
                                     
                                    $mt = microtime(true);
                                    $res_col = mysql_query($sql_col);if (!$res_col){echo $sql_col;exit();}
                                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_col;$data_['_sql']['time'][]=$mt;
                                    
                                    for ($myrow_col = mysql_fetch_array($res_col); $myrow_col==true; $myrow_col = mysql_fetch_array($res_col))
                                    {
                                        ?>
                                        data-parsing_chk_<?=$myrow_col['col'];?>="<?=$myrow_col['chk_active'];?>"
                                        data-parsing_<?=$myrow_col['col'];?>="<?=_IN($myrow_col['selector']);?>"
                                        data-parsing__code_<?=$myrow_col['col'];?>="<?=_IN($myrow_col['code']);?>"
                                        
                                        <?php
                                    }
                                
                                ?>
                                    
                                    class="span_hash"><span class="ico ico_hash"></span> <?=_IN($myrow['name']);?></span>
                                </span>
                                
                                <?php
                            }
                            
                            
                            ?>
                            
                            
                              
                            </p>
                            
                                <div style="width:100%" class="ttable __other__parsing_main_table">
                                    <div class="ttable_tbody_tr super_main__parsing">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Ссылка на сайт*:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input type="text" name="_parsing__link" placeholder="Ссылка на сайт" /></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Название скрипта*:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input type="text" name="_parsing__name" placeholder="Название скрипта" /></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Селектор следующей страницы:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input type="text" name="_parsing__page" placeholder="Селектор следующей страницы" /></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Селектор блока товара*:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input type="text" name="_parsing__block" placeholder="Селектор блока товара" /></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Селектор ссылки карточки товара:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input type="text" name="_parsing__link_item" placeholder="Селектор ссылки карточки товара" /></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Проверка на соответствие / если выполняется - парсим:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input type="text" name="_parsing__if" placeholder="Проверка на соответствие" /></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Режим:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><select name="_parsing__work"><option value="test">Тест</option><option value="in_db">Добавление в базу</option></select></div>
                                    </div>
                                    <div class="ttable_tbody_tr">
                                        <div class="ttable_tbody_tr_td __other__parsing_td1">Задержка, сек:</div>
                                        <div class="ttable_tbody_tr_td __other__parsing_td2"><input name="_parsing__sleep" type="text" value="0"></div>
                                    </div>
                                </div>
                                <hr />
                                <div class="ttable __other__parsing_res">
                                    
                                </div>
                                <p style="margin: 10px 0; color:#999; font-size: 12px;">
                                    <a href="http://simplehtmldom.sourceforge.net/manual.htm">Справка</a><br />
                                    $article->find('td',0)->find('a[name!=]',0)<br />
                                    $html_in->find('title', -1)<br />
                                    if(isset($val->plaintext)) {$m=$val->plaintext;unset($val);$val=$m;}else{$val='';}<br />
                                    if(isset($val->name)) {$m=$val->name;unset($val);$val=$m;}else{$val='';}$val=str_replace('rec_','',$val);<br />
                                    $temp_img=array();foreach($val as $a_img){$temp_img[]=@$a_img->src;}unset($val);$val=$temp_img;<br />
                                    $link_item_url=''; $e=$article; if (isset($e->onclick)){}
                                </p>                             
                                <div>
                                    
                                 </div>   
                        </div>
                    </li>
                <?php
                }
            }
            if ($com_=='del'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
                ?>
                    
                    <li style="display: none;"><div class="__other__delete" title="Удалить выделенные"></div></li>
                    
                <?php
                }
            }
            if ($com_=='change'){
                if (isset($a_admin_a_com_arr[$a_com_id])){
                    if (isset($names) and is_array($names) and in_array('chk_active',$names)){
                        
                        $sql = "SELECT  
                                        IF(COUNT(*)>0,a_col.chk_change,'0')
                                        
                        				FROM a_col
                        					WHERE a_col.chk_active='1'
                                            AND a_col.chk_view='1'
                                            AND a_col.a_menu_id='"._DB($inc_id)."'
                                            AND a_col.col='chk_active'
                                            
                        	"; 
                        $res = mysql_query($sql) or die(mysql_error());
                        $myrow = mysql_fetch_array($res);
                        if($myrow[0]=='1'){
                        
                ?>
                    
                    <li style="display: none;"><div class="__other__noactive" title="Отключить выделенные"></div></li>
                    <li style="display: none;"><div class="__other__active" title="Включить выделенные"></div></li>
                <?php
                        }
                    }
                
                                    
                    ?>
                        <li style="display: none;"><div class="__other__copypaste" title="Экспорт/импорт"></div></li>
                    <?php
                    if ($inc_id==7){
                        $sql = "SELECT  
                                        IF(COUNT(*)>0,a_menu.chk_active,'0')
                                        
                        				FROM a_menu
                        					WHERE a_menu.inc='m_zakaz'
                                            
                        	"; 
                        $res = mysql_query($sql) or die(mysql_error());
                        $myrow = mysql_fetch_array($res);
                        if($myrow[0]=='1'){
                            ?>
                            <li style="display: none;"><div class="__other__add_to_zakaz" title="Добавить в заказ"></div></li>
                            <?php
                        }
                        $sql = "SELECT  
                                        IF(COUNT(*)>0,a_menu.chk_active,'0')
                                        
                        				FROM a_menu
                        					WHERE a_menu.inc='m_postav'
                                            
                        	"; 
                        $res = mysql_query($sql) or die(mysql_error());
                        $myrow = mysql_fetch_array($res);
                       
                        if($myrow[0]=='1'){
                            ?>
                            <li style="display: none;"><div class="__other__add_to_postav" title="Добавить в поступление"></div></li>
                            <?php
                        }
                    }
                    
                    
                }
            }
        }
        ?>
    </ul>
    <div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>

<div class="main_block">
    <div class="page_name_block">
        <h1 class="page_name">
            <?php 
                if (isset($a_menu_arr['name'][$inc_id]) and $a_menu_arr['name'][$inc_id]!=''){
                    echo $a_menu_arr['name'][$inc_id];
                }
            ?>
            <span class="__other__open_lastpage"><?php
            if (isset($_SESSION[$inc]['other_lastpage']) and $_SESSION[$inc]['other_lastpage']!=''){
                ?>
                (<a href="?inc=<?=$inc;?>&com=_change&nomer=<?=$_SESSION[$inc]['other_lastpage']?>">Последняя страница</a>)
                <?php
            }
            ?></span>
        </h1>
    </div>
    
    <div style="clear: both;"></div>
    
   
    <!-- Результаты -->
    <div class="page_res" id="page_res" data-inc="<?=$inc;?>"></div>
    <div class="__other__res_loading_row_tr ttable2"></div>
</div>
<!-- Скрытый DIV пред-загрузки -->
<div class="preload" style="display: none;">
    <!-- Форма добавления -->
    <div class="__other__add">
            <h2></h2>
            <?php
            $names=get_column_names_with_show($inc);
            if (in_array('name',$names)){
             
            ?>
            <div class="__other__add_copy_from_div"><input type="text" class="__other__add_copy_from" placeholder="Заполнить форму копированием из..." /></div>
            <div class="__other__add_copy_from_div"><textarea class="__other__add_many" placeholder="Добавить несколько значений, каждое значение с новой строки" ></textarea><i class="fa fa-plus __other__add_many_add" title="Добавить"></i></div>
            <div style="clear: both;"></div>
            <?php
               
            }
            ?>
            <div class="ttable">
                <?php
                    
                    // получаем массив всех изменяемых столбцов
                    $data_['col']=array();$data_['col_ru']=array();$data_['tip']=array();$col_m=array();$table_m=array();
                    
                    $sql = "SELECT  a_col.`id`,
                                    a_col.`col`,
                                    a_col.`col_ru`,
                                    a_col.`tip`,
                                    a_col.`chk_change`
                                    
                    				FROM a_col
                    					WHERE a_col.chk_active='1'
                                        AND a_col.a_menu_id='"._DB($inc_id)."'
                                        AND a_col.id IN (SELECT a_admin_a_col.id2 FROM a_admin_a_col, a_admin WHERE a_admin_a_col.id1=a_admin.id AND a_admin.email='"._DB($_SESSION['admin']['email'])."' AND a_admin.password='"._DB($_SESSION['admin']['password'])."')
                                        
                                    ORDER BY a_col.sid
                                    
                    "; 
                    //
                    
                    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
                    $photo=0; //маркер фото
                    while ($myrow = mysql_fetch_array($res)) 
                    {
                      
                        $data_['col'][$myrow[0]]=$myrow[1];
                        $data_['col_ru'][$myrow[0]]=$myrow[2];
                        $data_['tip'][$myrow[0]]=$myrow[3];
                        $data_['chk_change'][$myrow[0]]=$myrow[4];
                        //получаем значение по умолчанию
                        $val_def='';
                        if ($data_['col'][$myrow[0]]!='' and $inc!=''){
                            $sql_="SELECT IF(COUNT(*)>0,`COLUMN_DEFAULT`,'') 
                                        FROM `information_schema`.`COLUMNS` 
                                            WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                                            AND `TABLE_NAME`='"._DB($inc)."' 
                                            AND `COLUMN_NAME`='"._DB($data_['col'][$myrow[0]])."'
                                "; 
            				$res_def = mysql_query($sql_);
            				$myrow_def = mysql_fetch_array($res_def);
            				$val_def=$myrow_def[0]; 
                        }
                            if ($val_def=='CURRENT_TIMESTAMP'){$val_def=date('d.m.Y H:i:s');}
                            $ico_='connect';if ($data_['col'][$myrow[0]]!=''){$ico_=$data_['col'][$myrow[0]];}
                            
                            
                        if ($data_['tip'][$myrow[0]]!='Фото')
                        {
                            if ($data_['chk_change'][$myrow[0]]=='1' or $data_['tip'][$myrow[0]]=='Функция'){
                                
                                $dis='';
                                if ($data_['tip'][$myrow[0]]=='Функция' and $data_['chk_change'][$myrow[0]]=='0'){
                                    $dis=' style="display:none;"';
                                }
                            ?>
                            <div class="ttable_tbody_tr" data-col="<?=_IN($data_['col'][$myrow[0]]);?>" data-col_id="<?=$myrow[0];?>" data-tip="<?=_IN($data_['tip'][$myrow[0]]);?>"<?=$dis;?>>
                                    <div class="ttable_tbody_tr_td">
                                        <span class="ico ico__left ico_change_<?=$ico_;?>"></span>
                                        <span><?=$data_['col_ru'][$myrow[0]];?></span>
                                        <span class="col_us_mini"><?=$data_['col'][$myrow[0]];?></span>
                                        <?php
                                        if ($data_['tip'][$myrow[0]]=='HTML-код' and in_array('name',$data_['col']))
                                        {
                                            ?><span class="__othrer__clone_name_html_code"><i class="fa fa-copy" title="Клонировать название"></i></span><?php
                                        }
                                        ?>
                                    </div>
                                    <div class="ttable_tbody_tr_td">
                            <?php
                            }
                        }
                        // **********************************************************************************
                        switch ($data_['tip'][$myrow[0]]) { //ТИП
                        case "Текст":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input type="text" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Длинный текст":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <textarea name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>"><?=$val_def;?></textarea>
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "HTML-код":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <textarea class="html_editor" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>"><?=$val_def;?></textarea>
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Целое число":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                           
                            <input type="text" class="spinner" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Дробное число":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input type="text" class="spinner_f" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Стоимость":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input type="text" class="price" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Дата":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input type="text" class="data" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Дата-время":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input type="text" class="datatime" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>"/>
                               
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Телефон":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input class="phone" pattern="\d\(\d\d\d\)?\d\d\d-\d\d\d\d" placeholder="#(###)###-####" type="tel" title="Формат:8(999)999-9999" name="<?=$data_['col'][$myrow[0]];?>" value="<?=$val_def;?>"/>
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Email":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input type="text" class="email" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Связанная таблица 1-max":
                            unset($val_all);
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            $sql_connect = "SELECT  a_menu.id,
                                                    a_menu.inc AS inc_,
                                                    a_col.id,
                                                    a_col.col AS col_,
                                                    a_connect.usl  AS usl_,
                                                    a_connect.chk AS chk_
                                                    
                            				FROM a_connect, a_col, a_menu
                            					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
                                                AND a_col.id=a_connect.a_col_id2
                            					AND a_col.a_menu_id=a_menu.id
                            	"; 
                            $res_connect = mysql_query($sql_connect) or die(mysql_error());
                            if (mysql_num_rows($res_connect)==0) {echo 'Не задана таблица связи!'; break;}
                            $myrow_connect = mysql_fetch_array($res_connect);
                            $table_connect=$myrow_connect['inc_'];
                            $col_connect=$myrow_connect['col_'];
                            $usl_connect=$myrow_connect['usl_'];
                                if ($usl_connect!=''){
                                    $usl_connect=' WHERE '.$usl_connect;
                                }
                            $chk_connect=$myrow_connect['chk_'];
                            if ($chk_connect=='1'){//авто-добавление
                                $sql_connect = "SELECT `"._DB($table_connect)."`.`"._DB($col_connect)."`
                                				FROM `"._DB($table_connect)."`
                                                    WHERE `"._DB($table_connect)."`.`id`='"._DB($val_def)."'
                                	"; 
                                
                                $res_connect = mysql_query($sql_connect);if (!$res_connect){echo $sql_connect;exit();}
                                $myrow_connect = mysql_fetch_array($res_connect);
                                $val_def_name=$myrow_connect[0];
                                
                                //получаем количество связанных значений
                                $sql_connect = "SELECT COUNT(*)
                                				FROM `"._DB($table_connect)."`
                                                    $usl_connect
                                	"; 
                                
                                $res_connect = mysql_query($sql_connect);if (!$res_connect){echo $sql_connect;exit();}
                                $myrow_connect = mysql_fetch_array($res_connect);
                                $val_all=array();
                                if ($myrow_connect[0]<=100){
                                    
                                    $sql_connect = "SELECT  `"._DB($table_connect)."`.`id`,
                                                            `"._DB($table_connect)."`.`"._DB($col_connect)."`,
                                                            (SELECT COUNT(*) FROM `"._DB($inc)."` WHERE `"._DB($inc)."`.`"._DB($data_['col'][$myrow[0]])."`=`"._DB($table_connect)."`.`id`) AS cnt_
                                    				FROM `"._DB($table_connect)."`
                                                        $usl_connect
                                                        GROUP BY `"._DB($table_connect)."`.`"._DB($col_connect)."`
                                    					ORDER BY FIELD(`id`,'"._DB($val_def)."') DESC, cnt_ DESC, `"._DB($col_connect)."`
                                                        
                                    "; 
                                   
                                    $res_connect = mysql_query($sql_connect) or die($sql_connect.'<br />'.mysql_error());
                                    for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
                                    {
                                        $val_all[$myrow_connect[0]]=$myrow_connect[1];
                                    }
                                }
                                ?>
                                <input type="text" class="connect_1_max" data-col_id="<?=$myrow[0];?>" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def_name;?>" />
                                <div class="connect_1_max_source" style="display:none;"><?=implode("||",$val_all);?></div>
                                 
                                <?php
                            }
                            
                            if ($chk_connect=='0'){//селект
                                $sql_connect = "SELECT  `"._DB($table_connect)."`.`id`,
                                                        `"._DB($table_connect)."`.`"._DB($col_connect)."`,
                                                        (SELECT COUNT(*) FROM `"._DB($inc)."` WHERE `"._DB($inc)."`.`"._DB($data_['col'][$myrow[0]])."`=`"._DB($table_connect)."`.`id`) AS cnt_
                                				FROM `"._DB($table_connect)."`
                                                    $usl_connect
                                                    GROUP BY `"._DB($table_connect)."`.`"._DB($col_connect)."`
                                					ORDER BY FIELD(`id`,'"._DB($val_def)."') DESC, cnt_ DESC, `"._DB($col_connect)."`
                                                    
                                "; 
                               
                                $res_connect = mysql_query($sql_connect) or die($sql_connect.'<br />'.mysql_error());
                                for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
                                {
                                    $val_all[$myrow_connect[0]]=$myrow_connect[1];
                                }
                                ?>
                                <select class="connect_1_max_sel" data-col_id="<?=$myrow[0];?>" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" >
                                    <?php
                                    foreach($val_all as $id => $val){
                                        $sel_='';if ($val_def==$id){
                                            $sel_=' selected="selected"';
                                        }
                                        ?>
                                        <option value="<?=$id;?>"<?=$sel_;?>><?=$val;?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
            
                                <?php
                            }
                            
                        }
                        break;
                        //***********************************************************************************
                        case "Связанная таблица max-max":
                        if ($data_['chk_change'][$myrow[0]]=='1'){
                            $sql_connect = "SELECT  a_menu.id,
                                                    a_menu.inc AS inc_,
                                                    a_col.id AS col_id_,
                                                    a_col.col AS col_,
                                                    
                                                    a_connect.usl  AS usl_,
                                                    a_connect.chk AS chk_,
                                                    a_connect.tbl AS tbl_,
                                                    a_connect.id AS a_connect_id
                                                    
                                                    
                            				FROM a_connect, a_col, a_menu
                            					WHERE a_connect.a_col_id1='"._DB($myrow[0])."'
                                                AND a_col.id=a_connect.a_col_id2
                            					AND a_col.a_menu_id=a_menu.id
                            	"; 
                            $res_connect = mysql_query($sql_connect) or die(mysql_error());
                            if (mysql_num_rows($res_connect)==0) {echo 'Не задана таблица связи!'; break;}
                            $myrow_connect = mysql_fetch_array($res_connect);
                            $inc_connect=$myrow_connect['inc_'];//$table_connect
                            $col_connect=$myrow_connect['col_'];
                            $col_id_connect=$col_connect.'_'.$myrow_connect['a_connect_id']; 
                            $usl_connect=$myrow_connect['usl_'];
                            $tbl_connect=$myrow_connect['tbl_'];
                            
                                if ($usl_connect!=''){
                                    $usl_connect=' WHERE '.$usl_connect;
                                }
                            $chk_connect=$myrow_connect['chk_'];
                            
                           
                            if ($chk_connect=='0'){ //CHK-BOX
                            
                                //PID/sid
                                $names=get_column_names_with_show($inc_connect);
                                $inc_connect_pid=" '0' AS pid_,";
                                if (in_array('pid',$names)){//присутствует вложенность
                                    $inc_connect_pid="`"._DB($inc_connect)."`.`pid` AS pid_,";
                                }
                                
                                $ORDER_MAX='';
                                if (in_array('sid',$names)){//присутствует вложенность
                                    $ORDER_MAX="ORDER BY `"._DB($inc_connect)."`.`sid`";
                                }
                                //end  PID/sid
                                
                                $sql_connect = "SELECT  `"._DB($inc_connect)."`.`id` AS id_,
                                                $inc_connect_pid
                                                `"._DB($inc_connect)."`.`"._DB($col_connect)."` AS val_
                                				FROM `"._DB($inc_connect)."`
                                					$usl_connect
                                                    
                                                    $ORDER_MAX		
                                "; 
                                //echo $sql_connect;
                                $res_connect = mysql_query($sql_connect) or die(mysql_error());
                                $connect_max_pid=array();$connect_max_name=array();
                                for ($myrow_connect = mysql_fetch_array($res_connect); $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect))
                                {
                                    $connect_max_name['name'][$myrow_connect['id_']]=$myrow_connect['val_']; 
                                    $connect_max_name['pid'][$myrow_connect['id_']]=$myrow_connect['pid_'];
                                }
                                //print_r($connect_max_name);
                                
                                if (in_array('pid',$names)){//PID
                                    if (isset($connect_max_name['name']) and count($connect_max_name['name'])>0){
                                        echo '<div id="'.$data_['col'][$myrow[0]].'" class="connect_max_max" data-col_id_connect="'.$col_id_connect.'">'
                                            . '<ul>'
                                            . parents_arr($connect_max_name,'li');
                                            foreach(nul_parents_id($connect_max_name) as $key_ => $id_cur){
                                                echo '<li data-id="'.$id_cur.'"><div><span>'.$connect_max_name['name'][$id_cur].'</span></div></li>';
                                            }
                                        echo "</ul></div>";
                                            
                                    }
                                }//end PID
                                else{//noPID
                                    ?>
                                    <div id="<?=$data_['col'][$myrow[0]];?>" class="connect_max_max" data-col_id_connect="<?=$col_id_connect;?>" >
                                        <ul>
                                            <?php
                                            if (isset($connect_max_name['name']) and count($connect_max_name['name'])>0){
                                                foreach($connect_max_name['name'] as $id=>$arr_con){
                                                    ?>
                                                    <li data-id="<?=$id;?>"><div><span><?=$arr_con;?></span></div></li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <?php
                                }//end noPID
                            }else{//MULTISELECT
                            //Автозаполнение ajax
                                ?>
                                <select data-col="<?=$myrow[0];?>"  id="<?=$data_['col'][$myrow[0]];?>" class="connect_max_max_sel" name="<?=$col_id_connect;?>" multiple data-placeholder="<?=$data_['col_ru'][$myrow[0]];?>" >
                                </select>
                                <?php
                            }
                            
                            
                        }    
                        break;
                        //***********************************************************************************
                        case "Функция":
                            
                            $file_function='include/__function_'.$inc.'_'.$data_['col'][$myrow[0]].'.php';
                            
                            if (file_exists($file_function)){
                                
                                    
                                    include $file_function;
                                
                            } else{
                                echo  'Нет файла: include/__function_'.$inc.'_'.$data_['col'][$myrow[0]].'.php';
                            }
                            
                        break;
                        //***********************************************************************************
                        case "chk":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            $chk=''; if($val_def=='1'){$chk=' checked="checked"';}
                            ?>
                            
                            <input type="checkbox" name="<?=$data_['col'][$myrow[0]];?>" <?=$chk;?> value="1"/>
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "enum":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            $sql_="SELECT `COLUMN_TYPE`
                                        FROM `information_schema`.`COLUMNS`
                                            WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                                            AND `TABLE_NAME`='"._DB($inc)."' 
                                            AND `COLUMN_NAME`='"._DB($data_['col'][$myrow[0]])."'
                                "; 
                            $res_enum = mysql_query($sql_);
                            $myrow_enum = mysql_fetch_array($res_enum);
                            $column_type=$myrow_enum[0];
                            $variants = explode (',', substr($column_type, 5, -1));
                            $option_txt='';
                            foreach ($variants as &$variant){
            				    $chk='';if (_IN(trim($variant, "'"))==$val_def){$chk=' selected="selected"';}
                                $option_txt.= '<option value="'._IN(trim($variant, "'")).'"'.$chk.'>'._IN(trim($variant, "'"))."</option>\n";
            				}
                            ?>
                            
                            <select class="enum" name="<?=$data_['col'][$myrow[0]];?>" data-placeholder="<?=$data_['col_ru'][$myrow[0]];?>">
                                <?=$option_txt;?>
                            </select>
                                
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Цвет":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            <input type="text" maxlength="6" class="color" name="<?=$data_['col'][$myrow[0]];?>" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" value="<?=$val_def;?>" />
                            <span class="color_ex"></span>
                            <?php
                            }
                        break;
                        //***********************************************************************************
                        case "Фото":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                                $photo=1;
                            }
                        break;
                         //***********************************************************************************
                        case "Ссылка":
                            if ($data_['chk_change'][$myrow[0]]=='1'){
                            ?>
                            
                            <input class="link" type="url" placeholder="<?=$data_['col_ru'][$myrow[0]];?>" name="<?=$data_['col'][$myrow[0]];?>" value="<?=$val_def;?>"/>
                                
                            <?php
                            }
                        break;
                        }//end ТИП
                        
                        if ($data_['tip'][$myrow[0]]!='Фото'){
                            if ($data_['chk_change'][$myrow[0]]=='1' or $data_['tip'][$myrow[0]]=='Функция'){
                            ?>
                                    </div>
                                </div>
                            <?php
                            }
                        }
                    }
                    //***********************************************************************************
                    // ******************* ФОТО ********************************************************
                    if ($photo==1){
                    ?>
                    <div class="ttable_tbody_tr" data-col="photo" data-tip="Фото">
                        <div class="ttable_tbody_tr_td __other__photo_td">
                            <p><span class="ico ico__left ico_change_photo"></span> <span>ФОТО</span></p>
                            
                            <div>
                                <p>Укажите степень сжатия:</p>
                                <select class="photo_compress" data-placeholder="Сжатие">
                                    <option value="1">Без сжатия</option>
                                    <option value="2">до 500*500</option>
                                    <option value="3" selected="selected">до 1000*1000</option>
                                    <option value="4">до 1500*1500</option>
                                    <option value="5">до 2000*2000</option>
                                    
                                </select>
                            </div>
                            <div>
                                <p>Обрезать фото:</p>
                                <select class="photo_crop" data-placeholder="Crop">
                                    <option value="1" selected="selected">Не обрезать</option>
                                    <option value="2">Обрезать</option>
                                    
                                </select>
                            </div>
                            <div class="__other__photo_tip_select">
                            <p>Укажите тип загружаемых изображений:</p>
                            <?php
                                $sql_="SELECT `COLUMN_TYPE`
                                            FROM `information_schema`.`COLUMNS`
                                                WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                                                AND `TABLE_NAME`='a_photo' 
                                                AND `COLUMN_NAME`='tip'
                                    "; 
                                $res_enum = mysql_query($sql_);
                                $myrow_enum = mysql_fetch_array($res_enum);
                                $column_type=$myrow_enum[0];
                                $variants = explode (',', substr($column_type, 5, -1));
                                $option_txt='';
                                foreach ($variants as &$variant){
                            	    $chk='';if (_IN(trim($variant, "'"))==$val_def){$chk=' selected="selected"';}
                                    $option_txt.= '<option value="'._IN(trim($variant, "'")).'"'.$chk.'>'._IN(trim($variant, "'"))."</option>\n";
                            	}
                            ?>
                            
                            <select class="photo_tip_ex">
                                <?=$option_txt;?>
                            </select>
                            </div>
                            
                        </div>
                        <div class="ttable_tbody_tr_td __other__photo_td">
                            <div class="drop_target">Загрузка изображений <span class="upload_photo_loading"></span></div>
                            <ul class="__other__photo_res">
                            
                            </ul>
                        </div>
                   </div>      
                   <?php
                   }
                   ?>
            </div>
        
    </div>
    <!-- -->
    

      
</div>

    <div style="clear: both;"></div>
    

    