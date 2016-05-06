<?php
global $device;

if (!isset($device))
	return error(500, "Illegal state!");


if (!REST::preferred("text/html") && !REST::preferred("application/json")):
	if (!$device)
		return error("not-found", "Device not found.");

	echo "Device ID:\t".$device["id"]."\n";
	echo "Name:\t".$device["name"]."\n";
	echo "Address:\t".$device["address"]."\n";
	echo "Last heard:\t".$device["lastheard"]."\n";
	echo "Reports:\t".$device["reportcount"]."\n";

elseif (!REST::preferred("text/html")):
	if (!$device) {
		REST::response_code("not-found");
		echo "NULL";
		return;
	}

	echo json_encode($device);

else:
	if (!$device):
		REST::response_code("not-found");
		?>

		<?php echo_breadcrumbs_bar(array(lnk("/devices") => "Devices", "Not found")); ?>

		<div class="main">
			<div class="container">
				<div class="title">
					Sorry, the device was not found!
				</div>
			</div>
		</div>

		<?php
		return;

	else:
		$id = $device["id"];
	?>

	<?php echo_breadcrumbs_bar(array(lnk("/devices") => "Devices", "Device $id")); ?>

	<div class="main">
		<div id="device-<?php echo $id; ?>" class="container">
			<div class="device-name title">
				<a href="<?php echo lnk('/devices/'.$device["id"]); ?>">
					<?php echo $device["name"]; ?>
				</a>
			</div>

			<ul class="unstyled info-list">
				<li class="device-id">
					<span class="data-label">Device id:</span>
					<span class="data-value"><b><?php echo $id; ?></b></span>
				</li>

				<li class="device-address">
					<span class="data-label">Address:</span>
					<span class="data-value"><?php echo $device['address']; ?></span>
				</li>

				<li class="device-lastheard">
					<span class="data-label">Last heard:</span>
					<span class="data-value"><?php echo $device["lastheard"]; ?></span>
				</li>

				<li class="device-reports">
					<span class="data-label">Reports:</span>
					<span class="data-value"><b><?php echo $device["reportcount"]; ?></b> total</span>
				</li>

				<li class="device-reports-list-link">
					<span class="data-label"> </span>
					<span class="data-value"><a href="<?php echo lnk("/devices/$id/reports"); ?>">List of reports</a></span>
				</li>

				<li class="device-commands">
					<span class="data-label">Commands:</span>
					<span class="data-value"><a href="<?php echo lnk("/commands")."?device=".$id; ?>"><button>Issue command</button></a></span>
				</li>

				<li class="device-report">

					<span class="data-label">Display report:</span>
					<span class="data-value">
						<select id="report_selection" autocomplete="off"
							onchange="show_report(this.value);">
							<option value="-1">None</option>

							<?php foreach(
								fetch("SELECT RID as id FROM reports WHERE DID=:id ORDER BY Timestamp DESC", array(":id" => $id))
								as $report):
							?>
								<option value="<?php echo $report["id"] ?>">Report <?php echo $report["id"] ?></option>
							<?php endforeach; ?>
						</select>

						<button onclick="--document.getElementById('report_selection').selectedIndex; update_report();">&uarr;</button>
						<button onclick="++document.getElementById('report_selection').selectedIndex; update_report();">&darr;</button>

						<div class="report" id="shown_report">
							<div>Report date: <span class="report-date" id="shown_report_date"></span></div>
							<pre class="report-content" id="shown_report_content"></pre>
						</div>
					</span>
				</li>
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


	<script type="text/javascript" src="<?php echo ASSETS_FOLDER.'/js/call.js'; ?>"></script>
	<script type="text/javascript">
	(function() {
		var report = document.getElementById("shown_report");
		var report_date = document.getElementById("shown_report_date");
		var report_content = document.getElementById("shown_report_content");

		window.show_report = function(id) {
			if (id < 0) {
				report.style.display = "none";
				return;
			}

			call("<?php echo lnk("/reports"); ?>/" + id + "/exists", function(content) {
				report.style.display = (content == "true")? "block" : "none";
			});

			call("<?php echo lnk("/reports"); ?>/" + id + "/date",
				function(content){
					var date = Date.from_mysql(content);
					report_date.innerHTML = date.toLocaleString() + " &nbsp; (" + new Date(new Date() - date).inWords() + " ago)";
				}
			);
			call("<?php echo lnk("/reports"); ?>/" + id + "/content", function(content){
				report_content.innerHTML = content;
			});
		}

		window.update_report = function() {
			show_report(document.getElementById("report_selection").value);
		};
		window.update_report();
	}())
	</script>

	<?php
	endif;
endif;
?>