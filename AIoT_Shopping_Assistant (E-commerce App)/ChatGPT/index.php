<?php

include_once "../assets/class.php";

// Define the new '_product' object:
$_product = new product();
$_product->__init__($conn);

if(!isset($_SESSION["username"]) && !isset($_SESSION["email"])){
	header('Location: ./');
	exit();
}

// Make a cURL call (request) to the OpenAI API in order to get suggestions regarding the given product from ChatGPT.
$suggestions = "<h2>Please enter a product name.</h2>";
$questions = ["Please enter a product name."];
if(isset($_GET["product"]) && $_GET["product"] != "Not Found!"){
	list($suggestions, $questions) = $_product->chatgpt_get_suggestion($_GET["product"]);
}


?>

<!DOCTYPE html>
<html>
<head>
<title>ChatGPT / AIoT Shopping Assistant</title>

<!--link to index.css-->
<link rel="stylesheet" type="text/css" href="index.css"></link>

<!--link to favicon-->
<link rel="icon" type="image/png" sizes="36x36" href="icon.png">

<!-- link to FontAwesome-->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.1/css/all.css">
 
<!-- link to font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

</head>
<body>
<?php ini_set('display_errors',1);?> 
<h1><i class="fa-solid fa-network-wired"></i> Powered by ChatGPT</h1>
<div class="question">
<?php

for($i=0;$i<count($questions);$i++){
	echo '<h2><i class="fa-regular fa-comment-dots"></i> '.$questions[$i].'</h2>';
}

?>
</div>
<br>
<div class="reply">
<p><img src="icon.png" alt="ChatGPT" />
<?php

echo $suggestions;

?>
</p>
</div>

</body>
</html>