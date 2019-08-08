<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<script>
function parse_url(str, component) {
	// example 1: parse_url('http://username:password@hostname/path?arg=value#anchor');
	// returns 1: {scheme: 'http', host: 'hostname', user: 'username', pass: 'password', path: '/path', query: 'arg=value', fragment: 'anchor'}

	var query, key = [
	'source', 'scheme', 'authority', 'userInfo', 
	'user', 'pass', 'host', 'port', 'relative', 'path', 
	'directory', 'file', 'query', 'fragment'
	],
	ini = (this.php_js && this.php_js.ini) || {},
	mode = (ini['phpjs.parse_url.mode'] &&
	ini['phpjs.parse_url.mode'].local_value) || 'php',
	parser = {
		php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
	};

	var m = parser[mode].exec(str),
	uri = {},
	i = 14;
	while (i--) {
		if (m[i]) {
			uri[key[i]] = m[i];
		}
	}

	if (component) {
		return uri[component.replace('PHP_URL_', '').toLowerCase()];
	}
	if (mode !== 'php') {
		var name = (ini['phpjs.parse_url.queryKey'] &&
		ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
		parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
		uri[name] = {};
		query = uri[key[12]] || '';
		query.replace(parser, function($0, $1, $2) {
			if ($1) {
				uri[name][$1] = $2;
			}
		});
	}
	delete uri.source;
	return uri;
}


//*****************************************************************************************************
$(document).ready(function(){
    $(document).delegate('.__mod_link_extractor_send','click',function(e){
        var tip_=$('input[name="tip"]:checked').val();
        
        var content=$('.__mod_link_extractor_in_text').val();
        if (tip_=='1'){//url
            
            $('.__mod_link_extractor_in_text').val(content.replace(/%2F/g,"/"));
            content=$('.__mod_link_extractor_in_text').val();
            
            var regexp = /(http[s]?:\/\/|ftp:\/\/)?(www\.)?[a-zA-Zа-яА-Я0-9-]+\.(рф|ru|info|io|ua|moskow|com|org|net|io|mil|edu|ca|co.uk|com.au|gov)/g;
            var matches_array = content.match(regexp);
            content='';var arr_=new Object;
            for (i=0;i<matches_array.length;i++)
            {
                if (typeof arr_[matches_array[i]]=='undefined'){
                    arr_[matches_array[i]]=i;
                    if (content!=''){content+='\n';}
                    matches_array[i]=(matches_array[i]).replace(/http:\/\//g,'');
                    matches_array[i]=(matches_array[i]).replace(/www./g,'');
                    content+=matches_array[i];
                }
            }
           
        }
        else if (tip_=='3'){//email
            var regexp = /([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}/gi;
            var matches_array = content.match(regexp);
            content='';var arr_=new Object;
            for (i=0;i<matches_array.length;i++)
            {
                if (typeof arr_[matches_array[i]]=='undefined'){
                    arr_[matches_array[i]]=i;
                    if (content!=''){content+='\n';}
                    content+=matches_array[i];
                }
            }
        }
        else if (tip_=='4'){//img
            $('.__mod_link_extractor_options').submit();
            content='';
        }
        else if (tip_=='5'){//цифры
            content=content.replace(/[^\d]/gi,"");
        }
        else if (tip_=='6'){//теги
            content=content.replace(/<[^>]*>/g, '');
        }
        
        $('.__mod_link_extractor_out_text').val(content);
    });    
});
</script>