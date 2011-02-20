<?php
include_once dirname(__FILE__).'/../model/quiz.php';

class quiz extends wls{	
	private $m = null;
	
	function quiz(){
		parent::wls();		
		$this->m = new m_quiz();
	}
}
?>