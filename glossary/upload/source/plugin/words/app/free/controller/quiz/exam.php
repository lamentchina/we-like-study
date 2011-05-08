<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/exam.php';
include_once dirname(__FILE__).'/../../model/user.php';

class quiz_exam extends quiz{
	private $m = null;

	function quiz_exam(){
		parent::wls();
		$this->m = new m_quiz_exam();
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
		$str = str_replace("\\r\\n"," ",json_encode($data));
		echo str_replace("\\t"," ",$str);
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
				<form action="wls.php?controller=quiz_exam&action=saveImportOne" method="post"
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
			$file = $this->cfg->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importExcel($file);
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
		$this->m->id = $_REQUEST['id'];
		$file = $this->m->exportExcel();
		echo "<a href='".$this->cfg->filePath.$file."'>".$this->lang['download']."</a>";
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
		$data = $this->m->checkMyExam($_POST['answersData'],$_POST['id'],$_POST['time_start'],$_POST['time_stop'],$_POST['time_used']);
//		print_r($data);exit();
		echo json_encode($data);
	}

	public function viewQuiz(){
//		include_once $this->cfg->license.'/model/user.php';
//		$userObj = new m_user();
//		
//		//IE6 is special
//		if( strpos($_SERVER['HTTP_meAGENT'],'MSIE 6') == true ){
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
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->libsPath."ext_3_2_1/resources/css/".$this->cfg->theme."\" />	

<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->license."/view/wls.css\" />	
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."jquery-1.4.2.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."ext_3_2_1/adapter/jquery/ext-jquery-adapter.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."jqueryextend.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."ext_3_2_1/ext-all.js\"></script>	
	
<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/quiz/exam.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/big.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/blank.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/mixed.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/depict.js\"></script>

<script type=\"text/javascript\">
var quiz_exam;
Ext.onReady(function(){
	quiz_exam = new wls.quiz.exam();
	
	quiz_exam.id = ".$_REQUEST['id'].";
	quiz_exam.naming = 'quiz_exam';
	quiz_exam.initLayout();
	quiz_exam.ajaxIds(\"quiz_exam.ajaxQuestions('quiz_exam.addQuestions()');\");
});
</script>
</head>
<body>

</body>
</html>
		";
		echo $html;
	}
	
	public function test(){
		$this->m->importOne($this->cfg->filePath."import/exam/quizlog2.xls");
//		echo 141234123;
	}
	
	/**
	 * For teachers and administrators.
	 * 
	 * */
	public function viewGetList(){
$str = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->libsPath."ext_3_2_1/resources/css/".$this->cfg->theme."\" />	

<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->license."/view/modules.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->cfg->license."/view/wls.css\" />			
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."jquery-1.4.2.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."ext_3_2_1/adapter/jquery/ext-jquery-adapter.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."jqueryextend.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."ext_3_2_1/ext-all.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->cfg->libsPath."ext_3_2_1/ext-lang-zh_CN.js\"></script>

<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/user.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/user/access.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/user/group.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/quiz/exam.js\"></script>


<script type=\"text/javascript\">
var me = new wls.user();
";
if(!isset($_SESSION)){
	session_start();
}
$str .= "me.myUser.access = '".$_SESSION['wls_user']['access']."';\n";
$str .= "me.myUser.access2 = ".json_encode($_SESSION['wls_user']['access2']).";\n";
$str .= "me.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
$str .= "me.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
$str .= "me.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
$str .= "me.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
$str .= "me.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
$str .= "me.myUser.photo = '".$_SESSION['wls_user']['photo']."';\n";
$str .= "
var obj;
Ext.onReady(function(){
	Ext.QuickTips.init(); 
	obj = new wls.quiz.exam();
	var obj2 = obj.getList('qd_w_q_p_l')
	obj2.render(Ext.getBody());
});
</script>
</head>
<body scroll='no'>

</body>
</html>
		";
		echo $str;
	}
	
	public function viewGetMyList(){
		
	}
}
?>