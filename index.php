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
$str = "---------------------------------\nPHP timer task starts running...\n@author zenbaowow\n@date 2018-11-21\n---------------------------------\n";
echo $str;
include_once 'common/common.php';
include_once 'common/dir.php';
shellOut("init common Function success!", 1);

ini_set('date.timezone', 'Asia/Shanghai');
shellOut("init timezone success!", 1);
include_once 'model/dbModel.php';
shellOut("init DBModel success!", 1);
$runType = 0;

//判断运行环境
if (!empty($argv[1])) {
    if ($argv[1] == "test") {
        //测试环境
        $runType = 1;
    }
}

//加载配置文件
if ($runType == 1) {
    include_once 'conf/conf_test.php';
} else {
    include_once 'conf/conf.php';
}
shellOut("init conf success!", 1);

//循环执行任务
while (true) {
    //每隔1秒执行一次。
    sleep(1);
    $now = date("H:i:s");
    $date = date("Y-m-d");
    shellOut("task is running...");
    //遍历扫描task文件
    if (date("s") == 0) {
        //每分钟检查task文件列表
        $fires = Dir::checkDir("task/");
    }

    if (!empty($fires)) {
        foreach ($fires as $key => $value) {
            //新建进程执行
            $pid = pcntl_fork();
            if ($pid == -1) {
                shellOut("创建{$tmp[0]}子进程失败");
            } elseif ($pid) {
                //进入进程池进行监控
                $processPool[] = array(
                    'pid' => $pid,
                    'taskName' => $tmp[0],
                    'watchTimes' => 1
                );
                shellOut("创建{$tmp[0]}子进程成功，并进入线程池监控");
                ${"task" . $date}[$tmp[0]] = 1;
            } else {// 子进程处理
                include_once 'task/' . $value;
                $tmp = explode('.', $value);

                //判断执行时间是否已经到了
                $exec_time = $tmp[0]::getRunTime();
                if ($exec_time['time'] != $now) {
                    shellOut("子进程消息：{$tmp[0]}未到运行时间！");
                    exit;
                }

                //判断是否已经执行过了
                if (${"task" . $date}[$tmp[0]] == 1) {
                    //这个任务今天已经执行，那么跳过
                    shellOut("子进程消息：{$tmp[0]}已经执行！");
                    exit;
                }
                
                $childStartTime = time();
                shellOut("子进程{$tmp[0]}开始执行...");
                $out[] = array(
                    'taskName' => $tmp[0]::getTaskName(),
                    'runTime' => $tmp[0]::getRunTime(),
                    'responsiblePeopleName' => $tmp[0]::getResponsiblePeopleName(),
                    'out' => $tmp[0]::run()
                );
                //保存执行结果TODO
                shellOut("子进程{$tmp[0]}结束运行，返回结果：" . json_encode($out));
                exit;
            }

        }
    }

    //检查进程池
    if (!empty($processPool)) {
        foreach ($processPool as $key => $value) {
            $out = pcntl_waitpid($value['pid'], $status, WNOHANG);
            $str = "第" . $value['watchTimes'] . "次检查" . $value['pid'] . "进程";
            shellOut($str);
            if ($out == 0) {
                $processPool[$key]['watchTimes'] ++;
                $str = "子进程{$value['pid']}正在运行...";
            } else if ($out < 0) {
                $str = "子进程{$value['pid']}出错";
                shellOut($str);
            } else {
                $str = "子进程{$value['pid']}运行成功";
                unset($processPool[$key]);
            }
            shellOut($str);
        }
    }
}


