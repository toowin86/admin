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
// ПОИСК ПО ТОВАРАМ В ЗАКАЗАХ
//**************************************************************

if ($_t=='find'){
    $data_=array();
    $data_['i']=array();
    
    $WHERE="";
    $TABLE="";
    $ORDER="ORDER BY FIELD(`status_dostavki`,'Доработка') DESC, m_zakaz_s_cat.id DESC";
    $LIMIT="";
    $HAVING_STATUS='';
    //ФИЛЬТР
    $data_dostavki1=_GP('data_dostavki1');
    $data_dostavki2=_GP('data_dostavki2');
    
    if ($data_dostavki1!='' or $data_dostavki2!=''){

        if ($data_dostavki1!=''){
            
            $WHERE.=" AND m_dostavka.data>='"._DB(date('Y-m-d 00:00:00',strtotime($data_dostavki1)))."'";
        }
        if ($data_dostavki2!=''){
           
            $WHERE.=" AND m_dostavka.data<='"._DB(date('Y-m-d 23:59:59',strtotime($data_dostavki2)))."'";
            
        }
       
        
    }
    $s_struktura_arr=array();
    $s_struktura=_GP('s_struktura');
        if (is_array($s_struktura)){
            $s_struktura_arr=$s_struktura;
        }else{
            if ($s_struktura!=''){
                $s_struktura_arr[0]=$s_struktura;
            }
        }
    if (count($s_struktura_arr)>0){
        $WHERE.=" AND s_cat.id IN (SELECT s_cat_s_struktura.id1 FROM s_cat_s_struktura WHERE s_cat_s_struktura.id2 IN ('".implode("','",$s_struktura_arr)."'))";
    }
        
    $status_dostavki=_GP('status_dostavki');
    $status_dostavki_txt='';
    if (is_array($status_dostavki)){
       $WHERE.=" AND m_zakaz_s_cat.status_dostavki IN ('".implode("','",$status_dostavki)."')";
        
    }else{
        if ($status_dostavki!=''){
            $WHERE.=" AND m_zakaz_s_cat.status_dostavki='".$status_dostavki."'";
        }
        
    }
    
    //ДОСТАВКА
     $dostavka=_GP('dostavka');//1-Доставка (не выполнен), 2-Доставка (выполнен), 3-Самовывоз
     if (!is_array($dostavka)){
        if ($dostavka!=''){
            $m=$dostavka;
            unset($dostavka);
            $dostavka=array();
            $dostavka[0]=$m;
        }else{
            unset($dostavka);
            $dostavka=array();
        }
     }
     foreach($dostavka as $key => $val){
        if ($val=='1'){//1-Доставка (не выполнен)
             //if ($HAVING_STATUS!=''){$HAVING_STATUS.=" AND ";}
             //$HAVING_STATUS.=" (`m_dostavka_data` !='' OR `m_dostavka_i_tk_id`!='' OR `m_dostavka_adress`!='' OR `m_dostavka_city`!='')";
             $WHERE.=" AND m_zakaz.status IN ('Частично выполнен','В обработке') AND m_dostavka.chk_active='1'";
          }
          elseif ($val=='2'){//2-Доставка (выполнен)
             //if ($HAVING_STATUS!=''){$HAVING_STATUS.=" AND ";}
             //$HAVING_STATUS.=" (`m_dostavka_data` !='' OR `m_dostavka_i_tk_id`!='' OR `m_dostavka_adress`!='' OR `m_dostavka_city`!='')";
             $WHERE.=" AND m_zakaz.status='Выполнен' AND m_dostavka.chk_active='1'";
            
          }
          elseif ($val=='3'){//3-Без доставки
             $WHERE.=" AND  m_dostavka.chk_active='0'";
             
            
          }
    }
        
      
      //город
    $city=_GP('city');
    if (!is_array($city)){
        if ($city!=''){
            $m=$city;
            unset($city);
            $city=array();
            $city[0]=$m;
        }else{
            unset($city);
            $city=array();
        }
     }
    if (count($city)>0){
      $WHERE.=" AND m_dostavka.i_city_id IN('".implode("','",$city)."')";
    }
      //ТК
    $i_tk=_GP('i_tk');
    if (!is_array($i_tk)){
        if ($i_tk!=''){
            $m=$i_tk;
            unset($i_tk);
            $i_tk=array();
            $i_tk[0]=$m;
        }else{
            unset($i_tk);
            $i_tk=array();
        }
     }
    if (count($i_tk)>0){
      $WHERE.=" AND m_dostavka.i_tk_id IN('".implode("','",$i_tk)."')";
    }
        
    //Получаем количество товаров
    $sql="SELECT 
                COUNT(m_zakaz_s_cat.id)
                    FROM m_zakaz_s_cat, m_zakaz, m_dostavka
                        WHERE m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                        AND m_zakaz.status IN ('В обработке','Частично выполнен')
                        AND m_dostavka.m_zakaz_id=m_zakaz.id
                        
                    ";
                   
    $mt = microtime(true);
    $res = mysql_query($sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    
    if ($HAVING_STATUS!=''){$HAVING_STATUS=" HAVING ".$HAVING_STATUS;}
    if ($myrow[0]>0){
        //Основной запрос 
        $sql="SELECT 
                    m_zakaz_s_cat.id,
                    m_zakaz.id,
                    m_zakaz.project_name,
                    m_zakaz.data,
                    s_cat.name,
                    m_zakaz_s_cat.price,
                    m_zakaz_s_cat.kolvo,
                    i_contr.name,
                    i_contr.phone,
                    (SELECT IF(COUNT(*)>0,i_city.name,'') FROM i_city WHERE m_dostavka.i_city_id=i_city.id LIMIT 1) AS m_dostavka_city,
                    m_dostavka.adress AS m_dostavka_adress,
                    m_zakaz_s_cat.status_dostavki,
                    s_cat.id,
                    (SELECT IF(COUNT(*)>0,i_tk.name,'') FROM  i_tk WHERE i_tk.id=m_dostavka.i_tk_id LIMIT 1) AS m_dostavka_i_tk,
                    m_dostavka.data AS m_dostavka_data,
                    m_zakaz_s_cat.comments,
                    m_zakaz.data_end,
                    m_dostavka.i_tk_id AS m_dostavka_i_tk_id,
                    m_dostavka.i_city_id AS m_dostavka_city_id,
                    (SELECT IF(COUNT(*)>0,GROUP_CONCAT(s_prop_val.val SEPARATOR '; '),'') FROM s_prop_val, s_cat_s_prop_val, s_prop WHERE s_prop.id=s_prop_val.s_prop_id AND s_prop_val.id=s_cat_s_prop_val.id2 AND s_cat_s_prop_val.id1=s_cat.id AND s_prop.chk_main='1' ORDER BY s_prop.sid LIMIT 10) AS prop_val,
                    (SELECT    GROUP_CONCAT(s_struktura.id SEPARATOR '@@')
                                FROM s_struktura, s_cat_s_struktura
                                    WHERE s_cat_s_struktura.id1=s_cat.id
                                    AND s_cat_s_struktura.id2=s_struktura.id
                                    ORDER BY s_struktura.sid) AS struktura_id,
                    (SELECT    GROUP_CONCAT(s_struktura.name SEPARATOR '@@')
                                FROM s_struktura, s_cat_s_struktura
                                    WHERE s_cat_s_struktura.id1=s_cat.id
                                    AND s_cat_s_struktura.id2=s_struktura.id
                                    ORDER BY s_struktura.sid) AS struktura_name
                    
                    
                        FROM m_zakaz_s_cat, m_zakaz, s_cat, i_contr, m_dostavka $TABLE
                            WHERE m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                            AND m_zakaz_s_cat.s_cat_id=s_cat.id
                            AND i_contr.id=m_zakaz.i_contr_id
                            AND m_dostavka.m_zakaz_id=m_zakaz.id
                            $WHERE
                            $HAVING_STATUS
                            $ORDER
                            
                            $LIMIT
                            
                ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        //echo $sql;
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['i']['id'][$i]=$myrow[0];
            $data_['i']['m_zakaz_id'][$i]=$myrow[1];
            $data_['i']['project_name'][$i]=$myrow[2];
            $data_['i']['data'][$i]=date('d.m.Y',strtotime($myrow[3]));
            $data_['i']['s_cat_name'][$i]=$myrow[4];
            $data_['i']['price'][$i]=$myrow[5];
            $data_['i']['kolvo'][$i]=$myrow[6];
            $data_['i']['i_contr_name'][$i]=$myrow[7];
            $data_['i']['i_contr_phone'][$i]=$myrow[8];
            $data_['i']['city'][$i]=$myrow[9];
            $data_['i']['adress'][$i]=$myrow[10];
            $data_['i']['status'][$i]=$myrow[11];
            $data_['i']['s_cat_id'][$i]=$myrow[12];
            $data_['i']['i_tk_name'][$i]=$myrow[13];
            $data_['i']['dostavka_data'][$i]='';
            if ($myrow[14]==null){$myrow[14]='';}
            if ($myrow[14]=='0000-00-00 00:00:00'){$myrow[14]='';}
            if ($myrow[14]!=''){
                $data_['i']['dostavka_data'][$i]=date('d.m.Y', strtotime($myrow[14]));
            }
            $data_['i']['comments'][$i]=$myrow[15];
            $data_['i']['data_end'][$i]='';
            if ($myrow[16]==null){$myrow[16]='';}
            if ($myrow[16]=='0000-00-00 00:00:00'){$myrow[16]='';}
            if ($myrow[16]!=''){
                $data_['i']['data_end'][$i]=date('d.m.Y', strtotime($myrow[16]));
            }
            $data_['i']['i_tk_id'][$i]=$myrow[17];
            $data_['i']['s_prop_val'][$i]=$myrow[19];
            
            $data_['i']['s_struktura'][$i]=array();
            $str_id=array();
            $str_name=array();
            if ($myrow['struktura_id']!=''){
                if (mb_strstr($myrow['struktura_id'],'@@',false,'utf-8')==true){
                    $str_id=explode('@@',$myrow['struktura_id']);
                    $str_name=explode('@@',$myrow['struktura_name']);
                }
                else{
                    $str_id[0]=$myrow['struktura_id'];
                    $str_name[0]=$myrow['struktura_name'];
                    
                }
            }
            
            foreach($str_id as $key2 => $val2){
                $data_['i']['s_struktura'][$i][$val2]=$str_name[$key2];
            }
              
        }
        
        
    }
    echo json_encode($data_);
}


// ************************************************************
// ПОЛУЧАЕМ НОМЕРА ЗАКАЗОВ
//**************************************************************

if ($_t=='get_id_zakaz'){
    $data_=array();
    $data_['i']=array();
    
    $WHERE="";
    $TABLE="";
    
    //ФИЛЬТР
    $data_dostavki1=_GP('data_dostavki1');
    $data_dostavki2=_GP('data_dostavki2');
    
    if ($data_dostavki1!='' or $data_dostavki2!=''){

        if ($data_dostavki1!=''){
            
            $WHERE.=" AND m_dostavka.data>='"._DB(date('Y-m-d 00:00:00',strtotime($data_dostavki1)))."'";
        }
        if ($data_dostavki2!=''){
           
            $WHERE.=" AND m_dostavka.data<='"._DB(date('Y-m-d 23:59:59',strtotime($data_dostavki2)))."'";
            
        }
       
        
    }
    $s_struktura_arr=array();
    $s_struktura=_GP('s_struktura');
        if (is_array($s_struktura)){
            $s_struktura_arr=$s_struktura;
        }else{
            if ($s_struktura!=''){
                $s_struktura_arr[0]=$s_struktura;
            }
        }
    if (count($s_struktura_arr)>0){
        $WHERE.=" AND s_cat.id IN (SELECT s_cat_s_struktura.id1 FROM s_cat_s_struktura WHERE s_cat_s_struktura.id2 IN ('".implode("','",$s_struktura_arr)."'))";
    }
        
    $status_dostavki=_GP('status_dostavki');
    $status_dostavki_txt='';
    if (is_array($status_dostavki)){
       $WHERE.=" AND m_zakaz_s_cat.status_dostavki IN ('".implode("','",$status_dostavki)."')";
        
    }else{
        if ($status_dostavki!=''){
            $WHERE.=" AND m_zakaz_s_cat.status_dostavki='".$status_dostavki."'";
        }
        
    }
    
    //ДОСТАВКА
     $dostavka=_GP('dostavka');//1-Доставка (не выполнен), 2-Доставка (выполнен), 3-Самовывоз
     if (!is_array($dostavka)){
        if ($dostavka!=''){
            $m=$dostavka;
            unset($dostavka);
            $dostavka=array();
            $dostavka[0]=$m;
        }else{
            unset($dostavka);
            $dostavka=array();
        }
     }
     foreach($dostavka as $key => $val){
        if ($val=='1'){//1-Доставка (не выполнен)
             $WHERE.=" AND m_zakaz.status IN ('Частично выполнен','В обработке') AND m_dostavka.chk_active='1'";
          }
          elseif ($val=='2'){//2-Доставка (выполнен)
             $WHERE.=" AND m_zakaz.status='Выполнен' AND m_dostavka.chk_active='1'";
            
          }
          elseif ($val=='3'){//3-Без доставки
             $WHERE.=" AND  m_dostavka.chk_active='0'";
             
            
          }
    }
        
      
      //город
    $city=_GP('city');
    if (!is_array($city)){
        if ($city!=''){
            $m=$city;
            unset($city);
            $city=array();
            $city[0]=$m;
        }else{
            unset($city);
            $city=array();
        }
     }
    if (count($city)>0){
      $WHERE.=" AND m_dostavka.i_city_id IN('".implode("','",$city)."')";
    }
      //ТК
    $i_tk=_GP('i_tk');
    if (!is_array($i_tk)){
        if ($i_tk!=''){
            $m=$i_tk;
            unset($i_tk);
            $i_tk=array();
            $i_tk[0]=$m;
        }else{
            unset($i_tk);
            $i_tk=array();
        }
     }
    if (count($i_tk)>0){
      $WHERE.=" AND m_dostavka.i_tk_id IN('".implode("','",$i_tk)."')";
    }
        
    //Получаем количество товаров
    $sql="SELECT 
                COUNT(m_zakaz_s_cat.id)
                    FROM m_zakaz_s_cat, m_zakaz, m_dostavka
                        WHERE m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                        AND m_zakaz.status IN ('В обработке','Частично выполнен')
                        AND m_dostavka.m_zakaz_id=m_zakaz.id
                        
                    ";
                   
    $mt = microtime(true);
    $res = mysql_query($sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['id']='';
    $data_['i']=array();
    if ($myrow[0]>0){
        //Основной запрос 
        $sql="SELECT 
                   m_zakaz.id
                    
                    
                    
                        FROM m_zakaz_s_cat, m_zakaz, s_cat, i_contr, m_dostavka $TABLE
                            WHERE m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                            AND m_zakaz_s_cat.s_cat_id=s_cat.id
                            AND i_contr.id=m_zakaz.i_contr_id
                            AND m_dostavka.m_zakaz_id=m_zakaz.id
                            $WHERE
                            
                            
                ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        //echo $sql;
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            $data_['i'][$myrow[0]]=$myrow[0];
        }
        $data_['id']=implode(",",$data_['i']);
        unset($data_['i']);
        
    }
    echo json_encode($data_);
}

//***************************************************************************************************************************
//СТАТУС
if ($_t=='change_status'){
    $data_=array();
    $m_zakaz_s_cat_id=_GP('m_zakaz_s_cat_id');
    $new_status=_GP('val');
    
    $sql = "UPDATE m_zakaz_s_cat 
    			SET  
    				status_dostavki='"._DB($new_status)."'
    		
    		WHERE id='"._DB($m_zakaz_s_cat_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
    echo json_encode($data_);
}
//***************************************************************************************************************************
//Дата доставки
if ($_t=='change_data_dostavki'){
    $data_=array();
    $m_zakaz_s_cat_id=_GP('m_zakaz_s_cat_id');
    $new_data_dostavki=_GP('val');
    if ($new_data_dostavki!=''){
        $new_data_dostavki=date('Y-m-d H:i:s',strtotime($new_data_dostavki));
    }
    
    $sql="SELECT 
                    m_zakaz_s_cat.m_zakaz_id
                    
                        FROM m_zakaz_s_cat
                            WHERE m_zakaz_s_cat.id='"._DB($m_zakaz_s_cat_id)."'
                ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_zakaz_id=$myrow[0];
    
     $sql="SELECT 
                    IF(COUNT(*)>0,m_dostavka.id,'')
                    
                        FROM m_dostavka
                            WHERE m_zakaz_id='"._DB($m_zakaz_id)."'
                ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_dostavka_id=$myrow[0];   
    
    if ($m_dostavka_id==''){
        $sql_ins = "INSERT into m_dostavka (
        				m_zakaz_id
        			) VALUES (
        				'"._DB($m_zakaz_id)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
        $m_dostavka_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
    }
    
    
    $sql = "UPDATE m_dostavka 
    			SET  
    				data='"._DB($new_data_dostavki)."'
    		
    		WHERE id='"._DB($m_dostavka_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
    echo json_encode($data_);
}

//***************************************************************************************************************************
//Дата напоминания
if ($_t=='change_data_end'){
    $data_=array();
    $m_zakaz_s_cat_id=_GP('m_zakaz_s_cat_id');
    $new_data_end=_GP('val');
    if ($new_data_end!=''){
        $new_data_end=date('Y-m-d H:i:s',strtotime($new_data_end));
    }
    
    $sql="SELECT 
                    m_zakaz_s_cat.m_zakaz_id
                    
                        FROM m_zakaz_s_cat
                            WHERE m_zakaz_s_cat.id='"._DB($m_zakaz_s_cat_id)."'
                ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_zakaz_id=$myrow[0];
    
    
    $sql = "UPDATE m_zakaz
    			SET  
    				data_end='"._DB($new_data_end)."'
    		
    		WHERE id='"._DB($m_zakaz_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
    echo json_encode($data_);
}

//***************************************************************************************************************************
//Изменение комментариев
if ($_t=='change_comments'){
    $data_=array();
    $m_zakaz_s_cat_id=_GP('m_zakaz_s_cat_id');
    $new_comments=_GP('val');

    
    
    $sql = "UPDATE m_zakaz_s_cat 
    			SET  
    				comments='"._DB($new_comments)."'
    		
    		WHERE m_zakaz_s_cat.id='"._DB($m_zakaz_s_cat_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
    echo json_encode($data_);
}

//***************************************************************************************************************************
//ТК
if ($_t=='change_i_tk'){
    $data_=array();
    $m_zakaz_s_cat_id=_GP('m_zakaz_s_cat_id');
    $new_i_tk_id=_GP('val');


    
    $sql="SELECT 
                    m_zakaz_s_cat.m_zakaz_id
                    
                        FROM m_zakaz_s_cat
                            WHERE m_zakaz_s_cat.id='"._DB($m_zakaz_s_cat_id)."'
                ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_zakaz_id=$myrow[0];
         $sql="SELECT 
                    IF(COUNT(*)>0,m_dostavka.id,'')
                    
                        FROM m_dostavka
                            WHERE m_zakaz_id='"._DB($m_zakaz_id)."'
                ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $m_dostavka_id=$myrow[0];   
    
    if ($m_dostavka_id==''){
        $sql_ins = "INSERT into m_dostavka (
        				m_zakaz_id
        			) VALUES (
        				'"._DB($m_zakaz_id)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_ins) or die(mysql_error().'<br>'.$sql_ins);
        $m_dostavka_id = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_ins;$data_['_sql']['time'][]=$mt;
    }
    
    
    $sql = "UPDATE m_dostavka 
    			SET  
    				i_tk_id='"._DB($new_i_tk_id)."'
    		
    		WHERE id='"._DB($m_dostavka_id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        
    echo json_encode($data_);
}

// ************************************************************
// АВТОЗАПОЛНЕНИЕ структуры
//**************************************************************

if ($_t=='s_struktura_autocomplete'){

    $data_=array();
   
    $term=_GP('term');
    
    $sql = "SELECT  
                    s_struktura.id,
                    s_struktura.name
    
    				FROM s_struktura
    					WHERE s_struktura.chk_active='1'
                        AND (s_struktura.name LIKE '%"._DB($term)."%'
                        OR s_struktura.id = '"._DB($term)."')
                        ORDER BY s_struktura.sid
                        
    ";
    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
    
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        
     
        $data_['items'][$i]['name']=$myrow[0].'. '.$myrow[1];
        $data_['items'][$i]['text']=$myrow[0].'. '.$myrow[1];
        $data_['items'][$i]['id']=$myrow[0];
        
        
    }
    echo json_encode($data_);
}


}
