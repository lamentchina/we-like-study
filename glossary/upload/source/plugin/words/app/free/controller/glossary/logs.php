<?php
include_once dirname(__FILE__).'/../../model/glossary/logs.php';
include_once dirname(__FILE__).'/../../model/user.php';

class glossary_logs extends wls{

	private $model = null;

	function glossary_logs(){
		parent::wls();
		$this->model = new m_glossary_logs();
	}	
	
	public function delete(){
		$this->model->delete($_POST['ids']);
	}
	
	public function getMyList() {
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 20;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$search = array();
		session_start();
		$search['user'] = $_SESSION['wls_user']['id']; 
		
		if(isset($_REQUEST['search']) && $_REQUEST['search']!='' && trim($_REQUEST['search'])!='' ){
			$_REQUEST['search'] = trim($_REQUEST['search']);

			//All the conditions are sepreated by blank space.
			//Example:
			//Money>9 Author=admin CreatedDate<2011-12-01 CreateDate>2011-01-01
			//It would search in each condition.
			//If there is no '=','>','<' there , Like:
			//keyword1 keyword2
			//Then just search in title
			$arr_conditions = explode(' ',$_REQUEST['search']);
			
			for($i=0;$i<count($arr_conditions);$i++){
				$temp1 = explode("=",$arr_conditions[$i]);
				$temp2 = explode(">",$arr_conditions[$i]);
				$temp3 = explode("<",$arr_conditions[$i]);
				
				if(count($temp1)==1 && count($temp2)==1 && count($temp3)==1){
					//The title contains
					if(!isset($search['title']))$search['title'] = array();
					$search['title'][] = $arr_conditions[$i];
				}else if(count($temp1)==2){
					//Money is equle to
					if($temp1[0]==$this->il8n['user']['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('=',$temp1[1]);
					}		
					if($temp1[0]==$this->il8n['subject']['subject']){
						if(!isset($search['subject']))$search['subject'] = array();
						$search['subject'][] = array('=',$temp1[1]);
					}												
				}else if(count($temp2)==2){
					//Money is more expensive than
					if($temp2[0]==$this->il8n['user']['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('>',$temp2[1]);
					}									
				}else if(count($temp3)==2){
					//Money is cheapper than
					if($temp3[0]==$this->il8n['user']['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('<',$temp3[1]);
					}									
				}
			}
		}
		$orderBy = ' order by id ';
		if(isset($_REQUEST['sort'])){
			if($_REQUEST['sort']=='money'){
				$orderBy = ' order by money '.$_REQUEST['dir'].' ';
			}
		}
		$data = $this->model->getList($page,$pagesize,$search,$orderBy);
		echo json_encode($data);
	}
	
	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 20;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$search = null;
		if(isset($_REQUEST['search']) && $_REQUEST['search']!='' && trim($_REQUEST['search'])!='' ){
			$_REQUEST['search'] = trim($_REQUEST['search']);
			$search = array();
			
			//All the conditions are sepreated by blank space.
			//Example:
			//Money>9 Author=admin CreatedDate<2011-12-01 CreateDate>2011-01-01
			//It would search in each condition.
			//If there is no '=','>','<' there , Like:
			//keyword1 keyword2
			//Then just search in title
			$arr_conditions = explode(' ',$_REQUEST['search']);
			
			for($i=0;$i<count($arr_conditions);$i++){
				$temp1 = explode("=",$arr_conditions[$i]);
				$temp2 = explode(">",$arr_conditions[$i]);
				$temp3 = explode("<",$arr_conditions[$i]);
				
				if(count($temp1)==1 && count($temp2)==1 && count($temp3)==1){
					//The title contains
					if(!isset($search['title']))$search['title'] = array();
					$search['title'][] = $arr_conditions[$i];
				}else if(count($temp1)==2){
					//Money is equle to
					if($temp1[0]==$this->il8n['user']['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('=',$temp1[1]);
					}		
					if($temp1[0]==$this->il8n['subject']['subject']){
						if(!isset($search['subject']))$search['subject'] = array();
						$search['subject'][] = array('=',$temp1[1]);
					}												
				}else if(count($temp2)==2){
					//Money is more expensive than
					if($temp2[0]==$this->il8n['user']['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('>',$temp2[1]);
					}									
				}else if(count($temp3)==2){
					//Money is cheapper than
					if($temp3[0]==$this->il8n['user']['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('<',$temp3[1]);
					}									
				}
			}
//			print_r($search);exit();
		}
		$orderBy = ' order by id ';
		if(isset($_REQUEST['sort'])){
			if($_REQUEST['sort']=='money'){
				$orderBy = ' order by money '.$_REQUEST['dir'].' ';
			}
		}
		$data = $this->model->getList($page,$pagesize,$search,$orderBy);
		echo json_encode($data);
	}	
	
	public function getDownload(){
		echo $this->model->exportAll();
	}
	
	public function getQuestions(){
		$data = $this->model->getQuestions();
		
		//print_r($data);
		echo json_encode($data);
	}
	
	public function submitQuiz(){
		$this->model->addLogs($_POST['data']);
	}
}
?>