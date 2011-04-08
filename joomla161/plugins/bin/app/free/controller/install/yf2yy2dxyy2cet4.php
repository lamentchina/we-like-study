<?php
include_once dirname(__FILE__).'/../../model/question.php';
include_once dirname(__FILE__).'/../../model/quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';

class install_yf2yy2dxyy2cet4 extends wls {

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
	public $ids_level_knowledge = array('300250','300252','300253','300254','300251');

	public $id_level_subject = '3002';
	function install_yf2yy2dxyy2cet4(){

		parent::wls();
		$this->types = array(
			'3002'=> array(
				'su_id'=>2
				,'mainfolder'=>'f语言f'
				,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f大学英语f/f大学英语四级f" 
			),
		);
	}

	function html(){

//		$filename = $this->types[$this->id_level_subject]['path']."/ids.txt";
//		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
//		$content = file( $filename );
//		$content = implode("\n", $content);
		$content = "5150,5151,5152,5154,5158,5159,5433,5434,5435,5438,5439,5440,5441,5442,5443,5444,5445,5446,5447,5493,5494";
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
		url: 'wls.php?controller=install_yf2yy2dxyy2cet4&action=down',
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

	public function getSkimming(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strpos($content,$this->down,$p1);
		$content = substr($content,$p1,$p2-$p1);

		$p1 = strpos($content,"<td width=\"100%\" valign=\"top\">")+strlen("<td width=\"100%\" valign=\"top\">");
		$p2 = strpos($content,"</td>",$p1);
		$title1 = substr($content,$p1,$p2-$p1);

		$title1 = $this->t->formatTitle($title1);

		$question = array(
			'id_quiz'=>$this->id_quiz
			,'title'=>$title1
			,'type'=>$this->lang['Qes_Mixed']
		,'type2'=>'Skimming'
		,'index'=>"1000"
		);
		$this->questions[$question['index']] = $question;

		for($i=1;$i<=10;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
			$data = substr($content,$p1,$p2-$p1);
//						if($i==8){
//					echo $data;
//					exit();
//				}

			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);
				
			if($i==9 || $i==10 || $i==8){
				$p1 = strpos($data,"type=\"text\"");
				$title = substr($data,0,$p1);
//				if($i==8){
//					echo $data;
//					exit();
//				}
				$title = str_replace(">".$i.".</td><td width='99%'>","",$title);
				$title = str_replace(">".$i.".<td width='99%'>","",$title);
				$title = str_replace("<tr><td width=\"50%\">","",$title);
				$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
				$title = str_replace("<td width=\"100%\" valign=\"top\">","",$title);
				$title = str_replace("</table><a name='anway'><input name=\"s".$i."tan\" ","",$title);
				$title = str_replace("______","[___1___]",$title);
				$title = str_replace("</table><a name='anway'><input ","",$title);
//				
//				if($i==8){
//					echo $title;
//					exit();
//				}

				$p1 = strpos($data,"<IMG src=image/amwser.gif>");
				$p2 = strpos($data,"<br>",$p1);
				$answer = substr($data,$p1,$p2-$p1);
				$answer = str_replace("<IMG src=image/amwser.gif>","",$answer);
				$answer = trim($answer);
				$p1 = strpos($data,"<br>[知识点]");
				$description = substr($data,$p1);
				$description = str_replace("<br>[知识点]","",$description);
				$description = str_replace("</TD></TR></TBODY></TABLE><br><input name=","",$description);

				$question = array(
	 				'id_quiz'=>$this->id_quiz
				,'title'=>$this->t->formatTitle($title)
				,'cent'=>0
				,'type'=>$this->lang['Qes_Blank']
				,'index' =>"10".( ($i>9)?$i:('0'.$i) )
				);
				$this->questions[$question['index']] = $question;

				$question2 = array(
	 				'id_quiz'=>$this->id_quiz
					,'cent'=>2
					,'type'=>$this->lang['Qes_Blank']
					,'title'=>1
					,'belongto'=>$question['index']
					,'answer'=>$answer
					,'index' =>"10".( ($i>9)?$i:('0'.$i) )."01"
					,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
				);
				$this->questions[$question2['index']] = $question2;

				continue;
			}
				
			$p1 = strpos($data,"A.");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<tr><td width=\"50%\">","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
				

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

			//			echo $data;exit();
			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			//			echo $answer;exit();

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);

			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE>","",$description);
			$description = str_replace("<input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);


			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$A
			,'option2'=>$B
			,'option3'=>$C
			,'option4'=>$D
			,'optionlength'=>4
			,'type'=>$this->lang['Qes_Choice']
			,'answer'=>$answer
			,'description'=>$description
			,'index' =>"10".( ($i>9)?$i:('0'.$i) )
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);

			$this->questions[$question['index']] = $question;
		}
	}

	public function getListening(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strpos($content,$this->down,$p1);
		$content = substr($content,$p1,$p2-$p1);

		$mp3 = array();
		$arr = explode(".mp3",$content);
		for($i=0;$i<count($arr)-1;$i+=2){
			$p1 = strpos($arr[$i],"yy/");
			$p2 = strlen($arr[$i]);
			$path = "http://www.yfzxmn.cn/yy/".substr($arr[$i],$p1+strlen("yy/"),$p2-$p1-strlen("yy/")).".mp3";

			$mp3[] = $path;
		}

		for($i=11;$i<=35;$i++){
			$p1 = strpos($content,">".$i.".</td>");
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
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<tr><td width=\"50%\">","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = str_replace("<td width=\"100%\" valign=\"top\"></table></table> ","",$title);

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

			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);

			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE>","",$description);
			$description = str_replace("<input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);

			$belongto = 0;
			if($i==11||
			$i==12||
			$i==13||
			$i==14||
			$i==15||
			$i==16||
			$i==17||
			$i==18){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'type'=>$this->lang['Qes_Mixed']
				,'title'=>'Listening Comprehension'
				,'type2'=>'Listening'
				,'index' =>'20000'
				,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[0])
				);

				$this->questions[$question['index']] = $question;
				$belongto = 20000;
			}else if($i==19||
			$i==20||
			$i==21){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'type'=>$this->lang['Qes_Mixed']
				,'title'=>'Listening Comprehension'
				,'type2'=>'Listening'
				,'index' =>'20001'
				,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[1])
				);

				$this->questions[$question['index']] = $question;
				$belongto = 20001;
			}else if($i==22||
			$i==23||
			$i==24||
			$i==25){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'type'=>$this->lang['Qes_Mixed']
				,'title'=>'Listening Comprehension'
				,'type2'=>'Listening'
				,'index' =>'20002'
				,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[1])
				);

				$this->questions[$question['index']] = $question;
				$belongto = 20002;
			}else if($i==26||
			$i==27||
			$i==28){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'type'=>$this->lang['Qes_Mixed']
				,'title'=>'Listening Comprehension'
				,'type2'=>'Listening'
				,'index' =>'20003'
				,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[2])
				);

				$this->questions[$question['index']] = $question;
				$belongto = 20003;
			}else if($i==29||
			$i==30||
			$i==31||
			$i==32){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'type'=>$this->lang['Qes_Mixed']
				,'title'=>'Listening Comprehension'
				,'type2'=>'Listening'
				,'index' =>'20004'
				,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[3])
				);

				$this->questions[$question['index']] = $question;
				$belongto = 20004;
			}else if($i==33||
			$i==34||
			$i==35){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'type'=>$this->lang['Qes_Mixed']
				,'title'=>'Listening Comprehension'
				,'type2'=>'Listening'
				,'index' =>'20005'
				,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[4])
				);

				$this->questions[$question['index']] = $question;
				$belongto = 20005;
			}
				
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$A
			,'option2'=>$B
			,'option3'=>$C
			,'option4'=>$D
			,'optionlength'=>4
			,'type'=>$this->lang['Qes_Choice']
			,'answer'=>$answer
			,'belongto'=>$belongto
			,'description'=>$description
			,'index' =>"20".$i
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);

			$this->questions[$question['index']] = $question;
		}

		$filename = $this->types[$this->id_level_subject]['path']."/images_all.lst";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		$mp3s = '';
		for($i=0;$i<count($mp3);$i++){
			$mp3s .= $mp3[$i]." \n";
		}

		$handle=fopen($filename,"a");
		fwrite($handle,$mp3s);
		fclose($handle);

	}

	public function getListeningCloze(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strpos($content,$this->down,$p1);
		$content = substr($content,$p1,$p2-$p1);

		$mp3 = array();
		$arr = explode(".mp3",$content);
		for($i=0;$i<count($arr)-1;$i+=2){
			$p1 = strpos($arr[$i],"yy/");
			$p2 = strlen($arr[$i]);
			$path = "http://www.yfzxmn.cn/yy/".substr($arr[$i],$p1+strlen("yy/"),$p2-$p1-strlen("yy/")).".mp3";

			$mp3[] = $path;
		}

		$p1 = strpos($content,".mp3\">");
		$p2 = strpos($content,"36.",$p1);
		$title1 = substr($content,$p1+strlen(".mp3\">"),$p2-$p1-strlen(".mp3\">"));

		for($i=36;$i<=46;$i++){
			$title1 = str_replace("(".$i.")","[___".($i-35)."___]",$title1);
		}
		$title1 = str_replace("<u>","",$title1);
		$title1 = str_replace("</u>","",$title1);
		$title1 = str_replace("<td width=\"100%\" valign=\"top\">","",$title1);
		$title1 = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title1);
		$title1 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","",$title1);
		$title1 = str_replace("\"images/2_5.gif\" width=\"30\" height=\"30\" border=\"0\">","",$title1);
		$title1 = str_replace("</a>","",$title1);
		$title1 = str_replace("<a>","",$title1);
		$title1 = str_replace("</td>","",$title1);
		$title1 = str_replace("</tr>","",$title1);
		$title1 = str_replace("</table>","",$title1);
		$title1 = str_replace("</DIV>","",$title1);
		$title1 = str_replace("<td>","",$title1);
		$title1 = str_replace("<tr>","",$title1);
		$title1 = str_replace("<table>","",$title1);
		$title1 = str_replace("<DIV>","",$title1);
		$title1 = str_replace("<td width='1%' valign='top'>","",$title1);
		$title1 = str_replace("<img src=","",$title1);

//				echo $title1;exit();
		$title1 = $this->t->formatTitle($title1);

		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>$title1
		,'type'=>$this->lang['Qes_Blank']
		,'type2'=>'ListeningCloze'
		,'path_listen'=>$this->c->filePath."images/cet4/".basename($mp3[0])
		,'index'=>"3000"
		);
		$this->questions[$question['index']] = $question;

		for($i=36;$i<=46;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
				
			$data = substr($content,$p1,$p2-$p1);

			$p1 = strpos($data,"amwser.gif>");
			$p2 = strpos($data,"<br>",$p1);
			$answer = substr($data,$p1+strlen("amwser.gif>"),$p2-$p1-strlen("amwser.gif>"));

			$answer = str_replace("name=s".($i)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
				
			$question = array(
				 'id_quiz'=>$this->id_quiz
				,'cent'=>2
				,'type'=>$this->lang['Qes_Blank']
				,'title'=>$i-35
				,'belongto'=>3000
				,'answer'=>$answer
				,'index' =>"30".( ($i>9)?$i:('0'.$i) )."01"
				,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);
			$this->questions[$question['index']] = $question;
		}
	}

	public function getReadingInDepth(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strpos($content,$this->down,$p1);
		$content = substr($content,$p1,$p2-$p1);


		$p1 = strpos($content,"47 to 56 are based on the following passage");
		$p2 = strpos($content,"47.",$p1);
		$title1 = substr($content,$p1+strlen("47 to 56 are based on the following passage"),$p2-$p1-strlen("47 to 56 are based on the following passage"));

		for($i=47;$i<=56;$i++){
			$title1 = str_replace("".$i."","[___".($i-47)."___]",$title1);
		}
		$title1 = str_replace("<u>","",$title1);
		$title1 = str_replace("</u>","",$title1);
		$title1 = str_replace("<td width=\"100%\" valign=\"top\">","",$title1);
		$title1 = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title1);
		$title1 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","",$title1);
		$title1 = str_replace("\"images/2_5.gif\" width=\"30\" height=\"30\" border=\"0\">","",$title1);
		$title1 = str_replace("</a>","",$title1);
		$title1 = str_replace("<a>","",$title1);
		$title1 = str_replace("</td>","",$title1);
		$title1 = str_replace("</tr>","",$title1);
		$title1 = str_replace("</table>","",$title1);
		$title1 = str_replace("</DIV>","",$title1);
		$title1 = str_replace("<td>","",$title1);
		$title1 = str_replace("<tr>","",$title1);
		$title1 = str_replace("<table>","",$title1);
		$title1 = str_replace("<DIV>","",$title1);
		$title1 = str_replace("</b>","",$title1);
		$title1 = str_replace("<td width='1%' valign='top'>","",$title1);
		$title1 = str_replace("<b>Section A</b><br>","",$title1);
		$title1 = str_replace("Depth)<br>","",$title1);
		$title1 = str_replace("<b>Section A<br>","",$title1);
//		echo $title1;exit();
		$title1 = $this->t->formatTitle($title1);

		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>$title1
		,'type'=>$this->lang['Qes_Blank']
		,'type2'=>'ListeningCloze'
		,'index'=>"4000"
		);
		$this->questions[$question['index']] = $question;

		for($i=47;$i<=56;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
				
			$data = substr($content,$p1,$p2-$p1);

			$p1 = strpos($data,"amwser.gif>");
			$p2 = strpos($data,"<br>",$p1);
			$answer = substr($data,$p1+strlen("amwser.gif>"),$p2-$p1-strlen("amwser.gif>"));

			$answer = str_replace("name=s".($i)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
				
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'cent'=>2
			,'type'=>$this->lang['Qes_Blank']
			,'title'=>$i-47
			,'belongto'=>4000
			,'answer'=>$answer
			,'index' =>"40".( ($i>9)?$i:('0'.$i) )."01"
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);
			$this->questions[$question['index']] = $question;
		}
	}

	public function getReading(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strpos($content,$this->down,$p1);
		$content = substr($content,$p1,$p2-$p1);

		
		$p1 = strpos($content,"56.");		
		$p2 = strpos($content,"57.",$p1);
		$title1 = substr($content,$p1+strlen("56."),$p2-$p1-strlen("56."));
//		echo $title1;exit();
		$p1 = strpos($title1,"Passage One");		
		$p2 = strlen($title1);
		$title1 = substr($title1,$p1+strlen("Passage One"),$p2-$p1-strlen("Passage One"));
//		echo $title1;exit();
		$title1 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","",$title1);
		$title1 = str_replace("<tr><td width='1%' valign='top'>","",$title1);
		$title1 = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title1);
		$title1 = str_replace("<td width=\"100%\" valign=\"top\">","",$title1);
		$title1 = str_replace("</td>","",$title1);
		$title1 = str_replace("<tr>","",$title1);
		$title1 = str_replace("</tr>","",$title1);
		$title1 = str_replace("</div>","",$title1);
		$title1 = str_replace("<div>","",$title1);
		$title1 = str_replace("</table>","",$title1);
//		echo $title1;exit();
		
		
		$p1 = strpos($content,"61.");		
		$p2 = strpos($content,"62.",$p1);
		$title2 = substr($content,$p1+strlen("61."),$p2-$p1-strlen("61."));
		
		$p1 = strpos($title2,"Passage Two");
		$p2 = strlen($title2);
		$title2 = substr($title2,$p1+strlen("Passage Two"),$p2-$p1-strlen("Passage Two"));

		$title2 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","",$title2);
		$title2 = str_replace("<tr><td width='1%' valign='top'>","",$title2);
		$title2 = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title2);
		$title2 = str_replace("<td width=\"100%\" valign=\"top\">","",$title2);
		$title2 = str_replace("</td>","",$title2);
		$title2 = str_replace("<tr>","",$title2);
		$title2 = str_replace("</tr>","",$title2);
		$title2 = str_replace("</div>","",$title2);
		$title2 = str_replace("<div>","",$title2);
		$title2 = str_replace("</table>","",$title2);

		for($i=57;$i<=66;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
			$data = substr($content,$p1,$p2-$p1);

			$data = str_replace("<td width=\"50%\">","",$data);

			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);
				
			$p1 = strpos($data,"A.");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace(">".$i.".</td><td width='99%'>","",$title);

			$title = str_replace("<tr><td width=\"50%\">","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);

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

			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);

			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE>","",$description);
			$description = str_replace("<input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);

			$belongto = 0;
			if($i==57||
			$i==58||
			$i==59||
			$i==60||
			$i==61){
				$question = array(
					 'id_quiz'=>$this->id_quiz
					,'type'=>$this->lang['Qes_Mixed']
					,'title'=>$title1
					,'type2'=>'reading'
					,'index' =>'50000'
				);
				
				$this->questions[$question['index']] = $question;
//				print_r($this->questions);exit();
				$belongto = 50000;
			}else if($i==62||
			$i==63||
			$i==64||
			$i==65||
			$i==66){
				$question = array(
					 'id_quiz'=>$this->id_quiz
					,'type'=>$this->lang['Qes_Mixed']
					,'title'=>$title2
					,'type2'=>'reading'
					,'index' =>'50001'
				);

				$this->questions[$question['index']] = $question;
//				print_r($this->questions);exit();
				$belongto = 50001;
			}
				
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$A
			,'option2'=>$B
			,'option3'=>$C
			,'option4'=>$D
			,'optionlength'=>4
			,'type'=>$this->lang['Qes_Choice']
			,'answer'=>$answer
			,'belongto'=>$belongto
			,'description'=>$description
			,'index' =>"50".$i
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);
				
			$this->questions[$question['index']] = $question;
		}
//				print_r($this->questions);exit();
	}

	public function getCloze(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strpos($content,$this->down,$p1);
		$content = substr($content,$p1,$p2-$p1);


		$p1 = strpos($content,"through the centre");
		$p2 = strpos($content,"67.",$p1);
		$title1 = substr($content,$p1+strlen("through the centre"),$p2-$p1-strlen("through the centre"));
		$title1 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","",$title1);
		$title1 = str_replace("<tr><td width='1%' valign='top'>","",$title1);
		$title1 = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title1);
		$title1 = str_replace("<td width=\"100%\" valign=\"top\">","",$title1);
		$title1 = str_replace("</td>","",$title1);
		$title1 = str_replace("<tr>","",$title1);
		$title1 = str_replace("</tr>","",$title1);
		$title1 = str_replace("</div>","",$title1);
		$title1 = str_replace("<div>","",$title1);
		$title1 = str_replace("</table>","",$title1);

		$question = array(
			 'id_quiz'=>$this->id_quiz
		,'type'=>$this->lang['Qes_Mixed']
		,'title'=>$title1
		,'type2'=>'cloze'
		,'index' =>'60000'
		);

		$this->questions[$question['index']] = $question;

		for($i=67;$i<=86;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
			$data = substr($content,$p1,$p2-$p1);

			$data = str_replace("<td width=\"50%\">","",$data);

			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);
				
			$p1 = strpos($data,"A.");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace(">".$i.".</td><td width='99%'>","",$title);

			$title = str_replace("<tr><td width=\"50%\">","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);

			$p1 = strpos($data,"A.");
			$p2 = strpos($data,"B.");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A.","",$A));
			$A = trim(str_replace("</td>","",$A));

			$p1 = strpos($data,"B.");
			$p2 = strpos($data,"C.");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B.","",$B));
			$B = trim(str_replace("</td>","",$B));

			$p1 = strpos($data,"C.");
			$p2 = strpos($data,"D.");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C.","",$C));
			$C = trim(str_replace("</td>","",$C));

			$p1 = strpos($data,"D.");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D.","",$D);

			$p1 = strpos($data,"an type=hidden");
			$p2 = strpos($data,"\"><input",$p1);
			$answer = substr($data,$p1+strlen("an type=hidden value=\""),$p2-$p1-strlen("an type=hidden value=\""));

			$answer = str_replace("name=s".($i)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);

			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE>","",$description);
			$description = str_replace("<input","",$description);
			$description = str_replace("<br>","",$description);
			$description = str_replace("name=s".$i,"",$description);
				
			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$A
			,'option2'=>$B
			,'option3'=>$C
			,'option4'=>$D
			,'optionlength'=>4
			,'type'=>$this->lang['Qes_Choice']
			,'layout'=>0
			,'answer'=>$answer
			,'belongto'=>60000
			,'description'=>$description
			,'index' =>"60".$i
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);
				
			$this->questions[$question['index']] = $question;
		}
	}

	public function getTranslation(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$this->up);
		$p2 = strlen($this->paperHtmlContent);
		$content = substr($content,$p1,$p2-$p1);

		for($i=87;$i<=91;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"fs type=hidden",$p1);
			$data = substr($content,$p1,$p2-$p1);

			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);
				
			$p1 = strpos($data,"type=\"text\"");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<tr><td width=\"50%\">","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = str_replace("<td width=\"100%\" valign=\"top\">","",$title);
			$title = str_replace("</table><a name='anway'><input name=\"s".$i."tan\" ","",$title);
			for($i2=1;$i2<20;$i2++){
				$title = str_replace("__","_",$title);
			}
			
			$title = str_replace("_","[___1___]",$title);
			$title = str_replace("</table><a name='anway'><input","",$title);
			
//			echo $title;exit();
				
			$p1 = strpos($data,"<IMG src=image/amwser.gif>");
			$p2 = strpos($data,"<br>",$p1);
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("<IMG src=image/amwser.gif>","",$answer);
			$answer = trim($answer);
			$p1 = strpos($data,"<br>[知识点]");
			$description = substr($data,$p1);
			$description = str_replace("<br>[知识点]","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input name=","",$description);
				
			$question = array(
 				'id_quiz'=>$this->id_quiz
			,'title'=>$this->t->formatTitle($title)
			,'cent'=>0
			,'type'=>$this->lang['Qes_Blank']
			,'index' =>"70".( ($i>9)?$i:('0'.$i) )
			);
			$this->questions[$question['index']] = $question;

			$question2 = array(
 				'id_quiz'=>$this->id_quiz
			,'cent'=>2
			,'type'=>$this->lang['Qes_Blank']
			,'title'=>1
			,'belongto'=>$question['index']
			,'answer'=>$answer
			,'index' =>"70".( ($i>9)?$i:('0'.$i) )."01"
			,'ids_level_knowledge'=>$this->ids_level_knowledge[rand(0,count($this->ids_level_knowledge)-1 )]
			);
			$this->questions[$question2['index']] = $question2;
		}
	}

	public function getQuestions(){
		$this->questionIndex = 10;
		$this->up = "Part Ⅱ";
		$this->down = 'Part Ⅲ';
		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>'Skimming'
		,'type'=>$this->lang['Qes_Big']
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getSkimming();

		$this->questionIndex = 20;
		$this->up = "Part Ⅲ";
		$this->down = 'Section C';
		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>'Listening'
		,'type'=>$this->lang['Qes_Big']
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getListening();

		$this->questionIndex = 30;
		$this->up = "Section C";
		$this->down = 'Part Ⅳ';
		$this->getListeningCloze();

		$this->questionIndex = 40;
		$this->up = "Part Ⅳ";
		$this->down = 'Passage One';
		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>'Reading in Depth'
		,'type'=>$this->lang['Qes_Big']
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getReadingInDepth();

		$this->questionIndex = 50;
		$this->up = "Passage One";
		$this->down = 'Part Ⅴ';
		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>'Reading'
		,'type'=>$this->lang['Qes_Big']
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getReading();

		$this->questionIndex = 60;
		$this->up = "Part Ⅴ";
		$this->down = 'Part Ⅵ';
		$question = array(
			'id_quiz'=>$this->id_quiz
		,'title'=>'Cloze'
		,'type'=>$this->lang['Qes_Big']
		,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getCloze();

		$this->questionIndex = 70;
		$this->up = "Part Ⅵ";
		$question = array(
			'id_quiz'=>$this->id_quiz
			,'title'=>'Translation'
			,'type'=>$this->lang['Qes_Big']
			,'index'=>$this->questionIndex
		);
		$this->questions[$question['index']] = $question;
		$this->getTranslation();
	}

	public function readFile(){
		$filename = $this->types[$this->id_level_subject]['path'].'/'.$_REQUEST['id'].'.xls';
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		if(file_exists($filename)){
//			echo $filename;
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
		$content = str_replace("<textarea","<input type=\"text\"",$content);
		

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