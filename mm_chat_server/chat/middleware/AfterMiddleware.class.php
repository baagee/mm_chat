<?php
/**
 *  author: BaAGee
 *  createTime: 2018/5/3 20:13
 */
namespace chat\middleware;
/**
 * 后置中间件类
 * 如果想设置全局后置中间件（函数名|任何请求都会经过）就在静态方法以‘g_’开头，比如‘g_global($input)’
 * Class AfterMiddleware
 * @package testapp\middleware
 */
class AfterMiddleware
{
    public static function g_global($response)
    {
//        echo 'after middleware global' . PHP_EOL;
        return $response;
    }

    public static function testAfter($response)
    {
//        var_dump($response);
//        echo 'testafter' . PHP_EOL;
//        $response['password'] = md5($response['password']);
//        $response['testafter'] = time();
//        throw new \Exception('后置错误', 23542);
         return $response;
    }
}
 