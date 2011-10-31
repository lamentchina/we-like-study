package test.toolbox;

import java.util.ArrayList;
import java.util.Hashtable;

import com.google.gson.Gson;

public class List2Tree {
	public static void main(String args[]){
		ArrayList t = new ArrayList();
		Hashtable t3 = new Hashtable();
		t3.put("id", "10");
		t3.put("name", "aaa");
		t.add(t3);
		
		t3 = new Hashtable();
		t3.put("id", "1011");
		t3.put("name", "aaaddd");
		t.add(t3);
		
		t3 = new Hashtable();
		t3.put("id", "1021");
		t3.put("name", "aaadgggg");
		t.add(t3);
		
		t3 = new Hashtable();
		t3.put("id", "11");
		t3.put("name", "456");
		t.add(t3);
		
		t3 = new Hashtable();
		t3.put("id", "1101");
		t3.put("name", "zzzz");
		t.add(t3);		

		
		ArrayList t2 = tools.Util.getTreeData("0", t);
		
		System.out.print(new Gson().toJson(t2));
	}
}
