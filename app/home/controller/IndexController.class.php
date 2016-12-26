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

        // #######$this->import('function.ext'); //注意common下的函数会直接加载，不需要load和import。
        //test123();

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

    function _empty(){
        d(I('get.'));
    }



    function stu()
    {
        echo U('index/stu',array('id'=>123,'type'=>456));
        exit;
        d(I('get.'));
        echo 'stu';
    }

    function test(){



        //$this->success("哈哈","http://baidu.com",100);
        //$this->error("哈哈","http://baidu.com",1000000);


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

          //d(I('keyword'));

          echo "test";

//          var_dump(Session::set("name","baozi"));
//          var_dump(Session::get("name"));

//        echo Aii::app('Demo/demo')->p();
        // ###  等价于上面
//            Aii::library('Demo/demo');
//            (new demo())->p();


//        $page = @(int)$_GET['page'];
//        $pager = Aii::app('Page');
//        echo $pager->loadCss('classic');
//        $pager_html = $pager->total(100)->num(10)->page($page)->url('/?page=')->output();
//        echo $pager_html;
//        echo Aii::app('Captcha')->show();  //验证码
//
//        $p = Aii::app('demo','123');
//        echo $p->p();

//
//        echo Aii::app('Pinyin')->output('包子');
//        echo Aii::app('page');
//

//        $this->import('demo');  //只加载不是实例化类
//        $d = new demo();
//        $d->t();
//上面等价于
//        $d = $this->load('demo');   //加载并实例化类
//        $d->t();

//
//        echo U('ab/cd',array('a'=>1,'b'=>2));
//
//        $a = '1';
//        $this->assign('a',$a);

         $this->display();
    }
}
