<?php
include_once dirname(__FILE__).'/../quiz.php';

class quiz_paper extends quiz{
	private $m = null;

	function quiz_paper(){
		parent::wls();
		include_once $this->c->license.'/model/quiz/paper.php';
		$this->m = new m_quiz_paper();
	}

	public function jsonList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$search = null;
		if(isset($_REQUEST['search']) && $_REQUEST['search']!=''){
			$search = array(
				'title'=>$_REQUEST['search']
			);
		}
		$data = $this->m->getList($page,$pagesize,$search,' order by date_created ');
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function viewUpload(){
		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					导入EXCEL
					<form action="wls.php?controller=quiz_paper&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">EXCEL文件:</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="提交" />
					</form>
				</body>
			</html>		
		';
	}

	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'文件上传错误'));
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
			//			$this->m->create();
			$this->m->importExcel(dirname(__FILE__)."/../../../../file/upload/upload".date('Ymdims').$_FILES["file"]["name"]);
		}

	}

	public function saveUpdate(){
		$data = array(
			'id'=>$_POST['id'],
		$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			echo "已经更新";
		}else{
			echo "更新失败";			
		}
	}

	public function viewExport(){
		$this->m->id = $_REQUEST['id'];
		$file = $this->m->exportExcel();
		echo "<a href='../../../../".$file."'>下载</a>";
	}

	public function getOne(){
		$id = $_POST['id'];
		echo json_encode($this->m->getList(1,1,array('id'=>$id)));
	}

	public function delete(){
		$this->m->delete($_POST['id']);
	}

	public function getAnswers(){
		sleep(2);

		$ques = $_POST['answersData'];
		$ques_ = array();
		$id_question = '';
		for($i=0;$i<count($ques);$i++){
			$ques_[$ques[$i]['id']] = $ques[$i]['answer'];
			$id_question .= $ques[$i]['id'].",";
		}

		$answers = $this->m->getAnswers($ques_);
		$json = json_encode($answers);

		$id_question = substr($id_question,0,strlen($id_question)-1);

		$id = $_POST['id'];
		$item = $this->m->getList(1,1,array('id'=>$id),null,'id,id_level_subject');
		$item = $item['data'][0];
		
		//插入试卷日志
		include_once $this->c->license.'/model/quiz/log.php';
		$quizLogObj = new m_quiz_log();
		$data = array(
			'date_created'=>date('Y-m-d H:i:s'),
			'id_question'=>$id_question,
			'id_level_subject'=>$item['id_level_subject'],
			'id_quiz_paper'=>$id,
			'time_start'=>$_POST['time']['start'],
			'time_stop'=>$_POST['time']['stop'],
			'time_used'=>$_POST['time']['used'],
		);
		$id_quiz_log = $quizLogObj->insert($data);

		$count_right = 0;
		$count_wrong = 0;
		$count_giveup = 0;
		$cent = 0;
		$mycent = 0;
		$count_total = count($answers);
		$user = $this->getMyUser();

		include_once dirname(__FILE__).'/../../model/quiz/wrong.php';
		$wrongObj = new m_quiz_wrong();
		include_once $this->c->license.'/model/question/log.php';
		$quesLogObj = new m_question_log();		
		include_once $this->c->license.'/model/knowledge/log.php';
		$knowledgeLogObj = new m_knowledge_log();				
		for($i=0;$i<count($answers);$i++){
			unset($answers[$i]['description']);
			$knowledgeLog = array(
				 'date_created'=>date('Y-m-d H:i:s')
				,'date_slide'=>3600
				,'id_user'=>$user['id']
				,'id_level_user_group'=>1
				,'id_question'=>$answers[$i]['id']
			);

			
			if($answers[$i]['myAnswer']=='I_DONT_KNOW'){
				$answers[$i]['correct'] = 2;
				$count_giveup ++;
			}else if($answers[$i]['myAnswer']==$answers[$i]['answer']){
				$answers[$i]['correct'] = 1;
				$count_right ++;
				$mycent += $answers[$i]['cent'];
				
				$knowledgeLog['count_right'] = 1;
				//写入知识点记录
				$knowledgeLogObj->insert($knowledgeLog);				
			}else{
				if($answers[$i]['type']!=5){
					$obj_->id_question = $answers[$i]['id'];
					$obj_->id_user = $user['id'];
					$wrong = array(
						'id_question' => $answers[$i]['id'],
						'id_quiz_paper' => $id,
						'id_level_subject' => $item['id_level_subject'],
						'id_user'=>$user['id'],
						'date_created'=>date('Y-m-d H:i:s'),
					);
					//写入错题本
					$wrongObj->insert($wrong);
					$answers[$i]['correct'] = 0;
					$count_wrong ++;
					
					$knowledgeLog['count_wrong'] = 1;
					//写入知识点记录
					$knowledgeLogObj->insert($knowledgeLog);
				}
			}		
			
			$cent += $answers[$i]['cent'];
			$answers[$i]['id_question'] = $answers[$i]['id'];
			$answers[$i]['date_created'] = date('Y-m-d H:i:s');
			unset($answers[$i]['id']);
			$answers[$i]['id_quiz_log'] = $id_quiz_log;
			$answers[$i]['id_quiz_paper'] = $id;
			$answers[$i]['id_level_subject'] = $item['id_level_subject'];
					
			//写入题目日志
			$quesLogObj->insert($answers[$i]);
		}

		$data = array(
			'id'=>$id_quiz_log,
			'count_right'=>$count_right,
			'count_wrong'=>$count_wrong,
			'count_giveup'=>$count_giveup,
			'count_total'=>$count_total,
			'proportion'=>0,
			'cent'=>$cent,
			'mycent'=>$mycent,
		);
		if(($count_right+$count_wrong)>0){
			$data['proportion'] = $count_right/($count_right+$count_wrong);
		}
		
		//跟新测验日志
		$quizLogObj->update($data);

		$this->m->id = $id;
		$this->m->cumulative('count_used');
		$this->m->mycent = $mycent;
		//更新卷子的最高分情况
		$this->m->cumulative('score');
		
		echo $json;
	}

	public function viewOne(){
		include_once $this->c->license.'/model/user.php';
		$obj_ = new m_user();

		if( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6') == false )
		{

		}
		else
		{
		    $obj_->id = $_REQUEST['uid'];
		}
		
		$foo = $obj_->checkMyPrivilege('1107');
		
		if($foo==false){
			echo "privilege request";
			exit();
		}else{
			if($this->m->checkMoney($_REQUEST['id'])==false){
				echo "money request";
				exit();
			}
		}
		
		$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"../libs/ext_3_2_1/resources/css/ext-all.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"../libs/star-rating/jquery.rating.css\" />
<link rel=\"stylesheet\" type=\"text/css\"
	href=\"".$this->c->license."/view/wls.css\" />	
<script type=\"text/javascript\"
	src=\"../libs/jquery-1.4.2.js\"></script>	
<script type=\"text/javascript\"
	src=\"../libs/ext_3_2_1/adapter/jquery/ext-jquery-adapter.js\"></script>
<script type=\"text/javascript\"
	src=\"../libs/jqueryextend.js\"></script>	
<!--  
<script type=\"text/javascript\"
	src=\"../libs/ext_3_2_1/adapter/ext/ext-base.js\"></script>	
-->
<script type=\"text/javascript\"
	src=\"../libs/ext_3_2_1/ext-all.js\"></script>
<script type=\"text/javascript\"
	src=\"../libs/star-rating/jquery.rating.pack.js\"></script>		
	
<script type=\"text/javascript\" src=\"".$this->c->license."/view/il8n.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz/paper.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/big.js\"></script>

<script type=\"text/javascript\">
var quiz_paper;
Ext.onReady(function(){
	quiz_paper = new wls.quiz.paper();
	
	quiz_paper.id = ".$_REQUEST['id'].";
	quiz_paper.naming = 'quiz_paper';
	quiz_paper.initLayout();
	quiz_paper.ajaxIds(\"quiz_paper.ajaxQuestions('quiz_paper.addQuestions()');\");
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