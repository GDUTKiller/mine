<?php
namespace Admin\Controller;

use Think\Controller\RestController;

class CarController extends RestController {
    /**
     * 初始化，确认管理员是否登录
     */
    public function _initialize() {
        $Admin = D('Admin');
        if(!$Admin->acc()) {
            $this->response(array('code'=>-11, 'info'=>'您尚未登录', 'data'=>null), 'json');
	}
    }

    /**
     * 修改矿车
     * @access public
     * @param int user_id 查询哪个城市
     * @param int status  修改用户状态为0正常，1封禁 
     * @return json 
     */
    public function update() {
        $user_id = I('user_id');
	$status = I('status');
	
	$Users = M('Users');
        $Users->where(array('user_id'=>$user_id))->save(array('status'=>$status, 'token_timeout'=>'20170101010101'));
        $this->response(array('code'=>0, 'info'=>'', 'data'=>null), 'json');

    }


}


