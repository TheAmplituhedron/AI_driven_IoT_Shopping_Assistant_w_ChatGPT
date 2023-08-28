<?php

session_start();

// If the user has already signed in, go to the user interface (dashboard).
if(isset($_SESSION["username"]) && isset($_SESSION["email"])){
	header('Location: ./dashboard.php');
	exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login / AIoT Shopping Assistant</title>

<!--link to index.css-->
<link rel="stylesheet" type="text/css" href="assets/index.css"></link>

<!--link to favicon-->
<link rel="icon" type="image/png" sizes="36x36" href="assets/icon.png">

<!-- link to FontAwesome-->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.1/css/all.css">
 
<!-- link to font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

</head>
<body>
<?php ini_set('display_errors',1);?> 
<h1><i class="fa-sharp fa-solid fa-network-wired"></i> <i class="fa-sharp fa-solid fa-wifi"></i> Please sign in to experience AIoT shopping <i class="fa-regular fa-face-smile"></i></h1>

<div class="container">
<form method="get" action="assets/new_account.php">
<div><label for="u_username">Username:</label><input name="u_username" placeholder="John_123" id="u_username"></input></div>
<div><label for="u_password">Password:</label><input type="password" name="u_password" placeholder="000_abc" id="u_password"></input></div>
<button type="submit"><i class="fa-solid fa-house-circle-check"></i> Sign In</button>
</form>
</div>
</body>
</html>