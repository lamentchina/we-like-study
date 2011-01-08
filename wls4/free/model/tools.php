<?php 
/**
 * 一些与系统逻辑关系不大,或者无法判断应该归到那个类的函数
 * 大多数函数都会被引用
 * */
class tools {
	
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
		if($key==''){
			if($bool){
				return '自动批改';
			}else{
				return 0;
			}
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
}
?>