<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/1/18
 * Time: 下午2:36
 */

include_once __DIR__ . '/ASCommon.class.php';

class Application extends ASCommon
{
    // 初始化静态方法
    public static function run($app)
    {
        //首先检查php版本
        self::checkVersion();
        self::$appName = $app;
        // 1：加载配置文件
        self::getConfig();
        // 2：设置错误信息级别
        self::setError();
        // 3：设置时区
        self::setTimezone();
        // 4：初始化系统常量
        self::setConst();
        // 5：初始化header
        self::setHeader();
        // 6：自动加载
        self::autoLoad();
        // 7：session开启
        self::startSession();
        // 8：开始执行
        self::execute(self::route());
    }

    private static function route()
    {
        extract(\core\Route::__init__());
        define('MODULE', $h_array[0]);
        define('CONTROLLER', $h_array[1]);
        define('ACTION', $h_array[2]);
        // 模块目录
        define('MODULE_DIR', APP_DIR . '/' . MODULE);
        // 控制器目录
        define('CONTROLLER_DIR', MODULE_DIR . '/controller');
        return ['params' => $params, 'after_middleware' => $after_middleware];
    }

    /**
     * 设置字符编码
     */
    private static function setHeader()
    {
        header('Content-type: text/json');
        // 最多支持这么多请求方式
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    }

    /**
     * 开启session
     */
    private static function startSession()
    {
        session_start(['cookie_httponly' => true]);
    }

    /**
     * 程序开始执行
     */
    private static function execute($vars)
    {
        $controller = '\\' . self::$appName . '\\' . MODULE . '\\controller\\' . CONTROLLER . 'Controller';
        $obj = new $controller();
        if (method_exists($obj, ACTION)) {
            $data = [
                'err_no' => 0,
                'err_msg' => 'success',
                'server_time' => time(),
                'data' => null
            ];
            try {
                $response = $obj->execute(ACTION, $vars['params']);
                // 处理中间件
                $response=\core\Middleware::execAfterMiddleware($response,$vars['after_middleware']);
                $data['data'] = $response;
            } catch (\Exception $e) {
                $log_err_msg = 'err_code:[' . $e->getCode() . '], err_msg[' . $e->getMessage() . ']';
                \core\Log::error($log_err_msg);
                $data['err_no'] = $e->getCode();
                $data['err_msg'] = $e->getMessage();
                $data['data'] = null;
                if ($GLOBALS['config']['debug'] === false && $data['err_no'] < intval(200000)) {
                    // 关闭开发模式隐藏系统错误
                    $data['err_msg'] = 'system error';
                }
            }
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit(0);
        } else {
            ob_end_clean();
            header("HTTP/1.1 404 Not Found");
            exit;
        }
    }
}