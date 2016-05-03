from time import sleep, time;
from thread import start_new_thread;
from Queue import Queue;
import os;
import urllib2;
import json;
from bt_helper import *


SERVER_ADDRESS = "192.168.0.7/Remote-Spi/web";
REPORTS_FOLDER = "reports/";
SETTINGS_FILE = "settings.json";

REPORTS_TO_KEEP = 4;
REPORT_SEND_INTERVAL = 10 * 60;
COMMAND_QUERY_INTERVAL = 1 * 30;
CLIENT_SOCKET = None

device_id = None;
device_name = "Unnamed Spi";
auth_token = "";
last_report = None;
last_command_query = "SYNC";
commands_queue = Queue();

def main():
	load_settings();
	get_device_id();

	start_new_thread( loop_reporter, ());
	start_new_thread( loop_executer, ());
	start_new_thread( loop_cnc, ());
	loop_bt()



def request(url, data=None, method="GET"):
	global auth_token;

	request = urllib2.Request(url, data=data);
	request.add_header("Authorization", auth_token);
	request.get_method = lambda: method;
	response = urllib2.urlopen(request);
	return response.read();


def get_device_id():
	global device_id, COMMAND_QUERY_INTERVAL, auth_token;
	load_settings();

	register_attempts = 0
	while device_id is None:
		try:
			result = json.loads(register());

			if result < 0:
				sleep(COMMAND_QUERY_INTERVAL);
			else:
				device_id = result["id"];
				auth_token = result["token"];
				save_settings();
				return device_id;
		except Exception as ee:
			print ee;
			if(register_attempts < 10):
				print "Reconnection attempt #"+str(register_attempts+1)
				sleep(1)
				register_attempts += 1
				continue

			break
			

	return device_id;

def register():
	request = urllib2.Request("http://"+SERVER_ADDRESS+"/devices", data=device_name);
	request.add_header("Content-Type", "text/plain");
	request.add_header("Accept", "application/json");
	request.get_method = lambda: "POST";
	response = urllib2.urlopen(request);
	return response.read();

def rename(name):
	global device_id, device_name, auth_token;
	device_name = name;

	request = urllib2.Request("http://"+SERVER_ADDRESS+"/devices/"+str(device_id), data=device_name);
	request.add_header("Content-Type", "text/plain");
	request.add_header("Accept", "application/json");
	request.add_header("Authorization", auth_token);
	request.get_method = lambda: "PUT";
	response = urllib2.urlopen(request);
	return response.read();

def send_report(report=None):
	global device_id;

	if report is None:
		report = get_report();

	if not report:
		print "Nothing to report";
		return;

	print "Sending a report from device-"+str(device_id);
	request = urllib2.Request("http://"+SERVER_ADDRESS+"/devices/"+str(device_id)+"/reports", data=report);
	request.add_header("Content-Type", "text/plain");
	request.add_header("Accept", "application/json");
	request.add_header("Authorization", auth_token);
	request.get_method = lambda: "POST";
	response = urllib2.urlopen(request);
	return response.read();

def receive_commands():
	global last_command_query;
	print "Querying for commands";

	request = urllib2.Request("http://"+SERVER_ADDRESS+"/devices/"+str(device_id)+"/commands?since="+(last_command_query.replace(" ", "%20")));
	request.add_header("Content-Type", "text/plain");
	request.add_header("Accept", "application/json");
	request.add_header("Authorization", auth_token);
	response = urllib2.urlopen(request);

	since = response.info().getheader("X-Since");
	last_command_query = since;
	save_settings();

	result = response.read();
	cmds = json.loads(result);
	print "Received "+str(len(cmds))+" commands";
	return cmds;


def load_settings():
	global device_id, device_name, auth_token, last_report, last_command_query, SERVER_ADDRESS;

	try:
		with open(SETTINGS_FILE, 'r') as f:
		    settings = json.load(f)

		    device_id = settings["device_id"];
		    device_name = settings["device_name"];
		    auth_token = settings["auth_token"];
		    last_report = settings["last_report"];
		    last_command_query = settings["last_command_query"];
		    SERVER_ADDRESS = settings["server_hostname"];
	except:
		pass;

def save_settings():
	global device_id, device_name, auth_token, last_report, last_command_query, SERVER_ADDRESS;

	settings = {};
	settings["device_id"] = device_id;
	settings["device_name"] = device_name;
	settings["auth_token"] = auth_token;
	settings["last_report"] = last_report;
	settings["last_command_query"] = last_command_query;
	settings["server_hostname"] = SERVER_ADDRESS;

	with open(SETTINGS_FILE, 'w') as f:
	    json.dump(settings, f);


def add_report(content):
	with open(REPORTS_FOLDER + "report-" + str(int(time())) + ".txt", "w") as f:
		f.write(content);
		f.close();

def get_report():
	global last_report;

	reports = os.listdir(REPORTS_FOLDER);
	reports.sort(key=lambda x: os.path.getctime(REPORTS_FOLDER + x), reverse=True);

	if not reports:
		return None;

	filename = reports[-1];
	try:
		i = reports.index(last_report);

		if i == 0:
			return None;

		filename = reports[i-1];
	except:
		pass;

	content = open(REPORTS_FOLDER + filename, 'r').read();
	last_report = filename;
	save_settings();

	i = REPORTS_TO_KEEP - 1;
	l = len(reports);
	while i < l:
		os.remove(REPORTS_FOLDER + reports[i]);
		i += 1;

	return content;



#thread bt
def loop_bt():
	global CLIENT_SOCKET
	print "Starting bluetooth server socket"

	server_socket, port = establishBTSocket()

	while(True):
		client_sock, request = bindConnection(server_socket, port)
		CLIENT_SOCKET = client_sock

		# dispatch(request)
		cmd = ""
		try:
			cmd = json.loads(request)
		except ValueError:
			print "Invalid JSON received, skipping response, try again"
			continue
		commands_queue.put(cmd);

#thread cnc
def loop_cnc():
	print "starting CNC thread..."

	global commands_queue, COMMAND_QUERY_INTERVAL;
	#get_device_id();

	while True:
		cmds = receive_commands();
		for cmd in cmds:
			commands_queue.put(cmd);
		sleep(COMMAND_QUERY_INTERVAL);

#thread reporter
def loop_reporter():
	print "Starting Reporter thread..."

	global REPORT_SEND_INTERVAL;

	while True:
		send_report();
		sleep(REPORT_SEND_INTERVAL);

#thread exc
def loop_executer():
	from commands import dispatch
	print "Starting Executor thread..."

	global CLIENT_SOCKET
	while True:
		cmd = commands_queue.get(True);
		dispatch(CLIENT_SOCKET, cmd);
		commands_queue.task_done();
		print "Task completed"

#main
if __name__ == '__main__':
	main()
