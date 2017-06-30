<?php
namespace Home\Controller;

use Think\Controller\RestController;

class AlipayController extends RestController {
    public function pay() {
        //require_once('/www/my/app/third_party/alipay/aop/AopClient.php');
        //require_once('/www/my/app/third_party/alipay/aop/request/AlipayTradeAppPayRequest.php');
        $aop = new \Home\Tool\AopClient();

        //**沙箱测试支付宝开始
        $aop->gatewayUrl = "https://openapi.alipaydev.com/gateway.do";
        //实际上线app id需真实的
        $aop->appId = "2016080700185793";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuHQMn0WcP+Yf+pwaq+1ZNrFRlyRoHD4F32QyarQKjdMcmUz62drYap/eub3tzniCmzHK3/uOR1OEJH/wrT0ZtN7cnw7iY02gSPgAVIdSifE5Dzc1HrY2HjslQyvcnMJDUVpneTZGmmiyqrwTuD79qxaUJU7+yGne58fTePF9ArkZN1FACgFEETkLOGUW/tI5xp2FS5K3yQqwGnmn1JehVSMK1Z/tR3vX/SphmODBKWncUJ2BANJjuAlglzSJdR9UZITmrIhrE0T9Qq1c+4pB7t+6M1deYX5XeJxu+3ZZEnMZ/9Ogywvr9NmUIz2hNMxz8RGIs01DmIJlujWjD2/UOQIDAQABAoIBAAuFC+jCkzCWcbbYGv5T03eL+XmEB5CD+x/phRCO7/3NioK0gRIsDcoS8/kLuJdlhVql8HKupkMkHyOcbe6T21AlfwfcCR2EkpccDgE5dlkxHIwruYCShqy6qugYDC4qH8Qr7jxuvFgWY9ov5tdh77vIrDXsShNAMXCuVTG/ezw5MfK1etdDUR7oPa7Byp1zpDJo0V623FS8HJvisGNR7rd97EOtMuy7wwr9Art3Q0XJd2ExmTBqa4lLwX34YNtUYTsoa3vj3I4ygc1ov0bqLKUnHi1TIKHecwzGe2DCZjgdqAgcpGuKaioaPuM8tuMBo0QFIjz5KJx2KW8KZjEvcoECgYEA7ADwH6FsgEvtL5qlN1Cf5qW/Cy9C7lZBBRB49/mRArNJwDkRrfXMLltcKJnaldy7VJbjTRRJTIARAgnVEUMXRYmW9X9Kp6VbpZNEXCHfnjzifq2AqWTW2jZdDZrQr8p6LDeDQQeXPxFxf11qIBnZowzlC5P6K+K29HAOkbDSD9ECgYEAyBT0Ayc1+9w6XuZl6NwG0JeUf8TbsvULAcpP+ljZFYFY9nMvE9qeWfbdUEvEozJdxerTwpcPUSdglXwOS75elhuvqzsq2MD36mxDntyz/H1nKht95QW34aF4ua1Xmv0M3ylHxaOhWfuWDYy/K09UPs5B/QWX60OJpqDqAjJrP+kCgYEA0owzrcRx7DPQszugUi0Xusn8GppbeA5zi3UatwBroqEZFujTIQO82U6gdYhtPm3ioqDKwKVsj1dh1RO4huH4DQ2nI/YgQFiB3sH3psqBmcZvutxHgNh55cvCULThoTNes7wC2S1Qfe+t9hb86w6k35ZNcXrfIe/tkT23gbribUECgYAlOMCsVX8Ve8LgJLyQtV4PMCPQIS89+5gwnRKD4EOCXK3QK112tBUBZ4uEhJPwSE5po2YBrViMIGc3Z/zA2ol+I2hq0ncGG+ADHGD4DNbvAeVPUA37rTSoJQHwiO7jRnA+k89mVSqPMt6XZreptvhVNsnP6Fp6yfWxqf3eqsKJAQKBgQDYjptcCRELl8VpVGjfqMjEbwQunUmJ1CfbjCky1/b7UZ9evaoKmKzVY66lltNtrKyQowycaI7PXcZJYPLhEUqjpALdHwyEdhCorN5ShFHY7M4QZJTr319qzeaM6zXfpStz3BafjxIsKUjfPxgXFizGgsh170/X0+gP99hMk/02HA==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuKfqWuNOYUF4vc4leGgAE/os3HHh0wvvvhzi8n2IT67Q+/pU10MoWAYCl5sDh82FrXhs3P1qdh0bd+9PynLGmyWiNB6sD9l07VUxeHsASC8lfIqTGQoitNKuFi5hq74+ULOWcET3YqcacXdvoIDn7WfGJYINdEEopF5zCiYxN3/1BlPto7LAGwkNhZaw4TGsBdwEhUhUyri70fcHjpQ8CGjTfSOQGc0UA65g32SeGYFbFlGV42PfvAR3X3QjuaKjV6BiB4Xp6rHq3A0VZOa9cspk+92K8QUlVsb+mESPKeWKNEKB66itmv30tM2N1e7YF2Abhjc2F+WYQRwDfWMNlQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'黄金矿车',
            'subject'=>'矿车',
            'out_trade_no'=>'123456',//此订单号为商户唯一订单号
            'total_amount'=> '9.88',//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY'
        ]);
        //**沙箱测试支付宝结束
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \Home\Tool\AlipayTradeAppPayRequest();
        //支付宝回调
        $request->setNotifyUrl("https://demo.com/pay/alinotify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo htmlspecialchars($response);

    }


}
