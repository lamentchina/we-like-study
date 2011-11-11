<?php
include_once dirname(__FILE__).'/access.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../user.php';

include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class m_user_group extends wls implements dbtable,fileLoad{

	public $phpexcel = null;
	public $id_level = null;

	public function insert($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user_group (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	public function linkUser($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "insert into ".$pfx."wls_user_group2user (id_level_group,username) values ('".$data['id_level_group']."','".$data['username']."');";
		mysql_query($sql,$conn);

		$sql = "update ".$pfx."wls_user_group set count_user = (select count(*) from ".$pfx."wls_user_group2user  where id_level_group = '".$data['id_level_group']."' ) where id_level = '".$data['id_level_group']."'";
		mysql_query($sql,$conn);
	}

	public function linkExam($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "insert into ".$pfx."wls_user_group2exam (id_level_group,id_exam) values ('".$data['id_level_group']."','".$data['id_exam']."');";
		mysql_query($sql,$conn);

	}

	public function linkAccess($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "insert into ".$pfx."wls_user_group2access (id_level_group,id_level_access) values ('".$data['id_level_group']."','".$data['id_level_access']."');";
		mysql_query($sql,$conn);
	}

	public function linkSubject($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user_group2subject (".$keys.") values ('".$values."');";
		mysql_query($sql,$conn);
	}

	public function linkSubject2Teacher($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user_group2subject2teacher (".$keys.") values ('".$values."');";
		mysql_query($sql,$conn);
	}

	public function updateSubject($id,$ids_subject){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user_group2subject where id_level_group = '".$id."' ;";
		mysql_query($sql,$conn);
		$arr = explode(",",$ids_subject);
		for($i=0;$i<count($arr);$i++){
			$data = array(
				'id_level_group'=>$id,
				'id_level_subject'=>$arr[$i]
			);
			$this->linkSubject($data);
		}
	}

	public function updateAccess($id,$ids_access){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user_group2access where id_level_group = '".$id."' ;";
		mysql_query($sql,$conn);
		$arr = explode(",",$ids_access);
		for($i=0;$i<count($arr);$i++){
			$data = array(
				'id_level_group'=>$id,
				'id_level_access'=>$arr[$i]
			);
			$this->linkaccess($data);
		}
	}

	public function delete($ids){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user_group where id  in (".$ids.");";
		$res = mysql_query($sql,$conn);
		return $res;
	}

	public function update($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_user_group set ";
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

	public function setLeaf(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select * from ".$pfx."wls_user_group order by id_level";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
			$sql2 = "update w_wls_user_group set count_user = (select count(*) from w_wls_user_group2user where id_level_group = '".$temp['id_level']."') where id_level = '".$temp['id_level']."';";
			mysql_query($sql2,$conn);
		}

		$ids = '';
		for($i=0;$i<count($data)-1;$i++){
			if(substr($data[$i+1]['id_level'],0,strlen($data[$i+1]['id_level'])-2) == $data[$i]['id_level']){
				$ids.= "'".$data[$i]['id_level']."',";
			}
		}
		if(strlen($data[count($data)-1]['id_level'])== $data[count($data)-2]['id_level']){
			$ids.= "'".$data[count($data)-1]['id_level']."',";
		}
		$ids = substr($ids,0,strlen($ids)-1);

		$sql = "update ".$pfx."wls_user_group set isleaf = 1 ;";
		mysql_query($sql,$conn);

		$sql = "update ".$pfx."wls_user_group set isleaf = 0 where id_level in (".$ids.") ";
		//		echo $sql;
		mysql_query($sql,$conn);
	}

	public function create($table=null){

		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		if($table==null||$table=='wls_user_group'){
			$sql = "drop table if exists ".$pfx."wls_user_group;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group(
				
					 id int primary key auto_increment	
					,id_level varchar(200) unique		
					,icon varchar(200) default ''		
					,name varchar(200) default '' 		
					,ordering int default 0				
					,count_user int default 0
					,isleaf int default 1
								
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);
		}

		return true;
	}
	
	private function addDefaultData(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "insert into ".$pfx."wls_user_group (id_level,name) values ('10','管理员')";
		mysql_query($sql,$conn);
	}

	public function importAll($path){
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['user']['userGroup']);
		$allRow = array($currentSheet->getHighestRow());

		$data = array();
		for($i=3;$i<=$allRow[0];$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell('A'.$i)->getValue(),
				'name'=>$currentSheet->getCell('B'.$i)->getValue(),
				'ordering'=>$currentSheet->getCell('C'.$i)->getValue(),
			);
			$this->insert($data);
		}
	}

	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->il8n['user']['userGroup']);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->cfg->siteName.'_'.$this->il8n['user']['exportFile']);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF808080');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->il8n['normal']['id']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->il8n['normal']['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->il8n['user']['ordering']);

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id_level']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['ordering']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A3');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+2));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file = "download/".date('YmdHis').".xls";
		if($path==null){
			$path = $this->cfg->filePath.$file;
		}
		$objWriter->save($path);
		return $file;
	}

	public function exportOne($path=null){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('group');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->il8n['normal']['id']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->il8n['normal']['name']);
		$sql = "select * from ".$pfx."wls_user_group where id_level = '".$this->id_level."' ";
		//		echo $sql;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $temp['id_level']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $temp['name']);

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('access');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->il8n['user']['id_level_group']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->il8n['user']['id_level_access']);
		$sql = "select * from ".$pfx."wls_user_group2access where id_level_group = '".$this->id_level."' ";
		$res = mysql_query($sql,$conn);
		$index = 2;
		while($temp = mysql_fetch_assoc($res)){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $temp['id_level_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $temp['id_level_access']);
			$index ++;
		}

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(2);
		$objPHPExcel->getActiveSheet()->setTitle('user');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->il8n['user']['id_level_group']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->il8n['user']['username']);
		$sql = "select * from ".$pfx."wls_user_group2user where id_level_group = '".$this->id_level."' ";
		$res = mysql_query($sql,$conn);
		$index = 2;
		while($temp = mysql_fetch_assoc($res)){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $temp['id_level_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $temp['username']);
			$index ++;
		}

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(3);
		$objPHPExcel->getActiveSheet()->setTitle('subject');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->il8n['user']['id_level_group']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->il8n['user']['id_level_subject']);
		$sql = "select * from ".$pfx."wls_user_group2subject where id_level_group = '".$this->id_level."' ";
		$res = mysql_query($sql,$conn);
		$index = 2;
		while($temp = mysql_fetch_assoc($res)){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $temp['id_level_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $temp['id_level_subject']);
			$index ++;
		}

		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$objWriter->save($this->cfg->filePath.$file);
		return $file;
	}

	public function importOne($path){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName('group');
		$this->id_level = $currentSheet->getCell('A2')->getValue();

		$currentSheet = $this->phpexcel->getSheetByName('access');
		$sql = "delete from ".$pfx."wls_user_group2access where id_level_group = '".$this->id_level."' ";
		mysql_query($sql,$conn);
		$allRow = array($currentSheet->getHighestRow());
		$data = array();
		$index = 0;
		for($i=2;$i<=$allRow[0];$i++){
			$data = array(
				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
				'id_level_access'=>$currentSheet->getCell('B'.$i)->getValue(),
			);
			$keys = array_keys($data);
			$keys = implode(",",$keys);
			$values = array_values($data);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_user_group2access (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
		}

		$currentSheet = $this->phpexcel->getSheetByName('user');
		$sql = "delete from ".$pfx."wls_user_group2user where id_level_group = '".$this->id_level."' ";
		mysql_query($sql,$conn);
		$allRow = array($currentSheet->getHighestRow());
		$data = array();
		$index = 0;
		for($i=2;$i<=$allRow[0];$i++){
			$data = array(
				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
				'username'=>$currentSheet->getCell('B'.$i)->getValue(),
			);
			$keys = array_keys($data);
			$keys = implode(",",$keys);
			$values = array_values($data);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_user_group2user (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
		}

		$currentSheet = $this->phpexcel->getSheetByName('subject');
		$sql = "delete from ".$pfx."wls_user_group2subject where id_level_group = '".$this->id_level."' ";
		mysql_query($sql,$conn);
		$allRow = array($currentSheet->getHighestRow());
		$data = array();
		$index = 0;
		for($i=2;$i<=$allRow[0];$i++){
			$data = array(
				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
				'id_level_subject'=>$currentSheet->getCell('B'.$i)->getValue(),
			);
			$keys = array_keys($data);
			$keys = implode(",",$keys);
			$values = array_values($data);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_user_group2subject (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
		}
	}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_'){
					if($search[$keys[$i]]!=''){
						$sql = "select * from ".$pfx."wls_user_group where id = ".$search[$keys[$i]];
						$res = mysql_query($sql,$conn);
						$temp = mysql_fetch_assoc($res);
						$where .= " and id_level like '".$temp['id_level']."__' ";
					}else{
						$where .= " and id_level like '__' ";
					}
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_user_group ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_user_group ".$where;
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

	public function getListForUser($username){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "
		SELECT *,(id_level in (
			select id_level_group from ".$pfx."wls_user_group2user where username = '".$username."' 
		))as checked FROM ".$pfx."wls_user_group order by id;
		";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		return $data;
	}

	public function importExcelWithAccess($path){
		if($this->phpexcel==null){		
			$objPHPExcel = new PHPExcel();
			$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
			$PHPReader->setReadDataOnly(true);
			$this->phpexcel = $PHPReader->load($path);
		}
		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['user']['AccessToGroup']);

		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->il8n['user']['name']){
				$grouppoint = ++$i;
				break;
			}
		}

		$groupsData = array();
		for($i=$grouppoint;$i<=$allColmun;$i++){
			$data = array(
				'name'=>$this->tool->formatTitle($currentSheet->getCell($i."1")->getValue()),
				'id_level'=>$currentSheet->getCell($i."2")->getValue()
			);
			$groupsData[] = $data;
			$this->insert($data);
		}
		$this->setLeaf();

		$keys = array();
		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['money']){
				$keys['money'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['normal']['icon']){
				$keys['icon'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['normal']['description']){
				$keys['description'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['normal']['name']){
				$keys['name'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['id_level']){
				$keys['id_level'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['isshortcut']){
				$keys['isshortcut'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['isquickstart']){
				$keys['isquickstart'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['ismenu']){
				$keys['ismenu'] = $i;
			}
		}
		
		$accesssData = array();
		$accessObj = new m_user_access();
		$accessObj->create();

		for($i=4;$i<=$allRow;$i++){
			$name = $this->tool->formatTitle($currentSheet->getCell($keys['name'].$i)->getValue());
			$name = str_replace("<br/>&nbsp;&nbsp;","",$name);
			$data = array(
				'name'=>$name,
				'id_level'=>$currentSheet->getCell($keys['id_level'].$i)->getValue(),
				'ismenu'=>($currentSheet->getCell($keys['ismenu'].$i)->getValue()=='√')?1:0,
				'isshortcut'=>($currentSheet->getCell($keys['isshortcut'].$i)->getValue()=='√')?1:0,
				'isquickstart'=>($currentSheet->getCell($keys['isquickstart'].$i)->getValue()=='√')?1:0,
				'icon'=>$currentSheet->getCell($keys['icon'].$i)->getValue(),
				'description'=>$currentSheet->getCell($keys['description'].$i)->getValue(),
				'money'=>$currentSheet->getCell($keys['money'].$i)->getValue(),
			);

			$accesssData[] = $data ;
			$accessObj->insert($data);
		}

		if($this->cfg->cmstype==''){
			$data = array(
				'name'=>$this->il8n['user']['login'],
				'id_level'=>'23',
				'ismenu'=>1,
				'isshortcut'=>1,
				'isquickstart'=>1,
				'icon'=>'key',
				'description'=>'WLS was installed alone, user can login from here',
				'money'=>'0'
				);
				$accesssData[] = $data ;
				$accessObj->insert($data);
		}else if($this->cfg->cmstype=='DiscuzX'){
			$data = array(
				'name'=>'DiscuzX',
				'id_level'=>'90',
				'ismenu'=>1,
				'isshortcut'=>1,
				'isquickstart'=>1,
				'icon'=>'discuzx',
				'description'=>'DiscuzX',
				'money'=>'0'
				);
				$accesssData[] = $data ;
				$accessObj->insert($data);
		}else if($this->cfg->cmstype=='Joomla' || $this->cfg->cmstype=='Joomla16'){
			$data = array(
				'name'=>'Joomla',
				'id_level'=>'90',
				'ismenu'=>1,
				'isshortcut'=>1,
				'isquickstart'=>1,
				'icon'=>'joomla',
				'description'=>'Joomla',
				'money'=>'0'
				);
				$accesssData[] = $data ;
				$accessObj->insert($data);
		}else if($this->cfg->cmstype=='PhpWind'){
			$data = array(
				'name'=>'PhpWind',
				'id_level'=>'90',
				'ismenu'=>1,
				'isshortcut'=>1,
				'isquickstart'=>1,
				'icon'=>'PhpWind',
				'description'=>'PhpWind',
				'money'=>'0'
			);
			$accesssData[] = $data ;
			$accessObj->insert($data);
		}

		for($i2=$grouppoint;$i2<=$allColmun;$i2++){
			for($i=4;$i<=$allRow;$i++){
				if($currentSheet->getCell($i2.$i)->getValue()=='√'){
					$data = array(
						'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
						'id_level_access'=>$accesssData[$i-4]['id_level']
					);
					$this->linkAccess($data);
				}
			}
			if($this->cfg->cmstype==''){
				$this->linkAccess(array(
					'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
					'id_level_access'=>'23'
					));
			}else{
				$this->linkAccess(array(
					'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
					'id_level_access'=>'90'
					));
			}
			$this->linkAccess($data);
		}
	}

	public function importExcelWithSubject($path){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$this->create('wls_user_group2subject');

		if($this->phpexcel==null){		
			$objPHPExcel = new PHPExcel();
			$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
			$this->phpexcel = $PHPReader->load($path);
		}
		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['user']['SubjectToGroup']);

		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->il8n['user']['name']){
				$grouppoint = ++$i;
				break;
			}
		}

		$groupsData = array();
		for($i=$grouppoint;$i<=$allColmun;$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell($i."2")->getValue()
			);
			$groupsData[] = $data;
		}

		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['id']){
				$c_level = $i;
			}
		}

		$subjectObj = new m_subject();
		$subjectObj->create();
		$subjectObj->phpexcel = array(
			 'currentSheet'=>$currentSheet
			,'allRow'=>$allRow
			,'allColmun'=> chr(ord($grouppoint)-2)
			,'keysRow'=>3
		);
		$subjectsData = $subjectObj->importAll(null);

		$p2s = array();
		for($i=4;$i<=$allRow;$i++){
			for($i2=$grouppoint;$i2<=$allColmun;$i2++){
				if($currentSheet->getCell($i2.$i)->getValue()=='√'){
					$data = array(
						'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
						'id_level_subject'=>$subjectsData[$i-4]['id_level']
					);
					$p2s[] = $data ;
					$this->linkSubject($data);
						
					$comments = $currentSheet->getComment($i2.$i)->getText();
					if($comments!=''){
//						echo $comments;
//						$comments = str_replace($this->il8n['user']['instructor'].":","",$comments);
						
						$sql = "select id from ".$pfx."wls_user where username = '".$comments."' ";
						$res = mysql_query($sql,$conn);
						$temp = mysql_fetch_assoc($res);
//						print_r($temp);
						$data = array(
							'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level']
							,'id_level_subject'=>$subjectsData[$i-4]['id_level']
							,'id_user'=>$temp['id']
						);
						$this->linkSubject2Teacher($data);
					}						
				}
			}
		}
	}

	public function importExcelWithUser($path){
		$this->create('wls_user_group2user');
		if($this->phpexcel==null){	
			$objPHPExcel = new PHPExcel();
			$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
			$PHPReader->setReadDataOnly(true);
			$this->phpexcel = $PHPReader->load($path);
		}
		$currentSheet = $this->phpexcel->getSheetByName($this->il8n['user']['UserToGroup']);

		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->il8n['user']['name']){
				$grouppoint = ++$i;
				break;
			}
		}
		$groupsData = array();
		for($i=$grouppoint;$i<=$allColmun;$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell($i."2")->getValue()
			);
			$groupsData[] = $data;
		}
		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()==$this->il8n['user']['id']){
				$c_level = $i;
			}
		}

		$userObj = new m_user();
		$userObj->create();
		$userObj->phpexcel = array(
			 'currentSheet'=>$currentSheet
			,'allRow'=>$allRow
			,'allColmun'=> chr(ord($grouppoint)-2)
			,'keysRow'=>3
		);
		$usersData = $userObj->importAll(null);

		for($i=4;$i<=$allRow;$i++){
			for($i2=$grouppoint;$i2<=$allColmun;$i2++){
				if($currentSheet->getCell($i2.$i)->getValue()=='√'){
					$data = array(
						'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
						'username'=>$usersData[$i-4]['username']
					);

					$this->linkUser($data);
				}
			}
		}
	}
}
?>