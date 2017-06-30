<?php
namespace Home\Controller; 
use Think\Controller\RestController;

class CarsController extends RestController {
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
     * GET host/cars
     * 获取用户自己的矿车
     * @param user_id 从cookie中获得
     * @return json
     */
    public function getInfo() {

	$Cars = M('Cars');
 	$data = $Cars->field('car_id,gold_count,car_type,durability,car_status')->order('car_type')->where(array('user_id'=>cookie('user_id')))->select();
	if($data[0]['durability'] == 0) {
	    array_shift($data);
	}
	$this->response(array('code'=>0, 'info'=>'获取矿车信息成功', 'data'=>$data), 'json');
  		
    } 


    /**
     * GET host/cargoods
     * 获取矿车商品详情
     * @access public
     * @return json
     */
    public function getCardetails() {
	$details = array();
	for($i = 1; $i < 4; $i++) {
	    $data['name'] = C('CAR_NAME_' . $i);     
	    $data['introduction'] = C('CAR_INTRODUCTION_' . $i);     
	    $data['rmb_price'] = C('CAR_RMB_PRICE_' . $i);     
	    $data['gold_price'] = C('CAR_GOLD_PRICE_' . $i);     
	    $data['allow'] = 0;
 	    $details[] = $data;
	}

	
	$this->response(array('code'=>0, 'info'=>'获取矿车商品信息成功', 'data'=>$details), 'json');

    }


    /**
     * 购买矿车 
     * POST host/cars
     * @access public
     * @param $car_type [1-3] 矿车类型
     * @param $captcha string 验证码
     * @param $gold_buy [0-1] 是否金币购买 1金币购买 0人民币购买
     * @return json
     */
    public function buyCar() {
	
	$rsa = new \Home\Tool\RsaTool();
	$privDecrypt = $rsa->privDecrypt(I('data'));
	if($privDecrypt === NULL)
	    $this->response(array('code'=>-10, 'info'=>'传入的加密字符串有误', 'data'=>null), 'json');
	$json_array = json_decode($privDecrypt, true);	    
	$car_type = intval($json_array['car_type']);
	$gold_buy = intval($json_array['gold_buy']);
	$captcha = $json_array['captcha'];
	

        //$car_type = intval(I('car_type')) ;
	//$gold_buy = intval(I('gold_buy'));	
	//$captcha = I('captcha');


        if($car_type != 1 && $car_type != 2 && $car_type != 3) {
            $this->response(array('code'=>-2, 'info'=>'请选择正确的矿车类型', 'data'=>null), 'json');
        }

        $user_id = cookie('user_id');
	
        $Cars = M('Cars');
        $Users = M('Users');

	$Users->where(array('user_id'=>$user_id))->find();
	$mobile = $Users->mobile;

	
        //验证码验证
	$Captchas = M('Captchas');
        $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>$mobile))->find();
        //验证码错误
        if($captcha != $data['captcha']) {
            $this->response(array('code'=>-6, 'info'=>'验证码错误', 'data'=>null), 'json');
        } else if(time() > strtotime($data['expires_at'])  || $data['status'] == '1') {
            //验证码过期 或者已经用过
            $this->response(array('code'=>-7, 'info'=>'验证码过期', 'data'=>null), 'json');
        }
        //更改验证码status并且保存
        $Captchas->status = 1;
        $Captchas->field('status')->where(array('mobile'=>$mobile))->save();


	$trans_flag = true;

	//金币购买
	if($gold_buy == 1) {
	    $golds = C('CAR_GOLD_PRICE_' . $car_type);
	    if($golds > $Users->count) {
            	$this->response(array('code'=>-4, 'info'=>'您当前的金币不足以购买此矿车', 'data'=>null), 'json');
	    }
	    //开启事务>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	    $Users->startTrans();

	    //减少用户金币数量
	    $Users->where(array('user_id'=>$user_id))->setDec('count', $golds ) === false ? $trans_flag = false : 1;
	    //增加用户表中的用户对应矿车数量
	    $Users->where(array('user_id'=>$user_id))->setInc('car_'.$car_type) === false ? $trans_flag = false : 1;
	    //添加一台矿车
	    $car_id = $Cars->add(array('user_id'=>$user_id, 'car_type'=>$car_type));

	    if($car_id === false ) 
		$trans_flag = false;

	    if($trans_flag === false ) {
	    $Users->rollback();
	    	$this->response(array('code'=>-3, 'info'=>'购买矿车失败', 'data'=>null), 'json');
	    } else {
	    	$Users->commit();
		//提交事务<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
	    }
	} else {
	    $this->response(array('code'=>-10, 'info'=>'现在还不能用人民币购买', 'data'=>null), 'json');
	}
	
        //购买矿车的用户id
        $car_user_id = $user_id;

	$Bills = M('Bills');
	$Commissions = M('Commissions');

	//支出记录
	$Bills->add(array('user_id'=>$user_id, 'ref_id'=>$car_id, 'type'=>4, 'in'=>0, 'golds'=>$golds)); 

	//给上级用户增加提成，两个上级用户
        for($i = 1; $i < 3; ++$i) {
            $user_id = $Users->where(array('user_id'=>$user_id))->getField('parent_user_id');  
            if(!$Users->where(array('user_id'=>$user_id))->find() )
        	break; //跳出循环

	    //提成
	    $commission = C('COMMISSION_' . $car_type . '_' . $i); 

            $Users->where(array('user_id'=>$user_id))->setInc('count', $commission );

	    //如果数据库没有该提成记录，添加新记录，否则增加该记录的count字段
	    $commission_id = $Commissions->where(array('user_id'=>$user_id, 'car_user_id'=>$car_user_id))->getField('commission_id');
	    if(!$commission_id ) {
		$commission_id = $Commissions->add(array('user_id'=>$user_id, 'car_user_id'=>$car_user_id, 'count'=>$commission));
	    } else {
		$Commissions->where(array('commission_id'=>$commission_id))->setInc('count', $commission);
	    }
	    
	    //收入记录
	    $Bills->add(array('user_id'=>$user_id, 'ref_id'=>$commission_id, 'type'=>1, 'golds'=>$commission)); 
	    
        }

        $this->response(array('code'=>0, 'info'=>'购买矿车成功', 'data'=>null), 'json');


    }   


    /**
     * PUT host/cars
     * 转赠矿车
     * @param car_id 要转赠的矿车id
     * @param mobile 转赠矿车给该用户
     */
    public function presentCar() {
	$Users = M('Users');

	$rsa = new \Home\Tool\RsaTool();
	$privDecrypt = $rsa->privDecrypt(I('data'));
	if($privDecrypt === NULL)
	    $this->response(array('code'=>-10, 'info'=>'传入的加密字符串有误', 'data'=>null), 'json');
	$json_array = json_decode($privDecrypt, true);	    
	$car_id = intval($json_array['car_id']);
	$to_mobile = $json_array['mobile'];
	$captcha = $json_array['captcha'];
	
	$from_user_id = cookie('user_id');	
	//$car_id = I('car_id');
	//$to_mobile = I('mobile');
	//$captcha = I('captcha');

	$from_mobile = $Users->where(array('user_id'=>$from_user_id))->getField('mobile'); 

	$Cars = M('Cars');
	
	$car_data = $Cars->where(array('user_id'=>$from_user_id, 'car_id'=>$car_id))->find();
	if($car_data == NULL) {
	    $this->response(array('code'=>-2, 'info'=>'您并不拥有该矿车', 'data'=>null), 'json');
	}

	if($car_data['car_status'] != 0) {
	    $this->response(array('code'=>-3, 'info'=>'该矿车正在使用中，不能转赠', 'data'=>null), 'json');
	}

	$car_type = $car_data['car_type'];
	if($car_type == 0) {
	    $this->response(array('code'=>-4, 'info'=>'不能转赠新手矿车', 'data'=>null), 'json');
	}

	//要转赠的用户
	$to_user_data = $Users->where(array('mobile'=>$to_mobile))->find();
	if($to_user_data == NULL) {
	    $this->response(array('code'=>-5, 'info'=>'要转赠的用户不存在', 'data'=>null), 'json');
	}
	$to_user_id = $to_user_data['user_id'];

	
	if($from_user_id == $to_user_id) {
	    $this->response(array('code'=>-9, 'info'=>'不能自己转赠矿车给自己', 'data'=>null), 'json');
	}

	//验证码验证
	$Captchas = M('Captchas');
        $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>$from_mobile))->find();

        //验证码错误
        if($captcha != $data['captcha']) {
            $this->response(array('code'=>-6, 'info'=>'验证码错误', 'data'=>null), 'json');
        } else if(time() > strtotime($data['expires_at'])  || $data['status'] == '1') {
            //验证码过期 或者已经用过
            $this->response(array('code'=>-7, 'info'=>'验证码过期', 'data'=>null), 'json');
        }

        //更改验证码status并且保存
        $Captchas->status = 1;
        $Captchas->field('status')->where(array('mobile'=>$from_mobile))->save();


	
	$trans_flag = true;
	//开启事务 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$Cars->startTrans();
	//转赠矿车
	$Cars->where(array('car_id'=>$car_id))->save(array('user_id'=>$to_user_id)) === false ? $trans_flag = false : 1 ;
	//设置转赠者的矿车数量
	$Users->where(array('user_id'=>$from_user_id))->setDec('car_' . $car_type, 1) === false ? $trans_flag = false : 1;	
	//设置被转赠者的矿车数量
	$Users->where(array('user_id'=>$to_user_id))->setInc('car_' . $car_type, 1) === false ? $trans_flag = false : 1;	

	//在转增表添加一行记录
	$Presents = M('Presents');
	$Presents->add(array('from_user_id'=>$from_user_id, 'to_user_id'=>$to_user_id, 'type'=>$car_type)) === false ? $trans_flag = false : 1 ;

	if($trans_flag === false) {
	    $Cars->rollback();
            $this->response(array('code'=>-8, 'info'=>'转增失败', 'data'=>null), 'json');
	} else {
	    //提交事务 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
	    $Cars->commit();
            $this->response(array('code'=>0, 'info'=>'转增成功', 'data'=>null), 'json');
	}

    }

}
