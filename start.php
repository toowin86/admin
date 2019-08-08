<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="http://vk.com/toowin86" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    
    <link type="text/css" href="css/style.css" rel="stylesheet" />
    <link type="text/css" href="css/arcticmodal.css" rel="stylesheet" />
    <link type="text/css" href="css/font-awesome.css" rel="stylesheet" />
    
    <script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="js/jquery.arcticmodal-0.3.min.js"></script>
    <script type="text/javascript" src="js/admin.js"></script>
    <script type="text/javascript" src="js/start.js"></script>
    
    <title>Авторизация <?=$_SERVER['SERVER_NAME'];?></title>
</head>

<body class="body_auth">
    <form class="start__form_auth" action="#">
        <div class="start__div_auth">
            <h1>Авторизация</h1>
            <a class="start__form_create" href="https://vk.com/toowin86" title="Система управления сайтом VWEB6 (v-web.ru)"><i class="fa fa-vk"></i></a>
            <div class="start__block_div">
                <input class="start__input_email" name="email" type="text" placeholder="Введите email" />
                <span class="start__input_name">Email</span>
                <span style="display: none;" class="start__input_info"></span>
            </div>
            <div class="start__block_res">
            <?=$auth_info;?>
            </div>
            <div class="start__block_div_com">
               
                <span class="start__recovery_password btn_gray"><span>Запросить ссылку для входа</span></span>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div class="start__bottom_shadow"></div>
    </form>
</body>
</html>