<?php
return array(
	//'配置项'=>'配置值' //
    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(
        // GET users/id 获取用户信息
        array('/^users\/([1-9]\d*)$/', 'Users/getInfo?id=:1', 'status=1', array('method'=>'get')),
        // PUT users/数字 更新用户信息
        array('/^users\/([1-9]\d*)$/', 'Users/update?id=:1', 'status=1', array('method'=>'put')),
        // POST users 新添加用户
        array('/^users$/', 'Users/register', 'status=1', array('method'=>'post')),
        // POST avatars 上传头像
        array('/^avatars$/', 'Users/upload', 'status=1', array('method'=>'post')),
        // POST session 登录
        array('/^session$/', 'Users/login', '', array('method'=>'post')),
        // DELETE session 注销
        array('/^session$/', 'Users/logout', '', array('method'=>'delete')),
        // POST captchas 发送手机验证码
        array('/^captchas$/', 'Captchas/send', 'status=1', array('method'=>'post')),
    ),
);