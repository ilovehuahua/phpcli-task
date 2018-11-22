<?php

/**
 * Description of baseModel
 *
 * @author hunliji
 */
class baseModel {
    public static function dbConnect($host,$user_name,$pass,$database_name) {
        return new mysqli($host, $user_name, $pass, $database_name);
    }
} 