<?php
$devices = fetch("SELECT * FROM devices");
?>

<?php echo_breadcrumbs_bar(array("Devices")); ?>

<div class="main">
	<div class="container">
		<div class="title">Connected devices:</div>
		<ul class="device-list">
			<?php
			foreach($devices as $device):
				$reports = fetch("SELECT Timestamp FROM reports"
					." WHERE DID=:id"
					." ORDER BY Timestamp DESC",
					array(":id" => $device["ID"]));
			?>

			<li id="device-<?php echo $device['ID']; ?>" class="device">
				<div class="device-info">
					<div class="device-name title">
						<a href="device/<?php echo $device['ID']; ?>"><?php echo $device["Name"]; ?></a></div>
					<div class="device-address">
						<span class="data-label">IP</span>
						<span class="data-value"><?php echo $device['Address']; ?></span>
					</div>
					<div class="device-lastheard">
						<span class="data-label">Last heard</span>
						<span class="data-value"><?php echo (isset($reports[0]))? $reports[0]['Timestamp'] : "Never"; ?></span>
					</div>
					<div class="device-reports">
						<span class="data-label">Reports</span>
						<span class="data-value">
							<span><b><?php echo count($reports);?></b> total</span>
						</span>
					</div>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
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

	var lastheard = document.querySelectorAll(".device .device-lastheard > .data-value");
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