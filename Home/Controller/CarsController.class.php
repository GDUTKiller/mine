<?php
namespace Home\Controller; 
use Think\Controller\RestController;

class CarsController extends RestController {
    /**
     * GET host/cars
     * 获取用户自己的矿车 POST
     * @param user_id 从cookie中获得
     * @return json
     */
    public function getInfo() {
	$Users = D('Users');
	if(!$Users->acc()) {
	    $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
	}

	$Cars = M('Cars');
 	$data = $Cars->field('car_id,gold_count,car_type,durability,car_status')->where(array('user_id'=>cookie('user_id')))->select();
	
	//foreach($data as $k => $v) {
	//    $data[$k]['introduction'] = C('CAR_INTRODUCTION_' . $v['car_type']);
	//}	

	$this->response(array('code'=>0, 'info'=>'获取矿车信息成功', 'data'=>$data), 'json');
  		
    } 


}
