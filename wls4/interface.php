<?php
/**
 * 数据库表的接口,
 * 系统中每张数据库表都对应着一个model,
 * 但是有些类没有数据库表,
 * Model中的任何函数都不会直接向浏览器输出内容,
 * 一般都要放回一个结果 
 * */
interface dbtable{
	
	/**
	 * 插入一条数据
	 * 
	 * @param $data 一个数组,其键值与数据库表中的列一一对应
	 * @return bool
	 * */
	public function insert($data);
	
	/**
	 * 只能根据编号来删除数据,一次性可以删除多条
	 * 
	 * @param $ids 编号,每张表都id这个列,一般为自动递增
	 * @return bool
	 * */
	public function delete($ids);
	
	/**
	 * 更新一条数据
	 * 
	 * @param $data 一个数组,其键值与数据库表中的列一一对应,肯定含有$id 
	 * @return bool
	 * */
	public function update($data);
	
	/**
	 * 创建这张数据库表
	 * 创建过程中,会先尝试删除这张表,然后重新建立.
	 * 因此在运行之前需要将数据备份
	 * 如果配置文件中的state不是debug,无法执行这类函数
	 * 每张表中都有字段id,作为主索引
	 * 
	 * @return bool
	 * */
	public function create();
	
	/**
	 * 导入一张EXCEL,并将数据全部填充到表中去
	 * EXCEL已经成为数据存储标准,每个办公人员都会用
	 * 这是实现批导入最方便的形式
	 * 
	 * @param $path EXCEL路径
	 * @return bool
	 * */
	public function importExcel($path);
	
	/**
	 * 导出一张EXCEL文件,
	 * 提供下载,实现数据的多处同步,并让这个EXCEL文件形成标准
	 * 
	 * @return $path
	 * */
	public function exportExcel();
	
	/**
	 * 累加某个值
	 * 
	 * @param $column 列名称
	 * @return bool
	 * */
	public function cumulative($column);
	
	/**
	 * 得到列表,
	 * 也充当了读取单行数据的角色
	 * 
	 * @param $page 页码,为整数
	 * @param $pagesize 页大小
	 * @param $search 查询条件
	 * @param $orderby 排序条件
	 * @param $columns 列
	 * 
	 * @return $array 
	 * */
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*");
	
}

/**
 * 级层列表,
 * 用户组,科目,知识点 这些表都有级层关系,
 * 这种表都有一个列 id_level ,通过这个列来算出其上级和下级关系
 * */
interface levelList{
	
	/**
	 * 获得具有级层关系的列表
	 * 
	 * @param $root 根元素
	 * */
	public function getLevelList($root);
	
}

/**
 * 日志,
 * 试卷,题目,错误操作等都有自己的日志
 * */
interface log{
	
	/**
	 * 创建一条日志
	 * 
	 * @param $whatHappend 事件类型
	 * */
	public function addLog($whatHappened);
	
}

/**
 * 集成安装
 * 系统本身可以独立运行,但也可以以依附的形式集成到其他CMS系统中,
 * 比如Discuz,DiscuzX,PhpWind,Joomla等
 * 安装过程都是在controller 上直接完成的,不需要 model
 * */
interface integrate{
	
	/**
	 * 同步配置文件
	 * */
	public function synchroConfig($path);
	
	/**
	 * 同步用户数据
	 * */
	public function synchroUsers();
	
	/**
	 * 同步用户组数据
	 * */
	public function synchroUserGroups();
	
	/**
	 * 同步权限数据
	 * */
	public function synchroPrivileges();	
}


/**
 * 测验卷操作
 * 主要有: 试卷,在线多人考试,错题本,知识点练习,随机做试卷,
 * */
interface quizdo{
	
	/**
	 * 导出这张试卷,允许用户下载
	 * 
	 * @param $type 类型,可以是 WORD,PDF,EXCEL等
	 * @return $path 
	 * */
	public function exportQuiz($type);
	
	/**
	 * 得到我个人的已做的列表
	 * 
	 * @param $page 页码,为整数
	 * @param $pagesize 页大小
	 * @param $search 查询条件
	 * @param $orderby 排序条件
	 * @return $array 
	 * */
	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null);
	
	/**
	 * 得到已经被做过了的列表,
	 * 一般为管理员操作,支持查询
	 * 
	 * @param $page 页码,为整数
	 * @param $pagesize 页大小
	 * @param $search 查询条件
	 * @param $orderby 排序条件
	 * @return $array 
	 * */
	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null);
	
	/**
	 * 得到题编号
	 * 
	 * @return $ids 一组题目编号
	 * */
	public function getQuizIds();
}
?>