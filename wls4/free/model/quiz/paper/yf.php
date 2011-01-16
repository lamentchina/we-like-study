<?php
include_once dirname(__FILE__).'/../paper.php';

class m_quiz_paper_yf extends m_quiz_paper{
	public $images = null;
	public $mp3s = null;
	public $paperHtmlContent = null;
	public $menuPath = null;
	public $allIds = null;
	public $path = null;
	
	public function saveImages(){}
	
	public function getMp3ListForXunlei(){}
	
	public function viewPaper(){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>m_quiz_paper_yf</title>
</head>
<body>';
		$html .= $this->paperHtmlContent;
		$html .= 
'</body>
</html>';
		$html = str_replace("src=\"","src=\"../file/images/",$html);
		echo $html;
	}
}

interface yfActions {
	
	public function getPaper();
	public function getQuestions();
	public function savePathListForXunlei();
	public function readFile();
	public function import();
	
}
?>