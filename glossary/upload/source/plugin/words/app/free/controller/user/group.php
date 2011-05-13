<?php
include_once dirname(__FILE__).'/../../model/user/group.php';
include_once dirname(__FILE__).'/../../model/subject.php';
include_once dirname(__FILE__).'/../../model/user/access.php';

class user_group extends wls{
	
	private $model = null;
	
	function user_group(){
		parent::wls();
		$this->model = new m_user_group();
	}
	
	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $this->model->getList($page,$pagesize);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}
	
	public function getTreeGrid(){
		$id = $_REQUEST['anode'];
		$data = $this->model->getList(1,500,array('id_'=>$id));
		
		for($i=0;$i<count($data['data']);$i++){
			$data['data'][$i]['_is_leaf'] = $data['data'][$i]['isleaf'];			
			$data['data'][$i]['_parent'] = ($_REQUEST['anode']=='')?null:intval($_REQUEST['anode']);
		}		
		
		$arr = array(
			 'success'=>true
			,'total'=>count($data['data'])
			,'data'=>$data['data']
		);
		
		echo json_encode($arr);
	}
	
	public function importAll(){
		echo '<html>		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->il8n['file']['importExcelAsGroup'].'
					<form action="wls.php?controller=user_group&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">Excel :</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->il8n['normal']['submit'].'" />
					</form>
				</body>
			</html>	';
	}

	
	public function saveImportAll(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],$this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
			$this->model->create();
			$this->model->importAll($this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
		echo '<html xmlns="http://www.w3.org/1999/xhtml">		
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>		
				<body>'.$this->il8n['normal']['success'].'</body>
			</html>';
	}	
	
	public function delete(){
		if($this->model->delete($_POST['id'])){
			$msg = $this->il8n['normal']['serverDataDeleted'];
			$msg = str_replace("{1}",$_POST['id'],$msg);
			echo json_encode(array('result'=>'success','msg'=>$msg));
		}else{
			echo 'fail';
		}
		$this->model->setLeaf();
	}
	
	public function saveUpdate(){
		sleep(1);
		$data = array(
			'id'=>$_POST['id'],
			$_POST['field']=>$_POST['value']
		);
		if($this->model->update($data)){
			$msg = $this->il8n['normal']['serverDataUpdated'];
			$msg = str_replace("{1}",$_POST['originalValue'],$msg);
			$msg = str_replace("{2}",$_POST['value'],$msg);
			echo json_encode(array('result'=>'success','msg'=>$msg));
		}else{
			echo 'fail';			
		}
		
		$this->model->setLeaf();
	}
	
	public function exportAll(){
		$file = $this->model->exportAll();
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>		
				<body><a href='".$this->c->filePath.$file."'>".$this->il8n['file']['download']."</a></body></html>";
	}
	
	public function getAccessTree(){
		$id = $_REQUEST['id'];				
		$obj = new m_user_access();
		$data = $obj->getListForGroup($id);		
		for($i=0;$i<count($data);$i++){
			unset($data[$i]['icon']);
		}		
		$data =  $this->tool->getTreeData(null,$data);			
		echo json_encode($data);
	}
	
	public function getSubjectTree(){
		$id = $_REQUEST['id'];				
		$obj = new m_subject();
		$data = $obj->getListForGroup($id);
		for($i=0;$i<count($data);$i++){
			unset($data[$i]['icon']);
		}	
		$data = $this->tool->getTreeData(null,$data);			
		echo json_encode($data);
	}	
	
	public function getCourseTree(){

	}
	
	public function saveAccessTree(){
		$this->model->updateaccess($_POST['id'],$_POST['ids']);
		$userObj = new m_user();
		$userObj->cleanCache();
	}
	
	public function saveSubjectTree(){
		$this->model->updateSubject($_POST['id'],$_POST['ids']);
		$userObj = new m_user();
		$userObj->cleanCache();
	}	
	
	//TODO undone yet, should redesign again.
	public function saveCourseTree(){
		$this->model->updateSubject($_POST['id'],$_POST['ids']);
	}
	
	public function add(){
		sleep(1);
		$id = $this->model->insert($_POST);
		echo $id;
		$this->model->setLeaf();
		$userObj = new m_user();
		$userObj->cleanCache();
	}
	
	public function setLeaf(){
		$this->model->setLeaf();
	}
}
?>