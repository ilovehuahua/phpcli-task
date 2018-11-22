<?php

/**
 * Description of interface
 *
 * @author hunliji
 */
interface task{
    /**
     * 获取任务名称
     */
    public static function getTaskName();

    /**
     * 获取任务负责人
     */
    public static function getResponsiblePeopleName();

    /**
     * 获取任务需要执行的时间
     */
    public static function getRunTime();
    /**
     * 执行任务，到了任务点会调用这个run
     */
    public static function run();
}
