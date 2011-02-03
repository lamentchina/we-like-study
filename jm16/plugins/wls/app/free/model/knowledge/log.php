<?php
class m_knowledge_log extends wls implements dbtable,log{

	public $phpexcel;	
	public $id = null;
	
	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();		

		if(!isset($data['id_user'])){
			$user = $this->getMyUser();
			$data['id_user'] = $user['id'];
		}
		if(isset($data['id_question'])){
			$sql = "select ids_level_knowledge from ".$pfx."wls_question where id = ".$data['id_question'];
			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);

			$arr = explode(",",$temp['ids_level_knowledge']);
			for($i=0;$i<count($arr);$i++){
				$data2 = $data;
				unset($data2['id_question']);
				$data2['id_level_knowledge'] = $arr[$i];

				$this->insert($data2);
			}
			return;
		}
		
		if($data['id_level_knowledge']==0)return;
	
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_knowledge_log (".$keys.") values ('".$values."')";
		$done = mysql_query($sql,$conn);
		if($done===false){
			if(isset($data['count_right'])){
				$sql2 = "update ".$pfx."wls_knowledge_log set count_right = count_right +1 where 
				 date_created = '".$data['date_created']."' and
				 date_slide = '".$data['date_slide']."' and
				 id_level_knowledge = '".$data['id_level_knowledge']."'
				"; 
			}else{
				$sql2 = "update ".$pfx."wls_knowledge_log set count_wrong = count_wrong +1 where 
				 date_created = '".$data['date_created']."' and
				 date_slide = '".$data['date_slide']."' and
				 id_level_knowledge = '".$data['id_level_knowledge']."'
				"; 
			}
			mysql_query($sql2,$conn);
		}
	}

	public function delete($ids){}

	public function update($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_knowledge_log set ";
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

		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_knowledge_log;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_knowledge_log(
				 id int primary key auto_increment	
				 
				,date_created datetime default '1987-03-18'
				,date_slide int default 86400				 
				,id_user int default 0				
				,id_level_user_group varchar(200) default '0' 	
				,id_level_knowledge varchar(200) default '0' 	
				,count_right int default 0			
				,count_wrong int default 0

				,CONSTRAINT ".$pfx."wls_quiz_worng_u UNIQUE (id_user,id_level_knowledge,date_created,date_slide)
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
				if($keys[$i]=='id_user'){
					$where .= " and id_user in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='date_created'){
					$where .= " and date_created = '".$search[$keys[$i]]."' ";
				}	
				if($keys[$i]=='id_level_knowledge_'){
					$where .= " and id_level_knowledge like '".$search[$keys[$i]]."__' ";
				}	
				if($keys[$i]=='lastDate'){
					$sql = "select max(date_created) as date_created_max from ".$pfx."wls_knowledge_log where id_user = ".$search['id_user'];
					$res = mysql_query($sql,$conn);
					$temp = mysql_fetch_assoc($res);					
					
					$where .= " and date_created = '".$temp['date_created_max']."' ";
				}																		
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns.",((count_right*100)/(count_right+count_wrong)) as proportion from ".$pfx."wls_knowledge_log ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
		
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		
		$sql2 = "select count(*) as total from ".$pfx."wls_knowledge_log ".$where;
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
	
	/**
	 * Get my recent knowledge statics info.
	 * 
	 * @param $id User id
	 * @return Array
	 * */
	public function getMyRecent($id){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
				
		$user = $this->getMyUser();
		$sql = "select id_level_knowledge from ".$pfx."wls_knowledge_log where id_user = ".$user['id']." and id_level_knowledge like '".$id."__' group by id_level_knowledge ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		$ids = "";
		while ($temp = mysql_fetch_assoc($res)) {
			$sql_ = "select * from ".$pfx."wls_knowledge_log where id_user= ".$user['id']." and id_level_knowledge = '".$temp['id_level_knowledge']."' order by date_created desc  ";
			$res_ = mysql_query($sql_,$conn);
			$temp_ = mysql_fetch_assoc($res_);
			$arr[$temp_['id_level_knowledge']] = $temp_;
			$ids.= "'".$temp['id_level_knowledge']."',";
		}
		$ids = substr($ids,0,strlen($ids)-1);
		
		$sql = "select * from ".$pfx."wls_knowledge where id_level in (".$ids."); ";
		$res = mysql_query($sql,$conn);
		while($temp = mysql_fetch_assoc($res)){
			$arr[$temp['id_level']]['name'] = $temp['name'];
		}
		return $arr;		
	}
}
?>