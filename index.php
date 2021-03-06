<?php

/**
 * php定时任务
 * @author xunyu
 * @date 2018-11-21
 */
//只允许cli运行
header('content-type:text/html;charset=utf-8');
$sapi_type = php_sapi_name();
//必须处理模式
if ($sapi_type != 'cli') {
    exit("only allow run in cli model!");
}
// 必须加载扩展  
if (!function_exists("pcntl_fork")) {
    die("pcntl extention is must !");
}
$str = "\n---------------------------------\nPHP timer task starts running...\n@author zenbaowow\n@date 2018-11-21\n---------------------------------\n";
echo $str;
include_once __DIR__ .'/common/common.php';
include_once __DIR__ .'/common/dir.php';
shellOut("init common Function success!", 1);

ini_set('date.timezone', 'Asia/Shanghai');
shellOut("init timezone success!", 1);
include_once __DIR__ .'/model/dbModel.php';
shellOut("init DBModel success!", 1);
//1为生产环境
$runType = 0;
//1为debug模式
$debug = 0;
//判断运行环境
if (!empty($argv[1])) {
    if ($argv[1] == "test") {
        //测试环境
        $runType = 1;
    }
}

//判断是不是debug
if (!empty($argv[2])) {
    if ($argv[2] == 'debug') {
        $debug = 1;
        shellOut("now you are in debug model!", 1);
    }
}
shellOut("now you are in release model!", 1);
//加载配置文件
if ($runType == 1) {
    include_once __DIR__ .'/conf/conf_test.php';
} else {
    include_once __DIR__ .'/conf/conf.php';
}
shellOut("init conf success!", 1);
shellOut("php task application run success,now you can close the shell windows.", 1);
//循环执行任务
while (true) {
    try {
        //每隔1秒执行一次。
        sleep(1);
        $now = date("H:i:s");
        $date = date("Y-m-d");
        //遍历扫描task文件
        $fires = Dir::checkDir(__DIR__ . "/task/");

        if (!empty($fires)) {
            foreach ($fires as $key => $value) {
                $tmp = explode('.', $value);
                //判断是否已经执行过了
              if (isset(${"task" . $date}[$tmp[0]]) && ${"task" . $date}[$tmp[0]] == 1) {
                    //这个任务今天已经执行，那么跳过
                   continue;
                }
                include_once __DIR__ .'/task/' . $value;
                //判断执行时间是否已经到了
                $exec_time = $tmp[0]::getRunTime();
                if ($exec_time['time'] > $now) {
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
                    shellOut("创建{$pid}号{$tmp[0]}子进程成功，并进入线程池监控". json_encode($processPool));
                } else {// 子进程处理
                    $out = array(
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
                $flag = pcntl_waitpid($value['pid'], $status, WNOHANG);
                $str = "第" . $value['watchTimes'] . "次检查" . $value['pid'] . "进程";
                shellOut($str);
                if ($flag == 0) {
                    $processPool[$key]['watchTimes'] ++;
                    $str = "子进程{$value['pid']}正在运行{$value['taskName']}...";
                } else if ($flag < 0) {
                    $str = "子进程{$value['pid']}出错";
                    shellOut($str);
                } else {
                    $str = "子进程{$value['pid']}运行成功，并已成功退出";
                    unset($processPool[$key]);
                }
                shellOut($str);
            }
        }
    } catch (Exception $exc) {
        shellOut("ERR:出现致命异常！系统已停止");
        shellOut($exc->getMessage());
        shellOut($exc->getTraceAsString());
        exit;
    }
}


