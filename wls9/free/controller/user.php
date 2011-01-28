<?php
class user extends wls{

	private $m = null;

	function user(){
		parent::wls();
		include_once $this->c->license.'/model/user.php';
		$this->m = new m_user();
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

	public function login(){
		include_once $this->c->libsPath."securimage/securimage.php";
		$securimage = new Securimage();
		if ($securimage->check($_POST['CAPTCHA']) == false) {
			echo json_encode(array(
				'msg'=>'CAPTCHA'
				));
		}else{
			if(isset($_SESSION['wls_user'])){
				unset($_SESSION['wls_user']);
				
				
			}			

			session_destroy();
			

			$temp = $this->m->login($_POST['username'],$_POST['password']);
			if($temp==false){
				echo json_encode(array(
					'msg'=>'wrong'
					));
			}else{
				echo json_encode(array(
					'msg'=>'ok'
					));
			}
		}
	}
	
	public function register(){
		include_once $this->c->libsPath."securimage/securimage.php";
		$securimage = new Securimage();
		if ($securimage->check($_POST['CAPTCHA']) == false) {
			echo json_encode(array(
				'msg'=>'CAPTCHA Code Dismatch!'
				));
		}else{
			if(isset($_SESSION['wls_user'])){
				unset($_SESSION['wls_user']);
			}

			session_destroy();
			
			$data = $_POST;
			unset($data['CAPTCHA']);
			$data['money'] = 30;
			
			$id = $this->m->insert($data);
			if($id==0){
				echo json_encode(array(
					'msg'=>'username'
				));
			}else{
				$data = array(
					'id_level_group'=>12
					,'username'=>$_POST['username']
				);
				include_once dirname(__FILE__).'/../model/user/group.php';
				$user_group = new m_user_group();
				$user_group->linkUser($data);
				
				$this->m->login($_POST['username'],$_POST['password']);
				echo json_encode(array(
					'msg'=>'Login OK!'
				));
			}
		}
	}
	
	public function logout(){
		session_start();
		unset($_SESSION['wls_user']);
		session_destroy();
		echo '
	<script language="javascript" type="text/javascript">
           window.location.href="wls.php"; 
    </script>
		';
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
					<form action="wls.php?controller=user&action=saveUpload" method="post"
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
			echo "已经更新";
		}else{
			echo "更新失败";			
		}
	}

	public function viewExport(){
		$file = $this->m->exportExcel();
		echo "<a href='/".$file."'>下载</a>";
	}

	public function delete(){
		if($this->m->delete($_POST['id'])){
			echo '操作成功';
		}else{
			echo '操作失败';
		}
	}

	public function getPrivilege(){
		$username = $_REQUEST['username'];
		include_once dirname(__FILE__).'/../model/user/privilege.php';

		$obj = new m_user_privilege();
		$data = $obj->getListForUser($username);

		include_once dirname(__FILE__).'/../model/tools.php';
		$t = new tools();
		$data = $t->getTreeData(null,$data);

		echo json_encode($data);
	}

	public function getMyMenu(){
		$username = $_REQUEST['username'];

		include_once dirname(__FILE__).'/../model/user/privilege.php';
		$obj = new m_user_privilege();
		$data = $obj->getListForUser($username);
		$data2 = array();
		for($i=0;$i<count($data);$i++){
			
			if($data[$i]['checked']==true || $data[$i]['checked']=='true' || $data[$i]['checked']==1){
				$data[$i]['type'] = 'menu';
				$data2[] = $data[$i];
			}
		}
		
		include_once dirname(__FILE__).'/../model/tools.php';
		$t = new tools();
		$data = $t->getTreeData(null,$data2);

		for($i=0;$i<count($data);$i++){
				
			if($data[$i]['id_level']=='11'){
				include_once dirname(__FILE__).'/../model/subject.php';
				$obj = new m_subject();
				$data_ = $obj->getListForUser($username);
				if(count($data)>0){
					$data__ = array();
					for($ii=0;$ii<count($data_);$ii++){
						$data_[$ii]['type'] = 'subject';
						if($data_[$ii]['checked']==1){
							$data_[$ii]['ismenu'] = 1;
							$data__[] = $data_[$ii];
						}
					}
					$subject = $t->getTreeData(null,$data__);

					$arr = array();
					if(isset($data[$i]['children']) && count($data[$i]['children'])>0){
						$arr = $data[$i]['children'];
					}
					$data[$i]['children'] = $subject;

					if(count($arr)>0){
						$data[$i]['children'][] = array('text'=>'slide');
						for($ii=0;$ii<count($arr);$ii++){
							$data[$i]['children'][] = $arr[$ii];
						}
					}
				}
			}
			if($data[$i]['id_level']=='13'){
				include_once dirname(__FILE__).'/../model/user/group.php';
				$obj = new m_user_group();
				$data_ = $obj->getListForUser($username);
				if(count($data)>0){
					$data__ = array();
					for($ii=0;$ii<count($data_);$ii++){
						$data_[$ii]['type'] = 'group';
						if($data_[$ii]['checked']==1){
							$data_[$ii]['ismenu'] = 1;
							$data__[] = $data_[$ii];
						}
					}
					$subject = $t->getTreeData(null,$data__);

					$arr = array();
					if(isset($data[$i]['children']) && count($data[$i]['children'])>0){
						$arr = $data[$i]['children'];
					}
					$data[$i]['children'] = $subject;

					if(count($arr)>0){
						$data[$i]['children'][] = array('text'=>'slide');
						for($ii=0;$ii<count($arr);$ii++){
							$data[$i]['children'][] = $arr[$ii];
						}
					}
				}
			}
		}

		echo json_encode($data);
	}

	public function getGroup(){
		$username = $_REQUEST['username'];
		include_once dirname(__FILE__).'/../model/user/group.php';

		$obj = new m_user_group();
		$data = $obj->getListForUser($username);

		$data =  $this->t->getTreeData(null,$data);

		echo json_encode($data);
	}

	public function updateGroup(){
		$this->m->updateGroup($_POST['username'],$_POST['privileges']);
	}

	public function getSubject(){
		$username = $_REQUEST['username'];
		include_once dirname(__FILE__).'/../model/subject.php';

		$obj = new m_subject();
		$data = $obj->getListForUser($username);

		for($i=0;$i<count($data);$i++){
			if($data[$i]['checked']!=1)unset($data[$i]);
		}
		echo json_encode(array(
			'data'=>$data
		));
	}
	
	public function getUserInfo(){
		echo json_encode(array(
			 'photo'=>'u1.jpeg'
			,'money'=>100
			,'groups'=>'用户组1,用户组2'
		));
	}
	
	public function getMyQuizLineData(){
		include_once dirname(__FILE__).'/../model/quiz/log.php';

		$user = $this->getMyUser();
		
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
}
?>