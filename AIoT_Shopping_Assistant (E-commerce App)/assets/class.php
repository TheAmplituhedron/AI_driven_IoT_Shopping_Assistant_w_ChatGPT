<?php

session_start();

// Define the user class and its functions:
class user {
	public $conn;
	
	private $Brevo_API_URL = "https://api.brevo.com/v3/smtp/email";
	private $Brevo_API_Key = '<_API_Key_>';
	private $Brevo_email = 'freelance@theamplituhedron.com';
	private $Brevo_email_name = 'AIoT Shopping Assistant';
	
	public function __init__($conn){
		$this->conn = $conn;
	}
	
	// Database -> Add new account information
	public function add_new_account($firstname, $lastname, $email, $username, $password, $c_password){
		// Check for existing users.
		$existing_sql = "SELECT * FROM `users` WHERE `username`='$username' OR `email`='$email'";
		$existing_sql_result = mysqli_query($this->conn, $existing_sql);
		$existing_sql_check = mysqli_num_rows($existing_sql_result);
		if($existing_sql_check > 0){
			header('Location: ../?userAlreadyExists');
			exit();
		}
		// Confirm the given account password.
		if($password != $c_password){
			header('Location: ../?wrongPassword');
			exit();
		}
		
		// Obtain the unique user token — 12 digits.
		$user_token = $this->generate_token(12, $username);

		// Create a QR code from the given username and the generated user token.
		$qr_code = "https://chart.googleapis.com/chart?cht=qr&chs=450x450&chl=user%".$user_token."&choe=UTF-8";
		
		// Insert new user information into the users MySQL database table.
		$insert_sql = "INSERT INTO `users` (`firstname`, `lastname`, `username`, `password`, `email`, `token`, `qr_code`, `successful_order`)
		               VALUES ('$firstname', '$lastname', '$username', '$password', '$email', '$user_token', '$qr_code', 1)";
		mysqli_query($this->conn, $insert_sql);
		
		// Create a unique MySQL database table for the registered account.
		$new_table = $this->create_products_table($user_token);
		if(!$new_table){ header('Location: ../?mysqlServerFailed'); exit(); }
		
		// Send a confirmation email to the user, including the verification QR code.
		$this->send_confirmation_email($email, "Verify Your Account", $firstname.' '.$lastname, $qr_code);
		
		// Set the required session variables.
		$_SESSION["name"] = $firstname.' '.$lastname;
		$_SESSION["username"] = $username;
		$_SESSION["email"] = $email;
		$_SESSION["user_token"] = $user_token;
		$_SESSION["qr_code"] = $qr_code;
		
		// If there is no error, go to the user interface (dashboard).
		header('Location: ../dashboard.php');
		exit();
	}
	
	// If the user requests to log into an existing account:
	public function user_login_request($u_username, $u_password){
		// Check whether the given account information is accurate.
	    $account_sql = "SELECT * FROM `users` WHERE `username`='$u_username' AND `password`='$u_password'";
		$account_sql_result = mysqli_query($this->conn, $account_sql);
		$account_sql_check = mysqli_num_rows($account_sql_result);
		if($account_sql_check > 0){
			if($row = mysqli_fetch_assoc($account_sql_result)){				
			    // Set the required session variables.
				$_SESSION["name"] = $row['firstname'].' '.$row['lastname'];
				$_SESSION["username"] = $row['username'];
				$_SESSION["email"] = $row['email'];
				$_SESSION["user_token"] = $row['token'];
				$_SESSION["qr_code"] = $row['qr_code'];
				// If there is no error, go to the user interface (dashboard).
				header('Location: ../dashboard.php');
				exit();
			}
		}else{
			header('Location: ../login.php?noAccountFound');
			exit();
		}
	}
	
	// Generate a unique user token.
	private function generate_token($len, $username){
		// Define the main string.
		$lowercase = "abcdefghijklmnopqrstuvwxyz"; $uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; $number = "0123456789"; $symbol = "*()[]{}#$?!";
		$main = $lowercase.$uppercase.$number.$symbol;
		// Derive the user token from the main string.
		$token = "";
		for ($i=0; $i<$len; $i++){ $token .= $main[random_int(0, (strlen($main)-1))]; }
        return $username."_".$token;
	}
	
	// Create a unique MySQL database table for the new user.
	private function create_products_table($table){
		// Create a new database table.
		$sql_create = "CREATE TABLE `$table`(		
							id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
							product_barcode varchar(255) NOT NULL,
							product_name varchar(255) NOT NULL,
							product_ingredients varchar(255) NOT NULL,
							product_price int NOT NULL,
							cart_number int NOT NULL,
							order_number int NOT NULL
					   );";
		if(mysqli_query($this->conn, $sql_create)){ return true; } else{ return false; }		
	}
	
	// Via Brevo's Email API, send an HTML email to the user.
	public function send_Brevo_email($to_email, $subject, $name, $html_content){
		// Define POST data parameters in the JSON format. 
		$data = '{  
					"sender":{  
								"name":"'.$this->Brevo_email_name.'",
								"email":"'.$this->Brevo_email.'"
							 },
					"to":[  
							 {  
								"email":"'.$to_email.'",
								"name":"'.$name.'"
							 }
						 ],
					"subject":"'.$subject.'",
					"htmlContent":"'.$html_content.'"
				 }';
		// Define the required HTML headers.
		$headers = array('accept: application/json', 'api-key:'.$this->Brevo_API_Key, 'content-type: application/json');
		// Send an HTML email via Brevo's Email API by making a cURL call.
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_URL, $this->Brevo_API_URL);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		// Execute the defined cURL call.
		$result = curl_exec($curl);
		if(!$result){ header('Location: ../?emailServerFailed'); exit(); }
        curl_close($curl);
	}
	
	// Send an account confirmation email to the new user, including the unique account verification QR code.
	private function send_confirmation_email($to_email, $subject, $name, $qr_code){
		// Define the HTML message (content) of the email.
		$html_content = '<html><head><style>h1{text-align:center;font-weight:bold;color:#505F4E;font-size:40px;}a{text-decoration:none;color:#9BB5CE;font-size:18px;font-weight:bold;}div{display:block;background-color:#F9E5C9;text-align:center;border:50px solid #5C5B57;}div p{font-size:25px;color:#505F4E;font-weight:bold;}@media only screen and (max-width: 600px){h1{font-size:20px;}a{font-size:9px;}div{border:10px solid #5C5B57;}div p{font-size:12px;}}</style></head><body><div><h1>Thanks for trying AIoT Shopping Assistant 😊</h1><img src=\"'.$qr_code.'\" alt=\"QR_CODE\" /><p>Please scan the account QR code with the shopping assistant to activate your cart 🛍️</p><a href=\"http://192.168.1.22/AIoT_Shopping_Assistant/\">➡️ Go to your Dashboard<br><br></a></div></body></html>';
		// Transfer the HTML email.
		$this->send_Brevo_email($to_email, $subject, $name, $html_content);
	}
	

}

// Define the product class and its functions:
class product extends user {
	private $OPENAI_API_KEY = "<_OPENAI_API_KEY_>";
	private $OPENAI_ENDPOINT = "https://api.openai.com/v1/chat/completions";
	
	// Obtain and decrypt the product information from the Open Food Facts JSON API by barcode.
	public function get_product_info($barcode){
		// Make an HTTP GET request to the Open Food Facts JSON API.
		// Then, decode the received JSON object.
		$data = json_decode(file_get_contents("https://world.openfoodfacts.org/api/v0/product/".$barcode.".json", TRUE));
		$product_info = array(
								"name" => $data->product->product_name,
								"ingredients" => (is_null($data->product->ingredients_text_en) || $data->product->ingredients_text_en == "") ? "Not Found" : $data->product->ingredients_text_en,
								"price" => (int)$data->product->product_quantity / 100
							 );
		return $product_info;
	}
	
	// Retrieve the current product list created by the customer.
	public function get_current_products($table){
		$total_price = 0;
		// Obtain the current order tag (number).
		$order_number = $this->get_order_number($table);
		// Obtain all registered product information of the current cart as a list.
		$p_barcode = []; $p_name = []; $p_ingredients = []; $p_price = []; $p_number = [];
		$sql_list = "SELECT * FROM `$table` WHERE `order_number`='$order_number' ORDER BY `id` ASC";
		$result = mysqli_query($this->conn, $sql_list);
		$check = mysqli_num_rows($result);
		if($check > 0){
			while($row = mysqli_fetch_assoc($result)){
				// Store the fetched product information as arrays.
				array_push($p_barcode, $row["product_barcode"]);
				array_push($p_name, $row["product_name"]);
				array_push($p_ingredients, $row["product_ingredients"]);
				array_push($p_price, $row["product_price"]);
				array_push($p_number, $row["cart_number"]);
				// Calculate the total cart price (amount).
				$price = $row["product_price"] * $row["cart_number"];
				$total_price+=$price;
			}
			return array($p_barcode, $p_name, $p_ingredients, $p_price, $p_number, array("total_price" => $total_price));
		}else{
			return array(["Not Found!"], ["Not Found!"], ["Not Found!"], ["Not Found!"], ["Not Found!"], array("total_price" => 0));
		}
	}
	
	// Retrieve and print the previous order lists.  
	public function get_previous_orders($table){
		// Obtain the current order tag (number).
		$order_number = $this->get_order_number($table);
		// If there are any previous orders, return the purchased products as an HTML list for each order.
		if($order_number == 1){
			echo '<h1><i class="fa-solid fa-circle-xmark"></i> No previous order was found!</h1>';
		}else{
			$list = "";
			for($i=1;$i<$order_number;$i++){
				$sql = "SELECT * FROM `$table` WHERE `order_number`='$i' ORDER BY `id` ASC";
				$result = mysqli_query($this->conn, $sql);
				$check = mysqli_num_rows($result);
				if($check > 0){
					while($row = mysqli_fetch_assoc($result)){
						$line = '<li>'.$row["product_name"].' ['.$row["product_barcode"].'] <span><i class="fa-solid fa-xmark"></i></span>'.$row["cart_number"].'</li>';
						$list.=$line;
					}
				}
				echo '<div class="orders"><h2><i class="fa-solid fa-cash-register"></i> Order ['.$i.']</h2><ul>'.$list.'</ul></div>';
				$list = "";			
			}
		}
	}
	
	// Generate the unique payment QR code and notify the user of the placed order via an HTML email.
	public function user_checkout($table, $email, $name){
		// Create a QR code from the user token and the given command.
		$qr_text = 'finished%'.$table;
		$qr_code = "https://chart.googleapis.com/chart?cht=qr&chs=450x450&chl=".$qr_text."&choe=UTF-8";
		
		// Update the successful order number after the checkout process.
		$sql = "UPDATE `users` SET `successful_order`=`successful_order`+1 WHERE `token` = '$table'";
		mysqli_query($this->conn, $sql);
		
		// Send a notification email to the user, including the unique payment QR code.
		$this->send_payment_email($email, "Order Successful", $name, $qr_code);
		
		// If there is no error, go to the user interface (dashboard).
		header('Location: ./dashboard.php?paymentCompleted');
		exit();	
	}
	
	// Send a notification email to the user after completing the checkout process, including the unique payment QR code.
	private function send_payment_email($to_email, $subject, $name, $qr_code){
		// Define the HTML message (content) of the email.
		$html_content = '<html><head><style>h1{text-align:center;font-weight:bold;color:#505F4E;font-size:40px;}a{text-decoration:none;color:#9BB5CE;font-size:18px;font-weight:bold;}div{display:block;background-color:#F9E5C9;text-align:center;border:50px solid #5C5B57;}div p{font-size:25px;color:#505F4E;font-weight:bold;}@media only screen and (max-width: 600px){h1{font-size:20px;}a{font-size:9px;}div{border:10px solid #5C5B57;}div p{font-size:12px;}}</style></head><body><div><h1>Thanks for your order 😊👍</h1><img src=\"'.$qr_code.'\" alt=\"QR_CODE\" /><p>Please scan your payment QR code with the shopping assistant to complete your order 💲✅</p><a href=\"http://192.168.1.22/AIoT_Shopping_Assistant/\">➡️ Go to your Dashboard<br><br></a></div></body></html>';
		// Transfer the HTML email.
		$this->send_Brevo_email($to_email, $subject, $name, $html_content);
	}
	
	// Make a cURL call (request) to the OpenAI API in order to get suggestions regarding the given product from ChatGPT.
	public function chatgpt_get_suggestion($product){
		// Define the questions related to the given product.
		$questions = array(
							"What is the nutritional value of ".$product."?",
							"What should I purchase with ".$product."?",
							"Can you teach me a recipe with ".$product."?",
							"How should I serve ".$product."?",
							"Is there a more affordable and healthy option than ".$product."?"
		                  );
		// Define POST data parameters in the JSON format. 
		$data = '{  
					"model": "gpt-3.5-turbo",
					"messages": [
									{"role": "user", "content": "'.$questions[0].'"},
									{"role": "user", "content": "'.$questions[1].'"},
									{"role": "user", "content": "'.$questions[2].'"},
									{"role": "user", "content": "'.$questions[3].'"},
									{"role": "user", "content": "'.$questions[4].'"},
									{"role": "user", "content": "Please add the exact question at the beginning of the answer with the question number."}
								],
					"temperature": 0.7
				 }';
		// Define the required HTML headers.
		$headers = array('Authorization: Bearer '.$this->OPENAI_API_KEY, 'Content-Type: application/json');
		// Obtain product suggestions from ChatGPT by making a cURL call to the OpenAI API.
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_URL, $this->OPENAI_ENDPOINT);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		// Execute the defined cURL call.
		$result = curl_exec($curl);
		if(!$result){ header('Location: ../?ChatGPTServerFailed'); exit(); }
        curl_close($curl);
        // Decode the received JSON object to obtain suggestions generated by ChatGPT.
		$res = json_decode($result);
		$suggestions = $res->choices[0]->message->content;
		// Modify the obtained suggestions to add line breaks.
		$modified_suggestions = $suggestions;
		$modified_suggestions = str_replace('1. '.$questions[0], "<h2>Suggestions</h2>", $modified_suggestions);
		for($i=1;$i<count($questions);$i++){
			$modified_suggestions = str_replace(strval($i+1).'. '.$questions[$i], "<br><br>", $modified_suggestions);
		} 
		// Return the modified suggestions and the defined product questions.
		return array($modified_suggestions, $questions);
	}
	
    // Database -> Insert product data
	public function insert_product($table, $barcode, $name, $ingredients, $price){
		// Obtain the current order tag (number).
		$order_number = $this->get_order_number($table);
		// Check whether the given product is in the user's database table or not.
		if($this->check_product($table, $barcode, $order_number)){
			// If the given product is already in the cart (table), update the product amount (cart number).
			$sql_update = "UPDATE `$table` SET `cart_number`=cart_number+1 WHERE `product_barcode` = '$barcode'";
			if(mysqli_query($this->conn, $sql_update)){ return true; } else{ return false; }
		}else{
			// If not, insert the new product information into the user's database table.
			$sql_insert = "INSERT INTO `$table` (`product_barcode`, `product_name`, `product_ingredients`, `product_price`, `cart_number`, `order_number`)
		                   VALUES('$barcode', '$name', '$ingredients', '$price', 1, '$order_number');
					      ";
		    if(mysqli_query($this->conn, $sql_insert)){ return true; } else{ return false; }
		}
	}

    // Database -> Delete product data
	public function delete_product($table, $barcode){
		// Obtain the current order tag (number).
		$order_number = $this->get_order_number($table);
		// Check whether the given product is in the user's database table or not.
		if($this->check_product($table, $barcode, $order_number)){
			// Remove the given product from the cart (table).
			$sql_delete = "DELETE FROM `$table` WHERE `product_barcode`='$barcode' AND `order_number` = '$order_number'";
			if(mysqli_query($this->conn, $sql_delete)){ return true; } else{ return false; }
		}
	}
	
	// Database -> Check database table
	public function check_table($table){
		$sql = "SELECT * FROM `information_schema`.`TABLES` WHERE `table_name` = '$table' limit 1";
		$sql_result = mysqli_query($this->conn, $sql);
		$sql_check = mysqli_num_rows($sql_result);
		if($sql_check > 0){ return true; } else{ return false; }
	}
	
	// Database -> Check product
	private function check_product($table, $barcode, $order_number){
		$sql = "SELECT * FROM `$table` WHERE `product_barcode` = '$barcode' AND `order_number` = '$order_number'";
		$sql_result = mysqli_query($this->conn, $sql);
		$sql_check = mysqli_num_rows($sql_result);
		if($sql_check > 0){ return true; } else{ return false; }
	}
	
    // Database -> Get order number 
	private function get_order_number($token){
		$order_number = 0;
		$sql = "SELECT * FROM `users` WHERE `token` = '$token'";
		$sql_result = mysqli_query($this->conn, $sql);
		$sql_check = mysqli_num_rows($sql_result);
		if($sql_check > 0){
			if($row = mysqli_fetch_assoc($sql_result)){
				$order_number = $row["successful_order"];
			}
			return $order_number;
		}
	}
}

// Define database and server settings:
$server = array(
	"name" => "localhost",
	"username" => "root",
	"password" => "",
	"database" => "shopping_assistant_users"
);

$conn = mysqli_connect($server["name"], $server["username"], $server["password"], $server["database"]);

?>