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
<title>AIoT Shopping Assistant</title>

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
<h1><i class="fa-sharp fa-solid fa-network-wired"></i> <i class="fa-sharp fa-solid fa-wifi"></i> Please create an account to experience AIoT shopping <i class="fa-regular fa-face-smile"></i></h1>

<div class="container">
<form method="get" action="assets/new_account.php">
<div><label for="firstname">First name:</label><input name="firstname" placeholder="John" id="firstname"></input></div>
<div><label for="lastname">Last name:</label><input name="lastname" placeholder="Doe" id="lastname"></input></div>
<div><label for="email">Email:</label><input name="email" placeholder="johndoe@gmail.com" id="email"></input></div>
<div><label for="username">Username:</label><input name="username" placeholder="John_123" id="username"></input></div>
<div><label for="password">Password:</label><input type="password" name="password" placeholder="000_abc" id="password"></input></div>
<div><label for="c_password">Confirm Password:</label><input type="password" name="c_password" placeholder="000_abc" id="c_password"></input></div>
<button type="submit"><i class="fa-solid fa-user-check"></i> Create New Account</button>
<p style="text-align:center;"><a href="./login.php">Already have an account?</a></p>
</form>
</div>
</body>
</html>