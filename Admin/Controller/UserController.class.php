<?php
namespace Admin\Controller;

use Think\Controller\RestController;

/** 
* 管理用户 
* 
* @author         yangjile<18826136974@163.com> 
* @since          1.0 
* @TODO 删除用户功能
*/  
class UserController extends RestController {
    /**
     * 初始化，确认管理员是否登录
     */
    public function _initialize() {
        $Admin = D('Admin');
        if(!$Admin->acc()) {
            $this->response(array('code'=>-11, 'info'=>'您尚未登录', 'data'=>null), 'json');
        }
    }

    /**
     * 查看用户
     * @access public
     * @param string [name] 模糊查询姓名
     * @param string [mobile] 模糊查询手机号
     * @param string [province] 查询哪个省
     * @param string [city] 查询哪个城市
     * @param int [status] 查询是否被封禁的用户，0正常，1封禁
     * @param int page 查询第几页的数据
     * @param int num  每页展示的数据
     * @return json data{count代表总记录数，items用户详情}
     */
    public function read() {
        //查询条件
	$map = array();
	if (isset($_GET['name'])) 
            $map['name'] = array('like', '%' . I('name') . '%');
        if (isset($_GET['mobile']))
	    $map['mobile'] = array('like', '%' . I('mobile') . '%');
        if (isset($_GET['province']))
	    $map['province'] = I('province'); 
        if (isset($_GET['city']))
	    $map['city'] = I('city'); 
        if (isset($_GET['status']))
	    $map['status'] = I('status'); 
	//分页
        $page = I('page');	
	$num = I('num');

        $Users = D('Home/Users');
	$items = $Users->where('1')->where($map)->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,province,city,count,status')->page($page, $num)->select();
	//总记录条数count
	$data['count'] = $Users->where('1')->where($map)->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,province,city,count,status')->count();
	$data['items'] = $items;

        $this->response(array('code'=>0, 'info'=>'', 'data'=>$data), 'json');
    }

    /**
     * 修改用户
     * @access public
     * @param int user_id 查询哪个城市
     * @param int status  修改用户状态为0正常，1封禁 
     * @return json 
     */
    public function update() {
        $user_id = I('user_id');
	$status = I('status');
	
	$Users = M('Users');
        $Users->where(array('user_id'=>$user_id))->save(array('status'=>$status, 'token_timeout'=>'20170101010101'));
        $this->response(array('code'=>0, 'info'=>'', 'data'=>null), 'json');

    }

}

