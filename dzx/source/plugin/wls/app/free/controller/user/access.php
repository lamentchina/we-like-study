<?php
class user_access extends wls{
	
	private $m = null;
	
	function user_access(){
		parent::wls();
		include_once $this->c->license.'/model/user/access.php';
		$this->m = new m_user_access();
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
					
					'.$this->lang['importExcel'].'
					<form action="wls.php?controller=user_access&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">'.$this->lang['ExcelFilePath'].'</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>';
	}
	
	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			$file = $this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->create();
			$this->m->importExcel($file);
		}
		echo 'success';
	}	
	
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
	
	public function viewExport(){
		$file = $this->m->exportExcel();
		echo "<a href='".$file."'>".$this->lang['download']."</a>";
	}
	
}
?>