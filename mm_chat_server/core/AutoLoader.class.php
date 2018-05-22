<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2017/11/13
 * Time: 上午10:22
 */

class AutoLoader
{
    protected $map = [];
    protected $classMap = [];
    const CLASS_MAP_PATH = RUNTIME_DIR . '/ClassMap.php';

    /**
     * 自动获取顶级目录
     * AutoLoader constructor.
     */
    public function __construct()
    {
        $fh = opendir(ROOT_DIR);
        while ($file = readdir($fh)) {
            if (!in_array($file, ['.', '..', '.git', 'public'])) {
                $dir = ROOT_DIR . '/' . $file;
                if (is_dir($dir)) {
                    $this->map[$file] = $dir;
                }
            }
        }
        closedir($fh);
        if (file_exists(self::CLASS_MAP_PATH)) {
            $inc = include self::CLASS_MAP_PATH;
            if (is_array($inc)) {
                $this->classMap = $inc;
            }
        }
    }

    /**
     * 自动加载器
     * @param string $class 类
     * @throws Exception
     */
    public function autoload($class)
    {
        $file = self::findFile($class);
        self::includeFile($file);
    }

    /**
     * 缓存
     * @param string $class 类名
     * @param string $path  路径
     */
    private function cache($class, $path)
    {
        $this->classMap[$class] = $path;
        file_put_contents(self::CLASS_MAP_PATH, '<?php ' . PHP_EOL . 'return ' . var_export($this->classMap, true) . ';');
    }

    /**
     * 解析文件路径
     * @param string $class 类
     * @return string 类的路径
     * @throws Exception
     */
    private function findFile($class)
    {
        if (!$GLOBALS['config']['debug'] && !empty($this->classMap) && array_key_exists($class, $this->classMap)) {
            //关闭开发模式
            return $this->classMap[$class];
        }
        $className = trim(strrchr($class, '\\'), '\\');//类名
        $vendorName = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        $vendorDir = $this->map[$vendorName]; // 命名空间目录
        $filePath = strstr(substr($class, strlen($vendorName)), '\\' . $className, true);// 文件相对路径
        // 文件绝对路径
        $fileDir = strtr($vendorDir . $filePath, '\\', DIRECTORY_SEPARATOR);
        $dh = opendir($fileDir);
        $realClassName = '';
        //domain.com/module/usertest/action  => UserTestController.class  文件名和类名大小写一样
        while ($file = readdir($dh)) {
            // 遍历类所在命名空间（目录）查找全小写文件名是否匹配
            if ($file != "." && $file != "..") {
                if (strpos(strtolower($file), strtolower($className))===0) {
                    $realClassName = $file;
                    break;
                }
            }
        }
        closedir($dh);
        if ($realClassName === '') {
            throw new \Exception($className . '控制器类不存在！', \core\CoreErrorCode::SYSTEM_FILE_NOT_EXISTS);
        }
        $path = $fileDir . "/" . $file;
        if (!$GLOBALS['config']['debug']) {
            // 关闭开发模式缓存
            $this->cache($class, $path);
        }
        return $path;
    }

    /**
     * 引入文件
     * @param string $file 文件路径
     * @throws Exception
     */
    private function includeFile($file)
    {
        if (is_file($file)) {
            include $file;
        } else {
            throw new \Exception($file . ' 不是一个文件！', \core\CoreErrorCode::SYSTEM_THIS_NOT_FILE);
        }
    }
}