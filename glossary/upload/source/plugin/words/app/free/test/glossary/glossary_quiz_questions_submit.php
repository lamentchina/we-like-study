<?php 
class oop {
	
	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');
		
		return $conn;
	}
	
	private function insert($data){
		$conn = $this->conn();

		$sql = "select * from pre_wls_glossary_wrongs where id_user = ".$data['id_user']." and id_word = ".$data['id_word'].";  ";
		$temp = mysql_query($sql,$conn);
		$count = mysql_num_rows($temp);
		if($count==0){
			$keys = array_keys($data);
			$keys = implode(",",$keys);
			$values = array_values($data);
			$values = implode("','",$values);
			$sql = "insert into pre_wls_glossary_wrongs (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
		}else{
			//echo $count;
			$sql = "update pre_wls_glossary_wrongs set count = count + 1 where id_user = ".$data['id_user']." and id_word = ".$data['id_word'].";";
			mysql_query($sql,$conn);
		}
		//echo $sql;
	}
	
	public function create(){
		$conn = $this->conn();

		$sql = "drop table if exists pre_wls_glossary_wrongs;";
		mysql_query($sql,$conn);
		$sql = "
			create table pre_wls_glossary_wrongs(
				 id int primary key auto_increment	
				,id_word int default '0'
				,id_user int default '0'
				,username varchar(200) default '0'
				,word varchar(200) default '0'
				,translation varchar(200) default '0'
				,count int default 1		

				,CONSTRAINT pre_wls_glossary_wrongs_u UNIQUE (id_user,id_word)
							
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}
	
	public function submit(){
		session_start();
		$user = array(
			 'id'=>$_SESSION['wls_user']['id']
			,'username'=>$_SESSION['wls_user']['username']
		);

		for($i=0;$i<count($_POST['data']);$i++){
			$data = array(
				 'id_word'=>$_POST['data'][$i]['id']
				,'id_user'=>$user['id']
				,'username'=>$user['username']
				,'word'=>$_POST['data'][$i]['word']
				,'translation'=>$_POST['data'][$i]['translation']
			);
			
			$this->insert($data);
			//print_r($data);
		}
		
		$sql = "update pre_wls_user set 
			glossary_total = glossary_total + ".$_POST['total']." , 
			glossary_wrong = glossary_wrong + ".count($_POST['data'])."
				where id = ".$_SESSION['wls_user']['id']."
			";
		$conn = $this->conn();
		mysql_query($sql,$conn);
		
		echo $sql;
		
		$sql = "update pre_wls_user set 
			glossary_proportion = (glossary_total - glossary_wrong) / glossary_total 
				where id = ".$_SESSION['wls_user']['id']."
			";
		
		mysql_query($sql,$conn);
	}	
}
//echo 1123123;
$obj = new oop();
$obj->submit();

?>