<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <title>系统发生错误</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        html{ overflow-y: scroll; }
        body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
        img{ border: 0; }
        .error{ padding: 24px 48px; }
        .face{ font-size: 50px; font-weight: normal; line-height: 70px; margin-bottom: 12px; }
        h1{ font-size: 32px; line-height: 48px; }
        .error .content{ padding-top: 10px}
        .error .info{ margin-bottom: 12px; }
        .error .info .title{ margin-bottom: 3px; }
        .error .info .title h3{ color: #000; font-weight: 700; font-size: 16px; }
        .error .info .text{ line-height: 24px; }
        .copyright{ padding: 12px 48px; color: #999; }
        .copyright a{ color: #000; text-decoration: none; }
        .text p{line-height:30px}
    </style>
</head>
<body>
<div class="error">
    <p class="face">异常错误：</p>
    <h3><?php echo strip_tags($e['message']);?> &nbsp;&nbsp;&nbsp;<a href="<?php echo HOST;?>">返回首页</a></h3>
    <div class="content">
        <?php if(isset($e['file'])) {?>
            <div class="info">
                <div class="title">
                    <h4>错误位置</h4>
                </div>
                <div class="text">
                    <p>FILE: <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?></p>
                </div>
            </div>
        <?php }?>
        <?php if(isset($e['trace'])) {?>
            <div class="info">
                <div class="title">
                    <h3>TRACE</h3>
                </div>
                <div class="text">
                    <p><?php echo nl2br($e['trace']);?></p>
                </div>
            </div>
        <?php }?>
    </div>
</div>
<div class="copyright">
    <p>Aiiphp v<?php echo VERSION ?></p>
</div>
</body>
</html>

<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 2016/10/17
 * Time: 下午12:31
 */