po.line = {
	list : function(id){
		var store = new Ext.data.JsonStore( {
				autoDestroy : true,
				proxy : new Ext.data.HttpProxy( {
					url : url.po_line_list+"&id="+id,
					method : 'GET'
				}),
				root : 'data',
				idProperty : 'ROW_ID',
				fields : [ 
					'ROW_ID',
					'PO_ID',
					'ROW_NUM',
					'BILL_TYPE',
					
					'PR_ID',
					'PR_NO',
					'PR_LINE_ID',
					'FST_PR_TYPE_ID',
					'FST_PR_TYPE',
					'SND_PR_TYPE_ID',
					'SND_PR_TYPE',
					
					'BUDG_ID',
					'BUDG_CODE',
					'BUDG_NAME',
					'PROJ_ID',
					'PROJ_CODE',
					'PROJ_NAME',
					'TASK_ID',
					'TASK_CODE',
					'TASK_NAME',
					'MTL_ID',
					'MTL_CODE',
					'MTL_NAME',
					'MTL_CAT_NAME',
					
					'MTL_DESC',
					'MTL_SKU',
					'PO_QTY',
					'PO_PRICE',
					'PO_AMT',
					'RCPT_DATE',
					'RCPT_ORG_ID',
					'RCPT_ORG_NAME',
					'RCPT_ADDR_ID',
					'RCPT_ADDR_TYPE',
					'RCPT_ADDR',
					'RCPT_PSN_NAME',
					'RCPT_PSN_MP',
					'RCPT_PSN_FAX',
					'RCPT_PSN_EMAIL',
					'ADJ_RCPT_DATE',
					'ADJ_RCPT_ORG_ID',
					'ADJ_RCPT_ORG_NAME',
					'ADJ_RCPT_ADDR_ID',
					'ADJ_RCPT_ADDR_TYPE',
					'ADJ_RCPT_ADDR',
					'ADJ_RCPT_PSN_NAME',
					'ADJ_RCPT_MOB_PHONE',
					'ADJ_RCPT_EMAIL',
					'ACT_RCPT_QTY',
					'ACT_RTN_QTY',
					'RCPT_EXEC_QTY',
					'RTN_EXEC_QTY',
					'CREATED_ORG_ID',
					'CREATED_ORG_CODE',
					'CREATED_ORG_NAME',
					'CREATED_USER',
					'MIS_ACPT_FLAG',
					
					'MIS_ERP_DATE',
					'MIS_SITE_NO',
					'MIS_WHS_UNIT_ID',
					'MIS_WHS_UNIT',
					'MIS_RCPT_ADDR',
					'MIS_TYPE',
					'MIS_EXP_TYPE',
					'MIS_EXP_ORG_ID',
					'MIS_EXP_ORG',
					'MIS_EXP_DATE',
					'MIS_COST_CENTRE',
					'DISTR_UNIT_ID',
					'DISTR_UNIT_CODE',
					'DISTR_UNIT',
					'DISTR_METHOD',
					'BIZ_STATUS',
					'REMARK',
					'ORIGIN_FLAG',
					'DELETED_FLAG',
					'ORIGIN_APP',
					'CREATED_BY',
					'CREATED_DATE',
					'LAST_UPD_BY',
					'LAST_UPD_DATE',
					'MODIFICATION_NUM',
					
					'TXT4',
					'TXT5',
					
					'ADJ_RCPT_PSN_FAX',
					'SUM_PO_LINE_ID',
					
					'ADV_USER_ID',
					'ADV_USER_NAME'
				]
			});	
			
			var cm = new Ext.grid.ColumnModel( {
				defaults : {
					sortable : true
				},
				columns : [ 
 { header : il8n.po.line.ROW_ID, dataIndex : 'ROW_ID',hidden:true }
,{ header : il8n.po.line.PO_ID, dataIndex : 'PO_ID',hidden:true }
,{ header : il8n.po.line.ROW_NUM, dataIndex : 'ROW_NUM',hidden:true }
,{ header : il8n.po.line.BILL_TYPE, dataIndex : 'BILL_TYPE',hidden:true }

,{ header : il8n.po.line.PR_ID, dataIndex : 'PR_ID',hidden:true }
,{ header : il8n.po.line.PR_NO, dataIndex : 'PR_NO',hidden:true }
,{ header : il8n.po.line.PR_LINE_ID, dataIndex : 'PR_LINE_ID',hidden:true }
,{ header : il8n.po.line.FST_PR_TYPE_ID, dataIndex : 'FST_PR_TYPE_ID',hidden:true }
,{ header : il8n.po.line.FST_PR_TYPE, dataIndex : 'FST_PR_TYPE',hidden:true }
,{ header : il8n.po.line.SND_PR_TYPE_ID, dataIndex : 'SND_PR_TYPE_ID',hidden:true }
,{ header : il8n.po.line.SND_PR_TYPE, dataIndex : 'SND_PR_TYPE',hidden:true }

,{ header : il8n.po.line.BUDG_ID, dataIndex : 'BUDG_ID',hidden:true }
,{ header : il8n.po.line.BUDG_CODE, dataIndex : 'BUDG_CODE',hidden:true  }
,{ header : il8n.po.line.BUDG_NAME, dataIndex : 'BUDG_NAME',hidden:true }
,{ header : il8n.po.line.PROJ_ID, dataIndex : 'PROJ_ID',hidden:true }
,{ header : il8n.po.line.PROJ_CODE, dataIndex : 'PROJ_CODE' }
,{ header : il8n.po.line.PROJ_NAME, dataIndex : 'PROJ_NAME',hidden:true }
,{ header : il8n.po.line.TASK_ID, dataIndex : 'TASK_ID',hidden:true }
,{ header : il8n.po.line.TASK_CODE, dataIndex : 'TASK_CODE',width:80 }
,{ header : il8n.po.line.TASK_NAME, dataIndex : 'TASK_NAME',hidden:true }
,{ header : il8n.po.line.MTL_ID, dataIndex : 'MTL_ID',hidden:true }
,{ header : il8n.po.line.MTL_CODE, dataIndex : 'MTL_CODE',hidden:true }
,{ header : il8n.po.line.MTL_NAME, dataIndex : 'MTL_NAME',width:230}
,{ header : il8n.po.line.MTL_CAT_NAME, dataIndex : 'MTL_CAT_NAME',hidden:true }

,{ header : il8n.po.line.MTL_DESC, dataIndex : 'MTL_DESC',hidden:true }
,{ header : il8n.po.line.MTL_SKU, dataIndex : 'MTL_SKU',hidden:true }
,{ header : il8n.po.line.PO_QTY, dataIndex : 'PO_QTY',width:70 }
,{ header : il8n.po.line.PO_PRICE, dataIndex : 'PO_PRICE',width:70 }
,{ header : il8n.po.line.PO_AMT, dataIndex : 'PO_AMT',width:70 }
,{ header : il8n.po.line.RCPT_DATE, dataIndex : 'RCPT_DATE',hidden:true }
,{ header : il8n.po.line.RCPT_ORG_ID, dataIndex : 'RCPT_ORG_ID',hidden:true }
,{ header : il8n.po.line.RCPT_ORG_NAME, dataIndex : 'RCPT_ORG_NAME',hidden:true }
,{ header : il8n.po.line.RCPT_ADDR_ID, dataIndex : 'RCPT_ADDR_ID',hidden:true }
,{ header : il8n.po.line.RCPT_ADDR_TYPE, dataIndex : 'RCPT_ADDR_TYPE',hidden:true }
,{ header : il8n.po.line.RCPT_ADDR, dataIndex : 'RCPT_ADDR',hidden:true }
,{ header : il8n.po.line.RCPT_PSN_NAME, dataIndex : 'RCPT_PSN_NAME',hidden:true }
,{ header : il8n.po.line.RCPT_PSN_MP, dataIndex : 'RCPT_PSN_MP',hidden:true }
,{ header : il8n.po.line.RCPT_PSN_FAX, dataIndex : 'RCPT_PSN_FAX',hidden:true }
,{ header : il8n.po.line.RCPT_PSN_EMAIL, dataIndex : 'RCPT_PSN_EMAIL',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_DATE, dataIndex : 'ADJ_RCPT_DATE',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_ORG_ID, dataIndex : 'ADJ_RCPT_ORG_ID',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_ORG_NAME, dataIndex : 'ADJ_RCPT_ORG_NAME',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_ADDR_ID, dataIndex : 'ADJ_RCPT_ADDR_ID',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_ADDR_TYPE, dataIndex : 'ADJ_RCPT_ADDR_TYPE',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_ADDR, dataIndex : 'ADJ_RCPT_ADDR',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_PSN_NAME, dataIndex : 'ADJ_RCPT_PSN_NAME',hidden:true  }
,{ header : il8n.po.line.ADJ_RCPT_MOB_PHONE, dataIndex : 'ADJ_RCPT_MOB_PHONE',hidden:true }
,{ header : il8n.po.line.ADJ_RCPT_EMAIL, dataIndex : 'ADJ_RCPT_EMAIL',hidden:true }
,{ header : il8n.po.line.ACT_RCPT_QTY, dataIndex : 'ACT_RCPT_QTY',width:80 }
,{ header : il8n.po.line.ACT_RTN_QTY, dataIndex : 'ACT_RTN_QTY',hidden:true }
,{ header : il8n.po.line.RCPT_EXEC_QTY, dataIndex : 'RCPT_EXEC_QTY',hidden:true }
,{ header : il8n.po.line.RTN_EXEC_QTY, dataIndex : 'RTN_EXEC_QTY',hidden:true }
,{ header : il8n.po.line.CREATED_ORG_ID, dataIndex : 'CREATED_ORG_ID',hidden:true }
,{ header : il8n.po.line.CREATED_ORG_CODE, dataIndex : 'CREATED_ORG_CODE',hidden:true }
,{ header : il8n.po.line.CREATED_ORG_NAME, dataIndex : 'CREATED_ORG_NAME',hidden:true }
,{ header : il8n.po.line.CREATED_USER, dataIndex : 'CREATED_USER',hidden:true }
,{ header : il8n.po.line.MIS_ACPT_FLAG, dataIndex : 'MIS_ACPT_FLAG',hidden:true }

,{ header : il8n.po.line.MIS_ERP_DATE, dataIndex : 'MIS_ERP_DATE',hidden:true }
,{ header : il8n.po.line.MIS_SITE_NO, dataIndex : 'MIS_SITE_NO',hidden:true }
,{ header : il8n.po.line.MIS_WHS_UNIT_ID, dataIndex : 'MIS_WHS_UNIT_ID',hidden:true }
,{ header : il8n.po.line.MIS_WHS_UNIT, dataIndex : 'MIS_WHS_UNIT',hidden:true }
,{ header : il8n.po.line.MIS_RCPT_ADDR, dataIndex : 'MIS_RCPT_ADDR',hidden:true }
,{ header : il8n.po.line.MIS_TYPE, dataIndex : 'MIS_TYPE',hidden:true }
,{ header : il8n.po.line.MIS_EXP_TYPE, dataIndex : 'MIS_EXP_TYPE',hidden:true }
,{ header : il8n.po.line.MIS_EXP_ORG_ID, dataIndex : 'MIS_EXP_ORG_ID',hidden:true }
,{ header : il8n.po.line.MIS_EXP_ORG, dataIndex : 'MIS_EXP_ORG',hidden:true }
,{ header : il8n.po.line.MIS_EXP_DATE, dataIndex : 'MIS_EXP_DATE',hidden:true }
,{ header : il8n.po.line.MIS_COST_CENTRE, dataIndex : 'MIS_COST_CENTRE',hidden:true }
,{ header : il8n.po.line.DISTR_UNIT_ID, dataIndex : 'DISTR_UNIT_ID',hidden:true }
,{ header : il8n.po.line.DISTR_UNIT_CODE, dataIndex : 'DISTR_UNIT_CODE',hidden:true }
,{ header : il8n.po.line.DISTR_UNIT, dataIndex : 'DISTR_UNIT',hidden:true }
,{ header : il8n.po.line.DISTR_METHOD, dataIndex : 'DISTR_METHOD',hidden:true }
,{ header : il8n.po.line.BIZ_STATUS, dataIndex : 'BIZ_STATUS',hidden:true }
,{ header : il8n.po.line.REMARK, dataIndex : 'REMARK',hidden:true }
,{ header : il8n.po.line.ORIGIN_FLAG, dataIndex : 'ORIGIN_FLAG',hidden:true }
,{ header : il8n.po.line.DELETED_FLAG, dataIndex : 'DELETED_FLAG',hidden:true }
,{ header : il8n.po.line.ORIGIN_APP, dataIndex : 'ORIGIN_APP',hidden:true }
,{ header : il8n.po.line.CREATED_BY, dataIndex : 'CREATED_BY',hidden:true }
,{ header : il8n.po.line.CREATED_DATE, dataIndex : 'CREATED_DATE',hidden:true }
,{ header : il8n.po.line.LAST_UPD_BY, dataIndex : 'LAST_UPD_BY',hidden:true }
,{ header : il8n.po.line.LAST_UPD_DATE, dataIndex : 'LAST_UPD_DATE',hidden:true }
,{ header : il8n.po.line.MODIFICATION_NUM, dataIndex : 'MODIFICATION_NUM',hidden:true }

,{ header : il8n.po.line.TXT4, dataIndex : 'TXT4',hidden:true }
,{ header : il8n.po.line.TXT5, dataIndex : 'TXT5',hidden:true }

,{ header : il8n.po.line.ADJ_RCPT_PSN_FAX, dataIndex : 'ADJ_RCPT_PSN_FAX',hidden:true }
,{ header : il8n.po.line.SUM_PO_LINE_ID, dataIndex : 'SUM_PO_LINE_ID',hidden:true }

,{ header : il8n.po.line.ADV_USER_ID, dataIndex : 'ADV_USER_ID',hidden:true }
,{ header : il8n.po.line.ADV_USER_NAME, dataIndex : 'ADV_USER_NAME' }

				 ]
			});
		
			var grid = new Ext.grid.GridPanel( {
				id : 'p_L_list',
				store : store,
				cm : cm,
				height : 350,
				width : '95%'
			});
		
			store.load();
			return grid;
	}
}