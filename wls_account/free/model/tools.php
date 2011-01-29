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

	public function getTimeDif($t1,$t2=null){
		$d1=strtotime($t1);
		$d2 = null;
		if($t2==null){
			$d2=strtotime("now");
		}

		$dif = ($d2-$d1);
		if($dif>2592000){
			return round($dif/30/3600/24)."月";
		}
		if($dif>86400){
			return round($dif/3600/24)."天";
		}
		if($dif>3600){
			return round($dif/3600)."小时";
		}
		if($dif>60){
			return round($dif/60)."分钟";
		}
		return $dif."秒";
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
			'5'=>'组合题',
			'6'=>'大题',
			'7'=>'填空题'
			);

			if($bool){
				$config = array_flip($config);
			}

			return $config[$key];
	}

	public function formatApplicationType($key,$bool=false){
		$config = array(
			'0'=>'做测验卷',
			'1'=>'随机练习',
			'2'=>'题型掌握度练习',
			'3'=>'知识点掌握度练习题',
			'4'=>'参加在线考试',
			'5'=>'错题本练习',
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
	 * @param bool true的话,根据< br/ >转\
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
			$title = str_replace("<img src=\"","TEMP1",$title);
			$title = str_replace(".gif\" />","TEMP2",$title);
			
			$title = str_replace("'","&acute;",$title);
			$title = str_replace('"','&quot;',$title);
			$title = str_replace("\n","<br/>&nbsp;&nbsp;",$title);
			
			$title = str_replace("TEMP1","<img src=\"",$title);
			$title = str_replace("TEMP2",".gif\" />",$title);			
			return $title;
		}
	}

	public $desktopMenu = array();
	public function treeMenuToDesktopMenu($id=null,$data_all){
		if($id==null){
			$len = 2;
			for($i=0;$i<count($data_all);$i++){
				if(strlen($data_all[$i]['id_level'])==$len){
					if(isset($data_all[$i]['children'])){
						$data_all[$i]['menupath'] = '/';
						for($i2=0;$i2<count($data_all[$i]['children']);$i2++){
							$data_all[$i]['children'][$i2]['menupath'] = $data_all[$i]['menupath'].$data_all[$i]['text']."/";
						}
						$this->treeMenuToDesktopMenu($data_all[$i]['id_level'],$data_all[$i]['children']);
						
						$data_all[$i]['startmenu'] = $data_all[$i]['menupath'].$data_all[$i]['text']."/";
						unset($data_all[$i]['children']);
						$this->desktopMenu[] = $data_all[$i];						
					}else{
						$data_all[$i]['startmenu'] = "/";
						$this->desktopMenu[] = $data_all[$i];
					}
				}
			}
		}else{
			$len = strlen($id)+2;
			for($i=0;$i<count($data_all);$i++){
				if(!isset($data_all[$i]['id_level'])){

				}else{
					if(strlen($data_all[$i]['id_level'])==$len){
						if(isset($data_all[$i]['children'])){
							for($i2=0;$i2<count($data_all[$i]['children']);$i2++){
								$data_all[$i]['children'][$i2]['menupath'] =  $data_all[$i]['menupath'].$data_all[$i]['text']."/";
							}
							$this->treeMenuToDesktopMenu($data_all[$i]['id_level'],$data_all[$i]['children']);
							
							$data_all[$i]['startmenu'] = $data_all[$i]['menupath'].$data_all[$i]['text']."/";
							unset($data_all[$i]['children']);
							$this->desktopMenu[] = $data_all[$i];	
						}else{
							$data_all[$i]['startmenu'] = $data_all[$i]['menupath'];
							$this->desktopMenu[] = $data_all[$i];
						}
					}
				}
			}
		}
	}

	public function getTreeData($id=null,$data_all){
		if($id==null){
			$len = 2;
			$data = array();
			for($i=0;$i<count($data_all);$i++){
				if(strlen($data_all[$i]['id_level'])==$len){
					$data_all[$i]['children'] = $this->getTreeData($data_all[$i]['id_level'],$data_all);
					if(count($data_all[$i]['children'])==0){
						unset($data_all[$i]['children']);
						$data_all[$i]['leaf'] = true;
					}else{
						$data_all[$i]['expanded'] = true;
					}
					$data_all[$i]['text'] = $data_all[$i]['name'];
					unset($data_all[$i]['name']);
					if(isset($data_all[$i]['money']))unset($data_all[$i]['money']);
					if(isset($data_all[$i]['id']))unset($data_all[$i]['id']);
					if($data_all[$i]['checked']==0){
						$data_all[$i]['checked'] = false;
					}else{
						$data_all[$i]['checked'] = true;
					}
					$data[] = $data_all[$i];
				}
			}

			return $data;
		}else{
			$data = array();
			$len = strlen($id)+2;
			for($i=0;$i<count($data_all);$i++){
				if(strlen($data_all[$i]['id_level'])==$len && substr($data_all[$i]['id_level'],0,$len-2)==$id){
					$data_all[$i]['children'] = $this->getTreeData($data_all[$i]['id_level'],$data_all);
					if(count($data_all[$i]['children'])==0){
						unset($data_all[$i]['children']);
						$data_all[$i]['leaf'] = true;
					}else{
						$data_all[$i]['expanded'] = true;
					}
					$data_all[$i]['text'] = $data_all[$i]['name'];
					unset($data_all[$i]['name']);
					if(isset($data_all[$i]['money']))unset($data_all[$i]['money']);
					if(isset($data_all[$i]['id']))unset($data_all[$i]['id']);
					if($data_all[$i]['checked']==0){
						$data_all[$i]['checked'] = false;
					}else{
						$data_all[$i]['checked'] = true;
					}
					$data[] = $data_all[$i];
				}
			}

			return $data;
		}
	}

	public function excelTime($date, $time=false){
		if(is_numeric($date)){
			$jd = GregorianToJD(1, 1, 1970);
			$gregorian = JDToGregorian($jd+intval($date)-25569);
			$date = explode('/',$gregorian);
			$date_str = str_pad($date[2],4,'0', STR_PAD_LEFT)
			."-".str_pad($date[0],2,'0', STR_PAD_LEFT)
			."-".str_pad($date[1],2,'0', STR_PAD_LEFT)
			.($time?" 00:00:00":'');
			return $date_str;
		}
		return $date;
	}

	public function formatCellData($celldata){
		$celldata = str_replace('\n','',$celldata);
		$celldata = trim($celldata);
		return $celldata;
	}
}
?>