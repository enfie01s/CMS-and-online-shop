<?php
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }
$custid=isset($get_arr['cust_id'])?$get_arr['cust_id']:"";

if($act=="edit"&&isset($post_arr['lastname']))
{
	//check email exists
	$founderrors="";
	$founderrors.=cart_emptyfieldscheck($post_arr,array("firstname"=>"Please enter a first name","lastname"=>"Please enter a last name","email"=>"Please enter an email address","address1"=>"Please enter the first line of the customer's address","city"=>"Please enter the city","state"=>"Please enter the county","postcode"=>"Please enter the postcode","country"=>"Please enter the country"));
	if(strlen($founderrors)>0)
	{
		$_SESSION['error']=$founderrors;
	}
	else
	{
		extract($post_arr);
		//encrypt password
		$binds=array($firstname,$lastname,$email);
		if(strlen($password)>0)
		{
			$firstwave=cart_hashandsalt($email,$password);
			$pass="`gpassword`=?, ";$binds[]=cart_hashandsalt($email,$firstwave);
		}else{$pass="";}
		$binds[]=$address1;
		$binds[]=$address2;
		$binds[]=$city;
		$binds[]=$state;
		$binds[]=$country;
		$binds[]=$postcode;
		$binds[]=$company;
		$binds[]=$phone;
		$binds[]=$homepage;
		$binds[]=$mailing;
		$binds[]=$status;
		$binds[]=$custid;
		//update
		cart_query("UPDATE cart_customers SET firstname=?, lastname=?, email=?,".$pass." address1=?, address2=?, `city`=?, `state`=?, `country`=?, `postcode`=?, company=?, phone=?, homepage=?, mailing=?, status=? WHERE cust_id=?",$binds);
		//redirect to user details
		//header("Location: $mainbase/admin.php?p=customers&act=view&cust_id=$custid");
	}
}
else if($act=="add"&&isset($post_arr['lastname']))
{
	//check email exists
	$founderrors="";
	/*$dupecheckq=ysql_query("SELECT `cust_id` FROM cart_customers WHERE `email`='$post_arr[email]'",CARTDB);
	$dupes=mysql_num_rows($dupecheckq);$dupe=mysql_fetch_row($dupecheckq);*/
	$dupecheckq=$db1->prepare("SELECT `cust_id` FROM cart_customers WHERE `email`=?");
	$dupecheckq->execute(array($post_arr['email']));
	$dupes=$dupecheckq->rowcount();$dupe=$dupecheckq->fetch();
	if($dupes>0){$founderrors.="That email address is already in use <a href='$self&amp;act=view&amp;cust_id=$dupe[0]'>here</a>";}
	$founderrors.=cart_emptyfieldscheck($post_arr,array("password"=>"Please enter a password","firstname"=>"Please enter a first name","lastname"=>"Please enter a last name","email"=>"Please enter an email address","address1"=>"Please enter the first line of the customer's address","city"=>"Please enter the city","state"=>"Please enter the county","postcode"=>"Please enter the postcode","country"=>"Please enter the country"));
	if(strlen($founderrors)>0)
	{
		$_SESSION['error']=$founderrors;
	}
	else
	{
		extract($post_arr);
		//encrypt password
		$firstwave=cart_hashandsalt($email,$password);
		$pass=cart_hashandsalt($email,$firstwave);
		//insert
		cart_query("INSERT INTO cart_customers (firstname,lastname,email,gpassword,address1,address2,city,state,country,postcode,company,phone,homepage,mailing,signup_date,status)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,'".date("U")."','1')",array($firstname,$lastname,$email,$pass,$address1,$address2,$city,$state,$country,$postcode,$company,$phone,$homepage,$mailing));
		$custid=$db1->lastInsertId();
		//redirect to new user details
		//header("Location: $mainbase/admin.php?p=customers&act=view&cust_id=$custid");
	}
}
else
{
	$del=array();
	$son=array();
	$soff=array();
	if(isset($post_arr['delete'])&&is_array($post_arr['delete']))
	{
		foreach($post_arr['delete'] as $cuid => $onoff)
		{
			$del[]=$cuid;
		}
	}	
	if(isset($post_arr['status'])&&is_array($post_arr['status']))
	{
		foreach($post_arr['status'] as $cuid => $onoff)
		{
			if($onoff==1&&!in_array($cuid,$del)){$son[]=$cuid;}
			else if($onoff==0&&!in_array($cuid,$del)){$soff[]=$cuid;}
		}
	}
	//print_r(array_keys($post_arr['delete']));
	$on_ins=bindIns(implode(",",$son));
	$off_ins=bindIns(implode(",",$soff));
	$del_ins=bindIns(implode(",",$del));
	if(count($son)>0){cart_query("UPDATE cart_customers SET `status`='1' WHERE `cust_id` IN(".$on_ins[0].")",$on_ins[1]);}
	if(count($soff)>0){cart_query("UPDATE cart_customers SET `status`='0' WHERE `cust_id` IN(".$off_ins[0].")",$off_ins[1]);}
	if(count($del)>0){cart_query("DELETE FROM cart_customers WHERE `cust_id` IN(".$del_ins[0].")",$del_ins[1]);}
}
$binds=array();
$searchby=isset($get_arr['searchby'])?$get_arr['searchby']:"";
$searchterm=isset($get_arr['searchterm'])?$get_arr['searchterm']:"";
$searchfrom=isset($get_arr['searchfrom'])?$get_arr['searchfrom']:"";
if(strlen($searchterm)>0){
	$col=cleanCols("cart_customers",$searchby);
	if(strlen($col)<1){$col=cleanCols("cart_counties",$searchby);}
	if(strlen($col)<1){$col=cleanCols("cart_countries",$searchby);}
	
	if(strlen($col)>0)
	{
		$search="WHERE $col LIKE ?";
		$binds[]="%".$searchterm."%";
	}
	$searchurl="&amp;searchby=$searchby&amp;searchterm=$searchterm";
}
else if(strlen($searchfrom)>0){
	$search="WHERE signup_date >= ?";
	$binds[]=$searchfrom;
	$searchurl="&amp;searchfrom=$searchfrom";
}
if(isset($custid))
{
	/*$custq=ysql_query("SELECT * FROM (cart_customers as cu LEFT JOIN cart_counties as c ON c.`county_id`=cu.`state`) LEFT JOIN cart_countries as co ON co.`country_id`=cu.`country` WHERE `cust_id`='$custid'");
	$cust=mysql_fetch_assoc($custq);*/
	$custq=$db1->prepare("SELECT * FROM (cart_customers as cu LEFT JOIN cart_counties as c ON c.`county_id`=cu.`state`) LEFT JOIN cart_countries as co ON co.`country_id`=cu.`country` WHERE `cust_id`=?");
	$custq->execute(array($custid));
	$cust=$custq->fetch(PDO::FETCH_ASSOC);
}
?>
<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Customers</a><?=strlen($custid)>0?$breadsep.($act=="edit"?"<a href='".str_replace("edit","view",$formaction)."'>$cust[firstname] $cust[lastname]</a>":$cust['firstname']." ".$cust['lastname']):""?></div>

<?php if(isset($_SESSION['error'])){?><div class="notice"><div style="font-weight:bold;font-size:14px">Error</div><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }

switch($act)
{
	case "view":
		/*$ordersq=ysql_query("SELECT `date_ordered`,`invoice`,`order_status`,`total_price` FROM cart_orders WHERE `cust_id`='$cust[cust_id]' ORDER BY `date_ordered` DESC",CARTDB);
		$num_orders=mysql_num_rows($ordersq);*/
		$ordersq=$db1->prepare("SELECT `date_ordered`,`invoice`,`order_status`,`total_price` FROM cart_orders WHERE `cust_id`=? ORDER BY `date_ordered` DESC");
		$ordersq->execute(array($cust['cust_id']));
		$num_orders=$ordersq->rowCount();
		?><p class="submittop"><a href="<?=$self?>&amp;act=edit&amp;cust_id=<?=$custid?>">Edit customer info</a></p>
		<table style="float:left;width:48%;margin:0 10px;">
		<tr class="head">
			<td colspan="2"><div class="titles">Customer Details</div></td>
		</tr>
		<tr>
			<td class="first left_light" style="width:30%">Signup date:</td>
			<td class="first left_light" style="width:70%"><?=date("F d\, Y",$cust['signup_date'])?></td>
		</tr>
		<tr>
			<td class="left_dark">Name</td>
			<td class="right_dark"><?=$cust['firstname']." ".$cust['lastname']?></td>
		</tr>
		<tr>
			<td class="left_light">Email</td>
			<td class='right_light blocklink'><?=($cust['email']!="")?"<a href='mailto:$cust[email]'>$cust[email]</a>":"";?></td>
		</tr>
		<tr>
			<td class="left_dark" style="vertical-align:top">Address</td>
			<td class="right_dark">
			<?=$cust['address1']?><br />
			<?=($cust['address2']?$cust['address2']."<br />":"")?>
			<?=$cust['city']?><br />
			<?=$cust['countyname']?><br />
			<?=$cust['postcode']?><br />
			<?=$cust['countryname']?><br />
			</td>
		</tr>
		<tr>
			<td class="left_light">Phone</td>
			<td class="right_light"><?=$cust['phone']?></td>
		</tr>
		<tr>
			<td class="left_dark">Website</td>
			<td class="right_dark"><?=$cust['homepage']!=""?"<a href='$ua[homepage]'>$ua[homepage]</a>":""?></td>
		</tr>
		<tr>
			<td class="left_light">Company</td>
			<td class="right_light"><?=$cust['company']?></td>
		</tr>
		<tr>
			<td class="left_dark">Receive Marketing Emails</td>
			<td class="right_dark"><?=$mailtype[$cust['mailing']]?></td>
		</tr>
		</table>
			<table style="float:left;width:49%">
			<tr class="head">
				<td colspan="5"><div class="titles"><?=$num_orders?> Order<?=$num_orders==1?"":"s"?></div></td>
			</tr>
			<tr class="subhead">
				<td style="width:10%">Invoice</td>
				<td style="width:60%">Order Date</td>
				<td style="width:15%">Status</td>
				<td style="width:15%">Value</td>
				<td class="blocklink">Details</td>
			</tr>
			<?php 
			//while($order=mysql_fetch_assoc($ordersq))
			while($order=$ordersq->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td><span><?=$order['invoice']?></span></td>
					<td><span><?=date("F j, Y",$order['date_ordered'])?></span></td>
					<td><span><?=$order['order_status']?></span></td>
					<td><span>&#163;<?=number_format($order['total_price'],2)?></span></td>
					<td><a href="<?=$indexpage?>?p=cart_invoices&amp;act=view&amp;invoice=<?=$order['invoice']?>">Details</a></td>
				</tr>
			<?php 
			}
			if($num_orders<1)
			{
				?><tr class="row_dark"><td colspan="5" style="text-align:center">No orders found</td></tr><?php
			}
			?>
			</table>
			<?php 
		break;
	case "edit":
	case "add":
		$errorlist=$higherr;
		$data=isset($post_arr['firstname'])?$post_arr:($act=="edit"?$cust:array());
		
		if(!array_key_exists("mailing",$data)){$data['mailing']=1;}
		if(!array_key_exists("status",$data)){$data['status']=1;}
		
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<form action="<?=$self?>&amp;act=<?=$act?><?=$custid!=""?"&amp;cust_id=$custid":""?>" name="editform" id="editform" method="post" onsubmit="javascript:return checkForm('editform')">
		<input type="hidden" name="required" value="<?=implode("editform,",$requireds['admindoupdate'])?>editform<?php if($act=="add"){?>,passwordeditform<?php }?>" />
		<table>
			<tr class="head">
				<td colspan="2"><div class="titles"><?=$act=="add"?"New":"Update"?> Customer</div></td>
			</tr>
			<?php cart_formrows(array("password"=>"Password","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company","mailing"=>"Email List","status"=>"Status"),$requireds['admindoupdate'],array("state"=>"SELECT county_id,countyname FROM cart_counties ORDER BY countyname ASC","country"=>"SELECT country_id,countryname FROM cart_countries ORDER BY countryname ASC"),array("mailing"=>"2:Plain Text,1:HTML,0:None","status"=>"1:On,0:Off"),array(),$data,"editform");?>
			
		</table>
		<p class="submit"><input type="submit" value="<?=$act=="add"?"Add":"Update"?> User" /></p>
		</form>
		<?php
		break;
	default:
		if(!isset($search)||strlen($search)<1){$search="";}
		$pgnums=cart_pagenums("SELECT * FROM (cart_customers as cu LEFT JOIN cart_counties as c ON c.county_id=cu.state) LEFT JOIN cart_countries as co ON co.country_id=cu.country $search ORDER BY lastname","$self",300,5,'',$binds);
		$query=$pgnums[0];
		/*$custsq=ysql_query($query);
		$custsnum=mysql_num_rows($custsq);*/
		$custsq=$db1->prepare($query);
		$custsq->execute($binds);
		$custsnum=$custsq->rowCount();
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script><form action="<?=$self?>" method="get" style="float:left;margin:0 0 10px 10px;">
			<input type="hidden" name="p" value="cart_customers" />
			Search <input type="text" name="searchterm" value="<?=cart_posted_value("searchterm","","",$get_arr)?>" /> by 
			<select name="searchby">
			<option value="lastname" <?=cart_is_selected("searchby","","lastname",$get_arr,"select")?>>Last Name</option>
			<option value="firstname" <?=cart_is_selected("searchby","","firstname",$get_arr,"select")?>>First Name</option>
			<option value="city" <?=cart_is_selected("searchby","","city",$get_arr,"select")?>>City</option>
			<option value="state" <?=cart_is_selected("searchby","","state",$get_arr,"select")?>>County/State</option>
			<option value="country" <?=cart_is_selected("searchby","","country",$get_arr,"select")?>>Country</option>
			</select> 
			<input type="submit" value="Search" />
			</form>
		<p class="submittop"><a href="<?=$self?>&amp;genmailing=1">Generate mailing list</a> <a href="<?=$self?>&amp;act=add">Add new customer</a></p>
		<div class="clear"></div>
		<?php if(!isset($searchurl)){$searchurl="";}?>
		<form action="<?=$self.$searchurl?>" method="post">
		<table class="details">
		<tr class="head">
			<td colspan="8"><div class="titles"><?=$prods_num?> Customers</div></td>
		</tr>		
		<tr class="subhead">
			<td style="width:30%">Customer</td>
			<td style="width:20%">County</td>
			<td style="width:20%">Country</td>
			<td style="width:10%;text-align:center">Newsletter</td>
			<td style="width:20%">Signup</td>
			<td style="text-align:center">Status</td>	
			<td style="text-align:center">Delete</td>		
		</tr>
		<?php while($custs=$custsq->fetch(PDO::FETCH_ASSOC)){$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";?>
		<tr class="<?=$row_class?>">
			<td class='blocklink'><a href="<?=$self?>&amp;act=view&amp;cust_id=<?=$custs['cust_id']?>"><?=$custs['lastname'].", ".$custs['firstname']?></a></td>
			<td><span><?=$custs['countyname']?></span></td>
			<td><span><?=$custs['countryname']?></span></td>
			<td style="text-align:center"><?=$mailtype[$custs['mailing']]?></td>
			<td><span><?=date("F d, Y",$custs['signup_date'])?></span></td>
			<td style="text-align:center">
			<input type="hidden" name="status[<?=$custs['cust_id']?>]" value="0" />
			<input type="checkbox" name="status[<?=$custs['cust_id']?>]" value="1" <?=cart_is_selected("status",$custs['cust_id'],"1",$custs,"check")?> />
			</td>		
			<td style="text-align:center"><input type="checkbox" name="delete[<?=$custs['cust_id']?>]" value="1" /></td>
		</tr>
		<?php }
		if(strlen($pgnums[1])>0){?>
		<tr class="infohead"><td colspan="8"><?=$pgnums[1]?></td></tr>
		<?php }
		if($custsnum<1)
		{
			?><tr class="row_light"><td colspan="8" style="text-align:center">No customers found</td></tr><?php
		}
		?>
		</table>
		<?php
		if($custsnum>0)
		{
			?>
			<p class="submit"><input type="submit" name="items" value="Update Selected" onclick="return confirm('Are you sure you wish to alter the status of/delete the selected customers?\n\n(Ticked: Enabled, Unticked: Disabled)\n\n')" /> <a href="#top">Back to top</a></p>
			<?php 
		}
		?>
		</form>
		<?php
		break; 
}
?>