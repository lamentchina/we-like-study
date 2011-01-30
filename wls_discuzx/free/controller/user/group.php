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
		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					导入EXCEL
					<form action="wls.php?controller=user_group&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">EXCEL文件:</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="提交" />
					</form>
				</body>
			</html>		
		';
	}
	
	public function viewUploadOne(){
		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					导入EXCEL
					<form action="wls.php?controller=user_group&action=saveUploadOne" method="post"
					enctype="multipart/form-data">
						<label for="file">EXCEL文件:</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="提交" />
					</form>
				</body>
			</html>		
		';
	}	
	
	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
			$this->m->create();
			$this->m->importExcel(dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
		echo '已经导入';
	}

	public function saveUploadOne(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
//			$this->m->create();
			$this->m->importExcelOne(dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
		echo '已经导入';
	}		
	
	public function saveUpdate(){
		$data = array(
			'id'=>$_POST['id'],
			$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			echo "已经更新";
		}else{
			echo "更新失败";			
		}
	}
	
	public function viewExport(){
		$file = $this->m->exportExcel();
		echo "<a href='/".$file."'>下载</a>";
	}
	
	public function viewExportOne(){
		$id_level = $_REQUEST['id_level'];
		$this->m->id_level = $id_level;
		$file = $this->m->exportExcelOne();
		echo "<a href='/".$file."'>下载</a>";
	}
	
	public function getPrivilege(){
		$id = $_REQUEST['id'];
		include_once dirname(__FILE__).'/../../model/user/privilege.php';
		
		$obj = new m_user_privilege();
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
	
	public function updatePrivilege(){
		$this->m->updatePrivilege($_POST['id'],$_POST['ids']);
	}
	
	public function updateSubject(){
		$this->m->updateSubject($_POST['id'],$_POST['ids']);
	}
	
	public function saveP2P(){
		$path =  'E:/Projects/WEBS/PHP/wls4/file/test/group2privilege.xls';
		$this->m->importExcelWithP($path);
	}
	
	public function saveP2S(){
		$path =  'E:/Projects/WEBS/PHP/wls4/file/test/group2privilege.xls';
		$this->m->importExcelWithS($path);
	}	

}
?>