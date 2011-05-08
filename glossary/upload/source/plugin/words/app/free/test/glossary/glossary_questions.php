<?php 
$conn = mysql_connect('localhost','root','');
mysql_select_db('ultrax',$conn);
mysql_query('SET NAMES UTF8');


$sql = " SELECT * 
FROM pre_wls_glossary
WHERE subject =  '".$_GET['id_level']."'
ORDER BY RAND( ) 
LIMIT 0 , 50 ";

$res = mysql_query($sql,$conn);
$data = array();
while ($temp = mysql_fetch_assoc($res)) {
	$data[] = $temp;
}
$count = count($data);
//sleep(2);

	$numbers = range (0,$count-1); 
	shuffle($numbers); 
	//print_r($numbers);exit();

for($i=0;$i<$count;$i++){
	
	$num = rand(0, $count-4);


	$data[$i]['option1'] = $data[$numbers[$num]]['translation'];
	$data[$i]['option2'] = $data[$numbers[$num+1]]['translation'];
	$data[$i]['option3'] = $data[$numbers[$num+2]]['translation'];

	$data[$i]['option4'] = $data[$i]['translation'];
	
	//print_r($data);exit();
	
	$answer = rand(1,4);
	$answerArr = array('A','B','C','D');
	//print_r($answerArr);exit();
	
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
		,'translation'=>$data[$i]['translation']
	);
	
	$temp = $data[$i]['option4'];	
	$data[$i]['option4'] = $data[$i]['option'.$answer];
	$data[$i]['option'.$answer] = $temp;
}
echo  json_encode($data);
//exit();
//print_r($data);
?>