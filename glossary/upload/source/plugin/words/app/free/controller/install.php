<?php
include_once dirname(__FILE__).'/../model/subject.php';
include_once dirname(__FILE__).'/../model/subject/log.php';
include_once dirname(__FILE__).'/../model/user.php';
include_once dirname(__FILE__).'/../model/user/access.php';
include_once dirname(__FILE__).'/../model/user/group.php';
include_once dirname(__FILE__).'/../model/question.php';
include_once dirname(__FILE__).'/../model/quiz.php';
include_once dirname(__FILE__).'/../model/quiz/exam.php';
include_once dirname(__FILE__).'/../model/quiz/log.php';
include_once dirname(__FILE__).'/../model/quiz/paper.php';
include_once dirname(__FILE__).'/../model/quiz/wrong.php';
include_once dirname(__FILE__).'/../model/glossary.php';
include_once dirname(__FILE__).'/../model/glossary/logs.php';
include_once dirname(__FILE__).'/../model/glossary/levels.php';
include_once dirname(__FILE__).'/../model/glossary/levels/logs.php';

class install extends wls {

	public function createTables(){
		$obj = new m_question();
		$obj->create();

		$obj = new m_quiz();
		$obj->create();

		$obj = new m_quiz_paper();
		$obj->create();

		$obj = new m_subject();
		$obj->create();

		$obj = new m_subject_log();
		$obj->create();

		$obj = new m_user();
		$obj->create();

		$obj = new m_user_access();
		$obj->create();

		$obj = new m_user_group();
		$obj->create();

		$obj->importExcelWithUser($this->cfg->filePath."demodata/zh-cn/config.xls");
		$obj->importExcelWithSubject($this->cfg->filePath."demodata/zh-cn/config.xls");
		$obj->importExcelWithAccess($this->cfg->filePath."demodata/zh-cn/config.xls");

		$obj->setLeaf();

		$obj = new m_quiz_wrong();
		$obj->create();

		$obj = new m_question_log();
		$obj->create();

		$obj = new m_quiz_log();
		$obj->create();

		$obj = new m_quiz_exam();
		$obj->create();


		$this->tool->removeDir($this->cfg->filePath."cache/quizlog/");
		mkdir($this->cfg->filePath."cache/quizlog/", 0777);

		
//		$obj = new m_user_group();

//		$obj->importExcelWithUser($this->cfg->filePath."demodata/config.xls");
//		$obj->importExcelWithSubject($this->cfg->filePath."demodata/config.xls");
//		$obj->importExcelWithAccess($this->cfg->filePath."demodata/config.xls");

		$obj = new m_quiz_paper();
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/paper/account.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/paper/CET4.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/paper/chinaofficer.xls");
		


		$obj = new m_quiz_exam();
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/exam/exam.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/exam/exam2.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/exam/exam3.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/exam/exam4.xls");
		
		$obj = new m_quiz_log();
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/log/examlog.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/log/examlog2.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/log/examlog3.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/log/log1.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/log/log2.xls");
		$obj->importOne($this->cfg->filePath."demodata/zh-cn/log/log3.xls");
		
		$obj = new m_glossary();
		$obj->create();
		
		$obj = new m_glossary_logs();
		$obj->create();		
		
		$obj = new m_glossary_levels();
		$obj->create();		

		$obj = new m_glossary_levels_logs();
		$obj->create();			
		exit();
		
		/*
		$obj = new m_user_group();
		$obj->importExcelWithUser($this->cfg->filePath."demodata/config.xls");
		$obj->importExcelWithSubject($this->cfg->filePath."demodata/config.xls");
		$obj->importExcelWithAccess($this->cfg->filePath."demodata/config.xls");
		*/
		
		$obj = new m_glossary();
		$obj->importAll($this->cfg->filePath."demodata/GLOSSARY_CET4.xls");
		
		$obj2 = new m_glossary_levels();
		$obj2->phpexcel = $obj->phpexcel;
		$obj2->importAll($this->cfg->filePath."demodata/GLOSSARY_CET4.xls");
		$obj2->caculateLevelWordCounts();
		
		$obj = new m_glossary_levels_logs();
		$obj->sendFreeLevelsToEveryone();		
		exit();
		
		echo '
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<script language="javascript">
		alert("'.$this->il8n['install']['createdTables'].'");
		self.location=("wls.php?controller=install&action=importSysConfig");
		</script>
		</head>
		<body>
		</body>
		</html>
		';			
	}

	public function importSysConfig(){
		$html = '
				<html xmlns="http://www.w3.org/1999/xhtml">		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->il8n['install']['ImNewAboutThis'].'
					<br/>
					<br/>
					<a href="wls.php?controller=install&action=installDemoData">'.$this->il8n['install']['installDemoData'].'</a>
					<hr style="height:5px;color:gray"/>
					'.$this->il8n['install']['ImFamiliarAboutThis'].'
					<br/><br/>
					'.$this->il8n['install']['importSysConfig'].',<a href="../file/test/config_'.$this->cfg->language.'.xls">'.$this->il8n['install']['seeExampleFile'].'</a>
					<br/>
					<br/>
					<form action="wls.php?controller=install&action=saveSysConfig" 
					method="post"
					enctype="multipart/form-data">
						<label for="file">'.$this->il8n['install']['ExcelFilePath'].':</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->il8n['install']['submit'].'" />
					</form>					
				</body>
			</html>				
		';
		echo $html;
	}

	public function saveSysConfig(){
		$file = $this->cfg->filePath."upload/upload".date('Ymdims').".xls";
		move_uploaded_file($_FILES["file"]["tmp_name"],$file);

		$obj = new m_user_group();
		$obj->importExcelWithAccess($file);
		$obj->importExcelWithSubject($file);
		$obj->importExcelWithUser($file);

		//		$obj = new m_knowledge();
		//		$obj->importExcel($file);

		$html = "
		<html>
		<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
		</head>
		<body>		
		<a href='wls.php'>".$this->il8n['install']['installDone']."</a>
		</body>
		</html>
		";

		echo $html;
	}

	public function installDemoData(){
		if($this->cfg->language!='zh-cn'){
			die('Language zh-cn supportted only');
		}
		
		$userObj = new m_user();
		$userObj->login('admin');

		$folder = $this->cfg->filePath.'demodata/'.$this->cfg->language.'/paper/';
		$paperObj = new m_quiz_paper();

		if(isset($_REQUEST['id'])){
			$paperObj->importOne($_REQUEST['id']);
			echo 'ok';
		}else{
			$file = $this->cfg->filePath.'demodata/'.$this->cfg->language.'/config.xls';
			$obj = new m_user_group();

			$obj->importExcelWithAccess($file);
			$obj->importExcelWithUser($file);
			$obj->importExcelWithSubject($file);

			$files = $this->tool->getAllFiles($folder);
			//			print_r($filename);exit();
			$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->cfg->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->cfg->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($files).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install&action=installDemoData',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length ){
				alert('".$this->il8n['install']['installDone']."');	
				$('#data').text('".$this->il8n['install']['success'].','.$this->il8n['install']['pageIsJumpping']."');	
							
				self.location=('wls.php?controller=install&action=installDemoExam');			
			}
			if(msg=='ok'){
				$('#data').text('".$this->il8n['install']['installDemoPapers']."index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			index++;
			down();			
			
		}
	});
}
down();
</script>
</head>
<body>
<div id='data'><div>
</body>
</html>			
			";
			echo $html;
		}
	}
	
	public function installDemoExam(){
		$folder = $this->cfg->filePath.'demodata/'.$this->cfg->language.'/exam/';
		$obj = new m_quiz_exam();

		if(isset($_REQUEST['id'])){
			$obj->importOne($_REQUEST['id']);
			echo 'ok';
		}else{
			$files = $this->tool->getAllFiles($folder);
			$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->cfg->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->cfg->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($files).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install&action=installDemoExam',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length ){
				alert('".$this->il8n['install']['installDone']."');	
				$('#data').text('".$this->il8n['install']['success'].','.$this->il8n['install']['pageIsJumpping']."');	
							
				self.location=('wls.php?controller=install&action=installDemoQuizLog');			
			}
			if(msg=='ok'){
				$('#data').text('".$this->il8n['install']['installDemoPapers']."index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			index++;
			down();	
		}
	});
}
down();
</script>
</head>
<body>
<div id='data'><div>
</body>
</html>			
			";
			echo $html;
		}
	}	
	
	public function installDemoQuizLog(){
		$folder = $this->cfg->filePath.'demodata/'.$this->cfg->language.'/log/';
		$obj = new m_quiz_log();

		if(isset($_REQUEST['id'])){
			$obj->importOne($_REQUEST['id']);
			echo 'ok';
		}else{

			$files = $this->tool->getAllFiles($folder);
			//			print_r($filename);exit();
			$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->cfg->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->cfg->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($files).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install&action=installDemoQuizLog',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length ){
				alert('".$this->il8n['install']['installDone']."');	
				$('#data').text('".$this->il8n['install']['success'].','.$this->il8n['install']['pageIsJumpping']."');	
							
				self.location=('wls.php');			
			}
			if(msg=='ok'){
				$('#data').text('".$this->il8n['install']['installDemoPapers']."index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			index++;
			down();	
		}
	});
}
down();
</script>
</head>
<body>
<div id='data'><div>
</body>
</html>			
			";
			echo $html;
		}
	}

	public function setLanguage(){
		//print_r($this->il8n);exit();
		if($this->cfg->state!='uninstall'){
			echo '
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			</head>
			<body>
			'.$this->il8n['install']['checkEnvironment'].'
			</body>
			</html>
			';	
			exit();
		}

		$html = "
			<html>
				<head>
					<meta http-equiv='content-type' content='text/html; charset=UTF-8'>
				</head>
			<body>
			<form method='post' action='wls.php?controller=install&action=saveLanguage'>
			<table width='100%'>			
				<tr>
					<td width='40%'>Language(语言,語系,日本語,taal,gjuhën,언어):</td>
					<td width='40%'>
						<select name='language' style='width:90%'>							
							<option value='zh-cn'>简体中文</option>
							<option value='en'>English</option>
							<!--
							<option value='zh-tw'>繁體中文</option>
							<option value='jp'>日本語</option>
							-->
						</select>
					</td>
					<td width='20%'><input type='submit' value='&nbsp;&nbsp;Next&nbsp;&nbsp;' /></td>
				</tr>																												
			</table>			
			</form>
			</body>
			</html>
		";
		echo $html;
	}

	public function checkEnvironment(){
		$paths = array(
		/*
			$this->cfg->filePath.'download/',
			$this->cfg->filePath.'export/',
			$this->cfg->filePath.'import/',
			$this->cfg->filePath.'log/error.txt',
			$this->cfg->filePath.'upload/',
			*/
			'config.php',
		);
		$console = $this->il8n['install']['checkEnvironment'].'<br/><br/>';
		$error = 0;
		for($i=0;$i<count($paths);$i++){
			if($this->tool->iswriteable($paths[$i])){
				$console .= $paths[$i].' &nbsp;&nbsp;&nbsp; OK <br/>';
			}else{
				$error = 1;
				$console .= $paths[$i].' &nbsp;&nbsp;&nbsp; Not Writable! <br/>';
			}
		}
			
		if($this->cfg->cmstype=='DiscuzX'){
			if(!file_exists(dirname(__FILE__).'/../../../../../../config/config_global.php')){
				$error = 1;
				$console .= $this->il8n['install']['integrationError'];
			}
		}
		if($this->cfg->cmstype=='Joomla'){
			if(!file_exists( dirname(__FILE__).'/../../../../../configuration.php' )){
				$error = 1;
				$console .= $this->il8n['install']['integrationError'];
			}
		}
		if($this->cfg->cmstype=='PhpWind'){
			if(!file_exists( dirname(__FILE__).'/../../../../../data/sql_config.php' )){
				$error = 1;
				$console .= $this->il8n['install']['integrationError'];
			}
		}
		if($error==0){
			echo '
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			</head>
			<body>
			'.$console.'
			<br/><br/><br/>
			<a href="wls.php?controller=install&action=createTables">'.$this->il8n['install']['createTables'].'</a>
			</body>
			</html>
			';	
		}else{
			echo '
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			</head>
			<body>
			'.$console.'
			<br/><br/><br/>
			'.$this->il8n['install']['environmentError'].'
			</body>
			</html>
			';	
		}
	}

	public function saveLanguage(){
		$this->rewirteConfig($_POST);
		echo '
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<script language="javascript">
		self.location=("wls.php?controller=install&action=setCMS&temp='.rand(1,100).'");
		</script>
		</head>
		<body>
		</body>
		</html>
		';	
	}

	public function setCMS(){
		$html = "
			<html>
				<head>
					<meta http-equiv='content-type' content='text/html; charset=UTF-8'>
				</head>
			<body>
			".$this->il8n['install']['loginAsAdminFirst']."
			<form method='post' action='wls.php?controller=install&action=saveCMS'>
			<table width='90%'>			
				<tr>
					<td style='width:20%'  >".$this->il8n['install']['cmstype']."</td>
					<td style='width:60%' >
						<select name='cmstype' style='width:90%' >
							<option value=''>".$this->il8n['install']['NOCMS']."</option>
							<option value='DiscuzX'>DiscuzX 1.5</option>
							<!--
							<option value='Discuz'>Discuz</option>
							-->
							<option value='Joomla'>Joomla</option>
							<!--Why did joomla 1.6 totally changed it's user module?-->
							<option value='Joomla16'>Joomla1.6</option>
							<option value='PhpWind'>PhpWind</option>
						</select>
					</td>
				</tr>	
				<tr>
					<td>".$this->il8n['install']['license']."</td>
					<td>
						<select name='license' style='width:90%' >
							<option value='free'>".$this->il8n['install']['freelicense']."</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>".$this->il8n['install']['siteName']."</td>
					<td><input name = 'siteName' value='We_Like_Study' style='width:90%' /></td>
				</tr>																														
			</table>
			<br/><br/>
			<input type='submit' value='".$this->il8n['install']['submit']."' />
			</form>
			</body>
			</html>
		";
		echo $html;
	}

	public function saveCMS(){
		if($_POST['cmstype']=='DiscuzX'){
			if(!file_exists(dirname(__FILE__).'/../../../../../../config/config_global.php')){
				$html = "<html><head><meta http-equiv='content-type' content='text/html; charset=UTF-8'></head>
			<body>".$this->il8n['install']['environmentError']."</body></html>";
				echo $html;
			}
			include_once dirname(__FILE__).'/../../../../../../config/config_global.php';
			$config = array(
				'dbname'=>$_config['db'][1]['dbname'],
				'dbhost'=>$_config['db'][1]['dbhost'],
				'dbuser'=>$_config['db'][1]['dbuser'],
				'dbpwd'=>$_config['db'][1]['dbpw'],
				'dbprefix'=>$_config['db'][1]['tablepre'],
				'cmstype'=>'DiscuzX',
				'state'=>'running',
			);
			$this->rewirteConfig($config);
		}else if($_POST['cmstype']=='Joomla' || $_POST['cmstype']=='Joomla16'){
			if(!file_exists(dirname(__FILE__).'/../../../../../configuration.php') ){
				$html = "<html><head><meta http-equiv='content-type' content='text/html; charset=UTF-8'></head>
			<body>".$this->il8n['install']['environmentError']."</body></html>";
				echo $html;
			}
			include_once dirname(__FILE__).'/../../../../../configuration.php';
			$jconfig = new JConfig();

			$config = array(
				'dbname'=>$jconfig->db,
				'dbhost'=>$jconfig->host,
				'dbuser'=>$jconfig->user,
				'dbpwd'=>$jconfig->password,
				'dbprefix'=>$jconfig->dbprefix,
				'cmstype'=>'Joomla',
				'state'=>'running',
			);
			if($_POST['cmstype']=='Joomla16'){
				$config['cmstype'] = 'Joomla16';
			}
			$this->rewirteConfig($config);
		}else if($_POST['cmstype']=='PhpWind'){
			if(!file_exists(dirname(__FILE__).'/../../../../../data/sql_config.php') ){
				$html = "<html><head><meta http-equiv='content-type' content='text/html; charset=UTF-8'></head>
			<body>".$this->il8n['install']['environmentError']."</body></html>";
				echo $html;
			}
			include_once dirname(__FILE__).'/../../../../../data/sql_config.php';
			$config = array(
				'dbname'=>$dbname,
				'dbhost'=>$dbhost,
				'dbuser'=>$dbuser,
				'dbpwd'=>$dbpw,
				'dbprefix'=>$PW,
				'cmstype'=>'PhpWind',
				'state'=>'running',
			);
			$this->rewirteConfig($config);
		}else{
			$this->rewirteConfig($_POST);
			$this->setConfig();
			return;
		}
		echo '
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<script language="javascript">
		alert("'.$this->il8n['install']['settedConfig'].'");
		self.location=("wls.php?controller=install&action=checkEnvironment");
		</script>
		</head>
		<body>
		</body>
		</html>
		';	
	}

	public function setConfig(){
		$html = "
			<html>
				<head>
					<meta http-equiv='content-type' content='text/html; charset=UTF-8'>
				</head>
			<body>
			<form method='post' action='wls.php?controller=install&action=saveconfig'>
			<table>
				<tr>
					<td colspan='2'><b>".$this->il8n['install']['setConfig']."</b></td>
				</tr>
				<tr>
					<td colspan='2'>&nbsp;</td>
				</tr>								
				<tr>
					<td>".$this->il8n['install']['dbhost']."</td>
					<td><input name = 'dbhost' value='localhost' /></td>
				</tr>				
				<tr>
					<td>".$this->il8n['install']['dbname']."</td>
					<td><input name = 'dbname' value='wls' /></td>
				</tr>
				<tr>
					<td>".$this->il8n['install']['dbuser']."</td>
					<td><input name = 'dbuser' value='admin' /></td>
				</tr>
				<tr>
					<td>".$this->il8n['install']['dbpwd']."</td>
					<td><input name = 'dbpwd' value='admin' /></td>
				</tr>
				<tr>
					<td>".$this->il8n['install']['dbprefix']."</td>
					<td><input name = 'dbprefix' value='w_' /></td>
				</tr>	
				<tr>
					<td colspan='2'>&nbsp;</td>
				</tr>																															
			</table>
			<input type='submit' value='".$this->il8n['install']['submit']."' />
			</form>
			</body>
			</html>
		";
		echo $html;
	}

	public function saveConfig(){
		$_POST['state']= 'running';
		$this->rewirteConfig($_POST);

		$conn = $this->conn();
		if($conn===false){
			echo '
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			</head>
			<body>
			'.$this->il8n['install']['dbConfigWrong'].'
			</body>
			</html>
			';	
			exit();
		}
		echo '
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<script language="javascript">
		alert("'.$this->il8n['install']['settedConfig'].'");
		self.location=("wls.php?controller=install&action=checkEnvironment");
		</script>
		</head>
		<body>
		</body>
		</html>
		';		
	}

	public function rewirteConfig($foo=null){
		$file_name = "config.php";
		if(!$file_handle = fopen($file_name,"w")){
			echo '<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		</head>
		<body>
		'.$this->il8n['install']['configFileError'].'
		</body>
		</html>
		';
		}
		$arr = array();
		$cfg = (array)$this->cfg;
		$keys = array_keys($cfg);
		for($i=0;$i<count($keys);$i++){
			eval('$arr["'.$keys[$i].'"] = $this->cfg->'.$keys[$i].';');
		}

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
}
?>