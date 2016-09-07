<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/6
 * Time: 上午12:50
 */
if (!defined('IN_Aii')) {
    exit();
}


class Controller{
    /**
     * @var string
     * 默认的模块名
     */
    public $module = 'home';

    /**
     * @var string
     * 控制器方法名称
     */
    public $control = 'index';

    /**
     * @var string
     * 动作方法名字
     */
    public $action = 'index';

    /**
     * 视图变量数组
     *
     * @var array
     */
    protected $_options = array();


    /**
     * 实例化
     *
     */
    function __construct(){
        $this->module = Aii::getModule();
        $this->control = Aii::getControl();
        $this->action = Aii::getAction();

    }


    /**
     * 废弃，统一通过 Aii::app['xxx/xxx']模式加载。
     * 加载 core/lib 下的核心库文件，支持实例化的时候，传递初始化参数。
     * 原则上不推荐使用，建议使用 Aii::app('xxxx')
     * @param $name
     * @param string $initArr
     * @return mixed
     * @throws newexception
     */
//    function lib($name,$initArr = ''){
//        self::require_cache(CORE_ROOT.'lib/'.$name.'.class.php');
//        //return new $name($initArr);
//        return Aii::singleton($name,$initArr);
//    }


    /**
     * 导入common目录 class 下的等文件，类是不进行实例化的。
     * function，已经系统自动加载了
     * @param $path
     * @param string $initArr
     * @return mixed
     * @throws newexception
     */
    function import($path){
        self::require_cache(APP.'common/class/'.$path.'.class.php');
    }


    /**
     * 加载 common目录 class 下的等文件，类会实例化。
     * common目录下function改为自动加载了
     * @param $path
     * @param string $initArr
     * @return mixed
     * @throws newexception
     */
    function load($path,$initArr = ''){
        $this->import($path);
        return Aii::singleton($path,$initArr);
    }

    /**
     * 视图变量赋值操作
     *
     * @access public
     *
     * @param mixed $keys 视图变量名
     * @param mixed $value 视图变量值
     *
     * @return mixed
     */
    public function assign($keys, $value = null) {

        //参数分析
        if (!$keys) {
            return false;
        }

        if (!is_array($keys)) {
            $this->_options[$keys] = $value;
            return true;
        }

        foreach ($keys as $handle=>$lines) {
            $this->_options[$handle] = $lines;
        }

        return true;
    }


    /**
     * @desc：显示模板 支持模式admin@home/test
     * @param：$path路径
     * @author：
     */
    function display($path=''){
        //模板变量赋值
        if ($this->_options) {
            extract($this->_options, EXTR_PREFIX_SAME, 'data');
            $this->_options = array();
        }
        if($path){
            $_path = explode('@',$path);
            if(count($_path)>1){
                $module = $_path[0];
                $path = $_path[1];
            }else{
                $module = $this->module;
            }
            $_path = explode('/',$path);
            if(count($_path)>1){
                $control = $_path[0];
                $action = $_path[1];
            }else{
                $control = $this->control;
                $action = $_path;
            }
        }else{
            $module = $this->module;
            $control = $this->control;
            $action = $this->action;
        }
        
        include (APP.$module.'/view/'.$control.'_'.$action.C('TMPL_TEMPLATE_SUFFIX'));
    }


    /**
     * 优化的require_once
     * @param string $filename 文件地址
     * @return boolean/mixed
     */
    public static function require_cache($filename,$isreturn = 0) {
        static $_importFiles = array();
        if (!isset($_importFiles[$filename])) {
            if (is_file($filename)) {
                $_t = require $filename;
                $_importFiles[$filename] = $isreturn ? $_t : true;
                unset($_t);
            } else {
                $_importFiles[$filename] = false;
                throw new newexception('不存在的文件路径：'.$filename);
            }
        }
        return $_importFiles[$filename];
    }


    /**
     * 加载模型model。
     *
     * @param string $model
     */
    public function model($model = ''){
        if(!$model){
            return new Model();
        }else{
            $_m = $model."Model";
            return new $_m();
        }
    }


    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data,$type='',$json_option=0) {
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                exit('-0');
        }
    }

    /**
     * 成功提示页面
     *
     * @param string $message   错误信息
     * @param string $jumpUrl   跳转地址
     * @param int $waitSecond   延时跳转的时间
     */
    protected function success($message='',$jumpUrl='',$waitSecond=3){
        //如果启用了模板
        $jumpUrl = isset($jumpUrl) && !empty($jumpUrl) ? $jumpUrl : (__HISTORY__?__HISTORY__:__ROOT__);
        $tplfile = CORE_ROOT.'view/message.php';
        if(C('TMPL_ACTION_SUCCESS')){
            $tpl = str_replace(":","_",C('TMPL_ACTION_SUCCESS'));
            $tpl = APP.$this->module.'/view/'.$tpl.C('TMPL_TEMPLATE_SUFFIX');
            if(file_exists($tpl)){
                $tplfile =  $tpl;
            }
        }
        include($tplfile);
        exit;
    }

    /**
     * 失败提示页面
     *
     * @param string $error    错误信息
     * @param string $jumpUrl  调整地址
     * @param int $waitSecond  延时跳转的时间
     */
    protected function error($error='',$jumpUrl='',$waitSecond=3){
        //如果启用了模板
        $jumpUrl = isset($jumpUrl) && !empty($jumpUrl) ? $jumpUrl : (__HISTORY__?__HISTORY__:__ROOT__);
        $tplfile = CORE_ROOT.'view/message.php';
        if(C('TMPL_ACTION_ERROR')){
            $tpl = str_replace(":","_",C('TMPL_ACTION_ERROR'));
            $tpl = APP.$this->module.'/view/'.$tpl.C('TMPL_TEMPLATE_SUFFIX');
            if(file_exists($tpl)){
                $tplfile =  $tpl;
            }
        }
        include($tplfile);
        exit;
    }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

}
