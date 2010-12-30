<?php
/**
 * wls本身没有 wls_user 这张表,也没有存储用户相关的信息
 * wls依赖所依附的CMS系统来读取用户数据
 * 这需要引用 controller/install/ 中的桥接文件
 * 
 * @author wei1224hf
 * @copyright www.wei1224hf.com
 * @see 
 * */
class user extends wls {	
	
	/**
	 * 为了尽量减少用户数据的重复读取,
	 * 将用户信息保存在这个变量中,
	 * 以实现重复使用
	 * */
	public $userinfo = null;
	
	/**
	 * 根据用户编号得到用户信息
	 * 在这里引入桥接文件
	 * 前台可以传编号参数过来
	 * 
	 * @param $id 用户编号,一般情况下为 int
	 * @return $data array
	 * */
	public function getUser($id=null){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();		
		
		$data = null;
		if($id==null && isset($_REQUEST['id']))$id=$_REQUEST['id'];
		
		//如果用户还没有初始化,就需要读取数据库内容了
		if($this->userinfo==null){
			eval("include_once 'controller/install/".$this->cfg->cmstype.".php';");
			eval('$obj = new install_'.$this->cfg->cmstype.'();');
			eval('$data = $obj->getUserInfo($id);');
			$this->userinfo = $data;
		}else{
			//尽量减少磁盘I/O
			$data = $this->userinfo;
		}
		return $data;
	}
	
	/**
	 * 访问WLS的主入口
	 * 需要先判断用户的在线状态,如果没有登录,
	 * 就让他先登录
	 * 
	 * @param $id 用户编号
	 * */
	public function viewWLS($id=null){	
		$userinfo = $this->getUser('mine');
		if($userinfo['id_user']==0){
			echo "
			<html>
				<head>
					<script type='text/javascript'>
						function loginFirst(){
							alert('你没有登录或长时间没有操作!');
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
		$profile = $this->getUserByHTML();		
		$title = $this->cfg->title;
		$head = $this->headerScripts();
		
		include_once 'view/user/profile.php';
	}
	
	/**
	 * 得到用户信息,
	 * 返回的是一段HTML代码,用于直接引用到前台
	 * 
	 * @param $id 用户编号
	 * @return $html 
	 * */
	public function getUserByHTML($id=null){
		if($id=null && isset($_REQUEST['id']))$id=$_REQUEST['id'];
		$userinfo = $this->getUser($id);
		
		$html = "
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
		return $html;
	}

	public function getSex($sex){
		if($sex==0)return ' ';
		if($sex==1)return '先生';
		if($sex==2)return '女士';
	}
	
	/**
	 * 得到多个用户的列表信息
	 * */
	public function getList($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['page']))$page=$_REQUEST['page'];
		if($rows==null && isset($_REQUEST['rows']))$rows=$_REQUEST['rows'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_decode($_REQUEST['search']);
		if($page==null)$page = 1;
		if($rows==null)$rows = 20;
		if($returnType==null)$returnType = 'json';

		//TODO
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
		
		//TODO
	}
	
}
?>