<?php

if (REST::$ARGS[0] == "device" && REST::$ARGS[2] == "report") {
	$did = REST::$ARGS[1];

	$devices = fetch("SELECT ID FROM devices WHERE ID=:id", array(":id"=>$did));
	if (!isset($devices[0])) {
		redirect("/device/not-found");
		exit;
	}

	$did = $devices[0]["ID"];
	$content = file_get_contents("php://input", true);

	global $db;
	$q = $db->prepare("INSERT INTO reports (DID, Content) VALUES (:did, :content)");
	if (!$q->execute(array(":did"=>$did, ":content"=>$content)))
		echo "failure"; //$q->errorInfo();
	else
		echo "success";
}


if (isset(REST::$ARGS[2])) {
	$query = REST::$ARGS[2];

	$select = function($field) {
		$reports = fetch("SELECT $field FROM reports WHERE RID=:id",
			array(":id" => REST::$ARGS[1]));

		if (isset($reports[0]))
			return $reports[0][$field];
	};

	if ($query == "exists") {
		echo ($select("RID") == REST::$ARGS[1])? "true" : "false";
	}

	if ($query == "date")
		echo $select("Timestamp");

	if ($query == "content")
		echo $select("Content");

	exit;
}
?>