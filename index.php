<?php

/**
 * php定时任务
 * @author xunyu
 * @date 2018-11-21
 */
//只允许cli运行
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
    exit("only allow run in cli model!");
}
$str = "\n---------------------------------\nPHP timer task starts running...\n@author zenbaowow\n@date 2018-11-21\n---------------------------------\n";
echo $str;
include_once 'common/common.php';
include_once 'common/dir.php';
shellOut("init common Function success!", 1);

ini_set('date.timezone', 'Asia/Shanghai');
shellOut("init timezone success!", 1);
include_once 'model/dbModel.php';
shellOut("init DBModel success!", 1);
//1为生产环境
$runType = 0;
//1为debug模式
$debug=0;
//判断运行环境
if (!empty($argv[1])) {
    if ($argv[1] == "test") {
        //测试环境
        $runType = 1;
    }
}

//判断是不是debug
if(!empty($argv[2])){
    if($argv[2]=='debug'){
        $debug=1;
        shellOut("now you are in debug model!", 1);
    }
}
shellOut("now you are in release model!", 1);
//加载配置文件
if ($runType == 1) {
    include_once 'conf/conf_test.php';
} else {
    include_once 'conf/conf.php';
}
shellOut("init conf success!", 1);
shellOut("php task application run success,now you can close the shell windows.", 1);
//循环执行任务
while (true) {
    //每隔1秒执行一次。
    sleep(1);
    $now = date("H:i:s");
    $date = date("Y-m-d");
    $debug&&shellOut("task is running...");
    //遍历扫描task文件
    if (date("s") == 0) {
        //每分钟检查task文件列表
        $fires = Dir::checkDir(__DIR__."task/");
    }

    if (!empty($fires)) {
        foreach ($fires as $key => $value) {
            $tmp = explode('.', $value);
            //判断是否已经执行过了
            if (isset(${"task" . $date}[$tmp[0]])&&${"task" . $date}[$tmp[0]] == 1) {
                //这个任务今天已经执行，那么跳过
                $debug&&shellOut("子进程消息：{$tmp[0]}已经执行！");
                continue;
            }
            include_once 'task/' . $value;
            //判断执行时间是否已经到了
            $exec_time = $tmp[0]::getRunTime();
            if ($exec_time['time'] != $now) {
                $debug&&shellOut("子进程消息：{$tmp[0]}未到运行时间！");
                continue;
            }
            //新建进程执行
            $pid = pcntl_fork();
            if ($pid == -1) {
                shellOut("创建{$tmp[0]}子进程失败");
            } elseif ($pid) {
                //进入进程池进行监控
                ${"task" . $date}[$tmp[0]] = 1;
                $processPool[] = array(
                    'pid' => $pid,
                    'taskName' => $tmp[0],
                    'watchTimes' => 1
                );
                $debug&&shellOut("创建{$tmp[0]}子进程成功，并进入线程池监控");
            } else {// 子进程处理
                $out[] = array(
                    'taskName' => $tmp[0]::getTaskName(),
                    'runTime' => $tmp[0]::getRunTime(),
                    'responsiblePeopleName' => $tmp[0]::getResponsiblePeopleName(),
                    'out' => $tmp[0]::run()
                );
                //保存执行结果TODO
                $debug&&shellOut("子进程{$tmp[0]}结束运行，返回结果：" . json_encode($out));
                exit;
            }

        }
    }

    //检查进程池
    if (!empty($processPool)) {
        foreach ($processPool as $key => $value) {
            $out = pcntl_waitpid($value['pid'], $status, WNOHANG);
            $debug&&$str = "第" . $value['watchTimes'] . "次检查" . $value['pid'] . "进程";
            shellOut($str);
            if ($out == 0) {
                $processPool[$key]['watchTimes'] ++;
                $str = "子进程{$value['pid']}正在运行{$value['taskName']}...";
            } else if ($out < 0) {
                $str = "子进程{$value['pid']}出错";
                shellOut($str);
            } else {
                $str = "子进程{$value['pid']}运行成功，并已成功退出";
                unset($processPool[$key]);
            }
            shellOut($str);
        }
    }
}


