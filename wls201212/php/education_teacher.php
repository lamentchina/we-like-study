<?php
class education_teacher {
    
    public function insert($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);    

        //先判断一下学号是否已存在
        $sql = "select * from education_teacher where code = '".$data['code']."';";
        $res = mysql_query($sql,$CONN);
        $temp = mysql_fetch_assoc($res);
        if($temp!=FALSE){
            echo json_encode(array('sql'=>$sql,'state'=>0,'id'=>$temp['id'],'msg'=>'code was already used'));
            return;
        }
        
        //添加一行 人员详细信息 记录
        $sql = "insert into basic_person (name) values ('".$data['code']."');";
        mysql_query($sql,$CONN);  
        $data['id_person'] = mysql_insert_id($CONN);        
        
        //添加一行 系统用户信息 记录
        $sql = "insert into basic_user (username,password,id_person,type) values (".$data['code']."','".md5('888888')."','".$data['id_person']."','3');";
        mysql_query($sql,$CONN);
        $data['id_user'] = mysql_insert_id($CONN); 

        //添加一行 用户所属用户组 记录
        $sql = "insert into basic_group_2_user(username,code_group,remark) values (".$data['code']."','".$data['department']."','teacher insert');";
        mysql_query($sql,$CONN);
        
        //正式添加一条 教师 记录
        $keys = array_keys($data);
        $keys = implode(",",$keys);
        $values = array_values($data);
        $values = implode("','",$values);    
        $sql = "insert into education_teacher (".$keys.") values ('".$values."')";
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1,'id_user'=>$data['id_user'],'id_person'=>$data['id_person']));
    }
    
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'education_teacher__title' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['title'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'education_teacher__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'education_teacher__honor' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['honor'] = $data;		
		
		$sql = "select code,name from basic_department where code like '4%' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
		    if($return=='json'){
    			for($i=0;$i<strlen($temp['code'])-2;$i++){
    		        $temp['name'] = "-".$temp['name'];
    		    }
		    }
			$data[] = $temp;
		}
		$config['department'] = $data;			

		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}
    }	
    
    public function import(){
        tools::checkPermission("120101");
        include_once '../libs/ajaxUpload/php.php';
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("xls");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
       
        self::importExcel($uploader->savePath);
    }    
    
    public function importExcel($path=NULL){
       //TODO
    }   

    public function view(){
        $CONN = tools::conn();    
        $id = $_REQUEST['id'];
        $res = mysql_query( "select * from education_teacher where id = '".$id."' " , $CONN );

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
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $data['time_lastupdated'] = date('Y-M-d');
        $keys = array_keys($data);        
        $sql = "update education_teacher set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        mysql_query("update education_teacher set count_updated = count_updated + 1 where id =".$id,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    } 
    
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
            if(!tools::checkPermission('19',$_REQUEST['username'],$_REQUEST['session'])){
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
        for($i=0;$i<count($search);$i++){
            if($search_keys[$i]=='subject' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_paper.subject = '".$search[$search_keys[$i]]."' ";
            }
            if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_paper.title like '%".$search[$search_keys[$i]]."%' ";
            }		
        }
        $sql_order = ' order by education_teacher.id desc ';
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出

        if($_REQUEST['user_type']=='1'){ 
            //管理员角色          

            $sql = "     
            SELECT
            education_teacher.code,
            education_teacher.name,
            education_teacher.department_code,
            education_teacher.department_name,
            education_teacher.certificate,
            education_teacher.years,
            education_teacher.specialty,
            education_teacher.specialty_code,
            education_teacher.id_person,
            education_teacher.id_user,
            education_teacher.id,
            education_teacher.`type`,
            education_teacher.`status`,
            basic_person.birthday,
            basic_person.idcard,
            basic_person.cardType,
            basic_person.photo,
            basic_person.gender,
            basic_person.degree,
            basic_person.degree_school,
            basic_person.degree_school_code,
            basic_person.politically,
            basic_person.address_birth,
            basic_person.address_birth_code,
            basic_person.cellphone
            FROM
            education_teacher
            Left Join basic_person ON education_teacher.id = basic_person.id
            
    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['address_birth'] = tools::cutString($temp['address_birth'],10);
    			$temp['birthday'] = substr( $temp['birthday'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total 
            FROM
            education_teacher
            Left Join basic_person ON education_teacher.id = basic_person.id
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }     
           
        if($_REQUEST['user_type']=='2'){ 
            //学生角色
            $sql_where .= " and education_exam_2_student.student_id = '".$_REQUEST['userid']."' ";
            $sql_order = ' order by education_exam_2_student.id desc ';

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
            education_exam_2_student.`type`,
            education_exam_2_student.id,
            education_exam_2_student.`status`
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
            if(tools::checkPermission('150102',$_REQUEST['username'],$_REQUEST['session'])){
                $sql_where .= " and education_paper.id_creater_group = '".$_REQUEST['usergroup']."' ";
            }else{                
                $sql_where .= " and education_paper.id_creater = '".$_REQUEST['userid']."' ";
            }

            $sql = "            
            SELECT
    		education_paper.subject AS subjectcode,
    		education_subject.name AS subjectname,
    		education_paper.count_questions,
    		education_paper.title,
    		education_paper.cost,
    		education_paper.author,
    		education_paper.cent,
    		education_paper.id,
    		education_paper.id_creater,
    		education_paper.status,    		
    		education_paper.time_created
    		FROM
    		education_paper
    		left Join education_subject ON education_paper.subject = education_subject.code    		
    		".$sql_where."
    		".$sql_order."
			limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['papertitle'] = tools::cutString($temp['papertitle'],10);
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from
    		education_paper
    		left Join education_subject ON education_paper.subject = education_subject.code ".$sql_where;
            
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
        
    public function delete() {
        $CONN = tools::conn();
        $ids = $_REQUEST['ids'];
		$arr = explode(",", $ids);
		for($i=0;$i<count($arr);$i++){
		    mysql_query("call education_teacher__delete(".$arr[$i].")",$CONN);
		}

        sleep(1);
        echo json_encode(array('state'=>1,'msg'=>'done')); 
    }
}