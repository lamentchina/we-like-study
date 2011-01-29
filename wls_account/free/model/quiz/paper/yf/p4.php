<?php
include_once dirname(__FILE__).'/../yf.php';

class m_quiz_paper_yf_p4 extends m_quiz_paper_yf implements yfActions{	
	
	public $checkLength = 0;
	public $choiceLength = 20;
	public $multiChoiceLength = 30;

	public function getPaper(){
		$title = '会计_注册_公司战略与风险管理'.rand(1,100);
		if($this->yfnum!=null){
			$title = $this->yfnum.'会计_注册_公司战略与风险管理';
		}
		$data = array(
			 'id_level_subject'=>'1202'
			,'name_subject'=>'公司战略与风险管理'
			,'title'=>$title
			,'description'=>'公司战略与风险管理'.rand(1,100)
			,'creator'=>'admin'
			,'date_created'=>date('Y-m-d H:i:s')
			,'questions'=>'0'
			,'money'=>rand(0,5)			
		);		
		
		$id = $this->insert($data);
		$data['id'] = $id;
		$this->paper = $data;
		$this->id = $id;
	}
	
	
	public function getCheckQuestions(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"判断题");		
		$p2 = strpos($content,"计算题");		
		$content = substr($content,$p1,$p2-$p1);	
//		$checkPos = strpos($content,"s39tan");
//		if($checkPos!=false){
//			 $this->checkLength = 40;
//		}
		$len = $this->choiceLength + $this->multiChoiceLength;
		
		for($i=1;$i<=$this->checkLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i+$len-1)."fs");
			$data = substr($content,$p1,$p2-$p1);
			
			$p1 = strpos($data,"<a name='anway'>");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".</td><td width='99%'>","",$title);
			$title = str_replace("</table>","",$title);
			$title = str_replace("</td>","",$title);
			$title = str_replace("</tr>","",$title);
			$title = $this->t->formatTitle($title);
			
			$p1 = strpos($data,"name=s".($i+$len-1)."an");
			$p2 = strpos($data,"name=s".($i+$len-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$len-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			
			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			
			$this->questions[] = array(
				 'title'=>$title
				,'answer'=>$answer
				,'description'=>$description
				,'date_created'=>date('Y-m-d H:i:s')
				,'markingmethod'=>'自动批改'
				,'type'=>'判断题'
				,'belongto'=>0
				,'index' =>"1".$i	
				,'cent'=>1
				
				,'id_level_subject'=>$this->paper['id_level_subject']
				,'name_subject'=>$this->paper['name_subject']
				,'id_quiz_paper'=>$this->paper['id']
				,'title_quiz_paper'=>$this->paper['title']
				
				,'ids_level_knowledge'=>$this->knowledges[rand(0,24)]
			);
		}
	}
	
	public function getChoiceQuestions(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"单项选择题");
		$p2 = strpos($content,"多项选择题");		
		$content = substr($content,$p1,$p2-$p1);	
//		$checkPos = strpos($content,"s74tan");
//		if($checkPos!=false){
//			 $this->choiceLength = 25;
//		}		
		
		$len = 0;
		
		for($i=1;$i<=$this->choiceLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i+$len-1)."fs");
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A．");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);

			$p1 = strpos($data,"A．");
			$p2 = strpos($data,"B．");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A．","",$A));

			$p1 = strpos($data,"B．");
			$p2 = strpos($data,"C．");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B．","",$B));

			$p1 = strpos($data,"C．");
			$p2 = strpos($data,"D．");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C．","",$C)); 

			$p1 = strpos($data,"D．");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D．","",$D);
			$D = trim(str_replace(">".$i,"",$D));

			$p1 = strpos($data,"name=s".($i+$len-1)."an");
			$p2 = strpos($data,"name=s".($i+$len-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$len-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);

			$question = array(
				 'id_level_subject'=>$this->paper['id_level_subject']
				,'name_subject'=>$this->paper['name_subject']
				,'id_quiz_paper'=>$this->paper['id']
				,'title_quiz_paper'=>$this->paper['title']
				,'title'=>$title
				,'cent'=>2
				,'option1'=>$A
				,'option2'=>$B
				,'option3'=>$C
				,'option4'=>$D
				,'optionlength'=>4
				,'date_created'=>date('Y-m-d H:i:s')
				,'markingmethod'=>'自动批改'
				,'type'=>'单项选择题'
				,'answer'=>$answer
				,'description'=>$description
				
				,'belongto'=>0
				,'index' =>"2".$i
				
				,'ids_level_knowledge'=>$this->knowledges[rand(0,24)]
			);
			
			$this->questions[] = $question;
		}
	}
	
	public function getMultiChoiceQuestions(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"多项选择题");
		$p2 = strpos($content,"判断题");	
		$content = substr($content,$p1,$p2-$p1);	
//		$checkPos = strpos($content,"s104tan");
//		if($checkPos!=false){
//			 $this->multiChoiceLength = 30;
//		}		
		$len =  $this->choiceLength;
		
		for($i=1;$i<=$this->multiChoiceLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i+$len-1)."fs");
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A．");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);

			$p1 = strpos($data,"A．");
			$p2 = strpos($data,"B．");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A．","",$A));

			$p1 = strpos($data,"B．");
			$p2 = strpos($data,"C．");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B．","",$B));

			$p1 = strpos($data,"C．");
			$p2 = strpos($data,"D．");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C．","",$C)); 

			$p1 = strpos($data,"D．");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D．","",$D);
			$D = trim(str_replace(">".$i,"",$D));

			$p1 = strpos($data,"name=s".($i+$len-1)."an");
			$p2 = strpos($data,"name=s".($i+$len-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$len-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			$answerArr = array();
			for($i2=0;$i2<strlen($answer);$i2++){
				$answerArr[] = substr($answer,$i2,1);
			}
			$answer = implode(',',$answerArr);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);

			$question = array(
				 'id_level_subject'=>$this->paper['id_level_subject']
				,'name_subject'=>$this->paper['name_subject']
				,'id_quiz_paper'=>$this->paper['id']
				,'title_quiz_paper'=>$this->paper['title']
				,'title'=>$title
				,'cent'=>4
				,'option1'=>$A
				,'option2'=>$B
				,'option3'=>$C
				,'option4'=>$D
				,'optionlength'=>4
				,'date_created'=>date('Y-m-d H:i:s')
				,'markingmethod'=>'自动批改'
				,'type'=>'多项选择题'
				,'answer'=>$answer
				,'description'=>$description
				
				,'belongto'=>0
				,'index' =>"3".$i
				
				,'ids_level_knowledge'=>$this->knowledges[rand(0,24)]
			);
			
			$this->questions[] = $question;
		}
	}
	
	public function getQuestions(){

		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'一.单项选择题'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'组合题'
			,'belongto'=>0
			,'ids_level_knowledge'=>'0'
			,'index'=>999
			,'markingmethod'=>'自动批改'
			,'answer'=>'0'
			,'description'=>'0'				
		);
		$this->questions[] = $data;
		
		$this->getChoiceQuestions();
		
//		return;
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'二.多项选择题'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'组合题'
			,'belongto'=>0
			,'ids_level_knowledge'=>'0'
			,'index'=>998
			,'markingmethod'=>'自动批改'
			,'answer'=>'0'
			,'description'=>'0'				
		);
		$this->questions[] = $data;		
		$this->getMultiChoiceQuestions();
		
		return;
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'三.判断题'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'组合题'
			,'belongto'=>0
			,'ids_level_knowledge'=>'0'
			,'index'=>997
			,'markingmethod'=>'自动批改'
			,'answer'=>'0'
			,'description'=>'0'				
		);
		$this->questions[] = $data;		
		$this->getCheckQuestions();		
		
//		print_r($this->questions);
	}
	
	public function savePathListForXunlei(){}
	public function readFile(){
		$path = $this->path;
//		echo $path;exit();
		$content = file($path);
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("DISPLAY: none","",$content);
		$content = str_replace("A. ","A) ",$content);
		$content = str_replace("B. ","B) ",$content);
		$content = str_replace("C. ","C) ",$content);
		$content = str_replace("D. ","D) ",$content);
		$content = str_replace("<a id=\"donw\" href=\"","",$content);
		
		$this->paperHtmlContent = $content;
	}
	public function import(){}
}
?>