<?php
include_once dirname(__FILE__).'/../quiz.php';

class m_quiz_paper extends m_quiz implements dbtable,quizdo{

	public $phpexcel;
	public $id = null;
	public $mycent = null;
	public $questions = null;
	public $paper = null;

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
		$sql = "insert into ".$pfx."wls_quiz_paper (".$keys.") values ('".$values."')";
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
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_quiz_paper where id in (".$ids.") ";
		try {
			mysql_query($sql,$conn);
			$sql = "delete from ".$pfx."wls_question where id_quiz_paper in (".$ids.") ";
			try{
				mysql_query($sql,$conn);
				return true;
			}
			catch (Exception $ex2){
				return false;
			}

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

		$sql = "update ".$pfx."wls_quiz_paper set ";
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

		$sql = "drop table if exists ".$pfx."wls_quiz_paper;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_paper(
				 id int primary key auto_increment	comment '自动编号'
				,id_level_subject int default 0		comment '考试科目编号,级别编号'
				,name_subject varchar(200) default '0' comment '考试科目名称'
				
				,title varchar(200) not null		comment '试卷名称'
				,questions text	comment '题目组成,由一大堆编号组成,这些编号都是主题目编号,不会记录子题目编号'
				
				,description varchar(200) default '0'	comment '描述'				
				,creator varchar(200) default 'admin'		comment '创建者'
				,date_created datetime not null 	comment '创建时间'
				
				,time_limit int default 3600		comment '时间限制,默认为1小时,1小时后自动提交试卷'
				,score_top float default 0			comment '最高分'
				,score_top_user varchar(200) default 0		comment '最高分获得者用户编号'
				,score_avg float default 0			comment '平均分'
				,count_used int	default 0			comment '使用次数'
				
				,money int default 0				comment '金币'
				
				,cache_path_quiz text 				comment '这张试卷的题目缓存路径'
			
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

		$paper = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()=='标题'){
				$paper['title'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='金币'){
				$paper['money'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='作者'){
				$paper['creator'] = $currentSheet->getCell($i."2")->getValue();
			}
			if($currentSheet->getCell($i."1")->getValue()=='科目'){
				$paper['id_level_subject'] = $currentSheet->getCell($i."2")->getValue();
				$sql_ = "select name from ".$pfx."wls_subject where id_level = '".$paper['id_level_subject']."'; ";
				$res = mysql_query($sql_,$conn);
				$temp = mysql_fetch_assoc($res);
				$paper['name_subject'] = $temp['name'];
			}
		}
		$paper['date_created'] = date('Y-m-d i:m:s');
		$paper['id'] = $this->insert($paper);
		$this->id = $paper['id'];
		$this->paper = $paper;

		$currentSheet = $this->phpexcel->getSheetByName('questions');
		$allRow = $currentSheet->getHighestRow();
		$allColmun = $currentSheet->getHighestColumn();

		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()=='序号'){
				$keys['index'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='属于'){
				$keys['belongto'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='题型'){
				$keys['type'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='题目'){
				$keys['title'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='答案'){
				$keys['answer'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='分值'){
				$keys['cent'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项A'){
				$keys['option1'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项B'){
				$keys['option2'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项C'){
				$keys['option3'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项D'){
				$keys['option4'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项E'){
				$keys['option5'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项F'){
				$keys['option6'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项G'){
				$keys['option7'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='解题说明'){
				$keys['description'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='听力文件'){
				$keys['path_listen'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='使用次数'){
				$keys['count_used'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='做对'){
				$keys['count_right'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='做错'){
				$keys['count_wrong'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='放弃'){
				$keys['count_giveup'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='难度'){
				$keys['difficulty'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='批改'){
				$keys['markingmethod'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()=='选项数'){
				$keys['optionlength'] = $i;
			}
		}

		include_once dirname(__FILE__).'/../tools.php';
		$t = new tools();

		$index = 0;
		$questions = array();
		for($i=3;$i<=$allRow;$i++){
			$questions[$currentSheet->getCell($keys['index'].$i)->getValue()] = array(
				'index'=>$currentSheet->getCell($keys['index'].$i)->getValue(),
				'belongto'=>$currentSheet->getCell($keys['belongto'].$i)->getValue(),
				'type'=>$currentSheet->getCell($keys['type'].$i)->getValue(),
				'title'=>$t->formatTitle($currentSheet->getCell($keys['title'].$i)->getValue()),
				'answer'=>$currentSheet->getCell($keys['answer'].$i)->getValue(),
				'cent'=>$currentSheet->getCell($keys['cent'].$i)->getValue(),
				'option1'=>$t->formatTitle($currentSheet->getCell($keys['option1'].$i)->getValue()),
				'option2'=>$t->formatTitle($currentSheet->getCell($keys['option2'].$i)->getValue()),
				'option3'=>$t->formatTitle($currentSheet->getCell($keys['option3'].$i)->getValue()),
				'option4'=>$t->formatTitle($currentSheet->getCell($keys['option4'].$i)->getValue()),
				'option5'=>$t->formatTitle($currentSheet->getCell($keys['option5'].$i)->getValue()),
				'option6'=>$t->formatTitle($currentSheet->getCell($keys['option6'].$i)->getValue()),
				'option7'=>$t->formatTitle($currentSheet->getCell($keys['option7'].$i)->getValue()),
				'description'=>$t->formatTitle($currentSheet->getCell($keys['description'].$i)->getValue()),
				'path_listen'=>$currentSheet->getCell($keys['path_listen'].$i)->getValue(),
				'count_used'=>$currentSheet->getCell($keys['count_used'].$i)->getValue(),
				'count_right'=>$currentSheet->getCell($keys['count_right'].$i)->getValue(),
				'count_wrong'=>$currentSheet->getCell($keys['count_wrong'].$i)->getValue(),
				'count_giveup'=>$currentSheet->getCell($keys['count_giveup'].$i)->getValue(),
				'difficulty'=>$currentSheet->getCell($keys['difficulty'].$i)->getValue(),
				'markingmethod'=>$currentSheet->getCell($keys['markingmethod'].$i)->getValue(),
				'optionlength'=>$currentSheet->getCell($keys['optionlength'].$i)->getValue(),
				'id_level_subject'=>$paper['id_level_subject'],
				'name_subject'=>$paper['name_subject'],
				'id_quiz_paper'=>$paper['id'],
				'title_quiz_paper'=>$paper['title'],
			);
		}

		$this->questions = $questions;
		$this->saveQuestions();
	}

	public function saveQuestions(){
		include_once dirname(__FILE__).'/../question.php';
		$quesObj = new m_question();
		$questions = $this->questions;
		$ques = $quesObj->insertMany($questions);
		if($ques==false){
			return false;
		}else{
			$values = array_values($ques);
			$ids = '';
			for($i=0;$i<count($values);$i++){
				$ids .= $values[$i]['id'].",";				
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$data = array(
				'id'=>$this->id,
				'questions'=>$ids
			);
			return $this->update($data);
		}
	}

	public function paperToExcel($paper,$questions){
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel.php';
		include_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once dirname(__FILE__).'/../../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$data = $paper;

		//处理试卷数据
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('paper');

		$objPHPExcel->getActiveSheet()->setCellValue('A1', '标题');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '科目');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', '金币');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', '作者');

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $data['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $data['id_level_subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $data['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $data['creator']);

		$data = $questions;
		//处理题目
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('questions');

		$objPHPExcel->getActiveSheet()->setCellValue('A2', '序号');
		$objPHPExcel->getActiveSheet()->setCellValue('B2', '属于');
		$objPHPExcel->getActiveSheet()->setCellValue('C2', '题型');
		$objPHPExcel->getActiveSheet()->setCellValue('D2', '题目');		
		$objPHPExcel->getActiveSheet()->setCellValue('E2', '答案');
		$objPHPExcel->getActiveSheet()->setCellValue('F2', '分值');
		$objPHPExcel->getActiveSheet()->setCellValue('G2', '选项A');
		$objPHPExcel->getActiveSheet()->setCellValue('H2', '选项B');	
		$objPHPExcel->getActiveSheet()->setCellValue('I2', '选项C');
		$objPHPExcel->getActiveSheet()->setCellValue('J2', '选项D');
		$objPHPExcel->getActiveSheet()->setCellValue('K2', '选项E');
		$objPHPExcel->getActiveSheet()->setCellValue('L2', '选项F');	
		$objPHPExcel->getActiveSheet()->setCellValue('M2', '选项G');
		$objPHPExcel->getActiveSheet()->setCellValue('N2', '选项数');
		$objPHPExcel->getActiveSheet()->setCellValue('O2', '解题说明');
		$objPHPExcel->getActiveSheet()->setCellValue('P2', '听力文件');	
		$objPHPExcel->getActiveSheet()->setCellValue('Q2', '使用次数');
		$objPHPExcel->getActiveSheet()->setCellValue('R2', '做对');
		$objPHPExcel->getActiveSheet()->setCellValue('S2', '做错');
		$objPHPExcel->getActiveSheet()->setCellValue('T2', '放弃');	
		$objPHPExcel->getActiveSheet()->setCellValue('U2', '难度');
		$objPHPExcel->getActiveSheet()->setCellValue('V2', '批改');
		$objPHPExcel->getActiveSheet()->setCellValue('W2', '排列');
		for($i=1;$i<=23;$i++){
			$objPHPExcel->getActiveSheet()->setCellValue(chr($i+64).'1', $i);
		}

		$index = 3;
		for($i=0;$i<count($data);$i++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['id_parent']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['type']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['title']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['answer']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['cent']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option1']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option2']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option3']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option4']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option5']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option6']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['option7']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['optionlength']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['description']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['path_listen']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['count_used']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['count_right']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['count_wrong']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['count_giveup']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['difficulty']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['markingmethod']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['path_listen']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['path_listen']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['path_listen']);
			$index ++;
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('E2');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'E2:E'.(count($data)+1));

		//保存EXCEL
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "file/download/".date('YmdHis').".xls";
		$objWriter->save(dirname(__FILE__)."/../../../".$file);
		return $file;
	}

	/**
	 * 导出一张EXCEL文件,
	 * 提供下载,实现数据的多处同步,并让这个EXCEL文件形成标准
	 *
	 * @return $path
	 * */
	public function exportExcel(){
		$data = $this->getList(1,1,array('id'=>$this->id));
		$paper = $data['data'][0];

		//处理题目数据
		include_once dirname(__FILE__).'/../question.php';
		$ques = new m_question();
		$data = $ques->getList(1,200,array('id_quiz_paper'=>$this->id));
		$questions = $data['data'];

		$this->paperToExcel($paper,$questions);
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
		if($column=='score'){
			$sql = "select score_top from ".$pfx."wls_quiz_paper where id = ".$this->id;

			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);
			if($temp['score_top']<=$this->mycent){
				$user = $this->getMyUser();
				$sql = "update ".$pfx."wls_quiz_paper set
					score_top_user = '".$user['username']."',
					score_top = '".$this->mycent."' 
					where id = ".$this->id;
				$this->error($sql);
				try{
					mysql_query($sql,$conn);
					return true;
				}catch (Exception $ex){
					return false;
				}
			}

			$sql = "update ".$pfx."wls_quiz_paper set score_avg = (score_avg*count_used+".$this->mycent.")/(count_used+1) where id = ".$this->id;

		}else{
			$sql = "update ".$pfx."wls_quiz_paper set ".$column." = ".$column."+1 where id = ".$this->id;
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
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_level_subject'){
					$where .= " and id_level_subject in (".$search[$keys[$i]].") ";
				}
				
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_quiz_paper ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_quiz_paper ".$where;
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

		$sql = "select questions,id from ".$pfx."wls_quiz_paper where id = ".$this->id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		return $temp['questions'];
	}
	
	public function checkMoney($id){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select money,id from ".$pfx."wls_quiz_paper where id= ".$id;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		
		$user = $this->getMyUser();
		if($user['money']>$temp['money']){
			$sql = "update ".$pfx."wls_user set money = money - ".$temp['money']." where id = ".$user['id'];
			$_SESSION['wls_user']['money'] -= $temp['money'];
			mysql_query($sql,$conn);
			return true;
		}else{
			return false;
		}
	}
}
?>