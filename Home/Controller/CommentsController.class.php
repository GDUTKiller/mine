<?php
namespace Home\Controller; 
use Think\Controller\RestController;

class CommentsController extends RestController {

    /**
     * 增加评论 POST
     * @param art_id 回复的动态的id
     * @param content 评论内容
     * @param user_id 从cookie中获得
     * @param response_user_id 不为空表示，是回复某用户的评论
     * @return json
     */
    public function add() {
	C('DB_CHARSET', 'utf8mb4');
	$Users = D('Users');
	if(!$Users->acc()) {
	    $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
	}


	$Arts = M('Arts');
	//要评论的动态不存在
	if(!$Arts->find(I('art_id'))) {
	    $this->response(array('code'=>-4, 'info'=>'要评论的动态不存在', 'data'=>null), 'json');
	}

	$Comments = D('Comments');
	if(!$Comments->field('response_user_id,art_id,content')->create()) {
	    $this->response(array('code'=>-2, 'info'=>$Comments->getError(), 'data'=>null), 'json');
	}
	$comment_id = $Comments->add();
	if(!$comment_id) {
	    $this->response(array('code'=>-3, 'info'=>'评论失败', 'data'=>null), 'json');
	}
	
	//动态表的评论数加一
	$Arts->comm = $Arts->comm +1;
	$Arts->field('comm')->where(array('art_id'=>I('art_id')))->save();


        //动态表和用户表join，查询
        $art_data = $Arts->join('users ON arts.user_id = users.user_id')->field('name,avatar,arts.*')->where(array('art_id'=>I('art_id')))->find();

	//用户是否对这篇文章点赞        
        $art_data['like'] = M('UserLikeArt')->where(array('user_id'=>cookie('user_id'), 'art_id'=>I('art_id')))->getField('like') ? 1: 0;

        //该文章的点赞总数
        $art_data['like_count'] = M('ArtLike')->where(array('art_id'=>I('art_id')))->getField('like_count');

        //查询该动态的前三条评论
	$sql = "select ifnull(puser.user_id, 0) as pid, ifnull(puser.name,'') as pname, ifnull(puser.avatar,'') as pavatar, cuser.user_id cid, cuser.name cname, cuser.avatar  cavatar, comments.comment_id, content, pubtime
		from comments left join users as puser on puser.user_id=response_user_id left join users as cuser on cuser.user_id = comments.user_id
	        where comments.art_id = %d  order by pubtime ASC limit 0, 3";
	$comm_data = $Comments->query($sql, I('art_id'));
      
	//$comm_data = $Comments->join('users ON comments.user_id = users.user_id')->field('name,avatar,comments.*')->order('pubtime ASC')->where(array('comments.art_id'=>I('art_id')))->limit(0, 3)->select();
        $art_data['comments'] = $comm_data;

	//$data = $Comments->find($comment_id);
	$this->response(array('code'=>0, 'info'=>'发表评论成功', 'data'=>$art_data), 'json');
    }

    /**
     * 删除评论 DELETE
     * @param comment_id 
     * @return json
     */
    public function delete() {
        $Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
        }

        $Comments = M('Comments');
        //查看该评论是否存在
        if(!$Comments->find(I('comment_id'))) {
            $this->response(array('code'=>-3, 'info'=>'要删除的评论不存在', 'data'=>null), 'json');
        }
        $user_id = $Comments->where(array('comment_id'=>I('comment_id')))->getField('user_id');
        //用户id和评论的user_id相同，才删除
        if(cookie('user_id') == $user_id) {
	
	    //获取评论中的动态id
            $art_id = $Comments->where(array('comment_id'=>I('comment_id')))->getField('art_id');

            $Comments->delete(I('comment_id'));

	    //动态表的评论数减一
	    $Arts = M('Arts');
	    $Arts->find($art_id);
	    $Arts->comm--;
	    $Arts->where(array('art_id'=>$art_id))->save();


	    $Arts = M('Arts');
            $this->response(array('code'=>0, 'info'=>'删除成功', 'data'=>null), 'json');
        } else {
            $this->response(array('code'=>-2, 'info'=>'不能删除其他用户的评论', 'data'=>null), 'json');
        }
    }

    /**
     * 获取某篇文章下的评论
     * @param art_id 文章的id
     * @param begin 从第几条评论开始获取,返回15条评论
     * @return json 
     */
    public function getComments() {
	C('DB_CHARSET', 'utf8mb4');
        $Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
        }

        $begin = I('begin');
	$art_id = I('art_id');


	$Arts = M('Arts');
	//该动态不存在
        if(!$Arts->find($art_id)) {
            $this->response(array('code'=>-2, 'info'=>'该动态不存在', 'data'=>null), 'json');
	}

	$sql = " select ifnull(puser.user_id, 0) as pid, ifnull(puser.name,'') as pname, ifnull(puser.avatar,'') as pavatar, cuser.user_id cid, cuser.name cname, cuser.avatar  cavatar, comments.comment_id, content, pubtime from comments left join users as puser on puser.user_id=response_user_id left join users as cuser on cuser.user_id = comments.user_id  where comments.art_id = %d  order by pubtime ASC limit %d, 15";

	$Comments = M('Comments');

        $data = $Comments->query($sql, $art_id, $begin);

        $this->response(array('code'=>0, 'info'=>'获取成功', 'data'=>$data), 'json');

    }
}


