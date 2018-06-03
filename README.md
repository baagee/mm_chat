# 聊天室

> 秘密聊天室，使用vue搭建界面，php的扩展swoole写websocket服务端，具有发表情，发图片(能粘贴/推拽图片发送)，@某人的功能。

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

#### upload_api文件夹的auth.php为文件上传授权接口，chat_server文件夹的脚本为提供websocket服务端的脚本，在php cli模式下运行

- 需要安装redis，安装php_redis扩展，安装swoole扩展
- 运行前请配置chat_server/run.php里面的图灵机器人apiKey(tuling_robot_apikey)
- 配置upload_api/public/auth.php里面的腾讯对象存储参数，如下：
```php
// 配置参数
$config = array(
    'Url' => 'https://sts.api.qcloud.com/v2/index.php',
    'Domain' => 'sts.api.qcloud.com',
    'Proxy' => '',
    'SecretId' => '', // 改自己的
    'SecretKey' => '', // 改自己的
    'Bucket' => 'ssss-1234123',// 改自己的
    'Region' => 'ap-beijing',// 改自己的
    'AllowPrefix' => 'images/*', // 不需要改
);
```
- 配置前端App.vue里面的bucket与region:
```
Bucket: "ssss-1234123",//改成你自己的
Region: "ap-beijing"//改成你自己的
```
- 在main.js里面配置你的服务器地址:
```
const HOST = '192.168.117.142'
```