<?php
/**
 * 错题本
 * 
 * 无法直接访问使用,必须先访问 myApp.php,在从myApp.php中引入此文件
 * */
class education_question_log_wrongs {
	
    public function getGrid(){
        $CONN = tools::conn();
		
		$where = " where 1=1 ";
		$orderby = " ORDER BY education_paper.time_created ASC ";
        //有查询条件
		if(isset($_REQUEST['searchJson'])){
			$search = json_decode($_REQUEST['searchJson'],true);
			$where = " where 1=1 ";
			if(trim($search['subject'])!=''){
				$where .= " and education_paper.subject = '".$search['subject']."' ";
			}
			if(trim($search['money'])!=''){
			    
				$where .= " and education_paper.cost < '".$search['money']."' ";
			}
			if(trim($search['name'])!=''){
				$where .= " and education_paper.name like '%".$search['name']."%' ";
			}			
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$orderby = " order by education_paper.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}
		
        $sql = " 
		SELECT
		education_paper.subject AS subjectcode,
		education_paper.count_questions,
		education_paper.name,
		education_paper.cost,
		education_paper.author,
		education_paper.id_teacher,
		education_paper.cent,
		education_paper.id,
		education_paper.id_creater,
		education_paper.`status`,
		education_subject.name AS subjectname,
		education_paper.time_created
		FROM
		education_paper
		Inner Join education_subject ON education_paper.subject = education_subject.code
		
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
        
        $sql2 = "select count(*) as total from education_paper".$where;
        $res = mysql_query($sql2,$CONN);
        $total = 0;
        while($temp = mysql_fetch_assoc($res)){
            $total = $temp['total'];
        }
        $data = array("Rows"=>$data,"Total"=>$total,'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)));
        echo json_encode($data);
    }
	
}
?>