## xxsphp 简单的PHP框架

结构清晰，高度自定义，轻量的php框架

已在php version >=7.0 and linux and nginx开发测试通过

PHP需pdo扩展

#### 项目目录结构介绍
```
.
├── core // 框架核心目录
│   ├── Application.class.php // 框架主文件
│   ├── ASCommon.class.php // 程序启动的一些公共方法
│   ├── AutoLoader.class.php // 框架实现的类的自动加载
│   ├── Controller.class.php // 父类控制器
│   ├── CoreErrorCode.class.php // 系统错误码
│   ├── Dao.class.php // 基础 dao层，封装了访问数据库的常用方法
│   ├── database // 和数据库相关的基类
│   │   ├── MySQLDB.class.php // 操作mysql基本类
│   │   └── PDOLink.class.php // 获取mysql数据库连接对象基本类
│   ├── FileCache.class.php // 文件缓存数据类
│   ├── Log.class.php //日志类
│   ├── Middleware.class.php //处理中间件类
│   ├── Script.class.php //脚本父类
│   └── Validator.class.php // 数据验证类
├── scheam //创建scheam的文件 【使用:php scheam app_name】
├── extension // 扩展类目录
│   └── qcloud // 腾讯云上传sdk 示例
│       ├── cos
│       │   ├── Api.php
│       │   ├── Auth.php
│       │   ├── Helper.php
│       │   ├── HttpClient.php
│       │   ├── HttpRequest.php
│       │   ├── HttpResponse.php
│       │   ├── LibcurlWrapper.php
│       │   └── SliceUploading.php
│       └── sample.php
├── nginx.conf
├── public //公共目录
│   └── index.php // 单一入口文件
├── README.md
└── testapp //项目目录
   ├── config.php //配置文件
   ├── dao // dao层，操作访问数据库
   │   └── CategoryDao.class.php
   ├── middleware // 中间件
   │   ├── AfterMiddleware.class.php //前置中间件
   │   └── BeforeMiddleware.class.php //后置中间件
   ├── helper.class.php //公共函数示例
   ├── Middleware.class.php //中间件
   ├── routes.php //定义路由规则
   ├── module // 模块
   │   ├── conf // 模块的配置
   │   │   └── config.php
   │   ├── controller // 控制器
   │   │   ├── CategoryController.class.php
   │   │   └── CategoryTestController.class.php
   │   ├── define // 常量和错误码定义
   │   │   └── ErrorCode.class.php
   │   ├── script //模块需要的脚本目录
   │   │   └── test.php
   │   └── service // service逻辑层
   │       └── CategoryService.class.php
   ├── runtime // 运行时目录
   │   ├── ClassMap.php
   │   ├── data //数据缓存目录
   │   │   └── module_res
   │   └── module //
   │       └── log // 日志目录
   │           └── 2018_02-13
   │               ├── 2018_02_13_13_DEBUG.log
   │               ├── 2018_02_13_13_ERROR.log
   │               ├── 2018_02_13_13_INFO.log
   │               └── 2018_02_13_22_WARNING.log
   └── scheams //创建的scheam目录
       └── xxs_test // 数据库名
           ├── table_student.php //表对应的文件
           └── xxs_category.php
```
为了减少代码耦合度，增加代码复用，主要分为4层：
* module 模块
* controller-action 主要用来做数据验证，和调用service层具体执行业务逻辑
* service 具体的action对应的业务逻辑
* dao 和数据库打交道的层，查询数据库的

#### 规范
- 控制器的action里面主要做数据验证，然后实例化对应service类，调用相关方法
- service层可以调用其他service层，service可以调用dao层
- dao层只能由service层调用，dao层不能调用dao层
- 不允许跨层调用，比如controller-action层不能直接调用dao层
- 自动加载机制通过命名空间实现的，所以命名空间以项目目录（本文件所在目录）为根目录，namespace就是其文件相对根目录所在目录
- 模块错误码必须大于200000

#### 开发步骤：

1. 建数据库，建表
2. 新建app目录，并修改public/index.php的appName名字为文件夹名，一定要对应
3. cli模式运行 php scheam app_name，创建项目所需的scheam文件，注意提前配置好app里的config.php配置文件，每次数据表结构变化都要重新生成scheam文件
4. 开发遇到不明白的直接看源码就行