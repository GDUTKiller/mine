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
     * 获取用户自己的矿车 POST
     * @param user_id 从cookie中获得
     * @return json
     */
    public function getInfo() {

	$Cars = M('Cars');
 	$data = $Cars->field('car_id,gold_count,car_type,durability,car_status')->where(array('user_id'=>cookie('user_id')))->select();
	

	$this->response(array('code'=>0, 'info'=>'获取矿车信息成功', 'data'=>$data), 'json');
  		
    } 

    /**
     * 购买矿车 
     * POST host/cars
     * @access public
     * @param $car_type 矿车类型
     * @return json
     */
    public function buyCar() {
        $car_type = intval(I('car_type')) ;
        if($car_type != 1 && $car_type != 2 && $car_type != 3) {
            $this->response(array('code'=>-2, 'info'=>'请选择正确的矿车类型', 'data'=>null), 'json');
        }
        $user_id = cookie('user_id');
        
        $Cars = M('Cars');
        
        $Cars->add(array('user_id'=>$user_id, 'car_type'=>$car_type));

        $Users = M('Users');

        //购买矿车的用户id
        $origin_user_id = $user_id;

        for($i = 1; $i < 4; ++$i) {
            $user_id = $Users->where(array('user_id'=>$user_id))->getField('parent_user_id');  
            if(!$Users->where(array('user_id'=>$user_id))->find() )
        	break; //跳出循环
	    $commission = C('COMMISSION_' . $car_type . '_' . $i); 
            $Users->where(array('user_id'=>$user_id))->setInc('count', $commission );
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

	$from_user_id = cookie('user_id');	
	$car_id = I('car_id');
	$to_mobile = I('mobile');
	$captcha = I('captcha');

	$from_mobile = $Users->where(array('user_id'=>$from_user_id))->getField('mobile'); 

	$Cars = M('Cars');
	
	$car_data = $Cars->where(array('user_id'=>$from_user_id, 'car_id'=>$car_id))->find();
	if($car_data == NULL) {
	    $this->response(array('code'=>-2, 'info'=>'用户并不拥有该矿车', 'data'=>null), 'json');
	}

	if($car_data['car_status'] == 1) {
	    $this->response(array('code'=>-3, 'info'=>'该矿车正在挖矿中，不能转赠', 'data'=>null), 'json');
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
	$Cars->startTrans();
	//转赠矿车
	$Cars->where(array('car_id'=>$car_id))->save(array('user_id'=>$to_user_id)) === false ? $trans_flag = false : 1 ;
	//设置转赠者的矿车数量
	$Users->where(array('user_id'=>$from_user_id))->setDec('car_' . $car_type, 1) === false ? $trans_flag = false : 1;	
	//设置被转赠者的矿车数量
	$Users->where(array('user_id'=>$to_user_id))->setInc('car_' . $car_type, 1) === false ? $trans_flag = false : 1;	

	if($trans_flag === false) {
	    $Cars->rollback();
            $this->response(array('code'=>-8, 'info'=>'转增失败', 'data'=>null), 'json');
	} else {
	    $Cars->commit();
            $this->response(array('code'=>0, 'info'=>'转增成功', 'data'=>null), 'json');
	}

    }

}
