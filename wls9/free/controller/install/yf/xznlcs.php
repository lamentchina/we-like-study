<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_xznlcs extends install_yf{	
	function install_yf_xznlcs(){
		$this->type = 'xznlcs';
		$this->ids = '201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_';
	}
	
	public function readPaper(){
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/xznlcs.php';
		
		$m = new m_quiz_paper_yf_xznlcs();
		$m->path = $this->path.$_REQUEST['id'].'.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->yfnum = $_REQUEST['id'];	
		$m->readFile();
		$m->getPaper();		
		

		$m->viewPaper();
		
		$m->getQuestions();
//		print_r($m->questions);
		$m->saveQuestions();
	}	
}
?>