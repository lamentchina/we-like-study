<?php
/**
 * 错题本
 * 当用户在做题,做测试卷的时候,做错了一道题目,系统应该将这次做错的题目记录下来
 * 错题本是学生在进行巩固复习时的重要材料
 * 
 * 
 * 一旦学生做错题目,就记录这道题和这个用户,如果下次再做错,累加做错次数
 * 再下次,一旦做对,就删掉这条记录.
 * 
 * 如果题目涉及主题和子题,只记录主题编号
 * */
class quiz_wrongs extends wls{
	
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_quiz_wrongs;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_quiz_wrongs (
				 id int primary key auto_increment	comment '自动编号'
				,date_created datetime 				comment '创建时间'
				 
				,id_user int default 0				comment '用户编号'				
				,id_ques int default 0				comment '题目编号'
				,id_ques_parent int default 0		comment '题目的上级编号'
				,id_quiz_paper int default 0		comment '试卷编号'
				,title_quiz_paper varchar(200) default '未知试卷' comment '试卷名称' 
				,questype int default 0 			comment '题目类型'
				,id_quiz_type int default 0			comment '考试科目编号'
				,title_quiz_type varchar(200) default '未知科目' comment '科目名称'
				,count_ int default 1				comment '做错次数'
	
			) DEFAULT CHARSET=utf8 					comment='题目记录,每个人每做一道题,都要记录下.这是所有统计分析功能的基础';
			";
		mysql_query($sql,$conn);
	}
	
	/**
	 * 添加一条错题记录
	 * */
	public function wrong($id=null){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if($id==null && isset($_REQUEST['id']))$id = $_REQUEST['id'];

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo('mine');
//		if($userinfo['id_user']==0)return;
		
		$sql = "select id_parent,id,id_quiz_paper,title_quiz_paper,id_quiz_type,title_quiz_type,type from ".$pfx."wls_question where id = ".$id;
		$res = mysql_query($sql,$conn);
		$ques = mysql_fetch_assoc($res);
		
		
		$sql = "select id,id_ques from ".$pfx."wls_quiz_wrongs where id_user = ".$userinfo['id_user']." and id_ques = ".$id;
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_array($res)){
			$sql = "update ".$pfx."wls_quiz_wrongs set count_ = count_+1 where id_user = ".$userinfo['id_user']." and id_ques = ".$id;

			mysql_query($sql,$conn);
		}else{
			$sql = "insert into ".$pfx."wls_quiz_wrongs(
					 date_created
					,id_user
					,id_ques
					,id_ques_parent
					,id_quiz_paper
					,title_quiz_paper
					,id_quiz_type
					,title_quiz_type
					,questype 
				) values(
					 '".date('Y-m-d h:i:s')."'
					,'".$userinfo['id_user']."'
					,'".$id."'
					,'".$ques['id_parent']."'
					,'".$ques['id_quiz_paper']."'
					,'".$ques['title_quiz_paper']."'
					,'".$ques['id_quiz_type']."'
					,'".$ques['title_quiz_type']."'
					,'".$ques['type']."'
				);";
			mysql_query($sql,$conn);
		}		
	}
	
	/**
	 * 这道题目做对了,就从错题本中将其删掉
	 * */
	public function right($id_question){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;	

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();
		
		$sql = "select id from ".$pfx."wls_quiz_wrongs where id_user = ".$userinfo['id_user']." and id_question = ".$id_question;
		$res = mysql_query($sql,$conn);
		if(!$res)return;
		while($temp = mysql_fetch_array($res)){
			$sql = "delete from  ".$pfx."wls_quiz_wrongs where id_user = ".$userinfo['id_user']." and id_question = ".$id_question;
			mysql_query($sql,$conn);
		}		
	}
		
	public function getMyWrongs(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();		
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();		

		
		include_once 'controller/question/question.php';
		$obj = new question();
		$list = $obj->getList('array',1,400,array('wrongs_userid'=>$userinfo['id_user']),' order by id_parent ');
		$data = $list['rows'];
		$ques = array();

		for($i=0;$i<count($data);$i++){
			$data[$i]['details'] = json_decode($data[$i]['details'],true);
			if($data[$i]['id_parent']==0){
				$data[$i]['child'] = array();
				$ques[$data[$i]['id']] = $data[$i];
			}else{
				$ques[$data[$i]['id_parent']]['child'][] = $data[$i];
			}
			
		}
		echo json_encode(array_values($ques));		
	}
	
	public function getDWZlist($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['pageNum']))$page=$_REQUEST['pageNum'];
		if($rows==null && isset($_REQUEST['numPerPage']))$rows=$_REQUEST['numPerPage'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_encode($_REQUEST['search']);
		if($page==null)$page = 1;
		if($rows==null)$rows = 10;
		if($returnType==null)$returnType = 'html';
		
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();			
		
		$data = $this->getList('array',$page,$rows,array('id_user'=>$userinfo['id_user']),' order by id');	

		include_once 'view/quiz/wrongs/list.php';
	}
	
	
	public function getList($returnType = null,$page=null,$rows=null,$search=null,$orderby=null){
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
				if($keys[$i]=='id_user'){
					$where .= " and id_user in (".$search[$keys[$i]].") ";
				}						
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select 
				 *				
				 from ".$pfx."wls_quiz_wrongs  ".$where.$orderby;
	
		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		
		$sql = "select count(*) as total from ".$pfx."wls_quiz_wrongs ".$where;
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
}
?>