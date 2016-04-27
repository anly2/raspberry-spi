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


REST::handle("/devices", function($r) {
	page("devices.php");
});

REST::handle("/device/not-found", function($r) {
	page("device not found.php");
});

REST::handle("/device/(\d+)", function($r) {
	page("device.php");
});

?>