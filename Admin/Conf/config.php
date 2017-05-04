<?php
return array(
	//'配置项'=>'配置值' //
    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(
        // GET users/id 获取用户信息
        array('/^users\/([1-9]\d*)$/', 'Users/getInfo?user_id=:1', 'status=1', array('method'=>'get')),
    ),
);
