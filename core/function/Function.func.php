<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/5
 * Time: 下午11:05
 */

/**
 * 转义 ，支持数组。
 *
 * @param $value
 * @return array|null|string
 */
function addslashes_deep($value){
    return is_array($value) ? array_map('addslashes_deep', $value) : (isset($value) ? addslashes($value) : null);
}


/**
 * 反转义 ，支持数组。
 *
 * @param $value
 * @return array|null|string
 */
function stripslashes_deep($value){
    return is_array($value) ? array_map('stripslashes_deep', $value) : (isset($value) ? stripslashes($value) : null);
}


/**
 * 转换html实体 ，支持数组。
 *
 * @param $value
 * @return array|null|string
 */
function htmlspecialchars_deep($value){
    return is_array($value) ? array_map('htmlspecialchars_deep', $value) : (isset($value) ? htmlspecialchars($value) : null);
}


/**
 * 打印输出
 *
 * @param $data
 */
function d($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}


/**
 * @desc：获取url参数，支持post，get等，借鉴thinkphp
 *
 * @param $name
 * @param：$default  默认值
 * @param：$filter 参数过滤方法，如果不想过滤，可以。
 * @return mixed
 */
function I($name,$default='',$filter=null){

    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :
            $input =& $_GET;
            break;
        case 'post'    :
            $input =& $_POST;
            break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'request' :
            $input =& $_REQUEST;
            break;
        case 'session' :
            $input =& $_SESSION;
            break;
        case 'cookie'  :
            $input =& $_COOKIE;
            break;
        case 'server'  :
            $input =& $_SERVER;
            break;
        case 'globals' :
            $input =& $GLOBALS;
            break;
        default:
            return null;
    }

    if(''==$name) { // 获取全部变量
        $data       =   $input;
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            if(is_string($filters)){
                if(0 === strpos($filters,'/')){
                    if(1 !== preg_match($filters,(string)$data)){
                        // 支持正则验证
                        return   isset($default) ? $default : null;
                    }
                }else{
                    $filters    =   explode(',',$filters);
                }
            }elseif(is_int($filters)){
                $filters    =   array($filters);
            }

            if(is_array($filters)){
                foreach($filters as $filter){
                    if(function_exists($filter)) {
                        $data   =   is_array($data) ? array_map_recursive($filter,$data) : $filter($data); // 参数过滤
                    }else{
                        $data   =   filter_var($data,is_int($filter) ? $filter : filter_id($filter));
                        if(false === $data) {
                            return   isset($default) ? $default : null;
                        }
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:null;
    }
    return $data;
}
function array_map_recursive($filter, $data) {
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}


/**
 * @desc：重组URL
 * @param：
 * @param $url
 * @param $param
 */
function U($url, $param=array()){
    if(!$url) return '';
    $url_model = Config::get('URL_MODEL');
    if(in_array($url_model,array(1,2))){
        $self = $_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : '';
        if($url_model == 2) {
            $selfArr = explode('/',$self);
            array_pop($selfArr);
            $self = implode('/',$selfArr);
        }
        $module = Aii::$module;
        $_m = '';
        if($module != Config::get('DEFAULT_MODULE')){
            $_m = $module.'/';
        }
        $_tmp = '';
        if($param){
            foreach($param as $k=>$v){
                $_tmp .= $k.'/'.$v.'/';
            }
        }else{
            //如果没有参数，同时url不存在 / 分隔，自动补上默认动作名称
            $url = count(explode('/',$url))>1 ? $url : $url."/".Aii::$action ;
        }

        $url = substr($url."/".$_tmp,0,-1);

        //反向生成自定义路由
        if(C('URL_ROUTER_ON') && C('URL_ROUTE_RULES')){
            foreach(C('URL_ROUTE_RULES') as $_k=>$_v){
                  $_k =  str_replace(array('(:num)','(:any)'),array('(\d+)','(\w+)'),$_k);
                  $_v =  str_replace(array('/'),array('\/'),$_v);

                  preg_match_all('/\(.+?\)/',$_k,$res);
                  preg_match_all('/\$\{\d{1}\}/',$_v,$res2);
                  if($res && $res2){
                      $_k = url_split_replace($_k);  //自定义url
                      $_v = str_replace($res2[0],$res[0],$_v);  //系统url
                      if(preg_match('/'.$_v.'$/',$url)){
                          $url = preg_replace('/'.$_v.'$/',$_k,$url);
                          break; //只匹配依次
                      }
                  }
            }
        }

        //根据配置，返回是否带上后缀
        return $self.'/'.$_m.$url.(C('URL_HTML_SUFFIX')?".".C('URL_HTML_SUFFIX'):'');

    }else{
        Aii::halt('错误的URL模式哦，请选择正确的');
    }
}
function url_split_replace($str){ //用于路由拆解组装
    if(!$str){
        return '';
    }
    $arr = preg_split('/\(.*?\)/',$str);
    $result = '';
    foreach($arr as $key=>$val){
        if($key>0){
            $result .= '${'.$key.'}';
        }
        $result .= $val;
    }
    return $result;
}


/**
 * @desc：获取配置文件
 * @param：
 * @param $url
 * @param $param
 * @author：
 */
function C($name){
    if(!$name) return false;
    return Config::get($name);
}


/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename,$isreturn = 0) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            $_t = require $filename;
            $_importFiles[$filename] = $isreturn ? $_t : true;
            unset($_t);
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}


/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename) {
    if (is_file($filename)) {
        // if (IS_WIN && APP_DEBUG) {
        //     if (basename(realpath($filename)) != basename($filename))
        //         return false;
        // }
        return true;
    }
    return false;
}


/**
 * 过滤 xss
 * @param：
 * @param $val
 * @return mixed
 */
function removexss($val){
    return remove_xss($val);
}
function remove_xss($val) {
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=@avascript:alert('XSS')>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // @ @ search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
        // @ @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}


/**
 *取文件后缀
 *@param string $file
 *@return string
 */
function getExt($file) {
    return pathinfo($file, PATHINFO_EXTENSION);
}


/**
 * 判断是否是移动设备
 */
function is_mobile() {
    if (isset ($_SERVER['HTTP_VIA'])){
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array (
            'iphone','android',
            'sony',     'ericsson',       'mot',   'samsung',     'htc',       'sgh',     'lg',       'sharp',
            'sie-',      'philips', 'panasonic',   'alcatel',  'lenovo',    'iphone',   'ipod',  'blackberry',
            'meizu',     'android',  'netfront',   'symbian',   'ucweb', 'windowsce',   'palm',   'operamini',
            'operamobi','openwave',  'nexusone',      'cldc',    'midp',       'wap', 'mobile',       'nokia',
        );
        foreach ($clientkeywords as $v) {
            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),$v) !== false) {
                return true;
            }
        }
    }
    return false;
}

/**
 * 判断客户端是否是微信
 */
function is_weixin(){
    return strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"micromessenger") !== false ? true : false;
}


/**
 * 删除目录和文件。
 * @param  string $delPath 文件目录
 * @return bool
 */
function delDir($delPath, $redir = true) {
    if (is_dir($delPath)) {
        $filesRes    =  opendir($delPath);
        while (($file = readdir($filesRes)) !== false) {
            if ($file != "."&&$file != "..") {
                if (is_file($delPath."/".$file)) {
                    chmod($delPath."/".$file, 0777);
                    unlink($delPath."/".$file);
                } else {
                    delDir($delPath."/".$file);
                }
            }
        }
        closedir($filesRes);
        if ($redir == true) {
            return rmdir($delPath);
        }
    } else if (is_file($delPath)) {
        return unlink($delPath);
    } else {
        return false;
    }
}


/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_status($code) {
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}


/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}


/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}


/**
 * @param string $string 原文或者密文
 * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
 * @param string $key 密钥
 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 *
 * @example
 *
 * $a = authcode('abc', 'ENCODE', 'key');
 * $b = authcode($a, 'DECODE', 'key');  // $b(abc)
 *
 * $a = authcode('abc', 'ENCODE', 'key', 3600);
 * $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 3600) {

    $ckey_length = 4;
    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥


    $key = md5 ( $key ? $key : 'key' ); //这里可以填写默认key值
    $keya = md5 ( substr ( $key, 0, 16 ) );
    $keyb = md5 ( substr ( $key, 16, 16 ) );
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '';

    $cryptkey = $keya . md5 ( $keya . $keyc );
    $key_length = strlen ( $cryptkey );

    $string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
    $string_length = strlen ( $string );

    $result = '';
    $box = range ( 0, 255 );

    $rndkey = array ();
    for($i = 0; $i <= 255; $i ++) {
        $rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
    }

    for($j = $i = 0; $i < 256; $i ++) {
        $j = ($j + $box [$i] + $rndkey [$i]) % 256;
        $tmp = $box [$i];
        $box [$i] = $box [$j];
        $box [$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i ++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box [$a]) % 256;
        $tmp = $box [$a];
        $box [$a] = $box [$j];
        $box [$j] = $tmp;
        $result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
    }

    if ($operation == 'DECODE') {
        if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
            return substr ( $result, 26 );
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
    }
}


/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='root', $item='item', $attr='', $id='id', $encoding='utf-8') {
    if(is_array($attr)){
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr   = trim($attr);
    $attr   = empty($attr) ? '' : " {$attr}";
    $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml   .= "<{$root}{$attr}>";
    $xml   .= data_to_xml($data, $item, $id);
    $xml   .= "</{$root}>";
    return $xml;
}


/**
 * 数据XML编码
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key  = $item;
        }
        $xml    .=  "<{$key}{$attr}>";
        $xml    .=  (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml    .=  "</{$key}>";
    }
    return $xml;
}


