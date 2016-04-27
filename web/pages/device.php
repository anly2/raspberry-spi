<?php
$device = fetch("SELECT * FROM devices"
					." WHERE ID=:id",
					array(":id" => REST::$ARGS[1]));

if (!isset($device[0])) {
	redirect(HOME."/device/not-found");
	exit;
}
else
	$device = $device[0];

$id = $device["ID"];
$reports = fetch("SELECT * FROM reports"
	." WHERE DID=:id"
	." ORDER BY Timestamp DESC",
	array(":id" => $device["ID"]));
?>

<?php echo_breadcrumbs_bar(array(HOME."/devices" => "Devices", "Device $id")); ?>


<div class="main">
	<div id="device-<?php echo $id; ?>" class="container">
		<div class="device-name title">
			<a href="<?php echo HOME.'device/'.$device['ID']; ?>"><?php echo $device["Name"]; ?></a>
		</div>
		
		<ul class="unstyled info-list">
			<li class="device-id">
				<span class="data-label">Device id:</span>
				<span class="data-value"><b><?php echo $id; ?></b></span>
			</li>

			<li class="device-address">
				<span class="data-label">Address:</span>
				<span class="data-value"><?php echo $device['Address']; ?></span>
			</li>

			<li class="device-lastheard">
				<span class="data-label">Last heard:</span>
				<span class="data-value"><?php echo (isset($reports[0]))? $reports[0]['Timestamp'] : "Never"; ?></span>
			</li>

			<li class="device-reports">
				<span class="data-label">Reports:</span>
				<span class="data-value"><b><?php echo count($reports);?></b> total</span>
			</li>
		</dl>
	</div>
</div>

<script type="text/javascript">
(function() {
	var articulate = function(t, m) {
		return t + " " + m + (t==1? "" : "s");
	}
	var inWords = function(date) {
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
	var asDate = function(value) {
		var t = "2010-06-09 13:12:01".split(/[- :]/);
		return new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
	}

	var lastheard = document.querySelectorAll(".device-lastheard > .data-value");
	var now = new Date();
	for (var i = lastheard.length - 1; i >= 0; i--) {
		var e = lastheard[i];
		var v = e.innerHTML;

		if (v == "Never")
			continue;

		e.setAttribute('timestamp', v);
		var d = asDate(v);
		e.innerHTML = inWords(new Date(now - d)) + " ago";
	}
}())
</script>