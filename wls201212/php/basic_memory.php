<?php
class basic_memory {
    
    public function il8n(){
        $CONN = tools::conn();
        mysql_query("delete from basic_memory where extend6 = 'il8n'",$CONN);
        $il8n = tools::getLanguage();
        //print_r($il8n);
        $keys = array_keys($il8n);
        $values = array_values($il8n);
        for($i=0;$i<count($il8n);$i++){
            if(is_array($values[$i])){
                $keys2 = array_keys($values[$i]);
                $values2 = array_values($values[$i]);
                for($i2=0;$i2<count($values[$i]);$i2++){
                    $sql = "insert into basic_memory (code,extend4,extend5,extend6) values ('".$keys2[$i2]."','".$values2[$i2]."','".$keys[$i]."','il8n')";
                    mysql_query($sql,$CONN);
                }
            }else{
                $sql = "insert into basic_memory (code,extend4,extend6) values ('".$keys[$i]."','".$values[$i]."','il8n')";
                mysql_query($sql,$CONN);
            }            
        }
    }
}