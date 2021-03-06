<?php
function auth_token() {
	return bin2hex(openssl_random_pseudo_bytes(16));
}

handle_list: {
if (REST::$URI == "devices") {
	if (REST::$REQUEST_METHOD == "GET"):

		global $devices;
		$devices = fetch("SELECT ID as id, Name as name, Address as address FROM devices");

		require "views/devices-list.php";


	elseif (REST::$REQUEST_METHOD == "POST"):
		$registration = fetch("SELECT Value as state FROM appinfo WHERE Field='registration_state'");

		if (!$registration || !isset($registration[0]))
			return error(500, "Failed to check registration state.");

		$registration_state = $registration[0]["state"];

		if (!$registration_state)
			return error(503, "Registration is closed.");


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
				return error(400, "Content type not supprted.");
		}

		if ($name === false)
			return error(400, "Name field is missing.");

		$addr = $_SERVER['REMOTE_ADDR'];
		$token = auth_token();

		$id = fetch("SELECT MAX(ID)+1 as id FROM devices")[0]["id"];
		if (!$id) $id = 1;

		global $db;
		$q = $db->prepare("INSERT INTO devices (ID, Name, Address, Auth_Token) VALUES (:id, :name, :addr, :token)");
		if (!$q->execute(array(":id"=>$id, ":name"=>$name, ":addr" => $addr, ":token" => $token)))
			return error("failure",
				REST::preferred("text/html")? "Failed to register the device." : "-1");


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
		return error("bad-method", "Unsupported HTTP Method");

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
		$GLOBALS["device"] = $device;
		include "views/devices-item.php";


	elseif (REST::$REQUEST_METHOD == "PUT"):
		$id = REST::$ARGS[1];


		// AUTHORIZE //

		$headers = apache_request_headers();
		if (!isset($headers["Authorization"]))
			return error(401, "Unauthorized!");

		$token = $headers["Authorization"];

		$auth_info = fetch("SELECT true FROM devices WHERE ID=:id AND Auth_Token=:token",
			array(":id" => $id, ":token" => $token));

		if (!$auth_info || !isset($auth_info[0]))
			return error(401, "Unauthorized!");


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
				return error(400, "Content type not supprted.");
		}

		if ($name === false)
			return error(400, "Name field is missing.");

		$addr = $_SERVER['REMOTE_ADDR'];


		global $db;
		$q = $db->prepare("UPDATE devices SET Name=:name, Address=:addr WHERE ID=:id AND Auth_Token=:token");
		$r = $q->execute(array(":id"=>$id, ":name"=>$name, ":addr" => $addr, ":token" => $token));

		// var_dump($q->errorInfo());
		if (!$r || $q->rowCount() == 0)
			return error("failure", "Failed to update device information.");
		else
			return success("Success");

	else:
		return error("bad-method", "Unsupported HTTP Method");

	endif;
}}

handle_item_reports: {
if (count(REST::$ARGS) == 3 && REST::$ARGS[2] == "reports") {
	if (REST::$REQUEST_METHOD == "GET"):
		$device_id = REST::$ARGS[1];

		$device_info = fetch("SELECT true FROM devices WHERE ID=:id", array(":id" => $device_id));
		if (!isset($device_info[0]))
			return error("not-found", "Device not found.");


		$reports = fetch("SELECT RID as id, Timestamp as timestamp FROM reports WHERE DID=:device_id",
			array(":device_id" => $device_id));

		if (!REST::preferred("text/html") && !REST::preferred("application/json")):
			echo join("\n", array_map(function ($r) { return $r["id"]; }, $reports));

		elseif (!REST::preferred("text/html")):
			foreach ($reports as &$report)
				$report["link"] = lnk("report/".$report["id"]);

			echo json_encode($reports);

		else:
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
					<?php if (count($reports) == 0): ?>
						No reports
					<?php endif; ?>

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
		endif;

	elseif (REST::$REQUEST_METHOD == "POST"):
		$device_id = REST::$ARGS[1];

		$device_info = fetch("SELECT ID FROM devices WHERE ID=:id", array(":id"=>$device_id));
		if (!isset($device_info[0]))
			return error("not-found", "Device not found.");

		// AUTHORIZE //

		$headers = apache_request_headers();
		if (!isset($headers["Authorization"]))
			return error(401, "Unauthorized!");

		$token = $headers["Authorization"];

		$auth_info = fetch("SELECT true FROM devices WHERE ID=:id AND Auth_Token=:token",
			array(":id" => $device_id, ":token" => $token));

		if (!$auth_info || !isset($auth_info[0]))
			return error(401, "Unauthorized!");


		// POST //

		$id = fetch("SELECT MAX(RID)+1 as id FROM reports")[0]["id"];
		if (!$id) $id = 1;

		$content = file_get_contents("php://input");

		global $db;
		$q = $db->prepare("INSERT INTO reports (RID, DID, Content) VALUES (:report_id, :device_id, :content)");
		if (!$q->execute(array(":report_id"=>$id, ":device_id"=>$device_id, ":content"=>$content)))
			return error("failure", "Failed to post the report.");


		// REPLY / RENDER //

		REST::response_code("created");
		header('Location: '.lnk("/reports/$id"));

		if (!REST::preferred("text/html"))
			return success("Successfully posted the report.");
		else
			echo '<script type="text/javascript">window.location.href="'.lnk("/reports/$id").'"</script>"';

	else:
		return error("bad-method", "Unsupported HTTP Method");

	endif;
}}

handle_item_commands: {
if (count(REST::$ARGS) == 3 && REST::$ARGS[2] == "commands") {
	if (REST::$REQUEST_METHOD == "GET"):
		$device_id = REST::$ARGS[1];

		if (isset($_REQUEST["since"]))
			$since = $_REQUEST["since"];
		else
			$since = false;

		$args = array(":device_id"=>$device_id);
		if ($since) $args[":since"] = $since;

		$cmds = fetch("SELECT Command as action, Data as args, Timestamp as timestamp"
				." FROM commands"
				." WHERE DID=:device_id"
				.($since? " AND Timestamp>:since" : "")
				." ORDER BY Timestamp DESC", $args);

		foreach ($cmds as &$cmd)
			$cmd["args"] = json_decode($cmd["args"]);

		$since_info = fetch("SELECT NOW() as timestamp");
		if (isset($since_info[0]))
			header("X-Since: ". $since_info[0]["timestamp"]);

		ob_clean();
		echo json_encode($cmds);
		exit;

	else:
		return error("bad-method", "Unsupported HTTP Method");

	endif;
}}
?>