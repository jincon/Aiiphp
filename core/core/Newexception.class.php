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

}