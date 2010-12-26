<?php
/**
 * 具体的一张试卷
 * */
class quiz_paper extends wls{

	public $phpexcel = null;

	public $ques = array();

	public $subques = array();

	public $mainques = array();

	public $paper = array();

	/**
	 * 初始化数据库表结构
	 * 在开发阶段,数据库表结构可能会经常变动
	 * */
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "drop table if exists ".$pfx."wls_quiz_paper;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_paper(
				 id int primary key auto_increment	comment '自动编号'
				,id_quiz_type int not null			comment '考试科目编号'
				,title_quiz_type varchar(200) default '' comment '考试科目名称'
				
				,title varchar(200) not null		comment '试卷名称'
				,questions text						comment '题目组成,由一大堆编号组成,这些编号都是主题目编号,不会记录子题目编号'
				,islisten int default 0				comment '是否包含听力题'
				
				,description text					comment '描述'				
				,creator varchar(200) not null		comment '创建者'
				,publisher varchar(200)				comment '出版者,审核者'
				,contributor varchar(200)			comment '其他编辑者,修改者'
				,date_created datetime not null 	comment '创建时间'
				,date_modified datetime				comment '最后一次修改日期'
				,date_available_start datetime		comment '可获得日期_开始'
				,date_available_stop datetime		comment '可获得日期_结束'
				,date_issued datetime				comment '发布日期'
				,count_quetions int default 0		comment '主题目总数'
				,count_subquestions int default 0	comment '子题目总数'

				,subquestions text					comment '子题目组成'
				,questions_types varchar(200)		comment '试卷中包含的题目类型'
				,scores text				 		comment '分数组成,题目编号一一对应,比如:[{num:23,cent:45,sub:[{num:24,cent:5}]},]'
				,sections text 						comment '试卷组成描述,存储JSON数据,比如[{\'类型\':\'听力\',\'描述\':\'听力题描述\',\'题目\':[1,2,3,4,5,6,7]},{\'类型\':阅读理解;阅读理解描述;{8,9,10,12}}]'
				,time_limit int default 3600		comment '时间限制,默认为1小时,1小时后自动提交试卷'
				,score_top float default 0			comment '最高分'
				,score_top_user varchar(200) default 0		comment '最高分获得者用户编号'
				,score_avg float default 0			comment '平均分'
				,count_used int	default 0			comment '使用次数'
				
				,difficulty int default 0			comment '难度值,最大100,最小0'
				,rank int default 1					comment '1,任何用户都能使用,2,注册用户能够使用,3,付费用户能够使用,4VIP用户能够使用'
				,price_money float default 0		comment '每次使用扣钱'
				,price_score float default 0		comment '每次使用扣积分'
			) DEFAULT CHARSET=utf8 					comment='试卷,由题目组成';
			";
		mysql_query($sql,$conn);
	}

	public function initTestData(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "INSERT INTO ".$pfx."wls_quiz_paper (id_quiz_type,title,date_created,questions) VALUES
		(1,'asf','".date('Y-m-d')."','12,23,28,33,43,53,44,67,77,87,92')
		;";		
		mysql_query($sql,$conn);
	}

	public function getOne(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if($_REQUEST['id']=='0'){//如果前台没有传ID过来,就随机的产生一个ID
			$_REQUEST['id'] = $this->getRandom();
		}
		$sql = "select id,title from ".$pfx."wls_quiz_paper where id = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		$temp_ = mysql_fetch_assoc($res);
		$temp_['ids'] = array();
		$sql = "select id,type from ".$pfx."wls_question where id_quiz_paper = ".$_REQUEST['id']." and id_parent = 0";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp_['ids'][] = $temp;
		}
		echo json_encode($temp_);
	}


	/**
	 * 显示一张试卷
	 * */
	public function viewOne(){
		if(!isset($_REQUEST['id'])){//如果前台没有传ID过来,就随机的产生一个ID
			$_REQUEST['id'] = $this->getRandom();
		}
		//TODO
	}

	/**
	 * 得到一个随机的编号
	 * 必定是主题编号
	 * */
	public function getRandom(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "select id from ".$pfx."wls_quiz_paper where
											id >= (select min(id) from ".$pfx."wls_quiz_paper ) and 
											id < (select max(id) from ".$pfx."wls_quiz_paper )*rand() order by id desc limit 1 ;";
		$res = mysql_query($sql,$conn);
		if($temp2 = mysql_fetch_assoc($res)){
			unset($res);
			return $temp2['id'];
		}else{
			return $this->getRandom();
		}
	}

	public function getList($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['page']))$page=$_REQUEST['page'];
		if($rows==null && isset($_REQUEST['rows']))$rows=$_REQUEST['rows'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		$search_ = '';
		if($search==null && isset($_REQUEST['search'])){
			$search =json_decode($_REQUEST['search'],true);
			$search_ = $_REQUEST['search'];
		}
		if($page==null)$page = 1;
		if($rows==null)$rows = 20;
		if($returnType==null)$returnType = 'json';

		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();

		$where = " where 1 =1  ";
		if($search!=null && count($search)>0){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='title'){
					$where .= " and title like '%".$search[$keys[$i]]."%' ";
				}
				if($keys[$i]=='id_quiz_type'){
					$where .= " and id_quiz_type in (".$search[$keys[$i]].") ";
				}
			}
		}
		$sql = "select * from ".$pfx."wls_quiz_paper  ".$where;

		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql = "select count(*) as total from ".$pfx."wls_quiz_paper ".$where;
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
		$search_ = '';
		if($search==null && isset($_REQUEST['search'])){
			$search_ = $_REQUEST['search'];
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

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();

		include_once 'view/quiz/paper/list.php';
	}

	public function viewOneInDWZ($id=null){
		if($id==null && isset($_REQUEST['id']))$id = $_REQUEST['id'];
		include_once 'view/quiz/paper/viewOne.php';
	}

	/**
	 * 读取一个EXCLE文件
	 * */
	public function readExcel($path=null){
		if($path==null)$path = 'file/install/cet6_2.xls';
		include_once 'libs/phpexcel/Classes/PHPExcel.php';
		include_once 'libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);
	}

	public function del(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "delete from ".$pfx."wls_quiz_paper where id in (".$_REQUEST['id']."); ";
		mysql_query($sql);
		echo '
		{
		"statusCode":"200",
		"message":"删除了编号为'.$_REQUEST['id'].'的试卷",
		"navTabId":"",
		"callbackType":"reload",
		"forwardUrl":""
		}		
		';
	}

	public function getAllQues($id=null){
		if($id==null && isset($_REQUEST['id']))$id=$_REQUEST['id'];

		include_once 'controller/question/question.php';
		$obj = new question();
		$list = $obj->getList('array',1,400,array('id_quiz_paper'=>$id),' order by id_parent ');
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

	/**
	 * 做这张试卷,我的钱够花吗
	 * */
	public function isMyMoneyEnough(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select price_money,id from ".$pfx."wls_quiz_paper where id = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($this->cfg->cmstype=='discuz'){
			include_once 'controller/install/discuz.php';
			$obj = new install_discuz();
			if($obj->reduceMyMoney('mine',$temp['price_money'])){
				echo json_encode(array(
					'enough'=>'yes'
				));
			}else{
				echo json_encode(array(
					'enough'=>'no'
				));
			}
		}else if($this->cfg->cmstype=='discuzx'){
			include_once 'controller/install/discuzx.php';
			$obj = new install_discuzx();
			if($obj->reduceMyMoney('mine',$temp['price_money'])){
				echo json_encode(array(
					'enough'=>'yes'
				));
			}else{
				echo json_encode(array(
					'enough'=>'no'
				));
			}
		}
	}
}

/**
 * 试卷分类的各个接口
 * */
interface quiz_paper_{

	/**
	 * 读取一张EXCEL文件
	 * */
	//	public function readExcel();
}
?>