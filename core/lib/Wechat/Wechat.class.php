<?php

if (!defined('IN_Aii')) {
  exit();
}

/**
 * Class Wechat
 * @author：jincon
 * 微信操作类库
 */
class Wechat {

    private $appId;
    private $appSecret;
    public $cachedir = ''; //缓存目录。



    public function __construct($appId, $appSecret) {
      $this->appId = $appId;
      $this->appSecret = $appSecret;
    }


    //创建微信的字符串
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    //获取access_token
    public function getAccessToken() {
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


    /**
     * 获取临时二维码ticket
     *
     * @param $accessToken
     * @param int $day
     * @param string $code
     * @return mixed
     */
    public function getQrcodeTicket($accessToken,$day=30,$code='123'){

        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken;
        //请求参数
        /*
         * scene_id 字段：
         * 临时二维码时为32位非0整型，
         * 永久二维码时最大值为100000（目前参数只支持1--100000）
         * 临时二维码不支持，scene_str字段
         * */
        $data = array (
            //过期时间设置
            'expire_seconds' => $day*86400,
            'action_name' => 'QR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_id' => $code,
                ),
            ),
        );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url);
        curl_setopt ( $ch, CURLOPT_HEADER, false);
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ( $ch, CURLOPT_POST, true);
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec ( $ch );
        curl_close ( $ch );
        return $res;
    }


    /**
     * 获取永久二维码ticket
     *
     * @param $accessToken
     * @param string $code
     * @param int $isNum  是否是数字，如果是数字，最大1-100000，如果非可以是字符串。
     * @return mixed
     */
    public function getLimitQrcodeTicket($accessToken,$code='123',$isNum=0)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $accessToken;
        //请求参数
        $type = 'scene_str';
        if ($isNum && (intval($code) < 100001 && intval($code) > 0)){
            $type = 'scene_id';
        }
        $data = array(
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' => array(
                'scene' => array(
                    $type => $code,
                ),
            ),
        );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url);
        curl_setopt ( $ch, CURLOPT_HEADER, false);
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ( $ch, CURLOPT_POST, true);
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec ( $ch );
        curl_close ( $ch );
        return $res;
    }


    //显示临时二维码图片
    //$isShowImg = 1 会显示img标签图片
    public function showQrcode($ticket,$isShowImg = 0){
        if(!$ticket){
            return false;
        }
        $img = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode( $ticket );
        if($isShowImg){
            echo "<img src='".$img."'>";  //430px;
        }else{
            return $img;
        }
    }


    //上传图片文件获得获取mediaId（应该都只有图片了吧）
    public function getMediaId($token,$file,$timeout = 60){
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$token.'&type=image';

        if (version_compare(phpversion(), '5.4.0') >= 0){
            $data = array (
                'media' => new CURLFile(realpath($file))
            );
            //注意，php5.5  5.6 开始慢慢废弃 @ 会报错。
        }else{
            $data = array (
                'media' => '@'.realpath($file)
            );
        }

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url);
        curl_setopt ( $ch, CURLOPT_HEADER, false);
        curl_setopt ( $ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ( $ch, CURLOPT_POST, true);
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec ( $ch );
        curl_close ( $ch );
        $resJson = json_decode($res,TRUE);
        if(isset($resJson['errcode'])){
            return $resJson['errmsg'];
        }else{
            return $resJson['media_id'];
        }
    }


    //获取用户信息（UnionID机制）
    public function getUserInfoByOpenid($token,$openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$openid."&lang=zh_CN";
        $res = json_decode($this->httpGet($url),1);
        return $res;
    }


    //curl get参数
    public function httpGet($url) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 500);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);//严格校验
      $res = curl_exec($curl);
      curl_close($curl);
      return $res;
    }
}