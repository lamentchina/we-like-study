<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__)."/../../model/user.php";
include_once dirname(__FILE__).'/../../model/quiz/random.php';
include_once dirname(__FILE__).'/../../model/quiz/log.php';

class quiz_random extends quiz{
	private $m = null;

	function quiz_random(){
		parent::wls();		
		$this->m = new m_quiz_random();
	}


	public function viewQuiz(){
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
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/quiz/random.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/big.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/blank.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/mixed.js\"></script>
<script type=\"text/javascript\" src=\"".$this->cfg->license."/view/question/depict.js\"></script>

<script type=\"text/javascript\">
var quiz_random = null;
Ext.onReady(function(){
	quiz_random = new wls.quiz.random();
	quiz_random.naming = 'quiz_random';
	quiz_random.subject_id_level = ".$_REQUEST['subject_id_level'].";
	quiz_random.questionType = ".$_REQUEST['questionType'].";
	quiz_random.initLayout();
	quiz_random.ajaxIds(\"quiz_random.ajaxQuestions('quiz_random.addQuestions()');\");
});
</script>
</head>
<body>

</body>
</html>";
		echo $html;
	}
	
	
	public function getOne(){
		$ids = $this->m->getMyRandomsId($_REQUEST['subject_id_level'],$_REQUEST['questionType']);
		echo $ids;
	}
	
	public function getAnswers(){

		sleep(1);
		$data = $this->m->getAnswers($_POST['answersData']);
		echo json_encode($data);
	}
}
?>