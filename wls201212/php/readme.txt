后台PHP编码的规则:

1 禁止使用 interface 定义接口,禁止使用 get set 之类的函数
所有的属性,都设置为 public 访问模式

2 OOP编程中,继承层级,最多继承1级

3 禁止使用 xml 的配置文件,使用 ini 格式的配置文件

4 一个PHP CLASS 中,常用的函数及其说明:

/**
 * 前端对服务端访问的时候,必定待的几个参数:
 * id_user 用户编号
 * id_group 用户所在组织机构的用户组编码
 * username 用户名,主要用于判断当前权限的
 * usertype 用户类型,不同的业务系统,有不同的类型
 * actioncode 操作编码,根据 basic_permission 的权限编码
 * session 非WEB服务器自带的session,来自 basic_user_session 的数据库session
 *
 * 服务端返回给前端的数据,都含有 status,msg 这两个参数
 */
class myclass{
    
    /**
     * 将这个业务模块的配置内容,以JSON的格式返回
     * 如果 return 是 'array' , 则返回一个哈希表
     */
    public function loadConfig(return='json'){
    
    }
    
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
            tools::checkPermission('XX50',$_REQUEST['username'],$_REQUEST['session']);
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
            if($search_keys[$i]=='keyname1'){
                $sql_where .= " and keyname1 like '%".$search['keyname1']."%' ";
            }
        }
        
        //根据不同的用户角色,会有不同的列输出
        if($_REQUEST['usertype']=='1'){ 
            $sql = "select column1,column2 from tablename ".$sql_where." limit ".($page*$pagesize)." ".$pagesize;
            $res = mysql_query($sql,$conn);
            $data = mysql_fetch_assoic($res);
            //做一些数据格式转化,比如 长title 的短截取,时间日期的截取,禁止在此插入HTML标签
            for($i=0;$i<count($data);$i++){
                $data[$i]['title'] = tools::cutString($data[$i]['title'],10);
            }
            
            $sql_total = "select count(id) as total from tablename ".$sql_where;
            $res = mysql_query($sql_total,$conn);
            $total = mysql_fetch_assoic($res);
            
            $returnData = array(
                'Rows'=>$data,
                'Total'
            );
            
        }
    }   
    
    /**
     * 任何对系统数据的 写 操作,都将迁移到数据库层操作,
     * 因为一般而言,除了对当前业务数据库表的简单处理,还要对 日志表,工作流程表,用户session内存表 操作
     *
     * return 返回给前端的一般就是JSON数据
     * ids 系统的数据库业务表,其主键必定是 id ,前端的 删除 操作,必定是在表格中,用 checkbox 的方式,
     * 可以批量删除多条的操作
     */
    public function delete(return='json',ids=NULL){
    
    }
    
    /**
     * 显示一条记录
     */
    public function view(return='json',id=NULL){
    
    }

    public function update(return='json',id=NULL){
    
    }
    
    public function insert(return='json',data=NULL){
    
    }
}


$data = array("Rows"=>$data,"Total"=>$total,'sql'=>preg_replace("/\s(?=\s)/","",preg_replace('/[\n\r\t]/'," ",$sql)));
,11,1101,1199,13,1301,130101,130102,130111,130112,130121,130122,130123,130190,1302,130201,130202,130211,130212,130221,130222,130223,130290,15,1501,1502,1511,1512,1521,1522,1523,16,1601,1602,1611,1612,1621,1622,1623,17,1701,1702,1711,1712,1721,1722,1723,18,1801,1802,1811,1812,1821,1822,1823,1823,