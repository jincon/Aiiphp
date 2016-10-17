<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/5
 * Time: 下午10:51
 */

header('Content-Type:text/html;charset=utf-8');

define('ROOT',dirname(__FILE__).'/');
define('APP',ROOT.'app/');

error_reporting(0); //屏蔽所有报错。

define('DEBUG',true);  //开启debug

require 'core/Aii.php';

Aii::run();



