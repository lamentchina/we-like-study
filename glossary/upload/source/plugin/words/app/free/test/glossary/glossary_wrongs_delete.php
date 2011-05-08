<?php 
class oop {
	
	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');
		
		return $conn;
	}
	
	public function delete(){
		$ids = $_POST['ids'];
		$conn = $this->conn();
		
		$sql = "delete from pre_wls_glossary_wrongs where id in (".$ids.") ;";
		mysql_query($sql,$conn);
	}
}

$obj = new oop();
$obj->delete();

?>