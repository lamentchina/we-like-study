<?php
include_once 'controller/quiz/paper/normal.php';

/**
 * 专门针对 www.fukan.org 而设计的转换文件
 * */
class install_fukan_xml extends quiz_paper_normal{

	public $content = '';

	public function transformXML2Excel($path=null){
		if($path==null && isset($_REQUEST['path']))$path = $_REQUEST['path'];
		$content = file($path);  
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("\n","",$content);
		$this->content = $content;
		
		$paper = array();		
		$p1 = strpos($content,"<CHAPTER");
		$p2 = strpos($content,">",$p1);
		$str = substr($content,$p1,$p2-$p1);
		$p1 = strpos($str,"name=\"");
		$p2 = strpos($str,"\" desc=");		
		$paper['title_quiz_type'] = substr($str,$p1+6,$p2-$p1-6);
		$p1 = strpos($str,"desc=\"");
		$p2 = strpos($str,"\"",$p1+6);	
		$paper['description'] = substr($str,$p1+6,$p2-$p1-6);
		$paper['title'] = $paper['title_quiz_type'].rand(1,100);
		$paper['date_created'] = date('Y-m-d');
		
		$this->paper = $paper;
		$this->savePaper();
		
		$this->getQuestionByXML();
	}
	
	public function format_($title){
		$title = str_replace("\t","",$title);
		$title = str_replace(' ','',$title);
		$title = str_replace('\n',"",$title);
		return $title;
	}
	
	public function getQuestionByXML(){
		$ques = array();
		$content = $this->content;
		$arr = explode("<SUBJECT ",$content);
		for($i=1;$i<count($arr);$i++){
			$p1 = strpos($arr[$i],"<SUB_FACE>");
			$p2 = strpos($arr[$i],"</SUB_FACE>");
			$title = substr($arr[$i],$p1+10,$p2-$p1-10);
			
			$p1 = strpos($arr[$i],"A\">");
			$p2 = strpos($arr[$i],"<",$p1);
			$a = substr($arr[$i],$p1+3,$p2-$p1-3);
			
			$p1 = strpos($arr[$i],"B\">");
			$p2 = strpos($arr[$i],"<",$p1);
			$b = substr($arr[$i],$p1+3,$p2-$p1-3);
			
			$p1 = strpos($arr[$i],"C\">");
			$p2 = strpos($arr[$i],"<",$p1);
			$c = substr($arr[$i],$p1+3,$p2-$p1-3);

			$p1 = strpos($arr[$i],"TRUE");
			$answer = substr($arr[$i],$p1+11,1);
			
			$ques[] = array(
				'id_quiz_type'=>$this->paper['id_quiz_type'],
				'title_quiz_type'=>$this->paper['title_quiz_type'],
				'id_quiz_paper'=>$this->paper['id'],
				'title_quiz_paper'=>$this->paper['title'],
				'title'=>$this->format_($title),
				'details_' => array(
					'options'=>array(
						array(
							'option'=>'A',
							'title'=>$this->format_($a),
						),
						array(
							'option'=>'B',
							'title'=>$this->format_($b),
						),
						array(
							'option'=>'C',
							'title'=>$this->format_($c),
						),
					)
				),
				'cent'=>1,
				'answer'=>$answer,
				'type'=>1,
				'extype'=>'选择题'
			);
		}
		print_r($ques);
//		exit();		
		$this->ques = $ques;	
		$this->saveQuestion();
		$this->updatePaper();
	}

}
?>