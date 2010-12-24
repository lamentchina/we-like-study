<?php
class install_cet4beta extends wls {	
	
	//数据读取的来源,路径
	public $path = '';
	
	public $id_paper = '';
	
	//试卷内容
	public $content = '';
	
	//试卷标题
	public $title = '';
	
	//1道写作题
	public $writing = array();
	
	//1篇快速阅读
	public $fastread = array();	
	
	//6道短文听力
	public $listencv = array();
	
	//1道听力填单词
	public $listencloze = array();	
	
	//2篇短文阅读
	public $read = array();

	//1篇深入阅读
	public $depthRead = array();
	
	//1道完形填空
	public $cloze = array();
	
	//翻译题
	public $translate = array();
	
	//题目数,编号集合
	public $questions = array();
	
	//子题目数,编号集
	public $subquestions = array();
	
		
	public function readTitle($path=null){
		if(isset($_REQUEST['path']) && $path==null)$path = $_REQUEST['path'];
		$content = file($path);  
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$pos1 = strpos($content,"<font style=\"font-size:24px\"><b>")+ strlen("<font style=\"font-size:24px\"><b>");
		$pos2 = strpos($content,"</b></font>");
		$title = substr($content,$pos1,$pos2-$pos1);
		$this->title = $title;
	}
	
	public function readContent($path=null){
		if(isset($_REQUEST['path']) && $path==null)$path = $_REQUEST['path'];
		$content = file($path);  
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("DISPLAY: none","",$content);
		$content = str_replace("A. ","A) ",$content);
		$content = str_replace("B. ","B) ",$content);
		$content = str_replace("C. ","C) ",$content);
		$content = str_replace("D. ","D) ",$content);
		$content = str_replace("<a id=\"donw\" href=\"","",$content);

		echo $content;
		
		$this->content = $content;	
//		echo $this->con?
//
	}
	
	public function read($method='local',$path=null,$id=null){
		if(isset($_REQUEST['method']))$method = $_REQUEST['method'];
		if(isset($_REQUEST['id']))$id = $_REQUEST['id'];
		if(isset($_REQUEST['path'])){
			$path = $_REQUEST['path'];
			$this->path = $path;
		}
		if($method=='local'){
			$this->readTitle($path."/examtitle(".$id.").jsp");
			$this->readContent($path."/examcontext(".$id.").jsp");
		}else{
			$this->readTitle("http://www.yfzxmn.cn/user/exam/examtitle.jsp?su_id=".$_REQUEST['suid']."&ex_id=".$id);
			$this->readContent("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=".$_REQUEST['suid']."&ex_id=".$id);
		}
		$this->getWriting();
		$this->getCloze();
		$this->getListencv();
		$this->getRead();
		$this->getListencloze();
		$this->getDepthRead();
		$this->getFastRead();
		$this->getTranslate();
		
		echo $this->title;
		echo "<br/>";
		echo $this->content;

		$this->save();
	}
	
	public function getWriting(){
		$pos1 = strpos($this->content,"Part Ⅰ Writing");
		$pos2 = strpos($this->content,"Part Ⅱ Reading Comprehension");
		$part1 = substr($this->content,$pos1,$pos2-$pos1);
		
		$p3 = strpos($part1,"<table");
		$p4 = strpos($part1,"</table>");

		$title = substr($part1,$p3,$p4-$p3+8);
		$title = str_replace("'","\"",$title);
		$this->writing['title'] = $title;

		$p5 = strpos($part1,"<TABLE");
		$p6 = strpos($part1,"</TABLE>");

		$description = substr($part1,$p5,$p6-$p5+8);
		$description = str_replace("'","\"",$description);
		$this->writing['description'] = $description;
	}
	
	public function getDepthRead(){
		$pos1 = strpos($this->content,">46.</td>");
		$pos2 = strpos($this->content,">47.</td>");
		$data = substr($this->content,$pos1,$pos2-$pos1);
		
		$p3 = strpos($data,"<input name=dtifs type=hidden value=\"249.0\">");
		$len = strlen("<input name=dtifs type=hidden value=\"249.0\">");
		$p4 = strpos($data,"<table width='100%'  border='0' cellpadding='0' cellspacing='0'>");
		$title = substr($data,$p3+$len,$p4-$p3-$len);
		$this->depthRead['title'] = $title;
		
		
		$p5 = strpos($this->content,">47.</td>");
		$p6 = strpos($this->content,">57.</td>");
		$data = substr($this->content,$p5,$p6-$p5);
		$arr = explode("amwser.gif",$data);
		for($i=1;$i<count($arr);$i++){
			$p7 = strpos($arr[$i],"</TD></TR></TBODY></TABLE><br>");
			$arr[$i] = substr($arr[$i],1,$p7-1);
		}
		$this->depthRead['questions'] = array();
		for($i=47;$i<=56;$i++){
			$this->depthRead['questions'][$i] = array(
				'desc'=>$arr[$i-46],
				'answer'=>substr($arr[$i-46],0,1),
				'cent'=>3.55
			);
		}
	}
	
	/**
	 * 只有一道阅读理解题
	 * */
	public function getCloze(){
		$pos1 = strpos($this->content,"Part Ⅴ");
		$pos2 = strpos($this->content,"Part Ⅵ");
		$data = substr($this->content,$pos1,$pos2-$pos1);

		$p1 = strpos($data,"through the centre");
		$p2 = strpos($data,"67.");

		$title = substr($data,$p1+26,$p2-$p1-26);
		$title = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","",$title);
		$title = str_replace("<tr><td width='1%' valign='top'>","",$title);
		$this->cloze['title'] = $title;
		
		$questions = substr($data,$p2);
		$questions = str_replace("C )","C)",$questions);	
		$this->cloze['questions'] = array();
		for($i=67;$i<86;$i++){			
			$p1 = strpos($questions,">".$i.".</td>");
			$p2 = strpos($questions,">".($i+1).".</td>");
			$ques = substr($questions,$p1,$p2-$p1);
			
			
			$p3 = strpos($ques,"A)");
			$p4 = strpos($ques,"B)");
			$a = substr($ques,$p3,$p4-$p3);
			$ptd = strpos($a,"</");
			$a = substr($a,0,$ptd);
			$a = str_replace("A)","",$a);
			$a = str_replace("&nbsp;","",$a);
			
			$p5 = strpos($ques,"B)");
			$p6 = strpos($ques,"C)");
			$b = substr($ques,$p5,$p6-$p5);
			$ptd = strpos($b,"</");
			$b = substr($b,0,$ptd);
			$b = str_replace("B)","",$b);
			$b = str_replace("&nbsp;","",$b);		

			$p7 = strpos($ques,"C)");
			$p8 = strpos($ques,"D)");
			$c = substr($ques,$p7,$p8-$p7);
			$ptd = strpos($c,"</");
			$c = substr($c,0,$ptd);
			$c = str_replace("C)","",$c);
			$c = str_replace("&nbsp;","",$c);		

			$p9 = strpos($ques,"D)");
			$p10 = strpos($ques,"<input");
			$d = substr($ques,$p9,$p10-$p9);
			$ptd = strpos($d,"</");
			$d = substr($d,0,$ptd);
			$d = str_replace("D)","",$d);
			$d = str_replace("&nbsp;","",$d);
			
			$p11 = strpos($ques,"amwser.gif>");
			$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
			$desc = substr($ques,$p11+16,$p12-$p11-16);
			$answer = substr($ques,$p11+11,1);
			
			$this->cloze['questions'][$i] = array(
				'a'=>$a,
				'b'=>$b,
				'c'=>$c,
				'd'=>$d,
				'desc'=>$desc,
				'answer'=>$answer,
				'cent'=>7.1,
			);
		}
		
		$p1 = strpos($questions,">86.</td>");
		$ques = substr($questions,$p1);
		
		$p3 = strpos($ques,"A)");
		$p4 = strpos($ques,"B)");
		$a = substr($ques,$p3,$p4-$p3);
		$ptd = strpos($a,"</");
		$a = substr($a,0,$ptd);
		$a = str_replace("A)","",$a);
		$a = str_replace("&nbsp;","",$a);
		
		$p5 = strpos($ques,"B)");
		$p6 = strpos($ques,"C)");
		$b = substr($ques,$p5,$p6-$p5);
		$ptd = strpos($b,"</");
		$b = substr($b,0,$ptd);
		$b = str_replace("B)","",$b);
		$b = str_replace("&nbsp;","",$b);		

		$p7 = strpos($ques,"C)");
		$p8 = strpos($ques,"D)");
		$c = substr($ques,$p7,$p8-$p7);
		$ptd = strpos($c,"</");
		$c = substr($c,0,$ptd);
		$c = str_replace("C)","",$c);
		$c = str_replace("&nbsp;","",$c);		

		$p9 = strpos($ques,"D)");
		$p10 = strpos($ques,"<input");
		$d = substr($ques,$p9,$p10-$p9);
		$ptd = strpos($d,"</");
		$d = substr($d,0,$ptd);
		$d = str_replace("D)","",$d);
		$d = str_replace("&nbsp;","",$d);
		
		$p11 = strpos($ques,"amwser.gif>");
		$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
		$desc = substr($ques,$p11+16,$p12-$p11-16);
		$answer = substr($ques,$p11+11,1);
		
		$this->cloze['questions'][86] = array(
			'a'=>$a,
			'b'=>$b,
			'c'=>$c,
			'd'=>$d,
			'desc'=>$desc,
			'answer'=>$answer,
			'cent'=>7.1,
		);
	}
	
	public function getListencv(){
		$pos1 = strpos($this->content,"Part Ⅲ");
		$pos2 = strpos($this->content,"Section C");
		$questions = substr($this->content,$pos1,$pos2-$pos1);

		$this->listencv['questions'] = array();		
		for($i=11;$i<=35;$i++){			
			$p1 = strpos($this->content,">".$i.".</td>");
			$p2 = strpos($this->content,">".($i+1).".</td>");
			$ques = substr($this->content,$p1,$p2-$p1);
			
			$pt1 = strpos($ques,"99%'>");
			$pt2 = strpos($ques,"<",$pt1+10);

			$p3 = strpos($ques,"A)");
			$p4 = strpos($ques,"B)");
			$a = substr($ques,$p3,$p4-$p3);
			$ptd = strpos($a,"</");
			$a = substr($a,0,$ptd);
			$a = str_replace("A)","",$a);
//			$a = str_replace("&nbsp;","",$a);
			
			$p5 = strpos($ques,"B)");
			$p6 = strpos($ques,"C)");
			$b = substr($ques,$p5,$p6-$p5);
			$ptd = strpos($b,"</");
			$b = substr($b,0,$ptd);
			$b = str_replace("B)","",$b);
//			$b = str_replace("&nbsp;","",$b);		

			$p7 = strpos($ques,"C)");
			$p8 = strpos($ques,"D)");
			$c = substr($ques,$p7,$p8-$p7);
			$ptd = strpos($c,"</");
			$c = substr($c,0,$ptd);
			$c = str_replace("C)","",$c);
//			$c = str_replace("&nbsp;","",$c);		

			$p9 = strpos($ques,"D)");
			$p10 = strpos($ques,"<input");
			$d = substr($ques,$p9,$p10-$p9);
			$ptd = strpos($d,"</");
			$d = substr($d,0,$ptd);
			$d = str_replace("D)","",$d);
//			$d = str_replace("&nbsp;","",$d);
			
			$p11 = strpos($ques,"amwser.gif>");
			$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
			$desc = substr($ques,$p11+11,$p12-$p11-11);
			$answer = substr($ques,$p11+11,1);
			
			$this->listencv['questions'][$i] = array(
				'a'=>trim($a),
				'b'=>trim($b),
				'c'=>trim($c),
				'd'=>trim($d),
				'desc'=>trim($desc),
				'answer'=>trim($answer),
				'cent'=>7.1
			);
		}
		
		$mp3length =strlen("/yy/0802/gg1161.1.mp3")-3;
		$arr = explode("mp3",$this->content);
		$mp3 = array();
		$fileName="E:\TDDOWNLOAD\mp3.lst";		
		$handle=fopen($fileName,"a");
		for($i=1;$i<count($arr);$i+=2){
			$temp = substr($arr[$i-1],strlen($arr[$i-1])-$mp3length+2,$mp3length-1).'mp3';
			$mp3[] = $temp;
			fwrite($handle,"http://www.yfzxmn.cn/".$temp."|\n\n");
		}
		fclose($handle);
		$this->listencv['mp3'] = $mp3;
	}
	
	
	public function getListencloze(){
		$pos1 = strpos($this->content,">35.</td>");
		$pos2 = strpos($this->content,">48.</td>");
		$data = substr($this->content,$pos1,$pos2-$pos1);

		$pos1 = strpos($data,"name=s35fs");
		$len = strlen("name=s35fs type=hidden value=\"7.0\">");
		$pos2 = strpos($data,"36.");
		$title = substr($data,$pos1+$len,$pos2-$pos1-$len);
		$pos1 = strpos($data,"2_5.gif");
		$pos2 = strpos($data,">36.</td>");
		$len = strlen("2_5.gif\" width=\"30\" height=\"30\" border=\"0\"></a>");
		$title = substr($data,$pos1+$len,$pos2-$pos1-$len);		
		$title = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'><tr><td width='1%' valign='top'","",$title);
		$this->listencloze['title'] = $title;
		
		for($i=36;$i<=46;$i++){			
			$p1 = strpos($data,">".$i.".</td>");
			$p2 = strpos($data,">".($i+1).".</td>");
			$ques = substr($data,$p1,$p2-$p1);

			$p11 = strpos($ques,"amwser.gif>");
			$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
			$desc = substr($ques,$p11+11,$p12-$p11-11);
			$p13 = strpos($desc,"<br>");
			$answer = substr($desc,0,$p13);
			
			$this->listencloze['questions'][$i] = array(
				'desc'=>$desc,
				'answer'=>$answer,
				'cent'=>3.55,
			);
			$listencloze['questions'][44]['cent'] = 14.2;
			$listencloze['questions'][45]['cent'] = 14.2;
			$listencloze['questions'][46]['cent'] = 14.2;
		}	
	}
	
	public function getRead(){
		$p1 = strpos($this->content,">56.</td>");
		$p2 = strpos($this->content,">57.</td>");
		$passage1 = substr($this->content,$p1,$p2-$p1);
		$p1 = strpos($passage1,"</TD></TR></TBODY></TABLE><br><input name=");
		$passage1 = substr($passage1,$p1+26);
		$passage1 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'><tr><td width='1%' valign='top'","",$passage1);
		
		$this->read['passages'][1] = array(
			'title'=>$passage1,
		);
		
		$p1 = strpos($this->content,">61.</td>");
		$p2 = strpos($this->content,">62.</td>");
		$passage1 = substr($this->content,$p1,$p2-$p1);
		$p1 = strpos($passage1,"</TD></TR></TBODY></TABLE><br><input name=");
		$passage1 = substr($passage1,$p1+26);
		$passage1 = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'><tr><td width='1%' valign='top'","",$passage1);
		
		$this->read['passages'][2] = array(
			'title'=>$passage1,
		);
				
		for($i=57;$i<=66;$i++){			
			$p1 = strpos($this->content,">".$i.".</td>");
			$p2 = strpos($this->content,">".($i+1).".</td>");
			$ques = substr($this->content,$p1,$p2-$p1);
			$ques = str_replace("\"","",$ques);
			
			$pt1 = strpos($ques,"99%'>");
			$pt2 = strpos($ques,"<",$pt1+10);
			
			$t = substr($ques,$pt1+5,$pt2-$pt1-5);
			$t = str_replace("'","\"",$t);
			$t = str_replace("/td>","",$t);
			$t = str_replace("<td width=\"99%\">","",$t);
			

			$p3 = strpos($ques,"A)");
			$p4 = strpos($ques,"B)");
			$a = substr($ques,$p3,$p4-$p3);
			$ptd = strpos($a,"</");
			$a = substr($a,0,$ptd);
			$a = str_replace("A)","",$a);
//			$a = str_replace("&nbsp;","",$a);
			
			$p5 = strpos($ques,"B)");
			$p6 = strpos($ques,"C)");
			$b = substr($ques,$p5,$p6-$p5);
			$ptd = strpos($b,"</");
			$b = substr($b,0,$ptd);
			$b = str_replace("B)","",$b);
//			$b = str_replace("&nbsp;","",$b);		

			$p7 = strpos($ques,"C)");
			$p8 = strpos($ques,"D)");
			$c = substr($ques,$p7,$p8-$p7);
			$ptd = strpos($c,"</");
			$c = substr($c,0,$ptd);
			$c = str_replace("C)","",$c);
//			$c = str_replace("&nbsp;","",$c);		

			$p9 = strpos($ques,"D)");
			$p10 = strpos($ques,"<input");
			$d = substr($ques,$p9,$p10-$p9);
			$ptd = strpos($d,"</");
			$d = substr($d,0,$ptd);
			$d = str_replace("D)","",$d);
//			$d = str_replace("&nbsp;","",$d);
			
			$p11 = strpos($ques,"amwser.gif>");
			$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
			$desc = substr($ques,$p11+11,$p12-$p11-11);
			$answer = substr($ques,$p11+11,1);
			
			$this->read['questions'][$i] = array(
				'a'=>trim($a),
				'b'=>trim($b),
				'c'=>trim($c),
				'd'=>trim($d),
				't'=>$t,
				'desc'=>trim($desc),
				'answer'=>trim($answer),
				'cent'=>14.2
			);
		}
	}
	
	public function getFastRead(){
		$pos1 = strpos($this->content,"Part Ⅱ");
		$pos2 = strpos($this->content,"Part Ⅲ");
		$data = substr($this->content,$pos1,$pos2-$pos1);

		$pos2 = strpos($data,"1.</td>");
		$title = substr($data,0,$pos2);
		$title = str_replace("'","\"",$title);
		$title = str_replace("<table width='100%'  border='0' cellpadding='0' cellspacing='0'>","\"",$title);
		$title = str_replace("<tr><td width='1%' valign='top'>","\"",$title);
		$this->fastread['title'] = $title;	
		
		for($i=1;$i<=9;$i++){			
			$p1 = strpos($data,">".$i.".</td>");
			$p2 = strpos($data,">".($i+1).".</td>");
			$ques = substr($data,$p1,$p2-$p1);

			$p3 = strpos($ques,"<td width='99%'>");
			$p4 = strpos($ques,"<a name='anway'>");
			
			$title = substr($ques,$p3,$p4-$p3);
			$title = str_replace("<td width='99%'>","",$title);
			$title = str_replace("</td></tr></table>","",$title);

			$p11 = strpos($ques,"amwser.gif>");
			$len = strlen('amwser.gif>');
			$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
			$desc = substr($ques,$p11+$len,$p12-$p11-$len);
			
			$p13 = strpos($desc,"<br>");
			$answer = substr($desc,0,$p13);
			$answer = str_replace(")","",$answer);
			$answer = str_replace("。","",$answer);
			
			$this->fastread['questions'][$i] = array(
				'desc'=>trim($desc),
				'answer'=>trim($answer),
				'title'=>trim($title),
				'cent'=>7.1,
			);
		}	
		$p1 = strpos($data,"10.</td>");
		$ques = substr($data,$p1);

		$p3 = strpos($ques,"<td width='99%'>");
		$p4 = strpos($ques,"<a name='anway'>");
		
		$title = substr($ques,$p3,$p4-$p3);
		$title = str_replace("<td width='99%'>","",$title);
		$title = str_replace("</td></tr></table>","",$title);

		$p11 = strpos($ques,"amwser.gif>");
		$len = strlen('amwser.gif>');
		$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
		$desc = substr($ques,$p11+$len,$p12-$p11-$len);
		
		$p13 = strpos($desc,"<br>");
		$answer = substr($desc,0,$p13);
		$answer = str_replace(")","",$answer);
		$answer = str_replace("。","",$answer);
		
		$this->fastread['questions'][10] = array(
			'desc'=>trim($desc),
			'answer'=>trim($answer),
			'title'=>trim($title),
			'cent'=>7.1,
		);
	}
	
	public function getTranslate(){
		$pos1 = strpos($this->content,"Part Ⅵ");
		$data = substr($this->content,$pos1);
		for($i=87;$i<=90;$i++){			
			$p1 = strpos($data,$i.".");
			$p2 = strpos($data,($i+1).".");
			$ques = substr($data,$p1,$p2-$p1);
			
			$p3 = strpos($ques,"<td width='99%'>");
			$p4 = strpos($ques,"<a name='anway'>");
			
			$title = substr($ques,$p3,$p4-$p3);
			$title = str_replace("<td width='99%'>","",$title);
			$title = str_replace("</td></tr></table>","",$title);
			
			$p11 = strpos($ques,"amwser.gif>");
			$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
			$desc = substr($ques,$p11+11,$p12-$p11-11);
			$p13 = strpos($desc,"<br>");
			$answer = substr($desc,0,$p13);
			
			$this->translate['questions'][$i] = array(
				'desc'=>trim($desc),
				'answer'=>trim($answer),
				'title'=>trim($title),
				'cent'=>7.1,
			);
		}
		$p1 = strpos($data,"91.");
		$ques = substr($data,$p1);
		
		$p3 = strpos($ques,"<td width='99%'>");
		$p4 = strpos($ques,"<a name='anway'>");
		
		$title = substr($ques,$p3,$p4-$p3);
		$title = str_replace("<td width='99%'>","",$title);
		$title = str_replace("</td></tr></table>","",$title);
		
		$p11 = strpos($ques,"amwser.gif>");
		$p12 = strpos($ques,"</TD></TR></TBODY></TABLE>");
		$desc = substr($ques,$p11+11,$p12-$p11-11);
		$p13 = strpos($desc,"<br>");
		$answer = substr($desc,0,$p13);
		
		$this->translate['questions'][91] = array(
			'desc'=>trim($desc),
			'answer'=>trim($answer),
			'title'=>trim($title),
			'cent'=>7.1,
		);		
	}
	
	public function save(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		$sql = "INSERT INTO ".$pfx."wls_quiz_paper (id_quiz_type,title,date_created,questions,rank,scores,price_money) VALUES 
		(1,'英语四级".rand(100,300)."','".date('Y-m-d')."','0',".rand(1,3).",
		'106,
		7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,
		7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,7.1,
		3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,
		14.2,14.2,14.2,
		3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,
		14.2,14.2,14.2,14.2,14.2,14.2,14.2,14.2,14.2,14.2,
		3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,3.55,
		7.1,7.1,7.1,7.1,7.1
		'
		,".rand(0,10)."
		)
		;";	
		mysql_query($sql,$conn);
		$this->id_paper = mysql_insert_id($conn);
		
		$this->saveWriting();
		$this->saveFastread();
		$this->saveListencv();
		$this->saveListencloze();
		$this->saveDepthRead();
		$this->saveRead();
		$this->saveCloze();
		$this->saveTranslate();
		
		$this->saveQuestionsId();
	}
	
	/**
	 * 写作
	 * */
	public function saveWriting(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		$sql = "insert into ".$pfx."wls_question
		(id_quiz_type,id_quiz_paper,title,description,type) values 
		(1,'".$this->id_paper."','".$this->writing['title']."','".$this->writing['description']."',10)";
		mysql_query($sql,$conn);	
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
	}
	
	public function saveQuestionsId(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$ids = '';
		for($i=0;$i<count($this->questions);$i++){
			$ids .= $this->questions[$i].",";
		}
		$ids = substr($ids,0,strlen($ids)-1);
		
		$sql = "update ".$pfx."wls_quiz_paper set questions = '".$ids."' where id = ".$this->id_paper;
		echo $sql;
		mysql_query($sql,$conn);	
	}
	
	/**
	 * 快速阅读,有主题和子题关系
	 * */
	public function saveFastread(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type) values (1,'".$this->id_paper."','".$this->fastread['title']."',11)";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
		for($i=1;$i<=10;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type) values (
			1,'".$this->id_paper."',".$id_parent.",'".$this->fastread['questions'][$i]['title']."',
			'".$this->fastread['questions'][$i]['desc']."',
			'".$this->fastread['questions'][$i]['answer']."'
			,11)";
			mysql_query($sql,$conn);
		}		
	}
	
	/**
	 * 6篇短文听力
	 * */
	public function saveListencv(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	

		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','
		<b>Part Ⅲ Listening Comprehension</b><br>
		<b>Section A</b><br/>
	   Directions: In this section, you will hear 8 short conversations and 2 long conversations. At the end of each conversation, one or more questions will be asked about what was said. Both the conversation and the questions will be spoken only once. After each question there will be a pause. During the pause, you must read the four choices marked A), B), C) and D), and decide which is the best answer. Then mark the corresponding letter on Answer Sheet 2 with a single line through the centre.<br/>
	   Questions 11 to 18 are based on the conversation you have just heard.<br/>
		',8,'{\"file\":\"".$this->listencv['mp3'][0]."\"}')";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
		for($i=11;$i<=18;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type,details) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->listencv['questions'][$i]['desc']."'
			,'".$this->listencv['questions'][$i]['answer']."'
			,8
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->listencv['questions'][$i]['a']."\"}
							,{\"option\":\"B\",\"title\":\"".$this->listencv['questions'][$i]['b']."\"}
							,{\"option\":\"C\",\"title\":\"".$this->listencv['questions'][$i]['c']."\"}
							,{\"option\":\"D\",\"title\":\"".$this->listencv['questions'][$i]['d']."\"}
							]}'
			)";
			mysql_query($sql,$conn);
		}
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','
		<b> Questions 19 to 22 are based on the conversation you have just heard.</b>
		',8,'{\"file\":\"".$this->listencv['mp3'][1]."\"}')";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		for($i=19;$i<=22;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type,details) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->listencv['questions'][$i]['desc']."'
			,'".$this->listencv['questions'][$i]['answer']."'
			,8
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->listencv['questions'][$i]['a']."\"}
							,{\"option\":\"B\",\"title\":\"".$this->listencv['questions'][$i]['b']."\"}
							,{\"option\":\"C\",\"title\":\"".$this->listencv['questions'][$i]['c']."\"}
							,{\"option\":\"D\",\"title\":\"".$this->listencv['questions'][$i]['d']."\"}
							]}'
			)";
			mysql_query($sql,$conn);
		}
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','
		<b> Questions 23 to 25 are based on the conversation you have just heard.</b>
		',8,'{\"file\":\"".$this->listencv['mp3'][2]."\"}')";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		for($i=23;$i<=25;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type,details) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->listencv['questions'][$i]['desc']."'
			,'".$this->listencv['questions'][$i]['answer']."'
			,8
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->listencv['questions'][$i]['a']."\"}
							,{\"option\":\"B\",\"title\":\"".$this->listencv['questions'][$i]['b']."\"}
							,{\"option\":\"C\",\"title\":\"".$this->listencv['questions'][$i]['c']."\"}
							,{\"option\":\"D\",\"title\":\"".$this->listencv['questions'][$i]['d']."\"}
							]}'
			)";
			mysql_query($sql,$conn);
		}		
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','
		<b>Section B</b>
<br/>Directions: In this section, you will hear 3 short passages. At the end of each passage, you will hear some questions. Both the passage and the questions will be spoken only once. After you hear a question, you must choose the best answer from the four choices marked A), B), C) and D). Then mark the corresponding letter on Answer Sheet 2 with a single line through the centre
  <br/> <b>Passage One</b>
   <br/>Questions 26 to 28 are based on the passage you have just heard.
		',8,'{\"file\":\"".$this->listencv['mp3'][3]."\"}')";
		mysql_query($sql,$conn);
		$id_parent4 = mysql_insert_id($conn);
		for($i=26;$i<=28;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type,details) values (
			1,'".$this->id_paper."',".$id_parent4.",' '
			,'".$this->listencv['questions'][$i]['desc']."'
			,'".$this->listencv['questions'][$i]['answer']."'
			,8
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->listencv['questions'][$i]['a']."\"}
							,{\"option\":\"B\",\"title\":\"".$this->listencv['questions'][$i]['b']."\"}
							,{\"option\":\"C\",\"title\":\"".$this->listencv['questions'][$i]['c']."\"}
							,{\"option\":\"D\",\"title\":\"".$this->listencv['questions'][$i]['d']."\"}
							]}'
			)";
			mysql_query($sql,$conn);
		}	
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','
		 <b>Passage Two</b><br/>
   Questions 29 to 31 are based on the passage you have just heard.
		',8,'{\"file\":\"".$this->listencv['mp3'][4]."\"}')";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		for($i=29;$i<=31;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type,details) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->listencv['questions'][$i]['desc']."'
			,'".$this->listencv['questions'][$i]['answer']."'
			,8
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->listencv['questions'][$i]['a']."\"}
							,{\"option\":\"B\",\"title\":\"".$this->listencv['questions'][$i]['b']."\"}
							,{\"option\":\"C\",\"title\":\"".$this->listencv['questions'][$i]['c']."\"}
							,{\"option\":\"D\",\"title\":\"".$this->listencv['questions'][$i]['d']."\"}
							]}'
			)";
			mysql_query($sql,$conn);
		}						
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','
		 <b>Passage Three</b><br/>
   Questions 32 to 35 are based on the passage you have just heard.
		',8,'{\"file\":\"".$this->listencv['mp3'][5]."\"}')";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		for($i=32;$i<=35;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type,details) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->listencv['questions'][$i]['desc']."'
			,'".$this->listencv['questions'][$i]['answer']."'
			,8
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->listencv['questions'][$i]['a']."\"}
							,{\"option\":\"B\",\"title\":\"".$this->listencv['questions'][$i]['b']."\"}
							,{\"option\":\"C\",\"title\":\"".$this->listencv['questions'][$i]['c']."\"}
							,{\"option\":\"D\",\"title\":\"".$this->listencv['questions'][$i]['d']."\"}
							]}'
			)";
			mysql_query($sql,$conn);
		}		
	}
	
	/**
	 * 听力听写
	 * */
	public function saveListencloze(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();			
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type,details) values (1,'".$this->id_paper."','".$this->listencloze['title']."',12,'{\"file\":\"".$this->listencv['mp3'][6]."\"}')";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
		for($i=36;$i<=46;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type) values (
			1,'".$this->id_paper."',".$id_parent.",' ',
			'".$this->listencloze['questions'][$i]['desc']."',
			'".$this->listencloze['questions'][$i]['answer']."'
			,12)";
			mysql_query($sql,$conn);
		}
	}
	
	/**
	 * 2篇阅读理解
	 * */
	public function saveRead(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type) values (1,'".$this->id_paper."','".$this->read['passages'][1]['title']."',5)";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
		for($i=57;$i<=61;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,details,answer,type) values (
			1,'".$this->id_paper."',".$id_parent.",'".$this->read['questions'][$i]['t']."'
			,'".$this->read['questions'][$i]['desc']."'
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->read['questions'][$i]['a']."\"}
				,{\"option\":\"B\",\"title\":\"".$this->read['questions'][$i]['b']."\"}
				,{\"option\":\"C\",\"title\":\"".$this->read['questions'][$i]['c']."\"}
				,{\"option\":\"D\",\"title\":\"".$this->read['questions'][$i]['d']."\"}
				]}'
			,'".$this->read['questions'][$i]['answer']."'
			,5)";
			echo $sql;
			mysql_query($sql,$conn);
		}
		
		$sql = "insert into ".$pfx."wls_question(id_quiz_type,id_quiz_paper,title,type) values (1,'".$this->id_paper."','".$this->read['passages'][2]['title']."',5)";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		for($i=62;$i<=66;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,details,answer,type) values (
			1,'".$this->id_paper."',".$id_parent.",'".$this->read['questions'][$i]['t']."'
			,'".$this->read['questions'][$i]['desc']."'
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->read['questions'][$i]['a']."\"}
				,{\"option\":\"B\",\"title\":\"".$this->read['questions'][$i]['b']."\"}
				,{\"option\":\"C\",\"title\":\"".$this->read['questions'][$i]['c']."\"}
				,{\"option\":\"D\",\"title\":\"".$this->read['questions'][$i]['d']."\"}
				]}'
			,'".$this->read['questions'][$i]['answer']."'
			,5)";
			mysql_query($sql,$conn);
		}		
	}
	
	/**
	 * 1篇完形填空
	 * */
	public function saveCloze(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		
		$sql = "insert into ".$pfx."wls_question
					(id_quiz_type,id_quiz_paper,title,type) values 
					(1,'".$this->id_paper."','".$this->cloze['title']."',6)";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
		
		for($i=67;$i<=86;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,details,answer,type) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->cloze['questions'][$i]['desc']."'
			,'{\"options\":[{\"option\":\"A\",\"title\":\"".$this->cloze['questions'][$i]['a']."\"}
				,{\"option\":\"B\",\"title\":\"".$this->cloze['questions'][$i]['b']."\"}
				,{\"option\":\"C\",\"title\":\"".$this->cloze['questions'][$i]['c']."\"}
				,{\"option\":\"D\",\"title\":\"".$this->cloze['questions'][$i]['d']."\"}
				]}'
			,'".$this->cloze['questions'][$i]['answer']."'
			,6)";
			mysql_query($sql,$conn);
			$id_subques = mysql_insert_id($conn);
			$this->subquestions[$i] = array(
				'id'=>$id_subques,
				'cent'=>$this->cloze['questions'][$i]['cent'],
			);
		}		
	}
	
	public function saveTranslate(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	

		for($i=87;$i<=91;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,title,description,answer,type) values (
			1,'".$this->id_paper."'
			,'".$this->translate['questions'][$i]['title']."'
			,'".$this->translate['questions'][$i]['desc']."'
			,'".$this->translate['questions'][$i]['answer']."'
			,13)";
			mysql_query($sql,$conn);
			$id_subques = mysql_insert_id($conn);
			$this->questions[] = $id_subques;
			$this->subquestions[$i] = array(
				'id'=>$id_subques,
				'cent'=>$this->translate['questions'][$i]['cent'],
			);			
		}

	}
	
	/**
	 * 1深入阅读
	 * */
	public function saveDepthRead(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		
		$sql = "insert into ".$pfx."wls_question
					(id_quiz_type,id_quiz_paper,title,type) values 
					(1,'".$this->id_paper."','".$this->depthRead['title']."',14)";
		mysql_query($sql,$conn);
		$id_parent = mysql_insert_id($conn);
		$this->questions[] = $id_parent;
		for($i=47;$i<=56;$i++){
			$sql = "insert into ".$pfx."wls_question
			(id_quiz_type,id_quiz_paper,id_parent,title,description,answer,type) values (
			1,'".$this->id_paper."',".$id_parent.",' '
			,'".$this->depthRead['questions'][$i]['desc']."'
			,'".$this->depthRead['questions'][$i]['answer']."'
			,14)";
			mysql_query($sql,$conn);
			$id_subques = mysql_insert_id($conn);
			$this->subquestions[$i] = array(
				'id'=>$id_subques,
				'cent'=>$this->depthRead['questions'][$i]['cent'],
			);	
		}		
	}
	
	public function saveCents(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();	
		//TODO
	}
	
	public function viewSaveProcess(){
		$html = "
		<html>
			<head>
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />	
	";
		$html .= $this->headerScripts();
		$html .="
				<script type=\"text/javascript\" >
					var w_q_p = new wls_quiz_paper('w_q_p');
					var ids = [30,31,32,34,35,36,37,38];
					w_q_p.saveByPrecess(ids,0);
				</script>	
			</head>
			<body>
			<div style='height:400px;width:400px;' id='quiz_main'></div>
			<body>
		</html>
		";
		echo $html;		
	}
	
	public function viewPaper($method='local',$path=null,$id=null){
		if(isset($_REQUEST['method']))$method = $_REQUEST['method'];
		if(isset($_REQUEST['id']))$id = $_REQUEST['id'];
		if(isset($_REQUEST['path'])){
			$path = $_REQUEST['path'];
			$this->path = $path;
		}
		if($path==null)$path = "E:/TDDOWNLOAD/again2_2500_3000";
		if($id==null)$id = 1;
		if($method=='local'){
			$this->readTitle($path."/examtitle(".$id.").jsp");
			$this->readContent($path."/examcontext(".$id.").jsp");
		}else{
			$this->readTitle("http://www.yfzxmn.cn/user/exam/examtitle.jsp?su_id=".$_REQUEST['suid']."&ex_id=".$id);
			$this->readContent("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=".$_REQUEST['suid']."&ex_id=".$id);
		}
		echo "<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
		
		</head><body>";
		echo "<div id='title'>".$this->title."</div>";
		echo $this->content;	
		echo "</body></html>";
	}
	
	/**
	 * 行政能力测试模拟卷
	 * */
	public function analysis(){
		$path = "E:/Projects/WEBS/PHP/Discuz_7_2/upload/plugins/wls/file/yf/国家公务员_公共基础知识.txt";
		$path = mb_convert_encoding($path,'GBK','UTF-8');
		$content = file($path);  
		$content = implode("\n", $content);
//		$content = str_replace("\n","", $content);
//		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$arr = explode("<img src=/icon/file.gif width=20 >",$content);
		$data = array();
		for($i=1;$i<count($arr);$i++){
			$p1 = strpos($arr[$i],"_blank");
			$p2 = strpos($arr[$i],"</a>");
			$title = substr($arr[$i],$p1,$p2-$p1);
			$title = str_replace("_blank>","", $title);
			
			$p1 = strpos($arr[$i],"ex_id=");
			$p2 = strpos($arr[$i],"&ef_id=");
			$ex_id = substr($arr[$i],$p1,$p2-$p1);
			$ex_id = str_replace("ex_id=","", $ex_id);
			$data[] = array(
				'title'=>$title,
				'ex_id'=>$ex_id,
			);
		}
		
		$fileName="E:/Projects/WEBS/PHP/Discuz_7_2/upload/plugins/wls/file/yf/国家公务员_公共基础知识.downlist";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');		
		$handle=fopen($fileName,"a");
		for($i=0;$i<count($data);$i++){
			fwrite($handle,"http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=5&ex_id=".$data[$i]['ex_id']."\n\n");
		}
		fclose($handle);
		
		
//		echo $content;
//		print_r($arr);
		print_r($data);
	}
}
?>