<?php
global $devices;

if (!isset($devices))
	return error(500, "Illegal state!");


if (count($devices) == 0)
	REST::response_code("empty");


// Plain text representation //

if (!REST::preferred("text/html") && !REST::preferred("application/json")):
	foreach ($devices as $device)
		echo "(".$device["id"].") ".$device["name"]."\n";


// JSON representation //

elseif (!REST::preferred("text/html")):
	foreach ($devices as &$device) {
		$device["link"] = lnk("/devices/".$device["id"]);
		unset($device["address"]);
	}

	echo json_encode($devices);


// HTML representation //

else:
?>

<div class="main">
	<div class="container">
		<div class="title">Registered devices:</div>

		<ul class="device-list">
			<?php if (count($devices) == 0): ?>
				No devices
			<?php endif; ?>

			<?php
			foreach($devices as &$device):
				$report_info = fetch("SELECT Timestamp as lastheard, COUNT(Timestamp) as count"
					." FROM reports"
					." WHERE DID=:id"
					." ORDER BY Timestamp DESC"
					." LIMIT 1",
					array(":id" => $device["id"]));

				if (!isset($report_info[0]) || $report_info[0]["count"] == 0) {
					$device["lastheard"] = "Never";
					$device["reportcount"] = 0;
				}
				else {
					$device["lastheard"] = $report_info[0]["lastheard"];
					$device["reportcount"] = $report_info[0]["count"];
				}
			?>

			<li id="device-<?php echo $device["id"]; ?>" class="device">
				<div class="device-info">
					<div class="device-name title">
						<a href="<?php echo lnk("/devices/".$device["id"]); ?>"><?php echo $device["name"]; ?></a></div>
					<div class="device-address">
						<span class="data-label">IP</span>
						<span class="data-value"><?php echo $device["address"]; ?></span>
					</div>
					<div class="device-lastheard">
						<span class="data-label">Last heard</span>
						<span class="data-value"><?php echo $device["lastheard"]; ?></span>
					</div>
					<div class="device-reports">
						<span class="data-label">Reports</span>
						<span class="data-value">
							<span><b><?php echo $device["reportcount"]; ?></b> total</span>
						</span>
					</div>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<script type="text/javascript" src="<?php echo ASSETS_FOLDER.'/js/dates.js'; ?>"></script>
<script type="text/javascript">
(function() {
	var lastheard = document.querySelectorAll(".device-lastheard > .data-value");
	var now = new Date();
	for (var i = lastheard.length - 1; i >= 0; i--) {
		var e = lastheard[i];
		var v = e.innerHTML;

		if (v == "Never")
			continue;

		e.setAttribute('timestamp', v);
		var d = Date.from_mysql(v);
		e.innerHTML = new Date(now - d).inWords() + " ago";
	}
}())
</script>

<?php endif; ?>