<?php
/**
 * 系统用户相关操作的服务端
 * 
 * 关于新建用户
 *   新建一个用户的时候,往往是在系统前端 注册 而来的.
 *   注册时,所必填的内容只有 
 *     用户名
 *     密码
 *     手机电话号
 *     邮箱
 *     类型
 *   其中的 手机电话号 跟 邮箱,是要勇于系统验证的.并且,手机号 跟 邮箱,必须唯一性
 *   当然,管理员在登录系统后直接添加也可以
 * 既然新建一个用户时,所必须的信息输入项这么少,那么,就可以教给存储过程来实现了
 * 
 * 关于修改用户信息
 *   管理员可以直接修改一个用户的 密码 邮箱 手机号 ,
 *   但是,登录用户,就只能修改密码
 * 
 * 那么,如果用户要修改业务身份呢？
 * 因为用户信息是跟业务身份联系在一起的,一个会计想修改自己的会计身份信息,那么,
 * 他就必须到 会计列表 那里,找到自己的那一条会计记录,然后修改
 * 
 * 至于修改个人档案,在用户信息修改界面上,是可以直接修改的,包括
 *   照片
 *   出生日期
 *   国籍
 *   籍贯
 *   民族
 *   学历
 *   身高
 *   
 * 等等.
 * 这些个人信息档案,是跟 用户信息修改 直接联系在一起的,应该在修改的时候直接可以操作
 * 由于字段很多,修改操作不应该教给数据库的存储过程,因为这样要提供一大串的参数,编程比较麻烦
 * 直接在服务端代码实现好了
 * 
 * 或者,提供一个 修改个人档案 按钮,额外的修改个人信息,在 basic_person 模块中操作   
 * 
 * 注册 跟 添加,是否可以引用同一个后台?
 *   添加,后台是需要判断权限的
 *   注册,后台不需要判断权限
 * 
 * @version 201210
 * @author wei1224hf@gmail.com
 * @prerequisites basic_memory__init,basic_memory.il8n()
 * */
class basic_user {
        
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
            if($search_keys[$i]=='username' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.username like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='group_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.group_code like '%".$search[$search_keys[$i]]."%' ";
            }	
            if($search_keys[$i]=='status' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.status = '".$search[$search_keys[$i]]."' ";
            }  
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.type = '".$search[$search_keys[$i]]."' ";
            }  
            if($search_keys[$i]=='money' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.money > '".$search[$search_keys[$i]]."' ";
            }                      	
        }
        $sql_order = ' order by basic_user.id desc ';
		//有排序条件
		if(isset($_REQUEST['sortname'])){
			$sql_order = " order by basic_user.".$_REQUEST['sortname']." ".$_REQUEST['sortorder']." ";
		}          
        
        $returnData = array();
        //根据不同的用户角色,会有不同的列输出

        if($_REQUEST['user_type']=='1'){ 
            //管理员角色          

            $sql = "     
            SELECT
            basic_user.username,
            basic_user.id,
            basic_user.group_code,
            basic_user.group_name,
            basic_user.group_id,
            basic_user.person_name,
            basic_user.person_id,
            basic_user.person_cellphone,
            basic_user.money,
            basic_user.money2,
            basic_user.`type`,
            basic_user.`status`,
            (select extend4 from basic_memory where extend5='basic_user__type' and code = basic_user.`type`) as name_type,
            (select extend4 from basic_memory where extend5='basic_user__status' and code = basic_user.`status`) as name_status,           
            basic_user.time_created,
            basic_user.id_creater
            FROM
            basic_user
            
    		".$sql_where."
    		".$sql_order."
    		limit ".(($page-1)*$pagesize).", ".$pagesize;
            //echo $sql;
            $res = mysql_query($sql,$conn);
            $data = array();
            while($temp = mysql_fetch_assoc($res)){
                //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
    			$temp['time_created'] = substr( $temp['time_created'],0,10);
                $data[] = $temp;
            }
            
            $sql_total = "select count(*) as total 
            FROM
            basic_user
    		".$sql_where;
            
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoc($res);
        }   
        
        $returnData = array(
            'Rows'=>$data,
            'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)),
            'Total'=>$total['total']
        );
        if ($return=='array') {
            return $returnData;
        }
        
        echo json_encode($returnData);
    }
        
    public function delete(){//TODO
        if(!tools::checkPermission("120223"))tools::error("access denied");
        $CONN = tools::conn();

        mysql_query("call basic_user__delete('".$_REQUEST['ids']."',@state,@msg)",$CONN);
        $res = mysql_query("select @state as state, @msg as msg",$CONN);
        $data = mysql_fetch_assoc($res);
        
        sleep(1.5);
        header("Content-type:text/json");
        echo json_encode($data);
    }
    
    private function getPermission($username=NULL){
        $CONN = tools::conn();
        
        $sql = "
            SELECT
            basic_permission.code,
            Min(basic_group_2_permission.cost) as cost,
            Max(basic_group_2_permission.credits) as credits,
            basic_permission.name,
            basic_permission.path,
			basic_permission.type,
			basic_permission.icon
            FROM
                    basic_permission
                    Right Join basic_group_2_permission ON basic_permission.id = basic_group_2_permission.id_permission
                    Right Join basic_group_2_user ON basic_group_2_permission.id_group = basic_group_2_user.id_group
                    Right Join basic_user ON basic_group_2_user.id_user = basic_user.id
            WHERE
                    basic_user.username =  '".$_REQUEST['username']."'
            GROUP BY
            basic_permission.code
            ORDER BY
            basic_permission.code ASC
        
        ";
        //echo $sql;
        $res = mysql_query($sql,$CONN);
        $data = array();
        while($temp = mysql_fetch_assoc($res)){
            //$data[] = $temp;continue;
            $len = strlen($temp['code']);
            if($len==2){
                $data[] = $temp;
            }else if($len==4){
                $pos_1 = count($data)-1;
                $data[$pos_1]['children'][] = $temp;
            }else if($len==6){
                $pos_1 = count($data)-1;
                $pos_2 = count($data[$pos_1]['children'])-1;
                $data[$pos_1]['children'][$pos_2]['children'][] = $temp;
            }else if($len==8){
                $pos_1 = count($data)-1;
                $pos_2 = count($data[$pos_1]['children'])-1;
                $pos_3 = count($data[$pos_1]['children'][$pos_2]['children'])-1;
                $data[$pos_1]['children'][$pos_2]['children'][$pos_3]['children'][] = $temp;
            }
        }
        return $data;
    }
    
    public function login(){
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];     

        //如果内存表是空的,说明系统刚启动,需要初始化
        $sql = "select count(*) as c from basic_memory ";
        $res = mysql_query($sql,$CONN);
        $data = mysql_fetch_assoc($res);
        if($data['c']==0){
            mysql_query("call basic_memory__init()",$CONN);
            include_once 'basic_memory.php';
            $m = new basic_memory();
            $m->il8n();
        }
        
        $sql ="call basic_user__login('".$username."','".$password."','".$_SERVER['REMOTE_ADDR']."',@msg,@state)";
        mysql_query($sql,$CONN);
        $res = mysql_query("select @state as state,@msg as msg",$CONN);
        $arr = mysql_fetch_array($res,MYSQL_ASSOC);
        if($arr['state']==1){
            $sql = "
                SELECT
                basic_user.money,
                basic_user.money2,
                basic_user.person_id,
                basic_user.group_id,
                basic_user.group_code,
                basic_user.group_name,
                basic_user.id,
                basic_user.lastlogintime,
                basic_user.time_created,
                basic_user.lastlogouttime,
                basic_person.cellphone,
                basic_person.email,
                basic_person.photo,
                basic_person.name,
                basic_person.birthday,
                basic_user.status,
                (select value from basic_parameter where code = basic_user.status and reference = 'basic_user__status') as statusname,
                basic_user.type,
                basic_user_session.groups,
                basic_user_session.ip,
                basic_user_session.permissions
                FROM
                basic_user
                Left Join basic_person ON basic_user.person_id = basic_person.id
                Left Join basic_user_session ON basic_user.id = basic_user_session.id_user
                WHERE
                basic_user.username =  '".$_REQUEST['username']."'            
            ";
            //echo $sql;
            $res2 = mysql_query($sql,$CONN);
            $arr2 = mysql_fetch_array($res2,MYSQL_ASSOC);

            $arr['loginData'] = $arr2;
            $arr['permission'] = $this->getPermission($_REQUEST['username']);
            $arr['il8n'] = tools::getLanguage();
        }
        header("Content-type:text/json");
        echo json_encode($arr);
    }
    
    public function logout(){
        
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $session = $_REQUEST['session'];        
        mysql_query("call basic_user__logout('".$username."','".$session."',@msg,@state)",$CONN);
        $res = mysql_query("select @state as state,@msg as msg" ,$CONN);
        $arr = mysql_fetch_array($res,MYSQL_ASSOC);
        
        header("Content-type:text/json");
        echo json_encode($arr);
    }
    
    public function changePWD(){
        $CONN = tools::conn();
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        
        mysql_query("update basic_user set password = '".$password."' where username = '".$username."' ",$CONN);
    }
	
    public function loadConfig($return='json') {
        $CONN = tools::conn();
        $config = array();
        
        $sql = "select code,value from basic_parameter where reference = 'basic_user__type' and code not in ('1','9')  order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['type'] = $data;
		
		$sql = "select code,value from basic_parameter where reference = 'basic_user__status' order by code";
        $res = mysql_query($sql,$CONN);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		$config['status'] = $data;
		
		if($return=='json'){
		    header("Content-type:text/json");
		    echo json_encode($config);
		}else{
		    return $config;
		}
	}  

    /**
     * 接收前端用AJAX方式发送过来的文件,
     * 存储在系统的 upload 位置
     * 
     * 前端和服务端都需要引用 qqFileUploader 这个控件
     * */
    public function import(){
        tools::checkPermission("120101");
        include_once '../libs/ajaxUpload/php.php';
        $allowedExtensions = array("xls");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        sleep(2);
        $result = $uploader->handleUpload('../file/upload/');
        // to pass data through iframe you will need to encode all html tags
       
		basic_excel::import($uploader->savePath);
		
		$CONN = tools::conn(); 
		mysql_query("call basic_user__import('".basic_excel::guid."',@state,@msg,@ids)",$CONN);
    }
    
    public function export() {
        
        $CONN = tools::conn();
        include_once '../libs/guid.php';
        $Guid = new Guid();  
        $guid = $Guid->toString();
        $search=$_REQUEST['search'];
        //数据库连接口,在一次服务端访问中,数据库必定只连接一次,而且不会断开
        $conn = tools::conn();
        
        //列表查询下,查询条件必定是SQL拼凑的
        $sql_where = " where 1=1 ";
        //判断前端传递过来的查询条件内容,格式是否正确,因为格式必须是一个 JSON 
        if(!tools::isjson($search))tools::error('grid,search data, wrong format');
        $search=json_decode($search,true);
        $search_keys = array_keys($search);
        for($i=0;$i<count($search);$i++){
            if($search_keys[$i]=='username' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.username like '%".$search[$search_keys[$i]]."%' ";
            }
            if($search_keys[$i]=='group_code' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.group_code like '%".$search[$search_keys[$i]]."%' ";
            }	
            if($search_keys[$i]=='status' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.status = '".$search[$search_keys[$i]]."' ";
            }  
            if($search_keys[$i]=='type' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.type = '".$search[$search_keys[$i]]."' ";
            }  
            if($search_keys[$i]=='money' && trim($search[$search_keys[$i]])!='' ){
                $sql_where .= " and basic_user.money > '".$search[$search_keys[$i]]."' ";
            }                      	
        }
        $sql_order = ' order by basic_user.id desc ';  
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
        ) values (
			'".$guid."'     
			,'1'       
			,'0'
			,basic_memory__il8n('user','basic_user',1)
			,1
			,5  

            ,basic_memory__il8n('username','basic_user',1)
            ,basic_memory__il8n('password','basic_user',1)
            ,basic_memory__il8n('type','basic_user',1)
            ,basic_memory__il8n('money','basic_user',1)
            ,basic_memory__il8n('group_code','basic_user',1)
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
        ) select 
            '".$guid."'
            ,'1'
            ,'0'   
            ,basic_memory__il8n('user','basic_user',1)
            ,1      
            ,5
            
            ,username
            ,password
            ,basic_memory__il8n(type,'basic_user__type',3)
            ,money
            ,group_code
          
		from basic_user  ".$sql_where.$sql_order." limit 1000";
        
        mysql_query($sql,$CONN);
        include_once 'basic_excel.php';
        $file = basic_excel::export($guid);
        
        header("Content-type:text/json");
        echo json_encode(array('path'=>$file,'state'=>1,'file'=>'download'));
    }
    
    /**
     * 添加一个用户,往数据库中插入一条记录
     * */
    public function insert($data=NULL,$retur='json') {
        if ($data==NULL) {
            $data = json_decode($_REQUEST['data'],true);
        }
        
        $CONN = tools::conn();    
        mysql_query("call basic_user__insert(
        	'".$data['username']."'
        	,'".$data['password']."'
        	,'".$data['type']."'
        	,'".$data['cellphone']."'
        	,'".$data['email']."'
        	,@state,@msg)",$CONN);
        $res = mysql_query("select @state as state, @msg as msg",$CONN);
        $data = mysql_fetch_assoc($res);
        
        sleep(1.5);
        echo json_encode($data);
    }
    
    public function view(){
        $CONN = tools::conn();    
        $id = $_REQUEST['id'];
        $sql = "
        SELECT
        basic_user.username,
        basic_user.money,
        basic_user.money2,
        basic_user.person_name,
        basic_user.person_cellphone,
        basic_user.person_email,
        basic_user.group_code,
        basic_user.group_name,
        basic_user.group_all,

        basic_user.lastlogintime,
        basic_user.lastlogouttime,
        basic_user.count_actions,
        basic_user.count_actions_period,
        
        basic_person.birthday,
        basic_person.degree_school,
        basic_person.photo,
        basic_person.address,
        basic_memory__il8n(gender,'basic_person__gender',3) as  gender,
        basic_memory__il8n(ismarried,'basic_person__ismarried',3) as  ismarried,
        basic_memory__il8n(degree,'basic_person__degree',3) as  degree,
        basic_memory__il8n(politically,'basic_person__politically',3) as  politically,                     
        
        basic_user.id,
        basic_user.`type`,
        basic_user.id_creater,
        basic_user.id_creater_group,
        basic_user.code_creater_group,
        basic_user.time_created,
        basic_user.time_lastupdated,
        basic_user.count_updated,
        basic_user.`status`,
        basic_user.remark
                
        FROM
        basic_user
        Left Join basic_person ON basic_person.id = basic_user.person_id
        where basic_user.id = '".$id."'
        ";
        $res = mysql_query($sql, $CONN );

        $data= mysql_fetch_assoc($res);
        $data['sql'] = $sql;
        
        header("Content-type:text/json");
        echo json_encode($data);
    }
    
    public function update($data=NULL,$retur='json') {
        tools::checkPermission("120101");//TODO
        if ($data==NULL) {
            $data = json_decode($_REQUEST['json'],true);
        }
        $id = $data['id'];
        unset($data['id']);
        if(isset($data['password']))$data['password'] = md5($data['password']);
        $data['time_lastupdated'] = date('YYYY-MM-dd');
        $il8n = tools::getLanguage(); 
        $CONN = tools::conn();    
        //为了防止用户误点,导致重复提交,设置 2.5 秒的服务端暂停
        sleep(2.5);       
        
        $keys = array_keys($data);        
        $sql = "update basic_user set ";
        for($i=0;$i<count($keys);$i++){
            $sql.= $keys[$i]."='".$data[$keys[$i]]."',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql .= " where id =".$id;    
        mysql_query($sql,$CONN);
        
        echo json_encode(array('sql'=>$sql,'state'=>1));
    }    
}