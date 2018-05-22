<?php

/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/3/22
 * Time: 下午7:42
 */
abstract class ASCommon
{
    public static $appName;

    /**
     * 检查PHP版本
     */
    public static function checkVersion()
    {
        if (!version_compare(phpversion(), '7.0', '>=')) {
            die('你的PHP版本太低了，请升级PHP版本到7.0及其以上！');
        }
    }

    /**
     * 设置时区
     */
    protected static function setTimezone()
    {
        ini_set('date.timezone', $GLOBALS['config']['timezone']);
    }

    /**
     * 设置全局配置信息
     */
    protected static function getConfig()
    {
        $GLOBALS['config'] = include_once str_replace('core', self::$appName, __DIR__) . '/config.php';
    }

    /**
     * 设置显示错误信息级别
     */
    protected static function setError()
    {
        if ($GLOBALS['config']['debug']) {
            //  显示所有错误
            @ini_set('error_reporting', E_ALL);
        } else {
            @ini_set('error_reporting', E_ERROR);
        }
        @ini_set('display_errors', $GLOBALS['config']['debug']);
    }

    /**
     * 类的自动加载
     */
    protected static function autoLoad()
    {
        include_once __DIR__ . '/AutoLoader.class.php';
        spl_autoload_register(array(new AutoLoader(), 'autoload'));
        include_once str_replace('core', 'vendor', __DIR__) . '/autoload.php';
    }

    /**
     * 设置常用常量
     */
    protected static function setConst()
    {
        // 设置根目录
        define("ROOT_DIR", str_replace('/core', '', str_replace('\\', '/', __DIR__)));
        // app名字
        define('APP_NAME', self::$appName);
        // 框架核心dir
        define('CORE_DIR', ROOT_DIR . '/core');
        // public 目录
        define('PUBLIC_DIR', ROOT_DIR . '/public');
        // app项目目录
        define('APP_DIR', ROOT_DIR . '/' . trim(self::$appName));
        // 缓存目录
        define('RUNTIME_DIR', APP_DIR . '/runtime');
    }
}