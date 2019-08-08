<?php
    $title='Выход. '.$title;
    unset($_SESSION['admin']);
    header("Location: http://".$_SERVER['SERVER_NAME'].'/admin/');
    $include_=0;
?>