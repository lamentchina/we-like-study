<?php
class education_student {
    
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
            tools::checkPermission('1501',$_REQUEST['username'],$_REQUEST['session']);
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
            if($search_keys[$i]=='name' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_student.name like '%".$search[$search_keys[$i]]."%' ";
            }	
        }
        $sql_order = ' order by education_student.id desc ';
        if(isset($_REQUEST['sortname'])){
            $sql_order = ' order by education_student.'.$_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        
        $sql = "
        SELECT
        education_student.code,
        education_student.name,
        education_student.class_code,
        education_student.class_name,
        education_student.class_teacher_name,
        education_student.class_teacher_code,
        education_student.class_teacher_id,
        education_student.class_manager,
        education_student.id_user,
        education_student.id_person,
        education_student.scorerank,
        education_student.scorerank2,
        education_student.scorerank3,
        education_student.id,
        education_student.`status`,
        education_student.`type`,
        education_student.id_creater,
        education_student.id_creater_group,
        education_student.code_creater_group
        FROM
        education_student
        ";
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['user_type']=='1'){ 
            //管理员角色
        }        
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['user_type']=='2'){ 
            //学生角色
            $sql_where .= " and education_student.class_code = '".$_REQUEST['group_code']."' ";
        }
        
        if($_REQUEST['user_type']=='3'){ 
            //教师角色
            $sql_where .= " and education_student.class_code in (select group_code from education_subject_2_group_2_teacher where teacher_code = '".$_REQUEST['user_code']."') ";
        }
        
        $sql = $sql.$sql_where.$sql_order." limit ".(($page-1)*$pagesize).", ".$pagesize;
        $res = mysql_query($sql,$conn);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签

            $data[] = $temp;
        }
        
        $sql_total = "select count(*) as total from education_student ".$sql_where;
        $res = mysql_query($sql_total,$conn);
        $total = mysql_fetch_assoc($res);        
        
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