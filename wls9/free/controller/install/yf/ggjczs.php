<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_ggjczs extends install_yf{	
	
	function install_yf_ggjczs(){
		$this->type = 'ggjczs';
		$this->ids = '562,563,564,565,566,567,568,569,570,571,572,573,574,575,576,577,578,579,580,581,582,583,584,585,586,587,588,589,590';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/公共基础知识/';
	}
	
	public function readPaper(){
		header("Content-type: text/html; charset=UTF-8");
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/ggjczs.php';
		
		$m = new m_quiz_paper_yf_ggjczs();
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