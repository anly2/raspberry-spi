from time import sleep, time;
from thread import start_new_thread;
from Queue import Queue;
import os;
import urllib2;
import json;
from bt_helper import *


SERVER_ADDRESS = "192.168.0.7/Sticky%20Pi/web";
REPORTS_FOLDER = "reports/";
SETTINGS_FILE = "settings.json";

REPORTS_TO_KEEP = 4;
REPORT_SEND_INTERVAL = 10 * 60;
COMMAND_QUERY_INTERVAL = 1 * 60;
CLIENT_SOCKET = None

device_id = None;
device_name = "Unnamed Spi";
last_report = None;
commands_queue = Queue();

def main():
	load_settings();
	get_device_id();

	start_new_thread( loop_reporter, ());
	start_new_thread( loop_executer, ());
	start_new_thread( loop_cnc, ());
	loop_bt()



def request(url, data=None, method="GET"):
	request = urllib2.Request(url, data=data);
	request.get_method = lambda: method;
	response = urllib2.urlopen(request);
	return response.read();


def get_device_id():
	global device_id, COMMAND_QUERY_INTERVAL;
	load_settings();

	while device_id is None:
		try:
			result = int(register());

			if result < 0:
				sleep(COMMAND_QUERY_INTERVAL);
			else:
				device_id = result;
				save_settings();
				return device_id;
		except:
			pass

	return device_id;

def register():
	return request("http://"+SERVER_ADDRESS+"/device/register", data=device_name, method="POST")

def rename(name):
	global device_id, device_name;
	device_name = name;
	return request("http://"+SERVER_ADDRESS+"/device/"+str(device_id), data=device_name, method="PUT");

def send_report(report=None):
	global device_id;

	if report is None:
		report = get_report();

	if not report:
		print "Nothing to report";
		return;

	print "Sending a report from device-"+str(device_id);
	return request("http://"+SERVER_ADDRESS+"/device/"+str(device_id)+"/report", data=report, method="POST");

def receive_commands():
	return [];
	#not implemented yet


def load_settings():
	global device_id, device_name, last_report, SERVER_ADDRESS;

	try:
		with open(SETTINGS_FILE, 'r') as f:
		    settings = json.load(f)

		    device_id = settings["device_id"];
		    device_name = settings["device_name"];
		    last_report = settings["last_report"];
		    SERVER_ADDRESS = settings["server_hostname"];
	except:
		pass;

def save_settings():
	global device_id, device_name, last_report, SERVER_ADDRESS;

	settings = {};
	settings["device_id"] = device_id;
	settings["device_name"] = device_name;
	settings["last_report"] = last_report;
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
			print request
			cmd = json.loads(request)
		except ValueError:
			print "Invalid JSON received, skipping response, try again"
			print cmd
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
