<?php
function auth_token() {
	return bin2hex(openssl_random_pseudo_bytes(16));
}

handle_list: {
if (REST::$URI == "devices") {
	if (REST::$REQUEST_METHOD == "GET"):
		$devices = fetch("SELECT ID as id, Name as name, Address as address FROM devices");

		if (count($devices) == 0)
			REST::response_code("empty");

		if (!REST::preferred("text/html") && !REST::preferred("application/json")):
			foreach ($devices as $device)
				echo "(".$device["id"].") ".$device["name"];

		elseif (!REST::preferred("text/html")):
			foreach ($devices as &$device) {
				$device["link"] = lnk("/devices/".$device["id"]);
				unset($device["address"]);
			}

			echo json_encode($devices);
		else:
?>

<div class="main">
	<div class="container">
		<div class="title">Registered devices:</div>
		<ul class="device-list">
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

<?php
		endif;

	elseif (REST::$REQUEST_METHOD == "POST"):
		$registration = fetch("SELECT Value as state FROM appinfo WHERE Field='registration_state'");

		if (!$registration || !isset($registration[0])) {
			REST::response_code(500);
			return error("Failed to check registration state.", false);
		}

		$registration_state = $registration[0]["state"];

		if (!$registration_state) {
			REST::response_code(503);
			return error("Registration is closed.", false);
		}


		// REGISTER //

		$raw = file_get_contents("php://input");
		switch ($_SERVER["CONTENT_TYPE"]) {
			case "text/plain":
				$name = $raw;
				break;
			case "application/json":
				$data = json_decode($raw);
				$name = isset($data["name"])? $data["name"] : false;
				break;
			case "application/x-www-form-urlencoded":
				parse_str($raw, $data);
				$name = isset($data["name"])? $data["name"] : false;
				break;
			default:
				REST::response_code(400);
				return error("Content type not supprted.", false);
		}

		if ($name === false) {
			REST::response_code(400);
			return error("Name field is missing.", false);
		}

		$addr = $_SERVER['REMOTE_ADDR'];
		$token = auth_token();

		$id = fetch("SELECT MAX(ID)+1 as id FROM devices")[0]["id"];
		if (!$id) $id = 1;

		global $db;
		$q = $db->prepare("INSERT INTO devices (ID, Name, Address, Auth_Token) VALUES (:id, :name, :addr, :token)");
		if (!$q->execute(array(":id"=>$id, ":name"=>$name, ":addr" => $addr, ":token" => $token))) {
			REST::response_code("failure");
			return error(REST::preferred("text/html")? "Failed to register the device." : "-1", false);
		}


		// REPLY / RENDER //

		REST::response_code("created");
		header('Location: '.lnk("/devices/$id"));
		header('Authorization: '.$token);

		if (!REST::preferred("text/html") && !REST::preferred("application/json"))
			echo $id . " " . $token;
		elseif (!REST::preferred("text/html"))
			echo json_encode(array("id" => $id, "token" => $token));
		else
			echo '<script type="text/javascript">window.location.href="'.lnk("/devices/$id").'"</script>"';

	else:
		REST::response_code("bad-method");
		return error("Unsupported HTTP Method", false);

	endif;
}}

handle_item: {
if (count(REST::$ARGS) == 2) {
	if (REST::$REQUEST_METHOD == "GET"):
		$device = fetch("SELECT ID as id, Name as name, Address as address FROM devices WHERE ID=:id",
			array(":id" => REST::$ARGS[1]));
		$device = isset($device[0])? $device[0] : false; //use ?:

		if ($device) {
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
		}


		// RENDER //

		if (!REST::preferred("text/html") && !REST::preferred("application/json")):
			if (!$device) {
				REST::response_code("not-found");
				error("Device not found.", false);
				return;
			}

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
			<a href="<?php echo lnk('/devices/'.$device['id']); ?>">
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

<?php
			endif;
		endif;

	elseif (REST::$REQUEST_METHOD == "PUT"):
		$id = REST::$ARGS[1];

		$headers = apache_request_headers();
		if (!isset($headers["Authorization"])) {
			REST::response_code(401);
			return error("Unauthorized!", false);
		}

		$token = $headers["Authorization"];

		$auth_info = fetch("SELECT true FROM devices WHERE ID=:id AND Auth_Token=:token",
			array(":id" => $id, ":token" => $token));

		if (!$auth_info || !isset($auth_info[0])) {
			REST::response_code(401);
			return error("Unauthorized!", false);
		}


		// UPDATE //

		$raw = file_get_contents("php://input");
		switch ($_SERVER["CONTENT_TYPE"]) {
			case "text/plain":
				$name = $raw;
				break;
			case "application/json":
				$data = json_decode($raw);
				$name = isset($data["name"])? $data["name"] : false;
				break;
			case "application/x-www-form-urlencoded":
				parse_str($raw, $data);
				$name = isset($data["name"])? $data["name"] : false;
				break;
			default:
				REST::response_code(400);
				return error("Content type not supprted.", false);
		}

		if ($name === false) {
			REST::response_code(400);
			return error("Name field is missing.", false);
		}

		$addr = $_SERVER['REMOTE_ADDR'];


		global $db;
		$q = $db->prepare("UPDATE devices SET Name=:name, Address=:addr WHERE ID=:id AND Auth_Token=:token");
		$r = $q->execute(array(":id"=>$id, ":name"=>$name, ":addr" => $addr, ":token" => $token));

		// var_dump($q->errorInfo());
		if (!$r || $q->rowCount() == 0)  {
			REST::response_code("failure");
			return error("Failed to update device information.", false);
		}
		else
			return success("Success", false);

	else:
		REST::response_code("bad-method");
		return error("Unsupported HTTP Method", false);

	endif;
}}

handle_item_reports: {
if (count(REST::$ARGS) == 3 && REST::$ARGS[2] == "reports") {
	if (REST::$REQUEST_METHOD == "GET"):
		$device_id = REST::$ARGS[1];

		$device_info = fetch("SELECT true FROM devices WHERE ID=:id", array(":id" => $device_id));
		if (!isset($device_info[0])) {
			REST::response_code("not-found");
			return error("Device not found.", false);
		}


		$reports = fetch("SELECT RID as id, Timestamp as timestamp FROM reports WHERE DID=:device_id",
			array(":device_id" => $device_id));

		if (!REST::preferred("text/html") && !REST::preferred("application/json"))
			echo join("\n", array_map(function ($r) { return $r["id"]; }, $reports));

		elseif (!REST::preferred("text/html")) {
			foreach ($reports as &$report)
				$report["link"] = lnk("report/".$report["id"]);

			echo json_encode($reports);
		}

		else {
?>


<?php echo_breadcrumbs_bar(array(
	lnk("/devices") => "Devices",
	lnk("/devices/$device_id") => "Device $device_id",
	"Reports"));
?>

<div class="main">
	<div class="container">
		<div class="title">
			Reports from Device <?php echo $device_id; ?>
		</div>

		<ul class="unstyled reports-list">
			<?php foreach ($reports as $report): ?>
			<li>
				<a href="<?php echo lnk("/reports/".$report["id"]); ?>">Report <?php echo $report["id"]; ?></a>
				<span class="report-date">
					<span class="absolute-date"><?php echo $report["timestamp"]; ?></span>
					<span class="relative-date"><?php echo $report["timestamp"]; ?></span>
				</span>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>


<script type="text/javascript" src="<?php echo ASSETS_FOLDER.'/js/dates.js'; ?>"></script>
<script type="text/javascript">
(function() {
	var lastheard = document.querySelectorAll(".relative-date");
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
		}

	endif;
}}
?>