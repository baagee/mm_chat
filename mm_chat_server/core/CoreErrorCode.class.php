<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/2/22
 * Time: 下午3:39
 */

namespace core;
class CoreErrorCode
{
    const SYSTEM_ERROR = 100000;
    const SYSTEM_FOLDER_CREATE_FAIL = 100001;
    const SYSTEM_VALIDATOR_NOT_EXISTS = 100002;
    const SYSTEM_FILE_NOT_EXISTS = 100003;
    const SYSTEM_DB_SQL_FAIL = 100004;
    const SYSTEM_THIS_NOT_FILE = 100005;
    const SYSTEM_FILE_CACHE_NOT_EXISTS = 100006;
    const SYSTEM_NOT_GD_EXTEND=100007;
    const SYSTEM_VALIDATE_FAILED=100008;
}