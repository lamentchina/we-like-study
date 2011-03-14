<?php 
$conn = mysql_connect('localhost','admin','admin');
mysql_select_db('wls',$conn);
mysql_query('SET NAMES UTF8');

$sql = '';
if(isset($_POST['anode']) && $_POST['anode']!=''){
	$sql = "select icon,id_level,name,isleaf from w_wls_subject where id_level like '".$_POST['anode']."__'";
}else{
	$sql = "select icon,id_level,name,isleaf from w_wls_subject where id_level like '__'";
}

$res = mysql_query($sql,$conn);
$data = array();
while($temp = mysql_fetch_assoc($res)){
	$temp['_is_leaf']=$temp['isleaf'];	
	$temp['id_level']=intval($temp['id_level']);
	$temp['_parent']=($_POST['anode']=='')?null:intval($_POST['anode']);

	$data[] = $temp;
}

//for($i=0;$i<count($data)-1;$i++){
//	if(substr($data[$i+1]['_id'],0,strlen($data[$i+1]['_id'])-2) == $data[$i]['_id']){
//		$data[$i]['_is_leaf']=false;	
//	}
//}
//if(strlen($data[count($data)-1]['_id'])== $data[count($data)-2]['_id']){
//	$data[count($data)-1]['_is_leaf'] = false;
//}

$arr = array(
	 'success'=>true
	,'total'=>count($data)
	,'data'=>$data
);


echo json_encode($arr);
?>