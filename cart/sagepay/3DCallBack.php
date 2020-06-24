<?php
include("includes.php");
 
/**************************************************************************************************
* Sage Pay Direct Kit 3D Callback Redirection page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============
*
* 18/12/2007 - Nick Selby - New PHP kit version adapted from ASP
**************************************************************************************************/

$strPaRes=$_REQUEST["PaRes"];
$strMD=$_REQUEST["MD"];
$strVendorTxCode=cleaninput($_REQUEST["VendorTxCode"],"VendorTxCode");
$_SESSION["VendorTxCode"]=$strVendorTxCode;
?>

<SCRIPT LANGUAGE="Javascript"> function OnLoadEvent() { document.form.submit(); } </SCRIPT>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="images/directKitStyle.css">
<title>3D-Secure Redirect</title>
</head>

<body OnLoad="OnLoadEvent();">
<FORM name="form" action="<?=$_SESSION['fqdn']?>index.php?p=cart_co_payment&amp;co=3DComplete" method="post" target="_top"/>
	<input type="hidden" name="PARes" value="<?=$strPaRes?>"/>
	<input type="hidden" name="MD" value="<?=$strMD?>"/>
	<NOSCRIPT>
	<center><p>Please click button below to Authorise your card</p><input type="submit" value="Go"/></p></center>
	</NOSCRIPT>
	</form>

</body>
</html>

