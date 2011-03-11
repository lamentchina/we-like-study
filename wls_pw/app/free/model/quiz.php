<?php
include_once dirname(__FILE__).'/question.php';

class m_quiz extends wls implements dbtable{

	public $id_quiz = 0;
	public $quizData = array();
	public $count_giveup = 0;
	public $count_right = 0;
	public $count_wrong = 0;
	public $count_manual = 0;
	public $count_total = 0;
	public $questions = array();
	public $ids_questions = '';
	public $cent = 0;
	public $mycent = 0;
	public $imagePath = "";

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if(!isset($data['cache_path_quiz'])){
			$data['cache_path_quiz'] = '0';
		}
		if(!isset($data['ids_questions'])){
			$data['ids_questions'] = '0';
		}
		if(!isset($data['date_created'])){
			$data['date_created'] = date('Y-m-d H:i:s');
		}
		if(!isset($data['author'])){
			$user = new m_user();
			$me = $user->getMyInfo();
			$data['author'] = $me['username'];
		}

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function delete($ids){}

	public function update($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_quiz set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;

		$res = mysql_query($sql,$conn);
		return $res;
	}

	public function create(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz(
				 id int primary key auto_increment	
				,id_level_subject int default 0		
				,name_subject varchar(200) default '0' 
				
				,title varchar(200) default 'title'		
				,ids_questions text
				,imagePath varchar(200) default ''
				
				,description varchar(200) default 'missed'			
				,author varchar(200) default 'admin'		
				,date_created datetime default '1987-03-18'				
	
				,score_top float default 0			
				,score_top_user varchar(200) default 0		
				,score_avg float default 0			
				,count_used int	default 0					
				
				,cache_path_quiz text 				
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){}

	public function importOne($phpexcel){
		$currentSheet = $phpexcel->getSheetByName($this->lang['question']);
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
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['Qes_Type2']){
				$keys['Qes_Type2'] = $i;
			}			
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['layout']){
				$keys['layout'] = $i;
			}
		}

		for($i=3;$i<=$allRow;$i++){
			$title = $this->t->formatTitle($currentSheet->getCell($keys['title'].$i)->getValue());
			$title = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$title);
			$title = str_replace("[/".$this->lang['image']."]","\">",$title);
			$title = str_replace("[___","<input width=\"100\" class=\"w_blank\" index=\"",$title);
			$title = str_replace("___]","\"/>",$title);

			$question = array(
				'type'=>$currentSheet->getCell($keys['type'].$i)->getValue(),
				'title'=>$title,
				'answer'=>$currentSheet->getCell($keys['answer'].$i)->getValue(),			
				'option1'=>$this->t->formatTitle($currentSheet->getCell($keys['option1'].$i)->getValue()),
				'id_quiz'=>$this->id_quiz	
			);

			if(isset($keys['belongto'])){
				$question['belongto']=$currentSheet->getCell($keys['belongto'].$i)->getValue();
			}else{
				$question['belongto']=0;
			}
			if(isset($keys['index'])){
				$question['index']=$currentSheet->getCell($keys['index'].$i)->getValue();
			}else{
				$question['index']=$i;
			}
			if(isset($keys['cent'])){
				$question['cent']=$currentSheet->getCell($keys['cent'].$i)->getValue();
			}
				
			$optionlength = 1;
			if(isset($keys['option2']) ){
				$value = $this->t->formatTitle($currentSheet->getCell($keys['option2'].$i)->getValue());
				if($value!=''){
					$optionlength = 2;
					$value = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$value);
					$value = str_replace("[/".$this->lang['image']."]","\">",$value);
					$question['option2'] = $value;
				}
			}
			if(isset($keys['option3']) ){
				$value = $this->t->formatTitle($currentSheet->getCell($keys['option3'].$i)->getValue());
				if($value!=''){
					$optionlength = 3;
					$value = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$value);
					$value = str_replace("[/".$this->lang['image']."]","\">",$value);
					$question['option3'] = $value;
				}
			}
			if(isset($keys['option4']) ){
				$value = $this->t->formatTitle($currentSheet->getCell($keys['option4'].$i)->getValue());
				if($value!=''){
					$optionlength = 4;
					$value = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$value);
					$value = str_replace("[/".$this->lang['image']."]","\">",$value);
					$question['option4'] = $value;
				}
			}
			if(isset($keys['option5']) ){
				$value = $this->t->formatTitle($currentSheet->getCell($keys['option5'].$i)->getValue());
				if($value!=''){
					$optionlength = 5;
					$value = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$value);
					$value = str_replace("[/".$this->lang['image']."]","\">",$value);
					$question['option5'] = $value;
				}
			}
			if(isset($keys['option6']) ){
				$value = $this->t->formatTitle($currentSheet->getCell($keys['option6'].$i)->getValue());
				if($value!=''){
					$optionlength = 6;
					$value = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$value);
					$value = str_replace("[/".$this->lang['image']."]","\">",$value);
					$question['option6'] = $value;
				}
			}
			if(isset($keys['option7']) ){
				$value = $this->t->formatTitle($currentSheet->getCell($keys['option7'].$i)->getValue());
				if($value!=''){
					$optionlength = 7;
					$value = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$value);
					$value = str_replace("[/".$this->lang['image']."]","\">",$value);
					$question['option7'] = $value;
				}
			}
			if(isset($keys['optionlength']) && ( $currentSheet->getCell($keys['optionlength'].$i)->getValue()!='') ){
				$optionlength = $currentSheet->getCell($keys['optionlength'].$i)->getValue();
			}
			$question['optionlength'] = $optionlength;

			if(isset($keys['description'])){
				$description = $this->t->formatTitle($currentSheet->getCell($keys['description'].$i)->getValue());
				$description = str_replace("[".$this->lang['image']."]","<img src=\"".$this->quizData['imagePath'],$description);
				$description = str_replace("[/".$this->lang['image']."]","\">",$description);
				$question['description'] = $description;
			}
			if(isset($keys['path_listen'])){
				$value = $currentSheet->getCell($keys['path_listen'].$i)->getValue();
				if($value!=''){
					$question['path_listen']=$this->quizData['imagePath'].$value;
				}
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
			if(isset($keys['layout'])){
				$value = $currentSheet->getCell($keys['layout'].$i)->getValue();
				if($value!=''){
					$question['layout']=$this->t->formatLayout($value,true);
				}
			}
			if(isset($keys['Qes_Type2'])){
				$value = $currentSheet->getCell($keys['Qes_Type2'].$i)->getValue();
				if($value!=''){
					$question['type2']=$value;
				}
			}			
			$this->questions[$question['index']] = $question;
		}

		$this->saveQuestions();
	}

	public function saveQuestions(){
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
				'id'=>$this->id_quiz,
				'ids_questions'=>$ids
			);

			$temp = $this->update($data);
			return $temp;
		}
	}

	public function cumulative($column){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if($column=='score'){
			$sql = "select score_top from ".$pfx."wls_quiz where id = ".$this->id_quiz;

			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);
			if($temp['score_top']< intval($this->mycent) ){
				$userObj = new m_user();
				$user = $userObj->getMyInfo();

				$sql = "update ".$pfx."wls_quiz set
					score_top_user = '".$user['username']."',
					score_top = '".$this->mycent."' 
					where id = ".$this->id_quiz;

				mysql_query($sql,$conn);
			}
			$sql = "update ".$pfx."wls_quiz set score_avg = (score_avg*count_used+".$this->mycent.")/(count_used+1) where id = ".$this->id_quiz;

		}else{
			$sql = "update ".$pfx."wls_quiz set ".$column." = ".$column."+1 where id = ".$this->id_quiz;
		}
		mysql_query($sql,$conn);
	}

	/**
	 * Export all the questions of one quiz to an Excel
	 * TODO export to a PDF ?
	 *
	 * @param $id
	 * */
	public function exportOne($id,$objPHPExcel){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "select * from ".$pfx."wls_question where id_quiz = ".$id;
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['question']);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->c->siteName.'_'.$this->lang['exportFile']);

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
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
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
		$objPHPExcel->getActiveSheet()->setCellValue('X2', $this->lang['Qes_Type2']);
		$objPHPExcel->getActiveSheet()->setCellValue('Y2', $this->lang['layout']);

		$index = 3;
		for($i=0;$i<count($data);$i++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['id_parent']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->t->formatQuesType($data[$i]['type']));			
				
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $this->t->formatImagePath($this->t->formatTitle($data[$i]['title'],true),$this->imagePath) );
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['answer']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, $data[$i]['cent']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$index, $this->t->formatImagePath($this->t->formatTitle($data[$i]['option1'],true),$this->imagePath) );
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$index, $this->t->formatImagePath($this->t->formatTitle($data[$i]['option2'],true),$this->imagePath) );
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$index, $this->t->formatImagePath($this->t->formatTitle($data[$i]['option3'],true),$this->imagePath) );
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$index, $this->t->formatImagePath($this->t->formatTitle($data[$i]['option4'],true),$this->imagePath) );
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$index, $data[$i]['option5']);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$index, $data[$i]['option6']);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$index, $data[$i]['option7']);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$index, $data[$i]['optionlength']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$index, $this->t->formatImagePath($this->t->formatTitle($data[$i]['description'],true),$this->imagePath) );
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$index, $data[$i]['path_listen']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$index, $data[$i]['count_used']);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$index, $data[$i]['count_right']);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$index, $data[$i]['count_wrong']);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$index, $data[$i]['count_giveup']);
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$index, $data[$i]['difficulty']);
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$index,$this->t->formatMarkingMethod($data[$i]['markingmethod']));
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$index, $data[$i]['ids_level_knowledge']);
			$objPHPExcel->getActiveSheet()->setCellValue('X'.$index, $data[$i]['type2']);
			$objPHPExcel->getActiveSheet()->setCellValue('Y'.$index, $this->t->formatLayout($data[$i]['layout']) );

			$index ++;
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('E2');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'E2:E'.(count($data)+1));
	}
}
?>