<?php
include_once dirname(__FILE__).'/../yf.php';

class m_quiz_paper_yf_xznlcs extends m_quiz_paper_yf implements yfActions{

	public function getPaper(){
		$data = array(
			 'id_level_subject'=>'1001'
			,'name_subject'=>'行政能力测试'
			,'title'=>'行政能力测试卷'.rand(1,100)
			,'description'=>'行政能力测试卷'.rand(1,100)
			,'creator'=>'admin'
			,'date_created'=>date('Y-m-d H:i:s')
			,'questions'=>'0'
		);		
		
		$id = $this->insert($data);
		$data['id'] = $id;
		$this->paper = $data;
		$this->id = $id;
	}
	
	public function getQuestions(){
		$content = $this->paperHtmlContent;
		include_once dirname(__FILE__).'/../../../tools.php';
		$t = new tools();
		for($i=1;$i<=135;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i-1)."fs");
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
			$title = $t->formatTitle($title);

			$img = null;
			if(strpos($data,'放大')!=false){
				$p1 = strpos($data,"放大");
				$p2 = strpos($data,"anway");
				$img = substr($data,$p1,$p2-$p1);
				$img = str_replace("放大 src=\"","",$img);
				$img = str_replace("\"></table></table><a name='","",$img);
			}

			$p1 = strpos($data,"A．");
			$p2 = strpos($data,"B．");
			$A = substr($data,$p1,$p2-$p1);
			$A = str_replace("A．","",$A);

			$p1 = strpos($data,"B．");
			$p2 = strpos($data,"C．");
			$B = substr($data,$p1,$p2-$p1);
			$B = str_replace("B．","",$B);

			$p1 = strpos($data,"C．");
			$p2 = strpos($data,"D．");
			$C = substr($data,$p1,$p2-$p1);
			$C = str_replace("C．","",$C); 

			$p1 = strpos($data,"D．");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D．","",$D);
			$D = str_replace(">".$i,"",$D);

			$p1 = strpos($data,"name=s".($i-1)."an");
			$p2 = strpos($data,"name=s".($i-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);

			$data = array(
				 'id_level_subject'=>$this->paper['id_level_subject']
				,'name_subject'=>$this->paper['name_subject']
				,'id_quiz_paper'=>$this->paper['id']
				,'title_quiz_paper'=>$this->paper['title']
				,'title'=>$title
				,'cent'=>1
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
			);

			if($img!=null){
				$data['title']='<img src="file/images/'.$img.'" />';
			}
			if($i<=25){
//				$data['extype']='言语理解与表达';
				$data['ids_knowledge']='1001';
			}else if($i>=26 && $i<=35){				
//				$data['extype']='数学推理';
				$data['ids_knowledge']='100201';
			}else if($i>=36 && $i<=50){
//				$data['extype']='数学运算';
				$data['ids_knowledge']='100202';
			}else if($i>=51 && $i<=60){
//				$data['extype']='图形推理'; 
				$data['ids_knowledge']='100301';
			}else if($i>=61 && $i<=70){
//				$data['extype']='定义判断';
				$data['ids_knowledge']='100302';
			}else if($i>=71 && $i<=80){
//				$data['extype']='类比推理';
				$data['ids_knowledge']='100303';
			}else if($i>=81 && $i<=95){
//				$data['extype']='演绎推理';
				$data['ids_knowledge']='100304';
			}else if($i>=96 && $i<=115){
//				$data['extype']='常识判断';
				$data['ids_knowledge']='1005';
			}else if($i>=116 && $i<=135){
//				$data['extype']='资料分析';
				$data['ids_knowledge']='1004';
			}
			$data['weight_knowledge']='100';
			$this->questions[] = $data;
		}
	}
	public function savePathListForXunlei(){}
	public function readFile(){
		$path = $this->path;

		$content = file($path);
		$content = implode("\n", $content);
		//		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("DISPLAY: none","",$content);
		$content = str_replace("A. ","A) ",$content);
		$content = str_replace("B. ","B) ",$content);
		$content = str_replace("C. ","C) ",$content);
		$content = str_replace("D. ","D) ",$content);
		$content = str_replace("<a id=\"donw\" href=\"","",$content);

		$this->paperHtmlContent = $content;
	}
	public function import(){}

	public function getRead(){
		$content = $this->paperHtmlContent;		
		
		for($i=114;$i<130;$i+=5){
			$p1 = strpos($content,"s".$i."fs");
			$p2 = strpos($content,">".($i+2).".<");
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<table width='100%'","",$data);
			$data = str_replace("s".$i."fs type=hidden value=\"1\">","",$data);
			$data = str_replace("border='0' cellpadding='0' cellspacing='0'>","",$data);
			$data = str_replace("<tr><td width='1%' valign='top'","",$data);
			$data = str_replace("src=\"","src=\"file/images/",$data);
			$data = array(
				'id_quiz_type'=>$this->paper['id_quiz_type'],
				'title_quiz_type'=>$this->paper['title_quiz_type'],
				'id_quiz_paper'=>$this->paper['id'],
				'title_quiz_paper'=>$this->paper['title'],
				'title'=>$data,
				'cent'=>1,
				'type'=>5,
				'extype'=>'资料分析'
			);
			$this->questions[] = $data;
		}
	}
}
?>