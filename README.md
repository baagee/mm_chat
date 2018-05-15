# 聊天室

> 秘密聊天室，使用vue搭建界面，php的扩展swoole写websocket服务端，具有发表情，发图片(能粘贴图片发送)，@某人的功能。

访问链接：[点击进入聊天室](http://chat.baagee.vip)

#### Build Setup

```bash
#install dependencies
npm install
# serve with hot reload at localhost:8080
npm run dev
# build for production with minification
npm run build
# build for production and view the bundle analyzer report
npm run build --report
```

#### upload_api文件夹的upload.php为文件上传接口，chat_server文件夹的脚本为提供websocket服务端的脚本，在php cli模式下运行

- 运行前请配置chat_server/run.php里面的图灵机器人apiKey(tuling_robot_apikey)
- 配置upload_api/public/upload.php里面的腾讯对象存储参数，如下：

```php
$qc_config = array(
    'app_id' => '',
    'secret_id' => '',
    'secret_key' => '',
    'region' => 'bj',   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
    'timeout' => 60
);
$bucket = 'chat-room';
```