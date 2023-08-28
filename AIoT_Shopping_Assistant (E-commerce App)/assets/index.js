// Every 5 seconds, retrieve the HTML table rows generated from the user's database table rows to showcase current products added to the cart
// and inform the user of the calculated total cart price (amount).
setInterval(function(){
	$.ajax({
		url: "./assets/dashboard_updates.php?update",
		type: "GET",
		success: (response) => {
			// Decode the obtained JSON object.
			const data = JSON.parse(response);
			// Assign the HTML table rows as the current product list in the cart. 
			$(".products table").html(data.list);
			// Assign the evaluated total cart price (amount).
			$("#total_price").html(data.total_price);
		}
	});
}, 5000);

// When the user clicks the checkout button, open the checkout page and transfer the evaluated total cart price via the hidden HTML form.
$(".info").on("click", "#checkout", () => {
	var total_price = $("#total_price").text();
	$("#checkout_price").val(total_price);
	$("#hidden_checkout").submit();
});