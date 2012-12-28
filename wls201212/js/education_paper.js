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
    version: '201211'    
        
    ,ajaxState: false        

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
                ,userid: top.basic_user.loginData.id
                ,usergroup: top.basic_user.loginData.id_group
                ,usertype: top.basic_user.loginData.type
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
    
    ,gridPermissions: []
    
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
  	         { display: top.il8n.title, name: 'title', align: 'left', width: 170, minWidth: 60 }
	        ,{ display: top.il8n.education_paper.subject, name: 'subjectname',isSort : false }
	        ,{ display: top.il8n.education_paper.cent, name: 'cent' ,width: 50 ,isSort : false}
	        ,{ display: top.il8n.education_paper.cost, name: 'cost' ,width: 50}
	        ,{ display: top.il8n.education_paper.author, name: 'author' ,width: 90}
	        ,{ display: top.il8n.education_paper.count_questions, name: 'count_questions',width: 55,isSort : false }
	        ,{ display: top.il8n.education_paper.count_used, name: 'count_questions',width: 55,isSort : false }
	        ,{ display: top.il8n.time_created, name: 'time_created'}
    	   ]
           ,[//教师列
   	         { display: top.il8n.title, name: 'title', align: 'left', width: 170, minWidth: 60 }
   	        ,{ display: top.il8n.education_paper.subject, name: 'subjectname',isSort : false }
   	        ,{ display: top.il8n.education_paper.cent, name: 'cent' ,width: 50 ,isSort : false}
   	        ,{ display: top.il8n.education_paper.cost, name: 'cost' ,width: 50}
   	        ,{ display: top.il8n.education_paper.author, name: 'author' ,width: 90}
   	        ,{ display: top.il8n.education_paper.count_questions, name: 'count_questions',width: 55,isSort : false }
   	        ,{ display: top.il8n.education_paper.count_used, name: 'count_questions',width: 55,isSort : false }
   	        ,{ display: top.il8n.time_created, name: 'time_created'}
           ]
           ,[//学生列
	         { display: top.il8n.title, name: 'title', align: 'left', width: 170, minWidth: 60 }
   	        ,{ display: top.il8n.education_paper.subject, name: 'subjectname',isSort : false }
   	        ,{ display: top.il8n.education_paper.cost, name: 'cost' ,width: 50}
   	        ,{ display: top.il8n.education_paper.mycent, name: 'mycent' ,width: 50, render: function(a,b){
				if(a.mycent==null)return '没做过';
				return a.mycent;
			 }}
           ]
        ];
        
        var config = {
            height:'100%',
            columns: [],  pageSize:20 ,rownumbers:true,
            url : myAppServer()+ "&class=education_paper&function=grid",
            method  : "POST",
            id : "education_paper_grid",
            parms : {
                username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                ,search: $.ligerui.toJSON( education_paper.searchOptions )
                ,userid: top.basic_user.loginData.id
                ,usergroup: top.basic_user.loginData.id_group
                ,usertype: top.basic_user.loginData.type                
            },
            toolbar: { items: [] }
        };
        
        if(top.basic_user.loginData.type==='1'){
        	config.columns = gridColmuns[0];
        }
        if(top.basic_user.loginData.type==='2'){
        	config.columns = gridColmuns[2];
        }
        if(top.basic_user.loginData.type==='3'){
        	config.columns = gridColmuns[1];
        }
        
        var permission = [];
        for(var i=0;i<top.basic_user.permission.length;i++){
            if(top.basic_user.permission[i].code=='15'){
                permission = top.basic_user.permission[i].children;
            }
        }
        
        for(var i=0;i<permission.length;i++){
            if(permission[i].code=='1501'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){
                        education_paper.search();
                    },disable:true
                });
            }else if(permission[i].code=='1511'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon , click : function(){
                        education_paper.upload();
                    }
                });
            }else if(permission[i].code=='1503'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        var id = education_paper.grid.getSelected().id;

                    }
                });
            }else if(permission[i].code=='1522'){
                config.checkbox = true;
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        education_paper.delet();
                    }
                });
            }else if(permission[i].code=='1505'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        
                    }
                });
            }else if(permission[i].code=='1590'){
                config.toolbar.items.push({line: true });
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
                            modal: false, title: $.ligerui.get('education_paper_grid').getSelected().title, slide: false
    
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
            }else if(permission[i].code=='1507'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        
                    }
                });
            }else if(permission[i].code=='1508'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        var arr = education_paper.grid.getCheckedRows();
                        if(arr.length==0){
                            $.ligerDialog.error(il8n.GRID.noSelect);
                            return;
                        }
                        var url = 'education_paper__do.html?id='+arr[0].id;
                        if(top===self){
                            $.ligerDialog.open({
                                id : "paper" , height:  550, url: url, width: 650, title: title, slide: false,
                                showMax: true, showToggle: true, showMin: true, isResize: true, modal: false
                            });
                        }else{
                            top.desktop.f_open(url,arr[0].name,"../file"+permission[i].icon,4);
                        }
                        win.max();
                    }
                });
            }else if(permission[i].code=='1509'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({                    
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        
                    }
                });
            }else if(permission[i].code=='1510'){
                config.toolbar.items.push({line: true });
                config.toolbar.items.push({                    
                    text: permission[i].name , img:permission[i].icon, click : function(){
                        
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
                    { display: top.il8n.education_subject.subject, name: "subject", newline: true, type: "select", comboboxName: "combo_select", options: { url:'../php/myApp.php?class=education_subject&function=getList', valueField : "code" , textField : "name",slide:false } },
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
    
    ,upload : function(){
        var dialog;
        if($.ligerui.get("education_paper__grid_upload_d")){
            dialog = $.ligerui.get("education_paper__grid_upload_d");
            dialog.show();
        }else{

            $(document.body).append( $("<div id='education_paper__grid_file'></div>"));
            var uploader = new qq.FileUploader({
                element: document.getElementById('education_paper__grid_file'),
                action: '../php/myApp.php?class=education_paper&function=import',
                allowedExtensions: ["xls"],
                params: {username: top.basic_user.username,
                    session: MD5( top.basic_user.session +((new Date()).getHours()))},
                downloadExampleFile : "../file/download/education_paper.xls",
                debug: true,
                onComplete: function(id, fileName, responseJSON){
                    education_paper.grid.loadData();
                }
            });    
            
            $.ligerDialog.open({
                title: top.il8n.importFile,
                
                id : "education_paper__grid_upload_d",
                width : 350,
                height : 200,
                target : $("#education_paper__grid_file"),
                modal : true
            });
        }
    }    
    
    ,delet: function(){
        //判断 ligerGrid 中,被勾选了的数据
        selected = education_paper.grid.getSelecteds();
        //如果一行都没有选中,就报错并退出函数
        if(selected.length==0){alert(il8n.noSelect);return;}
        //弹框让用户最后确认一下,是否真的需要删除.一旦删除,数据将不可恢复
        if(confirm(il8n.sureToDelete)){
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
                    
                    //服务端权限验证所需
                    ,username: top.basic_user.username
                    ,session: MD5( top.basic_user.session +((new Date()).getHours()))
                },
                type: "POST",
                dataType: 'json',
                success: function(response) {
                    if(response.state==1){
                        education_paper.grid.loadData();
                    }
                },
                error : function(){
                    //网络通信失败,则删除按钮再也不能点了
                    alert(top.il8n.disConnect);
                }
            });                
        }        
    }    
};



/**
 * 卷子
 * 这个类是 考卷,随机组卷 的父类
 * */
var paper = {
    
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
        '<div id="layout1">         '+
        '    <div position="left" title="导航">    '+
        '        <table><tr><td>                    '+
        '        <div id="navigation" ></div>    '+
        '        </td></tr>                        '+
        '        <tr><td>                        '+
        '        <br/>                            '+
        '        <div id="paperBrief" style=" background-color: #FAFAFA; border: 1px solid #DDDDDD;" ></div>            '+
        '        </td></tr>                        '+
        '        <tr><td>                        '+
        '        <br/>                            '+
        '        <input id="submit" style="width:100px;" class="l-button l-button-submit" onclick="paper.submit();" value="提交"></input>                '+
        '        </td></tr>                        '+
        '        </table>                        '+
        '    </div>                                '+
        '    <div position="center" title="标题" ><div type="submit" id="wls_quiz_main" class="w_q_container"></div></div> '+
        '</div> '+
        '');
        
        $("#layout1").ligerLayout(); 
    }  
    
    /**
     * 在页面上初始化所有题目
     * 每一种题型,都有 initDom() 这个函数
     * */
    ,initQuestions : function() {
         for (var i = 0; i < this.questions.length; i++) {
             this.questions[i].quiz = this;
             this.questions[i].state = 'INIT';
             this.questions[i].cent_ = 0;
             this.questions[i].initDom();
         }
         this.state = 'INIT';
         this.cent = 0;
         this.cent_ = 0;
         
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
    	$(".l-layout-header",$(".l-layout-center")).html( paper.title );
        $('#paperBrief').append("asdfadsfasffdadsf");
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
	            ,userid: top.basic_user.loginData.id
	            ,usergroup: top.basic_user.loginData.id_group
	            ,usertype: top.basic_user.loginData.type
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
    
    ,readQuestions : function(afterAjax){
        var id = this.id_paper;
        var paperObj = this;
        $.ajax({
            url : myAppServer()+ "&class=education_question&function=getForPaper",
            type : "POST",
            data : {id:id
                    ,session : MD5( top.basic_user.session +((new Date()).getHours()))
                    ,username: top.basic_user.username
                    ,userid: top.basic_user.loginData.id
                    ,usergroup: top.basic_user.loginData.id_group
                    ,usertype: top.basic_user.loginData.type
                    },
            dataType: 'json',
            success : function(responseData) {
                if(responseData.state!=1){
                    alert(responseData.msg);return;
                }
                //console.debug(responseData);
                quesData = responseData.Rows;
                $('#paperBrief').append("剩余金币:" + responseData.moneyLeft + "<br/>" );
                
                var index = 1;
                for(var i=0;i<quesData.length;i++){
                    var question = null;
                    if(quesData[i].type==1){//单项选择题
                        question = new question_choice();
                        question.optionLength = quesData[i].optionlength;
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
                        question.optionLength = quesData[i].optionlength;
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
                    question.paper = paperObj;
                    paperObj.questions.push(question);
                }

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
    
    ,submit: function(){
    	if(this.mode=='client'){
    		this.showDescription();
    		return;
    	}
    	if(top.basic_user.loginData.type!='2'){
    		alert("only student can submit");
    		return;
    	}
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
            url : "../php/myApp.php?class=education_paper&function=submitPaper",
            type : 'POST',
            data : {
                 json: $.ligerui.toJSON(toSend)
                ,id: paperObj.id_paper
                ,userid: top.basic_user.loginData.id
                ,usergroup: top.basic_user.loginData.id_group
                ,username: top.basic_user.username
                ,session: MD5( top.basic_user.session +((new Date()).getHours()))
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
                paperObj.count.giveup = parseInt( data.paper.count_giveup );
                paperObj.count.right = parseInt( data.paper.count_right );
                paperObj.count.wrong = parseInt( data.paper.count_wrong );
                paperObj.count.byTeacher = parseInt( data.paper.count_byTeacher );
                paperObj.cent = parseInt( data.paper.totalCent );
                paperObj.cent_ = parseInt( data.paper.myTotalCent );
                
                paperObj.showDescription();
            }
        });
    } 
    
    ,showDescription : function(){
        for(var i=0;i < this.questions.length;i++){
        	this.questions[i].showDescription();
        }    	
    }
    
};