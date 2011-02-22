<?php

session_start();
//print_r(session_name('site'));
print_r($_SESSION);
print_r($_COOKIE);


$_SESSION['wls_user'] = array(
	 'username'=>'admin'
	,'id'=>'1'
	,'group'=>'10,20,30'
);
?>