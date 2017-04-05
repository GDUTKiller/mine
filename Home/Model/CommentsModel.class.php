<?php
namespace Home\Model;
use Think\Model;
class CommentsModel extends Model {
    //自动验证
    protected $_validate = array(
        array('content', '1,50', '长度在1到50之间', 1, 'length', 1),
    );

    //自动完成
    protected $_auto = array(
        array('user_id', 'getUserId', 1, 'callback'),
    );	

    //获取用户id
    public function getUserId() {
        return cookie('user_id');
    }
}

