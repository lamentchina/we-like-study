<?php
class user_group extends wls{
	
	private $m = null;
	
	function user_group(){
		parent::wls();
		include_once $this->c->license.'/model/user/group.php';
		$this->m = new m_user_group();
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
	
	public function viewUpload(){
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
	
	public function viewUploadOne(){
		echo '<html>		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->lang['importExcelAsGroupItem'].'
					<form action="wls.php?controller=user_group&action=saveUploadOne" method="post"
					enctype="multipart/form-data">
						<label for="file">Excel :</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>		
		';
	}	
	
	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],$this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
			$this->m->create();
			$this->m->importExcel($this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
		echo '<html xmlns="http://www.w3.org/1999/xhtml">		
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>		
				<body>'.$this->lang['success'].'</body>
			</html>';
	}

	public function saveUploadOne(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			$file = $this->c->filePath."/upload/upload".date('Ymdims').$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importExcelOne($file);
		}
		echo '<html>		
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>		
				<body>'.$this->lang['success'].'</body>
			</html>';
	}		
	
	public function saveUpdate(){
		$data = array(
			'id'=>$_POST['id'],
			$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			echo 'success';
		}else{
			echo 'fail';			
		}
	}
	
	public function viewExport(){
		$file = $this->m->exportExcel();
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>		
				<body><a href='".$file."'>".$this->lang['download']."</a></body></html>";
	}
	
	public function viewExportOne(){
		$id_level = $_REQUEST['id_level'];
		$this->m->id_level = $id_level;
		$file = $this->m->exportExcelOne();
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>		
				<body><a href='".$file."'>".$this->lang['download']."</a></body></html>";
	}
	
	public function getaccess(){
		$id = $_REQUEST['id'];
		include_once dirname(__FILE__).'/../../model/user/access.php';
		
		$obj = new m_user_access();
		$data = $obj->getListForGroup($id);		
		
		include_once dirname(__FILE__).'/../../model/tools.php';
		$t = new tools();
		$data =  $t->getTreeData(null,$data);		
		
		echo json_encode($data);
	}
	
	public function getSubject(){
		$id = $_REQUEST['id'];
		include_once dirname(__FILE__).'/../../model/subject.php';
		
		$obj = new m_subject();
		$data = $obj->getListForGroup($id);
		
		include_once dirname(__FILE__).'/../../model/tools.php';
		$t = new tools();
		$data =  $t->getTreeData(null,$data);	
		
		echo json_encode($data);
	}
	
	public function updateaccess(){
		$this->m->updateaccess($_POST['id'],$_POST['ids']);
	}
	
	public function updateSubject(){
		$this->m->updateSubject($_POST['id'],$_POST['ids']);
	}
	
	public function addone(){
		sleep(1);
		$id = $this->m->insert($_POST);
		echo $id;
	}
}
?>