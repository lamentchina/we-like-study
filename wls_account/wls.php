<?php
include_once 'interface.php';
include_once 'config.php';
include_once 'free/model/tools.php';
include_once 'free/model/il8n.php';

/**
 * We Like Study!
 * 
 * This is an open-source project
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
	 * 工具包,提供一些与系统逻辑无关的,
	 * 但是又经常用到的函数
	 * */
	public $t = null;
	
	/**
	 * 国际化语言支持
	 * TODO
	 * */
	public $lang = null;

	/**
	 * 构造函数
	 * 每次引用这个文件时都会自动运行
	 * */
	public function wls(){
		$this->c = new wlsconfig();
		$this->t = new tools();
		$obj = new il8n();
		$this->lang = $obj->lang;
	}

	/**
	 * 得到数据库连接参数
	 * 需要读取配置
	 * TODO 我还不知道怎样实现持久性连接
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
	public function getMyUser(){
		include_once dirname(__FILE__)."/".$this->c->license."/model/user.php";
		$m = new m_user();
		$myuser = $m->getUser(null,true);
		
		return $myuser;
	}

	/**
	 * 错误操作,需要记录下用户的行为
	 *
	 * @param $whatHappened 事件描述,可以是数组
	 * */
	public function error($whatHappened,$arr=null){
		//TODO
		$fileName = $this->c->filePath.'/log/error.txt';
		$handle=fopen($fileName,"a");
		fwrite($handle,$whatHappened."\n");
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
			if($this->c->state=='uninstall'){//系统还未安装
				header("location:wls.php?controller=install&action=main"); 	
			}else{ 
				include_once 'free/model/user.php';
				$m_user = new m_user();
				$menus = $m_user->getMyMenuForDesktop();
//				print_r($menus);exit();
				
				$modules = array();
				$shortcut = array();
				$quickstart = array();
				for($i=0;$i<count($menus);$i++){
					$menuItem = array(
						'id'=>"id_".$menus[$i]['id_level'],
						'className'=>"class_".$menus[$i]['id_level'],
		          		"launcher"=>array(
		          			"text"=>$menus[$i]['text'],
		          			"tooltip"=>'<b>'.$menus[$i]['text'].'</b>',
		          		),
		          		"launcherPaths"=>array(
		          			"startmenu"=>$menus[$i]['startmenu']
		          		),
					);
					
					if(isset($menus[$i]['icon'])){
						$menuItem['launcher']['iconCls'] = 'icon_'.$menus[$i]['icon'].'_16_16';
						$menuItem['launcher']['shortcutIconCls'] = 'icon_'.$menus[$i]['icon'].'_48_48';
					}
					if(isset($menus[$i]['description'])){
						$menuItem['launcher']['tooltip'] = '<b>'.$menus[$i]['description'].'</b>';
					}					
					$modules[] = $menuItem;
					if(isset($menus[$i]['isshortcut']) && $menus[$i]['isshortcut']==1){
						$shortcut[] = "id_".$menus[$i]['id_level'];
					}
					if(isset($menus[$i]['isquickstart']) && $menus[$i]['isquickstart']==1){
						$quickstart[] = "id_".$menus[$i]['id_level'];
					}					
				}
				
				include_once "free/view/qWikiOffice/qDeskTop.php";
			}
		}

		//捕获前台传来的 action ,引用对应函数
		if(isset($_REQUEST['action'])){
			eval('$controller->'.$_REQUEST['action'].'();');
		}else{
//			echo 'need action!'; //TODO
		}
	}
}

$wls = new wls();
$wls->initApp();
?>
