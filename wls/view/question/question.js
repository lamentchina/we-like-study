/**
 * 题目的公共前台操作
 * 题目类型有多种,具体的每一种题目都有哪些操作,在type中有描述
 * */
var wls_question = function(){
	
	//题目编号
	this.quesid = '';
	
	//题目将使用的 DOM ID编号
	this.quesDomId = '';
	
	//题目所处的DOM位置
	this.parentDom = null;
	
	//题目在整个测试卷中所处的位置,如果是测试卷模式的话
	this.quesIndex = 0;
	
	//题目的内容,根据不同的题目类型,有不同的组成
	this.quesData = null;
	
	/**
	 * 题目的分值
	 * */
	this.cent = 0;//分值
	
	/**
	 * 我的得分
	 * */
	this.myCent = 0;
	
	this.answer = '';
	this.myAnswer = '';
	this.description = '';
	this.markingmethod = 0;
	

	
	/**
	 * 父级题目,即主题目
	 * */
	this.parentQuestion = null;
	
	/**
	 * 如果用户做错了这道题,
	 * 可以描述一下为什么我会做错
	 * 需要在题目的 工具栏 中添加一个 注释标签
	 * */
	this.whyImWrong = function(){}
	
	this.description = '';
	
	/**
	 * 显示这道题目的注释,
	 * 如果有注释的话,就在题目的 工具栏 中添加一个 注释标签
	 * */
	this.showDescription = function(){}
	
	/**
	 * 评论这道题目,将弹出一个模式窗口
	 * */
	this.comment = function(){
		
	}
	 
	this.quiz = null;
	
	this.desState = 0;
}

wls_question.prototype = new wls();