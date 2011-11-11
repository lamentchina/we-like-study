<?php
include_once dirname(__FILE__).'/../../model/question.php';
include_once dirname(__FILE__).'/../../model/quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';

class install_yf2yx2ysks2lczyys extends wls {

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

	public $id_level_subject = '4003';
	function install_yf2yx2ysks2lczyys(){
		session_start();
		if(isset($_SESSION['id_level_subject'])){
			$this->id_level_subject = $_SESSION['id_level_subject'];
		}
		parent::wls();
		$this->types = array(
			'4003'=> array(
				'su_id'=>14
		,'mainfolder'=>'f医学Af'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f临床执业医师f" 
		),

		);
	}

	function html(){
		if(isset($_REQUEST['id_level_subject'])){
			session_start();
			$_SESSION['id_level_subject'] = $_REQUEST['id_level_subject'];
		}
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
		url: 'wls.php?controller=install_yf2yx2ysks2lczyys&action=down',
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

			//			$this->readFile();
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
		,'title'=>$temp['name'].'_'.rand(1,10000)
		,'author'=>'admin'
		,'imagePath'=>$this->c->filePath."images/papers/".$_REQUEST['id']."/"
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
	public $images = "";
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
			$p1 = 0;return;
			$p2 = strlen($content);
		}
		$content = substr($content,$p1,$p2-$p1);
		if($this->up=="A2题型")return;
		$arr = explode("fs type=hidden",$content);


		$p1 = strpos($arr[0],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[0],"tan\" value=\"",$p1);
		$startPoint = substr($arr[0],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));


		$p1 = strpos($arr[count($arr)-2],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[count($arr)-2],"tan\" value=\"",$p1);
		$endPoint = substr($arr[count($arr)-2],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));

		$this->choiceLength = count($arr)-1;

		$len = 0;


			
		$p1 = strpos($arr[0],"<td width='1%' valign='top'>");
		$p2 = strpos($arr[0],".<",$p1);
		$startIndex = substr($arr[0],$p1+strlen("<td width='1%' valign='top'>"),$p2-$p1-strlen("<td width='1%' valign='top'>"));



		for($i=($startPoint+1);$i<=($endPoint+1);$i++){
			$p1 = strpos($content,">".($startIndex-1+$i-$startPoint).".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
			//			if($p2==false){
			//				$p2 = strpos($content,"name=s".($i)."fs");
			//			}
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);
				


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
			$D = trim(str_replace(">".($i).".","",$D));


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

			$p1 = strpos($data,"A.");

			$title = substr($data,0,$p1);
			$title = str_replace(">".($startIndex-1+$i-$startPoint).".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = str_replace("<td width=\"100%\" valign=\"top\">","",$title);
			$title = $this->t->formatTitle($title);
			$title = str_replace("<br/>&nbsp;&nbsp;<br/>&nbsp;&nbsp;","<br/>",$title);
				
				
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$this->t->formatTitle($A)
			,'option2'=>$this->t->formatTitle($B)
			,'option3'=>$this->t->formatTitle($C)
			,'option4'=>$this->t->formatTitle($D)
			,'type2'=>$this->type2
			,'optionlength'=>4
			,'type'=>'单项选择题'
			,'answer'=>$answer
			,'description'=>$description
			,'index' =>$this->questionIndex.( ($i>9)?$i:('0'.$i) )
			);

			if(isset($E)){
				$question['option5'] = $E;
				$question['optionlength'] = 5;
			}
			//			print_r($question);exit();
			$this->questions[$question['index']] = $question;
			$this->len ++;
		}
	}

	function images2(){
		$folder = $this->c->filePath."images/papers/".$_REQUEST['id']."/";
		if (!file_exists($folder)){
			//createFolder($folder);
			mkdir($folder, 0777);//0777可以不写
		}

		$filename = $folder.basename($_REQUEST['path']);
		if(!file_exists($filename)){
			echo $filename;
			$content = file_get_contents($_REQUEST['path']);
			//			$content = implode("\n", $content);
			$handle=fopen($filename,"a");
			fwrite($handle,$content);
			fclose($handle);

			//			$this->readFile();
		}else{

		}
		echo $filename;
	}

	function images(){
		$filename = $this->types[$this->id_level_subject]['path']."/".$_REQUEST['id'].".lst";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');

		$content = file( $filename );
		$content = implode("\n", $content);
		$content = str_replace("\n","",$content);
		$arr = explode("http",$content);
		$arr2 = array();
		for($i=1;$i<count($arr);$i++){
			$arr2[] = "http".$arr[$i];
		}
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var ids = ".json_encode($arr2).";
var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_yf2yx2ysks2lczyys&action=images2',
		data: {path:ids[index],id:".$_REQUEST['id']."},
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

	public $type2 = "";
	public function getQuestions(){
		if(strpos($this->paperHtmlContent,"A1型题")!=false){
			$this->questionIndex = 10;
			$this->up = "A1型题";
			$this->down = "A2型题";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'A1型题'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->type2 = "A1型题";
			$this->getChoiceQuestions();
		}
		
		if(strpos($this->paperHtmlContent,"A2型题")!=false){
			$this->questionIndex = 20;
			$this->up = "A2型题";
			$this->down = "A3型题";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'A2型题'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->type2 = "A2型题";
			$this->getChoiceQuestions();
		}
		
		if(strpos($this->paperHtmlContent,"A3型题")!=false){	
			$this->questionIndex = 30;
			$this->up = "A3型题";
			$this->down = "A4型题";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'A3型题'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->type2 = "A3型题";
			$this->getChoiceQuestions();
		}


		if($this->images=="")return;
		$filename = $this->types[$this->id_level_subject]['path']."/".$_REQUEST['id'].".lst";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		if(file_exists($filename)){

		}else{
			$handle=fopen($filename,"a");
			fwrite($handle,$this->images);
			fclose($handle);
		}
	}

	public function readFile(){
		$filename = $this->types[$this->id_level_subject]['path'].'/'.$_REQUEST['id'].'.xls';
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		if(file_exists($filename)){
			echo $filename;
			return;
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

		$p1 = strpos($content,"B1型题");
		if($p1!=false){
			die('NotGood');
		}

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
}
?>