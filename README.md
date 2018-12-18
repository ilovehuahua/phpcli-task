# phpcli-task
php多进程定时任务系统，master进程负责监听调度。slave进程负责执行定时任务。

新的任务php文件只要继承task目录下的interface task 接口，然后放在task目录下，由于master进程会动态读取task目录，所以新的定时任务会自动加入执行。

日志以及执行结果放在log目录
