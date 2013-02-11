<?php
$db_server = $_REQUEST['db_server'];
$db_username = $_REQUEST['db_username'];
$db_password = $_REQUEST['db_password'];
$db_name = $_REQUEST['db_name'];


$conn = mysql_connect($db_server,$db_username,$db_password,1,131072);
mysql_select_db($db_name,$conn);
mysql_query('SET NAMES UTF8');
mysql_query("DELIMITER ;",$conn);

$filename="../sql/create.txt";
$sql = file( $filename );
$sql = implode("\n", $sql);

$arr = explode(";",$sql);
for($i=0;$i<count($arr);$i++){
    mysql_query($arr[$i],$conn);
}

mysql_query("DELIMITER ;;",$conn);
$filename="../sql/proc_basic.txt";
$sql = file( $filename );
$sql = implode("\n", $sql);
$sql = str_replace("\n\n","\n",$sql);

$sql = str_replace("DELIMITER ;;", "", $sql);            
$sql = str_replace("DELIMITER ;" ,"", $sql);    
$sql = str_replace("`;", "`;;" , $sql);
$arr = explode(";;",$sql);
for($i=0;$i<count($arr);$i++){
    //echo $arr[$i]."<br/>";
    mysql_query($arr[$i],$conn);
}

$filename="../sql/proc_education.txt";
$sql = file( $filename );
$sql = implode("\n", $sql);
$sql = str_replace("\n\n","\n",$sql);
$sql = str_replace("DELIMITER ;;", "", $sql);            
$sql = str_replace("DELIMITER ;" ,"", $sql);    
$sql = str_replace("`;", "`;;" , $sql);
$arr = explode(";;",$sql);
for($i=0;$i<count($arr);$i++){
    //echo $arr[$i]."<br/>";
    mysql_query($arr[$i],$conn);
}
mysql_query("DELIMITER ;",$conn);

include_once '../libs/phpexcel/Classes/PHPExcel.php';
include_once '../libs/phpexcel/Classes/PHPExcel/IOFactory.php';
include_once '../libs/phpexcel/Classes/PHPExcel/Writer/Excel5.php';

$objPHPExcel = new PHPExcel();
$PHPReader = PHPExcel_IOFactory::createReader('Excel5');
$PHPReader->setReadDataOnly(true);
$obj = $PHPReader->load("../file/download/highschool/basic_parameter.xls");

mysql_query("truncate table basic_parameter ;",$conn);        
$currentSheet = $obj->getSheetByName("data");
$allRow = $currentSheet->getHighestRow();

$sql = "INSERT INTO basic_parameter(
	code,value,reference,remark
	) VALUES ";
for($i=2;$i<=$allRow;$i++){
    $code = $currentSheet->getCell("A".$i)->getValue();
    if($code=="")continue;
    //$code = str_replace("00", "", $code);
    $sql .="  (
	'".$code."'
	,'".$currentSheet->getCell("B".$i)->getValue()."'
	,'".$currentSheet->getCell("C".$i)->getValue()."'
	,'".$currentSheet->getCell("D".$i)->getValue()."'
	),";
}
$sql = substr($sql,0,strlen($sql)-1);
mysql_query($sql,$conn);

mysql_query("call basic_memory__init() ;",$conn);


        
$eval = "
class config{
	public static \$host = '".$db_server."';
	public static \$unm = '".$db_username."';
	public static \$pwd = '".$db_password."';
	public static \$db = '".$db_name."';
	public static \$language = 'zh-cn';
	public static \$phpexcel = \"../libs/phpexcel/Classes/\";
}
";
eval($eval);
require_once 'basic_memory.php';
require_once 'tools.php';
require_once 'basic_excel.php';

$obj = new basic_memory();
$obj->il8n();        
$_REQUEST['userid'] = 1;

basic_excel::import('../file/download/highschool/basic_permission.xls');
echo " basic_permission ".basic_excel::$guid; 	
mysql_query("call basic_permission__import('".basic_excel::$guid."',@state,@msg,@ids)",$conn);
$res = mysql_query("select @state,@msg,@ids",$conn);
$data = mysql_fetch_assoc($res);
print_r($data);


basic_excel::import('../file/download/highschool/basic_group2.xls');
echo " basic_group ".basic_excel::$guid."---------";
mysql_query("call basic_group__import('".basic_excel::$guid."',@state2,@msg2,@ids2)",$conn);
$res = mysql_query("select @state2,@msg2,@ids2",$conn);
$data = mysql_fetch_assoc($res);
print_r($data);

//导入权限对用户组
basic_excel::import('../file/download/highschool/basic_group_2_permission2.xls');
echo " basic_group_2_permission ".basic_excel::$guid;	
mysql_query("call basic_group_2_permission__import('".basic_excel::$guid."',@state,@msg)",$conn);
$res = mysql_query("select @state,@msg",$conn);  	
$data = mysql_fetch_assoc($res);	
print_r($data);		

//导入用户  
basic_excel::import('../file/download/highschool/basic_user.xls');
echo " basic_user ".basic_excel::$guid;
mysql_query("call basic_user__import('".basic_excel::$guid."',@state,@msg,@ids)",$conn);  
$res = mysql_query("select @state,@msg,@ids",$conn);
$data = mysql_fetch_assoc($res);
print_r($data); 

//导入科目
basic_excel::import('../file/download/highschool/education_subject2.xls');
echo " education_subject ".basic_excel::$guid;
mysql_query("call education_subject__import('".basic_excel::$guid."',@state,@msg,@ids)",$conn);  
$res = mysql_query("select @state,@msg,@ids",$conn);
$data = mysql_fetch_assoc($res);
print_r($data);	
 																		
//导入学生
basic_excel::import('../file/download/highschool/education_student2.xls');
echo " education_student ".basic_excel::$guid;
mysql_query("call education_student__import('".basic_excel::$guid."',@state,@msg,@ids)",$conn);  
$res = mysql_query("select @state,@msg,@ids",$conn);
$data = mysql_fetch_assoc($res);
print_r($data);	

//导入教师
basic_excel::import('../file/download/highschool/education_teacher2.xls');
echo " education_teacher ".basic_excel::$guid;
mysql_query("call education_teacher__import('".basic_excel::$guid."',@state,@msg,@ids)",$conn);  
$res = mysql_query("select @state,@msg,@ids",$conn);
$data = mysql_fetch_assoc($res);
print_r($data);			

//导入 科目-教师-班级 对应关系
basic_excel::import('../file/download/highschool/education_subject_2_group_2_teacher2.xls');
echo " education_subject_2_group_2_teacher ".basic_excel::$guid;
mysql_query("call education_subject_2_group_2_teacher__import('".basic_excel::$guid."',@state,@msg)",$conn);  
$res = mysql_query("select @state,@msg",$conn);
$data = mysql_fetch_assoc($res);
print_r($data);	

//模拟业务数据
mysql_query("call education_exam__init4test(4,@msg,@state)",$conn);  
mysql_query("call education_exam_2_student__init4test(80)",$conn);		
mysql_query("call education_paper__init4test(30)",$conn);	

$arr = array(
'host'=>$db_server
,'unm'=>$db_username
,'pwd'=>$db_password
,'db'=>$db_name
,'language'=>'zh-cn'
,'phpexcel'=>'../libs/phpexcel/Classes/'
);
$content = "<?php
class config{
";
$keys = array_keys($arr);
for($i=0;$i<count($arr);$i++){
	$content .= "
	public static \$".$keys[$i]." = '".$arr[$keys[$i]]."';";
}
$content.=
"
}";
$file_handle = fopen("config.php","w");
fwrite($file_handle,$content);
fclose($file_handle);

echo "<br/><br/><br/><br/><br/><a href='../html/desktop.html'>Back to system's front page</a>";