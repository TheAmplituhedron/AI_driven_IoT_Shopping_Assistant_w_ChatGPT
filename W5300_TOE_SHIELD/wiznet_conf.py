import network
import pyb

"""
    A class to configuration the W5300
"""
class wiznet5k_w5300:
    def __init__ (self):      
        self.nic = network.WIZNET5K() 
        self.nic.active(True)
        pyb.delay(300)

    def w5300_set_ip (self, ip_addr, gw_addr, netmask, dns_svr):
        self.nic.ifconfig((ip_addr, gw_addr, netmask, dns_svr))
        print("W5300 STATIC IP:  ('192.168.1.20', '255.255.255.0', '192.168.1.1', '8.8.8.8')")
        if(self.nic.isconnected() == False):
            print("\nW5300: Ethernet connection problem!")
        else:
            print("\nW5300: Connected successfully!")
        pyb.delay(500)
        
    def w5300_set_dhcp (self):
        self.nic.ifconfig('dhcp')
        print("device DHCP IP is:", self.nic.ifconfig())