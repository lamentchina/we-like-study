<?php
include_once dirname(__FILE__).'/../model/quiz.php';

class quiz extends wls{	
	private $m = null;
	
	function quiz(){
		parent::wls();		
		$this->m = new m_quiz();
	}
	
	public function about(){
		$filename = $this->c->filePath."about_cn.html";

		$content = file( $filename );
		$content = implode("\n", $content);
		$content = str_replace("\n","",$content);
		
		echo $content;
	}
}
?>