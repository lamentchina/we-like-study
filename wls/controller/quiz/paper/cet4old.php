<?php
include_once 'controller/quiz/paper/paper.php';

/**
 * CET4
 * 旧题型
 * */
class quiz_paper_cet4old extends quiz_paper implements quiz_paper_{

	//数据读取的来源,路径
	public $excelPath = '';

	//试卷插入数据库后,获得的编号
	public $id_paper = '';
	public $title_paper = '';

	public $id_quiz_type = 2;
	public $title_quiz_type = 'CET4';

	//试卷的配置信息,包括 题目,作者,来源,题型等
	public $paper = array();

	//1道写作题
	public $writing = array();

	//1篇快速阅读
	public $fastread = array();

	//6道短文听力
	public $listencv = array();

	//1道听力填单词
	public $listencloze = array();

	//2篇短文阅读
	public $read = array();

	//1篇深入阅读
	public $depthRead = array();

	//1道完形填空
	public $cloze = array();

	//5道翻译题
	public $translate = array();

	/**
	 * 题目数,编号集合
	 * */
	public $questions = array();

	/**
	 * 子题目数,编号集
	 * */
	public $subquestions = array();

	public function getPaper(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('paper');
		$data = array(
			'title' => $currentSheet->getCell('B2')->getValue(),
			'title_quiz_type' => $this->title_quiz_type,
			'description' => $currentSheet->getCell('B4')->getValue(),
			'id_quiz_type'=>$this->id_quiz_type,
			'date_created'=>date('Y-m-d'),
		);
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
		$this->id_paper = mysql_insert_id($conn);
		$this->title_paper = $data['title'];
		$data = array(
			'id_paper'=>$this->id_paper,
		);
	}

	/**
	 * 获得写作内容
	 * */
	public function getWriting(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part1');
		$data = array(
			'description'=>$this->format($currentSheet->getCell('B33')->getValue()),
			'title'=>$this->format($currentSheet->getCell('B3')->getValue()),
			'details'=>"{\"lines\":\"10\"}",
			'id_quiz_paper' => $this->id_paper,
			'title_quiz_paper' => $this->title_paper,
			'id_quiz_type' => $this->id_quiz_type,
			'title_quiz_type' => 'CET4(老题型)',
			'details'=>"{\"markingmethod\":\"manual\"}",
			'type'=>4,
			'markingmethod'=>1,
			'cent'=>106.5,
		);

		$this->writing = $data;
	}

	public function saveWriting(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->writing;

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);

		$this->writing['id'] = mysql_insert_id($conn);
	}

	public function getFastRead(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part2');
			
		$allRow = $currentSheet->getHighestRow();
		$title = '';
		for($i=2;$i<=$allRow;$i++){
			$title .= $currentSheet->getCell('A'.$i)->getValue()."<br/>";
		}
		$data = array(
			'title'=>$this->format($title),
			'child'=>array(),
			'type'=>5,
			'description'=>'null',
			'id_quiz_paper' => $this->id_paper,
			'title_quiz_paper' => $this->title_paper,
			'id_quiz_type' => $this->id_quiz_type,
			'title_quiz_type' => 'CET4(老题型)',
		);

		$currentSheet = $this->phpexcel->getSheetByName('part2_2');

		for($i=1;$i<=13;$i+=2){
			$data['child'][] = array(
					'title'=>$this->format($currentSheet->getCell('B'.($i))->getValue()),
					'description'=>$this->format($currentSheet->getCell('B'.($i+1))->getValue()),
					'answer'=>$this->format($currentSheet->getCell('A'.($i+1))->getValue()),
					'details'=>"{\"options\":[{\"option\":\"Y\",\"title\":\" \"}
									,{\"option\":\"N\",\"title\":\" \"}
									,{\"option\":\"NG\",\"title\":\" \"}
									],\"display\":\"vertical\"}
					",
					'type'=>1,
					'id_quiz_paper' => $this->id_paper,
					'title_quiz_paper' => $this->title_paper,
					'id_quiz_type' => $this->id_quiz_type,
					'title_quiz_type' => 'CET4(老题型)',
					'cent'=>7.1,
			);
		}
		for($i=15;$i<=21;$i+=3){
			$data['child'][] = array(
					'title'=>$this->format($currentSheet->getCell('B'.($i))->getValue()),
					'description'=>$this->format($currentSheet->getCell('B'.($i+2))->getValue()),
					'answer'=>$this->format($currentSheet->getCell('B'.($i+1))->getValue()),
					'type'=>4,
					'details'=>"{\"lines\":\"1\"}",		
					'id_quiz_paper' => $this->id_paper,
					'title_quiz_paper' => $this->title_paper,
					'id_quiz_type' => $this->id_quiz_type,
					'title_quiz_type' => 'CET4(老题型)',
					'cent'=>7.1,
			);
		}
		$this->fastread= $data;
	}


	public function saveFastRead(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->fastread;
		$child = $data['child'];
		unset($data['child']);

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);

		$this->fastread['id'] = mysql_insert_id($conn);
		$this->questions[] = $this->fastread['id'];
		
		for($j=0;$j<count($child);$j++){
			$child[$j]['id_parent'] = $this->fastread['id'];

			$keys = array_keys($child[$j]);
			$keys = implode(",",$keys);
			$values = array_values($child[$j]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$this->fastread['child'][$j]['id'] = mysql_insert_id($conn);
			$this->subquestions[] = $this->fastread['child'][$j]['id'];
		}
	}

	public function getListencv(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part3');
		$ques = array(
		array(4,8),
		array(47,4),
		array(70,3),
		array(91,3),
		array(110,3),
		array(129,4),
		);
		for($i=0;$i<count($ques);$i++){
			$data = array(
				'description'=>$this->format($currentSheet->getCell('A'.($ques[$i][0]+1))->getValue()),
				'title'=>$this->format($currentSheet->getCell('A'.$ques[$i][0])->getValue()),
				'details'=>"{\"listen\":\"".$currentSheet->getCell('C'.($ques[$i][0]+2))->getValue()."\"}",
				'child'=>array(),
				'id_quiz_paper' => $this->id_paper,
				'title_quiz_paper' => $this->title_paper,
				'id_quiz_type' => $this->id_quiz_type,
				'title_quiz_type' => 'CET4(老题型)',

				'type'=>5,
			);
			for($j=0;$j<$ques[$i][1];$j++){
				$data['child'][] = array(
					'description'=>$this->format($currentSheet->getCell('C'.($ques[$i][0]+7+($j*5)))->getValue()),
					'answer'=>$this->format($currentSheet->getCell('A'.($ques[$i][0]+7+($j*5)))->getValue()),
					'details'=>"{\"options\":[{\"option\":\"A\",\"title\":\"".$this->format($currentSheet->getCell('C'.($ques[$i][0]+3+($j*5)))->getValue())."\"}
									,{\"option\":\"B\",\"title\":\"".$this->format($currentSheet->getCell('C'.($ques[$i][0]+4+($j*5)))->getValue())."\"}
									,{\"option\":\"C\",\"title\":\"".$this->format($currentSheet->getCell('C'.($ques[$i][0]+5+($j*5)))->getValue())."\"}
									,{\"option\":\"D\",\"title\":\"".$this->format($currentSheet->getCell('C'.($ques[$i][0]+6+($j*5)))->getValue())."\"}
									]}
					",
					'id_quiz_paper' => $this->id_paper,
					'title_quiz_paper' => $this->title_paper,
					'id_quiz_type' => $this->id_quiz_type,
					'title_quiz_type' => 'CET4(老题型)',
					'type'=>1,
					'cent'=>7.1,
				);
			}
			$this->listencv[] = $data;
		}
	}

	public function saveListencv(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->listencv;
		for($i=0;$i<count($data);$i++){
			$child = $data[$i]['child'];
			unset($data[$i]['child']);

			$keys = array_keys($data[$i]);
			$keys = implode(",",$keys);
			$values = array_values($data[$i]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$sql = '';
			$this->listencv[$i]['id'] = mysql_insert_id($conn);
			$this->questions[] = $this->listencv[$i]['id'];
			for($j=0;$j<count($child);$j++){
				$child[$j]['id_parent'] = $this->listencv[$i]['id'];

				$keys = array_keys($child[$j]);
				$keys = implode(",",$keys);
				$values = array_values($child[$j]);
				$values = implode("','",$values);
				$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
				mysql_query($sql,$conn);
				$sql = '';
				$this->listencv[$i]['child'][$j]['id'] = mysql_insert_id($conn);
				$this->subquestions[] = $this->listencv[$i]['child'][$j]['id'];
			}
		}
	}

	public function getListenCloze(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part3_2');
		$data = array(
			'description'=>$this->format($currentSheet->getCell('C3')->getValue()),
			'title'=>$this->format($currentSheet->getCell('C6')->getValue()),
			'details'=>"{\"listen\":\"".$currentSheet->getCell('C4')->getValue()."\"}",
			'type'=>5,
			'id_quiz_paper' => $this->id_paper,
			'title_quiz_paper' => $this->title_paper,
			'id_quiz_type' => $this->id_quiz_type,
			'title_quiz_type' => 'CET4(老题型)',
			'child'=>array(),
		);
		$index = 0;
		for($j=7;$j<=37;$j+=3){
			$index++;
			if($index<=8){
				$cent = 3.55;
			}else{
				$cent = 14.2;
			}
			$data['child'][] = array(
				'description'=>$this->format($currentSheet->getCell('C'.($j+2))->getValue()),
				'answer'=>$this->format($currentSheet->getCell('C'.($j+1))->getValue()),
				'id_quiz_paper' => $this->id_paper,
				'title_quiz_paper' => $this->title_paper,
				'id_quiz_type' => $this->id_quiz_type,
				'title_quiz_type' => 'CET4(老题型)',
				'details'=>"{\"lines\":\"1\"}",			
				'type'=>4,
				'cent'=>$cent,
			);
		}
		$this->listencloze = $data;
	}

	public function saveListenCloze(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->listencloze;

		$child = $data['child'];
		unset($data['child']);

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		$sql = '';
		$this->listencloze['id'] = mysql_insert_id($conn);
		$this->questions[] = $this->listencloze['id'];
		for($j=0;$j<count($child);$j++){

			$child[$j]['id_parent'] = $this->listencloze['id'];

			$keys = array_keys($child[$j]);
			$keys = implode(",",$keys);
			$values = array_values($child[$j]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$sql = '';
			$this->listencloze['child'][$j]['id'] = mysql_insert_id($conn);
			$this->subquestions[] = $this->listencloze['child'][$j]['id'];
		}
	}

	public function getDepthRead(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part4');
		$allRow = $currentSheet->getHighestRow();

		$data = array(
			'title'=>'',
			'child'=>array(),
			'id_quiz_paper' => $this->id_paper,
			'title_quiz_paper' => $this->title_paper,
			'id_quiz_type' => $this->id_quiz_type,
			'title_quiz_type' => 'CET4(老题型)',
			'details'=>'{"foo":"bar"}',	
			'type'=>5,	
		);

		$index = 0;
		for($i=5;$i<=$allRow;$i++){
			if($currentSheet->getCell('A'.$i)->getValue()=='47'){
				$index = $i;
				break;
			}
			$data['title'] .= $currentSheet->getCell('C'.$i)->getValue();
		}
		$data['title'] = $this->format($data['title']);
		for($i=$index;$i<=$allRow;$i+=2){
			if($currentSheet->getCell('A'.$i)->getValue()==''){
				$index = $i;
				break;
			}
			$data['child'][] = array(
				'title'=>'&nbsp;',
				'answer'=>$this->format($currentSheet->getCell('A'.($i+1))->getValue()),
				'description'=>$this->format($currentSheet->getCell('C'.($i+1))->getValue()),
				'details'=>"{\"lines\":\"1\"}",
				'type'=>4,
				'id_quiz_paper' => $this->id_paper,
				'title_quiz_paper' => $this->title_paper,
				'id_quiz_type' => $this->id_quiz_type,
				'title_quiz_type' => 'CET4(老题型)',
				'cent'=>3.55,
			);
		}
		$this->depthRead = $data;
	}

	public function saveDepthRead(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->depthRead;

		$child = $data['child'];
		unset($data['child']);

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		$sql = '';
		$this->depthRead['id'] = mysql_insert_id($conn);
		$this->questions[] = $this->depthRead['id'];
		for($j=0;$j<count($child);$j++){
			$child[$j]['id_parent'] = $this->depthRead['id'];
			$keys = array_keys($child[$j]);
			$keys = implode(",",$keys);
			$values = array_values($child[$j]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$sql = '';
			$this->depthRead['child'][$j]['id'] = mysql_insert_id($conn);
			$this->subquestions[] = $this->depthRead['child'][$j]['id'];
		}
	}

	public function getRead(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part4');
		$allRow = $currentSheet->getHighestRow();

		$data = array();

		$data[] = array(
			'title'=>'',
			'child'=>array(),
			'id_quiz_paper'=>$this->id_paper,
			'id_quiz_type'=>$this->id_quiz_type,
			'details'=>'{"foo":"bar"}',	
			'type'=>5,	
		);
		$data[] = array(
			'title'=>'',
			'child'=>array(),
			'id_quiz_paper'=>$this->id_paper,
			'id_quiz_type'=>$this->id_quiz_type,	
			'details'=>'{"foo":"bar"}',	
			'type'=>5,	
		);

		$index = 0;
		for($i=5;$i<=$allRow;$i++){
			if($currentSheet->getCell('A'.$i)->getValue()=='Passage One'){
				$index = $i;
				break;
			}
		}
		for($i=$index+1;$i<=$allRow;$i++){
			if($currentSheet->getCell('A'.$i)->getValue()=='57'){
				$index = $i;
				break;
			}
			$data[0]['title'] .= $this->format($currentSheet->getCell('C'.$i)->getValue());
		}

		for($i=$index;$i<=$allRow;$i+=6){
			if($currentSheet->getCell('A'.$i)->getValue()=='Passage Two'){
				$index = $i;
				break;
			}
			$data[0]['child'][] = array(
				'title'=>$this->format($currentSheet->getCell('C'.$i)->getValue()),
				'answer'=>$this->format($currentSheet->getCell('A'.($i+5))->getValue()),
				'description'=>$this->format($currentSheet->getCell('C'.($i+5))->getValue()),
				'details'=>"{\"options\":[{\"option\":\"A\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+1))->getValue())."\"}
										,{\"option\":\"B\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+2))->getValue())."\"}
										,{\"option\":\"C\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+3))->getValue())."\"}
										,{\"option\":\"D\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+4))->getValue())."\"}
										]}
				",
				'type'=>1,
				'id_quiz_paper' => $this->id_paper,
				'title_quiz_paper' => $this->title_paper,
				'id_quiz_type' => $this->id_quiz_type,
				'title_quiz_type' => 'CET4(老题型)',
				'cent'=>14.2,
			);
		}

		for($i=$index+1;$i<=$allRow;$i++){
			if($currentSheet->getCell('A'.$i)->getValue()=='62'){
				$index = $i;
				break;
			}
			$data[1]['title'] .= $this->format($currentSheet->getCell('C'.$i)->getValue());
		}

		for($i=$index;$i<=$allRow;$i+=6){
			$data[1]['child'][] = array(
				'title'=>$this->format($currentSheet->getCell('C'.$i)->getValue()),
				'answer'=>$this->format($currentSheet->getCell('A'.($i+5))->getValue()),
				'description'=>$this->format($currentSheet->getCell('C'.($i+5))->getValue()),
				'details'=>"{\"options\":[{\"option\":\"A\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+1))->getValue())."\"}
										,{\"option\":\"B\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+2))->getValue())."\"}
										,{\"option\":\"C\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+3))->getValue())."\"}
										,{\"option\":\"D\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i+4))->getValue())."\"}
										]}
				",
				'type'=>1,
				'id_quiz_paper' => $this->id_paper,
				'title_quiz_paper' => $this->title_paper,
				'id_quiz_type' => $this->id_quiz_type,
				'title_quiz_type' => 'CET4(老题型)',
				'cent'=>14.2,
			);
		}
		$this->read = $data;
	}

	public function saveRead(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->read;
		for($i=0;$i<count($data);$i++){
			$child = $data[$i]['child'];
			unset($data[$i]['child']);
			$keys = array_keys($data[$i]);
			$keys = implode(",",$keys);
			$values = array_values($data[$i]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";

			mysql_query($sql,$conn);
			$sql = '';
			$this->read[$i]['id'] = mysql_insert_id($conn);
			$this->questions[] = $this->read[$i]['id'];
			for($j=0;$j<count($child);$j++){
				$child[$j]['id_parent'] = $this->read[$i]['id'];

				$keys = array_keys($child[$j]);
				$keys = implode(",",$keys);
				$values = array_values($child[$j]);
				$values = implode("','",$values);
				$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
				mysql_query($sql,$conn);
				$sql = '';
				$this->read[$i]['child'][$j]['id'] = mysql_insert_id($conn);
				$this->subquestions[] = $this->read[$i]['child'][$j]['id'];
			}
		}
	}

	public function getCloze(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part5');
		$allRow = $currentSheet->getHighestRow();
		$title = '';
		$index = '';
		for($i=4;$i<=$allRow;$i++){
			if($currentSheet->getCell('A'.$i)->getValue()=='67'){
				$index = $i;
				break;
			}
			$title .= $currentSheet->getCell('B'.$i)->getValue()."<br/>";
		}
		$data = array(
			'title'=>$this->format($title),
			'child'=>array(),
			'type'=>5,
			'description'=>'null',
			'details'=>'{"foo":"bar"}',	
			'id_quiz_paper' => $this->id_paper,
			'title_quiz_paper' => $this->title_paper,
			'id_quiz_type' => $this->id_quiz_type,
			'title_quiz_type' => 'CET4(老题型)',
		);

		for($i=$index;$i<=$allRow;$i+=2){
			$data['child'][] = array(
					'title'=>'',
					'description'=>$this->format($currentSheet->getCell('C'.($i+1))->getValue()),
					'answer'=>$this->format($currentSheet->getCell('A'.($i+1))->getValue()),
					'details'=>"{\"options\":[{\"option\":\"A\",\"title\":\"".$this->format($currentSheet->getCell('C'.($i))->getValue())."\"}
									,{\"option\":\"B\",\"title\":\"".$this->format($currentSheet->getCell('E'.($i))->getValue())."\"}
									,{\"option\":\"C\",\"title\":\"".$this->format($currentSheet->getCell('G'.($i))->getValue())."\"}
									,{\"option\":\"D\",\"title\":\"".$this->format($currentSheet->getCell('I'.($i))->getValue())."\"}
									],\"display\":\"vertical\"}
					",
					'type'=>1,
					'id_quiz_paper' => $this->id_paper,
					'title_quiz_paper' => $this->title_paper,
					'id_quiz_type' => $this->id_quiz_type,
					'title_quiz_type' => 'CET4(老题型)',	
					'cent'=>3.55,	
			);
		}
		$this->cloze= $data;
	}

	public function saveCloze(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->cloze;
		$child = $data['child'];
		unset($data['child']);

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		$sql = '';
		$this->cloze['id'] = mysql_insert_id($conn);
		$this->questions[] = $this->cloze['id'];
		for($j=0;$j<count($child);$j++){
			$child[$j]['id_parent'] = $this->cloze['id'];

			$keys = array_keys($child[$j]);
			$keys = implode(",",$keys);
			$values = array_values($child[$j]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$sql = '';
			$this->cloze['child'][$j]['id'] = mysql_insert_id($conn);
			$this->subquestions[] = $this->cloze['child'][$j]['id'];
		}
	}
	
	public function getTranslate(){
		if($this->phpexcel==null)$this->readExcel();
		$currentSheet = $this->phpexcel->getSheetByName('part6');
		$data = array();
		for($i=4;$i<=16;$i+=3){
			$temp = array(
				'title'=>$this->format($currentSheet->getCell('B'.($i))->getValue()),
				'type'=>4,
				'answer'=>$this->format($currentSheet->getCell('B'.($i+1))->getValue()),
				'description'=>$this->format($currentSheet->getCell('B'.($i+2))->getValue()),
				'id_quiz_paper' => $this->id_paper,
				'title_quiz_paper' => $this->title_paper,
				'id_quiz_type' => $this->id_quiz_type,
				'title_quiz_type' => 'CET4(老题型)',
				'details'=>"{\"lines\":\"1\"}",		
				'cent'=>7.1,	
			);
			
			$data[] = $temp;
		}	
		$this->translate = $data;
	}
	
	public function saveTranslate(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$data = $this->translate;
		for($i=0;$i<count($data);$i++){
			$keys = array_keys($data[$i]);
			$keys = implode(",",$keys);
			$values = array_values($data[$i]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$sql = '';
			$this->translate[$i]['id'] = mysql_insert_id($conn);
			$this->questions[] = $this->translate[$i]['id'];
			$this->subquestions[] = $this->translate[$i]['id'];
		}
	}
	
	public function updatePaper(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$ids = '';
		for($i=0;$i<count($this->questions);$i++){
			$ids .= $this->questions[$i].",";
		}		
		$ids = substr($ids,0,strlen($ids)-1);
		
		$ids2 = '';
		for($i=0;$i<count($this->subquestions);$i++){
			$ids2 .= $this->subquestions[$i].",";
		}		
		$ids2 = substr($ids2,0,strlen($ids2)-1);
		$sql = "update ".$pfx."wls_quiz_paper set 
			count_quetions = '".count($this->questions)."' ,
			count_subquestions = '".count($this->subquestions)."' ,
			subquestions = '".$ids2."',
			islisten = 1,
			questions = '".$ids."',
			price_money = 5,
			price_score = 5,
			rank = 1,
			difficulty = 4	
			
			where id = ".$this->id_paper.";
		";
		mysql_query($sql,$conn);
	}
	
	public function updateType(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "update ".$pfx."wls_quiz_type set count_paper = count_paper+1 ,  count_question =  count_question+".(count($this->questions)+count($this->subquestions))." where id =  ".$this->id_quiz_type;
//		echo $sql;
		mysql_query($sql,$conn);
	}

	public function importExcel(){		
		if(isset($_REQUEST['initall'])){
			
			include_once 'controller/user.php';
			$obj = new user();
			$obj->initTable();		
//			$obj->initTestData();				

			include_once 'controller/quiz/type.php';
			$obj = new quiz_type();
			$obj->initTable();		
			$obj->initTestData();	
			
			include_once 'controller/quiz/paper/paper.php';
			$obj = new quiz_paper();
			$obj->initTable();
			
			include_once 'controller/question/question.php';
			$obj = new question();
			$obj->initTable();			
			
			include_once 'controller/quiz/wrongs.php';
			$obj = new quiz_wrongs();
			$obj->initTable();	

			include_once 'controller/question/record.php';
			$obj = new question_record();
			$obj->initTable();	

			include_once 'controller/quiz/record.php';
			$obj = new quiz_record();
			$obj->initTable();				
		}
		
		$this->getPaper();
		$this->savePaper();

		$this->getWriting();
		$this->saveWriting();

		$this->getFastRead();
		$this->saveFastRead();

		$this->getListencv();
		$this->saveListencv();

		$this->getListenCloze();
		$this->saveListenCloze();

		$this->getDepthRead();
		$this->saveDepthRead();

		$this->getRead();
		$this->saveRead();

		$this->getCloze();
		$this->saveCloze();
		
		$this->getTranslate();
		$this->saveTranslate();
		
		$this->updatePaper();
		
		$this->updateType();
	}
}