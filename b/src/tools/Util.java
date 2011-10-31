package tools;

import java.io.File;
import java.io.FileInputStream;
import java.io.Serializable;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.Map;
import java.util.Properties;

import com.google.gson.Gson;

public class Util {

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

	private static int setGantt(Hashtable<String,String> node) {
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
}

class util_test {
	public static void main(String args[]) {

		System.out.println(new Gson().toJson(Util.getIl8n()));

	}

}