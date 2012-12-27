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
                $sql_where .= " and education_paper.subject = '".$search[$search_keys[$i]]."' ";
            }
            if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_paper.title like '%".$search[$search_keys[$i]]."%' ";
            }		
        }
        $sql_order = ' order by education_paper.id desc ';
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['usertype']=='2'){ 
            //学生角色
            $sql_where .= " and education_paper_log.id_creater = '".$_REQUEST['userid']."' ";

            $sql = "     
            SELECT
    		education_paper.subject AS subjectcode,
            education_paper.title as papertitle,
            education_paper.id as paperid,
            education_paper.type,
			education_paper_log.id,
			education_paper_log.time_created,
			education_paper_log.mycent as score_subjective,
			education_paper_log.myCentByTeacher as score_objective,
			education_paper_log.time_created,
			education_paper_log.status
			
            FROM
            education_paper_log
            left Join education_paper ON education_paper_log.id_paper = education_paper.id 
    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['papertitle'] = tools::cutString($temp['papertitle'],10);
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total 
            FROM
            education_paper_log
            left Join education_paper ON education_paper_log.id_paper = education_paper.id 
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }
        
        if($_REQUEST['usertype']=='3'){ 
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
		    echo json_encode($config);
		}else{
		    return $config;
		}		
    } 	
    
    public function view(){
        
        //数据库连接口,在一次服务端访问中,数据库必定只连接一次,而且不会断开
        $CONN = tools::conn();
        $jsonData = array();

        $sql = "select 
        education_paper_log.mycent
        ,education_paper_log.status
        ,education_paper_log.myCentByTeacher
        ,education_paper_log.count_right
        ,education_paper_log.count_wrong
        ,education_paper_log.count_giveup
        ,education_paper_log.time_created
        
        ,education_paper.subject
        ,education_paper.title
        ,education_paper.cost
        ,education_paper.count_questions
        
        from education_paper_log left join education_paper on education_paper_log.id_paper = education_paper.id 
        where education_paper_log.id =".$_REQUEST['id'];
        //echo $sql;
        $res = mysql_query($sql,$CONN);
		$jsonData['paper'] = mysql_fetch_assoc($res);
		
		$sql = "
		select
		
		 education_question_log.myanswer		 
		,education_question.title
		,education_question.type
		,education_question.answer
		,4 as optionlength
		,education_question.option1
		,education_question.option2
		,education_question.option3
		,education_question.option4
		,education_question.option5
		,education_question.option6
		,education_question.option7		
		,education_question.description
		,education_question.cent
		,education_question.layout
		,education_question.id_parent
		,education_question.path_listen
		,education_question.path_image
		,education_question.id_parent
		,education_question.id
		
		from
		education_question_log left join education_question on education_question_log.id_question = education_question.id
		where education_question_log.id_paper_log = ".$_REQUEST['id']."
		order by education_question_log.id_question
		";
		
        $res = mysql_query($sql,$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        $jsonData['question'] = $data;
        
        echo json_encode($jsonData);
    }
}