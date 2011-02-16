<?php
class m_quiz extends wls{
	
	public $count_giveup = 0;		
	public $count_right = 0;
	public $count_wrong = 0;
	public $count_manual = 0;
	public $count_total = 0;
	public $questions = array();
	public $ids_question = '';

	/**
	 * Get questions by ids.
	 * Each type of quiz, like quiz-paper , quiz-wrongs, quiz-random 
	 * These's client-side will call this
	 * 
	 * @param $ids Database table wls_question's id
	 * @return $data
	 * */
	public function getQuestions($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select 
			id,type,title,optionlength,
			option1,option2,option3,option4,option5,option6,option7,
			description,cent,id_quiz_paper,id_parent,layout,path_listen
			 from ".$pfx."wls_question where id in (".$ids.") or (id_parent !=0 and id_parent in (".$ids.")) order by id; ";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['title'] = str_replace("__IMAGEPATH__",$this->c->filePath."images/",$temp['title']);
			$data[] = $temp;
		}
		
		return $data;
	}
	
	/**
	 * The client-side post user's quiz result to the server, 
	 * The server check the answers.
	 * 
	 * @param $myAnswers See this in each controller file , it's mostly from $_POST
	 * @return $data
	 * */
	public function getAnswers($myAnswers){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$keys = array_keys($myAnswers);
		$ids = implode(",",$keys);
		
		$sql = "select answer,id,description,cent,type,option2,option3,option4 from ".$pfx."wls_question where id in (".$ids.") order by id ; ";
		$res = mysql_query($sql,$conn);
		$data = array();
		
		while($temp = mysql_fetch_assoc($res)){
			$temp['myAnswer'] = $myAnswers[$temp['id']];
			$data[] = $temp;
		}
				
		return $data;
	}	
}
?>