<?php
include_once dirname(__FILE__).'/../integration.php';
 
class m_integration_DiscuzX extends m_integration implements integrate{	
	public $cookiepre = null;
	public $sessionid = null;
	
	public function bridge(){
		$this->synchroConfig(dirname(__FILE__).'/../../../../../../../config/config_global.php');
		return $this->synchroMe(true);
	}
	
	public function synchroConfig($path){
		include_once $path;
		$this->cookiepre = $_config['cookie']['cookiepre'];
		if(!isset($_config['server'])){
			$this->sessionid = $_COOKIE[$this->cookiepre.'sid'];
		}else{
			$this->sessionid = $_COOKIE[$this->cookiepre.'2132_sid'];
		}
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
	 * Sychros the current user info from DsicuzX to WLS
	 * Every time when the user jumpped from DiscuzX to WLS, 
	 * this function would be fired.
	 * 
	 * @param $resetUserSession Boolen
	 * */
	public function synchroMe($resetUserSession = false){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		$sql = "select 
			".$pfx."common_member.username
			,".$pfx."common_member.password
			,".$pfx."common_member_count.extcredits2 as money 
			,".$pfx."common_session.sid
			 from 
			 ".$pfx."common_member
			,".$pfx."common_member_count
			,".$pfx."common_session
			 where 			
			".$pfx."common_member.uid = ".$pfx."common_member_count.uid and ".$pfx."common_session.uid = ".$pfx."common_member.uid
			 and ".$pfx."common_session.sid = '".$this->sessionid."';
		";
		
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		if($temp==false){
			
			return 'guest';
		}

		$sql2 = "select * from ".$pfx."wls_user where username = '".$temp['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);
		
		include_once dirname(__FILE__).'/../user.php';
		$userObj = new m_user();
		if($temp2==false){//Check the user info has synchrod , if not , synchrod
			unset($temp['sid']);
		
			$uid = $userObj->insert($temp);
			include_once dirname(__FILE__).'/../user/group.php';
			$usergroupObj = new m_user_group();
			$data = array(
				 'id_level_group'=>'11'
				,'username'=>$temp['username']
			);
			$usergroupObj->linkUser($data);
		}else{//Synchrod the money. the DiscuzX's money system is complex
			$data = array(
				 'id'=>$temp2['id']
				,'money'=>$temp['money']
				,'password'=>$temp['password']
			);
			$userObj->update($data);
		}
		return $temp['username'];
	}

	/**
	 * DiscuzX has it's own money and credits system.
	 * First,the WLS' money costted , then the DiscuzX's money reduced.
	 * 
	 * @param $username 
	 * */
	public function synchroMoney($username){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$sql = "select uid from ".$pfx."common_member where username = '".$username."' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$uid = $temp['uid'];
		
		$sql = "select money from ".$pfx."wls_user where username = '".$username."' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$money = $temp['money'];		
		$sql = "update ".$pfx."common_member_count set extcredits2 = ".$money." where uid = ".$uid;
		mysql_query($sql,$conn);
	}
}
?>