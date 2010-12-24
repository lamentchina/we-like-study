<?php
include_once 'controller/question/question.php';

/**
 * 类型
 * 完形填空
 * */
class question_multichoice extends question{
	
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
		('0', '多项选择题".rand(100,200)."','{\"options\":[{\"option\":\"A\",\"title\":\"sound\"}
																,{\"option\":\"B\",\"title\":\"noise\"}
																,{\"option\":\"C\",\"title\":\"voice\"}
																,{\"option\":\"D\",\"title\":\"voicasdfe\"}]
																}', 'A,B,C','解题描述',2);";
		mysql_query($sql,$conn);
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('0', '多项选择题".rand(100,200)."','{\"options\":[{\"option\":\"A\",\"title\":\"sound\"}
																,{\"option\":\"B\",\"title\":\"noise\"}
																,{\"option\":\"C\",\"title\":\"voice\"}
																,{\"option\":\"D\",\"title\":\"voicasdfe\"}]
																}', 'B,C','解题描述',2);";
		mysql_query($sql,$conn);
		$sql = "insert into ".$pfx."wls_question (id_parent,title,details,answer,description,type) values 
		('0', '多项选择题".rand(100,200)."','{\"options\":[{\"option\":\"A\",\"title\":\"sound\"}
																,{\"option\":\"B\",\"title\":\"noise\"}
																,{\"option\":\"C\",\"title\":\"voice\"}
																,{\"option\":\"D\",\"title\":\"voicasdfe\"}]
																}', 'D','解题描述',2);";
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

		$sql = "select id,title,details,id_parent from ".$pfx."wls_question where id_parent=0 and id = ".$_REQUEST['id'];

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$temp['details'] = str_replace("\t","",$temp['details']);
		$temp['details'] = str_replace("\r","",$temp['details']);
		$temp['details'] = str_replace("\n","",$temp['details']);
		$temp['details'] = json_decode($temp['details'],true);

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
											type = 2 and
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
		$document->addScript("/components/com_wls/view/question/type/multichoice.js");
		$document->addScript("/components/com_wls/view/question/question.js");
		$document->addStyleSheet("/components/com_wls/view/wls.css");
		
		echo "
		<script type=\"text/javascript\" >
			var w_q_mc = new wls_question('w_q_mc');
			var w_q_t_mc_1 = new wls_question_multichoice('w_q_t_mc_1',w_q_mc);
			w_q_t_mc_1.initOne(".$_REQUEST['id'].",\"thisObj.initButton()\",0,'w_q_t_mc');
		</script>
		<div id='w_q_t_mc' ></div>
		<div id='w_q_t_mc_btn'>
			<button onclick='w_q_t_mc_1.submit()'>提交</button>
			<button onclick='w_q_t_mc_1.next()'>下一题</button>
		</div>
		";
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
		
		$sql = "select id,answer,description from ".$pfx."wls_question where id = ".$_REQUEST['id']." ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		
		//引用做题事件日志
		include 'controller/question/record.php';
		$record = new question_record();	
		
		$correct = ($temp['answer']==$_REQUEST['answer'])?1:0;
		

		$record->add($_REQUEST['id'],'0',$_REQUEST['answer'],$temp['answer'],$correct,'1');	
		
		
		//累加做题对错记录
		$ids_wrong = ($correct==0)?$_REQUEST['id']:'';
		$ids_right = ($correct==1)?$_REQUEST['id']:'';
		$ids_giveup = ($_REQUEST['answer']=='I_DONT_KNOW')?$_REQUEST['id']:'';
		$cumulate = $this->cumulateResults($ids_right,$ids_wrong,$ids_giveup,$_REQUEST['id']);
		
		//引用错题本
		include 'controller/quiz/wrongs.php';
		$wrongs = new quiz_wrongs();
		if($correct==0 ){
			$wrongs->wrong($_REQUEST['id']);	
		}else{
			$wrongs->right($_REQUEST['id']);	
		}

		//累加这个用户的知识点掌握度

		//输出返回结果
		$data = array(
			'question'=>$temp,
			'result'=>$correct,
			'right'=>$ids_right,
			'wrong'=>$ids_wrong,
			'giveup'=>$ids_giveup,
			'cumulate'=>$cumulate,
		);
		echo json_encode($data);
	}

}
?>