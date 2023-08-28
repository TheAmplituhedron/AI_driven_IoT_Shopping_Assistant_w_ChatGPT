<?php

include_once "class.php";

// Define the new '_product' object:
$_product = new product();
$_product->__init__($conn);

if(isset($_GET["table"]) && isset($_GET["barcode"]) && isset($_GET["com"])){	
	// Check whether the given table is in the MySQL database.
	if($_product->check_table($_GET["table"])){
		// Make an HTTP GET request to the Open Food Facts JSON API to obtain the product information with the given barcode.
		$product_info = $_product->get_product_info($_GET["barcode"]);
		// According to the selected command by the user, add or remove the given product to/from the user's database table.
		if($_GET["com"] == "add"){
			$_product->insert_product($_GET["table"], $_GET["barcode"], $product_info["name"], $product_info["ingredients"], $product_info["price"]);
			echo "Given Product Added to the Cart Successfully!";
		}else if($_GET["com"] == "remove"){
			$_product->delete_product($_GET["table"], $_GET["barcode"]);
			echo "Given Product Removed from the Cart Successfully!";
		}
	}else{
		echo "No Table Found!";
	}
}

?>