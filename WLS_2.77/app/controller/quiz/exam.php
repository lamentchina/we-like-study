<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/exam.php';
include_once dirname(__FILE__).'/../../model/user.php';

class quiz_exam extends quiz{
	private $model = null;

	function quiz_exam(){
		parent::wls();
		$this->model = new m_quiz_exam();
	}
	
	public function getOne(){
		$data = $this->model->getList(1,1,array('id'=>$_REQUEST['id']),null,'money,name_subject,title,ids_questions,description,author,score_top,score_avg');
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
		$data = $this->model->getList($page,$pagesize,$search,' order by date_created ');
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
		$data = $this->model->getList($page,$pagesize,$search,' order by date_created ');
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}	

	public function saveImportOne(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'wrong c q p'));
			echo 'fail';
		}else{
			$file = $this->cfg->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->model->importExcel($file);
			echo 'success';
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
		$this->model->id = $_REQUEST['id'];
		$file = $this->model->exportExcel();
		echo "<a href='".$this->cfg->filePath.$file."'>".$this->lang['download']."</a>";
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
		$data = $this->model->checkMyExam($_POST['answersData'],$_POST['id'],$_POST['time_start'],$_POST['time_stop'],$_POST['time_used']);
		echo json_encode($data);
	}
}
?>