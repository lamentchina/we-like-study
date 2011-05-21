<?php
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/group.php';
include_once dirname(__FILE__).'/../model/subject.php';

class system extends wls{
	public function translateIniToJsClass(){
		$cacheFilePath = $this->cfg->filePath.'cache/il8n.json';
		if(file_exists($cacheFilePath)){	
			$content = file( $cacheFilePath );		
			$content = implode("\n", $content);
			echo $content;	
		}else{
			$data['il8n'] = $this->il8n;
			$str = json_encode($data);
			$handle=fopen($cacheFilePath,"a");
			fwrite($handle,$str);
			fclose($handle);
			
			echo $str;
		}
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
			$file = $this->cfg->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
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
			eval('$arr["'.$keys[$i].'"] = $this->cfg->'.$keys[$i].';');
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

		$cfg = (array)$this->cfg;
		$keys = array_keys($cfg);
		$arr = array();
		for($i=0;$i<count($keys);$i++){
			eval('$arr["'.$keys[$i].'"] = $this->cfg->'.$keys[$i].';');
		}
		unset($arr['dbname']);
		unset($arr['dbhost']);
		unset($arr['dbuser']);
		unset($arr['dbpwd']);

		echo json_encode($arr);
	}
	
	/**
	 * The core function of qWikiOffice's server side
	 * I modify the default qWikiOffice's App.js , 
	 * change the 'connection' property.
	 * 
	 * @see qWikiOffice/client/App.js
	 * */
	public function getQwikiModule(){
		header("Content-type: text/html; charset=utf-8");
		$actionid = explode("_",$_REQUEST['moduleId']);
		$actionid = $actionid[1];
		$subjects = new m_subject();
		$subjects = $subjects->getListsWithIdLevelKey();
		if(strlen($actionid)>2 && substr($actionid,0,2)=='11'){
			$str = "
		class_".$actionid." = Ext.extend(Ext.app.Module, {
		   id: 'id_".$actionid."',
		
		   init : function(){
		
		   },
			
			createWindow : function(){
		        var desktop = this.app.getDesktop();
		        var win = desktop.getWindow(this.id);
		    	var winWidth = desktop.getWinWidth() / 1.1;
				
		        if(!win){			
		            win = desktop.createWindow({
		                id: this.id,
		                title: il8n.quiz.paper+'(".$subjects[substr($actionid,2)].")',
		                width: winWidth,
		                height: 530,
						iconCls : 'icon_paper_16_16',
						iconClsGhostBar : 'icon_paper_32_32',
		                layout: 'fit',
						listeners : {
							'show':function(x){
								var c = document.getElementById('spList_".$actionid."');   
								c.src =  \"subject/paperGrid.html?id_level=".substr($actionid,2)."\";
							}
						},
						html : \"<iframe id='spList_".$actionid."' width='100%' height='500' frameborder='no' border='0' marginwidth='0' marginheight='0' />\"
		            });
		        }
		        win.show();
		    }
		});	
			";
			echo $str;
			exit();
		}
		if(file_exists( dirname(__FILE__).'/../view/qWikiModules/'.$actionid.'.js' )){
			include_once dirname(__FILE__).'/../view/qWikiModules/'.$actionid.'.js';
		}else{
			$str = "
		class_".$actionid." = Ext.extend(Ext.app.Module, {
		   id: 'id_".$actionid."',
		
		   init : function(){
		
		   },
			
			createWindow : function(){
		        var desktop = this.app.getDesktop();
		        var win = desktop.getWindow(this.id);
		    	var winWidth = desktop.getWinWidth() / 2.1;
				var winHeight = desktop.getWinHeight() / 2.1;
				
		        if(!win){			
		            win = desktop.createWindow({
		                id: this.id,
		                title: il8n.unDone,
		                width: winWidth,
		                height: winHeight,
						modal:true,
		                layout: 'fit',
		                items:[ new Ext.BoxComponent({
			                    html:'//TODO '
			            })]
		            });
		        }
		        win.show();
		    }
		});	
			";
			echo $str;
		}
	}
}
?>