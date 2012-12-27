<?php

class basic_permission {
    
    
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_permission__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}		
    }    
	
    /**
     * 得到JSON格式列表
     * */
    public function getGrid(){
        tools::checkPermission("1203");
        $CONN = tools::conn();
        $sql = "SELECT * FROM basic_permission order by code";
        $res = mysql_query($sql,$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){       
            $data[] = $temp;
        }
        
        echo json_encode(array("Rows"=>$data));
    }
    
    public function add(){
        tools::checkPermission("120303");
		$CONN = tools::conn();
		
		sleep(1.5);
		$json = json_decode($_REQUEST['json'],TRUE);
		$sql = "select * from basic_permission where code = '".$json['code']."' ; ";
		$res = mysql_query($sql,$CONN);
		$arr = mysql_fetch_array($res,MYSQL_ASSOC);
		if ($arr!=FALSE) {
		    echo json_encode(array("state"=>0,"msg"=>"code existed"));exit();
		}	
		if(strlen($json['code'])>2){
            $sql = "select * from basic_permission where code = '".substr($json['code'],0,strlen($json['code'])-2)."' ; ";

    		$res = mysql_query($sql,$CONN);
    		$arr = mysql_fetch_array($res,MYSQL_ASSOC);
    		if ($arr==FALSE) {
    		    echo json_encode(array("state"=>0,"msg"=>"top code unexisted"));exit();
    		}
		}

		$keys = array_keys($json);
		$keys = implode(",",$keys);
		$values = array_values($json);
		$values = implode("','",$values);		
		$sql2 = "insert into basic_permission (".$keys.") values ('".$values."')";
		
		try {
		    mysql_query($sql2,$CONN);
		    echo json_encode(array("state"=>1,"msg"=>"done"));
		} catch (Exception $e) {
		    echo json_encode(array("state"=>0,"msg"=>$e));
		}		
    }
    
    public function delete_(){
        tools::checkPermission("120302");
        $CONN = tools::conn();
        
        sleep(1.5);
		try {
		    mysql_query("delete from basic_permission where code = '".$_REQUEST['code']."' ",$CONN);
		    echo json_encode(array("state"=>1,"msg"=>"done"));
		} catch (Exception $e) {
		    echo json_encode(array("state"=>0,"msg"=>$e));
		}	        
    }
    
    public function modify() {
        tools::checkPermission("120301");
        $CONN = tools::conn();	

		$json = json_decode($_REQUEST['json'],true);
        $code = $json['code'];
		unset($json['code']);		
		
		$keys = array_keys($json);		
		$sql = "update basic_permission set ";
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
	
	public function getList(){
		$CONN = tools::conn();
		/*
		//一次性输出所有的菜单树结构
		$sql = "select name as text ,code,0 as isLeaf  from basic_permission  ;";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$len = strlen($temp['code']);
			if($len==2){
				$data[] = $temp;
			}else if($len==4){
				$data[count($data)-1]['children'][] = $temp;
			}else if($len==6){
				$data[count($data)-1]['children'][count($data[count($data)-1]['children'])-1]['children'][] = $temp;
			}
		}
		*/
		
		//前端异步导入菜单结构
		$code = '';
		if(isset($_REQUEST['code'])){
			$code = $_REQUEST['code'];
		}
		$sql = "select name , code , isleaf ,path from basic_permission where code like '".$code."__' order by rank  ;";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			if($temp['isleaf']==0){
				$temp['children'] = array();
				$temp['isexpand'] = false;
			}
			unset($temp['isleaf']);
			$data[] = $temp;
		}
		
		return $data;
		echo json_encode($data);
	}
	
 	/**
     * 接收前端用AJAX方式发送过来的文件,
     * 存储在系统的 upload 位置
     * 
     * 前端和服务端都需要引用 qqFileUploader 这个控件
     * */
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
		
		$il8n = tools::getLanguage(); 
		$CONN = tools::conn();
		$currentSheet = $obj->getSheetByName($il8n['basic_permission']['permission']);
		if($currentSheet==null){
			//如果这个sheet页不存在,就报错
			tools::error(array("state"=>0,"msg"=>$il8n['requestSheetMissing']." : ".$il8n['basic_permission']['basic_permission']),'json');
		}	
		
	    if($currentSheet->getCell("A1")->getValue()!=$il8n['title']){
            tools::error(array("state"=>0,"msg"=>'A1 wrong header format'),'json');
        }
		if($currentSheet->getCell("B1")->getValue()!=$il8n['code']){
            tools::error(array("state"=>0,"msg"=>'B1 wrong header format'),'json');
        }    
		if($currentSheet->getCell("C1")->getValue()!=$il8n['type']){
            tools::error(array("state"=>0,"msg"=>'C1 wrong header format'),'json');
        }     
		if($currentSheet->getCell("D1")->getValue()!=$il8n['icon']){
            tools::error(array("state"=>0,"msg"=>'D1 wrong header format'),'json');
        }     
		if($currentSheet->getCell("E1")->getValue()!=$il8n['basic_permission']['path']){
            tools::error(array("state"=>0,"msg"=>'E1 wrong header format'),'json');
        } 
		if($currentSheet->getCell("F1")->getValue()!=$il8n['remark']){
            tools::error(array("state"=>0,"msg"=>'F1 wrong header format'),'json');
        }                          
        
        $allRow = $currentSheet->getHighestRow();
        $config = self::loadConfig("array");
        $config = $config["type"];
        $types = array();
        for($i=0;$i<count($config);$i++){
            $types[$config[$i]['value']] = $config[$i]['code'];
        }
        for($i=2;$i<=$allRow;$i++){
            $sql = "select * from basic_permission where code = '".$currentSheet->getCell("C".$i)->getValue()."' ";
            $res = mysql_query($sql,$CONN);
            $arr = mysql_fetch_array($res,MYSQL_ASSOC);
            if($arr!=false){
                tools::error(array("state"=>0,"msg"=>'code already existed '."C".$i),'json');
            }
            
            $sql = "insert into basic_permission (name,code,type,icon,path,remark) values (
            	'".trim($currentSheet->getCell("A".$i)->getValue())."',
            	'".$currentSheet->getCell("B".$i)->getValue()."',
            	'".$types[$currentSheet->getCell("C".$i)->getValue()]."',
            	'".$currentSheet->getCell("D".$i)->getValue()."',       
            	'".$currentSheet->getCell("E".$i)->getValue()."',
            	'".$currentSheet->getCell("F".$i)->getValue()."'    	
            	) ";
            try {
                mysql_query($sql,$CONN);
            } catch (Exception $e) {
                echo $e;
            }
        }
        mysql_query("call basic_permission__setleaf();",$CONN);
        echo json_encode(array('state'=>'1','msg'=>'done'));
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
		$objPHPExcel->getActiveSheet()->setTitle( $il8n['basic_permission']['basic_permission'] );
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $il8n['title'] );
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $il8n['type'] );
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $il8n['code'] );
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $il8n['icon'] );
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $il8n['basic_permission']['path'] );
		$objPHPExcel->getActiveSheet()->setCellValue('F1', $il8n['remark'] );
		
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FFE0E0E0');
		$objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A1'), 'A1:F1');		
		
		$sql = "select * from basic_permission ;";
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
		    $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2), $data[$i]['path'] );
		    $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2), $data[$i]['remark'] );
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        
		$file = "basic_permission_".date('YmdHis').".xls";
		$path =  "../file/download/".$file;
        $objWriter->save($path);
		echo json_encode(array('state'=>'1','msg'=>'done','path'=>$path,'file'=>$file));;
	}	
	
	public function getTree4Group(){
	    tools::checkPermission("120106");
		$CONN = tools::conn();
		//echo $CONN;
		//一次性输出所有的菜单树结构
		$sql = "select t1.id,t1.code,t1.name,t1.icon,(select t2.cost from basic_group_2_permission t2 where t2.code_permission = t1.code and t2.code_group = ".$_REQUEST['code']." limit 1  ) as cost from basic_permission t1  ;";
		//echo $sql;
		//$sql = "select * from basic_group_2_permission";
		$res = mysql_query($sql,$CONN);
		//echo $res;
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			if($temp['cost']!=''){
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
	
	public function update4Group(){
	    tools::checkPermission("120106");
		$CONN = tools::conn();
		
		sleep(1.5);
		$code = $_REQUEST['code'];
		$codes = $_REQUEST['codes'];

		$sql = "delete from basic_group_2_permission where code_group = '".$code."'; ";
		mysql_query($sql,$CONN);
		
		$arr = explode(',',$codes);
		for($i=0;$i<count($arr);$i++){
			$sql = "insert into basic_group_2_permission (code_group , code_permission , cost ) values ('".$code."','".$arr[$i]."','0'); ";
			mysql_query($sql,$CONN);
		}
		
		echo json_encode(array("state"=>1,'msg'=>'done'));
	}
}
?>