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
// Календарь
//**************************************************************

if ($_t=='m_zakaz_calendar'){
    $data_=array();
    
    $sql = "SELECT      m_zakaz.id, 
                        m_zakaz.data_end,
                        m_zakaz.data,
                        m_zakaz.status,
                        m_zakaz.project_name,
                        i_contr.name AS i_contr_name,
                        i_contr.phone AS i_contr_phone,
                        i_contr.email AS i_contr_email,
                        (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                        (SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet,
                        m_zakaz.comments,
                        m_zakaz.a_admin_otvet_id,
                        (SELECT IF(COUNT(*)>0,a_admin.name,'') FROM a_admin WHERE m_zakaz.a_admin_otvet_id=a_admin.id) AS a_admin_name
                        
                        
    				FROM m_zakaz, i_contr, a_admin
                            WHERE m_zakaz.i_contr_id=i_contr.id
                            AND m_zakaz.status NOT IN ('Отменен')
                            
                             GROUP BY m_zakaz.id
                            ORDER BY FIELD(`m_zakaz`.`status`,'В обработке','Частично выполнен','Выполнен'), m_zakaz.data DESC
                            LIMIT 500
    ";
     
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;

    $i=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['aac'][$i]=0;
        if ($myrow['a_admin_otvet_id']==$a_admin_id_cur){
             $data_['aac'][$i]=1;
        }
        $data_['i'][$i]=$myrow['id'];
        $data_['in_'][$i]=$myrow['i_contr_name'];
        $data_['ip'][$i]=$myrow['i_contr_phone'];
        $data_['ie'][$i]=$myrow['i_contr_email'];
        $data_['st'][$i]=$myrow['status'];
        $data_['pn'][$i]=$myrow['project_name'];
        $data_['c'][$i]=$myrow['comments'];
        $data_['an'][$i]=$myrow['a_admin_name'];
        $data_['d1'][$i]=date('Y-m-d',strtotime($myrow['data']));
        $data_['d10'][$i]=date('H:i:s',strtotime($myrow['data']));
        $data_['d2'][$i]='';$data_['d_'][$i]='';$data_['d20'][$i]='';
        if ($myrow['data_end']!='null' and $myrow['data_end']!='0000-00-00 00:00:00' and $myrow['data_end']!=''){
            
            $data_['d2'][$i]=date('Y-m-d',strtotime($myrow['data_end']));
            $data_['d20'][$i]=date('H:i:s',strtotime($myrow['data_end']));
            $data_['d_'][$i]=raznica_po_vremeni(date('Y-m-dTH:i:s'),$myrow['data_end'],'hours');
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


}
?>