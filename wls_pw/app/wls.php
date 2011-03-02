<?php
include_once 'interface.php';
include_once 'config.php';
include_once 'free/model/tools.php';

/**
 * We Like Study! 
 * 
 * This is an open-source project
 * It's written by a Chinese , from mainland.
 * You are angry about this system's code? Now you know why ,
 * Ha ha ,because I'm a Chinese , my English is poor. 
 * Sorry dude, I suggest you to just view the code 
 * and get rid of all the comments.
 * 
 * I hope this software would be popular , I don't care if I can earn mony from this. I wish one day ,when 
 * this software was used everywhere , so I can say one word to the girl once I loved but despised me:
 * I can do something great, I'm not that useless.  
 *
 * @see www.welikestudy.com
 * @author wei1224hf
 * @copyright www.wei1224hf.com 
 * @version 1.0
 * */
class wls{

	/**
	 * The configure stuff, it's in config.php
	 * */
	public $c = null;
	
	/**
	 * Some functions which has nothing to do with the system's logic, 
	 * but yet they are usefull. 
	 * So I unify them in the tool class. 
	 * It's in tool.php
	 * */
	public $t = null;
	
	/**
	 * il8n supportted 
	 * TODO
	 * */
	public $lang = null;

	public function wls(){
		$this->c = new wlsconfig();
		$inifile = 'language/'.$this->c->language.".ini";
		$lang = parse_ini_file($inifile);
		$this->lang = $lang;
		$this->t = new tools();
		$this->t->lang = $lang;		
	}

	/**
	 * Request the config stuff.
	 * TODO How to make the connection better?
	 * */
	public function conn(){
		$conn = mysql_connect($this->c->dbhost,$this->c->dbuser,$this->c->dbpwd);
		mysql_select_db($this->c->dbname,$conn);
		mysql_query('SET NAMES UTF8');
		return $conn;
	}

	/**
	 * If there's someting wrong...
	 *
	 * @param $whatHappened It's an array
	 * */
	public function error($whatHappened,$arr=null){
		//TODO
		$fileName = $this->c->filePath.'/log/error.txt';
		$handle=fopen($fileName,"a");
		fwrite($handle,$whatHappened."\n");
		fclose($handle);
//		exit();
	}

	/** 
	 * The client-side access the server-side in this way:
	 * wls.php?controll=x&action=y
	 * The controller has it's level structure , by '_' character
	 * Each controller has it's .php file
	 * Each action has it's function in one controller
	 * 
	 * Or , just visit as wls.php
	 * */
	public function initApp(){
		if(isset($_REQUEST['controller'])){
			$controller = null;
			$arr = explode("_",$_REQUEST['controller']);
			$dept = count($arr);
			if($dept==1){
				//If the controller was only one level ,like controller=x
				//Then just include the  controller/x.php and init the class
				include $this->c->license.'/controller/'.$_REQUEST['controller'].'.php';
				eval('$controller = new '.$_REQUEST['controller'].'();');
			}else{
				//If the controller more than one level,like controller=x_x2_x3 
				//Then will include the file: controller/x/x2/x3.php 
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
			
			//Get the client-side's action ,and run the function in the controller
			if(isset($_REQUEST['action'])){
				eval('$controller->'.$_REQUEST['action'].'();');
			}else{
	
			}
		}else{
			//If it's uninstalled , Jump to the installation
			if($this->c->state=='uninstall'){
				header("location:wls.php?controller=install&action=setLanguage"); 	
			}else{
				//View WLS's main page. Ther user's session would be reset.
				$username = '';
				include_once 'free/model/user.php';
				$m_user = new m_user();					
				if(isset($_REQUEST['cmstype']) && $this->c->cmstype==$_REQUEST['cmstype']){
					//When it's installed as CMS integratted ,
					//Visit as wls.php?cmstype=joomla or wls.php?cmstype=discuzx
					//The client must have been refreshed,so the whole sessions would be reset
					//System should get the current user from CMS
					eval("include_once 'free/model/integration/".$_REQUEST['cmstype'].".php';");
					eval('$bridge = new m_integration_'.$_REQUEST['cmstype'].'();');	
					$username = $bridge->bridge();
					
				}else{	
					if($this->c->cmstype!=''){
						header("location:wls.php?cmstype=".$this->c->cmstype); 	
					}
					//Just visit as wls.php, The system is not integratted in any CMS
					//It's stand alone.The sessions would be resetted too.
					session_start();
					if(!isset($_SESSION['wls_user'])){
						$username = 'guest';
					}else{
						$username = $_SESSION['wls_user']['username'];
					}				
				}
				
				$m_user->login($username);			
				$menuStff = $m_user->getMyMenuWithShortCut();
				$modules = $menuStff['modules'];
				$quickstart = $menuStff['quickstart'];
				$shortcut = $menuStff['shortcut'];
				$menus = $menuStff['menus'];				
				include_once "free/view/qWikiOffice/qDeskTop.php";
			}
		}
	}
}

$wls = new wls();
//TODO Right , I use my own MVC framework ,Maybe I should upgrade it to CakePhp or ZendFrameWork? 
$wls->initApp();
?>
