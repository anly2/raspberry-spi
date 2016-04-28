<?php
if (REST::$ARGS[1] == "register") {
	$name = file_get_contents("php://input");
	$addr = $_SERVER['REMOTE_ADDR'];

	$id = fetch("SELECT MAX(ID)+1 as id FROM devices")[0]["id"];

	global $db;
	$q = $db->prepare("INSERT INTO devices (ID, Name, Address) VALUES (:id, :name, :addr)");
	if (!$q->execute(array(":id"=>$id, ":name"=>$name, ":addr" => $addr)))
		echo "-1";//, var_dump($q->errorInfo());
	else
		echo $id;

	exit;
}


if ($_SERVER["REQUEST_METHOD"] == "PUT") {
	$name = file_get_contents("php://input");
	$addr = $_SERVER['REMOTE_ADDR'];

	$id = REST::$ARGS[1];

	global $db;
	$q = $db->prepare("UPDATE devices SET Name=:name, Address=:addr WHERE ID=:id");
	$r = $q->execute(array(":id"=>$id, ":name"=>$name, ":addr" => $addr));

	// var_dump($q->errorInfo());
	echo (!$r || $q->rowCount() == 0) ? "failure" : "success";
	exit;
}


$device = fetch("SELECT * FROM devices WHERE ID=:id",
				array(":id" => REST::$ARGS[1]));

if (!isset($device[0])) {
	redirect(HOME."/device/not-found");
	exit;
}
else
	$device = $device[0];

$id = $device["ID"];
$reports = fetch("SELECT RID as id, Timestamp FROM reports"
	." WHERE DID=:id"
	." ORDER BY Timestamp DESC",
	array(":id" => $device["ID"]));
?>

<?php echo_breadcrumbs_bar(array(HOME."/devices" => "Devices", "Device $id")); ?>


<div class="main">
	<div id="device-<?php echo $id; ?>" class="container">
		<div class="device-name title">
			<a href="<?php echo HOME.'/device/'.$device['ID']; ?>"><?php echo $device["Name"]; ?></a>
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

			<li class="device-report">
				<span class="data-label">Display report:</span>
				<span class="data-value">
					<select id="report_selection" autocomplete="off"
						onchange="show_report(this.value);">
						<option value="-1">None</option>

						<?php foreach($reports as $report): ?>
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
		</dl>
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

		call("<?php echo HOME.'/report/'; ?>" + id + "/exists", function(content) {
			report.style.display = (content == "true")? "block" : "none";
		});

		call("<?php echo HOME.'/report/'; ?>" + id + "/date",
			function(content){
				var date = Date.from_mysql(content);
				report_date.innerHTML = date.toLocaleString() + " &nbsp; (" + new Date(new Date() - date).inWords() + " ago)";
			}
		);
		call("<?php echo HOME.'/report/'; ?>" + id + "/content", function(content){
			report_content.innerHTML = content;
		});
	}

	window.update_report = function() {
		show_report(document.getElementById("report_selection").value);
	};
	window.update_report();
}())
</script>