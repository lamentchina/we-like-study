<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../question.php';
include_once dirname(__FILE__).'/../question/log.php';
include_once dirname(__FILE__)."/../user.php";
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../quiz/log.php';

include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_quiz_paper extends m_quiz implements dbtable,fileLoad{
	public $phpexcel;
	public $id_paper = null;
	public $questions = null;
	public $paper = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_paper (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function delete($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_quiz_paper where id in (".$ids.") ";
		try {
			mysql_query($sql,$conn);
			$sql = "delete from ".$pfx."wls_question where id_quiz_paper in (".$ids.") ";
			try{
				mysql_query($sql,$conn);
				return true;
			}
			catch (Exception $ex2){
				return false;
			}
		}
		catch (Exception $ex){
			return false;
		}
	}

	public function update($data){

		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_quiz_paper set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
	}

	public function create(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz_paper;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_paper(
				 id int primary key auto_increment	
				,id_quiz int default 0
				
				,money int default 0				
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function importAll($path){}

	public function exportAll($path=null){}

	public function importOne($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['paper']);
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$imagePath = "";
		$quizData = array();
		$paperData = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['title']){
				$quizData['title'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['money']){
				$paperData['money'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['author']){
				$quizData['author'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['imagePath']){
				$imagePath = $currentSheet->getCell($i."2")->getValue();
				$quizData['imagePath'] = $imagePath;
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['subject']){
				$quizData['id_level_subject'] = $currentSheet->getCell($i."2")->getValue();
				$sql_ = "select name from ".$pfx."wls_subject where id_level = '".$quizData['id_level_subject']."'; ";
				$res = mysql_query($sql_,$conn);
				$temp = mysql_fetch_assoc($res);
				$quizData['name_subject'] = $temp['name'];
			}
		}

		$quizObj = new m_quiz();
		$quizData['id'] = $quizObj->insert($quizData);
		$quizObj->id_quiz = $quizData['id'];
		$quizObj->quizData = $quizData;
		$quizObj->importOne($this->phpexcel);

		$paperData['id_quiz'] = $quizData['id'];
		$this->insert($paperData);
	}

	public function paperToExcel($paper,$questions){
		$objPHPExcel = new PHPExcel();
		$data = $paper;

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['paper']);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $this->lang['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $this->lang['author']);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $data['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $data['id_level_subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $data['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $data['creator']);

		$data = $questions;
		//处理题目
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['question']);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['index']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['belongto']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->lang['Qes_Type']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $this->lang['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('E2', $this->lang['answer']);
		$objPHPExcel->getActiveSheet()->setCellValue('F2', $this->lang['cent']);
		$objPHPExcel->getActiveSheet()->setCellValue('G2', $this->lang['option'].'A');
		$objPHPExcel->getActiveSheet()->setCellValue('H2', $this->lang['option'].'B');
		$objPHPExcel->getActiveSheet()->setCellValue('I2', $this->lang['option'].'C');
		$objPHPExcel->getActiveSheet()->setCellValue('J2', $this->lang['option'].'D');
		$objPHPExcel->getActiveSheet()->setCellValue('K2', $this->lang['option'].'E');
		$objPHPExcel->getActiveSheet()->setCellValue('L2', $this->lang['option'].'F');
		$objPHPExcel->getActiveSheet()->setCellValue('M2', $this->lang['option'].'G');
		$objPHPExcel->getActiveSheet()->setCellValue('N2', $this->lang['optionlength']);
		$objPHPExcel->getActiveSheet()->setCellValue('O2', $this->lang['ques_description']);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(5);
		$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
		$objPHPExcel->getActiveSheet()->setCellValue('P2', $this->lang['listenningFile']);
		$objPHPExcel->getActiveSheet()->setCellValue('Q2', $this->lang['count_used']);
		$objPHPExcel->getActiveSheet()->setCellValue('R2', $this->lang['count_right']);
		$objPHPExcel->getActiveSheet()->setCellValue('S2', $this->lang['count_wrong']);
		$objPHPExcel->getActiveSheet()->setCellValue('T2', $this->lang['count_giveup']);
		$objPHPExcel->getActiveSheet()->setCellValue('U2', $this->lang['difficulty']);
		$objPHPExcel->getActiveSheet()->setCellValue('V2', $this->lang['markingmethod']);
		$objPHPExcel->getActiveSheet()->setCellValue('W2', $this->lang['ids_level_knowledge']);
		for($i=1;$i<=23;$i++){
			$objPHPExcel->getActiveSheet()->setCellValue(chr($i+64).'1', $i);
		}

		$index = 3;
		for($i=0;$i<count($data);$i++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['id_parent']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->t->formatQuesType($data[$i]['type']));
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['title']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['answer']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, $data[$i]['cent']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$index, $data[$i]['option1']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$index, $data[$i]['option2']);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$index, $data[$i]['option3']);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$index, $data[$i]['option4']);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$index, $data[$i]['option5']);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$index, $data[$i]['option6']);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$index, $data[$i]['option7']);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$index, $data[$i]['optionlength']);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$index, $data[$i]['description']);
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$index, $data[$i]['path_listen']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$index, $data[$i]['count_used']);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$index, $data[$i]['count_right']);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$index, $data[$i]['count_wrong']);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$index, $data[$i]['count_giveup']);
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$index, $data[$i]['difficulty']);
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$index,$this->t->formatMarkingMethod($data[$i]['markingmethod']));
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$index, $data[$i]['ids_level_knowledge']);

			$index ++;
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('E2');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'E2:E'.(count($data)+1));

		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$path = $this->c->filePath.$file;

		$objWriter->save($path);
		return $file;
	}

	public function exportOne($path=null){
		$data = $this->getList(1,1,array('id'=>$this->id));
		$paper = $data['data'][0];

		$ques = new m_question();
		$data = $ques->getList(1,200,array('id_quiz_paper'=>$this->id));
		$questions = $data['data'];

		return $this->paperToExcel($paper,$questions);
	}



	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id'){
					$where .= " and ".$pfx."wls_quiz_paper.id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_level_subject'){
					$where .= " and id_level_subject in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='title'){
					$where .= " and title like '%".$search[$keys[$i]]."%' ";
				}
			}
		}
		if($orderby==null)$orderby = " order by ".$pfx."wls_quiz_paper.id ";
		$sql = "select ".$columns." from ".$pfx."wls_quiz_paper join ".$pfx."wls_quiz on ".$pfx."wls_quiz_paper.id_quiz = ".$pfx."wls_quiz.id ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		$index = 1;
		while($temp = mysql_fetch_assoc($res)){
			$temp['index'] = $index;
			$index ++;
			if(isset($temp['date_created'])){
				$temp['date_created2'] = $this->t->getTimeDif($temp['date_created']);
			}
			$arr[] = $temp;
		}

		$sql2 = "select count(".$pfx."wls_quiz_paper.id) as total from ".$pfx."wls_quiz_paper join ".$pfx."wls_quiz on ".$pfx."wls_quiz_paper.id_quiz = ".$pfx."wls_quiz.id   ".$where;
		$res = mysql_query($sql2,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		return array(
			'page'=>$page,
			'data'=>$arr,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
		);
	}

	public function exportQuiz($type){}

	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}

	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}

	public function getQuizIds(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "select questions,id from ".$pfx."wls_quiz_paper where id = ".$this->id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		return $temp['questions'];
	}

	public function checkMoney($id){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "select money,id from ".$pfx."wls_quiz_paper where id= ".$id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);


		$userObj = new m_user();
		$user = $userObj->getMyInfo();

		if($user['money']>$temp['money']){
			$sql = "update ".$pfx."wls_user set money = money - ".$temp['money']." where id = ".$user['id'];

			if(!isset($_SESSION)){
				session_start();
			}
			$_SESSION['wls_user']['money'] -= $temp['money'];
			mysql_query($sql,$conn);

			if($this->c->cmstype!=''){
				$obj = null;
				eval("include_once dirname(__FILE__).'/../integration/".$this->c->cmstype.".php';");
				eval('$obj = new m_integration_'.$this->c->cmstype.'();');
				$obj->synchroMoney($user['username']);
			}
			return true;
		}else{
			return false;
		}
	}

	public function checkMyPaper($answers,$paperid){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "select * from ".$pfx."wls_quiz_paper where id=".$paperid;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$id_quiz = $temp['id_quiz'];

		$ques_ = array();
		$ids_question = '';
		for($i=0;$i<count($answers);$i++){
			$ques_[$answers[$i]['id']] = $answers[$i]['answer'];
			$ids_question .= $answers[$i]['id'].",";
		}
		$ids_question = substr($ids_question,0,strlen($ids_question)-1);

		//It's written in controller/quiz.php
		$questionObj = new m_question();
		$answers = $questionObj->getAnswers($ques_);
		$json = json_encode($answers);

		//Just out put the answsers if the current user is guest, and do nothing
		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		if($user['username']=='guest'){
			return $json;
		}

		//Do quiz log.
		$quizLogObj = new m_quiz_log();
		$data = array(
			'ids_question'=>$ids_question,
			'id_quiz'=>$id_quiz,
		);
		$id_quiz_log = $quizLogObj->insert($data);

		$count_right = 0;
		$count_wrong = 0;
		$count_giveup = 0;
		$cent = 0;
		$mycent = 0;
		$count_total = count($answers);

		$quesLogObj = new m_question_log();
		for($i=0;$i<count($answers);$i++){
			if($answers[$i]['type']==4||
			$answers[$i]['type']==5||
			$answers[$i]['type']==6||
			($answers[$i]['type']==7&&$answers[$i]['id_parent']==0))continue;
			$cent += $answers[$i]['cent'];
			$quesLogData = array(
				 'id_quiz'=>$paperid
				,'id_quiz_log'=>$id_quiz_log
				,'myAnswer'=>$answers[$i]['myAnswer']
				,'answer'=>$answers[$i]['answer']
				,'id_question'=>$answers[$i]['id']
				,'id_user'=>$user['id']
				,'cent'=>$answers[$i]['cent']
				,'type'=>$answers[$i]['type']
				,'markingmethod'=>$answers[$i]['markingmethod']
			);
			$result = $quesLogObj->addLog($quesLogData);
			if($result==1){
				$count_right++;
				$mycent += $answers[$i]['cent'];
			}
			if($result==0)$count_wrong++;
			if($result==-1)$count_giveup++;
		}

		$quizLogObj->update(array(
			 'count_right'=>$count_right
			,'count_wrong'=>$count_wrong
			,'count_giveup'=>$count_giveup
			,'count_total'=>$count_total
			,'proportion'=>( ($count_right*100)/($count_right+$count_wrong) )
			,'mycent'=>$mycent
			,'cent'=>$cent
			,'id'=>$id_quiz_log
		));

		$this->id_quiz = $id_quiz;
		$this->mycent = $mycent;
		$this->cumulative('score');
		$this->cumulative('count_used');
		return $json;
	}
}
?>