<?php
include_once dirname(__FILE__).'/user/access.php';
include_once dirname(__FILE__).'/user/group.php';
include_once dirname(__FILE__).'/subject.php';

include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel.php';
include_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/../../../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

/**
 * User's operations.
 * It's associated with the databast table wls_user
 * */
class m_user extends wls implements dbtable,fileLoad{

	public $phpexcel;
	public $id = null;

	/**
	 * Insert one row into the database's table
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return $id
	 * */
	public function insert($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		
		if(!isset($data['accesses'])){
			$data['accesses'] = 'nothing';
			$data['groups'] = 'nothing';
			$data['subjects'] = 'nothing';
		}	
		$data['log_created'] = date('Y-m-d H:i:s');
		
		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		
		$sql = "select * from ".$pfx."wls_user where username = '".$data['username']."';";
		//echo $sql;exit();
		$res = mysql_query($sql,$conn);
		//echo $sql;
		$temp = mysql_fetch_assoc($res);
		if($temp===false){		
			$sql = "insert into ".$pfx."wls_user (".$keys.") values ('".$values."')";
			mysql_query($sql,$conn);
			$this->error($sql);
			return mysql_insert_id($conn);
		}else{
			echo $temp;
			$data['id'] = $temp['id'];
			$this->update($data);
		}
	}

	/**
	 * Delete one or more rows by id. Only by id!
	 *
	 * @param $ids Every table has this column. 
	 * @return bool
	 * */
	public function delete($ids){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user where id  in (".$ids.");";
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
	}

	/**
	 * Update one row into the database's table
	 * There must have id in $data
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return bool
	 * */
	public function update($data){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$id = $data['id'];
		unset($data['id']);
		$keys = array_keys($data);

		$sql = "update ".$pfx."wls_user set ";
		for($i=0;$i<count($keys);$i++){
			$sql.= $keys[$i]."='".$data[$keys[$i]]."',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " where id =".$id;
		
		$this->error($sql);
		mysql_query($sql,$conn);
	}

	/**
	 * Create this table.
	 * If it's already exists, it would be droped first.
	 *
	 * @return bool
	 * */
	public function create(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_user;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user(
				 id int primary key auto_increment	
				
				,username varchar(200) unique
				,password varchar(200) not null
				,money int default 0
				,credits int default 0
				,photo varchar(200) default ''
				
				,log_created datetime default '1987-03-18'			
				,log_lastlogin datetime default '1987-03-18'			
				,log_count_visit int default 0
				,log_ip_lastlogin varchar(200) default '127.0.0.1'
				
				,accesses text
				,subjects text
				,groups text
				
				,glossary_wrong int default 0
				,glossary_right int default 0 
				,glossary_level_passed int default 0
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		
		$sql = "drop table if exists ".$pfx."wls_user_columns;";
		mysql_query($sql,$conn);
		$sql = "create table ".$pfx."wls_user_columns (
			id int primary key auto_increment	
			,title varchar(200) 
			);";
		mysql_query($sql,$conn);
		
			$sql = "drop table if exists ".$pfx."wls_user_group2user;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group2user(
				
					 id int primary key auto_increment	
					,id_level_group varchar(200) default ''		
					,username varchar(200) default '' 		
								
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);
			$sql = "ALTER TABLE ".$pfx."wls_user_group2user ADD INDEX idx_u_g2u (id_level_group,username);";
			mysql_query($sql,$conn);
			
			
			$sql = "drop table if exists ".$pfx."wls_user_group2subject2teacher;";
			mysql_query($sql,$conn);
			$sql = "
				create table ".$pfx."wls_user_group2subject2teacher(
					 id int primary key auto_increment	
					,id_level_group varchar(200) default '0'		
					,id_level_subject varchar(200) default '0'	
					,id_user int default 0
				) DEFAULT CHARSET=utf8;
				";
			mysql_query($sql,$conn);

			$sql = "ALTER TABLE ".$pfx."wls_user_group2subject2teacher ADD INDEX idx_gsu (id_level_group,id_level_subject,id_user);";
			mysql_query($sql,$conn);
		
		return true;
	}
	
	public function importOne($path){}

	public function importAll($path){
		if($this->phpexcel==null){
			$objPHPExcel = new PHPExcel();
			$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
			$PHPReader->setReadDataOnly(true);
			$this->phpexcel = $PHPReader->load($path);

			$currentSheet = $this->phpexcel->getSheetByName($this->il8n['user']['user']);
			$allRow = array($currentSheet->getHighestRow());
			$allRow = $allRow[0];
			$allColmun = $currentSheet->getHighestColumn();
			$keysRow = 2;
		}else{
			$currentSheet = $this->phpexcel['currentSheet'];
			$allRow = intval($this->phpexcel['allRow']);
			$allColmun = $this->phpexcel['allColmun'];
			$keysRow = intval($this->phpexcel['keysRow']);
		}
	
		$keys = array();
		$extendColumns = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['user']['username']){
				$keys['username'] = $i;
			}else if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['user']['password']){
				$keys['password'] = $i;
			}else if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['user']['money']){
				$keys['money'] = $i;
			}else if($currentSheet->getCell($i.$keysRow)->getValue()==$this->il8n['user']['photo']){
				$keys['photo'] = $i;
			}else{
				$index = count($extendColumns);
				$extendColumns[] = array(
					'title'=> $currentSheet->getCell($i.$keysRow)->getValue()
					,'name'=>'column'.$index
				);
				$keys['column'.$index] = $i;				
			}
		}		
		
		if(count($extendColumns)!=0){
			$conn = $this->conn();
			$pfx = $this->cfg->dbprefix;

			for($i=0;$i<count($extendColumns);$i++){
				$sql ="alter table ".$pfx."wls_user add column column".$i." varchar(200) ;";
				mysql_query($sql,$conn); 
				$sql ="insert into ".$pfx."wls_user_columns (title) values(
					'".$extendColumns[$i]['title']."'
				) ";
				mysql_query($sql,$conn); 
			}			
		}		
		
		$datas = array();
		for($i=($keysRow+1);$i<=$allRow;$i++){
			$data = array(
				'username'=>$currentSheet->getCell($keys['username'].$i)->getValue(),
				'password'=>$currentSheet->getCell($keys['password'].$i)->getValue(),				
			);
			if(isset($keys['photo'])){
				$data['photo'] = $currentSheet->getCell($keys['photo'].$i)->getValue();
			}
			if(isset($keys['money'])){
				$data['money'] = $currentSheet->getCell($keys['money'].$i)->getValue();
			}	
			for($i2=0;$i2<count($extendColumns);$i2++){
				$data['column'.$i2] = $currentSheet->getCell($keys['column'.$i2].$i)->getValue();
			}		
			$this->insert($data);
			$datas[] = $data;
		}
		return $datas;
	}
	
	public function exportOne($path=null){}

	public function exportAll($path=null){
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->il8n['user']['user']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1',  $this->cfg->siteName.'_'.$this->il8n['user']['exportFile']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('A2', $this->il8n['user']['username']);
		$objPHPExcel->getActiveSheet()->setCellValue('B2', $this->il8n['user']['password']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $this->il8n['user']['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2', $this->il8n['user']['credits']);
		$objPHPExcel->getActiveSheet()->setCellValue('E2', $this->il8n['user']['photo']);

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['username']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['password']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['money']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['credits']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$index, $data[$i]['photo']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('E2');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'E2:E'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$objWriter->save($this->cfg->filePath.$file);
		return $file;
	}

	/**
	 * Get a data list; It's normally used in grid and table work;
	 * Could also be used in to get a single data;
	 * 
	 * @param $page 
	 * @param $pagesize 
	 * @param $search Array . Search conditions here.
	 * @param $orderby 
	 * @return $array
	 * */
	public function getList($page=null,$pagesize=null,$search=null,$orderby=null,$columns="*"){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$where = " where 1 =1  ";

		if($search!=null){
			$keys = array_keys($search);
			for($i=0;$i<count($keys);$i++){
				if($keys[$i]=='type'){
					$where .= " and type in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='id'){
					$where .= " and id in (".$search[$keys[$i]].") ";
				}
				if($keys[$i]=='username'){
					$where .= " and username = '".$search[$keys[$i]]."' ";
				}
			}
		}
		if($orderby==null)$orderby = " order by id";
		$sql = "select ".$columns." from ".$pfx."wls_user ".$where." ".$orderby;
		$sql .= " limit ".($pagesize*($page-1)).",".$pagesize." ";

		$res = mysql_query($sql,$conn);
		if($res==false){
			return;
		}

		$arr = array();
		while($temp = mysql_fetch_assoc($res)){
			$arr[] = $temp;
		}

		$sql2 = "select count(*) as total from ".$pfx."wls_user ".$where;
		$res = mysql_query($sql2,$conn);
		$temp = mysql_fetch_assoc($res);
		$total = $temp['total'];

		return array(
			'page'=>$page,
			'data'=>$arr,
			'sql'=>$sql,
			'total'=>$total,
			'pagesize'=>$pagesize,
		);
	}
	
	public function getMyInfo(){
		if(!isset($_SESSION)){
			session_start();
		}
		if(isset($_SESSION['wls_user'])){
			return $_SESSION['wls_user'];
		}else{
			//die('Wrong'); TODO
		}		
	}

	/**
	 * Login and rest the session.
	 * Normally , the client-side would refresh the page.
	 * Because once logined , the current user's structur would change a lot
	 * 
	 * @param $username
	 * @param $password
	 * */
	public function login($username,$password=null){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();
		$sql = "select * from ".$pfx."wls_user where username = '".$username."';";
		$res = mysql_query($sql,$conn);
		$data = mysql_fetch_assoc($res);
		if($data==false){
			return false;
		}else{
			if( ($password!=null) && ($data['password']!=$password) ){
				return false;
			}
		}		
		unset($data['password']);
		
		if($data['accesses']=='nothing'){		
			//Get the accesss , It's a little complex. 
			//First , get the user's group info , 
			//than get the accesss info from the group info  						
			$o = new m_user_access();
			$d = $o->getListForUser($data['username']);
			//print_r($d);exit();
	
			$ids = '';
			for($i=0;$i<count($d);$i++){
				if($d[$i]['checked']=='1'){
					$ids .= $d[$i]['id_level'].",";
				}
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$data['accesses'] = $ids;
	
			//One user can belong to more than two groups.
			//And one user at least belong to one group.
			$o = new m_user_group();
			$d = $o->getListForUser($data['username']);
			$ids = '';
			for($i=0;$i<count($d);$i++){
				if($d[$i]['checked']=='1')$ids .= $d[$i]['id_level'].",";
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$data['groups'] = $ids;
	
			//How many subjects do this user participed?
			$o = new m_subject();
			$d = $o->getListForUser($data['username']);
			$ids = '';
			for($i=0;$i<count($d);$i++){
				if($d[$i]['checked']=='1')$ids .= $d[$i]['id_level'].",";
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$data['subjects'] = $ids;	
			
			$this->update(array(
				 'id'=>$data['id']
				,'subjects'=>$data['subjects']
				,'groups'=>$data['groups']
				,'accesses'=>$data['accesses']
			));
		}
		if(!isset($_SESSION)){
			session_start();
		}	
		$_SESSION['wls_user'] = $data;
		
		$onlineip= '127.0.0.1';
		if(isset($_SERVER['HTTP_CLIENT_IP']) ){
		     $onlineip=$_SERVER['HTTP_CLIENT_IP'];
		}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		     $onlineip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
		     $onlineip=$_SERVER['REMOTE_ADDR'];
		}
		
		$this->update(array(
			 'id'=>$data['id']
			,'log_ip_lastlogin'=>$onlineip
			,'log_lastlogin'=>date('Y-m-d H:i:s')
			,'log_count_visit'=>($data['log_count_visit']+1)
		));
		
		return $data;
	}

	public function updateGroup($username,$ids_group){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user_group2user where username = '".$username."' ;";
		mysql_query($sql,$conn);
		$sql = "update ".$pfx."wls_user set 
			accesses = 'nothing' ,
			subjects = 'nothing' ,
			groups  = 'nothing' 
			where username = '".$username."' ;";
		mysql_query($sql,$conn);		
		$arr = explode(",",$ids_group);

		$g = new m_user_group();

		for($i=0;$i<count($arr);$i++){
			$data = array(
				'id_level_group'=>$arr[$i],
				'username'=>$username
			);
			$g->linkUser($data);
		}
	}

	/**
	 * Check the current user have one permission or not.
	 * Check if he/she can do some actions.
	 * And money will be reduced if this action is 'expensive' 
	 * 
	 * @param $access It's a num ,it's level id
	 * */
	public function checkMyaccess($access,$withMoney=true){
		$pfx = $this->cfg->dbprefix;
		$conn = $this->conn();

		$user = $this->getMyInfo();
		$acceses = $user['accesses'];
		$acceses = explode(",",$acceses);
		
		
		$accessObj = new m_user_access();
		$accessdata = $accessObj->getList(1,500);
		$accessdata = $accessdata['data'];
		$access2 = array();
		for($i=0;$i<count($accessdata);$i++){
			$access2['p'.$accessdata[$i]['id_level']] = array(
				$accessdata[$i]['id_level'],
				$accessdata[$i]['icon'],
				$accessdata[$i]['name']
			);
		}
		
		if(in_array($access,$acceses)){
			if($withMoney){
				if(intval($user['money'])>intval($accessdata['p'.$access][1])){
					$sql = "update ".$pfx."wls_user set money = money - ".intval($accessdata['p'.$access][0])." where id = ".$user['id'];
					mysql_query($sql,$conn);
					if(!isset($_SESSION))session_start();
					$_SESSION['wls_user']['money'] -= intval($accessdata['p'.$access][0]);
	
					if($this->cfg->cmstype!='' && $user['username']!='guest'){//Synchro money
						$obj = null;
						eval("include_once dirname(__FILE__).'/integration/".$this->cfg->cmstype.".php';");
						eval('$obj = new m_integration_'.$this->cfg->cmstype.'();');
						$obj->synchroMoney($user['username']);
					}
					return true;					
				}else{
					return false;
				}
			}else{
				return true;
			}
		}else{
			return false;
		}
	}

	/**
	 * This function will cost a lot of memory.
	 * TODO the algorithm should be upgraded,
	 * It should be based on cache
	 * 
	 * @return $data An array.
	 * */
	public function getMyMenu(){
		$me = $this->getMyInfo();		
		$username = $me['username'];
		
		$obj = new m_user_access();
		$myAccessMenus = $obj->getListForUser($username);
		//print_r($myAccessMenus);exit();
		//filter the AccessMenus by 'checked=true'
		$myAccessMenus2 = array();
		for($i=0;$i<count($myAccessMenus);$i++){
			if($myAccessMenus[$i]['ismenu']==1){
				//Some php consider the 'true'!=1
				//And some mysql consider the same way
				if($myAccessMenus[$i]['checked']==true || $myAccessMenus[$i]['checked']=='true' || $myAccessMenus[$i]['checked']==1){
					$myAccessMenus[$i]['type'] = 'menu';
					$myAccessMenus2[] = $myAccessMenus[$i];
				}
			}
		}
		//print_r($myAccessMenus2);exit();
		unset($myAccessMenus);		
		
		//Transform the access from list to tree.
		//But the treeMenus is not onley formed by access, 
		//it also contains subject-list, recent-exam-list, myusergroup-list
		$accessTree = $this->tool->getTreeData(null,$myAccessMenus2);
		//print_r($accessTree);exit();
		for($i=0;$i<count($accessTree);$i++){
			//echo 2;exit();
			//How many subjects do this user participate in?
			if($accessTree[$i]['id_level']=='11' || $accessTree[$i]['id_level']=='30'){
				//echo 1;exit();
				$obj = new m_subject();
				$data_ = $obj->getListForUser($username);

				if(count($data_)>0){					
					$data__ = array();
					for($ii=0;$ii<count($data_);$ii++){
						unset($data_[$ii]['ids_level_knowledge']);
//						unset($data_[$ii]['icon']);
						$data_[$ii]['type'] = 'subject';
						$data_[$ii]['id_level_s'] = $data_[$ii]['id_level'];
						
						/* Copy all the subject into access
						 * Like: 
						 * Subject:
						 * 10		s1
						 * 11		s1
						 * 1101		s11
						 * 12		s3
						 * Change into
						 * 1110		s1
						 * 1111		s1
						 * 111101	s11
						 * 1112		s3
						 * 
						 * Because in access , the id '10' , '12' is already used
						 * */
						$data_[$ii]['id_level'] = '11'.$data_[$ii]['id_level'];
						if($data_[$ii]['checked']==1){
							$data_[$ii]['ismenu'] = 1;
							$data__[] = $data_[$ii];
						}
					}					
					//Transform the subject from list to tree
					$subject = $this->tool->getTreeData('11',$data__);
						
					//To fit the qWikiOffice-Menu structure
					//TODO it sucks!
					$arr = array();
					if(isset($accessTree[$i]['children']) && count($accessTree[$i]['children'])>0){
						$arr = $accessTree[$i]['children'];
					}
					$accessTree[$i]['children'] = $subject;
					$accessTree[$i]['leaf'] = false;
					//echo 123123123;
					//print_r($accessTree[$i]);exit();
					if(count($arr)>0){
						$accessTree[$i]['children'][] = array('text'=>'slide');
						for($ii=0;$ii<count($arr);$ii++){
							$accessTree[$i]['children'][] = $arr[$ii];
						}
					}
				}
			}
			
			//How many groups do this user in?
			if($accessTree[$i]['id_level']=='13'){				
				$obj = new m_user_group();
				$data_ = $obj->getListForUser($username);
				if(count($data_)>0){
					$data__ = array();
					for($ii=0;$ii<count($data_);$ii++){
						$data_[$ii]['type'] = 'group';
						$data_[$ii]['icon'] = 'groups';
						$data_[$ii]['isshortcut'] = '0';
						$data_[$ii]['isquickstart'] = '0';
						$data_[$ii]['description'] = '0';
						$data_[$ii]['id_level_g'] = $data_[$ii]['id_level'];
						
						//User group's menu id will be '11' sub group id.
						$data_[$ii]['id_level'] = '13'.$data_[$ii]['id_level'];
						if($data_[$ii]['checked']==1){
							$data_[$ii]['ismenu'] = 1;
							$data__[] = $data_[$ii];
						}
					}
					$subject = $this->tool->getTreeData('13',$data__);
						
					$arr = array();
					if(isset($accessTree[$i]['children']) && count($accessTree[$i]['children'])>0){
						$arr = $accessTree[$i]['children'];
					}
					$accessTree[$i]['children'] = $subject;

					if(count($arr)>0){
						$accessTree[$i]['children'][] = array('text'=>'slide');
						for($ii=0;$ii<count($arr);$ii++){
							$accessTree[$i]['children'][] = $arr[$ii];
						}
					}
				}
			}
		}
		return $accessTree;
	}
	
	public function removeNodeKey($data,$key){
		for($i=0;$i<count($data);$i++){
			unset($data[$i][$key]);
			if(isset($data[$i]['children'])){
				$data[$i]['children'] = $this->removeNodeKey($data[$i]['children'],$key);
			}
		}
		return $data;
	}

	/**
	 * Format the menu's structure to fit the qWikiOffice
	 * 
	 * @return $data Array
	 * */
	public function getMyMenuForDesktop(){
		$data = $this->getMyMenu();
		$this->treeMenuToDesktopMenu(null,$data);
		$data = $this->desktopMenu;

		return $data;
	}
	
	public function getMyMenuWithShortCut(){
		$me = $this->getMyInfo();
		$cacheFilePath = $this->cfg->filePath.'cache/user2qwiki/'.$me['id'].'.json';
		if(file_exists($cacheFilePath)){	
			$content = file( $cacheFilePath );		
			$content = implode("\n", $content);
			return json_decode($content,true);		
		}else{
			$menus = $this->getMyMenuForDesktop();
			$modules = array();
			$shortcut = array();
			$quickstart = array();
			$icons = array();
			for($i=0;$i<count($menus);$i++){
				$menuItem = array(
					'id'=>"id_".$menus[$i]['id_level'],
					'className'=>"class_".$menus[$i]['id_level'],
	          		"launcher"=>array(
	          			"text"=>$menus[$i]['text'],
	          			"tooltip"=>'<b>'.$menus[$i]['text'].'</b>',
	          		),
	          		"launcherPaths"=>array(
	          			"startmenu"=>$menus[$i]['startmenu']
	          		),
				);
				
				if(isset($menus[$i]['icon'])){
					$icons[$menus[$i]['icon']] =1;
					$menuItem['launcher']['iconCls'] = 'icon_'.$menus[$i]['icon'].'_16_16';
					$menuItem['launcher']['shortcutIconCls'] = 'icon_'.$menus[$i]['icon'].'_48_48';
				}
				if(isset($menus[$i]['description'])){
					$menuItem['launcher']['tooltip'] = '<b>'.$menus[$i]['description'].'</b>';
				}					
				$modules[] = $menuItem;
				if(isset($menus[$i]['isshortcut']) && $menus[$i]['isshortcut']==1){
					$shortcut[] = "id_".$menus[$i]['id_level'];
				}
				if(isset($menus[$i]['isquickstart']) && $menus[$i]['isquickstart']==1){
					$quickstart[] = "id_".$menus[$i]['id_level'];
				}					
			}
			
			$content = json_encode(array(				
				 'modules'=>$modules
				,'quickstart'=>$quickstart
				,'shortcut'=>$shortcut
			));
			$handle=fopen($cacheFilePath,"a");
			fwrite($handle,$content);
			fclose($handle);
			
			return array(				
				 'modules'=>$modules
				,'quickstart'=>$quickstart
				,'shortcut'=>$shortcut
			);
		}
	}
	
	public function getColumns(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = " select * from ".$pfx."wls_user_columns ";
		$res = mysql_query($sql,$conn);
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		return $data;
	}
	
	public function cleanCache(){
		$conn = $this->conn();
		$pfx = $this->cfg->dbprefix;

		$sql = " update ".$pfx."wls_user set  
		 
				accesses  = 'nothing'
				,subjects  = 'nothing'
				,groups  = 'nothing'  ";
		$res = mysql_query($sql,$conn);
		
		$this->tool->removeDir($this->cfg->filePath."cache/user2qwiki/");
		mkdir($this->cfg->filePath."cache/user2qwiki/", 0777);
	}
	
	//Store all the qWikiOffice-Menu here,it's used in the function below
	public $desktopMenu = array();

	/**
	 * To fit the qWikiOffice's menu rule.
	 * It's recursion
	 * */
	public function treeMenuToDesktopMenu($id=null,$data_all){
		if($id==null){
			$len = 2;
			//Level 1
			for($i=0;$i<count($data_all);$i++){
				if(strlen($data_all[$i]['id_level'])==$len){
					//It contains submenus
					if(isset($data_all[$i]['children'])){
						$data_all[$i]['menupath'] = '/';
						for($i2=0;$i2<count($data_all[$i]['children']);$i2++){
							$data_all[$i]['children'][$i2]['menupath'] = $data_all[$i]['menupath'].$data_all[$i]['text']."/";
						}
						//Get the submenus
						$this->treeMenuToDesktopMenu($data_all[$i]['id_level'],$data_all[$i]['children']);

						$data_all[$i]['startmenu'] = $data_all[$i]['menupath'].$data_all[$i]['text']."/";
						unset($data_all[$i]['children']);
						unset($data_all[$i]['menupath']);
						//Add this menu to qWikiOffice-Menu
						$this->desktopMenu[] = $data_all[$i];
					}else{//It's the root menu
						$data_all[$i]['startmenu'] = "/";
						unset($data_all[$i]['menupath']);
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
						//If it contains submenus
						if(isset($data_all[$i]['children'])){
							for($i2=0;$i2<count($data_all[$i]['children']);$i2++){
								//Add all the submenus to the current menuitem
								$data_all[$i]['children'][$i2]['menupath'] =  $data_all[$i]['menupath'].$data_all[$i]['text']."/";
							}
							$this->treeMenuToDesktopMenu($data_all[$i]['id_level'],$data_all[$i]['children']);

							$data_all[$i]['startmenu'] = $data_all[$i]['menupath'].$data_all[$i]['text']."/";
							unset($data_all[$i]['children']);
							unset($data_all[$i]['menupath']);
							if($data_all[$i]['type']!='subject'){
								$this->desktopMenu[] = $data_all[$i];
							}
						}else{
							$data_all[$i]['startmenu'] = $data_all[$i]['menupath'];
							unset($data_all[$i]['menupath']);
							$this->desktopMenu[] = $data_all[$i];
						}
					}
				}
			}
		}
	}
}
?>