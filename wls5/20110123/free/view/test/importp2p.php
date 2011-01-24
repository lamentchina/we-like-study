<?php
header("Content-type: text/html; charset=utf-8");

function formatCellData($celldata){
	$celldata = str_replace('\n','',$celldata);
	$celldata = trim($celldata);
	return $celldata;
}


$path =  'E:/Projects/WEBS/PHP/wls4/file/test/group2privilege.xls';
include_once 'E:/Projects/WEBS/PHP/wls4/libs/phpexcel/Classes/PHPExcel.php';
include_once 'E:/Projects/WEBS/PHP/wls4/libs/phpexcel/Classes/PHPExcel/IOFactory.php';
include_once 'E:/Projects/WEBS/PHP/wls4/libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
$PHPReader->setReadDataOnly(true);
$objPHPExcel = $PHPReader->load($path);
//print_r($objPHPExcel);
$currentSheet = $objPHPExcel->getSheetByName('PrivilegeToGroup');

$allRow = $currentSheet->getHighestRow();
$allColmun = $currentSheet->getHighestColumn();

		$grouppoint = '';
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."1")->getValue()=='名称'){
				$grouppoint = ++$i;
				break;
			}
		}
		
		$groupsData = array();
		for($i=$grouppoint;$i<=$allColmun;$i++){
			$groupsData[] = array(
				'name'=>formatCellData($currentSheet->getCell($i."1")->getValue()),
				'id_level'=>$currentSheet->getCell($i."2")->getValue()
			);
		}
		print_r($groupsData);
		
		$privilegesData = array();
		for($i=4;$i<=$allRow;$i++){
			$privilegesData[] = array(
				'name'=>formatCellData($currentSheet->getCell('D'.$i)->getValue()),
				'id_level'=>$currentSheet->getCell('E'.$i)->getValue(),
				'ismenu'=>$currentSheet->getCell('C'.$i)->getValue(),
			);
		}		
		print_r($privilegesData);
		

		
?>