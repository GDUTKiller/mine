<?php
namespace Home\Controller;
use Think\Controller\RestController;

class CommissionsController extends RestController {
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
     * 获取用户的提成	
     * @access public
     * @param $user_id 哪个用户的下级的提供的提成
     * @param page 第几页的数据
     * @return json
     */	
    public function getCommissions() {

	$user_id = intval(I('user_id'));
	$page = intval(I('page'));

	$Users = M('Users');
	$Commissions = M('Commissions');

	//user_id用户的下级用户
	$user_data = $Users->field('user_id,name,avatar')->where(array('parent_user_id'=>$user_id))->limit($page, 15)->select();

	//查看cookie('user_id')用户的下级用户提供的提成
	foreach($user_data as $k => $user) {
	    $count = $Commissions->where(array('user_id'=>cookie('user_id'), 'car_user_id'=>$user['user_id']))->getField('count');
	    if($count) {
		$user_data[$k]['count'] = $count; 
	    } else {
		$user_data[$k]['count'] = 0; 
	    }
	}
	$this->response(array('code'=>0, 'info'=>'', 'data'=>$user_data), 'json');
    }


}
