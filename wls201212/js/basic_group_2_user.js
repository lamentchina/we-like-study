var basic_group_2_user = {
		
	initTree: function(){
		$(document.body).append('<ul id="tree1"></ul>');
		$('#tree1').ligerTree({
			 id: 'basic_group_2_user__tree'
			,autoCheckboxEven : false
			//先随便填充一个tree结构,tree的内容需要等tree初始化后异步填充
			,data: [
	                { name: top.il8n.waitting , code: '1' },
	                { name: top.il8n.waitting , code: '2' },
	                { name: top.il8n.waitting , code: '3' },
	                { name: top.il8n.waitting , code: '4' }
	            ]
			,textFieldName : 'name'
			,slide : true
			,idFieldName : 'code'
			,nodeWidth : 140
		});
		
		//清掉树形结构中的节点,然后异步读取服务端数据,再填充树形节点
		$.ligerui.get('basic_group_2_user__tree').clear();
		$.ligerui.get('basic_group_2_user__tree').loadData(null
				,myAppServer() + "&class=basic_group_2_user&function=getTree&code="+getParameter("usercode", window.location.toString() )
				,{username: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))}
		);
		
		$(document.body).append('<input type="button" onclick="basic_group_2_user.update()" value="'+top.il8n.modify+'" id="button" class="l-button l-button-submit" style="position:absolute;top:5px;left:200px;"  />' );
	}
	
	//执行修改操作的AJAX状态,tree表示还在通信中
	,ajaxState: false
	
	/**
	 * 修改一个用户的用户组
	 * 用户列表必须先打开
	 * */
	,update: function(){

		//如果正在与服务端的通信
		if(basic_group_2_user.ajaxState)return;							
		
		var arr = $.ligerui.get('basic_group_2_user__tree').getChecked();
		var ids = "";
		if(arr.length != 0){
			for(var i=0;i<arr.length;i++){
				ids += arr[i].data.code+",";
			}
			ids = ids.substring(0,ids.length-1);
		
			//修改AJAX的通信状态
			$('#button').attr("value",top.il8n.waitting);
			basic_group_2_user.ajaxState = true;
		}else{
			alert(top.il8n.selectTreeItemFirst);
			return;
		}
		
		$.ajax({
			url: myAppServer() + "&class=basic_group_2_user&function=update",
			data: {
				codes: ids, 
				code: getParameter("usercode", window.location.toString() ), 
				
				//服务端权限验证所需
				username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
			},
			type: "POST",
			dataType: 'json',
			success: function(response) {
				//修改成功,就不能再继续执行修改操作了,必须先关闭窗口,再打开
				$('#button').attr("value",top.il8n.modify);		
				basic_group_2_user.ajaxState = false;
				if(response.state!=1){			
					//如果服务端操作失败,弹框显示服务端提示信息
					alert(response.msg);
				}else{
					//服务端操作成功
					alert(top.il8n.done);
				}
			},
			error : function(){
				//网络通信失败,按钮将不可按,只能关闭窗口
				alert(top.il8n.disConnect);
			}
		});
	}
};