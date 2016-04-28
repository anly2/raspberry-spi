import subprocess
import bt_helper

airodump_proc = None

def config_network(*args):
	skip

def config_c2_server(*args):
	skip

def ping(*args):
	if len(args) <  2:
		return
	cmd = ["ping"]
	if args[1] != "":
		cmd.append("-c " + args[1])
	if args[2] != "":
		cmd.append("-i " + args[2])
	cmd.append(args[0]) 

	ping_response = subprocess.Popen(cmd, stdout=subprocess.PIPE).stdout.read()
	print ping_response
	#GENERATE REPORT


def nmap(*args):
	if len(args)<1:
		return
	cmd = ["nmap", "-sS"]
	address = args[0]
	if args[1] !="":
		address += "/"+args[1]
	cmd.append(address)

	nmap_response = stubprocess.Popen(cmd, stdout=subprocess.PIPE).stdout.read()
	print nmap_response

def airodump(*args):
	global airodump_proc

	airmon = subprocess.Popen(["airmon-ng", "start", "wlan0"], stdout=subprocess.PIPE)
	airmon.stoud.read()
	cmd = ["airodump-ng", "mon0", "-w pcap", "--output-format pcap"]
	if args[0] != "":
		cmd.append("-c " + args[0])

	if args[1] != "":
		cmd.append("--bssid " + args[1])

	airodump_proc = subprocess.Popen(cmd, stdout=subprocess.PIPE)	


def stop_airodump():
	global airodump_proc
	airodump_proc.terminate()
	airmon = subprocess.Popen(["airmon-ng", "stop", "mon0"], stdout=subprocess.PIPE)
	print "terminated Airmon and cleared monitoring interface"

def file_download(*args):
	client_sock = bt_helper.CLIENT_SOCK
	fileContent = ""
	with open("text.txt","rb") as f:
	    fileContent = f.read()
	client_sock.send(fileContent)
	client_sock.close()
