<?php
namespace data\extend\wxAppPay;
require_once "WxPay.Api.php";
require_once "WxPay.Data.php";

class WPay {
    public function getPayStr($data){
        // 获取支付金额
        $total = floatval($data['actual_price']);
        $total = round($total*100); // 将元转成分
        if(empty($total)){
            $total = 100;
        }
        // 商品名称
        $subject   = '天师林支付';
        // 订单号，示例代码使用时间值作为唯一的订单ID号
        $out_trade_no =$data['order_no'];
        $unifiedOrder = new \WxPayUnifiedOrder();
        $unifiedOrder->SetBody($subject);//商品或支付单简要描述
//        $unifiedOrder->SetNotify_url('http://y.vhake.com/v1/api_pay/notify');
        $unifiedOrder->SetOut_trade_no($out_trade_no);
        $unifiedOrder->SetTotal_fee($total);
        $unifiedOrder->SetTrade_type("APP");
//        dump($unifiedOrder);die;
        $result = \WxPayApi::unifiedOrder($unifiedOrder);
        if (is_array($result)) {
            return $result;
        }
    }

    public function getJsPayStr($data, $openid){
        // 获取支付金额
        $total = floatval($data['money']);
        $total = round($total*100); // 将元转成分
        if(empty($total)){
            $total = 100;
        }
        // 商品名称
        $subject   = '天师林APP支付';
        // 订单号，示例代码使用时间值作为唯一的订单ID号
        $out_trade_no =$data['sn'];

        $unifiedOrder = new \WxPayUnifiedOrder();
        $unifiedOrder->SetBody($subject);//商品或支付单简要描述
//        $unifiedOrder->SetNotify_url('http://y.vhake.com/v1/api_pay/notify');

        $unifiedOrder->SetOut_trade_no($out_trade_no);
        $unifiedOrder->SetTotal_fee($total);

        //交易类型为JSAPI时，openid必传；
        $unifiedOrder->SetOpenid($openid);
        $unifiedOrder->SetTrade_type("JSAPI");
//        dump($unifiedOrder);die;
        $result = \WxPayApi::unifiedOrder($unifiedOrder);
        if (is_array($result)) {
            return $result;
        }
    }
}
?>