<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/5
 * Time: 下午11:48
 */
//
class IndexController extends JomController{


//    function __construct(){
//        parent::__construct();
//    }

    function index(){
        //echo $this->module;
        echo "<h1>Hello Aiiphp v".VERSION."</h1>";

//        $m = $this->model('Post');
//        var_dump($m->getConnect('bbs')->getOne('select * from tp_admin limit 1'));
//        echo $m->add(array('tid'=>'123','type'=>'5','title'=>'111','url'=>'222'));
//        echo $m->getTable();
//        var_dump($m->getDB()->getOne('select * from test limit 1'));
//
//        $res = $m->query('select * from test limit 1');
//        $res1 = $m->fetchRow();
//        var_dump($res1);

    }

    function test(){
//          $m = $this->model('Post');
//          echo $m->p();

//        $url = "url地址";
//        $data = Aii::app('Curl')->get($url);
//        print_r($data);



//        echo Aii::app('FileCache')->set('key','我是帅哥');
//        echo Aii::app('FileCache')->set('key2','我是帅哥222');
//        echo Aii::app('FileCache')->get('key2');

//        echo Aii::app('Curl')->get();

//        print_r(I('SERVER.'));

//        test();
//        echo Aii::app('demo')->p();
//        echo C('ext');

//          Log::write("啥东西");

          echo "test";

//        echo Aii::app('Demo/demo')->p();

//        $page = @(int)$_GET['page'];
//        $pager = Aii::app('Page');
//        echo $pager->loadCss('classic');
//        $pager_html = $pager->total(100)->num(10)->page($page)->url('/?page=')->output();
//        echo $pager_html;
//        echo $this->lib('Captcha')->show();  //验证码
//
//        $p = $this->lib('demo','123');
//        echo $p->p();
//
//
//
//        echo $this->lib('Pinyin')->output('包金昆');
//
//        echo $this->lib('page');
//
//        $this->import('function.demo');
//        $d =  $this->import('class.demo');
//        $d->t();
//
//        echo new code();
//
//        echo U('ab/cd',array('a'=>1,'b'=>2));
//
//        $a = '1';
//        $this->assign('a',$a);

         $this->display();
    }
}
