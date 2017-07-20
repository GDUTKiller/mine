<?php
namespace Admin\Controller;

use Think\Controller\RestController;

class AdminsController extends RestController {

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
        $this->cors();
	$Admins = D('Admins');
        if(!$Admins->field('mobile,password,name')->create()) {
            $this->response(array('code'=>-2,'info'=>$Admins->getError(), 'data'=>null), 'json');
        }
        if($Admins->reg())
            $this->response(array('code'=>0, 'info'=>'增加管理员成功', 'data'=>$data), 'json');
	else 
	    $this->response(array('code'=>-1, 'info'=>'增加管理员失败', 'data'=>null), 'json');
	    
    } 


    /**
     * 用户登录
     * post请求 host/admin/sessions
     * @return json
     */
    public function login() {
        $this->cors(); 
        $Admins = D("Admins"); // 实例化User对象
        if (!$Admins->field('mobile,password')->create($_POST, 4)){ // 登录验证数据
            // 验证没有通过 输出错误提示信息
            $this->response(array('code'=>-3, 'info'=>$Admins->getError(), 'data'=>null), 'json');
        } else {
            //先查找该用户，使$Admins->data为该用户的数据
            $Admins->where(array('mobile'=>I('mobile')))->find();
            $Admins->auth();

            //查找数据，返回
            $data = $Admins->field('admin_id,mobile,name,province,city')->where(array('mobile'=>I('mobile') ) )->find();
            $this->response(array('code'=>0, 'info'=>'登录成功', 'data'=>$data), 'json');
        }
    }

    /**
     * 解决跨域资源共享 
     */
    private function cors() {
        //允许来源解决CORS
	$reauest_origin = $_SERVER['HTTP_ORIGIN'];
        header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Origin:'.$reauest_origin);

        $request_method = $_SERVER['REQUEST_METHOD'];
        if ($request_method === 'OPTIONS') {
	    header('Access-Control-Allow-Methods:GET, POST, OPTIONS, PUT, DELETE');
	    header('Access-Control-Max-Age:1728000');
	    header('Content-Type:text/plain charset=UTF-8');
	    header('Content-Length: 0',true);
            header('status: 204');
            header('HTTP/1.1 204 No Content');
            //此处return因为options请求不需要返回数据
 	    return ;
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
