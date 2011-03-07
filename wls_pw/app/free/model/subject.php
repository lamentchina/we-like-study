<?php
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_subject extends wls implements dbtable,fileLoad{

	public $phpexcel = null;
	public $id_level = null;

	/**
	 * Insert one row into the database's table
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return $id
	 * */
	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		if(!isset($data['description'])){
			$data['description'] = '0';
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_subject (".$keys.") values ('".$values."')";
//		echo $sql;
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
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_subject where id  in (".$ids.");";
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
	}

	/**
	 * Update one row into the database's table
	 * There must have id in $data
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return bool
	 * */
	public function update($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_subject set ";
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

	/**
	 * Create this table.
	 * If it's already exists, it would be droped first.
	 *
	 * @return bool
	 * */
	public function create(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_subject;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_subject(
				 id int primary key auto_increment	
				,id_level varchar(200) unique		
				,name varchar(200) default '' 		
				,ordering int default 0				

				,icon varchar(200) default ''		
				,ids_level_knowledge varchar(200) default '0' 
				,description text 					
							
			) DEFAULT CHARSET=utf8;
			";
		
		mysql_query($sql,$conn);
		return true;
	}

	/**
	 * Import an Excel file into the subject's database table
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

		$currentSheet = $this->phpexcel->getSheetByName($this->lang['subject']);
		$allRow = array($currentSheet->getHighestRow());
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['id_level']){
				$keys['id_level'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['description']){
				$keys['description'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['name']){
				$keys['name'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['icon']){
				$keys['icon'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['ids_level_knowledge']){
				$keys['ids_level_knowledge'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['ordering']){
				$keys['ordering'] = $i;
			}			
		}	
		if( !(isset($keys['name'])&&isset($keys['id_level'])) ){
			$error = "Wrong Structrue of Excel";
			$this->error($error);
		}
		
		$data = array();
		for($i=3;$i<=$allRow[0];$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell($keys['id_level'].$i)->getValue(),
				'name'=> $this->t->formatTitle( $currentSheet->getCell($keys['name'].$i)->getValue() ),
			);
			if(isset($keys['ordering'])){
				$value = $currentSheet->getCell($keys['ordering'].$i)->getValue();
				if($value!='')$data['ordering']=$value;
			}
			if(isset($keys['description']) ){
				$value = $currentSheet->getCell($keys['description'].$i)->getValue();
				if($value!='')$data['description']=$value;
			}	
			if(isset($keys['ids_level_knowledge'])){
				$value = $currentSheet->getCell($keys['ids_level_knowledge'].$i)->getValue();
				if($value!='')$data['ids_level_knowledge']=$value;
			}	
			if(isset($keys['icon'])){
				$value = $currentSheet->getCell($keys['icon'].$i)->getValue();
				if($value!='')$data['icon']=$value;
			}								
			$this->insert($data);
		}
	}
	
	/**
	 * Export an Excel file , with all the subject data.
	 *
	 * @return $path filepath
	 * */
	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->c->siteName.'_'.$this->lang['exportFile']);
		
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['id_level']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->lang['ordering']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $this->lang['ids_level_knowledge']);
		$objPHPExcel->getActiveSheet()->setCellValue('E2', $this->lang['icon']);
		$objPHPExcel->getActiveSheet()->setCellValue('F2', $this->lang['description']);

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id_level']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['ordering']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['ids_level_knowledge']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['icon']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, $data[$i]['description']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		
		$file = "download/".date('YmdHis').".xls";
		if($path==null){
			$path = $this->c->filePath.$file;
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
		if($orderby==null)$orderby = " order by id_level ";
		$sql = "select ".$columns." from ".$pfx."wls_subject ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_subject ".$where;
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
	
	public function getLevelList($root){}

	/**
	 * One usergroup has participated in more than one subjects
	 * There is a many-to-many database table handle this 
	 * 
	 * @param $id_level_group
	 * @return $data
	 * */
	public function getListForGroup($id_level_group){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "
		SELECT *,(id_level in (
			select id_level_subject from ".$pfx."wls_user_group2subject where id_level_group = '".$id_level_group."' 
		))as checked FROM ".$pfx."wls_subject order by id;
		";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		return $data;
	}

	/**
	 * Can't get one user's subject list directly from user's infomation.
	 * First, get user's group data, then get subject list from the group.
	 * 
	 * @param $username See database table wls_user 
	 * @return $data a list contains id_level , username ,
	 * */
	public function getListForUser($username){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "
		select *,id_level in ( 
			select id_level_subject from ".$pfx."wls_user_group2subject where id_level_group in ( 
				select id_level_group from ".$pfx."wls_user_group2user where username = '".$username."'
			)
		) as checked from ".$pfx."wls_subject;";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		return $data;
	}
}
?>