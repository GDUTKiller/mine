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
            $this->response(array('code'=>-1, 'info'=>'you are not login','data'=>null), 'json');
        } else {
            $data = $Users->field('name,mobile' )->find(I('id'));
            if(!$data) {
                $this->response(array('code'=>-2, 'info'=>'user does not exist','data'=>null), 'json');
            } else {
                $this->response(array('code'=>0, 'info'=>'','data'=>$data), 'json');
            }
        }
    }

    /**
     * 更新用户信息
     * @param  PUT请求 host/users
     * @return [type] [description]
     */
    public function update() {
        $Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'you are not login'), 'json');
        }

        //更改密码，不能同时更改密码和其他数据
        //密码为空则更改其他数据
        if(null !== I('put.password')) {
            if(!preg_match('/^[0-9a-zA-Z_]{8,20}$/', I('put.password'))) {
                //密码格式不对
                $this->response(array('code'=>-5, 'info'=>'password format error'), 'json');
            } else if(null == I('put.captcha') ) {
                //验证码为空
                $this->response(array('code'=>-6, 'info'=>'captcha empty'), 'json');
            } else {
                $Captchas = M('Captchas');
                $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>cookie('mobile')))->find();
                //验证码错误
                if(I('put.captcha') != $data['captcha']) {
                    $this->response(array('code'=>-7, 'info'=>'captcha error'), 'json');
                } else if(strtotime(date('YmdHis')) > strtotime($data['expires_at'])  || $data['status'] == '1') {

                    //验证码过期 或者已经用过
                    $this->response(array('code'=>-8, 'info'=>'captcha expire'), 'json');
                }

                //更改验证码status并且保存
                $Captchas->status = 1;
                $Captchas->field('status')->where(array('mobile'=>cookie('mobile')))->save();

                $Users->password = I('put.password');
                //更改密码，生成新的盐
                $Users->encPass();

                //保存更改
                if($Users->where(array('id'=>cookie('id')))->save()) {
                    $this->response(array('code'=>0), 'json');
                }
            }
        }

        //更改姓名
        if(null !== I('put.name') && preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', I('put.name')) ) {
            $Users->name = I('put.name');
        } else {
            $this->response(array('code'=>-2, 'info'=>'name format error'), 'json');
        }

        //更改性别
        if(null !== I('put.sex') && preg_match('/^(\bmale\b)|(\bfemale\b)$/', I('put.sex')) ) {
            $Users->sex = I('put.sex');
        } else {
            $this->response(array('code'=>-3, 'info'=>'sex format error'), 'json');
        }

        //更改生日
        if(null !== I('put.birthday') && preg_match('/^(19|20)\d{2}-(1[0-2]|0?[1-9])-(0?[1-9]|[1-2][0-9]|3[0-1])$/', I('put.birthday')) ) {
            $Users->birthday = I('put.birthday');
        } else {
            $this->response(array('code'=>-4, 'info'=>'birthday format error'), 'json');
        }

        //保存姓名性别生日的更改
        if($Users->where(array('id'=>cookie('id')))->save()) {
            $this->response(array('code'=>0), 'json');
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
            $this->response(array('code'=>-7, 'info'=>'captcha error'), 'json');
        } else if(strtotime(date('YmdHis')) > strtotime($data['expires_at'])  || $data['status'] == '1') {
            //验证码过期 或者已经用过
            $this->response(array('code'=>-8, 'info'=>'captcha expire'), 'json');
        }

        //更改验证码status并且保存
        $Captchas->status = 1;
        $Captchas->field('status')->where(array('mobile'=>I('mobile')))->save();


        if(!$Users->create()) {
            $this->response(array('code'=>-1,'info'=>$Users->getError(), 'data'=>null), 'json');
        }
        if($Users->reg()) {
            $this->response(array('code'=>0), 'json');
        } else {
            $this->response(array('code'=>-2, 'info'=>'add user error'), 'json');
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
            $this->response(array('code'=>-1, 'info'=>'you are not login','data'=>null), 'json');
        } else {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3 * 1048576 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录
            // 上传文件
            $info   =   $upload->upload();

            //上传失败
            if(!$info) {
                $this->response(array('code'=>-1, 'info'=>$upload->getError()), 'json');
            } else {
                //删除用户以前的头像
                $oldAvatar = $Users->field('avatar')->find(cookie('id'));
                if($oldAvatar['avatar'] && file_exists('.' . $oldAvatar['avatar'])) {
                    unlink('.' . $oldAvatar['avatar']);
                }

                //保存用户头像路径
                $avatarPath = '/Uploads/' . $info['avatar']['savepath'] . $info['avatar']['savename'];
                $Users->avatar = $avatarPath;
                $Users->where(array('id'=>cookie('id')))->save();
                $this->response(array('code'=>0), 'json');
            }
        }
    }

    /**
     * 用户登录
     * POST请求 host/session
     * @return json
     */
    public function login() {
        $Users = D("Users"); // 实例化User对象
        if (!$Users->create($_POST, 4)){ // 登录验证数据
            // 验证没有通过 输出错误提示信息
            $this->response(array('code'=>-1, 'info'=>$Users->getError(), 'data'=>null), 'json');
        }else{
            $Users->where(array('mobile'=>I('mobile') ) )->find();
            $Users->auth();
            $this->response(array('code'=>0), 'json');
        }
    }

    /**
     * 用户注销
     * DELETE请求 host/session
     * @return [type] [description]
     */
    public function logout() {
        D('Users')->revoke();
        $this->response(array('code'=>0), 'json');
    }
}