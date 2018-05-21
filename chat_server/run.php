<?php
/**
 * author: BaAGee
 * createTime: 2018/5/14 1:08
 */

include_once './lib/ChatServer.php';

date_default_timezone_set('PRC');
$conf = [
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379
    ],
    'web_socket' => [
        'host' => '0.0.0.0',
        'port' => 8989
    ],
    'tuling_robot_apikey' => ''
];

$chat_server = new ChatServer($conf);
$chat_server->run();