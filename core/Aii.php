<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/5
 * Time: 下午10:51
 */

/*
 * core是核心mvc框架目录，Core是核心类库，
 * class：是自动自动加载的类库，注意这个目录下因为自动加载，所以是不能进行实例的时候进行初始化，要注意了。
 * core：系统核心类库，必须加载，并按照一定的顺序
 * function：是系统核心函数的文件目录
 * lib：属于扩展类库不会自动加载。可以在加载的时进行实例化传递参数。
 *
 * 其他app下的目录
 * common下有3个主要目录
 *      class：可以放系统核心类库，可以在加载的时进行实例化传递参数。
 *      function：函数库。
 *      config：配置文件，默认加载config.php
 *
 *
 *
    Aiiphp框架目录。结构

    |____app                App目录
    | |____admin            后台目录
    | | |____controller     后台控制器
    | | |____model          后台模型
    | | |____view           后台视图
    | |____common           通用文件
    | | |____class          项目通用类库
    | | |____config         项目配置文件
    | | |____function       项目通用函数库
    | |____home             前台目录
    | | |____controller     前台控制器
    | | |____model          前台模型
    | | |____view           前台视图
    |____core               系统框架目录
    | |____Aii.php          核心框架文件
    | |____class            自动加载的类库
    | |____Convention.php       系统配置模板
    | |____core             核心框架类库
    | | |____Config.class.php   配置获取类库
    | | |____Controller.class.php   父控制器类库
    | | |____Db.class.php       数据库类库
    | | |____Model.class.php    服模型类库
    | | |____Newexception.class.php 异常加载类库
    | |____function     核心框架函数库
    | | |____Function.func.php  核心函数库
    | |____lib              需要手动加载类库
    | |____view             系统的一些加载模板目录
    |____data               数据目录
    | |____cache            缓存目录
    |____index.php          首页文件
    |____static             静态资源目录。
    | |____css              CSS目录
    | |____font             字体目录
    | |____image            图片目录
    | |____js               JS目录。
 *
 *
 *
 */

define('VERSION','0.9.1');

// 记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);
// 记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();


define('CORE_ROOT',dirname(__FILE__).'/');

defined('DEBUG')  or define('DEBUG',false); // 是否调试模式

defined('IN_Aii')  or define('IN_Aii',true); // 是否调试模式

defined('DS') or define('DS', DIRECTORY_SEPARATOR);; // 是否调试模式


DEBUG or error_reporting(0); //屏蔽所有报错

define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

spl_autoload_register(array('Aii', 'autoload'));

class Aii {
    /**
     * @var string
     * 默认的模块名
     */
    public static $module = 'home';

    /**
     * @var string
     * 所有模块名，都要写，逗号分隔 home,admin
     */
    public static $modules = 'home';

    /**
     * @var string
     * 控制器方法名称
     */
    public static $control = 'home';

    /**
     * @var string
     * 动作方法名字
     */
    public static $action = 'index';

    /**
     * @var string
     * 0 c=home&a=index&.....
     * 1 home/index/xx/asd/  默认开启url样式，也暂时只开启pathinfo
     * 2 REWRITE模式，需要配置nginx，apache等。
     */
    public static $url_mode = '1';

    /**
     * 存储系统配置文件
     * @var
     */
    private static $_config;


    /**
     * 对象注册表
     *
     * @var array
     */
    private static $_objects = array();

    /**
     * APP对象注册表
     *
     * @var array
     */
    private static $_app;

    /**
     * 类库文件注册表
     *
     * @var array
     */
    private static $_lib;


    function __construct(){

    }

    /**
     * @desc：可以配置系统核心信息。
     * @param：
     * @param array $config
     * @author：
     */
    public static function config(){
        self::$_config = Config::get();
        self::$module       =       self::$_config['URL_CASE_INSENSITIVE'] ? strtolower(self::$_config['DEFAULT_MODULE']):self::$_config['DEFAULT_MODULE'];
        self::$modules      =       self::$_config['MODULE_ALLOW_LIST'];
        self::$control      =       self::$_config['URL_CASE_INSENSITIVE'] ? ucfirst(self::$_config['DEFAULT_CONTROLLER']):self::$_config['DEFAULT_CONTROLLER'];
        self::$action       =       self::$_config['DEFAULT_ACTION'];
        self::$url_mode     =       self::$_config['URL_MODEL'];
    }


    private static function init(){
        //自动核心类库加载，有顺序
        $coreLib = array(
            'Newexception',
            'Db',
            'Model',
            'Config',
            'Controller',
        );
        foreach ($coreLib as $value) {
            self::require_cache(CORE_ROOT.'core/'.$value.'.class.php');
        }

        //自动加载核心函数库
        $path = glob(CORE_ROOT.'function/*.func.php');
        foreach ($path as $value) {
            self::require_cache($value);
        }

        //end

        //配置初始化。
        self::config();

        // 定义系统一些常量
        define('HOST',    self::$_config['HOST']);
        define('BASE_URL',    self::$_config['BASE_URL']);
        define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
        define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
        define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
        define('IS_AJAX',       ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[self::$_config['VAR_AJAX_SUBMIT']]) || !empty($_GET[self::$_config['VAR_AJAX_SUBMIT']])) ? true : false);
        define('IS_WEIXIN', strpos( $_SERVER['HTTP_USER_AGENT'], 'MicroMessenger' ) !== FALSE );
        define('DATA_PATH',     self::$_config['DATA_PATH']);
        define('CACHE_PATH',    self::$_config['CACHE_PATH']);
        define('LOG_PATH',    self::$_config['LOG_PATH']);


        define('__STATIC__',    self::$_config['HOST'].self::$_config['STATIC_PATH']);
        define('__CSS__',       __STATIC__.'css/');
        define('__IMAGE__',     __STATIC__.'image/');
        define('__JS__',        __STATIC__.'js/');
        define('__HISTORY__', isset( $_SERVER["HTTP_REFERER"] ) ? $_SERVER["HTTP_REFERER"] : '' );
        //end

        // ============ init ============

        if(self::$_config['SESSION_AUTOSTART']){
            Session::start();
        }


        date_default_timezone_set(self::$_config['DEFAULT_TIMEZONE']);


        //set_error_handler(array("excep", "handleException"));
        //set_exception_handler(array("excep", "handleException"));

        // 系统信息
        if(version_compare(PHP_VERSION,'5.4.0','<')) {
            define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()? true : false);
        }else{
            define('MAGIC_QUOTES_GPC',false);
        }


    }


    /**
     * @desc：分析 拆解 URL参数
     * @param：
     * @author：
     */
    private static function uri(){
        /*
            url_mode = 0
            [QUERY_STRING] => a=1&b=2
            [REQUEST_URI] => /demo/mvc/index.php?a=1&b=2
            [SCRIPT_NAME] => /demo/mvc/index.php
            [PHP_SELF] => /demo/mvc/index.php
            url_mode = 1
            [QUERY_STRING] =>
            [REQUEST_URI] => /demo/mvc/index.php/home/index/a/1/b/2
            [SCRIPT_NAME] => /demo/mvc/index.php
            [PATH_INFO] => /home/index/a/1/b/2
            url_mode = 2
            [QUERY_STRING] =>
            [REQUEST_URI] => /demo/Aiiphp/index/test/a/1/b/
            [SCRIPT_NAME] => /demo/Aiiphp/index.php
            [PATH_INFO] => /home/index/test/a/1/b/
         * */
        if(in_array(self::$url_mode,array(1,2))){
            $uri = isset($_SERVER['PATH_INFO'])?trim($_SERVER['PATH_INFO'],'/'):'';
        }else{
            Aii::halt('错误的URL模式哦，请选择正确的');
        }

        if($uri){

            $uriArr = explode('/',$uri);
            $moduleArr = explode(',',self::$modules);

            if(in_array($uriArr[0],$moduleArr) && $uriArr[0] == self::$module){
                array_shift($uriArr);
            }elseif(in_array($uriArr[0],$moduleArr)){
                self::$module = self::$_config['URL_CASE_INSENSITIVE'] ? strtolower(array_shift($uriArr)):array_shift($uriArr);
            }

            if(isset($uriArr[0])){
                self::$control = self::$_config['URL_CASE_INSENSITIVE'] ? ucfirst($uriArr[0]):$uriArr[0];
                unset($uriArr[0]);
            }

            if(isset($uriArr[1])){
                self::$action = $uriArr[1];
                unset($uriArr[1]);
            }

            //分解参数到GET全局变量中
            foreach($uriArr as $k=>$v){
                if($k%2==0){
                    $_GET[$v] = '';
                }else{
                    $_GET[$uriArr[$k-1]] = $v;
                }
            }
        }

        /*
         * 注意：
         * 魔术函数的问题，争论已久。官方其实都觉得没有必要，包括php5.4已经取消了。
         * 处理sql注入攻击的问题，可以使用系统类库自带的pdo预编译功能，如果你非不这么干，那也没办法了。
         * web安全，是需要在编码的时候，主动有意识的避免此问题，特别是SQL这样的低级错误。
         * 为了适应新手，所以，默认开始过滤，你想关闭，可以通过系统配置文件的 FILTER_ON ，设置为 false。
         * */
        if(!MAGIC_QUOTES_GPC && self::$_config['FILTER_ON']){
            $_POST = self::addslashes_deep($_POST);
            $_GET = self::addslashes_deep($_GET);
            $_REQUEST = self::addslashes_deep($_REQUEST);
            $_COOKIE = self::addslashes_deep($_COOKIE);
            $_SERVER = self::filter_escape($_SERVER);
        }
    }


    /**
     * @desc：启动核心
     * @param：
     * @author：
     */
    public static function run(){
        //初始化
        self::init();

        //解析URL参数。
        self::uri();



//        $controlfile = APP.'/'.self::$module.'/controller/'.self::$control.'Controller.class.php';
//        if(file_exists($controlfile)){
//            self::require_cache($controlfile);
//        }


        if(!class_exists(self::$control.'Controller')){
            throw new Newexception('不存在的控制器：'.self::$control.'Controller');
        }
        $class = self::$control.'Controller';
        $obj = new $class();

        if(!method_exists(self::$control.'Controller',self::$action)){
            throw new Newexception('不存在的方法：'.self::$action);
        }

        $a = self::$action;
        $obj->$a();
    }


    /**
     * 获取模块名称
     *
     * @return string
     */
    public static function getModule(){
        return self::$module;
    }


    /**
     * 获取控制器名称
     *
     * @return string
     */
    public static function getControl(){
        return self::$control;
    }


    /**
     * 获取动作名称
     *
     * @return string
     */
    public static function getAction(){
        return self::$action;
    }


    /**
     * 安全过滤类-过滤javascript,css,iframes,object等不安全参数 过滤级别高
     * 使用方法：Aii::filter_script($value)
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filter_script($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::filter_script($v);
            }
            return $value;
        } else {
            $parten = array(
                "/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i",
                "/<script(.*?)>(.*?)<\/script>/si",
                "/<iframe(.*?)>(.*?)<\/iframe>/si",
                "/<object.+<\/object>/isU"
            );
            $replace = array("\\2", "", "", "");
            $value = preg_replace($parten, $replace, $value, -1, $count);
            if ($count > 0) {
                $value = self::filter_script($value);
            }
            return $value;
        }
    }


    /**
     * 安全过滤类-通用数据过滤
     *  Controller中使用方法：self::filter_escape($value)
     * @param string $value 需要过滤的变量
     * @return string|array
     */
    public static function filter_escape($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::filter_str($v);
            }
        } else {
            $value = self::filter_str($value);
        }
        return $value;
    }


    /**
     * 安全过滤类-字符串过滤 过滤特殊有危害字符
     *  Controller中使用方法：self::filter_str($value)
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filter_str($value) {
        $value = str_replace(array("\0","%00","\r"), '', $value);
        $value = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $value);
        $value = str_replace(array("%3C",'<'), '&lt;', $value);
        $value = str_replace(array("%3E",'>'), '&gt;', $value);
        $value = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $value);
        return $value;
    }


    /**
     * 安全过滤 addslashes。
     *
     * @param $value
     * @return array|null|string
     */
    public static function addslashes_deep($value){
        return is_array($value) ? array_map('addslashes_deep', $value) : (isset($value) ? addslashes($value) : null);
    }


    /**
     * 错误提示。
     *
     * @param：
     * @param string $mess
     */
    public static function error($mess=''){
        $mess = $mess?$mess:'系统发生错误，请检查';
        self::halt($mess);
    }


    /**
     * 显示404错误
     *
     * @param：
     */
    public static function show_404(){
        self::require_cache(CORE_ROOT.'view/404.php');
        die();
    }


    /**
     * 显示403错误
     *
     * @param：
     */
    public static function show_403(){
        self::require_cache(CORE_ROOT.'view/403.php');
        die();
    }


    /**
     * 显示halt停止页面
     *
     * @param：
     */
    public static function halt($message=''){
        $GLOBALS['message'] = $message;
        self::require_cache(CORE_ROOT.'view/error.php');
        die();
    }


    /**
     * @desc：自动加载，类库放在class里面，如果在lib，不会自动加载，需要手动加载。
     * @param：
     * @param string $type
     */
    public static function autoload($className){

        $path = glob(CORE_ROOT.'class/*.class.php');
        foreach ($path as $value) {
            self::require_cache($value);
        }

        $path = glob(APP.'common/function/*.func.php');
        foreach ($path as $value) {
            self::require_cache($value);
        }

        //加载控制器
        if (substr($className, -10) == 'Controller'){
            self::require_cache(APP.self::$module.'/controller/'.$className.'.class.php');

        //加载模型
        }elseif (substr($className, -5) == 'Model') {
            self::require_cache(APP.self::$module.'/model/'.$className.'.class.php');
        }

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
            }
        }
        return $_importFiles[$filename];
    }


    /**
     * 返回唯一的实例(单例模式)
     *
     * 程序开发中,model,module, widget, 或其它类在实例化的时候,将类名登记到doitPHP注册表数组($_objects)中,当程序再次实例化时,直接从注册表数组中返回所要的对象.
     * 若在注册表数组中没有查询到相关的实例化对象,则进行实例化,并将所实例化的对象登记在注册表数组中.此功能等同于类的单例模式.
     *
     * 注:本方法只支持实例化无须参数的类.如$object = new pagelist(); 不支持实例化含有参数的.
     * 如:$object = new pgelist($total_list, $page);
     *
     * <code>
     * $object = Aii::singleton('pagelist');
     * </code>
     *
     * @access public
     * @param string $className  要获取的对象的类名字
     * @return object 返回对象实例
     *
     * 注意，此单例加载仅仅对已经加载的文件目录下的类有效，如：class目录，core目录等
     */
    public static function singleton($className,$initArr='') {

        //参数分析
        if (!$className) {
            return false;
        }

        $className = trim($className);

        if (isset(self::$_objects[$className])) {
            return self::$_objects[$className];
        }

        return self::$_objects[$className] = new $className($initArr);
    }

    /**
     * Aii::App() 仅仅支持lib/  项目APP/common目录下的类库
     * 让他支持目录形式。注意，类名称不能出现相同的。
     * @param $name
     * @param string $initArr
     * @return mixed
     */
    public static function app($name,$initArr = ''){
        $t = explode('/',$name);
        $path = $name;
        if(count($t)>1){
            $name = array_pop($t);
        }
        unset($t);
        if(self::$_app[$name]) return self::$_app[$name];

        //优先核心lib目录
        $r = self::require_cache(CORE_ROOT.'lib/'.$path.'.class.php');
        if(!$r)
            $r = self::require_cache(APP.'common/class/'.$path.'.class.php');

        if($r){
            return self::$_app[$name] =  new $name($initArr);
        }else{
            throw new Newexception('类库'.$name.'无法找到');
        }
    }

    /**
     * Aii::library() 仅仅支持lib/ 支持目录加载
     * 类库，注意这个只加载，不是实例化
     * 让他支持目录形式。注意，类名称不能出现相同的。
     * @param $name
     * @param string $initArr
     * @return mixed
     */
    public static function library($name){
        $t = explode('/',$name);
        $path = $name;
        if(count($t)>1){
            $name = array_pop($t);
        }
        unset($t);
        if(self::$_lib[$name]) return self::$_lib[$name];

        //加载核心lib目录
        self::require_cache(CORE_ROOT.'lib/'.$path.'.class.php');
    }

}