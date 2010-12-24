<?php
include_once 'controller/question/question.php';

/**
 * 类型
 * 填空题
 * */
class question_blank extends question{
	
	/**
	 * 初始化一些测试数据
	 * */
	public function initTestData(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if(isset($_REQUEST['initTable'])){
			$this->initTable();
		}
		
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('0', '填空题".rand(100,200)."','Once Frenchman went to England. He  ___  only a little English. One day when he was  ___  by the windows of a restaurant and having lunch, he heard a  ___  Look out ! So he put his head out of the  ___  to find out what was  ___  outside. Just then a basin of dirty water poured over his  ___  . Then another. He was very angry. He shouted, Damn you! See what you have  ___  .
    The man passing by laughed him and he  ___  even angrier. One of them then said to him. You  ___  be a foreigner. Look outin English means he  ___  .', '','解题描述',4);";
		mysql_query($sql,$conn);
		$parentid = mysql_insert_id($conn);
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('".$parentid."', '', '',
		'A','解题描述',4)
		,('".$parentid."', '', '',
		'B','解题描述',4)
		,('".$parentid."', '', '',
		'C','解题描述',4)
		,('".$parentid."', '', '',
		'B','解题描述',4)
		,('".$parentid."', '', '',
		'C','解题描述',4)
		,('".$parentid."', '', '',
		'A','解题描述',4)
		,('".$parentid."', '', '',
		'C','解题描述',4)
		,('".$parentid."', '', '',
		'C','解题描述',4)
		,('".$parentid."', '', '',
		'C','解题描述',4)
		,('".$parentid."', '', '',
		'C','解题描述',4)
		;";
		mysql_query($sql,$conn);		
	}
	
	/**
	 * 得到一个的详细信息
	 * 返回类型可以是 JSON XML ARRAY
	 * */
	public function getOne(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		if($_REQUEST['id']=='0'){//如果前台没有传ID过来,就随机的产生一个ID
			$_REQUEST['id'] = $this->getRandom();
		}	

		$sql = "select id,title,details,id_parent,details,cent,markingmethod from ".$pfx."wls_question where id_parent=0 and id = ".$_REQUEST['id'];

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$temp['details'] = $this->formate($temp['details']);
		
		echo json_encode($temp);
	}	
	
	/**
	 * 得到一个随机的编号
	 * 必定是主题编号
	 * */
	public function getRandom(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "select id from ".$pfx."wls_question where 
											id_parent = 0 and 
											type = 4 and
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
	 * 显示一道
	 * */
	public function viewOne(){		
		if(!isset($_REQUEST['id'])){//如果前台没有传ID过来,就随机的产生一个ID
			$_REQUEST['id'] = $this->getRandom();
		}	
		$document =& JFactory::getDocument();
		$document->addScript("/components/com_wls/view/question/type/blank.js");
		$document->addScript("/components/com_wls/view/question/question.js");
		$document->addStyleSheet("/components/com_wls/view/wls.css");
		$document->addScript("/libraries/jquery.raty-1.0.0/js/jquery.raty.js");	
		echo "
		<script type=\"text/javascript\" >
			var w_q_1 = new wls_question('w_q_1');
			var w_q_t_b_1 = new wls_question_blank('w_q_t_b_1',w_q_1);
			w_q_t_b_1.initOne(".$_REQUEST['id'].",\"thisObj.initButton()\",0,'w_q_t_b');
			
		</script>
		<div id='w_q_t_b' ></div>
		<div id='w_q_t_b_btn' ></div>
		";
	}
	

}
?>