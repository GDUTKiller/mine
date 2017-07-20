<?php
namespace Admin\Model;

use Think\Model;

class AdminsModel extends Model {
    protected $_validate = array(
        //新增数据时验证,即注册用户
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', '手机格式错误', 1, 'regex', 1),
        array('mobile', '', '该手机号已经存在', 1, 'unique', 1),
        array('password', '/^[0-9a-zA-Z_]{6,16}$/', '密码格式6到16位，由数字字母下划线组成', 1, 'regex', 1),
        array('name', '/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', '名字由中文和英文字母组成，长度在2到10之间', 1, 'regex', 1),
	
        //登录时候验证
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', '手机格式错误', 1, 'regex', 4), // 4代表登录时验证
        array('mobile', 'isExist', '该账户不存在', 1, 'callback', 4), // 4代表登录时验证
        array('password', 'checkPass', '密码错误', 1, 'callback', 4), // 4代表登录时验证
 
    );
    
    /**
     * 注册
     * @return admin_id 
     */
    public function reg(){
        $this->encPass();
	return $this->add();
    }

    /**
     * 对明文密码加盐md5
     * @return 加密后的md5密码
     */
    private function encPass() {
        $this->salt(8);
        return $this->password = md5($this->password . $this->salt);
    }

    /**
     * 创建用户的盐
     * @return 盐
     */
    private function salt($len=8) {
        if(!$this->salt) {
            $this->salt = $this->randStr();
        }
        return $this->salt;
    }

    /**
     * 返回一个随机字符串
     * @param  int $length=8
     * @return string
     */
    protected function randStr($length = 8) {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($str), 0, $length);
    }

    /**
     * 登录
     * 设置cookie,其中admin_id为用户admin_id,token为加密字符串
     * 用户表中，保存该用户的token和token_timeout，即过期时间
     */
    public function auth() {
        cookie('admin_id', $this->admin_id);
	$admin_id = $this->admin_id;
	$this->token = md5($this->admin_id . $this->mobile . $this->randStr());
	$this->token_timeout = date('YmdHis',strtotime('+14 day'));	
        cookie('token', $this->token);
	$this->field('token,token_timeout')->where(array('admin_id'=>$admin_id))->save();
        return true;
    }
    
    /**
     * 是否已经登录
     * @return boolean
     */
    public function acc() {
        if(empty(cookie('admin_id')) || empty(cookie('token')) ) {
            return false;
        }
	$this->where(array('admin_id'=>cookie('admin_id')))->find();
	if(cookie('token') != $this->token ||  strtotime(date('YmdHis')) > strtotime($this->token_timeout)) {
	    return false;
	}
        return true;
    }

    /**
     * 手机号码是否存在于数据库中
     * @param  str  $mobile 
     * @return boolean
     */
    public function isExist($mobile) {
        if(!$this->where(array('mobile'=>$mobile))->find()) {
            return false;
        }
        return true;
    }

    /**
     * 判断密码是否正确
     * 执行此方法前，必须得先查找数据库中是否有mobile的记录，须先调用isExist
     * @param  str  password
     * @return bool
     */
    public function checkPass($password) {
        //原本的密码
        $selfpass = $this->password;
        //将密码改为传进来的密码
        $this->password = $password;
        //检测原来的密码和加密后的密码是否相同
        if($selfpass === $this->encPass()) {
            return true;
        } else {
            //将密码改回原来的密码
            $this->password = $selfpass;
            return false;
        }
    }
}

