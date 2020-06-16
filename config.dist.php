<?php
# Защита от выполнения через браузер
DEFINE('STATS', TRUE); 

# Определяем ip адресса, которые обробатывать
$ipcad_search_ip_regex = ('/^(192\.168\.)|(172\.16\.)/i');

$rra_path = '/var/flow';

$png_path = __DIR__ . DIRECTORY_SEPARATOR . 'flow';

# Параметры базы данных биллинга
$rm_db_server = "127.0.0.1";
$rm_db_username = "stg";
$rm_db_password = "mypassforbilling";
$rm_db = "stg";
?>
