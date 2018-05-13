<?php

/**
 * 图灵机器人api2.0
 * author: BaAGee
 * createTime: 2018/5/14 0:59
 */
class TulingRobot
{
    private $apiKey = '';

    /*发送文本类型*/
    const REQ_TEXT = 0;
    /*发送图片类型*/
    const REQ_IMAGE = 1;
    /*发送音频类型*/
    const REQ_MEDIA = 2;

    const API_URL = 'http://openapi.tuling123.com/openapi/api/v2';

    /*错误码及其错误描述*/
    private $error_map = [
        '5000' => '无解析结果',
        '6000' => '暂不支持该功能',
        '4000' => '请求参数格式错误',
        '4001' => '加密方式错误',
        '4002' => '无功能权限',
        '4003' => '该apikey没有可用请求次数',
        '4005' => '无功能权限',
        '4007' => 'apikey不合法',
        '4100' => 'userid获取失败',
        '4200' => '上传格式错误',
        '4300' => '批量操作超过限制',
        '4400' => '没有上传合法userid',
        '4500' => 'userid申请个数超过限制',
        '4600' => '输入内容为空',
        '4602' => '输入文本内容超长(上限150)',
        '7002' => '上传信息失败',
        '8008' => '服务器错误',
    ];

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * 请求方法
     * @param string $input 请求参数
     * @param int $userId 用户id
     * @param int $reqType 发送消息的类型 只能是 以上定义的
     * @return mixed
     */
    public function request($input, $userId, $reqType = 0)
    {
        switch ($reqType) {
            case self::REQ_TEXT:
                $perception = [
                    'inputText' => [
                        'text' => $input
                    ]
                ];
                break;
            case self::REQ_IMAGE:
                $perception = [
                    'inputImage' => [
                        'url' => $input
                    ]
                ];
                break;
            case self::REQ_MEDIA:
                $perception = [
                    'inputMedia' => [
                        'url' => $input
                    ]
                ];
                break;
            default:
                $perception = [
                    'inputText' => [
                        'text' => $input
                    ]
                ];
                break;
        }
        $send = [
            'reqType' => $reqType,
            'perception' => $perception,
            'userInfo' => [
                'apiKey' => $this->apiKey,
                'userId' => $userId
            ]
        ];
        $res = $this->curlPost(json_encode($send, JSON_UNESCAPED_UNICODE));
        if (in_array($res['intent']['code'], array_keys($this->error_map))) {
            $res['results'][0]['resultType'] = 'text';
            $res['results'][0]['values']['text'] = $this->error_map[$res['intent']['code']];
        }
        return $res['results'];
    }

    /**
     * curl发送post请求
     * @param string $data 请求json字符串
     * @return mixed
     */
    private function curlPost($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        $header = ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }
}

$apiKey = 'fc48aee50a9a46f888379777d133631b';
$robot = new TulingRobot($apiKey);


// $send='测试的';
// $res=$robot->request($send,2,TulingRobot::REQ_TEXT);

$send = 'img/em_20.jpg';
$res = $robot->request($send, 2, TulingRobot::REQ_IMAGE);

var_dump($res);
