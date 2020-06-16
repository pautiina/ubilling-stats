<?php
defined('STATS') or die('Direct Access to this location is not allowed.');

if(!extension_loaded('mysqli')) {
    print(('Unable to load module for database server "mysqli": PHP mysqli extension not available!'));
    exit;
}
$rm_loginDB = new mysqli($rm_db_server, $rm_db_username, $rm_db_password, $rm_db);
if ($rm_loginDB->connect_error) {
    die('Ошибка подключения (' . $rm_loginDB->connect_errno . ') '
            . $rm_loginDB->connect_error);
}

?>