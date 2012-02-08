define([
	"dojo/_base/kernel",
	"dojo/_base/declare",
	"dojo/_base/lang",
	"../_Plugin",
	"../../EnhancedGrid"

], function(kernel, declare,lang, _Plugin, EnhancedGrid){
	
	var ServerPaging = declare("dojox.grid.enhanced.plugins.ServerPaging", _Plugin, {	
		name: "serverPaging",
		limit : 15,
		start : 0,
		total : 0,
		pageDom : null,
		url : null,
		init: function(){
			var g = this.grid.domNode;
			this.url = this.grid.store.url;
			this.pageDom = dojo.doc.createElement("div");
			this.pageDom.innerHTML = '<table class="dojoxGridPaginator"><tr><td class="dojoxGridDescriptionTd"><div><a href="#">|&lt;</a>&nbsp;&nbsp;<a href="#">&lt;</a> &nbsp;&nbsp;<a href="#">&gt;</a>&nbsp;&nbsp;<a href="#">&gt;|</a>&nbsp;&nbsp;</div></td><td>&nbsp;</td><td><div class="desc">(0--15)/100</div></td></tr></table>';
			
			var a = dojo.query("a",this.pageDom);
			
			dojo.connect(a[1],"onclick", null, lang.hitch(this, 'prev'));
			dojo.connect(a[2],"onclick", null,  lang.hitch(this, 'next'));
			dojo.connect(a[0],"onclick", null, lang.hitch(this, 'first'));
			dojo.connect(a[3],"onclick", null,  lang.hitch(this, 'last'));			
			dojo.place(this.pageDom,g,"after");
			
			dojo.connect(this.grid,"_onFetchComplete",null,lang.hitch(this, 'setTotal'));
		},
		setTotal : function(){
			this.total = parseInt(this.grid.store._arrayOfAllItems[0].total[0]);			
			this.limit = parseInt(this.grid.store._arrayOfAllItems[0].pagesize[0]);
			this.start = ( parseInt(this.grid.store._arrayOfAllItems[0].page[0]) -1 )*this.limit;
			
			var dom = dojo.query(".desc",this.pageDom)[0];
			dom.innerHTML = "(&nbsp;"+this.start+"&nbsp;-&nbsp;"+(this.start+this.limit )+"&nbsp;)&nbsp;/&nbsp;"+this.total;

		},
		first : function(){
			this.start = 0;
			this.ajax();
		},
		last : function(){
			this.start = parseInt(this.total/this.limit) * this.limit;
			var remainder = this.total%this.limit;
			if(remainder==0)this.start -= this.limit;
			this.ajax();
		},
		ajax : function(){
			var store = new dojo.data.ItemFileReadStore({url:this.url+"&start="+this.start+"&limit="+this.limit});
			this.grid.setStore(store);
			this.grid.render();
		},
		prev : function(){
			this.start = (this.start==0)?0:this.start-this.limit;
			this.ajax();
		},
		next : function(){
			this.start = ((this.start+this.limit)<=this.total)?(this.start+this.limit):this.start;
			this.ajax();
		},
		destroy: function(){
			console.debug(2);
		}
	});

	EnhancedGrid.registerPlugin(ServerPaging);
	
	return ServerPaging;	

});