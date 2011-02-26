<?php
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/group.php';
include_once dirname(__FILE__).'/../model/user/access.php';
include_once dirname(__FILE__).'/../model/subject.php';
include_once dirname(__FILE__).'/../model/quiz/log.php';

class user extends wls{
	private $m = null;

	function user(){
		parent::wls();
		$this->m = new m_user();
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

	public function login(){
		include_once $this->c->libsPath."securimage/securimage.php";
		$securimage = new Securimage();
		if ($securimage->check($_POST['CAPTCHA']) == false) {
			echo json_encode(array(
				'msg'=>$this->lang['CAPTCHAFail']
			,'state'=>'fail'
			));
		}else{
			if(isset($_SESSION['wls_user'])){
				unset($_SESSION['wls_user']);
				session_unregister('wls_user');
			}

			$data = $this->m->login($_POST['username'],$_POST['password']);

			if($data['username']=='guest'){
				echo json_encode(array(
					'msg'=>$this->lang['loginFail']
				,'state'=>'fail'
				));
			}else{
				echo json_encode(array(
					'msg'=>$this->lang['loginSuccess']
				,'state'=>'success'
				));
			}
		}
	}

	public function add(){
		if(isset($_POST['CAPTCHA'])){
			include_once $this->c->libsPath."securimage/securimage.php";
			$securimage = new Securimage();
			if ($securimage->check($_POST['CAPTCHA']) == false) {
				echo json_encode(array(
					'msg'=>'CAPTCHA Code Dismatch!'
					));
				exit();
			}			
		}

		$id = $this->m->insert(array(
			 'username'=>$_POST['username']
			,'password'=>$_POST['password']
			,'money'=>'30'
		));
		if($id==0){
			echo json_encode(array(
						'msg'=>'username'
						));
		}else{
			$data = array(
						'id_level_group'=>11
						,'username'=>$_POST['username']
			);
				
			$user_group = new m_user_group();
			$user_group->linkUser($data);
				
			$this->m->login($_POST['username'],$_POST['password']);
			echo json_encode(array(
						'msg'=>'success'
						));
		}
	}

	public function logout(){
		session_start();
		if(isset($_SESSION['wls_user'])){
			unset($_SESSION['wls_user']);
			session_unregister('wls_user');
		}
		echo '
	<script language="javascript" type="text/javascript">
           window.location.href="wls.php"; 
    </script>
		';
	}

	public function importAll(){
		$html = '<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					<form action="wls.php?controller=user&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">Excel :</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>		
		';
		echo $html;
	}

	public function saveImportAll(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],$this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
			$this->m->importExcel($this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}
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

	public function exportAll(){
		$file = $this->m->exportExcel();
		echo "<a href='".$this->c->filePath.$file."'>".$this->lang['download']."</a>";
	}

	public function delete(){
		if($this->m->delete($_POST['id'])){
			echo 'success';
		}else{
			echo 'fail';
		}
	}

	public function getAccessTree(){
		$username = $_REQUEST['username'];
		$obj = new m_user_access();
		$data = $obj->getListForUser($username);
		$data = $this->t->getTreeData(null,$data);
		echo json_encode($data);
	}

	public function getGroupTree(){
		$username = $_REQUEST['username'];

		$obj = new m_user_group();
		$data = $obj->getListForUser($username);

		$data =  $this->t->getTreeData(null,$data);

		echo json_encode($data);
	}

	public function updateGroup(){
		$this->m->updateGroup($_POST['username'],$_POST['accesss']);
	}

	public function getSubject(){
		$username = $_REQUEST['username'];

		$obj = new m_subject();
		$data = $obj->getListForUser($username);

		for($i=0;$i<count($data);$i++){
			if($data[$i]['checked']!=1)unset($data[$i]);
		}
		echo json_encode(array(
			'data'=>array_values($data)
		));
	}

	public function getSubjectTree(){
		$username = $_REQUEST['username'];

		$obj = new m_subject();
		$data = $obj->getListForUser($username);
		$data = $this->t->getTreeData(null,$data);

		echo json_encode($data);
	}

	public function getMyQuizLineData(){
		$userObj = new m_user();
		$user = $userObj->getMyInfo();

		$obj = new m_quiz_log();
		$search = array(
			'id_user'=>$user['id']
		);
		if(isset($_REQUEST['id_level_subject'])){
			$search['id_level_subject'] = $_REQUEST['id_level_subject'];
		}
		$data = $obj->getList(null,null,$search);
		$data = $data['data'];
		$arr = array();
		for($i=0;$i<count($data);$i++){
			$arr[] = array(
				 'index' =>$i+1
			,'proportion'=>$data[$i]['proportion']*100
			,'id'=>$data[$i]['id']
			);
		}

		echo json_encode(array(
			'data'=>$arr
		));
	}

	public function getMyMenu4Desktop(){}


}
?>