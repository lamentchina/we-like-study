<?php
class user extends wls {
	
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_user;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_user(
				 id int primary key auto_increment	comment '自动编号'
				,id_user int not null				comment '从另外一个系统同步过来的用户编号,使之一一对应'
				,id_group varchar(200)				comment '用户组编号'
				,title_group varchar(200) 			comment '用户组名称'
				,sex int default 0					comment '性别'
				,name varchar(200) default '张三'	comment '姓名'
				,money int default 0				comment '学习币'
				,money_used int default 0			comment '已消费的学习币'
				,cents int default 0				comment '积分'
				,count_wrongs int default 0			comment '错题累积数'
				,count_papers int default 0			comment '累积做了几篇试卷'
				,ids_papers text					comment '总共做了哪些试卷,存储试卷编号'
				,photo varchar(200) default 'file/user/none.jpg' comment '用户照片'
				,myquiz varchar(200) default ''		comment '我参加的考试科目'
			) DEFAULT CHARSET=utf8 					comment='用户信息,同步自原有系统';
			";

		mysql_query($sql,$conn);
		
//		$sql = "drop table if exists ".$pfx."wls_user_group;";
//		mysql_query($sql,$conn);
//		$sql = "		
//			create table ".$pfx."wls_user_group(
//				 id int primary key auto_increment	comment '自动编号'
//				,name varchar(200) default '用户组' comment '用户组'
//				,id_parent int default 0			comment '上级编号'
//				,ordering int default 0				comment '排序'
//				,description text					comment '说明'
//			) DEFAULT CHARSET=utf8 					comment='此用户组中的内容只用于WLS本身';
//			";
//		mysql_query($sql,$conn);		
	}	
	
	public function getUserInfo($id=null){

		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();		
		
		$data = null;
		if($id==null && isset($_REQUEST['id']))$id=$_REQUEST['id'];

		if($id==null || $id=='mine' ){

			//需要依赖COOKIE和SESSION
//			if(!isset($_SESSION))session_start();
//			if(!isset($_SESSION['wls_user']) || count($_SESSION['wls_user'])==1){
				$cms = $this->cfg->cmstype;
				if($cms=='discuz'){
					if(isset($_COOKIE['qW3_sid'])){
						$sid = $_COOKIE['qW3_sid'];
						$sql = "select uid from ".$pfx."sessions where sid = '".$sid."'";
						$res = mysql_query($sql,$conn);
						$temp = mysql_fetch_assoc($res);	
						$id = $temp['uid'];		
					}else{
						$id = 0;
					}
				}
				if($id!=0){
					$sql_ = "select *					
						from ".$pfx."wls_user 
						where id_user = ".$id.";";
					$res_ = mysql_query($sql_,$conn);
					if($data = mysql_fetch_assoc($res_)){
						return $data;
					}else{
						if($cms=='discuz'){
							include_once 'controller/install/discuz.php';
							$obj = new install_discuz();
							$obj->updateUser($id);
						}
						return $this->getUserInfo($id);
					}
					
//					$_SESSION['wls_user'] = $data;
				}else{
//					$_SESSION['wls_user'] = array('id_user'=>0,'id_group'=>0);
					$data = array('id_user'=>0,'id_group'=>0,'photo'=>'file/images/user/none.jpg','name'=>'访客','sex'=>'0',
					'money'=>'0','cents'=>0,'count_wrongs'=>0,'count_papers'=>0,'myquiz'=>'所有测试用试卷');
				}			
//			}else{
//				$data = $_SESSION['wls_user'];
//			}
		}else{
			$sql_ = "select id_user,id_group,money,cents,count_wrongs,count_papers,sex,name,photo						
				from ".$pfx."wls_user 
				where id_user = ".$id.";";
			$res_ = mysql_query($sql_,$conn);
			if($temp = mysql_fetch_assoc($res_)){
				$data = $temp;
			}else{
				$data = array('id_user'=>0,'id_group'=>0,'photo'=>'file/images/user/none.jpg','name'=>'访客','sex'=>'0',
			'money'=>'0','cents'=>0,'count_wrongs'=>0,'count_papers'=>0,'myquiz'=>'所有测试用试卷');
			}
		}

		return $data;
	}
	
	public function viewOne($id=null){
		print_r($this->getUserInfo());
	}
	
	public function viewProfile($id=null){	
		$userinfo = $this->getUserInfo('mine');
		if($userinfo['id_user']==0){
			echo "
			<html>
				<head>
					<script type='text/javascript'>
						function loginFirst(){
							alert('你没有登录!');
							self.location='".$this->cfg->loginpath."';
						}
					</script>
				</head>
				<body onload='loginFirst();'></body>
			</html>
			";
			return;
		}
		
		include_once 'controller/quiz/paper/paper.php';
		$obj = new quiz_paper();
		$paperList = $obj->getRecentList();
		
		include_once 'controller/quiz/type.php';
		$obj = new quiz_type();
		$quiztypeList = $obj->getList('array',1,100,null);
		
		
		include_once 'controller/quiz/record.php';
		$obj = new quiz_record();
		$myList = $obj->getRecentList();
		
		$profile = $this->getProfile();
		
		$title = $this->cfg->title;
		$head = $this->headerScripts();
		
		include_once 'view/user/profile.php';
	}
	
	public function getProfile($id=null){
		if($id=null && isset($_REQUEST['id']))$id=$_REQUEST['id'];
		$userinfo = $this->getUserInfo($id);
		$dom = "
			<table width='98%' cellpadding='0' cellspacing='0' border='0'>
				<tr>
					<td class='w_u_k'>".$userinfo['name']."</td>
					<td class='w_u_v' colspan='3' style='width:80%'>".$this->getSex($userinfo['sex'])."</td>
								
				</tr>				
				<tr>
					<td class='w_u_k'>学习币</td>
					<td class='w_u_v'>".$userinfo['money']."</td>
					<td class='w_u_k'>积分</td>
					<td class='w_u_v'>".$userinfo['cents']."</td>
				</tr>	
				<tr>
					<td class='w_u_k'>错题数</td>
					<td class='w_u_v'>".$userinfo['count_wrongs']."</td>
					<td class='w_u_k'>已做试卷</td>
					<td class='w_u_v'>".$userinfo['count_papers']."</td>
				</tr>	
				<tr>
					<td class='w_u_k'>参加的考试科目</td>
					<td class='w_u_v' colspan='3' style='width:80%'>".$userinfo['myquiz']."</td>
				</tr>	
				<tr>
					<td class='w_u_k'>所在的用户组</td>
					<td class='w_u_v' colspan='3' style='width:80%'>".$userinfo['title_group']."</td>
				</tr>															
			</table>
		";
		return $dom;
	}

	public function getSex($sex){
		if($sex==0)return ' ';
		if($sex==1)return '先生';
		if($sex==2)return '女士';
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
		
		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){

			}
		}
		$sql = "select * from ".$pfx."wls_user  ".$where;
	
		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		
		$sql = "select count(*) as total from ".$pfx."wls_user ".$where;
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
		}else{
			$search = array();
		}
		if(isset($_REQUEST['keywords']) && $_REQUEST['keywords']!=''){
			$search['title']= $_REQUEST['keywords'];
		}		
		if($page==null)$page = 1;
		if($rows==null)$rows = 10;
		if($returnType==null)$returnType = 'html';
		
		$data = $this->getList('array',$page,$rows,$search);
		
		include_once 'view/user/dwzlist.php';
	}
	
	public function viewAddMoney(){
		$data = $this->getList('array',1,100,null);
		include_once 'view/user/addMoney.php';
	}
	
	public function addMoney(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "update ".$pfx."wls_user set money = money + ".$_REQUEST['money']." where id = ".$_REQUEST['id'];
		mysql_query($sql);
		echo json_encode(
			array(
				'state'=>'ok',
				'sql'=>$sql,
			)
		);	
	}
}
?>