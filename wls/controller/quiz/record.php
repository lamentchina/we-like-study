<?php
/**
 * 记录每次测验的测验结果
 * 测验包括 : 考卷测验 错题本联系 题型训练 多人在线考试等等
 * */
class quiz_record extends wls {

	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_quiz_record;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_record(
				 id int primary key auto_increment	comment '自动编号'
				,date_created datetime 				comment '创建时间'

				,id_user int default 0				comment '用户编号'
				,id_user_group int default 0		comment '用户所在用户组的编号'

				,id_quiz_paper int default 0		comment '试卷编号'
				,title_quiz_paper varchar(200) default '试卷0' comment '试卷名称'
				,id_quiz_type int default 0			comment '科目编号'
				,title_quiz_type varchar(200) default '科目0' comment '科目名称'
				
				,count_total int default 0			comment '题目总数' 
				,count_wrong int default 0 			comment '做错题目总数'
				,count_right int default 0
				,count_giveup int default 0
				,cent float default 0				comment '试卷总分'
				,mycent float default 0				comment '得分'		
				,timer int default 0				comment '考试所花时间'		
				,proportion float default 0			comment '正确率'
				
				,application int default 0			comment ' 用途:1随机练习,2题型掌握度练习,3知识点掌握度练习,4试卷练习,5参加在线考试'	
			) DEFAULT CHARSET=utf8 					comment='试卷使用日志';
			";
		mysql_query($sql,$conn);
	}

	/**
	 * 添加一条记录
	 * */
	public function add(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo('mine');	
		
		$sql = "select id_quiz_type , title_quiz_type from ".$pfx."wls_quiz_paper where id = ".$_REQUEST['id'];
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$sql = "
		insert into ".$pfx."wls_quiz_record (
			date_created,
			id_user,
			id_user_group,
			id_quiz_paper,
			title_quiz_paper,
			id_quiz_type,
			title_quiz_type,
			count_total,
			count_wrong,
			count_right,
			count_giveup,
			mycent,
			cent,
			timer,
			proportion
		) values(
			 '".date('Y-m-d h:i:s')."'
			,".$userinfo['id_user']."
			,".$userinfo['id_group']."
			,".$_REQUEST['id']."
			,'".$_REQUEST['title']."'
			,'".$temp['id_quiz_type']."'
			,'".$temp['title_quiz_type']."'
			,".$_REQUEST['count_total']."
			,".$_REQUEST['count_wrong']."
			,".$_REQUEST['count_right']."
			,".$_REQUEST['count_giveup']."
			,".$_REQUEST['mycent']."
			,".$_REQUEST['cent']."
			,".$_REQUEST['timer']."
			,".($_REQUEST['count_right']/($_REQUEST['count_right']+$_REQUEST['count_wrong']))."
		);";
		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		
		if(isset($_REQUEST['type']) && $_REQUEST['type']=='paper'){
			$sql = "select * from ".$pfx."wls_quiz_paper where id = ".$_REQUEST['id'];
			$res = mysql_query($sql,$conn);
			$temp = mysql_fetch_assoc($res);
			$topstr = '';
			if($temp['score_top']<=$_REQUEST['mycent']){
				$topstr = "
				, score_top = ".$_REQUEST['mycent'].",
				score_top_user = '".$userinfo['id_user']."'
				";
			}
			
			$sql = "update ".$pfx."wls_quiz_paper set 
			score_avg = ".($temp['score_avg']*$temp['count_used']+$_REQUEST['mycent'])/($temp['count_used']+1).",
			count_used = ".($temp['count_used']+1)." ".$topstr;			
			
			$sql .= " where id = ".$_REQUEST['id'];
			mysql_query($sql,$conn);			
			$sql = "update ".$pfx."wls_user set count_papers = count_papers+1 where id_user =".$userinfo['id_user'];
			mysql_query($sql,$conn);
		}
	
		echo json_encode(
			array(
				'ok'=>1,
				'id'=>$id,
				'a'=>$userinfo,
				'sql'=>$sql,
			)
		);
	}

	/**
	 * 查看我个人已完成的试卷
	 * */
	public function getList($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['page']))$page=$_REQUEST['page'];
		if($rows==null && isset($_REQUEST['rows']))$rows=$_REQUEST['rows'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_decode($_REQUEST['search'],true);
		if($page==null)$page = 1;
		if($rows==null)$rows = 20;
		if($returnType==null)$returnType = 'array';

		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();
		//$search = array('id_user'=>$userinfo['id_user']);

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_user'){
					$where .= " and id_user in (".$search[$keys[$i]].") ";
				}
			}
		}

		$sql = "select * from ".$pfx."wls_quiz_record ".$where;
		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		$sql = "select count(*) as total from ".$pfx."wls_quiz_record ".$where;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];
		$pagenum = floor($total/$rows);
		if($pagenum<($total/$rows))$pagenum++;
			
		switch($returnType) {
			case 'json':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$data,
					'sql'=>$sql,
					'total'=>$total,
				);
				unset($arr);
				echo json_encode($arr2);
				break;			
			case 'array':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$data,
					'pagesize'=>$rows,
					'sql'=>$sql,
					'total'=>$total,
					'pagenum'=>$pagenum,
				);
				return $arr2;
				break;	
			default:
				echo 'returnType is not defined';
				break;
		}
	}
	
	/**
	 * 显示我最近做题的列表
	 * 前台使用DWZ框架
	 * */
	public function getDWZlist($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['pageNum']))$page=$_REQUEST['pageNum'];
		if($rows==null && isset($_REQUEST['numPerPage']))$rows=$_REQUEST['numPerPage'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_encode($_REQUEST['search']);
		if($page==null)$page = 1;
		if($rows==null)$rows = 10;
		if($returnType==null)$returnType = 'html';
		
		$data = $this->getList('array',$page,$rows,$search);
		
		include_once 'view/quiz/paper/record/list.php';
	}
	
	/**
	 * 查看我个人最近的学习成绩变化曲线
	 * */
	public function getChart(){
		$html = '			
			考试科目:
			<select onchange="foo();">
				<option value="1">CET4</option>
				<option value="2">CET6</option>
			</select>
			<input type="radio" name="w_q_r_c_r" onchange="foo();"  />排名<input type="radio" name="w_q_r_c_r"  onchange="foo();"/>
			历次测验正确率<input type="radio" name="w_q_r_c_r"  onchange="foo();"/>知识点掌握度<input type="radio" name="w_q_r_c_r"  onchange="foo();"/>时间投入
			<div class="divider"></div>
			<b>历次测验学习成绩:</b>
			<div id="w_q_r_c" style="width:98%;height:200px;"></div>
			<div class="divider"></div>
			<script type="text/javascript">
			function foo(){
				alertMsg.info("此功能尚未完成,请关注QQ群:127222072！");
			}
			</script>
		';
		include_once 'controller/user.php';
		$obj = new user();
		$html .= $obj->getProfile();
		echo $html;
	}
}
?>