<?php
/**
 * @authors BaAGee (asm19950109@hotmail.com)
 * @date    2017-11-12 10:43:10
 */

namespace core\database;

use core\Log;

class MySQLDB extends PDOLink
{
    public $link;
    public $PDOStatement;
    public $transactionCount;
    public $fullSql = '';

    public function __construct()
    {
        //单例模式获取数据库连接对象
        $this->link = parent::getObj();//获取数据库连接对象
    }

    /**
     * 查询sql
     * @param string $sql  要查询的sql
     * @param array  $data 查询条件
     * @return array
     * @throws \Exception
     */
    public function query($sql, $data = [])
    {
        $this->PDOStatement = $this->link->prepare($sql);
        if($this->PDOStatement===false){
            $errorInfo = $this->link->errorInfo();
            throw new \Exception($errorInfo[2], $errorInfo[1]);
        }
        $this->PDOStatement->execute($data);
        $this->getFullSql($sql, $data);
        Log::info('query sql:' . $this->fullSql);
        $this->sqlBugInfo();
        return $this->PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 执行原生 insert, update create delete等语句
     * @param string $sql  sql语句
     * @param array  $data 查询条件
     * @return int 影响函数
     * @throws \Exception
     */
    public function execute($sql, $data = [])
    {
        $this->PDOStatement = $this->link->prepare($sql);
        if($this->PDOStatement===false){
            $errorInfo = $this->link->errorInfo();
            throw new \Exception($errorInfo[2], $errorInfo[1]);
        }
        $this->PDOStatement->execute($data);
        $this->getFullSql($sql, $data);
        Log::info('exec sql:' . $this->fullSql);
        $this->sqlBugInfo();
        return $this->PDOStatement->rowCount();
    }

    /**
     * 获取最后一次插入的ID
     * @return int
     */
    public function lastInsertId()
    {
        return intval($this->link->lastInsertId());
    }

    /**
     *开启事务
     */
    public function beginTransaction()
    {
        if (!$this->transactionCount++) {
            return $this->link->beginTransaction();
        }
        $this->link->exec('SAVEPOINT trans' . $this->transactionCount);
        return $this->transactionCount >= 0;
    }

    /**
     *提交事务
     */
    public function commit()
    {
        if (!--$this->transactionCount) {
            return $this->link->commit();
        }
        return $this->transactionCount >= 0;
    }

    /**
     *事务回滚
     */
    public function rollback()
    {
        if (--$this->transactionCount) {
            $this->link->exec('ROLLBACK TO trans' . ($this->transactionCount + 1));
            return true;
        }
        return $this->link->rollback();
    }

    /**
     * 获取完整的sql语句 将预处理语句中的占位符替换成对应值
     * @param string $sql  预处理sql语句
     * @param array  $data 预处理数据
     */
    private function getFullSql($sql, $data)
    {
        foreach ($data as $field => $value) {
            $type = gettype($value);
            switch ($type) {
                case 'integer':
                case 'double':
                    $sql = str_replace($field, $value, $sql);
                    break;
                default:
                    $sql = str_replace($field, '\'' . $value . '\'', $sql);
            }
        }
        $this->fullSql = $sql;
    }

    private function sqlBugInfo()
    {
        if ($this->PDOStatement === false) {
            $errorInfo = $this->link->errorInfo();
        } else {
            $errorInfo = $this->PDOStatement->errorInfo();
        }
        if ($errorInfo[0] != '00000') {
            throw new \Exception($errorInfo[2], $errorInfo[1]);
        }
    }
}