<?php

/**
 *  author: BaAGee
 *  createTime: 2018/5/8 21:09
 */
class ChatServer
{
    protected $redis_cli = null;
    protected $web_socket = null;

    const ACTION_LOGIN = 'login';
    const ACTION_USER_ONLINE = 'user_online';
    const ACTION_CHAT = 'chat';
    const REDIS_ONLINE_USERS_KEY = 'online_users';

    public function __construct($conf)
    {
        try {
            $this->redis_cli = $this->connectRedis($conf['redis']);
            $this->web_socket = $this->createWebSocket($conf['web_socket']);
        } catch (\Exception $e) {
            echo 'Error: ' . '[' . $e->getCode() . '] ' . $e->getMessage();
            exit();
        }
    }

    /*
     * 运行主程序入口
     */
    public function run()
    {
        $this->web_socket->on('open', [$this, 'onOpen']);
        $this->web_socket->on('message', [$this, 'onMessage']);
        $this->web_socket->on('close', [$this, 'onClose']);
        $this->web_socket->start();
    }

    /**
     * 用户连接时处理回调函数
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request)
    {
        echo '------------------------on open--------------------------' . PHP_EOL;
        echo '新用户已连接，未登录...' . PHP_EOL;
        echo "fd:" . $request->fd . PHP_EOL;
        echo "origin:" . $request->header['origin'] . PHP_EOL;
        echo "remote_addr:" . $request->server['remote_addr'] . PHP_EOL;
    }

    /**
     * 收到用户消息的回调函数
     * @param $ws
     * @param $request
     */
    public function onMessage($ws, $request)
    {
        echo '------------------------on message--------------------------' . PHP_EOL;
        $data = json_decode($request->data, true);
        echo '收到数据：' . PHP_EOL;
        var_dump($data);

        switch ($data['action']) {
            case self::ACTION_LOGIN:
//                处理登录
                $this->login($data['nickname'], $data['avatar_id'], $request->fd);
                break;
            case self::ACTION_CHAT:
//                处理聊天
                $this->chat($data['nickname'], $request->fd, $data['message'], $data['avatar_id'], $data['to']);
                break;
        }
        echo 'on message over' . PHP_EOL;
    }

    public function onClose($ws, $user_id)
    {
        $delete_user = json_decode($this->redis_cli->hGet(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_id), true);
//        删除redis在线用户
        $this->redis_cli->hDel(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_id);
        echo '用户：' . $delete_user['nickname'] . '下线' . PHP_EOL;
        /*通知在线用户有人下线*/
        $publish_user_logout = json_encode([
            'action' => 'logout_other',
            'user_id' => $user_id
        ], JSON_UNESCAPED_UNICODE);
        $this->batchSendMessage($this->web_socket->connections, $publish_user_logout, $user_id);
    }

    private function login($nickname, $avatar_id, $user_id)
    {
        echo '------------------------on message login--------------------------' . PHP_EOL;
        $user_data = [
            'nickname' => $nickname,
            'avatar_id' => $avatar_id,
            'user_id' => $user_id
        ];
        $this->redis_cli->hSet(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_id, json_encode($user_data, JSON_UNESCAPED_UNICODE));
        $return = [
            'action' => 'login',
            'res' => true,
            'myself' => [
                'user_id' => $user_id,
                'nickname' => $nickname,
                'avatar_id' => $avatar_id
            ]
        ];
        /*获取在线用户信息*/
        $online_users = $this->redis_cli->hGetAll(self::REDIS_ONLINE_USERS_KEY);
        /*给自己发送登陆成功消息*/
        $return['online_users'] = array_values($online_users);
        $return = json_encode($return, JSON_UNESCAPED_UNICODE);
        $this->web_socket->push($user_id, $return);
        echo '通知自己登陆成功:' . $return . PHP_EOL;
        /*给所有除了自己的在线用户发消息新用户上线*/
        $publish_user_online = json_encode([
            'action' => 'user_online',
            'user_info' => [
                'nickname' => $nickname,
                'user_id' => $user_id,
                'avatar_id' => $avatar_id
            ]
        ], JSON_UNESCAPED_UNICODE);
        $this->batchSendMessage($this->web_socket->connections, $publish_user_online, $user_id);
    }

    /**
     * 处理聊天
     * @param string $nickname 昵称
     * @param $fd
     * @param string $message 消息
     * @param int $avatar_id 头像id
     * @param array $to 发送目标用户id数组
     */
    private function chat($nickname, $fd, $message, $avatar_id, $to)
    {
        if (trim($message) !== '') {
//        给自己发消息说发送成功
            $return = [
                'action' => 'chat',
                'message' => [
                    'nickname' => $nickname,
                    'self' => true,
                    'message' => $message,
                    'time' => date('H:i:s'),
                    'avatar_id' => $avatar_id
                ]
            ];
            $send = json_encode($return, JSON_UNESCAPED_UNICODE);
            $this->web_socket->push($fd, $send);
            echo '通知自己消息发送成功:' . $send . PHP_EOL;

            $return['message']['self'] = false;
            $send = json_encode($return, JSON_UNESCAPED_UNICODE);
            if (empty($to)) {
                echo '给除了自己的所有人发送消息' . PHP_EOL;
                $this->batchSendMessage($this->web_socket->connections, $send, $fd);
            } else {
//            给指定的人发送
                echo '给指定的人发送' . PHP_EOL;
                $this->batchSendMessage($to, $send, $fd);
            }
        } else {
            echo '用户发来的消息为空,不做任何处理' . PHP_EOL;
        }
    }

    /**
     * 批量群发
     * @param array $tos 发送目标用户id
     * @param string $send_data 发送的数据
     * @param int $user_id 发送者id
     */
    private function batchSendMessage($tos, $send_data, $user_id)
    {
        foreach ($tos as $fd) {
            if ($fd != $user_id && $this->checkUserExist($fd)) {
                echo '向用户 user_id=' . $fd . ' 发消息:' . $send_data . PHP_EOL;
                $this->web_socket->push($fd, $send_data);
            }
        }
    }

    /**
     * 判断用户是否登录
     * @param int $user_id 用户id
     * @return bool
     */
    private function checkUserExist($user_id)
    {
        $res = $this->redis_cli->hExists(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_id);
        echo '检查用户 user_id=' . $user_id . ' 是否在线:' . $res . PHP_EOL;
        return $res;
    }

    /**
     * 连接redis
     * @param $conf
     * @return Redis
     */
    private function connectRedis($conf)
    {
        $redis = new \Redis();
        $redis->connect($conf['host'], $conf['port']);
        echo 'redis 已连接' . PHP_EOL;
        return $redis;
    }

    /**
     * 创建web socket
     * @param $conf
     * @return swoole_websocket_server
     */
    private function createWebSocket($conf)
    {
        $web_socket = new swoole_websocket_server($conf['host'], $conf['port']);
        echo 'web socket 已创建' . PHP_EOL;
        return $web_socket;
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
    ]
];

$chat_server = new ChatServer($conf);
$chat_server->run();
