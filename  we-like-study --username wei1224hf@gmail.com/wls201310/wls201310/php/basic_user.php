<?php
class basic_user {
	
	public static function callFunction(){
		$function = $_REQUEST['function'];
		$executor = $_REQUEST['executor'];
		$session = $_REQUEST['session'];
		
		$t_return = array(
				"status"=>"2"
				,"msg"=>"access denied"
				,"executor"=>$executor
				,"session"=>$session
		);
		
		if($function == "grid"){
			$action = "120201";
			if(basic_user::checkPermission($executor, $action, session)){
				$sortname = "id";
				$sortorder = "asc";
				if(isset($_REQUEST['sortname'])){
					$sortname = $_REQUEST['sortname'];
				}
				if(isset($_REQUEST['sortorder'])){
					$sortname = $_REQUEST['sortorder'];
				}

				$t_return = basic_user::grid(
					 $_REQUEST['search']
					,$_REQUEST['pagesize']
					,$_REQUEST['page']
					,$executor
					,$sortname
					,$sortorder
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="add"){
			$action = "120221";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = add(
					 $_REQUEST['data']
					,$executor
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="modify"){
			$action = "120221";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = modify(
					 $_REQUEST['data']
					,$executor
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="modify_myself"){
			$action = "1123";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = modify_myself(
					$_REQUEST['data']
					,$executor
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="remove"){
			$action = "1123";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = remove(
					$_REQUEST['usernames']
					,$executor
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="view"){
			$action = "120202";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = remove(
						$_REQUEST['id']
						,$executor
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="login"){
			$gis_lat = 0;
			$gis_lot = 0;
			if(isset($_REQUEST['gis_lat']))$gis_lat = $_REQUEST['gis_lat'];
			if(isset($_REQUEST['gis_lat']))$gis_lot = $_REQUEST['gis_lot'];
			$t_return = basic_user::login(
				 $_REQUEST['username']
				,$_REQUEST['password']
				,$_SERVER["REMOTE_ADDR"]
				,$_SERVER['HTTP_USER_AGENT']
				,$gis_lat
				,$gis_lot
			);	
		}
		if($function =="logout"){
			$t_return = basic_user::logout(
				$_REQUEST['username']
				,$_REQUEST['session']
			);
		}
		if($function =="loadConfig"){
			$t_return = basic_user::loadConfig();
		}
		if($function =="updateSession"){
			$t_return = updateSession(
				 $executor
				,$session
			);
		}
		if($function =="group_get"){
			$action = "120241";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = basic_user::group_get(
					 $_REQUEST['username']
					,$executor
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		if($function =="group_set"){
			$action = "120241";
			if(basic_user::checkPermission($executor, $action, session)){
				$t_return = basic_user::group_set(
					 $_REQUEST['username']
					,$_REQUEST['group_codes']
				);
			}else{
				$t_return['action'] = $action;
			}
		}
		return $t_return;
	}
    
    public static $userType = NULL;
    
    public static $userGroup = NULL;
    
    public static $permissions = NULL;
        
	/**
     * 系统大多数的业务逻辑,都转移到数据库用存储过程来实现
     * 但是,列表功能,将使用服务端代码实现,因为列表功能,一般而言就是查询访问功能
     * 是不会对系统的数据做 增删改 这种 写 的操作的,都是 读取 的操作,无需转移到存储过程
     * 
     * return 默认是JSON,是作为 WEB前端,手机终端,接口通信 的主要模式,也有可能是XML,如果是 array 的话,就返回一个数组
     * 输出的数据,其格式为: {Rows:[{key1:'value1',key2:'value2']},Total:12,page:1,pagesize:3,status:1,msg:'处理结果'}
     * search 默认是NULL,将依赖 $_REQUEST['serach'] 来获取,获取到的应该是一个JSON,内有各种查询参数
     */
    public static function grid($search=NULL,$page=NULL,$pagesize=NULL){
        if (!basic_user::checkPermission("120201")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	        
        if( (!isset($_REQUEST['search'])) || (!isset($_REQUEST['page'])) || (!isset($_REQUEST['pagesize'])) ){
            return array(
    		    'status'=>'1'
    		    ,'msg'=>'ok'
    		);
        }
        $search=$_REQUEST['search'];
        $page=$_REQUEST['page'];
        $pagesize=$_REQUEST['pagesize'];
        
        //数据库连接口,在一次服务端访问中,数据库必定只连接一次,而且不会断开
        $conn = tools::getConn();
        
        //列表查询下,查询条件必定是SQL拼凑的
        $sql_where = " where 1=1 ";
        
        $search=json_decode2($search,true);
        $search_keys = array_keys($search);
        for($i=0;$i<count($search);$i++){
            if($search_keys[$i]=='username' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.username like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='group_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.group_code = '".$search[$search_keys[$i]]."' ";
            }	
            if($search_keys[$i]=='status' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.status = '".$search[$search_keys[$i]]."' ";
            }  
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.type = '".$search[$search_keys[$i]]."' ";
            }                      	
        }
        $sql_order = ' order by basic_user.id desc ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by basic_user.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
    
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //管理员角色          
            $sql = tools::getConfigItem("basic_user__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM basic_user ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }   
        
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total']
        );

        return $returnData;
    }
        
	public static function remove($usernames=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120223")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	 	    
	    if($usernames==NULL)$usernames = $_REQUEST['usernames'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
		$conn = tools::getConn();
		$usernames = explode(",", $usernames);
		for($i=0;$i<count($usernames);$i++){
		    $sql = "delete from basic_user where username = '".$usernames[$i]."' ;";
		    mysql_query($sql,$conn);
		    $sql = "delete from basic_group_2_user where user_code = '".$usernames[$i]."' ;";
		    mysql_query($sql,$conn);
		}
		
		return  array(
			'status'=>1
		    ,'msg'=>'OK'
		);
	}	
    
	public static function getPermission($username){
		$s_return = "";
		$sql = tools::getConfigItem("basic_user__getPermission");
		$sql = str_replace( "__username__", "'".$username."'",$sql);
		
		$conn = tools::getConn();
		$res = mysql_query($sql,$conn);
		
        while($temp = mysql_fetch_assoc($res)){
            $s_return .= $temp['code'].",";
        }		
        $s_return = substr($s_return,0,strlen($s_return)-1);
		
		return $s_return;
	}    
    
    public static function getPermissionTree($username){
		$a_return = array();
		
		$sql = tools::getConfigItem("basic_user__getPermission");
		$sql = str_replace( "__username__", "'".$username."'",$sql);
		
		$conn = tools::getConn();
		$res = mysql_query($sql,$conn);

        while($temp = mysql_fetch_assoc($res)){
            $a_return[] = $temp;
        }
		$a_return = tools::list2Tree($a_return);	

		return $a_return;
    }
    
	public static function checkUsernameUsed($username){
		$sql = "select id from basic_user where username = '".$username."' ";
		$res = mysql_query($sql,tools::getConn());
	    if($temp = mysql_fetch_assoc($res)){
            return true;
        }
		return false;
	}
    
	public static function getSession($username){
        $conn = tools::getConn();
		$sql = tools::getConfigItem("basic_user__getSession");
		$sql = str_replace("__user_code__", "'".$_REQUEST['executor']."'",$sql);
		$sql = str_replace("__session__", "'".$_REQUEST['session']."'",$sql);
		$sql = str_replace("\n", " ",$sql);

		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp){		
		    
    		basic_user::$userGroup = $temp['group_code'];
    		basic_user::$userType = $temp['user_type'];
    		basic_user::$permissions = $temp['permissions'];
		}else{            
		    basic_user::$permissions = $sql;
		}
	}    
	
	public static function checkPermission($code){
	    $arr = explode(",", basic_user::$permissions);
	    for($i=0;$i<count($arr);$i++){
	        if($code == $arr[$i]){
	            return true; 
	        }
	    }
	    return false;
	}
    
	public static function login($username=NULL,$md5PasswordTime=NULL,$ip=NULL,$client=NULL){
		$t_return = array('status'=>'2','msg'=>'wrong');
	    if(tools::$systemType=='DZX'){
	        $t_return = basic_user::login_dzx();
	    }elseif(tools::$systemType=='JOOMLA'){
	        $t_return = basic_user::login_joomla();
	    }elseif(tools::$systemType=='DEDE'){
	        $t_return = basic_user::login_dede();
	    }else{
    	    if($username==NULL)$username = $_REQUEST['username'];
    	    if($md5PasswordTime==NULL)$md5PasswordTime = $_REQUEST['password'];
    	    if($ip==NULL)$ip = $_SERVER["REMOTE_ADDR"] ;
    	    if($client==NULL)$client = $_SERVER['HTTP_USER_AGENT'] ;
	        $t_return = basic_user::login_mobile($username,$md5PasswordTime,$ip,$client,"0","0");
	    }
		
		return $t_return;
	}
	
	public static function login_joomla(){
	    $key = md5(md5( tools::$joomlaConfig->secret.'site'));
	    $pfx = tools::$joomlaConfig->dbprefix;
	    $conn = tools::getConn();
	    
	    if(!isset($_COOKIE[$key])){
		    return array(
		        'status'=>2
		        ,'msg'=>'Please login the joomla first'
		    );
	    }
	    
		$sql = "select username,guest,userid,group_id from 
		".$pfx."session,
		".$pfx."user_usergroup_map
		
		where session_id = '".$_COOKIE[$key]."' 
			and ".$pfx."user_usergroup_map.user_id = ".$pfx."session.userid and client_id = 0
		";
		
        $res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp==false){
		    return array(
		        'status'=>2
		        ,'msg'=>'Please login the joomla first'
		    );
		}
		
		$sql2 = "select * from basic_user where username = '".$temp['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);			
		
        if($temp2==false){      
            $type = "'20'"; $group = "'20'"; 
            if($temp['group_id']=='5' || $temp['group_id']=='6' || $temp['group_id']=='7'){
                $type = '30'; $group = "'80'"; 
            }
			$data = array(
			    'username'=>"'".$temp['username']."'"
			    ,'password'=>"md5('".$temp['username']."')"
			    ,'money'=>"'100'"
			    ,'group_code'=>$group
			    ,'group_all'=>$group
			    ,'type'=>$type
			    ,'id'=>tools::getTableId("basic_user")
			);
			
			$sql = "insert into basic_user (";
    		$sql_ = ") values (";
    		$keys = array_keys($data);
    		for($i=0;$i<count($keys);$i++){
        		$sql .= $keys[$i].",";
    		    $sql_ .= $data[$keys[$i]].",";
    		}
    		$sql = substr($sql, 0,strlen($sql)-1);
    		$sql_ = substr($sql_, 0,strlen($sql_)-1).")";
    		$sql = $sql.$sql_;		
 	  
    		mysql_query($sql,$conn);	

    		$sql = "insert into basic_group_2_user (user_code,group_code) values ('".$temp['username']."',".$group.");";
    		mysql_query($sql,$conn);	    		
        }		
		
		$t_return = basic_user::login_mobile($temp['username'],'md5(concat(password, hour(now()) ))',$_SERVER["REMOTE_ADDR"],$_SERVER['HTTP_USER_AGENT'],"0","0");
		return $t_return;		
	}
	
	public static function login_dzx(){
	    if(!isset($_COOKIE[tools::$dzxConfig['cookie']['cookiepre'].'2132_sid'])){
		    return array(
		        'status'=>2
		        ,'msg'=>'Please login the Discuzx first'
		    );
	    }
        $sessionid = $_COOKIE[tools::$dzxConfig['cookie']['cookiepre'].'2132_sid'];
		$conn = tools::getConn();
		$pfx = tools::$dzxConfig['db'][1]['tablepre'];
		
		$sql_dzx = "select 
			".$pfx."common_member.username
			,".$pfx."common_member.password
			,".$pfx."common_member.adminid
			,".$pfx."common_member.groupid
			,(select type from ".$pfx."common_usergroup where ".$pfx."common_usergroup.groupid = ".$pfx."common_member.groupid) as type
			,".$pfx."common_member_count.extcredits2 as money 
			,".$pfx."common_member.credits
			,".$pfx."common_session.sid
			 from 
			 ".$pfx."common_member
			,".$pfx."common_member_count
			,".$pfx."common_session
			 where 			
			".$pfx."common_member.uid = ".$pfx."common_member_count.uid and ".$pfx."common_session.uid = ".$pfx."common_member.uid
			 and ".$pfx."common_session.sid = '".$sessionid."';
		";

		$res = mysql_query($sql_dzx,$conn);
		$data_dzx = mysql_fetch_assoc($res);
		if($data_dzx==false){
		    return array(
		        'status'=>2
		        ,'msg'=>'Please login the Discuzx first'
		    );
		}
		
		$sql2 = "select * from basic_user where username = '".$data_dzx['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);			
		
		//如果DZX中有这个用户,而WLS中没有,就先把 DZX 中的用户同步到 WLS
        if($temp2==false){      
            $type = "'20'"; $group = "'20'"; 
            if($data_dzx['groupid']=='2' || $data_dzx['groupid']=='3'){
                $type = '30'; $group = "'80'";      
            }
            if($data_dzx['groupid']=='1'){
                $type = '10'; $group = "'10'";      
            }            
            if($data_dzx['type']=='special')$group = $data_dzx['groupid'];
			$data = array(
			    'username'=>"'".$data_dzx['username']."'"
			    ,'password'=>"md5('".$data_dzx['username']."')"
			    ,'money'=>"'100'"
			    ,'group_code'=>$group
			    ,'group_all'=>$group
			    ,'type'=>$type
			    ,'id'=>tools::getTableId("basic_user")
			    ,'status'=>'10'
			);
			
			$sql = "insert into basic_user (";
    		$sql_ = ") values (";
    		$keys = array_keys($data);
    		for($i=0;$i<count($keys);$i++){
        		$sql .= $keys[$i].",";
    		    $sql_ .= $data[$keys[$i]].",";
    		}
    		$sql = substr($sql, 0,strlen($sql)-1);
    		$sql_ = substr($sql_, 0,strlen($sql_)-1).")";
    		$sql = $sql.$sql_;		
 	  
    		mysql_query($sql,$conn);	

    		$sql2 = "insert into basic_group_2_user (user_code,group_code) values ('".$data_dzx['username']."',".$group.");";
    		mysql_query($sql2,$conn);	    		
        }		
		
		$t_return = basic_user::login_mobile($data_dzx['username'],'md5(concat(password, hour(now()) ))',$_SERVER["REMOTE_ADDR"],$_SERVER['HTTP_USER_AGENT'],"0","0");
		//$t_return['sql1'] = $sql;
		//$t_return['sql2'] = $sql2;
		//$t_return['sql_dzx'] = $sql_dzx;
		$t_return['loginData']['money'] = $data_dzx['money'];
		$t_return['loginData']['credits'] = $data_dzx['credits'];
		return $t_return;
	}
	
    public static function login_dede(){
	    if(!isset($_COOKIE['DedeUserID'])){
		    return array(
		        'status'=>2
		        ,'msg'=>'Please login the Dede CMS first'
		    );
	    }
        $sessionid = $_COOKIE['DedeUserID'];
        include_once '../../data/common.inc.php';
		$conn = tools::getConn();
		$pfx = $cfg_dbprefix;
		
		$sql = "SELECT
        dede_member.userid as username,
        dede_member.money,
        dede_member.matt as groupid,
        dede_member.`mid`
        FROM
        dede_member where mid = ".$sessionid;
		
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp==false){
		    return array(
		        'status'=>2
		        ,'msg'=>'Please login the Discuzx first'
		    );
		}
		
		$sql2 = "select * from basic_user where username = '".$temp['username']."' ";
		$res2 = mysql_query($sql2,$conn);
		$temp2 = mysql_fetch_assoc($res2);			
		
		//如果DZX中有这个用户,而WLS中没有,就先把 DZX 中的用户同步到 WLS
        if($temp2==false){      
            $type = "'20'"; $group = "'20'";
			$data = array(
			    'username'=>"'".$temp['username']."'"
			    ,'password'=>"md5('".$temp['username']."')"
			    ,'money'=>"'100'"
			    ,'group_code'=>$group
			    ,'group_all'=>$group
			    ,'type'=>$type
			    ,'id'=>tools::getTableId("basic_user")
			);
			
			$sql = "insert into basic_user (";
    		$sql_ = ") values (";
    		$keys = array_keys($data);
    		for($i=0;$i<count($keys);$i++){
        		$sql .= $keys[$i].",";
    		    $sql_ .= $data[$keys[$i]].",";
    		}
    		$sql = substr($sql, 0,strlen($sql)-1);
    		$sql_ = substr($sql_, 0,strlen($sql_)-1).")";
    		$sql = $sql.$sql_;		
 	  
    		mysql_query($sql,$conn);	

    		$sql2 = "insert into basic_group_2_user (user_code,group_code) values ('".$temp['username']."',".$group.");";
    		mysql_query($sql2,$conn);	    		
        }		
		
		$t_return = basic_user::login_mobile($temp['username'],'md5(concat(password, hour(now()) ))',$_SERVER["REMOTE_ADDR"],$_SERVER['HTTP_USER_AGENT'],"0","0");
		$t_return['sql1'] = $sql;
		$t_return['sql2'] = $sql2;
		return $t_return;
	}	
	
	public static function login_phpwind(){
	    //TODO
	}

	public static function login_mobile($username=NULL,$md5PasswordTime=NULL,$ip=NULL,$client=NULL,$gis_lat=NULL,$gis_lot=NULL){
	    if($username==NULL)$username = $_REQUEST['username'];
	    if($md5PasswordTime==NULL)$md5PasswordTime = $_REQUEST['password'];
	    if($username!='guest' && tools::$systemType== 'WLS')$md5PasswordTime = "'".$md5PasswordTime."'";
	    if($ip==NULL)$ip = $_SERVER["REMOTE_ADDR"] ;
	    if($client==NULL)$client = $_SERVER['HTTP_USER_AGENT'] ;	    
	    if($gis_lat==NULL)$gis_lat = $_REQUEST['gis_lat'] ;
	    if($gis_lot==NULL)$gis_lot = $_REQUEST['gis_lot'] ;
		$t_return = array();
		$conn = tools::getConn();
		$sql = "";
		
		//判断系统内存表状态,如果为空,则说明需要初始化内存数据
		$sql = "select count(*) as total from basic_memory ";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		if($temp['total']=='0'){
		    tools::initMemory();
		}	
		
		//如果是访客登录,就不验证密码
		if($username=="guest"){
		    $md5PasswordTime = "md5(concat(password, hour(now()) ))";
		}
		
		//验证用户的密码,如果服务器的时钟跟用户的前端时钟不一致,需要修改
		mysql_query("set time_zone = '+8:00'; ");
		$sql = tools::getConfigItem("basic_user__login_check");
		$sql = str_replace("__username__", "'".$username."'",$sql);
		$sql = str_replace("__password__",$md5PasswordTime,$sql);
		$sql = str_replace("\n"," ",$sql);

		$res = mysql_query($sql,$conn);
		if(!$temp = mysql_fetch_assoc($res)){
		    return array(
		        'status'=>'2'
		        ,'msg'=>'Wrong password or username or group was closed'
		        ,'sql'=>$sql
		    );
		}else{
		    $session = md5(rand(10000, 99999));
		    $temp['session'] = md5($session.date("G"));
		    
		    $t_return = array(
				'foo'=>'bar'
		        ,'logindata'=>$temp
		        ,'status'=>"1"
		        ,'msg'=>'OK'
		        ,'permissions'=>basic_user::getPermissionTree($username)
		        ,'il8n'=>tools::readIl8n()	
		        ,'H'=>date("G")	       
		    );
		    
            $sql_logout = tools::getConfigItem("basic_user__login_logout");
            $sql_logout = str_replace( '__user_code__', "'".$username."'",$sql_logout);
			mysql_query($sql_logout,$conn);
						
			//更新SESSION表
			$permissions = basic_user::getPermission($username);
			$sql = tools::getConfigItem("basic_user__login_session");
			$sql = str_replace( '__username__', "'".$username."'" ,$sql);
			$sql = str_replace( '__permissions__', "'".$permissions."'",$sql);
			$sql = str_replace( '__session__', "'".$session."'",$sql);
			$sql = str_replace( '__ip__', "'".$ip."'",$sql);
			$sql = str_replace( '__client__', "'".$client."'",$sql);
			$sql = str_replace( '__gis_lat__', "'".$gis_lat."'",$sql);
			$sql = str_replace( '__gis_lot__', "'".$gis_lot."'",$sql);
			mysql_query($sql,$conn);
		}
		
		return $t_return;
	}
    
	public static function logout($username=NULL,$session=NULL){
	    if($username==NULL)$username = $_REQUEST['executor'];
	    if($session==NULL)$session = $_REQUEST['session'];
	    
		$conn = tools::getConn();
		$sql = tools::getConfigItem("basic_user__logout");
		$sql = str_replace("__user_code__", "'".$username."'", $sql) ;
		$sql = str_replace("__session__", "'".$session."'", $sql) ;
		mysql_query($sql,$conn);
		
		return array(
		    'status'=>'1'
		    ,'msg'=>'ok'
		);
	}
	
	public static function modify($data=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120222")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	    
	    if($data==NULL)$data = $_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];	    
	    $conn = tools::getConn();
	    
	    $t_data = json_decode2($data,true);
	    $username = $t_data['username'];
	    unset($t_data['username']);
		$str_keys = ",username,group_code,status,type,password,money";		
		$sql = "";

		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    if(!strpos($str_keys, $keys[$i])){		        
		        return array(
		            'status'=>'2'
		            ,'msg'=>'data wrong'.$keys[$i]
		        );
		    }
		    if ($keys[$i]=='group_code') {
		        mysql_query("delete from basic_group_2_user where user_code = '".$username."' ;",tools::getConn());
		        mysql_query("insert into basic_group_2_user (user_code,group_code) values ('".$username."','".$t_data[$keys[$i]]."'); ;",tools::getConn());
		    }
		    $t_data[$keys[$i]] = "'".$t_data[$keys[$i]]."'";
		}
		$t_data['time_lastupdated'] = "now()";
		$t_data['count_updated'] = "count_updated+1";
		
		$sql = "update basic_user set ";
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $sql .= $keys[$i]." = ".$t_data[$keys[$i]].",";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql .= " where username = '".$username."' ";
		
		mysql_query($sql,$conn);		
		
		return array(
            'status'=>'1'
            ,'msg'=>'ok'
        );
	}		
	
	public static function modify_myself($data=NULL,$executor=NULL){
        if (!basic_user::checkPermission("1101")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	    	    
	    if($data==NULL)$data = $_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    
	    $conn = tools::getConn();
	    
	    $t_data = json_decode2($data,true);
		$str_keys = ",password,email,photo,";		
		$sql = "";
		$password_old = $t_data["password_old"];
		unset($t_data["password_old"]);
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    if(!strpos($str_keys, $keys[$i])){
		        echo strpos($str_keys, $keys[$i]);
		        return array(
		            'status'=>'2'
		            ,'msg'=>'data wrong'.$keys[$i]
		        );
		    }
		    $t_data[$keys[$i]] = "'".$t_data[$keys[$i]]."'";
		}
		$t_data['time_lastupdated'] = "now()";
		$t_data['count_updated'] = "count_updated+1";
		
		$sql = "update basic_user set ";
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $sql .= $keys[$i]." = ".$t_data[$keys[$i]].",";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql .= " where username = '".$executor."' and password = '".$password_old."'";
		
		mysql_query($sql,$conn);		
		
		return array(
            'status'=>'1'
            ,'msg'=>'ok'
        );
	}	
	
    public static function loadConfig() {
        $conn = tools::getConn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_user__type' and code not in ('1','9')  order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'basic_user__status' order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;
		
		$sql = "select code,name as value from basic_group order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['group'] = $data;

	    return $config;		
	}  
    
	public static function add($data=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120221")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	   	    
	    if($data==NULL)$data=$_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    
	    $t_data = json_decode2($data,true);
		$conn = tools::getConn();
		$username = $t_data['username'];
		if(basic_user::checkUsernameUsed($username)){
			return array(
                'status'=>"2"
                ,'msg'=>'username wrong'
            );
		}				
		
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $t_data[$keys[$i]] = "'".$t_data[$keys[$i]]."'";
		}
		
		$id = tools::getTableId("basic_user");
		$t_data['id'] = "'".$id."'";
		$t_data['creater_code'] = "'".$executor."'";
		$t_data['creater_group_code'] = "(select group_code from basic_group_2_user where user_code = '".$executor."' order by group_code limit 1 )";
		
		$sql = "insert into basic_user (";
		$sql_ = ") values (";
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
    		$sql .= $keys[$i].",";
		    $sql_ .= $t_data[$keys[$i]].",";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql_ = substr($sql_, 0,strlen($sql_)-1).")";
		$sql = $sql.$sql_;			
		mysql_query($sql,$conn);
		
		$user_code = $t_data["username"];
		$group_code = $t_data["group_code"];
		
		$sql = "insert into basic_group_2_user (user_code,group_code) values (".$user_code.",".$group_code.");";
		mysql_query($sql,$conn);
		
		$sql = "update basic_group set count_users = (select count(*) from basic_user where basic_user.group_code = basic_group.code )";
		mysql_query($sql,$conn);
		
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
	}
	
	public static function add_register(){
	    $conn = tools::getConn();   	    
	    $json = json_decode2($_REQUEST['data'],true);

	    $data = array(
	        'username'=>"'".$json['executor']."'"
	        ,'password'=>"md5('".$json['password']."')"
	        ,'money'=>'100'
	        ,'group_code'=>"'20'"
	        ,'group_all'=>"'20'"
	        ,'id'=>tools::getTableId("basic_user")
	        ,'type'=>'20'
	    );
	    
	    $sql = "select * from basic_user where username = ".$data['username']."";	
	    $res = mysql_query($sql,$conn);
	    $temp = mysql_fetch_assoc($res);
	    if($temp!=false){
	        return array(
                'status'=>"2"
                ,'msg'=>'username already used'
            );
	    }    
	    
		$sql = "insert into basic_user (";
		$sql_ = ") values (";
		$keys = array_keys($data);
		for($i=0;$i<count($keys);$i++){
    		$sql .= $keys[$i].",";
		    $sql_ .= $data[$keys[$i]].",";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql_ = substr($sql_, 0,strlen($sql_)-1).")";
		$sql = $sql.$sql_;		    		
		mysql_query($sql,$conn);	

		$sql = "insert into basic_group_2_user (user_code,group_code) values (".$data['username'].",".$data['group_code'].");";
		mysql_query($sql,$conn);
		
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
            ,'data'=>$data
        );
	}
    
    public static function view(){
        if (!basic_user::checkPermission("120202")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	        
        $conn = tools::getConn();    
        $id = $_REQUEST['id'];
        
        $sql = tools::getConfigItem("basic_user__view");
        $sql = str_replace("__id__", $id, $sql);
        $res = mysql_query($sql, $conn );
        $data= mysql_fetch_assoc($res);
        
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
            ,'data'=>$data
        );
    }  

	public static function updateSession($user_code=NULL,$session=NULL){
	    if($user_code==NULL)$user_code = $_REQUEST['executor'];
	    if($session==NULL)$session = $_REQUEST['session'];
	    
		$r_session = md5(rand(1000, 9999));
		$sql = tools::getConfigItem("basic_user__session_update");
		$sql = str_replace("__user_code__", "'".$user_code."'", $sql);
		$sql = str_replace("__r_session__", "'".$r_session."'", $sql);
		$sql = str_replace("__session__", "'".$session."'", $sql);
        $conn = tools::getConn();
        mysql_query($sql, $conn );
        		
		$r_session = md5($r_session.date("G"));
		return array(
		    'status'=>'1'
		    ,'session'=>$r_session
		);		
	}
	
	public static function group_get($username=NULL){
        if (!basic_user::checkPermission("120290")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($username==NULL)$username = $_REQUEST['username'];
		$conn = tools::getConn();
		
		$sql = tools::getConfigItem("basic_user__group_get");
		$sql = str_replace("__username__", "'".$username."'", $sql);
		
        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            if ($temp['user_code']!=NULL) {
                $temp['ischecked'] = 1;
            }
            $data[] = $temp;
        }
        $data = tools::list2Tree($data);

        return $data;
	}	
	
	public static function group_set($username=NULL,$group_codes=NULL){
        if (!basic_user::checkPermission("120290")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($username==NULL)$username = $_REQUEST['username'];
	    if($group_codes==NULL)$group_codes = $_REQUEST['group_codes'];
		$conn = tools::getConn();
		
		$sql = "delete from basic_group_2_user where user_code = '".$username."' ";
		mysql_query($sql,$conn);
		
		$group_codes = explode(",", $group_codes);
		for($i=0;$i<count($group_codes);$i++){
		    $sql = "insert into basic_group_2_user (user_code,group_code) values ( '".$username."','".$group_codes[$i]."' ); ";
		    mysql_query($sql,$conn);
		}
		
		$sql = "update basic_user set group_all = '".implode($group_codes,",")."', group_code = (select group_code from basic_group_2_user where user_code = '".$username."' order by group_code limit 1) where username = '".$username."'";
		mysql_query($sql,$conn);
		
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
	}		
}