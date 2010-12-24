<table>
	<tr>
		<td>用户</td>
		<td>
			<select name="user">
			
			<?php 
			for($i=0;$i<count($data['rows']);$i++){
			?>
			<option value="<?php echo $data['rows'][$i]['id'] ?>"
			
			<?php 
				if($data['rows'][$i]['id']==$_REQUEST['id'])echo "selected='selected'";
			?>
			
			><?php echo $data['rows'][$i]['name']?></option>
			<?php 
			}
			?>
			</select>
		</td>		
	</tr>
	<tr>
		<td>学习币</td>
		<td>
			<input name="money" />
		</td>		
	</tr>
	<tr>
		<td colspan="2">
			<button onclick="wls_addMoney();">提交</button>
		</td>
	</tr>
</table>
<script type="text/javascript">
var wls_addMoney = function(){
	$.ajax({
		url: "wls.php?controller=user&action=addMoney",
		data: {
		id:$("select[name=user]").val(),
		money:$("input[name=money]").val()
		},
		type: "POST",
		success: function(msg){			
			var obj = jQuery.parseJSON(msg);	
			navTab.reload(null,null,'userlist');
			$.pdialog.closeCurrent();
		}
	});
}
</script>
