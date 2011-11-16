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
	
	var data = [];
	for(var i=0;i<data_to_load.length;i++){
		data.push([data_to_load[i].title,i]);
	}

	var store = new Ext.data.ArrayStore({
	    fields: [
	       {name: 'title'},
	       {name: 'index'}
	    ]
	});
	store.loadData(data);
	
	var grid = new Ext.grid.GridPanel({
	    store: store,
	    columns: [
	        {id:'title',header: '标题' ,dataIndex: 'title'},
	        {id:'index',header: '序号' ,dataIndex: 'index'}
	    ],
	    stripeRows: true,
	    height: 350,
	    width: 600,
	    stateful: true,
	    stateId: 'grid'        
	});
	
	grid.on('celldblclick',function(grid,row,col,rec){
		var index = grid.store.getAt(row).get("index");
		
		var data = wlsData.papers[index];
		if(wlsData.papers.length<=index){
			wlsData.papers.push(new wls.quiz.paper());
			wlsData.papers[index].objName = 'wlsData.papers['+index+']';
			wlsData.papers[index].store(data_to_load[index].questions);	
		}
		
		
		
		var panel = wlsData.papers[index].initLayout();	

		var w = new Ext.Window({
			title : il8n.general.detail,
			id : 'p_h_l_win_detail_',
			width : '90%',
			height : 550,
			layout : 'fit',
			modal : false,
			items : [panel]
		});
		w.show();
		
			
		wlsData.papers[index].initQuestions();
	});
	return grid;
}