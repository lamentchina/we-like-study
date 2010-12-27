<?php
/**
 * 考试科目类型
 * */
class quiz_type extends wls{

	/**
	 * 初始化数据库表结构
	 * 在开发阶段,数据库表结构可能会经常变动
	 * 考试科目类型
	 * */
	public function initTable(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		$sql = "drop table if exists ".$pfx."wls_quiz_type;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_quiz_type(
				 id int AUTO_INCREMENT primary key 	comment '自动编号'
				,id_parent int default 0			comment '上级编号'
				,haschild int default 0				comment '是否含有下级科目'
				,title varchar(200) 				comment '科目名称'
				,creator varchar(200) not null		comment '创建者'
				,ordering int default 0 			comment '排序'
				,date_created datetime not null 	comment '创建时间'
				,count_paper int default 0			comment '试卷数目'
				,count_question int default 0 		comment '题目数目'
				,count_joined int default 0 		comment '参加的人数'
				,knowledge varchar(200) default '0' comment '知识点'
				,knowledge_parts varchar(200) default '0'	comment '知识点分值组成'
				,description text					comment '描述'
				,price_money int default 0			comment '价格'
			) DEFAULT CHARSET=utf8 					comment='考试科目';	
			";
		mysql_query($sql,$conn);
	}

	/**
	 * 获得具有层级关系的考试科目组
	 * */
	public function getMyList(){
		include_once 'controller/user.php';
		$obj = new user();
		$userinfo = $obj->getUserInfo('mine');
		$arr = explode(",",$userinfo['id_group']);
		$search = null;
		//如果是管理员们
		if(in_array($this->cfg->group_admin,$arr)){
				
		}else{
			$search = array('id_user'=>$userinfo['id_user']);
		}

		$data = $this->getList('array',1,1000,$search);
		$data = $data['rows'];
		$arr = array();
		for($i=0;$i<count($data);$i++){
			if($data[$i]['id_parent']==0){
				$arr[$data[$i]['id']] = $data[$i];
			}else{
				$arr[$data[$i]['id_parent']]['child'][] = $data[$i];
			}
			if($data[$i]['haschild']==1){
				$arr[$data[$i]['id']]['child'] = array();
			}
		}
		return $arr;
	}

	/**
	 * 获得列表
	 *
	 * @param $returnType 可以是 array json xml 等格
	 * @param $page 分页序号
	 * @param $rows 每页大小
	 * @param $search 查询条件 
	 * */
	public function getList($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['page']))$page=$_REQUEST['page'];
		if($rows==null && isset($_REQUEST['rows']))$rows=$_REQUEST['rows'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search']))$search =json_decode($_REQUEST['search']);
		if($page==null)$page = 1;
		if($rows==null)$rows = 20;
		if($returnType==null)$returnType = 'json';

		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";
		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id_user'){
					$sql_ = "select id_quiz_type from ".$pfx."wls_quiz_type_record where id_user = ".$search[$keys[$i]];
					$res = mysql_query($sql_,$conn);
					$ids = '';
					while($temp = mysql_fetch_assoc($res)){
						$ids .= $temp['id_quiz_type'].",";
					}
					if($ids!=''){
						$ids = substr($ids,0,strlen($ids)-1);
						$where .= " and id in (".$ids.") ";
					}else{
						$where .= " and 1>2 ";
					}
				}
				if($keys[$i]=='id_quiz_type'){
					$where .= " and id_quiz_type in (".$search[$keys[$i]].") ";
				}
			}
		}

		$sql = "select * from ".$pfx."wls_quiz_type  ".$where;

		$sql .= " limit ".($rows*($page-1)).",".$rows." ";
		$res = mysql_query($sql,$conn);
		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql = "select count(*) as total from ".$pfx."wls_quiz_type ".$where;
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		header("Content-type: text/html; charset=utf-8");
		switch($returnType) {
			case 'json':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$arr,
					'sql'=>$sql,
					'total'=>$total,
					'pagesize'=>$rows,
				);
				unset($arr);
				echo json_encode($arr2);
				break;
			case 'xml':
				//TODO
			case 'array':
				$arr2 = array(
					'page'=>$page,
					'rows'=>$arr,
					'sql'=>$sql,
					'total'=>$total,
					'pagesize'=>$rows,
				);
				return $arr2;
				break;
			default:
				echo 'returnType is not defined';
				break;
		}
	}

	public function add($data=null){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
			
		if($data==null && isset($_POST))$data = $_POST;

		$keys = array_keys($data);
		$clumns = '';
		$values = '';
		for($i=0;$i<count($keys);$i++){
			$clumns .= $keys[$i].",";
			$values .= "'".$data[$keys[$i]]."',";
		}
		$clumns = substr($clumns,0,strlen($clumns)-1);
		$values = substr($values,0,strlen($values)-1);

		$sql = "insert into ".$pfx."wls_quiz_type ( ".$clumns." ) values (".$values.");";
		mysql_query($sql,$conn);
		$id = mysql_insert_id($conn);
		return $id;
	}

	public function remove($id=null){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		if($id==null && isset($_REQUEST['id']))$id = $_REQUEST['id'];
		$sql = "delete from ".$pfx."wls_quiz_type where id = ".$id;
		mysql_query($sql,$conn);
	}

	public function upate(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $_POST['id'];
		unset($_POST['id']);
		$keys = array_keys($_POST);

		$sql = " update ".$pfx."wls_quiz_type set ";
		for($i=0;$i<count($keys);$i++){
			$sql .= $keys[$i]." = '".$_POST[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;

		mysql_query($sql,$conn);
	}


	public function viewUpdateByDWZ(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "select id,title,price_money from ".$pfx."wls_quiz_type ";
		$res = mysql_query($sql,$conn);
		$data = array();
		$mydata = array();
		while($temp = mysql_fetch_assoc($res)){
			if($temp['id']==$_REQUEST['id']){
				$mydata = $temp;
			}else{
				$data[] = $temp;
			}
		}

		echo '
		<div class="pageFormContent" layoutH="56">
			<p>
				<label>标题：</label>
				<input name="id" type="hidden" value="'.$_REQUEST['id'].'" />
				<input name="title" type="text" size="30" value="'.$mydata['title'].'" />				
			</p>
			<p>
				<label>价格：</label>
				<input name="price_money" type="text" size="30" value="'.$mydata['price_money'].'" />				
			</p>
			<p>
				<label>上级科目：</label>
				<select style="width:190px;" name="id_parent">
					<option value="0">无</option>
				';
		for($i=0;$i<count($data);$i++){
			echo '	<option value="'.$data[$i]['id'].'">'.$data[$i]['title'].'</option>';
		}
			
		echo '
				</select>
			</p>
			<p>
				<button onclick="wls_q_t_u_save();">提交</button>
			</p>
		</div>
		<script type="text/javascript">
		var wls_q_t_u_save = function(){
			$.ajax({
				url: "wls.php?controller=quiz_type&action=upate",
				data: {
					id:$("input[name=id]").val(),
					title:$("input[name=title]").val(),
					price_money:$("input[name=price_money]").val(),
					id_parent:$("select[name=id_parent] option:selected").val()
				},
				type: "POST",
				success: function(msg){			
					//var obj = jQuery.parseJSON(msg);	
					$.pdialog.closeCurrent();
					navTab.reload(null,null,"quiz_type");
				}
			});
		}
		</script>
		';
	}

	public function viewAddByDWZ(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "select id,title from ".$pfx."wls_quiz_type ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}

		echo '
		<div class="pageFormContent" layoutH="56">
			<p>
				<label>标题：</label>
				<input name="title" type="text" size="30" />				
			</p>
			<p>
				<label>上级科目：</label>
				<select style="width:190px;" name="id_parent">
					<option value="0">无</option>
				';
		for($i=0;$i<count($data);$i++){
			echo '	<option value="'.$data[$i]['id'].'">'.$data[$i]['title'].'</option>';
		}
			
		echo '
				</select>
			</p>
			<p>
				<button onclick="wls_q_t_a_save();">提交</button>
			</p>
		</div>
		<script type="text/javascript">
		var wls_q_t_a_save = function(){
			$.ajax({
				url: "wls.php?controller=quiz_type&action=add",
				data: {
					title:$("input[name=title]").val(),
					id_parent:$("select[name=id_parent] option:selected").val()
				},
				type: "POST",
				success: function(msg){			
					//var obj = jQuery.parseJSON(msg);	
					$.pdialog.closeCurrent();
					navTab.reload(null,null,"quiz_type");
				}
			});
		}
		</script>
		';
	}

	public function getRemoveByDWZ(){
		$this->remove($_REQUEST['id']);
		echo '
		{
		"statusCode":"200",
		"message":"删除了编号为'.$_REQUEST['id'].'的试卷",
		"navTabId":"",
		"callbackType":"reload",
		"forwardUrl":""
		}		
		';
	}

	public function getDWZlist($returnType = null,$page=null,$rows=null,$search=null){
		if($page==null && isset($_REQUEST['pageNum']))$page=$_REQUEST['pageNum'];
		if($rows==null && isset($_REQUEST['numPerPage']))$rows=$_REQUEST['numPerPage'];
		if($returnType==null && isset($_REQUEST['returnType']))$returnType =$_REQUEST['returnType'];
		if($search==null && isset($_REQUEST['search'])){
			$_REQUEST['search'] = str_replace("'","\"",$_REQUEST['search']);
			$search =json_decode($_REQUEST['search'],true);
		}
		if($page==null)$page = 1;
		if($rows==null)$rows = 10;
		if($returnType==null)$returnType = 'html';

		$data = $this->getList('array',$page,$rows,$search);

		include_once 'controller/user.php';
		$user = new user();
		$userinfo = $user->getUserInfo();

		include_once 'view/quiz/type/list.php';
	}

	/**
	 * 判断某用户是否参与了这个科目
	 * */
	public function isJoined($id_user,$id_quiz_paper){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "select count(*) as count_ from ".$pfx."wls_quiz_type_record where id_user = ".$id_user." and id_quiz_type = ".$id_quiz_paper;
		$res = mysql_query($sql,$conn);
		if($temp = mysql_fetch_assoc($res)){
			if( $temp['count_']>0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
}
?>