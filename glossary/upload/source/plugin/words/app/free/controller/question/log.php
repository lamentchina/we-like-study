<?php
include_once dirname(__FILE__).'/../../model/question/log.php';

class question_log extends wls{
	
	private $m = null;
	
	function question_log(){
		parent::wls();
		$this->m = new m_question_log();
	}
	
	public function getMarkInfo(){
		$data = $this->m->getMarkInfo($_POST['id_quiz_log'],$_POST['index']);
		$data['correct'] = ($data['correct']==1)?$this->lang['right']:$this->lang['wrong'];
		$data['markked'] = ($data['markked']==1)?$this->lang['markked']:$this->lang['waitForMark'];
		echo json_encode($data);
	}
}
?>