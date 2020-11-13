<?php
if (!isset($_SERVER['REQUEST_URI'])) { 
     die('<br><strong>!!! YOU bad boy !!!</strong>');
}
# Подключаем настройки и необходимые библиотеки
require_once('config.php');
require_once('libs/mysqli.php');

list ($separetor, $ip_range) = explode('/',$_SERVER['REQUEST_URI']);
//$range = explode('.',$range);
$ip_range = explode('-', $ip_range);
$ip = @$ip_range['0'];
$range = @$ip_range['1'];
$login = '';

switch ($range) {
    case '1':
        $time = 36000;
        break;
    case '2':
        $time = 604800;
        break;
    case '3':
        $time = 2419200;
        break;    
    case '4':
        $time = 29030400;
        break;
    default:
        $time = 36000;
}
if (!empty($ip)) {
    $query_user = "SELECT `users`.`login` FROM `users` INNER JOIN (SELECT `nethosts`.`ip` FROM nethosts WHERE nethosts.ip='" . $ip . "') nethosts USING (IP)";
    $queried_user = $rm_loginDB->query($query_user) or die('wrong data input: '.$queried_user);

    // Ищем логин по IP
    while($row = mysqli_fetch_assoc($queried_user)) {
        $login = $row['login'];
    };
}
function LoadErrorPNG($text) {
        putenv('GDFONTPATH=' . realpath('.'));
        /* Создаем пустое изображение */
        $im  = imagecreatetruecolor(600, 300);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);
        // Создание цветов
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 600, 300, $white);

        /* Выводим сообщение об ошибке */
        #imagestring($im, 1, 5, 5, 'Ошибка загрузки ', $tc);

        #$text = htmlentities(iconv('','utf-8',$text), ENT_COMPAT, "windows-1251" );
        imagettftext($im, 14, 0, 11, 50, $grey, 'arial.ttf', $text);
        
    return $im;
}

# Подключаем настройки и необходимые библиотеки
require_once('libs/rrd_step_60.php');

if (!file_exists($png_path)) {
    if (!mkdir($png_path, 0755)) {
        die('Не удалось создать директории: '. $png_path);
    }
}
# print_r(posix_getpwuid(fileowner($png_path)));

# Полный путь файла RRA
$rra = $rra_path . DIRECTORY_SEPARATOR . $login . '.rrd';
# Полный путь файла PNG
$png = $png_path . DIRECTORY_SEPARATOR . $login . '_' . $range . '.png';

if (!file_exists($rra)) {
    #die('<br><strong>Ssory, but we dont have flow-stats. RRA not create</strong>');

    //header('Content-Type: image/png');
    $er_img = LoadErrorPNG('К сожалению файл ' . $rra . ' не существует');
    header('Content-Type: image/png');
    imagepng($er_img);
    imagedestroy($er_img);

}

$graph_res = rrd_graph($png, graph_opts($login, $rra, $time));
if(!is_array($graph_res) ) {
    $err_graph = rrd_error();
    echo "rrd_graph() ERROR: $err_graph\n";
}
if (file_exists($png)) {
    $im = imagecreatefrompng($png);
    header('Content-Type: image/png');
    imagepng($im);
}
?>
