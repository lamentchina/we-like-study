package po;

import java.io.Serializable;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Hashtable;

import javax.servlet.http.HttpServletRequest;

import com.google.gson.Gson;

public class Head {
	
	public String action(String action, HttpServletRequest request) {
		if(action==null){
			return "null function";
		}else if(action.equals("list")) {
			String start = request.getParameter("start") ;
			start = (start==null)?"1":start ;
			String limit = request.getParameter("limit") ;
			limit = (limit==null)?"15":limit ;
			String search = request.getParameter("search") ;
			search = (search==null)?"":search ;			
			Hashtable<String, Serializable> t = list(start,limit,search);
			
			return new Gson().toJson(t);
		}else if(action.equals("getWorkFlow")){
			String id = request.getParameter("id") ;
			if(id==null)return null;
			String json = new Gson().toJson(tools.Util.getTreeData("0", getWorkFlow(id)));
			json = json.replace("\"true\"", "true");
			return json;
		} else {
			return "wrong function";
		}
	}	
	
	private Hashtable<String, Serializable> list(String start, String limit, String search) {
		String total = "0";
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();

		String conditions = " where 1=1 ";
		if (!"".equals(search)) 
			conditions += " and po_no like '%" + search + "%' ";
		ArrayList<HashMap<String, String>> a = new ArrayList<HashMap<String, String>>();
		try {
			Connection conn = tools.Db.PoolConn();
			Statement stmt = null;

			String sql = "select count(1) as total from po_head " + conditions;
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			rs.next();
			total = rs.getString("total");

			sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM (" 
					+"select * from po_head "
			        + conditions
			        + " ) A WHERE ROWNUM <= "
			        + (Integer.parseInt(limit) + Integer.parseInt(start))
			        + " ) WHERE RN >= " + start;
			System.out.println(sql);
			rs = stmt.executeQuery(sql);

			while (rs.next()) {
				HashMap<String, String> t2 = new HashMap<String, String>();
				ResultSetMetaData rsmd = rs.getMetaData();
				int count = rsmd.getColumnCount();
				for(int i=1;i<=count;i++){					
					if(rs.getString(i)==null){
						t2.put(rsmd.getColumnName(i), "");
					}else{
						t2.put(rsmd.getColumnName(i), rs.getString(i));
					}
				}
				a.add(t2);			
			}
			rs.close();
			stmt.close();
			conn.close();

		} catch (SQLException e) {
			e.printStackTrace();
		}

		t.put("data", a);
		t.put("page", "1");
		t.put("total", total);
		t.put("pagesize", limit);
		return t;
	}
	
	/**
	 * 得到一张订单的业务流程图
	 * 相关的内容有: 订单审核,生成收货通知单,生成入库单,生成现场接收单,账面确认
	 * TODO 这个部分应该用存储过程实现,不然性能不行
	 * */
	public ArrayList getWorkFlow(String row_id) {
		ArrayList a = new ArrayList();
		try {
			Connection conn = tools.Db.PoolConn();
			Statement stmt = null;

			String sql = "select ph.* from po_head ph where ph.row_id = '"+row_id+"'";
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			rs.next();
			//if(rs.getString("BIZ_STATUS"))
			Hashtable t = new Hashtable();
			t.put("id", "10");
			t.put("task", "订单");
			t.put("trade", rs.getString("PO_NO"));
			t.put("chargeMan", rs.getString("CREATED_USER"));
			t.put("start", rs.getString("CREATED_DATE").substring(0,10));
			t.put("end", rs.getString("LAST_UPD_DATE").substring(0,10));
			t.put("duration", "10");
			t.put("gantt", "10,25,100");
			t.put("state", "OK");
			a.add(t);
			
			sql = "select * from sm_flow_history sfh where sfh.doc_id = '"+row_id+"' order by row_id";
			rs = stmt.executeQuery(sql);
			rs.next();
			rs.next();
			t = new Hashtable();
			t.put("id", "1001");
			t.put("task", "订单审批");
			t.put("trade", "X");
			t.put("chargeMan", rs.getString("AUDIT_USER_NAME"));
			t.put("start", rs.getString("CREATED_DATE").substring(0,10));
			t.put("end", rs.getString("AUDIT_DATE").substring(0,10));
			t.put("duration", "10");
			t.put("gantt", "10,25,100");
			t.put("state", "OK");
			a.add(t);	
			
			sql = "select sah.* from sl_advice_head sah where sah.po_head_id = '"+row_id+"' ";
			rs = stmt.executeQuery(sql);
			int i=50;
			int j=0;
			while(rs.next()){
				j++;
				t = new Hashtable();
				t.put("id",  String.valueOf(1000+i+j) );
				t.put("task", "收货通知单");
				t.put("trade", rs.getString("ADV_NO"));
				t.put("chargeMan", rs.getString("CREATED_USER"));
				t.put("start", rs.getString("CREATED_DATE").substring(0,10));
				t.put("end", rs.getString("LAST_UPD_DATE").substring(0,10));
				t.put("duration", "10");
				t.put("gantt", "10,25,100");
				t.put("state", "OK");
				a.add(t);
				
				String sah_rowid = rs.getString("ROW_ID");
				
				String sql2 = "select * from sl_trade_head sth where sth.ref_head_id = '"+sah_rowid+"' ";
				System.out.println(sql2);
				ResultSet rs2 = stmt.executeQuery(sql2);
				int j2 = 0;
				while(rs2.next()){
					t = new Hashtable();
					j2++;
					t.put("id", String.valueOf((1000+i+j)*100+j2) );
					t.put("task", "入库单");
					t.put("trade", rs2.getString("TRADE_NO"));
					t.put("chargeMan", rs2.getString("CREATED_USER"));
					t.put("start", rs2.getString("CREATED_DATE").substring(0,10));
					t.put("end", rs2.getString("LAST_UPD_DATE").substring(0,10));
					t.put("duration", "10");
					t.put("gantt", "10,25,100");
					t.put("state", "OK");
					a.add(t);
				}
			}
		}catch (Exception e) {
			
		}
		
		return a;
	}
}

class po_head_workflow{
	public static void main(String args[]){
		Head obj = new Head();
		ArrayList a = obj.getWorkFlow("300000029066468");
		
		System.out.println( new Gson().toJson(tools.Util.getTreeData("0", a)) );
	}
}