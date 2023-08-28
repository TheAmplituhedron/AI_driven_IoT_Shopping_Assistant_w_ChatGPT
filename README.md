# AI_driven_IoT_Shopping_Assistant_w_ChatGPT
Activate your cart with QR code sent via email by the web app, update product info by scanning barcodes, and get shopping tips from ChatGPT.

You can inspect the full project tutorial on Hackster:
[https://www.hackster.io/kutluhan-aktar/ai-driven-iot-shopping-assistant-w-chatgpt-cf3681](https://www.hackster.io/kutluhan-aktar/ai-driven-iot-shopping-assistant-w-chatgpt-cf3681)

In light of recent developments in machine learning and artificial intelligence, various brands aimed to implement AI-based solutions to improve the shopping experience and increase their profit margin by tailoring unique suggestions and advertisements depending on the targeted user's profile and preferences. Especially e-commerce companies, platforms, and marketplaces benefited from AI algorithms to autonomously identify the most optimal ad placements for individual customers.

Even though there are some pioneering methods to combine physical store shopping with AI-based e-commerce features, such as Amazon Go cashierless convenience stores, these self-checkout stations are expensive investments for local stores since they require renovating (remodeling) store layouts or paying monthly fees to cloud services. Furthermore, local businesses may need to construct data sets for niche market products since AI algorithms require broad data sets for methods based on computer vision, sensor fusion, and deep learning.

After scrutinizing the recent research papers and the granted patents on e-commerce methods, I noticed there are nearly no appliances focusing on merging physical store shopping with AI-based solutions without altering existing conditions for local businesses. Therefore, I decided to build a budget-friendly and accessible AIoT shopping assistant utilizing the most common and recognizable product identification method — barcodes — to bring AI-based e-commerce features to physical store shopping.

Since I wanted to build a shopping assistant providing a wholesome physical shopping experience with the most prominent e-commerce features, I decided to develop a full-fledged e-commerce web application from scratch in PHP, HTML, JavaScript, CSS, and MySQL. Since I developed this complementing web application to bridge the gap between physical store shopping and e-commerce, it is capable of communicating with the shopping assistant (device), obtaining product information from barcodes, and generating AI-based recommendations consecutively.

The complementing e-commerce web application allows the customer to:

- Create a user account identified with the unique 12-digit token
- Obtain the generated account verification QR code via an HTML email, including the unique user token
- Add or remove product information via barcodes scanned by the shopping assistant
- Display the current product list in the cart and the total cart price
- Inspect ChatGPT-powered recommendations for each product in the cart
- Place an order by making a payment via credit/debit card
- Get the payment confirmation QR code via an HTML email
- Check purchased items from previous orders

You can inspect the e-commerce web application under:

***AIoT_Shopping_Assistant (E-commerce App)***

You can inspect the MicroPython application for W5300 TOE SHIELD attached to NUCLEO-F439ZI under:

***W5300_TOE_SHIELD***

