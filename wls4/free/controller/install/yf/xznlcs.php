<?php
class install_yf_xznlcs{	
	public function readPapers(){
$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var arr = [201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230];
var index = 0;
var readPaper = function(){
	if(index==arr.length-1)return;
	$.ajax({
		url: \"wls.php?controller=install_yf&action=readPaper&id=\"+arr[index],
		success: function(msg){
			$(\"#console\").html($(\"#console\").html()+\"<br/>下载试卷\"+arr[index]);
			$(\"#index\").html(arr[index]+':'+arr.length);
			index ++;
			readPaper(index);
		}
	});
}
readPaper();
</script>
</head>
<body>
<div id='index'><div>
<div id=\"console\"><div>
</body>
</html>
		";
		echo $html;		
	}	
	
	public function readPaper(){
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/xznlcs.php';
		
		$m = new m_quiz_paper_yf_xznlcs();
		$m->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_'.$_REQUEST['id'].'.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		$m->getQuestions();
		$m->saveQuestions();	
	}	
}
?>