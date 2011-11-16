//全局变量,国际化语言设置
//var il8n = null;

/**
 * WLS,We Like Study,一种在线考试学习系统
 * 由于前台引用了EXTJS,
 * 所以整个前台代码都被迫开源
 * 
 * 这个版本是 单机版,
 * 用户下载之后,直接双击打开 index.html ,就可以做题了
 * 不用安装 APACHE , PHP , MYSQL 等
 * 主要目的是方便一般用户使用
 * 
 * 当然,功能会限制很多:
 *   只能有一个科目
 *   试卷最多10张
 *   题目总量最多2000个
 *   不会记录历次做题过程
 *   没有统计功能
 *   
 * 只保留了一些简单的必备功能：
 *   自动批卷,
 *   显示解题思路,
 *   随机组卷做题,
 *   统计错题,
 *   错题复习,
 *   知识点掌握度分析
 * 
 * @see www.welikestudy.com
 * @author wei1224hf, from China , mainland
 * @QQ:135426431(group)
 * */
var wls = function() {};//这只是一个namespace

/**
 * 全局变量
 * 其功能类似于传统软件中的数据库
 * 部分数据存储有交叉重叠,有冗余
 * */
var wlsData = {
	questions : [],      //储存所有题目,为 随机组题出卷 提供数据,为此题目总数务必要小,不然浏览器卡死
	papers : [],         //所有试卷
	knowledges : [],     //知识点
	wrongs : []	         //错题本
}