<?php
class wxPay {
    //配置参数信息
    const SHANGHUHAO = "";//商户号
    const PARTNERKEY = "";    //api，商户后台
    //核心支付函数,参数：请求地址和参数
    function pay($url,$obj) {
        $obj['nonce_str'] = $this->create_noncestr();    //创建随机字符串
        $stringA = $this->create_qianming($obj,false);    //创建签名
        $stringSignTemp = $stringA."&key=leFyTOXWzUEXxfp47WOu5xxxxxx";    //签名后加api
        $sign = strtoupper(md5($stringSignTemp));    //签名加密并大写
        $obj['sign'] = $sign;    //将签名传入数组
        $postXml = $this->arrayToXml($obj);    //将参数转为xml格式
        var_dump($postXml);    
        $responseXml = $this->curl_post_ssl($url,$postXml);    //提交请求
        var_dump($responseXml);
        return $responseXml;
    }
    
    //生成签名,参数：生成签名的参数和是否编码
    function create_qianming($arr,$urlencode) {
        $buff = "";
        ksort($arr); //对传进来的数组参数里面的内容按照字母顺序排序，a在前面，z在最后（字典序）
        foreach ($arr as $k=>$v) {
            if(null!=$v && "null" != $v && "sign" != $k) {    //签名不要转码
                if ($urlencode) {
                    $v = urlencode($v);
                }
                $buff.=$k."=".$v."&";
            }
        }
        if (strlen($buff)>0) {    
            $reqPar = substr($buff,0,strlen($buff)-1); //去掉末尾符号“&”
        }
        return $reqPar;
    }
    
    //生成随机字符串，默认32位
    function create_noncestr($length=32) {
        //创建随机字符
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for($i=0;$i<$length;$i++) {
            $str.=substr($chars, mt_rand(0,strlen($chars)-1),1);
        }
        return $str;    
    }
    //数组转xml
    function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key=>$val) {
            if (is_numeric($val)) {
                $xml.="<".$key.">".$val."</".$key.">";
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    
    //post请求网站，需要证书
    function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //cert 与 key 分别属于两个.pem文件
        //请确保您的libcurl版本是否支持双向认证，版本高于7.20.1
        curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).DIRECTORY_SEPARATOR.
                'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).DIRECTORY_SEPARATOR.
                'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_key.pem');
        curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).DIRECTORY_SEPARATOR.
                'zhengshu'.DIRECTORY_SEPARATOR.'rootca.pem');
        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}


//数组参数
$money = 100; //最低1元，单位分
$sender = "趣客";
$obj2 = array();
$obj2['wxappid'] = ""; //appid
$obj2['mch_id'] = "";　　//商户id
$obj2['mch_billno'] = "商户id".date('YmdHis').rand(1000,9999);　　//组合成28位，根据官方开发文档，可以自行设置
$obj2['client_ip'] = $_SERVER['REMOTE_ADDR'];
$obj2['re_openid'] = "";　　//接收红包openid
$obj2['total_amount'] = $money;
$obj2['min_value'] = $money;
$obj2['max_value'] = $money;
$obj2['total_num'] = 1;
$obj2['nick_name'] = $sender;
$obj2['send_name'] = $sender;
$obj2['wishing'] = "恭喜发财";
$obj2['act_name'] = $sender."红包";
$obj2['remark'] = $sender."红包";
$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
$wxpay = new wxPay();
$res = $wxpay->pay($url, $obj2);
var_dump($res);
