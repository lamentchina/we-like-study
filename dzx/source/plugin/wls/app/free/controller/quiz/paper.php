<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';
include_once dirname(__FILE__).'/../../model/user.php';

class quiz_paper extends quiz{
	private $m = null;

	function quiz_paper(){
		parent::wls();
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
		echo '<html>		
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			<body>
				'.$this->lang['importExcel'].'
				<form action="wls.php?controller=quiz_paper&action=saveUpload" method="post"
				enctype="multipart/form-data">
					<label for="file">'.$this->lang['ExcelFilePath'].'</label>
					<input type="file" name="file" id="file" />
					<br />
					<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
				</form>
			</body>
		</html>';
	}

	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'wrong c q p'));
			echo 'fail';
		}else{
			$file = $this->c->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->importExcel($file);
			echo 'success';
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
		echo "<a href='".$this->c->filePath.$file."'>".$this->lang['download']."</a>";
	}

	public function getOne(){
		$id = $_POST['id'];
		echo json_encode($this->m->getList(1,1,array('id'=>$id)));
	}

	public function delete(){
		$this->m->delete($_POST['id']);
	}

	/**
	 * If the current user is only a guest, just output the answers and do nothing.
	 * If the current user is system's user, it would do a lot of logs, as:
	 * quiz log
	 * questions log
	 * knowledge log
	 * My wrongs book
	 * */
	public function getAnswers(){
		//If the server-side's speed is very fast , 
		//faster than the client-side's JavaScript parsing , there would be an error
		//So let the server sleep 2 senconds whatever
		sleep(2);

		$ques = $_POST['answersData'];
		$ques_ = array();
		$id_question = '';
		for($i=0;$i<count($ques);$i++){
			$ques_[$ques[$i]['id']] = $ques[$i]['answer'];
			$id_question .= $ques[$i]['id'].",";
		}		
		$id_question = substr($id_question,0,strlen($id_question)-1);

		//It's written in controller/quiz.php
		$answers = $this->m->getAnswers($ques_);
		$json = json_encode($answers);
		
		//Just out put the answsers if the current user is guest, and do nothing
		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		if($user['username']=='guest'){
			echo $json;
			return;	
		}		

		$id = $_POST['id'];
		$item = $this->m->getList(1,1,array('id'=>$id),null,'id,id_level_subject');
		$item = $item['data'][0];
		
		//Do quiz log. 
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
		
		include_once $this->c->license.'/model/user.php';
		$userObj = new m_user();
		$user = $userObj->getMyInfo();

		include_once dirname(__FILE__).'/../../model/quiz/wrong.php';
		$wrongObj = new m_quiz_wrong();
		include_once $this->c->license.'/model/question/log.php';
		$quesLogObj = new m_question_log();		
		include_once $this->c->license.'/model/knowledge/log.php';
		$knowledgeLogObj = new m_knowledge_log();				
		for($i=0;$i<count($answers);$i++){
			unset($answers[$i]['description']);
			$knowledgeLog = array(
				 'date_created'=>date('Y-m-d H:00:00')
				,'date_slide'=>3600
				,'id_user'=>$user['id']
				,'id_level_user_group'=>$user['group']
				,'id_question'=>$answers[$i]['id']
			);
			
			if($answers[$i]['myAnswer']=='I_DONT_KNOW'){
				$answers[$i]['correct'] = 2;
				$count_giveup ++;
			}else if($answers[$i]['myAnswer']==$answers[$i]['answer']){
				$answers[$i]['correct'] = 1;
				$count_right ++;
				$mycent += $answers[$i]['cent'];
				
				//Set the knowledge log
				$knowledgeLog['count_right'] = 1;				
//				$knowledgeLogObj->insert($knowledgeLog);				
			}else if( ((int)$answers[$i]['type']) == 7 && (((int)$answers[$i]['id_parent'])!=0) && 
				($answers[$i]['myAnswer']==$answers[$i]['option2'] ||
				$answers[$i]['myAnswer']==$answers[$i]['option3'] ||
				$answers[$i]['myAnswer']==$answers[$i]['option4'])  ){

				$answers[$i]['correct'] = 1;
				$count_right ++;
				$mycent += $answers[$i]['cent'];
				
				//Set the knowledge log
				$knowledgeLog['count_right'] = 1;				
//				$knowledgeLogObj->insert($knowledgeLog);				
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

					$wrongObj->insert($wrong);
					$answers[$i]['correct'] = 0;
					$count_wrong ++;
					
					//Set the knowledge log
					$knowledgeLog['count_wrong'] = 1;
//					$knowledgeLogObj->insert($knowledgeLog);
				}
			}		
			
			//Set the question log
			$cent += $answers[$i]['cent'];
			$answers[$i]['id_question'] = $answers[$i]['id'];
			$answers[$i]['date_created'] = date('Y-m-d H:i:s');
			unset($answers[$i]['id']);
			$answers[$i]['id_quiz_log'] = $id_quiz_log;
			$answers[$i]['id_quiz_paper'] = $id;
			$answers[$i]['id_level_subject'] = $item['id_level_subject'];

			$quesLogData = array(
				 'date_created'=>date('Y-m-d H:i:s')
				,'id_user'=>$user['id']
				,'id_level_user_group'=>$user['group']
				,'id_question'=>$answers[$i]['id']
				,'id_question_parent'=>$answers[$i]['id_parent']
			);
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
		$userObj = new m_user();
		
		//IE6 is special
		if( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6') == true ){
			$userObj->id = $_REQUEST['uid'];
		}else{
			$me = $userObj->getMyInfo();
		    $userObj->id = $me['id'];
		}		
		$foo = $userObj->checkMyaccess('1107');
		
		if($foo==false){
			echo "access request";
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
<!--  
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/adapter/ext/ext-base.js\"></script>	
-->
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."ext_3_2_1/ext-all.js\"></script>
<script type=\"text/javascript\"
	src=\"".$this->c->libsPath."star-rating/jquery.rating.pack.js\"></script>		
	
<script type=\"text/javascript\" src=\"wls.php?controller=user&action=translateIniToJsClass\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/wls.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/quiz/paper.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/choice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/check.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/multichoice.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/big.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/blank.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/mixed.js\"></script>
<script type=\"text/javascript\" src=\"".$this->c->license."/view/question/depict.js\"></script>

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