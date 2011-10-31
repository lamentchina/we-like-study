package po;

import java.io.Serializable;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Hashtable;

import javax.servlet.http.HttpServletRequest;

import com.google.gson.Gson;

public class Src{

	public String action(String action, HttpServletRequest request) {
		if(action==null){
			return "null function";
		}else if (action.equals("list")) {
			String start = request.getParameter("start") ;
			start = (start==null)?"1":start ;
			String limit = request.getParameter("limit") ;
			limit = (limit==null)?"15":limit ;
			String search = request.getParameter("search") ;
			search = (search==null)?"":search ;			
			Hashtable<String, Serializable> t = list(start,limit,search);
			
			return new Gson().toJson(t);
		} else if (action.equals("ssList")) {
			String row_id = request.getParameter("row_id");
			if (row_id == null)
				return "row_id null";
			Hashtable<String, Serializable> t = ssList(row_id);
			return new Gson().toJson(t);	
		} else {
			return "wrong function";
		}
	}

	public Hashtable<String, Serializable> list(String start, String limit, String search) {
		String total = "0";
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();

		String conditions = " where 1=1 ";
		if (!"".equals(search)) 
			conditions += " and src_name like '%" + search + "%' ";
		ArrayList<Hashtable<String, String>> a = new ArrayList<Hashtable<String, String>>();
		try {
			Connection conn = tools.Db.PoolConn();
			Statement stmt = null;

			String sql = "select count(1) as total from po_src" + conditions;
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			rs.next();
			total = rs.getString("total");

			sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM (SELECT * FROM po_src "
			        + conditions
			        + " ) A WHERE ROWNUM <= "
			        + (limit + start)
			        + " ) WHERE RN >= " + start;

			rs = stmt.executeQuery(sql);

			while (rs.next()) {
				Hashtable<String, String> t2 = new Hashtable<String, String>();
				t2.put("row_id", rs.getString("row_id"));
				t2.put("created_date", rs.getString("created_date"));
				t2.put("created_user", rs.getString("created_user"));
				t2.put("corp_name", rs.getString("corp_name"));
				t2.put("src_no", rs.getString("src_no"));
				t2.put("src_name", rs.getString("src_name"));
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

	private Hashtable<String, Serializable> ssList(String row_id) {		
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();
		ArrayList<Hashtable<String, String>> a = new ArrayList<Hashtable<String, String>>();
		try {
			Connection conn = tools.Db.PoolConn();
			Statement stmt = null;
			String sql = "select * from sp_supplier t2 where t2.row_id in ( select t.sp_id from po_src_sp t where t.src_id = '"
			        + row_id + "' )";
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);

			while (rs.next()) {
				Hashtable<String, String> t2 = new Hashtable<String, String>();
				t2.put("row_id", rs.getString("row_id"));
				t2.put("created_date", rs.getString("created_date"));
				t2.put("ownr_psn", rs.getString("ownr_psn"));
				t2.put("sp_name", rs.getString("sp_name"));
				t2.put("onsp_type", rs.getString("onsp_type"));
				t2.put("reg_capital", rs.getString("reg_capital"));
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
		t.put("total", "1");
		t.put("pagesize", "1");
		return t;
	}
	
	public static void main(String args[]) {

	}
}
