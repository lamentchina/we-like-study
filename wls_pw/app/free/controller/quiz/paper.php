<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';
include_once dirname(__FILE__).'/../../model/user.php';

class quiz_paper extends quiz{
	private $m = null;

	function quiz_paper(){
		parent::wls();
		$this->m = new m_quiz_paper();
	}
	
	public function getOne(){
		$data = $this->m->getList(1,1,array('id'=>$_REQUEST['id']),null,'money,name_subject,title,ids_questions,description,author,score_top,score_avg');
		$data = $data['data'];
		$data = $data[0];
		echo json_encode($data);		
	}

	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$search = null;
		if(isset($_REQUEST['search']) && $_REQUEST['search']!=''){
			$search = array(
				'title'=>$_REQUEST['search']
			);
		}
		$data = $this->m->getList($page,$pagesize,$search,' order by date_created ');
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}
	
	//TODO
	public function getMyList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$search = null;
		if(isset($_REQUEST['search']) && $_REQUEST['search']!=''){
			$search = array(
				'title'=>$_REQUEST['search']
			);
		}
		$data = $this->m->getList($page,$pagesize,$search,' order by date_created ');
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}	

	public function importOne(){
		echo '<html>		
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			<body>
				'.$this->lang['importExcel'].'
				<form action="wls.php?controller=quiz_paper&action=saveImportOne" method="post"
				enctype="multipart/form-data">
					<label for="file">'.$this->lang['ExcelFilePath'].'</label>
					<input type="file" name="file" id="file" />
					<br />
					<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
				</form>
			</body>
		</html>';
	}

	public function saveImportOne(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'wrong c q p'));
			echo 'fail';
		}else{
			$file = $this->c->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importOne($file);
			echo 'success';
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

	public function exportOne(){
		$this->m->id_paper = $_REQUEST['id'];
		$file = $this->m->exportOne();
		echo "<a href='".$this->c->filePath.$file."'>".$this->lang['download']."</a>";
	}

	public function delete(){
		$this->m->delete($_POST['id']);
	}

	/**
	 * If the current user is only a guest, just output the answers and do nothing.
	 * If the current user is system's user, it would do a lot of logs, as:
	 * quiz log
	 * questions log
	 * knowledge log
	 * My wrongs book
	 * */
	public function getAnswers(){
		//If the server-side's speed is very fast , 
		//faster than the client-side's JavaScript parsing , there would be an error
		//So let the server sleep 2 senconds whatever
		sleep(2);
		$data = $this->m->checkMyPaper($_POST['answersData'],$_POST['id']);
		echo json_encode($data);
	}

	public function viewQuiz(){
//		include_once $this->c->license.'/model/user.php';
//		$userObj = new m_user();
//		
//		//IE6 is special
//		if( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6') == true ){
//			$userObj->id = $_REQUEST['uid'];
//		}else{
//			$me = $userObj->getMyInfo();
//		    $userObj->id = $me['id'];
//		}		
//		$foo = $userObj->checkMyaccess('1107');
//		
//		if($foo==false){
//			echo "access request";
//			exit();
//		}else{
//			if($this->m->checkMoney($_REQUEST['id'])==false){
//				echo "money request";
//				exit();
//			}
//		}
		
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
<!--  
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/adapter/ext/ext-base.js\"></script>	
-->
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/ext-all.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."star-rating/jquery.rating.pack.js\"></script>		
	
<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz/paper.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/big.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/blank.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/mixed.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/depict.js\"></script>

<script type=\"text/javascript\">
var quiz_paper;
Ext.onReady(function(){
	quiz_paper = new wls.quiz.paper();
	
	quiz_paper.id = ".$_REQUEST['id'].";
	quiz_paper.naming = 'quiz_paper';
	quiz_paper.initLayout();
	quiz_paper.ajaxIds(\"quiz_paper.ajaxQuestions('quiz_paper.addQuestions()');\");
});
</script>
</head>
<body>

</body>
</html>
		";
		echo $html;
	}
}
?>