<?php
/**
 * 插件安装入口
 *
 * 安装结束之后,此文件也不应该被删掉
 * */
class install extends wls{
	
	/**
	 * 访问主入口,就是打开一个页面
	 * */
	public function setConfig(){
		$html = '
		<form action="wls.php?controller=install&action=saveConfig" method="post">
		<table>
		<tr>
			<td>目标程序:</td>
			<td>
				<select name="cmstype">
					<option value="null" selected="selected">请选择</option>
					<option value="discuzx">DiscuzX 1.5</option>
					<option value="discuz">Discuz 7.2</option>
				</select>
			</td>		
		</tr>
		<tr>
			<td>服务器地址</td>
			<td>
				<input name="host" value="localhost" />
			</td>		
		</tr>			
		<tr>
			<td>数据库名称</td>
			<td>
				<input name="db" />
			</td>		
		</tr>	
		<tr>
			<td>数据库用户名</td>
			<td>
				<input name="user" />
			</td>		
		</tr>	
		<tr>
			<td>数据库密码</td>
			<td>
				<input name="password" />
			</td>		
		</tr>	
		<tr>	
			<td>数据库表前缀</td>
			<td>
				<input name="dbprefix" />
			</td>		
		</tr>		
		<tr>	
			<td colspan="2">
				<button type="submit" >提交</button>
			</td>		
		</tr>								
	</table>
	</form>
		';
		echo $html;
	}

	/**
	 * 根据前台传过来的配置参数,重写配置文件
	 * 必须保证 config.php 可写
	 * */
	public function saveConfig(){
		$data = array();
		if($_REQUEST['host']=='' ||
		$_REQUEST['user']=='' ||
		$_REQUEST['password']=='' ||
		$_REQUEST['db']=='' ||
		$_REQUEST['dbprefix']=='' ){
			echo '
				<htm><head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<script language="javascript">
				alert("缺少必要的参数");
				window.navigate("wls.php?controller=install&action=setConfig");
				</script>
				</head><body></body></html>
				';
		}else{
			$arr = array(
				'host'=>$_REQUEST['host'],
				'user'=>$_REQUEST['user'],
				'password'=>$_REQUEST['password'],
				'db'=>$_REQUEST['db'],
				'dbprefix'=>$_REQUEST['dbprefix'],
				'loginpath'=>"/logging.php?action=login",
				'cmstype'=>$_REQUEST['cmstype'],
			);
			$this->rewirteConfig($arr);
			echo '
				<htm><head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<script language="javascript">
				alert("参数配置结束,开始初始化数据库表结构");
				window.location.href=("wls.php?controller=install&action=initTables");
				</script>
				</head><body></body></html>
				';	
		}
	}

	/**
	 * 初始化数据库表结构
	 * 并和CMS系统做桥接
	 * */
	public function initTables(){
		include_once 'controller/quiz/type.php';
		$obj = new quiz_type();
		$obj->initTable();
		
		include_once 'controller/quiz/type/record.php';
		$obj = new quiz_type_record();
		$obj->initTable();

		include_once 'controller/quiz/paper.php';
		$obj = new quiz_paper();
		$obj->initTable();

		include_once 'controller/question.php';
		$obj = new question();
		$obj->initTable();

		include_once 'controller/quiz/wrongs.php';
		$obj = new quiz_wrongs();
		$obj->initTable();

		include_once 'controller/question/record.php';
		$obj = new question_record();
		$obj->initTable();

		include_once 'controller/quiz/record.php';
		$obj = new quiz_record();
		$obj->initTable();

		//根据CMS系统的类型做不同的桥接
		if($obj->cfg->cmstype=='discuz'){
			include_once 'controller/install/discuz.php';
			$obj2 = new install_discuz();
				
			$obj2->initGroup();
			$obj2->extendUser();
			$obj2->initNav();
			$obj2->rewrite['debug']=0;
			$this->rewirteConfig($obj2->rewrite);
		}else if($obj->cfg->cmstype=='discuzx'){
			include_once 'controller/install/discuzx.php';
			$obj2 = new install_discuzx();
				
			$obj2->extendUser();
			$obj2->initNav();
			$obj2->rewrite['debug']=0;
			$this->rewirteConfig($obj2->rewrite);
		}
		echo '
			<htm><head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<script language="javascript">
			alert("数据库表结构初始化结束,安装结束");
			//window.navigate("wls.php?controller=install&action=importXML");
			</script>
			</head><body></body></html>
			';	
	}

	/**
	 * 重写配置文件
	 * **/
	public function rewirteConfig($foo=null){
		if($this->cfg->debug!=1){
			$this->hackAttack();
			exit();
		}
		$file_name = "config.php";
		if(!$file_handle = fopen($file_name,"w")){
			die("不能打開$file_name");
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

		$content = "
<?php
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
?>
		";
		fwrite($file_handle,$content);
		fclose($file_handle);
	}
}
?>