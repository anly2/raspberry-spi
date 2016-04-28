<?php
//localhost
$db = new PDO('mysql:host=localhost;dbname=raspberry-spi;charset=utf8', "spi", "D25CcHTZeXDyNe3Q");

function fetch($sql, $args = array(), $mode = PDO::FETCH_ASSOC) {
	global $db;
	$q = $db->prepare($sql);
	$q->execute($args);
	return $q->fetchAll($mode);
}
?>