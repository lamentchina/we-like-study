<?php
/**
 * 试卷部分的语言包文件,参考 language/zh-cn/education/paper.ini
 * */
class education_paper_log {
    
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
                $sql_where .= " and education_paper_log.subject_code = '".$search[$search_keys[$i]]."' ";
            }
            if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_paper_log.paper_title like '%".$search[$search_keys[$i]]."%' ";
            }		
        }
        $sql_order = ' order by education_paper_log.id desc ';
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['usertype']=='2'){ 
            //学生角色
            $sql_where .= " and education_paper_log.id_creater = '".$_REQUEST['userid']."' ";

            $sql = "     
            SELECT
            education_paper_log.paper_title,
            education_paper_log.mycent,
            education_paper_log.cent,
            education_paper_log.count_right,
            education_paper_log.count_wrong,
            education_paper_log.count_giveup,
            education_paper_log.id,
            education_paper_log.paper_id,
            education_paper_log.`type`,
            education_paper_log.subject_name,
            education_paper_log.subject_code,
            education_paper_log.teacher_id,
            education_paper_log.teacher_name,
            education_paper_log.teacher_code,
            education_paper_log.student_id,
            education_paper_log.student_name,
            education_paper_log.student_code,
            education_paper_log.time_created
            FROM
            education_paper_log
            
    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['paper_title'] = tools::cutString($temp['paper_title'],10);
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total 
            FROM
            education_paper_log            
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }        
        if($_REQUEST['usertype']=='3'){ 
            //教师角色
            $sql_where .= " and education_paper_log.teacher_id = '".$_REQUEST['userid']."' ";

            $sql = "            
            SELECT
            education_paper_log.paper_title,
            education_paper_log.mycent,
            education_paper_log.cent,
            education_paper_log.count_right,
            education_paper_log.count_wrong,
            education_paper_log.count_giveup,
            education_paper_log.id,
            education_paper_log.paper_id,
            education_paper_log.`type`,
            education_paper_log.subject_name,
            education_paper_log.subject_code,
            education_paper_log.teacher_id,
            education_paper_log.teacher_name,
            education_paper_log.teacher_code,
            education_paper_log.student_id,
            education_paper_log.student_name,
            education_paper_log.student_code,
            education_paper_log.time_created
            FROM
            education_paper_log  		
    		".$sql_where."
    		".$sql_order."
			limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['paper_title'] = tools::cutString($temp['paper_title'],10);
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from education_paper ".$sql_where;
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);     
        }
        if($_REQUEST['usertype']=='1'){ 
            //管理员角色
            
            $sql = "            
            SELECT
            education_paper_log.paper_title,
            education_paper_log.mycent,
            education_paper_log.cent,
            education_paper_log.count_right,
            education_paper_log.count_wrong,
            education_paper_log.count_giveup,
            education_paper_log.id,
            education_paper_log.paper_id,
            education_paper_log.`type`,
            education_paper_log.subject_name,
            education_paper_log.subject_code,
            education_paper_log.teacher_id,
            education_paper_log.teacher_name,
            education_paper_log.teacher_code,
            education_paper_log.student_id,
            education_paper_log.student_name,
            education_paper_log.student_code,
            education_paper_log.time_created
            FROM
            education_paper_log  		
    		".$sql_where."
    		".$sql_order."
			limit ".(($page-1)*$pagesize).", ".$pagesize;

            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['paper_title'] = tools::cutString($temp['paper_title'],10);
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total from education_paper ".$sql_where;
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
        header("Content-type:text/json");
        echo json_encode($returnData);
    }     
    
    /**
     * 读取此模块对应的配置文件
     * 包括:班级列表,做题记录类型,做题记录批改状态
     * */
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
		
        $sql = "select code,value from basic_parameter where reference = 'education_paper_log__status' order by code";
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

        $sql = "select code,name as value from basic_department order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['department'] = $data;			

		if($return=='json'){
		    header("Content-type:text/json");
		    echo json_encode($config);
		}else{
		    return $config;
		}		
    } 	
    
    public function view(){
        tools::checkPermission("1590");//TODO
        $returnData = array();
        $id = $_REQUEST['id'];
        $CONN = tools::conn();

        $sql = " 
        SELECT
        education_paper_log.paper_title,
        education_paper_log.mycent,
        education_paper_log.cent,
        education_paper_log.count_right,
        education_paper_log.count_wrong,
        education_paper_log.count_giveup,
        education_paper_log.id,
        education_paper_log.paper_id,
        education_paper_log.`type`,
        education_paper_log.subject_name,
        education_paper_log.subject_code,
        education_paper_log.teacher_id,
        education_paper_log.teacher_name,
        education_paper_log.teacher_code,
        education_paper_log.student_id,
        education_paper_log.student_name,
        education_paper_log.student_code,
        education_paper_log.time_created
        FROM
        education_paper_log        
        where id = '".$id."'";
        //echo $sql;exit();
        $res = mysql_query($sql,$CONN);
        $data = mysql_fetch_assoc($res);
        $returnData['paperlog'] = $data;
        
        //输出试卷的正确答案跟解题思路
        $sql = "
        SELECT
        education_question_log.id_paper,
        education_question_log.id_paper_log,
        education_question_log.id_question,
        education_question_log.myanswer,
        education_question.id,
        education_question.title,
        education_question.answer,
        education_question.optionlength,
        education_question.option1,
        education_question.option2,
        education_question.option3,
        education_question.option4,
        education_question.option5,
        education_question.option6,
        education_question.option7,
        education_question.description,
        education_question.layout,
        education_question.cent,
        education_question.id_parent,
        education_question.path_listen,
        education_question.path_image,
        education_question.`type`
        FROM
        education_question_log
        Left Join education_question ON education_question_log.id_question = education_question.id

            where education_question_log.id_paper_log = '".$id."'
            order by education_question.id ";
                  
        $res = mysql_query($sql,$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        $returnData['question'] = $data;
        header("Content-type:text/json");
        $returnData['sql'] = preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql));
        echo json_encode($returnData);
    }
}