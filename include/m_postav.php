<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>

<?php

$s_cat_tip=array();
$sql = "SELECT DISTINCT s_cat.tip
				FROM s_cat
	";
$res = mysql_query($sql);if (!$res){echo $sql;exit();}
for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
{
    $s_cat_tip[]=$myrow[0];
}

?>

<div class="m_postav_first_tbl ttable2">
    <div class="ttable2_tbody_tr">
        
        <div class="ttable2_tbody_tr_td m_postav_add_form m_postav_add_form_close">
        <div class="for_mobile m_postav_add_form_open_close_div m_postav_add_form_open_div btn_gray">Открыть форму добавления поступления</div>
        <div class="m_postav_add_form_div">
        <div class="m_postav_add_form_div_div">
        <!-- Добавление поступления -->
            <div class="m_postav_add_head">
                <h2>Добавление поступления <input type="hidden" name="nomer" value="" /></h2>
                <ul class="m_postav_add_head_menu">
                    <li><span class="m_postav_add_form__clone"><i class="fa fa-copy" title="Клонировать поступление"></i></span></li>
                    <li><span class="m_postav_add_form__plus" style="display: none;"><i class="fa fa-plus"></i> Открыть</span></li>
                    <li><span class="m_postav_add_form__close" style="display: none;"><i class="fa fa-remove"></i> Отменить</span></li>
                    <li><span class="m_postav_add_form__clear"><i class="fa fa-eraser"></i> Очистить</span></li>
                    <li><span class="m_postav_add_form__save"><i class="fa fa-save"></i> Сохранить</span></li>
                </ul>
                <div style="clear: both;"></div>
            </div>
            <div style="clear: both;"></div>
            <form>
                <div class="status_order_div" style="display: none;"></div>
                <div class="ttable2 m_postav_add_all_info">
                
                    <div class="ttable2_tbody_tr">
                        <div class="ttable2_tbody_tr_td">
                            <div class="m_postav_add_main_info">
                                <div class="mandat">
                                    <div>
                                        <span>Работник*</span>
                                        <span class="fa fa-info-circle"><span>Укажите работника ответственного за поступление</span></span>
                                    </div>
                                    <div class="m_postav_add_main_a_admin">
                                        <div class="m_postav_add_main_a_admin_select">
                                        <select name="a_admin">
                                            <?php
                                            $sql = "SELECT IF(COUNT(*)>0,a_admin.id,'')
                                            				FROM a_admin 
                                            					WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."' 
                                            					AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                                                AND a_admin.chk_active='1'
                                            	"; 
                                            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                                            $myrow = mysql_fetch_array($res);
                                            $a_admin_id=$myrow[0];
                                            
                                            
                                            $sql = "SELECT  a_admin.id,
                                                            a_admin.name,
                                                            a_admin.chk_active,
                                                            a_admin.i_tp_id,
                                                            (SELECT IF(COUNT(*)>0,i_tp.name,'') FROM i_tp WHERE i_tp.id=a_admin.i_tp_id) AS i_tp_name,
                                                            (SELECT IF(COUNT(*)>0,GROUP_CONCAT(a_admin_i_post.id2 SEPARATOR ','),'') FROM a_admin_i_post WHERE a_admin_i_post.id1=a_admin.id) AS i_post_id
                                            				
                                                            FROM a_admin 
                                            					
                                            	"; 
                                            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                                            $i_tp_id='';
                                            $i_tp_name='';
                                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                            {
                                                $sel_='';if ($a_admin_id==$myrow[0]){$sel_=' selected="selected"';$i_tp_id=$myrow['i_tp_id'];$i_tp_name=$myrow['i_tp_name'];}
                                                ?>
                                                <option data-i_tp_id="<?=$myrow['i_tp_id'];?>" data-i_tp_name="<?=_IN($myrow['i_tp_name']);?>" value="<?=$myrow['id'];?>" data-chk_active="<?=$myrow['chk_active'];?>" data-i_post_id="<?=$myrow['i_post_id'];?>"<?=$sel_;?>><?=$myrow['name'];?></option>
                                                <?php
                                            }
                                            
                                            ?>
                                        </select>
                                        </div>
                                        <span class="i_tp_span"><span title="Укажите филиал для данного поступления" class="i_tp_span_cur" data-id="<?=$i_tp_id;?>"><span><?=$i_tp_name;?></span></span>
                                            <select class="i_tp_span_select" style="display: none;">
                                            <?php
                                                    
                                            $sql_i_tp = "SELECT i_tp.id, i_tp.chk_active, i_tp.name
                                                				FROM i_tp
                                             ";
                                            $res_i_tp = mysql_query($sql_i_tp) or die(mysql_error().'<br/>'.$sql_i_tp);
                                            for ($myrow_i_tp = mysql_fetch_array($res_i_tp); $myrow_i_tp==true; $myrow_i_tp = mysql_fetch_array($res_i_tp))
                                            {
                                                //print_rf($myrow_i_tp);
                                                $dis_='';//if ($myrow_i_tp['chk_active']=='0'){ $dis_=' disabled="disabled"';}
                                                
                                                ?>
                                                <option value="<?=$myrow_i_tp['id'];?>"<?=$dis_;?>><?=$myrow_i_tp['name'];?></option>
                                                <?php
                                            }
                                            
                                            ?>
                                        </select></span>
                                    </div>
                                </div>
                                <div class="mandat">
                                    <div class="m_postav_i_contr_tip_div">
                                        <span>Поставщик*</span>
                                        <span class="fa fa-info-circle"><span>Укажите контрагента, или добавьте нового</span></span>
                                        <span class="m_postav_i_contr_tip">
                                            <span data-id="1" class="i_contr_tip_1 active" style="display: none;"><i class="fa fa-user"></i> Физ. лицо</span>
                                            <span data-id="2" class="i_contr_tip_2" style="display: none;"><i class="fa fa-building"></i> Организация</span>
                                        </span>
                                    </div>
                                    <div class="m_postav_i_contr_txt"><input type="text" data-id="" name="i_contr" placeholder="Поставщик" /><span class="i_contr_com"><span class="fa fa-plus" title="Добавить нового контрагента"></span></span></div>
                                    <div class="i_contr_add_form_info"><span class="i_contr_add_form_phone"></span><span class="i_contr_add_form_email"></span></div>
                                </div>
                                
                                <div class="clear"></div>
                                <div class="m_postav_add_comments"><p class="for_mobile">Комментарии:</p><textarea name="comments" class="m_postav_add__comments" placeholder="Комментарии к поступлению"></textarea></div>
                        
                            </div>
                        </div>
                        <div class="ttable2_tbody_tr_td">
                        <div class="m_postav_other_info">
                            <div class="mandat">
                                <div><span>Дата*</span></div>
                                <div><input type="text" name="date" placeholder="Дата формирования поступления" value="<?=date('d.m.Y H:i');?>" /></div>
                            </div>
                            <div>
                                <div><span>На склад</span></div>
                                <div class="m_postav_other_info_time">
                                    <span class="time_hash" data-time="1">Сейчас</span>
                                    <span class="time_hash" data-time="0"><i class="fa fa-remove"></i></span>
                                    <span class="m_postav_auto_barcode" title="Назначить штрих-коды автоматически"><i class="fa fa-barcode"></i></span>
                                </div>
                                <div><input type="text" name="date_info" placeholder="Дата поступления на склад" /></div>
                            </div>
                            <div class="clear"></div>
                            <div class="m_postav_div_info_main">
                                <?php 
                                if ($_SESSION['a_options']['Поступление: размещение: Номер поступления']=='Основной блок'){
                                ?>
                                <p>Номер поступления:</p>
                                <div><input type="text" class="m_postav_add__num" name="control_num" placeholder="№ поступления" value="" /></div>
                                <div style="clear: both;"></div>
                                <?php
                                }
                                if ($_SESSION['a_options']['Поступление: размещение: Сумма поступления']=='Основной блок'){
                                
                                ?>
                                <p>Сумма поступления:</p>
                                <div><input type="text" class="m_postav_add__sum" name="control_sum" placeholder="Сумма поступления" value="" /></div>
                                <div style="clear: both;"></div>
                                <?php
                                }
                                ?>
                            </div>
                        
                            
                        </div>
                        </div>
                    </div>
                </div>
                
                <div style="clear: both;"></div>
                
                <!-- Документы -->
                <div class="m_postav_all_info_icon_div">
                    <div class="m_postav_all_info_icon" style="display: none;">
                        <ul>
                        <?php
                        
                            $sql = "SELECT i_docs.name,i_docs.file_name
                                				FROM i_docs 
                                					WHERE a_menu_id='17'
                                                    AND chk_active='1'
                             ";
                            $mt = microtime(true);
                            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                            {
                                ?>
                                <li><a target="_blank" data-href="?inc=i_docs&com=print&file_name=<?=$myrow['file_name'];?>&nomer=" href=""><i class="fa fa-print"></i> <?=$myrow['name'];?></a></li>
                                <?php
                            }
                        
                        ?>
                        </ul>
                        <div class="clear"></div>
                    </div>
                </div>
                <div style="clear: both;"></div>
                
                <div style="clear: both;"></div>
                <div class="m_postav_add_items tabs_items_work">
                    <ul>
                        <?php
                        $dis1='display:none;';if (in_array('Товар',$s_cat_tip)){ $dis1='';}
                        ?>
                        <li class="tabs1_li" style="<?=$dis1;?>"><a href="#tabs-1"><i class="fa fa-barcode"></i> Товары <span class="m_postav_add_items__all_tovar"></span><p class="m_postav_add_items__all_sum"></p></a></li>
                        <?php
                        
                        ?>
                        
                        
                        <li><a href="#tabs-3"><i class="fa fa-calculator"></i> Платежи <span class="m_postav_add_items__pl_kol"></span><p class="m_postav_add_work__pl_sum"></p></a></li>
                        <li><a href="#tabs-4"><i class="fa fa-info-circle"></i> Инфо</a></li>
                        <li><a href="#tabs-5"><i class="fa fa-truck"></i> Доставка  <span class="m_postav_add_items__dost_st"></span><p class="m_postav_add_work__dost_sum"></p></a></a></li>
                    </ul>
                    
                    
                    <!-- ТОВАР -->
                    <div id="tabs-1" style="<?=$dis1;?>">
                        <div class="find_tovar__block">
                            <div class="find_tovar">
                                <input name="s_cat_items_find" class="s_cat_items_find" type="text" placeholder="Найти товар..." />
                                <i class="fa fa-search"></i>
                                <p> из каталога:</p>
                                <div class="find_tovar_select">
                                    <select name="s_struktura_s_cat_select">
                                        <option value="-1">[Все]</option>
                                    <?php
                                    $sql = "SELECT DISTINCT s_struktura.id, s_struktura.name
                                    				FROM s_struktura, s_cat_s_struktura, s_cat
                                    					WHERE s_struktura.id =s_cat_s_struktura.id2
                                                        AND s_cat_s_struktura.id1=s_cat.id
                                                        AND s_cat.tip='Товар'
                                    	"; 
                                    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                    {
                                        ?>
                                        <option value="<?=$myrow[0];?>"><?=$myrow[1];?></option>
                                        <?php
                                    }
                                    ?>
                                    </select>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="find_tovar_res_all_info">Выбрано <strong class="find_tovar_all_kol"></strong> товаров, на сумму <strong class="find_tovar_all_sum"></strong> руб.</div>
                        </div>
                        <div class="find_tovar_res">
                            <div class="find_tovar_res__tbl ttable">
                                <div class="ttable_thead">
                                    <div class="ttable_thead_tr">
                                        <div class="ttable_thead_tr_th">№</div>
                                        <div class="ttable_thead_tr_th">Наименование</div>
                                        <div class="ttable_thead_tr_th">Кол-во</div>
                                        <div class="ttable_thead_tr_th">Цена</div>
                                        <div class="ttable_thead_tr_th">Итого</div>
                                        <div class="ttable_thead_tr_th">Функции</div>
                                    </div>
                                </div>
                                <div class="ttable_tbody">
                                
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <!-- Быстрый поиск товаров -->
                         <div class="find_tovar_res_help"></div>
                    </div>
                   
                    
                    
                    <div id="tabs-3">
                        <div class="m_postav_add_pl__add_div">
                            <div class="ttable2">
                                <div class="ttable2_tbody_tr">
                                    <div class="ttable2_tbody_tr_td">
                                        <p>Дата:</p>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <span><input type="text" class="pl_data" value="<?=date('d.m.Y H:i');?>" /></span>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>Сумма:</p>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <span><input type="price" class="pl_price" value="" /></span>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>Счет:</p>
                                    </div>
                                    <div class="ttable2_tbody_tr_td pl_schet_select_div">
                                        <span>
                                            <select class="pl_schet">
                                                <?php
                                                $sql = "SELECT i_scheta.id, i_scheta.name, i_scheta.i_tp_id
                                                    				FROM i_scheta 
                                                    					WHERE i_scheta.chk_active='1'
                                                                        ORDER BY i_scheta.sid
                                                 ";
                                                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                                {
                                                    ?>
                                                    <option value="<?=$myrow['id'];?>" data-i_tp_id="<?=$myrow[2];?>"><?=$myrow['name'];?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </span>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <span class="btn_gray m_postav_add_pl__add_com">Добавить</span>
                                    </div>
                                </div>
                            </div>
                            
                            
                        </div>
                        <div style="clear: both;"></div>
                        <div class="pl_all_info">Совершено <strong class="pl_all_kol">0</strong> платеж<span class="pl_end_word">ей</span>, на сумму <strong class="pl_all_sum">0</strong> руб.</div>
                        <div>
                        <div class="ttable m_postav_add_pl">
                            <div class="ttable_thead">
                                <div class="ttable_thead_tr">
                                    <div class="ttable_thead_tr_th">Дата</div>
                                    <div class="ttable_thead_tr_th">Сумма, руб.</div>
                                    <div class="ttable_thead_tr_th">Счет</div>
                                    <div class="ttable_thead_tr_th"></div>
                                </div>
                            </div>
                            <div class="ttable_tbody">
                            </div>
                        </div>
                        </div>
                    </div>
                    <div id="tabs-4">
                        <div class="m_postav_div_info">
                            <div class="m_postav_div_info_td">
                                
                                <p>Название поступления:</p>
                                <div><input type="text" class="m_postav_add__project_name" name="project_name" placeholder="Поступление" value="Поступление №@@id@@" /></div>
                                <div style="clear: both;"></div>
                            </div>
                            <?php
                            if ($_SESSION['a_options']['Поступление: размещение: Номер поступления']=='Блок инфо' or $_SESSION['a_options']['Поступление: размещение: Сумма поступления']=='Блок инфо' ){
                            ?>
                            <div class="m_postav_div_info_td">
                                <?php 
                                if ($_SESSION['a_options']['Поступление: размещение: Номер поступления']=='Блок инфо'){
                                ?>
                                <p>Номер поступления:</p>
                                <div><input type="text" class="m_postav_add__num" name="control_num" placeholder="№ поступления" value="" /></div>
                                <div style="clear: both;"></div>
                                <?php
                                }
                                if ($_SESSION['a_options']['Поступление: размещение: Сумма поступления']=='Блок инфо'){
                                
                                ?>
                                <p>Сумма поступления:</p>
                                <div><input type="text" class="m_postav_add__sum" name="control_sum" placeholder="Сумма поступления" value="" /></div>
                                <div style="clear: both;"></div>
                                <?php
                                }
                                ?>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
                        
                        <div class="m_postav_div_docs">
                            <p>Документация:</p>
                            <div class="m_postav_add__docs_load">
                                <span class="btn_gray m_postav_add__html_code"><i class="fa fa-exclamation-circle"></i> Подробная информация</span>
                                <span class="btn_gray" id="m_postav_add__docs_load_com"><i class="fa fa-download"></i> Загрузить документы</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="m_postav_add__docs_res">
                                <ul></ul>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                        <div class="m_postav_add__html_code_hide" style="display: none;"></div>
                        <div class="clear"></div>
                        
                        <!-- Сообщения -->
                        <div class="m_postav_div_mess">
                            <p>Отправить сообщение:</p>
                            <div class="m_postav_send_mess_form">
                                <div class="ttable2">
                                    <div class="ttable2_tbody_tr">
                                        <div class="ttable2_tbody_tr_td"><textarea id="m_postav_mess_text" class="m_postav_mess_text" placeholder="Текст сообщения"></textarea></div>
                                        <div class="ttable2_tbody_tr_td">
                                            <div><span class="btn_gray m_postav_add_file" id="upload_button"><i class="fa fa-download"></i> Загрузить</span></div>
                                            <div><span class="btn_orange m_postav_send_mess"><i class="fa fa-envelope"></i> Отправить</span></div>
                                        </div>
                                    </div>
                                    <div class="ttable2_tbody_tr">
                                        <div class="ttable2_tbody_tr_td">
                                            <div class="m_postav_mess_files">
                                                <div class="loading_file"></div>
                                                <ul>
                                                
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="ttable2_tbody_tr_td"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="m_postav_all_mess">
                                <ul></ul>
                            </div>
                            <div class="clear"></div>
                        </div>
                        
                        <!-- Сообщения -->
                        <div class="m_postav_div_logs">
                            <p>Логи:</p>
                            <div class="m_postav_log_text"></div>
                        </div>
                    </div>
                    <div id="tabs-5">
                        <div class="m_postav__dostavka_div">
                            <div class="bold_">
                                <p>Номер отправления</p>
                                <div>
                                    <input type="text" name="m_dostavka_tracking_number" placeholder="Номер отправления" />
                                </div>
                            </div>
                            <div class="bold_">
                                <p>Дата отправления</p>
                                <div>
                                    <input type="text" name="m_dostavka_data" placeholder="Дата отправления" />
                                </div>
                            </div>
                            <div class="bold_">
                                <p>Транспортная компания</p>
                                <div>
                                    <select name="i_tk_id">
                                        <option></option>
                                        <?php
                                        $sql = "SELECT i_tk.id, i_tk.name
                                            				FROM i_tk 
                                            					WHERE i_tk.chk_active='1'
                                         ";
                                        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                        {
                                            ?>
                                            <option value="<?=$myrow['id'];?>"><?=$myrow['name'];?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <a href="?inc=i_tk" target="_blank">Добавить ТК</a>
                                </div>
                            </div>
                            <hr />
                            <div>
                                <p>Ф.И.О. отправителя</p>
                                <div>
                                    <input type="text" name="m_dostavka_fio" placeholder="ФИО отправителя" />
                                </div>
                            </div>
                            <div>
                                <p>Город доставки</p>
                                <div>
                                    <select name="m_dostavka_city_id">
                                    </select>
                                </div>
                            </div>
                            <div>
                                <p>Адрес отправителя</p>
                                <div>
                                    <textarea name="m_dostavka_adress" placeholder="Адрес отправителя"></textarea>
                                </div>
                            </div>
                            <div>
                                <p>Индекс отправителя</p>
                                <div>
                                    <input type="text" name="m_dostavka_index" placeholder="Индекс отправителя" maxlength="6" />
                                </div>
                            </div>
                            <div>
                                <p>Телефон отправителя</p>
                                <div>
                                    <input type="text" name="m_dostavka_phone" placeholder="Телефон отправителя" />
                                </div>
                            </div>
                            <div>
                                <p>Стоимость доставки, руб.</p>
                                <div>
                                    <input type="text" name="m_dostavka_summa" placeholder="Стоимость доставки" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </form>
            
            
            <div class="m_postav_add_head for_mobile">
                <div class="clear"></div>
                <ul class="m_postav_add_head_menu">
                    <li><span class="m_postav_add_form__plus" style="display: none;"><i class="fa fa-plus"></i> Открыть</span></li>
                    <li><span class="m_postav_add_form__close" style="display: none;"><i class="fa fa-remove"></i> Отменить</span></li>
                    <li><span class="m_postav_add_form__clear"><i class="fa fa-eraser"></i> Очистить</span></li>
                    <li><span class="m_postav_add_form__save"><i class="fa fa-save"></i> Сохранить</span></li>
                </ul>
            </div>
            
            </div>
            </div>
        </div>
        <div class="m_postav_find ttable_tbody_tr_td2">
            <h1>Поступление товаров</h1>
            <div class="m_postav_fillter">
                <div class="m_postav_fillter__find_txt">
                    <input name="find_txt" placeholder="Поиск по контрагенту, исполнителю, товару/услуге, штрих-коду" type="text" value="<?=_IN(_GP('nomer'));?>" />
                    <i class="fa fa-search"></i>
                    <div style="clear: both;"></div>
                </div>
                
                <div class="m_postav_fillter__data_in_out">
                    <input type="text" name="m_postav_data1_find" placeholder="от" />
                    -
                    <input type="text" name="m_postav_data2_find" placeholder="до" />
                </div>
                <div class="clear display_none"></div>
                <div class="m_postav_fillter__tip display_none">
                    <ul>
                       
                        <li data-val="В обработке"><i class="fa fa-clock-o"></i> В обработке</li>
                        <li data-val="Оплачен"><i class="fa fa-rub"></i> Оплачен</li>
                        <li data-val="Отправлен"><i class="fa fa-truck"></i> Отправлен</li>
                        <li data-val="Доставлен"><i class="fa fa-check-circle-o"></i> Доставлен</li>
                        <li data-val="Отменен"><i class="fa fa-minus"></i> Отменен</li>
                    </ul>
                </div>
                <div class="m_postav_fillter__sklad_or_zakaz display_none">
                    <ul>
                        <li data-val="1"><i class="fa fa-cube"></i> На склад</li>
                        <li data-val="2"><i class="fa fa-user"></i> Под заказ</li>
                    </ul>
                </div>
                <div class="m_postav_fillter__control_num display_none">
                    <input name="control_num_find" placeholder="Номер накладной" type="text" value="" />
                    <i class="fa fa-search"></i>
                    <div style="clear: both;"></div>
                </div>
                <div class="clear display_none"></div>
                <div class="m_postav_fillter__sort">
                    <span>Сортировать:</span>
                    <ul>
                        <li data-val="1" class="active"><i class="fa fa-calendar"></i> По дате создания</li>
                        <li data-val="2"><i class="fa fa-calendar-o"></i> По дате поступления</li>
                    </ul>
                </div>
                <div class="view_all_fillter view_all_fillter_inherit btn_gray">Открыть фильтр</div>
                <div style="clear: both;"></div>
            </div>
            <!-- Результат -->
            <div class="m_postav_find_res">
                
            </div>
        </div>
    </div>
</div>