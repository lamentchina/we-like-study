<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_p3 extends install_yf{	
	
	function install_yf_p3(){
		$this->type = 'p3';
		$this->ids = '351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,394,395,396,397,398,399,400,401,402,403,404,405,406,483,484,485,792,793,794,795,796,797,798,799,800';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/经济类/模拟题试卷/注册会计/审计/6_';
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
		
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/p3.php';
		
		$m = new m_quiz_paper_yf_p3();
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