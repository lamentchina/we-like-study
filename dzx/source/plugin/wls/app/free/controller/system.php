<?php
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/group.php';

class system extends wls{
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
			$obj->importExcelWithG($file);
			$obj->importExcelWithS($file);
			$obj->importExcelWithU($file);
			echo 'success';
		}
	}

	public function exportAll(){}

	public function saveUpdate(){
		$userObj = new m_user();
		if($userObj->checkMyaccess(1906,false)==false)die('access denied');

		if(isset($_POST['dbname']) ||
		isset($_POST['dbhost']) ||
		isset($_POST['dbuser']) ||
		isset($_POST['dbpwd'])
		){
			$this->error("Attack!");
			die("You want to modify system's core info !");
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
}
?>