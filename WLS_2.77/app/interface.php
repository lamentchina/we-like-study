<?php

/**
 * 数据库层的接口定义
 * 简单的 增删改 操作函数定义
 * */
interface dbtable{
	
	public function insert($data);
	
	public function delete($ids);
	
	public function update($data);	
	
	public function create();
	
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*");	
}

/**
 * 文件的导入导出
 * 基本上没给个业务表(练习卷 考试卷 考试排名等)都会涉及  数据导入导出 功能
 * 导入导出的格式主要以EXCEL为主,也会涉及其他 world pdf等格式
 * */
interface fileLoad{
		
	public function importAll($path);
	
	public function exportAll($path=null);
	
	public function importOne($path);
	
	public function exportOne($path=null);
}


interface levelList{	
	
	public function getLevelList($root);	
}


interface log{	
	
	public function addLog($whatHappened);	
}

/**
 * WLS系统跟其他系统集成的方式是采用其自带的桥接程序
 * 跟其他系统桥接的时候,主要就是将其他系统一些简单的 用户数据 用户组数据 给同步到WSL中
 * 同步过程中,最棘手的部分是同步 SESSION 信息
 * */
interface integrate{	
	
	public function bridge();

	public function synchroConfig($path);
	
	public function synchroUsers();
	
	public function synchroUserGroups();
	
	public function synchroAccess();

	public function synchroMe($resetUserSession = false);
	
	public function synchroMoney($username);
}

//TODO 用不上了,下个版本中删除
interface quizdo{
	
	public function exportQuiz($type);
	
	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null);
	
	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null);
	
	public function getQuizIds();
}
?>