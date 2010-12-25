<?php
/**
 * 如果此系统是集成到 discuz 中去的话,需要执行的步骤:
 *  1同步用户DISCUZ中用户信息到插件中的用户表,以后用户在Discuz中修改了用户信息后
 *   每次用户打开此插件,都会将更新后的数据同步过去
 *   依赖Discuz中的members表
 *  2往Discuz中的用户组表中插入用户组的内容,包括: 
 *   考试系统管理员
 *   学员
 *   老师
 *   依赖Discuz中原有的 usergroups 表,并将 radminid 作为上级编号处理
 * */
class install_discuzx extends wls {
	
	public $rewrite = array();

	/**
	 * 扩展用户表
	 * */
	public function extendUser(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select fieldid from ".$pfx."common_member_profile_setting where title = '参加的考试科目';";
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			$sql = "delete from ".$pfx."common_member_profile_setting where title = '参加的考试科目';";	
			mysql_query($sql,$conn);
			$sql = "alter table ".$pfx."common_member_profile drop column ".$temp['fieldid']." ;";
			mysql_query($sql,$conn);
		}
		$sql = "
		insert into ".$pfx."common_member_profile_setting(
			 available
			,fieldid
			,size
			,title		
			,formtype
		) values (
			 '1'
			,'myquiz'
			,200
			,'参加的考试科目'
			,text
		)";
		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		$sql = "alter table ".$pfx."common_member_profile add column ".$id." varchar(200) default '无';";
		mysql_query($sql,$conn);

		$sql = "select fieldid from ".$pfx."common_member_profile_setting where title = '错题数';";
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			$sql = "delete from ".$pfx."common_member_profile_setting where title = '错题数';";	
			mysql_query($sql,$conn);
			$sql = "alter table ".$pfx."common_member_profile drop column ".$temp['fieldid']." ;";
			mysql_query($sql,$conn);
		}
		$sql = "
		insert into ".$pfx."common_member_profile_setting(
			 available
			,fieldid
			,size
			,title	
			,formtype	
		) values (
			 '1'
			,'wrongs'
			,2
			,'错题数'
			,text
		)";
		mysql_query($sql,$conn);
		$id2 = mysql_insert_id($conn);
		$sql = "alter table ".$pfx."common_member_profile add column ".$id2." int default 0;";
		mysql_query($sql,$conn);
		
		$sql = "select fieldid from ".$pfx."common_member_profile_setting where title = '已做试卷数';";
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			$sql = "delete from ".$pfx."common_member_profile_setting where title = '已做试卷数';";	
			mysql_query($sql,$conn);
			$sql = "alter table ".$pfx."common_member_profile drop column ".$temp['fieldid']." ;";
			mysql_query($sql,$conn);
		}
		$sql = "
		insert into ".$pfx."common_member_profile_setting(
			 available
			,fieldid
			,size
			,title		
			,formtype
		) values (
			 '1'
			,'papers'
			,2
			,'已做试卷数'
			,text
		)";
		mysql_query($sql,$conn);
		$id3 = mysql_insert_id($conn);
		$sql = "alter table ".$pfx."common_member_profile add column ".$id3." int default 0;";
		mysql_query($sql,$conn);
		
		$this->rewrite['user_extend_myquiz']='myquiz';
		$this->rewrite['user_extend_wrongs']='wrongs';
		$this->rewrite['user_extend_papers']='papers';
		$this->rewrite['loginpath']='/member.php?mod=register';		
	}

	public function getUserInfo($id){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if($id==null || $id=='mine'){
			if(isset($_COOKIE['qW3_sid'])){
				$sid = $_COOKIE['qW3_sid'];
				$sql = "select uid from ".$pfx."common_session where sid = '".$sid."'";
				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$id = $temp['uid'];				
			}else{
				return array(
					'id_user'=>0,
					'id_group'=>0,
					'sex'=>0,
					'money'=>0,
					'cents'=>0,
					'count_wrongs'=>0,
					'count_papers'=>0,
					'myquiz'=>'免费试卷',
				);
			}
		}
		$sql = "
		 select  
		 ".$pfx."common_member.uid,username,gender,adminid,extgroupids,credits,extcredits2
		 ,".$this->cfg->user_extend_myquiz."
		 ,".$this->cfg->user_extend_wrongs."
		 ,".$this->cfg->user_extend_papers."
		 from 
		".$pfx."common_member,".$pfx."common_member_profile,".$pfx."common_member_count where 
		".$pfx."common_member.uid = ".$id."  
		and ".$pfx."common_member.uid = ".$pfx."common_member_count.uid
		and ".$pfx."common_member.uid = ".$pfx."common_member_profile.uid ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$temp['extgroupids'] = str_replace("\t",",",$temp['extgroupids']);
		return array(
			'id_user'=>$id,
			'title_group'=>'',
			'name'=>$temp['username'],
			'id_group'=>$temp['extgroupids'],
			'sex'=>$temp['gender'],
			'money'=>$temp['extcredits2'],
			'cents'=>$temp['credits'],
			'count_wrongs'=>$temp[$this->cfg->user_extend_wrongs],
			'count_papers'=>$temp[$this->cfg->user_extend_papers],
			'myquiz'=>$temp[$this->cfg->user_extend_myquiz],
		);		
	}
	
	public function reduceMyMoney($id,$money){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if($id==null || $id=='mine'){
			if(isset($_COOKIE['qW3_sid'])){
				$sid = $_COOKIE['qW3_sid'];
				$sql = "select uid from ".$pfx."common_session where sid = '".$sid."'";
				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$id = $temp['uid'];				
			}else{
				
			}
		}	
		$sql = "select extcredits2 from ".$pfx."common_member_count where uid =".$id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp['extcredits2']<$money){
			return false;
		}else{
			$sql = "update ".$pfx."common_member_count set extcredits2 = ".($temp['extcredits2']-$money)." where uid =".$id;
			mysql_query($sql,$conn);
			return true;
		}
	}
	
	public function initNav(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "delete from ".$pfx."common_nav where title = 'wls' ";
		mysql_query($sql,$conn);
		$sql = "
		insert into ".$pfx."common_nav (
			 name
			,title
			,url
			,available	
			,displayorder	
			,highlight
			,navtype	
		) values(
			 '在线考试学习'
			,'wls'
			,'".$_SERVER['SCRIPT_NAME']."?controller=user&action=viewProfile'
			,'1'
			,'9'
			,'0'
			,'0'
		)  ";	
		mysql_query($sql,$conn);		
	}
}
?>