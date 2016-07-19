<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/8
 * Time: 下午12:16
 */

if (!defined('IN_Aii')) {
    exit();
}


class Model{

    protected static $_instance = null;
    public $db = null;
    public $table;

    /**
     * 主数据库实例化对象
     *
     * @var object
     */
    protected $_master = null;

    /**
     * 从数据库实例化对象
     *
     * @var object
     */
    protected $_slave = null;

    /**
     * 数据库连接参数
     *
     * @var array
     */
    protected $_config = array();

    /**
     * 数据库实例化是否为单库模式，即一个数据库
     *
     * @var boolean
     */
    protected $_singleton = false;

    /**
     * 单例模式实例化当前模型类
     *
     * @access public
     * @return object
     */
//    public static function init($table='') {
//        if (self::$_instance === null) {
//            self::$_instance = new self($table);
//        }
//        return self::$_instance;
//
//    }

    /**
     * 构造函数。单利模式。
     *
     * @param $table
     * @return object
     */
    public function __construct($table=''){

        //分析数据库连接参数
        $this->_config = $this->_parseConfig();

        if($table) {
            $this->table = $this->_prefix.$table; //加入表前缀
        }

        return true;
    }

    /**
     * 回调类方法：自定义当前模型（Model）的数据库连接参数
     *
     * @access protected
     * @return array
     */
    protected function getConfig() {
        return Config::get('DB_MASTER_SLAVE');
    }

    /**
     * 分析配置文件中数据库连接的相关内容
     *
     * 对数据库配置文件进行分析,以明确主从分离信息
     *
     * @access protected
     * @return array
     */
    protected function _parseConfig() {

        //获取数据库连接参数信息
        $params = $this->getConfig();

        if (!$params || !is_array($params)) {
            Aii::halt('The config data of database connect is not correct!');
        }

        //获取数据表前缀，默认为空
        $this->_prefix     = (isset($params['prefix']) && $params['prefix']) ? trim($params['prefix']) : '';

        //分析默认参数，默认编码为:utf-8
        $params['charset'] = (isset($params['charset']) && $params['charset']) ? trim($params['charset']) : 'utf8';

        //分析主数据库连接参数
        $configParam                          = array();
        if (isset($params['master']) && $params['master']) {
            $configParam['master']            = $params['master'];
            $configParam['master']['charset'] = $params['charset'];
        } else {
            $configParam['master']            = $params;
        }

        //分析从数据库连接参数
        if (isset($params['slave']) && $params['slave']) {
            //当从数据库只有一组数据时(Only One)。
            if (isset($params['slave']['dsn'])) {
                $configParam['slave'] = $params['slave'];
            } else {
                //当从数据库有多组时，随机选择一组进行连接
                $randIndex            = array_rand($params['slave']);
                $configParam['slave'] = $params['slave'][$randIndex];

            }
            $configParam['slave']['charset'] = $params['charset'];
        } else {
            $this->_singleton     = true;  //单库，非主从。
            $configParam['slave'] = $configParam['master'];
        }

        //将数据库的用户名及密码及时从内存中注销，提高程序性能
        unset($params);

        return $configParam;
    }

    /**
     * 实例化主数据库(Master MySQL Adapter)
     *
     * @access protected
     * @return object
     */
    protected function _master() {

        if ($this->_master) {
            return $this->_master;
        }

        //$this->_master = new Db($this->_config['master']);
        //改为单例。
        $this->_master = Db::getInstance($this->_config['master']);

        if ($this->_singleton) {
            $this->_slave = $this->_master;
        }

        return $this->_master;
    }


    /**
     * 获取当前数据库连接的实例化对象，默认返回从库，如果没有从库直接返回主库
     *
     * 使用本函数(类方法），可以实现对原生PDO所提供的函数的调用。
     *
     * @access public
     *
     * @param boolean $adapter 是否为主数据库。true：主数据库/false：从数据库
     *
     * @return object
     */
    public function getDB($adapter = false) {
        if (!$adapter) {
            return $this->_slave();
        }
        return $this->_master();
    }

    /**
     * 连接其他DB数据服务器。
     *
     * @param string $dbconfig
     * @return bool|object
     */
    public function getConnect($dbconfig = ''){
        if(!$dbconfig){
            return false;
        }
        $config = $this->getConfig();
        if(!$config[$dbconfig]){
            return false;
        }
        return Db::getInstance($config[$dbconfig]);
    }


    /**
     * 实例化从数据库(Slave Adapter)
     *
     * @access public
     * @return object
     */
    public function _slave() {

        if ($this->_slave) {
            return $this->_slave;
        }

        //$this->_slave = new Db($this->_config['slave']);
        $this->_slave = Db::getInstance($this->_config['slave']);

        if ($this->_singleton) {
            $this->_master = $this->_slave;
        }

        return $this->_slave;
    }


    /**
     * 获取表名。
     *
     * @return string
     */
    public function getTable(){
        return $this->table;
    }


    /**
     * 设置表名
     *
     * @param $table
     * @return bool
     */
    public function setTable($table){
        if($table) {
            $this->table = $this->_prefix.$table; //加入表前缀
            return true;
        }else{
            return false;
        }
    }


    /**
     * 执行SQL语句
     *
     * 注：本方法用于无需返回信息的操作。如：更改、删除、添加数据信息(即：用于执行非查询SQL语句)
     *
     * @access public
     *
     * @param string $sql 所要执行的SQL语句
     * @param array $params 待转义的数据。注：本参数支持字符串及数组，如果待转义的数据量在两个或两个以上请使用数组
     *
     * @return boolean
     */
    public function execute($sql, $params = null) {
        //参数分析
        if (!$sql) {
            return false;
        }
        //转义数据表前缀
        $sql = str_replace('#__', $this->_prefix, $sql);

        return $this->_master()->execute($sql, $params);
    }

    /**
     * 执行SQL语句
     *
     * 注：用于执行查询性的SQL语句（需要数据返回的情况）。
     *
     * @access public
     *
     * @param string $sql 所要执行的SQL语句
     * @param array $params 待转义的数据。注：本参数支持字符串及数组，如果待转义的数据量在两个或两个以上请使用数组
     *
     * @return boolean
     */
    public function query($sql, $params = null) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //转义数据表前缀
        $sql = str_replace('#__', $this->_prefix, $sql);

        return $this->_slave()->query($sql, $params);
    }

    /**
     * @desc：向数据库插入数组格式数据
     * @param：
     * @param $table
     * @param array $param
     */
    public function add($param=array()){
        if(empty($param) || !$this->table){
            return false;
        }
        return $this->_master()->insert($this->table,$param,1);
    }


    /**
     * @desc：返回插入的id
     * @param：
     * @return int
     */
    public function insertId(){
        return $this->_master()->lastInsertId();
    }


    /**
     * @desc：删除某一条数据
     * delete("id=?",array(3))
     *
     * @param：
     * @param $where
     * @param $array
     * @return bool
     */
    public function delete($where,$array){
        if(empty($array) || !$this->table){
            return false;
        }
        return $this->_master()->delete($this->table,$where,$array);
    }


    /**
     * @desc：更新数据
     * update(array('wechat_name'=>'888'),'id = ?',array(96));
     *
     * @param：
     * @param $data
     * @param $where
     * @param $array
     */
    function update($data ,$where, $array){
        if(empty($data) || empty($where) || empty($array) || !$this->table){
            return false;
        }
        $this->_master()->update($this->table,$data,$where,$array);
    }


    /**
     * @desc：替换
     * replace('test',array('id'=>'4','title'=>'title','url'=>'4444455555'));
     * @param：
     * @param $data
     */
    public function replace($data){
        if(empty($data) || !$this->table){
            return false;
        }
        return $this->_master()->replace($this->table,$data);
    }


    /**
     * 事务处理：开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        return $this->_master()->startTrans();
    }


    /**
     * 事务处理：提交事务处理
     *
     * @access public
     * @return boolean
     */
    public function commit() {

        return $this->_master()->commit();
    }


    /**
     * 事务处理：事务回滚
     *
     * @access public
     * @return boolean
     */
    public function rollback() {

        return $this->_master()->rollback();
    }


    /**
     *获取记录FETCH_ASSOC
     *
     * @param int $model
     * @return mixed
     */
    public function fetchRow($model = PDO::FETCH_ASSOC){
        return $this->_slave()->fetchRow($model);
    }


    /**
     *获取全部记录FETCH_ASSOC
     *
     * @param int $model
     * @return mixed
     */
    public function fetchAll($model = PDO::FETCH_ASSOC){
        return $this->_slave()->fetchAll($model);
    }


    /**
     * 获取错误。
     *
     * @return mixed
     */
    public function error(){
        return $this->_master()->lastError();
    }

    /**
     * SQL方式，获取一条记录
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function getOne($sql, $params = array()) {
        return $this->_slave()->getOne($sql, $params);
    }


    /**
     * SQL方式，获取全部记录
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function getAll($sql, $params = array()) {
        return $this->_slave()->getAll($sql, $params);
    }

    // 统计数量
//    function count($where=''){
//
//    }

    /* get($id) 取得一条数据 或
    *  get($postquery = '',$cur = 1,$psize = 30) 取得多条数据
    */
//    function get(){
//
//    }
//
//    function get_one($id){
//
//    }
//
//    function get_all($postquery = '',$cur = 1,$psize = 30) {
//
//    }

    /**
     * @desc：返回执行的sql，主从之后，就不能这么获取了。
     * @param：
     * @return string
     */
//    public function __string(){
//        return $this->db->getLastSql();
//    }


    public function __destruct() {

        $this->_master = null;

        $this->_slave  = null;
    }

}