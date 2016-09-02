<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/30
 * Time: 下午2:13
 */
class EmptyController extends Controller{
    function index(){
        echo "默认空控制器的index";
    }

    function _empty(){
        echo "空控制器的空操作";
    }
}