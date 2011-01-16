<?php
class install_yf {
	
	public function xznlcs(){
		include_once dirname(__FILE__).'/../../model/quiz/paper/yf/xznlcs.php';
		
		$m = new m_quiz_paper_yf_xznlcs();
		$m->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_201.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		$m->getQuestions();
		$m->saveQuestions();	

		$m->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_202.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		$m->getQuestions();
		$m->saveQuestions();	
		
		$m->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_203.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		$m->getQuestions();
		$m->saveQuestions();	
		
		$m->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_204.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		$m->getQuestions();
		$m->saveQuestions();	
//		$m->viewPaper();		
	}
}
?>