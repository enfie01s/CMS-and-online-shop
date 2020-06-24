<?php 
if(!in_array(12,$mods)){?>&nbsp;&nbsp;<?=$noaccessmsg?><?php }else{
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }
$invsort="";
if(isset($_GET['ssortby'])){$invsort.="&ssortby=".$_GET['ssortby'];}
if(isset($_GET['sstatus'])){$invsort.="&sstatus=".$_GET['sstatus'];}
if(isset($_GET['ssortdir'])){$invsort.="&ssortdir=".$_GET['ssortdir'];}
//&sstatus=all&ssortdir=DESC

/*
$os=isset($get_arr['sstatus'])?$get_arr['sstatus']:"all";
$sby=isset($get_arr['ssortby'])?$get_arr['ssortby']:"invoice";
$order_status=(isset($get_arr['sstatus'])&&$get_arr['sstatus']=="all")||$os=="all"?"":($os==""?"WHERE `order_status` is null":"WHERE `order_status`='$os'");
$sortby=!isset($get_arr['ssortby'])?"o.invoice":"o.".$get_arr['ssortby'];
$sort_direction=!isset($get_arr['ssortdir'])?"DESC":$get_arr['ssortdir'];
$invoice=isset($get_arr['invoice'])?$get_arr['invoice']:"";
$rangefrom=isset($get_arr['from'])?(($order_status!="")?"AND":"WHERE")." `date_ordered` >='".$get_arr['from']."'":"";
$rangeto=isset($get_arr['to'])?(($rangefrom!=""||$order_status!="")?"AND":"WHERE")." `date_ordered` <='".$get_arr['to']."'":"";
$istatus=isset($get_arr['istatus'])?(($rangefrom!=""||$rangeto!=""||$order_status!="")?"AND":"WHERE")." `iorder_status`=".$get_arr['istatus']:"";
$warranty=(($rangefrom!=""||$rangeto!=""||$order_status!="" || $istatus!="")?"AND":"WHERE")." `prod_id`='358'";
*/
$os=isset($get_arr['sstatus'])?$get_arr['sstatus']:"all";
$sby=isset($get_arr['ssortby'])?$get_arr['ssortby']:"invoice";
$order_status=(isset($get_arr['sstatus'])&&$get_arr['sstatus']=="all")||$os=="all"?"":($os==""?"WHERE `order_status` is null":"WHERE `order_status`=?");
if(!((isset($get_arr['sstatus'])&&$get_arr['sstatus']=="all")||$os=="all")&&strlen($os)>0){$binds[]=$os;}
$col=cleanCols("cart_orders",$get_arr['ssortby']);
if(strlen($col)<1){$col=cleanCols("cart_customers",$get_arr['ssortby']);}
if(strlen($col)<1){$col=cleanCols("cart_ordership",$get_arr['ssortby']);}
$sortby=!isset($get_arr['ssortby'])?"o.invoice":"o.".$col;
$sort_direction=!isset($get_arr['ssortdir'])||$get_arr['ssortdir']=="DESC"?"DESC":$get_arr['ssortdir'];
$invoice=isset($get_arr['invoice'])?$get_arr['invoice']:"";

$rangefrom=isset($get_arr['from'])?(($order_status!="")?"AND":"WHERE")." `date_ordered` >=?":"";
if(isset($get_arr['from'])){$binds[]=$get_arr['from'];}
$rangeto=isset($get_arr['to'])?(($rangefrom!=""||$order_status!="")?"AND":"WHERE")." `date_ordered` <=?":"";
if(isset($get_arr['to'])){$binds[]=$get_arr['to'];}
$istatus=isset($get_arr['istatus'])?(($rangefrom!=""||$rangeto!=""||$order_status!="")?"AND":"WHERE")." `iorder_status`=?":"";
if(isset($get_arr['istatus'])){$binds[]=$get_arr['istatus'];}
$warranty=(($rangefrom!=""||$rangeto!=""||$order_status!="" || $istatus!="")?"AND":"WHERE")." `prod_id`='358'";

?>
<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Warranties</a><?=$invoice!=""&&($act=="view"||$act=="updatemany"||$act=="update")?$breadsep.($act=="update"?"<a href='".str_replace("update","view",$formaction)."'>":"")."Invoice: ".$get_arr['invoice'].($act=="update"?"</a>".$breadsep."Tracking Information":""):""?></div>

<?php if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }?>
<!-- CONTENT -->

<form action="<?=$self?>&amp;act=search" method="post" style="padding-left:10px">
<input type="text" name="serial" value="<?=isset($_POST['serial'])?$_POST['serial']:""?>" /> <input type="submit" value="Search Serial" />
</form>
<?php
switch($act)
{
	case "search":
		if(isset($_POST['serial']))
		{
			//$sQ=ysql_query("SELECT * FROM gmkserialnums WHERE serial LIKE '%{$post_arr['serial']}%' ORDER BY Brand");
			//$numr=mysql_num_rows($sQ);
			$sQ=$db1->prepare("SELECT * FROM gmkserialnums WHERE serial LIKE ? ORDER BY Brand");
			$sQ->execute(array("%".$post_arr['serial']."%"));
			$numr=$sQ->rowCount();
			?><br />
			<table class="linkslist">
			<tr class="head">
				<td colspan="3"><div class="titles"><?=$numr?> result<?=$numr==1?"":"s"?> for serial number <?=$post_arr['serial']?></div></td>
			</tr>	
			<tr class="subhead">
				<td>Serial</td>
				<td>Brand</td>
				<td>Description</td>
			</tr>
			<?php
			//while($ser=mysql_fetch_assoc($sQ))
			while($ser=$sQ->fetch(PDO::FETCH_ASSOC))
			{
				$row=!isset($row)||$row=="_light"?"_dark":"_light";
				?>
				<tr class="row<?=$row?>">
					<td><?=str_ireplace($post_arr['serial'],"<span style='color:red'>".$post_arr['serial']."</span>",$ser['serial'])?></td>
					<td><?=$ser['Brand']?></td>
					<td><?=$ser['Description']?></td>
				</tr>
				<?php
			}
			if($numr<1)
			{
				?><tr class="row_light"><td colspan="3" style="text-align:center">No record found</td></tr><?php
			}
			?>
			</table>
			<?php
		}
		break;
	case "view":
		/*$invq=ysql_query("SELECT o.`cust_id`,o.`order_id`,`pay_status`,`iorder_status`,`order_status`,o.`nametitle`,o.`firstname`,o.`lastname`,o.`address1`,o.`address2`,o.`city`,o.`state`,o.`postcode`,o.`country`,o.`phone`,`sameasbilling`,`alt_nametitle`,`alt_name`,`alt_address1`,`alt_address2`,`alt_city`,`alt_state`,`alt_postcode`,`alt_country`,`alt_phone`,`ship_description`,`shipper`,`comments`,`tracking`,FROM_UNIXTIME(`date_ordered`,'%d/%m/%Y %h:%i') as date_ordered,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y %h:%i') as date_shipped,o.`Status`,o.`CardType`,o.`Last4Digits` FROM cart_orders AS o LEFT JOIN cart_customers AS c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0' LEFT JOIN cart_ordership AS os on o.`order_id`=os.`order_id` WHERE `invoice`='$invoice'",CARTDB);
		$inv=mysql_fetch_assoc($invq);*/
		$invq=$db1->prepare("SELECT o.`cust_id`,o.`order_id`,`pay_status`,`iorder_status`,`order_status`,o.`nametitle`,o.`firstname`,o.`lastname`,o.`address1`,o.`address2`,o.`city`,o.`state`,o.`postcode`,o.`country`,o.`phone`,`sameasbilling`,`alt_nametitle`,`alt_name`,`alt_address1`,`alt_address2`,`alt_city`,`alt_state`,`alt_postcode`,`alt_country`,`alt_phone`,`ship_description`,`shipper`,`comments`,`tracking`,FROM_UNIXTIME(`date_ordered`,'%d/%m/%Y %h:%i') as date_ordered,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y %h:%i') as date_shipped,o.`Status`,o.`CardType`,o.`Last4Digits`,o.`email` FROM cart_orders AS o LEFT JOIN cart_customers AS c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0' LEFT JOIN cart_ordership AS os on o.`order_id`=os.`order_id` WHERE `invoice`=?");
		$invq->execute(array($invoice));
		$inv=$invq->fetch(PDO::FETCH_ASSOC);
		
		?>
		<table class="details">
		<tr class="head">
			<td colspan="2"><div class="titles">Invoice Details</div></td>
		</tr>
		<tr class="subhead">
			<td style="width:50%">Invoice: <a href="?p=cart_invoices&amp;act=view&amp;invoice=<?=$invoice?>"><?=$invoice?></a></td>
			<td style="width:50%;padding-left:0 !important;">Order date: <?=$inv['date_ordered']?></td>
		</tr>
		<tr class="row_light">
			<td colspan="2">
			<div style="width:25%;float:left;">
				<strong>Bill to:</strong><br />
				<?=($inv['cust_id']!=0?"<a href='$self&amp;act=view&amp;cust_id=$inv[cust_id]'>":"").ucwords($inv['nametitle']." ".$inv['firstname']." ".$inv['lastname']).($inv['cust_id']!=0?"</a>":"")?><br />
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
			<td style="width:50%" class="hidefromprint"><span>Payment Status: <?=$inv['pay_status']==1?"Paid":"Unpaid"?><br />Marked Complete?: <?=$inv['iorder_status']==1?"Complete":"Incomplete"?></span></td>
			<td style="width:50%" class="hidefromprint">
			<span>
			Status: <?=$inv['order_status']?></span></td>
		</tr>
		</table>
		<p class="submit"><a href="#" onclick="window.print();return false">Print Invoice</a></p>
		
		<br />
		<?php /*cart_ordercontents("invoice='$invoice'","");*/cart_ordercontents("invoice=?","",array($invoice));?>
		<?php
		break;
	default:
		$toupdate=""; 
		$invoices=array();		
			$invsort=isset($invsort)?$invsort:"";
			$qq="SELECT o.order_id,invoice,date_ordered,pay_method,o.firstname,o.lastname,c.cust_id as custid,order_status,pay_status,iorder_status FROM (cart_orders as o JOIN cart_orderproducts as op ON o.order_id=op.order_id) LEFT JOIN cart_customers as c ON o.cust_id=c.cust_id $order_status $rangefrom $rangeto $istatus $warranty ORDER BY $sortby $sort_direction";
			//echo $qq;
			$pgnums=cart_pagenums($qq,"$self".$invsort,30,5,'',$binds);
			$query=$pgnums[0];
			$fromtxt=isset($get_arr['from'])?cart_relative_date("F j\, Y",$get_arr['from']):"";
			$totxt=isset($get_arr['to'])!=""?cart_relative_date("F j\, Y",$get_arr['to']):"Now";
			
			if(strlen($pgnums[1])>0){?><?=$pgnums[1]?><?php }?>
			<table class="linkslist">
			<tr class="head">
				<td><div class="titles">Invoices containing a 10yr Warranty</div></td>
			</tr>			
			<tr class="row_light">
				<td style="border-bottom:0;">
				<form action="<?=$self?>" method="get">
				<input type="hidden" name="p" value="cart_warranties" />
				Sort invoices by 
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
				<input type="submit" value="Sort" class="formbutton" />
				</form>
				</td>
			</tr>
			</table>
			<script type="text/javascript" src="functions.js"></script>
			<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
			<script type="text/javascript">boxarr['iorder_status']=[];</script>
			<table>
			<tr class="subhead">
				<td style="width:7%;text-align:center">Invoice</td>
				<td style="width:20%;text-align:center">Order Date</td>
				<td style="width:30%">Customer</td>
				<td style="width:8%;text-align:center">Method</td>
				<td style="width:5%;text-align:center">Paid?</td>
				<td style="width:15%;text-align:center">Status</td>
				<td style="width:15%;text-align:right">Complete?</td>
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
					<td style="text-align:center"><?=$inv['order_status']?>
					</td>
					<td style="text-align:right"><?=$inv['iorder_status']==1?"&#10003;":""?></td>
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
		<?php
	break;
}}?>		