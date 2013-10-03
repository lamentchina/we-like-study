<?php
class exam_paper_multionline {
        
    public static function grid($search=NULL,$page=NULL,$pagesize=NULL){
        if (!basic_user::checkPermission("41")){
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
            if($search_keys[$i]=='status' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and exam_paper.status = '".$search[$search_keys[$i]]."' ";
            }                                   	
        }
        $sql_order = ' order by exam_paper_multionline.time_start desc ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by ".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
        $data = array();
        
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //管理员角色          
            $sql = tools::getConfigItem("exam_paper_multionline__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);
            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM
    		exam_paper_multionline
    		Left Join exam_paper ON exam_paper_multionline.paper_id = exam_paper.id ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
            
        }if(basic_user::$userType=='20'){ 
            //学生角色
            $sql_where .= " and concat(',',exam_paper_multionline.students,',') like '%,".$_REQUEST['executor'].",%' ";          
            $sql = tools::getConfigItem("exam_paper_multionline__grid_student");   
            $sql = str_replace("__creater_code__", "'".$_REQUEST['executor']."'", $sql);      
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){

                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper_multionline ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }if(basic_user::$userType=='30'){ 
            //教师角色
            $sql_where .= " and exam_paper.creater_code = '".$_REQUEST['executor']."'";          
            $sql = tools::getConfigItem("exam_paper_multionline__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM
			exam_paper_multionline
			Left Join exam_paper ON exam_paper_multionline.paper_id = exam_paper.id ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }     
        
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total']
        );

        return $returnData;
    }
        
	public static function remove($ids=NULL,$executor=NULL){
        if (!basic_user::checkPermission("4122")){
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
		    $sql = "delete from exam_paper_multionline where paper_id = '".$ids[$i]."' ;";
		    mysql_query($sql,$conn);		    
		    $sql = "delete from exam_paper where id = '".$ids[$i]."' ;";
		    mysql_query($sql,$conn);
		    $sql = "delete from exam_paper_log where paper_id = '".$ids[$i]."' ;";
		    mysql_query($sql,$conn);		    
		    $sql = "delete from exam_question where paper_id = '".$ids[$i]."' ;";
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
        
        $sql = "select code,value from basic_parameter where reference = 'exam_paper__type'  order by code";
        $res = mysql_query($sql,$conn);
        if($res==false){
            return array(
                'status'=>'2'
                ,'msg'=>mysql_errno($conn)
                ,'sql'=>$sql
            );
        }
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
        $sql = "select code,name as value from exam_subject where type = '20' order by code";
        $res = mysql_query($sql,$conn);
        if($res==false){
            return array(
                'status'=>'2'
                ,'msg'=>mysql_errno($conn)
                ,'sql'=>$sql
            );
        }        
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['exam_subject__code'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'exam_paper__status' order by code";
        $res = mysql_query($sql,$conn);
        if($res==false){
            return array(
                'status'=>'2'
                ,'msg'=>mysql_errno($conn)
                ,'sql'=>$sql
            );
        }        
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;
		
		return array(
		    'status'=>'1'
		    ,'msg'=>'ok'
		    ,'data'=>$config
		);
	}  
	
	public static function questions($paper_id=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4190")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	    
	    if($paper_id==NULL) $paper_id = $_REQUEST['paper_id'];
	    if($executor==NULL) $executor = $_REQUEST['executor'];
        $conn = tools::getConn();
	    
	    $data = exam_paper_multionline::view($paper_id);
	    $cost = $data['data']['cost'];

        $sql = " update basic_user set money = money - ".$cost." , credits = credits + 3 where username = '".$executor."' and money >= ".$cost."  ;";
        mysql_query($sql,$conn);
        $count = mysql_affected_rows($conn);
        if($count==0){
            return array(
                'status'=>'2'
                ,'msg'=>'no money'
            );
        }
        
        $sql = tools::getConfigItem("exam_paper_multionline__questions");            
        $sql = str_replace("__paper_id__", $paper_id, $sql);
        
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
	
	public static function view($id=NULL){
	    if (!basic_user::checkPermission("4102") && !basic_user::checkPermission("4190") && !basic_user::checkPermission("4123")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($id==NULL) $id = $_REQUEST['id'];
	    $conn = tools::getConn();
        $sql = tools::getConfigItem("exam_paper_multionline__view");            
        $sql = str_replace("__paper_id__", $id, $sql);
       
        $res = mysql_query($sql,$conn);
        $data = mysql_fetch_assoc($res);

        return array(
            'data'=>$data
            ,'msg'=>'ok'
            ,'status'=>'1'
            ,'sql'=>$sql
        ); 
	}
	
	public static function submit($paper_id=NULL,$json=NULL,$executor=NULL){	
	    if (!basic_user::checkPermission("4190")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		        
	    if($paper_id==NULL) $paper_id = $_REQUEST['paper_id'];
	    if($json==NULL) $json = $_REQUEST['json'];
	    if($executor==NULL) $executor = $_REQUEST['executor'];
	    $conn = tools::getConn();
	    
	    $data_m = exam_paper_multionline::view($paper_id);
        $data_m = $data_m['data'];
	    if($data_m['time_stop']<date('Y-m-d H:i:s') || $data_m['time_start']>date('Y-m-d H:i:s')){
            return array(
                 'msg'=>tools::readIl8n('exam_paper_multionline','timeWrong')
                ,'status'=>'2'
            ); 
	    }
	    $sql = "select time_created from exam_paper_log where paper_id = '".$paper_id."' and creater_code = '".$_REQUEST['executor']."';";
        $res = mysql_query($sql,$conn);
        $data = mysql_fetch_assoc($res);
        if($data!=false){
    	    $msg = tools::readIl8n('exam_paper_multionline','doneAlready');
    	    $msg = str_replace("__time_submitted__", $data['time_created'], $msg);
            return array(
                 'msg'=>$msg
                ,'status'=>'2'
            ); 
        }
        
	    $data_paper = exam_paper::submit();	    
	    
	    $json_array = json_decode2($json,true);
	    for($i=0;$i<count($json_array);$i++){
	        $item = $json_array[$i];
	        $item['paper_log_id'] = $data_paper['paper_log']['id'];	        
	        
            $keys = array_keys($item);
            $keys = implode(",",$keys);
            $values = array_values($item);
            $values = implode("','",$values);    
            $sql = "insert into exam_question_log (".$keys.") values ('".$values."')";
            mysql_query($sql,$conn);
	        
	    }
	    unset($data_paper['questions']);
	    $data_paper['item'] = $item;
	    
	    $sql = "update exam_paper_log set status = '20' where id =".$data_paper['paper_log']['id'];
	    mysql_query($sql,$conn);
	    
	    $msg = tools::readIl8n('exam_paper_multionline','submitted');
	    $msg = str_replace("__time_stop__", $data_m['time_stop'], $msg);
	    return array(
	        'status'=>'1'
	        ,'msg'=>$msg
	    );	   
	}
	
	public static function modify($data=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4123")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($data==NULL)$data = $_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    
	    $conn = tools::getConn();
	    
	    $t_data = json_decode2($data,true);
	    $paper_id = $t_data['paper_id'];
	    unset($t_data['paper_id']);
		$str_keys = ",time_start,time_stop,passline,students,";		
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
		
		$sql = "update exam_paper_multionline set ";
		$keys = array_keys($t_data);
		for($i=0;$i<count($keys);$i++){
		    $sql .= $keys[$i]." = ".$t_data[$keys[$i]].",";
		}
		$sql = substr($sql, 0,strlen($sql)-1);
		$sql .= " where paper_id = '".$paper_id."' ";		

		if(mysql_query($sql,$conn)){		
    		return array(
                'status'=>'1'
                ,'msg'=>'ok'
            );
		}else{
		    return array(
                'status'=>'2'
                ,'msg'=>'sql wrong'
                ,'sql'=>$sql
            );
		}
	}	
	
	public static function upload(){	
	    if (!basic_user::checkPermission("4111")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		        
	    $data = exam_paper::upload();
	    if(!isset($data['status']) || $data['status']!='1')return $data;
	    $conn = tools::getConn();
	    $phpexcel = exam_paper::$phpexcel;
	    $currentSheet = $phpexcel->getSheetByName('exam_paper_multionline');
	    if($currentSheet==null){
	        return array(
	             'msg'=>'Missing sheet exam_paper_multionline, check your excel'
	            ,'status'=>'2'
	        );
	    }
	    
        $data2 = array(
             'id'=>tools::getTableId("exam_paper_multionline")
            ,'time_start'=>$currentSheet->getCell('A2')->getValue()
            ,'time_stop'=>$currentSheet->getCell('B2')->getValue()
            ,'passline'=>$currentSheet->getCell('C2')->getValue()
            ,'students'=>$currentSheet->getCell('D2')->getValue()
            ,'paper_id'=>$data['paper_id']
            ,'count_total'=>count( explode(",", $currentSheet->getCell('D2')->getValue()) )		
        );
        
        $sql = "update exam_paper set type = '10' where id =".$data['paper_id'];
        mysql_query($sql,$conn);
        
        $keys = array_keys($data2);
        $keys = implode(",",$keys);
        $values = array_values($data2);
        $values = implode("','",$values);    
        $sql = "insert into exam_paper_multionline (".$keys.") values ('".$values."')";
        mysql_query($sql,$conn);
        
		return array(
		     'status'=>'1'
		    ,'msg'=>"ok"
		    ,'paper_id'=>$data['paper_id']
		    ,'paper_multionline_id'=>$data2['id']
		);        
	}
	
	public static function download($id=NULL){
	    if (!basic_user::checkPermission("4112")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($id==NULL)$id = $_REQUEST['id'];
	    $data = exam_paper::download($id);
	    $phpexcel = exam_paper::$phpexcel;
	    
	    $data2 = exam_paper_multionline::view($id);
	    $data2 = $data2['data'];
		$phpexcel->createSheet();
		$phpexcel->setActiveSheetIndex($phpexcel->getSheetCount()-1);
		$phpexcel->getActiveSheet()->setTitle('exam_paper_multionline');	
		
		$phpexcel->getActiveSheet()->setCellValue('A1', tools::$LANG['exam_paper_multionline']['time_start']);
		$phpexcel->getActiveSheet()->setCellValue('B1', tools::$LANG['exam_paper_multionline']['time_stop']);
		$phpexcel->getActiveSheet()->setCellValue('C1', tools::$LANG['exam_paper_multionline']['passline']);
		$phpexcel->getActiveSheet()->setCellValue('D1', tools::$LANG['exam_paper_multionline']['students']);		
		
		$phpexcel->getActiveSheet()->setCellValue('A2', $data2['time_start']);
		$phpexcel->getActiveSheet()->setCellValue('B2', $data2['time_stop']);
		$phpexcel->getActiveSheet()->setCellValue('C2', $data2['passline']);
		$phpexcel->getActiveSheet()->setCellValue('D2', $data2['students']);
		
		$objWriter = new PHPExcel_Writer_Excel5($phpexcel);
		$file =  "../file/download/".date('YmdHis').".xls";
		$objWriter->save($file);

		return array(
		    'status'=>'1'
		    ,'file'=>$file
		);		
	}
	
	public static function order(){
	    if (!basic_user::checkPermission("4102")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    $id = $_REQUEST['id'];
	    $conn = tools::getConn();
	    $sql = tools::getConfigItem("exam_paper_multionline__order");
        $sql = str_replace("__paper_id__", $id, $sql);
        
        $data = array();
        $res = mysql_query($sql,$conn);        
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
		return array(
		    'status'=>'1'
		    ,'Rows'=>$data
		);
	}
}