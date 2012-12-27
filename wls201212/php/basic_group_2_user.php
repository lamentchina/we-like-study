<?php
class basic_group_2_user {
	
	public function getTree(){
		//tools::checkPermission("120106"); //TODO
		$CONN = tools::conn();
		//echo $CONN;
		//一次性输出所有的菜单树结构
		$sql = "SELECT
            basic_group.name,
            basic_group.code,
            basic_group.icon,
            basic_group.type,
            (select basic_group_2_user.id  from 
            	basic_group_2_user where 
            		basic_group_2_user.code_group = basic_group.code 
            		and basic_group_2_user.username = '".$_REQUEST['code']."') as cost
            FROM
            basic_group order by code";
        $sql = "
        select t.id,basic_group.code,basic_group.name from
        (
        SELECT
        basic_group_2_user.id,
        basic_group_2_user.username,
        basic_group_2_user.code_group
        FROM
        basic_group_2_user
         where basic_group_2_user.username = '".$_REQUEST['code']."'
        ) t right join basic_group on t.code_group = basic_group.code
        
        order by basic_group.code    
        ";		

		$res = mysql_query($sql,$CONN);
		//echo $res;
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			if($temp['id']!=''){
				$temp['ischecked'] = true;
			}
			$len = strlen($temp['code']);
			if($len==2){
				$data[] = $temp;
			}else if($len==4){
				$data[count($data)-1]['children'][] = $temp;
			}else if($len==6){
				$data[count($data)-1]['children'][count($data[count($data)-1]['children'])-1]['children'][] = $temp;
			}
		}
		echo json_encode($data);
	}
	
	public function import(){
	    tools::checkPermission("120101");
	    include_once '../libs/ajaxUpload/php.php';
	    // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("xls");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
       
        $uploader->savePath;
        self::importExcel($uploader->savePath);
	}
	
	public function importExcel($path=NULL){
	    if($path==NULL)$path=$_REQUEST['path']; //TODO delete
		include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';

		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load($path);
		
		$currentSheet = $obj->getSheetByName('basic_group_2_user');

		$allColumn = array($currentSheet->getHighestColumn());
		$allColumn = $allColumn[0];
		$CONN = tools::conn();


		$allRow = array($currentSheet->getHighestRow());
		$allRow = $allRow[0];

		
		
		$sql = "";
		for($j='B';$j<=$allColumn;$j++){
			mysql_query("delete from basic_group_2_user where code_group = '".$currentSheet->getCell($j.'2')->getValue()."' ;",$CONN);
			for($i=3;$i<=$allRow;$i++){
				$value = $currentSheet->getCell($j.$i)->getValue();
				if(trim($value)!=''){
					
					$sql = "insert into basic_group_2_user (username,code_group) values ('".$currentSheet->getCell('A'.$i)->getValue()."','".$currentSheet->getCell($j.'2')->getValue()."');";
					echo $sql;
					mysql_query($sql,$CONN);
				}
			}
		}
	}
	
	public function update(){
	    $CONN = tools::conn();
	    sleep(2.5);
		$code = $_REQUEST['code'];
		$codes = $_REQUEST['codes'];

		$sql = "delete from basic_group_2_user where username = '".$code."'; ";
		mysql_query($sql,$CONN);
		
		$arr = explode(',',$codes);
		for($i=0;$i<count($arr);$i++){
			$sql = "insert into basic_group_2_user ( username , code_group  ) values ('".$code."','".$arr[$i]."'); ";
			mysql_query($sql,$CONN);
		}
		
		$codes = str_replace(",", "','", $codes);
		$sql = "select * from basic_group where code in ('".$codes."')";
		$res = mysql_query($sql,$CONN);
		$groupnames = "";
		while ($data = mysql_fetch_assoc($res)) {
		    $groupnames .= $data['name'].",";
		}
		$sql = "update basic_user set groups = '".$groupnames."' where username = '".$code."' ";
		mysql_query($sql,$CONN);
		
		echo json_encode(array("state"=>1,'msg'=>'done'));
	}
	
	public function downloadAll(){
	    tools::checkPermission("120102");
	    //数据库连接参数
        $CONN = tools::conn();
		//初始化语言包
		$il8n = tools::getLanguage();   
		
        //导入EXCEL操作包
        include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
        include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
        include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

        $types = self::getTypes();
        $types = $types[0];
        $types = array_flip($types);
        
        $objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle( $il8n['basic']['group']['basic_group'] );
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $il8n['title'] );
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $il8n['type'] );
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $il8n['basic']['group']['code'] );
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $il8n['basic']['group']['icon'] );
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $il8n['basic']['group']['rank'] );
		$objPHPExcel->getActiveSheet()->setCellValue('F1', $il8n['basic']['group']['remark'] );
		
		$sql = "select * from basic_group ;";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		for($i=0;$i<count($data);$i++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2), $data[$i]['name'] );
			$objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2), $types[$data[$i]['type']] );
			$objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2), $data[$i]['code'] );
		    $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2), $data[$i]['icon'] );
		    $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2), $data[$i]['rank'] );
		    $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2), $data[$i]['remark'] );
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        
		$file = "basic_group_".date('YmdHis').".xls";
		$path =  "../file/download/".$file;
        $objWriter->save($path);
		echo json_encode(array('state'=>'1','msg'=>'done','path'=>$path,'file'=>$file));;
	}
}
?>