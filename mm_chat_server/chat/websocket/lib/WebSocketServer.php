<?php

/**
 * websocket服务端
 * author: BaAGee
 * createTime: 2018/5/8 21:09
 */
namespace chat\websocket\lib;

use chat\dao\UserDao;

class WebSocketServer
{
    protected $redis_cli = null;
    protected $web_socket = null;
    protected $robot = null;
//    protected $mysql_cli = null;

    const ACTION_LOGIN = 'login';
    const ACTION_USER_ONLINE = 'user_online';
    const ACTION_LOGOUT_OTHER = 'logout_other';
    const ACTION_CHAT = 'chat';
    const ACTION_HEART = 'heart';
    const REDIS_ONLINE_USERS_KEY = 'online_users';

//    const TULING_API = 'openapi.tuling123.com';
//    const TULING_API_PATH = '/openapi/api/v2';

    public function __construct($conf)
    {
        try {
            $this->redis_cli = $this->connectRedis($conf['redis']);
            $this->web_socket = $this->createWebSocket($conf['web_socket']);
//            $this->robot = $this->createRobot($conf['tuling_robot_apikey']);

            $this->initOnlineUsers();
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
                // 处理登录
                $this->login($data, $request->fd);
                break;
            case self::ACTION_CHAT:
                // 处理聊天
                $this->chat($data['nickname'], $request->fd, $data['message'], $data['avatar_id'], $data['to']);
                break;
            case self::ACTION_HEART:
                echo '心跳包' . PHP_EOL;
                break;
        }
        echo 'on message over' . PHP_EOL;
    }

    public function onClose($ws, $user_id)
    {
        $delete_user = json_decode($this->redis_cli->hGet(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_id), true);
        echo '用户：' . $delete_user['nickname'] . '下线' . PHP_EOL;
        // 删除redis在线用户
        $this->redis_cli->hDel(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_id);
        /*通知在线用户有人下线*/
        $publish_user_logout = json_encode([
            'action' => self::ACTION_LOGOUT_OTHER,
            'user_id' => $user_id
        ], JSON_UNESCAPED_UNICODE);
        $this->batchSendMessage($this->web_socket->connections, $publish_user_logout, $user_id);
    }

    private function login($user_info, $fd)
    {
        echo '------------------------on message login--------------------------' . PHP_EOL;
        $user_info['fd'] = $fd;
        $this->redis_cli->hSet(self::REDIS_ONLINE_USERS_KEY, 'user_' . $user_info['user_id'], json_encode($user_info, JSON_UNESCAPED_UNICODE));
        $return = [
            'action' => self::ACTION_LOGIN,
            'res' => true,
            'myself' => $user_info
        ];
//        $userDao=new UserDao();
//        var_dump($userDao->select([]));
        /*获取好友信息*/
        $online_users = $this->redis_cli->hGetAll(self::REDIS_ONLINE_USERS_KEY);
        /*给自己发送登陆成功消息*/

        $return['online_users'] = array_values($online_users);
        $return = json_encode($return, JSON_UNESCAPED_UNICODE);
        $this->web_socket->push($fd, $return);
        echo '通知自己登陆成功:' . $return . PHP_EOL;
        /*给所有除了自己的在线用户发消息新用户上线*/
        $publish_user_online = json_encode([
            'action' => self::ACTION_USER_ONLINE,
            'user_info' => $user_info
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
            // 给自己发消息说发送成功
            $return = [
                'action' => self::ACTION_CHAT,
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
                if (in_array(-1, $to)) {
                    // 如果是给机器人发消息，通知大家
                    $return['message']['self'] = false;
                    $this->batchSendMessage($this->web_socket->connections, $send, $fd);
                }
                // 给指定的人发送
                $return['message']['at_you'] = true;
                $send = json_encode($return, JSON_UNESCAPED_UNICODE);
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
            if ($fd == -1) {
                echo '请求图灵api' . PHP_EOL;
                $this->postRobot($send_data, $user_id);
            } else {
                if ($fd != $user_id && $this->checkUserExist($fd) && $this->web_socket->exist($fd)) {
                    echo '向用户 user_id=' . $fd . ' 发消息:' . $send_data . PHP_EOL;
                    $this->web_socket->push($fd, $send_data);
                }
            }
        }
    }

    /**
     * 处理和机器人的对话
     * @param $send_data
     * @param $user_id
     */
    private function postRobot($send_data, $user_id)
    {
        $send_data = json_decode($send_data, true);
        $at_nickname = $send_data['message']['nickname'];
        $send_data['message']['nickname'] = '机器人-小希';
        $send_data['message']['avatar_id'] = 261;
        $send_data['message']['time'] = date('H:i:s');
        unset($send_data['message']['at_you']);

        $message = str_replace('@机器人-小希 ', '', $send_data['message']['message']);
        if (strpos($message, '[img]:') === false) {
            if (trim($message) != '') {
                $requestJson = $this->robot->buildPostParams($message, $user_id, TulingRobot::REQ_TEXT);
            }
        } else {
            $requestJson = $this->robot->buildPostParams(str_replace('[img]:', '', $message), $user_id, TulingRobot::REQ_IMAGE);
        }
        if (trim($message) != '') {
            /*swoole 异步方式*/
            $cli = new \swoole_http_client(self::TULING_API, 80);
            $cli->post(self::TULING_API_PATH, $requestJson, function ($cli) use ($at_nickname, $send_data) {
                $res = json_decode($cli->body, true);
                echo '机器人响应：' . PHP_EOL;
                var_dump($cli->body);
                $response = $this->robot->getReturn($res);
                if (is_array($response)) {
                    foreach ($response as $item) {
                        /*文本(text);连接(url);音频(voice);视频(video);图片(image);图文(news)*/
                        if ($item['resultType'] === 'text') {
                            /*返回文字消息*/
                            $send_data['message']['message'] = '@' . $at_nickname . ' ' . $item['values']['text'];
                        } else if ($item['resultType'] === 'url') {
                            /*返回url地址*/
                            $send_data['message']['message'] = '[url]:' . $item['values']['url'];
                        } else if ($item['resultType'] === 'image') {
                            /*返回图片*/
                            $send_data['message']['message'] = '@' . $at_nickname . ' 丢你一张图';
                            $this->batchSendMessage($this->web_socket->connections, json_encode($send_data, JSON_UNESCAPED_UNICODE), -1);
                            $send_data['message']['message'] = '[img]:' . $item['values']['image'];
                        } else {
                            /*其他类型不应答*/
                            $send_data['message']['message'] = '@' . $at_nickname . ' &（*……￥￥@%￥E&*^&*()^*(7';
                        }
                        // 向所有人发送机器人结果
                        $this->batchSendMessage($this->web_socket->connections, json_encode($send_data, JSON_UNESCAPED_UNICODE), -1);
                    }
                } else {
                    $send_data['message']['message'] = '@' . $at_nickname . ' 我出错了';
                    $this->batchSendMessage($this->web_socket->connections, json_encode($send_data, JSON_UNESCAPED_UNICODE), -1);
                }
            });
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
        echo '检查用户 user_id=' . $user_id . ' 是否在redis:' . $res . PHP_EOL;
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
     * 启动时删除所有以前的在线用户
     */
    private function initOnlineUsers()
    {
        $this->redis_cli->delete(self::REDIS_ONLINE_USERS_KEY);
    }

    /**
     * 创建web socket
     * @param $conf
     * @return swoole_websocket_server
     */
    private function createWebSocket($conf)
    {
        $web_socket = new \swoole_websocket_server($conf['host'], $conf['port']);
        $web_socket->set(array(
            'heartbeat_check_interval' => 10,//10秒检查一次
            'heartbeat_idle_time' => 10,//如果客户端在10秒没发送消息，就说明挂了，主动断开连接
        ));
        echo 'web socket 已创建' . PHP_EOL;
        return $web_socket;
    }

//    private function createRobot($apiKey)
//    {
//        include_once __DIR__ . '/TulingRobot.php';
//        return new TulingRobot($apiKey, $this->web_socket);
//    }

}
