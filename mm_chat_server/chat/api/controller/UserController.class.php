<?php

/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/3/12
 * Time: 下午7:02
 */

namespace chat\api\controller;

use chat\api\service\UserService;
use core\Controller;

class UserController extends Controller
{
    public function login($input)
    {
        // 做一些数据验证合法性
        $rules = [
            'email'=>['email','邮箱格式不正确'],
            'password'=>['str|min[6]','密码长度不能少于6位']
        ];
        $input = $this->validator->validate($input, $rules);
        extract($input);
        return UserService::login($email,$password);
    }

    public function register($input)
    {
        $rules = [
//            'nickname' => ['str|max[7]', '昵称不能超过7位'],
            'email' => ['email', '邮箱不合法'],
            'password' => ['str|min[6]', '密码不能少于6位'],
            'rpassword' => ['str|equal[' . $input['password'] . ']', '两次密码不相等'],
        ];
        $data = $this->validator->validate($input, $rules);
        extract($data);
        return UserService::register($email, $password, $nickname);
    }
}