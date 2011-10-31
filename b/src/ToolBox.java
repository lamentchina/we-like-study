import java.util.Hashtable;


public class ToolBox {
	public String action(String action) {
		if (action == null) {
			return "null action";
		} else if (action.equals("list")) {
			return "";
		} else {
			return "wrong function";
		}
	}
	

}
