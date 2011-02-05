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
 * @see www.welikestudy.com
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
	 * but yet there are usefull. 
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
	 * Get the recent user.
	 * It's mainlly from php Session
	 * */
	public function getMyUser(){
		include_once dirname(__FILE__)."/".$this->c->license."/model/user.php";
		$m = new m_user();
		$myuser = $m->getUser(null,true);
		
		return $myuser;
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
	}

	/** 
	 * The client-side access the server-side in this way:
	 * wls.php?controll=x&action=y
	 * The controller has it's level structure , by '_' character
	 * Each controller has it's .php file
	 * Each action has it's function in one controller
	 * */
	public function initApp(){
		$controller = null;
		if(isset($_REQUEST['controller'])){
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
		}else{
			if($this->c->state=='uninstall'){//Jump to the installaton
				header("location:wls.php?controller=install&action=setLanguage"); 	
			}else{
				if(isset($_REQUEST['cmstype']) && $this->c->cmstype==$_REQUEST['cmstype']){
					eval("include_once 'free/model/integration/".$_REQUEST['cmstype'].".php';");
					eval('$bridge = new m_integration_'.$_REQUEST['cmstype'].'();');	

					$bridge->bridge();				
				}
				include_once 'free/model/user.php';
				$m_user = new m_user();
				$menus = $m_user->getMyMenuForDesktop();
				
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

		//Get the client-side's action ,and run the function in the controller
		if(isset($_REQUEST['action'])){
			eval('$controller->'.$_REQUEST['action'].'();');
		}else{

		}
	}
}

$wls = new wls();
//TODO Right , I use my own MVC framework ,Maybe I should upgrade it to CakePhp or ZendFrameWork? 
$wls->initApp();
?>
