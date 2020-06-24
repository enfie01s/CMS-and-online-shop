<?php if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
include "cart_head.php";
/* 
----- HANDLE SUBMITTED FORMS ----- 
*/

if(isset($post_arr['identifier']))
{
	$requires=explode(",",$post_arr['required']);
	$errors=array();
	foreach($requires as $require)
	{	
		$required=explode(":",$require);
		if(strlen($post_arr[$required[0]])<1){$errors[$required[0]]=$required[1]." is empty.";}
		else if($required[0]=="email"&&!eregi(EMAILREG, $post_arr['email'])){$errors[$required[0]]=$required[1]." is invalid (eg: user@host.com).";}
	}
	switch($post_arr['identifier'])
	{
		case "doregister":
			if(!array_key_exists("password1",$errors)&&!array_key_exists("password2",$errors)&&$post_arr['password1']!==$post_arr['password2'])
			{
				$errors['passwords']="Passwords do not match.";
			}
			if(!array_key_exists("email",$errors)&&$post_arr['email']!=$ua['email'])
			{
				/*$dupeusers=ysql_query("SELECT `email` FROM cart_customers WHERE `email`='$post_arr[email]'");
				if(mysql_num_rows($dupeusers)>0){$errors['email']="Email address already in use.";}	*/	
				$dupeusers=$db1->prepare("SELECT `email` FROM cart_customers WHERE `email`=?");
				$dupeusers->execute(array($post_arr['email']));
				if($dupeusers->rowCount()>0){$errors['email']="Email address already in use.";}			
			}
			if(!array_key_exists("terms_agree",$post_arr)){$errors['terms_agree']="You must agree to the terms &amp; conditions.";}
			if(count($errors)>0)
			{
				$_SESSION['error']=implode("<br />",$errors);
			}
			else
			{
				$newpass1=hashandsalt($post_arr['email'],$post_arr['password1']);
				$newpass2=hashandsalt($post_arr['email'],$newpass1);
				$q=$db1->prepare("INSERT INTO cart_customers(`nametitle`,`firstname`,`lastname`,`email`,`gpassword`,`address1`,`address2`,`city`,`state`,`country`,`postcode`,`company`,`phone`,`homepage`,`mailing`,`signup_date`,`status`)VALUES(".str_repeat("?,",15)."'".date("U")."','1')");
				$q->execute(array($post_arr['nametitle'],$post_arr['firstname'],$post_arr['lastname'],$post_arr['email'],$newpass2,$post_arr['address1'],$post_arr['address2'],$post_arr['city'],$post_arr['state'],$post_arr['country'],$post_arr['postcode'],$post_arr['company'],$post_arr['phone'],$post_arr['homepage'],$post_arr['opt_in']));
				$message="Hi ".ucwords($post_arr['firstname']." ".$post_arr['lastname']).",\r\n
				\r\n
				Thank you for signing up to GMK. Please find your login details below.\r\n
				\r\n
				<strong>Username:</strong>&#160;".$post_arr['email']."\r\n";
				cart_emailings($post_arr['email'],"Welcome to $sitename",$message,1);
				?><div id="errorbox" style="text-align:center">Successfully registered your account.</div><?php
			}
			break;
		case "doupdate":
			if(count($errors)>0)
			{
				$_SESSION['error']=implode("<br />",$errors);
			}
			else
			{
				cart_query("UPDATE cart_customers SET `nametitle`=?,`firstname`=?,`lastname`=?,`phone`=?,`address1`=?,`address2`=?,`city`=?,`state`=?,`postcode`=?,`country`=?,`homepage`=?,`company`=?,`mailing`=? WHERE `cust_id`=?",array($post_arr['nametitle'],$post_arr['firstname'],$post_arr['lastname'],$post_arr['phone'],$post_arr['address1'],$post_arr['address2'],$post_arr['city'],$post_arr['state'],$post_arr['postcode'],$post_arr['country'],$post_arr['homepage'],$post_arr['company'],$post_arr['mailing'],$ua['cust_id']));
				?><div id="errorbox" style="text-align:center">Details successfully updated</div><?php
			}
			break;
		case "dopassupdate":
			if(!array_key_exists("email",$errors)&&$post_arr['email']!=$ua['email'])
			{
				/*$dupeusers=ysql_query("SELECT `email` FROM cart_customers WHERE `email`='$post_arr[email]'");
				if(mysql_num_rows($dupeusers)>0){$errors['email']="Email address already in use.";}	*/	
				$dupeusers=$db1->prepare("SELECT `email` FROM cart_customers WHERE `email`=?");
				$dupeusers->execute(array($post_arr['email']));
				if($dupeusers->rowCount()>0){$errors['email']="Email address already in use.";}
			}
			if(!array_key_exists("password",$errors))
			{				
				$newpass1=hashandsalt($ua['email'],$post_arr['password']);
				$newpass2=hashandsalt($ua['email'],$newpass1);
				if(strlen($post_arr['password'])<1||$newpass2!=$ua['gpassword'])
				{
					$errors['passwords']="Your password was incorrect, please try again.";
				}
			}
			if(!array_key_exists("password1",$errors)&&!array_key_exists("password2",$errors)&&strlen($post_arr['password1'])>0&&$post_arr['password1']!==$post_arr['password2'])
			{
				$errors['passwords']="Passwords do not match.";
			}
			
			if(count($errors)>0)
			{
				$_SESSION['error']=implode("<br />",$errors);
			}
			else
			{
				$gpass="";
				$binds=array();
				$binds[]=$post_arr['email'];
				if(strlen($post_arr['password1'])>0)
				{
					$newpass1=hashandsalt($post_arr['email'],$post_arr['password1']);
					$newpass2=hashandsalt($post_arr['email'],$newpass1);
					$gpass=" AND `gpassword`=?";
					$binds[]=$newpass2;
				}
				else
				{
					$newpass1=hashandsalt($post_arr['email'],$post_arr['password']);
					$newpass2=hashandsalt($post_arr['email'],$newpass1);
				}
				$binds[]=$ua['cust_id'];
				if($cart_debugmode==0)
				{
					$_SESSION['pass']=$newpass1;
					$_SESSION['email']=$post_arr['email'];
					cart_query("UPDATE cart_customers SET `email`=? ".$gpass." WHERE `cust_id`=?",$binds);
				}else{echo "UPDATE cart_customers SET `email`=? ".$gpass." WHERE `cust_id`=?";}
				?><div id="errorbox" style="text-align:center">Successfully updated email<?=strlen($post_arr['password1'])>0?" and password":""?>.</div><?php
			}
			break;
	}
}
if(isset($_SESSION['error'])&&strlen($_SESSION['error'])>0)
{
	?><div id="errorbox" style=""><p>Error</p><?=$_SESSION['error']?></div><?php
	unset($_SESSION['error']);
}
/* 
----- UPDATE INFO FORM ----- 
*/

if((isset($get_arr['updateform'])&&$_SESSION['loggedin']!=0&&$errormsg=="")||($post_arr['identifier']=="doupdate"&&$errormsg!=""))
{
	$tomatch=isset($post_arr['identifier'])?$post_arr:$ua;
	?>
	All fields marked (*) are required
	<form action="./cart_account&amp;updateform" method="post">
	<input type="hidden" name="identifier" value="doupdate" />
	<input type="hidden" name="required" value="firstname:First Name,lastname:Last Name,email:Email,phone:Phone,address1:Address 1,city:City,state:County/State,postcode:Postcode/Zip,country:Country" />
	<table class="details">
	<tr class="head"><td colspan="2"><div class="titles">Update Information</div></td></tr>
		<!--formrows(fieldsarray(fieldname=>label),requiredfieldsarray,dropdownsarray,radiosarray(fieldname=>radiovalue:radioname),what to match(array/text))-->
		<?php cart_formrows(array("nametitle"=>"Title","firstname"=>"First Name","lastname"=>"Last Name","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company","mailing"=>"Email List"),$requireds['doupdate'],array("state"=>"SELECT `county_id`,`countyname` FROM cart_counties ORDER BY `countyname` ASC","country"=>"SELECT `country_id`,`countryname` FROM cart_countries ORDER BY `countryname` ASC"),array("mailing"=>"2:Plain Text,1:HTML,0:None"),array(),$tomatch,"editform");?>
	</table>
	<br />
	<input type="submit" name="submitupdate" value="Submit" class="formbutton" />
	</form>
<?php 

/*
 ----- PASSWORD CHANGE FORM ----- 
*/

}else if((isset($get_arr['updatepassform'])&&$_SESSION['loggedin']!=0&&$errormsg=="")||($post_arr['identifier']=="dopassupdate"&&$errormsg!="")){?>
	<form action=./cart_account&amp;updatepassform" method="post">
	<input type="hidden" name="identifier" value="dopassupdate" />
	<input type="hidden" name="required" value="email:Email address,password:Your current Password" />
	All fields marked (*) are required
	<table><tr class="head"><td colspan="2"><div class="titles">Change Password</div></td></tr>
	<?php cart_formrows(array("email"=>"Email","password"=>"Your current password","password1"=>"New Password","password2"=>"Confirm New Password"),$requireds['dopassupdate'],array(),array(),array(),array("email"=>$ua['email'],"password"=>"123"),"passchange");?>
	</table>
	<br />
	<input type="submit" name="submitpasschange" value="Modify Password" class="formbutton" />
	</form>
	<?php 
	
/* 
----- DISPLAY INFO ----- 
*/

}else if($_SESSION['loggedin']!=0&&$errormsg==""){?>
	<table class="details">
		<tr class="head">
			<td colspan="2"><div class="titles">Current Information</div><div class="links"><a href="<?=MAINBASE?>/cart_account&amp;updateform">Update Information</a> | <a href="<?=MAINBASE?>/cart_account&amp;updatepassform">Change Email/Password</a></div></td>
		</tr>
		<tr>
			<td class="left_light">Title</td>
			<td class="right_light"><?=$ua['nametitle']?></td>
		</tr>
		<tr>
			<td class="left_light">First Name</td>
			<td class="right_light"><?=$ua['firstname']?></td>
		</tr>
		<tr>
			<td class="left_dark">Last Name</td>
			<td class="right_dark"><?=$ua['lastname']?></td>
		</tr>
		<tr>
			<td class="left_light">Email</td>
			<td class="right_light"><?=($ua['email']!="")?"<a href='mailto:$ua[email]'>$ua[email]</a>":"";?></td>
		</tr>
		<tr>
			<td class="left_dark">Phone</td>
			<td class="right_dark"><?=$ua['phone']?></td>
		</tr>
		<tr>
			<td class="left_light" style="vertical-align:top">Address</td>
			<td class="right_light">
			<?=$ua['address1']?><br />
			<?=$ua['address2']?><br />
			<?=$ua['city']?><br />
			<?=$ua['countyname']?><br />
			<?=$ua['postcode']?><br />
			<?=$ua['countryname']?><br />
			</td>
		</tr>
		<tr>
			<td class="left_dark">Website</td>
			<td class="right_dark"><?=($ua['homepage']!="")?"<a href='$ua[homepage]'>$ua[homepage]</a>":""?></td>
		</tr>
		<tr>
			<td class="left_light">Company</td>
			<td class="right_light"><?=$ua['company']?></td>
		</tr>
		<tr>
			<td class="left_dark">Receive Marketing Emails</td>
			<td class="right_dark"><?=$mailtype[$ua['mailing']]?></td>
		</tr>
		<tr>
			<td class="left_light">Date Registered</td>
			<td class="right_light"><?=date("F d\, Y",$ua['signup_date'])?></td>
		</tr>
	</table>
	<br />
	<?php
	/*$ordersq=ysql_query("SELECT `date_ordered`,`invoice` FROM cart_orders WHERE `cust_id`='$ua[cust_id]' ORDER BY `date_ordered` DESC");
	$orders=mysql_num_rows($ordersq);*/
	$ordersq=$db1->prepare("SELECT `date_ordered`,`invoice` FROM cart_orders WHERE `cust_id`=? ORDER BY `date_ordered` DESC");
	$ordersq->execute(array($ua['cust_id']));
	$orders=$ordersq->rowCount();
	if($orders>0){
		?>
		<table class="details">
		<tr class="head">
			<td colspan="3"><div class="titles">My Orders</div></td>
		</tr>
		<tr class="subhead">
			<td>Order Date</td>
			<td>Invoice</td>
			<td>Details</td>
		</tr>
		<?php //while($order=mysql_fetch_assoc($ordersq))
		while($order=$ordersq->fetch())
		{$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";?>
			<tr class="<?=$row_class?>">
				<td><?=date("F j, Y",$order['date_ordered'])?></td>
				<td><?=$order['invoice']?></td>
				<td><a href="<?=MAINBASE?>/cart_receipt&amp;invoice=<?=$order['invoice']?>">Details</a></td>
			</tr>
		<?php }?>
		</table>
		<?php 
	}
} 

/* 
----- REGISTRATION FORM ----- 
*/

else 
{
	$tomatch=isset($post_arr['identifier'])?$post_arr:"";?>
	All fields marked (*) are required
	<form action="./cart_account&amp;registerform" method="post">
	<input type="hidden" name="identifier" value="doregister" />
	<input type="hidden" name="required" value="firstname:First Name,lastname:Last Name,email:Email,phone:Phone,address1:Address 1,city:City,state:County/State,postcode:Postcode/Zip,country:Country,password1:Password,password2:Comfirm Password" />
	<table class="details">
	<tr class="head"><td colspan="2"><div class="titles">Customer Registration Form</div></td></tr>
	<?php cart_formrows(array("password1"=>"Password","password2"=>"Confirm Password","nametitle"=>"Title","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company"),$requireds['doregister'],array("state"=>"SELECT `county_id`,`countyname` FROM cart_counties ORDER BY `countyname` ASC","country"=>"SELECT `country_id`,`countryname` FROM cart_countries ORDER BY `countryname` ASC"),array(),array(),$tomatch,"editform");?>
	</table>
	<input type="hidden" name="opt_in" value="0" /><input type="checkbox" name="opt_in" id="opt_in" value="1" checked="checked" /><label for="opt_in"> I would like to receive updates and special offers from GMK</label><br />
	<input type="checkbox" name="terms_agree" id="terms_agree" value="1" /><label for="terms_agree"> I agree to the <a href="<?=MAINBASE?>/terms" target="_blank">Terms &amp; Conditions</a></label><br /><br />
	<input type="submit" value="Process Request" class="formbutton" />
	</form>
	<?php 
}
include "cart_foot.php";
?>