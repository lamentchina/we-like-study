<?php
/**
 * 错题本
 * 
 * 无法直接访问使用,必须先访问 myApp.php,在从myApp.php中引入此文件
 * */
class education_question_log_wrongs {
	
    public function grid(){
        $CONN = tools::conn();
		
		$where = " where education_question_log_wrongs.id_creater = '".$_REQUEST['user_id']."' ";
		$orderby = " ORDER BY education_question_log_wrongs.id ";
        //有查询条件
		if(isset($_REQUEST['search'])){
			$search = json_decode($_REQUEST['search'],true);
			$where = " where 1=1 ";
			if(isset($search['subject']) && trim($search['subject'])!=''){
				$where .= " and education_question_log_wrongs.subject_code like '%".$search['subject']."%' ";
			}	
			if(isset($search['title']) && trim($search['title'])!=''){
				$where .= " and education_question_log_wrongs.paper_title like '%".$search['title']."%' ";
			}				
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$orderby = " order by education_question_log_wrongs.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}
		
        $sql = " 
        SELECT
        education_question_log_wrongs.paper_title,
        education_question_log_wrongs.question_title,
        education_question_log_wrongs.question_id,
        education_question_log_wrongs.paper_id,
        education_question_log_wrongs.subject_name,
        education_question_log_wrongs.subject_code,
        education_question_log_wrongs.time_created,
        education_question_log_wrongs.count_wrong,
        education_question_log_wrongs.count_right
        FROM
        education_question_log_wrongs
        
		".$where."
		".$orderby."
		limit ".($_REQUEST['page']-1)*$_REQUEST['pagesize'].",".$_REQUEST['pagesize']."
		";
		
        $res = mysql_query($sql,$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
			$temp['time_created'] = substr($temp['time_created'],0,10);
            $data[] = $temp;
        }
        
        $sql2 = "select count(*) as total from education_question_log_wrongs ".$where;
        $res = mysql_query($sql2,$CONN);
        $total = 0;
        while($temp = mysql_fetch_assoc($res)){
            $total = $temp['total'];
        }
        $data = array("Rows"=>$data,"Total"=>$total,'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)));
        header("Content-type:text/json");
        echo json_encode($data);
    }	
    
    public function getForPaper() {
		$CONN = tools::conn();//数据库连接

		$sql = "
        SELECT
        education_question.title,
        education_question.optionlength,
        education_question.option1,
        education_question.option2,
        education_question.option3,
        education_question.option4,
        education_question.option5,
        education_question.option6,
        education_question.option7,
        education_question.layout,
        education_question.id_parent,
        education_question.cent,
        education_question.path_listen,
        education_question.path_image,
        education_question.id,
        education_question.`type`
        FROM
        education_question_log_wrongs
        Left Join education_question ON education_question_log_wrongs.question_id = education_question.id
        where education_question_log_wrongs.id_creater = '".$_REQUEST['user_id']."'
        order by education_question.id
        limit 50        
		";
		$res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
        $data = array("Rows"=>$data,'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)));
        
        header("Content-type:text/json");        
        echo json_encode($data);
    }
    
    public function submit(){
        $CONN = tools::conn();
        
        
        $id_paperlog = tools::getId("education_paper_log");
        
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
    
        for($i=0;$i<count($arr);$i++){
            $sql .= " (
            '".$arr[$i]['id']."'
            ,'".$arr[$i]['myanswer']."'
            ,'".$_REQUEST['user_id']."'
            ,'".$_REQUEST['group_id']."'
            ,'".$_REQUEST['group_code']."'
            ,'0'
            ,'".$id_paperlog."'
            ,'".tools::getId("education_question_log")."'
            ),";            
        }
        $sql = substr($sql,0,strlen($sql)-1);
        //echo $sql;
        mysql_query($sql,$CONN);
               
        //使用存储过程,批改试卷,得到分数
        $res = mysql_query("call education_question_log_wrongs__submit(".$id_paperlog.",@state,@msg)",$CONN);
        
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        header("Content-type:text/json"); 
        echo json_encode(array(
            'questions'=>$data
            ,'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql))
        ));
    }    
}