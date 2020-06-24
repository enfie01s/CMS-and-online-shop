<?php
//if($_SERVER['REQUEST_METHOD']!="POST"){die("Access Denied");}//direct access security 
//echo "hi";
session_name("gmk");
session_start();
include "../../../config.inc.php";
include("../cart_functions.php");
//include("../cart_vars.php");
include "../cart_usession.php";
include("includes.php");
$strCart=$_SESSION['cart'];
if($_SESSION['test']==1){
	
	ini_set('display_startup_errors',1);
	ini_set('display_errors',1);
	error_reporting(-1);
	
}
if(count($strCart)==0) 
{
	redirect(MAINBASE."/index.php?p=cart_basket");
	exit();
}
if(cart_postapplicable()==1&&isset($post_arr['shipping'])){$_SESSION['shipping']=$post_arr['shipping'];}
//if session == 11 and freepost == 0 change session
if(isset($_SESSION['shipping'])&&$_SESSION['shipping']==$freepostid&&$freepost==0&&cart_postapplicable()==1){$_SESSION['shipping']=5;$_SESSION['postdesc']="P&P";}
$_SESSION['postdesc']=isset($_SESSION['postdesc'])&&strlen($_SESSION['postdesc'])>0?(cart_postapplicable()?$_SESSION['postdesc']:$freepostedsc):($freepost==1||cart_postapplicable()==0?$freepostedsc:"P&P");
$_SESSION['shipping']=isset($_SESSION['shipping'])&&strlen($_SESSION['shipping'])>0?(cart_postapplicable()?$_SESSION['shipping']:$freepostid):($freepost==1||cart_postapplicable()==0?$freepostid:5);
$_SESSION['postdesc']=!isset($_SESSION['postdesc'])?(cart_postapplicable()?$_SESSION['postdesc']:$freepostedsc):$_SESSION['postdesc'];
$_SESSION['shipping']=!isset($_SESSION['shipping'])?(cart_postapplicable()?$_SESSION['shipping']:$freepostid):$_SESSION['shipping'];

if(isset($_SESSION['shipping']))
{
	if($freepost!=1)
	{
		/*$pq="SELECT `restraints`,cpd.`description` as description,`field1`,`field2`,`field3`,cpd.`post_id` as post_id FROM cart_postage as cp,cart_postage_details as cpd WHERE cp.`post_id`=cpd.`post_id` AND `post_details_id`='".$_SESSION['shipping']."'";
		$postageq=ysql_query($pq,CARTDB);		
		$postage=mysql_fetch_assoc($postageq);*/
		$pq="SELECT `restraints`,cpd.`description` as description,`field1`,`field2`,`field3`,cpd.`post_id` as post_id FROM cart_postage as cp,cart_postage_details as cpd WHERE cp.`post_id`=cpd.`post_id` AND `post_details_id`=?";
		$postageq=$db1->prepare($pq);
		$postageq->execute(array($_SESSION['shipping']));		
		$postage=$postageq->fetch(PDO::FETCH_ASSOC);
		//print_r($postage);
		//echo $pq;
		$restraints=explode("#",$postage['restraints']);
		if(strlen($restraints[0])>0)
		{
			$stamp=strtotime($restraints[2]);
		}
	}
	if(strlen($restraints[0])>0&&cart_postage_expired($stamp)&&$freepost!=1)
	{
		redirect(MAINBASE."/index.php?p=cart_co_postage");
		exit();
		//echo "Time restrains for chosen postage method have expired, please go back and choose a different method";
	}
	else
	{
		/* All required fields are present, so first store the order in the database then format the POST to Sage Pay Direct 
		** First we need to generate a unique VendorTxCode for this transaction
		** We're using VendorName, time stamp and a random element.  You can use different methods if you wish
		** but the VendorTxCode MUST be unique for each transaction you send to Sage Pay Direct */
		$strTimeStamp = date("y/m/d : H:i:s", time());
		$intRandNum = rand(0,32000)*rand(0,32000);
		$strVendorTxCode=cleanInput($strVendorName . "-" . $strTimeStamp . "-" . $intRandNum,"VendorTxCode");
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
				list($sp)=$sprs->fetch();
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
		$strComments=(strtolower($_REQUEST["comments"])!="special requirements")?cleanInput($_REQUEST["comments"],"Text"):"";	
	
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
		
		$strPost.="&NotificationURL=" . $strYourSiteFQDN . $strVirtualDir . "cart/sagepay/notificationPage.php";
		
		
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
		 		
		
	
		// Set other optionals
		$strPost.="&CustomerEMail=" . urlencode($strCustomerEMail);
		$strPost.="&Basket=" . urlencode($strBasket); //As created above
	
		// For charities registered for Gift Aid, set to 1 to makr this as a Gift Aid transaction
		$strPost.="&AllowGiftAid=0";
		
		/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default
		** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
		if ($strTransactionType!=="AUTHENTICATE") $strPost.="&ApplyAVSCV2=0";
	
		// Send the IP address of the person entering the card details
		//$strPost.="&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];
	
		/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default **
		** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
		$strPost.="&Apply3DSecure=".(ISLOCALHN==1?2:0);
		$strPost.="&Profile=NORMAL"; //NORMAL is default setting. Can also be set to LOW for the simpler payment page version.
		/* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
		** If you are developing back-office applications for Mail Order/Telephone order, use M **
		** If your back office application is a subscription system with recurring transactions, use C **
		** Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E **/
		//$strPost.="&AccountType=E";
		if($runningTotal<1&&$_SESSION['discount_amount']>0)//free due to discount
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
			"ACSURL"=>"",			
			"NextURL"=>SECUREBASE."/index.php?p=cart_co_payment&co=orderSuccessful&VendorTxCode=".$strVendorTxCode
			);
		}
		else
		{$arrResponse = requestPost($strPurchaseURL, $strPost);}
			/* Analyse the response from Sage Pay Server to check that everything is okay
		** Registration results come back in the Status and StatusDetail fields */
		$strStatus=$arrResponse["Status"];
		$strStatusDetail=$arrResponse["StatusDetail"];
		
		//print_r($arrResponse);
		//echo $strPost;
		if(substr($strStatus,0,2)=="OK"||substr($strStatus,0,2)=="OKFREE")
		{
			$strVPSTxId=$arrResponse["VPSTxId"];
			$strSecurityKey=$arrResponse["SecurityKey"];
			$strNextURL=$arrResponse["NextURL"];
			/* Now store the order total and order details in your database for use in your own order fulfilment
		** These kits come with a table called tblOrders in which this data is stored
		** accompanied by the tblOrderProducts table to hold the basket contents for each order */
		$strSQL="SELECT MAX(`invoice`) FROM cart_orders";
		/*$rsPrimary = ysql_query($strSQL,CARTDB)	or die ();
		list($lastinvoice)=mysql_fetch_row($rsPrimary);*/
		$rsPrimary = $db1->query($strSQL);
		list($lastinvoice)=$rsPrimary->fetch();
		$rsPrimary="";
		$strSQL="";
		$strSQL="INSERT INTO cart_orders(`VendorTxCode`, `TxType`, `total_price`, `cust_id`, `invoice`, `date_ordered`, `ship_description`, `ship_method_id`, `ship_total`, `session_id`, `discount_code`, `discount`,`nametitle`, `firstname`, `lastname`, `email`, `address1`, `address2`, `city`, `postcode`, `country`, `state`, `phone`, `sameasbilling`, `alt_nametitle`,`alt_name`, `alt_address1`, `alt_address2`, `alt_city`, `alt_postcode`, `alt_country`, `alt_state`, `alt_phone`, `tax_rate`, `tax_price`, `comments`, `CardType`,`IP`,`Status`,`VPSTxId`, `SecurityKey`) VALUES (";

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
		$strSQL.="'".(isset($_SESSION['discount_code'])?mysql_real_escape_string($_SESSION['discount_code']):"")."',";
		$strSQL.="'".(isset($_SESSION['discount_amount'])?mysql_real_escape_string($_SESSION['discount_amount']):"")."',";*/
		$binds=array();
		$strSQL.="?,";$binds[]=$strVendorTxCode; //Add the VendorTxCode generated above
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
		$strSQL.="?,";$binds[]=session_id();
		if(isset($_SESSION['discount_code'])){$strSQL.="?,";$binds[]=$_SESSION['discount_code'];}else{$strSQL.="null,";}
		if(isset($_SESSION['discount_amount'])){$strSQL.="?,";	$binds[]=$_SESSION['discount_amount'];}else{$strSQL.="null,";}
    //** If this is a PaypalExpress checkout method then NO billing and delivery details are available here **
    if (isset($isPaypalExpress) && $isPaypalExpress == true) 
		{
				$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, null, null, ";
				$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, 'PAYPAL',";
		}
		else
		{
			/*// Add the Billing details 
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
			$strSQL.="'".(isset($strCardType)?mysql_real_escape_string($strCardType):"")."',";// Card Type*/
			// Add the Billing details 
			$strSQL.="?,";  $binds[]=$strBillingNametitle;
			$strSQL.="?,"; $binds[]=$strBillingFirstnames;  
			$strSQL.="?,";  $binds[]=$strBillingSurname;
			
			// -Customer email 
			if(strlen($strCustomerEMail)>0){$strSQL.="?,"; $binds[]=$strCustomerEMail;}else{$strSQL.="null,";}
			
			$strSQL.="?,"; $binds[]=$strBillingAddress1; 
			if(strlen($strBillingAddress2)>0){$strSQL.="?,";$binds[]=$strBillingAddress2;}else{$strSQL.="null,";}
			$strSQL.="?,";  $binds[]=$strBillingCity;
			$strSQL.="?,"; $binds[]=$strBillingPostCode;
			$strSQL.="?,";  $binds[]=str_replace("GB","100",$strBillingCountry);
			if(strlen($strBillingState)>0){$strSQL.="?,"; $binds[]=$strBillingState;}else{$strSQL.="null,";}
			if(strlen($strBillingPhone)>0){$strSQL.="?,";$binds[]=$strBillingPhone;}else{$strSQL.="null,";}
			
			// Add the Delivery details 
			if(strlen($bIsDeliverySame)>0){$strSQL.="?,";$binds[]=$bIsDeliverySame;}else{$strSQL.="null,";}
			$strSQL.="?,"; $binds[]=$strBillingNametitle;
			$strSQL.="?,"; $binds[]=$strDeliveryFirstnames." ".$strDeliverySurname; 
			$strSQL.="?,"; $binds[]=$strDeliveryAddress1;
			if(strlen($strDeliveryAddress2)>0){$strSQL.="?,";$binds[]=$strDeliveryAddress2;}else{$strSQL.="null,";}
			$strSQL.="?,"; $binds[]=$strDeliveryCity; 
			$strSQL.="?,"; $binds[]=$strDeliveryPostCode;
			$strSQL.="?,";  $binds[]=str_replace("GB","100",$strDeliveryCountry);
			if(strlen($strDeliveryState)>0){$strSQL.="?,";$binds[]=$strDeliveryState; }else{$strSQL.="null,";}
			if(strlen($strDeliveryPhone)>0){$strSQL.="?,";$binds[]=$strDeliveryPhone;}else{$strSQL.="null,";}
			$strSQL.="?,";$binds[]=number_format($vat,2);//tax_rate
			$strSQL.="?,";$binds[]=number_format($vattotal,2);//tax_price
			if(strlen($strComments)>0){$strSQL.="?,";$binds[]=$strComments;}else{$strSQL.="null,";}//comments
			if(isset($strCardType)){$strSQL.="?,";$binds[]=$strCardType;}else{$strSQL.="null,";}// Card Type
		}
		/*$strSQL.="'".$_SERVER['REMOTE_ADDR'] ."',";
		$strSQL.="'".($_SESSION['test']==1?"TESTING":"")."',";		
		$strSQL.="'" . mysql_real_escape_string($strVPSTxId) . "',"; //Save the Sage Pay System's unique transaction reference
		$strSQL.="'" . mysql_real_escape_string($strSecurityKey) . "'"; //Save the MD5 Hashing security key, used in notification
		$strSQL.=")";
		
		//Execute the SQL command to insert this data to the tblOrders table
		ysql_query($strSQL,CARTDB) or die ($strSQL.mysql_error());	
		$order_insert_id=mysql_insert_id();*/
		$strSQL.="?,";$binds[]=$_SERVER['REMOTE_ADDR'];
		$strSQL.="".($_SESSION['test']==1?"'TESTING'":"null").",";	
		$strSQL.="?,";$binds[]=$strVPSTxId; //Save the Sage Pay System's unique transaction reference
		$strSQL.="?";$binds[]=$strSecurityKey; //Save the MD5 Hashing security key, used in notification
		$strSQL.=")";
		
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
			$rsPrimary = ysql_query($strSQL,CARTDB)	or die ('error');*/
			$strSQL = "SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,cf.salediscount,cf.saletype FROM ".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` WHERE p.`".PFIELDID."`=?";
			$rsPrimary = $db1->prepare($strSQL);
			$rsPrimary->execute(array($iProductId));
			$row = $rsPrimary->fetch();
			
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
			$sngPrice=$sprice;
			$sngTitle=$row['title'];
			$sngSeoTitle=urlencode($row['title']);
			$sngShort=isset($row['shortdesc'])?$row['shortdesc']:"";
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
			
			$oname=$iProductId=='358'?$cartarray['product']." (Shop: ".$cartarray['fromshop'].", Date:".$cartarray['purchasedate'].", Serial:".$cartarray['serial'].", Brand:".$cartarray['brand'].")":"";
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
			$rsPrimary = ysql_query($strSQL,CARTDB)or die (mysql_error());*/
			$binds=array();
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
			if(isset($cartarray['areaofinterest'])){$strSQL.="?";$binds[]=$cartarray['areaofinterest'];}else{$strSQL.="null";}
			$strSQL.=")";
			$rsPrimary = $db1->prepare($strSQL);
			$rsPrimary->execute($binds);
			$rsPrimary="";
			$strSQL="";
			$orderprods_insert_id=$db1->lastInsertId();
			if($cartarray['ispack']!=0)
			{
				foreach($cartarray['skuvariant'] as $ident => $newsku)
				{
					$expsku=explode("-qty-",$newsku);
				
					/*$optInfostr = "SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title,`vname` as item_desc FROM ".PTABLE." as p,cart_fusion as fo,cart_variants as ov WHERE p.`".PFIELDID."`=fo.`pid` AND po.`optid`=fo.`opt` AND ov.`voptid`=fo.`opt` AND ov.`vskuvar`='".$expsku[0]."' AND p.`".PFIELDID."`='$ident'";
					
					$optInfoq=ysql_query($optInfostr) or die ("Query '$optInfostr' failed with error message: \"" . mysql_error () . '"');
					$opts=mysql_fetch_assoc($optInfoq);*/
					$optInfostr = "SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title,`vname` as item_desc FROM ".PTABLE." as p,cart_fusion as fo,cart_variants as ov WHERE p.`".PFIELDID."`=fo.`pid` AND po.`optid`=fo.`opt` AND ov.`voptid`=fo.`opt` AND ov.`vskuvar`=? AND p.`".PFIELDID."`=?";
					
					$optInfoq=$db1->prepare($optInfostr);
					$optInfoq->execute(array($expsku[0],$ident));
					$opts=$optInfoq->fetch(PDO::FETCH_ASSOC);
					$strSQL="INSERT INTO cart_orderkits(`order_id`,`order_prod_id`,`kprod_id`,`prod_id`,`kit_title`,`okit_skuvar`,`item_qty`,`oname`,`oitem`) VALUES(";
					
					/*$strSQL.="'".$order_insert_id."',";
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
			
			if ($strConnectTo!=="SIMULATOR")
			{
				ob_flush();
				redirect($strNextURL);
				exit();		
			}
		}
		elseif ($strStatus=="MALFORMED")
		{	
			//echo $strPost."<br />";
			/** A MALFORMED status occurs when the POST sent above is not correctly formatted **
			** or is missing compulsory fields. You will normally only see these during **
			** development and early testing **/
			$strPageError="Sage Pay returned an MALFORMED status. The POST was Malformed because \"" . $strStatusDetail . "\"";	
			//echo $strPageError;	
			redirect(SECUREBASE."/index.php?p=cart_co_payment&co=orderFailed&error=001");		
		}
		elseif ($strStatus=="INVALID")
		{
			//echo $strPost."<br />";
			/** An INVALID status occurs when the structure of the POST was correct, but **
			** one of the fields contains incorrect or invalid data.  These may happen when live **
			** but you should modify your code to format all data correctly before sending **
			** the POST to Sage Pay Server **/
			$strPageError="Sage Pay returned an INVALID status. The data sent was Invalid because \"" . $strStatusDetail . "\"";
			echo $strPageError;	
			//redirect(SECUREBASE."/index.php?p=cart_co_payment&co=orderFailed&error=002");			
		}
		else
		{
			//echo $strPost."<br />";
			/** The only remaining status is ERROR **
			** This occurs extremely rarely when there is a system level error at Sage Pay **
			** If you receive this status the payment systems may be unavailable **<br>
			** You could redirect your customer to a page offering alternative methods of payment here **/
			$strPageError="Sage Pay returned an ERROR status. The description of the error was \"" . $strStatusDetail . "\"";
			//echo $strPageError;	
			redirect(SECUREBASE."/index.php?p=cart_co_payment&co=orderFailed&error=003");		
		}
	}
}
?>