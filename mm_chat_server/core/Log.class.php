<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/2/13
 * Time: 下午1:02
 */

namespace core;
class Log
{
    /**
     * 写入log
     * @param int $level 日志级别
     * @param string $info 日志信息
     * @throws \Exception
     */
    private static function log($level, $info)
    {
        $logDir = RUNTIME_DIR . '/' . MODULE . '/log/' . date('Y_m_d');
        if (!file_exists($logDir) || !is_writeable($logDir)) {
            if (!@mkdir($logDir, 0755, true)) {
                throw new \Exception($logDir . '目录创建失败！', CoreErrorCode::SYSTEM_FOLDER_CREATE_FAIL);
            }
        }
        $logFile = $logDir . '/' . date('Y_m_d_H') . '_' . $level . '.log';
        $time = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace();
        $backtrace_call = $backtrace[1]; // 谁调用的log方法
        $file = $backtrace_call['file'];
        $line = $backtrace_call['line'];
        $logInfo = "[" . $level . "] [$time] file:$file line:$line log: $info";
        file_put_contents($logFile, $logInfo . PHP_EOL, FILE_APPEND);
    }

    /**
     * 错误信息
     * @param string $message 错误信息
     */
    public static function error($message)
    {
        self::log('ERROR', $message);
    }

    /**
     * 警告信息
     * @param string $message 警告信息
     */
    public static function warning($message)
    {
        self::log('WARNING', $message);
    }

    /**
     * 普通信息
     * @param string $message 普通信息
     */
    public static function info($message)
    {
        self::log('INFO', $message);
    }

    /**
     * 调试日志
     * @param string $message 调试信息
     */
    public static function debug($message)
    {
        self::log('DEBUG', $message);
    }
}