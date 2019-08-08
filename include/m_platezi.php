<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
?>

<?php
    //админ
    $sql = "SELECT id, i_tp_id
    				FROM a_admin
    					WHERE  a_admin.email='"._DB($_SESSION['admin']['email'])."' 
                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                        
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $myrow = mysql_fetch_array($res);
    $a_admin_id=$myrow[0];
    $a_admin_i_tp_id=$myrow[1];
    
    
    
?>

<div class="i_schet_information">
    <div class="i_schet_item" data-id="-1"><span class="i_schet_item_name">[Всего]</span><span class="i_schet_item_sum"><strong>0</strong> руб.</span></div>
<?php
$sql = "SELECT id, name
    				FROM i_scheta
    					WHERE i_scheta.chk_active='1'
                        ORDER BY i_scheta.sid
 ";
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
{
    ?>
    <div class="i_schet_item" style="display:none;" data-id="<?=$myrow[0];?>"><span class="i_schet_item_name"><?=$myrow[1];?></span><span class="i_schet_item_sum"><strong>0</strong> руб.</span></div>
    <?php
}
?>
    <div class="m_platezi_pl_hide" title="Скрыть счета"></div>
<div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>
<div class="ttable2 m_platezi_first_tbl">
    <div class="ttable2_tbody_tr">
        
        <div class="ttable2_tbody_tr_td m_platezi_add_form m_platezi_add_form_close">
        <div class="for_mobile m_platezi_add_form_open_close_div m_platezi_add_form_open_div btn_gray">Открыть форму добавления платежа</div>
        
        <div class="m_platezi_add_form_div">
        <div class="m_platezi_add_form_div_div">
        <!-- Добавление платежа -->
            <div class="m_platezi_add_head">
                <h2>Добавление платежа <input type="hidden" name="nomer" value="" /></h2>
                <ul class="m_platezi_add_head_menu">
                    <li><span class="m_platezi_add_form__plus" style="display: none;"><i class="fa fa-plus"></i> Восстановить</span></li>
                    <li><span class="m_platezi_add_form__close" style="display: none;"><i class="fa fa-remove"></i> Отменить платеж</span></li>
                    <li><span class="m_platezi_add_form__clear"><i class="fa fa-eraser"></i> Новый платеж</span></li>
                    <li><span class="m_platezi_add_form__save"><i class="fa fa-save"></i> Сохранить</span></li>
                </ul>
            </div>
            <div style="clear: both;"></div>
            <form>
                <div class="ttable2 m_platezi_add_all_tbl">
                    <div class="ttable2_tbody_tr">
                        <div data-colspan="2">
                            <div class="status_order_div" style="display: none;">
                                
                            </div>
                        </div>
                    </div>
                    <div class="ttable2_tbody_tr">
                        <div class="ttable2_tbody_tr_td">
                            <div class="m_platezi_add_main_info">
                                <div class="mandat m_platezi_add_summa">
                                    <div>
                                        <span><strong>Сумма*</strong></span>
                                    </div>
                                    <div>
                                        <input type="text" name="summa" placeholder="Сумма" value="" />
                                    </div>
                                </div>
                                <div class="mandat">
                                    <div>
                                        <span>Счет*</span>
                                        <span class="fa fa-info-circle"><span>Укажите счет с которого или на который совершается платеж</span></span>
                                    </div>
                                    <div>
                                        <select name="i_scheta">
                                            <?php
                                            $sql = "SELECT  i_scheta.id,
                                                            i_scheta.name, 
                                                            
                                                            i_scheta.i_tp_id
                                                            
                                                            FROM i_scheta
                                                            
                                                            WHERE i_scheta.chk_active='1'
                                                            
                                                            ORDER BY i_scheta.sid
                                                            
                                            					
                                            	"; 
                                            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                            {
                                                ?>
                                                <option value="<?=$myrow['id'];?>" data-i_tp_id="<?=$myrow[2];?>"><?=$myrow['name'];?></option>
                                                <?php
                                            }
                                            
                                            ?>
                                        </select>
                                        <p class="m_platezi_add_schet_info"></p>
                                    </div>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                        <div class="ttable2_tbody_tr_td">
                            <div class="m_platezi_other_info">
                                <div class="mandat">
                                    <div><span>Дата*</span></div>
                                    <div><input type="text" name="date" placeholder="Дата заказа" value="<?=date('d.m.Y H:i');?>" /></div>
                                </div>
                                <div class="mandat">
                                    <div>
                                        <span>Работник*</span>
                                        <span class="fa fa-info-circle"><span>Укажите работника, кто создает данный платеж</span></span>
                                    </div>
                                    <div>
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
                                            				
                                                            FROM a_admin, a_admin_i_post, i_post
                                                            
                                                            WHERE i_post.`obj`='Заказ'
                                                            AND a_admin_i_post.id2=i_post.id
                                                            AND a_admin_i_post.id1=a_admin.id
                                                            
                                                            GROUP BY a_admin.id
                                                            
                                            					
                                            	"; 
                                            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
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
                                </div>
                                
                                
                                <div style="clear: both;"></div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="m_platezi_add_comment_div">
                   <span>Описание платежа:</span>
                   <textarea class="pl_comments_textarea" name="comments" placeholder="Комментарии"></textarea>
                </div>
                
                <div style="clear: both;"></div>
                <div class="m_platezi_add_items tabs_tip_platezi">
                    <ul>
                        
                        <li><a href="#tabs-1"><i class="fa fa-shopping-cart"></i> Заказы </a></li>
                        <li><a href="#tabs-2"><i class="fa fa-dropbox"></i> Поступления</a></li>
                        <li><a href="#tabs-3"><i class="fa fa-users"></i> З/П</a></li>
                        <li><a href="#tabs-4"><i class="fa fa-bullhorn"></i> Реклама</a></li>
                        <li><a href="#tabs-5"><i class="fa fa-calculator"></i> Расходы</a></li>
                        <li><a href="#tabs-6"><i class="fa fa-retweet"></i> Переводы</a></li>
                        <li><a href="#tabs-7"><i class="fa fa-external-link"></i> Ввод/вывод</a></li>
                        
                    </ul>
                    
                    <!-- Заказы -->
                    <div id="tabs-1">
                        <div style="width: 100%;" class="ttable2 m_platezi_zakaz_tbl">
                            <div class="ttable2_tbody_tr no_mobile">
                                <div class="ttable2_tbody_tr_td"><p>Номер заказа:</p></div>
                                <div class="ttable2_tbody_tr_td"><p>Оплата или возврат:</p></div>
                            </div>
                            <div class="ttable2_tbody_tr">
                                <div class="ttable2_tbody_tr_td m_platezi_m_zakaz_id_div">
                                    <div>
                                        <select name="m_zakaz_id" data-placeholder="№ заказа">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr_td">
                                    <select name="m_zakaz_tip" data-placeholder="Оплата или возврат">
                                        <option value="Кредит">Оплата по заказу</option>
                                        <option value="Дебет">Возврат по заказу</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ttable2_tbody_tr">
                                <div data-colspan="2">
                                    <div class="m_platezi_m_zakaz_info"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    
                    <!-- Поступление -->
                    <div id="tabs-2">
                    
                        <div style="width: 100%;" class="ttable2 m_platezi_zakaz_tbl">
                            <div class="ttable2_tbody_tr no_mobile">
                                <div class="ttable2_tbody_tr_td"><p>Номер поступления:</p></div>
                                <div class="ttable2_tbody_tr_td"><p>Оплата поступления или возврат:</p></div>
                            </div>
                            <div class="ttable2_tbody_tr">
                                <div class="ttable2_tbody_tr_td m_platezi_m_postav_id_div">
                                    <div>
                                        <select name="m_postav_id" data-placeholder="№ поступления">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr_td">
                                    <select name="m_postav_tip" data-placeholder="Тип">
                                        <option value="Дебет">Оплата поставщику</option>
                                        <option value="Кредит">Возврат от поставщика</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ttable2_tbody_tr">
                                <div data-colspan="2">
                                    <div class="m_platezi_m_postav_info"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- З/П -->
                    <div id="tabs-3">
                        <div style="width: 100%;" class="ttable2 m_platezi_zarplata">
                            <div class="ttable2_tbody_tr no_mobile">
                                <div class="ttable2_tbody_tr_td"><p>Укажите работника:</p></div>
                                <div class="ttable2_tbody_tr_td"><p>выдача з/п или внесение на счет:</p></div>
                            </div>
                            <div class="ttable2_tbody_tr">
                                <div class="ttable2_tbody_tr_td m_platezi_a_admin_id_div">
                                    <span class="m_platezi_a_admin_select">
                                        <select name="a_admin_id" data-placeholder="Работник">
                                            <option></option>
                                            <?php
                                            $sql = "SELECT  a_admin.id,
                                                            a_admin.name
                                                				FROM a_admin 
                                                					WHERE a_admin.chk_active='1'
                                                						ORDER BY id
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
                                    </span>
                                </div>
                                <div class="ttable2_tbody_tr_td">
                                    <span>
                                        <select name="a_admin_tip" data-placeholder="Тип">
                                            <option value="Дебет">Выдача з/п</option>
                                            <option value="Кредит">Внесение на счет</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="ttable2_tbody_tr no_mobile">
                                <div class="ttable2_tbody_tr_td"><p><!--Укажите должность:--></p></div>
                                <div class="ttable2_tbody_tr_td"><p></p></div>
                            </div>
                            <div class="ttable2_tbody_tr no_mobile">
                                <div class="ttable2_tbody_tr_td">
                                    <span class="m_platezi_a_admin_i_post_select">
                                        <!--<select name="a_admin_i_post" data-placeholder="[Все]">
                                            <option></option>
                                            
                                        </select>
                                        -->
                                    </span>
                                </div>
                                <div class="ttable2_tbody_tr_td">
                                    <div class="btn_gray m_platezi_a_admin_chk_zp">Расчитать зарплату</div>
                                </div>
                            </div>
                              
                                   
                                
                        </div>
                        <div class="clear"></div>
                         <div class="m_platezi_a_admin_info"></div>
                    </div>
                    
                    <!-- Реклама -->
                    <div id="tabs-4">
                        <p class="no_mobile">Реклама:</p>
                        <div>
                            <select name="i_reklama_id" data-placeholder="Реклама">
                                <option></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Расходы -->
                    <div id="tabs-5">
                        <p class="no_mobile">Расходы:</p>
                        <div>
                            <span class="m_platezi_i_rashodi_select">
                                <select name="i_rashodi_id" data-placeholder="Расходы">
                                    <option></option>
                                    <?php
                                    $sql = "SELECT  i_rashodi.id,
                                                    i_rashodi.name
                                        				FROM i_rashodi 
                                        					WHERE i_rashodi.chk_active='1'
                                        						ORDER BY id
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
                            </span>
                        </div>
                    </div>
                    
                    <!-- Переводы -->
                    <div id="tabs-6">
                        <p class="no_mobile">Счет куда переводим:</p>
                        <div>
                            <span class="i_scheta_select">
                                <select name="i_scheta_id" data-placeholder="Счет куда переводим">
                                    <option></option>
                                    <?php
                                    $sql = "SELECT  i_scheta.id,
                                                    i_scheta.name
                                        				FROM i_scheta 
                                        					WHERE i_scheta.chk_active='1'
                                        						ORDER BY id
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
                            </span>
                        </div>
                    </div>
                    
                    <!-- Ввод/вывод -->
                    <div id="tabs-7">
                    <div style="width: 100%;" class="ttable2 m_platezi_inout">
                            <div class="ttable2_tbody_tr no_mobile">
                                <div class="ttable2_tbody_tr_td"><p>Назначение ввода/вывода</p></div>
                                <div class="ttable2_tbody_tr_td"><p>Тип: ввод или вывод</p></div>
                            </div>
                            <div class="ttable2_tbody_tr">
                                <div class="ttable2_tbody_tr_td">
                                
                                <div>
                                    <span class="i_inout_select">
                                        <select name="i_inout_id" data-placeholder="Назначение ввода/вывода">
                                            <option></option>
                                            <?php
                                            $sql = "SELECT  i_inout.id,
                                                            i_inout.name
                                                				FROM i_inout
                                                					WHERE i_inout.chk_active='1'
                                                						ORDER BY id
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
                                    </span>
                                </div>
                                </div>
                        
                                <div class="ttable2_tbody_tr_td">
                                    <span>
                                        <select name="i_inout_tip" data-placeholder="Тип: ввод или вывод">
                                            <option value="Дебет">Вывод с счета</option>
                                            <option value="Кредит">Ввод на счет</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div><div style="clear: both;"></div>
            </form>
            </div>
            </div>
        </div>
        
        <!-- Все платежи -->
        <div class="ttable2_tbody_tr_td m_platezi_find">
            <h1>Платежи</h1>
            <div class="m_platezi_fillter">
                <div class="m_platezi_fillter__find_txt">
                    <input name="find_txt" placeholder="Поиск по заказу, поступлению, сумме, комментариям" type="text" />
                    <i class="fa fa-search"></i>
                    <div style="clear: both;"></div>
                </div>
                <div class="m_platezi_fillter__other_form">
                    <div class="i_scheta_find_div">
                        <?php
                        
                        
                        $sql = "SELECT COUNT(*)
                        				FROM i_scheta
                        					WHERE i_scheta.chk_active='1'
                                            
                        	"; 
                        
                        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                        $myrow = mysql_fetch_array($res);
                        $dis='';if ($myrow[0]=='0'){$dis=' disabled="disabled"';}
                        ?>
                        <select name="i_scheta_id_find" data-placeholder="Счет" <?=$dis;?>>
                            <option value="-1">[Все]</option>
                            <?php
                            
                            $sql = "SELECT id, name 
                                				FROM i_scheta
                                                    WHERE i_scheta.chk_active='1'
                                                    ORDER BY i_scheta.sid
                                					
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
                    </div>
                </div>
                <div class="m_platezi_fillter__data_in_out">
                    <input type="text" name="m_platezi_data1_find" placeholder="от" />
                    -
                    <input type="text" name="m_platezi_data2_find" placeholder="до" />
                </div>
                <div class="m_platezi_fillter__print">
                    <i class="fa fa-print" title="Кассовая книга"></i>
                </div>
                
                <div class="m_platezi_fillter__other_form">
                    <div class="i_tp_find_div">
                        
                        <select name="i_tp_id_find" data-placeholder="Филиал">
                            <?php
                            
                            $sql = "SELECT DISTINCT i_tp.id, i_tp.name
                        				FROM i_tp, i_scheta
                        					WHERE i_scheta.chk_active='1'
                                            AND i_tp.id=i_scheta.i_tp_id
                                					
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
                    </div>
                </div>
                <div style="clear: both;"></div>
                
            </div>    
            <div class="m_platezi_fillter__tip">
                <ul class="m_platezi_fillter__tip_ul">
                    <li data-id="-1" class="active">Все <span></span></li>
                    <li data-id="16"><i class="fa fa-shopping-cart"></i> Заказы <span></span></li>
                    <li data-id="17"><i class="fa fa-dropbox"></i> Поступления <span></span></li>
                    <li data-id="4"><i class="fa fa-users"></i> З/П <span></span></li>
                    <li data-id="40"><i class="fa fa-bullhorn"></i> Реклама <span></span></li>
                    <li data-id="100"><i class="fa fa-calculator"></i> Расходы <span></span></li>
                    <li data-id="42"><i class="fa fa-retweet"></i> Переводы <span></span></li>
                    <li data-id="105"><i class="fa fa-external-link"></i> Ввод/вывод <span></span></li>
                </ul>
            </div>  
            <div style="clear: both;"></div>
            
             
            <!-- Сортировка платежей -->
            <div class="m_platezi_fillter__sort">
                <ul class="m_platezi_fillter__sort_ul">
                    <li data-id="1"><i class="fa fa-calendar"></i> По дате <span></span></li>
                    <li data-id="2" class="active"><i class="fa fa-sort-numeric-desc"></i> По номеру <span></span></li>
                </ul>
            </div>
            <!-- Актиывные или удаленые -->
            <div class="m_platezi_fillter__active_remove">
                <ul class="m_platezi_fillter__active_remove_ul">
                    <li data-id="1" class="active"><i class="fa fa-plus"></i> Активные <span></span></li>
                    <li data-id="2"><i class="fa fa-remove"></i> Удаленые <span></span></li>
                </ul>
            </div> 
            
            <!-- Под итоги -->
            <div class="m_platezi_find_res_info">
                <div class="phihod_sum">Итого приход: <span>0</span> руб.</div>
                <div class="rashod_sum">Итого расход: <span>0</span> руб.</div>
                <div class="ishod_ostatok">Итого сальдо: <span>0</span> руб.</div>
            </div>
            <div style="clear: both;"></div>
            <!-- Результат -->
            <div class="m_platezi_find_res">
                
            </div>
            <div class="m_platezi_find_res_loading">
                
            </div>
        </div>
        
    </div>
</div>