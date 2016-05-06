<?php

require "db.php";
require "rest.php";



/* Convenience functions */

$path = explode("/", $_SERVER["SCRIPT_NAME"]);
array_pop($path);
define('HOME', join("/", $path), 1);
define('ASSETS_FOLDER', HOME.'/assets', 1);


function map($uri, $page, $wrap=true, $method="ALL") {
	REST::handle($uri, function($r) use($page, $wrap) {
		ob_start();
		if ($wrap) require("pages/header.php");
		require("pages/" . $page);
		if ($wrap) require("pages/footer.php");
		// ob_end_flush();
	}, $method);
}

function lnk($uri) {
	$uri = trim($uri, "/");
	return (strpos($uri, HOME) === false)? HOME."/".$uri : $uri;
}

function redirect($url) {
	if (headers_sent()) {
		echo '<script type="text/javascript">window.location.href="'.lnk($url).'";</script>';
		return;
	}

	header('Location: '.lnk($url));
}


function error($status=false, $message="failure") {
	if ($status)
		REST::response_code($status);
	elseif (REST::response_code() < 300)
		REST::response_code(500);

	if (REST::preferred("text/html"))
		echo '<div class="container"><div class="alert alert-danger">'.$message.'</div></div>';
	else
		echo $message;
}

function success($message="success", $status=false) {
	if ($status)
		REST::response_code($status);
	elseif (REST::response_code() >= 300)
		REST::response_code(200);

	if (REST::preferred("text/html"))
		echo '<div class="container"><div class="alert alert-success">'.$message.'</div></div>';
	else
		echo $message;
}


/*
echo "<pre>";
var_dump($_SERVER);
echo "</pre>";
exit;
//*/


// Page mappings //

map("/devices", "devices.php");
map("/reports", "reports.php");
map("/commands", "commands.php");
map("/registration", "registration.php");

/*
map("/devices", "devices.php");
map("/device/not-found", "device not found.php");
map("/device/(\d+)", "device.php");
map("/registration", "registration.php");

REST::handle("/report/(\d+)/%", function($r) {
	require("pages/report.php");
});
map("/report/(\d+)", "report.php");
*/

// Test form page
map("/form", "form.php");

// DEFAULT page
//map("", "devices.php"); //does not change the URL, which is a nice cue to have
REST::handle("", function($r) { redirect("/devices"); });
?>