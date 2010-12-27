<?php
include_once 'controller/question.php';


/**
 * 题目类型
 * 阅读理解,主要针对CET考试中的阅读理解
 * */
class question_reading extends question{
	
	/**
	 * 初始化一些测试数据
	 * */
	public function initTestData(){
		if($this->cfg->debug==0)return;
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if(isset($_REQUEST['initQuestion'])){
			$this->initTable();
		}
		$id_paper = 1;
		if(isset($_REQUEST['initPaper'])){
			include_once 'controller/quiz/paper.php';
			$obj = new quiz_paper();
			$obj->initTable();
			$sql = "INSERT INTO ".$pfx."wls_quiz_paper (id_quiz_type,title,date_created,questions) VALUES 
			(1,'asf','".date('Y-m-d')."','1,2,3,4,5')
			;";		
			mysql_query($sql,$conn);
			$id_paper = mysql_insert_id($conn);
		}		
		
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type,id_quiz_paper) values 
		('0', '阅读理解测试题目1','{\"listen\":\"a.mp3\"}', '','解题描述',5,".$id_paper.");";
		mysql_query($sql,$conn);
		$parentid = mysql_insert_id($conn);
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type,id_quiz_paper) values 
		('".$parentid."', '题目', '{\"options\":[{\"option\":\"A\",\"title\":\"选项A描述\"}
																,{\"option\":\"B\",\"title\":\"选项B描述\"}
																,{\"option\":\"C\",\"title\":\"选项C描述\"}
																,{\"option\":\"D\",\"title\":\"选项D描述\"}
																]}',
		'A','解题描述',1,".$id_paper.")
		,('".$parentid."', '题目', '{\"options\":[{\"option\":\"A\",\"title\":\"选项A描述\"}
																,{\"option\":\"B\",\"title\":\"选项B描述\"}
																,{\"option\":\"C\",\"title\":\"选项C描述\"}
																,{\"option\":\"D\",\"title\":\"选项D描述\"}
																]}',
		'B','解题描述',1,".$id_paper.")
		,('".$parentid."', '题目', '{\"options\":[{\"option\":\"A\",\"title\":\"选项A描述\"}
																,{\"option\":\"B\",\"title\":\"选项B描述\"}
																,{\"option\":\"C\",\"title\":\"选项C描述\"}
																,{\"option\":\"D\",\"title\":\"选项D描述\"}
																]}',
		'C','解题描述',1,".$id_paper.")
		,('".$parentid."', '题目', '{\"options\":[{\"option\":\"A\",\"title\":\"选项A描述\"}
																,{\"option\":\"B\",\"title\":\"选项B描述\"}
																,{\"option\":\"C\",\"title\":\"选项C描述\"}
																,{\"option\":\"D\",\"title\":\"选项D描述\"}
																]}',
		'D','解题描述',1,".$id_paper.");";
		mysql_query($sql,$conn);		
	}
	
	/**
	 * 得到一个题目的详细信息
	 * 返回类型可以是 JSON XML ARRAY
	 * */
	public function getOne(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		if($_REQUEST['id']=='0'){//如果前台没有传ID过来,就随机的产生一个ID
			$_REQUEST['id'] = $this->getRandom();
		}	

		$sql = "select id,id_parent,title,type,details from ".$pfx."wls_question where id_parent=0 and id = ".$_REQUEST['id'];

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$temp['title'] = str_replace("</td>","",$temp['title']);
		$temp['title'] = str_replace("</DIV>","",$temp['title']);
		$temp['details'] = str_replace("\t","",$temp['details']);
		$temp['details'] = str_replace("\r","",$temp['details']);
		$temp['details'] = str_replace("\n","",$temp['details']);
		$temp['details'] = json_decode($temp['details'],true);
		$temp['child'] = array();
		$sql = "select id,id_parent,title,type,details,cent,markingmethod from ".$pfx."wls_question where id_parent=".$temp['id'];
		$res = mysql_query($sql,$conn);
		while($temp2 = mysql_fetch_assoc($res)){
			$temp2['title'] = str_replace("\t","",$temp2['title']);
			$temp2['title'] = str_replace("\r","",$temp2['title']);
			$temp2['title'] = str_replace("\n","",$temp2['title']);


			$temp2['details'] = str_replace("\t","",$temp2['details']);
			$temp2['details'] = str_replace("\r","",$temp2['details']);
			$temp2['details'] = str_replace("\n","",$temp2['details']);
			$temp2['details'] = json_decode($temp2['details'],true);
			$temp['child'][] = $temp2;
		}
		echo json_encode($temp);
	}	
	
	/**
	 * 得到一个随机的题目编号
	 * 必定是主题编号
	 * */
	public function getRandom(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "select id from ".$pfx."wls_question where 
											id_parent = 0 and 
											type = 5 and
											id >= (select min(id) from ".$pfx."wls_question ) and 
											id < (select max(id) from ".$pfx."wls_question )*rand() order by id desc limit 1 ;";
		$res = mysql_query($sql,$conn);
		if($temp2 = mysql_fetch_assoc($res)){
			unset($res);
			return $temp2['id'];
		}else{
			return $this->getRandom(); 
		}
		
	}
	
	/**
	 * 显示一道题目
	 * */
	public function viewOne(){		
		if(!isset($_REQUEST['id'])){//如果前台没有传ID过来,就随机的产生一个ID
			$_REQUEST['id'] = $this->getRandom();
		}	
		$document =& JFactory::getDocument();
		$document->addScript("/components/com_wls/view/question/type/reading.js");
		$document->addScript("/components/com_wls/view/question/question.js");
		$document->addStyleSheet("/components/com_wls/view/wls.css");
		$document->addScript("/libraries/jquery.raty-1.0.0/js/jquery.raty.js");	
		echo "
		<script type=\"text/javascript\" >
			var w_q_1 = new wls_question('w_q_1');
			var w_q_t_r_1 = new wls_question_reading('w_q_t_r_1',w_q_1);
			w_q_t_r_1.initOne(".$_REQUEST['id'].",\"thisObj.initButton()\",'w_q_t_r');
			
		</script>
		<div id='w_q_t_r' ></div>
		<div id='w_q_t_r_btn' ></div>
		";
	}
}
?>