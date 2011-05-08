<?php
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_glossary extends wls implements dbtable,fileLoad{

	public $phpexcel = null;
	public $id_level = null;
	public $subject = null;
	
	/**
	 * Befor doing the 'importAll' action , check if it is 
	 * to-append or to-rebuild
	 * The rebuild action will delete everything old , and then insert the new data. 
	 * The append  action will only insert the new data. 
	 * */
	public $operation = null;

	/**
	 * Insert one row into the database's table
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return $id
	 * */
	public function insert($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_glossary (".$keys.") values ('".$values."')";
		
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	/**
	 * Delete one or more rows by id. Only by id!
	 *
	 * @param $ids Every table has this column.
	 * @return bool
	 * */
	public function delete($ids){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "delete from ".$pfx."wls_glossary where id in( ".$ids." );";
		echo $sql;
		mysql_query($sql,$conn);
		return true;
	}

	/**
	 * Update one row into the database's table
	 * There must have id in $data
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return bool
	 * */
	public function update($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_glossary set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;

		mysql_query($sql,$conn);
		
		return true;
	}

	/**
	 * Create this table.
	 * If it's already exists, it would be droped first.
	 *
	 * @return bool
	 * */
	public function create(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists pre_wls_glossary;";
		$sql = str_replace('pre_', $pfx, $sql);
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_glossary(
			
				 id int primary key auto_increment	
				,word varchar(200) default '0'
				,translation varchar(200) default '0'
				,subject varchar(200) default '0'
				,level int default 0
							
			) DEFAULT CHARSET=utf8;
			";
		$sql = str_replace('pre_', $pfx, $sql);
		mysql_query($sql,$conn);
		
		return true;
	}
	
	/**
	 * Import an Excel file into the glossary's database table
	 * But the Excel must fit some ruls.
	 *
	 * @param $path Excel Path
	 * @return bool
	 * */
	public function importAll($path){

		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);
		
		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['normal']['main']);
		$this->subject = $currentSheet->getCell("A2")->getValue();
		$this->operation = $currentSheet->getCell("B2")->getValue();
		
		if($this->operation==$this->il8n['normal']['rebuild'])$this->create();			

		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['glossary']['glossary']);
		$allRow = array($currentSheet->getHighestRow());
		$allRow = $allRow[0];
		$allColmun = $currentSheet->getHighestColumn();
		$keysRow = 2;


		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary']['word']){
				$keys['word'] = $i;
			}
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary']['translation']){
				$keys['translation'] = $i;
			}	
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['subject']['subject']){
				$keys['subject'] = $i;
			}	
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary']['level']){
				$keys['level'] = $i;
			}									
		}

		$datas = array();
		for($i=($keysRow+1);$i<=$allRow;$i++){
			$data = array(
				'word'=>$currentSheet->getCell($keys['word'].$i)->getValue(),
				'translation'=>$currentSheet->getCell($keys['translation'].$i)->getValue(),
				'subject'=>$this->subject,
				'level'=>$currentSheet->getCell($keys['level'].$i)->getValue(),
			);
			if(isset($keys['subject'])){
				$data['subject'] = $currentSheet->getCell($keys['subject'].$i)->getValue();
			}

			$datas[] = $data;
			$this->insert($data);
		}
		
		return $datas;
	}

	/**
	 * Export an Excel file , with all the glossary data.
	 *
	 * @return $path filepath
	 * */
	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->il8n['glossary']['glossary']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->cfg->siteName.'_'.$this->il8n['normal']['exportFile']);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->il8n['glossary']['word']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->il8n['glossary']['translation']);


		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['word']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['translation']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+1));
		
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle($this->il8n['normal']['main']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->il8n['subject']['subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->il8n['normal']['operation']);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $data[0]['subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->il8n['normal']['append']);		
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);

		$file = "download/".date('YmdHis').".xls";
		if($path==null){
			$path = $this->cfg->filePath.$file;
		}
		$objWriter->save($path);
		return $file;
	}

	public function importOne($path){}

	public function exportOne($path=null){}
	


	/**
	 * It's normally used for client-side's grid and table stuff.
	 * The client should support the search and ordering abilty
	 * */
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_subject ; ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp=mysql_fetch_assoc($res)){
			$data[$temp['id_level']] = $temp['name'];
		}
		$subjects = $data;

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='title'){
					if(count($search[$keys[$i]])==1){
						$where .= " and word like '%".$search[$keys[$i]][0]."%' ";
					}else{
						$where .= " and (";
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$where .= " word like '%".$search[$keys[$i]][$i2]."%' or";
						}
						$where = substr($where,0,strlen($where)-2);
						$where .= " ) ";
					}
				}else if($keys[$i]=='subject'){
					if(count($search[$keys[$i]])==1){
						$sql_subject = "select id_level from ".$pfx."wls_subject where name = '".$search[$keys[$i]][0][1]."' ;";
						$res_subject = mysql_query($sql_subject,$conn);
						$temp_subject = mysql_fetch_assoc($res_subject);

						$where .= " and subject = ".$temp_subject['id_level']." ";
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

						$where .= " and subject in (".$ids_subject.")  ";
					}
				}
			}
		}
		if($orderby==null)$orderby = " order by id ";
		$sql = "select ".$columns." from ".$pfx."wls_glossary ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
		
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['subject_name'] = $subjects[$temp['subject']];
			$data[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_glossary ".$where;
		$res = mysql_query($sql2,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		return array(
			'page'=>$page,
			'data'=>$data,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
		);
	}
	
	/**
	 * Get the glossary list and transform them into questions,
	 * to fit the WLS-quiz functions
	 * */
	public function getQuestions($subject='3002',$level=1){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = " SELECT *
			FROM pre_wls_glossary
			WHERE subject =  '".$subject."' and level = ".$level."
			ORDER BY RAND( ) 
			LIMIT 0 , 50 ";
		$sql = str_replace('pre_', $pfx, $sql);

		$res = mysql_query($sql,$conn);
		$data = array();
		while ($temp = mysql_fetch_assoc($res)) {
			$data[] = $temp;
		}
		$count = count($data);

		$numbers = range (0,$count-1);
		shuffle($numbers);

		for($i=0;$i<$count;$i++){
			$num = rand(0, $count-4);
			$data[$i]['option1'] = $data[$numbers[$num]]['translation'];
			$data[$i]['option2'] = $data[$numbers[$num+1]]['translation'];
			$data[$i]['option3'] = $data[$numbers[$num+2]]['translation'];
			$data[$i]['option4'] = $data[$i]['translation'];

			$answer = rand(1,4);
			$answerArr = array('A','B','C','D');

			$data[$i]['title'] = $data[$i]['word'];
			$data[$i]['type'] = '1';
			$data[$i]['layout'] = '1';
			$data[$i]['optionlength'] = '4';
			$data[$i]['cent'] = '1';

			$data[$i]['answerData'] = array(
				 'markingmethod'=>0
				,'answer'=>$answerArr[($answer-1)]
				,'description'=>'nothing'
				,'word'=>$data[$i]['word']
				,'translation'=>$data[$i]['translation']
			);

			$temp = $data[$i]['option4'];
			$data[$i]['option4'] = $data[$i]['option'.$answer];
			$data[$i]['option'.$answer] = $temp;
		}
		return $data;
	}
}
?>