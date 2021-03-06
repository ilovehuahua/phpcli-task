<?php

include_once __DIR__ .'/taskInterface.php';

/**
 * Description of userInfluenceRankTask
 *
 * @author xunyu
 */
class userInfluenceRankTask implements task {

    public static function getTaskName() {
        return "影响力排行计算";
    }

    public static function getRunTime() {
        return array(
            'type' => "every_day",
            'time_interval' => "once",
            'time' => "00:00:10"
        );
    }

    public static function getResponsiblePeopleName() {
        return "xunyu";
    }

    public static function run() {
        $con = dbModel::connect(conf::$DB['DB_HOST'], conf::$DB['DB_USERNAME'], conf::$DB['DB_PASSWORD'], conf::$DB['DB_DATABASE'], conf::$DB['DB_PORT']);
        $date=date("Y-m-d");
        $yes_date= date("Y-m-d", strtotime("- 1 day"));
        //计算截止昨天的数据
        $out = $con->query(sprintf(self::$sql, $date,$date));
        $sql_arr=[];
        while ($row = mysqli_fetch_assoc($out)) {
            $sql_arr[]= array(
                'user_id'=>$row['id'],
                'date'=>$yes_date,
                'all_score'=>$row['all_score'],
                'area_rank'=>$row['area_rank'],
                'all_rank'=>$row['all_rank']
            );
        }
        $con->close();
        $con2=dbModel::connect(conf::$DB['DB_HOST'], conf::$DB['DB_USERNAME'], conf::$DB['DB_PASSWORD'], conf::$DB['DB_DATABASE'], conf::$DB['DB_PORT']);
        $out=dbModel::insert($con2, 'user_influence_daily_statistics', 'user_id,date,all_score,area_rank,all_rank', $sql_arr);
        $con2->close();
        return $out;
    }
    /**
     *计算数据的sql
     * 时间复杂度为用户数量O(n^2)
     * @var type 
     */
    public static $sql=<<<HERE
SELECT 
    c.id,
    c.all_score,
    IF(c.id IS NOT NULL AND c.area_rank IS NULL,
        100,
        c.area_rank) AS area_rank,
    IF(c.id IS NOT NULL AND c.all_rank IS NULL,
        100,
        c.all_rank) AS all_rank
FROM
    (SELECT 
        a.*,
            ROUND(SUM(IF(a.occupation_id = b.occupation_id
                AND a.all_score > b.all_score, 1, 0)) / (SUM(IF(a.occupation_id = b.occupation_id, 1, 0)) - 1) * 100, 2) AS all_rank,
            ROUND(SUM(IF(a.occupation_id = b.occupation_id
                AND a.area_id = b.area_id
                AND a.all_score > b.all_score, 1, 0)) / (SUM(IF(a.occupation_id = b.occupation_id
                AND a.area_id = b.area_id, 1, 0)) - 1) * 100, 2) AS area_rank
    FROM
        (SELECT 
        a.id,
            a.occupation_id,
            area_id,
            SUM(behavior_score) AS all_score
    FROM
        user a
    LEFT JOIN user_influence b ON a.id = b.user_id
    WHERE
        a.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND b.created_at < '%s'
    GROUP BY a.id
    ORDER BY a.id ASC) a
    JOIN (SELECT 
        a.id,
            a.occupation_id,
            area_id,
            SUM(behavior_score) AS all_score
    FROM
        user a
    LEFT JOIN user_influence b ON a.id = b.user_id
    WHERE
        a.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND b.created_at < '%s'
    GROUP BY a.id
    ORDER BY a.id ASC) b
    GROUP BY a.id) c
HERE;
}
