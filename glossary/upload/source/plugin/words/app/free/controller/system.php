<?php
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/group.php';

class system extends wls{
	public function translateIniToJsClass(){
		$data['il8n'] = $this->il8n;
		echo json_encode($data);
	}

	public function importAll(){
		echo '<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			<body>
				'.$this->lang['importExcel'].'
				<form action="wls.php?controller=system&action=saveImportAll" method="post"
				enctype="multipart/form-data">
					<label for="file">'.$this->lang['ExcelFilePath'].'</label>
					<input type="file" name="file" id="file" />
					<br />
					<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
				</form>
			</body>
		</html>';
	}

	public function saveImportAll(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'wrong c q p'));
			echo 'fail';
		}else{
			$file = $this->c->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$obj = new m_user_group();
			$obj->importExcelWithAccess($file);
			$obj->importExcelWithSubject($file);
			$obj->importExcelWithUser($file);
			
			echo 'success';
		}
	}

	//TODO
	public function exportAll(){}

	public function saveUpdate(){
		$userObj = new m_user();
		if($userObj->checkMyaccess(1906,false)==false)die('Access denied');

		if(isset($_POST['dbname']) ||
		isset($_POST['dbhost']) ||
		isset($_POST['dbuser']) ||
		isset($_POST['dbpwd'])
		){
			$this->error("Attack!");
			die("You hacker! Want to modify system's core info ?");
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
		$userObj = new m_user();
		if($userObj->checkMyaccess(1906,false)==false)die('access denied');

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
	
	public function viewModifySystemSettings(){
		echo "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/".$this->c->theme."\" />	

<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->license."/view/modules.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->license."/view/wls.css\" />			
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."jquery-1.4.2.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/adapter/jquery/ext-jquery-adapter.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."jqueryextend.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/ext-all.js\"></script>	
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/ext-lang-zh_CN.js\"></script>

<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/user.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/system.js\"></script>
<script type=\"text/javascript\">
var me = new wls.user();
";
if(!isset($_SESSION)){
	session_start();
}
echo "me.myUser.access = '".$_SESSION['wls_user']['access']."';\n";
echo "me.myUser.access2 = ".json_encode($_SESSION['wls_user']['access2']).";\n";
echo "me.myUser.group = '".$_SESSION['wls_user']['group']."';\n";
echo "me.myUser.subject = '".$_SESSION['wls_user']['subject']."';\n";
echo "me.myUser.username = '".$_SESSION['wls_user']['username']."';\n";
echo "me.myUser.money = '".$_SESSION['wls_user']['money']."';\n";
echo "me.myUser.id = '".$_SESSION['wls_user']['id']."';\n";
echo "me.myUser.photo = '".$_SESSION['wls_user']['photo']."';\n";
echo "
var obj;
Ext.onReady(function(){
	obj = new wls.system();
	var obj2 = obj.modifySystemSettings('qd_w_q_p_l')
	obj2.render(Ext.getBody());
});
</script>
</head>
<body style='BORDER-RIGHT: 0px; BORDER-TOP: 0px; BORDER-LEFT: 0px; BORDER-BOTTOM: 0px' scroll='no'>

</body>
</html>
		";
	}
}
?>