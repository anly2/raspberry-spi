from wifi import Cell, Scheme

STORED_SCHEME_NAME = 'last_network'

def connect(esid, passkey=None, intf='wlan0'):
	cells = Cell.where(intf, lambda c: c.ssid == esid);

	if len(cells) == 0:
		raise LookupError('Network was not found');

	if len(cells) > 1:
		raise LookupError('Sorry, network SSID is ambiguous');

	scheme = Scheme.for_cell(intf, STORED_SCHEME_NAME, cells[0], passkey);
	scheme.save();
	scheme.activate();