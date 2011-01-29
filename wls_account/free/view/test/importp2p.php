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
		for($i='A';$i<$grouppoint;$i++){
			if($currentSheet->getCell($i.'3')->getValue()=='代价'){
				$c_money = $i;
			}	
			if($currentSheet->getCell($i.'3')->getValue()=='图标'){
				$c_icon = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()=='描述'){
				$c_desc = $i;
			}
			if($currentSheet->getCell($i.'3')->getValue()=='名称'){
				$c_name = $i;
			}	
			if($currentSheet->getCell($i.'3')->getValue()=='编号'){
				$c_level = $i;
			}	
			if($currentSheet->getCell($i.'3')->getValue()=='菜单'){
				$c_menu = $i;
			}																	
		}
		for($i=4;$i<=$allRow;$i++){
			$privilegesData[] = array(
				'name'=>formatCellData($currentSheet->getCell($c_name.$i)->getValue()),
				'id_level'=>$currentSheet->getCell($c_level.$i)->getValue(),
				'ismenu'=>($currentSheet->getCell($c_menu.$i)->getValue()=='是')?1:0,
				'icon'=>$currentSheet->getCell($c_icon.$i)->getValue(),
				'description'=>$currentSheet->getCell($c_desc.$i)->getValue(),
				'money'=>$currentSheet->getCell($c_money.$i)->getValue(),
			);
		}		
		print_r($privilegesData);	

		
		$p2p = array();
		for($i=4;$i<=$allRow;$i++){
			for($i2=$grouppoint;$i2<=$allColmun;$i2++){
				if($currentSheet->getCell($i2.$i)->getValue()=='√'){
					$p2p[] = array(
						'id_level_group'=>$groupsData[ord($i2) - ord($grouppoint)]['id_level'],
						'id_level_privilege'=>$privilegesData[$i-4]['id_level']
					);
				}
			}
		}	
		
		print_r($p2p);
		
?>