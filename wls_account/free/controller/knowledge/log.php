<?php 
class knowledge_log extends wls{
	private $m = null;
	
	function knowledge_log(){
		include_once dirname(__FILE__).'/../../model/knowledge/log.php';
		$this->m = new m_knowledge_log();		 
	}
	
	/**
	 * 获得我的知识点掌握度雷达图统计描述
	 * 为 AM CHART 前台提供数据
	 * */
	public function getMyRaderSetting(){
		$id = '';
		if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){
			$id = $_REQUEST['id'];
		}
		$data = $this->m->getMyRecent($id);
		header('Content-Type:text/xml'); 		
		$data2 = array_values($data);
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<settings> 
	<type>stacked</type> 
	<radar>
		<x>40%</x>                                                   
		<y>40%</y>	
		<grow_time>2</grow_time>                                  
		<sequenced_grow>true</sequenced_grow>                    
	</radar>
  <background>
    <color>#EEEEEE</color>
    <alpha>100</alpha>
    <border_color>#999999</border_color>
    <border_alpha>90</border_alpha>
  </background>	
 <legend>
 <enabled>false</enabled>
   </legend>  
  <values>
  	<max>100</max>
  </values>
	<graphs>
		<graph gid="1">                                         
			<title>统计</title>                          
			<color>#999999</color>                                                            
			<fill_alpha>50</fill_alpha>                             
     	</graph>
     </graphs>   
	<data>
		<chart>
			<axes>';
		for($i=0;$i<count($data2);$i++){
			$xml .= '<axis xid="'.$i.'">'.$data2[$i]['name'].'</axis>';
		}
		
		$xml.='		
			</axes>
			<graphs>
				<graph gid="1"> ';
		for($i=0;$i<count($data2);$i++){
			$xml .= '<value xid="'.$i.'">'.floor(($data2[$i]['count_right']*100)/($data2[$i]['count_right'] + $data2[$i]['count_wrong'])).'</value>';
		}	

		$xml.='
				</graph>				          		
			</graphs>
		</chart>
	</data>  
</settings> 	    
';
		echo $xml;
	}
	
	public function getMyRaderData(){
		
	}
}
?>