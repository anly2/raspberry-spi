<?php
$authorised = $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (!$authorised)
		return error("forbidden", "Forbidden!");

	switch ($_SERVER["CONTENT_TYPE"]) {
		case "application/x-www-form-urlencoded":
			$state = $_REQUEST["registration_state"];
			break;
		case "application/json":
			$data = json_decode(file_get_contents("php://input"));
			$state = $data["registration_state"];
			break;
		case "text/plain":
			$state = file_get_contents("php://input");
			break;
		default:
	}
	$state = (strtolower($state) == "open");


	global $db;
	$q = $db->prepare("UPDATE appinfo SET Value=:state WHERE Field='registration_state'");
	$r = $q->execute(array(":state"=>$state? "open" : "closed"));

	// var_dump($q->errorInfo());
	if (!$r || $q->rowCount() == 0)
		error(500, "Failed to ".($state? "open" : "close")." registration.", !REST::preferred("text/html"));
	else
		success("Successfully ".($state? "opened" : "closed")." registration.", !REST::preferred("text/html"));

	exit;
}


$state = fetch("SELECT Value FROM appinfo WHERE Field='registration_state' LIMIT 1");
if (isset($state[0]))
	$state = (strtolower($state[0]["Value"]) == "open");
else
	return error('Failed to fetch Registration State.');

?>

<?php if (REST::preferred("text/html")): ?>
<div class="container">
	<div class="well">Registration is <strong><?php echo $state? "Open" : "Close"; ?></strong></div>

	<?php if ($authorised): ?>
		<form action="" method="POST">
			<input type="hidden" name="registration_state" value="<?php echo !$state? "open" : "close"; ?>">
			<button type="submit" class="form-control btn-primary">
				<strong style="text-transform: uppercase;"><?php echo !$state? "Open" : "Close"; ?></strong> registration
			</button>
		</form>
	<?php endif; ?>
</div>
<?php
else:
	echo $state? "open" : "closed";
endif;
?>