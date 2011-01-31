<?php
include_once dirname(__FILE__).'/../quiz.php';

class quiz_log extends quiz{
	private $m = null;

	function quiz_log(){
		parent::wls();
		include_once $this->c->license.'/model/quiz/log.php';
		$this->m = new m_quiz_log();
	}

	public function jsonList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $this->m->getList($page,$pagesize);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}
	
	public function getMyList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		
		$user = $this->getMyUser();		
		$data = $this->m->getList($page,$pagesize,array('id_user'=>$user['id']));
		
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function viewUpload(){
		echo '<html>		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->lang['importExcel'].'
					<form action="wls.php?controller=quiz_log&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">Excel :</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>';
	}

	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'文件上传错误'));
		}else{
			$file = $this->c->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importExcel($file);
		}
	}

	public function saveUpdate(){
		$data = array(
			'id'=>$_POST['id'],
		$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			echo "success";
		}else{
			echo "fail";			
		}
	}

	public function viewExport(){
		$this->m->id = $_REQUEST['id'];
		$file = $this->m->exportExcel();
		echo "<a href='/".$file."'>".$this->lang['download']."</a>";
	}

	public function getOne(){
		$id = $_POST['id'];
		$user = $this->getMyUser();

		$data = $this->m->getList(1,1,array('id'=>$id));
		$data = $data['data'][0];
		
		$data['application'] = $this->t->formatApplicationType($data['application']);
		
		echo json_encode($data);
	}

	public function delete(){
		$this->m->delete($_POST['id']);
	}

	public function getAnswers(){
		sleep(2);
		$id = $_POST['id'];

		$this->m->id = $id;
		$ques_ = $this->m->getLogAnswers();

		//得到答案并直接输出,没有任何的数据库写入
		$answers = $this->m->getAnswers($ques_);
		echo json_encode($answers);		
	}

	/**
	 * 查看这篇日志的详细经过
	 * 重现当日做这张测验卷的结果
	 * */
	public function viewOne(){
		$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->libsPath."star-rating/jquery.rating.css\" />
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
	src=\"".$this->c->libsPath."star-rating/jquery.rating.pack.js\"></script>		
	
<script type=\"text/javascript\" src=\"".$this->c->license."/view/il8n.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz/log.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/big.js\"></script>


<script type=\"text/javascript\">
var quiz_log;
Ext.onReady(function(){
	quiz_log = new wls.quiz.log();
	
	quiz_log.id = ".$_REQUEST['id'].";
	quiz_log.naming = 'quiz_log';
	quiz_log.initLayout();
	quiz_log.ajaxIds(\"quiz_log.ajaxQuestions('quiz_log.addQuestions();quiz_log.submit();');\");
});
</script>
</head>
<body>

</body>
</html>
		";
		echo $html;
	}
}
?>