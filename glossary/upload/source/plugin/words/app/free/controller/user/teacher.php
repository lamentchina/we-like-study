<?php
include_once dirname(__FILE__).'/../../model/user.php';
include_once dirname(__FILE__).'/../../model/user/teacher.php';
include_once dirname(__FILE__).'/../../model/quiz/log.php';

class user_teacher extends wls{
	
	private $m = null;
	
	function user_teacher(){
		parent::wls();
		$this->m = new m_user_teacher();
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
	
	public function getOne(){
		$data = $this->m->getOne($_REQUEST['id']);
		echo json_encode($data);
	}
	
	public function viewQuiz(){
		$userObj = new m_user();
		
		//IE6 is special
		if( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6') == true ){
			$userObj->id = $_REQUEST['uid'];
		}else{
			$me = $userObj->getMyInfo();
		    $userObj->id = $me['id'];
		}
		
		$foo = $userObj->checkMyaccess('1909');
		if($foo==false){
			echo "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
</head>
<body>";
			echo $this->il8n['normal']['accessDenied'];
echo "</body></html>";
			exit();
		}

		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/".$this->c->theme."\" />	

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
	src=\"".$this->c->libsPath."ext_3_2_1/examples/ux/Spinner.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/examples/ux/SpinnerField.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/examples/ux/css/Spinner.css\" />	
	
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
<script type=\"text/javascript\" src=\"".$this->c->license."/view/user.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/user/teacher.js\"></script>

<script type=\"text/javascript\">
var teacher;
Ext.onReady(function(){
	teacher = new wls.user.teacher();
	
	teacher.id = ".$_REQUEST['id'].";
	teacher.naming = 'teacher';
	teacher.initLayout();
	teacher.ajaxIds(\"teacher.ajaxQuestions('teacher.addQuestions()');\");
});
</script>
</head>
<body>

</body>
</html>
		";
		echo $html;
	}
	
	public function getQuestionsByIds(){
		$data = $this->m->getQuestionsByIds($_REQUEST['ids_questions'],$_REQUEST['id_quiz_log']);
		echo json_encode($data);
	}
	
	public function marking(){
		$questionLogObj = new m_question_log();
		$userObj = new m_user();
		$me = $userObj->getMyInfo();
		 
		$_POST['id_user_markkedBy'] = $me['id'];
		$questionLogObj->update($_POST);
	}
	
	public function finishMark(){
		$this->m->finishMark($_POST['id']);
	}
	
	public function viewGetList(){
echo "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/".$this->c->theme."\" />	

<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->license."/view/modules.css\" />
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
	src=\"".$this->c->libsPath."ext_3_2_1/ext-lang-zh_CN.js\"></script>

<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/user.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/user/teacher.js\"></script>

<script type=\"text/javascript\">
var me = new wls.user();
";
if(!isset($_SESSION)){
	session_start();
}
echo "me.myUser.access = '".$_SESSION['wls_user']['access']."';\n";
echo "me.myUser.access2 = ".json_encode($_SESSION['wls_user']['access2']).";\n";
echo "me.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
echo "me.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
echo "me.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
echo "me.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
echo "me.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
echo "me.myUser.photo = '".$_SESSION['wls_user']['photo']."';\n";
echo "
var obj;
Ext.onReady(function(){
	Ext.QuickTips.init(); 
	obj = new wls.user.teacher();
	var obj2 = obj.getList('qd_w_q_p_l')
	obj2.render(Ext.getBody());
});
</script>
</head>
<body scroll='no'>

</body>
</html>
		";
		//echo $html;
	}
}
?>