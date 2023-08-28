<?php

include_once "assets/class.php";

// Define the new '_product' object:
$_product = new product();
$_product->__init__($conn);

// If the user signed in successfully, proceed.
if(!isset($_SESSION["username"]) && !isset($_SESSION["email"])){
	header('Location: ./');
	exit();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Orders / AIoT Shopping Assistant</title>

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

<?php

$_product->get_previous_orders($_SESSION["user_token"]);

?>

</body>
</html>