import subprocess
from bt_helper import *
import os
import spi;

PCAP_FILE = "pcap-01.cap";


def dispatch(client_sock,cmd):
	print("Dispatching command...");
	global dispatch_handlers
	if cmd["action"] == "download_pcap":
		print "DOWNLOAD"
		file_download(client_sock,cmd["args"])
	else:
		dispatch_handlers[cmd["action"]](cmd["args"])



airodump_proc = None

def config_network(*args):
	pass

def config_c2_server(*args):
	args = args[0][0];

	spi.load_settings();

	if not args["port"]:
		args["port"] = "80";

	if args["address"]:
		spi.SERVER_ADDRESS = args["address"] + ":" + args["port"];
		spi.SERVER_ADDRESS += "/Sticky Pi/web";
		spi.save_settings();

	if args["identifier"]:
		spi.rename(args["identifier"]);


def ping(*args):
	print "Starting ping procedure"
	args = args[0]
	if len(args) <  2:
		print "less than 2 args"
		print args
		return
	cmd = ["ping"]
	if args[1] != "":
		cmd.append("-c " + args[1])
	if args[2] != "":
		cmd.append("-i " + args[2])
	cmd.append(args[0]) 

	ping_response = subprocess.Popen(cmd, stdout=subprocess.PIPE).stdout.read()
	print "ping finished, adding report"
	spi.add_report(ping_response)
	print ping_response
	#GENERATE REPORT


def nmap(*args):
	args = args[0]
	if len(args)<1:
		return
	cmd = ["nmap", "-sS"]
	address = args[0]
	if args[1] !="":
		address += "/"+args[1]
	cmd.append(address)
	
	print cmd
	nmap_response = subprocess.Popen(cmd, stdout=subprocess.PIPE).stdout.read()
	spi.add_report(ping_response)
	print nmap_response

def airodump(*args):
	global airodump_proc
	args = args[0]
	airmon = subprocess.Popen(["airmon-ng", "start", "wlan0"], stdout=subprocess.PIPE)
	airmon.stdout.read()
	cmd = ["airodump-ng", "mon0", "-w","pcap", "--output-format","pcap"]
	if args[0] != "":
		cmd.append("-c " + args[0])

	if args[1] != "":
		cmd.append("--bssid " + args[1])

	FNULL = open(os.devnull, 'w')
	if(os.path.isfile(PCAP_FILE)):
		os.remove(PCAP_FILE)
		
	airodump_proc = subprocess.Popen(cmd, stdout=FNULL, stderr=subprocess.STDOUT)	


def stop_airodump(*args):
	global airodump_proc
	airodump_proc.terminate()
	airmon = subprocess.Popen(["airmon-ng", "stop", "mon0"], stdout=subprocess.PIPE)
	airmon.stdout.read()	
	print "terminated Airmon and cleared monitoring interface"

def file_download(client_sock, *args):
	# client_sock = CLIENT_SOCKET
	fileContent = ""
	if os.path.isfile(PCAP_FILE):
		print "opening file to read"
		with open(PCAP_FILE,"rb") as f:
		    fileContent = f.read()
		client_sock.sendall(fileContent)
		client_sock.close()
		print "Removing file"
		os.remove(f.name)
	else:
		client_sock.send("a")
		client_sock.close()

dispatch_handlers = {
	"config_c2" : config_c2_server,
	"config_network" : config_network,
	"download_pcap" : file_download,
	"airodump" : airodump,
	"stop_airodump" : stop_airodump,
	"nmap_sS" : nmap,
	"ping" : ping
}