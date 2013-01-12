/**
 * 用户组-权限 关系操作
 * 其中的 金币-积分 策略比较复杂
 * 
 * @version 201210
 * @author wei1224hf@gmail.com
 * */
var basic_group_2_permission = {
		
	/**
	 * 前提条件: 用户登录,用户相关数据被前端获得,il8n初始化,权限初始化
	 * 这是一个树形结构
	 * 右击其中一个节点,可以修改 金币 积分 ,修改完毕后,点击最右边的按钮可以保存到内存
	 * 最后,点击页面右上角的按钮,实施服务端保存
	 * */
	initTree: function(){
		$(document.body).append('<ul id="tree1"></ul>');
		$('#tree1').ligerTree({
			 id: 'basic_group_2_permission__tree'
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
			,nodeWidth : 140
			//鼠标右击操作,编辑 金币 积分 策略
			,onContextmenu: function(a,b,c){
				if( $(".l-checkbox-checked",$(a.target)).length == 0 ) {
					return false;
				}
				$("span:eq(0)",$(a.target)).html(a.data.name_+"&nbsp;<input style='width:15px;' />&nbsp;<input style='width:15px;' />&nbsp;<input onclick='basic_group_2_permission.setCost("+a.data.treedataindex+","+a.data.id+")' type='button' style='width:15px;' />")
				return false;
			}
		    //勾选了一个节点,就初始化 金币 积分 策略,都是0
		    //如果是取消勾选,节点就只显示权限名称
			,onCheck: function (a,b,c) { 
				if(b==true){
					a.data.cost = a.data.credits = 0;
					$("span:eq(0)",$(a.target)).html(a.data.name_+" 0 0");
				}else{
					$("span:eq(0)",$(a.target)).html(a.data.name_);
				}
			}
		});
		
		//清掉树形结构中的节点,然后异步读取服务端数据,再填充树形节点
		$.ligerui.get('basic_group_2_permission__tree').clear();
		$.ligerui.get('basic_group_2_permission__tree').loadData(null
				,myAppServer() + "&class=basic_group_2_permission&function=getTree&id="+getParameter("id", window.location.toString() )
				,{username: top.basic_user.username
				,session: MD5( top.basic_user.session +((new Date()).getHours()))}
		);
		
		$(document.body).append('<input type="button" onclick="basic_group_2_permission.update()" value="'+top.il8n.modify+'" id="button" class="l-button l-button-submit" style="position:absolute;top:5px;left:200px;"  />' );
		//$(document.body).append('<div onclick="$.ligerui.get(\'basic_group_2_permission__tree\').collapseAll()" style="position:absolute;top:25px;left:200px;" >asdf</div>' );
	}

	/**
	 * 如果一个节点是 勾选 状态
	 * 右击一个节点,可以对这个节点执行 金币 积分 策略的修改
	 * 点击最右边的按钮,可以保存到浏览器内存,但是没有保存到服务器
	 * */
	,setCost: function(id,code){
		var tree = $.ligerui.get('basic_group_2_permission__tree');
		var dom = tree.getNodeDom(id) ;
		var cost = $('input:eq(0)',dom).val();
		var credits = $('input:eq(1)',dom).val();
		
		//验证用户输入的,是否是数字
		if(cost=='' || credits=='' || parseInt(cost)=='NaN' || parseInt(credits)=='NaN'){
			
		}else{
			tree.update(dom,{cost:cost,credits:credits});
		}
		var data = tree.getDataByID(code);
		$("span:eq(0)",dom).html(data.name_+" "+data.cost+" "+data.credits);
	}
	
	//执行修改操作的AJAX状态,tree表示还在通信中
	,ajaxState: false
	
	/**
	 * 修改一个用户的用户组
	 * 用户列表必须先打开
	 * */
	,update: function(){
		//如果正在与服务端通信,就不能执行修改
		if(basic_group_2_permission.ajaxState)return;							
		
		var arr = $.ligerui.get('basic_group_2_permission__tree').getChecked();
		var ids = "";
		var costs = "";
		var credits = "";
		if(arr.length != 0){
			for(var i=0;i<arr.length;i++){
				ids += arr[i].data.id+",";
				costs += arr[i].data.cost+",";
				credits += arr[i].data.credit+",";
			}
			ids = ids.substring(0,ids.length-1);
			costs = costs.substring(0,costs.length-1);
			credits = ids.substring(0,credits.length-1);
		
			//修改AJAX的通信状态
			$('#button').attr("value",top.il8n.waitting);
			basic_group_2_permission.ajaxState = true;
		}else{
			alert(top.il8n.selectTreeItemFirst);
			return;
		}
		
		$.ajax({
			url: myAppServer() + "&class=basic_group_2_permission&function=update",
			data: {
				ids: ids, 				
				costs: costs, 
				credits: credits, 
				id: getParameter("id", window.location.toString() ), 
				
				//服务端权限验证所需
				username: top.basic_user.username,
				session: MD5( top.basic_user.session +((new Date()).getHours()))
			},
			type: "POST",
			dataType: 'json',
			success: function(response) {
				//修改成功,就不能再继续执行修改操作了,必须先关闭窗口,再打开
				$('#button').attr("value",top.il8n.modify);		
				basic_group_2_permission.ajaxState = false;
				if(response.state==0){			
					//如果服务端操作失败,弹框显示服务端提示信息
					alert(response.msg);
				}else if(response.state==1){
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
