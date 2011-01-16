<?php
class m_knowledge_log extends wls implements dbtable,log{

	public $phpexcel;	
	public $id = null;
	
	/**
	 * 插入一条数据
	 *
	 * @param $data 一个数组,其键值与数据库表中的列一一对应
	 * @return bool
	 * */
	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();		

		$user = $this->getMyUser();
		$data['id_user'] = $user['id'];
		$data['id_level_user_group'] = $user['id_level_user_group'];		
	
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_knowledge_log (".$keys.") values ('".$values."')";
		try{
			mysql_query($sql,$conn);
		}catch (Exception $ex){
			if($data['count_wrong']==1){
				$sql2 = "update ".$pfx."wls_knowledge_log set count_wrong = count_wrong +1 where 
				 date_created = '".$data['date_created']."' and
				 date_slide = '".$data['date_slide']."' and
				 id_level_knowledge = '".$data['id_level_knowledge']."'
				"; 
			}else{
				$sql2 = "update ".$pfx."wls_knowledge_log set count_right = count_right +1 where 
				 date_created = '".$data['date_created']."' and
				 date_slide = '".$data['date_slide']."' and
				 id_level_knowledge = '".$data['id_level_knowledge']."'
				"; 
			}
			mysql_query($sql2,$conn);			
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
		if($this->c->state!='debug')return false;
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_knowledge_log;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_knowledge_log(
				 id int primary key auto_increment	/*自动编号*/
				 
				,date_created datetime 				/*创建时间*/	
				,date_slide int default 86400		/*时间间隔,默认一天*/			 
				,id_user int default 0				/*用户编号*/
				,id_level_user_group varchar(200) default '0' 	/*用户组编号*/
				,id_level_knowledge varchar(200) default '0' 	/*知识点编号*/
				,count_right int default 0			
				,count_wrong int default 0

				,CONSTRAINT ".$pfx."wls_quiz_worng_u UNIQUE (id_user,id_level_knowledge,date_created,date_slide)
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
		$conn = $this->conn();
		$pfx = $this->c->dbprefix;	

		
	}

	/**
	 * 导出一张EXCEL文件,
	 * 提供下载,实现数据的多处同步,并让这个EXCEL文件形成标准
	 *
	 * @return $path
	 * */
	public function exportExcel(){
		
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
		$sql = "select ".$columns." from ".$pfx."wls_knowledge_log ".$where." ".$orderby;
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
	
	/**
	 * 创建一条日志
	 * 
	 * @param $whatHappend 事件类型
	 * */
	public function addLog($whatHappened){
		
	}
}
?>