<?php
class gis{
	function conn(){
		$conn = mysql_connect("127.0.0.1","root","root");
		mysql_select_db("dbtest",$conn);
		mysql_query('SET NAMES UTF8');
		return $conn;
	}
	
	function grid(){
		$conn = $this->conn();
		$where = " where 1 =1  ";
		$sql = "select astext(polygon) as gis,id,name,code_community from community_building  ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			
			$str = $temp['gis'];
			$str = str_replace("POLYGON((","",$str);
			$str = str_replace("))","",$str);
			$arr1 = explode(",", $str);
			$arr2 = array();
			for($i=0;$i<count($arr1);$i++){
				$arr3 = explode(" ", $arr1[$i]);
				$arr2[] = $arr3;
			}
			$temp['array'] = $arr2;
			unset($temp['gis']);
			
			$arr[] = $temp;
			
		}
		
		return $arr;
	}
}

$obj = new gis();
//print_r($obj->grid());
echo json_encode($obj->grid());