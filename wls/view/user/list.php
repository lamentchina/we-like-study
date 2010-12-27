<form id="pagerForm" method="post" action="wls.php?controller=user&action=getDWZlist">
	<input type="hidden" name="status" value="${param.status}">
	<input type="hidden" name="keywords" value="${param.keywords}" />
	<input type="hidden" name="pageNum" value="<?php echo $data['page'] ?>" />
	<input type="hidden" name="numPerPage" value="<?php echo $data['pagesize'] ?>" />
	<input type="hidden" name="orderField" value="${param.orderField}" />
</form>
		
<div class="page">
	<div class="pageContent">

		<table class="table" layouth="138">
			<thead>
				<tr>
					<th width="18"></th>
					<th width="40">用户名</th>
					<th width="120">积分</th>
					<th width="100">学习币</th>
					<th width="100">已用学习币</th>
					<th width="80">操作</th>
				</tr>
			</thead>
			<tbody>
		<?php 
		for($i=0;$i<count($data['rows']);$i++){
			echo '
				<tr target="sid_user" rel="'.$data['rows'][$i]['id'].'">
					<td>'.$data['rows'][$i]['id'].'</td>
					<td>'.$data['rows'][$i]['name'].'</td>
					<td>'.$data['rows'][$i]['cents'].'</td>
					<td>'.$data['rows'][$i]['money'].'</td>
					<td>'.$data['rows'][$i]['money_used'].'</td>
					<td><a href="wls.php?controller=user&action=viewAddMoney&id='.$data['rows'][$i]['id'].'" target="dialog" rel="dlg_addmoney'.$data['rows'][$i]['id'].'" mask="true" >添加学习币</a></td>
				</tr>
			';
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