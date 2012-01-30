package com.wei1224hf.myapp;

import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;


public class Server extends HttpServlet {
	private static final long serialVersionUID = 1L;
	private String output;
       
    public Server() {
        super();
    }

	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		request.setCharacterEncoding("UTF-8");
		response.setCharacterEncoding("UTF-8");
		
		String cls = request.getParameter("class");
		String fun = request.getParameter("function");
		
		if(cls==null){
			output = "missing class";
		}else if(cls.equals("com.wei1224hf.myapp.Person")){
			output = new com.wei1224hf.myapp.Person().action(fun, request);
		}else{
			output = "error";
		}
		
		response.getWriter().write(output);
	}


	protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		doGet(request, response);
	}

}
