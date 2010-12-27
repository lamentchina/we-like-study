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
	public function main(){
		include_once 'view/install/install.php';
	}
	
	/**
	 * 根据前台传过来的配置参数,重写配置文件
	 * 必须保证 config.php 可写
	 * */
	public function step1(){
		$data = array();
		
		if($_REQUEST['host']=='' || 
			$_REQUEST['user']=='' ||
			$_REQUEST['password']=='' ||
			$_REQUEST['db']=='' ||
			$_REQUEST['dbprefix']=='' ){
				$data['message'] = '必要的参数为空';
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
			$data['message'] = '已设置配置文件';
			$data['state'] = 'ok';			
		}		
		echo json_encode($data);
	}
	
	/**
	 * 初始化数据库表结构
	 * 并和CMS系统做桥接
	 * */
	public function step2(){
		include_once 'controller/quiz/type.php';
		$obj = new quiz_type();
		$obj->initTable();		
		$obj->initTestData();	
		
		include_once 'controller/quiz/paper/paper.php';
		$obj = new quiz_paper();
		$obj->initTable();
		
		include_once 'controller/question/question.php';
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
			$this->rewirteConfig($obj2->rewrite);
		}else if($obj->cfg->cmstype=='discuzx'){
			include_once 'controller/install/discuzx.php';
			$obj2 = new install_discuzx();
			
			$obj2->extendUser();
			$obj2->initNav();
			$this->rewirteConfig($obj2->rewrite);
		}		
		
		$data = array(
			'message' =>'数据库表初始化成功',
			'state'=>'ok',
		);
		
		echo json_encode($data);
	}
	
	/**
	 * 插件安装完成之后,导入一些试卷做测试用的
	 * */
	public function step3(){
		
	}
	
	/**
	 * 重写配置文件
	 * **/
	public function rewirteConfig($foo=null){
		if($this->cfg->debug!=1){
			$this->hackAttack();
			return;
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