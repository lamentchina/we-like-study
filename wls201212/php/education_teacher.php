<?php
class education_teacher {
    
    public function insert($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);    

        //先判断一下学号是否已存在
        $sql = "select * from education_teacher where code = '".$data['code']."';";
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
        $sql = "insert into basic_user (username,password,id_person,type) values (".$data['code']."','".md5('888888')."','".$data['id_person']."','3');";
        mysql_query($sql,$CONN);
        $data['id_user'] = mysql_insert_id($CONN); 

        //添加一行 用户所属用户组 记录
        $sql = "insert into basic_group_2_user(username,code_group,remark) values (".$data['code']."','".$data['department']."','teacher insert');";
        mysql_query($sql,$CONN);
        
        //正式添加一条 教师 记录
        $keys = array_keys($data);
        $keys = implode(",",$keys);
        $values = array_values($data);
        $values = implode("','",$values);    
        $sql = "insert into education_teacher (".$keys.") values ('".$values."')";
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1,'id_user'=>$data['id_user'],'id_person'=>$data['id_person']));
    }
    
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'education_teacher__title' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['title'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'education_teacher__type' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'education_teacher__honor' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['honor'] = $data;		
		
		$sql = "select code,name from basic_department where code like '4%' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
		    if($return=='json'){
    			for($i=0;$i<strlen($temp['code'])-2;$i++){
    		        $temp['name'] = "-".$temp['name'];
    		    }
		    }
			$data[] = $temp;
		}
		$config['department'] = $data;	
		
		$sql = "select code,value from basic_parameter where reference = 'GB4762' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB4762'] = $data;	

		$sql = "select code,value from basic_parameter where reference = 'GB4568' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['GB4568'] = $data;			

		if($return=='json'){
		    echo json_encode($config);
		}else{
		    return $config;
		}
    }	
    
    public function import(){
        tools::checkPermission("120101");
        include_once '../libs/ajaxUpload/php.php';
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("xls");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
       
        self::importExcel($uploader->savePath);
    }    
    
     public function importExcel($path=NULL){
        if($path==NULL)$path=$_REQUEST['path']; //TODO delete
        include_once config::$phpexcel.'PHPExcel.php';
        include_once config::$phpexcel.'PHPExcel/IOFactory.php';
        include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
        
        $objPHPExcel = new PHPExcel();
        $PHPReader = PHPExcel_IOFactory::createReader('Excel5');
        $PHPReader->setReadDataOnly(true);
        $obj = $PHPReader->load($path);
        
        $il8n = tools::getLanguage(); 
        $CONN = tools::conn();
        $currentSheet = $obj->getSheetByName($il8n['education_teacher']['education_teacher']);
        if($currentSheet==null){
            //如果这个sheet页不存在,就报错
            tools::error(array("state"=>0,"msg"=>$il8n['requestSheetMissing']." : ".$il8n['education_teacher']['education_teacher']),'json');
        }    
        
        $allColmun = $currentSheet->getHighestColumn();
        $allRow = $currentSheet->getHighestRow();
        //echo $allColmun;
        
        $columns2int = array(
             'A'=>1
            ,'B'=>2
            ,'C'=>3
            ,'D'=>4
            ,'E'=>5
            ,'F'=>6
            ,'G'=>7
            ,'H'=>8
            ,'I'=>9
            ,'J'=>10
            ,'K'=>11
            ,'L'=>12
            ,'M'=>13
            ,'N'=>14
            ,'O'=>15
            ,'P'=>16
            ,'Q'=>17
            ,'R'=>18
            ,'S'=>19
            ,'T'=>20
            ,'U'=>21
            ,'V'=>22
            ,'W'=>23
            ,'X'=>24
            ,'Y'=>25
            ,'Z'=>26
            ,'AA'=>27
            ,'AB'=>28
            ,'AC'=>29
            ,'AD'=>30
            ,'AE'=>31
            ,'AF'=>32
            ,'AG'=>33
            ,'AH'=>34
            ,'AI'=>35
            ,'AJ'=>36
            ,'AK'=>37
        
        );
        $columns2int_ = array_flip($columns2int);
        //print_r($columns2int_);
        

        $keys = $keys2 = array();
        for($i=1;$i<=$columns2int[$allColmun];$i++){
            //echo $columns2int_[$i];
            //echo $currentSheet->getCell('AE1')->getValue();
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['code'])$keys['code'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['department'])$keys['department'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['certificate'])$keys['certificate'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['title'])$keys['title'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['years'])$keys['years'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['type'])$keys['type'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['honor'])$keys['honor'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['specialty'])$keys['specialty'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['experience_work'])$keys['experience_work'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['experience_publish'])$keys['experience_publish'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['experience_project'])$keys['experience_project'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['photo_certificate'])$keys['photo_certificate'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['education_teacher']['photo_degree'])$keys['photo_degree'] = $i;

            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['name'])$keys2['name'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['birthday'])$keys2['birthday'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['cardType'])$keys2['cardType'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['idcard'])$keys2['idcard'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['photo'])$keys2['photo'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['height'])$keys2['height'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['nationality'])$keys2['nationality'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['gender'])$keys2['gender'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['nation'])$keys2['nation'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['ismarried'])$keys2['ismarried'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['degree'])$keys2['degree'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['degree_school'])$keys2['degree_school'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['politically'])$keys2['politically'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['address_birth'])$keys2['address_birth'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['address'])$keys2['address'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['cellphone'])$keys2['cellphone'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['email'])$keys2['email'] = $i;
            if($currentSheet->getCell($columns2int_[$i].'1')->getValue()==$il8n['basic_person']['qq'])$keys2['qq'] = $i;
            
        }
        //print_r($keys);
        
        $config = self::loadConfig('Array');
        include_once 'basic_person.php';
        $basic_person = new basic_person();
        $config2 = $basic_person->loadConfig('Array');
        
        for($i=2;$i<=$allRow;$i++){
            
            //先插入一条 个人信息 记录
            $basic_person_data = Array();
            $keys2_keys = array_keys($keys2);
            for($i2=0;$i2<count($keys2);$i2++){
                $basic_person_data[$keys2_keys[$i2]] = $currentSheet->getCell($columns2int_[$keys2[$keys2_keys[$i2]]].$i)->getValue();
            }
            
            if(isset($keys2['cardType'])){
                for($i3=0;$i3<count($config2['cardType']);$i3++){
                    if($basic_person_data['cardType']==$config2['cardType'][$i3]['value'])
                        $basic_person_data['cardType'] = $config2['cardType'][$i3]['code'];
                }                
            }
            if(isset($keys2['nation'])){
                for($i3=0;$i3<count($config2['GB3304']);$i3++){
                    if($basic_person_data['nation'] == $config2['GB3304'][$i3]['value'])
                        $basic_person_data['nation'] = $config2['GB3304'][$i3]['code'];
                }                
            }       
            if(isset($keys2['degree'])){
                for($i3=0;$i3<count($config2['GB4568']);$i3++){
                    if($basic_person_data['degree'] == $config2['GB4568'][$i3]['value'])
                        $basic_person_data['degree'] = $config2['GB4568'][$i3]['code'];
                }                
            }   
            if(isset($keys2['politically'])){
                for($i3=0;$i3<count($config2['GB4762']);$i3++){
                    if($basic_person_data['politically'] == $config2['GB4762'][$i3]['value'])
                        $basic_person_data['politically'] = $config2['GB4762'][$i3]['code'];
                }                
            }     
            if(isset($keys2['gender'])){
                for($i3=0;$i3<count($config2['GB2261_1']);$i3++){
                    if($basic_person_data['gender'] == $config2['GB2261_1'][$i3]['value'])
                        $basic_person_data['gender'] = $config2['GB2261_1'][$i3]['code'];
                }                
            } 
            if(isset($keys2['ismarried'])){
                for($i3=0;$i3<count($config2['GB2261_2']);$i3++){
                    if($basic_person_data['ismarried'] == $config2['GB2261_2'][$i3]['value'])
                        $basic_person_data['ismarried'] = $config2['GB2261_2'][$i3]['code'];
                }                
            }                            
            $basic_person_data = array_merge($basic_person_data,array(
                'status'=>'1'
               ,'remark'=>'teacher excel import'
            ));
            $keys_ = array_keys($basic_person_data);
            $keys_ = implode(",",$keys_);
            $values = array_values($basic_person_data);
            $values = implode("','",$values);    
            $sql = "insert into basic_person (".$keys_.") values ('".$values."') ;";
            //echo $sql;            
            mysql_query($sql,$CONN);
            $id_person = mysql_insert_id($CONN);          
            
            //读取教师信息
            $education_teacher_data = Array();
            $keys_keys = array_keys($keys);
            for($i2=0;$i2<count($keys);$i2++){
                $education_teacher_data[$keys_keys[$i2]] = $currentSheet->getCell($columns2int_[$keys[$keys_keys[$i2]]].$i)->getValue();
            }
            $education_teacher_data['id_person'] = $id_person;            
            
            //添加一行 系统用户信息 记录
            $basic_user_data = Array(
                'username'=>$education_teacher_data['code'].''
               ,'password'=>md5('888888')
               ,'id_person'=>$id_person
               ,'type'=>'3'
               ,'money'=>'1000'
               ,'status'=>'1'
               ,'remark'=>'teacher excel import'
            );
            $keys_ = array_keys($basic_user_data);
            $keys_ = implode(",",$keys_);
            $values = array_values($basic_user_data);
            $values = implode("','",$values);    
            $sql = "insert into basic_user (".$keys_.") values ('".$values."') ;";            
            mysql_query($sql,$CONN);
            $id_teacher = mysql_insert_id($CONN);   
            mysql_query($sql,$CONN);
            $id_user = mysql_insert_id($CONN);     
            $education_teacher_data['id_user'] = $id_user;        
            
            if(isset($keys['title'])){
                for($i3=0;$i3<count($config['title']);$i3++){
                    if($education_teacher_data['title']==$config['title'][$i3]['value'])
                        $education_teacher_data['title'] = $config['title'][$i3]['code'];
                }                
            }
            if(isset($keys['type'])){
                for($i3=0;$i3<count($config['type']);$i3++){
                    if($education_teacher_data['type'] == $config['type'][$i3]['value'])
                        $education_teacher_data['type'] = $config['type'][$i3]['code'];
                }                
            } 
            if(isset($keys['honor'])){
                for($i3=0;$i3<count($config['honor']);$i3++){
                    if($education_teacher_data['honor']==$config['honor'][$i3]['value'])
                        $education_teacher_data['honor'] = $config['honor'][$i3]['code'];
                }                
            }
            if(isset($keys['department'])){
                for($i3=0;$i3<count($config['department']);$i3++){
                    if($education_teacher_data['department'] == $config['department'][$i3]['name'])
                        $education_teacher_data['department'] = $config['department'][$i3]['code'];
                }                
            } 
            $education_teacher_data = array_merge($education_teacher_data,array(
                'status'=>'1'
               ,'remark'=>'teacher excel import'
            ));
            $keys_ = array_keys($education_teacher_data);
            $keys_ = implode(",",$keys_);
            $values = array_values($education_teacher_data);
            $values = implode("','",$values);    
            $sql = "insert into education_teacher (".$keys_.") values ('".$values."') ;";            
            mysql_query($sql,$CONN);
            $id_teacher = mysql_insert_id($CONN);        

            //添加一行 用户所属用户组 记录
            $sql = "insert into basic_group_2_user(username,code_group,remark) values ('".$education_teacher_data['code']."','".$education_teacher_data['department']."','teacher import');";
            mysql_query($sql,$CONN);    
               
        }
        //print_r($config['department']);
        echo json_encode(array('state'=>'1','msg'=>'done'));
    }   

    public function view(){
        $CONN = tools::conn();    
        $id = $_REQUEST['id'];
        $res = mysql_query( "select * from education_teacher where id = '".$id."' " , $CONN );

        $data= mysql_fetch_assoc($res);
        
        echo json_encode($data);
    }  

    public function update($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $id = $data['id'];        
        unset($data['id']);
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $data['time_lastupdated'] = date('Y-M-d');
        $keys = array_keys($data);        
        $sql = "update education_teacher set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        mysql_query("update education_teacher set count_updated = count_updated + 1 where id =".$id,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    } 

    public function grid(){
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
		$orderby = " ORDER BY basic_person.name ASC ";
        //有查询条件
		if(isset($_REQUEST['search'])){
			$search = json_decode($_REQUEST['search'],true);
			$where = " where 1=1 ";
			if(isset($search['name']) && trim($search['name'])!=''){
				$where .= " and basic_person.name like '%".$search['name']."%' ";
			}
			if(isset($search['code']) && trim($search['code'])!=''){
				$where .= " and education_teacher.code like '%".$search['code']."%' ";
			}			
			if(isset($search['birthday_min']) && trim($search['birthday_min'])!=''){
				$where .= " and basic_person.birthday >= '".$search['birthday_min']."' ";
			}
			if(isset($search['birthday_max']) && trim($search['birthday_max'])!=''){
				$where .= " and basic_person.birthday >= '".$search['birthday_min']."' ";
			}			
			if(isset($search['type']) && trim($search['type'])!=''){
				$where .= " and education_teacher.type = '".$search['type']."' ";
			}	
			if(isset($search['status']) && trim($search['status'])!=''){
				$where .= " and education_teacher.status = '".$search['status']."' ";
			}			
			if(isset($search['department']) && trim($search['department'])!=''){
				$where .= " and education_teacher.department = '".$search['groups']."' ";
			}			
		}
		//有排序条件
		if(isset($_REQUEST['sortname'])){
		    if($_REQUEST['sortname']=='birthday' || 
		        $_REQUEST['sortname']=='degree' || 
		        $_REQUEST['sortname']=='politically'){
		        $orderby = " order by basic_person.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." "; 
		    }else{		    
			    $orderby = " order by education_teacher.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		    }
		}        
        
        $sql = "
SELECT
education_teacher.code,
basic_person.name,
basic_person.birthday,
basic_person.degree,
basic_person.politically,
education_teacher.department,
education_teacher.id_person,
education_teacher.id_user,
education_teacher.id,
education_teacher.`status`,
education_teacher.`type`,
education_teacher.title,
education_teacher.years
FROM
education_teacher
Left Join basic_person ON education_teacher.id_person = basic_person.id   
        
		".$where."
		".$orderby."        
                 limit ".($page-1)*$pagesize.",".$pagesize." ; ";
        //echo $sql;
        $res = mysql_query($sql,$CONN);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            $temp['birthday'] = substr($temp['birthday'], 0 , 10);
            $data[] = $temp;
        }        
        
        $sql2 = "select count(*) as total FROM
education_teacher
Left Join basic_person ON education_teacher.id_person = basic_person.id    ".$where;
        $res = mysql_query($sql2,$CONN);
        $total = 0;
        while($temp = mysql_fetch_assoc($res)){
            $total = $temp['total'];
        }
        sleep(0.9);
        echo json_encode(  array("Rows"=>$data,"Total"=>$total, 'sql'=>$sql) );
    }   

    public function delete() {
        $CONN = tools::conn();
        $ids = $_REQUEST['ids'];
		$arr = explode(",", $ids);
		for($i=0;$i<count($arr);$i++){
		    mysql_query("call education_teacher__delete(".$arr[$i].")",$CONN);
		}

        sleep(1);
        echo json_encode(array('state'=>1,'msg'=>'done')); 
    }
}