var sm = {
	session:{
		sid:''
	},
	login:function(){
		var panel = new Ext.Panel({
			layout:"fit",
			items:[{
				layout:"absolute",
				items:[
					new Ext.form.Label({
						x:"1%",
						y:5,
						text: il8n.username   
					}),
					new Ext.form.TextField({
						x:'10%',
						y:5,
						id : 'sm_login_username'
					}),					
					new Ext.form.Label({
						x:'25%',
						y:5,
						text: il8n.password   
					}),		
					new Ext.form.TextField({
						x:'35%',
						y:5,
						id : 'sm_login_password'
					})
				]
			}]
		});
		return panel;
	}
}