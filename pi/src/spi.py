from time import sleep;
from thread import start_new_thread;
from Queue import Queue;
import urllib2;

SERVER_ADDRESS = "192.168.0.7/Sticky%20Pi/web";

REPORT_SEND_INTERVAL = 10 * 60;
COMMAND_QUERY_INTERVAL = 1 * 60;

device_id = None;
device_name = "Unnamed Spi";
commands_queue = Queue();

def main():
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
		result = register();

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


def save_settings():
	print("saving");

def get_report():
	return "Some report that should be from a file";



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