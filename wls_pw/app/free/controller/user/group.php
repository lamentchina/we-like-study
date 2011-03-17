<?php
include_once dirname(__FILE__).'/../../model/user/group.php';
include_once dirname(__FILE__).'/../../model/subject.php';
include_once dirname(__FILE__).'/../../model/user/access.php';

class user_group extends wls{
	
	private $m = null;
	
	function user_group(){
		parent::wls();
		$this->m = new m_user_group();
	}
	
	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $this->m->getList($page,$pagesize);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}
	
	public function getTreeGrid(){
		$id = $_REQUEST['anode'];
		$data = $this->m->getList(1,500,array('id_'=>$id));
		
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
					'.$this->lang['importExcelAsGroup'].'
					<form action="wls.php?controller=user_group&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">Excel :</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>	';
	}

	
	public function saveImportAll(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],$this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
			$this->m->create();
			$this->m->importAll($this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
		echo '<html xmlns="http://www.w3.org/1999/xhtml">		
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>		
				<body>'.$this->lang['success'].'</body>
			</html>';
	}	
	
	public function delete(){
		if($this->m->delete($_POST['id'])){
			$msg = $this->lang['serverDataDeleted'];
			$msg = str_replace("{1}",$_POST['id'],$msg);
			echo json_encode(array('result'=>'success','msg'=>$msg));
		}else{
			echo 'fail';
		}
		$this->m->setLeaf();
	}
	
	public function saveUpdate(){
		sleep(1);
		$data = array(
			'id'=>$_POST['id'],
			$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			$msg = $this->lang['serverDataUpdated'];
			$msg = str_replace("{1}",$_POST['originalValue'],$msg);
			$msg = str_replace("{2}",$_POST['value'],$msg);
			echo json_encode(array('result'=>'success','msg'=>$msg));
		}else{
			echo 'fail';			
		}
		
		$this->m->setLeaf();
	}
	
	public function exportAll(){
		$file = $this->m->exportAll();
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>		
				<body><a href='".$this->c->filePath.$file."'>".$this->lang['download']."</a></body></html>";
	}
	
	public function getAccessTree(){
		$id = $_REQUEST['id'];				
		$obj = new m_user_access();
		$data = $obj->getListForGroup($id);				
		$data =  $this->t->getTreeData(null,$data);			
		echo json_encode($data);
	}
	
	public function getSubjectTree(){
		$id = $_REQUEST['id'];				
		$obj = new m_subject();
		$data = $obj->getListForGroup($id);
		$data = $this->t->getTreeData(null,$data);			
		echo json_encode($data);
	}	
	
	public function getCourseTree(){
		$id = $_REQUEST['id'];			
		$obj = new m_subject();
		$data = $obj->getListForGroup($id);
		$data =  $this->t->getTreeData(null,$data);			
		echo json_encode($data);
	}
	
	public function saveAccessTree(){
		$this->m->updateaccess($_POST['id'],$_POST['ids']);
	}
	
	public function saveSubjectTree(){
		$this->m->updateSubject($_POST['id'],$_POST['ids']);
	}	
	
	public function saveCourseTree(){
		$this->m->updateSubject($_POST['id'],$_POST['ids']);
	}
	
	public function add(){
		sleep(1);
		$id = $this->m->insert($_POST);
		echo $id;
		$this->m->setLeaf();
	}
	
	public function setLeaf(){
		$this->m->setLeaf();
	}
	
}
?>