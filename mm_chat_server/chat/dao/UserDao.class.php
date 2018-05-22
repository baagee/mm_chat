<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/2/13
 * Time: 下午2:48
 */

namespace chat\dao;

use core\Dao;

class UserDao extends Dao
{
    public $table = 'user';

    public function addUser($email, $password, $nickname)
    {
        $data = [
            'user_id' => ceil(microtime(true)*1000),
            'email' => $email,
            'password' => $password,
            'nickname' => $nickname
        ];
        return $this->insert($data);
    }

    public function findUserByEmail($email)
    {
        $conditions = [
            'email' => ['=', $email]
        ];
        return $this->select($conditions);
    }

    protected function __autoInsert()
    {
        return [
            'create_time' => time(),
            'update_time' => time()
        ];
    }

    protected function __autoUpdate()
    {
        return [
            'update_time' => time()
        ];
    }
}