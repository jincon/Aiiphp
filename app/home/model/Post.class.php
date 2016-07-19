<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/17
 * Time: 下午1:42
 */
class PostModel extends Model{

    public $table = 'test';

//    public function __construct(){
//        parent::__construct();
//    }

    function p(){
        echo "test model";
    }
}