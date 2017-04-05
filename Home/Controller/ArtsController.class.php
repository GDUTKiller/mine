<?php
namespace Home\Controller;
use Think\Controller\RestController;

class ArtsController extends RestController {
    /**
     * 上传动态的图片 POS请求
     * @param file pic 上传的图片
     * @return json 包含图片的路径
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
            $upload->rootPath  =     './Uploads/Pics/'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录
            // 上传文件
            //这里用一个变量保存上传的图片名，因为苹果客户端那边的问题，他采用二进制上传文件，无法指定上传的文件名
            $fileName = array_keys($_FILES)[0];
            $info   =   $upload->upload();

            //上传失败
            if(!$info) {
                $this->response(array('code'=>-2, 'info'=>$upload->getError(), 'data'=>null), 'json');
            } else {

                //图片的路径
                $picPath = '/Uploads/Pics/' . $info[$fileName]['savepath'] . $info[$fileName]['savename'];
                $this->response(array('code'=>0,'info'=>'上传图片成功', 'data'=>array('path'=>$picPath)), 'json');
            }
        }
    }



    /**
     * 发表动态 POST请求
     * @param string pic 图片相对服务器的路径
     * @param string content 动态内容
     * @return json
     */
    public function add() {
	$Users = D('Users');
	if(!$Users->acc()) {
	    $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
	}
        $Arts = D('Arts');	
	if(!$Arts->field('content,pic')->create()) {
	    $this->response(array('code'=>-2, 'info'=>$Arts->getError(), 'data'=>null) , 'json');
	} else {
	    $art_id = $Arts->add();
	    $data = $Arts->find($art_id);
	    if($art_id) {
	        $this->response(array('code'=>0, 'info'=>'发表动态成功', 'data'=>$data) , 'json');
	    } else {
	        $this->response(array('code'=>-3, 'info'=>'发表动态失败', 'data'=>null) , 'json');
	    }
	}
    }    


    /**
     * 删除用户自己的动态 DELETE请求
     * @param int art_id 要删除的文章的art_id
     * @return json
     */
    public function delete() {
  	$Users = D('Users');
	if(!$Users->acc()) {
	    $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
	}
	
	$Arts = M('Arts');
	//查看该文章是否存在
	if(!$Arts->find(I('art_id'))) {
	    $this->response(array('code'=>-3, 'info'=>'要删除的动态不存在', 'data'=>null), 'json');
	}
	$user_id = $Arts->where(array('art_id'=>I('art_id')))->getField('user_id');	

	//用户id和动态的user_id相同，才删除
	if(cookie('user_id') == $user_id) {
	    //删除文章中的图片
      	    $pic = $Arts->where(array('art_id'=>I('art_id')))->getField('pic');
            if($pic && file_exists('.' . $pic)) {
                unlink('.' . $pic);
            }

	    $Arts->delete(I('art_id'));
	    $this->response(array('code'=>0, 'info'=>'删除动态成功', 'data'=>null), 'json');
	} else {
	    $this->response(array('code'=>-2, 'info'=>'不能删除其他用户的动态', 'data'=>null), 'json');
 	}
    }


    /**
     * 获取用户营地的动态 GET 请求
     * @param time time之前发的动态
     * @param begin 从begin开始，获得十条动态
     * @return json
     */
    public function getArts() {
  	$Users = D('Users');
	if(!$Users->acc()) {
	    $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
	}
		
	$time = I('time');
	$begin = I('begin');
	//获取用户的城市所在地
 	$city = $Users->where(array('user_id'=>cookie('user_id')))->getField('city');	

	$Arts = M('Arts');
	$map['arts.city'] = $city;
	$map['pubtime'] = array('elt', $time);
	
	//动态表和用户表join，查询
	$data = $Arts->join('users ON arts.user_id = users.user_id')->field('name,avatar,arts.*')->order('pubtime desc')->where(array('arts.city'=>$city, 'arts.pubtime'=>array('elt', $time)))->limit($begin, 10)->select();
	
	$Comments = M('Comments');
	foreach($data as $k=>$v) {
	    //查询该动态的前三条数据
	    $comm_data = $Comments->join('users ON comments.user_id = users.user_id')->field('name,avatar,comments.*')->order('pubtime ASC')->where(array('comments.art_id'=>$v['art_id']))->limit(0, 3)->select();
	    $data[$k]['comments'] = $comm_data;
	}
	$this->response(array('code'=>0, 'info'=>'获取动态成功', 'data'=>$data), 'json');

    }
}
