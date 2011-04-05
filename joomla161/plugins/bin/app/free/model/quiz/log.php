<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../user.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../question/log.php';
include_once dirname(__FILE__).'/wrong.php';

include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_quiz_log extends wls implements dbtable,fileLoad,log{

	public $phpexcel;
	public $id = null;

	public function insert($data){

		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['id_user'])){
			$userObj = new m_user();
			$user = $userObj->getMyInfo();
			$data['id_user'] = $user['id'];
			$data['ids_level_user_group'] = $user['group'];
		}
		if(!isset($data['ids_question'])){
			$data['ids_question'] = '0';
		}
		if(!isset($data['date_created'])){
			$data['date_created'] = date('Y-m-d H:i:s');
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_log (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);

		return mysql_insert_id($conn);
	}
	
	public function linkTeacher($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_log2teacher (".$keys.") values ('".$values."');";
		mysql_query($sql,$conn);
	}

	public function delete($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "delete from ".$pfx."wls_quiz_log where id  in (".$ids.");";
		mysql_query($sql,$conn);
		$sql = "delete from ".$pfx."wls_question_log where id_quiz  in (".$ids.");";
		mysql_query($sql,$conn);
	}

	public function update($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_quiz_log set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;
		$res = mysql_query($sql,$conn);

		return $res;
	}

	public function create(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz_log;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_log(
				 id int primary key auto_increment	
				 
				,date_created datetime 							 
				,id_user int default 0				
				,ids_level_user_group varchar(200) default '' 				
				,ids_question text				

				,id_quiz int default 0					
				,cent float default 0
				,mycent float default 0				
				,count_right int default 0
				,count_wrong int default 0
				,count_giveup int default 0
				,count_total int default 0 				
				,proportion float default 0	
								
				,time_start datetime default '1987-03-18'
				,time_stop datetime default '1987-03-18'
				,time_used int default 0				
	
				,application int default 0			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		
		$sql = "drop table if exists ".$pfx."wls_quiz_log2teacher;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_log2teacher(
				 id int primary key auto_increment	
				 
				,id_quiz_log int default 0
				,id_teacher int default 0	
				,markked int default 0		
				,time_markked datetime default '1986-08-09'
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		
		$sql = "ALTER TABLE ".$pfx."wls_quiz_log2teacher ADD INDEX idx_qu (id_quiz,id_teacher);";
		mysql_query($sql,$conn);
		return true;
	}

	public function importOne($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['main']);
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$quizlog = array();
		$id_quiz = 0;
		$id_user = null;
		$usergroups = '';
		$time = date('Y-m-d H:i:s');
		$application = 0;
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['name']){
				$title = $currentSheet->getCell($i.'3')->getValue();
				
				$sql = "update ".$pfx."wls_quiz set count_used = count_used + 1 where title = '".$title."' ";
				//mysql_query($sql,$conn);
				
				$sql = "select * from ".$pfx."wls_quiz where title = '".$title."';";
				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$id_quiz = $temp['id'];
				$application = $temp['application'];
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['time']){
				$time = $currentSheet->getCell($i.'3')->getValue();
			}			
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['username']){
				$value = $currentSheet->getCell($i.'3')->getValue();
				$sql = "select id from ".$pfx."wls_user where username = '".$value."';";
				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$id_user = $temp['id'];
				
				$sql = "select * from ".$pfx."wls_user_group2user where username = '".$value."' ";
				$res = mysql_query($sql,$conn);
				while($temp = mysql_fetch_assoc($res)){
					$usergroups .= $temp['id_level_group'].",";
				}
				$usergroups = substr($usergroups,0,strlen($usergroups)-1);
			}
		}

		$sql = "select				
				 answer
				,id
				,id_parent
				,markingmethod
				,description
				,cent
				,type
				,option2
				,option3
				,option4				
				,ids_level_knowledge
				
					from ".$pfx."wls_question where id_quiz = ".$id_quiz." order by id;";
		$res = mysql_query($sql,$conn);
		$questions = array();
		$ids_question = '';
		while($temp = mysql_fetch_assoc($res)){
			$temp['myAnswer'] = 'I_DONT_KNOW';
			$questions[] = $temp;
			$ids_question .= $temp['id'].',';
		}
		$ids_question = substr($ids_question,0,strlen($ids_question)-1);

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['quizLog']);
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['index']){
				$keys['index'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['myAnswer']){
				$keys['myAnswer'] = $i;
			}
		}

		for($i=3;$i<=$allRow;$i++){
			$value = $currentSheet->getCell($keys['index'].$i)->getValue();
			if($value=='')continue;
			$questions[intval($value)-1]['myAnswer'] = $currentSheet->getCell($keys['myAnswer'].$i)->getValue();
		}

		$logDta = array(
			 'id_quiz'=>$id_quiz
			,'date_created'=>$time
			,'answers'=>$questions
			,'ids_question'=>$ids_question
		);
		if($id_user!=null){
			$logDta['id_user'] = $id_user;
			$logDta['ids_level_user_group'] = $usergroups;
		}
//		print_r($logDta);exit();
		$this->addLog($logDta);
		
	}

	public function exportOne($path=null){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
//		$sql = "select * from ".$pfx."wls_question WHERE instr((select ids_question from ".$pfx."wls_quiz_log where id = 1),id)>0 ";
		$sql = "select * from ".$pfx."wls_quiz,".$pfx."wls_user,".$pfx."wls_quiz_log 
				where 
				".$pfx."wls_quiz.id = ".$pfx."wls_quiz_log.id_quiz and  
				".$pfx."wls_quiz_log.id = ".$this->id." and 
				".$pfx."wls_user.id = ".$pfx."wls_quiz_log.id_user ";
//		echo $sql;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$id_question_start = intval( substr($temp['ids_questions'],0,strpos($temp['ids_questions'],',')) );
		
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['main']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['username']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->lang['time']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('A3', $temp["title"]);
		$objPHPExcel->getActiveSheet()->setCellValue('B3', $temp["username"]);
		$objPHPExcel->getActiveSheet()->setCellValue('C3', $temp["date_created"]);
		
		$data = $this->getLogAnswers();
//		print_r($data);
//		echo $id_question_start;
//		exit();
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['quizLog']);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['index']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['myAnswer']);
		
		$keys = array_keys($data);
		$index = 3;
		for($i=0;$i<count($keys);$i++){
			if($data[$keys[$i]]=='I_DONT_KNOW')continue;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index,(intval($keys[$i]) - $id_question_start)+1 );
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index,$data[$keys[$i]]);
			$index++;
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$path = $this->c->filePath."download/quizlog".$this->id.".xls";
		$objWriter->save($path);
		return basename($path);
	}

	public function exportAll($path=null){}

	public function importAll($path){}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if($page==null)$page = 1;
		if($pagesize==null)$pagesize = 100;

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id'){
					$where .= " and ".$pfx."wls_quiz_log.id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_user'){
					$where .= " and id_user in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_level_subject'){
					$where .= " and ".$pfx."wls_quiz.id_level_subject in (".$search[$keys[$i]].") ";
				}				
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select 
			 ".$pfx."wls_quiz_log.id as id
			,".$pfx."wls_quiz_log.id_user
			,".$pfx."wls_quiz_log.cent
			,".$pfx."wls_quiz_log.mycent
			,".$pfx."wls_quiz_log.proportion
			,".$pfx."wls_quiz_log.count_right
			,".$pfx."wls_quiz_log.count_wrong
			,".$pfx."wls_quiz_log.ids_question
			,".$pfx."wls_quiz_log.application
			,".$pfx."wls_quiz.title as title
			from ".$pfx."wls_quiz_log 
				join ".$pfx."wls_quiz on ".$pfx."wls_quiz.id = ".$pfx."wls_quiz_log.id_quiz
			".$where." ".$orderby;

		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
//		echo $sql;exit();
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['name_application'] = $this->t->formatApplicationType($temp['application']);
			$temp['count_questions'] = count(explode(",", $temp['ids_question']));
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total
				from ".$pfx."wls_quiz_log 
				join ".$pfx."wls_quiz on ".$pfx."wls_quiz.id = ".$pfx."wls_quiz_log.id_quiz
				 ".$where;
		$res = mysql_query($sql2,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		return array(
			'page'=>$page,
			'data'=>$arr,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
		);
	}

	public function addLog($whatHappened){
		$answers = $whatHappened['answers'];
		$id_quiz = $whatHappened['id_quiz'];
		$ids_question = $whatHappened['ids_question'];
		$id_user = null;
		if(isset($whatHappened['id_user'])){
			$id_user = $whatHappened['id_user'];
			$ids_level_user_group = $whatHappened['ids_level_user_group'];
		}

		if($id_user==null){
			$userObj = new m_user();
			$me = $userObj->getMyInfo();
			$id_user = $me['id'];
			$ids_level_user_group = $me['group'];
		}

		$data = array(
			 'ids_question'=>$ids_question
			,'id_quiz'=>$id_quiz
			,'id_user'=>$id_user
			,'ids_level_user_group'=>$ids_level_user_group
		);
		
		if(isset($whatHappened['time_start'])){
			$data['time_start'] = $whatHappened['time_start'];
			$data['time_stop'] = $whatHappened['time_stop'];
			$data['time_used'] = $whatHappened['time_used'];
		}
		
		if(isset($whatHappened['date_created'])){
			$data['date_created'] = $whatHappened['date_created'];
		}
//		print_r($whatHappened);
//		print_r($data);
		$this->id = $this->insert($data);

		$count_right = 0;
		$count_wrong = 0;
		$count_giveup = 0;
		$cent = 0;
		$mycent = 0;
		$count_total = count($answers);

		$quesLogObj = new m_question_log();
		for($i=0;$i<count($answers);$i++){
			if($answers[$i]['type']==4||
			$answers[$i]['type']==5||
			$answers[$i]['type']==6||
			($answers[$i]['type']==7&&$answers[$i]['id_parent']==0))continue;
			$cent += $answers[$i]['cent'];
			$quesLogData = array(
				 'id_quiz'=>$id_quiz
				,'id_quiz_log'=>$this->id
				,'myAnswer'=>$answers[$i]['myAnswer']
				,'answer'=>$answers[$i]['answer']
				,'id_question'=>$answers[$i]['id']
				,'id_user'=>$id_user
				,'ids_level_user_group'=>$ids_level_user_group
				,'cent'=>$answers[$i]['cent']
				,'type'=>$answers[$i]['type']
				,'markingmethod'=>$answers[$i]['markingmethod']
			);
			if(isset($whatHappened['date_created'])){
				$quesLogData['date_created'] = $whatHappened['date_created'];
			}
			if($answers[$i]['markingmethod']==0||$answers[$i]['markingmethod']==null){// Markked automaticly
				$quesLogData['markked'] = 1;
			}
			$result = $quesLogObj->addLog($quesLogData);
			if($result==1){
				$count_right++;
				$mycent += $answers[$i]['cent'];
			}
			if($result==0)$count_wrong++;
			if($result==-1)$count_giveup++;
		}

		$this->update(array(
			 'count_right'=>$count_right
			,'count_wrong'=>$count_wrong
			,'count_giveup'=>$count_giveup
			,'count_total'=>$count_total
			,'proportion'=>( ($count_right*100)/($count_right+$count_wrong) )
			,'mycent'=>$mycent
			,'cent'=>$cent
			,'id'=>$this->id
		));

		$quizObj = new m_quiz();
		$quizObj->id_quiz = $id_quiz;
		$quizObj->mycent = $mycent;
		$quizObj->cumulative('score');
		$quizObj->cumulative('count_used');
		
		$this->getLogAnswers();
	}

	/**
	 * Read log from cache first. 
	 * If cache do not exist, then create the cache ,then delete the question_log from table. 
	 * */
	public function getLogAnswers(){		
		$cacheFilePath = $this->c->filePath.'cache/quizlog/'.$this->id.'.json';
		if(file_exists($cacheFilePath)){	
			$content = file( $cacheFilePath );		
			$content = implode("\n", $content);
			return json_decode($content,true);		
		}	
		
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();		
		
		$sql = "select application,id,ids_question from ".$pfx."wls_quiz_log where id = ".$this->id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp['application']==0){
			$sql = "select
			".$pfx."wls_question.id as id_question
			,".$pfx."wls_question_log.myanswer
			,".$pfx."wls_question_log.markingmethod
			,".$pfx."wls_question_log.markked
			 from ".$pfx."wls_question,".$pfx."wls_question_log
			 where ".$pfx."wls_question.id = ".$pfx."wls_question_log.id_question
			 and ".$pfx."wls_question_log.id_quiz_log = ".$this->id."
			 order by  ".$pfx."wls_question.id  ";		
		}else{			
			
		}
		$res = mysql_query($sql,$conn);
		if($res==false  ){
			$this->error("Question Log do not exist,about the quizlog ".$this->id);
			return false;
		}else{
			$arr = array();
			$markked = 1;
			while($temp = mysql_fetch_assoc($res)){
				if($temp['myanswer']==''||$temp['myanswer']==null){
					$temp['myanswer'] = 'I_DONT_KNOW';
				}
				$arr[$temp['id_question']] = $temp['myanswer'];
				if($temp['markked']!=1){
					$markked = 0;
				}
			}
			if($markked == 1){
				$content = json_encode($arr);
				$handle=fopen($cacheFilePath,"a");
				fwrite($handle,$content);
				fclose($handle);
				
				$sql = "delete from ".$pfx."wls_question_log where id_quiz_log = ".$this->id." and markingmethod =0 ;";
				mysql_query($sql,$conn);
			}else{

				$sql = " select * from ".$pfx."wls_user_group2subject2teacher  
WHERE
 instr(
(select ids_level_user_group from ".$pfx."wls_quiz_log where id = 1)
,id_level_group)>0 
and id_level_subject = 
(select id_level_subject from ".$pfx."wls_quiz_log,".$pfx."wls_quiz where ".$pfx."wls_quiz_log.id_quiz = ".$pfx."wls_quiz.id and ".$pfx."wls_quiz_log.id = 1 )
				
				";
				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$data = array(
					 'id_teacher'=>$temp['id_user']
					,'id_quiz_log'=>$this->id
				);
				$this->linkTeacher($data);
			}
			
//			echo $sql;
			
			return $arr;
		}
	}
	
	public function getRankings($id_quiz){
		
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();		
		
		$sql = "select 
		 ".$pfx."wls_user.username
		,".$pfx."wls_user.column0
		,".$pfx."wls_quiz_log.mycent
		from 
		 ".$pfx."wls_quiz_log
		,".$pfx."wls_user

		where 
		 id_quiz = ".$id_quiz." and
		 ".$pfx."wls_quiz_log.id_user = ".$pfx."wls_user.id
		order by mycent desc ;";
		$res = mysql_query($sql,$conn);
		$arr = array();
		$index = 1;
		while($temp = mysql_fetch_assoc($res)){
			$temp['index'] = $index;
			$arr[] = $temp;
			$index++;
		}
		
		return array(
			'page'=>1,
			'data'=>$arr,
			'sql'=>$sql,
			'total'=>1,
			'pagesize'=>1,
		);
		
	}
}
?>