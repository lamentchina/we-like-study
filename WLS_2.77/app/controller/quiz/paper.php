<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';
include_once dirname(__FILE__).'/../../model/user.php';

class quiz_paper extends quiz{
	private $model = null;

	function quiz_paper(){
		parent::wls();
		$this->model = new m_quiz_paper();
	}

	public function getOne(){
		if($this->model->checkMoney($_REQUEST['id'])==true){			
			$data = $this->model->getList(1,1,array('id'=>$_REQUEST['id']));
			$data = $data['data'];
			$data = $data[0];
			echo json_encode($data);
		}else{
			echo json_encode(array(
				'ids_questions'=>0
			));
		}
	}

	/**
	 * The getMyList functionality is inside the 'getlist'
	 * Why I design in this ? 
	 * Because even when the administrator want to see the list ,
	 * system will also check his subjects access too 
	 * */
	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
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
					//Author's name is like
					if($temp1[0]==$this->il8n['quiz']['author']){
						if(!isset($search['author']))$search['author'] = array();
						$search['author'][] = array('=',$temp1[1]);
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
		$orderBy = ' order by date_created ';
		if(isset($_REQUEST['sort'])){
			if($_REQUEST['sort']=='money'){
				$orderBy = ' order by money '.$_REQUEST['dir'].' ';
			}
		}
		$data = $this->model->getList($page,$pagesize,$search,$orderBy);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	/**
	 * It's impossible to export all the papers from the database to the client in a sudden.
	 * The data is huge, the server will crush.
	 * So , system will export all the papers one by one. 
	 * By ajax
	 * */	
	public function exportAll(){
		//Check the current user if he has this permission or not.
		$userObj = new m_user();
		if($userObj->checkMyaccess("1108",false)==false)return;

		$folder = $this->cfg->filePath.'export/';
		
		//Part A , pure server actions. Export single one paper to somewhere.
		//It's called by AJAX. The clinet will send a GET['id'] here.
		if(isset($_REQUEST['id'])){
			$this->model->id_paper = $_REQUEST['id'];
			$this->model->exportOne($this->cfg->filePath.'export/'.$_REQUEST['id'].".xls");
			echo 'ok';
		
		//Part B , Pure HTML out put. There is a AJAX Call in the JS code, the AJAX
		//will call the main export-action-server by sending a id. 
		}else{
			//Get all the paper's ids from the table-wls_quiz_paper 
			$ids = $this->model->exportAll();
			echo '['.$ids.']';exit();
		}				
	}

	/**
	 * Not import all the data in a sudden.
	 * By Ajax , import one by one
	 * */
	public function importAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("1109",false)==false)return;

		$folder = $this->cfg->filePath.'import/paper/';
		if(isset($_REQUEST['id'])){
			$this->model->importOne($_REQUEST['id']);
			echo 'ok';
		}else{
			$filename = $this->tool->getAllFiles($folder);
			echo json_encode($filename);
		}
	}
	
	public function saveImportOne(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'wrong c q p'));
			echo 'fail';
		}else{
			$file = $this->cfg->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			if($this->model->importOne($file)==false){
				echo $this->il8n['file']['importFormatWrong'];
			}else{
				echo 'success';
			}
		}
	}

	public function saveUpdate(){
		$data = array(
			'id'=>$_POST['id'],
		$_POST['field']=>$_POST['value']
		);
		if($this->model->update($data)){
			echo "success";
		}else{
			echo "fail";
		}
	}

	public function exportOne(){
		$this->model->id_paper = $_REQUEST['id'];
		$file = $this->model->exportOne();
		echo "<a href='".$this->cfg->filePath.$file."'>".$this->il8n['normal']['download']."</a>";
	}

	public function delete(){
		$this->model->delete($_POST['id']);
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
		$data = $this->model->checkMyPaper($_POST['answersData'],$_POST['id']);
		echo json_encode($data);
	}
}
?>