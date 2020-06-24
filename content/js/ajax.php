<?php
include "../../../config.inc.php";
switch($_GET['q'])
{
	case "views":
		if(isset($_GET['p']))
		{
			mysql_query("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`='".mysql_real_escape_string($_GET['p'])."'",$con1);
		}
		break;
}
?>