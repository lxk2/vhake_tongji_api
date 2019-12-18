<?php

namespace data\extend\apay;

require_once 'aop/AopClient.php';
require_once 'aop/request/AlipayTradeAppPayRequest.php';

class AliPay
{

    public function pay($data) {

        // 获取支付金额
        $amount = $data['actual_price'];
//        $amount = 1;

        $total = floatval($amount);
        if (!$total) {
            $total = 1;
        }

        $aliConfig = new \data\extend\apay\AliConfig();

        $aop = new aop\AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $aliConfig->appId;
        $aop->rsaPrivateKey = $aliConfig->resPrivateKey;
        $aop->format = "json";
        $aop->postCharset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $aliConfig->alipayrsPublicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \data\extend\apay\aop\request\AlipayTradeAppPayRequest();

        // 异步通知地址
        $notify_url = urlencode($aliConfig->notify_url);
        // 订单标题
        $subject = '天师林APP支付';
        // 订单详情
        $body = '';
        // 订单号，示例代码使用时间值作为唯一的订单ID号
        $out_trade_no = $data['order_no'];

        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"" . $body . "\","
            . "\"subject\": \"" . $subject . "\","
            . "\"out_trade_no\": \"" . $out_trade_no . "\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"" . $total . "\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);

        // 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可
        return $response;
    }

    public function webpay($data) {

        // 获取支付金额
        $amount = $data['money'];
//        $amount = 1;

        $total = floatval($amount);
        if (!$total) {
            $total = 1;
        }

        $aliConfig = new \data\extend\apay\AliConfig();

        $aop = new aop\AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $aliConfig->appId;
        $aop->rsaPrivateKey = $aliConfig->resPrivateKey;
        $aop->format = "json";
        $aop->postCharset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $aliConfig->alipayrsPublicKey;

        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \data\extend\apay\aop\request\AlipayTradeWapPayRequest();

        //支付成功，返回页面
        $return_url = $aliConfig->return_url . $data['sn'];
        // 异步通知地址
        $notify_url = $aliConfig->notify_url;
        // 订单标题
        $subject = '营尚媒手机支付';
        // 订单详情
        $body = '';
        // 订单号，示例代码使用时间值作为唯一的订单ID号
        $out_trade_no = $data['sn'];

        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"" . $body . "\","
            . "\"subject\": \"" . $subject . "\","
            . "\"out_trade_no\": \"" . $out_trade_no . "\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"" . $total . "\","
            . "\"product_code\":\"QUICK_WAP_WAY\""
            . "}";

        $request->setNotifyUrl($notify_url);

        $request->setReturnUrl($return_url);

        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->pageExecute($request);

        // 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可
        return $response;
    }

    public function test() {
        $aop = new aop\AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = 'your app_id';
        $aop->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
        $aop->alipayrsaPublicKey='请填写支付宝公钥，一行字符串';
        $aop->apiVersion = '1.0';
        $aop->postCharset='GBK';
        $aop->format='json';
        $aop->signType='RSA2';
        $request = new AlipayTradeWapPayRequest ();
        $request->setBizContent("{" .
            "    \"body\":\"对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。\"," .
            "    \"subject\":\"大乐透\"," .
            "    \"out_trade_no\":\"70501111111S001111119\"," .
            "    \"timeout_express\":\"90m\"," .
            "    \"total_amount\":9.00," .
            "    \"product_code\":\"QUICK_WAP_WAY\"" .
            "  }");
        $result = $aop->pageExecute ( $request);
        echo $result;
    }
}