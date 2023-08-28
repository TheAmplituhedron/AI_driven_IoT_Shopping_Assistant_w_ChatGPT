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

// If the user places an order by entering the requested credit/debit card information:
if(isset($_GET["c_number"]) && isset($_GET["c_name"]) && isset($_GET["c_date"]) && isset($_GET["c_verify"])){
	$_product->user_checkout($_SESSION["user_token"], $_SESSION["email"], $_SESSION["name"]);
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout / AIoT Shopping Assistant</title>

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
<h1 style="text-align:left;padding-left:20px;margin-top:60px;"><i class="fa-solid fa-cart-shopping"></i> Checkout</h1>

<div class="container">
<form method="get" action="" id="checkout_form">
<br>
<fieldset>
<legend> Credit or Debit Card </legend>
<div><label for="c_number">Card Number:</label><input name="c_number" placeholder="4111 1111 1111 1111" id="c_number"></input></div>
<div><label for="c_name">Name:</label><input name="c_name" placeholder="John Doe" id="c_name"></input></div>
<div><label for="c_date">Expiration Date:</label><input name="c_date" placeholder="12/2023" id="c_date"></input></div>
<div><label for="c_verify">Card Verification Number:</label><input name="c_verify" placeholder="123" id="c_verify"></input></div>
<button type="submit"><i class="fa-solid fa-money-check-dollar"></i> Pay</button>
<img src="assets/credit.jpg" alt="credit" />
<span>
<?php
// Check whether the total price variable is received.
if(isset($_POST["checkout_price"])){
	echo $_POST["checkout_price"];
}else{
	echo "$0";
}
?>
</span>
</fieldset>
<br>
</form>
</div>
</body>
</html>