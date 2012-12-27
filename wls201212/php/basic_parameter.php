<?php
/**
 * 系统中的 参数表 ,存储了系统的主要配置数据以及一些行业标准数据
 * 比如 行政区划编码 人事系统编码 会计分类编码 等等
 * 因为这些数据大多不是核心业务数据
 * 因此服务端没有做权限校验,意味着任何第三方系统都可以直接调用获取
 * 
 * 很多参数数据,数据量比较小,不到100行,这些数据会在用户登录系统时,
 * 以 il8n 的形式传递到前端
 * 
 * @author wei1224hf@gmail.com
 * */
class basic_parameter {
	
	public function getNationsList(){
		echo json_encode(self::getNations());
	}
	
	public function getDegreesList(){
		echo json_encode(self::getDegrees());
	}	
	
	/**
	 * 行政区划编码
	 * 参考 GB 2260 标准,
	 * 前端用异步的形式调用,服务端返回JSON数据
	 * */
	public function getGB2260() {
		$CONN = tools::conn();
		$sql = "select code ,value  from basic_parameter where reference = 'GB2260' and code like '".$_REQUEST['code']."__' ";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		//return $data;
		echo json_encode($data);
	}	
	
	public function getEDU_BKZYML() {
		$CONN = tools::conn();
		$sql = "select code ,value  from basic_parameter where reference = 'EDU-BKZYML' and code like '".$_REQUEST['code']."__' ";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		if(count($data)==0)echo $sql;
		//return $data;
		echo json_encode($data);
	}	

	public function getJBGDXXHKYJG() {
		$CONN = tools::conn();
		$sql = "select code ,value  from basic_parameter where reference = 'JB-GDXXHKYJG' and extend1 = '".$_REQUEST['code']."' order by value ";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		if(count($data)==0)echo $sql;
		//return $data;
		echo json_encode($data);
	}		
	
	public function getIcons(){
	    $files = tools::getAllFiles("../file/icon16x16/");
	    $files2 = tools::getAllFiles("../file/icon48x48/");
	    $files = array_merge($files,$files2);
	    
	    echo json_encode($files);
	}
	
	public function photoUpload() {
        include_once '../libs/ajaxUpload/php.php';
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("jpg","jpeg","gif","png","bmp");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/photo/');
        // to pass data through iframe you will need to encode all html tags
        
        echo json_encode(array('state'=>'1','path'=>$uploader->savePath));
	}
	
	public function mp3Upload() {
        include_once '../libs/ajaxUpload/php.php';
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("mp3");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/mp3/');
        // to pass data through iframe you will need to encode all html tags
        
        echo json_encode(array('state'=>'1','path'=>$uploader->savePath));
	}
}