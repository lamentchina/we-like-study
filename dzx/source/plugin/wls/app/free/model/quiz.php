<?php
class m_quiz extends wls implements dbtable{
	
	public $count_giveup = 0;		
	public $count_right = 0;
	public $count_wrong = 0;
	public $count_manual = 0;
	public $count_total = 0;
	public $questions = array();
	public $ids_question = '';

	/**
	 * Get questions by ids.
	 * Each type of quiz, like quiz-paper , quiz-wrongs, quiz-random 
	 * These's client-side will call this
	 * 
	 * @param $ids Database table wls_question's id
	 * @return $data
	 * */
	public function getQuestions($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select 
			id,type,title,optionlength,
			option1,option2,option3,option4,option5,option6,option7,
			description,cent,id_quiz_paper,id_parent,layout,path_listen
			 from ".$pfx."wls_question where id in (".$ids.") or (id_parent !=0 and id_parent in (".$ids.")) order by id; ";

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['title'] = str_replace("__IMAGEPATH__",$this->c->filePath."images/",$temp['title']);
			$data[] = $temp;
		}
		
		return $data;
	}
	
	/**
	 * The client-side post user's quiz result to the server, 
	 * The server check the answers.
	 * 
	 * @param $myAnswers See this in each controller file , it's mostly from $_POST
	 * @return $data
	 * */
	public function getAnswers($myAnswers){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$keys = array_keys($myAnswers);
		$ids = implode(",",$keys);
		
		$sql = "select 		
				 answer
				,id
				,id_parent
				,id_quiz_paper
				,markingmethod
				,description
				,cent
				,type
				,option2
				,option3
				,option4
				,id_level_subject
				,ids_level_knowledge
				
			 from ".$pfx."wls_question where id in (".$ids.") order by id ; ";
		$res = mysql_query($sql,$conn);
		if($res==false)echo $sql;
		$data = array();		
		while($temp = mysql_fetch_assoc($res)){
			$temp['myAnswer'] = $myAnswers[$temp['id']];
			$data[] = $temp;
		}
				
		return $data;
	}	
	
	
	public function insert($data){}
	
	public function delete($ids){}
	
	public function update($data){}
	
	public function create(){
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz(
				 id int primary key auto_increment	
				,id_level_subject int default 0		
				,name_subject varchar(200) default '0' 
				
				,title varchar(200) default 'title'		
				,questions text
				
				,description varchar(200) default '0'			
				,creator varchar(200) default 'admin'		
				,date_created datetime not null 	
				
				,time_limit int default 3600		
				,score_top float default 0			
				,score_top_user varchar(200) default 0		
				,score_avg float default 0			
				,count_used int	default 0			
				
				,money int default 0				
				
				,cache_path_quiz text 				
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}
	
	public function importExcel($path){}
	
	public function exportExcel(){}
	
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){}
	
}
?>