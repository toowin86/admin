<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода
?>
<h1>Отчеты</h1>
<table class="m_reports_first_tbl">
    <tr>
        <td class="m_reports_find">
            <!-- Фильтр -->
            <div class="m_reports_fillter">
                <!-- Тип отчета -->
                <div class="m_reports_fillter__tip_report">
                    <select name="tip_report" data-placeholder="Укажите тип отчета">
                        <option value="Отчет по прибыли" data-tip="report1">Отчет по прибыли</option>
                        <option value="Отчет по товарам" data-tip="report2">Отчет по товарам</option>
                        <option value="Отчет по месяцам" data-tip="report3">Отчет по месяцам</option>
                        <!--
                        <option value="Отчет по складу">Отчет по складу</option>
                        <option value="Отчет по типу техники">Отчет по типу техники</option>
                        -->
                    </select>
                </div>
                <!-- Фильтр - Отчет по прибыли -->
                <div class="fillter_report fillter_report1">
                     <div class="m_reports_fillter__data_in_out">
                        <input type="text" name="m_reports_data1_report1_find" placeholder="от" value="<?=date('d.m.Y');?>" />
                        -
                        <input type="text" name="m_reports_data2_report1_find" placeholder="до" value="<?=date('d.m.Y');?>" />
                    </div>
                    
                    <!-- сортировка -->
                    <div class="m_reports_fillter__sort">
                        <ul>
                            <li class="active" data-val="data"><i class="fa fa-calendar"></i> По дате создания</li>
                            <li data-val="data_done"><i class="fa fa-calendar-o"></i> По дате выполнения</li>
                       </ul>
                    </div>
                    <!-- оплата -->
                    <div class="m_reports_fillter__pay">
                        <ul>
                            <li data-val="-1">[Все]</li>
                            <li data-val="1"><i class="fa fa-circle-o"></i> Не оплачен</li>
                            <li class="active" data-val="2"><i class="fa fa-check-circle"></i> Оплачен</li>
                       </ul>
                    </div>
                </div>
                <!-- Фильтр - Отчет по товарам -->
                <div class="fillter_report fillter_report2">
                     <div class="m_reports_fillter__data_in_out">
                        <input type="text" name="m_reports_data1_report2_find" placeholder="от" value="<?=date('d.m.Y');?>" />
                        -
                        <input type="text" name="m_reports_data2_report2_find" placeholder="до" value="<?=date('d.m.Y');?>" />
                    </div>
                
                </div>
                <!-- Фильтр - Отчет по месяцам -->
                <div class="fillter_report fillter_report3">
                    <div class="m_reports_fillter__datayear_report3">
                        <span>Год: </span>
                        <div class="m_reports_fillter__datayear">
                            <select name="report3_year" data-placeholder="Год">
                            
                            <?php 
                            $sql="SELECT DISTINCT DATE_FORMAT(m_platezi.data,'%Y') AS dy
                                        FROM m_platezi
                                            
                                            HAVING dy!='0000'
                                                ORDER BY m_platezi.data
                                    ";
                            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                            while ($row = mysql_fetch_array($res)) {
                                $sel_=''; if ($row[0]==date('Y')){$sel_=' selected="selected"';}
                                ?>
                                <option value="<?=$row[0];?>"<?=$sel_;?>><?=$row[0];?></option>
                                <?php
                            }
                            
                            ?>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Дата -->
               
            </div>
            <div style="clear: both;"></div>
            <!-- Результат -->
            <div class="m_reports_find_res">
                
            </div>
            <div style="clear: both;"></div>
            <div class="m_reports_find_res_loading">
                
            </div>
        </td>
        <td class="m_reports_full_info">
            <!-- Подробная информация отчета -->
            <div class="m_reports_full_info_div">
                <h2>Подробная информация</h2>
                <p>В данном отчете выводится информация только по выполненным заказам</p>
                <div class="m_reports_all_summa"></div>
                <div class="m_reports_all_workers"></div>
                <div class="m_reports_all_rashodi"></div>
                <div class="m_reports_all_reklama"></div>
                <div class="m_reports_all_itog_vse"></div>
            </div>
        </td>
    </tr>
</table>