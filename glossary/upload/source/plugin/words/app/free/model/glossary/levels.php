<?php
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

/**
 * 生词本模块中的 关卡 设计
 * 规定,默认每个新用户都有几个 免费的 关卡可以参与,这些参与信息会被写入到 关卡日志 中
 * 关卡必须逐关推进
 * 比如一个CET4生词本有3000个单词,每50个单词一个关卡
 * 则用户必须 50个,50个,50个 这样的关卡模式逐一推进
 * 
 * @version 2011-05-01
 * @author wei1224hf
 * @see http://www.welikestudy.com/forum.php?mod=viewthread&tid=1167
 * */
class m_glossary_levels extends wls implements dbtable,fileLoad{

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
		$sql = "insert into ".$pfx."wls_glossary_levels (".$keys.") values ('".$values."')";
		
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}
	
	public function add($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select max(level) as mx from ".$pfx."wls_glossary_levels where subject = '".$data['subject']."' ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$data['level'] = $temp['mx'] + 1;
		$this->insert($data);		
	}

	public function caculateLevelWordCounts(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_glossary_levels ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while ($temp = mysql_fetch_assoc($res)) {
			$sql2 = "update ".$pfx."wls_glossary_levels set count_words = 
				(select count(*) as count_ from ".$pfx."wls_glossary 
					where subject ='".$temp['subject']."' and level = ".$temp['level']." )
					where subject ='".$temp['subject']."' and level = ".$temp['level']." 
					";
			mysql_query($sql2,$conn);
			//echo $sql2;
		}
	}

	/**
	 * Delete one or more rows by id. Only by id!
	 *
	 * @param $ids Every table has this column.
	 * @return bool
	 * */
	public function delete($id){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "select subject,level from ".$pfx."wls_glossary_levels where id = ".$id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$subject = $temp['subject'];
		$sql = "delete from ".$pfx."wls_glossary_levels where id = ".$id." ;";
		mysql_query($sql,$conn);

		$sql = "update ".$pfx."wls_glossary set level = level - 1 where level > ".$temp['level']." and subject = '".$subject."' ;";
		mysql_query($sql,$conn);
		$sql = "update ".$pfx."wls_glossary_levels set level = level - 1 where level > ".$temp['level']." and subject = '".$subject."' ;";
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

		$sql = "update ".$pfx."wls_glossary_levels set ";
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
	 * If it's already exists, it would be dropped first.
	 *
	 * @return bool
	 * */
	public function create(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists pre_wls_glossary_levels;";
		$sql = str_replace('pre_', $pfx, $sql);
		mysql_query($sql,$conn);
		$sql = "
			create table pre_wls_glossary_levels(
			
				 id int primary key auto_increment	
				,level int default 1
				,subject varchar(200) default '0'

				,money int default 0
				,passline int default 0
				,count_passed int default 0		
				,count_joined int default 0
				,count_words int default 0	
							
			) DEFAULT CHARSET=utf8;
			";
		$sql = str_replace('pre_', $pfx, $sql);
		mysql_query($sql,$conn);
		
		return true;
	}
	
	/**
	 * Import an Excel file into the glossary_levels's database table
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
		}
		
		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['glossary']['level']);
		$allRow = array($currentSheet->getHighestRow());
		$allRow = $allRow[0];
		$allColmun = $currentSheet->getHighestColumn();
		$keysRow = 2;

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary']['level']){
				$keys['level'] = $i;
			}
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['subject']['subject']){
				$keys['subject'] = $i;
			}	
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['user']['money']){
				$keys['money'] = $i;
			}	
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['glossary']['passline']){
				$keys['passline'] = $i;
			}								
		}

		$datas = array();
		for($i=($keysRow+1);$i<=$allRow;$i++){
			$data = array(
				'level'=>$currentSheet->getCell($keys['level'].$i)->getValue(),
				'subject'=>$currentSheet->getCell($keys['subject'].$i)->getValue(),
				'money'=>$currentSheet->getCell($keys['money'].$i)->getValue(),
				'passline'=>$currentSheet->getCell($keys['passline'].$i)->getValue(),
			);

			$datas[] = $data;
			$this->insert($data);
		}
		
		return $datas;
	}

	/**
	 * Export an Excel file , with all the glossary_levels data.
	 *
	 * @return $path filepath
	 * */
	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->il8n['glossary_levels']['glossary_levels']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->cfg->siteName.'_'.$this->il8n['normal']['exportFile']);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->il8n['glossary_levels']['word']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->il8n['glossary_levels']['translation']);


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
	
	public function getItem($level,$subject){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_glossary_levels where level = ".$level." and subject = '".$subject."'; ";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		return $temp;
	}

	/**
	 * It's normally used for client-side's grid and table stuff.
	 * The client should support the search and ordering abilty
	 * */
	public function getList($page=1,$pagesize=20,$search=null,$orderby=null,$columns="*"){
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
						$where .= " and level = ".$search[$keys[$i]][0]." ";
					}else{
						$where .= " and (";
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$where .= " level = ".$search[$keys[$i]][$i2]." or";
						}
						$where = substr($where,0,strlen($where)-2);
						$where .= " ) ";
					}
				}else if($keys[$i]=='user'){
					//TODO
				}else if($keys[$i]=='money'){
					if(count($search[$keys[$i]])==1){
						$where .= " and money ".$search[$keys[$i]][0][0]." ".$search[$keys[$i]][0][1]." ";
					}else{
						$where .= " and (";
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$where .= " money ".$search[$keys[$i]][$i2][0]." ".$search[$keys[$i]][$i2][1]." or";
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
		$sql = "select ".$columns." from ".$pfx."wls_glossary_levels ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
		//echo $sql;
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['subject_name'] = $subjects[$temp['subject']];
			$data[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_glossary_levels ".$where;
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
}
?>