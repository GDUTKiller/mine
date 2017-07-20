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
        //增加管理员
        array('/^admins$/', 'Admins/register', 'status=1', array('method'=>'post')),
        //删除管理员
        array('/^admins$/', 'Admins/delete', 'status=1', array('method'=>'delete')),
        //改变管理员
        array('/^admins$/', 'Admins/update', 'status=1', array('method'=>'put')),
        //查看管理员
	array('/^admins$/', 'Admins/read', 'status=1', array('method'=>'get')),
	//管理员登录
	array('/^sessions$/', 'Admins/login', 'status=1', array('method'=>'post')),
        //管理员登录
	array('/^sessions$/', 'Admins/login', 'status=1', array('method'=>'options')),

	//查看用户
	array('/^users$/', 'Users/read', '', array('method'=>'get')),
	//修改用户
	array('/^users$/', 'Users/update', '', array('method'=>'put')),
        
	//查看矿车
	array('/^cars\/([0-3])$/', 'Cars/read?type=:1', '', array('method'=>'get')),
	//修改矿车
	array('/^cars$/', 'Cars/update', '', array('method'=>'put')),
    ),
);

