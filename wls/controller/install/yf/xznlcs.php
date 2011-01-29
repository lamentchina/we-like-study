<?php
include_once 'controller/quiz/paper/normal.php';

/**
 * 行政能力测试
 * */
class install_yf_xznlcs extends quiz_paper_normal {

	public $content = null;

	public function readContent($path=null){
		if(isset($_REQUEST['path']) && $path==null)$path = $_REQUEST['path'];
		$path = mb_convert_encoding($path,'GBK','UTF-8');
		$content = file($path);
		$content = implode("\n", $content);
		//		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("DISPLAY: none","",$content);
		$content = str_replace("A. ","A) ",$content);
		$content = str_replace("B. ","B) ",$content);
		$content = str_replace("C. ","C) ",$content);
		$content = str_replace("D. ","D) ",$content);
		$content = str_replace("<a id=\"donw\" href=\"","",$content);

		$this->content = $content;
	}

	public function readList(){
		//http://wei1224hf.gicp.net:8067/plugins/wls/wls.php?controller=install_yf_xznlcs&action=readList
		$content = file("http://www.yfzxmn.cn/com/left/left.jsp?so_id=13&su_id=5");
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("\n","",$content);

		$fileName="file/yf/国家公务员_行政职业能力.list";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		$handle=fopen($fileName,"a");
		fwrite($handle,$content);
		fclose($handle);

		$arr2 = explode("ex_id=",$content);
		$fileName="file/yf/国家公务员_行政职业能力.downlist";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		$handle=fopen($fileName,"a");
		$ids = '';
		for($i=1;$i<count($arr2);$i++){
			$arr3 = explode("&ef_id",$arr2[$i]);
			$ids .= $arr3[0].",";
			fwrite($handle,"http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=5&ex_id=".$arr3[0]."\n");
		}
		$ids = substr($ids,0,strlen($ids)-1);
		fclose($handle);

		$fileName="file/yf/国家公务员_行政职业能力.ids";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		$handle=fopen($fileName,"a");
		fwrite($handle,$ids);
		fclose($handle);

		$this->downLoadPaper($ids);
	}

	public function downLoadPaper($ids){
		$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"libs/DWZ/javascripts/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var arr = [".$ids."];
var index = 0;
var readPaper = function(){
	if(index==arr.length-1)return;
	$.ajax({
		url: \"wls.php?controller=install_yf_xznlcs&action=downLoadPaper2&id=\"+arr[index],
		success: function(msg){
			$(\"#console\").html($(\"#console\").html()+\"<br/>下载试卷\"+arr[index]);
			index ++;
			readPaper(index);
		}
	});
}
readPaper();
</script>
</head>
<body>
<div id=\"console\"><div>
</body>
</html>
		";
		echo $html;
	}

	public function downLoadPaper2(){
		$fileName="file/yf/公务员类/模拟题试卷/国家公务员/行政职业能力/5_".$_REQUEST['id'].".html";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		if(file_exists($fileName)){
			return;
		}else{
			$data=file_get_contents("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=5&ex_id=".$_REQUEST['id']);
			$data = mb_convert_encoding($data,'UTF-8','GBK');
			file_put_contents($fileName,$data);
		}
	}

	public function getRead(){
		for($i=114;$i<130;$i+=5){
			$p1 = strpos($this->content,"s".$i."fs");
			$p2 = strpos($this->content,">".($i+2).".<");
			$data = substr($this->content,$p1,$p2-$p1);
			$data = str_replace("<table width='100%'","",$data);
			$data = str_replace("s".$i."fs type=hidden value=\"1\">","",$data);
			$data = str_replace("border='0' cellpadding='0' cellspacing='0'>","",$data);
			$data = str_replace("<tr><td width='1%' valign='top'","",$data);
			$data = str_replace("src=\"","src=\"file/images/",$data);
			$data = array(
				'id_quiz_type'=>$this->paper['id_quiz_type'],
				'title_quiz_type'=>$this->paper['title_quiz_type'],
				'id_quiz_paper'=>$this->paper['id'],
				'title_quiz_paper'=>$this->paper['title'],
				'title'=>$data,
				'cent'=>1,
				'type'=>5,
				'extype'=>'资料分析'
				);
				$this->ques[] = $data;
		}
	}

	public function read(){
		$this->readContent();
		
		$this->getPaper();
		$this->savePaper();
		$this->getQuestions();
		$this->getRead();
		$this->saveQuestion();
		$this->getImages();
		$this->updatePaper();
		echo json_encode(
			array(
				'id'=>$this->paper['id']
			)
		);
	}

	public function getImages(){
		$arr = explode("src=\"tp",$this->content);
		$arr2 = array();
		for($i=1;$i<count($arr);$i++){
			$p1 = strpos($arr[$i],"\"");
			$data = substr($arr[$i],0,$p1);
			$arr2[] = $data;
		}

		$fileName="file/yf/".$this->paper['id']."_images.downlist";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		$handle=fopen($fileName,"a");
		for($i=0;$i<count($arr2);$i++){
			fwrite($handle,"http://www.yfzxmn.cn/tp".$arr2[$i]."\n");
		}
		fclose($handle);
	}

	public function downloadImages($path=null){
		if(isset($_REQUEST['path']) && $path==null)$path = $_REQUEST['path'];
		$f= fopen($path,"r");
		while (!feof($f)){
			$line = fgets($f);
			$this->GrabImage($line);
		}
		fclose($f);
	}

	public function read2(){
		$path = "公务员类/模拟题试卷/国家公务员/行政职业能力";
		//		$path = mb_convert_encoding($path,'GBK','UTF-8');
		$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"libs/DWZ/javascripts/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var arr = [201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,246,247,248,249,250,251,252,253,254,255,313,314,315,316,317,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,503,504,505,506,507,508,509,510,511,512,513,514,515,516,517,518,519,520,521,522,523,524,525,526,527,528,529,530,531,533,534,535,536,537,727,728,729,730,731,744,745,746,747,751,752,753,754,755,756,757,758,759,761,762,763,764,765,766,767,768,769,770,771,772,773,774,775,776,777,778,779,780,781,782,783,784,785,786,787,788,789,790,791,792,793,794,795,796,797,798,799,800,801,1292,1293,1294,1295,1296,1297,1298,1299,1300,1301,1302,1304,1305,1306,1307,1308,1309,1310,1311,1312,1313,1314,1315,1316,1317,1318,1319,1320,1321,1322,1437,1455,1456,1457,1458,1459,1460,1461,1462,1463,1464,1465,1466,1467,1468,1469,1470,1471,1472,1473,1474,1475,1476,1477,1670,1671,1672,1928,1929,1930,1931,1932,1933,1934,1935,1936,1937,1938,1939,1940,1941,1942,1943,1944,1945,1946,1947,1948,1949,1950,1951,1952];

var index = 0;
var readPaper = function(){	
	$.ajax({
		url: \"wls.php?controller=install_yf_xznlcs&action=read\",
		data:{path:\"file/yf/".$path."/5_\"+arr[index]+\".html\",id_:arr[index]},
		type: \"POST\",
		success: function(msg){
			var obj = jQuery.parseJSON(msg);
			$(\"#console\").html($(\"#console\").html()+\"<br/>导入试卷\"+arr[index]);
			readImages(obj.id);
		}
	});
}
var readImages = function(id){
	$.ajax({
		url: \"wls.php?controller=install_yf_xznlcs&action=downloadImages&path=file/yf/\"+id+\"_images.downlist\",
		success: function(msg){
			//var obj = jQuery.parseJSON(msg);
			$(\"#console\").html($(\"#console\").html()+\"<br/>导入图片\"+arr[index]);
			if(index==20)return;
			index++;
			readPaper();
		}
	});
}
readPaper();
</script>
</head>
<body style=\"border: 0px; padding: 0px; margin: 0px;\">
<div id=\"console\"><div>

</body>
</html>
		";
		echo $html;
	}

	public function GrabImage($url,$filename="") {
		if(!$url || $url=='')return;
		if($filename=="") {
			$ext=strrchr($url,"/");
			$filename="file/images/tp/gwy".$ext;
		}
		if(file_exists(trim($filename))){
			return;
		}else{
			$img=file_get_contents(trim($url));
			file_put_contents(trim($filename),$img);
		}
	}

	public function getQuestions(){
		for($i=1;$i<=135;$i++){

			$p1 = strpos($this->content,">".$i.".</td>");
			$p2 = strpos($this->content,"name=s".($i-1)."fs");
			$data = substr($this->content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A．");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->formatTitle($title);

			$img = null;
			if(strpos($data,'放大')!=false){
				$p1 = strpos($data,"放大");
				$p2 = strpos($data,"anway");
				$img = substr($data,$p1,$p2-$p1);
				$img = str_replace("放大 src=\"","",$img);
				$img = str_replace("\"></table></table><a name='","",$img);
			}

			$p1 = strpos($data,"A．");
			$p2 = strpos($data,"B．");
			$A = substr($data,$p1,$p2-$p1);
			$A = str_replace("A．","",$A);

			$p1 = strpos($data,"B．");
			$p2 = strpos($data,"C．");
			$B = substr($data,$p1,$p2-$p1);
			$B = str_replace("B．","",$B);

			$p1 = strpos($data,"C．");
			$p2 = strpos($data,"D．");
			$C = substr($data,$p1,$p2-$p1);
			$C = str_replace("C．","",$C); 

			$p1 = strpos($data,"D．");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D．","",$D);
			$D = str_replace(">".$i,"",$D);

			$p1 = strpos($data,"name=s".($i-1)."an");
			$p2 = strpos($data,"name=s".($i-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);

			$data = array(
				'id_quiz_type'=>$this->paper['id_quiz_type'],
				'title_quiz_type'=>$this->paper['title_quiz_type'],
				'id_quiz_paper'=>$this->paper['id'],
				'title_quiz_paper'=>$this->paper['title'],
				'title'=>$title,
				'cent'=>1,
				'details'=>json_encode(
			array(
						'options'=>array(
			array(
								'option'=>'A',
								'title'=>$A,
			),
			array(
								'option'=>'B',
								'title'=>$B,
			),
			array(
								'option'=>'C',
								'title'=>$C,
			),
			array(
								'option'=>'D',
								'title'=>$D,
			),
			)
			)
			),
				'type'=>1,
				'answer'=>$answer,
				'description'=>$description,
			);
			$data['details'] = str_replace("\\","\\\\",$data['details']);
			if($img!=null){
				$data['title']='<img src="file/images/'.$img.'" />';
			}
			if($i<=25){
				$data['extype']='言语理解与表达';
			}else if($i>=26 && $i<=35){
				$data['extype']='数学推理';
			}else if($i>=36 && $i<=50){
				$data['extype']='数学运算';
			}else if($i>=51 && $i<=60){
				$data['extype']='图形推理';
			}else if($i>=61 && $i<=70){
				$data['extype']='定义判断';
			}else if($i>=71 && $i<=80){
				$data['extype']='类比推理';
			}else if($i>=81 && $i<=95){
				$data['extype']='演绎推理';
			}else if($i>=96 && $i<=115){
				$data['extype']='常识判断';
			}else if($i>=116 && $i<=135){
				$data['extype']='资料分析';
			}
			$this->ques[] = $data;
		}
	}

	public function saveQuestion(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;
		for($i=0;$i<115;$i++){
			$keys = array_keys($this->ques[$i]);
			$keys = implode(",",$keys);
			$values = array_values($this->ques[$i]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$this->ques[$i]['id'] = mysql_insert_id($conn);
			$this->subques[] = $this->ques[$i]['id'];
			$this->mainques[] = $this->ques[$i]['id'];
		}

		for($i=0;$i<4;$i++){
			$keys = array_keys($this->ques[$i+135]);
			$keys = implode(",",$keys);
			$values = array_values($this->ques[$i+135]);
			$values = implode("','",$values);
			$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$id = mysql_insert_id($conn);
			$this->ques[$i]['id'] = mysql_insert_id($conn);
			$this->mainques[] = $this->ques[$i]['id'];

			for($j=(135-(4-$i)*5);$j<(135-(4-$i)*5)+5;$j++){
				$this->ques[$j]['id_parent'] = $id;
				$keys = array_keys($this->ques[$j]);
				$keys = implode(",",$keys);
				$values = array_values($this->ques[$j]);
				$values = implode("','",$values);
				$sql = "insert into ".$pfx."wls_question (".$keys.") values ('".$values."')";
				mysql_query($sql,$conn);
				$this->ques[$j]['id'] = mysql_insert_id($conn);
				$this->subques[] = $this->ques[$j]['id'] ;
			}
		}
	}

	public function getPaper(){
		$data = array(
			'title' => '国家公务员考试'.rand(100,200),
			'title_quiz_type' => '行政能力',
			'description' => '',
			'creator' => 'admin',
			'publisher' => 'admin',
			'date_created' => date('Y-m-d'),
			'difficulty'=>'1',
		);
		$this->paper = $data;
	}

}