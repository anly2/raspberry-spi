from time import sleep;
from thread import start_new_thread;
from Queue import Queue;
import os;
import urllib2;
import json;

SERVER_ADDRESS = "192.168.0.7/Sticky%20Pi/web";
REPORTS_FOLDER = "reports/";
SETTINGS_FILE = "settings.json";

REPORTS_TO_KEEP = 4;
REPORT_SEND_INTERVAL = 10 * 60;
COMMAND_QUERY_INTERVAL = 1 * 60;

device_id = None;
device_name = "Unnamed Spi";
last_report = None;
commands_queue = Queue();

def __main__():
	load_settings();
	start_new_thread( loop_bt, ());
	start_new_thread( loop_reporter, ());
	start_new_thread( loop_executer, ());
	start_new_thread( loop_cnc, ());



def request(url, data=None, method="GET"):
	request = urllib2.Request(url, data=data);
	request.get_method = lambda: method;
	response = urllib2.urlopen(request);
	return response.read();


def get_device_id():
	while device_id is None:
		result = int(register());

		if result < 0:
			sleep(COMMAND_QUERY_INTERVAL);
		else:
			device_id = result;
			save_settings();
			break;

def register():
	return requests.post("http://"+SERVER_ADDRESS+"/device/register", data=device_name);

def send_report():
	report = get_report();
	return requests.post("http://"+SERVER_ADDRESS+"/device/"+device_id+"/report", data=report);

def receive_commands():
	skip();
	#not implemented yet

def dispatch(cmd):
	print("Dispatching command...");


def load_settings():
	global device_id, device_name, last_report;

	try:
		with open(SETTINGS_FILE, 'r') as f:
		    settings = json.load(f)

		    device_id = settings["device_id"];
		    device_name = settings["device_name"];
		    last_report = settings["last_report"];
	except:
		pass;

def save_settings():
	global device_id, device_name, last_report;

	settings = {};
	settings["device_id"] = device_id;
	settings["device_name"] = device_name;
	settings["last_report"] = last_report;

	with open(SETTINGS_FILE, 'w') as f:
	    json.dump(settings, f);

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
	skip();
#	while bind conn

#thread cnc
def loop_cnc():
	get_device_id();
	while True:
		cmds = receive_commands();
		for cmd in cmds:
			commands_queue.put(cmd);
		sleep(COMMAND_QUERY_INTERVAL);

#thread reporter
def loop_reporter():
	get_device_id();
	while True:
		send_report();
		sleep(REPORT_SEND_INTERVAL);

#thread exc
def loop_executer():
	while True:
		if commands_queue.empty():
			sleep(COMMAND_QUERY_INTERVAL);
			continue;

		cmd = commands_queue.get();
		dispatch(cmd);
		commands_queue.task_done();