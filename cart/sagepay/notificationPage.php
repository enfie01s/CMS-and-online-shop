<?php
header("Content-type: text/plain");
include "../../../config.inc.php";
include("../cart_functions.php");
include("../cart_vars.php");
include("includes.php");
/**************************************************************************************************
* Sage Pay Server PHP Kit Notification Page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============

* 02/04/2009 - Simon Wolfe - Updated UI for re-brand
* 11/02/2009 - Simon Wolfe - Updated for VSP protocol 2.23
* 18/12/2007 - Nick Selby - New PHP version adapted from ASP
***************************************************************************************************
* Description
* ===========

* This page handles the notification POSTs from Sage Pay Server.  It should be made externally visible
* so that Sage Pay Server can send messages to over either HTTP or HTTPS.
* The code validates the Sage Pay Server POST using MD5 hashing, updates the database accordingly,
* and replies with a RedirectURL to which Sage Pay Server will send your customer.  This is normally your
* order completion page, or a page to handle failures or cancellations.
***************************************************************************************************

*** Information is POSTed to this page from Sage Pay Server. The POST will ALWAYS contain the VendorTxCode, **
*** VPSTxID and Status fields.  We'll extract these first and use them to decide how to respond to the POST. **/

//Define end of line character used to correctly format response to Sage Pay Server
$eoln = chr(13) . chr(10);
	
$strStatus=cleaninput($_REQUEST["Status"],"Text");
$strVendorTxCode=cleaninput($_REQUEST["VendorTxCode"],"VendorTxCode");
$strVPSTxId=cleaninput($_REQUEST["VPSTxId"],"Text");

// Using the VPSTxId and VendorTxCode, we can retrieve our SecurityKey from our database
// This enables us to validate the POST to ensure it came from the Sage Pay Systems
$strSecurityKey="";

/*$strSQL = "SELECT `SecurityKey` FROM cart_orders WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "' AND `VPSTxId`='" . mysql_real_escape_string($strVPSTxId) . "'";

$rsPrimary = ysql_query($strSQL)or die ("Query '$query' failed with error message: \"" . mysql_error () . '"');
while ($row = mysql_fetch_array($rsPrimary))*/
$strSQL = "SELECT `SecurityKey` FROM cart_orders WHERE `VendorTxCode`=? AND `VPSTxId`=?";

$rsPrimary = $db1->prepare($strSQL);
$rsPrimary->execute(array($strVendorTxCode,$strVPSTxId));
while ($row = $rsPrimary->fetch())
	$strSecurityKey=$row["SecurityKey"];

if (strlen($strSecurityKey)==0)
{
	/** We cannot find a record of this order in the database, so something isn't right **
	** To protect the customer, we should send back an INVALID response.  This will prevent **
	** the Sage Pay Server systems from settling any authorised transactions.  We will also send a **
	** RedirectURL that points to our orderFailure page, passing details of the error **
	** in the Query String so that the page knows how to respond to the customer **/
	
	ob_flush();
	header("Content-type: text/html");
	echo "Status=INVALID" . $eoln;
	
	/** Only use the Internal FQDN value during development.  In LIVE systems, always use the actual FQDN **/
	echo "RedirectURL=" . $strYourSiteFQDN . $strVirtualDir . "index.php?p=cart_co_payment&co=orderFailed&error=001" . $eoln;
	echo "StatusDetail=Unable to find the transaction in our database." . $eoln;
	exit();
}	
else
{
	/** We've found the order in the database, so now we can validate the message **
	** First blank out our result variables **/
	$strStatusDetail="";
	$strTxAuthNo="";
	$strAVSCV2="";
	$strAddressResult="";
	$strPostCodeResult="";
	$strCV2Result="";
	$strGiftAid="";
	$str3DSecureStatus="";
	$strCAVV="";
	$strAddressStatus="";
	$strPayerStatus="";
	$strCardType="";
	$strLast4Digits="";
	$strMySignature="";
	
	/** Now get the VPSSignature value from the POST, and the StatusDetail in case we need it **/
	$strVPSSignature=cleaninput($_REQUEST["VPSSignature"],"Text");
	$strStatusDetail=cleaninput($_REQUEST["StatusDetail"],"Text");

	/** Retrieve the other fields, from the POST if they are present **/
	if (strlen($_REQUEST["TxAuthNo"]>0)) $strTxAuthNo=cleaninput($_REQUEST["TxAuthNo"],"Number");
	$strAVSCV2=cleaninput($_REQUEST["AVSCV2"],"Text");
	$strAddressResult=cleaninput($_REQUEST["AddressResult"],"Text");
	$strPostCodeResult=cleaninput($_REQUEST["PostCodeResult"],"Text");
	$strCV2Result=cleaninput($_REQUEST["CV2Result"],"Text");
	$strGiftAid=cleaninput($_REQUEST["GiftAid"],"Number");
	$str3DSecureStatus=cleaninput($_REQUEST["3DSecureStatus"],"Text");
	$strCAVV=cleaninput($_REQUEST["CAVV"],"Text");
	$strAddressStatus=cleaninput($_REQUEST["AddressStatus"],"Text");
	$strPayerStatus=cleaninput($_REQUEST["PayerStatus"],"Text");
	$strCardType=cleaninput($_REQUEST["CardType"],"Text");
	$strLast4Digits=cleaninput($_REQUEST["Last4Digits"],"Text");

	/** Now we rebuilt the POST message, including our security key, and use the MD5 Hash **
	** component that is included to create our own signature to compare with **
	** the contents of the VPSSignature field in the POST.  Check the Sage Pay Server protocol **
	** if you need clarification on this process **/
	$strMessage=$strVPSTxId . $strVendorTxCode . $strStatus . $strTxAuthNo . $strVendorName . $strAVSCV2 . $strSecurityKey 
	               . $strAddressResult . $strPostCodeResult . $strCV2Result . $strGiftAid . $str3DSecureStatus . $strCAVV
	               . $strAddressStatus . $strPayerStatus . $strCardType . $strLast4Digits ;

	$strMySignature=strtoupper(md5($strMessage));

	/** We can now compare our MD5 Hash signature with that from Sage Pay Server **/
	if ($strMySignature!==$strVPSSignature)
	{
		/** If the signatures DON'T match, we should mark the order as tampered with, and **
		** send back a Status of INVALID and failure page RedirectURL **/
		
		/*$strSQL = "UPDATE cart_orders SET `Status`='TAMPER WARNING! Signatures do not match for this Order.  The Order was Cancelled. strMySignature=" . $strMySignature . " strVPSSignature=" . $strVPSSignature ."' 
					where VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'";
		//strMySignature=" . $strMySignature . " strVPSSignature=" . $strVPSSignature ."
		//strMessage=" . $strMessage . "
		
		$rsPrimary = ysql_query($strSQL)	or die ("Query '$query' failed with error message: \"" . mysql_error () . '"');*/
		$strSQL = "UPDATE cart_orders SET `Status`='TAMPER WARNING! Signatures do not match for this Order.  The Order was Cancelled. strMySignature=? strVPSSignature=?' WHERE VendorTxCode=?";
		
		$rsPrimary = $db1->prepare($strSQL);
		$rsPrimary->execute(array($strMySignature,$strVPSSignature,$strVendorTxCode));
		$strSQL="";
		$rsPrimary="";
		
		ob_flush();
		header("Content-type: text/plain");
		echo "Status=INVALID" . $eoln;
		
		/** Only use the Internal FQDN value during development.  In LIVE systems, always use the actual FQDN **/
		echo "RedirectURL=" .  $strYourSiteFQDN . $strVirtualDir . "index.php?p=cart_co_payment&co=orderFailed&error=002" . $eoln;
		
		echo "StatusDetail=Cannot match the MD5 Hash. Order might be tampered with." . $eoln;
		exit();
	}
	else
	{
		/** Great, the signatures DO match, so we can update the database and redirect the user appropriately **/
		if ($strStatus=="OK")
			$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
		elseif ($strStatus=="NOTAUTHED") 
			$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
		elseif ($strStatus=="ABORT")
			$strDBStatus="ABORTED - The customer clicked Cancel on the payment pages, or the transaction was timed out due to customer inactivity.";
		elseif ($strStatus=="REJECTED")
			$strDBStatus="REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
		elseif ($strStatus=="AUTHENTICATED")
			$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
		elseif ($strStatus=="REGISTERED")
			$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
		elseif ($strStatus=="ERROR")
			$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . $strStatusDetail;
		else
			$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . $strStatus .  ", with StatusDetail:" . $strStatusDetail;
							
		/** Update our database with the results from the Notification POST **/
		/*$strSQL="UPDATE cart_orders SET `pay_method`='".(strtolower($strCardType)=="paypal"?"paypal":"cc")."', `Status`='" . $strDBStatus . "'";
		if (strlen($strTxAuthNo)>0) $strSQL=$strSQL . ",TxAuthNo=" . mysql_real_escape_string($strTxAuthNo);
		if (strlen($strAVSCV2)>0) $strSQL=$strSQL . ",AVSCV2='" . mysql_real_escape_string($strAVSCV2) . "'";
		if (strlen($strAddressResult)>0) $strSQL=$strSQL . ",AddressResult='" . mysql_real_escape_string($strAddressResult) . "'";
		if (strlen($strPostCodeResult)>0) $strSQL=$strSQL . ",PostCodeResult='" . mysql_real_escape_string($strPostCodeResult) . "'";
		if (strlen($strCV2Result)>0) $strSQL=$strSQL . ",CV2Result='" . mysql_real_escape_string($strCV2Result) . "'";
		if (strlen($strGiftAid)>0) $strSQL=$strSQL . ",GiftAid=" . mysql_real_escape_string($strGiftAid);
		if (strlen($str3DSecureStatus)>0) $strSQL=$strSQL . ",ThreeDSecureStatus='" . mysql_real_escape_string($str3DSecureStatus) . "'";
		if (strlen($strCAVV)>0) $strSQL=$strSQL . ",CAVV='" . mysql_real_escape_string($strCAVV) . "'";
		if (strlen($strAddressStatus)>0) $strSQL=$strSQL . ",AddressStatus='" . mysql_real_escape_string($strAddressStatus) . "'";
		if (strlen($strPayerStatus)>0) $strSQL=$strSQL . ",PayerStatus='" . mysql_real_escape_string($strPayerStatus) . "'";
		if (strlen($strCardType)>0) $strSQL=$strSQL . ",CardType='" . mysql_real_escape_string($strCardType) . "'";
		if (strlen($strLast4Digits)>0) $strSQL=$strSQL . ",Last4Digits='" . mysql_real_escape_string($strLast4Digits) . "'";
		$strSQL.=",`pay_status`='".(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?1:0)."'";
		$strSQL.=",`order_status`='New'";
		$strSQL.=",`iorder_status`='0'";
		$strSQL.=",`humanorderdate`=NOW()";
		$strSQL=$strSQL . " WHERE VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'";

		$rsPrimary = ysql_query($strSQL)	or die ("Query '$query' failed with error message: \"" . mysql_error () . '"');*/
		$binds=array();
		$strSQL="UPDATE cart_orders SET `pay_method`='".(strtolower($strCardType)=="paypal"?"paypal":"cc")."', `Status`=?";$binds[]=$strDBStatus;
		if (strlen($strTxAuthNo)>0){ $strSQL.= ",TxAuthNo=?";$binds[]=$strTxAuthNo;}
		if (strlen($strAVSCV2)>0) {$strSQL.= ",AVSCV2=?";$binds[]=$strAVSCV2;}
		if (strlen($strAddressResult)>0){ $strSQL.= ",AddressResult=?";$binds[]=$strAddressResult;}
		if (strlen($strPostCodeResult)>0){ $strSQL.= ",PostCodeResult=?";$binds[]=$strPostCodeResult;}
		if (strlen($strCV2Result)>0){ $strSQL.= ",CV2Result=?";$binds[]=$strCV2Result;}
		if (strlen($strGiftAid)>0) {$strSQL.= ",GiftAid=?";$binds[]=$strGiftAid;}
		if (strlen($str3DSecureStatus)>0) {$strSQL.= ",ThreeDSecureStatus=?";$binds[]=$str3DSecureStatus;}
		if (strlen($strCAVV)>0) {$strSQL.= ",CAVV=?";$binds[]=$strCAVV;}
		if (strlen($strAddressStatus)>0) {$strSQL.= ",AddressStatus=?";$binds[]=$strAddressStatus;}
		if (strlen($strPayerStatus)>0){ $strSQL.= ",PayerStatus=?";$binds[]=$strPayerStatus;}
		if (strlen($strCardType)>0) {$strSQL.= ",CardType=?";$binds[]=$strCardType;}
		if (strlen($strLast4Digits)>0){ $strSQL.=",Last4Digits=?";$binds[]=$strLast4Digits;}
		$strSQL.=",`pay_status`='".(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?1:0)."'";
		$strSQL.=",`order_status`='New'";
		$strSQL.=",`iorder_status`='0'";
		$strSQL.=",`humanorderdate`=NOW()";
		$strSQL=$strSQL . " WHERE VendorTxCode=?";$binds[]=$strVendorTxCode;

		$rsPrimary = $db1->prepare($strSQL);
		$rsPrimary->execute($binds);
		$strSQL="";
		$rsPrimary="";

		/** New reply to Sage Pay Server to let the system know we've received the Notification POST **/
		ob_flush();
		header("Content-type: text/plain");
		
		/** Always send a Status of OK if we've read everything correctly.  Only INVALID for messages with a Status of ERROR **/
		if ($strStatus=="ERROR")
			echo "Status=INVALID" . $eoln;
		else{
			echo "Status=OK" . $eoln; 
			$strResponse="Status=OK" . $eoln;
		}
		$_SESSION["VendorTxCode"]=$strVendorTxCode;
		/** Now decide where to redirect the customer **/
		if ($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")
		{	/** If a transaction status is OK, AUTHENTICATED or REGISTERED, we should send the customer to the success page **/
			$strRedirectPage="index.php?p=cart_co_payment&co=orderSuccessful&VendorTxCode=" . $strVendorTxCode;
		}
		else
		{/** The status indicates a failure of one state or another, so send the customer to orderFailed instead **/
			$strRedirectPage="index.php?p=cart_co_payment&co=orderFailed&VendorTxCode=" . $strVendorTxCode;
		}
				
		/** Only use the Internal FQDN value during development.  In LIVE systems, always use the actual FQDN **/
		if ($strConnectTo=="LIVE")
		{	echo "RedirectURL=" . $strYourSiteFQDN . $strVirtualDir . $strRedirectPage . $eoln;}
		else {
			echo "RedirectURL=" . $strYourSiteFQDN . $strVirtualDir . $strRedirectPage . $eoln;
			$strResponse=$strResponse . "RedirectURL=" . $strYourSiteFQDN . $strVirtualDir . $strRedirectPage . $eoln;	
		}	
						
		/** No need to send a StatusDetail, since we're happy with the POST **/
		exit();
	}
}

?> 

