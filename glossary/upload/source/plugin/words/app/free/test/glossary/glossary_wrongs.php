<?php 
class oop {
	
	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');
		
		return $conn;
	}
	
	public function getList(){
		$conn = $this->conn();
		
		session_start();
		
		$sql = "select * from pre_wls_glossary_wrongs where id_user =  ".$_SESSION['wls_user']['id'];

		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$data = array('data'=>$data);
		
		echo json_encode($data);
	}
}

$obj = new oop();
$obj->getList();

?>