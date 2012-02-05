package com.wei1224hf.myapp;

import java.io.Serializable;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.sql.Statement;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Hashtable;

import javax.servlet.http.HttpServletRequest;

import com.google.gson.Gson;

public class Person {
	
	public String action(String action,HttpServletRequest request){
		String output = "";
		if(action==null){
			output = "null function";
		}else if(action.equals("list")) {
			String start = request.getParameter("start") ;
			start = (start==null)?"1":start ;
			String limit = request.getParameter("limit") ;
			limit = (limit==null)?"15":limit ;
			String search = request.getParameter("search") ;
			search = (search==null)?"":search ;			
			Hashtable<String, Serializable> t = list(start,limit,search);
			
			output = new Gson().toJson(t);
		} else {
			output = "wrong function";
		}
		return output;		
	}
	
	private Hashtable<String, Serializable> list(String start, String limit, String search) {
		String total = "0";
		Hashtable<String, Serializable> t = new Hashtable<String, Serializable>();

		String conditions = " where 1=1 ";
		if (!"".equals(search)) 
			conditions += " and name like '%" + search + "%' ";
		ArrayList<HashMap<String, String>> a = new ArrayList<HashMap<String, String>>();
		try {
			Connection conn = Tools.BasicConn();
			Statement stmt = null;

			String sql = "select count(1) as total from person " + conditions;
			stmt = conn.createStatement();
			ResultSet rs = stmt.executeQuery(sql);
			rs.next();
			total = rs.getString("total");

			sql = "select * from person limit "+start+","+limit;
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
				t2.put("age", String.valueOf( getAge(  (String)t2.get("birthday") )));
				t2.put("birthday", ((String)t2.get("birthday")).substring(0,10) );
				
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
	
	private int getAge(String birthday){
		int age = 0;
		DateFormat df = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
		try {
			Date d1 = df.parse(birthday);
			Date d2 = new Date();
			long diff = d2.getTime() - d1.getTime();
			long days = diff / (1000 * 60 * 60 * 24);
			age = (int) (days / 365) ;
		} catch (ParseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return age;
	}
	
	public static void main(String args[]){
		Person obj = new Person();
		System.out.println(obj.getAge("1987-01-02 00:00:00"));
		//Hashtable t = obj.list("0","0","");
		
		//System.out.println(new Gson().toJson(t));
	}
}
