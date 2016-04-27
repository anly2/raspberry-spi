
(function() {
	var articulate = function(t, m) {
		return t + " " + m + (t==1? "" : "s");
	}
	
	Date.prototype.inWords = function() {
		var date = this;
		var s = "";
		var t = 0;

		t = Math.floor(date.getHours() / 24);
		if (t > 0)
			s += articulate(t, "day") + " ";

		t = date.getHours() % 24;
		if (t > 0)
			s += articulate(t, "hour") + " ";

		t = date.getMinutes();
		if (t > 0)
			s += articulate(t, "minute") + " ";

		t = date.getSeconds();
		if (t > 0)
			s += articulate(t, "second") + " ";

		return s.trim();
	}

	Date.from_mysql = function(value) {
		var t = value.split(/[- :]/);
		return new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
	}
}());