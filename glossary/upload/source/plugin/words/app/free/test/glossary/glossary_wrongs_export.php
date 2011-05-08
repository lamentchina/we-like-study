<?php 
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

class oop {
	
	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');
		
		return $conn;
	}
	
	public function exportMyWrongs(){

		$conn = $this->conn();
		session_start();
		$sql = " select * from pre_wls_glossary_wrongs,pre_wls_glossary where id_user = ".$_SESSION['wls_user']['id']." 
			and pre_wls_glossary_wrongs.id_word = pre_wls_glossary.id
		;";
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', '单词');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '解释');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', '做错次数');
		
		$res = mysql_query($sql,$conn);
		$rowIndex = 2;
		while($temp = mysql_fetch_assoc($res)){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$rowIndex, $temp['word']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$rowIndex, $temp['translation']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$rowIndex, $temp['count']);
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save("test.xls");
		
		$html = "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
</head>
<body><a href='test.xls' />下载</a></body></html>";
		echo $html;
	}
}

$obj = new oop();
$obj->exportMyWrongs();

?>