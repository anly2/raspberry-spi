<?php

handle_item: {
if (count(REST::$ARGS) == 2) {
	if (REST::$REQUEST_METHOD == "GET"):
		$id = REST::$ARGS[1];

		$report = fetch("SELECT"
				." r.RID as id, r.DID as device_id, d.Name as device_name,"
				." r.Timestamp as timestamp, r.Content as content"
				." FROM reports as r"
				." INNER JOIN devices as d"
				." ON r.DID = d.ID"
				." WHERE RID=:id",
				array(":id"=>$id));

		if (!isset($report[0]))
			return error("not-found", "Report not found.");
		else
			$report = $report[0];


		// RENDER //

		if (!REST::preferred("text/html") && !REST::preferred("application/json")):

			echo "Report ".$report["id"]."\n";
			echo "Device: (".$report["device_id"].") ".$report["device_name"]."\n";
			echo "Date: ".$report["timestamp"]."\n";
			echo "\n";
			echo $report["content"];

		elseif (!REST::preferred("text/html")):
			echo json_encode($report);

		else:
		?>

		<?php echo_breadcrumbs_bar(array(
			lnk("/devices") => "Devices",
			lnk("/devices/".$report["device_id"]) => "Device ".$report["device_id"],
			lnk("/devices/".$report["device_id"]."/reports") => "Reports",
			"Report ".$report["id"])); ?>

		<div class="main">
			<div id="report-<?php echo $id; ?>" class="container">
				<div class="report-name title">
					<a href="<?php echo lnk('/reports/'.$report['id']); ?>">
						Report <?php echo $report["id"]; ?>
					</a>
				</div>

				<ul class="unstyled info-list">
					<li class="report-id">
						<span class="data-label">Report id:</span>
						<span class="data-value"><b><?php echo $report["id"]; ?></b></span>
					</li>

					<li class="report-device">
						<span class="data-label">Device name:</span>
						<span class="data-value">
							<a href="<?php echo lnk("/devices/".$report["device_id"]); ?>">
								<?php echo $report["device_name"]; ?>
							</a>
						</span>
					</li>

					<li class="report-absolute-date">
						<span class="data-label">Timestamp:</span>
						<span class="data-value"><?php echo $report["timestamp"]; ?></span>
					</li>

					<li class="report-relative-date">
						<span class="data-label">Timestamp:</span>
						<span class="data-value"><?php echo $report["timestamp"]; ?></span>
					</li>

					<li class="device-reports">
						<span class="data-label">Contents:</span>
						<span class="data-value"><br/>
							<pre><?php echo $report["content"]; ?></pre>
						</span>
					</li>
				</ul>
			</div>
		</div>

		<script type="text/javascript" src="<?php echo ASSETS_FOLDER.'/js/dates.js'; ?>"></script>
		<script type="text/javascript">
		(function() {
			var lastheard = document.querySelectorAll(".report-relative-date > .data-value");
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
	<?php
		endif;

	else:
		return error("bad-method", "Unsupported HTTP Method");

	endif;
}}

handle_subitem: {
if (count(REST::$ARGS) == 3) {
	if (REST::$REQUEST_METHOD == "GET"):
		
		ob_clean(); //clear the header

		$query = REST::$ARGS[2];

		$select = function($field) {
			$reports = fetch("SELECT $field FROM reports WHERE RID=:id",
				array(":id" => REST::$ARGS[1]));

			if (isset($reports[0]))
				return $reports[0][$field];
		};


		if ($query == "exists") {
			echo ($select("RID") == REST::$ARGS[1])? "true" : "false";
		}

		if ($query == "date")
			echo $select("Timestamp");

		if ($query == "content")
			echo $select("Content");

		exit;

	else:
		return error("bad-method", "Unsupported HTTP Method");

	endif;
}}
?>