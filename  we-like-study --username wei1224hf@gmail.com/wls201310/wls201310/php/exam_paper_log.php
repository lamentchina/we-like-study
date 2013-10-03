<?php
class exam_paper_log {
        
    public static function grid($search=NULL,$page=NULL,$pagesize=NULL){
        if (!basic_user::checkPermission("42")){
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
                $sql_where .= " and exam_paper.title like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='subject_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_paper.subject_code = '".$search[$search_keys[$i]]."' ";
            }    
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_paper.type = '".$search[$search_keys[$i]]."' ";
            }    
            if($search_keys[$i]=='status' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_paper_log.status = '".$search[$search_keys[$i]]."' ";
            }                                   	
        }
        $sql_order = ' order by exam_paper_log.id desc ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by ".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
        $data = array();
        
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //管理员角色          
            $sql = tools::getConfigItem("exam_paper_log__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper_log left Join exam_paper ON exam_paper_log.paper_id = exam_paper.id ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }if(basic_user::$userType=='20'){ 
            //学生角色
            $sql_where .= " and exam_paper_log.creater_code = '".$_REQUEST['executor']."'";          
            $sql = tools::getConfigItem("exam_paper_log__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper_log left Join exam_paper ON exam_paper_log.paper_id = exam_paper.id ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        } if(basic_user::$userType=='30'){ 
            //教师角色
            $sql_where .= " and exam_paper.creater_code = '".$_REQUEST['executor']."'";          
            $sql = tools::getConfigItem("exam_paper_log__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper_log left Join exam_paper ON exam_paper_log.paper_id = exam_paper.id ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }    
        
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total']
        );

        return $returnData;
    }
	
    public static function loadConfig() {
        $conn = tools::getConn();
        $config = array();
		
        $sql = "select code,extend4 as value from basic_memory where type = '4' and extend5 = 'exam_subject__code' order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['exam_subject__code'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'exam_paper_log__status' order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;
		
        $sql = "select code,value from basic_parameter where reference = 'exam_question__type' and code not in ('1','9')  order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['exam_question__type'] = $data;		

	    return $config;		
	}
	
	public static function questions($id=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4290")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($id==NULL) $id = $_REQUEST['id'];
	    if($executor==NULL) $executor = $_REQUEST['executor'];
        $conn = tools::getConn();

        $sql = tools::getConfigItem("exam_paper_log__questions");            
        $sql = str_replace("__id__", $id, $sql);
        
        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }

        return array(
             'data'=>$data
            ,'msg'=>'ok'
            ,'status'=>'1'
        ); 
	}	
	
	public static function submit($paper_id=NULL,$data=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4290")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($data==NULL) $data = $_REQUEST['data'];
	    if($executor==NULL) $executor = $_REQUEST['executor'];
	    $conn = tools::getConn();

	    $mycent = 0;
	    $arr = json_decode2($data,true);
	    for($i=0;$i<count($arr);$i++){
	        $mycent += $arr[$i]['mycent'];
	        $sql = "update exam_question_log set mycent = ".$arr[$i]['mycent']." where question_id = ".$arr[$i]['question_id']." and paper_log_id = ".$_REQUEST['paper_log_id']." ";
	        mysql_query($sql,$conn);
	        if(mysql_affected_rows()=='-1'){
        	    return array(
        	        'status'=>'2'
        	        ,'sql'=>$sql
        	    );
	        }
	    }
	    
	    $sql2 = "update exam_paper_log set mycent_subjective = ".$mycent.",mycent = mycent_objective + ".$mycent.",status = '10'  where status = '20' and id = ".$_REQUEST['paper_log_id'];
	    mysql_query($sql2,$conn);
	    $count = mysql_affected_rows($conn);
	    
	    return array(
	        'status'=>'1'
	        ,'mycent'=>$mycent
	        ,'sql'=>$sql
	        ,'sql2'=>$sql2
	    );
	}	
}