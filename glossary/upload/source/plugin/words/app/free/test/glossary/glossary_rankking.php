<?php 
class oop {
	
	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');
		
		return $conn;
	}
	
	public function getRankking(){
		$conn = $this->conn();
		session_start();
		
		$sql = "select id,username,glossary_total,glossary_wrong,glossary_proportion from pre_wls_user order by  glossary_proportion desc ";
		$res = mysql_query($sql,$conn);
		
		$data = array();
		$index = 1;
		while($temp = mysql_fetch_assoc($res)){
			$temp['rank'] = $index;
			$temp['glossary_proportion'] = intval(($temp['glossary_proportion']*100));
			$data[] = $temp;
			$index ++;
		}
		
		$data = array('data'=>$data);
		echo json_encode($data);
	}
}

$obj = new oop();
$obj->getRankking();
?>