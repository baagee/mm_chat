<?php

/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/3/3
 * Time: 下午5:02
 */
include_once __DIR__ . '/ASCommon.class.php';

abstract class Script extends ASCommon
{
    protected static $moduleName = '';

    public function __construct($app_name)
    {
        self::checkVersion();
        self::$appName = $app_name;
        self::$moduleName = array_slice(explode('/', debug_backtrace()[0]['file']), -3, 1)[0];
        self::getConfig();
        self::setError();
        self::setConst();
        self::setTimezone();
        self::autoLoad();
    }

    /**
     * 设置常量
     */
    protected static function setConst()
    {
        define('MODULE', self::$moduleName);
        parent::setConst();
        // 模块目录
        define('MODULE_DIR', APP_DIR . '/' . MODULE);
        // 控制器目录
        define('CONTROLLER_DIR', MODULE_DIR . '/controller');
    }

    /*
     * 脚本开始执行
     */
    abstract public function run();
}