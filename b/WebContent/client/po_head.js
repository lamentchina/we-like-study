var po_head_fields = [ 
 'ROW_ID'
,'PO_NO'
,'IF_GROUP'

,'PO_NO_FLAG'

,'BILL_TYPE'

,'MIS_PO_TYPE'
,'FST_PR_TYPE_ID'
,'FST_PR_TYPE'
,'SND_PR_TYPE_ID'

,'PO_ORIG_AMT'
,'PO_DEC_AMT'
,'PO_AMT'
,'RCPT_AMT'
,'CONT_ID'
,'CONT_CODE'
,'CONT_NAME'
,'CONT_AMT'
,'AGT_ID'
,'AGT_CODE'
,'AGT_NAME'
,'AGT_AMT'
,'PO_DATE'
,'ERP_PERIOD'

,'PO_LEVEL'
,'PO_TYPE'
,'PO_DESC'
,'PO_BASE'
,'PO_DEPT_ID'
,'PO_DEPT_NAME'
,'BUYER_ID'
,'BUYER_NAME'
,'BUYER_MP'
,'BUYER_FAX'
,'BUYER_EMAIL'
,'RCPT_MTL_ORG'
,'RCPT_BILL_ORG'
,'SP_ID'
,'SP_CODE'
,'SP_NAME'
,'SP_ADDR_ID'
,'SP_ADDR'
,'SP_CTC_PSN'

,'SP_MP'
,'SP_FAX'
,'SP_EMAIL'
,'CY_TYPE'

,'CORP_ID'
,'CORP_CODE'
,'CORP_NAME'
,'CREATED_DEPT_ID'
,'CREATED_DEPT_CODE'
,'CREATED_DEPT'
,'CREATED_USER'
,'REMARK'
,'BIZ_STATUS'

,'WF_DEF_ID'
,'WF_INST_ID'
,'DELETED_FLAG'
,'ORIGIN_FLAG'
,'ORIGIN_APP'
,'CREATED_BY'
,'CREATED_DATE'
,'LAST_UPD_BY'
,'LAST_UPD_DATE'
,'MODIFICATION_NUM'

,'SUM_PO_ID'
,'MIS_PO_DATE'
,'MIS_PO_DESC'
,'MIS_IMP_DATE'
,'ACCT_FLAG'
,'AUTO_ADVICE_FLAG'
]; 
var po_head_columns = [
 { header : il8n.general.ROW_ID, dataIndex : 'ROW_ID',hidden:true}
,{ header : il8n.po.head.PO_NO, dataIndex : 'PO_NO'}
,{ header : il8n.po.head.PO_DESC, dataIndex : 'PO_DESC',width:230}
,{ header : il8n.po.head.IF_GROUP, dataIndex : 'IF_GROUP',hidden:true}

,{ header : il8n.po.head.PO_NO_FLAG, dataIndex : 'PO_NO_FLAG',hidden:true}

,{ header : il8n.po.head.BILL_TYPE, dataIndex : 'BILL_TYPE',hidden:true}

,{ header : il8n.po.head.MIS_PO_TYPE, dataIndex : 'MIS_PO_TYPE',hidden:true}
,{ header : il8n.po.head.FST_PR_TYPE_ID, dataIndex : 'FST_PR_TYPE_ID',hidden:true}
,{ header : il8n.po.head.FST_PR_TYPE, dataIndex : 'FST_PR_TYPE',hidden:true}
,{ header : il8n.po.head.SND_PR_TYPE_ID, dataIndex : 'SND_PR_TYPE_ID',hidden:true}

,{ header : il8n.po.head.PO_ORIG_AMT, dataIndex : 'PO_ORIG_AMT',hidden:true}
,{ header : il8n.po.head.PO_DEC_AMT, dataIndex : 'PO_DEC_AMT',hidden:true}
,{ header : il8n.po.head.PO_AMT, dataIndex : 'PO_AMT'}
,{ header : il8n.po.head.RCPT_AMT, dataIndex : 'RCPT_AMT',hidden:true}
,{ header : il8n.po.head.CONT_ID, dataIndex : 'CONT_ID',hidden:true}
,{ header : il8n.po.head.CONT_CODE, dataIndex : 'CONT_CODE'}
,{ header : il8n.po.head.CONT_NAME, dataIndex : 'CONT_NAME',hidden:true}
,{ header : il8n.po.head.CONT_AMT, dataIndex : 'CONT_AMT',hidden:true}
,{ header : il8n.po.head.AGT_ID, dataIndex : 'AGT_ID',hidden:true}
,{ header : il8n.po.head.AGT_CODE, dataIndex : 'AGT_CODE',hidden:true}
,{ header : il8n.po.head.AGT_NAME, dataIndex : 'AGT_NAME',hidden:true}
,{ header : il8n.po.head.AGT_AMT, dataIndex : 'AGT_AMT',hidden:true}
,{ header : il8n.po.head.PO_DATE, dataIndex : 'PO_DATE',hidden:true}
,{ header : il8n.po.head.ERP_PERIOD, dataIndex : 'ERP_PERIOD',hidden:true}

,{ header : il8n.po.head.PO_LEVEL, dataIndex : 'PO_LEVEL',hidden:true}
,{ header : il8n.po.head.PO_TYPE, dataIndex : 'PO_TYPE',hidden:true}

,{ header : il8n.po.head.PO_BASE, dataIndex : 'PO_BASE',hidden:true}
,{ header : il8n.po.head.PO_DEPT_ID, dataIndex : 'PO_DEPT_ID',hidden:true}
,{ header : il8n.po.head.PO_DEPT_NAME, dataIndex : 'PO_DEPT_NAME',hidden:true}
,{ header : il8n.po.head.BUYER_ID, dataIndex : 'BUYER_ID',hidden:true}
,{ header : il8n.po.head.BUYER_NAME, dataIndex : 'BUYER_NAME',width:70}
,{ header : il8n.po.head.BUYER_MP, dataIndex : 'BUYER_MP',hidden:true}
,{ header : il8n.po.head.BUYER_FAX, dataIndex : 'BUYER_FAX',hidden:true}
,{ header : il8n.po.head.BUYER_EMAIL, dataIndex : 'BUYER_EMAIL',hidden:true}
,{ header : il8n.po.head.RCPT_MTL_ORG, dataIndex : 'RCPT_MTL_ORG',hidden:true}
,{ header : il8n.po.head.RCPT_BILL_ORG, dataIndex : 'RCPT_BILL_ORG',hidden:true}
,{ header : il8n.po.head.SP_ID, dataIndex : 'SP_ID',hidden:true}
,{ header : il8n.po.head.SP_CODE, dataIndex : 'SP_CODE',hidden:true}
,{ header : il8n.po.head.SP_NAME, dataIndex : 'SP_NAME'}
,{ header : il8n.po.head.SP_ADDR_ID, dataIndex : 'SP_ADDR_ID',hidden:true}
,{ header : il8n.po.head.SP_ADDR, dataIndex : 'SP_ADDR',hidden:true}
,{ header : il8n.po.head.SP_CTC_PSN, dataIndex : 'SP_CTC_PSN',hidden:true}

,{ header : il8n.po.head.SP_MP, dataIndex : 'SP_MP',hidden:true}
,{ header : il8n.po.head.SP_FAX, dataIndex : 'SP_FAX',hidden:true}
,{ header : il8n.po.head.SP_EMAIL, dataIndex : 'SP_EMAIL',hidden:true}
,{ header : il8n.po.head.CY_TYPE, dataIndex : 'CY_TYPE',hidden:true}

,{ header : il8n.po.head.CORP_ID, dataIndex : 'CORP_ID',hidden:true}
,{ header : il8n.po.head.CORP_CODE, dataIndex : 'CORP_CODE',hidden:true}
,{ header : il8n.po.head.CORP_NAME, dataIndex : 'CORP_NAME'}
,{ header : il8n.po.head.CREATED_DEPT_ID, dataIndex : 'CREATED_DEPT_ID',hidden:true}
,{ header : il8n.po.head.CREATED_DEPT_CODE, dataIndex : 'CREATED_DEPT_CODE',hidden:true}
,{ header : il8n.po.head.CREATED_DEPT, dataIndex : 'CREATED_DEPT',hidden:true}
,{ header : il8n.po.head.CREATED_USER, dataIndex : 'CREATED_USER',hidden:true}
,{ header : il8n.po.head.REMARK, dataIndex : 'REMARK',hidden:true}
,{ header : il8n.po.head.BIZ_STATUS, dataIndex : 'BIZ_STATUS', renderer : 
	function(v, meta, record, row_idx, col_idx, store){
		eval("var str = il8n.po.head."+record.data.BIZ_STATUS);
		if(record.data.BIZ_STATUS!='Y'){
			str = '<span style="color:red">'+str+'</span>';
		}		
		return str;
	}
}
,{ header : il8n.po.head.WF_DEF_ID, dataIndex : 'WF_DEF_ID',hidden:true}
,{ header : il8n.po.head.WF_INST_ID, dataIndex : 'WF_INST_ID',hidden:true}
,{ header : il8n.po.head.DELETED_FLAG, dataIndex : 'DELETED_FLAG',hidden:true}
,{ header : il8n.po.head.ORIGIN_FLAG, dataIndex : 'ORIGIN_FLAG',hidden:true}
,{ header : il8n.po.head.ORIGIN_APP, dataIndex : 'ORIGIN_APP',hidden:true}
,{ header : il8n.po.head.CREATED_BY, dataIndex : 'CREATED_BY',hidden:true}
,{ header : il8n.po.head.CREATED_DATE, dataIndex : 'CREATED_DATE',hidden:true}
,{ header : il8n.po.head.LAST_UPD_BY, dataIndex : 'LAST_UPD_BY',hidden:true}
,{ header : il8n.po.head.LAST_UPD_DATE, dataIndex : 'LAST_UPD_DATE',hidden:true}
,{ header : il8n.po.head.MODIFICATION_NUM, dataIndex : 'MODIFICATION_NUM',hidden:true}

,{ header : il8n.po.head.SUM_PO_ID, dataIndex : 'SUM_PO_ID',hidden:true}
,{ header : il8n.po.head.MIS_PO_DATE, dataIndex : 'MIS_PO_DATE',hidden:true}
,{ header : il8n.po.head.MIS_PO_DESC, dataIndex : 'MIS_PO_DESC',hidden:true}
,{ header : il8n.po.head.MIS_IMP_DATE, dataIndex : 'MIS_IMP_DATE',hidden:true}
,{ header : il8n.po.head.ACCT_FLAG, dataIndex : 'ACCT_FLAG',hidden:true}
,{ header : il8n.po.head.AUTO_ADVICE_FLAG, dataIndex : 'AUTO_ADVICE_FLAG',hidden:true}                       
];

po.head = {
	list : function(){
		var store = new Ext.data.JsonStore( {
				autoDestroy : true,
				proxy : new Ext.data.HttpProxy( {
					url : url.po_head_list,
					method : 'GET'
				}),
				root : 'data',
				idProperty : 'ROW_ID',
				fields : po_head_fields
			});	
			
			var cm = new Ext.grid.ColumnModel( {
				defaults : {
					sortable : true
				},
				columns : po_head_columns
			});
		
			var search = new Ext.form.TextField({
				id : 'p_h_l_search',
				width : 170,
				enableKeyEvents : true
			});		
		
			search.on('keyup', function(a, b, c) {
				if (b.button == 12) {
					store.load({
						params : {
							start : 0,
							limit : 20,
							search : Ext.getCmp('p_h_l_search').getValue()
						}
					});
				}
			});
			
			var tb = new Ext.Toolbar({
				items : [ search, {
					text : il8n.general.search,
					handler : function() {
						store.load({
									params : {
										start : 0,
										limit : 20
									}
								});
					}
				},'-',{
					 text : il8n.general.detail
					,handler : function() {
						if (Ext.getCmp('p_h_list').getSelectionModel().selections.items.length == 0) {
							alert(il8n.error.rowSelectedFirst);
							return;
						}
						var row_id = Ext.getCmp('p_h_list').getSelectionModel().selections.items[0].data.ROW_ID;
						var tabPanel = po.head.detail(row_id);

						var w = new Ext.Window({
							title : il8n.general.detail,
							id : 'p_h_l_win_detail_'+row_id,
							width : '80%',
							height : 450,
							layout : 'fit',
							items : [tabPanel]
						});
		
						w.show();
					}
				}]
			});
		
			store.on('beforeload', function() {
				Ext.apply(this.baseParams, {
					search : Ext.getCmp('p_h_l_search').getValue()
				});
			});
		
			var grid = new Ext.grid.GridPanel( {
				id : 'p_h_list',
				store : store,
				cm : cm,
				height : 350,
				width : '95%',
				tbar : tb,
				loadMask : {  
					msg : il8n.general.loading
				} ,
				bbar : new Ext.PagingToolbar( {
					store : store,
					pageSize : 15,
					displayInfo : true
				})				   
			});
			grid.on('celldblclick',function(grid,row,col,rec){
				var ROW_ID = grid.store.getAt(row).get("ROW_ID");
				var tabPanel = po.head.detail(ROW_ID);
				var w = new Ext.Window({
					title : il8n.general.detail,
					id : 'p_h_l_win_detail_'+ROW_ID,
					width : '80%',
					height : 450,
					layout : 'fit',
					items : [tabPanel]
				});
				w.show();
			});
			store.load();
			return grid;
	},
	edit : function(){
		
	},
	detail : function(po_id){		
		var lineList = po.line.list(po_id);
		lineList.title = il8n.po.line.list;
		var tree = po.head.getWorkFlow(po_id);
		tree.title = il8n.workFlow.gantt;
		var tabPanel = new Ext.TabPanel({   
			id: 'p_h_detail'+po_id,   
			width: 500,   
			height: 300,   
			activeTab: 0,   
			defaults: {   
				autoScroll: true,   
				autoHeight:true,   
				style: 'padding:5'
			},   
			items:[   
				lineList,tree
			],   
			enableTabScroll: true  
		});    
		return tabPanel;
	},
	getPermissions : function(toolBar){
		
	},
	getWorkFlow : function(id){
		var tree = new Ext.ux.tree.TreeGrid({
			defaultSortable: false,
			enableSort: false, 
			columns:[
			         { header: il8n.workFlow.task, dataIndex: 'task', width:200 },
			         { header: il8n.workFlow.invoice, dataIndex: 'trade', width:120 },
			         { header: il8n.workFlow.chargeMan, dataIndex: 'chargeMan' ,width:60 },
			         { header: il8n.general.start, dataIndex: 'start' ,width:60 },
			         { header: il8n.general.end, dataIndex: 'end', width:60 },
			         { header: il8n.workFlow.duration, dataIndex: 'duration', width:40 },
			         { header: il8n.workFlow.gantt, dataIndex: 'gantt', width:400
						,tpl: new Ext.XTemplate('{gantt:this.format}', {
							format: function(v) {
								eval("var arr = ["+v+"]" );
								var str = "";//TODO
								str += "<span style='color:white'>";
								for(var i=0;i<arr[0];i++){
									str += "|";
								}
								str += "</span>";
								str += "<span style='color:blue'>";
								for(var i=0;i<arr[1];i++){
									str += "|";
								}
								str += "</span>";	
								str += "<span style='color:red'>";
								for(var i=0;i<arr[2];i++){
									str += "|";
								}
								str += "</span>";								
								return str;
						    }
						})
			         },
			         { header: il8n.general.operate, dataIndex: 'id' , width:50,align: 'center'
							,tpl: new Ext.XTemplate('{id:this.format}', {
								format: function(v) {									
									return "<a onclick=\"alert('"+v+"')\">"+il8n.general.view+"</a>";
							    }
							})
			         }
			],
			dataUrl: url.po_head_workflow+'&id='+id
		});
		return tree;
	}
}