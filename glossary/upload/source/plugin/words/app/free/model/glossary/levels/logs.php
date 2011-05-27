<?php
/**
 * 关卡日志
 * 记录下哪些人参与了哪些关卡,通过了哪些关卡
 * 每个新用户产生时,都会在关卡日志表中插入几条日志,指那些 免费的,初级的 关卡
 * 
 * @version 2011-05-01
 * @author wei1224hf
 * @see http://www.welikestudy.com/forum.php?mod=viewthread&tid=1167
 * */
class m_glossary_levels_logs extends wls implements dbtable{

	public $id_level = null;
	public $subject = null;
	
	/**
	 * Befor doing the 'importAll' action , check if it is 
	 * to-append or to-rebuild
	 * The rebuild action will delete everything old , and then insert the new data. 
	 * The append  action will only insert the new data. 
	 * */
	public $operation = null;

	/**
	 * Insert one row into the database's table
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return $id
	 * */
	public function insert($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_glossary_levels_logs (".$keys.") values ('".$values."')";
		
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}
	
	/**
	 * Check if the current user can play the level
	 * Check if there is a log , and the status is OK
	 * */
	public function checkIfICanDo($subject,$level) {
		if(!isset($_SESSION))session_start();
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_glossary_levels_logs where subject = '".$subject."' and level = ".$level;
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			if($temp['status']==0)return false;
			return true;
		}
		return false;
	}
	
	public function checkMoney($id){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
				
		if(!isset($_SESSION))session_start();
		$id_user = $_SESSION['wls_user']['id'];
		$sql = "select money from ".$pfx."wls_user where id = ".$id_user;
		
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$myMoney = $temp['money'];
		
		$sql = "select money from ".$pfx."wls_glossary_levels where id =  ".$id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$theMoney = $temp['money'];
		
		if($theMoney > $myMoney)return false;
		
		$sql = "update ".$pfx."wls_user set money = money - ".$theMoney." where id = ".$id_user;
		$res = mysql_query($sql,$conn);
		return true;
	}
	
	public function sendFreeLevelsToEveryone(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "select * from pre_wls_glossary_levels where money = 0 and level = 1 ";
		$sql = str_replace('pre_', $pfx, $sql);
		$res = mysql_query($sql,$conn);
		$data1 = array();
		while ($temp = mysql_fetch_assoc($res)) {
			$data1[] = $temp;
		}
		//print_r($data1);
		
		$sql = "select * from pre_wls_user";
		$sql = str_replace('pre_', $pfx, $sql);
		$res = mysql_query($sql,$conn);
		$data2 = array();
		while ($temp = mysql_fetch_assoc($res)) {
			$data2[] = $temp;
		}		
		//print_r($data2);
		
		//Is it a bad efficiency to do the loop agian ? 
		//While I can do the loop in the previous 'while' ...
		//I just want to make the code clear.
		//And the efficiency? Go the hell
		for($i=0;$i<count($data1);$i++){
			for($j=0;$j<count($data2);$j++){
				$data = array(
					'id_user'=>$data2[$j]['id'],
					'level'=>$data1[$i]['level'],
					'subject'=>$data1[$i]['subject'],
					'time_joined'=>date('Y-m-d H:i:s'),
					'status'=>0,
					'time_passed'=>'3000-01-01'
				);
				$this->insert($data);
				
				$sql = "update pre_wls_glossary_levels set count_joined = count_joined + 1 where level = '".$data1[$i]['level']."' and subject = '".$data1[$i]['subject']."'; ";
				mysql_query($sql,$conn);
			}
		}
	}	

	/**
	 * Delete one or more rows by id. Only by id!
	 *
	 * @param $ids Every table has this column.
	 * @return bool
	 * */
	public function delete($id){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "delete from ".$pfx."wls_glossary_levels where id = ".$id;

		mysql_query($sql,$conn);
		return true;
	}

	/**
	 * Update one row into the database's table
	 * There must have id in $data
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return bool
	 * */
	public function update($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_glossary_levels_logs set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;

		mysql_query($sql,$conn);
		
		return true;
	}

	/**
	 * Create this table.
	 * If it's already exists, it would be dropped first.
	 * 
	 * About the status:
	 *  0 Can join but not joined yet , it's by the System's operatioin , the machine did it.
	 *  1 The user have joined this already. It's by user's operation
	 *  2 Passed
	 *
	 * @return bool
	 * */
	public function create(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists pre_wls_glossary_levels_logs;";
		$sql = str_replace('pre_', $pfx, $sql);
		mysql_query($sql,$conn);
		$sql = "
			create table pre_wls_glossary_levels_logs(
			
				 id int primary key auto_increment	
				,time_joined datetime default '1987-03-18'
				,time_passed datetime default '1987-03-18'
				
				,subject varchar(200) default '0'
				,status int default 0
				
				,level int default 0
				,id_user int default 0
				
				,count_wrong int default 0 
				,count_right int default 0			

				,CONSTRAINT pre_wls_glossary_levels_logs_u UNIQUE (id_user,level,subject)
			) DEFAULT CHARSET=utf8;
			
			";

		$sql = str_replace('pre_', $pfx, $sql);
		mysql_query($sql,$conn);
		
		return true;
	}

	/**
	 * It's normally used for client-side's grid and table stuff.
	 * The client should support the search and ordering abilty
	 * */
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_subject ; ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp=mysql_fetch_assoc($res)){
			$data[$temp['id_level']] = $temp['name'];
		}
		$subjects = $data;

		$where = " where 1 =1  ";
		if($search!=null){
			//print_r($search);
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='title'){
					if(count($search[$keys[$i]])==1){
						$sql_subject = "select id from ".$pfx."wls_user where username = '".$search[$keys[$i]][0]."' ;";
						//echo $sql_subject;
						$res_subject = mysql_query($sql_subject,$conn);
						$temp_subject = mysql_fetch_assoc($res_subject);

						$where .= " and id_user = ".$temp_subject['id']." ";
					}else{
						$name_subjects = '';
						//print_r($search[$keys[$i]]);
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							//print_r($search[$keys[$i]][$i2]);
							$name_subjects .= "'".$search[$keys[$i]][$i2]."',";
						}
						$name_subjects = substr($name_subjects,0,strlen($name_subjects)-1);
						//echo $name_subjects;
						$sql_subject = "select id from ".$pfx."wls_user where username in (".$name_subjects.") ;";
						$res_subject = mysql_query($sql_subject,$conn);
						$ids = '';
						while($temp_subject = mysql_fetch_assoc($res_subject)){
							$ids .= "'".$temp_subject['id']."',";
						}
						$ids = substr($ids,0,strlen($ids)-1);

						$where .= " and id_user in (".$ids.")  ";
					}
				}else if($keys[$i]=='user'){
					$where .= " and id_user = ".$search[$keys[$i]];
				}else if($keys[$i]=='subject'){
					if(count($search[$keys[$i]])==1){
						$sql_subject = "select id_level from ".$pfx."wls_subject where name = '".$search[$keys[$i]][0][1]."' ;";
						$res_subject = mysql_query($sql_subject,$conn);
						$temp_subject = mysql_fetch_assoc($res_subject);

						$where .= " and subject = ".$temp_subject['id_level']." ";
					}else{
						$name_subjects = '';
						for($i2=0;$i2<count($search[$keys[$i]]);$i2++){
							$name_subjects .= "'".$search[$keys[$i]][$i2][1]."',";
						}
						$name_subjects = substr($name_subjects,0,strlen($name_subjects)-1);
						$sql_subject = "select id_level from ".$pfx."wls_subject where name in (".$name_subjects.") ;";
						$res_subject = mysql_query($sql_subject,$conn);
						$ids_subject = '';
						while($temp_subject = mysql_fetch_assoc($res_subject)){
							$ids_subject .= "'".$temp_subject['id_level']."',";
						}
						$ids_subject = substr($ids_subject,0,strlen($ids_subject)-1);

						$where .= " and subject in (".$ids_subject.")  ";
					}
				}
			}
		}
		if($orderby==null)$orderby = " order by ".$pfx."wls_glossary_levels_logs.id ";
		$sql = "select ".$pfx."wls_glossary_levels_logs.id, time_joined, time_passed,".$pfx."wls_glossary_levels_logs.subject,status,level,id_user,count_wrong,count_right,username from ".$pfx."wls_glossary_levels_logs,
			".$pfx."wls_user
			".$where." and ".$pfx."wls_user.id = ".$pfx."wls_glossary_levels_logs.id_user ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";
		//echo $sql;exit();
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$temp['subject_name'] = $subjects[$temp['subject']];
			$data[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_glossary_levels_logs ".$where;
		$res = mysql_query($sql2,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		return array(
			'page'=>$page,
			'data'=>$data,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
		);
	}	
	
	public function passed($subject,$level) {
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		if(!isset($_SESSION))session_start();
		
		$sql = "select count(*) as count_ from ".$pfx."wls_glossary_levels where subject = '".$subject."' and level > ".$level;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp['count_']==0)return false;
		
		$data = array(
			'id_user'=>$_SESSION['wls_user']['id'],
			'level'=>($level+1),
			'subject'=>$subject,
			'time_joined'=>date('Y-m-d H:i:s'),
			'status'=>0,
			'time_passed'=>'3000-01-01'
		);
		$this->insert($data);
		
		$sql = "update ".$pfx."wls_glossary_levels_logs set status = 2 , time_passed = '".date('Y-m-d H:i:s')."' where subject = '".$subject."' and level = ".$level;	
		mysql_query($sql,$conn);
		
		$sql = "update ".$pfx."wls_glossary_levels set count_passed = count_passed + 1  where subject = '".$subject."' and level = ".$level;	
		mysql_query($sql,$conn);		
	}
	
	public function whatAreTheyDoing(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		//TODO
		$sql = "";
	}
}
?>