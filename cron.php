<?php
define('XHPROF', 0);
/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REMOTE_ADDR']) AND ! XHPROF) {
        die('<br><strong>This script is only meant to run at the command line.</strong>');
}

// Set following option to 1 for enable debug mode

if (XHPROF) {
define("XHPROF_ROOT", __DIR__ . '/xhprof');
require_once (XHPROF_ROOT . '/xhprof_lib/utils/xhprof_lib.php');
require_once (XHPROF_ROOT . '/xhprof_lib/utils/xhprof_runs.php');
//append XHPROF_FLAGS_NO_BUILTINS if your PHP instance crashes
xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}


# Подключаем настройки и необходимые библиотеки
require_once('config.php');
require_once('libs/mysqli.php');
require_once('libs/rrd_step_60.php');


if (!file_exists($png_path)) {
    if (!mkdir($png_path, 0755)) {
        die('Не удалось создать директории: '. $png_path);
    }
    # posix_getpwuid(fileowner($png_path))['uid']
}

$downloadTotal = 0;
$uploadTotal = 0;
$rraTotal = $rra_path . DIRECTORY_SEPARATOR . 'total' . '.rrd';

$query_nas = "SELECT `nasip` FROM `nas` WHERE nastype='local' or nastype='rscriptd' GROUP BY `nasip`";
$queried_nas = $rm_loginDB->query($query_nas) or die('wrong data input: ' . $query_nas);
// Цикл для беребора NAS
while($row_nas = mysqli_fetch_assoc($queried_nas)) {
    $nas = $row_nas['nasip'];

    require ('ipcad_flow_array.php');

    $query_user = "SELECT `users`.`login`,nethosts.ip FROM `users` INNER JOIN nethosts ON users.ip=nethosts.ip INNER JOIN (SELECT `nasip`,`netid` FROM `nas` WHERE `nasip` = '" . $nas  . "') AS t_nas USING (`netid`)";
    $queried_user = $rm_loginDB->query($query_user) or die('wrong data input: ' . $queried_user);

    while($row = mysqli_fetch_assoc($queried_user)) {
        $login = $row['login'];
        $ip = $row['ip'];
//      if (array_key_exists($ip, $ipcad_flow_array)) {
            # Полный путь файла RRA
            $rra = $rra_path . DIRECTORY_SEPARATOR . $login . '.rrd';
            # Полный путь файла PNG
            $png = $png_path . DIRECTORY_SEPARATOR . $login . '.png';
            # Проверяем заполненность данных FLOW-статистики
            $upload = isset($ipcad_flow_array[$ip]['src']) ? $ipcad_flow_array[$ip]['src'] : '0';
            $download = isset($ipcad_flow_array[$ip]['dst']) ? $ipcad_flow_array[$ip]['dst'] : '0';

            $downloadTotal += $download;
            $uploadTotal += $upload;

            if (!file_exists($rra)) {
                $ret = rrd_create($rra, $opts_create);
                    if (! $ret) {
                        $err_create = rrd_error();
                        print "Create error: $err_create\n";
                    }
            } else {
                $ret = rrd_update($rra, array("N:$download:$upload"));
                    if (! $ret ) {
                        $err_update = rrd_error();
                        print "Update error: $err_update\n";
                    }
            }
//      }

    }

}

// Create Total rrd
if (!file_exists($rraTotal)) {
    $ret = rrd_create($rraTotal, $opts_create);
        if (! $ret) {
            $err_create = rrd_error();
            print "Create error: $err_create\n";
        }
} else {
    $ret = rrd_update($rraTotal, array("N:$downloadTotal:$uploadTotal"));
        if (! $ret ) {
            $err_update = rrd_error();
            print "Update error: $err_update\n";
        }
}

if (XHPROF) {
$xhprof_data = xhprof_disable();
$xhprof_runs = new XHProfRuns_Default();
$xhprof_run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_stats");
$xhprof_link = ' <p><a href="xhprof/xhprof_html/index.php?run='.$xhprof_run_id.'&source=xhprof_stats" width="100%" height="750">XHPROF</a></p>';
echo $xhprof_link;
}

?>
