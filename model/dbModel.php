<?php
/**
 * 数据库处理
 *
 * @author xunyu
 */
class dbModel {
    /**
     * 连接数据库
     * @param type $host
     * @param type $user_name
     * @param type $pass
     * @param type $database_name
     * @return \mysqli
     */
    public static function connect($host,$user_name,$pass,$database_name,$port) {
        return new mysqli($host, $user_name, $pass, $database_name,$port);
    }
    /**
     * 关闭数据库
     * @param type $con
     * @return type
     */
    public static function close($con) {
        return mysqli_close($con);
    }
}
