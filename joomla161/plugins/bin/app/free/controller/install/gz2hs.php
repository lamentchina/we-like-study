<?php
include_once dirname(__FILE__).'/../../model/question.php';
include_once dirname(__FILE__).'/../../model/quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';

class xml2array{
	public $str = '';
	public $type = 1; //0为字符串，1为文件

	function readxml(){
		if($this->type==1){
			$this->xmlstr = simplexml_load_file($this->str);//simplexml_load_file()作用是：将一个XML文档装载入一个对象中。

		}else{
			$this->xmlstr = simplexml_load_string($this->str);
		}
	}
	function xarray(){
		$this->readxml();
		$arrstr = array();
			
		$str = serialize($this->xmlstr); //serialize()  产生一个可存储的值的表示 

		$str = str_replace('O:16:"SimpleXMLElement"', 'a', $str);
		$arrstr = unserialize($str); //unserialize()  从已存储的表示中创建 PHP 的值

		return $arrstr;

	}

}

class install_gz2hs extends wls {

	public function html(){
		$folder = "F:/gz/chinese/";
		$filename = $this->t->getAllFiles($folder);
		//		print_r($filename);
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->c->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->c->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($filename).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_gz2hs&action=readxml',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length ){
				$('#data').text('done')
				return;
			}
			if(msg=='ok'){
				
				$('#data').text('index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			index++;
			down();
		}
	});
}
down();
</script>
</head>
<body>
<div id='data'><div>
</body>
</html>			
			";
		echo $html;
	}

	public function readxml(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$filename = $_REQUEST['id'];
//		$filename = "F:/gzsj/getPaper.action.xml";
		$content = file( $filename );
		$content = implode("\n", $content);

		$content = str_replace("\n","",$content);
		$content = str_replace("<![CDATA[","<CDATA>",$content);
		$content = str_replace("]]>","</CDATA>",$content);
		$content = str_replace("x-gbk","gbk",$content);
		
		//		$content = mb_convert_encoding($content,'UTF-8','GBK');
		header("Content-type: text/html; charset=utf-8");
		//		echo $content;exit();
		$obj = new xml2array();
		$obj->type = 0;
		$obj->str = $content;

		$arr = $obj->xarray();
		$arr = $arr['tm'];
//		print_r($arr);exit();
		for($i=0;$i<count($arr);$i++){
			$data = $arr[$i];
			if(isset($arr[$i]['tm_title']['CDATA'])){
				$data['tm_title'] = $arr[$i]['tm_title']['CDATA'];
			}
			if(isset($arr[$i]['tm_question']['CDATA'])){
				$data['tm_question'] = (string)$arr[$i]['tm_question']['CDATA'];
			}
			if(isset($arr[$i]['tm_answer']['CDATA'])){
				if(count($arr[$i]['tm_answer']['CDATA'])==0){
//					print_r($arr[$i]['tm_answer']['CDATA']);exit();
					$data['tm_answer'] = '';
				}else{
					$data['tm_answer'] = $arr[$i]['tm_answer']['CDATA'];
				}
			}else{
				$data['tm_answer'] = '';
//				print_r($arr[$i]['tm_answer']);exit();
			}
			if(count($arr[$i]['tm_queImg'])==0){
				$data['tm_queImg'] = '';
			}else{
				$data['tm_queImg'] = $data['tm_queImg']['CDATA'];
			}
			if(count($arr[$i]['tm_ansImg'])==0){
				$data['tm_ansImg'] = '';
			}
//			print_r($data);exit();
			$this->insert($data);
		}
	}

	public function create(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();		
		
		$sql = "drop table if exists ".$pfx."wls_temp;";
		mysql_query($sql,$conn);
		$sql = "
				create table ".$pfx."wls_temp(
				
					 id int primary key auto_increment	
					,tm_score varchar(200) default '0'
					,tm_id varchar(200) unique default '0'
					,tm_title varchar(200) default '0'
					,tm_timuNum varchar(200) default '0'
					,tm_ansNum varchar(200) default '0'
					,tm_question varchar(200) default '0'
					,tm_answer varchar(200) default '0'
					,tm_typeId varchar(200) default '0'
					,tm_styleId varchar(200) default '0'
					,tm_styleName varchar(200) default '0'
					,tm_queImg varchar(200) default '0'
					,tm_ansImg varchar(200) default '0'
								
				) DEFAULT CHARSET=utf8;
				";
//		echo $sql;
		mysql_query($sql,$conn);
	}
	
	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_temp (".$keys.") values ('".$values."')";

		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		return $id;
	}
	
	public function downloadimage(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		$sql = "select * from ".$pfx."wls_temp where tm_queImg <> ''; ";
		$data = array();
		$res = mysql_query($sql,$conn);
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp['tm_queImg'];	
		}
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"".$this->c->libsPath."jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"".$this->c->libsPath."jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">

var ids = ".json_encode($data).";

var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_gz2hs&action=downloadimage2',
		data: {id:ids[index],index:index},
		success: function(msg){
			if(index==ids.length-1 ){
				$('#data').text('done')
				return;
			}
			if(msg=='ok'){
				
				$('#data').text('index:'+index+'/'+ids.length+';  file:'+ids[index]);
			}else{
				$('#data').text('wrong!');
			}
			index++;
			down();
		}
	});
}
down();
</script>
</head>
<body>
<div id='data'><div>
</body>
</html>			
			";
		echo $html;
	}
	
	
	public function downloadimage2(){
		$filename = "F:/images_all.lst";
		if(file_exists($filename)){
			$handle=fopen($filename,"a");
			fwrite($handle,$_POST['id']."\n");
			fclose($handle);
		}else{
			$handle=fopen($filename,"a");
			fwrite($handle,$_POST['id']."\n");
			fclose($handle);
		}
	}
	
	public function getPaper(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		
		
		
		$sql = "update ".$pfx."wls_temp set tm_question = '' where tm_question = 'Array';";
		$res = mysql_query($sql,$conn);
		$sql = "select * from ".$pfx."wls_temp limit 0,50 ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp=mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		print_r($data);
	}
}
?>