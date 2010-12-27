<div class="page">
	<div class="pageContent">
		<table class="table" layouth="138">
			<thead>
				<tr>
					<th width="30"></th>
					<th width="60">得分</th>
					<th width="60">总分</th>
					<th width="100">试卷名称</th>
					<th width="100">科目种类</th>
					<th >花费时间 </th>
					<th width="150">题总数/做错/放弃</th>
					<th width="80" align="center">正确率</th>
					<th width="80">记录日期</th>
				</tr>
			</thead>
			<tbody>
		<?php 
		for($i=0;$i<count($data['rows']);$i++){
			echo '
				<tr target="sid_user" rel="'.$data['rows'][$i]['id'].'">
					<td>'.$data['rows'][$i]['id'].'</td>
					<td>'.$data['rows'][$i]['mycent'].'</td>
					<td>'.$data['rows'][$i]['cent'].'</td>
					<td>'.$data['rows'][$i]['title_quiz_paper'].'</td>
					<td>'.$data['rows'][$i]['title_quiz_type'].'</td>
					<td>'.$this->getTimer($data['rows'][$i]['timer']).'</td>
					<td>'.$data['rows'][$i]['count_total'].'/'.$data['rows'][$i]['count_wrong'].'/'.$data['rows'][$i]['count_giveup'].'</td>
					<td>'.(int)($data['rows'][$i]['proportion']*100).'%'.'</td>
					<td>'.substr($data['rows'][$i]['date_created'],0,10).'</td>
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