<form id="pagerForm" method="post" action="wls.php?controller=quiz_type&action=getDWZlist">
	<input type="hidden" name="status" value="1">
	<input type="hidden" name="keywords" value="1" />
	<input type="hidden" name="pageNum" value="<?php echo $data['page'] ?>" />
	<input type="hidden" name="numPerPage" value="<?php echo $data['pagesize'] ?>" />
	<input type="hidden" name="orderField" value="1" />
</form>		
<div class="page">
	<div class="pageContent">
		<?php 
		$arr = explode(",",$userinfo['id_group']);
		if(in_array($this->cfg->group_admin,$arr)){
		?>
		<div class="panelBar">
			<ul class="toolBar">				
				<li><a class="add" href="wls.php?controller=quiz_type&action=viewEditByDWZ&action2=add" target="dialog" rel="dlg_q_t_a" mask="true"><span>添加</span></a></li>				
				<li><a class="delete" href="wls.php?controller=quiz_type&action=getRemoveByDWZ&id={sid_user}" target="navTabTodo" title="确定要删除吗?"><span>删除</span></a></li>
				<li><a class="icon" href="wls.php?controller=quiz_type&action=viewEditByDWZ&action2=update&id={sid_user}" target="dialog" rel="dlg_upload" mask="true"><span>修改</span></a></li>
				<li class="line">line</li>
			</ul>
		</div>
		<?php 
		}
		?>
		<table class="table" layouth="138">
			<thead>
				<tr>
					<th width="18"></th>
					<th width="120">名称</th>
					<th width="80">试卷总数</th>
					<th width="80">题目总数</th>
					<th width="80">级别</th>
					<th width="80">参与人数</th>
					<th width="80">价格</th>
					<th width="80">状态</th>
					<th width="80">操作</th>
				</tr>
			</thead>
			<tbody>
		<?php 
		for($i=0;$i<count($data['rows']);$i++){
			echo '
				<tr target="sid_user" rel="'.$data['rows'][$i]['id'].'">
					<td>'.$data['rows'][$i]['id'].'</td>
					<td>'.$data['rows'][$i]['title'].'</td>
					<td>'.$data['rows'][$i]['count_paper'].'</td>
					<td>'.$data['rows'][$i]['count_question'].'</td>
					<td>'.(($data['rows'][$i]['level'])?'付费':'公共').'</td>
					';
			if($data['rows'][$i]['level']!=0){
				echo '	<td>'.$data['rows'][$i]['count_joined'].'</td>
						<td>'.$data['rows'][$i]['price_money'].'</td>';
				if($this->isJoined($userinfo['id_user'],$data['rows'][$i]['id'])){
					echo '
					<td>已参加</td>
					<td><a href="wls.php?controller=quiz_type_record&action=remove&id='.$data['rows'][$i]['id'].'" target="navTab" rel="quiz_type"  >退出</a></td>';
				}else{
					echo '
					<td>未参加</td>
					<td><a href="wls.php?controller=quiz_type_record&action=add&id='.$data['rows'][$i]['id'].'" target="navTab" rel="quiz_type"  >参加</a></td>';
				}
			}else{
				echo "<td>-</td><td>-</td><td>-</td><td>-</td>";
			}
			echo '</tr>';
		}
		?>
			</tbody>
		</table>
		<div class="panelBar">
			<div class="pages">
				<span>显示</span>
				<select name="numPerPage" onchange="navTabPageBreak({numPerPage:this.value})">
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="200">200</option>
				</select>
				<span>条，共 <?php echo $data['total'] ?>条</span>
			</div>
			<div class="pagination" targetType="navTab" totalCount="<?php echo$data['total']?>" numPerPage="<?php echo$data['pagesize']?>" pageNumShown="10" currentPage="<?php echo $data['page']?>"></div>
		</div>
	</div>
</div>		