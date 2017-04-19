<?php
return array(
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'mine',          // 数据库名
    'DB_USER'               =>  'mine',      // 用户名
    'DB_PWD'                =>  'mine123',          // 密码
    'URL_MODEL'		    =>  1,

    'LOAD_EXT_CONFIG' => 'commission,room',
	
    //'配置项'=>'配置值' //
    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(

        // GET users/id 获取用户信息
        array('/^users\/([1-9]\d*)$/', 'Home/Users/getInfo?user_id=:1', 'status=1', array('method'=>'get')),
        // PUT users/数字 更新用户信息
        array('/^users$/', 'Home/Users/update', 'status=1', array('method'=>'put')),
        // POST users 新添加用户
        array('/^users$/', 'Home/Users/register', 'status=1', array('method'=>'post')),
        // POST avatars 上传头像
        array('/^avatars$/', 'Home/Users/upload', 'status=1', array('method'=>'post')),
        // POST session 登录
        array('/^sessions$/', 'Home/Users/login', '', array('method'=>'post')),
        // DELETE session 注销
        array('/^sessions$/', 'Home/Users/logout', '', array('method'=>'delete')),


        // POST captchas 发送手机验证码
        array('/^captchas$/', 'Home/Captchas/send', 'status=1', array('method'=>'post')),



        //发表动态的图片 POST host/arts/pics
        array('/^arts\/pics$/', 'Home/Arts/upload', '', array('method'=>'post') ),
	//PUT host/arts 点赞文章
	array('/^arts$/', 'Home/Arts/like', 'status=1', array('method'=>'put')),
        // POST host/arts  发表动态
        array('/^arts$/', 'Home/Arts/add', 'status=1', array('method'=>'post') ),
        //DELETE host/arts/art_id 删除文章
        array('/^arts\/([1-9]\d*)$/', 'Home/Arts/delete?art_id=:1', '', array('method'=>'delete')),
        //GET host/arts/art_id 获取动态
        array('/^arts\/(\d+)$/', 'Home/Arts/getArts?art_id=:1', '', array('method'=>'get')),
	

        //发表评论 POST host/comments
        array('/^comments$/', 'Home/Comments/add', '', array('method'=>'post')),
        //删除评论 host/comments/comment_id
        array('/^comments\/([0-9]\d+)$/', 'Home/Comments/delete?comment_id=:1', '', array('method'=>'delete')),
        //获取评论 host/comments/art_id/begin
        array('/^comments\/(\d+)\/(\d+)$/', 'Home/Comments/getComments?art_id=:1&begin=:2', '', array('method'=>'get')),


        //获取矿车 GET  host/cars
        array('/^cars$/', 'Home/Cars/getInfo', '', array('method'=>'get')),

	//查询房间状态 GET host/rooms/room_id
        array('/^rooms\/(\d{4,5})$/', 'Home/Rooms/getRoomInfo?room_id=:1', '', array('method'=>'get')),
	//加入房间 POST host/rooms
        array('/^rooms$/', 'Home/Rooms/joinRoom', '', array('method'=>'post')),
	//设置房间的聊天室 POST host/chatRooms
        array('/^chatRooms$/', 'Home/Rooms/setChatRoomId', '', array('method'=>'post')),
        //退出房间 PUT host/rooms
        array('/^rooms$/', 'Home/Rooms/quitRoom', '', array('method'=>'put')),
	//获取用户所加入的房间 GET host/rooms
        array('/^rooms$/', 'Home/Rooms/getRooms', '', array('method'=>'get')),
    	//刷新房间 GET host/rooms/room_id/car_id
        array('/^rooms\/([1-9]\d{0,3})\/([1-9]\d*)$/', 'Home/Rooms/getRoom?room_id=:1&car_id=:2', '', array('method'=>'get')),	
    ),
);
