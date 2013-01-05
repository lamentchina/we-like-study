<?php
/**
 * 试卷部分的语言包文件,参考 language/zh-cn/education/paper.ini
 * */
class education_paper {    
    
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
            tools::checkPermission('1501',$_REQUEST['username'],$_REQUEST['session']);
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
        $sql_order = ' order by education_paper.id desc ';
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['usertype']=='1'){ 
            //管理员角色

            $sql = "            
            SELECT
    		education_paper.subject_code AS subjectcode,
    		education_paper.subject_name AS subjectname,
    		education_paper.count_questions,
    		education_paper.title,
    		education_paper.cost,
    		education_paper.teacher_name as author,
    		education_paper.cent,
    		education_paper.id,
    		education_paper.id_creater,
    		education_paper.status,    		
    		education_paper.time_created
    		FROM
    		education_paper
    		  		
    		".$sql_where."
    		".$sql_order."
			limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['title'] = tools::cutString($temp['title'],10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from
    		education_paper
			".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }        
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['usertype']=='2'){ 
            //学生角色
            $sql_where .= "
    		and education_paper.subject_code in 
            (
                select subject_code from education_subject_2_group_2_teacher where education_subject_2_group_2_teacher.group_code = 
                    (
                        select code from basic_group where id = '".$_REQUEST['user_group']."'
                    )
            )
            ";

            $sql = "     
            SELECT
    		education_paper.subject_code AS subjectcode,
    		education_paper.subject_name AS subjectname,
            education_paper.title,
            education_paper.id,
            education_paper.cost,
            (select max(education_paper_log.mycent) from education_paper_log where education_paper_log.id_creater = '".$_REQUEST['user_id']."' and education_paper_log.paper_id =  education_paper.id ) as mycent
            FROM
            education_paper

    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['title'] = tools::cutString($temp['title'],10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from
    		education_paper
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }
        
        if($_REQUEST['usertype']=='3'){ 
            //教师角色
            if(tools::checkPermission('150102',$_REQUEST['username'],$_REQUEST['session'])){
                $sql_where .= " and education_paper.id_creater_group = '".$_REQUEST['user_group']."' ";
            }else{                
                $sql_where .= " and education_paper.id_creater = '".$_REQUEST['user_id']."' ";
            }

            $sql = "            
            SELECT
    		education_paper.subject_code AS subjectcode,
    		education_paper.subject_name AS subjectname,
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
    		  		
    		".$sql_where."
    		".$sql_order."
			limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['title'] = tools::cutString($temp['title'],10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from
    		education_paper
			".$sql_where;
            
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
    
	/**
	 * 导入一张试卷
	 * 系统中的所有题目,只能通过EXCEL导入的方式添加到系统中
	 * 由系统前端上传到服务器后,服务器通过phpExcel来读取EXCEL中的内容.
	 * EXCEL目前只支持2003版本的文件
	 * EXCEL中必须包含两个SHEET: 试卷 跟 题目
	 * @param $file EXCEL文件路径
	 * */    
    public function import(){
        tools::checkPermission("18");
        include_once '../libs/ajaxUpload/php.php';
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("xls");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
        
        self::importOne($uploader->savePath);
    }  
    
    public function delete(){
        if(!(isset($_REQUEST['ids']))){
            die("no ids");
        }
        $CONN = tools::conn();
        $ids = $_REQUEST['ids'];
		$arr = explode(",", $ids);
		for($i=0;$i<count($arr);$i++){
		    mysql_query("call education_paper__delete(".$arr[$i].")",$CONN);
		}

        sleep(1);
        echo json_encode(array('state'=>1));
    }
    
    public function update(){
        $CONN = tools::conn();
        $arr =  json_decode($_REQUEST['json'],true) ;
        $id = $arr['id'];
        unset($arr['id']);
        $keys = array_keys($arr);
        $sql = "update education_paper set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$arr[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        sleep(1.5);
        echo 1;
    }
    
    public function view(){
        tools::checkPermission("1590");//TODO
        $id = $_REQUEST['id'];
        $CONN = tools::conn();

        $sql = "select 
        	cost
        	,count_questions
        	,subject_name as subject
        	,subject_code as subjectCode
        	,cent
        	,title 
            from education_paper 
            where id = '".$id."'";
        //echo $sql;exit();
        $res = mysql_query($sql,$CONN);
        $data = mysql_fetch_assoc($res);
        echo json_encode($data);
    }
    
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'education_paper__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
        $sql = "select code,value from basic_parameter where reference = 'education_paper__status' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;	
		
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
    
    /**
     * 前端提交了一张试卷,服务端接收提交过来的 用户作答答案 ,并入库,
     * 然后计算有没有做错,然后返回正确答案跟解题思路
     */
    public function submit(){
        if(!tools::checkPermission("1590"))tools::error(array('access wrong'));//TODO
        //判断前端是否传递了足够的参数
        if( (!isset($_REQUEST['json'])) || (!isset($_REQUEST['id'])) || (!isset($_REQUEST['user_id'])) )die('wrong post');
        $CONN = tools::conn();
        
        $id_paperlog = tools::getId("education_paper_log");
        //先生成一份试卷做题日志,并得到日志编号
        $sql = "insert into education_paper_log (
            	id_paper
            	,id_creater
            	,id_creater_group
            	,code_creater_group
            	,id
        	) values (
            	'".$_REQUEST['id']."'
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
            ,'".$_REQUEST['id']."'
            ,'".$id_paperlog."'
            ,'".tools::getId("education_question_log")."'
            ),";            
        }
        $sql = substr($sql,0,strlen($sql)-1);
        //echo $sql;
        mysql_query($sql,$CONN);
        $paper = array(
            'totalCent'=>1
            ,'myTotalCent'=>1
            ,'count_right'=>1
            ,'count_wrong'=>1
            ,'count_giveup'=>1
            ,'count_byTeacher'=>1
        );
        
        //使用存储过程,批改试卷,得到分数
        mysql_query("call education_paper__submit(".$id_paperlog.",@totalCent,@myTotalCent,@count_right,@count_wrong,@count_giveup,@count_byTeacher,@state,@msg)",$CONN);
        $res = mysql_query("select @totalCent,@myTotalCent,@count_right,@count_wrong,@count_giveup,@count_byTeacher,@state,@msg",$CONN);
        $arr = mysql_fetch_array($res);
		if($arr[6]==0)die($arr[7]);
        $paper = array(
            'totalCent' => $arr[0]   ,
            'myTotalCent' => $arr[1],
            'count_right' => $arr[2],
            'count_wrong' => $arr[3],
            'count_giveup' => $arr[4],
            'count_byTeacher' => $arr[5]
        );
        
        //输出试卷的正确答案跟解题思路
        $sql = "
        select        
        education_question.id,        
        education_question.answer,   
        education_question.description     

        from education_question_log left join education_question 
            on education_question_log.id_question = education_question.id             
            where education_question_log.id_paper_log = '".$id_paperlog."' ";
                  
        $res = mysql_query($sql,$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        echo json_encode(array(
            'questions'=>$data
            ,'paper'=>$paper
            ,'sql'=>$sql
        ));
    }
}