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
	C('DB_CHARSET', 'utf8mb4');
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
		//创建文章点赞数表
		M('ArtLike')->data(array('art_id'=>$art_id))->add();
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
     * @return json
     */
    public function getArts() {
	C('DB_CHARSET', 'utf8mb4');
  	$Users = D('Users');
	if(!$Users->acc()) {
	    $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
	}
		
	$art_id = I('art_id');
	//获取用户的城市所在地
 	$city = $Users->where(array('user_id'=>cookie('user_id')))->getField('city');	

	$Arts = M('Arts');
	
	if($art_id == 0) {
	    //动态表和用户表join，查询
	    $data = $Arts->join('users ON arts.user_id = users.user_id')->field('name,avatar,arts.*')->order('art_id desc')->where(array('arts.city'=>$city))->limit(0, 10)->select();
	} else {
	    //动态表和用户表join，查询
	    $data = $Arts->join('users ON arts.user_id = users.user_id')->field('name,avatar,arts.*')->order('art_id desc')->where(array('arts.city'=>$city, 'arts.art_id'=>array('lt', $art_id)))->limit(0, 10)->select();
	}
	
	$Comments = M('Comments');
	foreach($data as $k=>$v) {
    	    //用户是否对这篇文章点赞	    
 	    $data[$k]['like'] = M('UserLikeArt')->where(array('user_id'=>cookie('user_id'), 'art_id'=>$v['art_id']))->getField('like') ? 1: 0;

	    //该文章的点赞总数
	    $data[$k]['like_count'] = M('ArtLike')->where(array('art_id'=>$v['art_id']))->getField('like_count');


	    //查询该动态的前三条评论
	    $sql = " select ifnull(puser.user_id, 0) as pid, ifnull(puser.name,'') as pname, ifnull(puser.avatar,'') as pavatar, cuser.user_id cid, cuser.name cname, cuser.avatar  cavatar, comments.comment_id, content, pubtime from
		 comments left join users as puser on puser.user_id=response_user_id left join users as cuser on cuser.user_id = comments.user_id  where comments.art_id = %d  order by pubtime ASC limit 0, 3";
	    $comm_data = $Comments->query($sql, $v['art_id']);
	    // $comm_data = $Comments->join('users ON comments.user_id = users.user_id')->field('name,avatar,comments.*')->order('pubtime ASC')->where(array('comments.art_id'=>$v['art_id']))->limit(0, 3)->select();
	    $data[$k]['comments'] = $comm_data;

	}
	$this->response(array('code'=>0, 'info'=>'获取动态成功', 'data'=>$data), 'json');

    }

    /**
     * 点赞 PUT host/arts
     * @param int art_id 要点赞的文章的art_id
     */
    public function like() {
	$Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
        }
	
	//用户动态点赞表
	$UserLikeArt = M('UserLikeArt');
	//文章点赞数表
	$ArtLike = M('ArtLike');

	//map 查询用户文章点赞表条件
	$map['user_id'] = cookie('user_id');
	$map['art_id'] = I('art_id');

	//如果用户没有点赞过这个动态
	if(!$UserLikeArt->where($map)->find() ) {
	    //创建用户动态点赞表
	    $UserLikeArt->data($map)->add();

	    $ArtLike->where(array('art_id'=>I('art_id')))->setInc('like_count');
	    $likeCount =  $ArtLike->where(array('art_id'=>I('art_id')))->getField('like_count');
	    $this->response(array('code'=>0, 'info'=>'点赞成功', 'data'=>array('like'=>1, 'like_count'=>$likeCount)), 'json');
	} else {
	    //用户点赞过这个动态,现在取消点赞
	    if($UserLikeArt->like) {
		$ArtLike->where(array('art_id'=>I('art_id')))->setDec('like_count');
		$UserLikeArt->where($map)->save(array('like'=>0));

	        $likeCount =  $ArtLike->where(array('art_id'=>I('art_id')))->getField('like_count');
	        $this->response(array('code'=>0, 'info'=>'取消点赞', 'data'=>array('like'=>0, 'like_count'=>$likeCount)), 'json');
	    } else {
		//用户点赞过这个表，但是取消点赞了，现在继续点赞
		$ArtLike->where(array('art_id'=>I('art_id')))->setInc('like_count');
		$UserLikeArt->where($map)->save(array('like'=>1));
		
	        $likeCount =  $ArtLike->where(array('art_id'=>I('art_id')))->getField('like_count');
	        $this->response(array('code'=>0, 'info'=>'点赞成功', 'data'=>array('like'=>1, 'like_count'=>$likeCount)), 'json');
	    }
	}
	
	

    }



}
