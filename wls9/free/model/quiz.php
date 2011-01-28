<?php
class m_quiz extends wls{
	
	public $count_giveup = 0;		
	public $count_right = 0;
	public $count_wrong = 0;
	public $count_manual = 0;
	public $count_total = 0;
	public $questions = array();
	public $ids_question = '';

	public function getQuestions($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select 
			id,type,title,optionlength,
			option1,option2,option3,option4,option5,option6,option7,
			description,cent,id_quiz_paper,id_parent
			 from ".$pfx."wls_question where id in (".$ids.") or (id_parent !=0 and id_parent in (".$ids.")) order by id; ";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		
		return $data;
	}
	
	public function getAnswers($myAnswers){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$keys = array_keys($myAnswers);
		$ids = implode(",",$keys);
		
		$sql = "select answer,id,description,cent,type from ".$pfx."wls_question where id in (".$ids.") ; ";
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