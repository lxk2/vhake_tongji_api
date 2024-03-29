<?php
namespace data\extend\hook;

use data\model\WebSiteModel;
use data\model\UserModel;
use data\model\ConfigModel;
use data\model\NoticeTemplateModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use phpDocumentor\Reflection\Types\This;
use data\model\NsOrderGoodsExpressModel;
class Notify
{
    public $result=array(
        "code"=>0,
        "message"=>"success",
        "param"=>""
    );
    /**
     * 邮件的配置信息
     * @var unknown
    */
    public $email_is_open=0;
    public $email_host="";
    public $email_port="";
    public $email_addr="";
    public $email_id="";
    public $email_pass="";
    /**
     * 短信的配置信息
     * @var unknown
     */
    public $mobile_is_open;
    public $appKey="";
    public $secretKey="";
    public $freeSignName="";
    
    public $shop_name;
    
    public $ali_use_type=0;
    /**
     * 得到系统通知的配置信息
     * @param unknown $shop_id
     */
    private function getShopNotifyInfo($shop_id){
    
        $website_model=new WebSiteModel();
        $website_obj=$website_model->getInfo("1=1", "title");
        if(empty($website_obj)){
            $this->shop_name="Vhake开源商城";
        }else{
            $this->shop_name=$website_obj["title"];
        }
    
        $config_model=new ConfigModel();
        #查看邮箱是否开启
        $email_info=$config_model->getInfo(["instance_id"=>$shop_id, "`key`"=>"EMAILMESSAGE"], "*");
        if(!empty($email_info)){
            $this->email_is_open=$email_info["is_use"];
            $value=$email_info["value"];
            if(!empty($value)){
                $email_array=json_decode($value, true);
                $this->email_host=$email_array["email_host"];
                $this->email_port=$email_array["email_port"];
                $this->email_addr=$email_array["email_addr"];
                $this->email_id=$email_array["email_id"];
                $this->email_pass=$email_array["email_pass"];
            }
        }
        $mobile_info=$config_model->getInfo(["instance_id"=>$shop_id, "`key`"=>"MOBILEMESSAGE"], "*");
        if(!empty($mobile_info)){
            $this->mobile_is_open=$mobile_info["is_use"];
            $value=$mobile_info["value"];
            if(!empty($value)){
                $mobile_array=json_decode($value, true);
                $this->appKey=$mobile_array["appKey"];
                $this->secretKey=$mobile_array["secretKey"];
                $this->freeSignName=$mobile_array["freeSignName"];
                $this->ali_use_type=$mobile_array["user_type"];
                if(empty($this->ali_use_type)){
                    $this->ali_use_type=0;
                }
            }
        }
    }
   
    /**
     * 查询模板的信息
     * @param unknown $shop_id
     * @param unknown $template_code
     * @param unknown $type
     * @return unknown
     */
    private function getTemplateDetail($shop_id, $template_code, $type){
       $template_model=new NoticeTemplateModel();
       $template_obj=$template_model->getInfo(["instance_id"=>$shop_id, "template_type"=>$type, "template_code"=>$template_code]);
       return $template_obj;
    }
    /**
     * 处理阿里大于 的返回数据
     * @param unknown $result
     */
    private function dealAliSmsResult($result){
        $deal_result=array();
        try {
            if($this->ali_use_type==0){
                #旧用户发送
                if(!empty($result)){
                    if(!isset($result->result)){
                        $result=json_decode(json_encode($result), true);
                        #发送失败
                        $deal_result["code"]=$result["code"];
                        $deal_result["message"]=$result["msg"];
                    }else{
                        #发送成功
                        $deal_result["code"]=0;
                        $deal_result["message"]="发送成功";
                    }
                }
            }else{
                #新用户发送
                if(!empty($result)){
                    if($result->Code=="OK"){
                        #发送成功
                        $deal_result["code"]=0;
                        $deal_result["message"]="发送成功";
                    }else{
                        #发送失败
                        $deal_result["code"]=-1;
                        $deal_result["message"]=$result->Message;
                    }
                }
          }
        } catch (\Exception $e) {
            $deal_result["code"]=-1;
            $deal_result["message"]="发送失败!";
        }
        
        return $deal_result;
    }
    /**
     * 用户注册成功后
     * @param string $params
     */
    public function registAfter($params=null){
        /**
         * 店铺id
         */
        $shop_id=$params["shop_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        /**
         * 用户id
         */
        $user_id=$params["user_id"];
        
        $user_model=new UserModel();
        $user_obj=$user_model->get($user_id);
        $mobile="";
        $user_name="";
        $email="";
        if(empty($user_obj)){
            $user_name="用户";
        }else{
            $user_name=$user_obj["user_name"];
            $mobile=$user_obj["user_tel"];
            $email=$user_obj["user_email"];
        }
        #短信验证
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "after_register", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $sms_params=array(
                    "shop_name"=>$this->shop_name,
                    "user_name"=>$user_name
                );
                $result=aliSmsSend($this->appKey, $this->secretKey,
                    $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
                $this->result["code"]=$result->code;
                $this->result["message"]=$result->msg;
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="商家没开启短信模板!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="商家没开启短信模板!";
        }
        #邮箱验证
        if(!empty($email) && $this->email_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "after_register", "email");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $content=$template_obj["template_content"];
                $content=str_replace("{商场名称}", $this->shop_name, $content);
                $content=str_replace("{用户名称}", $user_name, $content);
                $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                if($result){
                    $this->result["code"]=0;
                    $this->result["message"]="发送成功!";
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="发送失败!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="商家没开启邮箱验证";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="商家没开启邮箱验证";
        }
        return $this->result;
    }
    /**
     * 注册短信验证
     * @param string $params
     */
    public function registBefor($params=null){
        $rand = rand(100000,999999);
        $mobile=$params["mobile"];
        $shop_id=$params["shop_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        $result="";
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "register_validate", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $sms_params=array(
                    "number"=>$rand.""
                );
                $this->result["param"]=$rand;
                if(!empty($this->appKey) && !empty($this->secretKey) && !empty($template_obj["sign_name"]) && !empty($template_obj["template_title"])){
                    $result=aliSmsSend($this->appKey, $this->secretKey,
                        $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
                    $result=$this->dealAliSmsResult($result);
                    $this->result["code"]=$result["code"];
                    $this->result["message"]=$result["message"];
                    $this->result["param"]=$rand;
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="短信配置信息有误!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="短信通知模板有误!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启短信验证";
        }
        return $this->result;
    }
    /**
     * 注册邮箱验证
     * 已测试
     * @param string $params
     */
    public function registEmailValidation($params=null){
        $rand = rand(100000,999999);
        $email=$params["email"];
        $shop_id=$params["shop_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        if(!empty($email) && $this->email_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "register_validate", "email");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $content=$template_obj["template_content"];
                $content=str_replace("{验证码}", $rand, $content);
                if(!empty($this->email_host) && !empty($this->email_id) && !empty($this->email_pass) && !empty($this->email_addr)){
                    $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                    $this->result["param"]=$rand;
                    if($result){
                        $this->result["code"]=0;
                        $this->result["message"]="发送成功!";
                    }else{
                        $this->result["code"]=-1;
                        $this->result["message"]="发送失败!";
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="邮箱配置信息有误!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="配置邮箱注册验证模板有误!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启邮箱验证";
        }
        return $this->result;
        
    }
    /**
     * 订单发货
     * @param string $params
     */
    public function orderDelivery($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        $order_goods_ids=$params["order_goods_ids"];
        $order_goods_str=explode(",", $order_goods_ids);
        $result="";
        $user_name="";
        if(count($order_goods_str)>0){
            $order_goods_id=$order_goods_str[0];
            $order_goods_model=new NsOrderGoodsModel();
            $order_goods_obj=$order_goods_model->get($order_goods_id);
            $shop_id=$order_goods_obj["shop_id"];
            $order_id=$order_goods_obj["order_id"];
            $order_model=new NsOrderModel();
            $order_obj=$order_model->get($order_id);
            $user_name=$order_obj["receiver_name"];
            $buyer_id=$order_obj["buyer_id"];
            $goods_name=$order_goods_obj["goods_name"];
            $goods_sku=$order_goods_obj["sku_name"];
            $order_no=$order_obj["out_trade_no"];
            $order_money=$order_obj["pay_money"];
            $goods_money=$order_goods_obj["goods_money"];
            $mobile=$order_obj["receiver_mobile"];
            $goods_express_model=new NsOrderGoodsExpressModel();
            $express_obj=$goods_express_model->getInfo(["order_id"=>$order_id, "order_goods_id_array"=>$order_goods_ids], "*");
            $sms_params=array(
                "shop_name"=>$this->shop_name,
                "user_name"=>$user_name,
                "goods_name"=>$goods_name,
                "goods_sku"=>$goods_sku,
                "order_no"=>$order_no,
                "order_money"=>$order_money,
                "goods_money"=>$goods_money,
                "express_company"=>$express_obj["express_name"],
                "express_no"=>$express_obj["express_no"]
            );
            #短信发送
            if(!empty($mobile) && $this->mobile_is_open==1){
                $template_obj=$this->getTemplateDetail($shop_id, "order_deliver", "sms");
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    $result=aliSmsSend($this->appKey, $this->secretKey, $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
                }
            }
            // 邮件发送
            $user_model = new UserModel();
            $user_obj = $user_model->get($buyer_id);
            if (! empty($user_obj)) {
                $email = $user_obj["user_email"];
                if (! empty($email) && $this->email_is_open == 1) {
                    $template_obj = $this->getTemplateDetail($shop_id, "order_deliver", "email");
                    if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                        $content = $template_obj["template_content"];
                        $content = str_replace("{商场名称}", $this->shop_name, $content);
                        $content = str_replace("{用户名称}", $user_name, $content);
                        $content = str_replace("{商品名称}", $goods_name, $content);
                        $content = str_replace("{商品规格}", $goods_sku, $content);
                        $content = str_replace("{主订单号}", $order_no, $content);
                        $content = str_replace("{订单金额}", $order_money, $content);
                        $content = str_replace("{商品金额}", $goods_money, $content);
                        $content = str_replace("{物流公司}", $express_obj["express_name"], $content);
                        $content = str_replace("{快递编号}", $express_obj["express_no"], $content);
                        $result=emailSend($this->email_host, $this->email_id,
                            $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                    }
                }
            }
        }
    }
    /**
     * 订单确认
     * @param string $params
     */
    public function orderComplete($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        
        $order_id=$params["order_id"];
        $order_model=new NsOrderModel();
        $order_obj=$order_model->get($order_id);
        $shop_id=$order_obj["shop_id"];
        $buyer_id=$order_obj["buyer_id"];
        $user_name=$order_obj["receiver_name"];
        $order_no=$order_obj["out_trade_no"];
        $order_money=$order_obj["pay_money"];
        $mobile=$order_obj["receiver_mobile"];
        $sms_params=array(
            "shop_name"=>$this->shop_name,
            "user_name"=>$user_name,
            "order_no"=>$order_no,
            "order_money"=>$order_money
        );
            // 短信发送
        if (! empty($mobile) && $this->mobile_is_open == 1) {
            $template_obj = $this->getTemplateDetail($shop_id, "confirm_order", "sms");
            if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                $result = aliSmsSend($this->appKey, $this->secretKey, $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
            }
        }
        // 邮件发送
        $user_model = new UserModel();
        $user_obj = $user_model->get($buyer_id);
        if (! empty($user_obj)) {
            $email = $user_obj["user_email"];
            if (! empty($email) && $this->email_is_open == 1) {
                $template_obj = $this->getTemplateDetail($shop_id, "confirm_order", "email");
                if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                    $content = $template_obj["template_content"];
                    $content = str_replace("{商场名称}", $this->shop_name, $content);
                    $content=str_replace("{用户名称}", $user_name, $content);
                    $content=str_replace("{主订单号}", $order_no, $content);
                    $content=str_replace("{订单金额}", $order_money, $content);
                    $result=emailSend($this->email_host, $this->email_id,
                        $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                }
            }
        }
    }
    /**
     * 订单付款成功
     * @param string $params
     */
    public function orderPay($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        
        $order_id=$params["order_id"];
        $order_model=new NsOrderModel();
        $order_obj=$order_model->get($order_id);
        $shop_id=$order_obj["shop_id"];
        $buyer_id=$order_obj["buyer_id"];
        $user_name=$order_obj["receiver_name"];
        $order_no=$order_obj["out_trade_no"];
        $order_money=$order_obj["pay_money"];
        $mobile=$order_obj["receiver_mobile"];
        $goods_money=$order_obj["goods_money"];
        $sms_params=array(
            "shop_name"=>$this->shop_name,
            "user_name"=>$user_name,
            "order_no"=>$order_no,
            "order_money"=>$order_money,
            "goods_money"=>$goods_money
        );
        #短信发送
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "pay_success", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $result=aliSmsSend($this->appKey, $this->secretKey,
                    $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
            }
        }
        #邮件发送
        $user_model=new UserModel();
        $user_obj=$user_model->get($buyer_id);
        if(!empty($user_obj)){
            $email=$user_obj["user_email"];
            if(!empty($email) && $this->email_is_open==1){
                $template_obj=$this->getTemplateDetail($shop_id, "pay_success", "email");
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    $content=$template_obj["template_content"];
                    $content=str_replace("{商场名称}", $this->shop_name, $content);
                    $content=str_replace("{用户名称}", $user_name, $content);
                    $content=str_replace("{主订单号}", $order_no, $content);
                    $content=str_replace("{订单金额}", $order_money, $content);
                    $content=str_replace("{商品金额}", $goods_money, $content);
                    $result=emailSend($this->email_host, $this->email_id,
                                $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                }
            }
        }
    }
    /**
     * 订单创建成功
     * @param string $params
     */
    public function orderCreate($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        $order_id=$params["order_id"];
        $order_model=new NsOrderModel();
        $order_obj=$order_model->get($order_id);
        $shop_id=$order_obj["shop_id"];
        $buyer_id=$order_obj["buyer_id"];
        $user_name=$order_obj["receiver_name"];
        $order_no=$order_obj["out_trade_no"];
        $order_money=$order_obj["pay_money"];
        $mobile=$order_obj["receiver_mobile"];
        $goods_money=$order_obj["goods_money"];
        $sms_params=array(
            "shop_name"=>$this->shop_name,
            "user_name"=>$user_name,
            "order_no"=>$order_no,
            "order_money"=>$order_money,
            "goods_money"=>$goods_money
        );
        #短信发送
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "create_order", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $result=aliSmsSend($this->appKey, $this->secretKey,
                    $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
            }
        }
            // 邮件发送
        $user_model = new UserModel();
        $user_obj = $user_model->get($buyer_id);
        if (! empty($user_obj)) {
            $email = $user_obj["user_email"];
            if (! empty($email) && $this->email_is_open == 1) {
                $template_obj = $this->getTemplateDetail($shop_id, "create_order", "email");
                if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                    $content = $template_obj["template_content"];
                    $content = str_replace("{商场名称}", $this->shop_name, $content);
                    $content = str_replace("{用户名称}", $user_name, $content);
                    $content = str_replace("{主订单号}", $order_no, $content);
                    $content = str_replace("{订单金额}", $order_money, $content);
                    $content = str_replace("{商品金额}", $goods_money, $content);
                    $result = emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                }
            }
        }
    }
    /**
     * 找回密码
     * @param string $params
     * @return multitype:number string
     */
    public function forgotPassword($params=null){
        $send_type=$params["send_type"];
        $send_param=$params["send_param"];
        $shop_id=$params["shop_id"];
        $this->getShopNotifyInfo($shop_id);
        $rand = rand(100000,999999);
        $template_obj=$this->getTemplateDetail($shop_id, "forgot_password", $send_type);
        if($send_type=="email"){
            #邮箱验证
            if($this->email_is_open==1){
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    #发送
                    $content=$template_obj["template_content"];
                    $content=str_replace("{验证码}", $rand, $content);
                    $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_addr, $send_param, $template_obj["template_title"], $content);
                    $this->result["param"]=$rand;
                    if($result){
                        $this->result["code"]=0;
                        $this->result["message"]="发送成功!";
                    }else{
                        $this->result["code"]=-1;
                        $this->result["message"]="发送失败!";
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="商家没有设置找回密码的模板!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="商家没开启邮箱验证!";
            }
        }else{
            #短信验证
            if($this->mobile_is_open==1){
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    #发送
                    $sms_params=array(
                    "number"=>$rand.""
                        );
                        $result=aliSmsSend($this->appKey, $this->secretKey,
                            $template_obj["sign_name"], json_encode($sms_params), $send_param, $template_obj["template_title"], $this->ali_use_type);
                        $result=$this->dealAliSmsResult($result);
                        $this->result["code"]=$result["code"];
                        $this->result["message"]=$result["msg"];
                        $this->result["param"]=$rand;
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="商家没有设置找回密码的短信模板!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="商家没开启短信验证!";
            }
        }
        return $this->result;
    }
    /**
     * 用户绑定手机号
     * @param string $params
     */
    public function bindMobile($params=null){
        $rand = rand(100000,999999);
        $mobile=$params["mobile"];
        $shop_id=$params["shop_id"];
        $user_id=$params["user_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "bind_mobile", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $user_model=new UserModel();
                $user_obj=$user_model->get($user_id);
                $sms_params=array(
                    "number"=>$rand."",
                    "user_name"=>$user_obj["user_name"]
                );
                $this->result["param"]=$rand;
                if(!empty($this->appKey) && !empty($this->secretKey) && !empty($template_obj["sign_name"]) && !empty($template_obj["template_title"])){
                    $result=aliSmsSend($this->appKey, $this->secretKey,
                                    $template_obj["sign_name"], json_encode($sms_params), $mobile, $template_obj["template_title"], $this->ali_use_type);
                    $result=$this->dealAliSmsResult($result);
                    $this->result["code"]=$result["code"];
                    $this->result["message"]=$result["message"];
                    $this->result["param"]=$rand;
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="短信配置信息有误!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="短信通知模板有误!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启短信验证";
        }
        return $this->result;
    }
    /**
     * 用户绑定邮箱
     * @param string $params
     */
    public function bindEmail($params=null){
        $rand = rand(100000,999999);
        $email=$params["email"];
        $shop_id=$params["shop_id"];
        $user_id=$params["user_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        if(!empty($email) && $this->email_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "bind_email", "email");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $user_model=new UserModel();
                $user_obj=$user_model->get($user_id);
                $content=$template_obj["template_content"];
                $content=str_replace("{验证码}", $rand, $content);
                $content=str_replace("{用户名称}", $user_obj["user_name"], $content);
                $this->result["param"]=$rand;
                if(!empty($this->email_host) && !empty($this->email_id) && !empty($this->email_pass) && !empty($this->email_addr)){
                    $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_addr, $email, $template_obj["template_title"], $content);
                    if($result){
                        $this->result["code"]=0;
                        $this->result["message"]="发送成功!";
                    }else{
                        $this->result["code"]=-1;
                        $this->result["message"]="发送失败!";
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="邮箱配置信息有误!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="邮箱通知模板有误!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启邮箱验证";
        }
        return $this->result;
    }
}

?>