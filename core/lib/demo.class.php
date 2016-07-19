<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/9
 * Time: 下午1:02
 */

//演示demo

class demo{
    public $init;

    function __construct($init){
        $this->init = $init;
    }

    function p(){
        echo $this->init;
        echo 'p';
    }

    function __toString(){
        return 'page class';
    }
}