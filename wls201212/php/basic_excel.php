<?php
/**
 * 对于大数据量的导入导出,
 * 系统只支持 EXCEL 格式文件的导入导出,不支持 .pdf .txt .doc 操作
 * 将依赖数据库表中的 basic_excel 
 * 
 * @version 201211
 * @author wei1224hf@gmail.com
 * */
class basic_excel {
    
	public static $guid = '';
	
	public static $columns2int = array(
             'A'=>1
            ,'B'=>2
            ,'C'=>3
            ,'D'=>4
            ,'E'=>5
            ,'F'=>6
            ,'G'=>7
            ,'H'=>8
            ,'I'=>9
            ,'J'=>10
            ,'K'=>11
            ,'L'=>12
            ,'M'=>13
            ,'N'=>14
            ,'O'=>15
            ,'P'=>16
            ,'Q'=>17
            ,'R'=>18
            ,'S'=>19
            ,'T'=>20
            ,'U'=>21
            ,'V'=>22
            ,'W'=>23
            ,'X'=>24
            ,'Y'=>25
            ,'Z'=>26
            ,'AA'=>27
            ,'AB'=>28
            ,'AC'=>29
            ,'AD'=>30
            ,'AE'=>31
            ,'AF'=>32
            ,'AG'=>33
            ,'AH'=>34
            ,'AI'=>35
            ,'AJ'=>36
            ,'AK'=>37
            ,'AL'=>38
            ,'AM'=>39
            ,'AN'=>40		
        ); 
	
	public static function export($guid){
        include_once config::$phpexcel.'PHPExcel.php';
        include_once config::$phpexcel.'PHPExcel/IOFactory.php';
        include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
        $objPHPExcel = new PHPExcel();
        $CONN = tools::conn();
        $sql = "select * from basic_excel where guid = '".$guid."' order by sheetindex,rowindex";
        $res = mysql_query($sql,$CONN);
        $data = array();
        $sheetindex = null;
        $int2column = array_keys(self::$columns2int);

        while($temp = mysql_fetch_assoc($res)){
            if($sheetindex!=$temp['sheetindex']){
                $sheetindex = $temp['sheetindex'];
        		$objPHPExcel->createSheet();
        		$objPHPExcel->setActiveSheetIndex($temp['sheetindex']);
        		$objPHPExcel->getActiveSheet()->setTitle($temp['sheetname']);
            }  
    		for($i=0;$i<$temp['maxcolumn'];$i++){
    		    $objPHPExcel->getActiveSheet()->setCellValue($int2column[$i].$temp['rowindex'], $temp[$int2column[$i]]);
    		}
        }
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$file =  "../file/download/".date('YmdHis').".xls";
		$objWriter->save($file);
		
		mysql_query("delete from basic_excel where guid = '".self::$guid."' ;",$CONN);
		return $file;
	}
	
	/**
	 * 用户在浏览器端上传一个excel,提交到服务端,然后保存在服务端的某个路径
	 * 服务端再将这个 excel 文件读取,将内容插入到数据库表 basic_excel 中
	 * 一般而言,批量导入是一个非常复杂的过程,
	 * 需要对 excel 文件中的每一个单元格的内容检查一遍,每一次检查,都是一次 IO
	 * 因此,将业务数据判断逻辑,教给数据库端的存储过程去处理
	 * */
    public static function import($file=NULL,$return='json'){
        if($file==NULL && isset($_REQUEST['file'])) $file = $_REQUEST['file'];
        if($file==NULL)tools::error(array('state'=>0,'msg'=>'no file'));
        
        include_once config::$phpexcel.'PHPExcel.php';
        include_once config::$phpexcel.'PHPExcel/IOFactory.php';
        include_once config::$phpexcel.'PHPExcel/Writer/Excel5.php';
        $PHPReader = PHPExcel_IOFactory::createReader('Excel5');
        $PHPReader->setReadDataOnly(true);
        $phpexcel = $PHPReader->load($file);
        
        $CONN = tools::conn();

        $int2column = array_keys(self::$columns2int);
        $count = $phpexcel->getSheetCount();
        include_once '../libs/guid.php';
        $Guid = new Guid();  
        $guid = $Guid->toString();
		self::$guid = $guid;
		
        for($i=0;$i<$count;$i++){
            $currentSheet = $phpexcel->getSheet($i);
            $sheetname = $currentSheet->getTitle();
            $sheetData = array(
                 $currentSheet->getHighestRow()
                ,$currentSheet->getHighestColumn()
            );
            
            for($i2=0;$i2<$sheetData[0];$i2++){
                $data = array(
                     'guid'=>$guid
                    ,'sheets'=>$count
                    ,'sheetindex'=>$i+1
                    ,'sheetname'=>$sheetname
                    ,'rowindex'=>$i2+1
                    ,'maxcolumn'=>self::$columns2int[$sheetData[1]]
                    ,'id_creater'=>$_REQUEST['userid']
                );
                $maxcolumn_check = 0;
                for($i3=self::$columns2int[$sheetData[1]];$i3>=1;$i3--){
                    $value = $currentSheet->getCell($int2column[$i3-1].($i2+1))->getValue();
                    if($maxcolumn_check==0){
                        if($value == NULL) $data['maxcolumn'] = $i3-1;
                    }
                    if($value!=NULL)$maxcolumn_check=1;
                    $data[$int2column[$i3-1]] = trim($value);
                }                
                
                $keys = array_keys($data);
                $keys = implode(",",$keys);
                $values = array_values($data);
                $values = implode("','",$values);    
                $sql2 = "insert into basic_excel (".$keys.") values ('".$values."');";
                //echo $sql2;
                mysql_query($sql2,$CONN);
            }                 
        }        
   }
}