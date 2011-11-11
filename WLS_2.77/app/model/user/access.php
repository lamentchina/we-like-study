<?php
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_user_access extends wls implements dbtable,fileLoad{

	public $phpexcel = null;

	public function insert($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		if(!isset($data['description'])){
			$data['description'] = '';
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user_access (".$keys.") values ('".$values."');";

		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function delete($ids){}

	public function update($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_user_access set ";
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
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_user_access;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_access(
			
				 id int primary key auto_increment	
				,id_level varchar(200) unique		
				,name varchar(200) default '' 		
				,money int default 0				
				,ismenu int default 0				
				,isshortcut int default 0			
				,isquickstart int default 0 		
				,icon varchar(200) default ''
				,ordering int default 0
				,description text				
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		
		$sql = "drop table if exists ".$pfx."wls_user_group2access;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_group2access(
			
				 id int primary key auto_increment	
				,id_level_group varchar(200) default ''		
				,id_level_access varchar(200) default '' 						
				
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		$sql = "ALTER TABLE ".$pfx."wls_user_group2access ADD INDEX idx_u_g2p (id_level_group,id_level_access);";
		mysql_query($sql,$conn);
		
		
		
		return true;
	}

	public function importAll($path){
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['normal']['access']);
		$allRow = array($currentSheet->getHighestRow());
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['id_level']){
				$keys['id_level'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['description']){
				$keys['description'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['name']){
				$keys['name'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['ordering']){
				$keys['ordering'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['menu']){
				$keys['ismenu'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['desktop']){
				$keys['isquickstart'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['startupbar']){
				$keys['isshortcut'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['money']){
				$keys['money'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->il8n['user']['icon']){
				$keys['icon'] = $i;
			}
		}

		$data = array();
		for($i=3;$i<=$allRow[0];$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell($keys['id_level'].$i)->getValue(),
				'name'=>$currentSheet->getCell($keys['name'].$i)->getValue(),
			);
			if(isset($keys['money'])){
				$data['money']=$currentSheet->getCell($keys['money'].$i)->getValue();
			}
			if(isset($keys['ordering'])){
				$data['ordering']=$currentSheet->getCell($keys['ordering'].$i)->getValue();
			}
			if(isset($keys['description'])){
				$data['description']=$currentSheet->getCell($keys['description'].$i)->getValue();
			}
			if(isset($keys['ismenu'])){
				$data['ismenu']=($currentSheet->getCell($keys['ismenu'].$i)->getValue()=='√')?1:0;
			}
			if(isset($keys['isquickstart'])){
				$data['isquickstart']=($currentSheet->getCell($keys['isquickstart'].$i)->getValue()=='√')?1:0;
			}
			if(isset($keys['isshortcut'])){
				$data['isshortcut']=($currentSheet->getCell($keys['isshortcut'].$i)->getValue()=='√')?1:0;
			}	
			if(isset($keys['icon'])){
				$data['icon']=$currentSheet->getCell($keys['icon'].$i)->getValue();
			}					
			$this->insert($data);
		}
	}
	
	public function importOne($path){}
	
	public function exportOne($path=null){}

	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->lang['access']);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->cfg->siteName.'_'.$this->lang['exportFile']);

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->lang['id_level']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->lang['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->lang['ordering']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $this->lang['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('E2', $this->lang['description']);
		$objPHPExcel->getActiveSheet()->setCellValue('F2', $this->lang['menu'] );
		$objPHPExcel->getActiveSheet()->setCellValue('G2', $this->lang['desktop'] );
		$objPHPExcel->getActiveSheet()->setCellValue('H2', $this->lang['startupbar'] );
		$objPHPExcel->getActiveSheet()->setCellValue('I2', $this->lang['icon'] );

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id_level']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['ordering']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['money']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['description']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, (($data[$i]['ismenu']==1)?'√':'') );
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$index, (($data[$i]['isquickstart']==1)?'√':'') );
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$index, (($data[$i]['isshortcut']==1)?'√':'') );
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$index, $data[$i]['icon']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$objWriter->save($this->cfg->filePath.$file);
		return $this->cfg->filePath.$file;
	}

	public function cumulative($column){}

	public function getList($page=1,$pagesize=20,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_level_group'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_user_access ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_user_access ".$where;
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

	public function getListForGroup($id_level_group){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "
		SELECT *,(id_level in (
			select id_level_access from ".$pfx."wls_user_group2access where id_level_group = '".$id_level_group."' 
		))as checked FROM ".$pfx."wls_user_access order by id;
		";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			
			$data[] = $temp;
		}

		return $data;
	}

	public function getListForUser($username){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "
		select *,id_level in ( 
			select id_level_access from ".$pfx."wls_user_group2access where id_level_group in ( 
				select id_level_group from ".$pfx."wls_user_group2user where username = '".$username."'
			)
		) as checked from ".$pfx."wls_user_access;";

		
//		echo $sql;exit();
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['text'] = $temp['name'];
//			if($temp['checked']==0){
//				$temp['checked'] = false;
//			}else{
//				$temp['checked'] = true;
//			}

			$data[] = $temp;
		}

		return $data;
	}
}
?>