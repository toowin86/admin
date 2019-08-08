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


if ($_t=='start_menu_load'){
    
    $data_=array();
    $data_['res']=array();
    
    $id_=_GP('id_');
    $param=_GP('param');
    
    
    
    //ТОВАР ИЗ КАТАЛОГА
    if ($id_=='s_cat_add'){
        if (isset($param['tip'])){
            if ($param['tip']=='last'){
                 $sql = "SELECT     s_cat.id, 
                                    s_cat.name, 
                                    s_cat.price, 
                                    (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id ORDER BY a_photo.sid LIMIT 1) AS photo,
                                    TIME_TO_SEC(TIMEDIFF(NOW(), s_cat.data_create))/3600 AS hours_
                    				FROM s_cat
                    					WHERE id>0
                    						ORDER BY s_cat.data_create DESC
                                            LIMIT 8
                    ";
                     
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $i=0;
                    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                    {
                        $data_['res'][$i]['id']=$myrow[0];
                        $data_['res'][$i]['name']=$myrow[1];
                        $data_['res'][$i]['price']=$myrow[2];
                        $data_['res'][$i]['img']=$myrow[3];
                        $data_['res'][$i]['hours_']=number_format($myrow[4],0,'.','');
                        $i++;
                    }
            }
            if ($param['tip']=='pop'){
                $sql = "SELECT  s_cat.id, 
                                s_cat.name, 
                                s_cat.price, 
                                (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE a_photo.a_menu_id='7' AND a_photo.row_id=s_cat.id ORDER BY a_photo.sid LIMIT 1) AS photo,
                                (SELECT COUNT(l_s_cat_pop.s_cat_id) FROM l_s_cat_pop WHERE l_s_cat_pop.s_cat_id=s_cat.id AND l_s_cat_pop.data_create > '".date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))-(1*30*24*60*60))."' ) AS pop_
                				FROM s_cat
                					ORDER BY pop_ DESC
                                        LIMIT 8
                ";
                 
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $i=0;
                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                {
                    $data_['res'][$i]['id']=$myrow[0];
                    $data_['res'][$i]['name']=$myrow[1];
                    $data_['res'][$i]['price']=$myrow[2];
                    $data_['res'][$i]['img']=$myrow[3];
                    $data_['res'][$i]['cnt']=$myrow[4];
                    $i++;
                }
            }
            
            
        }
    }
    
     if ($id_=='m_zakaz_work'){
        if (isset($param['tip'])){
            if ($param['tip']=='in_work'){//в работе
                $sql = "SELECT  m_zakaz.id, 
                                m_zakaz.project_name, 
                                m_zakaz.data, 
                                m_zakaz.data_end, 
                                m_zakaz.status,
                                ((SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.a_menu_id='16' AND m_platezi.id_z_p_p=m_zakaz.id AND  m_platezi.tip='Кредит')-(SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.a_menu_id='16' AND m_platezi.id_z_p_p=m_zakaz.id AND  m_platezi.tip='Дебет')) AS pay,
                                (SELECT IF(COUNT(*)>0,(SELECT CONCAT(r_tip_oborud.name,' ',r_brend.name,' ',r_model.name) FROM r_tip_oborud, r_brend, r_model WHERE r_model.id=r_service.r_model_id AND r_model.r_tip_oborud_id=r_tip_oborud.id AND r_model.r_brend_id=r_brend.id),'') FROM r_service WHERE r_service.m_zakaz_id=m_zakaz.id LIMIT 1) AS service
                                
                                FROM m_zakaz
                                WHERE m_zakaz.status NOT IN ('Выполнен','Отменен')
                				
                                    ORDER BY m_zakaz.data_create DESC
                                        LIMIT 4
                ";
                 
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $i=0;
                for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
                {
                    if ($myrow['service']!=''){$myrow['service']=' <div class="r_model">'.$myrow['service'].'</div>';}
                    $data_['res'][$i]['id']=$myrow[0];
                    $data_['res'][$i]['project_name']=$myrow[1].$myrow['service'];
                    $data_['res'][$i]['pay']=$myrow['pay'];
                    $data_['res'][$i]['data_']='';
                        if ($myrow[2]!='0000-00-00 00:00:00'){$data_['res'][$i]['data_']=date('d.m.Y H:i',strtotime($myrow[2]));}
                    $data_['res'][$i]['data_end']='';
                        if ($myrow[3]!='0000-00-00 00:00:00'){$data_['res'][$i]['data_end']=date('d.m.Y H:i',strtotime($myrow[3]));}
                    
                    $data_['res'][$i]['status']=$myrow[4];
                    $data_['res'][$i]['items']=array();
                    $sum=0;
                    
                    $sql_m_zakaz = "SELECT s_cat.id, s_cat.name, m_zakaz_s_cat.price, m_zakaz_s_cat.kolvo
                    				FROM s_cat, m_zakaz_s_cat 
                    					WHERE m_zakaz_s_cat.s_cat_id=s_cat.id
                    					AND m_zakaz_s_cat.m_zakaz_id='"._DB($myrow[0])."'
                    ";
                     
                    $mt = microtime(true);
                    $res_m_zakaz = mysql_query($sql_m_zakaz) or die(mysql_error().'<br>'.$sql_m_zakaz);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_zakaz;$data_['_sql']['time'][]=$mt;
                    $j=0;
                    
                    for ($myrow_m_zakaz = mysql_fetch_array($res_m_zakaz); $myrow_m_zakaz==true; $myrow_m_zakaz = mysql_fetch_array($res_m_zakaz))
                    {
                        $data_['res'][$i]['items'][$j]['id']=$myrow_m_zakaz['id'];
                        $data_['res'][$i]['items'][$j]['name']=$myrow_m_zakaz['name'];
                        $data_['res'][$i]['items'][$j]['price']=$myrow_m_zakaz['price'];
                        $data_['res'][$i]['items'][$j]['kolvo']=$myrow_m_zakaz['kolvo'];
                        $sum=$sum+($myrow_m_zakaz['price']*$myrow_m_zakaz['kolvo']);
                        $j++;
                    }
                    $data_['res'][$i]['sum']=$sum;
                    
                    $i++;
                }
            }   
            
        }
        
     }
    if ($id_=='m_zakaz_dohod'){//График доходов
    
        //Получаем данные по оси х - даты
        $data_['res']['x']=array();
        
        $period=_GP('period');
        if ($period==''){$period='year';}
        
        $group=_GP('group');
        if ($group==''){$group='mounth';}
        
        
        //Указываем период
        $data_['res']=get_period($period,$group);
        
        
        
        
        
        
        
        $data_['res']['items']=array();
        $data_['res']['works']=array();
        //ТОВАРЫ
        if (strstr($param['tip'],'items')==true){
            foreach($data_['res']['x_start'] as $key => $data_start){
                
                $sql = "SELECT SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price) 
                				FROM s_cat, m_zakaz_s_cat, m_zakaz
                					WHERE s_cat.tip='Товар'
                                    AND m_zakaz_s_cat.s_cat_id=s_cat.id
                                    AND m_zakaz_s_cat.m_zakaz_id=m_zakaz.id
                                    AND m_zakaz.status='Выполнен'
                                    AND m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                    AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                    
                ";
                 $mt = microtime(true);
                 $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                 $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                 $myrow = mysql_fetch_array($res);
                 $data_['res']['items'][$key]=$myrow[0];
                
            }
            
        }
        //УСЛУГИ
        if (strstr($param['tip'],'work')==true){
            foreach($data_['res']['x_start'] as $key => $data_start){
                
                $sql = "SELECT SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price) 
                				FROM s_cat, m_zakaz_s_cat, m_zakaz
                					WHERE s_cat.tip='Услуга'
                                    AND m_zakaz_s_cat.s_cat_id=s_cat.id
                                    AND m_zakaz_s_cat.m_zakaz_id=m_zakaz.id
                                    AND m_zakaz.status='Выполнен'
                                    AND m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                    AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                    
                ";
                 $mt = microtime(true);
                 $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
                 $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                 $myrow = mysql_fetch_array($res);
                 $data_['res']['works'][$key]=$myrow[0];
                
            }
        }
         foreach($data_['res']['x_start'] as $key => $data_start){
            $data_['res']['sum'][$key]=0;
            if (isset($data_['res']['works'][$key]) and $data_['res']['works'][$key]>0){
                $data_['res']['sum'][$key]+=$data_['res']['works'][$key];
            }
            if (isset($data_['res']['items'][$key]) and $data_['res']['items'][$key]>0){
                $data_['res']['sum'][$key]+=$data_['res']['items'][$key];
            }
         }

    }
    
    
    //СРЕДНИЙ ЧЕК
    if ($id_=='m_zakaz_middle_check'){
        $data_['res']['x']=array();
        
        $period=_GP('period');
        if ($period==''){$period='year';}
        
        $group=_GP('group');
        if ($group==''){$group='mounth';}
        
        //Указываем период
        $data_['res']=get_period($period,$group);

        
       foreach($data_['res']['x_start'] as $key => $data_start){ 
            $sql = "SELECT  SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price)/COUNT(m_zakaz.id)
                            
                    				FROM m_zakaz_s_cat, m_zakaz
                    					WHERE  m_zakaz_s_cat.m_zakaz_id=m_zakaz.id
                                        AND m_zakaz.status='Выполнен'
                                        AND m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['res']['check'][$key]=$myrow[0];
            }
        }
        
    }
    //ЗАКАЗЫ
    if ($id_=='m_zakaz_cnt'){
        $data_['res']['x']=array();
        
        $period=_GP('period');
        if ($period==''){$period='year';}
        
        $group=_GP('group');
        if ($group==''){$group='mounth';}
        
        
        //Указываем период
        $data_['res']=get_period($period,$group);
        
       foreach($data_['res']['x_start'] as $key => $data_start){ 
            $sql = "SELECT  COUNT(m_zakaz.id)
                            
                            
                    				FROM m_zakaz
                    					WHERE  m_zakaz.status='Выполнен'
                                        AND m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['res']['good'][$key]=$myrow[0];
            }
            //отмененные
            $sql = "SELECT  COUNT(m_zakaz.id)
                            
                            
                    				FROM m_zakaz
                    					WHERE  m_zakaz.status='Отменен'
                                        AND m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['res']['cancel'][$key]=$myrow[0];
            }
            //выполненные
            $sql = "SELECT  COUNT(m_zakaz.id)
                            
                            
                    				FROM m_zakaz
                    					WHERE  m_zakaz.status NOT IN ('Отменен','Выполнен')
                                        AND m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['res']['in_work'][$key]=$myrow[0];
            }
            //выполненные
            $sql = "SELECT  COUNT(m_zakaz.id)
                            
                            
                    				FROM m_zakaz
                    					WHERE   m_zakaz.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_zakaz.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['res']['all'][$key]=$myrow[0];
            }
        }
        
    }
    
    
    //ПЛАТЕЖИ
    if ($id_=='m_platezi_in_out'){
        $data_['res']['x']=array();
        
        $period=_GP('period');
        if ($period==''){$period='year';}
        
        $group=_GP('group');
        if ($group==''){$group='mounth';}
        
        
        //Указываем период
        $data_['res']=get_period($period,$group);
        
       foreach($data_['res']['x_start'] as $key => $data_start){ 
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='16'
                                        AND m_platezi.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_platezi.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['vozvrat'][$key]=$myrow[0];
            
            // Поставщики
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='17'
                                        AND m_platezi.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_platezi.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['m_postav'][$key]=$myrow[0];
            
            
            // Расходы
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='100'
                                        AND m_platezi.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_platezi.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['i_rashodi'][$key]=$myrow[0];
            
            // З/П
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='4'
                                        AND m_platezi.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_platezi.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['a_admin'][$key]=$myrow[0];
            
            // Дивиденды
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='105'
                                        AND m_platezi.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_platezi.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['i_inout'][$key]=$myrow[0];
            
            
            // Реклама
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='40'
                                        AND m_platezi.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND m_platezi.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                   
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['i_reklama'][$key]=$myrow[0];
            
        }
        
        
    }
    
    //ТОП КЛИЕНТОВ
    if ($id_=='i_contr_top'){
    
    
    
    $data_['res']['name']=array();
      $data_['res']['id']=array();
      $data_['res']['val']=array();
      

        $sql = "SELECT  i_contr.id, 
                        i_contr.name, 
                        (SELECT COUNT(z3.id) FROM m_zakaz AS z3 WHERE z3.i_contr_id=i_contr.id AND z3.status!='Отменен') AS cnt_,
                        (SELECT IF(COUNT(*)>0,SUM(p1.summa),0) FROM m_zakaz AS z4, m_platezi AS p1 WHERE z4.i_contr_id=i_contr.id AND p1.id_z_p_p=z4.id AND p1.a_menu_id='16' AND p1.tip='Кредит' AND z4.status!='Отменен')-(SELECT IF(COUNT(*)>0,SUM(p1.summa),0) FROM m_zakaz AS z5, m_platezi AS p1 WHERE z5.i_contr_id=i_contr.id AND p1.id_z_p_p=z5.id AND p1.a_menu_id='16' AND p1.tip='Дебет' AND z5.status!='Отменен') AS summa_
                        
                            
				FROM m_zakaz AS z1, i_contr
					WHERE  z1.i_contr_id=i_contr.id
                    AND z1.status!='Отменен'
                    AND i_contr.name NOT IN ('Разборка','Ремонт','Брак')
                    GROUP BY i_contr.id
                    ORDER BY summa_ DESC       
                    LIMIT 15      
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            //$myrow = mysql_fetch_array($res);
            $i=0;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['res']['id'][$i]=$myrow[0];
                $data_['res']['name'][$i]=$myrow[1];
                if (strstr($myrow[1],' ')==true){
                    $arr_=explode(' ',$myrow[1]);
                    $data_['res']['name'][$i]=$arr_[0];
                }
                $data_['res']['cnt'][$i]=$myrow[2];
                $data_['res']['val'][$i]=$myrow[3];
                $i++;
            }
    }
    //СТРУКТУРА КЛИЕНТОВ
    if ($id_=='i_contr_struktura'){
        $data_['res']['x']=array();
        
        $period=_GP('period');
        if ($period==''){$period='year';}
        
        $group=_GP('group');
        if ($group==''){$group='mounth';}
        
        
        //Указываем период
        $data_['res']=get_period($period,$group);
        
        //всего клиентов
        $sql = "SELECT COUNT(*)
        				FROM i_contr 
        				
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $data_['res']['i_contr_all']=$myrow[0];
        
        $sql = "SELECT COUNT(*)
        				FROM i_contr 
        					WHERE i_contr.data_create > '".date('Y-m-d 00:00:00',strtotime(date('Y-m-01 00:00:00'))-((60*60*24*365)))."' 
        					AND i_contr.data_create < '".date('Y-m-d 00:00:00',strtotime(date('Y-m-01 00:00:00')))."' 
        				
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $data_['res']['i_contr_all_year']=$myrow[0];
        
        
       foreach($data_['res']['x_start'] as $key => $data_start){
        
            $sql = "SELECT  COUNT(i_contr.id)
                            
                            
                    				FROM m_zakaz AS z1, i_contr
                    					WHERE  z1.i_contr_id=i_contr.id
                                        AND z1.id = (SELECT z2.id FROM m_zakaz AS z2 WHERE z2.i_contr_id=i_contr.id AND z2.status!='Отменен' ORDER BY z2.data LIMIT 1)
                                        AND z1.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND z1.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                        AND z1.status!='Отменен'               
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['i_contr_new'][$key]=$myrow[0];
            
            // Постоянные
            $sql = "SELECT  COUNT(i_contr.id)
                            
                            
                    				FROM m_zakaz AS z1, i_contr
                    					WHERE  z1.i_contr_id=i_contr.id
                                        AND z1.id != (SELECT z2.id FROM m_zakaz AS z2 WHERE z2.i_contr_id=i_contr.id AND z2.status!='Отменен' ORDER BY z2.data LIMIT 1)
                                        AND z1.data > '".date('Y-m-d',strtotime($data_start))." 00:00:00' 
                                        AND z1.data < '".date('Y-m-d',strtotime($data_['res']['x_end'][$key]))." 00:00:00'
                                        AND z1.status!='Отменен'               
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['i_contr_old'][$key]=$myrow[0];
            
            
            
        }
        
        
    }
        
    //СТРУКТУРА ПЛАТЕЖЕЙ
    if ($id_=='m_platezi_rashod'){
      $data_['res']['name']=array();
      $data_['res']['id']=array();
      $data_['res']['val']=array();
      
      $sql = "SELECT id, name 
      				FROM i_rashodi
                    WHERE i_rashodi.id IN (SELECT DISTINCT m_platezi.id_z_p_p FROM m_platezi WHERE m_platezi.a_menu_id='100' AND m_platezi.tip='Дебет')
      ";
       
      $mt = microtime(true);
      $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
      $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
      $i=0;
      for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
      {
          $data_['res']['id'][$i]=$myrow[0];
          $data_['res']['name'][$i]=$myrow[1];
          $i++;
      }
        
        foreach($data_['res']['id'] as $key => $id_){
            $sql = "SELECT  SUM(m_platezi.summa)
                            
                            
                    				FROM m_platezi
                    					WHERE  m_platezi.tip='Дебет'
                                        AND m_platezi.a_menu_id='100'
                                        AND m_platezi.id_z_p_p='"._DB($id_)."'
                                        
                                        
                    ";
    
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $data_['res']['val'][$key]=$myrow[0];
       } 
        
    }
    echo json_encode($data_);
}



//Изменить заказ
if ($_t=='change_zakaz'){
    $data_=array();
    $id_=_GP('id_');
    $tip_=_GP('tip_');
    
    if ($tip_=='cancel'){
        $sql_upp = "
        		UPDATE m_zakaz 
        			SET  
        				status='Отменен',
                        data_change='".date('Y-m-d H:i:s')."'
        		
        		WHERE id='"._DB($id_)."'
        ";
        $mt = microtime(true);
        mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
    }
    if ($tip_=='otvet'){
        $sql_upp = "
        		UPDATE m_zakaz 
        			SET  
                        a_admin_otvet_id='"._DB($a_admin_id_cur)."',
                        data_end='".date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))+60*60*1)."',
                        data_change='".date('Y-m-d H:i:s')."'
        		
        		WHERE id='"._DB($id_)."'
        ";
        $mt = microtime(true);
        mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
    }
    if ($tip_=='data_end'){
        $val_=_GP('val_');
        if ($val_!=''){$val_=date('Y-m-d H:i:s',strtotime($val_));}
        $sql_upp = "
        		UPDATE m_zakaz 
        			SET  
                        data_end='"._DB($val_)."',
                        data_change='".date('Y-m-d H:i:s')."'
        		
        		WHERE id='"._DB($id_)."'
        ";
        $mt = microtime(true);
        mysql_query($sql_upp) or die(mysql_error().'<br>'.$sql_upp);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_upp;$data_['_sql']['time'][]=$mt;
    }
    
    
    echo json_encode($data_);
    
}

//Выгрузка контактов на Google
  if ($_t=='contacts_to_google'){
    $data_=array();
    $data_['cur']=_GP('cur');
    $cnt_load=20;
    $cnt_upload=0;//количество выгруженных
    $cnt_ins=0;//количество добавленных
    $cnt_upp=0;//количество измененных
    
    if ((isset($_SESSION['google_drive_access_token']) && $_SESSION['google_drive_access_token'])) {
        $access_token = $_SESSION['google_drive_access_token']['access_token'];
        
        $url = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json&v=3.0&oauth_token='.$access_token;
        $xmlresponse =  curl($url);
        $contacts = json_decode($xmlresponse,true);
  
        //получаем список контактов
    	$google_contacts = array();
    	$google_contacts['name'] = array();
    	$google_contacts['email'] = array();
    	$google_contacts['phone'] = array();
    	$google_contacts['etag'] = array();
    	$google_contacts['link'] = array();
        
        $SQL_TXT='';
        $i=0;
    	if (!empty($contacts['feed']['entry'])) {
    		foreach($contacts['feed']['entry'] as $contact) {
    		   $cnt_upload++;
    		   $name=@$contact['title']['$t'];
    		   $etag=@$contact['gd$etag'];
    		   $link=@$contact['link'][1]['href'];
    		   $email='';
               if (isset($contact['gd$email']) and isset($contact['gd$email'][0]) and isset($contact['gd$email'][0]['address'])){
                    $email=@$contact['gd$email'][0]['address'];
               }
               $phone='';
               if (isset($contact['gd$phoneNumber']) and isset($contact['gd$phoneNumber'][0]) and isset($contact['gd$phoneNumber'][0]['$t'])){
                    $phone=str_replace('+7','8',@$contact['gd$phoneNumber'][0]['$t']);
                    $phone=preg_replace('/[\D]{1,}/s', '',$phone);
               }
               
               
                
               //Тип авторизации - Телефон
               if(isset($_SESSION['a_options']['Регистрация: email-0/sms-1']) and $_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){
                    if ($phone!=''){
                        $SQL_TXT.=", '"._DB($phone)."'";
                    }
                    
               }else{//Тип авторизации - email
                    if ($email!=''){
                        $SQL_TXT.=", '"._DB($email)."'";
                    }
               }
               
    			$google_contacts['etag'][$i]=$etag;
    			$google_contacts['link'][$i]=$link;
    			$google_contacts['name'][$i]=$name;
    			$google_contacts['email'][$i]=$email;
    			$google_contacts['phone'][$i]=$phone;
    				
                $i++;
    		}
    	}
        
            
            $sql = "SELECT  id,  
                            name, 
                            phone, 
                            email, 
                            (SELECT IF(COUNT(m_zakaz.id)>0,GROUP_CONCAT(CONCAT('№',m_zakaz.id,' от ',DATE_FORMAT(m_zakaz.data,'%d.%m.%Y'),' (',m_zakaz.status,'). ',m_zakaz.comments,(SELECT IF(COUNT(*)>0,CONCAT(' / ',r_tip_oborud.name,' ',r_brend.name,' ',r_model.name,' / '),'') FROM r_service, r_model, r_tip_oborud, r_brend WHERE r_model.r_tip_oborud_id=r_tip_oborud.id AND r_model.r_brend_id=r_brend.id AND r_service.r_model_id=r_model.id AND r_service.m_zakaz_id=m_zakaz.id)) SEPARATOR '; '),'') FROM m_zakaz WHERE m_zakaz.i_contr_id=i_contr.id AND m_zakaz.status!='Отменен' ORDER BY FIELD(m_zakaz.`status`,'В обработке','Частично выполнен') DESC, `data_create` DESC) AS comments
            				
                            FROM i_contr 
            					WHERE i_contr.chk_active='1'
                                ORDER BY i_contr.data_create 
                                LIMIT "._DB($data_['cur']).", $cnt_load
                                
            ";
            $i=0;
            $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $site_contacts['id'][$i]=$myrow['id'];
                $site_contacts['name'][$i]=$myrow['name'];
                $myrow['phone']=preg_replace('/[\D]{1,}/s', '',$myrow['phone']);
                if (mb_strlen($myrow['phone'],'utf-8')==10){
                    $txt=$myrow['phone'];
                    if ($txt[0]=='7'){
                        $txt[0]='8';
                        $myrow['phone']=$txt;
                    }
                }
                $site_contacts['phone'][$i]=$myrow['phone'];
                $site_contacts['email'][$i]=$myrow['email'];
                $site_contacts['comments'][$i]=$myrow['comments'];
                $add=0;
                  
                //Тип авторизации - Телефон
                if(isset($_SESSION['a_options']['Регистрация: email-0/sms-1']) and $_SESSION['a_options']['Регистрация: email-0/sms-1']=='1'){
                    if (in_array($myrow['phone'],$google_contacts['phone'])){//обновление
                        $key = array_search($myrow['phone'],$google_contacts['phone']);
                        //удаление записи по ссылке
                        $headers = array(
                                'If-Match: *',
                                'X-HTTP-Method-Override: DELETE',
                                'Authorization: OAuth ' . $access_token);
                        $result =  curl($google_contacts['link'][$key],$headers,'','DELETE');
                        $cnt_upp++;
                    }else{
                        $cnt_ins++;
                    }
                }
                else{//Тип авторизации - email
                    if (in_array($myrow['email'],$google_contacts['email'])){//обновление
                        $key = array_search($myrow['email'],$google_contacts['email']);
                        //удаление записи по ссылке
                        $headers = array(
                                'If-Match: *',
                                'X-HTTP-Method-Override: DELETE',
                                'Authorization: OAuth ' . $access_token);
                        $result =  curl($google_contacts['link'][$key],$headers,'','DELETE');
                        $cnt_upp++;
                    }else{
                        $cnt_ins++;
                    }
                }
                
                //Добавление
                
                    $contact = '<?xml version="1.0" encoding="utf-8"?>'.'<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:gd="http://schemas.google.com/g/2005" xmlns:gContact="http://schemas.google.com/contact/2008">
                        <atom:category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/contact/2008#contact" />
                          <gd:name>
                             <gd:fullName>'.$myrow['name'].'</gd:fullName>
                          </gd:name>
                          <atom:content type="text">'.$myrow['comments'].'</atom:content>';
                          
                          if ($myrow['email']!=''){
                             $contact .= '<gd:email rel="http://schemas.google.com/g/2005#work"
                            primary="true"
                            address="'.$myrow['email'].'" displayName="'.$myrow['email'].'"/>';
                          }
                          $contact .= '<gd:phoneNumber rel="http://schemas.google.com/g/2005#work"
                            primary="true">'.conv_('phone_from_db',$myrow['phone']).'</gd:phoneNumber>
                         <gContact:groupMembershipInfo deleted="false"
                                href="http://www.google.com/m8/feeds/groups/'.@$_SESSION['a_options']['Google Drive - email google для синхронизации контактов и диска'].'/base/6"/>
                        </atom:entry>';
         
                        $headers = array('Host: www.google.com',
                            'Gdata-version: 3.0',
                            'Content-length: ' . strlen($contact),
                            'Content-type: application/atom+xml',
                            'Authorization: OAuth ' . $access_token);
                                                    
                        $url = 'https://www.google.com/m8/feeds/contacts/default/full?v=3.0&oauth_token='.$access_token;
                        $result =  curl($url,$headers,$contact);
                
                
                
                $i++;
            }
        
    }else{
        echo 'No Google auth';exit;
    }
    $data_['cnt_ins']=$cnt_ins;
    $data_['cnt_upp']=$cnt_upp;
    
    $data_['cur']=$data_['cur']+$cnt_load;
    echo json_encode($data_);
    
}
}
?>