<?php
include_once dirname(__FILE__).'/../model/subject.php';
include_once dirname(__FILE__).'/../model/quiz/paper.php';
include_once dirname(__FILE__).'/../model/quiz/log.php';
include_once dirname(__FILE__).'/../model/user.php';

class subject extends wls{

	private $m = null;

	function subject(){
		parent::wls();
		$this->m = new m_subject();
	}

	public function getList(){
		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $this->m->getList($page,$pagesize);
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function importAll(){
		echo '<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					'.$this->lang['privilageImportWarning'].'<br/><br/>
					'.$this->lang['importExcel'].'
					<form action="wls.php?controller=subject&action=saveImportAll" method="post"
					enctype="multipart/form-data">
						<label for="file">'.$this->lang['ExcelFilePath'].'</label>
						<input type="file" name="file" id="file" />
						<br />
						<input type="submit" name="submit" value="'.$this->lang['submit'].'"  />
					</form>
				</body>
			</html>		
		';
	}

	public function saveImportAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("190701",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to subject::saveImportAll ");
		}
		
		if ($_FILES["file"]["error"] > 0){
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}else{
			$file = $this->c->filePath."upload/". $_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],$file);
			$this->m->create();
			$this->m->importAll($file);
		}
		echo 'success';
	}

	public function saveUpdate(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("190704",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to subject::saveUpdate ");
		}
		
		$data = array(
			'id'=>$_POST['id'],
		$_POST['field']=>$_POST['value']
		);
		if($this->m->update($data)){
			echo "success";
		}else{
			echo "fail";
		}
	}

	public function exportAll(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("190702",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to subject::exportAll ");
		}
		
		$file = $this->m->exportAll();
		echo "<a href='".$this->c->filePath.$file."'>".$this->lang['download']."</a>";
	}

	public function delete(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("190703",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to subject::delete ");
		}
		
		if($this->m->delete($_POST['id'])){
			echo 'success';
		}else{
			echo 'fail';
		}
	}

	public function getPaperList(){		
		$paper = new m_quiz_paper();

		$page = 1;
		if(isset($_POST['start']))$page = ($_POST['start']+$_POST['limit'])/$_POST['limit'];
		$pagesize = 15;
		if(isset($_POST['limit']))$pagesize = $_POST['limit'];
		$data = $paper->getList($page,$pagesize,array('id_level_subject'=>array(array('=',$_REQUEST['id_level_subject'])) ));
		$data['totalCount'] = $data['total'];
		echo json_encode($data);
	}

	public function getMyQuizLine(){		
		$log = new m_quiz_log();
		$userObj = new m_user();
		$user = $userObj->getMyInfo();
		$search = array('id_user'=>$user['id']);
		if(isset($_REQUEST['id_level_subject_']) && $_REQUEST['id_level_subject_']!=''){
			$search['id_level_subject'] = $_REQUEST['id_level_subject_'];
		}
		$data = $log->getList(1,100,$search);
		$data = $data['data'];
		$arr = array();
		for($i=0;$i<count($data);$i++){
			$arr [] = array(
				'index' => $i+1,
				'proportion'=>floor((100*$data[$i]['count_right']) /($data[$i]['count_right'] + $data[$i]['count_wrong'])) 
			);
		}
		header("content-type: text/xml"); 
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<settings>
	<background>
	    <color>#EEEEEE</color>
	    <alpha>100</alpha>
	    <border_color>#999999</border_color>
	    <border_alpha>90</border_alpha>
  	</background>
	<values>
	  	<y_left> 
	  		<max>100</max>
	  		<min>0</min>
	  	</y_left>  
  	</values>  
	<plot_area>  
		<margins>                                                
			<left>30</left>                                          
			<top>10</top>                                             
			<right>0</right>                                        
			<bottom>0</bottom>                                       
		</margins>
 		<legend>
			<enabled>false</enabled>
		</legend> 
	</plot_area>
	<graphs>
		<graph gid="1">   
		 	<axis>left</axis>
		 	<color>#999999</color>                                                            
			<fill_alpha>50</fill_alpha>             
     	</graph>  	
	</graphs>   
	<data> 
		<chart>
			<series>';
		if(count($arr)>0){
			
	
		for($i=0;$i<count($arr);$i++){
$xml .= '		<value xid="'.$i.'">'.$arr[$i]['index'].'</value>';
		}
$xml .='	</series>
			<graphs>
				<graph gid="1">';
		for($i=0;$i<count($arr);$i++){
$xml .= '			<value xid="'.$i.'">'.$arr[$i]['proportion'].'</value>';
		}
		}else{
$xml .= '		<value xid="1">1</value>
			</series>
			<graphs>
				<graph gid="1">
					<value xid="1">1<value>
			';
		}
$xml .= '		</graph>
			</graphs>
			<guides>	                                   
	 			<guide>                                     
	   				<start_value>60</start_value>               
	   				<title>'.$this->lang['examPassLine'].'60</title>                           
	   				<color>#00CC00</color>                             
	   				<inside>true</inside>                         
	 			</guide>  
			</guides>	
		</chart>
	</data>
</settings>';
		echo $xml;
	}

	public function add(){
		$userObj = new m_user();
		if($userObj->checkMyaccess("190705",false)==false){
			$this->error("Attack! on ".date('Y-m-d H:i:s').",from ".$_SERVER['REMOTE_ADDR'].",try to subject::add ");
		}
		
		sleep(1);
		$id = $this->m->insert($_POST);
		echo $id;
	}
}
?>