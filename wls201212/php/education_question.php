<?php
/**
 * 考试系统中的题目
 * 
 * @author wei1224hf@gmail.com
 * @version 201209
 * */
class education_question {
    
    /**
     * 向前端输出此模块对应的配置内容
     * */
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'education_question__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
        $sql = "select code,value from basic_parameter where reference = 'education_question__layout' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['layout'] = $data;	
		
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
            //tools::checkPermission('1501',$_REQUEST['username'],$_REQUEST['session']);
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
        //print_r($search);
        for($i=0;$i<count($search);$i++){
            if($search_keys[$i]=='subject' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_question.subject_code like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_question.title like '%".$search[$search_keys[$i]]."%' ";
            }	
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_question.type = '".$search[$search_keys[$i]]."' ";
            }	            
        }
        $sql_order = ' order by education_question.id desc ';
        if(isset($_REQUEST['sortname'])){
            $sql_order = ' order by education_question.'.$_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['user_type']=='1'){ 
            //管理员角色 
            $sql = "            
SELECT
education_question.type2,
education_question.title,
education_question.`type`,
(select extend4 from basic_memory where extend5 = 'education_question__type' and code = education_question.type) as type_,   
education_question.time_created,
education_question.teacher_name,
education_question.subject_name,
education_question.subject_code,
education_question.teacher_id,
education_question.teacher_code,
education_question.count_used,
education_question.difficulty,
education_question.id_creater,
education_question.id_creater_group,
education_question.id,
education_question.code_creater_group
FROM
education_question
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
    		education_question
			".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }        
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['user_type']=='2'){ 
           
        }
        
        if($_REQUEST['user_type']=='3'){ 
            //教师角色              
            $sql_where .= " and education_question.id_creater = '".$_REQUEST['user_id']."' ";
            $sql = "            
SELECT
education_question.type2,
education_question.title,
education_question.`type`,
(select extend4 from basic_memory where extend5 = 'education_question__type' and code = education_question.type) as type_,   
education_question.time_created,
education_question.teacher_name,
education_question.subject_name,
education_question.subject_code,
education_question.teacher_id,
education_question.teacher_code,
education_question.count_used,
education_question.difficulty,
education_question.id_creater,
education_question.id_creater_group,
education_question.id,
education_question.code_creater_group
FROM
education_question
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
    		education_question
			".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
                      
        }
        if($_REQUEST['user_type']=='9'){            
                      
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
    
     public function insert($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);    

        $keys = array_keys($data);
        $keys = implode(",",$keys);
        $values = array_values($data);
        $values = implode("','",$values);    
        $sql = "insert into education_question (".$keys.") values ('".$values."')";
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }    
	
    public function view(){
        $CONN = tools::conn();    
        $id = $_REQUEST['id'];
        $res = mysql_query( "select * from education_question where id = '".$id."' " , $CONN );

        $data= mysql_fetch_assoc($res);
        header("Content-type:text/json");
        echo json_encode($data);
    } 	
    
    public function update($data=NULL,$retur='json') {
        tools::checkPermission("130111");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $id = $data['id'];
        unset($data['id']);

        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $data['count_updated'] = "count_updated + 1";
        $data['time_lastupdated'] = date("Y-m-d");
        $keys = array_keys($data);        
        $sql = "update education_question set ";
        for($i=0;$i<count($keys);$i++){
            if($keys[$i]=='count_updated'){
                $sql.= $keys[$i]."= ".$data[$keys[$i]]." ,";
                continue;
            }
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }     
	
	/**
	 * 得到一张试卷的所有题目,之前要判断用户的权限与金币剩余
	 * */
	public function getForPaper(){
        tools::checkPermission("1590");//TODO
		$CONN = tools::conn();//数据库连接
		
		$sql = "call education_paper__checkMoney('".$_REQUEST['user_id']."',".$_REQUEST['id'].",@state ,@msg,@money_left )";
		//echo $sql;
		mysql_query($sql ,$CONN);
		$res = mysql_query("select @money_left as money_left,@state as state,@msg as msg",$CONN);
		$arr = mysql_fetch_array($res,MYSQL_ASSOC);
		if($arr['state']==0){
		    echo json_encode($arr);exit();
		}		

		$sql = "select * from education_question where id in( select id_question from education_paper_2_question where id_paper = '".$_REQUEST['id']."'  ) order by id";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
        header("Content-type:text/json");
		echo json_encode(array('Rows'=>$data,'moneyLeft'=>$arr['money_left'],'sql'=>$sql,'state'=>1) );		
	}
	

	public function export(){
	    include_once 'basic_excel.php';
	    basic_excel::export("32931");
	}
	
    public function delete(){
        $CONN = tools::conn();
        $ids = $_REQUEST['ids'];
		$arr = explode(",", $ids);
		for($i=0;$i<count($arr);$i++){
		    mysql_query("call education_question__delete(".$arr[$i].")",$CONN);
		}

        sleep(1);
        echo json_encode(array('state'=>1));
    }	
}