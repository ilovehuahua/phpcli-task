<?php
/**
 * 文件操作，文件检查
 * @author xunyu
 */
class Dir {

    /**
     * 检查文件夹下文件
     * @param type $url
     * @return type
     */
    public static function checkDir($url) {
        //遍历扫描task文件
        $fires = my_dir($url);
        foreach ($fires as $key => $value) {
            //剔除接口
            if ($value == 'taskInterface.php') {
                unset($fires[$key]);
                break;
            }
        }
        return $fires;
    }

}