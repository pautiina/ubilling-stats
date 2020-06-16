<?php
defined('STATS') or die('Direct Access to this location is not allowed.');

function bsd_command($command, $arg = '', $action = '') {
    $descriptorspec = array(
       0 => array("pipe", "r"),  // stdin - канал, из которого дочерний процесс будет читать
       1 => array("pipe", "w"),  // stdout - канал, в который дочерний процесс будет записывать 
       2 => array("pipe", "w") // stderr - файл для записи
    );

    $process = proc_open("$command $arg $action", $descriptorspec, $pipes);

    if (is_resource($process)) {
        fwrite($pipes[0], ' \n>');
        fclose($pipes[0]);

        //echo stream_get_contents($pipes[1]);
        $result = (stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        // Важно закрывать все каналы перед вызовом
        // proc_close во избежание мертвой блокировки
        $return_value = proc_close($process);
        return $result;
    }
}
?>
