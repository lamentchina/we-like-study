<?php
include_once dirname(__FILE__).'/../quiz.php';

class quiz_paper extends quiz{
	private $m = null;
	
	function quiz_paper(){
		parent::wls();
		include_once $this->c->license.'/model/quiz/paper.php';
		$this->m = new m_quiz_paper();
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
					<form action="wls.php?controller=quiz_paper&action=saveUpload" method="post"
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
			$this->error(array('description'=>'文件上传错误'));
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
//			$this->m->create();
			$this->m->importExcel(dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
		
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
	
	public function getOne(){		
		$id = $_POST['id'];
		echo json_encode($this->m->getList(1,1,array('id'=>$id)));
	}
	
	public function delete(){
		$this->m->delete($_POST['id']);		
	}
	
	public function getAnswers(){
		$ques = $_POST['answersData'];		
		$ques_ = array();
		$id_question = '';	
		for($i=0;$i<count($ques);$i++){
			$ques_[$ques[$i]['id']] = $ques[$i]['answer'];
			$id_question .= $ques[$i]['answer']+",";
		}		
		$id_question = substr($id_question,0,strlen($id_question)-1);
		
		$id = $_POST['id'];
		
		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$data = array(
			'date_created'=>date('Y-m-d i:m:s'),
			'id_user'=>1,
			'id_usergroup'=>1,
			'id_question'=>$id_question,
			'id_subject'=>1,
			'id_quiz_paper'=>$id,
		);
		$obj->insert();
				

		echo json_encode($this->m->getAnswers($data_));
	}	
}
?>