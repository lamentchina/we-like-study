<?php
class exam_paper {
        
    public static function grid($search=NULL,$page=NULL,$pagesize=NULL){
	    if (!basic_user::checkPermission("40")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		        
        if( (!isset($_REQUEST['search'])) || (!isset($_REQUEST['page'])) || (!isset($_REQUEST['pagesize'])) ){
            return array(
    		    'status'=>'1'
    		    ,'msg'=>'ok'
    		);
        }
        $search=$_REQUEST['search'];
        $page=$_REQUEST['page'];
        $pagesize=$_REQUEST['pagesize'];
        
        //数据库连接口,在一次服务端访问中,数据库必定只连接一次,而且不会断开
        $conn = tools::getConn();
        
        //列表查询下,查询条件必定是SQL拼凑的
        $sql_where = " where exam_paper.type = '20' ";
        
        $search=json_decode2($search,true);
        $search_keys = array_keys($search);
        for($i=0;$i<count($search);$i++){
            if($search_keys[$i]=='title' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and title like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='subject_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and subject_code = '".$search[$search_keys[$i]]."' ";
            }    
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and type = '".$search[$search_keys[$i]]."' ";
            }    
            if($search_keys[$i]=='status' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and status = '".$search[$search_keys[$i]]."' ";
            }                                   	
        }
        $sql_order = ' order by time_created desc ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by ".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
        $data = array();
        
        //根据不同的用户角色,会有不同的列输出
        if(basic_user::$userType=='10'){ 
            //系统角色
            //如果不是管理员,那肯定是访客,只能看到 免费试卷,金币为0 的          
            if(basic_user::$userGroup!='10'){
                $sql_where .= " and exam_paper.cost = 0 ";
            }
            $sql = tools::getConfigItem("exam_paper__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }if(basic_user::$userType=='20'){ 
            //学生角色
            $sql_where .= " and subject_code in (select subject_code from exam_subject_2_group where group_code = '".basic_user::$userGroup."' )";          
            $sql = tools::getConfigItem("exam_paper__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }    
        if(basic_user::$userType=='30'){ 
            //教师角色
            $sql_where .= " and exam_paper.creater_code = '".$_REQUEST['executor']."'";          
            $sql = tools::getConfigItem("exam_paper__grid");            
            $sql .= $sql_where." ".$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
            
            $res = mysql_query($sql,$conn);            
            while($temp = mysql_fetch_assoc($res)){
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total FROM exam_paper ".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        } 
        $returnData = array(
            'Rows'=>$data,
            'Total'=>$total['total'],
        	'sql'=>$sql
        );

        return $returnData;
    }
        
	public static function remove($ids=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4022")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	    
	    if($ids==NULL)$ids = $_REQUEST['ids'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
		$conn = tools::getConn();
		$ids = explode(",", $ids);
		for($i=0;$i<count($ids);$i++){
		    $sql = "delete from exam_paper where id = '".$ids[$i]."' ;";
		    mysql_query($sql,$conn);
		    $sql = "delete from exam_question where paper_id = '".$ids[$i]."' ;";
		    mysql_query($sql,$conn);		    
		}
		
		return  array(
			'status'=>1
		    ,'msg'=>'OK'
		);
	}
	
    public static function loadConfig() {
        $conn = tools::getConn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'exam_paper__type' and code not in ('1','9')  order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		basic_user::getSession($_REQUEST['executor'],$_REQUEST['session']);
		if(basic_user::$userType=='10'||basic_user::$userType=='30'){
            $sql = "select code,extend4 as value from basic_memory where type = '4' and extend5 = 'exam_subject__code' order by code";
		}
		if(basic_user::$userType=='20'){
            $sql = "select code,name as value from exam_subject where code in (select subject_code from exam_subject_2_group where group_code = '".basic_user::$userGroup."'); ";
		}		

        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
		    $len = strlen($temp['code']);
		    for($i=1;$i<$len/2;$i++){
		        $temp['value'] = "--".$temp['value'];
		    }
			$data[] = $temp;
		}
		$config['exam_subject__code'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'exam_paper__status' order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;
		
        $sql = "select code,value from basic_parameter where reference = 'exam_question__type' and code not in ('1','9')  order by code";
        $res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['exam_question__type'] = $data;		

	    return $config;		
	}  
	
	public static function questions($paper_id=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4090")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($paper_id==NULL) $paper_id = $_REQUEST['paper_id'];
	    if($executor==NULL) $executor = $_REQUEST['executor'];
        $conn = tools::getConn();
	    
	    $data = exam_paper::view($paper_id);
	    $cost = $data['data']['cost'];
	    
	    if($cost>0){
    	    //扣除金币
			$sql = "";
    	    if(tools::$systemType=='DZX'){
    	        $pfx = tools::$dzxConfig['db']['1']['tablepre'];
    	        $sql = " update ".$pfx."common_member_count set extcredits2 = extcredits2 - ".$cost." where extcredits2 >= ".$cost." and uid in ( select uid from ".$pfx."common_member where username = '".$executor."'   )";    
    	    }else{
                $sql = " update basic_user set money = money - ".$cost." , credits = credits + 3 where username = '".$executor."' and money >= ".$cost."  ;";
    	    }
            mysql_query($sql,$conn);
            $count = mysql_affected_rows($conn);
            if($count==0){
                return array(
                    'status'=>'2'
                    ,'msg'=>'no money'
					,'error'=>mysql_error()
					,'sql'=>$sql
                );
            }
	    }
        
        $sql = tools::getConfigItem("exam_paper__questions");            
        $sql = str_replace("__paper_id__", $paper_id, $sql);
        
        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }

        return array(
             'data'=>$data
            ,'msg'=>'ok'
            ,'status'=>'1'
        ); 
	}
	
	public static function view($id=NULL){
	    if($id==NULL) $id = $_REQUEST['id'];
	    $conn = tools::getConn();
        $sql = tools::getConfigItem("exam_paper__view");            
        $sql = str_replace("__id__", $id, $sql);

        $res = mysql_query($sql,$conn);
        $data = mysql_fetch_assoc($res);

        return array(
            'data'=>$data
            ,'msg'=>'ok'
            ,'status'=>'1'
           
        ); 
	}
	
	public static function modify($data=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4123")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }	
	    if($data==NULL)$data = $_REQUEST['data'];
	    if($executor==NULL)$executor = $_REQUEST['executor'];
	    $t_data = json_decode2($data,true);	    

	    $conn = tools::getConn();
        $sql = "update exam_paper set cost = '".$t_data['cost']."' where id = ".$t_data['id'];
        $res = mysql_query($sql,$conn);
        if($res==FALSE){
	        return array(
	             'msg'=>'sql wrong'
	            ,'sql'=>$sql
	            ,'status'=>'2'
	        );
        }else{
	        return array(
	            'status'=>'1'
	            ,'msg'=>'OK'
	        );
        }
	}
	
	public static function submit($paper_id=NULL,$json=NULL,$executor=NULL){
	    if (!basic_user::checkPermission("4090")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($paper_id==NULL) $paper_id = $_REQUEST['paper_id'];
	    if($json==NULL) $json = $_REQUEST['json'];
	    if($executor==NULL) $executor = $_REQUEST['executor'];
	    $conn = tools::getConn();
	    
	    $sql = "update exam_paper set count_updated = count_updated + 1 where id = ".$paper_id;
	    mysql_query($sql,$conn);
	    
	    $paperData = exam_paper::view($paper_id);
	    $paper_log['id'] = tools::getTableId("exam_paper_log");
	    $paper_log['paper_id'] = $paper_id;
	    $paper_log['mycent'] = 0;
	    $paper_log['mycent_objective'] = 0;
	    $paper_log['count_right'] = 0;
	    $paper_log['count_wrong'] = 0;
	    $paper_log['count_giveup'] = 0;
	    $paper_log['status'] = '10';
	    $paper_log['creater_code'] = $executor;
	    $paper_log['creater_group_code'] = basic_user::$userGroup;
	    $cent = 0;
	    
	    //得到知识点列表,按 code 排序,准备插入
	    $subject_code = $paperData['data']['subject_code'];
	    $sql = "select code from exam_subject where code like '".$subject_code."__' order by code ;";
	    $hash_subjects = array();
        $res = mysql_query($sql,$conn);
        while($temp = mysql_fetch_assoc($res)){
            $exam_subject_2_user_log__id = tools::getTableId("exam_subject_2_user_log");
            $hash_subjects[$temp['code']] = array('right'=>0,'wrong'=>0);
        }
        
        //前端提交过来的AJAX答案数据,必定是按照 id 排序的,所以服务端可以直接匹配
        $questions_user = json_decode2($json,true);        
        $sql = tools::getConfigItem("exam_paper__submit_check"); 
  
        $sql = str_replace("__id__", $paper_id, $sql);
	    $questions_check = array();
        $res2 = mysql_query($sql,$conn);

        $index = 0;
        while($temp = mysql_fetch_assoc($res2)){
            $questions_check[] = $temp; 
            $cent += $temp['cent'];
            $question_user = $questions_user[$index];            
            $index ++;
            $knowledge = $temp['knowledge'];
            $knowledge_subject = explode(",", $knowledge);
            
            if( $temp['type']!='3' && $temp['type']!='2' && $temp['type']!='1' ){
                continue;
            }else if($question_user['myanswer']=='I_DONT_KNOW'){
                $paper_log['count_giveup'] ++;
                continue;
            }else if($question_user['myanswer']==$temp['answer']){

                $paper_log['mycent'] += $temp['cent'];
                $paper_log['mycent_objective'] += $temp['cent'];
                $paper_log['count_right'] ++;
                for($i=0;$i<count($knowledge_subject);$i++){
                    $hash_subjects[$knowledge_subject[$i]]['right'] += (5 - $temp['difficulty']) ;
                }
            }else{
                $paper_log['count_wrong'] ++;
                for($i=0;$i<count($knowledge_subject);$i++){
                    $hash_subjects[$knowledge_subject[$i]]['wrong'] += (5 - $temp['difficulty']) ;
                }
                
                //错题本
                $sql = "insert into exam_question_log_wrongs (question_id,creater_code) values ('".$temp['id']."','".$executor."');";
                $res3 = mysql_query($sql,$conn);
            }  

			        			
        }
        /*
        if( ($paper_log['count_right'] + $paper_log['count_wrong'])*3 < $paper_log['count_giveup'] ){
            return array(
                 'msg'=>'give up too much'
                ,'status'=>'2'
            ); 
        }
        */
        
        if(($paper_log['count_right'] + $paper_log['count_wrong'])!=0){
            $paper_log['proportion'] =  floor (( $paper_log['count_right'] * 100 ) / ($paper_log['count_right'] + $paper_log['count_wrong']));
        }
        
        $keys = array_keys($paper_log);
        $keys = implode(",",$keys);
        $values = array_values($paper_log);
        $values = implode("','",$values);    
        $sql = "insert into exam_paper_log (".$keys.") values ('".$values."')";
        $res4 = mysql_query($sql,$conn);
        if($res4==false){
            return array(
                 'msg'=>mysql_error($conn)
                ,'status'=>'2'
                ,'sql'=>$sql
            ); 
        }
        
        $paper_log['cent'] = $cent;
        $hash_subjects[$paperData['data']['subject_code']] = array('right'=>$paper_log['count_right'],'wrong'=>$paper_log['count_wrong']);
        $keys = array_keys($hash_subjects);
        for($i=0;$i<count($keys);$i++){
            if($hash_subjects[$keys[$i]]['right']==0 && $hash_subjects[$keys[$i]]['wrong']==0){
                unset($hash_subjects[$keys[$i]]);
                continue;
            }
            $proportion = floor(($hash_subjects[$keys[$i]]['right'] * 100) / ($hash_subjects[$keys[$i]]['right'] + $hash_subjects[$keys[$i]]['wrong']));
            $sql = "insert into exam_subject_2_user_log 
            (subject_code,count_positive,count_negative,proportion,paper_id,paper_log_id,creater_code,creater_group_code) values 
            ('".$keys[$i]."','".$hash_subjects[$keys[$i]]['right']."','".$hash_subjects[$keys[$i]]['wrong']."','".$proportion."','".$paper_id."','".$paper_log['id']."','".$executor."','".basic_user::$userGroup."'); ";
            $res5 = mysql_query($sql,$conn);
            if($res5==false){
                return array(
                     'msg'=>mysql_error($conn)
                    ,'status'=>'2'
                    ,'sql'=>$sql
                ); 
            }
        }
        
        return array(
             'questions'=>$questions_check
            ,'paper_log'=>$paper_log
            ,'hash_subjects'=>$hash_subjects
            ,'msg'=>'ok'
            ,'status'=>'1'
        );         
	}
	
	public static function upload_img(){
	    if (!basic_user::checkPermission("4090")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }			    
	    if (($_FILES["file"]["size"] > 2000000)){
	        exit();
	    }
        if ($_FILES["file"]["error"] > 0){            
            exit();
        }	    
        $type = $_FILES['file']['type'];
        if($type!='image/jpeg' && $type!='image/gif' &&$type!='image/x-png'&&$type!='image/png'){
    		return array(
    		    'status'=>'2'
    		    ,'msg'=>$type
    		); 
        }
        
        $file = "../file/upload/paper/img/".$_REQUEST['executor']."_".rand(10000, 99999)."_".$_FILES["file"]["name"];
        move_uploaded_file($_FILES["file"]["tmp_name"],$file);    
        
		return array(
		    'status'=>'1'
		    ,'file'=>$file
		);        
	} 
	
	public static $phpexcel = null;
	
	public static function upload(){
		$file = "";
		if(!isset($_REQUEST['dfile'])){
			if (!basic_user::checkPermission("4011")){
				return array(
					 'msg'=>'access denied'
					,'status'=>'2'
				);
			}			        
			if (($_FILES["file"]["size"] > 2000000)){
				exit();
			}
			if ($_FILES["file"]["error"] > 0){            
				exit();
			}	
			
			/*
			if( ('application/vnd.ms-excel'!=$_FILES['file']['type']) && ('application/octet-stream'!=$_FILES['file']['type']) ){
				return array(
					'status'=>'2'
					,'type'=>$_FILES['file']['type']
				);            
			}
			*/
			
			$file = "../file/upload/paper/".rand(10000, 99999)."_".$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);  
		}else{
			$file = "../file/upload/paper/".$_REQUEST['dfile'];
		}
        
        include_once '../libs/phpexcel/Classes/PHPExcel.php';
        include_once '../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
        include_once '../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
        $PHPReader = PHPExcel_IOFactory::createReader('Excel5');
        $PHPReader->setReadDataOnly(true);
        $phpexcel = $PHPReader->load($file);
        exam_paper::$phpexcel = $phpexcel;
        
        $conn = tools::getConn();
        $currentSheet = $phpexcel->getSheetByName('exam_paper');
	    if($currentSheet==null){
	        return array(
	             'msg'=>'Missing sheet exam_paper, check your excel'
	            ,'status'=>'2'
	        );
	    }        
        $paper = array(
             'id'=>tools::getTableId("exam_paper")
            ,'subject_code'=>$currentSheet->getCell('A2')->getValue()
            ,'title'=>$currentSheet->getCell('B2')->getValue()
            ,'cost'=>$currentSheet->getCell('C2')->getValue()
            ,'type'=>'20'
            ,'status'=>'10'
            ,'cent'=>0
            ,'cent_subjective'=>0
            ,'cent_objective'=>0
            ,'count_question'=>0
            ,'count_subjective'=>0
            ,'count_objective'=>0
            ,'creater_code'=>$_REQUEST['executor']
            ,'creater_group_code'=> basic_user::$userGroup
        );
		
        $currentSheet = $phpexcel->getSheetByName('exam_question');
	    if($currentSheet==null){
	        return array(
	             'msg'=>'Missing sheet exam_question, check your excel'
	            ,'status'=>'2'
	        );
	    }            
        $row = $currentSheet->getHighestRow();
        
        $questions = array();
        for($i=2;$i<=$row;$i++){
            $title = $currentSheet->getCell('G'.$i)->getValue();
            $title = str_replace("\n", "<br/>", $title);
            $title = str_replace("\r", "<br/>", $title);
            $title = str_replace("'", "&acute;", $title);
            $title = str_replace("\"", "&quot;", $title);
            $id_parent = $currentSheet->getCell('B'.$i)->getValue();
            if($id_parent==NULL)$id_parent = '0';
            if($id_parent!='0'){
                $id_parent = $questions[$id_parent-$questions[0]['index']]['id'];
            }
            $option_length = $currentSheet->getCell('H'.$i)->getValue();
            if(!is_numeric($option_length))$option_length = '0';
            $difficulty = $currentSheet->getCell('S'.$i)->getValue();
            if(!is_numeric($difficulty))$difficulty = '0';     
            $cent = $currentSheet->getCell('C'.$i)->getValue();
            if(!is_numeric($cent))$cent = '0';        
            $layout = $currentSheet->getCell('D'.$i)->getValue();
            if($layout!='1'&&$layout!='2')$layout = '0';                             
            $question = array(
                 'id'=>tools::getTableId("exam_question")
                ,'id_parent'=>$id_parent
                ,'cent'=>$cent
                ,'layout'=>$layout
                ,'type'=>$currentSheet->getCell('E'.$i)->getValue()
                ,'subject_code'=>trim($currentSheet->getCell('F'.$i)->getValue())
                ,'title'=>$title
                ,'option_length'=>$option_length
                ,'option_1'=>str_replace("'", "&acute;", $currentSheet->getCell('I'.$i)->getValue())
                ,'option_2'=>str_replace("'", "&acute;", $currentSheet->getCell('J'.$i)->getValue())
                ,'option_3'=>str_replace("'", "&acute;", $currentSheet->getCell('K'.$i)->getValue())
                ,'option_4'=>str_replace("'", "&acute;", $currentSheet->getCell('L'.$i)->getValue())
                ,'option_5'=>str_replace("'", "&acute;", $currentSheet->getCell('M'.$i)->getValue())
                ,'option_6'=>str_replace("'", "&acute;", $currentSheet->getCell('N'.$i)->getValue())
                ,'option_7'=>str_replace("'", "&acute;", $currentSheet->getCell('O'.$i)->getValue())
                ,'answer'=>$currentSheet->getCell('P'.$i)->getValue()
                ,'description'=>$currentSheet->getCell('Q'.$i)->getValue()
                ,'knowledge'=>$currentSheet->getCell('R'.$i)->getValue()
                ,'difficulty'=>$difficulty
                ,'path_listen'=>$currentSheet->getCell('T'.$i)->getValue()
                ,'path_img'=>$currentSheet->getCell('U'.$i)->getValue()
                ,'paper_id'=>$paper['id']
                ,'index'=>$currentSheet->getCell('A'.$i)->getValue()
            );
            
            //TODO 内容,格式判断
            if($question['type']=='1'||$question['type']=='2'||$question['type']=='3'){
                $paper['count_objective'] ++;
                $paper['count_question'] ++;
                $paper['cent'] += $question['cent'];
                $paper['cent_objective'] += $question['cent'];
            }else if($question['type']=='4'||$question['type']=='6'){
                $paper['count_subjective'] ++;
                $paper['count_question'] ++;
                $paper['cent'] += $question['cent'];
                $paper['cent_subjective'] += $question['cent'];
            }  

            $questions[] = $question;
        }   
        
        unset($question['index']);
        $keys = array_keys($question);
        $keys = implode(",",$keys);
        for($i=0;$i<count($questions);$i++){
            unset($questions[$i]['index']);
            $values = array_values($questions[$i]);
            $values = implode("','",$values);    
            $sql_q = "insert into exam_question (".$keys.") values ('".$values."')";
            $res = mysql_query($sql_q,$conn);
            if($res==FALSE){
        		return array(
        		     'status'=>'2'
        		    ,'msg'=>"question wrong , line ".$i
        		    ,'sql_q'=>$sql_q
        		    ,'error'=>mysql_error()
        		);
            }
        }
        
        $keys = array_keys($paper);
        $keys = implode(",",$keys);
        $values = array_values($paper);
        $values = implode("','",$values);    
        $sql = "insert into exam_paper (".$keys.") values ('".$values."')";
        $res = mysql_query($sql,$conn);
        if($res==false){
            $msg = mysql_error($conn);
            mysql_query("delete from exam_question where paper_id = ".$paper['id'],$conn);
    		return array(
    		     'status'=>'2'
    		    ,'msg'=>$msg
    		    ,'sql'=>$sql
    		);
        }
        
		return array(
		     'status'=>'1'
		    ,'msg'=>"ok"
		    ,'paper_id'=>$paper['id']
		);
	}
	
	public static function download($id=NULL){
	    if (!basic_user::checkPermission("4012")){
	        return array(
	             'msg'=>'access denied'
	            ,'status'=>'2'
	        );
	    }		    
	    if($id==NULL)$id = $_REQUEST['id'];
        include_once '../libs/phpexcel/Classes/PHPExcel.php';
        include_once '../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
        include_once '../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
        $objPHPExcel = new PHPExcel();
        exam_paper::$phpexcel = $objPHPExcel;
        $conn = tools::getConn();
        $paper = exam_paper::view($id);
        
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('exam_paper');
		tools::readIl8n();
		$objPHPExcel->getActiveSheet()->setCellValue('A1', tools::$LANG['exam_subject']['exam_subject']);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', "  ".$paper['data']['subject_code']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', tools::$LANG['basic_normal']['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $paper['data']['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', tools::$LANG['exam_paper']['cost']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $paper['data']['cost']);		

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('exam_question');	
		
		$objPHPExcel->getActiveSheet()->setCellValue('A1', tools::$LANG['basic_normal']['id'] );
		$objPHPExcel->getActiveSheet()->setCellValue('B1', tools::$LANG['exam_paper']['id_parent'] );
		$objPHPExcel->getActiveSheet()->setCellValue('C1', tools::$LANG['exam_paper']['cent'] );
		$objPHPExcel->getActiveSheet()->setCellValue('D1', tools::$LANG['exam_paper']['layout']);
		$objPHPExcel->getActiveSheet()->setCellValue('E1', tools::$LANG['basic_normal']['type']);
		$objPHPExcel->getActiveSheet()->setCellValue('F1', tools::$LANG['exam_subject']['exam_subject'] );
		$objPHPExcel->getActiveSheet()->setCellValue('G1', tools::$LANG['basic_normal']['title']);
		$objPHPExcel->getActiveSheet()->setCellValue('H1', tools::$LANG['exam_paper']['option_length']);
		$objPHPExcel->getActiveSheet()->setCellValue('I1', tools::$LANG['exam_paper']['option'].'A');
		$objPHPExcel->getActiveSheet()->setCellValue('J1', tools::$LANG['exam_paper']['option'].'B');
		$objPHPExcel->getActiveSheet()->setCellValue('K1', tools::$LANG['exam_paper']['option'].'C');
		$objPHPExcel->getActiveSheet()->setCellValue('L1', tools::$LANG['exam_paper']['option'].'D');
		$objPHPExcel->getActiveSheet()->setCellValue('M1', tools::$LANG['exam_paper']['option'].'E');
		$objPHPExcel->getActiveSheet()->setCellValue('N1', tools::$LANG['exam_paper']['option'].'F');
		$objPHPExcel->getActiveSheet()->setCellValue('O1', tools::$LANG['exam_paper']['option'].'G');
		$objPHPExcel->getActiveSheet()->setCellValue('P1', tools::$LANG['exam_paper']['answer']);
		$objPHPExcel->getActiveSheet()->setCellValue('Q1', tools::$LANG['exam_paper']['description']);
		$objPHPExcel->getActiveSheet()->setCellValue('R1', tools::$LANG['exam_paper']['knowledge']);
		$objPHPExcel->getActiveSheet()->setCellValue('S1', tools::$LANG['exam_paper']['difficulty']);
		$objPHPExcel->getActiveSheet()->setCellValue('T1', tools::$LANG['exam_paper']['path_listen']);
		$objPHPExcel->getActiveSheet()->setCellValue('U1', tools::$LANG['exam_paper']['path_img']);		
		
		$sql = "select * from exam_question where paper_id = ".$id." order by id";	
        $res = mysql_query($sql,$conn);
        $data = array();
        $index = 2;
        while($temp = mysql_fetch_assoc($res)){
    		$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $temp['id']);
    		$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $temp['id_parent']);
    		$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $temp['cent']);
    		$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $temp['layout']);
    		$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $temp['type']);
    		$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, "  ".$temp['subject_code']);
    		$objPHPExcel->getActiveSheet()->setCellValue('G'.$index, $temp['title']);
    		$objPHPExcel->getActiveSheet()->setCellValue('H'.$index, $temp['option_length']);
    		$objPHPExcel->getActiveSheet()->setCellValue('I'.$index, $temp['option_1']);
    		$objPHPExcel->getActiveSheet()->setCellValue('J'.$index, $temp['option_2']);
    		$objPHPExcel->getActiveSheet()->setCellValue('K'.$index, $temp['option_3']);
    		$objPHPExcel->getActiveSheet()->setCellValue('L'.$index, $temp['option_4']);
    		$objPHPExcel->getActiveSheet()->setCellValue('M'.$index, $temp['option_5']);
    		$objPHPExcel->getActiveSheet()->setCellValue('N'.$index, $temp['option_6']);
    		$objPHPExcel->getActiveSheet()->setCellValue('O'.$index, $temp['option_7']);
    		$objPHPExcel->getActiveSheet()->setCellValue('P'.$index, $temp['answer']);
    		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$index, $temp['description']);
    		$objPHPExcel->getActiveSheet()->setCellValue('R'.$index, $temp['knowledge']);
    		$objPHPExcel->getActiveSheet()->setCellValue('S'.$index, $temp['difficulty']);
    		$objPHPExcel->getActiveSheet()->setCellValue('T'.$index, $temp['path_listen']);
    		$objPHPExcel->getActiveSheet()->setCellValue('U'.$index, $temp['path_img']);
    		$index ++;
        }
        
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "../file/download/".date('YmdHis').".xls";
		$objWriter->save($file);

		return array(
		    'status'=>'1'
		    ,'file'=>$file
		);
	}
}