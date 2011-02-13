<?php
include_once dirname(__FILE__).'/../../yf.php';

class install_yf_gwy_ggjczs extends install_yf{

	public $path = "E:/TDDOWNLOAD/Discuz_X_12_25/upload/source/plugin/wls3/file/yf/公务员类/模拟题试卷/国家公务员/公共基础知识/";
	public $url = "http://www.yfzxmn.cn/com/left/left.jsp?so_id=18&su_id=5";

	public function readList(){
		$content = file($this->url);
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("\n","",$content);

		$fileName= $this->path."menu.txt";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		if(file_exists($fileName)){

		}else{
			$handle=fopen($fileName,"a");
			fwrite($handle,$content);
			fclose($handle);
		}

		$arr2 = explode("ex_id=",$content);
		$fileName=$this->path."xunlei.downlist";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		if(file_exists($fileName)){		
			
		}else{
			$handle=fopen($fileName,"a");
			$ids = '';
			for($i=1;$i<count($arr2);$i++){
				$arr3 = explode("&ef_id",$arr2[$i]);
				$ids .= $arr3[0].",";
				fwrite($handle,"http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=5&ex_id=".$arr3[0]."\n");
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$this->ids = $ids;
			fclose($handle);
		}

		$fileName=$this->path."ids.txt";
		$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
		if(file_exists($fileName)){
			$this->ids = file_get_contents($fileName);
		}else{
			$handle=fopen($fileName,"a");
			fwrite($handle,$ids);
			fclose($handle);
		}

		$this->readPapers();
	}

	function install_yf_gwy_ggjczs(){
		$this->type = 'gwy_ggjczs';
		$this->path = $this->path.'5_';
	}

	public function readPaper(){
		header("Content-type: text/html; charset=UTF-8");
//		include_once dirname(__FILE__).'/../../../model/quiz/paper.php';
//		$obj = new m_quiz_paper();
//		$obj->create();
//		include_once dirname(__FILE__).'/../../../model/question.php';
//		$obj = new m_question();
//		$obj->create();
		include_once 'free/model/quiz/paper/yf/gwy/ggjczs.php';
		
		$m = new m_quiz_paper_yf_gwy_ggjczs();
		$m->path = $this->path.$_REQUEST['id'].'.html';
		$m->yfnum = $_REQUEST['id'];
		$m->path = mb_convert_encoding($m->path,'GBK','UTF-8');
		
		$filename = $this->path.$_REQUEST['id'].'.html';
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		if(!file_exists($filename)){
			$content = file("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=5&ex_id=".$_REQUEST['id']);
			$content = implode("\n", $content);
			$handle=fopen($filename,"a");
			fwrite($handle,$content);
			fclose($handle);
		}
		$m->readFile();
//		$m->viewPaper();
		$m->getPaper();
		$m->getQuestions();
		$m->saveQuestions();

	}
}
?>