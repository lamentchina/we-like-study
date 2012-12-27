<?php
/**
 * 系统用户组相关的服务端操作
 * 
 * @version 201210
 * @author wei1224hf@gmail.com
 * @prerequisites basic_memory__init,basic_memory.il8n()
 * */
class basic_group {
	
	public function getGrid(){
	    tools::checkPermission("1201");
		$CONN = tools::conn();
		
		$sql = "select name, code , type , id , count_users , remark ,status from basic_group order by code;";
		$res = mysql_query($sql,$CONN);
		
		$data = array();
		
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		
        $data = array("Rows"=>$data);
        echo json_encode($data);
	}
	
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_group__type' order by code";
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

    public function delete(){
        tools::checkPermission("120204"); 

        $CONN = tools::conn();
        if(!(isset($_REQUEST['ids']))){
            die("no ids");
        }
        $ids = $_REQUEST['ids'];
        $arr = explode(",",$ids);
        //print_r($arr);exit();
        
        for($i=0;$i<count($arr);$i++){
            mysql_query("delete from basic_group where code like '".$arr[$i]."%' ",$CONN);
            mysql_query("delete from basic_department where code like '".$arr[$i]."%' ",$CONN);
		    mysql_query("delete from basic_group_2_permission where code_group = '".$arr[$i]."' ",$CONN);
        }
        sleep(2.5);
        echo json_encode(array("msg"=>'done',"state"=>'1'));		
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
	    include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load($path);
		
		$il8n = tools::getLanguage(); 
		$CONN = tools::conn();
		$currentSheet = $obj->getSheetByName($il8n['basic_group']['basic_group']);
		if($currentSheet==null){
			//如果这个sheet页不存在,就报错
			tools::error(array("state"=>0,"msg"=>$il8n['requestSheetMissing']." : ".$il8n['basic_group']['basic_group']),'json');
		}	
		
	    if($currentSheet->getCell("A1")->getValue()!=$il8n['title']){
            tools::error(array("state"=>0,"msg"=>'A1 wrong header format'),'json');
        }
		if($currentSheet->getCell("B1")->getValue()!=$il8n['type']){
            tools::error(array("state"=>0,"msg"=>'B1 wrong header format'),'json');
        }    
		if($currentSheet->getCell("C1")->getValue()!=$il8n['basic_group']['code']){
            tools::error(array("state"=>0,"msg"=>'C1 wrong header format'),'json');
        }     
		if($currentSheet->getCell("D1")->getValue()!=$il8n['basic_group']['icon']){
            tools::error(array("state"=>0,"msg"=>'D1 wrong header format'),'json');
        }     
		if($currentSheet->getCell("E1")->getValue()!=$il8n['basic_group']['remark']){
            tools::error(array("state"=>0,"msg"=>'E1 wrong header format'),'json');
        }                          
        
        $allRow = $currentSheet->getHighestRow();
	    
        $config = self::loadConfig("array");
        $config = $config["type"];
        $types = array();
        for($i=0;$i<count($config);$i++){
            $types[$config[$i]['value']] = $config[$i]['code'];
        }        
        for($i=2;$i<=$allRow;$i++){
            $sql = "select * from basic_group where code = '".$currentSheet->getCell("C".$i)->getValue()."' ";
            $res = mysql_query($sql,$CONN);
            $arr = mysql_fetch_array($res,MYSQL_ASSOC);
            if($arr!=false){
                tools::error(array("state"=>0,"msg"=>'code already existed '."C".$i),'json');
            }
            
            $sql = "insert into basic_group (name,type,code,icon,remark) values (
            	'".$currentSheet->getCell("A".$i)->getValue()."',
            	'".$types[$currentSheet->getCell("B".$i)->getValue()]."',
            	'".$currentSheet->getCell("C".$i)->getValue()."',
            	'".$currentSheet->getCell("D".$i)->getValue()."',       
            	'".$currentSheet->getCell("E".$i)->getValue()."'	
            	) ";

            mysql_query($sql,$CONN);
            
            if($types[$currentSheet->getCell("B".$i)->getValue()]=='2'){
                $sql = "insert into basic_department (code , name , remark , type ) values ( '".$currentSheet->getCell("C".$i)->getValue()."'
                ,'".$currentSheet->getCell("A".$i)->getValue()."'
                ,'Excel Import' , '1' ) ";
                 mysql_query($sql,$CONN);
            }

        }
        mysql_query("call basic_group_setleaf();",$CONN);
        echo json_encode(array('state'=>'1','msg'=>'done'));
	}
	
	public function insert(){
        tools::checkPermission("120105");
		$CONN = tools::conn();
		
		sleep(1.5);
		$json = json_decode($_REQUEST['json'],TRUE);
		
		$sql = "select * from basic_group where code = '".$json['code']."'; ";
		$res = mysql_query($sql,$CONN);
		if( !mysql_fetch_assoc($res) ){    
    		$keys = array_keys($json);
    		$keys = implode(",",$keys);
    		$values = array_values($json);
    		$values = implode("','",$values);		
    		$sql2 = "insert into basic_group (".$keys.") values ('".$values."')";		
		    mysql_query($sql2,$CONN);
		    echo json_encode(array("state"=>1,"msg"=>"done","sql"=>$sql2));
		}else{
		     echo json_encode(array("state"=>0,"msg"=>"code already in"));
		}
	}
	
    public function view(){
        $CONN = tools::conn();    
        $code = $_REQUEST['code'];
        $res = mysql_query( "select * from basic_group where code = '".$code."' " , $CONN );

        $data= mysql_fetch_assoc($res);
        
        echo json_encode($data);
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
		    $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2), $data[$i]['remark'] );
		}
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        
		$file = "basic_group_".date('YmdHis').".xls";
		$path =  "../file/download/".$file;
        $objWriter->save($path);
		echo json_encode(array('state'=>'1','msg'=>'done','path'=>$path,'file'=>$file));;
	}
	
    public function update($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $code = $data['code'];
        unset($data['code']);
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $keys = array_keys($data);        
        $sql = "update basic_group set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where code =".$code;    
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }  	
}