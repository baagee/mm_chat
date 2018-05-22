<?php
namespace chat\middleware;
/**
 * 如果想设置前置全局中间件（函数名|任何请求都会经过）就在静态方法以‘g_’开头，比如‘g_global($input)’
 * Class Middleware
 * @package testapp
 */
class BeforeMiddleware
{
    //注意多个全局中间件定义顺序，从上到下执行
    public static function g_global($request)
    {
        // 全局公用中间件
        header('Access-Control-Allow-Origin:http://localhost:8080');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Content-Type:application/json; charset=utf-8');
        header('Access-Control-Allow-Credentials:true');
        return $request;
    }

    public static function g_test($input)
    {
//        echo 'test' . PHP_EOL;
        return $input;
    }

    public static function checkLogin($request)
    {
        // 示例检查是否登录中间件
//        echo 'checklogin' . PHP_EOL;
//        throw new \Exception('未登录',235);
//        return $request;
    }

    public static function checkPrivilege($input)
    {
        // 示例检查权限中间件
//        echo 'checkprivilege' . PHP_EOL;
        return $input;
    }
}