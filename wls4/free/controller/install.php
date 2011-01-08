<?php
class install extends wls {
	public function createTables(){
		include_once $this->c->license.'/model/subject.php';
		$obj = new m_subject();
		$obj->create();				
				
		include_once $this->c->license.'/model/user/group.php';
		$obj = new m_user_group();
		$obj->create();	

		include_once $this->c->license.'/model/user.php';
		$obj = new m_user();
		$obj->create();		
		
		include_once $this->c->license.'/model/question.php';
		$obj = new m_question();
		$obj->create();	
		
		include_once $this->c->license.'/model/quiz/paper.php';
		$obj = new m_quiz_paper();
		$obj->create();

		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->create();				
	}
}
?>