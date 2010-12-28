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
	
	public function viewRandQuizByDWZ(){
		include_once 'controller/user.php';
		$obj = new user();
		$userinfo = $obj->getUserInfo('mine');
		
		$arr = explode(",",$userinfo['id_group']);
		$search = null;
		//如果是管理员们
		if(in_array($this->cfg->group_admin,$arr)){
				
		}else{
			$search = array('id_user'=>$userinfo['id_user']);
		}
		
		include_once 'controller/quiz/type.php';
		$obj = new quiz_type();
		$data = $obj->getList('array',1,100,$search);
		$data = $data['rows'];
		$html = '
		<table>
			<tr>
				<td>选择科目</td>
				<td>
					<select name="quiz_type">
					';
		for($i=0;$i<count($data);$i++){
			$html .= "<option value='".$data[$i]['id']."'>".$data[$i]['title']."</option>";
		}
		
		$html .=	'</select>				
				</td>
			</tr>
			<tr>
				<td>题目数</td>
				<td>
					<input type="text" />							
				</td>
			</tr>
			<tr>
				<td columns="2">
					<button onclick="wls_q_r();">提交</button>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
		var wls_q_r = function(){	
			var id = $("select[name=quiz_type] option:selected").val();
			window.open ("wls.php?controller=quiz&action=viewRandQuizByJquery&id_quiz_type="+id, "newwindow");
		}
		</script>
		';
		echo $html;
	}
	
	public function viewRandQuizByJquery(){
		$html = "
		
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"libs/DWZ/javascripts/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script src=\"view/wls.js\" type=\"text/javascript\"></script>
<script src=\"view/quiz/quiz.js\" type=\"text/javascript\"></script>
<script src=\"view/quiz/paper.js\" type=\"text/javascript\"></script>
<script src=\"view/question/question.js\" type=\"text/javascript\"></script>

<script src=\"view/question/choice.js\" type=\"text/javascript\"></script>
<script src=\"view/question/reading.js\" type=\"text/javascript\"></script>
<script src=\"view/question/blank.js\" type=\"text/javascript\"></script>
<link href=\"view/wls.css\" rel=\"stylesheet\" type=\"text/css\" />

</head>
<body style=\"border: 0px; padding: 0px; margin: 0px;\">
<div id=\"wls\"><div>
<script type=\"text/javascript\">
var obj_paper = new wls_quiz_paper();

obj_paper.naming =\"obj_paper\";
obj_paper.quizDomId =\"wls\";
obj_paper.initLayout();
obj_paper.paperId = 2;
obj_paper.submitQuesWay = \"onceAll\";
var funstr = 'obj_paper.AJAXAllQues(\"obj_paper.initButton();obj_paper.addSubQuesNav();obj_paper.getClock();\")';

obj_paper.AJAXData(funstr);
</script>
</body>
</html>		
		
		";
		echo $html;
	}
}
?>