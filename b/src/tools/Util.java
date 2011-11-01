package tools;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.Serializable;
import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.Properties;

import jxl.Sheet;
import jxl.Workbook;
import jxl.read.biff.BiffException;

/**
 * 一些与系统业务逻辑不想关的,
 * 但又非常有用的函数
 * 
 * @author wei1224hf
 * */
public class Util {

	/**
	 * 将一个纯粹的二维表根据ID字段,
	 * 转化为一个TREE,
	 * 其树形支点规则是按照EXTJS组织数据的形式来设定的
	 * 子节点集都用 children 来处理
	 * */
	public static ArrayList<Serializable> getTreeData(String id,
	        ArrayList<Serializable> bigList) {
		if (id == "0") {
			int len = 2;
			ArrayList<Serializable> bigList2 = new ArrayList<Serializable>();
			for (int i = 0; i < bigList.size(); i++) {
				Hashtable<String, Serializable> t = (Hashtable) bigList.get(i);
				String id_ = (String) t.get("id");
				if (id_.length() == len) {
					ArrayList<Serializable> childen = getTreeData(id_, bigList);
					if (childen.size() == 0) {
						t.put("leaf", "true");
					} else {
						t.put("expanded", "true");
						t.put("children", childen);
					}
					bigList2.add(t);
				}
			}
			return bigList2;
		} else {
			int len = id.length() + 2;
			ArrayList<Serializable> bigList2 = new ArrayList<Serializable>();
			for (int i = 0; i < bigList.size(); i++) {
				Hashtable<String, Serializable> t = (Hashtable) bigList.get(i);
				String id_ = (String) t.get("id");
				if (id_.length() == len && id_.substring(0, len - 2).equals(id)) {
					ArrayList<Serializable> childen = getTreeData(id_, bigList);
					if (childen.size() == 0) {
						t.put("leaf", "true");
					} else {
						t.put("expanded", "true");
						t.put("children", childen);
					}
					bigList2.add(t);
				}
			}
			return bigList2;
		}
	}

	/**
	 * 设置甘特图专用的节点属性
	 * 在业务流程图的展现中,
	 * 需要专门为 甘特图 这种图表添加几种属性值
	 * */
	public static int setGantt(Hashtable<String,String> node) {
		DateFormat df = new SimpleDateFormat("yyyy-MM-dd");

		try {
			Date d1 = df.parse((String) node.get("start"));
			Date d2 = df.parse((String) node.get("end"));
			long diff = d1.getTime() - d2.getTime();
			long days = diff / (1000 * 60 * 60 * 24);
			String days_ = Integer.toString( ((int)days));
			
			node.put("duration",days_);
			return 0;
		} catch (Exception e) {
			return 0;
		}
	}

	private static Hashtable<String, Serializable> il8n = null;

	/**
	 * 得到国际化语言包
	 * 那些语言包文件都放置在 src\il8n 文件夹内
	 * 每个模块都有一个对应的语言包文件
	 * */
	public static Hashtable<String, Serializable> getIl8n() {
		if (il8n == null) {
			il8n = setIl8n(new File("src\\il8n"));
		}
		return il8n;
	}

	private static Hashtable<String, Serializable> setIl8n(File file) {
		if (file.isFile()) {
			String key = file.getName().substring(0,
			        file.getName().length() - 4);
			Properties ini = null;
			try {
				ini = new Properties();
				ini.load(new FileInputStream(file));
				Hashtable<String, String> htable = (Hashtable) ini;
				for (Iterator<String> in = htable.keySet().iterator(); in
				        .hasNext();) {
					String key_ = (String) in.next();
					String value_ = (String) htable.get(key_);
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
	
	/**
	 * 将一个EXCEL文件中的标准性数据抽取出来插入到数据库表中
	 * 必须能够成功连接数据库
	 * 而且数据库中存在表 standards 才可以
	 * 当然,必须有规定的数据库表列才行
	 * */
	public static void importStarndsFromXLS(String path){
		int i=0;
		try {
	        Workbook workbook = Workbook.getWorkbook(new File(path));
	        Sheet sheet = workbook.getSheet(0);
	        String source = sheet.getName();
	        int rows = sheet.getRows(); 
	        

	        Connection conn = tools.Db.PoolConn();
			Statement stmt = conn.createStatement();
	        for(i=1;i<rows;i++){
	        	String sql = "insert into standards (code,value,source) values ('"+sheet.getCell(0,i).getContents()+"','"+sheet.getCell(1,i).getContents()+"','"+source+"')";
	        	stmt.executeQuery(sql);
	        }
	        workbook.close();
	        
	       
        } catch (BiffException e) {
	        e.printStackTrace();
        } catch (IOException e) {
	        e.printStackTrace();
        } catch (SQLException e) {
        	System.out.println(i);
	        e.printStackTrace();
        }
	}
}


