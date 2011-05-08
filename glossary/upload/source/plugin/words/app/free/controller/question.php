<?php
include_once dirname(__FILE__).'/../model/question.php';

class question extends wls{
	
	private $m = null;
	
	function question(){
		parent::wls();
		$this->m = new m_question();
	}
	
	public function saveComment(){
		$this->m->id = $_POST['id'];
		$this->m->cumulative("comment_ywrong_".$_POST['value']);
		
		$data = $this->m->getList(1,1,array('id'=>$_POST['id']),null,"id,comment_ywrong_1,comment_ywrong_2,comment_ywrong_3,comment_ywrong_4");
		$data = $data['data'][0];
		echo json_encode($data);
	}
	
	public function getByIds(){
		$ids = $_REQUEST['ids_questions'];
		$data = $this->m->getByIds($ids);
		echo json_encode($data);
	}
}
?>