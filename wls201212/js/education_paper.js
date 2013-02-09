/**
 * 试卷模块的前端
 * 包括: 列表,查看功能,批量导入导出,删除
 * 
 * 试卷的类型有: 
 *   公开试卷,可以被 guest 使用的
 *   普通试卷,一般的普通学生根据学科可以使用的
 *   来自考试模块的试卷,只有管理员跟教师可以看到,学生都看不到
 * 
 * 试卷的状态有:
 *   对学生:
 *      已经做过了
 *      还没有做过
 *   对管理员:
 *      封闭(试卷无法被使用)
 *      正常
 *   对教师:
 *      编辑
 *      发布
 *      封闭
 *      
 * 
 * @version 201209
 * @author wei1224hf@gmail.com
 * */
var education_paper = {
        
    ajaxState: false        

    /**
     * 配置文件中,包含 下拉列表内容, 用户角色(教师,还是学生)
     * 下拉列表内容(类别型数据),包含: 试卷类型,试卷状态(对学生),试卷状态(对教师)
     * */    
    ,config: null
    ,loadConfig: function(afterAjax){
        $.ajax({
            url: myAppServer()+ "&class=education_paper&function=loadConfig"
            ,dataType: 'json'
            ,type: "POST"
            ,data: {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code
            }        
            ,success : function(response) {
                education_paper.config = response;
                if ( typeof(afterAjax) == "string" ){
                    eval(afterAjax);
                }else if( typeof(afterAjax) == "function"){
                    afterAjax();
                }
            }
            ,error : function(){                
                alert(top.il8n.disConnect);
            }
        });    
    }        
    
    /**
     * 不同的用户组看到的试卷的列是不同的
     * 管理员:
     *     标题,科目,分值,金币,作者,类型,状态,题目总数,创建时间,最后更改时间,更改次数
     * 教师:
     *     标题,科目,分值,创建时间,题目总数,类型,状态,被使用的次数,作者,金币
     *     教师只能看到自己上传提交的试卷,或者在自己管辖内的所有试卷
     * 学生:
     *     标题,科目,金币,是否做过,最后一次做卷子的时间,最后一次做时候的分值,状态,试卷发布时间
     *     
     * 不同的用户组可以执行的操作是不同的:
     * 
     * 管理员:
     *    导入一张试卷,导出一张试卷,批量删除试卷,批量封闭试卷,新建一张试卷,修改一张试卷
     *    
     * 教师:
     *    导入一张试卷,导出一张试卷,新建一张试卷,修改一张试卷,只能看到自己负责的卷子
     *    
     *   教师组长:
     *      导入一张试卷,导出一张试卷,新建一张试卷,修改管理权限内的试卷,删除管理权限内的试卷
     *    
     * 学生:
     *    做试卷
     * 
     * */
    ,grid : function(){
    	var gridColmuns = [
    	   [//管理员列
  	         { display: top.il8n.title, name: 'title', align: 'left', width: 140, minWidth: 60 }
  	        ,{ display: top.il8n.education_paper.subject, name: 'subject_name',isSort : false }
	        ,{ display: top.il8n.education_paper.subject, name: 'subject_code',isSort : false, hide: true }
	        ,{ display: top.il8n.education_paper.cent, name: 'cent' ,width: 50 ,isSort : false}
	        ,{ display: top.il8n.education_paper.cost, name: 'cost' ,width: 50}
 		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_name' }
 		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_code',isSort : false, hide: true }
 		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_id',isSort : false, hide: true }
	        ,{ display: top.il8n.education_paper.count_questions, name: 'count_questions',width: 55,isSort : false }
	        ,{ display: top.il8n.education_paper.count_used, name: 'count_questions',width: 55,isSort : false }
	        ,{ display: top.il8n.time_created, name: 'time_created', width: 80 }
	        ,{ display: top.il8n.type, name: 'type_' }
    	   ]
           ,[//学生列
             { display: top.il8n.id, name: 'id', minWidth: 60, hide:true }
	        ,{ display: top.il8n.title, name: 'title', align: 'left', width: 140, minWidth: 60 }
	        ,{ display: top.il8n.education_paper.subject, name: 'subject_name',isSort : false }
   	        ,{ display: top.il8n.education_paper.subject, name: 'subject_code',isSort : false, hide: true }
			,{ display: top.il8n.education_paper.count_questions, name: 'count_questions',isSort : false, width: 80 }
			,{ display: top.il8n.education_paper.cent, name: 'cent',isSort : false }
		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_name',isSort : false, hide: true }
		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_code',isSort : false, hide: true }
		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_id',isSort : false, hide: true }
		   	,{ display: top.il8n.time_created, name: 'time_created',isSort : false, hide: true }
		   	,{ display: top.il8n.education_paper.count_used, name: 'count_used',isSort : false, hide: true, hide: 80 }
   	        ,{ display: top.il8n.education_paper.cost, name: 'cost' ,width: 30}
   	        ,{ display: top.il8n.education_paper.mycent, name: 'mycent' ,width: 70, render: function(a,b){
				if(a.mycent==null)return '没做过';
				return a.mycent;
			 }}
   	        ,{ display: top.il8n.type, name: 'type' }
           ]
    	   ,[//教师列
    	     { display: top.il8n.id, name: 'id', minWidth: 60, hide:true }
   	        ,{ display: top.il8n.title, name: 'title', align: 'left', width: 140, minWidth: 60 }
 	        ,{ display: top.il8n.education_paper.subject, name: 'subject_name',isSort : false }
   	        ,{ display: top.il8n.education_paper.subject, name: 'subject_code',isSort : false, hide: true }
   	        ,{ display: top.il8n.education_paper.cent, name: 'cent' ,width: 50 ,isSort : false}
   	        ,{ display: top.il8n.education_paper.cost, name: 'cost' ,width: 50}
		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_name',isSort : false, hide: true }
		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_code',isSort : false, hide: true }
		   	,{ display: top.il8n.education_paper.teacher_name, name: 'teacher_id',isSort : false, hide: true }
   	        ,{ display: top.il8n.education_paper.count_questions, name: 'count_questions',width: 55,isSort : false }
   	        ,{ display: top.il8n.education_paper.count_used, name: 'count_questions',width: 55,isSort : false }
   	        ,{ display: top.il8n.time_created, name: 'time_created', width: 80 }
   	        ,{ display: top.il8n.type, name: 'type' }
           ]
    	   ,[//访客列
  	         { display: top.il8n.title, name: 'title', align: 'left', width: 140, minWidth: 60 }
   	        ,{ display: top.il8n.education_paper.subject, name: 'subject_name',isSort : false }
    	     ]
        ];
        
        var config = {
            height:'100%'
            ,columns: []
        	,pageSize: 15 
        	,rownumbers:true
            ,url : myAppServer()+ "&class=education_paper&function=grid"
            ,method  : "POST"
            ,id : "education_paper_grid"
            ,parms : {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code              
            }
            ,toolbar: { items: [] }
            ,frozen: false
        };
        
        config.columns = gridColmuns[(top.basic_user.loginData.type)*1-1]; 
        var permission = [];
        for(var i=0;i<top.basic_user.permission.length;i++){
            if(top.basic_user.permission[i].code=='15'){
                permission = top.basic_user.permission[i].children;
            }
        }
        
        for(var i=0;i<permission.length;i++){      
        	config.toolbar.items.push({line: true });
            if(permission[i].code=='1501'){
            	//查询功能,基本上每个用户组都有
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){
                        education_paper.search();
                    },disable:true
                });
            }else if(permission[i].code=='1502'){
            	//查看功能,查看一张试卷
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                    	var selected = null;
                    	if($.ligerui.get('education_paper_grid').options.checkbox){
                    		//启用了多行勾选
							selected = $.ligerui.get('education_paper_grid').getSelecteds();
							if(selected.length!=1){
								alert(top.il8n.selectOne);return;
							}
							selected = selected[0];
                    	}else{
                    		selected = $.ligerui.get('education_paper_grid').getSelected();
							if(selected==null){
								alert(top.il8n.noSelect);return;
							}
                    	}
                    	
                    	var id = selected.id;
                        if(top.$.ligerui.get("win_paper_view_"+id)){
                            top.$.ligerui.get("win_paper_view_"+id).show();
                            return;
                        }
                        top.$.ligerDialog.open({
                            isHidden:false,
                            id : "win_paper_view_"+id , height:  550, width: 600,
                            url: "education_paper__view.html?id="+id,  
                            showMax: true, showToggle: true, showMin: true, isResize: true,
                            modal: false, title: $.ligerui.get('education_paper_grid').getSelected().title
                            , slide: false    
                        });
                        
                        top.$.ligerui.get("win_paper_view_"+id).close = function(){
                            var g = this, p = this.options;
                            top.$.ligerui.win.removeTask(this);
                            g.unmask();
                            g._removeDialog();
                            top.$.ligerui.remove(top.$.ligerui.get("win_paper_view_"+id));
                            top.$('body').unbind('keydown.dialog');
                        }
                    }
                });
            }else if(permission[i].code=='1511'){
            	//上传EXCEL文件,批量导入数据
                config.toolbar.items.push({
                    text: permission[i].name 
                    ,img:permission[i].icon , click : function(){
                    	education_paper.import_();
                    }
                });
            }else if(permission[i].code=='1512'){
            	//勾选一张试卷,导出数据,EXCEL下载
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                    	var selected = null;
                    	if($.ligerui.get('education_paper_grid').options.checkbox){
                    		//启用了多行勾选
							selected = $.ligerui.get('education_paper_grid').getSelecteds();
							if(selected.length!=1){
								alert(top.il8n.selectOne);return;
							}
							selected = selected[0];
                    	}else{
                    		selected = $.ligerui.get('education_paper_grid').getSelected();
							if(selected==null){
								alert(top.il8n.noSelect);return;
							}
                    	}
                    	
                    	var id = selected.id;
                    	var title = selected.title;
                    	education_paper.export_(id,title);
                    }
                });
            }else if(permission[i].code=='1521'){
                //添加功能
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        top.$.ligerDialog.open({
                            isHidden:false,
                            id : "win_paper_insert", height:  550, width: 600,
                            url: "education_paper__insert.html",  
                            showMax: true, showToggle: true, showMin: true, isResize: true,
                            modal: false, title: "s"
                            , slide: false    
                        }).max();
                        
                        top.$.ligerui.get("win_paper_insert"+id).close = function(){
                            var g = this, p = this.options;
                            top.$.ligerui.win.removeTask(this);
                            g.unmask();
                            g._removeDialog();
                            top.$.ligerui.remove(top.$.ligerui.get("win_paper_insert"+id));
                            top.$('body').unbind('keydown.dialog');
                        }                    	
                        
                    }
                });            
            }else if(permission[i].code=='1522'){
            	//批量删除,将启用 checkBox 功能
                config.checkbox = true;
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        education_paper.delete_();
                    }
                });            
            }else if(permission[i].code=='1523'){
            	//修改功能,一次只能修改一张试卷,不能批量修改
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        
                    }
                });
            }else if(permission[i].code=='1590'){
            	//做试卷,只有 学生 有这个权限
                config.toolbar.items.push({
                    text: permission[i].name, img:permission[i].icon , click : function(){
                        var id = $.ligerui.get('education_paper_grid').getSelected().id;
                        if(top.$.ligerui.get("win_paper_"+id)){
                            top.$.ligerui.get("win_paper_"+id).show();
                            return;
                        }
                        top.$.ligerDialog.open({
                            isHidden:false,
                            id : "win_paper_"+id , height:  550, width: 600,
                            url: "education_paper__do.html?id="+id,  
                            showMax: true, showToggle: true, showMin: true, isResize: true,
                            modal: false, title: $.ligerui.get('education_paper_grid').getSelected().title
                            , slide: false    
                        }).max();
                        
                        top.$.ligerui.get("win_paper_"+id).close = function(){
                            var g = this, p = this.options;
                            top.$.ligerui.win.removeTask(this);
                            g.unmask();
                            g._removeDialog();
                            top.$.ligerui.remove(top.$.ligerui.get("win_paper_"+id));
                            top.$('body').unbind('keydown.dialog');
                        }
                    }
                });
            }
        }
        $(document.body).ligerGrid(config);
    }
    
    ,searchOptions : {}    
    ,search : function(){
        var formD;
        if($.ligerui.get("formD")){
            formD = $.ligerui.get("formD");
            formD.show();
        }else{
            var form = $("<form id='form'></form>");
            $(form).ligerForm({
                inputWidth: 170, labelWidth: 90, space: 40,
                fields: [
                    { display: top.il8n.title, name: "title", newline: false, type: "text" },
                    { display: top.il8n.education_subject.subject, name: "subject", newline: true, type: "select", comboboxName: "combo_select"
                    	, options: { data: education_paper.config.subject, valueField : "code" , textField : "value",slide:false } },
                    { display: top.il8n.money, name: "money", newline: true, type: "text"  }
                ]
            }); 
            $.ligerDialog.open({
                id : "formD",
                width : 350,
                height : 150,
                content : form,
                title : top.il8n.search,
                buttons : [
                    //清空查询条件
                    {text:top.il8n.clear,onclick:function(){
                        $.ligerui.get("education_paper_grid").options.parms.search =  $.ligerui.toJSON({foo:1});
                        $.ligerui.get("education_paper_grid").loadData();
                        
                        $.ligerui.get("title").setValue('');
                        $.ligerui.get("money").setValue('');
                        $.ligerui.get("combo_select").setValue('');
                    }},
                    //提交查询条件
                    {text:top.il8n.search,onclick:function(){
                    $.ligerui.get("education_paper_grid").options.parms.search =  $.ligerui.toJSON({
                    		title : $.ligerui.get("title").getValue(),
                            subject : $.ligerui.get("combo_select").getValue(),
                            money : $.ligerui.get("money").getValue(),
                            foo : 'bar'
                        });
                    $.ligerui.get("education_paper_grid").loadData();
                }}]
            });
        }
    }
    
    /**
     * 批量导入文件
     * */
    ,import_: function(){
        var dialog;
        if($.ligerui.get("education_paper__grid_import_d")){
            dialog = $.ligerui.get("education_paper__grid_import_d");
            dialog.show();
        }else{

            $(document.body).append( $("<div id='education_paper__grid_file'></div>"));
            var importer = new qq.FileUploader({
                element: document.getElementById('education_paper__grid_file')
                ,action: '../php/myApp.php?class=education_paper&function=import'
                ,allowedExtensions: ["xls"]
                ,params: {
                     username: top.basic_user.username
                    ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                    ,search: $.ligerui.toJSON( basic_user.searchOptions )
                    ,user_id: top.basic_user.loginData.id
                    ,user_type: top.basic_user.loginData.type    
                    ,group_id: top.basic_user.loginData.group_id
                    ,group_code: top.basic_user.loginData.group_code  
                }
                ,downloadExampleFile : "../file/download/education_paper.xls"
                ,debug: true
                ,onComplete: function(id, fileName, responseJSON){
                    
                }
            });  
            
			$.ligerDialog.open({
				title: top.il8n.importFile,
				id : "education_paper__grid_import_d",
				width : 350,
				height : 200,
				target : $("#education_paper__grid_file"),
				modal : true
			});
        }
    }  
    
    /**
	 * 实现EXCEL导出
     * 先从业务表到 basic_excel 
     * 再从 basic_excel 到 .xls
     * 然后前端再下载
     * */
    ,export_: function(id,title){
    	var download_dom;
        if($.ligerui.get("download_dom")){
        	download_dom = $.ligerui.get("download_dom");        	
        	download_dom.show();
        }else{                    
            $.ligerDialog.open({
                id : "download_dom",
                width : 350,
                height : 150,
                content : "<div id='download_dom_'></div>",
                title : top.il8n.download
            });
        }    	
    	$.ajax({
            url: myAppServer() + "&class=education_paper&function=export",
            data: {
                id:id 
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code  
            },
            type: "POST",
            dataType: 'json',
            success: function(response) {
            	$('#download_dom_').append("<a href='"+response.file+"' target='_blank' >&nbsp;"+top.il8n.download+"&nbsp;"+title+"<br/></a>");
            },
            error : function(){               
                alert(top.il8n.disConnect);
            }
        });     
    }
    
    /**
     * 批量删除行数据,列表必须启用了 checkBox 模式
     * */
    ,delete_: function(){
    	selected = $.ligerui.get('education_paper_grid').getSelecteds();
		if(selected.length==0){
			alert(top.il8n.noSelect);return;
		}
        if(confirm(il8n.areYouSure)){
            var ids = "";
            //遍历每一行元素,获得 id 
            for(var i=0; i<selected.length; i++){
                ids += selected[i].id+","
            }
            ids = ids.substring(0,ids.length-1);            
            $.ajax({
                url: myAppServer() + "&class=education_paper&function=delete",
                data: {
                    ids: ids 
                    
                    ,username: top.basic_user.username
                    ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                    ,search: $.ligerui.toJSON( basic_user.searchOptions )
                    ,user_id: top.basic_user.loginData.id
                    ,user_type: top.basic_user.loginData.type    
                    ,group_id: top.basic_user.loginData.group_id
                    ,group_code: top.basic_user.loginData.group_code  
                },
                type: "POST",
                dataType: 'json',
                success: function(response) {
                    if(response.state==1){
                        $.ligerui.get('education_paper_grid').loadData();
                    }
                },
                error : function(){
                    //网络通信失败,则删除按钮再也不能点了
                    alert(top.il8n.disConnect);
                }
            });                
        }        
    }    
    
    /**
     * 查看一张试卷的详细信息
     * 需要额外的弹出一个 ligerDialog 
     * 并引用 education_paper__view.html?id=
     * 这是一个包含有 HTML 标签的页面
     * 
     * 在查看功能页上,也会有权限操作按钮:
     *   查看创作人用户信息
     *   查看创作人用户组信息
     *   修改
     *   删除
     * 在查看功能页面上,末尾有一个 fieldset 标签,显示一些通用的业务数据:
     *   id
     *   status
     *   type
     *   time_created
     *   time_lastupdated
     *   count_update
     *   id_creater
     *   id_creater_group
     *   code_creater_group
     *   remark 
     * */
    ,view: function(){
    	var id = getParameter("id", window.location.toString() );
    	$(document.body).html("<div id='buttons' style='width:95%' ></div><div id='content' style='width:98%;margin-top:5px;'></div>");
    	var htmls = "";
    	$.ajax({
            url: myAppServer() + "&class=education_paper&function=view",
            data: {
                id:id 
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code  
            },
            type: "POST",
            dataType: 'json',
            success: function(response) {
            	for(var j in response){   
            		if(j=='sql')continue;
            		if(j=='id'||j=='remark')htmls+="<div style='width:100%;float:left;display:block;margin-top:5px;'/>";
            		eval("var key = getIl8n('education_paper','"+j+"');");
            		htmls += "<span class='view_lable'>"+key+"</span><span class='view_data'>"+response[j]+"</span>";
            	}; 
            	$("#content").html(htmls);
            	
            	//查看详细,页面上也有按钮的
            	var items = [];            	
                var permission = top.basic_user.permission;
                for(var i=0;i<permission.length;i++){
                    if(permission[i].code=='15'){
                        permission = permission[i].children;
                        break;
                    }
                }      
                for(var i=0;i<permission.length;i++){
                    if(permission[i].code=='1502'){
                    	if(typeof(permission[i].children)=='undefined')return;
                        permission = permission[i].children;
                        break;
                    }
                }    
                
                for(var i=0;i<permission.length;i++){        	
                    if(permission[i].code=='150201'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon , click : function(){
                                
                            }
                        });
                    }else if(permission[i].code=='150202'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon, click : function(){                            	
                                
                            }
                        });
                    }else if(permission[i].code=='150222'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon, click : function(){
                                
                            }
                        });
                    }else if(permission[i].code=='150223'){
                        items.push({line: true });
                        items.push({
                            text: permission[i].name , img:permission[i].icon, click : function(){
                                
                            }
                        });
                    }
                }
                if(items.length>0){
	            	$("#buttons").ligerToolBar({
	            		items:items
	            	});
            	}
            },
            error : function(){               
                alert(top.il8n.disConnect);
            }
        });
    }
    
    ,insert: function(){
		$(document.body).append('<form id="form"></form>');
		
		$("#form").ligerForm({
			inputWidth: 170, labelWidth: 90, space: 40,
			fields: [
			     { display: top.il8n.education_paper.subject_code, name: "subject_code", type: "text", validate: {required:true,minlength:3,maxlength:20} }
				,{ display: top.il8n.education_paper.title, name: "title", type: "text", validate: {required:true,minlength:3,maxlength:20} }
				,{ display: top.il8n.education_paper.cost, name: "cost", type: "text", validate: {required:true,minlength:3,maxlength:20} }
			]
		});
		$("#form").append('<br/><br/><br/><br/><table style="width:80%"><tr><td style="width:25%"><input type="submit" value="'+top.il8n.submit+'" class="l-button l-button-submit" /></td></tr></table>' );
		
		var v = $("#form").validate({
			debug: true,
			errorPlacement: function (lable, element) {
				if (element.hasClass("l-textarea")) {
				element.addClass("l-textarea-invalid");
				}
				else if (element.hasClass("l-text-field")) {
				element.parent().addClass("l-text-invalid");
				} 
			},
			success: function (lable) {
				var element = $("[ligeruiid="+$(lable).attr('for')+"]",$("form"));
				if (element.hasClass("l-textarea")) {
					element.removeClass("l-textarea-invalid");
				} else if (element.hasClass("l-text-field")) {
					element.parent().removeClass("l-text-invalid");
				}
			},
			submitHandler: function () {
				
			}
		});
    }
    
    /**
     * 需要额外的弹出一个 ligerDialog 
     * 并引用 education_paper__update.html?id=
     * 这是一个包含有 HTML 标签的页面
     * 
     * */
    ,update: function(){
    	
    }
};

/**
 * 卷子
 * 这个类是 考卷,随机组卷 的父类
 * */
var paper = {
    
	objName: 'paper',
    questions : [],   //题目集
    count : {
        giveup : 0,   //漏题数量,放弃不做的
        right : 0,    //作对数
        wrong : 0,    //做错
        total : 0,     //题目总数
        byTeacher : 0  //需要教师批改的题目总数
    },

    state : '',       //试卷状态 
    mode : 'server',  //服务端模式或者 client 单机模式
    cent : 0,         //卷子总分
    cent_ : 0,        //我的得分
    
    id_paper : null,      //试卷数据库中的 id 编号
    brief: {foo:'bar'},
    
    /**
     * 点击了题目导航处的序号
     * 试卷会翻滚定位到这个题目处
     * */
    wls_quiz_nav : function(id) {
        $("#wls_quiz_main").parent().scrollTop($("#wls_quiz_main").parent().scrollTop() * (-1));
        var target = $('#wls_quiz_main').find('#w_qs_'+id);
        $("#wls_quiz_main").parent().scrollTop($(target).offset().top-30);
    }

    /**
     * 初始化页面布局
     * 页面分为左右两部分,
     *   左边部分是导航按钮 , 做题统计 , 题目提交按钮 ,被包含在一个 Accordion Panel 空间中
     *   右边部分是卷子的题目浏览
     * */
    ,initLayout : function() {
		 $(document.body).append(''+
		 '<div id="layout1"> '+ 
		 ' <div position="left" title="导航"> '+
		 ' <table><tr><td> '+
		 ' <div id="navigation" ></div> '+
		 ' </td></tr> '+
		 ' <tr><td> '+
		 ' <br/> '+
		 ' <div id="paperBrief" style=" background-color: #FAFAFA; border: 1px solid #DDDDDD;" ></div>'+
		 ' </td></tr> '+
		 ' <tr><td> '+
		 ' <br/> '+
		 ' <input id="submit" style="width:100px;" class="l-button l-button-submit" onclick="'+this.objName+'.submit();" value="提交"></input> '+
		 ' </td></tr> '+
		 ' </table> '+
		 ' </div> '+
		 ' <div position="center" title="标题" ><div type="submit" id="wls_quiz_main" class="w_q_container"></div></div> '+
		 '</div> '+
		 '');
        
        $("#layout1").ligerLayout(); 
    }  
    
    /**
     * 在页面上初始化所有题目
     * 每一种题型,都有 initDom() 这个函数
     * */
    ,initQuestions : function() {
    	
    	var quesData = this.questions;
    	var questions_ = [];
    	
    	var index = 1;
        for(var i=0;i<quesData.length;i++){
            var question = null;
            if(quesData[i].type==1){//单项选择题
                question = new question_choice();
                question.optionlength = quesData[i].optionlength;
                question.options = [];
                for(var ii=1;ii<=parseInt(quesData[i].optionlength);ii++){
                    eval("question.options.push(quesData[i].option"+ii+")");
                }
                question.index = index;index++;
                question.layout = quesData[i].layout;
                question.title = quesData[i].title;                       
            }
            else if(quesData[i].type==2){//多项选择题
                question = new question_multichoice();
                question.optionlength = quesData[i].optionlength;
                question.index = index;index++;
                question.layout = quesData[i].layout;
                question.title = quesData[i].title;
                question.options = [];
                for(var ii=1;ii<=parseInt(quesData[i].optionlength);ii++){
                    eval("question.options.push(quesData[i].option"+ii+")");
                }
            }
            else if(quesData[i].type==3){//判断题
                question = new question_check();
                question.index = index;index++;
                question.layout = quesData[i].layout;
                question.title = quesData[i].title;
                question.options = [quesData[i].option1,quesData[i].option2];
            }else if(quesData[i].type==7){//大题, 不需要题编号
                question = new question_big();
                question.title = quesData[i].title;
            }else if(quesData[i].type==4){//填空题
                question = new question_blank();
                if(quesData[i].cent!=0){//填空题题干不需要题编号
                    question.index = index;
                    index++;
                }
                question.title = quesData[i].title;
            }else if(quesData[i].type==5){//组合题, 不需要题编号
                question = new question_mixed();
                question.title = quesData[i].title;
            }else{
                continue;
            }
            //console.debug(index+"     "+quesData[i].type);
            question.type = quesData[i].type;
            question.path_listen = quesData[i].path_listen;
            question.cent = quesData[i].cent;
            question.id = quesData[i].id;
            question.id_parent = quesData[i].id_parent;
            question.paper = this;
            question.initDom();
            questions_.push(question);
        }    	
        this.questions = questions_;   	

    	$('#wls_quiz_main').parent().css("overflow","auto");
     }
     
     /**
      * 在题目导航处,依次添加各个题目的导航按钮
      * 点击导航按钮,右侧的卷子就会翻滚定位
      * */
    ,initNavigation : function() {
         var str = '';
         var index = 1;
         for (var i = 0; i < this.questions.length; i++) {
             var type = this.questions[i].type;
             if( type==1||type==2||type==3||(type==4 && this.questions[i].cent!=0)||(type==6)){
                 str += "<div class='w_q_sn_undone' id='w_q_subQuesNav_"
                         + this.questions[i].id
                         + "' onclick='paper.wls_quiz_nav("
                         + this.questions[i].id
                         + ")' style='height:18px;'><a href='#' style='border:0px;font-size:10px;margin-top:2px;' >"
                         + index + "</a></div>";
                 index ++;
             }
         }
         $("#navigation").append(str);
    }
    
    ,initBrief : function(){   
    	$(".l-layout-header",$(".l-layout-center")).html( this.title );
    	var htmlStr = "";
    	for(var j in this.brief){    		
    		eval("var value = this.brief."+j);
    		eval("var key = top.il8n.education_"+this.objName+"."+j);
    		htmlStr += "<span class='brief' style='width:50px;'>"+key+"</span><span class='brief'>&nbsp;"+value+"</span><br/>";
    	}; 
    	
        $('#paperBrief').html(htmlStr);
        //setInterval($('#paperBrief').fadeOut(500).fadeIn(500),2000);
    }
    
    ,readPaper : function(afterAjax){
        var id = getParameter("id", window.location.toString() );
        this.id_paper = id;
        var paperObj = this;
        $.ajax({
             url :  myAppServer()+ "&class=education_paper&function=view&id="+id
            ,type : "POST"
            ,data : {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code   
            }      
            ,dataType: 'json'
            ,success : function(data) {  
            	/*
                paper.cent = data.cent;
                paper.count.total = data.count_questions;
                paper.subjectCode = data.subjectCode;
                paper.subjectName = data.subject;
                
                paper.cost = parseInt(data.cost);
                paper.setPaperBrief();
                */
            	paperObj.title = data.title;
            	paperObj.brief = {
     		       subject_name: data.subject_name
     		       ,cost: data.cost
    		       ,count_questions: data.count_questions
    		       ,teacher_name: data.teacher_name
    		       ,cent: data.cent
            	};
                
                if ( typeof(afterAjax) == "string" ){
	                eval(afterAjax);
	            }else if( typeof(afterAjax) == "function"){
	                afterAjax();
	            }				
            }
            ,error : function(){
                $.ligerDialog.error('网络通信失败');
            }
        });
    }   
    
    ,readQuestions: function(afterAjax){
        var id = this.id_paper;
        var paperObj = this;
        $.ajax({
            url : myAppServer()+ "&class=education_question&function=getForPaper",
            type : "POST",
            data : {id:id
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code   
            },
            dataType: 'json',
            success : function(responseData) {
                if(responseData.state!=1){
                    alert(responseData.msg);return;
                }

                $('#paperBrief').append("剩余金币:" + responseData.moneyLeft + "<br/>" );
                paperObj.questions = responseData.Rows;
                
                if ( typeof(afterAjax) == "string" ){
	                eval(afterAjax);
	            }else if( typeof(afterAjax) == "function"){
	                afterAjax();
	            }		                
                
            },
            error : function(){
                $.ligerDialog.error('网络通信失败');
            }
        });
    }
    
    /**
     * 练习卷模式,提交试卷
     * 提交后,服务端立刻计算试卷的做对做错情况
     * 并返回解题思路,直接显示到前端试卷上
     * 与 统考模式 不同
     * */
    ,submit: function(){
    	if(this.mode=='client'){
    		this.showDescription();
    		return;
    	}
    	/*
    	if(top.basic_user.loginData.type!='2'){
    		alert("only student can submit");
    		return;
    	}
    	*/
        if(this.state=='submitted'){
            alert("paper has submitted arleady");
            return;
        }
        this.state = 'submitted';
        $('#submit').val(top.il8n.waitting);

        var toSend = [];
        var ids = "";
        for(var i=0;i<this.questions.length;i++){
            toSend.push({
                id:this.questions[i].id,
                myanswer:this.questions[i].getMyAnswer()
            });
            ids += this.questions[i].id+",";//搜集所有题目的编号
        }        
        ids = ids.substring(0,ids.length-1);//去掉最后一个 ,  TODO 
        var paperObj = this;
        
        $.ajax({
            url : "../php/myApp.php?class=education_paper&function=submit",
            type : 'POST',
            data : {
                 json: $.ligerui.toJSON(toSend)
                ,id: paperObj.id_paper
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( basic_user.searchOptions )
                ,user_id: top.basic_user.loginData.id
                ,user_type: top.basic_user.loginData.type    
                ,group_id: top.basic_user.loginData.group_id
                ,group_code: top.basic_user.loginData.group_code
            }, 
            dataType: 'json',
            success : function(data) {
                var questions = data.questions;
                //console.debug(questions.length);
                for(var i=0;i < questions.length;i++){
                    //console.debug(questions[i]+" "+i);
                	paperObj.questions[i].answer = questions[i].answer;
                	paperObj.questions[i].description = questions[i].description;
                    
                }                
                paperObj.showDescription();
                
                paperObj.brief = {
                	totalCent: data.paper.totalCent
                	,myTotalCent: data.paper.myTotalCent
                	,count_giveup: data.paper.count_giveup
                	,count_right: data.paper.count_right
                	,count_wrong: data.paper.count_wrong
                };
                paperObj.initBrief();
                
                $('#submit').remove();
            }
        });
    } 
    
    /**
     * 显示解题思路,并标注对错情况
     * 试卷总分错题总数,不会在此处理
     * 不过在 question 中,会累加错题总数
     * */
    ,showDescription : function(){
        for(var i=0;i < this.questions.length;i++){
        	this.questions[i].showDescription();
        }    	
    }    
};