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
		//TODO		
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
}
?>