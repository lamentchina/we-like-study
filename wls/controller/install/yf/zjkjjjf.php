<?php
/**
 * 公共英语2级
 * */
class install_yf_zjkjjjf extends wls {

	public $ques = array();
	
	public $subques = array();

	public $mainques = array();

	public $content = null;

	public $paper = array(
		'id'=>0,
		'title'=>0,
		'title_quiz_type'=>0,
		'id_quiz_type'=>0,
	);
	
	public function readList(){
		$content = file("http://www.yfzxmn.cn/com/left/left.jsp?so_id=30&su_id=6");  
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("\n","",$content);
		
		$fileName="file/yf/会计_中级_经济法.list";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		$handle=fopen($fileName,"a");
		fwrite($handle,$content);
		fclose($handle);
		
		$arr2 = explode("ex_id=",$content);
		$fileName="file/yf/会计_中级_经济法.downlist";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		$handle=fopen($fileName,"a");
		$ids = '';
		for($i=1;$i<count($arr2);$i++){
			$arr3 = explode("&ef_id",$arr2[$i]);	
			$ids .= $arr3[0].",";		
			fwrite($handle,"http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=6&ex_id=".$arr3[0]."\n");
		}
		$ids = substr($ids,0,strlen($ids)-1);
		fclose($handle);
		
		$fileName="file/yf/会计_中级_经济法.ids";
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
		url: \"wls.php?controller=install_yf_zjkjjjf&action=downLoadPaper2&id=\"+arr[index],
		success: function(msg){
			$(\"#console\").html($(\"#console\").html()+\"<br/>下载试卷\"+arr[index]);
			$(\"#index\").html(arr[index]+':'+arr.length);
			index ++;
			readPaper(index);
		}
	});
}
readPaper();
</script>
</head>
<body>
<div id='index'><div>
<div id=\"console\"><div>
</body>
</html>
		";
		echo $html;		
	}
	
	public function downLoadPaper2(){
		$fileName="file/yf/经济类/模拟题试卷/中级会计/经济法/6_".$_REQUEST['id'].".html";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		if(file_exists($fileName)){
			return;
		}else{
			$data=file_get_contents("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=6&ex_id=".$_REQUEST['id']);
//			$data = mb_convert_encoding($data,'UTF-8','GBK');
			file_put_contents($fileName,$data);
		}
	}


	public function readContent($path=null){
		if(isset($_REQUEST['path']) && $path==null)$path = $_REQUEST['path'];
		if($path==null)$path = "E:/Projects/WEBS/PHP/Discuz_7_2/upload/plugins/wls/file/yf/经济类/模拟题试卷/中级会计/经济法/6_".$_REQUEST["id"].".html";
		$path = mb_convert_encoding($path,'GBK','UTF-8');
		$content = file_get_contents($path);
//		$content = implode("\n", $content);
		$content = str_replace("DISPLAY: none","",$content);
		$content = str_replace("A. ","A) ",$content);
		$content = str_replace("B. ","B) ",$content);
		$content = str_replace("C. ","C) ",$content);
		$content = str_replace("D. ","D) ",$content);
		$content = str_replace("<a id=\"donw\" href=\"","",$content);

		$this->content = $content;
		echo ($content);

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

//		$this->savePaper();
		$this->getQuestions();
		$this->getRead();
//		$this->saveQuestion();
		$this->getImages();
//		$this->updatePaper();
//		echo json_encode(
//			array(
//				'id'=>$this->paper['id']
//			)
//		);
	}

	public function getImages(){
		$arr = explode("src=\"tp",$this->content);
		$arr2 = array();
		for($i=1;$i<count($arr);$i++){
			$p1 = strpos($arr[$i],"\"");
			$data = substr($arr[$i],0,$p1);
			$arr2[] = $data;
		}

		$fileName="E:/Projects/WEBS/PHP/Discuz_7_2/upload/plugins/wls/file/yf/".$this->paper['id']."_images.downlist";
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
			$url = fgets($f);			

			$ext=strrchr($url,"/");
			$filename="file/images/tp/gwy".$ext;			
			
			if(file_exists(trim($filename))){
				return;
			}else{
				$img=file_get_contents(trim($url));
				file_put_contents(trim($filename),$img);
			}			
		}
		fclose($f);
	}
	
	public function read2(){
		$html = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"libs/DWZ/javascripts/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var index = 1;
var readPaper = function(id){
	$.ajax({
		url: \"wls.php?controller=install_xznlcs&action=read&path=E:/TDDOWNLOAD/yf/%B9%FA%BC%D2%B9%AB%CE%F1%D4%B1_%B9%AB%B9%B2%BB%F9%B4%A1%D6%AA%CA%B6/examcontext(\"+id+\").jsp\",
		success: function(msg){
			var obj = jQuery.parseJSON(msg);
			$(\"#console\").html($(\"#console\").html()+\"<br/>导入试卷\"+id);
			readImages(obj.id);
		}
	});
}
var readImages = function(id){
	$.ajax({
		url: \"wls.php?controller=install_xznlcs&action=downloadImages&path=file/yf/\"+id+\"_images.downlist\",
		success: function(msg){
			//var obj = jQuery.parseJSON(msg);
			$(\"#console\").html($(\"#console\").html()+\"<br/>导入图片\"+id);
			if(index==11)return;
			index++;
			readPaper(index);
		}
	});
}
readPaper(index);
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
			$title = $this->format($title);
				
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
		print_r($this->ques);
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

	public function savePaper(){

		$data = array(
			'title' => '国家公务员考试'.rand(100,200),
			'title_quiz_type' => '行政能力',
			'description' => '',
			'creator' => 'admin',
			'publisher' => 'admin',
			'date_created' => date('Y-m-d'),
			'difficulty'=>'1',
		);

		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "select id from ".$pfx."wls_quiz_type where title = '".$data['title_quiz_type']."' ";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$data['id_quiz_type'] = $temp['id'];

		$this->paper = $data;

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_quiz_paper (".$keys.") values ('".$values."')";
		mysql_query($sql,$conn);
		$this->paper['id'] = mysql_insert_id($conn);
	}
	
	/**
	 * 跟新试卷的信息
	 * 主要包括 题目总数,子题目总数,试卷价值等等
	 * */
	public function updatePaper(){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$ids = '';
		for($i=0;$i<count($this->mainques);$i++){
			$ids .= $this->mainques[$i].",";
		}
		$ids = substr($ids,0,strlen($ids)-1);

		$ids2 = '';
		for($i=0;$i<count($this->subques);$i++){
			$ids2 .= $this->subques[$i].",";
		}
		$ids2 = substr($ids2,0,strlen($ids2)-1);
		$sql = "update ".$pfx."wls_quiz_paper set
			count_quetions = '".count($this->mainques)."' ,
			count_subquestions = '".count($this->subques)."' ,
			subquestions = '".$ids2."',
			islisten = 1,
			questions = '".$ids."',
			price_money = 5,
			price_score = 5,
			rank = 1,
			difficulty = 4	
			
			where id = ".$this->paper['id'].";
		";
		mysql_query($sql,$conn);
	}
}