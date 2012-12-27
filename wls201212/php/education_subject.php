<?php
class education_subject {
	
	public function getGrid(){
	    tools::checkPermission("1201");
		$CONN = tools::conn();
		
		$sql = "select name, code  , type , count_papers,count_questions , remark ,status from education_subject order by code  ;";
		$res = mysql_query($sql,$CONN);
		
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		
        $data = array("Rows"=>$data);
        echo json_encode($data);
	}
	
	public function getList() {
	    
		$CONN = tools::conn();
		
		$sql = "select name, code from education_subject order by code;";
		$res = mysql_query($sql,$CONN);
		
		$il8n = tools::getLanguage();  
		$data = array(array('name'=>'&nbsp;','code'=>'00'));
		while($temp = mysql_fetch_assoc($res)){
		    for($i=0;$i<strlen($temp['code'])-2;$i++){
		        $temp['name'] = "-".$temp['name'];
		    }
			$data[] = $temp;
		}
		
        echo json_encode($data);		
	}
	
	
	public function getSynTree(){
		$CONN = tools::conn();
		/*
		//一次性输出所有的菜单树结构
		$sql = "select name as text ,code,0 as isLeaf  from education_subject  ;";
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
		$sql = "select name , code , isleaf from education_subject where code like '".$code."__' order by code, rank  ;";
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
		
		echo json_encode($data);
	}
	
	private  function getTypes(){
	    $il8n = tools::getLanguage();
	    //各种题型对应的编码
        $types = array(
            $il8n['education_subject']['top']=>1,
            $il8n['education_subject']['term']=>2,
            $il8n['education_subject']['chapter']=>3,
            $il8n['education_subject']['knowledge']=>4,
            '0'=>'0'
        );

        return array($types);
	}

    public function delete_(){
        tools::checkPermission("120204");
        $CONN = tools::conn();
        
        sleep(1.5);
		try {
		    mysql_query("delete from education_subject where code like '".$_REQUEST['code']."%' ",$CONN);
		    mysql_query("delete from education_subject_2_group where code_group like '".$_REQUEST['code']."%' ",$CONN);
		    echo json_encode(array("state"=>1,"msg"=>"done"));
		} catch (Exception $e) {
		    echo json_encode(array("state"=>0,"msg"=>$e));
		}	        
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
       
        $uploader->savePath;
        self::importExcel($uploader->savePath);
	}
	
	public function importExcel($path=NULL){
	    if($path==NULL && isset($_REQUEST['path']))$path = $_REQUEST['path'];
	    include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load($path);
		
		$il8n = tools::getLanguage(); 
		$CONN = tools::conn();
		$currentSheet = $obj->getSheetByName($il8n['education_subject']['education_subject']);
		if($currentSheet==null){
			//如果这个sheet页不存在,就报错
			tools::error(array("state"=>0,"msg"=>$il8n['requestSheetMissing']." : ".$il8n['education_subject']['education_subject']),'json');
		}	
		
	    if($currentSheet->getCell("A1")->getValue()!=$il8n['title']){
            tools::error(array("state"=>0,"msg"=>'A1 wrong header format'),'json');
        }
		if($currentSheet->getCell("B1")->getValue()!=$il8n['type']){
            tools::error(array("state"=>0,"msg"=>'B1 wrong header format'),'json');
        }    
		if($currentSheet->getCell("C1")->getValue()!=$il8n['code']){
            tools::error(array("state"=>0,"msg"=>'C1 wrong header format'),'json');
        }        
		if($currentSheet->getCell("D1")->getValue()!=$il8n['remark']){
            tools::error(array("state"=>0,"msg"=>'E1 wrong header format'),'json');
        }                          
        
        $allRow = $currentSheet->getHighestRow();

      
        $config = self::loadConfig("array");
        $types = array();
        for($i=0;$i<count($config['type']);$i++){
            $types[ $config['type'][$i]['value'] ] = $config['type'][$i]['code'] ;
        }        
        for($i=2;$i<=$allRow;$i++){
            $sql = "select * from education_subject where code = '".$currentSheet->getCell("C".$i)->getValue()."' ";
            $res = mysql_query($sql,$CONN);
            $arr = mysql_fetch_array($res,MYSQL_ASSOC);
            if($arr!=false){
                tools::error(array("state"=>0,"msg"=>'code already existed '."C".$i),'json');
            }
            
            $sql = "insert into education_subject (name,type,code,remark) values (
            	'".$currentSheet->getCell("A".$i)->getValue()."',
            	'".$types[$currentSheet->getCell("B".$i)->getValue()]."',
            	'".$currentSheet->getCell("C".$i)->getValue()."',     
            	'".$currentSheet->getCell("D".$i)->getValue()."'    	
            	) ";
            echo $sql;
                mysql_query($sql,$CONN);

        }
        mysql_query("call education_subject__setleaf();",$CONN);
        echo json_encode(array('state'=>'1','msg'=>'done'));
	}
	
	public function add(){
        tools::checkPermission("120105");
		$CONN = tools::conn();
		
		sleep(2.5);
		$json = json_decode($_REQUEST['json'],TRUE);
		if($json['code']=='00'){//顶级编号
		    $sql = "select max(code) as a from education_subject where code like '__'";
		    $res = mysql_query($sql,$CONN);
		    $arr = mysql_fetch_array($res,MYSQL_ASSOC);
		    $a = intval($arr['a']);
		    $a ++;
		    if($a<10)$a="0".$a;
		    $json['code'] = $a;
		}else{
		    $sql = "select max(code) as code from education_subject where code like '".$json['code']."__' ";
		    $res = mysql_query($sql,$CONN);
		    $arr = mysql_fetch_array($res,MYSQL_ASSOC);

		    if($arr['code']==false || $arr['code']==null || $arr==false){
		        $json['code'] = $json['code'].'01';
		    }else{
    		    $a = $arr['code'];
    		    $a1 = substr($a, 0,strlen($a)-2);
    		    $a2 = substr($a, strlen($a)-2,strlen($a));
    		    $a2 = intval($a2);
    		    $a2 ++;
    		    if($a2<10)$a2="0".$a2;
    		    $json['code'] = $a1.$a2;
		    }
		}		

		$keys = array_keys($json);
		$keys = implode(",",$keys);
		$values = array_values($json);
		$values = implode("','",$values);		
		$sql2 = "insert into education_subject (".$keys.") values ('".$values."')";
		
		try {
		    mysql_query($sql2,$CONN);
		    echo json_encode(array("state"=>1,"msg"=>"done","sql"=>$sql,"sql2"=>$sql2,"arr"=>$arr));
		} catch (Exception $e) {
		    echo json_encode(array("state"=>0,"msg"=>$e));
		}		
	}
	
	public function modify(){
        tools::checkPermission("120104");
        $CONN = tools::conn();	

		$json = json_decode($_REQUEST['json'],true);
        $code = $json['code'];
		unset($json['code']);		
		
		$keys = array_keys($json);		
		$sql = "update education_subject set ";
		for($i=0;$i<count($json);$i++){
			$sql.= $keys[$i]."='".$json[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where code = '".$code."' ;";	
        sleep(2.5);
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
		$objPHPExcel->getActiveSheet()->setTitle( $il8n['education_subject']['education_subject'] );
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $il8n['title'] );
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $il8n['type'] );
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $il8n['education_subject']['code'] );
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $il8n['icon'] );
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $il8n['education_subject']['rank'] );
		$objPHPExcel->getActiveSheet()->setCellValue('F1', $il8n['remark'] );
		
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FFE0E0E0');
		$objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A1'), 'A1:F1');		
		
		$sql = "select * from education_subject ;";
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
        
		$file = "education_subject_".date('YmdHis').".xls";
		$path =  "../file/download/".$file;
        $objWriter->save($path);
		echo json_encode(array('state'=>'1','msg'=>'done','path'=>$path,'file'=>$file));;
	}
	
	public function getOne(){
		$id = $_REQUEST['id'];
		$CONN = tools::conn();
		
		$sql = "select name,code,type,rank,status,icon,remark,id from education_subject where id = ".$id;
		$res = mysql_query($sql,$CONN);
		$data = mysql_fetch_assoc($res);
		
		sleep(1.5);
		echo json_encode($data);
	}
	
	public function getTree4group() {
	    //tools::checkPermission("120106"); //TODO
		$CONN = tools::conn();
		//echo $CONN;
		//一次性输出所有的菜单树结构
		$sql = "SELECT
            basic_group.name,
            basic_group.code,
            basic_group.icon,
            (select education_subject_2_group.id  from 
            	education_subject_2_group where 
            		education_subject_2_group.code_group = basic_group.code 
            		and education_subject_2_group.code_subject = '".$_REQUEST['code']."') as cost
            FROM
            basic_group order by code";
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
	
	public function update4group() {
	    $CONN = tools::conn();
	    sleep(2.5);
		$code = $_REQUEST['code'];
		$codes = $_REQUEST['codes'];

		$sql = "delete from education_subject_2_group where code_subject = '".$code."'; ";
		mysql_query($sql,$CONN);
		
		$arr = explode(',',$codes);
		for($i=0;$i<count($arr);$i++){
			$sql = "insert into education_subject_2_group ( code_subject , code_group  ) values ('".$code."','".$arr[$i]."'); ";
			mysql_query($sql,$CONN);
		}
		
		echo json_encode(array("state"=>1,'msg'=>'done'));
	}
	
     public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
		
		$sql = "select code,value from basic_parameter where reference = 'education_subject__type' order by code";
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
}
?>