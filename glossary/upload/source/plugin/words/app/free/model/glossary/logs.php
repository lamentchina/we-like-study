<?php
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

/**
 * 词汇表模块
 * 词汇表日志
 * 记录每个用户对每个单词的掌握度
 * 估计数据量会非常大
 * 
 * @author wei1224hf
 * @version 2011-05-01
 * @see www.welikestudy.com
 * */
class m_glossary_logs extends wls implements dbtable,fileLoad{

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

		$sql = "select * from ".$pfx."wls_glossary_logs where id_user = ".$data['id_user']." and id_word = ".$data['id_word'];
		$res = mysql_query($sql,$conn);
		
		if($temp = mysql_affected_rows($conn)){
			if($data['count_right']==1){
				$sql = "update ".$pfx."wls_glossary_logs set count_right = count_right + 1 where id_user = ".$data['id_user']." and id_word = ".$data['id_word'];
			}else{
				$sql = "update ".$pfx."wls_glossary_logs set count_wrong = count_wrong + 1 where id_user = ".$data['id_user']." and id_word = ".$data['id_word'];
			}
		}else{
			if(!isset($data['logtime']))$data['logtime']=date('Y-m-d H:i:s');
			$keys = array_keys($data);
			$keys = implode(",",$keys);
			$values = array_values($data);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_glossary_logs (".$keys.") values ('".$values."')";
		}
		
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
		$sql = "delete from ".$pfx."wls_glossary_logs where id in( ".$ids." );";
		//echo $sql;
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

		$sql = "update ".$pfx."wls_glossary_logs set ";
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

		$sql = "drop table if exists ".$pfx."wls_glossary_logs;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_glossary_logs(
			
				 id int primary key auto_increment	
				,id_word int default '0'
				
				,subject varchar(200) default '0'
				,level int default 0
				
				,id_user int default '0'
				,username varchar(200) default '0'
				,word varchar(200) default '0'
				,translation varchar(200) default '0'
				,logtime datetime default '1987-03-18'
				,count_right int default 0
				,count_wrong int default 0		
							
				,CONSTRAINT ".$pfx."wls_glossary_logs_u UNIQUE (id_user,id_word)
			) DEFAULT CHARSET=utf8;
			";

		mysql_query($sql,$conn);
		
		return true;
	}
	
	/**
	 * Import an Excel file into the glossary_logs's database table
	 * But the Excel must fit some ruls.
	 *
	 * @param $path Excel Path
	 * @return bool
	 * */
	public function importAll($path){
		if($this->phpexcel==null){
			$objPHPExcel = new PHPExcel();
			$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
			$PHPReader->setReadDataOnly(true);
			$this->phpexcel = $PHPReader->load($path);
			
			$currentSheet = $this->phpexcel->getSheetByName($this->il8n['normal']['main']);
			$this->subject = $currentSheet->getCell("A2")->getValue();
			$this->operation = $currentSheet->getCell("B2")->getValue();
			
			if($this->operation==$this->il8n['normal']['rebuild'])$this->create();			

			$currentSheet = $this->phpexcel->getSheetByName($this->il8n['glossary_logs']['glossary_logs']);
			$allRow = array($currentSheet->getHighestRow());
			$allRow = $allRow[0];
			$allColmun = $currentSheet->getHighestColumn();
			$keysRow = 2;
		}else{
			$currentSheet = $this->phpexcel['currentSheet'];
			$allRow = intval($this->phpexcel['allRow']);
			$allColmun = $this->phpexcel['allColmun'];
			$keysRow = intval($this->phpexcel['keysRow']);
		}

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary_logs']['word']){
				$keys['word'] = $i;
			}
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary_logs']['translation']){
				$keys['translation'] = $i;
			}			
		}

		if( !(isset($keys['name']) || isset($keys['id_level'])) ){
			$error = "Wrong Structrue of Excel";
			$this->error($error);
		}

		$datas = array();
		for($i=($keysRow+1);$i<=$allRow;$i++){
			$data = array(
				'word'=>$currentSheet->getCell($keys['word'].$i)->getValue(),
				'translation'=>$currentSheet->getCell($keys['translation'].$i)->getValue(),
				'subject'=>$this->subject,
			);

			$datas[] = $data;
			$this->insert($data);
		}
		
		return $datas;
	}

	/**
	 * Export an Excel file , with all the glossary_logs data.
	 *
	 * @return $path filepath
	 * */
	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		if(!isset($_SESSION))session_start();
		$data = $this->getList(1,1000,array('user'=>$_SESSION['wls_user']['id']));
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->il8n['glossary']['word']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->cfg->siteName.'_'.$this->il8n['normal']['exportFile']);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->il8n['glossary']['word']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->il8n['glossary']['translation']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->il8n['glossary']['level']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $this->il8n['subject']['subject']);		

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['word']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['translation']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['level']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['subject']);			
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+1));	
		
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
				}else if($keys[$i]=='user'){
					$where .= " and id_user = ".$search['user']." ";
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
		$sql = "select * from ".$pfx."wls_glossary_logs ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
		
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			if(isset($temp['subject']) && $temp['subject']!=''){
				$temp['subject_name'] = $subjects[$temp['subject']];
			}
			$data[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_glossary_logs ".$where;
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
	
	public function addLogs($data){
		//print_r($data);
		if(!isset($_SESSION))session_start();
		$id_user = $_SESSION['wls_user']['id'];
		$username = $_SESSION['wls_user']['username'];
		for($i=0;$i<count($data);$i++){
			$data2 = array(
				"id_word"=>$data[$i]['id'],
				"id_user"=>$id_user,
				"username"=>$username,
				"word"=>$data[$i]['word'],
				"translation"=>$data[$i]['translation'],
				'subject'=>$data[$i]['subject'],
				'level'=>$data[$i]['level'],
				'count_right'=>0,
				'count_wrong'=>0,
			);
			if($data[$i]['correct']==1 || $data[$i]['correct']=='1' || $data[$i]['correct']==true){
				$data2['count_right']=1;
			}else{
				$data2['count_wrong']=1;
			}
			$this->insert($data2);
			//print_r($data2);
		}
	}
	
	public function getQuestions(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		if(!isset($_SESSION))session_start();
		$sql = " select * from ".$pfx."wls_glossary_logs where id_user = ".$_SESSION['wls_user']['id']." and count_wrong>=count_right order by logtime desc limit 0,50 ";
		
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