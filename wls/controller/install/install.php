<?php
class install{	
	public function main(){
		include_once 'view/install/install.php';
	}
	
	public function step1(){
		$data = array();
		if($_REQUEST['cmstype']!='discuz'){
			$data['message'] = '目前只支持 Discuz进行安装';
		}else{
			if($_REQUEST['host']=='' || 
			$_REQUEST['user']=='' ||
			$_REQUEST['password']=='' ||
			$_REQUEST['db']=='' ||
			$_REQUEST['dbprefix']=='' ){
				$data['message'] = '必要的参数为空';
			}else{
				include_once 'controller/install/discuz.php';	
				$obj = new install_discuz();
				$arr = array(
					'host'=>$_REQUEST['host'],
					'user'=>$_REQUEST['user'],
					'password'=>$_REQUEST['password'],
					'db'=>$_REQUEST['db'],
					'dbprefix'=>$_REQUEST['dbprefix'],
					'loginpath'=>"/logging.php?action=login",
					'cmstype'=>"discuz",
				);
				$obj->rewirteConfig($arr);
				$data['message'] = '已设置配置文件';
				$data['state'] = 'ok';
			}
		}
		echo json_encode($data);
	}
	
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
		
		if($obj->cfg->cmstype=='discuz'){
			include_once 'controller/install/discuz.php';
			$obj2 = new install_discuz();
			
			$obj2->initGroup();
			$obj2->extendUser();
			$obj2->rewirteConfig($obj2->rewrite);
		}		
		
		$data = array(
			'message' =>'数据库表初始化成功',
			'state'=>'ok',
		);
		
		echo json_encode($data);
	}
	
	public function step3(){
		
	}
}
?>