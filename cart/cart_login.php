<?php 
if(basename($_SERVER['PHP_SELF'])=="cart_login.php"){die("Access Denied");}//direct access security
if(isset($get_arr['to_p'])&&$get_arr['to_p']=="cart_co_address"){$breadstring=$breadsep."<a href='./shop/basket'>Shopping Basket</a>".$breadsep."Customer Login";}

include "cart_head.php";

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
	/*$emailcheck_q=ysql_query("SELECT `email`, `gpassword`, `firstname`, `signup_date`,`cust_id` FROM cart_customers WHERE `email`='$post_arr[email]'")or die(sql_error("Error"));
	$emailcheck=mysql_num_rows($emailcheck_q);
	list($demail,$dpass,$dname,$dsign,$dcustid)=mysql_fetch_row($emailcheck_q);*/
	$emailcheck_q=$db1->prepare("SELECT `email`, `gpassword`, `firstname`, `signup_date`,`cust_id` FROM cart_customers WHERE `email`=?");
	$emailcheck_q->execute(array($post_arr['email']));
	$emailcheck=$emailcheck_q->rowCount();
	list($demail,$dpass,$dname,$dsign,$dcustid)=$emailcheck_q->fetch(PDO::FETCH_NUM);
	switch($post_arr['identifier'])
	{
		case "lostpass":
			if($emailcheck==0&&strlen($errors['email'])<1){$errors['email']="Sorry, no customer was found with that email address.";}
			if(count($errors)>0)
			{
				$_SESSION['error']=implode("<br />",$errors);
			}
			else
			{
				$thehash=md5($dname.$demail.$dpass.$dsign);
				$eheaders = "From: $sitename <sales@llc-ltd.co.uk>\r\n";
				$eheaders .= "Reply-To: sales@llc-ltd.co.uk\r\n";
				$eheaders .= "Return-Path: sales@llc-ltd.co.uk\r\n";
				$eheaders .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
				$to=$demail;
				$subject="Resquest to reset your password on $sitename";
				$msg="===========================================<br />
				Request to reset your password on the $sitename website<br />
				===========================================<br />
				Hi ".$dname.",<br />
				<br />
				A request was made to reset your password. If you did not make this request or have since remembered your password, please ignore this email.<br /><br />
				Click <a href='".MAINBASE."/index.php?p=cart_login&amp;resetpassform=".$thehash."'>HERE</a> to reset your password.<br /><br />
				Kind Regards<br />
				<a href='$webby'>$sitename</a><br />
				01489 557 600<br />
				<a href='mailto:sales@llc-ltd.co.uk'>sales@llc-ltd.co.uk</a>";
				@mail($to,$subject,$msg,$eheaders);
			}
			break;
		case "dopassreset":
			if(md5($dname.$demail.$dpass.$dsign)!=$post_arr['code'])
			{
				$errors['code']="Invalid code, please submit the lost password form again <a href='./cart_login'>here</a>.";
			}
			if(!array_key_exists("password1",$errors)&&!array_key_exists("password2",$errors)&&$post_arr['password1']!==$post_arr['password2'])
			{
				$errors['passwords']="Passwords do not match.";
			}
			if(count($errors)>0)
			{
				$_SESSION['error']=implode("<br />",$errors);
			}
			else
			{
				$newpass1=cart_hashandsalt($demail,$post_arr['password1']);
				$newpass2=cart_hashandsalt($demail,$newpass1);
				if($cart_debugmode==0)
				{
					$_SESSION['pass']=$newpass1;
					$_SESSION['email']=$demail;
				}
				cart_query("UPDATE cart_customers SET gpassword=? WHERE cust_id=?",array($newpass2,$dcustid));
				?><div id="errorbox" style="text-align:center">Password successfully updated, click <a href="./cart_login" style="color:red;font-weight:bold;text-decoration:underline">here</a> to log in</div><?php
			}
			break;
	}
}
if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }
/* ----- PASSWORD RESET FORM ----- */
if(isset($get_arr['resetpassform'])&&$get_arr['resetpassform']!=""){
$match=(isset($post_arr['identifier']))?$post_arr:"";
?><h2 id="pagetitle">Password Reset</h2>
	<form action="./cart_login&resetpassform=<?=$get_arr['resetpassform']?>" method="post">
	<input type="hidden" name="identifier" value="dopassreset" />
	<input type="hidden" name="required" value="email:Email,password1:New Password,password2:Confirm New Password" />
	<input type="hidden" name="code" value="<?=$get_arr['resetpassform']?>" />	
	All fields marked (*) are required
	<table>
	<tr class="head"><td colspan="2"><div class="titles">Password reset form</div></td></tr>
	<?php cart_formrows(array("email"=>"Email","password1"=>"New Password","password2"=>"Confirm New Password"),array('email','password1','password2'),array(),array(),array(),$match,"resetpass");?>
	</table>
	<br />
	<input type="submit" name="submitpasschange" value="Modify Password" class="formbutton" />
	</form>
<?php }else{
/* ----- CUSTOMER LOGIN FORM ----- */
?><h2 id="pagetitle">Customer Login</h2><?php 
if(isset($post_arr['identifier'])&&$post_arr['identifier']=="lostpass"&&$errormsg==""){?>Request Successful. Please check your email for further instructions.<?php }
if(isset($_SESSION["success"])&&$_SESSION["success"]!=""){echo $_SESSION["success"];unset($_SESSION["success"]);}
?><div id="errorbox" style=" <?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div><?php
if(isset($get_arr['to_'])&&$get_arr['to_']=="/cart_co_address")
{//#385276
	?><h2><a href="./cart_co_address?checkoutnew=1" style="display:inline-block;padding:3px;background:#2B324D;border:1px solid red;">Not Registered? Click here to continue as a new customer &#62;</a></h2><?php
}
else
{
	?><h2><a href="./cart_account&amp;registerform">Not Registered? Sign up here &#62;</a></h2><?php 
}?>
<div style="width:90%">
<form action="<?=$cart_path?>/cart_auth.php" method="post">
<input type="hidden" name="identifier" value="login" />
<input type="hidden" name="redirectstring" value="<?=str_replace(array("mp=cart_login&","mp=cart_login","to_=","to_","&hash="),array("","","","","#"),$_SERVER['QUERY_STRING'])?>" />
<table style="width:100%">
<tr class="head"><td colspan="2"><div class="titles">Customer Login</div></td></tr>
<?php cart_formrows(array("email"=>"Email Address","pass"=>"Password"),array("email","pass"),array(),array(),array(),$match,"login");?>
</table>
<p class="submit"><input type="submit" name="loginsubmit" value="Customer Login" class="formbutton" /></p>
</form>
<br />
<form action="./cart_login" method="post">
<input type="hidden" name="identifier" value="lostpass" />
<input type="hidden" name="required" value="email:Email Address" />
<table style="width:100%">
<tr class="head"><td colspan="2"><div class="titles">Lost your password?</div></td></tr>
<tr>
<td class="left_light"><label for="emaillost">Email Address</label></td>
<td class="right_light"><input type="text" name="email" id="emaillost" value="email address" onfocus="this.select()" class="input_text" /></td>
</tr>
</table>
<p class="submit"><input type="submit" name="passsubmit" value="Send Password" class="formbutton" /></p>
</form>
</div>
<?php }include "cart_foot.php";?>