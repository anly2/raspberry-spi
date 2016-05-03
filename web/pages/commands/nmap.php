<?php
global $command;
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
				<span class="data-label">Host:</span>
				<span class="data-value"><input type="text" name="host" /></span>
			</li>
			<li>
				<span class="data-label">Subnet mask:</span>
				<span class="data-value">/<input type="number" name="subnet" placeholder="24" min="1" max="32"></span>
			</li>
			<li>
				<input type="submit" value="Submit" />
			</li>
		</ul>
	</form>
</div>