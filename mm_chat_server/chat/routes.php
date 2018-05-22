<?php
// 路由规则
return [
    // [路径，[请求方法],'对应的处理',[中间件（可选,注意中间件前后顺序，从前往后处理）]]
    ['/api/user/register', ['POST'], 'api/user/register'],
    ['/api/user/login', ['POST'], 'api/user/login'],
];