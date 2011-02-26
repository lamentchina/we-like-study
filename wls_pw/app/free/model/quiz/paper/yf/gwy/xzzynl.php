<?php
include_once dirname(__FILE__).'/../../yf.php';

class m_quiz_paper_yf_gwy_xzzynl extends m_quiz_paper_yf implements yfActions{	
	
	public $start = null;
	public $end = null;

	public function getPaper(){
		$title = '行政能力测试'.rand(1,100);
		if($this->yfnum!=null){
			$title = $this->yfnum.'行政能力测试';
		}
		$data = array(
			 'id_level_subject'=>'1001'
			,'name_subject'=>'行政能力测试'
			,'title'=>$title
			,'description'=>'行政能力测试卷'.rand(1,100)
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
	
	public function getPartQuestions($part1,$part2=null,$index=10,$belongto=0){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,$part1);
		if($part2==null){
			$p2 = strlen($content);
		}else{
			$p2 = strpos($content,$part2);
		}		
		$content = substr($content,$p1,$p2-$p1);	
//		echo $content;exit();		
		
		$p1 = strpos($content,"<input type=\"radio\" name=\"s");
		$p2 = strpos($content,"tan\" value=\"");	
		$start = substr($content,$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));
//		echo $start;exit();	
		$this->start = $start;

		$p1 = strrpos($content,"<input type=\"radio\" name=\"s");
		$p2 = strrpos($content,"tan\" value=\"");	
		$end = substr($content,$p1+strlen("<input type=\"radio\" name=\"s"),$p2-$p1-strlen("<input type=\"radio\" name=\"s"));
//		echo $end;exit();	
		$this->end = $end;

		
		$len = $end - $start;
//		echo $len;exit();
		
		for($i=1;$i<=$len+1;$i++){
			$p1 = strpos($content,">".($i+$start).".</td>");
			$p2 = strpos($content,"name=s".($i+$start-1)."fs");
			$data = substr($content,$p1,$p2-$p1);
//			echo $data;exit();
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			if(strpos($data,"title=放大")==false){
				$p1 = strpos($data,"A．");
				$title = substr($data,0,$p1);
				$title = str_replace(">".($i+$start).".<td width='99%'>","",$title);
				$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
				$title = $this->t->formatTitle($title);
			}else{
				$p1 = strpos($data,"title=放大 src=\"")+strlen("title=放大 src=\"");
//				echo $p1;exit();
				$p2 = strpos($data,"\"",$p1);
				$title = substr($data,$p1,$p2-$p1);
				
				$p = strrpos($this->path,"/");
				$filename = substr($this->path,0,$p)."/images.downlist";
//				echo $filename;exit();
				if(!file_exists($filename)){
					$handle=fopen($filename,"a");
					fwrite($handle,"");
					fclose($handle);
				}else{
					$handle=fopen($filename,"a");
					fwrite($handle,"http://www.yfzxmn.cn/".$title."\n");
					fclose($handle);
				}				
				
				$title = "<img src=\"__IMAGEPATH__".$title."\">"; 
//				echo $title;exit();
				$description = "无";		
				

//				if(file_exists())
			}

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

			$p1 = strpos($data,"name=s".($i+$start-1)."an");
			$p2 = strpos($data,"name=s".($i+$start-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$start-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);

			if(!isset($description)){
				$p1 = strpos($data,"amwser.gif");
				$description = substr($data,$p1);
	//			echo $description;exit();
				$description = str_replace("amwser.gif>","",$description);
				$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
				$description = str_replace("<br>","",$description);
	//			echo $description;exit();
			}

			$temp = ($i>9)?$i:"0".$i;
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
				
				,'belongto'=>$belongto
				,'index' =>$index.$temp
				
				,'ids_level_knowledge'=>$this->knowledges[rand(0,24)]
			);
			
			$this->questions[$question['index']] = $question;
		}
//		print_r($this->questions);exit();
	}
	
	public function getQuestions(){
		$this->paperHtmlContent = str_replace("数字推理","数学推理",$this->paperHtmlContent);
		
		$data = array(
			 'title'=>'<b>第一部分 言语理解与表达<b/>
<br/>每道题包含一段文字(或一个句子),后面是一个不完整的陈述,要求你从四个选项中选出一个来完成陈述.
<br/>注意,答案可能是完成对所给文字主要意思的提要,也可能是满足陈述中其他方面的要求,你的选择应与所提要求最相符合.'
			,'index'=>1000
			
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''		
			,'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']					
		);
		$this->questions[1000] = $data;
		$this->getPartQuestions("言语理解与表达","数学推理","10");
		
		$data2 = array(
			 'title'=>'<b/>第二部分 数量关系</b>
<br/>本部分包括两种类型的题目:
<br/><b>一.数学推理</b>
<br/>给你一个数列,但其中缺少一项,要求你仔细观察数列的排列规律,然后从四个供选择的选项中选择你认为最合理的一项,来填补空缺项,使之符合原数列的排列规律.'			
			,'index'=>2000
			
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''		
			,'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']				
		);
		$this->questions[2000] = $data2;
		$this->getPartQuestions("数学推理","数学运算","20");
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'<b>二.数学运算</b>
<br/>你可以在草稿纸上运算,遇到难题,可以跳过暂时不做,待你有时间再返回解决它.'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>3000
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[3000] = $data;
		$this->getPartQuestions("数学运算","图形推理","30");	

		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'<b>第三部分 判断推理</b>
<br/>本部分包括四种类型的题目.
<br/><b>一.图形推理</b>
<br/>本部分包括三种类型的题目,共10题.'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>4000
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[4000] = $data;
		$this->getPartQuestions("图形推理","定义判断","40");	

		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'<b>二.定义判断</b>
<br/>每道题先给出一个概念的定义,然后分别列出四种情况,要求你严格依据定义从中选出一个最符合或最不符合该定义的答案.注意:假设这个定义是正确的,不容置疑的.'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>5000
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[5000] = $data;
		$this->getPartQuestions("定义判断","类比推理","50");	
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'<b>三.类比推理</b>
<br/>每道题给出一对相关的词,然后要求考生在备选答案中找出一对与之在逻辑关系上最为贴近或相似的词.
'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>6000
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[6000] = $data;
		$this->getPartQuestions("类比推理","演绎推理","60");	
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'<b>四.演绎推理</b>
<br/>每题给出一段陈述,这段陈述被假设是正确的,不容置疑的.要求你根据这段陈述,选择一个答案.
<br/>注意,正确的答案应与所给的陈述相符合,不需要任何附加说明即可以从陈述中直接推出.
'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>7000
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[7000] = $data;
		$this->getPartQuestions("演绎推理","常识判断","70");	
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'<b>第四部分 常识判断</b>
<br/>根据题目要求,从给定的四个选项中,选出你认为正确的一个答案.
'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>8000
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[8000] = $data;
		$this->getPartQuestions("常识判断","资料分析","80");	
		/*
		
		$data = array(
			 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>'第五部分 资料分析'
			,'cent'=>0
			,'optionlength'=>0
			,'type'=>'大题'
			,'belongto'=>0
			
			,'index'=>1098
			,'markingmethod'=>'自动批改'
			,'answer'=>''
			,'description'=>''				
		);
		$this->questions[1099] = $data;		
		for($i=114;$i<130;$i+=5){
			$this->getRead($i);
			$this->getPartQuestions($i+2,$i+6);
		}
		*/		
	}
	
	public function savePathListForXunlei(){}

	public function import(){}	
}
?>