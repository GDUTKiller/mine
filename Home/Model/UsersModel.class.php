<?php
namespace Home\Model;
use Think\Model;
class UsersModel extends Model {
    //自动验证
    protected $_validate = array(
        //新增数据时验证,即注册用户
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', 'mobile format is wrong', 1, 'regex', 1),
        array('mobile', '', 'mobile already exists', 1, 'unique', 1),
        array('password', '/^[0-9a-zA-Z_]{8,20}$/', 'password format error', 1, 'regex', 1),
        array('name', '/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', 'name must be chinese', 1, 'regex', 1),

        //登录时候验证
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', 'mobile format is wrong', 1, 'regex', 4), // 4代表登录时验证
        array('mobile', 'isExist', 'mobile does not exist', 1, 'callback', 4), // 4代表登录时验证
        array('password', 'checkPass', 'password error', 1, 'callback', 4), // 4代表登录时验证
    );


    /**
     * 注册
     * @return [type] [description]
     */
    public function reg() {
        $this->encPass();
        return $this->add();
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
     * 登录
     */
    public function auth() {
        cookie('id', $this->id);
        cookie('mobile', $this->mobile);
        cookie('ccode', $this->encCookie($this->id, $this->mobile));
        return true;
    }

    /**
     * 退出登录
     */
    public function revoke() {
        cookie('id', null);
        cookie('name', null);
        cookie('ccode', null);
    }

    /**
     * 加密cookie
     */
    public function encCookie($id, $mobile) {
        return md5($id . '|' . $mobile . '|' . C('COO_KEY'));
    }

    /**
     * 是否已经登录
     * @return boolean
     */
    public function acc() {
        if(empty(cookie('id')) || empty(cookie('mobile')) || empty(cookie('ccode')) ) {
            return false;
        }

        if(md5(cookie('id') . '|' . cookie('mobile') . '|' . C('COO_KEY')) !== cookie('ccode') ) {
            return false;
        }

        return true;
    }
}