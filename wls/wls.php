<?php
/**
 * 在线学习模块
 * Wei Like Study
 * 其他后台控制器都继承自这里
 *
 * 主要的分块有:
 *     题目
 *     考试科目
 *     测试卷
 *     知识点
 *     错题本
 *     题目评价
 *
 *     做题记录日志
 *     对错率日志
 *
 *
 * @version 2010-09
 * @copyright www.wei1224hf.com.cn wei1224hf@gmail.com 大菜鸟
 * */
class wls {

	public $cfg ;

	/**
	 * 初始化配置内容
	 * 启用了Joomla本身自带的其他性能,因为过于复杂
	 * */
	function wls(){
//		sleep(1);
		header('Content-type: text/html; charset=utf-8');
		require_once 'config.php';
		$this->cfg = new wlsconfig();	
	}

	/**
	 * 得到数据库连接参数
	 * 需要引用配置文件
	 * */
	public function conn(){
		$conn = mysql_connect($this->cfg->host,$this->cfg->user,$this->cfg->password);
		mysql_select_db($this->cfg->db,$conn);
		mysql_query('SET NAMES UTF8');		
		return $conn;
	}
	
	/**
	 * 系统倚赖 JQUERY UI 框架: DWZ
	 * DWZ 是一套国产的,基于JQUERY的RIA,功能强大
	 * 
	 * @see http://bbs.dwzjs.com/
	 * */
	public function headerScripts(){
		$html = '
		
<link href="libs/DWZ/themes/default/style.css" rel="stylesheet" type="text/css" />
<link href="libs/DWZ/themes/css/core.css" rel="stylesheet" type="text/css" />
<link href="libs/DWZ/uploadify/css/uploadify.css" rel="stylesheet" type="text/css" />
<link href="view/wls.css" rel="stylesheet" type="text/css" />
		
<script  src="libs/DWZ/javascripts/speedup.js" type="text/javascript"></script>
<script  src="libs/DWZ/javascripts/jquery-1.4.2.js" type="text/javascript"></script>

<script  src="libs/DWZ/javascripts/jquery.cookie.js" type="text/javascript"></script>
<script  src="libs/DWZ/javascripts/jquery.validate.js" type="text/javascript"></script>
<script  src="libs/DWZ/javascripts/jquery.bgiframe.js" type="text/javascript"></script>
<script  src="libs/DWZ/xheditor/xheditor-zh-cn.min.js" type="text/javascript"></script>

<script src="libs/DWZ/javascripts/dwz.core.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.scrollCenter.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.validate.method.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.regional.zh.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.barDrag.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.drag.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.tree.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.accordion.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.ui.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.theme.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.switchEnv.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.alertMsg.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.contextmenu.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.navTab.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.tab.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.resize.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.jDialog.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.dialogDrag.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.cssTable.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.stable.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.taskBar.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.ajax.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.pagination.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.datepicker.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.effects.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.panel.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.checkbox.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.history.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.combox.js" type="text/javascript"></script>
<!-- 
<script  src="libs/DWZ/bin/dwz.min.js" type="text/javascript"></script>
-->
<script  src="libs/DWZ/javascripts/dwz.regional.zh.js" type="text/javascript"></script>
<script  src="libs/DWZ/uploadify/scripts/swfobject.js" type="text/javascript"></script>
<script  src="libs/DWZ/uploadify/scripts/jquery.uploadify.v2.1.0.js" type="text/javascript"></script>
<script  src="view/wls.js" type="text/javascript"></script>
<script  src="view/quiz/quiz.js" type="text/javascript"></script>
<script  src="view/user/user.js" type="text/javascript"></script>
<script  src="libs/jquer.float.js" type="text/javascript"></script>
		';
		return $html;
	}
	
	/**
	 * 根据秒数
	 * */
	public function getTimer($sec){
		$days = 0;
		$hours = 0;
		$minutes = 0;
		$days = floor($sec / (24*3600));
		$sec = $sec % (24*3600);
		$hours = floor($sec / 3600);
		$remainSeconds = $sec % 3600;
		$minutes = floor($remainSeconds / 60);
		$seconds = intval($sec - $hours * 3600 - $minutes * 60);
		$str = '';
		if($days!=0)$str .= "&nbsp;".$days."天"; 
		if($hours!=0)$str .= "&nbsp;".$hours."小时"; 
		if($minutes!=0)$str .= "&nbsp;".$minutes."分钟"; 
		if($seconds!=0)$str .= "&nbsp;".$seconds."秒";
		return $str;
	}

	public function split_title($title,$num){
		$str=strlen($title);
		if($str> $num){
			$title=mb_substr($title,0,$num,'UTF-8');
			$title=$title. "... ";
		}
		return   $title;
	}
	
	public function isgb($str){
		if (strlen($str)>=2){
			$str=strtok($str,"");
			if ((ord($str[0])<161) || (ord($str[0])>247)){
				return false;
			}else{
				if    ((ord($str[1])    <    161)||(ord($str[1])    >    254)){
					return false;
				}else{
					return true;
				}
			}
		}else{
			return false;
		}
	}
	
	public function format($str){
		$str = str_replace("'","&acute;",$str);
		$str = str_replace('"','&quot;',$str);
		$str = str_replace("\n","<br/>&nbsp;&nbsp;",$str);
		//TODO excel 特殊空格
//		$str_ = '';
//
//		for($i=0;$i<strlen($str);$i++){
//			$c = ord(substr($str,$i,1));
//			if( ($c>=20 && $c<=126 ) || (($c & 0x80) == 128) ){
//				$str_ .= substr($str,$i,1);
//			}
//		}

		return trim($str);
	}	

	/**
	 * 处理试卷的批改方式的键值对应关系,如果bool是true,就根据汉字读索引 
	 *  
	 * @param key 可以是str/int,是索引
	 * @param bool true的话,则根据汉字读索引
	 * */
	public function formatMarkingMethod($key,$bool=false){
		$config = array(
			'0'=>'自动批改',
			'1'=>'教师批改',
			'2'=>'用户批改',
			'3'=>'多用户批改',
		);
		
		if($bool){
			$config = array_flip($config);
		}
		
		return $config[$key];
	}
	
	/**
	 * 处理题目基础类型的对应关系,如果bool是true,就根据汉字读索引 
	 *  
	 * @param key 可以是str/int,是索引
	 * @param bool true的话,则根据汉字读索引
	 * */
	public function formatQuesType($key,$bool=false){
		$config = array(
			'1'=>'单项选择题',
			'2'=>'多项选择题',
			'3'=>'判断题',
			'4'=>'简答题',
			'5'=>'短文阅读',
		);
		
		if($bool){
			$config = array_flip($config);
		}
		
		return $config[$key];
	}
	
	/**
	 * 处理题目标题,主要处理\n变< br/ >之类的
	 *  
	 * @param title 要处理的标题
	 * @param bool true的话,根据< br/ >转\n
	 * */
	public function formatTitle($title,$bool=false){
		if($bool){
			$title = str_replace("&acute;","'",$title);
			$title = str_replace('&quot;','"',$title);
			$title = str_replace('&nbsp;',' ',$title);
			$title = str_replace('<br>',"\n",$title);
			$title = str_replace('<br/>',"\n",$title);
			return $title;
		}else{
			$title = str_replace("'","&acute;",$title);
			$title = str_replace('"','&quot;',$title);
			$title = str_replace("\n","<br/>&nbsp;&nbsp;",$title);
			return $title;
		}
	}
	
	public function hackAttack(){
		echo "
			!老大
			,我知道你技术牛逼
			,但别黑我的网站了行不
			,咱草根站长维护一个网站也不容易啊
			,有事好商量
			,但如果你发现了哪个漏洞
			,欢迎来余姚我请你喝烧酒^_^
		";
		//TODO
	}
}

/**
 * 统一接口日志记录
 * 日志表中其中必有一项是 时间 ,其中的时间主要按天来存
 *
 * 日志主要有:
 *   题目记录.每个人每做一道题目,都要记录这次做题的详细情况.题目日志是系统所有统计分析功能的基础
 *   试卷记录.每个人每做一篇试卷
 *   每日排名.每天凌晨,系统自动统计昨天的排名,包括 单个科目考试排名,每个科目的题型的排名,某些用户组的排名
 *   知识点掌握度记录.每天凌晨,系统自动统计每个人在所参与科目的知识点掌握程度
 *   对错率记录.每天凌晨,系统自动统计每个人在所参与的科目及题型做题的对错率情况
 * */
interface record{

	/**
	 * 创建一张新的日志表
	 * 由于日志都是数据量非常大的表,因此会每隔一段时间建一张表,
	 * 通常是每隔一个月
	 * */
	public function creatNewTable();

	/**
	 * 创建一条日志记录
	 * 由于日志记录一般计算量比较大,详细的算法可能不会写在PHP后台,而是在数据库的存储过程中
	 * */
	public function add();

	/**
	 * 得到一条日志记录
	 * 日志记录一般都存JSON数据,这种数据拿到前台可以直接使用
	 * 根据 returnType ,可以返回 JSON 或 ARRAY
	 * */
	public function get();

	/**
	 * 按照时间颗粒来统计某些数值
	 * 时间颗粒一般有:
	 *   小时,上下午,天,星期,旬,月,年
	 * */
	public function summaryByPeriod();
}

//控制器有级层关系,以空格分开
//每一个控制器对应一个后台文件
$controller = null;
if(isset($_REQUEST['controller'])){
	$arr = explode("_",$_REQUEST['controller']);
	$dept = count($arr);
	if($dept==1){
		include 'controller/'.$_REQUEST['controller'].'.php';
		eval('$controller = new '.$_REQUEST['controller'].'();');
	}else{
		$path = '';
		for($i=0;$i<count($arr);$i++){
			$path .= "/".$arr[$i];
		}
		if($arr[count($arr)-1] == $arr[count($arr)-2]){
			unset($arr[count($arr)-1]);
		}
		$controllername = '';
		for($i=0 ;$i<count($arr);$i++){
			$controllername .= $arr[$i]."_";
		}
		$controllername = substr($controllername,0,strlen($controllername)-1);
		include 'controller'.$path.'.php';
		eval('$controller = new '.$controllername.'();');
	}
}else{
	//echo 'need controller!';
}

//每个行为对应某个控制器的一个函数,这个函数必定是PUBLIC的
if(isset($_REQUEST['action'])){
	eval('$controller->'.$_REQUEST['action'].'();');
}else{
	//echo 'need action!';
}