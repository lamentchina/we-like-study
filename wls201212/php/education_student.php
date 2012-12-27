<?php
class education_student {
    
    public function getGrid(){
        $CONN = tools::conn();

        $page = 1;
        if(isset($_REQUEST['page'])){
            $page = $_REQUEST['page'];
        }
        $pagesize = 20;
        if(isset($_REQUEST['pagesize'])){
            $pagesize = $_REQUEST['pagesize'];
        }   

    	$where = " where 1=1 ";
		$orderby = " ORDER BY education_student.id ASC ";
        //有查询条件
		if(isset($_REQUEST['search'])){
			$search = json_decode($_REQUEST['search'],true);
			if(isset($search['name']) && trim($search['name'])!=''){
				$where .= " and basic_person.name like = '%".$search['name']."%' ";
			}			
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$orderby = " order by education_student.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}        
        
        $sql = "        
        SELECT
        education_student.code,
        education_student.class_code,
        education_student.class_teacher_name,
        education_student.class_manager,
        education_student.scorerank,
        basic_person.birthday,
        basic_person.name,
        education_student.id,
        education_student.id_person,
        education_student.id_user
        FROM
        education_student
        Inner Join basic_person ON education_student.id_person = basic_person.id 
		".$where."
		".$orderby."        
                 limit ".($page-1)*$pagesize.",".$pagesize." ; ";
        $res = mysql_query($sql,$CONN);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $data[] = $temp;
        }
        
        $sql = " select count(*) as total FROM
education_student
Left Join basic_person ON education_teacher.id_person = basic_person.id ".$where;
        $res = mysql_query($sql,$CONN);
        $total = 0;
        while($temp = mysql_fetch_assoc($res)){
            $total = $temp['total'];
        }
        sleep(0.9);
        echo json_encode(  array("Rows"=>$data,"Total"=>$total) );
    }
    
    public function insert($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);    

        //先判断一下学号是否已存在
        $sql = "select * from education_student where code = '".$data['code']."';";
        $res = mysql_query($sql,$CONN);
        $temp = mysql_fetch_assoc($res);
        if($temp!=FALSE){
            echo json_encode(array('sql'=>$sql,'state'=>0,'id'=>$temp['id'],'msg'=>'code was already used'));
            return;
        }
        
        //添加一行 人员详细信息 记录
        $sql = "insert into basic_person (name) values ('".$data['code']."');";
        mysql_query($sql,$CONN);  
        $data['id_person'] = mysql_insert_id($CONN);        
        
        //添加一行 系统用户信息 记录
        $sql = "insert into basic_user (username,password,id_person,type) values ('st".$data['code']."','".md5('888888')."','".$data['id_person']."','2');";
        mysql_query($sql,$CONN);
        $data['id_user'] = mysql_insert_id($CONN); 

        //添加一行 用户所属用户组 记录
        $sql = "insert into basic_group_2_user(username,code_group,remark) values ('st".$data['code']."','".$data['class_code']."','student insert');";
        mysql_query($sql,$CONN);
        
        //正式添加一条 学生 记录
        $keys = array_keys($data);
        $keys = implode(",",$keys);
        $values = array_values($data);
        $values = implode("','",$values);    
        $sql = "insert into education_student (".$keys.") values ('".$values."')";
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1,'id_user'=>$data['id_user'],'id_person'=>$data['id_person']));
    }
    
    public function loadConfig() {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'education_student__classmanager' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['classmanager'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'education_student__specialty' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['specialty'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'education_student__hobby' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['hobby'] = $data;		
		
		$sql = "select code,value from basic_parameter where reference = 'education_student__characters' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['characters'] = $data;	

		$sql = "select code,value from basic_parameter where reference = 'education_student__alife' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['alife'] = $data;		
		
		$sql = "select code,value from basic_parameter where reference = 'education_student__alearn' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['alearn'] = $data;		

		$sql = "select code,value from basic_parameter where reference = 'education_student__ateacher' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['ateacher'] = $data;		

		$sql = "select code,value from basic_parameter where reference = 'education_student__aclassmate' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['aclassmate'] = $data;		

		$sql = "select code,value from basic_parameter where reference = 'education_student__aoppositesex' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['aoppositesex'] = $data;	

		$sql = "select code,value from basic_parameter where reference = 'education_student__intelligence' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['intelligence'] = $data;	
		
		$sql = "select code,name from basic_department where code like '5%' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			for($i=0;$i<strlen($temp['code'])-2;$i++){
		        $temp['name'] = "-".$temp['name'];
		    }
			$data[] = $temp;
		}
		$config['department'] = $data;		

		echo json_encode($config);
    }	
}