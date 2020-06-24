<?php
include("sagepay/includes.php");

/**************************************************************************************************
* Sage Pay Direct PHP Transaction Registration Page
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

* This page performs 3 main functions:
*	(1) Displays the card details screen for the user to enter their payment method
*	(2) Stores the order details in the database
*	(3) POSTS the information to Sage Pay Direct and redirect's the user if 3D-Auth is enabled, otherwise<br>
*		it simply updates the transaction with the success or failure of the transaction.
* If the kit is in SIMULATOR mode, everything is shown on the screen and the user asked to Proceed
* at each stage.  In Test and Live mode, nothing is echoed to the screen and the browser
* is automatically redirected to either the 3D-Authentication, or completion pages.

* This code is all carried out on one page to avoid ever storing card details either in the database
* or the session.  Such storage is not compliant with Visa and MasterCard PCI-DSS rules.
***************************************************************************************************/

$strPageState = "Payment";

// Check if request was for a PayPal express checkout
if ($_SESSION["paypalExpress"] == true) 
{
    $isPaypalExpress = true;
    $_SESSION["paypalExpress"] = false;
} 
else
{
    $isPaypalExpress = false;
}
 
// Check we have a cart in the session.  If not, go back to the buildOrder page to get one
$strCart=$_SESSION["cart"];
if (!is_array($strCart)||count($strCart)==0) 
{
	ob_end_flush();
	redirect(MAINBASE."/index.php?p=cart_basket");
	exit();
}
// Check we have a billing address in the session if its not a PayPal Express checkout request
elseif (strlen($_SESSION["address_details"])==0 && ($isPaypalExpress==false)) 
{
	ob_flush();
	redirect(MAINBASE."/index.php?p=cart_co_address");
	exit();
}
elseif ((isset($post_arr["navigate"])&&$post_arr["navigate"]=="proceed") || ($isPaypalExpress==true))
{
  // The user wants to proceed to the confirmation page.  Send them there **
	if ($_REQUEST["PageState"] == "Completion") 
	{
		ob_flush();
		if (strlen($_REQUEST["CompletionURL"]) > 0) {
		    redirect($_REQUEST["CompletionURL"]);
		    exit();
		}
	}
	// The Customer is checking out with the PayPal express payment method
	elseif ($isPaypalExpress == true) 
	{
	    $strCardType = "PAYPAL";
	}
	//The customer wants to take a payment, so validate the payment boxes first
	else if(!($price<1&&$_SESSION['discount_amount']>0))
	{	
		// Extract Card Details from the page
		$strCardType=cleanInput($_REQUEST["CardType"],"Text");	
		$strCardHolder=substr($_REQUEST["CardHolder"],0,100);
		$strCardNumber=cleanInput($_REQUEST["CardNumber"],"Number");
		$strStartDate=cleanInput($_REQUEST["StartDate1"].$_REQUEST["StartDate2"],"Number");
		$strExpiryDate=cleanInput($_REQUEST["ExpiryDate1"].$_REQUEST["ExpiryDate2"],"Number");
		$strIssueNumber=cleanInput($_REQUEST["IssueNumber"],"Number");
		$strCV2=cleanInput($_REQUEST["CV2"],"Number");
		$strComments=(strtolower($_REQUEST["comments"])!="special requirements")?cleanInput($_REQUEST["comments"],"Text"):"";	
		
		// Right then... check em
		if ($strCardType!="PAYPAL") 
		{
			if ($strCardHolder=="")
				$strPageError="You must enter the name of the Card Holder.";
			elseif ($strCardType=="")
				$strPageError="You must select the type of card being used.";
			elseif ($strCardNumber=="" || !is_numeric($strCardNumber))
				$strPageError="You must enter the full card number.";
			elseif ($strStartDate!=="" && (strlen($strStartDate)!=4 || !is_numeric($strStartDate)))
				$strPageError="Provide a Start Date for Maestro";
			elseif ($strExpiryDate=="" || strlen($strExpiryDate)!=4 || !is_numeric($strExpiryDate)) 
				$strPageError="You must provide an Expiry Date".$strExpiryDate;
			elseif (($strIssueNumber!=="") and (!is_numeric($strIssueNumber))) 
				$strPageError="If you provide an Issue number, it should be numeric.";
			elseif (($strCV2=="")||(!is_numeric($strCV2))) {
				$strPageError="You must provide a Card Verification Value. This is the last 3 digits on the signature strip 
				               of your card (or for American Express cards, the 4 digits printed to the right of the main 
				               card number on the front of the card.)";
			}
		}
	}

   if (strlen($strPageError) == 0 ) 
   { 
		/* All required fields are present, so first store the order in the database then format the POST to Sage Pay Direct 
		** First we need to generate a unique VendorTxCode for this transaction
		** We're using VendorName, time stamp and a random element.  You can use different methods if you wish
		** but the VendorTxCode MUST be unique for each transaction you send to Sage Pay Direct */
		$strTimeStamp = date("y/m/d : H:i:s", time());
		$intRandNum = rand(0,32000)*rand(0,32000);
		$strVendorTxCode=cleanInput($strVendorPrefix . "-" . $strTimeStamp . "-" . $intRandNum,"VendorTxCode");
		$_SESSION["VendorTxCode"] = $strVendorTxCode;
			
		/* Calculate the transaction total based on basket contents.  For security
		** we recalculate it here rather than relying on totals stored in the session or hidden fields
		** We'll also create the basket contents to pass to Sage Pay Direct. See the Sage Pay Direct Protocol for
		** the full valid basket format.  The code below converts from our "x of y" style into
		** the Sage Pay system basket format (using a 17.5% VAT calculation for the tax columns) */
		$sngTotal=count($strCart)+1;
		$strThisEntry=$strCart;
		$strBasket="";
		$iBasketItems=0;
		$runningTotal=0;	
		$vattotal=0;	
		$postageDescrip="";	
		$postageDid=0;
		$thisdiscount=0;	
		$totaldiscount=0;
								
		foreach($strCart as $cartid => $cartarray)
		{
			
			$iQuantity=$cartarray['qty'];
			$iProductId=$cartarray['prod_id'];
			
			/*$strSQL="SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,`salediscount`,`saletype` FROM ".PTABLE." as p JOIN cart_fusion as f ON p.`".PFIELDID."`=f.`pid` WHERE p.`".PFIELDID."`='" . $iProductId . "'";
			$rsPrimary = ysql_query($strSQL,CARTDB)or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
			$row = mysql_fetch_array($rsPrimary);*/
			$strSQL="SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,`salediscount`,`saletype` FROM ".PTABLE." as p JOIN cart_fusion as f ON p.`".PFIELDID."`=f.`pid` WHERE p.`".PFIELDID."`=?";
			$rsPrimary = $db1->prepare($strSQL);
			$rsPrimary->execute(array($iProductId));
			$row = $rsPrimary->fetch();
			$rsPrimary="";
			$strSQL="";
			$sprice=0;
			foreach($cartarray['skuvariant'] as $spid => $svsu)
			{
				$skubits=explode("-qty-",$svsu);
				/*$sprs=ysql_query("SELECT `price` FROM cart_variants WHERE `vskuvar`='$skubits[0]'",CARTDB);
				list($sp)=mysql_fetch_row($sprs);*/
				$sprs=$db1->prepare("SELECT `price` FROM cart_variants WHERE `vskuvar`=?");
				$sprs->execute(array($skubits[0]));
				list($sp)=$sprs->fetch(PDO::FETCH_NUM);
				$sprice+=$sp-cart_getdiscount($sp,$row['salediscount'],$row['saletype']);
			}
			$thisdiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($row['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $row['exclude_discount']!=1?(($sprice/100)*$_SESSION['discount_amount']):0;
			
			$totaldiscount+=$thisdiscount;
			$thisprice=$sprice-$thisdiscount;
			$vatadded=cart_addvat($thisprice,1);
			$thisvat=cart_getvat($thisprice);
			//$vattoadd=$vat*($thisprice/100);
			$runningTotal+=$vatadded*$iQuantity;
			$vattotal+=$thisvat[1]*$iQuantity;
			$rowtitle=$iProductId=='358'?$cartarray["title"]:$row["title"];
			
			$strBasket.=":" . $rowtitle . ":" . $iQuantity;
			$strBasket.=":" . number_format($thisvat[0],2); /** Price ex-Vat **/
			$strBasket.=":" . number_format($thisvat[1],2); /** VAT component **/
			$strBasket.=":" . number_format($vatadded,2); /** Item price **/
			$strBasket.=":" . number_format($vatadded*$iQuantity,2); /** Line total **/			
			/*
			number of items (inc postage line):item name:item quantity:Price ex-Vat:VAT component:Item price:line total
			2:Polymer+Spacer:1:10.00:2.00:12.00:12.00:P&P:---:---:---:---:5.00
			*/
		}					
			
		// We've been right through the cart, so add delivery to the total and the basket
		$discount=0;
		$price=cart_postagecalc($runningTotal,$_SESSION['shipping']);
		$strBasket=$sngTotal . $strBasket . ":".$_SESSION['postdesc'].":---:---:---:---:".$price;
//print_r($strBasket);
		$runningTotal+=$price;
		$postageDescrip=$_SESSION['postdesc'];
		$postageDid=$_SESSION['shipping'];
		
		// Gather customer details from the session
		$strCustomerEMail = $_SESSION['address_details']['billing']['email'];
		$strBillingNametitle = $_SESSION['address_details']['billing']['nametitle'];
		$strBillingFirstnames = $_SESSION['address_details']['billing']['firstname'];
		$strBillingSurname = $_SESSION['address_details']['billing']['lastname'];
		$strBillingAddress1  = $_SESSION['address_details']['billing']['address1'];
		$strBillingAddress2 = $_SESSION['address_details']['billing']['address2'];
		$strBillingCity = $_SESSION['address_details']['billing']['city'];
		$strBillingPostCode = $_SESSION['address_details']['billing']['postcode'];
		$strBillingCountry = $_SESSION['address_details']['billing']['country'];
		$strBillingState = $_SESSION['address_details']['billing']['county'];
		$strBillingPhone = $_SESSION['address_details']['billing']['phone'];
		$strBillingCompany = $_SESSION['address_details']['billing']['company'];
		$strBillingHomepage = $_SESSION['address_details']['billing']['website'];
		$bIsDeliverySame = $_SESSION['address_details']['delivery']['sameasbilling'];
		$strDeliveryNametitle = $_SESSION['address_details']['delivery']['nametitle'];
		$strDeliveryFirstnames = $_SESSION['address_details']['delivery']['firstname'];
		$strDeliverySurname = $_SESSION['address_details']['delivery']['lastname'];
		$strDeliveryAddress1 = $_SESSION['address_details']['delivery']['address1'];
		$strDeliveryAddress2 = $_SESSION['address_details']['delivery']['address2'];
		$strDeliveryCity = $_SESSION['address_details']['delivery']['city'];
		$strDeliveryPostCode = $_SESSION['address_details']['delivery']['postcode'];
		$strDeliveryCountry = $_SESSION['address_details']['delivery']['country'];
		$strDeliveryState = $_SESSION['address_details']['delivery']['county'];
		$strDeliveryPhone = $_SESSION['address_details']['delivery']['phone'];
		$strCustId = ((isset($_SESSION['loggedin'])&&$_SESSION['loggedin']!=0)?$ua['cust_id']:0);
	
		/* Now store the order total and order details in your database for use in your own order fulfilment
		** These kits come with a table called tblOrders in which this data is stored
		** accompanied by the tblOrderProducts table to hold the basket contents for each order */
		$strSQL="SELECT MAX(`invoice`) FROM cart_orders";
		/*$rsPrimary = ysql_query($strSQL,CARTDB)or die ();
		list($lastinvoice)=mysql_fetch_row($rsPrimary);*/
		$rsPrimary = $db1->query($strSQL);
		list($lastinvoice)=$rsPrimary->fetch(PDO::FETCH_NUM);
		$rsPrimary="";
		$strSQL="";
		$strSQL="INSERT INTO cart_orders(`VendorTxCode`, `TxType`, `total_price`, `cust_id`, `invoice`, `date_ordered`, `ship_description`, `ship_method_id`, `ship_total`, `session_id`, `discount_code`, `discount`,`nametitle`, `firstname`, `lastname`, `email`, `address1`, `address2`, `city`, `postcode`, `country`, `state`, `phone`, `sameasbilling`, `alt_nametitle`,`alt_name`, `alt_address1`, `alt_address2`, `alt_city`, `alt_postcode`, `alt_country`, `alt_state`, `alt_phone`, `tax_rate`, `tax_price`, `comments`, `CardType`,`IP`) VALUES (";

		/*$strSQL.="'" . mysql_real_escape_string($strVendorTxCode) . "',"; //Add the VendorTxCode generated above
		$strSQL.="'" . mysql_real_escape_string($strTransactionType) . "',"; //Add the TxType from the includes file
		$strSQL.="'" . number_format($runningTotal,2,".","") . "',"; //Add the formatted total amount
		//$strSQL.="'" . mysql_real_escape_string($strCurrency) . "',"; //Add the Currency
		$strSQL.="'" . mysql_real_escape_string($strCustId)."',";//customer id
		$strSQL.="'" . ($lastinvoice+1) ."',";//invoice
		$strSQL.="'" . date("U")."',";//date ordered
		$strSQL.="'".mysql_real_escape_string($postageDescrip)."',";//ship desc
		$strSQL.="'".mysql_real_escape_string($postageDid)."',";//ship method id
		$strSQL.="'".number_format($price,2,".","")."',";//ship total
	
		$strSQL.="'" . session_id() . "',";
		$strSQL.="'".mysql_real_escape_string($_SESSION['discount_code'])."',";
		$strSQL.="'".mysql_real_escape_string($_SESSION['discount_amount'])."',";
			
    
    if ($isPaypalExpress == true) 
		{
				$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, null, null, ";
				$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, 'PAYPAL',";
		}
		else
		{
			// Add the Billing details 
			$strSQL.="'" . mysql_real_escape_string($strBillingNametitle) . "',";  
			$strSQL.="'" . mysql_real_escape_string($strBillingFirstnames) . "',";   
			$strSQL.="'" . mysql_real_escape_string($strBillingSurname) . "',";  
			
			// -Customer email 
			$strSQL.=((strlen($strCustomerEMail)>0)?"'" . mysql_real_escape_string($strCustomerEMail) . "'":"null").","; 
			
			$strSQL.="'" . mysql_real_escape_string($strBillingAddress1) . "',";  
			$strSQL.=((strlen($strBillingAddress2)>0)?"'" . mysql_real_escape_string($strBillingAddress2) . "'":"null").",";
			$strSQL.="'" . mysql_real_escape_string($strBillingCity) . "',";  
			$strSQL.="'" . mysql_real_escape_string($strBillingPostCode) . "',"; 
			$strSQL.="'" . mysql_real_escape_string(str_replace("GB","100",$strBillingCountry)) . "',";  
			$strSQL.=((strlen($strBillingState)>0)?"'" . mysql_real_escape_string($strBillingState) . "'":"null").","; 
			$strSQL.=((strlen($strBillingPhone)>0)?"'" . mysql_real_escape_string($strBillingPhone) . "'":"null").",";
			
			// Add the Delivery details 
			$strSQL.=(strlen($bIsDeliverySame)>0?"'".$bIsDeliverySame."'":"null").",";
			$strSQL.="'" . mysql_real_escape_string($strBillingNametitle) . "',"; 
			$strSQL.="'" . mysql_real_escape_string($strDeliveryFirstnames." ".$strDeliverySurname) . "',";  
			$strSQL.="'" . mysql_real_escape_string($strDeliveryAddress1) . "',"; 
			$strSQL.=(strlen($strDeliveryAddress2)>0?"'" . mysql_real_escape_string($strDeliveryAddress2) . "'":"null").",";
			$strSQL.="'" . mysql_real_escape_string($strDeliveryCity) . "',";  
			$strSQL.="'" . mysql_real_escape_string($strDeliveryPostCode) . "',"; 
			$strSQL.="'" . mysql_real_escape_string(str_replace("GB","100",$strDeliveryCountry)) . "',";  
			$strSQL.=(strlen($strDeliveryState)>0?"'" . mysql_real_escape_string($strDeliveryState) . "'":"null").","; 
			$strSQL.=(strlen($strDeliveryPhone)>0?"'" . mysql_real_escape_string($strDeliveryPhone) . "'":"null").",";
			$strSQL.="'".number_format($vat,2)."',";//tax_rate
			$strSQL.="'".number_format($vattotal,2)."',";//tax_price
			$strSQL.=(strlen($strComments)>0?"'" . mysql_real_escape_string($strComments) . "'":"null").",";//comments
			$strSQL.="'".mysql_real_escape_string($strCardType)."',";// Card Type
		}
		$strSQL.="'".$_SERVER['REMOTE_ADDR'] ."')";
		
		//Execute the SQL command to insert this data to the tblOrders table
		ysql_query($strSQL,CARTDB) or die ($strSQL.mysql_error());	
		$order_insert_id=mysql_insert_id();*/
		$binds=array();
		$strSQL.="?,"; $binds[]=$strVendorTxCode;//Add the VendorTxCode generated above
		$strSQL.="?,";$binds[]=$strTransactionType; //Add the TxType from the includes file
		$strSQL.="?,"; $binds[]=number_format($runningTotal,2,".","");//Add the formatted total amount
		//$strSQL.="'" . mysql_real_escape_string($strCurrency) . "',"; //Add the Currency
		$strSQL.="?,";$binds[]=$strCustId;//customer id
		$strSQL.="?,";$binds[]=$lastinvoice+1;//invoice
		$strSQL.="'" . date("U")."',";//date ordered
		$strSQL.="?,";$binds[]=$postageDescrip;//ship desc
		$strSQL.="?,";$binds[]=$postageDid;//ship method id
		$strSQL.="?,";$binds[]=number_format($price,2,".","");//ship total
		/** Now save the fields returned from the Sage Pay System and extracted above **/
		$strSQL.="'" . session_id() . "',";
		$strSQL.="?,";$binds[]=$_SESSION['discount_code'];
		$strSQL.="?,";$binds[]=$_SESSION['discount_amount'];
			
    //** If this is a PaypalExpress checkout method then NO billing and delivery details are available here **
    if ($isPaypalExpress == true) 
		{
				$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, null, null, ";
				$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, 'PAYPAL',";
		}
		else
		{
			// Add the Billing details 
			$strSQL.="?,";  $binds[]=$strBillingNametitle;
			$strSQL.="?,"; $binds[]=$strBillingFirstnames;  
			$strSQL.="?,";  $binds[]=$strBillingSurname;
			
			// -Customer email 
			if(strlen($strCustomerEMail)>0){$strSQL.="?,"; $binds[]=$strCustomerEMail;}else{$strSQL.="null,";}
			
			$strSQL.="?,"; $binds[]=$strBillingAddress1; 
			if(strlen($strBillingAddress2)>0){$strSQL.="?,";$binds[]=$strBillingAddress2;}else{$strSQL.="null,";}
			$strSQL.="?,"; $binds[]=$strBillingCity; 
			$strSQL.="?,"; $binds[]=$strBillingPostCode;
			$strSQL.="?,";  $binds[]=str_replace("GB","100",$strBillingCountry);
			if(strlen($strBillingState)>0){$strSQL.="?,"; $binds[]=$strBillingState;}else{$strSQL.="null,";}
			if(strlen($strBillingPhone)>0){$strSQL.="?,";$binds[]=$strBillingPhone;}else{$strSQL.="null,";}
			
			// Add the Delivery details 
			if(strlen($bIsDeliverySame)>0){$strSQL.="?,";$binds[]=$bIsDeliverySame;}else{$strSQL.="null,";}
			$strSQL.="?,"; $binds[]=$strBillingNametitle;
			$strSQL.="?,";  $binds[]=$strDeliveryFirstnames." ".$strDeliverySurname;
			$strSQL.="?,"; $binds[]=$strDeliveryAddress1;
			if(strlen($strDeliveryAddress2)>0){$strSQL.="?,";$binds[]=$strDeliveryAddress2;}else{$strSQL.="null,";}
			$strSQL.="?,";  $binds[]=$strDeliveryCity;
			$strSQL.="?,"; $binds[]=$strDeliveryPostCode;
			$strSQL.="?,";  $binds[]=str_replace("GB","100",$strDeliveryCountry);
			if(strlen($strDeliveryState)>0){$strSQL.="?,"; $binds[]=$strDeliveryState;}else{$strSQL.="null,";}
			if(strlen($strDeliveryPhone)>0){$strSQL.="?,";$binds[]=$strDeliveryPhone;}else{$strSQL.="null,";}
			$strSQL.="?,";$binds[]=number_format($vat,2);//tax_rate
			$strSQL.="?,";$binds[]=number_format($vattotal,2);//tax_price
			if(strlen($strComments)>0){$strSQL.="?,";$binds[]=$strComments;}else{$strSQL.="null,";}//comments
			$strSQL.="?,";$binds[]=$strCardType;// Card Type
		}
		$strSQL.="'".$_SERVER['REMOTE_ADDR'] ."')";
		
		//Execute the SQL command to insert this data to the tblOrders table
		$q=$db1->prepare($strSQL);	
		$q->execute($binds);
		$order_insert_id=$db1->lastInsertId();
		$strSQL="";
		$strPageState="Posted";
		/** Now add the basket contents to the orderproducts table, one line at a time **/
		
		
		foreach($strCart as $cart_id => $cartarray)
		{
			// Extract the Quantity and Product from the list of "x of y," entries in the cart
			$iQuantity=$cartarray['qty'];
			$iProductId=$cartarray['prod_id'];
			$excldiscount=$cartarray['exclude_discount'];
			
			//Look up the current price of the items in the database
			/*$strSQL = "SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id FROM ".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` WHERE p.`".PFIELDID."`='" . $iProductId . "'";
			$rsPrimary = ysql_query($strSQL,CARTDB)or die ('error');*/
			$strSQL = "SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id FROM ".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` WHERE p.`".PFIELDID."`=?";
			$rsPrimary = $db1->prepare($strSQL);
			$rsPrimary->execute(array($iProductId));
			foreach($cartarray['skuvariant'] as $spid => $svsu)
			{
				$skubits=explode("-qty-",$svsu);
				/*$sprs=ysql_query("SELECT `price` FROM cart_variants WHERE `vskuvar`='$skubits[0]'",CARTDB);
				list($sp)=mysql_fetch_row($sprs);*/
				$sprs=$db1->prepare("SELECT `price` FROM cart_variants WHERE `vskuvar`=?");
				$sprs->execute(array($skubits[0]));
				list($sp)=$sprs->fetch(PDO::FETCH_NUM);
				$sprice=$sp-cart_getdiscount($sp,$row['salediscount'],$row['saletype']);
			}
			//$row = mysql_fetch_array($rsPrimary);
			$row = $rsPrimary->fetch();
			$sngPrice=$sprice;
			$sngTitle=$row['title'];
			$sngSeoTitle=urlencode($row['title']);
			$sngShort=$row['shortdesc'];
			$sngTaxable="1";
			$sngShipnotes="";
			$sngDiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($row['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $row['exclude_discount']!=1?(($sprice/100)*$_SESSION['discount_amount']):0;
			$strSQL="";
			$rsPrimary = "";
			$skuvars="";
			if($cartarray['ispack']==0)
			{
				foreach($cartarray['skuvariant'] as $ident => $newsku)
				{
					$expsku=explode("-qty-",$newsku);
				}
				/*$optInfostr = "SELECT `vname` as item_desc,`vskuvar` FROM cart_variants WHERE `vskuvar`='".$expsku[0]."'";
				$optInfoq=ysql_query($optInfostr,CARTDB)	or die ();
				list($oitem,$var_id)=mysql_fetch_row($optInfoq);*/
				$optInfostr = "SELECT `vname` as item_desc,`vskuvar` FROM cart_variants WHERE `vskuvar`=?";
				$optInfoq=$db1->prepare($optInfostr);
				$optInfoq->execute(array($expsku[0]));
				list($oitem,$var_id)=$optInfoq->fetch();
			}
			else
			{
				$oitem="";$var_id="";
			}
			$getskuvar=explode("-v-",$var_id);
			$sngSku=$cartarray['ispack']==0?$getskuvar[0]:$row['sku'];
			/** Save the basket contents with price included so we know the price at the time of order **
			** so that subsequent price changes will not affect the price paid for items in this order **/
			
			$oname=$iProductId=='358'?$cartarray['product']." (Shop: ".$cartarray['fromshop'].", Date:".$cartarray['purchasedate'].", Serial:".$cartarray['serial'].")":"";
			$binds=array();
			$strSQL="INSERT INTO cart_orderproducts(`order_id`,`prod_id`,`VendorTxCode`,`qty`,`price`,`discount`,`exclude_discount`,`title`,`seo_title`,`sku`,`short_desc`,`taxable`,`postage_notes`,`goptid`,`oname`,`oitem`,`variant_id`,`ispack`,`areaofinterest`) VALUES(";
			/*$strSQL.="'".$order_insert_id."',";
			$strSQL.="'".$iProductId."',";
			$strSQL.="'" . mysql_real_escape_string($strVendorTxCode) . "',"; //Add the VendorTxCode generated above
			$strSQL.="'".$cartarray['qty']."',";
			$strSQL.="'".$sngPrice."',";
			$strSQL.="'".$sngDiscount."',";
			$strSQL.="'".$excldiscount."',";
			$strSQL.="'".mysql_real_escape_string($sngTitle)."',";
			$strSQL.="'".mysql_real_escape_string($sngSeoTitle)."',";
			$strSQL.="'".mysql_real_escape_string($sngSku)."',";
			$strSQL.="'".mysql_real_escape_string($sngShort)."',";
			$strSQL.="'".$sngTaxable."',";
			$strSQL.="'".mysql_real_escape_string($sngShipnotes)."',";
			$strSQL.="'',";//goptid
			$strSQL.="'".mysql_real_escape_string($oname)."',";//oname
			$strSQL.="'".mysql_real_escape_string($oitem)."',";
			$strSQL.="'".mysql_real_escape_string($getskuvar[1])."',";
			$strSQL.="'".$cartarray['ispack']."',";
			$strSQL.="'".(isset($cartarray['areaofinterest'])?$cartarray['areaofinterest']:"")."'";
			$strSQL.=")";
			$rsPrimary = ysql_query($strSQL,CARTDB)	or die (mysql_error());*/
			
			$strSQL.="?,";$binds[]=$order_insert_id;
			$strSQL.="?,";$binds[]=$iProductId;
			$strSQL.="?,";$binds[]=$strVendorTxCode; //Add the VendorTxCode generated above
			$strSQL.="?,";$binds[]=$cartarray['qty'];
			$strSQL.="?,";$binds[]=$sngPrice;
			$strSQL.="?,";$binds[]=$sngDiscount;
			$strSQL.="?,";$binds[]=$excldiscount;
			$strSQL.="?,";$binds[]=$sngTitle;
			$strSQL.="?,";$binds[]=$sngSeoTitle;
			$strSQL.="?,";$binds[]=$sngSku;
			$strSQL.="?,";$binds[]=$sngShort;
			$strSQL.="?,";$binds[]=$sngTaxable;
			$strSQL.="?,";$binds[]=$sngShipnotes;
			$strSQL.="'',";//goptid
			$strSQL.="?,";$binds[]=$oname;//oname
			$strSQL.="?,";$binds[]=$oitem;
			$strSQL.="?,";$binds[]=$getskuvar[1];
			$strSQL.="?,";$binds[]=$cartarray['ispack'];
			if(isset($cartarray['areaofinterest'])){$strSQL.="?";$binds[]=$cartarray['areaofinterest'];}else{$strSQL.="";}
			$strSQL.=")";
			$rsPrimary = $db1->prepare($strSQL);
			$rsPrimary->execute($binds);
			$orderprods_insert_id=$db1->lastInsertId();
			$rsPrimary="";
			$strSQL="";
			if($cartarray['ispack']!=0)
			{
				foreach($cartarray['skuvariant'] as $ident => $newsku)
				{
					$expsku=explode("-qty-",$newsku);
				
					/*$optInfostr = "SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title,`vname` as item_desc FROM ".PTABLE." as p,cart_fusion as fo,cart_variants as ov WHERE p.`".PFIELDID."`=fo.`pid` AND po.`optid`=fo.`opt` AND ov.`voptid`=fo.`opt` AND ov.`vskuvar`='".$expsku[0]."' AND p.`".PFIELDID."`='$ident'";
					$optInfoq=ysql_query($optInfostr)	or die ("Query '$optInfostr' failed with error message: \"" . mysql_error () . '"');
					$opts=mysql_fetch_assoc($optInfoq);*/
					
					$optInfostr = "SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title,`vname` as item_desc FROM ".PTABLE." as p,cart_fusion as fo,cart_variants as ov WHERE p.`".PFIELDID."`=fo.`pid` AND po.`optid`=fo.`opt` AND ov.`voptid`=fo.`opt` AND ov.`vskuvar`=? AND p.`".PFIELDID."`=?";
					$optInfoq=$db1->prepare($optInfostr);
					$optInfoq->execute(array($expsku[0],$ident));
					$opts=$optInfoq->fetch(PDO::FETCH_ASSOC);
					
					$strSQL="INSERT INTO cart_orderkits(`order_id`,`order_prod_id`,`kprod_id`,`prod_id`,`kit_title`,`okit_skuvar`,`item_qty`,`oname`,`oitem`) VALUES(";
					
				/*	$strSQL.="'".$order_insert_id."',";
					$strSQL.="'".$orderprods_insert_id."',";
					$strSQL.="'".$iProductId."',";
					$strSQL.="'".$opts['prod_id']."',";
					$strSQL.="'".$opts['title']."',";
					$strSQL.="'".$expsku[0]."',";
					$strSQL.="'".$expsku[1]."',";
					$strSQL.="'',";//oname
					$strSQL.="'".$opts['item_desc']."'";
					
					$strSQL.=")";	
					$rsPrimary = ysql_query($strSQL,CARTDB)	or die ();*/
					$binds=array();
					$strSQL.="?,";$binds[]=$order_insert_id;
					$strSQL.="?,";$binds[]=$orderprods_insert_id;
					$strSQL.="?,";$binds[]=$iProductId;
					$strSQL.="?,";$binds[]=$opts['prod_id'];
					$strSQL.="?,";$binds[]=$opts['title'];
					$strSQL.="?,";$binds[]=$expsku[0];
					$strSQL.="?,";$binds[]=$expsku[1];
					$strSQL.="'',";//oname
					$strSQL.="?";$binds[]=$opts['item_desc'];
					
					$strSQL.=")";	
					$rsPrimary = $db1->prepare($strSQL);
					$rsPrimary->execute($binds);
					$rsPrimary="";
					$strSQL="";
				}
			}
			
		}
		
		// Now create the Sage Pay Direct POST

		/* Now to build the Sage Pay Direct POST.  For more details see the Sage Pay Direct Protocol 2.23
		** NB: Fields potentially containing non ASCII characters are URLEncoded when included in the POST */
		$strPost="VPSProtocol=" . $strProtocol;
		$strPost.="&TxType=" . $strTransactionType; //PAYMENT by default.  You can change this in the includes file
		$strPost.="&Vendor=" . $strVendorName;
		$strPost.="&VendorTxCode=" . $strVendorTxCode; //As generated above
		
		// Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
	    if (strlen($strPartnerID) > 0)
	            $strPost.="&ReferrerID=" . URLEncode($strPartnerID);  //You can change this in the includes file

		$strPost.="&Amount=" . number_format($runningTotal,2); //Formatted to 2 decimal places with leading digit but no commas or currency symbols **
		$strPost.="&Currency=" . $strCurrency;
		
		// Up to 100 chars of free format description
		$strPost.="&Description=" . urlencode($sitename." products");
		
		// Card details. Not required if CardType = "PAYPAL" 
		if ($strCardType != "PAYPAL")
		{ 
			$strPost.="&CardHolder=" . $strCardHolder;
			$strPost.="&CardNumber=" . $strCardNumber;
			if (strlen($strStartDate)>0) 
				$strPost.="&StartDate=" . $strStartDate;
			$strPost.="&ExpiryDate=" . $strExpiryDate;
			if (strlen($strIssueNumber)>0) 
				$strPost.="&IssueNumber=" . $strIssueNumber;
			$strPost.="&CV2=" . $strCV2;
		}
		$strPost.="&CardType=" . $strCardType;
		
    // If this is a PaypalExpress checkout method then NO billing and delivery details are supplied 
    if ($isPaypalExpress == false) 
    {
       /* Billing Details 
       ** This section is optional in its entirety but if one field of the address is provided then all non-optional fields must be provided 
      ** If AVS/CV2 is ON for your account, or, if paypal cardtype is specified and its not via PayPal Express then this section is compulsory */
			$strPost.="&BillingFirstnames=" . urlencode($strBillingFirstnames);
			$strPost.="&BillingSurname=" . urlencode($strBillingSurname);
			$strPost.="&BillingAddress1=" . urlencode($strBillingAddress1);
			if (strlen($strBillingAddress2) > 0) $strPost=$strPost . "&BillingAddress2=" . urlencode($strBillingAddress2);
			$strPost.="&BillingCity=" . urlencode($strBillingCity);
			$strPost.="&BillingPostCode=" . urlencode($strBillingPostCode);
			$strPost.="&BillingCountry=" . urlencode($strBillingCountry);
			if (strlen($strBillingState) > 0&&$strBillingCountry=="US") $strPost.="&BillingState=" . urlencode($strBillingState);
			if (strlen($strBillingPhone) > 0) $strPost.="&BillingPhone=" . urlencode($strBillingPhone);

            /* Delivery Details
            ** This section is optional in its entirety but if one field of the address is provided then all non-optional fields must be provided
            ** If paypal cardtype is specified then this section is compulsory */
			$strPost.="&DeliveryFirstnames=" . urlencode($strDeliveryFirstnames);
			$strPost.="&DeliverySurname=" . urlencode($strDeliverySurname);
			$strPost.="&DeliveryAddress1=" . urlencode($strDeliveryAddress1);
			if (strlen($strDeliveryAddress2) > 0) $strPost.="&DeliveryAddress2=" . urlencode($strDeliveryAddress2);
			$strPost.="&DeliveryCity=" . urlencode($strDeliveryCity);
			$strPost.="&DeliveryPostCode=" . urlencode($strDeliveryPostCode);
			$strPost.="&DeliveryCountry=" . urlencode($strDeliveryCountry);
			if (strlen($strDeliveryState) > 0&&$strDeliveryCountry=="US") $strPost.="&DeliveryState=" . urlencode($strDeliveryState);
			if (strlen($strDeliveryPhone) > 0) $strPost.="&DeliveryPhone=" . urlencode($strDeliveryPhone);     
        }
        
		/* For PAYPAL cardtype only: Fully qualified domain name of the URL to which customers are redirected upon 
        ** completion of a PAYPAL transaction. Here we are getting strYourSiteFQDN & strVirtualDir from  
        ** the includes file. Must begin with http:// or https:// */
        if ($strCardType == "PAYPAL") 
        {
        	$strPost.="&PayPalCallbackURL=" . urlencode($strYourSiteFQDN . $strVirtualDir . "/paypalCallback.php");
        }

		// Set other optionals
		$strPost.="&CustomerEMail=" . urlencode($strCustomerEMail);
		$strPost.="&Basket=" . urlencode($strBasket); //As created above

		// For charities registered for Gift Aid, set to 1 to makr this as a Gift Aid transaction
		$strPost.="&GiftAidPayment=0";
		
		/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default
		** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
		if ($strTransactionType!=="AUTHENTICATE") $strPost.="&ApplyAVSCV2=0";
	
		// Send the IP address of the person entering the card details
		$strPost.="&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];

		/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default **
		** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
		$strPost.="&Apply3DSecure=0";
		
		/* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
		** If you are developing back-office applications for Mail Order/Telephone order, use M **
		** If your back office application is a subscription system with recurring transactions, use C **
		** Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E **/
		$strPost.="&AccountType=E";

		/* The full transaction registration POST has now been built **
		** Send the post to the target URL
		** if anything goes wrong with the connection process:
		** - $arrResponse["Status"] will be 'FAIL';
		** - $arrResponse["StatusDetail"] will be set to describe the problem 
		** Data is posted to strPurchaseURL which is set depending on whether you are using SIMULATOR, TEST or LIVE */
		if($price<1&&$_SESSION['discount_amount']>0)
		{
			$arrResponse=array(
			"VPSTxId"=>"",
			"SecurityKey"=>"",
			"TxAuthNo"=>"",
			"AVSCV2"=>"",
			"AddressResult"=>"",
			"PostCodeResult"=>"",
			"CV2Result"=>"",
			"3DSecureStatus"=>"",
			"CAVV"=>"",
			"Status"=>"OKFREE",
			"StatusDetail"=>"The Transaction was free",
			"PAReq"=>"",
			"MD"=>"",
			"ACSURL"=>""
			);
		}
		else if(INHOUSE)
		{
			$statuses=array("OK","MALFORMED","INVALID","NOTAUTHED","REJECTED","AUTHENTICATED","REGISTERED","ERROR");
			$rnum=rand(0,count($statuses)-1);
			$ttstatus=$statuses[$rnum];
			$arrResponse=array(
			"VPSTxId"=>"{943A0FF0-0BB3-0C82-E6D2-C4DEB30D33BA}",
			"SecurityKey"=>"QGZ8NQEYEN",
			"TxAuthNo"=>"15322354",
			"AVSCV2"=>"0",
			"AddressResult"=>"MATCHED",
			"PostCodeResult"=>"MATCHED",
			"CV2Result"=>"MATCHED",
			"3DSecureStatus"=>"OK",
			"CAVV"=>"hsdfDFWSEGwergWEGfwefWegwef",
			"Status"=>$ttstatus,/*MALFORMED*/
			"StatusDetail"=>"2014: The Transaction was something.",
			"PAReq"=>"BSkaFwYFFTYAGyFbBCIlQDQeBhoANXFcRFpZVWIuNh9AAAAAEhQQHSEbLC4MBkkUHToRVDceOxdOHg0LEU9ecXxaUjcZFjYGGSoUDQ4DA0gkDAMNLiEMVBYNBilJGjoKL0k4NiYnESYKfDdaNjFfVnIvRGJedFotS0E3XVdDeQ8vTVkoUARZNg1XAVdXUUIOTzwaIDgcB0k8JBskMQEy",
			"MD"=>"061104630847190185",
			"ACSURL"=>"https://test.sagepay.com/Simulator/3DAuthPage.asp"
			);
		}
		else{$arrResponse = requestPost($strPurchaseURL, $strPost);}
		//$arrResponse = requestPost($strPurchaseURL, $strPost);
	
		/* Analyse the response from Sage Pay Direct to check that everything is okay
		** Registration results come back in the Status and StatusDetail fields */
		$strStatus=$arrResponse["Status"];
		$strStatusDetail=$arrResponse["StatusDetail"];
						
		if ($strStatus=="3DAUTH") 
		{
			/* This is a 3D-Secure transaction, so we need to redirect the customer to their bank
			** for authentication.  First get the pertinent information from the response */
			$strMD=$arrResponse["MD"];
			$strACSURL=$arrResponse["ACSURL"];
			$strPAReq=$arrResponse["PAReq"];
			$strPageState="3DRedirect";
		}            
		elseif ($strStatus=="PPREDIRECT") 
		{ 
			/* The customer needs to be redirected to a PayPal URL as PayPal was chosen as a card type or
			** payment method and PayPal is active for your account. A VPSTxId and a PayPalRedirectURL are
			** returned in this response so store the VPSTxId in your database now to match to the response
			** after the customer is redirected to the PayPalRedirectURL to go through PayPal authentication */
			$strPayPalRedirectURL=$arrResponse["PayPalRedirectURL"];
			$strVPSTxId=$arrResponse["VPSTxId"];
			$strPageState="PayPalRedirect";

			// Update the current order in the database to store the newly acquired VPSTxId 
			/*$strSQL="UPDATE cart_orders SET `VPSTxId`='" . mysql_real_escape_string($strVPSTxId) . "'";
		$strSQL.=",`pay_method`='paypal'";
		$strSQL.=",`pay_status`='0'";
		$strSQL.=",`order_status`='New'"; 
		$strSQL.=" WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
		ysql_query($strSQL) or die ();*/
		$strSQL="UPDATE cart_orders SET `VPSTxId`=?";
		$strSQL.=",`pay_method`='paypal'";
		$strSQL.=",`pay_status`='0'";
		$strSQL.=",`order_status`='New'"; 
		$strSQL.=" WHERE `VendorTxCode`=?";
		$q=$db1->prepare($strSQL);
		$q->execute(array($strVPSTxId,$strVendorTxCode));
		$strSQL="";
			
			// Redirect customer to go through PayPal Authentication
			ob_end_flush();
			redirect($strPayPalRedirectURL);
			exit();
		}
		else
		{
			/*$getinvoices=ysql_query("SELECT `invoice`,`Status` FROM cart_orders WHERE `VendorTxCode`='$strVendorTxCode'");
			list($invoice,$checkstatus)=mysql_fetch_row($getinvoices);*/
			$getinvoices=$db1->prepare("SELECT `invoice`,`Status` FROM cart_orders WHERE `VendorTxCode`=?");
			$getinvoices->execute(array($strVendorTxCode));
			list($invoice,$checkstatus)=$getinvoices->fetch(PDO::FETCH_NUM);
			if(strlen($checkstatus)<1){//prevent duplicated requests
				/* If this isn't 3D-Auth, then this is an authorisation result (either successful or otherwise) **
				** Get the results form the POST if they are there */
				$strVPSTxId=$arrResponse["VPSTxId"];
				$strSecurityKey=$arrResponse["SecurityKey"];
				$strTxAuthNo=$arrResponse["TxAuthNo"];
				$strAVSCV2=$arrResponse["AVSCV2"];
				$strAddressResult=$arrResponse["AddressResult"];
				$strPostCodeResult=$arrResponse["PostCodeResult"];
				$strCV2Result=$arrResponse["CV2Result"];
				$str3DSecureStatus=$arrResponse["3DSecureStatus"];
				$strCAVV=$arrResponse["CAVV"];
						
				// Update the database and redirect the user appropriately				
				if ($strStatus=="OK")
					$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
				elseif ($strStatus=="OKFREE")
					$strDBStatus="The Transaction was free.";
				elseif ($strStatus=="MALFORMED")
					$strDBStatus="MALFORMED - The StatusDetail was: " . substr($strStatusDetail,0,255);
				elseif ($strStatus=="INVALID")
					$strDBStatus="INVALID - The StatusDetail was: " . substr($strStatusDetail,0,255);
				elseif ($strStatus=="NOTAUTHED")
					$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
				elseif ($strStatus=="REJECTED")
					$strDBStatus="REJECTED - The transaction failed because 3D-Secure or AVS/CV2 authorisation failed.";
				elseif ($strStatus=="AUTHENTICATED")
					$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
				elseif ($strStatus=="REGISTERED")
					$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
				elseif ($strStatus=="ERROR")
					$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . $strStatusDetail;
				else
					$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . $strStatus . ", with StatusDetail: " . $strStatusDetail;
	
				// Update our database with the results from the Notification POST
				/*$strSQL="UPDATE cart_orders SET `Status`='" . mysql_real_escape_string($strDBStatus) . "'";
				if (strlen($strVPSTxId)>0) $strSQL.=",`VPSTxId`='" . mysql_real_escape_string($strVPSTxId) . "'";
				if (strlen($strSecurityKey)>0) $strSQL.=",`SecurityKey`='" . mysql_real_escape_string($strSecurityKey) . "'";
				if (strlen($strTxAuthNo)>0) $strSQL.=",`TxAuthNo`=" . mysql_real_escape_string($strTxAuthNo);
				if (strlen($strAVSCV2)>0) $strSQL.=",`AVSCV2`='" . mysql_real_escape_string($strAVSCV2) . "'";
				if (strlen($strAddressResult)>0) $strSQL.=",`AddressResult`='" . mysql_real_escape_string($strAddressResult) . "'";
				if (strlen($strPostCodeResult)>0) $strSQL.=",`PostCodeResult`='" . mysql_real_escape_string($strPostCodeResult) . "'";
				if (strlen($strCV2Result)>0) $strSQL.=",`CV2Result`='" . mysql_real_escape_string($strCV2Result) . "'";
				if (strlen($strGiftAid)>0) $strSQL.=",`GiftAid`=" . mysql_real_escape_string($strGiftAid);
				if (strlen($str3DSecureStatus)>0) $strSQL.=",`ThreeDSecureStatus`='" . mysql_real_escape_string($str3DSecureStatus) . "'";
				if (strlen($strCAVV)>0) $strSQL.=",`CAVV`='" . mysql_real_escape_string($strCAVV) . "'";
				if (strlen($strDBStatus)>0) $strSQL.=",`Status`='" . mysql_real_escape_string($strDBStatus) . "'";
				$strSQL.=",`pay_method`='cc'";
				$strSQL.=",`pay_status`='".(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?1:0)."'";
				$strSQL.=",`order_status`='New'";
				$strSQL.=",`iorder_status`='0'";
				$strSQL.=" WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
	
				ysql_query($strSQL) or die ();*/
				$binds=array();
				$strSQL="UPDATE cart_orders SET `Status`=?";$binds[]=$strDBStatus;
				if (strlen($strVPSTxId)>0) {$strSQL.=",`VPSTxId`=?";$binds[]=$strVPSTxId;}
				if (strlen($strSecurityKey)>0) {$strSQL.=",`SecurityKey`=?";$binds[]=$strSecurityKey;}
				if (strlen($strTxAuthNo)>0) {$strSQL.=",`TxAuthNo`=?";$binds[]=$strTxAuthNo;}
				if (strlen($strAVSCV2)>0) {$strSQL.=",`AVSCV2`=?";$binds[]=$strAVSCV2;}
				if (strlen($strAddressResult)>0){ $strSQL.=",`AddressResult`=?";$binds[]=$strAddressResult;}
				if (strlen($strPostCodeResult)>0) {$strSQL.=",`PostCodeResult`=?";$binds[]=$strPostCodeResult;}
				if (strlen($strCV2Result)>0) {$strSQL.=",`CV2Result`=?";$binds[]=$strCV2Result;}
				if (strlen($strGiftAid)>0) {$strSQL.=",`GiftAid`=?";$binds[]=$strGiftAid;}
				if (strlen($str3DSecureStatus)>0){ $strSQL.=",`ThreeDSecureStatus`=?";$binds[]=$str3DSecureStatus;}
				if (strlen($strCAVV)>0) {$strSQL.=",`CAVV`=?";$binds[]=$strCAVV;}
				if (strlen($strDBStatus)>0){ $strSQL.=",`Status`=?";$binds[]=$strDBStatus;}
				$strSQL.=",`pay_method`='cc'";
				$strSQL.=",`pay_status`='".(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?1:0)."'";
				$strSQL.=",`order_status`='New'";
				$strSQL.=",`iorder_status`='0'";
				$strSQL.=" WHERE `VendorTxCode`=?";$binds[]=$strVendorTxCode;
	
				$q=$db1->prepare($strSQL);
				$q->execute($binds);
			}
			// Work out where to send the customer
			$_SESSION["VendorTxCode"]=$strVendorTxCode;
			if (in_array($strStatus,array("OK","OKFREE","AUTHENTICATED","REGISTERED")))
				$strCompletionURL=SECUREBASE."/index.php?p=cart_co_payment&co=orderSuccessful";
			else {
				$strCompletionURL=SECUREBASE."/index.php?p=cart_co_payment&co=orderFailed";
				$strPageError=$strDBStatus;
			}

			// Finally, if we're in LIVE then go stright to the success page
			//In other modes, we allow this page to display and ask for Proceed to be clicked
			if ($strConnectTo!=="SIMULATOR") {
				ob_end_flush();
				redirect($strCompletionURL);
				exit();
			}
		}
	}
}

?>

<script type="text/javascript" language="javascript" src="<?=$root_to_cart?>sagepay/scripts/common.js" ></script>

<?php 
if ($strPageState=="Payment") 
{
	//We need the customer to enter their card details, so display an entry page for them
	if (strlen($strPageError)>0) {
	?>
	<div id="errorbox"><p>Input Validation Error</p><?php echo $strPageError ?></div>
	<?php
	}
	?>
	<form name="storeform" method="post" action="<?=SECUREBASE?>/index.php?p=cart_co_payment&co=payment" onSubmit="javascript:timerStart('processtimer')">
	<input type="hidden" name="navigate" value="proceed" />
	<input type="hidden" name="PageState" value="CardDetails">
	<table class="details" summary="Credit card payment options">
		<tr class="head"><td colspan="2"><div class="titles">Pay by credit/debit card</div></td></tr>
		<tr> 
			<td class="left_light"><label for="cardholder_name">Card Holder Name (as printed on card)</label></td>
			<td class="right_light"><input name="CardHolder" type="text" value="<?php echo $strCardHolder ?>" maxlength="50" id="cardholder_name" class="input_text" onFocus="this.select()"></td>
		</tr>
		<tr> 
			<td class="left_dark"><label for="cc_type">Select card type</label></td>
			<td class="right_dark">
				<SELECT NAME="CardType" onChange="cardTypeChanged(this);" id="cc_type">
				<option value="VISA" <?=(($strCardType=="VISA")?" SELECTED ":""); ?> >VISA Credit</option>
				<option value="DELTA" <?=(($strCardType=="DELTA")?" SELECTED ":""); ?> >VISA Debit</option>
				<option value="UKE" <?=(($strCardType=="UKE")?" SELECTED ":""); ?> >VISA Electron</option>
				<option value="MC" <?=(($strCardType=="MC")?" SELECTED ":""); ?> >MasterCard</option>
				<option value="MAESTRO" <?=(($strCardType=="MAESTRO")?" SELECTED ":""); ?> >Maestro</option>
				<!--<option value="AMEX" <?/*=(($strCardType=="AMEX")?" SELECTED ":"");*/?> >American Express</option>-->
				<!--<option value="DC" <?/*=(($strCardType=="DC")?" SELECTED ":"");*/?> >Diner's Club</option>-->
				<!--<option value="JCB" <?/*=(($strCardType=="JCB")?" SELECTED ":"");*/?> >JCB Card</option>-->
				<!--<option value="LASER" <?/*=(($strCardType=="LASER")?" SELECTED ":"");*/?> >Laser</option>-->
				<option value="SOLO" <?=(($strCardType=="SOLO")?" SELECTED ":""); ?> >Solo</option>
				<!--<option value="PAYPAL" <?/*=(($strCardType=="PAYPAL")?" SELECTED ":"");*/?> >PayPal</option>-->
				</SELECT>
				<script type="text/javascript">
						function cardTypeChanged(selectObject) 
						{
								if(selectObject.value=='PAYPAL') {
										var sDisabledBGColour = "#DDDDDD";
										document.storeform.CardHolder.value='';
										document.storeform.CardHolder.style.background=sDisabledBGColour;
													document.storeform.CardHolder.disabled = true;
										document.storeform.CardNumber.value='';
										document.storeform.CardNumber.style.background=sDisabledBGColour;
													document.storeform.CardNumber.disabled = true;
										document.storeform.StartDate1.value='';
										document.storeform.StartDate1.style.background=sDisabledBGColour;
													document.storeform.StartDate1.disabled = true;
										document.storeform.StartDate2.value='';
										document.storeform.StartDate2.style.background=sDisabledBGColour;
													document.storeform.StartDate2.disabled = true;
										document.storeform.ExpiryDate1.value='';
										document.storeform.ExpiryDate1.style.background=sDisabledBGColour;
													document.storeform.ExpiryDate1.disabled = true;
										document.storeform.ExpiryDate2.value='';
										document.storeform.ExpiryDate2.style.background=sDisabledBGColour;
													document.storeform.ExpiryDate2.disabled = true;
										document.storeform.IssueNumber.value='';
										document.storeform.IssueNumber.style.background=sDisabledBGColour;
													document.storeform.IssueNumber.disabled = true;
										document.storeform.CV2.value='';
										document.storeform.CV2.style.background=sDisabledBGColour;
													document.storeform.CV2.disabled = true;
										alert('You just selected a payment method of PayPal so card details will not be required here.\n\nAfter clicking \'Proceed\' you will be securely redirected to the PayPal website to authorise your details.');
											} else {
													document.storeform.CardHolder.disabled = false;
										document.storeform.CardHolder.style.background = "";
													document.storeform.CardNumber.disabled = false;
										document.storeform.CardNumber.style.background = "";
													document.storeform.StartDate1.disabled = false;
										document.storeform.StartDate1.style.background = "";
													document.storeform.StartDate2.disabled = false;
										document.storeform.StartDate2.style.background = "";
													document.storeform.ExpiryDate1.disabled = false;
										document.storeform.ExpiryDate1.style.background = "";
													document.storeform.ExpiryDate2.disabled = false;
										document.storeform.ExpiryDate2.style.background = "";
													document.storeform.IssueNumber.disabled = false;
										document.storeform.IssueNumber.style.background = "";
													document.storeform.CV2.disabled = false;
										document.storeform.CV2.style.background = "";
								}
						}
				</script>
			</td>
		</tr>
		<tr> 
			<td class="left_light"><label for="cc_number">Credit/Debit card number</label></td>
			<td class="right_light"><input name="CardNumber" type="text" autocomplete="off" id="cc_number" class="input_text" onFocus="this.select()"></td>
		</tr>
		<tr> 
			<td class="left_dark"><label for="cc_month">Expiry date</label></td>
			<td class="right_dark">
			<select name="ExpiryDate1" id="cc_month" class="input_textm">
			<option value="">month</option>
			<?php for($x=1;$x<13;$x++){?>
			<option value="<?=(($x<10)?"0":"").$x?>"><?=(($x<10)?"0":"").$x?></option>
			<?php }?>
			</select>
			<select name="ExpiryDate2" class="input_textm">
			<option value="">year</option>
			<?php for($x=date("y");$x<date("y")+6;$x++){?>
			<option value="<?=$x?>">20<?=$x?></option>
			<?php }?>
			</select>
			</td>
		</tr>
		<tr> 
			<td class="left_light"><label for="cv2">CV2 (3 digit security code)</label></td>
			<td class="right_light"><input name="CV2" type="text" value="" maxlength="4" id="cv2" class="input_textm" onFocus="this.select()"><br /><img src="content/images/main/mini_cvv2.gif" alt="" /></td>
		</tr>
		<tr class="subhead"><td colspan="2">Maestro/Switch/Solo only</td></tr>
		<tr> 
			<td class="left_dark"><label for="issuenumber">Issue number</label></td>
			<td class="right_dark"><input name="IssueNumber" type="text" value="<?php echo $strIssueNumber ?>" maxlength="2" id="issuenumber" class="input_textm" onFocus="this.select()"></td>
		</tr>
		<tr> 
			<td class="left_light"><label for="cc_start_month">Start date</label></td>
			<td class="right_light"><select name="StartDate1" id="cc_start_month" class="input_textm">
			<option value="">month</option>
			<?php for($x=1;$x<13;$x++){?>
			<option value="<?=(($x<10)?"0":"").$x?>"><?=(($x<10)?"0":"").$x?></option>
			<?php }?>
			</select>
			<select name="StartDate2" class="input_textm">
			<option value="">year</option>
			<?php for($x=date("y")-5;$x<date("y")+9;$x++){?>
			<option value="<?=$x?>">20<?=$x?></option>
			<?php }?>
			</select></td>
		</tr>
	</table><br />
	<table class="details" summary="skip this table unless you have any special requirements">
		<tr class="head"><td colspan="2"><div class="titles">Special Requirements</div></td></tr>
		<tr>
			<td class="left_light" style="vertical-align:top"><label for="comments">Special requirements</label><br /><dfn>* Please note: All goods MUST be signed for BY THE ADDRESSEE and will not be left with a neighbour or unattended.</dfn></td>
			<td class="right_light"><textarea name="comments" id="comments" style="height:120px;<?=$deviceType=="phone"?"":"width:400px;"?>" onFocus="this.select()">special requirements</textarea></td>
		</tr>
	</table>
	<p class="submit"><span style="color:#CD071E">Please do not stop the processing once the button is pressed</span><br /><span style="display:inline-block;border:1px solid #999;padding:2px;" id="processtimer">0:00</span> <input type="submit" value="Complete Order" style="display:inline"></p>
	</form><?php
}						
elseif ($strPageState=="3DRedirect") /*3D secure box*/
{ 
	//A 3D-Auth response has been returned, so show the bank page inline if possible, or redirect to it otherwise
	?>
	<table class="formTable">
		<tr>
			<td><div class="subheader">3D-Secure Authentication with your Bank</div></td>
		</tr>
		<tr>
			<td>
				<table class="formTable">
					<tr>
						<td width="80%">
							<p>To increase the security of Internet transactions Visa and Mastercard have introduced 3D-Secure (like an online version of Chip and PIN). <br>
									<br>
									You have chosen to use a card that is part of the 3D-Secure scheme, so you will need to authenticate yourself with your bank in the section below.
								</p>
						</td>
						<td width="20%" align="center"><img src="<?=$root_to_cart?>/images/vbv_logo_small.gif" alt="Verified by Visa"><BR><BR><img src="<?=$root_to_cart?>/images/mcsc_logo.gif" alt="MasterCard SecureCode"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top">
			<?php
			// Attempt to set up an inline frame here.  If we can't, set up a standard full page redirection
			$_SESSION["MD"]=$strMD;
			$_SESSION["PAReq"]=$strPAReq;
			$_SESSION["ACSURL"]=$strACSURL;
			$_SESSION["VendorTxCode"]=$strVendorTxCode;
			$_SESSION['fqdncart']=$strYourSiteFQDN.$root_to_cart;
			$_SESSION['fqdn']=$strYourSiteFQDN;
			?>
			<iframe src=" <?=SECUREBASE."/".$root_to_cart?>sagepay/3DRedirect.php" name="3DIFrame" width="100%" height="500" frameborder="0">
			<!--Non-IFRAME browser support-->
			<script language="Javascript"> function OnLoadEvent() { document.form.submit(); }</script>
			<html><head><title>3D Secure Verification</title></head>
				<body OnLoad="OnLoadEvent();">
				<form name="form" action="<?=$strACSURL ?>" method="post">
				<input type="hidden" name="PaReq" value="<?php echo $strPAReq ?>"/>
				<input type="hidden" name="TermUrl" value="<?=SECUREBASE . "/" . $root_to_cart."sagepay/3DCallBack.php?VendorTxCode=" . $strVendorTxCode ?>"/>
				<input type="hidden" name="MD" value="<?php echo $strMD ?>"/>

			<NOSCRIPT> 
			<center><p>Please click button below to Authenticate your card</p><input type="submit" value="Go"/></p></center>
			</NOSCRIPT>
			</form></body></html>
			</iframe>
			</td>
		</tr>
	</table><?php
}
else // The customer has already entered their card details and we're displaying the result - would have been redirected at this point if LIVE
{
	if(strlen($strPageError)!=0){
		?>
		<h2>Your order has <span style="text-decoration:underline">NOT</span> been successful.</h2>
		<p>Your transaction was not successful for the following reason:
			<br />
			<span class="warning"><strong><?=$strPageError?></strong></span><br />
		</p>
		<p>To place your order, please contact our sales team on <?=$sales_phone?>.</p>
		<h3>Your basket contents</h3>
		<?php 
		cart_contents(0);
	}		  
	if ($strConnectTo!=="LIVE") 
	{ 
			?><p>*** TEST INFORMATION BELOW - NOT SHOWN ON LIVE SITE ***</p><?php
		if (strlen($strPageError)==0) 
		{ 
			//There are no errors to display, so show the detail of the POST to Sage Pay Direct							
				echo
			"<p>This page shows the contents of the POST sent to Sage Pay Direct (based on your selections on the previous screens)
				and the response sent back by the system. Because you are in SIMULATOR mode, you are seeing this information
				and having to click Proceed to continue to the payment pages. In LIVE mode, the POST and redirect 
			happen invisibly, with no information sent to the browser and no user involvement.</p>";
		}
		else 
		{
			//An error occurred during transaction registration. Show the details here
			echo
			"<p>A problem occurred whilst attempting to register this transaction with the Sage Pay systems.
			The details of the error are shown below. This information is provided for your own debugging 
			purposes and especially once LIVE you should avoid displaying this level of detail to your customers. 
			Instead you should modify the transactionRegistration.php page to automatically handle these errors and 
			redirect your customer appropriately (e.g. to an error reporting page, or alternative customer 
			services number to offline payment)</p>";
		}
		?>
		
	<table class="details">
		<tr class="head">
			<td colspan="2"><div class="titles">Sage Pay Direct returned a Status of <span style="background:<?=(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?"#67F76A":"#F73C38")?>"><?=$strStatus?></span></div></td>
		</tr>
		<?php if(strlen($strPageError)>0){?>
		<tr class="infohead">
			<td colspan="2"><?=$strPageError ?></td>
		</tr>
		<?php }?>
		<tr class="subhead">
			<td colspan="2">Post Sent to Sage Pay Direct</td>
		</tr>
		<tr>
			<td colspan="2" class="right_light" style="word-break: break-all; word-wrap: break-word;"><?php echo $strPost ?></td>
		</tr>
		<tr class="subhead">
			<td colspan="2">Reply from Sage Pay Direct</td>
		</tr>
		<tr>
			<td colspan="2" class="right_light" style="word-break: break-all; word-wrap: break-word;"><?php echo $_SESSION["rawresponse"] ?></td>
		</tr>
		<tr class="subhead">
			<td colspan="2">Order Details stored in your Database</td>
		</tr>
		<tr>
			<td class="left_light">VendorTxCode:</td>
			<td class="right_light"><?php echo $strVendorTxCode ?>&#160;</td>
		</tr>
		<tr>
			<td class="left_dark">VPSTxId:</td>
			<td class="right_dark"><?php echo $strVPSTxId ?>&#160;</td>
		</tr>
		<?php
		if (strlen($strSecurityKey)>0) 
		{
		$row=!isset($row)||$row=="_dark"?"_light":"_dark";
		?>
		<tr>
			<td class="left<?=$row?>">SecurityKey:</td>
			<td class="right<?=$row?>"><?php echo $strSecurityKey ?>&#160;</td>
		</tr>
		<?php
		}
		if (strlen($strTxAuthNo)>0) 
		{$row=!isset($row)||$row=="_dark"?"_light":"_dark";
		?>
		<tr>
			<td class="left<?=$row?>">TxAuthNo:</td>
			<td class="right<?=$row?>"><?php echo $strTxAuthNo ?>&#160;</td>
		</tr>
		<?php
		}
		if (strlen($str3DSecureStatus)>0) 
		{$row=!isset($row)||$row=="_dark"?"_light":"_dark";
		?>
		<tr>
			<td class="left<?=$row?>">3D-Secure Status:</td>
			<td class="right<?=$row?>"><?php echo $str3DSecureStatus ?>&#160;</td>
		</tr>
		<?php
		}
		if (strlen($strCAVV)>0) 
		{$row=!isset($row)||$row=="_dark"?"_light":"_dark";
		?>
		<tr>
			<td class="left<?=$row?>">CAVV:</td>
			<td class="right<?=$row?>"><?php echo $strCAVV ?>&#160;</td>
		</tr>
		<?php
		}
		?>
		</table><p>&#160;</p>
		
	<form name="customerform" action="<?=$strCompletionURL ?>" method="POST">
	<input type="hidden" name="navigate" value="" />
	<input type="hidden" name="CompletionURL" value="<?=$strCompletionURL ?>">
	<input type="hidden" name="PageState" value="Completion">
	<div class="formFooter">
		<a href="javascript:submitForm('customerform','proceed');" title="Proceed to the completion screens" style="float: right;">Proceed</a>
	</div>
	</form>
	<?php
	}
}
?>




