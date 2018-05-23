<?php
/**
 *  author: BaAGee
 *  createTime: 2018/5/22 16:05
 */

//include_once __DIR__ . '/lib/WebSocketServer.php';
//date_default_timezone_set('PRC');
//$conf = [
//    'redis' => [
//        'host' => '127.0.0.1',
//        'port' => 6379
//    ],
//    'web_socket' => [
//        'host' => '0.0.0.0',
//        'port' => 8989
//    ],
//    'tuling_robot_apikey' => '',
//    'app_name'=>'chat'
//];
//
//$a = new WebSocketServer($conf);
//$a->run();


include_once '../../core/Script.class.php';

class ChatServer extends Script
{
    private $conf = [];

    public function __construct($conf)
    {
        $this->conf = $conf;
        parent::__construct($conf['app_name']);
    }

    public function run()
    {
        echo 'start...'.PHP_EOL;
        $webSocket=new \chat\websocket\lib\WebSocketServer($this->conf);
        $webSocket->run();
    }
}

$conf = [
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379
    ],
    'web_socket' => [
        'host' => '0.0.0.0',
        'port' => 8989
    ],
//    'tuling_robot_apikey' => '',
    'app_name' => 'chat'
];

$a = new ChatServer($conf);
$a->run();