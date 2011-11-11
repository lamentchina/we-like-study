<?php
include_once dirname(__FILE__).'/../quiz/log.php';

class m_user_teacher extends wls{

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "select 

		 ".$pfx."wls_quiz_log2teacher.id
		,".$pfx."wls_quiz_log2teacher.markked
		,".$pfx."wls_quiz_log2teacher.time_markked
		,".$pfx."wls_quiz_log2teacher.id_teacher
		

		
		,".$pfx."wls_quiz_log.id_user

		,".$pfx."wls_quiz.title
		,".$pfx."wls_quiz.name_subject
		
		from ".$pfx."wls_quiz_log2teacher,".$pfx."wls_quiz_log,".$pfx."wls_quiz
		 where
			 ".$pfx."wls_quiz.id = ".$pfx."wls_quiz_log.id_quiz
			 and ".$pfx."wls_quiz_log.id = ".$pfx."wls_quiz_log2teacher.id_quiz_log

		";
//				echo $sql;
		$arr = array();
		$res = mysql_query($sql,$conn);
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_quiz_log2teacher ";
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

	public function getOne($id){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "select * from ".$pfx."wls_quiz_log2teacher,".$pfx."wls_quiz_log where
			  ".$pfx."wls_quiz_log.id = ".$pfx."wls_quiz_log2teacher.id_quiz_log and  ".$pfx."wls_quiz_log2teacher.id= ".$id;

		$arr = array();
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		return $temp;
	}

	public function getQuestionsByIds($ids,$id_quiz_log){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "SELECT 
		 w_wls_question.id
		,w_wls_question.type		
		,w_wls_question.title
		,w_wls_question.optionlength
		,w_wls_question.option1
		,w_wls_question.option2
		,w_wls_question.option3
		,w_wls_question.option4
		,w_wls_question.option5
		,w_wls_question.option6
		,w_wls_question.option7
		,w_wls_question.description
		,w_wls_question.cent
		,w_wls_question.id_quiz
		,w_wls_question.id_parent
		,w_wls_question.layout
		,w_wls_question.path_listen
		
		,w_wls_question_log.id as id_question_log
		,w_wls_question_log.answer
		,w_wls_question_log.myAnswer
		,w_wls_question_log.markingmethod
		FROM w_wls_question left join w_wls_question_log on w_wls_question_log.id_question = w_wls_question.id and w_wls_question_log.id_quiz_log = ".$id_quiz_log."
		
		
		where w_wls_question.id in (".$ids.") 
		 ";
//		echo $sql;
		$arr = array();
		$res = mysql_query($sql,$conn);
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		
		return $arr;
	}
	
	
	public function finishMark($id){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "update ".$pfx."wls_question_log set markked = 1 where id_quiz_log = ".$id;
		mysql_query($sql,$conn);
		
		$sql = "update ".$pfx."wls_quiz_log set mycent = (select sum(mycent) from ".$pfx."wls_question_log where id_quiz_log = ".$id." ) 
		
		where id = ".$id;
		mysql_query($sql,$conn);	

		$userObj = new m_user();
		$me = $userObj->getMyInfo();
		$sql = "update ".$pfx."wls_quiz_log2teacher set markked = 1 ,time_markked = '".date('Y-m-d H:i:s')."' where id_teacher = ".$me['id']." and id_quiz_log = ".$id;
		mysql_query($sql,$conn);	
		
		$quizLogObj = new m_quiz_log();
		$quizLogObj->id = $id;
		$quizLogObj->getLogAnswers();		
	}
}
?>