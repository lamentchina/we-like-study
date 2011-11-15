/**
 * 题目来源于一张卷子(paper),
 * 每一道题都有 题目说明,解题思路,答案 等属性
 * 还有 初始化DOM,显示解题思路 等函数,
 * 不过部分函数跟属性会在扩展题型中添加
 * 这里是父类,给出了一道题所必须要有的公共属性
 * */
wls.question = Ext.extend(wls, {

    id : null,               //索引编号,在一张卷子里的题目序号,比如 1 2 3 4 5
	id_parent : null,        //在'阅读理解','完型填空','短文听力'等大题中使用,让子题目指向母题目
	id_quiz_paper : null,    //这个题目属于哪张试卷.WLS系统中,每个题目都是属于某一张特定的试卷的,其值应该是 wls_quiz_paper表中的id
	
	answer : null,           //正确答案
	myAnswer : null,         //我选择的答案
	questionData : null,     //这是一个Object,内含各个题目选项.如果体型是 填空题 或 简答题,则只包含题目描述(title)
	
	cent : null,             //这个题目的分值
	cent_ : null,            //用户答题后所获得的分值,一般而言,要么是0,要么就等于cent.但简答题则不同
	
	type : null,             //题型,字符串,可以是 单选题,多选题,判断题,填空题等
	quiz : null,             //试卷对象.	
	
	markingmethod : 0,       //卷子批改方式:自动批改 或 教师人工批改
	
	layout : 'vertical',     //题目选项的排列方式,横向或纵向,默认为纵向,就是一个选项一行
	description : '',        //解题思路
	title : '',              //题目标题
	
	state : '',              //状态 submitted 已提交
	
    initDom : function(){},         //初始化页面,   接口定义,子类覆盖
    showDescription : function(){},	//显示解题思路, 接口定义,子类覆盖
    submit : function(){}           //提交答案               接口定义,子类覆盖

});

/**
 * */
var wls_question_toogle = function(id) {
	$('#w_qs_d_t_' + id, $('#w_qs_' + id)).toggleClass('w_q_d_t_2');
	$(".w_q_d", $('#w_qs_' + id)).toggleClass('w_q_d_h');
}
var wls_question_done = function(id){
	if($('#w_q_subQuesNav_' + id).hasClass('w_q_sn_undone')){
		$('#w_q_subQuesNav_' + id).attr('class','w_q_sn_done');
	}
}
