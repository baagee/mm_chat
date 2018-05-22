<?php
/**
 * 脚本示例
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/3/3
 * Time: 下午4:51
 */

// 引入基本Script类
include_once '../../../core/Script.class.php';


// 继承基本Script类
class TestScript extends Script
{
    // 实现run方法
    public function run()
    {
        echo 'test script' . PHP_EOL;
        $cateogry = $this->test();
        // 查询数据库
        var_dump($cateogry->findOne(9));
        // 得到完整的查询sql
        var_dump($cateogry->fullSql);

        var_dump(\testapp\module\define\ErrorCode::CATEGORY_ID_NOT_EMPTY);
    }

    private function test()
    {
        return new \testapp\dao\CategoryDao();
    }
}

(new TestScript('testapp'))->run();