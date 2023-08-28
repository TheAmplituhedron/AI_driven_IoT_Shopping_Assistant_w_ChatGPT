# AI-driven IoT Shopping Assistant w/ ChatGPT
#
# STM32 Nucleo-144 NUCLEO-F439ZI
#
# By Kutluhan Aktar
#
# Activate your cart with a QR code sent via email by the web app, update product info by scanning barcodes,
# and get shopping tips from ChatGPT. 
# 
#
# For more information:
# https://www.theamplituhedron.com/projects/AI_assisted_IoT_Shopping_Assistant_w_ChatGPT

from usocket import socket
from wiznet_conf import wiznet5k_w5300
import urequests
import network
from pyb import Pin, UART, SPI
from ssd1309 import Display
from xglcd_font import XglcdFont
from Adafruit_Thermal import *
from time import sleep

# Create the shopping_assistant class and define its functions. 
class shopping_assistant:
    def __init__(self):
        # Define the W5300 object and the static IP address settings.
        self.w5300 = wiznet5k_w5300()
        self.w5300.w5300_set_ip('0.0.0.0','0.0.0.0','0.0.0.0','0.0.0.0')
        # Define the required print and hardware serial port settings for the tiny (embedded) thermal printer.
        self.printer = Adafruit_Thermal(bus=6, heattime=120, heatdots=5, heatinterval=40)
        # Define the required hardware serial port settings for the GM77 barcode and QR code scanner.
        self.scanner = UART(2, 9600, bits=8, parity=None, stop=1)
        self.user_token = ""
        self.new_product_barcode = ""
        # Define the required settings for the SSD1309 OLED transparent display. (SCK: PA5, MOSI: PA7)
        spi = SPI(1, SPI.MASTER, baudrate=10000000)
        self.display = Display(spi, dc=Pin("A4"), cs=Pin("C6"), rst=Pin("C7"))
        # Define the given fonts.
        self.bold = XglcdFont('assets/Unispace12x24.c', 12, 24)
        self.light = XglcdFont('assets/Bally5x8.c', 5, 8)
        # Define the control button pins.
        self.button_A = Pin("C0", Pin.IN, Pin.PULL_UP)
        self.button_B = Pin("C3", Pin.IN, Pin.PULL_UP)
        self.button_C = Pin("C2", Pin.IN, Pin.PULL_UP)
        # Define the RGB LED pins.
        self.red = Pin("A1", Pin.OUT_PP)
        self.blue = Pin("A0", Pin.OUT_PP)
        self.green = Pin("F9", Pin.OUT_PP)
        self.adjust_color([0,0,0])
        sleep(2)
        # Initialize the SSD1309 OLED transparent display.
        self.product_menu_activate = False
        self.change_layout("home")

    # If the scanner module reads a barcode or QR code successfully, decode the scanned data.
    def read_QR_barcode(self):
        scanned_data = self.scanner.readline()
        # Decode and modify the received data packet to obtain the user (account) token with the given command or the new product barcode.
        if(type(scanned_data) is not type(None)):
            decoded_data = scanned_data.decode("utf_8")
            decoded_data = decoded_data.replace("\r", "")
            print("\nScanned: " + decoded_data)
            # Get the user token.
            if(decoded_data.find("user%") >= 0):
                self.user_token = decoded_data.split("%")[1]
                print("\nYour cart registered successfully!")
                print("Registered Token: " + self.user_token)
                self.change_layout("register")
                sleep(10)
                self.change_layout("scan")
            # After getting the finished command, discard the registered user token to deactivate the cart.
            elif(decoded_data.find("finished%") >= 0):
                given_token = decoded_data.split("%")[1]
                if(given_token == self.user_token):
                    print("\nPayment Received Successfully!")
                    print("Your cart discarded successfully!")
                    self.change_layout("payment")
                    sleep(2)
                    # Notify the customer via the thermal printer.
                    self.print_status(True)
                    self.change_layout("home")
                    self.user_token = ""
            # Get the product barcode.
            else:
                if(self.user_token != ""):
                    self.new_product_barcode = decoded_data
                    print("New Product Barcode: " + self.new_product_barcode)
                    self.product_menu_activate = True
                    if(self.product_menu_activate == True):
                        self.change_layout("barcode")
                        while(self.product_menu_activate == True):
                            self.product_menu()
                else:
                    print("\nPlease scan your unique account QR code to register your cart.") 
        sleep(1)
        
    # Make an HTTP GET request to the AIoT Shopping Assistant web application with the obtained barcode.
    def make_get_request(self, barcode, com="add"):
        path = "http://192.168.1.22/AIoT_Shopping_Assistant/assets/barcode.php?table={}&barcode={}&com={}".format(self.user_token, barcode, com)
        # Make an HTTP GET request.
        request = urequests.get(path)
        print("\nURL => "+path)
        print("App Response => ")
        print(request.text)
        sleep(1)
    
    # Display the product menu (interface) when the scanner detects a new product barcode.
    def product_menu(self):
        print("Press: Button (A) -> Add | Button (B) -> Remove")
        sleep(1)
        if(self.button_A.value() == False):
            self.make_get_request(self.new_product_barcode, "add")
            self.change_layout("add")
            sleep(10)
            self.change_layout("scan")
            self.product_menu_activate = False
        if(self.button_B.value() == False):
            self.make_get_request(self.new_product_barcode, "remove")
            self.change_layout("remove")
            sleep(10)
            self.change_layout("scan")
            self.product_menu_activate = False
            
    # If requested, print the current device status via the tiny (embedded) thermal printer.
    def print_status(self, payment=False):
        if(self.button_C.value() == False):
            print("\nPrinting the device status...")
            # Change the thermal printer hardware settings to obtain smoother prints depending on the targeted feature.
            if(self.user_token == ""):
                self.printer = Adafruit_Thermal(bus=6, heattime=155, heatdots=1, heatinterval=1)
                self.printer.printBMPImage('assets/printer_chatgpt.bmp')
                self.printer = Adafruit_Thermal(bus=6, heattime=120, heatdots=5, heatinterval=40)
                self.printer.justify('C')
                self.printer.setSize('L')
                self.printer.println("AIoT")
                self.printer.println("Shopping")
                self.printer.println("Assistant")
                self.printer.println("Status\n\n")
                self.printer.justify('L')
                self.printer.setSize('S')
                self.printer.boldOn()
                self.printer.println("Please scan")
                self.printer.println("your unique")
                self.printer.println("account QR code")
                self.printer.println("to activate your")
                self.printer.println("cart and start")
                self.printer.println("shopping!\n\n")
                self.printer.boldOff()
                self.printer.justify('R')
                self.printer.setSize('M')
                self.printer.inverseOn()
                self.printer.println(" Have a ")
                self.printer.println(" Great ")
                self.printer.println(" Day :) \n")
                self.printer.inverseOff()
                self.printer = Adafruit_Thermal(bus=6, heattime=155, heatdots=1, heatinterval=1)
                self.printer.printBMPImage('assets/printer_scan.bmp')
                self.printer.feed(5)
            elif(self.user_token != ""):
                self.printer = Adafruit_Thermal(bus=6, heattime=155, heatdots=1, heatinterval=1)
                self.printer.printBMPImage('assets/printer_chatgpt.bmp')
                self.printer = Adafruit_Thermal(bus=6, heattime=120, heatdots=5, heatinterval=40)
                self.printer.justify('C')
                self.printer.setSize('L')
                self.printer.println("AIoT")
                self.printer.println("Shopping")
                self.printer.println("Assistant")
                self.printer.println("Status\n\n")
                self.printer.justify('L')
                self.printer.setSize('S')
                self.printer.boldOn()
                self.printer.println("Your cart is")
                self.printer.println("registered and")
                self.printer.println("initialized")
                self.printer.println("successfully.")
                self.printer.println("Please scan a")
                self.printer.println("product barcode")
                self.printer.println("to change")
                self.printer.println("the cart")
                self.printer.println("items!\n\n")
                self.printer.println("Registered Token:")
                self.printer.doubleHeightOn()
                self.printer.println(self.user_token)
                self.printer.println("\n")
                self.printer.doubleHeightOff()
                self.printer.boldOff()
                self.printer.justify('R')
                self.printer.setSize('M')
                self.printer.inverseOn()
                self.printer.println(" Have a ")
                self.printer.println(" Great ")
                self.printer.println(" Day :) \n")
                self.printer.inverseOff()
                self.printer = Adafruit_Thermal(bus=6, heattime=155, heatdots=1, heatinterval=1)
                self.printer.printBMPImage('assets/printer_cart.bmp')
                self.printer.feed(5)
        # If the customer places an order successfully:
        if(payment == True):
            self.printer = Adafruit_Thermal(bus=6, heattime=155, heatdots=1, heatinterval=1)
            self.printer.printBMPImage('assets/printer_chatgpt.bmp')
            self.printer = Adafruit_Thermal(bus=6, heattime=120, heatdots=5, heatinterval=40)
            self.printer.justify('C')
            self.printer.setSize('L')
            self.printer.println("AIoT")
            self.printer.println("Shopping")
            self.printer.println("Assistant")
            self.printer.println("Status\n\n")
            self.printer.justify('L')
            self.printer.setSize('S')
            self.printer.boldOn()
            self.printer.println("Payment for")
            self.printer.println("your latest")
            self.printer.println("order is received")
            self.printer.println("successfully via")
            self.printer.println("your dashboard")
            self.printer.println("on the web")
            self.printer.println("application.")
            self.printer.println("Please scan")
            self.printer.println("your account")
            self.printer.println("QR code to")
            self.printer.println("activate your")
            self.printer.println("cart for")
            self.printer.println("your next")
            self.printer.println("order!\n\n")
            self.printer.println("Removed Token:")
            self.printer.doubleHeightOn()
            self.printer.println(self.user_token)
            self.printer.println("\n")
            self.printer.doubleHeightOff()
            self.printer.boldOff()
            self.printer.justify('R')
            self.printer.setSize('M')
            self.printer.inverseOn()
            self.printer.println(" Have a ")
            self.printer.println(" Great ")
            self.printer.println(" Day :) \n")
            self.printer.inverseOff()
            self.printer = Adafruit_Thermal(bus=6, heattime=155, heatdots=1, heatinterval=1)
            self.printer.printBMPImage('assets/printer_payment.bmp')
            self.printer.feed(5)
                           
    # Change the layout on the SSD1309 OLED transparent display depending on the given command.
    def change_layout(self, activated):
        self.display.clear_buffers()
        if(activated == "home"):
            self.adjust_color([0,1,0])
            self.display.draw_bitmap("assets/cart.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Please scan", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "your unique", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "account QR ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "code to    ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "activate   ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "your cart! ", self.light, invert=True)
        elif(activated == "scan"):
            self.adjust_color([0,1,1])
            self.display.draw_bitmap("assets/chatgpt.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Please scan", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "a product  ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "barcode to ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "change     ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "the cart   ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "items!     ", self.light, invert=True)
        elif(activated == "register"):
            self.adjust_color([0,0,1])
            self.display.draw_bitmap("assets/registered.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Account QR ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "code       ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "registered ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "and your   ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "cart       ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "is ready!  ", self.light, invert=True)        
        elif(activated == "barcode"):
            self.adjust_color([1,0,1])
            self.display.draw_bitmap("assets/barcode.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Press:     ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "           ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "Button (A) ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "-> Add     ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "Button (B) ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "-> Remove  ", self.light, invert=True)
        elif(activated == "add"):
            self.adjust_color([0,0,1])
            self.display.draw_bitmap("assets/add.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Given      ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "product    ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "added to   ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "your       ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "registered ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "cart!      ", self.light, invert=True)
        elif(activated == "remove"):
            self.adjust_color([1,0,0])
            self.display.draw_bitmap("assets/remove.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Given      ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "product    ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "removed    ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "from your  ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "registered ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "cart!      ", self.light, invert=True)
        elif(activated == "payment"):
            self.adjust_color([1,1,1])
            self.display.draw_bitmap("assets/payment.mono", 5, (self.display.height-48) // 2, 48, 48, invert=True)
            self.display.draw_text(48+10, (self.display.height-48) // 2, "Order      ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*1), "payment    ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*2), "received   ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*3), "and your   ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*4), "cart       ", self.light, invert=True)
            self.display.draw_text(48+10, ((self.display.height-48) // 2) + (self.light.height*5), "deactivated", self.light, invert=True)            
        self.display.present()
        sleep(1)
            
    def adjust_color(self, color):
        self.red.value(1-color[0])
        self.blue.value(1-color[1])
        self.green.value(1-color[2])
    
    def start(self):
        self.read_QR_barcode()
        self.print_status()
            

# Define the assistant object.
assistant = shopping_assistant()

while True:
    assistant.start()
