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

	/**
	 * 配置参数
	 * */
	public $cfg ;

	/**
	 * 初始化配置内容
	 * 强制规定内容的输出格式都是UTF-8的
	 * */
	function wls(){
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
		
<script src="libs/DWZ/javascripts/speedup.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/jquery-1.4.2.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/jquery.cookie.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/jquery.validate.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/jquery.bgiframe.js" type="text/javascript"></script>
<script src="libs/DWZ/xheditor/xheditor-zh-cn.min.js" type="text/javascript"></script>
<script src="libs/DWZ/bin/dwz.min.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.navTab.js" type="text/javascript"></script>
<script src="libs/DWZ/javascripts/dwz.regional.zh.js" type="text/javascript"></script>
<script src="libs/DWZ/uploadify/scripts/swfobject.js" type="text/javascript"></script>
<script src="libs/DWZ/uploadify/scripts/jquery.uploadify.v2.1.0.js" type="text/javascript"></script>

<!--[if IE]>
<link href="libs/DWZ/themes/css/ieHack.css" rel="stylesheet" type="text/css" />
<script src="libs/jquery.flot.excanvas.js" type="text/javascript"></script>
<![endif]-->

<link href="view/wls.css" rel="stylesheet" type="text/css" />
<script src="view/wls.js" type="text/javascript"></script>
<script src="view/quiz/quiz.js" type="text/javascript"></script>
<script src="view/user/user.js" type="text/javascript"></script>
<script src="libs/jquery.flot.js" type="text/javascript"></script>
<script src="libs/jquery.flot.excanvas.js" type="text/javascript"></script>
		';
		return $html;
	}
	
	/**
	 * 根据秒数,返回更友好,符合中国人习惯的时间描述
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

	/**
	 * 将长的标题切断,以短标题再加3个点号显示
	 * */
	public function split_title($title,$num){
		$str=strlen($title);
		if($str> $num){
			$title=mb_substr($title,0,$num,'UTF-8');
			$title=$title. "... ";
		}
		return   $title;
	}
	
	/**
	 * 判断这整个字符串是不是中文的
	 * */
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
	
	/**
	 * 发现有人在黑这个系统
	 * */
	public function hackAttack(){
		echo "
			不允许此操作!请修改配置文件中的 debug = 1 再执行.
		";
		//TODO
	}
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