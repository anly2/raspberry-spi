<?php
global $command;

if (REST::$REQUEST_METHOD == "POST") {
	$data = json_encode(array($_REQUEST["address"], $_REQUEST["count"], $_REQUEST["interval"], $_REQUEST["ttl"]));
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
				<span class="data-label">Address:</span>
				<span class="data-value"><input type="text" name="address" /></span>
			</li>
			<li>
				<span class="data-label">Count:</span>
				<span class="data-value"><input type="number" name="count" min="1"></span>
			</li>
			<li>
				<span class="data-label">Interval:</span>
				<span class="data-value"><input type="number" name="interval" min="1"></span>
			</li>
			<li>
				<span class="data-label">Time-to-live:</span>
				<span class="data-value"><input type="number" name="ttl" min="1"></span>
			</li>
			<li>
				<input type="submit" value="Submit" />
			</li>
		</ul>
	</form>
</div>