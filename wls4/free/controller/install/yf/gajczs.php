<?php
include_once dirname(__FILE__).'/../yf.php';

class install_yf_gajczs extends install_yf{	
	
	function install_yf_gajczs(){
		$this->type = 'gajczs';
		$this->ids = '892,893,894,895,896,897,898,899,900,901,902,903,904,905,906,907,908,909,910,911,912,913,914,915,916';
		$this->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/公安基础知识/5_';
	}
	
	public function readPaper(){
		header("Content-type: text/html; charset=UTF-8");
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/gajczs.php';
		
		$m = new m_quiz_paper_yf_gajczs();
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