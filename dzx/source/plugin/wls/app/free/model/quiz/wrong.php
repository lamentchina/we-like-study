<?php
include_once dirname(__FILE__).'/../quiz.php';

class m_quiz_wrong extends m_quiz implements dbtable,quizdo{

	public $phpexcel;
	public $id = null;
	public $id_user = null;
	public $id_question = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_worng (".$keys.") values ('".$values."')";

		$temp = mysql_query($sql,$conn);
		if ( $temp === false ){
			$this->cumulative('count');
			return false;
		}
		return mysql_insert_id($conn);
	}

	public function delete($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_quiz_worng where id in (".$ids.") ";
		try {
			mysql_query($sql,$conn);
			return true;
		}
		catch (Exception $ex){
			return false;
		}
	}

	public function update($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_quiz_worng set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){

		}
	}

	public function create(){

		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz_worng;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_worng(
				 id int primary key auto_increment	
				 
				,id_user int default 0 				
				,id_question int default 0 			
				,id_quiz_paper int default 0		
				,id_level_subject varchar(200) default '0' 
				,date_created datetime not null 	
				,count int default 1				
			
				,CONSTRAINT ".$pfx."wls_quiz_worng_u UNIQUE (id_user,id_question)
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function importExcel($path){}

	public function exportExcel(){}

	public function cumulative($column){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		if($column=='count'){
			$sql = "update ".$pfx."wls_quiz_worng
				set count = count + 1 
				where id_user = ".$this->id_user." and 
						id_question = ".$this->id_question.";";  

		}else{
			$sql = "update ".$pfx."wls_quiz_worng set ".$column." = ".$column."+1 where id = ".$this->id;
		}
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
	}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id_level_subject'){
					$where .= " and id_level_subject in ('".$search[$keys[$i]]."') ";
				}
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_user'){
					$where .= " and id_user in (".$search[$keys[$i]].") ";
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_quiz_worng ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
		$res = mysql_query($sql,$conn);


		include_once dirname(__FILE__).'/../subject.php';
		$obj = new m_subject();
		$data = $obj->getList(1,100);
		$data = $data['data'];
		$keys = array();
		for($i=0;$i<count($data);$i++){
			$keys[$data[$i]['id_level']] = $data[$i]['name'];
		}

		include_once dirname(__FILE__).'/../tools.php';
		$t = new tools();
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['timedif'] = $t->getTimeDif($temp['date_created']); 
			$temp['subject_name'] = $keys[$temp['id_level_subject']];
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_quiz_worng ".$where;
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
	 * Download this quiz , for client-side user to print.
	 *
	 * @param $type Could be WORD,PDF,EXCEL
	 * @return $path
	 * */
	public function exportQuiz($type){}

	/**
	 * All the wrongs work I've done
	 *
	 * @param $page 
	 * @param $pagesize 
	 * @param $search Search conditions. 
	 * @param $orderby 
	 * @return $array
	 * */
	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}

	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}

	/**
	 * Get the wrongs' questions id, only id.
	 *
	 * @param $id 
	 * @return $ids 
	 * */
	public function getQuizIds(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "select questions,id from ".$pfx."wls_quiz_worng where id = ".$this->id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		return $temp['questions'];
	}


}
?>