<?php

$commands = array(
	array("slug" => "tcpdump", "label" => "TCP Dump", "cmd" => "tcpdump --args"),
	array("slug" => "start_ssh", "label" => "Accept SSH connections", "cmd" => "sshstart --args"),
	array("slug" => "log_avail_networks", "label" => "Log available networks", "cmd" => "dump_networks.sh --interval 15"),
);

function sticky_pi($name) {
	static $id = 0;
	static $ip = 41;
	global $commands;

	return array(
		"id" => $id++,
		"ip" => "1.1.1." . $ip++,
		"name" => $name,
		"uptime" => rand(),
		"last_heard" => time() - rand(60*1000, 3*24*60*60*1000),
		"commands" => $commands,
		"reports" => array("read" => array("2", "a"), "unread" => array("lorem"))
	);
}

$model = array(
	"devices" => array(
		sticky_pi("Sticky at Highfield"),
		sticky_pi("Sticky at Sainsbury"),
		sticky_pi("Sticky at Home")
	),
);


include "home.php";
?>