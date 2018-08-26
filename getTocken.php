<?php
class Wxsdk
{
    private $appId;
    private $appSecret;

    /*
     * 这里为威狮码的公众号的openid和appsecret,如果配置到其他的子商家会出现需要关注威狮码公众号,
     * 则需要获取数据库的vender表里面的openid和appsecret
     * */
    public function __construct($appId = '自己的appid', $appSecret = '自己的appSecret')
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }


    public function getSignPackage(Request $request)
    {
//接收到前端的转义url转义回来
        $url = $_POST;
        $durl = $url['url'];
        $durl = urldecode($durl);

        $jsapiTicket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$durl";


        $signature = sha1($string);


        $signPackage = [
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        ];
//        var_dump($signPackage);die;
        throw new SuccessMessage(['msg' => $signPackage]);
    }


    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("jssdk/jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            //定义传递的参数数组
            $params['type'] = 'jsapi';
            $params['access_token'] = $accessToken;
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $params['access_token'] . "&type=" . $params['type'] . "";
            $res = json_decode(curl_get($url, $params));
            $ticket = isset($res->ticket) ? $res->ticket : NULL;
            if ($ticket) {
                $res->expire_time = time() + 7000;
                $res->jsapi_ticket = $ticket;
                $fp = fopen("jssdk/jsapi_ticket.json", "w");
                fwrite($fp, json_encode($res));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }


    private function getAccessToken()
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("jssdk/access_token.json"));
        if ($data->expire_time < time()) {
            //定义传递的参数数组
            $params['grant_type'] = 'client_credential';
            $params['appid'] = $this->appId;
            $params['secret'] = $this->appSecret;
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=" . $params['grant_type'] . "&appid=" . $params['appid'] . "&secret=" . $params['secret'] . "";
            $res = json_decode(curl_post($url, $params));
            $access_token = isset($res->access_token) ? $res->access_token : NULL;
            if ($access_token) {
                $res->expire_time = time() + 7000;
                $res->access_token = $access_token;
                $fp = fopen("jssdk/access_token.json", "w");
                fwrite($fp, json_encode($res));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }