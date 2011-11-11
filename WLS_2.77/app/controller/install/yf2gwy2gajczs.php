<?php
include_once dirname(__FILE__).'/../../model/question.php';
include_once dirname(__FILE__).'/../../model/quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';

class install_yf2gwy2gajczs extends wls {

	public $types = null;
	public $id_quiz = 0;
	public $id_quiz_paper = 0;
	public $quizData = null;

	public $ids = null;
	public $paperHtmlContent = null;
	public $allIds = null;

	public $questions = array();
	public $len = 1;
	public $choiceLength = 0;
	public $ids_level_knowledge = array();

	public $id_level_subject = '500103';
	function install_yf2gwy2gajczs(){
		parent::wls();
		$this->types = array(
			'500103'=> array(
				'su_id'=>5
				,'mainfolder'=>'f公务员f'
				,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f公安基础知识模拟题f" 
			),
		);
		
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "select * from ".$pfx."wls_subject where id_level like '".$this->id_level_subject."%' and isknowledge =1 ; ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while ($temp = mysql_fetch_assoc($res)){
			$data[] = $temp['id_level'];
		}
		$this->ids_level_knowledge = $data;
	}

	function html(){

		$filename = $this->types[$this->id_level_subject]['path']."/ids.txt";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		$content = file( $filename );
		$content = implode("\n", $content);
		$content = str_replace("\n","",$content);
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var ids = [".$content."];
var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_yf2gwy2gajczs&action=down',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length )return;
			down();			
			index++;
		}
	});
}
down();
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

	function down(){
		$filename = $this->types[$this->id_level_subject]['path']."/".$_REQUEST['id'].".html";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');

		if(!file_exists($filename)){
			$content = file("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=".$this->types[$this->id_level_subject]['su_id']."&ex_id=".$_REQUEST['id']);
			$content = implode("\n", $content);
			$handle=fopen($filename,"a");
			fwrite($handle,$content);
			fclose($handle);

			$this->readFile();
		}else{
			$this->readFile();
		}
		echo $filename;
	}

	public function getPaper(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "select * from ".$pfx."wls_subject where id_level = '".$this->id_level_subject."';";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$data = array(
			  'id_level_subject'=>$this->id_level_subject
			,'name_subject'=>$temp['name']
			,'title'=>$temp['name'].'_'.$_REQUEST['id']
			,'author'=>'admin'
		);
		$this->quizData = $data;

		$quizObj = new m_quiz();
		$this->id_quiz = $quizObj->insert($data);

		$paperObj = new m_quiz_paper();
		$data = array(
			 'id_quiz'=>$this->id_quiz
		,'money'=>rand(0,10)
		);
		$this->id_quiz_paper = $paperObj->insert($data);
	}

	public $up = "";
	public $down = "";
	public $questionIndex = "";

	public function getCheckQuestions(){
		$content = $this->paperHtmlContent;
		if(!is_array($this->up)){
			$p1 = strpos($content,$this->up);
		}else{
			for($i=0;$i<count($this->up);$i++){
				$p1 = strpos($content,$this->up[$i]);
				if($p1!=false)break;
			}
		}
		$p2 = strpos($content,$this->down);
		if($p2==false)$p2 = strlen($this->paperHtmlContent);
		$content = substr($content,$p1,$p2-$p1);

		$arr = explode("fs type=hidden",$content);

		$p1 = strpos($arr[0],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[0],"tan\" value=\"",$p1);
		$startPoint = substr($arr[0],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));

		$p1 = strpos($arr[count($arr)-2],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[count($arr)-2],"tan\" value=\"",$p1);
		$endPoint = substr($arr[count($arr)-2],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));



		$p1 = strpos($arr[0],"<td width='1%' valign='top'>");
		$p2 = strpos($arr[0],".<",$p1);
		$startIndex = substr($arr[0],$p1+strlen("<td width='1%' valign='top'>"),$p2-$p1-strlen("<td width='1%' valign='top'>"));


		for($i=($startPoint+1);$i<=($endPoint+1);$i++){
			$p1 = strpos($content,">".($startIndex).".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
				
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"<a name='anway'>");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$startIndex.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);
			$title = str_replace("</table>","",$title);

				

			//			echo $data;exit();
			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			//			echo $answer;exit();

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
				
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);
			$description = str_replace("name=s".($i-1),"",$description);

			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'type'=>'判断题'
			,'answer'=>$answer
			,'description'=>$description
			,'index' =>"10".( ($i>9)?$i:('0'.$i) )
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);

			$this->questions[$question['index']] = $question;
			$startIndex++;
		}
	}

	public function getChoiceQuestions(){
		$content = $this->paperHtmlContent;
		if(!is_array($this->up)){
			$p1 = strpos($content,$this->up);
		}else{
			for($i=0;$i<count($this->up);$i++){
				$p1 = strpos($content,$this->up[$i]);
				if($p1!=false)break;
			}
		}
		if(!is_array($this->down)){
			$p2 = strpos($content,$this->down);
		}else{
			for($i=0;$i<count($this->down);$i++){
				$p2 = strpos($content,$this->down[$i]);
				if($p2!=false)break;
			}
		}
		if($p1==false){
			$p1 = 0;
			$p2 = strlen($content);
		}
		$content = substr($content,$p1,$p2-$p1);

		$arr = explode("fs type=hidden",$content);
		$p1 = strpos($arr[0],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[0],"tan\" value=\"",$p1);
		$startPoint = substr($arr[0],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));

		$p1 = strpos($arr[count($arr)-2],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[count($arr)-2],"tan\" value=\"",$p1);
		$endPoint = substr($arr[count($arr)-2],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));

		$p1 = strpos($arr[0],"<td width='1%' valign='top'>");
		$p2 = strpos($arr[0],".<",$p1);
		$startIndex = substr($arr[0],$p1+strlen("<td width='1%' valign='top'>"),$p2-$p1-strlen("<td width='1%' valign='top'>"));

		//		echo $startIndex;exit();

		for($i=($startPoint+1);$i<=($endPoint+1);$i++){
			$p1 = strpos($content,">".($startIndex).".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
				
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A.");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$startIndex.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);
			$title = str_replace("<br/>&nbsp;&nbsp;<br/>&nbsp;&nbsp;","<br/>",$title);

			$p1 = strpos($data,"A.");
			$p2 = strpos($data,"B.");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A.","",$A));

			$p1 = strpos($data,"B.");
			$p2 = strpos($data,"C.");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B.","",$B));

			$p1 = strpos($data,"C.");
			$p2 = strpos($data,"D.");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C.","",$C));

			$p1 = strpos($data,"D.");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D.","",$D);
			$D = trim(str_replace(">".$i,"",$D));

			$p1 = strpos($data,"E.");
			if($p1!=false){
				$p2 = strpos($data,"<",$p1);
				$E = substr($data,$p1,$p2-$p1);
				$E = str_replace("E.","",$E);
				$E = trim(str_replace(">".$i,"",$E));
			}

			//			echo $data;exit();
			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			//			echo $answer;exit();

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
				
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);
			$description = str_replace("name=s".($i-1),"",$description);

			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$this->t->formatTitle($A)
			,'option2'=>$this->t->formatTitle($B)
			,'option3'=>$this->t->formatTitle($C)
			,'option4'=>$this->t->formatTitle($D)
			,'optionlength'=>4
			,'type'=>'单项选择题'
			,'answer'=>$answer
			,'description'=>$description
			,'index' =>"10".( ($i>9)?$i:('0'.$i) )
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);

			if(isset($E)){
				$question['option5'] = $E;
				$question['optionlength'] = 5;
			}
			//			print_r($question);exit();
			$this->questions[$question['index']] = $question;
			$startIndex++;
		}
	}

	public function getMultiChoiceQuestions(){
		$content = $this->paperHtmlContent;
		if(!is_array($this->up)){
			$p1 = strpos($content,$this->up);
		}else{
			for($i=0;$i<count($this->up);$i++){
				$p1 = strpos($content,$this->up[$i]);
				if($p1!=false)break;
			}
		}

		$p2 = strpos($content,$this->down);
		if($p2==false)$p2 = strlen($this->paperHtmlContent);

		$content = substr($content,$p1,$p2-$p1);

		$arr = explode("fs type=hidden",$content);

		$p1 = strpos($arr[0],"<input type=\"checkbox\" name=\"s");
		$p2 = strpos($arr[0],"tan\" value=\"",$p1);
		$startPoint = substr($arr[0],$p1+strlen("<input type=\"checkbox\" name=\"s"),$p2-$p1-strlen("<input type=\"checkbox\" name=\"s"));

		$p1 = strpos($arr[count($arr)-2],"<input type=\"checkbox\" name=\"s");
		$p2 = strpos($arr[count($arr)-2],"tan\" value=\"",$p1);
		$endPoint = substr($arr[count($arr)-2],$p1+strlen("<input type=\"checkbox\" name=\"s"),$p2-$p1-strlen("<input type=\"checkbox\" name=\"s"));

		$p1 = strpos($arr[0],"<td width='1%' valign='top'>");
		$p2 = strpos($arr[0],".<",$p1);
		$startIndex = substr($arr[0],$p1+strlen("<td width='1%' valign='top'>"),$p2-$p1-strlen("<td width='1%' valign='top'>"));


		for($i=($startPoint+1);$i<=($endPoint+1);$i++){
			$p1 = strpos($content,">".($startIndex).".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
				
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A.");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$startIndex.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);
			$title = str_replace("<br/>&nbsp;&nbsp;<br/>&nbsp;&nbsp;","<br/>",$title);

			$p1 = strpos($data,"A.");
			$p2 = strpos($data,"B.");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A.","",$A));

			$p1 = strpos($data,"B.");
			$p2 = strpos($data,"C.");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B.","",$B));

			$p1 = strpos($data,"C.");
			$p2 = strpos($data,"D.");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C.","",$C));

			$p1 = strpos($data,"D.");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D.","",$D);

			if(isset($E))unset($E);
			$p1 = strpos($data,"E.");
			if($p1!=false){
				$p2 = strpos($data,"<",$p1);
				$E = substr($data,$p1,$p2-$p1);
				$E = str_replace("E.","",$E);
			}

			//			echo $data;exit();
			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			$answer2 = '';
			for($i2=0;$i2<strlen($answer);$i2++){
				$answer2 .= substr($answer,$i2,1).",";
			}
			$answer2 = substr($answer2,0,strlen($answer2)-1);
			$answer = $answer2;

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
				
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);
			$description = str_replace("name=s".($i-1),"",$description);

			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$this->t->formatTitle($A)
			,'option2'=>$this->t->formatTitle($B)
			,'option3'=>$this->t->formatTitle($C)
			,'option4'=>$this->t->formatTitle($D)
			,'optionlength'=>4
			,'type'=>'多项选择题'
			,'answer'=>$answer
			,'description'=>$description
			,'index' =>"10".( ($i>9)?$i:('0'.$i) )
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);

			if(isset($E)){
				$question['option5'] = $E;
				$question['optionlength'] = 5;
			}
			//			print_r($question);exit();
			$this->questions[$question['index']] = $question;
				
			$startIndex++;
		}
	}

	public function getQuestions(){

		if(strpos($this->paperHtmlContent,"一、单项选择题")!=false){				
			$this->questionIndex = 10;
			$this->up = "单项选择题";
			$this->down = "多项选择题";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'单项选择题'
			,'type'=>'大题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();
				
			$this->questionIndex = 20;
			$this->up = "多项选择题";
			$this->down = "判断";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'多项选择题'
			,'type'=>'大题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getMultiChoiceQuestions();
				
			$this->questionIndex = 30;
			$this->up = "判断题";
			$this->down = 'ASFASDFASDFSADF';
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'判断题'
			,'type'=>'大题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getCheckQuestions();
			
			return;
		}

		$this->questionIndex = 30;
		$this->up = "判断题";
		$this->down = '单项选择题';
		$question = array(
				 'id_quiz'=>$this->id_quiz
		,'title'=>'判断题'
		,'type'=>'大题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getCheckQuestions();
			
		$this->questionIndex = 10;
		$this->up = "单项选择题";
		$this->down = "多项选择题";
		$question = array(
				 'id_quiz'=>$this->id_quiz
		,'title'=>'单项选择题'
		,'type'=>'大题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getChoiceQuestions();
			
		$this->questionIndex = 20;
		$this->up = "多项选择题";
		$this->down = "ASDFASDFSAFSFD";
		$question = array(
				 'id_quiz'=>$this->id_quiz
		,'title'=>'多项选择题'
		,'type'=>'大题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getMultiChoiceQuestions();
	}

	public function readFile(){
		$filename = $this->types[$this->id_level_subject]['path'].'/'.$_REQUEST['id'].'.xls';
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		if(file_exists($filename)){
			echo $filename;
//			return;
		}

		$path = $this->types[$this->id_level_subject]['path']."/".$_REQUEST['id'].".html";
		$path = mb_convert_encoding($path,'GBK','UTF-8');
		//		echo $path;exit();
		$content = file($path);
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("name=dtifs type=hidden","",$content);

		$content = str_replace("&nbsp;"," ",$content);
		$content = str_replace("title=放大","",$content);

		$content = str_replace("DISPLAY: none","",$content);
		//		$content = str_replace(".gif\">",".gif\" />",$content);
		$content = str_replace("A)","A.",$content);
		$content = str_replace("B)","B.",$content);
		$content = str_replace("C)","C.",$content);
		$content = str_replace("D)","D.",$content);
		$content = str_replace("E)","E.",$content);
		$content = str_replace("F)","F.",$content);

		$content = str_replace("A．","A.",$content);
		$content = str_replace("B．","B.",$content);
		$content = str_replace("C．","C.",$content);
		$content = str_replace("D．","D.",$content);	
		$content = str_replace("E．","E.",$content);
		$content = str_replace("F．","F.",$content);	
		$content = str_replace("<a id=\"donw\" href=\"","",$content);
		header("Content-type: text/html; charset=utf-8");
		//		echo $content;
		//		exit();
		$this->paperHtmlContent = $content;
		$this->getPaper();
		$this->getQuestions();
		//				print_r($this->questions);
		//				exit();
		$questionObj = new m_question();
		$this->questions = $questionObj->insertMany($this->questions);
		$this->questions = array_values($this->questions);
		$ids = '';
		for($i=0;$i<count($this->questions);$i++){
			$ids .= $this->questions[$i]['id'].',';
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$quizObj = new m_quiz();
		$quizObj->update(array(
			 'id'=>$this->id_quiz
		,'ids_questions'=>$ids
		));

		$paprObj = new m_quiz_paper();
		$paprObj->id_paper = $this->id_quiz_paper;

		$paprObj->exportOne($filename);
	}
	
	public function testLog(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();		
		$sql = "select answer,type from ".$pfx."wls_question where id_quiz = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		$data = array();
		$index = 1;
		$percent = ($_REQUEST['index']*1.5 + 40);
		while($temp = mysql_fetch_assoc($res)){
			
			if($temp['type']==1){
				if(rand(1,100)<$percent){
					$data[$index] = $temp['answer'];
				}else{
					$data[$index] = 'D';
				}
			}if($temp['type']==3){
				if(rand(1,100)<$percent){
					$data[$index] = $temp['answer'];
				}else{
					$data[$index] = 'A';
				}
			}if($temp['type']==2){
				if(rand(1,100)<$percent){
					$data[$index] = $temp['answer'];
				}else{
					$data[$index] = 'A,B';
				}
			}			
			$index ++;			
		}

		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['main']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['username']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->lang['time']);
		
		$sql = "select * from ".$pfx."wls_quiz where id = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		
		$objPHPExcel->getActiveSheet()->setCellValue('A3', $temp["title"]);
		$objPHPExcel->getActiveSheet()->setCellValue('B3', $_REQUEST["username"]);
		$objPHPExcel->getActiveSheet()->setCellValue('C3', $_REQUEST["date"]);
		
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['quizLog']);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['index']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['myAnswer']);
		
		$keys = array_keys($data);

		$index = 3;
		for($i=0;$i<count($keys);$i++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index,$keys[$i] );
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index,$data[$keys[$i]]);
			$index ++;
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);		
		$filename = $this->types[$this->id_level_subject]['path']."/quizlog/".$_REQUEST['username']."_".$_REQUEST['id']."_".$_REQUEST['date'].".xls";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		
		$objWriter->save($filename);
		
		include_once dirname(__FILE__)."/../../model/quiz/log.php";
		$logObj = new m_quiz_log();
		$logObj->importOne($filename);//TODO
	}
	
	public function testLogAll(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_user where username like '20%';";
		$res = mysql_query($sql,$conn);
		$users = '';
		while($temp = mysql_fetch_assoc($res)){
			$users .= "'".$temp['username']."',";
		}
		$users = substr($users,0,strlen($users)-1);
		
		$sql = "select * from ".$pfx."wls_quiz where id_level_subject = '".$this->id_level_subject."' limit 0,45 ";
		$res = mysql_query($sql,$conn);
		$ids = '';
		$dates = '';
		$index = 1;
		while($temp = mysql_fetch_assoc($res)){
			$ids .= $temp['id'].",";
			$dates .= "'".date('Y-m-d',strtotime( "-".($index*2)." day"))."',";
			$index ++;
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$dates = substr($dates,0,strlen($dates)-1);

		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var ids = [".$ids."];
var users = [".$users."];
var dates = [".$dates."];

var index_ids = 0;
var index_users = 0;

var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_yf2gwy2gajczs&action=testLog',
		data: {id:ids[index_ids],username:users[index_users],date:dates[index_ids],index:index_ids},
		success: function(msg){
			if(index_ids==ids.length ){
				index_ids = 0;
				index_users ++;
			}
			if(index_users==users.length ){
				return;
			}	
						
			down();			
			index_ids++;
		}
	});
}
down();
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
}
?>