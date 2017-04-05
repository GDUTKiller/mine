<?php
namespace Home\Controller;
use Think\Controller\RestController;

class UsersController extends RestController {
    /**
     * 获取用户信息
     * GET host/users/id
     * @return $_GET['id'] 用户主键
     */
    public function getInfo() {
        $Users = D('Users');
        if(!$Users->acc()) {
            //用户尚未登录，返回错误
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录','data'=>null), 'json');
        } else {
            $data = $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->find(I('user_id'));
            if(!$data) {
                $this->response(array('code'=>-2, 'info'=>'用户不存在','data'=>null), 'json');
            } else {
		if(I('user_id') != cookie('user_id')) {
		    $data['mobile'] = '';
		    $data['count'] = '';
		}
                $this->response(array('code'=>0, 'info'=>'获取用户信息成功','data'=>$data), 'json');
            }
        }
    }

    /**
     * 更新用户信息
     * @param  PUT请求 host/users
     * @return [type] [description]
     */
    public function update() {
        //更改密码，不能同时更改密码和其他数据
        //密码为空则更改其他数据
        if(null != I('put.password')) {

	    //手机号
	    $mobile = I('put.mobile');
	    if($mobile == null) {
		$this->response(array('code'=>-5, 'info'=>'手机号码不能为空', 'data'=>null), 'json');
	    }
	    if(!preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', $mobile) ) {
	   	$this->response(array('code'=>-6, 'info'=>'手机号码格式错误', 'data'=>null), 'json');
	    }

	   
            if(!preg_match('/^[0-9a-zA-Z_]{6,16}$/', I('put.password'))) {
                //密码格式不对
                $this->response(array('code'=>-7, 'info'=>'密码格式错误', 'data'=>null), 'json');
            } else if(null == I('put.captcha') ) {
                //验证码为空
                $this->response(array('code'=>-8, 'info'=>'验证码不能为空', 'data'=>null), 'json');
            } else {
                $Captchas = M('Captchas');
                $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>$mobile))->find();
                //验证码错误
                if(I('put.captcha') != $data['captcha']) {
                    $this->response(array('code'=>-9, 'info'=>'验证码错误', 'data'=>null), 'json');
                } else if(strtotime(date('YmdHis')) > strtotime($data['expires_at'])  || $data['status'] == '1') {

                    //验证码过期 或者已经用过
                    $this->response(array('code'=>-10, 'info'=>'验证码过期', 'data'=>null), 'json');
                }

                //更改验证码status并且保存
                $Captchas->status = 1;
		$Captchas->field('status')->where(array('mobile'=>$mobile))->save();

		$Users = D('Users');
                $Users->password = I('put.password');
                //更改密码，生成新的盐
                $Users->encPass();

                //保存更改
                $Users->field('password,salt')->where(array('mobile'=>$mobile))->save();
 		$data = $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('mobile'=>$mobile))->find();
                $this->response(array('code'=>0, 'info'=>'更改密码成功', 'data'=>$data), 'json');
               
            }
        }


	//更改姓名性别生日时需要登录状态下操作
        $Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
        }

        //更改姓名
        if(!empty(I('put.name')) ) {
            if( !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', I('put.name')) ) {
                $this->response(array('code'=>-2, 'info'=>'名字格式错误', 'data'=>null), 'json');
  	    }
	    $Users->name = I('put.name');
        }

        //更改性别
        if(!empty(I('put.sex')) ) {
	    if(!preg_match('/(男|女)$/', I('put.sex'))) {
                $this->response(array('code'=>-3, 'info'=>'性别格式错误', 'data'=>null), 'json');
	    }
            $Users->sex = I('put.sex');
        }

        //更改生日
        if(!empty(I('put.birthday'))  ) {
	    if(!preg_match('/^(19|20)\d{2}-(1[0-2]|0?[1-9])-(0?[1-9]|[1-2][0-9]|3[0-1])$/', I('put.birthday'))) {
 	    
                $this->response(array('code'=>-4, 'info'=>'生日格式错误', 'data'=>null), 'json');
 	    }
            $Users->birthday = I('put.birthday');
        }

        //保存姓名性别生日的更改
        if( $Users->field('name,sex,birthday')->where(array('user_id'=>cookie('user_id')))->save()) {
            $this->response(array('code'=>0, 'info'=>'更改用户信息成功', 'data'=>$Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('user_id'=>cookie('user_id')))->find()), 'json');
 	} else {
            $this->response(array('code'=>-11, 'info'=>'更改失败'), 'json');
 	}
    }

    /**
     * 注册用户
     * POST请求 host/users
     * @return json
     */
    public function register() {
        $Users = D('Users');

        $Captchas = M('Captchas');
        $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>I('mobile')))->find();
        //验证码错误
        if(I('captcha') != $data['captcha']) {
            $this->response(array('code'=>-3, 'info'=>'验证码错误', 'data'=>null), 'json');
        } else if(strtotime(date('YmdHis')) > strtotime($data['expires_at'])  || $data['status'] == '1') {
            //验证码过期 或者已经用过
            $this->response(array('code'=>-4, 'info'=>'验证码过期', 'data'=>null), 'json');
        }

        //更改验证码status并且保存
        $Captchas->status = 1;
        $Captchas->field('status')->where(array('mobile'=>I('mobile')))->save();


        if(!$Users->field('mobile,password,name,recommend_code')->create()) {
            $this->response(array('code'=>-1,'info'=>$Users->getError(), 'data'=>null), 'json');
        }
	$user_id = $Users->reg();
        if($user_id ) {
            $this->response(array('code'=>0, 'info'=>'注册成功', 'data'=> $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('user_id'=>$user_id))->find()), 'json');
        } else {
            $this->response(array('code'=>-2, 'info'=>'注册失败', 'data'=>null), 'json');
        }
    }

    /**
     * 上传用户头像
     * POST host/avatars
     * @return [type] [description]
     */
    public function upload() {
        $Users = D('Users');
        if(!$Users->acc()) {
            //用户尚未登录，返回错误
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录','data'=>null), 'json');
        } else {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     1 * 1048576 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     './Uploads/Avatars/'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录
            // 上传文件
	    //这里用一个变量保存上传的图片名，因为苹果客户端那边的问题，他采用二进制上传文件，无法指定上传的文件名
	    $fileName = array_keys($_FILES)[0];

            $info   =   $upload->upload();

            //上传失败
            if(!$info) {
                $this->response(array('code'=>-2, 'info'=>$upload->getError(), 'data'=>null), 'json');
            } else {
                //删除用户以前的头像
                $oldAvatar = $Users->field('avatar')->find(cookie('user_id'));
                if($oldAvatar['avatar'] && file_exists('.' . $oldAvatar['avatar'])) {
                    unlink('.' . $oldAvatar['avatar']);
                }

                //保存用户头像路径
                $avatarPath = '/Uploads/Avatars/' . $info[$fileName]['savepath'] . $info[$fileName]['savename'];
                $Users->avatar = $avatarPath;
                $Users->where(array('user_id'=>cookie('user_id')))->save();
                $this->response(array('code'=>0,'info'=>'上传头像成功', 'data'=>array('path'=>$avatarPath)), 'json');
            }
        }
    }

    /**
     * 用户登录
     * POST请求 host/sessions
     * @return json
     */
    public function login() {
        $Users = D("Users"); // 实例化User对象
        if (!$Users->field('mobile,password')->create($_POST, 4)){ // 登录验证数据
            // 验证没有通过 输出错误提示信息
            $this->response(array('code'=>-1, 'info'=>$Users->getError(), 'data'=>null), 'json');
        }else{
	    //先查找该用户，使$Users->data()为该用户的数据
            $Users->where(array('mobile'=>I('mobile') ) )->find();
            $Users->auth();

	    //查找数据，返回
            $data = $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('mobile'=>I('mobile') ) )->find();
            $this->response(array('code'=>0, 'info'=>'登录成功', 'data'=>$data), 'json');
        }
    }

    /**
     * 用户注销
     * DELETE请求 host/session
     * @return [type] [description]
     */
    public function logout() {
        D('Users')->revoke();
        $this->response(array('code'=>0, 'info'=>'注销成功', 'data'=>null), 'json');
    }
    
    public function test1() {
	echo C('YJL');
    }

    public function test2() {
	$config = array('YJL'=>'yjl');
	C($config);
	echo C('YJL');
    }
    
}
