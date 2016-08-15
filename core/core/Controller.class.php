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
            if(count($path)>1){
                $module = $_path[0];
                $path = $_path[1];
            }
            $_path = explode('/',$path);
            if(count($path)>1){
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


}
