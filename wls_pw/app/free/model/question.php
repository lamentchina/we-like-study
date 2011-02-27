<?php
class m_question extends wls implements dbtable{

	public $phpexcel;
	public $id;

	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['title'])){
			$data['title'] = 'question title missed';
		}

		if( !isset($data['cent']) || $data['cent']==''){
			$data['cent'] = '0';
		}
		if(!isset($data['optionlength']) || $data['optionlength']==''){
			$data['optionlength'] = '0';
		}
		if(!isset($data['answer'])){
			$data['answer'] = 'A';
		}
		if(!isset($data['option1'])){
			$data['option1'] = ' ';
		}
		if(!isset($data['option2'])){
			$data['option2'] = ' ';
		}
		if(!isset($data['option3'])){
			$data['option3'] = ' ';
		}
		if(!isset($data['option4'])){
			$data['option4'] = ' ';
		}
		if(!isset($data['option5'])){
			$data['option5'] = ' ';
		}
		if(!isset($data['option6'])){
			$data['option6'] = ' ';
		}
		if(!isset($data['option7'])){
			$data['option7'] = ' ';
		}
		if(!isset($data['description'])){
			$data['description'] = $this->lang['ques_description'];
		}
		if(!isset($data['date_created'])){
			$data['date_created'] = date('Y-m-d H:i:s');
		}
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";

		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		return $id;
	}

	public function delete($ids){}

	public function update($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_question set ";
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

		$sql = "drop table if exists ".$pfx."wls_question;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_question(
				  id int primary key auto_increment	

				,type int default 1					
				,title text not null				
				,answer text not null				
				,optionlength int default 0			
				,option1 text						
				,option2 text
				,option3 text
				,option4 text
				,option5 text
				,option6 text
				,option7 text							
				
				,description text					
				,cent float default 0				
				,layout int default 1 /*1 vertical 0 horizonal */
					
				,id_quiz int default	0		
				,id_parent int default 0			/*If it's a big question , mixed quesiton or depict question*/
				,path_listen varchar(200) default '0'  			/*If this question has listenning file*/								

				,date_created datetime not null 		

				,count_used int default 0			
				,count_right int default 0			
				,count_wrong int default 0 			
				,count_giveup int default 0 		
				
				,comment_ywrong_1 int default 0		/*Why I'm wrong : My knowledge is not good enough*/
				,comment_ywrong_2 int default 0		/*                I'm too careless*/
				,comment_ywrong_3 int default 0		/*                I have no iedea why I'm worng*/
				,comment_ywrong_4 int default 0		/*                I'm not worng at all! The 'right-answer' is wrong!*/

				,difficulty int default 0	
				,markingmethod int default 0		
				
				,ids_level_knowledge varchar(200) default '0' 
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	/**
	 * Get questions by ids.
	 * Each type of quiz, like quiz-paper , quiz-wrongs, quiz-random
	 * These's client-side will call this
	 *
	 * @param $ids Database table wls_question's id
	 * @return $data
	 * */
	public function getByIds($ids){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "select
			id,type,title,optionlength,
			option1,option2,option3,option4,option5,option6,option7,
			description,cent,id_quiz,id_parent,layout,path_listen
			 from ".$pfx."wls_question where id in (".$ids.") or (id_parent !=0 and id_parent in (".$ids.")) order by id; ";

		$res = mysql_query($sql,$conn);
		if($res==false)die($sql);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			if($temp==false)die($sql);
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

				,markingmethod
				,description
				,cent
				,type
				,option2
				,option3
				,option4
				
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


	/**
	 * Cumulative one column,
	 * Like:
	 *  comment_ywrong_1
	 *  comment_ywrong_2
	 *  comment_ywrong_3
	 *  comment_ywrong_4
	 *  count_used
	 *  count_right
	 *  count_wrong
	 *  count_giveup
	 *
	 * @param $column
	 * @return bool
	 * */
	public function cumulative($column){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "update ".$pfx."wls_question set ".$column." = ".$column."+1 where id = ".$this->id;
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
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_quiz_paper'){
					$where .= " and id_quiz_paper in (".$search[$keys[$i]].") ";
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_question ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_question ".$where;
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
	 * Insert a lot questions one time.
	 * When the client-side upload a paper
	 *
	 * @param $questions array
	 * @return $questions A big array
	 * */
	public function insertMany($questions){
		$indexs = array_keys($questions);
		$mainIds = '';
		for($i=0;$i<count($indexs);$i++){
			if(!isset($questions[$indexs[$i]]['belongto'])){
				$questions[$indexs[$i]]['belongto'] = '0';
			}
			$data = $questions[$indexs[$i]];

			unset($data['index']);
			unset($data['belongto']);
			if(isset($data['markingmethod'])){
				$data['markingmethod'] = $this->t->formatMarkingMethod($questions[$indexs[$i]]['markingmethod'],true);
			}
			if(isset($data['type'])){
				$data['type'] = $this->t->formatQuesType($questions[$indexs[$i]]['type'],true);
			}
				
			$data['date_created'] = date('Y-m-d H:i:s');
			if($questions[$indexs[$i]]['belongto']=='0'){
				$questions[$indexs[$i]]['id_parent'] = $data['id_parent'] = 0;
			}else{
				$questions[$indexs[$i]]['id_parent'] = $data['id_parent'] = $questions[$questions[$indexs[$i]]['belongto']]['id'];
			}

			$id = $this->insert($data);
			if($id===false){
				return false;
			}else{
				$questions[$indexs[$i]]['id'] = $id;
				continue;
			}
		}
		return $questions;
	}

}
?>