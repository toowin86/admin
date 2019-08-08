<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<style>
    .__mod_link_extractor{font-size: 16px;margin: 0 0 0 20px;}
    .__mod_link_extractor_in{margin: 10px 0;}
    .__mod_link_extractor_com{margin: 10px 0;}
    .__mod_link_extractor_options p{margin: 10px 0;}
    .__mod_link_extractor_options label{padding: 0 15px;}
        .__mod_link_extractor_in_text{width: 100%; height: 220px;padding: 5px;}
        .__mod_link_extractor_out_text{width: 100%; height: 220px;padding: 5px;}
</style>
<?php
$links_txt='';
$in_=_GP('in');
if (_GP('tip')=='4' and $in_!=''){
    function get_links(&$body)
    {
     $pattern  = "/((@import\s+[\"'`]([\w:?=@&\/#._;-]+)[\"'`];)|";
     $pattern .= "(:\s*url\s*\([\s\"'`]*([\w:?=@&\/#._;-]+)";
     $pattern .= "([\s\"'`]*\))|<[^>]*\s+(src|href|url)\=[\s\"'`]*";
     $pattern .= "([\w:?=@&\/#._;-]+)[\s\"'`]*[^>]*>))/i";
     preg_match_all($pattern,$body,$matches);
     return (is_array($matches))?$matches:FALSE;
    }
    function x_array_merge($arr1,$arr2)
    {
     for($i=0;$i<count($arr1);$i++) {$arr[$i]= ($arr1[$i] == '')?$arr2[$i]:$arr1[$i];}
     return $arr;
    } 
    $heap = get_links($in_);
    $links = x_array_merge($heap[3],x_array_merge($heap[5],$heap[8]));
    
    foreach($links as $key => $val){
        if ($links_txt!=''){$links_txt.="\n";}
        $links_txt.=$val;
    }
}
?>

<div class="__mod_link_extractor">
    <h1>Извлечение из текста</h1>
    <form class="__mod_link_extractor_options" action="?tip=4" method="post">
        <p><input name="tip" type="radio" value="1" id="options_tip1" checked="checked" /><label for="options_tip1"><strong>Домен</strong> # var regexp = /(http[s]?:\/\/|ftp:\/\/)?(www\.)?[a-zA-Zа-яА-Я0-9-]+\.(рф|ru|info|io|ua|moskow|com|org|net|io|mil|edu|ca|co.uk|com.au|gov)/g;</label></p>
        <p><input name="tip" type="radio" value="3" id="options_tip3" /><label for="options_tip3"><strong>email</strong> # var regexp = /([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}/gi;</label></p>
        <p><input name="tip" type="radio" value="4" id="options_tip4" /><label for="options_tip4"><strong>URLs</strong></label></p>
        <p><input name="tip" type="radio" value="5" id="options_tip5" /><label for="options_tip5"><strong>Цифры</strong> # content.replace(/[^\d]/gi,"");</label></p>
        <p><input name="tip" type="radio" value="6" id="options_tip6" /><label for="options_tip6"><strong>Удалить теги</strong> # content.replace(/<[^>]*>/g, '');</label></p>
    
        <div class="__mod_link_extractor_in">
            <textarea class="__mod_link_extractor_in_text" name="in" placeholder="Введите текст для извлечения url"><?=_IN($in_);?></textarea>
        </div>
        <div class="__mod_link_extractor_out">
            <textarea class="__mod_link_extractor_out_text" placeholder="Результат"><?=$links_txt;?></textarea>
        </div>
        <div class="__mod_link_extractor_com">
            <center><span class="btn_orange __mod_link_extractor_send">Извлечь</span></center>
        </div>
    </form>
</div>
