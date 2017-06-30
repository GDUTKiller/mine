<?php
namespace Home\Controller; 
use Think\Controller\RestController;

class RoomsController extends RestController {

    /**
     * 初始化，确认用户是否登录
     */
    public function _initialize() {
        $Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
        }
        
    }


    /**
     * GET host/rooms/{room_id}
     * 获取指定房间的信息
     * @param room_id 从$_GET 数组中获得
     * return json
     */
    public function getRoomInfo() {
	$room_id = I('room_id');	
	$Rooms = M('Rooms');

	if(!$Rooms->where(array('room_id'=>$room_id))->find()) {
            $this->response(array('code'=>-2, 'info'=>'该房间不存在', 'data'=>null), 'json');
	}

	$room_data = $Rooms->field('room_status,people_num,chat_room_id')->where(array('room_id'=>$room_id))->find();
	$this->response(array('code'=>0, 'info'=>'获取房间信息成功', 'data'=>$room_data), 'json');
    }

    /**
     * POST host/rooms
     * 派遣矿车去房间挖矿 
     * @param car_id 矿车id POST数组中获得
     * @param room_id POST数组中获得  要进入的room 没有则随机派遣
     * @return json
     */
    public function joinRoom() {
       
	//没有传入car_id参数
	$car_id = I('car_id'); 
	if(empty($car_id)) {
	    $this->response(array('code'=>-2, 'info'=>'请选择要派遣的矿车', 'data'=>null), 'json');
	}

	//把car_id转为整型
	$car_id += 0;
	$Cars = M('Cars');
	//该矿车不存在
	$car_data = $Cars->where(array('car_id'=>$car_id))->find();	
	if(!$car_data) {
	    $this->response(array('code'=>-3, 'info'=>'派遣的矿车不存在', 'data'=>null), 'json');
	}

	//派遣其他用户的矿车
	if($car_data['user_id'] != cookie('user_id')) {
	    $this->response(array('code'=>-4, 'info'=>'不能派遣其他用户的矿车', 'data'=>null), 'json');
	}

	//该矿车已在挖矿中
	if($car_data['car_status'] == 1) {
	    $this->response(array('code'=>-5, 'info'=>'该矿车已在挖矿中', 'data'=>null), 'json');
	}

	if($car_data['durability'] == 0) {
	    $this->response(array('code'=>-6, 'info'=>'该矿车耐久度为0', 'data'=>null), 'json');
	}
		
	$car_type = intval($car_data['car_type']);
	
	$Rooms = M('Rooms');
	$room_id = I('room_id');
	//如果没传入room_id,或者room_id为0  则为用户随机指定一个房间
 	if(empty($room_id)) {
	    $sql = "SELECT room_id FROM rooms WHERE room_type = '{$car_type}' AND room_status < 2 ORDER BY RAND() LIMIT 1";
	    $room_data = $Rooms->query($sql);
	    $room_id = $room_data[0]['room_id'];
	} else {
	    $room_id = intval($room_id) + $car_type * 10000;
	}	

	$room_data = $Rooms->where(array('room_id'=>$room_id))->find();
	
	if(!$room_data) {
	    $this->response(array('code'=>-7, 'info'=>"房间{$room_id}不存在", 'data'=>null), 'json');
	}
	//该房间已满人，在用户传入指定房间号时需要判断
	if($room_data['room_status'] == 2 || $room_data['people_num'] >= 15 ) {
	    $this->response(array('code'=>-8, 'info'=>"房间{$room_id}已满", 'data'=>null), 'json');
	}
	$Digs = M('Digs');

	//加入房间,开启事务>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$Rooms->startTrans();
	
	//房间人数加一
	$rs1 = $Rooms->where(array('room_id'=>$room_id))->setInc('people_num');	
	$people_num = $Rooms->where(array('room_id'=>$room_id))->getField('people_num');
	
	$flag = false; //判断是否超过十五人
	
	//房间人数为15人，设置房间状态为满人(room_status = 2)
	$rs2 = true;
	if($people_num == 15) {
 	    $rs2 = $Rooms->where(array('room_id'=>$room_id))->save(array('room_status'=>2));
	} elseif($people_num == 1) {
	    //设置房间状态为挖掘中
	    $Rooms->where(array('room_id'=>$room_id))->save(array('room_status'=>1));
	    //如果房间一开始没人，则刷新时间时间为现在的时间
	    $Rooms->where(array('room_id'=>$room_id))->save(array('update_time'=>date('YmdHis')));
	} elseif($people_num > 15) {
	    $flag = true;
	}

	//设置矿车状态为挖矿中
	$rs3 = $Cars->where(array('car_id'=>$car_id))->save(array('car_status'=>1));
	
	//新增矿车挖矿表
	$rs4 = $Digs->data(array('room_id'=>$room_id, 'car_id'=>$car_id, 'user_id'=>cookie('user_id')))->add();
	//设置挖矿的更新时间
	$Digs->where(array('dig_id'=>$rs4))->save(array('dig_update'=>date('YmdHis')));	
	if($flag || $rs1 === false || $rs2 === false || $rs3 === false || $rs4 === false ) {
	    $Rooms->rollback();
	    $this->response(array('code'=>-9, 'info'=>"加入房间{$room_id}失败", 'data'=>null), 'json');
	} else {
	    $Rooms->commit(); //提交事务<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
	    $chat_room_id = $Rooms->where(array('room_id'=>$room_id))->getField('chat_room_id');
	    $this->response(array('code'=>0, 'info'=>"加入房间{$room_id}成功", 'data'=>array('room_id'=>$room_id, 'chat_room_id'=>$chat_room_id)), 'json');
	}
    }    

    /**
     */
    public function setChatRoomId() {
	
	$room_id = I('room_id');
	$chat_room_id = I('chat_room_id');
	$Rooms = M('Rooms');

	if(!$Rooms->where(array('room_id'=>$room_id))->find()) {
            $this->response(array('code'=>-2, 'info'=>'该房间不存在', 'data'=>null), 'json');
	}

	$o_chat_room_id = $Rooms->where(array('room_id'=>$room_id))->getField('chat_room_id');
	if(!empty($o_chat_room_id) ) {
            $this->response(array('code'=>-3, 'info'=>'该房间已经有聊天室', 'data'=>array('chat_room_id'=>$o_chat_room_id)), 'json');
        }

	//开启事务 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$Rooms->startTrans(); 
	$room_data = $Rooms->query('SELECT chat_room_id FROM rooms WHERE room_id = %d for update', $room_id);
	$o_chat_room_id = $room_data[0]['chat_room_id'];
	if(!empty($o_chat_room_id) ) {
	    $Rooms->rollback();
	    $this->response(array('code'=>-3, 'info'=>'该房间已经有聊天室', 'data'=>array('chat_room_id'=>$o_chat_room_id)), 'json');
	} else {
	    $Rooms->where(array('room_id'=>$room_id))->save(array('chat_room_id'=>$chat_room_id));
	    $Rooms->commit(); //提交事务<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
	    $this->response(array('code'=>-0, 'info'=>'向房间添加聊天室成功', 'data'=>array('chat_room_id'=>$chat_room_id)), 'json');
	}
    }

    /**
     * PUT host/rooms
     * 退出房间
     * @param car_id 要操控的矿车id
     * @param room_id 要退出的房间
     * @return json
     */    
    public function quitRoom() {
	
	$car_id = intval(I('car_id'));
	$room_id = intval(I('room_id'));

	$Cars = M('Cars');
	if(!$Cars->where(array('car_id'=>$car_id))->find()) {
	    $this->response(array('code'=>-2, 'info'=>'该矿车不存在', 'data'=>null), 'json');
	}

	if($Cars->user_id != cookie('user_id')) {
	    $this->response(array('code'=>-3, 'info'=>'不能操控其他用户的矿车', 'data'=>null), 'json');
	}

	if($Cars->car_status == 0) {
	    $this->response(array('code'=>-4, 'info'=>'该矿车处于空闲状态', 'data'=>null), 'json');
	}

	//更改房间id为实际的房间id
	$car_type = $Cars->car_type;
	$room_id += $car_type * 10000;	

	$Rooms = M('Rooms');
	if(!$Rooms->where(array('room_id'=>$room_id))->find()) {
	    $this->response(array('code'=>-5, 'info'=>'该房间不存在', 'data'=>null), 'json');
	}
	
	$Digs = M('Digs');
	if(!$Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'room_status'=>1))->find() ) {
	    $this->response(array('code'=>-6, 'info'=>"该矿车已退出房间{$room_id}", 'data'=>null), 'json');
	}	
	
	$Bills = M('Bills');
	$trans_flag = true;
	//开启事务>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$Rooms->startTrans();
	
	$Rooms->where(array('room_id'=>$room_id))->setDec('people_num') === false ? $trans_flag = false : 1;
	//房间状态设置为挖掘(room_status=1)
	$Rooms->where(array('room_id'=>$room_id))->save(array('room_status'=>1)) === false ? $trans_flag = false : 1;

	$people_num = $Rooms->where(array('room_id'=>$room_id))->getField('people_num');
	if($people_num == 0) {
	    $Rooms->where(array('room_id'=>$room_id))->save(array('room_status'=>0)) === false ? $trans_flag = false : 1;
	}	
	//设置矿车状态为空闲(car_status = 0)
	$Cars->where(array('car_id'=>$car_id))->save(array('car_status'=>0 ) ) === false ? $trans_flag = false : 1;

	//设置挖矿状态为完成(dig_status=2)
	$Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'dig_status'=>1))->save(array('dig_status'=>2, 'dig_end'=>date('YmdHis'))) === false ? $trans_flag = false : 1;

	//获取此次挖矿信息，添加收入支出表
	$t_dig_data = $Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'dig_status'=>2))->order('dig_id desc')->find();
	if($t_dig_data['dig_count'] > 0) {
	    $Bills->add(array('user_id'=>$t_dig_data['user_id'], 'ref_id'=>$t_dig_data['dig_id'], 'type'=>2,  'golds'=>$t_dig_data['dig_count'])) === false ? $trans_flag = false : 1;	
	}

	if($trans_flag === false ) {
	    $Rooms->rollback(); 
 	    $this->response(array('code'=>-7, 'info'=>"退出房间成功", 'data'=>null), 'json');
	} else {
	    $Rooms->commit(); //提交事务<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 	    $this->response(array('code'=>0, 'info'=>"退出房间成功", 'data'=>null), 'json');
	}
	    
	
    }

    /**
     * GET host/rooms
     * 获取用户加入的房间
     */
    public function getRooms() {

	$Cars = M('Cars');	
	//获取用户的矿车id
	$car_data = $Cars->where(array('user_id'=>cookie('user_id')))->getField('car_id', true);

	$dig_data = array();
	//该矿区是否已解锁，默认未解锁
	$dig_data['status'] = array();
	$dig_data['status'][0] = 0;
	$dig_data['status'][1] = 0;
	$dig_data['status'][2] = 0;
	$dig_data['status'][3] = 0;

	//根据用户的矿车类型，判断用户是否解锁对应矿区
	$car_type = $Cars->where(array('user_id'=>cookie('user_id')))->getField('car_type', true);
	$car_type = array_unique($car_type);
	sort($car_type);

	//设置已解锁矿区
	foreach($car_type as $v) {
	    $dig_data['status'][$v] = 1;
	}

	for($i = 0; $i < 4; ++$i) {
	    $dig_data['zone' . $i] = array();
	}

	$Cars->where(array('user_id'=>cookie('user_id'), 'car_type'=>0))->find();
	if($Cars->durability == 0) {
	    $dig_data['status'][0] = 0;
	}

	$Rooms = M('Rooms');
	$Digs = M('Digs');
	foreach($car_data as $k=>$v) {
	    $tmp = $Digs->field('car_id,room_id')->where(array('car_id'=>$v, 'dig_status'=>1))->find();

	    //如果该矿车在挖矿中
	    if($tmp !== NULL && $tmp !== false) {
		//获取房间的聊天室id和人数
		$room_data  = $Rooms->field('chat_room_id,people_num,room_count,buff_begin,buff_end,buff')->where(array('room_id'=>$tmp['room_id']))->find();
		$tmp['chat_room_id'] = $room_data['chat_room_id'];
		$tmp['people_num'] = $room_data['people_num'];
		$tmp['room_count'] = $room_data['room_count'];	
		//默认buff为1
		$tmp['buff'] = 1;

		$time = time();
		if(strtotime($room_data['buff_begin']) < $time && $time < strtotime($room_data['buff_end']) ) {
		    $tmp['buff'] = $room_data['buff'];
		}


		$car_type = $Cars->where(array('car_id'=>$v))->getField('car_type');
		//房间原有金币
 		$tmp['origin_room_count'] = 	C('ROOM_COUNT_' . $car_type);

		if($tmp['room_id'] < 10000) {
		    array_push($dig_data['zone0'], $tmp);
		} elseif($tmp['room_id'] < 20000) {
		    array_push($dig_data['zone1'], $tmp);
		} elseif($tmp['room_id'] < 30000) {
		    array_push($dig_data['zone2'], $tmp);
		} else {
		    array_push($dig_data['zone3'], $tmp);
		}

	    }
	}

	
 	$this->response(array('code'=>0, 'info'=>"获取房间成功", 'data'=>$dig_data), 'json');
    }

    /**
     * 查看本次挖矿信息，并刷新
     * host/rooms/{room_id}/{car_id}
     * @access public 
     * @param room_id 长度必须四位
     * @car_id int 矿车id
     * @return json
     */
    public function getRoom() {

	$room_id = I('room_id');
	$car_id = I('car_id');

	$Cars = M('Cars');
	
	$car_type = $Cars->where(array('car_id'=>$car_id))->getField('car_type');
	
	$room_id += $car_type * 10000;

	$this->_refresh($room_id);

	$Rooms = M('Rooms');
	$Digs = M('Digs');
	$Users = M('Users');

	$room_data = $Rooms->field('room_count,people_num,buff,buff_begin,buff_end')->where(array('room_id'=>$room_id))->find();
	$dig_data = $Digs->field('dig_begin,dig_end,dig_count')->where(array('room_id'=>$room_id, 'car_id'=>$car_id, 'dig_status'=>1))->find();
	$user_data = array();
	//如果$dig_data === null 说明此次挖矿已经结束
	if($dig_data === null) {
	    $room_data['complete'] = true;
	    $dig_data = $Digs->field('dig_begin,dig_end,dig_count')->where(array('room_id'=>$room_id, 'car_id'=>$car_id, 'dig_status'=>2))->order(array('dig_end'=>'desc'))->find();
	} else {
	    $room_data['complete'] = false;
	    
	    $car_id_array = $Digs->where(array('room_id'=>$room_id,  'dig_status'=>1))->getField('car_id', true);
	    foreach($car_id_array as $k=>$v) {
		$user_id = $Cars->where(array('car_id'=>$v))->getField('user_id');
		
		$user_data[] = $Users->field('user_id,name,avatar')->where(array('user_id'=>$user_id))->find();		
	    }
	}

	$room_data['users'] = $user_data;		

 	$this->response(array('code'=>0, 'info'=>"获取挖矿信息成功", 'data'=>array_merge($room_data,$dig_data)), 'json');
    }

	
    /**
     * 刷新房间
     * @access private
     * @param $room_id
     * @return 
     *
     */
    private function _refresh($room_id) {
	$Rooms = M('Rooms');

	$room_data = $Rooms->where(array('room_id'=>$room_id))->find();

	//如果房间为空
	if($room_data['room_status'] == 0) {
	    return ;
	}

	//现在时间的时间戳
	$now_time = time(); 

	//当前时间减去上次刷新时间大于刷新间隔，则刷新
	if( $now_time - strtotime($room_data['update_time']) >= C('ROOM_INTERVAL') ) {
	    $Cars = M('Cars');
	    $Digs = M('Digs');
	    $Users = M('Users');
 	    $Bills = M('Bills');	    

	    //开启事务>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	    $Rooms->startTrans();	    
	    
	    //防止高并发情况下，重复刷新，或者用户对房间进行其他操作，这里对房间使用排他锁，并再次判断上次刷新时间
	    $data = $Rooms->query("SELECT * FROM rooms WHERE room_id = '%d' for update", $room_id);
	    $update_time = $data[0]['update_time'];

	    //房间buff信息
	    $buff_begin = $data[0]['buff_begin'];
	    $buff_end = $data[0]['buff_end'];
	    //$buff默认为1
	    $buff = 1;

	    //如果现在处于buff时间内，则设置buff
	    if(strtotime($buff_end) > $now_time && $now_time > strtotime($buff_end)) {
	        $buff = $data[0]['buff'];
	    }

	    if($now_time - strtotime($update_time) < C('ROOM_REFRESH_INTERVAL') ) {
	        $Rooms->rollback();
	    } else {
		//在此房间挖矿的矿车
		$dig_data = $Digs->where(array('room_id'=>$room_id, 'dig_status'=>1) )->select();				
		//房间金币是否为0，标志
		$room_flag = false;
		//事务过程中，是否有某一次查询失败
		$trans_flag = true;
		foreach($dig_data as $dig) {

		    $car_id = $dig['car_id'];
		    $dig_update = $dig['dig_update'];
		    $user_id = $Cars->where(array('car_id'=>$car_id))->getField('user_id');
		
		    $car_data = $Cars->where(array('car_id'=>$car_id))->find();
		    $durability = $car_data['durability'];
		    $car_type = $car_data['car_type'];

		    //该矿车挖矿次数，如刷新间隔为10分钟，上次刷新时间至今19分钟，则为一次
		    $times = intval(floor(( $now_time - strtotime($dig_update) ) / C('ROOM_REFRESH_INTERVAL') )  ); 
		    //如果挖矿次数为0，则跳过此次循环
		    if($times == 0) continue;		    

		    //耐久度决定金币获取比例
		    if($durability > 8000 ) {
			$rate = 1;
		    } elseif($durability > 6000) {
			$rate = 0.8;
		    } elseif($durability > 4000) {
			$rate = 0.6;
		    } elseif($durability > 2000) {
			$rate = 0.4;
		    } elseif($durability > 0 ) {
			$rate = 0.2;
		    } else {
			$rate = 0;
			$Rooms->where(array('room_id'=>$room_id))->find();
			//房间人数减1
			$Rooms->people_num--;
			//房间状态设置为挖掘(room_status=1)
			$Rooms->room_status = 1;
			if($Rooms->people_num == 0) {
			    $Rooms->room_status = 0;
			}	
			$Rooms->save() === false ? $trans_flag = false : 1;

			//设置矿车状态为空闲(car_status = 0)
			$Cars->where(array('car_id'=>$car_id))->save(array('car_status'=>0 ) ) === false ? $trans_flag = false : 1;

			//设置挖矿状态为完成(dig_status=2)
			$Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'dig_status'=>1))->save(array('dig_status'=>2, 'dig_end'=>date('YmdHis'))) === false ? $trans_flag = false : 1;

			//获取此次挖矿信息，添加收入支出表
			$t_dig_data = $Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'dig_status'=>2))->order('dig_id desc')->find();
			if($t_dig_data['dig_count'] > 0) {
			    $Bills->add(array('user_id'=>$t_dig_data['user_id'], 'ref_id'=>$t_dig_data['dig_id'], 'type'=>2,  'golds'=>$t_dig_data['dig_count'])) === false ? $trans_flag = false : 1;	
			}

			//跳出此次循环
			continue;
		    }

		    //此次刷新，该矿车应当挖到的挖矿数量，为挖矿速率 乘以挖矿次数$times 乘以金币获取比例$rate 乘以$buff
		    $dig_count = C('CAR_RATE_' . $car_type) * $times * $rate * $buff; 
		    //房间剩余金币
	 	    $room_count = $Rooms->where(array('room_id'=>$room_id))->getField('room_count');		
		    
		    //房间剩余金币不大于用户此次挖矿总数量
		    if($room_count - $dig_count <= 0) {
			//此时应挖得的矿应该为房间剩余的矿
			$dig_count = $room_count;

			//此时挖矿次数需要重新计算
		        $times = intval(ceil($dig_count / ($buff * $rate * C('CAR_RATE_' . $car_type) ) )); 

			//设置房间金币为0 标志为真
			$room_flag = true;
 		    }

		    //挖矿的更新时间
		    $dig_update = date('YmdHis', strtotime($dig_update) + $times * C('ROOM_REFRESH_INTERVAL'));

		    //增加此次挖矿金币数量
		    $Digs->where(array('room_id'=>$room_id, 'car_id'=>$car_id, 'dig_status'=>1))->setInc('dig_count', $dig_count) === false ? $trans_flag = false : 1;
		    //设置挖矿更新时间
		    $Digs->where(array('room_id'=>$room_id, 'car_id'=>$car_id, 'dig_status'=>1))->save(array('dig_update'=>$dig_update)) === false ? $trans_flag = false : 1;

		    //增加矿车挖矿金币
		    $Cars->where(array('car_id'=>$car_id))->setInc('gold_count', $dig_count) === false ? $trans_flag = false : 1;
		    //降低矿车耐久度
		    $dec_durability = $times * C('CAR_DURABILITY_' . $car_type); //此次更新应当减少的耐久度
		    if($dec_durability > $durability) {
			$dec_durability = $durability;
		    }
		    $Cars->where(array('car_id'=>$car_id))->setDec('durability', $dec_durability ) === false ? $trans_flag = false : 1;

		    //增加用户挖矿金币
		    $Users->where(array('user_id'=>$user_id))->setInc('count', $dig_count) === false ? $trans_flag = false : 1;

		    //降低房间金币数量
		    $Rooms->where(array('room_id'=>$room_id))->setDec('room_count', $dig_count) === false ? $trans_flag = false : 1;

		    //如果房间金币为0则跳出循环
		    if($room_flag) {
			break;	
		    }
		}

		//设置房间更新时间
		$Rooms->where(array('room_id'=>$room_id))->save(array('update_time'=>date('YmdHis'))) === false ? $trans_flag = false : 1;
		//房间金币数量为空，重置房间，设置矿车状态为空闲，设置挖矿状态为完成
		if($room_flag) {

		    //$dig_data 重新查询，因为可能某台矿车耐久度为0后，已经退出了房间，dig_status已置2
		    $dig_data = $Digs->where(array('room_id'=>$room_id, 'dig_status'=>1) )->select();				
		    foreach($dig_data as $dig) {
			$car_id = $dig['car_id'];
			//设置矿车状态
			$Cars->where(array('car_id'=>$car_id))->save(array('car_status'=>0)) === false ? $trans_flag = false : 1;
			//设置挖矿状态，结束时间
			$Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'dig_status'=>1))->save(array('dig_status'=>2, 'dig_end'=>date('YmdHis'))) === false ? $trans_flag = false : 1;

			//获取此次挖矿信息，添加收入支出表
			$t_dig_data = $Digs->where(array('car_id'=>$car_id, 'room_id'=>$room_id, 'dig_status'=>2))->order('dig_id desc')->find();
			if($t_dig_data['dig_count'] > 0) {
		  	    $Bills->add(array('user_id'=>$t_dig_data['user_id'], 'ref_id'=>$t_dig_data['dig_id'], 'type'=>2,  'golds'=>$t_dig_data['dig_count'])) === false ? $trans_flag = false : 1;	
			}
		    }

		    //设置房间金币数量，状态，人数
		    $Rooms->where(array('room_id'=>$room_id))->save(array('room_count'=>C('ROOM_COUNT_' . $car_type), 'people_num'=>0, 'room_status'=>0)) === false ? $trans_flag = false : 1;
		}

		if($trans_flag === false) {
		    $Rooms->rollback();	
		} else {
	            $Rooms->commit(); //提交事务<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
		}
		
		
	    }
	}
    }	

}
