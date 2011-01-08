wls.question = Ext.extend(wls, {
	
	 id:null
	,index:null		 
	,id_quiz_paper:null
	,answerData:null
	,questionData:null	
	,cent:null
	,mycent:null	
	,type:null
	,parent:null
	,quiz:null
	
	,addWhyImWrong:function(){
	
	}
	,addCommenter:function(){
		
	}
	,addListening:function(){
		
	}
	,showComments:function(){
		
	}
	,showWrongs:function(){
		
	}
	
});

var wls_question_toogle = function(id){
	$('#w_qs_d_t_'+id,$('#w_qs_'+id)).toggleClass('w_q_d_t_2');
	$(".w_q_d",$('#w_qs_'+id)).toggleClass('w_q_d_h');
}