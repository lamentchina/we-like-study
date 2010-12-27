<?php 
class quiz extends wls{
	
	/**
	 * 一次性得到多个题目的数据
	 * */
	public function getQuestions(){
		
	} 
	
	public function undo(){
		echo "<b>此功能尚未完成...</b>";
	}
	
	public function authorInfo(){
		$content = file('file/aboutme.txt');
		$content = implode("\n", $content);
		$content = str_replace("\n","<br/>",$content);
		
		echo $content;		
	}
	
	public function commercial(){
		$content = file('file/commercial.txt');
		$content = implode("\n", $content);
		$content = str_replace("\n","<br/>",$content);
		
		echo $content;		
	}
	
	public function aboutplugin(){
		$content = file('file/readme.txt');
		$content = implode("\n", $content);
		$content = str_replace("\n","<br/>",$content);
		
		echo $content;		
	}
	
	/**
	 * 一次性检验所有的题目
	 * */
	public function checkAllOnce(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$answers = $_REQUEST['myAnswers'];
		$ids = '';
		for($i=0;$i<count($answers);$i++){
			$ids .= $answers[$i]['id'].",";	
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$sql = "select answer,id,description,markingmethod from ".$pfx."wls_question where id in (".$ids.");";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		include 'controller/question/record.php';
		$record = new question_record();
		
		include 'controller/quiz/wrongs.php';
		$quiz_wrongs_obj = new quiz_wrongs();		
		
		for($i=0;$i<count($answers);$i++){
			$answers[$i]['description'] = $data[$i]['description'];
			$answers[$i]['answer'] = trim($data[$i]['answer']);
			$answers[$i]['markingmethod'] = $data[$i]['markingmethod'];
			if($data[$i]['markingmethod']!=0){//非自动批改
				$answers[$i]['correct'] = 3;
				$record->add($data[$i]['id'],$answers[$i]['myAnswer'],'3','1');	
			}else{
				if($answers[$i]['myAnswer']=='I_DONT_KNOW'){
					$record->add($data[$i]['id'],$answers[$i]['myAnswer'],'2','1');	
					$answers[$i]['correct'] = 2;
				}else if(trim($data[$i]['answer'])!=trim($answers[$i]['myAnswer'])){
					$record->add($data[$i]['id'],$answers[$i]['myAnswer'],'1','1');	
					$answers[$i]['correct'] = 0;

					$quiz_wrongs_obj->wrong($data[$i]['id']);
				}else {
					$record->add($data[$i]['id'],$answers[$i]['myAnswer'],'0','1');	
					$answers[$i]['correct'] = 1;
					
					$quiz_wrongs_obj->right($data[$i]['id']);
				}
			}
		}
		echo json_encode($answers);
	}
}
?>