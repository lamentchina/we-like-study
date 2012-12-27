<?
/**
 * 科目-用户组-教师
 * 学生在访问试卷列表的时候,需要先判断其有哪些科目访问权限
 * 教师可以看到所有的试卷
 * 
 * @author wei1224hf@gmail.com
 * @version 201211
 * */
class education_subject_2_group_2_teacher {
	
	public function getGrid(){
	    tools::checkPermission("1201");
		$CONN = tools::conn();
		
		$sql = "select name, code  , type , count_papers,count_questions , remark ,status from education_subject_2_group_2_teacher order by code  ;";
		$res = mysql_query($sql,$CONN);
		
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		
        $data = array("Rows"=>$data);
        echo json_encode($data);
	}
	
	
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
		
		$sql = "select code,value from basic_parameter where reference = 'education_subject_2_group_2_teacher__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;

		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}
    }		
}