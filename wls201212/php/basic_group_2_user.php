<?php
class basic_group_2_user {
	
	public function getTree(){
		//tools::checkPermission("120106"); //TODO
		$CONN = tools::conn();

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
		header("Content-type:text/json");
		echo json_encode($data);
	}
	
	public function update(){
	    if(!tools::checkPermission("120290"))return;
	    $CONN = tools::conn();
	    sleep(2.5);
		$code = $_REQUEST['code'];
		$codes = $_REQUEST['codes'];

		$sql = "call basic_group_2_user__update('".$code."','".$codes."',@state,@msg)";
		mysql_query($sql,$CONN);
		$sql = "select @state as state,@msg as msg";
		$res = mysql_query($sql,$CONN);
		$data = mysql_fetch_assoc($res);	
		
		echo json_encode($data);
	}
}