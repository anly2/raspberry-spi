<?php

class REST {
	public static $ARGS = array();

	public static function handle($uri_pattern, $action) {
		$pattern = $uri_pattern;

		if (strpos($pattern, "/") !== 0 || strrpos($pattern, "/", -1) !== strlen($pattern)-1) {
			$pattern = str_replace("/", "\\/", $pattern);

			$pattern = "/^" . $pattern . "/";

			$pattern = str_replace("%", "(.*)", $pattern);
		}

		if (preg_match($pattern, $_REQUEST["_url"], $matches))
			$action($matches);
	}
}

if (isset($_REQUEST['_url'])) {
	$url = $_REQUEST['_url'];
	if (strpos($url, "/") === 0)
		$url = substr($url, 1);

	REST::$ARGS = explode("/", $url);
}
?>