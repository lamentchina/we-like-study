<?php
include_once dirname(__FILE__).'/../quiz.php';

class m_quiz_log extends m_quiz implements dbtable,quizdo{

	public $phpexcel;
	public $id = null;

	/**
	 * 插入一条数据
	 *
	 * @param $data 一个数组,其键值与数据库表中的列一一对应
	 * @return bool
	 * */
	public function insert($data){
//		print_r($data);
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		if(!isset($data['id_user'])){
			$user = $this->getMyUser();
			$data['id_user'] = $user['id'];
			$data['id_level_user_group'] = $user['id_level_user_group'];
		}

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_log (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
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

		$sql = "update ".$pfx."wls_quiz_log set ";
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

		$sql = "drop table if exists ".$pfx."wls_quiz_log;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_log(
				 id int primary key auto_increment	/*自动编号*/
				 
				,date_created datetime 				comment '创建时间'				 
				,id_user int default 0				comment '用户编号'
				,id_level_user_group varchar(200) default '' comment '用户所在用户组的编号'
				
				,id_question text 					/*题目编号*/
				
				,id_level_subject varchar(200) default '0' comment '考试科目编号'
				,id_quiz_paper int default 0		comment '试卷编号'
				
				,cent float default 0
				,mycent float default 0
				
				,count_right int default 0
				,count_wrong int default 0
				,count_giveup int default 0
				,count_total int default 0 
				
				,proportion float default 0			/*做对率*/
				
				,time_start datetime default '2011-01-08'
				,time_stop datetime default '2011-01-08'
				,time_used int default 0				
	
				,application int default 0			/* 0 做测验卷, 1随机练习,2题型掌握度练习,3知识点掌握度练习,4参加在线考试,5错题本练习*/
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

		include_once dirname(__FILE__).'/../subject.php';
		$obj = new m_subject();
		$data = $obj->getList(1,100);
		if(count($data['data'])<1){
			$this->error(array('description'=>'科目尚未初始化'));
			include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
			return false;
		}

		include_once dirname(__FILE__).'/../tools.php';
		$t = new tools();

		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName('paper');
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$quizlog = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()=='时间'){
				$quizlog['date_created'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='用户编号'){
				$quizlog['id_user'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='试卷编号'){
				$quizlog['id_quiz_paper'] = $currentSheet->getCell($i."2")->getValue();
				$sql = "select id_level_subject from ".$pfx."wls_quiz_paper where id =".$quizlog['id_quiz_paper'];

				$res = mysql_query($sql,$conn);
				$temp = mysql_fetch_assoc($res);
				$quizlog['id_level_subject'] = $temp['id_level_subject'];
			}
		}
		$quizlog['id'] = $this->insert($quizlog);
		//		exit();

		$currentSheet = $this->phpexcel->getSheetByName('questions');
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()=='题目编号'){
				$keys['id_question'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='回答'){
				$keys['myanswer'] = $i;
			}
		}

		include_once dirname(__FILE__).'/../question/log.php';
		$quesObj = new m_question_log();
		$index = 0;
		$queslogs = array();
		$count_wrong = 0;
		$count_right = 0;
		$quizlog_cent = 0;
		$id_question = '';
		include_once dirname(__FILE__).'/wrong.php';
		$wrongObj = new m_quiz_wrong();
		for($i=3;$i<=$allRow;$i++){
			$sql = "select id,answer,cent,id_parent from ".$pfx."wls_question where id = ".$currentSheet->getCell($keys['id_question'].$i)->getValue();
			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);
			$id_question .= $temp['id'].',';
			$queslog = array(
				'date_created'=>$quizlog['date_created']
				,'id_user'=>$quizlog['id_user']
				,'id_level_subject'=>$quizlog['id_level_subject']
				,'id_quiz_paper'=>$quizlog['id_quiz_paper']
				,'id_quiz_log'=>$quizlog['id']
				,'id_question'=>$temp['id']
				,'id_question_parent'=>$temp['id_parent']
				,'answer'=>$temp['answer']
				,'cent'=>$temp['cent']
				,'myanswer'=>$currentSheet->getCell($keys['myanswer'].$i)->getValue()
			);
			if($queslog['myanswer']==$queslog['answer']){
				$queslog['correct'] = 1;
				$quizlog_cent += $queslog['cent'];
				$count_right ++;
			}else{
				$queslog['correct'] = 0;
				$count_wrong ++;
				$wrong = array(
					 'id_question' => $temp['id']
					,'id_quiz_paper' => $quizlog['id_quiz_paper']
					,'id_level_subject' => $quizlog['id_level_subject']
					,'id_user'=>$quizlog['id_user']
					,'date_created'=>$quizlog['date_created']
				);
				$wrongObj->insert($wrong);				
			}
			$queslog['id'] = $quesObj->insert($queslog);
			$queslogs[] = $queslog;
		}
		$id_question = substr($id_question,0,strlen($id_question)-1);
		$quizlog_update = array(
			 'id'=>$quizlog['id']
		,'mycent'=>$quizlog_cent
		,'id_question'=>$id_question
		,'count_right'=>$count_right
		,'count_wrong'=>$count_wrong
		,'proportion'=>$count_right/($count_wrong+$count_right)
			
		);
		$this->update($quizlog_update);
	}

	/**
	 * 导出一张EXCEL文件,
	 * 提供下载,实现数据的多处同步,并让这个EXCEL文件形成标准
	 *
	 * @return $path
	 * */
	public function exportExcel(){
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('data');

		$objPHPExcel->getActiveSheet()->setCellValue('A1', '用户名');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '密码');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', '金币');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', '积分');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', '用户组');

		$index = 1;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['username']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['password']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['money']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['credits']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['id_level_user_group']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('E2');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'E2:E'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "file/download/".date('YmdHis').".xls";
		$objWriter->save(dirname(__FILE__)."/../../../".$file);
		return $file;
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
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_quiz_log ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_quiz_log ".$where;
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

	public function getLogAnswers(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select id,id_question,myanswer from ".$pfx."wls_question_log where id_quiz_log = ".$this->id." order by id_question;";
		
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[$temp['id_question']] = $temp['myanswer'];
		}
		return $arr;
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
	 * @return $ids 一组题目编号
	 * */
	public function getQuizIds(){}
}
?>