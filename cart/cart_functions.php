<?php 
include	"cart_vars.php";
$_SESSION['postdesc']=isset($_SESSION['postdesc'])&&strlen($_SESSION['postdesc'])>0?(cart_postapplicable()?$_SESSION['postdesc']:$freepostedsc):($freepost==1||cart_postapplicable()==0?$freepostedsc:"P&P");
$_SESSION['shipping']=isset($_SESSION['shipping'])&&strlen($_SESSION['shipping'])>0?(cart_postapplicable()?$_SESSION['shipping']:$freepostid):($freepost==1||cart_postapplicable()==0?$freepostid:5);
$_SESSION['postdesc']=!isset($_SESSION['postdesc'])?(cart_postapplicable()?$_SESSION['postdesc']:$freepostedsc):$_SESSION['postdesc'];
$_SESSION['shipping']=!isset($_SESSION['shipping'])?(cart_postapplicable()?$_SESSION['shipping']:$freepostid):$_SESSION['shipping'];

if($cart_debugmode==1)
{
	error_reporting(E_ALL);
	ini_set("display_errors", 1); 
}
/* HEADER ACTIONS */
if(isset($post_arr['identifier'])&&$post_arr['identifier']=="update_cart"&&$post_arr['mode']=="Start Checkout")
{
	header("Location: $post_arr[checkout]");
}

if(isset($get_arr['logout'])){unset($_SESSION['pass']);unset($_SESSION['email']);$_SESSION['loggedin']=0;}

$_SESSION['checkoutnew']=isset($get_arr['checkoutnew'])&&$get_arr['checkoutnew']==1?1:(!isset($_SESSION['checkoutnew'])?0:$_SESSION['checkoutnew']);
$regoncheckout=isset($post_arr['identifier'])&&$post_arr['identifier']=="checkout_customer"&&$_SESSION['checkoutnew']==1&&strlen($post_arr['password1'])>0?1:0;

if(isset($post_arr['identifier']))
{
	switch($post_arr['identifier'])
	{
		case "checkout_customer"://$regoncheckout==0 - if 1, we've already error checked above.
			if(!isset($post_arr['matchbilling'])||$post_arr['matchbilling'][0]!=1)
			{
				$requireds['checkout_customer'][]="deliver_nametitle";
				$requireds['checkout_customer'][]="deliver_firstname";
				$requireds['checkout_customer'][]="deliver_lastname";
				$requireds['checkout_customer'][]="deliver_address1";
				$requireds['checkout_customer'][]="deliver_city";
				$requireds['checkout_customer'][]="deliver_state";
				$requireds['checkout_customer'][]="deliver_postcode";
				$requireds['checkout_customer'][]="deliver_country";
				$requireds['checkout_customer'][]="deliver_phone";
			}
			$errorlist=array();
			
			//check for empty fields which are required
			foreach($post_arr as $field => $value)
			{
				if(in_array($field,$requireds[$post_arr['identifier']])&&$value==""){$errorlist[$field]=$fieldtitles[$field]." is empty.";}
			}
			if(isset($post_arr['email'])&&$post_arr['email']!=""&&!eregi(EMAILREG, $post_arr['email'])){$errorlist["email"]="Please enter a valid email address (eg: user@host.com).";}
			if($regoncheckout==0)
			{
				if($post_arr['terms_agree']!=1){$errorlist['terms']="You must agree to the terms &amp; conditions to continue";}
			}
			else
			{
				/*$emailcheck_q=ysql_query("SELECT `email` FROM cart_customers WHERE `email`='$post_arr[email]'")or die(sql_error("Error"));
				$emailcheck=mysql_num_rows($emailcheck_q);*/
				$emailcheck_q=$db1->prepare("SELECT `email` FROM cart_customers WHERE `email`=?");
				$emailcheck_q->execute(array($post_arr['email']));
				$emailcheck=$emailcheck_q->rowCount();
				if($emailcheck>0){$errorlist["email"]="The email address you entered is already registered on another customer account.";}
				//pass matching test
				if($post_arr['password1']!=$post_arr['password2']){$errorlist["password2"]="Passwords don't match";}
			}
			if(count($errorlist)>0)//errors found
			{
				foreach($errorlist as $error=>$desc)
				{
					$errormsg.= $desc."<br />";
				}
				$errorboxdisplay="display:block;";
			}
			else
			{
				$_SESSION['terms_agree']=$post_arr['terms_agree'];
			
			/* SET UP ADDRESS SESSION */
				$_SESSION['address_details']=array();
				$_SESSION['address_details']['billing']=array("nametitle"=>$post_arr['nametitle'],"firstname"=>$post_arr['firstname'],"lastname"=>$post_arr['lastname'],"address1"=>$post_arr['address1'],"address2"=>$post_arr['address2'],"city"=>$post_arr['city'],"county"=>$post_arr['state'],"postcode"=>$post_arr['postcode'],"country"=>$post_arr['country'],"phone"=>$post_arr['phone'],"email"=>$post_arr['email'],"website"=>$post_arr['homepage'],"company"=>$post_arr['company']);
				if($post_arr['matchbilling'][0]==1)
				{
					$_SESSION['address_details']['delivery']=array("nametitle"=>$post_arr['nametitle'],"firstname"=>$post_arr['firstname'],"lastname"=>$post_arr['lastname'],"address1"=>$post_arr['address1'],"address2"=>$post_arr['address2'],"city"=>$post_arr['city'],"county"=>$post_arr['state'],"postcode"=>$post_arr['postcode'],"country"=>$post_arr['country'],"phone"=>$post_arr['phone'],"comments"=>$post_arr['comments'],"sameasbilling"=>$post_arr['matchbilling'][0]);
				}
				else
				{
					$_SESSION['address_details']['delivery']=array("nametitle"=>$post_arr['deliver_nametitle'],"firstname"=>$post_arr['deliver_firstname'],"lastname"=>$post_arr['deliver_lastname'],"address1"=>$post_arr['deliver_address1'],"address2"=>$post_arr['deliver_address2'],"city"=>$post_arr['deliver_city'],"county"=>$post_arr['deliver_state'],"postcode"=>$post_arr['deliver_postcode'],"country"=>$post_arr['deliver_country'],"phone"=>$post_arr['deliver_phone'],"comments"=>$post_arr['comments'],"sameasbilling"=>$post_arr['matchbilling'][0]);
				}
				if($regoncheckout==1)
				{
					$newpass1=hashandsalt($post_arr['email'],$post_arr['password1']);
					$newpass2=hashandsalt($post_arr['email'],$newpass1);
					$_SESSION['pass']=$newpass1;
					$_SESSION['email']=$post_arr['email'];
					$date=date('U');
					$country=str_replace("GB","100",$post_arr['country']);
					/*ysql_query("INSERT INTO cart_customers (`nametitle`,`firstname`,`lastname`,`email`,`gpassword`,`phone`,`address1`,`address2`,`city`,`state`,`postcode`,`country`,`homepage`,`company`,`mailing`,`signup_date`,`status`) VALUES('$post_arr[nametitle]','$post_arr[firstname]','$post_arr[lastname]','$post_arr[email]','$newpass2','$post_arr[phone]','$post_arr[address1]','$post_arr[address2]','$post_arr[city]','$post_arr[state]','$post_arr[postcode]','$country','$post_arr[homepage]','$post_arr[company]','$post_arr[opt_in]','$date','1')",CARTDB);*/
					$q=$db1->prepare("INSERT INTO cart_customers (`nametitle`,`firstname`,`lastname`,`email`,`gpassword`,`phone`,`address1`,`address2`,`city`,`state`,`postcode`,`country`,`homepage`,`company`,`mailing`,`signup_date`,`status`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'$date','1')");
					$q->execute(array($post_arr['nametitle'],$post_arr['firstname'],$post_arr['lastname'],$post_arr['email'],$newpass2,$post_arr['phone'],$post_arr['address1'],$post_arr['address2'],$post_arr['city'],$post_arr['state'],$post_arr['postcode'],$country,$post_arr['homepage'],$post_arr['company'],$post_arr['opt_in']));
					//set confirm email details
					$message="
					Dear ".$post_arr['nametitle']." ".$post_arr['firstname']." ".$post_arr['lastname'].",<br />
					<br />
					Thank you for signing up to GMK. Please find your login details below.<br />
					<br>
					<strong>Username:</strong>&#160;".$post_arr['email']."<br />";
					
					cart_emailings($post_arr['email'],"Welcome to $sitename",$message,1);
				}
				$redi="postage";//cart_postapplicable()?"postage":"review";
				header("Location: ./cart_co_".$redi);
				exit();
			}
			break;
	}
}
if(((!isset($post_arr['shipping'])&&cart_postapplicable()==1)||!isset($_SESSION['shipping']))&&$page=="cart_co_review"){cart_redirection(MAINBASE."/cart_co_postage");}
function pdoDebug($str,$binds)
{
	$finds=array_fill(0,count($binds),"?");
	$fc=count($finds);
	$qPos=stripos($str,"?");//find first position
	for($f=0;$f<$fc;$f++)//each question mark
	{		
		$str=substr_replace($str,"",$qPos,1);//remove the ?
		$str=substr_replace($str,$binds[$f],$qPos,0);//replace ? with bind at current position in loop
		$qPos=stripos($str,"?",$qPos+1);//find next position
	}
	$str=str_ireplace(array("UPDATE","INSERT","DELETE"),array("<br />UPDATE","<br />INSERT","<br />DELETE"),$str);
	echo $str;
}
/* /HEADER ACTIONS */
function cart_trimtext($text,$chars,$link="")
{
	if(strlen($text) > $chars)
	{
		$text=substr($text,0,$chars);
		$lastspace=strrpos($text," ");
		$text=substr($text,0,$lastspace);
		$text.="...";
		$text .= strlen($link)>0?" <a href='$link'>Read&#160;More&#160;&#62;&#62;</a>":"";
	}
	return $text;
}
function cart_hashandsalt($in1,$in2)
{
	$salt=hash("sha256",$in1.$in2);
	return hash("sha256",$in2.$salt);
}
function cart_xml($strVendorTxCode,$path='../cart/orders/')
{
	global $root_to_cart, $db1;
	/*$strSQL="SELECT * FROM cart_orders WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
	$rsPrimary = ysql_query($strSQL,CARTDB) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
	$row=mysql_fetch_assoc($rsPrimary);*/
	$strSQL="SELECT * FROM cart_orders WHERE `VendorTxCode`=?";
	$rsPrimary = $db1->prepare($strSQL);
	$rsPrimary->execute(array($strVendorTxCode));
	$row=$rsPrimary->fetch(PDO::FETCH_ASSOC);
	$strSQL="";
	$prexix="G";
	$filename=$path .$prexix. $row['invoice'] . '.txt';
	if(!file_exists($filename))/*prevent data duplication and excessive emailing*/
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
		$sales_head.= date("d-m-Y",$row['date_ordered']) . ",";/*order date*/
		$sales_head.= date("d-m-Y",$row['date_ordered']) . ",,,";/*required date required time,booking ref*/
		$sales_head.= substr(str_replace(","," - ",$row['comments']),0,70) .",";/*comments (del instruct 1)*/
		$sales_head.= ",,,,";/*del instruct 2-4,contact name*/
		$sales_head.= substr(str_replace(","," ",(strlen($row['alt_phone'])<1?$row['phone']:$row['alt_phone'])),0,20).",";/*contact tel*/
		$sales_head.= ",";/*contact country*/
		$sales_head.= substr(str_replace(","," ",(strlen($row['alt_email'])<1?$row['email']:$row['alt_email'])),0,200);	/*contact email*/
		
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
				$q=$db1->prepare("UPDATE nav_stock SET `nav_qty`=`nav_qty`-? WHERE `nav_skuvar`=?");
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
					$q=$db1->prepare("UPDATE nav_stock SET `nav_qty`=`nav_qty`-? WHERE `nav_skuvar`=?");
					$q->execute(array($order_kits['item_qty']*$order_prods['qty'],$order_kits['okit_skuvar']));
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
}
function cart_orderemail($strVendorTxCode)
{
	global $root_to_cart,$postaladdy,$vatreg,$coreg,$admin_email,$sitename,$sales_email,$cart_order_email,$strConnectTo,$warranty_email, $db1;
	
	//Execute the SQL command
	/*
	$strSQL="SELECT * FROM cart_orders WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
	$rsPrimary = ysql_query($strSQL,CARTDB) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
	$row=mysql_fetch_assoc($rsPrimary);*/
	$strSQL="SELECT * FROM cart_orders WHERE `VendorTxCode`=?";
	$rsPrimary = $db1->prepare($strSQL);
	$rsPrimary->execute(array($strVendorTxCode));
	$row=$rsPrimary->fetch(PDO::FETCH_ASSOC);
	$strSQL="";
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
/*$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount` FROM cart_orders as o,cart_orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`itemId` WHERE o.`VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "' AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";

$orderq=ysql_query($sstring,CARTDB);*/
$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount` FROM cart_orders as o,cart_orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`itemId` WHERE o.`VendorTxCode`=? AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";
$orderq=$db1->prepare($sstring);
$orderq->execute(array($strVendorTxCode));
$warrantyOrder=0;
while($order=$orderq->fetch(PDO::FETCH_ASSOC))
{
	$iQuantity=$order['qty'];
	$iProductId=$order['prod_id'];
	if($iProductId=='358'){$warrantyOrder=1;}
/*	$orderkitq=ysql_query("SELECT `fusionId`,`kit_title`,`item_qty`,`oname`,`oitem,prod_id` FROM cart_orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`itemId` AND `itemType`='product' WHERE `order_prod_id`='$order[order_prod_id]' GROUP BY `okit_id`",CARTDB);
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
	{
		?>
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
	echo "-----------------------------------------------".PHP_EOL.PHP_EOL;
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

<?=str_replace("<br />",PHP_EOL,$postaladdy)?>


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
	$to_llc_orders=($strConnectTo=="TEST")?"senfield@gmk.co.uk":$cart_order_email;
	if(!isset($_SESSION['sentemail'])||$_SESSION['sentemail']!=$row['invoice'])
	{
		$_SESSION['sentemail']=$row['invoice'];
		//$mail_sent_llc = @mail( $to_llc_orders, $sitename." Invoice ".$row['invoice'].$sat, $message, $headers );
		//$mail_sent_test = mail( "senfield@gmk.co.uk", $sitename." Invoice ".$row['invoice'].$sat, $message.$cart_order_email, $headers );
		$mail_sent_cust = @mail( $row['email'], "Thank You for your order at ".$sitename, $message, $headers );
		if($warrantyOrder==1){@mail( $warranty_email, "Warranty: ".$sitename." Invoice ".$row['invoice'], $message, $headers );}
	}
	/* /SEND EMAILS OUT*/
}
// admin index page plug in
function cart_adminindex()
{
	//new orders, new enquiries, orders in last 7, new cust in last 7, running promos, prods low stock, prods in shop, most popular prod, most delivered to county
	global $stocklimit,$lastdays,$self,$mods, $db1;
	/* pending items */
	if(in_array(4,$mods)){
	//$pgnums=cart_pagenums("SELECT order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status FROM cart_orders as o LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id WHERE `order_status`='New' ORDER BY `date_ordered` DESC","$self",10,5);
			$query="SELECT order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status FROM cart_orders as o LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id WHERE `order_status`='New' AND pay_method !='Free' ORDER BY `date_ordered` DESC LIMIT 10";//$pgnums[0];
			/*$invQ=ysql_query($query,CARTDB)or die(sql_error("Error","$query<br />".mysql_error()));
			$invNum=mysql_num_rows($invQ);*/
			$invQ=$db1->query($query);
			$invNum=$invQ->rowCount();
			?>			
			<table style="float:left;width:49%;margin:0 10px 10px;">
			<tr class="head"><td colspan="3"><?=$invNum?> Recent (Pending) Orders</td></tr>
			<tr class="subhead">
				<td style="width:25%;text-align:left">Order Date</td>
				<td style="width:65%">Customer</td>
				<td style="width:10%;text-align:center">Paid?</td>
			</tr>
			<?php 
			//while($inv=mysql_fetch_assoc($invQ))
			while($inv=$invQ->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td style="text-align:left"><a href="?p=cart_invoices&amp;act=view&amp;invoice=<?=$inv['invoice']?>"><?=date("M j\, Y",$inv['date_ordered'])?></a></td>
					<?php if(strlen($inv['custid'])>0){?><td class="blocklink"><a href="?p=cart_customers&amp;act=view&amp;cust_id=<?=$inv['custid']?>"><?php }else{?><td><span><?php }?><?=ucwords($inv['firstname']." ".$inv['lastname'])?><?php if(strlen($inv['custid'])>0){?></a><?php }else{?></span><?php }?></td>
					<td style="text-align:center"><span><?=$inv['pay_status']==1?$inv['pay_method']:"Unpaid"?></span></td>
				</tr>
				<?php 
			}
			if($invNum==0){?>
			<tr><td colspan="8" style="text-align:center">No invoices found for this time period</td></tr>
			<?php }?>
			<?php if(strlen($pgnums[1])>0){?>
			<tr class="infohead">
				<td colspan="8"><?=$pgnums[1]?></td>
			</tr>
			<?php }?>
			</table>
			<?php }
			if(in_array(6,$mods)){
				
			$enqs=$db1->query("SELECT * FROM cart_contactus ORDER BY `date_created` DESC LIMIT 10");
			$enqnum=$enqs->rowCount();
			?>
			<table style="float:left;width:48%;margin:0 0 10px;">
			<tr class="head">
				<td colspan="3"><div class="titles"><?=$enqnum?> Recent Enquiries</div></td>
			</tr>
			<tr class="subhead">
				<td style="width:30%">Name</td>
				<td style="width:35%">Sample</td>
				<td style="width:35%">Date</td>
			</tr>
			<?php 
			/*$enqs=ysql_query("SELECT * FROM cart_contactus ORDER BY `date_created` ASC",CARTDB);
			$enqnum=mysql_num_rows($enqs);
			while($enq=mysql_fetch_assoc($enqs))*/
			while($enq=$enqs->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="?p=cart_enquiries&amp;act=view&amp;eid=<?=$enq['contactus_id']?>"><?=$enq['name']?></a></td>
					<td><span><?=substr($enq['comments'],0,20)."..."?></span></td>
					<td><span><?=date("d/m/Y h:i A",strtotime($enq['date_created']))?></span></td>
				</tr>
				<?php 
			}
			if($enqnum<1)
			{
				?>
				<tr class="row_dark"><td colspan="5" style="text-align:center"><span>No pending enquiries</span></td></tr>
				<?php
			}
			?>
		</table><?php }?>
		<div class="clear"></div>
			<?php
	/* /pending items */
	
	
	
	$last7=strtotime("today")-(86400*$lastdays);
	
	$orders= $db1->prepare("SELECT SUM(`total_price`),count(distinct(`order_id`)),count(distinct(`prod_id`)),SUM(IF(`iorder_status`=0,1,0)),SUM(IF(`order_status`='New',1,0)) FROM cart_orders as o JOIN cart_orderproducts as op USING(`order_id`) WHERE `date_ordered` >= ?");
	$orders->execute(array($last7));
	list($orderstprice,$orderstcount,$orderstpcount,$orderstincomp,$ordersnew)=$orders->fetch(PDO::FETCH_NUM);
	$neworders=$db1->query("SELECT count(`order_id`),SUM(`total_price`) FROM cart_orders WHERE `order_status`='New'");
	list($neworder,$neworderprice)=$neworders->fetch(PDO::FETCH_NUM);
	$newcusts=$db1->prepare("SELECT count(`cust_id`) FROM cart_customers WHERE `signup_date` >= ?");
	$newcusts->execute(array($last7));
	list($newcust)=$newcusts->fetch(PDO::FETCH_NUM);
	$promos=$db1->query("SELECT count(`order_id`) FROM cart_discounts as d LEFT JOIN cart_orders as o ON o.`discount_code`=d.`code` WHERE d.`date_end` > '".date("U")."' AND d.`state`='1' AND d.`date_start` <= '".date("U")."' GROUP BY `discount_id`");
	list($promoinorders)=$promos->fetch(PDO::FETCH_NUM);
	$promo=$promos->rowCount();
	$totalprods=$db1->query("SELECT count(distinct(p.`".PFIELDID."`)),count(cv.`vid`) FROM (".PTABLE." as p JOIN cart_fusion as c ON p.`".PFIELDID."`=c.`pid`) LEFT JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."` WHERE c.`allowpurchase`='1'");
	list($totalprod,$totalvars)=$totalprods->fetch(PDO::FETCH_NUM);
	$popprods=$db1->query("SELECT p.`".PFIELDNAME."`,p.`".PFIELDID."`,`ownerId`,op.`prod_id` FROM ((cart_orders as o JOIN cart_orderproducts as op USING(`order_id`)) JOIN ".PTABLE." as p ON p.`".PFIELDID."`=op.`prod_id`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` GROUP BY op.`prod_id` ORDER BY count(op.`prod_id`) DESC LIMIT 1");
	list($popprod,$popprodid,$popprodowner)=$popprods->fetch(PDO::FETCH_NUM);
	$pendingqs=$db1->query("SELECT count(`contactus_id`) FROM cart_contactus");
	list($pendingq)=$pendingqs->fetch(PDO::FETCH_NUM);
	$popcounties=$db1->query("SELECT `countyname` FROM cart_orders as o JOIN cart_counties as c ON o.`state`=c.`county_id` GROUP BY `state` ORDER BY count(`state`) DESC LIMIT 1");
	list($popcounty)=$popcounties->fetch(PDO::FETCH_NUM);
	$lowstocks=$db1->prepare("SELECT `cfid` FROM ((".PTABLE." as p JOIN cart_fusion as c ON p.`".PFIELDID."`=c.`pid`) LEFT JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN nav_stock as n ON cv.`vskuvar`=n.`nav_skuvar` WHERE c.`allowpurchase`='1' GROUP BY p.`".PFIELDID."` HAVING SUM(`nav_qty`) < ?");
	$lowstocks->execute(array($stocklimit));
	$lowstock=$lowstocks->rowCount();
	?>
	<div class="clear"></div>
	<table style="float:left;width:49%;margin:0 10px;">
		<tr class="head">
			<td colspan="2"><div class="titles">Quick Stats</div></td>
		</tr>
		<tr class="subhead">
			<td>Detail</td>
			<td>Description</td>
		</tr>
		<tr>
			<td class="left_light" style="width:10%;text-align:center"><?=$neworder?></td>
			<td class="right_light" style="width:90%">New orders awaiting action. <?=$neworder>0?"Total: &#163;".$neworderprice:""?>.</td>
		</tr>
		<tr>
			<td class="left_dark" style="width:10%;text-align:center"><?=$pendingq?></td>
			<td class="right_dark" style="width:90%">Pending <a href="?p=cart_enquiries">enquiries</a>.</td>
		</tr>
		<tr>
			<td class="left_light" style="width:10%;text-align:center"><?=$orderstcount?></td>
			<td class="right_light" style="width:90%">
			<form name="orders" action="?p=cart_reports&amp;report=order&amp;showgen=1" method="post"><input type="hidden" name="sstatus" value="all" /><input type="hidden" name="from" value="<?=date("Y-m-d",$last7)?>" /><input type="hidden" name="to" value="<?=date("Y-m-d")?>" /><input type="hidden" name="what" value="Orders by date" /></form><form name="ordersp" action="?p=cart_reports&amp;report=order&amp;showgen=1" method="post"><input type="hidden" name="sstatus" value="all" /><input type="hidden" name="from" value="<?=date("Y-m-d",$last7)?>" /><input type="hidden" name="to" value="<?=date("Y-m-d")?>" /><input type="hidden" name="what" value="Products ordered by date" /></form>
			Reports: <a href="javascript:document.forms['orders'].submit();">Orders in the last <?=$lastdays?> days</a> (<?=$orderstincomp?> incomplete, <?=$ordersnew?> new) with <a href="javascript:document.forms['ordersp'].submit();"><?=$orderstpcount?> unique products</a>. <?=$orderstcount>0?"Total: &#163;".$orderstprice:""?>.
			</td>
		</tr>
		<tr>
			<td class="left_dark" style="width:10%;text-align:center"><?=$newcust?></td>
			<td class="right_dark" style="width:90%"><a href='?p=cart_customers&amp;searchfrom=<?=$last7?>'>New customers</a> in the last <?=$lastdays?> days.</td>
		</tr>
		<tr>
			<td class="left_light" style="width:10%;text-align:center"><?=$promo?></td>
			<td class="right_light" style="width:90%">Running promotions (used in <a href="?p=cart_promotions"><?=$promoinorders?> order<?=$promoinorders>1||$promoinorders<1?"s":""?></a>).</td>
		</tr>
		<tr>
			<td class="left_dark" style="width:10%;text-align:center"><?=$lowstock?></td>
			<td class="right_dark" style="width:90%">Reports: Products running low on stock (<a href='index.php?p=cart_reports&report=products'>View all stock</a>).</td>
		</tr>
		<tr>
			<td class="left_light" style="width:10%;text-align:center"><?=$totalprod?></td>
			<td class="right_light" style="width:90%">Reports: <a href='index.php?p=cart_reports&report=products'>Total products in shop</a> (<?=$totalvars?> total variations).</td>
		</tr>
		<tr>
			<td class="left_dark" style="width:10%;text-align:center"><?=$popprod?></td>
			<td class="right_dark" style="width:90%;white-space:nowrap"><a href="./<?=ADMINPRODUCTS?>&amp;showing=prodform&amp;pid=<?=$popprodid?>&amp;owner=<?=$popprodowner?>&amp;curpage=<?=urlencode($popprod)?>">Most popular product</a>.</td>
		</tr>
		<tr>
			<td class="left_light" style="width:10%;text-align:center"><?=$popcounty?></td>
			<td class="right_light" style="width:90%;white-space:nowrap">County most delivered to.</td>
		</tr>
	</table>
	<?php
}
//form plugin for cart_fusion data
function cart_prodedit($prodid)
{
	global $modules_pages, $page, $mods, $fields, $post_arr, $get_arr,$formaction,$cart_path, $db1;
	$key = array_search("cart_prods", $modules_pages);
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		if(!isset($get_arr['pid']))
		{
			/*$get_types=ysql_query("SELECT `ctype` FROM gmk_categories WHERE `cid`='".$get_arr['owner']."'",CARTDB);
			list($fctype)=mysql_fetch_row($get_types);*/
			$get_types=$db1->prepare("SELECT `ctype` FROM gmk_categories WHERE `cid`=?");
			$get_types->execute(array($get_arr['owner']));
			list($fctype)=$get_types->fetch();
		}
		/*$cart_prods=ysql_query("SELECT * FROM cart_fusion as cf WHERE `pid`='$prodid'",CARTDB);
		$cart_prod=@mysql_fetch_assoc($cart_prods);
		$variants=ysql_query("SELECT * FROM (cart_variants as cv LEFT JOIN fusion as f ON cv.`pid`=f.`itemId`) LEFT JOIN gmk_categories as c ON c.`cid`=f.`ownerId` WHERE `pid`='$prodid' GROUP BY `vid` ORDER BY `order` ASC",CARTDB);
		$variantcount=@mysql_num_rows($variants);
		$variant=@mysql_fetch_assoc($variants);	*/	
		$cart_prods=$db1->prepare("SELECT * FROM cart_fusion as cf WHERE `pid`=?");
		$cart_prods->execute(array($prodid));
		$cart_prod=$cart_prods->fetch(PDO::FETCH_ASSOC);
		$variants=$db1->prepare("SELECT * FROM (cart_variants as cv LEFT JOIN fusion as f ON cv.`pid`=f.`itemId` AND f.itemType='product') LEFT JOIN gmk_categories as c ON c.`cid`=f.`ownerId` AND f.ownerType='category' WHERE `pid`=? GROUP BY `vid` ORDER BY `order` ASC");
		$variants->execute(array($prodid));
		$variantcount=$variants->rowCount();
		$variant=$variants->fetch(PDO::FETCH_ASSOC);
		$fctype=!isset($fctype)?$variant['ctype']:$fctype;
		$fctype=strlen($fctype)<1||!array_key_exists($fctype,$fields)?"default":$fctype;
		$vsku_info=array_splice($fields[$fctype],array_search("vskuvar",array_values($fields[$fctype])),1);		
		$vsku_title=key($vsku_info);
		$vsku_field=$vsku_info[$vsku_title];
		$colspan=count($fields[$fctype]);
		$widths=100/$colspan;		
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<script type="text/javascript">boxarr['delvar']=[];</script>
		<table style="margin-bottom:30px;border-bottom:0;">
			<tr class="subhead">
				<td colspan="<?=$colspan+2?>"><div class="titles">Cart Settings</div></td>
			</tr>
			<tr>
				<td colspan="2" class="left_light">Sale Discount</td>
				<td colspan="<?=$colspan?>" class="right_light"><input type="text" name="salediscount" value="<?=$cart_prod['salediscount']?>" class="input_text_med" /> <input type="radio" id="saletype0" name="saletype" value="0" <?=$cart_prod['saletype']==0?"checked='checked'":""?> /><label for="saletype0"> % (Discount is a percentage)</label> <input type="radio" id="saletype1" name="saletype" value="1" <?=$cart_prod['saletype']==1?"checked='checked'":""?> /><label for="saletype1"> &#163; (Discount is a set figure in pounds)</label></td>
			</tr>
			<tr>
				<td colspan="2" class="left_dark">Exclude from discounts?</td>
				<td colspan="<?=$colspan?>" class="right_dark">				
				<label for="excludediscount1" class="yes"><input type="radio" name="excludediscount" id="excludediscount1" value="1" <?=$cart_prod['excludediscount']?"checked='checked'":""?> /> Yes</label><label for="excludediscount0" class="no"><input type="radio" name="excludediscount" id="excludediscount0" value="0" <?=$cart_prod['excludediscount']?"":"checked='checked'"?> /> No</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="left_light">Display in shop?</td>
				<td colspan="<?=$colspan?>" class="right_light">
				<label for="allowpurchase1" class="yes"><input type="radio" name="allowpurchase" id="allowpurchase1" value="1" <?=$cart_prod['allowpurchase']?"checked='checked'":""?> /> Yes</label><label for="allowpurchase0" class="no"><input type="radio" name="allowpurchase" id="allowpurchase0" value="0" <?=$cart_prod['allowpurchase']?"":"checked='checked'"?> /> No</label>
				</td>
			</tr>
		</table>
		<p class="submittop"><a href="<?=$formaction?>&amp;new=1#new">Add Variant</a></p>
		<table style="margin-bottom:0;border-top:0;">
			<tr class="head">
				<td colspan="<?=$colspan+3?>"><div class="titles">Variants</div></td>
			</tr>
			<?php
				//$nav_skus=ysql_query("SELECT `nav_skuvar`,`nav_description`,`nav_qty` FROM nav_stock ORDER BY `nav_skuvar`,`nav_variant`",CARTDB);
				$nav_skus=$db1->prepare("SELECT `nav_skuvar`,`nav_description`,`nav_qty` FROM nav_stock ORDER BY `nav_skuvar`,`nav_variant`");
				if($variantcount>0||isset($get_arr['new'])||strlen($prodid)<1)
				{
					?>
					<tr class="subhead">
						<?php foreach($fields[$fctype] as $fieldname => $col){?>
						<td style="text-align:center;width:<?=$widths?>%"><?=ucwords($fieldname)?></td>
						<?php }?>
						<td style="text-align:center;">Trade &#163;</td>
						<td style="text-align:center;">Sorting</td>
						<td style="text-align:right;white-space:nowrap;">Del <input type="checkbox" onclick="cart_multiCheck(this.form,'delvar',this)" /></td>
					</tr>
					<?php
				}
				if($variantcount<1&&!isset($get_arr['new'])&&strlen($prodid)>0)
				{
					?>
					<tr class="row_light">
						<td colspan="<?=$colspan+3?>">No Variants Found</td>
					</tr>
					<?php
				}
				else
				{	
					//@mysql_data_seek($variants,0);
					//while($variant=@mysql_fetch_assoc($variants))
					$variants->execute(array($prodid));
					while($variant=$variants->fetch(PDO::FETCH_ASSOC))
					{
						$row=!isset($row)||$row=="dark"?"light":"dark";
						?>
						<tr class="row_<?=$row?>">
							<?php foreach($fields[$fctype] as $fieldname => $col){?>
							<td style="text-align:center">
							<?php if($fieldname=="caliber"&&$fctype=="rifle"){
								cart_calibers($variant[$col],$fctype,$col."[".$variant['vid']."]");
							}else{?>
							<input type="text" name="<?=$col?>[<?=$variant['vid']?>]" value="<?=htmlspecialchars($variant[$col],ENT_QUOTES,"ISO-8859-1")?>" class="input_text<?=$fieldname=="caliber"||$col=="vname"?"":"_med"?>" />
							<?php }?>
							</td>
							<?php }?>
							<td style="text-align:center">
							<input type="text" name="tradeprice[<?=$variant['vid']?>]" value="<?=htmlspecialchars($variant['tradeprice'],ENT_QUOTES,"ISO-8859-1")?>" class="input_text_med" />
							</td>
							<td style="text-align:center;"><input type="text" name="order[<?=$variant['vid']?>]" value="<?=$variant['order']?>" style="width:20px !important" /></td>
							<td style="text-align:right"><input type="hidden" id="delvar[<?=$variant['vid']?>]" name="delvar[]" value="0" />
							<input type="checkbox" name="delvar[]" id="delvar[<?=$variant['vid']?>]" value="<?=$variant['vid']?>" />
							<script type="text/javascript">boxarr["delvar"].push("delvar[<?=$variant['vid']?>]");</script>
							</td>
						</tr>
						<tr class="row_<?=$row?>">
							<td colspan="<?=$colspan+3?>">
							&nbsp;&nbsp;<?=ucwords($vsku_title)?>: 
							<select name="<?=$vsku_field?>[<?=$variant['vid']?>]">
								<option value=""></option>
								<?php 
								$navskuarr=array();
								//while(list($navskuvar,$navdesc,$navqty)=mysql_fetch_row($nav_skus))
								$nav_skus->execute();
								while(list($navskuvar,$navdesc,$navqty)=$nav_skus->fetch(PDO::FETCH_NUM))
								{
									$navskubits=explode("-v-",$navskuvar);
									?><option value="<?=$navskuvar?>" <?php if(isset($variant[$vsku_field])&&$navskuvar==$variant[$vsku_field]){?>selected='selected'<?php }?>><?=$navskubits[0]." (".str_replace(array("NONE","V00","V000"),array("V0","V","V"),$navskubits[1]).")".(strlen($navdesc)>0?" - ".$navdesc:"")." [Stock:".$navqty."]"?></option><?php
									$navskuarr[]=$navskuvar;
								}
								//mysql_data_seek($nav_skus,0);
								?>
							</select>
							or Manual <input type="text" name="<?=$vsku_field?>_alt[<?=$variant['vid']?>]" value="<?=isset($navskuarr)&&is_array($navskuarr)&&!in_array($variant[$vsku_field],$navskuarr)?str_replace("-v-NONE","",$variant[$vsku_field]):""?>" />
							</td>
						</tr>
						<?php
					}
				}
				if(isset($get_arr['new'])||strlen($prodid)<1)
				{
					$row=!isset($row)||$row=="dark"?"light":"dark";					
					?>
					<tr class="row_<?=$row?>">
						<?php foreach($fields[$fctype] as $fieldname => $col){?>
						<td style="text-align:center">
						<input type="text" name="<?=$col?>[new]" value="<?=isset($post_arr[$col])?$post_arr[$col]['new']:""?>" class="input_text<?=$fieldname=="caliber"||$col=="vname"?"":"_med"?>" />
						</td>
						<?php }?>	
						<td style="text-align:center">
							<input type="text" name="tradeprice[new]" value="<?=isset($post_arr['tradeprice'])?$post_arr['tradeprice']['new']:""?>" class="input_text_med" />
							</td>					
						<td style="text-align:center;"><input type="text" name="order[new]" value="" style="width:20px !important" /></td>
						<td><a name="new" /></td>
					</tr>
					<tr class="row_<?=$row?>">
						<td colspan="<?=$colspan+3?>">
						&nbsp;&nbsp;<?=ucwords($vsku_title)?>: 
						<select name="<?=$vsku_field?>[new]">
							<option value=""></option>
							<?php 
							//while(list($navskuvar,$navdesc,$navqty)=mysql_fetch_row($nav_skus))
							$nav_skus->execute();
							while(list($navskuvar,$navdesc,$navqty)=$nav_skus->fetch(PDO::FETCH_NUM))
							{
								$navskubits=explode("-v-",$navskuvar);
								?><option value="<?=$navskuvar?>"<?php if($navqty<1){?>style="color:#800D0D;"<?php }?> <?php if(isset($post_arr[$vsku_field])&&$navskuvar==$post_arr[$vsku_field]){?>selected='selected'<?php }?>><?=$navskubits[0]." (".str_replace(array("NONE","V00","V000"),array("V0","V","V"),$navskubits[1]).")".(strlen($navdesc)>0?" - ".$navdesc:"")." [Stock:".$navqty."]"?></option><?php
							}
							?>
						</select>
						or Manual <input type="text" name="<?=$vsku_field?>_alt[new]" value="<?=isset($navskuarr)&&is_array($navskuarr)&&!in_array($post_arr[$vsku_field],$navskuarr)?str_replace("-v-NONE","",$post_arr[$vsku_field]):""?>" />
						</td>
					</tr>
					<?php
				}
			?>
		</table>
		<?php if($fctype=="rifle"){?>
		<dfn>&nbsp;&nbsp;&nbsp;Hold CTRL while selecting multiple calibers</dfn>
		<?php }?>
		<?php
	}
}
function cart_calibers($sel,$type,$name)
{
	global $abbrev;
	if(!isset($abbrev)){include "../abbreviations.php";}
	if($type=="rifle")
	{
		$sels=explode(",",str_replace(array(" "),array(""),$sel));
		?><select name="<?=$name?>[]" multiple="multiple" size="3"><?php
		foreach($abbrev['sako'] as $a => $b)
		{
			?><option value="<?=$a?>" <?=in_array($a,$sels)?"selected='selected'":""?>><?=$b?></option><?php
		}
		?></select><?php
	}
}
/*for updating cart_fusion*/
function cart_sql_prod($task,$postdata,$prodid)
{
	global $modules_pages, $page, $mods, $db1;
	$key = array_search("cart_prods", $modules_pages);
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		/*$finds=ysql_query("SELECT `cfid` FROM cart_fusion WHERE `pid`='$prodid'",CARTDB);
		$find=mysql_num_rows($finds);*/
		$finds=$db1->prepare("SELECT `cfid` FROM cart_fusion WHERE `pid`=?");
		$finds->execute(array($prodid));
		$find=$finds->rowCount();
		$task=$find<1&&$task!="delete"?"add":$task;
		/*switch($task)
		{
			case "add":
				cart_query("INSERT INTO cart_fusion(`pid`,`salediscount`,`saletype`,`excludediscount`,`allowpurchase`)VALUES('$prodid','".$postdata['salediscount']."','".$postdata['saletype']."','".$postdata['excludediscount']."','".$postdata['allowpurchase']."')",CARTDB);
				break;
			case "update":
				cart_query("UPDATE cart_fusion SET `salediscount`='".$postdata['salediscount']."', `saletype`='".$postdata['saletype']."',`excludediscount`='".$postdata['excludediscount']."',`allowpurchase`='".$postdata['allowpurchase']."' WHERE `pid`='$prodid'",CARTDB);
				break;
			case "delete":
				sql_query("DELETE FROM cart_fusion WHERE `pid`='$prodid'",CARTDB);
				$getowners=ysql_query("SELECT `ownerId` FROM fusion WHERE `itemId`='$prodid'",CARTDB);
				list($owner)=mysql_fetch_row($getowners);
				break;
		}*/
		switch($task)
		{
			case "add":
				cart_query("INSERT INTO cart_fusion(`pid`,`salediscount`,`saletype`,`excludediscount`,`allowpurchase`)VALUES(?,?,?,?,?)",array($prodid,$postdata['salediscount'],$postdata['saletype'],$postdata['excludediscount'],$postdata['allowpurchase']));
				break;
			case "update":
				cart_query("UPDATE cart_fusion SET `salediscount`=?, `saletype`=?,`excludediscount`=?,`allowpurchase`=? WHERE `pid`=?",array($postdata['salediscount'],$postdata['saletype'],$postdata['excludediscount'],$postdata['allowpurchase'],$prodid));
				break;
			case "delete":
				sql_query("DELETE FROM cart_fusion WHERE `pid`=?",$db1,array($prodid));
				$getowners=$db1->prepare("SELECT `ownerId` FROM fusion WHERE `itemId`=?");
				$getowners->execute(array($prodid));
				list($owner)=$getowners->fetch();
				break;
		}
		if(isset($postdata['vname']))
		{
			foreach($postdata['vname'] as $vid => $val)
			{
				if(is_array($postdata['delvar'])&&in_array($vid,$postdata['delvar'])){
					//ysql_query("DELETE FROM cart_variants WHERE `vid`='$vid'");
					$q=$db1->prepare("DELETE FROM cart_variants WHERE `vid`=?");
					$q->execute(array($vid));
				}
				else
				{
					$vskuvarr=strlen($postdata['vskuvar_alt'][$vid])?$postdata['vskuvar_alt'][$vid]."-v-NONE":$postdata['vskuvar'][$vid];
					$f1=is_array($postdata['field1'][$vid])?implode(",",$postdata['field1'][$vid]):$postdata['field1'][$vid];
					switch($vid)
					{
						case "new":
							/*cart_query("INSERT INTO cart_variants(`pid`,`vname`,`vskuvar`,`field1`,`field2`,`field3`,`field4`,`kg`,`tradeprice`,`price`,`order`)VALUES('".$prodid."','".$val."','".$vskuvarr."','".$postdata['field1'][$vid]."','".$postdata['field2'][$vid]."','".$postdata['field3'][$vid]."','".$postdata['field4'][$vid]."','".$postdata['kg'][$vid]."','".$postdata['tradeprice'][$vid]."','".$postdata['price'][$vid]."','".$postdata['order'][$vid]."')",CARTDB);*/
							cart_query("INSERT INTO cart_variants(`pid`,`vname`,`vskuvar`,`field1`,`field2`,`field3`,`field4`,`kg`,`tradeprice`,`price`,`order`)VALUES(?,?,?,?,?,?,?,?,?,?,?)",array($prodid,$val,$vskuvarr,$f1,$postdata['field2'][$vid],$postdata['field3'][$vid],$postdata['field4'][$vid],(strlen($postdata['kg'][$vid])>0?$postdata['kg'][$vid]:0),$postdata['tradeprice'][$vid],$postdata['price'][$vid],$postdata['order'][$vid]));
							break;
						default:
							/*cart_query("UPDATE cart_variants SET `vname`='$val',`field1`='".$postdata['field1'][$vid]."',`field2`='".$postdata['field2'][$vid]."',`field3`='".$postdata['field3'][$vid]."',`field4`='".$postdata['field4'][$vid]."',`vskuvar`='".$vskuvarr."',`kg`='".$postdata['kg'][$vid]."',`tradeprice`='".$postdata['tradeprice'][$vid]."',`price`='".$postdata['price'][$vid]."',`order`='".$postdata['order'][$vid]."' WHERE `vid`='$vid'",CARTDB);*/
							cart_query("UPDATE cart_variants SET `vname`=?,`field1`=?,`field2`=?,`field3`=?,`field4`=?,`vskuvar`=?,`kg`=?,`tradeprice`=?,`price`=?,`order`=? WHERE `vid`=?",array($val,$f1,$postdata['field2'][$vid],$postdata['field3'][$vid],$postdata['field4'][$vid],$vskuvarr,$postdata['kg'][$vid],$postdata['tradeprice'][$vid],$postdata['price'][$vid],$postdata['order'][$vid],$vid));
							break;
					}
				}
			}
		}
	}
}
function cart_catform_opt($catid,$array=0)
{ 
	global $modules_pages, $page, $mods, $db1;
	$key = array_search("cart_prods", $modules_pages);
	$name=$array==0?"showincart":"showincart[$catid]";
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		/*$cart_catopts=ysql_query("SELECT * FROM cart_catopts WHERE `cat_id`='$catid'",CARTDB);
		$cart_catopt=mysql_fetch_assoc($cart_catopts);*/
		$cart_catopts=$db1->prepare("SELECT * FROM cart_catopts WHERE `cat_id`=?");
		$cart_catopts->execute(array($catid));
		$cart_catopt=$cart_catopts->fetch(PDO::FETCH_ASSOC);
		if($array!=0){?>
		 <input type="hidden" name="<?=$name?>" value="0" />
		<input type="checkbox" name="<?=$name?>" value="1" id="<?=$name?>" <?=$cart_catopt['showincart']==1?"checked='checked'":""?> />
		 <script type="text/javascript">boxarr["showincart"].push("<?=$name?>");</script><?php }
		else{?>
		<label for="<?=$name?>1" class="yes"><input type="radio" name="<?=$name?>" value="1" id="<?=$name?>1" <?=$cart_catopt['showincart']==1?"checked='checked'":""?> /> On</label><label for="<?=$name?>0" class="no"><input type="radio" name="<?=$name?>" value="0" id="<?=$name?>0" <?=$cart_catopt['showincart']==1?"":"checked='checked'"?> /> Off</label>
		<?php }
	}
}
function cart_sql_cat($task,$postdata,$catid)
{
	global $modules_pages, $page, $mods,$db1;
	$key = array_search("cart_prods", $modules_pages);
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		/*$finds=ysql_query("SELECT * FROM cart_catopts WHERE `cat_id`='$catid'",CARTDB);
		$find=mysql_num_rows($finds);*/
		$finds=$db1->prepare("SELECT * FROM cart_catopts WHERE `cat_id`=?");
		$finds->execute(array($catid));
		$find=$finds->rowCount();
		$task=$find<1&&$task!="delete"?"add":$task;
		switch($task)
		{
			case "update":
				sql_query("UPDATE cart_catopts SET `showincart`=? WHERE `cat_id`=?",$db1,array($postdata,$catid));
				break;
			case "add":
				sql_query("INSERT INTO cart_catopts(`cat_id`,`showincart`)VALUES(?,?)",$db1,array($catid,$postdata['showincart']));
				break;
			case "delete":
				sql_query("DELETE FROM cart_catopts WHERE `cat_id`=?",$db1,array($catid));
				break;
		}
	}
}

function cart_allowpurch_head()
{
	global $modules_pages, $page, $mods, $db1;
	$key = array_search("cart_prods", $modules_pages);
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		?><td style="width:10%;text-align:right;white-space:nowrap;"><?=SHOP_ONOFF?> <input type="checkbox" onclick="cart_multiCheck(this.form,'allowpurchase',this)" /><script type="text/javascript">boxarr['allowpurchase']=[];</script></td><?php
	}
}
function cart_allowpurch_opt($pid,$array=0)
{
	global $modules_pages, $page, $mods, $db1;
	$key = array_search("cart_prods", $modules_pages);
	$name=$array==0?"allowpurchase":"allowpurchase[$pid]";
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		/*$cart_apopts=ysql_query("SELECT * FROM cart_fusion WHERE `pid`='$pid'",CARTDB);
		$cart_apopt=mysql_fetch_assoc($cart_apopts);*/
		$cart_apopts=$db1->prepare("SELECT * FROM cart_fusion WHERE `pid`=?");
		$cart_apopts->execute(array($pid));
		$cart_apopt=$cart_apopts->fetch(PDO::FETCH_ASSOC);
		?>
		<input type="hidden" name="<?=$name?>" value="0" />
		<input type="checkbox" name="<?=$name?>" value="1" id="<?=$name?>" <?=$cart_apopt['allowpurchase']==1?"checked='checked'":""?> />
		<script type="text/javascript">boxarr['allowpurchase'].push("<?=$name?>");</script>
		<?php
	}
}
function cart_sql_allowpurch($postdata,$prodid)
{
	global $modules_pages, $page, $mods, $db1;
	$key = array_search("cart_prods", $modules_pages);
	if(in_array($key,$mods)||!in_array("cart_prods",$modules_pages))
	{
		/*$finds=ysql_query("SELECT `cfid` FROM cart_fusion WHERE `pid`='$prodid'",CARTDB);
		$find=mysql_num_rows($finds);*/
		$finds=$db1->prepare("SELECT `cfid` FROM cart_fusion WHERE `pid`=?");
		$finds->execute(array($prodid));
		$find=$finds->rowCount();
		$task=$find<1?"add":"update";
		switch($task)
		{
			/*case "add":
				cart_query("INSERT INTO cart_fusion(`pid`,`allowpurchase`)VALUES('$prodid','".$postdata."')",CARTDB);
				break;
			case "update":
				cart_query("UPDATE cart_fusion SET `allowpurchase`='".$postdata."' WHERE `pid`='$prodid'",CARTDB);
				break;*/
			case "add":
				cart_query("INSERT INTO cart_fusion(`pid`,`allowpurchase`)VALUES(?,?)",array($prodid,$postdata));
				break;
			case "update":
				cart_query("UPDATE cart_fusion SET `allowpurchase`=? WHERE `pid`=?",array($postdata,$prodid));
				break;
		}
	}
}

//main cart menu to plug into admin menu
function cart_adminmenu()
{
	global $page, $menusection, $perms, $mods, $modules_pages, $modules, $db1, $uaa;

	foreach($menusection as $sectname => $items)
	{
		if(count(array_intersect($mods,$items))>0)
		{
			?>
			<ul id="<?=$sectname?>">
				<li class="heading"><?=ucwords($sectname)?></li>
				<?php foreach($items as $i)
				{
					if(in_array($i,$mods)||$uaa['super']==1)
					{
						?>
						<li id="menu<?=$modules_pages[$i]?>"><a href="index.php?p=<?=$modules_pages[$i]?>"><?=$modules[$i]?></a></li>
						<?php 
					}
				}?>
			</ul>
			<?php 
		}
	}
}
//function for query debug/execution
function cart_query($query,$binds)
{
	global $cart_debugmode,$db1;
	if($cart_debugmode){echo pdoDebug($query,$binds);}
	else{
		if(is_array($binds)&&count($binds)>0)
		{
			$q=$db1->prepare($query);
			$q->execute($binds);
		}
		else
		{
			$q=$db1->query($query);
		}
		/*if(is_resource(CARTDB)){ysql_query($query,CARTDB)or die(sql_error("Error","Query: $query<br />".mysql_error()));}
		else{ysql_query($query)or die(sql_error("Error","Query: $query<br />".mysql_error()));}*/
	}
}
function cart_delete_img($img)
{
	global $cart_debugmode;
	if($cart_debugmode){echo "Delete img: $img<br />";}
	else
	{
	if(@file_exists($img)){if(!@unlink($img)){/*$_SESSION['error']="Could not delete the image (".$img.").";*/}}else{/*$_SESSION['error']="Image not found (".$img.").";*/}}
}
function cart_getExtension($str) 
{         
	/*$i = strrpos($str,".");         
	if (!$i) { return ""; }         
	$l = strlen($str) - $i;         
	$ext = substr($str,$i+1,$l);       
	return $ext; */
	return pathinfo($str, PATHINFO_EXTENSION);
}
function cart_fileupload($target_path,$file_name,$tmp_name,$newname,$maxsize,$allowed)
{
	global $images_arr,$page;
	$upload_ok = 1;
	$size1 = number_format(filesize($tmp_name) / 1024,2);
	$filename1 = basename($file_name);
	if ($newname) { $extension1 = pathinfo($filename1, PATHINFO_EXTENSION); }
	
	if(!in_array($extension1, $allowed)){$error="File extension: $extension1 $newname not allowed, please go back and try again.";}
	else if($size1 > $maxsize){$error="File too big, please try again.";}
	else if(strlen($filename1) < 1){$error="Please choose a file to upload";}
	else{$error="";}
	$upload_ok = ($error!="")?0:1;
	if ($upload_ok == 1)
	{
		$target_path1 = $target_path . $newname . "." . $extension1;
		if ($newname)
		{	
			if(move_uploaded_file($tmp_name, $target_path1)) 
			{ 
				if($page=="cart_variantgroups")
				{
					copy($target_path1,$target_path."/small/" . $newname);
					cart_resizeimg($target_path."/small/".$newname,$size1,$extension1,$images_arr['variants']['images']['small']);
				}
				chmod($target_path1, 0755); 
				return $target_path1;
				//header('Location: ../index.php?p=main&sub=lechameau&con=news');
			}
			else 
			{ 
				return "There was an error uploading the file, please try again!"; 
			}	
		}	
		else 
		{ 
			return "New filename not specified, please try again!"; 
		}	
	}
	else
	{
		return $error;
	}
}

function cart_imgdimensions($maxwidth,$maxheight,$file_tmp)
{
	/*LIST THE WIDTH AND HEIGHT AND KEEP THE HEIGHT RATIO*/
	list($width, $height) = getimagesize($file_tmp);
	
	/*CALCULATE THE IMAGE RATIO*/
	if($width>$height)
	{
		$imgratio=$width/$height;
		$newwidth = ($imgratio>1)?$maxwidth:$maxwidth*$imgratio;
		$newheight = ($imgratio>1)?$maxwidth/$imgratio:$maxwidth;
	}
	else
	{
		$imgratio=$height/$width;
		$newwidth = ($imgratio>1)?$maxwidth:$maxwidth/$imgratio;
		$newheight = ($imgratio>1)?$maxwidth*$imgratio:$maxwidth;
	}
	
	
	/*SIZE DOWN AGAIN TO KEEP WITHIN HEIGHT CONTRAINT*/
	if($newheight>$maxheight)
	{
		if($newwidth>$newheight)
		{
			$imgratio=$newwidth/$newheight;
			$newheight = ($imgratio>1)?$maxheight:$maxheight/$imgratio;
			$newwidth = ($imgratio>1)?$maxheight*$imgratio:$maxheight;
		}
		else
		{
			$imgratio=$newheight/$newwidth;
			$newheight = ($imgratio>1)?$maxheight:$maxheight*$imgratio;
			$newwidth = ($imgratio>1)?$maxheight/$imgratio:$maxheight;
		}
	}
	$newheight=round($newheight);
	$newwidth=round($newwidth);
	return array($newwidth,$newheight);
}	
function cart_resizeimg($file_tmp,$file_size,$file_ext,$imgsize)
{
	$err="";
	$spo=explode("x",$imgsize);
	$maxwidth=$spo[0];
	$maxheight=$spo[1];
	
	if($file_size)
	{
		if($file_ext=="jpeg"||$file_ext=="jpg"){$new_img = imagecreatefromjpeg($file_tmp);}
		elseif($file_ext=="png"){$new_img = imagecreatefrompng($file_tmp);}
		elseif($file_ext=="gif"){$new_img = imagecreatefromgif($file_tmp);}
		
		$newdims=cart_imgdimensions($maxwidth,$maxheight,$file_tmp);
		$newheight=$newdims[0];
		$newwidth=$newdims[1];
		
		/*CHECK FUNCTION FOR RESIZE IMAGE.*/
		if (function_exists(imagecreatetruecolor)){
			$resized_img = imagecreatetruecolor($newwidth,$newheight);
		}
		else
		{
			$err="Could not resize image, do you have GD library ver 2+?";
		}
		
		/*DO THE RESIZE*/
		imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		/*SAVE THE IMAGE*/
		ImageJpeg ($resized_img,$file_tmp);
		ImageDestroy ($resized_img);
		ImageDestroy ($new_img);
	}
}
function cart_getparents($cid,$seperator="",$link="")
{
	global $db1;
	$parent="";
	if($cid!=""&&$cid!=0)
	{
		/*$pquery="SELECT * FROM ".CTABLE." as c,fusion as f WHERE `itemId`='$cid' AND f.`itemId`=c.".CFIELDID." AND `itemType`='category' AND `ownerType`='category' LIMIT 1";
		$parents_query=ysql_query($pquery,CARTDB);
		$parents=mysql_fetch_assoc($parents_query);
		$parents_num=mysql_num_rows($parents_query);*/
		$pquery="SELECT * FROM ".CTABLE." as c,fusion as f WHERE `itemId`=? AND f.`itemId`=c.".CFIELDID." AND `itemType`='category' AND `ownerType`='category' LIMIT 1";
		$parents_query=$db1->prepare($pquery);
		$parents_query->execute(array($cid));
		$parents=$parents_query->fetch(PDO::FETCH_ASSOC);
		$parents_num=$parents_query->rowCount();
		if($parents_num>0)
		{		
			if($parents['ownerId']!=0){$parent.=cart_getparents($parents['ownerId'],$seperator,$link);}//recursively get parents
				$parent.=$seperator;
				if(strlen($link)>0){$parent.="<a href='$link";$parent.=$parents[CFIELDID];$parent.="'>";}
				$parent.=ucwords($parents[CFIELDNAME]);
				if(strlen($link)>0){$parent.="</a>";}
		}
	}
	return $parent;
}
function cart_gettopcat($childcat)
{
	global $db1;
	/*$tops=ysql_query("SELECT c.`".CFIELDID."`,f.`ownerId`,c.`".CFIELDNAME."` FROM ".CTABLE." as c JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND f.`ownerType`='category' WHERE f.`itemId`='$childcat' AND f.`itemType`='category'",CARTDB);
	$top=mysql_fetch_row($tops);*/
	$tops=$db1->prepare("SELECT c.`".CFIELDID."`,f.`ownerId`,c.`".CFIELDNAME."` FROM ".CTABLE." as c JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND f.`ownerType`='category' WHERE f.`itemId`=? AND f.`itemType`='category'");
	$tops->execute(array($childcat));
	$top=$tops->fetch(PDO::FETCH_NUM);
	if($top[1]>0)
	{
		$return=cart_gettopcat($top[1]);
	}
	else
	{
		$return=array($top[0],$top[2]);
	}
	return $return;
}
function cart_mysql_real_extracted($x)
{
	global $db1;
	$e=array();
	foreach($x as $f => $v)
	{
		if(!is_array($v)&&is_numeric($v)&&stristr($v,".")===false&&stristr($f,"phone")===false&&stristr($f,"tel")===false){$v=intval($v);}
		//$e[$f]=is_array($v)?cart_mysql_real_extracted($v):mysql_real_escape_string(stripslashes(str_replace("\\r\\n","",$v)));
		$e[$f]=is_array($v)?cart_mysql_real_extracted($v):stripslashes(str_replace("\\r\\n","",$v));
	}
	return $e;
}
$prods_num=0;
function cart_pagenums($query,$inurl,$perpage,$maxpglinks,$forceseltype='',$binds=array())
{
	global $get_arr,$prods_num,$db1;
	//$prods_query1=ysql_query($query,CARTDB) or die(sql_error("Error"));
	//$prods_num=mysql_num_rows($prods_query1);
	if(count($binds)>0)
	{
		$prods_query1=$db1->prepare($query);
		$prods_query1->execute($binds);
	}
	else
	{
		$prods_query1=$db1->query($query);
	}
	$prods_num=$prods_query1->rowCount();
	$pgnum=(isset($get_arr['page'])&&$get_arr['page']>0)?intval($get_arr['page']):1;
	$pgstart = ($pgnum > 0 && (($pgnum-1)*$perpage) <= $prods_num) ? (($pgnum-1)*$perpage) : 0;
	$pgend = ($pgstart+$perpage >= $prods_num) ? $prods_num : $pgstart+$perpage;
	if($prods_num > $perpage)
	{
		$totalpages = ceil($prods_num/$perpage);//raw pages
		$seltype = strlen($forceseltype)>0?$forceseltype:($totalpages > ($maxpglinks*2) ? 1 : 0);
		$backlink = $pgnum > 1&&($seltype==1||($seltype==0&&$maxpglinks<$totalpages)) ? "| &#160;<a href='$inurl&amp;page=".($pgnum-1)."'>BACK</a>" : "";
		$nextlink = $pgnum < $totalpages&&($seltype==1||($seltype==0&&$maxpglinks<$totalpages)) ? "&#160;<a href='$inurl&amp;page=".($pgnum+1)."'>NEXT</a>&#160;|" : "";
		$firstlink =$pgnum > 1&&($seltype==1||($seltype==0&&$maxpglinks<$totalpages)) ? "<a href='$inurl&amp;page=1'>&#171; FIRST</a>" : ($pgnum <= 1&&($seltype==1||($seltype==0&&$maxpglinks<$totalpages))?"<span style='color:#999999'>&#171; FIRST</span>":"");
		$lastlink = $pgnum < $totalpages&&($seltype==1||($seltype==0&&$maxpglinks<$totalpages)) ? "<a href='$inurl&amp;page=".($totalpages)."'>&#160;LAST &#187;</a>" : ($pgnum >= $totalpages&&($seltype==1||($seltype==0&&$maxpglinks<$totalpages))?"<span style='color:#999999'>&#160;LAST &#187;</span>":"");
		$paginationstart = $pgnum > ceil($maxpglinks/2) && !($totalpages < $maxpglinks && $pgnum == $totalpages) ? (($pgnum < $totalpages-floor($maxpglinks/2)) ? $pgnum-($maxpglinks-3) : ($totalpages-$maxpglinks+1)) : 1;		
		$pgnumbers = "";
		if($seltype==0)
		{ 
			for($p=$paginationstart;$p<=$totalpages && $p < $paginationstart+$maxpglinks;$p++)
			{
				if($p == $pgnum){
					$pgnumbers.="<span class='pagelinkon'>$p</span>";
				}else{
					$pgnumbers.="<a href='$inurl&amp;page=$p' class='pagelink'>$p</a>";
				}
			}
		}
		else
		{
			$pgnumbers="(Page <form action='$inurl' method='get' name='pageform' class='pageform'><select name='page' onchange='location.href=this.options[selectedIndex].value'>";
			for($p=1;$p<=$totalpages;$p++)
			{
				$ss=($p == $pgnum)?"selected='selected'":"";
				$pgnumbers.="<option value='".$inurl."&amp;page=".$p."' $ss>$p</option>";
			}
			$pgnumbers.="</select></form> of ".$totalpages.")";
		}
		$pagesdisplay="<div class='pagination'>";
		if($seltype==0){$pagesdisplay.="<span class='desc'>$totalpages PAGES:</span> "; }
		$pagesdisplay.="$firstlink $backlink $pgnumbers $nextlink $lastlink</div>";
		if(basename(dirname($_SERVER['PHP_SELF']))=="admin"){
			$pagesdisplay.="<div class='paginationshowing'>Showing: ".($pgstart+1)." to $pgend of $prods_num</div>";
		}
		$pagesdisplay.="<div class='clear'></div>";
	}
	if(!isset($pagesdisplay)||strlen($pagesdisplay)<1){$pagesdisplay="";}
	$toreturn=array($query." LIMIT ".(($pgnum-1)*$perpage).",$perpage",$pagesdisplay,$prods_num);
	return $toreturn;
}
function cart_get_country($code)
{
	global $db1;
	$country="Unknown Country";
	/*$countryq=@ysql_query("SELECT `countryname` FROM cart_countries WHERE `country_id`='$code'",CARTDB);
	list($country)=@mysql_fetch_row($countryq);*/
	$countryq=$db1->prepare("SELECT `countryname` FROM cart_countries WHERE `country_id`=?");
	$countryq->execute(array($code));
	list($country)=$countryq->fetch(PDO::FETCH_NUM);
	if(strlen($country)==0&&$code=="GB"){$country="United Kingdom";}
	return $country;
}
function cart_get_county($code)
{
	global $db1;
	$county="Unknown County";
	/*$countyq=@ysql_query("SELECT `countyname` FROM cart_counties WHERE `county_id`='$code'",CARTDB);
	list($county)=@mysql_fetch_row($countyq);*/
	$countyq=$db1->prepare("SELECT `countyname` FROM cart_counties WHERE `county_id`=?");
	$countyq->execute(array($code));
	list($county)=$countyq->fetch(PDO::FETCH_NUM);
	return $county;
}
function cart_get_variant_qty($variants)
{
	global $db1;
	$vquantity=array();
	foreach($variants as $variant)
	{
		$vars=explode("-v-",$variant);
		//$nav_stock=ysql_query("SELECT `nav_qty` FROM nav_stock WHERE `nav_sku`='$vars[0]' AND `nav_variant`='$vars[1]'",CARTDB);
		//list($qty)=mysql_fetch_row($nav_stock);
		$nav_stock=$db1->prepare("SELECT `nav_qty` FROM nav_stock WHERE `nav_sku`=? AND `nav_variant`=?");
		$nav_stock->execute(array($vars[0],$vars[1]));
		list($qty)=$nav_stock->fetch();
		$vquantity[$variant]=$qty;
	}
	return $vquantity;
}

function cart_colourchooser($prodid,$qty_per_item,$totalitems,$omit,$nochanger="",$arrayprodid="",$table="")
{
	global $images_arr,$page,$extraimg,$stocklimit,$the_array,$db1;
	$table=strlen($table)<1?"`cart_orderproducts`":$table;
	$match1=$table=="`cart_orderproducts`"?"CONCAT(op.`sku`,'-v-',op.`variant_id`)":"op.`okit_skuvar`";
	$match2=$table=="`cart_orderproducts`"?"`order_prod_id`":"`okit_id`";
	$skuvarname=$arrayprodid==""?"skuvariant[$prodid]":"skuvariant[$arrayprodid][$prodid]";
	$extraimg=array();
	//$opts_q=ysql_query("SELECT * FROM product_options,nav_stock WHERE product_options.variant_id=nav_stock.nav_skuvar AND nav_qty > 0 AND prod_id='$prodid' ORDER BY prod_opt_id");
	$option="";
	$ovalid="";
	$selected="none";
	$adinv=$page=="cart_invoices"?1:0;
	$binds=array();
	$binds[]=$prodid;
	if($adinv)//viewing invoice from ACP
	{
		$selected="this";
		/*$selectedoptq=ysql_query("SELECT * FROM $table as op LEFT JOIN cart_variants as v ON $match1=v.`vskuvar` WHERE $match2='$omit'",CARTDB);
		$selopt=mysql_fetch_assoc($selectedoptq);*/
		$selectedoptq=$db1->prepare("SELECT * FROM $table as op LEFT JOIN cart_variants as v ON $match1=v.`vskuvar` WHERE $match2=?");
		$selectedoptq->execute(array($omit));
		$selopt=$selectedoptq->fetch(PDO::FETCH_ASSOC);
		$ovalid="AND `vid`!=?";
		$binds[]=$selopt['vid'];
		$chosencolor=$selopt['oitem'];
		$option="<option value='$omit' selected='selected'>$chosencolor (Current Choice)</option>";
	}
	/*$qqq="SELECT p.`".PFIELDID."` as prod_id,ov.`vname` as item,`nav_qty`,`nav_skuvar`,ov.`vid` as vid,p.`".PFIELDID."` as optid,`vimg`,ov.`price` FROM 
	(((".PTABLE." as p JOIN cart_fusion as fo ON p.`".PFIELDID."`=fo.`pid`) JOIN cart_variants as ov ON p.`".PFIELDID."`=ov.`pid`) JOIN nav_stock as n ON ov.`vskuvar`=n.`nav_skuvar`)
	 WHERE `nav_qty` > '0' AND p.`".PFIELDID."`='$prodid' $ovalid ORDER BY `order` ASC,`price` ASC";
	//echo $qqq;
	$opts_q=ysql_query($qqq,CARTDB)or die(sql_error("Error","$qqq<br />".mysql_error()));
	$opt=mysql_fetch_assoc($opts_q);
	$opts_num=mysql_num_rows($opts_q);*/
	$qqq="SELECT p.`".PFIELDID."` as prod_id,ov.`vname` as item,`nav_qty`,`nav_skuvar`,ov.`vid` as vid,p.`".PFIELDID."` as optid,`vimg`,ov.`price` FROM 
	(((".PTABLE." as p JOIN cart_fusion as fo ON p.`".PFIELDID."`=fo.`pid`) JOIN cart_variants as ov ON p.`".PFIELDID."`=ov.`pid`) JOIN nav_stock as n ON ov.`vskuvar`=n.`nav_skuvar`)
	 WHERE `nav_qty` > '0' AND p.`".PFIELDID."`=? $ovalid ORDER BY `order` ASC,`price` ASC";
	//echo $qqq;
	$opts_q=$db1->prepare($qqq);
	$opts_q->execute($binds);
	//$opt=$opts_q->fetch(PDO::FETCH_ASSOC);/* doesn't seem needed here */
	$opts_num=$opts_q->rowCount();
	if($opts_num>0)
	{
		?>
		<?php if($totalitems==1){?>
			<label for="skuvariant<?=$prodid?>">Variation</label><?=(($adinv)?" ":"<br />")?>
		<?php }
		
		if($adinv){?><input type="hidden" name="popttoorderopt[<?=$prodid?>]" value="<?=$omit?>" /><?php }?>
		<span class="hidefromprint">
		<select name="<?=$skuvarname?>" id="skuvariant<?=$prodid?>" class="formfield"<?php if(0/*$adinv==0&&$page=="cart_products"&&$nochanger==""*/){?> onchange="javascript:swapimage('thumbnail',this.options[this.selectedIndex].className)"<?php }?>>
		<?php if(!$adinv){?><option value="" class="<?=$images_arr['product']['path'].$prodid."/small.jpg"?>" selected="selected">-- Select Option --</option><?php }?>
		<?=$option?>
		<?php
		//mysql_data_seek($opts_q,0);
		//while($opt=mysql_fetch_assoc($opts_q))
		while($opt=$opts_q->fetch(PDO::FETCH_ASSOC))
		{
			$price=$opt['price']-cart_getdiscount($opt['price'],$the_array['salediscount'],$the_array['saletype']);
			if(floor($opt['nav_qty']/$qty_per_item)>=1)
			{
				$item=str_replace(array("(",")"),"",$opt['item']);
				$stripos=stripos($item,"BER");
				if(!$stripos){$stripos=stripos($item,"60");}
				$nums[0]=($stripos)?substr($item,0,$stripos):$item;
				$color=trim($nums[0]);
				$img=$images_arr['variants']['path'].$opt['optid']."/".$opt['vimg']."-t-main.jpg";
				$img2=$images_arr['variants']['path'].$opt['optid']."/".$opt['vimg']."-t-prod.jpg";
				?><option class="<?=$img2?>" value="<?=$opt['nav_skuvar']."-qty-".$qty_per_item?>" style="background:url(<?=$img?>) no-repeat -25px -10px;padding: 2px 0 2px 23px;margin-bottom:1px;"><?=str_replace("-v-NONE","",$opt['nav_skuvar'])." ".$color?> &#163;<?=number_format($price,2)?> (<?=floor($opt['nav_qty']/$qty_per_item)>$stocklimit?"in":"low"?> stock)</option><?php
			}
		}
		?>
		</select></span><span class="hidefromweb"><?=$chosencolor?></span>
		<?php if($totalitems==1){?><br /><?=(($adinv)?"":"<br />")?><?php }else{?>&#160;<?php }?>
		<?php 
	}
	else if($adinv)
	{
		?>
		<label for="skuvariant<?=$prodid?>">Variation</label> 
		<select name="<?=$skuvarname?>" id="skuvariant<?=$prodid?>" class="formfield">
		<?=$option?>
		</select>
		<?php 
	}
	else//if no option associated so set default
	{
		/*$sq=ysql_query("SELECT `nav_skuvar` FROM cart_variants as v,nav_stock as n WHERE v.`vskuvar`=n.`nav_skuvar` AND v.`pid`='$prodid' AND `nav_qty`>'0' ORDER BY `nav_skuvar` ASC",CARTDB);echo mysql_error();
		list($prodsku)=mysql_fetch_row($sq);*/
		$sq=$db1->prepare("SELECT `nav_skuvar` FROM cart_variants as v,nav_stock as n WHERE v.`vskuvar`=n.`nav_skuvar` AND v.`pid`=? AND `nav_qty`>'0' ORDER BY `nav_skuvar` ASC");
		$sq->execute(array($prodid));
		list($prodsku)=$sq->fetch(PDO::FETCH_NUM);
		?><input type="hidden" name="<?=$skuvarname?>" value="<?=$prodsku?>-qty-<?=$qty_per_item?>" /><?php
	}
}
function cart_postage_expired($stamp)
{
	$expired=((date("w")>date("w",$stamp) || (date("w")>=date("w",$stamp) && date("A")>=date("A",$stamp) && date("H")>=date("H",$stamp) && date("i")>=date("i",$stamp))))?1:0;
	return $expired;
}
function cart_ordercontents($where,$width,$binds=array())
{
	global $page,$db1;
?>
<table style="width:<?=$width?>" class="details">
<?php if($page=="cart_invoices"){?>

		<tr class="head">
			<td colspan="4">Order Contents</td>
		</tr>
	<?php }?>
	<tr class="subhead">
		<td style="text-align:center">Quantity</td>
		<td>Product</td>
		<td style="text-align:right">Price (inc. VAT)</td>
		<td style="text-align:right">Sub Total</td>
	</tr>
	<?php 
	$runtotal=0;
	$removefromdiscount=0;
	$discount=0;
	$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount`,f.`ownerId` as own FROM cart_orders as o,cart_orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`itemId` AND `itemType`='product' WHERE $where AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";
	/*
	$orderq=ysql_query($sstring,CARTDB);
	while($order=mysql_fetch_assoc($orderq))*/	
	if(count($binds)>0)
	{
		$orderq=$db1->prepare($sstring);
		$orderq->execute($binds);
	}
	else
	{
		$orderq=$db1->query($sstring);
	}
	
	while($order=$orderq->fetch(PDO::FETCH_ASSOC))
	{
		$iQuantity=$order['qty'];
		$iProductId=$order['prod_id'];
		/*$orderkitq=ysql_query("SELECT `fusionId`,`kit_title`,`item_qty`,`oitem`,`prod_id`,`okit_id` FROM cart_orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`itemId` AND `itemType`='product' WHERE `order_prod_id`='$order[order_prod_id]' GROUP BY `okit_id`",CARTDB);
		$ispack=mysql_num_rows($orderkitq);*/
		$orderkitq=$db1->prepare("SELECT `fusionId`,`kit_title`,`item_qty`,`oitem`,`prod_id`,`okit_id` FROM cart_orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`itemId` AND `itemType`='product' WHERE `order_prod_id`=? GROUP BY `okit_id`");
		$orderkitq->execute(array($order['order_prod_id']));
		$ispack=$orderkitq->rowCount();
		$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
		$plink=$order['prod_id']=='358'?"./warranty":($adinv?"index.php?p=".ADMINPRODUCTS."&amp;pid=".$order['fusionId']."&amp;curpage=".urlencode($order['title']):"./shop/item/".$order['fusionId']);
		?>
		<tr class="<?=$row_class?>">
			<td style="vertical-align:top;text-align:center"><?php if($page=="cart_invoices"&&$iProductId!='358'){?>
			<input type="hidden" name="price[<?=$order['order_prod_id']?>]" value="<?=$order['price']?>" />
			<input type="text" name="qty[<?=$order['order_prod_id']?>]" value="<?php }?><?=$order['qty']?><?php if($page=="cart_invoices"&&$iProductId!='358'){?>" class="input_text_small" style="text-align:center" /><?php }?><?php if($order['exclude_discount']==1&&$order['discount_code']!="WARRANTYTIM"){?><br /><dfn style="font-size:90%;font-style:italic;color:#CD071E">Discount exempt</dfn><?php }?></td>
			<td style="vertical-align:top"><a href="<?=$plink?>"><?=$order['title']?><?=strlen($order['sku'])>0?" (".$order['sku'].")":""?></a><br /><?php if($order['prod_id']=='358'){?>Details:<br /><?=$order['oname']?><br /><?php }?>
				<?php if($ispack==0){?>
					<?php if($page=="cart_invoices")
					{
						cart_colourchooser($iProductId,$iQuantity,1,$order['order_prod_id']);
					}
					else
					{
						echo ucwords("Variation: ".$order['oitem']);
					}
				}else{?>
					<div class="pack_contents"> <strong>Pack Contents</strong><br />
						<?php 
						//while($orderkit=mysql_fetch_assoc($orderkitq))
						while($orderkit=$orderkitq->fetch(PDO::FETCH_ASSOC))
						{
							?>
							<a href="<?=MAINBASE?>/shop/item/<?=$orderkit['fusionId']?>"><?=$orderkit['kit_title']?></a> (<?=$orderkit['item_qty']?>)<br />
							<?php
							if($page=="cart_invoices")
							{
								cart_colourchooser($orderkit['prod_id'],1,1,$orderkit['okit_id'],$nochanger="",$arrayprodid="","cart_orderkits");
							}
							else
							{
								echo ucwords("Variation: ".$orderkit['oitem']);
							}
							?>
							<br />
							<?php 
						}?>
					</div>
				<?php }?>
			</td>
			<td style="vertical-align:top;text-align:right"><span class="price">&#163;
				<?=number_format($order['price'],2)?>
				</span></td>
			<td style="vertical-align:top;text-align:right"><span class="price">&#163;
				<?=number_format($order['price']*$order['qty'],2)?>
				</span></td>
		</tr>
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
	}
	//$discount=(($runtotal-$removefromdiscount)/100)*$odiscount;
	?><?php $row_class=$row_class=="row_light"?"row_dark":"row_light"; ?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right"><strong>Sub Total:</strong></td>
		<td style="text-align:right;"><span class="price">&#163;<?=number_format($runtotal,2)?></span></td>
	</tr>
	<?php if(strlen($odiscountcode)>0&&$odiscountcode!="discount code"){?>
	<?php $row_class=$row_class=="row_light"?"row_dark":"row_light"; ?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right"><strong><?=$odiscount?>% Discount (<?=$odiscountcode?>):</strong></td>
		<td style="text-align:right"><span class="price">- &#163;<?=number_format($discount,2)?></span></td>
	</tr>
	<?php }?>
	<?php $row_class=$row_class=="row_light"?"row_dark":"row_light"; ?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right"><strong>Postage (<?=htmlentities($oshipdesc,ENT_QUOTES,"UTF-8")?>):</strong></td>
		<td style="text-align:right"><span class="price">&#163;<?=number_format($oshiptotal,2)?></span></td>
	</tr>
	<?php $row_class=$row_class=="row_light"?"row_dark":"row_light"; ?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right"><strong>Total:</strong></td>
		<td style="text-align:right"><span class="price">&#163;<?=number_format($ototalprice,2)?></span></td>
	</tr>
</table>
	<div class="vatline" style="width:<?=$width?>px">Total includes VAT (@<?=$otaxrate?>%) of &#163;<?=$otaxprice?></div>
<?php
}

function cart_contents($showremove)
{
	global $basket_total, $defaultpostage, $vat, $vattoadd, $sub_total, $defaultpostdesc, $discount, $cart_path,$db1;
	$discountinfo="";
	if(isset($_SESSION['discount_code'])){$discountinfo=cart_discount_check($_SESSION['discount_code'],$sub_total);}
	
	?>
	<table>
	<tr class="head"><td colspan="<?=$showremove==1?5:4?>"><div class="titles">Basket Contents</div></td></tr>
	<tr class="subhead">
		<td>Quantity</td>
		<td>Product</td>
		<td style="text-align:right">Price</td>
		<td style="text-align:right">Total</td>
		<?php if($showremove==1){?><td style="text-align:center">Remove</td><?php }?>
	</tr>
	<?php 
	foreach($_SESSION['cart'] as $id => $cart)//each cart prod
	{
		/*code to add message telling customer or additional product removals*/
		$rcart_ids=array();
		$showmsg="";
		foreach($_SESSION['cart'] as $rid => $rcartitems){
			if(!in_array($rcartitems['prod_id'],$rcart_ids)&&$rid!=$id){array_push($rcart_ids,$rcartitems['prod_id']);}
		}
		$msgbits=array();
		foreach($_SESSION['cart'] as $ccid => $cartitems)
		{
			$allowedmatches=!is_array($cartitems['allowlist'])||count($cartitems['allowlist'])<1?1:count(array_intersect($cartitems['allowlist'],$rcart_ids));
			if($allowedmatches<1){$msgbits[]=$cartitems['title'];}
		}
		if(count($msgbits)>0){$showmsg.=implode(" & ",$msgbits);}
		if(strlen($showmsg)>1&&count($msgbits)>0){$showmsg.=" will also be removed as ".(count($msgbits)>1?"they are":"it is")." available only in conjunction with certain products.";}
		/*code to add message telling customer or additional product removals*/
		$skuvars="";
		foreach($cart['skuvariant'] as $ident => $newsku)
		{
			$expsku=explode("-qty-",$newsku);
			$skuvars.=($skuvars!=""?",":"")."".$expsku[0]."";
		}
		$query=$cart['ispack']==1?"SELECT p.`".PFIELDNAME."` as title,cv.`vskuvar` as sku, f.`fusionId` as fusionId FROM ((".PTABLE." as p JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' WHERE p.`".PFIELDID."`=?":"SELECT p.`".PFIELDNAME."` as title,cv.`vskuvar` as sku, f.`fusionId` as fusionId FROM ((".PTABLE." as p JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' WHERE p.`".PFIELDID."`=?";
		
		/*$prodinfoq=ysql_query($query,CARTDB);
		$prodinfo=mysql_fetch_assoc($prodinfoq);*/
		$prodinfoq=$db1->prepare($query);
		$prodinfoq->execute(array($cart['prod_id']));
		$prodinfo=$prodinfoq->fetch(PDO::FETCH_ASSOC);
		//print_r($prodinfo);
		$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
		$plink=$cart['prod_id']=='358'?"./warranty":$cart['rlink'];
		?>
		<tr class="<?=$row_class?>">
			<td style="vertical-align:top"><?php if($showremove==1&&$cart['prod_id']!='358'){?>Quantity <input type="text" name="qty[<?=$id?>]" value="<?=$cart['qty']?>" style="width:20px;" class="formfield" /><?php }else{echo $cart['qty']; }?><?php if($cart['exclude_discount']==1){?><br /><dfn style="font-size:90%;font-style:italic;color:#CD071E">Discount exempt</dfn><?php }?></td>
			<td style="vertical-align:top">
				<a href="<?=$plink?>"><?=$prodinfo['title']?></a><?=isset($cart['serial'])&&strlen($cart['serial'])>0?" (Serial: ".$cart['serial'].")":""?>
				<?php if($cart['ispack']==0){$choice=cart_variants($skuvars);?>
					<br /><?=is_array($choice)?ucwords("Variation: ".$choice['vname'])." (".str_replace("-v-NONE","",$choice['vskuvar']).")":""?>
				<?php }else{?>
				<div class="pack_contents">
					<strong>Pack Contents</strong><br />
					<?php foreach($cart['skuvariant'] as $prod_id => $skuvar)
					{
						$expsku=explode("-qty-",$skuvar);
						/*$packq=ysql_query("SELECT `fusionId`,p.`".PFIELDNAME."` as title,`qty` FROM (".PTABLE." as p JOIN cart_kits as pk ON pk.`itemId`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' WHERE p.`".PFIELDID."`='$prod_id' AND pk.`ownerId`='$cart[prod_id]'",CARTDB);
						$pack=mysql_fetch_assoc($packq);*/
						$packq=$db1->prepare("SELECT `fusionId`,p.`".PFIELDNAME."` as title,`qty` FROM (".PTABLE." as p JOIN cart_kits as pk ON pk.`itemId`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' WHERE p.`".PFIELDID."`=? AND pk.`ownerId`=?");
						$packq->execute(array($prod_id,$cart['prod_id']));
						$pack=$packq->fetch(PDO::FETCH_ASSOC);
						$pchoice=cart_variants("'".$expsku[0]."'");
						?>
						<?php if(strlen($pack['fusionId'])>0){?><a href="./shop/item/<?=$pack['fusionId']?>"><?php }?><?=$pack['title']?><?php if(strlen($pack['fusionId'])>0){?></a><?php }?> (<?=$pack['qty']?>)<br /><?=is_array($pchoice)?ucwords("Variation: ".$pchoice['vname']):""?><br />
						<?php 
					}?>
				</div>
				<?php }?>
			</td>
			<td style="vertical-align:top;text-align:right"><span class="price">&#163;<?=number_format($cart['price'],2)?></span></td>
			<td style="vertical-align:top;text-align:right"><span class="price">&#163;<?=number_format($cart['price']*$cart['qty'],2)?></span></td>
			<?php if($showremove==1){?><td style="vertical-align:top;text-align:center"><a href="./cart_basket&amp;remove_item=<?=$id?>" id="<?=$id?>"><img src="<?=$cart_path?>/images/remove_item.bmp" alt="Remove" /></a>
			<script type="text/javascript">
			//<[CDATA[
			$('#<?=$id?>').click(function() {
				if(confirm('Are you sure you want to delete <?=$prodinfo['title'].(is_array($choice)?" - ".ucwords("Variation: ".$choice['vname']):"")?>?<?php if(strlen($showmsg)>0){?>\r\n\r\n<?=$showmsg?><?php }?>')) return true;else return false;
			}
			)
			//]]>
			</script>
				</td><?php }?>
		</tr>
		<?php
	}
	$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
	?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right;"><strong>Sub-Total</strong></td>
		<td style="text-align:right;"><span class="price">&#163;<?=number_format($sub_total,2)?></span></td>
		<?php if($showremove==1){?><td>&#160;</td><?php }?>
	</tr>
	<?php if(isset($_SESSION['discount_code'])&&strlen($_SESSION['discount_code'])>0)
	{
		$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";?>
		<tr class="<?=$row_class?>">
			<td colspan="3" style="text-align:right"><strong><?=$_SESSION['discount_amount']?>% Discount (<?=$_SESSION['discount_code']?>)</strong></td>
			<td style="text-align:right"><span class="price">- &#163;<?=number_format($discount,2)?></span></td>
			<?php if($showremove==1){?><td>&#160;</td><?php }?>
		</tr>
		<?php 
	}
	else if(strlen($discountinfo)>0)
	{
		$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";?>
		<tr class="<?=$row_class?>">
			<td colspan="3" style="text-align:right"><strong><?=str_replace("<br />","",$discountinfo)?></strong></td>
			<td style="text-align:right"></td>
			<?php if($showremove==1){?><td>&#160;</td><?php }?>
		</tr>
		<?php 
	}	
	$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
	//$postwords=;
	?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right"><strong>Postage Method (<?=htmlspecialchars($_SESSION['postdesc'],ENT_QUOTES,"UTF-8")?>)</strong></td>
		<td style="text-align:right"><span class="price">&#163;<?=number_format(cart_postagecalc($sub_total,$_SESSION['shipping']),2)?></span></td> 
		<?php if($showremove==1){?><td>&#160;</td><?php }?>
	</tr>
	<?php $row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";?>
	<tr class="<?=$row_class?>">
		<td colspan="3" style="text-align:right"><strong>Current Total</strong></td>
		<td style="text-align:right"><span class="price">&#163;<?=number_format($basket_total,2)?></span></td>
		<?php if($showremove==1){?><td>&#160;</td><?php }?>
	</tr>
	</table>
	<?php $vatstuff=cart_getvat($sub_total);?>
	<div style="text-align:right">Current Total includes VAT (@<?=VAT?>%) of &#163;<?=$vatstuff[1]?></div>
	<?php
}
function cart_postapplicable()
{
	if(isset($_SESSION['cart']))
	{	
		$ret=0;
		foreach($_SESSION['cart'] as $cc => $ar)
		{
			if(!isset($ar['freeship'])||$ar['freeship']==0){$ret=1;}
		}
	}else{$ret=1;}
	return $ret;
}
function cart_discount_check($code,$sub_total)
{
	global $_SESSION,$db1;
	/*$checkdiscount=ysql_query("SELECT * FROM cart_discounts WHERE `code`='".$code."' AND `state`='1'");
	$thediscount=mysql_fetch_assoc($checkdiscount);
	$numdiscount=mysql_num_rows($checkdiscount);*/
	$checkdiscount=$db1->prepare("SELECT * FROM cart_discounts WHERE `code`=? AND `state`='1'");
	$checkdiscount->execute(array($code));
	$thediscount=$checkdiscount->fetch(PDO::FETCH_ASSOC);
	$numdiscount=$checkdiscount->rowCount();
	if($numdiscount>0)
	{
		if($thediscount['date_start']<=date("U")&&$thediscount['date_end']>date("U"))
		{
			if($sub_total>=$thediscount['mintotal'])
			{				
				$list=$thediscount['uselist']==1&&strlen($thediscount['prodlist'])>0?explode(",",$thediscount['prodlist']):array();
				if(count($list)>0)
				{
					$okprods=array();
					foreach($_SESSION['cart'] as $im => $item)
					{
						if(in_array($item['prod_id'],$list))
						{	
							$okprods[]=$item['prod_id'];		
						}
					}
				}
				if(count($okprods)>0||count($list)<1)
				{
					$_SESSION['discount_code']=$code;
					$_SESSION['discount_amount']=$thediscount['discount'];
					$_SESSION['discount_list']=$list;	
				}
				else
				{
					unset_discount();		
					return "<br /><span style='color:red;'>Sorry, this code is not valid for your basket.</span>";
				}
			}
			else
			{
				unset_discount();		
				return "<br /><span style='color:red;'>Sorry, this code is valid on orders of &#163;".$thediscount['mintotal']." and above.</span>";
			}
		}
		else
		{
			unset_discount();
			return "<br /><span style='color:red;'>Sorry, this code ($code) has expired.</span>";//code outside of dates
		}
	}
	else 
	{		
		unset_discount();
		if(strlen($code)>0)
		{
			return "<br /><span style='color:red;'>Sorry, this code ($code) is invalid.</span>";//invalid code
		}
		else
		{
			return "<br /><span style='color:red;'>Please enter a valid code.</span>";//invalid code
		}
	}
}
function unset_discount()
{
	if(isset($_SESSION['discount_code']))
	{
		unset($_SESSION['discount_code']);
		unset($_SESSION['discount_amount']);
		unset($_SESSION['discount_list']);
	}
}
function cart_postagecalc($cost,$posttype)//function needs to be copied to payment.php
{	
	global $vat,$db1;
	$amount=number_format($cost+($vat*($cost/100)),2,".","");
	
	//$posttype 0 = free post
	$return=0;
	/*$string="SELECT * FROM cart_postage as cp JOIN cart_postage_details as cpd ON cp.`post_id`=cpd.`post_id` WHERE cpd.`post_details_id`='$posttype'";
	$postq=ysql_query($string,CARTDB) or die(sql_error("Error","Mysql query '$string' failed with error:<br />".mysql_error()));
	$post=mysql_fetch_assoc($postq);*/
	$string="SELECT * FROM cart_postage as cp JOIN cart_postage_details as cpd ON cp.`post_id`=cpd.`post_id` WHERE cpd.`post_details_id`=?";
	$postq=$db1->prepare($string);
	$postq->execute(array($posttype));
	$post=$postq->fetch(PDO::FETCH_ASSOC);
	switch($post['post_id'])
	{
		case 6://special rate
		case 7://free delivery
			$return=$post['field3'];
			if($_SESSION['shipping']==$posttype){$_SESSION['postdesc']=$post['description'];}
			break;
		case 8://by weight
			$return=$post['field3'];
			if($_SESSION['shipping']==$posttype){$_SESSION['postdesc']=$post['description'];}
			break;
		default://range
			/*$q="SELECT * FROM cart_postage as cp JOIN cart_postage_details as cpd ON cp.`post_id`=cpd.`post_id` WHERE cpd.`post_id`='$post[post_id]' AND `field1` <= '$amount' AND (`field2` >= '$amount' || `field2` < '1') ORDER BY `field1` DESC";
			$thepostq=ysql_query($q,CARTDB);			
			$thepost=mysql_fetch_assoc($thepostq);*/
			$q="SELECT * FROM cart_postage as cp JOIN cart_postage_details as cpd ON cp.`post_id`=cpd.`post_id` WHERE cpd.`post_id`=? AND `field1` <= ? AND (`field2` >= ? || `field2` < '1') ORDER BY `field1` DESC";
			$thepostq=$db1->prepare($q);
			$thepostq->execute(array($post['post_id'],$amount,$amount));			
			$thepost=$thepostq->fetch(PDO::FETCH_ASSOC);			
			if($_SESSION['shipping']==$post['post_id']&&strlen($thepost['description'])>0){$_SESSION['postdesc']=$thepost['description'];}
			$return=$thepost['field3'];
			break;
		//(($amount>$defaultpost['field2'])?$defaultpost['field1']:$defaultpost['field3'])
	}
	return $return;
}
function cart_variants($skuvar)
{
	global $db1;
	list($skuvar,$binds)=bindIns($skuvar);
	$str="SELECT `vname`,`vskuvar` FROM cart_variants WHERE `vskuvar` IN($skuvar)";
	$svq=$db1->prepare($str);
	$svq->execute($binds);
	//$svn=$svq->rowCount();
	$sv=$svq->fetch(PDO::FETCH_ASSOC);
	return $sv;
}
function cart_redirection($url)
{
	if (!headers_sent())
		header('Location: '.$url);
	else
	{
		?>
		<script type="text/javascript">window.location.href="<?=$url?>";</script>
		<noscript><meta http-equiv="refresh" content="0;url=<?=$url?>" /></noscript>
		<?php
	}
}
function cart_formrows($details,$required,$selects,$radios,$checkboxes,$match,$formname,$textarea=array())
{
	global $errorlist, $page,$post_arr,$get_arr,$db1;
	$row=0;
	//$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
	foreach($details as $name => $title)
	{
		$col_class=!isset($col_class)||$col_class=="_dark"?"_light":"_dark";
		$matching=is_array($match)?$match[str_replace($formname,"",$name)]:$match;
		$highlight=(array_key_exists($name,$errorlist)||(basename(dirname($_SERVER['PHP_SELF']))=="admin"&&in_array($name,$errorlist)))?" style='border:1px solid red;'":"";
		?>
		<tr>
			<td class="left<?=$col_class?>" <?php if(in_array($name,$textarea)){?>style="vertical-align:top"<?php }?>><?=array_key_exists($name,$radios)||array_key_exists($name,$checkboxes)?$title:"<label for='".$name.$formname."'>$title</label>"?><?php if(in_array(str_replace($formname,"",$name),$required)){?> <span>*</span><?php }?></td>
			<td class="right<?=$col_class?>">
			<?php 
			/* ------- DROPDOWNS ------- */
			if(array_key_exists($name,$selects)){?>
				<select name="<?=$name?>" id="<?=$name.$formname?>"<?=$highlight?>>
				<option value="">Select Below</option>
				<?php 
				$curco=$name=="state"?"":"no";
				/*$query=ysql_query($selects[$name],CARTDB);
				while($result=mysql_fetch_row($query))*/
				$query=$db1->query($selects[$name]);
				while($result=$query->fetch(PDO::FETCH_NUM))
				{
					if($matching=="100"&&str_replace($formname,"",$name)=="country"&&$formname!="editform"){$matching="GB";}
					if($curco!="no"&&isset($result[2])&&$curco!=$result[2])
					{
						if(strlen($curco)>0){?></optgroup><?php }
						$curco=isset($result[2])?$result[2]:"no";
						?><optgroup label="<?=$curco?>"><?php
					}
					?>
					<option value="<?=$result[0]?>" <?=$result[0]==$matching||($name=="state"&&isset($_SESSION['address_details']['billing']['county'])&&$_SESSION['address_details']['billing']['county']==$result[1])||($name=="deliver_state"&&isset($_SESSION['address_details']['delivery']['county'])&&$_SESSION['address_details']['delivery']['county']==$result[1])?"selected='selected'":""?>><?=$result[1]?></option>
					<?php
				}
				if($curco!="no"&&strlen($curco)>0){?></optgroup><?php }
				?>
				</select>
			<?php 
			/* ------- RADIOS ------- */
			}else if(array_key_exists($name,$radios)){
				$radioslist=explode(",",$radios[$name]);
				foreach($radioslist as $rad)
				{
					$radvals=explode(":",$rad);
					?><label for="<?=$name.$radvals[0]?>" <?=in_array($radvals[1],array('On','Yes'))?"class='yes'":(in_array($radvals[1],array('Off','No'))?"class='no'":"")?>><input type="radio" name="<?=$name?>" id="<?=$name.$radvals[0]?>" value="<?=$radvals[0]?>" <?=($matching==$radvals[0])?"checked='checked'":""?><?=$highlight?> /> <?=$radvals[1]?></label><?php 
				}
			/* ------- CHECKBOXES ------- */
			}else if(array_key_exists($name,$checkboxes)){
				$checkboxeslist=explode(",",$checkboxes[$name]);
				foreach($checkboxeslist as $checkbox)
				{
					$checkboxvals=explode(":",$checkbox);
					?>
					<label for="<?=$name.$checkboxvals[0]?>"><input type="checkbox" name="<?=$name?>[]" id="<?=$name.$checkboxvals[0]?>" value="<?=$checkboxvals[0]?>" <?=($matching==$checkboxvals[0])?"checked='checked'":""?><?=$highlight?> /> <?=$checkboxvals[1]?></label> 
					<?php 
				}
			/* ------- INPUTS ------- */
			}else if(in_array($name,$textarea)){
				?><textarea name="<?=$name?>" id="<?=$name.$formname?>" onfocus="this.select()" class="input_text" rows="3" cols="5"<?=$highlight?>><?=$matching?></textarea><?php
			/* ------- BIRTHDATE ------- */
			}else if($name=="dob"){
				$vardob=isset($post_arr[$name])?$post_arr[$name]:(isset($get_arr[$name])?$get_arr[$name]:"");
				?><script type="text/javascript">DateInput('<?=$name?>', true, 'DD/MM/YYYY','<?=strlen($vardob)>0?$vardob:date("d/m/Y")?>')</script>
					<noscript><input type="text" name="<?=$name?>" value="<?=strlen($vardob)>0?date("d/m/Y",$name):date("d/m/Y")?>"<?=$highlight?> /></noscript><?php
			
			}
			/* TITLE */
			/*else if($name=="nametitle"){
				?>
				<select name="<?=$name?>" id="<?=$name.$formname?>"<?=$highlight?>>
					<option value="">Select Below</option>
					<option value="Mr" <?=("Mr"==$matching)?"selected='selected'":""?>>Mr</option>
					<option value="Ms" <?=("Ms"==$matching)?"selected='selected'":""?>>Ms</option>
					<option value="Mrs" <?=("Mrs"==$matching)?"selected='selected'":""?>>Mrs</option>
				</select>
				<?php
			}*/
			/* ------- INPUTS ------- */
			else{?>
				<input type="<?=stristr($name,"pass")?"password":"text"?>" name="<?=$name?>" id="<?=$name.$formname?>" value="<?=$matching?>" onfocus="this.select()" class="input_text"<?=$highlight?> />
			<?php }?>
			</td>
		</tr>
		<?php
		$row++;
	}
}
function cart_stars($rank,$size="")
{
	global $cart_path;
	$floorrank=floor($rank);
	$starsoff=5-$floorrank;
	$starson=5-$starsoff;
	if($rank-$floorrank>0&&$rank-$floorrank<1){$starsoff-=1;}
	for($on=0;$on<$starson;$on++){
	?><span style="font-size:170%;color:#ddd319"><img src="<?=$cart_path?>/images/stars/star<?=$size?>.png" alt="&#9733;" style="vertical-align:middle" /></span><?php
	}if($rank-$floorrank>0&&$rank-$floorrank<1){
	?><span style="font-size:170%;color:#ddd319"><img src="<?=$cart_path?>/images/stars/halfstar<?=$size?>.png" alt="/" style="vertical-align:middle" /></span><?php
	}for($off=0;$off<$starsoff;$off++){
	?><span style="font-size:150%;color:#bbb"><img src="<?=$cart_path?>/images/stars/emptystar<?=$size?>.png" alt="&#9734;" style="vertical-align:middle" /></span><?php 
	}
}
function cart_emptyfieldscheck($postdata,$required)
{
	global $higherr;
	$returntxt="";
	foreach($postdata as $field => $value)
	{
		if(array_key_exists($field,$required))
		{
			if(is_array($value))
			{
				$x=1;
				foreach($value as $id => $arrval)
				{
					/*VALUE EMPTY? - ALERT UNLESS DELTING THIS ITEM*/
					if(strlen($arrval)<1&&$postdata['delete'][$id]!=1)
					{
						$returntxt.=$required[$field]." $x<br />";array_push($higherr,$field."_".$id);
					}
					$x++;
				}
			}
			else
			{
				if(strlen($value)<1){$returntxt.=$required[$field]."<br />";array_push($higherr,$field);}
			}
		}
	}
	return $returntxt;
}
function cart_is_selected($field,$id,$value,$postdata,$type)
{
	$selected=($type=="check")?"checked='checked'":"selected='selected'";
	
	if(isset($postdata[$field])&&is_array($postdata[$field]))
	{
		return (array_key_exists($field,$postdata)&&strtolower($postdata[$field][$id])==strtolower($value))?$selected:"";
	}
	else if(isset($postdata[$field]))
	{
		return (array_key_exists($field,$postdata)&&strtolower($postdata[$field])==strtolower($value))?$selected:"";
	}
}
function cart_highlighterrors($errorarray,$field)
{
	$dohighlight="";
	if(in_array($field,$errorarray)){$dohighlight="style='border:1px solid red;'"; }
	return $dohighlight;
}
function cart_idhighlighterrors($errorarray,$field,$elementid)
{
	if(in_array($field,$errorarray)){foreach($elementid as $cssid){?><style type="text/css">#<?=$cssid?> {border:1px solid red;}</style><?php }}
}
function cart_posted_value($field,$id,$alt,$postdata)
{
	if(isset($postdata[$field])){return is_array($postdata[$field])?$postdata[$field][$id]:$postdata[$field];}
	else{return $alt;}
}
function cart_validemail($emailin,$field)
{	
	global $higherr;
	$returntxt="";
	if(!eregi(EMAILREG, $emailin)){$returntxt.="Please enter a valid email address (eg: user@host.com).<br />";array_push($higherr,$field);}
	return $returntxt;
}
function cart_addvat($price,$hidevat="")
{
	global $vat;
	$hidevat=strlen($hidevat)<1?" inc. vat.":"";
	//return number_format($price+($vat*($price/100)),2).$hidevat;
	return number_format($price,2).$hidevat;
}
function cart_getvat($gross)
{
	$net=number_format($gross/((VAT/100)+1),2,".","");
	$thevat=number_format($gross-$net,2,".","");
	//net = before vat
	return array($net,$thevat);
}
function cart_catlist($query,$binds=array())
{
	global $page, $images_arr,$db1;
	$loop=0;
	/*$cats_query=ysql_query($query,CARTDB)or die(sql_error("Error"));
	$count_cats=mysql_num_rows($cats_query);*/
	if(count($binds)>0)
	{
		$cats_query=$db1->prepare($query);
		$cats_query->execute($binds);
	}
	else
	{
		$cats_query=$db1->query($query);
	}
	$count_cats=$cats_query->rowCount();
	
	if($count_cats>0)
	{ 
		?>
		<h3 class="orangebold">Sub Categories</h3>
		<table id="catthumbs">
		<?php 
		//while($cat=mysql_fetch_assoc($cats_query))
		while($cat=$cats_query->fetch(PDO::FETCH_ASSOC))
		{
			$loop++;
			if($loop%PERROW==1){echo "<tr>";}
			?>
			<td><p><a href="./cart_products&amp;cat=<?=$cat['cat_id']?>&amp;catname=<?=urlencode(strtolower(str_replace(" ","_",$cat['title'])))?>"><img src="<?=$images_arr['department']['path'].$cat['cat_id']?>_thumb.jpg" alt="" /></a><br /><a href="./cart_products&amp;cat=<?=$cat['cat_id']?>&amp;catname=<?=urlencode(strtolower(str_replace(" ","_",$cat['title'])))?>"><?=ucwords($cat['title'])?></a></p></td>
			<?php
			if($loop==$count_cats&&$count_cats%PERROW<PERROW&&$count_cats%PERROW>0&&$count_cats>PERROW){echo "<td colspan='".(PERROW-($count_cats%PERROW))."'>&#160;</td>"; }
			if($loop%PERROW==0||$loop==$count_cats){echo "</tr>";}
			/*if($loop==PERROW&&$page=="home")
			{?><tr><td colspan="<?=PERROW?>"><span class="orangebold">SAME DAY DISPATCH! - on all orders placed before 12pm</span></td></tr><?php }*/
		}
	 
		?></table><?php
	}
}
function cart_prodlist($query,$limit,$header,$class="",$cols="",$binds=array())
{
	global $db1,$parentcat,$images_arr,$page,$pid,$_SESSION,$the_array,$get_arr,$root_to_cart,$fields,$tofilter,$totalprods,$prods_num,$cart_inhouse,$cart_userlocal,$deviceType,$prodmod,$mods;
	$showit=$cart_userlocal!=1&&!$cart_inhouse!=1?1:0;
	$get_page=isset($get_arr['page'])?$get_arr['page']:1;
	$ignore=array("p");
	$qstring="";
	$keytotal=count($_GET)-count($ignore);
	$class=strlen($class)>0?$class:"product";
	$cols=strlen($cols)>0?$cols:2;
	$l=0;
	foreach($get_arr as $key => $val){if(!in_array($key,$ignore)){if($l<=$keytotal){$qstring.="&amp;";}$qstring.=$key."=".$val;}$l++;}//remove ignored keys
	$taklimit=" LIMIT ".intval($limit);
	if($limit=="")
	{
		$takquery=cart_pagenums($query,"index.php?p=cart_products".$qstring,PERPAGE,MAXPGLINKS,'',$binds);
		$final_query=$takquery[0];
	}
	else
	{
		$final_query=$query.$taklimit;
	}
	$products=array("withpic"=>array(),"nopic"=>array());
	
	/*$prods_query=ysql_query($final_query,CARTDB) or die(sql_error("Error","Query error<br />$final_query<br /><br />".mysql_error()));
	$prods_count=mysql_num_rows($prods_query);	
	while($prod=mysql_fetch_assoc($prods_query))*/
	if(count($binds)>0)
	{
	$prods_query=$db1->prepare($final_query);
	$prods_query->execute($binds);
	}
	else
	{
	$prods_query=$db1->query($final_query);
	}
	$prods_count=$prods_query->rowCount();	
	while($prod=$prods_query->fetch(PDO::FETCH_ASSOC))
	{
		if(file_exists($images_arr['product']['path'].$prod['prod_id'].".png"))
		{
			$products["withpic"][]=$prod;
		}
		else
		{
			$products["nopic"][]=$prod;
		}		
	}
	if(!in_array($class,array("offerprods","prodthumbs","lrgthumbs"))&&!in_array($header,array("Most Viewed Products")))
	{?>
	<div id="quicksearchbar">QUICK SEARCH</div>
	<div style="float:left" id="quicksearchmain">
		<div id="sorting"> 
			<form action="index.php" method="get" style="display:inline;vertical-align:middle">
			<input type="hidden" name="p" value="<?=$page?>" />
			<input type="hidden" name="cat" value="<?=$_GET['cat']?>" />
			
			Sort By: 
			<span id="sortbox">
			<select name="sort" class="formselect" style="width:auto">
				<option value="p.sorting"<?php if((isset($_GET['sort'])&&$_GET['sort']=="p.sorting")||!isset($_GET['sort'])){?> selected="selected"<?php }?>>Default</option>
				<option value="p.title"<?php if(isset($_GET['sort'])&&$_GET['sort']=="p.title"){?> selected="selected"<?php }?>>Name</option>
				<option value="avgrank"<?php if(isset($_GET['sort'])&&$_GET['sort']=="avgrank"){?> selected="selected"<?php }?>>Rating</option>
				<option value="p.price"<?php if(isset($_GET['sort'])&&$_GET['sort']=="p.price"){?> selected="selected"<?php }?>>Price</option>
			</select>
			<select name="ascdesc" class="formselect" style="width:auto">
				<option value="ASC"<?php if((isset($_GET['ascdesc'])&&$_GET['ascdesc']=="ASC")||!isset($_GET['ascdesc'])){?> selected="selected"<?php }?>>Low - High</option>
				<option value="DESC"<?php if(isset($_GET['ascdesc'])&&$_GET['ascdesc']=="DESC"){?> selected="selected"<?php }?>>High - Low</option>
			</select>
			</span>
			<?php if(count($tofilter)>0){?>
				Filter By: 
				<span class="fieldgroup">
				<?php
				foreach($tofilter as $ct_field => $options)
				{
					$fname=array_search($ct_field,$fields[$the_array['ctype']]);
					?>
					<select name="<?=$ct_field?>" class="formselect" style="width:auto">
					<option value="">All <?=$fname?>s</option>
					<?php
					if($_GET['cat']==22&&$ct_field=="field3")
					{
						?>
						<option value="flush" <?php if(urldecode($get_arr[$ct_field])=="flush"){?>selected="selected"<?php }?>>Flush</option>
						<option value="extended" <?php if(urldecode($get_arr[$ct_field])=="extended"){?>selected="selected"<?php }?>>Extended</option>
						<?php
					}
					else
					{
						foreach($options as $opt)
						{
							$opt=strlen($opt)<1?"Unspecified":$opt;
							?><option value="<?=urlencode($opt)?>" <?php if(urldecode($get_arr[$ct_field])==$opt){?>selected="selected"<?php }?>><?=$opt?></option><?php
						}
					}
					?>
					</select>	
					<?php
				}
				?></span><?php
			}
			?>			
			<input type="submit" value="Go" class="button" />
			</form>
		</div>
	</div>
	<div class="clear"></div>
	<?php
	if(isset($get_arr)&&isset($tofilter))
	{
		$match=array_intersect_key($get_arr,$tofilter);
		if(count($match)>0)
		{
			$string="";
			foreach($match as $num => $mf){if(strlen($mf)>0){if(strlen($string)>0){$string.=", ";}$string.=array_search($num,$fields[$the_array['ctype']])." (".urldecode($mf).")"; }}
			?>
			<div id="infobar">
			Filtering by: <?=$string?> <span class='pages'><?php if($prods_num > 0){?><?=1+($get_page*PERPAGE)-PERPAGE?>-<?=$get_page*PERPAGE>$prods_num?$prods_num:$get_page*PERPAGE?> of <?php }?><?=$prods_num?> product<?=$prods_num==1?"":"s"?></span>
			</div>
			<?php
		}
	}
	?><div style="float:right"><?=$takquery[1]?></div><?php
	}
	if($prods_count>0){
		if($class==="offerprods"){?><div id="offerprod"><div style="position:absolute;right:5px;top:0px;font-weight:bold;font-size:110%;color:#888;">CLOSE <a href="index.php?<?=str_replace("&offerprod=1","",$_SERVER['QUERY_STRING'])?>" style="color:#000">[X]</a></div><?php }?>
		<?php if(strlen($header)>0&&$header!="Products&nbsp;"){?><h2 style="margin:10px 6px"><?=$header?></h2><?php }?>
		<?php 	
		if(count($products['withpic'])>0){?>
		<table class="<?=$class?>">
		<tr>
		<?php
		$row=0;
		
		foreach($products['withpic'] as $aid => $prod)
		{
			$newdims=cart_imgdimensions(65,65,$images_arr['product']['path'].$prod['prod_id'].".png");
			$imgsrc="src='".$images_arr['product']['path'].$prod['prod_id'].".png"."'".($class=="prodthumbs"?" style='width:".$newdims[0]."px;height:".$newdims[1]."px;'":"");
			$price=$prod['price']-cart_getdiscount($prod['price'],$prod['salediscount'],$prod['saletype']);
			$row++;
			switch($class){
				case "prodthumbs"://suggested
					?>
					<td>
					<div class="pimg"><a href="./shop/item/<?=$prod['fusionId']?>" title="<?=$prod['title']?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt='' /></a></div>
					</td>
					<?=(($row%$cols==0&&$row<$prods_count)?"</tr><tr>":(($row==$prods_count&&$row%$cols!=0)?"<td class='empty'>&#160;</td>":""));
					break;
				case "lrgthumbs"://index page
					if(file_exists("./content/images/products/".$prod['prod_id'].".png")){$timage="./content/images/products/".$prod['prod_id'].".png";}
					else{$timage="./content/images/products/unavail.png";}
					?>
					<td class="pimg" style="border:0">
					<div class='productbox' <?php if(($row+1)%$cols == 2){?>style='margin:18px 18px 0px;'<?php }?>>
					<div class='brand'><img src='./content/images/logos/<?=strtolower($prod['brand'])?><?php if($prod['premium'] == "y"){?>_premium<?php }?>.gif' alt='<?=ucwords($prod['brand'])?>' /></div>
					<div class='gun'><a href='./shop/item/<?=$prod['fusionId']?>'<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img src='<?=$timage?>' alt='' border='0' /></a></div>
					<div class='prodinfo'><span class='rrp'>RRP from: &pound;<?=$price?></span><br /><?=findreplace($prod['title'],"displayraw")?></div>
					</div>
					</td>
					<?=(($row%$cols==0&&$row<$prods_count)?"</tr><tr>":(($row==$prods_count&&$row%$cols!=0)?"<td class='empty'>&#160;</td>":""));
					break;
				case "offerprods"://popup offer
					?>
					<td class="pimg"><div style="position:relative;top:0px;left:0px;">
						<a href="./shop/item/<?=$prod['fusionId']?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt="<?=$prod['title']?>" /></a>
						<?php if($prod['sale']==1){?><div class="salebadge"><img src="<?=$root_to_cart?>/images/sale_icon.png" alt="On Sale!" /></div><?php }?>
						</div>
					</td>
					<td class="pinfo">
						<div>
						<a href="./shop/item/<?=$prod['fusionId']?>" class="orangebold"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt="<?=$prod['title']?>" /></a> <span class="pprice">&#163;<?=cart_addvat($price)?><?php if($prod['salediscount']!=0){?> <span style="text-decoration:line-through">RRP: &#163;<?=cart_addvat($prod['price'],1)?></span><?php }?></span><br />
						<?=trimtext(strip_tags($prod['content']),100,MAINBASE."/shop/item/".$prod['fusionId'])?>
						<?php if($prod['iState']==0){?><br /><dfn>Available for purchase due to your addition of <?=$the_array['title']?> to your basket.</dfn><?php }?>
						</div>
					</td>
					<?=$row%$cols==0&&$row<$prods_count?"</tr><tr>":"";
					break;
				default:
					$width=$deviceType=="phone"?"100":(100/$cols);
					?>
					<td class="pimg" style="border:0 !important;width:<?=number_format($width,1)?>%;">
					<div style="position:relative;top:0px;left:0px;text-align:center">
					<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
					<a class="acpbutton" style="position:absolute;top:0;right:0" href="./admin/index.php?p=products&amp;showing=prodform&amp;pid=<?=urlencode($prod['prod_id'])?>&amp;owner=<?=urlencode($prod['ownerId'])?>" target="_blank">Edit</a>
					<?php }?>
						<a href="./shop/item/<?=$prod['fusionId']?>" title="<?=cart_trimtext(strip_tags($prod['description']),250)?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt="" /></a><br />
						<a href="./shop/item/<?=$prod['fusionId']?>" style="font-weight:bold;font-size:12px" title="<?=cart_trimtext(strip_tags($prod['description']),250)?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><?=ucwords($prod['title'])?></a>
					<br />
						<span style="font-weight:bold;font-size:12px;color:#A9AEB7">
							From &#163;<?=cart_addvat($price)?><?php
							if($prod['salediscount']!=0){?> <span style="text-decoration:line-through">RRP: &#163;<?=cart_addvat($prod['price'],1)?></span><?php }?>
						</span><br />
						<span style="white-space:nowrap;"><?php if(isset($prod['avgrank'])&&$prod['avgrank']>0){?>Customer Reviews:</span> <?=cart_stars($prod['avgrank'],"small")?><?php }else{?>&nbsp;<?php }?>
						<?php if($prod['salediscount']!=0){?><div class="salebadge"><img src="<?=$root_to_cart?>/images/sale_icon.png" alt="On Sale!" /></div><?php }?>
						</div>
					</td>
					<?=($row%$cols==0||$deviceType=="phone")&&$row<$prods_count?"</tr><tr>":"";
					break;
			}
		}
		?></tr></table><p>&nbsp;</p>
		
		<?php 
		}?>
		<?php if(count($products['nopic'])>0){?>
		
		<table class="<?=$class?>" style="margin:0 6px">
		<tr>
		<?php
		$row=0;
		$nopic_cols=1;
		foreach($products['nopic'] as $aid => $prod)
		{
			$imgsrc="src='".$images_arr['product']['path']."unavail.png'".($class=="prodthumbs"?" style='width:65px;height65px;'":" style='width:78px;height:75px;'");
			$price=$prod['price']-cart_getdiscount($prod['price'],$prod['salediscount'],$prod['saletype']);
			$row++;
			switch($class){
				case "prodthumbs"://suggested
					?>
					<td>
					<div class="pimg"><a href="./shop/item/<?=$prod['fusionId']?>" title="<?=$prod['title']?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt='' /></a></div>
					</td>
					<?=(($row%$cols==0&&$row<$prods_count)?"</tr><tr>":(($row==$prods_count&&$row%$cols!=0)?"<td class='empty'>&#160;</td>":""));
					break;
				case "lrgthumbs"://index page
					if(file_exists("./content/images/products/".$prod['prod_id'].".png")){$timage="./content/images/products/".$prod['prod_id'].".png";}
					else{$timage="./content/images/products/unavail.png";}
					?>
					<td class="pimg" style="border:0">
					<div class='productbox' <?php if(($row+1)%$cols == 2){?>style='margin:18px 18px 0px;'<?php }?>>
					<div class='brand'><img src='./content/images/logos/<?=strtolower($prod['brand'])?><?php if($prod['premium'] == "y"){?>_premium<?php }?>.gif' alt='<?=ucwords($prod['brand'])?>' /></div>
					<div class='gun'><a href='./shop/item/<?=$prod['fusionId']?>'<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img src='<?=$timage?>' alt='' border='0' /></a></div>
					<div class='prodinfo'><span class='rrp'>RRP from: &pound;<?=$price?></span><br /><?=findreplace($prod['title'],"displayraw")?></div>
					</div>
					</td>
					<?=(($row%$cols==0&&$row<$prods_count)?"</tr><tr>":(($row==$prods_count&&$row%$cols!=0)?"<td class='empty'>&#160;</td>":""));
					break;
				case "offerprods"://popup offer
					?>
					<td class="pimg"><div style="position:relative;top:0px;left:0px;">
						<a href="./shop/item/<?=$prod['fusionId']?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt="<?=$prod['title']?>" /></a>
						<?php if($prod['sale']==1){?><div class="salebadge"><img src="<?=$root_to_cart?>/images/sale_icon.png" alt="On Sale!" /></div><?php }?>
						</div>
					</td>
					<td class="pinfo">
						<div>
						<a href="./shop/item/<?=$prod['fusionId']?>" class="orangebold"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?>><img <?=$imgsrc?> alt="<?=$prod['title']?>" /></a> <span class="pprice">&#163;<?=cart_addvat($price)?><?php if($prod['salediscount']!=0){?> <span style="text-decoration:line-through">RRP: &#163;<?=cart_addvat($prod['price'],1)?></span><?php }?></span><br />
						<?=trimtext(strip_tags($prod['content']),100,MAINBASE."/shop/item/".$prod['fusionId'])?>
						<?php if($prod['iState']==0){?><br /><dfn>Available for purchase due to your addition of <?=$the_array['title']?> to your basket.</dfn><?php }?>
						</div>
					</td>
					<?=$row%$cols==0&&$row<$prods_count?"</tr><tr>":"";
					break;
				default:
					?><td class="pinfo" style="width:<?=(100/$nopic_cols)?>%;padding:4px 10px 4px 0 !important;">
						<a href="./shop/item/<?=$prod['fusionId']?>" class="titlesbold" title="<?=cart_trimtext(strip_tags($prod['description']),250)?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$prod['prod_id']?>)'<?php }?> style=""><?=ucwords($prod['title'])?></a>	<span class="pprice" style="font-size:11px">From &#163;<?=cart_addvat($price)?>
						<?php if($prod['salediscount']!=0){?> <span style="text-decoration:line-through">RRP: &#163;<?=cart_addvat($prod['price'],1)?></span><?php }?></span> <span style="white-space:nowrap;"><?php if(isset($prod['avgrank'])&&$prod['avgrank']>0){?><?=cart_stars($prod['avgrank'],"small")?><?php }?></span>
						
					</td>
					<?=$row%$nopic_cols==0&&$row<$prods_count?"</tr><tr>":"";
					break;
			}
		}
		?>
		</tr>
		</table><?php }
		if(!in_array($class,array("offerprods","prodthumbs","lrgthumbs"))&&!in_array($header,array("Most Viewed Products"))){?><div style="float:right"><?=$takquery[1]?></div><?php }
		if($class==="offerprods"){?></div><?php }
	}else if(!in_array($header,array("Most Viewed Products","Suggested Products","Special Offers"))&&$class!=="offerprods"&&$_GET['cat']!=10){
		?><div class="largenotify" style="text-align:left">No products found</div><?php
	}
}

$cart_breadarray=array();
function cart_buildbread($iid,$type)
{	
	global $cart_breadarray,$get_arr,$db1;
	$tablebits=$type=="category"?"gmk_categories as c ON c.`cid`":"lechameau_products as p ON p.`pid`";
	/*$query="SELECT * FROM fusion as f JOIN $tablebits =f.`itemId` AND `itemType`='$type' WHERE `itemId`='".$iid."'";
	$breadq=ysql_query($query,CARTDB) or die(sql_error("Error",$query."<br />".mysql_error()));
	$bread=mysql_fetch_assoc($breadq);*/
	$query="SELECT * FROM fusion as f JOIN $tablebits =f.`itemId` AND `itemType`=? WHERE `itemId`=?";
	$breadq=$db1->prepare($query);
	$breadq->execute(array($type,$iid));
	$bread=$breadq->fetch(PDO::FETCH_ASSOC);
	if($bread['ownerId']!=0){cart_buildbread($bread['ownerId'],$bread['ownerType']);}
	
	if($bread['itemType']=="category"){
		$cart_breadarray["category_".$bread['itemId']]=array($bread['cat_title'],"index.php?p=".ADMINPRODUCTS."&amp;showing=list&amp;owner=$bread[itemId]");
	}
	if($bread['itemType']=="product"){
		$cart_breadarray["product_".$bread['itemId']]=array($bread['prod_title'],"index.php?p=".ADMINPRODUCTS."&amp;showing=prodform&amp;pid=$bread[itemId]&amp;owner=$bread[ownerId]");
	}
}
function cart_bread()
{
	global $subdirs,$pcdetail;
	/*$type=strlen($type)>0?$type:"category";
	$cart_breadarray["Products_0"]=array("Products","index.php?p=".ADMINPRODUCTS);
	cart_buildbread($iid,$type);
	$breadarray_count=count($cart_breadarray);
	$x=0;
	foreach($cart_breadarray as $id => $textlink)
	{
		$theid=explode("_",$id);
		if(!($theid[1]==$iid&&$theid[0]==$type)||$id=="Products"||strlen($action)>0){?><a href="<?=$textlink[1]?>"><?=ucwords($textlink[0])?></a><?php }
		else{echo ucwords($textlink[0]);}
		if($x<$breadarray_count-1){?> &#62; <?php }
		$x++;
	}
	if(strlen($action)>0){echo " &#62; ".$action;}*/
	$depth=count($subdirs);
	$url="./shop";
	$title=$pcdetail;
	$bread="";
	if($subdirs[1]!="item")
	{
		for($dp=1;$dp<$depth;$dp++)
		{ 
			$title=$title[$subdirs[$dp-1]];
			$url.="/".urlencode(urlencode($subdirs[$dp]));
			if($dp==$depth-1){$bread.=" &#187; ".ucwords($title[$subdirs[$dp]]['itemtitle']); }
			else{$bread.=" &#187; <a href='".$url."'>".ucwords(urldecode($subdirs[$dp]))."</a>"; }
		}
	}else{
		$bread.=" &#187; Item #".$subdirs[2];
	}
	echo $bread;
}
	/*function cart_theparent($fusionId)
{
	global $db1;
	$q=ysql_query("SELECT `ownerId`,`ownerType` FROM fusion WHERE `fusionId`='$fusionId'",CARTDB);
	list($owner,$type)=mysql_fetch_row($q);
	$qq=ysql_query("SELECT `fusionId` FROM fusion WHERE `itemId`='$owner' AND `itemType`='$type'",CARTDB);
	list($fuse)=mysql_fetch_row($qq);
	return $fuse;
}*/
function cart_theparent($fusionId)
{
	global $db1;
	$q=$db1->prepare("SELECT `ownerId`,`ownerType` FROM fusion WHERE `fusionId`=?");
	$q->execute(array($fusionId));
	list($owner,$type)=$q->fetch(PDO::FETCH_NUM);
	$qq=$db1->prepare("SELECT `fusionId` FROM fusion WHERE `itemId`=? AND `itemType`=?");
	$qq->execute(array($owner,$type));
	list($fuse)=$qq->fetch(PDO::FETCH_NUM);
	return $fuse;
}
function cart_checkstock($skuvar)
{
	global $db1;
	$avail=0;
	/*$q=ysql_query("SELECT `nav_qty` FROM nav_stock WHERE `nav_skuvar`='$skuvar'",CARTDB);
	list($avail)=mysql_fetch_row($q);*/
	$q=$db1->prepare("SELECT `nav_qty` FROM nav_stock WHERE `nav_skuvar`=?");
	$q->execute(array($skuvar));
	list($avail)=$q->fetch(PDO::FETCH_NUM);
	return $avail;
}
function whats_in_cart($num)
{
	global $_SESSION;
	$thisarray=array();
	$thisarray[0]=array();
	$thisarray[1]=array();
	if(isset($_SESSION['cart']))
	{
	foreach($_SESSION['cart'] as $cid => $values)
	{
		if($num==0)
		{
			foreach($values['skuvariant'] as $product_id => $askuvar)
			{
				$skuinfo=explode("-qty-",$askuvar);
				if(!array_key_exists($skuinfo[0],$thisarray[0])){$thisarray[0][$skuinfo[0]]=0;}
				$thisarray[0][$skuinfo[0]]+=$values['qty']*$skuinfo[1];//qty*item qty
			}
		}else if($num==1){
			if(!in_array($values['prod_id'],$thisarray[1])){array_push($thisarray[1],$values['prod_id']);}
		}
	}
	}
	return $thisarray[$num];
}
function add_to_cart($post_skuvariant,$post_prodid,$post_quantity,$post_price,$post_ispack,$post_excldiscount,$post_allowlist,$post_title,$rlink="")
{
	global $_SESSION,$pid,$skuvar_count,$db1;
	$newqty=0;
	$ok_to_add=2;//set the var
	$paprice=$post_price;
	foreach($post_skuvariant as $itemid => $itemsku)
	{
		if(strlen($itemsku)<1){$ok_to_add=0;}
		$cleansku=explode("-qty-",$itemsku);
		if($ok_to_add!=0)//stop as soon as we find stock unavailable
		{
			$newqty=(array_key_exists($cleansku[0],$skuvar_count))?$skuvar_count[$cleansku[0]]+($cleansku[1]*$post_quantity):($cleansku[1]*$post_quantity);
			$stock=cart_checkstock($cleansku[0]);
			$ok_to_add=$newqty<=$stock?1:0;//enough stock?, can we continue?
			if($ok_to_add==1)
			{
				/*$varprices=ysql_query("SELECT `price` FROM cart_variants WHERE `vskuvar`='$cleansku[0]'");
				list($paprice)=mysql_fetch_row($varprices);*/
				$varprices=$db1->prepare("SELECT `price` FROM cart_variants WHERE `vskuvar`=?");
				$varprices->execute(array($cleansku[0]));
				list($paprice)=$varprices->fetch(PDO::FETCH_NUM);
			}
		}
	}
	$dupecheck="none";
	if(isset($_SESSION['cart']))
	{
		foreach($_SESSION['cart'] as $num => $array)
		{//duplicate item check
			if($array["prod_id"]==$post_prodid&&$array["skuvariant"]==$post_skuvariant)
			{
				$dupecheck=$num;
			}
		}
	}
	if($ok_to_add==1)
	{
		if(!is_int($dupecheck))
		{
			$_SESSION['cart'][]=array(
			"prod_id"=>$post_prodid,
			"skuvariant"=>$post_skuvariant,
			"qty"=>$post_quantity,
			"price"=>$paprice,
			"ispack"=>$post_ispack,
			"exclude_discount"=>$post_excldiscount,
			"allowlist"=>$post_allowlist,
			"title"=>$post_title,
			"rlink"=>$rlink
			);
			$_SESSION['added']=array(count($_SESSION['cart'])-1,'new');
			$_SESSION['cartupdate']="<a href='index.php?p=cart_basket' style='color:red;display:block;padding:6px'>Product added to basket. [View Basket]</a>";
			$_SESSION['pageloads']=3;
			$_SESSION['offerprod']=$post_prodid;
		}
		else
		{
			$_SESSION['cart'][$dupecheck]["qty"]+=$post_quantity;
			$_SESSION['added']=array($dupecheck,'update');
			$_SESSION['cartupdate']="<a href='index.php?p=cart_basket' style='color:red;display:block;padding:6px'>Basket quantity increased. [View Basket]</a>";
			$_SESSION['pageloads']=3;
		}
	}
	else{
		if(strlen($itemsku)<1)
		{$_SESSION['error']=array();$_SESSION['error'][$post_prodid]="Please choose a variation from the drop down menu.";}
		else
		{$_SESSION['error']=array();
			if(is_int($dupecheck))//some in basket
			{$_SESSION['error'][$post_prodid]="Sorry, there is not enough stock to add the specified quanitity of this ".($post_ispack==1?"package.":"product/colour.");}
			else//not in basket and out of stock
			{$_SESSION['error'][$post_prodid]="Sorry, this ".($post_ispack==1?"package":"product/colour").($stock<1?" is now out of stock.":" does not have enough stock to add your desired quantity.");}
		}
	}
}
function cart_emailings($to,$subject,$message,$mailing)
{
	global $date,$sales_email,$cart_debugmode,$sitename,$admin_email,$webby,$sales_phone,$db1;
	$eheaders  = "From: ".$sitename." <".$admin_email.">\r\n";
	$eheaders .= "Reply-To: ".$admin_email."\r\n";
	$eheaders .= "Return-Path: ".$admin_email."\r\n";
	$eheaders .= "MIME-Version: 1.0\r\n";
	$eheaders .= "Content-Type: ".($mailing==1?"text/html":"text/plain")."; charset=ISO_8859-1\r\n";
	$eheaders .= "X-Mailer: PHP/".phpversion();
		
	//set confirm email details
	$msg="==================".PHP_EOL."
	".$subject.PHP_EOL
	."==================".PHP_EOL;
	$msg.=$message;
	$msg.="\r\n
	Kind Regards\r\n
	<a href='".$webby."/'>".$sitename."</a>\r\n
	".$sales_phone."\r\n
	<a href='mailto:".$admin_email."'>".$admin_email."</a>";
	if($cart_debugmode==1)
	{
		?>
		Email to be sent:<br />
		To: <?=$to?><br />
		Subject: <?=$subject?><br />
		Message: <?=str_replace("\r\n","<br />",$message)?><br />
		Headers: <?=$eheaders?>
		<?php
	}
	else
	{
		@mail($to,$subject,$msg,$eheaders);
		//@mail("senfield@gmk.co.uk",$subject,$msg,$eheaders);
	}
}
function cart_relative_date($format,$time) 
{
	$today = strtotime(date('M j, Y'));
	$reldays = ($time - $today)/86400;
	if ($reldays >= 0 && $reldays < 1) {
			return 'today';
	} else if ($reldays >= 1 && $reldays < 2) {
			return 'tomorrow';
	} else if ($reldays >= -1 && $reldays < 0) {
			return 'yesterday';
	}
	if (abs($reldays) < 7) {
			if ($reldays > 0) {
					$reldays = floor($reldays);
					return 'in ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
			} else {
					$reldays = abs(floor($reldays));
					return $reldays . ' day'  . ($reldays != 1 ? 's' : '') . ' ago';
			}
	}
	if (abs($reldays) < 182) {
			return date($format,$time);
	} else {
			return date($format.(stristr(strtolower($format),"y")?"":", Y"),$time);//add year if not already included
	}
}
function cart_getdiscount($price,$discount,$type)
{
	$finaldiscount=0;
	if($type==0)//percent
	{
		$finaldiscount=($price/100)*$discount;
	}
	else//straight money off
	{
		$finaldiscount=$discount;
	}
	return $finaldiscount;
}
//basket total
$cart_ids=array();
if(count($_SESSION['cart'])>0)
{
	foreach($_SESSION['cart'] as $id => $item)
	{
		$itemprice=$item['price']*$item['qty'];
		$itemdiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($item['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $item['exclude_discount']!=1?$itemprice:0;
		
		$basket_qty+=$item['qty'];
		$sub_total+=$itemprice;
		$totaldiscount+=$itemdiscount;//add up all items which are excluded from discount
		if(!in_array($item['prod_id'],$cart_ids)){array_push($cart_ids,$item['prod_id']);}
	}
	$defaultpostage=$basket_qty>0?cart_postagecalc($sub_total,$_SESSION['shipping']):0;

	$discount=isset($_SESSION['discount_amount'])&&$_SESSION['discount_amount']>0?(($totaldiscount)/100)*$_SESSION['discount_amount']:0;
	$total=$sub_total-$discount;
	//$vattoadd=$vat*($total/100);//total vat for all items (including non discountable)
	$basket_total=cart_addvat($total,1)+$defaultpostage;//total, vat, postage cost and discount
}
/*Array ( [0] => Array ( [prod_id] => 105 [skuvariant] => Array ( [105] => BCG1225-v-V0001-qty-1 ) [qty] => 1 [price] => 70.83 [ispack] => 0 [exclude_discount] => 0 [allowlist] => Array ( ) [title] => Bodiam ) ) */
?>