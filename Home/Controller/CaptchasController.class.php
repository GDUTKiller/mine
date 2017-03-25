<?php
namespace Home\Controller;
use Think\Controller\RestController;

class CaptchasController extends RestController {
    public function send() {
        //如果传入的不是手机号
        if(!preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\d{8}$/', I('mobile') ) ) {
            $this->response(array('code'=>-1, 'info'=>'mobile format is wrong', 'data'=>null),'json');
        } else {
            $Captchas = D('Captchas');
            //查询该手机号数据
            $data = $Captchas->where(array('mobile'=>I('mobile')))->find();
            //数据库中验证码表没有该数据，则新增该数据
            if(!$data) {
                $Captchas->create();
                //变量保存验证码
                $code = $Captchas->captcha;
                $Captchas->add();
            } else if(strtotime(date('YmdHis')) - strtotime($data['created_time']) < 60) {
                //该手机号一分钟内已经发了一次短信，等一分钟再发下一次
                $this->response(array('code'=>-2, 'info'=>'send this request after 1 minute', 'data'=>null),'json');
            } else {
                //重新生成验证码，发送
                $Captchas->create();
                $code = $Captchas->captcha;
                $Captchas->where(array('mobile'=>I('mobile')))->save();
            }
            //发送验证码
            $Sms = new \Home\Tool\SmsTool();
            $this->response(array('code'=>0, 'info'=>'', 'data'=>$Sms->send($code, I('mobile')) ),'json');


        }
    }
}