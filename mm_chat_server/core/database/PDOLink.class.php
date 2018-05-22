<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2017/12/6
 * Time: 下午10:34
 */

namespace core\database;


/**
 * Class PDOLink 单例模式获取pdo连接对象
 * @package core\database
 */
class PDOLink
{
    // 保存唯一数据库连接对象
    private static $PDOLinkObj = null;

    // 封锁外部new操作
    private function __construct()
    {
    }

    // 封锁外部clone
    private function __clone()
    {
    }

    /**
     * 获取pdo连接对象
     * @return null|\PDO
     */
    public static function getObj()
    {
        if (self::$PDOLinkObj === null) {
            $DBHost = $GLOBALS['config']['mysql']['host'];
            $DBUser = $GLOBALS['config']['mysql']['user'];
            $DBPass = $GLOBALS['config']['mysql']['password'];
            $DBPort = $GLOBALS['config']['mysql']['port'];
            $DBCharset = $GLOBALS['config']['mysql']['charset'];
            $DBName = $GLOBALS['config']['mysql']['database'];
            self::$PDOLinkObj = new \PDO('mysql:dbname=' . $DBName . ';host=' . $DBHost . ';port=' . $DBPort, $DBUser, $DBPass, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . $DBCharset . "';"));
            self::$PDOLinkObj->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果
        }
        return self::$PDOLinkObj;
    }
}
