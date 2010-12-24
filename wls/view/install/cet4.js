var install_cet4 = function(){
	this.getQuesList = function(){
		var quesList = [];

		//所有选择题
		var list = $("td[width='99%']");
		for(var i=0;i<list.length;i++){
			//var title = $(list[i]).html();
			 $(list[i]).html( $(list[i]).html().replace(" )",")") );
			 $(list[i]).html( $(list[i]).html().replace("A)","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace("B)","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace("C)","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace("D)","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace(" .",".") );
			 $(list[i]).html( $(list[i]).html().replace("A.","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace("B.","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace("C.","_OPTION_") );
			 $(list[i]).html( $(list[i]).html().replace("D.","_OPTION_") );	
		}
		
		for(var i=1;i<list.length;i++){
		
			
			var id_ = 0;
			var dom = null;
			var a = $('div:contains(OPTION)',list[i]);
			var b = $('td:contains(OPTION)',list[i]);
			if(a.length==4){
				dom = $("td",$(a[0]).parent().parent());
				id_ = $(dom[0]).text().replace(".","");
			}else if(b.length==4){
				dom = $("td",$(b[0]).parent().parent().parent().parent().parent());
				id_ = $(dom[0]).text().replace(".","");
			}else{
				dom = $("td",$(list[i]).parent());
				id_ = $(dom[0]).text().replace(".","");
			}		
			var desc = $('#s'+(parseInt(id_))+'p').parent().html();
			var obj = {'id':id_,'data':$(dom[1]).text(),'an':$('input[name=s'+id_+'an]').val(),'desc':desc};
			quesList.push(obj);
		}
		
		console.debug(quesList);
		

		//题目组织
		var list = $("input[name='dtisn']");
		console.debug(list);
	
		//mp3听力
		var list = $("a[id='donw']");
		console.debug(list);		
	}
}