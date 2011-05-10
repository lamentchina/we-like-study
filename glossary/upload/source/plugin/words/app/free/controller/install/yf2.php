<?php
include_once dirname(__FILE__).'/../../model/question.php';
include_once dirname(__FILE__).'/../../model/quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';

class install_yf2 extends wls {

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

	public $id_level_subject = '500101';
	function install_yf2(){
		session_start();
		if(isset($_SESSION['id_level_subject'])){
			$this->id_level_subject = $_SESSION['id_level_subject'];
		}
		parent::wls();
		$this->types = array(
			'500101'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f行政职业能力测试模拟题f" 
		),
			'500102'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f公共基础知识模拟题f" 
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
		url: 'wls.php?controller=install_yf2&action=down',
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
			$p1 = 0;
			$p2 = strlen($content);
		}
		$content = substr($content,$p1,$p2-$p1);

		$arr = explode("fs type=hidden",$content);
		$p1 = strpos($arr[0],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[0],"tan\" value=\"",$p1);
		$startPoint = substr($arr[0],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));
		

//		echo $arr[count($arr)-2];exit();
		$p1 = strpos($arr[count($arr)-2],"<input type=\"radio\" name=\"s");
		$p2 = strpos($arr[count($arr)-2],"tan\" value=\"",$p1);
		$endPoint = substr($arr[count($arr)-2],$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));
//		echo $endPoint;exit();	
		
		$this->choiceLength = count($arr)-1;
		//		echo $this->choiceLength;
		//		echo '<br/>';
		$len = $this->len;
		//		echo $len;
		//		echo '<br/>';
		for($i=($startPoint+1);$i<=($endPoint+1);$i++){
			$p1 = strpos($content,">".($i).".</td>");
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
			
//			if($i==12){
//				echo $data;exit();
//			}

			$p1 = strpos($data,"A.");
			$p2 = strpos($data,"B.");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A.","",$A));
			$p1 = strpos($A,"IMG");
			if($p1!=false){
				$p1 = strpos($A,"<IMG onClick=over(this)  src=\"");
				$p2 = strpos($A,"\">",$p1);
				$title2 = substr($A,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
				$title3 = basename($title2);
				$A = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
				$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
			}


			$p1 = strpos($data,"B.");
			$p2 = strpos($data,"C.");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B.","",$B));
			$p1 = strpos($B,"IMG");
			if($p1!=false){
				$p1 = strpos($B,"<IMG onClick=over(this)  src=\"");
				$p2 = strpos($B,"\">",$p1);
				$title2 = substr($B,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
				$title3 = basename($title2);
				$B = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
				$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
			}

			$p1 = strpos($data,"C.");
			$p2 = strpos($data,"D.");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C.","",$C));
			$p1 = strpos($C,"IMG");
			if($p1!=false){
				$p1 = strpos($C,"<IMG onClick=over(this)  src=\"");
				$p2 = strpos($C,"\">",$p1);
				$title2 = substr($C,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
				$title3 = basename($title2);
				$C = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
				$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
			}

			$p1 = strpos($data,"D.");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D.","",$D);
			$D = trim(str_replace(">".($i).".","",$D));
			$p1 = strpos($D,"IMG");
			if($p1!=false){
				$p1 = strpos($D,"<IMG onClick=over(this)  src=\"");
				$p2 = strpos($D,"\">",$p1);
				$title2 = substr($D,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
				$title3 = basename($title2);
				$D = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
				$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
			}

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


			if(strpos($description,"<IMG onClick")!=false){
				$description = str_replace(".gif\">",".gif\" />",$description);
				
				$p1 = strpos($description,"<IMG onClick=over(this)  src=\"");
				$p2 = strpos($description,"\" />",$p1);
				$title2 = substr($description,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
				$title3 = basename($title2);
				$description = str_replace("<IMG onClick=over(this)  src=\"tp/gwy/","<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/",$description);
				$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
			}


			$p1 = strpos($data,"A.");
			$p2 = strpos($data,"B.");
			$p3 = strpos($data,"C.");
			$p4 = strpos($data,"D.");

			if($p1!=false && $p2!=false && $p3!=false && $p4!=false ){//有 ABCD这样的选项
				$title = substr($data,0,$p1);
				$title = str_replace(">".($i).".<td width='99%'>","",$title);
				$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
				$title = str_replace("<td width=\"100%\" valign=\"top\">","",$title);
				$title = $this->t->formatTitle($title);
				$title = str_replace("<br/>&nbsp;&nbsp;<br/>&nbsp;&nbsp;","<br/>",$title);

				$p1 = strpos($title,"IMG");
				if($p1!=false){
					$p1 = strpos($title,"<IMG onClick=over(this)  src=\"");
					$p2 = strpos($title,"\">",$p1);
					$title2 = substr($title,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
					$title3 = basename($title2);
					$title = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
					$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
				}
			}else{

				if(strpos($data,"<IMG onClick")!=false){
					//echo $data;exit();
					$p1 = strpos($data,"<IMG onClick=over(this)  src=\"");
					$p2 = strpos($data,"\">",$p1);
					$title2 = substr($data,$p1+strlen("<IMG onClick=over(this)  src=\""),$p2-$p1-strlen("<IMG onClick=over(this)  src=\""));
					$title3 = basename($title2);

					$title = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
					$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
				}
				$image_count = explode("<IMG onClick",$data);
				if(count($image_count)==6){
					$arr_images = array();
					for($i2=1;$i2<=5;$i2++){
						$p1 = strpos($image_count[$i2],"=over(this)  src=\"");
						$p2 = strpos($image_count[$i2],"\">",$p1);
						$title2 = substr($image_count[$i2],$p1+strlen("=over(this)  src=\""),$p2-$p1-strlen("=over(this)  src=\""));
						$title3 = basename($title2);

						$arr_images[] = "<img src=\"".$this->c->filePath."images/papers/".$_REQUEST["id"]."/".$title3."\" />";
						$this->images .= "http://www.yfzxmn.cn/".$title2."\n";
					}

					$title = $arr_images[0];
					$A = $arr_images[1];
					$B = $arr_images[2];
					$C = $arr_images[3];
					$D = $arr_images[4];
				}
			}

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
		url: 'wls.php?controller=install_yf2&action=images2',
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

	public function getQuestions(){

		$this->questionIndex = 10;
		$this->up = "言语理解与表达";
		$this->down = array("数学推理","数字推理");
		$question = array(
			 'id_quiz'=>$this->id_quiz
		,'title'=>'言语理解与表达'
		,'type'=>'组合题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getChoiceQuestions();

		$this->questionIndex = 20;
		$this->up = array("数学推理","数字推理");
		$this->down = "数学运算";
		$question = array(
			 'id_quiz'=>$this->id_quiz
		,'title'=>'数学推理'
		,'type'=>'组合题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getChoiceQuestions();

		$this->questionIndex = 30;
		$this->up = "数学运算";
		$this->down = "图形推理";
		$question = array(
			 'id_quiz'=>$this->id_quiz
		,'title'=>'数学运算'
		,'type'=>'组合题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getChoiceQuestions();

		$this->questionIndex = 40;
		$this->up = "图形推理";
		$this->down = "定义判断";
		$question = array(
			 'id_quiz'=>$this->id_quiz
		,'title'=>'图形推理'
		,'type'=>'组合题'
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getChoiceQuestions();

		if(strpos($this->paperHtmlContent,"事件排序")!=false){
			$this->questionIndex = 50;
			$this->up = "定义判断";
			$this->down = "演绎推理";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'定义判断'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();

			$this->questionIndex = 60;
			$this->up = "演绎推理";
			$this->down = "事件排序";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'演绎推理'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();

			$this->questionIndex = 70;
			$this->up = "事件排序";
			$this->down = "常识判断";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'事件排序'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();

		}else{

			$this->questionIndex = 50;
			$this->up = "定义判断";
			$this->down = "类比推理";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'定义判断'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();

			$this->questionIndex = 60;
			$this->up = "类比推理";
			$this->down = "演绎推理";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'类比推理'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();

			$this->questionIndex = 70;
			$this->up = "演绎推理";
			$this->down = "常识判断";
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>'演绎推理'
			,'type'=>'组合题'
			,'index'=>$this->questionIndex
			);
			$this->questions[$question['index']] = $question;
			$this->getChoiceQuestions();


		}
		$this->questionIndex = 80;
		$this->up = "常识判断";
		$this->down = "资料分析";
		$question = array(
				 'id_quiz'=>$this->id_quiz
		,'title'=>'常识判断'
		,'type'=>'组合题'
		,'index'=>$this->questionIndex
		);

		$this->questions[$question['index']] = $question;
		$this->getChoiceQuestions();

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
//		if(file_exists($filename)){
//			echo $filename;
//			return;
//		}

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
}
?>