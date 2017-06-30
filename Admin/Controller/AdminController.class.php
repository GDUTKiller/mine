<?php
namespace Admin\Controller;

use Think\Controller\RestController;

class AdminController extends RestController {

    /**
     * 注册用户
     * POST请求 host/admins
     * @TODO 设置登录后才可以进行其他操作 
     * @param mobile 管理员手机号
     * @param password 密码
     * @param name 姓名
     * @return json
     */
    public function register() {
	$Admin = D('Admin');
        if(!$Admin->field('mobile,password,name')->create()) {
            $this->response(array('code'=>-2,'info'=>$Admin->getError(), 'data'=>null), 'json');
        }
        echo $Admin->reg();
    } 
    /**
     * 用户登录
     * POST请求 host/admin/sessions
     * @return json
     */
    public function login() {
        $Admin = D("Admin"); // 实例化User对象
        if (!$Admin->field('mobile,password')->create($_POST, 4)){ // 登录验证数据
            // 验证没有通过 输出错误提示信息
            $this->response(array('code'=>-3, 'info'=>$Admin->getError(), 'data'=>null), 'json');
        } else {
            //先查找该用户，使$Admin->data()为该用户的数据
            $Admin->find();
            $Admin->auth();

            //查找数据，返回
            $data = $Admin->field('id,mobile,name,province,city')->where(array('mobile'=>I('mobile') ) )->find();
            $this->response(array('code'=>0, 'info'=>'登录成功', 'data'=>$data), 'json');
        }
    }

    public function delete() {
        echo 'delete';
    }  

    public function update() {
        echo 'update';
    }

    public function read() {
        echo 'read';
    }
}
