<?php
namespace Home\Model;
use Think\Model;
class ArtsModel extends Model {
    //自动验证
    protected $_validate = array(
        array('content', '1,200', '长度在1到200之间', 1, 'length', 1),
    );

    //自动完成
    protected $_auto = array(
        array('city', 'getCity', 1, 'callback'),
	array('user_id', 'getUserId', 1, 'callback'),
    );    


    public function getCity() {
    	$Users = M('Users');	
	$city = $Users->where(array('user_id'=>cookie('user_id')))->getField('city');
	if($city) {
	    return $city;
	}
 	return '其他';
    }

    public function getUserId() {
	return cookie('user_id');
    }
}
