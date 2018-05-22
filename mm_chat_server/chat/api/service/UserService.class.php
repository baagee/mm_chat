<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/2/6
 * Time: 下午8:09
 */

namespace chat\api\service;

use chat\dao\UserDao;
use chat\helper;
use chat\api\define\ErrorCode;

class UserService
{
    private static function getUserDao()
    {
        return new UserDao();
    }

    public static function register($email, $password, $nickname)
    {
        return self::getUserDao()->addUser($email, helper::makePassword($password), $nickname);
    }

    public static function login($email, $password)
    {
        $user = self::getUserDao()->findUserByEmail($email);
        if (!empty($user)) {
            if ($user[0]['password'] !== helper::makePassword($password)) {
                throw new \Exception('用户名或者密码错误', ErrorCode::USER_NOT_EXISTS);
            }
            $_SESSION['user_info'] = $user[0];
            return [
                'nickname'=>$user[0]['nickname'],
                'user_id'=>$user[0]['user_id'],
                'avatar'=>$user[0]['avatar'],
            ];
        } else {
            throw new \Exception('用户名或者密码错误', ErrorCode::USER_NOT_EXISTS);
        }
    }
}