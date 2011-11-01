package test;

import tools.Util;

import com.google.gson.Gson;

public class Center {

}

class DB{
	public static void main(String args[]){
		tools.Db.PoolConn();
	}	
}

class util_test {
	public static void main(String args[]) {
		System.out.println(new Gson().toJson(Util.getIl8n()));
	}
}
class xlsImportTest{
	public static void main(String args[]){
		System.out.print(111);
		tools.Util.importStarndsFromXLS("src\\tools\\GB2260.XLS");
		System.out.print(222);
	}
}