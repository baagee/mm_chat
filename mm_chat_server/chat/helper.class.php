<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/1/23
 * Time: 下午4:07
 */

namespace chat;

class helper
{
    public static function makePassword($password)
    {
        return md5('sdfh345u89&*((R%672843'.$password.md5('35)(&893405t6789ausdi9yu'));
    }
}