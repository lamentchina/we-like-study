<?php
/**
 * 用户组操作,对应着一张数据库表
 * */
class m_user_group extends wls implements dbtable,levelList{

	public $phpexcel = null;
	public $id_level = null;

	/**
	 * 插入一条数据
	 *
	 * @param $data 一个数组,其键值与数据库表中的列一一对应
	 * @return bool
	 * */
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

	/**
	 * 只能根据编号来删除数据,一次性可以删除多条
	 *
	 * @param $ids 编号,每张表都id这个列,一般为自动递增
	 * @return bool
	 * */
	public function delete($ids){}

	/**
	 * 更新一条数据
	 *
	 * @param $data 一个数组,其键值与数据库表中的列一一对应,肯定含有$id 
	 * @return bool
	 * */
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

	/**
	 * 创建这张数据库表
	 * 创建过程中,会先尝试删除这张表,然后重新建立.
	 * 因此在运行之前需要将数据备份
	 * 如果配置文件中的state不是debug,无法执行这类函数
	 *
	 * @return bool
	 * */
	public function create(){
		if($this->c->state!='debug')return false;
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_user_group;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_group(
			
				 id int primary key auto_increment	/*自动编号*/
				,id_level varchar(200) unique		/*级层编号*/
				,name varchar(200) default '' 		/*用户组名称*/
				,ordering int default 0				/*排序规则*/
				,count_user int default 0				/*用户数*/
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);


		$sql = "drop table if exists ".$pfx."wls_user_group2privilege;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_group2privilege(
			
				 id int primary key auto_increment	/*自动编号*/
				,id_level_group varchar(200) default ''		/*用户组编号*/
				,id_level_privilege varchar(200) default '' 		/*权限编号*/
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);

		$sql = "drop table if exists ".$pfx."wls_user_group2user;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_group2user(
			
				 id int primary key auto_increment	/*自动编号*/
				,id_level_group varchar(200) default ''		/*用户组编号*/
				,username varchar(200) default '' 		/*用户名*/
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		
		$sql = "drop table if exists ".$pfx."wls_user_group2subject;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user_group2subject(
			
				 id int primary key auto_increment	/*自动编号*/
				,id_level_group varchar(200) default ''		/*用户组编号*/
				,id_level_subject varchar(200) default '' 		/*科目编号*/
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		
		return true;
	}

	/**
	 * 导入一张EXCEL,并将数据全部填充到表中去
	 * EXCEL已经成为数据存储标准,每个办公人员都会用
	 * 这是实现批导入最方便的形式
	 *
	 * @param $path EXCEL路径
	 * @return bool
	 * */
	public function importExcel($path){
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
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

//		$currentSheet = $this->phpexcel->getSheetByName('privilege');
//		$allRow = array($currentSheet->getHighestRow());
//		$data = array();
//		$index = 0;
//		for($i=2;$i<=$allRow[0];$i++){
//			$data = array(
//				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
//				'id_level_privilege'=>$currentSheet->getCell('B'.$i)->getValue(),
//			);
//			$this->linkPrivilege($data);
//		}
//
//		$currentSheet = $this->phpexcel->getSheetByName('user');
//		$allRow = array($currentSheet->getHighestRow());
//		$data = array();
//		$index = 0;
//		for($i=2;$i<=$allRow[0];$i++){
//			$data = array(
//				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
//				'username'=>$currentSheet->getCell('B'.$i)->getValue(),
//			);
//			$this->linkUser($data);
//		}
//				
//		$currentSheet = $this->phpexcel->getSheetByName('subject');
//		$allRow = array($currentSheet->getHighestRow());
//		$data = array();
//		$index = 0;
//		for($i=2;$i<=$allRow[0];$i++){
//			$data = array(
//				'id_level_group'=>$currentSheet->getCell('A'.$i)->getValue(),
//				'id_level_subject'=>$currentSheet->getCell('B'.$i)->getValue(),
//			);
//			$this->linkSubject($data);
//		}
		
	}

	/**
	 * 导出一张EXCEL文件,
	 * 提供下载,实现数据的多处同步,并让这个EXCEL文件形成标准
	 *
	 * @return $path
	 * */
	public function exportExcel(){
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('data');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '序号');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '名称');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', '排序');

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
		$file =  "file/download/".date('YmdHis').".xls";
		$objWriter->save(dirname(__FILE__)."/../../../../".$file);
		return $file;
	}

	/**
	 * 导出单条记录
	 * 包括: 这个用户组的详细内容,
	 * 这个用户组的权限
	 * 这个用户组的科目分配
	 * 所拥有的用户
	 * 依赖 id_level
	 * 
	 * @return $file 路径
	 * */
	public function exportExcelOne(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('group');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '编号');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '名称');
		$sql = "select * from ".$pfx."wls_user_group where id_level = '".$this->id_level."' ";
//		echo $sql;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $temp['id_level']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $temp['name']);

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('privilege');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '组编号');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '权限编号');			
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
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '组编号');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '用户名');			
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
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '组编号');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '科目编号');			
		$sql = "select * from ".$pfx."wls_user_group2subject where id_level_group = '".$this->id_level."' ";
		$res = mysql_query($sql,$conn);
		$index = 2;
		while($temp = mysql_fetch_assoc($res)){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $temp['id_level_group']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $temp['id_level_subject']);
			$index ++;
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "file/download/".date('YmdHis').".xls";
		$objWriter->save(dirname(__FILE__)."/../../../../".$file);
		return $file;
	}
	
	public function importExcelOne($path){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;		
		
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
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
	
	/**
	 * 累加某个值
	 *
	 * @param $column 列名称
	 * @return bool
	 * */
	public function cumulative($column){}

	/**
	 * 得到列表,
	 * 也充当了读取单行数据的角色
	 *
	 * @param $page 页码,为整数
	 * @param $pagesize 页大小
	 * @param $search 查询条件
	 * @param $orderby 排序条件
	 * @return $array
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

	/**
	 * 获得具有级层关系的列表
	 *
	 * @param $root 根元素
	 * */
	public function getLevelList($root){

	}

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

}
?>