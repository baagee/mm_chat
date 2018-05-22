<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/1/24
 * Time: 上午11:52
 */

namespace core;

class FileCache
{
    const CACHE_DIR = RUNTIME_DIR . '/data';
    const SPLIT = 'SPLIT-_-SPLIT';

    /**
     * 保存数据 默认永久保存100年
     * @param string                                    $key  保存的名字
     * @param string|array|int|float|double|object|bool $data 要保存的数据
     * @param int                                       $time 保存的时间 秒数
     * @throws \Exception
     */
    public static function save($key, $data, $time = 3600 * 24 * 365 * 100)
    {
        if (is_array($data) || is_object($data)) {
            $data = $time . self::SPLIT . serialize($data);
        } else {
            $data = $time . self::SPLIT . strval($data);
        }
        if (!file_exists(self::CACHE_DIR) || !is_writeable(self::CACHE_DIR)) {
            if (!@mkdir(self::CACHE_DIR, 0755, true)) {
                throw new \Exception(self::CACHE_DIR . '目录创建失败！', CoreErrorCode::SYSTEM_FOLDER_CREATE_FAIL);
            }
        }
        $f = fopen(self::CACHE_DIR . '/' . MODULE . '_' . $key, 'w');
        fwrite($f, $data);
        fclose($f);
    }

    /**
     * 删除数据
     * @param string $key 名字
     * @return bool
     */
    public static function delete($key)
    {
        $file = self::CACHE_DIR . '/' . MODULE . '_' . $key;
        if (file_exists($file) && is_file($file)) {
            unlink($file);
        }
        return true;
    }

    /**
     * 删除所有的
     * @return bool
     */
    public static function deleteAll()
    {
        $dh = opendir(self::CACHE_DIR);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullPath = self::CACHE_DIR . "/" . $file;
                if (is_file($fullPath)) {
                    unlink($fullPath);
                }
            }
        }
        closedir($dh);
        return true;
    }

    /**
     * 获取数据
     * @param string $key 名字
     * @return bool|mixed
     */
    public static function get($key)
    {
        $file = self::CACHE_DIR . '/' . MODULE . '_' . $key;
        if (!file_exists($file) || !is_file($file)) {
            return false;
        }
        if (self::isExpired($key)) {
            return false;
        } else {
            $contentStr = file_get_contents($file);
            $content = explode(self::SPLIT, $contentStr)[1];
            if (self::isSerialized($content)) {
                return unserialize($content);
            } else {
                return $content;
            }
        }
    }

    /**
     * 检查是否过期
     * @param string $key 名字
     * @return bool 过期返回true
     * @throws \Exception
     */
    public static function isExpired($key)
    {
        $file = self::CACHE_DIR . '/' . MODULE . '_' . $key;
        if (!file_exists($file) || !is_file($file)) {
            throw new \Exception($key . '缓存不存在', CoreErrorCode::SYSTEM_FILE_CACHE_NOT_EXISTS);
        }
        $contentStr = file_get_contents($file);
        $time = explode(self::SPLIT, $contentStr)[0];
        $fileTime = filemtime($file);
        if ($fileTime + $time < time()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取所有保存的key
     * @return array
     */
    public static function allKeys()
    {
        $keys = [];
        $dh = opendir(self::CACHE_DIR);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullPath = self::CACHE_DIR . "/" . $file;
                if (is_file($fullPath)) {
                    $key = str_replace(MODULE . '_', '', $file);
                    if (!self::isExpired($key)) {
                        //只显示有效期内的key
                        $keys[] = $key;
                    }
                }
            }
        }
        closedir($dh);
        return $keys;
    }

    /**
     * 判断是否为序列化后的数据
     * @param string $data 要检查的数据
     * @return bool
     */
    private static function isSerialized($data)
    {
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a' :
            case 'O' :
            case 's' :
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }
}