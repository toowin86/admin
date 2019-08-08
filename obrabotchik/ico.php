<?php
    header('Content-type: image/png');

    if(isset($_GET['char']) && !empty($_GET['char'])) {
           $string = $_GET['char'];
    } else {
           $string = 'V';
    }
   
    
    // Создание изображения
    $im = imagecreatefrompng("i/favicon.png");
    // Создание цветов
    $white = imagecolorallocate($im, 255, 255, 255);
    $grey = imagecolorallocate($im, 128, 128, 128);
    
    // Текст надписи
    // Замена пути к шрифту на пользовательский
    $font = 'font/PT_Sans-Narrow-Web-Regular.ttf';
    
    imagettftext($im, 9, 0, 2, 12, $white, $font, $string);
    
    imagepng($im);
    imagedestroy($im);
    $include_=0;
?>