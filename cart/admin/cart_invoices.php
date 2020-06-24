<?php 
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }
$invsort="";
if(isset($_GET['ssortby'])){$invsort.="&ssortby=".$_GET['ssortby'];}
if(isset($_GET['sstatus'])){$invsort.="&sstatus=".$_GET['sstatus'];}
if(isset($_GET['ssortdir'])){$invsort.="&ssortdir=".$_GET['ssortdir'];}
if(isset($_GET['invoice'])){$invsort.="&invoice=".$_GET['invoice'];}
if(isset($_GET['lastname'])){$invsort.="&lastname=".$_GET['lastname'];}
//&sstatus=all&ssortdir=DESC
/*........................ UPDATE MULTIPE ORDERS ..........................*/
if(isset($post_arr['tracking']))
{
	foreach($post_arr['order_id'] as $invoice => $orderid)
	{
		//$invoice=$post_arr['invoice'][$orderid];
		$datebits=explode("/",$post_arr['date_shipped'][$orderid]);
		$shipper=(strlen($post_arr['shipper'][$orderid])<1)?$post_arr['shipper2'][$orderid]:$post_arr['shipper'][$orderid];
		if(strlen($shipper)>0&&count($datebits)>2)
		{
			$day=($datebits[0]<10&&strlen($datebits[0])>1)?substr($datebits[0],1,1):$datebits[0];
			$month=($datebits[1]<10&&strlen($datebits[1])>1)?substr($datebits[1],1,1):$datebits[1];
			$year=(strlen($datebits[2])==2)?"20".$datebits[2]:$datebits[2];
			$shipdate=date("U",mktime(0,0,0,$month,$day,$year));
			/*$oshipq=ysql_query("SELECT `ordership_id` FROM cart_ordership WHERE `order_id`='$orderid'");$oship=mysql_fetch_row($oshipq);*/
			$oshipq=$db1->prepare("SELECT `ordership_id` FROM cart_ordership WHERE `order_id`=?");$oshipq->execute(array($orderid));$oship=$oshipq->fetch(PDO::FETCH_NUM);
			if($oship)
			{
				cart_query("UPDATE cart_ordership SET `shipper`='$shipper',`tracking`=?,`date_shipped`=? WHERE `ordership_id`=?",array($post_arr['tracking'][$orderid],$shipdate,$oship[0]));
			}
			else
			{
				cart_query("INSERT INTO cart_ordership(`order_id`,`shipper`,`tracking`,`date_shipped`) VALUES(?,?,?,?)",array($orderid,$shipper,$post_arr['tracking'][$orderid],$shipdate));
			}
			cart_query("UPDATE cart_orders SET `order_status`='Shipped' WHERE `order_id`=?",array($orderid));
			if($post_arr['notify'][$orderid])
			{
				/*$custq=ysql_query("SELECT o.`email`,o.`firstname`,o.`lastname`,o.`cust_id`,`mailing` FROM (cart_orders as o LEFT JOIN cart_customers as c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0') WHERE `order_id`='$orderid'");
				$cust=mysql_fetch_assoc($custq);*/
				$custq=$db1->prepare("SELECT o.`email`,o.`firstname`,o.`lastname`,o.`cust_id`,`mailing` FROM (cart_orders as o LEFT JOIN cart_customers as c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0') WHERE `order_id`=?");
				$custq->execute(array($orderid));
				$cust=$custq->fetch(PDO::FETCH_ASSOC);
				$conttype=$contenttype[$cust['mailing']];
				$headers = "From: $sitename <$admin_email>\r\n";
				$headers .= "Reply-To: $admin_email\r\n";
				$headers .= "Return-Path: $admin_email\r\n";
				$headers .= "MIME-Version: 1.0\r\nContent-Type: $conttype; charset=UTF-8\r\n";
				$message="Dear $cust[firstname] $cust[lastname],".$br[$cust['mailing']].
				$br[$cust['mailing']].
				"Invoice Number: $invoice".$br[$cust['mailing']].
				$br[$cust['mailing']].
				"We are pleased to inform you that your order has been sent.".$br[$cust['mailing']].
				"================================================".$br[$cust['mailing']].
				"Method: $shipper ".$br[$cust['mailing']].
				"Tracking Number: ".$post_arr['tracking'][$orderid].$br[$cust['mailing']];
				if($cust['cust_id']!=0){
					$message.=$br[$cust['mailing']].
					"You can check the status of your order by going to the url below ".$br[$cust['mailing']].
					MAINBASE."/index.php?p=receipt&invoice=$invoice ".$br[$cust['mailing']];
				}
				$message.="=================================================".$br[$cust['mailing']].
				"Thank you for your business. ".$br[$cust['mailing']].
				"$sitename".$br[$cust['mailing']].
				"Bear House,".$br[$cust['mailing']].
				"Concorde Way,".$br[$cust['mailing']].
				"Fareham,".$br[$cust['mailing']].
				"Hampshire,".$br[$cust['mailing']].
				"PO15 5RL".$br[$cust['mailing']].
				"United Kingdom".$br[$cust['mailing']].
				"Email: $admin_email".$br[$cust['mailing']].
				"Tel: $sales_phone".$br[$cust['mailing']].
				$br[$cust['mailing']].
				"vat. Registration No: $vatreg".$br[$cust['mailing']].
				"Company Registration No.: $coreg".$br[$cust['mailing']].
				"=================================================".$br[$cust['mailing']];
				$to=($cart_live==1&&$inhouse==0)?$cust['email']:"senfield@gmk.co.uk";
				mail($to,"Your order from $sitename has shipped!",$message,$headers);/*send mail*/
				?><div class="notice">Successfully added shipping information</div><?php
			}
			//if($action=="updatemany"){header("Location: $mainbase/admin.php?p=invoices".$invsort);}
			//else{header("Location: $mainbase/admin.php?p=invoices&act=view&invoice=$get_arr[invoice]");}
		}
		else
		{
			if(!isset($_SESSION['error'])){$_SESSION['error']="";}
			if(count($datebits)<3)
			{
				$_SESSION['error'].="Please specify a valid date for invoice ".$post_arr['invoice'][$orderid].".<br />";
				array_push($higherr,"ship_date_".$orderid);
			}
			if(strlen($shipper)<1)
			{
				$_SESSION['error'].="Please specify the shipping method for invoice ".$post_arr['invoice'][$orderid].".<br />";
				array_push($higherr,"shipper_".$orderid);
				array_push($higherr,"shipper2_".$orderid);
			}
		}
	}
}
/*........................UPDATE MULTIPE ORDERS.............................*/
else if($act=="updatemany")
{
	foreach($post_arr['inv'] as $inv => $stat)
	{
		if($stat!="Delete"&&$stat!="Shipped"){
			$stat=$stat==""?"null":$stat;
			cart_query("UPDATE cart_orders SET `order_status`=? WHERE `invoice`=?",array($stat,$inv));
		}
	}
	foreach($post_arr['iorder_status'] as $inv => $stat)
	{
		cart_query("UPDATE cart_orders SET `iorder_status`=? WHERE `invoice`=?",array($stat,$inv));
	}	
}
/*............................DELETE MULTIPLE ORDERS..........................*/
/*else if(isset($post_arr['delinv']))
{
	$invoices=(count($post_arr['delinv'])>1)?"IN('".implode("','",$post_arr['delinv'])."')":"='".$post_arr['delinv'][0]."'";
	$invtodelq=mysql_query("SELECT order_id FROM orders WHERE invoice $invoices")or die("SELECT order_id FROM orders WHERE invoice $invoices".mysql_error());
	$ordernums="";
	while($invtodel=mysql_fetch_row($invtodelq))
	{
		if($ordernums!=""){$ordernums.=",";}
		$ordernums.="'$invtodel[0]'";
	}
	mysql_query("DELETE FROM orders WHERE order_id IN($ordernums)");
	mysql_query("DELETE FROM orderproducts WHERE order_id IN($ordernums)");
	mysql_query("DELETE FROM orderkits WHERE order_id IN($ordernums)");
	mysql_query("DELETE FROM ordership WHERE order_id IN($ordernums)");
}*/
/*..............................UPDATE ORDER STATUS............................*/
else if(isset($post_arr['updateinv']))
{
	$ps=($post_arr['opaid']==1)?1:0;
	$ios=($post_arr['ocomp']==1)?1:0;
	$os=$post_arr['newstatus']==""?"null":$post_arr['newstatus'];
	if(strtolower($os)!="delete")
	{
		cart_query("UPDATE cart_orders SET `pay_status`=?,`iorder_status`=?,`order_status`=? WHERE `invoice`=?",array($ps,$ios,$os,$get_arr['invoice']));
		//if(strtolower($os)!="shipped"){header("Location: $mainbase/admin.php?p=invoices&act=view&invoice=$get_arr[invoice]");}
		$act="view";
	}
}
/*...............................GENERATE NAV XML................................*/
else if(isset($post_arr['genxml']))
{
	cart_xml($post_arr['vendortx']);
	$act="view";
}
else if(isset($post_arr['sendemail']))
{
	cart_orderemail($post_arr['vendortx']);
	$act="view";
}
/*...............................UPDATE ORDER ITEMS................................*/
else if(isset($post_arr['qty']))
{
	$new_qty=0;
	$new_sub_total=0;
	/*$orderq=ysql_query("SELECT `ship_method_id`,d.`discount`,`price` FROM (cart_orders as o JOIN cart_orderproducts as op ON o.`order_id`=op.`order_id`) LEFT JOIN cart_discounts as d ON o.`discount_code`=d.`code` WHERE `invoice`='$get_arr[invoice]'",CARTDB);
	$order=mysql_fetch_assoc($orderq);*/
	$orderq=$db1->prepare("SELECT `ship_method_id`,d.`discount`,`price` FROM (cart_orders as o JOIN cart_orderproducts as op ON o.`order_id`=op.`order_id`) LEFT JOIN cart_discounts as d ON o.`discount_code`=d.`code` WHERE `invoice`=?");
	$orderq->execute(array($get_arr['invoice']));
	$order=$orderq->fetch(PDO::FETCH_ASSOC);
	$shipid=($order['ship_method_id']==0)?5:$order['ship_method_id'];
	/*$postq=ysql_query("SELECT * FROM cart_postage as p JOIN cart_postage_details as pd ON p.`post_id`=pd.`post_id` WHERE `post_details_id`='$shipid'",CARTDB);
	$post=mysql_fetch_assoc($postq);*/
	$postq=$db1->prepare("SELECT * FROM cart_postage as p JOIN cart_postage_details as pd ON p.`post_id`=pd.`post_id` WHERE `post_details_id`=?");
	$postq->execute(array($shipid));
	$post=$postq->fetch(PDO::FETCH_ASSOC);
	
	foreach($post_arr['skuvariant'] as $spid => $val)
	{
		if(strlen(stristr($val,"-qty-"))>0)
		{
			$expqty=explode("-qty-",$val);
			$expsku=explode("-v-",$expqty[0]);
			/*$optvq=ysql_query("SELECT `vid`,`vname` FROM cart_variants as v WHERE `vskuvar`='$expqty[0]'",CARTDB);
			list($gopt,$col,$type)=mysql_fetch_row($optvq);echo mysql_error();*/
			$optvq=$db1->prepare("SELECT `vid`,`vname` FROM cart_variants as v WHERE `vskuvar`=?");
			$optvq->execute(array($expqty[0]));
			list($gopt,$col,$type)=$optvq->fetch(PDO::FETCH_NUM);
			cart_query("UPDATE cart_orderproducts SET `oitem`=? WHERE `order_prod_id`=?",array($col,$post_arr['popttoorderopt'][$spid]));
		}
	}
	foreach($post_arr['qty'] as $opid => $qty)
	{
		cart_query("UPDATE cart_orderproducts SET `qty`=? WHERE `order_prod_id`=?",array($qty,$opid));
		$new_qty+=$qty;
		$new_sub_total+=($post_arr['price'][$opid]*$qty);
	}
	$postage=($new_qty>0)?(($new_sub_total>$post['field2'])?$post['field1']:$post['field3']):0;
	//$newvat=$vat*($new_sub_total/100);
	//$newtotal=$new_sub_total+$newvat;
	$newvatty=cart_getvat($new_sub_total);
	$newvat=$newvatty[1];
	$newtotal=cart_addvat($new_sub_total,1);
	
	if(isset($order['discount_code'])&&strlen($order['discount_code'])>0){
		$discount=(($newtotal/100)*$order['discount']);
		$newtotal=($newtotal+$postage)-$discount;
	}
	else
	{
		$newtotal=$newtotal+$postage;
	}
	cart_query("UPDATE cart_orders SET `total_price`=?,`tax_price`=? WHERE `invoice`=?",array($newtotal,$newvat,$get_arr['invoice']));
}
/* /admin stuff */
/*$os=isset($get_arr['sstatus'])?$get_arr['sstatus']:"New";
$sby=isset($get_arr['ssortby'])?$get_arr['ssortby']:"invoice";
$order_status=isset($get_arr['sstatus'])&&$get_arr['sstatus']=="all"?"":($os==""?"WHERE `order_status` is null":"WHERE `order_status`='$os'");
$sortby=!isset($get_arr['ssortby'])?"o.invoice":"o.".$get_arr['ssortby'];
$sort_direction=!isset($get_arr['ssortdir'])?"DESC":$get_arr['ssortdir'];
$invoice=isset($get_arr['invoice'])?$get_arr['invoice']:"";
$rangefrom=isset($get_arr['from'])?(($order_status!="")?"AND":"WHERE")." `date_ordered` >='".$get_arr['from']."'":"";
$rangeto=isset($get_arr['to'])?(($rangefrom!=""||$order_status!="")?"AND":"WHERE")." `date_ordered` <='".$get_arr['to']."'":"";
$istatus=isset($get_arr['istatus'])?(($rangefrom!=""||$rangeto!=""||$order_status!="")?"AND":"WHERE")." `iorder_status`=".$get_arr['istatus']:"";*/

$binds=array();
$os="New";
$sort_direction=isset($get_arr['ssortdir'])&&$get_arr['ssortdir']=="ASC"?"ASC":"DESC";
$invoice=isset($get_arr['invoice'])?$get_arr['invoice']:"";
$rangefrom="";
$rangeto="";
$istatus="";

if(isset($get_arr['sstatus']))
{
	$os=$get_arr['sstatus'];
	if($get_arr['sstatus']=="all")
	{
		$order_status="";
	}
}
if((!isset($get_arr['sstatus'])||$get_arr['sstatus']!="all")&&$os=="")
{
	$order_status="WHERE `order_status` is null";
}
else
{
	$order_status="WHERE `order_status`=?";
	$binds[]=$os;
}

if(isset($get_arr['ssortby']))
{
	$sby=$get_arr['ssortby'];
	$col=cleanCols("cart_orders",$get_arr['ssortby']);
	if(strlen($col)<1){$col=cleanCols("cart_customers",$get_arr['ssortby']);}
	if(strlen($col)<1){$col="invoice";}
	$sortby="o.".$col;
}
else
{
	$sby="invoice";
	$sortby="o.invoice";
}

if(isset($get_arr['from']))
{
	$rangefrom=(($order_status!="")?"AND":"WHERE")." `date_ordered` >=?";
	$binds[]=$get_arr['from'];
}
if(isset($get_arr['to']))
{
	$rangeto=(($rangefrom!=""||$order_status!="")?"AND":"WHERE")." `date_ordered` <=?";
	$binds[]=$get_arr['to'];
}
if(isset($get_arr['istatus']))
{
	$istatus=(($rangefrom!=""||$rangeto!=""||$order_status!="")?"AND":"WHERE")." `iorder_status`=?";
	$binds[]=$get_arr['istatus'];
}
$nofree=(($rangefrom!=""||$rangeto!=""||$order_status!=""||$istatus!="")?"AND":"WHERE")." `pay_method`!='Free'";


$isrch="";
if(isset($get_arr['invoice'])||isset($get_arr['lastname']))
{
	$bracks=isset($get_arr['invoice'])&&isset($get_arr['lastname'])&&strlen($get_arr['invoice'])>0&&strlen($get_arr['lastname'])>0?1:0;
	if(strlen($get_arr['invoice'])>0||strlen($get_arr['lastname'])>0){$isrch.=($rangefrom!=""||$rangeto!=""||$order_status!=""||$istatus!="")?"AND":"WHERE";}
	$isrch.=$bracks==1?" (":" ";
	if(strlen($get_arr['invoice'])>0){$isrch.="`invoice` LIKE ?";$binds[]="%".$get_arr['invoice']."%";}
	else{$isrch.="";}
	$isrch.=$bracks==1?" OR ":"";
	if(strlen($get_arr['lastname'])>0){$isrch.="o.`lastname` LIKE ?";$binds[]="%".$get_arr['lastname']."%";}
	else{$isrch.="";}
	$isrch.=$bracks==1?")":"";
}
//$binds[]=$invoice;
?>
<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Invoices</a><?=$invoice!=""&&($act=="view"||$act=="updatemany"||$act=="update")?$breadsep.($act=="update"?"<a href='".str_replace("update","view",$formaction)."'>":"")."Invoice: ".$get_arr['invoice'].($act=="update"?"</a>".$breadsep."Tracking Information":""):""?></div>

<?php if(isset($_SESSION['error'])&&strlen($_SESSION['error'])>0){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }?>
<!-- CONTENT -->
<?php
switch($act)
{
	case "view":
		/*$invq=ysql_query("SELECT o.`cust_id`,o.`order_id`,`pay_status`,`iorder_status`,`order_status`,o.`nametitle`,o.`firstname`,o.`lastname`,o.`address1`,o.`address2`,o.`city`,o.`state`,o.`postcode`,o.`country`,o.`phone`,o.email,`sameasbilling`,`alt_nametitle`,`alt_name`,`alt_address1`,`alt_address2`,`alt_city`,`alt_state`,`alt_postcode`,`alt_country`,`alt_phone`,`ship_description`,`shipper`,`comments`,`tracking`,FROM_UNIXTIME(`date_ordered`,'%d/%m/%Y %H:%i') as date_ordered,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y %h:%i') as date_shipped,o.`Status`,o.`CardType`,o.`Last4Digits`,o.`VendorTxCode`,o.`xmlmade` FROM cart_orders AS o LEFT JOIN cart_customers AS c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0' LEFT JOIN cart_ordership AS os on o.`order_id`=os.`order_id` WHERE `invoice`='$invoice'",CARTDB);
		$inv=mysql_fetch_assoc($invq);*/
		$invq=$db1->prepare("SELECT o.`cust_id`,o.`order_id`,`pay_status`,`iorder_status`,`order_status`,o.`nametitle`,o.`firstname`,o.`lastname`,o.`address1`,o.`address2`,o.`city`,o.`state`,o.`postcode`,o.`country`,o.`phone`,o.email,`sameasbilling`,`alt_nametitle`,`alt_name`,`alt_address1`,`alt_address2`,`alt_city`,`alt_state`,`alt_postcode`,`alt_country`,`alt_phone`,`ship_description`,`shipper`,`comments`,`tracking`,FROM_UNIXTIME(`date_ordered`,'%d/%m/%Y %H:%i') as date_ordered,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y %h:%i') as date_shipped,o.`Status`,o.`CardType`,o.`Last4Digits`,o.`VendorTxCode`,o.`xmlmade` FROM cart_orders AS o LEFT JOIN cart_customers AS c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0' LEFT JOIN cart_ordership AS os on o.`order_id`=os.`order_id` WHERE `invoice`=?");
		$invq->execute(array($invoice));
		$inv=$invq->fetch(PDO::FETCH_ASSOC);
		
		?>
		<form action="<?=str_replace("view","update",$formaction)?>" method="post">
		<input type="hidden" name="order_id" value="<?=$inv['order_id']?>" />
		<input type="hidden" name="vendortx" value="<?=$inv['VendorTxCode']?>" />
		<table class="details">
		<tr class="head">
			<td colspan="2"><div class="titles">Invoice Details</div></td>
		</tr>
		<tr class="subhead">
			<td style="width:50%">Invoice: <?=$invoice?></td>
			<td style="width:50%;padding-left:0 !important;">Order date: <?=$inv['date_ordered']?></td>
		</tr>
		<tr class="row_light">
			<td colspan="2">
			<div style="width:25%;float:left;">
				<strong>Bill to:</strong><br />
				<?=($inv['cust_id']!=0?"<a href='index.php?p=cart_customers&amp;act=view&amp;cust_id=$inv[cust_id]'>":"").ucwords($inv['nametitle']." ".$inv['firstname']." ".$inv['lastname']).($inv['cust_id']!=0?"</a>":"")?><br />
				<?=$inv['address1']?><br />
				<?=((strlen($inv['address2'])>0)?$inv['address2']."<br />":"")?>
				<?=$inv['city']?><br />
				<?=cart_get_county($inv['state'])?><br />
				<?=$inv['postcode']?><br />
				<?=cart_get_country($inv['country'])?><br />
				<?=$inv['phone']?><br />
				<?=$inv['email']?>
			</div>
			<div style="width:25%;float:left;">
				<strong>Deliver to:</strong><br />
				<?php if($inv['sameasbilling']==1){?>
				Same as billing address
				<?php }else{?>
				<?=$inv['alt_nametitle']." ".$inv['alt_name']?><br />
				<?=$inv['alt_address1']?><br />
				<?=((strlen($inv['alt_address2'])>0)?$inv['alt_address2']."<br />":"")?>
				<?=$inv['alt_city']?><br />
				<?=cart_get_county($inv['alt_state'])?><br />
				<?=$inv['alt_postcode']?><br />
				<?=cart_get_country($inv['alt_country'])?><br />
				<?=$inv['alt_phone']?>
				<?php }?>
			</div>
			<div style="width:50%;float:left;">
				<strong>Payment method:</strong><br />
				<?php if($inv['pay_status']==1){?>
				<?=$inv['CardType']=="MC"?"Mastercard":$inv['CardType']?> <?=strlen($inv['Last4Digits'])>0?($inv['CardType']=="AMEX"?"**** ****** *":"**** **** **** ").$inv['Last4Digits']:""?>
				<?php }else{?>Unpaid<?php }?>
				<br /><br />
				<strong>Postage method:</strong><br />
				<?=$inv['ship_description']?>
				<?php if($inv['shipper']){
					$turl1=array_key_exists($inv['shipper'],$postaltracking)&&$inv['tracking']?"<a href='".$postaltracking[$inv['shipper']].$inv['tracking']."' target='_blank'>":"";
					$turl2=array_key_exists($inv['shipper'],$postaltracking)&&$inv['tracking']?"</a>":"";
					?>
					<br /><br />
					<strong>Shipped: <?=$inv['date_shipped']?></strong><br />
					Carrier: <?=$inv['shipper']?><br />
					Tracking: <?=$turl1.(($inv['tracking'])?$inv['tracking']:"Not available").$turl2?>
				<?php }?>
			</div>
			</td>
		</tr>
		<tr class="subhead">
			<td colspan="2"><strong>Customer comments</strong></td>
		</tr>
		<tr class="row_light">
			<td colspan="2"><?=isset($inv['comments'])&&strlen($inv['comments'])>0?$inv['comments']:"None"?></td>
		</tr>
		<tr class="subhead">
			<td colspan="2">Payment Status Message</td>
		</tr>
		<tr class="row_light">
			<td colspan="2"><?=$inv['Status']?></td>
		</tr>
		<tr class="subhead">
			<td colspan="2">Invoice Settings</td>
		</tr>
		<tr class="row_light">
			<td style="width:50%" class="hidefromprint"><span><input type="checkbox" name="opaid" id="opaid" value="1" <?php if($inv['pay_status']==1){?>checked="checked"<?php }?> /> <label for="opaid">Mark Order as paid</label><br /><input type="checkbox" name="ocomp" id="ocomp" value="1" <?php if($inv['iorder_status']==1){?>checked="checked"<?php }?> /> <label for="ocomp">Mark order as complete</label></span></td>
			<td style="width:50%" class="hidefromprint">
			<span>
			<select name="newstatus" class="formfieldm">
			<?php foreach($cart_orderstatuses as $dbstatus => $displaystatus){if($dbstatus!="Pending"){?>
				<option value="<?=$dbstatus?>" <?php if($inv['order_status']==$dbstatus){?>selected="selected"<?php }?>><?=$displaystatus?></option>
			<?php }}?>
			</select> </span></td>
		</tr>
		</table>
		<p class="submit"> <?php if(strtolower($inv['order_status'])!="void"){?><input type="submit" name="sendemail" value="Resend customer email" class="formbutton" /> <input type="submit" name="genxml" value="Generate NAV file" class="formbutton" /> <?php }?><a href="#" onclick="window.print();return false">Print Invoice</a> <input type="submit" name="updateinv" value="Update Invoice" class="formbutton" />
		<br /><dfn>Only generate NAV file if you are sure it hasn't already been imported to NAV. <?=strlen($inv['xmlmade'])>0&&$inv['xmlmade']!="0000-00-00 00:00:00"&&$inv['xmlmade']!=null?"(Last generated on ".date("d/m/y @H:i",strtotime($inv['xmlmade'])).")":(($inv['xmlmade']=="0000-00-00 00:00:00"||$inv['xmlmade']==null)&&$inv['date_ordered']<strtotime("24 June 2015")?"<span style='color:red'>FILE NOT GENERATED!</span>":"")?></dfn></p>
		</form>
		<br />
		<form action="<?=$formaction?>" method="post" id="productoptions" name="productoptions">
		<?php /*cart_ordercontents("invoice='$invoice'","");*/cart_ordercontents("invoice=?","",array($invoice));?>
		<p class="submit"><input type="submit" name="updateorder" value="Update Order" /></p>
		</form>
		<?php
		break;
	default:
		$toupdate=""; 
		$invoices=array();
		if($act=="updatemany"&&in_array("Shipped",$post_arr['inv']))
		{
			$binds1=array();
			foreach($post_arr['order_id'] as $tinvoice => $oid)
			{
				if($post_arr['inv'][$tinvoice]=="Shipped")
				{
					if($toupdate!=""){$toupdate.=",";}
					$toupdate.="?";
					$binds1[]=$tinvoice;
				}
			}
			/*$changedq=ysql_query("SELECT `invoice` FROM cart_orders WHERE `order_status`!='Shipped' AND `invoice` IN($toupdate)",CARTDB);
			while($changed=mysql_fetch_row($changedq)){$invoices[$changed[0]]=$post_arr['order_id'][$changed[0]];}*/
			$changedq=$db1->prepare("SELECT `invoice` FROM cart_orders WHERE `order_status`!='Shipped' AND `invoice` IN($toupdate)");
			$changedq->execute($binds1);
			while($changed=$changedq->fetch(PDO::FETCH_NUM)){$invoices[$changed[0]]=$post_arr['order_id'][$changed[0]];}
			
		}
		
		if(($act=="update"&&strtolower($post_arr['newstatus'])=="shipped")||($act=="updatemany"&&count($invoices)>0))/* setting shipped */
		{
			if(count($invoices)<2){
				if(count($invoices)<1)
				{
					$invoice=$get_arr['invoice'];
					$oid=is_array($post_arr['order_id'])?$post_arr['order_id'][$invoice]:$post_arr['order_id'];
				}
				else
				{
					$invoice=key($invoices);
					$oid=$invoices[$invoice];
				}
				/*$shipq=ysql_query("SELECT `shipper`,`tracking`,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y') as date_shipped FROM cart_ordership WHERE `order_id`='$oid'",CARTDB);
				$shipn=mysql_num_rows($shipq);
				$ship=mysql_fetch_assoc($shipq);*/
				$shipq=$db1->prepare("SELECT `shipper`,`tracking`,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y') as date_shipped FROM cart_ordership WHERE `order_id`=?");
				$shipq->execute(array($oid));
				$shipn=$shipq->rowCount();
				$ship=$shipq->fetch(PDO::FETCH_ASSOC);
				$arr=isset($post_arr['invoice'])?$post_arr:($shipn>0?$ship:array());
				$shipper2=!in_array($ship['shipper'],$postal)&&!isset($post_arr['invoice'])?"shipper":"shipper2";
				
				?>
				<form action="<?=$formaction?>" method="post">
				<input type="hidden" name="newstatus" value="Shipped" />
				<input type="hidden" name="order_id[<?=$invoice?>]" value="<?=$oid?>" />
				<input type="hidden" name="inv[<?=$invoice?>]" value="Shipped" />
				<input type="hidden" name="invoice[<?=$oid?>]" value="<?=$invoice?>" />
				<table class="details">
				<tr class="head"> 
					<td colspan="2"><div class="titles">Tracking Information for invoice: <?=$invoice?></div></td>
				</tr>
				<tr class="row_light">
					<td colspan="2">
						If you wish to provide your customer with a tracking number from one of the major companies, select the sender and then place the tracking number in the space provided.
						If you select 'notify customer' a confirmation email will be sent to the customer with their tracking number.
						Otherwise, <a href="<?=$self?>&amp;act=view&amp;invoice=<?=$invoice?>">return</a> to the invoice.
					</td>
				</tr>
				<tr class="row_dark">
					<td class="first">Method</td>
					<td>
						<select name="shipper[<?=$oid?>]" <?=highlighterrors($higherr,"shipper_".$oid)?>>
							<option value="" <?=cart_is_selected("shipper",$oid,"",$arr,"select")?>> --- Select method --- </option>
							<?php foreach($postal as $carrier){?>
							<option value="<?=$carrier?>" <?=cart_is_selected("shipper",$oid,$carrier,$arr,"select")?>><?=$carrier?></option>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr class="row_light">
					<td>Other</td>
					<td><input name="shipper2[<?=$oid?>]" size="18" value="<?=cart_posted_value($shipper2,$oid,"",$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"shipper2_".$oid)?> /></td>
				</tr>
				<tr class="row_dark">
					<td>Date Posted</td>
					<td><input name="date_shipped[<?=$oid?>]" size="18" value="<?=cart_posted_value("date_shipped",$oid,date("d/m/Y"),$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"ship_date_".$oid)?> /> <dfn>(dd/mm/yyyy)</dfn></td>
				</tr>
				<tr class="row_light">
					<td>Tracking Number</td>
					<td><input name="tracking[<?=$oid?>]" value="<?=cart_posted_value("tracking",$oid,"",$arr)?>" size="18" maxlength="20" type="text" /></td>
				</tr>
				<tr class="row_dark">
					<td>Notification</td>
					<td><input class="checkbox" name="notify[<?=$oid?>]" value="1" type="checkbox" <?=cart_is_selected("notify",$oid,"1",$arr,"check")?> />Notify customer by email?</td>
				</tr>
				</table>
				<p class="submit"><input value="Process" type="image" src="<?=$cart_adminpath?>/images/submit.png" /></p>
				</form>
				<?php
			}
			else
			{
				?>
				<form action="<?=$formaction?>" method="post">
				<table class="details">
				<tr class="head"> 
					<td colspan="6"><div class="titles">Tracking Information for multiple invoices</div></td>
				</tr>
				<tr>
					<td colspan="6">
						If you wish to provide your customers with a tracking number from one of the major companies, select the sender and then place the tracking number in the space provided.
						If you select 'notify customer' a confirmation email will be sent to the customer with their tracking number.
					</td>
				</tr>
				<tr class="subhead">
					<td style="width:10%;text-align:center">Invoice</td>
					<td width="23%">Method</td>
					<td style="width:15%">Other</td>
					<td width="22%">Date Posted <dfn>(dd/mm/yyyy)</dfn></td>
					<td style="width:15%">Tracking Number</td>
					<td style="width:15%">Notify customer?</td>
				</tr>
				<?php foreach($invoices as $invoice => $oid){
					/*$shipq=ysql_query("SELECT `shipper`,`tracking`,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y') as date_shipped FROM cart_ordership WHERE `order_id`='$oid'");
					$ship=mysql_fetch_assoc($shipq);*/
					$shipq=$db1->prepare("SELECT `shipper`,`tracking`,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y') as date_shipped FROM cart_ordership WHERE `order_id`=?");
					$shipq->execute(array($oid));
					$ship=$shipq->fetch(PDO::FETCH_ASSOC);
					$arr=isset($post_arr['invoice'])?$post_arr:(isset($ship)?$ship:"");
					//shipper2 posted value ok
					//shipper2 shows shipper from sql where not in postal arr 
					$shipper2=!in_array($ship['shipper'],$postal)&&!isset($post_arr['invoice'])?"shipper":"shipper2";
					?>
					<input type="hidden" name="order_id[<?=$invoice?>]" value="<?=$oid?>" />
					<input type="hidden" name="inv[<?=$invoice?>]" value="Shipped" />
					<input type="hidden" name="invoice[<?=$oid?>]" value="<?=$invoice?>" />
					<tr>
					<td style="text-align:center"><?=$invoice?></td>
					<td>
						<select name="shipper[<?=$oid?>]" <?=highlighterrors($higherr,"shipper_".$oid)?>>
							<option value="" <?=cart_is_selected("shipper",$oid,"",$arr,"select")?>>- Select method -</option>
							<?php foreach($postal as $carrier){?>
							<option value="<?=$carrier?>" <?=cart_is_selected("shipper",$oid,$carrier,$arr,"select")?>><?=$carrier?></option>
							<?php }?>
						</select>
					</td>
					<td><input name="shipper2[<?=$oid?>]" size="18" value="<?=cart_posted_value($shipper2,$oid,"",$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"shipper2_".$oid)?> class="formfieldm" /></td>
					<td><input name="date_shipped[<?=$oid?>]" size="18" value="<?=cart_posted_value("date_shipped",$oid,date("d/m/Y"),$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"ship_date_".$oid)?> class="formfieldm" /></td>
					<td><input name="tracking[<?=$oid?>]" value="<?=cart_posted_value("tracking",$oid,"",$arr)?>" size="18" maxlength="20" type="text" class="formfieldm" /></td>
					<td style="text-align:center"><input class="checkbox" name="notify[<?=$oid?>]" value="1" type="checkbox" <?=cart_is_selected("notify",$oid,"1",$post_arr,"check")?> /></td>
					</tr>
				<?php }?>
				</table>
				<p class="submit"><input value="Process" type="image" src="<?=$cart_adminpath?>/images/submit.png"></p>
				</form>
				<?php
			}
		}
		else
		{
			$invsort=isset($invsort)?$invsort:"";
			//$pgnums=cart_pagenums("SELECT order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status,xmlmade FROM cart_orders as o LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id $order_status $rangefrom $rangeto $istatus $isrch ORDER BY $sortby $sort_direction","$self".$invsort,30,5);
			//echo "SELECT order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status,xmlmade FROM cart_orders as o LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id $order_status $rangefrom $rangeto $istatus $isrch ORDER BY $sortby $sort_direction";
			//pdoDebug("SELECT order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status,xmlmade FROM cart_orders as o LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id $order_status $rangefrom $rangeto $istatus $isrch ORDER BY $sortby $sort_direction",$binds);
			$pgnums=cart_pagenums("SELECT order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status,xmlmade FROM cart_orders as o LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id $order_status $rangefrom $rangeto $istatus $nofree $isrch ORDER BY $sortby $sort_direction","$self".$invsort,30,5,'',$binds);
			$query=$pgnums[0];
			$fromtxt=isset($get_arr['from'])?cart_relative_date("F j\, Y",$get_arr['from']):"";
			$totxt=isset($get_arr['to'])!=""?cart_relative_date("F j\, Y",$get_arr['to']):"Now";
			?><?php if(strlen($pgnums[1])>0){?><?=$pgnums[1]?><?php }?>
			<table class="linkslist">
			<tr class="head">
				<td><div class="titles">Invoices <?=ucwords($fromtxt)?><?=(($fromtxt!="today")?" to ".ucwords($totxt):"")?></div></td>
			</tr>
			
			
			<tr class="row_dark"><form action="<?=$self?>" method="get">
				<td><input type="hidden" name="p" value="cart_invoices" />
				<fieldset style="display:inline"><span class="fieldset_title">Search:</span>&nbsp;<label for="inv">Number </label><input type="text" id="inv" name="invoice" value="<?=isset($get_arr['invoice'])?$get_arr['invoice']:""?>" class="formfields" style="width:50px !important" /> <label for="nme">Last Name </label><input type="text" id="nme" name="lastname" value="<?=isset($get_arr['lastname'])?$get_arr['lastname']:""?>" class="formfields" /></fieldset>
				<fieldset style="display:inline"><span class="fieldset_title">Sort:</span>&nbsp;
				<select name="ssortby" class="formfieldm">
				<option value="invoice" <?php if($sby=="invoice"){?>selected="selected"<?php }?>>Invoice</option>
				<option value="date_ordered" <?php if($sby=="date_ordered"){?>selected="selected"<?php }?>>Order Date</option>
				<option value="pay_method" <?php if($sby=="pay_method"){?>selected="selected"<?php }?>>Payment Method</option>
				<option value="pay_status" <?php if($sby=="pay_status"){?>selected="selected"<?php }?>>Payment Status</option>
				<option value="lastname" <?php if($sby=="lastname"){?>selected="selected"<?php }?>>Last name</option>
				<option value="city" <?php if($sby=="city"){?>selected="selected"<?php }?>>City</option>
				<option value="state" <?php if($sby=="state"){?>selected="selected"<?php }?>>County/State</option>
				<option value="country" <?php if($sby=="country"){?>selected="selected"<?php }?>>Country</option>
				<option value="alt_city" <?php if($sby=="alt_city"){?>selected="selected"<?php }?>>Postage City</option>
				<option value="alt_state" <?php if($sby=="alt_state"){?>selected="selected"<?php }?>>Postage County</option>
				<option value="alt_country" <?php if($sby=="alt_country"){?>selected="selected"<?php }?>>Postage Country</option>
				</select> 
				Status 
				<select name="sstatus" class="formfieldm">
				<?php foreach($cart_orderstatuses as $dbstatus => $displaystatus){?>
					<option value="<?=$dbstatus?>" <?php if($os==$dbstatus){?>selected="selected"<?php }?>><?=$displaystatus?></option>
				<?php }?>
				<option value="all" <?php if($os=="all"){?>selected="selected"<?php }?>>All Orders</option>
				</select> 
				<input type="radio" name="ssortdir" value="DESC" id="desc" <?php if($sort_direction=="DESC"){?>checked="checked"<?php }?> /><label for="desc"> desc.</label> <input type="radio" name="ssortdir" value="ASC" id="asc" <?php if($sort_direction=="ASC"){?>checked="checked"<?php }?> /><label for="asc"> asc.</label> 
				</fieldset></div>
				<input type="submit" value="GO" class="formbutton" name="invsrch" />
				</form>
				</td>
			</tr>
			</table>
			<script type="text/javascript" src="functions.js"></script>
			<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
			<script type="text/javascript">boxarr['iorder_status']=[];</script>
			<form action="<?=$formaction?>&amp;act=updatemany" method="post">
			<table>
			<tr class="subhead">
				<td style="width:7%;text-align:center">Invoice</td>
				<td style="width:20%;text-align:center">Order Date</td>
				<td style="width:35%">Customer</td>
				<td style="width:8%;text-align:center">Method</td>
				<td style="width:5%;text-align:center">Paid?</td>
				<td style="width:10%;text-align:center">Status</td>
				<td style="width:5%;text-align:center">NAV&nbsp;File?</td>
				<td style="width:10%;text-align:right;white-space:nowrap">Complete?&nbsp;<input type="checkbox" onclick="cart_multiCheck(this.form,'iorder_status',this)" /></td>
			</tr>
			<?php 
			/*$invQ=ysql_query($query,CARTDB)or die(sql_error("Error","$query<br />".mysql_error()));
			$invNum=mysql_num_rows($invQ);
			while($inv=mysql_fetch_assoc($invQ))*/
			$invQ=$db1->prepare($query);
			$invQ->execute($binds);
			$invNum=$invQ->rowCount();
			while($inv=$invQ->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td style="text-align:center"><a href="<?=$self?>&amp;act=view&amp;invoice=<?=$inv['invoice']?>"><?=$inv['invoice']?></a></td>
					<td style="text-align:center"><span><?=date("F j\, Y",$inv['date_ordered'])?></span></td>
					<?php if(strlen($inv['custid'])>0){?><td class="blocklink"><a href="index.php?p=cart_customers&amp;act=view&amp;cust_id=<?=$inv['custid']?>"><?php }else{?><td><span><?php }?><?=ucwords($inv['firstname']." ".$inv['lastname'])?><?php if(strlen($inv['custid'])>0){?></a><?php }else{?></span><?php }?></td>
					<td style="text-align:center"><span><?=$inv['pay_method']?></span></td>
					<td style="text-align:center"><span><?=(($inv['pay_status']==1)?"Paid":"Unpaid")?></span></td>
					<td style="text-align:center">
					<input type="hidden" name="order_id[<?=$inv['invoice']?>]" value="<?=$inv['order_id']?>" />
					<select name="inv[<?=$inv['invoice']?>]" class="formfieldm">
					<?php foreach($cart_orderstatuses as $dbstatus => $displaystatus){?>
					<option value="<?=$dbstatus?>" <?php if($inv['order_status']==$dbstatus){?>selected="selected"<?php }?>><?=$displaystatus?></option>
					<?php }?>
					</select>
					</td>
					<td style="text-align:center"><input type='checkbox' <?=($inv['xmlmade']!="0000-00-00 00:00:00"||($inv['xmlmade']!="0000-00-00 00:00:00"&&$inv['date_ordered']<strtotime("24 June 2015")))&&$inv['xmlmade']!=null?"checked='checked'":""?> disabled='disabled' /></td>
					<td style="text-align:right"><input type="hidden" name="iorder_status[<?=$inv['invoice']?>]" value="0" /><input type="checkbox" name="iorder_status[<?=$inv['invoice']?>]" value="1" <?=$inv['iorder_status']==1?"checked='checked'":""?> /></td>
				</tr>				
				<script type="text/javascript">boxarr["iorder_status"].push("iorder_status[<?=$inv['invoice']?>]");</script>
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
			<p class="submit"><input type="submit" value="Update Orders" /></p>
			</form>
		<?php
	}
	break;
}?>		