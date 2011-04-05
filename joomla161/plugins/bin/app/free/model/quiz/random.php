<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../user.php';

class m_quiz_random extends m_quiz{

	public $id = null;
	
	public function getMyRandomsId($id_level_subject,$questionType){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$userObj = new m_user();
		$me = $userObj->getMyInfo();
		
		$sql = "select questiontypes from ".$pfx."wls_subject where id_level = '".$id_level_subject."' ";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$arr = explode(",",$temp['questiontypes']);
		
		$sql = "select ".$pfx."wls_question.id from 
		".$pfx."wls_question,
		".$pfx."wls_quiz where 
		 ".$pfx."wls_question.id_quiz = ".$pfx."wls_quiz.id and 
		 ".$pfx."wls_quiz.id_level_subject = '".$id_level_subject."' and 
		 type2 = '".$arr[$questionType]."' 
		  order by rand() limit 0,50" ;
		$res = mysql_query($sql,$conn);
//		echo $sql;
		$ids = '';
		while($temp = mysql_fetch_assoc($res)){
			$ids .= $temp['id'].',';
		}
		
		$ids = substr($ids,0,strlen($ids)-1);
		return $ids;
	}
	
	public function getAnswers($answers){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();


		$ques_ = array();
		for($i=0;$i<count($answers);$i++){
			$ques_[$answers[$i]['id']] = $answers[$i]['answer'];
		}

		//It's written in controller/quiz.php
		$questionObj = new m_question();
		$answers = $questionObj->getAnswers($ques_);

		return $answers;
	}
}
?>