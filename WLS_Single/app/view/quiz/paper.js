/**
 * 试卷
 * */
wls.quiz.paper = Ext.extend(wls.quiz, {
	
	store : function(data){
		for(var i=0;i<data.length;i++){
			var ques = new wls.question.choice();
			ques.title = data[i].title;
		}
	}
});