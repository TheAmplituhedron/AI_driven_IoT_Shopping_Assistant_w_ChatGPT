<?php

include_once "class.php";

// Define the new '_user' object:
$_user = new user();
$_user->__init__($conn);

// Create a new user account if requested.
if(isset($_GET['firstname']) && isset($_GET['lastname']) && isset($_GET['email']) && isset($_GET['username']) && isset($_GET['password']) && isset($_GET['c_password'])){
	$_user->add_new_account($_GET['firstname'], $_GET['lastname'], $_GET['email'], $_GET['username'], $_GET['password'], $_GET['c_password']);
}

// If the user requests to sign in:
if(isset($_GET['u_username']) && isset($_GET['u_password'])){
	$_user->user_login_request($_GET['u_username'], $_GET['u_password']);
}

?>