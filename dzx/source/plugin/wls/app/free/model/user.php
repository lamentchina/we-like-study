<?php
/**
 * User's operations.
 * It's associated with the databast table wls_user
 * */
class m_user extends wls implements dbtable{

	public $phpexcel;
	public $id = null;

	/**
	 * Insert one row into the database's table
	 *
	 * @param $data Array , the keys must fit the database table's columns
	 * @return $id
	 * */
	public function insert($data){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$keys = array_keys($data);
		$keys = implode(",",$keys);
		$values = array_values($data);
		$values = implode("','",$values);
		$sql = "insert into ".$pfx."wls_user (".$keys.") values ('".$values."')";

		mysql_query($sql,$conn);
		return mysql_insert_id($conn);
	}

	/**
	 * Delete one or more rows by id. Only by id!
	 *
	 * @param $ids Every table has this column. 
	 * @return bool
	 * */
	public function delete($ids){
		$pfx = $this->c->dbprefix;
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
		$pfx = $this->c->dbprefix;
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
		try{
			mysql_query($sql,$conn);
			return true;
		}catch (Exception $ex){
			return false;
		}
	}

	/**
	 * Create this table.
	 * If it's already exists, it would be droped first.
	 *
	 * @return bool
	 * */
	public function create(){

		$conn = $this->conn();
		$pfx = $this->c->dbprefix;

		$sql = "drop table if exists ".$pfx."wls_user;";
		mysql_query($sql,$conn);
		$sql = "
			create table ".$pfx."wls_user(
				 id int primary key auto_increment	
				
				,username varchar(200) unique
				,password varchar(200) not null
				,money int default 0
				,credits int default 0
				
				,name varchar(200) 
				,sex varchar(200) default ''
				,birthday datetime default '1999-09-09'
				,qq varchar(200) default '0'
				,photo varchar(200) 
			
			) DEFAULT CHARSET=utf8;
			";
		mysql_query($sql,$conn);
		return true;
	}

	/**
	 * Import an Excel file into the user's database table
	 * But the Excel must fit some ruls.
	 *
	 * @param $path Excel Path
	 * @return bool
	 * */
	public function importExcel($path){
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPReader->setReadDataOnly(true);
		$this->phpexcel = $PHPReader->load($path);

		$currentSheet = $this->phpexcel->getSheetByName('user');
		$allRow = array($currentSheet->getHighestRow());		
	
		$keys = array();
		for($i='A';$i<=$allColmun;$i++){
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['username']){
				$keys['username'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['password']){
				$keys['password'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['money']){
				$keys['money'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['photo']){
				$keys['photo'] = $i;
			}
			if($currentSheet->getCell($i."2")->getValue()==$this->lang['credits']){
				$keys['credits'] = $i;
			}
		}		
		
		$data = array();
		for($i=3;$i<=$allRow[0];$i++){
			$data = array(
				'username'=>$currentSheet->getCell($keys['username'].$i)->getValue(),
				'password'=>$currentSheet->getCell($keys['password'].$i)->getValue(),				
				'money'=>$currentSheet->getCell($keys['money'].$i)->getValue(),
				'credits'=>$currentSheet->getCell($keys['credits'].$i)->getValue(),
				'photo'=>$currentSheet->getCell($keys['photo'].$i)->getValue(),
			);
			$this->insert($data);
		}
	}

	/**
	 * Export an Excel file , with all the user data.
	 * Do this when you want to upgrade or move your site ;
	 * The Excel file could also be importted to another site.
	 *
	 * @return $path filepath
	 * */
	public function exportExcel(){
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel.php';
		include_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/IOFactory.php';
		require_once $this->c->libsPath.'phpexcel/Classes/PHPExcel/Writer/Excel5.php';
		$objPHPExcel = new PHPExcel();
		$data = $this->getList(1,1000);
		$data = $data['data'];

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('user');

		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->lang['username']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $this->lang['password']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $this->lang['money']);
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $this->lang['credits']);
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $this->lang['photo']);

		$index = 2;
		for($i=0;$i<count($data);$i++){
			$index ++;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$index, $data[$i]['username']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$index, $data[$i]['password']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$index, $data[$i]['money']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$index, $data[$i]['credits']);

			$objPHPExcel->getActiveSheet()->setCellValue('F'.$index, $data[$i]['photo']);
		}
		$objStyle = $objPHPExcel->getActiveSheet()->getStyle('E2');
		$objStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->duplicateStyle($objStyle, 'E2:E'.(count($data)+1));
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "download/".date('YmdHis').".xls";
		$objWriter->save($this->c->filePath.$file);
		return $file;
	}

	public function cumulative($column){}

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
		$pfx = $this->c->dbprefix;
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
		return $_SESSION['wls_user'];
	}

	/**
	 * Get the current user's information. 
	 * TODO It should depand on database's session
	 *
	 * @param $id If it's null , will return the current user's info.
	 * @param $resetSession To reset the current user's session ? 
	 * */
	public function getInfo(){
		
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
		$pfx = $this->c->dbprefix;
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
		
		//Get the privileges , It's a little complex. 
		//First , get the user's group info , 
		//than get the privileges info from the group info  				
		include_once dirname(__FILE__).'/user/privilege.php';
		$o = new m_user_privilege();
		$d = $o->getListForUser($data['username']);
		$ids = '';
		$privileges = array();
		for($i=0;$i<count($d);$i++){
			if($d[$i]['checked']=='1'){
				$ids .= $d[$i]['id_level'].",";
				$privileges[$d[$i]['id_level']] = $d[$i]['money'];
			}
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$data['privilege'] = $ids;
		$data['privileges'] = $privileges;

		//One user can belong to more than two groups.
		//And one user at least belong to one group.
		include_once dirname(__FILE__).'/user/group.php';
		$o = new m_user_group();
		$d = $o->getListForUser($data['username']);
		$ids = '';
		for($i=0;$i<count($d);$i++){
			if($d[$i]['checked']=='1')$ids .= $d[$i]['id_level'].",";
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$data['group'] = $ids;

		//How many subjects do this user participed?
		include_once dirname(__FILE__).'/subject.php';
		$o = new m_subject();
		$d = $o->getListForUser($data['username']);
		$ids = '';
		for($i=0;$i<count($d);$i++){
			if($d[$i]['checked']=='1')$ids .= $d[$i]['id_level'].",";
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$data['subject'] = $ids;		
		if(!isset($_SESSION)){
			session_start();
		}	
		$_SESSION['wls_user'] = $data;
		
		return $data;
	}

	/**
	 * Export some user's Transcripts
	 *
	 * @param $id If $id is null , will export the current user's stuff.
	 * */
	public function exportTranscripts($id){}

	/**
	 * Import one student's transcripts
	 *
	 * @param $id Get the current user's stuff if id is null
	 * */
	public function importTranscripts($id){}

	public function updateGroup($username,$ids_group){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$sql = "delete from ".$pfx."wls_user_group2user where username = '".$username."' ;";
		mysql_query($sql,$conn);
		$arr = explode(",",$ids_group);

		include_once dirname(__FILE__).'/user/group.php';
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
	 * @param $privilege It's a num ,it's level id
	 * */
	public function checkMyPrivilege($privilege){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();

		$user = $this->getMyInfo();
		$privileges = $user['privilege'];
		$privileges = explode(",",$privileges);

		if(in_array($privilege,$privileges)){
			if($user['money']>$user['privileges'][$privilege]){
				$sql = "update ".$pfx."wls_user set money = money - ".$user['privileges'][$privilege]." where id = ".$user['id'];
				mysql_query($sql,$conn);
				if(!isset($_SESSION))session_start();
				$_SESSION['wls_user']['money'] -= $user['privileges'][$privilege];

				if($this->c->cmstype!=''){//Synchro money
					$obj = null;
					eval("include_once dirname(__FILE__).'/integration/".$this->c->cmstype.".php';");
					eval('$obj = new m_integration_'.$this->c->cmstype.'();');
					$obj->synchroMoney($user['username']);
				}
				return true;
			}else{
				return false;
			}
		}else{
			
			return false;
		}
	}

	/**
	 * This function will cost a lot of memory.
	 * TODO the algorithm should be upgraded
	 * 
	 * @return $data An array.
	 * */
	public function getMyMenu(){
		$me = $this->getMyInfo();
		
		$username = $me['username'];
		include_once dirname(__FILE__).'/user/privilege.php';
		$obj = new m_user_privilege();
		$data = $obj->getListForUser($username);

		$data2 = array();
		for($i=0;$i<count($data);$i++){
			if($data[$i]['ismenu']==1){
				if($data[$i]['checked']==true || $data[$i]['checked']=='true' || $data[$i]['checked']==1){
					$data[$i]['type'] = 'menu';
					$data2[] = $data[$i];
				}
			}
		}

		$data = $this->t->getTreeData(null,$data2);
		for($i=0;$i<count($data);$i++){
			//How many subjects do this user participate in?
			if($data[$i]['id_level']=='11'){
				include_once dirname(__FILE__).'/subject.php';
				$obj = new m_subject();
				$data_ = $obj->getListForUser($username);

				if(count($data_)>0){
					$data__ = array();
					for($ii=0;$ii<count($data_);$ii++){
						$data_[$ii]['type'] = 'subject';
						$data_[$ii]['id_level_s'] = $data_[$ii]['id_level'];
						
						$data_[$ii]['id_level'] = '11'.$data_[$ii]['id_level'];
						if($data_[$ii]['checked']==1){
							$data_[$ii]['ismenu'] = 1;
							$data__[] = $data_[$ii];
						}
					}
					$subject = $this->t->getTreeData('11',$data__);
						
					$arr = array();
					if(isset($data[$i]['children']) && count($data[$i]['children'])>0){
						$arr = $data[$i]['children'];
					}
					$data[$i]['children'] = $subject;

					if(count($arr)>0){
						$data[$i]['children'][] = array('text'=>'slide');
						for($ii=0;$ii<count($arr);$ii++){
							$data[$i]['children'][] = $arr[$ii];
						}
					}
				}
			}
			
			//How many groups do this user in?
			if($data[$i]['id_level']=='13'){
				include_once dirname(__FILE__).'/user/group.php';
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
					$subject = $this->t->getTreeData('13',$data__);
						
					$arr = array();
					if(isset($data[$i]['children']) && count($data[$i]['children'])>0){
						$arr = $data[$i]['children'];
					}
					$data[$i]['children'] = $subject;

					if(count($arr)>0){
						$data[$i]['children'][] = array('text'=>'slide');
						for($ii=0;$ii<count($arr);$ii++){
							$data[$i]['children'][] = $arr[$ii];
						}
					}
				}
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

		$this->t->treeMenuToDesktopMenu(null,$data);
		$data = $this->t->desktopMenu;
		return $data;
	}
	
	public function getMyMenuWithShortCut(){
		$menus = $this->getMyMenuForDesktop();

		$modules = array();
		$shortcut = array();
		$quickstart = array();
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
		
		return array(
			'menus'=>$menus
			,'modules'=>$modules
			,'quickstart'=>$quickstart
			,'shortcut'=>$shortcut
		);
	}
}
?>