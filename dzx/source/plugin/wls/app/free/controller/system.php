<?php
include_once dirname(__FILE__).'/../model/user.php';

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
	
	public function importAll(){}
	
	public function saveImportAll(){}	
	
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