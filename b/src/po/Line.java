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

public class Line {
	public String action(String action, HttpServletRequest request) {
		if(action==null){
			return "null function";
		}else if(action.equals("list")) {
			String row_id = request.getParameter("id");
			if (row_id == null)
				return "id null";
			Hashtable<String, Serializable> t = list(row_id);
			return new Gson().toJson(t);
		} else {
			return "wrong function";
		}
	}	
	
	private Hashtable<String, Serializable> list(String id) {
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();
		ArrayList<Hashtable<String, String>> a = new ArrayList<Hashtable<String, String>>();
		try {
			Connection conn = tools.Db.PoolConn();
			Statement stmt = null;
			String sql = " select * from po_line where po_id="+id;
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			
			while (rs.next()){					
				Hashtable<String, String> t2 = new Hashtable<String, String>();
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
			};
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
	
	public static void main(String args[]){
		Line obj = new Line();
		Hashtable<String, Serializable> t = obj.list("300000001172484");
		System.out.println(new Gson().toJson(t));
	}
}
