<?php

session_start();

// If the user signed in successfully, proceed.
if(!isset($_SESSION["username"]) && !isset($_SESSION["email"])){
	header('Location: ./');
	exit();
}

// If the user requests to log out:
if(isset($_GET["logout"])){
	// Remove and destroy all session variables.
	session_unset();
	session_destroy();
	// Go to the home page.
	header('Location: ./');
	exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard / AIoT Shopping Assistant</title>

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

<!--link to jQuery script-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

</head>
<body>
<?php ini_set('display_errors',1);?> 
<h1 style="text-align:left;padding-left:20px;margin-top:60px;"><i class="fa-solid fa-keyboard"></i> Welcome to your Dashboard</h1>
<div class="products">
<table>
<tr><th>Product</th><th>Barcode</th><th>Ingredients</th><th>Price($)</th><th>Unit</th><th>Counsel</th></tr>
<tr><td>X</td><td>X</td><td>X</td><td>X</td><td>X</td><td><a href="ChatGPT/?product=Not Found!" target="_blank"><button><i class="fa-solid fa-comment-dots"></i> Ask ChatGPT</button></a></td></tr>
</table>
</div>

<div class="info">
<br>
<section>
<div>
<p><i class="fa-solid fa-money-check-dollar"></i> Total Price:</p>
<span id="total_price">$0</span>
<form><button id="checkout"><i class="fa-solid fa-cart-shopping"></i> Checkout</button></form>
</div>
<img src="<?php echo $_SESSION["qr_code"] ?>" alt="QR_CODE" />
<a href="?logout=OK"><button class="logout"><i class="fa-solid fa-door-open"></i> Logout</button></a>
</section>
<br>
<section>
<h2><i class="fa-solid fa-address-card"></i> Account Information</h2>
<p>Name: <span><?php echo $_SESSION["name"]; ?></span></p>
<p>Email: <span><?php echo $_SESSION["email"]; ?></span></p>
<p>Username: <span><?php echo $_SESSION["username"]; ?></span></p>
<a href="previous_orders.php" target="_blank"><button><i class="fa-solid fa-backward"> </i> Previous Orders</button></a>
</section>
</div>

<form id="hidden_checkout" action="checkout.php" method="post" target="_blank" style="display:none;">
<input id="checkout_price" name="checkout_price" type="hidden" value="$0">
</form>

<!--Add the index.js file-->
<script type="text/javascript" src="assets/index.js"></script>

</body>
</html>