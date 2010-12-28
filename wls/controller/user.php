<?php
class user extends wls {	
	
	public $userinfo = null;
	
	public function getUserInfo($id=null){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();		
		
		$data = null;
		if($id==null && isset($_REQUEST['id']))$id=$_REQUEST['id'];
		
		if($this->userinfo==null){
			eval("include_once 'controller/install/".$this->cfg->cmstype.".php';");
			eval('$obj = new install_'.$this->cfg->cmstype.'();');
			eval('$data = $obj->getUserInfo($id);');
			$this->userinfo = $data;
		}else{
			$data = $this->userinfo;
		}
		return $data;
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

		include_once 'controller/quiz/type.php';
		$obj = new quiz_type();
		$quiztypeList = $obj->getMyList();		
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
				<!--
				<tr>
					<td class='w_u_k'>所在的用户组</td>
					<td class='w_u_v' colspan='3' style='width:80%'>".$userinfo['title_group']."</td>
				</tr>
				-->															
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
	
}
?>