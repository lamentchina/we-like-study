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

class m_quiz_paper extends wls implements dbtable,fileLoad{
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

	public function exportAll($path=null){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$sql = "select id from ".$pfx."wls_quiz_paper; ";
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

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['paper']);
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		
		$quizData = array();
		$paperData = array();
		$quizData['imagePath'] = '';
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

	public function exportOne($path=null){		
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$sql = "select 
			 ".$pfx."wls_quiz_paper.id as pid
			,".$pfx."wls_quiz.id as qid
			,money
			,author
			,id_level_subject
			,title
			,imagePath
			
			from ".$pfx."wls_quiz_paper
			join ".$pfx."wls_quiz 
			on ".$pfx."wls_quiz_paper.id_quiz = ".$pfx."wls_quiz.id 
			where ".$pfx."wls_quiz_paper.id = ".$this->id_paper." ;";

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['paper']);

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
		if(!($temp['imagePath']==''||$temp['imagePath']=='0')){
			$chr++;
			$objPHPExcel->getActiveSheet()->setCellValue(chr($chr).'1', $this->lang['imagePath']);	
			$objPHPExcel->getActiveSheet()->setCellValue(chr($chr).'2', $temp['imagePath']);				
		}	
		
		$quizObj = new m_quiz();
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
		$temp = explode(',',$me['subject']);
		$ids = '';
		for($i=0;$i<count($temp);$i++){
			$ids .= "'".$temp[$i]."',";
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$where .= " and id_level_subject in (".$ids.") ";
		
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){				
				if($keys[$i]=='id'){
					$where .= " and ".$pfx."wls_quiz_paper.id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='title'){
					if(count($search[$keys[$i]])==1){
						$where .= " and ".$pfx."wls_quiz.title like '%".$search[$keys[$i]][0]."%' ";
					}else{
						$where .= " and (";
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$where .= " ".$pfx."wls_quiz.title like '%".$search[$keys[$i]][$i2]."%' or";
						}
						$where = substr($where,0,strlen($where)-2);
						$where .= " ) ";
					}					
				}else if($keys[$i]=='money'){
					if(count($search[$keys[$i]])==1){
						$where .= " and ".$pfx."wls_quiz_paper.money ".$search[$keys[$i]][0][0]." ".$search[$keys[$i]][0][1]." ";
					}else{
						$where .= " and (";
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$where .= " ".$pfx."wls_quiz_paper.money ".$search[$keys[$i]][$i2][0]." ".$search[$keys[$i]][$i2][1]." or";
						}
						$where = substr($where,0,strlen($where)-2);
						$where .= " ) ";
					}	
				}else if($keys[$i]=='subject'){
					if(count($search[$keys[$i]])==1){
						$sql_subject = "select id_level from ".$pfx."wls_subject where name = '".$search[$keys[$i]][0][1]."' ;";
						$res_subject = mysql_query($sql_subject,$conn);
						$temp_subject = mysql_fetch_assoc($res_subject);
						
						$where .= " and ".$pfx."wls_quiz.id_level_subject = ".$temp_subject['id_level']." ";
					}else{
						$name_subjects = '';
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$name_subjects .= "'".$search[$keys[$i]][$i2][1]."',";
						}
						$name_subjects = substr($name_subjects,0,strlen($name_subjects)-1);
						$sql_subject = "select id_level from ".$pfx."wls_subject where name in (".$name_subjects.") ;";
						$res_subject = mysql_query($sql_subject,$conn);
						$ids_subject = '';
						while($temp_subject = mysql_fetch_assoc($res_subject)){
							$ids_subject .= "'".$temp_subject['id_level']."',";
						}
						$ids_subject = substr($ids_subject,0,strlen($ids_subject)-1);
						
						$where .= " and ".$pfx."wls_quiz.id_level_subject in (".$ids_subject.")  ";
					}	
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
			'search'=>json_encode($search)
		);
	}

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