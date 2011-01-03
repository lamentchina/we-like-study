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
	 * */
	public function insert($data);
	
	/**
	 * 只能根据编号来删除数据,一次性可以删除多条
	 * 
	 * @param $ids 编号,每张表都id这个列,一般为自动递增
	 * */
	public function delete($ids);
	
	/**
	 * 更新一条数据
	 * 
	 * @param $data 一个数组,其键值与数据库表中的列一一对应,肯定含有$id 
	 * */
	public function update($data);
	
	/**
	 * 创建这张数据库表
	 * 创建过程中,会先尝试删除这张表,然后重新建立.
	 * 因此在运行之前需要将数据备份
	 * 如果配置文件中的state不是debug,无法执行这类函数
	 * */
	public function create();
	
	/**
	 * 导入一张EXCEL,并将数据全部填充到表中去
	 * EXCEL已经成为数据存储标准,每个办公人员都会用
	 * 这是实现批导入最方便的形式
	 * 
	 * @param $path EXCEL路径
	 * */
	public function importExcel($path);
	
	/**
	 * 累加某个值
	 * 
	 * @param $column 列名称
	 * */
	public function cumulative($column);
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
 * 
 * */
interface log{
	
}
?>