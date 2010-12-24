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
	
	/**
	 * 同步用户信息
	 * 插件本身含有一张 用户信息表 ,表内包含有此插件需要的一些字段	 * 
	 * */
	public function updateUser($id=null){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
				
		if(isset($_REQUEST['id']))$id = $_REQUEST['id'];
		$id_ = $id;
		if($id<10)$id_ = '0'.$id;
		
		$sql_ = "select * from ".$pfx."members where uid = ".$id;
		$res_ = mysql_query($sql_,$conn);
		$temp_ = mysql_fetch_array($res_);	
		$temp_['extgroupids'] = str_replace("\t",",",$temp_['extgroupids']);
		$sql = "select grouptitle from ".$pfx."usergroups where groupid in (".$temp_['extgroupids'].");";
//		echo $sql;
		$res = mysql_query($sql,$conn);
		$title_group = '';
		while($temp = mysql_fetch_assoc($res)){
			$title_group .= $temp['grouptitle'].",";
		}
		$title_group = substr($title_group,0,strlen($title_group)-1);
	
//		print_r($title_group);
		
		$sql = "select id from ".$pfx."wls_user where id_user = ".$id;
		$res = mysql_query($sql,$conn);
		if(!$res){
			$sql = "update ".$pfx."wls_user set photo = '/uc_server/data/avatar/000/00/00/".$id_."_avatar_big.jpg',sex = ".$temp_['gender']."  where id_user = ".$id.";";
		}else{
			$imgpath = 'file/images/user/none.jpg';
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/uc_server/data/avatar/000/00/00/".$id_."_avatar_big.jpg")){
				$imgpath = "/uc_server/data/avatar/000/00/00/".$id_."_avatar_big.jpg";
			}
			$sql = "insert into ".$pfx."wls_user (id_user,photo,sex,name,id_group,title_group) values(".$id.",'".$imgpath."',".$temp_['gender'].",'".$temp_['username']."','".$temp_['extgroupids']."','".$title_group."');";
		}
		mysql_query($sql,$conn);
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
		
		$this->rewirteConfig(array(
			'group_admin'=>$id1,
			'group_teacher'=>$id2,
			'group_student'=>$id3,
		));
		
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
		
		$arr = array(
			'host'=>$this->cfg->host,
			'user'=>$this->cfg->user,
			'password'=>$this->cfg->password,
			'db'=>$this->cfg->db,
			'dbprefix'=>$this->cfg->dbprefix,
			'cmstype'=>$this->cfg->cmstype,
			'debug'=>$this->cfg->debug,
			'title'=>$this->cfg->title,
			'loginpath'=>$this->cfg->loginpath,
			'group_admin'=>$this->cfg->group_admin,
			'group_teacher'=>$this->cfg->group_teacher,
			'group_student'=>$this->cfg->group_student,
		);
		if($foo!=null){
			$keys = array_keys($foo);
			for($i=0;$i<count($foo);$i++){
				$arr[$keys[$i]] = $foo[$keys[$i]];
			}
		}
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
	}
}
?>