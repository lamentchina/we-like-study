package tools;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import org.apache.tomcat.dbcp.dbcp.BasicDataSource;
import org.apache.tomcat.dbcp.dbcp.ConnectionFactory;
import org.apache.tomcat.dbcp.dbcp.DriverManagerConnectionFactory;
import org.apache.tomcat.dbcp.dbcp.PoolableConnectionFactory;
import org.apache.tomcat.dbcp.dbcp.PoolingDataSource;
import org.apache.tomcat.dbcp.pool.ObjectPool;
import org.apache.tomcat.dbcp.pool.impl.GenericObjectPool;

public class Db {
	
	public static ObjectPool connectionPool=null; 
	private static String dbUrl = "jdbc:oracle:thin:test1/test1@(description=(address_list=( address=(host=127.0.0.1)(protocol=tcp)(port=1521) )(load_balance=no)(failover=yes))(connect_data=(server=dedicated)(service_name=wei1224hf)))";
	
	public static Connection BasicConn() {
		Connection conn = null;
		BasicDataSource ds = new BasicDataSource();
		ds.setDriverClassName("oracle.jdbc.driver.OracleDriver");
		ds.setUrl(dbUrl);
		try {
			conn = ds.getConnection();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return conn;
	}
	
    public static Connection getOracleConnection(){
        Connection conn = null;
        try {
            Class.forName("oracle.jdbc.driver.OracleDriver").newInstance();
            conn= DriverManager.getConnection(dbUrl);
        } catch (ClassNotFoundException e) {
            e.printStackTrace();
            System.out.println("Oracle驱动没找到");
        } catch (InstantiationException e) {
            e.printStackTrace();
        } catch (IllegalAccessException e) {
            e.printStackTrace();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return conn;
    }

	public static Connection PoolConn() {
		try {
			Class.forName("oracle.jdbc.driver.OracleDriver");
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		}
		if(connectionPool == null){
			connectionPool = new GenericObjectPool(null);	
			ConnectionFactory connectionFactory = new DriverManagerConnectionFactory(
			        dbUrl,
			        null);	
			PoolableConnectionFactory poolableConnectionFactory = new PoolableConnectionFactory(
			        connectionFactory, connectionPool, null, null, false, true);
		}
		
		Connection conn = null;
		PoolingDataSource dataSource = new PoolingDataSource(connectionPool);
		try {
			conn = dataSource.getConnection();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return conn;
	}
	
	public static Connection PoolConn4Web(){
		Connection conn = null;
        try {
        	Context context = new InitialContext();
			DataSource ds = (DataSource) context.lookup("java:/comp/env/jdbc/oracle");
			conn = ds.getConnection();
        } catch (NamingException e) {
            e.printStackTrace();
        } catch (SQLException e) {
	        e.printStackTrace();
        }
        return conn;
	}
}
