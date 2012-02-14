<?php
$arr = array(
	 'identifier'=>'id'
	,'label' => 'name'
	,'items' => array()
);

for($i=0;$i<100;$i++){
	$arr['items'][] = array(
		 'id' => $_REQUEST['page'].$i
		,'name' => "char".$i
	);
}

echo json_encode($arr);