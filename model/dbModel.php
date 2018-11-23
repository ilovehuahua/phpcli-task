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
    public static function connect($host, $user_name, $pass, $database_name, $port) {
        return new mysqli($host, $user_name, $pass, $database_name, $port);
    }

    /**
     * 关闭数据库
     * @param type $con
     * @return type
     */
    public static function close($con) {
        return mysqli_close($con);
    }

    /**
     * 插入数据，注意field和arr中的数据必须对应
     * @param type $con
     * @param type $table
     * @param type $field
     * @param type $arr
     */
    public static function insert($con, $table, $field, $arr) {
        $sql = 'INSERT INTO ' . $table . " (" . $field . ") values (";
        $sql_v = '';
        foreach ($arr as $key => $value) {
            //判断是不是有多个插入
            if (is_array($value)) {
                //多条数据
                if (!empty($sql_v)) {
                    $sql_v .= "),(";
                }
                $arr_tmp=0;
                foreach ($value as $key_arr => $value_arr) {
                    if (empty($arr_tmp)) {
                        $sql_v = '\'' . $value_arr . '\'';
                    } else {
                        $sql_v .= ', \'' . $value_arr . '\'';
                    }
                    $arr_tmp=1;
                }
            } else {
                //单条数据
                if (empty($sql_v)) {
                    $sql_v = '\'' . $value . '\'';
                } else {
                    $sql_v .= ', \'' . $value . '\'';
                }
            }
        }
        $sql .= $sql_v . ")";
        echo $sql;
        return $con->query($sql);
    }

}
