 
//Нули перед числом
function PrefInt(number, len) {
 if ((number+'').length < len)
 {
    return (Array(len).join('0') + number).slice(-len); // тут было у вас lenght которое не определено поэтому и возвращалось число без обрезания
 }else{
    return number;
 }
} 
//удаляем одинаковые элементы массива
function doSmth(a) {
  for (var q=1, i=1; q<a.length; ++q) {
    if (a[q] !== a[q-1]) {
      a[i++] = a[q];
    }
  }

  a.length = i;
  return a;
}

function checkTime(i)
{
    if (i<10)
    {
    i="0" + i;
    }
    return i;
}
//Текущая дата
function date(tp,msec){
    tp = tp || 'd.m.Y H:i:s';
    msec = msec || '';
    if (msec!=''){
        var d=new Date(msec); 
    }else{
        var d=new Date(); 
    }
    
    var day=checkTime(d.getDate());
    var month=checkTime(d.getMonth() + 1);
    var year=d.getFullYear();
    var h_=checkTime(d.getHours());
    var m_=checkTime(d.getMinutes());
    var s_=checkTime(d.getSeconds());
    
    if (tp=='d.m.Y H:i:s'){ return day+'.'+month+'.'+year+' '+h_+':'+m_+':'+s_;}
    if (tp=='d.m.Y H:i'){ return day+'.'+month+'.'+year+' '+h_+':'+m_;}
    if (tp=='d.m.Y'){ return day+'.'+month+'.'+year;}
    
    if (tp=='Y-m-d H:i:s'){ return year+'.'+month+'.'+day+' '+h_+':'+m_+':'+s_;}
    if (tp=='Y-m-d H:i'){ return year+'.'+month+'.'+day+' '+h_+':'+m_;}
    if (tp=='Y-m-d'){ return year+'.'+month+'.'+day;}
}


//копирование текста в буфер
function copyText(text){
  function selectElementText(element) {
    if (document.selection) {
      var range = document.body.createTextRange();
      range.moveToElementText(element);
      range.select();
    } else if (window.getSelection) {
      var range = document.createRange();
      range.selectNode(element);
      window.getSelection().removeAllRanges();
      window.getSelection().addRange(range);
    }
  }
  var element = document.createElement('DIV');
  element.textContent = text;
  document.body.appendChild(element);
  selectElementText(element);
  document.execCommand('copy');
  element.remove();
}
 
//Определение размера окна
function  getPageSize(){
       var xScroll, yScroll;

       if (window.innerHeight && window.scrollMaxY) {
               xScroll = document.body.scrollWidth;
               yScroll = window.innerHeight + window.scrollMaxY;
       } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
               xScroll = document.body.scrollWidth;
               yScroll = document.body.scrollHeight;
       } else if (document.documentElement && document.documentElement.scrollHeight > document.documentElement.offsetHeight){ // Explorer 6 strict mode
               xScroll = document.documentElement.scrollWidth;
               yScroll = document.documentElement.scrollHeight;
       } else { // Explorer Mac...would also work in Mozilla and Safari
               xScroll = document.body.offsetWidth;
               yScroll = document.body.offsetHeight;
       }

       var windowWidth, windowHeight;
       if (self.innerHeight) { // all except Explorer
               windowWidth = self.innerWidth;
               windowHeight = self.innerHeight;
       } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
               windowWidth = document.documentElement.clientWidth;
               windowHeight = document.documentElement.clientHeight;
       } else if (document.body) { // other Explorers
               windowWidth = document.body.clientWidth;
               windowHeight = document.body.clientHeight;
       }

       // for small pages with total height less then height of the viewport
       if(yScroll < windowHeight){
               pageHeight = windowHeight;
       } else {
               pageHeight = yScroll;
       }

       // for small pages with total width less then width of the viewport
       if(xScroll < windowWidth){
               pageWidth = windowWidth;
       } else {
               pageWidth = xScroll;
       }

       return [pageWidth,pageHeight,windowWidth,windowHeight];
}

function is_array( mixed_var ) {
	return ( mixed_var instanceof Array );
}
//Куки
function setCookie(name, value, options) {
  options = options || {};
  var expires = options.expires;
  if (typeof expires == "number" && expires) {
    var d = new Date();
    d.setTime(d.getTime() + expires * 1000);
    expires = options.expires = d;
  }
  if (expires && expires.toUTCString) {
    options.expires = expires.toUTCString();
  }
  value = encodeURIComponent(value);
  var updatedCookie = name + "=" + value;
  for (var propName in options) {
    updatedCookie += "; " + propName;
    var propValue = options[propName];
    if (propValue !== true) {
      updatedCookie += "=" + propValue;
    }
  }
  document.cookie = updatedCookie;
}

jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
};

//Загрузка
function loading(v){
    if (v==1){
        $('body').css({'overflow':'hidden'}).append('<div class="loadind_div_bg" style="position: fixed;background-color: rgba(30,30,30,0.8); width: 100%; height: 100%; z-index:9999;left: 0; top:0; overflow: no-display;"><img style="position: absolute; top:50%; left:50%;" src="i/loading_30_1e1e1e.gif" /></div>');
    }//,"margin-right": '17px'
    else{
        $('.loadind_div_bg').detach();
        $('body').css({'overflow':'auto'});
    }
}
// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}


//Маска
$.jMaskGlobals = {
    nonInput: 'td,span,div', 
    dataMask: false, 
    watchInterval: 100000,
    watchInputs: false, 
    watchDataMask: false,
    byPassKeys: [9, 16, 17, 18, 36, 37, 38, 39, 40, 91],
    translation: {
      '0': {pattern: /\d/},
      '9': {pattern: /\d/, optional: true},
      '#': {pattern: /\d/, recursive: true},
      'A': {pattern: /[a-zA-Z0-9]/},
      'S': {pattern: /[a-zA-Z]/}
    }
  };

// Strip HTML and PHP tags from a string
function strip_tags( str ){
	return str.replace(/<\/?[^>]+>/gi, '');
}


//Дата посещения
function data_visit_update(){
    var timerId = setTimeout(function(){
        var data_=new Object();
        data_['_t']='last_visit';
    
        $.ajax({
        	"type": "POST",
        	"url": "ajax/_admin.php",
        	"dataType": "text",
        	"data":data_,
        	"success":function(data,textStatus){
        	   data_visit_update();
        	}
        });
        
    }, 10000);
}
//IMPLODE
function implode( glue, pieces ) {
	return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}

//проверка на json
function is_json(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}
// Количество элементов массива
function count(array) {var cnt=0; for(var i in array) { if (i) {cnt++;}} return cnt;}

//перебор селоктора в массив
function sel_in_array(sel_,type,dt) {
    type = type || "text";
    dt = dt || "";
    var ret_arr = new Array(sel_.size()); 
    sel_.each(function(i) {
        if (type=='text') {ret_arr[i]=$(this).text();}
        if (type=='html') {ret_arr[i]=$(this).html();}
        if (type=='val') {ret_arr[i]=$(this).val();}
        if (type=='data') {ret_arr[i]=$(this).data(dt);}
        if (type=='css') {ret_arr[i]=$(this).css(dt);}
        if (type=='attr') {ret_arr[i]=$(this).attr(dt);}
    });
    return ret_arr;
}

//разбивка по 3
function gap(str) {
      var separator=separator||' ';
      str=str.replace(/[ ]/g,'');
	  return str.replace(/\d(?=(?:\d{3})+\b)/g, "$&" + (separator)) ;
}
//проверка на наличие в массиве
function in_array(needle, haystack, strict) {
	var found = false, key, strict = !!strict;
	for (key in haystack) {
		if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
			found = true;
			break;}
	}
	return found;
}
//поиск в массиве
function array_search( needle, haystack, strict ) {
	var strict = !!strict;
	for(var key in haystack){
		if( (strict && haystack[key] === needle) || (!strict && haystack[key] == needle) ){
			return key;
		}
	}
	return false;
}
//Окончание слова ##($int_,$zer='ов',$one='',$two='а')
function end_word(int_,zer,one,two)
{
    
	int_=int_+'';
    
	var $arr=int_.split();
	var $simv=int_[int_.length-1];
    var $simv2=int_[int_.length-2];
   
    if ($simv2!='1'){// toowin86 12-06-13
       
    	if ($simv=='0' || $simv=='5' || $simv=='6' || $simv=='7' || $simv=='8' || $simv=='9')
    		{return(zer);}
    	else if ($simv=='1')
    		{return(one);}
    	else if ($simv=='2' || $simv=='3' || $simv=='4')
    		{return(two);}
    	else 
    		{return('');}
            }// toowin86 12-06-13
     else{ // toowin86 12-06-13
        return(zer);// toowin86 12-06-13
     }   // toowin86 12-06-13
}


// ВВОД ТОЛЬКО ДРОБНЫХ ЦИФР
jQuery.fn.float_ =
function()
{
    return this.each(function()
    {
        $(this).keydown(function(e)
        {
            var key = e.charCode || e.keyCode || 0;
           
            return (
               (key == 8 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               (key == 9 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               (key == 46 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               (key == 190 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               ((key >= 37 && key <= 40) && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               ((key >= 48 && key <= 57) && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               ((key >= 96 && key <= 105) && e.shiftKey==false && e.ctrlKey==false && e.altKey==false) );
        });
    });
};
// ВВОД ТОЛЬКО ЦИФР
jQuery.fn.integer_ =
function()
{
    return this.each(function()
    {
        $(this).keydown(function(e)
        {
            var key = e.charCode || e.keyCode || 0;
           
            return (
               (key == 8 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               (key == 9 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               (key == 46 && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               ((key >= 37 && key <= 40) && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               ((key >= 48 && key <= 57) && e.shiftKey==false && e.ctrlKey==false && e.altKey==false)||
               ((key >= 96 && key <= 105) && e.shiftKey==false && e.ctrlKey==false && e.altKey==false) );
        });
    });
};

//Сериализация формы
$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

//Окна
function alert_m(text_,callback,style_,time,w,after,over)
{
    callback = callback || "";
    time = time || 1500;
    style_ = style_ || 'ok';
    w=w || '';
    after=after || '';
    
    over=over || '';
    if (over=='1'){over=false;}else{over=true;}
    
    if (w!=''){w=' style="width:'+w+'px"';}
    
    $.arcticmodal({
        closeOnOverlayClick: over,
        openEffect: {
            type: 'fade',
            speed: 100
        },
        content:    '<div'+w+' class="modal_'+style_+'">'
                    +'<div class="arcticmodal-close" title="Закрыть">X</div>'
                    +text_
                    +'</div>',
        overlay: {css: {backgroundImage: 'url("i/bg_top.jpg")',opacity: 0.7}},
        
        afterOpen: function(data, el) {
            if (typeof after == 'function'){after();}
        },
        
        afterClose: function(data, el) {
            if (typeof callback == 'function'){callback();}
        }    
    });
    
    if (time!='none')
    {
        setTimeout(function(){$('.modal_'+style_).arcticmodal('close');}, time);
    }
    
}

//Валидность email
function isEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 
//MD5
var md5=new function(){
  var l='length',
  h=[
   '0123456789abcdef',0x0F,0x80,0xFFFF,
    0x67452301,0xEFCDAB89,0x98BADCFE,0x10325476
  ],
  x=[
    [0,1,[7,12,17,22]],
    [1,5,[5, 9,14,20]],
    [5,3,[4,11,16,23]],
    [0,7,[6,10,15,21]]
  ],
  A=function(x,y,z){
    return(((x>>16)+(y>>16)+((z=(x&h[3])+(y&h[3]))>>16))<<16)|(z&h[3])
  },
  B=function(s){
    var n=((s[l]+8)>>6)+1,b=new Array(1+n*16).join('0').split('');
    for(var i=0;i<s[l];i++)b[i>>2]|=s.charCodeAt(i)<<((i%4)*8);
    return(b[i>>2]|=h[2]<<((i%4)*8),b[n*16-2]=s[l]*8,b)
  },
  R=function(n,c){return(n<<c)|(n>>>(32-c))},
  C=function(q,a,b,x,s,t){return A(R(A(A(a,q),A(x,t)),s),b)},
  F=function(a,b,c,d,x,s,t){return C((b&c)|((~b)&d),a,b,x,s,t)},
  G=function(a,b,c,d,x,s,t){return C((b&d)|(c&(~d)),a,b,x,s,t)},
  H=function(a,b,c,d,x,s,t){return C(b^c^d,a,b,x,s,t)},
  I=function(a,b,c,d,x,s,t){return C(c^(b|(~d)),a,b,x,s,t)},
  _=[F,G,H,I],
  S=(function(){
    with(Math)for(var i=0,a=[],x=pow(2,32);i<64;a[i]=floor(abs(sin(++i))*x));
    return a
  })(),
  X=function (n){
    for(var j=0,s='';j<4;j++)
      s+=h[0].charAt((n>>(j*8+4))&h[1])+h[0].charAt((n>>(j*8))&h[1]);
    return s
  };
  return function(s){
    var $=B(''+s),a=[0,1,2,3],b=[0,3,2,1],v=[h[4],h[5],h[6],h[7]];
    for(var i,j,k,N=0,J=0,o=[].concat(v);N<$[l];N+=16,o=[].concat(v),J=0){
      for(i=0;i<4;i++)
        for(j=0;j<4;j++)
          for(k=0;k<4;k++,a.unshift(a.pop()))
            v[b[k]]=_[i](
              v[a[0]],
              v[a[1]],
              v[a[2]],
              v[a[3]],
              $[N+(((j*4+k)*x[i][1]+x[i][0])%16)],
              x[i][2][k],
              S[J++]
            );
      for(i=0;i<4;i++)
        v[i]=A(v[i],o[i]);
    };return X(v[0])+X(v[1])+X(v[2])+X(v[3]);
}};
//Переводим при ошибки в раскладке
function in_barcode(th)
{
        var L = {
            'й': 'q', 'ц': 'w', 'у': 'e', 'к': 'r', 'е': 't', 'н': 'y', 
            'г': 'u', 'ш': 'i', 'щ': 'o', 'з': 'p', 'х': '', 'ъ': '', 
            'ф': 'a', 'ы': 's', 'в': 'd', 'а': 'f', 'п': 'g', 'р': 'h', 
            'о': 'j', 'л': 'k', 'д': 'l', 'ж': ';', 'э': '', 'я': 'z', 
            'ч': 'x', 'с': 'c', 'м': 'v', 'и': 'b', 'т': 'n', 'ь': 'm', 
            'б': ',', 'ю': '.', '.': '', 'Й': 'Q', 'Ц': 'W', 'У': 'E', 
            'К': 'R', 'Е': 'T', 'Н': 'Y', 'Г': 'U', 'Ш': 'I', 'Щ': 'O', 
            'З': 'P', 'Х': '', 'Ъ': '', 'Ф': 'A', 'Ы': 'S', 'В': 'D',
            'А': 'F', 'П': 'G', 'Р': 'H', 'О': 'J', 'Л': 'K', 'Д': 'L',
            'Ж': ';', 'Э': '', 'Я': 'Z', 'Ч': 'X', 'С': 'C', 'М': 'V',
            'И': 'B', 'Т': 'N', 'Ь': 'M', 'Б': '', 'Ю': '', '.': '',
            '1': '1', '2': '2', '3': '3', '4': '4', '5': '5', '6': '6',
            '7': '7', '8': '8', '9': '9', '0': '0', '-': '-', '_': '_',
            ' ': '', '/': '',  '+': '', '~': '','Ё': '','ё': '',
            '!': '1', '@': '2', '#': '3', '$': '4', '%': '5', '^': '6',
            '&': '7', '*': '8', '(': '9', ')': '0', '"': '2', '№': '3',
            ';': '4', '%': '5', ':': '6', '?': '7', '=': '', '\\': ''
            
        };
        var result='', ch, next, cnt=-1;
        var next;
        for (var i = 0; i < th.length; i++) {
          ch=th.charAt(i);
          next = th.charAt(i+1);
          next=ch.toUpperCase() === ch?next && next.toUpperCase() === next?2:1:0;

          if (!ch.match(/[a-z\d]/i)) {
             ch=L[ch.toLowerCase()];
             if (ch && next) ch=next==2?ch.toUpperCase():ch.substr(0,1).toUpperCase()+ch.substring(1);
          }
          if (ch) {
            result += (cnt>0?'-':'')+ch;
            cnt=0;
          } else if (cnt>-1) cnt++;
        } 
        return result;
}
//Переводим русский в английский
function ru_us(th)
{
        var L = {
            "А" : "A", "Б" : "B", "В" : "V", "Г" : "G", 
			"Д" : "D", "Е" : "E", "Ё" : "E", "Ж" : "J", "З" : "Z", "И" : "I", 
			"Й" : "Y", "К" : "K", "Л" : "L", "М" : "M", "Н" : "N", 
			"О" : "O", "П" : "P", "Р" : "R", "С" : "S", "Т" : "T", 
			"У" : "U", "Ф" : "F", "Х" : "H", "Ц" : "TS", "Ч" : "CH", 
			"Ш" : "SH", "Щ" : "SCH", "Ъ" : "", "Ы" : "YI", "Ь" : "", 
			"Э" : "E", "Ю" : "YU", "Я" : "YA", "а" : "a", "б" : "b", 
			"в" : "v", "г" : "g", "д" : "d", "е" : "e", "ё" : "e", "ж" : "j", 
			"з" : "z", "и" : "i", "й" : "y", "к" : "k", "л" : "l", 
			"м" : "m", "н" : "n", "о" : "o", "п" : "p", "р" : "r", 
			"с" : "s", "т" : "t", "у" : "u", "ф" : "f", "х" : "h", 
			"ц" : "ts", "ч" : "ch", "ш" : "sh", "щ" : "sch", "ъ" : "y", 
			"ы" : "yi", "ь" : "", "э" : "e", "ю" : "yu", "я" : "ya", " " : "-", "+" : "-", "-" : "-", 
			" " : "-", "+" : "-", "-" : "-", "`" : "-", "!" : "-", "@" : "-", ":" : "-", ";" : "-", "#" : "-", "$" : "-", "%" : "-", 
			"^" : "-", "&" : "-", "*" : "-", "(" : "-", ")" : "-", "|" : "-", "\\" : "-", "]" : "-", "[" : "-", 
			"{" : "-", "}" : "-", "/" : "-", "." : "-", "–" : "-", "-" : "-", ", " : "-", "'" : "-", "»" : "-", "\"" : "-", "«" : "-", "?" : "-", "~" : "-", 
            "®" : "-","_" : "-"
            
        };
        var result='', ch, next, cnt=-1;
        var next;
        for (var i = 0; i < th.length; i++) {
          ch=th.charAt(i);
          next = th.charAt(i+1);
          next=ch.toUpperCase() === ch?next && next.toUpperCase() === next?2:1:0;

          if (!ch.match(/[a-z\d]/i)) {
             ch=L[ch.toLowerCase()];
             if (ch && next) ch=next==2?ch.toUpperCase():ch.substr(0,1).toUpperCase()+ch.substring(1);
          }
          if (ch) {
            result += (cnt>0?'-':'')+ch;
            cnt=0;
          } else if (cnt>-1) cnt++;
        } 
        return result;
}
//родители
function parents_arr(arr, tip, id){ //tip=li, tip=option
    id=id||0;
    var html = '';
    var pid_arr=arr['pid'];
    var name_arr=arr['name'];
    var val_arr=arr['val'];
    
    
    for (var id_cur in pid_arr){
        
        if (pid_arr[id_cur] == id) {
            
            if (tip=='li'){
                
                var sel_='';if (in_array(id_cur,val_arr)==true){sel_=' class="active"';}
                
                html += '<li data-id="'+id_cur+'">' + "\n";
                html += '<div'+sel_+'><span>'+name_arr[id_cur]+'</span></div>' + "\n";
                html += parents_arr(arr,tip, id_cur) + "\n"; 
                html += '</li>' + "\n";
            }
            else if (tip=='option'){
                var sel_='';if (in_array(id_cur,val_arr)==true){sel_=' selected="selected"';}
                
                html += '<option value="'+id_cur+'"'+sel_+'>' + "\n";
                html += str_repeat(' - ',count(parents_id(arr,id_cur)))+arr['name'][id_cur]+'</option>' + "\n";
                html += parents_arr(arr,tip, id_cur) + "\n"; 
            }
            else{
                alert_m('Не определен тип tip='+tip,'','error','none');
            }
        }
    }
    if (tip=='li'){if (html!=''){return '<ul>' + html + '</ul>' + "\n";}else{return '';}}
    else if (tip=='option'){if (html!=''){return  html + "\n";}else{return '';}}
    else{}
}

function parents_id(arr, id){
    var par_arr=new Array();
    if (arr['pid'][id]!='0'){
        par_arr.push(arr['pid'][id]);
        par_arr= array_merge(par_arr,parents_id(arr, arr['pid'][id]));
    }
    return par_arr;
}
//
function in_array(needle, haystack, strict) {	// Checks if a value exists in an array
	// 
	// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

	var found = false, key, strict = !!strict;

	for (key in haystack) {
		if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
			found = true;
			break;
		}
	}

	return found;
}

//
function array_merge() {

  var args = Array.prototype.slice.call(arguments),
    argl = args.length,
    arg,
    retObj = {},
    k = '',
    argil = 0,
    j = 0,
    i = 0,
    ct = 0,
    toStr = Object.prototype.toString,
    retArr = true;

  for (i = 0; i < argl; i++) {
    if (toStr.call(args[i]) !== '[object Array]') {
      retArr = false;
      break;
    }
  }

  if (retArr) {
    retArr = [];
    for (i = 0; i < argl; i++) {
      retArr = retArr.concat(args[i]);
    }
    return retArr;
  }

  for (i = 0, ct = 0; i < argl; i++) {
    arg = args[i];
    if (toStr.call(arg) === '[object Array]') {
      for (j = 0, argil = arg.length; j < argil; j++) {
        retObj[ct++] = arg[j];
      }
    } else {
      for (k in arg) {
        if (arg.hasOwnProperty(k)) {
          if (parseInt(k, 10) + '' === k) {
            retObj[ct++] = arg[k];
          } else {
            retObj[k] = arg[k];
          }
        }
      }
    }
  }
  return retObj;
}

function number_format(number, decimals, dec_point, thousands_sep) {	// Format a number with grouped thousands
	// 
	// +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +	 bugfix by: Michael White (http://crestidg.com)
    var minus_='';
    if ((number-0)<0){number=number*(-1);minus_='-';}
    
	var i, j, kw, kd, km;

	// input sanitation & defaults
	if( isNaN(decimals = Math.abs(decimals)) ){
		decimals = 0;
	}
	if( dec_point == undefined ){
		dec_point = ".";
	}
	if( thousands_sep == undefined ){
		thousands_sep = " ";
	}

	i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

	if( (j = i.length) > 3 ){
		j = j % 3;
	} else{
		j = 0;
	}

	km = (j ? i.substr(0, j) + thousands_sep : "");
	kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
	//kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
	kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


	return minus_+km + kw + kd;
}

function str_replace(search, replace, string)
{
    string=string+'';
	// 1. все должно быть массивами
	search = [].concat(search);
	replace = [].concat(replace);

	// 2. выровнять массивы
	var len = replace.length - search.length;

	var p_last = search[search.length - 1];

	// 2.1. если массив строк поиска короче
	for (var i = 0; i < len; i++) {
		search.push(p_last);
	}

	// 2.2. если массив строк замены короче
	for (var i = 0; i < -len; i++) {
		replace.push('');
	}

	// 3. непосредственная замена
	var result = string;
	for (var i = 0; i < search.length; i++) {
		result = result.split(search[i]).join(replace[i]);
	}
	return result;
}

function _IN(val){
    if (typeof val=='undefined'){
        val='';
    }
    return str_replace(['&', '"', "'", '<', '>', '?'],['&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;'],val);
}


//позиция 
function strpos( haystack, needle, offset){	// Find position of first occurrence of a string
	// 
	// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

	var i = haystack.indexOf( needle, offset ); // returns -1
	return i >= 0 ? i : false;
}
//убираем теги
function strip_tags( str ){	// Strip HTML and PHP tags from a string
	// 
	// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

	return str.replace(/<\/?[^>]+>/gi, '');
}
function str_repeat ( input, multiplier ) {	// Repeat a string
	var buf = '';

	for (i=0; i < multiplier; i++){
		buf += input;
	}

	return buf;
 }
//Получение информации по счетам
function get_sum_info_from_shablon(){
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='m_platezi_get_schet_summa';
    data_['i_scheta_id']='-1';
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_platezi.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
    	            if (typeof data_n.s=='object'){
    	               var sum=0;
                       var txt='';
    	               for(var id in data_n.s){
    	                   
                           var i_scheta_chk_view='';if (data_n.sv[id]=='1'){
                                i_scheta_chk_view='checked="checked"';
                                sum=(sum-0)+(data_n.s[id]-0);
                           }
                           txt+='<p data-id="'+id+'"><input type="checkbox" class="i_scheta_chk_view" '+i_scheta_chk_view+' /> <span>'+data_n.sn[id]+':</span> <strong>'+number_format(data_n.s[id],0,'.',' ')+' <i class="fa fa-rub"></i></strong> </p>';
    	               }
                       txt+='<p class="i_scheta_chk_view_com"><a href="?inc=m_platezi" class="btn_orange">Платежи</a></p>';
                       $('.m_platezi_all_info div').html(number_format(sum,0,'.',' '));
                       $('.m_platezi_all_info span').html(txt);
    	            }
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

//Получение информации по заказам с таймером
function get_zakaz_info_from_shablon(){
    var err_text='';
    var th_=$(this);
    var data_=new Object();
    data_['_t']='get_zakaz_info_from_shablon';
    
    if (err_text!=''){alert_m(err_text,'','error','none');}
    else{
    	$.ajax({
    		"type": "POST",
    		"url": "ajax/m_zakaz.php",
    		"dataType": "text",
    		"data":data_,
    		"success":function(data,textStatus){
    	        if (is_json(data)==true){
    	            data_n=JSON.parse(data);
                    var txt_0='';var i_0=0;
                    var txt_1='';var i_1=0;
    	            if (typeof data_n.i=='object'){
                        for(var i in data_n.i){//по заказам
                            var tm_=(data_n.d_[i]-0);
                            var arr_=data_n.w[i];
                            
                            if (tm_<0){
                                tm_=tm_*(-1);
                                i_0++;
                                txt_0+='<div class="get_zakaz_info_item">';
                                txt_0+='<p><a href="?inc=m_zakaz&nomer='+data_n.i[i]+'">Заказ №'+data_n.i[i]+' от <span>'+data_n.d1[i]+'</span></a></p>';
                                
                                if (typeof data_n.in_!='undefined' && typeof data_n.in_[i]!='undefined' && data_n.in_[i]!=''){
                                    txt_0+='<p class="get_zakaz_info_item_i_contr_info">'+data_n.in_[i]+'';
                                    if (typeof data_n.ip[i]!='undefined' && data_n.ip[i]!=''){txt_0+=' <a href="tel:'+data_n.ip[i]+'">'+data_n.ip[i]+'</a>';}
                                    if (typeof data_n.ie[i]!='undefined' && data_n.ie[i]!=''){txt_0+=' <a href="mailto:'+data_n.ie[i]+'">'+data_n.ie[i]+'</a>';}
                                    txt_0+='</p>';
                                }
                                txt_0+='<p>Просрочка: '+tm_+' час'+end_word(tm_,'ов','','а')+'</p>';
                                
                                var txt_0tbl='';
                                if (typeof arr_.i!='undefined'){
                                    for(var j in arr_.n){//по товарам
                                        txt_0tbl+='<div class="ttable_tbody_tr">';
                                        txt_0tbl+='<div class="ttable_tbody_tr_td">';
                                        
                                        if (typeof arr_.im[j]!='undefined' && arr_.im[j]!=''){
                                            txt_0tbl+='<i class="fa fa-image thumbnail"><span><img src="../i/s_cat/small/'+arr_.im[j]+'"/></span></i> ';
                                    
                                        }
                                        txt_0tbl+='<a href="?inc=s_cat&nomer='+arr_.i[j]+'" class="get_zakaz_items_name">'+arr_.n[j]+'</a>';
                                        txt_0tbl+='</div>';
                                        txt_0tbl+='<div class="ttable_tbody_tr_td">';
                                            txt_0tbl+='<span class="get_zakaz_items_price">'+data_n.w[i].p[j]+'</span>';
                                        txt_0tbl+='</div>';
                                        txt_0tbl+='<div class="ttable_tbody_tr_td">';
                                            txt_0tbl+='<span class="get_zakaz_items_kol">'+data_n.w[i].k[j]+'</span>';
                                        txt_0tbl+='</div>';
                                        txt_0tbl+='<div class="ttable_tbody_tr_td">';
                                            txt_0tbl+='<span class="get_zakaz_items_itog">'+(data_n.w[i].p[j]*data_n.w[i].k[j])+'</span>';
                                        txt_0tbl+='</div>';
                                        
                                        txt_0tbl+='</div>';
                                    }
                                    if (txt_0tbl!=''){
                                        txt_0=txt_0+'<div class="ttable get_zakaz_items_tbl">'
                                                   +txt_0tbl
                                                   +'</div>';
                                    }
                                }
                                txt_0+='<p class="get_zakaz_items_comments">'+data_n.c[i]+'</p>';
                                txt_0+='</div>';
                            }else{
                                
                                i_1++;
                                txt_1+='<div class="get_zakaz_info_item">';
                                txt_1+='<p><a href="?inc=m_zakaz&nomer='+data_n.i[i]+'">Заказ №'+data_n.i[i]+' от <span>'+data_n.d1[i]+'</span></a></p>';
                                txt_1+='<p>Осталось: '+tm_+' час'+end_word(tm_,'ов','','а')+'</p>';
                                var txt_1tbl='';
                                if (typeof arr_.i!='undefined'){
                                    for(var j in arr_.n){//по товарам
                                        txt_1tbl+='<div class="ttable_tbody_tr">';
                                        txt_1tbl+='<div class="ttable_tbody_tr_td">';
                                        
                                        if (typeof arr_.im[j]!='undefined' && arr_.im[j]!=''){
                                            txt_1tbl+='<i class="fa fa-image thumbnail"><span><img src="../i/s_cat/small/'+arr_.im[j]+'"/></span></i> ';
                                    
                                        } 
                                        txt_1tbl+='<a href="?inc=s_cat&nomer='+arr_.i[j]+'" class="get_zakaz_items_name">'+arr_.n[j]+'</a>';
                                        txt_1tbl+='</div>';
                                        txt_1tbl+='<div class="ttable_tbody_tr_td">';
                                            txt_1tbl+='<span class="get_zakaz_items_price">'+data_n.w[i].p[j]+'</span>';
                                        txt_1tbl+='</div>';
                                        txt_1tbl+='<div class="ttable_tbody_tr_td">';
                                            txt_1tbl+='<span class="get_zakaz_items_kol">'+data_n.w[i].k[j]+'</span>';
                                        txt_1tbl+='</div>';
                                        txt_1tbl+='<div class="ttable_tbody_tr_td">';
                                            txt_1tbl+='<span class="get_zakaz_items_itog">'+(data_n.w[i].p[j]*data_n.w[i].k[j])+'</span>';
                                        txt_1tbl+='</div>';
                                        
                                        txt_1tbl+='</div>';
                                    }
                                    if (txt_1tbl!=''){
                                        txt_1=txt_1+'<div class="ttable get_zakaz_items_tbl">'
                                                   +txt_1tbl
                                                   +'</div>';
                                    }
                                }
                                txt_1+='<p class="get_zakaz_items_comments">'+data_n.c[i]+'</p>';
                                txt_1+='</div>';
                            }
                        }
                    }
                   
                    if (txt_0!=''){txt_0='<span class=" thumbnail"><i class="fa fa-bell"> '+i_0+'</i> <span>'+txt_0+'</span></span>';}
                    if (txt_1!=''){txt_1='<span class="get_zakaz_info_1 thumbnail"><i class="fa fa-bell"> '+i_1+'</i> <span>'+txt_1+'</span></span>';}
                    
                    if (txt_0!='' || txt_1!=''){
                        $('.m_zakaz_all_info').html(txt_0+txt_1);
                    }
                    
                    
                    /*
                    var txt_0='';
                    var txt_1='';
    	            if (typeof data_n[0]=='object'){
    	               for(var id in data_n[0]){
    	                   txt_0+='<p><a href="?inc=m_zakaz&nomer='+id+'">Заказ №'+id+': <span>'+data_n[0][id]+'</span></a></p>';
    	               }
                    }
                    if (typeof data_n[1]=='object'){
    	               for(var id in data_n[1]){
    	                   txt_1+='<p><a href="?inc=m_zakaz&nomer='+id+'">Заказ №'+id+': <span>'+data_n[1][id]+'</span></a></p>';
    	               }
                    }
                    if (txt_0!=''){txt_0='<span class="get_zakaz_info_0 thumbnail"><i class="fa fa-bell"> '+count(data_n[0])+'</i> <span>'+txt_0+'</span></span>';}
                    if (txt_1!=''){txt_1='<span class="get_zakaz_info_1 thumbnail"><i class="fa fa-bell"> '+count(data_n[1])+'</i> <span>'+txt_1+'</span></span>';}
                    
                    if (txt_0!='' || txt_1!=''){
                        $('.m_zakaz_all_info').html(txt_0+txt_1);
                    }
                    
                    */
    			}
    			else{
    				alert_m(data,'','error','none');
    			}            
    		}
    	});
    }
}

//**************************************************************************************************************
//**************************************************************************************************************
$(document).ready(function(){
    
    //меню профиля
    $(document).delegate('.profile_menu_bg','click',function(){
        $('.profile_menu_all').hide();
    });
    $(document).delegate('.header_block__change_profile','click',function(e){
        var cord=$('.header_block__change_profile').offset();
        $('.profile_menu').css({'left':cord.left+'px','top':(cord.top-0+10)+'px'});
        $('.profile_menu_all').show();
    });
    
    //Для мобильной верстки
    if ($('.top_menu li:hidden').size()>0){
    $(document).delegate('.top_menu','click',function(){
        $('.top_menu li').css({'display':'inline-block'});
        
    });
    }
    // последенее посещение
    // data_visit_update();
    
    //Изменения отображени суммы в шапке (смена выбранных счетов)
    $(document).delegate('.i_scheta_chk_view','click',function(){
        var err_text='';
        var th_=$(this);
        var data_=new Object();
        data_['_t']='i_scheta_chk_view';
        data_['nomer']=$(this).closest('p').data('id');
        data_['chk']=$(this).prop('checked');
        
        if (err_text!=''){alert_m(err_text,'','error','none');}
        else{
        	$.ajax({
        		"type": "POST",
        		"url": "ajax/m_platezi.php",
        		"dataType": "text",
        		"data":data_,
        		"success":function(data,textStatus){
        	        if (is_json(data)==true){
        	            data_n=JSON.parse(data);
        	            get_sum_info_from_shablon();
        			}
        			else{
        				alert_m(data,'','error','none');
        			}            
        		}
        	});
        }
    });
    
    $('.top_menu li a').click(function(e){
            e.preventDefault();
            $('.top_menu li').removeClass('active');
            $(this).closest('li').addClass('active');
            $('.left_podmenu .left_podmenu_div').css({'display':'none'});
            $('.left_podmenu div[data-id='+$(this).data('id')+']').css({'display':'inherit'});
            $(window).scroll();
            
            if ( (($(this).closest('li').attr('class')).split('header_block__home').length - 1)==0 && 
            (($(this).closest('li').attr('class')).split('header_block__version').length - 1)==0){
                
                
            }else{
                window.location.href = $(this).attr('href');
            }
            
    });
    
    //Автозаполнение столбцов #HASH
    $(document).delegate('.span_hash','click',function(){
        var th_=$(this);
        th_.closest('form').find('input[type=checkbox]').removeAttr('checked');
        th_.closest('form').find('input[type=text]').val('');
        th_.closest('form').find('textarea').val('');
        th_.closest('form').find('input, select,textarea').each(function(){
            
            
            var n_=$(this).attr('name');
            var val=th_.data(n_);
            
            if (typeof val!='undefined'){
                if ($(this).prop("tagName")=='INPUT'){
                    if ($(this).attr('type')=='checkbox'){
                        if (val=='1'){$(this).prop('checked','checked');}
                        else{$(this).removeAttr('checked');}
                    }
                    else if ($(this).attr('type')=='text'){
                        $(this).val(val);
                    }
                }
                else if($(this).prop("tagName")=='SELECT'){
                    $(this).find('option').removeAttr('selected').each(function(){
                        if ($(this).val()==val){$(this).prop('selected','selected');}
                    });
                }
                else if($(this).prop("tagName")=='TEXTAREA'){
                    $(this).val(val);
                }
           }
        });
    });
    
    //Информация
    $(document).delegate('.a_menu_info_a','click',function(){
        alert_m($(this).find('div').html(),'','info','none','1000');
    });
    
    //скролл
    $('.up_').mouseover(function(){$(this).css({'background-color':'#E1E7ED'});});    
    $('.up_').mouseout(function(){$(this).css({'background':'none'});}); 
    $('.up_').click(function(){
       
        if ($(document).scrollTop()>100){
             
            setCookie('current_top',$(document).scrollTop()); 
            $('body,html').animate({scrollTop: 0},100);
        }else{
            $('body,html').animate({scrollTop: getCookie('current_top')},100);
        }
        
    });
    setCookie('current_top',0);
    
    
    //Отобразить большой текст
    // <span class="view_longtext">Показать текст <span style="display:none;">Длинный текст...</span></span>
    $(document).delegate('.view_longtext','click',function(){
       
        if ($(this).find('>span').css('display')=='none'){
            $(this).find('>span').animate({height: 'show'}, 300); 
        }
        else{
            $(this).find('>span').animate({height: 'hide'}, 500); 
        }
    });
    
   //Сумма на счетах  -отображение в шаблоне
   if ($('.m_platezi_all_info').size()>0 && typeof get_sum_info_from_shablon=='function'){get_sum_info_from_shablon(); }
    
    //Напоминания о заказах
    if ($('.header_block__profile .m_zakaz_all_info').size()>0 && typeof get_zakaz_info_from_shablon=='function'){get_zakaz_info_from_shablon(); }
    
        
    

    //Календарь
    $(document).delegate('.m_zakaz_calendar','click',function(){
    
        
        	var todayDate = moment().startOf('day');
        	var YM = todayDate.format('YYYY-MM');
        	var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
        	var TODAY = todayDate.format('YYYY-MM-DD');
        	var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');
    
            var err_text='';
            var th_=$(this);
            var data_=new Object();
            data_['_t']='m_zakaz_calendar';
            
            if (err_text!=''){alert_m(err_text,'','error','none');}
            else{
                loading(1);
            	$.ajax({
            		"type": "POST",
            		"url": "ajax/admin.php",
            		"dataType": "text",
            		"data":data_,
            		"success":function(data,textStatus){
            	        if (is_json(data)==true){
            	            data_n=JSON.parse(data);
                            loading(0);
            	            var events_arr=new Array();
                            for(var i in data_n.i){
                                var d_end=data_n.d1[i];//TODAY; 
                                if (data_n.d2[i]!=''){d_end=data_n.d2[i];}
                                var cl_='';if (data_n.aac[i]-0==0){cl_='m_zakaz_calendar_event_notmy';}
                                if (data_n.aac[i]-0==1){cl_='m_zakaz_calendar_event_my';}
                                
                                var ico_='';
                                if (data_n.st[i]=='В обработке'){d_end=TODAY; ico_='<i class="fa fa-star-o"></i>';cl_+=' m_zakaz_calendar_event_obrab';}
                                if (data_n.st[i]=='Частично выполнен'){d_end=TODAY; ico_='<i class="fa fa-star-half-o"></i>';cl_+=' m_zakaz_calendar_event_part';}
                                if (data_n.st[i]=='Выполнен'){ico_='<i class="fa fa-star"></i>';cl_+=' m_zakaz_calendar_event_full';}
                                
                                var desc_=ico_+' ';
                                    if (data_n.an[i]!=''){desc_+=data_n.an[i]+' ';}
                                    if (data_n.in_[i]!=''){desc_+='<strong>'+data_n.in_[i]+'</strong> ';}
                                    if (data_n.c[i]!=''){desc_+='<span>'+data_n.c[i]+'</span> ';}
                                //d_end=TODAY; 
                                events_arr[i]={
                                    id: data_n.i[i],
                                    title: data_n.pn[i],
                                    description: '<p class="calendar_desc_item">'+desc_+'</p>',
            					    start: data_n.d1[i]+' '+data_n.d10[i],
            					    end: d_end,
                                    className: cl_,
                                    allDay: false,
            					    url: '?inc=m_zakaz&nomer='+data_n.i[i]
                                    };
                            }
                            
                    alert_m('<div id="calendar"></div>','','calendar','none');        
            		$('#calendar').fullCalendar({
            			header: {
            				left: 'prev,next today',
            				center: 'title',
            				right: 'month,agendaWeek,agendaDay,listMonth'
            			},
            			locale: 'ru',
            			buttonIcons: true, // show the prev/next text
            			weekNumbers: true,
            			navLinks: true, // can click day/week names to navigate views
            			editable: true,
            			eventLimit: true, // allow "more" link when too many events
            			events: events_arr,
                        eventRender: function(event, element) {
                            element.prepend('<strong>');
                            element.append('</strong>'+event.description);
                            
                        }
            		});
            
            		// when the selected option changes, dynamically change the calendar option
            		$('#locale-selector').on('change', function() {
            			if (this.value) {
            				$('#calendar').fullCalendar('option', 'locale', this.value);
            			}
            		});
                            
                            
                            
                            
            			}
            			else{
            				alert_m(data,'','error','none');
            			}            
            		}
            	});
            }
            
    });


});
