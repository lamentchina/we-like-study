<?php
/**
 * 服务端工具包函数库
 * 一些常用的功能性函数,很难判断具体是属于哪个业务模块,
 * 并且会被频繁的在各个业务模块中反复调用的函数,
 * 就都被放置在这里
 * 函数跟变量一律为 static 格式
 * 
 * @author wei1224hf@gmail.com
 * @version 201209
 * */
class tools{
    
	public static $CONN = null;   
	public static $LANG = NULL;	 
    
    /**
     * 得到某一个文件夹内的所有文件,甚至其子文件夹内的所有文件
     * */
	public static function getAllFiles($filedir) {
		$allfiles = array(); //文件名数组
		$tempArr = array(); //临时文件名数组
		if (is_dir($filedir)) {//判断要遍历的是否是目录
			if ($dh = opendir($filedir)) {//打开目录并赋值一个目录句柄(directory handle)
				while (FALSE !== ($filestring = readdir($dh))) {//读取目录中的文件名
					if ($filestring != '.' && $filestring != '..' && $filestring != '.svn') {//如果不是.和..(每个目录下都默认有.和..)
						if (is_dir($filedir . $filestring)) {//该文件名是一个目录时
							$tempArr = tools::getAllFiles($filedir . $filestring . '/');//继续遍历该子目录
							$allfiles = array_merge($allfiles, $tempArr); //把临时文件名和临时文件名组合
						} else if (is_file($filedir . $filestring)) {
							$allfiles[] = $filedir . $filestring; //如果该文件名是一个文件不是目录,直接赋值给文件名数组
						}
					}
				}
			} else {//打开目录失败
				exit('Open the directory failed');
			}
			closedir($dh);//关闭目录句柄
			return $allfiles;//返回文件名数组
		} else {//目录不存在
			exit('The directory is not exist');
		}		
	}
	
	public static function getLanguage(){
	    if (self::$LANG==NULL) {
            self::$LANG = self::readLanguage();
	    }
	    return self::$LANG;
	}	

	/**
	 * 读取国际化语言包内容
	 * */
	public static function readLanguage(){

		$languageFiels = tools::getAllFiles('../language/'.config::$language.'/');
		$arr = array();
		for($i=0;$i<count($languageFiels);$i++){
			//echo substr($languageFiels[$i], strlen($languageFiels[$i])-4 ,4);
			if(substr($languageFiels[$i], strlen($languageFiels[$i])-4 ,4)=='.ini'){
				$path = str_replace('../language/'.config::$language.'/', '', $languageFiels[$i]);
				$path = str_replace('.ini', '', $path);
				$folderLevel = explode("__",$path);
	
				$lang = parse_ini_file($languageFiels[$i],true);
				if($folderLevel[0]=='normal'){
					$arr = array_merge($arr,$lang);
				}else{						
					$arr[$folderLevel[0]] = $lang;
				}		

			}
		}
		return $arr;
	}	

	/**
	 * 数据库链接
	 * */
	public static function conn(){
		if(self::$CONN==null){
			self::$CONN = mysql_connect(config::$host,config::$unm,config::$pwd);
			mysql_select_db(config::$db,self::$CONN);
			mysql_query('SET NAMES UTF8');
		}
		
		return self::$CONN;
	}
	
	public static function closeConn(){
	    mysql_close(self::$CONN);
	}
	
	/**
	 * 获取数据库表中新的一个主键编号
	 * 大多数业务表的 id 都不是自动递增的
	 * */
	public static function getId($tablename){
	    $sql = "select basic_memory__index('".$tablename."') as id";
	    $res = mysql_query($sql,self::conn());
	    $data = mysql_fetch_assoc($res);
	    return $data['id'];
	}
	
	/**
	 * 判断某用户是否具有某权限
	 * */
	public static function checkPermission($code,$username=NULL,$session=NULL){
	    $CONN = tools::conn();
	    
	    //如果开启了 开发模式 的话,就不进行验证
        $res = mysql_query("select extend1 from basic_memory where code = 'develop' ;",$CONN);
        $arr = mysql_fetch_array($res);
        if($arr['extend1']=='1')return TRUE;

        if($username==NULL && isset($_REQUEST['username']))$username = $_REQUEST['username'];
        if($session==NULL && isset($_REQUEST['session']))$session = $_REQUEST['session'];
		
		mysql_query("call basic_user__action('".$username."','".$session."','".$code."',@state,@msg)",$CONN);
		$res = mysql_query("select @state as state,@msg as msg",$CONN);
		$arr = mysql_fetch_array($res,MYSQL_ASSOC);
		if($arr['state']==0){
		    //tools::error(array("state"=>0,"msg"=>$arr['msg']));
		    return false; //TODO 错误信息如何返回
		}else if($arr['state']==1){
		    $data = array(
		    	"state"=>1,
		    	"msg"=>$arr['msg'],
		    	"group"=>"",
		    	"groups"=>""
		    );
		    return true;//TODO 
		}else{
		    return false;
		}
	}
	
	//TODO 所有错误操作,都要有所记录,包括 : 数据库日志记录,文件日志记录,短信错误题型,账号封杀
	public static function error($arr,$type='json') {
	   
	    $states = array("一般错误","严重错误","安全性警报");
	    if($type=='html'){
	        $html = '
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv=content-type content="text/html; charset=UTF-8">
</head>
<body><table>';
            $keys = array_keys($arr);
            for($i=0;$i<count($keys);$i++){
                $html .= "<tr><td>".$keys[$i]."</td><td>".$arr[$keys[$i]]."</td></tr>";
            }
            $html .='
</body>
</html>
	    ';
            echo $html;
	    }elseif ($type=='json'){
	        echo json_encode($arr);exit();
	    }elseif ($type=='xml'){
	        //TODO 
	        echo 'xml not done yet';
	    }
	}
	
	// unicode解码 (测试可行)
    public static function utfdecode($url) {
       preg_match_all('/%u([[:alnum:]]{4})/', $url, $a);
       foreach ($a[1] as $uniord) {
           $dec = hexdec($uniord);
           $utf = '';
           if ($dec < 128) {
               $utf = chr($dec);
           }
           else if ($dec < 2048) {
               $utf = chr(192 + (($dec - ($dec % 64)) / 64));
               $utf .= chr(128 + ($dec % 64));
           } else {
               $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
               $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
               $utf .= chr(128 + ($dec % 64));
           }
           $url = str_replace('%u'.$uniord, $utf, $url);
       }
       return urldecode($url);
    }
    
    /**
     * 判断字符串里的字符个数
     * 英文字母 汉字 都算一个
     * */
    public static function cutString($sourcestr,$cutlength)
    {
       $returnstr='';
       $i=0;
       $n=0;
       $str_length=strlen($sourcestr);//字符串的字节数
       while (($n<$cutlength) and ($i<=$str_length))
        {
          $temp_str=substr($sourcestr,$i,1);
          $ascnum=Ord($temp_str);//得到字符串中第$i位字符的ascii码
          if ($ascnum>=224)    //如果ASCII位高与224，
          {
             $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符         
             $i=$i+3;            //实际Byte计为3
             $n++;            //字串长度计1
          }
           elseif ($ascnum>=192) //如果ASCII位高与192，
          {
             $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
             $i=$i+2;            //实际Byte计为2
             $n++;            //字串长度计1
          }
           elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，
          {
             $returnstr=$returnstr.substr($sourcestr,$i,1);
             $i=$i+1;            //实际的Byte数仍计1个
             $n++;            //但考虑整体美观，大写字母计成一个高位字符
          }
           else                //其他情况下，包括小写字母和半角标点符号，
          {
             $returnstr=$returnstr.substr($sourcestr,$i,1);
             $i=$i+1;            //实际的Byte数计1个
             $n=$n+0.5;        //小写字母和半角标点等与半个高位字符宽...
          }
        }
              if ($str_length>$cutlength){
              $returnstr = $returnstr . "...";//超过长度时在尾处加上省略号
          }
         return $returnstr;
    }     
    
    public static function isjson($json) {
        return  true;
    }
    
    //判断时间格式是否正确
    public static function checkDateFormat($date,$format="Y-m-d")
    {
      if($format=='Y-m-d'){
          //match the format of the date
          if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
          {
            //check weather the date is valid of not
        	if(checkdate($parts[2],$parts[3],$parts[1]))
        	  return true;
        	else
        	 return false;
          }
          else
            return false;
      }else if($format=="Y-m-d H:i:s"){
         
         $strArr = explode(" ",$date);
         
         if(empty($strArr) || count($strArr)!=2 ){
          return false;
         }
         
         if(!tools::checkDateFormat($strArr[0],'Y-m-d'))return false;
         return (bool)preg_match("/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/",$strArr[1]); 
      }
    }   
}

//echo urldecode("%7B%22id%22%3A%22%22%2C%22tablename%22%3A%22building%22%2C%22name%22%3A%22A%22%2C%22address%22%3A%22A%22%2C%22buildingid%22%3A%22100%22%7D");
//echo urldecode("%7B%22id%22%3A%22588%22%2C%22tablename%22%3A%22building%22%2C%22name%22%3A%22%E8%B4%BE%E5%AE%B6%E5%BC%84%E6%96%B0%E6%9D%9113%E5%B9%A2%22%2C%22address%22%3A%22%22%2C%22buildingid%22%3A%22435%22%7D");
