<?php
function adminer_object() {
	class AdminerSoftware extends Adminer {
		function login($login, $password) {
			return true;
		}
	}
	return new AdminerSoftware;
}
// Este es el nexo directo a la ultima versión de Adminer:
// Una vez descargada, sustituir esta linea:  1547
//    name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'>Adminer</a>";}function
// por esta : 
//    name(){return"<a href='../crud_pdo/index.php'".target_blank()." id='h1'>Inicio - Adminer</a>";}function
//
// las ultimas versiones y siguientes de esta : esto no funciona, 
// ver:  https://github.com/vrana/adminer/blob/master/adminer/sqlite.php
//
/*
Ejemplo del archivo : sqlite.php:

<?php
function adminer_object() {
	include_once "../plugins/login-password-less.php";
	return new Adminer\Plugins(array(
		// TODO: inline the result of password_hash() so that the password is not visible in source codes
		new AdminerLoginPasswordLess(password_hash("YOUR_PASSWORD_HERE", PASSWORD_DEFAULT)),
	));
}

include "./index.php";

*/
include "./adminer-4.7.8.php";
