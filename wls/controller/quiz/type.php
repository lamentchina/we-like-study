<?php
/**
 * 考试科目类型
 * */
class quiz_type extends wls{
	
	/**
	 * 初始化数据库表结构
	 * 在开发阶段,数据库表结构可能会经常变动
	 * 考试科目类型
	 * */
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "drop table if exists ".$pfx."wls_quiz_type;";
		mysql_query($sql,$conn);
		$sql = "				
			create table ".$pfx."wls_quiz_type(
				 id int AUTO_INCREMENT primary key 	comment '自动编号'
				,id_parent int default 0			comment '上级编号'
				,haschild int default 0				comment '是否含有下级科目'
				,title varchar(200) 				comment '科目名称'
				,creator varchar(200) not null		comment '创建者'
				,ordering int default 0 			comment '排序'
				,date_created datetime not null 	comment '创建时间'
				,count_paper int default 0			comment '试卷数目'
				,count_question int default 0 		comment '题目数目'
				,count_joined int default 0 		comment '参加的人数'
				,knowledge varchar(200) default '0' comment '知识点'
				,knowledge_parts varchar(200) default '0'	comment '知识点分值组成'
				,description text					comment '描述'
			) DEFAULT CHARSET=utf8 					comment='考试科目';	
			";
		mysql_query($sql,$conn);
		
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
	
	public function initTestData(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,haschild,title,creator,date_created)
		VALUES ('0','1', '英语', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,title,creator,date_created)
		VALUES ('".$id."', '公共英语四级', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,title,creator,date_created)
		VALUES ('".$id."', '大学英语四级', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,title,creator,date_created)
		VALUES ('".$id."', '大学英语六级', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
		
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,haschild,title,creator,date_created)
		VALUES ('0','1', '公务员', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);		
		
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,title,creator,date_created)
		VALUES ('".$id."', '公共基础', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
		$sql = "INSERT INTO ".$pfx."wls_quiz_type(id_parent,title,creator,date_created)
		VALUES ('".$id."', '行政能力', 'admin', '".date('Y-m-d')."')";
		mysql_query($sql,$conn);
	}
	
	public function getListWithLevel(){
		$data = $this->getList('array');
		$data = $data['rows'];
		$arr = array();
		for($i=0;$i<count($data);$i++){
			if($data[$i]['id_parent']==0){
				$arr[$data[$i]['id']] = $data[$i];
			}else{
				$arr[$data[$i]['id_parent']]['child'][] = $data[$i];
			}
			if($data[$i]['haschild']==1){
				$arr[$data[$i]['id']]['child'] = array();
			}			
		}
		return $arr;
	}
	
	public function getList($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['page']))$page=$_REQUEST['page'];
		if($rows==null && isset($_REQUEST['rows']))$rows=$_REQUEST['rows'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_decode($_REQUEST['search']);
		if($page==null)$page = 1;
		if($rows==null)$rows = 20;
		if($returnType==null)$returnType = 'json';

		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();
		
		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_quiz_type'){
					$where .= " and id_quiz_type in (".$search[$keys[$i]].") ";
				}
			}
		}
		
		$sql = "select * from ".$pfx."wls_quiz_type  ".$where;
	
		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		
		$sql = "select count(*) as total from ".$pfx."wls_quiz_type ".$where;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];
				
		header("Content-type: text/html; charset=utf-8"); 
		switch($returnType) {
			case 'json':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$arr,
					'sql'=>$sql,
					'total'=>$total,
					'pagesize'=>$rows,
				);
				unset($arr);
				echo json_encode($arr2);
			break;
			case 'xml':
				//TODO
			case 'array':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$arr,
					'sql'=>$sql,
					'total'=>$total,
					'pagesize'=>$rows,
				);
				return $arr2;
			break;
			default:
				echo 'returnType is not defined';
			break;
		}
	}	
	
	public function getDWZlist($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['pageNum']))$page=$_REQUEST['pageNum'];
		if($rows==null && isset($_REQUEST['numPerPage']))$rows=$_REQUEST['numPerPage'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search'])){
			$_REQUEST['search'] = str_replace("'","\"",$_REQUEST['search']);
			$search =json_decode($_REQUEST['search'],true);
		}
		if($page==null)$page = 1;
		if($rows==null)$rows = 10;
		if($returnType==null)$returnType = 'html';
		
		$data = $this->getList('array',$page,$rows,$search);
		
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();
		
		include_once 'view/quiz/type/list.php';
	}		
	
	public function getOne(){
		
	}
	
	public function viewList(){
		echo "功能未完成";
	}
	
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
	
	public function updateMyquiz(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();		
		
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo('mine');	
		
		$sql = "select title_quiz_type from ".$pfx."wls_quiz_type_record where id_user = ".$userinfo['id_user'];
		$res = mysql_query($sql,$conn);
		$str = "";
		while($temp = mysql_fetch_assoc($res)){
			$str .= $temp['title_quiz_type'].",";
		}
		$str = substr($str,0,strlen($str)-1);
		$sql = "update ".$pfx."wls_user set myquiz = '".$str."' where id_user = ".$userinfo['id_user'];
		mysql_query($sql,$conn);
	}
	
	public function updateType($id_quiz_type=null){
		if($id_quiz_type==null && isset($_REQUEST['id_quiz_type']))$id_quiz_type = $_REQUEST['id_quiz_type'];
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();				
		$sql = "select count(*) as count_ from ".$pfx."wls_quiz_type_record where id_quiz_type = ".$id_quiz_type;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$sql = "update ".$pfx."wls_quiz_type set count_joined = ".$temp['count_']." where id = ".$id_quiz_type;
//		echo $sql;
		mysql_query($sql,$conn);
	}
	
	public function check($id_user,$id_quiz_paper){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "select count(*) as count_ from ".$pfx."wls_quiz_type_record where id_user = ".$id_user." and id_quiz_type = ".$id_quiz_paper;
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			if( $temp['count_']>0){
				return true;
			}else{
				return false;
			}			
		}else{
			return false;
		}		
	}	
}
?>