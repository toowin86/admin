<?php


$WHERE="";
$TABLE="";
$ORDER="ORDER BY FIELD(`status_dostavki`,'Доработка') DESC, m_zakaz_s_cat.id DESC";
$LIMIT="";
$HAVING_STATUS='';
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
                    s_cat.name AS s_cat_name,
                    m_zakaz_s_cat.price,
                    m_zakaz_s_cat.kolvo,
                    i_contr.name,
                    i_contr.phone,
                    (SELECT IF(COUNT(*)>0,i_city.name,'') FROM i_city WHERE m_dostavka.i_city_id=i_city.id LIMIT 1) AS m_dostavka_city,
                    m_dostavka.adress AS m_dostavka_adress,
                    m_zakaz_s_cat.status_dostavki,
                    s_cat.id AS s_cat_id,
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
                                    ORDER BY s_struktura.sid) AS struktura_name,
                    s_cat.kolvo AS s_cat_kolvo
                    
                    
                        FROM m_zakaz_s_cat, m_zakaz, s_cat, i_contr, m_dostavka $TABLE
                            WHERE m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                            AND m_zakaz_s_cat.s_cat_id=s_cat.id
                            AND i_contr.id=m_zakaz.i_contr_id
                            AND m_dostavka.m_zakaz_id=m_zakaz.id
                            $WHERE
                            $HAVING_STATUS
                            $ORDER
                            
                            
                ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        //echo $sql;
        
        header('Date: '.date('D M j G:i:s T Y'));
        header('Last-Modified: '.date('D M j G:i:s T Y'));
        header('Content-Disposition: attachment;filename="EXPORT_CSV_m_zakaz_s_cat.csv"');
        header('Content-Type: application/vnd.ms-excel; ');
        //header('Content-Type: text/plain; charset: utf-8');
        header('Cache-Control: no-store, no-cache');
      
        ini_set('display_errors', 1); 
        error_reporting(E_ALL);
        echo "\xEF\xBB\xBF";
        
        $script_opt1=_GP('script_opt1',';'); //Разделитель полей
        $script_opt2=_GP('script_opt2','"'); //Значения полей обрамлены
        $script_opt3=_GP('script_opt3','"'); //Символ экранирования
        $script_opt4=_GP('script_opt4',"\n"); //Разделитель строк
        $script_opt5=_GP('script_opt5',true); //Выводить названия столбцов
        
        
        for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
        {
            
            $prop_txt='';
            $sql_prop="SELECT   
                                s_prop.name,
                                s_prop_val.val
                                
                        FROM s_prop, s_prop_val, s_cat_s_prop_val
                            WHERE s_prop.id=s_prop_val.s_prop_id
                            AND s_cat_s_prop_val.id1='"._DB($myrow['s_cat_id'])."'
                            AND s_cat_s_prop_val.id2=s_prop_val.id
                            AND s_prop.chk_main='1'
                            
                            ORDER BY s_prop.sid
                            
                    ";
            $res_prop = mysql_query($sql_prop) or die(mysql_error().'<br>'.$sql_prop);
            for ($myrow_prop = mysql_fetch_array($res_prop),$j=0; $myrow_prop==true; $myrow_prop = mysql_fetch_array($res_prop),$j++)
            {
                if ($prop_txt!=''){$prop_txt.=', ';}
                $prop_txt.=$myrow_prop[0].': '.$myrow_prop[1];
            }
            
            echo $script_opt2.str_replace('"',$script_opt3.'"',$myrow['s_cat_name'].'. '.$prop_txt).$script_opt2.$script_opt1;
            echo $script_opt2.str_replace('"',$script_opt3.'"',$myrow['kolvo']).$script_opt2.$script_opt1;
            
            
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
            
            
            echo $script_opt2.str_replace('"',$script_opt3.'"',implode(", ",$str_name)).$script_opt2;
           
              
              
        //разделитель строк
        if ($script_opt4=='\n'){echo "\n";}
        else{echo $script_opt4;}
        }
        
        
    }

exit;
?>