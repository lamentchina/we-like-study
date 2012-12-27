<?php
/**
 * @version 201210
 * @author wei1224hf@gmail.com
 * */
class basic_workflow {
    
    public function getGrid(){
        $CONN = tools::conn();

        $page = 1;
        if(isset($_REQUEST['page'])){
            $page = $_REQUEST['page'];
        }
        $pagesize = 20;
        if(isset($_REQUEST['pagesize'])){
            $pagesize = $_REQUEST['pagesize'];
        }   

    	$where = " where 1=1 ";
		$orderby = " ORDER BY basic_workflow.username ASC ";
        //有查询条件
		if(isset($_REQUEST['search'])){
			$search = json_decode($_REQUEST['search'],true);
			$where = " where 1=1 ";
			if(isset($search['username']) && trim($search['username'])!=''){
				$where .= " and basic_workflow.username like = '%".$search['username']."%' ";
			}
			if(isset($search['money']) && trim($search['money'])!=''){
			    
				$where .= " and basic_workflow.money < '".$search['money']."' ";
			}
			if(isset($search['type']) && trim($search['type'])!=''){
				$where .= " and basic_workflow.type = '".$search['type']."' ";
			}	
			if(isset($search['status']) && trim($search['status'])!=''){
				$where .= " and basic_workflow.status = '".$search['status']."' ";
			}			
			if(isset($search['groups']) && trim($search['groups'])!=''){
				$where .= " and basic_workflow.groups like '%".$search['groups']."%' ";
			}			
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$orderby = " order by basic_workflow.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}        
        
        $sql = "select * from basic_workflow  
		".$where."
		".$orderby."        
                 limit ".($page-1)*$pagesize.",".$pagesize." ; ";
        $res = mysql_query($sql,$CONN);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        $sql = "select count(*) as total from basic_workflow ".$where;
        $res = mysql_query($sql,$CONN);
        $total = 0;
        while($temp = mysql_fetch_assoc($res)){
            $total = $temp['total'];
        }
        sleep(0.9);
        echo json_encode(  array("Rows"=>$data,"Total"=>$total) );
    }
    
    public function delete(){//TODO
        $CONN = tools::conn();
        if(!(isset($_REQUEST['ids']))){
            die("no ids");
        }
        $ids = $_REQUEST['ids'];
        $arr = explode(",",$ids);
        //print_r($arr);exit();
        
        for($i=0;$i<count($arr);$i++){
            //echo "call basic_workflow_delete(".$arr[$i].")";
            mysql_query("call basic_workflow_delete(".$arr[$i].")",$CONN);
        }
        sleep(2.5);
        echo json_encode(array("msg"=>'done',"state"=>'1'));
    }
    
    private function getPermission($username=NULL){
        $CONN = tools::conn();
        
        $sql = "
        select t2.* from 
        (
        select code_permission,min(cost) from basic_group_2_permission where code_group in
        (
        select code_group from basic_group_2_user where username = '".$username."'
            
        ) group by code_permission
        ) t left join basic_permission t2 on t2.code = t.code_permission
        ";
        //echo $sql;
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
        return $data;
    }
    
   
    
    
	
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_workflow__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'basic_workflow__status' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;
		
		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}
	}  

    
   
    /**
     * 添加一个用户,往数据库中插入一条记录
     * */
    public function insert($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $il8n = tools::getLanguage(); 
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);
        
        //检查用户名是否已存在
        $sql = "select * from basic_workflow where username = '".$data['username']."';";
        $res = mysql_query($sql,$CONN);
        $temp = mysql_fetch_assoc($res);
        if($temp===false){
            //1 插入一条个人信息
            $sql = "insert into basic_person (cellphone,email) values ('".$data['cellphone']."','".$data['email']."');";
            mysql_query($sql,$CONN);        
            $data['id_person'] = mysql_insert_id($CONN);
            $data['password'] = md5($data['password']);
            
            //2 插入一条用户信息
            $keys = array_keys($data);
            $keys = implode(",",$keys);
            $values = array_values($data);
            $values = implode("','",$values);    
            $sql2 = "insert into basic_workflow (".$keys.") values ('".$values."')";
            mysql_query($sql2,$CONN);  
            $id_user = mysql_insert_id($CONN);
            
            //3 根据用户类型,再插 扩展用户类型
            $id_extend = 0;
            if($data['type']=='2'){
                //学生
                $sql3 = "insert into education_student (id_user,id_person,code) values ('".$id_user."','".$data['id_person']."','000000') ";
                mysql_query($sql3);
                $id_extend = mysql_insert_id($CONN);                
            }else if($data['type']=='3'){
                //教师
                $sql3 = "insert into education_teacher (id_user,id_person,code) values ('".$id_user."','".$data['id_person']."','000000') ";
                mysql_query($sql3);
                $id_extend = mysql_insert_id($CONN);                
            }
            
            $return = array(
                'state'=>'1',
                'msg'=>'done',
                'data'=>array(
                    'id_user'=>$id_user,
                    'id_person'=>$data['id_person'],
                    'id_extend'=>$id_extend
                )
            );
            if($retur=='json'){
                echo json_encode($return);
            }else if($retur=='array'){
                return $return;
            }
        }else{
            $return = array('state'=>'0','msg'=>'username already existed');
            if($retur=='json'){
                echo json_encode($return);
            }else if($retur=='array'){
                return $return;
            }
        }
    }
    
    public function view(){
        $CONN = tools::conn();    
        $id = $_REQUEST['id'];
        $res = mysql_query( "select * from basic_workflow where id = '".$id."' " , $CONN );

        $data= mysql_fetch_assoc($res);
        
        echo json_encode($data);
    }

    
    public function update($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $id = $data['id'];
        unset($data['id']);
        if(isset($data['password']))$data['password'] = md5($data['password']);
        $data['time_lastupdated'] = date('YYYY-MM-dd');
        $il8n = tools::getLanguage(); 
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $keys = array_keys($data);        
        $sql = "update basic_workflow set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }    
    
    public function check(){
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $session = $_REQUEST['session'];          
        $sql ="call basic_workflow__check('".$username."','".$session."',@msg,@state,@session,@app)";
        //echo $sql;
        mysql_query($sql,$CONN);
        $res = mysql_query("select @session as session,@state as state,@msg as msg,@app as app",$CONN);
        $arr = mysql_fetch_array($res,MYSQL_ASSOC);
        echo json_encode($arr);
    }
}