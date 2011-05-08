<?php
include_once dirname(__FILE__).'/../model/glossary.php';
include_once dirname(__FILE__).'/../model/glossary/logs.php';
include_once dirname(__FILE__).'/../model/glossary/levels.php';
include_once dirname(__FILE__).'/../model/glossary/levels/logs.php';
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/group.php';

class glossary extends wls{

	private $model = null;

	function glossary(){
		parent::wls();
		$this->model = new m_glossary();
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

					if($temp1[0]==$this->il8n['subject']['subject']){
						if(!isset($search['subject']))$search['subject'] = array();
						$search['subject'][] = array('=',$temp1[1]);
					}
				}else if(count($temp2)==2){

				}else if(count($temp3)==2){
						
				}
			}
			//print_r($search);exit();
		}


		$data = $this->model->getList($page,$pagesize,$search);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function importAll(){
		echo '<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->il8n['file']['importExcel'].'
					<form action="wls.php?controller=glossary&action=saveImportAll" method="post"
					enctype="multipart/form-data">
						<label for="file">'.$this->il8n['file']['ExcelFilePath'].'</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->il8n['normal']['submit'].'"  />
					</form>
				</body>
			</html>		
		';
	}

	public function saveImportAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("3001",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to glossary::saveImportAll ");
		}

		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			$file = $this->cfg->filePath."upload/". $_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
				
			$this->model->importAll($file);
		}
		echo 'success';
	}

	public function exportAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("3002",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to glossary::exportAll ");
		}

		$file = $this->model->exportAll();
		echo "<a href='".$this->cfg->filePath.$file."'>".$this->il8n['normal']['download']."</a>";
	}

	public function delete(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("3003",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to glossary::delete ");
		}
		if($this->model->delete($_POST['id'])){
			echo "success";
		}else{
			echo "fail";
		}
	}

	public function saveUpdate(){
		/*
		$userObj = new m_user();
		if($userObj->checkMyaccess("3004",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to glossary::saveUpdate ");
		}
		*/

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

	public function add(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("3005",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to glossary::add ");
		}

		sleep(1);
		$id = $this->model->insert($_POST);

		echo $id;
	}

	public function getQuestions(){
		if(!isset($_REQUEST['subject']) || !isset($_REQUEST['level']))die('Parameters missing');

		$subject = $_REQUEST['subject'];
		$level = $_REQUEST['level'];
		
		$obj = new m_glossary_levels_logs();
		if(!$obj->checkIfICanDo($subject, $level))die('Access denied');		

		$data = $this->model->getQuestions($subject,$level);
		$data['questionsData'] = $data;
		$obj = new m_glossary_levels();
		$obj->conn = $this->model->conn;
		$levelData = $obj->getItem($level, $subject);
		$data['levelData'] = $levelData;
		echo json_encode($data);
	}
	
	public function submitQuiz(){
		$obj = new m_glossary_logs();
		$obj->addLogs($_POST['data']);
	}
}
?>