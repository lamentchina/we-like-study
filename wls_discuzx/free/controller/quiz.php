<?php
class quiz extends wls{
	
	private $m = null;
	
	function quiz(){
		parent::wls();
		include_once $this->c->license.'/model/quiz.php';
		$this->m = new m_quiz();
	}
	
	public function getQuestions(){
		$data = $this->m->getQuestions($_POST['questionsIds']);
		echo json_encode($data);
	}
	

}
?>