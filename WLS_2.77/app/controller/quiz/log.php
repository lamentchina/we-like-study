<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/log.php';
include_once dirname(__FILE__).'/../../model/user.php';
include_once dirname(__FILE__).'/../../model/question.php';

class quiz_log extends quiz{
	private $m = null;

	function quiz_log(){
		parent::wls();		
		$this->m = new m_quiz_log();
	}

	public function getList(){
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

		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		$data = $this->m->getList($page,$pagesize,array('id_user'=>$user['id']));

		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function importOne(){
		echo '<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->lang['importExcel'].'
					<form action="wls.php?controller=quiz_log&action=saveImportOne" method="post"
					enctype="multipart/form-data">
						<label for="file">Excel :</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
					</form>
				</body>
			</html>';
	}

	public function saveImportOne(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'error'));
		}else{
			$file = $this->cfg->filePath."upload/upload".date('Ymdims').$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importOne($file);
		}
		echo 'success';
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

	public function exportOne(){
		$this->m->id = $_REQUEST['id'];
		$file = $this->m->exportOne();
		
		echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
		</head><body><a href='".$this->cfg->filePath."download/".$file."'>".$this->lang['download']."</a></body></html>";
	}

	public function delete(){
		$this->m->delete($_POST['ids']);
	}

	public function getAnswers(){
		sleep(1);
		$id = $_REQUEST['id'];

		$this->m->id = $id;
		$ques_ = $this->m->getLogAnswers();
		
		if($ques_==false){
			echo 'wrong';
			return;
		}

		$questionObj = new m_question();
		$answers = $questionObj->getAnswers($ques_);

		echo json_encode($answers);
	}
	
	public function getOne(){
		$data = $this->m->getList(1,1,array('id'=>$_REQUEST['id']));
		$data = $data['data'][0];
		echo json_encode($data);
	}
	
	/**
	 * Not import all the data in a sudden.
	 * By Ajax , import one by one
	 * */
	public function importAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("165109",false)==false)return;

		$folder = $this->cfg->filePath.'import/quizlog/';
		if(isset($_REQUEST['id'])){
			$this->m->importOne($_REQUEST['id']);
			echo 'ok';
		}else{
			$filename = $this->t->getAllFiles($folder);
			$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->cfg->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->cfg->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($filename).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=quiz_log&action=importAll',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length ){
				$('#data').text('done')
				return;
			}
			if(msg=='ok'){
				index++;
				$('#data').text('index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
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
	
	public function getRankings(){
		$id_quiz = $_REQUEST['id_quiz'];
		$data = $this->m->getRankings($id_quiz);
		$str = str_replace("\\r"," ",json_encode($data));
		$str = str_replace("\\n"," ",$str);
		$str = str_replace("\\t"," ",$str);
		echo $str;
	}
}
?>