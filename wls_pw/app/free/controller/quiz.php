<?php
include_once dirname(__FILE__).'/../model/quiz.php';

class quiz extends wls{	
	private $m = null;
	
	function quiz(){
		parent::wls();		
		$this->m = new m_quiz();
	}
	
	public function about(){
		$html = "
			<html>		
			<head>
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
			</head>
			<body>
<p>
This is an open-source project, you can update this system by downloading the most
recent code from http://code.google.com/p/we-like-study/downloads/list 
<br/>
If you have any suggestions, contact me: wei1224hf@gmail.com
</p>

Here , thank all these people who cares this project. With their help , our software can grow better:
<ul>
<li>南湖煮酒</li> 
<li>日照爱牙</li>
<li>铁臂阿童木</li>
<li>天&睿</li>
<li>天天飞</li>
<li>在云端</li>
</ul>

This project is small, but yet it can do a lot cool works. 
That's because its foundations are great:
<ul>
<li>Php5 </li>
<li>ExtJS </li>
<li>QwikiOffice </li>
<li>JQueryJs </li>
<li>AmChart </li>
<li>PhpExcel </li>
<li>securimage </li>
</ul>

Authors:
<ul>
<li>wei1224hf</li>
<li>First Eyes </li>
<li>xiaoyong8000</li>
<li>范小延</li>
</ul>
			
</body>
</html>
		";
		
		echo $html;
	}
}
?>