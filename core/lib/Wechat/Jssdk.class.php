<?php

if (!defined('IN_Aii')) {
  exit();
}

/**
 * Class Jssdk
 * @author：jincon
 * 微信js分享等操作，类库
 */
class Jssdk {
  private $appId;
  private $appSecret;
  public $cachedir = ''; //缓存目录。
  public $url = ''; //URL地址。


  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();
    $url = $this->url ? $this->url : "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = file_exists($this->cachedir."jsapi_ticket.json")?json_decode(file_get_contents($this->cachedir."jsapi_ticket.json")):"";
    if (!$data || $data->expire_time < time()) {
      $accessToken = $this->getAccessToken();
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        $_t = array();
        $_t['expire_time'] = time() + 7000;
        $_t['jsapi_ticket'] = $ticket;
        $fp = fopen($this->cachedir."jsapi_ticket.json", "w");
        fwrite($fp, json_encode($_t));
        fclose($fp);
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = file_exists($this->cachedir."access_token.json")?json_decode(file_get_contents($this->cachedir."access_token.json")):"";
    if (!$data || $data->expire_time < time()) {
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $_t = array();
        $_t['expire_time'] = time() + 7000;
        $_t['access_token'] = $access_token;
        $fp = fopen($this->cachedir."access_token.json", "w");
        fwrite($fp, json_encode($_t));
        fclose($fp);
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  public function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);//严格校验
    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}