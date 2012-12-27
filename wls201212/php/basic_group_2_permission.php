<?php
class basic_group_2_permission {
	
	public function getTree(){
		//tools::checkPermission("120106"); //TODO
		$CONN = tools::conn();
		//echo $CONN;
		//一次性输出所有的菜单树结构
		$sql = "
            select basic_permission.id,basic_permission.code,basic_permission.name,basic_permission.icon,t.cost,t.credits from basic_permission left join
            (
                SELECT
                basic_group_2_permission.cost,
                basic_group_2_permission.credits,
                basic_group_2_permission.id_permission
                FROM
                basic_group_2_permission
                
                WHERE
                basic_group_2_permission.id_group =  '".$_REQUEST['id']."'
            ) t
            on t.id_permission = basic_permission.id
		";
		//echo $sql;
		//$sql = "select * from basic_group_2_permission";

	    $res = mysql_query($sql,$CONN);

		$data = array();
		while($temp = mysql_fetch_assoc($res)){
            $temp['name_'] = $temp['name'];
			if($temp['cost']!=''){
				$temp['ischecked'] = true;
				
		        $temp['name'] = $temp['name']." ".$temp['cost']." ".$temp['credits'];
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
	
	public function modify(){
        tools::checkPermission("120104");
        $CONN = tools::conn();	

		$json = json_decode($_REQUEST['json'],true);
        $code = $json['code'];
		unset($json['code']);		
		
		$keys = array_keys($json);		
		$sql = "update basic_group set ";
		for($i=0;$i<count($json);$i++){
			$sql.= $keys[$i]."='".$json[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where code = '".$code."' ;";	
        sleep(1.5);
		try {
		    mysql_query($sql,$CONN);
		    echo json_encode(array("state"=>1,"msg"=>"done"));
		} catch (Exception $e) {
		    echo json_encode(array("state"=>0,"msg"=>$e));
		}	
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
		$objPHPExcel->getActiveSheet()->setTitle( $il8n['basic_group']['basic_group'] );
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $il8n['title'] );
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $il8n['type'] );
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $il8n['basic_group']['code'] );
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $il8n['basic_group']['icon'] );
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $il8n['basic_group']['rank'] );
		$objPHPExcel->getActiveSheet()->setCellValue('F1', $il8n['basic_group']['remark'] );
		
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
	
	public function update(){
	    $CONN = tools::conn();
		$sql = "delete from basic_group_2_permission where id_group =  ".$_REQUEST['id'];
		mysql_query($sql,$CONN);
		
		$arr = explode(',',$_REQUEST['ids']);
		$arr2 = explode(',',$_REQUEST['costs']);
		$arr3 = explode(',',$_REQUEST['credits']);

		for($i=0;$i<count($arr);$i++){
			$sql = "insert into basic_group_2_permission (  id_group  ,id_permission,cost,credits ) values ('".$_REQUEST['id']."','".$arr[$i]."','".$arr2[$i]."','".$arr3[$i]."'); ";
			//echo $sql;
			mysql_query($sql,$CONN);
		}	
		
		echo json_encode(array("state"=>1,'msg'=>'done'));
	}	
}
?>