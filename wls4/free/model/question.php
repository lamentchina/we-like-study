<?php
/**
 * 用户操作,对应着一张数据库表
 * */
class m_question extends wls implements dbtable{
	
	public $phpexcel;	
	
	public $id;
	
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
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	/**
	 * 只能根据编号来删除数据,一次性可以删除多条
	 *
	 * @param $ids 编号,每张表都id这个列,一般为自动递增
	 * @return bool
	 * */
	public function delete($ids){
		
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
		
		$sql = "drop table if exists ".$pfx."wls_question;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_question(
				  id int primary key auto_increment	comment '自动编号'

				,type int default 1					comment '类别(1单项选择题,2多项选择题,3判断题,4简答题,5大题)'
				,title text not null				comment '题目标题'
				,answer text not null				comment '答案,根据题型不同有不同的组织形式'
				,optionlength int default 2			comment '选项个数.2个,对应判断题,多个对应选择题.最多7个选项'
				,option1 text						comment '选项1'
				,option2 text
				,option3 text
				,option4 text
				,option5 text
				,option6 text
				,option7 text							
				
				,description text					comment '描述,解题说明'	
				,cent float default 0				comment '分数.一道题目必然属于一张试卷,必然有自己的分数比例,一道题不可能属于2张试卷'
				
				,id_level_subject int default 0			comment '考试科目编号'
				,name_subject varchar(200) default '未知考试科目'		comment '考试科目名称'
				,id_quiz_paper int default	0		comment '试卷编号'
				,title_quiz_paper varchar(200) default '未知试卷名称'	comment '考试试卷名称'
				,id_parent int default 0			comment '父级题目编号,如果是阅读理解 完形填空 短文听力 填空'
				,path_listen varchar(200)  comment '如果这道题涉及听力,听力文件地址'
								

				,date_created datetime not null 	comment '创建时间'				

				,count_used int default 0			comment '使用次数'
				,count_right int default 0			comment '被做对的次数' 
				,count_wrong int default 0 			comment '被做错的次数' 
				,count_giveup int default 0 		comment '被放弃的次数' 	
				
				,comment_ywrong_1 int default 0		comment '用户评论,为什么我会做错,知识没掌握'
				,comment_ywrong_2 int default 0		comment '用户评论,为什么我会做错,粗心'
				,comment_ywrong_3 int default 0		comment '用户评论,为什么我会做错,题目文字太混,读不懂'
				,comment_ywrong_4 int default 0		comment '用户评论,为什么我会做错,我没错,答案错了'			

				,difficulty int default 0			comment '难度值,最大100,最小0'		

				,markingmethod int default 0		comment '批改方式:0 自动批改,1教师批改,2用户批改,3多用户批改'
				
				,ids_knowledge varchar(200) default '0' /*知识点编号*/
				,weight_knowledge varchar(200) default '0' /*权重*/
			
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
		
		$sql = "update ".$pfx."wls_question set ".$column." = ".$column."+1 where id = ".$this->id;
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
	 * 一次性插入多个题目
	 * 在导入试卷的时候执行
	 * 
	 * @param $questions array
	 * */
	public function insertMany($questions){
		include_once dirname(__FILE__).'/tools.php';
		$t = new tools();
		
		$indexs = array_keys($questions);
		$mainIds = '';
		for($i=0;$i<count($indexs);$i++){
			$data = $questions[$indexs[$i]];
			unset($data['index']);
			unset($data['belongto']);
			$data['markingmethod'] = $t->formatMarkingMethod($questions[$indexs[$i]]['markingmethod'],true);
			$data['type'] = $t->formatQuesType($questions[$indexs[$i]]['type'],true);
			$data['date_created'] = date('Y-m-d H:i:s');
			if($questions[$indexs[$i]]['belongto']=='0'){
				$questions[$indexs[$i]]['id_parent'] = $data['id_parent'] = 0;
			}else{
				$questions[$indexs[$i]]['id_parent'] = $data['id_parent'] = $questions[$questions[$indexs[$i]]['belongto']]['id'];
			}
			$id =  $this->insert($data);
			if($id===false){
				print_r($data);
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