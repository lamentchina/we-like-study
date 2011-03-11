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

	public function getOne(){
		$data = $this->m->getList(1,1,array('id'=>$_REQUEST['id']),null,'money,name_subject,title,ids_questions,description,author,score_top,score_avg');
		$data = $data['data'];
		$data = $data[0];
		echo json_encode($data);
	}

	/**
	 * The getMyList functionality is inside the 'getlist'
	 * Why I design in this ? 
	 * Because even when the administrator want to see the list ,
	 * system will also check his subjects access too 
	 * */
	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$search = null;
		if(isset($_REQUEST['search']) && $_REQUEST['search']!='' && trim($_REQUEST['search'])!='' ){
			$_REQUEST['search'] = trim($_REQUEST['search']);
			$search = array();
			
			//All the conditions are sepreated by blank space.
			//Example:
			//Money>9 Author=admin CreatedDate<2011-12-01 CreateDate>2011-01-01
			//It would search in each condition.
			//If there is no '=','>','<' there , Like:
			//keyword1 keyword2
			//Then just search in title
			$arr_conditions = explode(' ',$_REQUEST['search']);
			
			for($i=0;$i<count($arr_conditions);$i++){
				$temp1 = explode("=",$arr_conditions[$i]);
				$temp2 = explode(">",$arr_conditions[$i]);
				$temp3 = explode("<",$arr_conditions[$i]);
				
				if(count($temp1)==1 && count($temp2)==1 && count($temp3)==1){
					//The title contains
					if(!isset($search['title']))$search['title'] = array();
					$search['title'][] = $arr_conditions[$i];
				}else if(count($temp1)==2){
					//Money is equle to
					if($temp1[0]==$this->lang['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('=',$temp1[1]);
					}		
					//Author's name is like
					if($temp1[0]==$this->lang['author']){
						if(!isset($search['author']))$search['author'] = array();
						$search['author'][] = array('=',$temp1[1]);
					}	
					if($temp1[0]==$this->lang['subject']){
						if(!isset($search['subject']))$search['subject'] = array();
						$search['subject'][] = array('=',$temp1[1]);
					}												
				}else if(count($temp2)==2){
					//Money is more expensive than
					if($temp2[0]==$this->lang['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('>',$temp2[1]);
					}									
				}else if(count($temp3)==2){
					//Money is cheapper than
					if($temp3[0]==$this->lang['money']){
						if(!isset($search['money']))$search['money'] = array();
						$search['money'][] = array('<',$temp3[1]);
					}									
				}
			}
//			print_r($search);exit();
		}
		$orderBy = ' order by date_created ';
		if(isset($_REQUEST['sort'])){
			if($_REQUEST['sort']=='money'){
				$orderBy = ' order by money '.$_REQUEST['dir'].' ';
			}
		}
		$data = $this->m->getList($page,$pagesize,$search,$orderBy);
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
				<form action="wls.php?controller=quiz_paper&action=saveImportOne" method="post"
				enctype="multipart/form-data">
					<label for="file">'.$this->lang['ExcelFilePath'].'</label>
					<input type="file" name="file" id="file" />
					<br />
					<input type="submit" name="submit" value="'.$this->lang['submit'].'" />
				</form>
			</body>
		</html>';
	}

	/**
	 * It's impossible to export all the papers from the database to the client in a sudden.
	 * The data is huge, the server will crush.
	 * So , system will export all the papers one by one. 
	 * By ajax
	 * */	
	public function exportAll(){
		//Check the current user if he has this permission or not.
		$userObj = new m_user();
		if($userObj->checkMyaccess("1108",false)==false)return;

		$folder = $this->c->filePath.'export/';
		
		//Part A , pure server actions. Export single one paper to somewhere.
		//It's called by AJAX. The clinet will send a GET['id'] here.
		if(isset($_REQUEST['id'])){
			$this->m->id_paper = $_REQUEST['id'];
			$this->m->exportOne($this->c->filePath.'export/'.$_REQUEST['id'].".xls");
			echo 'ok';
		
		//Part B , Pure HTML out put. There is a AJAX Call in the JS code, the AJAX
		//will call the main export-action-server by sending a id. 
		}else{
			//Get all the paper's ids from the table-wls_quiz_paper 
			$ids = $this->m->exportAll();
			$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->c->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->c->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var ids = [".$ids."];

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		//Export papers one by one , in Ajax request here.
		url: 'wls.php?controller=quiz_paper&action=exportAll',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length )return;
			if(msg=='ok'){
				//Show the current state
				$('#data').text('index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			down();			
			index++;
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

	/**
	 * Not import all the data in a sudden.
	 * By Ajax , import one by one
	 * */
	public function importAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("1109",false)==false)return;

		$folder = $this->c->filePath.'import/';
		if(isset($_REQUEST['id'])){
			$this->m->importOne($_REQUEST['id']);
			echo 'ok';
		}else{
			$filename = $this->t->getAllFiles($folder);
			$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->c->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->c->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($filename).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=quiz_paper&action=importAll',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length )return;
			if(msg=='ok'){
				$('#data').text('index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			down();			
			index++;
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

	public function saveImportOne(){
		if ($_FILES["file"]["error"] > 0){
			$this->error(array('description'=>'wrong c q p'));
			echo 'fail';
		}else{
			$file = $this->c->filePath."upload/upload".rand(1,1000).date('YmdHis').".xls";
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			if($this->m->importOne($file)==false){
			echo "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
</head>
<body>".$this->lang['importFormatWrong']."</body></html>";
			}
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

	public function exportOne(){
		$this->m->id_paper = $_REQUEST['id'];
		$file = $this->m->exportOne();
		echo "<a href='".$this->c->filePath.$file."'>".$this->lang['download']."</a>";
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
		$data = $this->m->checkMyPaper($_POST['answersData'],$_POST['id']);
		echo json_encode($data);
	}

	public function viewQuiz(){
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
			echo "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
</head>
<body>";
			echo $this->lang['accessDenied'];
echo "</body></html>";
			exit();
		}else{
			if($this->m->checkMoney($_REQUEST['id'])==false){
			echo "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
</head>
<body>";
				echo $this->lang['moneyRequest'];
echo "</body></html>";				
				exit();
			}
		}

		$html = "
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
	
<script type=\"text/javascript\" src=\"wls.php?controller=system&action=translateIniToJsClass\"></script>
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