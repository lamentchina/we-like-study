<?php
class basic_group {
        
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
        if (!basic_user::checkPermission("1201")){
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
        $sql_where = " where code <> '10' ";
        
        $search=json_decode2($search,true);
        $search_keys = array_keys($search);
        for($i=0;$i<count($search);$i++){
            if($search_keys[$i]=='name' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and name like '%".$search[$search_keys[$i]]."%' ";
            }
                               	
        }
        $sql_order = ' order by code ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by ".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
    
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //管理员角色          
            $sql = tools::getConfigItem("basic_group__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM basic_group ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }   
        
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total']
        );

        return $returnData;
    }
        
	public static function remove($codes=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120122")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	    
	    if($codes==NULL)$codes = $_REQUEST['codes'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
		$conn = tools::getConn();
		$codes = explode(",", $codes);
		for($i=0;$i<count($codes);$i++){
		    $sql = "delete from basic_group where code = '".$codes[$i]."' ;";
		    mysql_query($sql,$conn);
		    
		    $sql = "delete from basic_user where group_code = '".$codes[$i]."' ;";
		    mysql_query($sql,$conn);		

		    $sql = "delete from basic_group_2_user where group_code = '".$codes[$i]."' ;";
		    mysql_query($sql,$conn);			 

		    $sql = "delete from basic_group_2_permission where group_code = '".$codes[$i]."' ;";
		    mysql_query($sql,$conn);			    
		}
		
		return  array(
			'status'=>1
		    ,'msg'=>'OK'
		);
	}
	
	public static function modify($data=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120123")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	 	    
	    if($data==NULL)$data = $_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    
	    $conn = tools::getConn();
	    
	    $t_data = json_decode2($data,true);
	    $code = $t_data['code'];
	    unset($t_data['code']);
		$str_keys = ",name,type,status,remark,";		
		$sql = "";

		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    if(!strpos($str_keys, $keys[$i])){		        
		        return array(
		            'status'=>'2'
		            ,'msg'=>'data wrong'.$keys[$i]
		        );
		    }

		    $t_data[$keys[$i]] = "'".$t_data[$keys[$i]]."'";
		}
		
		$sql = "update basic_group set ";
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $sql .= $keys[$i]." = ".$t_data[$keys[$i]].",";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql .= " where code = '".$code."' ";
		
		mysql_query($sql,$conn);		
		
		return array(
            'status'=>'1'
            ,'msg'=>'ok'
        );
	}			
	
    public static function loadConfig() {
        $conn = tools::getConn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_group__type' and code not in ('1','9')  order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'basic_group__status' order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;

	    return $config;		
	}  
    
	public static function add($data=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120121")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($data==NULL)$data=$_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    
	    $t_data = json_decode2($data,true);
	    
	    //编码长度必须为偶数
	    if(strlen($t_data['code'])%2!=0){
            return array(
                'status'=>"2"
                ,'msg'=>"The code's length must be an even"
            );
	    }
		$conn = tools::getConn();
	    
		//编码必须没有使用过
	    $sql = "select * from basic_group where code = '".$t_data['code']."'";
	    $res = mysql_query($sql,$conn);
	    $temp = mysql_fetch_assoc($res);
	    if($temp!=false){
            return array(
                'status'=>"2"
                ,'msg'=>"Code already used"
            );
	    }
	    
	    $t_data['status'] = '10';		
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $t_data[$keys[$i]] = "'".$t_data[$keys[$i]]."'";
		}
		
		//数据库插入用户组
		$sql = "insert into basic_group (";
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
		
		//分配基础权限
		$sql = "insert into basic_group_2_permission (permission_code,group_code) values ('11',".$t_data['code'].");";
		mysql_query($sql,$conn);
		$sql = "insert into basic_group_2_permission (permission_code,group_code) values ('1199',".$t_data['code'].");";
		mysql_query($sql,$conn);	
		
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
	}
    
    public static function view(){
	        
        $conn = tools::getConn();    
        $code = $_REQUEST['code'];
        
        $sql = "select * from basic_group where code = '".$code."'";
        $res = mysql_query($sql, $conn );
        $data= mysql_fetch_assoc($res);
        
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
            ,'data'=>$data
        );
    }  
    
    public static function permission_set($group_code=NULL,$permission_codes=NULL,$cost_=NULL,$credits_=NULL){
        if (!basic_user::checkPermission("120190")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }        
	    if($group_code==NULL)$group_code=$_REQUEST['code'];
	    if($permission_codes==NULL)$permission_codes = $_REQUEST['codes'];        
	    if($cost_==NULL)$cost_=$_REQUEST['cost_'];
	    if($credits_==NULL)$credits_ = $_REQUEST['credits_'];       	    
		$conn = tools::getConn();


		$sql = "delete from basic_group_2_permission where group_code = '".$group_code."' ";
		mysql_query($sql,$conn);

		$codes = explode(",", $permission_codes) ;

		$cost = explode(",", $cost_) ;
		$credits = explode(",", $credits_) ;
		for($i=0;$i<count($codes);$i++){
			$sql = "insert into basic_group_2_permission (group_code,permission_code,cost,credits) values ( '".$group_code."','".$codes[$i]."','".$cost[$i]."','".$credits[$i]."' ); ";
			mysql_query($sql,$conn);
		}	
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
		return t_return;
	}	
	
	public static function permission_get($code=NULL){
        if (!basic_user::checkPermission("120190")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    } 	    
	    if($code==NULL)$code=$_REQUEST['code'];
		$conn = tools::getConn();
		
		$sql = tools::getConfigItem("basic_group__permission_get");
		$sql = str_replace("__group_code__", "'".$code."'", $sql);

        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            if ($temp['cost']!=NULL) {
                $temp['ischecked'] = 1;
            }
            $data[] = $temp;
        }
		$data = tools::list2Tree($data);
		
		return $data;
	}
}