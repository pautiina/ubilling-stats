<?php
/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
        die('<br><strong>This script is only meant to run at the command line.</strong>');
}
# Подключаем настройки и необходимые библиотеки
require_once('libs/bsd-command.php');
require_once('libs/bsd-ipcad.php');

$ipcad_flow_array = array();

$ipcad_prepear = bsd_command($rsh_path, $nas, $clear_ip);
$ipcad_flow = bsd_command($rsh_path, $nas, $show_ip);
$ipcad_finish = bsd_command($rsh_path, $nas, $clear_ip_ch);

$ipcad_flow = explode(PHP_EOL, $ipcad_flow);

foreach ($ipcad_flow as $line_num=>$line) {
    if (preg_match('/\./', $line)) {
        list($ip_src, $ip_dst, $pcks, $bytes) = explode(' ', preg_replace('/ {2,}/',' ', trim($line)));
            # Считаем отданый трафик пользователя
            if (preg_match($ipcad_search_ip_regex, $ip_src)) {
                $ipcad_flow_array[$ip_src]['src'] = (array_key_exists($ip_src, $ipcad_flow_array) and isset($ipcad_flow_array[$ip_src]['src'])) ? $ipcad_flow_array[$ip_src]['src']+$bytes : $bytes;
            }
            # Считаем скаченный трафик пользователя
            if (preg_match($ipcad_search_ip_regex, $ip_dst)) {
                $ipcad_flow_array[$ip_dst]['dst'] = (array_key_exists($ip_dst, $ipcad_flow_array) and isset($ipcad_flow_array[$ip_dst]['dst'])) ? $ipcad_flow_array[$ip_dst]['dst']+$bytes : $bytes;
            }
    }
}

# print_r ($ipcad_flow_array);
# print ($nas . PHP_EOL);

?>
