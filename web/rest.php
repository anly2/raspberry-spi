<?php

class REST {
	public static $ARGS = array();
	private static $consumed = false;

	public static function preferred($type, $other="*/*") {
		$a = $_SERVER["HTTP_ACCEPT"];
		$i = stripos($a, $type);
		$o = stripos($a, $other);
		return ($i !== false && ($o === false || ($o > $i)));
	}

	public static function handle($uri_pattern, $action, $method="ALL") {
		if (REST::$consumed) return;

		if ($method != "ALL" && !(is_array($method) && in_array("ALL", $method))) {
			$m = $_SERVER['REQUEST_METHOD'];
			if (is_array($method)) {
				if (!in_array($m, $method))
					return;
			} else {
				if ($m != $method)
					return;
			}
		}

		$pattern = $uri_pattern;

		if (strpos($pattern, "/") !== 0 || strrpos($pattern, "/", -1) !== strlen($pattern)-1) {
			$pattern = str_replace("/", "\\/", $pattern);

			$pattern = "/^" . $pattern . "/";

			$pattern = str_replace("%", "(.*)", $pattern);
		}

		if (preg_match($pattern, $_REQUEST["_url"], $matches))
			if ($action($matches) !== false)
				REST::$consumed = true;
	}
}

if (isset($_REQUEST['_url'])) {
	$url = $_REQUEST['_url'];
	if (strpos($url, "/") === 0)
		$url = substr($url, 1);

	REST::$ARGS = explode("/", $url);
}
?>