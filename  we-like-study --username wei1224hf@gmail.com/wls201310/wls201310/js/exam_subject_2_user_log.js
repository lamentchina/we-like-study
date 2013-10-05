var exam_subject_2_user_log = {
	
	
	 config: null
	,loadConfig: function(afterAjax){
		$.ajax({
			url: config_path__exam_subject_2_user_log__loadConfig
			,dataType: 'json'
	        ,type: "POST"
	        ,data: {
                 executor: top.basic_user.loginData.username
                ,session: top.basic_user.loginData.session
	        } 			
			,success : function(response) {
				exam_subject_2_user_log.config = response;
				if ( typeof(afterAjax) == "string" ){
					eval(afterAjax);
				}else if( typeof(afterAjax) == "function"){
					afterAjax();
				}
			}
			,error : function(){				
				alert(top.getIl8n('disConnect'));
			}
		});	
	}	
	
	,initDom: function(){
		$('body').append("<form id='form'></form>");
		
		$('body').append('<div id="container" style="width: 99%; height: 400px; margin: 0 auto"></div>');		
		$("#form").ligerForm({
		    inputWidth: 170, labelWidth: 90, space: 40,
		    fields: [
			     { display: top.getIl8n("time_start"), name: "time_start",  type: "date", validate: {required:true} }
			    ,{ display: top.getIl8n("time_stop"), name: "time_stop",  type: "date" ,newline: false, validate: {required:true}  }
				,{ display: top.getIl8n('exam_paper','subject'), name: "subject", newline: true, type: "select", options: {data : exam_subject_2_user_log.config.subject, valueField : "code" , textField: "value" },  validate: {required:true}  }
				,{ display: top.getIl8n('time'), name: "gap", newline: true, type: "select", options: {data : [{'code':'day','value':top.getIl8n('day')},{'code':'month','value':top.getIl8n('month')}], valueField: "code" , textField: "value" }, newline: false, validate: {required:true}  }
				,{ display: top.getIl8n('type'), name: "type", newline: true, type: "select", options: {data : [{'code':'line','value':top.getIl8n('exam_subject_2_user_log','lineChart')},{'code':'rader','value':top.getIl8n('exam_subject_2_user_log','raderChart')}], valueField: "code" , textField: "value" }, newline: false, validate: {required:true}  }
		    ]
		});			
		
		$('#form').append('<br/><br/><input type="submit" value="'+top.getIl8n('statistic')+'" id="basic_user__submit" class="l-button l-button-submit" />' );
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
				exam_subject_2_user_log.statistics();
			}
		});		
	}
	
	,statistics: function(){
		var type = $.ligerui.get('type').getValue();
		if(type=='line'){		
			$.ajax({
			    url: config_path__exam_subject_2_user_log__statistics_time
			    ,dataType: 'json'
			    ,type: "POST"
			    ,data: {
			    	 executor: top.basic_user.loginData.username
			        ,time_start: $('#time_start').val()
			       	,time_stop: $('#time_stop').val()
			       	,gap: $.ligerui.get('gap').getValue()
			       	,subject: $.ligerui.get('subject').getValue()
			    }           
			    ,success : function(response) {
			    	var categories = [];
			    	var data = [];
			    	for(var i=0;i<response.data.length;i++){
			    		categories.push(response.data[i].time);
			    		data.push(response.data[i].data*1);
			    	}
			    	
			        $('#container').highcharts({
			        	title: '',
			            chart: {
			                type: 'line',
			                marginRight: 130,
			                marginBottom: 25
			            },
			            xAxis: {
			                categories: categories
			            },
			            yAxis: {min: 30, max: 100, title: {text: '成绩'}},
	
			            legend: {
			                layout: 'vertical',
			                align: 'right',
			                verticalAlign: 'top',
			                x: -10,
			                y: 100,
			                borderWidth: 0
			            },
			            series: [{
			                
			                data: data
			            }]
			        });
			        $('.highcharts-container').css('overflow','');
	
			    }
			    ,error : function(){                
			        alert(top.il8n.disConnect);
			    }
			});
		}else{
			$.ajax({
			    url: config_path__exam_subject_2_user_log__statistics_subject
			    ,dataType: 'json'
			    ,type: "POST"
			    ,data: {
			    	 executor: top.basic_user.loginData.username
			        ,time: $('#time_start').val()
			       	,subject: $.ligerui.get('subject').getValue()
			       	,gap: $.ligerui.get('gap').getValue()
			    }           
			    ,success : function(response) {
			    	var categories = [];
			    	var data = [];
			    	for(var i=0;i<response.data.length;i++){
			    		categories.push(response.data[i].subject_name);
			    		data.push(response.data[i].data*1);
			    	}
			    	
			        $('#container').highcharts({
			            
			    	    chart: {
			    	        polar: true
			    	    },
			    	    
			    	    title: {
			    	        text: ''
			    	    },
			    	    
			    	    pane: {
			    	        startAngle: 0,
			    	        endAngle: 360
			    	    },
			    	
			            xAxis: {
			    	        tickInterval: 1,
			    	        min: 0,
			    	        max: data.length,
			    	        labels: {
			    	        	formatter: function () {
			    	        		return categories[this.value];
			    	        	}
			    	        }
			            },
			    	        
			    	    yAxis: {
			    	        min: 20
			    	    },    
			    	    
			    	    series: [  {
			    	        type: 'area',
			    	        name: $('#subject').val(),
			    	        data: data
			    	    }]
			    	});
			        $('.highcharts-container').css('overflow','');
	
			    }
			    ,error : function(){                
			        alert(top.il8n.disConnect);
			    }
			});
		}
	}
};