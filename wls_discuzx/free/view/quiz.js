wls.quiz = Ext.extend(wls, {
	
	answersData:null,
	questionsData:null,
	questionsIds:null,
	questions:[],
	count:{
		giveup:0,
		right:0,
		wrong:0,
		mannual:0,
		total:0
	},
	
	/**
	 * 0 尚未初始化 
	 * 1已经得到题目编号数据 
	 * 11已经初始化编号  
	 * 2已经得到题目数据 
	 * 21已经初始化题目 
	 * 3用户提交答案 
	 * 4得到服务端返回的答案
	 * 41处理了题目的对错判断
	 * 42添加了注释
	 * */
	state:0,
	cent:0,
	mycent:0,	
	naming:null,
	
	addQuestions:function(){
		var obj = this.questionsData;
		var index = 1;
		for(var i=0;i<obj.length;i++){
			var ques = null;			
			switch(obj[i].type){
				case '1':
					ques = new wls.question.choice();
					ques.index = index;
					index++;
					ques.questionData = obj[i];
					ques.id = obj[i].id;
					ques.quiz = this;
					break;	
				case '2':
					ques = new wls.question.multichoice();
					ques.index = index;
					index++;
					ques.questionData = obj[i];
					ques.id = obj[i].id;
					ques.quiz = this;
					break;	
				case '3':
					ques = new wls.question.check();
					ques.index = index;
					index++;
					ques.questionData = obj[i];
					ques.id = obj[i].id;
					ques.quiz = this;
					break;					
				case '5':
					ques = new wls.question.big();
					ques.index = '';
					ques.questionData = obj[i];
					ques.id = obj[i].id;
					ques.quiz = this;
					break;												
				default :
					break;
			}
			if(ques!=null){
				ques.initDom();
				this.questions.push(ques);
			}				
		}
		this.addNavigation();
		this.status = 2;
	},
	addNavigation:function(){
		
		var str = '';
		var index = 0;
		for(var i=0;i<this.questions.length;i++){
			if(this.questions[i].index=='')continue;
			var temp = index+1;
			if(temp<10){
				temp = '0'+temp;
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.questions[i].id+"' onclick='"+this.naming+".wls_quiz_nav("+this.questions[i].id+")'><a href='#' style='border:0px;'>"+temp+"</a></div>";
			}else if(temp>=10 && temp <100){
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.questions[i].id+"' onclick='"+this.naming+".wls_quiz_nav("+this.questions[i].id+")'><a href='#' style='border:0px;'>"+temp+"</a></div>";
			}else{
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.questions[i].id+"' onclick='"+this.naming+".wls_quiz_nav("+this.questions[i].id+")' style='height:18px;'><a href='#' style='border:0px;font-size:10px;margin-top:2px;' >"+temp+"</a></div>";
			}	
			index++;
		}
		$("#navigation").append(str);	
		
	},
	wls_quiz_nav:function(id){
		$("#wls_quiz_main").scrollTop($("#wls_quiz_main").scrollTop()*(-1));
		var num = $("#w_qs_"+id).offset().top-150;
		$("#wls_quiz_main").scrollTop(num);
	},
	addAnswers:function(){
		
	},
	addDescriptions:function(){
		this.count.giveup = 0;
		this.count.total = 0;
		for(var i=0;i<this.questions.length;i++){
			this.questions[i].showDescription();
		}
		this.state = 42;
	},
	addList:function(){
		
	},
	addMyList:function(){
		
	},
	ajaxQuestions:function(nextFunction){
		var thisObj = this;
		$.blockUI({
			message: '<h1>'+il8n.Question+il8n.Loading+'</h1><br/>'+il8n.Wait+'......' 
		}); 
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz&action=getQuestions",
			data: {questionsIds:thisObj.questionsIds},
			type: "POST",
			success: function(msg){
				thisObj.questionsData = jQuery.parseJSON(msg);
				thisObj.state = 2;
				$.unblockUI();
				Ext.getCmp('ext_Operations').layout.setActiveItem('ext_Navigation');
				
				eval(nextFunction);
			}
		});
	},	
	initLayout:function(){	
		var thisObj = this;
		var viewport = new Ext.Viewport({
	        layout: 'border',
	        items: [{
		            collapsible: false,
		            region: 'center',
		            margins: '5 0 0 0',
		            html: '<div id="wls_quiz_main" class="w_q_container"></div>'
		        },{
		        
	            title: il8n.Operations,
	            collapsible: true,		        	
			    layout: 'border',
			    region:'west',		            
	            split: true,
                width: 200,
                minSize: 175,
                maxSize: 400,			    
			    defaults: {
			        collapsible: true,
			        split: true,
			        animFloat: false,
			        autoHide: false,
			        useSplitTips: true			        
			    },	
	        	items:[	  
	        		new Ext.Button({
	        			id:'quiz_submit',
	        			text:il8n.Submit, region:'south',
	        			handler:function(){
	        				thisObj.submit(thisObj.naming+".addDescriptions();");
	        				Ext.getCmp('quiz_submit').disable();
	        			}
	        		}),{
	        		id:'ext_Operations',
					collapsible: false,		
		            region:'center',
		            floatable: false,
		            margins: '0 0 0 0',
		            cmargins: '5 5 0 0',
		            split: true,
	                width: 200,
	                minSize: 175,
	                maxSize: 400,
	                
	                layout: {
	                    type: 'accordion',
	                    animate: true
	                },	
				    items: [{
				    	id:'ext_Navigation',
				        title: il8n.Navigation,
				        html: '<div id="navigation"></div>'
				    },{
				    	id:'ext_Brief',
				        title: il8n.Paper.Brief,
				        html: '<div id="paperBrief"></div>'
				    }]
	
		        }]
	        }]		        
		});
		$("#wls_quiz_main").css("height",$(document).height()-10);
	}
	,initLayoutSaminWindow:function(domid){
		var viewport = new Ext.Panel({
	        layout: 'border',
	        items: [{
		            collapsible: false,
		            region: 'center',
		            margins: '5 0 0 0',
		            html: '<div id="wls_quiz_main" class="w_q_container"></div>'
		        },{
		        
	            title: il8n.Operations,
	            collapsible: true,		        	
			    layout: 'border',
			    region:'west',		            
	            split: true,
                width: 200,
                minSize: 175,
                maxSize: 400,			    
			    defaults: {
			        collapsible: true,
			        split: true,
			        animFloat: false,
			        autoHide: false,
			        useSplitTips: true			        
			    },	
	        	items:[	  
	        		new Ext.Button({
	        			id:'quiz_submit',
	        			text:il8n.Submit, region:'south',
	        			handler:function(){
	        				thisObj.submit(thisObj.naming+".addDescriptions();");
	        				Ext.getCmp('quiz_submit').disable();
	        			}
	        		}),{
	        		id:'ext_Operations',
					collapsible: false,		
		            region:'center',
		            floatable: false,
		            margins: '0 0 0 0',
		            cmargins: '5 5 0 0',
		            split: true,
	                width: 200,
	                minSize: 175,
	                maxSize: 400,
	                
	                layout: {
	                    type: 'accordion',
	                    animate: true
	                },	
				    items: [{
				    	id:'ext_Navigation',
				        title: il8n.Navigation,
				        html: '<div id="navigation"></div>'
				    },{
				    	id:'ext_Brief',
				        title: il8n.Paper.Brief,
				        html: '<div id="paperBrief"></div>'
				    }]
	
		        }]
	        }]		        
		});
		return viewport;
	}
});

