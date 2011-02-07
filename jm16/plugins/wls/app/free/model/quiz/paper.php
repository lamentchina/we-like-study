<?php
include_once dirname(__FILE__).'/../quiz.php';

class m_quiz_paper extends m_quiz implements dbtable,quizdo{

	public $phpexcel;
	public $id = null;
	public $mycent = null;
	public $questions = null;
	public $paper = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if(!isset($data['cache_path_quiz'])){
			$data['cache_path_quiz'] = '';
		}
		if(!isset($data['questions'])){
			$data['questions'] = '';
		}

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
				,id_level_subject int default 0		
				,name_subject varchar(200) default '0' 
				
				,title varchar(200) default 'title'		
				,questions text
				
				,description varchar(200) default '0'			
				,creator varchar(200) default 'admin'		
				,date_created datetime not null 	
				
				,time_limit int default 3600		
				,score_top float default 0			
				,score_top_user varchar(200) default 0		
				,score_avg float default 0			
				,count_used int	default 0			
				
				,money int default 0				
				
				,cache_path_quiz text 				
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function importExcel($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		include_once dirname(__FILE__).'/../subject.php';
		$obj = new m_subject();
		$data = $obj->getList(1,100);
		if(count($data['data'])<1){
			$this->error(array('description'=>'quiz paper wrong'));
			include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
			return false;
		}

		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';

		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['paper']);
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$paper = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['title']){
				$paper['title'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['money']){
				$paper['money'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['author']){
				$paper['creator'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['subject']){
				$paper['id_level_subject'] = $currentSheet->getCell($i."2")->getValue();
				$sql_ = "select name from ".$pfx."wls_subject where id_level = '".$paper['id_level_subject']."'; ";
				$res = mysql_query($sql_,$conn);
				$temp = mysql_fetch_assoc($res);
				$paper['name_subject'] = $temp['name'];
			}
		}
		$paper['date_created'] = date('Y-m-d H:i:s');
		$paper['id'] = $this->insert($paper);		
		$this->id = $paper['id'];
		$this->paper = $paper;

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['question']);
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['index']){
				$keys['index'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['belongto']){
				$keys['belongto'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['Qes_Type']){
				$keys['type'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['title']){
				$keys['title'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['answer']){
				$keys['answer'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['cent']){
				$keys['cent'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'A'){
				$keys['option1'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'B'){
				$keys['option2'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'C'){
				$keys['option3'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'D'){
				$keys['option4'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'E'){
				$keys['option5'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'F'){
				$keys['option6'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['option'].'G'){
				$keys['option7'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['ques_description']){
				$keys['description'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['listenningFile']){
				$keys['path_listen'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['count_used']){
				$keys['count_used'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['count_right']){
				$keys['count_right'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['count_wrong']){
				$keys['count_wrong'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['count_giveup']){
				$keys['count_giveup'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['difficulty']){
				$keys['difficulty'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['markingmethod']){
				$keys['markingmethod'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['optionlength']){
				$keys['optionlength'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['ids_level_knowledge']){
				$keys['ids_level_knowledge'] = $i;
			}
		}

		$index = 0;

		for($i=3;$i<=$allRow;$i++){
			$question = array(
				'index'=>$currentSheet->getCell($keys['index'].$i)->getValue(),
				'type'=>$currentSheet->getCell($keys['type'].$i)->getValue(),
				'title'=>$this->t->formatTitle($currentSheet->getCell($keys['title'].$i)->getValue()),
				'answer'=>$currentSheet->getCell($keys['answer'].$i)->getValue(),			
				'option1'=>$this->t->formatTitle($currentSheet->getCell($keys['option1'].$i)->getValue()),
				'optionlength'=>$currentSheet->getCell($keys['optionlength'].$i)->getValue(),
				'id_level_subject'=>$paper['id_level_subject'],
				'name_subject'=>$paper['name_subject'],
				'id_quiz_paper'=>$paper['id'],
				'title_quiz_paper'=>$paper['title'],			
			);
			if(isset($keys['belongto'])){
				$question['belongto']=$currentSheet->getCell($keys['belongto'].$i)->getValue();
			}
			if(isset($keys['cent'])){
				$question['cent']=$currentSheet->getCell($keys['cent'].$i)->getValue();
			}
			if(isset($keys['option2'])){
				$question['option2']=$this->t->formatTitle($currentSheet->getCell($keys['option2'].$i)->getValue());
			}
			if(isset($keys['option3'])){
				$question['option3']=$this->t->formatTitle($currentSheet->getCell($keys['option3'].$i)->getValue());
			}
			if(isset($keys['option4'])){
				$question['option4']=$this->t->formatTitle($currentSheet->getCell($keys['option4'].$i)->getValue());
			}
			if(isset($keys['option5'])){
				$question['option5']=$this->t->formatTitle($currentSheet->getCell($keys['option5'].$i)->getValue());
			}
			if(isset($keys['option6'])){
				$question['option6']=$this->t->formatTitle($currentSheet->getCell($keys['option6'].$i)->getValue());
			}
			if(isset($keys['option7'])){
				$question['option7']=$this->t->formatTitle($currentSheet->getCell($keys['option7'].$i)->getValue());
			}
			if(isset($keys['description'])){
				$question['description']=$this->t->formatTitle($currentSheet->getCell($keys['description'].$i)->getValue());
			}
			if(isset($keys['path_listen'])){
				$question['path_listen']=$currentSheet->getCell($keys['path_listen'].$i)->getValue();
			}
			if(isset($keys['count_used'])){
				$question['count_used']=$currentSheet->getCell($keys['count_used'].$i)->getValue();
			}
			if(isset($keys['count_right'])){
				$question['count_right']=$currentSheet->getCell($keys['count_right'].$i)->getValue();
			}
			if(isset($keys['count_wrong'])){
				$question['count_wrong']=$currentSheet->getCell($keys['count_wrong'].$i)->getValue();
			}
			if(isset($keys['count_giveup'])){
				$question['count_giveup']=$currentSheet->getCell($keys['count_giveup'].$i)->getValue();
			}
			if(isset($keys['difficulty'])){
				$question['difficulty']=$currentSheet->getCell($keys['difficulty'].$i)->getValue();
			}
			if(isset($keys['markingmethod'])){
				$question['markingmethod']=$currentSheet->getCell($keys['markingmethod'].$i)->getValue();
			}
			if(isset($keys['id_level_subject'])){
				$question['id_level_subject']=$paper['id_level_subject'];
			}
			if(isset($keys['id_quiz_paper'])){
				$question['id_quiz_paper']=$paper['id'];
			}
			if(isset($keys['title_quiz_paper'])){
				$question['title_quiz_paper']=$paper['title'];
			}
			if(isset($keys['ids_level_knowledge'])){
				$question['ids_level_knowledge']=$currentSheet->getCell($keys['ids_level_knowledge'].$i)->getValue();
			}
			$this->questions[$currentSheet->getCell($keys['index'].$i)->getValue()] = $question;
		}
		$this->saveQuestions();
	}

	public function saveQuestions(){
		include_once dirname(__FILE__).'/../question.php';
		$quesObj = new m_question();
		$questions = $this->questions;
		$ques = $quesObj->insertMany($questions);
		if($ques==false){
			return false;
		}else{
			$values = array_values($ques);
			$ids = '';
			for($i=0;$i<count($values);$i++){
				$ids .= $values[$i]['id'].",";
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$data = array(
				'id'=>$this->id,
				'questions'=>$ids
			);
			return $this->update($data);
		}
	}

	public function paperToExcel($paper,$questions){

		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$data = $paper;

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['paper']);

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

	public function exportExcel(){
		$data = $this->getList(1,1,array('id'=>$this->id));
		$paper = $data['data'][0];

		include_once dirname(__FILE__).'/../question.php';
		$ques = new m_question();
		$data = $ques->getList(1,200,array('id_quiz_paper'=>$this->id));
		$questions = $data['data'];

		return $this->paperToExcel($paper,$questions);
	}

	public function cumulative($column){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if($column=='score'){
			$sql = "select score_top from ".$pfx."wls_quiz_paper where id = ".$this->id;

			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);
			if($temp['score_top']<=$this->mycent){
				$user = $this->getMyUser();
				$sql = "update ".$pfx."wls_quiz_paper set
					score_top_user = '".$user['username']."',
					score_top = '".$this->mycent."' 
					where id = ".$this->id;
				$this->error($sql);
				try{
					mysql_query($sql,$conn);
					return true;
				}catch (Exception $ex){
					return false;
				}
			}
			$sql = "update ".$pfx."wls_quiz_paper set score_avg = (score_avg*count_used+".$this->mycent.")/(count_used+1) where id = ".$this->id;
		}else{
			$sql = "update ".$pfx."wls_quiz_paper set ".$column." = ".$column."+1 where id = ".$this->id;
		}
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
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
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_level_subject'){
					$where .= " and id_level_subject in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='title'){
					$where .= " and title like '%".$search[$keys[$i]]."%' ";
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_quiz_paper ".$where." ".$orderby;
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

		$sql2 = "select count(*) as total from ".$pfx."wls_quiz_paper ".$where;
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

		$user = $this->getMyUser();
		if($user['money']>$temp['money']){
			$sql = "update ".$pfx."wls_user set money = money - ".$temp['money']." where id = ".$user['id'];

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
}
?>