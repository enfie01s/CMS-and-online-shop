<?php
$error = $_GET["error"];
switch($error)
{
	case "404":
		$errorText = "<h2>404 Page not Found</h2><p>The page you requested was not found</p><p>
		Click <a href='index.php'>here</a> to return to the homepage</p>";
		echo $errorText;
		break;
	case "403":
		$errorText = "<h2>403 Forbidden</h2><p>You do not have permission to view this directory or page</p><p>
		Click <a href='index.php'>here</a> to return to the homepage</p>";
		echo $errorText;
		break;
	case "500":
		$errorText = "<h2>500 Internal Server error</h2><p></p><p>
		Click <a href='index.php'>here</a> to return to the homepage</p><p>If this error persists, please <a href='index.php?p=about'>contact us</a></p>";
		echo $errorText;
		break;
}

?>
