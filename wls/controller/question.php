<?php
/**
 * 考试学习系统的基本元素之一
 * 题目
 * */
class question extends wls {	

	/**
	 * 初始化数据库表结构
	 * 在开发阶段,数据库表结构可能会经常变动
	 * */
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_question;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_question(
				 id int primary key auto_increment	comment '自动编号'

				,type int default 1					comment '类别(1单项选择题,2多项选择题,3判断题,4简答题,5短文阅读)'
				,extype varchar(200) default '扩展'	comment '扩展题型'
				,title text not null				comment '题目标题'
				,details text not null 				comment '详细的题目配置内容,根据题目类别不同而设置'				
				,answer text not null				comment '答案,根据题型不同有不同的组织形式'
				,description text					comment '描述,解题说明'	
				,cent float default 0				comment '分数.一道题目必然属于一张试卷,必然有自己的分数比例,一道题不可能属于2张试卷'	

				,id_quiz_type int default 0			comment '考试科目编号'
				,title_quiz_type varchar(200) default '未知考试科目'		comment '考试科目名称'
				,id_quiz_paper int default	0		comment '试卷编号'
				,title_quiz_paper varchar(200) default '未知试卷名称'	comment '考试试卷名称'
				,id_parent int default 0			comment '父级题目编号,如果是阅读理解 完形填空 短文听力 填空'
								
				,creator varchar(200) default 'admin' comment '创建者'

				,date_created datetime not null 	comment '创建时间'				
				,rank int default 1					comment '使用级别 1,任何用户都能使用,2,注册用户能够使用,3,付费用户能够使用,4VIP用户能够使用'
				
				,count_right int default 0			comment '被做对的次数' 
				,count_wrong int default 0 			comment '被做错的次数' 
				,count_giveup int default 0 		comment '被放弃的次数' 	
				
				,comment_ywrong_1 int default 0		comment '用户评论,为什么我会做错,知识没掌握'
				,comment_ywrong_2 int default 0		comment '用户评论,为什么我会做错,粗心'
				,comment_ywrong_3 int default 0		comment '用户评论,为什么我会做错,题目文字太混,读不懂'
				,comment_ywrong_4 int default 0		comment '用户评论,为什么我会做错,我没错,答案错了'			
				,comment_difficulty int default 0	comment '用户对题目难度的评价,0到100'
				,comment_diff_count int default 0	comment '有多少人对题目难度做了评价'
				,comment_quality int default 0		comment '对题目质量的评价,0到100'
				,comment_qual_count int default 0 	comment '有多少人对题目质量做了评价'	
				,difficulty int default 0			comment '难度值,最大100,最小0'		

				,markingmethod int default 0		comment '批改方式:0 自动批改,1教师批改,2用户批改,3多用户批改'
				,recommended int default 0 			comment '推荐:1 首页推荐,2'
				,ids_knowledge varchar(200) default '0'	comment '知识点的编号集'
				,distribute varchar(200) default '0'	comment '知识点的百分比分配'
			) DEFAULT CHARSET=utf8 					comment='题目,基本组成单位';
			";
		mysql_query($sql,$conn);
	}
	
	/**
	 * 每个人每做一道题,都要累加做题对错记录,
	 * 包括: 做对,做错,放弃 次数
	 * 方便统计
	 * 
	 * TODO 应该用存储过程
	 * */
	public function cumulateResults($ids_right,$ids_wrong,$ids_giveup,$id_parent){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		//更新这道题的做题记录
		if($ids_wrong!=''){
			$ids_wrong = substr($ids_wrong,0,strlen($ids_wrong)-1);
			$sql = "update ".$pfx."wls_question set count_wrong = count_wrong+1 where id in (".$ids_wrong.")";
			mysql_query($sql,$conn);
		}
		if($ids_right!=''){
			$ids_right = substr($ids_right,0,strlen($ids_right)-1);
			$sql = "update ".$pfx."wls_question set count_right = count_right+1 where id in (".$ids_right.")";
			mysql_query($sql,$conn);
		}
		if($ids_giveup!=''){
			$ids_giveup = substr($ids_giveup,0,strlen($ids_giveup)-1);
			$sql = "update ".$pfx."wls_question set count_giveup = count_giveup+1 where id in (".$ids_giveup.")";
			mysql_query($sql,$conn);
		}	
		//没有弃题,没有错题,才算做对
		if($ids_wrong=='' && $ids_giveup==''){
			$sql = "update ".$pfx."wls_question set count_right = count_right+1 where id = ".$id_parent;
			mysql_query($sql,$conn);
		}else{
			$sql = "update ".$pfx."wls_question set count_wrong = count_wrong+1 where id = ".$id_parent;
			mysql_query($sql,$conn);
		}
		
		//得到这道题的做题记录
		$sql = "select count_right,count_wrong,count_giveup,id,id_parent from ".$pfx."wls_question where id = ".$id_parent." or id_parent = ".$id_parent;
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		return $arr;
	}
	
	/**
	 * 如果用户题目做错了,用户可以对
	 * 为什么我会做错 Y I'm Wrong 做出评价
	 * 评价之后,系统要返回所有用户对这道题的评价结论
	 * 
	 * */
	public function commentYWrong(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = '';
		if($_REQUEST['YWrong']=='1'){
			$sql = "update ".$pfx."wls_question set comment_ywrong_1 = comment_ywrong_1+1 where id = ".$_REQUEST['id'];
		}
		if($_REQUEST['YWrong']=='2'){
			$sql = "update ".$pfx."wls_question set comment_ywrong_2 = comment_ywrong_2+1 where id = ".$_REQUEST['id'];
		}
		if($_REQUEST['YWrong']=='3'){
			$sql = "update ".$pfx."wls_question set comment_ywrong_3 = comment_ywrong_3+1 where id = ".$_REQUEST['id'];
		}
		if($_REQUEST['YWrong']=='4'){
			$sql = "update ".$pfx."wls_question set comment_ywrong_4 = comment_ywrong_4+1 where id = ".$_REQUEST['id'];
		}
		mysql_query($sql,$conn);
		
		$sql = "select id,comment_ywrong_1,comment_ywrong_2,comment_ywrong_3,comment_ywrong_4 from ".$pfx."wls_question where id = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		if($temp=mysql_fetch_assoc($res)){
			echo json_encode($temp);
		}
	}
	
	/**
	 * 评价一道题目的质量
	 * */
	public function commentQuality(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "update ".$pfx."wls_question set comment_quality = (comment_quality*comment_qual_count+".$_REQUEST['score']." )/(comment_qual_count + 1), comment_qual_count = comment_qual_count+1 where id = ".$_REQUEST['id'];
		mysql_query($sql,$conn);
	}
	
	/**
	 * 前台将一道题的答题结果发送到后台
	 * 后台判断这道题没有有做错,然后:
	 *   更新这道题的现状: 做对次数,做错次数,放弃次数
	 *      如果是多项选择题的话,还要累加各个选项分别被选了多少次
	 *   在日志
	 *   
	 * 无论是否是注册用户,的整体对错率都要更新
	 * 
	 * 但是错题本,做题记录仅仅在注册用户下在使用
	 * */
	public function checkOne(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$answer = $this->format($_REQUEST['answer']);
		$id = $_REQUEST['id'];
		
		include 'controller/question/record.php';
		$record = new question_record();			
		
		$sql = "select id,answer,description,id_parent,markingmethod from ".$pfx."wls_question where id = ".$id;
		$res = mysql_query($sql,$conn);

		$temp = mysql_fetch_assoc($res);
		if($temp['markingmethod']!=0){//非自动批改
			$temp['correct']= 0;	
			$record->add($temp['id'],$answer,'2','1');	
			echo json_encode($temp);	
			exit();
		}
		if(trim($temp['answer'])==trim($answer)){//做对了
			$record->add($temp['id'],$answer,'1','1');	
			$temp['correct']= 1;		
		}else if($answer=='I_DONT_KNOW'){
			$record->add($temp['id'],$answer,'2','1');	
			$temp['correct']= 2;	
		}else{
			$record->add($temp['id'],$answer,'0','1');	
			include 'controller/quiz/wrongs.php';
			$obj = new quiz_wrongs();
			$obj->wrong($temp['id']);
			$temp['correct']= 0;		
		}

		echo json_encode($temp);
	}	
	
	public function formate($str){
		$str = str_replace("\t","",$str);
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","",$str);
		$str = json_decode($str,true);
		return $str;
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
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_quiz_type'){
					$where .= " and id_quiz_type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_quiz_paper'){
					$where .= " and id_quiz_paper in (".$search[$keys[$i]].") ";
				}	
				if($keys[$i]=='wrongs_userid'){
					$where .= " and (
					id in (
						select id_ques_parent from cdb_wls_quiz_wrongs  where id_user = ".$search[$keys[$i]]."
    					group by id_ques_parent
					) or 
					id_parent in (
						select id_ques_parent from cdb_wls_quiz_wrongs  where id_user = ".$search[$keys[$i]]."
    					group by id_ques_parent
					)
					) ";
				}
							
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select 
				 id
				,type
				,title
				,details
				,answer
				,description
				,cent
				,id_quiz_type
				,title_quiz_type
				,id_quiz_paper
				,title_quiz_paper
				,id_parent
				
				 from ".$pfx."wls_question  ".$where.$orderby;
	
		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		
		$sql = "select count(*) as total from ".$pfx."wls_question ".$where;
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