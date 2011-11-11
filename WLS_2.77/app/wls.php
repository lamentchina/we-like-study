<?php
include_once 'interface.php';
include_once 'config.php';
include_once 'model/tools.php';

/**
 * We Like Study! 
 * 
 * 这是一个开源的在线学习系统,基于 B/S结构,php + mysql
 * 这是在我大学毕业设计的基础上改进的,这个系统本来仅仅是作为我个人的技术研究用的实验用系统开发而已
 * 不过当我将源码及文档共享了之后,有不少网友觉得不错,建议我将其改善,甚至提出愿意付费实现一些专属功能
 * 因此我决定好好完善这个系统
 *
 * @see www.welikestudy.com
 * @see http://code.google.com/p/we-like-study/
 * @author wei1224hf
 * @copyright www.welikestudy.com
 * @version 1.0
 * */
class wls{

	/**
	 * 配置文件,详见  config.php
	 * 配置文件必须是可读可写的,系统安装过程及系统配置修改功能都要求此文件可写
	 * */
	public $cfg = null;
	
	/**
	 * 我将一些业务逻辑与程序无关的,但是却有非常有用的小函数,全部集中放到一个工具箱文件中
	 * 这些函数都在 model/tools.php 中
	 * */
	public $tool = null;
	
	/**
	 * 国际化语言包支持
	 * 每个模块都有一个自己的语言包文件
	 * 前台引用语言包文件的方法是:后台将语言包文件处理为一堆JSON输出
	 * */
	public $il8n = null;
	
	public $conn = null;

	/**
	 * 构造函数
	 * 1 读取配置文件
	 * 2 读取语言包文件
	 * */
	public function wls(){
		$this->cfg = new wlsconfig();		
		$this->tool = new tools();	
		
		$languageFiels = $this->tool->getAllFiles('language/'.$this->cfg->language.'/');
		$arr = array();
		for($i=0;$i<count($languageFiels);$i++){
			if(substr($languageFiels[$i], strlen($languageFiels[$i])-4 ,4)=='.ini'){
				$lang = parse_ini_file($languageFiels[$i],true);
				$keys = array_keys($lang);
				for($i2=0;$i2<count($keys);$i2++){
					$arr[$keys[$i2]] = $lang[$keys[$i2]];	
				}			
			}
		}
		$this->il8n = $arr;
		$this->tool->il8n = $arr;
	}

	/**
	 * 数据库连接,提供的是短链接而不是长链接
	 * */
	public function conn(){
		if($this->conn == null){
			$this->conn = mysql_connect($this->cfg->dbhost,$this->cfg->dbuser,$this->cfg->dbpwd);
			mysql_select_db($this->cfg->dbname,$this->conn);
			mysql_query('SET NAMES UTF8');
		}
		return $this->conn;
	}

	/**
	 * 系统异常事务处理,异常事务会被记录到日志文件或日志数据库表中
	 * 日志文件会很冗余,会很占空间,需要运维人员人工清空
	 *
	 * @param $whatHappened 这是一个数组,不是个字符串
	 * */
	public function error($whatHappened,$arr=null){
		//TODO, 数据库表层次的日志记录功能还未做好
		$fileName = $this->cfg->filePath.'/log/error.txt';
		$handle=fopen($fileName,"a");
		fwrite($handle,$whatHappened."\n");
		fclose($handle);
	}

	/** 
	 * 我自己搞了一套MVC规则 
	 * 
	 * 前台(v层)全部用HTML跟JS实现,前台访问服务端的方法全部是依赖AJAX读取
	 * 服务端的访问方法是这样的:
	 * wls.php?controll=x&action=y
	 * 控制器层是由层次关系的,用下划线(_)隔开,每一个控制器都有一个自己的php文件
	 * 如果前台访问的控制器只有一层,像这样:
	 * wls.php?controller=a&action=b
	 * 那么就会指向到controller/a.php 里面的 function b(){}
	 * 如果前台访问的控制器有三层,像这样:
	 * wls.php?controller=a_a1_a2&action=b
	 * 就会指向到controller/a/a1/a2.php 里面的 function b(){}
	 * 
	 * 如果直接访问 wls.php 的话,系统会先根据配置文件判断一下安装了没有,
	 * 没有就直接跳到安装流程
	 * 如果安装好了,就直接跳到系统主页
	 * 在跳到主页之前,先要判断一下系统的安装模式,如果是集成安装(比如集成到DZX),
	 * 那么就要先读取cookie做一步 '同步登陆'
	 * */
	public function initApp(){		
		if(isset($_REQUEST['controller'])){			
			$controller = null;
			$arr = explode("_",$_REQUEST['controller']);
			$dept = count($arr);
			if($dept==1){
				//如果要访问的控制器只有一层
				include 'controller/'.$_REQUEST['controller'].'.php';
				eval('$controller = new '.$_REQUEST['controller'].'();');
			}else{
				//如果要访问的控制器不止一层,就像 controller=x_x2_x3 
				//那就要这个类文件包含进来: controller/x/x2/x3.php 
				//并初始化这个类
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
				include 'controller'.$path.'.php';
				eval('$controller = new '.$controllername.'();');
			}
			
			if(isset($_REQUEST['action'])){
				eval('$controller->'.$_REQUEST['action'].'();');
			}else{
				die('Controller must together with an action');
			}
		}else{			
			if($this->cfg->state=='uninstall'){
				header("location:wls.php?controller=install&action=setLanguage"); 	
			}else{				
				$username = '';
				include_once 'model/user.php';
				
				$m_user = new m_user();					
				if(isset($_REQUEST['cmstype']) && $this->cfg->cmstype==$_REQUEST['cmstype']){
					//如果系统是以集成安装的模式嵌入到其他系统里去得,就要运行一下 桥接 函数,
					//做同步登陆
					eval("include_once 'model/integration/".$_REQUEST['cmstype'].".php';");
					eval('$bridge = new m_integration_'.$_REQUEST['cmstype'].'();');	
					$username = $bridge->bridge();
					
				}else{	
					if($this->cfg->cmstype!=''){
						header("location:wls.php?cmstype=".$this->cfg->cmstype); 	
					}
					//如果系统是独立安装模式,就清空一下session,然后跳转到首页					
					session_start();
					
					if(!isset($_SESSION['wls_user'])){
						$username = 'guest';
					}else{
						$username = $_SESSION['wls_user']['username'];
					}				
				}
				
				$m_user->login($username);
				header("location:html/desktop.html"); 	
			}
		}
	}
}

$wls = new wls();
$wls->initApp();
?>