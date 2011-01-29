<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_P4 extends install_yf{	
	
	function install_yf_P4(){
		$this->type = 'P4';
		$this->ids = '2161,2162,2163,2164,2165,2166,2167,2168,3310,3311,3312,3313,3314,3315,3316,3317,3318,3319,3320,3321,3322,3323,3324';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/经济类/模拟题试卷/注册会计/公司战略与风险管理/6_';
	}
	
	public function readPaper(){
		header("Content-type: text/html; charset=UTF-8");
		
//		include_once dirname(__FILE__).'/../../../model/quiz/paper.php';
//		$obj = new m_quiz_paper();
//		$obj->create();
//		
//		include_once dirname(__FILE__).'/../../../model/question.php';
//		$obj = new m_question();
//		$obj->create();
		
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/P4.php';
		
		$m = new m_quiz_paper_yf_P4();
		$m->path = $this->path.$_REQUEST['id'].'.html';
		$m->yfnum = $_REQUEST['id'];
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		

		$m->viewPaper();
		
		$m->getQuestions();
		$m->saveQuestions();
	}
}
?>