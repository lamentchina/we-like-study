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
            if($search_keys[$i]=='code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_student.code like '%".$search[$search_keys[$i]]."%' ";
            }	
            if($search_keys[$i]=='class_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_student.class_code = '".$search[$search_keys[$i]]."' ";
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
        header("Content-type:text/json");
		echo json_encode($config);
    }	
    
    /**
     * 查看单个学生的信息,基本上将所有的业务数据都取出来了
     * 学生个人信息来自3个模块: 
     *  各人档案信息 basic_person 表, 存储 姓名 性别 籍贯 等
     *  学生档案信息 education_student 表,存储班级 班委 性格 特长 等
     *  系统用户信息 basic_person 表, 存储金币,积分,在线状态灯
     * */
    public function view(){
        if(!tools::checkPermission("1702"))tools::error("access denied");
        $CONN = tools::conn();
        $sql = "
        SELECT
        education_student.code,
        education_student.name,
        education_student.class_code,
        education_student.class_name,
        education_student.class_teacher_name,
        education_student.class_teacher_code,
        education_student.class_teacher_id,
        education_student.growth,
        education_student.health,
        education_student.mentalhealth,
        education_student.healthdefect,
        education_student.junior_school,
        education_student.junior_graduated,
        education_student.junior_scores,
        education_student.junior_rank,
        education_student.parents_name,
        education_student.parents_cellphone,
        education_student.scorerank,
        education_student.scorerank2,
        education_student.scorerank3,
        
        basic_memory__il8n(education_student.specialty,'education_student__specialty',3) as  specialty,
        basic_memory__il8n(education_student.hobby,'education_student__hobby',3) as  hobby,
        basic_memory__il8n(education_student.characters,'education_student__characters',3) as characters,
        basic_memory__il8n(education_student.attitude_life,'education_student__attitude_life',3) as attitude_life,
        basic_memory__il8n(education_student.attitude_learn,'education_student__attitude_learn',3) as attitude_learn,
        basic_memory__il8n(education_student.attitude_teacher,'education_student__attitude_teacher',3) as attitude_teacher,
        basic_memory__il8n(education_student.attitude_classmate,'education_student__attitude_classmate',3) as attitude_classmate,
        basic_memory__il8n(education_student.attitude_oppositesex,'education_student__attitude_oppositesex',3) as attitude_oppositesex,
        basic_memory__il8n(education_student.intelligence,'education_student__intelligence',3) as intelligence,
        basic_memory__il8n(education_student.class_manager,'education_student__class_manager',3) as class_manager,        
        
        basic_person.birthday,
        basic_person.idcard,
        basic_person.photo,
        basic_person.height,
        basic_person.nationality,
        basic_person.degree_school,
        basic_person.degree_school_code,
        basic_person.address_birth,
        basic_person.address_birth_code,
        basic_person.cellphone,
        basic_person.email,
        basic_person.qq,
        basic_person.address,
        basic_person.address_code,
        
        basic_memory__il8n(basic_person.cardType,'basic_person__cardType',3) as cardType,
        basic_memory__il8n(basic_person.gender,'basic_person__gender',3) as gender,
        basic_memory__il8n(basic_person.nation,'basic_person__nation',3) as nation,
        basic_memory__il8n(basic_person.degree,'basic_person__degree',3) as degree,
        basic_memory__il8n(basic_person.ismarried,'basic_person__ismarried',3) as ismarried,
        basic_memory__il8n(basic_person.politically,'basic_person__politically',3) as politically,              
        
        basic_user.money,
        basic_user.money2,
     
        education_student.id,
        education_student.`type`,
        education_student.id_creater,
        education_student.id_creater_group,
        education_student.code_creater_group,
        education_student.time_created,
        education_student.time_lastupdated,
        education_student.count_updated,
        education_student.`status`,
        education_student.remark     
        
        FROM
        education_student
        Left Join basic_person ON education_student.id_person = basic_person.id
        Left Join basic_user ON education_student.id_user = basic_user.id
        where education_student.id = '".$_REQUEST['id']."'
        ";
        
        $res = mysql_query($sql,$CONN);
        $data = mysql_fetch_assoc($res);
        $data['sql'] = preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql));
        header("Content-type:text/json");        
        echo json_encode($data);        
    }
    
    /**
     * 前端上传一个EXCEL文件,服务端接收后,将其放置在服务端的某个文件夹位置
     * 然后再将这个EXCEL中的数据读取并插入到数据库表 basic_excel 中
     * 再利用数据库中的存储过程,将业务数据导入到业务表中
     * 
     * */
    public function import(){
        if(!tools::checkPermission("1711"))tools::error("access denied");
        include_once '../libs/ajaxUpload/php.php';
        $allowedExtensions = array("xls");
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('../file/upload/');
        $path = $uploader->savePath;        
        tools::import($path);
        
        $CONN = tools::conn();
        $sql = "call education_student__import('".tools::$guid."',@state,@msg,@ids)";
        mysql_query($sql,$CONN);
        $res = mysql_query("select @state as state, @msg as msg,@ids as ids",$CONN);
        $data = mysql_fetch_assoc($res);
        
        header("Content-type:text/json");        
        echo json_encode($data);
    }    
    
    /**
     * 批量导出学生数据
     * 在系统前端,导出功能是跟列表绑定在一起的
     * 列表功能上有个查询功能,
     * 导出的数据是按照查询条件来导出的,最高一次性只能导出1000条
     * 
     * EXCEL文件导出的时候,首先会把符合条件的业务数据查询出来,然后先插入到 basic_excel 表中
     * 这个过程依赖数据库存储过程,
     * 名称一般是 业务模块__export(in_guid,out_state,out_msg)
     * 然后再由服务端将将 basic_excel 中的数据导出到EXCEL供客户下载
     * 
     * 学生的数据包含两部分: basic_person 中的个人档案信息 , education_student 中的学生信息
     * */
    public function export() {
        if(!tools::checkPermission("1712"))tools::error("access denied");
        
        //数据库连接口,在一次服务端访问中,数据库必定只连接一次,而且不会断开
        $CONN = tools::conn();
        include_once '../libs/guid.php';
        $Guid = new Guid();  
        $guid = $Guid->toString();
        $search=$_REQUEST['search'];
        
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
            if($search_keys[$i]=='code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_student.code like '%".$search[$search_keys[$i]]."%' ";
            }	
            if($search_keys[$i]=='class_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and education_student.class_code = '".$search[$search_keys[$i]]."' ";
            }	            
        }
        $sql_order = ' order by education_student.id desc ';
        $sql = "
        insert into basic_excel (
             guid 
            ,sheets 
            ,sheetindex 
            ,sheetname
            ,rowindex 
            ,maxcolumn 
        
            ,A 
            ,Z        
        ) values (
			'".$guid."'     
			,'1'       
			,'0'
			,basic_memory__il8n('education_student','education_student',1)
			,1
			,40

			,basic_memory__il8n('doc_student','education_student',1)
			,basic_memory__il8n('doc_person','education_student',1)
		)";  
        mysql_query($sql,$CONN);
        
        $sql = "insert into basic_excel (
             guid 
            ,sheets 
            ,sheetindex 
            ,sheetname
            ,rowindex 
            ,maxcolumn 
        
            ,A 
            ,B  
            ,C   
            ,D   
            ,E 
            ,F
            ,G
            ,H
            ,I
            ,J
            ,K
            ,L
            ,M
            ,N
            ,O
            ,P
            ,Q
            ,R
            ,S
            ,T
            ,U
            ,V
            ,W
            ,X
            ,Y
            ,Z
            ,AA
            ,AB
            ,AC
            ,AD
            ,AE
            ,AF
            ,AG
            ,AH
            ,AI
            ,AJ
            ,AK
            ,AL
            ,AM
            ,AN
             
        ) values (
			'".$guid."'     
			,'1'       
			,'0'
			,basic_memory__il8n('education_student','education_student',1)
			,2
			,40  
			
			,basic_memory__il8n('code','education_student',1)
			,basic_memory__il8n('class_code','education_student',1)
			,basic_memory__il8n('scorerank','education_student',1)
			,basic_memory__il8n('scorerank2','education_student',1)
			,basic_memory__il8n('scorerank3','education_student',1)
			,basic_memory__il8n('specialty','education_student',1)
			,basic_memory__il8n('hobby','education_student',1)
			,basic_memory__il8n('characters','education_student',1)
			,basic_memory__il8n('growth','education_student',1)
			,basic_memory__il8n('health','education_student',1)
			,basic_memory__il8n('healthdefect','education_student',1)
			,basic_memory__il8n('mentalhealth','education_student',1)
			,basic_memory__il8n('attitude_learn','education_student',1)
			,basic_memory__il8n('attitude_teacher','education_student',1)
			,basic_memory__il8n('attitude_life','education_student',1)
			,basic_memory__il8n('attitude_classmate','education_student',1)
			,basic_memory__il8n('attitude_oppositesex','education_student',1)
			,basic_memory__il8n('intelligence','education_student',1)
			,basic_memory__il8n('class_manager','education_student',1)
			,basic_memory__il8n('junior_school','education_student',1)
			,basic_memory__il8n('junior_graduated','education_student',1)
			,basic_memory__il8n('junior_scores','education_student',1)
			,basic_memory__il8n('junior_rank','education_student',1)
			,basic_memory__il8n('parents_name','education_student',1)
			,basic_memory__il8n('parents_cellphone','education_student',1)
			
			,basic_memory__il8n('name','basic_person',1)
			,basic_memory__il8n('birthday','basic_person',1)
			,basic_memory__il8n('cardType','basic_person',1)
			,basic_memory__il8n('idcard','basic_person',1)
			,basic_memory__il8n('photo','basic_person',1)
			,basic_memory__il8n('height','basic_person',1)
			,basic_memory__il8n('nationality','basic_person',1)
			,basic_memory__il8n('gender','basic_person',1)
			,basic_memory__il8n('nation','basic_person',1)
			,basic_memory__il8n('politically','basic_person',1)
			,basic_memory__il8n('address_birth','basic_person',1)
			,basic_memory__il8n('address','basic_person',1)
			,basic_memory__il8n('cellphone','basic_person',1)
			,basic_memory__il8n('email','basic_person',1)
			,basic_memory__il8n('qq','basic_person',1)	
        ); ";
        mysql_query($sql,$CONN);
        
        $sql = "insert into basic_excel (
             guid 
            ,sheets 
            ,sheetindex 
            ,sheetname
            ,rowindex 
            ,maxcolumn 
        
            ,A 
            ,B  
            ,C   
            ,D   
            ,E 
            ,F
            ,G
            ,H
            ,I
            ,J
            ,K
            ,L
            ,M
            ,N
            ,O
            ,P
            ,Q
            ,R
            ,S
            ,T
            ,U
            ,V
            ,W
            ,X
            ,Y
            ,Z
            ,AA
            ,AB
            ,AC
            ,AD
            ,AE
            ,AF
            ,AG
            ,AH
            ,AI
            ,AJ
            ,AK
            ,AL
            ,AM
            ,AN      
        ) select 
            '".$guid."'
            ,'1'
            ,'0'   
            ,basic_memory__il8n('user','basic_user',1)
            ,(education_student.id+2)      
            ,40
            
			,code
			,class_code
			,scorerank
			,scorerank2
			,scorerank3
			,basic_memory__il8n(education_student.specialty,'education_student__specialty',3)
			,basic_memory__il8n(education_student.hobby,'education_student__hobby',3)
			,basic_memory__il8n(education_student.characters,'education_student__characters',3)
			,growth
			,health
			,healthdefect
			,mentalhealth
			,basic_memory__il8n(education_student.attitude_learn,'education_student__attitude_learn',3)
			,basic_memory__il8n(education_student.attitude_teacher,'education_student__attitude_teacher',3)
			,basic_memory__il8n(education_student.attitude_life,'education_student__attitude_life',3)
			,basic_memory__il8n(education_student.attitude_classmate,'education_student__attitude_classmate',3)
			,basic_memory__il8n(education_student.attitude_oppositesex,'education_student__attitude_oppositesex',3)
			,basic_memory__il8n(education_student.intelligence,'education_student__intelligence',3)
			,basic_memory__il8n(education_student.class_manager,'education_student__class_manager',3)
			,junior_school
			,junior_graduated
			,junior_scores
			,junior_rank
			,parents_name
			,parents_cellphone
			
			,basic_person.name
			,birthday
			,basic_memory__il8n(basic_person.cardType,'basic_person__cardType',3)
			,concat(\"'\",idcard)
			,photo
			,height
			,nationality
			,basic_memory__il8n(basic_person.gender,'basic_person__gender',3)
			,basic_memory__il8n(basic_person.nation,'basic_person__nation',3)
			,basic_memory__il8n(basic_person.politically,'basic_person__politically',3)
			,address_birth
			,address
			,cellphone
			,email
			,qq		
          
		FROM
        education_student
        Left Join basic_person ON education_student.id_person = basic_person.id  ".$sql_where.$sql_order." limit 1000";
        
        mysql_query($sql,$CONN);
        $file = tools::export($guid);
        header("Content-type:text/json");        
        echo json_encode(array('path'=>$file,'state'=>1,'file'=>'download','sql'=>$sql));
    }
}