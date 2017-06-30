<?php
namespace Home\Model;

use Think\Model;

class UsersModel extends Model {
    //自动验证
    protected $_validate = array(
        //新增数据时验证,即注册用户
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', '手机格式错误', 1, 'regex', 1),
        array('mobile', '', '该手机号已经存在', 1, 'unique', 1),
        array('password', '/^[0-9a-zA-Z_]{6,16}$/', '密码格式6到16位，由数字字母下划线组成', 1, 'regex', 1),
        array('name', '/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', '名字由中文和英文字母组成，长度在2到10之间', 1, 'regex', 1),

	//推荐码自动验证
        array('recommend_code', '/^[a-z0-9]{8}$/', '推荐码错误', 1, 'regex', 1),
        array('recommend_code', 'checkRecommend', '推荐码不存在', 1, 'callback', 1),

        //登录时候验证
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', '手机格式错误', 1, 'regex', 4), // 4代表登录时验证
        array('mobile', 'isExist', '该账户不存在', 1, 'callback', 4), // 4代表登录时验证
        array('password', 'checkPass', '密码错误', 1, 'callback', 4), // 4代表登录时验证
    );

    //自动完成
    protected $_auto = array(
	//新增数据时，自动完成city字段
        array('city', 'getCity', 1, 'callback'),
    );


    /**
     * 获取手机号所在城市
     * @param post.mobile
     */
    public function getCity() {
	$Phones = M('Phones');
	//获取手机的前七位
	$phoneStr = substr(I('mobile'), 0, 7);

 	$city = $Phones->where(array('phone'=>$phoneStr))->getField('city');
	if(!$city) {
	    return '火星';
	}
	return $city;
    }


    /**
     * 检查推荐码
     * @param string $code 
     * @return mixed false | recommend id
     */
    public function checkRecommend($code) {
	$user_id = octdec((int)substr($code, 0, strpos($code, '9') ) );
	if(!$this->where(array('user_id'=>$user_id))->find() ) {
 	    return false;
	} else {
	    return $id;
	}
    }

    /**
     * 注册
     * @return [type] [description]
     */
    public function reg() {
        $this->encPass();
	// 设置推荐人user_id，八进制转回十进制
	$this->parent_user_id = octdec((int)substr($this->recommend_code, 0, strpos($this->recommend_code, '9')) );
        $user_id = $this->add();
	
	$recommend = substr( decoct($user_id) . '9' . str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8);
	$this->where(array('user_id'=>$user_id) )->save(array('recommend_code'=>$recommend));
	return $user_id;
    }

    /**
     * 对明文密码加盐md5
     * @return 加密后的md5密码
     */
    public function encPass() {
        $this->salt();
        return $this->password = md5($this->password . $this->salt);
    }

    /**
     * 创建用户的盐
     * @return 盐
     */
    public function salt() {
        if(!$this->salt) {
            $this->salt = $this->randStr();
        }
        return $this->salt;
    }

    /**
     * 返回一个随机字符串
     * @param  integer $length=8 [description]
     * @return string          [description]
     */
    protected function randStr($length = 8) {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($str), 0, $length);
    }

    /**
     * 手机号码是否存在于数据库中
     * @param  str  $mobile [description]
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

    /**
     * 登录，若用户已经被封禁，则登录失败 
     * 设置cookie,其中user_id为用户id,token为加密字符串
     * 用户表中，保存该用户的token和token_timeout，即过期时间
     */
    public function auth() {
        if($this->status == 1)
	    return false;

        cookie('user_id', $this->user_id);
	$user_id = $this->user_id;
	$this->token = md5($this->user_id . $this->mobile . $this->randStr());
	$this->token_timeout = date('YmdHis',strtotime('+14 day'));	
        cookie('token', $this->token);
	$this->field('token,token_timeout')->where(array('user_id'=>$user_id))->save();
        return true;
    }

    /**
     * 退出登录
     */
    public function revoke() {
        cookie('user_id', null);
        cookie('token', null);
    }


    /**
     * 是否已经登录
     * @return boolean
     */
    public function acc() {
        if(empty(cookie('user_id')) || empty(cookie('token')) ) {
            return false;
        }
	$this->where(array('user_id'=>cookie('user_id')))->find();
	if(cookie('token') != $this->token ||  strtotime(date('YmdHis')) > strtotime($this->token_timeout)) {
	    return false;
	}
        return true;
    }
}
