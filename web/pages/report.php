<?php
if (isset(REST::$ARGS[2])) {
	$query = REST::$ARGS[2];

	$select = function($field) {
		$reports = fetch("SELECT $field FROM reports WHERE RID=:id",
			array(":id" => REST::$ARGS[1]));

		if (isset($reports[0]))
			echo $reports[0][$field];
	};

	if ($query == "date")
		echo $select("Timestamp");

	if ($query == "content")
		echo $select("Content");

	exit;
}
?>