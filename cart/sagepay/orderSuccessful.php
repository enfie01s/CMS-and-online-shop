<?php
include("sagepay/includes.php");
session_name("gmk");
session_start(); 

/**************************************************************************************************
* Sage Pay Direct PHP Kit Order Successful Page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============

* 02/04/2009 - Simon Wolfe - Updated UI for re-brand
* 18/12/2007 - Nick Selby - New PHP version adapted from ASP
****************************************************************************************************
* Description
* ===========

* This is a placeholder for your Successful Order Completion Page.  It retrieves the VendorTxCode
* from the crypt string and displays the transaction results on the screen.  You wouldn't display 
* all the information in a live application, but during development this page shows everything
* sent back in the confirmation screen.
****************************************************************************************************/

// Check for the proceed button click, and if so, go to the buildOrder page


//Now check we have a VendorTxCode passed to this page
$strVendorTxCode=$_SESSION["VendorTxCode"];/*should this be unset at the end of the doc to prevent duplication? if so, we need to check if invoice found so no file is generated with weirdness*/
if (strlen($strVendorTxCode)==0) { 
	//No VendorTxCode, so take the customer to the home page
	ob_end_flush();
	session_destroy();
	redirect(MAINBASE."/index.php");
	exit();
}

//Empty the cart, we're done with it now because the order is successful
if(isset($_SESSION['cart'])){unset($_SESSION['cart']);}
if(isset($_SESSION['address_details'])){unset($_SESSION['address_details']);}
if(isset($_SESSION['shipping'])){unset($_SESSION['shipping']);}
if(isset($_SESSION['terms_agree'])){unset($_SESSION['terms_agree']);}
if(isset($_SESSION['discount_code'])){unset($_SESSION['discount_code']);}
if(isset($_SESSION['discount_amount'])){unset($_SESSION['discount_amount']);}
if(isset($_SESSION['checkoutnew'])){unset($_SESSION['checkoutnew']);}

/*$strSQL="SELECT * FROM cart_orders WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
$rsPrimary = ysql_query($strSQL,CARTDB)or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
$row=mysql_fetch_assoc($rsPrimary);*/
$strSQL="SELECT * FROM cart_orders WHERE `VendorTxCode`=?";
$rsPrimary = $db1->prepare($strSQL);
$rsPrimary->execute(array($strVendorTxCode));
$row=$rsPrimary->fetch(PDO::FETCH_ASSOC);
$strSQL="";

/* GENERATE DATA FOR NAV */
$prexix="G";
$filename=$root_to_cart.'orders/' .$prexix. $row['invoice'] . '.txt';
if(!file_exists($filename)&&$strConnectTo=="LIVE")/*prevent data duplication*/
{
	$fp = fopen($filename, 'w') or die("can't open file");
	$cref = $row['cust_id'];
	if($cref<1)
	{
		$pref = $prexix . $row['invoice'];
	}
	else
	{
		$pref = $prexix . $row['invoice'] . "-" . $cref;
	}
	
	$sales_head	= "HED" . ",";/* label*/
	$sales_head.= $pref . ",";/*customer order num*/
	$sales_head.= "ZZWEB" . ",";/*navision customer no*/
	$sales_head.= ",";/*delivery cust code*/
	$sales_head.= substr(str_replace(","," ",$row['alt_name']),0,40).",";/*cust name*/
	$sales_head.= substr(str_replace(","," ",$row['alt_address1']),0,35).",";/*cust addy 1*/
	$sales_head.= (strlen($row['alt_address2'])>0?substr(str_replace(","," ",$row['alt_address2']),0,35):'').",";/*cust addy 2*/
	$sales_head.=	substr(str_replace(","," ",$row['alt_city']),0,35).",";/*cust addy 3*/
	$sales_head.= substr(str_replace(","," ",cart_get_county($row['alt_state'])),0,35).",";/*cust addy 4*/
	$sales_head.= substr(str_replace(","," ",$row['alt_postcode']),0,10).",";/*cust post code*/
	$sales_head.= date("d-m-Y") . ",";/*order date*/
	$sales_head.= date("d-m-Y") . ",,,";/*required date required time,booking ref*/
	$sales_head.= substr(str_replace(","," - ",$row['comments']),0,70) .",";/*comments (del instruct 1)*/
	$sales_head.= ",,,,";/*del instruct 2-4,contact name*/
	$sales_head.= substr(str_replace(","," ",$row['alt_phone']),0,20).",";/*contact tel*/
	$sales_head.= ",";/*contact country*/
	$sales_head.= substr(str_replace(","," ",$row['alt_email']),0,200);	/*contact email*/
	
	fwrite($fp, $sales_head);
	$i = 1;
	/*$order_prodsq = ysql_query("SELECT * FROM cart_orderproducts WHERE `order_id`='".$row['order_id']."'",CARTDB) or die(sql_error("Error"));
	while ($order_prods = mysql_fetch_array($order_prodsq))*/
	$order_prodsq = $db1->prepare("SELECT * FROM cart_orderproducts WHERE `order_id`=?");
	$order_prodsq->execute(array($row['order_id']));
	while ($order_prods = $order_prodsq->fetch())
	{
		if($order_prods['ispack']==0)
		{
			$sales_line	= PHP_EOL."LNE" . ",";/*label*/
			$sales_line.= $prexix.$row['invoice'] . ",";/*customer order num*/
			$sales_line.= $i . ",";/*line id*/
			$sales_line.= $order_prods['sku'] . ",";/*nav product code*/
			$sales_line.= substr(str_replace(","," ",$order_prods['title']),0,40) . ",";/*product description*/
			$sales_line.= $order_prods['qty'] . ",";/*line qty*/
			$sales_line.= ",";/*pack size*/
			$sales_line.= cart_addvat($order_prods['price'],1).",";/*price + vat*/
			$sales_line.= ",,";/*cust line no,order cust*/
			$sales_line.= ",";/*cust name*/
			$sales_line.= ",";/*cust addy 1*/
			$sales_line.= ",";/*cust addy 2*/
			$sales_line.=	",";/*cust addy 3*/
			$sales_line.= ",";/*cust addy 4*/
			$sales_line.= ",";/*cust post code*/
			$sales_line.= ",,";/*req'd date,req'd time*/
			$sales_line.= ($order_prods['variant_id']!="NONE"?$order_prods['variant_id']:"");/*variant code*/
			fwrite($fp, $sales_line);
			$i++;
			//ysql_query("UPDATE nav_stock SET `nav_qty`=`nav_qty`-".$order_prods['qty']." WHERE `nav_skuvar`='".$order_prods['sku']."-v-".$order_prods['variant_id']."'",CARTDB);//not pack
			$q=$db1->prepare("UPDATE nav_stock SET `nav_qty`=`nav_qty`-? WHERE `nav_skuvar`=?");//not pack
			$q->execute(array($order_prods['qty'],$order_prods['sku']."-v-".$order_prods['variant_id']));
		}
		else
		{
			/*$order_kitsq=ysql_query("SELECT * FROM cart_orderkits WHERE `order_prod_id`='".$order_prods['order_prod_id']."'",CARTDB);
			while($order_kits=mysql_fetch_assoc($order_kitsq))*/
			$order_kitsq=$db1->prepare("SELECT * FROM cart_orderkits WHERE `order_prod_id`=?");
			$order_kitsq->execute(array($order_prods['order_prod_id']));
			while($order_kits=$order_kitsq->fetch(PDO::FETCH_ASSOC))
			{
				$okskuvar=explode("-v-",$order_kits['okit_skuvar']);
				$sales_line	= PHP_EOL."LNE" . ",";/*label*/
				$sales_line.= $prexix.$row['invoice'] . ",";/*customer order num*/
				$sales_line.= $i . ",";/*line id*/
				$sales_line.= $okskuvar[0] . ",";/*nav product code*/
				$sales_line.= substr(str_replace(","," ",$order_kits['kit_title']),0,40) . ",";/*product description*/
				$sales_line.= $order_kits['item_qty']*$order_prods['qty'] . ",";/*line qty*/
				$sales_line.= ",";/*pack size*/
				$sales_line.= cart_addvat($order_prods['price'],1).",";/*price + vat*/
				$sales_line.= ",,";/*cust line no,order cust*/
				$sales_line.= ",";/*cust name*/
				$sales_line.= ",";/*cust addy 1*/
				$sales_line.= ",";/*cust addy 2*/
				$sales_line.=	",";/*cust addy 3*/
				$sales_line.= ",";/*cust addy 4*/
				$sales_line.= ",";/*cust post code*/
				$sales_line.= ",,";/*req'd date,req'd time*/
				$sales_line.= $okskuvar[1];/*variant code*/
				fwrite($fp, $sales_line);
				$i++;
				//ysql_query("UPDATE nav_stock SET `nav_qty`=`nav_qty`-".($order_kits['item_qty']*$order_prods['qty'])." WHERE `nav_skuvar`='".$order_kits['okit_skuvar']."'",CARTDB);//pack
				$q=$db1->prepare("UPDATE nav_stock SET `nav_qty`=`nav_qty`-? WHERE `nav_skuvar`=?");//pack
				$q->execute(array(($order_kits['item_qty']*$order_prods['qty']),$order_kits['okit_skuvar']));
			}
		}
	}
	fclose($fp);
	if(is_file($filename))//if file has been created
	{
		//ysql_query("UPDATE cart_orders SET `xmlmade`=NOW() WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'",CARTDB);
		$q=$db1->prepare("UPDATE cart_orders SET `xmlmade`=NOW() WHERE `VendorTxCode`=?");
		$q->execute(array($strVendorTxCode));
	}
	/* GENERATE DATA FOR NAV */
}
	if($strConnectTo=="TEST"){$_SESSION['sentemail']=123;}
if(!isset($_SESSION['sentemail'])||$_SESSION['sentemail']!=$row['invoice'])
{	
	/* SEND EMAILS OUT*/
	$_SESSION['sentemail']=$row['invoice'];
	$random_hash = md5(date('r', time())); 
	
	ob_start(); //Turn on output buffering
	?>
--PHP-alt-<?php echo $random_hash; ?>

Content-Type: text/plain; charset = "iso-8859-1"
Content-Transfer-Encoding: 7bit

Invoice Number <?=$row['invoice']?> - Order Date <?=date("d/m/Y",$row['date_ordered'])?>
============================================================

Order Status: <?=$row['order_status']?>
Order Comments: <?=((strlen($row['comments'])>0)?$row['comments']:"None")?>
Payment Method: <?=stristr($row['Status'],"free")===false?"Credit/Debit Card":"Free"?>
Postage Method: <?=$row['ship_description']?>


Billing Address
----------------------------
<?=$row['nametitle']." ".$row['firstname']." ".$row['lastname']?>
<?=$row['address1']?>
<?=((strlen($row['address2'])>0)?$row['address2']."":"")?>
<?=$row['city']?>
<?=cart_get_county($row['state'])?>
<?=cart_get_country($row['country'])?>
<?=$row['postcode']?>
<?=$row['email']?>
<?=$row['phone']?>


Delivery Address
----------------------------
<?php if($row['sameasbilling']==1){?>
Same as billing address
<?php }else{?>
<?=$row['alt_nametitle']." ".$row['alt_name']?>
<?=$row['alt_address1']?>
<?=((strlen($row['alt_address2'])>0)?$row['alt_address2']."":"")?>
<?=$row['alt_city']?>
<?=cart_get_county($row['alt_state'])?>
<?=cart_get_country($row['alt_country'])?>
<?=$row['alt_postcode']?>
<?=$row['alt_phone']?>
<?php }?>


<?php /* order contents */?>
<?php 
$runtotal=0;
$removefromdiscount=0;
$discount=0;
$warrantyOrder=0;
/*$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount` FROM cart_orders as o,cart_orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`itemId` WHERE o.`VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "' AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";

$orderq=ysql_query($sstring,CARTDB);
while($order=mysql_fetch_assoc($orderq))*/
$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount` FROM cart_orders as o,cart_orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`itemId` WHERE o.`VendorTxCode`=? AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";

$orderq=$db1->prepare($sstring);
$orderq->execute(array($strVendorTxCode));
while($order=$orderq->fetch(PDO::FETCH_ASSOC))
{
$iQuantity=$order['qty'];
$iProductId=$order['prod_id'];
if($iProductId=='358'){$warrantyOrder=1;}
/*$orderkitq=ysql_query("SELECT `fusionId`,`kit_title`,`item_qty`,`oname`,`oitem`,`prod_id` FROM cart_orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`itemId` AND `itemType`='product' WHERE `order_prod_id`='$order[order_prod_id]' GROUP BY `okit_id`",CARTDB);
$ispack=mysql_num_rows($orderkitq);*/

$orderkitq=$db1->prepare("SELECT `fusionId`,`kit_title`,`item_qty`,`oname`,`oitem`,`prod_id` FROM cart_orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`itemId` AND `itemType`='product' WHERE `order_prod_id`=? GROUP BY `okit_id`");
$orderkitq->execute(array($order['order_prod_id']));
$ispack=$orderkitq->rowCount();
?>

Product: <?=$order['title']." (".$order['sku'].")"?>
Quantity: <?=$order['qty']?><?php if($order['exclude_discount']==1){?>(Discount exempt)<?php }?>
<?php 
if($ispack==0)
{
echo ucwords($order['oname']).": ".$order['oitem'];
}
else
{?>
Pack Contents:<?php 
//while($orderkit=mysql_fetch_assoc($orderkitq))
while($orderkit=$orderkitq->fetch(PDO::FETCH_ASSOC))
{
?><?=$orderkit['kit_title']?> (<?=$orderkit['item_qty']?>)<?php
echo ucwords($orderkit['oname']).": ".$orderkit['oitem'];
}
}
$vatbits=cart_getvat($order['price']);
?>
Price(ex. VAT): £<?=$vatbits[0]?>
Sub Total: £<?=number_format($order['price']*$order['qty'],2)?>

<?php
$itemprice=$order['price']*$order['qty'];
$runtotal+=$itemprice;
$odiscount=$order['odiscount'];//discount percentage
$odiscountcode=$order['discount_code'];
$oshipdesc=$order['ship_description'];
$oshiptotal=$order['ship_total'];
$ototalprice=$order['total_price'];
$otaxrate=$order['tax_rate'];
$otaxprice=$order['tax_price'];
$discount+=$order['opdiscount']*$order['qty'];
echo "-----------------------------------------------

";
}
?>
=================
Sub Total: £<?=number_format($runtotal,2)?>
<?php if(strlen($odiscountcode)>0&&$odiscountcode!="discount code"){?>
<?=$odiscount?>% Discount (<?=$odiscountcode?>): - £<?=number_format($discount,2)?>
<?php }?>
VAT @<?=$otaxrate?>%: £<?=number_format($otaxprice,2)?>
Postage (<?=$oshipdesc?>): £<?=number_format($oshiptotal,2)?>
Total: £<?=number_format($ototalprice,2)?>
<?php /*order contents */?>

<?=str_replace("<br />","
",$postaladdy)?>


VAT. Registration No: <?=$vatreg?>
Company Registration No.: <?=$coreg?>

Thank you for your order, we appreciate your custom.
If you could spare a few moments, we would be very grateful if you could add review(s) of the products you have purchased.

If for any reason you are unhappy with your purchase, please contact us at <?=$admin_email?>.
	
--PHP-alt-<?php echo $random_hash; ?>

Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Mail</title>
<style>
body{font-size:10pt;color:#555555;font-family:Arial,Helvetica,sans-serif;}
address{font-style:normal;font-size:11pt;}
table{border-collapse:collapse;}
td{border:1px solid #FFF;}
table .price{
	color:#13689D;
	font-size:10pt;
	font-weight:bold;
}
table.details{background:#ffffff;border:0;}
table.details tr td{
	background:#eeeeee;
	padding:3px;
	color:#555555;
	font:10pt Arial,Helvetica,sans-serif;
	mso-margin-top-alt:2px;
	margin-right:0cm;
	mso-margin-bottom-alt:2px;
	margin-left:0cm;
	border:1px solid #999;
}
table.details tr.head td{
	background:#DDDDDD;
	font-weight:bold;
	mso-margin-top-alt:0cm;
	margin-right:0cm;
	mso-margin-bottom-alt:0cm;
	margin-left:0cm;
}
table.details tr.subhead td{
	background:#DDDDDD;
	font-weight:bold;
	mso-margin-top-alt:0cm;
	margin-right:0cm;
	mso-margin-bottom-alt:0cm;
	margin-left:0cm;
}
.note{font-size:10pt;}
h3{
	clear:both;
	color:#999999;
	font-weight:normal;
	padding:0;
	margin:1em 0;
	font-size:13pt;
}
h2{
	color:#010C39;
	font-size:15pt;
	font-weight:normal;
	height:19px;
	line-height:15pt;
	margin:1em 0;
	padding:0 0 0 20px;
}
a{text-decoration:none;color:#309A95;}
a:hover{color:#555555;}
.pack_contents{
	border:1px solid #cccccc;
	background:#f9f9f9;
	padding:0px 3px;
	mso-margin-top-alt:0cm;
	margin-right:0cm;
	mso-margin-bottom-alt:0cm;
	margin-left:0cm;
}
</style>
</head>
<body>
<h2>Invoice Number <?=$row['invoice']?> - Order Date <?=date("d/m/Y",$row['date_ordered'])?></h2>
<table style="width:720px;border:10px solid #FFF;">
<tr>
	<td width="50%" style="vertical-align:top;">
		<h3>Order Status</h3>
		<p class="note"><?=$row['order_status']?></p>
	</td>
	<td width="50%" style="vertical-align:top;">
		<h3>Order Comments</h3>
		<p class="note">
			<?=((strlen($row['comments'])>0)?$row['comments']:"None")?>
		</p>
	</td>
</tr>
<tr>
	<td width="50%" style="vertical-align:top;">
		<h3>Payment Method</h3>
		<p class="note"><?=stristr($row['Status'],"free")===false?"Credit/Debit Card":"Free"?></p>
	</td>
	<td width="50%" style="vertical-align:top;">
		<h3>Postage Method</h3>
		<p class="note"<?php if($row['ship_description']=="Saturday Delivery"){?> style='font-weight:bold;'<?php }?>>
			<?=$row['ship_description']?>
		</p>
	</td>
</tr>
<tr>
	<td width="50%" style="vertical-align:top;">
		<h3>Billing Address</h3>
		<address>
		<?=$row['nametitle']." ".$row['firstname']?> <?=$row['lastname']?><br />
		<?=$row['address1']?><br />
		<?=((strlen($row['address2'])>0)?$row['address2']."<br />":"")?>
		<?=$row['city']?><br />
		<?=cart_get_county($row['state'])?><br />
		<?=cart_get_country($row['country'])?><br />
		<?=$row['postcode']?><br />
		<?=$row['email']?><br />
		<?=$row['phone']?>
		</address>
	</td>
	<td width="50%" style="vertical-align:top;">
		<h3>Delivery Address</h3>
		<address>
		<?php if($row['sameasbilling']==1){?>
		Same as billing address
		<?php }else{?>
		<?=$row['alt_nametitle']." ".$row['alt_name']?><br />
		<?=$row['alt_address1']?><br />
		<?=((strlen($row['alt_address2'])>0)?$row['alt_address2']."<br />":"")?>
		<?=$row['alt_city']?><br />
		<?=cart_get_county($row['alt_state'])?><br />
		<?=cart_get_country($row['alt_country'])?><br />
		<?=$row['alt_postcode']?><br />
		<?=$row['alt_phone']?>
		<?php }?>
		</address>
	</td>
</tr>
</table><p>&#160;</p>
<?php /*cart_ordercontents("o.VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'","720");*/
cart_ordercontents("o.VendorTxCode=?","720",array($strVendorTxCode));?>
<p style="font-style:italic;">
<?=$postaladdy?><br />
</p>
<p>
VAT. Registration No: <?=$vatreg?><br />
Company Registration No.: <?=$coreg?>
</p>
<p>
Thank you for your order, we appreciate your custom.<br />
If you could spare a few moments, we would be very grateful if you could add review(s) of the products you have purchased.<br /><br />
If for any reason you are unhappy with your purchase, please contact us at <?=$admin_email?>.
</p>
</body></html>

--PHP-alt-<?php echo $random_hash; ?>--
	<?php
	//copy current buffer contents into $message variable and delete current output buffer
	$message = ob_get_clean();
	
	$headers = "From: ".$sitename." <".$sales_email.">\r\nReply-To: ".$sales_email;
	$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
	$sat=$row['ship_description']=="Saturday Delivery"?" - Saturday Delivery":"";
	
	$mail_sent_llc = @mail( $cart_order_email, $sitename." Invoice ".$row['invoice'].$sat, $message, $headers );
	//$mail_sent_test = @mail( "senfield@gmk.co.uk", $sitename." Invoice ".$row['invoice'].$sat, $message, $headers );
	$mail_sent_cust = @mail( $row['email'], "Thank You for your order at ".$sitename, $message, $headers );
	if($warrantyOrder==1){@mail( $warranty_email, "Warranty: ".$sitename." Invoice ".$row['invoice'], $message, $headers );}

	/* /SEND EMAILS OUT*/
}
?>
<p>Your order has been placed successfully.</p>
<p><a href="<?=SECUREBASE?>/cart_receipt">View your receipt</a></p>
<?		
if ($strConnectTo=="TEST")
{ 
	?>
	<script type="text/javascript" language="javascript" src="<?=$root_to_cart?>sagepay/scripts/common.js" ></script>
	<script type="text/javascript" language="javascript" src="<?=$root_to_cart?>sagepay/scripts/countrycodes.js" ></script>
	<p>*** TEST INFORMATION BELOW - NOT SHOWN ON LIVE SITE ***</p><?php
	echo $mail_sent_llc ? "LLC Mail sent<br />" : "LLC Mail failed<br />";
	echo $mail_sent_cust ? "Customer Mail sent<br />" : "Customer Mail failed<br />";	
	?>
	<div id="contentHeader">Your order has been Successful</div>
	<p>The Sage Pay Direct transaction has completed successfully and the customer has been returned to this order completion page<br>
		<br>
		The order number, for your customer's reference is: <span class="arrowbullets"><strong><?php echo $strVendorTxCode ?></strong></span> <br>
		<br>
		They should quote this in all correspondence with you, and likewise you should use this reference when sending queries to Sage Pay about this transaction (along with your Sage Pay Vendor Name).<br>
		<br>
		The table below shows everything in the database about this order.  You would not normally show this level of detail to your customers, but it is useful during development.<br>
		<br>
		You can customise this page to send confirmation e-mails, display delivery times, present download pages, whatever is appropriate for your application.  The code is in orderSuccessful.php.
	</p>
												
	<?php /*cart_ordercontents("o.VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'","100%");*/
	cart_ordercontents("o.VendorTxCode=?","100%",array($strVendorTxCode));?>
	<br />
	<table class="details">
		<tr class="head">
			<td colspan="2"><div class="titles">Order Details stored in your Database</div></td>
		</tr>
		<tr>
			<td class="left_light">VendorTxCode:</td>
			<td class="right_light"><?=$strVendorTxCode?></td>
		</tr>
		<tr>
			<td class="left_dark">Transaction Type:</td>
			<td class="right_dark"><?=$row["TxType"]?></td>
		</tr>
		<tr>
			<td class="left_light">Status:</td>
			<td class="right_light"><?=$row["Status"]?></td>
		</tr>
		<tr>
			<td class="left_dark">Amount:</td>
			<td class="right_dark"><?=number_format($row["total_price"],2) . " " . $strCurrency; ?></td>
		</tr>
		<tr>
			<td class="left_light">Billing Name:</td>
			<td class="right_light"><?=$row["firstname"] . " " . $row["lastname"]; ?></td>
		</tr>
		<tr>
			<td class="left_dark">Billing Phone:</td>
			<td class="right_dark"><?=$row["phone"]; ?>&#160;</td>
		</tr>
		<tr>
			<td class="left_light" style="vertical-align:top">Billing Address:</td>
			<td class="right_light"><?=$row["address1"] ?><br />
				<?php if (isset($row["address2"])&&$row["address2"]!=null){ echo $row["address2"]. "<br />";} ?>
				<?=$row["city"] ?>&#160;
				<?php if (isset($row["state"])) echo "<br />".cart_get_county($row["state"]); ?>
				<br />
				<?=$row["postcode"]; ?><br />
				<script type="text/javascript" language="javascript">
											document.write( getCountryName( "<?php echo $row["country"]; ?>" ));
									</script>
			</td>
		</tr>
		<tr>
			<td class="left_dark">Billing e-Mail:</td>
			<td class="right_dark"><?=$row["email"] ?>&#160;</td>
		</tr>
		<tr>
			<td class="left_light">Delivery Name:</td>
			<td class="right_light"><?=$row["alt_name"] ?></td>
		</tr>
		<tr>
			<td class="left_dark" style="vertical-align:top">Delivery Address:</td>
			<td class="right_dark"><?=$row["alt_address1"]; ?><br />
				<?php if (isset($row["alt_address2"])&&$row["alt_address2"]!=null) {echo $row["alt_address2"] . "<br />"; }?>
				<?=$row["alt_city"]; ?>&#160;
				<?php if (isset($row["alt_state"])) echo "<br />".cart_get_county($row["alt_state"]); ?>
				<br />
				<?php echo $row["alt_postcode"]; ?><br />
				<script type="text/javascript" language="javascript">
											document.write( getCountryName( "<?php echo $row["DeliveryCountry"]; ?>" ));
									</script>
			</td>
		</tr>
		<tr>
			<td class="left_light">Delivery Phone:</td>
			<td class="right_light"><?=$row["alt_phone"]; ?>&#160;</td>
		</tr>

		<tr>
			<td class="left_dark">VPSTxId:</td>
			<td class="right_dark"><?=$row["VPSTxId"]?>&#160;</td>
		</tr>
		<tr>
			<td class="left_light">SecurityKey:</td>
			<td class="right_light"><?=$row["SecurityKey"]?>&#160;</td>
		</tr>
		<tr>
			<td class="left_dark">VPSAuthCode (TxAuthNo):</td>
			<td class="right_dark"><?=$row["TxAuthNo"]?>&#160;</td>
		</tr>
		<tr>
			<td class="left_light">AVSCV2 Results:</td>
			<td class="right_light"><?=$row["AVSCV2"]?><span class=\"smalltext\"> - Address:<?=$row["AddressResult"]?> 
			, Post Code:<?=$row["PostCodeResult"]?>, CV2:<?=$row["CV2Result"]?></span></td>
		</tr>
		<tr>
			<td class="left_dark">Gift Aid Transaction?:</td>
			<td class="right_dark">
			<?php if ($row["GiftAid"]==1) { echo "Yes"; } else { echo "No"; } ?>
			
			</td>
		</tr>
		<tr>
			<td class="left_light">3D-Secure Status:</td>
			<td class="right_light"><?=$row["ThreeDSecureStatus"]?>&#160;</td>
		</tr>
		<tr>
			<td class="left_dark">CAVV:</td>
			<td class="right_dark"><?=$row["CAVV"]?>&#160;</td>
		</tr>
		<tr>
			<td class="left_light">Card Type:</td>
			<td class="right_light"><?=$row["CardType"]?>&#160;</td>
		</tr>
			<tr>
				<td class="left_dark">Address Status:</td>
				<td class="right_dark"><span style=\"float:right; font-size: smaller;\">&#160;*PayPal transactions only</span><?=$row["AddressStatus"]?></td>
			</tr>
			<tr>
				<td class="left_light">Payer Status:</td>
				<td class="right_light"><span style=\"float:right; font-size: smaller;\">&#160;*PayPal transactions only</span><?=$row["PayerStatus"]?></td>
			</tr>
			<tr>
				<td class="left_dark">PayerID:</td>
				<td class="right_dark"><span style=\"float:right; font-size: smaller;\">&#160;*PayPal transactions only</span><?=$row["PayPalPayerID"]?></td>
			</tr>
	</table>
	<?php 
}
?>
<?php 
$_SESSION['invoice']=$row['invoice'];
$_SESSION['date_ordered']=date("d/m/Y",$row['date_ordered']);
?>