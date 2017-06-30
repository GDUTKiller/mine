<?php
return array(
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'mine',          // 数据库名
    'DB_USER'               =>  'mine',      // 用户名
    'DB_PWD'                =>  'mine123',          // 密码
    'URL_MODEL'		    =>  1,

    //配置扩展
    'LOAD_EXT_CONFIG' => 'commission,room,car,recharge',
	
    //'配置项'=>'配置值' //
    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(

	//用户路由
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


        // POST ids 实名认证
        array('/^ids$/', 'Home/Users/identify', '', array('method'=>'post')),
	// GET /presents 转赠的手续，最小转赠金币数	
        array('/^presents$/', 'Home/Users/getPresents', '', array('method'=>'get')),
        // PUT host/golds 转赠金币
        array('/^golds$/', 'Home/Users/presentGolds', '', array('method'=>'put')),
        // GET host/notices/{$id} 获取通知
        array('/^notices\/(\d+)$/', 'Home/Users/getNotices?id=:1', '', array('method'=>'get')),
        // PUT host/notices/ 删除通知
        array('/^notices$/', 'Home/Users/delNotice', '', array('method'=>'put')),
        // GET host/bills/{bill_id} 获取收支
        array('/^bills\/(\d+)$/', 'Home/Users/getBills?bill_id=:1', '', array('method'=>'get')),
        // GET host/recharge 获取充值信息
        array('/^recharges/', 'Home/Users/getRecharges', '', array('method'=>'get')),
        // POST /recharge  充值
        array('/^recharge$/', 'Home/Users/recharge', '', array('method'=>'post')),
        // GET host/ranks/{type} 获取排行榜
        array('/^ranks\/(\d)$/', 'Home/Users/getRank?type=:1', '', array('method'=>'get')),
        // GET host/commissions/  获取提成
        array('/^commissions\/(\d+)\/(\d+)$/', 'Home/Commissions/getCommissions?user_id=:1&page=:2', '', array('method'=>'get')),
        // POST captchas 发送手机验证码
        array('/^captchas$/', 'Home/Captchas/send', 'status=1', array('method'=>'post')),

        // POST test 测试接口
        array('/^test$/', 'Home/Users/test', '', array('method'=>'post')),

        //发表动态的图片 POST host/arts/pics
        array('/^arts\/pics$/', 'Home/Arts/upload', '', array('method'=>'post') ),
	//PUT host/arts 点赞文章
	array('/^arts$/', 'Home/Arts/like', 'status=1', array('method'=>'put')),
        // POST host/arts  发表动态
        array('/^arts$/', 'Home/Arts/add', 'status=1', array('method'=>'post') ),
        //DELETE host/arts/art_id 删除文章
 

        // GET host/ranks/{type} 获取排行榜
        array('/^ranks\/(\d)$/', 'Home/Users/getRank?type=:1', '', array('method'=>'get')),

        // GET host/commissions/  获取提成
        array('/^commissions\/(\d+)\/(\d+)$/', 'Home/Commissions/getCommissions?user_id=:1&page=:2', '', array('method'=>'get')),

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
        //转赠矿车 PUT  host/cars
        array('/^cars$/', 'Home/Cars/presentCar', '', array('method'=>'put')),
        //获取矿车商品详情 GET  host/cargoods
        array('/^cargoods$/', 'Home/Cars/getCardetails', '', array('method'=>'get')),
        //购买矿车 POST  host/cars
        array('/^cars$/', 'Home/Cars/buyCar', '', array('method'=>'post')),

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
        array('/^rooms\/(\d{4})\/([1-9]\d*)$/', 'Home/Rooms/getRoom?room_id=:1&car_id=:2', '', array('method'=>'get')),	
    ),
);
