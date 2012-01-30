package com.wei1224hf.myapp;

import java.io.File;
import java.io.FileInputStream;
import java.io.Serializable;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.Properties;

import org.apache.tomcat.dbcp.dbcp.BasicDataSource;
import org.apache.tomcat.dbcp.dbcp.ConnectionFactory;
import org.apache.tomcat.dbcp.dbcp.DriverManagerConnectionFactory;
import org.apache.tomcat.dbcp.dbcp.PoolableConnectionFactory;
import org.apache.tomcat.dbcp.dbcp.PoolingDataSource;
import org.apache.tomcat.dbcp.pool.ObjectPool;
import org.apache.tomcat.dbcp.pool.impl.GenericObjectPool;
public class Tools {
	private static Hashtable<String, Serializable> il8n = null;
	public static ObjectPool connectionPool=null; 
	private static String dbUrl = "jdbc:mysql://127.0.0.1:3306/dbtest?useUnicode=true&characterEncoding=utf8";
	private static String username = "root";
	private static String password = "root";

	public static Hashtable<String, Serializable> getIl8n(String path) {
		if (il8n == null) {
			il8n = setIl8n(new File(path));
		}
		return il8n;
	}
	
	private static Hashtable<String, Serializable> setIl8n(File file) {
		if (file.isFile()) {
			String key = file.getName().substring(0, file.getName().length() - 4);

			Properties ini = null;
			try {
				ini = new Properties();
				ini.load(new FileInputStream(file));
				Hashtable<String, String> htable = (Hashtable) ini;
				for (Iterator<String> in = htable.keySet().iterator(); in.hasNext();) {
					String key_ = in.next();
					String value_ = htable.get(key_);
					value_ = new String(value_.getBytes("ISO8859-1"), "UTF-8");
					htable.put(key_, value_);
				}
				Hashtable<String, Serializable> ht = new Hashtable<String, Serializable>();
				ht.put(key, htable);
				return ht;
			} catch (Exception ex) {
				ex.printStackTrace();
				return null;
			}
		} else {
			String key = file.getName();
			Hashtable<String, Serializable> ht = new Hashtable<String, Serializable>();
			Hashtable<String, Serializable> ht3 = new Hashtable<String, Serializable>();
			File[] files = file.listFiles();
			for (int i = 0; i < files.length; i++) {
				Hashtable<String, Serializable> ht2 = setIl8n(files[i]);
				String key2 = (String) ht2.keySet().toArray()[0];
				ht3.put(key2, ht2.get(key2));
			}
			ht.put(key, ht3);
			return ht;
		}
	}
	
	public static Connection BasicConn() {
		Connection conn = null;
		BasicDataSource ds = new BasicDataSource();
		ds.setDriverClassName("com.mysql.jdbc.Driver");
		ds.setUrl(dbUrl);
		ds.setUsername(username);
		ds.setPassword(password);
		try {
			conn = ds.getConnection();
			conn.createStatement().execute("SET NAMES UTF8");
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return conn;
	}
	
	public static Connection PoolConn() throws SQLException {
		try {
			Class.forName("com.mysql.jdbc.Driver");
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		}
		if(connectionPool == null){
			connectionPool = new GenericObjectPool(null);	
			ConnectionFactory connectionFactory = new DriverManagerConnectionFactory(dbUrl,username,password);	
			PoolableConnectionFactory poolableConnectionFactory = new PoolableConnectionFactory(connectionFactory, connectionPool, null, null, false, true);
		}		
		Connection conn = null;
		PoolingDataSource dataSource = new PoolingDataSource(connectionPool);	
		try {
			conn = dataSource.getConnection();
			conn.createStatement().execute("SET NAMES UTF8");
		} catch (SQLException e) {
			e.printStackTrace();
		}
		
		return conn;
	}
}
