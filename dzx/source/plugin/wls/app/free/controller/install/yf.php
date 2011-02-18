<?php
class install_yf {

	public $ids = null;
	public $path = null;
	public $type = null;

	public function readPapers(){
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var arr = [".$this->ids."];
var index = 0;
var readPaper = function(){
	if(index==arr.length-1)return;
	$.ajax({
		url: \"wls.php?controller=install_yf_".$this->type."&action=readPaper&id=\"+arr[index],
		success: function(msg){
			//$(\"#console\").html($(\"#console\").html()+\"<br/>下载试卷\"+arr[index]);
			$(\"#index\").html(arr[index]+':'+arr.length+':'+index);
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

	public function downLoadAll(){
		header("Content-type: text/html; charset=utf-8");
		$url = "http://www.yfzxmn.cn/com/left/left.jsp?so_id=".$_REQUEST['so']."&su_id=".$_REQUEST['su'];
		$folder = "";
		if(isset($_REQUEST['folder'])){
			$folder = str_replace("_","/",$_REQUEST['folder'])."/";
		}

		$content = file($url);
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("\n","",$content);

		$menuStr = $content;
		$menuStr = str_replace("<img src=/icon/CDefault.gif width=20 >","",$menuStr);
		$menuStr = str_replace("<img src=/icon/CFolder.gif width=20 >","",$menuStr);
		$menuStr = str_replace("<font style=\"cursor:hand;\" onClick=","",$menuStr);
		$menuStr = str_replace("(","",$menuStr);
		$menuStr = str_replace("'","",$menuStr);
		$menuStr = str_replace("this)>","",$menuStr);
		$arr = explode("loadid",$menuStr);
		if($arr==false || count($arr)<2){
			$data = array(
				'folderLeaf'=>1
			);
			echo json_encode($data);
			exit();
		}


		$data = array();
		for($i=1;$i<count($arr);$i++){
			$arr2 = explode(",",$arr[$i]);
			$arr3 = explode("</font>",$arr2[3]);
			$item = array(
				 'su'=>$arr2[0]
				,'so'=>$arr2[1]
				,'name'=>$arr3[0]
				,'folder'=>$folder.$arr3[0]."_"
			);
			$data[] = $item;
			$path = dirname(__FILE__);
			mkdir(mb_convert_encoding($path.$folder.$arr3[0],'GBK','UTF-8'),0777);
		}
		$data = array(
			'data'=>$data,
			'folderLeaf'=>0
		);
		echo json_encode($data);
	}


	public function makeHtml($str,$path,$su,$ex){

	}

	public function downLoadAllView(){
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var downLoadAll = function(so,su,folder){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_yf&action=downLoadAll',
		data: {so:so,su:su,folder:folder},
		success: function(msg){
			var obj = jQuery.parseJSON(msg);
			if(obj.folderLeaf==1){
				
			}else{
				for(var i=0;i<obj.data.length;i++){
					downLoadAll(obj.data[i].so,obj.data[i].su,obj.data[i].folder);
				}
			}
		}
	});
}
downLoadAll(0,14,'');
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
	
	public function downLoadUrl2Path(){
		$url = $_REQUEST['url'];
		$path = $_REQUEST['path'];
		
	}
}
?>