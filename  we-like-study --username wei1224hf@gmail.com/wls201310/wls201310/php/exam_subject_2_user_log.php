<?php
class exam_subject_2_user_log {
    
    public static function loadConfig() {
        $conn = tools::getConn();
        $config = array();
        
        $sql = "select name as value,code from exam_subject order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
		    $len = strlen($temp['code']);
		    for($i=1;$i<$len/2;$i++){
		        $temp['value'] = "--".$temp['value'];
		    }
			$data[] = $temp;
		}
		$config['subject'] = $data;

	    return $config;		
	}    

    public static function statistics_time($time_start=NULL,$time_stop=NULL,$gap=NULL,$subject=NULL,$user_code=NULL){
	    if (!basic_user::checkPermission("44")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	
        if($time_start==NULL)$time_start = $_REQUEST['time_start'];
        if($time_stop==NULL)$time_stop = $_REQUEST['time_stop'];
        if($gap==NULL)$gap = $_REQUEST['gap'];
        if($subject==NULL)$subject = $_REQUEST['subject'];
        if($user_code==NULL)$user_code = $_REQUEST['executor'];

        $conn = tools::getConn();
        $sql = tools::getConfigItem("exam_subject_2_user_log__statistics_time");
        $sql = str_replace("__bigger__", ">", $sql);
        $sql = str_replace("__smaller__", "<", $sql);
        $sql = str_replace("__creater_code__", "'".$user_code."'", $sql);
        $sql = str_replace("__subject_code__", "'".$subject."'", $sql);
        $sql = str_replace("__time_start__", "'".$time_start."'", $sql);
        $sql = str_replace("__time_stop__", "'".$time_stop."'", $sql);
        
        if($gap=='day'){
            $sql = str_replace("__gap__", "10", $sql);
        }
        if($gap=='month'){
            $sql = str_replace("__gap__", "7", $sql);
        }
        
        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        return array(
             'status'=>'1'
            ,'data'=>$data
            ,'sql'=>$sql
        );
    }
    
    public static function statistics_subject($time=NULL,$subject=NULL,$user_code=NULL,$gap=NULL){
        if($time==NULL)$time = $_REQUEST['time'];
        if($subject==NULL)$subject = $_REQUEST['subject'];
        if($user_code==NULL)$user_code = $_REQUEST['executor'];
        if($gap==NULL)$gap = $_REQUEST['gap'];
        $conn = tools::getConn();
        
        $sql = tools::getConfigItem("exam_subject_2_user_log__statistics_subject");
        $sql = str_replace("__subject_code__", "'".$subject."__'", $sql);
        $sql = str_replace("__date__", "'".$time."'", $sql);
        $sql = str_replace("__creater_code__", "'".$user_code."'", $sql); 

        if($gap=='day'){
            $sql = str_replace("__gap__", "10", $sql);
        }
        if($gap=='month'){
            $sql = str_replace("__gap__", "7", $sql);
        }        
        
        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        return array(
             'status'=>'1'
            ,'data'=>$data
            ,'sql'=>$sql
        );
    }    
}