<?php
include_once dirname(__FILE__).'/../quiz.php';
include_once dirname(__FILE__).'/../subject.php';
include_once dirname(__FILE__).'/../user.php';

class m_quiz_wrong extends wls implements dbtable{

	public $id = null;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['date_created'])){
			$data['date_created'] = date('Y-m-d H:i:s');
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_wrong (".$keys.") values ('".$values."')";

		$temp = mysql_query($sql,$conn);
		if ( $temp === false ){
			if(isset($data['id_question']) && isset($data['id_user'])){
				$this->cumulative($data['id_question'],$data['id_user']);
			}
			return false;
		}
		return mysql_insert_id($conn);
	}

	public function delete($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_quiz_wrong where id in (".$ids.") ";
		try {
			mysql_query($sql,$conn);
			return true;
		}
		catch (Exception $ex){
			return false;
		}
	}
	
	public function update($data){}

	public function create(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz_wrong;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_wrong(
				 id int primary key auto_increment	
				 
				,id_user int default 0 				
				,id_question int default 0 		
					
				,date_created datetime not null 	
				,count int default 1				
			
				,CONSTRAINT ".$pfx."wls_quiz_wrong_u UNIQUE (id_user,id_question)
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	public function cumulative($id_question,$id_user){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "update ".$pfx."wls_quiz_wrong
			set count = count + 1 
			where id_user = ".$id_user." and 
					id_question = ".$id_question.";";  
		mysql_query($sql,$conn);
	}

	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$where = " ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id_user'){
					$where .= " and ".$pfx."wls_quiz_wrong.id_user in (".$search[$keys[$i]].") ";
				}
			}
		}
		if($orderby==null)$orderby = " order by ".$pfx."wls_quiz_wrong.id";
		//TODO , it will crush if the data was huge
		$sql = "select 
				 ".$pfx."wls_quiz_wrong.count
				,".$pfx."wls_quiz_wrong.id
				,".$pfx."wls_quiz_wrong.date_created
				
				,".$pfx."wls_question.title as title_question
				,".$pfx."wls_question.type
				,".$pfx."wls_question.count_right
				,".$pfx."wls_question.count_wrong
				,".$pfx."wls_question.count_giveup
				,".$pfx."wls_question.comment_ywrong_1
				,".$pfx."wls_question.comment_ywrong_2
				,".$pfx."wls_question.comment_ywrong_3
				,".$pfx."wls_question.comment_ywrong_4
				,".$pfx."wls_question.difficulty
				,".$pfx."wls_question.markingmethod
				
				,".$pfx."wls_quiz.title as title_quiz
				,".$pfx."wls_quiz.author
				,".$pfx."wls_quiz.name_subject
				,".$pfx."wls_quiz.id_level_subject
				,".$pfx."wls_quiz.count_used
				
		from ".$pfx."wls_quiz_wrong,".$pfx."wls_question ,".$pfx."wls_quiz
					where ".$pfx."wls_quiz_wrong.id_question = ".$pfx."wls_question.id and 
					".$pfx."wls_question.id_quiz = ".$pfx."wls_quiz.id
		
		".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
//		echo $sql;
		$res = mysql_query($sql,$conn);

		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['timedif'] = $this->t->getTimeDif($temp['date_created']); 
			$temp['title_question'] = $this->t->split_title($temp['title_question'],10);
			$temp['type'] = $this->t->formatQuesType($temp['type']); 
			$arr[] = $temp;
		}

		$total = 500;

		return array(
			'page'=>$page,
			'data'=>$arr,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
		);
	}
	
	public function getMyWrongsId(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$userObj = new m_user();
		$me = $userObj->getMyInfo();
			
		$sql = "select id_question from ".$pfx."wls_quiz_wrong where id_user = ".$me['id'];
		$res = mysql_query($sql,$conn);
		$ids = '';
		while($temp = mysql_fetch_assoc($res)){
			$ids .= $temp['id_question'].',';
		}
		
		$ids = substr($ids,0,strlen($ids)-1);
		return $ids;
	}
}
?>