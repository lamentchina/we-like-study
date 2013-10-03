<?php
class basic_parameter {
        
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
        if (!basic_user::checkPermission("120301")){
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
            if($search_keys[$i]=='name' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and reference like '%".$search[$search_keys[$i]]."%' ";
            }
                               	
        }
        $sql_order = ' order by reference ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by ".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
    
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //管理员角色          
            $sql = "select * from basic_parameter";           
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM basic_parameter ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }   
        
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total']
            ,'sql'=>$sql
        );

        return $returnData;
    }
        
	public static function remove($ids=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120322")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($ids==NULL)$ids = $_REQUEST['ids'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
		$conn = tools::getConn();
		$ids = explode(",", $ids);
		for($i=0;$i<count($ids);$i++){
		    $sql = "delete from basic_parameter where id = '".$ids[$i]."' ;";
		    mysql_query($sql,$conn);		    
		}
		
		return  array(
			'status'=>1
		    ,'msg'=>'OK'
		);
	}
	    
	public static function add($data=NULL,$executor=NULL){
        if (!basic_user::checkPermission("120321")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($data==NULL)$data=$_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
		$conn = tools::getConn();
	    $t_data = json_decode2($data,true);
	    
		$sql = "insert into basic_parameter (";
		$sql_ = ") values (";
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
    		$sql .= $keys[$i].",";
		    $sql_ .= "'".$t_data[$keys[$i]]."',";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql_ = substr($sql_, 0,strlen($sql_)-1).")";
		$sql = $sql.$sql_;			
		$res = mysql_query($sql,$conn);
		if($res!=false){
            return array(
                'status'=>"2"
                ,'msg'=>mysql_error($conn)
                ,'sql'=>$sql
            );
		}
		
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
	}
    
    public static function resetMemory(){
        if (!basic_user::checkPermission("120390")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		
	    
	    $conn = tools::getConn();
	    $sql = "delete from basic_memory";
	    mysql_query($sql,$conn);
	    tools::initMemory();
	    
	    return array(
	        'status'=>'1'
	        ,'msg'=>'ok'
	    );	    
    }
}