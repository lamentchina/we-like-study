<?php
include_once dirname(__FILE__).'/../integration.php';

class m_integration_Joomla extends m_integration implements integrate{

	public $user = null;

	public function bridge(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		include_once dirname(__FILE__).'/../../../../../../configuration.php';
		$c = new JConfig();
		$key = md5(md5($c->secret.'site'));

		$sql = "select username,guest from ".$pfx."session where session_id = '".$_COOKIE[$key]."' ";
		$res = mysql_query($sql,$conn);
		if($res==false){
			die('Bridge Error');
		}
		$temp = mysql_fetch_assoc($res);

		if((int)$temp['guest']==1){			
			return 'guest';
		}

		$sql2 = "select * from ".$pfx."wls_user where username = '".$temp['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		
		if($res2==false || (mysql_fetch_assoc($res2)==false) ){//这个用户的信息还没有同步过来,需要实施数据插入
			include_once dirname(__FILE__).'/../user.php';
			$userObj = new m_user();
			$temp['money']=100;
			$temp['password']=$temp['username'];
			$temp['name']=$temp['username'];
			unset($temp['guest']);
			$uid = $userObj->insert($temp);
			include_once dirname(__FILE__).'/../user/group.php';
			$usergroupObj = new m_user_group();
			$data = array(
				 'id_level_group'=>'11'
				 ,'username'=>$temp['username']
			);
			$usergroupObj->linkUser($data);
		}else{//No money to synchro
				
		}

		return $temp['username'];
	}

	public function synchroConfig($path){}

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

	public function synchroaccesss(){}

	/**
	 * Only when the user jumpped from joomla to wls
	 *
	 * @param $resetUserSession If true , the Session would be rest
	 * */
	public function synchroMe($resetUserSession = false){}

	public function synchroMoney($username){

	}
}
?>