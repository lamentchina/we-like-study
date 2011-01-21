<?php
class install extends wls {

	/**
	 * 初始化数据库表
	 * */
	public function createTables(){
		include_once $this->c->license.'/model/subject.php';
		$obj = new m_subject();
		$obj->create();

		include_once $this->c->license.'/model/knowledge.php';
		$obj = new m_knowledge();
		$obj->create();
		
		include_once $this->c->license.'/model/knowledge/log.php';
		$obj = new m_knowledge_log();
		$obj->create();		

		include_once $this->c->license.'/model/user/group.php';
		$obj = new m_user_group();
		$obj->create();

		include_once $this->c->license.'/model/user.php';
		$obj = new m_user();
		$obj->create();

		include_once $this->c->license.'/model/user/privilege.php';
		$obj = new m_user_privilege();
		$obj->create();

		include_once $this->c->license.'/model/question.php';
		$obj = new m_question();
		$obj->create();

		include_once $this->c->license.'/model/question/log.php';
		$obj = new m_question_log();
		$obj->create();

		include_once $this->c->license.'/model/quiz/paper.php';
		$obj = new m_quiz_paper();
		$obj->create();

		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->create();

		include_once $this->c->license.'/model/quiz/wrong.php';
		$obj = new m_quiz_wrong();
		$obj->create();
	}

	/**
	 * 插入一些测试用数据
	 * */
	public function insertData4Test(){
		$this->createTables();
		
		include_once $this->c->license.'/model/subject.php';
		$obj = new m_subject();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_subject_chinaoffical.xls");

		include_once $this->c->license.'/model/knowledge.php';
		$obj = new m_knowledge();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_knowledge.xls");

		include_once $this->c->license.'/model/user/group.php';
		$obj = new m_user_group();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user_group.xls");
		$obj->importExcelOne(dirname(__FILE__)."../../../../file/test/wls_user_group_admin.xls");
		$obj->importExcelOne(dirname(__FILE__)."../../../../file/test/wls_user_group_user.xls");

		include_once $this->c->license.'/model/user.php';
		$obj = new m_user();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user.xls");


		include_once $this->c->license.'/model/user/privilege.php';
		$obj = new m_user_privilege();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wls_user_privilege.xls");

		//		include_once $this->c->license.'/model/quiz/paper.php';
		//		$obj = new m_quiz_paper();
		//		$obj->importExcel(dirname(__FILE__)."../../../../file/test/wlls_quiz_paper.xls");
	}
	
	public function insertData4TestLog(){
		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log2.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log3.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log4.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log5.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log6.xls");	

		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log2_.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log3_.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log4_.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log5_.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log6_.xls");	

		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log__.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log2__.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log3__.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log4__.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log5__.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log6__.xls");		
	}
	
	public function insertData4TestLogAdmin(){
		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_x_1.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_x_2.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_x_3.xls");		
		
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_1.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_2.xls");	
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_3.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_4.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_5.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_6.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_7.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_ga_8.xls");	

		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_1.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_2.xls");	
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_3.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_4.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_5.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_6.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_7.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_admin_gg_8.xls");		
	}	
	
	public function insertData4TestLogUser1(){
		include_once $this->c->license.'/model/quiz/log.php';
		$obj = new m_quiz_log();
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_x_1.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_x_2.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_x_3.xls");		
		
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_1.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_2.xls");	
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_3.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_4.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_5.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_6.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_7.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_ga_8.xls");	

		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_1.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_2.xls");	
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_3.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_4.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_5.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_6.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_7.xls");
		$obj->importExcel(dirname(__FILE__)."../../../../file/test/quizlog/wls_quiz_log_user1_gg_8.xls");		
	}	
	
	public function main(){
		$html = "
			<form method='post' action='wls.php?controller=install&action=saveconfig'>
			<table>
				<tr>
					<td>dbhost</td>
					<td><input name = 'dbhost' value='localhost' /></td>
				</tr>
				<tr>
					<td>dbname</td>
					<td><input name = 'dbname' value='wls4' /></td>
				</tr>
				<tr>
					<td>dbuser</td>
					<td><input name = 'dbuser' value='admin' /></td>
				</tr>
				<tr>
					<td>dbpwd</td>
					<td><input name = 'dbpwd' value='admin' /></td>
				</tr>
				<tr>
					<td>dbprefix</td>
					<td><input name = 'dbprefix' value='' /></td>
				</tr>																			
			</table>
			<input type='submit' />
			</form>
		";
		echo $html;
	}
	
	public function saveconfig(){
		$_POST['debug']= 0;
		$this->rewirteConfig($_POST);
		
		$this->insertData4Test();
			echo '
		<htm><head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<script language="javascript">
		alert("安装结束");
		window.navigate("wls.php");
		</script>
		</head><body></body></html>
		';
	}
	
	

	/**
	 * 重写配置文件
	 * **/
	public function rewirteConfig($foo=null){
		$file_name = "config.php";
		if(!$file_handle = fopen($file_name,"w")){
			die("config.php cant write");
		}
		$arr = array();
		$cfg = (array)$this->c;
		$keys = array_keys($cfg);
		for($i=0;$i<count($keys);$i++){
			eval('$arr["'.$keys[$i].'"] = $this->c->'.$keys[$i].';');
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