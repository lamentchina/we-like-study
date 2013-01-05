<?php
class education_exam {
    
/**
     * 系统大多数的业务逻辑,都转移到数据库用存储过程来实现
     * 但是,列表功能,将使用服务端代码实现,因为列表功能,一般而言就是查询访问功能
     * 是不会对系统的数据做 增删改 这种 写 的操作的,都是 读取 的操作,无需转移到存储过程
     * 
     * return 默认是JSON,是作为 WEB前端,手机终端,接口通信 的主要模式,也有可能是XML,如果是 array 的话,就返回一个数组
     * 输出的数据,其格式为: {Rows:[{key1:'value1',key2:'value2']},Total:12,page:1,pagesize:3,status:1,msg:'处理结果'}
     * search 默认是NULL,将依赖 $_REQUEST['serach'] 来获取,获取到的应该是一个JSON,内有各种查询参数
     */
    public function grid($return='json',$search=NULL,$page=NULL,$pagesize=NULL){
        if($return<>'array'){
            //判断当前用户有没有 查询 权限,如果权限没有,将直接在 tools::error 中断
            if(!tools::checkPermission('16',$_REQUEST['username'],$_REQUEST['session'])){
                tools::error("access wrong");        
            }
            //判断前端是否缺少必要的参数
            if( (!isset($_REQUEST['search'])) || (!isset($_REQUEST['page'])) || (!isset($_REQUEST['pagesize'])) )tools::error('grid action wrong');
            $search=$_REQUEST['search'];
            $page=$_REQUEST['page'];
            $pagesize=$_REQUEST['pagesize'];
        }
        
        //数据库连接口,在一次服务端访问中,数据库必定只连接一次,而且不会断开
        $conn = tools::conn();
        
        //列表查询下,查询条件必定是SQL拼凑的
        $sql_where = " where 1=1 ";
        //判断前端传递过来的查询条件内容,格式是否正确,因为格式必须是一个 JSON 
        if(!tools::isjson($search))tools::error('grid,search data, wrong format');
        $search=json_decode($search,true);
        $search_keys = array_keys($search);

        $sql_order = ' order by education_exam.id desc ';
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出

        if($_REQUEST['user_type']=='1'){ 
            //管理员角色          

            $sql = "     
            SELECT
            education_exam.subject_code,
            education_exam.subject_name,
            education_exam.count_students_planed,
            education_exam.title,
            education_exam.time_start,
            education_exam.time_end,
            education_exam.score,
            education_exam.mode,
            education_exam.passline,
            education_exam.teacher_id,
            education_exam.teacher_name,
            education_exam.teacher_code,
            education_exam.id_paper,
            education_exam.type,
            education_exam.id,
            education_exam.id_creater,
            education_exam.id_creater_group,
            education_exam.code_creater_group,
            education_exam.time_created,
            education_exam.status,
            education_exam.count_students,
            education_exam.count_passed
            FROM
            education_exam
            
    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['title'] = tools::cutString($temp['title'],10);
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total 
            FROM
            education_exam
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }     
           
        if($_REQUEST['user_type']=='2'){ 
            //学生角色
            $sql_where .= " and education_exam_2_student.student_id = '".$_REQUEST['user_id']."' ";
            $sql_order = ' order by education_exam_2_student.id desc ';
            
            for($i=0;$i<count($search);$i++){
                if($search_keys[$i]=='subject' && trim($search[$search_keys[$i]])!='' ){
                    $sql_where .= " and education_exam_2_student.subject_code like '%".$search[$search_keys[$i]]."%' ";
                }
                if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                    $sql_where .= " and education_exam_2_student.exam_title like '%".$search[$search_keys[$i]]."%' ";
                }		
            }            

            $sql = "     
            SELECT
            education_exam_2_student.exam_id,
            education_exam_2_student.exam_title,
            education_exam_2_student.teacher_name,
            education_exam_2_student.subject_code,
            education_exam_2_student.subject_name,
            education_exam_2_student.teacher_id,
            education_exam_2_student.rank,
            education_exam_2_student.rank_calss,
            education_exam_2_student.score,
            education_exam_2_student.passline,
            education_exam_2_student.totalcent,
            education_exam_2_student.id_paper,
            education_exam_2_student.id_paper_log,
            education_exam_2_student.time_start,
            education_exam_2_student.time_end,
            education_exam_2_student.time_submit,
            education_exam_2_student.time_mark,
            education_exam_2_student.id,
            education_exam_2_student.`type`,            
            education_exam_2_student.`status`,
            (select extend4 from basic_memory where extend5 = 'education_exam__type' and code = education_exam_2_student.type) as name_type,            
            (select extend4 from basic_memory where extend5 = 'education_exam_2_student__status' and code = education_exam_2_student.status) as name_status
                              
            FROM
            education_exam_2_student
    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['exam_title'] = tools::cutString($temp['exam_title'],10);
    			$temp['time_start'] = substr( $temp['time_start'],0,10);
    			$temp['time_end'] = substr( $temp['time_end'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total 
            FROM
            education_exam_2_student
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }
        
        if($_REQUEST['user_type']=='3'){ 
            //教师角色
            if(tools::checkPermission('160102',$_REQUEST['username'],$_REQUEST['session'])){
                $sql_where .= " and education_exam.id_creater_group = '".$_REQUEST['group_id']."' ";
            }else if(tools::checkPermission('160103',$_REQUEST['username'],$_REQUEST['session'])){
                $sql_where .= " and education_exam.code_creater_group = '".$_REQUEST['group_code']."' ";
            }else{                
                $sql_where .= " and education_exam.id_creater = '".$_REQUEST['user_id']."' ";
            }
            
            for($i=0;$i<count($search);$i++){
                if($search_keys[$i]=='subject' && trim($search[$search_keys[$i]])!='' ){
                    $sql_where .= " and education_exam.subject_code like '%".$search[$search_keys[$i]]."%' ";
                }
                if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                    $sql_where .= " and education_exam.title like '%".$search[$search_keys[$i]]."%' ";
                }		
            }

            $sql = "            
            SELECT
            education_exam.subject_code,
            education_exam.subject_name,
            education_exam.count_students_planed,
            education_exam.count_students,
            education_exam.count_passed,
            education_exam.title,
            education_exam.time_start,
            education_exam.time_end,
            education_exam.score,
            education_exam.mode,
            education_exam.teacher_id,
            education_exam.teacher_code,
            education_exam.teacher_name,
            education_exam.id_paper,
            education_exam.`type`,
            education_exam.id,
            education_exam.`status`,
            (select extend4 from basic_memory where extend5 = 'education_exam__type' and code = education_exam.type) as name_type,            
            (select extend4 from basic_memory where extend5 = 'education_exam_2_student__status' and code = education_exam.status) as name_status            
            FROM
            education_exam
            
    				
    		".$sql_where."
    		".$sql_order."
			limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['title'] = tools::cutString($temp['title'],10);
    			$temp['time_start'] = substr( $temp['time_start'],0,10);
    			$temp['time_end'] = substr( $temp['time_end'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from
    		education_exam ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);                      
        }
        
        $returnData = array(
            'Rows'=>$data,
            'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)),
            'Total'=>$total['total']
        );
        if ($return=='array') {
            return $returnData;
        }
        
        echo json_encode($returnData);
    }     
    
    public function import(){
        $path = NULL;
        $CONN = tools::conn();
        
        //tools::checkPermission("18");
        include_once '../libs/ajaxUpload/php.php';
        $allowedExtensions = array("xls");
        $sizeLimit = 10 * 1024 * 1024;
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('../file/upload/');
        $path = $uploader->savePath;
        
        include_once 'basic_excel.php';
        basic_excel::import($path);        
        
        echo basic_excel::$guid;
		mysql_query("call education_exam__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);        
    }      
    
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'education_exam__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
        $sql = "select code,value from basic_parameter where reference = 'education_exam__mode' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['mode'] = $data;	
		
        $sql = "select code,name as value from education_subject order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['subject'] = $data;			

		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}		
    }           
    
    public function delete(){
        if(!(isset($_REQUEST['ids']))){
            die("no ids");
        }
        $CONN = tools::conn();
        $ids = $_REQUEST['ids'];
		$arr = explode(",", $ids);
		for($i=0;$i<count($arr);$i++){
		    mysql_query("call education_exam__delete(".$arr[$i].")",$CONN);
		}

        sleep(1);
        echo json_encode(array('state'=>1));
    }
    
    public function view(){
        tools::checkPermission("1590");//TODO
        $id = $_REQUEST['id'];
        $CONN = tools::conn();

        $sql = " 
        SELECT
        education_exam.subject_code,
        education_exam.subject_name,
        education_exam.count_students_planed,
        education_exam.title,
        education_exam.place,
        left(education_exam.time_start,10) as time_start,
        left(education_exam.time_end,10) as time_end,
        education_exam.mode,
        education_exam.passline,
        education_exam.invigilator,
        education_exam.teacher_name,
        education_exam.teacher_id,
        education_exam.teacher_code,
        education_exam.id_paper,
        education_exam.`type`,
        education_exam.id,
        education_exam.`status`,
        education_paper.count_questions,
        education_paper.cent
        FROM
        education_exam
        Left Join education_paper ON education_exam.id_paper = education_paper.id
        
        where education_exam.id = '".$id."'";
        //echo $sql;exit();
        $res = mysql_query($sql,$CONN);
        $data = mysql_fetch_assoc($res);
        //print_r($data);
        $data['sql'] = preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql));
        echo json_encode($data);
    }
    
    /**
     * 前端提交了一张试卷,服务端接收提交过来的 用户作答答案 ,并入库,
     * 然后计算有没有做错,然后返回正确答案跟解题思路
     */
    public function submit(){
        if(!tools::checkPermission("16"))tools::error(array('access wrong'));//TODO
        //判断前端是否传递了足够的参数
        if( (!isset($_REQUEST['json'])) 
         || (!isset($_REQUEST['id_paper'])) 
         || (!isset($_REQUEST['user_id']))
         || (!isset($_REQUEST['id'])) )die('wrong post');
        $CONN = tools::conn();
        
        $res = mysql_query("select id_paper_log from education_exam_2_student where id = ".$_REQUEST['id'],$CONN);
        $data = mysql_fetch_assoc($res);      
        if($data['id_paper_log']!='0'){
            echo json_encode(array('msg'=>'done befor','status'=>'0'));
            exit();
        }  
        
        $id_paperlog = tools::getId("education_paper_log");
        //先生成一份试卷做题日志,并得到日志编号
        $sql = "insert into education_paper_log (
            	id_paper
            	,id_creater
            	,id_creater_group
            	,code_creater_group
            	,id
        	) values (
            	'".$_REQUEST['id_paper']."'
            	,'".$_REQUEST['user_id']."'
            	,'".$_REQUEST['group_id']."'
            	,'".$_REQUEST['group_code']."'
            	,'".$id_paperlog."'
        	);";
        mysql_query($sql,$CONN);
        
        //从前端POST过来的JSON数据中,解析出学生提交的答案
        $arr = json_decode($_REQUEST['json'],true);
        //并将做题答案直接插入到数据库表中
        $sql = "insert into education_question_log (
         id_question
        ,myanswer
        ,id_creater
        ,id_creater_group
        ,code_creater_group
        ,id_paper
        ,id_paper_log
        ,id
        ) values ";
    
        for($i=1;$i<count($arr);$i++){
            $sql .= " (
            '".$arr[$i]['id']."'
            ,'".$arr[$i]['myanswer']."'
            ,'".$_REQUEST['user_id']."'
            ,'".$_REQUEST['group_id']."'
            ,'".$_REQUEST['group_code']."'
            ,'".$_REQUEST['id_paper']."'
            ,'".$id_paperlog."'
            ,'".tools::getId("education_question_log")."'
            ),";            
        }
        $sql = substr($sql,0,strlen($sql)-1);
        //echo $sql;
        mysql_query($sql,$CONN);
        mysql_query("update education_exam_2_student set
        id_paper_log = '".$id_paperlog."' 
        ,status = '23' 
        ,time_submit = '".date('Y-m-d H:i:s')."'
        ,time_lastupdated = '".date('Y-m-d H:i:s')."'
        ,count_updated = count_updated + 1
        where id = '".$_REQUEST['id']."'",$CONN);
        
        echo json_encode(array('status'=>1,'msg'=>'ok','paperlog'=>$id_paperlog));
    }
}

