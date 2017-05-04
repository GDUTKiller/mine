<?php
namespace Home\Controller;
use Think\Controller\RestController;

class UsersController extends RestController {


   /**
     * 获取用户信息
     * GET host/users/id
     * @return $_GET['id'] 用户主键
     */
    public function getInfo() {
        $Users = D('Users');
        //if(!$Users->acc()) {
        //    //用户尚未登录，返回错误
        //    $this->response(array('code'=>-1, 'info'=>'用户尚未登录','data'=>null), 'json');
        //}

	$data = $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->find(I('user_id'));
	//查询的用户不存在
	if(!$data) {
            $this->response(array('code'=>-2, 'info'=>'要查询的用户不存在','data'=>null), 'json');
        }

	$Id = M('Identifications');

	$id_data = $Id->field('id,id_name')->where(array('user_id'=>I('user_id')))->find();
	//如果用户尚未实名认证，则$id_data为null，不可直接加
	if(!$id_data) {
	    $data['id_name'] = '';
	    $data['id'] = '';
	} else {
	//已实名认证，$id_data为数组，可直接加
	    $data += $id_data;
	}

	if(I('user_id') != cookie('user_id')) {
	    $data['mobile'] = '';
	    $data['count'] = '';
	    $data['id_name'] = '';
	    $data['id'] = '';
	}
	$this->response(array('code'=>0, 'info'=>'获取用户信息成功','data'=>$data), 'json');
        
    }

    /**
     * 更新用户信息
     * @param  PUT请求 host/users
     * @return [type] [description]
     */
    public function update() {
        //更改密码，不能同时更改密码和其他数据
        //密码为空则更改其他数据
        if(null != I('put.password')) {

	    //手机号
	    $mobile = I('put.mobile');
	    if($mobile == null) {
		$this->response(array('code'=>-5, 'info'=>'手机号码不能为空', 'data'=>null), 'json');
	    }
	    if(!preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', $mobile) ) {
	   	$this->response(array('code'=>-6, 'info'=>'手机号码格式错误', 'data'=>null), 'json');
	    }

	   
            if(!preg_match('/^[0-9a-zA-Z_]{6,16}$/', I('put.password'))) {
                //密码格式不对
                $this->response(array('code'=>-7, 'info'=>'密码格式错误', 'data'=>null), 'json');
            } else if(null == I('put.captcha') ) {
                //验证码为空
                $this->response(array('code'=>-8, 'info'=>'验证码不能为空', 'data'=>null), 'json');
            } else {
                $Captchas = M('Captchas');
                $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>$mobile))->find();
                //验证码错误
                if(I('put.captcha') != $data['captcha']) {
                    $this->response(array('code'=>-9, 'info'=>'验证码错误', 'data'=>null), 'json');
                } else if(strtotime(date('YmdHis')) > strtotime($data['expires_at'])  || $data['status'] == '1') {

                    //验证码过期 或者已经用过
                    $this->response(array('code'=>-10, 'info'=>'验证码过期', 'data'=>null), 'json');
                }

                //更改验证码status并且保存
                $Captchas->status = 1;
		$Captchas->field('status')->where(array('mobile'=>$mobile))->save();

		$Users = D('Users');
                $Users->password = I('put.password');
                //更改密码，生成新的盐
                $Users->encPass();

                //保存更改
                $Users->field('password,salt')->where(array('mobile'=>$mobile))->save();
 		$data = $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('mobile'=>$mobile))->find();
                $this->response(array('code'=>0, 'info'=>'更改密码成功', 'data'=>$data), 'json');
               
            }
        }


	//更改姓名性别生日时需要登录状态下操作
        $Users = D('Users');
        if(!$Users->acc()) {
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录', 'data'=>null), 'json');
        }

        //更改姓名
        if(!empty(I('put.name')) ) {
            if( !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', I('put.name')) ) {
                $this->response(array('code'=>-2, 'info'=>'名字格式错误', 'data'=>null), 'json');
  	    }
	    $Users->name = I('put.name');
        }

        //更改性别
        if(!empty(I('put.sex')) ) {
	    if(!preg_match('/(男|女)$/', I('put.sex'))) {
                $this->response(array('code'=>-3, 'info'=>'性别格式错误', 'data'=>null), 'json');
	    }
            $Users->sex = I('put.sex');
        }

        //更改生日
        if(!empty(I('put.birthday'))  ) {
	    if(!preg_match('/^(19|20)\d{2}-(1[0-2]|0?[1-9])-(0?[1-9]|[1-2][0-9]|3[0-1])$/', I('put.birthday'))) {
 	    
                $this->response(array('code'=>-4, 'info'=>'生日格式错误', 'data'=>null), 'json');
 	    }
            $Users->birthday = I('put.birthday');
        }

        //保存姓名性别生日的更改
        if( $Users->field('name,sex,birthday')->where(array('user_id'=>cookie('user_id')))->save()) {
            $this->response(array('code'=>0, 'info'=>'更改用户信息成功', 'data'=>$Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('user_id'=>cookie('user_id')))->find()), 'json');
 	} else {
            $this->response(array('code'=>-11, 'info'=>'更改失败'), 'json');
 	}
    }

    /**
     * 注册用户
     * POST请求 host/users
     * @return json
     */
    public function register() {
        $Users = D('Users');

        $Captchas = M('Captchas');
        $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>I('mobile')))->find();

	if($data == NULL) {
            $this->response(array('code'=>-6, 'info'=>'尚未发送验证码给用户', 'data'=>null), 'json');
	}

        //验证码错误
        if(I('captcha') != $data['captcha']) {
            $this->response(array('code'=>-3, 'info'=>'验证码错误', 'data'=>null), 'json');
        } else if(strtotime(date('YmdHis')) > strtotime($data['expires_at'])  || $data['status'] == '1') {
            //验证码过期 或者已经用过
            $this->response(array('code'=>-4, 'info'=>'验证码过期', 'data'=>null), 'json');
        }

        //更改验证码status并且保存
        $Captchas->status = 1;
        $Captchas->field('status')->where(array('mobile'=>I('mobile')))->save();


        if(!$Users->field('mobile,password,name,recommend_code')->create()) {
            $this->response(array('code'=>-5,'info'=>$Users->getError(), 'data'=>null), 'json');
        }
	$user_id = $Users->reg();

	//给用户一台新手矿车
	$Cars = M('Cars');
	$Cars->add(array('user_id'=>$user_id));

        if($user_id ) {
            $this->response(array('code'=>0, 'info'=>'注册成功', 'data'=> $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('user_id'=>$user_id))->find()), 'json');
        } else {
            $this->response(array('code'=>-2, 'info'=>'注册失败', 'data'=>null), 'json');
        }
    }

    /**
     * 上传用户头像
     * POST host/avatars
     * @return [type] [description]
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
            $upload->rootPath  =     './Uploads/Avatars/'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录
            // 上传文件
	    //这里用一个变量保存上传的图片名，因为苹果客户端那边的问题，他采用二进制上传文件，无法指定上传的文件名
	    $fileName = array_keys($_FILES)[0];

            $info   =   $upload->upload();

            //上传失败
            if(!$info) {
                $this->response(array('code'=>-2, 'info'=>$upload->getError(), 'data'=>null), 'json');
            } else {
                //删除用户以前的头像
                $oldAvatar = $Users->field('avatar')->find(cookie('user_id'));
                if($oldAvatar['avatar'] && file_exists('.' . $oldAvatar['avatar'])) {
                    unlink('.' . $oldAvatar['avatar']);
                }

                //保存用户头像路径
                $avatarPath = '/Uploads/Avatars/' . $info[$fileName]['savepath'] . $info[$fileName]['savename'];
                $Users->avatar = $avatarPath;
                $Users->where(array('user_id'=>cookie('user_id')))->save();
                $this->response(array('code'=>0,'info'=>'上传头像成功', 'data'=>array('path'=>$avatarPath)), 'json');
            }
        }
    }

    /**
     * 用户登录
     * POST请求 host/sessions
     * @return json
     */
    public function login() {
        $Users = D("Users"); // 实例化User对象
        if (!$Users->field('mobile,password')->create($_POST, 4)){ // 登录验证数据
            // 验证没有通过 输出错误提示信息
            $this->response(array('code'=>-2, 'info'=>$Users->getError(), 'data'=>null), 'json');
        }else{
	    //先查找该用户，使$Users->data()为该用户的数据
            $Users->where(array('mobile'=>I('mobile') ) )->find();
            $Users->auth();

	    //查找数据，返回
            $data = $Users->field('user_id,mobile,name,avatar,sex,birthday,recommend_code,city,count')->where(array('mobile'=>I('mobile') ) )->find();
            $this->response(array('code'=>0, 'info'=>'登录成功', 'data'=>$data), 'json');
        }
    }


    /**
     * @uri host/ids POST
     * @access public
     * @param POST['id'] 身份证号码
     * @param POST['name'] 姓名
     * @return josn 
     */
    public function identify() {
  	$Users = D('Users');
        if(!$Users->acc()) {
            //用户尚未登录，返回错误
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录','data'=>null), 'json');
        }

	//存入用户user_id和手机mobile
	$data = $Users->field('user_id,mobile')->where(array('user_id'=>cookie('user_id') ))->find();

	$Id = M('Identifications');

	if($Id->where($data)->find() ) {
            $this->response(array('code'=>-2, 'info'=>'您已实名认证，无需再认证', 'data'=>null), 'json');
	}

	if(!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]{2,10}$/u', I('name')) ) {
            $this->response(array('code'=>-3, 'info'=>'姓名格式不对', 'data'=>null), 'json');
	}

	//15位和18位身份证号码的正则表达式
	$regIdCard='/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/';

	$idCard = I('id');	

	//存入身份证号id和姓名name
	$data['id'] = $idCard;
	$data['id_name'] = I('name');

	//如果通过该验证，说明身份证格式正确，但准确性还需计算
	if(preg_match($regIdCard, $idCard ) ){
	    if( strlen($idCard) == 18){
	    $idCardWi = array( 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ); //将前17位加权因子保存在数组里
	    $idCardY = array( 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ); //这是除以11后，可能产生的11位余数、验证码，也保存成数组
	    $idCardWiSum = 0; //用来保存前17位各自乖以加权因子后的总和
	    for($i = 0; $i < 17; ++$i){
	    	$idCardWiSum += substr($idCard, $i, 1) * $idCardWi[$i];
	    }

	    $idCardMod = $idCardWiSum % 11;//计算出校验码所在数组的位置
	    $idCardLast = substr($idCard, 17, 1);//得到最后一位身份证号码

	    //如果等于2，则说明校验码是10，身份证号码最后一位应该是X
	    if($idCardMod == 2){
	        if($idCardLast == "X" || $idCardLast == "x"){
		    $Id->add($data);
            	    $this->response(array('code'=>0, 'info'=>'实名认证成功', 'data'=>null), 'json');
	        }else{
            	    $this->response(array('code'=>-5, 'info'=>'身份证号码错误！', 'data'=>null), 'json');
	        }
	    } else {
	        //用计算出的验证码与最后一位身份证号码匹配，如果一致，说明通过，否则是无效的身份证号码
	        if($idCardLast == $idCardY[$idCardMod]){
		    $Id->add($data);
            	    $this->response(array('code'=>0, 'info'=>'实名认证成功', 'data'=>null), 'json');
	        } else {
            	    $this->response(array('code'=>-5, 'info'=>'身份证号码错误！', 'data'=>null), 'json');
	        }
	    }
	    } 
	} else {
            $this->response(array('code'=>-4, 'info'=>'身份证格式不正确！', 'data'=>null), 'json');
	}
		
    }

    /**
     * 转赠金币
     * @access public 转赠金币 PUT host/golds
     * @param int gold 要转赠的金币数
     * @param string mobile 被转赠者的手机号
     * @param string captcha 验证码
     */
    public function changeCount() {
  	$Users = D('Users');
        if(!$Users->acc()) {
            //用户尚未登录，返回错误
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录','data'=>null), 'json');
        }

	$from_user_id = cookie('user_id');	
	$gold = intval(I('gold'));
	$to_mobile = I('mobile');
	$captcha = I('captcha');

	if($gold < C('PRESENT_LEAST')) {
	    $this->response(array('code'=>-2, 'info'=>"要转赠的金币最少为" . C('PRESENT_LEAST'), 'data'=>null), 'json');
	}
	
	$origin_gold = $Users->where(array('user_id'=>$from_user_id))->getField('count'); 
	if($gold > $origin_gold) {
	    $this->response(array('code'=>-3, 'info'=>"要转赠的金币最大值为{$origin_gold}", 'data'=>null), 'json');
	}

	$from_mobile = $Users->where(array('user_id'=>$from_user_id))->getField('mobile'); 

	//要转赠的用户
	$to_user_data = $Users->where(array('mobile'=>$to_mobile))->find();
	if($to_user_data == NULL) {
	    $this->response(array('code'=>-4, 'info'=>'要转赠的用户不存在', 'data'=>null), 'json');
	}
	$to_user_id = $to_user_data['user_id'];

	//验证码验证
	$Captchas = M('Captchas');
        $data = $Captchas->field('captcha, expires_at, status')->where(array('mobile'=>$from_mobile))->find();

        //验证码错误
        if($captcha != $data['captcha']) {
            $this->response(array('code'=>-5, 'info'=>'验证码错误', 'data'=>null), 'json');
        } else if(time() > strtotime($data['expires_at'])  || $data['status'] == '1') {
            //验证码过期 或者已经用过
            $this->response(array('code'=>-6, 'info'=>'验证码过期', 'data'=>null), 'json');
        }

        //更改验证码status并且保存
        $Captchas->status = 1;
        $Captchas->field('status')->where(array('mobile'=>$from_mobile))->save();

	$Users->startTrans();
	$rs1 = $Users->where(array('user_id'=>$from_user_id))->setDec('count', $gold);
	$rs2 = $Users->where(array('user_id'=>$to_user_id))->setInc('count', intval($gold * C('PRESENT_COMMISSION')) );
	if($rs1 === false || $rs2 === false) {
	    $Users->rollback();
            $this->response(array('code'=>-7, 'info'=>'转赠金币失败', 'data'=>null), 'json');
	} else {
	    $Users->commit();
            $this->response(array('code'=>0, 'info'=>'转增金币成功', 'data'=>null), 'json');
	}
    }
    

    /*
     * 获取排行榜
     * GET host/ranks/{$type}
     * @param int $type 排行榜的类型 0为金币排行榜 1为矿车折算价值排行榜
     * @return json 
     */
    public function getRank() {
  	$Users = D('Users');
        if(!$Users->acc()) {
            //用户尚未登录，返回错误
            $this->response(array('code'=>-1, 'info'=>'用户尚未登录','data'=>null), 'json');
        }
	$city = $Users->where(array('user_id'=>cookie('user_id')))->getField('city');
 	$type = I('type'); 
	if($type == 0) {
	    $user_data = $Users->field('user_id,name,count,name,avatar')->where(array('city'=>$city))->order('count desc')->limit(30)->select();
            $this->response(array('code'=>0, 'info'=>'获取排行榜成功', 'data'=>$user_data), 'json');

	}

	

	$car_1 = C('CAR_PRICE_1');
	$car_2 = C('CAR_PRICE_2');
	$car_3 = C('CAR_PRICE_3');
	$user_data = $Users->query("select user_id,name,count,name,avatar,car_1,car_2,car_3 from users where city = '广州' order by car_1 * $car_1 + car_2 * $car_2 + car_3 * $car_3 desc limit 30;");
	S($city.'rank'.$type, $user_data, 60 * 30);
        $this->response(array('code'=>0, 'info'=>'获取排行榜成功', 'data'=>$user_data), 'json');
		

    }


    /**
     * 用户注销
     * DELETE请求 host/session
     * @return [type] [description]
     */
    public function logout() {
        D('Users')->revoke();
        $this->response(array('code'=>0, 'info'=>'注销成功', 'data'=>null), 'json');
    }
    
    public function test1() {
	define('KILLER', 'killer');
	echo KILLER;
    }

    public function test2() {
	$siteinfo_file = './Common/Conf/commission.php';
	$config = array('COMMISSION_FIRST'=>'0.6');
	$result = file_put_contents($siteinfo_file, "<?php\nreturn " . var_export($config, true).';');
	echo $result;
    }
    
}
