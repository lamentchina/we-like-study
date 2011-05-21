<?php
/**
 * 往系统中插入一些测试数据
 * 这个仅仅是一个测试用文件,只是在开发过程中需要使用而已,
 * 在开发结束之后应该将此文件删除
 * 
 * 此文件的存在会给系统带来安全性威胁
 * 
 * @author wei1224hf
 * @see www.welikestudy.com
 * */ 
class oop {
	
	/**
	 * 连接数据库,返回数据库连接配置
	 * 
	 * @return conn
	 * */
	private function conn(){
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('ultrax',$conn);
		mysql_query('SET NAMES UTF8');
		
		return $conn;
	}
	
	/**
	 * 模拟某一个用户做了某一个关卡
	 * 这是一个后台函数,在AJAX中会被前台频繁的调用
	 * 根据 用户名查到这个用户的用户编号,根据 关卡序号和科目名称查到对应的关卡
	 * 然后判断一下用户能不能参与此关卡,因为大部分关卡是要付费的,不是默认参与的
	 * 然后将这个关卡的所有单词取出来
	 * 然后开始模拟用户对每个单词的做题情况设置答案
	 * 根据参数 正确率,来设置用户做题作对的概率
	 * 
	 * 
	 * 需要读取数据的表有:
	 *  用户表 wls_user
	 *  科目表 wls_subject
	 *  关卡表 wls_glossary_levels
	 *  词汇表 wls_glossary
	 *  
	 * 将要写入数据的表有:
	 *  关卡日志表 wls_glossary_levels_logs
	 *  词汇表日志 wls_glossary_logs
	 *  关卡表 wls_glossary_levels
	 *  
	 * 在关卡日志表中,记录统计后的信息
	 * 在词汇日志表中,记录每个用户做每道题的信息,数据量会非常大
	 * 如果用户通过了某关卡,就要更新关卡表中的统计信息:通过人数,
	 *  然后在关卡日志表中新增下个关卡
	 * 
	 * @param username 用户名
	 * @param level 关卡序号
	 * @param subject 科目名称
	 * @param accuracy 正确率,一个在 0 和 100 之间的数
	 * @return isPassed 是否通过.如果没有通过,应该会再次调用此函数,直到通过为止
	 * */
	public function simulate1UserDo1Level($username=null,$level=null,$subject=null,$accuracy=null){
		$conn = $this->conn();
		
		//获得变量的值
		if( (!$username) && isset($_REQUEST['username']))$username = $_REQUEST['username'];
		if( (!$level) && isset($_REQUEST['level']))$level = $_REQUEST['level'];
		if( (!$subject) && isset($_REQUEST['subject']))$subject = $_REQUEST['subject'];
		if( (!$accuracy) && isset($_REQUEST['accuracy']))$accuracy = $_REQUEST['accuracy'];
		if( (!$username) || (!$level) || (!$subject) || (!$accuracy) ){
			die('Missing paramer!');
		}
		
		//根据用户名得到用户编号
		$sql = "select id from pre_wls_user where username = '".$username."';";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$data['id_user'] = $temp['id'];

		//根据科目名得到科目编号
		$sql = "select id_level from pre_wls_subject where name = '".$subject."';";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$data['id_subject'] = $temp['id_level'];
		
		//根据科目编号和关卡序号,得到关卡的分数线和金币需求
		$sql = "select money,passline from pre_wls_glossary_levels where subject = '".$data['id_subject']."' and level = ".$level." ;";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$data['money_level'] = $temp['money'];
		$data['passline_level'] = $temp['passline'];
		
		//根据关卡序号和科目编号,得到此关卡的所有词汇信息,
		//这些信息要插入到 词汇日志表 中去
		$sql = "select id,word,translation from pre_wls_glossary where subject = '".$data['id_subject']."' and level = ".$level." and id_user = ".$data['id_user']." ; ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		$data['count_right'] = 0;
		while($temp = mysql_fetch_assoc($res)){	
			//判断一下此用户有关这个词汇的日志是否已记载
			$sql2 = "select id from pre_wls_glossary_logs where id_user = ".$data['id_user']." and id_word = ".$temp['id'];
			$res2 = mysql_query($sql2,$conn);
			$temp2 = mysql_affected_rows($conn);
			//如果已经有记载,就更新记录
			if($temp2){
				$result = rand(1, $accuracy);
				$temp['right'] = ($result>50)?1:0;
				if($temp['right']){
					$data['count_right'] ++;
					$sql3 = "update pre_wls_glossary_logs set count_right = count_right + 1;";
				}else{
					$sql3 = "update pre_wls_glossary_logs set count_wrong = count_wrong + 1;";
				}
				mysql_query($sql3,$conn);				
			}else{
				//如果没有记载,就插入一条记录
				$sql3 = "insert into pre_wls_glossary_logs (
						 id_word
						,subject
						,level
						,id_user
						,username
						,word
						,translation
					)values(
						 '".$temp['id']."'
						,'".$data['id_subject']."'
						,".$level."
						,'".$data['id_user']."'
						,'".$username."'
						,'".$temp['word']."'
						,'".$temp['translation']."'
					) ";
				mysql_query($sql3,$conn);	
			}
			$arr[] = $temp;
		}
		$data['glossary'] = $arr;
		
		//判断一下此用户这一次模拟关卡有没有通过
		$data['passed'] = 0;
		if( ($data['count_right'] * 100)/count($data['glossary']) > $data['passline_level'] )$data['passed'] = 1;
		print_r($data);exit();
		//最后是 关卡日志表
		//先判断一下此用户有关此关卡的日志是否存在
		$sql = "select id from pre_wls_glossary_levels_logs where subject = '".$data['id_subject']."' and level = ".$level." and id_user = ".$data['id_user'];
		$res = mysql_query($sql,$conn);
		//如果存在,就更新一下关卡统计信息
		$sql2 = "";
		if ( mysql_affected_rows($res) ){
			if($data['passed']){
				$sql2 = "upate pre_wls_glossary_levels_logs set count_right = count_right + 1 ";
			}else{
				$sql2 = "upate pre_wls_glossary_levels_logs set count_wrong = count_wrong + 1 ";
			}
		}		
	}
	
	/**
	 * 模拟某个用户做某个关卡,
	 * 第一次做可能是失败,失败后就再模拟做一次
	 * 直到这个用户通过了此关卡
	 * 
	 * 这是一个前台函数
	 * 直接向前台输出HTML内容,HTML使用Jquery来做重复的AJAX访问
	 * 
	 * @param $_GET username 用户名
	 * @param $_GET level 关卡序号
	 * @param $_GET subject 科目名称
	 * */
	public function simulate1UserDo1LevelUntialPassed(){
		$html = "";
		//TODO
		echo $html;
	}
	
	
	/**
	 * 模拟某一个用户做了所有关卡
	 * 直接调用上一个函数实现
	 * 
	 * @param username 用户名
	 * */
	public function simulate1UserDoAllLevel(){
		//TODO
	}
	
	/**
	 * 模拟所有用户做了所有关卡
	 * */
	public function simulateAllUserDoAllLevel(){
		//TODO
	}
}
$obj = new oop();
$obj->simulate1UserDo1Level("admin",1,"CET4",70);
?>