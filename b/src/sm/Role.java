package sm;

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

import tools.Db;

import com.google.gson.Gson;

public class Role {
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
		}else if(action.equals("permissionsList")) {
			String row_id = request.getParameter("id");
			if (row_id == null)
				return "row_id null";
			Hashtable<String, Serializable> t = permissionsList(row_id);
			String s = new Gson().toJson(tools.Util.getTreeData("0", (ArrayList)t.get("data")));
			s = s.replace("\"true\"", "true");
			s = s.replace("\"false\"", "false");
			return s;
		}else if(action.equals("permissionsList4edit")) {
			String row_id = request.getParameter("id");
			if (row_id == null)
				return "row_id null";
			Hashtable<String, Serializable> t = permissionsList4edit(row_id);
			String s = new Gson().toJson(tools.Util.getTreeData("0", (ArrayList)t.get("data")));
			s = s.replace("\"true\"", "true");
			s = s.replace("\"false\"", "false");
			return s;
		} else {
			return "wrong function";
		}
	}	
	
	private Hashtable<String, Serializable> list(String start, String limit, String search) {
		String total = "0";
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();

		String conditions = " where 1=1 ";
		if (!"".equals(search)) 
			conditions += " and role_name like '%" + search + "%' ";
		ArrayList<HashMap<String, String>> a = new ArrayList<HashMap<String, String>>();
		try {
			Connection conn = tools.Db.PoolConn();
			Statement stmt = null;

			String sql = "select count(1) as total from sm_role " + conditions;
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			rs.next();
			total = rs.getString("total");

			sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM (select row_id id,role_code code,role_name name,remark from sm_role sr "
			        + conditions
			        + " ) A WHERE ROWNUM <= "
			        + (Integer.parseInt(limit) + Integer.parseInt(start))
			        + " ) WHERE RN >= " + start;
			rs = stmt.executeQuery(sql);

			while (rs.next()) {
				HashMap<String, String> t2 = new HashMap<String, String>();
				ResultSetMetaData rsmd = rs.getMetaData();
				int count = rsmd.getColumnCount();
				for(int i=1;i<=count;i++){					
					if(rs.getString(i)==null){
						t2.put(rsmd.getColumnName(i).toLowerCase(), "");
					}else{
						t2.put(rsmd.getColumnName(i).toLowerCase(), rs.getString(i));
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
	
	private Hashtable<String, Serializable> permissionsList(String row_id) {
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();
		t.put("page", "1");
		t.put("total", "1");
		t.put("pagesize", "1");

		ArrayList<Hashtable<String, String>> a = new ArrayList<Hashtable<String, String>>();
		try {
			Statement stmt = null;
			String sql = "select ap.program_code id,ap.program_name name from ad_program ap where ap.row_id in( "
						+"select smr.program_id from sm_role_program smr where smr.role_id = '"+row_id+"' )";

			Connection conn = Db.PoolConn();
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			while (rs.next()) {
				Hashtable<String, String> t2 = new Hashtable<String, String>();
				ResultSetMetaData rsmd = rs.getMetaData();
				int count = rsmd.getColumnCount();
				for(int i=1;i<=count;i++){					
					if(rs.getString(i)==null){
						t2.put(rsmd.getColumnName(i).toLowerCase(), "");
					}else{
						t2.put(rsmd.getColumnName(i).toLowerCase(), rs.getString(i));
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
		return t;
	}
	
	private Hashtable<String, Serializable> permissionsList4edit(String row_id) {
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();
		t.put("page", "1");
		t.put("total", "1");
		t.put("pagesize", "1");

		ArrayList<Hashtable<String, String>> a = new ArrayList<Hashtable<String, String>>();
		try {
			Statement stmt = null;
			String sql = " select ap.program_code id,ap.program_name text ,'true' checked from ad_program ap where ap.row_id in("
				+" select smr.program_id from sm_role_program smr where smr.role_id = '"+row_id+"')"
				+" union"
				+" select ap.program_code id,ap.program_name text ,'false' checked from ad_program ap where ap.row_id not in("
				+" select smr.program_id from sm_role_program smr where smr.role_id = '"+row_id+"')"
				+" order by id ";
			System.out.print(sql);
			Connection conn = Db.PoolConn();
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			while (rs.next()) {
				Hashtable<String, String> t2 = new Hashtable<String, String>();
				ResultSetMetaData rsmd = rs.getMetaData();
				int count = rsmd.getColumnCount();
				for(int i=1;i<=count;i++){					
					if(rs.getString(i)==null){
						t2.put(rsmd.getColumnName(i).toLowerCase(), "");
					}else{
						t2.put(rsmd.getColumnName(i).toLowerCase(), rs.getString(i));
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
		return t;
	}
}
