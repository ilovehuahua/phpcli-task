<?php

/**
 * cli运行环境
 * @author xunyu
 * @date 2018-11-21
 */
include_once 'model/dbModel.php';
include_once 'common/common.php';
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

//循环执行任务
while (true) {
    //遍历扫描task文件
    $fires = my_dir("task/");
    foreach ($fires as $key => $value) {
        //剔除接口
        if ($value == 'taskInterface.php') {
            unset($fires[$key]);
            break;
        }
    }

    if (!empty($fires)) {
        foreach ($fires as $key => $value) {
            include_once 'task/' . $value;
            $tmp = explode('.', $value);

            //判断执行时间是否已经到了
            $exec_time = $tmp[0]::getRunTime();

            //判断是否已经执行过了
            //新建进程执行
            $pid = pcntl_fork();
            if ($pid == -1) {
                die("could not fork");
            } elseif ($pid) {
                echo "I'm the Parent $i\n";
                $proc = pcntl_waitpid($pid, $status, WNOHANG);
                var_dump($proc);
                if ($proc == 0) {
                    //进入进程池进行监控
                    $processPool[] = $pid;
                } else if ($proc == -1) {
                    echo "子进程出错！";
                }
            } else {// 子进程处理
                $out[] = array(
                    'taskName' => $tmp[0]::getTaskName(),
                    'runTime' => $tmp[0]::getRunTime(),
                    'responsiblePeopleName' => $tmp[0]::getResponsiblePeopleName(),
                    'out' => $tmp[0]::run()
                );
                var_dump($out);
                exit;
            }
            //保存执行结果
        }
    }
    $i=0;
    while (true) {
        $i++;
        if (!empty($processPool)) {
            foreach ($processPool as $key => $value) {
                $out = pcntl_waitpid($value, $status, WNOHANG);
                echo $i.":\n";
                var_dump($out);
            }
        } else {
            break;
        }
        //等待100毫秒等待子进程执行
        usleep(100000);
    }


    break;
}