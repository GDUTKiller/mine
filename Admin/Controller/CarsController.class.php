<?php
namespace Admin\Controller;

use Think\Controller\RestController;

class CarsController extends RestController {
    /**
     * 初始化，确认管理员是否登录
     */
    public function _initialize() {
        $this->cors();
        $Admins = D('Admins');
        if(!$Admins->acc()) {
            $this->response(array('code'=>-11, 'info'=>'您尚未登录', 'data'=>null), 'json');
	}
    }

    /**
     * 修改矿车
     * @access public
     * @param int type 修改哪种类型的矿车 
     * @param  
     * @return json 
     */
    public function update() {

	$Configs = D('Configs');
	//这里必须用全等操作符，因为这里验证成功后返回一个对象或者数据，
	//因为put里面的参数的名称，和configs里的字段名称不一致，返回的是空数组，被认为是false
	if($Configs->create(I('put.')) === false) 
            $this->response(array('code'=>-13, 'info'=>$Configs->getError(), 'data'=>null), 'json');
	   
        $type = I('type');
	   
	$data = array();
	if(I('car_gold_price'))
	    $data['car_gold_price_'.$type] = I('car_gold_price');
        if(I('car_rmb_price'))
	    $data['car_rmb_price_'.$type] = I('car_rmb_price');
        if(I('commission_1'))
	    $data['commission_'.$type.'_1'] = I('commission_1');
        if(I('commission_2'))
	    $data['commission_'.$type.'_2'] = I('commission_2');
        if(I('room_count'))
	    $data['room_count_'.$type] = I('room_count');
        if(I('car_rate'))
	    $data['car_rate_'.$type] = I('car_rate');
        if(I('times')) 
	    $data['car_durability_'.$type] = round(2000 / I('times'));
	if(I('allow_but_car_after'))
	    $data['allow_but_car_'.$type.'_after'] = I('allow_but_car_after');
        var_dump($data);	

	
    }

    /**
     * 查看矿车
     * @access public
     * @param int type 查询哪种类型的矿车，0新手，1黄铜，2黄金，3钻石
     * @return json 
     */
    public function read() {
        $Configs = M('Configs');
	$type = I('type');
	if($type < 0 || $type > 3) {
            $this->response(array('code'=>-12, 'info'=>'输入的type参数有错', 'data'=>null), 'json');
	}
	$data = array();
	$data['car_name'] = $Configs->where(array('name'=>'car_name_'.$type))->getField('value');
	$data['car_introduction'] = $Configs->where(array('name'=>'car_introduction_'.$type))->getField('value');
	$data['car_gold_price'] = $Configs->where(array('name'=>'car_gold_price_'.$type))->getField('value');
	$data['car_rmb_price'] = $Configs->where(array('name'=>'car_rmb_price_'.$type))->getField('value');
	$data['commission_1'] = $Configs->where(array('name'=>'commission_'.$type.'_1'))->getField('value');
	$data['commission_2'] = $Configs->where(array('name'=>'commission_'.$type.'_2'))->getField('value');
	$data['room_count'] = $Configs->where(array('name'=>'room_count_'.$type))->getField('value');
	$data['car_rate'] = $Configs->where(array('name'=>'car_rate_'.$type))->getField('value');
        $data['allow_buy_car_after'] =  $Configs->where(array('name'=>'allow_buy_car_'.$type.'_after'))->getField('value');
	$car_durability  = $Configs->where(array('name'=>'car_durability_'.$type))->getField('value');
        //$car_initial_durability = $Configs->where(array('name'=>'car_initial_durability_'.$type))->getField('value');
	//10000为矿车初始耐久度
	$data['times'] = 2000 / $car_durability;
        $this->response(array('code'=>0, 'info'=>'', 'data'=>$data), 'json');
    }

    /**
     * 解决跨域资源共享 
     */
    private function cors() {
        //允许来源解决CORS
	$reauest_origin = $_SERVER['HTTP_ORIGIN'];
	header('Access-Control-Allow-Origin:'.$reauest_origin);
        header('Access-Control-Allow-Credentials:true');
            
        $request_method = $_SERVER['REQUEST_METHOD'];
        if ($request_method === 'OPTIONS') {
	    header('Access-Control-Allow-Methods:GET, POST, OPTIONS, PUT');
	    header('Access-Control-Max-Age:1728000');
	    header('Content-Type:text/plain charset=UTF-8');
	    header('Content-Length: 0',true);
            header('status: 204');
            header('HTTP/1.1 204 No Content');
	    //此处return因为options请求不需要返回数据
 	    return ; 							      
        }
    
    }

}


