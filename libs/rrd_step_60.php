<?php
defined('STATS') or die('Direct Access to this location is not allowed.');

if(!extension_loaded('rrd')) {
    print('Unable to load module for database server "rrd": PHP rrd extension not available!' . PHP_EOL);
    print('Please download pecl-rrd extension from official site https://pecl.php.net/package/rrd and install' . PHP_EOL);
    exit;
}
# Проверка существования директории для файлов RRA
if (!file_exists($rra_path)) {
    print('Directory "' . $rra_path . '" does not exist. Please create a directory' . PHP_EOL);
    exit;
}
if (!is_writable($rra_path)) {
    print('Directory "' . $rra_path . '" does not have write permission. Please install the correct permissions on the directory' . PHP_EOL);
    exit;
}

function rrdtool_escape_string($text) {
    $text = str_replace('"', '\"', $text);
    $text = str_replace(":", "\:", $text);
    $text = str_replace('%', '', $text);

    return $text;
}

$opts_create = array(
                "--step",60,
                "--start",0,
                "DS:traffic_in:ABSOLUTE:120:0:U",
                "DS:traffic_out:ABSOLUTE:120:0:U",
                "RRA:AVERAGE:0.5:1:10080",
                "RRA:AVERAGE:0.5:5:25920",
                "RRA:AVERAGE:0.5:30:9000",
                "RRA:AVERAGE:0.5:120:2250",
                "RRA:AVERAGE:0.5:1440:797",
                "RRA:MAX:0.5:1:10080",
                "RRA:MAX:0.5:5:25920",
                "RRA:MAX:0.5:30:9000",
                "RRA:MAX:0.5:120:2250",
                "RRA:MAX:0.5:1440:797"
);

function graph_opts($login, $rra, $time_before = 36000){
    
    $time_start = time() - $time_before;
    $time_end  = time();

    $time_start_comment = date('Y-m-d H:i:s', time() - $time_before);
    $time_end_comment  = date('Y-m-d H:i:s', time());
    $comment_arg = "From $time_start_comment To $time_end_comment \c";
    $comment_arg = rrdtool_escape_string(htmlspecialchars($comment_arg, ENT_QUOTES, 'UTF-8'));

    $opts_graph = array(
                "--imgformat", "PNG",
                "--start",$time_start,
                "--end",$time_end,
                "--pango-markup",
                "--title", "Traffic for user - $login",
                "--vertical-label", "bits per second",
                "--slope-mode",
                "--base",1000,
                "--height",200,
                "--width",700,
                "--rigid",
                "--alt-autoscale-max",
                "--lower-limit",0,
                "COMMENT:$comment_arg",
                "COMMENT:\\n",
                "--color", "BACK#F3F3F3",
                "--color", "CANVAS#FDFDFD",
                "--color", "SHADEA#CBCBCB",
                "--color", "SHADEB#999999",
                "--color", "FONT#000000",
                "--color", "AXIS#2C4D43",
                "--color", "ARROW#2C4D43",
                "--color", "FRAME#2C4D43",
                "--border", 1,
                "--font", "TITLE:11:'Ariel'",
                "--font", "AXIS:8:'Ariel'",
                "--font", "LEGEND:8:'Courier'",
                "--font", "UNIT:8:'Ariel'",
                "--font", "WATERMARK:6:'Ariel'",
                "--slope-mode",
                "DEF:a=" . $rra . ":traffic_in:MAX",
                "DEF:b=" . $rra . ":traffic_in:AVERAGE",
                "DEF:c=" . $rra . ":traffic_out:MAX",
                "DEF:d=" . $rra . ":traffic_out:AVERAGE",
                "CDEF:cdefa=a,8,*",
                "CDEF:cdefb=b,8,*",
                "CDEF:cdeff=c,8,*",
                "CDEF:cdefg=d,8,*",
                "LINE1:cdefa#00CF00FF:",
                "AREA:cdefb#00CF007F:Inbound  ",
                "GPRINT:cdefb:LAST:Current\:%8.2lf %s",
                "GPRINT:cdefb:AVERAGE:Average\:%8.2lf %s",
                "GPRINT:cdefa:MAX:Maximum\:%8.2lf %s \\n",
                "LINE1:cdeff#002A97FF:",
                "AREA:cdefg#002A977F:Outbound",
                "GPRINT:cdefg:LAST:Current\:%8.2lf %s",
                "GPRINT:cdefg:AVERAGE:Average\:%8.2lf %s",
                "GPRINT:cdeff:MAX:Maximum\:%8.2lf %s \\n"
    );
    return $opts_graph;
}
?>