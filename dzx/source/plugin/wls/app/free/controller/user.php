<?php
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/group.php';
include_once dirname(__FILE__).'/../model/user/access.php';
include_once dirname(__FILE__).'/../model/tools.php';
include_once dirname(__FILE__).'/../model/subject.php';
include_once dirname(__FILE__).'/../model/quiz/log.php';

class user extends wls{
	private $m = null;

	function user(){
		parent::wls();		
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

	public function viewUpload(){
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
			echo "success";
		}else{
			echo "fail";			
		}
	}

	public function viewExport(){
		$file = $this->m->exportExcel();
		echo "<a href='/".$file."'>".$this->lang['download']."</a>";
	}

	public function delete(){
		if($this->m->delete($_POST['id'])){
			echo 'success';
		}else{
			echo 'fail';
		}
	}

	public function getTreeaccess(){
		$username = $_REQUEST['username'];
		$obj = new m_user_access();
		$data = $obj->getListForUser($username);
		$data = $this->t->getTreeData(null,$data);
		echo json_encode($data);
	}

	public function getMyMenu(){
		$username = $_REQUEST['username'];		
		$obj = new m_user_access();
		$data = $obj->getListForUser($username);
		$data2 = array();
		for($i=0;$i<count($data);$i++){			
			if($data[$i]['checked']==true || $data[$i]['checked']=='true' || $data[$i]['checked']==1){
				$data[$i]['type'] = 'menu';
				$data2[] = $data[$i];
			}
		}		
		
		$t = new tools();
		$data = $t->getTreeData(null,$data2);

		for($i=0;$i<count($data);$i++){				
			if($data[$i]['id_level']=='11'){				
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
	
	public function getTreeSubject(){
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
	
	public function translateIniToJsClass(){
		$js = 'var il8n = {';
		$keys = array_keys($this->lang);
		for($i=0;$i<count($this->lang);$i++ ){
			$js .= "\n".$keys[$i].':"'.$this->lang[$keys[$i]].'",';
		}		
		$js = substr($js,0,strlen($js)-1);
		$js .= "\n }";
		header("Content-type: text/html; charset=utf-8");
		echo $js;
	}
	
	public function rewriteConfig(){
		if($this->m->checkMyaccess(1906,false)==false)exit();
		
		if(isset($_POST['dbname']) ||
			isset($_POST['dbhost']) ||
			isset($_POST['dbuser']) ||
			isset($_POST['dbpwd']) 
		){
			$this->error("Attack!");
			exit();
		}		
		
		$file_name = "config.php";
		if(!$file_handle = fopen($file_name,"w")){
			die($this->lang['configFileError']);
		}
		$arr = array();
		$cfg = (array)$this->c;
		$keys = array_keys($cfg);
		for($i=0;$i<count($keys);$i++){
			eval('$arr["'.$keys[$i].'"] = $this->c->'.$keys[$i].';');
		}
		$foo = $_POST;
		if($foo!=null){
			$keys = array_keys($foo);
			for($i=0;$i<count($foo);$i++){
				$arr[$keys[$i]] = $foo[$keys[$i]];
			}
		}

		$content = "<?php
class wlsconfig{
";
		$keys = array_keys($arr);
		for($i=0;$i<count($arr);$i++){
			$content .= "
			public \$".$keys[$i]." = '".$arr[$keys[$i]]."';";
		}
		$content.=
"
}
?>";
		fwrite($file_handle,$content);
		fclose($file_handle);
	}
	
	public function getConfig(){
		if($this->m->checkMyaccess(1906,false)==false)exit();
		
		$cfg = (array)$this->c;
		$keys = array_keys($cfg);
		$arr = array();
		for($i=0;$i<count($keys);$i++){
			eval('$arr["'.$keys[$i].'"] = $this->c->'.$keys[$i].';');
		}
		unset($arr['dbname']);
		unset($arr['dbhost']);
		unset($arr['dbuser']);
		unset($arr['dbpwd']);
		
		echo json_encode($arr);
	}
}
?>