<?php
include_once 'controller/question.php';


/**
 * 类型
 * 完形填空
 * */
class question_choice extends question{
	
	/**
	 * 初始化一些测试数据
	 * */
	public function initTestData(){
		if($this->cfg->debug==0)return;
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		if(isset($_REQUEST['initTable'])){
			$this->initTable();
		}
		
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('0', '单项选择题".rand(100,200)."','{\"options\":[{\"option\":\"A\",\"title\":\"sound\"}
																,{\"option\":\"B\",\"title\":\"noise\"}
																,{\"option\":\"C\",\"title\":\"voice\"}]
																}', 'A','解题描述',1);";
		mysql_query($sql,$conn);
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('0', '单项选择题".rand(100,200)."','{\"options\":[{\"option\":\"A\",\"title\":\"sound\"}
																,{\"option\":\"B\",\"title\":\"noise\"}
																,{\"option\":\"C\",\"title\":\"voice\"}]
																}', 'B','解题描述',1);";
		mysql_query($sql,$conn);
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('0', '单项选择题".rand(100,200)."','{\"options\":[{\"option\":\"A\",\"title\":\"sound\"}
																,{\"option\":\"B\",\"title\":\"noise\"}
																,{\"option\":\"C\",\"title\":\"voice\"}]
																}', 'C','解题描述',1);";
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

		$sql = "select id,title,details,id_parent,cent from ".$pfx."wls_question where id_parent=0 and id = ".$_REQUEST['id'];

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
											type = 1 and
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
		
		$document->addScript("/libraries/jquery.raty-1.0.0/js/jquery.raty.js");	
		$document->addScript("/components/com_wls/view/question/type/choice.js");
		$document->addScript("/components/com_wls/view/question/question.js");
		$document->addStyleSheet("/components/com_wls/view/wls.css");
		
		echo "
		<script type=\"text/javascript\" >
			var w_q_ch = new wls_question('w_q_ch');
			var w_q_t_ch_1 = new wls_question_choice('w_q_t_ch_1',w_q_ch);
			w_q_t_ch_1.initOne(".$_REQUEST['id'].",\"thisObj.initButton()\",'w_q_t_ch',0);
		</script>
		<div id='w_q_t_ch' ></div>
		<div id='w_q_t_ch_btn'>
			<button onclick='w_q_t_ch_1.submit()'>提交</button>
			<button onclick='w_q_t_ch_1.next()'>下一题</button>
		</div>
		";
	}
	
	
	



}
?>