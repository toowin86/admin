<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
  
?>
<div class="top_com">
    <ul>
        <li style="display: none;"><div class="top_com__m_zakaz_close" title="Отменить выбранные заказы"></div></li>
        <li style="display: none;"><div class="top_com__m_zakaz_start" title="Открыть выбранные заказы"></div></li>
        <li style="display: none;"><div class="top_com__m_zakaz_marshrut" title="Маршрутный лист"></div></li>
        <li style="display: none;"><div class="top_com___loading loading38" ></div></li>
        <li style="display: none;"><div class="top_com___loading_s loading_s38" ></div></li>
        <li style="display: none;"><div class="top_com___loading_f loading_f38" ></div></li>
        
    </ul>
</div>
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

//сервис
$sql = "SELECT COUNT(*)
				FROM r_service 
	"; 
$res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
$myrow = mysql_fetch_array($res);
$cnt_service=$myrow[0];

?>

<div class="ttable2 m_zakaz_first_tbl">
    <div class="ttable2_tbody_tr">
        
        <div class="ttable2_tbody_tr_td m_zakaz_add_form m_zakaz_add_form_close">
        <div class="for_mobile m_zakaz_add_form_open_close_div m_zakaz_add_form_open_div btn_gray">Открыть форму добавления заказа</div>
        <div class="m_zakaz_add_form_div_div">
            <div class="m_zakaz_add_form_div">
            <!-- Добавление заказа -->
            <div class="m_zakaz_add_head">
                <h2>Добавление заказа <input type="hidden" name="nomer" value="" /></h2>
                <ul class="m_zakaz_add_head_menu">
                    <li><span class="m_zakaz_add_form__clone"><i class="fa fa-copy" title="Клонировать заказ"></i></span></li>
                    <li><span class="m_zakaz_add_form__plus" style="display: none;"><i class="fa fa-plus"></i> Открыть заказ</span></li>
                    <li><span class="m_zakaz_add_form__close" style="display: none;"><i class="fa fa-remove"></i> Отменить заказ</span></li>
                    <li><span class="m_zakaz_add_form__clear"><i class="fa fa-eraser"></i> Новый заказ</span></li>
                    <li><span class="m_zakaz_add_form__save"><i class="fa fa-save"></i> Сохранить</span></li>
                </ul>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <form>
                <!-- Звонок -->
                <p class="c_call_client_info_div"></p>
                <input type="hidden" name="c_call_client_id" value="" />
                <div class="status_order_div" style="display: none;"></div>
                <div class="ttable2">
                    <div class="ttable2_tbody_tr">
                        <div class="ttable2_tbody_tr_td m_zakaz_add_td_first">
                            <div class="m_zakaz_add_main_info">
                                
                                <div class="mandat">
                                    <div class="m_zakaz_i_contr_tip_div">
                                        <span>Покупатель*</span>
                                        <span class="fa fa-info-circle"><span>Укажите контрагента, или добавьте нового</span></span>
                                        <span class="m_zakaz_i_contr_tip">
                                            <span data-id="1" class="i_contr_tip_1 active" style="display: none;"><i class="fa fa-user"></i> Физ. лицо</span>
                                            <span data-id="2" class="i_contr_tip_2" style="display: none;"><i class="fa fa-building"></i> Организация</span>
                                        </span>
                                    </div>
                                    <div class="m_zakaz_i_contr_txt"><input type="text" data-id="" name="i_contr" placeholder="Покупатель" /><span class="i_contr_com"><span class="fa fa-plus" title="Добавить нового контрагента"></span></span></div>
                                    <div class="i_contr_add_form_info"><span class="i_contr_add_form_phone"></span><span class="i_contr_add_form_email"></span></div>
                                </div>
                                
                                <!-- Ответстввенный -->
                                <div>
                                    <div>
                                        <span>Ответственный*</span>
                                        <span class="fa fa-info-circle"><span>Укажите работника ответственного за данный заказ</span></span>
                                    </div>
                                    <div>
                                        <select name="a_admin_otvet" data-placeholder="Ответственный">
                                            <option value=""></option>
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
                                                            
                                                            WHERE  a_admin_i_post.id2=i_post.id
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
                                
                                <!-- Напомнить -->
                                <div>
                                    <div><span>Напоминание (дедлайн)</span></div>
                                    <div class="m_zakaz_other_info_time">
                                        <span class="time_hash" data-time="3600">1 час</span>
                                        <span class="time_hash" data-time="86400">1 день</span>
                                        <span class="time_hash" data-time="2592000">1 месяц</span>
                                        <span class="time_hash" data-time="0"><i class="fa fa-remove"></i></span>
                                    </div>
                                    <div class="date_info_div_div"><input type="text" name="date_info" placeholder="Дата напоминания" /></div>
                                    <div class="clear"></div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="ttable2_tbody_tr_td m_zakaz_add_td_last">
                        <div class="m_zakaz_other_info">
                            <div class="mandat">
                                <div><span>Дата*</span></div>
                                <div><input type="text" name="date" placeholder="Дата заказа" value="<?=date('d.m.Y H:i');?>" /></div>
                            </div>
                            
                            <div class="mandat">
                                    <div>
                                        <span>Работник*</span>
                                        <span class="fa fa-info-circle"><span>Укажите работника принявшего данный заказ</span></span>
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
                                        <span class="i_tp_span"><span title="Укажите филиал для данного заказа" class="i_tp_span_cur" data-id="<?=$i_tp_id;?>"><span><?=$i_tp_name;?></span></span>
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
                            <div class="clear"></div>
                            <div class="m_zakaz_add_comments">   
                                <p class="for_mobile">Комментарии:</p>
                                <div><textarea name="comments" class="m_zakaz_add__comments" placeholder="Комментарии к заказу"></textarea></div>
                            </div>
                            <div class="clear"></div>
                           
                            
                            <div class="m_zakaz_add_dostav_chk">
                                <p><label><input type="checkbox" class="m_dostavka_chk_active" name="m_dostavka_chk_active" placeholder="В доставку" /> <i class="fa fa-truck"></i> В доставку</label></p>
                            </div>
                            <div class="clear"></div>
                        </div>
                        </div>
                    </div>
                    
                </div>
                <div class="clear"></div>
                
                <!-- Документы -->
                <div class="m_zakaz_all_info_icon" style="display: none;">
                    <ul>
                    <?php
                    
                        $sql = "SELECT i_docs.name,i_docs.file_name
                            				FROM i_docs 
                            					WHERE a_menu_id='16'
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
                <div class="clear"></div>
                <div class="m_zakaz_add_items tabs_items_work">
                    <ul>
                        <?php
                        $sql = "SELECT  COUNT(*)
                        				FROM a_admin_a_menu, a_menu 
                        					WHERE a_admin_a_menu.id1='"._DB($a_admin_id)."'
                                            AND a_admin_a_menu.id2=a_menu.id
                                            AND a_menu.inc='remont'
                                            AND a_menu.chk_active='1'
                        "; 
                        $res = mysql_query($sql);
                        $myrow = mysql_fetch_array($res);
                        $service_chk=$myrow[0];
                        if ($service_chk>0){
                            ?>
                            <li><a href="#tabs-7"><i class="fa fa-cogs"></i> Сервис</a></li>
                            <?php
                        }
                        ?>
                        
                        <?php
                        
                        
                        $dis1='display:none;';if (in_array('Товар',$s_cat_tip)){ $dis1='';}
                        ?>
                        <li class="tabs1_li" style="<?=$dis1;?>"><a href="#tabs-1"><i class="fa fa-barcode"></i> Товары <span class="m_zakaz_add_items__all_tovar"></span><p class="m_zakaz_add_items__all_sum"></p></a></li>
                        
                        <?php
                        $dis2='display:none;';if (in_array('Услуга',$s_cat_tip)){ $dis2='';}
                        
                        ?>
                        <li class="tabs2_li" style="<?=$dis2;?>"><a href="#tabs-2"><i class="fa fa-user"></i> Услуги <span class="m_zakaz_add_items__all_work"></span><p class="m_zakaz_add_work__all_sum"></p></a></li>
                        <?php
                        
                        ?>
                        
                        <li><a href="#tabs-3"><i class="fa fa-calculator"></i> Платежи <span class="m_zakaz_add_items__pl_kol"></span><p class="m_zakaz_add_work__pl_sum"></p></a></li>
                        <li><a href="#tabs-4"><i class="fa fa-info-circle"></i> Инфо</a></li>
                        <li class="m_dostavka_li"><a href="#tabs-5"><i class="fa fa-truck"></i> Доставка  <span class="m_zakaz_add_items__dost_st"></span><p class="m_zakaz_add_work__dost_sum"></p></a></li>
                        
                    </ul>
                    <?php
                    
                    if ($service_chk>0){
                        ?>
                        <!-- СЕРВИС -->
                        <div id="tabs-7">
                            <div class="ttable2 m_zakaz_r_service_tbl">
                                <div class="ttable2_tbody_tr">
                                    <div class="ttable2_tbody_tr_td"><p>Статус:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <!-- Статус оборудования -->
                                        <div class="m_zakaz_r_service_div">
                                            <select name="r_status" data-placeholder="Статус" data-data_inform="">
                                                <option></option>
                                                <?php
                                                $sql_="SELECT `COLUMN_TYPE`
                                                            FROM `information_schema`.`COLUMNS`
                                                                WHERE `TABLE_SCHEMA`='"._DB($base_name)."' 
                                                                AND `TABLE_NAME`='r_service' 
                                                                AND `COLUMN_NAME`='status'
                                                    "; 
                                                $res_enum = mysql_query($sql_);
                                                $myrow_enum = mysql_fetch_array($res_enum);
                                                $column_type=$myrow_enum[0];
                                                $variants = explode (',', substr($column_type, 5, -1));
                                                $option_txt='';
                                                foreach ($variants as &$variant){
                                				    echo '<option value="'._IN(trim($variant, "'")).'">'._IN(trim($variant, "'"))."</option>\n";
                                				}
                                                ?>
                                            </select>
                                        </div>
                                        
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <span class="m_zakaz_r_service_status_info"></span>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p><strong>Тип*</strong>:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <!-- Тип оборудования -->
                                        <div class="m_zakaz_r_service_div">
                                            <select name="r_tip_oborud" data-placeholder="Тип оборудования">
                                                <option></option>
                                                <?php
                                                $tip_pop_txt='';
                                                $sql = "SELECT  r_tip_oborud.id,
                                                                r_tip_oborud.name,
                                                                (SELECT COUNT(*) FROM r_model WHERE r_model.r_tip_oborud_id=r_tip_oborud.id) AS cnt_
                                                                
                                                    				FROM r_tip_oborud 
                                                    					WHERE r_tip_oborud.chk_active='1'
                                                                        ORDER BY cnt_ DESC, r_tip_oborud.id
                                                 ";
                                                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                                for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
                                                {
                                                    if ($i<3){
                                                        $tip_pop_txt.='<label class="r_tip_oborud_label" data-id="'._IN($myrow['id']).'">'.$myrow['name'].'</label>';
                                                    }
                                                    ?>
                                                    <option value="<?=$myrow[0];?>" data-val="<?=_IN($myrow[1]);?>"><?=$myrow[1];?> (<?=$myrow['cnt_'];?>)</option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>
                                        <span class="m_zakaz_r_service_quick_span quick_span">
                                            <?=$tip_pop_txt;?>
                                        </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p>Бренд:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <!-- Бренд оборудования -->
                                        <div class="m_zakaz_r_service_div">
                                            <select name="r_brend" data-placeholder="Бренд оборудования">
                                            
                                            </select>
                                        </div>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>
                                        <span class="m_zakaz_r_service_quick_span_auto quick_span">
                                            
                                        </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p>Модель:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <!-- Модель оборудования -->
                                        <div class="m_zakaz_r_service_div">
                                            <input name="r_model" placeholder="Модель оборудования" type="text" />
                                            
                                        </div>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>
                                        <span class="m_zakaz_r_service_quick_span_input quick_span">
                                                
                                        </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p>Комплектация:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <input type="text" name="komplekt" placeholder="Комплектация" />
                                    </div>
                                    <div class="ttable2_tbody_tr_td"></div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p>Неисправности:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <input class="r_neispravnosti" type="text" placeholder="Неисправность" />
                                        <div class="m_zakaz_r_neispravnosti_res"></div>
                                        
                                        
                                        <!-- 
                                        <select name="r_neispravnosti" data-placeholder="Неисправности" multiple>
                                        
                                        
                                        <?php
                                        $sql = "SELECT  r_neispravnosti.id,
                                                        r_neispravnosti.name,
                                                        (SELECT COUNT(r_service_r_neispravnosti.id1) FROM r_service_r_neispravnosti WHERE r_service_r_neispravnosti.id2=r_neispravnosti.id) AS cnt_
                                            				FROM r_neispravnosti 
                                            					WHERE r_neispravnosti.chk_active='1'
                                            						ORDER BY cnt_ DESC
                                         ";
                                        $mt = microtime(true);
                                        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                            {
                                            ?>
                                            <option value="<?=$myrow['id'];?>"><?=$myrow['name'];?></option>
                                            <?php
                                            }
                                        ?>
                                        </select>
                                        -->
                                    </div>
                                    <div class="ttable2_tbody_tr_td"></div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p>Состояние:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <input type="text" name="sost" placeholder="Состояние" value="" />
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>
                                            <span class="m_zakaz_r_service_quick_span quick_span">
                                            <?php
                                            
                                            $sql = "SELECT r_service.sost, COUNT(r_service.id) AS cnt_
                                            				FROM r_service 
                                            				    GROUP BY r_service.sost
                                            						ORDER BY cnt_ DESC
                                            ";
                                             
                                            $mt = microtime(true);
                                            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                                            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                            
                                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                            {
                                                ?>
                                                <label data-id="<?=_IN($myrow['sost']);?>"><?=$myrow['sost'];?></label>
                                                <?php
                                            }
                                            
                                            ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td"><p>Гарантия:</p></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <input type="text" name="r_service_id" placeholder="Номер гарантийного заказа" value="" />
                                    </div>
                                    <div class="ttable2_tbody_tr_td"><p>Гарантийный номер заказа</p></div>
                                </div>
                                <div class="ttable2_tbody_tr m_zakaz_r_service_view m_zakaz_r_service_hide">
                                    <div class="ttable2_tbody_tr_td">Диагноз:</div>
                                    <div class="ttable2_tbody_tr_td">
                                        <textarea class="r_service_diagnoz" name="diagnoz" placeholder="Диагностическое заключение"></textarea>
                                    </div>
                                    <div class="ttable2_tbody_tr_td m_zakaz_send_mess_div"><span class="r_service_diagnoz_send btn_gray"><i class="fa fa-envelope-o"></i> Отправить sms</span></div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                
                    
                    <!-- ТОВАР -->
                    <div id="tabs-1" style="<?=$dis1;?>">
                        <div class="find_tovar__block">
                            <div class="find_tovar">
                                <div class="find_tovar_tbl">
                                    <div class="find_tovar_tbl_td">
                                         <input name="s_cat_items_nalich" class="s_cat_items_nalich" type="checkbox" title="В наличии" />
                                    </div>
                                    <div class="find_tovar_tbl_td">
                                        <input name="s_cat_items_find" class="s_cat_items_find" type="text" placeholder="Найти товар..." />
                                        <i class="fa fa-search"></i>
                                    </div>
                                    <div class="find_tovar_tbl_td">
                                        <p> из каталога:</p>
                                    </div>
                                    <div class="find_tovar_tbl_td">
                                        <div class="find_tovar_select">
                                            <select name="s_struktura_s_cat_select" data-placeholder="[Все]">
                                                <option></option>
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
                                        <div class="clear"></div>
                                    </div>
                                    
                                    
                                </div>
                            </div>
                            <div class="find_tovar_res_all_div">
                                <div class="find_tovar_res_all_copy">Копировать из: <span class="find_tovar_res_all_copy_m_zakaz">заказа</span></div>
                                <div class="find_tovar_res_all_info">Выбрано <strong class="find_tovar_all_kol"></strong> товаров, на сумму <strong class="find_tovar_all_sum"></strong> руб.</div>
                            </div>
                        </div>
                        <div class="tovar_res_from_postav" style="display: none;">
                            <h3>Добавить товар к заказу из поступления:</h3>
                            <ul></ul>
                        </div>
                        <div class="find_tovar_res">
                            <div class="ttable find_tovar_res__tbl">
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
                    </div>
                   
                    
                    <!-- УСЛУГА -->
                    <div id="tabs-2" style="<?=$dis2;?>">
                        <div class="find_usluga__block">
                            <div class="find_usluga">
                                <input name="s_cat_items_find" class="s_cat_items_find" type="text" placeholder="Найти услугу..." />
                                <i class="fa fa-search"></i>
                                <p> из каталога:</p>
                                <div class="find_usluga_select">
                                    <select name="s_struktura_s_cat_select" data-placeholder="[Все]">
                                        <option></option>
                                    <?php
                                    $sql = "SELECT DISTINCT s_struktura.id, s_struktura.name
                                    				FROM s_struktura, s_cat_s_struktura, s_cat
                                    					WHERE s_struktura.id =s_cat_s_struktura.id2
                                                        AND s_cat_s_struktura.id1=s_cat.id
                                                        AND s_cat.tip='Услуга'
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
                                <div class="clear"></div>
                            </div>
                            <div class="find_usluga_res_all_info">Выбрано <strong class="find_usluga_all_kol"></strong> услуг, на сумму <strong class="find_usluga_all_sum"></strong> руб.</div>
                        
                        </div>
                        <div class="find_usluga_res">
                            <div class="ttable find_usluga_res__tbl">
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
                    </div>
                    
                    <div id="tabs-3">
                        <div class="m_zakaz_add_pl__add_div">
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
                                        <span><input type="number" class="pl_price" value="" placeholder="Сумма" /></span>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <p>Счет:</p>
                                    </div>
                                    <div class="ttable2_tbody_tr_td">
                                        <span class="pl_schet_span">
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
                                        <span class="btn_gray m_zakaz_add_pl__add_com">Добавить</span>
                                    </div>
                                </div>
                            </div>
                            
                            
                        </div>
                        <div class="clear"></div>
                        <div class="pl_all_info">Совершено <strong class="pl_all_kol">0</strong> платеж<span class="pl_end_word">ей</span>, на сумму <strong class="pl_all_sum">0</strong> руб.</div>
                        <div>
                        <div class="ttable m_zakaz_add_pl">
                            <div class="ttable_thead">
                                <div class="ttable_thead_tr">
                                    <div class="ttable_thead_tr_th">Дата</div>
                                    <div class="ttable_thead_tr_th">Сумма, руб.</div>
                                    <div class="ttable_thead_tr_th">Счет</div>
                                    <div class="ttable_thead_tr_th"></div>
                                </div>
                            </div>
                            <div class="ttable_tbody"></div>
                        </div>
                        </div>
                    </div>
                    <div id="tabs-4">
                        <p>Название проекта:</p>
                        <div><input type="text" class="m_zakaz_add__project_name" name="project_name" placeholder="Проект" value="Заказ №@@id@@" /></div>
                        <p>Документация:</p>
                        <div class="m_zakaz_add__docs_load">
                            <span class="btn_gray m_zakaz_add__html_code"><i class="fa fa-exclamation-circle"></i> Подробная информация</span>
                            <span class="btn_gray" id="m_zakaz_add__docs_load_com"><i class="fa fa-download"></i> Загрузить документы</span>
                        </div>
                        <div class="clear"></div>
                        <div class="m_zakaz_add__docs_res">
                            <ul></ul>
                            <div class="clear"></div>
                        </div>
                        <p>Отправить сообщение:</p>
                        <div class="m_zakaz_add__html_code_hide" style="display: none;"></div>
                        <div class="m_zakaz_send_mess_form">
                            <div class="ttable2">
                                <div class="ttable2_tbody_tr">
                                    <div class="ttable2_tbody_tr_td"><textarea class="m_zakaz_mess_text" id="m_zakaz_mess_text" placeholder="Текст сообщения"></textarea></div>
                                    <div class="ttable2_tbody_tr_td">
                                        <span class="btn_gray m_zakaz_add_file" id="upload_button"><i class="fa fa-download"></i> Загрузить</span>
                                        <span class="btn_orange m_zakaz_send_mess"><i class="fa fa-envelope"></i> Отправить</span>
                                    
                                    </div>
                                </div>
                                <div class="ttable2_tbody_tr">
                                    <div class="ttable2_tbody_tr_td">
                                        <div class="m_zakaz_mess_files">
                                            <div class="loading_file"></div>
                                            <ul>
                                            
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p>Сообщения:</p>
                        <div class="m_zakaz_all_mess">
                            <p class="m_zakaz_add_items__mess_all"></p>
                            <ul>
                            </ul>
                        </div>
                        <div class="clear"></div>
                        <div class="m_zakaz_log_text"></div>
                    </div>
                    <div id="tabs-5">
                        <div class="m_zakaz__dostavka_div">
                    
                            <div class="m_zakaz__dostavka_div_main no_dostavka">
                                <div class="bold_">
                                    <p>Номер отправления</p>
                                    <div>
                                        <input type="text" name="m_dostavka_tracking_number" placeholder="Номер отправления" />
                                    </div>
                                    <div class="m_zakaz__dostavka_send_mess"></div>
                                </div>
                                <div class="bold_">
                                    <p>Дата отправления</p>
                                    <div>
                                        <input type="text" name="m_dostavka_data" placeholder="Дата отправления" />
                                    </div>
                                </div>
                                <div class="bold_">
                                    <p>Транспортная компания</p>
                                    <div class="m_zakaz_i_tk_id_div">
                                    <div class="m_zakaz_i_tk_id_div_select">
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
                                        </div>
                                        <a href="?inc=i_tk" target="_blank">Добавить ТК</a>
                                    </div>
                                </div>
                                <hr />
                                <div>
                                    <p>Ф.И.О. получателя</p>
                                    <div>
                                        <input type="text" class="m_zakaz_m_dostavka_fio" name="m_dostavka_fio" placeholder="ФИО получателя" /> <span class="btn_gray m_dostavka_fio_clone">Из контрагента</span>
                                    </div>
                                </div>
                                <div>
                                    <p>Город доставки</p>
                                    <div>
                                        <select name="m_dostavka_city_id" data-placeholder="Город получателя">
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <p>Адрес получателя</p>
                                    <div>
                                        <textarea name="m_dostavka_adress" placeholder="Адрес получателя"></textarea>
                                    </div>
                                </div>
                                <div>
                                    <p>Индекс получателя</p>
                                    <div>
                                        <input type="number" name="m_dostavka_index" placeholder="Индекс получателя" maxlength="6" />
                                    </div>
                                </div>
                                <div>
                                    <p>Телефон получателя</p>
                                    <div>
                                        <input type="tel" class="m_zakaz_m_dostavka_phone" name="m_dostavka_phone" placeholder="Телефон получателя" /> <span class="btn_gray m_dostavka_phone_clone">Из контрагента</span>
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
                    
                </div><div class="clear"></div>
            </form>
            
            <!-- Добавление заказа -->
            <div class="m_zakaz_add_head m_zakaz_add_head_bottom">
                <ul class="m_zakaz_add_head_menu">
                    <li><span class="m_zakaz_add_form__plus" style="display: none;"><i class="fa fa-plus"></i> Открыть заказ</span></li>
                    <li><span class="m_zakaz_add_form__close" style="display: none;"><i class="fa fa-remove"></i> Отменить заказ</span></li>
                    <li><span class="m_zakaz_add_form__clear"><i class="fa fa-eraser"></i> Новый заказ</span></li>
                    <li><span class="m_zakaz_add_form__save"><i class="fa fa-save"></i> Сохранить</span></li>
                </ul>
            </div>
            <div class="clear"></div>
            </div>
            <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
       <div class="clear"></div>
        <div class="ttable2_tbody_tr_td m_zakaz_find">
            <h1>Заказы покупателей</h1>
            <div class="m_zakaz_find_div">
            <div class="m_zakaz_fillter">
                <div class="m_zakaz_fillter__find_txt">
                    <input name="find_txt" placeholder="Поиск по контрагенту, исполнителю, товару/услуге" type="text" value="<?=_IN(_GP('nomer'));?>" />
                    <i class="fa fa-search"></i>
                    <div class="clear"></div>
                </div>
                <div class="m_zakaz_fillter__data_in_out">
                    <input type="text" name="m_zakaz_data1_find" placeholder="от" />
                    -
                    <input type="text" name="m_zakaz_data2_find" placeholder="до" />
                </div>
                <div class="m_zakaz_fillter__fire">
                    <label><input type="checkbox"  name="m_zakaz_fire" /> <span class="thumbnail">Горящие заказы <span>Для заказов необходимо выбрать ответстенного! <br />
                    <?php
                    if ($service_chk>0){
                        ?>Сервисные заказы необходимо перевести в статус "В работе" или назначить ответственного<?php
                    }
                    
                    ?></span></span> старше
                    <input type="text" name="m_zakaz_fire_h" value="<?=$_SESSION['a_options']['Дедлайн для новых заказов, часов'];?>" placeholder="часов" /> часов</label>
                </div>
                <div class="m_zakaz_fillter__other_form">
                    <div class="i_tp_find_div">
                        <?php
                        
                        $sql = "SELECT COUNT(*)
                        				FROM a_admin_a_menu
                        					WHERE a_admin_a_menu.id1='"._DB($a_admin_id)."'
                                            AND a_admin_a_menu.id2='53'
                                            
                        	"; 
                        
                        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                        $myrow = mysql_fetch_array($res);
                        $dis='';if ($myrow[0]=='0'){$dis=' disabled="disabled"';}
                        ?>
                        <select name="i_tp_id_find" data-placeholder="Филиал" <?=$dis;?>>
                            <option value="-1">[Все]</option>
                            <?php
                            
                            $sql = "SELECT id, name 
                                				FROM i_tp 
                                					
                             ";
                            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                            {
                                $sel_='';if ($a_admin_i_tp_id==$myrow[0]){
                                    $sel_=' selected="selected"';
                                }
                                ?>
                                <option value="<?=$myrow['id'];?>" <?=$sel_;?>><?=$myrow['name'];?></option>
                                <?php
                            }
                            
                            ?>
                        </select>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
            
                <div class="m_zakaz_fillter__data_done_in_out display_none">
                    <i class="fa fa-bell" title="Дата напоминания"></i>
                    <input type="text" name="m_zakaz_data_done1_find" placeholder="от" />
                    -
                    <input type="text" name="m_zakaz_data_done2_find" placeholder="до" />
                </div>
                <div class="m_zakaz_otvet_find_div display_none">
                    <select name="m_zakaz_otvet_find" data-placeholder="Ответственный">
                        <option value=""></option>
                        <option value="-1">[ОТВЕТСТВЕННЫЙ НЕ НАЗНАЧЕН]</option>
                        <?php
                        $sql = "SELECT a_admin.id, a_admin.name
                        				FROM a_admin, m_zakaz
                        					WHERE a_admin.chk_active='1'
                                            AND a_admin.id=m_zakaz.a_admin_otvet_id
                                            GROUP BY a_admin.id
                        ";
                         
                        $mt = microtime(true);
                        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                        
                        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                        {
                            ?>
                            <option value="<?=_IN($myrow[0]);?>"><?=$myrow[1];?></option>
                            <?php
                        }
                        ?>
                    </select>
                    
                    <div class="clear"></div>
                </div>
                
                <div class="m_zakaz_i_tk_find_div display_none">
                    <select name="m_zakaz_i_tk_find" data-placeholder="ТК" multiple="multiple">
                        <?php
                        $sql = "SELECT i_tk.id, i_tk.name
                        				FROM i_tk
                        					WHERE i_tk.chk_active='1'
                                            
                        ";
                         
                        $mt = microtime(true);
                        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                        
                        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                        {
                            ?>
                            <option value="<?=_IN($myrow[0]);?>"><?=$myrow[1];?></option>
                            <?php
                        }
                        ?>
                    </select>
                    
                    <div class="clear"></div>
                </div>
                
                
                <div class="clear display_none"></div>
                <div class="m_zakaz_i_reklama_find_div display_none">
                    <select name="m_zakaz_i_reklama_find" data-placeholder="Реклама" multiple="multiple">
                        <?php
                        $sql = "SELECT i_reklama.id, i_reklama.name
                        				FROM i_reklama
                        					WHERE i_reklama.chk_active='1'
                                            
                        ";
                         
                        $mt = microtime(true);
                        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                        
                        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                        {
                            ?>
                            <option value="<?=_IN($myrow[0]);?>"><?=$myrow[1];?></option>
                            <?php
                        }
                        ?>
                    </select>
                    
                    <div class="clear"></div>
                </div>
                    
                <div class="clear display_none"></div>
                
                    
                <div class="m_zakaz_fillter__tip display_none">
                    <ul class="m_zakaz_fillter__status_zakaz">
                        <li data-val="В обработке" class=""><i class="fa fa-clock-o"></i> В обработке</li>
                        <li data-val="Частично выполнен" class=""><i class="fa fa-pie-chart"></i> Частично выполнен</li>
                        <li data-val="Выполнен" class=""><i class="fa fa-check-circle-o"></i> Выполнен</li>
                        <li data-val="Отменен"><i class="fa fa-minus"></i> Отменен</li>
                        <div class="clear"></div>
                    </ul>
                    <div class="clear"></div>
                    <ul class="m_zakaz_fillter__status_pay">
                        <li class="no_pay" data-val="Не оплачен"><i class="fa fa-circle-o"></i> Не оплачен</li>
                        <li class="no_full_pay" data-val="Частично оплачен"><i class="fa fa-dot-circle-o"></i> Частично оплачен</li>
                        <li class="full_pay" data-val="Оплачен"><i class="fa fa-check-circle"></i> Оплачен</li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <div class="clear display_none"></div>
                <div class="m_zakaz_fillter__time display_none">
                    <ul class="m_zakaz_fillter__time_ul">
                        <li data-id="1" class="time_li_1"><i class="fa fa-bell"></i> Просрочка <span></span></li>
                        <li data-id="2" class="time_li_2"><i class="fa fa-bell"></i> Напоминание <span></span></li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <div class="m_zakaz_fillter__sort display_none">
                    <span><i class="fa fa-sort-amount-desc" title="Сортировка"></i></span>
                    <ul>
                        <li data-val="id" class="active" title="Сортировка по номеру заказа"><span class="for_mobile">Сортировать по: </span>Номер</li>
                        <li data-val="data" title="Сортировка по дате заказа"><span class="for_mobile">Сортировать по: </span>Дата заказа</li>
                        <li data-val="data_change" title="Сортировка по дате последнего изменения заказа"><span class="for_mobile">Сортировать по: </span>Дата изменения</li>
                        <li data-val="data_done" title="Сортировка по дате выполнения заказа"><span class="for_mobile">Сортировать по: </span>Дата выполнения</li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <?php
                if ($cnt_service>0){
                ?>
                <div class="m_zakaz_fillter__service display_none">
                    <ul class="m_zakaz_fillter__service_ul">
                        <li data-id="-1" class="service_li_0 active">[ВСЕ]<span></span></li>
                        <li data-id="act" class="service_li_10" title="Принят в ремонт"><i class="fa fa-cogs"></i><span></span></li>
                        <li data-id="no_act" class="service_li_11" title="Продажа, без приема в ремонт"><i class="fa fa-cogs"></i><i class="fa fa-remove"></i><span></span></li>
                        <li data-id="Принят" class="service_li_1">Принят<span></span></li>
                        <li data-id="Согласование" class="service_li_2">Согласование<span></span></li>
                        <li data-id="В работе" class="service_li_3">В работе<span></span></li>
                        <li data-id="Ожидание запчастей" class="service_li_4">Заказ запчастей<span></span></li>
                        <li data-id="Готов" class="service_li_5">Готов<span></span></li>
                        <li data-id="Отдан" class="service_li_6">Отдан<span></span></li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <?php
                }
                ?>
                <div class="clear display_none"></div>
                <div class="view_all_fillter view_all_fillter_inherit btn_gray">Открыть фильтр</div>
                <div class="clear"></div>
            </div>
           
            <div class="m_zakaz_find_res">
                
            </div>
            </div>
        </div>
             <!---->
    </div>
</div>
