<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/3/8
 * Time: 下午1:45
 */

namespace core;
abstract class Controller
{
    protected $validator = null;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    public function execute($action, $params)
    {
        return $this->$action($params);
    }
}