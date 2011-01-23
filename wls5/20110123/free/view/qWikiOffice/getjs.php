<?php 
$actionid = explode("_",$_REQUEST['moduleId']);
$actionid = $actionid[1];
include_once 'getjs/'.$actionid.".js";
?>