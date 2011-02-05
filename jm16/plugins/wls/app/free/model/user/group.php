<?php
class m_user_group extends wls implements dbtable,levelList{

	public $phpexcel = null;
	public $id_level = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
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
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "insert into ".$pfx."wls_user_group2user (id_level_group,username) values ('".$data['id_level_group']."','".$data['username']."');";
		mysql_query($sql,$conn);

		$sql = "update ".$pfx."wls_user_group set count_user = (select count(*) from ".$pfx."wls_user_group2user  where id_level_group = '".$data['id_level_group']."' ) where id_level = '".$data['id_level_group']."'";
		mysql_query($sql,$conn);
	}

	public function linkPrivilege($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "insert into ".$pfx."wls_user_group2privilege (id_level_group,id_level_privilege) values ('".$data['id_level_group']."','".$data['id_level_privilege']."');";
		mysql_query($sql,$conn);
	}

	public function linkSubject($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user_group2subject (".$keys.") values ('".$values."');";
		mysql_query($sql,$conn);
	}

	public function updateSubject($id,$ids_subject){
		$pfx = $this->c->dbprefix;
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

	public function updatePrivilege($id,$ids_privilege){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user_group2privilege where id_level_group = '".$id."' ;";
		mysql_query($sql,$conn);
		$arr = explode(",",$ids_privilege);
		for($i=0;$i<count($arr);$i++){
			$data = array(
				'id_level_group'=>$id,
				'id_level_privilege'=>$arr[$i]
			);
			$this->linkPrivilege($data);
		}
	}

	public function delete($ids){}

	public function update($data){
		$pfx = $this->c->dbprefix;
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

	public function create($table=null){

		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		if($table==null||$table=='wls_user_group'){
			$sql = "drop table if exists ".$pfx."wls_user_group;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group(
				
					 id int primary key auto_increment	
					,id_level varchar(200) unique		
					,name varchar(200) default '' 		
					,ordering int default 0				
					,count_user int default 0
								
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);
		}

		if($table==null||$table=='wls_user_group2privilege'){
			$sql = "drop table if exists ".$pfx."wls_user_group2privilege;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group2privilege(
				
					 id int primary key auto_increment	
					,id_level_group varchar(200) default ''		
					,id_level_privilege varchar(200) default '' 						
					
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);
			$sql = "ALTER TABLE ".$pfx."wls_user_group2privilege ADD INDEX idx_u_g2p (id_level_group,id_level_privilege);";
			mysql_query($sql,$conn);
		}

		if($table==null||$table=='wls_user_group2user'){
			$sql = "drop table if exists ".$pfx."wls_user_group2user;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group2user(
				
					 id int primary key auto_increment	
					,id_level_group varchar(200) default ''		
					,username varchar(200) default '' 		
								
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);
			$sql = "ALTER TABLE ".$pfx."wls_user_group2user ADD INDEX idx_u_g2u (id_level_group,username);";
			mysql_query($sql,$conn);
		}

		if($table==null||$table=='wls_user_group2subject'){
			$sql = "drop table if exists ".$pfx."wls_user_group2subject;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group2subject(
				
					 id int primary key auto_increment	
					,id_level_group varchar(200) default ''		
					,id_level_subject varchar(200) default '' 		
												
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);
			$sql = "ALTER TABLE ".$pfx."wls_user_group2subject ADD INDEX idx_u_g2s (id_level_group,id_level_subject);";
			mysql_query($sql,$conn);
		}

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

		$currentSheet = $this->phpexcel->getSheetByName('group');
		$allRow = array($currentSheet->getHighestRow());
		$data = array();
		$index = 0;
		for($i=2;$i<=$allRow[0];$i++){
			$data = array(
				'id_level'=>$currentSheet->getCell('A'.$i)->getValue(),
				'name'=>$currentSheet->getCell('B'.$i)->getValue(),
				'ordering'=>$currentSheet->getCell('C'.$i)->getValue(),
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
		$objPHPExcel->getActiveSheet()->setTitle('data');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['id']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $this->lang['ordering']);

		$index = 1;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id_level']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['ordering']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'A1:A'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$objWriter->save($this->c->filePath.$file);
		return $file;
	}

	public function exportExcelOne(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('group');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['id']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['name']);
		$sql = "select * from ".$pfx."wls_user_group where id_level = '".$this->id_level."' ";
		//		echo $sql;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $temp['id_level']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $temp['name']);

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('privilege');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['id_level_group']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['id_level_privilege']);			
		$sql = "select * from ".$pfx."wls_user_group2privilege where id_level_group = '".$this->id_level."' ";
		$res = mysql_query($sql,$conn);
		$index = 2;
		while($temp = mysql_fetch_assoc($res)){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $temp['id_level_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $temp['id_level_privilege']);
			$index ++;
		}

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(2);
		$objPHPExcel->getActiveSheet()->setTitle('user');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['id_level_group']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['username']);			
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
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['id_level_group']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['id_level_subject']);			
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
		$objWriter->save($this->c->filePath.$file);
		return $file;
	}

	public function importExcelOne($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName('group');
		$this->id_level = $currentSheet->getCell('A2')->getValue();

		$currentSheet = $this->phpexcel->getSheetByName('privilege');
		$sql = "delete from ".$pfx."wls_user_group2privilege where id_level_group = '".$this->id_level."' ";
		mysql_query($sql,$conn);
		$allRow = array($currentSheet->getHighestRow());
		$data = array();
		$index = 0;
		for($i=2;$i<=$allRow[0];$i++){
			$data = array(
				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
				'id_level_privilege'=>$currentSheet->getCell('B'.$i)->getValue(),
			);
			$keys = array_keys($data);
			$keys = implode(",",$keys);
			$values = array_values($data);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_user_group2privilege (".$keys.") values ('".$values."')";
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

	public function getLevelList($root){}

	public function getListForUser($username){
		$pfx = $this->c->dbprefix;
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

	public function importExcelWithP($path){
		$this->create();

		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);
		$currentSheet = $this->phpexcel->getSheetByName($this->lang['PrivilegeToGroup']);

		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['name']){
				$grouppoint = ++$i;
				break;
			}
		}

		$groupsData = array();
		for($i=$grouppoint;$i<=$allColmun;$i++){
			$data = array(
				'name'=>$this->t->formatTitle($currentSheet->getCell($i."1")->getValue()),
				'id_level'=>$currentSheet->getCell($i."2")->getValue()
			);
			$groupsData[] = $data;
			$this->insert($data);
		}

		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['cost']){
				$c_money = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['icon']){
				$c_icon = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['description']){
				$c_desc = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['name']){
				$c_name = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['id']){
				$c_level = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['desktop']){
				$c_shortcut = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['startupbar']){
				$c_quickstar = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['menu']){
				$c_menu = $i;
			}
		}
		$privilegesData = array();
		include_once dirname(__FILE__).'/privilege.php';
		$privilegeObj = new m_user_privilege();
		$privilegeObj->create();
		for($i=4;$i<=$allRow;$i++){
			$data = array(
				'name'=>$this->t->formatTitle($currentSheet->getCell($c_name.$i)->getValue()),
				'id_level'=>$currentSheet->getCell($c_level.$i)->getValue(),
				'ismenu'=>($currentSheet->getCell($c_menu.$i)->getValue()=='√')?1:0,
				'isshortcut'=>($currentSheet->getCell($c_shortcut.$i)->getValue()=='√')?1:0,
				'isquickstart'=>($currentSheet->getCell($c_quickstar.$i)->getValue()=='√')?1:0,
				'icon'=>$currentSheet->getCell($c_icon.$i)->getValue(),
				'description'=>$currentSheet->getCell($c_desc.$i)->getValue(),
				'money'=>$currentSheet->getCell($c_money.$i)->getValue(),
			);
			$privilegesData[] = $data ;
			$privilegeObj->insert($data);
		}

		$p2p = array();
		for($i=4;$i<=$allRow;$i++){
			for($i2=$grouppoint;$i2<=$allColmun;$i2++){
				if($currentSheet->getCell($i2.$i)->getValue()=='√'){
					$data = array(
						'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
						'id_level_privilege'=>$privilegesData[$i-4]['id_level']
					);
					$p2p[] = $data ;
					$this->linkPrivilege($data);
				}
			}
		}
	}

	public function importExcelWithS($path){
		$this->create('wls_user_group2subject');
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);
		$currentSheet = $this->phpexcel->getSheetByName($this->lang['SubjectToGroup']);

		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['name']){
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
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['id']){
				$c_level = $i;
			}
		}

		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['icon']){
				$c_icon = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['description']){
				$c_desc = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['name']){
				$c_name = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['id']){
				$c_level = $i;
			}
		}
		$subjectsData = array();
		include_once dirname(__FILE__).'/../subject.php';
		$subjectObj = new m_subject();
		$subjectObj->create();
		for($i=4;$i<=$allRow;$i++){
			$data = array(
				'name'=>$this->t->formatTitle($currentSheet->getCell($c_name.$i)->getValue()),
				'id_level'=>$currentSheet->getCell($c_level.$i)->getValue(),
				'icon'=>$currentSheet->getCell($c_icon.$i)->getValue(),
				'description'=>$currentSheet->getCell($c_desc.$i)->getValue(),
			);
			$subjectsData[] = $data ;
			$subjectObj->insert($data);
		}

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
				}
			}
		}
	}

	public function importExcelWithU($path){
		$this->create('wls_user_group2user');
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);
		$currentSheet = $this->phpexcel->getSheetByName($this->lang['UserToGroup']);

		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()==$this->lang['name']){
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
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['id']){
				$c_level = $i;
			}
		}

		$columns = array();
		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['username']){
				$columns['username'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['birthday']){
				$columns['birthday'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()=='QQ'){
				$columns['qq'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['sex']){
				$columns['sex'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['realname']){
				$columns['name'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['photo']){
				$columns['photo'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['money']){
				$columns['money'] = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()==$this->lang['password']){
				$columns['password'] = $i;
			}
		}
		
		$userData = array();
		include_once dirname(__FILE__).'/../user.php';
		$userObj = new m_user();
		$userObj->create();
		for($i=4;$i<=$allRow;$i++){
			$data = array(
				'username'=>$currentSheet->getCell($columns['username'].$i)->getValue(),
				'birthday'=>$currentSheet->getCell($columns['birthday'].$i)->getValue(),
				'qq'=>$currentSheet->getCell($columns['qq'].$i)->getValue(),
				'sex'=>$currentSheet->getCell($columns['sex'].$i)->getValue(),
				'name'=>$currentSheet->getCell($columns['name'].$i)->getValue(),
				'photo'=>$currentSheet->getCell($columns['photo'].$i)->getValue(),
				'money'=>$currentSheet->getCell($columns['money'].$i)->getValue(),
				'password'=>$currentSheet->getCell($columns['password'].$i)->getValue(),
			);
			$userData[] = $data ;
			$userObj->insert($data);
		}
	
		$p2u = array();
		for($i=4;$i<=$allRow;$i++){
			for($i2=$grouppoint;$i2<=$allColmun;$i2++){
				if($currentSheet->getCell($i2.$i)->getValue()=='√'){
					$data = array(
						'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
						'username'=>$userData[$i-4]['username']
					);
					$p2u[] = $data ;
					$this->linkUser($data);
				}
			}
		}
	}
}
?>