<?php
include_once 'interface.php';
include_once 'config.php';

/**
 * We Like Study!
 * 我们喜欢学习
 *
 * @see www.welikestudy.com
 * @copyright www.wei1224hf.com
 * @version 1.0
 * */
class wls{

	/**
	 * 配置参数,在这个类初始化之后得到
	 * 需要引用外部文件 config.php
	 * */
	public $c = null;

	/**
	 * 构造函数
	 * 每次引用这个文件时都会自动运行
	 * */
	public function wls(){
		$this->c = new wlsconfig();
	}

	/**
	 * 得到数据库连接参数
	 * 需要读取配置
	 * */
	public function conn(){
		$conn = mysql_connect($this->c->dbhost,$this->c->dbuser,$this->c->dbpwd);
		mysql_select_db($this->c->dbname,$conn);
		mysql_query('SET NAMES UTF8');
		return $conn;
	}

	/**
	 * 得到当前用户数据
	 * 需要其权限数据
	 * */
	public function getUser($id=null){

	}
	
	/**
	 * 错误操作,需要记录下用户的行为
	 *
	 * @param $whatHappened 事件描述,可以是数组
	 * */
	public function error($whatHappened){
		//TODO
		$fileName = dirname(__FILE__).'/../file/log/error.txt';
		$handle=fopen($fileName,"a");
		fwrite($handle,$whatHappened['description']."\n");
		fclose($handle);
	}

	/**
	 * 解析前台传过来的  wls.php?controll=x&action=y 这种格式
	 * 控制器有级层关系,以空格分开
	 * 每一个控制器对应一个后台文件
	 * 每一个动作对应一个函数
	 * */
	public function initApp(){
		$controller = null;
		if(isset($_REQUEST['controller'])){
			$arr = explode("_",$_REQUEST['controller']);
			$dept = count($arr);
			if($dept==1){
				//如果 controller 只有一层,比如 controller=x
				//直接导入这个 controller/x.php 文件,并初始化这个class对象
				include $this->c->license.'/controller/'.$_REQUEST['controller'].'.php';
				eval('$controller = new '.$_REQUEST['controller'].'();');
			}else{
				//如果 controller 不止一层,比如 controller=x_x2_x3 
				//那么就要导入文件  controller/x/x2/x3.php 
				$path = '';
				for($i=0;$i<count($arr);$i++){
					$path .= "/".$arr[$i];
				}
				if($arr[count($arr)-1] == $arr[count($arr)-2]){
					unset($arr[count($arr)-1]);
				}
				$controllername = '';
				for($i=0 ;$i<count($arr);$i++){
					$controllername .= $arr[$i]."_";
				}
				$controllername = substr($controllername,0,strlen($controllername)-1);
				include $this->c->license.'/controller'.$path.'.php';
				eval('$controller = new '.$controllername.'();');
			}
		}else{
			echo 'need controller!';
		}

		//捕获前台传来的 action ,引用那个函
		if(isset($_REQUEST['action'])){
			eval('$controller->'.$_REQUEST['action'].'();');
		}else{
			echo 'need action!';
		}
	}
}

$wls = new wls();
$wls->initApp();
?>