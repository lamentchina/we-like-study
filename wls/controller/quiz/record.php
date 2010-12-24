<?php
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
	 * 已完成的试卷
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
			case 'jsonflexgrid'://jquery flexgrid 对JSON有特殊的要求
				$rows = array();
				$keys = array_keys($data[0]);
				for($i=0;$i<count($data);$i++){
					$cell = array();
					for($j=0;$j<count($keys);$j++){
						$cell[] = $data[$i][$keys[$j]];
					}
					$rows[] = array(
						'id'=>$data[$i]['id'],
						'cell'=>$cell,
					);
				}
				$arr2 = array(
					'page'=>$page,
					'rows'=>$rows,
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
			case 'html':
				$arr = $data;
				$html = "<div id='w_q_r_l' >已完成试卷";
				$html .= "<table style='width:100%' width='100%' cellpadding='0' cellspacing='0' border='0'>
							<tr>
								<td>标题</td><td>得分/总分</td><td>考试时间</td><td>花费时间 </td><td>错题率</td>
							</tr>
								";
				for($i=0;$i<count($arr);$i++){
					$html.= "<tr>
								<td><a target='_blank' href='wls.php?controller=quiz_paper&action=viewOne&id=".$arr[$i]['id_quiz_paper']."'>".$this->split_title($arr[$i]['title_quiz_paper'],7)."</a></td>
								<td>".$arr[$i]['cent']."/".$arr[$i]['cent_total']."</td>
								<td>".substr($arr[$i]['date_created'],0,10)."</td>
								<td>".$this->getTimer($arr[$i]['timer'])."</td>
								<td>".$arr[$i]['count_wrong']."/".$arr[$i]['count_total']."</td>
							</tr>";
				}
				$html .="</table>";
				for($i=0;$i<$pagenum;$i++){
					$html .= "<a href=\"wls.php?controller=user&action=viewProfile&page=".($i+1)."\">".($i+1)."</a>";
				}
				$html .="</div>";
				echo $html;
				break;
			case 'htmlflexgrid':
				$this->getListByHtmlflexgrid();
				break;	
			case 'dwz':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$data,
					'sql'=>$sql,
					'total'=>$total,
					'pagenum'=>$pagenum,
				);
				$this->getDWZlist($arr2);
				break;		
			default:
				echo 'returnType is not defined';
				break;
		}
	}
	
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
	
	public function getRecentList($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['page']))$page=$_REQUEST['page'];
		if($rows==null && isset($_REQUEST['rows']))$rows=$_REQUEST['rows'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_encode($_REQUEST['search']);
		if($page==null)$page = 1;
		if($rows==null)$rows = 20;
		if($returnType==null)$returnType = 'dom';
		
		$list = $this->getList('array',$page,$rows);
		$arr = $list['rows'];
		$dom = "<table width='100%' cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td style='font-size:20px;font-color:rgb(161,215,180);height:25px;border:1px solid rgb(0,99,175);background-color:rgb(0,204,255);' colspan='2'>
							<img src='file/images/systm/fbico2.gif'/>&nbsp;最近考试:
						</td>
					</tr>";
		for($i=0;$i<count($arr);$i++){
			$dom.= "<tr >
						<td>
							<a target='_blank' href='wls.php?controller=quiz_paper&action=viewOne&id=".$arr[$i]['id_quiz_paper']."'>".$this->split_title($arr[$i]['title_quiz_paper'],10)."</a>
						</td>
						<td>
							".$arr[$i]['proportion']."
						</td>
					</tr>
			";
		}
		$dom .="</table>";
		return $dom;
	}
	
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
			<b>正确率变化过程线:</b>
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