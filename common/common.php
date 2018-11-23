<?php

/**
 * 通用函数
 * @author xunyu
 */

/**
 * 递归获取文件
 * @param type $dir
 * @return type
 */
function my_dir($dir) {
    $files = array();
    if (@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
        while (($file = readdir($handle)) !== false) {
            if ($file != ".." && $file != ".") { //排除根目录；
                if (is_dir($dir . "/" . $file)) { //如果是子文件夹，就进行递归
                    $files[$file] = my_dir($dir . "/" . $file);
                } else { //不然就将文件的名字存入数组；
                    $files[] = $file;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}

/**
 * shell输出
 */
function shellOut($str,$wait=0) {
    if($wait===1){
        usleep(200000);
    }
    echo date("Y-m-d H:i:s")."  ".$str."\n";
}