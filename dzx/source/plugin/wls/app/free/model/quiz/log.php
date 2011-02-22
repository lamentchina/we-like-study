<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../user.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../knowledge/log.php';
include_once dirname(__FILE__).'/../question/log.php';
include_once dirname(__FILE__).'/wrong.php';

include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_quiz_log extends m_quiz implements dbtable{

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
		if(!isset($data['ids_questions'])){
			$data['ids_questions'] = '0';
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_log (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function delete($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "delete from ".$pfx."wls_quiz_log where id  in (".$ids.");";
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
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
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
				,ids_questions text				

				,id_quiz_paper int default 0					
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
		return true;
	}

	public function importExcel($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$obj = new m_subject();
		$data = $obj->getList(1,100);
		if(count($data['data'])<1){
			$this->error(array('description'=>'quiz log wrong'));
			return false;
		}		

		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['paper']);//TODO
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$quizlog = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['time']){
				$quizlog['date_created'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['id_user']){
				$quizlog['id_user'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['id_paper']){
				$quizlog['id_quiz_paper'] = $currentSheet->getCell($i."2")->getValue();
				$sql = "select id_level_subject from ".$pfx."wls_quiz_paper where id =".$quizlog['id_quiz_paper'];

				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$quizlog['id_level_subject'] = $temp['id_level_subject'];
			}
		}
		$sql = "select questions from ".$pfx."wls_quiz_paper where id = ".$quizlog['id_quiz_paper'];
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);	
		$paperQuestions = $temp['questions'];
		$quizlog['id_question'] = $paperQuestions;
				
		$quizlog['id'] = $this->insert($quizlog);
		
		$sql = "select min(id) as minid from ".$pfx."wls_question where id_quiz_paper = ".$quizlog['id_quiz_paper'];
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);	
		$minQuesId = $temp['minid'];

		$currentSheet = $this->phpexcel->getSheetByName('questions');
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['id_question']){
				$keys['id_question'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['answer']){
				$keys['myanswer'] = $i;
			}
		}
		
		$quesObj = new m_question_log();
		$index = 0;
		$queslogs = array();
		$count_wrong = 0;
		$count_right = 0;
		$quizlog_cent = 0;
		$id_question = '';		
		$wrongObj = new m_quiz_wrong();		
		$knowledgeLogObj = new m_knowledge_log();		
		for($i=3;$i<=$allRow;$i++){
			$sql = "select id,answer,cent,id_parent,ids_level_knowledge from ".$pfx."wls_question where id = ".($currentSheet->getCell($keys['id_question'].$i)->getValue()+($minQuesId-1));

			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);
			$id_question .= $temp['id'].',';
			$queslog = array(
				'date_created'=>$quizlog['date_created']
				,'id_user'=>$quizlog['id_user']
				,'id_level_subject'=>$quizlog['id_level_subject']
				,'id_quiz_paper'=>$quizlog['id_quiz_paper']
				,'id_quiz_log'=>$quizlog['id']
				,'id_question'=>$temp['id']
				,'id_question_parent'=>$temp['id_parent']
				,'answer'=>$temp['answer']
				,'cent'=>$temp['cent']
				,'myanswer'=>$currentSheet->getCell($keys['myanswer'].$i)->getValue()
			);
			if($queslog['myanswer']==$queslog['answer']){
				$queslog['correct'] = 1;
				$quizlog_cent += $queslog['cent'];
				$count_right ++;
			}else{
				$queslog['correct'] = 0;
				$count_wrong ++;
				$wrong = array(
					 'id_question' => $temp['id']
					,'id_quiz_paper' => $quizlog['id_quiz_paper']
					,'id_level_subject' => $quizlog['id_level_subject']
					,'id_user'=>$quizlog['id_user']
					,'date_created'=>$quizlog['date_created']
				);
				$wrongObj->insert($wrong);				
			}
			$knowledges = explode(",",$temp['ids_level_knowledge']);
			for($ii=0;$ii<count($knowledges);$ii++){
				if($queslog['correct']==1){				
					$knowledgeLog = array(
						 'date_created'=>date('Y-m-d',strtotime($quizlog['date_created']))
						,'id_user'=>$quizlog['id_user']
						,'id_level_knowledge'=>$knowledges[$ii]
						,'count_right'=>1
						,'date_slide'=>86400
					);
				}else{
					$knowledgeLog = array(
						 'date_created'=>date('Y-m-d',strtotime($quizlog['date_created']))
						,'id_user'=>$quizlog['id_user']
						,'id_level_knowledge'=>$knowledges[$ii]
						,'count_wrong'=>1
						,'date_slide'=>86400
					);
				}
				$knowledgeLogObj->insert($knowledgeLog);
			}

			$queslog['id'] = $quesObj->insert($queslog);
			$queslogs[] = $queslog;
		}
		$id_question = substr($id_question,0,strlen($id_question)-1);
		$quizlog_update = array(
			 'id'=>$quizlog['id']
			,'mycent'=>$quizlog_cent
			,'count_right'=>$count_right
			,'count_wrong'=>$count_wrong
			,'proportion'=>$count_right/($count_wrong+$count_right)			
		);
		$this->update($quizlog_update);
	}

	public function exportExcel(){}

	public function cumulative($column){}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if($page==null)$page = 1;
		if($pagesize==null)$pagesize = 100;
		
		
		$subjectObj = new m_subject();
		$data = $subjectObj->getList(1,1000);
		$list = $data['data'];
		$subjects = array();
		for($i=0;$i<count($list);$i++){
			$subjects[$list[$i]['id_level']] = $list[$i]['name'];
		}

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_user'){
					$where .= " and id_user in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_level_subject'){
					$where .= " and id_level_subject in ('".$search[$keys[$i]]."') ";
				}	
				if($keys[$i]=='id_level_subject_'){
					$where .= " and id_level_subject like '".$search[$keys[$i]]."%' ";
				}		
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_quiz_log ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			if($temp['id_level_subject']!=0){
				$temp['name_subject'] = $subjects[$temp['id_level_subject']];
			}
			$temp['name_application'] = $this->t->formatApplicationType($temp['application']);
			$temp['count_questions'] = count(explode(",", $temp['id_question']));
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_quiz_log ".$where;
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

	public function addLog($whatHappened){}

	public function getLogAnswers(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select application,id,id_question from ".$pfx."wls_quiz_log where id = ".$this->id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp['application']==0){
			$sql = "select 
			
			".$pfx."wls_question.id as id_question,
			".$pfx."wls_question_log.myanswer
			 from ".$pfx."wls_question
			LEFT JOIN  
			".$pfx."wls_question_log
				ON ".$pfx."wls_question.id = ".$pfx."wls_question_log.id_question
			
			where ".$pfx."wls_question.id in (".$temp['id_question'].")
			 order by ".$pfx."wls_question.id
			  ";					
		}else{		
			$sql = "select id,id_question,myanswer from ".$pfx."wls_question_log where id_quiz_log = ".$this->id." order by id_question;";
		}
		$res = mysql_query($sql,$conn);
		if($res==false){
			echo $sql;	
		}else{
			$arr = array();
			while($temp = mysql_fetch_assoc($res)){
				if($temp['myanswer']==''||$temp['myanswer']==null)$temp['myanswer'] = 'I_DONT_KNOW';
				$arr[$temp['id_question']] = $temp['myanswer'];
			}
			return $arr;
		}
	}
	
	public function exportQuiz($type){}
	
	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}
	
	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}
	
	public function getQuizIds(){}
}
?>