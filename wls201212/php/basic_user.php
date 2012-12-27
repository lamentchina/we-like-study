<?php
/**
 * 系统用户相关操作的服务端
 * 
 * @version 201210
 * @author wei1224hf@gmail.com
 * @prerequisites basic_memory__init,basic_memory.il8n()
 * */
class basic_user {
    
    /**
     * 虽然提倡将业务迁移到数据库的存储过程
     * 但是,像这种表格查询功能,还是用服务端拼凑SQL比较好
     * */
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
		$orderby = " ORDER BY basic_user.username ASC ";
        //有查询条件
		if(isset($_REQUEST['search'])){
			$search = json_decode($_REQUEST['search'],true);
			$where = " where 1=1 ";
			if(isset($search['username']) && trim($search['username'])!=''){
				$where .= " and basic_user.username like = '%".$search['username']."%' ";
			}
			if(isset($search['money']) && trim($search['money'])!=''){
			    
				$where .= " and basic_user.money < '".$search['money']."' ";
			}
			if(isset($search['type']) && trim($search['type'])!=''){
				$where .= " and basic_user.type = '".$search['type']."' ";
			}	
			if(isset($search['status']) && trim($search['status'])!=''){
				$where .= " and basic_user.status = '".$search['status']."' ";
			}			
			if(isset($search['groups']) && trim($search['groups'])!=''){
				$where .= " and basic_user.groups like '%".$search['groups']."%' ";
			}			
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$orderby = " order by basic_user.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}        
        
        $sql = "select basic_user.*,basic_person.name from basic_user  left join basic_person on basic_user.id_person = basic_person.id
		".$where."
		".$orderby."        
                 limit ".($page-1)*$pagesize.",".$pagesize." ; ";
        $res = mysql_query($sql,$CONN);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        $sql = "select count(*) as total from basic_user ".$where;
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
            //echo "call basic_user_delete(".$arr[$i].")";
            mysql_query("call basic_user_delete(".$arr[$i].")",$CONN);
        }
        sleep(2.5);
        echo json_encode(array("msg"=>'done',"state"=>'1'));
    }
    
    private function getPermission($username=NULL){
        $CONN = tools::conn();
        
        $sql = "
            SELECT
            basic_permission.code,
            Min(basic_group_2_permission.cost) as cost,
            Max(basic_group_2_permission.credits) as credits,
            basic_permission.name,
            basic_permission.path,
			basic_permission.type,
			basic_permission.icon
            FROM
                    basic_permission
                    Right Join basic_group_2_permission ON basic_permission.id = basic_group_2_permission.id_permission
                    Right Join basic_group_2_user ON basic_group_2_permission.id_group = basic_group_2_user.id_group
                    Right Join basic_user ON basic_group_2_user.id_user = basic_user.id
            WHERE
                    basic_user.username =  '".$_REQUEST['username']."'
            GROUP BY
            basic_permission.code
            ORDER BY
            basic_permission.code ASC
        
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
    
    public function login(){
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];     

        //如果内存表是空的,说明系统刚启动,需要初始化
        $sql = "select count(*) as c from basic_memory ";
        $res = mysql_query($sql,$CONN);
        $data = mysql_fetch_assoc($res);
        if($data['c']==0){
            mysql_query("call basic_memory__init()",$CONN);
            include_once 'basic_memory.php';
            $m = new basic_memory();
            $m->il8n();
        }
        
        $sql ="call basic_user__login('".$username."','".$password."','".$_SERVER['REMOTE_ADDR']."',@msg,@state)";
        mysql_query($sql,$CONN);
        $res = mysql_query("select @state as state,@msg as msg",$CONN);
        $arr = mysql_fetch_array($res,MYSQL_ASSOC);
        if($arr['state']==1){
            $sql = "
                SELECT
                basic_user.money,
                basic_user.money2,
                basic_user.person_id,
                basic_user.group_id,
                basic_user.group_code,
                basic_user.group_name,
                basic_user.id,
                basic_user.lastlogintime,
                basic_user.time_created,
                basic_user.lastlogouttime,
                basic_person.cellphone,
                basic_person.email,
                basic_person.photo,
                basic_person.name,
                basic_person.birthday,
                basic_user.status,
                (select value from basic_parameter where code = basic_user.status and reference = 'basic_user__status') as statusname,
                basic_user.type,
                basic_user_session.groups,
                basic_user_session.ip,
                basic_user_session.source,
                basic_user_session.permissions
                FROM
                basic_user
                Left Join basic_person ON basic_user.id_person = basic_person.id
                Left Join basic_user_session ON basic_user.id = basic_user_session.id_user
                WHERE
                basic_user.username =  '".$_REQUEST['username']."'            
            ";
            $res2 = mysql_query($sql,$CONN);
            $arr2 = mysql_fetch_array($res2,MYSQL_ASSOC);

            $arr['loginData'] = $arr2;
            $arr['permission'] = $this->getPermission($_REQUEST['username']);
            $arr['il8n'] = tools::getLanguage();
        }

        echo json_encode($arr);
    }
    
    public function logout(){
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $session = $_REQUEST['session'];        
        mysql_query("call basic_user__logout('".$username."','".$session."',@msg,@state)",$CONN);
        $res = mysql_query("select @state as state,@msg as msg" ,$CONN);
        $arr = mysql_fetch_array($res,MYSQL_ASSOC);
        echo json_encode($arr);
        
    }
    
    public function changePWD(){
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        
        mysql_query("update basic_user set password = '".$password."' where username = '".$username."' ",$CONN);
    }
	
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_user__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'basic_user__status' order by code";
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
     * 接收前端用AJAX方式发送过来的文件,
     * 存储在系统的 upload 位置
     * 
     * 前端和服务端都需要引用 qqFileUploader 这个控件
     * */
    public function import(){
        tools::checkPermission("120101");
        include_once '../libs/ajaxUpload/php.php';
        $allowedExtensions = array("xls");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
       
		basic_excel::import($uploader->savePath);
		
		$CONN = tools::conn(); 
		mysql_query("call basic_user__import('".basic_excel::guid."',@state,@msg,@ids)",$CONN);
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
        $sql = "select * from basic_user where username = '".$data['username']."';";
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
            $sql2 = "insert into basic_user (".$keys.") values ('".$values."')";
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
        $res = mysql_query( "select * from basic_user where id = '".$id."' " , $CONN );

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
        $sql = "update basic_user set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }    
}