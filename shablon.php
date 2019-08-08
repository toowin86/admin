<?php
if (admin_auth($_SESSION['admin']['email'],$_SESSION['admin']['password'])!='1'){echo 'no_auth';exit();}
?><html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="author" content="v-web.ru"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    
    <link type="text/css" href="css/style.css?clear=<?=rand(0,100);?>" rel="stylesheet" />
    <!--<link type="text/css" href="css/chosen.min.css" rel="stylesheet" />-->
    <link type="text/css" href="css/select2.css" rel="stylesheet" />
    <link type="text/css" href="css/arcticmodal.css" rel="stylesheet" />
    <link type="text/css" href="css/jquery-ui.min.css" rel="stylesheet" />
    <link type="text/css" href="css/jquery.datetimepicker_new.css" rel="stylesheet" />
    <link type="text/css" href="css/colorpicker.css" rel="stylesheet" />
    <link type="text/css" href="css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="css/jquery.fancybox.css" media="screen" /> <!-- Увеличение фото -->
    <link rel="shortcut icon" href="?inc=ico&char=<?=mb_strtoupper(mb_substr($_SERVER['SERVER_NAME'],0,2,'UTF-8')); ?>" />
    <link rel="stylesheet" href="js/period_picker.5.1.6.pro/build/jquery.periodpicker.min.css">
    <link rel="stylesheet" href="js/period_picker.5.1.6.pro/build/jquery.timepicker.min.css">
    <link rel="stylesheet" href="css/fullcalendar.min.css"/>
    <link rel="stylesheet" href="css/jquery.qtip.min.css"/>
    <link rel="stylesheet" href="css/timedropper.min.css"/>


    <script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="js/plupload.full.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/jquery.mousewheel.js"></script>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/fullcalendar.min.js"></script>
    <script type="text/javascript" src="js/locale-all.js"></script>
    
    <script type="text/javascript" src="js/jquery.fancybox.pack.js"></script> <!-- Увеличение фото -->
    <script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
    <script type="text/javascript" src="js/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="js/jquery.arcticmodal-0.3.min.js"></script>
    <script type="text/javascript" src="js/select2.full.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/prettify.min.js"></script>
    <script type="text/javascript" src="js/anchor.min.js"></script>
    <script type="text/javascript" src="js/colorpicker.js"></script>
    <script type="text/javascript" src="js/timedropper.min.js"></script>
    <script src="js/period_picker.5.1.6.pro/build/jquery.periodpicker.full.min.js"></script>
    <script src="js/period_picker.5.1.6.pro/build/jquery.timepicker.min.js"></script>

    <!--<script type="text/javascript" src="js/chosen.jquery.min.js"></script>-->
    <script type="text/javascript" src="js/jquery.mask.min.js"></script>
    <script type="text/javascript" src="js/jquery.ui.datepicker-ru.js"></script>
    <script type="text/javascript" src="js/autoNumeric.js"></script>
    <script type="text/javascript" src="js/jquery.email-autocomplete.js"></script>
    
   
    <script type="text/javascript" src="js/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="js/AjexFileManager/ajex.js"></script>
    <script type="text/javascript" src="js/jquery.qtip.min.js"></script>
   
     <!--
    <script type="text/javascript" src="js/ckeditor473/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="js/ckeditor473/djenx/djenx-explorer.js"></script>
     -->
    <script type="text/javascript" src="js/jquery.json.js"></script>
    <script type="text/javascript" src="js/json2.js"></script><!-- История-управление url -->
    <script type="text/javascript" src="js/jquery.history.js"></script><!-- История-управление url -->

    
    <script>
    
        
        var inc='<?php if(isset($inc)){echo $inc;} ;?>';
        $(function() {
    	
    	    var menu_ul = $('.profile_menu > li > ul'),
    	           menu_a  = $('.profile_menu > li > a');
    	    
    	    menu_ul.hide();
    	
    	    menu_a.click(function(e) {
    	       var cnt_=$(this).closest('li').find('ul').size();
                if (cnt_>0){
        	        e.preventDefault();
        	        if(!$(this).hasClass('active')) {
        	            menu_a.removeClass('active');
        	            menu_ul.filter(':visible').slideUp('normal');
        	            $(this).addClass('active').next().stop(true,true).slideDown('normal');
        	        } else {
        	            $(this).removeClass('active');
        	            $(this).next().stop(true,true).slideUp('normal');
        	        }
                }
    	    });
    	
    	});
    </script>
    <script type="text/javascript" src="js/admin.js"></script>
    <?php
        if ($inc!=''){
            if (file_exists('scripts/'._DB($inc).'.php')){
                include 'scripts/'._DB($inc).'.php';
            }else{
                include 'scripts/__other__.php';
            }
        }
    ?>
    
    <title><?php if (isset($title)){echo $title; if ($title!='') {echo ' | ';} echo 'Админ-панель ['.$_SERVER['SERVER_NAME'].']';} ?></title>
</head>
<body class="body_admin">


<div class="profile_menu_all" style="display: none;">
    <div class="profile_menu_bg"></div>
    <ul class="profile_menu">
		
        <li class="item1"><a href="#"><i class="fa fa-cogs"></i> Модули</a>
			<ul>
				<li class="subitem1"><a href="?inc=__mod_link_extractor">Извлечение из строк</a></li>
				<li class="subitem1"><a href="?inc=__mod_difference_lines">Отличие строк</a></li>
			</ul>
		</li>
        <?php if (isset($a_menu_arr['comments'][$inc_id]) and trim(strip_tags($a_menu_arr['comments'][$inc_id]))!=''){ ?>
                <li class="item2"><a class="a_menu_info_a" href="#info" title="Информация"><i class="fa fa-info"></i> <span>Информация</span><div class="a_menu_info_res"><?=$a_menu_arr['comments'][$inc_id];?></div></a></li>
        <?php } ?>
        <li class="item2"><a class="header_block__exit_profile" href="?inc=exit"><i class="fa fa-sign-out"></i> <span class="header_block__text">Выйти</span></a></li>
	</ul>
</div>


<div class="layer1">
    <div class="header_block">
        <ul class="top_menu">
            <li class="header_block__version"><a href="http://<?=$_SERVER['SERVER_NAME'].$cur_dir;?>" target="_blank" title="Перейти на САЙТ"></a></li>
            <li class="header_block__home"><a href="?inc=start_menu"><i class="fa fa-home"></i></a></li>
            <?=$top_menu_txt;?>
        </ul>
        <div class="header_block__profile">
        <?php
            $sql = "SELECT COUNT(*)
            				FROM m_zakaz
                            WHERE m_zakaz.data_end!='0000-00-00 00:00:00'
                            AND m_zakaz.status NOT IN ('Отменен','Выполнен')
            	";
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $myrow = mysql_fetch_array($res);
            //print_rf($myrow);
            if ($myrow[0]>0 and $a_menu_arr['dostup'][16]==1){
                ?>
                <span class="m_zakaz_all_info"></span>
                <?php
            }
            ?>
            <i class="fa fa-calendar m_zakaz_calendar" title="Калндарь"></i>
            <?php
            $sql = "SELECT COUNT(*)
            				FROM m_platezi
            	";
            $res = mysql_query($sql) or die(mysql_error().'<br/>'.$sql);
            $myrow = mysql_fetch_array($res);
            if ($myrow[0]>0 and $a_menu_arr['dostup'][50]==1){
                ?>
                <span class="m_platezi_all_info thumbnail"><div></div><span></span> <i class="fa fa-rub"></i></span>
                <?php
            }
            ?>
            <a class="header_block__change_profile" href="#"><span class="ico ico_opt"></span><?=$_SESSION['admin']['email'];?></a>
            
            
        </div>
        <div style="clear: both;"></div>
        
    </div>
    <div class="left_podmenu">
        <?=$left_menu_txt;?>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
        
    <div class="main_block">
    <table class="main_table" cellpadding="0" cellspacing="0">
        <tr>
        <td class="content_block">
            <div class="content_block_main">
             <?php
                if ($inc!=''){
                    if (file_exists('include/'._DB($inc).'.php')){
                        include 'include/'._DB($inc).'.php';
                    }else{
                        include 'include/__other__.php';
                    }
                }
            ?>
            </div>
            <div style="clear: both;"></div>
        </td>
        </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
    </div>
    <div class="preloader_ico" style="display: none;"><img src="i/l_20_w.gif" /></div>
    <div style="display: block; background: none repeat scroll 0% 0% transparent;" class="up_"><img src="i/up_.png" /></div>
    <!--
    <div class="log_sql_time">
        <ul>
        <?php
        /*
        if (isset($data_['_sql']['time']) and is_array($data_['_sql']['time'])){
            foreach($data_['_sql']['time'] as $key => $tm){
                ?>
                    <li class="thumbnail"><?=$data_['_sql']['time'][$key];?> <span style="width: 500px;"><?=$data_['_sql']['sql'][$key];?></span></li>
                <?php
            }
        }*/
        ?>
        </ul>
    </div>
    -->
</body>
</html>