<?php
class gis{
	function conn(){
		$conn = mysql_connect("127.0.0.1","root","root");
		mysql_select_db("dbtest",$conn);
		mysql_query('SET NAMES UTF8');
		return $conn;
	}
	
	function gisgrid(){
		$conn = $this->conn();
		$where = " where 1 =1  ";
		$sql = "select astext(polygon) as gis,id,name,code_community from community_building  ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;			
		}		
		
		echo json_encode($arr);
	}
	
	function familygrid(){
		$arr = array();
		$arr[] = array(
			'id' => 1,
			'name' => '张三',
			'count' => 4,
			'type' => '低保户'
		);
		$arr[] = array(
			'id' => 21,
			'name' => '张三',
			'count' => 4,
			'type' => '低保户'
		);
		$arr[] = array(
			'id' => 31,
			'name' => '张三',
			'count' => 4,
			'type' => '低保户'
		);
		
		echo json_encode(array('data'=>$arr));
	}
}

$controller = new gis();
eval('$controller->'.$_REQUEST['action'].'();');
