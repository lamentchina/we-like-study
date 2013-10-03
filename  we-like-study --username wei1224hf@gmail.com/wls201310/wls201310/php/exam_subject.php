<?php
class exam_subject {

    public static function grid($search=NULL,$page=NULL,$pagesize=NULL){
	    if (!basic_user::checkPermission("45")){
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
            $sql = "select name,code,weight,type,(select value from basic_parameter where reference = 'exam_subject__type' and basic_parameter.code = exam_subject.type) as type_,id from exam_subject  ";   
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            $padder = array(
                 'X0'=>''
                ,'X2'=>'--'
                ,'X4'=>'----'
                ,'X6'=>'------'
                ,'X8'=>'--------'
            ); 
            while($temp = mysql_fetch_assoc($res)){
                $len = strlen($temp['code']) - 2;
                $temp['name_'] = $padder['X'.$len].$temp['name'];
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_subject ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }   
        
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total'],
            'sql'=>$sql
        );

        return $returnData;
    }
        
	public static function remove($codes=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4522")){
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
		    $sql = "delete from exam_subject where code = '".$codes[$i]."' ;";
		    mysql_query($sql,$conn);
		}
		
		return  array(
			'status'=>1
		    ,'msg'=>'OK'
		);
	}

	
    public static function loadConfig() {
        $conn = tools::getConn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'exam_subject__type' order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		

	    return $config;		
	}  
    
	public static function add($data=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4521")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	    
	    if($data==NULL)$data=$_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    
	    $t_data = json_decode2($data,true);
		$conn = tools::getConn();			
		
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $t_data[$keys[$i]] = "'".$t_data[$keys[$i]]."'";
		}
		
		$sql = "insert into exam_subject (";
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
		
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
	}
    
    public static function group_set($codes=NULL,$code=NULL){
	    if (!basic_user::checkPermission("4590")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	        
	    if($code==NULL)$code=$_REQUEST['code'];
	    if($codes==NULL)$codes = $_REQUEST['codes'];        
     	    
		$conn = tools::getConn();

		$sql = "delete from exam_subject_2_group where subject_code = '".$code."' ";
		mysql_query($sql,$conn);

		$codes = explode(",", $codes) ;

		for($i=0;$i<count($codes);$i++){
			$sql = "insert into exam_subject_2_group (group_code,subject_code) values ( '".$codes[$i]."','".$code."' ); ";
			mysql_query($sql,$conn);
		}	
        return array(
            'status'=>"1"
            ,'msg'=>'ok'
        );
		return t_return;
	}	
	
	public static function group_get($code=NULL){
	    if (!basic_user::checkPermission("4590")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($code==NULL)$code=$_REQUEST['code'];
		$conn = tools::getConn();
		
		$sql = tools::getConfigItem("exam_subject__group_get");
		$sql = str_replace("__code__", "'".$code."'", $sql);

        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            if ($temp['subject_code']!=NULL) {
                $temp['ischecked'] = 1;
            }
            $data[] = $temp;
        }
		$data = tools::list2Tree($data);
		
		return $data;
	}
}