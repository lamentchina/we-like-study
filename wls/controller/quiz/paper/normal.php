<?php
include_once 'controller/quiz/paper.php';

/**
 * 普通型的试卷导入
 * */
class quiz_paper_normal extends quiz_paper {

	public function getPaper(){
		$currentSheet = $this->phpexcel->getSheetByName('paper');

		$data = array(
			'title' => $currentSheet->getCell('B2')->getValue(),
			'title_quiz_type' => trim($currentSheet->getCell('B1')->getValue()),
			'description' => $currentSheet->getCell('B4')->getValue(),
			'creator' => trim($currentSheet->getCell('B3')->getValue()),
			'publisher' => trim($currentSheet->getCell('B5')->getValue()),
			'date_created' => date('Y-m-d'),
			'difficulty'=>trim($currentSheet->getCell('B15')->getValue()),
		);

		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select id from ".$pfx."wls_quiz_type where title = '".$data['title_quiz_type']."' ";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$data['id_quiz_type'] = $temp['id'];

		$this->paper = $data;
	}

	public function savePaper(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->paper;
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_paper (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		$this->paper['id'] = mysql_insert_id($conn);
	}


	/**
	 * 从EXCEL文件中读取题目信息并保存到一个数组中
	 * 这个EXCEL文件有一定的格式要求
	 * */
	public function getQuestion(){
		$currentSheet = $this->phpexcel->getSheetByName('data');
		$allRow = array($currentSheet->getHighestRow());

		$data = array();
		$index = 0;
		for($i=2;$i<=$allRow[0];$i++){
			if($currentSheet->getCell('A'.$i)->getValue() != $index){
				$index = $currentSheet->getCell('A'.$i)->getValue();
				$data[$index] = array(
					'id_quiz_type'=>$this->paper['id_quiz_type'],
					'title_quiz_type'=>$this->paper['title_quiz_type'],
					'id_quiz_paper'=>$this->paper['id'],
					'title_quiz_paper'=>$this->paper['title'],
				);
			}

			if($currentSheet->getCell('B'.$i)->getValue() == '分值'){
				$data[$index]['cent'] = $currentSheet->getCell('C'.$i)->getValue();
			}else if($currentSheet->getCell('B'.$i)->getValue() == '选项'){	
				$str = $currentSheet->getCell('C'.$i)->getValue();
				$arr = explode(";",$str);
				$data[$index]['details_'] = array(
					'options'=>array(
				array(
							'option'=>'A',
							'title'=>$arr[0],
				),
				array(
							'option'=>'B',
							'title'=>$arr[1],
				),
				array(
							'option'=>'C',
							'title'=>$arr[2],
				),
				)
				);
				if(count($arr)==4){
					$data[$index]['details_']['options'][] = array(
						'option'=>'D',
						'title'=>$arr[3],
					);
				}
			}else if($currentSheet->getCell('B'.$i)->getValue() == '详细选项'){
				if(!isset($data[$index]['details_'])){
					$data[$index]['details_'] = array(
						'options'=>array()
					);
				}
				$temp = $this->format($currentSheet->getCell('C'.$i)->getValue());
				$arr = explode(". ",$temp);
				$data[$index]['details_']['options'][] = array(
					'option'=>$arr[0],
					'title'=>$arr[1],
				);
			}else if($currentSheet->getCell('B'.$i)->getValue() == '听力文件'){
				$data[$index]['details_'] = array(
					'listen'=>$currentSheet->getCell('C'.$i)->getValue(),
				);
			}else if($currentSheet->getCell('B'.$i)->getValue() == '选项排列'){
				$data[$index]['details_']['display'] = 'vertical';
			}else if($currentSheet->getCell('B'.$i)->getValue() == '批改方式'&&
				$currentSheet->getCell('C'.$i)->getValue() != '自动批改'
			){
				$data[$index]['markingmethod'] = 1;
				$data[$index]['details_'] = array(
					'markingmethod'=>'manual'
					);
			}else if($currentSheet->getCell('B'.$i)->getValue() == '基础题型'){				
				$data[$index]['type'] = $this->formatType($currentSheet->getCell('C'.$i)->getValue());
			}else if($currentSheet->getCell('B'.$i)->getValue() == '题型'){		
				$data[$index]['extype'] = $currentSheet->getCell('C'.$i)->getValue();
				if($data[$index]['extype']=='CET作文'){

				}
			}else if($currentSheet->getCell('B'.$i)->getValue() == '题目'){		
				if(isset($data[$index]['title'])){
					$data[$index]['title'] .= $this->format($currentSheet->getCell('C'.$i)->getValue());
				}else{
					$data[$index]['title'] = $this->format($currentSheet->getCell('C'.$i)->getValue());
				}
			}else if($currentSheet->getCell('B'.$i)->getValue() == '答案'){		
				$data[$index]['answer'] = $this->format($currentSheet->getCell('C'.$i)->getValue());
			}else if($currentSheet->getCell('B'.$i)->getValue() == '解题说明'){				
				$data[$index]['description'] = $this->format($currentSheet->getCell('C'.$i)->getValue());
			}else if($currentSheet->getCell('B'.$i)->getValue() == '属于'){		
				$data[$index]['belongto'] = $currentSheet->getCell('C'.$i)->getValue();
			}
		}
		$this->ques = $data;
	}

	/**
	 * 保存题目信息
	 * 并记录每个题目的编号
	 * */
	public function saveQuestion(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$excelids = array_keys($this->ques);
		for($i=0;$i<count($excelids);$i++){
			if(array_key_exists('details_',$this->ques[$excelids[$i]])){
				$this->ques[$excelids[$i]]['details'] = json_encode($this->ques[$excelids[$i]]['details_']);
				$this->ques[$excelids[$i]]['details'] = str_replace("\\u","\\\\u",$this->ques[$excelids[$i]]['details']);
				unset($this->ques[$excelids[$i]]['details_']);
			}
			$sub = false;
			if(array_key_exists('belongto',$this->ques[$excelids[$i]])){
				$sub = true;
				$this->ques[$excelids[$i]]['id_parent'] = $this->ques[$this->ques[$excelids[$i]]['belongto']]['id'];
				unset($this->ques[$excelids[$i]]['belongto']);
			}
			$keys = array_keys($this->ques[$excelids[$i]]);
			$keys = implode(",",$keys);
			$values = array_values($this->ques[$excelids[$i]]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$this->ques[$excelids[$i]]['id'] = mysql_insert_id($conn);
			if($sub){
				$this->subques[] = $this->ques[$excelids[$i]]['id'];
			}else{
				$this->mainques[] = $this->ques[$excelids[$i]]['id'];
			}
		}
		print_r($this->ques);
	}

	/**
	 * 跟新试卷的信息
	 * 主要包括 题目总数,子题目总数,试卷价值等等
	 * */
	public function updatePaper(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$ids = '';
		for($i=0;$i<count($this->mainques);$i++){
			$ids .= $this->mainques[$i].",";
		}
		$ids = substr($ids,0,strlen($ids)-1);

		$ids2 = '';
		for($i=0;$i<count($this->subques);$i++){
			$ids2 .= $this->subques[$i].",";
		}
		$ids2 = substr($ids2,0,strlen($ids2)-1);
		$sql = "update ".$pfx."wls_quiz_paper set
			count_quetions = '".count($this->mainques)."' ,
			count_subquestions = '".count($this->subques)."' ,
			subquestions = '".$ids2."',
			islisten = 1,
			questions = '".$ids."',
			price_money = 5,
			price_score = 5,
			rank = 1,
			difficulty = 4	
			
			where id = ".$this->paper['id'].";
		";
		mysql_query($sql,$conn);
	}

	public function formatType($str){
		if($str=='单项选择题')return 1;
		if($str=='多项选择题')return 2;
		if($str=='判断题')return 3;
		if($str=='简答题')return 4;
		if($str=='短文阅读')return 5;
	}

	public function importExcel($path=null){
		if($path==null && isset($_REQUEST['path']))$path = $_REQUEST['path'];
		$this->readExcel($path);

		$this->getPaper();
		$this->savePaper();

		$this->getQuestion();
		$this->saveQuestion();

		$this->updatePaper();		
	}

	public function exportExcel($id=null){
		if($id==null && isset($_REQUEST['id']))$id = $_REQUEST['id'];
		$this->getQuestions2($id);
		$this->getPaper2($id);

		include_once 'libs/phpexcel/Classes/PHPExcel.php';
		include_once 'libs/phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once 'libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();


		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
		$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
		$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
		$objPHPExcel->getProperties()->setCategory("Test result file");

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('data');
		$data = $this->ques;

		$index = 1;
		$objStyleA5 = null;
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(120);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(4);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '序号');

		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '题目');		
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->formatTitle($data[$i]['title'],true));
			if($objStyleA5==null){
				$objPHPExcel->getActiveSheet()->getStyle('A'.$index)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('A'.$index)->getFill()->getStartColor()->setARGB('0DB0E59FF');
				$objStyleA5 = $objPHPExcel->getActiveSheet()->getStyle('A'.$index);
			}
			$objPHPExcel->getActiveSheet()->duplicateStyle($objStyleA5, 'A'.$index.':C'.$index);

			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '分值');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['cent']);
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '批改方式');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->formatMarkingMethod($data[$i]['markingmethod'],false));
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '基础题型');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->formatQuesType($data[$i]['type'],false));
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '题型');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['extype']);
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '答案');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->formatTitle($data[$i]['answer'],true));
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '解题说明');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $this->formatTitle($data[$i]['description'],true));
			if($data[$i]['details']){
				$obj = json_decode($data[$i]['details'],true);
				if(isset($obj['options'])){
					for($ii=0;$ii<count($obj['options']);$ii++){
						$index ++;
						$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '详细选项');
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $obj['options'][$ii]['option'].". ".$this->formatTitle($obj['options'][$ii]['title'],true));
					}
				}
				if(isset($obj['listen'])){
					$index ++;
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '听力文件');
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $obj['listen']);
				}
			}
			if($data[$i]['id_parent']>0){
				$index ++;
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['id']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, '属于');
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['id_parent']);
			}
		}
		$objPHPExcel->getActiveSheet()->getStyle('C1:C'.$index)->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1:C'.$index)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('A1:C'.$index)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		//		$objStyleA5 = $objPHPExcel->getActiveSheet()->getStyle('C1');
		//		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyleA5, 'C1:C'.$index);

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('paper');
		$data = $this->paper;

		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(120);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '考试科目');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $data['title_quiz_type']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('0DB0E59FF');

		$objPHPExcel->getActiveSheet()->setCellValue('A2', '试卷名称');
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $data['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('A3', '创建者用户');
		$objPHPExcel->getActiveSheet()->setCellValue('B3', $data['creator']);
		$objPHPExcel->getActiveSheet()->setCellValue('A4', '描述');
		$objPHPExcel->getActiveSheet()->setCellValue('B4', $data['description']);
		$objPHPExcel->getActiveSheet()->setCellValue('A5', '出版者,审核者');
		$objPHPExcel->getActiveSheet()->setCellValue('B5', $data['publisher']);

		$objPHPExcel->getActiveSheet()->setCellValue('A7', '创建时间');
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $data['date_created']);

		$objPHPExcel->getActiveSheet()->setCellValue('A15', '难度值');
		$objPHPExcel->getActiveSheet()->setCellValue('B15', $data['difficulty']);

		$objStyleA5 = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyleA5, 'A1:A16');

		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "file/download/down".date('YmdHis').".xls";
		$objWriter->save($file);
		echo "<a href='".$file."'>下载</a>";
	}

	public function getQuestions2($id_quiz_paper=null){
		if($id_quiz_paper==null && isset($_REQUEST['id_quiz_paper']))$id_quiz_paper = $_REQUEST['id_quiz_paper'];
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select * from ".$pfx."wls_question where id_quiz_paper = ".$id_quiz_paper;
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}
		$this->ques = $arr;
	}

	public function getPaper2($id=null){
		if($id==null && isset($_REQUEST['id']))$id = $_REQUEST['id'];
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select * from ".$pfx."wls_quiz_paper where id = ".$id;
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$this->paper = $temp;
		}
	}

	/**
	 * 以IFRAME的形式显示文件上传
	 * */
	public function viewUploadExcel(){
		$html = '
			<iframe src ="wls.php?controller=quiz_paper_normal&action=viewUploadExcel2" width="100%" height="300"></iframe>
		';
		echo $html;
	}

	public function viewUploadExcel2(){
		$html = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">		
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					目前只能以EXCEL导入的形式添加试题和题目
					<form action="wls.php?controller=quiz_paper_normal&action=saveUpload" method="post"
					enctype="multipart/form-data">
						<label for="file">EXCEL文件:</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="提交" />
					</form>
				</body>
			</html>		
		';
		echo $html;
	}

	public function saveUpload(){
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			move_uploaded_file($_FILES["file"]["tmp_name"],"file/upload/" . $_FILES["file"]["name"]);
			$this->importExcel("file/upload/" . $_FILES["file"]["name"]);
		}
	}
}