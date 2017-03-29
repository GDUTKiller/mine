
<?php
namespace Home\Controller;
use Think\Controller\RestController;

class UsersController extends RestController {
    public function add() {
        $Arts = D('Arts');	
	if(!$Arts->create()) {
	    $this->response(array('code'=>-1, 'info'=>$Arts->getError(), 'data'=>null) , 'json');
	} else {
	    $this->response(array('code'=>0, 'info'=>'', 'data'=>null) , 'json');
	}
    }    
}
