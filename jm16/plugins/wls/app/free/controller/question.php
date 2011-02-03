<?php
class question extends wls{
	
	private $m = null;
	
	function question(){
		parent::wls();
		include_once $this->c->license.'/model/question.php';
		$this->m = new m_question();
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
	
	public function viewUpload(){}
	
	public function saveUpload(){}	
	
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
	
	public function viewExport(){}
	
	public function saveComment(){
		$this->m->id = $_POST['id'];
		$this->m->cumulative("comment_ywrong_".$_POST['value']);
		
		$data = $this->m->getList(1,1,array('id'=>$_POST['id']),null,"id,comment_ywrong_1,comment_ywrong_2,comment_ywrong_3,comment_ywrong_4");
		$data = $data['data'][0];
		echo json_encode($data);
	}
}
?>