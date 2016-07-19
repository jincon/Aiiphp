<?php
/**
 * 数据库驱动层
 *
 * 用于完成对mysql、oracle, sqllite, postgresql, mssql、firebird等数据库的操作
 *
 */

if (!defined('IN_Aii')) {
    exit();
}

class Db {

    /**
     * 单例模式实例化本类
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 事务处理开启状态
     *
     * @var boolean
     */
    protected $_transactions = false;

    /**
     * 数据库连接实例化对象名
     *
     * @var object
     */
    protected $_dbLink = null;

    /**
     * 执行SQL语句后的返回对象
     *
     * @var object
     */
    protected $_query = null;

    /**
     * 执行SQL语句，主要用于存储和输出。。
     *
     * @var object
     */
    protected $_sql = "no sql";

    /**
     * 数据库连接参数默认值
     *
     * @var array
     */
    protected $_defaultConfig = array(
        'persistency'    => false,
        'username'       => "root",
        'password'       => null,
        'charset'        => 'utf8',
    );
    /**
     * 单例模式
     *
     * 用于本类的单例模式(singleton)实例化
     *
     * @access public
     *
     * @param array $params 数据库连接参数,如数据库服务器名,用户名,密码等
     *
     * @return object
     */
    public static function getInstance($params = array()) {

        if (!self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }

    /**
     * 构造方法
     *
     * 用于初始化运行环境,或对基本变量进行赋值，只能使用单利模式，进行实例化。
     *
     * @access private
     *
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     *
     * @return boolean
     */
    private function __construct($params = array()) {

        //参数分析
        if (!$params['dsn']) {
            printf('数据库配置错误');
        }

        $params += $this->_defaultConfig;

        //数据库连接
        try {
            $flags = array(
                PDO::ATTR_PERSISTENT => $params['persistency'],
                PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION
            );

            //实例化数据库连接
            $this->_dbLink = new PDO($params['dsn'], $params['username'], $params['password'], $flags);

        } catch (PDOException $exception) {
            printf('数据库连接失败！'.$exception->getMessage());

        }

        //设置数据编码
        $driverName = $this->_dbLink->getAttribute(PDO::ATTR_DRIVER_NAME);
        switch ($driverName) {
            case 'mysql':
            case 'pgsql':
                $this->_dbLink->exec("SET NAMES {$params['charset']}");
                break;

            case 'sqlsrv':
                $this->_dbLink->setAttribute(PDO::SQLSRV_ATTR_ENCODING, $params['charset']);
                break;
        }

        return true;
    }

    /**
     * 获取数据库连接的实例化对象
     *
     * @access public
     *
     * @return object
     */
    public function getDbConnection() {

        return $this->_dbLink;
    }

    /**
     * 执行SQL语句，此方法会返回当前对象。
     *
     * 注：用于执行查询性的SQL语句（需要数据返回的情况）。
     *
     * @access public
     *
     * @param string $sql SQL语句
     * @param array $params 待转义的参数值
     *
     * @return mixed
     */
    public function query($sql, $params = array()) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //当待转义的参数值不为数组时
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        $result = $this->_execute($sql, $params);

        if (!$result) {
            $result->closeCursor();
            $this->_query = null;
            return $this;
        }

        $this->_query = $result;

        return $this;
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

        $this->_builtSql($sql,$params);

        //参数分析
        if (!$sql) {
            return false;
        }
        $sql = trim($sql);

        //当待转义的参数值不为数组时
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        try {
            //执行SQL语句
            $sth = $this->_dbLink->prepare($sql);
            if (!$params) {
                $result = $sth->execute();
            } else {
                $result = $sth->execute($params);
            }

            //分析执行结果
            if (!$result) {
                $sth->closeCursor();
                return false;
            }

            return true;

        } catch (PDOException $exception) {
            //抛出异常信息
            $this->throwException($exception, $sql, $params);
        }
    }

    /**
     * 获取一行查询信息
     *
     * @access public
     *
     * @param string $model 返回数据的索引类型：字段型/数据型 等。默认：字段型
     *
     * @return array
     */
    public function fetchRow($model = PDO::FETCH_ASSOC) {

        //参数分析
        if (!$model) {
            return false;
        }

        if (!$this->_query) {
            return false;
        }

        $myrow = $this->_query->fetch($model);
        $this->_query->closeCursor();

        //重值$this->_query
        $this->_query  = null;

        return $myrow;
    }

    /**
     * 获取全部查询信息
     *
     * @access public
     *
     * @param string $model 返回数据的索引类型：字段型/数据型 等。默认：字段型
     *
     * @return array
     */
    public function fetchAll($model = PDO::FETCH_ASSOC) {

        //参数分析
        if (!$model) {
            return false;
        }

        if (!$this->_query) {
            return false;
        }

        $myrow = $this->_query->fetchAll($model);
        $this->_query->closeCursor();

        //重值$this->_query
        $this->_query  = null;

        return $myrow;
    }

    /**
     * 分析组装所执行的SQL语句
     *
     * 用于prepare()与exexute()组合使用时，组装所执行的SQL语句
     *
     * @access protected
     *
     * @param string $sql SQL语句
     * @param array $params 参数值
     *
     * @return string
     */
    protected function _parseQuerySql($sql, $params = array()) {

        //参数分析
        if (!$sql) {
            return false;
        }
        $sql = trim($sql);

        //当所要转义的参数值为空时
        if (!$params) {
            return $sql;
        }

        //当所要转义的参数值不为数组时
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        $sql    = str_replace('?', '%s', $sql);
        $params = $this->escape($params);

        return vsprintf($sql, $params);
    }

    /**
     * 获取数据库错误描述信息
     *
     * @access public
     *
     * @param PDOStatement $query
     *
     * @return string
     *
     * @example
     * 例一：
     * $erronInfo = $this->lastError();
     *
     * 例二：
     * $sth = $this->_dbLink->prepare('select * from tablename');
     * $sth->execute();
     *
     * $erronInfo = $this->lastError($sth);
     *
     */
    public function lastError(PDOStatement $query = null) {

        $error = (!$query) ? $this->_dbLink->errorInfo() : $query->errorInfo();
        if (!$error[2]) {
            return null;
        }

        return $error[1] . ': ' . $error[2];
    }

    /**
     * 通过一个SQL语句获取一行信息(字段型)
     *
     * @access public
     *
     * @param string $sql SQL语句内容
     * @param array $params 待转义的参数值
     *
     * @return array
     */
    public function getOne($sql, $params = array()) {

        //参数分析
        if (!$sql) {
            return false;
        }
        //当待转义的参数值不为数组时
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        $result = $this->_execute($sql, $params);

        if (!$result) {
            $result->closeCursor();
            return false;
        }

        $myrow = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();

        return $myrow;
    }

    /**
     * 通过一个SQL语句获取全部信息(字段型)
     *
     * @access public
     *
     * @param string $sql SQL语句
     * @param array $params 待转义的参数值
     *
     * @return array
     */
    public function getAll($sql, $params = array()) {

        //参数分析
        if (!$sql) {
            return false;
        }
        //当待转义的参数值不为数组时
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        $result = $this->_execute($sql, $params);

        if (!$result) {
            $result->closeCursor();
            return false;
        }

        $myrow = $result->fetchAll(PDO::FETCH_ASSOC);
        $result->closeCursor();

        return $myrow;
    }

    /**
     * 获取执行SQL语句的返回结果
     *
     * @access public
     *
     * @param string $sql SQL语句内容
     * @param array $params 待转义的参数值
     *
     * @return object
     */
    protected function _execute($sql, $params = array()) {

        $this->_builtSql($sql,$params);

        //参数分析
        $sql = trim($sql);
        try {
            //执行SQL语句
            $sth = $this->_dbLink->prepare($sql);
            if (!$params) {
                $result = $sth->execute();
            } else {
                $result = $sth->execute($params);
            }

            //分析执行结果
            if (!$result) {
                $sth->closeCursor();
                return false;
            }

            return $sth;
        } catch (PDOException $exception) {

            //抛出异常信息
            $this->throwException($exception, $sql, $params);
        }
    }
    /**
     * 抛出异常提示信息处理
     *
     * 用于执行SQL语句时，程序出现异常时的异常信息抛出
     *
     * @access public
     * @return integer
     */
    public function throwException($exception, $sql, $params = array()) {

        //参数分析
        if (!is_object($exception) || !$sql) {
            return false;
        }
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        //获取所执行的SQL语句
        $sql = $this->_parseQuerySql($sql, $params);

        printf('错误信息：'.$exception->getMessage().'<br>');
        printf('SQL语句执行错误！%s',$sql);
    }

    /*
     * 组装sql语句，用于打印输出等
     * @private 私有
     * @param $sql
     * @param $params
     * */
    private function _builtSql($sql, $params = array()) {
        //参数分析
        if (!$sql) {
            return false;
        }
        if (!is_array($params)) {
            $params = func_get_args();
            array_shift($params);
        }

        //获取所执行的SQL语句
        $this->_sql = $this->_parseQuerySql($sql, $params);
    }

    /*
     * 输出对象的时候,输出sql语句
     *
     * @return string
     * */
    public function __toString() {
        return (string)$this->_sql;
    }

    /*
     * 输出执行的sql语句
     *
     * @return string
     * */
    public function getLastSql(){
        return (string)$this->_sql;
    }

    /**
     * 获取最新的insert_id
     * @return integer
     */
    public function lastInsertId() {

        return $this->_dbLink->lastInsertId();
    }
    /*
     * 复写
     * */
    public function getInsertId() {
        return $this->_dbLink->lastInsertId();
    }

    /**
     * 开启事务处理
     *
     * @access public
     *
     * @return boolean
     */
    public function startTrans() {
        if ($this->_transactions == false) {
            $this->_dbLink->beginTransaction();
            $this->_transactions = true;
        }
        return true;
    }

    /**
     * 提交事务处理
     *
     * @access public
     *
     * @return boolean
     */
    public function commit() {
        //当事务处理开启时
        if ($this->_transactions == true) {
            if ($this->_dbLink->commit()) {
                $this->_transactions = false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     *
     * @access public
     *
     * @return boolean
     */
    public function rollback() {
        //当事务处理开启时
        if ($this->_transactions == true) {
            if ($this->_dbLink->rollBack()) {
                $this->_transactions = false;
            }
        }
        return true;
    }

    /**
     * 对字符串进行转义,提高数据库操作安全
     *
     * @access public
     *
     * @param string $value 待转义的字符串内容
     *
     * @return string
     */
    public function escape($value = null) {
        //参数分析
        if (is_null($value)) {
            return null;
        }

        if (!is_array($value)) {
            return trim($this->_dbLink->quote($value));
        }

        //当参数为数组时
        return array_map(array($this, 'escape'), $value);
    }

    /**
     * 数据表写入操作
     *
     * @access public
     *
     * @param string $tableName 所要操作的数据表名称
     * @param array $data 所要写入的数据内容。注：数据必须为数组
     * @param boolean $returnId 是否返回数据为:last insert id
     *
     * @return mixed
     */
    public function insert($tableName, $data, $returnId = false) {

        //参数分析
        if(!$tableName || !$data || !is_array($data)) {
            return false;
        }

        //处理数据表字段与数据的对应关系
        $contentArray  = array_values($data);

        $fieldString   = implode(',', array_keys($data));
        $contentString = rtrim(str_repeat('?,', count($contentArray)), ',');

        //组装SQL语句
        $sql = "INSERT INTO {$tableName} ({$fieldString}) VALUES ({$contentString})";

        $reulst = $this->execute($sql, $contentArray);

        //清空不必要的内容占用
        unset($fieldString, $contentString, $contentString);

        //当返回数据需要返回insert id时
        if ($reulst && $returnId === true) {
            return $this->lastInsertId();
        }

        return $reulst;
    }

    /**
     * 数据表数据替换操作
     *
     * @access public
     *
     * @param string $tableName 所要操作的数据表名称
     * @param array $data 所要替换的数据内容。注：数据必须为数组
     *
     * @return mixed
     */
    public function replace($tableName, $data) {

        //参数分析
        if(!$tableName || !$data || !is_array($data)) {
            return false;
        }

        //处理数据表字段与数据的对应关系
        $contentArray  = array_values($data);

        $fieldString   = implode(',', array_keys($data));
        $contentString = rtrim(str_repeat('?,', count($contentArray)), ',');

        //组装SQL语句
        $sql = "REPLACE INTO {$tableName} ({$fieldString}) VALUES ({$contentString})";

        $reulst = $this->execute($sql, $contentArray);

        //清空不必要的内容占用
        unset($fieldString, $contentString, $contentString);

        return $reulst;
    }

    /**
     * 数据表更新操作
     *
     * @access public
     *
     * @param string $tableName 所要操作的数据表名称
     * @param array $data 所要更改的数据内容
     * @param string $where 更改数据所须的条件
     * @param array $value 待转义的参数值
     *
     * @return boolean
     */
    public function update($tableName, $data, $where = null, $value = array()) {

        //参数分析
        if(!$tableName || !$data || !is_array($data)) {
            return false;
        }

        $fieldArray    = array_keys($data);
        $contentString = implode('=?,', $fieldArray) . '=?';
        $params        = array_values($data);

        //分析SQL语句的条件
        if ($value) {
            if (!is_array($value)) {
                array_push($params, $value);
            } else {
                $params = array_merge($params, $value);
            }
        }

        //组装SQL语句
        $sql = "UPDATE {$tableName} SET {$contentString}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }

        $reulst = $this->execute($sql, $params);

        //清除不必要内存占用
        unset($fieldArray, $contentString, $params);

        return $reulst;
    }

    /**
     * 数据表删除操作
     *
     * @access public
     *
     * @param string $tableName 所要操作的数据表名称
     * @param string $where 删除数据所需的SQL条件
     * @param array $value 待转义的参数值
     *
     * @return boolean
     */
    public function delete($tableName, $where = null, $value = array()) {

        //参数分析
        if(!$tableName) {
            return false;
        }
        if ($value && !is_array($value)) {
            $value = array($value);
        }

        //组装SQL语句
        $sql = "DELETE FROM {$tableName}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }

        return $this->execute($sql, $value);
    }

    /**
     * 根据数据表名获取该数据表的字段信息
     *
     * @access public
     *
     * @param string $tableName 数据表名
     * @param boolean $extItem 数据返回类型选项，即是否返回完成的信息(包含扩展信息)。true:含扩展信息/false:不含扩展信息
     * @return array
     */
    public function getTableInfo($tableName, $extItem = false) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        $fieldList = $this->getAll("SHOW FIELDS FROM {$tableName}");
        if ($extItem === true) {
            return $fieldList;
        }

        //过滤掉杂数据
        $primaryArray = array();
        $fieldArray   = array();

        foreach ($fieldList as $lines) {
            //分析主键
            if ($lines['Key'] == 'PRI') {
                $primaryArray[] = $lines['Field'];
            }
            //分析字段
            $fieldArray[] = $lines['Field'];
        }

        return array('primaryKey'=>$primaryArray, 'fields'=>$fieldArray);
    }

    /**
     * 获取当前数据库中的所有的数据表名的列表
     * @access public
     * @return array
     */
    public function getTableList() {
        //执行SQL语句，获取数据信息
        $dbList = $this->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (!$dbList) {
            return array();
        }
        return array_values($dbList);
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        if (isset($this->_dbLink)) {
            $this->_dbLink = null;
        }
        return true;
    }
}

//使用方法：

/*
 * ? 占位符，是整个值的位置的
 * */
//搜索的时候
/*$stmt = $dbh->prepare("SELECT * FROM REGISTRY where name LIKE ?");
$res = $stmt->execute(array("%$_GET[name]%"));*/

/* error_reporting(0);
header('Content-type:text/html;charset=utf-8');


 */
/*
//查询单条记录
$res = $db->getOne('select * from test');
print_r($res);

//查询全部
$res = $db->getAll('select * from test');
print_r($res);

//增加
$db->insert("test",array("title"=>"444","url"=>"55555555555"));
$id = $db->insert("test",array("title"=>"444","url"=>"55555555555"),1);

//删除
$res = $db->delete("test","id=?",array(3));
var_dump($res);

$res = $db->delete("test","type=? and title=?",array(3,555));
echo $db->getLastSql();
var_dump($res);

//更新
$res = $db->update('admin_wechat_list',array('wechat_name'=>'888'),'id = 96');
var_dump($res);

$res = $db->update('admin_wechat_list',array('wechat_name'=>'888'),'id = ?',array(96));
var_dump($res);

//替换，替换要注意使用场景，
//data 中的字段必须要哟：PRIMARY KEY或一个UNIQUE索引，否则会产生一条新的记录。
$res = $db->replace('test',array('id'=>'4','title'=>'title','url'=>'4444455555'));
var_dump($res);
echo $db->lastInsertId();

//获取执行的sql语句。
echo $db->getLastSql();

//其他
$db->execute('INSERT INTO admin_wechat_list (wechat_name) VALUES (:wechat_name)',array('wechat_name'=>'456'));
$db->execute('INSERT INTO admin_wechat_list (wechat_name) VALUES (?)',array('123'));

//获取报错
echo $db->lastError();
*/
