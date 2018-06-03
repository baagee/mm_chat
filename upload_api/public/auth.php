<?php

// header('Access-Control-Allow-Origin:http://localhost:8080');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type:application/json; charset=utf-8');
header('Access-Control-Allow-Credentials:true');

// 配置参数
$config = array(
    'Url' => 'https://sts.api.qcloud.com/v2/index.php',
    'Domain' => 'sts.api.qcloud.com',
    'Proxy' => '',
    'SecretId' => '', // 改自己的
    'SecretKey' => '', // 改自己的
    'Bucket' => 'ssss-1234123',// 改自己的
    'Region' => 'ap-beijing',// 改自己的
    'AllowPrefix' => 'images/*', // 这里改成路径前缀，例子：* 或者 a/*
);
// 缓存临时密钥
if (!isset($_SESSION['tempKeysCache'])) {
    $_SESSION['tempKeysCache'] = array(
        'policyStr' => '',
        'expiredTime' => 0
    );
}

function json2str($obj) {
    ksort($obj);
    $arr = array();
    foreach ($obj as $key => $val) {
        array_push($arr, $key . '=' . $val);
    }
    return join('&', $arr);
}
// 计算临时密钥用的签名
function getSignature($opt, $key, $method) {
    global $config;
    $formatString = $method . $config['Domain'] . '/v2/index.php?' . json2str($opt);
    $sign = hash_hmac('sha1', $formatString, $key);
    $sign = base64_encode(hex2bin($sign));
    return $sign;
}
// 获取临时密钥
function getTempKeys() {
    global $config;
    // 判断是否修改了 AllowPrefix
    if ($config['AllowPrefix'] === '_ALLOW_DIR_/*') {
        return array('error'=> '请修改 AllowPrefix 配置项，指定允许上传的路径前缀');
    }
    $ShortBucketName = substr($config['Bucket'],0, strripos($config['Bucket'], '-'));
    $AppId = substr($config['Bucket'], 1 + strripos($config['Bucket'], '-'));
    $policy = array(
        'version'=> '2.0',
        'statement'=> array(
            array(
                'action'=> array(
                    // 简单文件操作
                    'name/cos:PutObject',
                    'name/cos:PostObject',
                    'name/cos:AppendObject',
                    'name/cos:GetObject',
                    'name/cos:HeadObject',
                    'name/cos:OptionsObject',
                    'name/cos:PutObjectCopy',
                    'name/cos:PostObjectRestore',
                ),
                'effect'=> 'allow',
                'principal'=> array('qcs'=> array('*')),
                'resource'=> array(
                    'qcs::cos:' . $config['Region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/',
                    'qcs::cos:' . $config['Region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . $config['AllowPrefix']
                )
            )
        )
    );
    $policyStr = str_replace('\\/', '/', json_encode($policy));
    // 有效时间小于 30 秒就重新获取临时密钥，否则使用缓存的临时密钥
    if (isset($_SESSION['tempKeysCache']) && isset($_SESSION['tempKeysCache']['expiredTime']) && isset($_SESSION['tempKeysCache']['policyStr']) &&
        $_SESSION['tempKeysCache']['expiredTime'] - time() > 30 && $_SESSION['tempKeysCache']['policyStr'] === $policyStr) {
        return $_SESSION['tempKeysCache'];
    }
    $Action = 'GetFederationToken';
    $Nonce = rand(10000, 20000);
    $Timestamp = time() - 1;
    $Method = 'GET';
    $params = array(
        'Action'=> $Action,
        'Nonce'=> $Nonce,
        'Region'=> '',
        'SecretId'=> $config['SecretId'],
        'Timestamp'=> $Timestamp,
        'durationSeconds'=> 7200,
        'name'=> '',
        'policy'=> $policyStr
    );
    $params['Signature'] = urlencode(getSignature($params, $config['SecretKey'], $Method));
    $url = $config['Url'] . '?' . json2str($params);
    $ch = curl_init($url);
    $config['Proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['Proxy']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    if(curl_errno($ch)) $result = curl_error($ch);
    curl_close($ch);
    $result = json_decode($result, 1);
    if (isset($result['data'])) $result = $result['data'];
    $_SESSION['tempKeysCache'] = $result;
    $_SESSION['tempKeysCache']['policyStr'] = $policyStr;
    return $result;
};
// 计算 COS API 请求用的签名
function getAuthorization($keys, $method, $pathname)
{
    // 获取个人 API 密钥 https://console.qcloud.com/capi
    $SecretId = $keys['credentials']['tmpSecretId'];
    $SecretKey = $keys['credentials']['tmpSecretKey'];
    // 整理参数
    $query = array();
    $headers = array();
    $method = strtolower($method ? $method : 'get');
    $pathname = $pathname ? $pathname : '/';
    substr($pathname, 0, 1) != '/' && ($pathname = '/' . $pathname);
    // 工具方法
    function getObjectKeys($obj)
    {
        $list = array_keys($obj);
        sort($list);
        return $list;
    }
    function obj2str($obj)
    {
        $list = array();
        $keyList = getObjectKeys($obj);
        $len = count($keyList);
        for ($i = 0; $i < $len; $i++) {
            $key = $keyList[$i];
            $val = isset($obj[$key]) ? $obj[$key] : '';
            $key = strtolower($key);
            $list[] = rawurlencode($key) . '=' . rawurlencode($val);
        }
        return implode('&', $list);
    }
    // 签名有效起止时间
    $now = time() - 1;
    $expired = $now + 600; // 签名过期时刻，600 秒后
    // 要用到的 Authorization 参数列表
    $qSignAlgorithm = 'sha1';
    $qAk = $SecretId;
    $qSignTime = $now . ';' . $expired;
    $qKeyTime = $now . ';' . $expired;
    $qHeaderList = strtolower(implode(';', getObjectKeys($headers)));
    $qUrlParamList = strtolower(implode(';', getObjectKeys($query)));
    // 签名算法说明文档：https://www.qcloud.com/document/product/436/7778
    // 步骤一：计算 SignKey
    $signKey = hash_hmac("sha1", $qKeyTime, $SecretKey);
    // 步骤二：构成 FormatString
    $formatString = implode("\n", array(strtolower($method), $pathname, obj2str($query), obj2str($headers), ''));
    header('x-test-method', $method);
    header('x-test-pathname', $pathname);
    // 步骤三：计算 StringToSign
    $stringToSign = implode("\n", array('sha1', $qSignTime, sha1($formatString), ''));
    // 步骤四：计算 Signature
    $qSignature = hash_hmac('sha1', $stringToSign, $signKey);
    // 步骤五：构造 Authorization
    $authorization = implode('&', array(
        'q-sign-algorithm=' . $qSignAlgorithm,
        'q-ak=' . $qAk,
        'q-sign-time=' . $qSignTime,
        'q-key-time=' . $qKeyTime,
        'q-header-list=' . $qHeaderList,
        'q-url-param-list=' . $qUrlParamList,
        'q-signature=' . $qSignature
    ));
    return $authorization;
}
// 开启 session 缓存临时密钥
session_start();
// 获取前端过来的参数
$method = isset($_GET['method']) ? $_GET['method'] : 'get';
$pathname = isset($_GET['pathname']) ? $_GET['pathname'] : '/';
// 获取临时密钥，计算签名
$tempKeys = getTempKeys();
if ($tempKeys && isset($tempKeys['credentials'])) {
    $data = array(
        'Authorization' => getAuthorization($tempKeys, $method, $pathname),
        'XCosSecurityToken' => $tempKeys['credentials']['sessionToken'],
    );
} else {
    $data = array('error'=> $tempKeys);
}
// 返回数据给前端
echo json_encode($data);