<?php
class basic_excel {
    
	public static $guid = '';
	
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
        
        $columns2int = array(
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
        $int2column = array_keys($columns2int);
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
                    ,'maxcolumn'=>$columns2int[$sheetData[1]]
                    ,'id_creater'=>$_REQUEST['userid']
                );
                $maxcolumn_check = 0;
                for($i3=$columns2int[$sheetData[1]];$i3>=1;$i3--){
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