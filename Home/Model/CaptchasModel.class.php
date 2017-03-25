<?php
namespace Home\Model;
use Think\Model;
class CaptchasModel extends Model {
    //自动验证
    protected $_validate = array(
        array('mobile', '/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', 'mobile format is wrong', 1, 'regex', 3),
    );

    //自动完成
    protected $_auto = array(
        //array(完成字段1,完成规则,[完成条件,附加规则]),
        array('created_time', 'createDate', 3, 'callback'),
        array('expires_at', 'expireDate', 3, 'callback'),
        array('status', 0, 1),
        array('captcha', 'randStr', 1, 'callback'),
    );

    /**
     * 返回当前时间戳
     * @return date 当前时间戳
     */
    public function createDate() {
        return date('YmdHis');
    }

    /**
     * 返回当前时间加5分钟的时间戳
     * @return date
     */
    public function expireDate() {
        return date('YmdHis',strtotime('+5 minute'));
    }

    /**
     * 生成指定长度的随机数字字符串
     * @param  integer $length 生成的数字长度
     * @return string  字符串
     */
    public function randStr($length = 6) {
        $str = '01234567890123456789';
        return substr(str_shuffle($str), 0, $length);
    }
}