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
//Поиск платежей
if ($_t=='m_platezi__find'){
    $data_=array();
    
    $kol_load=100;
    $WHERE="";
    $TABLE="";
    $LIMIT="";
    $HAVING_STATUS='';
    
    $TBL_='m_platezi';
    $del_active=_GP('del_active');
        if ($del_active=='2'){
            $TBL_='l_m_platezi_remove';
        }
    
    
    //Догрузка
    $kol_load=100;
    $limit=_GP('limit');
        if ($limit!=''){
            $LIMIT=$limit.', '.$kol_load;
        }
    $i_tp_id=_GP('i_tp_id')-0;
        if ($i_tp_id>0){
            $WHERE.=" AND i_scheta.i_tp_id='"._DB($i_tp_id)."' ";
        }        
    $i_scheta_id=_GP('i_scheta_id')-0;
        if ($i_scheta_id>0){
            $WHERE.=" AND $TBL_.i_scheta_id='"._DB($i_scheta_id)."' ";
        }
    $d1=_GP('d1');
        if ($d1!=''){
            $d1=date('Y-m-d',strtotime($d1)).' 00:00:00';
            $WHERE.=" AND $TBL_.data>='"._DB($d1)."' ";
        }
    $d2=_GP('d2');
        if ($d2!=''){
            $d2=date('Y-m-d',strtotime($d2)).' 23:59:59';
            $WHERE.=" AND $TBL_.data<='"._DB($d2)."' ";
        }
    $tip=_GP('tip',array());//типы платежей
        if (count($tip)>0){
            $WHERE_TIP='';
            foreach($tip as $key => $val){
                if ($val!='-1'){
                    if ($WHERE_TIP!=''){$WHERE_TIP.=' OR ';}
                    $WHERE_TIP.=" $TBL_.a_menu_id='"._DB($val)."' ";
                }
            }
            if ($WHERE_TIP!=''){
                $WHERE.=' AND ('.$WHERE_TIP.')';
            }
        }
    $ORDER='data DESC, id DESC';
    $sort=_GP('sort');
    if ($sort!=''){
        if ($sort=='1'){
            if ($del_active=='1'){//активынй
                $ORDER='data DESC';
            }
            if ($del_active=='2'){//удаленый
                $ORDER='data_del DESC';
            }
            
        }
        if ($sort=='2'){$ORDER='id DESC';}
        
    }
    
    
    
        
    //Догрузка
        $limit=_GP('limit');
        if ($limit!=''){
            $LIMIT=$limit.', '.$kol_load;
        }
        
    //ФИЛЬТР
    $txt=_GP('txt');
    
    if ($txt!=''){
        
        
        
        
        $txt_num=$txt-0;
        //Сортировка по номеру
        $sql = "SELECT COUNT(*)
        				FROM m_postav
        					WHERE m_postav.id='"._DB($txt_num)."'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        if ($myrow[0]>0){
            $ORDER=" FIELD(`m_zakaz`.`id`,"._DB($txt_num).") DESC";
        }
        
        if (($txt-1+1).''===$txt){//Поиск по числу
            $WHERE.=" AND (
                $TBL_.summa='"._DB($txt)."'
                OR $TBL_.id='"._DB($txt)."'
                OR $TBL_.id_z_p_p ='"._DB($txt)."'
                OR $TBL_.comments LIKE '%"._DB($txt)."%'
                OR ($TBL_.id_z_p_p IN (SELECT id
        				FROM a_admin 
        					WHERE name LIKE '%"._DB($txt)."%' )
                    AND $TBL_.a_menu_id='4'
                    )
            
            ) ";
        }else{//Поиск по тексту
            $WHERE.=" AND   ( $TBL_.comments LIKE '%"._DB($txt)."%'
                            OR ( $TBL_.id_z_p_p IN (SELECT id
                				FROM a_admin 
                					WHERE name LIKE '%"._DB($txt)."%' )
                                AND $TBL_.a_menu_id='4'
                                )
                            ) 
            ";
            
            
            
        }
    }
    
        
    if ($ORDER!=''){$ORDER=' ORDER BY '.$ORDER;}
    if ($LIMIT!=''){$LIMIT=' LIMIT '.$LIMIT;}else{$LIMIT=' LIMIT '.$kol_load;}
    if ($HAVING_STATUS!=''){$HAVING_STATUS=' HAVING '.$HAVING_STATUS;}
    
  
    //Получаем сумму кредита
    $sql = "SELECT IF(COUNT(*)>0,SUM($TBL_.summa),0)
                     
				FROM $TBL_, i_scheta, a_admin
                        WHERE $TBL_.i_scheta_id=i_scheta.id
                        AND $TBL_.tip='Кредит'
                        AND $TBL_.a_admin_id=a_admin.id
        					$WHERE
                            $HAVING_STATUS
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res); 
    $data_['kredit']=$myrow[0];
    
    
    //Получаем сумму дебета
    $sql = "SELECT IF(COUNT(*)>0,SUM($TBL_.summa),0)
                     
				FROM $TBL_, i_scheta, a_admin
                        WHERE $TBL_.i_scheta_id=i_scheta.id
                        AND $TBL_.tip='Дебет'
                        AND $TBL_.a_admin_id=a_admin.id
        					$WHERE
                            $HAVING_STATUS
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res); 
    $data_['debet']=$myrow[0];
    
    
    //Получаем общее количество платежей
    $sql = "SELECT DISTINCT $TBL_.id
                     
				FROM $TBL_, i_scheta, a_admin
                        WHERE $TBL_.i_scheta_id=i_scheta.id
                        AND $TBL_.a_admin_id=a_admin.id
        					$WHERE
                            $HAVING_STATUS
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['cnt_']=0;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $data_['cnt_']++;
    }

    
    
    //Задаем начальный массив
    $data_['p']=array();
    $data_['p']['i']=array();
    $data_['p']['d']=array();
    $data_['p']['sh']=array();
    $data_['p']['sk']=array();
    $data_['p']['sd']=array();
    $data_['p']['t']=array();
    $data_['p']['a']=array();
    $data_['p']['c']=array();
    $data_['p']['dc']=array();
    $data_['p']['ii']=array();
    $data_['p']['aa']=array();
    $data_['p']['aai']=array();
    $data_['p']['o']=array();
    $data_['p']['dd']=array();
    
    $DATA_DEL=", '' AS data_del" ;
    if ($del_active=='2'){//удаленный
        $DATA_DEL=", $TBL_.data_del AS data_del";
    }
    
    $sql = "SELECT  $TBL_.id,
                    $TBL_.data,
                    $TBL_.i_scheta_id,
                    $TBL_.summa,
                    $TBL_.tip,
                    $TBL_.a_menu_id,
                    $TBL_.id_z_p_p,
                    IF($TBL_.comments IS NULL,'',$TBL_.comments) AS comments,
                    $TBL_.data_create,
                    $TBL_.a_admin_id,
                    $TBL_.a_admin_id_info,
                    $TBL_.ostatok
                    $DATA_DEL
                    
                    
        				FROM $TBL_, i_scheta, a_admin
                        WHERE $TBL_.i_scheta_id=i_scheta.id
                        AND $TBL_.a_admin_id=a_admin.id
        					$WHERE
                            $HAVING_STATUS
        					$ORDER
                            $LIMIT
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res),$i=0; $myrow==true; $myrow = mysql_fetch_array($res),$i++)
    {
        $data_['p']['i'][$i]=$myrow['id'];
        $data_['p']['d'][$i]=date('d.m.Y H:i',strtotime($myrow['data']));
        $data_['p']['dd'][$i]='';
        if ($myrow['data_del']!=''){
            $data_['p']['dd'][$i]=date('d.m.Y H:i',strtotime($myrow['data_del']));
        }
        $data_['p']['sch'][$i]=$myrow['i_scheta_id'];
        
        if ($myrow['tip']=='Кредит'){
            $data_['p']['sk'][$i]=$myrow['summa']-0;
            $data_['p']['sd'][$i]='';
        }else{
            $data_['p']['sk'][$i]='';
            $data_['p']['sd'][$i]=$myrow['summa']-0;
        }
        $data_['p']['a'][$i]=$myrow['a_menu_id'];
        $data_['p']['ii'][$i]=$myrow['id_z_p_p'];
        $data_['p']['c'][$i]=$myrow['comments'];
        $data_['p']['dc'][$i]=date('d.m.Y H:i',strtotime($myrow['data_create']));
        $data_['p']['aa'][$i]=$myrow['a_admin_id'];
        $data_['p']['aai'][$i]=$myrow['a_admin_id_info'];
        $data_['p']['o'][$i]=$myrow['ostatok']-0;
        if ($myrow['ostatok']==null){$data_['p']['o'][$i]=0;}
    }
    
    
    
    echo json_encode($data_);
}//**************************************************************************************************
//Сохраняем платеж
if ($_t=='m_platezi__save'){
    $data_=array();
    $data_['nomer']=_GP('nomer');
    $summa=_GP('summa')-0;
        if ($summa==0){echo 'Сумма платежа не должна быть равна нулю!';exit;}
        
    $date=_GP('data');
        if ($date==''){echo 'Дата платежа не должна быть пустой!';exit;}
        $date=date('Y-m-d H:i:s',strtotime($date));
        
    $i_scheta=_GP('i_scheta');
        $sql = "SELECT i_scheta.name
        				FROM i_scheta 
        					WHERE i_scheta.id='"._DB($i_scheta)."'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $i_scheta_name=$myrow[0];
        
    $a_admin=_GP('a_admin');
    
        $sql = "SELECT a_admin.name
        				FROM a_admin 
        					WHERE a_admin.id='"._DB($a_admin)."'
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $a_admin_name=$myrow[0];
        
    $pl_comments=_GP('pl_comments');
    $tip=_GP('tip');//Дебет - Кредит
        if ($tip==''){echo 'Не определен тип';exit;}
        $tip_txt='';$tip_txt_cl=' style="color:#090;"';if ($tip=='Дебет'){$tip_txt='-';$tip_txt_cl=' style="color:#900;"';}
    $pl_tip=_GP('pl_tip');
    
    $message='';//Текст сообщения для оповещения администратора


    
    //ПОЛУЧАЕМ id a_menu_id
    $a_menu_id='';$a_menu_txt='';
    if($pl_tip=='0'){//ЗАКАЗЫ
        $id_z_p_p=_GP('m_zakaz_id');
        if ($id_z_p_p==''){echo 'Не определен номер заказа';exit;}
        $a_menu_id='16';
        $txt_name='Заказ'; if($tip=='Дебет'){$txt_name='Возврат по заказу';}
        $a_menu_txt='<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=m_zakaz&nomer='.$id_z_p_p.'">'.$txt_name.' №'.$id_z_p_p.'</a>';
    }
    elseif($pl_tip=='1'){//ПОСТУПЛЕНИЯ
        $id_z_p_p=_GP('m_postav_id');
        if ($id_z_p_p==''){echo 'Не определен номер поступления';exit;}
        $a_menu_id='17';
        $txt_name='Возврат средств от поставщика. Поступление'; if($tip=='Дебет'){$txt_name='Оплата поставщику. Поступление';}
        $a_menu_txt='<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=m_postav&nomer='.$id_z_p_p.'">'.$txt_name.' №'.$id_z_p_p.'</a>';
    }
    elseif($pl_tip=='2'){//з/п
        $id_z_p_p=_GP('a_admin_id');
        if ($id_z_p_p==''){echo 'Не определен работник, для начисления зарплаты';exit;}
        $a_menu_id='4';
        
            $sql = "SELECT a_admin.name
            				FROM a_admin 
            					WHERE a_admin.id='"._DB($id_z_p_p)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $a_admin_name=$myrow[0];
        $txt_name='Внесение работником на счет'; if($tip=='Дебет'){$txt_name='Выдача з/п работнику';}
        
        $a_menu_txt=$txt_name.' '.$a_admin_name.'';
       
    }
    elseif($pl_tip=='3'){//реклама
        $id_z_p_p=_GP('i_reklama_id');
        if ($id_z_p_p==''){echo 'Не определен тип рекламы';exit;}
        $a_menu_id='40';
        
            $sql = "SELECT i_reklama.name
            				FROM i_reklama 
            					WHERE i_reklama.id='"._DB($id_z_p_p)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $i_reklama_name=$myrow[0];
            
        $a_menu_txt='Оплата рекламы '.$i_reklama_name;
    }
    elseif($pl_tip=='4'){//Расходы
        $id_z_p_p=_GP('i_rashodi_id');
        if ($id_z_p_p==''){echo 'Не определен тип расходов';exit;}
        $a_menu_id='100';
        
            $sql = "SELECT i_rashodi.name
            				FROM i_rashodi 
            					WHERE i_rashodi.id='"._DB($id_z_p_p)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $i_rashodi_name=$myrow[0];
            
        $a_menu_txt='Расходы - '.$i_rashodi_name;
        
    }
    elseif($pl_tip=='5'){//Переводы
        $id_z_p_p=_GP('i_scheta_id');
        if ($id_z_p_p==''){echo 'Не определен счет получатель';exit;}
        $a_menu_id='42';
        
        
            $sql = "SELECT i_scheta.name
            				FROM i_scheta 
            					WHERE i_scheta.id='"._DB($id_z_p_p)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $i_scheta_name_2=$myrow[0];
            
        $a_menu_txt='Перевод с счета '.$i_scheta_name.', на счет '.$i_scheta_name_2;
        
        
    }
    elseif($pl_tip=='6'){//ввод/вывод
        $id_z_p_p=_GP('i_inout');
        if ($id_z_p_p==''){echo 'Не определено назначение ввода/вывода';exit;}
        $a_menu_id='105';
        
            $sql = "SELECT i_inout.name
            				FROM i_inout 
            					WHERE i_inout.id='"._DB($id_z_p_p)."'
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $i_inout_name=$myrow[0];
        $txt_name='Ввод на счет'; if($tip=='Дебет'){$txt_name='Вывод с счета';}
        
        $a_menu_txt=$txt_name.' '.$i_inout_name.'';
       
    }
    
    else{
        echo 'Не определен тип $pl_tip';exit;
    }
    if ($a_menu_id==''){
        echo 'Не определен $a_menu_id';exit;
    }
    if ($id_z_p_p==''){
        echo 'Не определена переменная $id_z_p_p';exit;
    }
    
    if ($data_['nomer']==''){//Создаем платеж, если нет
    
        //Получаем остаток по счету после выполнения платежа
        $sql = "SELECT (SELECT SUM(m_platezi.summa)
        				FROM m_platezi 
        					WHERE m_platezi.i_scheta_id='"._DB($i_scheta)."'
                            AND m_platezi.tip='Кредит') AS kredit,
                        (SELECT SUM(m_platezi.summa)
        				FROM m_platezi 
        					WHERE m_platezi.i_scheta_id='"._DB($i_scheta)."'
                            AND m_platezi.tip='Дебет') AS debet		
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $ostatok=$myrow[0]-$myrow[1];
        if ($tip=='Дебет'){$ostatok=$ostatok-$summa;}
        else{$ostatok=$ostatok+$summa;}
        
        if (!isset($a_admin_id_cur)){echo 'Ошибка авторизации. $a_admin_id_cur не определен!';exit;}
        
        //Создаем платеж
        $sql = "INSERT into m_platezi (
                        a_admin_id,
                        data,
        				i_scheta_id,
                        summa,
                        a_menu_id,
                        tip,
        				id_z_p_p,
                        ostatok,
                        a_admin_id_info,
                        comments
                        
                        
        			) VALUES (
                        '"._DB($a_admin)."',
        				'"._DB($date)."',
        				'"._DB($i_scheta)."',
                        '"._DB($summa)."',
                        '"._DB($a_menu_id)."',
                        '"._DB($tip)."',
                        '"._DB($id_z_p_p)."',
                        '"._DB($ostatok)."',
                        '"._DB($a_admin_id_cur)."',
                        '"._DB($pl_comments)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $data_['nomer'] = mysql_insert_id();
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        //если перевод - создаем второй платеж
        if($pl_tip=='5'){
            
            //Получаем остаток по счету после выполнения платежа
            $sql = "SELECT (SELECT SUM(m_platezi.summa)
            				FROM m_platezi 
            					WHERE m_platezi.i_scheta_id='"._DB($id_z_p_p)."'
                                AND m_platezi.tip='Кредит') AS kredit,
                            (SELECT SUM(m_platezi.summa)
            				FROM m_platezi 
            					WHERE m_platezi.i_scheta_id='"._DB($id_z_p_p)."'
                                AND m_platezi.tip='Дебет') AS debet		
            	"; 
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            $myrow = mysql_fetch_array($res);
            $ostatok=$myrow[0]-$myrow[1];
            $ostatok=$ostatok+$summa;
            
            //Добавляем платеж
            $sql = "INSERT into m_platezi (
                            a_admin_id,
                            data,
            				i_scheta_id,
                            summa,
                            a_menu_id,
                            tip,
            				id_z_p_p,
                            a_admin_id_info,
                            comments
                            
            			) VALUES (
                            '"._DB($a_admin)."',
            				'"._DB($date)."',
            				'"._DB($id_z_p_p)."',
                            '"._DB($summa)."',
                            '"._DB($a_menu_id)."',
                            'Кредит',
                            '"._DB($i_scheta)."',
                            '"._DB($a_admin_id_cur)."',
                            '"._DB($pl_comments)."'
            )";
            
            $mt = microtime(true);
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $data_['nomer'] = mysql_insert_id();
            $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        }
        
        $message.=' <hr/><h1 style="font-size:20px;">Добавлен новый платеж</h1>
        <div style="background:#d1ffca;padding:10px;">
            <p'.$tip_txt_cl.'>Сумма: <strong>'.$tip_txt.$summa.'</strong> руб.</p>
            <p>Тип: <strong>'.$a_menu_txt.'</strong>.</p>
            <p>Счет: <strong>'.$i_scheta_name.'</strong>.</p>
            <p>Дата: <strong>'.date('d.m.Y H:i',strtotime($date)).'</strong>.</p>
            <p>Работник: <strong>'.$a_admin_name.'</strong>.</p>
            <p>Комментарии: <strong>'.$pl_comments.'</strong></p>
        </div>
        ';
        
    }else{
        
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
        					WHERE m_platezi.id='"._DB($data_['nomer'])."' 
        				
        	"; 
        
        $mt = microtime(true);
        $res_old_pl = mysql_query($sql_old_pl) or die(mysql_error().'<br/>'.$sql_old_pl);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql_old_pl;$data_['_sql']['time'][]=$mt;
        $myrow_old_pl = mysql_fetch_array($res_old_pl);
        
        $tip_txt_old='';$tip_txt_cl_old=' style="color:#090;"';if ($myrow_old_pl['tip']=='Дебет'){$tip_txt_old='-';$tip_txt_cl_old=' style="color:#900;"';}
        $a_menu_txt_old='';
        if ($myrow_old_pl['a_menu_id']=='16'){$a_menu_txt_old='Заказ';}
        if ($myrow_old_pl['a_menu_id']=='17'){$a_menu_txt_old='Поступление';}
        if ($myrow_old_pl['a_menu_id']=='4'){$a_menu_txt_old='З/П';}
        if ($myrow_old_pl['a_menu_id']=='40'){$a_menu_txt_old='Реклама';}
        if ($myrow_old_pl['a_menu_id']=='100'){$a_menu_txt_old='Расход';}
        if ($myrow_old_pl['a_menu_id']=='42'){$a_menu_txt_old='Перевод';}
        if ($myrow_old_pl['a_menu_id']=='105'){$a_menu_txt_old='Ввод/вывод';}
        
        
        $message.='<h1 style="font-size:20px;">Изменен платеж</h1>
        <hr/>
        <h2>Старый платеж</h2>
        <div style="background:#eee;padding:10px;">
            <p'.$tip_txt_cl_old.'>Сумма: <strong>'.$tip_txt_old.$myrow_old_pl['summa'].'</strong> руб.</p>
            <p>Тип: <strong>'.$a_menu_txt_old.'</strong>.</p>
            <p>Счет: <strong>'.$myrow_old_pl['i_scheta_name'].'</strong>.</p>
            <p>Дата: <strong>'.date('d.m.Y H:i',strtotime($myrow_old_pl['data'])).'</strong>.</p>
            <p>Работник: <strong>'.$myrow_old_pl['a_admin_name'].'</strong>.</p>
            <p>Работник: <strong>'.$myrow_old_pl['comments'].'</strong>.</p>
        </div>
        
        <h2>Новый платеж</h2>
        <div style="background:#d1ffca;padding:10px;">
            <p'.$tip_txt_cl.'>Сумма: <strong>'.$tip_txt.$summa.'</strong> руб.</p>
            <p>Тип: <strong>'.$a_menu_txt.'</strong>.</p>
            <p>Счет: <strong>'.$i_scheta_name.'</strong>.</p>
            <p>Дата: <strong>'.date('d.m.Y H:i',strtotime($date)).'</strong>.</p>
            <p>Работник: <strong>'.$a_admin_name.'</strong>.</p>
            <p>Комментарии: <strong>'.$pl_comments.'</strong></p>
        </div>
        <hr/>
        ';
        
        if (!isset($a_admin_id_cur)){echo 'Ошибка авторизации. $a_admin_id_cur не определен (UPDATE)!';exit;}
    
        $sql = "UPDATE m_platezi 
        			SET  
        				a_admin_id='"._DB($a_admin)."',
        				id_z_p_p='"._DB($id_z_p_p)."',
        				data='"._DB($date)."',
        				i_scheta_id='"._DB($i_scheta)."',
        				summa='"._DB($summa)."',
        				a_menu_id='"._DB($a_menu_id)."',
        				tip='"._DB($tip)."',
                        a_admin_id_info='"._DB($a_admin_id_cur)."'
                        
        		
        		WHERE id='"._DB($data_['nomer'])."'
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        
        ///если был перевод с счета на счет
        if ($myrow_old_pl['a_menu_id']=='42'){
            if ($a_menu_id=='42'){//остался переводом
                $sql = "UPDATE m_platezi 
                			SET  
                				a_admin_id='"._DB($a_admin)."',
                				id_z_p_p='"._DB($id_z_p_p)."',
                				data='"._DB($date)."',
                				i_scheta_id='"._DB($i_scheta)."',
                				summa='"._DB($summa)."',
                				a_menu_id='"._DB($a_menu_id)."',
                				tip='"._DB($tip)."',
                                a_admin_id_info='"._DB($a_admin_id_cur)."'
                                
                		
                		WHERE  m_platezi.a_admin_id='"._DB($myrow_old_pl['a_admin_id'])."'
                                AND m_platezi.data='"._DB($myrow_old_pl['data'])."'
                                AND m_platezi.i_scheta_id='"._DB($myrow_old_pl['id_z_p_p'])."'
                                AND m_platezi.summa='"._DB($myrow_old_pl['summa'])."'
                                AND m_platezi.tip='Кредит'
                                AND m_platezi.a_menu_id='"._DB($myrow_old_pl['a_menu_id'])."'
                                AND m_platezi.id_z_p_p='"._DB($myrow_old_pl['i_scheta_id'])."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
            }
            else{
                $sql = "DELETE 
                			FROM m_platezi 
                				WHERE m_platezi.a_admin_id='"._DB($myrow_old_pl['a_admin_id'])."'
                                AND m_platezi.data='"._DB($myrow_old_pl['data'])."'
                                AND m_platezi.i_scheta_id='"._DB($myrow_old_pl['id_z_p_p'])."'
                                AND m_platezi.summa='"._DB($myrow_old_pl['summa'])."'
                                AND m_platezi.tip='Кредит'
                                AND m_platezi.a_menu_id='"._DB($myrow_old_pl['a_menu_id'])."'
                                AND m_platezi.id_z_p_p='"._DB($myrow_old_pl['i_scheta_id'])."'
                ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;   
            }
        }
        
        
    }
    
    send_mail_smtp(
            $_SESSION['a_options']['email администратора'],
            'Сохранение платежа '.$_SERVER['SERVER_NAME'],
            $message, 
            'Администратору платежей',
            'test@mail.ru',
            'Bot '.$_SERVER['SERVER_NAME'],
            1,
            array(),
            array(),
            0,
            0,
            array()
    );
    
    
    echo json_encode($data_);
}
//**************************************************************************************************
//Сохраняем быстрое изменение
if ($_t=='quick_change_pl'){
    $data_=array();
    
    $nomer=_GP('nomer');
        if ($nomer==''){echo 'Номер платежа не может быть пустым!';exit;}
    $col=_GP('col');
        if ($col==''){echo 'Название столбца col не может быть пустым!';exit;}
    $val=_GP('val');
    
    $names_arr=get_column_names_with_show('m_platezi');
        if (!in_array($col,$names_arr)){echo 'Не обноружен стролбец "'.$col.'"';exit;}
    
    $sql = "SELECT COUNT(*)
    				FROM m_platezi 
    					WHERE m_platezi.id='"._DB($nomer)."' 
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $TBL_='m_platezi';
    if ($myrow[0]==0){
        
    
        $sql = "SELECT COUNT(*)
        				FROM l_m_platezi_remove 
        					WHERE l_m_platezi_remove.id='"._DB($nomer)."' 
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $TBL_='l_m_platezi_remove';
        if ($myrow[0]==0){echo 'Номер платежа не определен!';exit;}
    }
    
    $sql = "UPDATE $TBL_ 
    			SET  
    				"._DB($col)."='"._DB($val)."'
    		
    		WHERE $TBL_.id='"._DB($nomer)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    
    
    echo json_encode($data_);
}

//**************************************************************************************************
//Получаем информацию по счету
if ($_t=='m_platezi_get_schet_summa'){
    $data_=array();
    $data_['sn']=array();
    $data_['sv']=array();
    $i_scheta_id=_GP('i_scheta_id');
    
    if ($i_scheta_id=='-1'){//по всем счетам
        
        $sql = "SELECT id, name
            			FROM i_scheta
         ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
        {
            $i_scheta_id_arr[]=$myrow[0];
            $data_['sn'][$myrow[0]]=$myrow[1];
            if (isset($_SESSION['m_platezi_i_scheta_view_all']) //отображение суммы по счетам в шаблоне
                and isset($_SESSION['m_platezi_i_scheta_view_all'][$a_admin_id_cur])
                and isset($_SESSION['m_platezi_i_scheta_view_all'][$a_admin_id_cur][$myrow[0]])){
                    
                }else{
                    $_SESSION['m_platezi_i_scheta_view_all'][$a_admin_id_cur][$myrow[0]]='1';
                }
                $data_['sv'][$myrow[0]]=$_SESSION['m_platezi_i_scheta_view_all'][$a_admin_id_cur][$myrow[0]];
        }
        
    }else{
        $i_scheta_id_arr[]=$i_scheta_id;
    }
    
    $data_['s']=array();
    //перебор по счетам
    foreach($i_scheta_id_arr as $key => $i_scheta_id){
        
        $sql = "SELECT (SELECT SUM(m_platezi.summa)
        				FROM m_platezi 
        					WHERE m_platezi.i_scheta_id='"._DB($i_scheta_id)."'
                            AND m_platezi.tip='Кредит') AS kredit,
                        (SELECT SUM(m_platezi.summa)
        				FROM m_platezi 
        					WHERE m_platezi.i_scheta_id='"._DB($i_scheta_id)."'
                            AND m_platezi.tip='Дебет') AS debet		
        	"; 
        
        $mt = microtime(true);
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        $myrow = mysql_fetch_array($res);
        $data_['s'][$i_scheta_id]=$myrow[0]-$myrow[1];
        
    }
    echo json_encode($data_);
}
//**************************************************************************************************
//Изменения отображени суммы в шапке (смена выбранных счетов)
if ($_t=='i_scheta_chk_view'){
    $data_=array();
    $nomer=_GP('nomer');
    $chk=_GP('chk');
    if ($chk=='true'){
        $_SESSION['m_platezi_i_scheta_view_all'][$a_admin_id_cur][$nomer]='1';
    }
    else{
        $_SESSION['m_platezi_i_scheta_view_all'][$a_admin_id_cur][$nomer]='0';
    }
    //
    
    echo json_encode($data_);
}
    
//**************************************************************************************************
//Получаем информацию по заказу
if ($_t=='get_info_from_zakaz'){
    $data_=array();
    $nomer=_GP('nomer');
    
    $sql = "SELECT  m_zakaz.data,
                    i_contr.name,
                    (SELECT SUM(m_zakaz_s_cat.kolvo) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS cnt_,
                    (SELECT SUM(m_zakaz_s_cat.kolvo*m_zakaz_s_cat.price) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS summa,
                    (SELECT SUM(m_platezi.summa) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Кредит') AS pl_,
                    (SELECT SUM(m_platezi.summa) FROM m_platezi WHERE m_platezi.id_z_p_p=m_zakaz.id AND m_platezi.a_menu_id='16' AND m_platezi.tip='Дебет') AS pl_debet
                    
                    
    				FROM m_zakaz, i_contr
    					WHERE m_zakaz.id='"._DB($nomer)."'
                        AND i_contr.id=m_zakaz.i_contr_id
    				
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $data_['d']=date('d.m.Y H:i',strtotime($myrow[0]));
    $data_['n']=$myrow[1];
    $data_['c']=$myrow[2];
    $data_['s']=$myrow[3];
    $data_['p']=$myrow[4]-$myrow[5];

    echo json_encode($data_);
}
//**************************************************************************************************
//Получаем информацию по поступлению
if ($_t=='get_info_from_postav'){
    $data_=array();
    $nomer=_GP('nomer');
    
    $sql = "SELECT  m_postav.data,
                    i_contr.name,
                    (SELECT SUM(m_postav_s_cat.kolvo) FROM m_postav_s_cat WHERE m_postav_s_cat.m_postav_id=m_postav.id) AS cnt_,
                    (SELECT SUM(m_postav_s_cat.kolvo*m_postav_s_cat.price) FROM m_postav_s_cat WHERE m_postav_s_cat.m_postav_id=m_postav.id) AS summa,
                    (SELECT SUM(m_platezi.summa) FROM m_platezi WHERE m_platezi.id_z_p_p=m_postav.id AND m_platezi.a_menu_id='17' AND m_platezi.tip='Дебет') AS pl_,
                    (SELECT SUM(m_platezi.summa) FROM m_platezi WHERE m_platezi.id_z_p_p=m_postav.id AND m_platezi.a_menu_id='17' AND m_platezi.tip='Кредит') AS pl_debet
                    
                    
    				FROM m_postav, i_contr
    					WHERE m_postav.id='"._DB($nomer)."'
                        AND i_contr.id=m_postav.i_contr_id
    				
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $data_['d']=date('d.m.Y H:i',strtotime($myrow[0]));
    $data_['n']=$myrow[1];
    $data_['c']=$myrow[2];
    $data_['s']=$myrow[3];
    $data_['p']=$myrow[4]-$myrow[5];

    echo json_encode($data_);
}
//**************************************************************************************************
//Получаем информацию по з/п работника
if ($_t=='get_info_from_zp'){
    $data_=array();
    $a_admin_id=_GP('a_admin_id');
        if ($a_admin_id==''){echo 'Не определен id работника!';exit;}
    $a_admin_i_post_id=_GP('a_admin_i_post_id');
    
    //Получаем сумму выданную работнику
    $sql = "SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0)
    				FROM m_platezi 
    					WHERE m_platezi.a_menu_id='4'
                        AND m_platezi.id_z_p_p='"._DB($a_admin_id)."'
                        AND m_platezi.tip='Дебет'
    				
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['summa_debet']=$myrow[0];
    
    
    //Получаем сумму положенную работником на счет
    $sql = "SELECT IF(COUNT(*)>0,SUM(m_platezi.summa),0)
    				FROM m_platezi 
    					WHERE m_platezi.a_menu_id='4'
                        AND m_platezi.id_z_p_p='"._DB($a_admin_id)."'
                        AND m_platezi.tip='Кредит'
    				
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    $data_['summa_kredit']=$myrow[0];
    
    //Получаем все должности данного работника
    $zp_arr=array();$data_['i_post']=array();
    $sql = "SELECT  
                    i_post.id, 
                    i_post.name, 
                    a_admin_i_post.id AS a_admin_i_post_id, 
                    a_admin_i_post.data_start, 
                    a_admin_i_post.data_end,
                    i_zp.id AS i_zp_id,
                    i_zp.val AS i_zp_val,
                    i_obj.obj AS i_obj_obj,
                    i_obj.target AS i_obj_target
        			
                    	FROM a_admin_i_post, i_post, a_admin_i_post_i_zp, i_zp, i_obj
        					WHERE i_post.id=a_admin_i_post.id2
                            AND a_admin_i_post.id1='"._DB($a_admin_id)."'
                            AND a_admin_i_post_i_zp.id1=a_admin_i_post.id
                            AND a_admin_i_post_i_zp.id2=i_zp.id
                            AND i_zp.i_obj_id=i_obj.id
     ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $a_admin_i_post_id=$myrow['a_admin_i_post_id'];
        $data_['i_post'][$a_admin_i_post_id]=$myrow[1];
        $zp_arr[$a_admin_i_post_id]['data_start']=$myrow['data_start'];
        $zp_arr[$a_admin_i_post_id]['data_end']=$myrow['data_end'];
        $zp_arr[$a_admin_i_post_id]['zp_tar'][$myrow['i_zp_id']]=$myrow['i_obj_target'];
        $zp_arr[$a_admin_i_post_id]['zp_val'][$myrow['i_zp_id']]=$myrow['i_zp_val'];
        $data_['i_obj_target'][$a_admin_i_post_id][$myrow['i_zp_id']]=$myrow['i_obj_target'];
        $data_['i_zp_val'][$a_admin_i_post_id][$myrow['i_zp_id']]=$myrow['i_zp_val'];
    }
    
    //Считаем заработанные деньги по каждой из должностей
    foreach($zp_arr as $a_admin_i_post_id => $zp_arr2){//перебор по должностям
    
        
        
        $data_start=$zp_arr[$a_admin_i_post_id]['data_start'];
        $data_end=$zp_arr[$a_admin_i_post_id]['data_end'];
        
        foreach($zp_arr[$a_admin_i_post_id]['zp_tar'] as $i_zp_id => $i_obj_target){//Перебор по начислениям средств
            
            //print_rf($i_obj_target);
            
            $SQL_DATA='';
            $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=0;
            $i_zp_val=$zp_arr[$a_admin_i_post_id]['zp_val'][$i_zp_id];
            
            //ЗАРПЛАТА СЧИТАЕТСЯ ТОЛЬКО С ВЫПОЛНЕННЫХ ЗАКАЗОВ
          
            if ($i_obj_target=='Процент со всего заказа'){
                if ($data_start!=''){$SQL_DATA.="AND m_zakaz.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_zakaz.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                
                $sql = "SELECT  IF(COUNT(*)>0,SUM(m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo),0)
            				FROM m_zakaz_s_cat, m_zakaz
            					WHERE  m_zakaz.a_admin_id='"._DB($a_admin_id)."'
                                AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                                AND m_zakaz.status='Выполнен'
                                $SQL_DATA
                                
            	";  

                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                
                $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0]*$i_zp_val/100;
            }
            if ($i_obj_target=='Процент с маржи заказа'){
                
               //**************************************************************************//
               //echo 'Процент с маржи заказа - НЕ РЕАЛИЗОВАН!';exit;
                if ($data_start!=''){$SQL_DATA.="AND m_zakaz.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_zakaz.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                $all_sum_0=0;
                //ПЕРЕБОР ПО ВСЕМ ЗАКАЗАМ
                /*
                $sql = "SELECT  m_zakaz.id,
                                (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) AS sum_zakaz,
                                (SELECT IF(COUNT(*)>0,SUM(m_postav_s_cat.price*m_zakaz_s_cat_m_tovar.kolvo),0) FROM m_zakaz_s_cat,m_zakaz_s_cat_m_tovar,m_tovar,m_postav_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id) AS all_zakup,
                                (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat_a_admin_i_post.summa),0) FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id) AS sum_work_podr,
                    			(SELECT IF(COUNT(*)>0,
                                                GROUP_CONCAT(DISTINCT CONCAT((m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo*i_zp.val/100),'=',m_zakaz_s_cat_a_admin_i_post.id) SEPARATOR ', ' )
                                
                                                ,0) FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, a_admin_i_post, a_admin_i_post_i_zp, i_zp, i_obj WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id AND a_admin_i_post.id=m_zakaz_s_cat_a_admin_i_post.id2 AND a_admin_i_post_i_zp.id1=a_admin_i_post.id AND a_admin_i_post_i_zp.id2=i_zp.id AND i_zp.i_obj_id=i_obj.id AND i_obj.target='Процент с работы') AS sum_work_other
                                ,(SELECT IF(COUNT(*)>0,
                                                GROUP_CONCAT(DISTINCT CONCAT((i_zp.val),'=',m_zakaz_s_cat_a_admin_i_post.id) SEPARATOR ', ' )
                                
                                                ,0) FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, a_admin_i_post, a_admin_i_post_i_zp, i_zp, i_obj WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id AND a_admin_i_post.id=m_zakaz_s_cat_a_admin_i_post.id2 AND a_admin_i_post_i_zp.id1=a_admin_i_post.id AND a_admin_i_post_i_zp.id2=i_zp.id AND i_zp.i_obj_id=i_obj.id AND i_obj.target='Фиксированная сумма с работы: авто') AS sum_work_other2
                                	FROM m_zakaz 
                    					WHERE m_zakaz.status='Выполнен'
                                        AND m_zakaz.a_admin_id='"._DB($a_admin_id)."'
                                        $SQL_DATA
                 ";*/
                $sql = "SELECT  m_zakaz.id,
                                (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo),0) FROM m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id) 
                                -
                                (
                                    (SELECT IF(COUNT(*)>0,SUM(m_postav_s_cat.price*m_zakaz_s_cat_m_tovar.kolvo),0) FROM m_zakaz_s_cat,m_zakaz_s_cat_m_tovar,m_tovar,m_postav_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id) 
                                    +
                                    (SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat_a_admin_i_post.summa),0) FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id) 
                                    +
                        			(SELECT IF(COUNT(*)>0,
                                                    SUM(m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo*i_zp.val/100)
                                    
                                                    ,0) FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, a_admin_i_post, a_admin_i_post_i_zp, i_zp, i_obj WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id AND a_admin_i_post.id=m_zakaz_s_cat_a_admin_i_post.id2 AND a_admin_i_post_i_zp.id1=a_admin_i_post.id AND a_admin_i_post_i_zp.id2=i_zp.id AND i_zp.i_obj_id=i_obj.id AND i_obj.target='Процент с работы')
                                    +
                                    (SELECT IF(COUNT(*)>0,
                                                    SUM(i_zp.val)
                                    
                                                    ,0) FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, a_admin_i_post, a_admin_i_post_i_zp, i_zp, i_obj WHERE m_zakaz_s_cat.m_zakaz_id=m_zakaz.id AND m_zakaz_s_cat_a_admin_i_post.id1=m_zakaz_s_cat.id AND a_admin_i_post.id=m_zakaz_s_cat_a_admin_i_post.id2 AND a_admin_i_post_i_zp.id1=a_admin_i_post.id AND a_admin_i_post_i_zp.id2=i_zp.id AND i_zp.i_obj_id=i_obj.id AND i_obj.target='Фиксированная сумма с работы: авто') 
                                                    
                                ) AS marga,
                                m_zakaz.data_change, 
                                m_zakaz.data_create
                                	
                                    FROM m_zakaz
                					WHERE m_zakaz.a_admin_id='"._DB($a_admin_id)."'
                                    AND m_zakaz.status='Выполнен'
                                    $SQL_DATA
                 ";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                
                $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id]=array();
                for ($myrow = mysql_fetch_array($res),$ii=0; $myrow==true; $myrow = mysql_fetch_array($res),$ii++)
                {
                    $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[1]*$i_zp_val/100;
                    $all_sum_0=$all_sum_0+$data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii];
                    $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[2]));
                    $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[3]));
                    $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[0];
                }
               $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$all_sum_0;
                //print_rf($data_);exit;
            }
            if ($i_obj_target=='Процент с работы'){
                if ($data_start!=''){$SQL_DATA.="AND m_zakaz.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_zakaz.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                
                $sql = "SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo),0)
            				FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, m_zakaz
            					WHERE m_zakaz_s_cat.id=m_zakaz_s_cat_a_admin_i_post.id1
                                AND m_zakaz_s_cat_a_admin_i_post.id2='"._DB($a_admin_i_post_id)."'
                                AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                                AND m_zakaz.status='Выполнен'
                                $SQL_DATA
                                
            	";  

                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                
                $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0]*$i_zp_val/100;
                
                $sql = "SELECT m_zakaz.id, SUM(m_zakaz_s_cat.price*m_zakaz_s_cat.kolvo), m_zakaz.data_change, m_zakaz.data_create
                				FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, m_zakaz
                					WHERE m_zakaz_s_cat.id=m_zakaz_s_cat_a_admin_i_post.id1
                                        AND m_zakaz_s_cat_a_admin_i_post.id2='"._DB($a_admin_i_post_id)."'
                                        AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                                        AND m_zakaz.status='Выполнен'
                                        $SQL_DATA
                                        GROUP BY m_zakaz.id
                                        ORDER BY m_zakaz.data_change DESC
                ";
                 
                $mt = microtime(true);
                $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id]=array();
                for ($myrow = mysql_fetch_array($res),$ii=0; $myrow==true; $myrow = mysql_fetch_array($res),$ii++)
                {
                    $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[1]*$i_zp_val/100;
                    $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[2]));
                    $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[3]));
                    $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[0];
                }
                
            }
            if ($i_obj_target=='Фиксированная сумма с заказа: авто'){
                
                //echo $i_obj_target;
                if ($data_start!=''){$SQL_DATA.="AND m_zakaz.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_zakaz.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                
                $sql = "SELECT IF(COUNT(*)>0,COUNT(m_zakaz.id),0)
            				FROM m_zakaz
            					WHERE  m_zakaz.a_admin_id='"._DB($a_admin_id)."'
                                AND m_zakaz.status='Выполнен'
                                $SQL_DATA
                                
            	";  
//echo $sql;
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                
                $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0]*$i_zp_val;
                
                
                   $sql = "SELECT m_zakaz.id, IF(COUNT(*)>0,COUNT(m_zakaz.id),0), m_zakaz.data_change, m_zakaz.data_create
                    				FROM m_zakaz
                    					WHERE  m_zakaz.a_admin_id='"._DB($a_admin_id)."'
                                            AND m_zakaz.status='Выполнен'
                                            $SQL_DATA
                                            GROUP BY m_zakaz.id
                                            ORDER BY m_zakaz.data_change DESC
                    ";
                     
                    $mt = microtime(true);
                    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id]=array();
                    for ($myrow = mysql_fetch_array($res),$ii=0; $myrow==true; $myrow = mysql_fetch_array($res),$ii++)
                    {
                        $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii]=$i_zp_val;
                        $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[2]));
                        $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[3]));
                        $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[0];
                    }
                
            }
            if ($i_obj_target=='Фиксированная сумма с поступления: авто'){
                if ($data_start!=''){$SQL_DATA.="AND m_postav.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_postav.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                
                $sql = "SELECT IF(COUNT(*)>0,COUNT(m_postav.id),0)
            				FROM m_postav
            					WHERE  m_postav.a_admin_id='"._DB($a_admin_id)."'
                                AND m_postav.status='Доставлен'
                                $SQL_DATA
                                
            	";  

                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                
                $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0]*$i_zp_val;
                
                
                
                
                   $sql = "SELECT m_postav.id, IF(COUNT(*)>0,COUNT(m_postav.id),0), m_postav.data_change, m_postav.data_create
                    				FROM m_postav
                        					WHERE  m_postav.a_admin_id='"._DB($a_admin_id)."'
                                            AND m_postav.status='Доставлен'
                                            $SQL_DATA
                                            GROUP BY m_postav.id
                                            ORDER BY m_postav.data_change DESC
                    ";
                     
                    $mt = microtime(true);
                    $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id]=array();
                    for ($myrow = mysql_fetch_array($res),$ii=0; $myrow==true; $myrow = mysql_fetch_array($res),$ii++)
                    {
                        $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii]=$i_zp_val;
                        $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[2]));
                        $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[3]));
                        $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[0];
                    }
                    
                    
            }
            if ($i_obj_target=='Процент с маржи проданного товара из поступления: авто'){
                if ($data_start!=''){$SQL_DATA.="AND m_postav.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_postav.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                


                   $sql = "SELECT  
                                    SUM(m_zakaz_s_cat.price)-SUM(m_postav_s_cat.price)
                                   
                                    
                    				FROM m_postav, m_tovar, m_postav_s_cat, m_zakaz_s_cat_m_tovar, m_zakaz_s_cat 
                        					WHERE  m_postav.a_admin_id='"._DB($a_admin_id)."'
                                            AND m_postav.status='Доставлен'
                                            AND m_zakaz_s_cat.id=m_zakaz_s_cat_m_tovar.id1 
                                            AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id 
                                            AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id 
                                            AND m_postav_s_cat.m_postav_id=m_postav.id
                                           
                                            $SQL_DATA
                                           
                    ";
                     
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $myrow = mysql_fetch_array($res);
        

                    //итого
                    $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0]*$i_zp_val/100;
                
                
                
                
                   $sql = "SELECT   m_postav.id, 
                                    SUM(m_zakaz_s_cat.price) AS sum_, 
                                    SUM(m_postav_s_cat.price) AS sebest_, 
                                    m_postav.data_change, 
                                    m_postav.data_create
                                    
                    				FROM m_postav, m_tovar, m_postav_s_cat, m_zakaz_s_cat_m_tovar, m_zakaz_s_cat 
                        					WHERE  m_postav.a_admin_id='"._DB($a_admin_id)."'
                                            AND m_postav.status='Доставлен'
                                            AND m_zakaz_s_cat.id=m_zakaz_s_cat_m_tovar.id1 
                                            AND m_zakaz_s_cat_m_tovar.id2=m_tovar.id 
                                            AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id 
                                            AND m_postav_s_cat.m_postav_id=m_postav.id
                                            $SQL_DATA
                                            GROUP BY m_postav.id
                                            ORDER BY m_postav.data_change DESC
                    ";
                     
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
                    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id]=array();
                    $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id]=array();
                    
                    for ($myrow = mysql_fetch_array($res),$ii=0; $myrow==true; $myrow = mysql_fetch_array($res),$ii++)
                    {
                        
                        $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii]=($myrow['sum_']-$myrow['sebest_'])*$i_zp_val/100;
                        $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[2]));
                        $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[3]));
                        $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[0];
                    }
                    
                    
            }
            if ($i_obj_target=='Фиксированная сумма с работы: авто'){
                
                if ($data_start!=''){$SQL_DATA.="AND m_zakaz.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_zakaz.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                
                
                $sql = "SELECT IF(COUNT(*)>0,COUNT(m_zakaz_s_cat.id),0)
            				FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, m_zakaz
            					WHERE m_zakaz_s_cat.id=m_zakaz_s_cat_a_admin_i_post.id1
                                AND m_zakaz_s_cat_a_admin_i_post.id2='"._DB($a_admin_i_post_id)."'
                                AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                                AND m_zakaz.status='Выполнен'
                                $SQL_DATA
            	";  

                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                
                $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0]*$i_zp_val;
                
            }
            if ($i_obj_target=='Фиксированная сумма с работы: вручную'){//используем данные из таблицы m_zakaz_s_cat_a_admin_i_post
                if ($data_start!=''){$SQL_DATA.="AND m_zakaz.data>='"._DB($zp_arr[$a_admin_i_post_id]['data_start'])."'";}
                if ($data_end!=''){$SQL_DATA.="AND m_zakaz.data<='"._DB($zp_arr[$a_admin_i_post_id]['data_end'])."'";}
                
                $sql = "SELECT IF(COUNT(*)>0,SUM(m_zakaz_s_cat_a_admin_i_post.summa),0)
            				FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, m_zakaz
            					WHERE m_zakaz_s_cat.id=m_zakaz_s_cat_a_admin_i_post.id1
                                AND m_zakaz_s_cat_a_admin_i_post.id2='"._DB($a_admin_i_post_id)."'
                                AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                                AND m_zakaz.status='Выполнен'
                                $SQL_DATA
                                
            	";
                $mt = microtime(true);
                $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $myrow = mysql_fetch_array($res);
                
                $data_['zp_cur_zp_summ'][$a_admin_i_post_id][$i_zp_id]=$myrow[0];
                  
                  
                  
                
                $sql = "SELECT m_zakaz.id, SUM(m_zakaz_s_cat_a_admin_i_post.summa), m_zakaz.data_change, m_zakaz.data_create
                				FROM m_zakaz_s_cat_a_admin_i_post, m_zakaz_s_cat, m_zakaz
                					WHERE m_zakaz_s_cat.id=m_zakaz_s_cat_a_admin_i_post.id1
                                    AND m_zakaz_s_cat_a_admin_i_post.id2='"._DB($a_admin_i_post_id)."'
                                    AND m_zakaz.id=m_zakaz_s_cat.m_zakaz_id
                                    AND m_zakaz.status='Выполнен'
                                    $SQL_DATA
                                        GROUP BY m_zakaz.id
                                        ORDER BY m_zakaz.data_change DESC
                ";
                 
                $mt = microtime(true);
                $res = mysql_query($sql);if (!$res){echo $sql;exit();}
                $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id]=array();
                $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id]=array();
                for ($myrow = mysql_fetch_array($res),$ii=0; $myrow==true; $myrow = mysql_fetch_array($res),$ii++)
                {
                    $data_['zp_cur_zp_zakaz'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[1];
                    $data_['zp_cur_zp_dt'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[2]));
                    $data_['zp_cur_zp_dtc'][$a_admin_i_post_id][$i_zp_id][$ii]=date('d.m.Y H:i:s',strtotime($myrow[3]));
                    $data_['zp_cur_zp_id'][$a_admin_i_post_id][$i_zp_id][$ii]=$myrow[0];
                }
            }
            
            //echo '<br />'.$i_obj_target.' = '.$i_zp_val.'<br />';
        }
    }
    
    
    echo json_encode($data_);
}
//************************************************************************************************** 
//отмена платежа
if ($_t=='pl_remove'){
    $data_=array();
    $id=_GP('id');
        if ($id==''){echo 'id платежа не должен быть пустым';exit;}
    $pass=_GP('pass');
        if ($pass==''){echo 'Пароль не должен быть пустым';exit;}
    
    $sql = "SELECT COUNT(*)
    				FROM m_platezi 
    					WHERE m_platezi.id='"._DB($id)."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
        if ($myrow[0]==0){echo 'Платежа с id = '.$id,' в базе не обнаруженно';exit;}
        
    $sql = "SELECT COUNT(*)
    				FROM a_options 
    					WHERE a_options.name='Пароль для удаления платежей' 
    					AND a_options.val='"._DB($pass)."'
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    if ($myrow[0]==0){echo 'Не верно указан пароль';exit;}
    
    $sql = "SELECT  m_platezi.id,
                    m_platezi.a_admin_id,
                    m_platezi.data,
                    (SELECT IF(COUNT(*)>0,i_scheta.name,'') FROM i_scheta WHERE m_platezi.i_scheta_id=i_scheta.id) AS schet_name,
                    m_platezi.summa,
                    m_platezi.tip,
                    m_platezi.a_menu_id,
                    m_platezi.id_z_p_p,
                    m_platezi.comments,
                    m_platezi.ostatok,
                    m_platezi.a_admin_id_info,
                    m_platezi.data_create
                    
                    
    				FROM m_platezi 
    					WHERE m_platezi.id='"._DB($id)."'
               
    	"; 
    
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $myrow = mysql_fetch_array($res);
    
    $a_menu='';
    if ($myrow['a_menu_id']=='16'){$a_menu='<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=m_zakaz&nomer='._DB($myrow['id_z_p_p']).'">Заказ №'.$myrow['id_z_p_p'].'</a>';}
    elseif ($myrow['a_menu_id']=='17'){$a_menu='<a href="http://'.$_SERVER['SERVER_NAME'].'/admin/?inc=m_postav&nomer='._DB($myrow['id_z_p_p']).'">Поступление №'.$myrow['id_z_p_p'].'</a>';}

    $message='<h2>Удален платеж №'.$myrow['id'].' от '.date('d.m.Y H:i',strtotime($myrow['data'])).'</h2>
                <p><strong>'.$a_menu.'</strong></p>
                <p>Сумма: <strong>'.$myrow['summa'].'</strong> руб.</p>
                <p>Тип: <strong>'.$myrow['tip'].'</strong></p>
                <p>Счет: <strong>'.$myrow['schet_name'].'</strong></p>
                <p>Остаток: <strong>'.$myrow['ostatok'].'</strong> руб.</p>
                ';
                //Оповещение
            send_mail_smtp(
                    $_SESSION['a_options']['email администратора'],
                    'Удаление платежей',
                    $message, 
                    'Администратору платежей',
                    '',
                    'Bot '.$_SERVER['SERVER_NAME']
            );
            
    log_remove_platezi($id);//логируем платеж до удаления
    
    $sql = "DELETE 
    			FROM m_platezi 
    				WHERE id='"._DB($id)."'
    ";
    $mt = microtime(true);
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $mt = microtime(true)-$mt; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
    $data_['id']=$id;
    
    
    echo json_encode($data_);
}
//************************************************************************************************** 
//автозаполнение заказов
if ($_t=='m_zakaz_id_autocomplete'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    

                            
    $sql_connect = "SELECT  m_zakaz.id,
                            m_zakaz.project_name,
                            (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE m_zakaz.i_contr_id=i_contr.id) AS i_contr_name
    				FROM m_zakaz
    					WHERE m_zakaz.id LIKE '"._DB($term)."%'
                        ORDER BY id DESC
                       LIMIT 50
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $data_['items']=array();
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['id'].'. '.$myrow_connect['project_name'].' ('.$myrow_connect['i_contr_name'].')';
        $data_['items'][$i]['text']=$myrow_connect['id'].'. '.$myrow_connect['project_name'].' ('.$myrow_connect['i_contr_name'].')';
        $data_['items'][$i]['id']=$myrow_connect['id'];
    } 

    echo json_encode($data_);
}
//************************************************************************************************** 
//автозаполнение поступлений
if ($_t=='m_postav_id_autocomplete'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    

                            
    $sql_connect = "SELECT  m_postav.id,
                            m_postav.project_name,
                            (SELECT IF(COUNT(*)>0,i_contr.name,'') FROM i_contr WHERE m_postav.i_contr_id=i_contr.id) AS i_contr_name
    				FROM m_postav
    					WHERE m_postav.id LIKE '"._DB($term)."%'
                        ORDER BY id DESC
                       LIMIT 50
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $data_['items']=array();
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['id'].'. '.$myrow_connect['project_name'].' ('.$myrow_connect['i_contr_name'].')';
        $data_['items'][$i]['text']=$myrow_connect['id'].'. '.$myrow_connect['project_name'].' ('.$myrow_connect['i_contr_name'].')';
        $data_['items'][$i]['id']=$myrow_connect['id'];
    } 

    echo json_encode($data_);
}
//************************************************************************************************** 
//автозаполнение должностей работника
if ($_t=='a_admin_i_post_autocomplete'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
    $a_admin_id=_GP('a_admin_id');
    

                            
    $sql_connect = "SELECT  a_admin_i_post.id,
                            i_post.name
                            
    				FROM a_admin_i_post, i_post
                    
    					WHERE a_admin_i_post.id2=i_post.id
                        AND a_admin_i_post.id1='"._DB($a_admin_id)."'
                        AND i_post.name LIKE '%"._DB($term)."%'
                        
                        GROUP BY i_post.name
                        ORDER BY i_post.name
                       LIMIT 50
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $data_['items']=array();
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['id'].'. '.$myrow_connect['name'];
        $data_['items'][$i]['text']=$myrow_connect['id'].'. '.$myrow_connect['name'];
        $data_['items'][$i]['id']=$myrow_connect['id'];
    } 

    echo json_encode($data_);
}
//************************************************************************************************** 
//автозаполнение поступлений
if ($_t=='i_reklama_id_autocomplete'){
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    $term=_GP('q');
     
    $sql_connect = "SELECT  i_reklama.id,
                            i_reklama.name
                    
                    FROM i_reklama
    					WHERE i_reklama.name LIKE '%"._DB($term)."%'
                        ORDER BY id DESC
                       LIMIT 50
    "; 
    //echo $sql_connect;
    $res_connect = mysql_query($sql_connect) or die(mysql_error());
    $data_['items']=array();
    for ($myrow_connect = mysql_fetch_array($res_connect),$i=0; $myrow_connect==true; $myrow_connect = mysql_fetch_array($res_connect),$i++)
    {
        $data_['items'][$i]['name']=$myrow_connect['id'].'. '.$myrow_connect['name'];
        $data_['items'][$i]['text']=$myrow_connect['id'].'. '.$myrow_connect['name'];
        $data_['items'][$i]['id']=$myrow_connect['id'];
    } 

    echo json_encode($data_);
}
//************************************************************************************************** 
}else{
    
    $_SESSION['error']['auth_'.date('Y-m-d H:i:s')]='Ошибка авторизации! $login="'.@$_SESSION['admin']['login'].'", pass: "'.@$_SESSION['admin']['password'].'"';
    echo 'Ошибка авторизации!';
    
}

?>