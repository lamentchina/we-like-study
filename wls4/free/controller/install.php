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
		
		include_once $this->c->license.'/model/knowledge/log.php';
		$obj = new m_knowledge_log();
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
		$obj = new m_quiz_wrong();
		$obj->create();
	}

	/**
	 * 插入一些测试用数据
	 * */
	public function insertData4Test(){
		$this->createTables();
		
		include_once $this->c->license.'/model/subject.php';
		$obj = new m_subject();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_subject_chinaoffical.xls");

		include_once $this->c->license.'/model/knowledge.php';
		$obj = new m_knowledge();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_knowledge.xls");

		include_once $this->c->license.'/model/user/group.php';
		$obj = new m_user_group();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user_group.xls");
		$obj->importExcelOne(dirname(__FILE__)."../../../../file/test/wls_user_group_one.xls");
		$obj->importExcelOne(dirname(__FILE__)."../../../../file/test/wls_user_group_one2.xls");

		include_once $this->c->license.'/model/user.php';
		$obj = new m_user();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user.xls");


		include_once $this->c->license.'/model/user/privilege.php';
		$obj = new m_user_privilege();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user_privilege.xls");

		include_once dirname(__FILE__)."/install/yf.php";
		$obj = new install_yf();
		$obj->xznlcs();

		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_quiz_log.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_quiz_log2.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_quiz_log3.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_quiz_log4.xls");
		//		include_once $this->c->license.'/model/quiz/paper.php';
		//		$obj = new m_quiz_paper();
		//		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wlls_quiz_paper.xls");
	}
}
?>