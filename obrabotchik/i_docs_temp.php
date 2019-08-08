<?php

$com=_GP('com');
if ($com=='print'){
    $file_name=_GP('file_name');
        
    $nomer=_GP('nomer'); if ($nomer==''){echo 'Не определена переменная $nomer';exit;}
    
    
    if (!file_exists('class/docxGenerator.php')){$err_text='НЕТ ФАЙЛА: class/docxGenerator.php';exit;}
    include_once('class/docxGenerator.php');
    if (!file_exists('../upload/temp/')) {@mkdir('../upload/temp/',0777);}
                
    $sql = "SELECT i_docs.html_code, i_docs.name
    				FROM i_docs 
    					WHERE i_docs.file_name='"._DB($file_name)."'
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $myrow = mysql_fetch_array($res);
    $file_docs='../'.strip_tags($myrow[0]);
    $name_docs=strip_tags($myrow[1]);
    if (!file_exists($file_docs)){
        echo 'Не найден файл '.$file_docs;exit;
    }
    if ($file_name!=''){
        if($file_name=='docx_pko' or $file_name=='docx_rko'){
        //**************************************************************************************
        //обработка платежей
        //**************************************************************************************
            $nomer=_GP('nomer');
            
            $sql_m_platezi = "SELECT    m_platezi.data,
                                        m_platezi.summa,
                                        m_platezi.i_scheta_id,
                                        m_platezi.a_menu_id,
                                        m_platezi.tip,
                                        m_platezi.id_z_p_p,
                                        m_platezi.a_admin_id,
                                        (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE m_platezi.a_admin_id=a_admin.id) AS a_admin_name,
                                        (SELECT IF(COUNT(*)>0,i_contr_org.name,'') FROM i_tp, a_admin, i_contr_org WHERE i_tp.i_contr_org_id=i_contr_org.id AND a_admin.id=m_platezi.a_admin_id AND  i_tp.id=a_admin.i_tp_id) AS i_tp_name,
                                        m_platezi.comments
            				FROM m_platezi 
            					WHERE m_platezi.id='"._DB($nomer)."' 
            					ORDER BY m_platezi.id DESC
            	"; 
            
            $mt = microtime(true);
            $res_m_platezi = mysql_query($sql_m_platezi) or die(mysql_error().'<br/>'.$sql_m_platezi);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_platezi;$data_['_sql']['time'][]=$mt;
            $myrow_m_platezi = mysql_fetch_array($res_m_platezi);
            $sum_txt=mb_ufirst(trim(num2str($myrow_m_platezi['summa'])));
            
            
            
            //**************************************************************************************************
            if ($file_name=='docx_pko'){
                
                if ($myrow_m_platezi['a_menu_id']=='16'){
                    $sql = "SELECT  m_zakaz.data,
                                    m_zakaz.i_contr_id,
                                    m_zakaz.i_contr_org_id,
                                    m_zakaz.project_name,
                                    i_contr.name,
                                    i_contr.phone,
                                    m_zakaz.i_tp_id,
                                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'Не указана организация в филиале!') FROM i_contr_org,i_tp  WHERE i_tp.id=m_zakaz.i_tp_id AND i_tp.i_contr_org_id=i_contr_org.id) AS i_tp_name,
                                    m_zakaz.data_create,
                                    (SELECT IF(COUNT(*)>0,r_service.id,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS r_service_id,
                                    (SELECT SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_summ_m_zakaz,
                                    (SELECT SUM(m_platezi.summa) FROM m_platezi WHERE m_platezi.id_z_p_p='"._DB($myrow_m_platezi['id_z_p_p'])."' AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS all_summ_platezi
                                    
                                    
                    				FROM m_zakaz, i_contr
                    					WHERE m_zakaz.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        AND i_contr.id=m_zakaz.i_contr_id
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                    $i_tp_name=$myrow['i_tp_name'];
                    $prill='Заказ №'.$myrow_m_platezi['id_z_p_p'];
                 
                    if ($myrow['r_service_id']!=''){$myrow['project_name']='Договор №'.$myrow_m_platezi['id_z_p_p'];}
                    if ($myrow['all_summ_m_zakaz']>$myrow['all_summ_platezi'] and $myrow['r_service_id']!=''){
                        $myrow['project_name']='Задаток по договору №'.$myrow_m_platezi['id_z_p_p'];
                    }
                    if ($myrow['all_summ_m_zakaz']>$myrow['all_summ_platezi'] and $myrow['r_service_id']==''){
                        $myrow['project_name']='Задаток по заказу №'.$myrow_m_platezi['id_z_p_p'];
                    }
                    
                    $i_contr_name='';
                    if ($myrow['i_contr_org_id']>0){
                        $sql_i_contr_org = "SELECT  i_contr_org.name,
                                                    i_contr_org.inn
                                                    
                                				FROM i_contr_org 
                                					WHERE i_contr_org.id='"._DB($myrow['i_contr_org_id'])."' 
                                					
                        	"; 
                        
                        $res_i_contr_org = mysql_query($sql_i_contr_org) or die(mysql_error().'<br/>'.$sql_i_contr_org);
                        $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org);
                        $i_contr_name=$myrow_i_contr_org[0];
                    }else{
                        $i_contr_name=$myrow[4];
                    }
                  
                }
                elseif ($myrow_m_platezi['a_menu_id']=='17'){
                    $sql = "SELECT  m_postav.data,
                                    m_postav.i_contr_id,
                                    m_postav.i_contr_org_id,
                                    m_postav.project_name,
                                    i_contr.name,
                                    i_contr.phone,
                                    m_postav.i_tp_id,
                                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'Не указана организация в филиале!') FROM i_contr_org,i_tp  WHERE i_tp.id=m_postav.i_tp_id AND i_tp.i_contr_org_id=i_contr_org.id) AS i_tp_name,
                                    m_postav.data_create
                                    
                    				FROM m_postav, i_contr
                    					WHERE m_postav.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        AND i_contr.id=m_postav.i_contr_id
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                    $i_tp_name=$myrow['i_tp_name'];
                    $prill='Поступление №'.$myrow_m_platezi['id_z_p_p'];
                    $i_contr_name='';
                    if ($myrow['i_contr_org_id']>0){
                        $sql_i_contr_org = "SELECT  i_contr_org.name,
                                                    i_contr_org.inn
                                                    
                                				FROM i_contr_org 
                                					WHERE i_contr_org.id='"._DB($myrow['i_contr_org_id'])."' 
                                					
                        	"; 
                        
                        $res_i_contr_org = mysql_query($sql_i_contr_org) or die(mysql_error().'<br/>'.$sql_i_contr_org);
                        $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org);
                        $i_contr_name=$myrow_i_contr_org[0];
                    }else{
                        $i_contr_name=$myrow[4];
                    }
                  
                }
                else{
                    echo 'Не определен тип a_menu_id='.$myrow_m_platezi['a_menu_id'];
                    exit;
                }
                
                //echo 'ПКО в процессе доработки!';exit;
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $word = new docxGenerator($file_docs);
                $word->val('nomerdoc', $nomer);
                $word->val('ddata', date('d.m.Y',strtotime($myrow_m_platezi['data'])));
                $word->val('dtt', date('d.m.Y',strtotime($myrow_m_platezi['data'])));
                $word->val('summ', number_format($myrow_m_platezi['summa'],2,',',' '));
                $word->val('stext', $sum_txt);
                $word->val('osnovanie',$myrow['project_name']);
                $word->val('ndspotc','Без НДС');
                $word->val('ndssum','0');
                
                $word->val('pokupatel', $i_contr_name);
                $word->val('postavchik', $i_tp_name);
                $word->val('prill', $prill);
               
                $word->save($filename);
            }
            //**************************************************************************************************
            if ($file_name=='docx_rko'){
                $postavshik='';
                if ($myrow_m_platezi['a_menu_id']=='16'){//возврат по заказу
                    $sql = "SELECT  m_zakaz.data,
                                    m_zakaz.i_contr_id,
                                    m_zakaz.i_contr_org_id,
                                    m_zakaz.project_name,
                                    i_contr.name AS i_contr_name,
                                    i_contr.phone,
                                    m_zakaz.i_tp_id,
                                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'Не указана организация в филиале!') FROM i_contr_org,i_tp  WHERE i_tp.id=m_zakaz.i_tp_id AND i_tp.i_contr_org_id=i_contr_org.id) AS i_tp_name,
                                    m_zakaz.data_create
                                    
                    				FROM m_zakaz, i_contr
                    					WHERE m_zakaz.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        AND i_contr.id=m_zakaz.i_contr_id
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                    $i_tp_name=$myrow['i_tp_name'];
                    $prill='Заказ №'.$myrow_m_platezi['id_z_p_p'].' от '.date('d.m.Y',strtotime($myrow['data']));
                    $i_contr_name='';
                    if ($myrow['i_contr_org_id']>0){
                        $sql_i_contr_org = "SELECT  i_contr_org.name,
                                                    i_contr_org.inn
                                                    
                                				FROM i_contr_org 
                                					WHERE i_contr_org.id='"._DB($myrow['i_contr_org_id'])."' 
                                					
                        	"; 
                        
                        $res_i_contr_org = mysql_query($sql_i_contr_org) or die(mysql_error().'<br/>'.$sql_i_contr_org);
                        $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org);
                        $i_contr_name=$myrow_i_contr_org[0];
                    }else{
                        $i_contr_name=$myrow[4];
                    }
                    $osnovanie='Возврат по заказу №'.$myrow_m_platezi['id_z_p_p'];
                }
                elseif ($myrow_m_platezi['a_menu_id']=='17'){//Поступление
                    $sql = "SELECT  m_postav.data,
                                    m_postav.i_contr_id,
                                    m_postav.i_contr_org_id,
                                    m_postav.project_name,
                                    i_contr.name AS i_contr_name,
                                    i_contr.phone,
                                    m_postav.i_tp_id,
                                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'Не указана организация в филиале!') FROM i_contr_org,i_tp  WHERE i_tp.id=m_postav.i_tp_id AND i_tp.i_contr_org_id=i_contr_org.id) AS i_tp_name,
                                    m_postav.data_create
                                    
                    				FROM m_postav, i_contr
                    					WHERE m_postav.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        AND i_contr.id=m_postav.i_contr_id
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                    $i_tp_name=$myrow['i_tp_name'];
                    $prill='Поступление №'.$myrow_m_platezi['id_z_p_p'].' от '.date('d.m.Y',strtotime($myrow['data']));
                    $i_contr_name='';
                    if ($myrow['i_contr_org_id']>0){
                        $sql_i_contr_org = "SELECT  i_contr_org.name,
                                                    i_contr_org.inn
                                                    
                                				FROM i_contr_org 
                                					WHERE i_contr_org.id='"._DB($myrow['i_contr_org_id'])."' 
                                					
                        	"; 
                        
                        $res_i_contr_org = mysql_query($sql_i_contr_org) or die(mysql_error().'<br/>'.$sql_i_contr_org);
                        $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org);
                        $i_contr_name=$myrow_i_contr_org[0];
                    }else{
                        $i_contr_name=$myrow[4];
                    }
                    $osnovanie='Поступление товара №'.$myrow_m_platezi['id_z_p_p'];
                }
                elseif ($myrow_m_platezi['a_menu_id']=='4'){//З/П
                    $sql = "SELECT  a_admin.id,
                                    a_admin.name,
                                    a_admin.phone,
                                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'Не указана организация в филиале!') FROM i_contr_org,i_tp  WHERE i_tp.id=a_admin.i_tp_id AND i_tp.i_contr_org_id=i_contr_org.id) AS i_tp_name
                                                                        
                    				FROM a_admin
                    					WHERE a_admin.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                   
                    $i_tp_name=$myrow['i_tp_name']; 
                  
                    $prill='Выдача заработной платы №'.$nomer.' от '.date('d.m.Y',strtotime($myrow_m_platezi['data']));
                    
                    $i_contr_name=$myrow['name'];
                    $osnovanie='Выдача заработной платы, выдал: '.$myrow_m_platezi['a_admin_name'];
                }
                elseif ($myrow_m_platezi['a_menu_id']=='100'){//Расходы
                    $sql = "SELECT  i_rashodi.name
                                                    
                    				FROM i_rashodi
                    					WHERE i_rashodi.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                   
                    $i_tp_name=$myrow_m_platezi['i_tp_name']; 
                  
                    $prill='Расходы №'.$nomer.' от '.date('d.m.Y',strtotime($myrow_m_platezi['data']));
                    
                    $i_contr_name=$myrow_m_platezi['a_admin_name'];
                    $osnovanie='Расходы. '.$myrow['name'].'. '.$myrow_m_platezi['comments'];
                }
                elseif ($myrow_m_platezi['a_menu_id']=='40'){//Реклама
                    $sql = "SELECT  i_reklama.name
                                                    
                    				FROM i_reklama
                    					WHERE i_reklama.id='"._DB($myrow_m_platezi['id_z_p_p'])."'
                                        
                    	"; 
                    
                    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                    $myrow = mysql_fetch_array($res);
                   
                    $i_tp_name=$myrow_m_platezi['i_tp_name']; 
                  
                    $prill='Реклама №'.$nomer.' от '.date('d.m.Y',strtotime($myrow_m_platezi['data']));
                    
                    $i_contr_name=$myrow_m_platezi['a_admin_name'];
                    $osnovanie='Реклама. '.$myrow['name'].'. '.$myrow_m_platezi['comments'];
                }
                else{
                    echo 'Не определен тип a_menu_id='.$myrow_m_platezi['a_menu_id'];
                    exit;
                }
                
                //echo 'ПКО в процессе доработки!';exit;
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $word = new docxGenerator($file_docs);
                $word->val('nomerdoc', $nomer);
                $word->val('ddata', date('d.m.Y',strtotime($myrow_m_platezi['data'])));
                $word->val('summ', number_format($myrow_m_platezi['summa'],2,',',' '));
                $word->val('stext', $sum_txt);
                $word->val('osnovanie',$osnovanie);
                $word->val('ndspotc','Без НДС');
                $word->val('ndssum','0');
                $word->val('postavshik',$i_tp_name);
                
                $word->val('pokupatel', $i_contr_name);
                $word->val('namsokr', $i_contr_name);
                $word->val('prill', $prill);
               
                $word->save($filename);
            }
        }
   
        elseif (       $file_name=='docx_schet'
                or $file_name=='schet_print'
                or $file_name=='docx_act'
                or $file_name=='docx_tovar_check'
                or $file_name=='ttn'
                or $file_name=='sf'
                or $file_name=='dogovor1'
                or $file_name=='dogovor2'
                or $file_name=='dogovor3'
                or $file_name=='post_form'
                or $file_name=='garant_t'
                or $file_name=='diagnoz'
                or $file_name=='dogovor_postavki'
        ){
        //**************************************************************************************
        //Обработка заказа
        //**************************************************************************************
            $sql_m_zakaz = "SELECT  m_zakaz.a_admin_id,
                            (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE a_admin.id=m_zakaz.a_admin_id) AS a_admin_name,
                            m_zakaz.i_contr_id,
                            i_contr.name,
                            i_contr.email,
                            i_contr.phone,
                            i_contr.adress,
                            i_contr.skidka,
                            i_contr.pasport,
                            i_contr.birthday,
                            i_contr.html_code,
                            i_contr.link,
                            m_zakaz.project_name,
                            m_zakaz.status,
                            m_zakaz.tip_pay,
                            m_zakaz.comments,
                            m_zakaz.data,
                            m_zakaz.data_create,
                            m_zakaz.data_end,
                            m_zakaz.data_done,
                            m_zakaz.i_contr_org_id,
                            m_zakaz.i_tp_id,
                            (SELECT IF(COUNT(*)>0,i_tp.name,'') FROM i_tp WHERE i_tp.id=m_zakaz.i_tp_id) AS i_tp_name,
                            (SELECT IF(COUNT(*)>0,i_tp.phone,'') FROM i_tp WHERE i_tp.id=m_zakaz.i_tp_id) AS i_tp_phone,
                            (SELECT IF(COUNT(*)>0,i_tp.index_,'') FROM i_tp WHERE i_tp.id=m_zakaz.i_tp_id) AS i_tp_index,
                            (SELECT IF(COUNT(*)>0,i_tp.adress,'') FROM i_tp WHERE i_tp.id=m_zakaz.i_tp_id) AS i_tp_adress,
                            (SELECT IF(COUNT(*)>0,i_city.name,'') FROM i_tp, i_city WHERE i_city.id=i_tp.i_city_id AND i_tp.id=m_zakaz.i_tp_id) AS i_tp_city_name,
                            (SELECT IF(COUNT(*)>0,i_city.region,'') FROM i_tp, i_city WHERE i_city.id=i_tp.i_city_id AND i_tp.id=m_zakaz.i_tp_id) AS i_tp_region_name,
                            (SELECT IF(COUNT(*)>0,i_contr_org.name,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_name,
                            (SELECT IF(COUNT(*)>0,i_contr_org.inn,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_inn,
                            (SELECT IF(COUNT(*)>0,i_contr_org.kpp,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_kpp,
                            (SELECT IF(COUNT(*)>0,i_contr_org.ogrn,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_ogrn,
                            (SELECT IF(COUNT(*)>0,i_contr_org.bik,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_bik,
                            (SELECT IF(COUNT(*)>0,i_contr_org.bank,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_bank,
                            (SELECT IF(COUNT(*)>0,i_contr_org.schet,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_schet,
                            (SELECT IF(COUNT(*)>0,i_contr_org.kschet,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_kschet,
                            (SELECT IF(COUNT(*)>0,i_contr_org.phone,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_phone,
                            (SELECT IF(COUNT(*)>0,i_contr_org.u_adress,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_u_adress,
                            (SELECT IF(COUNT(*)>0,i_contr_org.fio_director,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_fio_director,
                            (SELECT IF(COUNT(*)>0,i_contr_org.tip_director,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_tip_director,
                            (SELECT IF(COUNT(*)>0,i_contr_org.na_osnovanii,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_na_osnovanii,
                            (SELECT IF(COUNT(*)>0,i_contr_org.id,'') FROM i_tp, i_contr_org WHERE i_tp.id=m_zakaz.i_tp_id AND i_contr_org.id=i_tp.i_contr_org_id) AS i_tp_i_contr_org_id,
                            (SELECT IF(COUNT(*)>0,r_service.data_vidachi,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS data_vidachi
                            
            				FROM m_zakaz, i_contr
            					WHERE m_zakaz.id='"._DB($nomer)."'
                                AND i_contr.id=m_zakaz.i_contr_id
            	"; 
            
            $res_m_zakaz = mysql_query($sql_m_zakaz) or die(mysql_error().'<br/>'.$sql_m_zakaz);
            $myrow_m_zakaz = mysql_fetch_assoc($res_m_zakaz);
            if ($myrow_m_zakaz['i_tp_i_contr_org_name']==''){
                echo 'Не привязана к филиалу "<a href="?inc=i_tp&com=_change&nomer='.$myrow_m_zakaz['i_tp_id'].'">'.$myrow_m_zakaz['i_tp_name'].'</a>" организация!';
                exit;
            }
            
           
            //Дата выдачи заказа
            if ($myrow_m_zakaz['data_vidachi']=='' or $myrow_m_zakaz['data_vidachi']=='0000-00-00 00:00:00'){
                $myrow_m_zakaz['data_vidachi']=$myrow_m_zakaz['data_done'];
            }
            
            if ($myrow_m_zakaz['data_vidachi']!='' and $myrow_m_zakaz['data_vidachi']!='0000-00-00 00:00:00'){
                $myrow_m_zakaz['data_vidachi']=date('d.m.Y',strtotime($myrow_m_zakaz['data_vidachi']));
            }
            
            
            $myrow_m_zakaz['i_tp_full_name']='';
            if ($myrow_m_zakaz['i_tp_i_contr_org_name']!=''){if ($myrow_m_zakaz['i_tp_full_name']!=''){$myrow_m_zakaz['i_tp_full_name'].=', ';}$myrow_m_zakaz['i_tp_full_name'].=$myrow_m_zakaz['i_tp_i_contr_org_name'];}
            if ($myrow_m_zakaz['i_tp_i_contr_org_inn']!=''){if ($myrow_m_zakaz['i_tp_full_name']!=''){$myrow_m_zakaz['i_tp_full_name'].=', ';}$myrow_m_zakaz['i_tp_full_name'].='ИНН: '.$myrow_m_zakaz['i_tp_i_contr_org_inn'];}
            if ($myrow_m_zakaz['i_tp_i_contr_org_kpp']!=''){if ($myrow_m_zakaz['i_tp_full_name']!=''){$myrow_m_zakaz['i_tp_full_name'].=', ';}$myrow_m_zakaz['i_tp_full_name'].='КПП: '.$myrow_m_zakaz['i_tp_i_contr_org_kpp'];}
            if ($myrow_m_zakaz['i_tp_i_contr_org_phone']!=''){if ($myrow_m_zakaz['i_tp_full_name']!=''){$myrow_m_zakaz['i_tp_full_name'].=', ';}$myrow_m_zakaz['i_tp_full_name'].='тел.: '.conv_('phone_from_db',$myrow_m_zakaz['i_tp_i_contr_org_phone']);}
            if ($myrow_m_zakaz['i_tp_i_contr_org_u_adress']!=''){if ($myrow_m_zakaz['i_tp_full_name']!=''){$myrow_m_zakaz['i_tp_full_name'].=', ';}$myrow_m_zakaz['i_tp_full_name'].=''.$myrow_m_zakaz['i_tp_i_contr_org_u_adress'];}
            
            
            
            //организация
            $myrow_i_contr_org['name']='';
            $myrow_i_contr_org['inn']='';
            $myrow_i_contr_org['kpp']='';
            $myrow_i_contr_org['ogrn']='';
            $myrow_i_contr_org['bik']='';
            $myrow_i_contr_org['bank']='';
            $myrow_i_contr_org['schet']='';
            $myrow_i_contr_org['kschet']='';
            $myrow_i_contr_org['phone']='';
            $myrow_i_contr_org['u_adress']='';
            $myrow_i_contr_org['fio_director']='';
            $myrow_i_contr_org['tip_director']='';
            $myrow_i_contr_org['na_osnovanii']='';
            $myrow_i_contr_org['tip_firm']='';
            
            if (($myrow_m_zakaz['i_contr_org_id'])-0>0){
                $sql_i_contr_org = "SELECT  i_contr_org.name,
                                            i_contr_org.inn,
                                            i_contr_org.kpp,
                                            i_contr_org.ogrn,
                                            i_contr_org.bik,
                                            i_contr_org.bank,
                                            i_contr_org.schet,
                                            i_contr_org.kschet,
                                            i_contr_org.phone,
                                            i_contr_org.u_adress,
                                            i_contr_org.fio_director,
                                            i_contr_org.tip_director,
                                            i_contr_org.na_osnovanii
                                            
                        				FROM i_contr_org 
                        					WHERE i_contr_org.id='"._DB($myrow_m_zakaz['i_contr_org_id'])."' 
                					
                	"; 
                
                $res_i_contr_org = mysql_query($sql_i_contr_org) or die(mysql_error().'<br/>'.$sql_i_contr_org);
                $myrow_i_contr_org = mysql_fetch_assoc($res_i_contr_org);
            }
            
            if ($myrow_i_contr_org['inn']!=''){
                $myrow_i_contr_org['tip_firm']='ИП';
                if (mb_strlen($myrow_i_contr_org['inn'],'UTF-8')==10){
                    $myrow_i_contr_org['tip_firm']='ООО';
                }
            }
            
            
            $myrow_i_contr_org['full_name']='';
            if ($myrow_i_contr_org['name']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].=$myrow_i_contr_org['name'];}
            if ($myrow_i_contr_org['inn']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].='ИНН: '.$myrow_i_contr_org['inn'];}
            if ($myrow_i_contr_org['kpp']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].='КПП: '.$myrow_i_contr_org['kpp'];}
            if ($myrow_i_contr_org['phone']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].='тел.: '.conv_('phone_from_db',$myrow_i_contr_org['phone']);}
            if ($myrow_i_contr_org['u_adress']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].=''.$myrow_i_contr_org['u_adress'];}
            
            if ($myrow_i_contr_org['full_name']==''){
                $myrow_i_contr_org['full_name']=$myrow_m_zakaz['name'];
                if ($myrow_m_zakaz['phone']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].=''.conv_('phone_from_db',$myrow_m_zakaz['phone']);}
                if ($myrow_m_zakaz['email']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].=''.$myrow_m_zakaz['email'];}
                if ($myrow_m_zakaz['adress']!=''){if ($myrow_i_contr_org['full_name']!=''){$myrow_i_contr_org['full_name'].=', ';}$myrow_i_contr_org['full_name'].=''.$myrow_m_zakaz['adress'];}
            
            }
            
            //директор если не указан - физ.лицо имя
            if ($myrow_i_contr_org['fio_director']==''){$myrow_i_contr_org['fio_director']=$myrow_m_zakaz['name'];}
            
            if ($myrow_i_contr_org['name']==''){
                $myrow_i_contr_org['na_osnovanii']='Паспорта';//организация если не указана - паспорта
            }
            else{
                if ($myrow_i_contr_org['na_osnovanii']==''){
                    $myrow_i_contr_org['na_osnovanii']='Устава';//основание если не указано - устава
                }
            }
            
            // огранизация есои не указана - физ-лицо имя
            if ($myrow_i_contr_org['name']==''){$myrow_i_contr_org['name']=$myrow_m_zakaz['name'];}
            
            //print_rf($myrow_i_contr_org);exit;
            
            
            $s_prop_arr=array();
            $s_prop_val_arr=array();
            $s_cat_s_prop_val_arr=array();
            //Массив доступных свойств
            $sql = "SELECT  s_prop.id,
                            s_prop.name,
                            s_prop_val.id,
                            s_prop_val.val,
                            m_zakaz_s_cat.s_cat_id
            
                				FROM s_prop, s_prop_val,s_cat_s_prop_val, m_zakaz_s_cat
                					WHERE s_prop.id=s_prop_val.s_prop_id
                                    AND s_prop_val.id=s_cat_s_prop_val.id2
                                    AND s_cat_s_prop_val.id1=m_zakaz_s_cat.s_cat_id
                                    AND m_zakaz_s_cat.m_zakaz_id='"._DB($nomer)."'
             ";
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $s_prop_arr[$myrow[0]]=$myrow[1];
                $s_prop_val_arr[$myrow[0]][$myrow[2]]=$myrow[3];
                $s_cat_s_prop_val_arr[$myrow[4]][]=$myrow[2];
            }
            
            //print_rf($s_prop_arr);
            //print_rf($s_prop_val_arr);
            //print_rf($s_cat_s_prop_val_arr);
            
            //товар
            $items_arr=array();
            $items_arr['id']=array();
            $items_arr['name']=array();
            $items_arr['price']=array();
            $items_arr['kolvo']=array();
            $items_arr['img']=array();
            $items_arr['a_admin_id']=array();
            $items_arr['tip']=array();
            $items_arr['i_class_unit_name']=array();
            $items_arr['i_class_unit_id']=array();
            $items_arr['summ']=array();
            
            $sql = "SELECT  s_cat.id,
                            s_cat.name,
                            m_zakaz_s_cat.price,
                            m_zakaz_s_cat.kolvo,
                            s_cat.tip,
                            s_cat.i_class_unit_id,
                            (SELECT IF(COUNT(*)>0,i_class_unit.rus_name1,'') FROM i_class_unit WHERE i_class_unit.id=s_cat.i_class_unit_id) AS i_class_unit_name,
                            (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.row_id=s_cat.id AND a_photo.a_menu_id='7' AND a_photo.tip='Основное' ORDER BY a_photo.sid LIMIT 1) AS img,
                            (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM m_zakaz_s_cat_a_admin_i_post, a_admin_i_post, a_admin
                                WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id 
                                AND m_zakaz_s_cat_a_admin_i_post.id2=a_admin_i_post.id
                                AND a_admin.id=a_admin_i_post.id1) AS a_admin_id
                
                				FROM m_zakaz_s_cat, s_cat
                					WHERE m_zakaz_s_cat.s_cat_id=s_cat.id
                                    AND m_zakaz_s_cat.m_zakaz_id='"._DB($nomer)."'
                                    
                                    ORDER BY m_zakaz_s_cat.id
             ";
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);$i=0;
            $all_sum=0;
            for ($myrow = mysql_fetch_assoc($res); $myrow==true; $myrow = mysql_fetch_assoc($res))
            {
                //print_rf($myrow);
                $items_arr['id'][$i]=$myrow['id'];
                $items_arr['name'][$i]=$myrow['name'];
                $items_arr['price'][$i]=$myrow['price'];
                $items_arr['kolvo'][$i]=$myrow['kolvo'];
                $items_arr['img'][$i]=$myrow['img'];
                $items_arr['tip'][$i]=$myrow['tip'];
                $items_arr['a_admin_id'][$i]=$myrow['a_admin_id'];
                $items_arr['i_class_unit_name'][$i]=$myrow['i_class_unit_name'];
                $items_arr['i_class_unit_id'][$i]=$myrow['i_class_unit_id'];
                $items_arr['summ'][$i]=$myrow['price']*$myrow['kolvo'];
                $all_sum=$all_sum+$items_arr['summ'][$i];
                $i++;
            }
            $all_kol=count($items_arr['id']);
            $sum_txt=mb_ufirst(trim(num2str($all_sum)));
      
            //print_rf($items_arr);
            //***********************************************************************************************************
            
            //ВЫВОИМ ДОКУМЕНТЫ
            if ($file_name=='docx_schet' or $file_name=='schet_print'){
                
                //строка word для товара
                $tbl_tr='<w:tr w:rsidR="00AB048F" w:rsidRPr="00095BB8" w:rsidTr="00496484" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                .'<w:trPr><w:trHeight w:val="28" /></w:trPr><w:tc><w:tcPr><w:tcW w:w="696" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:tcMar><w:top w:w="57" w:type="dxa" /><w:bottom w:w="57" w:type="dxa" /></w:tcMar><w:hideMark /></w:tcPr><w:p w:rsidR="00AB048F" w:rsidRPr="00AB048F" w:rsidRDefault="00AB048F" w:rsidP="003B5EAE"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tov_nom</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4253" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:tcMar><w:top w:w="57" w:type="dxa" /><w:bottom w:w="57" w:type="dxa" /></w:tcMar><w:hideMark /></w:tcPr><w:p w:rsidR="00AB048F" w:rsidRPr="00AB048F" w:rsidRDefault="00AB048F" w:rsidP="003B5EAE"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tov_name</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1119" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:tcMar><w:top w:w="57" w:type="dxa" /><w:bottom w:w="57" w:type="dxa" /></w:tcMar><w:hideMark /></w:tcPr><w:p w:rsidR="00AB048F" w:rsidRPr="00AB048F" w:rsidRDefault="00AB048F" w:rsidP="003B5EAE"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tov_kol</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1094" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:tcMar><w:top w:w="57" w:type="dxa" /><w:bottom w:w="57" w:type="dxa" /></w:tcMar><w:hideMark /></w:tcPr><w:p w:rsidR="00AB048F" w:rsidRPr="00AB048F" w:rsidRDefault="00AB048F" w:rsidP="003B5EAE"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tov_ed</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1257" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:tcMar><w:top w:w="57" w:type="dxa" /><w:bottom w:w="57" w:type="dxa" /></w:tcMar><w:hideMark /></w:tcPr><w:p w:rsidR="00AB048F" w:rsidRPr="00AB048F" w:rsidRDefault="00AB048F" w:rsidP="003B5EAE"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tov_price</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1342" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:tcMar><w:top w:w="57" w:type="dxa" /><w:bottom w:w="57" w:type="dxa" /></w:tcMar><w:hideMark /></w:tcPr><w:p w:rsidR="00AB048F" w:rsidRPr="00AB048F" w:rsidRDefault="00AB048F" w:rsidP="003B5EAE"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tov_summ</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc></w:tr>';
                
                $table_='';$i=1;
                foreach($items_arr['id'] as $key => $s_cat_id){
                    $txt_=$tbl_tr;
                    $txt_ = str_replace("tov_nom",$i,$txt_);
                    $txt_ = str_replace("tov_name",$items_arr['name'][$key],$txt_);
                    $txt_ = str_replace("tov_kol",$items_arr['kolvo'][$key],$txt_);
                    $txt_ = str_replace("tov_ed",$items_arr['i_class_unit_name'][$key],$txt_);
                    $txt_ = str_replace("tov_price",$items_arr['price'][$key],$txt_);
                    $txt_ = str_replace("tov_summ",$items_arr['summ'][$key],$txt_);
                    $table_.=$txt_;
                    $i++;
                }
                
                    
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $word = new docxGenerator($file_docs); 
                $word->val('<w:t>Сумма</w:t></w:r></w:p></w:tc></w:tr></w:tbl>', '<w:t>Сумма</w:t></w:r></w:p></w:tc></w:tr>'.$table_.'</w:tbl>');
                $word->val('bik_poluch', $myrow_m_zakaz['i_tp_i_contr_org_bik']);
                $word->val('bank_poluch',$myrow_m_zakaz['i_tp_i_contr_org_bank']);
                $word->val('schet_poluch',$myrow_m_zakaz['i_tp_i_contr_org_kschet']);
                $word->val('k_schet',$myrow_m_zakaz['i_tp_i_contr_org_schet']);
                $word->val('tip_firm','');
                $word->val('firm_name',$myrow_m_zakaz['i_tp_i_contr_org_name']);
                $word->val('inn',$myrow_m_zakaz['i_tp_i_contr_org_inn']);
                $word->val('kpp',$myrow_m_zakaz['i_tp_i_contr_org_kpp']);
                $word->val('nom_schet',$nomer);
                $word->val('dat_schet',data_convert_for_user($myrow_m_zakaz['data']));
                $word->val('postavchik',$myrow_m_zakaz['i_tp_full_name']);
                $word->val('pokupatel',$myrow_i_contr_org['full_name']);
                $word->val('tov_all',number_format($all_sum,2,',',' '));
                $word->val('tov_nds',number_format(0,0,',',' '));
                $word->val('tov_al2',number_format($all_sum,2,',',' '));
                $word->val('tovkolall',number_format($all_kol,0,',',' '));
                $word->val('tov_all',number_format($all_sum,2,',',' '));
                $word->val('tovalltxt',$sum_txt);
                $word->val('tipfirmn',$myrow_m_zakaz['i_tp_i_contr_org_tip_director']);
                $word->save($filename);
                
                
                //********************************************* C ПЕЧАТЬЮ
                if ($file_name=='schet_print'){
                    
                    if ($myrow_m_zakaz['i_tp_i_contr_org_inn']==''){echo 'Не определен ИНН организации <a href="?inc=i_contr_org&com=_change&nomer='.$myrow_m_zakaz['i_contr_org_id'].'">'.$myrow_m_zakaz['i_tp_i_contr_org_name'].'</a>!';@unlink($filename);exit;}
                    $sql_img = "SELECT IF(COUNT(*)>0,a_photo.img,'') 
                        				FROM a_photo
                        					WHERE a_photo.row_id='"._DB($myrow_m_zakaz['i_tp_i_contr_org_id'])."'
                                            AND a_photo.a_menu_id='41'
                                            AND a_photo.comments='"._DB($myrow_m_zakaz['i_tp_i_contr_org_inn'])."'
                                           
                     ";
                    $res_img = mysql_query($sql_img) or die(mysql_error().'<br/>'.$sql_img);
                    $myrow_img = mysql_fetch_array($res_img);
                    if ($myrow_img[0]==''){
                        echo 'Не загружена печать организации <a href="?inc=i_contr_org&com=_change&nomer='.$myrow_m_zakaz['i_contr_org_id'].'">'.$myrow_m_zakaz['i_tp_i_contr_org_name'].'</a><br />или в комментариях фотографии с печатью не указан ИНН!';
                        @unlink($filename);
                        exit;
                    }
                    if (!file_exists('../i/i_contr_org/original/'.$myrow_img[0])){
                        echo 'Не найден файл (../i/i_contr_org/original/'.$myrow_img[0].') с фотографией печати организации <a href="?inc=i_contr_org&com=_change&nomer='.$myrow_m_zakaz['i_contr_org_id'].'">'.$myrow_m_zakaz['i_tp_i_contr_org_name'].'</a><br />или в комментариях фотографии с печатью не указан ИНН!';
                        @unlink($filename);
                        exit;
                    }
                    
                    $zip = new ZipArchive;
                    //echo $filename.'='.!file_exists($filename);exit;
                    if ($zip->open($filename) === TRUE) {
                
                    	$zip->deleteName('word/media/image1.png');
                        $zip->addFile('../i/i_contr_org/original/'.$myrow_img[0],'word/media/image1.png');
                        $zip->close();
                    }
                }
            }
            
            
            //********************************************* Акт выполненных работ
            if ($file_name=='docx_act'){
                $tbl_tr='<w:tr w:rsidR="001D156D" w:rsidTr="00481C72" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:trPr><w:trHeight w:val="420" /></w:trPr><w:tc><w:tcPr><w:tcW w:w="827" w:type="dxa" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="001D156D" w:rsidRDefault="001D156D" w:rsidP="00506BBB"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr></w:pPr><w:r w:rsidRPr="00506BBB"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr>'
                        .'<w:t>number</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4474" w:type="dxa" /><w:vAlign w:val="left" /></w:tcPr><w:p w:rsidR="001D156D" w:rsidRDefault="00586EFD" w:rsidP="00506BBB"><w:pPr><w:jc w:val="left" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr>'
                        .'<w:t>tov</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1250" w:type="dxa" /><w:vAlign w:val="right" /></w:tcPr><w:p w:rsidR="001D156D" w:rsidRDefault="001D156D" w:rsidP="00506BBB"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr></w:pPr><w:r w:rsidRPr="00506BBB"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr>'
                        .'<w:t>kol</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1287" w:type="dxa" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="001D156D" w:rsidRPr="00506BBB" w:rsidRDefault="001D156D" w:rsidP="00506BBB"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr></w:pPr><w:r w:rsidRPr="001D156D"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr>'
                        .'<w:t>ed</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1373" w:type="dxa" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="001D156D" w:rsidRDefault="001D156D" w:rsidP="00506BBB"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr></w:pPr><w:r w:rsidRPr="00506BBB"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr>'
                        .'<w:t>price</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1471" w:type="dxa" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="001D156D" w:rsidRDefault="001D156D" w:rsidP="00506BBB"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr></w:pPr><w:r w:rsidRPr="00506BBB"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /></w:rPr>'
                        .'<w:t>sum</w:t></w:r></w:p></w:tc></w:tr>';

                
                $table_='';$i=1;
                $all_sum_usl=0;
                $all_kol_usl=0;
                
                foreach($items_arr['id'] as $key => $s_cat_id){
                    
                    if ($items_arr['tip'][$key]=='Услуга'){
                        $txt_=$tbl_tr;
                        $txt_ = str_replace("number",$i,$txt_);
                        $txt_ = str_replace("tov",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("kol",$items_arr['kolvo'][$key],$txt_);
                            $all_kol_usl=$all_kol_usl+$items_arr['kolvo'][$key];
                        $txt_ = str_replace("ed",$items_arr['i_class_unit_name'][$key],$txt_);
                        $txt_ = str_replace("price",$items_arr['price'][$key],$txt_);
                        $txt_ = str_replace("sum",$items_arr['summ'][$key],$txt_);
                            $all_sum_usl=$all_sum_usl+$items_arr['summ'][$key];
                        $table_.=$txt_;
                        $i++;
                    }
                }
                
                $sum_txt_usl=mb_ufirst(trim(num2str($all_sum_usl)));
            
                if ($table_==''){
                    echo 'В данном заказе не услуг!';exit;
                }
                
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $word = new docxGenerator($file_docs);
                $word->val('<w:t>Сумма</w:t>
            </w:r>
          </w:p>
        </w:tc>
      </w:tr>', '<w:t>Сумма</w:t></w:r></w:p></w:tc></w:tr>'.$table_);
        $ddt=date('d.m.Y',strtotime($myrow_m_zakaz['data']));
        if ($myrow_m_zakaz['data_vidachi']!='' and $myrow_m_zakaz['data_vidachi']!='0000-00-00 00:00:00'){
            $ddt=$myrow_m_zakaz['data_vidachi'];
        }
                $word->val('nomer', $nomer);
                $word->val('ddata',data_convert_for_user($ddt));
                $word->val('nds', number_format(0,0,',',' '));
                $word->val('summ', number_format($all_sum_usl,2,',',' '));
                $word->val('stext', $sum_txt_usl);
                $word->val('nubs', number_format($all_kol_usl,0,',',' '));
                $word->val('sutext', number_format($all_sum_usl,2,',',' '));
                $word->val('postavshik', $myrow_m_zakaz['i_tp_full_name']);
                $word->val('postn', $myrow_m_zakaz['i_tp_i_contr_org_name']);
                $word->val('pokupatel', $myrow_i_contr_org['full_name']);
                $word->val('pokup', $myrow_i_contr_org['name']);
                $word->val('tpname', $myrow_m_zakaz['i_tp_i_contr_org_fio_director']);
                $word->val('pokt', $myrow_i_contr_org['fio_director']);
                
                $word->save($filename);
            }
            //********************************************* ТОВАРНЫЙ ЧЕК
            if ($file_name=='docx_tovar_check'){
                $tbl_tr='<w:tr w:rsidR="00506BBB" w:rsidTr="00C24DAE" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:trPr><w:trHeight w:val="284" /></w:trPr><w:tc><w:tcPr><w:tcW w:w="817" w:type="dxa" /></w:tcPr><w:p w:rsidR="00506BBB" w:rsidRPr="00723B3A" w:rsidRDefault="00723B3A" w:rsidP="00506BBB"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr>'
                        .'<w:t>number</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5245" w:type="dxa" /></w:tcPr><w:p w:rsidR="00506BBB" w:rsidRPr="00723B3A" w:rsidRDefault="00723B3A"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr>'
                        .'<w:t>tov</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1417" w:type="dxa" /></w:tcPr><w:p w:rsidR="00506BBB" w:rsidRPr="00723B3A" w:rsidRDefault="00723B3A" w:rsidP="00506BBB"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr>'
                        .'<w:t>kol</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa" /></w:tcPr><w:p w:rsidR="00506BBB" w:rsidRPr="00723B3A" w:rsidRDefault="00723B3A" w:rsidP="00506BBB"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr>'
                        .'<w:t>price</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1643" w:type="dxa" /></w:tcPr><w:p w:rsidR="00506BBB" w:rsidRPr="00723B3A" w:rsidRDefault="00723B3A" w:rsidP="00506BBB"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="18" /><w:szCs w:val="18" /><w:lang w:val="en-US" /></w:rPr>'
                        .'<w:t>sum</w:t></w:r></w:p></w:tc></w:tr>';
                
                $table_='';$i=1;
                $all_sum_item=0;
                $all_kol_item=0;
                foreach($items_arr['id'] as $key => $s_cat_id){
                    
                    if ($items_arr['tip'][$key]=='Товар'){
                        $txt_=$tbl_tr;
                        $txt_ = str_replace("number",$i,$txt_);
                        $txt_ = str_replace("tov",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("kol",$items_arr['kolvo'][$key],$txt_);
                        $txt_ = str_replace("ed",$items_arr['i_class_unit_name'][$key],$txt_);
                        $txt_ = str_replace("price",$items_arr['price'][$key],$txt_);
                            $all_kol_item=$all_kol_item+$items_arr['kolvo'][$key];
                        $txt_ = str_replace("sum",$items_arr['summ'][$key],$txt_);
                            $all_sum_item=$all_sum_item+$items_arr['summ'][$key];
                        
                        
                        $table_.=$txt_;
                        $i++;
                    }
                }
              
                $sum_txt_item=mb_ufirst(trim(num2str($all_sum_item)));
                
                if ($table_==''){
                    echo 'В данном заказе не товаров!';exit;
                }
                
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $word = new docxGenerator($file_docs);
                $word->val('<w:t>Сумма</w:t>
            </w:r>
          </w:p>
        </w:tc>
      </w:tr>', '<w:t>Сумма</w:t></w:r></w:p></w:tc></w:tr>'.$table_);
      $ddt=date('d.m.Y',strtotime($myrow_m_zakaz['data']));
      if ($myrow_m_zakaz['data_vidachi']!='' and $myrow_m_zakaz['data_vidachi']!='0000-00-00 00:00:00'){
        $ddt=$myrow_m_zakaz['data_vidachi'];
      }
      
                $word->val('nomer', $nomer);
                $word->val('ddata', data_convert_for_user($ddt));
                $word->val('nds', number_format(0,0,',',' '));
                $word->val('summ', number_format($all_sum_item,2,',',' '));
                $word->val('stext', $sum_txt_item);
                $word->val('nubs', number_format($all_kol_item,0,',',' '));
                $word->val('sutext', number_format($all_sum_item,2,',',' '));
                $word->val('postavshik', $myrow_m_zakaz['i_tp_full_name']);
                $word->val('pokupatel', $myrow_i_contr_org['full_name']);
               
                $word->save($filename);
            }
            
            //********************************************* ТТН
            if ($file_name=='ttn'){
                $text_find='<w:p w:rsidR="006F6A8C" w:rsidRDefault="006F6A8C"/><w:sectPr w:rsidR="006F6A8C" w:rsidSect="00431597"><w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/><w:pgMar w:top="426" w:right="284" w:bottom="567" w:left="567" w:header="709" w:footer="709" w:gutter="0"/><w:cols w:space="708"/><w:docGrid w:linePitch="360"/></w:sectPr>';
                $text_head='<w:p w:rsidR="00E41930" w:rsidRPr="00D620F5" w:rsidRDefault="00E41930" w:rsidP="006F2A16" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:pPr><w:tabs><w:tab w:val="left" w:pos="14318" /><w:tab w:val="right" w:pos="15987" /></w:tabs><w:spacing w:after="0" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:i /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:r w:rsidRPr="004079C0"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:i /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:lastRenderedPageBreak /><w:t xml:space="preserve">Страница </w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:i /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>'
                .'numstr</w:t></w:r></w:p>'
                .'<w:tbl xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tblPr><w:tblStyle w:val="a3" /><w:tblW w:w="15617" w:type="dxa" /><w:tblLayout w:type="fixed" /><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1" /></w:tblPr><w:tblGrid><w:gridCol w:w="814" /><w:gridCol w:w="2546" /><w:gridCol w:w="714" /><w:gridCol w:w="990" /><w:gridCol w:w="850" /><w:gridCol w:w="851" /><w:gridCol w:w="853" /><w:gridCol w:w="995" /><w:gridCol w:w="993" /><w:gridCol w:w="1134" /><w:gridCol w:w="872" /><w:gridCol w:w="1112" /><w:gridCol w:w="992" /><w:gridCol w:w="900" /><w:gridCol w:w="1001" /></w:tblGrid><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="814" w:type="dxa" /><w:vMerge w:val="restart" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Но-</w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>мер</w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">по </w:t></w:r><w:proofErr w:type="spellStart" /><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>по</w:t></w:r><w:proofErr w:type="spellEnd" /><w:proofErr w:type="gramEnd" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>-</w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>рядку</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3260" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Товар</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Единица измерения</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="851" w:type="dxa" /><w:vMerge w:val="restart" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Вид упаковки</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1848" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Количество</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:vMerge w:val="restart" /><w:tcBorders><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Масса брутто</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:vMerge w:val="restart" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">Количество </w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">(масса </w:t></w:r><w:proofErr w:type="gramEnd" /></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>нетто)</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:vMerge w:val="restart" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Цена,</w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>руб. коп.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:vMerge w:val="restart" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">Сумма </w:t></w:r><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>без</w:t></w:r><w:proofErr w:type="gramEnd" /></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>учета НДС,</w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>руб. коп.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1892" w:type="dxa" /><w:gridSpan w:val="2" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>НДС</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:vMerge w:val="restart" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">Сумма </w:t></w:r><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>с</w:t></w:r><w:proofErr w:type="gramEnd" /></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">учетом </w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">НДС, </w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>руб. коп.</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="814" w:type="dxa" /><w:vMerge /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2546" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>наименование, характеристика, сорт, артикул товара</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>код</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="990" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>наим</w:t></w:r><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>е</w:t></w:r><w:proofErr w:type="spellEnd" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>-</w:t></w:r><w:proofErr w:type="gramEnd" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:proofErr w:type="spellStart" /><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>нование</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="850" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>код по ОКЕИ</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="851" w:type="dxa" /><w:vMerge /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="853" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>в одном месте</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="995" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>мест,</w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>штук</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:vMerge /><w:tcBorders><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:vMerge /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:vMerge /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:vMerge /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>ставка, %</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa" /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">сумма, </w:t></w:r></w:p><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E33177"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>руб. коп.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:vMerge /><w:vAlign w:val="center" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="814" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>1</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2546" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>2</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>3</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="990" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>4</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="850" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>5</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="851" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>6</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="853" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>7</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="995" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>8</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>9</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>10</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders>
                </w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>11</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>12</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>13</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>14</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr><w:t>15</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';
                
                //строка товара
                $text_tr='<w:tbl xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tblPr><w:tblStyle w:val="a3" /><w:tblW w:w="15617" w:type="dxa" /><w:tblLayout w:type="fixed" /><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1" /></w:tblPr><w:tblGrid><w:gridCol w:w="813" /><w:gridCol w:w="2547" /><w:gridCol w:w="714" /><w:gridCol w:w="990" /><w:gridCol w:w="850" /><w:gridCol w:w="851" /><w:gridCol w:w="853" /><w:gridCol w:w="995" /><w:gridCol w:w="993" /><w:gridCol w:w="1134" /><w:gridCol w:w="872" /><w:gridCol w:w="1112" /><w:gridCol w:w="992" /><w:gridCol w:w="900" /><w:gridCol w:w="1001" /></w:tblGrid><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00D335B5"><w:tc><w:tcPr><w:tcW w:w="813" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00D02503"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:lastRenderedPageBreak /><w:t>tnom</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2547" w:type="dxa" /><w:tcBorders><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00D02503"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tmane</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tidcat</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="990" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tedizm</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="850" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tokei</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="851" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="853" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="995" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00D02503"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tkool</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00D02503"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tprr</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:tcBorders><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00D02503"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>titog</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>tnds</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="007D7780" w:rsidRDefault="00E41930" w:rsidP="00D02503"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="14" /><w:szCs w:val="14" /><w:lang w:val="en-US" /></w:rPr><w:t>taallsum</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc></w:tr></w:tbl>';
                
                
                //перед окончанием документа
                $text_itogo='<w:tbl xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tblPr><w:tblStyle w:val="a3" /><w:tblW w:w="15617" w:type="dxa" /><w:tblLayout w:type="fixed" /><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1" /></w:tblPr><w:tblGrid><w:gridCol w:w="7618" /><w:gridCol w:w="995" /><w:gridCol w:w="993" /><w:gridCol w:w="1134" /><w:gridCol w:w="872" /><w:gridCol w:w="1112" /><w:gridCol w:w="992" /><w:gridCol w:w="900" /><w:gridCol w:w="1001" /></w:tblGrid><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="7618" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:lastRenderedPageBreak /><w:t>Итого</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="995" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:tcBorders><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00C01008" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>
                koll</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Х</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00900562" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>
                summ</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /><w:tcBorders><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Х</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E6143B" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>
                summ</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc></w:tr></w:tbl>';
                
                //окончание документа
                $text_footer='<w:tbl xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tblPr><w:tblStyle w:val="a3" /><w:tblW w:w="15617" w:type="dxa" /><w:tblLayout w:type="fixed" /><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1" /></w:tblPr><w:tblGrid><w:gridCol w:w="1082" /><w:gridCol w:w="860" /><w:gridCol w:w="225" /><w:gridCol w:w="767" /><w:gridCol w:w="426" /><w:gridCol w:w="425" /><w:gridCol w:w="289" /><w:gridCol w:w="1276" /><w:gridCol w:w="425" /><w:gridCol w:w="1843" /><w:gridCol w:w="284" /><w:gridCol w:w="711" /><w:gridCol w:w="281" /><w:gridCol w:w="712" /><w:gridCol w:w="422" /><w:gridCol w:w="425" /><w:gridCol w:w="158" /><w:gridCol w:w="129" /><w:gridCol w:w="872" /><w:gridCol w:w="403" /><w:gridCol w:w="598" /><w:gridCol w:w="111" /><w:gridCol w:w="284" /><w:gridCol w:w="283" /><w:gridCol w:w="324" /><w:gridCol w:w="101" /><w:gridCol w:w="900" /><w:gridCol w:w="1001" /></w:tblGrid><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="7618" w:type="dxa" /><w:gridSpan w:val="10" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:lastRenderedPageBreak /><w:t>Всего по накладной</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="995" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="001E3D8E" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>amast</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="001E3D8E" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>akolt</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Х</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00900562" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>asumt</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Х</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E6143B" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>asumt</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3608" w:type="dxa" /><w:gridSpan w:val="6" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">Товарная накладная имеет приложение </w:t></w:r><w:proofErr w:type="gramStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>на</w:t></w:r><w:proofErr w:type="gramEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4965" w:type="dxa" /><w:gridSpan w:val="9" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00C819DC" w:rsidRDefault="00C819DC" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>kollist</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00C819DC" w:rsidP="00C819DC"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:proofErr w:type="gramStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>листах</w:t></w:r><w:proofErr w:type="gramEnd" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve"> </w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1112" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1618" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>и содержит</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="6955" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00614C5C" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>kolzap</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4877" w:type="dxa" /><w:gridSpan w:val="10" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>порядковых номеров записей</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1193" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders>
                </w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00A41668" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r w:rsidRPr="00A41668"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>прописью</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="284" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="711" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="872" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1002" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2002" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge w:val="restart" /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="006B5179" w:rsidRPr="00E33177" w:rsidTr="003A29A1"><w:trPr><w:trHeight w:val="44" /></w:trPr><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1193" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge w:val="restart" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="00A41668" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="00E33177" w:rsidRDefault="006B5179" w:rsidP="006B5179"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00A41668"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">    Масса груза (нетто)</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4678" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="324" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2002" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="004079C0" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1193" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00A41668" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="284" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="711" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00A41668" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>прописью</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1005" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1002" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2002" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge w:val="restart" /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="006B5179" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:trPr><w:trHeight w:val="167" /></w:trPr><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1193" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Всего мест</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="00A41668" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="00E33177" w:rsidRDefault="006B5179" w:rsidP="006B5179"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00A41668"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">    Масса груза (брутто)</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4678" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="00256B12" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>allkg</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="324" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2002" w:type="dxa" /><w:gridSpan w:val="3" /><w:vMerge /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="12" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="006B5179" w:rsidRPr="004079C0" w:rsidRDefault="006B5179" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1193" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00A41668" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>прописью</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="284" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="711" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00A41668" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r w:rsidRPr="00A41668"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>прописью</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="847" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1002" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="12" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr>
                <w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="3785" w:type="dxa" /><w:gridSpan w:val="6" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">Приложение (паспорта, сертификаты и т.п.) </w:t></w:r><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>на</w:t></w:r><w:proofErr w:type="gramEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:proofErr w:type="gramStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>листах</w:t></w:r><w:proofErr w:type="gramEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="284" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="711" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00C237E0"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>По доверенности №</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2160" w:type="dxa" /><w:gridSpan w:val="5" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="395" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>от</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2609" w:type="dxa" /><w:gridSpan w:val="5" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1082" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1085" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1193" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" />
                <w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>прописью</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="284" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="711" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1002" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="3785" w:type="dxa" /><w:gridSpan w:val="6" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Всего отпущено  на сумму</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1990" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="284" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="711" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="281" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>выданной</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5164" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="8613" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00FB5EDA" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t>alsmm</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="7004" w:type="dxa" /><w:gridSpan w:val="16" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00C237E0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:ind w:left="1732" /><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r w:rsidRPr="00C237E0"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t xml:space="preserve">кем, кому (организация, должность, фамилия, и. </w:t></w:r><w:proofErr w:type="gramStart" /><w:r w:rsidRPr="00C237E0"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>о</w:t></w:r><w:proofErr w:type="gramEnd" /><w:r w:rsidRPr="00C237E0"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>.)</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="00FB7523"><w:tc><w:tcPr><w:tcW w:w="8613" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>прописью</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5164" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1942" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Отпуск груза разрешил</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1418" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5164" w:type="dxa" /><w:gridSpan w:val="12" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00256B12" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1942" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1418" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>должность</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>подпись</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>расшифровка подписи</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1325" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1001" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="2934" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Главный (старший) бухгалтер</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="426" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="281" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1559" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00C237E0"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Груз принял</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1901" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="2934" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="426" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" />
                <w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>подпись</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>расшифровка подписи</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E5101C" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>должность</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E5101C" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E5101C" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r w:rsidRPr="00E5101C"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>подпись</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1901" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E5101C" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>расшифровка подписи</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1942" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Отпуск груза произвел</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1418" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="281" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1559" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E5101C"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>Груз получил</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1901" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1942" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1418" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>должность</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>подпись</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>расшифровка подписи</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="281" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1559" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E5101C"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>грузополучатель</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>должность</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r w:rsidRPr="00E5101C"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>подпись</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1901" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr><w:t>расшифровка подписи</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1942" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:b /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1418" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2838" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1901" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidTr="006B5179"><w:tc><w:tcPr><w:tcW w:w="1942" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>М.П.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1418" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="center" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="12" /><w:szCs w:val="12" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="714" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve">«    </w:t></w:r><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>»</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3263" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00640294" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>20      года</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1840" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>М.П.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1159" w:type="dxa" /><w:gridSpan w:val="3" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="008875E5" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>«</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr><w:t xml:space="preserve">        </w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="403" w:type="dxa" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="008875E5" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:r w:rsidRPr="00640294"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>»</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:gridSpan w:val="4" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="425" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="00E33177" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t>20</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1901" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="nil" /><w:left w:val="nil" /><w:bottom w:val="nil" /><w:right w:val="nil" /></w:tcBorders></w:tcPr><w:p w:rsidR="00E41930" w:rsidRPr="004079C0" w:rsidRDefault="00E41930" w:rsidP="00FB7523"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /></w:rPr><w:t xml:space="preserve"> года</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';
                
                //конец страницы
                $text_end_page='<w:p w:rsidR="00220E22" w:rsidRDefault="00220E22" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="10" /><w:szCs w:val="12" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="10" /><w:szCs w:val="12" /></w:rPr><w:br w:type="page" /></w:r></w:p>';
                
                $str=1;//страница
                $text_all=$text_head;
                $text_all = str_replace("numstr",$str,$text_all);
                $all_summ=0;$kol_=0;//всего
                $all_summ_str=0;$kol_str=0;//НА странице
                foreach($items_arr['id'] as $key => $s_cat_id){
                    
                    if ($items_arr['tip'][$key]=='Товар'){
                        
                        if ($i%10==3)
                        {
                            $str++;
                            $text_all.=$text_itogo.$text_end_page.$text_head;
                            $text_all = str_replace("numstr",$str,$text_all);
                            $text_all = str_replace("koll",number_format($kol_str,3,',',' '),$text_all);
                            $text_all = str_replace("summ",number_format($all_summ_str,3,',',' '),$text_all);
                            
                            $all_summ_str=0;    // сумма на странице
                            $kol_str=0;         // количество товаров на странице
                        }
                        
                        $txt_=$text_tr;                                     // задаем переменную
                        
                        $kol_=$kol_+$items_arr['kolvo'][$key];                   //общее количество товара
                        $kol_str=$kol_str+$items_arr['kolvo'][$key];             // количество товаров на странице
                        $all_summ=$all_summ+$items_arr['summ'][$key];                          //общая сумма
                        $all_summ_str=$all_summ_str+$items_arr['summ'][$key];                  // сумма на странице
                        
                        
                        $txt_ = str_replace("tnom",$i,$txt_);
                        $txt_ = str_replace("tmane",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("tidcat",$s_cat_id,$txt_);
                        $txt_ = str_replace("tedizm",$items_arr['i_class_unit_name'][$key],$txt_);
                        $txt_ = str_replace("tokei",$items_arr['i_class_unit_id'][$key],$txt_);
                        $txt_ = str_replace("tkool",number_format($items_arr['kolvo'][$key],3,',',' '),$txt_);
                        $txt_ = str_replace("tprr",number_format($items_arr['price'][$key],2,',',' '),$txt_);
                        $txt_ = str_replace("titog",number_format($items_arr['summ'][$key],2,',',' '),$txt_);
                        $txt_ = str_replace("tnds","Без НДС",$txt_);
                        $txt_ = str_replace("taallsum",number_format($items_arr['summ'][$key],2,',',' '),$txt_);
                        $text_all.=$txt_;
                        $i++;
                        
                    }
                }
                if ($kol_==0){
                    echo 'В данном заказе не товаров!';exit;
                }
                
                $text_all.=$text_itogo.$text_footer;
                $text_all = str_replace("kollist",$str,$text_all);
                $text_all = str_replace("koll",number_format($kol_str,3,',',' '),$text_all);
                $text_all = str_replace("summ",number_format($all_summ_str,2,',',' '),$text_all);
                $text_all = str_replace("akolt",number_format($kol_,3,',',' '),$text_all);
                $text_all = str_replace("asumt",number_format($all_summ,2,',',' '),$text_all);
                
                $text_all = str_replace("kolzap",mb_ufirst(trim(numer2str($kol_)),'utf-8'),$text_all);
                $text_all = str_replace("alsmm",mb_ufirst(trim(num2str($all_summ)),'utf-8'),$text_all);
                
                $text_all = str_replace("allkg",'',$text_all);
                $text_all = str_replace("amast",'',$text_all);    
                
                
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                /*
                    $word->val('orgotpr', mb_convert_encoding ($data['orgotpr'] ,"UTF-8" , "Windows-1251" ));
                    $word->val('strukpod', mb_convert_encoding ($data['strukpod'] ,"UTF-8" , "Windows-1251" ));
                    $word->val('gruzpoluch', mb_convert_encoding ($data['gruzpoluch'] ,"UTF-8" , "Windows-1251" ));
                    $word->val('osnovanie', mb_convert_encoding ($data['osnovanie'] ,"UTF-8" , "Windows-1251" ));

                */
                $word = new docxGenerator($file_docs);
                $word->val($text_find, $text_all.$text_find);
                $word->val('nomer', $nomer);
                $word->val('ddata', date('d.m.Y',strtotime($myrow_m_zakaz['data'])));
                $word->val('orgotpr', $myrow_m_zakaz['i_tp_full_name']);
                $word->val('strukpod',$myrow_m_zakaz['i_tp_name']);
                $word->val('pokupatel', $myrow_i_contr_org['full_name']);
                $word->val('postavchik', $myrow_m_zakaz['i_tp_full_name']);
                $word->val('gruzpoluch', '');
                $word->val('osnovanie', '');
               
                $word->save($filename);
            }
            
            //********************************************* ТТН
            if ($file_name=='sf'){
                $table_='';
                $tbl_tr='<w:tr w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidTr="00E9755E" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:trPr><w:gridAfter w:val="3" /><w:wAfter w:w="4589" w:type="dxa" /><w:trHeight w:val="60" /></w:trPr><w:tc><w:tcPr><w:tcW w:w="2552" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="00705DBF" w:rsidRDefault="00705DBF" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tovname</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="709" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="001B210E" w:rsidRDefault="001B210E" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>tovkod</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1326" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="001B210E" w:rsidRDefault="001B210E" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>ed_izm</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="992" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="003C5860" w:rsidRDefault="003C5860" w:rsidP="003C5860"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>kol_vo</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1367" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="003068BB" w:rsidRDefault="003068BB" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>price</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1276" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="00F57091" w:rsidRDefault="00F57091" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>sumtov</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1171" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidRDefault="009A0D8F" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r w:rsidRPr="009A0D8F"><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>без акциза</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1097" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidRDefault="009A0D8F" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r w:rsidRPr="009A0D8F"><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>без НДС</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidRDefault="009A0D8F" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r w:rsidRPr="009A0D8F"><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>без НДС</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1417" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:noWrap /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="00D80A09" w:rsidRDefault="00D80A09" w:rsidP="00D80A09"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:jc w:val="right" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:val="en-US" w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>sumtov</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="709" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidRDefault="009A0D8F" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>--</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="746" w:type="dxa" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidRDefault="009A0D8F" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>--</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="993" w:type="dxa" /><w:gridSpan w:val="2" /><w:tcBorders><w:top w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:left w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:bottom w:val="single" w:sz="8" w:space="0" w:color="auto" /><w:right w:val="single" w:sz="8" w:space="0" w:color="auto" /></w:tcBorders><w:shd w:val="clear" w:color="auto" w:fill="auto" /><w:hideMark /></w:tcPr><w:p w:rsidR="009A0D8F" w:rsidRPr="009A0D8F" w:rsidRDefault="009A0D8F" w:rsidP="009A0D8F"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto" /><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr></w:pPr><w:r w:rsidRPr="009A0D8F"><w:rPr><w:rFonts w:ascii="Arial" w:eastAsia="Times New Roman" w:hAnsi="Arial" w:cs="Arial" /><w:sz w:val="16" /><w:szCs w:val="16" /><w:lang w:eastAsia="ru-RU" /></w:rPr>'
                .'<w:t>--</w:t></w:r></w:p></w:tc></w:tr>';
    
                $kol_=0;$all_summ=0;
                foreach($items_arr['id'] as $key => $s_cat_id){
                
                    $kol_=$kol_+$items_arr['kolvo'][$key];                   //общее количество товара
                    $all_summ=$all_summ+$items_arr['summ'][$key];                          //общая сумма
                        
                
                    $txt_=$tbl_tr;
                    
                    $txt_ = str_replace("tovname",$items_arr['name'][$key],$txt_);
                    $txt_ = str_replace("tovkod",$items_arr['i_class_unit_id'][$key],$txt_);
                    $txt_ = str_replace("ed_izm",$items_arr['i_class_unit_name'][$key],$txt_);
                    $txt_ = str_replace("kol_vo",number_format($items_arr['kolvo'][$key],3,',',' '),$txt_);
                    $txt_ = str_replace("price",number_format($items_arr['price'][$key],2,',',' '),$txt_);
                    $txt_ = str_replace("sumtov",number_format($items_arr['summ'][$key],2,',',' '),$txt_);
                    
                    $table_.=$txt_;
                    
                }
                $sum_txt=mb_ufirst(trim(num2str($all_summ)));
                
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
               
                $word = new docxGenerator($file_docs);
                
                $word->val('<w:t>11</w:t></w:r></w:p></w:tc></w:tr>', '<w:t>11</w:t></w:r></w:p></w:tc></w:tr>'.$table_.'');
                $word->val('nomer', $nomer);
                $word->val('ddata', date('d.m.Y',strtotime($myrow_m_zakaz['data'])));
                $word->val('postavchik', $myrow_m_zakaz['i_tp_i_contr_org_name']);
                $word->val('adress', $myrow_m_zakaz['i_tp_i_contr_org_u_adress']);
                $word->val('innk', $myrow_m_zakaz['i_tp_i_contr_org_inn'].'/'. $myrow_m_zakaz['i_tp_i_contr_org_kpp']);
                $word->val('pokupatel', $myrow_i_contr_org['name']);
                $word->val('adpokup', $myrow_i_contr_org['u_adress']);
                $word->val('inp', $myrow_i_contr_org['inn'].'/'.$myrow_i_contr_org['kpp']);

                $word->val('summ', $all_summ);
                $word->val('nds', '0.00');
                
                if ($myrow_m_zakaz['i_tp_i_contr_org_na_osnovanii']=='Устава'){
                    $word->val('fioorg', $myrow_m_zakaz['i_tp_i_contr_org_fio_director']);
                    $word->val('fioip', '');
                    $word->val('svedip', '');
                }
                else{
                    $word->val('fioorg', '');
                    $word->val('fioip', $myrow_m_zakaz['i_tp_i_contr_org_fio_director']);
                    $word->val('svedip', '');
                }
                
                
               
                $word->save($filename);
                
            }
            
            //********************************************* ПОЧТОВЫЙ БЛАНК
            if ($file_name=='post_form'){
                $table_='';
                
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
               
                $word = new docxGenerator($file_docs);
                
                $sql_dostavka = "SELECT  fio,
                                index_,
                                (SELECT IF(COUNT(*)>0,i_city.name,'') FROM i_city WHERE i_city.id=m_dostavka.i_city_id) AS i_city_name,
                                (SELECT IF(COUNT(*)>0,i_city.region,'') FROM i_city WHERE i_city.id=m_dostavka.i_city_id) AS i_city_region,
                                adress,
                                phone
                                
                				FROM m_dostavka 
                					WHERE m_dostavka.m_zakaz_id='"._DB($nomer)."'
                                    LIMIT 1
                	"; 
                
                $res_dostavka = mysql_query($sql_dostavka) or die(mysql_error().'<br/>'.$sql_dostavka);
                $myrow_dostavka = mysql_fetch_array($res_dostavka);
   
                
                $ind_arr=trim($myrow_m_zakaz['i_tp_index']);
                $ind2_arr=trim($myrow_dostavka['index_']);
                
                $phone_arr=trim($myrow_dostavka['phone']);
                    if (mb_substr($phone_arr,0,1,'UTF-8')=='8'){
                        $phone_arr=mb_substr($phone_arr,1,10,'UTF-8');
                    }
   
                
                $word->val('otkogo', $myrow_m_zakaz['i_tp_i_contr_org_fio_director']);
                $word->val('addres', $myrow_m_zakaz['i_tp_region_name'].', '.$myrow_m_zakaz['i_tp_city_name']);
                $word->val('adrres', $myrow_m_zakaz['i_tp_adress']);
                $word->val('komufio', $myrow_dostavka['fio']);
                $word->val('kommuad', $myrow_dostavka['i_city_region'].', '.$myrow_dostavka['i_city_name']);
                $word->val('kkomuad', $myrow_dostavka['adress']);
                $word->val('qqw', @$ind_arr[0]);
                $word->val('qqe', @$ind_arr[1]);
                $word->val('qqr', @$ind_arr[2]);
                $word->val('qqt', @$ind_arr[3]);
                $word->val('qqy', @$ind_arr[4]);
                $word->val('qqu', @$ind_arr[5]);
                
                $word->val('wwq', @$ind2_arr[0]);
                $word->val('wwe', @$ind2_arr[1]);
                $word->val('wwr', @$ind2_arr[2]);
                $word->val('wwt', @$ind2_arr[3]);
                $word->val('wwy', @$ind2_arr[4]);
                $word->val('wwu', @$ind2_arr[5]);
                
                $word->val('eeq', @$phone_arr[0]);
                $word->val('eew', @$phone_arr[1]);
                $word->val('eer', @$phone_arr[2]);
                $word->val('eet', @$phone_arr[3]);
                $word->val('eey', @$phone_arr[4]);
                $word->val('eeu', @$phone_arr[5]);
                $word->val('eei', @$phone_arr[6]);
                $word->val('eeo', @$phone_arr[7]);
                $word->val('eep', @$phone_arr[8]);
                $word->val('eea', @$phone_arr[9]);
                
                $word->save($filename);
                
            }
            
            
            //********************************************* //ГАРАНТИЯ
            if ($file_name=='garant_t'){
                $table_='';
                
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $table_t='<w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00E45607" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:szCs w:val="28" /></w:rPr><w:t>Товары</w:t></w:r></w:p><w:tbl xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tblPr><w:tblStyle w:val="a5" /><w:tblW w:w="10564" w:type="dxa" /><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1" /></w:tblPr><w:tblGrid><w:gridCol w:w="417" /><w:gridCol w:w="4454" /><w:gridCol w:w="624" /><w:gridCol w:w="3260" /><w:gridCol w:w="1809" /></w:tblGrid><w:tr w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidTr="00097E8F"><w:tc><w:tcPr><w:tcW w:w="417" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00097E8F"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>№</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4454" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00097E8F"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Товар</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="624" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00097E8F"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Кол.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3260" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00097E8F"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Серийный номер</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1809" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00097E8F"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Срок гарантии</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';
                $table_u='<w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00E45607" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:b /><w:sz w:val="28" /><w:szCs w:val="28" /></w:rPr></w:pPr></w:p><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00E45607" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:szCs w:val="28" /></w:rPr><w:t>Услуги</w:t></w:r></w:p><w:tbl xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tblPr><w:tblStyle w:val="a5" /><w:tblW w:w="10564" w:type="dxa" /><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1" /></w:tblPr><w:tblGrid><w:gridCol w:w="417" /><w:gridCol w:w="4454" /><w:gridCol w:w="624" /><w:gridCol w:w="3260" /><w:gridCol w:w="1809" /></w:tblGrid><w:tr w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidTr="002C0CC8"><w:tc><w:tcPr><w:tcW w:w="417" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>№</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4454" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Услуга</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="624" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Кол.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3260" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Мастер</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1809" w:type="dxa" /><w:shd w:val="clear" w:color="auto" w:fill="F2F2F2" w:themeFill="background1" w:themeFillShade="F2" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:jc w:val="center" /><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:b /><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>Гарантийный срок</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';
                $tr_t0='<w:tr w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidTr="00097E8F" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tc><w:tcPr><w:tcW w:w="417" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00E45607"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>nom_t</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4454" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="00E45607"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>name_t</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="624" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00E45607"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>kol_t</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3260" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="00E45607"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /><w:lang w:val="en-US" /></w:rPr><w:t>sn_t</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1809" w:type="dxa" /></w:tcPr><w:p w:rsidR="00E45607" w:rsidRPr="00097E8F" w:rsidRDefault="00E45607" w:rsidP="00E45607"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /><w:lang w:val="en-US" /></w:rPr><w:t>garant_t</w:t></w:r></w:p></w:tc></w:tr>';
                $tr_u0='<w:tr w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidTr="002C0CC8" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tc><w:tcPr><w:tcW w:w="417" w:type="dxa" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>nom_u</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4454" w:type="dxa" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>name_u</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="624" w:type="dxa" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr><w:t>kol_u</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3260" w:type="dxa" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /><w:lang w:val="en-US" /></w:rPr></w:pPr><w:proofErr w:type="spellStart" /><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /><w:lang w:val="en-US" /></w:rPr><w:t>master_u</w:t></w:r><w:proofErr w:type="spellEnd" /></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1809" w:type="dxa" /></w:tcPr><w:p w:rsidR="00097E8F" w:rsidRPr="00097E8F" w:rsidRDefault="00097E8F" w:rsidP="002C0CC8"><w:pPr><w:pStyle w:val="a3" /><w:tabs><w:tab w:val="left" w:pos="5521" /></w:tabs><w:spacing w:before="0" w:after="120" /><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /></w:rPr></w:pPr><w:r w:rsidRPr="00097E8F"><w:rPr><w:sz w:val="20" /><w:szCs w:val="28" /><w:lang w:val="en-US" /></w:rPr><w:t>garant_u</w:t></w:r></w:p></w:tc></w:tr>';
                
                
                
                
                $table_items='';$table_work='';$i=1;
                foreach($items_arr['id'] as $key => $s_cat_id){
                    
                    //Гарантия
                    $sql = "SELECT IF(COUNT(*)>0,s_prop_val.val,'')
                    				FROM s_prop, s_prop_val, s_cat_s_prop_val
                    					WHERE s_prop.name='Гарантия'
                    					AND s_prop.id=s_prop_val.s_prop_id
                                        AND s_cat_s_prop_val.id2=s_prop_val.id
                                        AND s_cat_s_prop_val.id1='"._DB($s_cat_id)."'
                                        LIMIT 1
                    	"; 
                    
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $myrow = mysql_fetch_array($res);
                    $garant=$myrow[0];
                        
                        
                    if ($items_arr['tip'][$key]=='Товар'){
                        
                        //Получаем серийный номер товара
                        $sql = "SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_tovar.barcode SEPARATOR ', '),'')
                        				FROM m_zakaz_s_cat, m_zakaz_s_cat_m_tovar, m_tovar 
                        					WHERE m_zakaz_s_cat.m_zakaz_id='"._DB($nomer)."' 
                                            AND m_zakaz_s_cat.s_cat_id='"._DB($s_cat_id)."'
                                            AND m_zakaz_s_cat.id=m_zakaz_s_cat_m_tovar.id1
                                            AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id
                                            
                        	"; 
                        
                        $mt = microtime(true);
                        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                        $myrow = mysql_fetch_array($res);
                        $sn=$myrow[0];
                        
                        $txt_=$tr_t0;
                        
                        $txt_ = str_replace("nom_t",$i,$txt_);
                        $txt_ = str_replace("name_t",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("kol_t",$items_arr['kolvo'][$key],$txt_);
                        $txt_ = str_replace("sn_t",$sn,$txt_);
                        $txt_ = str_replace("garant_t",$garant,$txt_);
                   
                        $table_items.=$txt_;
    
                    }
                    if ($items_arr['tip'][$key]=='Услуга'){
                        $txt_=$tr_u0;
                        
                        $txt_ = str_replace("nom_u",$i,$txt_);
                        $txt_ = str_replace("name_u",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("kol_u",$items_arr['kolvo'][$key],$txt_);
                        $txt_ = str_replace("master_u",$items_arr['a_admin_id'][$key],$txt_);
                        $txt_ = str_replace("garant_u",$garant,$txt_);
    
                        $table_work.=$txt_;
                   
                    } 
                    $i++;
                }
                $word = new docxGenerator($file_docs);
                $all_table='';
                if ($table_items!=''){
                    $all_table.=$table_t;
                }
                if ($table_work!=''){
                    $all_table.=$table_u;
                }
                $word->val('garant_id</w:t></w:r></w:p>', 'garant_id</w:t></w:r></w:p>'.$all_table);

                if ($table_items!=''){
                    $word->val('<w:t>Срок гарантии</w:t></w:r></w:p></w:tc></w:tr>','<w:t>Срок гарантии</w:t></w:r></w:p></w:tc></w:tr>'.$table_items.'');
                }
                if ($table_work!=''){
                    $word->val('<w:t>Гарантийный срок</w:t></w:r></w:p></w:tc></w:tr>', '<w:t>Гарантийный срок</w:t></w:r></w:p></w:tc></w:tr>'.$table_work.'');
                }
               
                $word->val('headname',$myrow_m_zakaz['i_tp_i_contr_org_name']); //
                $word->val('headinn',$myrow_m_zakaz['i_tp_i_contr_org_inn']);//
                $word->val('headuadr',$myrow_m_zakaz['i_tp_i_contr_org_u_adress']);//
                $word->val('headpadr', $myrow_m_zakaz['i_tp_adress']);//
                $word->val('headrchet', $myrow_m_zakaz['i_tp_i_contr_org_schet']);
                $word->val('headbank', $myrow_m_zakaz['i_tp_i_contr_org_bank']);//
                $word->val('headks',$myrow_m_zakaz['i_tp_i_contr_org_kschet']);//
                $word->val('headbik', $myrow_m_zakaz['i_tp_i_contr_org_bik']);//
                $word->val('headcont', conv_('phone_from_db',$myrow_m_zakaz['i_tp_phone'] ));
               /* */
                $word->val('data_vidachi',date('d.m.Y',strtotime($myrow_m_zakaz['data'])));//дата продажи
                $word->val('garant_id', $nomer);// номер

         
                $word->save($filename);
            }
            
            
            //********************************************* //Диагностика
            if ($file_name=='diagnoz'){
                
                
                $word = new docxGenerator($file_docs);
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                
                $sql_r_service = "SELECT  
                                        r_tip_oborud.name, 
                                        r_brend.name,
                                        r_model.name,
                                        (SELECT GROUP_CONCAT(r_neispravnosti.name SEPARATOR ', ')
                                            FROM r_service_r_neispravnosti, r_neispravnosti 
                                            WHERE r_service_r_neispravnosti.id1=r_service.id
                                            AND r_service_r_neispravnosti.id2=r_neispravnosti.id
                                            
                                            ) AS neispavnosti,
                                        r_service.diagnoz
                                        
                                
                				FROM r_service, r_model, r_tip_oborud, r_brend
                					WHERE r_service.m_zakaz_id='"._DB($nomer)."'
                                    AND r_service.r_model_id=r_model.id
                                    AND r_model.r_tip_oborud_id=r_tip_oborud.id
                                    AND r_model.r_brend_id=r_brend.id
                					LIMIT 1
                	"; 
                
                $mt = microtime(true);
                $res_r_service = mysql_query($sql_r_service) or die(mysql_error().'<br />'.$sql_r_service);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_r_service;$data_['_sql']['time'][]=$mt;
                $myrow_r_service = mysql_fetch_array($res_r_service);
                
                
                
                $word->val('model',$myrow_r_service[0].' '.$myrow_r_service[1].' '.$myrow_r_service[2]); //Модель
                $word->val('serial',''); //s/n
                $word->val('zakazfio',$myrow_m_zakaz['name']); //заказчик
                $word->val('datazakaz',date('d.m.Y',strtotime($myrow_m_zakaz['data']))); //дата
                $word->val('neispavnosti',$myrow_r_service['neispavnosti']); //неисправности
                $word->val('zakluchenie',$myrow_r_service['diagnoz']); //диагностика
                
                $vip_rab='';
                foreach($items_arr['id'] as $key => $s_cat_id){
                    if ($items_arr['tip'][$key]=='Услуга'){
                        if ($vip_rab!=''){$vip_rab.=', ';}
                        $vip_rab.=mb_strtolower($items_arr['name'][$key],'utf-8');
                    }
                    
                }
                $word->val('viprab',$vip_rab); //Работы
                
                $word->val('headname',$myrow_m_zakaz['i_tp_i_contr_org_name']); //
                $word->val('headinn',$myrow_m_zakaz['i_tp_i_contr_org_inn']);//
                $word->val('headuadr',$myrow_m_zakaz['i_tp_i_contr_org_u_adress']);//
                $word->val('headpadr', $myrow_m_zakaz['i_tp_adress']);//
                $word->val('headrchet', $myrow_m_zakaz['i_tp_i_contr_org_schet']);
                $word->val('headbank', $myrow_m_zakaz['i_tp_i_contr_org_bank']);//
                $word->val('headks',$myrow_m_zakaz['i_tp_i_contr_org_kschet']);//
                $word->val('headbik', $myrow_m_zakaz['i_tp_i_contr_org_bik']);//
                $word->val('headcont', conv_('phone_from_db',$myrow_m_zakaz['i_tp_phone'] ));
                
                $word->val('actid', $nomer);// номер
                $word->save($filename);
            }
            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            /*
            
            //ШАПКА ДОГОВОРА
            nomer       // номер заказа                                     #1255
            nompos      // номер поступления                                #551
            
            city        // город подписания                                 #Краснодар
            dtt         // дата в формате день.месяц.год                    #25.12.2016
            
            //ОБЪЕКТЫ/СУБЪЕКЫ ДОГОВОРА
            zakazz      // Название организации Исполнителя                 #ООО "НАНО-БИТ"
            zakazu      // Основание Исполнителя                            #Устав
            zakazl      // В лице Исполнителя                               #Генерального Директора 
            zakazm      // Должность Исполнителя                            #Генеральный Директор 
            zakazf      // ФИО Директора Исполнителя                        #Вершинский Алексей Владимирович
            zakazs      // ФИО Директора Исполнителя                        #Вершинский А.В.
            
            
            pokupz      // Название организации Заказчика                   #ИП Петров Петр Петрович
            pokupu      // Основание Заказчика (Устав)                      #Сидетельство
            pokupl      // В лице Заказчика                                 #Индивидуального  прдпринимателя
            pokupm      // Должность Заказчика                                 #Индивидуальный прдприниматель
            pokupf      // ФИО Директора Заказчика                          #Петров Петр Петрович
            pokups      // ФИО Директора Заказчика                          #Петров П. П.
            
            //ПАРАМЕТРЫ ДОГОВОРА
            summo       // Сумма договора прописью                          #Одна тысяча двести рублей 
            timee       // "Время, дней" (если есть свойство)               #15 дней
            timec       // "Время, часов" (если есть свойство)              #3 часа
            
            zakname     // ФИО права подписи заказчика                      #Петров П.П.
            rekva       // полные рекизиты заказчика                        #Петров Петр Петрович, 8(928)465-0000
            
            ispname     // ФИО права подписи исполнитля                     #Вершинский А.В.
            rekvb       // полные рекизиты исполнитля                       #ООО "НАНО-БИТ", ИНН: 2460086836, КПП: 246001001, тел.: 8(861)246-0735, 660075, г. Красноярск, ул. Маерчака, д.38, офис 925 
            
            //ПРОЧИЕ КОНТАКТЫ
            isite       //сайт из организации филиала                       #k-tehno.ru
            iphone      //телефон из организации филиала                    #+7(861)246-07-35
            iadress     //адрес из организации филиала                      #Красноярск, ул. Маерчака, 38
            
            */
            if (strstr($file_name,'dogovor')==true){
                
                //Родительный падеж ДИРЕКТОРА ИСПОЛНИТЕЛЯ
                $tip_director_isp=$myrow_m_zakaz['i_tp_i_contr_org_tip_director'];
                    $t01=@getNewFormText($tip_director_isp,1);
                    if ($t01){$tip_director_isp=$t01;}
                $fio_director_isp=$myrow_m_zakaz['i_tp_i_contr_org_fio_director'];
                    $t01=@getNewFormText($fio_director_isp,1);
                    if ($t01){$fio_director_isp=$t01;}
                    
                //Родительный падеж ДИРЕКТОРА ЗАКАЗЧИКА
                $tip_director_zak=$myrow_i_contr_org['tip_director'];
                    $t01=@getNewFormText($tip_director_zak,1);
                    if ($t01){$tip_director_zak=$t01;}
                $fio_director_zak=$myrow_i_contr_org['fio_director'];
                    $t01=@getNewFormText($fio_director_zak,1);
                    if ($t01){$fio_director_zak=$t01;}
                
                //Фамилия и инициалы директора
                if (strstr($myrow_m_zakaz['i_tp_i_contr_org_fio_director'],' ')==true){
                    //$arr_dd=explode(' ',$myrow_m_zakaz['i_tp_i_contr_org_fio_director']$myrow_m_zakaz['i_tp_i_contr_org_fio_director'])
                }
                
                
                $word = new docxGenerator($file_docs);
                $filename = '../upload/temp/'.$file_name.'_'.date('YmdHis').'_'.rand(111,999).'.docx'; //Имя файла
                    
                $word->val('nomer', $nomer); //номер договора - заказа
                $word->val('dtt',date('d.m.Y',strtotime($myrow_m_zakaz['data']))); //дата договора
                $word->val('city', $myrow_m_zakaz['i_tp_city_name']); //город договора
                
                $word->val('zakazz',$myrow_m_zakaz['i_tp_i_contr_org_name']);//Название организации исполнителя
                $word->val('zakazu',$myrow_m_zakaz['i_tp_i_contr_org_na_osnovanii']);//Основание организации исполнителя
                $word->val('zakazl',$tip_director_isp);//ТИП  исполнителя
                $word->val('zakazm',$myrow_m_zakaz['i_tp_i_contr_org_tip_director']);//ТИП  исполнителя
                $word->val('zakazf',$fio_director_isp);//ФИО  исполнителя
                
                $word->val('pokupz',$myrow_i_contr_org['name']);//Название организации Заказчика
                $word->val('pokupu', $myrow_i_contr_org['na_osnovanii']);// Основание Заказчика (Устав)
                $word->val('pokupl', $tip_director_zak);// ТИП Заказчика
                $word->val('pokupm', $myrow_i_contr_org['tip_director']);// ТИП Заказчика
                $word->val('pokupf',$fio_director_zak);//ФИО  Заказчика
                
                //********************************************* ДОГОВОР 1
                if ($file_name=='dogovor1'){
                    $tbl_tr='<w:tr w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidTr="006713F2"><w:tc><w:tcPr><w:tcW w:w="675" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00397701" w:rsidP="008F5CB7"><w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                             .'number</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5526" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00A379D0" w:rsidP="008F5CB7"><w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                             .'tov</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1704" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00A379D0" w:rsidP="006713F2"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1050"/></w:tabs><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                             .'sum</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1948" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00A379D0" w:rsidP="008F5CB7"><w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                             .'days_</w:t></w:r></w:p></w:tc></w:tr>';
                             
                    $table_='';$i=1;$all_days_=0;
                    foreach($items_arr['id'] as $key => $s_cat_id){
                        
                        $days_='1';
                        if (in_array('Время, дней',$s_prop_arr) and isset($s_cat_s_prop_val_arr[$s_cat_id]) and is_array($s_cat_s_prop_val_arr[$s_cat_id])){
                            $key_prop=array_search('Время, дней',$s_prop_arr);
                            if ($key_prop!=''){
                                foreach($s_cat_s_prop_val_arr[$s_cat_id] as $key_2 => $s_prop_val_id){
                                    if (isset($s_prop_val_arr[$key_prop][$s_prop_val_id])){
                                        $days_=$s_prop_val_arr[$key_prop][$s_prop_val_id];
                                    }
                                }    
                            }
                        }
                        $txt_=$tbl_tr;
                        $txt_ = str_replace("number",$i,$txt_);
                        $txt_ = str_replace("tov",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("sum",$items_arr['summ'][$key],$txt_);
                        $txt_ = str_replace("days_",$days_,$txt_);
                        $all_days_+=$days_;
                        
                        $table_.=$txt_;
                        $i++;
                    }
                    
                    $all_days_txt=end_word($all_days_,$all_days_.' дней',$all_days_.' день',$all_days_.' дня');
                    
                    if ($table_==''){
                        echo 'В данном заказе не товаров!';exit;
                    }
                    
                    $table_all='<w:tbl><w:tblPr><w:tblStyle w:val="a3"/><w:tblW w:w="0" w:type="auto"/><w:tblCellMar><w:top w:w="57" w:type="dxa"/><w:bottom w:w="57" w:type="dxa"/></w:tblCellMar><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>
                                </w:tblPr><w:tblGrid><w:gridCol w:w="675"/><w:gridCol w:w="5526"/><w:gridCol w:w="1704"/><w:gridCol w:w="1948"/></w:tblGrid>
                                <w:tr w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidTr="00397701"><w:tc><w:tcPr><w:tcW w:w="675" w:type="dxa"/><w:shd w:val="pct10" w:color="auto" w:fill="auto"/>
                                <w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                                <w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/>
                                <w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                                <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>№</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5526" w:type="dxa"/>
                                <w:shd w:val="pct10" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                                <w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/>
                                <w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                                <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Название работы</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1704" w:type="dxa"/>
                                <w:shd w:val="pct10" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                                <w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/>
                                <w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                                <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Стоимость, руб.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1948" w:type="dxa"/>
                                <w:shd w:val="pct10" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                                <w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/>
                                <w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                                <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Время выполнения</w:t></w:r></w:p></w:tc></w:tr>'
                                .$table_.'<w:tr w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidTr="006713F2"><w:tc><w:tcPr><w:tcW w:w="6201" w:type="dxa"/>
                                <w:gridSpan w:val="2"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="00397701" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                                <w:pPr><w:jc w:val="right"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/>
                                <w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00397701"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                                <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">Итого: </w:t></w:r></w:p></w:tc><w:tc>
                                <w:tcPr><w:tcW w:w="1704" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="00397701" w:rsidRDefault="006713F2" w:rsidP="008F5CB7">
                                <w:pPr><w:rPr> <w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/>
                                </w:rPr></w:pPr><w:r w:rsidRPr="00397701"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                                .number_format($all_sum,2,',',' ').'</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1948" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="00397701" w:rsidRDefault="006713F2" w:rsidP="008F5CB7">
                                <w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/>
                                </w:rPr></w:pPr><w:r w:rsidRPr="00397701"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                                <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                                .$all_days_txt.'</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';
                    
                    
                   
                    $word->val('grafik',$table_all); // график
                    $word->val('zakazu',$myrow_m_zakaz['i_tp_i_contr_org_na_osnovanii']);
                    $word->val('pokupz',$myrow_i_contr_org['name']);
                    $word->val('pokupu', $myrow_i_contr_org['na_osnovanii']);
                    $word->val('pokupp','');
                    $word->val('summo',number_format($all_sum,2,',',' '));
                    $word->val('summa',$sum_txt);
                    $word->val('timee',$all_days_); // время разработки
                    $word->val('rekva',$myrow_m_zakaz['i_tp_full_name']);
                    $word->val('rekvb',$myrow_i_contr_org['full_name']);//$pokupatel
                    $word->val('pokupe','');//
                    $word->val('zakaze','');
                    $word->val('pokupi',$myrow_i_contr_org['fio_director']);
                    $word->val('zakazi',$myrow_m_zakaz['i_tp_i_contr_org_fio_director']); 
                    $word->val('summ',number_format(($all_sum/2),2,',',' '));
                    $word->val('tpname',$myrow_m_zakaz['i_tp_i_contr_org_name']);
                    $word->val('phonecode','('. mb_substr($myrow_m_zakaz['i_tp_phone'],1,3,'UTF-8').')');
                    $word->val('phonenum',mb_substr($myrow_m_zakaz['i_tp_phone'],4,3,'UTF-8').'-'.mb_substr($myrow_m_zakaz['i_tp_phone'],7,4
                    ,'UTF-8'));
                   
                    
                }
                //********************************************* ДОГОВОР 2
                if ($file_name=='dogovor2'){
    
                   $tbl_tr='<w:tr w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidTr="006713F2"><w:tc><w:tcPr><w:tcW w:w="675" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00397701" w:rsidP="008F5CB7"><w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                        .'number</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5526" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00A379D0" w:rsidP="008F5CB7"><w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                        .'tov</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1704" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00A379D0" w:rsidP="006713F2"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1050"/></w:tabs><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                        .'sum</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1948" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="00A379D0" w:rsidP="008F5CB7"><w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                        .'days_</w:t></w:r></w:p></w:tc></w:tr>'; 
    
                    $table_='';$i=1;$all_days_=0;
                    foreach($items_arr['id'] as $key => $s_cat_id){
                        
                        $days_='1';
                        if (in_array('Время, дней',$s_prop_arr) and isset($s_cat_s_prop_val_arr[$s_cat_id]) and is_array($s_cat_s_prop_val_arr[$s_cat_id])){
                            
                            $key_prop=array_search('Время, дней',$s_prop_arr);
                            if ($key_prop!=''){
                                foreach($s_cat_s_prop_val_arr[$s_cat_id] as $key_2 => $s_prop_val_id){
                                    if (isset($s_prop_val_arr[$key_prop][$s_prop_val_id])){
                                        $days_=$s_prop_val_arr[$key_prop][$s_prop_val_id];
                                    }
                                }    
                            }
                        }
                        $txt_=$tbl_tr;
                        $txt_ = str_replace("number",$i,$txt_);
                        $txt_ = str_replace("tov",$items_arr['name'][$key],$txt_);
                        $txt_ = str_replace("sum",$items_arr['summ'][$key],$txt_);
                        $txt_ = str_replace("days_",$days_,$txt_);
                        $all_days_+=$days_;
                        
                        $table_.=$txt_;
                        $i++;
                    }
                    
                    $all_days_txt=end_word($all_days_,$all_days_.' дней',$all_days_.' день',$all_days_.' дня');
                    
                    if ($table_==''){
                        echo 'В данном заказе не товаров!';exit;
                    }
                    
                    $table_all='<w:tbl><w:tblPr><w:tblStyle w:val="a3"/><w:tblW w:w="0" w:type="auto"/><w:tblCellMar>
                    <w:top w:w="57" w:type="dxa"/><w:bottom w:w="57" w:type="dxa"/></w:tblCellMar><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>
                    </w:tblPr><w:tblGrid><w:gridCol w:w="675"/><w:gridCol w:w="5526"/><w:gridCol w:w="1704"/><w:gridCol w:w="1948"/></w:tblGrid>
                    <w:tr w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidTr="00397701"><w:tc><w:tcPr><w:tcW w:w="675" w:type="dxa"/><w:shd w:val="pct10" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2"><w:pPr> <w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>№</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="5526" w:type="dxa"/><w:shd w:val="pct10" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                    <w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Название работы</w:t></w:r></w:p></w:tc><w:tc><w:tcPr>
                    <w:tcW w:w="1704" w:type="dxa"/><w:shd w:val="pct10" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2">
                    <w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Стоимость, руб.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1948" w:type="dxa"/><w:shd w:val="pct10" w:color="auto" w:fill="auto"/>
                    <w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidRDefault="006713F2" w:rsidP="006713F2"><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="006713F2"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Время выполнения</w:t></w:r></w:p></w:tc></w:tr>'
                    .$table_.'<w:tr w:rsidR="006713F2" w:rsidRPr="006713F2" w:rsidTr="006713F2"><w:tc><w:tcPr><w:tcW w:w="6201" w:type="dxa"/><w:gridSpan w:val="2"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="00397701" w:rsidRDefault="006713F2" w:rsidP="006713F2"><w:pPr><w:jc w:val="right"/><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00397701"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">Итого: </w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1704" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="00397701" w:rsidRDefault="006713F2" w:rsidP="008F5CB7">
                    <w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00397701"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                    .number_format($all_sum,2,',',' ').'</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1948" w:type="dxa"/></w:tcPr><w:p w:rsidR="006713F2" w:rsidRPr="00397701" w:rsidRDefault="006713F2" w:rsidP="008F5CB7">
                    <w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00397701"><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
                    <w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>'
                    .$all_days_txt.'</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';
                    
                    
                    $word->val('grafik',$table_all); // график
                    $word->val('ddt',date('d.m.Y',strtotime($myrow_m_zakaz['data'])));
                    $word->val('zakazz','');
                    $word->val('zakazp',$myrow_m_zakaz['i_tp_i_contr_org_name']);
                    $word->val('zakazu',$myrow_m_zakaz['i_tp_i_contr_org_na_osnovanii']);
                    $word->val('pokupz',$myrow_i_contr_org['name']);
                    $word->val('pokupu', $myrow_i_contr_org['na_osnovanii']);
                    $word->val('pokupp','');
                    $word->val('summo',number_format($all_sum,2,',',' '));
                    $word->val('summa',$sum_txt);
                    $word->val('timee',$all_days_); // время разработки
                    $word->val('rekva',$myrow_m_zakaz['i_tp_full_name']);
                    $word->val('rekvb',$myrow_i_contr_org['full_name']);//$pokupatel
                    $word->val('pokupe','');//
                    $word->val('zakaze','');
                    $word->val('pokupi',$myrow_i_contr_org['fio_director']);
                    $word->val('zakazi',$myrow_m_zakaz['i_tp_i_contr_org_fio_director']); 
                    $word->val('summ',number_format(($all_sum/2),2,',',' '));
                    $word->val('site', $myrow_m_zakaz['link']);
                    $word->val('komment',$myrow_m_zakaz['comments']); //ключевые слова
                    $word->val('tpname',$myrow_m_zakaz['i_tp_i_contr_org_name']);
                    $word->val('phonecode','('. mb_substr($myrow_m_zakaz['i_tp_phone'],1,3,'UTF-8').')');
                    $word->val('phonenum',mb_substr($myrow_m_zakaz['i_tp_phone'],4,3,'UTF-8').'-'.mb_substr($myrow_m_zakaz['i_tp_phone'],7,4));
                   
                }
                //********************************************* ДОГОВОР 3
                if ($file_name=='dogovor3'){
    
                    $sql_service = "SELECT  r_service.id,
                                    (SELECT IF(COUNT(*)>0,r_tip_oborud.name,'') FROM r_tip_oborud WHERE r_tip_oborud.id=r_model.r_tip_oborud_id LIMIT 1) AS r_tip_oborud_name,
                                    (SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_brend WHERE r_brend.id=r_model.r_brend_id LIMIT 1) AS r_brend_name,
                                    r_model.name AS r_model_name,
                                    r_service.sn,
                                    r_service.komplekt,
                                    r_service.sost,
                                    r_service.r_service_id
                                    
                    				FROM r_service, r_model
                    					WHERE r_service.m_zakaz_id='"._DB($nomer)."'
                                        AND r_model.id=r_service.r_model_id
                    	"; 
                    
                    $mt = microtime(true);
                    $res_service = mysql_query($sql_service) or die(mysql_error().'<br/>'.$sql_service);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_service;$data_['_sql']['time'][]=$mt;
                    $myrow_service = mysql_fetch_array($res_service);
                    
                    $r_neispravnosti='';
                    $sql_r_neispravnosti = "SELECT r_neispravnosti.name
                        				FROM r_service_r_neispravnosti, r_neispravnosti
                        					WHERE r_service_r_neispravnosti.id1='"._DB($myrow_service['id'])."'
                                            AND r_neispravnosti.id=r_service_r_neispravnosti.id2
                     ";
                    $mt = microtime(true);
                    $res_r_neispravnosti = mysql_query($sql_r_neispravnosti) or die(mysql_error().'<br/>'.$sql_r_neispravnosti);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_r_neispravnosti;$data_['_sql']['time'][]=$mt;
                    for ($myrow_r_neispravnosti = mysql_fetch_array($res_r_neispravnosti); $myrow_r_neispravnosti==true; $myrow_r_neispravnosti = mysql_fetch_array($res_r_neispravnosti))
                    {
                        if ($r_neispravnosti!=''){$r_neispravnosti.=', ';}
                        $r_neispravnosti.=$myrow_r_neispravnosti['name'];
                    }
                    
                    $word->val('zakazz','');
                    $word->val('zakazp',$myrow_m_zakaz['i_tp_i_contr_org_name']);
                    $word->val('zakazu',$myrow_m_zakaz['i_tp_i_contr_org_na_osnovanii']);
                    $word->val('pokupz',$myrow_i_contr_org['name']);
                    $word->val('pokupu', $myrow_i_contr_org['na_osnovanii']);
                    $word->val('pokupp','');
                    $word->val('rekva',$myrow_i_contr_org['full_name']);
                    $word->val('rekvb',$myrow_m_zakaz['i_tp_full_name']);//$pokupatel
                    $word->val('zakname',$myrow_i_contr_org['fio_director']);
                    $word->val('ispname',$myrow_m_zakaz['i_tp_i_contr_org_fio_director']);
                    $word->val('pphone',conv_('phone_from_db',$myrow_m_zakaz['phone']));
                    
                    $word->val('mmodel',$myrow_service['r_tip_oborud_name'].' '.$myrow_service['r_brend_name'].' '.$myrow_service['r_model_name']);
                    $word->val('ssn',$myrow_service['sn']);
                    $word->val('kkompl',$myrow_service['komplekt']);
                    $word->val('nneisp',$r_neispravnosti);
                    $word->val('ssost',$myrow_service['sost']);
                    $word->val('isite',$_SERVER['SERVER_NAME']);
                    
                }
                
                
                
                
                //********************************************* //Договор поставки
                if ($file_name=='dogovor_postavki'){
                    
                    
                   
                    
                    
                }
                $word->save($filename);
            }//end договоры
            
        }
        
        else{//
            echo 'Не определен тип $file_name='.$file_name;exit;
        }
        //выводим файл
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="'.$name_docs.' '.$nomer.'.docx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.filesize($filename));
        if (ob_get_length()) {ob_clean();}
        flush();
        @readfile($filename);
        @unlink($filename);
        
    }else{
        echo '$file_name=""';exit;
    }
}



?>