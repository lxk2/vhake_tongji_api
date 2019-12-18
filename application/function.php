<?php
// +----------------------------------------------------------------------
// | Vhake框架 [ VhakeAdmin ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 深圳市威骇客网络科技有限公司 [ http://www.vhake.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://VhakeAdmin.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

// 为方便系统核心升级，二次开发中需要用到的公共函数请写在这个文件，不要去修改common.php文件

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); //支持的http 动作
header('Access-Control-Allow-Headers:x-requested-with,content-type');  //响应头 请按照自己需求添加。

// 异常错误报错级别,
error_reporting(E_ERROR | E_PARSE);

define('APISUCCESS', 1);
define('APIERROR', 0);
define('BINDUSERINFO', 999);
define('APIERROR_AUTH', -1);


/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}

/**
 * PHP发送Json对象数据
 *
 * @param $url 请求url
 * @param $jsonStr 发送的json字符串
 * @return array
 */
function http_post_json($url, $jsonStr)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return json_decode($response, true);
}

function http_post_auth_json($url, $json)
{
    $json = json_encode($json, true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($json)
        )
    );

    $arr_header[] = "Content-Type:application/json";
    $arr_header[] = "Authorization: Basic " . base64_encode("8e9c4c7368761:Mjg0MTMyMjE3NTE0NzczNTYzMjYzNjQ1MDg1NjgzNTQ4MTG"); //添加头，在name和pass处填写对应账号密码
    curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return json_decode($response, true);
}


/**
 * 获取随机数
 * @param  [type]  $len   [description]
 * @param  boolean $htime [description]
 * @return [type]         [description]
 */
function vk_random_number_card($len, $htime = false)
{
    $chars = array(
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    if ($htime) return date('YmdH', time()) . $output;
    else return $output;
}

/**
 * 发送短信
 * @param $mobile
 * @param $msg
 * @return bool
 */
function vk_send_sms($mobile, $msg)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'api:key-8038fe9d1e3f6f5710c6df1205c6e5aa');

    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile, 'message' => $msg . '【VCloudShop】'));

    $res = curl_exec($ch);
    curl_close($ch);
    //$res  = curl_error( $ch );
    $data = json_decode($res, true);
    return $data;
}

/**
 * 生成订单
 * @return string
 */
function createOrderNm()
{
    $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $date_code = array('0',
        '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A',
        'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'T', 'U', 'V', 'W', 'X', 'Y');
    //一共15位订单号,同一秒内重复概率1/10000000,26年一次的循环\
    $order_sn = $year_code[(intval(date('Y')) - 2010) % 26] . //年 1位
        strtoupper(dechex(date('m'))) . //月(16进制) 1位
        $date_code[intval(date('d'))] . //日 1位
        substr(time(), -5) . substr(microtime(), 2, 5) . //秒 5位 // 微秒 5位
        sprintf('%02d', rand(0, 99)); //  随机数 2位
    return $order_sn;
}

/**
 * 输出xml字符
 * @throws WxPayException
 **/
function toXml($data)
{
    if (!is_array($data)
        || count($data) <= 0
    ) {
        throw new WxPayException("数组数据异常！");
    }

    $xml = "<xml>";
    foreach ($data as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
    }
    $xml .= "</xml>";
    return $xml;
}

/**
 * 将xml转为array
 * @param string $xml
 * @throws WxPayException
 */
function fromXml($xml)
{
    //将XML转为array
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $data;
}

/**
 * 获取随即红包
 * @param int $total
 * @param int $count
 * @param int $type
 * @return array
 */
function sendRandBonus($total = 0, $count = 3, $type = 1)
{
    if ($type == 1) {
        $input = range(0.01, $total, 0.01);
        if ($count > 1) {
            $rand_keys = (array)array_rand($input, $count - 1);
            $last = 0;
            foreach ($rand_keys as $i => $key) {
                $current = $input[$key] - $last;
                $items[] = $current;
                $last = $input[$key];
            }
        }
        $items[] = $total - array_sum($items);
    } else {
        $avg = number_format($total / $count, 2);
        $i = 0;
        while ($i < $count) {
            $items[] = $i < $count - 1 ? $avg : ($total - array_sum($items));
            $i++;
        }
    }
    return $items;
}

/**
 * VK密码加密方法
 * @param string $pw 要加密的字符串
 * @return string
 */
function vk_password($pw, $authcode = '')
{
    if (empty($authcode)) {
        $authcode = config("authcode");
    }
    $result = "###" . md5(md5($authcode . $pw));
    return $result;
}

/**
 * VK密码加密方法 (X2.0.0以前的方法)
 * @param string $pw 要加密的字符串
 * @return string
 */
function vk_password_old($pw)
{
    $decor = md5(config('authcode'));
    $mi = md5($pw);
    return substr($decor, 0, 12) . $mi . substr($decor, -4, 4);
}


/**
 * VK密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $password_in_db 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function vk_compare_password($password, $password_in_db)
{
    if (strpos($password_in_db, "###") === 0) {
        return vk_password($password) == $password_in_db;
    } else {
        return vk_password_old($password) == $password_in_db;
    }
}

function vk_send_mail($email, $title, $content, $file = null)
{
    try {
        $mail = new \PHPMailer(true); //实例化
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host = config('MAIL_HOST'); //smtp服务器的名称
        $mail->SMTPAuth = config('MAIL_SMTPAUTH'); //启用smtp认证
        $mail->Username = config('MAIL_USERNAME'); //你的邮箱名
        $mail->Password = config('MAIL_PASSWORD'); //邮箱密码
        $mail->From = config('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
        $mail->FromName = config('MAIL_FROMNAME'); //发件人姓名
        $mail->AddAddress($email, "尊敬的客户");
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML(config('MAIL_ISHTML')); // 是否HTML格式邮件
        $mail->CharSet = config('MAIL_CHARSET'); //设置邮件编码
        $mail->Subject = $title; //邮件主题
        $mail->Body = $content; //邮件内容
        if ($file)
            $mail->AddAttachment($file); // attachment 附件
        //$mail->AltBody = ""; //邮件正文不支持HTML的备用显示
        return $mail->Send();
    } catch (phpmailerException $e) {
        return $e->errorMessage();
    }
}

/**
 * 获取老黄历内容
 */
function getAlmanacName($datetime, $isStr = false)
{
    $model = new app\common\model\SettingAlmanac();
    $obj = $model->where([
        'begin_time' => ['< time', $datetime],
        'end_time' => ['> time', $datetime],
    ])->find();
    if ($obj) {
        if (!$isStr)
            return [
                'year' => $obj['year'],
                'month' => $obj['month']
            ];
        else
            return $obj['year'] . $obj['month'];
    } else {
        if (!$isStr)
            return [
                'year' => '',
                'month' => ''
            ];
        else
            return '';
    }
}

/**
 * 字符串转换姓
 */
function strGetLastName($str, $is_leh = false)
{
    $fuxing = '安陵 安平 安期 安阳 白马 百里 柏侯 鲍俎 北宫 北郭 北门 北山 北唐 奔水 逼阳 宾牟 薄奚 薄野 曹牟 曹丘 常涛 长鱼 车非 成功 成阳 乘马 叱卢 丑门 樗里 穿封 淳子 答禄 达勃 达步 达奚 淡台 邓陵 第五 地连 地伦 东方 东里 东南 东宫 东门 东乡 东丹 东郭 东陵 东关 东闾 东阳 东野 东莱 豆卢 斗于 都尉 独孤 端木 段干 多子 尔朱 方雷 丰将 封人 封父 夫蒙 夫馀 浮丘 傅余 干已 高车 高陵 高堂 高阳 高辛 皋落 哥舒 盖楼 庚桑 梗阳 宫孙 公羊 公良 公孙 公罔 公西 公冶 公敛 公梁 公输 公上 公山 公户 公玉 公仪 公仲 公坚 公伯 公祖 公乘 公晰 公族 姑布 古口 古龙 古孙 谷梁 谷浑 瓜田 关龙 鲑阳 归海 函治 韩馀 罕井 浩生 浩星 纥骨 纥奚 纥于 贺拨 贺兰 贺楼 赫连 黑齿 黑肱 侯冈 呼延 壶丘 呼衍 斛律 胡非 胡母 胡毋 皇甫 皇父 兀官 吉白 即墨 季瓜 季连 季孙 茄众 蒋丘 金齿 晋楚 京城 泾阳 九百 九方 睢鸠 沮渠 巨母 勘阻 渴侯 渴单 可汗 空桐 空相 昆吾 老阳 乐羊 荔菲 栎阳 梁丘 梁由 梁馀 梁垣 陵阳 伶舟 冷沦 令狐 刘王 柳下 龙丘 卢妃 卢蒲 鲁步 陆费 角里 闾丘 马矢 麦丘 茅夷 弥牟 密革 密茅 墨夷 墨台 万俊 昌顿 慕容 木门 木易 南宫 南郭 南门 南荣 欧侯 欧阳 逄门 盆成 彭祖 平陵 平宁 破丑 仆固 濮阳 漆雕 奇介 綦母 綦毋 綦连 祁连 乞伏 绮里 千代 千乘 勤宿 青阳 丘丽 丘陵 屈侯 屈突 屈男 屈卢 屈同 屈门 屈引 壤四 扰龙 容成 汝嫣 萨孤 三饭 三闾 三州 桑丘 商瞿 上官 尚方 少师 少施 少室 少叔 少正 社南 社北 申屠 申徒 沈犹 胜屠 石作 石牛 侍其 士季 士弱 士孙 士贞 叔孙 叔先 叔促 水丘 司城 司空 司寇 司鸿 司马 司徒 司士 似和 素和 夙沙 孙阳 索阳 索卢 沓卢 太史 太叔 太阳 澹台 唐山 堂溪 陶丘 同蹄 统奚 秃发 涂钦 吐火 吐贺 吐万 吐罗 吐门 吐难 吐缶 吐浑 吐奚 吐和 屯浑 脱脱 拓拨 完颜 王孙 王官 王人 微生 尾勺 温孤 温稽 闻人 屋户 巫马 吾丘 无庸 无钩 五鹿 息夫 西陵 西乞 西钥 西乡 西门 西周 西郭 西方 西野 西宫 戏阳 瑕吕 霞露 夏侯 鲜虞 鲜于 鲜阳 咸丘 相里 解枇 谢丘 新垣 辛垣 信都 信平 修鱼 徐吾 宣于 轩辕 轩丘 阏氏 延陵 罔法 铅陵 羊角 耶律 叶阳 伊祁 伊耆 猗卢 义渠 邑由 因孙 银齿 尹文 雍门 游水 由吾 右师 宥连 於陵 虞丘 盂丘 宇文 尉迟 乐羊 乐正 运奄 运期 宰父 辗迟 湛卢 章仇 仉督 长孙 长儿 真鄂 正令 执头 中央 中长 中行 中野 中英 中梁 中垒 钟离 钟吾 终黎 终葵 仲孙 仲长 周阳 周氏 周生 朱阳 诸葛 主父 颛孙 颛顼 訾辱 淄丘 子言 子人 子服 子家 子桑 子叔 子车 子阳 宗伯 宗正 宗政 尊卢 昨和 左人 左丘 左师 左行 刘文 额尔 达力 蔡斯 浩赏 斛斯 夹谷 揭阳 ';
    $fuxing_arr = explode(" ", $fuxing);
    $sub_str = mb_substr($str, 0, 2, 'utf-8');
    if (in_array($sub_str, $fuxing_arr)) {
        if ($is_leh)
            return '堂上' . $sub_str . '姓歴代祖先神主位';
        else
            return $sub_str;
    } else {
        if ($is_leh)
            return '堂上' . mb_substr($str, 0, 1, 'utf-8') . '姓歴代祖先之神主位';
        else
            return mb_substr($str, 0, 1, 'utf-8');
    }
}


function uploadNativeQiniu()
{

    // 需要填写你的 Access Key 和 Secret Key
    $accessKey = 'rCdFT1tACyHNjgELAE-AGEdOpywb1xakSD0xWe41';
    $secretKey = 'UhPJ-lSM-mLu9SPozqrbxM1dzKlB2S_iNKrCdm_6';
    $doman = "http://images.vhake.com/";
    // 构建鉴权对象
    $auth = new \Qiniu\Auth($accessKey, $secretKey);
    // 要上传的空间
    $bucket = 'www-vhake';
    // 生成上传 Token
    $token = $auth->uploadToken($bucket);
    $name = $_FILES['uploadkey1']['name'];
    // 要上传文件的本地路径
    $filePath = $_FILES['uploadkey1']['tmp_name'];
    // 上传到七牛后保存的文件名
    $type = $_FILES['uploadkey1']['type'];
    // 初始化 UploadManager 对象并进行文件的上传
    $uploadMgr = new \Qiniu\Storage\UploadManager();
    // 调用 UploadManager 的 putFile 方法进行文件的上传
    list($ret, $err) = $uploadMgr->putFile($token, null, $filePath, null, $type, false);
    if ($err !== null) {
        return false;
    } else {
        return $doman . $ret['key'];
    }
}

/**
 * 获取配置
 * @param $key
 */
function getConfig($key)
{
    $model = new app\common\model\Setting();
    $obj = $model->where([
        'config_key' => $key
    ])->find();
    if ($obj)
        return $obj['config_value'];
    else
        return '';
}


/**
 * 解析时间
 * @param $time
 * @return string
 */
function vk_format_date($time)
{
    $t = time() - $time;
    $f = array(
        '31536000' => '年',
        '2592000' => '个月',
        '604800' => '星期',
        '86400' => '天',
        '3600' => '小时',
        '60' => '分钟',
        '1' => '秒'
    );
    foreach ($f as $k => $v) {
        if (0 != $c = floor($t / (int)$k)) {
            return $c . $v . '前';
        }
    }
}

function vk_format_list_date(&$list, $keys = ['create_time', 'update_time'], $type = 0)
{
    foreach ($list as &$item) {
        foreach ($keys as $value) {
            switch ($type) {
                case 0 :
                    $item[$value] = date('Y-m-d h:i:s', $item[$value]);
                    break;
                case 1:
                    $item[$value] = vk_format_date($item[$value]);
            }
        }
    }
}

function vk_format_detail_date($data, $keys = ['create_time', 'update_time'], $type = 0)
{
    foreach ($keys as $value) {
        switch ($type) {
            case 0 :
                $data[$value] = date('Y-m-d h:i:s', $data[$value]);
                break;
            case 1:
                $data[$value] = vk_format_date($data[$value]);
        }
    }
}


function make_coupon_card()
{
    mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid = //chr(123)// "{"
        substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
    //.chr(125);// "}"
    return $uuid;

}


/**
 * 格式化列表时间
 * @param $list
 * @param array $field
 */
function listFormatTime(&$list, $field = ['create_time', 'update_time'], $format = 'Y-m-d h:i:s')
{
    foreach ($list as &$item) {
        foreach ($field as $key) {
            $item[$key] = date($format, $item[$key]);
        }
    }
}

/**
 * 格式化info时间
 * @param $list
 * @param array $field
 */
function itemFormatTime(&$info, $field = ['create_time', 'update_time'])
{
    foreach ($field as $key) {
        $info[$key] = date('Y-m-d h:i:s', $info[$key]);
    }
}

function listFormatPic(&$list, $field = ['logo'])
{
    foreach ($list as &$item) {
        foreach ($field as $key) {
            $item[$key] = get_file_path($item[$key]);
        }
    }
}

function itemFormatPic(&$info, $field = ['logo'])
{
    foreach ($field as $key) {
        $info[$key] = get_file_path($info[$key]);
    }
}


//把时间戳转换为几分钟或几小时前或几天前
function wordTime($time)
{
    $time = (int)substr($time, 0, 10);
    $int = time() - $time;
    $str = '';
    if ($int <= 30) {
        $str = sprintf('刚刚', $int);
    } elseif ($int < 60) {
        $str = sprintf('%d秒前', $int);
    } elseif ($int < 3600) {
        $str = sprintf('%d分钟前', floor($int / 60));
    } elseif ($int < 86400) {
        $str = sprintf('%d小时前', floor($int / 3600));
    } elseif ($int < 2592000) {
        $str = sprintf('%d天前', floor($int / 86400));
    } else {
        $str = date('Y-m-d H:i:s', $time);
    }
    return $str;
}

/*
* array unique_rand( int $min, int $max, int $num )
* 生成一定数量的不重复随机数
* $min 和 $max: 指定随机数的范围
* $num: 指定生成数量
*/
function unique_rand($min, $max, $num)
{
//初始化变量为0
    $count = 0;
//建一个新数组
    $return = array();
    while ($count < $num) {
//在一定范围内随机生成一个数放入数组中
        $return[] = mt_rand($min, $max);
//去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
        $return = array_flip(array_flip($return));
//将数组的数量存入变量count中
        $count = count($return);
    }
//为数组赋予新的键名
    shuffle($return);
    return $return;
}

/**
 * 还原成图片
 */
function getPic(&$data, $field)
{
    foreach ($data as &$item) {
        if (is_array($item)) {
            $arr = explode(',', $item[$field]);
            foreach ($arr as &$val) {
                $val = is_numeric($val) ? get_file_path($val) : $val;
            }
            $item[$field] = $arr;
        } else {
            $arr = explode(',', $data[$field]);
            foreach ($arr as &$val) {
                $val = is_numeric($val) ? get_file_path($val) : $val;
            }
            $data[$field] = $arr;
            break;
        }
    }
}

function getLevelTree($array, $pid = 0, $level = 0)
{

    //声明静态数组,避免递归调用时,多次声明导致数组覆盖
    static $list = [];
    foreach ($array as $key => $value) {
        //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
        if ($value['pid'] == $pid) {
            //父节点为根节点的节点,级别为0，也就是第一级
            $value['level'] = $level;
            //把数组放到list中
            $list[] = $value;
            //把这个节点从数组中移除,减少后续递归消耗
            unset($array[$key]);
            //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
            getLevelTree($array, $value['id'], $level + 1);

        }
    }
    return $list;
}

//GCJ-02(火星，高德) 坐标转换成 BD-09(百度) 坐标//@param gg_lon 火星经度//@param gg_lat 火星纬度
function bd_encrypt($gg_lon, $gg_lat)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $gg_lon;
    $y = $gg_lat;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $data['bd_lon'] = $z * cos($theta) + 0.0065;
    $data['bd_lat'] = $z * sin($theta) + 0.006;
    return $data;
}

//BD-09(百度) 坐标转换成  GCJ-02(火星，高德) 坐标//@param bd_lon 百度经度//@param bd_lat 百度纬度
function bd_decrypt($bd_lon, $bd_lat)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $bd_lon - 0.0065;
    $y = $bd_lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $data['gg_lon'] = $z * cos($theta);
    $data['gg_lat'] = $z * sin($theta);
    return $data;
}