<?php
$image=isset($_GET['image'])?$_GET['image'].".jpg":"1.jpg";
?>
<img src="<?=$image?>" alt="" />
<?php
	$random_hash = md5(date('r', time())); 
	ob_end_flush();
	ob_start(); //Turn on output buffering
	?>
--PHP-alt-<?php echo $random_hash; ?>

Content-Type: text/plain; charset = "iso-8859-1"
Content-Transfer-Encoding: 7bit

Invoice Number <?=$row['invoice']?> - Order Date <?=date("d/m/Y",$row['date_ordered'])?>
============================================================

Order Status: <?=$row['order_status']?>
Order Comments: <?=((strlen($row['comments'])>0)?$row['comments']:"None")?>
Payment Method: Credit/Debit Card
Postage Method: <?=$row['ship_description']?>


Billing Address
----------------------------
<?=$row['firstname']?> <?=$row['lastname']?>
<?=$row['address1']?>
<?=((strlen($row['address2'])>0)?$row['address2']."":"")?>
<?=$row['city']?>
<?=$row['postcode']?>
<?=$row['email']?>
<?=$row['phone']?>


Delivery Address
----------------------------
<?php if($row['sameasbilling']==1){?>
Same as billing address
<?php }else{?>
<?=$row['alt_name']?>
<?=$row['alt_address1']?>
<?=((strlen($row['alt_address2'])>0)?$row['alt_address2']."":"")?>
<?=$row['alt_city']?>
<?=$row['alt_postcode']?>
<?=$row['alt_phone']?>
<?php }?>


<?php /* order contents */?>
<?php 
$runtotal=0;
$removefromdiscount=0;
$discount=0;
$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount` FROM cart_orders as o,cart_orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`itemId` WHERE o.`VendorTxCode`='" . $strVendorTxCode . "' AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";

$orderq=ysql_query($sstring,CARTDB);
while($order=mysql_fetch_assoc($orderq))
{
$iQuantity=$order['qty'];
$iProductId=$order['prod_id'];
$orderkitq=ysql_query("SELECT `fusionId`,`kit_title`,`item_qty`,`oname`,`oitem,prod_id` FROM cart_orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`itemId` AND `itemType`='product' WHERE `order_prod_id`='$order[order_prod_id]' GROUP BY `okit_id`",CARTDB);
$ispack=mysql_num_rows($orderkitq);
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
while($orderkit=mysql_fetch_assoc($orderkitq))
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
		<p class="note">Credit/Debit Card</p>
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
		<?=$row['firstname']?> <?=$row['lastname']?><br />
		<?=$row['address1']?><br />
		<?=((strlen($row['address2'])>0)?$row['address2']."<br />":"")?>
		<?=$row['city']?><br />
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
		<?=$row['alt_name']?><br />
		<?=$row['alt_address1']?><br />
		<?=((strlen($row['alt_address2'])>0)?$row['alt_address2']."<br />":"")?>
		<?=$row['alt_city']?><br />
		<?=$row['alt_postcode']?><br />
		<?=$row['alt_phone']?>
		<?php }?>
		</address>
	</td>
</tr>
</table><p>&#160;</p>
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
	
	
	//$mail_sent_llc = @mail( "senfield@gmk.co.uk", $sitename." Invoice ".$row['invoice'].$sat, $message, $headers );
?>