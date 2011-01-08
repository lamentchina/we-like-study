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
	state:0,
	cent:0,
	mycent:0,	
	naming:null,
	
	addQuestions:function(){
		var obj = this.questionsData;
		for(var i=0;i<obj.length;i++){
			var ques = null;
			switch(obj[i].type){
				case '1':
					ques = new wls.question.choice();
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
	},
	addNavigation:function(){
		var str = '';
		for(var i=0;i<this.questions.length;i++){
			var temp = i+1;
			if(temp<10){
				temp = '0'+temp;
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.questions[i].id+"' onclick='"+this.naming+".wls_quiz_nav("+this.questions[i].id+")'><a href='#' style='border:0px;'>"+temp+"</a></div>";
			}else if(temp>=10 && temp <100){
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.questions[i].id+"' onclick='"+this.naming+".wls_quiz_nav("+this.questions[i].id+")'><a href='#' style='border:0px;'>"+temp+"</a></div>";
			}else{
				str += "<div class='w_q_subQuesNav_i' id='w_q_subQuesNav_"+this.questions[i].id+"' onclick='"+this.naming+".wls_quiz_nav("+this.questions[i].id+")' style='height:18px;'><a href='#' style='border:0px;font-size:10px;margin-top:2px;' >"+temp+"</a></div>";
			}	
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
		for(var i=0;i<this.questions.length;i++){
			this.questions[i].showDescription();
		}
	},
	addList:function(){
		
	},
	addMyList:function(){
		
	},
	ajaxQuestions:function(nextFunction){
		var thisObj = this;
		$.ajax({
			url: thisObj.config.AJAXPATH+"?controller=quiz&action=getQuestions",
			data: {questionsIds:thisObj.questionsIds},
			type: "POST",
			success: function(msg){
				thisObj.questionsData = jQuery.parseJSON(msg);
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
	        			text:il8n.Submit, region:'south',
	        			handler:function(){
	        				thisObj.submit(thisObj.naming+".addDescriptions();");
	        			}
	        		}),{
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
				        title: il8n.Paper.Brief,
				        html: '<div id="paperBrief"></div>'
				    },{
				        title: il8n.Navigation,
				        html: '<div id="navigation"></div>'
				    }]
	
		        }]
	        }]		        
		});
		$("#wls_quiz_main").css("height",$(document).height()-10);
	}
});

