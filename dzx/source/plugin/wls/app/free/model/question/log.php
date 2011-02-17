<?php
include_once dirname(__FILE__)."/../user.php";

class m_question_log extends wls implements dbtable,log{

	public $phpexcel;
	public $id = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['id_user'])){
			$userObj = new m_user();
			$user = $userObj->getMyInfo();
			$data['id_user'] = $user['id'];
		}
		if(!isset($data['myAnswer'])){
			$data['myAnswer'] = 'I_DONT_KNOW';
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

		$userObj = new m_user();
		$user = $userObj->getMyInfo();

		$keys = null;
		$sql = '';
		for($i=0;$i<count($datas);$i++){
			$data = $datas[$i];

			if($data['myAnswer']=='I_DONT_KNOW' || $data['type']==5)continue;
			$data['myanswer'] = $data['myAnswer'];
			unset($data['myAnswer']);
			$data['id_user'] = $user['id'];

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
				,myAnswer text 						
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

	public function importExcel($path){}

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