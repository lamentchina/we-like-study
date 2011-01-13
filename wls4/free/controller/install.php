<?php
class install extends wls {
	
	/**
	 * 初始化数据库表
	 * */
	public function createTables(){
		include_once $this->c->license.'/model/subject.php';
		$obj = new m_subject();
		$obj->create();		

		include_once $this->c->license.'/model/knowledge.php';
		$obj = new m_knowledge();
		$obj->create();				
				
		include_once $this->c->license.'/model/user/group.php';
		$obj = new m_user_group();
		$obj->create();	

		include_once $this->c->license.'/model/user.php';
		$obj = new m_user();
		$obj->create();	

		include_once $this->c->license.'/model/user/privilege.php';
		$obj = new m_user_privilege();
		$obj->create();			
		
		include_once $this->c->license.'/model/question.php';
		$obj = new m_question();
		$obj->create();	
		
		include_once $this->c->license.'/model/question/log.php';
		$obj = new m_question_log();
		$obj->create();			
		
		include_once $this->c->license.'/model/quiz/paper.php';
		$obj = new m_quiz_paper();
		$obj->create();

		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->create();	

		include_once $this->c->license.'/model/quiz/wrong.php';
		$obj = new m_quiz_worng();
		$obj->create();			
	}
	
	/**
	 * 插入一些测试用数据
	 * */
	public function insertData4Test(){
		include_once $this->c->license.'/model/subject.php';
		$obj = new m_subject();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_subject_highschool.xls");	
		
		include_once $this->c->license.'/model/knowledge.php';
		$obj = new m_knowledge();
//		echo 241242134213;
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_knowledge.xls");			
				
		include_once $this->c->license.'/model/user/group.php';
		$obj = new m_user_group();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user_group.xls");	

		include_once $this->c->license.'/model/user.php';
		$obj = new m_user();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user.xls");	
		
		
		include_once $this->c->license.'/model/user/privilege.php';
		$obj = new m_user_privilege();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user_privilege.xls");		
		
		include_once $this->c->license.'/model/quiz/paper.php';
		$obj = new m_quiz_paper();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wlls_quiz_paper.xls");			
	}
}
?>