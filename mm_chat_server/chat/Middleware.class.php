<?php
namespace testapp;
/**
 * 如果想设置全局中间件（函数名|任何请求都会经过）就在静态方法以‘__’开头，比如‘__global($input)’
 * Class Middleware
 * @package testapp
 */
class Middleware
{
    //注意多个全局中间件定义顺序，从上到下执行
    public static function __global($request)
    {
        // 全局公用中间件
        echo 'global' . PHP_EOL;
        return $request;
    }

    public static function __test($input)
    {
        echo 'test' . PHP_EOL;
        return $input;
    }

    public static function checkLogin($request)
    {
        // 示例检查是否登录中间件
        echo 'checklogin' . PHP_EOL;
        return $request;
    }

    public static function checkPrivilege($input)
    {
        // 示例检查权限中间件
        echo 'checkprivilege' . PHP_EOL;
        return $input;
    }
}