<?php
include_once dirname(__FILE__)."/../user.php";
include_once dirname(__FILE__)."/../quiz/wrong.php";
include_once dirname(__FILE__)."/../subject/log.php";

class m_question_log extends wls implements dbtable,log{

	public $phpexcel;
	public $id = null;

	public function insert($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		if(!isset($data['id_quiz'])){
			die(' id_quiz missed in m_question_log::insert ');
		}
		if(!isset($data['id_quiz_log'])){
			die(' id_quiz_log missed in m_question_log::insert ');
		}

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
		if(!isset($data['description'])){
			$data['description'] = ' ';
		}	
		if(!isset($data['comment'])){
			$data['comment'] = ' ';
		}	
		if(!isset($data['date_created'])){
			$data['date_created'] = date('Y-m-d H:i:s');
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
		$pfx = $this->cfg->dbprefix;
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

	public function update($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_question_log set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;

		mysql_query($sql,$conn);
		echo $sql;
		return true;			
	}

	public function create(){

		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_question_log;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_question_log(
				 id int primary key auto_increment	
				 
				,date_created datetime default '1987-03-18'					 
				,id_user int default 0		
				,ids_level_user_group varchar(200) default '0'		
				,id_quiz int default 0
				,id_quiz_log int default 0	
				,type int default 0
				,markingmethod int default 0				
				,myAnswer text 						
				,answer text 						
				,correct int default 0						
				,cent float default 0			
				,mycent float default 0			
				,id_question int default 0			

				,application int default 0			
				,description text 
				
				,comment text 
				,markked int default '0'
				,id_user_markkedBy int default '0' 
				
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function importExcel($path){}

	public function exportExcel(){}

	public function cumulative($column){}

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

	/**
	 * Add question log , then add the knowledge log.
	 * And other work for static.
	 * TODO
	 * 
	 * @param $whatHappened   a big array contains all the answers stuff
	 * */
	public function addLog($whatHappened){
		$answerData = $whatHappened;
		$knowledgeLogObj = new m_subject_log();
		$return = 0;
		$wrongObj = new m_quiz_wrong();
		if($answerData['myAnswer']=='I_DONT_KNOW'){
			$answerData['correct'] = -1;
			$return = -1;
		}else if($answerData['myAnswer']==$answerData['answer']){
			$answerData['correct'] = 1;
			$answerData['mycent'] = $answerData['cent'];
			
			$knowledgeLogData = array(
				 'id_user'=>$answerData['id_user']
				,'result'=>'right'
				,'ids_level_user_group'=>$answerData['ids_level_user_group']
				,'id_question'=>$answerData['id_question']
			);
			if(isset($whatHappened['date_created'])){
				$knowledgeLogData['date_created'] = substr($whatHappened['date_created'],0,13).":00:00";
			}
			$knowledgeLogObj->addLog($knowledgeLogData);
			
			$return = 1;
		}else{
			$wrongData = array(
				 'id_user'=>$answerData['id_user']
				,'id_question'=>$answerData['id_question']
			);
			$knowledgeLogData = array(
				 'id_user'=>$answerData['id_user']
				,'result'=>'wrong'
				,'ids_level_user_group'=>$answerData['ids_level_user_group']
				,'id_question'=>$answerData['id_question']
			);
			if(isset($whatHappened['date_created'])){
				$knowledgeLogData['date_created'] = substr($whatHappened['date_created'],0,13).":00:00";
			}
			$knowledgeLogObj->addLog($knowledgeLogData);
			$wrongObj->insert($wrongData);
			$answerData['correct'] = 0;
			$return = 0;
		}
		$this->insert($answerData);
		return $return;
	}
	
	public function getMarkInfo($id_quiz_log,$index){
				$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_quiz_log where id = ".$id_quiz_log;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		
		$arr = explode(",",$temp['ids_question']);
		$id_question = $arr[$index-1];
		
		$sql = "select comment,mycent,correct,markked from ".$pfx."wls_question_log where id_quiz_log =".$id_quiz_log." and id_question =".$id_question;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		
		return $temp;
	}
}
?>