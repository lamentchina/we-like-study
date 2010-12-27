<?php
/**
 * 记录哪个人报了哪个考试科目
 * 哪个用户组报了哪个考试科目
 * */
class quiz_type_record extends wls{
	
	/**
	 * 添加一条记录
	 * */
	public function add(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo('mine');

		$sql = "select title from ".$pfx."wls_quiz_type where id = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$data = array(
			'id_quiz_type'=>$_REQUEST['id'],
			'title_quiz_type'=>$temp['title'],
			'id_user'=>$userinfo['id_user'],
			'username'=>$userinfo['name'],
			'date_created'=>date('Y-m-d'),
		);
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_type_record (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);

		$this->updateMyquiz();
		$this->updateType($_REQUEST['id']);

		header('Location: wls.php?controller=quiz_type&action=getDWZlist');
	}

	public function remove(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo('mine');
		$sql = "delete from  ".$pfx."wls_quiz_type_record where id_user = ".$userinfo['id_user']." and id_quiz_type =  ".$_REQUEST['id'];
		mysql_query($sql,$conn);

		$this->updateMyquiz();
		$this->updateType($_REQUEST['id']);

		header('Location: wls.php?controller=quiz_type&action=getDWZlist');
		//		echo '<div style="padding:15px;"><a class="button" href="#" onclick=>已退出</a></div>';
	}
	
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_quiz_type_record;";
		mysql_query($sql,$conn);
		$sql = "				
			create table ".$pfx."wls_quiz_type_record(
				 id int AUTO_INCREMENT primary key 	comment '自动编号'
				,id_user int default 0 				comment '用户编号'
				,id_quiz_type int default 0 		comment '考试科目编号'
				,username varchar(200) 				comment '用户名'
				,title_quiz_type varchar(200)		comment '科目名称'
				,date_created datetime not null 	comment '创建时间'
				
			) DEFAULT CHARSET=utf8 					comment='考试科目记录';	
			";
		mysql_query($sql,$conn);
	}
}
?>