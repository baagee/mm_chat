<?php
/**
 *  author: BaAGee
 *  createTime: 2018/5/3 21:16
 */

namespace core;
/**
 * 处理中间件类
 * Class Middleware
 * @package core
 */
class Middleware
{
    const BEFORE_MIDDLEWARE_FILE = APP_DIR . '/middleware/BeforeMiddleware.class.php';

    const AFTER_MIDDLEWARE_FILE = APP_DIR . '/middleware/AfterMiddleware.class.php';

    /**
     * 处理前置中间件
     * @param array $request 请求参数
     * @param array $routeInfo 匹配到的路由信息
     * @param array $middlewareMap 中间件数组
     * @return array
     */
    public static function execBeforeMiddleware($request, $routeInfo, $middlewareMap)
    {
        $after_middleware = [];//存放后置中间件
        //首先检查前置中间件是否存在
        if (file_exists(self::BEFORE_MIDDLEWARE_FILE)) {
//                    前置中间件存在
            try {
                $m = APP_NAME . '\\middleware\\BeforeMiddleware';
                $class = new \ReflectionClass($m);
                $global_mids = [];
                foreach ($class->getMethods() as $method) {
                    if (strpos($method->name, 'g_') === 0) {
                        //以__开头的方法为全局中间件
                        $method = $method->name;
                        $global_mids[] = $method;
                        $request = $m::$method($request);
                    }
                }
                if (array_key_exists($routeInfo[1], $middlewareMap)) {
                    if (isset($middlewareMap[$routeInfo[1]]['before'])) {
                        $mids = array_diff($middlewareMap[$routeInfo[1]]['before'], $global_mids);
                        if (!empty($mids)) {
                            // 有局部路由中间件，就去中间件处理
                            foreach ($mids as $mid) {
                                $request = $m::$mid($request);
                            }
                        }
                    }
                    if (isset($middlewareMap[$routeInfo[1]]['after'])) {
                        // 获取后置中间件
                        $after_middleware = $middlewareMap[$routeInfo[1]]['after'];
                    }
                }
            } catch (\Exception $e) {
                // 如果报错
                $data = [
                    'err_no' => $e->getCode(),
                    'err_msg' => $e->getMessage(),
                    'server_time' => time(),
                    'data' => null
                ];
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                exit(0);
            }
        } else {
            // 中间件文件不存在
        }
        return [
            'after_middleware' => $after_middleware,
            'params' => $request
        ];
    }

    /**
     * 处理后置中间件
     * @param mixed $response 响应
     * @return mixed
     */
    public static function execAfterMiddleware($response,$after_middleware)
    {
        if (file_exists(self::AFTER_MIDDLEWARE_FILE)) {
            // 后置中间件存在
            $m = APP_NAME . '\\middleware\\AfterMiddleware';
            $class = new \ReflectionClass($m);
            $global_mids = [];
            foreach ($class->getMethods() as $method) {
                if (strpos($method->name, 'g_') === 0) {
                    //以__开头的方法为全局中间件            // 处理全局后置中间件
                    $method = $method->name;
                    $global_mids[] = $method;
                    $response = $m::$method($response);
                }
            }
            if (!empty($after_middleware)) {
                // 如果还有后置中间件
                $mids = array_diff($after_middleware, $global_mids);
                if (!empty($mids)) {
                    // 有局部后置中间件，就去中间件处理
                    foreach ($mids as $mid) {
                        $response = $m::$mid($response);
                    }
                }
            }
        }
        return $response;
    }
}