<?php
namespace Admin\Model;

use Think\Model;

class ConfigsModel extends Model {
    protected $_validate = array(
        //验证配置信息
	array('type', '/^[0-3]$/', 'type参数不正确', 1, 'regex', '3'),
	array('car_gold_price', '/^[1-9]\d*$/', '价格必须为正整数', 2, 'regex', '3'),
	array('car_rmb_price', '/^[1-9]\d*$/', '价格必须为正整数', 2, 'regex', '3'),
	array('commission_1', '/^[1-9]\d*$/', '提成必须为正整数', 2, 'regex', '3'),
	array('commission_2', '/^[1-9]\d*$/', '提成必须为正整数', 2, 'regex', '3'),
	array('room_count', '/^[1-9]\d*$/', '房间金币总数必须为正整数', 2, 'regex', '3'),
	array('car_rate', '/^[1-9]\d*$/', '金币产生速率必须为正整数', 2, 'regex', '3'),
	array('times', '/^[1-9]\d*$/', '第一阶段的可工作次数必须为正整数', 2, 'regex', '3'),
        array('times', '1,200', '耐久度为81-100之间的工作次数，在1-400之间', 2, 'between', '3'),
 
    );
}
 
