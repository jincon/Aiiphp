<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/8
 * Time: 下午1:26
 */
if (!defined('IN_Aii')) {
    exit();
}

class Config{
    public static function get($name=''){
        $config1 = require_cache(CORE_ROOT."Convention.php",1);
        $config2 = require_cache(APP."common/config/config.php",1);
        $config = array_merge($config1,$config2);
        if($config2['LOAD_EXT_CONFIG']){
            foreach(explode(',',$config2['LOAD_EXT_CONFIG']) as $v){
                $config += require_cache(APP."common/config/".$v.".php",1);
            }
        }
        unset($config1);unset($config2);
        if(!$name){
            return $config;
        }else{
            return isset($config[$name]) ? $config[$name] : false;
        }
    }
}