<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/5
 * Time: 下午11:48
 */
class IndexController extends BaseController{

    function index(){
        //print_r($_GET);
        echo "hello admin mvc";
    }

    function test(){
        echo U('ab/cd',array('a'=>1,'b'=>2));
        die;
        echo "admin test";
    }
}