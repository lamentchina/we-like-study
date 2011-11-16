/**
 * 试卷
 * */
wls.quiz.paper = Ext.extend(wls.quiz, {
	
	store : function(data){
		for(var i=0;i<data.length;i++){
			var ques = new wls.question.choice();
			ques.title = data[i].title;
			ques.options = data[i].options;
			ques.cent = data[i].cent;
			ques.answer = data[i].answer;
			ques.description = data[i].description;
			wlsData.questions.push(ques);//全局变量
			ques.id = (i+1);
			
			this.questions.push(ques);
		}
	}
});


var wls_quiz_paper = function(){
	
	var grid = new Ext.grid.GridPanel({
	    store: wlsData.stores[0],
	    columns: [
            {id:'index',header: ' ' ,dataIndex: 'index',width:20},
	        {id:'title',header: '标题' ,dataIndex: 'title'},	        
	        {id:'title',header: '题总数' ,dataIndex: 'count'},
	        {id:'title',header: '总分' ,dataIndex: 'cent'},
	        {id:'title',header: '得分' ,dataIndex: 'cent_'}
	    ],
	    stripeRows: true,
	    height: 350,
	    width: 600,
	    stateful: true,
	    stateId: 'grid'        
	});
	
	grid.on('celldblclick',function(grid,row,col,rec){
		var index = grid.store.getAt(row).get("index");		
		var panel = wlsData.papers[index].initLayout();	

		var w = new Ext.Window({
			title : il8n.general.detail,
			id : 'p_h_l_win_detail_',
			width : '95%',
			height : 560,
			layout : 'fit',
			modal : true,
			items : [panel],
			listeners :{
				hide : function(){
					w.destroy();
				}
			}
		});
		w.show();		
			
		wlsData.papers[index].initQuestions();
	});
	return grid;
}