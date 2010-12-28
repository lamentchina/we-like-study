<form id="pagerForm" method="post" action="wls.php?controller=quiz_paper&action=getDWZlist">
	<input type="hidden" name="status" value="${param.status}">
	<input type="hidden" name="search" value="<?php $search_ = str_replace("\\",'',$search_); echo str_replace("\"","'",$search_); ?>" />
	<input type="hidden" name="pageNum" value="<?php echo $data['page'] ?>" />
	<input type="hidden" name="numPerPage" value="<?php echo $data['pagesize'] ?>" />
	<input type="hidden" name="orderField" value="${param.orderField}" />
</form>
		
<div class="page">
	<div class="pageContent">
		<?php 
		$arr = explode(",",$userinfo['id_group']);
		if(in_array($this->cfg->group_admin,$arr)){
		?>
		<div class="panelBar">
			<ul class="toolBar">				
				<li><a class="add" href="wls.php?controller=quiz_paper_normal&action=viewUploadExcel" target="dialog" rel="dlg_upload" mask="true"><span>添加</span></a></li>				
				<li><a class="delete" href="wls.php?controller=quiz_paper&action=del&id={sid_user}" target="navTabTodo" title="确定要删除吗?"><span>删除</span></a></li>
				<li><a class="icon" href="wls.php?controller=quiz_paper_normal&action=exportExcel&id={sid_user}" target="dialog" rel="dlg_export" mask="true"><span>导出</span></a></li>
				<li><a class="icon" href="wls.php?controller=quiz_paper_normal&action=viewEditByDWZ&id={sid_user}" target="dialog" rel="dlg_modify" mask="true"><span>修改</span></a></li>
				<li class="line">line</li>
			</ul>
		</div>
		<?php 
		}
		?>
		<table class="table" layouth="138">
			<thead>
				<tr>
					<th width="20"></th>
					<th width="70">类型</th>
					<th width="120">试卷名称</th>
					<th width="50">最高分/平均分</th>
					<th width="60">题总数/子题数</th>
					<th width="45" align="center">使用次数</th>

					<th width="45">加入日期</th>
					<th width="30">价格</th>
					<th width="80">操作</th>
				</tr>
			</thead>
			<tbody>
		<?php 
		for($i=0;$i<count($data['rows']);$i++){
			echo '
				<tr target="sid_user" rel="'.$data['rows'][$i]['id'].'">
					<td>'.$data['rows'][$i]['id'].'</td>
					<td>'.$data['rows'][$i]['title_quiz_type'].'</td>
					<td>'.$data['rows'][$i]['title'].'</td>
					<td>'.$data['rows'][$i]['score_top'].'/'.$data['rows'][$i]['score_avg'].'</td>
					<td>'.$data['rows'][$i]['count_quetions'].'/'.$data['rows'][$i]['count_subquestions'].'</td>
					<td>'.$data['rows'][$i]['count_used'].'</td>

					<td>'.substr($data['rows'][$i]['date_created'],0,10).'</td>
					<td>'.substr($data['rows'][$i]['price_money'],0,10).'</td>
					<td><a href="wls.php?controller=quiz_paper&action=viewOneInDWZ&id='.$data['rows'][$i]['id'].'" target="_blank" >做题</a></td>
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