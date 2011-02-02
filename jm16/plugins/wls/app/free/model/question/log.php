<?php
class m_question_log extends wls implements dbtable,log{

	public $phpexcel;
	public $id = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['id_user'])){
			$user = $this->getMyUser();
			$data['id_user'] = $user['id'];
		}
		if(!isset($data['myanswer'])){
			$data['myanswer'] = 'I_DONT_KNOW';
		}
		if(!isset($data['answer'])){
			$data['answer'] = 'A';
		}		

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question_log (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function insertMany($datas){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$user = $this->getMyUser();

		$keys = null;
		$sql = '';
		for($i=0;$i<count($datas);$i++){
			$data = $datas[$i];

			if($data['myAnswer']=='I_DONT_KNOW' || $data['type']==5)continue;
			$data['myanswer'] = $data['myAnswer'];
			unset($data['myAnswer']);
			$data['id_user'] = $user['id'];
			$data['id_level_user_group'] = $user['id_level_user_group'];

			if($keys == null){
				$keys = array_keys($data);
				$keys = implode(",",$keys);
				$sql = "insert into ".$pfx."wls_question_log (".$keys.") values ";
			}

			$values = array_values($data);
			$values = implode("','",$values);
			$sql .= "('".$values."'),";
		}
		$sql = substr($sql,0,strlen($sql)-1);

		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			$this->error(array('description'=>$sql));
			return false;
		}

	}

	public function delete($ids){}

	public function update($data){}

	public function create(){

		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_question_log;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_question_log(
				 id int primary key auto_increment	
				 
				,date_created datetime default '1987-03-18'					 
				,id_user int default 0				
				,id_level_user_group varchar(200) default '0' 	
				,id_level_subject varchar(200) default '0' 	
				,id_quiz_paper int default 0		
				,id_quiz_log int default 0					
				,myanswer text 						
				,answer text 						
				,correct int default 0				
				,type int default 1				
				,cent float default 0						
				,id_question int default 0			
				,id_question_parent int default 0	
				,application int default 0			
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
			$this->error(array('description'=>'Wrong'));
			include_once $this->c->libsPaht.'phpexcel/Classes/PHPExcel.php';
			return false;
		}

		include_once $this->c->libsPaht.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPaht.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPaht.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName('paper');
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$paper = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()=='标题'){
				$paper['title'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='金币'){
				$paper['money'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='作者'){
				$paper['creator'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='科目'){
				$paper['id_level_subject'] = $currentSheet->getCell($i."2")->getValue();
				$sql_ = "select name from ".$pfx."wls_subject where id_level = '".$paper['id_level_subject']."'; ";
				$res = mysql_query($sql_,$conn);
				$temp = mysql_fetch_assoc($res);
				$paper['name_subject'] = $temp['name'];
			}
		}
		$paper['date_created'] = date('Y-m-d i:m:s');
		$paper['id'] = $this->insert($paper);

		$currentSheet = $this->phpexcel->getSheetByName('questions');
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()=='序号'){
				$keys['index'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='属于'){
				$keys['belongto'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='题型'){
				$keys['type'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='题目'){
				$keys['title'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='答案'){
				$keys['answer'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='分值'){
				$keys['cent'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项A'){
				$keys['option1'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项B'){
				$keys['option2'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项C'){
				$keys['option3'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项D'){
				$keys['option4'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项E'){
				$keys['option5'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项F'){
				$keys['option6'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项G'){
				$keys['option7'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='解题说明'){
				$keys['description'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='听力文件'){
				$keys['path_listen'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='使用次数'){
				$keys['count_used'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='做对'){
				$keys['count_right'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='做错'){
				$keys['count_wrong'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='放弃'){
				$keys['count_giveup'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='难度'){
				$keys['difficulty'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='批改'){
				$keys['markingmethod'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项数'){
				$keys['optionlength'] = $i;
			}
		}

		include_once dirname(__FILE__).'/../question.php';
		$quesObj = new m_question();
		$index = 0;
		$questions = array();
		for($i=3;$i<=$allRow;$i++){
			$questions[$currentSheet->getCell($keys['index'].$i)->getValue()] = array(
				'index'=>$currentSheet->getCell($keys['index'].$i)->getValue(),
				'belongto'=>$currentSheet->getCell($keys['belongto'].$i)->getValue(),
				'type'=>$currentSheet->getCell($keys['type'].$i)->getValue(),
				'title'=>$currentSheet->getCell($keys['title'].$i)->getValue(),
				'answer'=>$currentSheet->getCell($keys['answer'].$i)->getValue(),
				'cent'=>$currentSheet->getCell($keys['cent'].$i)->getValue(),
				'option1'=>$currentSheet->getCell($keys['option1'].$i)->getValue(),
				'option2'=>$currentSheet->getCell($keys['option2'].$i)->getValue(),
				'option3'=>$currentSheet->getCell($keys['option3'].$i)->getValue(),
				'option4'=>$currentSheet->getCell($keys['option4'].$i)->getValue(),
				'option5'=>$currentSheet->getCell($keys['option5'].$i)->getValue(),
				'option6'=>$currentSheet->getCell($keys['option6'].$i)->getValue(),
				'option7'=>$currentSheet->getCell($keys['option7'].$i)->getValue(),
				'description'=>$currentSheet->getCell($keys['description'].$i)->getValue(),
				'path_listen'=>$currentSheet->getCell($keys['path_listen'].$i)->getValue(),
				'count_used'=>$currentSheet->getCell($keys['count_used'].$i)->getValue(),
				'count_right'=>$currentSheet->getCell($keys['count_right'].$i)->getValue(),
				'count_wrong'=>$currentSheet->getCell($keys['count_wrong'].$i)->getValue(),
				'count_giveup'=>$currentSheet->getCell($keys['count_giveup'].$i)->getValue(),
				'difficulty'=>$currentSheet->getCell($keys['difficulty'].$i)->getValue(),
				'markingmethod'=>$currentSheet->getCell($keys['markingmethod'].$i)->getValue(),
				'optionlength'=>$currentSheet->getCell($keys['optionlength'].$i)->getValue(),
				'id_level_subject'=>$paper['id_level_subject'],
				'name_subject'=>$paper['name_subject'],
				'id_quiz_paper'=>$paper['id'],
				'title_quiz_paper'=>$paper['title'],
			);
		}
		$ques = $quesObj->insertMany($questions);
		if($ques==false){
			return false;
		}else{
			$values = array_values($ques);
			$ids = '';
			for($i=0;$i<count($values);$i++){
				if($values[$i]['id_parent']==0){
					$ids .= $values[$i]['id'].",";
				}
				$ids = substr($ids,0,strlen($ids)-1);
			}
			$data = array(
				'id'=>$paper['id'],
				'questions'=>$ids
			);
			return $this->update($data);
		}
	}

	public function exportExcel(){}

	public function cumulative($column){}

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
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_question_log ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_question_log ".$where;
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

	public function addLog($whatHappened){}
}
?>