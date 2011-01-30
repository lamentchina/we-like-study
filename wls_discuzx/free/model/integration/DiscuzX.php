<?php
include_once dirname(__FILE__).'/../integration.php';
 
class m_integration_DiscuzX extends m_integration implements integrate{
	
	public $cookiepre = null;
	public $sessionid = null;
	
	public function bridge(){
		$this->synchroConfig(dirname(__FILE__).'/../../../../../../../config/config_global.php');
		$this->synchroMe(true);
	}
	
	/**
	 * 同步配置文件
	 * */
	public function synchroConfig($path){
		include_once $path;

		$this->cookiepre = $_config['cookie']['cookiepre'];
		$this->sessionid = $_COOKIE[$this->cookiepre.'2132_sid'];
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
			 and sid = '".$this->sessionid."';
		";

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp==false)return;

		$sql2 = "select * from ".$pfx."wls_user where username = '".$temp['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);
		
		include_once dirname(__FILE__).'/../user.php';
		$userObj = new m_user();
		if($temp2==false){//这个用户的信息还没有同步过来,需要实施数据插入
			unset($temp['sid']);
		
			$uid = $userObj->insert($temp);
			include_once dirname(__FILE__).'/../user/group.php';
			$usergroupObj = new m_user_group();
			$data = array(
				 'id_level_group'=>'11'
				,'username'=>$temp['username']
			);
			$usergroupObj->linkUser($data);
		}else{//此用户的信息已经同步过来了,那么就将 金钱 同步一下
			$data = array(
				 'id'=>$temp2['id']
				,'money'=>$temp['money']
				,'password'=>$temp['password']
			);
			$userObj->update($data);
		}
		if($resetUserSession==true){
			if(!isset($_SESSION))session_start();
			if(isset($_SESSION['wls_user'])){
				unset($_SESSION['wls_user']);
			}
			session_destroy();//TODO 
//			echo 1241234;exit();
//			print_r($temp);exit();
			$userObj->login($temp['username'],$temp['password']);
			
		}
	}	
}
?>