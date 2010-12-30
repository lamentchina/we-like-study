<?php
/**
 * 每做完一道题目,都要生成一条日志 
 * */
class question_record extends wls {
		
	/**
	 * 创建一张新的日志表
	 * 由于日志都是数据量非常大的表,因此会每隔一段时间建一张表,
	 * 通常是每隔一个月
	 * */
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		$sql = "drop table if exists ".$pfx."wls_question_record;";
		mysql_query($sql,$conn);
		$sql = "		
			create table ".$pfx."wls_question_record(
				 id int primary key auto_increment	comment '自动编号'
				,date_created datetime 				comment '创建时间'
				 
				,id_user int default 0				comment '用户编号'
				,id_usergroup int default 0			comment '用户所在用户组的编号'
				
				,id_question int default 0			comment '题目编号'
				,id_question_parent int default 0	comment '上级题目所在编号'
				
				,id_quiz_exam int default 0			comment '考试科目编号'
				,id_quiz_paper int default 0		comment '试卷编号'
				,examtype int default 0				comment '题型'
				
				,myanswer text 						comment '回答'
				,answer text 						comment '答案'
				,correct int default 0				comment '正确否 1正确 0错误 2放弃'
				
				,ids_knowledge text					comment '题目涉及的知识点'
				,distribute text 					comment '题目知识点的分配'
	
				,application int default 0			comment ' 用途:1随机练习,2题型掌握度练习,3知识点掌握度练习,4试卷练习,5参加在线考试'
						
			) DEFAULT CHARSET=utf8 					comment='题目记录,每个人每做一道题,都要记录下.这是所有统计分析功能的基础';
			";
		mysql_query($sql,$conn);
	}
	
	/**
	 * 创建一条日志记录
	 * 由于日志记录一般计算量比较大,详细的算法可能不会写在PHP后台,而是在数据库的存储过程中
	 * */
	public function add($id_question,$myanswer,$correct,$application){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		
		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUser('mine');
		
		$sql = "select id_parent,answer,id,id_quiz_paper from ".$pfx."wls_question where id = ".$id_question;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$sql = "insert into ".$pfx."wls_question_record (
			date_created,id_user,id_usergroup,id_question,id_question_parent,id_quiz_paper,answer,myanswer,correct,application
		) values(
			'".date('Y-m-d h:i:s')."'
			,".$userinfo['id_user']."
			,".$userinfo['id_group']."
			,".$id_question."
			,".$temp['id_parent']."
			,".$temp['id_quiz_paper']."
			,'".$temp['answer']."'
			,'".$myanswer."'
			,".$correct."
			,'".$application."'
		);";
		mysql_query($sql,$conn);
	}
	
	/**
	 * 得到一条日志记录
	 * 日志记录一般都存JSON数据,这种数据拿到前台可以直接使用
	 * 根据 returnType ,可以返回 JSON 或 ARRAY
	 * */
	public function get(){
		
	}
	
	/**
	 * 按照时间颗粒来统计某些数值
	 * 时间颗粒一般有:
	 *   小时,上下午,天,星期,旬,月,年
	 * */
	public function summaryByPeriod(){
		
	}
}
?>