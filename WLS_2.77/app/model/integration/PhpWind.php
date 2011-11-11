<?php
include_once dirname(__FILE__).'/../integration.php';
include_once dirname(__FILE__).'/../user.php';
include_once dirname(__FILE__).'/../user/group.php';
 
class m_integration_PhpWind extends m_integration implements integrate{	
	public $hash = '';
	public $winduser = '';
	
	public function bridge(){		
		return $this->synchroMe(true);
	}
	
	public function synchroConfig($path){}
	
	/**
	 * Synchro all the user informations from DisuczX to WLS.
	 * The passwords would set to be MD5 securied
	 * */
	public function synchroUsers(){}
	
	/**
	 * Not synchoro all the user groups. Only these groups setted by PhpWind admin.
	 * These default PhpWind grousp would not be synchrod.
	 * */
	public function synchroUserGroups(){}
	
	public function synchroAccess(){}

	public function StrCode($string, $action = 'ENCODE') {
		$action != 'ENCODE' && $string = base64_decode($string);
		$code = '';
		$key = substr(md5($_SERVER['HTTP_USER_AGENT'] . $this->hash), 8, 18);
		$keyLen = strlen($key);
		$strLen = strlen($string);
		for ($i = 0; $i < $strLen; $i++) {
			$k = $i % $keyLen;
			$code .= $string[$i] ^ $key[$k];
		}
		return ($action != 'DECODE' ? base64_encode($code) : $code);
	}
	
	/**
	 * Sychros the current user info from PhpWind to WLS
	 * Every time when the user jumpped from PhpWind to WLS, 
	 * this function would be fired.
	 * 
	 * @param $resetUserSession Boolen
	 * @see http://www.phpwind.net/simple/?t769804.html
	 * */
	public function synchroMe($resetUserSession = false){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "select * from ".$pfx."config where db_name = 'db_sitehash' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$sitehash = $temp['db_value'];
		$pre = substr(md5($sitehash),0,5);
		if(!isset($_COOKIE[$pre.'_winduser'])){
			return 'guest';
		}
		$this->winduser = $_COOKIE[$pre.'_winduser'];
//		echo $this->winduser;
//		echo '<br/>';

		$sql = "select * from ".$pfx."config where db_name = 'db_hash' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$this->hash = $temp['db_value'];
		
		$sql = "select 
			 ".$pfx."members.uid as uid
			,".$pfx."members.password 
			,".$pfx."members.username
			,".$pfx."members.groupid
			,".$pfx."memberdata.money
		from ".$pfx."members join ".$pfx."memberdata on ".$pfx."members.uid =  ".$pfx."memberdata.uid ";
		$res = mysql_query($sql,$conn);
		$me = null;
		while($temp=mysql_fetch_assoc($res)){
			$windpwd = md5($_SERVER['HTTP_USER_AGENT'].$temp['password'].$this->hash);
			$winduser2 = $this->StrCode($temp['uid']."\t".$windpwd."\t".'');
			$winduser2 = substr($winduser2,0,strlen($winduser2)-1);
//			echo $winduser2;
//			echo "<br/>";
			if($winduser2==$this->winduser){
				$me = $temp;
				break;
			} 
		}

		$sql2 = "select * from ".$pfx."wls_user where username = '".$me['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);		
		
		$userObj = new m_user();
		if($temp2==false){//Check the user info has synchrod , if not , synchrod					
			$uid = $userObj->insert(
				array(
					 'username'=>$me['username']
					,'password'=>$me['password']
					,'money'=>$me['money']
				)
			);
			$usergroupObj = new m_user_group();
			if($me['groupid']==3){
				$data = array(
					 'id_level_group'=>'10'
					,'username'=>$me['username']
				);
			}else{
				$data = array(
					 'id_level_group'=>'11'
					,'username'=>$me['username']
				);
			}
			$usergroupObj->linkUser($data);
		}else{//Synchrod the money. the PhpWind's money system is complex
			$data = array(
				 'id'=>$temp2['id']
				,'money'=>$me['money']
				,'password'=>$me['password']
			);
			
			$userObj->update($data);
		}
		return $me['username'];
	}

	/**
	 * PhpWind has it's own money and credits system.
	 * First,the WLS' money costted , then the PhpWind's money reduced.
	 * 
	 * @param $username 
	 * */
	public function synchroMoney($username){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "select uid from ".$pfx."members where username = '".$username."' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$uid = $temp['uid'];
		
		$sql = "select money from ".$pfx."wls_user where username = '".$username."' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$money = $temp['money'];		
		$sql = "update ".$pfx."memberdata set money = ".$money." where uid = ".$uid;
		mysql_query($sql,$conn);
	}
}
?>