<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<title>Sticky Pi - Home</title>

	<link href="<?php echo ASSETS_FOLDER; ?>/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
	<link href="<?php echo ASSETS_FOLDER; ?>/css/main.css" type="text/css" rel="stylesheet" />
</head>
<body>

<div class="header">
	<div class="container">
		<div class="logo text-center">
			<span class="logo-text">Remote sPi</span>
		</div>

		<!--
		<div class="action-bar">
			<div id="breadcrumbs" class="breadcrumbs">
				<a href="?" class="crumb" title="Home">Home</a>
				»
				<b class="last crumb" title="Devices">Devices</b>
			</div>

			<div class="user-menu">
				<a class="username">Ashayr</a>
				<a href="?logout" class="logout link">Logout</a>
			</div>
		</div>
		-->
	</div>
</div>

<?php
function breadcrumbs($crumbs) {
	$code = "";

	$code .= '<div id="breadcrumbs" class="breadcrumbs">' . "\n";
	$code .= '	<a href="'.HOME.'" class="crumb" title="Home">Home</a>' . "\n";

	$last = array_pop($crumbs);

	foreach ($crumbs as $link => $title) {
		$code .= '	»' . "\n";
		$code .= '	<a href="'.$link.'" class="crumb" title="'.$title.'">'.$title.'</a>' . "\n";
	}

	if ($last !== NULL) {
		$code .= '	»' . "\n";
		$code .= '	<b class="last crumb" title="'.$last.'">'.$last.'</b>' . "\n";
	}

	$code .= '</div>' . "\n";
	return $code;
}

function echo_breadcrumbs_bar($crumbs) {
	echo '	<div class="action-bar">'."\n";
	echo '		<div class="container">'."\n";
	echo breadcrumbs($crumbs);
	echo '		</div>'."\n";
	echo '	</div>'."\n";
}
?>