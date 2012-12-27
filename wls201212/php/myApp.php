<?php
/**
 * 系统后台的主文件
 * 任何前端对后台的访问,都要依赖这个文件跳转
 * 无法对后台其他的任何PHP文件直接访问
 * 因为其他的PHP文件都仅仅是一个CLASS定义,没有对内部FUNCTION的访问口
 *
 * TODO 数据过滤,过滤所有的HTML标签跟SQL标签
 * @author wei1224hf@gmail.com
 * @version 2010
 */

//读取配置文件内容,包括: 数据库配置,系统状态,邮件服务器配置,日志文件地址等
include_once 'config.php';
//引入一个 static 的PHP类,里面定义了一些常用的小函数,这些函数与各个业务模块无关
include_once 'tools.php';

//要访问系统的后台,前端必须传一个 &class=classname&function=functionname 的URL过来
if(!isset($_GET['class']))die('class missed!');
if(!isset($_GET['function']))die('function missed!');
$class = $_REQUEST['class'];
eval('require_once \''.$class.'.php\';');
$function = $_REQUEST['function'];
eval('$class = new '.$class.'();');
eval('$class->'.$function.'();');

//如果使用了数据库连接,就关闭掉.一次访问,数据库必定只打开一次
if(tools::$CONN <> NULL)tools::closeConn();