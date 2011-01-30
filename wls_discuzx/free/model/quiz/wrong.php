<?php
include_once dirname(__FILE__).'/../quiz.php';

class m_quiz_wrong extends m_quiz implements dbtable,quizdo{

	public $phpexcel;
	public $id = null;
	public $id_user = null;
	public $id_question = null;

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
		$sql = "insert into ".$pfx."wls_quiz_worng (".$keys.") values ('".$values."')";

		$temp = mysql_query($sql,$conn);
		if ( $temp === false ){
			$this->cumulative('count');
			return false;
		}
		return mysql_insert_id($conn);

	}

	/**
	 * 只能根据编号来删除数据,一次性可以删除多条
	 *
	 * @param $ids 编号,每张表都id这个列,一般为自动递增
	 * @return bool
	 * */
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

		$sql = "drop table if exists ".$pfx."wls_quiz_worng;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_worng(
				 id int primary key auto_increment	comment '自动编号'
				 
				,id_user int default 0 				/*用户编号*/
				,id_question int default 0 			/*题目编号*/
				,id_quiz_paper int default 0		/*所属试卷编号*/
				,id_level_subject varchar(200) default '0' /*科目编号*/
				,date_created datetime not null 	/*创建时间*/
				,count int default 1				/*错误次数*/
			
				,CONSTRAINT ".$pfx."wls_quiz_worng_u UNIQUE (id_user,id_question)
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
			$temp['timedif'] = $t->getTimeDif($temp['date_created'])."前"; 
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
	 * 导出这张试卷,允许用户下载
	 *
	 * @param $type 类型,可以是 WORD,PDF,EXCEL等
	 * @return $path
	 * */
	public function exportQuiz($type){}

	/**
	 * 得到我个人的已做的列表
	 *
	 * @param $page 页码,为整数
	 * @param $pagesize 页大小
	 * @param $search 查询条件
	 * @param $orderby 排序条件
	 * @return $array
	 * */
	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}

	/**
	 * 得到已经被做过了的列表,
	 * 一般为管理员操作,支持查询
	 *
	 * @param $page 页码,为整数
	 * @param $pagesize 页大小
	 * @param $search 查询条件
	 * @param $orderby 排序条件
	 * @return $array
	 * */
	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null){}

	/**
	 * 得到题编号
	 *
	 * @param $id 试卷编号
	 * @return $ids 一组题目编号
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