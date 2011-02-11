<?php
include_once dirname(__FILE__).'/../quiz.php';

class quiz_wrong extends quiz{
	private $m = null;

	function quiz_wrong(){
		parent::wls();
		include_once $this->c->license.'/model/quiz/wrong.php';
		$this->m = new m_quiz_wrong();
	}

	public function jsonList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $this->m->getList($page,$pagesize);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function myList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		include_once $this->c->license.'/model/user.php';
		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		$data = $this->m->getList($page,$pagesize,array('id_user'=>$user['id']));
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}	
	
	public function viewUpload(){
		echo '<html>		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->lang['importExcel'].'
					<form action="wls.php?controller=quiz_wrong&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">'.$this->lang['ExcelFilePath'].'</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>	';
	}

	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'error , upload faild'));
		}else{
			$file = $this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importExcel($file);
		}
	}

	public function saveUpdate(){
		$data = array(
			'id'=>$_POST['id'],
		$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			echo "success";
		}else{
			echo "fail";			
		}
	}

	public function viewExport(){
		$this->m->id = $_REQUEST['id'];
		$file = $this->m->exportExcel();
		echo "<a href='/".$file."'>".$this->lang['download']."</a>";
	}

	public function getOne(){
		$id_level_subject = $_POST['id_level_subject'];
		include_once dirname(__FILE__)."/../../model/user.php";
		$userObj = new m_user();
		$user = $userObj->getMyInfo();

		$data = $this->m->getList(1,50,array(
			'id_level_subject'=>$id_level_subject,
			'id_user'=>$user['id'],
		)," Order by RAND() ");
		$data = $data['data'];
		$ids = '';
		for($i=0;$i<count($data);$i++){
			$ids .= $data[$i]['id_question'].",";
		}
		$ids = substr($ids,0,strlen($ids)-1);

		echo $ids;
	}

	public function delete(){
		$this->m->delete($_POST['ids']);
	}

	public function getAnswers(){
		sleep(2);

		$ques = $_POST['answersData'];
		$ques_ = array();
		$id_question = '';
		for($i=0;$i<count($ques);$i++){
			$ques_[$ques[$i]['id']] = $ques[$i]['answer'];
			$id_question .= $ques[$i]['id'].",";
		}
		$id_question = substr($id_question,0,strlen($id_question)-1);

		$answers = $this->m->getAnswers($ques_);
		$json = json_encode($answers);

		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$data = array(
			'date_created'=>date('Y-m-d H:i:s'),
			'id_question'=>$id_question,
			'id_level_subject'=>$_POST['id_level_subject'],
			'application'=>5,
		);
		$id_quiz_log = $obj->insert($data);

		$count_right = 0;
		$count_wrong = 0;
		$count_giveup = 0;
		$cent = 0;
		$mycent = 0;
		$count_total = count($answers);

		include_once dirname(__FILE__)."/../../model/user.php";
		$userObj = new m_user();
		$user = $userObj->getMyInfo();


		$wrongObj = $this->m;

		for($i=0;$i<count($answers);$i++){
			unset($answers[$i]['description']);
			if($answers[$i]['myAnswer']=='I_DONT_KNOW'){
				$answers[$i]['correct'] = 2;
				$count_giveup ++;
			}else if($answers[$i]['myAnswer']==$answers[$i]['answer']){
				$answers[$i]['correct'] = 1;
				$count_right ++;
				$mycent += $answers[$i]['cent'];
			}else{

				$wrongObj->id_question = $answers[$i]['id'];
				$wrongObj->id_user = $user['id'];
				$wrong = array(
					'id_question' => $answers[$i]['id'],
					'id_level_subject' => $_POST['id_level_subject'],
					'id_user'=>$user['id'],
					'date_created'=>date('Y-m-d H:i:s'),
				);
				$wrongObj->insert($wrong);
				$answers[$i]['correct'] = 0;
				$count_wrong ++;
			}
			$cent += $answers[$i]['cent'];
			$answers[$i]['id_question'] = $answers[$i]['id'];
			$answers[$i]['date_created'] = date('Y-m-d H:i:s');
			unset($answers[$i]['id']);
			$answers[$i]['id_quiz_log'] = $id_quiz_log;
			$answers[$i]['id_level_subject'] = $_POST['id_level_subject'];
			$answers[$i]['application'] = 5;
				
		}
		include_once $this->c->license.'/model/question/log.php';
		$obj_ = new m_question_log();
		$obj_->insertMany($answers);

		$data = array(
			'id'=>$id_quiz_log,
			'count_right'=>$count_right,
			'count_wrong'=>$count_wrong,
			'count_giveup'=>$count_giveup,
			'count_total'=>$count_total,
			'proportion'=>0,
			'cent'=>$cent,
			'mycent'=>$mycent,
		);
		if(($count_right+$count_wrong)>0){
			$data['proportion'] = $count_right/($count_right+$count_wrong);
		}
		$obj->update($data);
		
		echo $json;
	}

	public function viewOne(){
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
	
<script type=\"text/javascript\" src=\"wls.php?controller=user&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz/wrong.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/big.js\"></script>


<script type=\"text/javascript\">
var quiz_wrong;
Ext.onReady(function(){
	quiz_wrong = new wls.quiz.wrong();
	
	quiz_wrong.id_level_subject = ".$_REQUEST['id_level_subject'].";
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
}
?>