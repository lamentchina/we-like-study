<?php 
include_once "tools.php";
//This file must be removed after installation.
if(!isset($_REQUEST['post'])){
?>
<html>
<body>
<form action='install.php?post=true&magic_test=[{"a":"b"},{"a":"b1"}]' method="post">
Database Host <input name="DB_HOST" /> <br/>
Database Name  <input name="DB_NAME" /> <br/>
Database Username <input name="DB_UNM" /> <br/>
Database Password  <input name="DB_PWD" /> <br/>
Mode 	<select name="MODE" >
			<option value="WLS">WLS</option>
			<option value="BAIDU">BaiduCloud</option>
			<option value="DZX">Discuzx 2.5/2.0</option>
			<option value="JOOMLA">Joomla 3.0</option>
			<option value="DEDE">DeDeCMS 5.7</option>
			<!-- option value="PHPWIND">PHPWIND9</option -->
		</select> <br/>
Language   	<select name="IL8N" >
			<option value="zh-cn">简体中文</option>
			<option value="en">English</option>
		</select> <br/>
<input type="submit">
</form>
</body>
</html>
<?php 
}else {
    $magic_test = json_decode2($_REQUEST['magic_test'],true);
    if(count($magic_test)!=2)die("modify your php.ini and disable the magic ");
    $DB_HOST = $_POST['DB_HOST'];
    $DB_NAME = $_POST['DB_NAME'];
    $DB_UNM = $_POST['DB_UNM'];
    $DB_PWD = $_POST['DB_PWD'];
    $MODE = $_POST['MODE'];
    $IL8N = $_POST['IL8N'];
    
	
    if($MODE=='DZX'){
        include_once '../../config/config_global.php';
        
        $DB_NAME = $_config['db'][1]['dbname'];
        $DB_UNM = $_config['db'][1]['dbuser'] ;
        $DB_PWD = $_config['db'][1]['dbpw'];
        $DB_HOST = $_config['db'][1]['dbhost'];
        unset($_config);
    }
    if($MODE=='JOOMLA'){
        include_once '../../configuration.php';
        $obj = new JConfig();
        
        $DB_NAME = $obj->db;
        $DB_UNM = $obj->user;
        $DB_PWD = $obj->password;
        $DB_HOST = $obj->host;
        unset($obj);
    }
    if($MODE=='DEDE'){
        include_once '../../data/common.inc.php';
        
        $DB_NAME = $cfg_dbname;
        $DB_UNM = $cfg_dbuser;
        $DB_PWD = $cfg_dbpwd;
        $DB_HOST = $cfg_dbhost;
    }  
    if($MODE=='BAIDU'){
        $DB_UNM = getenv('HTTP_BAE_ENV_AK');
        $DB_PWD = getenv('HTTP_BAE_ENV_SK');
        $DB_HOST = getenv('HTTP_BAE_ENV_ADDR_SQL_IP').":".getenv('HTTP_BAE_ENV_ADDR_SQL_PORT');
    } 	
    
    $file = "../config.xml.lock";
    if(!is_writable($file)){
        echo $file." is not writable, change it's mode to 777";
        exit();
    }
    $fp = fopen($file, 'r');
    $arr = array();
    $num = 1;
    while(!feof($fp)){
    	$line = trim(fgets($fp));

    	if($num == 3){
    	    $line = "<IL8N ID=\"IL8N\">".$IL8N."</IL8N>";
    	}
    	if($num == 4){
    	    $line = "<DATABASE ID=\"DB_NAME\">".$DB_NAME."</DATABASE>";
    	}
    	if($num == 5){
    	    $line = "<DATABASE ID=\"DB_UNM\">".$DB_UNM."</DATABASE>";
    	}
    	if($num == 6){
    	    $line = "<DATABASE ID=\"DB_PWD\">".$DB_PWD."</DATABASE>";
    	}
    	if($num == 7){
    	    $line = "<DATABASE ID=\"DB_HOST\">".$DB_HOST."</DATABASE>";
    	}
    	if($num == 8){
    	    $line = "<MODE ID=\"MODE\">".$MODE."</MODE>";
    	}
    	
    	$arr[] = $line;
    	$num++;
    }
    fclose($fp);
    $s = implode("\r\n", $arr)."\r\n";
    file_put_contents('../config.xml.lock', $s);    

	$conn =mysql_connect($DB_HOST,$DB_UNM,$DB_PWD);
	if($conn==false){
        echo "Cant't connect to the Database";
        exit();
	}
	mysql_select_db($DB_NAME,$conn);
	$res = mysql_query("show variables like 'character_set_database'");
	$temp = mysql_fetch_array($res);	
	$charset = $temp['1'];
	//mysql_query("SET NAMES '".$charset."'");
	
    $filename="../sql/basic_create.sql";
    $sql = file( $filename );
    $sql = implode("\n", $sql);    
    $arr = explode(";",$sql);
    
    for($i=0;$i<count($arr)-1;$i++){
        $res = mysql_query($arr[$i],$conn);
        if($res==false){
            echo $filename." ".mysql_error($conn);
            exit();
        }
    }
    
    $filename="../sql/exam_create.sql";
    $sql = file( $filename );
    $sql = implode("\n", $sql);    
    $arr = explode(";",$sql);
    for($i=0;$i<count($arr)-1;$i++){
        $res = mysql_query($arr[$i],$conn);
        if($res==false){
            echo $filename." ".mysql_error($conn);
            exit();
        }       
    }
    
    header('Content-type: text/html; charset='.$charset);
    $filename="../sql/".$IL8N."/basic_data.sql";
    $sql = file( $filename );
    $sql = implode("\n", $sql);    
    $arr = explode(");",$sql);
   
    for($i=0;$i<count($arr)-1;$i++){
        $sql = $arr[$i].")";
        $res = mysql_query($sql,$conn);
        if($res==false){
            echo $filename." ".mysql_error($conn)." ".$sql;
            exit();
        }  
    }     

    $filename="../sql/".$IL8N."/exam_data.sql";
    $sql = file( $filename );
    $sql = implode("\n", $sql);    
    $arr = explode(");",$sql);
    for($i=0;$i<count($arr)-1;$i++){
        $sql = $arr[$i];
        $res = mysql_query($sql.");",$conn);
        if($res==false){
            echo $filename." ".mysql_error($conn)." ".$sql;
            exit();
        }
    }     
    
?>
<a href='../html/desktop.html'>Front page</a>
<?php 
}
?>