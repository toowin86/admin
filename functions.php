<?php
if (!isset($db)){echo 'no db connect';exit();}
?><?php
// Funcions
// create by toowin86@yandex.ru
setlocale(LC_ALL, 'rus');
include 'class/a.charset.php';


// ***********************************************************************************************************
// обработка до записи в БД
function _DB($txt){
    if (isset($txt) and !is_array($txt)){
        //if (is_array($txt)){$txt="'".implode("','",$txt)."'";}
        
        if(get_magic_quotes_gpc()==1){
            $txt=stripslashes(trim($txt));
        }
        else{
            $txt=@trim($txt);
        }
        return mysql_real_escape_string($txt);
    }else {if (is_array($txt)) {$_SESSION['error']['_DB:'.date('Y-m-d H:i:s')]='is_array: '.json_encode(debug_backtrace())."<hr />'".implode("','",$txt)."'";} return false;}
}
// ***********************************************************************************************************
// Обработка данных до вывода в input
function _IN($txt){
   if (is_array($txt)){$_SESSION['error']['_IN:'.date('Y-m-d H:i:s')]='is_array: '.json_encode(debug_backtrace())."<hr />'".implode("','",$txt)."'";unset($txt);$txt='';}
   return str_replace ( array ( '&', '"', "'", '<', '>'), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $txt );

}
// ***********************************************************************************************************
// АВТОРИЗАЦИЯ
function admin_auth($l,$p){
    $sql = "SELECT IF(COUNT(*)>0,a_admin.chk_active,'-') 
    				FROM a_admin 
    					WHERE a_admin.email='"._DB($l)."' 
                        AND a_admin.password='"._DB($p)."'
    	"; 
    $res = mysql_query($sql);
    $myrow = mysql_fetch_array($res);
    return $myrow[0];
}

// ***********************************************************************************************************
// ПОЛУЧЕНИЕ ПЕРЕМЕННЫХ
function _GP($txt,$nul='') # Получение переменной
{
    if (isset($_REQUEST[$txt])) {return $_REQUEST[$txt];} else {return $nul;}
}


// ***********************************************************************************************************
function sp_simv($txt)
{
    $txt=str_replace("&","&amp;",$txt);
    $txt=str_replace("\"","&quot;",$txt);
    $txt=str_replace("<","&lt;",$txt);
    $txt=str_replace(">","&gt;",$txt);
    $txt=str_replace("'","&apos;",$txt);
   
    return $txt;
}

// ***********************************************************************************************************
function sp_simv_back($txt)
{
    $txt=str_replace("&quot;","\\\"",$txt);
    $txt=str_replace("&lt;","<",$txt);
    $txt=str_replace("&gt;",">",$txt);
    $txt=str_replace("&apos;","'",$txt);
    $txt=str_replace("&amp;","&",$txt);
    $txt=str_replace("&nbsp;"," ",$txt);
    return $txt;
}


// ***********************************************************************************************************
// ОБРАБОТКА РОДИТЕЛЕЙ
function parents_arr($arr, $tip, $id=0){ # $arr['pid'][$id]=$pid;  $arr['name'][$id]=$name; ...
    $html = '';
    
    foreach ($arr['pid'] as $id_cur => $pid ) {
     
        if ($pid == $id) {
            
            if ($tip=='li'){
                $html .= '<li data-id="'.$id_cur.'">' . "\n";
                $html .= '<div><span>'.$arr['name'][$id_cur].'</span></div>' . "\n";
                $html .= parents_arr($arr,$tip, $id_cur) . "\n"; 
                $html .= '</li>' . "\n";
            }
            elseif ($tip=='option'){
                $html .= '<option value="'.$id_cur.'">' . "\n";
                $html .= str_repeat(' - ',count(parents_id($arr,$id_cur))).$arr['name'][$id_cur].'</option>' . "\n";
                $html .= parents_arr($arr,$tip, $id_cur) . "\n"; 
            }
        }
    
    }
    
        if ($tip=='li'){if ($id==0){return $html ?  $html . "\n" : '';} else{return $html ? '<ul>' . $html . '</ul>' . "\n" : '';}}
        elseif ($tip=='option'){return $html ? $html . "\n" : '';}
    
}

//Определяем текущую директорию
function cur_dir_(){
    //Если сайт лежит не на корне
    $cur_dir='';
    
    if (mb_strstr(__FILE__,'/docs/',false,'utf-8')==true){//для Nic.ru
        $dir_par_arr=explode('/docs/',__FILE__);
        $cur_dir=str_replace(array('/admin/functions.php','admin/functions.php'),'',$dir_par_arr[1]);
        if ($cur_dir!=''){$cur_dir='/'.$cur_dir;}
    }elseif (mb_strstr(__FILE__,'/httpdocs/',false,'utf-8')==true){//для reg.ru
        $dir_par_arr=explode($_SERVER['SERVER_NAME'],__FILE__);
        if (mb_strstr($dir_par_arr[1],'/httpdocs/',false,'utf-8')==true){
            $dir_par_arr[1]=str_replace('httpdocs/','',$dir_par_arr[1]);
        }
        $cur_dir=str_replace(array('/admin/functions.php','admin/functions.php'),'',$dir_par_arr[1]);
    }

    return $cur_dir;
}
// ***********************************************************************************************************
// ПОЛУЧАЕМ МАССИВ РОДИТЕЛЕЙ (до основного родителя, у которого pid=0)
function parents_id($arr, $id){ # $arr['pid'][$id]=$pid
    $par_arr=array();
    if ($arr['pid'][$id]!='0'){
        $par_arr[]=$arr['pid'][$id];
        $par_arr= array_merge($par_arr,parents_id($arr, $arr['pid'][$id]));
    }
    return $par_arr;
}
// ***********************************************************************************************************
// ПОЛУЧАЕМ МАССИВ ID у которых отсутствуют родители
function nul_parents_id($arr){ # $arr['pid'][$id]=$pid
    $nul_par_arr=array();
    foreach($arr['pid'] as $id_ => $pid_){
        if (!isset($arr['pid'][$pid_]) and $pid_!=0){
            $nul_par_arr[]=$id_;
        }
    }
    return $nul_par_arr;
}
// ***********************************************************************************************************
// ПОЛУЧАЕМ МАССИВ ДЕТЕЙ (первого порядка))
function children_id($arr, $id){ # $arr['pid'][$id]=$pid
    $child_arr=array();
    foreach($arr['pid'] as $cur_id =>$pid){
        if ($pid==$id){
            $child_arr[]=$cur_id;
        }
    }
    return $child_arr;
}
// ***********************************************************************************************************
// //получаем полный url и первую страницу в шаблоне сайта
function get_struktura_url_all_full($struktura){
    
    

    $cur_dir=cur_dir_();
   
    
    foreach($struktura['id'] as $key => $cur_id){
        if ($struktura['tip'][$key]=='Ссылка'){
            $struktura['url_all_full'][$key]=$struktura['url'][$key];
        }
        else{
            $link_='';
            $parents_id=parents_id_shablon($struktura, $cur_id);
            foreach($parents_id as $key3 => $id_){
                $key_ = array_search($id_, $struktura['id']);
                if ($struktura['tip'][$key_]!='Ссылка'){
                    if ($link_!=''){$link_='/'.$link_;}
                    $link_=$struktura['url'][$key_].$link_;
                }
            }
            
            if ($link_!=''){$link_='/'.$link_;}
            $link_=$link_.'/'.$struktura['url'][$key].'/';
            
            if (!isset($first_key) and $struktura['chk_active'][$key]=='1'){
                $first_key=$key;
                
                //город в url
                $cit_='';
                if (isset($_SESSION['change_city_url']) and $_SESSION['change_city_url']!=''){
                    $cit_=$_SESSION['change_city_url'].'/';
                }
                
                $struktura['url_all_full'][$key]=@$_SESSION['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$cur_dir.'/'.$cit_;   
            }else{
                 //город в url
                $cit_='';
                if (isset($_SESSION['change_city_url']) and $_SESSION['change_city_url']!=''){
                    $cit_='/'.$_SESSION['change_city_url'];
                }
                $struktura['url_all_full'][$key]=@$_SESSION['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$cur_dir.$cit_.$link_;        
            }
        }
    }
    if (!isset($first_key)){$first_key=0;}
    $data['struktura']=$struktura;
    $data['first_key']=$first_key;
    return $data;
}
// ***********************************************************************************************************
// Получаем ссылку для товара
function get_struktura_url_for_item($struktura,$struktura_id_txt){
    $url_str='';
    if ($struktura_id_txt!=''){
        $str_arr=array();
        if (strstr($struktura_id_txt,',')==true){
            $str_arr=explode(',',$struktura_id_txt);
        }else{
            $str_arr[0]=$struktura_id_txt;
        }
        foreach($str_arr as $key => $struktura_id){
            $key=array_search($struktura_id,$struktura['id']);
            if ($struktura['pid'][$key]=='0'){
                $url_str0='';
            }
            else{
                $url_str0=$struktura['url_all_full'][$key];
            }
            if (mb_strlen($url_str0,'UTF-8')>mb_strlen($url_str,'UTF-8')){
                $url_str=$url_str0;
            }
        }
    }
    return $url_str;
}


// ***********************************************************************************************************
// Функция потроения меню в зависимости от связаных пунктов в структуре
function create_tree_shablon($id,$struktura,$f_n){
    $txt='';
    foreach ($struktura['pid'] as $key => $pid){
        if ($pid==$id){
            if (in_array('Левое меню',$struktura['s_shablon'][$key]) and $struktura['chk_active'][$key]=='1')
            {
                $cl_='';if ($struktura['id'][$key]==$f_n){$cl_='  class="active"';}
                
                $txt.='<li'.$cl_.'><a href="'.$struktura['url_all_full'][$key].'"><span>';
                
                $txt_new=create_tree_shablon($struktura['id'][$key],$struktura,$f_n);
                if ($txt_new!=''){
                    $txt.='<i class="fa fa-plus"></i> '.$struktura['name'][$key].'</span></a>'.$txt_new;  
                }else{
                    $txt.='<span class="spanfa"></span>'.$struktura['name'][$key].'</span></a>';  
                }
                $txt.='</li>'."\n";
            }
        }
        elseif (isset($struktura['s_struktura_s_struktura'][$id])){
            if (in_array($struktura['id'][$key],$struktura['s_struktura_s_struktura'][$id])){
                if (in_array('Левое меню',$struktura['s_shablon'][$key]) and $struktura['chk_active'][$key]=='1')
                {
                    $cl_='';if ($struktura['id'][$key]==$f_n){$cl_='  class="active"';}
                
                    $txt.='<li'.$cl_.'><a href="'.$struktura['url_all_full'][$key].'"><span>';
                    $txt_new=create_tree_shablon($struktura['id'][$key],$struktura,$f_n);
                    if ($txt_new!=''){
                        $txt.='<i class="fa fa-plus"></i> '.$struktura['name'][$key].'</span></a>'.$txt_new;  
                    }else{
                        $txt.='<span class="spanfa"></span>'.$struktura['name'][$key].'</span></a>';  
                    }
                    $txt.='</li>'."\n";
                }
            }
        }
        
        
    }
    if ($txt!=''){
        
            $txt='<ul>'."\n".$txt.'</ul>'."\n";
        
    }
    return $txt;
}

// ***********************************************************************************************************
// ***********************************************************************************************************
// ПОЛУЧАЕМ МАССИВ РОДИТЕЛЕЙ (до основного родителя, у которого pid=0)
function parents_id_shablon($s_struktura_arr, $id){
    $parents_arr=array();
    $key = array_search($id, $s_struktura_arr['id']);
    $pid_=$s_struktura_arr['pid'][$key];
    if ($pid_!='0'){
        $parents_arr[]=$pid_;
        $parents_arr= array_merge($parents_arr,parents_id_shablon($s_struktura_arr, $pid_));
    }
    return $parents_arr;
}

// ***********************************************************************************************************
// ***********************************************************************************************************
// БУКВЕЕНЫЕ СУММЫ
$_1_2[1] = "одна ";
$_1_2[2] = "две ";

$_1_19[1] = "один ";
$_1_19[2] = "два ";
$_1_19[3] = "три ";
$_1_19[4] = "четыре ";
$_1_19[5] = "пять ";
$_1_19[6] = "шесть ";
$_1_19[7] = "семь ";
$_1_19[8] = "восемь ";
$_1_19[9] = "девять ";
$_1_19[10] = "десять ";

$_1_19[11] = "одиннадцать ";
$_1_19[12] = "двенадцать ";
$_1_19[13] = "тринадцать ";
$_1_19[14] = "четырнадцать ";
$_1_19[15] = "пятнадцать ";
$_1_19[16] = "шестнадцать ";
$_1_19[17] = "семнадцать ";
$_1_19[18] = "восемнадцать ";
$_1_19[19] = "девятнадцать ";

$des[2] = "двадцать ";
$des[3] = "тридцать ";
$des[4] = "сорок ";
$des[5] = "пятьдесят ";
$des[6] = "шестьдесят ";
$des[7] = "семьдесят ";
$des[8] = "восемдесят ";
$des[9] = "девяносто ";

$hang[1] = "сто ";
$hang[2] = "двести ";
$hang[3] = "триста ";
$hang[4] = "четыреста ";
$hang[5] = "пятьсот ";
$hang[6] = "шестьсот ";
$hang[7] = "семьсот ";
$hang[8] = "восемьсот ";
$hang[9] = "девятьсот ";

$namerub[1] = "рубль ";
$namerub[2] = "рубля ";
$namerub[3] = "рублей ";

$nametho[1] = "тысяча ";
$nametho[2] = "тысячи ";
$nametho[3] = "тысяч ";

$namemil[1] = "миллион ";
$namemil[2] = "миллиона ";
$namemil[3] = "миллионов ";

$namemrd[1] = "миллиард ";
$namemrd[2] = "миллиарда ";
$namemrd[3] = "миллиардов ";

$kopeek[1] = "копейка ";
$kopeek[2] = "копейки ";
$kopeek[3] = "копеек ";
function semantic($i, &$words, &$fem, $f)
{


    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd;
    $words = "";
    $fl = 0;
    if ($i >= 100) {
        $jkl = intval($i / 100);
        $words .= $hang[$jkl];
        $i %= 100;
    }
    if ($i >= 20) {
        $jkl = intval($i / 10);
        $words .= $des[$jkl];
        $i %= 10;
        $fl = 1;
    }
    switch ($i) {
        case 1:
            $fem = 1;
            break;
        case 2:
        case 3:
        case 4:
            $fem = 2;
            break;
        default:
            $fem = 3;
            break;
    }
    if ($i) {
        if ($i < 3 && $f > 0) {
            if ($f >= 2) {
                $words .= $_1_19[$i];
            } else {
                $words .= $_1_2[$i];
            }
        } else {
            $words .= $_1_19[$i];
        }
    }
}

function numer2str($L)
{

    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;

    $s = " ";
    $s1 = " ";
    $s2 = " ";
    $kop = intval(($L * 100 - intval($L) * 100));
    $L = intval($L);
    if ($L >= 1000000000) {
        $many = 0;
        semantic(intval($L / 1000000000), $s1, $many, 3);
        $s .= $s1 . $namemrd[$many];
        $L %= 1000000000;
    }

    if ($L >= 1000000) {
        $many = 0;
        semantic(intval($L / 1000000), $s1, $many, 2);
        $s .= $s1 . $namemil[$many];
        $L %= 1000000;
    }

    if ($L >= 1000) {
        $many = 0;
        semantic(intval($L / 1000), $s1, $many, 1);
        $s .= $s1 . $nametho[$many];
        $L %= 1000;
    }

    if ($L != 0) {
        $many = 0;
        semantic($L, $s1, $many, 0);
        $s .= $s1;
    }


    return $s;
}
function num2str($L)
{

    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;

    $s = " ";
    $s1 = " ";
    $s2 = " ";
    $kop = intval(($L * 100 - intval($L) * 100));
    $L = intval($L);
    if ($L >= 1000000000) {
        $many = 0;
        semantic(intval($L / 1000000000), $s1, $many, 3);
        $s .= $s1 . $namemrd[$many];
        $L %= 1000000000;
    }

    if ($L >= 1000000) {
        $many = 0;
        semantic(intval($L / 1000000), $s1, $many, 2);
        $s .= $s1 . $namemil[$many];
        $L %= 1000000;
        if ($L == 0) {
            $s .= "рублей ";
        }
    }

    if ($L >= 1000) {
        $many = 0;
        semantic(intval($L / 1000), $s1, $many, 1);
        $s .= $s1 . $nametho[$many];
        $L %= 1000;
        if ($L == 0) {
            $s .= "рублей ";
        }
    }

    if ($L != 0) {
        $many = 0;
        semantic($L, $s1, $many, 0);
        $s .= $s1 . $namerub[$many];
    }

    if ($kop > 0) {
        $many = 0;
        semantic($kop, $s1, $many, 1);
        $s .= $s1 . $kopeek[$many];
    } else {
        $s .= " 00 копеек";
    }

    return $s;
}
// ***********************************************************************************************************
//  Первая заглавнаая буква
 if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
      $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
      $str_end = "";
      if ($lower_str_end) {
 $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
      }
      else {
 $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
      }
      $str = $first_letter . $str_end;
      return $str;
    }
  }

// ***********************************************************************************************************
// Dix2491@yandex.ru
// Функция - генератор пароля пользователя
// Функция генерирует из заданной строки буквенно - циферно - символьный код
// Длинна кода 7 символов
// Использование: echo generate_code();
function generate_code($length = 7,$symbols='0123456789abcdefghijklmnopqrstuvwxyzQWERTYUIOPASDFGHJKLZXCVBNM'){
    $code = '';
    for( $i = 0; $i < (int)$length; $i++ )
    {
        $num = rand(1, strlen($symbols))-1;
        $code .= substr( $symbols, $num, 1 );  
    }              
    return $code;
}

// ***********************************************************************************************************
// Функция - Разделяет пробелом в числе - тысячи, миллионы, миллиарды
// Использование: echo thousand(число);
function thousand($number) 
{
    //$number = 40000000000000000000000;
    $number_ = number_format($number, 0, ' ', ' ');
    return $number_;
}


// ***********************************************************************************************************
//Вывод массива
function print_rf($arr)
{
    echo "\n".'<br /><pre>';
    print_r($arr);
    echo '</pre><br />'."\n";
}

// ***********************************************************************************************************
// ***********************************************************************************************************
// При отправке почты, все не латинские символы в заголовках кодируется,
// например тема письма может выглядеть так =?windows-1251?B?7/Du4uXw6uA=?=
// вот такие тексты и будет преобразовывать эта функция
function decode_mime_string($subject) {
    $string = $subject;
    $newresult='';
    if(($pos = strpos($string,"=?")) === false) return $string;
    while(!($pos === false)) {
        $newresult .= substr($string,0,$pos);
        $string = substr($string,$pos+2,strlen($string));
        $intpos = strpos($string,"?");
        $charset = substr($string,0,$intpos);
        $enctype = strtolower(substr($string,$intpos+1,1));
        $string = substr($string,$intpos+3,strlen($string));
        $endpos = strpos($string,"?=");
        $mystring = substr($string,0,$endpos);
        $string = substr($string,$endpos+2,strlen($string));
        if($enctype == "q") $mystring = quoted_printable_decode(preg_replace("/_/"," ",$mystring));
        else if ($enctype == "b") $mystring = base64_decode($mystring);
        $newresult .= $mystring;
        $pos = strpos($string,"=?");
    }

    $result = $newresult.$string;
    if(preg_match("/koi8/", $subject)) $result = convert_cyr_string($result, "k", "w");
    if(preg_match("/KOI8/", $subject)) $result = convert_cyr_string($result, "k", "w");
    return $result;
}

// перекодировщик тела письма.
// Само письмо может быть закодировано и данная функция приводит тело письма в нормальный вид.
// Так же и вложенные файлы будут перекодироваться этой функцией.
function compile_body($body,$enctype,$ctype) {
    $enctype = explode(" ",$enctype); $enctype = $enctype[0];
    if(strtolower($enctype) == "base64")
    $body = base64_decode($body);
    elseif(strtolower($enctype) == "quoted-printable")
    $body = quoted_printable_decode($body);
    if(preg_match("/koi8/", $ctype)) $body = convert_cyr_string($body, "k", "w");
    return $body;
}

// Функция для выдергивания метки boundary из заголовка Content-Type
// boundary это разделитель между разным содержимым в письме,
// например, чтобы отделить файл от текста письма
function get_boundary($ctype){
    if(preg_match('/boundary[ ]?=[ ]?(["]?.*)/i',$ctype,$regs)) {
        $boundary = preg_replace('/^\"(.*)\"$/', "\1", $regs[1]);
        return trim("--$boundary");
    }
}

// если письмо будет состоять из нескольких частей (текст, файлы и т.д.)
// то эта функция разобьет такое письмо на части (в массив), согласно разделителю boundary
function split_parts($boundary,$body) {
    $startpos = strpos($body,$boundary)+strlen($boundary)+2;
    $lenbody = strpos($body,"\r\n$boundary--") - $startpos;
    $body = substr($body,$startpos,$lenbody);
    return explode($boundary."\r\n",$body);
}

// Эта функция отделяет заголовки от тела.
// и возвращает массив с заголовками и телом
function fetch_structure($email) {
    $ARemail = Array();
    $separador = "\r\n\r\n";
    $header = trim(substr($email,0,strpos($email,$separador)));
    $bodypos = strlen($header)+strlen($separador);
    $body = substr($email,$bodypos,strlen($email)-$bodypos);
    $ARemail["header"] = $header;
    $ARemail["body"] = $body;
    return $ARemail;
}

// разбирает все заголовки и выводит массив, в котором каждый элемент является соответсвующим заголовком
function decode_header($header) {
    $headers = explode("\r\n",$header);
    $decodedheaders = Array();
    for($i=0;$i<count($headers);$i++) {
        $thisheader = trim($headers[$i]);
        if(!empty($thisheader))
        if(!preg_match("/^[A-Z0-9a-z_-]+:/",$thisheader))
        $decodedheaders[$lasthead] .= " $thisheader";
        else {
            $dbpoint = strpos($thisheader,":");
            $headname = strtolower(substr($thisheader,0,$dbpoint));
            $headvalue = trim(substr($thisheader,$dbpoint+1));
            if(isset($decodedheaders[$headname]) and $decodedheaders[$headname] != "") {
                $decodedheaders[$headname] .= "; $headvalue";
            }
            else {
                $decodedheaders[$headname] = $headvalue;
            }
            $lasthead = $headname;
        }
    }
    return $decodedheaders;
}
//получение почты
function get_data_pop($pop_conn)
{
    $data="";
    while (!feof($pop_conn)) {
        $buffer = chop(fgets($pop_conn,1024));
        $data .= "$buffer\r\n";
        if(trim($buffer) == ".") break;
    }
    return $data;
}

function get_data_smtp($smtp_conn)
{
    $data="";
    while($str = fgets($smtp_conn,515))
    {
        $data .= $str;
        if(substr($str,3,1) == " ") { break; }
    }
    return $data;
}

//отправка писем

//toowin86 2015-02-19 отправка письма
// ПРИМЕР ИСПОЛЬЗОВАНИЯ:
// $file_[0]='akt.docx';
// $file_[1]='sf.docx';
// $file_name[0]='Акт выполненных работ №'.$nomer22.'.docx';
// $file_name[1]='Счет №'.$nomer22.'.docx';
// $Subscribe - подписка
// $config - конфигурация, в случае невозможности использовать сессии (пример: ответ от яндекса)
// send_mail_smtp($_SESSION['options']['Отправка счетов при сохранении на email'],'Отправка счета','Проверка 123...','admin@internet-magazin','Интернет-магазин','Ивану Иванычу',1, $file_,$file_name);

/*
send_mail_smtp(
            $_SESSION['a_options']['email администратора'],
            $subject,
            $message, 
            'To',
            'test@mail.ru',
            'From_name',
            1,
            array(),
            array(),
            0,
            0,
            array()
    );
*/

function send_mail_smtp(
            $email_to,/* кому */
            $subject,/* тема */
            $message, /* текст письма в HTML */
            $name_to='To', /* кому ИМЯ */
            $email_from='test@mail.ru',/* устаревший параметр, не убрал, т.к. некоторые сайты его используют - теперь берется из  данных авторизации $_SESSION['a_options']['SMTP: login'] */
            $name_from='From',  /* от кого ИМЯ */
            $vazno=3, /* 1- важные письма */
            $file_=array(), /* массив файлов */
            $file_name_=array(),/* массив имен файлов */
            $mailru=0,/* устаревший параметр, не убрал, т.к. некоторые сайты его используют */
            $Subscribe=0, /* подписка */
            $config=array() /* для API, если нет врзможности хранить в сессиях */
    )
{

    $cur_dir=cur_dir_();//текущая директория
    
    
    if (count($config)==0){
        if (!isset($_SESSION['a_options']['SMTP: порт']) or $_SESSION['a_options']['SMTP: порт']=='') {echo "error session: no smtp options";}
        $config['smtp_username'] = $email_to; 
        $config['smtp_from']     = $_SESSION['a_options']['SMTP: login']; //Ваше имя - или имя Вашего сайта. Будет показывать при прочтении в поле "От кого"
        $config['smtp_charset']  = 'UTF-8'; //кодировка сообщений. (или UTF-8, итд)
        $config['smtp_debug']    = true; //Если Вы хотите видеть сообщения ошибок, укажите true вместо false
        $config['smtp_host']     = $_SESSION['a_options']['SMTP: сервер']; //сервер для отправки почты(для наших клиентов менять не требуется)
        $config['smtp_password'] = $_SESSION['a_options']['SMTP: password']; //Измените пароль
        $config['smtp_login'] = $_SESSION['a_options']['SMTP: login'];
        $config['smtp_port']     = $_SESSION['a_options']['SMTP: порт']; // Порт работы. Не меняйте, если не уверены.
        
     }
     
     
    $header =  "Date: ".date("D, d M Y H:i:s") . " UT\r\n";
    $header.="From: =?".$config['smtp_charset']."?Q?".str_replace("+","_",str_replace("%","=",urlencode($name_from)))."?= <".$config['smtp_from'].">\r\n";
    $header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
    $header.="Reply-To: =?".$config['smtp_charset']."?Q?".str_replace("+","_",str_replace("%","=",urlencode($name_to)))."?= <".$config['smtp_username'].">\r\n";
    $header.="X-Priority: ".$vazno."\r\n";
    $header.="Message-ID: <172562218.".date("YmjHis")."@mail.ru>\r\n";
    $header.="To: =?".$config['smtp_charset']."?Q?".str_replace("+","_",str_replace("%","=",urlencode($name_to)))."?= <".$config['smtp_username'].">\r\n";
    $header.="Subject: =?".$config['smtp_charset']."?Q?".str_replace("+","_",str_replace("%","=",urlencode($subject)))."?=\r\n";
    $header.="MIME-Version: 1.0\r\n";
    $header.="List-id: "._DB(ru_us($subject))." <".date('F')." ".date('Y').">\r\n";
    
    $Subscribe_txt='';
    if ($Subscribe==1){//подписка
        
        $Subscribe_txt='<p style="color#666;"><span style="font-size: 12px;color: #ccc;">Если хотите отказаться от получения писем с сайта: <a style="color: #5c96ff;" href="'.@$_SESSION['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$cur_dir.'/">'.$_SERVER['SERVER_NAME'].$cur_dir.'</a>, перейдите по ссылке: <a style="color: #5c96ff;" href="'.@$_SESSION['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/?com=Unsunscribe&Unsunscribe='.strip_tags($email_to).'&code='.md5(md5(trim($email_to)).$_SESSION['a_options']['secret_key'].md5(trim($email_to))).'">Отписаться</a></span></p>';
        
        $header.="List-Owner: <mailto:".$config['smtp_login'].">\r\n";//владелец списка
        $header.="List-Subscribe: <http://".$_SERVER['SERVER_NAME'].$cur_dir."/admin/functions/ajax/seo_send_mail.php?__tip=Subscribe&email=".trim($email_to).">\r\n";
        $header.="List-Unsubscribe: <".@$_SESSION['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$cur_dir.'/?com=Unsunscribe&Unsunscribe='.strip_tags($email_to).'&code='.md5(md5(trim($email_to)).$_SESSION['a_options']['secret_key'].md5(trim($email_to))).">\r\n";
    } 
    
    
   //Шаблонизатор сообщений
     if (        isset($_SESSION['REQUEST_SCHEME']) and @$_SESSION['REQUEST_SCHEME']!=''
        and     isset($_SERVER['SERVER_NAME']) and $_SERVER['SERVER_NAME']!=''
        and     isset($subject) and $subject!=''
        and     isset($message)
        and     isset($_SESSION['s_words']['Название организации']) and $_SESSION['s_words']['Название организации']!=''
        and     isset($_SESSION['s_words']['Код']) and $_SESSION['s_words']['Код']!=''
        and     isset($_SESSION['s_words']['Телефон']) and $_SESSION['s_words']['Телефон']!=''
        and     isset($_SESSION['s_words']['email']) and $_SESSION['s_words']['email']!=''
    ){
        
        $content = readPage(@$_SESSION['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$cur_dir.'/admin/mess.html');
        if (isset($content) and $content!=''){
            $message = str_replace( 
                        array(  '!!subject!!',
                                '!!message!!',
                                '!!http!!',
                                '!!host!!',
                                '!!name!!',
                                '!!mail_to!!',
                                '!!code!!',
                                '!!phone!!',
                                '!!email!!',
                                '!!Unsubscribe!!'), 
                        array(  $subject,
                                $message,
                                @$_SESSION['REQUEST_SCHEME'],
                                $_SERVER['SERVER_NAME'].$cur_dir,
                                strip_tags($_SESSION['s_words']['Название организации']),
                                strip_tags($email_to),
                                strip_tags($_SESSION['s_words']['Код']),
                                strip_tags($_SESSION['s_words']['Телефон']),
                                strip_tags($_SESSION['s_words']['email'],
                                $Subscribe_txt)
                        
                        ),$content);
            
        }
        else{
            //echo 'no file:<br />'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/admin/mess.html';
        }
    }
    
     
    // проверяем файлы
    if (is_array($file_) and count($file_)>0)
    {
        $header.="Content-Type: multipart/mixed; boundary=\"http://v-web.ru - disign studio\"\r\n\r\n";
        
        //Формируем письмо
        $header.="--http://v-web.ru - disign studio\r\n";
        $header.="Content-Type: text/html; charset=".$config['smtp_charset']."\r\n";
        $header.="Content-Transfer-Encoding: 8bit\r\n";
        
        $text=$message;
    
        foreach ($file_ as $key => $file_one) {
      
           unset($match);
           if (is_array($file_one) and isset($file_one[0])) {$one=$file_one[0];unset($file_one);$file_one=$one;}
           
            if (file_exists($file_one))
            {
                $ext[$key]=preg_replace("/.*?\./", '', $file_one);//расширение файла
                preg_match('/(.*?)[\.^]/i',basename($file_one),$match);
                $name[$key]=$match[1]; //имя файла
                $fp = fopen($file_one, "rb");
                
                if (isset($file_name_[$key])){
                    if (is_array($file_name_[$key])) {$f_=$file_name_[$key][0];}
                    else{$f_=$file_name_[$key];}
                }else{
                    $f_=ru_us(str_replace('.'.$ext[$key],'',$file_[$key])).'.'.$ext;
                }
               
                
                $text.="\r\n--http://v-web.ru - disign studio\r\n";
                $text.="Content-Disposition: attachment; filename=\"".$f_."\"\r\n";
                $text.="Content-transfer-encoding: base64\r\n";
                $text.="Content-Type: application/octet-stream; name=\"".$f_."\"\r\n\r\n";
                $text.=chunk_split(base64_encode(fread($fp, filesize($file_one))))."\r\n";

                fclose($fp);
            }
        }
        $text.="\r\n--http://v-web.ru - disign studio--\r\n"; 
        
    }else{
        $header.="Content-Type: text/html; ".$config['smtp_charset']."\r\n";
        $header.="Content-Transfer-Encoding: 8bit\r\n";
        $text=$message;
    }
    //print_rf($config);exit();
    $smtp_conn = @fsockopen($config['smtp_host'], $config['smtp_port'],$errno, $errstr, 10);
    if(!$smtp_conn) {print "Сбой при отправке сообщения! Соединение с серверов не прошло config[smtp_host]=".$config['smtp_host']."<br />"; @fclose($smtp_conn); return false;}
    
    ini_set("SMTP","ssl://".$config['smtp_host']);
    ini_set("smtp_port","465");

    $data = get_data_smtp($smtp_conn);
    fputs($smtp_conn,"EHLO ".$config['smtp_host']."\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 250) {print "ошибка приветсвия EHLO"; @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,"STARTTLS"."\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    stream_socket_enable_crypto($smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT); 
    
    fputs($smtp_conn,"EHLO ".$config['smtp_host']."\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 250) {print "ошибка приветсвия EHLO2"; @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,"AUTH LOGIN\r\n");
    $code0 = get_data_smtp($smtp_conn);
    $code=substr($code0,0,3);
    if($code != 334) {print "Сбой при отправке сообщения! сервер не разрешил начать авторизацию<br />".$code0; @fclose($smtp_conn);return false;}
    
    fputs($smtp_conn,base64_encode($config['smtp_login'])."\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 334) {print "Сбой при отправке сообщения! ошибка доступа к пользователю '".$config['smtp_login']."'<br />"; @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,base64_encode($config['smtp_password'])."\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 235) {print "Сбой при отправке сообщения! не правильный пароль<br />"; @fclose($smtp_conn); return false;}
     //echo $header."\r\n".$text;
    $size_msg=strlen($header."\r\n".$text);
    
    fputs($smtp_conn,"MAIL FROM:<".$config['smtp_from']."> SIZE=".$size_msg."\r\n");
    
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 250) {print "Сбой при отправке сообщения! Проверьте email MAIL FROM:'".$config['smtp_from']."'.<br />"; @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,"RCPT TO:<".$email_to.">\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 250 AND $code != 251) {print "Сбой при отправке сообщения! Проверьте email: '".$email_to."'. RCPT TO<br />";  @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,"DATA\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 354) {print "сервер не принял DATA<br />"; @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,$header."\r\n".$text."\r\n.\r\n");
    $code = substr(get_data_smtp($smtp_conn),0,3);
    if($code != 250) {print "ошибка отправки письма<br />"; @fclose($smtp_conn); return false;}
    
    fputs($smtp_conn,"QUIT\r\n");
    fclose($smtp_conn);
    
    return true;
}

// ********************************************************************************************************
// разница во времени
function raznica_po_vremeni($old_date, $new_date, $type='hours') {
    $offset = strtotime($new_date . " UTC") - strtotime($old_date . " UTC");
    if( $type == 'days'){$return_time = round($offset/60/60/24,1);}
    if( $type == 'hours'){$return_time = round($offset/60/60,1);}
    if( $type == 'minyte'){$return_time = round($offset/60,1);}
    if( $type == 'second'){$return_time = $offset;}
    return $return_time;
}
// ********************************************************************************************************
// конвертация
// conv_('phone_to_db',$phone);
// conv_('data_to_db',$val);
function conv_($tip,$txt){
    if ($tip=='phone_to_db'){
        $txt = trim($txt);
        $arr1=array(" ","(",")","-");
        $arr2=array("");
        
        $txt = str_replace($arr1,$arr2,$txt);
        return $txt;
    }
    elseif ($tip=='phone_from_db'){
        $txt = trim($txt);
        return substr($txt, 0, 1).'('.substr($txt, 1, 3).')'.substr($txt,4, 3).'-'.substr($txt,7, 4);
    }
    elseif ($tip=='data_to_db'){
        if (strstr($txt,'.')==true){
            if (strlen($txt)<=10){ //дата
                $date = DateTime::createFromFormat('d.m.Y', $txt);
                return $date->format('Y-m-d');            
            }else{ //дата-время
                $date = DateTime::createFromFormat('d.m.Y H:i:s', $txt);
                return $date->format('Y-m-d H:i:s'); 
            }
        }else{
            return $txt;
        }
    }
    elseif ($tip=='data_from_db'){
        if (strstr($txt,'-')==true){
            if (strlen($txt)<=10){ //дата
                $date = DateTime::createFromFormat('Y-m-d', $txt);
                return $date->format('d.m.Y');            
            }else{ //дата-время
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $txt);
                return $date->format('d.m.Y H:i:s'); 
            }
        }else{
            return $txt;
        }
    }
    elseif ($tip=='price_to_db'){
        if (strstr($txt,'.')==true){
            $key=strpos($txt,'.');
            $txt=substr($txt,0,$key);
            $txt=preg_replace('/[\D]{1,}/s', '',$txt);
        }else{
            $txt=preg_replace('/[\D]{1,}/s', '',$txt);
        }
        return $txt;
    }
}
// ********************************************************************************************************
// переврд из русского в английский
function ru_us($str)
{
	$translit = array(
		"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
		"Д"=>"D","Е"=>"E","Ё"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
		"Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
		"О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
		"У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
		"Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
		"Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j",
		"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
		"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"," "=>"-","+"=>"-",
		" "=>"-","+"=>"-","_"=>"-","`"=>"-","!"=>"-","@"=>"-",":"=>"-",";"=>"-","#"=>"-","$"=>"-","%"=>"-",
		"^"=>"-","&"=>"-","*"=>"-","("=>"-",")"=>"-","|"=>"-","\\"=>"-","]"=>"-","["=>"-",
		"{"=>"-","}"=>"-","/"=>"-","."=>"-","–"=>"-","-"=>"-",","=>"-","'"=>"-","»"=>"-","\""=>"-","«"=>"-","?"=>"-","~"=>"-",
        "®"=>"-"
	);
    $str=strtr($str,$translit);
    $reg='/[a-zA-Z0-9-\.]/si';
    preg_match_all($reg, $str, $regs);
    $new_str=implode('',$regs[0]);
    return $new_str; 
}
// ********************************************************************************************************
// Функция определения имен столбцов
function get_column_names_with_show ($table) 
{
    $query = "SHOW COLUMNS FROM `"._DB($table)."`";
    if (!($result_id = mysql_query ($query)))
    return (FALSE);
    $names = array();
    while (list ($name) = mysql_fetch_row ($result_id))
    $names[] = $name; 
    mysql_free_result ($result_id);
    return ($names);
}

//**********************************************************************************************************
#'0','5','6','7','8','9': write('ок');
#'1': write('ку');
#'2','3','4': write('ки');
//end_word($int_,'ов','','а');
function end_word($int_,$zer='ов',$one='',$two='а')
{
	$int_=$int_.'';
	$arr=str_split($int_);
	$simv=array_pop($arr);
    $simv2=array_pop($arr);// toowin86 12-06-13
    //echo $simv2.' '.$simv.';';
    if ($simv2!='1'){// toowin86 12-06-13
    	if ($simv=='0' or $simv=='5' or $simv=='6' or $simv=='7' or $simv=='8' or $simv=='9')
    		{return($zer);}
    	elseif ($simv=='1')
    		{return($one);}
    	elseif ($simv=='2' or $simv=='3' or $simv=='4')
    		{return($two);}
    	else 
    		{return('');}
            }// toowin86 12-06-13
     else{ // toowin86 12-06-13
        return($zer);// toowin86 12-06-13
     }   // toowin86 12-06-13
}



//**********************************************************************************************************
function smart_resize_image( $file, $output , $width = 0, $height = 0, $proportional = true, $delete_original = false, $use_linux_commands = false )
{
	if ( $height <= 0 && $width <= 0 )
	{
		return false;
	}
	
	$info = getimagesize($file);
	$image = '';
	
	$final_width = 0;
	$final_height = 0;
	list($width_old, $height_old) = $info;
    if (!isset($width_old) or $width_old==0 or !isset($height_old) or  $height_old==0){
        return false;
    }
    
	if ($proportional)
	{
		if ($width == 0) $factor = $height/$height_old;
		elseif ($height == 0) $factor = $width/$width_old;
		else $factor = min ( $width / $width_old, $height / $height_old);
		$final_width = round ($width_old * $factor);
		$final_height = round ($height_old * $factor);
	}
	else
	{
		$final_width = ( $width <= 0 ) ? $width_old : $width;
		$final_height = ( $height <= 0 ) ? $height_old : $height;
	}
	
	switch ($info[2])
	{
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($file);
			break;
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($file);
			break;
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($file);
			break;
		default:
			return false;
	}
	
	$image_resized = imagecreatetruecolor( $final_width, $final_height );
	
	if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) )
	{
		$trnprt_indx = imagecolortransparent($image);
        $palletsize = imagecolorstotal($image);
        if ($trnprt_indx >= 0 && $trnprt_indx < $palletsize) {
            $trnprt_color = imagecolorsforindex($image, $trnprt_indx);
      
			$trnprt_color = imagecolorsforindex($image, $trnprt_indx);
			$trnprt_indx = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
			imagefill($image_resized, 0, 0, $trnprt_indx);
			imagecolortransparent($image_resized, $trnprt_indx);
		}
		elseif ($info[2] == IMAGETYPE_PNG)
		{
			
			imagealphablending($image_resized, false);
			$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
			imagefill($image_resized, 0, 0, $color);
			imagesavealpha($image_resized, true);
		}
	}
	
	imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
	
	if ( $delete_original )
	{
		if ( $use_linux_commands )
			exec('rm '.$file);
		else
			@unlink($file);
	}
	/*
	switch ( strtolower($output) )
	{
		case 'browser':
			$mime = image_type_to_mime_type($info[2]);
			header("Content-type: $mime");
			$output = NULL;
			break;
		case 'file':
			$output = $file;
			break;
		case 'return':
			return $image_resized;
			break;
		default:
			break;
	}
	*/
    if ($output==''){
		return $image_resized;
    }

    
	switch ($info[2])
	{
		case IMAGETYPE_GIF:
			imagegif($image_resized, $output);
			break;
		case IMAGETYPE_JPEG:
			$res=@imagejpeg($image_resized, $output);
            if (!$res){echo 'error: no correct JPEG';exit();}
			break;
		case IMAGETYPE_PNG:
			imagepng($image_resized, $output);
			break;
		default:
			return false;
	}
	
	return true;
}


//**********************************************************************************************************

class SimpleImage {

   var $image;
   var $image_type;

   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
}
//РАБОТА С ПАМЯТЬЮ
function memoryUsage($usage, $base_memory_usage) {
    printf("Bytes diff: %d\n", $usage - $base_memory_usage);
}
function someBigValue() {
    return str_repeat('SOME BIG STRING', 1024);
}

//ЧТЕНИЕ ФАЙЛА
function readPage($url)
{
    if (!function_exists('curl_init')){echo 'Не определена функция curl_init!';exit();}
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}



// Получение массива адресов
function get_adress($val){
    global $_SESSION;
    

//print_r(get_adress('Красноярск,  улиц. 26 Бакинский Комиссаров, д. 5в'));
    //https://geocode-maps.yandex.ru/1.x
    $data_=array();
    $data_['country']='';
    $data_['area']='';
    $data_['city']='';
    $data_['city_raion']='';
    $data_['street']='';
    $data_['build']='';
    $data_['geo']='';
    $val=str_replace(' ','+',$val);
    
    
    //использование
    if (isset($_SESSION['a_options']) and isset($_SESSION['a_options']['Ключ API: Яндекс Геодекодер']) and $_SESSION['a_options']['Ключ API: Яндекс Геодекодер']!=''){
       $xml =  simplexml_load_file('https://geocode-maps.yandex.ru/1.x/?geocode='.$val.'&apikey='.$_SESSION['a_options']['Ключ API: Яндекс Геодекодер']); 
       
    }else{//нет ключа для геодекодера- пробуем старую версию
    
        $xml =  simplexml_load_file('http://geocode-maps.yandex.ru/1.x/?geocode='.$val);
    }
    
    

    
    $obj_=$xml->GeoObjectCollection->featureMember->GeoObject;
    
    if (isset($obj_) and is_object($obj_) and count($obj_)>0){
        
        //Страна
        if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->CountryName)){
            $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->CountryName;
            if (count($val_)>0){$data_['country']=$val_.'';}
        }
        //Область/край
        if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->AdministrativeAreaName)){
            $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->AdministrativeAreaName;
            if (count($val_)>0){$data_['area']=$val_.'';}
        }
        //город
        if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName)){
            $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;
            if (count($val_)>0){$data_['city']=$val_.'';}
        }
        
        //Микрорайон
        if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->DependentLocalityName)){
            $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->DependentLocalityName;
            if (count($val_)>0){$data_['city_raion']=$val_.'';}
        }
        
        //улица
        if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->Thoroughfare->ThoroughfareName)){
            $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
            if (count($val_)>0){$data_['street']=$val_.'';}
        }
        if ($data_['street']==''){
            if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->ThoroughfareName)){
                $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->ThoroughfareName;
                if (count($val_)>0){$data_['street']=$val_.'';}
            }
        }
        
        //дом
        if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->Thoroughfare->Premise->PremiseNumber)){
            $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->Thoroughfare->Premise->PremiseNumber;
            if (count($val_)>0){$data_['build']=$val_.'';}
        }
        if ($data_['build']==''){
            if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->DependentLocality->Locality->Thoroughfare->Premise->PremiseNumber)){
                $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->DependentLocality->Locality->Thoroughfare->Premise->PremiseNumber;
                if (count($val_)>0){$data_['build']=$val_.'';}
            }
        }
        if ($data_['build']==''){
            if (isset($obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->Premise->PremiseNumber)){
                $val_=$obj_->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->Premise->PremiseNumber;
                if (count($val_)>0){$data_['build']=$val_.'';}
            }
        }
        //гео координаты в виде: 92.891460 56.013915
        if (isset($obj_->Point->pos)){
            $val_=$obj_->Point->pos;
            if (count($val_)>0){$data_['geo']=$val_.'';}
        }
    }
    return $data_;
}


///////////////////////////
// получение массива id родителей
function get_parent_id_array($cur_id,$arr_id,$arr_pid)
{
    //print_r($arr_pid);print_r($arr_id);print_r($cur_id);
    unset($id_arr);
    $id_arr=array();
    $key = array_search($cur_id, $arr_id);
    $par_pid= $arr_pid[$key];
    $i=0;

    while($par_pid>0)
    {
        $key_par_id=array_search($par_pid, $arr_id);
        $id_arr[$i]=$arr_id[$key_par_id];  
        $par_pid= $arr_pid[$key_par_id];  
        $i++;
    }

    return $id_arr;
}
// end получение массива id родителей
// получение массива id детей
function get_children_id_array($cur_id,$arr_id,$arr_pid)
{
    $key = array_search($cur_id, $arr_pid);
    $return_arr=array();
    foreach ($arr_pid as $key => $pid) {
        if ($pid==$cur_id)
        {
            $return_arr[]=$arr_id[$key];
        }
    }
    return $return_arr;
}
// end получение массива id детей

function RemoveDirTime($path,$time_) # Удаление всех файлов из папки по расписанию
{
	if(file_exists($path) && is_dir($path))
	{
		$dirHandle = opendir($path);
		while (false !== ($file = readdir($dirHandle))) 
		{
			if ($file!='.' && $file!='..') // исключаем папки с назварием '.' и '..' 
			{
				$tmpPath=$path.'/'.$file;
				@chmod($path, 0777);
				@chmod($tmpPath, 0777);
				if (is_dir($tmpPath))
				{  // если папка
					RemoveDir($tmpPath);
				} 
				else 
				{ 
                    // текущее время
                    $time_sec=time();
                    // время изменения файла
                    $time_file=filemtime($tmpPath);
                    // тепрь узнаем сколько прошло времени (в секундах)
                    $time=$time_sec-$time_file;
                    
                    if($time>$time_){
						if(file_exists($tmpPath))
						{
							// удаляем файл 
							unlink($tmpPath);
						}
                    }
				}
			}
		}
		closedir($dirHandle);
		
		// удаляем текущую папку
		if(file_exists($path))
		{
			#rmdir($path);
		}
	}
	else
	{
		echo "<br>Удаляемой папки не существует или это файл!";
	}
}

//Создаем url
function make_url($name,$var=0){
    
    //английский урл
    if (isset($_SESSION['a_options']['Русский URL']) and $_SESSION['a_options']['Русский URL']=='0'){
        $name=ru_us($name);
    }
    
    if ($var==0){
        return preg_replace('/([ ~-])/u','_',preg_replace('/([<>@`#&.*+?^=!;%,№\'\":${}()|\[\]\/])/u','',$name));
    }else{
        return preg_replace('/([ ~-])/u','_',preg_replace('/([<>@`#&.*+?^=!;%,№\'\":${}()|\[\]\/])/u','',$name.'_'.$var));
    }
    
}
// **********************************************************************************************************
//Изменение записи
function change_row($inc,$col,$nomer){
    $names=get_column_names_with_show($inc);
    $sql_upp='';
    $data_['_sql']['sql']=array();
    $data_['_sql']['time']=array();
    if (is_array($nomer)){
        $nomer_arr=$nomer;
    }else{
        $nomer_arr[0]=$nomer;
    }//('".implode("','",$nomer_arr)."')
    // ***********************************************************************
    // ************************ URL ******************************************
    // ***********************************************************************
    if ($col=='name' or $col=='url'){
        $tip_='';
        if (in_array('tip',$names)){
            $sql = "SELECT `tip`
            				FROM `"._DB($inc)."`
            					WHERE `"._DB($inc)."`.`id` IN ('".implode("','",$nomer_arr)."')
            	"; 
            $mt = microtime(true);
            $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
            $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                if (!$res){
                    $_SESSION['error']['function_change_row_'.date('Y-m-d H:i:s')]='Ошибка $sql! $sql="'.@$sql.'; $inc='.$inc.'; $col='.$col.'; $nomer='."('".implode("','",$nomer_arr)."')";
                }
            $myrow = mysql_fetch_array($res);
            $tip_=$myrow[0];
        }
        if ($tip_!='Ссылка' and $tip_!='Функция'){
            if (in_array('url',$names) and in_array('name',$names) and (count($nomer_arr)==1 or $col=='name') and _GP('url')==''){
                
                $sql = "SELECT `name`, `url`
                				FROM `"._DB($inc)."`
                					WHERE `"._DB($inc)."`.`id` IN ('".implode("','",$nomer_arr)."')
                	"; 
                $mt = microtime(true);
                $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
                $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                    if (!$res){
                        $_SESSION['error']['function_change_row_'.date('Y-m-d H:i:s')]='Ошибка $sql! $sql="'.@$sql.'; $inc='.$inc.'; $col='.$col.'; $nomer='."('".implode("','",$nomer_arr)."')";
                    }
                $myrow = mysql_fetch_array($res);
                $name_=$myrow[0];
                $url_old=$myrow[1];
                if ($url_old==''){
                    $url_=make_url($name_);
                   
                    $sql = "SELECT COUNT(*)
                    				FROM `"._DB($inc)."`
                    					WHERE `"._DB($inc)."`.`url`='"._DB($url_)."'
                                        AND `"._DB($inc)."`.`id` NOT IN ('".implode("','",$nomer_arr)."')
                    	";
                    $mt = microtime(true);
                    $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
                    $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                        if (!$res){
                            $_SESSION['error']['function_change_row_'.date('Y-m-d H:i:s')]='Ошибка $sql! $sql="'.@$sql.'; $inc='.$inc.'; $col='.$col.'; $nomer='."('".implode("','",$nomer_arr)."')";
                        }
                    $myrow = mysql_fetch_array($res);
                    $i=2;
                    while($myrow[0]>0){
                        $url_=make_url($name_,$i);
                        $sql = "SELECT COUNT(*)
                        				FROM `"._DB($inc)."`
                        					WHERE `"._DB($inc)."`.`url`='"._DB($url_)."'
                                            AND `"._DB($inc)."`.`id` NOT IN ('".implode("','",$nomer_arr)."')
                        	";
                        $mt = microtime(true);
                        $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
                        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
                            if (!$res){
                                $_SESSION['error']['function_change_row_'.date('Y-m-d H:i:s')]='Ошибка $sql! $sql="'.@$sql.'; $inc='.$inc.'; $col='.$col.'; $nomer='."('".implode("','",$nomer_arr)."')";
                            }
                        $myrow = mysql_fetch_array($res);
                        $i++;
                    }
                    
                   
                     
                    if ($sql_upp!=''){$sql_upp.=', ';}
                    $sql_upp.="`"._DB($inc)."`.`url`='"._DB($url_)."'";
                }
            }
        }
    }
    // *********************************************************************************
    // ********** ДАТА ИЗМЕНЕНИЯ *******************************************************
    // *********************************************************************************
    if (in_array('data_change',$names)){
        if ($sql_upp!=''){$sql_upp.=', ';}
        $sql_upp.="`"._DB($inc)."`.`data_change`='"._DB(date('Y-m-d H:i:s'))."'";
    } 
    // *********************************************************************************
    // ********** Гео *******************************************************
    // *********************************************************************************
    if (in_array('geo',$names) and in_array('adress',$names) and in_array('i_city_id',$names) and _GP('adress')!='' and _GP('i_city_id')!=''){
        
        //Получаем название города
        $sql0 = "SELECT IF(COUNT(*)>0,i_city.name,'') 
            				FROM i_city 
            					WHERE i_city.id='"._DB(_GP('i_city_id'))."' OR  i_city.name='"._DB(_GP('i_city_id'))."'
                	"; 
        $res0 = mysql_query($sql0);
        
        $myrow0 = mysql_fetch_array($res0);
      
        $geo_arr=get_adress(@$myrow0[0].', '._GP('adress'));

        if (isset($geo_arr) and isset($geo_arr['geo']) and $geo_arr['geo']!=''){
        
            if ($sql_upp!=''){$sql_upp.=', ';}
            $sql_upp.="`"._DB($inc)."`.`geo`='"._DB($geo_arr['geo'])."'";
        }
    }
    //Пользователь изменяющий товар
    if (in_array('a_admin_id_change',$names)){
        
        //Получаем id админа
        $sql0 = "SELECT IF(COUNT(*)>0,a_admin.id,'') 
            				FROM a_admin 
            					WHERE a_admin.email='"._DB($_SESSION['admin']['email'])."' 
                                AND a_admin.password='"._DB($_SESSION['admin']['password'])."'
                	"; 
        $res0 = mysql_query($sql0);
        $myrow0 = mysql_fetch_array($res0);
        $a_admin_id_cur=$myrow0[0];

        if ($sql_upp!=''){$sql_upp.=', ';}
        $sql_upp.="`"._DB($inc)."`.`a_admin_id_change`='"._DB($a_admin_id_cur)."'";
    }
    
    if ($sql_upp!=''){
        $sql = "
        		UPDATE `"._DB($inc)."` 
        			SET  
        				$sql_upp
        		
        		WHERE `"._DB($inc)."`.`id` IN ('".implode("','",$nomer_arr)."')
        ";
        $mt = microtime(true);
        $res = mysql_query($sql) or die($sql.'<br />'.mysql_error());
        $mt = microtime(true)-$mt ; $data_['_sql']['sql'][]=$sql;$data_['_sql']['time'][]=$mt;
        if (!$res){
            $_SESSION['error']['function_change_row_'.date('Y-m-d H:i:s')]='Ошибка $sql! $sql="'.@$sql.'; $inc='.$inc.'; $col='.$col.'; $nomer='."('".implode("','",$nomer_arr)."')";
        }
    }
    return $data_;
}

//Проверка доступности сайта
function check_domain_availible($domain)
{
    
    if (!filter_var($domain, FILTER_VALIDATE_URL)){echo 'ERROR: FILTER_VALIDATE_URL - '.$domain;return false;}
    
    $curlInit = curl_init($domain);
    curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curlInit, CURLOPT_HEADER, true);
    curl_setopt($curlInit, CURLOPT_NOBODY, true);
    curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curlInit);
    curl_close($curlInit);
    
    if ($response){return true;}
    else{return false;}
    
}

/**
* Склонение слов по падежам. С использованием api Яндекса
* @var string $text - текст
* @var integer $numForm - нужный падеж. Число от 0 до 5
*
* @return - вернет false при неудаче. При успехе вернет нужную форму слова
*/
function getNewFormText($text, $numForm){
    //$urlXml = "http://export.yandex.ru/inflect.xml?name=".urlencode($text);
    $urlXml = "http://morpher.ru/WebService.asmx/GetXml?s=".urlencode($text);
    $result = @simplexml_load_file($urlXml);
    if($result){
     
        $arrData = array();
        $arrData[]=$text;
        foreach ($result as $one) {
           $arrData[] = (string) $one;
        }
        //print_rf($arrData);exit();
        if (isset($arrData[$numForm])){return $arrData[$numForm];}
        else{
            //print_rf($arrData);exit();
        }
    }
    return false;
}
//***************************************************************************
//Проверка кода eval
function syntax_check_php_file($code) {   

    // первый этап проверки
    $braces = 0;
    $inString = 0;
    foreach ( token_get_all($code) as $token ) {
        if ( is_array($token) ) {
            switch ($token[0]) {
                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                case T_START_HEREDOC: ++$inString; break;
                case T_END_HEREDOC:   --$inString; break;
            }
        }
        else if ($inString & 1) {
            switch ($token) {
                case '`':
                case '"': --$inString; break;
            }
        }
        else {
            switch ($token) {
                case '`':
                case '"': ++$inString; break;
 
                case '{': ++$braces; break;
                case '}':
                    if ($inString) {
                        --$inString;
                    }
                    else {
                        --$braces;
                        if ($braces < 0) {
                            throw new Exception('Braces problem!');
                        }
                    }
                break;
            }
        }
    }
     
    // расхождение в открывающих-закрывающих фигурных скобках
    if ($braces) {
        throw new Exception('Braces problem!');
    }
     
    $res = false;
    // второй этап проверки
    ob_start();
    eval('if (0) {'.$code.' }; $res = true;');
    
    $error_text = ob_get_clean();
     
    if (!$res) {
        return false;
    }else{
        return true;
    }
}

function is_email($email) {
  return preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email);
}
function is_phone($phone) {
  return preg_match ( "/^(7|38|8)\(\d{1,3}\)\d{7,8}$/", $phone);
}
function is_name($name) {
  return preg_match("/^[а-яА-Я ]{2,}+$/i", $name);
}



//Авторизация пользователя
function auth_user(){
    if (isset($_SESSION['cart']['user_login']) and isset($_SESSION['cart']['user_password']) and $_SESSION['cart']['user_password']!='' and $_SESSION['cart']['user_login']!=''){
        
        $login='phone';if ($_SESSION['a_options']['Регистрация: email-0/sms-1']=='0'){$login='email';}
        
        $sql = "SELECT IF(COUNT(*)>0,i_contr.id,'')
        				FROM i_contr 
        					WHERE i_contr.$login='"._DB($_SESSION['cart']['user_login'])."' 
        					AND i_contr.password='".md5($_SESSION['cart']['user_password'].$_SESSION['a_options']['secret_key'].$_SESSION['cart']['user_password'])."'
                            AND i_contr.chk_active='1'
        	"; 
        $res = mysql_query($sql);if (!$res){echo $sql;exit();}
        $myrow = mysql_fetch_array($res);
        $i_contr_id=$myrow[0];
        if ($i_contr_id!=''){
            return $i_contr_id;
        }else{return false;}
       
    }else{
        return false;
    }
}

//Сохраняем из base64 в картинку
function base64_to_jpeg($base64_string, $output_file) {
	$ifp = fopen($output_file, "wb");
	fwrite($ifp, base64_decode($base64_string)); 
	fclose($ifp);
	return $output_file; 
}

//Получение всех изображений 
function get_all_image($data){
    $images = array();
    preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $data, $media);
    unset($data);
    $data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);
     
    foreach ($data as $url) {
        $info = pathinfo($url);
        if (isset($info['extension'])) {
            if (($info['extension'] == 'jpg') ||
                    ($info['extension'] == 'jpeg') ||
                    ($info['extension'] == 'gif') ||
                    ($info['extension'] == 'png'))
                array_push($images, $url);
        }
    }
    return $images;
}

//Определяем тип слова
function chastrechiRUS($string){
    //http://habrahabr.ru/post/152389/
     /*
     Группы окончаний:
     1. прилагательные
     2. причастие
     3. глагол
     4. существительное
     5. наречие
     6. числительное
     7. союз
     8. предлог
    */
    
     $groups = array(
     1 => array ('ее','ие','ые','ое','ими','ыми','ей','ий','ый','ой','ем','им','ым','ом',
    'его','ого','ему','ому','их','ых','ую','юю','ая','яя','ою','ею'),
     2 => array ('ивш','ывш','ующ','ем','нн','вш','ющ','ущи','ющи','ящий','щих','щие','ляя'),
     3 => array ('ила','ыла','ена','ейте','уйте','ите','или','ыли','ей','уй','ил','ыл','им','ым','ен',
    'ило','ыло','ено','ят','ует','уют','ит','ыт','ены','ить','ыть','ишь','ую','ю','ла','на','ете','йте',
    'ли','й','л','ем','н','ло','ет','ют','ны','ть','ешь','нно'),
     4 => array ('а','ев','ов','ье','иями','ями','ами','еи','ии','и','ией','ей','ой','ий','й','иям','ям','ием','ем',
    'ам','ом','о','у','ах','иях','ях','ы','ь','ию','ью','ю','ия','ья','я','ок', 'мва', 'яна', 'ровать','ег','ги','га','сть','сти'),
     5 => array ('чно', 'еко', 'соко', 'боко', 'роко', 'имо', 'мно', 'жно', 'жко','ело','тно','льно','здо','зко','шо','хо','но'),
     6 => array ('чуть','много','мало','еро','вое','рое','еро','сти','одной','двух','рех','еми','яти','ьми','ати',
    'дного','сто','ста','тысяча','тысячи','две','три','одна','умя','тью','мя','тью','мью','тью','одним'),
     7 => array ('более','менее','очень','крайне','скоре','некотор','кажд','други','котор','когд','однак',
    'если','чтоб','хот','смотря','как','также','так','зато','что','или','потом','эт','тог','тоже','словно',
    'ежели','кабы','коли','ничем','чем'),
     8 => array ('в','на','по','из')
    );
    
    $res=array();
    $string=mb_strtolower($string);
    $words=explode(' ',$string);
    foreach ($words as $wk=>$w){
        $len_w=mb_strlen($w);
     foreach ($groups as $gk=>$g){
      foreach ($g as $part){
        $len_part=mb_strlen($part);
       if (
            mb_substr($w,-$len_part)==$part && $res[$wk][$gk]<$len_part //любая часть речи, окончания
            || mb_strpos($w,$part)>=(round(2*$len_w)/5) && $gk==2 //причастие, от 40% и правее от длины слова
            || mb_substr($w,0,$len_part)==$part && $res[$wk][$gk]<$len_part && $gk==7 //союз, сначала слОва
            || $w==$part //полное совпадение
          ) {
             if ($w!=$part) $res[$wk][$gk]=mb_strlen($part); else $res[$wk][$gk]=99;
            }
    
      }
     }
    if (!isset($res[$wk][$gk])) $res[$wk][$gk]=0;
    }
    $result=array();
    foreach($res as $r) {
     arsort($r);
     array_push($result,key($r));
    }
    return $result;
}
//*************************************************************************************************************************
// Класс для ввода текста на картинку
//
// Берем какую-нибудь картинку
//$ttfImg = new ttfTextOnImage('images/hlwn.jpg');
//      
//// Пишем шрифтом Scrawn размером 64 пункта бордовым цветом с 80%-ой прозрачностью
//$ttfImg->setFont('files/fonts/scra.ttf', 64, "#800000", 80);      
//$ttfImg->writeText(40, 570, "Happy halloween!");
//
//// Шрифтом Constantin размером 15 пунктов оранжевым цветом с 90%-ой прозрачностью
//$ttfImg->setFont('files/fonts/constan.ttf', 15, "#ff8200", 90);      
//
//// Хотим написать много, поэтому сначала отформатируем наш текст
//$message = $ttfImg->textFormat(400, 500,
//"Хеллоуин (англ. Halloween) — преимущественно американский праздник, празднуется в ночь с 31 октября на 1 ноября.
//
//Также упоминается как «канун Дня всех святых». Праздник корнями уходит к старинному кельтскому празднеству Самайн.");
//
//// Пишем (чуть-чуть наклоним)
//$ttfImg->writeText(40, 100, $message, 5);
//
//// и вывод в файл
//$ttfImg->output('images/postcard.jpg');
//
function data_convert_for_user($data,$razd=' ')
{
    if ($data!=''){
    $data=date('Y-m-d H:i:s',strtotime($data));
    $yea=substr($data, 0, 4);
    $mon=substr($data, 5, 2);
    $day=substr($data,8, 2);
    
        if ($razd==' '){
            if ($mon=='01') {$mon='января';}
            elseif ($mon=='02') {$mon='февраля';}
            elseif ($mon=='03') {$mon='марта';}
            elseif ($mon=='04') {$mon='апреля';}
            elseif ($mon=='05') {$mon='мая';}
            elseif ($mon=='06') {$mon='июня';}
            elseif ($mon=='07') {$mon='июля';}
            elseif ($mon=='08') {$mon='августа';}
            elseif ($mon=='09') {$mon='сентября';}
            elseif ($mon=='10') {$mon='октября';}
            elseif ($mon=='11') {$mon='ноября';}
            elseif ($mon=='12') {$mon='декабря';}
        }
    
    return $day.$razd.$mon.$razd.$yea;
    } else {return '';}
}
function widthForStringUsingFontSize($string, $font, $fontSize)
{
     $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
     $characters = array();
     for ($i = 0; $i < strlen($drawingString); $i++) {
         $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
     }
     $glyphs = $font->glyphNumbersForCharacters($characters);
     $widths = $font->widthsForGlyphs($glyphs);
     $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
     return $stringWidth;
 }


class ttfTextOnImage
{  
  // Качество jpg по-умолчанияю
  public   $jpegQuality = 100;      
  
  // Каталог шрифтов
  public   $ttfFontDir   = 'ttf';  
  
  private $ttfFont    = false;
  private $ttfFontSize  = false;
    
  private $hImage      = false;
  private $hColor      = false;

  public function __construct($imagePath)
  {
    if (!is_file($imagePath) || !list(,,$type) = @getimagesize($imagePath)) return false;
        
   
    switch ($type)
    {      
      case 1:  $this->hImage = imagecreatefromgif($imagePath);  break;
      case 2:  $this->hImage = imagecreatefromjpeg($imagePath);  break;
      case 3:  $this->hImage = imagecreatefrompng($imagePath);  break;        
      default: $this->hImage = false;
    }
  }
  
  public function __destruct()
  {
    if ($this->hImage) imagedestroy($this->hImage);
  }
  
  /**
   * Устанавливает шрифт
   *
   */  
  public function setFont($font, $size = 14, $color = false, $alpha = false)
  {
    if (!is_file($font) && !is_file($font = $this->ttfFontDir.'/'.$font))
    return false;
    
    $this->ttfFont     = $font;
    $this->ttfFontSize   = $size;
    
    if ($color) $this->setColor($color, $alpha);
  }
  
  /**
   * Пишет текст
   *
   */    
  public function writeText ($x, $y, $text, $angle = 0,$color_1=255,$color_2=255,$color_3=255,$proz=70)
  {
    if (!$this->ttfFont || !$this->hImage || !$this->hColor) return false;
    $content_bounds = bounds($text, $this->ttfFont, $this->ttfFontSize, 0);
    
    imagesavealpha($this->hImage, true);
    imagefilledrectangle($this->hImage, 0, $y-20 , $x+$content_bounds['width']+500, $y + $this->ttfFontSize+20,imagecolorallocatealpha($this->hImage,$color_1, $color_2, $color_3, $proz));

    imagettftext(
      $this->hImage,
      $this->ttfFontSize, $angle, $x, $y + $this->ttfFontSize,
      $this->hColor, $this->ttfFont, $text);  
  }
  
  /**
   * Форматирует текст (согласно текущему установленному шрифту),
   * что бы он не вылезал за рамки ($bWidth, $bHeight)
   * Убирает слишком длинные слова
   */
  public function textFormat($bWidth, $bHeight, $text)
  {
    // Если в строке есть длинные слова, разбиваем их на более короткие
    // Разбиваем текст по строкам
    
    $strings   = explode("\n",
      preg_replace('!([^\s]{24})[^\s]!su', '\\1 ',
        str_replace(array("\r", "\t"),array("\n", ' '), $text)));        
        
    $textOut   = array(0 => '');
    $i = 0;
          
    foreach ($strings as $str)
    {
      // Уничтожаем совокупности пробелов, разбиваем по словам
      $words = array_filter(explode(' ', $str));
      
      foreach ($words as $word)
      {
        // Какие параметры у текста в строке?
        $sizes = imagettfbbox($this->ttfFontSize, 0, $this->ttfFont, $textOut[$i].$word.' ');  
        
        // Если размер линии превышает заданный, принудительно
        // перескакиваем на следующую строку
        // Иначе пишем на этой же строке
        if ($sizes[2] > $bWidth) $textOut[++$i] = $word.' '; else $textOut[$i].= $word.' ';
        
        // Если вышли за границы текста по вертикали, то заканчиваем
        if ($i*$this->ttfFontSize >= $bHeight) break(2);
      }
      
      // "Естественный" переход на новую строку
      $textOut[++$i] = ''; if ($i*$this->ttfFontSize >= $bHeight) break;
    }
    
    return implode ("\n", $textOut);
  }
  
  /**
   * Устанваливет цвет вида #34dc12
   *
   */
  public function setColor($color, $alpha = false)
  {
    if (!$this->hImage) return false;
    
    list($r, $g, $b) = array_map('hexdec', str_split(ltrim($color, '#'), 2));
    
    return $alpha === false ?
      $this->hColor = imagecolorallocate($this->hImage, $r+1, $g+1, $b+1) :
      $this->hColor = imagecolorallocatealpha($this->hImage, $r+1, $g+1, $b+1, $alpha);    
  }
  
  /**
   * Выводит картинку в файл. Тип вывода определяется из расширения.
   *
   */
  public function output ($target='', $replace = true)
  {
    if ($target==''){
        imagejpeg($this->hImage);
        return true;
    }
    
    if (is_file ($target) && !$replace) return false;
    $ext = strtolower(substr($target, strrpos($target, ".") + 1));    
    switch ($ext)
    {
      case "gif":
           imagegif ($this->hImage, $target);
             
        break;
                
      case "jpg" :
      case "jpeg":
        imagejpeg($this->hImage, $target, $this->jpegQuality);
        break;
        
      case "png":
           imagepng($this->hImage, $target);
        break;
        
      default: return false;
    }
    return true;     
  }
}

//***************************************************************************************************************
// Вычисляет ширину блока со шрифтом
function bounds($text,$fontFile,$fontSize,$fontAngle) {
    //ЧТОБЫ НЕ БЫЛО ОШИБКИ С РУССКИМИ БУКВАМИ - ЗАМЕНИ ВЕРСИЮ PHP НА 5.6
    $rect = imagettfbbox($fontSize,$fontAngle,$fontFile,$text); 
    $minX = min(array($rect[0],$rect[2],$rect[4],$rect[6])); 
    $maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6])); 
    $minY = min(array($rect[1],$rect[3],$rect[5],$rect[7])); 
    $maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7])); 

    return array( 
        "left"   => abs($minX) - 1, 
        "top"    => abs($minY) - 1, 
        "width"  => $maxX - $minX, 
        "height" => $maxY - $minY, 
        "box"    => $rect ); 
} 
//********************************************************
//Создание надписи и телефона в картинке
function create_img_width_text($img,$font,$text,$phone,$color1="#000000",$color2="#990000",$c1=255,$c2=255,$c3=255,$pr=80){
                  
    $image_info = getimagesize($img);
    
    $img_width=$image_info[0]-40;
    $ln=mb_strlen($text,'UTF-8');
    
    $font_size=100;
    $content_bounds = bounds($text, $font, $font_size, 0);
    
    //Опрделяем размер шрифта для вписывания в ширину
    while($content_bounds['width']>$img_width){
        $font_size--;
        $content_bounds = bounds($text, $font, $font_size, 0);
    }
    $phone_size=$ln/18*$font_size;
    $margin_left=($img_width-$content_bounds['width'])/2+20;
    //Берем какую-нибудь картинку
    $ttfImg = new ttfTextOnImage($img);
    
    // Пишем шрифтом Scrawn размером 64 пункта бордовым цветом с 80%-ой прозрачностью
    $ttfImg->setFont($font, $font_size, $color1, false);
    $ttfImg->writeText($margin_left, 20,$text,0,$c1,$c2,$c3,$pr);
    $ttfImg->setFont($font, $phone_size, $color1, false);     
    $ttfImg->writeText(12, $image_info[1]-$phone_size-10,$phone,0,$c1,$c2,$c3,$pr);
    $ttfImg->setFont($font, $phone_size, $color2, false);     
    $ttfImg->writeText(10, $image_info[1]-$phone_size-12,$phone,0,$c1,$c2,$c3,$pr);
    // и вывод в файл
    return $ttfImg->output();
}

// первая буква - заглавная
function mb_ufirst($word, $chr = "utf-8")
{
    return mb_strtoupper(mb_substr($word, 0, 1, $chr), $chr).
       mb_substr($word, 1, mb_strlen($word, $chr) - 1, $chr);
} 
//обрезка по границе слова
function substr_word($txt,$strart_len,$len,$code='CP1251'){
    if ($txt!=''){
        $key=@mb_strpos($txt,' ',$len,$code);
        $key2=@mb_strpos($txt,'.',$len,$code);
        if ($key2<$key){$key=$key2;}
        return mb_substr($txt,$strart_len,$key,$code);
    }else{
        return $txt;
    }
}

//Обновляем количество товара на складе, $update =1 - меняем в базе, $update=0 - выводим
function chk_kol_s_cat_from_id($s_cat_id,$update=1){
    $kol_all=0;
    $kol_all_minus=0;
    $sql = "SELECT m_postav_s_cat.kolvo
    				FROM m_tovar, m_postav_s_cat 
    					WHERE m_postav_s_cat.s_cat_id='"._DB($s_cat_id)."'
                        AND m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                        
                        GROUP BY m_postav_s_cat.id
                        
    	"; 
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        //print_rf($myrow);
        $kol_all=$kol_all+$myrow[0];
    }
    //echo $kol_all.'+++';
    
    
    $sql = "SELECT m_zakaz_s_cat_m_tovar.kolvo
    				FROM m_zakaz_s_cat_m_tovar, m_zakaz_s_cat
    					WHERE m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id
                        AND m_zakaz_s_cat.s_cat_id='"._DB($s_cat_id)."'
                        GROUP BY m_zakaz_s_cat_m_tovar.id
    	"; 
       // echo '<br /><br /><br />';
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        //print_rf($myrow);
        $kol_all_minus=$kol_all_minus+$myrow[0];
    } 
    //echo $kol_all_minus.'+++';
    $kol_all=$kol_all-$kol_all_minus;
    
    //exit;
    if ($update==1){
        $sql = "UPDATE s_cat 
        		SET  
        		
        			kolvo='"._DB($kol_all)."'
        	
        	WHERE id='"._DB($s_cat_id)."'
        ";
        //echo $sql;
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    }
    return $kol_all;
}

function log_remove_platezi($id){

    
    $sql = "INSERT into l_m_platezi_remove (
    				id,
                    a_admin_id,
    				data,
                    i_scheta_id,
                    summa,
                    tip,
                    a_menu_id,
                    id_z_p_p,
                    comments,
                    ostatok,
                    a_admin_id_info,
                    data_create
    			) (
                SELECT  m_platezi.id,
                    m_platezi.a_admin_id,
                    m_platezi.data,
                    m_platezi.i_scheta_id,
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
                )";
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    return mysql_insert_id();
    
    
    
}

//Добавление свойства
function add_prop($s_prop_name){
    $sql = "SELECT IF(COUNT(*)>0,(SELECT s_prop.id FROM s_prop WHERE s_prop.name='"._DB($s_prop_name)."' LIMIT 1),'')
    				FROM s_prop 
    					WHERE s_prop.name='"._DB($s_prop_name)."'
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br />'.$sql);
    $myrow = mysql_fetch_array($res);
    $s_prop_id=$myrow[0];
    if ($s_prop_id==''){
        $sql_insert = "INSERT into s_prop (
        				chk_active,
        				name
        			) VALUES (
        				'1',
        				'"._DB($s_prop_name)."'
        )";
        
        $mt = microtime(true);
        $res = mysql_query($sql_insert) or die(mysql_error().'<br />'.$sql_insert);
        $s_prop_id = mysql_insert_id();
        
    }
    return $s_prop_id;
}


//Добавление значения свойства
function add_prop_val($s_prop_id,$s_prop_val,$s_cat_id){
    $sql = "SELECT IF(COUNT(*)>0,(SELECT s_prop_val.id
    				FROM s_prop_val 
    					WHERE s_prop_val.s_prop_id='"._DB($s_prop_id)."'
                        AND s_prop_val.val='"._DB($s_prop_val)."' LIMIT 1),'') 
    				FROM s_prop_val 
    					WHERE s_prop_val.s_prop_id='"._DB($s_prop_id)."'
                        AND s_prop_val.val='"._DB($s_prop_val)."'
    	"; 
    
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    $myrow = mysql_fetch_array($res);
    $s_prop_val_id=$myrow[0];
    
    if ($s_prop_val_id==''){
        $sql = "INSERT into s_prop_val (
        				s_prop_id,
        				val
        			) VALUES (
        				'"._DB($s_prop_id)."',
        				'"._DB($s_prop_val)."'
        )";
        $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
        $s_prop_val_id = mysql_insert_id();
        
    }
    
    $sql = "INSERT into s_cat_s_prop_val (
    				id1,
    				id2
    			) VALUES (
    				'"._DB($s_cat_id)."',
    				'"._DB($s_prop_val_id)."'
    )";
    
    $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
    
    return true;
}

//Перебор по файлам google drive
function retrieveAllFiles($service) {
  $result = array();
  $pageToken = NULL;

  do {
    try {
      $parameters = array();
      if ($pageToken) {
        $parameters['pageToken'] = $pageToken;
      }
      $files = $service->files->listFiles($parameters);

      $result = array_merge($result, $files->getFiles());
      $pageToken = $files->getNextPageToken();
    } catch (Exception $e) {
      $result['err_txt']= "An error occurred: " . $e->getMessage();
      $result['err']= "An error occurred";
      
      $pageToken = NULL;
    }
  } while ($pageToken);
  return $result;
}

//ФУНКЦИЯ CURL
function curl($url,$headers=array('Content-Type: application/atom+xml','GData-Version: 3.0'), $post = "",$CURLOPT_CUSTOMREQUEST='') {
	$curl = curl_init();
	$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
	curl_setopt($curl, CURLOPT_URL, $url);
	//The URL to fetch. This can also be set when initializing a session with curl_init().
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
	//The number of seconds to wait while trying to connect.
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
   // curl_setopt($curl, CURLOPT_HTTPHEADER, );

    if (is_array($post)){$post=http_build_query($post);}
	if ($post != "") {
		curl_setopt($curl, CURLOPT_POST, 50);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	}
	curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
    if ($CURLOPT_CUSTOMREQUEST!=''){
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $CURLOPT_CUSTOMREQUEST);
    }
	
	//The contents of the "User-Agent: " header to be used in a HTTP request.
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	//To follow any "Location: " header that the server sends as part of the HTTP header.
	curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
	//To automatically set the Referer: field in requests where it follows a Location: redirect.
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	//The maximum number of seconds to allow cURL functions to execute.
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	//To stop cURL from verifying the peer's certificate.
	$contents = curl_exec($curl);
	curl_close($curl);
	return $contents;
}




//Замена слов с окончанием на конце (т.е. когда нужно заменить слово "нотбук" он не менял слово "ноутбуках"...)
function repl_in_tag($txt1,$txt2,$text){

    $text=str_replace($txt1.' ',$txt2.' ',$text);
    $text=str_replace($txt1."&nbsp;",$txt2."&nbsp;",$text);
    $text=str_replace($txt1.'.',$txt2.'.',$text);
    $text=str_replace($txt1.',',$txt2.',',$text);
    $text=str_replace($txt1.';',$txt2.';',$text);
    $text=str_replace($txt1.'<',$txt2.'<',$text);
    $text=str_replace($txt1.'"',$txt2.'"',$text);
    $text=str_replace($txt1."'",$txt2."'",$text);
    $text=str_replace($txt1."?",$txt2."?",$text);
    $text=str_replace($txt1."!",$txt2."!",$text);
    $text=str_replace($txt1.":",$txt2.":",$text);
    if (mb_strrpos($text, $txt1, null, 'utf-8')+mb_strlen($txt1,'utf-8')==mb_strlen($text,'utf-8')){
        $text=str_replace($txt1,$txt2,$text);
    }
    return $text;
}
// ***********************************************************************************************************
// Функция перелнковки

    function perelinkovka($struktura,$html_code,$cur_key){
        
        foreach($struktura['keywords_arr'] as $key1 => $keywords_arr)
        {
            if ($cur_key!=$key1 and $struktura['chk_active'][$key1]=='1'){//исключаем перелинковку самого на себя
                foreach($keywords_arr as $key2 => $keyword1)
                {
                    if ($keyword1!=''){
                        if (mb_strstr($html_code,$keyword1,false,'utf-8')==true){
                            
                            
                            // $html_code
                            $skip_tags = array('img','a');
                            $find_replace = array($keyword1=>'<a title="'._IN($struktura['page_name'][$key1]).'" href="'.$struktura['url_all_full'][$key1].'">'.$keyword1.'</a>');
                            foreach($skip_tags as $v) {
                                $pt_arr[] = "<{$v}(?:\s[^>]+)?>.+</{$v}>";
                            }
                            $skip_pt = '#('.join('|',$pt_arr).')#isU';
                            
                            $parts = preg_split($skip_pt,$html_code,-1,1|2); $html_code='';
                            foreach($parts as $part) {
                               if(preg_match($skip_pt,$part)) $html_code .= $part;
                               else $html_code .= strtr($part,$find_replace); 
                            }
                            
                        }
                    }
                }
            }
        }
        return $html_code;
    }
    
//KeyWords generator
class KeyWordsGen
{
    var $origin_arr;
    var $modif_arr;
    var $min_word_length = 3;
 
function explode_str_on_words($text)
{
    $search = array ("'ё'",
                     "'<script[^>]*?>.*?</script>'si",  // Вырезается javascript
                     "'<[\/\!]*?[^<>]*?>'si",           // Вырезаются html-тэги
                     "'([\r\n])[\s]+'",                 // Вырезается пустое пространство
                     "'&(quot|#34);'i",                 // Замещаются html-элементы
                     "'&(amp|#38);'i",
                     "'&(lt|#60);'i",
                     "'&(gt|#62);'i",
                     "'&(nbsp|#160);'i",
                     "'&(iexcl|#161);'i",
                     "'&(cent|#162);'i",
                     "'&(pound|#163);'i",
                     "'&(copy|#169);'i",
                     "'&#(\d+);'e");
    $replace = array ("е",
                      " ",
                      " ",
                      "\\1 ",
                      "\" ",
                      " ",
                      " ",
                      " ",
                      " ",
                      chr(161),
                      chr(162),
                      chr(163),
                      chr(169),
                      "chr(\\1)");
    $text = preg_replace ($search, $replace, $text);
    $del_symbols = array(",", ".", ";", ":", "\"", "#", "\$", "%", "^",
                         "!", "@", "`", "~", "*", "-", "=", "+", "\\",
                         "|", "/", ">", "<", "(", ")", "&", "?", "¹", "\t",
                         "\r", "\n", "{","}","[","]", "'", "“", "”", "•",
                         "этих", "после", "перед", "вместо","заместо","других","прочих","другие","прочие","рублей","рубль","рубли",
                         "всех", "когда"
                         );
    $text = str_replace($del_symbols, array(" "), $text);
    $text = ereg_replace("( +)", " ", $text);
    $this->origin_arr = explode(" ", trim($text));
    return $this->origin_arr;
}
 
function count_words()
{
    $tmp_arr = array();
    foreach ($this->origin_arr as $val)
    {
        if (mb_strlen($val,'utf-8')>=$this->min_word_length)
        {
            $val = mb_strtolower($val,'utf-8');
            
            if (array_key_exists($val, $tmp_arr))
            {
                $tmp_arr[$val]++;
            }
            else
            {
                $tmp_arr[$val] = 1;
            }
        }
    }
    arsort ($tmp_arr);
    $this->modif_arr = $tmp_arr;
}
 
function get_keywords($text)
{
    $this->explode_str_on_words(($text));
    $this->count_words();
    $arr = array_slice($this->modif_arr, 0, 30);
    $str = "";
    foreach ($arr as $key=>$val)
    {
        if (mb_strlen($key,'utf-8')>3){
            if ($str!=''){$str.=', ';}
            $str .= $key;
       }
    }
    return trim($str);
}
}

//объединяем в таблицу]
function implode_a($array,$g_s='<tr><td>',$g_e='</td></tr>',$g_m='</td><td>') #Переводит массив в строку через запятую
{
   $results='';
	foreach ($array as $key => $value) {
	  $results.=$g_s.$key.$g_m.$value.$g_e;
	}
    return $results;
}
    


//Рандомизатор для случайного изменения текста по хэшу
function rand_md5($text,$max_value,$count_results){
    
    $arr_str=array();
    
    if ($text!='' and $max_value-0>1 and $count_results-0>0){
        $n=hexdec(substr(md5($text),-15,15));//ХЭШ ТЕКСТА В 10 системе счисления
        $txt_8='';
        while(round($n,0,PHP_ROUND_HALF_DOWN)>0){
            $k=$n % $max_value;
            $txt_8=$k.$txt_8;
            $n=($n-$k)/$max_value;
        }
        for($i=mb_strlen($txt_8,'utf-8')-1;$i>=0;$i--){
             $arr_str[$txt_8[$i]] = $txt_8[$i];
             if (count($arr_str)>=$count_results){
                break;
             }
        }
    }elseif ($max_value-0==1){
        $arr_str[0]=0;
    }
    return $arr_str;
}

//СКЛОНЕНИЕ
function morph_($txt){
    
    require_once( dirname(__FILE__) .'/class/morphy-0_3/src/common.php');
    $opts = array('storage' => PHPMORPHY_STORAGE_FILE,
    	'with_gramtab' => false,
    	'predict_by_suffix' => true, 
    	'predict_by_db' => true
    );


$dir = dirname(__FILE__).'/class/morphy-0_3/dicts/';
$dict_bundle = new phpMorphy_FilesBundle($dir, 'rus');

try {
	$morphy = new phpMorphy($dict_bundle, $opts);
} catch(phpMorphy_Exception $e) {
	die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
}

$word_one = mb_strtoupper($txt,'utf-8');

try {
	$base_form = $morphy->getBaseForm($word_one);
	$all_forms = $morphy->getAllForms($word_one);
	$pseudo_root = $morphy->getPseudoRoot($word_one);
	
	if(false === $base_form || false === $all_forms || false === $pseudo_root) {
		die("Can`t find or predict $word_one word");
	}
} catch(phpMorphy_Exception $e) {
	die('Error occured while text processing: ' . $e->getMessage());
}
return $all_forms;

}



///ПОЛУЧАЕМ ПЕРИОД
function get_period($period,$group){
    $res=array();
    //Указываем период
    if ($period=='year'){
        if ($group=='mounth'){
            
            for($i=0;$i<12;$i++){
                
                $m_=date('m')-(12-$i);//1 2 3 4 5 6 7 8 9 10 11 12
                    $y_raz=0;
                    if ($m_<=0){$m_=12+$m_;$y_raz=-1;}
                    
                $y_=date('Y')+$y_raz;
                
                $m_2=$m_+1;
                $y_2=$y_;
                if ($m_2>12){$m_2=$m_2-12;$y_2=$y_+1;}
                if (strlen($m_.'')==1){$m_='0'.$m_;}
                if (strlen($m_2.'')==1){$m_2='0'.$m_2;}
                $res['x_start'][$i]='01.'.$m_.'.'.$y_.' 00:00:00';
                $res['x_end'][$i]='01.'.$m_2.'.'.$y_2.' 00:00:00';
                $res['x_all'][$i]=$m_.'.'.$y_;
                
            }
        }
    }
    return $res;
}




//Получаем количество товара на складе
function getCOUNTtovarINsklad($s_cat_id){
    
    
    $sum=0;
    $sql="SELECT s_cat.name,
                (m_postav_s_cat.kolvo)
                    FROM m_tovar, m_postav_s_cat, s_cat
                    WHERE m_tovar.m_postav_s_cat_id=m_postav_s_cat.id
                    AND m_postav_s_cat.s_cat_id=s_cat.id
                    AND s_cat.id='"._DB($s_cat_id)."'
                    GROUP BY m_postav_s_cat.id
                    ";
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    for ($myrow = mysql_fetch_array($res); $myrow==true; $myrow = mysql_fetch_array($res))
    {
        $sum=$sum+$myrow[1];
    }
    
    $sql="SELECT s_cat.name,
                SUM(m_zakaz_s_cat_m_tovar.kolvo)
                    FROM m_zakaz_s_cat_m_tovar, m_zakaz_s_cat, s_cat
                    WHERE m_zakaz_s_cat_m_tovar.id1=m_zakaz_s_cat.id
                    AND m_zakaz_s_cat.s_cat_id=s_cat.id
                    AND s_cat.id='"._DB($s_cat_id)."'
                    
                    ";
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    $myrow = mysql_fetch_array($res);
    $sum=$sum-$myrow[1];
    return $sum;
}



//Получаем протакол
function get_protocol(){
    global $_SERVER;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }
    return $protocol;
}

## Создает CSV файл из переданных в массиве данных.
## @param array  $create_data  Массив данных из которых нужно созать CSV файл.
## @param string $file         Путь до файла 'path/to/test.csv'. Если не указать, то просто вернет результат.
## @return string/false        CSV строку или false, если не удалось создать файл.
## ver 2
function kama_create_csv_file( $create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n" ){

	if( ! is_array($create_data) )
		return false;

	if( $file && ! is_dir( dirname($file) ) )
		return false;

	// строка, которая будет записана в csv файл
	$collected_rows = array() ;

	// перебираем все данные
	foreach( $create_data as $row ){
		$cols = array();

		foreach( $row as $col_val ){
			// строки должны быть в кавычках ""
			// кавычки " внутри строк нужно предварить такой же кавычкой "
			if( $col_val && preg_match('/[",;\r\n]/', $col_val) ){
				// поправим перенос строки
				if( $row_delimiter === "\r\n" ){
					$col_val = str_replace( "\r\n", '\n', $col_val );
					$col_val = str_replace( "\r", '', $col_val );
				}
				elseif( $row_delimiter === "\n" ){
					$col_val = str_replace( "\n", '\r', $col_val );
					$col_val = str_replace( "\r\r", '\r', $col_val );
				}

				$col_val = str_replace( '"', '""', $col_val ); // предваряем "
				$col_val = '"'. $col_val .'"'; // обрамляем в "
			}

			$cols[] = $col_val; // добавляем колонку в данные
		}

		$collected_rows[] = implode( $col_delimiter, $cols ); // добавляем строку в данные
	}

	$CSV_str = implode( $row_delimiter, $collected_rows ); // объединяем строки

	// задаем кодировку windows-1251 для строки
	if( $file ){
		$CSV_str = iconv( "UTF-8", "cp1251",  $CSV_str );

		// создаем csv файл и записываем в него строку
		$done = file_put_contents( $file, $CSV_str );

		if( $done )
			return $CSV_str;
		return false;
	}

	return $CSV_str;

}

## Читает CSV файл и возвращает данные в виде массива.
## @param string $file_path Путь до csv файла.
## string $col_delimiter Разделитель колонки (по умолчанию автоопределине)
## string $row_delimiter Разделитель строки (по умолчанию автоопределине)
## ver 6
function kama_parse_csv_file( $file_path, $file_encodings='UTF-8', $col_delimiter = '', $row_delimiter = "" ){

	if( ! file_exists($file_path) )
		return false;

	$cont = trim( file_get_contents( $file_path ) );
    $cont=charset_x_win($cont);
    //echo mb_detect_encoding(file_get_contents( $file_path ), array('utf-8', 'cp1251'));
	$encoded_cont = mb_convert_encoding( $cont, 'UTF-8','cp1251' );//
//print_rf($encoded_cont);
	unset( $cont );

	// определим разделитель
	if( ! $row_delimiter ){
		$row_delimiter = "\r\n";
		if( false === strpos($encoded_cont, "\r\n") )
			$row_delimiter = "\n";
	}

	$lines = explode( $row_delimiter, trim($encoded_cont) );
	$lines = array_filter( $lines );
	$lines = array_map( 'trim', $lines );

	// авто-определим разделитель из двух возможных: ';' или ','. 
	// для расчета берем не больше 30 строк
	if( ! $col_delimiter ){
		$lines10 = array_slice( $lines, 0, 30 );

		// если в строке нет одного из разделителей, то значит другой точно он...
		foreach( $lines10 as $line ){
			if( ! strpos( $line, ',') ) $col_delimiter = ';';
			if( ! strpos( $line, ';') ) $col_delimiter = ',';

			if( $col_delimiter ) break;
		}

		// если первый способ не дал результатов, то погружаемся в задачу и считаем кол разделителей в каждой строке.
		// где больше одинаковых количеств найденного разделителя, тот и разделитель...
		if( ! $col_delimiter ){
			$delim_counts = array( ';'=>array(), ','=>array() );
			foreach( $lines10 as $line ){
				$delim_counts[','][] = substr_count( $line, ',' );
				$delim_counts[';'][] = substr_count( $line, ';' );
			}

			$delim_counts = array_map( 'array_filter', $delim_counts ); // уберем нули

			// кол-во одинаковых значений массива - это потенциальный разделитель
			$delim_counts = array_map( 'array_count_values', $delim_counts );

			$delim_counts = array_map( 'max', $delim_counts ); // берем только макс. значения вхождений

			if( $delim_counts[';'] === $delim_counts[','] )
				return array('Не удалось определить разделитель колонок.');

			$col_delimiter = array_search( max($delim_counts), $delim_counts );
		}

	}

	foreach( $lines as $key => $line ){
	    $data[] = str_getcsv( $line, $col_delimiter, '"' ,'/"' ); // linedata
		unset( $lines[$key] );
	}

	return $data;
}
//Проверка строки на json
function isJSON($string) {
    return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
}

?>