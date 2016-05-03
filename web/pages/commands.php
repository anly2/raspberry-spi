<?php
$commands = array(
	array(
		"slug" => "aerodump",
		"name"=>"aerodump-ng",
		"view"=>"commands/aerodump.php"),
	array(
		"slug" => "ping",
		"name"=>"Ping",
		"view"=>"commands/ping.php"),

	array(
		"slug" => "nmap",
		"name"=>"nmap",
		"view"=>"commands/nmap.php")
);

handle_list: {
if (REST::$URI == "commands") {
	if (REST::$REQUEST_METHOD == "GET"):
		if (count($commands) == 0)
			REST::response_code("empty");

		if (isset($_REQUEST["device"])) {
			$device_id = intval($_REQUEST["device"]);
			$device_query = "?device=".$device_id;
		} else
			$device_query = "";

		if (!REST::preferred("text/html") && !REST::preferred("application/json")):
			foreach($commands as $command)
				echo "(".$command["slug"].") ".$command["name"]."\n";

		elseif (!REST::preferred("text/html")):
			foreach ($commands as &$command) {
				$command["link"] = lnk("/commands/".$command["slug"]).$device_query;
				unset($command["view"]);
			}

			echo json_encode($commands);

		else:
		?>

		<?php echo_breadcrumbs_bar(array("Commands")); ?>

		<div class="main">
			<div class="container">
				<div class="title">
					Available commands
				</div>

				<ul class="unstyled commands-list">
					<?php if (count($commands) == 0): ?>
						No commands
					<?php endif; ?>

					<?php foreach ($commands as $command): ?>
					<li>
						<a href="<?php echo lnk("/commands/".$command["slug"]).$device_query; ?>">
							<?php echo $command["name"]; ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<?php
		endif;

	else:
		REST::response_code("bad-method");
		return error("Unsupported HTTP Method", false);

	endif;
}}

handle_item: {
if (count(REST::$ARGS) == 2) {
	if (REST::$REQUEST_METHOD == "GET"):

		$query = REST::$ARGS[1];
		global $command;
		$command = false;

		foreach ($commands as $cmd)
			if ($cmd["slug"] == $query) {
				$command = $cmd;
				break;
			}

		if (!$command) {
			REST::response_code("not-found");
			return error("Command not found.", false);
		}


		// RENDER //
		echo_breadcrumbs_bar(array(lnk("/commands") => "Commands", $command["name"]));
		include $command["view"];

	elseif (REST::$REQUEST_METHOD == "POST"):
		if (!isset($_REQUEST["device_id"])) {
			REST::response_code(400);
			return error("Missing device id field.", false);
		}

		$device_id = $_REQUEST["device_id"];

		// AUTHORIZE //

		$authorized = false;

		authorize: {
			/// has header? ///
			$headers = apache_request_headers();
			if (isset($headers["Authorization"])) {
				$token = $headers["Authorization"];

				$auth_info = fetch("SELECT true FROM devices WHERE ID=:id AND Auth_Token=:token",
					array(":id" => $device_id, ":token" => $token));

				if ($auth_info && isset($auth_info[0]))
					$authorized = true;
			}

			/// is local? ///
			if ($_SERVER["REMOTE_ADDR"] == $_SERVER["SERVER_ADDR"])
				$authorized = true;
		}

		if (!$authorized) {
			REST::response_code(401);
			return error("Unauthorized!", false);
		}


		// POST //

		$query = REST::$ARGS[1];
		global $command;
		$command = false;

		foreach ($commands as $cmd)
			if ($cmd["slug"] == $query) {
				$command = $cmd;
				break;
			}

		if (!$command) {
			REST::response_code("not-found");
			return error("Command not found.", false);
		}

		$cmd = $command["slug"];
		$data = include $command["view"];

		global $db;
		$q = $db->prepare("INSERT INTO commands (DID, Command, Data) VALUES (:device_id, :cmd, :data)");
		if (!$q->execute(array(":device_id"=>$device_id, ":cmd"=>$cmd, ":data"=>$data))) {
			REST::response_code("failure");
			return error("Failed to issue command.", false);
		}

		success("Successfully issued command.");

	else:
		REST::response_code("bad-method");
		return error("Unsupported HTTP Method", false);

	endif;
}}
?>