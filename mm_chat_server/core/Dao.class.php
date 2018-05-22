<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/2/6
 * Time: 下午8:07
 */

namespace core;

use core\database\MySQLDB;

abstract class Dao extends MySQLDB
{
    // 表名
    public $table = '';
    // 表文件
    protected $table_scheam = [];

    // 最后一次预处理sql语句
    public $lastPrepareSql = '';
    // 最后一次处理sql的数据参数
    public $lastPrepareData = [];

    public function __construct()
    {
        parent::__construct();
        // 获取对应表的scheam文件
        $scheam_file = str_replace('core', APP_NAME, __DIR__) . '/scheams/' . $GLOBALS['config']['mysql']['database'] . '/' . $this->table . '.php';
        if (file_exists($scheam_file)) {
            $this->table_scheam = include $scheam_file;
        } else {
            throw new \Exception($scheam_file . '文件不存在', CoreErrorCode::SYSTEM_FILE_NOT_EXISTS);
        }
    }

    /**
     * 添加数据
     * @param array $data 数据
     * @return bool|string false|插入的ID
     */
    public function insert($data)
    {
        if(method_exists($this,'__autoInsert')){
            $data=array_merge($data,$this->__autoInsert());
        }
        $this->lastPrepareSql = 'INSERT INTO `' . $this->table . '` (';
        $fields = $placeholder = '';
        $this->lastPrepareData = [];
        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($this->table_scheam['columns']))) {
                $fields .= '`' . $k . '`, ';
                $placeholder .= ':' . $k . ', ';
                $this->lastPrepareData[':' . $k] = $v;
            }
        }
        $fields = rtrim($fields, ', ');
        $placeholder = rtrim($placeholder, ', ');
        $this->lastPrepareSql .= $fields . ') VALUES(' . $placeholder . ')';
        if ($this->execute($this->lastPrepareSql, $this->lastPrepareData)) {
            return $this->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * 批量添加数据
     * @param array $data 要添加的二维数组
     * @return int 返回插入的行数
     */
    public function batchInsert($data)
    {
        $this->lastPrepareSql = 'INSERT INTO `' . $this->table . '` (';
        $fields = [];
        $zz = '';
        $this->lastPrepareData = [];
        foreach ($data as $i => $item_array) {
            $z = '(';
            if(method_exists($this,'__autoInsert')){
                $item_array=array_merge($item_array,$this->__autoInsert());
            }
            foreach ($item_array as $k => $v) {
                if (in_array($k, array_keys($this->table_scheam['columns']))) {
                    if (!in_array($k, $fields)) {
                        $fields[] = $k;
                    }
                    $z .= ':' . $k . '_' . $i . ', ';
                    $this->lastPrepareData[':' . $k . '_' . $i] = $v;
                }
            }
            $zz .= rtrim($z, ', ') . '),';
        }
        $fields = implode(', ', $fields);
        $this->lastPrepareSql .= $fields;
        $this->lastPrepareSql = rtrim($this->lastPrepareSql, ', ') . ') VALUES ' . rtrim($zz, ',');
        $res = $this->execute($this->lastPrepareSql, $this->lastPrepareData);
        return $res;
    }

    /**
     * 删除数据
     * @param array $conditions 条件
     *                          [
     *                          'field1'=>['>|<|!=|=',value,'or'],
     *                          'field2'=>['like','%like','and'],
     *                          'field3'=>['[not]in',[1,2,3,4,5],'and']
     *                          'field4'=>['between',[start,end],'or']
     *                          ]
     * @return int 返回影响行数
     */
    public function delete($conditions)
    {
        $this->lastPrepareSql = 'DELETE FROM `' . $this->table . '` WHERE 1 ';
        $whereString = $this->getWhere($conditions);
        if ($whereString) {
            $this->lastPrepareSql .= ' AND ' . $whereString;
        } else {
            // 全删除
        }
        return $this->execute($this->lastPrepareSql, $this->lastPrepareData);
    }

    /**
     * 更新数据
     * @param array $condition 更新条件 eg:
     *                         [
     *                         'field1'=>['>|<|!=|=',value,'or'],
     *                         'field2'=>['like','%like','and'],
     *                         'field3'=>['[not]in',[1,2,3,4,5],'and']
     *                         'field4'=>['between',[start,end],'or']
     *                         ]
     * @param array $data      要更新的数据
     * @return int 返回影响行数
     */
    public function update($condition, $data)
    {
        $this->lastPrepareSql = 'UPDATE `' . $this->table . '` SET ';
        $whereString = $this->getWhere($condition);
        if(method_exists($this,'__autoUpdate')){
            $data=array_merge($data,$this->__autoUpdate());
        }
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($this->table_scheam['columns']))) {
                $this->lastPrepareSql .= '`' . $field . '` = :' . $field . ', ';
                $this->lastPrepareData[':' . $field] = $value;
            }
        }
        $this->lastPrepareSql = rtrim($this->lastPrepareSql, ', ');
        if ($whereString) {
            $this->lastPrepareSql .= ' WHERE ' . $whereString;
        } else {
            // 全更新
        }
        return $this->execute($this->lastPrepareSql, $this->lastPrepareData);
    }

    /**
     * 查询数据
     * @param array  $conditions eg:
     *                           [
     *                           'field1'=>['>|<|!=|=',value,'or'],
     *                           'field2'=>['like','%like','and'],
     *                           'field3'=>['[not]in',[1,2,3,4,5],'and']
     *                           'field4'=>['between',[start,end],'or']
     *                           ]
     * @param array  $fields     字段数组
     * @param string $group_by   $group_by='字段名'
     * @param array  $having     同$conditions的格式
     * @param array  $order_by   order_by=['id'=>'desc','time'=>'asc']
     * @param array  $limit      eg:limit=[1,4]|[1]
     * @return array 结果集二维数组
     * @throws \Exception
     */
    public function select($conditions, $fields = ['*'], $group_by = '', $having = [], $order_by = [], $limit = [])
    {
        $this->lastPrepareSql = 'SELECT ';
        foreach ($fields as $field) {
            if ($field === '*') {
                $this->lastPrepareSql .= '*';
                break;
            }
            if (in_array($field, array_keys($this->table_scheam['columns']))) {
                $this->lastPrepareSql .= '`' . trim($field, '`') . '`, ';
            }
        }
        $this->lastPrepareSql = rtrim($this->lastPrepareSql, ', ') . ' FROM `' . $this->table . '` ';
        $whereString = $this->getWhere($conditions);
        if ($whereString) {
            $this->lastPrepareSql .= ' WHERE ' . $whereString;
        } else {
            // 查询全部
        }

        if (!empty($group_by)) {
            $this->lastPrepareSql .= ' GROUP BY `' . trim($field, '`') . '`';
        }
        if (!empty($having)) {
            $havingString = $this->getWhere($having, true);
            $this->lastPrepareSql .= ' HAVING ' . $havingString;
        }
        if (!empty($order_by)) {
            if (is_array($order_by)) {
                $str = ' ORDER BY ';
                foreach ($order_by as $field => $order) {
                    $str .= '`' . trim($field, '`') . '` ' . strtoupper($order) . ', ';
                }
                $orderByString = rtrim($str, ', ');
            } else {
                throw new \Exception('order_by格式出现错误', CoreErrorCode::SYSTEM_DB_SQL_FAIL);
            }
            $this->lastPrepareSql .= $orderByString;
        }
        if (!empty($limit)) {
            $this->lastPrepareSql .= ' LIMIT ' . (count($limit) > 1 ? (intval($limit[0]) . ', ' . intval($limit[1])) : intval($limit[0]));
        }
        return $this->query($this->lastPrepareSql, $this->lastPrepareData);
    }

    /**
     * sum|count|avg|min|max 查询
     * @param string $scamm      sum|count|avg|min|max
     * @param array  $conditions 条件
     * @param string $field      字段
     * @return mixed
     */
    private function scamm($scamm, $conditions, $field)
    {
        $field = '`' . trim($field, '`') . '`';
        $this->lastPrepareSql = 'SELECT ' . strtoupper($scamm) . '(' . $field . ') AS _' . $scamm . ' FROM `' . $this->table . '` ';
        $whereString = $this->getWhere($conditions);
        if ($whereString) {
            $this->lastPrepareSql .= ' WHERE ' . $whereString;
        } else {
            // 查询全部
        }
        $res = $this->query($this->lastPrepareSql, $this->lastPrepareData);
        return $res[0]['_' . $scamm];
    }

    /**
     * 查询数目
     * @param array  $conditions 条件
     * @param string $field      字段
     * @return mixed
     */
    public function count($conditions, $field = '*')
    {
        return $this->scamm('count', $conditions, $field);
    }

    /**
     * 查询字段和
     * @param array  $conditions 条件
     * @param string $field      字段
     * @return mixed
     */
    public function sum($conditions, $field)
    {
        return $this->scamm('sum', $conditions, $field);
    }

    /**
     * 求平均数
     * @param array  $conditions 条件数组
     * @param string $field      求平均数的字段
     * @return mixed
     */
    public function avg($conditions, $field)
    {
        return $this->scamm('avg', $conditions, $field);
    }

    /**
     * 查询字段最大值
     * @param array  $conditions 条件
     * @param string $field      字段
     * @return mixed
     */
    public function max($conditions, $field)
    {
        return $this->scamm('max', $conditions, $field);
    }

    /**
     * 查询字段最小值
     * @param array  $conditions 条件
     * @param string $field      字段
     * @return mixed
     */
    public function min($conditions, $field)
    {
        return $this->scamm('min', $conditions, $field);
    }

    /**
     * 处理条件
     * @param array $conditions
     *                      [
     *                      'field1'=>['>|<|!=|=',value,'or'],
     *                      'field2'=>['like','%like','and'],
     *                      'field3'=>['[not]in',[1,2,3,4,5],'and']
     *                      'field4'=>['between',[start,end],'or']
     *                      ]
     * @param bool  $having 是否为having查询
     * @return string
     * @throws \Exception
     */
    private function getWhere($conditions, $having = false)
    {
        if (!is_array($conditions)) {
            throw new \Exception($conditions . '条件不是数组格式', CoreErrorCode::SYSTEM_DB_SQL_FAIL);
        }
        if (!$having) {
            $this->lastPrepareData = [];
        }
        $whereString = '';
        foreach ($conditions as $k => $v) {
            if (in_array($k, array_keys($this->table_scheam['columns']))) {
                if ($having) {
                    $z_k = '_h_' . $k;
                } else {
                    $z_k = '_w_' . $k;
                }
                $w = strtoupper(trim($v[0]));
                $vv = $v[1];
                if (!isset($v[2])) {
                    // 默认and
                    $op = 'AND';
                } else {
                    $op = strtoupper($v[2]);
                }
                if (strpos($w, 'BETWEEN') !== false) {
                    // between
                    $whereString .= ' `' . $k . '` BETWEEN :' . $z_k . '_min AND :' . $z_k . '_max ' . $op;
                    if ($this->table_scheam['columns'][$k] === 'int') {
                        $vv[0] = intval($vv[0]);
                        $vv[1] = intval($vv[1]);
                    } elseif ($this->table_scheam['columns'][$k] === 'float') {
                        $vv[0] = floatval($vv[0]);
                        $vv[1] = floatval($vv[1]);
                    } else {
                        $vv[0] = strval($vv[0]);
                        $vv[1] = strval($vv[1]);
                    }
                    $this->lastPrepareData[':' . $z_k . '_min'] = $vv[0];
                    $this->lastPrepareData[':' . $z_k . '_max'] = $vv[1];
                } else if (strpos($w, 'IN') !== false) {
                    // in 不能用参数绑定，预处理
                    $ppp = '';
                    foreach ($vv as $v) {
                        if ($this->table_scheam['columns'][$k] === 'int') {
                            $ppp .= intval($v) . ',';
                        } else {
                            $ppp .= '\'' . strval($v) . '\',';
                        }
                    }
                    $whereString .= ' `' . $k . '` in (' . rtrim($ppp, ',') . ') ' . $op;
                } else {
                    // > < != = like %112233% intval => 0
                    $whereString .= ' `' . $k . '` ' . $w . ' :' . $z_k . ' ' . $op;
                    if ($this->table_scheam['columns'][$k] === 'int') {
                        $vv = $w === 'LIKE' ? strval($vv) : intval($vv);
                    } elseif ($this->table_scheam['columns'][$k] === 'float') {
                        $vv = floatval($vv);
                    } else {
                        $vv = strval($vv);
                    }
                    $this->lastPrepareData[':' . $z_k] = $vv;
                }
            }
        }
        return rtrim(rtrim($whereString, 'OR'), 'AND');
    }
}