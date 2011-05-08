<?php

class oop{

	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');

		return $conn;
	}

	public function getQuestions(){
		$conn = $this->conn();
		session_start();
		$sql = " SELECT *
			FROM pre_wls_glossary,pre_wls_glossary_wrongs 
				where pre_wls_glossary.id = pre_wls_glossary_wrongs.id_word
				and id_user = ".$_SESSION['wls_user']['id']."
			LIMIT 0 , 50 ";

		$res = mysql_query($sql,$conn);
		$data = array();
		while ($temp = mysql_fetch_assoc($res)) {
			$data[] = $temp;
		}
		$count = count($data);
		if($count<=5)die('less than 5');

		$numbers = range (0,$count-1);
		shuffle($numbers);

		for($i=0;$i<$count;$i++){
			$num = rand(0, $count-4);
			$data[$i]['option1'] = $data[$numbers[$num]]['translation'];
			$data[$i]['option2'] = $data[$numbers[$num+1]]['translation'];
			$data[$i]['option3'] = $data[$numbers[$num+2]]['translation'];
			$data[$i]['option4'] = $data[$i]['translation'];
			$answer = rand(1,4);
			$answerArr = array('A','B','C','D');
			$data[$i]['title'] = $data[$i]['word'];
			$data[$i]['type'] = '1';
			$data[$i]['layout'] = '1';
			$data[$i]['optionlength'] = '4';
			$data[$i]['cent'] = '1';

			$data[$i]['answerData'] = array(
				 'markingmethod'=>0
				,'answer'=>$answerArr[($answer-1)]
				,'description'=>'nothing'
				,'word'=>$data[$i]['word']
			);

			$temp = $data[$i]['option4'];
			$data[$i]['option4'] = $data[$i]['option'.$answer];
			$data[$i]['option'.$answer] = $temp;
		}
		echo  json_encode($data);
	}
}

$obj = new oop();
$obj->getQuestions();
?>