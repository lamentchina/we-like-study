<?php
/**
 * All the interfaces are used by model.
 * This interface is for the database's table
 * */
interface dbtable{
	
	/**
	 * Insert one item to the table
	 * The table's columns must fit $data's keys
	 * 
	 * @param $data 
	 * @return bool
	 * */
	public function insert($data);
	
	/**
	 * Delete by id.
	 * 
	 * @param $ids Eache talbe have the id column 
	 * @return bool
	 * */
	public function delete($ids);
	
	public function update($data);
	
	public function create();
	
	public function importExcel($path);
	
	public function exportExcel();
	
	public function cumulative($column);
	
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*");
	
}


interface levelList{
	
	public function getLevelList($root);
	
}


interface log{
	
	public function addLog($whatHappened);
	
}

interface integrate{	

	public function synchroConfig($path);
	
	public function synchroUsers();
	
	public function synchroUserGroups();
	
	public function synchroPrivileges();

	public function synchroMe($resetUserSession = false);
	
	public function synchroMoney($username);
}


interface quizdo{
	
	public function exportQuiz($type);
	
	public function getMyDoneList($page=null,$pagesize=null,$search=null,$orderby=null);
	
	public function getDoneList($page=null,$pagesize=null,$search=null,$orderby=null);
	
	public function getQuizIds();
}
?>