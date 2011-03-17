<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../question.php';
include_once dirname(__FILE__).'/../question/log.php';
include_once dirname(__FILE__)."/../user.php";
include_once dirname(__FILE__)."/../user/group.php";
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../quiz/log.php';

include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_quiz_exam extends wls implements dbtable,fileLoad{
	public $phpexcel;
	public $id_exam = null;
	public $questions = null;
	public $exam = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_exam (".$keys.") values ('".$values."')";
//		echo $sql;
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function delete($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_question where id_quiz in (
			select id_quiz from ".$pfx."wls_quiz_exam where id in (".$ids.")
		) ";
		mysql_query($sql,$conn);	
		
		$sql = "delete from ".$pfx."wls_quiz where id in (
			select id_quiz from ".$pfx."wls_quiz_exam where id in (".$ids.")
		) ";
		mysql_query($sql,$conn);
		
		$sql = "delete from ".$pfx."wls_quiz_exam where id in (".$ids.") ";
		mysql_query($sql,$conn);
	}

	public function update($data){

		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_quiz_exam set ";
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

		$sql = "drop table if exists ".$pfx."wls_quiz_exam;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_exam(
				 id int primary key auto_increment	
				,id_quiz int default 0
				,time_start datetime default '1987-03-18'		
				,time_stop datetime default '1986-08-09'
				,passline float default '60'		
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);

		$sql = "drop table if exists ".$pfx."wls_quiz_exam_union;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_exam_union(
				 id int primary key auto_increment	
				,ids_exam varchar(200) default ''	
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);	
		
		return true;
	}

	public function importAll($path){}

	public function exportAll($path=null){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "select id from ".$pfx."wls_quiz_exam; ";
		$res = mysql_query($sql,$conn);
		$ids = '';
		while($temp = mysql_fetch_assoc($res)){
			$ids .= $temp['id'].',';
		}
		$ids = substr($ids,0,strlen($ids)-1);

		return $ids;
	}

	public function importOne($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);
//		echo $path;exit();
		$currentSheet = $this->phpexcel->getSheetByName($this->lang['exam']);
		$allColmun = $currentSheet->getHighestColumn();

		$quizData = array();
		$examData = array();
		$quizData['imagePath'] = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['title']){
				$quizData['title'] = $currentSheet->getCell($i."3")->getValue();
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['time_start']){
				$examData['time_start'] = $currentSheet->getCell($i."3")->getValue();
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['time_stop']){
				$examData['time_stop'] = $currentSheet->getCell($i."3")->getValue();
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['examPassLine']){
				$examData['passline'] = $currentSheet->getCell($i."3")->getValue();
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['imagePath']){
				$imagePath = $currentSheet->getCell($i."3")->getValue();
				$quizData['imagePath'] = $imagePath;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['subject']){
				$quizData['id_level_subject'] = $currentSheet->getCell($i."3")->getValue();
				$sql_ = "select name from ".$pfx."wls_subject where id_level = '".$quizData['id_level_subject']."'; ";
				$res = mysql_query($sql_,$conn);
				$temp = mysql_fetch_assoc($res);
				if($temp==false){
					$this->error("quiz_paper::importOne, subject name not found");
					return false;
				}
				$quizData['name_subject'] = $temp['name'];
			}
		}

		$quizObj = new m_quiz();
//		echo 12312312;
		$quizData['id'] = $quizObj->insert($quizData);
//		print_r($quizData);
//		exit();
		if($quizData['id']==false)return false;
		$quizObj->id_quiz = $quizData['id'];
		$quizObj->quizData = $quizData;
		$return = $quizObj->importOne($this->phpexcel);
//		exit();
		if($return==false){
			$this->error("quiz_exam::importOne, quiz::importOne , error");
			return false;
		}

		$examData['id_quiz'] = $quizData['id'];
		$this->id_exam = $this->insert($examData);
//		print_r($examData);
		$currentSheet = $this->phpexcel->getSheetByName($this->lang['userGroup']);
		$allRow = $currentSheet->getHighestRow();
//		$allRow = $allRow[0];
		$groupObj = new m_user_group();
		for($i=3;$i<$allRow;$i++){
			$groupObj->linkExam(array(
				 'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue()
				,'id_exam'=>$this->id_exam
			));
		}
		
		return true;
	}

	public function exportOne($path=null){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "select
			 ".$pfx."wls_quiz_exam.id as pid
			,".$pfx."wls_quiz.id as qid
			,money
			,author
			,id_level_subject
			,title
			,imagePath
			
			from ".$pfx."wls_quiz_exam
			join ".$pfx."wls_quiz 
			on ".$pfx."wls_quiz_exam.id_quiz = ".$pfx."wls_quiz.id 
			where ".$pfx."wls_quiz_exam.id = ".$this->id_exam." ;";

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['exam']);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $this->lang['author']);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $temp['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $temp['id_level_subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $temp['author']);

		$chr = 66;
		if(!($temp['money']==''||$temp['money']=='0')){
			$chr++;
			$objPHPExcel->getActiveSheet()->setCellValue(chr($chr).'1', $this->lang['money']);
			$objPHPExcel->getActiveSheet()->setCellValue(chr($chr).'2', $temp['money']);
		}
		$quizObj = new m_quiz();
		if(!($temp['imagePath']==''||$temp['imagePath']=='0')){
			$chr++;
			$objPHPExcel->getActiveSheet()->setCellValue(chr($chr).'1', $this->lang['imagePath']);
			$objPHPExcel->getActiveSheet()->setCellValue(chr($chr).'2', $temp['imagePath']);
			$quizObj->imagePath = $temp['imagePath'];
		}

		$quizObj->exportOne($temp['qid'],$objPHPExcel);
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);

		$file =  "download/".date('YmdHis').".xls";
		if($path==null){
			$path = $this->c->filePath.$file;
		}
		$objWriter->save($path);
		return $file;
	}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";
		$userObj = new m_user();
		$me = $userObj->getMyInfo();

		$sql = "select * 
,(select count(*) from w_wls_user_group2exam where id_exam = w_wls_quiz_exam.id  ) as count_groups  
,(select count(*) from w_wls_user_group2user where id_level_group in  (
 select id_level_group from w_wls_user_group2exam where  id_exam = w_wls_quiz_exam.id 
)  ) as count_users

 from w_wls_quiz_exam,w_wls_quiz

where w_wls_quiz_exam.id in (
select id_exam from w_wls_user_group2exam where id_level_group in (
select id_level_group from w_wls_user_group2user where username = '".$me['username']."'
)
) and w_wls_quiz.id = w_wls_quiz_exam.id_quiz";
		
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		$index = 1;
		while($temp = mysql_fetch_assoc($res)){
			$temp['index'] = $index;
			$index ++;
			$arr[] = $temp;
		}

		$sql2 = "select count(id_quiz) as total from w_wls_quiz_exam,w_wls_quiz where w_wls_quiz_exam.id in (
select id_exam from w_wls_user_group2exam where id_level_group in (
select id_level_group from w_wls_user_group2user where username = '".$me['username']."'
)
) and w_wls_quiz.id = w_wls_quiz_exam.id_quiz";
		$res = mysql_query($sql2,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		return array(
			'page'=>$page,
			'data'=>$arr,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
			'search'=>json_encode($search)
		);
	}

	public function getQuizIds(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "select questions,id from ".$pfx."wls_quiz_exam where id = ".$this->id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		return $temp['questions'];
	}

	public function checkMoney($id){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "select money,id from ".$pfx."wls_quiz_exam where id= ".$id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$userObj = new m_user();
		$user = $userObj->getMyInfo();

		if(intval($temp['money'])>0 && $user['username']=='guest')return false;
		if(intval($user['money'])>intval($temp['money'])){
			$sql = "update ".$pfx."wls_user set money = money - ".$temp['money']." where id = ".$user['id'];

			if(!isset($_SESSION)){
				session_start();
			}
			$_SESSION['wls_user']['money'] -= $temp['money'];
			mysql_query($sql,$conn);

			if($this->c->cmstype!='' && $user['username']!='guest' ){
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

	public function checkMyexam($answers,$examid){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "select * from ".$pfx."wls_quiz_exam where id=".$examid;
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

		//Just out put the answsers if the current user is guest, and do nothing
		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		if($user['username']=='guest'){
			return $answers;
		}

		//Do quiz log.
		$quizLogObj = new m_quiz_log();
		$quizLogObj->addLog(array(
			 'id_quiz'=>$temp['id_quiz']
			,'answers'=>$answers
			,'ids_question'=>$ids_question
		));

		return $answers;
	}
}
?>