<form id="pagerForm" method="post" action="wls.php?controller=quiz_wrongs&action=getDWZlist">
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
					<th width="40">类型</th>
					<th width="40">考试科目</th>
					<th width="40">试卷</th>
					<th width="40">记录时间</th>
				</tr>
			</thead>
			<tbody>
		<?php 
		for($i=0;$i<count($data['rows']);$i++){
			echo '
				<tr target="sid_user" rel="'.$data['rows'][$i]['id'].'">
					<td>'.$data['rows'][$i]['id'].'</td>
					<td>'.$this->formatQuesType($data['rows'][$i]['questype']).'</td>
					<td>'.$data['rows'][$i]['title_quiz_type'].'</td>
					<td>'.$data['rows'][$i]['title_quiz_paper'].'</td>
					<td>'.$data['rows'][$i]['date_created'].'</td>
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