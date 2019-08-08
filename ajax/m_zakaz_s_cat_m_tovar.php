<?php

header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

include "../db.php";
    
include "../functions.php";


if (isset($_SESSION['admin']['email']) and isset($_SESSION['admin']['password']) 
and admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])=='1'){


//Получаем id админа
$sql = "SELECT IF(COUNT(*)>0,a_admin.id,''), IF(COUNT(*)>0,a_admin.i_tp_id,'') 
    				FROM a_admin 
    					WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."' 
                        AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
        	"; 
$res = mysql_query($sql);
$myrow = mysql_fetch_array($res);
$a_admin_id_cur=$myrow[0];
$a_admin_i_tp_id_cur=$myrow[1];


$_t=_GP('_t');

//**************************************************************************************************
//Поиск структуры склада
if ($_t=='m_tovar_s_struktura_find'){
    $data_=array();
    $status_tovar=_GP('status_tovar');
    
    $SQL_STAT="";
        if (is_array($status_tovar) and count($status_tovar)==1){
            if ($status_tovar[0]=='В наличии'){
                $SQL_STAT=" AND m_tovar.id NOT IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar)";
            }
            if ($status_tovar[0]=='Продан'){
                $SQL_STAT=" AND m_tovar.id IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar)";
            }
            
        }
    //поиск по названию товара
    $term=_GP('term');
        if ($term!=''){
            $SQL_STAT.=" AND s_cat.name LIKE '%"._DB($term)."%'";
        }
    
    //поставщик
    $i_contr_postav=_GP('m_zakaz_s_cat_m_tovar_i_contr_postav');
    if ($i_contr_postav!=''){
       $SQL_STAT.=" AND m_postav.i_contr_id='"._DB($i_contr_postav)."'"; 
    }
    //покупатель
    $i_contr_pokup=_GP('m_zakaz_s_cat_m_tovar_i_contr_pokup');
    if ($i_contr_pokup!=''){
       $SQL_STAT.=" AND  m_tovar.id IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat, m_zakaz WHERE m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz.i_contr_id='"._DB($i_contr_pokup)."')"; 
    }
    
    $data_['s']=array();
    $data_['s']['i']=array();
    $data_['s']['p']=array();
    $data_['s']['n']=array();
    $data_['s']['c']=array();
    $sql = "SELECT  s_struktura.id, 
                    s_struktura.pid, 
                    s_struktura.name, 
                    (SELECT COUNT(m_tovar.id) 
                            FROM s_cat_s_struktura, s_cat, m_postav_s_cat, m_tovar, m_postav
                                WHERE s_cat_s_struktura.id2=s_struktura.id 
                                AND s_cat.id=s_cat_s_struktura.id1 AND s_cat.tip='Товар'
                                AND s_cat.id=m_postav_s_cat.s_cat_id
                                AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                AND m_postav.id=m_postav_s_cat.m_postav_id
                                
                                
                                
                                $SQL_STAT
                        ) AS cnt_
        				
                        FROM s_struktura
                        ORDER BY s_struktura.sid
                            
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['s']['i'][$i]=$myrow['id'];
        $data_['s']['p'][$i]=$myrow['pid'];
        $data_['s']['n'][$i]=$myrow['name'];
        $data_['s']['c'][$i]=$myrow['cnt_'];
    }
    
    echo json_encode($data_);
}
//*************************************************************************************************************
if ($_t=='m_zakaz_create'){
    $data_=array();
    $m_tovar_id=_GP('m_tovar_id');
    $m_tovar_arr=array();
    if ($m_tovar_id==''){echo 'Не определены id товаров для формирования заказа!';exit;}
    if (mb_strstr($m_tovar_id,',',false,'utf-8')==true){
        $m_tovar_arr=explode(',',$m_tovar_id);
    }else{
        $m_tovar_arr[0]=$m_tovar_id;
    }
    
    //Прверяем наличие контрагента Инвентаризация
    $sql="SELECT 
                IF(COUNT(*)>0,i_contr.id,'')
                    FROM i_contr
                    WHERE i_contr.name='"._DB($_SESSION['a_options']['Название контрагента для инвентаризации'])."'
                    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $i_contr_id=$myrow[0];
    if ($i_contr_id==''){
        $sql_ins = "INSERT into i_contr (
        				chk_active,
        				name,
                        email,
                        phone,
                        password,
                        data_change,
                        i_reklama_id
                        
        			) VALUES (
        				'1',
        				'"._DB($_SESSION['a_options']['Название контрагента для инвентаризации'])."',
                        'test@test.ru',
                        '80000000000',
                        '".rand(100000,999999999)."',
                        '".date('Y-m-d H:i:s')."',
                        '1'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins) or die(mysql_error().'<br/>'.$sql_ins);
        $i_contr_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
    }
    
    $sql_ins = "INSERT into m_zakaz (
                        chk_active,
        				status,
                        data,
                        i_tp_id,
                        a_admin_otvet_id,
                        i_contr_id,
                        i_contr_org_id,
                        project_name,
                        comments,
                        data_done,
                        data_change
                        
        			) VALUES (
                        '1',
        				'Выполнен',
                        '".date('Y-m-d H:i:s')."',
                        '"._DB($a_admin_i_tp_id_cur)."',
                        '"._DB($a_admin_i_tp_id_cur)."',
                        '"._DB($i_contr_id)."',
                        '',
                        'Инвентаризация',
                        'Инвентаризация со склада',
                        '".date('Y-m-d H:i:s')."',
                        '".date('Y-m-d H:i:s')."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins);
        	if (!$res){echo $sql_ins;exit();}
        	else{$data_['m_zakaz_id'] = mysql_insert_id();}
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
    
    //Перебор по товарам
    foreach($m_tovar_arr as $key => $m_tovar_id){
    
        $sql="SELECT 
                    IF(COUNT(*)>0,m_postav_s_cat.s_cat_id,'')
                        FROM m_tovar, m_postav_s_cat
                        WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                        AND m_tovar.id='"._DB($m_tovar_id)."'
                        LIMIT 1
                        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        if ($myrow[0]==''){echo 'Не определен товар!';exit;}
        $s_cat_id=$myrow[0];
        
        $sql_ins = "INSERT into m_zakaz_s_cat (
        				m_zakaz_id,
        				s_cat_id,
                        kolvo,
                        price,
                        comments
        			) VALUES (
        				'"._DB($data_['m_zakaz_id'])."',
        				'"._DB($s_cat_id)."',
        				'1',
        				'0',
                        ''
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
        $m_zakaz_s_cat_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
        
       
        
        //Привязываем тоар к заказу
        $sql_ins = "INSERT into m_zakaz_s_cat_m_tovar (
        				id1,
        				id2,
                        kolvo
        			) VALUES (
        				'"._DB($m_zakaz_s_cat_id)."',
        				'"._DB($m_tovar_id)."',
                        '1'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
        
        
        
    }
    
    echo json_encode($data_);
}
//*************************************************************************************************************
if ($_t=='find_s_cat'){
    $data_=array();
    $data_['s']=array();
    $data_['s']['i']=array();
    $data_['s']['n']=array();
    $data_['s']['p']=array();
    $data_['s']['t']=array();
    $data_['s']['d']=array();
    $data_['s']['b']=array();
    $data_['s']['pi']=array();
    $data_['s']['zi']=array();
    $data_['s']['zd']=array();
    $data_['s']['zp']=array();
    $data_['s']['zk']=array();
    $data_['s']['pr']=array();
    
    
    $s_struktura_id=_GP('s_struktura_id');
    $status_tovar=_GP('status_tovar');
    $SQL_STAT="";
        if (is_array($status_tovar) and count($status_tovar)==1){
            if ($status_tovar[0]=='В наличии'){
                $SQL_STAT=" AND m_tovar.id NOT IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar)";
            }
            if ($status_tovar[0]=='Продан'){
                $SQL_STAT=" AND m_tovar.id IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar)";
            }
            
        }
    
    
    //поставщик
    $i_contr_postav=_GP('m_zakaz_s_cat_m_tovar_i_contr_postav');
    if ($i_contr_postav!=''){
       $SQL_STAT.=" AND m_postav.i_contr_id='"._DB($i_contr_postav)."'"; 
    }
    
    //покупатель
    $i_contr_pokup=_GP('m_zakaz_s_cat_m_tovar_i_contr_pokup');
    if ($i_contr_pokup!=''){
       $SQL_STAT.=" AND  m_tovar.id IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat, m_zakaz WHERE m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz.i_contr_id='"._DB($i_contr_pokup)."')"; 
    }
    
    //поиск по названию товара
    $term=_GP('term');
        if ($term!=''){
            $SQL_STAT.=" AND s_cat.name LIKE '%"._DB($term)."%'";
        }
    
    
    $sql="SELECT    s_cat.id,
                    s_cat.name,
                    m_postav_s_cat.price AS m_postav_price,
                    m_tovar.i_tp_id,
                    m_postav.data,
                    m_tovar.barcode,
                    m_postav.id,
                    m_tovar.id,
                    (SELECT IF(COUNT(*)>0,CONCAT(m_zakaz.id,'@@',m_zakaz.data,'@@',m_zakaz_s_cat.price,'@@',m_zakaz_s_cat.kolvo),'') FROM m_zakaz_s_cat_m_tovar, m_zakaz, m_zakaz_s_cat WHERE m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id LIMIT 1) AS m_zakaz_id,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id  AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val
                    
                    
                FROM s_cat, m_postav_s_cat, m_tovar, m_postav, s_cat_s_struktura
                    WHERE s_cat.id=m_postav_s_cat.s_cat_id
                    AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                    AND m_postav.id=m_postav_s_cat.m_postav_id
                    AND s_cat_s_struktura.id1=s_cat.id
                    AND s_cat_s_struktura.id2='"._DB($s_struktura_id)."'
                    $SQL_STAT
                        ORDER BY s_cat.id
            ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['s']['i'][$i]=$myrow[7];
        $data_['s']['n'][$i]=$myrow[1];
        $data_['s']['p'][$i]=$myrow[2];
        $data_['s']['t'][$i]=$myrow[3];
        $data_['s']['d'][$i]='';
        if ($myrow[4]!='0000-00-00 00:00:00'){$data_['s']['d'][$i]=date('d.m.Y H:i',strtotime($myrow[4]));}
        
        $data_['s']['b'][$i]=$myrow[5];
        $data_['s']['pi'][$i]=$myrow[6];
        $data_['s']['zi'][$i]='';
        $data_['s']['zd'][$i]='';
        $data_['s']['zp'][$i]='';
        $data_['s']['zk'][$i]='';
        if ($myrow['m_zakaz_id']!=''){
            $arr=explode('@@',$myrow['m_zakaz_id']);
            $data_['s']['zi'][$i]=$arr[0];
            $data_['s']['zd'][$i]=date('d.m.Y',strtotime($arr[1]));
            $data_['s']['zp'][$i]=$arr[2];
            $data_['s']['zk'][$i]=$arr[3];
        }
        $data_['s']['pr'][$i]=$myrow[9];
    }
    
    
    echo json_encode($data_);
}
//*************************************************************************************************************
if ($_t=='get_cnt_items_in_tree'){
    $data_=array();
    $id=_GP('id');
    if ($id!=''){
        $id_arr=array();
        if (mb_strstr($id,',',false,'utf-8')==true){
            $id_arr=explode(',',$id);
        }else{
            $id_arr[0]=$id;
        }
        
        $status_tovar=_GP('status_tovar');
        $SQL_STAT="";
        if (is_array($status_tovar) and count($status_tovar)==1){
            if ($status_tovar[0]=='В наличии'){
                $SQL_STAT=" AND m_tovar.id NOT IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar)";
            }
            if ($status_tovar[0]=='Продан'){
                $SQL_STAT=" AND m_tovar.id IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar)";
            }
            
        }
        
    //поиск по названию товара
    $term=_GP('term');
        if ($term!=''){
            $SQL_STAT.=" AND s_cat.name LIKE '%"._DB($term)."%'";
        }
        
    //поставщик
    $i_contr_postav=_GP('m_zakaz_s_cat_m_tovar_i_contr_postav');
    if ($i_contr_postav!=''){
       $SQL_STAT.=" AND m_postav.i_contr_id='"._DB($i_contr_postav)."'"; 
    }
        
    //покупатель
    $i_contr_pokup=_GP('m_zakaz_s_cat_m_tovar_i_contr_pokup');
    if ($i_contr_pokup!=''){
       $SQL_STAT.=" AND  m_tovar.id IN (SELECT m_zakaz_s_cat_m_tovar.id2 FROM m_zakaz_s_cat_m_tovar,m_zakaz_s_cat, m_zakaz WHERE m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz.i_contr_id='"._DB($i_contr_pokup)."')"; 
    }
         $data_['s']=array();
        $data_['s']['i']=array();
        $data_['s']['c']=array();
        $sql = "SELECT  s_struktura.id,
                        (SELECT COUNT(m_tovar.id) 
                                FROM s_cat_s_struktura, s_cat, m_postav_s_cat, m_tovar, m_postav
                                    WHERE s_cat_s_struktura.id2=s_struktura.id 
                                    AND s_cat.id=s_cat_s_struktura.id1 AND s_cat.tip='Товар'
                                    AND s_cat.id=m_postav_s_cat.s_cat_id
                                    AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                                    AND m_postav.id=m_postav_s_cat.m_postav_id
                                    
                                    $SQL_STAT
                            ) AS cnt_
            				
                            FROM s_struktura
                            WHERE s_struktura.id IN ('".implode("','",$id_arr)."')
                            ORDER BY s_struktura.sid
                                
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['s']['i'][$i]=$myrow['id'];
            $data_['s']['c'][$i]=$myrow['cnt_'];
        }
        
    }
    echo json_encode($data_);
}


}
