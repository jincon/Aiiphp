<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/9
 * Time: 下午1:50
 */

if (!defined('IN_Aii')) {
    exit();
}

class Newexception extends Exception {

    /**
     * 异常输出
     *
     * 注：当调试模式关闭时,异常提示信息将会写入日志
     *
     * @access public
     * @return string
     */
    public function __toString() {
        //分析获取异常信息
        $code         = $this->getCode();
        $exceptionMsg = $this->getMessage();
        $message      = ($code ? "Error Code:{$code}<br/>" : '') . ($exceptionMsg ? "Error Message:{$exceptionMsg}" : '');

        $line = $this->getLine();
        $sourceFile = $this->getFile() . (!$line ? '' : "({$line})");

        if (DEBUG === true) {
            $traceString = '';
            $traces = $this->getTrace();
            foreach ($traces as $key=>$trace) {
                //代码跟踪级别限制
                if ($key > 2) {
                    break;
                }
                $traceString .= "#{$key} {$trace['file']}({$trace['line']})<br/>";
            }
            //定义错误级别(当错误级别为Normal时，则不显示代码跟踪信息)
            $level = 'Error';

            //ob_start();
            //加载,分析,并输出excepiton文件内容
            include CORE_ROOT . 'view/exception.php';

            //$exceptionMessage = ob_get_clean();
        }


        if (DEBUG === false) {
            $exceptionMsg = str_replace('<br/>', ' ', $exceptionMsg);
            $logContent   = ((!$code) ? "" : "Error Code:{$code} ") . "Error Message:{$exceptionMsg} File:{$sourceFile}";
            //以后写入程序运行日志作为记录吧。
            //Log::write($logContent);
        }

        //return $exceptionMessage;
        return '';
    }


    static public function halt($error)
    {
        $e = array();
        if (DEBUG) {
            //调试模式下输出错误信息
            if (!is_array($error)) {
                $trace = debug_backtrace();
                $e['message'] = $error;
                $e['file'] = $trace[0]['file'];
                $e['line'] = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace'] = ob_get_clean();
            } else {
                $e = $error;
            }
            if (IS_CLI) {
                exit(iconv('UTF-8', 'gbk', $e['message']) . PHP_EOL . 'FILE: ' . $e['file'] . '(' . $e['line'] . ')' . PHP_EOL . $e['trace']);
            }
        } else {
            //否则定向到错误页面
            $error_page = C('ERROR_PAGE');
            if (!empty($error_page)) {
                redirect($error_page);
            } else {
                $message = is_array($error) ? $error['message'] : $error;
                $e['message'] = C('SHOW_ERROR_MSG') ? $message : C('ERROR_MESSAGE');
            }
        }

        // 包含异常页面模板
        $exceptionFile =  C('TMPL_EXCEPTION_FILE')?C('TMPL_EXCEPTION_FILE'):CORE_ROOT.'view/trace_exception.php';
        include $exceptionFile;
        exit;
    }


    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $trace              =   $e->getTrace();
        if('E'==$trace[0]['function']) {
            $error['file']  =   $trace[0]['file'];
            $error['line']  =   $trace[0]['line'];
        }else{
            $error['file']  =   $e->getFile();
            $error['line']  =   $e->getLine();
        }
        $error['trace']     =   $e->getTraceAsString();
        // 发送404信息
        header('HTTP/1.1 404 Not Found');
        header('Status:404 Not Found');
        self::halt($error);
    }


    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                $errorStr = "$errstr ".$errfile." 第 $errline 行.";
                self::halt($errorStr);
                break;
            default:
                $errorStr = "$errstr ".$errfile." 第 $errline 行.";
                self::halt($errorStr);
                //echo $errorStr = "<p>[$errno] $errstr ".$errfile." 第 $errline 行.</p>";
                break;
        }
    }


    /**
     * 致命错误捕获
     *
     *  @return void
     */
    static public function fatalError() {
        if ($e = error_get_last()) {
            switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    self::halt($e);
                    //self::errorHandler($e['type'],$e['message'],$e['file'],$e['line']);
                    break;
            }
        }
    }


    //废弃
    /*static public function errorHandler($type,$errstr,$errfile,$errline){
        $arr = array(
            '['.date('Y-m-d h-i-s').']',
            'type:'.$type,
            '|',
            $errstr,
            $errfile,
            'line:'.$errline,
        );
        //写入错误日志
        //格式 ：  时间 uri | 错误消息 文件位置 第几行
        //error_log(implode(' ',$arr)."\r\n",3,'./test.txt','extra');

        echo implode(' ',$arr)."\r\n";
    }*/
}