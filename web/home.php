<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<title>Sticky Pi - Home</title>

	<link href="assets/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
	<link href="assets/css/main.css" type="text/css" rel="stylesheet" />
</head>
<body>

<div class="header">
	<div class="container">
		<div class="logo text-center">
			<span class="logo-text">Remote sPi</span>
		</div>

		<div class="action-bar">
			<div id="breadcrumbs" class="breadcrumbs">
				<a href="?" class="crumb" title="Home">Home</a>
				»
				<b class="last crumb" title="Devices">Devices</b>
			</div>

			<div class="user-menu">
				<a class="username">Ashayr</a>
				<a href="?logout" class="logout link">Logout</a>
			</div>
		</div>
	</div>
</div>

<div class="main">
	<div class="container">
		<div class="title">Connected devices:</div>
		<ul class="device-list">
			<?php foreach($model['devices'] as $device): ?>
			<li id="device-<?php echo $device['id']; ?>" class="device">
				<div class="device-info">
					<div class="device-name title"><a href="?/device-1">Sticky at Highfield</a></div>
					<div class="device-address">
						<span class="data-label">IP</span>
						<span class="data-value"><?php echo $device['ip'];?></span>
					</div>
					<div class="device-uptime">
						<span class="data-label">Uptime</span>
						<span class="data-value"><?php echo $device['uptime'];?></span>
					</div>
					<div class="device-lastheard">
						<span class="data-label">Last heard</span>
						<span class="data-value"><?php echo $device['last_heard'];?></span>
					</div>
					<div class="device-reports">
						<span class="data-label">Reports</span>
						<span class="data-value">
							<span><b><?php echo count($device['reports']["read"]);?></b> total</span>,
							<span><b><?php echo count($device['reports']["unread"]);?></b> unread</span></span>
					</div>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<script type="text/javascript">
(function() {
	var articulate = function(t, m) {
		return t + " " + m + (t==1? "" : "s");
	}
	var inWords = function(date) {
		var s = "";
		var t = 0;

		t = Math.floor(date.getHours() / 24);
		if (t > 0)
			s += articulate(t, "day") + " ";

		t = date.getHours() % 24;
		if (t > 0)
			s += articulate(t, "hour") + " ";

		t = date.getMinutes();
		if (t > 0)
			s += articulate(t, "minute") + " ";

		t = date.getSeconds();
		if (t > 0)
			s += articulate(t, "second") + " ";

		return s.trim();
	}


	var uptimes = document.querySelectorAll(".device .device-uptime > .data-value");
	for (var i = uptimes.length - 1; i >= 0; i--) {
		var e = uptimes[i];
		var v = e.innerHTML;

		e.setAttribute('timestamp', v);
		e.innerHTML = inWords(new Date(v * 1000));
	}


	var lastheard = document.querySelectorAll(".device .device-lastheard > .data-value");
	var now = new Date();
	for (var i = lastheard.length - 1; i >= 0; i--) {
		var e = lastheard[i];
		var v = e.innerHTML;

		e.setAttribute('timestamp', v);
		var d = new Date(v * 1000);
		e.innerHTML = inWords(new Date(now - d)) + " ago";
	}
}())
</script>

<div class="footer">
	<div class="container text-center">
		<nav class="footer-nav nav">
			<ul class="horizontal">
				<li><a href="?">Home</a></li>
				<li><a href="?logout">Logout</a></li>
			</ul>
		</nav>

		<div class="copyright">
			<ul class="authors">
				<li><a href="#">Anko Anchev</a></li>
				<li><a href="#">Yordan Ganchev</a></li>
			</ul>
			<span class="statement">Copyright © 2016 - 2016</span>
		</div>
	</div>
</div>

<script type="text/javascript">
(function(){
	var footer = document.querySelector(".footer");
	var contentHeight = document.body.clientHeight
	var footerTop = footer.offsetTop;

	if (contentHeight > footerTop)
		footer.style.position = "static";
})();
</script>

</body>
</html>