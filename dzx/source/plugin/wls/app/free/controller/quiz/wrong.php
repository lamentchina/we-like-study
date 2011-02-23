<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__)."/../../model/user.php";
include_once dirname(__FILE__).'/../../model/quiz/wrong.php';
include_once dirname(__FILE__).'/../../model/quiz/log.php';

class quiz_wrong extends quiz{
	private $m = null;

	function quiz_wrong(){
		parent::wls();		
		$this->m = new m_quiz_wrong();
	}

	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $this->m->getList($page,$pagesize);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function getMyList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		$data = $this->m->getList($page,$pagesize,array('id_user'=>$user['id']));
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}	
	
	public function importAll(){
		echo '<html>		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->lang['importExcel'].'
					<form action="wls.php?controller=quiz_wrong&action=saveImportAll" method="post"
					enctype="multipart/form-data">
						<label for="file">'.$this->lang['ExcelFilePath'].'</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>	';
	}

	public function saveImportAll(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'error , upload faild'));
		}else{
			$file = $this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importExcel($file);
		}
	}

	public function exportAll(){
		$this->m->id = $_REQUEST['id'];
		$file = $this->m->exportExcel();
		echo "<a href='/".$file."'>".$this->lang['download']."</a>";
	}

	public function delete(){
		$this->m->delete($_POST['ids']);
	}

	public function getAnswers(){
		sleep(2);

		$ques_ = array();
		for($i=0;$i<count($_POST['answersData']);$i++){
			$ques_[$_POST['answersData'][$i]['id']] = $_POST['answersData'][$i]['answer'];
		}
		$questionObj = new m_question();
		$answers = $questionObj->getAnswers($ques_);
		
		$userObj = new m_user();
		$me = $userObj->getMyInfo();
		
		for($i=0;$i<count($answers);$i++){
			if( ($answers[$i]['myAnswer']!=$answers[$i]['answer']) &&
				$answers[$i]['myAnswer']!='I_DONT_KNOW'
			){
				$this->m->insert(array(
					 'id_question'=>$answers[$i]['id']
					,'id_user'=>$me['id']
				));
			}
		}
		
		echo json_encode($answers);
	}

	public function viewQuiz(){
		$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."star-rating/jquery.rating.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->license."/view/wls.css\" />	
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."jquery-1.4.2.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/adapter/jquery/ext-jquery-adapter.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."jqueryextend.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/ext-all.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."star-rating/jquery.rating.pack.js\"></script>		
	
<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz/wrong.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/big.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/blank.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/mixed.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/depict.js\"></script>

<script type=\"text/javascript\">
var quiz_wrong;
Ext.onReady(function(){
	quiz_wrong = new wls.quiz.wrong();
	quiz_wrong.naming = 'quiz_wrong';
	quiz_wrong.initLayout();
	quiz_wrong.ajaxIds(\"quiz_wrong.ajaxQuestions('quiz_wrong.addQuestions()');\");
});
</script>
</head>
<body>

</body>
</html>";
		echo $html;
	}
	
	public function getOne(){
		echo $this->m->getMyWrongsId();
	}
}
?>