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
     * 得到JSON格式目列表
     * */
    public function select(){
        $CONN = tools::conn();
        
        if(!isset($_REQUEST['page']))$_REQUEST['page'] = 1;
        if(!isset($_REQUEST['pagesize']))$_REQUEST['pagesize'] = 15;
		
		$where = " where 1=1 ";
		$orderby = " ORDER BY education_question.time_created ASC ";
        //有查询条件
		if(isset($_REQUEST['searchJson'])){
			$search = json_decode($_REQUEST['searchJson'],true);
			//print_r($search);
			$where = " where 1=1 ";
			if(trim($search['subject'])!=''){
				$where .= " and education_question.subject = '".$search['subject']."' ";
			}
			if(trim($search['type'])!=''){
				$where .= " and education_question.type = '".$search['type']."' ";
			}
			if(trim($search['title'])!=''){
				$where .= " and education_question.title like '%".$search['title']."%' ";
			}	
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$orderby = " order by education_question.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}
		
        $sql = " 
		SELECT
		
		education_question.*,
		education_subject.name AS subjectname
		
		FROM
		education_question
		left Join education_subject ON education_question.subject = education_subject.code
		
		".$where."
		".$orderby."
		limit ".($_REQUEST['page']-1)*$_REQUEST['pagesize'].",".$_REQUEST['pagesize']."
		";
		
        $res = mysql_query($sql,$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
			$temp['time_created'] = substr($temp['time_created'],0,10);
			if(strlen($temp['title']) >= 16 ){
			    $temp['title'] = tools::cut_str($temp['title'], 8);
			}			
            $data[] = $temp;
        }
        
        $sql2 = "select count(*) as total from education_question ".$where;
        $res = mysql_query($sql2,$CONN);
        $total = 0;
        while($temp = mysql_fetch_assoc($res)){
            $total = $temp['total'];
        }
        $data = array("Rows"=>$data,"Total"=>$total,'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)));
        echo json_encode($data);
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