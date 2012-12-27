<?php
class basic_person {
	
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_person__cardType' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['cardType'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'GB2261_1' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB2261_1'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'GB2261_2' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB2261_2'] = $data;	

		$sql = "select code,value from basic_parameter where reference = 'GB3304' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB3304'] = $data;	

		$sql = "select code,value from basic_parameter where reference = 'GB4762' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB4762'] = $data;	

		$sql = "select code,value from basic_parameter where reference = 'GB4568' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB4568'] = $data;			

		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}
    }	    
	
    public function photoUpload(){
        include_once '../libs/ajaxUpload/php.php';
	    // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("jpg","JPG","gif","GIF","png","PNG");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
       
        echo json_encode(array("path"=>$uploader->savePath,"state"=>1));
    }
    
    public function insert($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $il8n = tools::getLanguage(); 
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $keys = array_keys($data);
        $keys = implode(",",$keys);
        $values = array_values($data);
        $values = implode("','",$values);    
        $sql = "insert into basic_person (".$keys.") values ('".$values."')";
        mysql_query($sql,$CONN);  
        $id_person = mysql_insert_id($CONN);
        
        echo json_encode(array('sql'=>$sql,'id'=>$id_person,'state'=>1));
    }
    
    public function update($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $id = $data['id'];
        unset($data['id']);
        $il8n = tools::getLanguage(); 
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $keys = array_keys($data);        
        $sql = "update basic_person set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }
    
    public function view(){
        $CONN = tools::conn();    
        $id = $_REQUEST['id'];
        $res = mysql_query( "select * from basic_person where id = '".$id."' " , $CONN );

        $data= mysql_fetch_assoc($res);
        $data['birthday'] = substr( $data['birthday'], 0, 10);
        
        echo json_encode($data);
    }
	
}