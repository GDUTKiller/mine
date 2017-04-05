<?php
return array(
	//'配置项'=>'配置值' //
    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(

        // GET users/id 获取用户信息
        array('/^users\/([1-9]\d*)$/', 'Users/getInfo?user_id=:1', 'status=1', array('method'=>'get')),
        // PUT users/数字 更新用户信息
        array('/^users$/', 'Users/update', 'status=1', array('method'=>'put')),
        // POST users 新添加用户
        array('/^users$/', 'Users/register', 'status=1', array('method'=>'post')),
        // POST avatars 上传头像
        array('/^avatars$/', 'Users/upload', 'status=1', array('method'=>'post')),
        // POST session 登录
        array('/^sessions$/', 'Users/login', '', array('method'=>'post')),
        // DELETE session 注销
        array('/^sessions$/', 'Users/logout', '', array('method'=>'delete')),


        // POST captchas 发送手机验证码
        array('/^captchas$/', 'Captchas/send', 'status=1', array('method'=>'post')),



	//发表动态的图片 POST host/arts/pics
	array('/^arts\/pics$/', 'Arts/upload', '', array('method'=>'post') ),
	// POST host/arts  发表动态
	array('/^arts$/', 'Arts/add', '', array('method'=>'post') ),
	//DELETE host/arts/art_id 删除文章
	array('/^arts\/([1-9]\d*)$/', 'Arts/delete?art_id=:1', '', array('method'=>'delete')),
	//GET host/arts/time/page 获取动态
	array('/^arts\/(\d+)\/(\d+)$/', 'Arts/getArts?time=:1&begin=:2', '', array('method'=>'get')),


	//发表评论 POST host/comments
	array('/^comments$/', 'Comments/add', '', array('method'=>'post')),
	//删除评论 host/comments/comment_id
	array('/^comments\/([0-9]\d+)$/', 'Comments/delete?comment_id=:1', '', array('method'=>'delete')),
	//获取评论 host/comments/art_id/begin
	array('/^comments\/(\d+)\/(\d+)$/', 'Comments/getComments?art_id=:1&begin=:2', '', array('method'=>'get')),
    ),
);
