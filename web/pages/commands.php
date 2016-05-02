<?php
$commands = array(
	array(
		"slug" => "aurodump",
		"name"=>"aurodump-ng",
		"view"=>"commands/aurodump.php"),
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
	endif;
}}

handle_item: {

}


?>