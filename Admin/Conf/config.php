<?php
return array(
    //'配置项'=>'配置值' //
    
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'mine',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'controll123',          // 密码

    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(
        //管理员 

        //增加管理员
        array('/^admins$/', 'Admin/register', 'status=1', array('method'=>'post')),
        //删除管理员
        array('/^admins$/', 'Admin/delete', 'status=1', array('method'=>'delete')),
        //改变管理员
        array('/^admins$/', 'Admin/update', 'status=1', array('method'=>'put')),
        //查看管理员
	array('/^admins$/', 'Admin/read', 'status=1', array('method'=>'get')),
	//管理员登录
	array('/^sessions$/', 'Admin/login', 'status=1', array('method'=>'post')),

	//查看用户
	array('/^users$/', 'User/read', '', array('method'=>'get')),
	//修改用户
	array('/^users$/', 'User/update', '', array('method'=>'put')),
        
    ),
);
