<form id="pagerForm" method="post" action="wls.php?controller=quiz_type&action=getDWZlist">
	<input type="hidden" name="status" value="${param.status}">
	<input type="hidden" name="keywords" value="${param.keywords}" />
	<input type="hidden" name="pageNum" value="<?php echo $data['page'] ?>" />
	<input type="hidden" name="numPerPage" value="<?php echo $data['pagesize'] ?>" />
	<input type="hidden" name="orderField" value="${param.orderField}" />
</form>		
<div class="page">
	<div class="pageContent">
		<div class="panelBar">
			<ul class="toolBar">				
			</ul>
		</div>
		<table class="table" layouth="138">
			<thead>
				<tr>
					<th width="18"></th>
					<th width="120">名称</th>
					<th width="80">试卷总数</th>
					<th width="80">题目总数</th>
					
					<th width="80">参与人数</th>
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
					<td>'.$data['rows'][$i]['count_joined'].'</td>
					';
			
			if($this->check($userinfo['id_user'],$data['rows'][$i]['id'])){
				echo '
				<td>已参加</td>
				<td><a href="wls.php?controller=quiz_type&action=remove&id='.$data['rows'][$i]['id'].'" target="navTab" rel="quiz_type"  >退出</a></td>';
			}else{
				echo '
				<td>未参加</td>
				<td><a href="wls.php?controller=quiz_type&action=add&id='.$data['rows'][$i]['id'].'" target="navTab" rel="quiz_type"  >参加</a></td>';
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