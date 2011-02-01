<?php
include_once dirname(__FILE__).'/../integration.php';
 
class m_integration_Joomla extends m_integration implements integrate{
	
	public $user = null;
	
	public function bridge(){
		$this->synchroConfig(null);
		if($this->user==null)return false;
		$this->synchroMe(true);
	}
	
	/**
	 * 同步配置文件
	 * */
	public function synchroConfig($path){
		
		define('_JEXEC', 1);
		define('DS', DIRECTORY_SEPARATOR);
		
		if (file_exists(dirname(__FILE__) . '/../../../../../../defines.php')) {
			include_once dirname(__FILE__) . '/../../../../../../defines.php';
		}
		
		if (!defined('_JDEFINES')) {
			define('JPATH_BASE', dirname(__FILE__).'/../../../../../../');
			require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
		}
		
		require_once JPATH_BASE.DS.'includes'.DS.'framework.php';
		
		// Mark afterLoad in the profiler.
		JDEBUG ? $_PROFILER->mark('afterLoad') : null;
		
		// Instantiate the application.
		$app = JFactory::getApplication('site');
		
		$user = JFactory::getUser();
		if($user->id==0)return;
		
		$this->user = array(
			 'username'=>$user->username
			,'name'=>$user->name
			,'password'=>'joomla'
		);		
	}
	
	/**
	 * 同步用户数据
	 * */
	public function synchroUsers(){}
	
	/**
	 * 同步用户组数据
	 * */
	public function synchroUserGroups(){}
	
	/**
	 * 同步权限数据
	 * */
	public function synchroPrivileges(){}

	/**
	 * 同步当前用户 
	 * 只在用户从Joomla跳入到WLS的时候启用
	 * */
	public function synchroMe($resetUserSession = false){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql2 = "select * from ".$pfx."wls_user where username = '".$this->user['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);
		
		include_once dirname(__FILE__).'/../user.php';
		$userObj = new m_user();
		if($temp2==false){//这个用户的信息还没有同步过来,需要实施数据插入
		
			$uid = $userObj->insert($this->user);
			include_once dirname(__FILE__).'/../user/group.php';
			$usergroupObj = new m_user_group();
			$data = array(
				 'id_level_group'=>'11'
				,'username'=>$this->user['username']
			);
			$usergroupObj->linkUser($data);
		}else{//此用户的信息已经同步过来了,那么就将 金钱 同步一下

		}
		if($resetUserSession==true){
			if(isset($_SESSION['wls_user'])){
				unset($_SESSION['wls_user']);
			}		

			$userObj->login($temp2['username'],$temp2['password']);
		}
	}

	public function synchroMoney($username){

	}
}
?>