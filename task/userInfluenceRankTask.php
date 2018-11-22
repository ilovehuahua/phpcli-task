<?php

include_once 'taskInterface.php';
include_once 'model/dbModel.php';

/**
 * Description of userInfluenceRankTask
 *
 * @author hunliji
 */
class userInfluenceRankTask implements task {

    public static function getTaskName() {
        return "影响力排行计算";
    }

    public static function getRunTime() {
        return array(
            'type' => "every_day",
            'time_interval' => "once",
            'time' => "17:30:00"
        );
    }

    public static function getResponsiblePeopleName() {
        return "xunyu";
    }

    public static function run() {
        $con = dbModel::connect(conf::$DB['DB_HOST'], conf::$DB['DB_USERNAME'], conf::$DB['DB_PASSWORD'], conf::$DB['DB_DATABASE'], conf::$DB['DB_PORT']);
        $out = $con->query("SELECT a.*,b.name FROM user_influence a left join user b on a.user_id=b.id order by id desc limit 10");
        $con->close();
        while ($row = mysqli_fetch_assoc($out)) {
            echo $row['id'] . ":" . $row['name'];
        }
        return 12345;
    }
}
