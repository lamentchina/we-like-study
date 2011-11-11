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


/**
 * 考试系统的安装核心文件
 * 这个文件在安装结束之后应该被人为删除或修改,
 * 以防止系统被他人再次恶意修改
 * 
 * 安装文件的controller与其他的controller不一样:
 * 	此controller里面的函数会直接输出HTML,其他控制器都是只输出JSON或XML
 * 因为安装过程比较简单,我直接将V跟C层集合在一起了
 * */
class install extends wls {
	
	/**
	 * 设置系统语言
	 * 系统的语言包放置在 app/language 文件夹内
	 * 用户可以根据需要自己扩展
	 * 如果要修改系统语言,直接修改系统配置文件即可
	 * */
	public function setLanguage(){
		//print_r($this->il8n);exit();
		if($this->cfg->state!='uninstall'){
			echo '
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<title>WLS install</title>
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
					<title>WLS install</title>
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
	
	public function saveLanguage(){
		$this->rewirteConfig($_POST);
		echo '
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>WLS install</title>
		<script language="javascript">
		self.location=("wls.php?controller=install&action=setCMS&temp='.rand(1,100).'");
		</script>
		</head>
		<body>
		</body>
		</html>
		';	
	}	
	
	/**
	 * 设置系统的集成环境
	 * 系统是独立安装,还是集成到其他平台安装
	 * 
	 * 目前能够集成安装的平台有:
	 * 	DiscuzX (1.5 2.0都可以)
	 *  PhpWind
	 *  Joomla (1.5 1.6 都可以)
	 * 
	 * 下一个阶段,我要将系统升级到能够集成到更多的开源CMS中
	 * */
	public function setCMS(){
		$html = "
			<html>
				<head>
					<meta http-equiv='content-type' content='text/html; charset=UTF-8'>
					<title>WLS install</title>
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

	/**
	 * 如果是集成安装到其他平台系统,就需要先读取目标平台的配置文件
	 * 主要是 数据库链接方式
	 * 然后将目标配置文件中的内容写入到本系统的配置文件中
	 * */
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
			//如果是独立安装,非集成安装,先保存前台传来的 cmstype 值并写入到配置文件
			$this->rewirteConfig($_POST);
			//然后再跳到配置设置界面
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
	
	/**
	 * 如果选择了 独立安装 系统,
	 * 需要手动设置 数据库链接参数
	 * */
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
		$_POST['state']= 'installing';//修改系统运行状态.未安装状态为 uninstall
		$this->rewirteConfig($_POST);
		
		$conn = $this->conn();//检查一下用户输入的数据库链接参数是否正确,如果参数错误了的话
		if($conn===false || $conn == null){
			$this->rewirteConfig(array(//修改系统安装状态为 未安装 (因为安装失败)
				'state'=>'uninstall'
			));
			
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
	
	/**
	 * 重写配置文件
	 * 传过来的数组里面如果有配置文件中的项,就修改
	 *
	 * @param $foo 一个数组,其中的KEY必须跟配置文件中数组的KEY一一对应
	 * */
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
	
	/**
	 * 检查系统环境,保证系统安装过程可以顺利通过
	 * 主要是保证部分文件及文件夹的可写性
	 * 
	 * @TODO 应该在安装过程中给部分报错内容提供详细的问题解决方案,不然很多人都不知道下一步该怎么安装
	 * */
	public function checkEnvironment(){
		$paths = array(		
			$this->cfg->filePath.'download/',				//系统产生的一些单独的试卷,答案,成绩单会导出在这里,需要可写
			$this->cfg->filePath.'export/',					//系统批量导出的试卷在这里,需要可写
			$this->cfg->filePath.'log/error.txt',			//一些基于文本的系统日志保存在此,需要可写
			$this->cfg->filePath.'upload/',					//用户自己上传的试卷在这里,需要可写
			'config.php',									//系统的皮肤 背景图 站点名称 能够动态更改,需要可写
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
			
		//如果在安装的时候选择的是集成安装到其他系统中去,就需要先检查目标集成系统配置文件是否可读
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
			<title>WLS install</title>
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
			<title>WLS install</title>
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

	/**
	 * 在系统数据库中创建数据库表
	 * 每个模块都带有数据库建表函数
	 * */
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
		
		/*
		$obj->importExcelWithUser($this->cfg->filePath."/zh-cn/config.xls");		
		$obj->importExcelWithSubject($this->cfg->filePath."/zh-cn/config.xls");
		$obj->importExcelWithAccess($this->cfg->filePath."/zh-cn/config.xls");
		*/

		//$obj->setLeaf();

		$obj = new m_quiz_wrong();
		$obj->create();

		$obj = new m_question_log();
		$obj->create();

		$obj = new m_quiz_log();
		$obj->create();

		$obj = new m_quiz_exam();
		$obj->create();

		//$this->tool->removeDir($this->cfg->filePath."cache/");
		//mkdir($this->cfg->filePath."cache/", 0777);
		//mkdir($this->cfg->filePath."cache/quizlog/", 0777);
		//mkdir($this->cfg->filePath."cache/user2qwiki/", 0777);		

		/*
		$obj = new m_quiz_paper();		
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/account.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/CET4.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer612.xls");		
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer613.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer615.xls");	
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer616.xls");		
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer626.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer930.xls");	
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer931.xls");		
		$obj->importOne($this->cfg->filePath."/zh-cn/paper/chinaofficer932.xls");

		$obj = new m_quiz_exam();
		$obj->importOne($this->cfg->filePath."/zh-cn/exam/exam.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/exam/exam2.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/exam/exam3.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/exam/exam4.xls");
		
		$obj = new m_quiz_log();
		$obj->importOne($this->cfg->filePath."/zh-cn/log/examlog.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/log/examlog2.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/log/examlog3.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/log/log1.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/log/log2.xls");
		$obj->importOne($this->cfg->filePath."/zh-cn/log/log3.xls");
		*/
		
		
		
		//数据库表创建之后,就需要导入配置文件了
		echo '
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>WLS install</title>
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

	/**
	 * 导入系统配置文件
	 * 
	 * 在安装过程中,用户可以自己上传一个配置文件(如果用户对系统比较熟悉的话)
	 * 初次使用此系统的用户,可以选择 运行示例文件 ,来先熟悉一下系统
	 * 不过示例文件中的例子只针对中文系统 
	 *   TODO 我应该提供其他语言的示例程序
	 * 
	 * 就是让系统读取一个EXCEL文件,将其中的配置内容读取出来并插入到系统数据库表中
	 * 这是一个相当耗资源的过程,因为配置文件中的数据比较复杂,比较大
	 * 读取的配置内容包括:
	 * 	用户组
	 *  用户名
	 *  权限
	 *  用户组-权限 的对应关系
	 *  科目
	 *  用户组-科目 的对应关系
	 *  
	 * 国内的很多PHP空间对单个PHP文件的运行时间作了限制,最长15秒的运行时间
	 * 这导致系统在这一步会卡住,系统运行会超时
	 * 
	 * @TODO 我应该改进这个部分的效率
	 * */
	public function importSysConfig(){
		//$max_execution_time = get_cfg_var("max_execution_time");
		$html = '
				<html xmlns="http://www.w3.org/1999/xhtml">		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>WLS install</title>
				</head>
				<body>
					'.$this->il8n['install']['ImNewAboutThis'].'
					<br/>
					<br/>
					<a href="wls.php?controller=install&action=importUser">'.$this->il8n['install']['installDemoData'].'</a>
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

		
		$this->rewirteConfig(array('state'=>'running'));
		
		//		$obj = new m_knowledge();
		//		$obj->importExcel($file);

		$html = "
		<html>
		<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
		<title>WLS install</title>
		</head>
		<body>		
		<a href='wls.php'>".$this->il8n['install']['installDone']."</a>
		</body>
		</html>
		";

		echo $html;
	}
	
	/**
	 * 如果用户选择了 安装示例数据
	 * 这个环节会导入 权限 跟用户组
	 * */		
	public function importAccess(){
		if($this->cfg->language!='zh-cn'){
			die('Language zh-cn supportted only');
		}
		
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
</head>
<body>
".$this->il8n['install']['demoData_access']."
<br/><a href='wls.php?controller=install&action=installPaper'/>Next</a>
</body>";
				
		$file = $this->cfg->filePath.'demodata/'.$this->cfg->language.'/config.xls';
		$obj = new m_user_group();		
		$obj->importExcelWithAccess($file);
		$obj->setLeaf();
		
		$this->rewirteConfig(array('state'=>'running'));
			
		echo $html;		
	}
	
	/**
	 * 如果用户选择了 安装示例数据
	 * 这个环节会导入 用户
	 * */	
	public function importUser(){
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
</head>
<body>
".$this->il8n['install']['demoData_user']."
<br/><a href='wls.php?controller=install&action=importSubject'/>Next</a>
</body>";
		$file = $this->cfg->filePath.'demoData/'.$this->cfg->language.'/config.xls';
		$obj = new m_user_group();		
		$obj->importExcelWithUser($file);
		
		echo $html;		
	}
	
	/**
	 * 如果用户选择了 安装示例数据
	 * 这个环节会导入 科目
	 * */
	public function importSubject(){
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
</head>
<body>
".$this->il8n['install']['demoData_subject']."
<br/><a href='wls.php?controller=install&action=importAccess'/>Next</a>
</body>";		
		$file = $this->cfg->filePath.'demoData/'.$this->cfg->language.'/config.xls';
		$obj = new m_user_group();		
		$obj->importExcelWithSubject($file);
		
	
		echo $html;		
	}
	
	/**
	 * 如果用户选择了 安装示例数据
	 * 这个环节会导入 练习用卷子
	 * 
	 * TODO 这个函数应该被拆分开来,分为前台跟后台两个部分
	 * 目前这个函数在无其他参数情况下运行时(前台没有传递 id)就直接输出HTML
	 * 	HTML 页面是一个有AJAX功能的页面
	 * 它会逐一将文件夹下面的所有卷子一张一张的导入到系统中去
	 * 
	 * 当然,如果试卷少的话,我可以一次性都导入,但是用户电脑的配置就不清楚了,还是用AJAX一张一张导入吧
	 * */
	public function installPaper(){
		$folder = $this->cfg->filePath.'demoData/'.$this->cfg->language.'/paper/';
		$paperObj = new m_quiz_paper();

		if(isset($_REQUEST['id'])){
			$paperObj->importOne($_REQUEST['id']);
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
		url: 'wls.php?controller=install&action=installPaper',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length ){
				alert('".$this->il8n['install']['demoData_paperDone']."');							
				self.location=('wls.php?controller=install&action=installDemoExam');			
			}
			if(msg=='ok'){
				$('#data').text('".$this->il8n['install']['installDemoData']."index:'+index+'/'+ids.length+';  file:'+ids[index]);
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
".demoData_paperDoing."<br/>
<div id='data'><div>
</body>
</html>			
			";
			echo $html;
		}
	}
	
	/**
	 * 如果用户选择了 安装示例数据
	 * 这个环节会导入 示例考试试卷
	 * */
	public function installDemoExam(){
		$folder = $this->cfg->filePath.'demoData/'.$this->cfg->language.'/exam/';
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
				alert('".$this->il8n['install']['demoData_examDone']."');	
		
				self.location=('wls.php?controller=install&action=installDemoQuizLog');			
			}
			if(msg=='ok'){
				$('#data').text('".$this->il8n['install']['installDemoData']."index:'+index+'/'+ids.length+';  file:'+ids[index]);
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
".demoData_examDoing."<br/>
<div id='data'><div>
</body>
</html>			
			";
			echo $html;
		}
	}	
	
	/**
	 * 如果用户选择了 安装示例数据
	 * 这个环节会导入 示例日志数据
	 * 就是模拟几次做题记录跟考试记录
	 * */
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
				alert('".$this->il8n['install']['demoData_logDone']."');	
							
				self.location=('wls.php');//示例数据全部安装完毕,直接跳到首页
			}
			if(msg=='ok'){
				$('#data').text('".$this->il8n['install']['installDemoData']."index:'+index+'/'+ids.length+';  file:'+ids[index]);
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
".demoData_logDoing."<br/>
<div id='data'><div>
</body>
</html>			
			";
			echo $html;
		}
	}
}
?>