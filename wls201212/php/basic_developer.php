<?php
/**
 * TODO 开发者所需文件,发布时需要删掉
 * 依赖 file/developer 中的所有文件
 * */
class basic_developer {

    public function importGB2260() {
	    include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load("../file/developer/GB2260-2007.xls");
		
		$CONN = tools::conn();
		$currentSheet = $obj->getSheetByName("行政区划代码");
		$allRow = $currentSheet->getHighestRow();
		
		mysql_query("delete from basic_parameter where reference='GB2260'",$CONN);
		$sql = "INSERT INTO basic_parameter(code,value,reference) VALUES ";
        for($i=6;$i<=$allRow;$i++){
            $code = $currentSheet->getCell("A".$i)->getValue();
            if($code=="")continue;
            //$code = str_replace("00", "", $code);
            $sql .="  (
        	'".$code."'
        	,'".$code = $currentSheet->getCell("B".$i)->getValue()."'
        	,'GB2260'
        	),";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        mysql_query($sql,$CONN);
    }   
    
    public function importEDUBKZYML() {
	    include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load("../file/developer/BKZYML.XLS");
		
		$CONN = tools::conn();
		$currentSheet = $obj->getSheetByName("Sheet1");
		$allRow = $currentSheet->getHighestRow();
		
		mysql_query("delete from basic_parameter where reference='EDU-BKZYML'",$CONN);
		$sql = "INSERT INTO basic_parameter(code,value,reference,remark) VALUES ";
        for($i=1;$i<=$allRow;$i++){
            $code = $currentSheet->getCell("A".$i)->getValue();
            if($code=="")continue;
            //$code = str_replace("00", "", $code);
            $sql .="  (
        	'".$code."'
        	,'".$code = $currentSheet->getCell("B".$i)->getValue()."'
        	,'specialty'
        	,'教育 行业标准 本科专业目录 ,http://www.stats.edu.cn/tjbz/bkzyml.htm'
        	),";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        mysql_query($sql,$CONN);
    }  

    public function importParameter(){
        include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load("../file/download/highschool/basic_parameter.xls");
		
		$CONN = tools::conn();
		mysql_query("truncate table basic_parameter ;",$CONN);        
		$currentSheet = $obj->getSheetByName("data");
		$allRow = $currentSheet->getHighestRow();
		
		$sql = "INSERT INTO basic_parameter(
			code,value,reference,remark
			) VALUES ";
        for($i=2;$i<=$allRow;$i++){
            $code = $currentSheet->getCell("A".$i)->getValue();
            if($code=="")continue;
            //$code = str_replace("00", "", $code);
            $sql .="  (
        	'".$code."'
        	,'".$currentSheet->getCell("B".$i)->getValue()."'
        	,'".$currentSheet->getCell("C".$i)->getValue()."'
        	,'".$currentSheet->getCell("D".$i)->getValue()."'
        	),";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        mysql_query($sql,$CONN);
    }

    public function importJBGDXXHKYJG() {
	    include_once config::$phpexcel.'PHPExcel.php';
		include_once config::$phpexcel.'PHPExcel/IOFactory.php';
		include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$obj = $PHPReader->load("../file/developer/JB-GDXXHKYJG.xls");
		
		$CONN = tools::conn();
		$currentSheet = $obj->getSheetByName("学校代码");
		$allRow = $currentSheet->getHighestRow();
		
		mysql_query("delete from basic_parameter where reference='JB-GDXXHKYJG' ;",$CONN);
		$sql = "INSERT INTO basic_parameter(
			code,value,extend1,extend2,extend3,reference,remark
			) VALUES ";
        for($i=6;$i<=$allRow;$i++){
            $code = $currentSheet->getCell("A".$i)->getValue();
            if($code=="")continue;
            //$code = str_replace("00", "", $code);
            $sql .="  (
        	'".$code."'
        	,'".$currentSheet->getCell("B".$i)->getValue()."'
        	,'".$currentSheet->getCell("C".$i)->getValue()."'
        	,'".$currentSheet->getCell("D".$i)->getValue()."'
        	,'".$currentSheet->getCell("F".$i)->getValue()."'
        	,'JB-GDXXHKYJG'
        	,'学校代码'
        	),";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        mysql_query($sql,$CONN);
    }     

    public function formatQQColledge() {
        $CONN = tools::conn();
        if(isset($_REQUEST['clean'])){
            mysql_query("delete from basic_parameter where reference = 'api.pengyou.com';",$CONN);
            return;
        }
        $path = "http://api.pengyou.com/json.php?cb=__i_3&mod=school&act=selector&schooltype=0&country=0&province=".$_REQUEST['GB2260']."&g_tk=1417101710";
        echo $path;
        $content = file( $path );
        //$content = file( "../file/developer/qq_pengyou_colledge.txt" );
		$content = implode("\n", $content);
		$arr = explode( "choose_school(" ,$content );
		$str = "[";
		for($i=1;$i<count($arr);$i++){
		    $pos = strpos($arr[$i],")");
		    $arr[$i] = substr($arr[$i], 0,$pos);
		    $arr[$i] = str_replace('\'', '', $arr[$i]);
		    $str .= '"'.$arr[$i].'",';
		}
		$str .= "1]";
		$arr2 = json_decode($str,TRUE);
		print_r($arr2);
		//return;
		$sql = "INSERT INTO basic_parameter(code,value,extend1,reference) VALUES ";
        for($i=0;$i<=count($arr2)-2;$i++){
            $item = $arr2[$i];
            $arr = explode( "," ,$item );
            $sql .="  (
        	 '".$arr[0]."'
        	,'".$arr[1]."'
        	,'".$_REQUEST['GB2260']."'
        	,'api.pengyou.com'
        	),";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        mysql_query($sql,$CONN);
    }
    
    public function giveMeAll() {
        $CONN = tools::conn();
        $res = mysql_query("select value from basic_config where code = 'develop' ;",$CONN);
        $arr = mysql_fetch_array($res);
        //print_r($arr);
        if($arr['value']<>'1')exit();
        $arr = array();    
	    $sql = "select code,name,icon,0 as cost from basic_permission order by code";
	    $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$len = strlen($temp['code']);
			if($len==2){
				$data[] = $temp;
			}else if($len==4){
				$data[count($data)-1]['children'][] = $temp;
			}else if($len==6){
				$data[count($data)-1]['children'][count($data[count($data)-1]['children'])-1]['children'][] = $temp;
			}
		}
		$arr['permission'] = $data;
        $arr['il8n'] = tools::getLanguage();     
        
        echo json_encode($arr);
        //echo 1;
    }
   
    public function importAll() {
        $CONN = tools::conn();
        require_once 'basic_excel.php';

        
        mysql_query(" truncate table basic_excel ;",$CONN);
        mysql_query(" truncate table basic_memory ;",$CONN);
        mysql_query(" truncate table basic_permission ;",$CONN);
        mysql_query(" truncate table basic_group ;",$CONN);
        mysql_query(" truncate table basic_department ;",$CONN);    
        mysql_query(" truncate table basic_group_2_user ;",$CONN);     
        mysql_query(" truncate table basic_group_2_permission ;",$CONN);
        
        mysql_query(" truncate table basic_user ;",$CONN);
        mysql_query(" truncate table basic_group_2_user ;",$CONN);    
        mysql_query(" truncate table basic_person ;",$CONN);  
        mysql_query(" truncate table education_student ;",$CONN);
        mysql_query(" truncate table education_teacher ;",$CONN);   
        mysql_query(" truncate table education_subject ;",$CONN);       
                      
        mysql_query("call basic_memory__init() ;",$CONN);
        
        require_once 'basic_memory.php';
        $obj = new basic_memory();
        $obj->il8n();        
        
        $_REQUEST['id_user'] = 1;
        
		basic_excel::import('../file/download/basic_permission.xls');
		echo " basic_permission ".basic_excel::$guid;
		mysql_query("call basic_permission__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);
 
		basic_excel::import('../file/download/basic_group.xls');
		echo " basic_group ".basic_excel::$guid."---------";
		mysql_query("call basic_group__import('".basic_excel::$guid."',@state2,@msg2,@ids2)",$CONN);
		$res = mysql_query("select @state2,@msg2,@ids2",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);		
		
        //导入权限对用户组
		basic_excel::import('../file/download/basic_group_2_permission.xls"');
		echo " basic_group_2_permission ".basic_excel::$guid;
		mysql_query("call basic_group_2_permission__import('".basic_excel::$guid."',@state,@msg)",$CONN);
		$res = mysql_query("select @state,@msg",$CONN);  	
		$data = mysql_fetch_assoc($res);
		print_r($data);			
        
        //导入用户  
		basic_excel::import('../file/download/basic_user.xls');
		echo " basic_user ".basic_excel::$guid;
		mysql_query("call basic_user__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);		
	
        //导入科目
		basic_excel::import('../file/download/education_subject.xls');
		echo " education_subject ".basic_excel::$guid;
		mysql_query("call education_subject__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);	
		 																
        //导入教师
		basic_excel::import('../file/download/education_teacher.xls');
		echo " education_teacher ".basic_excel::$guid;
		mysql_query("call education_teacher__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);	
		
        //导入学生
		basic_excel::import('../file/download/education_student.xls');
		echo " education_student ".basic_excel::$guid;
		mysql_query("call education_student__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);	

		//导入 科目-教师-班级 对应关系
		basic_excel::import('../file/download/education_subject_2_group_2_teacher.xls');
		echo " education_student ".basic_excel::$guid;
		mysql_query("call education_subject_2_group_2_teacher__import('".basic_excel::$guid."',@state,@msg)",$CONN);  
		$res = mysql_query("select @state,@msg",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);			
    }  
    
    /**
     * 导入很多很多的试卷
     * */
    public function importAll3() {
        $_REQUEST['id_user'] = 1;
        $CONN = tools::conn();
        require_once 'basic_excel.php';
		basic_excel::import('../file/download/education_subject_2_group_2_teacher.xls');
		echo basic_excel::$guid;
		//mysql_query("call education_teacher__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  	
    }

    /**
     * 导入很多很多的试卷
     * */
    public function importAll2() {
        $CONN = tools::conn();
        require_once 'basic_excel.php';
        $_REQUEST['id_user'] = 4;
        mysql_query(" truncate table education_paper ;",$CONN);
        mysql_query(" truncate table education_question ;",$CONN);
        mysql_query(" truncate table education_paper_log ;",$CONN);
        mysql_query(" truncate table education_question_log ;",$CONN);    
        mysql_query(" truncate table education_question_log_wrongs ;",$CONN);         
        mysql_query("call basic_user__login('TC0000X','".md5(md5("admin").date("H"))."');",$CONN);
        
		basic_excel::import('../file/download/education_paper2.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  	
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 	
        basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  	
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 	
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  	
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN); 
		basic_excel::import('../file/download/education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);		        
    }
    
    public function importTest(){
        if(!isset($_REQUEST['key']))exit();
        $CONN = tools::conn();
        $sql = "call basic_permission__import('".$_REQUEST['key']."',@state,@msg,@ids);";
        //$sql = "call ccc(@xxx);";
        echo $sql;
        try{
            mysql_query($sql,$CONN);
        }catch(Exception $e){
            echo $e;
        }
        echo mysql_errno().mysql_error();
		$res = mysql_query("select @state as c",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);		
    }
    
    public function importAll4Highschool() {
       
        $CONN = tools::conn();
        
        require_once 'basic_excel.php';
        $this->importParameter();

        mysql_query("truncate table basic_excel ;",$CONN);        
        mysql_query("truncate table basic_memory ;",$CONN);        
        mysql_query("truncate table basic_permission ;",$CONN);        
        mysql_query("truncate table basic_group ;",$CONN);        
        mysql_query("truncate table basic_department ;",$CONN);            
        mysql_query("truncate table basic_group_2_user ;",$CONN);        
        mysql_query("truncate table basic_group_2_permission ;",$CONN);        
        mysql_query("truncate table basic_user ;",$CONN);        
        mysql_query("truncate table basic_user_session ;",$CONN);                 
        mysql_query("truncate table basic_group_2_user ;",$CONN);          
        mysql_query("truncate table basic_person ;",$CONN);        
        mysql_query("truncate table education_student ;",$CONN);        
        mysql_query("truncate table education_teacher ;",$CONN);        
        mysql_query("truncate table education_subject ;",$CONN);        
        mysql_query("truncate table education_subject_2_group_2_teacher ;",$CONN);          
                     
        mysql_query("call basic_memory__init() ;",$CONN);
        
        require_once 'basic_memory.php';
        $obj = new basic_memory();
        $obj->il8n();        
        $_REQUEST['userid'] = 1;
        
        
		basic_excel::import('../file/download/highschool/basic_permission.xls');
		echo " basic_permission ".basic_excel::$guid; 	
		mysql_query("call basic_permission__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);
		

		basic_excel::import('../file/download/highschool/basic_group2.xls');
		echo " basic_group ".basic_excel::$guid."---------";
		mysql_query("call basic_group__import('".basic_excel::$guid."',@state2,@msg2,@ids2)",$CONN);
		$res = mysql_query("select @state2,@msg2,@ids2",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);
		
        //导入权限对用户组
		basic_excel::import('../file/download/highschool/basic_group_2_permission2.xls');
		echo " basic_group_2_permission ".basic_excel::$guid;	
		mysql_query("call basic_group_2_permission__import('".basic_excel::$guid."',@state,@msg)",$CONN);
		$res = mysql_query("select @state,@msg",$CONN);  	
		$data = mysql_fetch_assoc($res);	
		print_r($data);		
		
        
        //导入用户  
		basic_excel::import('../file/download/highschool/basic_user.xls');
		echo " basic_user ".basic_excel::$guid;
		mysql_query("call basic_user__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data); 
	
        //导入科目
		basic_excel::import('../file/download/highschool/education_subject2.xls');
		echo " education_subject ".basic_excel::$guid;
		mysql_query("call education_subject__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);	
		 																		
        //导入学生
		basic_excel::import('../file/download/highschool/education_student2.xls');
		echo " education_student ".basic_excel::$guid;
		mysql_query("call education_student__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);	
		
		/*
		$this->importEDUBKZYML();
		$this->importGB2260();
		$this->importJBGDXXHKYJG();
		*/
		
        //导入教师
		basic_excel::import('../file/download/highschool/education_teacher2.xls');
		echo " education_teacher ".basic_excel::$guid;
		mysql_query("call education_teacher__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  
		$res = mysql_query("select @state,@msg,@ids",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);			

		//导入 科目-教师-班级 对应关系
		basic_excel::import('../file/download/highschool/education_subject_2_group_2_teacher2.xls');
		echo " education_subject_2_group_2_teacher ".basic_excel::$guid;
		mysql_query("call education_subject_2_group_2_teacher__import('".basic_excel::$guid."',@state,@msg)",$CONN);  
		$res = mysql_query("select @state,@msg",$CONN);
		$data = mysql_fetch_assoc($res);
		print_r($data);	
        
		//mysql_query("call education_paper__init4test(21)",$CONN);  
		//mysql_query("call education_paper_log__int4test(20,@state,@msg)",$CONN);  
		//模拟6个月
		mysql_query("call education_exam__init4test(2,@msg,@state)",$CONN);  
		mysql_query("call education_exam_2_student__init4test(140)",$CONN);		
		mysql_query("call education_paper_log__int4test(100)",$CONN);		
    }     

    public function importAll4() {
        $CONN = tools::conn();
        require_once 'basic_excel.php';
        $_REQUEST['id_user'] = 4;
        mysql_query(" truncate table education_paper ;",$CONN);
        mysql_query(" truncate table education_question ;",$CONN);
        mysql_query(" truncate table education_paper_log ;",$CONN);
        mysql_query(" truncate table education_question_log ;",$CONN);    
        mysql_query(" truncate table education_question_log_wrongs ;",$CONN);         
        mysql_query("call basic_user__login('TC0000X','".md5(md5("admin").date("H"))."');",$CONN);
        
		basic_excel::import('../file/download/highschool/A1_education_paper.xls');
		mysql_query("call education_paper__import('".basic_excel::$guid."',@state,@msg,@ids)",$CONN);  	
    }	
}