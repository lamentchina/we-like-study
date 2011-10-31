
import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

public class Server extends HttpServlet {
    private static final long serialVersionUID = 1L;
	String output;
       
    public Server() {
        super();
    }

	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		response.setCharacterEncoding("UTF-8");

		String cls = request.getParameter("class");
		String act = request.getParameter("function");
		if(cls == null ){
			output = new ToolBox().action(act);
		}else if(cls.equals("po.Src")){
			output = new po.Src().action(act, request);
		}else if(cls.equals("po.src.Sp")){
			output = new po.src.Sp().action(act, request);
		}else if(cls.equals("sm.Role")){
			output = new sm.Role().action(act, request);
		}else if(cls.equals("po.Head")){
			output = new po.Head().action(act, request);
		}else if(cls.equals("po.Line")){
			output = new po.Line().action(act, request);
		}else{
			output = "wrong class";
		}

		response.getWriter().write(output);
	}
	
	protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		doGet(request, response);
	}
}
