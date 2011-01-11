<?php
class QoDesk{
	public function getJS(){
		switch($_REQUEST['moduleId']){
			case 'qd_wls_user':
				include 'free/view/QoDesk/user.js';
				break;
			case 'qo-preferences':
				include '';
				break;
			default :break;
		}
	}
}
?>