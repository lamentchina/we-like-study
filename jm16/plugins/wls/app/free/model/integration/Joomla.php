<?php
include_once dirname(__FILE__).'/../integration.php';
 
class m_integration_Joomla extends m_integration implements integrate{
	
	public $user = null;
	
	public function bridge(){
		$this->synchroConfig(dirname(__FILE__).'/../../../../../../configuration.php');
		if($this->user!=null){
			$this->synchroMe(true);
		}
	}
	
	public function synchroConfig($path){	
		
		include_once $path;
		$c = new JConfig();
		$key = md5(md5($c->secret.'site'));
		
		if(!isset($_COOKIE[$key])){
			echo $this->lang['JoomlaFirst'];
			exit();
		}
		
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$sql = "select username,guest from ".$pfx."session where session_id = '".$_COOKIE[$key]."' ";
		
		$res = mysql_query($sql,$conn);
		if($res==false){
			echo $this->lang['JoomlaFirst'];
			exit();
		}
		$temp = mysql_fetch_assoc($res);
		
		if((int)$temp['guest']==1){
			include_once dirname(__FILE__).'/../user.php';
			$userObj = new m_user();
			$userObj->login("guest","guest");
			return;
		}
		$this->user = array(
			 'username'=>$temp['username']
		);
	}
	
	/**
	 * Synchro all the user informations from DisuczX to WLS.
	 * The passwords would set to be MD5 securied
	 * */
	public function synchroUsers(){}
	
	/**
	 * Not synchoro all the user groups. Only these groups setted by DiscuzX admin.
	 * These default DiscuzX grousp would not be synchrod.
	 * */
	public function synchroUserGroups(){}
	
	public function synchroPrivileges(){}

	/**
	 * Only when the user jumpped from joomla to wls
	 * 
	 * @param $resetUserSession If true , the Session would be rest
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
			$this->user['money']=100;
			$this->user['password']=$this->user['username'];
			$this->user['name']=$this->user['username'];
			$temp2 = $this->user;
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
			if(!isset($_SESSION))session_start();
			if(isset($_SESSION['wls_user'])){
				unset($_SESSION['wls_user']);
			}
//			session_unset();
//			session_destroy();
			
			
			$userObj->login($temp2['username'],$temp2['password']);

		}
	}

	public function synchroMoney($username){

	}
}
?>