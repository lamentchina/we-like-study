<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>WeLikeStudy 我们喜欢学习! 插件安装</title>
<script src="/plugins/wls/libs/DWZ/javascripts/jquery-1.4.2.js" type="text/javascript"></script>
<script type="text/javascript">
var wls_install_step1 = function(){
	$.ajax({
		url: "wls.php?controller=install_install&action=step1",
		data: {
		cmstype:$("select[name=cmstype]").val(),
		host:$("input[name=host]").val(),
		db:$("input[name=db]").val(),
		user:$("input[name=user]").val(),
		password:$("input[name=password]").val(),
		dbprefix:$("input[name=dbprefix]").val()
		},
		type: "POST",
		success: function(msg){			
			var obj = jQuery.parseJSON(msg);	
			$('#console').empty();
			$('#console').text(obj.message);		
			if(obj.state!=null){
				$('#step1').css('display','none');
				$('#step2').css('display','');
			}
		}
	});
}

var wls_install_step2 = function(){
	$.ajax({
		url: "wls.php?controller=install_install&action=step2",
		success: function(msg){			
			var obj = jQuery.parseJSON(msg);	
			$('#console').empty();
			$('#console').text(obj.message);		
			if(obj.state!=null){
				$('#step2').css('display','none');
				$('#step3').css('display','');
			}
		}
	});
}

var wls_install_step3 = function(){
	$.ajax({
		url: "wls.php?controller=install_install&action=step3",
		success: function(msg){			
			var obj = jQuery.parseJSON(msg);	
			$('#console').empty();
			$('#console').text(obj.message);		
			if(obj.state!=null){
				$('#step2').css('display','none');
				$('#step3').css('display','');
			}
		}
	});
}
</script>
</head>
<body>
<div id='step1'>
	<table>
		<tr>
			<td>目标程序:</td>
			<td>
				<select name="cmstype">
					<option value="null" selected="selected">请选择</option>
					<option value="discuz">Discuz</option>
					<option value="phpwind">phpWind</option>
					<option value="x1">帝国CMS</option>
					<option value="x2">织梦CMS</option>
					<option value="joomla">Joomla</option>
					<option value="drupal">Drupal</option>
					<option value="wordpress">Wordpress</option>
				</select>
			</td>		
		</tr>
		<tr>
			<td>服务器地址</td>
			<td>
				<input name="host" value="localhost" />
			</td>		
		</tr>			
		<tr>
			<td>数据库名称</td>
			<td>
				<input name="db" />
			</td>		
		</tr>	
		<tr>
			<td>数据库用户名</td>
			<td>
				<input name="user" />
			</td>		
		</tr>	
		<tr>
			<td>数据库密码</td>
			<td>
				<input name="password" />
			</td>		
		</tr>	
		<tr>	
			<td>数据库表前缀</td>
			<td>
				<input name="dbprefix" />
			</td>		
		</tr>		
		<tr>	
			<td colspan="2">
				<button onclick="wls_install_step1();">提交</button>
			</td>		
		</tr>								
	</table>
</div>
<div id='step2' style="display: none;">
<button onclick="wls_install_step2();">初始化数据库表</button>
</div>
<div id='step3' style="display: none;">
<button onclick="wls_install_step3();">插入一些测试数据</button>
</div>
<div id='console'>

</div>
</body> 
</html>