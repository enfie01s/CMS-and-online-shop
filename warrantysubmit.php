<?php $httppath = "http://www.gmk.co.uk/";if(!isset($page)){header("Location: ".$httppath."index.php");}

if(isset($_POST['warrantysubmit']) && $submitagain == 1)//shouldbeempty is for form bots
{
	$_SESSION['submitagain']=date("U")+$formsubmitdelay;
	$_POST['areaofinterest']=isset($_POST['areaofinterest'])?implode(",",$_POST['areaofinterest']):"";
	$missingfield = false;
	$missingstring = "";
	$notifytext = "";
	$pwidth = "245px";
	
	foreach($_POST as $postfield => $postvalue)
	{
		if((in_array($postfield,$required) && trim($postvalue) == null ) || ($postfield == "email" && !eregi($emailereg, $postvalue)) || ($postfield == "fromshop" && trim($postvalue) == 'other' && trim($_POST['fromshop_other']) == null))//found some errors
		{
			$missingfield = true;
			$errordetail = ($postfield == "email" && $postvalue != null && !eregi($emailereg, $postvalue)) ? " is invalid.<br /><span class='errormsg' style='font-style:italic;'>Correct format: user@host.com</span>" : " is not filled in.";
			$notifytext .= "<li>&quot;".$warrantyarray[$postfield]."&quot;".$errordetail."</li>";
		}
		if($postfield=="skuvariant"){
			$wrong=0;
			if($_POST['skuvariant'][358]=="1215"&&($_POST['brand']!="BERETTA"&&$_POST['brand']!="FRANCHI"||($_POST['brand']=="FRANCHI"&&stripos($_POST['product'],"ALCIONE")===false))){$wrong=1;}//3 year option
			elseif($_POST['skuvariant'][358]=="1303"&&($_POST['brand']!="FRANCHI"||($_POST['brand']=="FRANCHI"&&stripos($_POST['product'],"AFFINITY")===false))){$wrong=1;}//7 year option
			elseif($_POST['skuvariant'][358]=="1216"&&$_POST['brand']!="BERETTA"){$wrong=1;}//10 year option
			elseif($_POST['skuvariant'][358]=="1290"&&$_POST['brand']!="BENELLI"){$wrong=1;}//5 year
			elseif($_POST['skuvariant'][358]=="1288"&&$_POST['brand']!="STOEGER"){$wrong=1;}//1 year
			elseif($_POST['skuvariant'][358]=="1289"&&$_POST['brand']!="SAKO"&&$_POST['brand']!="TIKKA"){$wrong=1;}//2 year
			if($wrong==1)
			{
				$missingfield = true;
				$notifytext .= "<li>Selected warranty length is not applicable to this brand/model.</li>";
			}
		}
		$missingstring .= "&amp;".$postfield;
		$missingstring .= $postvalue != null ? "=".urlencode(($postfield=="skuvariant"?$postvalue[358]:$postvalue)) : "";
	}	
	if(!isset($_POST["termsagree"])){$notifytext .= "<li>You did not agree to the terms &amp; conditions.</li>";}
	//$trimmed = strtoupper(trim(htmlspecialchars(mysql_real_escape_string($_POST['serial']))));
	$trimmed = strtoupper(trim(htmlspecialchars($_POST['serial'])));
	if(isset($_POST['serial']) && $trimmed != null)
	{
		/*$query = "SELECT serial FROM gmkserialnums WHERE `serial`='$trimmed' AND `Brand`='".mysql_real_escape_string($_POST['brand'])."'";
		$serial_query = ysql_query($query,$con1) or die(sql_error("Error"));
		$serial = mysql_fetch_row($serial_query);*/
		$query = "SELECT serial,registered FROM gmkserialnums WHERE `serial`=? AND `Brand`=?";
		$serial_query = $db1->prepare($query);
		$serial_query->execute(array($trimmed,$_POST['brand']));
		$serial = $serial_query->fetch();
		debug($query);
		if(!$serial)
		{
			$missingfield = true;
			$missingstring .= "&amp;invalidserial=1";
			$notifytext .= "<li>The {$_POST['brand']} serial number ('$trimmed') cannot be found. ".
			(preg_match('/5/',$trimmed)||stristr(strtolower($trimmed),"s")||preg_match('/0/',$trimmed)||stristr(strtolower($trimmed),"o")?"Please check you haven't typed a ".(preg_match('/5/',$trimmed)||stristr(strtolower($trimmed),"s")?"number 5 instead of the letter S, ":"").(preg_match('/0/',$trimmed)||stristr(strtolower($trimmed),"o")?"number 0 instead of the letter O ":"")."or vice versa.":"")
			."<br /><span class='errormsg' style='font-style:italic;'>For persistent errors, please call 01489 579 999 for assistance.</span></li>";
		$pwidth = "280px";
		}
		else if($serial['registered']==1)
		{
			$missingfield = true;
			$missingstring .= "&amp;invalidserial=1";
			$notifytext .= "<li>The serial number ('$trimmed') has already been registered. ".
			(preg_match('/5/',$trimmed)||stristr(strtolower($trimmed),"s")||preg_match('/0/',$trimmed)||stristr(strtolower($trimmed),"o")?"Please check you haven't typed a ".(preg_match('/5/',$trimmed)||stristr(strtolower($trimmed),"s")?"number 5 instead of the letter S, ":"").(preg_match('/0/',$trimmed)||stristr(strtolower($trimmed),"o")?"number 0 instead of the letter O ":"")."or vice versa.":"")
			."<br /><span class='errormsg' style='font-style:italic;'>For persistent errors, please call 01489 579 999 for assistance.</span></li>";
		$pwidth = "280px";
		}
	}
	if($_POST['wr_days'] > date('t',mktime(0,0,0,$_POST['wr_month'],1,$_POST['wr_year'])))//invalid date
	{
		$missingstring .= "&amp;daysover=1";
		$missingfield = true;
		$notifytext .= "<li>Date (".date('jS ',mktime(0,0,0,1,$_POST['wr_days'],1)).date('F Y',mktime(0,0,0,$_POST['wr_month'],1,$_POST['wr_year'])).") is invalid.<br /><span style='font-style:italic;' class='errormsg'>".date('F Y \c\o\n\t\a\i\n\s \o\n\l\y t \d\a\y\s',mktime(0,0,0,$_POST['wr_month'],1,$_POST['wr_year'])).".</span></li>";
		$pwidth = "295px";
	}
	if($missingfield == true || $_POST['shouldbeempty'] != null)
	{
		?><div class='failedsubmit'>The required fields (listed below) have errors.<br /><div class='missinglist' style='width:<?=$pwidth?>'><ul><?=$notifytext?></ul></div>Please click <a href='./warranty?missingfields<?=$missingstring?>'>HERE</a> to return to the registration form.</div>
		<?php
	}
	else 
	{ 
		$_POST['fromshop']=trim($_POST['fromshop']) == 'other'?"Other-".$_POST['fromshop_other']:$_POST['fromshop'];
		if($_POST['skuvariant'][358]!=1216)//passed all the tests with flying colours
		{
			//URL Variables
			$setokurl = "1";
			$okurls = "109.123.78.12";
			if ($setokurl == "1") // Security Check for URL
			{
				$referer = $_SERVER["SERVER_ADDR"];
				$found_url = ($referer == $okurls) ? "y" : "n";
			}	
			if($found_url == "y" || $_SESSION['test'] == 1)
			{
				// End URL Check	
				$submitok = 1;
				foreach($_POST as $postfield => $postvalue)
				{
					$$postfield = isset($postvalue)&&!is_array($postvalue)&&strlen($postvalue)>0?trim($postvalue):"";//generate variables for the email text
				}
				$date = $wr_days."-".$wr_month."-".$wr_year;//get formatted date
				include "./warrantyemail.php";
				if($_SESSION['test']==1)
				{
				$send_to="senfield@gmk.co.uk";
				$send_togmk="senfield@gmk.co.uk";
				}
				//$wid=1;//id of the warranty
				//genWarrantyXML($nametitle,$firstname,$lastname,$address1,$address2,$city,$county,$postcode,$telephone,$email,$wid);
				### INSERT TO DB ###
				include "cart/sagepay/includes.php";
				$strTimeStamp = date("y/m/d : H:i:s", time());
				$intRandNum = rand(0,32000)*rand(0,32000);
				$strVendorTxCode=cleanInput($strVendorPrefix . "-" . $strTimeStamp . "-" . $intRandNum,"VendorTxCode");
				$q=$db1->query("SELECT MAX(`invoice`) FROM cart_orders");
				list($lastinvoice)=$q->fetch(PDO::FETCH_NUM);
				$strSQL="INSERT INTO cart_orders(`VendorTxCode`,`total_price`, `cust_id`, `invoice`, `date_ordered`, `ship_description`,`ship_method_id`, `ship_total`, `session_id`,`nametitle`, `firstname`, `lastname`, `email`, `address1`, `address2`, `city`, `postcode`, `state`, `phone`, `sameasbilling`, `alt_nametitle`,`alt_name`, `alt_address1`, `alt_address2`, `alt_city`, `alt_postcode`, `alt_state`, `alt_phone`, `tax_rate`, `tax_price`, `IP`,`pay_method`,`pay_status`,`order_status`,`humanorderdate`) VALUES (";
				$binds=array();
				$strSQL.="?,";$binds[]=$strVendorTxCode; 
				$strSQL.="?,"; $binds[]="0";//Add the formatted total amount
				$strSQL.="?,";$binds[]=isset($ua['cust_id'])?$ua['cust_id']:"";//customer id
				$strSQL.="?,";$binds[]=$lastinvoice+1;//invoice
				$strSQL.="'" . date("U")."',";//date ordered
				$strSQL.="?,";$binds[]="Free P&P";
				$strSQL.="?,";$binds[]="7";
				$strSQL.="?,";$binds[]="0";//ship total
				/** Now save the fields returned from the Sage Pay System and extracted above **/
				$strSQL.="'" . session_id() . "',";
				// Add the Billing details 
				$strSQL.="?,";  $binds[]=$nametitle;
				$strSQL.="?,"; $binds[]=$firstname;  
				$strSQL.="?,";  $binds[]=$lastname;
				
				// -Customer email 
				if(strlen($email)>0){$strSQL.="?,"; $binds[]=$email;}else{$strSQL.="null,";}
				
				$strSQL.="?,"; $binds[]=$address1; 
				if(strlen($address2)>0){$strSQL.="?,";$binds[]=$address2;}else{$strSQL.="null,";}
				$strSQL.="?,"; $binds[]=$city; 
				$strSQL.="?,"; $binds[]=$postcode;
				if(strlen($county)>0){$strSQL.="?,"; $binds[]=$county;}else{$strSQL.="null,";}
				if(strlen($telephone)>0){$strSQL.="?,";$binds[]=$telephone;}else{$strSQL.="null,";}
				
				// Add the Delivery details 
				$strSQL.="?,";$binds[]="1";
				$strSQL.="?,"; $binds[]=$nametitle;
				$strSQL.="?,";  $binds[]=$firstname." ".$lastname;
				$strSQL.="?,"; $binds[]=$address1;
				if(strlen($address2)>0){$strSQL.="?,";$binds[]=$address2;}else{$strSQL.="null,";}
				$strSQL.="?,";  $binds[]=$city;
				$strSQL.="?,"; $binds[]=$postcode;
				if(strlen($county)>0){$strSQL.="?,"; $binds[]=$county;}else{$strSQL.="null,";}
				if(strlen($telephone)>0){$strSQL.="?,";$binds[]=$telephone;}else{$strSQL.="null,";}
				$strSQL.="?,";$binds[]=number_format($vat,2);//tax_rate
				$strSQL.="?,";$binds[]="0";//tax_price
				$strSQL.="?,";$binds[]=$_SERVER['REMOTE_ADDR'];//IP
				$strSQL.="?,";$binds[]="Free";//pay method
				$strSQL.="?,";$binds[]="1";//pay status
				$strSQL.="?,";$binds[]="New";//New/Dispatched order status
				$strSQL.="NOW()";
				$strSQL.=")";
				$q=$db1->prepare($strSQL);	
				$q->execute($binds);
				$order_insert_id=$db1->lastInsertId();
			
				$q=$db1->prepare("SELECT `vskuvar` FROM cart_variants WHERE `vid`=?");
				$q->execute(array($_POST['skuvariant'][358]));
				list($wsku)=$q->fetch(PDO::FETCH_NUM);
				
				$oname=$product." (Shop: ".$fromshop.", Date:".$date.", Serial:".$serial.")";
				$binds=array();
				$strSQL="INSERT INTO cart_orderproducts(`order_id`,`prod_id`,`VendorTxCode`,`qty`,`price`,`discount`,`exclude_discount`,`title`,`seo_title`,`sku`,`short_desc`,`taxable`,`postage_notes`,`goptid`,`oname`,`oitem`,`variant_id`,`ispack`,`areaofinterest`) VALUES(";
				$strSQL.="?,";$binds[]=$order_insert_id;
				$strSQL.="?,";$binds[]="358";
				$strSQL.="?,";$binds[]=$strVendorTxCode; //Add the VendorTxCode generated above
				$strSQL.="?,";$binds[]="1";
				$strSQL.="?,";$binds[]="0";
				$strSQL.="?,";$binds[]="0";
				$strSQL.="?,";$binds[]="0";
				$strSQL.="?,";$binds[]="Warranty";
				$strSQL.="?,";$binds[]="Warranty";
				$strSQL.="?,";$binds[]=str_replace("-v-NONE","",$wsku);
				$strSQL.="?,";$binds[]="";
				$strSQL.="?,";$binds[]="0";
				$strSQL.="?,";$binds[]="";
				$strSQL.="'',";//goptid
				$strSQL.="?,";$binds[]=$oname;//oname
				$strSQL.="?,";$binds[]="3 Year (Free)";
				$strSQL.="?,";$binds[]="NONE";
				$strSQL.="?,";$binds[]="0";
				if(isset($areaofinterest)){$strSQL.="?";$binds[]=$areaofinterest;}else{$strSQL.="";}
				$strSQL.=")";
				$rsPrimary = $db1->prepare($strSQL);
				$rsPrimary->execute($binds);
				###
				$mailcust=mail($send_to, $subject, $body, $headers);
				$mailadmin=mail($send_togmk, $subjectgmk, $bodygmk, $headersgmk);
				if($mailcust && $mailadmin)
				{
					?>
					<div class='success'>Thank you, you have sucessfully registered your warranty.<br />You should receive your confirmation by email. (<?php print $email; ?>)</div>
					<?php
				}
				else
				{
					?>
					<div class='failedsubmit'>Sorry, there was an error while trying to send your registration. Please call 01489 579 999 for assistance.</div>
					<?php
				}
			}
			else
			{
				?>
				<div class='failedsubmit'>Sorry, there was an error while trying to send your registration from your location. Please call 01489 579 999 for assistance.</div>
				<?php
			}
		}
		else
		{
			//$varprices=ysql_query("SELECT `price`,`vname` FROM cart_variants WHERE `vid`='".mysql_real_escape_string($_POST['skuvariant'][358])."'");
			//list($paprice,$post_title)=mysql_fetch_row($varprices);
			$varprices=$db1->prepare("SELECT `price`,`vname` FROM cart_variants WHERE `vid`=?");
			$varprices->execute(array($_POST['skuvariant'][358]));
			list($paprice,$post_title)=$varprices->fetch();
			/*
			pid = 358
			serial - goptid
			product - prod::shop
			purchasedate
			fromshop
			Z52543S
			*/
			$_SESSION['cart'][]=array(
			"prod_id"=>'358',
			"skuvariant"=>array('358'=>"WARRANTY10-v-NONE-qty-1"),
			"qty"=>1,
			"price"=>$paprice,
			"ispack"=>0,
			"exclude_discount"=>1,
			"allowlist"=>array(),
			"title"=>$post_title." Warranty",
			'mailinglist'=>$_POST['mailinglist'],
			'serial'=>$_POST['serial'],
			'product'=>$_POST['product'],
			'brand'=>$_POST['brand'],
			'purchasedate'=>date('j-M-Y',mktime(0,0,0,$_POST['wr_month'],$_POST['wr_days'],$_POST['wr_year'])),
			'fromshop'=>trim($_POST['fromshop']),			
			'areaofinterest'=>$_POST['areaofinterest'],
			'freeship'=>1
			);
			$_SESSION['added']=array(count($_SESSION['cart'])-1,'new');
			if(!isset($_SESSION['address_details']))
			{
				$_SESSION['address_details']=array();
				$_SESSION['address_details']['delivery']=array(
				'nametitle'=>$_POST['nametitle'],
				'firstname'=>$_POST['firstname'],
				'lastname'=>$_POST['lastname'],
				'address1'=>$_POST['address1'],
				'address2'=>$_POST['address2'],
				'city'=>$_POST['city'],
				'postcode'=>$_POST['postcode'],
				'country'=>'GB',
				'county'=>$_POST['county'],
				'phone'=>$_POST['telephone'],
				'email'=>$_POST['email']
				);
			}
			cart_redirection('./cart_basket');			
			?>Please proceed to the <a href="./cart_basket">shopping cart</a>.<?php
		}
	}
}
else if(isset($_POST['warrantysubmit']) && $submitagain == 0)
{
	?><div class='failedsubmit'>You have re submitted this form too soon, please wait another <?=($_SESSION['submitagain'] - date("U"))?> seconds then click your browser's refresh button to try again.</div><?php
}
else
{
	$submitok = 0;
	header("Location: ".$httppath."index.php");
}
?>