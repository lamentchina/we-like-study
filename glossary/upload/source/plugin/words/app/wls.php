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
 * @copyright www.welikestudy.com
 * @version 1.0
 * */
class wls{

	/**
	 * The configure stuff, it's in config.php
	 * */
	public $cfg = null;
	
	/**
	 * Some functions which has nothing to do with the system's logic, 
	 * but yet they are usefull. 
	 * So I unify them in the tool class. 
	 * It's in tool.php
	 * */
	public $tool = null;
	
	/**
	 * il8n supportted 
	 * Each module has it's own il8n file ,
	 * They are in the folder : language 
	 * */
	public $il8n = null;
	
	public $conn = null;

	public function wls(){
		//sleep(2);
		$this->cfg = new wlsconfig();		
		$this->tool = new tools();	

		//Each module has an il8n file.
		//Read each il8n file , and parse them
		$languageFiels = $this->tool->getAllFiles('language/'.$this->cfg->language.'/');
		//print_r($languageFiels);exit();
		$arr = array();
		for($i=0;$i<count($languageFiels);$i++){
			//echo substr($languageFiels[$i], strlen($languageFiels[$i])-4 ,4);
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
	 * Request the config stuff first
	 * All the system's charset , together with the databse's charset 
	 * must set to be UTF-8
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
	 * If there's someting wrong...
	 *
	 * @param $whatHappened It's an array
	 * */
	public function error($whatHappened,$arr=null){
		//TODO, should do something in database
		$fileName = $this->cfg->filePath.'/log/error.txt';
		$handle=fopen($fileName,"a");
		fwrite($handle,$whatHappened."\n");
		fclose($handle);
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
				include $this->cfg->license.'/controller/'.$_REQUEST['controller'].'.php';
				eval('$controller = new '.$_REQUEST['controller'].'();');
			}else{
				//If the controller more than one level,like controller=x_x2_x3 
				//include the file: controller/x/x2/x3.php 
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
				include $this->cfg->license.'/controller'.$path.'.php';
				eval('$controller = new '.$controllername.'();');
			}
			
			//Get the client-side's action ,and run the function in the controller
			if(isset($_REQUEST['action'])){
				eval('$controller->'.$_REQUEST['action'].'();');
			}else{
	
			}
		}else{
			
			//If it's uninstalled , Jump to the installation
			if($this->cfg->state=='uninstall'){
				header("location:wls.php?controller=install&action=setLanguage"); 	
			}else{
				
				//View WLS's main page. Ther user's session would be reset.
				$username = '';
				include_once 'free/model/user.php';
				
				$m_user = new m_user();					
				if(isset($_REQUEST['cmstype']) && $this->cfg->cmstype==$_REQUEST['cmstype']){
					//When it's installed as CMS integratted ,
					//Visit as wls.php?cmstype=joomla or wls.php?cmstype=discuzx
					//The client must have been refreshed,so the whole sessions would be reset
					//System should get the current user from CMS
					eval("include_once 'free/model/integration/".$_REQUEST['cmstype'].".php';");
					eval('$bridge = new m_integration_'.$_REQUEST['cmstype'].'();');	
					$username = $bridge->bridge();
					
				}else{	
					if($this->cfg->cmstype!=''){
						header("location:wls.php?cmstype=".$this->cfg->cmstype); 	
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
				
				include_once "free/view/qWikiOffice/qDeskTop.php";
			}
		}
	}
}

$wls = new wls();
//TODO Right , I use my own MVC framework ,Maybe I should upgrade it to CakePhp or ZendFrameWork? 
$wls->initApp();
?>