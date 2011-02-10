<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_p1 extends install_yf{	
	
	function install_yf_p1(){
		$this->type = 'p1';
		$this->ids = '139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,630,631,632,633,634,635,636,637,638,639,640,641,642,643,644,645,646,647,670,671,672,673,674,675,676,677,1199,1200,1201,1202,1203,1204,1205,1206,1207,1208,1209,1210,1211,1212,1213,1598,1599,1600,1601,1602,1603,1604,1605,2973,2974,2975,2976,2977,2978,2979,2980,2981,2982,2983';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/经济类/模拟题试卷/中级会计/财务管理/2_';
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
		
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/p1.php';
		
		$m = new m_quiz_paper_yf_p1();
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