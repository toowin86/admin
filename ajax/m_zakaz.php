<?php

header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

include "../db.php";
    
include "../functions.php";




if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){

//Получаем id админа
$sql = "SELECT IF(COUNT(*)>0,a_admin.id,'') 
    				FROM a_admin 
    					WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."' 
                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
        	"; 
$res = mysql_query($sql);
$myrow = mysql_fetch_array($res);
$a_admin_id_cur=$myrow[0];

$_t=_GP('_t');

// ************************************************************
// ПОИСК ПО ЗАКАЗАМ
//**************************************************************

if ($_t=='m_zakaz__find'){
    $data_=array();
    $WHERE="";
    $TABLE="";
    $ORDER='id DESC';
        $sort=_GP('sort');
        if ($sort!=''){
            $ORDER=' m_zakaz.'.$sort.' DESC';
        }
    $LIMIT="";
    $HAVING_STATUS='';
    
    //Догрузка
    $kol_load=100;
    $limit=_GP('limit');
        if ($limit!=''){
            $LIMIT=$limit.', '.$kol_load;
        }
        
    //ФИЛЬТР
    $txt=_GP('txt');
    $time=_GP('time',array());
    
    
    //ГОРЯЩИЕ ЗАКАЗЫ
    $fire=_GP('fire');
    $fire_h=_GP('fire_h');
   
    $sql_upp = "UPDATE a_options 
    			SET  
    				val='"._DB($fire_h)."'
    		WHERE name='Дедлайн для новых заказов, часов'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
    if ($fire=='true'){
       $WHERE.=" AND (
                        (m_zakaz.id NOT IN (SELECT r_service.m_zakaz_id FROM r_service) AND m_zakaz.status='В обработке') 
                        OR 
                        (m_zakaz.id IN (SELECT r_service.m_zakaz_id FROM r_service WHERE r_service.status='Принят'))
                    )
                     AND m_zakaz.data<='".date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))-$fire_h*60*60)."'
                     AND (m_zakaz.a_admin_otvet_id='' OR m_zakaz.a_admin_otvet_id IS NULL)
                     
                     ";
    }
     //end ГОРЯЩИЕ ЗАКАЗЫ
    
    if ($txt!=''){
        
        $txt_num=$txt-0;
        //Сортировка по номеру
        $sql = "SELECT COUNT(*)
        				FROM m_zakaz 
        					WHERE m_zakaz.id='"._DB($txt_num)."'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        if ($myrow[0]>0){
            $ord_='';if($ORDER!=''){$ord_=', '.$ORDER;}
            $ORDER=" FIELD(`m_zakaz`.`id`,"._DB($txt_num).") DESC".$ord_;
            
        }
        
        
        //ПОЛУЧАЕМ ID ЗАКАЗОВ, ГДЕ ЕСТЬ ТОВАР, с ДАННЫМ ПОИСКОВЫМ СЛОВОМ
        $m_zakaz_id_arr=array();
        $sql = "SELECT DISTINCT m_zakaz_s_cat.m_zakaz_id
            				FROM s_cat, m_zakaz_s_cat
            					WHERE s_cat.name LIKE '%"._DB($txt)."%'
                                AND m_zakaz_s_cat.s_cat_id=s_cat.id
                                
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $m_zakaz_id_arr[$myrow[0]]=$myrow[0];
        }
        //ПОЛУЧАЕМ ID ЗАКАЗОВ, ГДЕ ЕСТЬ ОРГАНИЗАЦИЯ, с ДАННЫМ ПОИСКОВЫМ СЛОВОМ
        $sql = "SELECT DISTINCT m_zakaz.id
            				FROM i_contr_org, m_zakaz
            					WHERE (i_contr_org.name LIKE '%"._DB($txt)."%'
                                    OR i_contr_org.inn LIKE '%"._DB($txt)."%'
                                    OR i_contr_org.phone LIKE '%"._DB($txt)."%')
                                AND i_contr_org.id=m_zakaz.i_contr_org_id
                                
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $m_zakaz_id_arr[$myrow[0]]=$myrow[0];
        }
        
        if ($txt-0>0){
            $m_zakaz_id_arr[$txt]=_DB($txt);
        }
        
        
        
        $WHERE.="   
            AND
            (
                m_zakaz.id IN ('".implode("','",$m_zakaz_id_arr)."')
                OR m_zakaz.project_name LIKE '%"._DB($txt)."%'
                OR m_zakaz.comments LIKE '%"._DB($txt)."%'
                OR i_contr.name LIKE '%"._DB($txt)."%'
                OR i_contr.phone LIKE '%"._DB($txt)."%'
                OR i_contr.email LIKE '%"._DB($txt)."%'
                OR (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_tip_oborud.name,'') FROM r_model, r_tip_oborud WHERE r_service.r_model_id=r_model.id AND r_model.r_tip_oborud_id=r_tip_oborud.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) LIKE '%"._DB($txt)."%'
                OR (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_model, r_brend WHERE r_service.r_model_id=r_model.id AND r_model.r_brend_id=r_brend.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) LIKE '%"._DB($txt)."%'
                OR (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_model.name,'') FROM r_model WHERE r_service.r_model_id=r_model.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) LIKE '%"._DB($txt)."%'
                    
            )
        ";
        
        
    }
    
    
    //ФИЛИАЛЫ
    $i_tp=_GP('i_tp');
        if ($i_tp!='' and $i_tp!='-1'){
            $WHERE.=" AND m_zakaz.i_tp_id='"._DB($i_tp)."' " ;
        }
        
    //Ответственный
    $a_admin_otvet_id=_GP('a_admin_otvet_id');
        if ($a_admin_otvet_id=='-1'){
            $WHERE.=" AND m_zakaz.a_admin_otvet_id='' " ;
        }
        if ($a_admin_otvet_id!='' and $a_admin_otvet_id!='-1'){
            $WHERE.=" AND m_zakaz.a_admin_otvet_id='"._DB($a_admin_otvet_id)."' " ;
        }    
    
    
    //статус заказа
    $status_=_GP('status_');
    if (is_array($status_) and count($status_)>0){
        $WHERE.=" AND (";
        foreach($status_ as $k => $val_){
            if ($k>0){$WHERE.=" OR ";}
            $WHERE.="m_zakaz.status='"._DB($val_)."'";
        }
        $WHERE.=" ) ";
    }else{
        $WHERE.=" AND m_zakaz.status!='Отменен'";
    }

    //Транспортная компания
    $i_tk_id_arr=_GP('i_tk_id');
    if (is_array($i_tk_id_arr) and count($i_tk_id_arr)>0){
        $WHERE.=" AND m_dostavka.m_zakaz_id=m_zakaz.id AND m_dostavka.i_tk_id IN ('".implode("','",$i_tk_id_arr)."')";
        $TABLE.=", m_dostavka";
        
    }
    //Реклама
    $i_reklama_id_arr=_GP('i_reklama_id');
    if (is_array($i_reklama_id_arr) and count($i_reklama_id_arr)>0){
        $WHERE.=" AND i_contr.i_reklama_id IN ('".implode("','",$i_reklama_id_arr)."')";
        
    }
    
    
    //статус сервиса
    $status_service=_GP('status_service');
        if ($status_service!='-1' and $status_service!='' and $status_service!='act' and $status_service!='no_act'){
            
            $WHERE.=" AND m_zakaz.id IN (SELECT r_service.m_zakaz_id FROM r_service WHERE r_service.status='"._DB($status_service)."')";
            $status_service='';
        }
        elseif ($status_service=='act'){
            $WHERE.=" AND m_zakaz.id IN (SELECT r_service.m_zakaz_id FROM r_service)";
            $status_service='';
        }
        elseif ($status_service=='no_act'){
            $WHERE.=" AND m_zakaz.id NOT IN (SELECT r_service.m_zakaz_id FROM r_service) ";
            $status_service='';
        }
        else{
            //$WHERE.=" AND m_zakaz.status!='Отменен'";//все кроме отмененных
        }
        
        
        
    //ДАТА
    $d1=_GP('d1');
        if ($d1!=''){
            $d1=date('Y-m-d',strtotime($d1)).' 00:00:00';
            $WHERE.=" AND m_zakaz.data>='"._DB($d1)."'";
        }
    $d2=_GP('d2');
        if ($d2!=''){
            $d2=date('Y-m-d',strtotime($d2)).' 23:59:59';
            $WHERE.=" AND m_zakaz.data<='"._DB($d2)."'";
        }    
    $d1_done=_GP('d1_done');
        if ($d1_done!=''){
            $d1_done=date('Y-m-d',strtotime($d1_done)).' 00:00:00';
            $WHERE.=" AND m_zakaz.data_end>='"._DB($d1_done)."' AND m_zakaz.data_end!='0000-00-00 00:00:00'";
        }
    $d2_done=_GP('d2_done');
        if ($d2_done!=''){
            $d2_done=date('Y-m-d',strtotime($d2_done)).' 23:59:59';
            $WHERE.=" AND m_zakaz.data_end<='"._DB($d2_done)."' AND m_zakaz.data_end!='0000-00-00 00:00:00'";
        }    
    
    
    
    //статус оплаты
    $status_pay=_GP('status_pay');

    if (is_array($status_pay) and count($status_pay)<3 and count($status_pay)>0){
        $HAVING_STATUS.=" (";
        foreach($status_pay as $k => $val_){
            
            if ($k>0){$HAVING_STATUS.=' OR ';}
            if ($val_=='Не оплачен'){
                $HAVING_STATUS.=" ((pl_-pl_debet)='0' AND all_sum>0)";
            }
            if ($val_=='Частично оплачен'){
                $HAVING_STATUS.=" ((pl_-pl_debet)>0 AND (pl_-pl_debet)<all_sum)  ";
            }
            if ($val_=='Оплачен'){
                $HAVING_STATUS.=" ((pl_-pl_debet)>=all_sum) ";
            }
            
        }
        $HAVING_STATUS.=" ) ";
    }
    
    
    if ($ORDER!=''){$ORDER=" ORDER BY FIELD(`status_dostavki_dor`,'Доработка') DESC, ".$ORDER;}
    if ($LIMIT!=''){$LIMIT=' LIMIT '.$LIMIT;}else{$LIMIT=' LIMIT '.$kol_load;}
    if ($HAVING_STATUS!=''){$HAVING_STATUS=' HAVING '.$HAVING_STATUS;}
    
    
    
    //Получаем общее количество напоминаний
    $sql = "SELECT m_zakaz.id,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                    (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_sum,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_tip_oborud.name,'') FROM r_model, r_tip_oborud WHERE r_service.r_model_id=r_model.id AND r_model.r_tip_oborud_id=r_tip_oborud.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_tip_oborud_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_model, r_brend WHERE r_service.r_model_id=r_model.id AND r_model.r_brend_id=r_brend.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_brend_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_model.name,'') FROM r_model WHERE r_service.r_model_id=r_model.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_model_name
                    
                     
				FROM m_zakaz , i_contr $TABLE
                WHERE m_zakaz.i_contr_id=i_contr.id
                AND (m_zakaz.data_end!='0000-00-00 00:00:00' AND m_zakaz.data_end<NOW()) 
					$WHERE
                    $HAVING_STATUS
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['time1']=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['time1']++;
    }
    $sql = "SELECT m_zakaz.id,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                    (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_sum,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_tip_oborud.name,'') FROM r_model, r_tip_oborud WHERE r_service.r_model_id=r_model.id AND r_model.r_tip_oborud_id=r_tip_oborud.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_tip_oborud_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_model, r_brend WHERE r_service.r_model_id=r_model.id AND r_model.r_brend_id=r_brend.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_brend_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_model.name,'') FROM r_model WHERE r_service.r_model_id=r_model.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_model_name
                    
                     
				FROM m_zakaz , i_contr $TABLE
                WHERE m_zakaz.i_contr_id=i_contr.id
                AND (m_zakaz.data_end!='0000-00-00 00:00:00' AND m_zakaz.data_end>=NOW()) 
					$WHERE
                    $HAVING_STATUS
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['time2']=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['time2']++;
    }
    
    //Напоминания
    if (count($time)>0){
        if (in_array('1',$time) and in_array('2',$time)){
            $WHERE.=" AND (m_zakaz.data_end!='0000-00-00 00:00:00') ";
        }
        elseif(in_array('1',$time) and !in_array('2',$time)){
            $WHERE.=" AND (m_zakaz.data_end!='0000-00-00 00:00:00' AND m_zakaz.data_end<NOW()) ";
        }
        elseif(!in_array('1',$time) and in_array('2',$time)){
            $WHERE.=" AND (m_zakaz.data_end!='0000-00-00 00:00:00' AND m_zakaz.data_end>=NOW()) ";
        }
    }
    
    
    
    $data_['cnt_']=0;
    $data_['pl_all']=0;
    $data_['sum_all']=0;
    $sql = "SELECT m_zakaz.id,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                    (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_sum,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_tip_oborud.name,'') FROM r_model, r_tip_oborud WHERE r_service.r_model_id=r_model.id AND r_model.r_tip_oborud_id=r_tip_oborud.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_tip_oborud_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_model, r_brend WHERE r_service.r_model_id=r_model.id AND r_model.r_brend_id=r_brend.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_brend_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_model.name,'') FROM r_model WHERE r_service.r_model_id=r_model.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_model_name
                     
				FROM m_zakaz , i_contr $TABLE
                WHERE m_zakaz.i_contr_id=i_contr.id
					$WHERE
                    $HAVING_STATUS
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['cnt_']++;
        $data_['pl_all']+=$myrow['pl_'];
        $data_['sum_all']+=$myrow['all_sum'];
    }
    
    
    
    
    
    $sql = "SELECT  DISTINCT
                    m_zakaz.id,
                    m_zakaz.data,
                    m_zakaz.data_end,
                    m_zakaz.a_admin_id,
                    (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE m_zakaz.a_admin_id=a_admin.id) AS a_admin_name,
                    m_zakaz.i_contr_id,
                    i_contr.name AS i_contr_name,
                    i_contr.phone AS i_contr_phone,
                    i_contr.email AS i_contr_email,
                    m_zakaz.i_contr_org_id,
                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'') FROM i_contr_org WHERE m_zakaz.i_contr_org_id=i_contr_org.id) AS i_contr_org_name,
                    m_zakaz.project_name,
                    m_zakaz.status,
                    tip_pay,
                    comments,
                    data_end,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                    (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                    (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_sum,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_tip_oborud.name,'') FROM r_model, r_tip_oborud WHERE r_service.r_model_id=r_model.id AND r_model.r_tip_oborud_id=r_tip_oborud.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_tip_oborud_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_model, r_brend WHERE r_service.r_model_id=r_model.id AND r_model.r_brend_id=r_brend.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_brend_name,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,r_model.name,'') FROM r_model WHERE r_service.r_model_id=r_model.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_model_name,
                    (SELECT IF(COUNT(*)>0,r_service.diagnoz,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS dizagnoz,
                    (SELECT IF(COUNT(*)>0,r_service.status,'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id) AS r_service_status,
                    
                    
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(r_neispravnosti.name SEPARATOR ', '),'') FROM r_service, r_service_r_neispravnosti,r_neispravnosti  WHERE r_service_r_neispravnosti.id2=r_neispravnosti.id AND r_service_r_neispravnosti.id1=r_service.id AND r_service.m_zakaz_id=m_zakaz.id) AS r_service_neispr,
                    (SELECT COUNT(*) FROM m_dialog WHERE m_dialog.row_id=m_zakaz.id AND m_dialog.a_menu_id='16') AS m_dialog_cnt,
                    m_zakaz.a_admin_otvet_id,
                    (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE m_zakaz.a_admin_otvet_id=a_admin.id) AS a_admin_otvet_name,
                    
                    (SELECT IF(COUNT(*)>0,'Доработка','') FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat.status_dostavki='Доработка') AS status_dostavki_dor,
                    (SELECT substring_index(GROUP_CONCAT(CONCAT(mz2.id,'@@',mz2.status) SEPARATOR ','), ',', 5) FROM m_zakaz AS mz2 WHERE i_contr.id=mz2.i_contr_id AND m_zakaz.id!=mz2.id ORDER BY mz2.id DESC) AS mz2_
                    
				FROM m_zakaz, i_contr $TABLE
					WHERE m_zakaz.i_contr_id=i_contr.id
                    $WHERE
                    $HAVING_STATUS
						$ORDER
                        $LIMIT
     ";
     //(SELECT IF(COUNT(*)>0,GROUP_CONCAT(m_postav.id SEPARATOR ', '),'') FROM m_postav, m_postav_s_cat WHERE m_postav_s_cat.m_postav_id=m_postav.id AND m_postav_s_cat.m_zakaz_id=m_zakaz.id AND m_postav.status!='Отменен') AS m_postav_id,
                    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['i']=array();
    $data_['d1']=array();
    $data_['d2']=array();
    $data_['d_']=array();
    $data_['a']=array();
    $data_['ao']=array();
    $data_['cao']=array();
    $data_['c']=array();
    $data_['o']=array();
    $data_['ci']=array();
    $data_['oi']=array();
    
    $data_['p']=array();
    $data_['s']=array();
    $data_['t']=array();
    $data_['h']=array();
    $data_['p']=array();
    $data_['ce']=array();
    $data_['cp']=array();
    $data_['mod']=array();
    $data_['r_st']=array();
    $data_['r_di']=array();
    $data_['r_n']=array();
    $data_['mz2']=array();
    
    $data_['mc']=array();
    $data_['sd']=array();
    $i=0;
    $s_cat__m_zakaz_id_array=array();
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $s_cat__m_zakaz_id_array[]=$myrow[0];
        //echo date('d.m.Y H:i:s',strtotime($myrow['data']));
        //echo $myrow['data'].'<br />';
        $data_['i'][$i]=$myrow['id'];
        $data_['d1'][$i]=date('d.m.Y H:i:s',strtotime($myrow['data']));
        $data_['d2'][$i]='';$data_['d_'][$i]='';
        if ($myrow['data_end']!='null' and $myrow['data_end']!='0000-00-00 00:00:00' and $myrow['data_end']!=''){
            
            $data_['d2'][$i]=date('d.m.Y',strtotime($myrow['data_end']));
            $data_['d_'][$i]=raznica_po_vremeni(date('d.m.Y H:i:s'),$myrow['data_end'],'hours');
        }
                
        $data_['a'][$i]=$myrow['a_admin_name'];
        $data_['ao'][$i]=$myrow['a_admin_otvet_name'];
        $data_['cao'][$i]='0';
        if ($myrow['a_admin_otvet_id']==$a_admin_id_cur){
            $data_['cao'][$i]='1';
        }
        $data_['c'][$i]=$myrow['i_contr_name'];
        $data_['ci'][$i]=$myrow['i_contr_id'];
        $data_['ce'][$i]=$myrow['i_contr_email'];
            $data_['cp'][$i]='';if ($myrow['i_contr_phone']!=''){$data_['cp'][$i]=conv_('phone_from_db',$myrow['i_contr_phone']);}
        
        $data_['o'][$i]=$myrow['i_contr_org_name'];
        $data_['oi'][$i]=$myrow['i_contr_org_id'];
        $data_['pn'][$i]=$myrow['project_name'];
        $data_['s'][$i]=$myrow['status'];
        $data_['t'][$i]=$myrow['tip_pay'];
        $data_['h'][$i]=$myrow['comments'];
        $data_['mz2'][$i]=$myrow['mz2_'];
       // $data_['pi'][$i]=$myrow['m_postav_id'];
       // $data_['ps'][$i]=$myrow['m_postav_status'];
        $data_['p'][$i]=$myrow['pl_']-$myrow['pl_debet'];
        $data_['mc'][$i]=$myrow['m_dialog_cnt'];
        
        $data_['mod'][$i]='';
        if ($myrow['r_tip_oborud_name']!=''){
            $data_['mod'][$i]=$myrow['r_tip_oborud_name'].' '.$myrow['r_brend_name'].' '.$myrow['r_model_name'];
        }
        $data_['r_st'][$i]='';
        if ($myrow['r_service_status']!=null){
            $data_['r_st'][$i]=$myrow['r_service_status'];
        }
        $data_['r_n'][$i]='';
        if ($myrow['r_service_neispr']!=null){
            $data_['r_n'][$i]=$myrow['r_service_neispr'];
        }
        $data_['r_di'][$i]='';
        if ($myrow['dizagnoz']!=null){
            $data_['r_di'][$i]=$myrow['dizagnoz'];
        }
        
        $data_['sd'][$i]=$myrow['status_dostavki_dor'];
        
        //Товар
        $data_['w'][$i]['i']=array();
        $data_['w'][$i]['n']=array();
        $data_['w'][$i]['p']=array();
        $data_['w'][$i]['k']=array();
        $data_['w'][$i]['c']=array();
        $data_['w'][$i]['im']=array();
        $data_['w'][$i]['pi']=array();
        $data_['w'][$i]['ps']=array();
        $data_['w'][$i]['pr']=array();
        //print_rf($myrow['id']);
       
        $i++;
    }
    
    
    //Поиск - получаем товар
    $sql_s_cat = "SELECT    s_cat.id AS s_cat_id_,
                            s_cat.name,
                            m_zakaz_s_cat.price,
                            m_zakaz_s_cat.kolvo,
                            m_zakaz_s_cat.comments,
                            (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat_id_ ORDER BY a_photo.sid LIMIT 1) AS img,
                            (SELECT IF(COUNT(*)>0,m_postav_s_cat.m_postav_id,'') FROM m_zakaz_s_cat_m_tovar, m_tovar, m_postav_s_cat WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id) AS m_postav_id,
                            (SELECT SUM(m_postav_s_cat.price*m_zakaz_s_cat_m_tovar.kolvo) FROM m_zakaz_s_cat_m_tovar, m_tovar, m_postav_s_cat WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id) AS m_postav_sum,
                            (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat_id_ AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val,
                            (SELECT IF(COUNT(m_postav.id)>0,GROUP_CONCAT(CONCAT(m_postav.id,',',m_postav.status) SEPARATOR ';;'),'') FROM m_postav_s_cat,m_postav  WHERE m_postav_s_cat.s_cat_id=s_cat_id_ AND m_postav_s_cat.m_postav_id=m_postav.id AND m_postav_s_cat.m_zakaz_id=m_zakaz_s_cat.m_zakaz_id AND m_postav.status!='Отменен') AS m_postav_status,
                            m_zakaz_s_cat.m_zakaz_id,
                            (SELECT IF(COUNT(m_zakaz_s_cat_a_admin_i_post.id)>0,a_admin.name,'') FROM m_zakaz_s_cat_a_admin_i_post, a_admin_i_post,a_admin WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat_a_admin_i_post.id2=a_admin_i_post.id AND a_admin_i_post.id1=a_admin.id) AS a_admin_name
                            
        				FROM s_cat, m_zakaz_s_cat
        					WHERE m_zakaz_s_cat.s_cat_id=s_cat.id
                            AND m_zakaz_s_cat.m_zakaz_id IN ('".implode("','",$s_cat__m_zakaz_id_array)."')
                            ORDER BY m_zakaz_s_cat.id
     ";
    
    
    $j=0;
    $s_cat_arr['mzid']=array();
    $s_cat_arr['zi']=array();
    $s_cat_arr['i']=array();
    $s_cat_arr['n']=array();
    $s_cat_arr['p']=array();
    $s_cat_arr['k']=array();
    $s_cat_arr['c']=array();
    $s_cat_arr['pi']=array();
    $s_cat_arr['ps']=array();
    $s_cat_arr['pst']=array();
    $s_cat_arr['pr']=array();
    $s_cat_arr['im']=array();
    $s_cat_arr['ksk']=array();
    $s_cat_arr['aan']=array();
    
    $mt = microtime(true);
    $res_s_cat = mysql_query($sql_s_cat) or die(mysql_error().'<br/>'.$sql_s_cat);  
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_cat;$data_['_sql']['time'][]=$mt;
    for ($myrow_s_cat = mysql_fetch_array($res_s_cat); $myrow_s_cat==true; $myrow_s_cat = mysql_fetch_array($res_s_cat))
    {
        
            //print_rf($myrow_s_cat);
            $s_cat_arr['mzid'][$myrow_s_cat['m_zakaz_id']][]=$j;
            $s_cat_arr['zi'][$j]=$myrow_s_cat['m_zakaz_id'];
            $s_cat_arr['i'][$j]=$myrow_s_cat['s_cat_id_'];
            $s_cat_arr['n'][$j]=$myrow_s_cat['name'];
            $s_cat_arr['p'][$j]=number_format($myrow_s_cat['price'],0,'.','');
            $s_cat_arr['k'][$j]=$myrow_s_cat['kolvo'];
            $s_cat_arr['c'][$j]=$myrow_s_cat['comments'];
            $s_cat_arr['pi'][$j]=$myrow_s_cat['m_postav_id'];
            $s_cat_arr['ps'][$j]=$myrow_s_cat['m_postav_sum'];
            $s_cat_arr['pst'][$j]=$myrow_s_cat['m_postav_status'];
            $s_cat_arr['pr'][$j]=$myrow_s_cat['prop_val'];
            $s_cat_arr['im'][$j]=$myrow_s_cat['img'];
            $s_cat_arr['aan'][$j]=$myrow_s_cat['a_admin_name'];
            //Получаем количество товара на складе
            $s_cat_arr['ksk'][$j]=getCOUNTtovarINsklad($myrow_s_cat['s_cat_id_']);
            
            
                if (!file_exists('../../i/s_cat/original/'.$myrow_s_cat['img'])){$s_cat_arr['im'][$j]='';}
            $j++;
    }
    
    foreach($data_['i'] as $i => $m_zakaz_id){
         ///поиск по товарам
       
        $u=0;
        if (isset($s_cat_arr['mzid'][$m_zakaz_id]) and is_array($s_cat_arr['mzid'][$m_zakaz_id])){
            foreach($s_cat_arr['mzid'][$m_zakaz_id] as $key => $j){
                $data_['w'][$i]['i'][$u]=$s_cat_arr['i'][$j];
                $data_['w'][$i]['n'][$u]=$s_cat_arr['n'][$j];
                $data_['w'][$i]['p'][$u]=$s_cat_arr['p'][$j];
                $data_['w'][$i]['k'][$u]=$s_cat_arr['k'][$j];
                $data_['w'][$i]['c'][$u]=$s_cat_arr['c'][$j];
                $data_['w'][$i]['pi'][$u]=$s_cat_arr['pi'][$j];
                $data_['w'][$i]['ps'][$u]=$s_cat_arr['ps'][$j];
                $data_['w'][$i]['pst'][$u]=$s_cat_arr['pst'][$j];
                $data_['w'][$i]['pr'][$u]=$s_cat_arr['pr'][$j];
                $data_['w'][$i]['im'][$u]=$s_cat_arr['im'][$j];
                $data_['w'][$i]['ksk'][$u]=$s_cat_arr['ksk'][$j];
                $data_['w'][$i]['aan'][$u]=$s_cat_arr['aan'][$j];
                $u++;
            }
        }
    }
    
    
    
    echo json_encode($data_);
}

// ************************************************************
// ЗАГРУЗКА ЗАКАЗА
//**************************************************************

if ($_t=='zakaz_load'){

    $data_=array();    
    $data_['nomer']=_GP('nomer');
    
    $sql = "SELECT  m_zakaz.data,
                    m_zakaz.a_admin_id,
                    m_zakaz.i_contr_id,
                    (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE i_contr.id=m_zakaz.i_contr_id) AS i_contr_name,
                    (SELECT IF(COUNT(*)>0,i_contr.phone,'') FROM i_contr WHERE i_contr.id=m_zakaz.i_contr_id) AS i_contr_phone,
                    (SELECT IF(COUNT(*)>0,i_contr.email,'') FROM i_contr WHERE i_contr.id=m_zakaz.i_contr_id) AS i_contr_email,
                    m_zakaz.i_contr_org_id,
                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'') FROM i_contr_org WHERE i_contr_org.id=m_zakaz.i_contr_org_id) AS i_contr_org_name,
                    m_zakaz.project_name,
                    m_zakaz.status,
                    m_zakaz.comments,
                    m_zakaz.html_code,
                    m_zakaz.data_end,
                    m_zakaz.i_tp_id,
                    (SELECT IF(COUNT(*)>0,i_tp.name,'') FROM i_tp WHERE i_tp.id=m_zakaz.i_tp_id) AS i_tp_name,
                    m_zakaz.a_admin_otvet_id,
                    (SELECT IF(COUNT(*)>0,c_call_client.id,'') FROM c_call_client WHERE c_call_client.m_zakaz_id=m_zakaz.id LIMIT 1) AS c_call_client_id
                    
    				FROM m_zakaz 
    					WHERE m_zakaz.id='"._DB($data_['nomer'])."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    
    //2018-05-17 toowin86 - выбор организаций
    $sql_i_contr_org = "SELECT  i_contr_org.id, 
                                    i_contr_org.name, 
                                    i_contr_org.phone, 
                                    i_contr_org.email
                    FROM i_contr_org, i_contr_i_contr_org
    					WHERE i_contr_i_contr_org.id2=i_contr_org.id
                        AND i_contr_i_contr_org.id1='"._DB($myrow['i_contr_id'])."'
    ";
     
    $res_i_contr_org = mysql_query($sql_i_contr_org);if (!$res_i_contr_org){echo $sql_i_contr_org;exit();}
    $data_['i_contr_org']['id']=array();
    $data_['i_contr_org']['name']=array();
    $data_['i_contr_org']['phone']=array();
    $data_['i_contr_org']['email']=array();
    for ($myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j=0; $myrow_i_contr_org==true; $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j++)
    {
        $data_['i_contr_org']['id'][$j]=$myrow_i_contr_org['id'];
        $data_['i_contr_org']['name'][$j]=$myrow_i_contr_org['name'];
        $data_['i_contr_org']['phone'][$j]=$myrow_i_contr_org['phone'];
            if ($data_['i_contr_org']['phone'][$j]!=''){$data_['i_contr_org']['phone'][$j]=conv_('phone_from_db',$myrow_i_contr_org['phone']);}
        $data_['i_contr_org']['email'][$j]=''; if ($myrow_i_contr_org['email']!='') {$data_['i_contr_org']['email'][$j]=$myrow_i_contr_org['email']; }
    }
    $data_['active']=0; if ($myrow['i_contr_org_id']-0>0){$data_['active']=$myrow['i_contr_org_id'];}
    
    $data_['i_contr']['id']=$myrow['i_contr_id'];
    $data_['i_contr']['name']=$myrow['i_contr_name'];
    $data_['i_contr']['phone']=$myrow['i_contr_phone'];
        if ($data_['i_contr']['phone']!=''){$data_['i_contr']['phone']=conv_('phone_from_db',$myrow['i_contr_phone']);}
    $data_['i_contr']['email']=''; if ($myrow['i_contr_email']!='') {$data_['i_contr']['email']=$myrow['i_contr_email']; }
    //end 2018-05-17 toowin86
    
    
    $data_['d1']=date('d.m.Y H:i',strtotime($myrow['data']));
    $data_['tpi']=$myrow['i_tp_id'];
    $data_['tpn']=$myrow['i_tp_name'];
    $data_['a']=$myrow['a_admin_id'];
    $data_['ao']=$myrow['a_admin_otvet_id'];
    
    $data_['pn']=$myrow['project_name'];
    $data_['st']=$myrow['status'];
    $data_['c']=$myrow['comments'];
    $data_['h']=$myrow['html_code'];
    $data_['ci']=$myrow['c_call_client_id'];
    $data_['d2']='';
    if ($myrow['data_end']!='' and $myrow['data_end']!='0000-00-00 00:00:00'){
        $data_['d2']=date('d.m.Y H:i',strtotime($myrow['data_end']));
    }
    
    
    
    $data_['i']=array();
    $data_['i']['i']=array();
    $data_['i']['n']=array();
    $data_['i']['t']=array();
    $data_['i']['p']=array();
    $data_['i']['k']=array();
    $data_['i']['c']=array();
    $data_['i']['b']=array();//штрих-коды
    $data_['i']['img']=array();//фото
    
    //Товары и услуги
    $sql = "SELECT  s_cat.id,
                    s_cat.name,
                    s_cat.tip,
                    m_zakaz_s_cat.kolvo,
                    m_zakaz_s_cat.id AS m_zakaz_s_cat_id,
                    m_zakaz_s_cat.price,
                    m_zakaz_s_cat.comments,
                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id ORDER BY a_photo.sid LIMIT 1) AS img,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id  AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val
                    
        				FROM m_zakaz_s_cat, s_cat
        					WHERE m_zakaz_s_cat.m_zakaz_id='"._DB($data_['nomer'])."'
                            AND m_zakaz_s_cat.s_cat_id=s_cat.id
                            ORDER BY m_zakaz_s_cat.id
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        if ($myrow['tip']=='Товар'){$myrow['tip']='1';}
        elseif ($myrow['tip']=='Услуга'){$myrow['tip']='2';}
        $data_['i']['i'][$i]=$myrow['id'];
        $data_['i']['n'][$i]=$myrow['name'];
        $data_['i']['t'][$i]=$myrow['tip'];
        $data_['i']['k'][$i]=$myrow['kolvo'];
        $data_['i']['p'][$i]=$myrow['price'];
        $data_['i']['c'][$i]=$myrow['comments'];
        $data_['i']['img'][$i]=$myrow['img'];
        $data_['i']['pr'][$i]=$myrow['prop_val'];
        
        //ШТРИХ-код

         if ($myrow['tip']=='1'){//товар
            $sql_barcode = "SELECT  m_tovar.id, 
                            m_tovar.barcode,
                            (SELECT IF (m_postav_s_cat.kolvo!=COUNT(m1.id),m_postav_s_cat.kolvo,'1') FROM m_tovar AS m1 WHERE m1.m_postav_s_cat_id=m_postav_s_cat.id) AS kolvo,
                            (SELECT SUM(m_zakaz_s_cat_m_tovar.kolvo) FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id!='"._DB($data_['nomer'])."') AS prodanno,
                            (SELECT IF(SUM(m_zakaz_s_cat_m_tovar.kolvo)>0,SUM(m_zakaz_s_cat_m_tovar.kolvo),0) FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id='"._DB($data_['nomer'])."') AS vibranno
                            
                            
                				FROM m_tovar, m_postav_s_cat
                					WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                    AND m_postav_s_cat.s_cat_id='"._DB($myrow['id'])."'
                					
             ";
            $mt = microtime(true);
            $res_barcode = mysql_query($sql_barcode) or die(mysql_error().'<br/>'.$sql_barcode);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_barcode;$data_['_sql']['time'][]=$mt;
            //echo $sql_barcode;
            for ($myrow_barcode = mysql_fetch_array($res_barcode); $myrow_barcode==true; $myrow_barcode = mysql_fetch_array($res_barcode))
            {
                //print_rf($myrow_barcode);
                
                $data_['i']['m_b'][$i][$myrow_barcode[0]]=$myrow_barcode[1];
                $data_['i']['m_k'][$i][$myrow_barcode[0]]=$myrow_barcode[2]-$myrow_barcode[3];
                $data_['i']['m_v'][$i][$myrow_barcode[0]]=$myrow_barcode[4];
                
            }
        }
        //работники
        if ($myrow['tip']=='2'){//услуга
            $sql_work = "SELECT m_zakaz_s_cat_a_admin_i_post.id, a_admin_i_post.id1,a_admin_i_post.id, m_zakaz_s_cat_a_admin_i_post.summa
            				FROM m_zakaz_s_cat_a_admin_i_post, a_admin_i_post
            					WHERE m_zakaz_s_cat_a_admin_i_post.id1='"._DB($myrow['m_zakaz_s_cat_id'])."' 
            					AND a_admin_i_post.id=m_zakaz_s_cat_a_admin_i_post.id2
                                
            	"; 
            
            $mt = microtime(true);
            $res_work = mysql_query($sql_work) or die(mysql_error().'<br/>'.$sql_work);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_work;$data_['_sql']['time'][]=$mt;
            $myrow_work = mysql_fetch_array($res_work);
            //print_rf($myrow_work);
            if (isset($myrow_work[0]) and $myrow_work[0]>0){
                $data_['i']['w_a'][$i][$myrow_work[0]]=$myrow_work[1];
                $data_['i']['w_p'][$i][$myrow_work[0]]=$myrow_work[2];
                $data_['i']['w_s'][$i][$myrow_work[0]]=$myrow_work[3];
            }
        }
        
        $i++;
    }
    
    
    //Товары и услуги с поступления
    $data_['ip']=array();
    $data_['ip']['i']=array();
    $data_['ip']['n']=array();
    $data_['ip']['t']=array();
    $data_['ip']['p']=array();
    $data_['ip']['k']=array();
    $data_['ip']['c']=array();
    $data_['ip']['b']=array();//штрих-коды
    $data_['ip']['img']=array();//фото
    
    $sql = "SELECT  s_cat.id,
                    s_cat.name,
                    s_cat.tip,
                    m_postav_s_cat.kolvo,
                    m_postav_s_cat.id AS m_postav_s_cat_id,
                    s_cat.price,
                    '' AS comments,
                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id ORDER BY a_photo.sid LIMIT 1) AS img,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id  AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val
                    
        				FROM m_postav_s_cat, s_cat
        					WHERE m_postav_s_cat.m_zakaz_id='"._DB($data_['nomer'])."'
                            AND m_postav_s_cat.s_cat_id=s_cat.id
                            AND s_cat.tip='Товар'
                            ORDER BY m_postav_s_cat.id
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        if ($myrow['tip']=='Товар'){$myrow['tip']='1';}
        elseif ($myrow['tip']=='Услуга'){$myrow['tip']='2';}
        $data_['ip']['i'][$i]=$myrow['id'];
        $data_['ip']['n'][$i]=$myrow['name'];
        $data_['ip']['t'][$i]=$myrow['tip'];
        $data_['ip']['k'][$i]=$myrow['kolvo'];
        $data_['ip']['p'][$i]=$myrow['price'];
        $data_['ip']['c'][$i]=$myrow['comments'];
        $data_['ip']['img'][$i]=$myrow['img'];
        $data_['ip']['pr'][$i]=$myrow['prop_val'];
        
        //ШТРИХ-код

         if ($myrow['tip']=='1'){//товар
            $sql_barcode = "SELECT  m_tovar.id, 
                            m_tovar.barcode,
                            (SELECT IF (m_postav_s_cat.kolvo!=COUNT(m1.id),m_postav_s_cat.kolvo,'1') FROM m_tovar AS m1 WHERE m1.m_postav_s_cat_id=m_postav_s_cat.id) AS kolvo,
                            (SELECT SUM(m_zakaz_s_cat_m_tovar.kolvo) FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id!='"._DB($data_['nomer'])."') AS prodanno,
                            (SELECT IF(SUM(m_zakaz_s_cat_m_tovar.kolvo)>0,SUM(m_zakaz_s_cat_m_tovar.kolvo),0) FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id='"._DB($data_['nomer'])."') AS vibranno
                            
                            
                				FROM m_tovar, m_postav_s_cat
                					WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                    AND m_postav_s_cat.s_cat_id='"._DB($myrow['id'])."'
                					
             ";
            $mt = microtime(true);
            $res_barcode = mysql_query($sql_barcode) or die(mysql_error().'<br/>'.$sql_barcode);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_barcode;$data_['_sql']['time'][]=$mt;
            //echo $sql_barcode;
            for ($myrow_barcode = mysql_fetch_array($res_barcode); $myrow_barcode==true; $myrow_barcode = mysql_fetch_array($res_barcode))
            {
                //print_rf($myrow_barcode);
                
                $data_['ip']['m_b'][$i][$myrow_barcode[0]]=$myrow_barcode[1];
                $data_['ip']['m_k'][$i][$myrow_barcode[0]]=$myrow_barcode[2]-$myrow_barcode[3];
                $data_['ip']['m_v'][$i][$myrow_barcode[0]]=$myrow_barcode[4];
                
            }
        }
        
        $i++;
    }
    
    
    //Платежи
    $data_['pl']=array();
    $data_['pl']['d']=array();
    $data_['pl']['p']=array();
    $data_['pl']['s']=array();
    $data_['pl']['i']=array();
    $data_['pl']['t']=array();
    $sql = "SELECT  m_platezi.data,
                    m_platezi.summa,
                    m_platezi.i_scheta_id,
                    m_platezi.id,
                    m_platezi.tip
                    
        				FROM m_platezi 
        					WHERE m_platezi.id_z_p_p='"._DB($data_['nomer'])."'
                            AND m_platezi.a_menu_id='16'
        						ORDER BY m_platezi.id 
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
    
        $data_['pl']['d'][$i]=date('d.m.Y H:i',strtotime($myrow['data']));
        $data_['pl']['p'][$i]=$myrow['summa'];
        $data_['pl']['s'][$i]=$myrow['i_scheta_id'];
        $data_['pl']['i'][$i]=$myrow['id'];
        $data_['pl']['t'][$i]=$myrow['tip'];
        
        $i++;
    }
    
    //ФАЙЛЫ
    
    $data_['fl']=array();
    $data_['fl']['f']=array();
    $data_['fl']['c']=array();
    $sql = "SELECT a_photo.img, a_photo.comments
        				FROM a_photo 
        					WHERE a_photo.a_menu_id='16'
                            AND a_photo.row_id='"._DB($data_['nomer'])."'
                            AND a_photo.tip='Основное'
                            
                            ORDER BY a_photo.sid
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
    
        $data_['fl']['f'][$i]=$myrow['img'];
        $data_['fl']['c'][$i]=$myrow['comments'];
        $i++;
    }
    
    //доставка
    $sql = "SELECT  m_dostavka.i_tk_id,
                    m_dostavka.fio,
                    m_dostavka.index_,
                    m_dostavka.i_city_id,
                    (SELECT i_city.name FROM i_city WHERE i_city.id=m_dostavka.i_city_id) AS i_city_name,
                    m_dostavka.tracking_number,
                    m_dostavka.adress,
                    m_dostavka.phone,
                    m_dostavka.summa,
                    m_dostavka.data,
                    m_dostavka.chk_active
                    
    				FROM m_dostavka 
    					WHERE m_dostavka.m_zakaz_id='"._DB($data_['nomer'])."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['dost']['i_tk_id']=$myrow['i_tk_id'];
    $data_['dost']['fio']=$myrow['fio'];
    $data_['dost']['index_']=$myrow['index_'];
    $data_['dost']['i_city_id']=$myrow['i_city_id'];
    $data_['dost']['i_city_name']=$myrow['i_city_name'];
    $data_['dost']['tracking_number']=$myrow['tracking_number'];
    $data_['dost']['adress']=$myrow['adress'];
    $data_['dost']['phone']=$myrow['phone'];
        if ($data_['dost']['phone']!=''){$data_['dost']['phone']=conv_('phone_from_db',$data_['dost']['phone']);}
    $data_['dost']['summa']=$myrow['summa'];
    $data_['dost']['data']=$myrow['data'];
    if ($data_['dost']['data']=='0000-00-00 00:00:00'){$data_['dost']['data']='';}
    if ($data_['dost']['data']!=''){
        $data_['dost']['data']=date('d.m.Y',strtotime($data_['dost']['data']));
    }
    $data_['dost']['chk_active']=$myrow['chk_active'];
    
    //получение данных о ремонте
    
    $data_['rem']['stat']='';
    $data_['rem']['mi']='';
    $data_['rem']['ti']='';
    $data_['rem']['bi']='';
    $data_['rem']['bn']='';
    $data_['rem']['mn']='';
    $data_['rem']['sn']='';
    $data_['rem']['k']='';
    $data_['rem']['s']='';
    $data_['rem']['si']='';
    $data_['rem']['n']=array();
    $data_['rem']['nn']=array();
    
    $sql = "SELECT IF(COUNT(*)>0,r_service.id,'')
    				FROM r_service 
    					WHERE r_service.m_zakaz_id='"._DB($data_['nomer'])."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $r_service_id=$myrow[0];
    if ($r_service_id!=''){
        $sql = "SELECT  r_service.status,
                        r_service.r_model_id,
                        r_model.r_tip_oborud_id,
                        r_model.r_brend_id,
                        (SELECT IF(COUNT(*)>0,r_brend.name,'') FROM r_brend WHERE r_model.r_brend_id=r_brend.id) AS r_brend_name,
                        r_model.name AS r_model_name,
                        r_service.sn,
                        r_service.komplekt,
                        r_service.sost,
                        r_service.r_service_id,
                        r_service.data_inform,
                        r_service.diagnoz,
                        r_service.data_vidachi
                        
        
        				FROM r_service, r_model
        					WHERE r_service.id='"._DB($r_service_id)."'
                            AND r_model.id=r_service.r_model_id
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $data_['rem']['stat']=$myrow['status'];
        $data_['rem']['mi']=$myrow['r_model_id'];
        $data_['rem']['ti']=$myrow['r_tip_oborud_id'];
        $data_['rem']['bi']=$myrow['r_brend_id'];
        $data_['rem']['bn']=$myrow['r_brend_name'];
        $data_['rem']['mn']=$myrow['r_model_name'];
        $data_['rem']['sn']=$myrow['sn'];
        $data_['rem']['k']=$myrow['komplekt'];
        $data_['rem']['s']=$myrow['sost'];
        $data_['rem']['si']=$myrow['r_service_id'];
        $data_['rem']['di']=$myrow['data_inform'];
        $data_['rem']['dz']=$myrow['diagnoz'];
            if ($data_['rem']['di']=='0000-00-00 00:00:00'){
                $data_['rem']['di']=''; 
            }
            else{
                $data_['rem']['di']=date('d.m.Y H:i:s',strtotime($data_['rem']['di']));
            }
          
        $data_['rem']['dv']=$myrow['data_vidachi'];
        if ($data_['rem']['dv']!=''){
            if ($data_['rem']['dv']=='0000-00-00 00:00:00'){
                $data_['rem']['dv']='';
            }else{
                $data_['rem']['dv']=date('d.m.Y H:i',strtotime($data_['rem']['dv']));
            }
        }
        $sql = "SELECT r_service_r_neispravnosti.id2, r_neispravnosti.name
            				FROM r_service_r_neispravnosti, r_neispravnosti 
            					WHERE r_service_r_neispravnosti.id1='"._DB($r_service_id)."'
                                AND r_neispravnosti.id=r_service_r_neispravnosti.id2
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
        $data_['rem']['n'][]=$myrow[0];
        $data_['rem']['nn'][]=$myrow[1];
        }
    }
    
    
    
    //Логи
    $sql = "SELECT m_log.id, m_log.data_create, m_log.text, (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE m_log.a_admin_id=a_admin.id) a_admin_name
    				, (SELECT IF(COUNT(*)>0,m_log_type.name,'') FROM m_log_type WHERE m_log_type.id=m_log.m_log_type_id LIMIT 1) AS m_log_type_name
                    FROM m_log 
    					WHERE m_log.a_menu_id='16'
                        AND m_log.id_z_p_p='"._DB($data_['nomer'])."'
                        ORDER BY m_log.data_create
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['log']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['log'][$i]=array();
        $data_['log'][$i]['t']=$myrow['m_log_type_name'];
        $data_['log'][$i]['l']=$myrow['text'];
        $data_['log'][$i]['i']=$myrow['id'];
        $data_['log'][$i]['a']=$myrow['a_admin_name'];
        $data_['log'][$i]['d']=date('d.m.Y H:i:s',strtotime($myrow['data_create']));
    }
    
    echo json_encode($data_);
}

// ************************************************************
// СОХРАНЕНИЕ ЗАКАЗА
//**************************************************************

if ($_t=='m_zakaz__save'){
    $message='';
    $data_=array();
    $data_['nomer']=_GP('nomer');
    $data_['project_name']=_GP('project_name');
    $data_['i_tp_id']=_GP('i_tp_id');
    $data_['a_admin_id']=_GP('a_admin_id');
    $data_['a_admin_otvet_id']=_GP('a_admin_otvet_id');
    $data_['i_contr_id']=_GP('i_contr_id');
    $data_['i_contr_org_id']=_GP('i_contr_org_id');
    $data_['c_call_client_id']=_GP('c_call_client_id');//оформление заказа по звонку
    $data_['data']=_GP('data');
    $data_['log_t']=_GP('log_t');
    $data_['log_l']=_GP('log_l');
    $data_['log_d']=_GP('log_d');
    $data_['data_info']=_GP('data_info');
        $data_['data']=date('Y-m-d H:i:s',strtotime($data_['data']));
        
        if ($data_['data_info']!=''){
            $data_['data_info']=date('Y-m-d H:i:s',strtotime($data_['data_info']));
        }
    $data_['status']=_GP('status','В обработке');
    $data_['tip_pay']=_GP('tip_pay','Оплата отключена');
    $data_['comments']=_GP('comments');
    
    //сервис
    $data_['r_status']=_GP('r_status');
    $data_['r_tip_oborud']=_GP('r_tip_oborud');
    $data_['r_brend']=_GP('r_brend');
    $data_['r_model']=_GP('r_model');
    $data_['komplekt']=_GP('komplekt');
    $data_['r_neispravnosti']=_GP('r_neispravnosti');
   // print_rf($data_['r_neispravnosti']);exit;
     if ($data_['r_tip_oborud']!=''){
        if (!isset($data_['r_neispravnosti']) or !is_array($data_['r_neispravnosti'])){echo 'Не указанны неисправности!';exit;}
     }
         
    $data_['sost']=_GP('sost');
    $data_['r_service_id']=_GP('r_service_id');
    $data_['diagnoz']=_GP('diagnoz');
    $data_['data_vidachi']=_GP('data_vidachi');
        if ($data_['data_vidachi']!=''){$data_['data_vidachi']=$data_['data_vidachi'].':00';$data_['data_vidachi']=date('Y-m-d H:i:s',strtotime($data_['data_vidachi']));}
        else{$data_['data_vidachi']='0000-00-00 00:00:00';}
    
    //доставка
    $data_['m_dostavka_chk_active']=_GP('m_dostavka_chk_active');

    if ($data_['m_dostavka_chk_active']=='true'){
        $data_['m_dostavka_chk_active']='1';
    }
    else{
        $data_['m_dostavka_chk_active']='0';
    }
    $data_['m_dostavka_tracking_number']=_GP('m_dostavka_tracking_number');
    $data_['i_tk_id']=_GP('i_tk_id');
    $data_['m_dostavka_fio']=_GP('m_dostavka_fio');
    $data_['m_dostavka_city_id']=_GP('m_dostavka_city_id');
    $data_['m_dostavka_adress']=_GP('m_dostavka_adress');
    $data_['m_dostavka_index']=_GP('m_dostavka_index');
    $data_['m_dostavka_phone']=_GP('m_dostavka_phone');
    
        if ($data_['m_dostavka_phone']!=''){$data_['m_dostavka_phone']=conv_('phone_to_db',$data_['m_dostavka_phone']);}
    $data_['m_dostavka_summa']=_GP('m_dostavka_summa')-0;
    $data_['m_dostavka_data']=_GP('m_dostavka_data');
    if ($data_['m_dostavka_data']!=''){
        $data_['m_dostavka_data']=date('Y-m-d',strtotime($data_['m_dostavka_data']));
    }
        if ($data_['m_dostavka_data']==date('d.m.Y')){
            $data_['m_dostavka_data']=$data_['m_dostavka_data'].' '.date('H:i:s');
        }else{
            $data_['m_dostavka_data']=$data_['m_dostavka_data'].' 12:00:00';
        }
    
    $data_['html_code']=_GP('html_code');
    
    //$data_['item']=_GP('item');//товары услуги
    $data_['pl']=_GP('pl');//Платежи
    $data_['fl']=_GP('fl');//Файлы
    
    
    $data_['item']['id']=array();
    $data_['item']['price']=array();
    $data_['item']['kol']=array();
    $data_['item']['comments']=array();
    $data_['item']['barcode']=array();
    $data_['item']['worker']=array();
    
    
    $data_['i_i']=_GP('i_i');//товары услуги
    $data_['i_p']=_GP('i_p');//товары услуги
    $data_['i_k']=_GP('i_k');//товары услуги
    $data_['i_c']=_GP('i_c');//товары услуги
    $data_['i_b']=_GP('i_b');//товары услуги
    $data_['i_w']=_GP('i_w');//товары услуги
    
    if (isset($data_['i_i']) and $data_['i_i']!=''){
        if (mb_strstr($data_['i_i'],'|||',false,'utf-8')==true){
            $data_['item']['id']=explode('|||',$data_['i_i']);
        }else{
            $data_['item']['id'][0]=$data_['i_i'];
        }
    }
    if (isset($data_['i_p']) and $data_['i_p']!=''){
        if (mb_strstr($data_['i_p'],'|||',false,'utf-8')==true){
            $data_['item']['price']=explode('|||',$data_['i_p']);
        }else{
            $data_['item']['price'][0]=$data_['i_p'];
        }
    }
    if (isset($data_['i_k']) and $data_['i_k']!=''){
        if (mb_strstr($data_['i_k'],'|||',false,'utf-8')==true){
            $data_['item']['kol']=explode('|||',$data_['i_k']);
        }else{
            $data_['item']['kol'][0]=$data_['i_k'];
        }
    }
    if (isset($data_['i_c'])){
        if (mb_strstr($data_['i_c'],'|||',false,'utf-8')==true){
            $data_['item']['comments']=explode('|||',$data_['i_c']);
        }else{
            $data_['item']['comments'][0]=$data_['i_c'];
        }
    }
    if (isset($data_['i_b']) and $data_['i_b']!=''){
        if (mb_strstr($data_['i_b'],'|||',false,'utf-8')==true){
            $data_['item']['barcode']=explode('|||',$data_['i_b']);
        }else{
            $data_['item']['barcode'][0]=$data_['i_b'];
        }
    }
    if (isset($data_['i_w']) and $data_['i_w']!=''){
        if (mb_strstr($data_['i_w'],'|||',false,'utf-8')==true){
            $data_['item']['worker']=explode('|||',$data_['i_w']);
        }else{
            $data_['item']['worker'][0]=$data_['i_w'];
        }
    }
    
    //Добавляем новый заказ
    if ($data_['nomer']==''){
        $sql = "INSERT into m_zakaz (
        				status
                        
        			) VALUES (
        				'"._DB($data_['status'])."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        	else{$data_['nomer'] = mysql_insert_id();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    }
    
    if ($data_['project_name']==''){
        $data_['project_name']='Заказ №'.$data_['nomer'];
    }else{
        $data_['project_name']=str_replace(array('@@id@@','@@data@@'),array($data_['nomer'],$data_['data']),$data_['project_name']);
    }
    
    //
    if ($data_['c_call_client_id']!=''){
        $sql_upp = "
        		UPDATE c_call_client
        			SET  
        				m_zakaz_id='"._DB($data_['nomer'])."'
        		
        		WHERE id='"._DB($data_['c_call_client_id'])."'
        ";
        $res = mysql_query($sql_upp) or die(mysql_error().'<br />'.$sql_upp);
    }
    
    
    //*****************************************************************************************************************
    //**********************************************************************************************************
    
    
    //массив старого товара в заказе
    $m_tovar_arr=array();
    $m_tovar_arr['s_cat_id']=array();
    $m_tovar_arr['kolvo']=array();
    $m_tovar_arr['m_zakaz_s_cat_m_tovar_id']=array();
    

    //ПОЛУЧАЕМ МАССИВ ТЕКУЩИХ ТОВАРОВ
    $sql = "SELECT 
                    m_zakaz_s_cat.id,
                    m_zakaz_s_cat.s_cat_id,
                    m_zakaz_s_cat.kolvo
                    
                    
        				FROM m_zakaz_s_cat
        					WHERE m_zakaz_s_cat.m_zakaz_id='"._DB($data_['nomer'])."'
        					
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $s_cat_id_arr=array();//массив всех товаров
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        
        $m_zakaz_s_cat_id=$myrow[0];
        $s_cat_id_arr[$myrow[1]]=$myrow[1];
        $m_tovar_arr['s_cat_id'][$m_zakaz_s_cat_id]=$myrow[1];
        $m_tovar_arr['kolvo'][$m_zakaz_s_cat_id]=$myrow[2];
        $m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id]=array();
        $sql_m_tovar = "SELECT  m_zakaz_s_cat_m_tovar.id2, m_zakaz_s_cat_m_tovar.id
            				FROM m_zakaz_s_cat_m_tovar 
            					WHERE m_zakaz_s_cat_m_tovar.id1='"._DB($m_zakaz_s_cat_id)."' 
            					
         ";
        $mt = microtime(true);
        $res_m_tovar = mysql_query($sql_m_tovar) or die(mysql_error().'<br/>'.$sql_m_tovar );
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_tovar;$data_['_sql']['time'][]=$mt;
        for ($myrow_m_tovar = mysql_fetch_array($res_m_tovar); $myrow_m_tovar==true; $myrow_m_tovar = mysql_fetch_array($res_m_tovar))
        {
            $m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id][$myrow_m_tovar[0]]=$myrow_m_tovar[1];
        }
        
       
    }
    
    //массив новых товаров
    $m_tovar_new_arr=array();
    $m_tovar_new_arr['s_cat_id']=array();
    $m_tovar_new_arr['kolvo']=array();
    $m_tovar_new_arr['m_zakaz_s_cat_m_tovar_id']=array();
    
    //ПРОВЕРЯЕМ ЕСТЬ ЛИ ТОВАР В ЗАКАЗЕ 
    $kol_ins=0;$status_chk=1;$status_chk_no_all=0;
    if (isset($data_['item']['id']) and is_array($data_['item']['id'])){
        foreach($data_['item']['id'] as $key => $s_cat_id){// перебор по новому товару в заказе
            $s_cat_id_arr[$s_cat_id]=$s_cat_id;
            
            
            //Статус доставки - панель доставок
            $STATUS_dostavki="";
            if ($data_['m_dostavka_chk_active']=='true'){
                $sql_sel="SELECT 
                           status_dostavki
                                FROM m_zakaz_s_cat
                                WHERE id='"._DB($m_zakaz_s_cat_id)."'
                                ";
                $mt = microtime(true);
                $res_sel = mysql_query($sql_sel) or die(mysql_error().'<br/>'.$sql_sel);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_sel;$data_['_sql']['time'][]=$mt;
                $myrow_sel = mysql_fetch_array($res_sel);
                if ($myrow_sel[0]=='Доработка'){
                    $STATUS_dostavki="status_dostavki='Не заказан'";
                }
            }
            
            
            if (in_array($s_cat_id,$m_tovar_arr['s_cat_id'])){//ЕСЛИ ЕСТЬ - ОБНОВЛЯЕМ
                $m_zakaz_s_cat_id=array_search($s_cat_id,$m_tovar_arr['s_cat_id']);
                $sql = "UPDATE m_zakaz_s_cat 
                			SET  
                				kolvo='"._DB(@$data_['item']['kol'][$key])."',
                				price='"._DB(@$data_['item']['price'][$key])."',
                                comments='"._DB(@$data_['item']['comments'][$key])."'
                                $STATUS_dostavki
                		
                		WHERE id='"._DB($m_zakaz_s_cat_id)."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                //удаляем из массива товаров присутствующий
                unset($m_tovar_arr['s_cat_id'][$m_zakaz_s_cat_id],$m_tovar_arr['kolvo'][$m_zakaz_s_cat_id]);
                
            }else{//ЕСЛИ НЕТ - ДОБАВЛЯЕМ
                $sql = "INSERT into m_zakaz_s_cat (
                				m_zakaz_id,
                				s_cat_id,
                                kolvo,
                                price,
                                comments
                			) VALUES (
                                '"._DB($data_['nomer'])."',
                                '"._DB($s_cat_id)."',
                                '"._DB(@$data_['item']['kol'][$key])."',
                                '"._DB(@$data_['item']['price'][$key])."',
                				'"._DB(@$data_['item']['comments'][$key])."'
                )";
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $m_zakaz_s_cat_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                //заполнем массив новых товаров
                $m_tovar_new_arr['s_cat_id'][$m_zakaz_s_cat_id]=$s_cat_id;
                $m_tovar_new_arr['kolvo'][$m_zakaz_s_cat_id]=$data_['item']['kol'][$key];
                
                $kol_ins++;
            }
            
            //ОБРАБАТЫВАЕМ ШТРИХ-КОДЫ
            $barcode='';
            if (isset($data_['item']['barcode'][$key])){
                $barcode=$data_['item']['barcode'][$key];
            }
            
            $barcode_arr=array();
                if ($barcode!=''){

                        if (strstr($barcode,'@@')==true){
                            $barcode_arr=explode('@@',$barcode);
                        }else{
                            $barcode_arr[0]=$barcode;
                        }
                        
                        //Получаем штрих-коды и номера товаров
                        foreach ($barcode_arr as $key_barcode => $val){
                            if (strstr($barcode,'##')==true){
                                $barcode_arr2=explode('##',$val);
                                
                               
                                
                                $m_tovar_id=$barcode_arr2[0];
                                $m_tovar_barcode=$barcode_arr2[1];//не нужен
                                $m_tovar_kolvo_all=$barcode_arr2[2];//не нужен
                                $m_tovar_kolvo_select=$barcode_arr2[3];
                                
                                //----------------
                                //---- ПРОВЕРИТЬ ТуТ - не сохранеяет штриъ коды при  первом переборе
                                if ($m_tovar_kolvo_select>0){// and isset($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id]) and is_array($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id])){
                                     //print_rf($barcode_arr2);
                                    
                                    if (!isset($m_tovar_arr['m_zakaz_s_cat_m_tovar_id']) or !isset($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id]) or !isset($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id][$m_tovar_id])){//добавляем
                                        
                                        $sql = "INSERT into m_zakaz_s_cat_m_tovar (
                                        				id1,
                                        				id2,
                                                        kolvo
                                        			) VALUES (
                                        				'"._DB($m_zakaz_s_cat_id)."',
                                        				'"._DB($m_tovar_id)."',
                                                        '"._DB($m_tovar_kolvo_select)."'
                                        )";
                                        
                                        $mt = microtime(true);
                                        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                        $m_tovar_new_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id][$m_tovar_id] = mysql_insert_id();
                                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                        
                                    }else{//обновляем
                                        $sql = "
                                        		UPDATE m_zakaz_s_cat_m_tovar 
                                        			SET  
                                        				id1='"._DB($m_zakaz_s_cat_id)."',
                                        				id2='"._DB($m_tovar_id)."',
                                                        kolvo='"._DB($m_tovar_kolvo_select)."'
                                        		
                                        		WHERE id='"._DB($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id][$m_tovar_id])."'
                                        ";
                                        $mt = microtime(true);
                                        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                                        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                                        unset($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id][$m_tovar_id]);
                                    }
                                }
                            }else{
                                echo 'Не определен id товара ('.$s_cat_id.'): '.$barcode.'!';exit;
                            }
                        }
                }
                
                $sql = "DELETE 
                			FROM m_zakaz_s_cat_a_admin_i_post 
                				WHERE id1='"._DB($m_zakaz_s_cat_id)."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                //Обрабатываем услуги
                if (isset($data_['item']['worker'][$key])){
                    $worker=$data_['item']['worker'][$key];
                    if ($worker!=''){
                        if (strstr($worker,'@@')==true){
                            $worker_arr=explode('@@',$worker);
                            $a_admin_id=$worker_arr[0];
                            $a_admin_i_post_id=$worker_arr[1];
                            $summa=$worker_arr[2];
                            
                            $sql = "INSERT into m_zakaz_s_cat_a_admin_i_post (
                            				id1,
                            				id2,
                                            summa
                            			) VALUES (
                            				'"._DB($m_zakaz_s_cat_id)."',
                            				'"._DB($a_admin_i_post_id)."',
                                            '"._DB($summa)."'
                            )";
                            
                            $mt = microtime(true);
                            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                        }
                    }
                }
                if ((isset($data_['item']['worker']) and isset($data_['item']['worker'][$key]) and isset($data_['item']['barcode']) and isset($data_['item']['barcode'][$key])) and ($data_['item']['barcode'][$key]!='' or $data_['item']['worker'][$key]!='')){
                    $status_chk_no_all=1;
                }
                
                if ((!isset($data_['item']['worker']) or !isset($data_['item']['worker'][$key]) or $data_['item']['worker'][$key]=='') and (!isset($data_['item']['barcode']) or !isset($data_['item']['barcode'][$key]) or $data_['item']['barcode'][$key]=='') ){
                    $status_chk=0;
                }
                
        }
        $data_done='';
        if ($status_chk==1){
            
            $sql = "SELECT status, data_done
            				FROM m_zakaz 
            					WHERE id='"._DB($data_['nomer'])."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $status=$myrow[0];
            $data_done_old=$myrow[1];
            
            $data_['status']='Выполнен';
            if ($status!=$data_['status'] and $data_done_old=='0000-00-00 00:00:00'){
                $data_done=", data_done='".date('Y-m-d H:i:s')."'";
            }
        }else{
            if ($status_chk_no_all==1){
                $data_['status']='Частично выполнен';
            }
        }
        
        //ОБНОВЛЯЕМ ЗАКАЗ
        $sql = "
        		UPDATE m_zakaz 
        			SET  
        				i_tp_id='"._DB($data_['i_tp_id'])."',
                        data='"._DB(date('Y-m-d H:i:s',strtotime($data_['data'])))."',
        				a_admin_id='"._DB($data_['a_admin_id'])."',
                        a_admin_otvet_id='"._DB($data_['a_admin_otvet_id'])."',
        				i_contr_id='"._DB($data_['i_contr_id'])."',
        				i_contr_org_id='"._DB($data_['i_contr_org_id'])."',
        				project_name='"._DB($data_['project_name'])."',
        				status='"._DB($data_['status'])."',
        				tip_pay='"._DB($data_['tip_pay'])."',
        				comments='"._DB($data_['comments'])."',
        				html_code='"._DB($data_['html_code'])."',
                        data_end='"._DB($data_['data_info'])."',
                        data_change='".date('Y-m-d H:i:s')."'
                        $data_done
        		WHERE id='"._DB($data_['nomer'])."'
        ";
 
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    }
//*****************************
    //удаляем из базы отсутствующий в новом массиве товар
    foreach($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'] as $m_zakaz_s_cat_id => $barcode_arr){
        foreach($barcode_arr as  $m_tovar_id => $m_zakaz_s_cat_m_tovar_id){
            $sql = "DELETE 
            			FROM m_zakaz_s_cat_m_tovar 
            				WHERE id='"._DB($m_zakaz_s_cat_m_tovar_id)."'
            ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            unset($m_tovar_arr['m_zakaz_s_cat_m_tovar_id'][$m_zakaz_s_cat_id][$m_tovar_id]);
        }
        unset($m_tovar_arr['barcode'][$m_zakaz_s_cat_id]);
    }
    //удаляем товар из поступления
    foreach($m_tovar_arr['s_cat_id'] as $m_zakaz_s_cat_id => $s_cat_id){
        
        if (isset($m_tovar_arr['s_cat_id'][$m_zakaz_s_cat_id])){
            $sql = "DELETE 
            			FROM m_zakaz_s_cat 
            				WHERE id='"._DB($m_zakaz_s_cat_id)."'
            ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            unset($m_tovar_arr['s_cat_id'][$m_zakaz_s_cat_id],$m_tovar_arr['kolvo'][$m_zakaz_s_cat_id]);
        }
        
        
    }
    
//МЕНЯЕМ КОЛИЧЕСТВО ТОВАРА В СПРАВОЧНОМ ПОЛЕ s_cat.kol 
    foreach($s_cat_id_arr as $key => $s_cat_id){
        chk_kol_s_cat_from_id($s_cat_id);
    }
    
    //**************************************************************************************************************
    //*****************************************************************************************************************
    
    //Платежи 
    //Получаем старые платежи
    $pl_old['data']=array();
    $pl_old['i_scheta_id']=array();
    $pl_old['summa']=array();
    $pl_old['a_admin_id']=array();
    $pl_old['tip']=array();
    $pl_old['comments']=array();
    $sql = "SELECT  m_platezi.id,
                    m_platezi.data,
                    m_platezi.i_scheta_id,
                    m_platezi.summa,
                    m_platezi.a_admin_id,
                    m_platezi.tip
				FROM m_platezi 
					WHERE m_platezi.id_z_p_p='"._DB($data_['nomer'])."'
                    AND m_platezi.a_menu_id='16'
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $pl_old['data'][$myrow['id']]=$myrow['data'];
        $pl_old['i_scheta_id'][$myrow['id']]=$myrow['i_scheta_id'];
        $pl_old['summa'][$myrow['id']]=$myrow['summa'];
        $pl_old['a_admin_id'][$myrow['id']]=$myrow['a_admin_id'];
        $pl_old['tip'][$myrow['id']]=$myrow['tip'];
    }
    
    //Проверяем новые платежи
    if (isset($data_['pl']['sum']) and is_array($data_['pl']['sum']) and count($data_['pl']['sum'])>0){
        foreach($data_['pl']['sum'] as $key => $summ_){
            $dt_=date('Y-m-d H:i:s',strtotime($data_['pl']['data'][$key]));
            
            $tip_pl='Кредит';
            if ($summ_-0<0){
                $summ_=$summ_*(-1);
                $tip_pl='Дебет';
            }
            
            if (isset($data_['pl']['id']) and isset($data_['pl']['id'][$key]) and isset($pl_old['summa'][$data_['pl']['id'][$key]])){//обновляем
                
                
                //Получаем старую информацию по существующему платежу
                $sql_old_pl = "SELECT  
                                m_platezi.a_admin_id,
                                m_platezi.data,
                                m_platezi.i_scheta_id,
                                m_platezi.summa,
                                m_platezi.tip,
                                m_platezi.a_menu_id,
                                m_platezi.id_z_p_p,
                                m_platezi.comments,
                                (SELECT IF(COUNT(*)>0,i_scheta.name,'') FROM i_scheta WHERE m_platezi.i_scheta_id=i_scheta.id LIMIT 1) AS i_scheta_name,
                                (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE m_platezi.a_admin_id=a_admin.id LIMIT 1) AS a_admin_name
                                
                                
                				FROM m_platezi 
                					WHERE m_platezi.id='"._DB($data_['pl']['id'][$key])."' 
                				
                	"; 
                
                $mt = microtime(true);
                $res_old_pl = mysql_query($sql_old_pl) or die(mysql_error().'<br/>'.$sql_old_pl);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_old_pl;$data_['_sql']['time'][]=$mt;
                $myrow_old_pl = mysql_fetch_array($res_old_pl);
                
                $tip_txt_old='';$tip_txt_cl_old=' style="color:#090;"';if ($myrow_old_pl['tip']=='Дебет'){$tip_txt_old='-';$tip_txt_cl_old=' style="color:#900;"';}
                $a_menu_txt_old='';
                if ($myrow_old_pl['a_menu_id']=='16'){$a_menu_txt_old='Заказ';}
                
                $sql = "SELECT a_admin.name
                				FROM a_admin 
                					WHERE a_admin.id='"._DB($data_['a_admin_id'])."'
                	"; 
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                $a_admin_name=$myrow[0];
                
                $tip_txt='';$tip_txt_cl=' style="color:#090;"';if ($tip_pl=='Дебет'){$tip_txt='-';$tip_txt_cl=' style="color:#900;"';}
                $txt_name='Заказ'; if($tip_pl=='Дебет'){$txt_name='Возврат по заказу';}
                $a_menu_txt='<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=m_zakaz&nomer='.$data_['nomer'].'">'.$txt_name.' №'.$data_['nomer'].'</a>';
                
                
                $sql = "SELECT i_scheta.name
                				FROM i_scheta 
                					WHERE i_scheta.id='"._DB($data_['pl']['schet'][$key])."'
                	"; 
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                $i_scheta_name=$myrow[0];
                
                $message.='<h1 style="font-size:20px;">Изменен платеж</h1>
                <hr/>
                <h2>Старый платеж</h2>
                <div style="background:#eee;padding:10px;">
                    <p'.$tip_txt_cl_old.'>Сумма: <strong>'.$tip_txt_old.$myrow_old_pl['summa'].'</strong> руб.</p>
                    <p>Тип: <strong>'.$a_menu_txt_old.'</strong>.</p>
                    <p>Счет: <strong>'.$myrow_old_pl['i_scheta_name'].'</strong>.</p>
                    <p>Дата: <strong>'.date('d.m.Y H:i',strtotime($myrow_old_pl['data'])).'</strong>.</p>
                    <p>Работник: <strong>'.$myrow_old_pl['a_admin_name'].'</strong>.</p>
                </div>
                                
                <h2>Новый платеж</h2>
                <div style="background:#d1ffca;padding:10px;">
                    <p'.$tip_txt_cl.'>Сумма: <strong>'.$tip_txt.$summ_.'</strong> руб.</p>
                    <p>Тип: <strong>'.$a_menu_txt.'</strong>.</p>
                    <p>Счет: <strong>'.$i_scheta_name.'</strong>.</p>
                    <p>Дата: <strong>'.date('d.m.Y H:i',strtotime($dt_)).'</strong>.</p>
                    <p>Работник: <strong>'.$a_admin_name.'</strong>.</p>
                </div>
                <hr/>
                ';
                
                $sql = "
                		UPDATE m_platezi 
                			SET  
                				id_z_p_p='"._DB($data_['nomer'])."',
                				data='"._DB($dt_)."',
                				i_scheta_id='"._DB($data_['pl']['schet'][$key])."',
                				summa='"._DB($summ_)."',
                                a_admin_id='"._DB($data_['a_admin_id'])."',
                                tip='"._DB($tip_pl)."'
                                
                		
                		WHERE id='"._DB($data_['pl']['id'][$key])."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                unset($pl_old['summa'][$data_['pl']['id'][$key]],$pl_old['tip'][$data_['pl']['id'][$key]],$pl_old['data'][$data_['pl']['id'][$key]],$pl_old['i_scheta_id'][$data_['pl']['id'][$key]]);
            }
            else{//добавляем
                
                //Получаем остаток по счету после выполнения платежа
                $sql = "SELECT (SELECT SUM(m_platezi.summa)
                				FROM m_platezi 
                					WHERE m_platezi.i_scheta_id='"._DB($data_['pl']['schet'][$key])."'
                                    AND m_platezi.tip='Кредит') AS kredit,
                                (SELECT SUM(m_platezi.summa)
                				FROM m_platezi 
                					WHERE m_platezi.i_scheta_id='"._DB($data_['pl']['schet'][$key])."'
                                    AND m_platezi.tip='Дебет') AS debet		
                	"; 
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                $ostatok=$myrow[0]-$myrow[1];
                if ($tip_pl=='Кредит'){
                    $ostatok=$ostatok+$summ_;
                }
                else{
                    $ostatok=$ostatok-$summ_;
                }
                
                $sql = "INSERT into m_platezi (
                                a_admin_id,
                				id_z_p_p,
                                data,
                				i_scheta_id,
                                summa,
                                a_menu_id,
                                tip,
                                ostatok,
                                a_admin_id_info                           
                                
                			) VALUES (
                                '"._DB($data_['a_admin_id'])."',
                                '"._DB($data_['nomer'])."',
                				'"._DB($dt_)."',
                				'"._DB($data_['pl']['schet'][$key])."',
                                '"._DB($summ_)."',
                                '16',
                                '"._DB($tip_pl)."',
                                '"._DB($ostatok)."',
                                '"._DB($a_admin_id_cur)."'
                )";
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $m_platezi_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                //ОПОВЕЩЕНИЕ АДМИНИСТРАТОРА
                $sql = "SELECT a_admin.name
                				FROM a_admin 
                					WHERE a_admin.id='"._DB($data_['a_admin_id'])."'
                	"; 
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                $a_admin_name=$myrow[0];
                
                $tip_txt='';$tip_txt_cl=' style="color:#090;"';if ($tip_pl=='Дебет'){$tip_txt='-';$tip_txt_cl=' style="color:#900;"';}
                $txt_name='Заказ'; if($tip_pl=='Дебет'){$txt_name='Возврат по заказу';}
                $a_menu_txt='<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=m_zakaz&nomer='.$data_['nomer'].'">'.$txt_name.' №'.$data_['nomer'].'</a>';
                
                
                $sql = "SELECT i_scheta.name
                				FROM i_scheta 
                					WHERE i_scheta.id='"._DB($data_['pl']['schet'][$key])."'
                	"; 
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                $i_scheta_name=$myrow[0];
                
                $message.='<h1 style="font-size:20px;">Добавлен новый платеж</h1>
                            <hr/>
                            <div style="background:#d1ffca;padding:10px;">
                                <p'.$tip_txt_cl.'>Сумма: <strong>'.$tip_txt.$summ_.'</strong> руб.</p>
                                <p>Тип: <strong>'.$a_menu_txt.'</strong>.</p>
                                <p>Счет: <strong>'.$i_scheta_name.'</strong>.</p>
                                <p>Дата: <strong>'.date('d.m.Y H:i',strtotime($dt_)).'</strong>.</p>
                                <p>Работник: <strong>'.$a_admin_name.'</strong>.</p>
                            </div>
                            ';
                //Оповещение
                send_mail_smtp(
                        $_SESSION['a_options']['email администратора'],
                        'Сохранение платежа',
                        $message, 
                        'Администратору платежей',
                        '',
                        'Bot '.$_SERVER['SERVER_NAME']
                );
            }
            
            
        }
    }
    
    //Очищаем удаленные платежи
    if (isset($pl_old['summa']) and is_array($pl_old['summa']) and count($pl_old['summa'])>0){
        $SQL_DEL="";
        foreach($pl_old['summa'] as $m_platezi_id => $val){
            if ($SQL_DEL!=''){$SQL_DEL.="','";}
            $SQL_DEL.=$m_platezi_id;
        }
        if ($SQL_DEL!=''){
            
            $message='';
            $sql = "SELECT m_platezi.id, m_platezi.summa, data, id_z_p_p, i_scheta_id, tip, ostatok
                			FROM m_platezi 
            				    WHERE m_platezi.id IN ('".$SQL_DEL."')
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $message.='<h2>Удален платеж №'.$myrow['id'].' от '.date('d.m.Y H:i',strtotime($myrow['data'])).'</h2>
                <p>Заказ №: <strong>'.$myrow['id_z_p_p'].'</strong> руб.</p>
                <p>Сумма: <strong>'.$myrow['summa'].'</strong> руб.</p>
                <p>Тип: <strong>'.$myrow['tip'].'</strong> руб.</p>
                <p>Счет: <strong>'.$myrow['i_scheta_id'].'</strong> руб.</p>
                <p>Остаток: <strong>'.$myrow['ostatok'].'</strong> руб.</p>
                ';
                log_remove_platezi($myrow['id']);//логируем платеж до удаления
            }
            //Оповещение
            send_mail_smtp(
                    $_SESSION['a_options']['email администратора'],
                    'Удаление платежей',
                    $message, 
                    'Администратору платежей',
                    '',
                    'Bot '.$_SERVER['SERVER_NAME']
            );
            
            $sql = "DELETE 
            			FROM m_platezi 
            				WHERE m_platezi.id IN ('".$SQL_DEL."')
            ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        }
    }
    
    //ФАЙЛЫ, ДОКУМЕНТЫ к ЗАКАЗУ
    
    if (!file_exists('../../i/m_zakaz/')) {@mkdir('../../i/m_zakaz/',0777);}
    if (!file_exists('../../i/m_zakaz/original/')) {@mkdir('../../i/m_zakaz/original/',0777);}
    if (!file_exists('../../i/m_zakaz/small/')) {@mkdir('../../i/m_zakaz/small/',0777);}
    
    $file_arr_old=array();
    
    $sql = "SELECT a_photo.id, a_photo.img
        				FROM a_photo 
        					WHERE a_photo.row_id='"._DB($data_['nomer'])."'
                            AND a_photo.a_menu_id='16'
                            AND a_photo.tip='Основное'
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $file_arr_old[$myrow[0]]=$myrow[1];
    }
    
    //Перебор по новым файлам
        
    if (isset($data_['fl']['f']) and is_array($data_['fl']['f']) and count($data_['fl']['f'])>0)
    {
        $sid_=0;
        foreach($data_['fl']['f'] as $key => $img_){
            if (in_array($img_,$file_arr_old)){//удаляем из старого массива
                $a_photo_id=array_search($img_,$file_arr_old);
                $sql = "
                		UPDATE a_photo 
                			SET  
                                a_photo.sid='"._DB($sid_)."',
                				a_photo.comments='"._DB($data_['fl']['c'][$key])."'
                		
                		WHERE a_photo.id='"._DB($a_photo_id)."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                unset($file_arr_old[$a_photo_id]);
            }else{//добавляем
                //создаем имя файла
                $ext=preg_replace("/.*?\./", '', $img_);
                $comm_=$data_['fl']['c'][$key];
                $name_file=ru_us($comm_).'.'.$ext;
                $path_='../../i/m_zakaz/original/'.$name_file;
                $i=1;
                while(file_exists($path_)){
                    $name_file=ru_us($comm_).'_'.$i.'.'.$ext;
                    $path_='../../i/m_zakaz/original/'.$name_file;
                    $i++;  
                }
                //Копируем файл
                if(!copy('../../i/m_zakaz/temp/'.$img_,$path_)){
                    echo 'Ошибка копирования файла: <br />из: '.'../../i/m_zakaz/original/'.$img_.'<br />в: '.$path_;
                    exit;
                }
                if ($ext=='jpg' or $ext=='jpeg' or $ext=='png' or $ext=='gif'){
                    smart_resize_image($path_,'../../i/m_zakaz/small/'.$name_file, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
                }
                @unlink('../../i/m_zakaz/temp/'.$img_);
                
                //Добавляем в бд
                $sql = "INSERT into a_photo (
                                sid,
                				a_menu_id,
                				row_id,
                                img,
                                comments
                			) VALUES (
                                '"._DB($sid_)."',
                				'16',
                				'"._DB($data_['nomer'])."',
                				'"._DB($name_file)."',
                				'"._DB($data_['fl']['c'][$key])."'
                                
                )";
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $new_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
            }
            $sid_++;
        }
    }
    //удаляем отсутствующие файлы
    $SQL_DEL="";
    foreach($file_arr_old as $a_photo_id => $img_){
        $ext=preg_replace("/.*?\./", '', $img_);
        $file_='../../i/m_zakaz/original/'.$img_;
        if (file_exists($file_) and $img_!=''){
            @unlink($file_);
        }
        if ($ext=='jpg' or $ext=='jpeg' or $ext=='png' or $ext=='gif'){
            $file_='../../i/m_zakaz/small/'.$img_;
            if (file_exists($file_) and $img_!=''){
                @unlink($file_);
            }
        }
        if ($SQL_DEL!=''){$SQL_DEL.="','";}
        $SQL_DEL.=$a_photo_id;
        
    }
    if ($SQL_DEL!=''){
        $sql = "DELETE 
        			FROM a_photo 
        				WHERE a_photo.id IN ('".$SQL_DEL."')
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    }
    
    
    //ДОСТАВКА
    $sql = "DELETE 
    			FROM m_dostavka 
    				WHERE m_dostavka.m_zakaz_id='"._DB($data_['nomer'])."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    $SQL_TK1='';$SQL_TK2='';$SQL_CITY1='';$SQL_CITY2='';
    if ($data_['i_tk_id']!=''){
        $SQL_TK1="i_tk_id,";
        $SQL_TK2="'"._DB($data_['i_tk_id'])."',";
    }
    if ($data_['m_dostavka_city_id']!=''){
        $SQL_CITY1="i_city_id,";
        $SQL_CITY2="'"._DB($data_['m_dostavka_city_id'])."',";
    }
    
    $sql = "INSERT into m_dostavka (
                    data,
    				m_zakaz_id,
    				$SQL_TK1
                    fio,
                    index_,
                    $SQL_CITY1
                	tracking_number,
                    adress,
                    phone,
                    summa,
                    chk_active
    			) VALUES (
                    '"._DB($data_['m_dostavka_data'])."',
                    '"._DB($data_['nomer'])."',
    				$SQL_TK2
                    '"._DB($data_['m_dostavka_fio'])."',
                    '"._DB($data_['m_dostavka_index'])."',
                    $SQL_CITY2
                    '"._DB($data_['m_dostavka_tracking_number'])."',
                    '"._DB($data_['m_dostavka_adress'])."',
                    '"._DB($data_['m_dostavka_phone'])."',
                    '"._DB($data_['m_dostavka_summa'])."',
                    '"._DB($data_['m_dostavka_chk_active'])."'
                    
                    
    )";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    
    //получаем информацию о сервисе
    if ($data_['r_tip_oborud']!=''){
        
        $sql_service = "SELECT IF(COUNT(*)>0,r_service.id,'')
        				FROM r_service 
        					WHERE r_service.m_zakaz_id='"._DB($data_['nomer'])."'
        	"; 
        
        $mt = microtime(true);
        $res_service = mysql_query($sql_service) or die(mysql_error().'<br/>'.$sql_service);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_service;$data_['_sql']['time'][]=$mt;
        $myrow_service = mysql_fetch_array($res_service);
        $r_service_id=$myrow_service[0];
        if ($r_service_id==''){
            $sql = "INSERT into r_service (
            				chk_active
            			) VALUES (
            				'1'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $r_service_id = mysql_insert_id();
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        }
        
        //получаем id модели
        $sql = "SELECT IF(COUNT(*)>0,r_model.id,'')
        				FROM r_model 
        					WHERE  r_model.r_tip_oborud_id='"._DB($data_['r_tip_oborud'])."'
                            AND r_model.r_brend_id='"._DB($data_['r_brend'])."'
                            AND r_model.name='"._DB($data_['r_model'])."'
                           
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $r_model_id=$myrow[0];
        if ($r_model_id==''){
            $sql = "INSERT into r_model (
            				r_tip_oborud_id,
                            r_brend_id,
            				name
            			) VALUES (
            				'"._DB($data_['r_tip_oborud'])."',
            				'"._DB($data_['r_brend'])."',
            				'"._DB($data_['r_model'])."'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $r_model_id = mysql_insert_id();
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
        }
        
        $chk_='0';if ($data_['r_service_id']!=''){$chk_='1';}
        
        //обновляем акт ремонта
        $sql = "UPDATE r_service 
        			SET  
        				chk_active='1',
        				status='"._DB($data_['r_status'])."',
                        data_priem='"._DB(date('Y-m-d H:i:s',strtotime($data_['data'])))."',
                        data_vidachi='',
                        a_admin_id='"._DB($data_['a_admin_id'])."',
                        i_contr_id='"._DB($data_['i_contr_id'])."',
                        m_zakaz_id='"._DB($data_['nomer'])."',
                        r_model_id='"._DB($r_model_id)."',
                        sn='',
                        komplekt='"._DB($data_['komplekt'])."',
                        sost='"._DB($data_['sost'])."',
                        comments='',
                        chk_garant='"._DB($chk_)."',
                        r_service_id='"._DB($data_['r_service_id'])."',
                        diagnoz='"._DB($data_['diagnoz'])."',
                        data_vidachi='"._DB($data_['data_vidachi'])."'
                        
                
                
        		WHERE id='"._DB($r_service_id)."'
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        //Удаляем неисправности
        $sql = "DELETE 
        			FROM r_service_r_neispravnosti 
        				WHERE id1='"._DB($r_service_id)."'
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        //добавляем неисправности
       
        foreach($data_['r_neispravnosti'] as $key => $r_neispravnosti_id){
            $sql = "INSERT into r_service_r_neispravnosti (
            				id1,
            				id2
            			) VALUES (
            				'"._DB($r_service_id)."',
            				'"._DB($r_neispravnosti_id)."'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
        }
    }
    else{//убираем информацию о сервисе, если убрано
        
        $sql = "UPDATE r_service 
        			SET  
        				chk_active='0'
        		
        		WHERE r_service.m_zakaz_id='"._DB($data_['nomer'])."'
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    }
    

    //Логи
    $m_log_type_arr=array();
    $sql = "SELECT m_log_type.id, m_log_type.name
        				FROM m_log_type
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $m_log_type_arr[$myrow[0]]=$myrow[1];
    }
    

    $data_log_t_arr=array();
    $data_log_l_arr=array();
    $data_log_d_arr=array();
    if (isset($data_['log_l']) and $data_['log_l']!=''){
        if (mb_strstr($data_['log_l'],'||',false,'utf-8')==true){
            $data_log_t_arr=explode('||',$data_['log_t']);
            $data_log_l_arr=explode('||',$data_['log_l']);
            $data_log_d_arr=explode('||',$data_['log_d']);
        }else{
            $data_log_t_arr[0]=$data_['log_t'];
            $data_log_l_arr[0]=$data_['log_l'];
            $data_log_d_arr[0]=$data_['log_d'];
        }
    }
    unset($data_['log']);
    $data_['log']=array();
    if (count($data_log_t_arr)>0){
        foreach($data_log_t_arr as $key => $val_){
            if (in_array($val_,$m_log_type_arr)){
            
            $m_log_type_id=array_search($val_,$m_log_type_arr);
            
            $data_['log'][$key]['t']=@$m_log_type_id;
            $data_['log'][$key]['l']=@$data_log_l_arr[$key];
            $data_['log'][$key]['d']=@$data_log_d_arr[$key];
                
            }else{
                echo 'No log type: '.$val_;exit;
            }
        }
    }
    
    $SQL_INS_LOG="";
    if (isset($data_['log']) and is_array($data_['log'])){
        foreach($data_['log'] as $key =>$val_arr){
            if ($SQL_INS_LOG!=''){$SQL_INS_LOG.=',';}
            $SQL_INS_LOG.="('"._DB($a_admin_id_cur)."','16','"._DB($data_['nomer'])."','"._DB($data_['log'][$key]['l'])."','"._DB($data_['log'][$key]['t'])."','"._DB(date('Y-m-d H:i:s',strtotime(@$data_['log'][$key]['d'])))."')";
            
            if (mb_strlen($SQL_INS_LOG,'utf-8')>30000){
                $sql = "INSERT into m_log (
                				a_admin_id,
                				a_menu_id,
                				id_z_p_p,
                                text,
                                m_log_type_id,
                                data_create
                			) VALUES $SQL_INS_LOG";
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                $SQL_INS_LOG='';
            }
        }
        
        if ($SQL_INS_LOG!=''){
            $sql = "INSERT into m_log (
            				a_admin_id,
            				a_menu_id,
            				id_z_p_p,
                            text,
                            m_log_type_id,
                            data_create
            			) VALUES $SQL_INS_LOG";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
        }
    }
    //************************
    
    echo json_encode($data_);
    unset($data_);
}
//************************************************************************************************** 
// ОТМЕНА ЗАКАЗА
if ($_t=='m_zakaz_close'){
    $data_=array();
    $data_['nomer']=_GP('nomer');
        if ($data_['nomer']==''){
            echo 'Не определен номер';exit;
        }
        
    //Номера заказов
    if (is_array($data_['nomer'])){
        $nomer_arr=$data_['nomer'];
    }
    else{
        $nomer_arr=array();
        if (mb_strstr($data_['nomer'],',',false,'utf-8')==true){
            $nomer_arr=explode(',',$data_['nomer']);
        }else{
            $nomer_arr[0]=$data_['nomer'];
        }
    }
    $sql = "
    		UPDATE m_zakaz 
    			SET  
    				status='Отменен',
                    data_change='".date('Y-m-d H:i:s')."'
    		
    		WHERE id IN ('".implode("','",$nomer_arr)."')
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    echo json_encode($data_);
}
//************************************************************************************************** 
// ВОЗВРАТ ЗАКАЗА
if ($_t=='m_zakaz_open'){
    $data_=array();
    $data_['nomer']=_GP('nomer');
        if ($data_['nomer']==''){
            echo 'Не определен номер';exit;
        }
        
    //Номера заказов
    if (is_array($data_['nomer'])){
        $nomer_arr=$data_['nomer'];
    }
    else{
        $nomer_arr=array();
        if (mb_strstr($data_['nomer'],',',false,'utf-8')==true){
            $nomer_arr=explode(',',$data_['nomer']);
        }else{
            $nomer_arr[0]=$data_['nomer'];
        }
    }
    
    $sql = "
    		UPDATE m_zakaz 
    			SET  
    				status='В обработке',
                    data_change='".date('Y-m-d H:i:s')."'
    		
    		WHERE id IN ('".implode("','",$nomer_arr)."')
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    echo json_encode($data_);
}
// ************************************************************
// АВТОЗАПОЛНЕНИЕ ТОВАРА
//**************************************************************

if ($_t=='s_cat_autocomplete'){

    $data_=array();
    $WHERE='';$TABLE=''; 
        
    $term=_GP('term');
    $ORDERBY='';if ($term!=''){$ORDERBY="FIELD(`s_cat`.`name`,'"._DB($term)."') DESC, ";}
    $nalich=_GP('nalich');
        if ($nalich=='true'){
            $WHERE.=" AND s_cat.kolvo>0 ";
        }
    

    
    $s_struktuta_id=_GP('s_struktuta_id');
        if($s_struktuta_id>0){
            $TABLE=', s_struktura, s_cat_s_struktura';
            $WHERE=" 
            AND s_cat_s_struktura.id1=s_cat.id 
            AND s_cat_s_struktura.id2=s_struktura.id 
            AND s_struktura.id='"._DB($s_struktuta_id)."'";
        }
    
    $tip=_GP('tip');if($tip=='1'){$tip='Товар';$data_[0]['value']='Добавить новый товар';}
    else{$tip='Услуга';$data_[0]['value']='Добавить новую услугу';}
    
    
    $data_[0]['id']='-1';
    $data_[0]['p']='';
    $data_[0]['img']='';
    $data_[0]['pr']='';
    
    $sql = "SELECT  
                    s_cat.id,
                    s_cat.name,
                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id AND a_photo.tip='Основное' ORDER BY sid LIMIT 1) AS img,
                    s_cat.price,
                    s_cat.tip,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id  AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val
                    
                    
                    
    
    				FROM s_cat $TABLE
    					WHERE (s_cat.id='"._DB($term)."' 
                        OR s_cat.name LIKE '%"._DB($term)."%'
                        OR s_cat.html_code LIKE '"._DB($term)."%')
                        AND s_cat.name!=''
                        AND s_cat.tip='"._DB($tip)."'
                        $WHERE
                        ORDER BY $ORDERBY s_cat.pop DESC, s_cat.name
                        LIMIT 20
    ";
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        
        $data_[$i]['img']='';
        $img='../../i/s_cat/small/'.$myrow['img'];
        if (file_exists($img) and $myrow['img']!=''){
            $data_[$i]['img']=$myrow['img'];
        }
        
        $data_[$i]['p']=number_format($myrow['price'],0,'.','');
        $data_[$i]['value']=$myrow[1];
        
        $data_[$i]['pr']=$myrow['prop_val'];
        $data_[$i]['id']=$myrow[0];
        if ($myrow[4]=='Услуга'){
            $data_[$i]['t']='2';
        }else{
            $data_[$i]['t']='1';
        }
        
        $data_[$i]['b']=array();
        $data_[$i]['k']=array();
        
        //ШТРИХ-код
         if ($myrow[4]=='Товар'){
            $sql_barcode = "SELECT  m_tovar.id, 
                            m_tovar.barcode, 
                            (SELECT IF (m_postav_s_cat.kolvo!=COUNT(m1.id),m_postav_s_cat.kolvo,'1') FROM m_tovar AS m1 WHERE m1.m_postav_s_cat_id=m_postav_s_cat.id) AS kolvo,
                            (SELECT SUM(m_zakaz_s_cat_m_tovar.kolvo) FROM m_zakaz_s_cat_m_tovar WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id) AS prodanno
                            
                            
                            
                				FROM m_tovar, m_postav_s_cat
                					WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                    AND m_postav_s_cat.s_cat_id='"._DB($myrow[0])."'
                					
             ";
            $mt = microtime(true);
            $res_barcode = mysql_query($sql_barcode) or die(mysql_error().'<br/>'.$sql_barcode);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_barcode;$data_['_sql']['time'][]=$mt;
            for ($myrow_barcode = mysql_fetch_array($res_barcode); $myrow_barcode==true; $myrow_barcode = mysql_fetch_array($res_barcode))
            {
                //print_rf($myrow_barcode);
                $data_[$i]['b'][$myrow_barcode[0]]=$myrow_barcode[1];
                $data_[$i]['k'][$myrow_barcode[0]]=$myrow_barcode[2]-$myrow_barcode[3];
                
            }
            if ($nalich=='true' and count($data_[$i]['b'])==0){
                unset( $data_[$i]);
                $i--;
            }
            //echo '<br /><br /><br /><br />';
        }
        
    }
    echo json_encode($data_);
}

// ************************************************************
// ПОИСК КОНТРАГЕНТА
//**************************************************************
elseif ($_t=='i_contr_autocomplete'){
    
    $data_=array();
    $term=_GP('term','');
    
    $data_[0]['label']='Добавить нового контрагента';
    $data_[0]['value']=$term;
    $data_[0]['i_contr']['id']='-1';
    $data_[0]['i_contr']['phone']='';
    $data_[0]['i_contr']['name']='';
    $data_[0]['i_contr']['email']='';
    $data_[0]['i_contr_org']['id']='';
    $data_[0]['i_contr_org']['phone']='';
    $data_[0]['i_contr_org']['name']='';
    $data_[0]['i_contr_org']['email']='';
    $data_[0]['active_id']='0';
    $data_[0]['text']='';
    

    
    $sql = "SELECT  i_contr.id AS i_contr_id, 
                    i_contr.name AS i_contr_name, 
                    i_contr.`phone` AS i_contr_phone, 
                    i_contr.`email` AS i_contr_email, 
                    (SELECT COUNT(m_zakaz.id) FROM m_zakaz WHERE m_zakaz.i_contr_id=i_contr.id) AS cnt_
    				
                    FROM i_contr
    					WHERE i_contr.id='"._DB($term)."'
                        OR i_contr.name LIKE '%"._DB($term)."%'
                        OR i_contr.`phone` LIKE '"._DB($term)."%'
                        OR i_contr.`email` LIKE '"._DB($term)."%'
   						OR i_contr.id IN (SELECT i_contr_i_contr_org.id1 FROM i_contr_org, i_contr_i_contr_org WHERE i_contr_i_contr_org.id2=i_contr_org.id AND i_contr_org.name LIKE '%"._DB($term)."%' OR i_contr_org.inn LIKE '%"._DB($term)."%')
                        
                        ORDER BY cnt_ DESC
                        LIMIT 20
    ";
     
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_[$i]['label']=$myrow['i_contr_name'];
        $data_[$i]['value']=$myrow['i_contr_name'];
        $data_[$i]['text']=$myrow['i_contr_name'];
        $data_[$i]['id']=$myrow['i_contr_id'];
        
        //выбор организаций
        //2018-05-17 toowin86
        $sql_i_contr_org = "SELECT  i_contr_org.id, 
                                        i_contr_org.name, 
                                        i_contr_org.phone, 
                                        i_contr_org.email
                        FROM i_contr_org, i_contr_i_contr_org
        					WHERE i_contr_i_contr_org.id2=i_contr_org.id
                            AND i_contr_i_contr_org.id1='"._DB($myrow[0])."'
        ";
         
        $res_i_contr_org = mysql_query($sql_i_contr_org);if (!$res_i_contr_org){echo $sql_i_contr_org;exit();}
        $data_[$i]['i_contr_org']['id']=array();
        $data_[$i]['i_contr_org']['name']=array();
        $data_[$i]['i_contr_org']['phone']=array();
        $data_[$i]['i_contr_org']['email']=array();
        for ($myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j=0; $myrow_i_contr_org==true; $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j++)
        {
            $data_[$i]['i_contr_org']['id'][$j]=$myrow_i_contr_org['id'];
            $data_[$i]['i_contr_org']['name'][$j]=$myrow_i_contr_org['name'];
            $data_[$i]['i_contr_org']['phone'][$j]=$myrow_i_contr_org['phone'];
                if ($data_[$i]['i_contr_org']['phone'][$j]!=''){$data_[$i]['i_contr_org']['phone'][$j]=conv_('phone_from_db',$myrow_i_contr_org['phone']);}
            $data_[$i]['i_contr_org']['email'][$j]=''; if ($myrow_i_contr_org['email']!='') {$data_[$i]['i_contr_org']['email'][$j]=$myrow_i_contr_org['email']; }
        }
        $data_[$i]['active']=0; if (count($data_[$i]['i_contr_org']['id'])>0){$data_[$i]['active']=$data_[$i]['i_contr_org']['id'][0];}
        
        $data_[$i]['i_contr']['id']=$myrow['i_contr_id'];
        $data_[$i]['i_contr']['name']=$myrow['i_contr_name'];
        $data_[$i]['i_contr']['phone']=$myrow['i_contr_phone'];
            if ($data_[$i]['i_contr']['phone']!=''){$data_[$i]['i_contr']['phone']=conv_('phone_from_db',$myrow['i_contr_phone']);}
        $data_[$i]['i_contr']['email']=''; if ($myrow['i_contr_email']!='') {$data_[$i]['i_contr']['email']=$myrow['i_contr_email']; }
        //end 2018-05-17 toowin86
        
    }
    echo json_encode($data_);
}

// ************************************************************
// ПОИСК ТК
//**************************************************************
elseif ($_t=='i_tk_autocomplete'){
    $data_=array();

    
    
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('term');
    $sql = "SELECT  i_tk.id AS i_tk_id, 
                    i_tk.name AS i_tk_name
                    
    				
                    FROM i_tk
    					WHERE i_tk.id='"._DB($term)."'
                        OR i_tk.name LIKE '%"._DB($term)."%'
                      
                        LIMIT 20
     ";
     
    $data_['items']=array();
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;$i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['items'][$i]['name']=$myrow['i_tk_name'];
        $data_['items'][$i]['text']=$myrow['i_tk_name'];
        $data_['items'][$i]['id']=$myrow['i_tk_id'];
        $i++;
    }

    echo json_encode($data_);
}

// ************************************************************
// ФОРМА КОНТРАГЕНТА
//**************************************************************
elseif ($_t=='i_contr_form'){
    $nomer=_GP('nomer');
        $title='Добавление нового контрагента <input type="hidden" name="nomer" value="" />';
        if ($nomer!=''){$title='Изменение контрагента №'.$nomer.' <input type="hidden" name="nomer" value="'._IN($nomer).'" />';}
        
      // получаем массив всех отображаемых столбцов
    $sql = "SELECT  a_col.id,
                    a_col.col,
                    a_col.col_ru,
                    a_col.tip,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_change='1'
                        AND a_col.a_menu_id='25'
                        AND a_col.id IN (
                                            SELECT a_admin_a_col.id2
                                                FROM a_admin_a_col, a_admin
                                                    WHERE a_admin_a_col.id1=a_admin.id
                                                        AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                                                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                        )
                    ORDER BY a_col.sid
    "; 
    $res = mysql_query($sql) or die(mysql_error());
    
    $data_['col']=array();$data_['col_ru']=array();$data_['tip']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
    }
    if (!in_array('i_reklama_id',$data_['col']) or !in_array('name',$data_['col']) 
        or ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='1' and !in_array('phone',$data_['col']))
        or ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0' and !in_array('email',$data_['col']))
        ){
            $txt='"Email"';
            if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){$txt='"Телефон"';}
            echo 'Нет доступа к изменению стролбцов "Название" или "Вид рекламы" или '.$txt.' у пользователя: '.$_SESSION['admin']['email'];exit;
    }
    
    if ($nomer!=''){
        //Данные клиента
        $sql = "SELECT  i_contr.chk_active,
                        i_contr.name,
                        i_contr.email,
                        i_contr.phone,
                        i_contr.adress,
                        i_contr.html_code,
                        i_contr.link,
                        i_contr.i_reklama_id,
                        i_contr.i_contr_id
                        
                        FROM i_contr 
        					WHERE i_contr.id='"._DB($nomer)."'
        	"; 
        $res = mysql_query($sql) or die(mysql_error().'<br /><br />'.$sql);
        $myrow_i_contr = mysql_fetch_array($res);
    }
    ?>
    <form class="i_contr_mini_form" name="i_contr_mini_form">
    <h1><?=$title;?></h1>
    
    <div class="i_contr_mini_menu">
        
            
            <div class="ttable">
                <?php
                if (in_array('chk_active',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-toggle-on"></i> Активность</div>
                            <div class="ttable_tbody_tr_td"><label for="i_contr_chk_active" class="i_contr_chk_active_lbl"><input type="checkbox" id="i_contr_chk_active" name="chk_active" value="1" <?php if (isset($myrow_i_contr['chk_active']) and $myrow_i_contr['chk_active']=='0'){}else{echo ' checked="checked"';} ?> /></label></div>
                        </div>
                    <?php
                }
               
                ?>
                    <div class="ttable_tbody_tr mandat">
                        <div class="ttable_tbody_tr_td"><i class="fa fa-user"></i> Ф.И.О.*</div>
                        <div class="ttable_tbody_tr_td"><input type="text" name="name" placeholder="Иванов Иван Иванович" value="<?php if (isset($myrow_i_contr['name']) and $myrow_i_contr['name']!=''){echo _IN($myrow_i_contr['name']);} ?>" /></div>
                    </div>
                <?php
               
                if (in_array('phone',$data_['col'])){
                    $mand='';
                    if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){//Телефон
                        $mand=' mandat';
                    }
                    ?>
                        <div class="ttable_tbody_tr<?=$mand;?>">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-phone"></i> Телефон</div>
                            <div class="ttable_tbody_tr_td"><input class="phone" type="tel" name="phone" placeholder="8(XXX)XXX-XX-XX" value="<?php if (isset($myrow_i_contr['phone']) and $myrow_i_contr['phone']!=''){echo _IN($myrow_i_contr['phone']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('email',$data_['col'])){
                    $mand='';
                    if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0'){//email
                        $mand=' mandat';
                    }
                    ?>
                        <div class="ttable_tbody_tr<?=$mand;?>">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-envelope-o"></i> Email</div>
                            <div class="ttable_tbody_tr_td"><input class="email" type="email" name="email" placeholder="mail@yandex.ru" value="<?php if (isset($myrow_i_contr['email']) and $myrow_i_contr['email']!=''){echo _IN($myrow_i_contr['email']);} ?>" /></div>
                        </div>
                    <?php
                }
               
                ?>
                    <div class="ttable_tbody_tr mandat">
                        <div class="ttable_tbody_tr_td"><i class="fa fa-bullhorn"></i> От куда узнали?</div>
                        <div class="ttable_tbody_tr_td">
                            <select name="i_reklama_id" data-placeholder="Вид рекламы">
                                <option></option>
                                <?php
                                $sql_i_reklama = "SELECT i_reklama.id, i_reklama.name, (SELECT COUNT(i_contr.id) FROM i_contr WHERE i_contr.i_reklama_id=i_reklama.id) AS cnt
                                				FROM i_reklama 
                                					WHERE chk_active='1'
                                						ORDER BY cnt DESC, name
                                ";
                                 
                                $res_i_reklama = mysql_query($sql_i_reklama);if (!$res_i_reklama){echo $sql_i_reklama;exit();}
                                for ($myrow_i_reklama = mysql_fetch_array($res_i_reklama); $myrow_i_reklama==true; $myrow_i_reklama = mysql_fetch_array($res_i_reklama))
                                {
                                    
                                    $sel_='';
                                    if (isset($myrow_i_contr['i_reklama_id']) and $myrow_i_contr['i_reklama_id']!='' and $myrow_i_contr['i_reklama_id']==$myrow_i_reklama[0]){
                                        $sel_=' selected="selected"';
                                    }
                                ?>
                                    <option value="<?=$myrow_i_reklama[0];?>"<?=$sel_;?> data-cnt="<?=_IN($myrow_i_reklama[2]);?>"><?=$myrow_i_reklama[1];?></option>
                                <?php
                                }
                                ?>
                                
                            </select>
                            <span class="i_reklama_id_info"></span>
                            <div class="i_contr_i_contr_div" style="display: none;">
                                <select data-placeholder="Укажите имя, телефон или email знакомого" name="i_contr_i_contr_id">
                                </select>
                            </div>
                        </div>
                    </div>
                <?php
                
                if (in_array('adress',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-map"></i> Адрес</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="adress" placeholder="660000, г. Красноясрк, ул. Маечака, 38" value="<?php if (isset($myrow_i_contr['adress']) and $myrow_i_contr['adress']!=''){echo _IN($myrow_i_contr['adress']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('html_code',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-align-left"></i> Комментарии</div>
                            <div class="ttable_tbody_tr_td"><textarea name="comments" placeholder="Комментарии"><?php if (isset($myrow_i_contr['html_code']) and $myrow_i_contr['html_code']!=''){echo ($myrow_i_contr['html_code']);} ?></textarea></div>
                        </div>
                    <?php
                }
                if (in_array('link',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-align-left"></i> Сайт</div>
                            <div class="ttable_tbody_tr_td"><input name="link" type="text" placeholder="http://www.site.ru" value="<?php if (isset($myrow_i_contr['link']) and $myrow_i_contr['link']!=''){echo _IN($myrow_i_contr['link']);} ?>"/></div>
                        </div>
                    <?php
                }
                
                ?>
                <div class="ttable_tbody_tr">
                    <div class="ttable_tbody_tr_td"><i class="fa fa-build"></i> Организации</div>
                    <div class="ttable_tbody_tr_td">
                        <div class="i_contr_mini_form_org_add">
                            <input name="i_contr_org_name_auto" type="text" placeholder="Добавить организацию. Введите название организации или ИНН" />
                            <span class="fa fa-plus"></span>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="i_contr_org_current">
                        <?php
                        $sql = "SELECT i_contr_org.id, i_contr_org.name, i_contr_org.inn
                        				FROM i_contr_org, i_contr_i_contr_org 
                        					WHERE i_contr_org.id=i_contr_i_contr_org.id2
                                            AND i_contr_i_contr_org.id1='"._DB($nomer)."'
                        ";
                         
                        $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                        {
                            ?>
                            <div data-id="<?=$myrow['id'];?>">
                                <input type="hidden" name="i_contr_org_id[]" value="<?=$myrow['id'];?>" />
                                <span class="i_contr_org_mini_edit">
                                    <i class="fa fa-building"></i>
                                    <span><?=$myrow['name'];?></span> / 
                                    <span><?=$myrow['inn'];?></span>
                                </span>
                                
                                <span class="i_contr_org_mini_change">
                                    <span class="fa fa-edit" title="Изменить организацию"></span>
                                </span>
                                <span class="i_contr_org_mini_remove">
                                    <span class="fa fa-remove" title="Удалить организацию"></span>
                                </span>
                            </div>
                            <?php
                        }
                        ?>
                        </div>
                    </div>
                </div>
                
            </div>
            <div style="clear: both;"></div>
            <div class="google_api_ontacts_syn">
            
            <?php
            
            //Если есть связь с google drive
                 if ((isset($_SESSION['google_drive_access_token']) && $_SESSION['google_drive_access_token'])) {
                    ?>
                    <label><input type="checkbox" name="google_api_ontacts_syn" <?php if (isset($_SESSION['a_options']['Google API - синхронизировать контакты с Google']) and $_SESSION['a_options']['Google API - синхронизировать контакты с Google']=='1'){?> checked="checked"<?php }else{?>  <?php } ?>/> Синхронизировать контакт с Google</label>
                    <?php
                 }
                 else{//Если нет связи с google drive
                    
                    ?>
                    <a class="google_drive_connect" href="?inc=c_call_client&google_drive_connect"><i class="fa fa-plus"></i> Подключиться к Google Disk</a>
                    <?php
                 }
            ?>
            
                </div>
        <div class="i_contr_mini_form_com"><center><span class="btn_orange i_contr_mini_form_save">Сохранить</span></center></div>
    </div>
    </form> 
    
    
    <?php
}
//************************************************************************************************** 
// ************************************************************
// ФОРМА ОРГАНИЗАЦИИ КОНТРАГЕНТА
//**************************************************************
elseif ($_t=='i_contr_org_form'){
    
    
      // получаем массив всех отображаемых столбцов
    $sql = "SELECT  a_col.id,
                    a_col.col,
                    a_col.col_ru,
                    a_col.tip,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_change='1'
                        AND a_col.a_menu_id='41'
                        AND a_col.id IN (
                                            SELECT a_admin_a_col.id2
                                                FROM a_admin_a_col, a_admin
                                                    WHERE a_admin_a_col.id1=a_admin.id
                                                        AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                                                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                        )
                    ORDER BY a_col.sid
    "; 
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    
    $data_['col']=array();$data_['col_ru']=array();$data_['tip']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
    
    }
    if (!in_array('name',$data_['col']) or !in_array('inn',$data_['col'])){
        echo 'Нет доступа к изменению стролбцов "Название" или "ИНН" у пользователя: '.$_SESSION['admin']['email'];exit;
    }
    
    
    $nomer=_GP('nomer');
    $val_=_GP('val');
    
    if ($nomer!=''){
        $title='Изменение организации №'.$nomer.' <input type="hidden" name="nomer" value="'._IN($nomer).'" />';
        $sql_i_contr_org = "SELECT  i_contr_org.id,
                                    i_contr_org.name,
                                    i_contr_org.inn,
                                    i_contr_org.kpp,
                                    i_contr_org.ogrn,
                                    i_contr_org.bik,
                                    i_contr_org.bank,
                                    i_contr_org.schet,
                                    i_contr_org.kschet,
                                    i_contr_org.u_adress,
                                    i_contr_org.phone,
                                    i_contr_org.tip_director,
                                    i_contr_org.fio_director,
                                    i_contr_org.na_osnovanii
                                
                				FROM i_contr_org 
                					WHERE i_contr_org.id='"._DB($nomer)."'
        	"; 
        
        $mt = microtime(true);
        $res_i_contr_org = mysql_query($sql_i_contr_org);if (!$res_i_contr_org){echo $sql_i_contr_org;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_contr_org;$data_['_sql']['time'][]=$mt;
        $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org);
    
    }else{
        $title='Добавление новой организации <input type="hidden" name="nomer" value="" />';
        if ($val_!=''){$myrow_i_contr_org['name']=$val_;}
    }
    ?>
            <h1><?=$title;?></h1>
            
            <form class="i_contr_org_mini_form">
            <div class="ttable">
                <div class="ttable_tbody_tr mandat">
                    <div class="ttable_tbody_tr_td"><i class="fa fa-building"></i> Название организации*</div>
                    <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_name" placeholder="ООО Фирма" value="<?php if (isset($myrow_i_contr_org['name']) and $myrow_i_contr_org['name']!=''){echo _IN($myrow_i_contr_org['name']);} ?>" /></div>
                </div>
                <div class="ttable_tbody_tr mandat">
                    <div class="ttable_tbody_tr_td"><i class="fa fa-file-o"></i> ИНН*</div>
                    <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_inn" placeholder="24XXXXXXXX" value="<?php if (isset($myrow_i_contr_org['inn']) and $myrow_i_contr_org['inn']!=''){echo _IN($myrow_i_contr_org['inn']);} ?>" /></div>
                </div>
                
                <?php
                if (in_array('kpp',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-file-o"></i> КПП</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_kpp" placeholder="24XXXXXXX" value="<?php if (isset($myrow_i_contr_org['kpp']) and $myrow_i_contr_org['kpp']!=''){echo _IN($myrow_i_contr_org['kpp']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('ogrn',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-file-o"></i> ОГРН</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_ogrn" placeholder="11XXXXXXXXXXX" value="<?php if (isset($myrow_i_contr_org['ogrn']) and $myrow_i_contr_org['ogrn']!=''){echo _IN($myrow_i_contr_org['ogrn']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('u_adress',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-map"></i> Адрес</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_u_adress" placeholder="660000, г. Красноясрк, ул. Маечака, 38" value="<?php if (isset($myrow_i_contr_org['u_adress']) and $myrow_i_contr_org['u_adress']!=''){echo _IN($myrow_i_contr_org['u_adress']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('phone',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-phone"></i> Телефон организации</div>
                            <div class="ttable_tbody_tr_td"><input class="phone" type="text" name="i_contr_org_phone" placeholder="8(XXX)XXX-XX-XX" value="<?php if (isset($myrow_i_contr_org['phone']) and $myrow_i_contr_org['phone']!=''){echo _IN($myrow_i_contr_org['phone']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('tip_director',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-user"></i> Тип руководителя</div>
                            <div class="ttable_tbody_tr_td">
                                <input type="text" name="i_contr_tip_director" placeholder="Директор" value="<?php if (isset($myrow_i_contr_org['tip_director']) and $myrow_i_contr_org['tip_director']!=''){echo _IN($myrow_i_contr_org['tip_director']);} else{echo 'Директор';} ?>" />
                            </div>
                        </div>
                    <?php
                }
                if (in_array('fio_director',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-user"></i> Ф.И.О. руководителя</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_fio_director" placeholder="Иванов Иван Иванович" value="<?php if (isset($myrow_i_contr_org['fio_director']) and $myrow_i_contr_org['fio_director']!=''){echo _IN($myrow_i_contr_org['fio_director']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('na_osnovanii',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-book"></i> На основании</div>
                            <div class="ttable_tbody_tr_td">
                                <input type="text" name="i_contr_na_osnovanii" placeholder="Устава" value="<?php if (isset($myrow_i_contr_org['na_osnovanii']) and $myrow_i_contr_org['na_osnovanii']!=''){echo _IN($myrow_i_contr_org['na_osnovanii']);}else{echo 'Устава';}  ?>" />
                            </div>
                        </div>
                    <?php
                }
                if (in_array('bik',$data_['col']) or in_array('bank',$data_['col']) or in_array('schet',$data_['col']) or in_array('kschet',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><p>Платежные реквизиты:</p></div>
                        </div>
                    <?php
                }
                if (in_array('bank',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-bank"></i> БАНК</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_bank" placeholder="Филиал «Новосибирский» ОАО «АЛЬФА-БАНК»" value="<?php if (isset($myrow_i_contr_org['bank']) and $myrow_i_contr_org['bank']!=''){echo _IN($myrow_i_contr_org['bank']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('bik',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-file-o"></i> БИК</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_bik" placeholder="045004774" value="<?php if (isset($myrow_i_contr_org['bik']) and $myrow_i_contr_org['bik']!=''){echo _IN($myrow_i_contr_org['bik']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('schet',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-file-o"></i> Расчетный счет</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_schet" placeholder="407XXXXXXXXXXXXXXXXX" value="<?php if (isset($myrow_i_contr_org['schet']) and $myrow_i_contr_org['schet']!=''){echo _IN($myrow_i_contr_org['schet']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('kschet',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-file-o"></i> Кор. счет</div>
                            <div class="ttable_tbody_tr_td"><input type="text" name="i_contr_org_kschet" placeholder="301XXXXXXXXXXXXXXXXX" value="<?php if (isset($myrow_i_contr_org['kschet']) and $myrow_i_contr_org['kschet']!=''){echo _IN($myrow_i_contr_org['kschet']);} ?>" /></div>
                        </div>
                    <?php
                }
                if (in_array('html_code',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-align-left"></i> Комментарии</div>
                            <div class="ttable_tbody_tr_td"><textarea name="comments" placeholder="Комментарии"><?php if (isset($myrow['html_code']) and $myrow['html_code']!=''){echo ($myrow['html_code']);} ?></textarea></div>
                        </div>
                    <?php
                }
                ?>
            </div>

            <div class="i_contr_org_mini_form_com">
                <center>
                    <span class="btn_orange i_contr_org_mini_form_save">Добавить организацию</span>
                </center>
            </div>
            </form>
            
    <?php
}
//************************************************************************************************** 
// ************************************************************
// ФОРМА МИНИ КОНТРАГЕНТА - СОХРАНЕНИЕ
//**************************************************************
elseif ($_t=='i_contr_form_save'){
    $data_=array();
    
    $nomer=_GP('nomer');
    $data_['name']=_GP('name');
    $data_['chk_active']=_GP('chk_active');
        if ($data_['chk_active']!='1'){$data_['chk_active']='0';}
    $data_['email']=_GP('email');
    $data_['phone']=preg_replace('/[\D]{1,}/s', '',_GP('phone'));
    

    $data_['i_contr']['name']=_GP('name');
    $data_['i_contr']['phone']=preg_replace('/[\D]{1,}/s', '',_GP('phone'));
    $data_['i_contr']['email']=_GP('email'); 
    
    
    
        if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='1' and $data_['phone']==''){echo '<p>Контгагент не сохранен!</p><p>Не указан телефон контрагента!</p>';exit;}
        //проверяем на отсутствие дубликатов
        //echo '+'.$data_['phone'].'+';exit;
        if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='1' and $data_['phone']!='' and ($data_['phone']-0)!=0 and $nomer==''){
            $sql = "SELECT IF(COUNT(*)>0,(SELECT i_contr.id
            				FROM i_contr 
            					WHERE i_contr.phone='"._DB($data_['phone'])."' LIMIT 1),'')
            				FROM i_contr 
            					WHERE i_contr.phone='"._DB($data_['phone'])."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $i_contr_id=$myrow[0];
            if ($i_contr_id!=''){
                echo '<p>Покупатель с таким телефоном уже есть в базе данных.</p><div><span class="btn_gray i_contr_duble_change" data-id="'._IN($i_contr_id).'">Изменить покупателя №'.$i_contr_id.'</span></div>';exit;
            }
        }
        if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0' and $data_['email']==''){echo '<p>Контгагент не сохранен!</p><p>Не указан email контрагента!</p>';exit;}
        //проверяем на отсутствие дубликатов
        if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='1' and $data_['email']!='' and $nomer==''){
            $sql = "SELECT IF(COUNT(*)>0,(SELECT i_contr.id
            				FROM i_contr 
            					WHERE i_contr.email='"._DB($data_['email'])."' LIMIT 1),'')
            				FROM i_contr 
            					WHERE i_contr.email='"._DB($data_['email'])."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $i_contr_id=$myrow[0];
            if ($i_contr_id!=''){
                echo '<p>Покупатель с таким email уже есть в базе данных.</p><div><span class="btn_gray i_contr_duble_change" data-id="'._IN($i_contr_id).'">Изменить покупателя №'.$i_contr_id.'</span></div>';exit;
            }
        }
    $data_['adress']=_GP('adress');
    $data_['comments']=_GP('comments');
    $data_['link']=_GP('link');
    $data_['i_reklama_id']=_GP('i_reklama_id');
    $data_['i_contr_i_contr_id']=_GP('i_contr_i_contr_id');
    
    $data_['i_contr_org_id']=_GP('i_contr_org_id',array());
    
    //ДОБАВЛЯЕМ НОВОГО КОНТРАГЕНТА
    if ($nomer==''){
        
        if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0'){
            $login_='email';
        }else{
            $login_='phone';
        }
        //Добавляем в базу нового контрагента
        $sql = "INSERT into i_contr (
        				name,
                        password,
                        $login_
        			) VALUES (
        				'"._DB($data_['name'])."',
                        '".md5(rand(10000,99999999))."',
                        '"._DB($data_[$login_])."'
        )";
        
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        	else{$nomer = mysql_insert_id();}
        
    }
    $data_['i_contr']['id']=$nomer;
    
    //Изменяем контрагента
    $sql = "UPDATE i_contr 
    			SET  
    				chk_active='"._DB($data_['chk_active'])."',
    				name='"._DB($data_['name'])."',
                    email='"._DB($data_['email'])."',
                    phone='"._DB($data_['phone'])."',
                    adress='"._DB($data_['adress'])."',
                    html_code='"._DB($data_['comments'])."',
                    link='"._DB($data_['link'])."',
                    i_reklama_id='"._DB($data_['i_reklama_id'])."',
                    i_contr_id='"._DB($data_['i_contr_i_contr_id'])."'
                    
    		
    		WHERE i_contr.id='"._DB($nomer)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    //Удаляем организации
    $sql = "DELETE 
    			FROM i_contr_i_contr_org 
    				WHERE i_contr_i_contr_org.id1='"._DB($nomer)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
        //выбор организаций
        //2018-05-17 toowin86
    $data_['i_contr_org']['id']=array();
    $data_['i_contr_org']['name']=array();
    $data_['i_contr_org']['phone']=array();
    $data_['i_contr_org']['email']=array();
    $data_['active']=0; 
        
    //Добавляем организации
    if (isset($data_['i_contr_org_id']) and is_array($data_['i_contr_org_id']) and count($data_['i_contr_org_id'])>0){
        
        foreach($data_['i_contr_org_id'] as $key => $i_contr_org_id){
            
            $sql = "INSERT into i_contr_i_contr_org (
            				id1,
            				id2
            			) VALUES (
            				'"._DB($nomer)."',
            				'"._DB($i_contr_org_id)."'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql);if (!$res){echo $sql;exit();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        }
        
        
        //выбор организаций
        //2018-05-17 toowin86
        $sql_i_contr_org = "SELECT  i_contr_org.id, 
                                        i_contr_org.name, 
                                        i_contr_org.phone, 
                                        i_contr_org.email
                        FROM i_contr_org, i_contr_i_contr_org
        					WHERE i_contr_i_contr_org.id2=i_contr_org.id
                            AND i_contr_i_contr_org.id1='"._DB($nomer)."'
        ";
         
        $res_i_contr_org = mysql_query($sql_i_contr_org);if (!$res_i_contr_org){echo $sql_i_contr_org;exit();}
        for ($myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j=0; $myrow_i_contr_org==true; $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j++)
        {
            $data_['i_contr_org']['id'][$j]=$myrow_i_contr_org['id'];
            $data_['i_contr_org']['name'][$j]=$myrow_i_contr_org['name'];
            $data_['i_contr_org']['phone'][$j]=$myrow_i_contr_org['phone'];
                if ($data_['i_contr_org']['phone'][$j]!=''){$data_['i_contr_org']['phone'][$j]=conv_('phone_from_db',$myrow_i_contr_org['phone']);}
            $data_['i_contr_org']['email'][$j]=''; if ($myrow_i_contr_org['email']!='') {$data_['i_contr_org']['email'][$j]=$myrow_i_contr_org['email']; }
        }
       if (count($data_['i_contr_org']['id'])>0){$data_['active']=$data_['i_contr_org']['id'][0];}
        
      //end 2018-05-17 toowin86
    
    }
    if ($data_['phone']!=''){$data_['phone']=conv_('phone_from_db',$data_['phone']);}
    $data_['id']=$nomer;
    $data_['name']=$data_['name'];
    
    if (_GP('google_api_ontacts_syn')=='on'){
       google_api_ontacts_syn($data_['id']);
    }
     
    echo json_encode($data_);
}

// ************************************************************
// АВТОЗАПОЛНЕНИЕ ОРГАНИЗАЦИИ
//**************************************************************

if ($_t=='i_contr_org_autocomplete'){

    $data_=array();
    $term=_GP('term');
    $data_['items']=array();
    $sql = "SELECT  i_contr_org.id,
                    i_contr_org.name,
                    i_contr_org.inn,
                    i_contr_org.kpp,
                    i_contr_org.ogrn,
                    
                    i_contr_org.bik,
                    i_contr_org.bank,
                    i_contr_org.schet,
                    i_contr_org.kschet,
                    i_contr_org.phone,
                    i_contr_org.u_adress,
                    i_contr_org.tip_director,
                    i_contr_org.fio_director,
                    i_contr_org.na_osnovanii
                    
    				FROM i_contr_org
    					WHERE (i_contr_org.id='"._DB($term)."' 
                        OR i_contr_org.name LIKE '%"._DB($term)."%'
                        OR i_contr_org.inn LIKE '"._DB($term)."%')
                     
                        ORDER BY i_contr_org.name
                        LIMIT 8
    ";
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['items'][$i]['id']=$myrow[0];
        $data_['items'][$i]['value']=($myrow[1]);
        $data_['items'][$i]['i']=$myrow[2];
        $data_['items'][$i]['k']=$myrow[3];
        $data_['items'][$i]['o']=$myrow[4];
        $data_['items'][$i]['bi']=$myrow[5];
        $data_['items'][$i]['ba']=$myrow[6];
        $data_['items'][$i]['sc']=$myrow[7];
        $data_['items'][$i]['ks']=$myrow[8];
        $data_['items'][$i]['p']=$myrow[9];
        $data_['items'][$i]['u']=$myrow[10];
        $data_['items'][$i]['td']=$myrow[11];
        $data_['items'][$i]['fd']=$myrow[12];
        $data_['items'][$i]['no']=$myrow[13];
        
    }
    echo json_encode($data_);
}

// ************************************************************
// СОХРАНЕНИЕ ОРГАНИЗАЦИИ
//**************************************************************

if ($_t=='i_contr_org_form_save'){

    $data_=array();
    $data_['i_contr_org_name']=_GP('i_contr_org_name');
        if ($data_['i_contr_org_name']==''){echo '<p>Название организации не должно быть пустым!</p>';exit;}
    $data_['i_contr_org_phone']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_phone'));
    $data_['i_contr_org_inn']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_inn'));
        if ($data_['i_contr_org_inn']==''){echo '<p>ИНН не должен быть пустым!</p>';exit;}
    $data_['i_contr_org_kpp']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_kpp'));
    $data_['i_contr_org_ogrn']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_ogrn'));
    $data_['i_contr_org_bik']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_bik'));
    $data_['i_contr_org_schet']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_schet'));
    $data_['i_contr_org_kschet']=preg_replace('/[\D]{1,}/s', '',_GP('i_contr_org_kschet'));
    $data_['i_contr_org_bank']=_GP('i_contr_org_bank');
    $data_['i_contr_org_fio_director']=_GP('i_contr_org_fio_director');
    $data_['i_contr_org_u_adress']=_GP('i_contr_org_u_adress');
    $data_['i_contr_tip_director']=_GP('i_contr_tip_director');
    $data_['i_contr_na_osnovanii']=_GP('i_contr_na_osnovanii');
    
    $sql = "SELECT IF(COUNT(*)>0,i_contr_org.id,'')
    				FROM i_contr_org 
    					WHERE i_contr_org.inn='"._DB($data_['i_contr_org_inn'])."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $i_contr_org_id=$myrow[0];
    if ($i_contr_org_id==''){
        //Добавляем
        $sql = "INSERT into i_contr_org (
        				name,
        				inn
        			) VALUES (
        				'"._DB($data_['i_contr_org_name'])."',
        				'"._DB($data_['i_contr_org_inn'])."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $i_contr_org_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    }
    $sql = "
    		UPDATE i_contr_org 
    			SET  
    				name='"._DB($data_['i_contr_org_name'])."',
    				inn='"._DB($data_['i_contr_org_inn'])."',
    				kpp='"._DB($data_['i_contr_org_kpp'])."',
    				ogrn='"._DB($data_['i_contr_org_ogrn'])."',
    				bik='"._DB($data_['i_contr_org_bik'])."',
    				bank='"._DB($data_['i_contr_org_bank'])."',
    				schet='"._DB($data_['i_contr_org_schet'])."',
    				kschet='"._DB($data_['i_contr_org_kschet'])."',
    				phone='"._DB($data_['i_contr_org_phone'])."',
    				u_adress='"._DB($data_['i_contr_org_u_adress'])."',
    				fio_director='"._DB($data_['i_contr_org_fio_director'])."',
    				tip_director='"._DB($data_['i_contr_tip_director'])."',
    				na_osnovanii='"._DB($data_['i_contr_na_osnovanii'])."'
    		
    		WHERE id='"._DB($i_contr_org_id)."'
    ";
    if(!mysql_query($sql)){echo $sql;}
    $data_['id']=$i_contr_org_id;
    
    echo json_encode($data_);
}

// ************************************************************
// ФОРМА ТОВАРА/УСЛУГИ
//**************************************************************

if ($_t=='m_zakaz__s_cat_add'){

    $data_=array();
    $nomer=_GP('nomer');
    $val=_GP('val');
    $s_struktura_id_cur=_GP('s_struktura_id');
    
    $tip=_GP('tip');
        $tip1='';$tip2=' selected="selected"';if ($tip=='1'){$tip1=' selected="selected"';$tip2='';}
    
        $title='Добавление товара/услуги <input type="hidden" name="nomer" value="" />';
        if ($nomer!=''){$title='Изменение товара/услуги №'.$nomer.' <input type="hidden" name="nomer" value="'._IN($nomer).'" /> <a target="_blank" href="?inc=s_cat&nomer='._IN($nomer).'"><i class="fa fa-edit"></i></a>';}
        
      // получаем массив всех отображаемых столбцов
    $sql = "SELECT  a_col.id,
                    a_col.col,
                    a_col.col_ru,
                    a_col.tip,
                    a_col.chk_change
                    
    				FROM a_col
    					WHERE a_col.chk_active='1'
                        AND a_col.chk_change='1'
                        AND a_col.a_menu_id='7'
                        AND a_col.id IN (
                                            SELECT a_admin_a_col.id2
                                                FROM a_admin_a_col, a_admin
                                                    WHERE a_admin_a_col.id1=a_admin.id
                                                        AND a_admin.email='"._DB($_SESSION['admin']['email'])."'
                                                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                                        )
                    ORDER BY a_col.sid
    "; 
    $res = mysql_query($sql) or die(mysql_error());
    
    $data_['col']=array();$data_['col_ru']=array();$data_['tip']=array();
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['col'][$i]=$myrow[1];
        $data_['col_ru'][$i]=$myrow[2];
        $data_['tip'][$i]=$myrow[3];
    }
    //print_rf($data_['col']);
    if (!in_array('name',$data_['col']) or !in_array('price',$data_['col'])){
        echo 'Отключен доступ у пользователя "'.$_SESSION['admin']['email'].'" к параметру "Название" или "Цена". Дальнейшее редактирование не возможно!';exit;
    }
    
    $myrow_s_cat=array();
    if ($nomer!=''){
        $sql_s_cat = "SELECT    s_cat.id,
                                s_cat.name,
                                s_cat.price,
                                s_cat.tip,
                                (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_cat_s_struktura.id2 SEPARATOR ','),'') FROM s_cat_s_struktura WHERE s_cat_s_struktura.id1=s_cat.id) AS s_struktura_id
        				FROM s_cat 
        					WHERE s_cat.id='"._DB($nomer)."' 
       					
        	"; 
        
        $mt = microtime(true);
        $res_s_cat = mysql_query($sql_s_cat);if (!$res_s_cat){echo $sql_s_cat;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_cat;$data_['_sql']['time'][]=$mt;
        $myrow_s_cat = mysql_fetch_array($res_s_cat);
        
        $tip1='';$tip2=' selected="selected"';if ($myrow_s_cat['tip']=='Товар'){$tip1=' selected="selected"';$tip2='';}
        
    }
    ?>
    <form name="m_zakaz_s_cat_add_form">
        <h1><?=$title;?></h1>
        <div class="ttable" style="width: 100%;">
            <?php
                if (in_array('chk_active',$data_['col'])){
                    ?>
                        <div class="ttable_tbody_tr">
                            <div class="ttable_tbody_tr_td"><i class="fa fa-toggle-on"></i> Активность</div>
                            <div class="ttable_tbody_tr_td"><label for="s_cat_chk_active"><input type="checkbox" id="s_cat_chk_active" name="chk_active" value="1" <?php if (isset($myrow_s_cat['chk_active']) and $myrow_s_cat['chk_active']=='0'){}else{echo ' checked="checked"';} ?> /></label>
                            </div>
                        </div>
                    <?php
                }
                if (in_array('name',$data_['col'])){
                ?>
                    <div class="ttable_tbody_tr mandat">
                        <div class="ttable_tbody_tr_td"><i class="fa fa-sticky-note-o"></i> Название*</div>
                        <div class="ttable_tbody_tr_td"><input type="text" name="name" placeholder="Название" value="<?php if (isset($myrow_s_cat['name']) and $myrow_s_cat['name']!=''){echo _IN($myrow_s_cat['name']);} else{echo _IN($val);} ?>" />
                        </div>
                    </div>
                <?php
                }
                if (in_array('price',$data_['col'])){
                ?>
                    <div class="ttable_tbody_tr mandat">
                        <div class="ttable_tbody_tr_td"><i class="fa fa-calculator"></i> Цена*</div>
                        <div class="ttable_tbody_tr_td"><input type="text" name="price" placeholder="Цена" value="<?php if (isset($myrow_s_cat['price']) and $myrow_s_cat['price']!=''){echo _IN($myrow_s_cat['price']);} ?>" />
                        </div>
                    </div>
                <?php
                }
                if (in_array('tip',$data_['col'])){
                ?>
                    <div class="ttable_tbody_tr">
                        <div class="ttable_tbody_tr_td"><i class="fa fa-cube"></i> Тип*</div>
                        <div class="ttable_tbody_tr_td">
                            <select name="tip">
                                <option value="Товар"<?=$tip1;?>>Товар</option>
                                <option value="Услуга"<?=$tip2;?>>Услуга</option>
                            </select>
                        </div>
                    </div>
                <?php
                }
                if (in_array('s_struktura_id',$data_['col'])){
                ?>
                    <div class="ttable_tbody_tr">
                        <div class="ttable_tbody_tr_td"><i class="fa fa-sitemap"></i> В структуре</div>
                        <div class="ttable_tbody_tr_td">
                            <select name="s_struktura_id" multiple>
                                <option></option>
                                <?php
                                    $str_arr=array();
                                    if (isset($myrow_s_cat['s_struktura_id']) and $myrow_s_cat['s_struktura_id']!=''){
                                        if (strstr($myrow_s_cat['s_struktura_id'],',')==true){
                                            $str_arr=explode(',',$myrow_s_cat['s_struktura_id']);
                                        }else{
                                            $str_arr[0]=$myrow_s_cat['s_struktura_id'];
                                        }
                                    }
                                    
                                    $sql = "SELECT s_struktura.id, s_struktura.name
                                    				FROM s_struktura 
                                    					WHERE s_struktura.tip='Каталог'
                                                        ORDER BY s_struktura.sid
                                    ";
                                    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                                    {
                                        $sel_='';if (in_array($myrow[0],$str_arr)){$sel_=' selected="selected"';}
                                        if($nomer==''){
                                            if($myrow[0]==$s_struktura_id_cur){
                                                $sel_=' selected="selected"';
                                            }
                                        }
                                        ?>
                                        <option value="<?=$myrow[0];?>"<?=$sel_;?>><?=$myrow[1];?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                <?php
                }
                ?>
        </div>
        <div><center><span class="btn_orange m_zakaz_s_cat_add_form_save">Сохранить</span></center></div>
    </form>
    <?php
}

// ************************************************************
// СОХРАНЕНИЕ ТОВАРА/УСЛУГИ
//**************************************************************

if ($_t=='m_zakaz__s_cat_save'){
    $data_=array();
    $chk_active=_GP('chk_active');
    $data_['name']=_GP('name');
    $data_['price']=_GP('price');
    $data_['tip']=_GP('tip');
    $s_struktura_id=_GP('s_struktura_id');
    $nomer=_GP('nomer');
    
    //Получаем url
    $url=make_url($data_['name']);
    
    $nomer_sql='';
    if ($nomer==''){//Добаляем новую запись в каталог
        $sql = "INSERT into s_cat (
        				name
        			) VALUES (
        				'"._DB($data_['name'])."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql);
        	if (!$res){echo $sql;exit();}
        	else{$nomer = mysql_insert_id();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
        //Обновляем информацию о том кто создал товар в каталоге
        $inc='s_cat';
        $names=get_column_names_with_show($inc);
        if (in_array('a_admin_id_create',$names)){
            $sql_upp = "
            		UPDATE `"._DB($inc)."` 
            			SET  
            				`"._DB($inc)."`.a_admin_id_create='"._DB($a_admin_id_cur)."'
            		
            		WHERE `"._DB($inc)."`.id='"._DB($nomer)."'
            ";
            $res_upp = mysql_query($sql_upp) or die(mysql_error().'<br />'.$sql_upp);
        }
    
    
        
    }else{
        $nomer_sql='AND s_cat.id!="'._DB($nomer).'"';
    }
    
    
    $sql = "SELECT IF(COUNT(*)>0,s_cat.id,'')
    				FROM s_cat 
    					WHERE s_cat.url='"._DB($url)."' 
    					$nomer_sql
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $i=1;
    while($myrow[0]!=''){
        $url=make_url($data_['name'],$i);
        $sql = "SELECT IF(COUNT(*)>0,s_cat.id,'')
        				FROM s_cat 
        					WHERE s_cat.url='"._DB($url)."' 
        					$nomer_sql
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql);if (!$res){echo $sql;exit();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
    }
    
    
    //Обновляем запись в каталоге
    $sql = "
    		UPDATE s_cat 
    			SET  
    				name='"._DB($data_['name'])."',
    				url='"._DB($url)."',
    				tip='"._DB($data_['tip'])."',
                    chk_active='"._DB($chk_active)."',
                    price='"._DB($data_['price'])."',
                    data_change='".date('Y-m-d H:i:s')."'
    		
    		WHERE id='"._DB($nomer)."'
    ";
    if(!mysql_query($sql)){echo $sql;}
    
    $sql = "DELETE 
    			FROM s_cat_s_struktura 
    				WHERE s_cat_s_struktura.id1='"._DB($nomer)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql);
    	if (!$res){echo $sql;exit();}
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    //Добовляем связь со структурой
    if ($s_struktura_id!=''){
        if(!is_array($s_struktura_id)){$n=$s_struktura_id;unset($s_struktura_id);$s_struktura_id[0]=$n;}
    
        foreach($s_struktura_id as $key => $id2){
            
            $sql = "INSERT into s_cat_s_struktura (
            				id1,
            				id2
            			) VALUES (
            				'"._DB($nomer)."',
            				'"._DB($id2)."'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql);
            	if (!$res){echo $sql;exit();}
            	else{$new_id = mysql_insert_id();}
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        }
    }
    
    if (is_array($nomer)){
        $nomer_arr=$nomer;
    }else{
        $nomer_arr[0]=$nomer;
    }
    
    $arr_=change_row('s_cat','',$nomer_arr);
    
    $data_['id']=$nomer;
    echo json_encode($data_);
}
//************************************************************************************************** 
if ($_t=='send_mess'){
    $data_=array();
    $id=_GP('id'); 
        if ($id==''){echo 'Не определен id заказа';exit;}
    $text=_GP('text');
    $f=_GP('f');//файлы $f['i'], $f['t']
    

    $sql = "SELECT IF(COUNT(*)>0,m_a_admin_i_contr.id,'')
    				FROM m_a_admin_i_contr
    					WHERE m_a_admin_i_contr.id1='"._DB($a_admin_id_cur)."'
                        
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_a_admin_i_contr_id=$myrow[0];
    if ($m_a_admin_i_contr_id==''){
        $sql = "INSERT into m_a_admin_i_contr (
        				id1
        			) VALUES (
        				'"._DB($a_admin_id_cur)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $m_a_admin_i_contr_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    }
    $sql = "INSERT into m_dialog (
    				pid,
    				chk_active,
                    m_a_admin_i_contr_id1,
                    row_id,
                    message,
                    chk_in_out,
                    a_menu_id
    			) VALUES (
    				'0',
    				'1',
                    '"._DB($m_a_admin_i_contr_id)."',
                    '"._DB($id)."',
                    '"._DB($text)."',
                    '1',
                    '16'
    )";
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $data_['nomer'] = mysql_insert_id();
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    //ОБРАБОТКА ФАЙЛОВ
    
    if (!file_exists('../../i/m_dialog/')) {@mkdir('../../i/m_dialog/',0777);}
    if (!file_exists('../../i/m_dialog/original/')) {@mkdir('../../i/m_dialog/original/',0777);}
    if (!file_exists('../../i/m_dialog/small/')) {@mkdir('../../i/m_dialog/small/',0777);}
    if (isset($f['i']) and is_array($f['i']) and count($f['i'])>0){
        foreach($f['i'] as $key => $img_){
            $text=$f['t'][$key];
            $ext=preg_replace("/.*?\./", '', $img_);
            $file_name=ru_us($text).'.'.$ext;
            $j=1;
            while(file_exists('../../i/m_dialog/original/'.$file_name)){
                $file_name=ru_us($text).'_'.$j.'.'.$ext;
                $j++;
            }
            //копируем файл
            if (!copy('../../i/m_dialog/temp/'.$img_,'../../i/m_dialog/original/'.$file_name)){
                echo 'Ошибка копирования файла: <br />'.'../../i/m_dialog/temp/'.$img_.'<br />'.'../../i/m_dialog/original/'.$file_name;exit;
            }
            if ($ext=='jpg' or $ext=='jpeg' or $ext=='png' or $ext=='gif'){
                smart_resize_image('../../i/m_dialog/original/'.$file_name,'../../i/m_dialog/small/'.$file_name, $_SESSION['a_options']['Ширина миниатюры'], $_SESSION['a_options']['Высота миниатюры']);
            }
            
            @unlink('../../i/m_dialog/temp/'.$img_);
            
            $sql = "INSERT into a_photo (
            				row_id,
            				img,
                            comments,
                            a_menu_id,
                            tip
            			) VALUES (
            				'"._DB($data_['nomer'])."',
                            '"._DB($file_name)."',
                            '"._DB($text)."',
                            '18',
                            'Основное'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            
        }
    }
    
    echo json_encode($data_);
}

// ************************************************************
// УДАЛЕНИЕ СООБЩЕНИЯ
//**************************************************************

if ($_t=='mess_del'){
    $data_=array();
    $data_['nomer']=_GP('id');
    
    $sql = "SELECT m_a_admin_i_contr.id1
    				FROM m_dialog, m_a_admin_i_contr
    					WHERE m_dialog.id='"._DB($data_['nomer'])."'
                        AND m_a_admin_i_contr.id=m_dialog.m_a_admin_i_contr_id1
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_a_admin_i_contr_id=$myrow[0];
        
    if (!isset($a_admin_id_cur)){echo 'Ошибка авторизации. $a_admin_id_cur не определен!';exit;}

    if ($m_a_admin_i_contr_id!='' and $m_a_admin_i_contr_id==$a_admin_id_cur){
        $sql = "
        		UPDATE m_dialog 
        			SET  
        				chk_active='0'
        		
        		WHERE m_dialog.id='"._DB($data_['nomer'])."'
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    }else{
        echo 'Нет доступа!';exit;
    }
    
    echo json_encode($data_);
}
// ************************************************************
// ПОИСК ПО СООБЩЕНИЯМ
//**************************************************************

if ($_t=='m_zakaz__mess_find'){
    $data_=array();
    $data_['nomer']=_GP('id');
    
    $kol_load=20;
    $WHERE="";
    $ORDER="";
        if ($ORDER==''){$ORDER='id DESC';}
    $LIMIT="";
    
    $limit=_GP('limit');
        if ($limit!=''){
            $LIMIT=$limit.', '.$kol_load;
        }

    
    
    if ($ORDER!=''){$ORDER=' ORDER BY '.$ORDER;}else{$ORDER=' ORDER BY id';}
    if ($LIMIT!=''){$LIMIT=' LIMIT '.$LIMIT;}else{$LIMIT=' LIMIT '.$kol_load;}
    
    
    $sql = "SELECT COUNT(*)
				FROM m_dialog
					WHERE m_dialog.row_id='"._DB($data_['nomer'])."'
                    AND m_dialog.a_menu_id='16'
                    AND m_dialog.chk_active='1'
                    AND m_dialog.chk_in_out='1'
                    $WHERE
                    
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['cnt_']=$myrow[0];
    
    $sql = "SELECT IF(COUNT(*)>0,a_admin.id,'')
    				FROM a_admin 
    					WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."'
                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['a_admin_id']=$myrow[0];
        if ($data_['a_admin_id']==''){echo 'Ошибка авторизации';exit;}
    
    $sql = "SELECT      m_dialog.id,
                        m_dialog.pid,
                        (SELECT m_a_admin_i_contr.id1 FROM m_a_admin_i_contr WHERE m_a_admin_i_contr.id=m_dialog.m_a_admin_i_contr_id1) AS a_admin_id,
                        (SELECT m_a_admin_i_contr.id2 FROM m_a_admin_i_contr WHERE m_a_admin_i_contr.id=m_dialog.m_a_admin_i_contr_id1) AS i_contr_id,
                        m_dialog.message,
                        m_dialog.data_create,
                        m_dialog.data_send_sms,
                        m_dialog.data_send_email
                        
        				FROM m_dialog 
        					WHERE m_dialog.row_id='"._DB($data_['nomer'])."'
                            AND m_dialog.a_menu_id='16'
                            AND m_dialog.chk_active='1'
                            AND m_dialog.chk_in_out='1'
                            $WHERE
                            $ORDER
                            $LIMIT
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['m']=array();
        $data_['m']['i']=array();
        $data_['m']['p']=array();
        $data_['m']['m']=array();
        $data_['m']['d']=array();
        $data_['m']['f']=array();
        
        $data_['m']['ai']=array();
        $data_['m']['an']=array();
        $data_['m']['ae']=array();
        $data_['m']['ap']=array();
        
        $data_['m']['ii']=array();
        $data_['m']['in_']=array();
        $data_['m']['ie']=array();
        $data_['m']['ip']=array();
        $data_['m']['ds']=array();
        $data_['m']['de']=array();
    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['m']['i'][$i]=$myrow['id'];
        $data_['m']['p'][$i]=$myrow['pid'];
        $data_['m']['m'][$i]=$myrow['message'];
        $data_['m']['d'][$i]=date('d.m.Y H:i:s',strtotime($myrow['data_create']));

        $data_['m']['ds'][$i]=''; if ($myrow['data_send_sms']!='0000-00-00 00:00:00' and $myrow['data_send_sms']!=''){$data_['m']['ds'][$i]=date('d.m.Y H:i:s',strtotime($myrow['data_send_sms']));}
        $data_['m']['de'][$i]=''; if ($myrow['data_send_email']!='0000-00-00 00:00:00' and $myrow['data_send_email']!=''){$data_['m']['de'][$i]=date('d.m.Y H:i:s',strtotime($myrow['data_send_email']));}
        
        if ($myrow['a_admin_id']>0){
            $sql_a_admin = "SELECT  a_admin.id,
                                    a_admin.name,
                                    a_admin.email,
                                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.row_id=a_admin.id AND a_photo.a_menu_id='4' AND a_photo.tip='Основное' ORDER BY a_photo.sid LIMIT 1) AS img
            				FROM a_admin 
            					WHERE a_admin.id='"._DB($myrow['a_admin_id'])."'
            	"; 
            
            $mt = microtime(true);
            $res_a_admin = mysql_query($sql_a_admin) or die(mysql_error().'<br/>'.$sql_a_admin);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_a_admin;$data_['_sql']['time'][]=$mt;
            $myrow_a_admin = mysql_fetch_array($res_a_admin);
            
            $data_['m']['ai'][$i]=$myrow_a_admin['id'];
            $data_['m']['an'][$i]=$myrow_a_admin['name'];
            $data_['m']['ae'][$i]=$myrow_a_admin['email'];
            $data_['m']['ap'][$i]=$myrow_a_admin['img'];
        }
        elseif($myrow['i_contr_id']>0){
            
            $login='email';if ($_SESSION['a_options']['Регистрация: email-0/sms-1']==1){$login='phone';}
            
            $sql_i_contr = "SELECT  i_contr.id,
                                    i_contr.name,
                                    i_contr.$login,
                                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.row_id=i_contr.id AND a_photo.a_menu_id='25' AND a_photo.tip='Основное' ORDER BY a_photo.sid LIMIT 1) AS img
            				FROM i_contr 
            					WHERE i_contr.id='"._DB($myrow['i_contr_id'])."'
            	"; 
            
            $mt = microtime(true);
            $res_i_contr = mysql_query($sql_i_contr) or die(mysql_error().'<br/>'.$sql_i_contr);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_i_contr;$data_['_sql']['time'][]=$mt;
            $myrow_i_contr = mysql_fetch_array($res_i_contr);
            
            $data_['m']['ai'][$i]=$myrow_i_contr['id'];
            $data_['m']['an'][$i]=$myrow_i_contr['name'];
            
            $data_['m']['ae'][$i]=$myrow_i_contr[$login];
            if ($login=='phone'){
                if ($myrow_i_contr[$login]!=''){
                    $data_['m']['ae'][$i]=conv_('phone_from_db',$myrow_i_contr[$login]);
                }
            }
            
            $data_['m']['ap'][$i]=$myrow_i_contr['img'];
        }
        else{
            echo 'Не определен отправитель: <br />';print_rf($myrow);exit;
        }
        
        //ФАЙЛЫ
        $data_['m']['f'][$i]['i']=array();
        $data_['m']['f'][$i]['n']=array();
        $data_['m']['f'][$i]['t']=array();
        $sql_file = "SELECT a_photo.id,
                            a_photo.img,
                            a_photo.comments
                            
            				FROM a_photo 
            					WHERE a_photo.row_id='"._DB($myrow['id'])."' 
                                AND a_photo.a_menu_id='18'
                                AND a_photo.tip='Основное'
            						ORDER BY a_photo.sid
         ";
        $mt = microtime(true);
        $res_file = mysql_query($sql_file) or die(mysql_error().'<br/>'.$sql_file);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_file;$data_['_sql']['time'][]=$mt;
        $j=0;
        for ($myrow_file = mysql_fetch_array($res_file); $myrow_file==true; $myrow_file = mysql_fetch_array($res_file))
        {
            $data_['m']['f'][$i]['i'][$j]=$myrow_file['id'];
            $data_['m']['f'][$i]['n'][$j]=$myrow_file['img'];
            $data_['m']['f'][$i]['t'][$j]=$myrow_file['comments'];
            $j++;
        }
        
        $i++;
    }
    echo json_encode($data_);
}
// ************************************************************
// ВЫВОДИМ ФОРМУ НАЗНАЧЕНИЯ РАБОТНИКОВ
//**************************************************************

if ($_t=='s_cat_set_worker'){
    
    $nom=_GP('nom');
    $sql = "SELECT  a_admin.id,
                    a_admin.name AS a_admin_name,
                    a_admin_i_post.id AS a_admin_i_post_id,
                    i_post.name AS i_post_name,
                    (SELECT i_obj.target FROM a_admin_i_post_i_zp, i_zp, i_obj
                         WHERE a_admin_i_post.id=a_admin_i_post_i_zp.id1
                            AND a_admin_i_post_i_zp.id2=i_zp.id
                            AND i_zp.i_obj_id=i_obj.id
                            AND i_post.obj='Работа' LIMIT 1) AS target,
                    a_admin.chk_active
                    
        				FROM a_admin, a_admin_i_post, i_post
        					WHERE a_admin.id=a_admin_i_post.id1
                            AND i_post.id=a_admin_i_post.id2
                            AND a_admin_i_post.data_end IS NULL
                            AND i_post.obj='Работа'
                            
     ";
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $a_admin_arr=array();
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $a_admin_arr[$myrow['id']]['a_admin_name']=$myrow['a_admin_name'];
        $a_admin_arr[$myrow['id']]['a_admin_chk']=$myrow['chk_active'];
        $a_admin_arr[$myrow['id']]['post'][$myrow['a_admin_i_post_id']]['name']=$myrow['i_post_name'];
        $a_admin_arr[$myrow['id']]['post'][$myrow['a_admin_i_post_id']]['target'][]=$myrow['target'];
        
        
    }
    //print_rf($a_admin_arr);
    
    ?>
    <div class="s_cat_set_worker_div" data-nom="<?=$nom;?>">
    <p>Укажите работника:</p>
    <div class="s_cat_set_worker_select_div">
        <select class="s_cat_set_worker_select" data-placeholder="Укажите работника">
            <option></option>
            <?php
            $txt='';
            foreach($a_admin_arr as $a_admin_id => $a_admin_arr2){
                $dis_='';if ($a_admin_arr[$a_admin_id]['a_admin_chk']=='0'){$dis_=' disabled="disabled"';}
                $txt.='<ul style="display: none;" data-id="'.$a_admin_id.'">';$i=0;
                foreach($a_admin_arr2['post'] as $a_admin_i_post_id =>$i_post_arr){
                    $auto=1;
                    if (in_array('Фиксированная сумма с работы: вручную',$i_post_arr['target'])){
                        $auto=0;
                    }
                    $txt.='<li data-id="'.$a_admin_i_post_id.'" data-auto="'.$auto.'">'._IN($i_post_arr['name']).'</li>';
                    $i++;
                }
                $txt.='</ul>';
                ?>
                <option value="<?=$a_admin_id;?>"<?=$dis_;?>><?=$a_admin_arr2['a_admin_name'];?></option>
                <?php
            }
            ?>
        </select>
    </div>
    <div class="s_cat_set_worker_res">
        <?=$txt;?>
    </div>
    <div style="clear: both;"></div>
    <div class="s_cat_set_worker_res_auto">
    </div>
    <div style="clear: both;"></div>
    <div class="s_cat_set_worker_save_div">
        <center>
            <span class="btn_orange s_cat_set_worker_save">Сохранить</span>
        </center>
    </div>
    </div>
    <?php
}
//************************************************************************************************** 
//автозаполнение городов
if ($_t=='autocomplete_city'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    $sql = "SELECT i_city.id, i_city.name
        				FROM i_city 
        					WHERE i_city.name LIKE '"._DB($term)."%'
                            LIMIT 20
     ";
     
    $data_['items']=array();
    $data_['items'][0]['name']='[пусто]';
    $data_['items'][0]['text']='[пусто]';
    $data_['items'][0]['pid']='0';
    $data_['items'][0]['id']='-1';
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;$i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['items'][$i]['name']=$myrow['name'];
        $data_['items'][$i]['text']=$myrow['name'];
        $data_['items'][$i]['id']=$myrow['id'];
        $i++;
    }

    echo json_encode($data_);
}
//************************************************************************************************** 
if ($_t=='upload'){
    
    $inc='m_dialog';
    $fileName='';
    $targetDir = '../../i/'.$inc.'/temp';
    
    
    // проверяем на пустоту
    if (!isset($_SESSION['a_admin_dialog'][$inc]['photo_temp']) or $_SESSION['a_admin_dialog'][$inc]['photo_temp']==''){
            
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        
        $name_file=str_replace('.'.$ext,'',$fileName);
        $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
        
        $_SESSION['a_admin_dialog'][$inc]['photo_temp']=$fileName;
        $_SESSION['a_admin_dialog'][$inc]['name_file']=$name_file;
    }else{
        $fileName=$_SESSION['a_admin_dialog'][$inc]['photo_temp'];
        $name_file=$_SESSION['a_admin_dialog'][$inc]['name_file'];
    }
    
    
        
    @set_time_limit(5 * 60);
    if (!file_exists('../../i')) {@mkdir('../../i',0777);}
    if (!file_exists('../../i/'.$inc)) {@mkdir('../../i/'.$inc,0777);}
    
    
    if (!file_exists($targetDir)) {@mkdir($targetDir,0777);}
    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 5 * 3600; // Temp file age in seconds
    $filePath = $targetDir . '/' . $fileName;
    

    
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    
    if ($cleanupTargetDir) { // Удаление старых файлов
    	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {}
    	while (($file = readdir($dir)) !== false) {
    		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
    		if ($tmpfilePath == "{$filePath}.part") {
    			continue;
    		}
    		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
    			@unlink($tmpfilePath);
    		}
    	}
    	closedir($dir);
    }
    if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {}
    
    if (!empty($_FILES)) {
    	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {}
    	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {}
    } else {if (!$in = @fopen("php://input", "rb")) {}}
    
    while ($buff = fread($in, 4096)) {fwrite($out, $buff);}
    
    @fclose($out);
    @fclose($in);
    
    if (!$chunks || $chunk == $chunks - 1) {rename("{$filePath}.part", $filePath);
    unset($_SESSION['a_admin_dialog'][$inc]['name_file'],$_SESSION['a_admin_dialog'][$inc]['photo_temp']);}
    echo $fileName.'@@'.$name_file;
}
//************************************************************************************************** 
if ($_t=='m_zakaz_upload_docs'){
    
    $inc='m_zakaz';
    $fileName='';
    $targetDir = '../../i/'.$inc.'/temp';
    
    
    // проверяем на пустоту
    if (!isset($_SESSION['a_admin_docs'][$inc]['photo_temp']) or $_SESSION['a_admin_docs'][$inc]['photo_temp']==''){
            
        if (isset($_REQUEST["name"])) {$fileName = $_REQUEST["name"];} 
        elseif (!empty($_FILES)) {$fileName = $_FILES["file"]["name"];} 
        else {$fileName = uniqid("file_");}
        
        $ext=preg_replace("/.*?\./", '', $fileName);
        
        $name_file=str_replace('.'.$ext,'',$fileName);
        $fileName='rand_'.date('Y_m_d__H_i_s').'__'.rand(1000,9999).'.'.$ext;
        
        $_SESSION['a_admin_docs'][$inc]['photo_temp']=$fileName;
        $_SESSION['a_admin_docs'][$inc]['name_file']=$name_file;
    }else{
        $fileName=$_SESSION['a_admin_docs'][$inc]['photo_temp'];
        $name_file=$_SESSION['a_admin_docs'][$inc]['name_file'];
    }
    
    
        
    @set_time_limit(5 * 60);
    if (!file_exists('../../i')) {@mkdir('../../i',0777);}
    if (!file_exists('../../i/'.$inc)) {@mkdir('../../i/'.$inc,0777);}
    
    
    if (!file_exists($targetDir)) {@mkdir($targetDir,0777);}
    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 5 * 3600; // Temp file age in seconds
    $filePath = $targetDir . '/' . $fileName;
    

    
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    
    if ($cleanupTargetDir) { // Удаление старых файлов
    	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {}
    	while (($file = readdir($dir)) !== false) {
    		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
    		if ($tmpfilePath == "{$filePath}.part") {
    			continue;
    		}
    		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
    			@unlink($tmpfilePath);
    		}
    	}
    	closedir($dir);
    }
    if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {}
    
    if (!empty($_FILES)) {
    	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {}
    	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {}
    } else {if (!$in = @fopen("php://input", "rb")) {}}
    
    while ($buff = fread($in, 4096)) {fwrite($out, $buff);}
    
    @fclose($out);
    @fclose($in);
    
    if (!$chunks || $chunk == $chunks - 1) {rename("{$filePath}.part", $filePath);
    unset($_SESSION['a_admin_docs'][$inc]['name_file'],$_SESSION['a_admin_docs'][$inc]['photo_temp']);}
    echo $fileName.'@@'.$name_file;
}

//********************************************************
// Неисправности - новые
if ($_t=='r_neispravnosti_autocomplete'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('term');
    $r_tip_id=_GP('_r_tip_id');
    $SQL_WHERE="";
        if ($r_tip_id!=''){$SQL_WHERE="AND r_neispravnosti.r_tip_oborud_id='"._DB($r_tip_id)."'";}
    
    $data_['items']=array();
    $data_['items'][0]['name']='Добавить неисправность';
    $data_['items'][0]['text']='Добавить неисправность';
    $data_['items'][0]['id']='-1';
    
    $sql_connect = "SELECT  DISTINCT
                            r_neispravnosti.id,
                            r_neispravnosti.name,
                            (SELECT COUNT(r_service_r_neispravnosti.id2) FROM r_service_r_neispravnosti WHERE r_service_r_neispravnosti.id2=r_neispravnosti.id) AS cnt_
    				FROM r_neispravnosti
    					WHERE r_neispravnosti.name LIKE '%"._DB($term)."%'
                        $SQL_WHERE
                        
                       
                        GROUP BY r_neispravnosti.id
                        
                        ORDER BY cnt_ DESC, r_neispravnosti.name
                       LIMIT 50
    "; 
    //echo $sql_connect;
    
    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error().'<br/>'.$sql_connect);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;$i=0;

    for ($myrow_connect = mysql_fetch_array($res_connect),$i=1; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['name'];
        $data_['items'][$i]['text']=$myrow_connect['name'];
        $data_['items'][$i]['id']=$myrow_connect['id'];
    } 

    echo json_encode($data_);
}

//********************************************************
// сохранение неисправности
if ($_t=='r_neispr_form_new_save'){
    $data_=array();
    $data_['id']='';
    $tip_=_GP('tip_');
        if ($tip_=='' or $tip_-0==0){echo 'Укажите тип оборудования!';exit;}
    $name_=_GP('name_');
        if ($name_==''){echo 'Название неисправности не может быть пустой!';exit;}
    $sql = "SELECT IF(COUNT(*)>0,r_neispravnosti.id,'')
    				FROM r_neispravnosti 
    					WHERE r_neispravnosti.name='"._DB($name_)."' 
    					AND r_neispravnosti.r_tip_oborud_id='"._DB($tip_)."' 
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $r_neispravnosti_id=$myrow[0];
    if ($r_neispravnosti_id==''){
        $sql = "INSERT into r_neispravnosti (
                        chk_active,
        				r_tip_oborud_id,
        				name
        			) VALUES (
        				'1',
        				'"._DB($tip_)."',
        				'"._DB($name_)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $data_['id'] = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    }else{
        echo 'Данная неисправность уже есть в базе!';exit;
    }
    
    echo json_encode($data_);
}

//********************************************************
// копирование товара из заказа
if ($_t=='find_tovar_res_all_copy_m_zakaz_com'){
    $data_=array();
    $id=_GP('id');
    
    $sql = "SELECT  
                    s_cat.id,
                    s_cat.name,
                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id AND a_photo.tip='Основное' ORDER BY sid LIMIT 1) AS img,
                    s_cat.price,
                    s_cat.tip,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id  AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val,
                    m_zakaz_s_cat.kolvo,
                    m_zakaz_s_cat.price AS m_zakaz_s_cat_price,
                    m_zakaz_s_cat.comments AS comments
                    
                    
                    
    
    				FROM s_cat, m_zakaz_s_cat
    					WHERE  m_zakaz_s_cat.m_zakaz_id='"._DB($id)."'
                        AND m_zakaz_s_cat.s_cat_id=s_cat.id
                        ORDER BY m_zakaz_s_cat.id
                        
    ";
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    
    for ($myrow = mysql_fetch_array($res),$i=1; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_[$i]=array();
        $data_[$i]['img']='';
        $img='../../i/s_cat/small/'.$myrow['img'];
        if (file_exists($img) and $myrow['img']!=''){
            $data_[$i]['img']=$myrow['img'];
        }
        
        $data_[$i]['p']=number_format($myrow['price'],0,'.','');
        $data_[$i]['kk']=number_format($myrow['kolvo'],0,'.','');
        $data_[$i]['pp']=number_format($myrow['m_zakaz_s_cat_price'],0,'.','');
        $data_[$i]['cc']=$myrow['comments'];
        $data_[$i]['value']=$myrow[1];
        
        $data_[$i]['pr']=$myrow['prop_val'];
        $data_[$i]['id']=$myrow[0];
        if ($myrow[4]=='Услуга'){
            $data_[$i]['t']='2';
        }else{
            $data_[$i]['t']='1';
        }
        
        $data_[$i]['b']=array();
        $data_[$i]['k']=array();
        
        //ШТРИХ-код
         if ($myrow[4]=='Товар'){
            $sql_barcode = "SELECT  m_tovar.id, 
                            m_tovar.barcode, 
                            (SELECT IF (m_postav_s_cat.kolvo!=COUNT(m1.id),m_postav_s_cat.kolvo,'1') FROM m_tovar AS m1 WHERE m1.m_postav_s_cat_id=m_postav_s_cat.id) AS kolvo,
                            (SELECT SUM(m_zakaz_s_cat_m_tovar.kolvo) FROM m_zakaz_s_cat_m_tovar WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id) AS prodanno
                            
                            
                            
                				FROM m_tovar, m_postav_s_cat
                					WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                    AND m_postav_s_cat.s_cat_id='"._DB($myrow[0])."'
                					
             ";
            $res_barcode = mysql_query($sql_barcode) or die(mysql_error().'<br/>'.$sql_barcode);
            for ($myrow_barcode = mysql_fetch_array($res_barcode); $myrow_barcode==true; $myrow_barcode = mysql_fetch_array($res_barcode))
            {
                //print_rf($myrow_barcode);
                $data_[$i]['b'][$myrow_barcode[0]]=$myrow_barcode[1];
                $data_[$i]['k'][$myrow_barcode[0]]=$myrow_barcode[2]-$myrow_barcode[3];
                
            }
            //echo '<br /><br /><br /><br />';
        }
        
    }
    
    
    echo json_encode($data_);
}

//********************************************************
// Неисправности
if ($_t=='autocomplete_r_neispravnosti'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    $r_tip_id=_GP('_r_tip_id');
              
    $sql_connect = "SELECT  r_neispravnosti.id,
                            r_neispravnosti.name,
                            (SELECT COUNT(r_service_r_neispravnosti.id2) FROM r_service_r_neispravnosti WHERE r_service_r_neispravnosti.id2=r_neispravnosti.id) AS cnt_
    				FROM r_neispravnosti
    					WHERE r_neispravnosti.name LIKE '%"._DB($term)."%'
                        AND r_neispravnosti.r_tip_oborud_id='"._DB($r_tip_id)."'
                        
                       
                        GROUP BY r_neispravnosti.id
                        
                        ORDER BY cnt_ DESC, r_neispravnosti.name
                       LIMIT 50
    "; 
    //echo $sql_connect;
    
    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error().'<br/>'.$sql_connect);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;$i=0;
    $data_['items']=array();
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['name'];
        $data_['items'][$i]['text']=$myrow_connect['name'];
        $data_['items'][$i]['id']=$myrow_connect['id'];
        
        
    } 

    echo json_encode($data_);
}
//********************************************************
// Бренд
if ($_t=='autocomplete_r_brend'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    if ($term!=''){$term=" AND r_brend.name LIKE '%"._DB($term)."%'";}
    $r_tip_id=_GP('_r_tip_id');
    
    
    $sql_connect = "SELECT  DISTINCT
                            r_brend.id,
                            r_brend.name,
                            (SELECT COUNT(r_service.id) FROM r_service WHERE r_service.r_model_id=r_model.id) AS cnt_
    				FROM r_brend, r_model 
    					WHERE r_model.r_brend_id=r_brend.id
                        AND r_model.r_tip_oborud_id='"._DB($r_tip_id)."'
                        
                        $term
                        GROUP BY r_model.r_brend_id
                        
                        ORDER BY cnt_ DESC, r_brend.name
                       LIMIT 50
    "; 
    //echo $sql_connect;
    
    $mt = microtime(true);
    $res_connect = mysql_query($sql_connect) or die(mysql_error().'<br/>'.$sql_connect);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_connect;$data_['_sql']['time'][]=$mt;$i=0;
    $data_['items']=array();
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['name'];
        $data_['items'][$i]['text']=$myrow_connect['name'];
        $data_['items'][$i]['id']=$myrow_connect['id'];
    } 

    echo json_encode($data_);
}
//********************************************************
// Модель
if ($_t=='autocomplete_r_model'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $data_=array();
    $term=_GP('term');if ($term!=''){$term=" AND r_model.name LIKE '%"._DB($term)."%'";}
    $r_tip_id=_GP('_r_tip_id');
    $r_brend_id=_GP('_r_brend_id');
    
    if ($r_tip_id!='' and $r_brend_id!=''){
        $sql_connect = "SELECT  r_model.id,
                                r_model.name
                                
        				FROM r_model
                        
        					WHERE r_model.r_tip_oborud_id='"._DB($r_tip_id)."'
                            AND r_model.r_brend_id='"._DB($r_brend_id)."'
                            $term
                            
                            GROUP BY r_model.id
                            
                            ORDER BY r_model.name
                           LIMIT 12
        "; 
        //echo $sql_connect;
        $res_connect = mysql_query($sql_connect) or die(mysql_error().'<br />'.$sql_connect);
        for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
        {
            $data_[$i]=$myrow_connect['name'];
        } 
    }
    echo json_encode($data_);
}

//********************************************************
// Комплектация
if ($_t=='autocomplete_komplekt'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $data_=array();
    $term=_GP('term');
                      
    $sql_connect = "SELECT  r_service.komplekt AS name,
                            (SELECT COUNT(r_service.id) FROM r_service WHERE r_service.komplekt = name) AS cnt_
                            
    				FROM r_service
                        
                        WHERE r_service.komplekt LIKE '%"._DB($term)."%'
                        
                        GROUP BY r_service.komplekt
                        
                        ORDER BY cnt_ DESC
                       LIMIT 9
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_[$i]=$myrow_connect['name'];
    } 

    echo json_encode($data_);
}
//********************************************************
// Состояние
if ($_t=='autocomplete_sost'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $data_=array();
    $term=_GP('term');
                      
    $sql_connect = "SELECT  r_service.sost AS name,
                            (SELECT COUNT(r_service.id) FROM r_service WHERE r_service.sost = name) AS cnt_
                            
    				FROM r_service
                        
                        WHERE r_service.sost LIKE '%"._DB($term)."%'
                        
                        GROUP BY r_service.sost
                        
                        ORDER BY cnt_ DESC
                       LIMIT 9
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_[$i]=$myrow_connect['name'];
    } 

    echo json_encode($data_);
}
//********************************************************
// Диагностика
if ($_t=='service_diagnoz_send_sms'){
    $data_=array();
    $nomer=_GP('nomer');
        
    $sql = "SELECT  r_service.id,
                    r_service.diagnoz,
                    i_contr.phone
                    
                    
    				FROM r_service, m_zakaz, i_contr
    					WHERE m_zakaz.id='"._DB($nomer)."'
                        AND m_zakaz.id=r_service.m_zakaz_id
                        AND m_zakaz.i_contr_id=i_contr.id
                        
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $i_contr_phone=$myrow[2];
    $r_service_id=$myrow[0];
    $diagnoz=$myrow[1];
        if ($diagnoz==''){echo 'Введите диагностическое заключение!';exit;}
    $text='Диагностика к акту №'.$nomer.': '.$diagnoz;
    
    $data_['send']=array();
    if (require_once("../class/transport.php")){} else {$data['error'][]='error no file ../class/transport.php';}
    $api = new Transport();
    $params["text"]= $text;
    $params["use_alfasource"]='1';

    if ($i_contr_phone!='') {
        $phones = array($i_contr_phone);
        $data_['send'] = $api->send($params,$phones);
        
        if ($data_['send']['code']==1){
            
            if (!isset($a_admin_id_cur)){echo 'Ошибка авторизации, $a_admin_id_cur не определен';}
            
            //id пользователя
            $sql = "SELECT IF(COUNT(*)>0,m_a_admin_i_contr.id,'')
            				FROM m_a_admin_i_contr 
            					WHERE m_a_admin_i_contr.id1='"._DB($a_admin_id_cur)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $m_a_admin_i_contr_id=$myrow[0];
            if ($m_a_admin_i_contr_id==''){
                $sql = "INSERT into m_a_admin_i_contr (
                				id1
                			) VALUES (
                				'"._DB($a_admin_id_cur)."'
                )";
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $m_a_admin_i_contr_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
            }
            
            $sql = "SELECT MAX(sid)+1 FROM m_dialog";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $sid=$myrow[0];
            
            //Добавляем ссобщение об оповещении
            $sql = "INSERT into m_dialog (
            				pid,
            				sid,
                            chk_active,
                            m_a_admin_i_contr_id1,
                            row_id,
                            subject,
                            message,
                            chk_in_out,
                            chk_view,
                            a_menu_id
            			) VALUES (
            				'0',
                            '"._DB($sid)."',
                            '1',
            				'"._DB($m_a_admin_i_contr_id)."',
                            '"._DB($nomer)."',
                            'Отправка сообщения клиенту: диагностическое заключение',
                            'Сообщение: <strong>"._DB($text)."</strong> на номер: "._DB($i_contr_phone)."',
                            '1',
                            '0',
                            '16'
                            
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
        }
        
    } else {echo 'Не указан номер телефона клиента!';exit;}
    
    echo json_encode($data_);
}
//********************************************************
// Состояние
if ($_t=='service_status_info_send_sms'){
    $data_=array();
    $nomer=_GP('nomer');
    
    $sql = "SELECT  r_tip_oborud.name,
                    r_brend.name,
                    i_contr.phone,
                    (SELECT SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_summ,
                    ((SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.a_menu_id='16' AND m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.tip='Кредит')-(SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.a_menu_id='16' AND m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.tip='Дебет')) AS pl_summ,
                    r_service.id
                    
                    
    				FROM r_service, m_zakaz, r_model, r_brend, r_tip_oborud, i_contr
    					WHERE m_zakaz.id='"._DB($nomer)."'
                        AND m_zakaz.id=r_service.m_zakaz_id
                        AND r_model.id=r_service.r_model_id
                        AND r_model.r_tip_oborud_id=r_tip_oborud.id
                        AND r_model.r_brend_id=r_brend.id
                        AND m_zakaz.i_contr_id=i_contr.id
                        
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $i_contr_phone=$myrow[2];
    $r_service_id=$myrow[5];
    
    $text='';
    if ($myrow[0]=='Ноутбук')
    {
        $text='Ноутбук готов';
    }
    elseif ($myrow[0]=='Системный блок')
    {
        $text='Системный блок готов';
    }
    elseif ($myrow[0]=='Жесткий диск')
    {
        $text='Жесткий диск готов';
    }
    elseif ($myrow[0]=='Монитор')
    {
        $text='Монитор готов';
    }
    elseif ($myrow[0]=='Планшет')
    {
        $text='Планшет готов';
    }
    elseif ($myrow[0]=='Телефон')
    {
        $text='Телефон готов';
    }
    elseif ($myrow[0]=='Flash')
    {
        $text='Flash готов';
    }
    else{
        $text='Оборудование готово';
    }
    
    
    
    
    $summ=$myrow[3]-$myrow[4];
    if ($summ>0){
        if ($myrow[3]-$myrow[4]>0){
            $text.='. ';
            if ($myrow[4]>0){//были платежи
                $text.='Доплата';
            }else{
                $text.='Стоимость ремонта';
            }
        }
        $text.=' '.$summ.'р';
    }
    $text.='. Сервисный центр.';
    
    
    
    
    
    $data_['send']=array();
    if (require_once("../class/transport.php")){} else {$data['error'][]='error no file ../class/transport.php';}
    $api = new Transport();
    $params["text"]= $text;
    $params["use_alfasource"]='1';

    if ($i_contr_phone!='') {
        $phones = array($i_contr_phone);
        $data_['send'] = $api->send($params,$phones);
        
        if ($data_['send']['code']==1){
            
            //отмечаем дату оповещения
            $sql = "
            		UPDATE r_service 
            			SET  
            				data_inform='".date('Y-m-d H:i:s')."'
            		
            		WHERE id='"._DB($r_service_id)."'
            ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            if (!isset($a_admin_id_cur)){echo 'Ошибка авторизации, $a_admin_id_cur не определен';}
            
            //id пользователя
            $sql = "SELECT IF(COUNT(*)>0,m_a_admin_i_contr.id,'')
            				FROM m_a_admin_i_contr 
            					WHERE m_a_admin_i_contr.id1='"._DB($a_admin_id_cur)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $m_a_admin_i_contr_id=$myrow[0];
            if ($m_a_admin_i_contr_id==''){
                $sql = "INSERT into m_a_admin_i_contr (
                				id1
                			) VALUES (
                				'"._DB($a_admin_id_cur)."'
                )";
                
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $m_a_admin_i_contr_id = mysql_insert_id();
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
            }
            
            $sql = "SELECT MAX(sid)+1 FROM m_dialog";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $sid=$myrow[0];
            
            //Добавляем ссобщение об оповещении
            $sql = "INSERT into m_dialog (
            				pid,
            				sid,
                            chk_active,
                            m_a_admin_i_contr_id1,
                            row_id,
                            subject,
                            message,
                            chk_in_out,
                            chk_view,
                            a_menu_id
            			) VALUES (
            				'0',
                            '"._DB($sid)."',
                            '1',
            				'"._DB($m_a_admin_i_contr_id)."',
                            '"._DB($nomer)."',
                            'Отправка сообщения клиенту о готовности техники',
                            'Сообщение: <strong>"._DB($text)."</strong> на номер: "._DB($i_contr_phone)."',
                            '1',
                            '0',
                            '16'
                            
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
        }else{
            echo 'Сообщение не отправленно!<br />';
            print_rf($data_['send']);
            exit;
        }
        
    } else {echo 'Не указан номер телефона клиента!';exit;}

    echo json_encode($data_);
}

//********************************************************
// Получаем информацию по звонку
if ($_t=='zakaz_load_from_call'){
    
    $nomer=_GP('id');
    $sql = "SELECT  c_call_client.comments,
                    i_contr.id AS i_contr_id_,
                    i_contr.name,
                    i_contr.phone,
                    i_contr.email,
                    (SELECT IF(COUNT(*)>0,i_contr_i_contr_org.id2,'') FROM i_contr_i_contr_org WHERE i_contr_i_contr_org.id1=i_contr.id LIMIT 1) AS i_contr_org_id,
                    (SELECT IF(COUNT(*)>0,(SELECT IF(COUNT(*)>0,i_contr_org.name,'') FROM i_contr_org WHERE i_contr_i_contr_org.id2=i_contr_org.id LIMIT 1),'') FROM i_contr_i_contr_org WHERE i_contr_i_contr_org.id1=i_contr.id LIMIT 1) AS i_contr_org_name
                    
    				FROM c_call_client, i_contr
    					WHERE c_call_client.id='"._DB($nomer)."'
                        AND c_call_client.i_contr_id=i_contr.id
    					ORDER BY c_call_client.id DESC
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $sql_i_contr_org = "SELECT  i_contr_org.id, 
                                i_contr_org.name
                    FROM i_contr_org, i_contr_i_contr_org
    					WHERE i_contr_i_contr_org.id2=i_contr_org.id
                        AND i_contr_i_contr_org.id1='"._DB($myrow['i_contr_id_'])."'
    ";
     
    $res_i_contr_org = mysql_query($sql_i_contr_org);if (!$res_i_contr_org){echo $sql_i_contr_org;exit();}
    
    for ($myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j=0; $myrow_i_contr_org==true; $myrow_i_contr_org = mysql_fetch_array($res_i_contr_org),$j++)
    {
        $data_['i_contr_org_name'][$j]=$myrow_i_contr_org['name'];
        $data_['i_contr_org_id'][$j]=$myrow_i_contr_org['id'];
    }
    
    $data_['comments']=$myrow['comments'];
    $data_['i_contr_id']=$myrow['i_contr_id_'];
    $data_['i_contr_name']=$myrow['name'];
    $data_['i_contr_phone']=$myrow['phone'];
    $data_['i_contr_email']=$myrow['email'];
    
    $sql = "SELECT  a_col.col,
                    a_menu.inc,
                    c_call_answer.id_z_p_p,
                    c_call_answer.comments
                    
    				FROM c_call_answer, c_questions, a_col, a_menu
    					WHERE c_call_answer.c_call_client_id= '"._DB($nomer)."' 
                        AND c_questions.id=c_call_answer.c_questions_id
                        AND a_col.id=c_questions.a_col_id
                        AND a_menu.id=a_col.a_menu_id
                        
    						
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        //print_rf($myrow);
        
        if ($myrow['id_z_p_p']>0){
            $sql_col = "SELECT `"._DB($myrow['inc'])."`.`"._DB($myrow['col'])."`
            				FROM `"._DB($myrow['inc'])."`
            					WHERE `"._DB($myrow['inc'])."`.id='"._DB($myrow['id_z_p_p'])."' 
            				
            	"; 
            
            $mt = microtime(true);
            $res_col = mysql_query($sql_col) or die(mysql_error().'<br />'.$sql_col);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_col;$data_['_sql']['time'][]=$mt;
            $myrow_col = mysql_fetch_array($res_col);
            if (isset($data_[$myrow['inc']]['val'])){
                
                $nn=$data_[$myrow['inc']]['id_z_p_p'];
                $n=$data_[$myrow['inc']]['val'];
                unset($data_[$myrow['inc']]['val'],$data_[$myrow['inc']]['id_z_p_p']);
                $data_[$myrow['inc']]['val'][]=$n;
                $data_[$myrow['inc']]['id_z_p_p'][]=$nn;
                $data_[$myrow['inc']]['val'][]=$myrow_col[0];
                $data_[$myrow['inc']]['id_z_p_p'][]=$myrow['id_z_p_p'];
            }else{
                $data_[$myrow['inc']]['val']=$myrow_col[0];
                $data_[$myrow['inc']]['id_z_p_p']=$myrow['id_z_p_p'];
            }
            
        }else{
            $data_[$myrow['inc']]['id_z_p_p']=$myrow['id_z_p_p'];
            $data_[$myrow['inc']]['val']=$myrow['comments'];
        }
        
    }
    
    
    //print_rf($myrow);
    
    echo json_encode($data_);
}    
//********************************************************
// Отрправка сообщения по заказу
if ($_t=='send_mess_comm_send'){
    
    
    $data_=array();
    $tip=_GP('tip');
    $id=_GP('id');
    
    $sql = "SELECT  m_dialog.message,
                    m_zakaz.id AS m_zakaz_id,
                    (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM m_a_admin_i_contr, a_admin WHERE m_dialog.m_a_admin_i_contr_id1=m_a_admin_i_contr.id AND m_a_admin_i_contr.id1=a_admin.id) AS a_admin_name,
                    m_zakaz.i_contr_id,
                    i_contr.name AS i_contr_name,
                    i_contr.phone AS i_contr_phone,
                    i_contr.email AS i_contr_email,
                    (SELECT IF(COUNT(*)>0,i_tp.phone,'') FROM m_a_admin_i_contr, a_admin, i_tp WHERE m_dialog.m_a_admin_i_contr_id1=m_a_admin_i_contr.id AND m_a_admin_i_contr.id1=a_admin.id AND a_admin.i_tp_id=i_tp.id) AS a_admin_i_tp_phone,
                    (SELECT IF(COUNT(*)>0,i_contr_org.name,'') FROM m_a_admin_i_contr, a_admin, i_tp,i_contr_org WHERE m_dialog.m_a_admin_i_contr_id1=m_a_admin_i_contr.id AND m_a_admin_i_contr.id1=a_admin.id AND a_admin.i_tp_id=i_tp.id AND i_tp.i_contr_org_id=i_contr_org.id) AS i_contr_org_name
                    
                                        
    				FROM m_dialog, m_zakaz, i_contr
    					WHERE m_dialog.id='"._DB($id)."' 
                        AND m_dialog.row_id=m_zakaz.id
                        AND m_dialog.a_menu_id='16'
                        AND m_zakaz.i_contr_id=i_contr.id
    				
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $i_contr_phone=$myrow['i_contr_phone'];
    $i_contr_email=$myrow['i_contr_email'];
    $m_zakaz_id=$myrow['m_zakaz_id'];
    $message=$myrow['message'];
    $a_admin_name=$myrow['a_admin_name'];
    $i_contr_name=$myrow['i_contr_name'];
    $i_contr_org_name=$myrow['i_contr_org_name'];
    $a_admin_i_tp_phone=$myrow['a_admin_i_tp_phone'];
    
    if ($tip=='sms'){
        
        $data_['send']=array();
        if (require_once("../class/transport.php")){} else {$data['error'][]='error no file ../class/transport.php';}
        $api = new Transport();
        $params["text"]=str_replace(array('\t','\n'),'',strip_tags($message));
        //echo '<pre>+'.$params["text"].'+</pre>';exit;
        
        $params["use_alfasource"]='1';
    
        if ($i_contr_phone!='') {
            $phones = array($i_contr_phone);
            $data_['send'] = $api->send($params,$phones);
            
            if ($data_['send']['code']==1){
                    
                $sql = "UPDATE m_dialog 
                			SET  
                				data_send_sms='".date('Y-m-d H:i:s')."'
                		
                		WHERE id='"._DB($id)."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            }else{
                echo 'Сообщение не отправленно!<br />';
                print_rf($phones);print_rf($params);
                print_rf($data_['send']);
                exit;
            }
        }else{
            echo 'Не определен телефон клиента';exit;
        }
    }
    elseif ($tip=='email'){
        if ($i_contr_email!=''){
            
            $file_=array();
            $file_name=array();
            
            $sql = "SELECT a_photo.img, a_photo.comments
                				FROM a_photo 
                					WHERE a_photo.row_id='"._DB($id)."'
                                    AND a_photo.a_menu_id='18'
                						
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $file_n='../../i/m_dialog/original/'.$myrow['img'];
                
                if (file_exists($file_n)){
                    $ext=preg_replace("/.*?\./", '', $file_n);
                    $file_[]=$file_n;
                    $file_name[]=$myrow['comments'].'.'.$ext;
                }
                
            }
            
        
            $message.='<p>--</p><p>С Уважением, '.$a_admin_name.'</p><p>'.$i_contr_org_name.'</p><p>'.$a_admin_i_tp_phone.'</p>';
            send_mail_smtp(
                    $i_contr_email,
                    'Заказ №'.$m_zakaz_id,
                    $message, 
                    $i_contr_name,
                    $_SESSION['a_options']['email администратора'],
                    $i_contr_org_name,
                    1,
                    $file_,
                    $file_name
            );
            $sql = "UPDATE m_dialog 
                			SET  
                				data_send_email='".date('Y-m-d H:i:s')."'
                		
                		WHERE id='"._DB($id)."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        }else{
            echo 'Не определен email клиента!';exit;
        }
    }
    else{
        echo 'Тип не определен!';exit;
    }
    
    
    echo json_encode($data_);
}

//********************************************************
// Получение информации по товарам или услугам для добавления в заказ
if ($_t=='s_cat_add_from_id'){
    $data_=array();
    $id_=_GP('id_');
    if ($id_==''){echo 'Не указан id';exit;}
       
   $id_arr=array();
   if (mb_strstr($id_,',',false,'utf-8')==true){
        $id_arr=explode(',',$id_);
   }else{
        $id_arr[]=$id_;
   }
   $id_arr2=array();
   foreach($id_arr as $k =>$val){
        $id_arr2[$k]=preg_replace('/[\D]{1,}/s', '',$val);
   }
   unset($id_arr);$id_arr=$id_arr2;
    $sql = "SELECT  
                    s_cat.id,
                    s_cat.name,
                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id AND a_photo.tip='Основное' ORDER BY sid LIMIT 1) AS img,
                    s_cat.price,
                    s_cat.tip,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id  AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val
                    
                    
                    
    
    				FROM s_cat
    					WHERE s_cat.id IN ('".(implode("','",$id_arr))."')
                        
                       
                        ORDER BY FIELD(`id`,"._DB($id_).")
                        
    ";
   
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        
        $data_['i'][$i]['img']='';
        $img='../../i/s_cat/small/'.$myrow['img'];
        if (file_exists($img) and $myrow['img']!=''){
            $data_['i'][$i]['img']=$myrow['img'];
        }
        
        $data_['i'][$i]['p']=number_format($myrow['price'],0,'.','');
        $data_['i'][$i]['value']=$myrow[1];
        
        $data_['i'][$i]['pr']=$myrow['prop_val'];
        $data_['i'][$i]['id']=$myrow[0];
        if ($myrow[4]=='Услуга'){
            $data_['i'][$i]['t']='2';
        }else{
            $data_['i'][$i]['t']='1';
        }
        
        $data_['i'][$i]['b']=array();
        $data_['i'][$i]['k']=array();
        
        //ШТРИХ-код
         if ($myrow[4]=='Товар'){
            $sql_barcode = "SELECT  m_tovar.id, 
                            m_tovar.barcode, 
                            (SELECT IF (m_postav_s_cat.kolvo!=COUNT(m1.id),m_postav_s_cat.kolvo,'1') FROM m_tovar AS m1 WHERE m1.m_postav_s_cat_id=m_postav_s_cat.id) AS kolvo,
                            (SELECT SUM(m_zakaz_s_cat_m_tovar.kolvo) FROM m_zakaz_s_cat_m_tovar WHERE m_zakaz_s_cat_m_tovar.id2=m_tovar.id) AS prodanno
                            
                            
                            
                				FROM m_tovar, m_postav_s_cat
                					WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                    AND m_postav_s_cat.s_cat_id='"._DB($myrow[0])."'
                					
             ";
            $mt = microtime(true);
            $res_barcode = mysql_query($sql_barcode) or die(mysql_error().'<br/>'.$sql_barcode);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_barcode;$data_['_sql']['time'][]=$mt;
            for ($myrow_barcode = mysql_fetch_array($res_barcode); $myrow_barcode==true; $myrow_barcode = mysql_fetch_array($res_barcode))
            {
                //print_rf($myrow_barcode);
                $data_['i'][$i]['b'][$myrow_barcode[0]]=$myrow_barcode[1];
                $data_['i'][$i]['k'][$myrow_barcode[0]]=$myrow_barcode[2]-$myrow_barcode[3];
                
            }
            //echo '<br /><br /><br /><br />';
        }
        
    }
    
    
    echo json_encode($data_);
}


//********************************************************
// Получение информации по просроченным заказам
if ($_t=='get_zakaz_info_from_shablon'){
    $data_=array();
    
    $sql = "SELECT      m_zakaz.id, 
                        m_zakaz.data_end,
                        m_zakaz.data,  
                        i_contr.name AS i_contr_name,
                        i_contr.phone AS i_contr_phone,
                        i_contr.email AS i_contr_email,
                        m_zakaz.project_name,
                        
                        (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                        (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                        m_zakaz.comments
                        
                        
    				FROM m_zakaz,i_contr
                            WHERE m_zakaz.data_end!='0000-00-00 00:00:00'
                            AND m_zakaz.i_contr_id=i_contr.id
                            AND m_zakaz.status NOT IN ('Отменен','Выполнен')
                            GROUP BY m_zakaz.id
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;

    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        
        $data_['i'][$i]=$myrow['id'];
        $data_['in_'][$i]=$myrow['i_contr_name'];
        $data_['ip'][$i]=$myrow['i_contr_phone'];
        $data_['ie'][$i]=$myrow['i_contr_email'];
        $data_['pn'][$i]=$myrow['project_name'];
        $data_['c'][$i]=$myrow['comments'];
        $data_['d1'][$i]=date('d.m.Y H:i:s',strtotime($myrow['data']));//'2017-11-09T16:00:00'
        $data_['d2'][$i]='';$data_['d_'][$i]='';
        if ($myrow['data_end']!='null' and $myrow['data_end']!='0000-00-00 00:00:00' and $myrow['data_end']!=''){
            
            $data_['d2'][$i]=date('d.m.Y',strtotime($myrow['data_end']));
            $data_['d_'][$i]=raznica_po_vremeni(date('d.m.Y H:i:s'),$myrow['data_end'],'hours');
        }
        
        //Товар
        $data_['w'][$i]['i']=array();
        $data_['w'][$i]['n']=array();
        $data_['w'][$i]['p']=array();
        $data_['w'][$i]['k']=array();
        $data_['w'][$i]['c']=array();
        $data_['w'][$i]['im']=array();
        $data_['w'][$i]['pi']=array();
        $data_['w'][$i]['ps']=array();
        $sql_s_cat = "SELECT    s_cat.id,
                                s_cat.name,
                                m_zakaz_s_cat.price,
                                m_zakaz_s_cat.kolvo,
                                m_zakaz_s_cat.comments,
                                (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id ORDER BY a_photo.sid LIMIT 1) AS img,
                                (SELECT IF(COUNT(*)>0,m_postav_s_cat.m_postav_id,'') FROM m_zakaz_s_cat_m_tovar, m_tovar, m_postav_s_cat WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id) AS m_postav_id,
                                (SELECT SUM(m_postav_s_cat.price*m_zakaz_s_cat.kolvo) FROM m_zakaz_s_cat_m_tovar, m_tovar, m_postav_s_cat WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id) AS m_postav_sum
                                
            				FROM s_cat, m_zakaz_s_cat
            					WHERE m_zakaz_s_cat.s_cat_id=s_cat.id
                                AND m_zakaz_s_cat.m_zakaz_id='"._DB($myrow['id'])."'
                                ORDER BY m_zakaz_s_cat.id
         ";
        $mt = microtime(true);
        $res_s_cat = mysql_query($sql_s_cat) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_cat;$data_['_sql']['time'][]=$mt;
        $j=0;
        for ($myrow_s_cat = mysql_fetch_array($res_s_cat); $myrow_s_cat==true; $myrow_s_cat = mysql_fetch_array($res_s_cat))
        {
            $data_['w'][$i]['i'][$j]=$myrow_s_cat['id'];
            $data_['w'][$i]['n'][$j]=$myrow_s_cat['name'];
            $data_['w'][$i]['p'][$j]=number_format($myrow_s_cat['price'],0,'.','');
            $data_['w'][$i]['k'][$j]=$myrow_s_cat['kolvo'];
            $data_['w'][$i]['c'][$j]=$myrow_s_cat['comments'];
            $data_['w'][$i]['pi'][$j]=$myrow_s_cat['m_postav_id'];
            $data_['w'][$i]['ps'][$j]=$myrow_s_cat['m_postav_sum'];
            $data_['w'][$i]['im'][$j]=$myrow_s_cat['img'];
                if (!file_exists('../../i/s_cat/original/'.$myrow_s_cat['img'])){$data_['w'][$i]['im'][$j]='';}
            $j++;
        }
        
        
    }
    
    
    echo json_encode($data_);
}
//************************************************************************************************** 
}else{
    
    $_SESSION['error']['auth_'.date('Y-m-d H:i:s')]='Ошибка авторизации! $login="'.@$_SESSION['admin']['login'].'", pass: "'.@$_SESSION['admin']['password'].'"';
    echo 'Ошибка авторизации!';
    
}

?>