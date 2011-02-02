<?php
class m_user_privilege extends wls implements dbtable,levelList{

	public $phpexcel = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['description'])){
			$data['description'] = '';
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user_privilege (".$keys.") values ('".$values."');";

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

		$sql = "update ".$pfx."wls_user_privilege set ";
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

		$sql = "drop table if exists ".$pfx."wls_user_privilege;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_privilege(
			
				 id int primary key auto_increment	
				,id_level varchar(200) unique		
				,name varchar(200) default '' 		
				,money int default 0				
				,ismenu int default 0				
				,isshortcut int default 0			
				,isquickstart int default 0 		
				,icon varchar(200) default ''
				,description text				
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function importExcel($path){
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName('privilege');
		$allRow = array($currentSheet->getHighestRow());

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
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['ordering']){
				$keys['ordering'] = $i;
			}	
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['menu']){
				$keys['ismenu'] = $i;
			}	
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['desktop']){
				$keys['isquickstart'] = $i;
			}	
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['startupbar']){
				$keys['isshortcut'] = $i;
			}												
		}	
		
		$data = array();
		for($i=2;$i<=$allRow[0];$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell($keys['id_level'].$i)->getValue(),
				'name'=>$currentSheet->getCell($keys['id_level'].$i)->getValue(),
				'money'=>$currentSheet->getCell($keys['money'].$i)->getValue(),
				'ordering'=>$currentSheet->getCell($keys['ordering'].$i)->getValue(),
				'description'=>$currentSheet->getCell($keys['description'].$i)->getValue(),
				'ismenu'=>($currentSheet->getCell($keys['ismenu'].$i)->getValue()==$this->lang['yes'])?1:0,
				'isquickstart'=>($currentSheet->getCell($keys['isquickstart'].$i)->getValue()==$this->lang['yes'])?1:0,
				'isshortcut'=>($currentSheet->getCell($keys['isshortcut'].$i)->getValue()==$this->lang['yes'])?1:0,
			);
			$this->insert($data);
		}
	}

	public function exportExcel(){
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('privilege');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['id_level']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $this->lang['ordering']);
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $this->lang['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $this->lang['description']);
		$objPHPExcel->getActiveSheet()->setCellValue('F1', $this->lang['menu'] );
		$objPHPExcel->getActiveSheet()->setCellValue('G1', $this->lang['desktop'] );
		$objPHPExcel->getActiveSheet()->setCellValue('H1', $this->lang['startupbar'] );

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id_level']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['ordering']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['money']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['description']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, (($data[$i]['ismenu']==1)?$this->lang['yes']:'') );
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$index, (($data[$i]['isquickstart']==1)?$this->lang['yes']:'') );
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$index, (($data[$i]['isshortcut']==1)?$this->lang['yes']:'') );
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$objWriter->save($this->c->filePath.$file);
		return $file;
	}
	
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
				if($keys[$i]=='id_level_group'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}				
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_user_privilege ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_user_privilege ".$where;
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
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "
		SELECT *,(id_level in (
			select id_level_privilege from ".$pfx."wls_user_group2privilege where id_level_group = '".$id_level_group."' 
		))as checked FROM ".$pfx."wls_user_privilege order by id;
		";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		
		return $data;
	}

	public function getListForUser($username){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "
		select *,id_level in ( 
			select id_level_privilege from ".$pfx."wls_user_group2privilege where id_level_group in ( 
				select id_level_group from ".$pfx."wls_user_group2user where username = '".$username."'
			)
		) as checked from ".$pfx."wls_user_privilege;";
		
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['text'] = $temp['name'];
			if($temp['checked']==0){
				$temp['checked'] = false;
			}else{
				$temp['checked'] = true;
			}
			$data[] = $temp;
		}

		return $data;
	}
}
?>