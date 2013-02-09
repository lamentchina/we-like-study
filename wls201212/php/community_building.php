<?php
/**
 * 
 * @version 201301
 * @author wei1224hf@gmail.com
 * */
class community_building {
    
    public function grid() {
        $CONN = tools::conn();
       
        
        $sql = "select astext(ogc_geom) as gis,name,code,id from zone1 limit 100 ";
        $sql = "            SELECT
                id,
                ASTEXT(ogc_geom) AS gis
            FROM
                buildings_complete
            WHERE
                MBRINTERSECTS(GEOMFROMTEXT('POLYGON((
                ".$_REQUEST['right']." ".$_REQUEST['bottom'].",

                ".$_REQUEST['right']." ".$_REQUEST['top'].",

                ".$_REQUEST['left']." ".$_REQUEST['top'].",

                ".$_REQUEST['left']." ".$_REQUEST['bottom'].",

                ".$_REQUEST['right']." ".$_REQUEST['bottom']."

                
                ))'), ogc_geom)
            ORDER BY
                height";
        //echo $sql;
        $res = mysql_query($sql,$CONN);
        
		$data = array();
		while($temp = mysql_fetch_assoc($res)){
			$data[] = $temp;
		}
		
		//header("Content-type:text/json");        
		echo json_encode($data);
    }
}