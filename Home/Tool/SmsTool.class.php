<?php
namespace Home\Tool;
class SmsTool {
    private $BASE_URL = '',
           $ACCOUNT_SID = '',
           $AUTH_TOKEN = '',
           $CONTENT_TYPE = '',
           $ACCEPT = '';
    public function __construct() {
        /**
         * url前半部分
         */
        $this->BASE_URL = "https://api.miaodiyun.com/20150822/";

        /**
         * url中的accountSid。如果接口验证级别是主账户则传网站“个人中心”页面的“账户ID”，
         */
        $this->ACCOUNT_SID = "443cb1071008416db0b485234ab074ea"; // 主账户
        $this->AUTH_TOKEN = "fd7364493f684b23bbaf23ad092217d0";

        /**
         * 请求的内容类型，application/x-www-form-urlencoded
         */
        $this->CONTENT_TYPE = "application/x-www-form-urlencoded";

        /**
         * 期望服务器响应的内容类型，可以是application/json或application/xml
         */
        $this->ACCEPT = "application/json";

    }

    /**
     * 创建url
     *
     * @param funAndOperate
     *            请求的功能和操作
     * @return
     */
    private function createUrl($funAndOperate) {
        // 时间戳
        date_default_timezone_set("Asia/Shanghai");
        $timestamp = date("YmdHis");

        return $this->BASE_URL . $funAndOperate;
    }

    private function createBasicAuthData() {
        $timestamp = date("YmdHis");
        // 签名
        $sig = md5($this->ACCOUNT_SID . $this->AUTH_TOKEN . $timestamp);
        return array("accountSid" => $this->ACCOUNT_SID, "timestamp" => $timestamp, "sig" => $sig, "respDataType"=> "JSON");
    }

    /**
     * 创建请求头
     * @param body
     * @return
     */
    private function createHeaders() {

        $headers = array('Content-type: ' . $this->CONTENT_TYPE, 'Accept: ' . $this->ACCEPT);

        return $headers;
    }

    /**
     * post请求
     *
     * @param funAndOperate
     *            功能和操作
     * @param body
     *            要post的数据
     * @return
     * @throws IOException
     */
    private function post($funAndOperate, $body) {

        // 构造请求数据
        $url = $this->createUrl($funAndOperate);
        $headers = $this->createHeaders();

        // echo("url:<br/>" . $url . "\n");
        // echo("<br/><br/>body:<br/>" . json_encode($body));
        // echo("<br/><br/>headers:<br/>");
        // var_dump($headers);

        // 要求post请求的消息体为&拼接的字符串，所以做下面转换
        $fields_string = "";
        foreach ($body as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        // 提交请求
        $con = curl_init();
        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($con, CURLOPT_HEADER, 0);
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($con, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($con, CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($con);
        curl_close($con);

        return "" . $result;
    }

    public function send($code, $mobile) {
        /**
         * url中{function}/{operation}?部分
         */
        $funAndOperate = "industrySMS/sendSMS";

        // 参数详述请参考http://miaodiyun.com/https-xinxichaxun.html

        // 生成body
        $body = $this->createBasicAuthData();
        // 在基本认证参数的基础上添加短信内容和发送目标号码的参数
        $body['smsContent'] = "【一蝉科技】您的验证码为" . $code . "，请于5分钟内正确输入，如非本人操作，请忽略此短信。";
        $body['to'] = $mobile;

        // 提交请求
        // $result =
        return $this->post($funAndOperate, $body);
        // echo("<br/>result:<br/><br/>");
        // var_dump($result);
    }
}