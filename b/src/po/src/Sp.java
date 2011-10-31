package po.src;

import java.io.Serializable;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Hashtable;

import javax.servlet.http.HttpServletRequest;

import com.google.gson.Gson;

import tools.Db;

public class Sp {
	
	public String action(String action, HttpServletRequest request) {
		if(action==null){
			return "null function";
		}else if(action.equals("list")) {
			String row_id = request.getParameter("row_id");
			if (row_id == null)
				return "row_id null";
			Hashtable<String, Serializable> t = list(row_id);			
			return new Gson().toJson(t);
		} else {
			return "wrong function";
		}
	}	
	
	private Hashtable<String, Serializable> list(String row_id) {
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();
		t.put("page", "1");
		t.put("total", "1");
		t.put("pagesize", "1");

		ArrayList<Hashtable<String, String>> a = new ArrayList<Hashtable<String, String>>();
		try {
			Statement stmt = null;
			String sql = "select row_id,brand_type,brand_rate,proc_model,level1,final_pr,final_order,sp_name,sp_id,sp_cert_level,biz_status,origin_flag from po_src_sp t where t.src_id = '"
			        + row_id + "' ";

			Connection conn = Db.PoolConn();
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			while (rs.next()) {
				Hashtable<String, String> t2 = new Hashtable<String, String>();
				ResultSetMetaData rsmd = rs.getMetaData();
				int count = rsmd.getColumnCount();
				for(int i=1;i<count;i++){					
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
