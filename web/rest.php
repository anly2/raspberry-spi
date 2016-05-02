<?php

class REST {
	public static $URI = "";
	public static $ARGS = array();
	public static $REQUEST_METHOD = "GET";

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


	public static $named_codes = array(
		"forbidden" => 403,
		"not-found" => 404,
		"bad-method" => 405,
		"failure" => 406,
		"success" => 200,
		"created" => 201,
		"empty" => 204
	);

	public static function response_code($id, $doSend=true) {
		$i = strtolower($id);
		if (array_key_exists($i, REST::$named_codes))
			$code = REST::$named_codes[$i];
		else
			$code = $id;

		if ($doSend)
			http_response_code($code);

		return $code;
	}
}

if (isset($_REQUEST['_url'])) {
	$uri = $_REQUEST['_url'];
	if (strpos($uri, "/") === 0)
		$uri = substr($uri, 1);

	$uri = trim($uri, "/");
	REST::$URI = $uri;
	REST::$ARGS = explode("/", $uri);
	REST::$REQUEST_METHOD = strtoupper($_SERVER["REQUEST_METHOD"]);
}
?>