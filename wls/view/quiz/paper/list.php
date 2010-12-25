<form id="pagerForm" method="post" action="wls.php?controller=quiz_paper_paper&action=getDWZlist">
	<input type="hidden" name="status" value="${param.status}">
	<input type="hidden" name="search" value="<?php echo str_replace("\"","'",$search_) ?>" />
	<input type="hidden" name="pageNum" value="<?php echo $data['page'] ?>" />
	<input type="hidden" name="numPerPage" value="<?php echo $data['pagesize'] ?>" />
	<input type="hidden" name="orderField" value="${param.orderField}" />
</form>
		
<div class="page">
<!--  
	<div class="pageHeader">
		<form onsubmit="return navTabSearch(this);" action="wls.php?controller=quiz_paper_paper&action=getDWZlist" method="post">
		<div class="searchBar">
			<ul class="searchContent">
				<li>
					<label>试卷名称：</label>
					<input name='keywords' type="text" />
				</li>
			</ul>
			<div class="subBar">
				<ul>
					<li><div class="buttonActive"><div class="buttonContent"><button type="submit">检索</button></div></div></li>
					<li><a class="button" href="demo_page6.html" target="dialog" rel="dlg_page1" title="查询框"><span>高级检索</span></a></li>
				</ul>
			</div>
		</div>
		</form>
	</div>
	-->
	<div class="pageContent">
		<?php 
		$arr = explode(",",$userinfo['id_group']);
		if(in_array($this->cfg->group_admin,$arr)){
		?>
		<div class="panelBar">
			<ul class="toolBar">				
				<li><a class="add" href="wls.php?controller=quiz_paper_normal&action=viewUploadExcel" target="dialog" rel="dlg_upload" mask="true"><span>添加</span></a></li>				
				<li><a class="delete" href="wls.php?controller=quiz_paper_paper&action=del&id={sid_user}" target="navTabTodo" title="确定要删除吗?"><span>删除</span></a></li>
				<li><a class="icon" href="wls.php?controller=quiz_paper_normal&action=exportExcel&id={sid_user}" target="dialog" rel="dlg_upload" mask="true"><span>导出</span></a></li>
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
					<th width="40">类型</th>
					<th width="120">试卷名称</th>
					<th width="50" title="最高分/平均分 ">分数</th>
					<th width="80" title="题总数/子题总数">题目</th>
					<th width="80" align="center">使用次数</th>
					<th width="80" title="访问级别/难度级别">级别</th>
					<th width="80">考试日期</th>
					<th width="40">价格</th>
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
					<td>'.$data['rows'][$i]['rank'].'/'.$data['rows'][$i]['difficulty'].'</td>
					<td>'.substr($data['rows'][$i]['date_created'],0,10).'</td>
					<td>'.substr($data['rows'][$i]['price_money'],0,10).'</td>
					<td><a href="wls.php?controller=quiz_paper_paper&action=viewOneInDWZ&id='.$data['rows'][$i]['id'].'" target="_blank" >做题</a></td>
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