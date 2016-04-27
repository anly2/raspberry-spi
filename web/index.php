<?php

require "db.php";
require "rest.php";

function page($page) {
	require("pages/header.php");
	require("pages/" . $page);
	require("pages/footer.php");
}

$path = explode("/", $_SERVER["SCRIPT_NAME"]);
array_pop($path);
define('HOME', join("/", $path), 1);
define('ASSETS_FOLDER', HOME.'/assets', 1);


function redirect($url) {
	echo '<script type="text/javascript">window.location.href="'.$url.'";</script>';
}

function map($uri, $page) {
	REST::handle($uri, function($r) use($page) {
		page($page);
	});	
}


REST::handle("/device/(\d+)/report", function($r) {
	require("pages/report.php");
}, "POST");

map("/form", "form.php");
map("/devices", "devices.php");
map("/device/not-found", "device not found.php");
map("/device/(\d+)", "device.php");

REST::handle("/report/(\d+)/%", function($r) {
	require("pages/report.php");
});
map("/report/(\d+)", "report.php");
?>