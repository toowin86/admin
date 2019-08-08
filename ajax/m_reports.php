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

//**************************************************************************************************
//
if ($_t=='m_reports__find'){
    
    $data_=array();
    
    $SQL_='';
    $ORDER='';
    $HAVING='';
    $tip_report=_GP('tip_report');
    
    
    //****************** ОТЧЕТ ПО ПРИБЫЛИ * *************************************************
    if ($tip_report=='Отчет по прибыли'){
        
        $d1=_GP('d1');
            if ($d1==''){echo 'Укажите дату начала отчета!';exit;}
        $d2=_GP('d2');
            if ($d2==''){echo 'Укажите дату окончания отчета!';exit;}
            
        $pay=_GP('pay');//оплата
        if ($pay!='-1'){
            if ($pay=='1'){//не оплачен
                $HAVING.="(pl_-pl_debet)<all_sum ";
            }
            if ($pay=='2'){//оплачен
                $HAVING.="(pl_-pl_debet)>=all_sum ";
            }
        }
    
    
        $sort=_GP('sort');
            if ($sort==''){$ORDER="m_zakaz.id DESC";}
            else{
                $ORDER="m_zakaz."._DB($sort)." DESC";
            }
        if ($d1!=''){
            $d1=$d1.' 00:00:00';
            $SQL_.=" AND m_zakaz."._DB($sort).">='".date('Y-m-d H:i:s',strtotime($d1))."'";
        }
        if ($d2!=''){
            $d2=$d2.' 23:59:59';
            $SQL_.=" AND m_zakaz."._DB($sort)."<='".date('Y-m-d H:i:s',strtotime($d2))."'";
        }
        
        $zp_arr=array();
        //Получаем размер процента по должностям
        $sql = "SELECT  a_admin_i_post.id,a_admin.name, i_zp.val, i_obj.target, a_admin_i_post.data_start, a_admin_i_post.data_end, a_admin_i_post.id1
            				FROM a_admin_i_post, a_admin_i_post_i_zp, i_zp, i_obj, a_admin
                            WHERE a_admin_i_post.id=a_admin_i_post_i_zp.id1
                            AND i_zp.id=a_admin_i_post_i_zp.id2
                            AND i_zp.i_obj_id=i_obj.id
                            AND a_admin_i_post.id1=a_admin.id
                            
            					
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $zp_arr[$myrow[0]]['a_admin'][]=$myrow[1];
            $zp_arr[$myrow[0]]['val'][]=$myrow[2];
            $zp_arr[$myrow[0]]['target'][]=$myrow[3];
            $zp_arr[$myrow[0]]['data_start'][]=$myrow[4];
            $zp_arr[$myrow[0]]['data_end'][]=$myrow[5];
            $zp_arr[$myrow[0]]['a_admin_id'][]=$myrow[6];
        }
        // Процент со всего заказа
        // Фиксированная сумма с заказа: авто
        
        
        // Процент с работы
        // Фиксированная сумма с работы: авто 
        // Фиксированная сумма с работы: вручную
     // print_rf($zp_arr); exit;
        
        if ($ORDER!=''){$ORDER=' ORDER BY '.$ORDER;}
        if ($HAVING!=''){$HAVING=' HAVING '.$HAVING;}
        
        $sql_m_zakaz = "SELECT  m_zakaz.id AS m_zakaz_id,
                                m_zakaz.data,
                                m_zakaz.status,
                                m_zakaz.data_done,
                                
                                i_contr.id AS i_contr_id,
                                i_contr.name AS i_contr_name,
                                
                                a_admin.id AS a_admin_id,
                                a_admin.name AS a_admin_name,
                                (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                                (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                                (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_sum
                                
                                
                                
                        
            				FROM m_zakaz, i_contr, a_admin
                            
            					WHERE m_zakaz.i_contr_id=i_contr.id
                                AND m_zakaz.a_admin_id=a_admin.id
                                AND m_zakaz.status='Выполнен'
                                $SQL_
                                $HAVING
            						$ORDER
         ";
        $mt = microtime(true);
        $res_m_zakaz = mysql_query($sql_m_zakaz) or die(mysql_error().'<br/>'.$sql_m_zakaz);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_zakaz;$data_['_sql']['time'][]=$mt;
        
        $data_['z']['i']=array();
        $data_['z']['d']=array();
        $data_['z']['s']=array();
        $data_['z']['dd']=array();
        $data_['z']['ii']=array();
        $data_['z']['in_']=array();
        $data_['z']['ai']=array();
        $data_['z']['an']=array();
        $data_['z']['t']=array();
        $data_['z']['sz']=array();// сумма заказа 
        $data_['z']['sp']=array();// сумма оплаты
        
        for ($myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$i=0; $myrow_m_zakaz==true; $myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$i++)
        {
            //print_rf($myrow_m_zakaz);
            $data_['z']['sz'][$i]=$myrow_m_zakaz['all_sum'];
            $data_['z']['sp'][$i]=$myrow_m_zakaz['pl_']-$myrow_m_zakaz['pl_debet'];
            $data_['z']['i'][$i]=$myrow_m_zakaz['m_zakaz_id'];
            $data_['z']['d'][$i]=date('d.m.Y H:i:s',strtotime($myrow_m_zakaz['data']));
            $data_['z']['s'][$i]=$myrow_m_zakaz['status'];
            $data_['z']['dd'][$i]='';
            if ($myrow_m_zakaz['data_done']!=''){
                $data_['z']['dd'][$i]=date('d.m.Y H:i:s',strtotime($myrow_m_zakaz['data_done']));
            }
            
            $data_['z']['ii'][$i]=$myrow_m_zakaz['i_contr_id'];
            $data_['z']['in_'][$i]=$myrow_m_zakaz['i_contr_name'];
            $data_['z']['ai'][$i]=$myrow_m_zakaz['a_admin_id'];
            $data_['z']['an'][$i]=$myrow_m_zakaz['a_admin_name'];
            
            
            $data_['z']['t'][$i]['i']=array();
            $data_['z']['t'][$i]['n']=array();
            $data_['z']['t'][$i]['p']=array();
            $data_['z']['t'][$i]['t']=array();
            $data_['z']['t'][$i]['zp']=array();
            $data_['z']['t'][$i]['zk']=array();
            $data_['z']['t'][$i]['ts']=array();//себестоимость товаров
            $data_['z']['t'][$i]['ws']=array();//себестоимость услуг
            $data_['z']['t'][$i]['wa']=array();//работник
            $data_['z']['t'][$i]['wi']=array();//инфо по работам
            $summa_zakaza=0;
            //Перебор по товару в заказе
            $sql_s_cat = "SELECT    s_cat.id,
                                    s_cat.name,
                                    s_cat.price,
                                    s_cat.tip,
                                    m_zakaz_s_cat.price AS m_zakaz_s_cat_price,
                                    m_zakaz_s_cat.kolvo,
                                    
                                    IF(s_cat.tip='Товар',
                                        (SELECT 
                                            IF(COUNT(m_zakaz_s_cat_m_tovar.id)>0,
                                                SUM(m_zakaz_s_cat_m_tovar.kolvo*m_postav_s_cat.price),
                                                0) 
                                            FROM m_zakaz_s_cat_m_tovar, m_tovar, m_postav_s_cat 
                                            WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id 
                                            AND m_tovar.id=m_zakaz_s_cat_m_tovar.id2 
                                            AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id)
                                    ,0) AS sum_tovar,
                                    IF(s_cat.tip='Услуга',
                                        (SELECT 
                                            IF(COUNT(m_zakaz_s_cat_a_admin_i_post.summa)>0,
                                                SUM(m_zakaz_s_cat_a_admin_i_post.summa),
                                                0) 
                                            FROM m_zakaz_s_cat_a_admin_i_post
                                            WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id)
                                    ,0) AS sum_work,
                                    
                                    IF(s_cat.tip='Услуга',
                                        (SELECT 
                                            IF(COUNT(m_zakaz_s_cat_a_admin_i_post.id2)>0,
                                                m_zakaz_s_cat_a_admin_i_post.id2,
                                                0) 
                                            FROM m_zakaz_s_cat_a_admin_i_post
                                            WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id)
                                    ,0) AS a_admin_i_post_id
                                    
                                    
                				FROM s_cat, m_zakaz_s_cat
                					WHERE s_cat.id=m_zakaz_s_cat.s_cat_id
                                    AND m_zakaz_s_cat.m_zakaz_id='"._DB($myrow_m_zakaz['m_zakaz_id'])."'
             ";
            $mt = microtime(true);
            $res_s_cat = mysql_query($sql_s_cat) or die(mysql_error().'<br/>'.$sql_s_cat);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_cat;$data_['_sql']['time'][]=$mt;
            for ($myrow_s_cat = mysql_fetch_array($res_s_cat),$j=0; $myrow_s_cat==true; $myrow_s_cat = mysql_fetch_array($res_s_cat),$j++)
            {
                $data_['z']['t'][$i]['i'][$j]=$myrow_s_cat['id'];
                $data_['z']['t'][$i]['n'][$j]=$myrow_s_cat['name'];
                $data_['z']['t'][$i]['p'][$j]=$myrow_s_cat['price'];
                $data_['z']['t'][$i]['t'][$j]=$myrow_s_cat['tip'];
                $data_['z']['t'][$i]['zp'][$j]=$myrow_s_cat['m_zakaz_s_cat_price'];
                $data_['z']['t'][$i]['zk'][$j]=$myrow_s_cat['kolvo'];
                $data_['z']['t'][$i]['ts'][$j]=$myrow_s_cat['sum_tovar'];//себестоимость товаров
                $itogo=$myrow_s_cat['m_zakaz_s_cat_price']*$myrow_s_cat['kolvo'];
                $summa_zakaza=$summa_zakaza+$itogo;
                
                
                $data_['z']['t'][$i]['ws'][$j]=0;//себестоимость услуг
                $data_['z']['t'][$i]['wa'][$j]='';//работник
                $data_['z']['t'][$i]['wi'][$j]='';//инфо
                
            
                 $summa_work=0;
                 $info_work='';
                 $a_admin_work='';
                 
                     if (isset($zp_arr[$myrow_s_cat['a_admin_i_post_id']]) and isset($zp_arr[$myrow_s_cat['a_admin_i_post_id']]['target'])){
                        foreach ($zp_arr[$myrow_s_cat['a_admin_i_post_id']]['target'] as $key => $target){
                            $a_admin_work=$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['a_admin'][0];
                            if ($target=='Процент с работы'){
                                $summa_=($itogo*$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['val'][$key]/100);
                                $summa_work=$summa_work+($summa_);
                                $info_work.=$a_admin_work.'<br />'.$itogo.' x '.$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['val'][$key].'% = '.$summa_.' рублей. ';
                            }
                            elseif ($target=='Фиксированная сумма с работы: авто'){
                                $summa_work=$summa_work+$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['val'][$key]-0;
                                $info_work.=$a_admin_work.'<br />'.$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['val'][$key].' рублей. ';
                            }
                            elseif ($target=='Фиксированная сумма с работы: вручную'){
                                $summa_work=$summa_work+$myrow_s_cat['sum_work'];
                                $info_work.=$a_admin_work.'<br />Оплата подрядчику: '.$myrow_s_cat['sum_work'].' рублей. ';
                            }
                            else{
                                echo 'Не определен тип: '.$target;exit;
                            }
                            
                        }
                    }
                
                $data_['z']['t'][$i]['ws'][$j]=$summa_work;//себестоимость услуг
                $data_['z']['t'][$i]['wa'][$j]=$a_admin_work;//работник
                $data_['z']['t'][$i]['wi'][$j]=$info_work;//инфо
            }
            
            //Получаем з/п приемщика
            $data_['z']['az'][$i]=0; // з/п приемщика
            foreach($zp_arr as $a_admin_i_post_id => $zp_arr2){
                if (isset($zp_arr[$a_admin_i_post_id]) and isset($zp_arr[$a_admin_i_post_id]['a_admin_id'])){
                    $a_admin_id_arr=$zp_arr[$a_admin_i_post_id]['a_admin_id'];
                    if (in_array($myrow_m_zakaz['a_admin_id'],$a_admin_id_arr)){
                        $key_a_admin=array_search($myrow_m_zakaz['a_admin_id'],$a_admin_id_arr);
                        if (isset($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]) and ($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Фиксированная сумма с заказа: авто' or $zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Процент со всего заказа')){
                            
                            //проверка: устроет ли работник на данную должность на момент заказа
                            if (strtotime($myrow_m_zakaz['data'])>strtotime($zp_arr[$a_admin_i_post_id]['data_start'][$key_a_admin]) 
                                and ($zp_arr[$a_admin_i_post_id]['data_end'][$key_a_admin]=='' 
                                    or strtotime($myrow_m_zakaz['data'])<strtotime($zp_arr[$a_admin_i_post_id]['data_end'][$key_a_admin]))){
                               
                               //Процент с заказа
                               if ($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Процент со всего заказа'){
                                    $data_['z']['az'][$i]=$data_['z']['az'][$i]+($summa_zakaza*$zp_arr[$a_admin_i_post_id]['val'][$key_a_admin]/100);
                               }
                               
                               //Фиксированная сумма с заказа
                               elseif ($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Фиксированная сумма с заказа: авто'){
                                    $data_['z']['az'][$i]=$data_['z']['az'][$i]+$zp_arr[$a_admin_i_post_id]['val'][$key_a_admin];
                               }
                               else{
                                echo 'Не определен тип начисления з/п по заказу';
                               }
                            }
                             
                        }
                    }
                }
            }
            
            
        }
        
        
        //****************************
        //Получаем расходы за период
        $SQL_="";
        if ($d1!=''){
            $SQL_.=" AND m_platezi.data>='".date('Y-m-d H:i:s',strtotime($d1))."'";
        }
        if ($d2!=''){
            $SQL_.=" AND m_platezi.data<='".date('Y-m-d H:i:s',strtotime($d2))."'";
        }
        
        $data_['r']['name']=array();
        $data_['r']['summa']=array();
        $sql = "SELECT id, name
            				FROM i_rashodi 
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $data_['r']['name'][$myrow[0]]=$myrow[1];
        }
        
        $sql = "SELECT  m_platezi.id, 
                        m_platezi.summa, 
                        m_platezi.tip, 
                        i_scheta.name, 
                        m_platezi.comments,
                        m_platezi.data,
                        m_platezi.id_z_p_p
                        
                        
            				FROM m_platezi, i_scheta
            					WHERE m_platezi.a_menu_id='100'
                                AND m_platezi.i_scheta_id=i_scheta.id
                                $SQL_
                                
                                
                                
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            if (!isset($data_['r']['summa'][$myrow['id_z_p_p']])){$data_['r']['summa'][$myrow['id_z_p_p']]=0;}
            $data_['r']['summa'][$myrow['id_z_p_p']]+=$myrow['summa'];
        }
        
        ///Реклама
        
        $data_['rk']['name']=array();
        $data_['rk']['summa']=array();
        $sql = "SELECT id, name
            				FROM i_reklama
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $data_['rk']['name'][$myrow[0]]=$myrow[1];
        }
        
        $sql = "SELECT  m_platezi.id, 
                        m_platezi.summa, 
                        m_platezi.tip, 
                        i_scheta.name, 
                        m_platezi.comments,
                        m_platezi.data,
                        m_platezi.id_z_p_p
                        
                        
            				FROM m_platezi, i_scheta
            					WHERE m_platezi.a_menu_id='40'
                                AND m_platezi.i_scheta_id=i_scheta.id
                                $SQL_
                                
                                
                                
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            if (!isset($data_['rk']['summa'][$myrow['id_z_p_p']])){$data_['rk']['summa'][$myrow['id_z_p_p']]=0;}
            $data_['rk']['summa'][$myrow['id_z_p_p']]+=$myrow['summa'];
        }
       // print_rf($data_['rk']);
    }//end ОТЧЕТ ПО ПРИБЫЛИ
    
    
    //****************** Отчет по товарам * *************************************************
    if ($tip_report=='Отчет по товарам'){
        
        
        $d1=_GP('d1');
            if ($d1==''){echo 'Укажите дату начала отчета!';exit;}
        $d2=_GP('d2');
            if ($d2==''){echo 'Укажите дату окончания отчета!';exit;}
            
        $SQL_='';
       if ($d1!=''){
            $d1=$d1.' 00:00:00';
            $SQL_.=" AND l_s_cat_pop.data_create>='".date('Y-m-d H:i:s',strtotime($d1))."'";
        }
        if ($d2!=''){
            $d2=$d2.' 23:59:59';
            $SQL_.=" AND l_s_cat_pop.data_create<='".date('Y-m-d H:i:s',strtotime($d2))."'";
        }
        
        $data_['i']=array();
        $data_['n']=array();
        $data_['p']=array();
        $data_['img']=array();
        $data_['cnt_']=array();
        $data_['dt']=array();
        $data_['dtt']=array();
            
            
        $sql = "SELECT      s_cat.id, 
                            s_cat.name,
                            s_cat.price,
                            (SELECT IF(COUNT(*)>0,a_photo.img,'') FROM a_photo WHERE s_cat.id=a_photo.row_id AND a_photo.a_menu_id='7' ORDER BY a_photo.sid LIMIT 1) AS img,
                            COUNT(l_s_cat_pop.id) AS cnt_,
                            s_cat.data_create AS dt
                            
        				FROM l_s_cat_pop, s_cat
        					WHERE l_s_cat_pop.s_cat_id=s_cat.id
                            $SQL_
                            
                            GROUP BY s_cat.id
                            ORDER BY cnt_ DESC
                            LIMIT 100
        ";
         
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['i'][$i]=$myrow['id'];
            $data_['n'][$i]=$myrow['name'];
            $data_['p'][$i]=$myrow['price'];
            $data_['img'][$i]=$myrow['img'];
            $data_['cnt_'][$i]=$myrow['cnt_'];
            $data_['dt'][$i]=date('d.m.Y',strtotime($myrow['dt']));
            $data_['dtt'][$i]=round(raznica_po_vremeni($myrow['dt'],date('Y-m-d H:i:s'),'days'),0);
        }
            
    }
    //****************** Отчет по месяцам * *************************************************
    if ($tip_report=='Отчет по месяцам'){
        $year=_GP('year');
        $data_['year']=$year;
        
        $sort='data';
        
        //РАСХОДЫ
        $data_['rash']=array();
        $sql = "SELECT id, name
            				FROM i_rashodi 
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $data_['rash'][$myrow[0]]=$myrow[1];
        }
        
        
        //Получаем размер процента по должностям
        $zp_arr=array();
        $sql = "SELECT  a_admin_i_post.id,a_admin.name, i_zp.val, i_obj.target, a_admin_i_post.data_start, a_admin_i_post.data_end, a_admin_i_post.id1
            				FROM a_admin_i_post, a_admin_i_post_i_zp, i_zp, i_obj, a_admin
                            WHERE a_admin_i_post.id=a_admin_i_post_i_zp.id1
                            AND i_zp.id=a_admin_i_post_i_zp.id2
                            AND i_zp.i_obj_id=i_obj.id
                            AND a_admin_i_post.id1=a_admin.id
                            
            					
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $zp_arr[$myrow[0]]['a_admin'][]=$myrow[1];
            $zp_arr[$myrow[0]]['val'][]=$myrow[2];
            $zp_arr[$myrow[0]]['target'][]=$myrow[3];
            $zp_arr[$myrow[0]]['data_start'][]=$myrow[4];
            $zp_arr[$myrow[0]]['data_end'][]=$myrow[5];
            $zp_arr[$myrow[0]]['a_admin_id'][]=$myrow[6];
        }

        //Перебор по месяцам
        for($m=1;$m<=12;$m++){
            
            $mn=$m+1;//следующий месяц
           
            $m_txt=''; if ($m<=9){$m_txt='0'.$m;}else{$m_txt=''.$m;}
            $d1=date('Y-m-01 00:00:00',strtotime('01.'.$m_txt.'.'.$year));
            
            
            if ($mn==13){$mn=1;$year++;}
            $mn_txt=''; if ($mn<=9){$mn_txt='0'.$mn;}else{$mn_txt=''.$mn;}
            $d2=date('Y-m-01 00:00:00',strtotime('01.'.$mn_txt.'.'.$year));

            $data_['z'][$m]['i']=0;//количество заказов
            
            $data_['z'][$m]['va']=0;// выручка итого
            $data_['z'][$m]['vi']=0;// выручка с товаров 
            $data_['z'][$m]['vw']=0;// выручка с услуг 
            
            $data_['z'][$m]['sa']=0;//себестоимость итого
            $data_['z'][$m]['si']=0;//себестоимость товаров
            $data_['z'][$m]['sw']=0;//себестоимость услуг
            
            $data_['z'][$m]['pa']=0;// прибыль итого
            $data_['z'][$m]['pi']=0;// прибыль товары 
            $data_['z'][$m]['pw']=0;// прибыль услуги
            
            $data_['z'][$m]['zm']=0;//зарплата менеджера
            
            $data_['z'][$m]['r']=array();//расходы по категориям
            $data_['z'][$m]['ra']=0;//итого расходы
            $data_['z'][$m]['rka']=0;//итого реклама
            
            $data_['z'][$m]['ina']=0;//итого вводы на счет
            $data_['z'][$m]['outa']=0;//итого выводы с счета
            $data_['z'][$m]['pch']=0;//чистая прибыль
            //$data_['z'][$m]['posta']=0;//итого закупки
            
            $sql_m_zakaz = "SELECT  m_zakaz.id AS m_zakaz_id,
                                    m_zakaz.data,
                                    i_contr.id AS i_contr_id,
                                    a_admin.id AS a_admin_id,
                                    (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS all_sum
                                    
                                    
                                    
                            
                				FROM m_zakaz, i_contr, a_admin
                                
                					WHERE m_zakaz.i_contr_id=i_contr.id
                                    AND m_zakaz.a_admin_id=a_admin.id
                                    AND m_zakaz.status='Выполнен'
                                    AND m_zakaz.data>='".$d1."'
                                    AND m_zakaz.data<'".$d2."'
             ";
            $mt = microtime(true);
            $res_m_zakaz = mysql_query($sql_m_zakaz) or die(mysql_error().'<br/>'.$sql_m_zakaz);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_m_zakaz;$data_['_sql']['time'][]=$mt;
            
            
            for ($myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$i=0; $myrow_m_zakaz==true; $myrow_m_zakaz = mysql_fetch_array($res_m_zakaz),$i++)
            {
                $data_['z'][$m]['va']=$data_['z'][$m]['va']+$myrow_m_zakaz['all_sum'];
                $data_['z'][$m]['i']++;
                
                $summa_zakaza=0;
                //Перебор по товару в заказе
                $sql_s_cat = "SELECT    s_cat.id,
                                        s_cat.tip,
                                        m_zakaz_s_cat.price AS m_zakaz_s_cat_price,
                                        m_zakaz_s_cat.kolvo,
                                        
                                        
                                        IF(s_cat.tip='Товар',
                                            (SELECT 
                                                IF(COUNT(m_zakaz_s_cat_m_tovar.id)>0,
                                                    SUM(m_zakaz_s_cat_m_tovar.kolvo*m_postav_s_cat.price),
                                                    0) 
                                                FROM m_zakaz_s_cat_m_tovar, m_tovar, m_postav_s_cat 
                                                WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id 
                                                AND m_tovar.id=m_zakaz_s_cat_m_tovar.id2 
                                                AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id)
                                        ,0) AS sum_tovar,
                                        IF(s_cat.tip='Услуга',
                                            (SELECT 
                                                IF(COUNT(m_zakaz_s_cat_a_admin_i_post.summa)>0,
                                                    SUM(m_zakaz_s_cat_a_admin_i_post.summa),
                                                    0) 
                                                FROM m_zakaz_s_cat_a_admin_i_post
                                                WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id)
                                        ,0) AS sum_work,
                                        
                                        IF(s_cat.tip='Услуга',
                                            (SELECT 
                                                IF(COUNT(m_zakaz_s_cat_a_admin_i_post.id2)>0,
                                                    m_zakaz_s_cat_a_admin_i_post.id2,
                                                    0) 
                                                FROM m_zakaz_s_cat_a_admin_i_post
                                                WHERE m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id)
                                        ,0) AS a_admin_i_post_id
                                        
                                        
                    				FROM s_cat, m_zakaz_s_cat
                    					WHERE s_cat.id=m_zakaz_s_cat.s_cat_id
                                        AND m_zakaz_s_cat.m_zakaz_id='"._DB($myrow_m_zakaz['m_zakaz_id'])."'
                 ";
                $mt = microtime(true);
                $res_s_cat = mysql_query($sql_s_cat) or die(mysql_error().'<br/>'.$sql_s_cat);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_s_cat;$data_['_sql']['time'][]=$mt;
                for ($myrow_s_cat = mysql_fetch_array($res_s_cat),$j=0; $myrow_s_cat==true; $myrow_s_cat = mysql_fetch_array($res_s_cat),$j++)
                {
                    
                    
                    
                    $data_['z'][$m]['si']=$data_['z'][$m]['si']+$myrow_s_cat['sum_tovar'];//себестоимость товаров
                    
                    //расчет зарплаты
                    $itogo=$myrow_s_cat['m_zakaz_s_cat_price']*$myrow_s_cat['kolvo'];
                    $summa_zakaza=$summa_zakaza+$itogo;
                    
                
                    ///Выручка с товаров и услуг
                    if ($myrow_s_cat['tip']=='Товар'){
                        $data_['z'][$m]['vi']=$data_['z'][$m]['vi']+($itogo);
                    }
                    if ($myrow_s_cat['tip']=='Услуга'){
                        $data_['z'][$m]['vw']=$data_['z'][$m]['vw']+($itogo);
                    }
                    
                     $summa_work=0;
                     
                         if (isset($zp_arr[$myrow_s_cat['a_admin_i_post_id']]) and isset($zp_arr[$myrow_s_cat['a_admin_i_post_id']]['target'])){
                            foreach ($zp_arr[$myrow_s_cat['a_admin_i_post_id']]['target'] as $key => $target){
                                if ($target=='Процент с работы'){
                                    $summa_=($itogo*$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['val'][$key]/100);
                                    $summa_work=$summa_work+($summa_);
                                }
                                elseif ($target=='Фиксированная сумма с работы: авто'){
                                    $summa_work=$summa_work+$zp_arr[$myrow_s_cat['a_admin_i_post_id']]['val'][$key]-0;
                                }
                                elseif ($target=='Фиксированная сумма с работы: вручную'){
                                    $summa_work=$summa_work+$myrow_s_cat['sum_work'];
                                }
                                else{
                                    echo 'Не определен тип: '.$target;exit;
                                }
                                
                            }
                        }
                    
                    $data_['z'][$m]['sw']=$data_['z'][$m]['sw']+$summa_work;//себестоимость услуг
                }
                
                
                
                
                //Получаем з/п приемщика
                foreach($zp_arr as $a_admin_i_post_id => $zp_arr2){
                    if (isset($zp_arr[$a_admin_i_post_id]) and isset($zp_arr[$a_admin_i_post_id]['a_admin_id'])){
                        $a_admin_id_arr=$zp_arr[$a_admin_i_post_id]['a_admin_id'];
                        if (in_array($myrow_m_zakaz['a_admin_id'],$a_admin_id_arr)){
                            $key_a_admin=array_search($myrow_m_zakaz['a_admin_id'],$a_admin_id_arr);
                            if (isset($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]) and ($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Фиксированная сумма с заказа: авто' or $zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Процент со всего заказа')){
                                
                                //проверка: устроет ли работник на данную должность на момент заказа
                                if (strtotime($myrow_m_zakaz['data'])>strtotime($zp_arr[$a_admin_i_post_id]['data_start'][$key_a_admin]) 
                                    and ($zp_arr[$a_admin_i_post_id]['data_end'][$key_a_admin]=='' 
                                        or strtotime($myrow_m_zakaz['data'])<strtotime($zp_arr[$a_admin_i_post_id]['data_end'][$key_a_admin]))){
                                   
                                   //Процент с заказа
                                   if ($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Процент со всего заказа'){
                                        $data_['z'][$m]['zm']=$data_['z'][$m]['zm']+($summa_zakaza*$zp_arr[$a_admin_i_post_id]['val'][$key_a_admin]/100);
                                   }
                                   
                                   //Фиксированная сумма с заказа
                                   elseif ($zp_arr[$a_admin_i_post_id]['target'][$key_a_admin]=='Фиксированная сумма с заказа: авто'){
                                        $data_['z'][$m]['zm']=$data_['z'][$m]['zm']+$zp_arr[$a_admin_i_post_id]['val'][$key_a_admin];
                                   }
                                   else{
                                    echo 'Не определен тип начисления з/п по заказу';
                                   }
                                }
                                 
                            }
                        }
                    }
                }
                
                
            }
            
            $data_['z'][$m]['sa']=$data_['z'][$m]['si']+$data_['z'][$m]['sw'];//себестоимость итого
            
  
            $data_['z'][$m]['pa']=$data_['z'][$m]['va']-$data_['z'][$m]['sa'];//прибыль итого
            $data_['z'][$m]['pi']=$data_['z'][$m]['vi']-$data_['z'][$m]['si'];//прибыль товаров
            $data_['z'][$m]['pw']=$data_['z'][$m]['vw']-$data_['z'][$m]['sw'];//прибыль услуг
            
            
            //РАСХОДЫ
             $sql = "SELECT  m_platezi.id, 
                            m_platezi.summa, 
                            m_platezi.tip, 
                            i_scheta.name, 
                            m_platezi.comments,
                            m_platezi.data,
                            m_platezi.id_z_p_p
                            
                            
                				FROM m_platezi, i_scheta
                					WHERE m_platezi.a_menu_id='100'
                                    AND m_platezi.i_scheta_id=i_scheta.id
                                    AND m_platezi.data>='".date('Y-m-d H:i:s',strtotime($d1))."'
                                    AND m_platezi.data<'".date('Y-m-d H:i:s',strtotime($d2))."'
                                    
                                    
                                    
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                if (!isset($data_['z'][$m]['r'][$myrow['id_z_p_p']])){$data_['z'][$m]['r'][$myrow['id_z_p_p']]=0;}
                $data_['z'][$m]['r'][$myrow['id_z_p_p']]+=$myrow['summa'];
                $data_['z'][$m]['ra']=$data_['z'][$m]['ra']+$myrow['summa'];
            }
            
            //РЕКЛАМА
            $sql = "SELECT  m_platezi.id, 
                            m_platezi.summa, 
                            m_platezi.tip, 
                            i_scheta.name, 
                            m_platezi.comments,
                            m_platezi.data,
                            m_platezi.id_z_p_p
                            
                            
                				FROM m_platezi, i_scheta
                					WHERE m_platezi.a_menu_id='40'
                                    AND m_platezi.i_scheta_id=i_scheta.id
                                    AND m_platezi.data>='".date('Y-m-d H:i:s',strtotime($d1))."'
                                    AND m_platezi.data<'".date('Y-m-d H:i:s',strtotime($d2))."'
                                    
                                    
                                    
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['z'][$m]['rka']=$data_['z'][$m]['rka']+$myrow['summa'];
                
            }
            //ВВОДЫ НА СЧЕТА
            $sql = "SELECT  m_platezi.id, 
                            m_platezi.summa, 
                            m_platezi.tip, 
                            i_scheta.name, 
                            m_platezi.comments,
                            m_platezi.data,
                            m_platezi.id_z_p_p
                            
                            
                				FROM m_platezi, i_scheta
                					WHERE m_platezi.a_menu_id='105'
                                    AND m_platezi.tip='Кредит'
                                    AND m_platezi.i_scheta_id=i_scheta.id
                                    AND m_platezi.data>='".date('Y-m-d H:i:s',strtotime($d1))."'
                                    AND m_platezi.data<'".date('Y-m-d H:i:s',strtotime($d2))."'
                                    
                                    
                                    
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['z'][$m]['ina']=$data_['z'][$m]['ina']+$myrow['summa'];
            }
            //ВЫВОДЫ С СЧЕТОВ
            $sql = "SELECT  m_platezi.id, 
                            m_platezi.summa, 
                            m_platezi.tip, 
                            i_scheta.name, 
                            m_platezi.comments,
                            m_platezi.data,
                            m_platezi.id_z_p_p
                            
                            
                				FROM m_platezi, i_scheta
                					WHERE m_platezi.a_menu_id='105'
                                    AND m_platezi.tip='Дебет'
                                    AND m_platezi.i_scheta_id=i_scheta.id
                                    AND m_platezi.data>='".date('Y-m-d H:i:s',strtotime($d1))."'
                                    AND m_platezi.data<'".date('Y-m-d H:i:s',strtotime($d2))."'
                                    
                                    
                                    
             ";
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
            {
                $data_['z'][$m]['outa']=$data_['z'][$m]['outa']+$myrow['summa'];
            }
            
            //чистая прибыль
            $data_['z'][$m]['pch']=$data_['z'][$m]['pa']-$data_['z'][$m]['ra']-$data_['z'][$m]['rka']-$data_['z'][$m]['zm'];
            
        }
        
       // unset($data_['_sql']);print_rf($data_);exit;
    }
    
    
    
    //*******************************************************************
    echo json_encode($data_);
}

//************************************************************************************************** 
}else{
    
    $_SESSION['error']['auth_'.date('Y-m-d H:i:s')]='Ошибка авторизации! $login="'.@$_SESSION['admin']['login'].'", pass: "'.@$_SESSION['admin']['password'].'"';
    echo 'Ошибка авторизации!';
    
}

?>