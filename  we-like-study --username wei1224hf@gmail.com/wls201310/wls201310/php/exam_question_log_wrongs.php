<?php
class exam_question_log_wrongs {
        
    public static function grid($search=NULL,$page=NULL,$pagesize=NULL){
	    if (!basic_user::checkPermission("43")){
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
            if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_question.title like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='subject_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_question.subject_code = '".$search[$search_keys[$i]]."' ";
            }    
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_question.type = '".$search[$search_keys[$i]]."' ";
            }                               	
        }
        $sql_order = ' order by exam_question_log_wrongs.id desc ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by ".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
        $data = array();
        
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //管理员角色          
            $sql = tools::getConfigItem("exam_question_log_wrongs__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM
			exam_question_log_wrongs
			Left Join exam_question ON exam_question_log_wrongs.question_id = exam_question.id ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }
        if(basic_user::$userType=='20'){ 
            //学生角色
            $sql_where .= " and exam_question_log_wrongs.creater_code = '".$_REQUEST['executor']."'";          
            $sql = tools::getConfigItem("exam_question_log_wrongs__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM 
            exam_question_log_wrongs
			Left Join exam_question ON exam_question_log_wrongs.question_id = exam_question.id ".$sql_where;
            
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
	    if (!basic_user::checkPermission("4322")){
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
		    $sql = "delete from exam_question_log_wrongs where id = '".$ids[$i]."' ;";
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
        
        $sql = "select code,extend4 as value from basic_memory where type = '4' and extend5 = 'exam_subject__code'  order by code";
        if(basic_user::$userGroup=='20'){
           $sql = "select code,extend4 as value from basic_memory where type = '4' and extend5 = 'exam_subject__code' and code in (select subject_code from exam_subject_2_group where group_code = '".basic_user::$userGroup."' ) order by code"; 
        }
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['exam_subject__code'] = $data;        

	    return $config;		
	}
	
	public static function questions() {
	    if (!basic_user::checkPermission("4390")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    $conn = tools::getConn();
	    
	    $sql = tools::getConfigItem("exam_question_log_wrongs__questions");
	    $sql = str_replace("__creater_code__", "'".$_REQUEST['executor']."'", $sql);
	    //echo $sql;
	    $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		return $data;		
	}
}