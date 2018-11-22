<?php
/**
 * cli运行环境
 * @author xunyu
 * @date 2018-11-21
 */
include_once 'model/dbModel.php';
$runType=0;

//判断运行环境
if(!empty($argv[1])){
    if($argv[1]=="test"){
        //测试环境
        $runType=1;
    }
}

//加载配置文件
if($runType==1){
    include_once 'conf/conf_test.php';
} else {
    include_once 'conf/conf.php';
}


//循环执行任务
while (true) {
    $con=dbModel::connect(conf::$DB['DB_HOST'], conf::$DB['DB_USERNAME'], conf::$DB['DB_PASSWORD'], conf::$DB['DB_DATABASE'],conf::$DB['DB_PORT']);
    $out=$con->query("SELECT a.*,b.name FROM user_influence a left join user b on a.user_id=b.id order by id desc limit 10");
    $con->close();
    while($row = mysqli_fetch_assoc($out)){
        echo $row['id'].":".$row['name'];
    }
    break;
}