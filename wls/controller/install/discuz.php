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
class install_discuz extends wls {
	
	public $rewrite = array();

	/**
	 * 扩展用户表
	 * */
	public function extendUser(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select fieldid from ".$pfx."profilefields where title = '参加的考试科目';";
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			$sql = "delete from ".$pfx."profilefields where title = '参加的考试科目';";	
			mysql_query($sql,$conn);
			$sql = "alter table ".$pfx."memberfields drop column field_".$temp['fieldid']." ;";
			mysql_query($sql,$conn);
		}
		$sql = "
		insert into ".$pfx."profilefields(
			 available
			,size
			,title		
		) values (
			 '1'
			,200
			,'参加的考试科目'
		)";
		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		$sql = "alter table ".$pfx."memberfields add column field_".$id." varchar(200) default '无';";
		mysql_query($sql,$conn);

		$sql = "select fieldid from ".$pfx."profilefields where title = '错题数';";
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			$sql = "delete from ".$pfx."profilefields where title = '错题数';";	
			mysql_query($sql,$conn);
			$sql = "alter table ".$pfx."memberfields drop column field_".$temp['fieldid']." ;";
			mysql_query($sql,$conn);
		}
		$sql = "
		insert into ".$pfx."profilefields(
			 available
			,size
			,title		
		) values (
			 '1'
			,2
			,'错题数'
		)";
		mysql_query($sql,$conn);
		$id2 = mysql_insert_id($conn);
		$sql = "alter table ".$pfx."memberfields add column field_".$id2." int default 0;";
		mysql_query($sql,$conn);
		
		$sql = "select fieldid from ".$pfx."profilefields where title = '已做试卷数';";
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			$sql = "delete from ".$pfx."profilefields where title = '已做试卷数';";	
			mysql_query($sql,$conn);
			$sql = "alter table ".$pfx."memberfields drop column field_".$temp['fieldid']." ;";
			mysql_query($sql,$conn);
		}
		$sql = "
		insert into ".$pfx."profilefields(
			 available
			,size
			,title		
		) values (
			 '1'
			,2
			,'已做试卷数'
		)";
		mysql_query($sql,$conn);
		$id3 = mysql_insert_id($conn);
		$sql = "alter table ".$pfx."memberfields add column field_".$id3." int default 0;";
		mysql_query($sql,$conn);
		
		$this->rewrite['user_extend_myquiz']=$id;
		$this->rewrite['user_extend_wrongs']=$id2;
		$this->rewrite['user_extend_papers']=$id3;

	}

	public function getUserInfo($id){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if($id==null || $id=='mine'){
			if(isset($_COOKIE['qW3_sid'])){
				$sid = $_COOKIE['qW3_sid'];
				$sql = "select uid from ".$pfx."sessions where sid = '".$sid."'";
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
		$sql = "select 
		  ".$pfx."members.uid,username,gender,groupid,extgroupids,credits,extcredits2
		 ,field_".$this->cfg->user_extend_myquiz."
		 ,field_".$this->cfg->user_extend_wrongs."
		 ,field_".$this->cfg->user_extend_papers."
		 from 
		".$pfx."members,".$pfx."memberfields where ".$pfx."members.uid = ".$id." and ".$pfx."members.uid = ".$pfx."memberfields.uid ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		return array(
			'id_user'=>$id,
			'title_group'=>'',
			'name'=>$temp['username'],
			'id_group'=>$temp['groupid'],
			'sex'=>$temp['gender'],
			'money'=>$temp['extcredits2'],
			'cents'=>$temp['credits'],
			'count_wrongs'=>$temp["field_".$this->cfg->user_extend_wrongs],
			'count_papers'=>$temp["field_".$this->cfg->user_extend_papers],
			'myquiz'=>$temp["field_".$this->cfg->user_extend_myquiz],
		);
		
	}

	/**
	 * 往Discuz中的用户组表中插入用户组信息
	 * */
	public function initGroup(){
		if($this->cfg->debug!=1){
			$this->hackAttack();
			return;
		}
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "
		delete from ".$pfx."usergroups where radminid = 0 and grouptitle in ('管理员','教师','学员'); ";
		mysql_query($sql,$conn);

		$sql = "
		insert into ".$pfx."usergroups(
			 radminid
			,type
			,grouptitle
		)values(
			 '0'
			,'special'
			,'管理员'
		) ";
		mysql_query($sql,$conn);
		$id1 = mysql_insert_id($conn);
		$sql = "
		insert into ".$pfx."usergroups(
			 radminid
			,type
			,grouptitle
		)values(
			 '0'
			,'special'
			,'教师'
		) ";
		mysql_query($sql,$conn);
		$id2 = mysql_insert_id($conn);
		$sql = "
		insert into ".$pfx."usergroups(
			 radminid
			,type
			,grouptitle
		)values(
			 '0'
			,'special'
			,'学员'
		) ";
		mysql_query($sql,$conn);
		$id3 = mysql_insert_id($conn);

		$this->rewrite['group_admin']=$id1;
		$this->rewrite['group_teacher']=$id2;
		$this->rewrite['group_student']=$id3;

	}

	/**
	 * 重写配置文件
	 * **/
	public function rewirteConfig($foo=null){
		if($this->cfg->debug!=1){
			$this->hackAttack();
			return;
		}
		$file_name = "config.php";
		if(!$file_handle = fopen($file_name,"w")){
			die("不能打開$file_name");
		}
		$arr = array();
		$cfg = (array)$this->cfg;
		$keys = array_keys($cfg);
		for($i=0;$i<count($keys);$i++){
			eval('$arr["'.$keys[$i].'"] = $this->cfg->'.$keys[$i].';');
		}
		
		
		
		if($foo!=null){
			$keys = array_keys($foo);
			for($i=0;$i<count($foo);$i++){
				$arr[$keys[$i]] = $foo[$keys[$i]];
			}
		}
		print_r($arr);
		$content = "
<?php
class wlsconfig{
";
		$keys = array_keys($arr);
		for($i=0;$i<count($arr);$i++){
			$content .= "
			public \$".$keys[$i]." = '".$arr[$keys[$i]]."';";
		}
		$content.=
"
}
?>
		";
		fwrite($file_handle,$content);
		fclose($file_handle);
	}
}
?>