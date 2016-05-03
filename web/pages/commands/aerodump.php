<?php
global $command;

if (REST::$REQUEST_METHOD == "POST") {
	$data = json_encode(array($_REQUEST["bssid"], $_REQUEST["channel"]));
	return $data;
}

?>

<div class="main">
	<form action="<?php echo lnk("/commands/".$command["slug"]); ?>" method="POST" class="container">
		<div class="title">
			Command: <?php echo $command["name"]; ?>
		</div>

		<input type="hidden" name="command" value="<?php echo $command["slug"]; ?>" />
		<?php if (isset($_REQUEST["device"])): //#!!! XSS ?>
			<input type="hidden" name="device_id" value="<?php echo $_REQUEST["device"]; //#!!! XSS ?>" />
		<?php endif; ?>

		<ul class="unstyled arguments-list">
			<li>
				<span class="data-label">BSSID:</span>
				<span class="data-value"><input type="text" name="bssid" /></span>
			</li>
			<li>
				<span class="data-label">Channel:</span>
				<span class="data-value"><input type="number" name="channel" min="1" max="16"></span>
			</li>
			<li>
				<input type="submit" value="Submit" />
			</li>
		</ul>
	</form>
</div>