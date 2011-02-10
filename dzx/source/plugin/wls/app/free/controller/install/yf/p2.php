<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_p2 extends install_yf{	
	
	function install_yf_p2(){
		$this->type = 'p2';
		$this->ids = '78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,171,172,173,174,175,176,600,601';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/经济类/模拟题试卷/中级会计/会计实务/6_';
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
		
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/p2.php';
		
		$m = new m_quiz_paper_yf_p2();
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