<?php
class install_yf_gajczs{	
	public function readPapers(){
$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var arr = [892,893,894,895,896,897,898,899,900,901,902,903,904,905,906,907,908,909,910,911,912,913,914,915,916,917,918,919,920,921,922,923,924,925,926,927,928,929,930,931,932,933,934,935,936,937,938,939,940,941,942,989,990,991,992,993,994,995,996,997,1044,1045,1046,1047,1048,1049,1050,1051,1052,1053,1193,1194,1195,1196,1197,1198,1199,1200];
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
		header("Content-type: text/html; charset=UTF-8");
		include_once dirname(__FILE__).'/../../../model/quiz/paper/yf/gajczs.php';
		
		$m = new m_quiz_paper_yf_gajczs();
		$m->path = 'E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/公安基础知识/5_'.$_REQUEST['id'].'.html';
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');		
		$m->readFile();
		$m->getPaper();		
		

//		$m->viewPaper();
		
		$m->getQuestions();
		$m->saveQuestions();
	}
}
?>